<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use Config\Database;

class Fees extends BaseController
{
    protected $session;
    protected $db;

    public function __construct()
    {
        $this->session = session();
        $this->db      = Database::connect();
        helper(['url', 'number', 'server', 'parent_portal']);
    }

    public function index()
    {
        $auth = $this->session->get('auth');
        if (! $auth || empty($auth['logged_in'])) {
            return redirect()->route('login');
        }

        $role      = $auth['role'] ?? '';
        $parentId  = (int) ($auth['user_id'] ?? 0);
        $studentId = 0;

        if ($role === 'parent') {
            $studentId = (int) ($this->session->get('active_student_id') ?? 0);
            if ($studentId <= 0) {
                $kids = \parent_portal_get_children($parentId);
                if (! empty($kids)) {
                    $studentId = (int) $kids[0]['student_id'];
                    $this->session->set('active_student_id', $studentId);
                }
            }
            if ($studentId <= 0) {
                return redirect()->route('dashboard')
                    ->with('error', 'Please select a child from the dashboard first.');
            }
        } elseif ($role === 'student') {
            $studentId = (int) ($auth['student_id'] ?? 0);
            if ($studentId <= 0) {
                return redirect()->route('login')
                    ->with('error', 'Student information not found. Please log in again.');
            }
        } else {
            return redirect()->route('login');
        }

        $children = ($role === 'parent') ? \parent_portal_get_children($parentId) : [];
        $familyIds = array_values(array_unique(array_map(static fn ($c) => (int) ($c['student_id'] ?? 0), $children)));
        if ($role === 'student') {
            $familyIds = [$studentId];
        }
        if ($familyIds === [] && $studentId > 0) {
            $familyIds = [$studentId];
        }

        $viewFamily = $role === 'parent'
            && strtolower((string) $this->request->getGet('view')) === 'family';

        $scopeIds = $viewFamily ? $familyIds : [$studentId];
        if (empty($scopeIds)) {
            $scopeIds = [$studentId];
        }

        $rows = $this->fetchFeeRowsForStudents($scopeIds);

        $summary = $this->computeSummary($rows);

        $paymentHistoryByMonth = $this->groupPaymentHistoryByPaidMonth($rows);
        $feeDetailByMonth      = $this->groupFeeDetailByFeeMonth($rows);

        $returnPath = 'student/fees' . ($viewFamily ? '?view=family' : '');

        return view('frontend/fees/index', [
            'title'                   => 'Fees',
            'role'                    => $role,
            'children'                => $children,
            'view_family'             => $viewFamily,
            'return_path'             => $returnPath,
            'show_student_column'     => $viewFamily,
            'summary'                 => $summary,
            'payment_history_months'  => $paymentHistoryByMonth,
            'fee_detail_months'       => $feeDetailByMonth,
            'active_student_id'       => $studentId,
        ]);
    }

    /**
     * @param list<int> $studentIds
     * @return list<array<string, mixed>>
     */
    private function fetchFeeRowsForStudents(array $studentIds): array
    {
        $studentIds = array_values(array_filter(array_map('intval', $studentIds), static fn ($id) => $id > 0));
        if ($studentIds === []) {
            return [];
        }

        $builder = $this->db->table('fee_chalan fc');
        $builder->select('fc.chalan_id, fc.student_id, fc.fee_month, fc.due_date, fc.amount, fc.discount,
            fc.status, fc.paid_date, fc.created_date, fc.updated_date,
            ft.fee_type_name,
            TRIM(CONCAT(COALESCE(s.first_name, ""), " ", COALESCE(s.last_name, ""))) AS student_name', false);
        $builder->join('students s', 's.student_id = fc.student_id', 'inner');
        $builder->join('fee_type ft', 'ft.fee_type_id = fc.fee_type_id', 'left');
        $builder->whereIn('fc.student_id', $studentIds);
        $builder->orderBy('fc.chalan_id', 'DESC');

        $q = $builder->get();
        if ($q === false) {
            log_message('error', 'Fees portal: query failed');

            return [];
        }

        return $q->getResultArray();
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return array{paid: float, unpaid: float, total_net: float}
     */
    private function computeSummary(array $rows): array
    {
        $paid   = 0.0;
        $unpaid = 0.0;

        foreach ($rows as $r) {
            $net = $this->rowNetAmount($r);
            $st  = $this->normalizeStatus($r['status'] ?? '');

            if ($st === 'paid') {
                $paid += $net;
            } elseif (in_array($st, ['unpaid', 'discounted'], true)) {
                $unpaid += $net;
            }
        }

        return [
            'paid'       => $paid,
            'unpaid'     => $unpaid,
            'total_net'  => $paid + $unpaid,
        ];
    }

    /**
     * Paid rows grouped by calendar month of paid_date (newest month first).
     *
     * @param list<array<string, mixed>> $rows
     * @return list<array{month_key: string, month_label: string, items: list<array<string,mixed>>, month_total: float}>
     */
    private function groupPaymentHistoryByPaidMonth(array $rows): array
    {
        $groups = [];

        foreach ($rows as $r) {
            if ($this->normalizeStatus($r['status'] ?? '') !== 'paid') {
                continue;
            }
            $pd = $this->sanitizeDate($r['paid_date'] ?? null);
            if ($pd === null) {
                continue;
            }
            $ts = strtotime($pd);
            if ($ts === false) {
                continue;
            }
            $monthKey   = date('Y-m', $ts);
            $monthLabel = date('F Y', $ts);

            if (! isset($groups[$monthKey])) {
                $groups[$monthKey] = [
                    'month_key'   => $monthKey,
                    'month_label' => $monthLabel,
                    'items'       => [],
                    'month_total' => 0.0,
                ];
            }
            $net = $this->rowNetAmount($r);
            $groups[$monthKey]['items'][] = $r;
            $groups[$monthKey]['month_total'] += $net;
        }

        krsort($groups);

        foreach ($groups as &$g) {
            usort($g['items'], static function (array $a, array $b): int {
                $ta = strtotime((string) ($a['paid_date'] ?? '')) ?: 0;
                $tb = strtotime((string) ($b['paid_date'] ?? '')) ?: 0;

                return $tb <=> $ta;
            });
        }
        unset($g);

        return array_values($groups);
    }

    /**
     * All rows grouped by fee_month (billing month), newest first.
     *
     * @param list<array<string, mixed>> $rows
     * @return list<array{month_key: string, month_label: string, items: list<array<string,mixed>>, month_total: float}>
     */
    private function groupFeeDetailByFeeMonth(array $rows): array
    {
        $groups = [];

        foreach ($rows as $r) {
            $raw = trim((string) ($r['fee_month'] ?? ''));
            if ($raw === '') {
                $raw = '—';
            }
            $monthKey   = $this->feeMonthSortKey($raw);
            $monthLabel = $this->formatFeeMonthLabel($raw);

            if (! isset($groups[$monthKey])) {
                $groups[$monthKey] = [
                    'month_key'   => $monthKey,
                    'month_label' => $monthLabel,
                    'raw_fee_month' => $raw,
                    'items'       => [],
                    'month_total' => 0.0,
                ];
            }
            $net = $this->rowNetAmount($r);
            $groups[$monthKey]['items'][] = $r;
            $groups[$monthKey]['month_total'] += $net;
        }

        krsort($groups);

        foreach ($groups as &$g) {
            usort($g['items'], static function (array $a, array $b): int {
                return ((int) ($b['chalan_id'] ?? 0)) <=> ((int) ($a['chalan_id'] ?? 0));
            });
        }
        unset($g);

        return array_values($groups);
    }

    private function rowNetAmount(array $r): float
    {
        $a = (float) ($r['amount'] ?? 0);
        $d = (float) ($r['discount'] ?? 0);

        return max(0.0, $a - $d);
    }

    private function normalizeStatus(string $status): string
    {
        $s = strtolower(str_replace([' ', '-', '_'], '', $status));

        if ($s === 'paid') {
            return 'paid';
        }
        if ($s === 'discounted') {
            return 'discounted';
        }
        if ($s === 'unpaid') {
            return 'unpaid';
        }

        return 'other';
    }

    private function sanitizeDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $s = trim((string) $value);
        if ($s === '' || strpos($s, '0000-00-00') === 0) {
            return null;
        }

        return $s;
    }

    private function feeMonthSortKey(string $feeMonth): string
    {
        $feeMonth = trim($feeMonth);
        if (preg_match('/^(\d{4})-(\d{2})/', $feeMonth, $m)) {
            return $m[1] . $m[2];
        }
        if (preg_match('#^(\d{1,2})/(\d{4})$#', $feeMonth, $m)) {
            return $m[2] . str_pad($m[1], 2, '0', STR_PAD_LEFT);
        }
        $t = strtotime($feeMonth);

        return $t ? date('Ym', $t) : '000000';
    }

    private function formatFeeMonthLabel(string $feeMonth): string
    {
        $feeMonth = trim($feeMonth);
        if ($feeMonth === '' || $feeMonth === '—') {
            return '—';
        }
        if (preg_match('/^(\d{4})-(\d{2})/', $feeMonth, $m)) {
            $t = strtotime($m[1] . '-' . $m[2] . '-01');

            return $t ? date('F Y', $t) : $feeMonth;
        }
        if (preg_match('#^(\d{1,2})/(\d{4})$#', $feeMonth, $m)) {
            $t = strtotime($m[2] . '-' . str_pad($m[1], 2, '0', STR_PAD_LEFT) . '-01');

            return $t ? date('F Y', $t) : $feeMonth;
        }
        $t = strtotime($feeMonth);

        return $t ? date('F Y', $t) : $feeMonth;
    }
}
