<?php

if (!function_exists('parent_portal_is_safe_return_path')) {
    /**
     * Allow only same-site student/* paths after switch (no open redirect).
     */
    function parent_portal_is_safe_return_path(string $path): bool
    {
        $path = trim(str_replace('\\', '/', $path), '/');
        if ($path === '' || strpos($path, '..') !== false) {
            return false;
        }

        return (bool) preg_match('#^student/[a-zA-Z0-9/_\-]+(\?[a-zA-Z0-9_=&+%.\-]*)?$#', $path);
    }
}

if (! function_exists('parent_portal_build_action_center')) {
    /**
     * @param array{monthly?: float, other?: float, total?: float} $unpaidTotals
     * @param list<mixed> $quizSchedule
     * @param list<mixed> $todayDiary
     *
     * @return list<array{label: string, detail: string, url: string, icon: string, badge: int}>
     */
    function parent_portal_build_action_center(array $unpaidTotals, array $quizSchedule, array $todayDiary): array
    {
        helper('url');
        $items = [];
        $total = (float) ($unpaidTotals['total'] ?? 0);

        if ($total > 0) {
            $items[] = [
                'label'  => lang('ParentPortal.action_unpaid_fees'),
                'detail' => lang('ParentPortal.action_unpaid_fees_detail', [number_format($total, 0)]),
                'url'    => base_url('student/fees'),
                'icon'   => 'fas fa-money-bill-wave',
                'badge'  => 0,
            ];
        }

        $quizCount = is_array($quizSchedule) ? count($quizSchedule) : 0;
        if ($quizCount > 0) {
            $items[] = [
                'label'  => lang('ParentPortal.action_pending_quizzes'),
                'detail' => lang('ParentPortal.action_pending_quizzes_detail', [$quizCount]),
                'url'    => base_url('student/pending-quizzes'),
                'icon'   => 'fas fa-clipboard-list',
                'badge'  => $quizCount,
            ];
        }

        $diaryCount = is_array($todayDiary) ? count($todayDiary) : 0;
        if ($diaryCount > 0) {
            $items[] = [
                'label'  => lang('ParentPortal.action_diary_today'),
                'detail' => lang('ParentPortal.action_diary_today_detail', [$diaryCount]),
                'url'    => base_url('student/classdiary'),
                'icon'   => 'fas fa-book-open',
                'badge'  => $diaryCount,
            ];
        }

        return $items;
    }
}

if (! function_exists('parent_portal_get_notices')) {
    /**
     * Active notices for a student on the parent portal.
     *
     * @return list<array{title: string, body: string, date: string}>
     */
    function parent_portal_get_notices(int $studentId, int $campusId, int $limit = 10): array
    {
        if ($studentId <= 0 || $campusId <= 0) {
            return [];
        }

        $db = \Config\Database::connect();

        try {
            $rows = $db->table('student_notices sn')
                ->select('n.notice_name, n.notice_date, n.notice_detail')
                ->join('notices n', 'n.notice_id = sn.notice_id')
                ->where('sn.std_id', $studentId)
                ->where('n.campus_id', $campusId)
                ->where('n.status', 1)
                ->orderBy('n.notice_date', 'DESC')
                ->limit(max(1, $limit))
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'parent_portal_get_notices: {msg}', ['msg' => $e->getMessage()]);

            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'title' => (string) ($row['notice_name'] ?? ''),
                'body'  => (string) ($row['notice_detail'] ?? ''),
                'date'  => (string) ($row['notice_date'] ?? ''),
            ];
        }

        return $out;
    }
}

if (! function_exists('parent_portal_hifz_summary')) {
    /**
     * Compact Hifz summary for parent dashboard.
     *
     * @return array<string, mixed>|null
     */
    function parent_portal_hifz_summary(int $studentId): ?array
    {
        helper('hifz');
        if (! campusHifzEnabled() || studentHifzActive($studentId) === null) {
            return null;
        }

        $sessionId = hifzStudentSessionId($studentId);
        if ($sessionId <= 0) {
            return null;
        }

        $data = (new \App\Libraries\HifzReportService())->getPortalData($studentId, $sessionId, 14);
        if (empty($data['enrolled'])) {
            return null;
        }

        return [
            'section'      => $data['section_name'] ?? '',
            'current_para' => $data['current_para_label'] ?? '',
            'teacher'      => $data['teacher_name'] ?? '',
            'url'          => base_url('student/dashboard/section/hifz'),
        ];
    }
}

if (!function_exists('parent_portal_get_children')) {
    /**
     * Children rows for parent portal (same shape as Dashboard::getChildrenWithCurrentClass).
     *
     * @return list<array<string, mixed>>
     */
    function parent_portal_get_children(int $parentId): array
    {
        $db = \Config\Database::connect();

        $sql = "
        SELECT 
            s.student_id,
            s.first_name,
            s.last_name,
            s.date_of_birth,
            s.profile_photo,
            s.reg_no,
            c.class_name,
            sec.section_name,
            sc.cls_sec_id
        FROM students s
        JOIN student_class sc ON sc.student_id = s.student_id AND sc.status = 1
        JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
        JOIN classes c ON c.class_id = cs.class_id
        LEFT JOIN sections sec ON sec.section_id = cs.section_id
        WHERE s.parent_id = ? AND s.status = '1'
        ORDER BY s.first_name ASC
    ";

        $rows = $db->query($sql, [$parentId])->getResultArray();

        $children = [];
        foreach ($rows as $row) {
            $photoFile = $row['profile_photo'] ?? '';
            $photoFile = ltrim((string) $photoFile, '/');

            $photoUrl = getStudentPhotoUrl($row['profile_photo'] ?? '');
            if (!empty($photoFile)) {
                $possiblePaths = [
                    'uploads/' . $photoFile,
                    'student_photos/' . $photoFile,
                    'system-logo/' . $photoFile,
                ];

                foreach ($possiblePaths as $path) {
                    $fullPath = FCPATH . $path;
                    if (file_exists($fullPath)) {
                        $photoUrl = base_url($path);
                        break;
                    }
                }
            }

            $children[] = [
                'student_id'        => (int) $row['student_id'],
                'name'              => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
                'first_name'        => $row['first_name'] ?? '',
                'last_name'         => $row['last_name'] ?? '',
                'reg_no'            => $row['reg_no'] ?? '',
                'class_name'        => $row['class_name'] ?? '',
                'section_name'      => $row['section_name'] ?? '',
                'class_display'     => trim(($row['class_name'] ?? '') . ' ' . ($row['section_name'] ?? '')),
                'profile_photo_url' => $photoUrl,
                'cls_sec_id'        => (int) $row['cls_sec_id'],
                'date_of_birth'     => $row['date_of_birth'] ?? null,
            ];
        }

        return $children;
    }
}
