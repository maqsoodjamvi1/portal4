<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use stdClass;

class WpSubjectsObjectives extends BaseController
{
    protected $db;

    public function __construct()
    {
        check_permission('admin-wp-subjects-objectives');
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        return view('admin/wp_subjects_objectives', $this->template_data ?? []);
    }

    public function data()
    {
        $response = new stdClass();
        $response->draw = $this->request->getPost('draw');
        $campusid = session('member_campusid');
        $schoolinfo = getSchoolInfo();

        $keyword = $this->request->getPost('search')['value'] ?? '';

        $builder = $this->db->table('section_subjects A');
        $builder->select('count(A.sec_sub_id) as ccount', false);
        $builder->where("(A.cls_sec_id IN(select cls_sec_id from class_section where status=1 AND campus_id=" . $this->db->escape($campusid) . "))");
        $q = $builder->get()->getRow();
        $response->recordsTotal = $q->ccount;

        $builder = $this->db->table('section_subjects A');
        $builder->select('A.*');
        $builder->where("(A.cls_sec_id IN(select cls_sec_id from class_section where status=1 AND campus_id=" . $this->db->escape($campusid) . "))");
        $builder->orderBy('A.sec_sub_id', 'desc');
        $builder->limit($this->request->getPost('length'), $this->request->getPost('start'));
        $results = $builder->get()->getResult();

        $response->recordsFiltered = $response->recordsTotal;
        $response->data = [];

        foreach ($results as $row) {
            $classSection = getClassSection($row->cls_sec_id);
            $subject = $this->db->table('allsubject')->where('sid', $row->subject_id)->get()->getRow();

            $response->data[] = [
                'id' => $row->sec_sub_id,
                'section_name' => $classSection['sectionclassname'] ?? '',
                'short_name' => $subject->subject_name ?? '',
            ];
        }

        return $this->response->setJSON($response);
    }

    public function data2()
    {
        $campusid = session('member_campusid');
        $schoolinfo = getSchoolInfo();
        $class_id = $this->request->getPost('class_id');

        // Get all unique subject IDs for this class/section
        $subjectsinfo = $this->db->query(
            "SELECT DISTINCT(subject_id) FROM section_subjects 
             WHERE cls_sec_id IN (
                SELECT cls_sec_id FROM class_section 
                WHERE class_id=? AND status=1 AND campus_id=?
             ) 
             AND status=1", 
             [$class_id, $campusid]
        )->getResult();

        $subjectclassinfo = [];
        foreach ($subjectsinfo as $subject) {
            $subjectRow = $this->db->table('allsubject')->where('sid', $subject->subject_id)->get()->getRow();
            if ($subjectRow) {
                $subjectclassinfo[] = [
                    'subject_id' => $subjectRow->sid,
                    'subject_name' => $subjectRow->subject_name,
                ];
            }
        }

        // Get all objectives
        $wp_objectives_info = $this->db->table('wp_objectives')
            ->where('system_id', $schoolinfo->system_id)
            ->get()->getResult();

        // Get all existing status for this class_id (to avoid DB in view)
        $wp_sub_objectives = $this->db->table('wp_sub_objectives')
            ->where('class_id', $class_id)
            ->get()->getResult();

        $existing_objectives = [];
        foreach ($wp_sub_objectives as $obj) {
            $existing_objectives[$obj->subject_id][$obj->obj_id] = $obj->status;
        }

        return view('admin/wp_subjects_objectives_html', [
            'subjectclassinfo' => $subjectclassinfo,
            'wp_objectives_info' => $wp_objectives_info,
            'class_id' => $class_id,
            'existing_objectives' => $existing_objectives,
        ]);
    }

    public function updateWpSubjectObjective()
    {
        $campusid = session('member_campusid');
        $status = $this->request->getPost('status');
        $section_subject_ids = $this->request->getPost('section_subject_id');
        $parts = explode('_', $section_subject_ids);

        $user_id = session('member_userid');
        $date = date('Y-m-d');

        $class_id = $parts[0];
        $subject_id = $parts[1];
        $obj_id = $parts[2];

        $exists = $this->db->table('wp_sub_objectives')
            ->where(['class_id' => $class_id, 'subject_id' => $subject_id, 'obj_id' => $obj_id])
            ->get()->getRow();

        if ($exists) {
            $this->db->table('wp_sub_objectives')
                ->where(['class_id' => $class_id, 'subject_id' => $subject_id, 'obj_id' => $obj_id])
                ->update([
                    'user_id' => $user_id,
                    'updated_date' => $date,
                    'status' => $status
                ]);
        } else {
            $this->db->table('wp_sub_objectives')->insert([
                'class_id' => $class_id,
                'subject_id' => $subject_id,
                'obj_id' => $obj_id,
                'user_id' => $user_id,
                'created_date' => $date,
                'status' => 1
            ]);
        }

        return $this->response->setJSON(['success' => true, 'msg' => 'Add Subject Objective Success']);
    }

    public function add()
    {
        check_permission('admin-add-wp-subjects-objectives');
        $campusid = session('member_campusid');
        $schoolinfo = getSchoolInfo();

        $classesinfo = $this->db->table('classes')
            ->where('system_id', $schoolinfo->system_id)
            ->get()->getResult();

        $subjectinfo = $this->db->table('allsubject')->get()->getResult();

        $this->template_data['classesinfo'] = $classesinfo;
        $this->template_data['subjectinfo'] = $subjectinfo;

        return view('admin/wp_subjects_objectives_edit', $this->template_data);
    }
}
