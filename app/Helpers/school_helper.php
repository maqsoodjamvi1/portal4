<?php

// function check_permission($permission)
// {
//     $role = session()->get('role');
//     return in_array($role, ['admin', 'teacher']);
// }

// function getSchoolInfo()
// {
//     return (object)[
//         'system_id' => session()->get('school_id') ?? 1,
//         'school_name' => 'Default School'
//     ];
// }

if (!function_exists('getSchoolInfo')) {
    function getSchoolInfo()
    {
        $campusId = session()->get('member_campusid');
        if (!$campusId) {
            return null;
        }

        $db = \Config\Database::connect();

        return $db->query(
            'SELECT * FROM `system` WHERE system_id IN (SELECT system_id FROM campus WHERE campus_id = ?)',
            [$campusId]
        )->getRow();
    }
}

/**
 * Font size for a one-line school title: full size up to $refChars (UTF-8),
 * then scale down proportionally so long names still fit on one line.
 *
 * @param string $name      School display name
 * @param int    $refChars  Character count at which base size applies (default 22)
 * @param float  $baseSize  Font size for names up to $refChars (pt or px in caller)
 * @param float  $minSize   Floor so text stays readable
 */
if (!function_exists('school_name_fit_font_size')) {
    function school_name_fit_font_size(string $name, int $refChars = 22, float $baseSize = 11.0, float $minSize = 6.5): float
    {
        $name = trim($name);
        $len  = mb_strlen($name, 'UTF-8');
        if ($len <= 0 || $len <= $refChars) {
            return $baseSize;
        }

        return max($minSize, round($baseSize * ($refChars / $len), 2));
    }
}

/**
 * Class label next to student name on fee challans: prefer DB short name, else compact "Grade N" → "N".
 */
if (! function_exists('fee_chalan_class_badge_text')) {
    function fee_chalan_class_badge_text(?string $classShortName, ?string $classFullName): string
    {
        $src = trim((string) $classShortName);
        if ($src === '') {
            $src = trim((string) $classFullName);
        }
        if ($src === '') {
            return '';
        }
        $out = preg_replace('/^Grade\s+/iu', '', $src);

        return $out !== '' ? $out : $src;
    }
}

/**
 * Fee challan student line: full name plus registration in parentheses, e.g. Ali Ahmad (26-DHS-2023).
 */
if (! function_exists('fee_chalan_student_display_name')) {
    function fee_chalan_student_display_name(?string $name, ?string $regNo): string
    {
        $name = trim((string) $name);
        $reg  = trim((string) $regNo);
        if ($reg === '') {
            return $name;
        }
        $suffix = '(' . $reg . ')';
        if ($name !== '' && strpos($name, $suffix) !== false) {
            return $name;
        }

        return $name === '' ? $suffix : $name . ' ' . $suffix;
    }
}

/**
 * Advance fee is stored as a paid fee_chalan row with this fee_type_id (balance in amount).
 */
if (! function_exists('advance_fee_type_id')) {
    function advance_fee_type_id(): int
    {
        return 194;
    }
}

/**
 * Remaining advance balance for a student (one ledger row per student).
 */
if (! function_exists('get_student_advance_balance')) {
    function get_student_advance_balance($db, int $studentId): float
    {
        if (! $db || $studentId <= 0) {
            return 0.0;
        }

        $advanceId = advance_fee_type_id();

        $row = $db->table('fee_chalan')
            ->select('amount')
            ->where('student_id', $studentId)
            ->where('fee_type_id', $advanceId)
            ->where('status', 'paid')
            ->where('amount >', 0)
            ->orderBy('chalan_id', 'DESC')
            ->get()
            ->getRow();

        return $row ? (float) $row->amount : 0.0;
    }
}

/**
 * Upsert advance balance (adds $paymentAmount to existing balance).
 *
 * @return array{chalan_id:int,balance:float}
 */
if (! function_exists('add_student_advance_payment')) {
    function add_student_advance_payment(
        $db,
        int $studentId,
        float $paymentAmount,
        int $userId,
        ?string $paidDateYmd = null
    ): array {
        $paymentAmount = round(max(0.0, $paymentAmount), 2);
        if ($paymentAmount <= 0) {
            return ['chalan_id' => 0, 'balance' => get_student_advance_balance($db, $studentId)];
        }

        $paidDate = $paidDateYmd ?: date('Y-m-d');
        $now      = date('Y-m-d H:i:s');
        $feeType  = advance_fee_type_id();

        $existing = $db->table('fee_chalan')
            ->select('chalan_id, amount')
            ->where('student_id', $studentId)
            ->where('fee_type_id', $feeType)
            ->where('status', 'paid')
            ->orderBy('chalan_id', 'DESC')
            ->get()
            ->getRow();

        $newBalance = $paymentAmount + ($existing ? (float) $existing->amount : 0.0);

        $payload = [
            'amount'         => round($newBalance, 2),
            'discount'       => 0,
            'fee_type_id'    => $feeType,
            'status'         => 'paid',
            'payment_status' => 'advance',
            'student_id'     => $studentId,
            'fee_month'      => date('Y-m'),
            'user_id'        => $userId,
            'paid_date'      => $paidDate,
            'updated_date'   => $now,
        ];

        if ($existing) {
            $db->table('fee_chalan')->where('chalan_id', (int) $existing->chalan_id)->update($payload);

            return ['chalan_id' => (int) $existing->chalan_id, 'balance' => round($newBalance, 2)];
        }

        $payload['created_date'] = $now;
        $payload['issue_date']   = $paidDate;
        $payload['due_date']       = $paidDate;
        $db->table('fee_chalan')->insert($payload);

        return ['chalan_id' => (int) $db->insertID(), 'balance' => round($newBalance, 2)];
    }
}

/**
 * Set advance balance to an exact amount (admin edit). Preserves paid_date on existing rows.
 *
 * @return array{chalan_id:int,balance:float}
 */
if (! function_exists('set_student_advance_balance')) {
    function set_student_advance_balance(
        $db,
        int $studentId,
        float $balance,
        int $userId
    ): array {
        $balance = round(max(0.0, $balance), 2);
        $feeType   = advance_fee_type_id();
        $now       = date('Y-m-d H:i:s');

        $existing = $db->table('fee_chalan')
            ->select('chalan_id, paid_date')
            ->where('student_id', $studentId)
            ->where('fee_type_id', $feeType)
            ->where('status', 'paid')
            ->orderBy('chalan_id', 'DESC')
            ->get()
            ->getRow();

        if ($existing) {
            $db->table('fee_chalan')->where('chalan_id', (int) $existing->chalan_id)->update([
                'amount'       => $balance,
                'fee_type_id'  => $feeType,
                'updated_date' => $now,
                'user_id'      => $userId,
            ]);

            return ['chalan_id' => (int) $existing->chalan_id, 'balance' => $balance];
        }

        if ($balance <= 0) {
            return ['chalan_id' => 0, 'balance' => 0.0];
        }

        $paidDate = date('Y-m-d');
        $db->table('fee_chalan')->insert([
            'student_id'     => $studentId,
            'fee_type_id'    => $feeType,
            'amount'         => $balance,
            'discount'       => 0,
            'status'         => 'paid',
            'payment_status' => 'advance',
            'fee_month'      => date('Y-m'),
            'paid_date'      => $paidDate,
            'issue_date'     => $paidDate,
            'due_date'       => $paidDate,
            'user_id'        => $userId,
            'created_date'   => $now,
            'updated_date'   => $now,
        ]);

        return ['chalan_id' => (int) $db->insertID(), 'balance' => $balance];
    }
}

if (! function_exists('normalizeSchoolTimingTime')) {
    /**
     * Normalize a time string for comparison (HH:MM or HH:MM:SS).
     */
    function normalizeSchoolTimingTime(?string $time): ?string
    {
        $time = trim((string) $time);
        if ($time === '') {
            return null;
        }

        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $time, $m)) {
            return sprintf('%02d:%02d:%02d', (int) $m[1], (int) $m[2], (int) ($m[3] ?? 0));
        }

        return $time;
    }
}

if (! function_exists('isSchoolTimingWorkingDay')) {
    /**
     * Working day when check-in and check-out are both set and differ (off day = same time).
     */
    function isSchoolTimingWorkingDay(?string $checkin, ?string $checkout): bool
    {
        $checkin  = normalizeSchoolTimingTime($checkin);
        $checkout = normalizeSchoolTimingTime($checkout);

        if ($checkin === null || $checkout === null) {
            return false;
        }

        return $checkin !== $checkout;
    }
}

if (! function_exists('schoolTimingDayStatusLabel')) {
    function schoolTimingDayStatusLabel(?string $checkin, ?string $checkout): string
    {
        return isSchoolTimingWorkingDay($checkin, $checkout) ? 'Working' : 'Off';
    }
}

if (! function_exists('schoolTimingWeekdayMap')) {
    /**
     * @return array<string, int> e.g. Monday => 1 (ISO-8601)
     */
    function schoolTimingWeekdayMap(): array
    {
        return [
            'Monday'    => 1,
            'Tuesday'   => 2,
            'Wednesday' => 3,
            'Thursday'  => 4,
            'Friday'    => 5,
            'Saturday'  => 6,
            'Sunday'    => 7,
        ];
    }
}

if (! function_exists('schoolTimingsHasCampusIdColumn')) {
    function schoolTimingsHasCampusIdColumn(): bool
    {
        static $has = null;
        if ($has !== null) {
            return $has;
        }

        try {
            $db   = \Config\Database::connect();
            $has  = $db->tableExists('school_timings')
                && in_array('campus_id', $db->getFieldNames('school_timings'), true);
        } catch (\Throwable $e) {
            $has = false;
        }

        return $has;
    }
}

if (! function_exists('dedupeSchoolTimingRows')) {
    /**
     * Keep one row per (cls_sec_id, dayname); prefer working days, then latest id.
     *
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    function dedupeSchoolTimingRows(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        $grouped = [];
        foreach ($rows as $row) {
            $key = (int) ($row['cls_sec_id'] ?? 0) . '|' . (string) ($row['dayname'] ?? '');
            $grouped[$key][] = $row;
        }

        $out = [];
        foreach ($grouped as $group) {
            usort($group, static function (array $a, array $b): int {
                $aWorking = isSchoolTimingWorkingDay($a['checkin_timing'] ?? null, $a['checkout_timing'] ?? null) ? 1 : 0;
                $bWorking = isSchoolTimingWorkingDay($b['checkin_timing'] ?? null, $b['checkout_timing'] ?? null) ? 1 : 0;
                if ($aWorking !== $bWorking) {
                    return $bWorking <=> $aWorking;
                }

                foreach (['id', 'st_id', 'school_timing_id', 'timing_id'] as $pk) {
                    if (isset($a[$pk], $b[$pk])) {
                        return ((int) $b[$pk]) <=> ((int) $a[$pk]);
                    }
                }

                return 0;
            });
            $out[] = $group[0];
        }

        return $out;
    }
}

if (! function_exists('getSchoolTimingsForSections')) {
    /**
     * Batch-load school timings for sections (no type_id).
     *
     * @param list<int> $clsSecIds
     * @return list<array<string, mixed>>
     */
    function getSchoolTimingsForSections(array $clsSecIds, ?int $campusId = null): array
    {
        $clsSecIds = array_values(array_unique(array_filter(array_map('intval', $clsSecIds))));
        if ($clsSecIds === []) {
            return [];
        }

        $db = \Config\Database::connect();
        $select = 'cls_sec_id, dayname, checkin_timing, checkout_timing';
        if (schoolTimingsHasCampusIdColumn()) {
            $select .= ', campus_id';
        }

        $builder = $db->table('school_timings')
            ->select($select, false)
            ->whereIn('cls_sec_id', $clsSecIds);

        if ($campusId !== null && $campusId > 0 && schoolTimingsHasCampusIdColumn()) {
            $builder->groupStart()
                ->where('campus_id', $campusId)
                ->orWhere('campus_id IS NULL', null, false)
                ->orWhere('campus_id', 0)
                ->groupEnd();
        }

        try {
            $rows = $builder->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'getSchoolTimingsForSections failed: {msg}', ['msg' => $e->getMessage()]);

            $rows = $db->table('school_timings')
                ->select('cls_sec_id, dayname, checkin_timing, checkout_timing', false)
                ->whereIn('cls_sec_id', $clsSecIds)
                ->get()
                ->getResultArray();
        }

        return dedupeSchoolTimingRows($rows);
    }
}

if (! function_exists('getSchoolTimingForSectionDay')) {
    /**
     * Single section/day timing row, or null if not configured.
     */
    function getSchoolTimingForSectionDay(int $clsSecId, string $dayName, ?int $campusId = null): ?array
    {
        if ($clsSecId <= 0 || trim($dayName) === '') {
            return null;
        }

        $rows = getSchoolTimingsForSections([$clsSecId], $campusId);
        foreach ($rows as $row) {
            if ((int) ($row['cls_sec_id'] ?? 0) === $clsSecId && ($row['dayname'] ?? '') === $dayName) {
                return $row;
            }
        }

        return null;
    }
}

if (! function_exists('buildAllowedWorkingDaysMap')) {
    /**
     * @param list<array<string, mixed>> $timingsRows
     * @return array<int, array<string, true>>
     */
    function buildAllowedWorkingDaysMap(array $timingsRows): array
    {
        $map = [];
        foreach ($timingsRows as $row) {
            if (! isSchoolTimingWorkingDay($row['checkin_timing'] ?? null, $row['checkout_timing'] ?? null)) {
                continue;
            }
            $clsSecId = (int) ($row['cls_sec_id'] ?? 0);
            $dayname  = (string) ($row['dayname'] ?? '');
            if ($clsSecId > 0 && $dayname !== '') {
                $map[$clsSecId][$dayname] = true;
            }
        }

        return $map;
    }
}

if (! function_exists('isWorkingDayForSection')) {
    function isWorkingDayForSection(int $clsSecId, string $dayName, array $allowedMap): bool
    {
        return ! empty($allowedMap[$clsSecId][$dayName]);
    }
}

if (! function_exists('isSchoolTimingOffDay')) {
    /**
     * Confirmed off day: row exists and check-in equals check-out.
     */
    function isSchoolTimingOffDay(?array $timingRow): bool
    {
        if ($timingRow === null) {
            return false;
        }

        $checkin  = $timingRow['checkin_timing'] ?? null;
        $checkout = $timingRow['checkout_timing'] ?? null;

        if ($checkin === null || $checkout === null || trim((string) $checkin) === '' || trim((string) $checkout) === '') {
            return false;
        }

        return ! isSchoolTimingWorkingDay($checkin, $checkout);
    }
}

if (! function_exists('getWorkingWeekdayNumbersForSection')) {
    /**
     * ISO weekday numbers (1=Mon … 7=Sun) configured as working for a section.
     *
     * @return list<int>
     */
    function getWorkingWeekdayNumbersForSection(int $clsSecId, int $campusId): array
    {
        $rows   = getSchoolTimingsForSections([$clsSecId], $campusId);
        $map    = buildAllowedWorkingDaysMap($rows);
        $dayMap = schoolTimingWeekdayMap();
        $nums   = [];

        foreach ($map[$clsSecId] ?? [] as $dayName => $_) {
            if (isset($dayMap[$dayName])) {
                $nums[] = $dayMap[$dayName];
            }
        }

        sort($nums);

        return $nums;
    }
}

if (! function_exists('isSectionWorkingOnDay')) {
    function isSectionWorkingOnDay(int $clsSecId, string $dayName, int $campusId): bool
    {
        $row = getSchoolTimingForSectionDay($clsSecId, $dayName, $campusId);
        if ($row === null) {
            return false;
        }

        return isSchoolTimingWorkingDay($row['checkin_timing'] ?? null, $row['checkout_timing'] ?? null);
    }
}

if (! function_exists('ensureDefaultBillTypeId')) {
    /**
     * Campus billing uses a single implicit bill type (no admin "Bill Type" setup).
     */
    function ensureDefaultBillTypeId(): int
    {
        $db = \Config\Database::connect();
        $row = $db->table('bill_type')->orderBy('bill_type_id', 'ASC')->limit(1)->get()->getRowArray();

        if ($row !== null) {
            return (int) $row['bill_type_id'];
        }

        $userId = (int) (session('member_userid') ?? 0);
        $db->table('bill_type')->insert([
            'bill_type_name'  => 'Campus Bill',
            'bill_type_detail'=> '',
            'user_id'         => $userId,
            'created_date'    => date('Y-m-d'),
        ]);

        return (int) $db->insertID();
    }
}