<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use stdClass;

class Grades extends BaseController
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
        check_permission('admin-grades');
        return view('admin/grades', []);
    }

    /**
     * Unified page: grade names + percentage policy bands in one form.
     */
    public function setup()
    {
        if (! hasPermission('admin-grades') && ! hasPermission('admin-grading-policy')) {
            check_permission('admin-grades');
        }

        $schoolinfo = getSchoolInfo();
        $data['rows'] = $this->loadGradesWithPolicies((int) $schoolinfo->system_id);

        return view('admin/grades_setup', $data);
    }

    public function saveSetup()
    {
        if (! hasPermission('admin-add-grades') && ! hasPermission('admin-add-grading-policy')) {
            check_permission('admin-add-grades');
        }

        $request    = $this->request;
        $schoolinfo = getSchoolInfo();
        $systemId   = (int) $schoolinfo->system_id;
        $userId     = $this->session->get('member_userid');
        $now        = date('Y-m-d H:i:s');
        $today      = date('Y-m-d');

        $gids       = $request->getPost('gid') ?? [];
        $gpIds      = $request->getPost('gp_id') ?? [];
        $names      = $request->getPost('name') ?? [];
        $details    = $request->getPost('detail') ?? [];
        $markFrom   = $request->getPost('mark_from') ?? [];
        $markTo     = $request->getPost('marks_to') ?? [];
        $isF        = (string) ($request->getPost('is_f') ?? '');
        $rowscount  = $request->getPost('rowscount') ?? [];

        if (! is_array($rowscount) || count($rowscount) === 0) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Add at least one grade row.']);
        }

        $this->db->transStart();

        try {
            for ($i = 0, $n = count($rowscount); $i < $n; $i++) {
                $name = trim((string) ($names[$i] ?? ''));
                if ($name === '') {
                    continue;
                }

                $gid    = (int) ($gids[$i] ?? 0);
                $gpId   = (int) ($gpIds[$i] ?? 0);
                $from   = (int) ($markFrom[$i] ?? 0);
                $to     = (int) ($markTo[$i] ?? 0);
                $isFail = ($isF === 'is_f_' . $i) ? 1 : 0;

                $gradeData = [
                    'name'         => $name,
                    'detail'       => trim((string) ($details[$i] ?? '')),
                    'system_id'    => $systemId,
                    'is_f'         => $isFail,
                    'user_id'      => $userId,
                    'created_date' => $now,
                ];

                if ($gid > 0) {
                    unset($gradeData['created_date']);
                    $this->db->table('grades')->where('gid', $gid)->where('system_id', $systemId)->update($gradeData);
                } else {
                    $this->db->table('grades')->insert($gradeData);
                    $gid = (int) $this->db->insertID();
                }

                $policyData = [
                    'mark_from'    => $from,
                    'mark_to'      => $to,
                    'user_id'      => $userId,
                    'updated_date' => $today,
                ];

                if ($gpId > 0) {
                    $this->db->table('grading_policy')->where('gp_id', $gpId)->where('system_id', $systemId)->update($policyData);
                } else {
                    $policyData['system_id']    = $systemId;
                    $policyData['gid']          = $gid;
                    $policyData['created_date'] = $today;
                    $this->db->table('grading_policy')->insert($policyData);
                }
            }

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Database error while saving grades.');
            }

            $this->db->transComplete();

            return $this->response->setJSON(['success' => true, 'msg' => 'Grades and grading policy saved successfully.']);
        } catch (\Throwable $e) {
            $this->db->transComplete();

            return $this->response->setJSON(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    private function loadGradesWithPolicies(int $systemId): array
    {
        $rows = $this->db->table('grades g')
            ->select('g.gid, g.name, g.detail, g.is_f, gp.gp_id, gp.mark_from, gp.mark_to')
            ->join('grading_policy gp', 'gp.gid = g.gid AND gp.system_id = g.system_id', 'left')
            ->where('g.system_id', $systemId)
            ->orderBy('gp.mark_from', 'DESC')
            ->orderBy('g.gid', 'DESC')
            ->get()
            ->getResult();

        if ($rows !== []) {
            return $rows;
        }

        return [(object) [
            'gid'       => 0,
            'name'      => '',
            'detail'    => '',
            'is_f'      => 0,
            'gp_id'     => 0,
            'mark_from' => 0,
            'mark_to'   => 0,
        ]];
    }

    public function data()
    {
        $request = $this->request;
        $response = new stdClass;

        $response->draw = $request->getPost('draw');
        $schoolinfo = getSchoolInfo();

        $search = $request->getPost('search');
        $keyword = '';
        if ($search && isset($search['value'])) {
            $keyword = $search['value'];
        }

        // Total count
        $builder = $this->db->table('grades A');
        $builder->select('COUNT(A.gid) as ccount', false);
        $builder->where('A.system_id', $schoolinfo->system_id);
        if ($keyword) {
            $builder->where('A.name', $keyword);
        }
        $q = $builder->get()->getRow();
        $response->recordsTotal = $q->ccount;

        // Paginated results
        $builder2 = $this->db->table('grades A');
        $builder2->select('A.*, gp.mark_from, gp.mark_to');
        $builder2->join('grading_policy gp', 'gp.gid = A.gid AND gp.system_id = A.system_id', 'left');
        $builder2->where('A.system_id', $schoolinfo->system_id);
        if ($keyword) {
            $builder2->like('A.name', $keyword);
        }
        $builder2->orderBy('gp.mark_from', 'DESC');
        $builder2->orderBy('A.gid', 'DESC');
        $length = (int) $request->getPost('length');
        $start = (int) $request->getPost('start');
        $builder2->limit($length, $start);
        $results = $builder2->get()->getResult();

        $response->recordsFiltered = $response->recordsTotal;

        $response->data = [];
        foreach ($results as $row) {
            $data = [];
            $data['id']        = $row->gid;
            $data['name']      = $row->name;
            $data['detail']    = $row->detail;
            $data['mark_from'] = $row->mark_from ?? '—';
            $data['mark_to']   = $row->mark_to ?? '—';
            $data['is_fail']   = ((int) ($row->is_f ?? 0) === 1) ? 'Yes' : '';
            $response->data[]  = $data;
        }

        return $this->response->setJSON($response);
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
        $id = (int) $request->getPost('id');
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d H:i:s');
        $schoolinfo = getSchoolInfo();
        $rowscount = $request->getPost('rowscount');
        $is_f = $request->getPost('is_f');

        for ($i = 0; $i < count($rowscount); $i++) {
            $id = $request->getPost('id' . $i);

            if ('is_f_' . $i == $is_f) {
                $isF = 1;
            } else {
                $isF = 0;
            }

            $this->db->transBegin();

            $data = [
                'name'        => trim($request->getPost('name' . $i)),
                'detail'      => trim($request->getPost('detail' . $i)),
                'system_id'   => $schoolinfo->system_id,
                'is_f'        => $isF,
                'user_id'     => $user_id,
                'created_date'=> $date,
            ];

            if ($id == 0) {
                $this->db->table('grades')->insert($data);
            } else {
                $this->db->table('grades')->where('gid', $id)->update($data);
            }

            $this->db->transComplete();
        }

        // Use your own json_response helper or CI4 style below
        return $this->response->setJSON(['success' => true, 'msg' => 'Add Class Success']);
    }
}
