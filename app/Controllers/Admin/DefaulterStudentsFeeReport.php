<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DefaulterStudentsFeeReport extends BaseController
{
    protected $db;

    public function __construct()
    {
        helper(['form', 'url', 'text']);
        $this->db = \Config\Database::connect();
        check_permission('admin-defaulter-student-fee-report');
    }

    public function index()
    {
        $schoolinfo = getSchoolInfo();
        $campusid   = (int) session()->get('member_campusid');

        $rows = $this->db->table('class_section cs')
            ->select(
                'cs.cls_sec_id AS section_id, CONCAT(COALESCE(c.class_short_name, c.class_name), " (", s.section_name, ")") AS sectionclassname',
                false
            )
            ->join('classes c', 'c.class_id = cs.class_id', 'inner')
            ->join('sections s', 's.section_id = cs.section_id', 'inner')
            ->where('cs.campus_id', $campusid)
            ->where('cs.status', 1)
            ->orderBy('c.class_name', 'ASC')
            ->orderBy('s.section_name', 'ASC')
            ->get()
            ->getResultArray();

        $data['sectionsclassinfo'] = $rows;

        $data['termsinfo'] = $this->db->table('terms')->get()->getResult();

        $data['academic_session'] = $this->db->table('academic_session')
            ->where('system_id', $schoolinfo->system_id)
            ->get()
            ->getResult();

        return view('admin/defaulter_students_fee_report', $data);
    }

    public function data()
    {
        $session_id = (int) session()->get('member_sessionid');
        $campus_id  = (int) session()->get('member_campusid');
        $schoolinfo = getSchoolInfo();
        $cls_sec_id = $this->request->getPost('cls_sec_id');

        $academic_session = $this->db->table('academic_session')
            ->where('session_id', $session_id)
            ->where('system_id', $schoolinfo->system_id)
            ->get()
            ->getRow();

        if (!$academic_session) {
            return $this->response->setBody(
                '<div class="alert alert-danger">Academic session not found for the selected session.</div>'
            );
        }

        $feeTypesInfo = $this->db->table('fee_type')
            ->where('s_flag', 1)
            ->where('status', 1)
            ->where('system_id', $schoolinfo->system_id)
            ->orderBy('fee_type_name', 'ASC')
            ->get()
            ->getResult();

        if ($cls_sec_id === 'all') {
            $studentClass = $this->db->query(
                'SELECT sc.student_id, sc.cls_sec_id FROM student_class sc
                INNER JOIN students st ON st.student_id = sc.student_id AND st.status = 1 AND st.campus_id = ?
                WHERE sc.session_id = ?
                ORDER BY sc.cls_sec_id, sc.student_id',
                [$campus_id, $session_id]
            )->getResult();
        } else {
            $studentClass = $this->db->query(
                'SELECT sc.student_id, sc.cls_sec_id FROM student_class sc
                INNER JOIN students st ON st.student_id = sc.student_id AND st.status = 1 AND st.campus_id = ?
                WHERE sc.session_id = ? AND sc.cls_sec_id = ?
                ORDER BY sc.cls_sec_id, sc.student_id',
                [$campus_id, $session_id, $cls_sec_id]
            )->getResult();
        }

        $monthList = $this->buildSessionMonthList($academic_session->start_date, $academic_session->end_date);
        if ($monthList === []) {
            return $this->response->setBody(
                '<div class="alert alert-warning">Month range is empty. Check academic session start/end dates.</div>'
            );
        }

        $studentIds = [];
        foreach ($studentClass as $row) {
            $studentIds[(int) $row->student_id] = true;
        }
        $studentIds = array_keys($studentIds);

        if ($studentIds === []) {
            return $this->response->setBody(
                '<div class="alert alert-info">No active students found for this filter.</div>'
            );
        }

        $classSectionCache = [];

        $byStudentFeeType = $this->fetchUnpaidByStudentAndFeeType($monthList, $studentIds);
        $dueMonthsByStudent = $this->fetchDueMonthsByStudent($monthList, $studentIds);
        $studentProfiles = $this->fetchStudentProfiles($studentIds);

        $footerByFeeType = $this->fetchFooterTotalsByFeeType($monthList, $session_id, $campus_id, $cls_sec_id);

        $headerRep = reportHeader();
        $html      = $headerRep;
        $html .= '<div class="reportHeading">Fee Defaulters Report (by fee type)</div>';
        $html .= '<table class="resultReport table table-bordered table-sm"><thead><tr>';
        $html .= '<th style="width:220px;">Student</th><th style="width:115px;">Due period</th>';

        foreach ($feeTypesInfo as $feeType) {
            $html .= '<th>' . esc($feeType->fee_type_name) . '</th>';
        }
        $html .= '<th>Total</th></tr></thead><tbody>';

        $studentTotalSum = 0;
        $nCount          = 1;
        $seenStudent     = [];

        foreach ($studentClass as $students) {
            $sid = (int) $students->student_id;
            if (isset($seenStudent[$sid])) {
                continue;
            }
            $seenStudent[$sid] = true;

            $rowTotal = 0;
            foreach ($feeTypesInfo as $ft) {
                $fid = (int) $ft->fee_type_id;
                $rowTotal += (float) ($byStudentFeeType[$sid][$fid] ?? 0);
            }

            if ($rowTotal <= 0) {
                continue;
            }

            $stu = $studentProfiles[$sid] ?? null;
            if (!$stu) {
                continue;
            }

            if (!isset($classSectionCache[$students->cls_sec_id])) {
                $classSectionCache[$students->cls_sec_id] = getClassSection($students->cls_sec_id);
            }
            $class_info = $classSectionCache[$students->cls_sec_id];
            $classLabel = $class_info['sectionclassname'] ?? '';

            $html .= '<tr><th style="text-align:left;padding:4px;">' . $nCount . '. '
                . esc(trim($stu['first_name'] . ' ' . $stu['last_name']))
                . ' C/O <br>'
                . esc(trim($stu['parent_f_name'] ?? ''))
                . ' ' . esc($classLabel)
                . '</th><td>';

            $monthLabels = $dueMonthsByStudent[$sid] ?? [];
            $html .= '<div style="color:#000;">' . esc(implode(', ', $monthLabels)) . '</div></td>';

            foreach ($feeTypesInfo as $feeType) {
                $fid    = (int) $feeType->fee_type_id;
                $amount = (float) ($byStudentFeeType[$sid][$fid] ?? 0);
                $html .= '<td><div style="color:#000;">'
                    . ($amount != 0.0 ? number_format($amount, 0) . '/-' : '0/-')
                    . '</div></td>';
            }

            $html .= '<td><strong>' . number_format($rowTotal, 0) . '/-</strong></td></tr>';

            $studentTotalSum += $rowTotal;
            $nCount++;
        }

        if ($nCount === 1) {
            $colspan = 3 + count($feeTypesInfo);
            $html .= '<tr><td class="text-center text-muted" colspan="' . $colspan
                . '">No fee defaulters for this selection (no unpaid challans in the session months).</td></tr>';
        }

        $html .= '</tbody><tfoot><tr><td></td><th>Total</th>';

        foreach ($feeTypesInfo as $feeType) {
            $fid   = (int) $feeType->fee_type_id;
            $tot   = (float) ($footerByFeeType[$fid] ?? 0);
            $html .= '<th>' . ($tot != 0.0 ? number_format($tot, 0) . '/-' : '0/-') . '</th>';
        }

        $html .= '<th>' . number_format($studentTotalSum, 0) . '/-</th></tr></tfoot></table>';

        return $this->response->setBody($html)->setContentType('text/html');
    }

    /**
     * Calendar months in the academic session, in every fee_month shape used in `fee_chalan`.
     * Fee challan UI stores months as YYYY-MM; older rows may use MM/YYYY — include both for WHERE IN.
     *
     * @return list<string>
     */
    private function buildSessionMonthList(string $startDate, string $endDate): array
    {
        $start = new \DateTime($startDate);
        $start->modify('first day of this month');
        $end = new \DateTime($endDate);
        $end->modify('first day of next month');
        $interval = \DateInterval::createFromDateString('1 month');
        $period   = new \DatePeriod($start, $interval, $end);

        $variants = [];
        foreach ($period as $dt) {
            $variants[$dt->format('Y-m')] = true;
            $variants[$dt->format('Y-n')] = true;
            $variants[$dt->format('m/Y')] = true;
        }

        return array_keys($variants);
    }

    /**
     * @param list<string> $monthList
     * @param list<int>    $studentIds
     *
     * @return array<int, array<int, float>> [student_id][fee_type_id] => amount
     */
    private function fetchUnpaidByStudentAndFeeType(array $monthList, array $studentIds): array
    {
        $result = [];
        foreach (array_chunk($studentIds, 500) as $chunk) {
            $rows = $this->db->table('fee_chalan')
                ->select(
                    'student_id, fee_type_id, SUM(COALESCE(amount, 0) - COALESCE(discount, 0)) AS total',
                    false
                )
                ->where('status', 'unpaid')
                ->whereIn('fee_month', $monthList)
                ->whereIn('student_id', $chunk)
                ->groupBy('student_id, fee_type_id', false)
                ->get()
                ->getResult();

            foreach ($rows as $r) {
                $sid = (int) $r->student_id;
                $fid = (int) $r->fee_type_id;
                if (!isset($result[$sid])) {
                    $result[$sid] = [];
                }
                $result[$sid][$fid] = (float) $r->total;
            }
        }

        return $result;
    }

    /**
     * Distinct calendar months with unpaid balance (label includes year if session spans multiple years).
     *
     * @param list<string> $monthList
     * @param list<int>    $studentIds
     *
     * @return array<int, list<string>>
     */
    private function fetchDueMonthsByStudent(array $monthList, array $studentIds): array
    {
        $result = [];
        foreach (array_chunk($studentIds, 500) as $chunk) {
            $rows = $this->db->table('fee_chalan')
                ->select(
                    'student_id, fee_month, SUM(COALESCE(amount, 0) - COALESCE(discount, 0)) AS total',
                    false
                )
                ->where('status', 'unpaid')
                ->whereIn('fee_month', $monthList)
                ->whereIn('student_id', $chunk)
                ->groupBy('student_id, fee_month', false)
                ->get()
                ->getResult();

            foreach ($rows as $r) {
                $amt = (float) $r->total;
                if ($amt <= 0) {
                    continue;
                }
                $sid = (int) $r->student_id;
                $fm = (string) $r->fee_month;
                $sortKey = $this->feeMonthSortKey($fm);
                $label = $this->feeMonthLabel($fm);
                if (!isset($result[$sid])) {
                    $result[$sid] = [];
                }
                $result[$sid][$sortKey] = $label;
            }
        }

        foreach ($result as $sid => $map) {
            ksort($map);
            $result[$sid] = array_values($map);
        }

        return $result;
    }

    /**
     * @param list<int> $studentIds
     *
     * @return array<int, array<string, mixed>>
     */
    private function fetchStudentProfiles(array $studentIds): array
    {
        $map = [];
        foreach (array_chunk($studentIds, 500) as $chunk) {
            $rows = $this->db->table('students s')
                ->select('s.student_id, s.first_name, s.last_name, p.f_name AS parent_f_name')
                ->join('parents p', 'p.parent_id = s.parent_id', 'inner')
                ->whereIn('s.student_id', $chunk)
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $map[(int) $row['student_id']] = $row;
            }
        }

        return $map;
    }

    /**
     * Cohort totals per fee type (same scope as original: all students in filter with unpaid challans).
     *
     * @return array<int, float> fee_type_id => total
     */
    private function fetchFooterTotalsByFeeType(
        array $monthList,
        int $session_id,
        int $campus_id,
        $cls_sec_id
    ): array {
        $monthPlaceholders = implode(',', array_fill(0, count($monthList), '?'));

        if ($cls_sec_id === 'all') {
            $sql = "SELECT fc.fee_type_id,
                    SUM(COALESCE(fc.amount, 0) - COALESCE(fc.discount, 0)) AS total
                FROM fee_chalan fc
                WHERE fc.status = 'unpaid'
                AND fc.fee_month IN ({$monthPlaceholders})
                AND fc.student_id IN (
                    SELECT DISTINCT sc.student_id FROM student_class sc
                    INNER JOIN students st ON st.student_id = sc.student_id AND st.status = 1 AND st.campus_id = ?
                    WHERE sc.session_id = ?
                )
                GROUP BY fc.fee_type_id";
            $binds = array_merge($monthList, [$campus_id, $session_id]);
        } else {
            $sql = "SELECT fc.fee_type_id,
                    SUM(COALESCE(fc.amount, 0) - COALESCE(fc.discount, 0)) AS total
                FROM fee_chalan fc
                WHERE fc.status = 'unpaid'
                AND fc.fee_month IN ({$monthPlaceholders})
                AND fc.student_id IN (
                    SELECT DISTINCT sc.student_id FROM student_class sc
                    INNER JOIN students st ON st.student_id = sc.student_id AND st.status = 1 AND st.campus_id = ?
                    WHERE sc.session_id = ? AND sc.cls_sec_id = ?
                )
                GROUP BY fc.fee_type_id";
            $binds = array_merge($monthList, [$campus_id, $session_id, $cls_sec_id]);
        }

        $rows = $this->db->query($sql, $binds)->getResult();
        $out  = [];
        foreach ($rows as $r) {
            $out[(int) $r->fee_type_id] = (float) $r->total;
        }

        return $out;
    }

    private function feeMonthLabel(string $feeMonth): string
    {
        $feeMonth = trim($feeMonth);
        if ($feeMonth === '') {
            return '';
        }

        if (preg_match('/^(\d{4})-(\d{1,2})$/', $feeMonth, $m)) {
            $dt = \DateTime::createFromFormat(
                '!Y-m-d',
                sprintf('%s-%02d-01', $m[1], (int) $m[2])
            );

            return $dt ? $dt->format('F Y') : $feeMonth;
        }

        $parts = explode('/', $feeMonth);
        if (count($parts) === 2) {
            $m = str_pad((string) ((int) $parts[0]), 2, '0', STR_PAD_LEFT);
            $y = $parts[1];
            $dt = \DateTime::createFromFormat('m/Y', $m . '/' . $y);

            return $dt ? $dt->format('F Y') : $feeMonth;
        }

        $parts = explode('-', $feeMonth);
        if (count($parts) === 2) {
            if ((int) $parts[0] > 12) {
                $dt = \DateTime::createFromFormat(
                    '!Y-m-d',
                    sprintf('%s-%02d-01', $parts[0], (int) $parts[1])
                );
            } else {
                $dt = \DateTime::createFromFormat(
                    '!Y-m-d',
                    sprintf('%s-%02d-01', $parts[1], (int) $parts[0])
                );
            }

            return $dt ? $dt->format('F Y') : $feeMonth;
        }

        return $feeMonth;
    }

    private function feeMonthSortKey(string $feeMonth): string
    {
        $feeMonth = trim($feeMonth);
        if (preg_match('/^(\d{4})-(\d{1,2})$/', $feeMonth, $m)) {
            return $m[1] . '-' . str_pad((string) ((int) $m[2]), 2, '0', STR_PAD_LEFT);
        }

        $parts = explode('/', $feeMonth);
        if (count($parts) === 2) {
            return $parts[1] . '-' . str_pad((string) ((int) $parts[0]), 2, '0', STR_PAD_LEFT);
        }

        $parts = explode('-', $feeMonth);
        if (count($parts) === 2) {
            if ((int) $parts[0] > 12) {
                return $parts[0] . '-' . str_pad((string) ((int) $parts[1]), 2, '0', STR_PAD_LEFT);
            }

            return $parts[1] . '-' . str_pad((string) ((int) $parts[0]), 2, '0', STR_PAD_LEFT);
        }

        return $feeMonth;
    }
}
