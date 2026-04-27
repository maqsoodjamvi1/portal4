<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use stdClass;

class Assets extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        check_permission('admin-assets');
    }

    public function index()
    {
        return view('admin/assets', []);
    }

    public function data(): ResponseInterface
    {
        $draw = $this->request->getPost('draw');
        $length = $this->request->getPost('length');
        $start = $this->request->getPost('start');
        $campusid = $this->session->get('member_campusid');
        $search = $this->request->getPost('search');
        $keyword = $search['value'] ?? '';

        // Count
        $builder = $this->db->table('assets A')
            ->selectCount('A.asset_id', 'ccount')
            ->where('A.campus_id', $campusid);
        if ($keyword) {
            $builder->where('A.title', $keyword);
        }
        $q = $builder->get()->getRow();
        $recordsTotal = $q->ccount ?? 0;

        // Data
        $builder = $this->db->table('assets A')
            ->select('A.*')
            ->where('A.campus_id', $campusid);
        if ($keyword) {
            $builder->where('A.title', $keyword);
        }
        $builder->limit($length, $start);
        $results = $builder->get()->getResult();

        $data = [];
        foreach ($results as $row) {
            $asset_headsinfo = $this->db->table('asset_heads')
                ->where('asset_head_id', $row->asset_head_id)
                ->get()->getRow();
            $head_title = $asset_headsinfo ? $asset_headsinfo->head_title : '';
            $data[] = [
                'id' => $row->asset_id,
                'head_title' => $head_title,
                'title' => $row->title,
                'detail' => $row->detail,
                'amount' => $row->amount,
                'expense_date' => date("d-m-Y", strtotime($row->created_date)),
            ];
        }

        $response = [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $data
        ];

        return $this->response->setJSON($response);
    }

    public function getAssets(): ResponseInterface
    {
        $campusid = $this->session->get('member_campusid');
        $asset_head_id = $this->request->getPost('asset_head_id');
        $purchasingDate = $this->request->getPost('purchasing_date');
        $month = date("m", strtotime($purchasingDate));
        $year = date("Y", strtotime($purchasingDate));

        $builder = $this->db->table('assets');
        $builder->where('asset_head_id', $asset_head_id)
            ->where('campus_id', $campusid)
            ->where('MONTH(created_date)', $month)
            ->where('YEAR(created_date)', $year);
        $info = $builder->get()->getResult();

        $expense_list = '<div class=""><table class="table table-bordered" id="dynamic_field">';
        $expense_list .= '<tr><th>Title</th><th>Detail</th><th> Amount</th></tr>';
        $i = 0;
        foreach ($info as $value) {
            $expense_list .= '<tr><td><input type="hidden" name="rowscount[]" value="1" />';
            $expense_list .= '<input type="hidden" name="id' . $i . '" value="' . $value->asset_id . '"><input type="text" id="title' . $i . '" name="title' . $i . '"  value="' . esc($value->title) . '" placeholder="Title" class="form-control name_list" required /></td>';
            $expense_list .= '<td><input type="text" name="detail' . $i . '"  value="' . esc($value->detail) . '" placeholder="Detail" class="form-control name_list detail' . $i . '" required /></td>';
            $expense_list .= '<td><input type="text" name="amount' . $i . '"
             value="' . esc($value->amount) . '" placeholder="Amount" class="form-control name_list"  /><small>Purchasing Date: ' . date("d-m-Y", strtotime($value->created_date)) . '</small></td></tr>';
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
        check_permission('admin-add-assets');
        $schoolinfo = getSchoolInfo();
        $asset_heads = $this->db->table('asset_heads')
            ->where('system_id', $schoolinfo->system_id)
            ->get()->getResult();
        return view('admin/assets_edit', [
            'asset_heads' => $asset_heads
        ]);
    }

    public function save(): ResponseInterface
    {
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d H:i:s');
        $purchasingDate = $this->request->getPost('purchasing_date');
        $rowscount = $this->request->getPost('rowscount');
        $campusid = $this->session->get('member_campusid');

        // Add CI4 validation here if needed.

        foreach ((array)$rowscount as $i) {
            $id = $this->request->getPost('id' . $i);
            $title = $this->request->getPost('title' . $i);
            $detail = $this->request->getPost('detail' . $i);
            $amount = $this->request->getPost('amount' . $i);

            if ($id == 0) {
                $data = [
                    'title' => trim($title),
                    'detail' => trim($detail),
                    'amount' => trim($amount),
                    'campus_id' => trim($campusid),
                    'asset_head_id' => trim($this->request->getPost('asset_head_id')),
                    'purchasing_date' => $purchasingDate,
                    'user_id' => $user_id,
                    'created_date' => $date
                ];
                $this->db->table('assets')->insert($data);
            } else {
                $data = [
                    'title' => trim($title),
                    'detail' => trim($detail),
                    'amount' => trim($amount),
                    'asset_head_id' => trim($this->request->getPost('asset_head_id')),
                    'purchasing_date' => $purchasingDate,
                    'user_id' => $user_id,
                    'updated_date' => $date
                ];
                $this->db->table('assets')->where('asset_id', $id)->update($data);
            }
            $this->db->transComplete();
        }
        return $this->response->setJSON(['success' => true, 'msg' => 'Add Assets Success']);
    }

    public function delete(): ResponseInterface
    {
        check_permission('admin-del-account-expenses');
        $id = intval($this->request->getGet('id'));
        $this->db->transStart();
        $this->db->table('assets')->where('asset_id', $id)->delete();
        $this->db->transComplete();
        return $this->response->setJSON(['success' => true, 'msg' => 'Delete Assets Success']);
    }
}
