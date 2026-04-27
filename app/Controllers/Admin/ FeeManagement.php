<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class FeeManagement extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url', 'text']);
        check_permission('admin-fee-management');
    }

    public function index()
    {
        $schoolinfo = getSchoolInfo();
        $systemId = $schoolinfo->system_id ?? 0;
        $campusId = $this->session->get('campus_id') ?? 0;

        // Get all sessions
        $sessions = $this->db->table('academic_session')
            ->where('system_id', $systemId)
            ->orderBy('session_id', 'DESC')
            ->get()->getResult();

        // Get fee types
        $feeTypes = $this->db->table('fee_type')
            ->where('system_id', $systemId)
            ->where('status', 1)
            ->orderBy('fee_type_name', 'ASC')
            ->get()->getResult();

        // Get classes for fee structure
        $classes = $this->db->table('classes')
            ->where('system_id', $systemId)
            ->where('status', 1)
            ->orderBy('class_name', 'ASC')
            ->get()->getResult();

        $data = [
            'system_id' => $systemId,
            'campus_id' => $campusId,
            'sessions' => $sessions,
            'fee_types' => $feeTypes,
            'classes' => $classes,
            'current_session' => !empty($sessions) ? $sessions[0] : null
        ];

        return view('admin/fee_management', $data);
    }

    public function saveFeeTypes()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Invalid request']);
        }

        $schoolinfo = getSchoolInfo();
        $systemId = $schoolinfo->system_id ?? 0;
        $userId = $this->session->get('member_userid');
        $now = date('Y-m-d H:i:s');

        $feeTypes = $this->request->getPost('fee_types');
        
        if (empty($feeTypes)) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Please add at least one fee type']);
        }

        $this->db->transBegin();

        try {
            foreach ($feeTypes as $feeType) {
                // Check if fee type already exists
                $existing = $this->db->table('fee_type')
                    ->where('system_id', $systemId)
                    ->where('fee_type_name', $feeType['name'])
                    ->get()->getRow();

                if ($existing) {
                    // Update only if is_monthly is not already set to 1
                    if ($existing->is_monthly_fee == 1 && $feeType['is_monthly'] == 1) {
                        // Skip - already monthly
                        continue;
                    }
                    
                    $updateData = [
                        'fee_type_detail' => $feeType['detail'] ?? '',
                        'updated_date' => $now,
                        'user_id' => $userId
                    ];
                    
                    // Only allow is_monthly to be set if not already 1
                    if ($existing->is_monthly_fee != 1) {
                        $updateData['is_monthly_fee'] = $feeType['is_monthly'] ?? 0;
                    }
                    
                    $this->db->table('fee_type')
                        ->where('fee_type_id', $existing->fee_type_id)
                        ->update($updateData);
                } else {
                    // Insert new fee type
                    $this->db->table('fee_type')->insert([
                        'system_id' => $systemId,
                        'fee_type_name' => $feeType['name'],
                        'fee_type_detail' => $feeType['detail'] ?? '',
                        'is_monthly_fee' => $feeType['is_monthly'] ?? 0,
                        'is_transport_fee' => $feeType['is_transport'] ?? 0,
                        'status' => 1,
                        'created_date' => $now,
                        'user_id' => $userId
                    ]);
                }
            }
            
            $this->db->transCommit();
            return $this->response->setJSON(['success' => true, 'msg' => 'Fee types saved successfully']);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setJSON(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function getFeeTypes()
    {
        $schoolinfo = getSchoolInfo();
        $systemId = $schoolinfo->system_id ?? 0;
        
        $feeTypes = $this->db->table('fee_type')
            ->where('system_id', $systemId)
            ->where('status', 1)
            ->orderBy('fee_type_name', 'ASC')
            ->get()->getResult();
        
        return $this->response->setJSON(['success' => true, 'data' => $feeTypes]);
    }

    public function getClasses()
    {
        $schoolinfo = getSchoolInfo();
        $systemId = $schoolinfo->system_id ?? 0;
        
        $classes = $this->db->table('classes')
            ->where('system_id', $systemId)
            ->where('status', 1)
            ->orderBy('class_name', 'ASC')
            ->get()->getResult();
        
        return $this->response->setJSON(['success' => true, 'data' => $classes]);
    }

    public function getSessions()
    {
        $schoolinfo = getSchoolInfo();
        $systemId = $schoolinfo->system_id ?? 0;
        
        $sessions = $this->db->table('academic_session')
            ->where('system_id', $systemId)
            ->orderBy('session_id', 'DESC')
            ->get()->getResult();
        
        return $this->response->setJSON(['success' => true, 'data' => $sessions]);
    }

    public function getFeeStructure()
    {
        $campusId = $this->session->get('campus_id') ?? 0;
        $sessionId = $this->request->getGet('session_id');
        
        if (!$campusId || !$sessionId) {
            return $this->response->setJSON(['success' => true, 'data' => []]);
        }
        
        $feeStructure = $this->db->table('fee_amount fa')
            ->select('fa.*, ft.fee_type_name, ft.is_monthly_fee, c.class_name')
            ->join('fee_type ft', 'ft.fee_type_id = fa.fee_type_id')
            ->join('classes c', 'c.class_id = fa.class_id')
            ->where('fa.campus_id', $campusId)
            ->where('fa.session_id', $sessionId)
            ->orderBy('c.class_name', 'ASC')
            ->orderBy('ft.fee_type_name', 'ASC')
            ->get()->getResult();
        
        return $this->response->setJSON(['success' => true, 'data' => $feeStructure]);
    }

    public function saveFeeStructure()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Invalid request']);
        }

        $schoolinfo = getSchoolInfo();
        $campusId = $this->session->get('campus_id') ?? 0;
        $userId = $this->session->get('member_userid');
        $now = date('Y-m-d H:i:s');

        $sessionId = $this->request->getPost('session_id');
        $feeData = $this->request->getPost('fee_data');
        
        if (!$sessionId || empty($feeData)) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Invalid data']);
        }

        $this->db->transBegin();

        try {
            // Delete existing fee structure for this session
            $this->db->table('fee_amount')
                ->where('campus_id', $campusId)
                ->where('session_id', $sessionId)
                ->delete();
            
            // Insert new fee structure
            foreach ($feeData as $item) {
                $this->db->table('fee_amount')->insert([
                    'campus_id' => $campusId,
                    'fee_type_id' => $item['fee_type_id'],
                    'class_id' => $item['class_id'],
                    'amount' => $item['amount'],
                    'session_id' => $sessionId,
                    'currency_code' => 'PKR',
                    'created_date' => $now,
                    'user_id' => $userId
                ]);
            }
            
            $this->db->transCommit();
            return $this->response->setJSON(['success' => true, 'msg' => 'Fee structure saved successfully']);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setJSON(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function copyFromPreviousSession()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Invalid request']);
        }

        $campusId = $this->session->get('campus_id') ?? 0;
        $sourceSessionId = $this->request->getPost('source_session_id');
        $targetSessionId = $this->request->getPost('target_session_id');
        
        if (!$campusId || !$sourceSessionId || !$targetSessionId) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Invalid parameters']);
        }

        $sourceData = $this->db->table('fee_amount')
            ->where('campus_id', $campusId)
            ->where('session_id', $sourceSessionId)
            ->get()->getResult();
        
        return $this->response->setJSON(['success' => true, 'data' => $sourceData]);
    }
}