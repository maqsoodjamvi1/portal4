<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DatesheetReport extends BaseController
{
    protected $db;
    protected $session;
    protected $template_data = [];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url']);
        check_permission('admin-datesheet-report');
    }

    /**
     * Index Page for this controller.
     */
    public function index()
    {
        // If you want to fetch and pass more data, do it here.
        return view('admin/datesheet_report', $this->template_data);
    }

    public function data()
{
    $exam_id = (int) $this->request->getPost('exam_id');
    if ($exam_id <= 0) {
        return $this->response->setBody('<div class="alert alert-warning m-2">Please select an exam.</div>');
    }

    $db = $this->db;

    // Exam meta (name) + date range (min/max used in print header)
    $exam = $db->table('exam')->select('exam_name')->where('eid', $exam_id)->get()->getRow();
    $examName = $exam->exam_name ?? 'Exam';

    $dates = $db->table('datesheet')
        ->select('exam_date')
        ->where('eid', $exam_id)
        ->where('exam_date IS NOT NULL', null, false)
        ->groupBy('exam_date')
        ->orderBy('exam_date', 'ASC')
        ->get()->getResultArray();

    if (empty($dates)) {
        return $this->response->setBody('<div class="alert alert-info m-2">No papers scheduled for this exam.</div>');
    }

    $dateKeys = array_values(array_unique(array_map(fn($r) => $r['exam_date'], $dates)));
    $startDate = reset($dateKeys);
    $endDate   = end($dateKeys);

    // Pull *all* papers once (avoid per-cell queries)
    // If your table has different time columns, adjust start_time/end_time below.
    $rows = $db->table('datesheet d')
        ->select("
            d.exam_date,
            d.cls_sec_id,
         
            ss.subject_id,
            subj.subject_short_name AS subj_name
        ", false)
        ->join('section_subjects ss', 'ss.sec_sub_id = d.sec_sub_id', 'left')
        ->join('allsubject subj', 'subj.sid = ss.subject_id', 'left')
        ->where('d.eid', $exam_id)
        ->whereIn('d.exam_date', $dateKeys)
        ->orderBy('d.exam_date', 'ASC')
        ->get()->getResult();

    // Grid: $grid[cls_sec_id][exam_date] = [ {name, start, end}, ... ]
    $grid = [];
    foreach ($rows as $r) {
        $cls = (int) $r->cls_sec_id;
        $dt  = (string) $r->exam_date;
        $grid[$cls][$dt][] = [
            'name'  => trim($r->subj_name ?? ''),
            'start' => $r->start_time ?? '',
            'end'   => $r->end_time   ?? '',
        ];
    }

    // Sections list; skip rows that have no exams at all (cleaner view)
    $sections = getAllClassSection(); // expects: ['cls_sec_id','sectionclassname',...]
    $sections = $this->db->table('class_section cs')
    ->select("
        cs.cls_sec_id,
        CONCAT(COALESCE(NULLIF(c.class_short_name, ''), c.class_name), ' - ', s.section_name) AS sectionclassname
    ", false)
    ->join('classes c',  'c.class_id = cs.class_id', 'inner')
    ->join('sections s', 's.section_id = cs.section_id', 'inner')
    ->where('cs.status', 1)
    ->orderBy('c.class_id', 'ASC')
    ->orderBy('s.section_id', 'ASC')
    ->get()->getResultArray();
    
    $sections = array_filter($sections, function($s) use ($grid) {
        $id = (int) $s['cls_sec_id'];
        return !empty($grid[$id] ?? []);
    });

    if (empty($sections)) {
        return $this->response->setBody('<div class="alert alert-info m-2">No classes/sections have papers in this exam.</div>');
    }

    // ---- Build HTML --------------------------------------------------------
    ob_start();
    ?>
    <div class="ds-toolbar d-flex align-items-center justify-content-between no-print p-2">
      <div class="small text-muted">
        <strong><?= esc($examName) ?></strong> &middot;
        <?= date('d M Y', strtotime($startDate)) ?> &rarr; <?= date('d M Y', strtotime($endDate)) ?>
      </div>
      <div>
        <button id="dsPrintBtn" class="btn btn-sm btn-outline-dark">
          <i class="fas fa-print mr-1"></i> Print
        </button>
      </div>
    </div>

    <!-- Header shown only on print -->
    <div class="ds-print-header d-none d-print-block text-center mb-3">
      <h3 class="mb-1"><?= esc($examName) ?></h3>
      <div class="small">
        Period:
        <strong><?= date('d M Y', strtotime($startDate)) ?></strong>
        to
        <strong><?= date('d M Y', strtotime($endDate)) ?></strong>
        &nbsp; | &nbsp;
        Printed on: <strong><?= date('d M Y, h:i A') ?></strong>
      </div>
    </div>

    <div class="table-responsive table-sticky-wrap">
      <table class="table table-bordered table-hover table-sm ds-table mb-0">
        <thead class="thead-dark">
          <tr>
            <th class="sticky-col th-sec">Class / Section</th>
            <?php foreach ($dateKeys as $d): ?>
              <th class="text-center">
                <?= date('D', strtotime($d)) ?><br>
                <span class="small"><?= date('Y-m-d', strtotime($d)) ?></span>
              </th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($sections as $sec): ?>
            <?php
              $cid = (int) $sec['cls_sec_id'];
              // If the whole row is empty (paranoia guard after earlier filter), skip
              $hasAny = !empty($grid[$cid] ?? []);
              if (!$hasAny) continue;
            ?>
            <tr>
              <th class="sticky-col td-sec"><?= esc($sec['sectionclassname'] ?? ('#'.$cid)) ?></th>
              <?php foreach ($dateKeys as $d): ?>
                <td>
                  <?php if (!empty($grid[$cid][$d])): ?>
                    <?php foreach ($grid[$cid][$d] as $paper): ?>
                      <?php
                        $nm = $paper['name'] ?: 'Subject';
                        $tm = '';
                        if (!empty($paper['start']) || !empty($paper['end'])) {
                            $tm = ' <span class="text-muted small">('
                                 . trim(($paper['start'] ?: '') . ( ($paper['start'] && $paper['end']) ? '–' : '' ) . ($paper['end'] ?: ''))
                                 . ')</span>';
                        }
                      ?>
                      <div class="badge badge-pill badge-info ds-badge mb-1">
                        <?= esc($nm) ?>
                      </div><?= $tm ?><br>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
              <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php
    $html = ob_get_clean();

    return $this->response->setBody($html);
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
}
// end file
