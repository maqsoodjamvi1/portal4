<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\CampusFinanceService;
use App\Libraries\CampusFinanceInstaller;
use CodeIgniter\HTTP\ResponseInterface;

class CampusFinanceAccounts extends BaseController
{
    protected $db;
    protected CampusFinanceService $finance;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->finance = new CampusFinanceService($this->db);
        if (function_exists('hasPermission')
            && (hasPermission('admin-finance-accounts') || hasPermission('admin-accounts'))) {
            return;
        }
        check_permission('admin-finance-accounts');
    }

    public function index()
    {
        $campusId = (int) session('member_campusid');
        $userId   = (int) session('member_userid');

        $installer = new CampusFinanceInstaller($this->db);
        $setup = $installer->ensureAll();

        if ($this->finance->tablesReady()) {
            $this->finance->ensureCampusCashAccount($campusId, $userId);
        }

        return view('admin/campus_finance_accounts/index', [
            'settings'       => $this->finance->getSettings($campusId),
            'accounts'       => $this->finance->getAccountsForCampus($campusId, false),
            'account_types'  => CampusFinanceService::ACCOUNT_TYPES,
            'tables_ready'   => $this->finance->tablesReady(),
            'setup_messages' => $setup['messages'] ?? [],
        ]);
    }

    public function saveAccount(): ResponseInterface
    {
        if (! $this->finance->tablesReady()) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Finance tables not installed. Run database migration.']);
        }

        $campusId = (int) session('member_campusid');
        $userId   = (int) session('member_userid');
        $accountId = (int) $this->request->getPost('account_id');
        $type = strtolower(trim((string) $this->request->getPost('account_type')));
        $name = trim((string) $this->request->getPost('account_name'));
        $number = trim((string) $this->request->getPost('account_number'));
        $opening = (float) $this->request->getPost('opening_balance');
        $isActive = (int) $this->request->getPost('is_active') === 1 ? 1 : 0;

        if ($name === '') {
            return $this->response->setJSON(['success' => false, 'msg' => 'Account name is required']);
        }

        if (! in_array($type, CampusFinanceService::ACCOUNT_TYPES, true)) {
            $type = 'other';
        }

        $now = date('Y-m-d H:i:s');
        $data = [
            'account_type'    => $type,
            'account_name'    => $name,
            'account_number'  => $number !== '' ? $number : null,
            'opening_balance' => $opening,
            'is_active'       => $isActive,
            'user_id'         => $userId,
            'updated_date'    => $now,
        ];

        if ($accountId > 0) {
            $row = $this->db->table('campus_finance_accounts')
                ->where('account_id', $accountId)
                ->where('campus_id', $campusId)
                ->get()
                ->getRow();

            if (! $row) {
                return $this->response->setJSON(['success' => false, 'msg' => 'Account not found']);
            }

            if ((int) ($row->is_campus_cash ?? 0) === 1) {
                unset($data['account_type']);
            }

            $this->db->table('campus_finance_accounts')
                ->where('account_id', $accountId)
                ->update($data);
        } else {
            $data['campus_id']    = $campusId;
            $data['is_campus_cash'] = 0;
            $data['created_date'] = $now;
            $this->db->table('campus_finance_accounts')->insert($data);
        }

        return $this->response->setJSON(['success' => true, 'msg' => 'Account saved']);
    }

    public function saveSettings(): ResponseInterface
    {
        $campusId = (int) session('member_campusid');
        $userId   = (int) session('member_userid');
        $enable = (int) $this->request->getPost('enable_user_petty_cash') === 1 ? 1 : 0;

        $this->finance->saveSettings($campusId, $enable, $userId);

        return $this->response->setJSON(['success' => true, 'msg' => 'Settings saved']);
    }

    public function getBalances(): ResponseInterface
    {
        $campusId = (int) session('member_campusid');
        $accounts = $this->finance->getAccountsForCampus($campusId);
        $out = [];

        foreach ($accounts as $acc) {
            $out[] = [
                'account_id'   => (int) $acc->account_id,
                'account_name' => $acc->account_name,
                'account_type' => $acc->account_type,
                'balance'      => $this->finance->getAccountBalance((int) $acc->account_id),
            ];
        }

        return $this->response->setJSON(['success' => true, 'accounts' => $out]);
    }

    /**
     * Accounts list for pay/expense dropdowns (fee pay screen).
     */
    public function accountsJson(): ResponseInterface
    {
        $campusId = (int) session('member_campusid');
        $userId   = (int) session('member_userid');
        $payload  = $this->finance->getAccountsPayload($campusId, $userId);

        return $this->response->setJSON(array_merge(['success' => true], $payload, [
            'received_by_user_id' => $userId,
        ]));
    }
}
