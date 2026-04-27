<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Expense_report extends BaseController
{
    protected $db;
    protected $session;
    protected $template_data = [];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url']);
        check_permission('admin-expense-reports');
    }

    /**
     * Index Page for this controller.
     */
    public function index()
    {
        return view('admin/expense_report_by_head', $this->template_data);
    }

    public function data()
    {
        $session_id = $this->session->get('member_sessionid');
        $campus_id = $this->session->get('member_campusid');
        $schoolinfo = getSchoolInfo();

        $academic_session = $this->db->table('academic_session')
            ->where('session_id', $session_id)
            ->where('system_id', $schoolinfo->system_id)
            ->get()->getRow();

        $feeTypesInfo = $this->db->table('expense_heads')
            ->where('system_id', $schoolinfo->system_id)
            ->get()->getResult();

        $start  = new \DateTime($academic_session->start_date);
        $start->modify('first day of this month');
        $end = new \DateTime($academic_session->end_date);
        $end->modify('first day of next month');
        $interval = \DateInterval::createFromDateString('1 month');
        $period = new \DatePeriod($start, $interval, $end);

        $data = '<table class="table"><tr><th style="width: 115px;"></th>';
        foreach ($feeTypesInfo as $feeType) {
            $data .= '<th>' . $feeType->head_title . '</th>';
        }
        $data .= '<th>Monthly Total</th>';
        $data .= '</tr>';

        foreach ($period as $dt) {
            $data .= '<tr><th>' . $dt->format("m/Y") . '</th>';

            foreach ($feeTypesInfo as $feeType) {
                $feeInfo = $this->db->query(
                    'SELECT SUM(amount) as total FROM expenses 
                     WHERE MONTH(created_date)=? AND YEAR(created_date)=? AND campus_id=? AND exp_head_id=?',
                    [$dt->format("m"), $dt->format("Y"), $campus_id, $feeType->exp_head_id]
                )->getRow();

                $data .= '<td>';
                if ($feeInfo->total) {
                    $data .= '<div style="color:#000;border-bottom:1px solid #000;">' . $feeInfo->total . '/- </div>';
                } else {
                    $data .= '<div style="color:#000;border-bottom:1px solid #000;">0/- </div>';
                }
                $data .= '</td>';
            }

            $feeInfoTotal = $this->db->query(
                'SELECT SUM(amount) as totalSum FROM expenses WHERE MONTH(created_date)=? AND YEAR(created_date)=? AND campus_id=?',
                [$dt->format("m"), $dt->format("Y"), $campus_id]
            )->getRow();

            $data .= '<td>';
            if ($feeInfoTotal->totalSum) {
                $data .= '<div style="color:#000;border-bottom:1px solid #000;">' . $feeInfoTotal->totalSum . '/- </div>';
            } else {
                $data .= '<div style="color:#000;border-bottom:1px solid #000;">0/- </div>';
            }
            $data .=  '</td>';
            $data .= '</tr>';
        }

        $data .= '<tr><th>Total</th>';
        foreach ($feeTypesInfo as $feeType) {
            $feeInfoTotal = $this->db->query(
                'SELECT SUM(amount) as total FROM expenses 
                 WHERE YEAR(created_date) BETWEEN ? AND ? AND campus_id=? AND exp_head_id=?',
                [$start->format("Y"), $end->format("Y"), $campus_id, $feeType->exp_head_id]
            )->getRow();

            $data .= '<td>';
            if ($feeInfoTotal->total) {
                $data .= '<div style="color:#000;border-bottom:1px solid #000;">' . $feeInfoTotal->total . '/- </div>';
            } else {
                $data .= '<div style="color:#000;border-bottom:1px solid #000;">0/- </div>';
            }
            $data .= '</td>';
        }
        $data .= '</tr>';

        $data .= '</table>';

        return $this->response->setBody($data);
    }

    public function report_by_fee_type()
    {
        check_permission('admin-add-terms-session');
        $schoolinfo = getSchoolInfo();

        $termsinfo = $this->db->table('terms')->get()->getResult();
        $this->template_data['termsinfo'] = $termsinfo;

        $academic_session = $this->db->table('academic_session')
            ->where('system_id', $schoolinfo->system_id)
            ->get()->getResult();
        $this->template_data['academic_session'] = $academic_session;

        return view('admin/student_report_by_fee_type', $this->template_data);
    }

    public function edit()
    {
        check_permission('admin-student-fee-report');
        $id = intval($this->request->getGet('id'));
        $info = $this->db->table('terms_session')->where('term_session_id', $id)->get()->getRow();

        $termsinfo = $this->db->table('terms')->get()->getResult();
        $this->template_data['termsinfo'] = $termsinfo;

        $academic_session = $this->db->table('academic_session')->get()->getResult();
        $this->template_data['academic_session'] = $academic_session;

        $this->template_data['info'] = $info;
        return view('admin/student_fee_report', $this->template_data);
    }

    public function delete()
    {
        check_permission('admin-del-terms-session');
        $id = intval($this->request->getGet('id'));

        $this->db->transStart();
        $this->db->table('terms_session')->where('term_session_id', $id)->delete();
        $this->db->transComplete();

        return $this->response->setJSON(['success' => true, 'msg' => 'Delete Term Session Success']);
    }
}
// end file
