<?php
namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use Config\Database;

class Events extends BaseController
{
    protected $db; protected $session;
    public function __construct(){ $this->db=Database::connect(); $this->session=session(); helper(['url']); }

    public function index()
    {
        $auth=$this->session->get('auth'); if(!$auth||empty($auth['logged_in'])) return redirect()->route('login');
        $sid=(int)(session('active_student_id')??0); if(!$sid) return redirect()->route('dashboard')->with('error','No active student selected.');
        $this->assertParentOwnsStudentOrFail($sid);

        $stu=$this->db->table('students')->select('class_id, campus_id')->where('student_id',$sid)->get()->getRowArray();
        $classId=(int)($stu['class_id']??0); $campusId=(int)($stu['campus_id']??0);

        $b=$this->db->table('events e')->select('e.id, e.title, e.event_date, e.start_time, e.end_time, e.venue, e.description');
        if($campusId) $b->groupStart()->where('e.campus_id',$campusId)->orWhere('e.campus_id IS NULL',null,false)->groupEnd();
        if($classId)  $b->groupStart()->where('e.class_id',$classId)->orWhere('e.class_id IS NULL',null,false)->groupEnd();
        $rows=$b->orderBy('e.event_date','DESC')->limit(100)->get()->getResultArray();

        return view('frontend/events/index',['role'=>$auth['role'],'name'=>$auth['name']??'','rows'=>$rows]);
    }

    private function assertParentOwnsStudentOrFail(int $studentId): void
    {
        $auth=session('auth'); if(!$auth||$auth['role']!=='parent') return;
        $row=$this->db->table('students')->select('student_id')->where('student_id',$studentId)->where('parent_id',(int)$auth['user_id'])->get()->getRowArray();
        if(!$row){ throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(); }
    }
}
