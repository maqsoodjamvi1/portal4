<?php

namespace App\Models\Admin;

use CodeIgniter\Model;
use CodeIgniter\Database\BaseBuilder;

class FamilyFeeHistoryModel extends Model
{
    protected $table = 'parents';
    protected $primaryKey = 'parent_id';
    protected $allowedFields = [];
    protected $useTimestamps = false;

    protected $column_order = [null, 'first_name', 'last_name', 'father_contact', 'address_line1', 'city'];
    protected $column_search = ['parent_id', 'status'];
    protected $order = ['parent_id' => 'asc'];

    protected $db;
    protected $request;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
        $this->request = \Config\Services::request();
    }

    public function fetchDefaulters(array $filters = [], $limit = null, $offset = null)
    {
        $campusId = session('member_campusid');
        $year = date('Y');
        $month = (!empty($filters['month']) && is_numeric($filters['month']) && $filters['month'] > 0 && $filters['month'] <= 12)
            ? str_pad($filters['month'], 2, '0', STR_PAD_LEFT)
            : date('m');
        $monthFilter = $month . '/' . $year;

        $builder = $this->db->table('parents p');
        $builder->select("
            p.parent_id,
            MAX(s.student_id) as student_id,
            MAX(CONCAT(s.first_name, ' ', s.last_name)) as student_name,
            p.f_name, p.father_contact, p.mother_contact,
            MAX(curr.code) as currency_code,
            MAX(curr.decimal_places) as decimal_places,
            SUM(CASE WHEN fc.fee_month = '$monthFilter' THEN fc.amount - fc.discount ELSE 0 END) AS current_month_due,
            SUM(fc.amount - fc.discount) AS total_due
        ");
        $builder->join('students s', 's.parent_id = p.parent_id');
        $builder->join('fee_chalan fc', 'fc.student_id = s.student_id');
        $builder->join('currencies curr', 'fc.currency_code = curr.code');
        $builder->where('s.status', 1)
                ->where('s.campus_id', $campusId)
                ->where('fc.status', 'UnPaid')
                ->groupBy('p.parent_id');

        if (!empty($filters['parent_id'])) {
            $builder->where('p.parent_id', $filters['parent_id']);
        }

        if (!empty($filters['fee_type'])) {
            $builder->where('fc.fee_type_id', $filters['fee_type']);
        }

        if ($limit !== null && $offset !== null) {
            $builder->limit($limit, $offset);
        }

        return $builder->get()->getResult();
    }

    public function countAllDefaulters(array $filters = []): int
    {
        $campusId = session('member_campusid');
        return $this->db->table('parents p')
            ->join('students s', 's.parent_id = p.parent_id')
            ->where('s.status', 1)
            ->where('s.campus_id', $campusId)
            ->groupBy('p.parent_id')
            ->countAllResults();
    }

    private function _getDatatablesQuery(): BaseBuilder
    {
        $campusId = session('member_campusid');
        $sessionId = session('member_sessionid');
        $request = $this->request;

        $builder = $this->db->table($this->table);
        $builder->distinct();
        $builder->join(
            'students',
            "students.parent_id = {$this->table}.parent_id AND students.status = 1 AND students.campus_id = $campusId"
        );

        if ($request->getPost('parent_id')) {
            $builder->where("{$this->table}.parent_id", $request->getPost('parent_id'));
        }

        $searchValue = $request->getPost('search')['value'] ?? '';
        if (!empty($searchValue)) {
            $builder->groupStart();
            foreach ($this->column_search as $idx => $item) {
                if ($idx === 0) {
                    $builder->like($item, $searchValue);
                } else {
                    $builder->orLike($item, $searchValue);
                }
            }
            $builder->groupEnd();
        }

        $orderPost = $request->getPost('order');
        if (!empty($orderPost)) {
            $builder->orderBy($this->column_order[$orderPost[0]['column']], $orderPost[0]['dir']);
        } else {
            $builder->orderBy(key($this->order), current($this->order));
        }

        return $builder;
    }

    public function getDatatables(): array
    {
        $request = $this->request;
        $builder = $this->_getDatatablesQuery();

        $feeMonth = $request->getPost('month') ?? '';
        $feeTypeId = $request->getPost('fee_type') ?? '';

        if ($request->getPost('length') != -1) {
            $builder->limit($request->getPost('length'), $request->getPost('start'));
        }

        return [
            'query_result' => $builder->get()->getResult(),
            'fee_month'    => $feeMonth,
            'fee_type_id'  => $feeTypeId
        ];
    }

    public function countFiltered(): int
    {
        return $this->_getDatatablesQuery()->get()->getNumRows();
    }

    public function countAll(): int
    {
        $campusId = session('member_campusid');
        return $this->db->table($this->table)->where('campus_id', $campusId)->countAllResults();
    }

    public function getListParents(): array
    {
        $parents = $this->db->table($this->table)
            ->select('country')
            ->orderBy('country', 'asc')
            ->get()
            ->getResult();

        return array_column($parents, 'country');
    }
}
