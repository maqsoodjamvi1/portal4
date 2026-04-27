<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use DateTime;

class FeeCollectionSessions extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
        helper(['form', 'url']);
    }

    public function index()
    {
        $session   = session();
        $campusId  = (int) ($session->get('member_campusid') ?? 0);
        $systemId  = 0;
        $campusRow = null;

        // ---- Resolve system_id from campus ----
        if ($campusId > 0) {
            $campusRow = $this->db->table('campus')
                ->select('system_id, campus_name, short_name, location')
                ->where('campus_id', $campusId)
                ->get()
                ->getRowArray();

            if ($campusRow) {
                $systemId = (int) ($campusRow['system_id'] ?? 0);
            }
        }

        // ---- Load all academic sessions of this system ----
        $sessions = [];
        if ($systemId > 0) {
            $sessions = $this->db->table('academic_session')
                ->where('system_id', $systemId)
                ->orderBy('start_date', 'ASC')
                ->get()
                ->getResultArray();
        }

        // ---- Determine latest session for month headings ----
        $latestSession = null;
        $months        = [];  // each: ['label' => 'Apr', 'month_no' => 4]

        if ($systemId > 0) {
            $latestSession = $this->db->table('academic_session')
                ->where('system_id', $systemId)
                ->orderBy('start_date', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();
        }

        if ($latestSession && ! empty($latestSession['start_date'])) {
            $start = new DateTime($latestSession['start_date']);
            $startMonthNo = (int) $start->format('n'); // 1-12

            for ($i = 0; $i < 12; $i++) {
                $monthNo = (($startMonthNo + $i - 1) % 12) + 1; // wrap 1..12
                $label   = date('M', mktime(0, 0, 0, $monthNo, 1, 2000)); // Apr, May...

                $months[] = [
                    'label'    => $label,
                    'month_no' => $monthNo,
                ];
            }
        }

        // ---- All active fee types ----
        $feeTypes = [];
        if ($systemId > 0) {
            $feeTypes = $this->db->table('fee_type')
                ->where('system_id', $systemId)
                ->where('s_flag', 1)
                ->where('status', 1)
                ->orderBy('fee_type_name', 'ASC')
                ->get()
                ->getResultArray();
        }

        // ---- Selected fee types from form ----
        $selectedFeeTypes = $this->request->getPost('fee_types');
        if (! is_array($selectedFeeTypes)) {
            $selectedFeeTypes = [];
        }

        // Default: if none checked, use all active fee types
        if (empty($selectedFeeTypes) && ! empty($feeTypes)) {
            $selectedFeeTypes = array_column($feeTypes, 'fee_type_id');
        }

        $matrix = [];  // [session_id][month_no] => amount

        if (
            $campusId > 0
            && $systemId > 0
            && ! empty($months)
            && ! empty($selectedFeeTypes)
            && ! empty($sessions)
        ) {
            $allowedMonthNos = array_column($months, 'month_no');

            $builder = $this->db->table('fee_chalan fc');

            // fee_month assumed as 'YYYY-MM'. Convert to date and then MONTH()
            $builder->select(
                "s.session_id, " .
                "MONTH(DATE(CONCAT(fc.fee_month,'-01'))) AS month_no, " .
                "SUM(fc.amount - IFNULL(fc.discount, 0)) AS collected",
                false
            );

            // JOIN with students only for campus filter – ignore students.session_id
            $builder->join('students st', 'st.student_id = fc.student_id', 'inner');

            // JOIN with academic_session using date range
            $joinCond = "s.system_id = {$systemId} " .
                        "AND DATE(CONCAT(fc.fee_month,'-01')) BETWEEN s.start_date AND s.end_date";
            $builder->join('academic_session s', $joinCond, 'inner', false);

            $builder->where('st.campus_id', $campusId);
            $builder->whereIn('fc.fee_type_id', $selectedFeeTypes);

            // If you only want paid records, keep this:
            $builder->where('fc.status', 'Paid');

            $builder->groupBy('s.session_id, month_no');

            $rows = $builder->get()->getResultArray();

            foreach ($rows as $r) {
                $sid = (int) $r['session_id'];
                $mNo = (int) $r['month_no'];

                // Only months in our 12-column pattern (from latest session)
                if (! in_array($mNo, $allowedMonthNos, true)) {
                    continue;
                }

                $amt = (float) $r['collected'];

                if (! isset($matrix[$sid])) {
                    $matrix[$sid] = [];
                }

                $matrix[$sid][$mNo] = $amt;
            }
        }

        // Index sessions by id (optional for view)
        $sessionsById = [];
        foreach ($sessions as $s) {
            $sessionsById[(int) $s['session_id']] = $s;
        }

        $data = [
            'campus'           => $campusRow,
            'campusId'         => $campusId,
            'systemId'         => $systemId,
            'sessions'         => $sessions,
            'sessionsById'     => $sessionsById,
            'months'           => $months,
            'feeTypes'         => $feeTypes,
            'selectedFeeTypes' => $selectedFeeTypes,
            'matrix'           => $matrix,
        ];

        return view('admin/fee_collection_sessions', $data);
    }
}
