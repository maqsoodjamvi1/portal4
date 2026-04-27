<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use stdClass;
use DateTime;

class TestSeries extends BaseController
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
        check_permission('admin-test-series');
        return view('admin/test_series', []);
    }

    public function data()
    {
        $response = new stdClass;
        $response->draw = $this->request->getPost('draw');
        $campusid = $this->session->get('member_campusid');
        $keyword = $this->request->getPost('search')['value'] ?? '';

        // Count total records
        $builder = $this->db->table('test_series A');
        $builder->select('count(A.t_series_id) as ccount', false)
            ->where('A.campus_id', $campusid);
        if ($keyword) {
            $builder->where('A.series_name', $keyword);
        }
        $q = $builder->get()->getRow();
        $response->recordsTotal = $q->ccount;

        // Fetch paginated records
        $builder2 = $this->db->table('test_series A');
        $builder2->select('A.*')
            ->where('A.campus_id', $campusid);
        if ($keyword) {
            $builder2->where('A.series_name', $keyword);
        }
        $builder2->orderBy('A.t_series_id', 'desc');
        $builder2->limit($this->request->getPost('length'), $this->request->getPost('start'));
        $results = $builder2->get()->getResult();

        $response->recordsFiltered = $response->recordsTotal;
        $response->data = [];

        foreach ($results as $row) {
            $data = [];
            $sessioninfo = $this->db->table('academic_session')->where('session_id', $row->session_id)->get()->getRow();
            $data['id'] = $row->t_series_id;
            $data['series_name'] = $row->series_name;
            $data['short_name'] = $row->short_name;
            $data['series_start_date'] = $row->series_start_date;
            $data['series_end_date'] = $row->series_end_date;
            $data['series_session'] = $sessioninfo->session_name ?? '';
            $data['status'] = $row->status;
            $response->data[] = $data;
        }

        return $this->response->setJSON($response);
    }

    public function add()
    {
        check_permission('admin-add-test-series');
        return view('admin/test_series_edit', []);
    }

    public function edit()
    {
        check_permission('admin-edit-test-series');
        $id = intval($this->request->getGet('id'));
        $info = $this->db->table('test_series')->where('t_series_id', $id)->get()->getRow();
        $data['info'] = $info;
        return view('admin/test_series_edit', $data);
    }

    public function save()
    {
        $id = intval($this->request->getPost('id'));
        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d H:i:s');

        $series_start_date = DateTime::createFromFormat('d/m/Y', $this->request->getPost('series_start_date'));
        $series_start_date = $series_start_date ? $series_start_date->format('Y-m-d') : null;

        $series_end_date = DateTime::createFromFormat('d/m/Y', $this->request->getPost('series_end_date'));
        $series_end_date = $series_end_date ? $series_end_date->format('Y-m-d') : null;

        if ($id == 0) {
            $data = [
                'series_name' => trim($this->request->getPost('series_name')),
                'short_name' => trim($this->request->getPost('short_name')),
                'series_start_date' => $series_start_date,
                'campus_id' => $campusid,
                'series_end_date' => $series_end_date,
                'session_id' => $sessionid,
                'status' => 0,
                'created_date' => $date,
                'user_id' => $user_id
            ];
            $this->db->table('test_series')->insert($data);
        } else {
            $data = [
                'series_name' => trim($this->request->getPost('series_name')),
                'short_name' => trim($this->request->getPost('short_name')),
                'series_start_date' => $series_start_date,
                'campus_id' => $campusid,
                'series_end_date' => $series_end_date,
                'session_id' => $sessionid,
                'updated_date' => $date,
                'user_id' => $user_id
            ];
            $this->db->table('test_series')->where('t_series_id', $id)->update($data);
        }

        // No explicit transaction needed unless you have more steps
        return $this->response->setJSON(['success' => true, 'msg' => 'Add Test Series Success']);
    }
}
