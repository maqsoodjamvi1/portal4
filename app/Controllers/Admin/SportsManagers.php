<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SportsManagers extends BaseController
{
    protected $db; public function __construct(){ $this->db=db_connect(); }

    public function index($eventId)
    {
        $eventId = (int)$eventId;
        $rows = $this->db->table('sports_event_managers m')
            ->select('m.*, u.first_name, u.last_name')
            ->join('users u','u.id=m.tid','left') // teachers table
            ->where('m.event_id',$eventId)->get()->getResultArray();
        return view('admin/sports/event_managers', compact('rows','eventId'));
    }

    public function assign()
    {
        $payload = [
            'campus_id'=> (int)session('member_campusid'),
            'session_id'=> (int)session('member_sessionid'),
            'event_id'=> (int)$this->request->getPost('event_id'),
            'tid'=> (int)$this->request->getPost('tid'),
            'house_id'=> $this->request->getPost('house_id') ? (int)$this->request->getPost('house_id') : null,
            'user_id'=> (int)session('id'),
        ];
        $this->db->table('sports_event_managers')->insert($payload);
        return $this->response->setJSON(['ok'=>true]);
    }

    public function remove()
    {
        $this->db->table('sports_event_managers')->delete(['id'=>(int)$this->request->getPost('id')]);
        return $this->response->setJSON(['ok'=>true]);
    }
}
