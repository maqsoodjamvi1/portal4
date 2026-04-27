<?php
namespace App\Models\Frontend;

use CodeIgniter\Model;

class AuthModel extends Model
{
    protected $DBGroup = 'default'; // adjust if you use another group
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = db_connect($this->DBGroup);
    }

    public function verify(string $plain, string $hash): bool
    {
        // Adjust if you used md5/sha1 previously
        return password_verify($plain, $hash);
    }

    public function findParentByLogin(string $login): ?array
    {
        $b = $this->db->table('parents p')
            ->select('p.parent_id, p.f_name AS name, p.father_email AS email, p.father_cnic AS cnic, p.password, p.status')
            ->groupStart()
                // check both CNIC and email if you want
                ->where('p.father_cnic', $login)
                // ->orWhere('p.father_email', $login)
            ->groupEnd()
            ->limit(1);

        // If you want to see the SQL BEFORE running it:
        $compiled = $b->getCompiledSelect(false);
        log_message('debug', 'AuthModel::findParentByLogin compiled SQL: ' . $compiled);

        $q = $b->get();

        //print_r($this->db->getLastQuery());
        //exit;

        // If execution failed, $q === false
        if ($q === false) {
            $err = $this->db->error(); // ['code'=>..., 'message'=>...]
            log_message('error', 'AuthModel::findParentByLogin SQL error: {code} {message}', $err);

            // Show the last executed SQL (CI4 way)
            $last = (string) $this->db->getLastQuery();
            log_message('error', 'AuthModel::findParentByLogin last SQL: ' . $last);

            return null;
        }

        // Optional: log the last executed SQL when OK
        log_message('debug', 'AuthModel::findParentByLogin last SQL: ' . (string) $this->db->getLastQuery());

        return $q->getRowArray() ?: null;
    }


    // public function findParentByLogin(string $login): ?array
    // {
    //     $b = $this->db->table('parents p')
    //         ->select('p.parent_id, p.father_name AS name, p.father_email AS email, p.father_cnicnew AS cnic, p.password, p.status')
    //         ->groupStart()->where('p.father_cnicnew', $login)
    //         ->groupEnd()
    //         ->limit(1);

    //     $q = $b->get();
    //     print_r($this->db->last_query());
    //     print_r($q);
    //     exit;
            

    //     if ($q === false) {
    //         // Log underlying DB error so you can see the root cause in logs
    //         $err = $this->db->error(); // ['code'=>..., 'message'=>...]
    //         log_message('error', 'AuthModel::findParentByLogin SQL error: {code} {message}', $err);
    //         // Optional: also log the SQL to spot bad column/table names
    //         log_message('error', 'SQL: ' . $b->getCompiledSelect(false));
    //         return null;
    //     }

    //     return $q->getRowArray() ?: null;
    // }

    public function findStudentByLogin(string $login): ?array
    {
        $b = $this->db->table('students s')
            ->select('s.student_id, s.parent_id, CONCAT(s.first_name," ",s.last_name) AS name, s.reg_no, s.password, s.status')
            ->groupStart()
                ->where('s.reg_no', $login) // extend if you also allow email/CNIC for students
                // ->orWhere('s.email', $login)
                // ->orWhere('s.cnic',  $login)
            ->groupEnd()
            ->limit(1);

        $q = $b->get();

        if ($q === false) {
            $err = $this->db->error();
            log_message('error', 'AuthModel::findStudentByLogin SQL error: {code} {message}', $err);
            log_message('error', 'SQL: ' . $b->getCompiledSelect(false));
            return null;
        }

        return $q->getRowArray() ?: null;
    }

    /**
     * Return list of children (students) for a parent.
     * Adjust field names to your schema if needed.
     */
    public function getChildren(int $parentId): array
{
    // students → class_section (by cls_sec_id) → classes / sections
    $b = $this->db->table('students s')
        ->select("
            s.student_id,
            s.parent_id,
            s.campus_id,
            s.session_id,
            s.cls_sec_id,
            s.reg_no,
            CONCAT(COALESCE(s.first_name,''),' ',COALESCE(s.last_name,'')) AS name,
            s.profile_photo,
            s.status,
            cs.class_id,
            cs.section_id,
            c.class_name,
            sec.section_name,
            sec.short_name AS section_short
        ")
        ->join('class_section cs', 'cs.cls_sec_id = s.cls_sec_id', 'left')
        ->join('classes c',       'c.class_id   = cs.class_id',   'left')
        ->join('sections sec',    'sec.section_id = cs.section_id','left')
        ->where('s.parent_id', $parentId)
        ->orderBy('s.student_id', 'ASC');

    // Optional: log SQL for debugging
    log_message('debug', 'AuthModel::getChildren SQL: ' . $b->getCompiledSelect(false));

    $q = $b->get();
    if ($q === false) {
        $err = $this->db->error();
        log_message('error', 'AuthModel::getChildren error: {code} {message}', $err);
        log_message('error', 'AuthModel::getChildren last SQL: ' . (string) $this->db->getLastQuery());
        return [];
    }

    return $q->getResultArray();
}

}
