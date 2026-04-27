<?php
namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use Config\Database;

class dDatesheet extends BaseController
{
    protected $db; protected $session;
    public function __construct(){ $this->db=Database::connect(); $this->session=session(); helper(['url']); }

    public function index()
    {
        // 1) Auth & active student
        $auth = $this->session->get('auth');
        if (!$auth || empty($auth['logged_in'])) {
            return redirect()->route('login');
        }

        $sid = (int) (session('active_student_id') ?? 0);
        if (!$sid) {
            return redirect()->route('dashboard')->with('error', 'No active student selected.');
        }

        $this->assertParentOwnsStudentOrFail($sid);

        // 2) Resolve class_id from students.cls_sec_id -> class_section.class_id
        $bStu = $this->db->table('students s')
            ->select('cs.class_id')
            ->join('class_section cs', 'cs.cls_sec_id = s.cls_sec_id', 'left')
            ->where('s.student_id', $sid)
            ->limit(1);

        $qStu = $bStu->get();
        if ($qStu === false) {
            $err = $this->db->error();
            log_message('error', 'Datesheet: student lookup SQL error: {code} {message}', $err);
            log_message('error', 'Datesheet: student SQL => ' . $bStu->getCompiledSelect(false));
            return redirect()->route('dashboard')->with('error', 'Unable to determine student class.');
        }

        $stu    = $qStu->getRowArray() ?: [];
        $classId = (int) ($stu['class_id'] ?? 0);
        if ($classId <= 0) {
            return redirect()->route('dashboard')->with('error', 'No class found for this student.');
        }

        // 3) Datesheet query (d_exam_datesheet columns)
        $b = $this->db->table('datesheet d')
            ->select('d.id, d.exam_id, d.class_id, d.subject_id, d.exam_date, d.start_time, d.end_time, d.room')
            ->where('d.class_id', $classId)
            ->orderBy('d.exam_date', 'ASC')
            ->orderBy('d.start_time', 'ASC');

        $q = $b->get();
        if ($q === false) {
            $err = $this->db->error();
            log_message('error', 'Datesheet: datesheet SQL error: {code} {message}', $err);
            log_message('error', 'Datesheet: datesheet SQL => ' . $b->getCompiledSelect(false));
            return redirect()->route('dashboard')->with('error', 'Unable to load datesheet right now.');
        }

        $rows = $q->getResultArray();

        return view('frontend/datesheet/index', [
            'role'     => $auth['role'],
            'name'     => $auth['name'] ?? '',
            'rows'     => $rows,
            'class_id' => $classId,
        ]);
    }


    private function assertParentOwnsStudentOrFail(int $studentId): void
    {
        $auth=session('auth'); if(!$auth||$auth['role']!=='parent') return;
        $row=$this->db->table('students')->select('student_id')->where('student_id',$studentId)->where('parent_id',(int)$auth['user_id'])->get()->getRowArray();
        if(!$row){ throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(); }
    }
}
