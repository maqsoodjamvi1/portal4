<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use DateTime;
use DateInterval;
use DatePeriod;

class DefaulterMessage extends BaseController
{
    protected $db;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->db = \Config\Database::connect();
        check_permission('admin-defaulter-message');
    }

    public function index()
    {
        $sessionId = session('member_sessionid');
        $schoolInfo = getSchoolInfo();

        $sessionInfo = $this->db->table('academic_session')
                                ->where('session_id', $sessionId)
                                ->get()
                                ->getRow();

        $months = $this->generateMonthOptions($sessionInfo->start_date, $sessionInfo->end_date);

        $feeTypes = $this->db->table('fee_type')
                             ->where('system_id', $schoolInfo->system_id)
                             ->get()
                             ->getResult();

        return view('admin/defaulter_message_edit', [
            'months' => $months,
            'fee_types' => $feeTypes
        ]);
    }

    protected function generateMonthOptions($start, $end)
    {
        $begin = new DateTime($start);
        $end = (new DateTime($end))->modify('+1 month');

        $period = new DatePeriod($begin, DateInterval::createFromDateString('1 month'), $end);
        $months = [];

        foreach ($period as $dt) {
            $months[] = [
                'id'    => $dt->format('m/Y'),
                'value' => $dt->format('M/Y')
            ];
        }

        return $months;
    }

    public function data()
    {
        $campusId = session('member_campusid');
        $sessionId = session('member_sessionid');
        $schoolInfo = getSchoolInfo();
        $feeTypeId = $this->request->getPost('fee_type_id');
        $feeMonth = $this->request->getPost('month');

        $sessionInfo = $this->db->table('academic_session')
                                ->where('session_id', $sessionId)
                                ->get()
                                ->getRow();

        $monthList = $this->getMonthList($sessionInfo->start_date, $sessionInfo->end_date);
        $monthdate = $feeMonth ? '"' . $feeMonth . '"' : $monthList;

        $defaultMessage = '';
        $campusInfo = $this->db->table('campus')->where('campus_id', $campusId)->get()->getRow();
        if ($campusInfo) {
            $defaultMessage = $campusInfo->student_fee_sms;
        }

        $students = $this->db->table('student_class')
            ->whereIn('student_id', static function ($builder) use ($campusId) {
                return $builder->select('student_id')->from('students')->where('campus_id', (int) $campusId);
            })
            ->where(['status' => 1, 'session_id' => (int) $sessionId])
            ->get()
            ->getResult();

        $output = view('partials/defaulter_message_form', [
            'students' => $students,
            'feeTypeId' => $feeTypeId,
            'monthdate' => $monthdate,
            'defaultMessage' => $defaultMessage,
            'campusId' => $campusId,
            'schoolInfo' => $schoolInfo
        ]);

        return $this->response->setBody($output);
    }

    protected function getMonthList($start, $end)
    {
        $begin = new DateTime($start);
        $end = (new DateTime($end))->modify('+1 month');

        $period = new DatePeriod($begin, DateInterval::createFromDateString('1 month'), $end);
        $months = '';

        foreach ($period as $dt) {
            $months .= '"' . $dt->format('m/Y') . '",';
        }

        return rtrim($months, ',');
    }

    public function save()
    {
        helper('text');
        $userId = session('member_userid');
        $campusId = session('member_campusid');
        $sessionId = session('member_sessionid');
        $template = $this->request->getPost('message');
        $contacts = $this->request->getPost('contacts');
        $studentIds = $this->request->getPost('student_id');

        if (empty($contacts)) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Select Contact Type']);
        }

        $this->db->transStart();

        foreach ($studentIds as $studentId) {
            $feeTotal = $this->getStudentUnpaidFee($studentId);
            if ($feeTotal > 0) {
                $studentInfo = $this->getStudentInfo($studentId);
                $parentInfo = $this->getParentInfo($studentId);
                $studentClass = $this->getStudentClass($studentId);

                foreach ($contacts as $type) {
                    $mobile = $parentInfo->{$type} ?? '';
                    if (!empty($mobile)) {
                        $parsedMessage = $this->parseTemplate($template, [
                            'first_name' => $studentInfo->first_name,
                            'last_name' => $studentInfo->last_name,
                            'father_name' => $parentInfo->f_name,
                            'class' => $studentClass,
                            'balance' => $feeTotal,
                            'date' => date('Y-m-d')
                        ]);

                        $this->db->table('sms')->insert([
                            'mobile' => $mobile,
                            'message' => trim($parsedMessage),
                            'campus_id' => $campusId,
                            'parent_id' => $parentInfo->parent_id,
                            'status' => 0,
                            'user_id' => $userId,
                            'created_date' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
        }

        $this->db->transComplete();

        return $this->response->setJSON(['success' => true, 'msg' => 'Add Message Success']);
    }

    protected function getStudentUnpaidFee($studentId)
    {
        $row = $this->db->table('fee_chalan')
            ->select('SUM(amount - discount) AS feeTotal')
            ->where(['student_id' => $studentId, 'status' => 'unpaid'])
            ->get()->getRow();
 
        return $row->feeTotal ?? 0;
    }

    protected function getStudentInfo($studentId)
    {
        return $this->db->table('students')->where('student_id', $studentId)->get()->getRow();
    }

    protected function getParentInfo($studentId)
    {
        $student = $this->db->table('students')->select('parent_id')->where('student_id', (int) $studentId)->get()->getRow();
        if (! $student) {
            return null;
        }

        return $this->db->table('parents')->where('parent_id', (int) $student->parent_id)->get()->getRow();
    }

    protected function getStudentClass($studentId)
    {
        $sc = $this->db->table('student_class')->where('student_id', $studentId)->get()->getRow();
        $cs = $this->db->table('class_section')->where('cls_sec_id', $sc->cls_sec_id)->get()->getRow();
        $class = $this->db->table('classes')->where('class_id', $cs->class_id)->get()->getRow();
        $section = $this->db->table('sections')->where('section_id', $cs->section_id)->get()->getRow();

        return $class->class_name . '(' . $section->section_name . ')';
    }

    protected function parseTemplate(string $template, array $data): string
    {
        foreach ($data as $key => $val) {
            $template = str_replace('{' . $key . '}', $val, $template);
        }
        return $template;
    }

     public function parent_sms()
    {
        $campusId = session('member_campusid');
        $sessionId = session('member_sessionid');

        $defaulterMessage = '';
        $campusInfo = $this->db->table('campus')->where('campus_id', $campusId)->get()->getRow();

        if ($campusInfo) {
            $defaulterMessage = $campusInfo->family_fee_sms;
        }

        $parents = $this->db->query(
            "SELECT * FROM parents WHERE campus_id = $campusId AND parent_id IN (
                SELECT parent_id FROM students WHERE campus_id = $campusId AND status = 1
            )"
        )->getResult();

        $defaulters = [];

        foreach ($parents as $parent) {
            $feeInfo = $this->db->query(
                "SELECT SUM(amount - discount) AS feeTotal FROM fee_chalan WHERE status = 'unpaid' AND student_id IN (
                    SELECT student_id FROM students WHERE parent_id = $parent->parent_id AND status = 1
                )"
            )->getRow();

            if ($feeInfo && $feeInfo->feeTotal > 0) {
                $defaulters[] = [
                    'f_name'     => $parent->f_name,
                    'parent_id'  => $parent->parent_id,
                    'unpaid_fee' => $feeInfo->feeTotal,
                ];
            }
        }

        return view('admin/parent_message_edit', [
            'defaulter_fee_sms' => $defaulterMessage,
            'campusSections'    => getAllClassSection(),
            'detaulterArr'      => $defaulters
        ]);
    }

    public function saveparent()
    {
        $userId = session('member_userid');
        $campusId = session('member_campusid');
        $template = $this->request->getPost('message');
        $contacts = $this->request->getPost('contacts');
        $parentIds = $this->request->getPost('parent_id');

        if (empty($contacts)) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Select Contact Type']);
        }

        $this->db->transStart();

        foreach ($parentIds as $parentId) {
            $feeInfo = $this->db->query(
                "SELECT SUM(amount - discount) AS feeTotal FROM fee_chalan WHERE status = 'unpaid' AND student_id IN (
                    SELECT student_id FROM students WHERE parent_id = $parentId AND status = 1
                )"
            )->getRow();

            if ($feeInfo && $feeInfo->feeTotal > 0) {
                $parentInfo = $this->db->table('parents')->where('parent_id', $parentId)->get()->getRow();

                foreach ($contacts as $type) {
                    $mobile = $parentInfo->{$type} ?? '';

                    if (!empty($mobile)) {
                        $parsedMessage = $this->parseTemplate($template, [
                            'father_name' => $parentInfo->f_name,
                            'balance'     => $feeInfo->feeTotal,
                            'date'        => date('Y-m-d')
                        ]);

                        $this->db->table('sms')->insert([
                            'mobile'       => $mobile,
                            'message'      => trim($parsedMessage),
                            'campus_id'    => $campusId,
                            'parent_id'    => $parentInfo->parent_id,
                            'status'       => 0,
                            'user_id'      => $userId,
                            'created_date' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
        }

        $this->db->transComplete();

        return $this->response->setJSON(['success' => true, 'msg' => 'Add Message Success']);
    }

    public function delete()
    {
        check_permission('admin-del-enquiry');
        $id = (int) $this->request->getGet('id');

        $this->db->transStart();
        $this->db->table('classes')->where('id', $id)->delete();
        $this->db->transComplete();

        return $this->response->setJSON(['success' => true, 'msg' => 'Delete Question Quiz Success']);
    }
}