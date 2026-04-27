<?php

namespace App\Models\Admin;

use CodeIgniter\Model;

class StudentsModelResultList extends Model
{
    protected $table = 'students';
    protected $primaryKey = 'student_id';
    protected $allowedFields = [];

    protected $columnOrder = [null, 'first_name', 'last_name', 'father_contact', 'address_line1', 'city'];
    protected $columnSearch = ['student_id', 'parent_id', 'status'];
    protected $order = ['student_id' => 'asc'];

    protected $db;
    protected $session;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
        $this->session = service('session');
    }

    private function _getDatatablesQuery()
    {
        $campusId = $this->session->get('member_campusid');
        $sessionId = $this->session->get('member_sessionid');

        $builder = $this->db->table($this->table)->where('campus_id', $campusId);

        $post = service('request')->getPost();
        $get = service('request')->getGet();

        if (!empty($post['status'])) {
            $builder->where('status', $post['status']);
        }

        if (!empty($get['status'])) {
            $builder->where('status', $get['status']);
        }

        if (!empty($post['student_id'])) {
            $builder->where('student_id', $post['student_id']);
        }

        if (!empty($post['cls_sec_id'])) {
            $clsSecId = (int) $post['cls_sec_id'];
            $builder->where("student_id IN (SELECT student_id FROM student_class WHERE session_id = $sessionId AND cls_sec_id = $clsSecId)");
        }

        if (!empty($post['parent_id'])) {
            $builder->where('parent_id', $post['parent_id']);
        }

        $searchValue = $post['search']['value'] ?? null;
        if (!empty($searchValue)) {
            $builder->groupStart();
            foreach ($this->columnSearch as $index => $item) {
                if ($index === 0) {
                    $builder->like($item, $searchValue);
                } else {
                    $builder->orLike($item, $searchValue);
                }
            }
            $builder->groupEnd();
        }

        // Ordering
        if (isset($post['order'])) {
            $orderColumn = $this->columnOrder[$post['order'][0]['column']];
            $orderDir = $post['order'][0]['dir'];
            $builder->orderBy($orderColumn, $orderDir);
        } else {
            foreach ($this->order as $key => $val) {
                $builder->orderBy($key, $val);
            }
        }

        return $builder;
    }

    public function getDatatables()
    {
        $builder = $this->_getDatatablesQuery();

        $length = (int) service('request')->getPost('length');
        $start = (int) service('request')->getPost('start');

        if ($length != -1) {
            $builder->limit($length, $start);
        }

        return $builder->get()->getResult();
    }

    public function countFiltered()
    {
        return $this->_getDatatablesQuery()->countAllResults(false);
    }

    public function countAll()
    {
        $campusId = $this->session->get('member_campusid');
        return $this->db->table($this->table)->where('campus_id', $campusId)->countAllResults();
    }
}
