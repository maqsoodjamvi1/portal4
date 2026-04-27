<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DayOnReset extends BaseController
{
    protected $db;
    protected $session;
    protected $request;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url', 'text']);
    }

   public function index()
{
    // View stays the same; it will AJAX-load the simplified table from data()
    return view('admin/day_on_reset_edit', []);
}

public function data()
{
    $campusid = (int) $this->session->get('member_campusid');

    // Accept date from client; default to today if invalid/missing
    $dateStr = trim($this->request->getPost('date') ?? '');
    if (!$dateStr || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
        $dateStr = date('Y-m-d');
    }
    $ts      = strtotime($dateStr);
    $currDay = date('l', $ts);  // Monday, Tuesday, ...

    // Timing type: posted or fallback to first active
    $typeId = (int) ($this->request->getPost('school_timing_type_id') ?? 0);
    if ($typeId <= 0) {
        $row = $this->db->table('school_timing_types')
            ->select('type_id')
            ->where('campus_id', $campusid)
            ->where('status', 1)
            ->orderBy('type_id', 'ASC')
            ->get()->getRow();
        $typeId = (int)($row->type_id ?? 0);
    }
    if ($typeId <= 0) {
        return $this->response->setBody("<div class='alert alert-warning'>No active timing type found.</div>");
    }

    // Fetch sections with non-zero (checkout - checkin) for that weekday + type
    $builder = $this->db->table('class_section cs');
    $builder->select('cs.cls_sec_id, c.class_name, s.section_name')
        ->join('classes c', 'c.class_id = cs.class_id', 'inner')
        ->join('sections s', 's.section_id = cs.section_id', 'inner')
        ->join(
            'school_timings st',
            "st.cls_sec_id = cs.cls_sec_id
             AND st.dayname = " . $this->db->escape($currDay) . "
             AND st.type_id = " . (int)$typeId . "
             AND st.checkin_timing IS NOT NULL
             AND st.checkout_timing IS NOT NULL
             AND TIME_TO_SEC(TIMEDIFF(st.checkout_timing, st.checkin_timing)) <> 0",
            'inner',
            false
        )
        ->where('cs.campus_id', $campusid)
        ->where('cs.status', 1)
        ->groupBy('cs.cls_sec_id, c.class_name, s.section_name')
        ->orderBy('c.class_name', 'ASC')
        ->orderBy('s.section_name', 'ASC');

    $rows = $builder->get()->getResultArray();

    if (empty($rows)) {
        return $this->response->setBody(
            "<div class='alert alert-info mb-0'>No class sections found for "
            . esc($currDay) . " (". esc($dateStr) .") with non-zero timing.</div>"
        );
    }

    // Build table (unchanged)
    $html  = "<div class='table-responsive'>";
    $html .= "  <table class='table table-striped table-hover mb-0'>";
    $html .= "    <thead><tr>
                    <th style='width:60px;'>
                      <div class='custom-control custom-checkbox'>
                        <input type='checkbox' class='custom-control-input' id='select_all_sections'>
                        <label class='custom-control-label' for='select_all_sections'>All</label>
                      </div>
                    </th>
                    <th>Class</th><th>Section</th>
                  </tr></thead><tbody>";

    foreach ($rows as $r) {
        $id = (int)$r['cls_sec_id'];
        $html .= "<tr>
          <td>
            <div class='custom-control custom-checkbox'>
              <input type='checkbox' class='custom-control-input section-check' id='sec_{$id}' name='sections[]' value='{$id}' checked>
              <label class='custom-control-label' for='sec_{$id}'></label>
            </div>
          </td>
          <td>".esc($r['class_name'])."</td>
          <td>".esc($r['section_name'])."</td>
        </tr>";
    }

    $html .= "</tbody></table></div>";

    $html .= "<script>
      $(function(){
        var \$wrap = $('#timetablearea');
        var \$all  = \$wrap.find('#select_all_sections');
        \$all.off('change').on('change', function(){ \$wrap.find('.section-check').prop('checked', this.checked); });
        \$wrap.off('change', '.section-check').on('change', '.section-check', function(){
          var total = \$wrap.find('.section-check').length;
          var ck    = \$wrap.find('.section-check:checked').length;
          \$all.prop('checked', total && total === ck);
        });
      });
    </script>";

    return $this->response->setBody($html);
}


public function add()
{
    $campusid = (int) $this->session->get('member_campusid');

    // Day to check (allow ?day=Friday in URL, default -> today)
    $currDay = $this->request->getGet('day');
    if (!$currDay) {
        $currDay = date('l'); // Monday, Tuesday, ...
    }

    // Active timing types for this campus
    $typesQuery = $this->db->table('school_timing_types')
        ->where('campus_id', $campusid)
        ->where('status', 1);

    // Optional sorting if column exists
    // (safe: will be ignored if column doesn't exist)
    $typesQuery->orderBy('type_id', 'ASC');

    $types = $typesQuery->get()->getResultArray();
    $defaultTypeId = $types[0]['type_id'] ?? null;

    // Collect active type_ids for subquery/IN()
    $typeIds = array_column($types, 'type_id');

    $nonzero_cls_sec_ids = [];
    if (!empty($typeIds)) {
        // DISTINCT cls_sec_id where TIME_TO_SEC(TIMEDIFF(checkout, checkin)) != 0
        $timingsQB = $this->db->table('school_timings')
            ->distinct()
            ->select('cls_sec_id')
            ->whereIn('type_id', $typeIds)
            ->where('dayname', $currDay)
            // next where uses a raw expression (no escaping)
            ->where('TIME_TO_SEC(TIMEDIFF(checkout_timing, checkin_timing)) <> 0', null, false);

        // (Optional safety) ignore rows with NULL values in either time
        $timingsQB->where('checkin_timing IS NOT NULL', null, false)
                  ->where('checkout_timing IS NOT NULL', null, false);

        $nonzero = $timingsQB->get()->getResultArray();
        $nonzero_cls_sec_ids = array_map(static function($r){ return (int)$r['cls_sec_id']; }, $nonzero);
    }

    $template_data = [
        'infoschooltimingtypes' => $types,
        'default_type_id'       => $defaultTypeId,
        'curr_day'              => $currDay,
        // Use this in your view or AJAX to filter/show/pre-check
        'nonzero_cls_sec_ids'   => $nonzero_cls_sec_ids,
    ];

    return view('admin/day_on_reset_edit', $template_data);
}


    public function edit()
    {
        $id = (int) $this->request->getGet('id');
        $campusid = $this->session->get('member_campusid');

        $info = $this->db->table('teacher_subjects')
            ->where('sst', $id)
            ->where('campus_id', $campusid)
            ->get()->getRow();
        $infoteachers = $this->db->table('teachers')->where('campus_id', $campusid)->get()->getResult();
        $classinfo = $this->db->table('classes')->get()->getResult();
        $subjectinfo = $this->db->table('allsubject')->get()->getResult();

        $template_data = [
            'info' => $info,
            'infoteachers' => $infoteachers,
            'classinfo' => $classinfo,
            'subjectinfo' => $subjectinfo,
        ];
        return view('admin/day_on_reset_edit', $template_data);
    }

    public function save()
{
    $user_id   = (int) $this->session->get('member_userid');
    $today     = date('Y-m-d');
    $now       = date('Y-m-d H:i:s');

    // Support either `sections[]` (new view) or `section_id[]` (old)
    $sections = $this->request->getPost('sections');
    if (empty($sections)) {
        $sections = $this->request->getPost('section_id');
    }

    // Normalize to array of ints
    if (empty($sections)) {
        return $this->response->setJSON([
            'success' => false,
            'msg'     => 'Select at least one class section.'
        ]);
    }
    if (!is_array($sections)) {
        $sections = [$sections];
    }
    $sections = array_values(array_unique(array_map('intval', $sections)));
    $sections = array_filter($sections, static fn($v) => $v > 0);

    if (empty($sections)) {
        return $this->response->setJSON([
            'success' => false,
            'msg'     => 'Select at least one valid class section.'
        ]);
    }

    // Find which ones already exist for today (avoid duplicates)
    $existing = $this->db->table('mark_attendance')
        ->select('cls_sec_id')
        ->where('date', $today)
        ->whereIn('cls_sec_id', $sections)
        ->get()
        ->getResultArray();

    $existingSet = [];
    foreach ($existing as $row) {
        $existingSet[(int)$row['cls_sec_id']] = true;
    }

    // Prepare rows to insert
    $rows = [];
    foreach ($sections as $sid) {
        if (!isset($existingSet[$sid])) {
            $rows[] = [
                'cls_sec_id'   => $sid,
                'date'         => $today,
                'status'       => 'pending',
                'created_date' => $now,
                'updated_date' => $now,
                'user_id'      => $user_id,
            ];
        }
    }

    // Nothing new?
    if (empty($rows)) {
        return $this->response->setJSON([
            'success' => true,
            'msg'     => 'Already marked as pending for today.'
        ]);
    }

    // Insert in a transaction
    $this->db->transStart();
    $this->db->table('mark_attendance')->insertBatch($rows);
    $this->db->transComplete();
if ($this->db->transStatus() === false) {
    $payload = ['success' => false, 'msg' => 'Failed to save attendance marks.'];
    return $this->response
        ->setStatusCode(200)
        ->setContentType('application/json')
        ->setJSON($payload);
}

$payload = [
    'success'        => true,
    'msg'            => 'Attendance marks queued as pending for selected sections.',
    'inserted_count' => count($rows),
];

return $this->response
    ->setStatusCode(200)
    ->setContentType('application/json')
    ->setJSON($payload);
}

}
