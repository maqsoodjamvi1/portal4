<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\ReportCsvExport;
use CodeIgniter\HTTP\ResponseInterface;

class ClassSectionStrengthReport extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db      = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url', 'server']);
        check_permission('admin-students');
    }

    public function index()
    {
        $campusId  = (int) $this->session->get('member_campusid');
        $sessionId = (int) $this->session->get('member_sessionid');
        $systemId  = (int) ($this->session->get('member_systemid') ?? 0);

        $sessionsQuery = $this->db->table('academic_session');
        if ($systemId > 0) {
            $sessionsQuery->where('system_id', $systemId);
        }
        $sessions = $sessionsQuery
            ->orderBy('start_date', 'DESC')
            ->get()
            ->getResultArray();

        return view('admin/class_section_strength_report', [
            'sessions'         => $sessions,
            'currentSessionId' => $sessionId,
            'campusId'         => $campusId,
        ]);
    }

    public function data(): ResponseInterface
    {
        $viewMode  = strtolower(trim((string) $this->request->getPost('view_mode')));
        $sessionId = (int) $this->request->getPost('session_id');
        $campusId  = (int) $this->session->get('member_campusid');

        if (! in_array($viewMode, ['class', 'section'], true)) {
            return $this->response->setBody(
                '<div class="alert alert-warning mb-0">Please select a valid view type.</div>'
            );
        }

        if ($sessionId <= 0) {
            return $this->response->setBody(
                '<div class="alert alert-warning mb-0">Please select an academic session.</div>'
            );
        }

        if ($campusId <= 0) {
            return $this->response->setBody(
                '<div class="alert alert-danger mb-0">Campus is not set in your session.</div>'
            );
        }

        $sessionRow = $this->db->table('academic_session')
            ->where('session_id', $sessionId)
            ->get()
            ->getRow();

        if (! $sessionRow) {
            return $this->response->setBody(
                '<div class="alert alert-danger mb-0">Invalid academic session selected.</div>'
            );
        }

        $campusRow = $this->db->table('campus')
            ->select('campus_name')
            ->where('campus_id', $campusId)
            ->get()
            ->getRow();

        $sectionRows = $this->buildSectionStrengthRows($campusId, $sessionId);

        if ($sectionRows === []) {
            return $this->response->setBody(
                '<div class="alert alert-info mb-0">No active class sections found for this campus.</div>'
            );
        }

        $sessionLabel = trim((string) ($sessionRow->session_name ?? 'Session ' . $sessionId));
        $campusLabel  = trim((string) ($campusRow->campus_name ?? ''));
        $viewLabel    = $viewMode === 'class' ? 'Class Wise Strength' : 'Class Section Wise Strength';
        $printedAt    = date('d M Y, h:i A');

        ob_start();

        if ($viewMode === 'class') {
            $this->renderClassWiseTable($this->aggregateByClass($sectionRows));
        } else {
            $this->renderSectionWiseTable($sectionRows);
        }

        $tableHtml = ob_get_clean();

        $html = view('admin/partials/class_section_strength_report_result', [
            'viewLabel'    => $viewLabel,
            'sessionLabel' => $sessionLabel,
            'campusLabel'  => $campusLabel,
            'printedAt'    => $printedAt,
            'tableHtml'    => $tableHtml,
        ]);

        return $this->response->setBody($html);
    }

    public function exportCsv(): ResponseInterface
    {
        $sessionId = (int) $this->request->getPost('session_id');
        $campusId  = (int) $this->session->get('member_campusid');
        $viewMode  = strtolower(trim((string) $this->request->getPost('view_mode')));

        if ($sessionId <= 0 || $campusId <= 0) {
            return $this->response->setStatusCode(400)->setBody('Invalid session or campus.');
        }

        if (! in_array($viewMode, ['class', 'section'], true)) {
            $viewMode = 'section';
        }

        $sectionRows = $this->buildSectionStrengthRows($campusId, $sessionId);
        $csvRows     = [];

        if ($viewMode === 'class') {
            foreach ($this->aggregateByClass($sectionRows) as $row) {
                $csvRows[] = [
                    $row['class_name'] ?? '',
                    (string) ($row['male'] ?? 0),
                    (string) ($row['female'] ?? 0),
                    (string) ($row['other'] ?? 0),
                    (string) ($row['total'] ?? 0),
                ];
            }
            $headers = ['Class', 'Male', 'Female', 'Other', 'Total'];
        } else {
            foreach ($sectionRows as $row) {
                $csvRows[] = [
                    ($row['class_name'] ?? '') . ' - ' . ($row['section_name'] ?? ''),
                    (string) ($row['male'] ?? 0),
                    (string) ($row['female'] ?? 0),
                    (string) ($row['other'] ?? 0),
                    (string) ($row['total'] ?? 0),
                ];
            }
            $headers = ['Class Section', 'Male', 'Female', 'Other', 'Total'];
        }

        return ReportCsvExport::downloadResponse(
            $this->response,
            'class-section-strength-' . $sessionId . '.csv',
            $headers,
            $csvRows
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildSectionStrengthRows(int $campusId, int $sessionId): array
    {
        $sections = $this->db->table('class_section cs')
            ->select(
                'cs.cls_sec_id,
                 cs.class_id,
                 cs.section_id,
                 COALESCE(NULLIF(c.class_short_name, ""), c.class_name) AS class_name,
                 COALESCE(NULLIF(sec.short_name, ""), sec.section_name) AS section_name',
                false
            )
            ->join('classes c', 'c.class_id = cs.class_id', 'inner')
            ->join('sections sec', 'sec.section_id = cs.section_id', 'inner')
            ->where('cs.campus_id', $campusId)
            ->where('cs.status', 1)
            ->orderBy('c.class_id', 'ASC')
            ->orderBy('sec.section_id', 'ASC')
            ->get()
            ->getResultArray();

        if ($sections === []) {
            return [];
        }

        $clsSecIds = array_map(static fn(array $row): int => (int) ($row['cls_sec_id'] ?? 0), $sections);

        $countRows = $this->db->table('student_class sc')
            ->select(
                'sc.cls_sec_id,
                 LOWER(TRIM(s.gender)) AS gender,
                 COUNT(DISTINCT s.student_id) AS cnt',
                false
            )
            ->join('students s', 's.student_id = sc.student_id', 'inner')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'inner')
            ->whereIn('sc.cls_sec_id', $clsSecIds)
            ->where('sc.session_id', $sessionId)
            ->where('sc.status', 1)
            ->where('s.campus_id', $campusId)
            ->where('s.status', 1)
            ->where('cs.campus_id', $campusId)
            ->where('cs.status', 1)
            ->groupBy('sc.cls_sec_id, LOWER(TRIM(s.gender))')
            ->get()
            ->getResultArray();

        $countsBySection = [];
        foreach ($countRows as $row) {
            $clsSecId = (int) ($row['cls_sec_id'] ?? 0);
            if ($clsSecId <= 0) {
                continue;
            }

            if (! isset($countsBySection[$clsSecId])) {
                $countsBySection[$clsSecId] = ['male' => 0, 'female' => 0, 'other' => 0];
            }

            $gender = $this->normalizeGender((string) ($row['gender'] ?? ''));
            $count  = (int) ($row['cnt'] ?? 0);
            $countsBySection[$clsSecId][$gender] += $count;
        }

        $rows = [];
        foreach ($sections as $section) {
            $clsSecId = (int) ($section['cls_sec_id'] ?? 0);
            $counts   = $countsBySection[$clsSecId] ?? ['male' => 0, 'female' => 0, 'other' => 0];
            $male     = (int) $counts['male'];
            $female   = (int) $counts['female'];
            $other    = (int) $counts['other'];
            $total    = $male + $female + $other;

            $rows[] = [
                'cls_sec_id'   => $clsSecId,
                'class_id'     => (int) ($section['class_id'] ?? 0),
                'section_id'   => (int) ($section['section_id'] ?? 0),
                'class_name'   => (string) ($section['class_name'] ?? ''),
                'section_name' => (string) ($section['section_name'] ?? ''),
                'male'         => $male,
                'female'       => $female,
                'other'        => $other,
                'total'        => $total,
            ];
        }

        return $rows;
    }

    /**
     * @param list<array<string, mixed>> $sectionRows
     * @return list<array<string, mixed>>
     */
    private function aggregateByClass(array $sectionRows): array
    {
        $byClass = [];

        foreach ($sectionRows as $row) {
            $classId = (int) ($row['class_id'] ?? 0);
            if ($classId <= 0) {
                continue;
            }

            if (! isset($byClass[$classId])) {
                $byClass[$classId] = [
                    'class_id'   => $classId,
                    'class_name' => (string) ($row['class_name'] ?? ''),
                    'male'       => 0,
                    'female'     => 0,
                    'other'      => 0,
                    'total'      => 0,
                ];
            }

            $byClass[$classId]['male']   += (int) $row['male'];
            $byClass[$classId]['female'] += (int) $row['female'];
            $byClass[$classId]['other']  += (int) $row['other'];
            $byClass[$classId]['total']  += (int) $row['total'];
        }

        return array_values($byClass);
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    private function renderClassWiseTable(array $rows): void
    {
        $totals = ['male' => 0, 'female' => 0, 'other' => 0, 'total' => 0];
        ?>
        <table class="table table-bordered table-sm strength-report-table mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-center col-num">#</th>
                    <th>Class</th>
                    <th class="text-end">Boys</th>
                    <th class="text-end">Girls</th>
                    <th class="text-end">Other</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $index => $row): ?>
                    <?php
                    $totals['male']   += (int) $row['male'];
                    $totals['female'] += (int) $row['female'];
                    $totals['other']  += (int) $row['other'];
                    $totals['total']  += (int) $row['total'];
                    ?>
                    <tr>
                        <td class="text-center"><?= $index + 1 ?></td>
                        <td><?= esc($row['class_name']) ?></td>
                        <td class="text-end"><?= number_format((int) $row['male']) ?></td>
                        <td class="text-end"><?= number_format((int) $row['female']) ?></td>
                        <td class="text-end"><?= number_format((int) $row['other']) ?></td>
                        <td class="text-end fw-bold"><?= number_format((int) $row['total']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="bg-light fw-bold">
                    <td colspan="2" class="text-end">Grand Total</td>
                    <td class="text-end"><?= number_format($totals['male']) ?></td>
                    <td class="text-end"><?= number_format($totals['female']) ?></td>
                    <td class="text-end"><?= number_format($totals['other']) ?></td>
                    <td class="text-end"><?= number_format($totals['total']) ?></td>
                </tr>
            </tfoot>
        </table>
        <?php
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    private function renderSectionWiseTable(array $rows): void
    {
        $totals = ['male' => 0, 'female' => 0, 'other' => 0, 'total' => 0];
        ?>
        <table class="table table-bordered table-sm strength-report-table mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-center col-num">#</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th class="text-end">Boys</th>
                    <th class="text-end">Girls</th>
                    <th class="text-end">Other</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $serial      = 0;
                $prevClassId = null;
                $classTotals = null;

                foreach ($rows as $row):
                    $classId = (int) ($row['class_id'] ?? 0);

                    if ($prevClassId !== null && $classId !== $prevClassId && $classTotals !== null):
                        ?>
                        <tr class="table-secondary fw-bold">
                            <td colspan="3" class="text-end">Class Total</td>
                            <td class="text-end"><?= number_format((int) $classTotals['male']) ?></td>
                            <td class="text-end"><?= number_format((int) $classTotals['female']) ?></td>
                            <td class="text-end"><?= number_format((int) $classTotals['other']) ?></td>
                            <td class="text-end"><?= number_format((int) $classTotals['total']) ?></td>
                        </tr>
                        <?php
                        $classTotals = null;
                    endif;

                    if ($classTotals === null) {
                        $classTotals = ['male' => 0, 'female' => 0, 'other' => 0, 'total' => 0];
                    }

                    $serial++;
                    $male   = (int) $row['male'];
                    $female = (int) $row['female'];
                    $other  = (int) $row['other'];
                    $total  = (int) $row['total'];

                    $classTotals['male']   += $male;
                    $classTotals['female'] += $female;
                    $classTotals['other']  += $other;
                    $classTotals['total']  += $total;

                    $totals['male']   += $male;
                    $totals['female'] += $female;
                    $totals['other']  += $other;
                    $totals['total']  += $total;
                    ?>
                    <tr>
                        <td class="text-center"><?= $serial ?></td>
                        <td><?= esc($row['class_name']) ?></td>
                        <td><?= esc($row['section_name']) ?></td>
                        <td class="text-end"><?= number_format($male) ?></td>
                        <td class="text-end"><?= number_format($female) ?></td>
                        <td class="text-end"><?= number_format($other) ?></td>
                        <td class="text-end fw-bold"><?= number_format($total) ?></td>
                    </tr>
                    <?php
                    $prevClassId = $classId;
                endforeach;

                if ($classTotals !== null):
                    ?>
                    <tr class="table-secondary fw-bold">
                        <td colspan="3" class="text-end">Class Total</td>
                        <td class="text-end"><?= number_format((int) $classTotals['male']) ?></td>
                        <td class="text-end"><?= number_format((int) $classTotals['female']) ?></td>
                        <td class="text-end"><?= number_format((int) $classTotals['other']) ?></td>
                        <td class="text-end"><?= number_format((int) $classTotals['total']) ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr class="bg-light fw-bold">
                    <td colspan="3" class="text-end">Grand Total</td>
                    <td class="text-end"><?= number_format($totals['male']) ?></td>
                    <td class="text-end"><?= number_format($totals['female']) ?></td>
                    <td class="text-end"><?= number_format($totals['other']) ?></td>
                    <td class="text-end"><?= number_format($totals['total']) ?></td>
                </tr>
            </tfoot>
        </table>
        <?php
    }

    private function normalizeGender(string $gender): string
    {
        $value = strtolower(trim($gender));

        if (in_array($value, ['male', 'm', 'boy'], true)) {
            return 'male';
        }

        if (in_array($value, ['female', 'f', 'girl'], true)) {
            return 'female';
        }

        return 'other';
    }
}
