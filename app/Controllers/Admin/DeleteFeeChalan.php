<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DeleteFeeChalan extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = \Config\Services::session();
        
        // Check permission (you'll need to implement this function or use your existing permission system)
        $this->checkPermission('admin-del-fee-chalan');
    }

    /**
     * Check if user has permission
     */
    private function checkPermission(string $permission): void
    {
        // Implement your permission checking logic here
        // This is a placeholder - adjust based on your permission system
        if (!function_exists('check_permission')) {
            // If you have a helper function, load it
            helper('permission');
        }
        
        if (function_exists('check_permission')) {
            check_permission($permission);
        }
    }

    /**
     * Index Page - Show fee chalans created today
     */

public function index()
{
    $campusid = (int) session()->get('member_campusid');
    $date = date('Y-m-d');
    
    $sql = "SELECT fc.*, 
                   CONCAT(s.first_name, ' ', COALESCE(s.last_name, '')) as student_name,
                   ft.fee_type_name
            FROM fee_chalan fc
            INNER JOIN students s ON s.student_id = fc.student_id
            LEFT JOIN fee_type ft ON ft.fee_type_id = fc.fee_type_id
            WHERE DATE(fc.created_date) = ? 
            AND fc.status = 'unpaid'
            AND s.campus_id = ?
            ORDER BY fc.created_date DESC";
    
    $query = $this->db->query($sql, [$date, $campusid]);
    
    $data['studentFeeDetail'] = $query->getResultArray();
    
    return view('admin/delete_fee_chalan_edit', $data);
}

    /**
     * Delete today's unpaid fee chalans
     */
    public function delete()
    {
        $this->checkPermission('admin-del-fee-chalan');
        
        $campusid = (int) $this->session->get('member_campusid');
        $date = date('Y-m-d');

        // Start transaction
        $this->db->transBegin();

        try {
            // Delete fee chalans created today for this campus
            $sql = "DELETE fc FROM fee_chalan fc
                    INNER JOIN students s ON s.student_id = fc.student_id
                    WHERE DATE(fc.created_date) = ? 
                    AND fc.status = 'unpaid'
                    AND s.campus_id = ?";

            $this->db->query($sql, [$date, $campusid]);
            
            // Check if any rows were affected
            $affectedRows = $this->db->affectedRows();

            // Commit transaction
            $this->db->transCommit();

            return $this->response->setJSON([
                'success' => true,
                'msg' => $affectedRows . ' Fee Chalan(s) Deleted Successfully'
            ]);

        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->db->transRollback();

            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Error deleting fee chalans: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete selected fee chalans by IDs
     */
    public function deleteSelected()
    {
        $this->checkPermission('admin-del-fee-chalan');
        
        $chalanIds = $this->request->getPost('chalan_ids');
        
        if (empty($chalanIds)) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'No fee chalans selected for deletion'
            ]);
        }

        // Start transaction
        $this->db->transBegin();

        try {
            foreach ($chalanIds as $id) {
                $this->db->table('fee_chalan')->delete(['chalan_id' => $id]);
            }

            $this->db->transCommit();

            return $this->response->setJSON([
                'success' => true,
                'msg' => count($chalanIds) . ' Fee Chalan(s) Deleted Successfully'
            ]);

        } catch (\Exception $e) {
            $this->db->transRollback();

            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Error deleting fee chalans: ' . $e->getMessage()
            ]);
        }
    }
}