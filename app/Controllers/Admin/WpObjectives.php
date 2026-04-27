<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use stdClass;

class WpObjectives extends BaseController
{
    protected $db;

    public function __construct()
    {
        check_permission('admin-wp-objectives');
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        return view('admin/wp_objectives', $this->template_data ?? []);
    }

    public function data()
    {
        $response = new stdClass();
        $response->draw = $this->request->getPost('draw');
        $schoolinfo = getSchoolInfo();

        $keyword = $this->request->getPost('search')['value'] ?? '';

        $builder = $this->db->table('wp_objectives A');
        $builder->select('count(A.obj_id) as ccount', false);
        $builder->where('A.system_id', $schoolinfo->system_id);
        if (!empty($keyword)) {
            $builder->where('A.objective', $keyword);
        }
        $q = $builder->get()->getRow();
        $response->recordsTotal = $q->ccount;

        $builder = $this->db->table('wp_objectives A');
        $builder->select('A.*');
        $builder->where('A.system_id', $schoolinfo->system_id);
        if (!empty($keyword)) {
            $builder->where('A.objective', $keyword);
        }
        $builder->orderBy('A.obj_id', 'desc');
        $builder->limit($this->request->getPost('length'), $this->request->getPost('start'));
        $results = $builder->get()->getResult();

        $response->recordsFiltered = $response->recordsTotal;
        $response->data = [];

        foreach ($results as $row) {
            $response->data[] = [
                'id' => $row->obj_id,
                'objective' => $row->objective
            ];
        }

        return $this->response->setJSON($response);
    }

    public function add()
    {
        check_permission('admin-add-objectives');
        $schoolinfo = getSchoolInfo();

        $info = $this->db->table('wp_objectives')
            ->where('system_id', $schoolinfo->system_id)
            ->get()
            ->getResult();

        $this->template_data['info'] = $info;
        return view('admin/wp_objective_edit', $this->template_data);
    }

    public function edit()
    {
        check_permission('admin-edit-objectives');
        $id = intval($this->request->getGet('id'));

        $info = $this->db->table('wp_objectives')->where('obj_id', $id)->get()->getRow();
        $this->template_data['info'] = $info;

        return view('admni/wp_objective_edit', $this->template_data);
    }

    public function save()
    {
        $user_id = session('member_userid');
        $date = date('Y-m-d H:i:s');
        $schoolinfo = getSchoolInfo();
        $rowscount = $this->request->getPost('rowscount');

        for ($i = 0; $i < count($rowscount); $i++) {
            $id = intval($this->request->getPost('id' . $i));
            $objective = trim($this->request->getPost('objective' . $i));

            $this->db->transStart();

            if ($id === 0) {
                $data = [
                    'objective' => $objective,
                    'system_id' => $schoolinfo->system_id,
                    'user_id' => $user_id,
                    'created_date' => $date
                ];
                $this->db->table('wp_objectives')->insert($data);
            } else {
                $data = [
                    'objective' => $objective,
                    'system_id' => $schoolinfo->system_id,
                    'user_id' => $user_id,
                    'updated_date' => $date
                ];
                $this->db->table('wp_objectives')->where('obj_id', $id)->update($data);
            }

            $this->db->transComplete();
        }

        return $this->response->setJSON(['success' => true, 'msg' => 'Add Objective Success']);
    }

    public function delete()
    {
        check_permission('admin-del-class');
        $id = intval($this->request->getGet('id'));

        $this->db->transStart();
        $this->db->table('classes')->where('class_id', $id)->delete();
        $this->db->transComplete();

        return $this->response->setJSON(['success' => true, 'msg' => 'Delete Classes Success']);
    }
}
