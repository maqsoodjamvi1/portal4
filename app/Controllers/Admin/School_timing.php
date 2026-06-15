<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class School_timing extends BaseController
{
    protected $db;
    protected $session;
    protected $template_data = [];

    private const WEEK_DAYS = [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
    ];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url', 'school']);
        check_permission('admin-school-timing');
    }

    public function index()
    {
        return redirect()->to(base_url('admin/school_timing/add'));
    }

    public function data(): ResponseInterface
    {
        $campusId = (int) $this->session->get('member_campusid');

        if ($campusId <= 0) {
            return $this->response->setBody(
                '<div class="alert alert-warning mb-0">Campus is not selected.</div>'
            );
        }

        $sectionsclassinfo = $this->getSectionOptions($campusId);
        if ($sectionsclassinfo === []) {
            return $this->response->setBody(
                "<div class='alert alert-danger mb-0'>Please add class sections before adding school timing.</div>"
            );
        }

        $sectionIds = array_column($sectionsclassinfo, 'section_id');
        $schoolTimingsInfo = [];

        foreach (getSchoolTimingsForSections($sectionIds, $campusId) as $row) {
            $day = (string) ($row['dayname'] ?? '');
            $sec = (int) ($row['cls_sec_id'] ?? 0);
            if ($day !== '' && $sec > 0) {
                $schoolTimingsInfo[$day][$sec] = (object) $row;
            }
        }

        return $this->response->setBody(view('admin/partials/school_timing_table', [
            'sectionsclassinfo' => $sectionsclassinfo,
            'schoolTimingsInfo' => $schoolTimingsInfo,
        ]));
    }

    public function add()
    {
        check_permission('admin-add-timetable');

        return view('admin/school_timing_edit', $this->template_data);
    }

    public function save(): ResponseInterface
    {
        check_permission('admin-add-timetable');

        $campusId = (int) $this->session->get('member_campusid');
        $sectionIds = array_values(array_unique(array_filter(array_map('intval', (array) $this->request->getPost('section_id')))));
        $days = array_values(array_intersect((array) $this->request->getPost('dayname'), self::WEEK_DAYS));

        if ($campusId <= 0) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Campus is not selected.']);
        }

        if ($sectionIds === [] || $days === []) {
            return $this->response->setJSON(['success' => false, 'msg' => 'No timing data to save.']);
        }

        foreach ($days as $day) {
            foreach ($sectionIds as $sectionId) {
                $checkIn  = trim((string) $this->request->getPost("{$day}_{$sectionId}_checkin_date"));
                $checkOut = trim((string) $this->request->getPost("{$day}_{$sectionId}_checkout_date"));

                if ($checkIn === '' xor $checkOut === '') {
                    return $this->response->setJSON([
                        'success' => false,
                        'msg'     => "Enter both check-in and check-out for {$day} (section {$sectionId}), or leave both empty.",
                    ]);
                }
            }
        }

        $insertRows = [];
        foreach ($days as $day) {
            foreach ($sectionIds as $sectionId) {
                $checkIn  = trim((string) $this->request->getPost("{$day}_{$sectionId}_checkin_date"));
                $checkOut = trim((string) $this->request->getPost("{$day}_{$sectionId}_checkout_date"));

                if ($checkIn === '' && $checkOut === '') {
                    continue;
                }

                $row = [
                    'cls_sec_id'      => $sectionId,
                    'dayname'         => $day,
                    'checkin_timing'  => $checkIn,
                    'checkout_timing' => $checkOut,
                ];

                if (schoolTimingsHasCampusIdColumn()) {
                    $row['campus_id'] = $campusId;
                }

                $insertRows[] = $row;
            }
        }

        $this->db->transBegin();

        $deleteBuilder = $this->db->table('school_timings')->whereIn('cls_sec_id', $sectionIds);
        if (schoolTimingsHasCampusIdColumn()) {
            $deleteBuilder->where('campus_id', $campusId);
        }
        $deleteBuilder->delete();

        if ($insertRows !== []) {
            $this->db->table('school_timings')->insertBatch($insertRows);
        }

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            return $this->response->setJSON(['success' => false, 'msg' => 'Failed to save school timing.']);
        }

        $this->db->transCommit();

        return $this->response->setJSON(['success' => true, 'msg' => 'School timing saved successfully.']);
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

    /**
     * @return list<array{section_id:int, sectionclassname:string}>
     */
    private function getSectionOptions(int $campusId): array
    {
        if ($campusId <= 0) {
            return [];
        }

        $rows = $this->db->table('class_section cs')
            ->select('cs.cls_sec_id, c.class_name, s.section_name')
            ->join('classes c', 'c.class_id = cs.class_id', 'inner')
            ->join('sections s', 's.section_id = cs.section_id', 'inner')
            ->where('cs.campus_id', $campusId)
            ->where('cs.status', 1)
            ->orderBy('c.class_id', 'ASC')
            ->orderBy('s.section_id', 'ASC')
            ->get()
            ->getResult();

        $sections = [];
        foreach ($rows as $row) {
            $sections[] = [
                'section_id'       => (int) $row->cls_sec_id,
                'sectionclassname' => trim($row->class_name . ' (' . $row->section_name . ')'),
            ];
        }

        return $sections;
    }
}
