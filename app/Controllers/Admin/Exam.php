<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Database\BaseBuilder;

class Exam extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->db = \Config\Database::connect();
        $this->session = session();
    }

    public function index()
    {
        return view('admin/exam', []);
    }

   public function data()
{
    $request   = service('request');
    $draw      = (int) ($request->getPost('draw') ?? 0);
    $keyword   = (string) ($request->getPost('search')['value'] ?? '');
    $sessionid = (int) $this->session->get('member_sessionid');
    $campusid  = (int) $this->session->get('member_campusid');

    // total
    $b = $this->db->table('exam A')->selectCount('A.eid','ccount')
        ->where(['A.campus_id'=>$campusid,'A.session_id'=>$sessionid]);
    if ($keyword) $b->like('A.exam_name',$keyword);
    $total = (int) ($b->get()->getRow()->ccount ?? 0);

    // data + locked flag without placeholders
    $b = $this->db->table('exam A');
    $b->select("
        A.*,
        IF(COALESCE(ds.cnt,0) > 0 OR COALESCE(sr.cnt,0) > 0, 1, 0) AS locked
    ", false);

    // pre-aggregated counts to avoid scanning large tables per row
    $b->join('(SELECT eid, COUNT(*) AS cnt FROM datesheet GROUP BY eid) ds', 'ds.eid = A.eid', 'left');
    $b->join('(SELECT eid, COUNT(*) AS cnt FROM subject_results GROUP BY eid) sr', 'sr.eid = A.eid', 'left');

    $b->where(['A.campus_id'=>$campusid,'A.session_id'=>$sessionid]);
    if ($keyword) $b->like('A.exam_name',$keyword);
    $b->orderBy('A.eid', 'DESC')
      ->limit((int)$request->getPost('length'), (int)$request->getPost('start'));

    $results = $b->get()->getResult();

    $data = [];
    foreach ($results as $row) {
        $sessioninfo = $this->db->table('academic_session')->where('session_id', $row->session_id)->get()->getRow();
        $terminfo    = $this->db->table('terms')->where('term_id', $row->term_id)->get()->getRow();

        $data[] = [
            'id'               => (int)$row->eid,
            'exam_name'        => (string)$row->exam_name,
            'short_name'       => (string)$row->short_name,
            'exam_start_date'  => (string)$row->exam_start_date,
            'exam_end_date'    => (string)$row->exam_end_date,
            'term_name'        => (string)($terminfo->name ?? ''),
            'exam_session'     => (string)($sessioninfo->session_name ?? ''),
            'status'           => (string)$row->status,
            'locked'           => (int)$row->locked,   // 0 or 1
        ];
    }

    return $this->response->setJSON([
        'draw'            => $draw,
        'recordsTotal'    => $total,
        'recordsFiltered' => $total,
        'data'            => $data,
    ]);
}


private function examLocked(int $eid): bool
{
    $sql = "
        SELECT
          (EXISTS(SELECT 1 FROM datesheet       d  WHERE d.eid = ?)
        OR EXISTS(SELECT 1 FROM subject_results sr WHERE sr.eid = ?)) AS locked
    ";
    $row = $this->db->query($sql, [$eid, $eid])->getRowArray();
    return !empty($row['locked']);
}

public function add()
{
    $campus_id  = (int) $this->session->get('member_campusid');
    $session_id = (int) $this->session->get('member_sessionid');

    // find current unannounced exam for this campus (same session is sensible)
    $un = $this->db->table('exam')
        ->where('campus_id', $campus_id)
        ->where('session_id', $session_id)
        ->groupStart()->where('status', '0')->orWhere('status', 0)->groupEnd()
        ->orderBy('created_date', 'DESC')
        ->get()->getRow();

    if ($un) {
        // If it exists, go to edit. Editing may be locked (we’ll tell the view).
        return redirect()
            ->to(base_url('admin/exam/edit?id=' . (int) $un->eid))
            ->with('flash_msg', 'You already have an unannounced exam. You can edit it here.');
    }

    // no unannounced exam – proceed to normal "add" UI
    $data = [
        'sessionData' => [
            'campusid'  => $campus_id,
            'sessionid' => $session_id
        ],
        'termsinfo' => termSessions()
    ];
    return view('admin/exam_add', $data);
}

   public function edit()
{
    $id = (int) $this->request->getGet('id');
    $info = $this->db->table('exam')->where('eid', $id)->get()->getRow();

    if (!$info) {
        return redirect()->to(base_url('admin/exam'))->with('flash_err', 'Exam not found.');
    }

    $terms_session = $this->db->table('terms_session')
        ->where(['term_id' => $info->term_id, 'session_id' => $info->session_id])
        ->get()->getRow();

    $data = [
        'sessionData' => [
            'campusid'  => $this->session->get('member_campusid'),
            'sessionid' => $this->session->get('member_sessionid')
        ],
        'academic_session_info' => $this->db->table('academic_session')->get()->getResult(),
        'termsinfo'             => $this->db->table('terms')->get()->getResult(),
        'info'                  => $info,
        'terms_session'         => $terms_session,
        'locked'                => $this->examLocked($id), // <<< pass lock flag to the view
        'flash_msg'             => session('flash_msg'),
        'flash_err'             => session('flash_err'),
    ];

    return view('admin/exam_edit', $data);
}

// Make "save_edit" go through the same robust logic as save() (which also writes exam_days).
// You can keep your existing `save()` from earlier — we’ll reuse it.
public function save_edit()
{
    return $this->save();
}
    public function save()
{
    $db = \Config\Database::connect();
    $examTbl = $db->table('exam');
    $daysTbl = $db->table('exam_days'); // make sure this table exists (exam_id INT, exam_date DATE, is_on TINYINT(1))

    $id              = (int) $this->request->getPost('id');
    $term_session_id = (int) $this->request->getPost('term_session_id');

 // If trying to update but exam is locked, block immediately
    if ($id > 0 && $this->examLocked($id)) {
        return $this->response->setStatusCode(423)->setJSON([
            'success' => false,
            'msg'     => 'This exam is locked for editing because a datesheet or results exist.'
        ]);
    }
    // Load the term (for term_id and range validation)
    $term = $this->db->table('terms_session')
        ->where('term_session_id', $term_session_id)
        ->get()->getRow();

    if (!$term) {
        return $this->response->setStatusCode(422)->setJSON(['success' => false, 'msg' => 'Invalid term_session_id']);
    }

     // 🔽 Use robust normalization for both Y-m-d AND d/m/Y (and d-m-Y)
    $exam_start_date = $this->normalizeDate($this->request->getPost('exam_start_date'));
    $exam_end_date   = $this->normalizeDate($this->request->getPost('exam_end_date'));
 if (!$exam_start_date || !$exam_end_date) {
        return $this->response->setStatusCode(422)->setJSON(['success' => false, 'msg' => 'Start/End date is required']);
    }
    $exam_days       = (array) ($this->request->getPost('exam_days') ?? []); // ['YYYY-MM-DD' => '1'|'0', ...]

    if (!$exam_start_date || !$exam_end_date) {
        return $this->response->setStatusCode(422)->setJSON(['success' => false, 'msg' => 'Start/End date is required']);
    }
    if ($exam_end_date < $exam_start_date) {
        return $this->response->setStatusCode(422)->setJSON(['success' => false, 'msg' => 'End date cannot be before start date']);
    }

    // Validate inside the term range (optional but recommended)
    $termStart = date('Y-m-d', strtotime($term->start_date));
    $termEnd   = date('Y-m-d', strtotime($term->end_date));
    if ($exam_start_date < $termStart || $exam_end_date > $termEnd) {
        return $this->response->setStatusCode(422)->setJSON([
            'success' => false,
            'msg'     => 'Exam date range must be within the selected term range'
        ]);
    }

    // Common exam fields
    $examDataCommon = [
        'exam_name'       => $this->request->getPost('exam_name'),
        'short_name'      => $this->request->getPost('short_name'),
        'exam_start_date' => $exam_start_date,
        'exam_end_date'   => $exam_end_date,
        'session_id'      => $this->session->get('member_sessionid'),
        'term_id'         => (int) $term->term_id,
        'user_id'         => $this->session->get('member_userid'),
    ];

    // Helper: build exam_days batch (defaults to ON=1 when missing)
    $buildDaysBatch = static function (int $examId, string $start, string $end, array $daysMap): array {
        $out  = [];
        $curr = new \DateTime($start);
        $last = new \DateTime($end);
        while ($curr <= $last) {
            $ymd  = $curr->format('Y-m-d');
            $isOn = (isset($daysMap[$ymd]) && $daysMap[$ymd] === '0') ? 0 : 1; // default ON
            $out[] = ['exam_id' => $examId, 'exam_date' => $ymd, 'is_on' => $isOn];
            $curr->modify('+1 day');
        }
        return $out;
    };

    $db->transStart();

    if ($id === 0) {
        // CREATE: insert an exam per campus in this system
        $campuses = $this->db->table('campus')
            ->where('system_id', getSchoolInfo()->system_id)
            ->get()->getResult();

        if (empty($campuses)) {
            $db->transRollback();
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'msg' => 'No campuses found for system']);
        }

        $created = [];

        foreach ($campuses as $campus) {
            $examRow = $examDataCommon + [
                'campus_id'    => (int) $campus->campus_id,
                'status'       => '0', // varchar in schema; keep consistent
                'created_date' => date('Y-m-d H:i:s'),
            ];

            // insert exam
            $examTbl->insert($examRow);
            $eid = (int) $db->insertID();

            // insert days for this exam
            $batch = $buildDaysBatch($eid, $exam_start_date, $exam_end_date, $exam_days);
            if (!empty($batch)) {
                $daysTbl->insertBatch($batch);
            }

            $created[] = $eid;
        }

    } else {
        // UPDATE: update the single exam, then replace its days
        $examRow = $examDataCommon + [
            'campus_id'    => (int) $this->request->getPost('campus_id'),
            'updated_date' => date('Y-m-d H:i:s'),
        ];

        $examTbl->where('eid', $id)->update($examRow);

        // replace days for this exam
        $daysTbl->where('exam_id', $id)->delete();

        $batch = $buildDaysBatch($id, $exam_start_date, $exam_end_date, $exam_days);
        if (!empty($batch)) {
            $daysTbl->insertBatch($batch);
        }
    }

    $db->transComplete();

    if ($db->transStatus() === false) {
        return $this->response->setStatusCode(500)->setJSON(['success' => false, 'msg' => 'Save failed']);
    }

    return $this->response->setJSON(['success' => true, 'msg' => ($id === 0 ? 'Exam(s) created' : 'Exam updated')]);
}


private function normalizeDate(?string $v): ?string
{
    if (!$v) return null;
    $v = trim($v);
    if ($v === '') return null;

    $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'];
    foreach ($formats as $fmt) {
        $dt = \DateTime::createFromFormat($fmt, $v);
        if ($dt && $dt->format($fmt) === $v) {
            return $dt->format('Y-m-d');
        }
    }
    $ts = strtotime($v);
    return $ts ? date('Y-m-d', $ts) : null;
}

public function getTermDateRange()
{
    $termSessionId = (int) ($this->request->getPost('term_session_id') ?? 0);
    $examId        = (int) ($this->request->getPost('exam_id') ?? 0); // optional for EDIT

    if ($termSessionId <= 0) {
        return $this->response->setStatusCode(400)->setBody('Missing term_session_id');
    }

    // base term range + term name
    $row = $this->db->table('terms_session ts')
        ->select('ts.start_date AS start, ts.end_date AS `end`, t.name AS term_name')
        ->join('terms t', 't.term_id = ts.term_id', 'left')
        ->where('ts.term_session_id', $termSessionId)
        ->get()->getRowArray();

    if (!$row) {
        return $this->response->setStatusCode(404)->setBody('Term session not found');
    }

    // Defaults for the form’s date inputs
    $viewData = [
        'range'      => $row,   // ['start','end','term_name']
        'exam_start' => null,   // override when editing
        'exam_end'   => null,   // override when editing
        'days_map'   => [],     // ['YYYY-MM-DD' => '1'|'0']
    ];

    if ($examId > 0) {
        // Use existing exam dates as the initial values
        $exam = $this->db->table('exam')
            ->select('exam_start_date, exam_end_date')
            ->where('eid', $examId)
            ->get()->getRowArray();

        if ($exam) {
            $viewData['exam_start'] = $exam['exam_start_date'] ?? null; // Y-m-d
            $viewData['exam_end']   = $exam['exam_end_date']   ?? null; // Y-m-d
        }

        // Pull saved day toggles
        $days = $this->db->table('exam_days')
            ->select('exam_date, is_on')
            ->where('exam_id', $examId)
            ->get()->getResultArray();

        if ($days) {
            $map = [];
            foreach ($days as $d) {
                $ymd = date('Y-m-d', strtotime($d['exam_date']));
                $map[$ymd] = (string)((int)$d['is_on']); // '1' or '0'
            }
            $viewData['days_map'] = $map;
        }
    }

    // Return the partial
    return view('admin/partials/exam_term_date_range', $viewData);
}
   public function getDateRange()
    {
        $examId    = (int) ($this->request->getPost('exam_id') ?? 0);
        $campus_id = (int) session('member_campusid');     // adjust if you don't want campus filter
        $sessionid = (int) session('member_sessionid');    // adjust if optional

        if ($examId <= 0) {
            // Bad request – no exam id
            return $this->response->setStatusCode(400)
                ->setBody('Missing exam_id');
        }

        // Pull dates from `exam` table
        $row = $this->db->table('exam')
            ->select('exam_start_date AS start, exam_end_date AS end')
            ->where('eid', $examId)
            // Uncomment if you want to scope by campus/session too:
             ->where('campus_id', $campus_id)
             ->where('session_id', $sessionid)
            ->get()->getRowArray(); // exam has exam_start_date & exam_end_date

        if (!$row || empty($row['start']) || empty($row['end'])) {
            // Still nothing – show the same partial with the "No date range" message
            return view('admin/partials/exam_date_range', ['range' => null]);
        }

        // Success
        return view('admin/partials/exam_date_range', ['range' => $row]);
    }

    private function formatDate($dateString)
    {
        $date = \DateTime::createFromFormat('d/m/Y', $dateString);
        return $date ? $date->format('Y-m-d') : null;
    }
}
