<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\CampusFinanceService;
use CodeIgniter\HTTP\ResponseInterface;
use stdClass;

class Expenses extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        check_permission('admin-account-expenses');
    }

    public function index()
    {
        return view('admin/expenses', []);
    }

    public function data(): ResponseInterface
    {
        $draw = $this->request->getPost('draw');
        $length = $this->request->getPost('length');
        $start = $this->request->getPost('start');
        $campusid = $this->session->get('member_campusid');
        $schoolinfo = getSchoolInfo();

        $search = $this->request->getPost('search');
        $keyword = '';
        if ($search) $keyword = $search['value'];

        // Count
        $builder = $this->db->table('expenses A')
            ->selectCount('A.expense_id', 'ccount')
            ->where('A.campus_id', $campusid);
        if ($keyword) {
            $builder->where('A.title', $keyword);
        }
        $q = $builder->get()->getRow();
        $recordsTotal = $q->ccount ?? 0;

        // Data
        $builder = $this->db->table('expenses A')
            ->select('A.*')
            ->where('A.campus_id', $campusid);
        if ($keyword) {
            $builder->where('A.title', $keyword);
        }
        $builder->limit($length, $start);
        $results = $builder->get()->getResult();

        $data = [];
        foreach ($results as $row) {
            $expense_headsinfo = $this->db->table('expense_heads')
                ->where('exp_head_id', $row->exp_head_id)
                ->get()->getRow();
            if ($expense_headsinfo) {
                $data[] = [
                    'id' => $row->expense_id,
                    'head_title' => $expense_headsinfo->head_title,
                    'title' => $row->title,
                    'detail' => $row->detail,
                    'amount' => $row->amount,
                    'expense_date' => date("d-m-Y", strtotime($row->created_date)),
                ];
            }
        }

        $response = [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $data
        ];

        return $this->response->setJSON($response);
    }

    public function getExpenses(): ResponseInterface
    {
        $campusid = $this->session->get('member_campusid');
        $exp_head_id = $this->request->getPost('exp_head_id');
        $expenseDate = $this->request->getPost('expense_date');
        $month = date("m", strtotime($expenseDate));
        $year = date("Y", strtotime($expenseDate));

        $builder = $this->db->table('expenses');
        $builder->where('exp_head_id', $exp_head_id)
            ->where('campus_id', $campusid)
            ->where('MONTH(created_date)', $month)
            ->where('YEAR(created_date)', $year);
        $info = $builder->get()->getResult();

        $expense_list = '<div class="">
            <table class="table table-bordered" id="dynamic_field">';
        $expense_list .= '<tr><th>Title</th><th>Detail</th><th> Amount</th></tr>';

        $i = 0;
        foreach ($info as $value) {
            $expense_list .= '<tr><td><input type="hidden" name="rowscount[]" value="1" />';
            $expense_list .= '<input type="hidden" name="id' . $i . '" value="' . $value->expense_id . '"><input type="text" id="title' . $i . '" name="title' . $i . '"  value="' . esc($value->title) . '" placeholder="Title" class="form-control name_list" required /></td>';
            $expense_list .= '<td><input type="text" name="detail' . $i . '"  value="' . esc($value->detail) . '" placeholder="Detail" class="form-control name_list detail' . $i . '" required /></td>';
            $expense_list .= '<td><input type="text" name="amount' . $i . '"
             value="' . esc($value->amount) . '" placeholder="Amount" class="form-control name_list"  /><small>Expenses Date: ' . date("d-m-Y", strtotime($value->created_date)) . '</small></td></tr>';
            $i++;
        }
        $expense_list .= '<tr><td></td><td></td> <td><button type="button" name="add" id="add" class="btn btn-success">Add More</button></td></tr></table>';
        $expense_list .=  "<script type='text/javascript'>
        $(document).ready(function(){
          var i = " . $i . ";
          $('#add').click(function(){
               $('#dynamic_field').append(\"<tr id='row\" + i + \"' class='dynamic-added'><td><input type='hidden' name='id\" + i + \"' value='0'><input type='hidden' name='rowscount[]' value='1' /><input type='text' id='title\"+ i +\"' name='title\" + i + \"' placeholder='Title' class='form-control name_list' required /></td><td><input type='text' name='detail\" + i + \"' placeholder='Detail' class='form-control name_list detail\"+ i +\"' required /></td><td><input type='text' name='amount\" + i + \"' placeholder='Amount' class='form-control name_list'  /></td><td><button type='button' name='remove' id='\" + i + \"' class='btn btn-danger btn_remove btn-sm'>X</button></td></tr>\");
              i++;
          });
          $(document).on('click', '.btn_remove', function(){
               var button_id = $(this).attr(\"id\");
               $('#row'+button_id).remove();
          });
        });
        </script>";

        return $this->response->setJSON(['html' => $expense_list]);
    }

    public function add()
    {
        check_permission('admin-add-account-expenses');
        $schoolinfo = getSchoolInfo();
        $campusid = (int) $this->session->get('member_campusid');
        $finance = new CampusFinanceService($this->db);
        $expense_heads = $this->db->table('expense_heads')
            ->where('system_id', $schoolinfo->system_id)
            ->get()->getResult();
        return view('admin/expenses_edit', [
            'expense_heads' => $expense_heads,
            'finance_enabled' => $finance->campusHasFinanceAccounts($campusid),
            'finance_accounts' => $finance->getAccountsForCampus($campusid),
            'default_account_id' => $finance->getCampusCashAccountId($campusid)
                ?: $finance->ensureCampusCashAccount($campusid, (int) $this->session->get('member_userid')),
        ]);
    }

    public function edit()
    {
        check_permission('admin-edit-account-expenses');
        $id = intval($this->request->getGet('id'));
        $info = $this->db->table('terms_session')
            ->where('term_session_id', $id)
            ->get()->getRow();

        $termsinfo = $this->db->table('terms')->get()->getResult();
        $academic_session = $this->db->table('academic_session')->get()->getResult();

        return view('admin/expenses_edit', [
            'info' => $info,
            'termsinfo' => $termsinfo,
            'academic_session' => $academic_session
        ]);
    }

    public function save(): ResponseInterface
    {
        $user_id = (int) $this->session->get('member_userid');
        $date = date('Y-m-d H:i:s');
        $expenseDate = $this->request->getPost('expense_date') ?: date('Y-m-d');
        $rowscount = $this->request->getPost('rowscount');
        $campusid = (int) $this->session->get('member_campusid');
        $accountId = (int) $this->request->getPost('account_id');
        $finance = new CampusFinanceService($this->db);

        $this->db->transStart();

        foreach ((array) $rowscount as $i) {
            $id = $this->request->getPost('id' . $i);
            $title = $this->request->getPost('title' . $i);
            $detail = $this->request->getPost('detail' . $i);
            $amount = (float) $this->request->getPost('amount' . $i);

            if ($id == 0 && $amount > 0) {
                $data = [
                    'title' => trim($title),
                    'detail' => trim($detail),
                    'amount' => $amount,
                    'campus_id' => $campusid,
                    'exp_head_id' => trim($this->request->getPost('exp_head_id')),
                    'user_id' => $user_id,
                    'created_date' => $expenseDate,
                ];
                if ($this->db->fieldExists('expense_date', 'expenses')) {
                    $data['expense_date'] = date('Y-m-d', strtotime($expenseDate));
                }
                $this->db->table('expenses')->insert($data);
                $expenseId = (int) $this->db->insertID();

                if ($finance->campusHasFinanceAccounts($campusid)) {
                    $finance->recordExpense(
                        $expenseId,
                        $campusid,
                        $amount,
                        $expenseDate,
                        $accountId,
                        $user_id,
                        trim($title)
                    );
                }
            } elseif ($id != 0) {
                $data = [
                    'title' => trim($title),
                    'detail' => trim($detail),
                    'amount' => $amount,
                    'exp_head_id' => trim($this->request->getPost('exp_head_id')),
                    'user_id' => $user_id,
                    'updated_date' => $date,
                ];
                if ($this->db->fieldExists('expense_date', 'expenses')) {
                    $data['expense_date'] = date('Y-m-d', strtotime($expenseDate));
                }
                $this->db->table('expenses')->where('expense_id', $id)->update($data);
            }
        }

        $this->db->transComplete();

        return $this->response->setJSON(['success' => true, 'msg' => 'Add Expenses Success']);
    }

    public function delete(): ResponseInterface
    {
        check_permission('admin-del-account-expenses');
        $id = intval($this->request->getGet('id'));
        $this->db->transStart();
        $this->db->table('terms_session')->where('term_session_id', $id)->delete();
        $this->db->transComplete();
        return $this->response->setJSON(['success' => true, 'msg' => 'Delete Expenses Success']);
    }
}
