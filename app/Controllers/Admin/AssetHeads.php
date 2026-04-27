<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use stdClass;

class AssetHeads extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        check_permission('admin-asset-heads');
    }

    public function index()
    {
        return view('admin/asset_heads', []);
    }

    public function data(): ResponseInterface
    {
        $request = $this->request->getPost();
        $draw = $request['draw'] ?? 1;
        $length = $request['length'] ?? 10;
        $start = $request['start'] ?? 0;
        $schoolinfo = getSchoolInfo();
        $keyword = $request['search']['value'] ?? '';

        // Count
        $builder = $this->db->table('asset_heads A');
        $builder->selectCount('A.asset_head_id', 'ccount');
        $builder->where('A.system_id', $schoolinfo->system_id);
        if ($keyword) {
            $builder->where('A.head_title', $keyword);
        }
        $q = $builder->get()->getRow();
        $recordsTotal = $q->ccount ?? 0;

        // Result set
        $builder = $this->db->table('asset_heads A');
        $builder->select('A.*');
        $builder->where('A.system_id', $schoolinfo->system_id);
        if ($keyword) {
            $builder->where('A.head_title', $keyword);
        }
        $builder->orderBy('A.asset_head_id', 'desc');
        $builder->limit($length, $start);
        $results = $builder->get()->getResult();

        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'id' => $row->asset_head_id,
                'head_title' => $row->head_title,
                'detail' => $row->detail,
            ];
        }

        $response = [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $data
        ];

        return $this->response->setJSON($response);
    }

    public function add()
    {
        check_permission('admin-add-asset-heads');
        $schoolinfo = getSchoolInfo();
        $info = $this->db->table('asset_heads')->where('system_id', $schoolinfo->system_id)->get()->getResult();
        return view('admin/asset_heads_edit', [
            'info' => $info
        ]);
    }

    public function edit()
    {
        check_permission('admin-edit-asset-heads');
        $asset_head_id = intval($this->request->getGet('id'));
        $info = $this->db->table('asset_heads')->where('asset_head_id', $asset_head_id)->get()->getRow();
        return view('admin/asset_heads_edit', [
            'info' => $info
        ]);
    }

    public function save(): ResponseInterface
    {
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d H:i:s');
        $schoolinfo = getSchoolInfo();
        $rowscount = $this->request->getPost('rowscount');

        foreach ((array)$rowscount as $i) {
            $id = $this->request->getPost('id' . $i);
            $this->db->transStart();
            if ($id == 0) {
                $data = [
                    'head_title' => trim($this->request->getPost('head_title' . $i)),
                    'detail' => trim($this->request->getPost('detail' . $i)),
                    'system_id' => $schoolinfo->system_id,
                    'user_id' => $user_id,
                    'created_date' => $date
                ];
                $this->db->table('asset_heads')->insert($data);
            } else {
                $data = [
                    'head_title' => trim($this->request->getPost('head_title' . $i)),
                    'detail' => trim($this->request->getPost('detail' . $i)),
                    'user_id' => $user_id,
                    'updated_date' => $date
                ];
                $this->db->table('asset_heads')->where('asset_head_id', $id)->update($data);
            }
            $this->db->transComplete();
        }

        return $this->response->setJSON(['success' => true, 'msg' => 'Asset head update Success']);
    }

    public function delete(): ResponseInterface
    {
        check_permission('admin-del-account-heads');
        $id = intval($this->request->getGet('id'));
        $this->db->transStart();
        $this->db->table('asset_heads')->where('asset_head_id', $id)->delete();
        $this->db->transComplete();
        return $this->response->setJSON(['success' => true, 'msg' => 'Delete Asset Head Success']);
    }
}
