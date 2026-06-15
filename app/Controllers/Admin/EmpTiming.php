<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class EmpTiming extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = \Config\Services::session();
    }

    public function index()
    {
        check_permission('admin-add-teacher-section');

        return view('admin/emp_timing_edit', []);
    }

    public function data(): ResponseInterface
    {
        check_permission('admin-add-teacher-section');

        $campusid = (int) $this->session->get('member_campusid');
        if ($campusid <= 0) {
            return $this->response->setBody(
                "<div class='alert alert-warning mb-0'>Campus not selected. Choose a campus from the header and try again.</div>"
            );
        }

        $empInfo = $this->db->table('users')
            ->select('id, first_name, last_name')
            ->where('campus_id', $campusid)
            ->where('status', 1)
            ->orderBy('first_name', 'ASC')
            ->orderBy('last_name', 'ASC')
            ->get()
            ->getResult();

        $empDetailsinfo = [];
        foreach ($empInfo as $emp) {
            $empDetailsinfo[] = [
                'user_id' => (int) $emp->id,
                'name'    => trim($emp->first_name . ' ' . $emp->last_name),
            ];
        }

        if ($empDetailsinfo === []) {
            return $this->response->setBody(
                "<div class='alert alert-info mb-0'>No active employees found for this campus.</div>"
            );
        }

        $userIds = array_column($empDetailsinfo, 'user_id');
        $timingsByUserDay = [];
        $timingRows = $this->db->table('emp_timings')
            ->whereIn('user_id', $userIds)
            ->get()
            ->getResult();

        foreach ($timingRows as $row) {
            $timingsByUserDay[(int) $row->user_id][$row->dayname] = $row;
        }

        $daysName = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $firstUserId = $empDetailsinfo[0]['user_id'];

        $data = "<div class='table-responsive'><table class='table table-bordered'><tr><th></th>";

        foreach ($daysName as $day) {
            $dayEsc = esc($day);
            $data .= '<th style="width: 132px;">'
                . '<input type="hidden" name="dayname[]" value="' . $dayEsc . '"/>'
                . $dayEsc . '<br>'
                . 'Set Off <input type="checkbox" class="emp-timing-set-off" data-day="' . $dayEsc . '"><br>'
                . 'Set to column <input type="checkbox" class="emp-timing-set-col" data-day="' . $dayEsc . '" data-first-user="' . $firstUserId . '">'
                . '</th>';
        }
        $data .= '</tr>';

        foreach ($empDetailsinfo as $employee) {
            $uid = $employee['user_id'];
            $nameEsc = esc($employee['name']);
            $data .= '<tr><th>' . $nameEsc
                . '<input type="hidden" name="user_id[]" value="' . $uid . '" /><br>'
                . 'Set to Row <input type="checkbox" class="emp-timing-set-row" data-user-id="' . $uid . '">'
                . '</th>';

            foreach ($daysName as $value) {
                $empTimingsInfo = $timingsByUserDay[$uid][$value] ?? null;
                $checkin = esc($empTimingsInfo->checkin ?? '');
                $checkout = esc($empTimingsInfo->checkout ?? '');
                $valueEsc = esc($value);

                $data .= '<td>
                        <div class="input-group clockpicker" data-bs-placement="left" data-align="top" data-autoclose="true">
                            <input type="text" class="form-control clockpicker_' . $valueEsc . ' clockpicker_' . $uid . '" placeholder="Check In" name="' . $valueEsc . '_' . $uid . '_checkin_date" id="' . $valueEsc . '_' . $uid . '_checkin_date" value="' . $checkin . '">
                            <span class="input-group-text btn btn-secondary"><span class="far fa-clock"></span></span>
                        </div>
                        <div class="input-group clockpicker" data-bs-placement="left" data-align="top" data-autoclose="true">
                            <input type="text" class="form-control clockpickercheckout_' . $valueEsc . ' clockpickercheckout_' . $uid . '" placeholder="Check Out" name="' . $valueEsc . '_' . $uid . '_checkout_date" id="' . $valueEsc . '_' . $uid . '_checkout_date" value="' . $checkout . '">
                            <span class="input-group-text btn btn-secondary"><span class="far fa-clock"></span></span>
                        </div>
                    </td>';
            }
            $data .= '</tr>';
        }

        $data .= '</table></div>';

        return $this->response->setBody($data);
    }

    public function add()
    {
        check_permission('admin-add-teacher-section');

        return view('admin/emp_timing_edit', []);
    }

    public function edit()
    {
        check_permission('admin-edit-teacher-subject');
        $id = (int) $this->request->getGet('id');
        $campusid = $this->session->get('member_campusid');

        $info = $this->db->table('teacher_subjects')->where('sst', $id)->where('campus_id', $campusid)->get()->getRow();
        $infoteachers = $this->db->table('teachers')->where('campus_id', $campusid)->get()->getResult();
        $classinfo = $this->db->table('classes')->get()->getResult();
        $subjectinfo = $this->db->table('allsubject')->get()->getResult();

        $template_data = [
            'info' => $info,
            'infoteachers' => $infoteachers,
            'classinfo' => $classinfo,
            'subjectinfo' => $subjectinfo,
        ];

        return view('admin/school_timing_edit', $template_data);
    }

    public function save(): ResponseInterface
    {
        check_permission('admin-add-teacher-section');
        $request = $this->request;
        $user_ids = $request->getPost('user_id');
        $days = $request->getPost('dayname');

        if (!$user_ids || !$days) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Missing users or days']);
        }

        $this->db->table('emp_timings')->whereIn('user_id', $user_ids)->delete();

        $this->db->transStart();

        foreach ($days as $day) {
            foreach ($user_ids as $user_id) {
                $checkin = $request->getPost($day . '_' . $user_id . '_checkin_date');
                $checkout = $request->getPost($day . '_' . $user_id . '_checkout_date');
                $this->db->table('emp_timings')->insert([
                    'user_id'  => $user_id,
                    'dayname'  => $day,
                    'checkin'  => $checkin,
                    'checkout' => $checkout,
                ]);
            }
        }

        $this->db->transComplete();

        return $this->response->setJSON(['success' => true, 'msg' => 'Add Employee Timing Success']);
    }

    public function delete(): ResponseInterface
    {
        check_permission('admin-del-user');
        $id = (int) $this->request->getGet('id');

        $this->db->transStart();
        $this->db->table('teacher_subjects')->where('sst', $id)->delete();
        $this->db->transComplete();

        return $this->response->setJSON(['success' => true, 'msg' => 'Delete Classes Success']);
    }
}
