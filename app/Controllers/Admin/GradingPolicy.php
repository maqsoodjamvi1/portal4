<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use stdClass;

class GradingPolicy extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->db = \Config\Database::connect();
        $this->session = session();
    }

    public function index()
    {
        check_permission('admin-grading-policy');
        return redirect()->to(base_url('admin/grades'));
    }

    public function data()
    {
        $request = $this->request;
        $response = new stdClass();
        $response->draw = $request->getPost('draw');
        $schoolinfo = getSchoolInfo();

        $search = $request->getPost('search');
        $keyword = '';
        if ($search && isset($search['value'])) {
            $keyword = $search['value'];
        }

        // Total count
        $builder = $this->db->table('grading_policy A');
        $builder->select('COUNT(A.gp_id) as ccount', false);
        $builder->where('A.system_id', $schoolinfo->system_id);
        $q = $builder->get()->getRow();
        $response->recordsTotal = $q->ccount;

        // Paginated results
        $builder2 = $this->db->table('grading_policy A');
        $builder2->select('A.*');
        $builder2->where('A.system_id', $schoolinfo->system_id);
        $builder2->orderBy('A.gp_id', 'desc');
        $length = (int) $request->getPost('length');
        $start = (int) $request->getPost('start');
        $builder2->limit($length, $start);
        $results = $builder2->get()->getResult();

        $response->recordsFiltered = $response->recordsTotal;
        $response->data = [];

        foreach ($results as $row) {
            $grades_info = $this->db->table('grades')->where('gid', $row->gid)->get()->getRow();
            $data = [];
            $data['id'] = $row->gp_id;
            $data['grade_name'] = $grades_info->name ?? '';
            $data['marks_from'] = $row->mark_from;
            $data['marks_to'] = $row->mark_to;
            $response->data[] = $data;
        }

        return $this->response->setJSON($response);
    }

    public function data2()
    {
        $data = '';
        $session_id = $this->request->getPost('session_id');
        $schoolinfo = getSchoolInfo();

        $gradesinfo = $this->db->table('grades')
            ->where('system_id', $schoolinfo->system_id)
            ->orderBy('gid', 'desc')
            ->get()->getResult();

        $grades = $this->db->table('grades')
            ->select('COUNT(gid) as totalCount')
            ->where('system_id', $schoolinfo->system_id)
            ->get()->getRow();
        $gradeCount = $grades->totalCount;

        $data .= '<table class="table"><tr><th style="width: 115px;"></th><th>Percentage From</th><th>Percentage To</th></tr>';
        $i = 1;
        foreach ($gradesinfo as $grade) {
            $grading_policy = $this->db->table('grading_policy')
                ->where('gid', $grade->gid)
                ->get()->getRow();

            $mark_from = '';
            $marks_to = '';
            if ($grading_policy) {
                $mark_from = $grading_policy->mark_from;
                $marks_to = $grading_policy->mark_to;
                $grading_policy_id = $grading_policy->gp_id;
            } else {
                $grading_policy_id = 0;
                $mark_from = 0;
                $marks_to = 0;
            }

            $data .= '<tr><th>' . $grade->name . '<input type="hidden" name="rowscount[]" value="1" /><input type="hidden" name="gid[]" value="' . $grade->gid . '"><input type="hidden" name="gp_id[]" value="' . $grading_policy_id . '"></th>';
            $data .= '<td><div class="form-group"><input type="number" class="form-control float-end" readonly id="mark_from' . $i . '" value="' . $mark_from . '" name="mark_from[]"></td>';
            $data .= '<td><input type="number" class="form-control float-end" autocomplete="off" id="marks_to' . $i . '" name="marks_to[]" value="' . $marks_to . '" ';
            $data .= '></td>';
            $data .= '</tr><script>
            $(function(){
                $( "#marks_to' . $i . '" ).keyup(function(){
                    var mark_to = parseInt($(this).val());
                    $( "#mark_from' . ($i + 1) . '" ).val((mark_to+1));
                });
            });
            </script>';
            $i++;
        }
        $data .= '</table>';
        return $this->response->setBody($data);
    }

    public function add()
    {
        return redirect()->to(base_url('admin/grades/setup'));
    }

    public function edit()
    {
        return redirect()->to(base_url('admin/grades/setup'));
    }

    public function save()
    {
        $request = $this->request;
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d');
        $schoolinfo = getSchoolInfo();
        $rowscount = $request->getPost('rowscount');
        $marks_from = $request->getPost('mark_from');
        $marks_to = $request->getPost('marks_to');
        $gids = $request->getPost('gid');
        $gp_ids = $request->getPost('gp_id');

        check_permission('admin-add-grading-policy');
        $this->db->transBegin();
        for ($i = 0; $i < count($rowscount); $i++) {
            $markfrom = $marks_from[$i];
            $markto = $marks_to[$i];

            if ($gp_ids[$i]) {
                $data = [
                    'mark_from'    => $markfrom,
                    'mark_to'      => $markto,
                    'user_id'      => $user_id,
                    'updated_date' => $date,
                ];
                $this->db->table('grading_policy')->where('gp_id', $gp_ids[$i])->update($data);
            } else {
                $data = [
                    'system_id'    => $schoolinfo->system_id,
                    'gid'          => $gids[$i],
                    'mark_from'    => $markfrom,
                    'mark_to'      => $markto,
                    'user_id'      => $user_id,
                    'created_date' => $date,
                ];
                $this->db->table('grading_policy')->insert($data);
            }
        }
        $this->db->transComplete();
        return $this->response->setJSON(['success' => true, 'msg' => 'Add grading policy success']);
    }

    public function delete()
    {
        check_permission('admin-del-terms-session');
        $id = (int) $this->request->getGet('id');
        $this->db->transBegin();
        $this->db->table('terms_session')->where('term_session_id', $id)->delete();
        $this->db->transComplete();
        return $this->response->setJSON(['success' => true, 'msg' => 'Delete Term Session Success']);
    }
}
