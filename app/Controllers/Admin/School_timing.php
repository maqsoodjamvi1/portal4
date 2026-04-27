<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class School_timing extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url']);
        check_permission('admin-school-timing');
    }

    public function index()
    {
        return view('admin/school_timing', $this->template_data);
    }

     public function data()
    {
        $campusid = $this->session->get('member_campusid');
        $school_timing_type_id = $this->request->getPost('school_timing_type_id');

        // 1. Get sections & class/section names
        $sectionsinfo = $this->db->table('class_section')
            ->where(['campus_id' => $campusid, 'status' => 1])
            ->get()->getResult();

        if (empty($sectionsinfo)) {
            return $this->response->setBody("<div class='btn btn-danger'>Please add class sections to add school timing.</div>");
        }

        $sectionsclassinfo = [];
        foreach ($sectionsinfo as $section) {
            $classinfo = $this->db->table('classes')->where('class_id', $section->class_id)->get()->getRow();
            $sectioninfo = $this->db->table('sections')->where('section_id', $section->section_id)->get()->getRow();
            $sectionsclassinfo[] = [
                'section_id' => $section->cls_sec_id,
                'sectionclassname' => $classinfo->class_name . " (" . $sectioninfo->section_name . ")"
            ];
        }

        // 2. Prepare all school timings, indexed by day/section
        $schoolTimingsInfo = [];
        $timings = $this->db->table('school_timings')
            ->where('type_id', $school_timing_type_id)
            ->get()->getResult();
        foreach ($timings as $row) {
            $schoolTimingsInfo[$row->dayname][$row->cls_sec_id] = $row;
        }

        // 3. Pass all to view
        return view('admin/partials/school_timing_table', [
            'sectionsclassinfo' => $sectionsclassinfo,
            'schoolTimingsInfo' => $schoolTimingsInfo,
            'school_timing_type_id' => $school_timing_type_id,
        ]);
    }

    public function add()
    {
        check_permission('admin-add-timetable');
        $campusid = $this->session->get('member_campusid');

        $this->template_data['info'] = $this->db->table('school_timings')->get()->getResultArray();

        $infoschooltimingtypes = $this->db->table('school_timing_types')
            ->where('campus_id', $campusid)
            ->get()->getResultArray();

        if (empty($infoschooltimingtypes)) {
            return $this->response->setBody("<div class='alert alert-danger'>Click Here To Set School Timing Type Before Adding School Timings <a href='/admin.php#/school_timming_type?m=add'>School Timing Type</a></div>");
        }

        $this->template_data['infoschooltimingtypes'] = $infoschooltimingtypes;
        $this->template_data['sectionsclassinfo'] = userClassSections();
        $this->template_data['subjectinfo'] = $this->db->table('allsubject')->get()->getResult();

        return view('admin/school_timing_edit', $this->template_data);
    }

    public function save()
    {
        check_permission('admin-add-timetable');

        $campus_id = (int) $this->session->get('member_campusid');
        $school_timing_type_id = $this->request->getPost('school_timing_type_id');
        $section_ids = $this->request->getPost('section_id');
        $days = $this->request->getPost('dayname');

        $cls_sec_List = implode(', ', $section_ids);
        $this->db->query("DELETE FROM school_timings WHERE cls_sec_id IN($cls_sec_List) AND type_id={$school_timing_type_id}");

        $this->db->transBegin();

        foreach ($days as $day) {
            foreach ($section_ids as $section_id) {
                $checkintime = $this->request->getPost("{$day}_{$section_id}_checkin_date");
                $checkouttime = $this->request->getPost("{$day}_{$section_id}_checkout_date");

                $data = [
                    'cls_sec_id' => $section_id,
                    'dayname' => $day,
                    'checkin_timing' => $checkintime,
                    'checkout_timing' => $checkouttime,
                    'type_id' => $school_timing_type_id
                ];

                $this->db->table('school_timings')->insert($data);
            }
        }

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            return $this->response->setJSON(['success' => false, 'msg' => 'Failed to save school timing']);
        }

        $this->db->transCommit();
        return $this->response->setJSON(['success' => true, 'msg' => 'Add School Timing Success']);
    }

    public function delete()
    {
        check_permission('admin-del-user');
        $id = (int) $this->request->getGet('id');

        $this->db->transBegin();
        $this->db->table('teacher_subjects')->where('sst', $id)->delete();
        $this->db->transComplete();

        return $this->response->setJSON(['success' => true, 'msg' => 'Delete Classes Success']);
    }
}
// end file
