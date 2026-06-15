<?php
namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use Config\Database;

class Results extends BaseController
{
    protected $db; protected $session;
    public function __construct()
    {
        $this->db      = Database::connect();
        $this->session = session();
        helper(['url', 'server', 'parent_portal']);
    }

    public function index()
    {
        // 1) Auth & active student checks
        $auth = $this->session->get('auth');
        if (!$auth || empty($auth['logged_in'])) {
            return redirect()->route('login');
        }

        $sid = (int) (session('active_student_id') ?? 0);
        $role = $auth['role'] ?? '';

        if ($role === 'parent' && $sid <= 0) {
            $kids = \parent_portal_get_children((int) $auth['user_id']);
            if (! empty($kids)) {
                $sid = (int) $kids[0]['student_id'];
                $this->session->set('active_student_id', $sid);
            }
        }

        if (! $sid) {
            return redirect()->route('dashboard')->with('error', 'No active student selected.');
        }

        $this->assertParentOwnsStudentOrFail($sid);

        $c = $this->db;
        $table = null;
        foreach (['exam_results', 'student_exam_results', 'd_exam_results'] as $t) {
            if ($c->tableExists($t)) {
                $table = $t;
                break;
            }
        }
        if ($table === null) {
            log_message('error', 'Results: no exam results table found.');

            return redirect()->route('dashboard')->with('error', 'Results are not available yet.');
        }
        try {
            $fields = $c->getFieldNames($table);
        } catch (\Throwable $e) {
            log_message('error', 'Results: cannot read table fields: ' . $e->getMessage());

            return redirect()->route('dashboard')->with('error', 'Unable to load results.');
        }

        $has = static function (string $name) use ($fields): bool {
            return in_array($name, $fields, true);
        };

        // Candidate mappings (pick the first that exists)
        $colId         = $has('id')                ? 'id'                : ( $has('result_id')         ? 'result_id'         : null );
        $colExamId     = $has('exam_id')           ? 'exam_id'           : ( $has('d_exam_id')         ? 'd_exam_id'         : null );
        $colStudentId  = $has('student_id')        ? 'student_id'        : ( $has('std_id')            ? 'std_id'            : null );

        // Obtained marks variants
        $colObtained   = $has('obtain_total_mark') ? 'obtain_total_mark' :
                         ( $has('obtained_total')  ? 'obtained_total'    :
                         ( $has('obtained_mark')   ? 'obtained_mark'     :
                         ( $has('obtained_marks')  ? 'obtained_marks'    : null )));

        // Total marks variants
        $colTotal      = $has('exam_total_mark')   ? 'exam_total_mark'   :
                         ( $has('total_mark')      ? 'total_mark'        :
                         ( $has('total_marks')     ? 'total_marks'       : null ));

        // Grade column variants
        $colGrade      = $has('grade')             ? 'grade'             :
                         ( $has('grade_name')      ? 'grade_name'        :
                         ( $has('grade_letter')    ? 'grade_letter'      : null ));

        // Timestamp variants
        $colCreatedAt  = $has('created_at')        ? 'created_at'        :
                         ( $has('createdon')       ? 'createdon'         :
                         ( $has('created_date')    ? 'created_date'      : null ));

        // Validate must-have cols
        if ($colStudentId === null) {
            log_message('error', 'Results: student_id column not found in {table}', ['table' => $table]);
            return redirect()->route('dashboard')->with('error', 'Results table is missing student_id column.');
        }

        // 3) Build select list with safe aliases used by the view
        $selectPieces = [];

        // Always include primary identifier if available
        if ($colId !== null)        $selectPieces[] = "r.{$colId} AS id";
        if ($colExamId !== null)    $selectPieces[] = "r.{$colExamId} AS exam_id";

        // Required filter column (still alias it for the view)
        $selectPieces[] = "r.{$colStudentId} AS student_id";

        // Optional columns with friendly aliases expected by your view
        if ($colObtained !== null)  $selectPieces[] = "r.{$colObtained} AS obtain_total_mark";
        if ($colTotal !== null)     $selectPieces[] = "r.{$colTotal} AS exam_total_mark";
        if ($colGrade !== null)     $selectPieces[] = "r.{$colGrade} AS grade";
        if ($colCreatedAt !== null) $selectPieces[] = "r.{$colCreatedAt} AS created_at";

        // If nothing else was detected, at least select *
        if (empty($selectPieces)) {
            $selectPieces[] = 'r.*';
        }

        $b = $this->db->table("$table r")
            ->select(implode(', ', $selectPieces))
            ->where("r.$colStudentId", $sid);

        // Order: prefer created_at variant, else id desc if present
        if ($colCreatedAt !== null) {
            $b->orderBy("r.$colCreatedAt", 'DESC');
        } elseif ($colId !== null) {
            $b->orderBy("r.$colId", 'DESC');
        }

        // 4) Execute safely
        $q = $b->get();

        if ($q === false) {
            $err = $this->db->error();
            log_message('error', 'Frontend/Results index SQL error: {code} {message}', $err);
            log_message('error', 'Frontend/Results SQL: ' . $b->getCompiledSelect(false));
            return redirect()->route('dashboard')->with('error', 'Unable to load exam results right now.');
        }

        $rows = $q->getResultArray();

        $children = ($role === 'parent') ? \parent_portal_get_children((int) $auth['user_id']) : [];

        return view('frontend/results/index', [
            'role'      => $auth['role'],
            'name'      => $auth['name'] ?? '',
            'studentId' => $sid,
            'results'   => $rows,
            'children'  => $children,
        ]);
    }

    private function assertParentOwnsStudentOrFail(int $studentId): void
    {
        $auth=session('auth'); if(!$auth||$auth['role']!=='parent') return;
        $row=$this->db->table('students')->select('student_id')->where('student_id',$studentId)->where('parent_id',(int)$auth['user_id'])->get()->getRowArray();
        if(!$row){ throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(); }
    }
}
