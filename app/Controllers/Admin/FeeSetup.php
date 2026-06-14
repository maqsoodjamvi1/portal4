<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Unified fee configuration wizard: types and class amounts.
 * Plan months live under Finance → Fee Plan Months (optional, not part of setup).
 */
class FeeSetup extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form']);

        if (!hasPermission('admin-fee-type') && !hasPermission('admin-fee-amount')) {
            check_permission('admin-fee-type');
        }
    }

    public function index()
    {
        $tab = $this->request->getGet('tab') ?? 'types';

        if ($tab === 'months') {
            return redirect()->to(base_url('admin/fee_plan_months'));
        }

        $allowedTabs = [];

        if (hasPermission('admin-fee-type') || hasPermission('admin-add-fee-type')) {
            $allowedTabs[] = 'types';
        }
        if (hasPermission('admin-fee-amount') || hasPermission('admin-add-fee-amount')) {
            $allowedTabs[] = 'amounts';
        }

        if (empty($allowedTabs)) {
            return redirect()->to(base_url('admin/dashboard'));
        }

        if (!in_array($tab, $allowedTabs, true)) {
            $tab = $allowedTabs[0];
        }

        $data = [
            'active_tab' => $tab,
            'allowed_tabs' => $allowedTabs,
            'can_types' => in_array('types', $allowedTabs, true),
            'can_amounts' => in_array('amounts', $allowedTabs, true),
        ];

        if ($data['can_types']) {
            $feeType = new FeeType(true);
            $data['fee_types'] = $feeType->getTypesForSetup();
            $data['type_count'] = count($data['fee_types']);
            $data['active_type_count'] = count(array_filter($data['fee_types'], static fn($r) => (int) $r->status === 1));
            $data['monthly_fee_locked'] = $feeType->isMonthlyFeeLocked();
        }

        if ($data['can_amounts']) {
            $feeAmount = new FeeAmount(true);
            $amountData = $feeAmount->getAddViewData();
            $amountData['embedded'] = true;
            $data = array_merge($data, $amountData);
        }

        return view('admin/fee_setup/index', $data);
    }
}
