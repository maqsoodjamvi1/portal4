<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SportsRules extends BaseController
{
    protected $db; public function __construct(){ $this->db=db_connect(); }

    public function index()
    {
        $rows = $this->db->table('sports_scoring_rules')
            ->where([
                'campus_id'=>(int)session('member_campusid'),
                'session_id'=>(int)session('member_sessionid'),
            ])->orderBy('position','ASC')->get()->getResultArray();
        return view('admin/sports/rules', compact('rows'));
    }

    public function save()
    {
        $campusId=(int)session('member_campusid'); $sessionId=(int)session('member_sessionid');
        $positions = $this->request->getPost('position'); // arrays align
        $points    = $this->request->getPost('points');

        $tbl = $this->db->table('sports_scoring_rules');
        // simple replace: wipe then insert
        $tbl->where(['campus_id'=>$campusId,'session_id'=>$sessionId])->delete();
        foreach ($positions as $i=>$pos) {
            $tbl->insert(['campus_id'=>$campusId,'session_id'=>$sessionId,'position'=>(int)$pos,'points'=>(int)$points[$i],'user_id'=>(int)session('id')]);
        }
        return $this->response->setJSON(['ok'=>true]);
    }
}
