<?php

namespace App\Models\Admin;

use CodeIgniter\Model;

class StudentsPrintModel extends Model
{
    protected $table = 'students';
    protected $primaryKey = 'student_id';
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'reg_no', 'first_name', 'last_name', 'date_of_birth', 'parent_id',
        'class_id', 'discounted_amount', 'gender', 'profile_photo', 'campus_id',
        'session_id', 'date_of_admission', 'std_cnic'
    ];

    public function get_datatables()
    {
        $builder = $this->builder();
        $search = $_POST['search']['value'] ?? '';

        if (!empty($search)) {
            $builder->groupStart()
                ->like('first_name', $search)
                ->orLike('last_name', $search)
                ->orLike('reg_no', $search)
                ->groupEnd();
        }

        if (isset($_POST['length']) && $_POST['length'] != -1) {
            $builder->limit($_POST['length'], $_POST['start']);
        }

        return $builder->orderBy('student_id', 'DESC')->get()->getResult();
    }

    public function countAll()
    {
        return $this->countAllResults();
    }

    public function countFiltered()
    {
        $builder = $this->builder();
        $search = $_POST['search']['value'] ?? '';

        if (!empty($search)) {
            $builder->groupStart()
                ->like('first_name', $search)
                ->orLike('last_name', $search)
                ->orLike('reg_no', $search)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

    public function calculateUnpaidAmount($student_id)
    {
        return $this->db->table('fee_chalan')
            ->select('SUM(amount) - SUM(discount) as total')
            ->where('status', 'UnPaid')
            ->where('student_id', $student_id)
            ->get()
            ->getRow()->total ?? 0;
    }

    public function calculateTotalDiscount($student_id)
    {
        return $this->db->table('fee_chalan')
            ->select('SUM(discount) as total_discount')
            ->where('status', 'UnPaid')
            ->where('student_id', $student_id)
            ->get()
            ->getRow()->total_discount ?? 0;
    }

    public function getStudentClassInfo($student_id, $session_id, $status)
    {
        $builder = $this->db->table('student_class');
        if ($status == 3) {
            return $builder->where('student_id', $student_id)
                ->orderBy('sc_id', 'DESC')
                ->get()->getRow();
        }

        return $builder->where('student_id', $student_id)
            ->where('session_id', $session_id)
            ->get()->getRow();
    }

    public function getClassInfo($studentclassinfo)
    {
        if (!$studentclassinfo) return null;

        $class_section = $this->db->table('class_section')
            ->where('cls_sec_id', $studentclassinfo->cls_sec_id)
            ->get()->getRow();

        if ($class_section) {
            return $this->db->table('classes')
                ->where('class_id', $class_section->class_id)
                ->get()->getRow();
        }

        return null;
    }

    public function getSectionName($studentclassinfo)
    {
        if (!$studentclassinfo) return '';
        $class_section = $this->db->table('class_section')
            ->where('cls_sec_id', $studentclassinfo->cls_sec_id)
            ->get()->getRow();

        if ($class_section) {
            $section = $this->db->table('sections')
                ->where('section_id', $class_section->section_id)
                ->get()->getRow();
            return $section->section_name ?? '';
        }

        return '';
    }

    public function getClassFee($classinfo, $session_id, $campus_id)
    {
        if (!$classinfo) return null;

        return $this->db->table('fee_amount')
            ->where('class_id', $classinfo->class_id)
            ->where('session_id', $session_id)
            ->where('campus_id', $campus_id)
            ->whereIn('fee_type_id', function ($builder) {
                return $builder->select('fee_type_id')
                    ->from('fee_type')
                    ->where('is_monthly_fee', 1)
                    ->where('s_flag', 1);
            })
            ->get()->getRow();
    }

    public function getParentInfo($parent_id)
    {
        return $this->db->table('parents')
            ->where('parent_id', $parent_id)
            ->get()
            ->getRow();
    }
}