<?php
namespace App\Models;

use CodeIgniter\Model;

class ReportModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    public function getStudentFeeSummary($campus_id, $session_id)
    {
        $sql = "
            WITH student_net_fee AS (
                SELECT 
                    s.student_id,
                    s.first_name,
                    s.last_name,
                    COALESCE(s.discounted_amount, 0) AS student_discount,
                    cs.class_id,
                    c.class_name,
                    fa.amount AS standard_monthly_fee,
                    (fa.amount - COALESCE(s.discounted_amount, 0)) AS net_monthly_fee,
                    s.campus_id,
                    sc.session_id
                FROM students s
                INNER JOIN student_class sc ON s.student_id = sc.student_id 
                    AND sc.session_id = ?
                    AND sc.status = 1
                INNER JOIN class_section cs ON sc.cls_sec_id = cs.cls_sec_id
                INNER JOIN a_classes c ON cs.class_id = c.class_id
                INNER JOIN fee_amount fa ON cs.class_id = fa.class_id 
                    AND fa.session_id = ?
                    AND fa.campus_id = ?
                INNER JOIN fee_type ft ON fa.fee_type_id = ft.fee_type_id
                WHERE s.status = '1'
                    AND s.campus_id = ?
                    AND ft.is_monthly_fee = 1
                    AND fa.amount IS NOT NULL
            )
            SELECT 
                net_monthly_fee AS fee_amount,
                COUNT(*) AS number_of_students,
                SUM(net_monthly_fee) AS total_monthly_fee_for_this_amount,
                ROUND(SUM(net_monthly_fee) * 12, 2) AS projected_annual_fee_for_this_amount,
                ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER(), 2) AS percentage_of_total_students,
                ROUND(SUM(net_monthly_fee) * 100.0 / SUM(SUM(net_monthly_fee)) OVER(), 2) AS percentage_of_total_fee
            FROM student_net_fee
            GROUP BY net_monthly_fee
            ORDER BY net_monthly_fee
        ";
        
        $summary = $this->db->query($sql, [$session_id, $session_id, $campus_id, $campus_id])->getResultArray();
        
        // Get totals
        $totals_sql = "
            SELECT 
                COUNT(*) AS total_active_students,
                SUM(fa.amount - COALESCE(s.discounted_amount, 0)) AS total_net_monthly_fee,
                SUM(fa.amount - COALESCE(s.discounted_amount, 0)) * 12 AS total_projected_annual_fee,
                SUM(fa.amount) AS total_standard_monthly_fee,
                SUM(COALESCE(s.discounted_amount, 0)) AS total_discount_given,
                ROUND(AVG(COALESCE(s.discounted_amount, 0)), 2) AS avg_discount_per_student,
                ROUND(AVG(fa.amount - COALESCE(s.discounted_amount, 0)), 2) AS avg_fee_per_student,
                MIN(fa.amount - COALESCE(s.discounted_amount, 0)) AS minimum_fee,
                MAX(fa.amount - COALESCE(s.discounted_amount, 0)) AS maximum_fee
            FROM students s
            INNER JOIN student_class sc ON s.student_id = sc.student_id 
                AND sc.session_id = ?
                AND sc.status = 1
            INNER JOIN class_section cs ON sc.cls_sec_id = cs.cls_sec_id
            INNER JOIN fee_amount fa ON cs.class_id = fa.class_id 
                AND fa.session_id = ?
                AND fa.campus_id = ?
            INNER JOIN fee_type ft ON fa.fee_type_id = ft.fee_type_id
            WHERE s.status = '1'
                AND s.campus_id = ?
                AND ft.is_monthly_fee = 1
        ";
        
        $totals = $this->db->query($totals_sql, [$session_id, $session_id, $campus_id, $campus_id])->getRowArray();
        
        // Get class-wise breakdown
        $class_breakdown_sql = "
            WITH student_net_fee AS (
                SELECT 
                    s.student_id,
                    COALESCE(s.discounted_amount, 0) AS student_discount,
                    cs.class_id,
                    c.class_name,
                    fa.amount AS standard_monthly_fee,
                    (fa.amount - COALESCE(s.discounted_amount, 0)) AS net_monthly_fee
                FROM students s
                INNER JOIN student_class sc ON s.student_id = sc.student_id 
                    AND sc.session_id = ?
                    AND sc.status = 1
                INNER JOIN class_section cs ON sc.cls_sec_id = cs.cls_sec_id
                INNER JOIN a_classes c ON cs.class_id = c.class_id
                INNER JOIN fee_amount fa ON cs.class_id = fa.class_id 
                    AND fa.session_id = ?
                    AND fa.campus_id = ?
                INNER JOIN fee_type ft ON fa.fee_type_id = ft.fee_type_id
                WHERE s.status = '1'
                    AND s.campus_id = ?
                    AND ft.is_monthly_fee = 1
                    AND fa.amount IS NOT NULL
            )
            SELECT 
                class_name,
                net_monthly_fee AS fee_amount,
                COUNT(*) AS number_of_students,
                SUM(net_monthly_fee) AS total_monthly_fee,
                SUM(net_monthly_fee) * 12 AS projected_annual_fee,
                ROUND(AVG(student_discount), 2) AS avg_discount_per_student
            FROM student_net_fee
            GROUP BY class_name, net_monthly_fee
            ORDER BY class_name, net_monthly_fee
        ";
        
        $class_breakdown = $this->db->query($class_breakdown_sql, [$session_id, $session_id, $campus_id, $campus_id])->getResultArray();
        
        // Get student details
        $student_details_sql = "
            SELECT 
                s.student_id,
                s.reg_no,
                CONCAT(s.first_name, ' ', s.last_name) AS student_name,
                c.class_name,
                sec.section_name,
                sc.cls_sec_id,
                fa.amount AS standard_monthly_fee,
                COALESCE(s.discounted_amount, 0) AS discount_amount,
                (fa.amount - COALESCE(s.discounted_amount, 0)) AS net_monthly_fee,
                (fa.amount - COALESCE(s.discounted_amount, 0)) * 12 AS projected_annual_fee
            FROM students s
            INNER JOIN student_class sc ON s.student_id = sc.student_id 
                AND sc.session_id = ?
                AND sc.status = 1
            INNER JOIN class_section cs ON sc.cls_sec_id = cs.cls_sec_id
            INNER JOIN a_classes c ON cs.class_id = c.class_id
            INNER JOIN sections sec ON cs.section_id = sec.section_id
            INNER JOIN fee_amount fa ON cs.class_id = fa.class_id 
                AND fa.session_id = ?
                AND fa.campus_id = ?
            INNER JOIN fee_type ft ON fa.fee_type_id = ft.fee_type_id
            WHERE s.status = '1'
                AND s.campus_id = ?
                AND ft.is_monthly_fee = 1
            ORDER BY c.class_name, sec.section_name, net_monthly_fee DESC
        ";
        
        $student_details = $this->db->query($student_details_sql, [$session_id, $session_id, $campus_id, $campus_id])->getResultArray();
        
        return [
            'summary' => $summary,
            'totals' => $totals,
            'class_breakdown' => $class_breakdown,
            'student_details' => $student_details
        ];
    }
}