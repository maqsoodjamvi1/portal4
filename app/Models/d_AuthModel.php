<?php
namespace App\Models;

use CodeIgniter\Model;
use Config\Database;

class AuthModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::connect();
    }

    /**
     * Try login as Parent by email or CNIC (father_email, father_cnicnew)
     */
    public function findParentByLogin(string $login): ?array
    {
         $this->db->table('parents p')
            ->select('p.parent_id, p.father_name AS name, p.father_email AS email, p.father_cnicnew AS cnic, p.password, p.status')
            ->groupStart()
                ->where('p.father_email', $login)
                ->orWhere('p.father_cnic', $login)
            ->groupEnd()
            ->limit(1)
            ->get()
            ->getRowArray() ?: null;
            echo "<pre>";
        print_r($this->db->getLastQuery());
        echo "</pre>";
        exit;
    }

    /**
     * Try login as Student by reg_no (or email/CNIC if you store them)
     * Adjust WHEREs if you use another student login identifier.
     */
    public function findStudentByLogin(string $login): ?array
    {
        return $this->db->table('students s')
            ->select('s.student_id, s.parent_id, CONCAT(s.first_name," ",s.last_name) AS name, s.reg_no, s.password, s.status')
            ->where('s.reg_no', $login)
            ->limit(1)
            ->get()
            ->getRowArray() ?: null;
    }

    public function verifyPassword(string $plain, string $hash): bool
    {
        // If legacy MD5/plain existed, you can add fallback here.
        return password_verify($plain, $hash);
    }

    /**
     * Get children for a given parent
     */
    public function getChildrenByParent(int $parentId): array
    {
        return $this->db->table('students s')
            ->select('s.student_id, s.first_name, s.last_name, s.class_id, s.reg_no')
            ->where('s.parent_id', $parentId)
            ->where('s.status', 1)
            ->orderBy('s.first_name', 'ASC')
            ->get()
            ->getResultArray();
    }
}
