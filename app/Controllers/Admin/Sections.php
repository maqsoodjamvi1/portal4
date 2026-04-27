<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Sections extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form']);
        check_permission('admin-sections');
    }

    public function index()
    {
        return view('admin/sections');
    }


   public function data()
{
    $request = $this->request;
    $schoolinfo = getSchoolInfo();
    $campusid = $this->session->get('member_campusid');

    $keyword = $request->getPost('search')['value'] ?? '';

    $builder = $this->db->table('sections')->where('system_id', $schoolinfo->system_id);
    if ($keyword) {
        $builder->like('section_name', $keyword);
    }

    $total = $builder->countAllResults(false);

    $results = $builder
        ->orderBy('section_id', 'asc')
        ->limit($request->getPost('length'), $request->getPost('start'))
        ->get()
        ->getResult();

    $data = [];
    $start = (int)$request->getPost('start');
    $count = $start + 1;

    foreach ($results as $row) {
        $data[] = [
            'sno' => $count++,
            'section_name' => $row->section_name,
            'short_name'   => $row->short_name,
            'section_id'   => $row->section_id,
            'status'       => $row->status ?? 0,

            'id'           => $row->section_id // internal use (e.g., toggle)
        ];
    }

    return $this->response->setJSON([
        'draw'            => $request->getPost('draw'),
        'recordsTotal'    => $total,
        'recordsFiltered' => $total,
        'data'            => $data,
    ]);
}





    public function add()
    {
        check_permission('admin-add-class');
        $schoolinfo = getSchoolInfo();

        $section_info = $this->db
            ->table('sections')
            ->where('system_id', $schoolinfo->system_id)
            ->where('status', 1)
            ->get()
            ->getRow();

        $info = $this->db->table('sections')->where('system_id', $schoolinfo->system_id)->get()->getResult();

        return view('admin/sections_edit', ['info' => $info, 'classes_info' => $section_info]);
    }


  public function edit()
    {
        check_permission('admin-edit-section');
        $id = (int) $this->request->getGet('id');

        $info = $this->db->table('sections')->where('section_id', $id)->get()->getRow();
        return view('admin/sections_edit', ['info' => $info]);
    }

   
  public function save()
    {
        $request = $this->request;
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d');
        $schoolinfo = getSchoolInfo();
        $rowscount = $request->getPost('rowscount');

         if (!is_array($rowscount)) {
        return json_response(['success' => false, 'msg' => 'Invalid row input']);
    }

        foreach ($rowscount as $i) {
    $id = (int)$request->getPost('id' . $i);
            $data = [
                'section_name' => trim($request->getPost('section_name' . $i)),
                'short_name'   => trim($request->getPost('short_name' . $i)),
                'system_id'    => $schoolinfo->system_id,
                'user_id'          => $user_id,
            'status'           => 1,
            'created_date'     => $date
            ];

            if ($id === 0) {
                $this->db->table('sections')->insert($data);
            } else {
                $this->db->table('sections')->where('section_id', $id)->update($data);
            }

            $this->db->transComplete();
        }

        $class_section_info = $this->db->query(
            'SELECT * FROM class_section WHERE section_id IN (SELECT section_id FROM sections WHERE system_id = ' . $schoolinfo->system_id . ')'
        )->getRow();

        if (empty($class_section_info->cls_sec_id)) {
            return $this->response->setJSON(['cls_sec_id' => false, 'msg' => 'Class Section Success']);
        }

        return json_response(['success' => true, 'msg' => 'Class Section Success']);
    }



  public function toggleStatus()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setStatusCode(403)->setJSON(['success' => false, 'msg' => 'Invalid request']);
    }

    $id = (int) $this->request->getPost('id');
    $status = (int) $this->request->getPost('status');

    $updated = $this->db->table('sections')->where('section_id', $id)->update(['status' => $status]);

    return $this->response->setJSON([
        'success' => $updated,
        'msg' => $updated ? 'Status updated' : 'Failed to update status'
    ]);
}
   
}
