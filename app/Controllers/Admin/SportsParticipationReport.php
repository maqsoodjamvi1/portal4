<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SportsParticipationReport extends BaseController
{
    protected $db;
    protected $sessionId;
    protected $campusId;

    public function __construct()
    {
        $this->db = db_connect();
        helper(['url', 'form']);
        $this->sessionId = session('member_sessionid') ?? 0;
        $this->campusId  = session('member_campusid') ?? 0;
    }

   public function index()
{
    $db = db_connect();
    helper(['form', 'url']);

    // 🔹 Fetch current campus/session from session
    $campusId  = (int) (session('member_campusid') ?? 0);
    $sessionId = (int) (session('current_session_id') ?? 0);

    // 🔹 Get only ACTIVE class-sections for this campus
   $sections = $db->table('class_section cs')
    ->select('
        cs.cls_sec_id,
        cs.class_id,
        cs.section_id,
        c.class_name,
        c.class_short_name AS class_short,
        s.section_name
    ')
    ->join('classes c', 'c.class_id = cs.class_id', 'left')
    ->join('sections s', 's.section_id = cs.section_id', 'left')
    ->where('cs.campus_id', $campusId)
    ->where('cs.status', 1)
    
    ->orderBy('c.class_id', 'ASC')
    
    ->get()
    ->getResultArray();

    // 🔹 Load view
    return view('admin/sports/participation_cards', [
        'sections' => $sections,
        'campus_id' => $campusId,
        'session_id' => $sessionId,
    ]);
}
   

public function data()
{
    $campusId  = (int) (session('member_campusid')  ?? 0);
    $sessionId = (int) (session('member_sessionid') ?? 0);
    $clsSecId  = (int) ($this->request->getPost('cls_sec_id') ?? 0);

    if ($sessionId <= 0 || $campusId <= 0) {
        return $this->response->setJSON(['ok' => true, 'rows' => []]);
    }

    $builder = $this->db->table('sports_event_entries se')
        ->select("
            s.student_id,
            s.first_name,
            s.last_name,
            s.profile_photo,
            s.date_of_birth,
            h.house_id,
            h.house_name,
            h.color_code,
            COALESCE(c.class_short_name, c.class_name) AS class_short,
            COUNT(DISTINCT se.event_id) AS participation_count,
            GROUP_CONCAT(DISTINCT e.event_name ORDER BY e.event_date, e.event_id SEPARATOR ',') AS events_list
        ", false)
        ->join('students s', 's.student_id = se.student_id', 'left')
        ->join('sports_houses h', 'h.house_id = s.house_id', 'left')
        // Lock to current session for class info
        ->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = '.$this->db->escape($sessionId), 'left')
        ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
        ->join('classes c', 'c.class_id = cs.class_id', 'left')
        ->join('sports_events e', 'e.event_id = se.event_id', 'left')
        ->where('se.session_id', $sessionId)
        ->where('se.campus_id', $campusId)
        ->where('s.status', 1)
        ->groupBy('s.student_id')
        ->orderBy('s.first_name', 'ASC')
        ->orderBy('s.last_name', 'ASC');

    if ($clsSecId > 0) {
        $builder->where('cs.cls_sec_id', $clsSecId);
    }

    $rows = $builder->get()->getResultArray();

    // Format events into clean array for frontend
    foreach ($rows as &$r) {
        $r['events_array'] = [];
        if (!empty($r['events_list'])) {
            $list = explode(',', $r['events_list']);
            foreach ($list as $ev) {
                $ev = trim($ev);
                if ($ev !== '') {
                    $r['events_array'][] = $ev;
                }
            }
        }
    }

    return $this->response->setJSON(['ok' => true, 'rows' => $rows]);
}
}
