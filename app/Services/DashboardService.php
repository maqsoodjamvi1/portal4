<?php
namespace App\Services;

class DashboardService
{
    protected $db;
    protected $campusId;

    public function __construct()
    {
        $this->db = db_connect();
        $this->campusId = session('member_campusid');
    }

    public function getStats()
    {
        return [
            'students' => $this->db->table('students')
                ->where('campus_id', $this->campusId)
                ->where('status', 1)
                ->countAllResults(),

           'teachers' => $this->db->table('users u')
    ->join('user_roles ur', 'ur.userid = u.id')
    ->join('role_name r', 'r.role_name_id = ur.roleid')
    ->where('r.rolename', 'teacher') // exact column name
    ->countAllResults(),
        ];
    }
}