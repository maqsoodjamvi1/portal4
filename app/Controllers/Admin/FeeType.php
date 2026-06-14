<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class FeeType extends BaseController
{
    protected $db;
    protected $session;

    public function __construct(bool $skipPermissionCheck = false)
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form']);
        if (!$skipPermissionCheck) {
            check_permission('admin-fee-type');
        }
    }

    public function index()
    {
        return redirect()->to(base_url('admin/fee_setup?tab=types'));
    }

    public function data()
    {
        $request = $this->request;
        $schoolinfo = getSchoolInfo();
        $keyword = $request->getPost('search')['value'] ?? '';

        $builder = $this->db->table('fee_type')->where('system_id', $schoolinfo->system_id);
        if ($keyword) {
            $builder->like('fee_type_name', $keyword);
        }

        $results = $builder->orderBy('fee_type_id', 'asc')
                            ->limit($request->getPost('length'), $request->getPost('start'))
                            ->get()
                            ->getResult();

        $total = $builder->countAllResults(false);

        $monthlyLocked = $this->isMonthlyFeeLocked();

        $data = [];
        $count = 1;
        foreach ($results as $row) {
            $data[] = [
                'id' => $row->fee_type_id,
                'sno' => $count,
                'fee_type_name' => $row->fee_type_name,
                'is_monthly_fee' => $row->is_monthly_fee,
                'status' => $row->status,
                'monthly_fee_locked' => $monthlyLocked,
            ];

            $count++;
        }

        return $this->response->setJSON([
            'draw' => $request->getPost('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'monthly_fee_locked' => $monthlyLocked,
            'data' => $data,
        ]);
    }

    public function add()
    {
        check_permission('admin-add-fee-type');

        return redirect()->to(base_url('admin/fee_setup?tab=types'));
    }

    /**
     * Fee types for setup form (used by fee_setup).
     */
    public function getTypesForSetup(): array
    {
        $schoolinfo = getSchoolInfo();

        return $this->db
            ->table('fee_type')
            ->where('system_id', $schoolinfo->system_id)
            ->orderBy('fee_type_id', 'asc')
            ->get()
            ->getResult();
    }

    /**
     * True once any fee type is marked monthly for this school (finance-critical; cannot be changed via UI).
     */
    public function isMonthlyFeeLocked(): bool
    {
        $schoolinfo = getSchoolInfo();

        return $this->db->table('fee_type')
            ->where('system_id', $schoolinfo->system_id)
            ->where('is_monthly_fee', 1)
            ->countAllResults() > 0;
    }



    public function save()
    {
        $request = $this->request;
        $user_id = $this->session->get('member_userid');
        $schoolinfo = getSchoolInfo();
        $campusid = $this->session->get('member_campusid');
        $date = date('Y-m-d H:i:s');
        $rowscount = $request->getPost('rowscount');

        if (!is_array($rowscount)) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Invalid row input']);
        }

        $inserted = false;
        $rowIndex = 0;

        foreach ($rowscount as $i) {
            $id = (int) $request->getPost('id' . $i);
            $fee_type_name = trim($request->getPost('fee_type_name' . $i));

            if ($fee_type_name === '') {
                continue;
            }

            $monthlyLocked = $this->isMonthlyFeeLocked();
            // New rows: only first form row may become monthly, and only if none is set yet
            $is_monthly_fee = (!$monthlyLocked && $rowIndex === 0) ? 1 : 0;

            if ($id === 0) {
                // Check for duplicate
                $exists = $this->db->table('fee_type')
                    ->where('fee_type_name', $fee_type_name)
                    ->where('system_id', $schoolinfo->system_id)
                    ->countAllResults();

                if ($exists === 0) {
                    $data = [
                        'fee_type_name'  => $fee_type_name,
                        'is_monthly_fee' => $is_monthly_fee,
                        'system_id'      => $schoolinfo->system_id,
                        'user_id'        => $user_id,
                        'std_type'        => 1,
                        's_flag'        => 1,
                        'created_date'   => $date,
                        'status'         => 1
                    ];
                    $this->db->table('fee_type')->insert($data);
                    $inserted = true;
                }
            } else {
                $data = [
                    'fee_type_name'  => $fee_type_name,
                    'user_id'        => $user_id,
                    'updated_date'   => $date
                    // ⚠️ If you also want to allow updating is_monthly_fee, add it here
                ];
                $this->db->table('fee_type')->where('fee_type_id', $id)->update($data);
                $inserted = true;
            }

            $rowIndex++;
        }

         $fee_amount_info = $this->db->table('fee_amount')
            ->where('campus_id', $campusid)
            ->get()
            ->getRow();

        if (empty($fee_amount_info->amount_id)) {
            return $this->response->setJSON(['amount_id' => false, 'msg' => 'Fee Structure Not Found']);
        }

        return $this->response->setJSON([
            'success' => $inserted,
            'msg' => $inserted ? 'Fee Type Saved Successfully' : 'No new data saved or updated',
        ]);
    }

   public function toggleStatus()
{
    $req = $this->request;

    // CSRF is auto-checked if enabled; otherwise validate token as you do elsewhere
    $fee_type_id = (int) $req->getPost('fee_type_id');
    if (!$fee_type_id) {
        return $this->response->setJSON(['success' => false, 'msg' => 'fee_type_id is required']);
    }

    $db = \Config\Database::connect();
    $row = $db->table('fee_type')->select('fee_type_id, status')->where('fee_type_id', $fee_type_id)->get()->getRow();
    if (!$row) {
        return $this->response->setJSON(['success' => false, 'msg' => 'Fee type not found']);
    }

    // toggle: if numeric 0/1; if you store strings, map accordingly
    $current = (string)($row->status ?? '0');
    $next    = ($current === '1' || strtolower($current) === 'active') ? 0 : 1;

    $ok  = $db->table('fee_type')->where('fee_type_id', $fee_type_id)->update([
        'status'      => $next,
        'updated_date'=> date('Y-m-d H:i:s'),
        'updated_id'  => (int) session('member_userid'),
    ]);
    $err = $db->error();
    if (!$ok || !empty($err['code'])) {
        return $this->response->setJSON([
            'success'=> false,
            'msg'    => 'Failed to toggle status: ['.($err['code']??'').'] '.($err['message']??''),
        ]);
    }

    return $this->response->setJSON([
        'success' => true,
        'msg'     => 'Status updated',
        'status'  => $next,
    ]);
}

    public function setMonthlyFee()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'msg' => 'Invalid request'
            ]);
        }

        $schoolinfo = getSchoolInfo();
        $fee_type_id = (int) $this->request->getPost('id');

        $feeType = $this->db->table('fee_type')
            ->where('fee_type_id', $fee_type_id)
            ->where('system_id', $schoolinfo->system_id)
            ->get()->getRow();

        if (!$feeType) {
            return $this->response->setJSON([
                'success' => false,
                'msg' => 'Fee Type not found',
            ]);
        }

        $currentMonthly = $this->db->table('fee_type')
            ->select('fee_type_id')
            ->where('system_id', $schoolinfo->system_id)
            ->where('is_monthly_fee', 1)
            ->orderBy('fee_type_id', 'ASC')
            ->get()
            ->getRow();

        if ($currentMonthly !== null) {
            $currentId = (int) $currentMonthly->fee_type_id;
            if ($currentId !== $fee_type_id) {
                return $this->response->setJSON([
                    'success' => false,
                    'locked' => true,
                    'msg' => 'The monthly fee type cannot be changed after it has been set. It drives fee calculations and challans. Contact support if a correction is required.',
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'msg' => 'Monthly fee type is already set.',
                'locked' => true,
            ]);
        }

        $this->db->transStart();

    // Set is_monthly_fee = 0 for all fee types of the same system
    $this->db->table('fee_type')
        ->where('system_id', $schoolinfo->system_id)
        ->update(['is_monthly_fee' => 0]);

    // Set is_monthly_fee = 1 for selected fee type
    $this->db->table('fee_type')
        ->where('fee_type_id', $fee_type_id)
        ->update(['is_monthly_fee' => 1]);

    $this->db->transComplete();

    if ($this->db->transStatus() === false) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Failed to update monthly fee type'
        ]);
    }

    return $this->response->setJSON([
        'success' => true,
        'msg' => 'Monthly fee type set successfully',
        'apply_lock_ui' => true,
    ]);


}
}
