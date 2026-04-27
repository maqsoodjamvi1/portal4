<?php
namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use Config\Database;

class Attendance extends BaseController
{
    protected $db; protected $session;
    public function __construct(){ $this->db=Database::connect(); $this->session=session(); helper(['url']); }

    public function index()
    {
        // 1) Auth & active student checks
        $auth = $this->session->get('auth');
        if (!$auth || empty($auth['logged_in'])) {
            return redirect()->route('login');
        }

        $sid = (int) (session('active_student_id') ?? 0);
        if (!$sid) {
            return redirect()->route('dashboard')->with('error', 'No active student selected.');
        }

        $this->assertParentOwnsStudentOrFail($sid);

        // 2) Pick attendance table (try common names)
        $c = $this->db;
        $table = null;
        foreach (['attendance', 'student_attendance', 'attendance_register'] as $t) {
            if ($c->tableExists($t)) { $table = $t; break; }
        }
        if ($table === null) {
            log_message('error', 'Attendance: no attendance table found.');
            return redirect()->route('dashboard')->with('error', 'Attendance table not found.');
        }

        // 3) Read columns and map variants → canonical aliases for the view
        try {
            $fields = $c->getFieldNames($table);
        } catch (\Throwable $e) {
            log_message('error', 'Attendance: cannot fetch fields for {table}: ' . $e->getMessage(), ['table' => $table]);
            return redirect()->route('dashboard')->with('error', 'Attendance table not accessible.');
        }

        $has = static function (string $name) use ($fields): bool {
            return in_array($name, $fields, true);
        };

        $colId        = $has('id')                ? 'id'                : ($has('att_id') ? 'att_id' : null);
        $colStudentId = $has('student_id')        ? 'student_id'        : ($has('std_id') ?: ($has('studentid') ? 'studentid' : null));
        $colDate      = $has('attendance_date')   ? 'attendance_date'   : ($has('att_date') ?: ($has('date') ? 'date' : null));
        $colStatus    = $has('status')            ? 'status'            : ($has('attendance_status') ?: ($has('present') ? 'present' : null));
        $colRemarks   = $has('remarks')           ? 'remarks'           : ($has('comment') ?: ($has('note') ? 'note' : null));
        $colCreated   = $has('created_at')        ? 'created_at'        : ($has('createdon') ?: ($has('created_date') ? 'created_date' : null));

        if ($colStudentId === null || $colDate === null) {
            log_message('error', 'Attendance: required columns missing in {table}', ['table' => $table]);
            return redirect()->route('dashboard')->with('error', 'Attendance table missing required columns.');
        }

        // 4) Build select with canonical aliases expected by your view
        $select = [];
        if ($colId)        $select[] = "a.$colId AS id";
        $select[] = "a.$colStudentId AS student_id";
        $select[] = "a.$colDate AS attendance_date";
        if ($colStatus)    $select[] = "a.$colStatus AS status";
        if ($colRemarks)   $select[] = "a.$colRemarks AS remarks";
        if ($colCreated)   $select[] = "a.$colCreated AS created_at";

        $b = $c->table("$table a")
            ->select(implode(', ', $select))
            ->where("a.$colStudentId", $sid)
            ->limit(120);

        // Order by date desc, else created desc, else id desc
        if ($colDate)     $b->orderBy("a.$colDate", 'DESC');
        elseif ($colCreated) $b->orderBy("a.$colCreated", 'DESC');
        elseif ($colId)   $b->orderBy("a.$colId", 'DESC');

        // 5) Execute safely
        $q = $b->get();
        if ($q === false) {
            $err = $c->error();
            log_message('error', 'Frontend/Attendance SQL error: {code} {message}', $err);
            log_message('error', 'Frontend/Attendance SQL: ' . $b->getCompiledSelect(false));
            return redirect()->route('dashboard')->with('error', 'Unable to load attendance right now.');
        }

        $rows = $q->getResultArray();

        // If your schema uses boolean "present" instead of textual status, normalize:
        if ($colStatus === 'present') {
            foreach ($rows as &$r) {
                // cast to text status for consistency
                $r['status'] = ((string)$r['status'] === '1' || $r['status'] === 1) ? 'Present' : 'Absent';
            }
            unset($r);
        }

        return view('frontend/attendance/index', [
            'role'      => $auth['role'],
            'name'      => $auth['name'] ?? '',
            'studentId' => $sid,
            'rows'      => $rows,
        ]);
    }


    private function assertParentOwnsStudentOrFail(int $studentId): void
    {
        $auth=session('auth'); if(!$auth||$auth['role']!=='parent') return;
        $row=$this->db->table('students')->select('student_id')->where('student_id',$studentId)->where('parent_id',(int)$auth['user_id'])->get()->getRowArray();
        if(!$row){ throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(); }
    }
}
