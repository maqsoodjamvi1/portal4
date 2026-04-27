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
        check_permission('admin-emp-timing');
        // You may populate $template_data as needed
        return view('admin/emp_timing_edit', []); // Adjust as per your data needs
    }

    public function data(): ResponseInterface
    {
        $campusid = $this->session->get('member_campusid');
        $empInfo = $this->db->table('users')
            ->where('campus_id', $campusid)
            ->where('status', 1)
            ->get()->getResult();

        $empDetailsinfo = [];
        foreach ($empInfo as $emp) {
            $empDetailsinfo[] = [
                'user_id' => $emp->id,
                'name' => $emp->first_name . " " . $emp->last_name,
            ];
        }

        $daysName = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
        $data = "<table class='table'><tr><th></th>";

        foreach ($daysName as $day) {
            $firstUserId = $empDetailsinfo[0]['user_id'] ?? 0;
            $data .= '<th style="width: 132px;">
                <input type="hidden" name="dayname[]" value="'.$day.'"/>' . $day . '<br>
                Set Off <input type="checkbox" id="setclockoff_'.$day.'"><br>
                Set to column <input type="checkbox" id="setclock_'.$day.'">
                <script>
                $(function(){
                    $("#setclock_'.$day.'").click(function(){
                        if (this.checked) {
                            var checkintime = $("#'.$day.'_'.$firstUserId.'_checkin_date").val();
                            $(".clockpicker_'.$day.'").val(checkintime);
                            var checkouttime = $("#'.$day.'_'.$firstUserId.'_checkout_date").val();
                            $(".clockpickercheckout_'.$day.'").val(checkouttime);
                        }
                    });
                    $("#setclockoff_'.$day.'").click(function(){
                        if (this.checked) {
                            $(".clockpicker_'.$day.'").val("08:00");
                            $(".clockpickercheckout_'.$day.'").val("08:00");
                        }
                    });
                });
                </script>
            </th>';
        }
        $data .= '</tr>';

        if (!empty($empDetailsinfo)) {
            foreach ($empDetailsinfo as $employee) {
                $data .= '<tr><th>'.$employee['name'].'<input type="hidden" name="user_id[]" value="'.$employee['user_id'].'" /><br>
                    Set to Row <input type="checkbox" id="setclock_'.$employee['user_id'].'">
                    <script>
                    $(function(){
                        $("#setclock_'.$employee['user_id'].'").click(function(){
                            if (this.checked) {
                                var checkintime = $("#Monday_'.$employee['user_id'].'_checkin_date").val();
                                $(".clockpicker_'.$employee['user_id'].'").val(checkintime);
                                var checkouttime = $("#Monday_'.$employee['user_id'].'_checkout_date").val();
                                $(".clockpickercheckout_'.$employee['user_id'].'").val(checkouttime);
                            }
                        });
                    });
                    </script>
                </th>';
                foreach ($daysName as $value) {
                    $emp_timings_info = $this->db->table('emp_timings')
                        ->where('dayname', $value)
                        ->where('user_id', $employee['user_id'])
                        ->get()->getRow();
                    $checkin = $emp_timings_info->checkin ?? '';
                    $checkout = $emp_timings_info->checkout ?? '';
                    $data .= '<td>
                        <div class="input-group clockpicker" data-placement="left" data-align="top" data-autoclose="true">
                            <input type="text" class="form-control clockpicker_'.$value.' clockpicker_'.$employee['user_id'].'" placeholder="Check In" name="'.$value.'_'.$employee['user_id'].'_checkin_date" id="'.$value.'_'.$employee['user_id'].'_checkin_date" value="'.$checkin.'">
                            <span class="input-group-addon btn btn-default"><span class="far fa-clock"></span></span>
                        </div>
                        <div class="input-group clockpicker" data-placement="left" data-align="top" data-autoclose="true">
                            <input type="text" class="form-control clockpickercheckout_'.$value.' clockpickercheckout_'.$employee['user_id'].'" placeholder="Check Out" name="'.$value.'_'.$employee['user_id'].'_checkout_date" id="'.$value.'_'.$employee['user_id'].'_checkout_date" value="'.$checkout.'">
                            <span class="input-group-addon btn btn-default"><span class="far fa-clock"></span></span>
                        </div>
                    </td>';
                }
                $data .= '</tr>';
            }
        }
        $data .=  '</table>
            <script>
                $(function() { $(".clockpicker").clockpicker(); });
            </script>';
        return $this->response->setJSON($data);
    }

    public function add()
    {
        check_permission('admin-add-timetable');
        $campusid = $this->session->get('member_campusid');

        $info = $this->db->table('school_timings')->get()->getResultArray();
        $infoschooltimingtypes = $this->db->table('school_timing_types')->get()->getResultArray();
        $sectionsclassinfo = userClassSections();
        $subjectinfo = $this->db->table('allsubject')->get()->getResult();

        $template_data = [
            'info' => $info,
            'infoschooltimingtypes' => $infoschooltimingtypes,
            'sectionsclassinfo' => $sectionsclassinfo,
            'subjectinfo' => $subjectinfo,
        ];

        return view('admin/emp_timing_edit', $template_data);
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
        check_permission('admin-add-timetable');
        $campus_id = (int) $this->session->get('member_campusid');
        $request = $this->request;
        $user_ids = $request->getPost('user_id');
        $days = $request->getPost('dayname');

        if (!$user_ids || !$days) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Missing users or days']);
        }

        // Delete previous timings for these users
        $this->db->table('emp_timings')->whereIn('user_id', $user_ids)->delete();

        $this->db->transStart();

        foreach ($days as $day) {
            foreach ($user_ids as $user_id) {
                $checkin = $request->getPost($day . "_" . $user_id . "_checkin_date");
                $checkout = $request->getPost($day . "_" . $user_id . "_checkout_date");
                $data = [
                    'user_id' => $user_id,
                    'dayname' => $day,
                    'checkin' => $checkin,
                    'checkout' => $checkout,
                ];
                $this->db->table('emp_timings')->insert($data);
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
