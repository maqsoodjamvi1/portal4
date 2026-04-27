<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Subjects extends BaseController
{
    protected $db;
    protected $session;

   public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form']);
        check_permission('admin-subjects');
    }

    public function index()
    {
        return view('admin/subjects');
    }


   public function data()
   {
        $request = $this->request;
        $schoolinfo = getSchoolInfo();
        $campusid = $this->session->get('member_campusid');

        $keyword = $request->getPost('search')['value'] ?? '';

        $builder = $this->db->table('allsubject')->where('system_id', $schoolinfo->system_id);
        if ($keyword) {
            $builder->like('subject_name', $keyword);
        }

        $total = $builder->countAllResults(false);

        $results = $builder
            ->orderBy('sid', 'asc')
            ->limit($request->getPost('length'), $request->getPost('start'))
            ->get()
            ->getResult();

        $data = [];
        $start = (int)$request->getPost('start');
        $count = $start + 1;

        foreach ($results as $row) {
            $data[] = [
                'sno' => $count++,
                'subject_name' => $row->subject_name,
                'subject_short_name'   => $row->subject_short_name,
                'sid'   => $row->sid,
                'status'       => $row->status ?? 0,
                'id'           => $row->sid // internal use (e.g., toggle)
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

        $subject_info = $this->db
            ->table('allsubject')
            ->where('system_id', $schoolinfo->system_id)
            ->where('status', 1)
            ->get()
            ->getRow();

        $info = $this->db->table('allsubject')->where('system_id', $schoolinfo->system_id)->get()->getResult();

        return view('admin/subjects_edit', ['info' => $info, 'subject_info' => $subject_info]);
    }


  public function edit()
    {
        check_permission('admin-edit-subject');
        $id = (int) $this->request->getGet('id');

        $info = $this->db->table('allsubject')->where('sid', $id)->get()->getRow();
        return view('admin/subjects_edit', ['info' => $info]);
    }

public function save()
{
    $user_id = $this->session->get('member_userid');
    $campusid = $this->session->get('member_campusid');
    $schoolinfo = getSchoolInfo();
    $date = date('Y-m-d H:i:s');

    $total_rows = (int) $this->request->getPost('total_rows');

    if (!$total_rows) {
        $i = 0;
        while ($this->request->getPost("subject_name{$i}") !== null) {
            $total_rows++;
            $i++;
        }
    }

    if ($total_rows === 0) {
        return $this->response->setJSON(['success' => false, 'msg' => 'No rows submitted']);
    }

    $inserted_any = false;

    for ($i = 0; $i < $total_rows; $i++) {
        $id = (int) $this->request->getPost("id{$i}");
        $subject_name = trim($this->request->getPost("subject_name{$i}"));
        $subject_short_name = trim($this->request->getPost("subject_short_name{$i}"));

        // Skip empty rows
        if ($subject_name === '' || $subject_short_name === '') {
            continue;
        }

        if ($id === 0) {
            // Insert new subject if not duplicate
            $exists = $this->db->table('allsubject')
                ->where('system_id', $schoolinfo->system_id)
                ->where('subject_name', $subject_name)
                ->countAllResults();

            if ($exists === 0) {
                $data = [
                    'subject_name'        => $subject_name,
                    'subject_short_name'  => $subject_short_name,
                    'system_id'           => $schoolinfo->system_id,
                    'user_id'             => $user_id,
                    'created_date'        => $date,
                    'status'              => 1
                ];
                $this->db->table('allsubject')->insert($data);
                $inserted_any = true;
            }
        } else {
            // Update existing subject
            $data = [
                'subject_name'        => $subject_name,
                'subject_short_name'  => $subject_short_name,
                'system_id'           => $schoolinfo->system_id,
                'user_id'             => $user_id,
                'created_date'        => $date
            ];
            $this->db->table('allsubject')->where('sid', $id)->update($data);
            $inserted_any = true;
        }
    }

    // Check for section-subject linkage
    $section_subjects_info = $this->db->query(
        'SELECT * FROM section_subjects WHERE subject_id IN 
         (SELECT sid FROM allsubject WHERE system_id = ' . (int) $schoolinfo->system_id . ')'
    )->getRow();

    if (empty($section_subjects_info) || empty($section_subjects_info->sec_sub_id)) {
        return $this->response->setJSON([
            'success' => $inserted_any,
            'msg'     => $inserted_any ? 'Subject saved, but no section-subject mapping found.' : 'No new data saved.',
            'sec_sub_id' => false
        ]);
    }

    return $this->response->setJSON([
        'success' => true,
        'msg'     => 'Subject saved successfully',
        'sec_sub_id' => $section_subjects_info->sec_sub_id
    ]);
}


    public function toggleStatus()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'msg' => 'Invalid request']);
        }

        $id = (int) $this->request->getPost('id');
        $status = (int) $this->request->getPost('status');

        $updated = $this->db->table('allsubject')->where('sid', $id)->update(['status' => $status]);

        return $this->response->setJSON([
            'success' => $updated,
            'msg' => $updated ? 'Status updated' : 'Failed to update status'
        ]);
    }
   
}
