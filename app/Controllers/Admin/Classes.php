<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Classes extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form']);
        check_permission('admin-classes');
    }

    public function index()
    {
        return view('admin/classes');
    }

    public function data()
    {
        $request = $this->request;
        $schoolinfo = getSchoolInfo();
        $campusid = $this->session->get('member_campusid');

        $keyword = $request->getPost('search')['value'] ?? '';

        $builder = $this->db->table('classes')->where('system_id', $schoolinfo->system_id);
        if ($keyword) {
            $builder->like('class_name', $keyword);
        }

        $total = $builder->countAllResults(false);

        $results = $builder
            ->orderBy('class_id', 'asc')
            ->limit($request->getPost('length'), $request->getPost('start'))
            ->get()
            ->getResult();

        $data = [];
        $start = (int)$request->getPost('start');
$count = $start + 1;

foreach ($results as $row) {
    $StudentCount = $this->db->query(
        "SELECT COUNT(*) AS totalStd FROM student_class
         WHERE status = 1 AND cls_sec_id IN (
             SELECT cls_sec_id FROM class_section WHERE class_id = {$row->class_id}
         ) AND student_id IN (
             SELECT student_id FROM students WHERE campus_id = {$campusid}
         )"
    )->getRow();

    $data[] = [
        'sno' => $count++,
        'class_name' => $row->class_name,
        'class_short_name' => $row->class_short_name,
        'class_id' => $row->class_id,
        'strength' => $StudentCount->totalStd ?? 0,
        'status' => $row->status ?? 0,
        'id' => $row->class_id // keep for internal use like toggle
    ];
}
        return $this->response->setJSON([
            'draw' => $request->getPost('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data,
        ]);
    }

    public function add()
    {
        check_permission('admin-add-class');
        $schoolinfo = getSchoolInfo();

        $classes_info = $this->db
            ->table('classes')
            ->where('system_id', $schoolinfo->system_id)
            ->where('status', 1)
            ->get()
            ->getRow();

        $info = $this->db->table('classes')->where('system_id', $schoolinfo->system_id)->get()->getResult();

        return view('admin/classes_edit', ['info' => $info, 'classes_info' => $classes_info]);
    }

    public function edit()
    {
        check_permission('admin-edit-class');
        $id = (int) $this->request->getGet('id');

        $info = $this->db->table('classes')->where('class_id', $id)->get()->getRow();
        return view('admin/classes_edit', ['info' => $info]);
    }

   
public function save()
{
    $request = $this->request;
    $user_id = session('member_userid');
    $date = date('Y-m-d H:i:s');
    $schoolinfo = getSchoolInfo();
    $rowscount = $request->getPost('rowscount');

    if (!is_array($rowscount)) {
        return json_response(['success' => false, 'msg' => 'Invalid row input']);
    }

    foreach ($rowscount as $i) {
    $id = (int)$request->getPost('id' . $i);

          $data = [
        'class_name'       => trim($request->getPost('class_name' . $i)),
        'class_short_name' => trim($request->getPost('class_short_name' . $i)),
        'system_id'        => $schoolinfo->system_id,
        'user_id'          => $user_id,
        'status'           => 1,
        'created_date'     => $date
    ];

        $this->db->transBegin();

        if ($id === 0) {
            $this->db->table('classes')->insert($data);
        } else {
            $this->db->table('classes')->where('class_id', $id)->update($data);
        }

        $this->db->transComplete();
    }

    $section_info = $this->db->table('sections')
        ->where('system_id', $schoolinfo->system_id)
        ->get()
        ->getRow();

    if (empty($section_info->section_id)) {
        return $this->response->setJSON(['section_id' => false, 'msg' => 'Class Success']);
    }

    return json_response(['success' => true, 'msg' => 'Add Class Success']);
}




public function toggleStatus()
{
    if (!$this->request->isAJAX()) {
           return $this->response->setStatusCode(403)->setJSON([
            'success' => false,
            'msg' => 'Invalid request'
        ]);
    }

    $id = (int) $this->request->getPost('id');
    $status = (int) $this->request->getPost('status');
    
   $class = $this->db->table('classes')->where('class_id', $id)->get()->getRow();
    if (!$class) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Class not found'
        ]);
    }

     $this->db->table('classes')->where('class_id', $id)->update(['status' => $status]);
    $updated = $this->db->affectedRows();

    return $this->response->setJSON([
        'success' => $updated > 0,
        'msg' => $updated > 0 ? 'Status updated' : 'Already up-to-date'
    ]);
   
}
}
