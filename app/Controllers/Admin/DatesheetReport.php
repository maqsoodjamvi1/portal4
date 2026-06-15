<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DatesheetReport extends BaseController
{
    protected $db;
    protected $session;
    protected $template_data = [];

    /** Max date columns per landscape matrix chunk (incl. sticky section col handled separately). */
    private const MATRIX_CHUNK_SIZE = 12;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url']);
        check_permission('admin-datesheet-report');
    }

    public function index()
    {
        return view('admin/datesheet_report', $this->template_data);
    }

    public function data()
    {
        $examId = (int) $this->request->getPost('exam_id');
        if ($examId <= 0) {
            return $this->response->setBody('<div class="alert alert-warning m-2">Please select an exam.</div>');
        }

        $payload = $this->buildReportPayload($examId);
        if ($payload === null) {
            return $this->response->setBody('<div class="alert alert-info m-2">No papers scheduled for this exam.</div>');
        }

        if (empty($payload['sections'])) {
            return $this->response->setBody('<div class="alert alert-info m-2">No classes/sections have papers in this exam.</div>');
        }

        ob_start();
        $this->renderReportHtml($payload);
        return $this->response->setBody(ob_get_clean());
    }

    public function add()
    {
        check_permission('admin-datesheet-report');
        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');

        $examInfo = $this->db->table('exam')
            ->where('campus_id', $campusid)
            ->where('session_id', $sessionid)
            ->get()->getResult();

        $this->template_data['examInfo'] = $examInfo;

        return view('admin/datesheet_report_edit', $this->template_data);
    }

    /**
     * @return array<string,mixed>|null
     */
    private function buildReportPayload(int $examId): ?array
    {
        $db = $this->db;

        $exam = $db->table('exam')->select('exam_name')->where('eid', $examId)->get()->getRow();
        $examName = $exam->exam_name ?? 'Exam';

        $dates = $db->table('datesheet')
            ->select('exam_date')
            ->where('eid', $examId)
            ->where('exam_date IS NOT NULL', null, false)
            ->groupBy('exam_date')
            ->orderBy('exam_date', 'ASC')
            ->get()->getResultArray();

        if (empty($dates)) {
            return null;
        }

        $dateKeys = array_values(array_unique(array_map(static fn ($r) => $r['exam_date'], $dates)));
        $startDate = reset($dateKeys);
        $endDate   = end($dateKeys);

        $rows = $db->table('datesheet d')
            ->select("
                d.exam_date,
                d.cls_sec_id,
                ss.subject_id,
                subj.subject_short_name AS subj_name,
                subj.subject_name AS subj_full_name
            ", false)
            ->join('section_subjects ss', 'ss.sec_sub_id = d.sec_sub_id', 'left')
            ->join('allsubject subj', 'subj.sid = ss.subject_id', 'left')
            ->where('d.eid', $examId)
            ->whereIn('d.exam_date', $dateKeys)
            ->orderBy('d.exam_date', 'ASC')
            ->orderBy('subj.subject_short_name', 'ASC')
            ->get()->getResult();

        $grid = [];
        foreach ($rows as $r) {
            $cls = (int) $r->cls_sec_id;
            $dt  = (string) $r->exam_date;
            $name = trim($r->subj_name ?? '') ?: trim($r->subj_full_name ?? '') ?: 'Subject';
            $grid[$cls][$dt][] = ['name' => $name];
        }

        $sections = $db->table('class_section cs')
            ->select("
                cs.cls_sec_id,
                CONCAT(COALESCE(NULLIF(c.class_short_name, ''), c.class_name), ' - ', s.section_name) AS sectionclassname
            ", false)
            ->join('classes c', 'c.class_id = cs.class_id', 'inner')
            ->join('sections s', 's.section_id = cs.section_id', 'inner')
            ->where('cs.status', 1)
            ->orderBy('c.class_id', 'ASC')
            ->orderBy('s.section_id', 'ASC')
            ->get()->getResultArray();

        $sections = array_values(array_filter($sections, static function ($s) use ($grid) {
            return !empty($grid[(int) $s['cls_sec_id']] ?? []);
        }));

        $schoolInfo = getSchoolInfo();
        $campusInfo = getCampusInfo();
        $schoolName = trim($schoolInfo->system_name ?? '') ?: 'School';
        $campusLabel = trim($campusInfo->campus_name ?? '');

        return [
            'examName'    => $examName,
            'dateKeys'    => $dateKeys,
            'startDate'   => $startDate,
            'endDate'     => $endDate,
            'grid'        => $grid,
            'sections'    => $sections,
            'schoolName'  => $schoolName,
            'campusLabel' => $campusLabel,
        ];
    }

    /**
     * @param array<string,mixed> $p
     */
    private function renderReportHtml(array $p): void
    {
        $examName    = $p['examName'];
        $dateKeys    = $p['dateKeys'];
        $startDate   = $p['startDate'];
        $endDate     = $p['endDate'];
        $grid        = $p['grid'];
        $sections    = $p['sections'];
        $schoolName  = $p['schoolName'];
        $campusLabel = $p['campusLabel'];

        $periodLabel = date('d M Y', strtotime($startDate)) . ' – ' . date('d M Y', strtotime($endDate));
        $printedAt   = date('d M Y, h:i A');
        ?>
        <div class="ds-toolbar d-flex flex-wrap align-items-center justify-content-between no-print p-2 gap-2">
            <div class="small text-muted">
                <strong><?= esc($examName) ?></strong> &middot; <?= esc($periodLabel) ?>
                &middot; <?= count($sections) ?> class<?= count($sections) === 1 ? '' : 'es' ?>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="btn-group btn-group-sm" role="group" aria-label="Print layout">
                    <button type="button" class="btn btn-outline-secondary active" data-ds-print-mode="list">List (recommended)</button>
                    <button type="button" class="btn btn-outline-secondary" data-ds-print-mode="matrix">Grid</button>
                </div>
                <button id="dsPrintBtn" type="button" class="btn btn-sm btn-dark">
                    <i class="fas fa-print me-1"></i> Print class-wise
                </button>
            </div>
        </div>

        <div class="ds-screen-only">
            <div class="small text-muted px-2 py-1 border border-top-0 bg-light">
                Overview — scroll to compare all classes. Printing uses one A4 landscape page per class.
            </div>
            <div class="ds-overview-wrap">
                <table class="table ds-overview-table mb-0">
                    <thead>
                        <tr>
                            <th class="ds-sticky-sec">Class / Section</th>
                            <?php foreach ($dateKeys as $d): ?>
                                <th class="ds-date-col">
                                    <?= esc(date('D', strtotime($d))) ?><br>
                                    <span><?= esc(date('d-m-y', strtotime($d))) ?></span>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($sections as $sec): ?>
                        <?php $cid = (int) $sec['cls_sec_id']; ?>
                        <tr>
                            <th class="ds-sticky-sec"><?= esc($sec['sectionclassname'] ?? ('#' . $cid)) ?></th>
                            <?php foreach ($dateKeys as $d): ?>
                                <td class="ds-date-col">
                                    <?= $this->renderOverviewCell($grid[$cid][$d] ?? []) ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="dsPrintRoot" class="ds-print-root" data-print-mode="list">
            <?php foreach ($sections as $sec): ?>
                <?php
                    $cid = (int) $sec['cls_sec_id'];
                    $classLabel = $sec['sectionclassname'] ?? ('#' . $cid);
                    $sectionDates = array_values(array_filter(
                        $dateKeys,
                        static fn ($d) => !empty($grid[$cid][$d] ?? [])
                    ));
                ?>
                <div class="ds-print-sheet" data-section-id="<?= $cid ?>">
                    <?= $this->renderPrintHeader($schoolName, $campusLabel, $examName, $classLabel, $periodLabel, $printedAt) ?>

                    <div class="ds-print-layout ds-print-layout-list">
                        <table class="ds-class-table">
                            <thead>
                                <tr>
                                    <th class="ds-col-sn">#</th>
                                    <th class="ds-col-date">Date</th>
                                    <th class="ds-col-day">Day</th>
                                    <th class="ds-col-subj">Subject(s)</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($sectionDates as $i => $d): ?>
                                <tr>
                                    <td class="ds-col-sn"><?= $i + 1 ?></td>
                                    <td class="ds-col-date"><?= esc(date('d-m-Y', strtotime($d))) ?></td>
                                    <td class="ds-col-day"><?= esc(date('l', strtotime($d))) ?></td>
                                    <td class="ds-col-subj"><?= $this->renderPrintSubjects($grid[$cid][$d] ?? []) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="ds-print-layout ds-print-layout-matrix d-none">
                        <?php
                            $chunks = array_chunk($dateKeys, self::MATRIX_CHUNK_SIZE);
                            $chunkTotal = count($chunks);
                        ?>
                        <?php foreach ($chunks as $chunkIndex => $chunk): ?>
                            <?php
                                $manyCols = count($chunk) > 10;
                                $tableClass = 'ds-matrix-table' . ($manyCols ? ' ds-cols-many' : '');
                            ?>
                            <?php if ($chunkTotal > 1): ?>
                                <div class="ds-chunk-note">Dates <?= $chunkIndex + 1 ?> / <?= $chunkTotal ?></div>
                            <?php endif; ?>
                            <table class="<?= esc($tableClass) ?>">
                                <thead>
                                    <tr>
                                        <th class="ds-mx-sec"><?= esc($classLabel) ?></th>
                                        <?php foreach ($chunk as $d): ?>
                                            <th>
                                                <?= esc(date('D', strtotime($d))) ?><br>
                                                <?= esc(date('d-m', strtotime($d))) ?>
                                            </th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th class="ds-mx-sec"><?= esc($classLabel) ?></th>
                                        <?php foreach ($chunk as $d): ?>
                                            <td><?= $this->renderPrintSubjects($grid[$cid][$d] ?? [], true) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                </tbody>
                            </table>
                            <?php if ($chunkIndex < $chunkTotal - 1): ?>
                                <div style="page-break-after: always; break-after: page;"></div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * @param list<array{name:string}> $papers
     */
    private function renderOverviewCell(array $papers): string
    {
        if ($papers === []) {
            return '<span class="ds-empty-cell">—</span>';
        }

        $html = '';
        foreach ($papers as $paper) {
            $html .= '<span class="ds-subj-line">' . esc($paper['name']) . '</span>';
        }

        return $html;
    }

    /**
     * @param list<array{name:string}> $papers
     */
    private function renderPrintSubjects(array $papers, bool $compact = false): string
    {
        if ($papers === []) {
            return $compact
                ? '<span class="ds-print-empty">—</span>'
                : '<span class="ds-print-empty">—</span>';
        }

        $html = '';
        foreach ($papers as $paper) {
            $html .= '<span class="ds-print-subj">' . esc($paper['name']) . '</span>';
        }

        return $html;
    }

    private function renderPrintHeader(
        string $schoolName,
        string $campusLabel,
        string $examName,
        string $classLabel,
        string $periodLabel,
        string $printedAt
    ): string {
        ob_start();
        ?>
        <div class="ds-print-head">
            <div class="ds-school"><?= esc($schoolName) ?></div>
            <?php if ($campusLabel !== ''): ?>
                <div class="ds-meta"><?= esc($campusLabel) ?></div>
            <?php endif; ?>
            <div class="ds-exam"><?= esc($examName) ?> — Examination Datesheet</div>
            <div class="ds-class">Class: <?= esc($classLabel) ?></div>
            <div class="ds-meta">
                Period: <strong><?= esc($periodLabel) ?></strong>
                &nbsp;|&nbsp; Printed: <?= esc($printedAt) ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
