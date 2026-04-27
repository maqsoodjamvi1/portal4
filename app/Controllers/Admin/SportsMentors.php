<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SportsMentors extends BaseController
{
    protected $db; public function __construct(){ $this->db=db_connect(); }

    public function index(){ return view('admin/sports/mentors'); }

    public function assign()
    {
        $campusId  = (int) session('member_campusid');
        $sessionId = (int) session('member_sessionid');
        $houseId   = (int) $this->request->getPost('house_id');
        $tid       = (int) $this->request->getPost('tid');
        $role      = trim($this->request->getPost('role') ?? 'Mentor');

        $this->db->table('sports_house_teachers')->insert([
            'campus_id'=>$campusId,'session_id'=>$sessionId,'house_id'=>$houseId,
            'tid'=>$tid,'role'=>$role,'user_id'=>(int)session('id')
        ]);
        return $this->response->setJSON(['ok'=>true]);
    }

    public function remove()
    {
        $id = (int) $this->request->getPost('id');
        $this->db->table('sports_house_teachers')->where('id',$id)->delete();
        return $this->response->setJSON(['ok'=>true]);
    }
}
