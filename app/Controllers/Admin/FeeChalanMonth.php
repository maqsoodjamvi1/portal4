<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class FeeChalanMonth extends BaseController
{
    protected $db;
    protected $session;
    protected $template_data = [];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url']);
        check_permission('admin-fee-chalan-balance');
    }

    public function index()
    {
        $schoolinfo = getSchoolInfo();

        $fee_type = $this->db->table('fee_type')
            ->where('system_id', $schoolinfo->system_id)
            ->get()
            ->getResult();
        $this->template_data['fee_type'] = $fee_type;

        return view('admin/fee_chalan_month', $this->template_data);
    }

    public function getTotalfee()
    {
        $campusid = $this->session->get('member_campusid');

        $paid_date_from = $this->request->getPost('paid_date_from');
        $paid_date_to   = $this->request->getPost('paid_date_to');

        // Convert dates from d/m/Y to Y-m-d
        $paid_date_from = \DateTime::createFromFormat('d/m/Y', $paid_date_from)->format('Y-m-d');
        $paid_date_to   = \DateTime::createFromFormat('d/m/Y', $paid_date_to)->format('Y-m-d');

        $month_balance = $this->db->query(
            'SELECT (sum(amount)-sum(discount)) as total FROM fee_chalan WHERE status="paid" and paid_date BETWEEN ? AND ? and student_id IN(select student_id from students where campus_id=?)',
            [$paid_date_from, $paid_date_to, $campusid]
        )->getRow();

        $remaing_balance = $this->db->query(
            'SELECT (sum(amount)-sum(discount)) as total FROM fee_chalan WHERE status="unpaid" and student_id IN(select student_id from students where campus_id=? and status=1)',
            [$campusid]
        )->getRow();

        $totalbalance = "Payment Recieved: " . ($month_balance->total ?? 0) . "/-<br> Payment Balance: " . ($remaing_balance->total ?? 0) . "/-<br>";

        $result = $this->db->query(
            'SELECT * FROM parents where parent_id IN(select parent_id from students where campus_id=?)',
            [$campusid]
        )->getResult();

        $totalbalance .= "<table class='table'><tr><th>#</th><th>Parent Name</th><th>Students Name</th><th>Paid Date</th><th>Paid Amount</th></tr>";
        $i = 1;
        foreach ($result as $row) {
            $student_names = $this->db->query(
                'SELECT student_id,reg_no,first_name,last_name,status FROM students WHERE campus_id=? and parent_id =? order by class_id DESC',
                [$campusid, $row->parent_id]
            )->getResultArray();

            $stdinfo = '';
            $sessionid = $this->session->get('member_sessionid');
            foreach ($student_names as $stddata) {
                $classSecInfo = $this->db->query(
                    'SELECT cls_sec_id FROM student_class WHERE session_id=? and student_id =?',
                    [$sessionid, $stddata['student_id']]
                )->getRow();

                $sectionclassname = '';
                if ($classSecInfo) {
                    $classSection = getClassSection($classSecInfo->cls_sec_id);
                    if ($classSection) {
                        $sectionclassname = $classSection['sectionclassname'];
                    }
                }

                $stdinfo .= $stddata['first_name'] . " " . $stddata['last_name'] . " <strong>" . $sectionclassname . "</strong>, ";
            }

            $parent_balance = $this->db->query(
                'SELECT paid_date,(sum(amount)-sum(discount)) as total FROM fee_chalan WHERE status="paid" and paid_date BETWEEN ? AND ? and student_id IN(select student_id from students where parent_id=?) group by paid_date ORDER BY paid_date',
                [$paid_date_from, $paid_date_to, $row->parent_id]
            )->getResult();

            if ($parent_balance) {
                foreach ($parent_balance as $amount) {
                    if ($amount->total > 0) {
                        $nmonth = date("d M Y l", strtotime($amount->paid_date));
                        $totalbalance .= "<tr><th>" . $i . "</th><th>" . $row->f_name . '</th><th>' . $stdinfo . "</th><td>" . $nmonth . "</td><td>" . $amount->total . "</td></tr>";
                        $i++;
                    }
                }
            }
        }
        $totalbalance .= "</table>";

        return $this->response->setBody($totalbalance);
    }

    public function getTotalfeebymonth()
    {
        $campusid = $this->session->get('member_campusid');
        $fee_month = $this->request->getPost('fee_month');
        if (empty($fee_month)) {
            return $this->response->setBody("Please Select Month");
        }

        $feemonthArr = explode('-', $fee_month);
        $fee_month_format = $feemonthArr[1] . "/" . $feemonthArr[0];

        $feemonth_balance = $this->db->query(
            'SELECT fee_type_id,fee_month, SUM( CASE WHEN status = "paid" THEN (amount-discount) END)  as Total_1 FROM fee_chalan WHERE YEAR(paid_date) = ? and MONTH(paid_date) = ? and student_id IN(select student_id from students where campus_id=?) group by fee_month,fee_type_id order by fee_type_id ASC',
            [$feemonthArr[0], $feemonthArr[1], $campusid]
        )->getResult();

        $feemonth_balance_total = $this->db->query(
            'SELECT SUM( CASE WHEN status = "paid" THEN (amount-discount) END)  as total FROM fee_chalan WHERE YEAR(paid_date) = ? and MONTH(paid_date) = ? and student_id IN(select student_id from students where campus_id=?)',
            [$feemonthArr[0], $feemonthArr[1], $campusid]
        )->getRow();

        $strFeeReport = '';
        $strFeeReport .= '<table class="table">';
        $strFeeReport .= '<thead class="thead-light"><tr><th>Fee Month</th><th>Fee Type</th><th>Paid</th></tr></thead>';
        foreach ($feemonth_balance as $value) {
            if ($value->Total_1 != '') {
                $feeTypeInfo = $this->db->table('fee_type')->where('fee_type_id', $value->fee_type_id)->get()->getRow();
                $strFeeReport .= '<tr><td>' . $value->fee_month . '</td><td>' . $feeTypeInfo->fee_type_name . '</td><td>' . $value->Total_1 . '</td></tr>';
            }
        }
        $strFeeReport .= '<tfoot class="thead-light"><tr><th></th><th>Total</th><th>' . ($feemonth_balance_total->total ?? 0) . '</th></tr></tfoot>';
        $strFeeReport .= '</table>';

        return $this->response->setBody($strFeeReport);
    }
}

// end this file
