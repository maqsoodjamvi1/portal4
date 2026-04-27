<?php

namespace App\Models\Admin;

use CodeIgniter\Model;

class StudentsModel extends Model
{
    protected $table = 'students';
    protected $primaryKey = 'student_id';
    protected $allowedFields = [
        'parent_id', 'campus_id', 'first_name', 'last_name', 'date_of_birth',
        'std_cnic', 'status', 'discounted_amount', 'cls_sec_id', 'session_id'
    ];

    protected $columnOrder = [null, 'first_name', 'last_name', 'father_contact', 'address_line1', 'city'];
    protected $columnSearch = ['student_id', 'parent_id', 'status'];
    protected $order = ['cls_sec_id' => 'asc'];

    public function getActiveParentsWithStudents($campus_id)
    {
        return $this->db->query(
            "SELECT * FROM parents WHERE parent_id IN (
                SELECT parent_id FROM students WHERE status = 1 AND campus_id = ?
            )",
            [$campus_id]
        )->getResult();
    }

    public function getStudentsByParent($parent_id, $campus_id)
    {
        return $this->where('campus_id', $campus_id)
                    ->where('parent_id', $parent_id)
                    ->where('status', 1)
                    ->findAll();
    }

    private function _getDatatablesQuery($filters, $searchTerm, $orderColumn, $orderDir)
    {
        $builder = $this->builder();

        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (!empty($filters['student_id'])) {
            $builder->where('student_id', $filters['student_id']);
        }

        if (!empty($filters['parent_id'])) {
            $builder->where('parent_id', $filters['parent_id']);
        }

        if (!empty($filters['cls_sec_id']) && $filters['cls_sec_id'] !== 'all') {
            $builder->whereIn('student_id',
                $this->db->table('student_class')
                    ->select('student_id')
                    ->where('session_id', $filters['session_id'])
                    ->where('cls_sec_id', $filters['cls_sec_id'])
            );
        } elseif ($filters['cls_sec_id'] === 'all') {
            $builder->whereIn('student_id',
                $this->db->table('student_class')
                    ->select('student_id')
                    ->where('session_id', $filters['session_id'])
            );
        }

        $builder->where('campus_id', $filters['campus_id']);

        if (!empty($searchTerm)) {
            $builder->groupStart();
            foreach ($this->columnSearch as $i => $item) {
                if ($i === 0) {
                    $builder->like($item, $searchTerm);
                } else {
                    $builder->orLike($item, $searchTerm);
                }
            }
            $builder->groupEnd();
        }

        if (isset($orderColumn) && isset($orderDir)) {
            $builder->orderBy($this->columnOrder[$orderColumn] ?? $this->primaryKey, $orderDir);
        } else {
            $builder->orderBy(key($this->order), current($this->order));
        }

        return $builder;
    }

    public function getDatatables($filters, $searchTerm = '', $start = 0, $length = 10, $orderColumn = null, $orderDir = 'asc')
    {
        $builder = $this->_getDatatablesQuery($filters, $searchTerm, $orderColumn, $orderDir);
        return $builder->get($length, $start)->getResult();
    }

    public function countFiltered($filters, $searchTerm = '', $orderColumn = null, $orderDir = 'asc')
    {
        $builder = $this->_getDatatablesQuery($filters, $searchTerm, $orderColumn, $orderDir);
        return $builder->countAllResults();
    }

    public function countAll($campus_id)
    {
        return $this->where('campus_id', $campus_id)->countAllResults();
    }
}
