<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Campus-scoped finance ledger (fee receipts, expenses, salary).
 */
class CampusFinanceService
{
    protected BaseConnection $db;

    public const ACCOUNT_TYPES = ['cash', 'bank', 'easypaisa', 'jazzcash', 'other'];

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function tablesReady(): bool
    {
        return $this->db->tableExists('campus_finance_accounts')
            && $this->db->tableExists('finance_transactions');
    }

    public function campusHasFinanceAccounts(int $campusId): bool
    {
        if (! $this->tablesReady() || $campusId <= 0) {
            return false;
        }

        return $this->db->table('campus_finance_accounts')
            ->where('campus_id', $campusId)
            ->where('is_active', 1)
            ->countAllResults() > 0;
    }

    public function getSettings(int $campusId): object
    {
        $defaults = (object) [
            'enable_user_petty_cash' => 0,
        ];

        if (! $this->db->tableExists('campus_finance_settings') || $campusId <= 0) {
            return $defaults;
        }

        $row = $this->db->table('campus_finance_settings')
            ->where('campus_id', $campusId)
            ->get()
            ->getRow();

        return $row ?: $defaults;
    }

    public function saveSettings(int $campusId, int $enablePettyCash, int $userId): void
    {
        if (! $this->db->tableExists('campus_finance_settings')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $existing = $this->db->table('campus_finance_settings')
            ->where('campus_id', $campusId)
            ->get()
            ->getRow();

        $data = [
            'enable_user_petty_cash' => $enablePettyCash ? 1 : 0,
            'user_id'                => $userId,
            'updated_date'           => $now,
        ];

        if ($existing) {
            $this->db->table('campus_finance_settings')
                ->where('campus_id', $campusId)
                ->update($data);
        } else {
            $data['campus_id']    = $campusId;
            $data['created_date'] = $now;
            $this->db->table('campus_finance_settings')->insert($data);
        }
    }

    /**
     * @return list<object>
     */
    public function getAccountsForCampus(int $campusId, bool $activeOnly = true): array
    {
        if (! $this->tablesReady() || $campusId <= 0) {
            return [];
        }

        $b = $this->db->table('campus_finance_accounts')
            ->where('campus_id', $campusId)
            ->orderBy('is_campus_cash', 'DESC')
            ->orderBy('account_name', 'ASC');

        if ($activeOnly) {
            $b->where('is_active', 1);
        }

        return $b->get()->getResult();
    }

    public function ensureCampusCashAccount(int $campusId, int $userId): int
    {
        if (! $this->tablesReady()) {
            return 0;
        }

        $existing = $this->db->table('campus_finance_accounts')
            ->where('campus_id', $campusId)
            ->where('is_campus_cash', 1)
            ->get()
            ->getRow();

        if ($existing) {
            return (int) $existing->account_id;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('campus_finance_accounts')->insert([
            'campus_id'       => $campusId,
            'account_type'    => 'cash',
            'account_name'    => 'Campus Cash',
            'account_number'  => null,
            'is_campus_cash'  => 1,
            'is_active'       => 1,
            'opening_balance' => 0,
            'user_id'         => $userId,
            'created_date'    => $now,
            'updated_date'    => $now,
        ]);

        return (int) $this->db->insertID();
    }

    public function getCampusCashAccountId(int $campusId): int
    {
        if (! $this->tablesReady()) {
            return 0;
        }

        $row = $this->db->table('campus_finance_accounts')
            ->select('account_id')
            ->where('campus_id', $campusId)
            ->where('is_campus_cash', 1)
            ->where('is_active', 1)
            ->get()
            ->getRow();

        return $row ? (int) $row->account_id : 0;
    }

    public function getOrCreateUserPettyCashAccount(int $campusId, int $userId, int $createdBy): int
    {
        if (! $this->tablesReady() || $campusId <= 0 || $userId <= 0) {
            return 0;
        }

        $link = $this->db->table('user_finance_accounts')
            ->where('campus_id', $campusId)
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->get()
            ->getRow();

        if ($link) {
            return (int) $link->account_id;
        }

        $user = $this->db->table('users')
            ->select('first_name, last_name, username')
            ->where('id', $userId)
            ->get()
            ->getRow();

        $label = $user
            ? trim((string) ($user->first_name ?? '') . ' ' . (string) ($user->last_name ?? ''))
            : ('User #' . $userId);
        if ($label === '' && $user) {
            $label = (string) ($user->username ?? ('User #' . $userId));
        }
        $now   = date('Y-m-d H:i:s');

        $this->db->table('campus_finance_accounts')->insert([
            'campus_id'       => $campusId,
            'account_type'    => 'cash',
            'account_name'    => 'Petty Cash - ' . $label,
            'account_number'  => null,
            'is_campus_cash'  => 0,
            'is_active'       => 1,
            'opening_balance' => 0,
            'user_id'         => $createdBy,
            'created_date'    => $now,
            'updated_date'    => $now,
        ]);

        $accountId = (int) $this->db->insertID();

        $this->db->table('user_finance_accounts')->insert([
            'campus_id'    => $campusId,
            'user_id'      => $userId,
            'account_id'   => $accountId,
            'is_active'    => 1,
            'created_date' => $now,
        ]);

        return $accountId;
    }

    public function resolveDefaultReceiptAccount(int $userId, int $campusId): int
    {
        if (! $this->campusHasFinanceAccounts($campusId)) {
            return 0;
        }

        $settings = $this->getSettings($campusId);
        if ((int) ($settings->enable_user_petty_cash ?? 0) === 1 && $userId > 0) {
            $petty = $this->getOrCreateUserPettyCashAccount($campusId, $userId, $userId);
            if ($petty > 0) {
                return $petty;
            }
        }

        $cashId = $this->getCampusCashAccountId($campusId);
        if ($cashId > 0) {
            return $cashId;
        }

        return $this->ensureCampusCashAccount($campusId, $userId);
    }

    public function validateAccountForCampus(int $accountId, int $campusId): bool
    {
        if ($accountId <= 0 || $campusId <= 0) {
            return false;
        }

        return $this->db->table('campus_finance_accounts')
            ->where('account_id', $accountId)
            ->where('campus_id', $campusId)
            ->where('is_active', 1)
            ->countAllResults() > 0;
    }

    /**
     * Record fee receipt and mark challans paid inside a DB transaction.
     *
     * @param list<array{chalan_id:int,amount?:float,discount?:float}> $fees
     * @return array{success:bool,message?:string,transaction_id?:int,last_chalan_id?:int}
     */
    public function recordFeeReceipt(
        int $campusId,
        int $studentId,
        array $fees,
        string $paidDate,
        int $receivedByUserId,
        int $accountId,
        int $createdBy
    ): array {
        if (! $fees) {
            return ['success' => false, 'message' => 'No fees to pay'];
        }

        $useLedger = $this->campusHasFinanceAccounts($campusId);

        if ($useLedger) {
            if ($accountId <= 0) {
                $accountId = $this->resolveDefaultReceiptAccount($receivedByUserId, $campusId);
            }
            if (! $this->validateAccountForCampus($accountId, $campusId)) {
                return ['success' => false, 'message' => 'Invalid collection account'];
            }
        }

        $paidDateNorm = date('Y-m-d', strtotime($paidDate ?: 'now'));
        $now          = date('Y-m-d H:i:s');
        $totalNet     = 0.0;
        $lineItems    = [];

        foreach ($fees as $fee) {
            $chalanId = (int) ($fee['chalan_id'] ?? 0);
            if ($chalanId <= 0) {
                continue;
            }

            $row = $this->db->table('fee_chalan fc')
                ->select('fc.*, s.campus_id')
                ->join('students s', 's.student_id = fc.student_id', 'inner')
                ->where('fc.chalan_id', $chalanId)
                ->get()
                ->getRow();

            if (! $row || (int) $row->campus_id !== $campusId) {
                return ['success' => false, 'message' => 'Invalid challan for this campus'];
            }

            $status = strtolower((string) ($row->status ?? ''));
            if ($status === 'paid') {
                continue;
            }

            $amount   = isset($fee['amount']) ? (float) $fee['amount'] : (float) $row->amount;
            $discount = isset($fee['discount']) ? (float) $fee['discount'] : (float) $row->discount;
            $net      = $amount - $discount;
            $totalNet += $net;

            $lineItems[] = [
                'chalan_id' => $chalanId,
                'amount'    => $amount,
                'discount'  => $discount,
                'net'       => $net,
            ];
        }

        if ($lineItems === []) {
            return ['success' => false, 'message' => 'No valid unpaid challans'];
        }

        $this->db->transStart();

        $transactionId = 0;
        if ($useLedger) {
            $this->db->table('finance_transactions')->insert([
                'campus_id'           => $campusId,
                'transaction_type'    => 'fee_receipt',
                'direction'           => 'credit',
                'amount'              => round($totalNet, 2),
                'account_id'          => $accountId,
                'received_by_user_id' => $receivedByUserId,
                'transaction_date'    => $paidDateNorm,
                'reference_type'      => 'fee_receipt_batch',
                'reference_id'        => $studentId,
                'notes'               => null,
                'is_reversed'         => 0,
                'created_by'          => $createdBy,
                'created_date'        => $now,
            ]);
            $transactionId = (int) $this->db->insertID();

            foreach ($lineItems as $item) {
                $this->db->table('finance_transaction_items')->insert([
                    'transaction_id' => $transactionId,
                    'chalan_id'      => $item['chalan_id'],
                    'amount'         => $item['amount'],
                    'discount'       => $item['discount'],
                ]);
            }
        }

        $updateData = [
            'status'       => 'paid',
            'updated_date' => $now,
            'user_id'      => $receivedByUserId,
            'paid_date'    => $paidDateNorm,
        ];

        if ($useLedger && $this->db->fieldExists('finance_transaction_id', 'fee_chalan')) {
            $updateData['finance_transaction_id']   = $transactionId;
            $updateData['collection_account_id']    = $accountId;
        }

        foreach ($lineItems as $item) {
            $this->db->table('fee_chalan')
                ->where('chalan_id', $item['chalan_id'])
                ->update($updateData);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return ['success' => false, 'message' => 'Payment failed'];
        }

        $lastChalan = $this->db->table('fee_chalan')
            ->select('chalan_id')
            ->where('status', 'paid')
            ->orderBy('paid_date', 'DESC')
            ->limit(1)
            ->get()
            ->getRow('chalan_id');

        return [
            'success'        => true,
            'transaction_id' => $transactionId,
            'last_chalan_id' => $lastChalan ? (int) $lastChalan : null,
        ];
    }

    /**
     * Reverse fee payment (make unpaid) when paid today via ledger.
     */
    public function reverseFeeReceipt(int $chalanId, int $campusId, int $userId): array
    {
        $chalan = $this->db->table('fee_chalan fc')
            ->select('fc.*, s.campus_id')
            ->join('students s', 's.student_id = fc.student_id', 'inner')
            ->where('fc.chalan_id', $chalanId)
            ->get()
            ->getRow();

        if (! $chalan) {
            return ['success' => false, 'message' => 'Fee record not found'];
        }

        if ((int) $chalan->campus_id !== $campusId) {
            return ['success' => false, 'message' => 'Invalid campus'];
        }

        $txnId = (int) ($chalan->finance_transaction_id ?? 0);

        $this->db->transStart();

        $update = [
            'status'       => 'unpaid',
            'paid_date'    => null,
            'user_id'      => $userId,
            'updated_date' => date('Y-m-d H:i:s'),
        ];

        if ($this->db->fieldExists('finance_transaction_id', 'fee_chalan')) {
            $update['finance_transaction_id']  = null;
            $update['collection_account_id']   = null;
        }

        $this->db->table('fee_chalan')->where('chalan_id', $chalanId)->update($update);

        if ($txnId > 0 && $this->tablesReady()) {
            $orig = $this->db->table('finance_transactions')
                ->where('transaction_id', $txnId)
                ->where('is_reversed', 0)
                ->get()
                ->getRow();

            if ($orig) {
                $now = date('Y-m-d H:i:s');
                $this->db->table('finance_transactions')
                    ->where('transaction_id', $txnId)
                    ->update(['is_reversed' => 1]);

                $this->db->table('finance_transactions')->insert([
                    'campus_id'           => $campusId,
                    'transaction_type'    => 'fee_receipt',
                    'direction'           => 'debit',
                    'amount'              => $orig->amount,
                    'account_id'          => $orig->account_id,
                    'received_by_user_id' => $userId,
                    'transaction_date'    => date('Y-m-d'),
                    'reference_type'      => 'fee_reversal',
                    'reference_id'        => $chalanId,
                    'notes'               => 'Reversal of transaction #' . $txnId,
                    'is_reversed'         => 0,
                    'reversal_of'         => $txnId,
                    'created_by'          => $userId,
                    'created_date'        => $now,
                ]);
            }
        }

        $this->db->transComplete();

        return ['success' => $this->db->transStatus() !== false];
    }

    /**
     * Ledger credit without changing fee_chalan status (e.g. advance top-up).
     */
    public function recordStandaloneCredit(
        int $campusId,
        int $accountId,
        float $amount,
        string $transactionDate,
        int $receivedByUserId,
        string $referenceType,
        int $referenceId,
        ?int $chalanId = null,
        string $transactionType = 'fee_receipt'
    ): int {
        if (! $this->campusHasFinanceAccounts($campusId) || $amount <= 0) {
            return 0;
        }

        if ($accountId <= 0) {
            $accountId = $this->resolveDefaultReceiptAccount($receivedByUserId, $campusId);
        }

        if (! $this->validateAccountForCampus($accountId, $campusId)) {
            return 0;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('finance_transactions')->insert([
            'campus_id'           => $campusId,
            'transaction_type'    => $transactionType,
            'direction'           => 'credit',
            'amount'              => round($amount, 2),
            'account_id'          => $accountId,
            'received_by_user_id' => $receivedByUserId,
            'transaction_date'    => date('Y-m-d', strtotime($transactionDate ?: 'now')),
            'reference_type'      => $referenceType,
            'reference_id'        => $referenceId,
            'is_reversed'         => 0,
            'created_by'          => $receivedByUserId,
            'created_date'        => $now,
        ]);

        $txnId = (int) $this->db->insertID();

        if ($chalanId > 0 && $this->db->fieldExists('collection_account_id', 'fee_chalan')) {
            $this->db->table('fee_chalan')->where('chalan_id', $chalanId)->update([
                'collection_account_id'  => $accountId,
                'finance_transaction_id' => $txnId,
            ]);
        }

        if ($chalanId > 0) {
            $this->db->table('finance_transaction_items')->insert([
                'transaction_id' => $txnId,
                'chalan_id'      => $chalanId,
                'amount'         => round($amount, 2),
                'discount'       => 0,
            ]);
        }

        return $txnId;
    }

    public function recordExpense(
        int $expenseId,
        int $campusId,
        float $amount,
        string $expenseDate,
        int $accountId,
        int $paidByUserId,
        string $title = ''
    ): int {
        if (! $this->campusHasFinanceAccounts($campusId) || $amount <= 0) {
            return 0;
        }

        if ($accountId <= 0) {
            $accountId = $this->getCampusCashAccountId($campusId)
                ?: $this->ensureCampusCashAccount($campusId, $paidByUserId);
        }

        if (! $this->validateAccountForCampus($accountId, $campusId)) {
            return 0;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('finance_transactions')->insert([
            'campus_id'           => $campusId,
            'transaction_type'    => 'expense',
            'direction'           => 'debit',
            'amount'              => round($amount, 2),
            'account_id'          => $accountId,
            'received_by_user_id' => $paidByUserId,
            'transaction_date'    => date('Y-m-d', strtotime($expenseDate ?: 'now')),
            'reference_type'      => 'expense',
            'reference_id'        => $expenseId,
            'notes'               => $title ?: null,
            'is_reversed'         => 0,
            'created_by'          => $paidByUserId,
            'created_date'        => $now,
        ]);

        $txnId = (int) $this->db->insertID();

        if ($this->db->fieldExists('finance_transaction_id', 'expenses')) {
            $this->db->table('expenses')->where('expense_id', $expenseId)->update([
                'finance_transaction_id' => $txnId,
                'account_id'             => $accountId,
            ]);
        }

        return $txnId;
    }

    public function recordSalaryPayment(
        int $slipId,
        int $campusId,
        float $amount,
        int $accountId,
        int $paidByUserId,
        string $notes = ''
    ): int {
        if (! $this->campusHasFinanceAccounts($campusId) || $amount <= 0 || $slipId <= 0) {
            return 0;
        }

        if ($accountId <= 0) {
            $accountId = $this->getCampusCashAccountId($campusId)
                ?: $this->ensureCampusCashAccount($campusId, $paidByUserId);
        }

        if (! $this->validateAccountForCampus($accountId, $campusId)) {
            return 0;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('finance_transactions')->insert([
            'campus_id'           => $campusId,
            'transaction_type'    => 'salary',
            'direction'           => 'debit',
            'amount'              => round($amount, 2),
            'account_id'          => $accountId,
            'received_by_user_id' => $paidByUserId,
            'transaction_date'    => date('Y-m-d'),
            'reference_type'      => 'salary_slip',
            'reference_id'        => $slipId,
            'notes'               => $notes ?: null,
            'is_reversed'         => 0,
            'created_by'          => $paidByUserId,
            'created_date'        => $now,
        ]);

        $txnId = (int) $this->db->insertID();

        if ($this->db->tableExists('salary_slips') && $this->db->fieldExists('finance_transaction_id', 'salary_slips')) {
            $this->db->table('salary_slips')->where('slip_id', $slipId)->update([
                'finance_transaction_id' => $txnId,
                'paid_from_account_id'   => $accountId,
            ]);
        }

        return $txnId;
    }

    public function getAccountBalance(int $accountId, ?string $asOfDate = null): float
    {
        if ($accountId <= 0 || ! $this->tablesReady()) {
            return 0.0;
        }

        $acct = $this->db->table('campus_finance_accounts')
            ->where('account_id', $accountId)
            ->get()
            ->getRow();

        if (! $acct) {
            return 0.0;
        }

        $opening = (float) ($acct->opening_balance ?? 0);

        $b = $this->db->table('finance_transactions')
            ->select("
                COALESCE(SUM(CASE WHEN direction = 'credit' AND is_reversed = 0 THEN amount ELSE 0 END), 0) AS credits,
                COALESCE(SUM(CASE WHEN direction = 'debit' AND is_reversed = 0 THEN amount ELSE 0 END), 0) AS debits
            ", false)
            ->where('account_id', $accountId);

        if ($asOfDate) {
            $b->where('transaction_date <=', date('Y-m-d', strtotime($asOfDate)));
        }

        $sums = $b->get()->getRow();

        return $opening + (float) ($sums->credits ?? 0) - (float) ($sums->debits ?? 0);
    }

    /**
     * Monthly cash-flow summary for dashboard.
     */
    public function getMonthlySummary(int $campusId, int $year, int $month): array
    {
        $monthStart = sprintf('%04d-%02d-01', $year, $month);
        $monthEnd   = date('Y-m-t', strtotime($monthStart));

        $feeIncome = $this->sumFeeIncome($campusId, $monthStart, $monthEnd);
        $ledgerIncome = 0.0;
        $expenseByHead = [];
        $salaryOut = 0.0;
        $accounts = [];

        if ($this->tablesReady() && $this->campusHasFinanceAccounts($campusId)) {
            $ledgerIncome = $this->sumLedger($campusId, $monthStart, $monthEnd, 'fee_receipt', 'credit');
            $expenseByHead = $this->sumExpensesByHead($campusId, $monthStart, $monthEnd);
            $salaryOut = $this->sumLedger($campusId, $monthStart, $monthEnd, 'salary', 'debit');

            foreach ($this->getAccountsForCampus($campusId) as $acc) {
                $accounts[] = [
                    'account_id'   => (int) $acc->account_id,
                    'account_name' => $acc->account_name,
                    'account_type' => $acc->account_type,
                    'is_campus_cash' => (int) $acc->is_campus_cash,
                    'balance'      => $this->getAccountBalance((int) $acc->account_id, $monthEnd),
                ];
            }
        }

        $income = $ledgerIncome > 0 ? $ledgerIncome : $feeIncome;
        $expenseTotal = array_sum(array_column($expenseByHead, 'total'));
        $totalOut = $expenseTotal + $salaryOut;

        $pettyCash = [];
        if ($this->db->tableExists('user_finance_accounts')) {
            $links = $this->db->table('user_finance_accounts ufa')
                ->select('ufa.user_id, ufa.account_id, u.first_name, u.last_name, u.username')
                ->join('users u', 'u.id = ufa.user_id', 'left')
                ->where('ufa.campus_id', $campusId)
                ->where('ufa.is_active', 1)
                ->get()
                ->getResult();

            foreach ($links as $link) {
                $display = trim((string) ($link->first_name ?? '') . ' ' . (string) ($link->last_name ?? ''));
                if ($display === '') {
                    $display = (string) ($link->username ?? ('User #' . $link->user_id));
                }
                $pettyCash[] = [
                    'user_id'   => (int) $link->user_id,
                    'user_name' => $display,
                    'balance'   => $this->getAccountBalance((int) $link->account_id, $monthEnd),
                ];
            }
        }

        $campusCashBalance = 0.0;
        $cashId = $this->getCampusCashAccountId($campusId);
        if ($cashId > 0) {
            $campusCashBalance = $this->getAccountBalance($cashId, $monthEnd);
        }

        return [
            'fee_income'           => $feeIncome,
            'ledger_fee_income'    => $ledgerIncome,
            'income'               => $income,
            'expenses_by_head'     => $expenseByHead,
            'expense_total'        => $expenseTotal,
            'salary_outflow'       => $salaryOut,
            'total_outflow'        => $totalOut,
            'net'                  => $income - $totalOut,
            'accounts'             => $accounts,
            'petty_cash'           => $pettyCash,
            'campus_cash_balance'  => $campusCashBalance,
            'month_start'          => $monthStart,
            'month_end'            => $monthEnd,
        ];
    }

    protected function sumFeeIncome(int $campusId, string $from, string $to): float
    {
        $row = $this->db->table('fee_chalan fc')
            ->select('COALESCE(SUM(fc.amount - fc.discount), 0) AS net', false)
            ->join('students s', 's.student_id = fc.student_id', 'inner')
            ->where('s.campus_id', $campusId)
            ->whereIn('fc.status', ['paid', 'Paid'])
            ->where('fc.paid_date >=', $from)
            ->where('fc.paid_date <=', $to)
            ->get()
            ->getRow();

        return (float) ($row->net ?? 0);
    }

    protected function sumLedger(int $campusId, string $from, string $to, string $type, string $direction): float
    {
        $row = $this->db->table('finance_transactions')
            ->select('COALESCE(SUM(amount), 0) AS total', false)
            ->where('campus_id', $campusId)
            ->where('transaction_type', $type)
            ->where('direction', $direction)
            ->where('is_reversed', 0)
            ->where('transaction_date >=', $from)
            ->where('transaction_date <=', $to)
            ->get()
            ->getRow();

        return (float) ($row->total ?? 0);
    }

    /**
     * @return list<array{exp_head_id:int,head_title:string,total:float}>
     */
    protected function sumExpensesByHead(int $campusId, string $from, string $to): array
    {
        if ($this->tablesReady() && $this->campusHasFinanceAccounts($campusId)) {
            $rows = $this->db->table('finance_transactions ft')
                ->select('e.exp_head_id, eh.head_title, COALESCE(SUM(ft.amount), 0) AS total', false)
                ->join('expenses e', 'e.expense_id = ft.reference_id AND ft.reference_type = \'expense\'', 'inner')
                ->join('expense_heads eh', 'eh.exp_head_id = e.exp_head_id', 'left')
                ->where('ft.campus_id', $campusId)
                ->where('ft.transaction_type', 'expense')
                ->where('ft.direction', 'debit')
                ->where('ft.is_reversed', 0)
                ->where('ft.transaction_date >=', $from)
                ->where('ft.transaction_date <=', $to)
                ->groupBy('e.exp_head_id, eh.head_title')
                ->get()
                ->getResultArray();

            if ($rows !== []) {
                return array_map(static fn ($r) => [
                    'exp_head_id' => (int) ($r['exp_head_id'] ?? 0),
                    'head_title'  => (string) ($r['head_title'] ?? 'Other'),
                    'total'       => (float) ($r['total'] ?? 0),
                ], $rows);
            }
        }

        $dateCol = $this->db->fieldExists('expense_date', 'expenses') ? 'expense_date' : 'created_date';
        $rows = $this->db->table('expenses e')
            ->select('e.exp_head_id, eh.head_title, COALESCE(SUM(e.amount), 0) AS total', false)
            ->join('expense_heads eh', 'eh.exp_head_id = e.exp_head_id', 'left')
            ->where('e.campus_id', $campusId)
            ->where("e.{$dateCol} >=", $from)
            ->where("e.{$dateCol} <=", $to)
            ->groupBy('e.exp_head_id, eh.head_title')
            ->get()
            ->getResultArray();

        return array_map(static fn ($r) => [
            'exp_head_id' => (int) ($r['exp_head_id'] ?? 0),
            'head_title'  => (string) ($r['head_title'] ?? 'Other'),
            'total'       => (float) ($r['total'] ?? 0),
        ], $rows);
    }

    public function accountLabel(object $account): string
    {
        $type = ucfirst((string) ($account->account_type ?? ''));
        return $account->account_name . ($type ? " ({$type})" : '');
    }

    public function getStaffDisplayName(int $userId): string
    {
        if ($userId <= 0) {
            return '';
        }

        $user = $this->db->table('users')
            ->select('first_name, last_name, username')
            ->where('id', $userId)
            ->get()
            ->getRow();

        if (! $user) {
            return 'User #' . $userId;
        }

        $name = trim((string) ($user->first_name ?? '') . ' ' . (string) ($user->last_name ?? ''));

        return $name !== '' ? $name : (string) ($user->username ?? ('User #' . $userId));
    }

    /**
     * @return array{enabled:bool,accounts:list<array>,default_account_id:int,received_by:string}
     */
    public function getAccountsPayload(int $campusId, int $userId): array
    {
        if (! $this->campusHasFinanceAccounts($campusId)) {
            return [
                'enabled'             => false,
                'accounts'            => [],
                'default_account_id'  => 0,
                'received_by'         => $this->getStaffDisplayName($userId),
            ];
        }

        $defaultId = $this->resolveDefaultReceiptAccount($userId, $campusId);
        $list      = [];

        foreach ($this->getAccountsForCampus($campusId) as $acc) {
            $list[] = [
                'account_id'   => (int) $acc->account_id,
                'account_name' => $acc->account_name,
                'account_type' => $acc->account_type,
                'label'        => $this->accountLabel($acc),
            ];
        }

        return [
            'enabled'            => true,
            'accounts'           => $list,
            'default_account_id' => $defaultId,
            'received_by'        => $this->getStaffDisplayName($userId),
        ];
    }
}
