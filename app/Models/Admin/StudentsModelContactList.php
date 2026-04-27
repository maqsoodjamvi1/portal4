<?php

namespace App\Models\Admin;

use CodeIgniter\Model;

class StudentsModelContactList extends Model
{
    protected $table = 'students';
    protected $allowedFields = [
        'student_id', 'parent_id', 'status', 'campus_id', 'first_name', 'last_name'
    ];

    protected $columnOrder = [null, 'first_name', 'last_name', 'father_contact', 'address_line1', 'city'];
    protected $columnSearch = ['student_id', 'parent_id', 'status'];
    protected $order = ['student_id' => 'asc'];

    protected $request;
    protected $db;
    protected $builder;

    public function __construct()
    {
        parent::__construct();
        $this->db = db_connect();
        $this->request = service('request');
        $this->builder = $this->db->table($this->table);
    }

    private function _getDatatablesQuery()
    {
        $campusId = session('member_campusid');
        $sessionId = session('member_sessionid');

        $builder = $this->builder;
        $builder->where('campus_id', $campusId);

        if ($this->request->getPost('status')) {
            $builder->where('status', $this->request->getPost('status'));
        }

        if ($this->request->getPost('student_id')) {
            $builder->where('student_id', $this->request->getPost('student_id'));
        }

        if ($this->request->getPost('cls_sec_id')) {
            $clsSecId = (int) $this->request->getPost('cls_sec_id');
            $builder->where("student_id IN (SELECT student_id FROM student_class WHERE session_id = $sessionId AND cls_sec_id = $clsSecId)", null, false);
        }

        if ($this->request->getPost('parent_id')) {
            $builder->where('parent_id', $this->request->getPost('parent_id'));
        }

        $searchValue = $this->request->getPost('search')['value'] ?? '';
        if (!empty($searchValue)) {
            $builder->groupStart();
            foreach ($this->columnSearch as $i => $item) {
                if ($i === 0) {
                    $builder->like($item, $searchValue);
                } else {
                    $builder->orLike($item, $searchValue);
                }
            }
            $builder->groupEnd();
        }

        if (isset($_POST['order'])) {
            $orderCol = $_POST['order']['0']['column'];
            $orderDir = $_POST['order']['0']['dir'];
            $builder->orderBy($this->columnOrder[$orderCol], $orderDir);
        } else {
            $builder->orderBy(key($this->order), $this->order[key($this->order)]);
        }
    }

    public function getDatatables()
    {
        $this->_getDatatablesQuery();
        if ($this->request->getPost('length') != -1) {
            $this->builder->limit($this->request->getPost('length'), $this->request->getPost('start'));
        }
        return $this->builder->get()->getResult();
    }

    public function countFiltered()
    {
        $this->_getDatatablesQuery();
        return $this->builder->countAllResults(false);
    }

    public function countAll()
    {
        $campusId = session('member_campusid');
        return $this->builder->where('campus_id', $campusId)->countAllResults();
    }
}
