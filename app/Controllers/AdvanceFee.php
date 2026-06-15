<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class AdvanceFee extends BaseController
{
    protected $db;

    public function __construct()
    {
        helper(['form', 'url', 'school']);
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $campus_id  = (int) session('member_campusid');
        $session_id = (int) session('member_sessionid');

        return view('admin/advance_fee/index', [
            'rows' => $this->fetchAdvanceRows($campus_id, $session_id),
        ]);
    }

    /**
     * POST balances: { "student_id": "amount", ... }
     */
    public function save(): ResponseInterface
    {
        if (! $this->request->is('post')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request.']);
        }

        $raw = $this->request->getPost('balances');
        if (is_string($raw)) {
            $balances = json_decode($raw, true);
        } else {
            $balances = is_array($raw) ? $raw : [];
        }

        if ($balances === [] || ! is_array($balances)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No balances submitted.']);
        }

        $campus_id = (int) session('member_campusid');
        $user_id   = (int) session('member_userid');
        $updated   = 0;
        $errors    = [];

        $this->db->transStart();

        foreach ($balances as $studentId => $amount) {
            $studentId = (int) $studentId;
            $amount    = round((float) $amount, 2);

            if ($studentId <= 0) {
                continue;
            }

            $belongs = $this->db->table('students')
                ->where('student_id', $studentId)
                ->where('campus_id', $campus_id)
                ->where('status', 1)
                ->countAllResults();

            if ($belongs < 1) {
                $errors[] = 'Student #' . $studentId . ' not found.';
                continue;
            }

            set_student_advance_balance($this->db, $studentId, $amount, $user_id);
            $updated++;
        }

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Could not save advance balances.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => $updated . ' record(s) updated.',
            'updated' => $updated,
            'errors'  => $errors,
        ]);
    }

    /**
     * @return list<object>
     */
    private function fetchAdvanceRows(int $campus_id, int $session_id): array
    {
        if ($campus_id <= 0) {
            return [];
        }

        $advanceId = advance_fee_type_id();

        $sql = '
            SELECT
                fc.chalan_id,
                fc.student_id,
                fc.amount,
                fc.paid_date,
                s.reg_no,
                TRIM(CONCAT(s.first_name, " ", COALESCE(s.last_name, ""))) AS student_name,
                COALESCE(c.class_name, "") AS class_name,
                COALESCE(sec.section_name, "") AS section_name
            FROM fee_chalan fc
            INNER JOIN (
                SELECT student_id, MAX(chalan_id) AS chalan_id
                FROM fee_chalan
                WHERE fee_type_id = ?
                  AND status = "paid"
                  AND amount > 0
                GROUP BY student_id
            ) latest ON latest.chalan_id = fc.chalan_id
            INNER JOIN students s ON s.student_id = fc.student_id
            LEFT JOIN student_class sc
                ON sc.student_id = s.student_id AND sc.session_id = ?
            LEFT JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
            LEFT JOIN classes c ON c.class_id = cs.class_id
            LEFT JOIN sections sec ON sec.section_id = cs.section_id
            WHERE s.campus_id = ?
              AND s.status = 1
            ORDER BY student_name ASC, s.reg_no ASC
        ';

        $result = $this->db->query($sql, [$advanceId, $session_id, $campus_id]);

        return $result ? $result->getResult() : [];
    }
}
