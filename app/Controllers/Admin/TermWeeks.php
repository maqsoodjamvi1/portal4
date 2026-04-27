<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class TermWeeks extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form']);
        check_permission('admin-term-weeks');
    }

    public function index()
    {
        $schoolinfo = getSchoolInfo();
        $termweeks_info = $this->db->table('term_weeks')
        ->where('system_id', $schoolinfo->system_id)
        ->get()
        ->getRow();

        
        return view('admin/term_weeks', [
            'termweeks_info' => $termweeks_info
        ]);
    }

    public function data()
    {
        $request = $this->request;
        $schoolinfo = getSchoolInfo();
        $keyword = $request->getPost('search')['value'] ?? '';

        $builder = $this->db->table('term_weeks')->where('system_id', $schoolinfo->system_id);
        if ($keyword) {
            $builder->where('week_no', $keyword);
        }

        $total = $builder->countAllResults(false);

        $results = $builder->select('*')
            ->orderBy('term_weeks_id', 'desc')
            ->limit($request->getPost('length'), $request->getPost('start'))
            ->get()
            ->getResult();

        $data = [];
        foreach ($results as $row) {
            $week_type = $this->db->table('week_type')->where('type_id', $row->week_type_id)->get()->getRow();
            $data[] = [
                'id' => $row->term_weeks_id,
                'term_session_id' => termSessionsById($row->term_session_id),
                'week_no' => $row->week_no,
                'start_date' => dateFormat($row->start_date),
                'end_date' => dateFormat($row->end_date),
                'week_type' => $week_type->type_name ?? '',
                'week_name' => $row->week_name,
                'detail' => $row->detail,
            ];
        }

        return $this->response->setJSON([
            'draw' => $request->getPost('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data,
        ]);
    }

    public function add()
    {
        check_permission('admin-add-term-week');
        $terms_session_info = termSessions();
        return view('admin/term_weeks_edit', ['terms_session_info' => $terms_session_info]);
    }

    public function edit()
    {
        check_permission('admin-edit-term-week');

        $id = (int) $this->request->getGet('id');
        $schoolinfo = getSchoolInfo();

        $terms_session = $this->db->table('terms_session')->where('system_id', $schoolinfo->system_id)->get()->getResult();
        $terms_session_info = [];

        foreach ($terms_session as $term_data) {
            $term = $this->db->table('terms')->where(['term_id' => $term_data->term_id, 'system_id' => $schoolinfo->system_id])->get()->getRow();
            $session = $this->db->table('academic_session')->where(['session_id' => $term_data->session_id, 'system_id' => $schoolinfo->system_id])->get()->getRow();

            $terms_session_info[] = [
                'term_session_id' => $term_data->term_session_id,
                'term_name' => ($term->name ?? '') . " (" . ($session->session_name ?? '') . ")",
            ];
        }

        $info = $this->db->table('term_weeks')->where('term_weeks_id', $id)->get()->getRow();

        return view('admin/term_weeks_edit', [
            'terms_session_info' => $terms_session_info,
            'info' => $info
        ]);
    }

    public function save()
    {
        check_permission('admin-add-term-week');
        $user_id = $this->session->get('member_userid');
        $schoolinfo = getSchoolInfo();
        $date = date('Y-m-d');

        $rows = $this->request->getPost('rowscount');
        $term_session_id = $this->request->getPost('term_session');
        $week_nos = $this->request->getPost('week_no');
        $start_dates = $this->request->getPost('start_date');
        $end_dates = $this->request->getPost('end_date');
        $week_names = $this->request->getPost('week_name');
        $week_types = $this->request->getPost('week_type');
        $details = $this->request->getPost('detail');
        $term_week_ids = $this->request->getPost('term_week_id');

        $this->db->transStart();

        foreach ($rows as $i => $r) {
            $data = [
                'term_session_id' => $term_session_id,
                'week_no' => $week_nos[$i],
                'start_date' => $start_dates[$i],
                'end_date' => $end_dates[$i],
                'week_type_id' => $week_types[$i],
                'week_name' => $week_names[$i],
                'detail' => $details[$i],
                'user_id' => $user_id
            ];

            if (!empty($term_week_ids[$i])) {
                $data['updated_date'] = $date;
                $this->db->table('term_weeks')->where('term_weeks_id', $term_week_ids[$i])->update($data);
            } else {
                $data['system_id'] = $schoolinfo->system_id;
                $data['created_date'] = $date;
                $this->db->table('term_weeks')->insert($data);
            }
        }

        $this->db->transComplete();

        $class = $this->db->table('classes')->where('system_id', $schoolinfo->system_id)->get()->getRow();

        if (empty($class->class_id)) {
            return $this->response->setJSON(['class_id' => false, 'msg' => 'Terms Weeks Success']);
        }

        return $this->response->setJSON(['success' => true, 'msg' => 'Add Term Weeks Success']);
    }

    public function generate_term_weeks()
    {
        $term_session_id = $this->request->getPost('term_session');
        $schoolinfo = getSchoolInfo();

        $info = $this->db->table('terms_session')->where([
            'system_id' => $schoolinfo->system_id,
            'term_session_id' => $term_session_id
        ])->get()->getRow();

        $term = $this->db->table('terms')->where([
            'system_id' => $schoolinfo->system_id,
            'term_id' => $info->term_id
        ])->get()->getRow();

        $session = $this->db->table('academic_session')->where([
            'system_id' => $schoolinfo->system_id,
            'session_id' => $info->session_id
        ])->get()->getRow();

        $sessionparts = explode('-', $session->session_name);
        $week_types = $this->db->table('week_type')->get()->getResult();

        $weeks = $this->getMondaysInRange($info->start_date, $info->end_date);
        $nCount = 1;
        $data = '<table class="table"><tr><th>Week No</th><th>Week Start Date</th><th>Week End Date</th><th>Week Name</th><th>Week Type</th><th>Detail</th></tr>';

        foreach ($weeks as $start) {
            $end = date('Y-m-d', strtotime($start . ' +6 days'));

            $existing = $this->db->table('term_weeks')->where([
                'term_session_id' => $info->term_session_id,
                'system_id' => $schoolinfo->system_id,
                'start_date' => $start,
                'end_date' => $end
            ])->get()->getRow();

            $data .= '<tr>';
            $data .= "<td><input type='hidden' name='rowscount[]' value='1' /><input type='hidden' name='week_no[]' value='$nCount' />$nCount<input type='hidden' name='term_week_id[]' value='" . ($existing->term_weeks_id ?? 0) . "' /></td>";
            $data .= "<td><input type='hidden' name='start_date[]' value='$start'>" . dateFormat($start) . "</td>";
            $data .= "<td><input type='hidden' name='end_date[]' value='$end'>" . dateFormat($end) . "</td>";
            $week_name = ($sessionparts[1] - 1) . '-' . $term->short_name . '-W' . $nCount;
            $data .= "<td><input type='hidden' name='week_name[]' value='$week_name'>$week_name</td>";
            $data .= "<td><select name='week_type[]' class='form-control'>";
            foreach ($week_types as $type) {
                $selected = (isset($existing->week_type_id) && $existing->week_type_id == $type->type_id) ? 'selected' : '';
                $data .= "<option value='$type->type_id' $selected>$type->type_name</option>";
            }
            $data .= "</select></td>";
            $data .= "<td><textarea name='detail[]' class='form-control'>" . ($existing->detail ?? '') . "</textarea></td>";
            $data .= '</tr>';
            $nCount++;
        }

        $data .= '</table>';
        return $this->response->setBody($data);
    }

    private function getMondaysInRange($start, $end)
    {
        $dates = [];
        $startDate = new \DateTime($start);
        $endDate = new \DateTime($end);

        if ($startDate->format('N') != 1) {
            $startDate->modify('next monday');
        }

        while ($startDate <= $endDate) {
            $dates[] = $startDate->format('Y-m-d');
            $startDate->modify('+1 week');
        }

        return $dates;
    }

    public function delete()
    {
        check_permission('admin-del-user');
        $id = (int) $this->request->getGet('id');

        $this->db->transStart();
        $this->db->table('term_weeks')->where('term_weeks_id', $id)->delete();
        $this->db->transComplete();

        return $this->response->setJSON(['success' => true, 'msg' => 'Delete Term Success']);
    }
}
