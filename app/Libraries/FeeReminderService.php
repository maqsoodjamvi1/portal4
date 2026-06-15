<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Finds overdue fee challans and can dispatch reminder notifications.
 */
class FeeReminderService
{
    protected BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getOverdueChallans(int $campusId, int $daysPastDue = 0): array
    {
        if ($campusId <= 0) {
            return [];
        }

        $cutoff = date('Y-m-d', strtotime('-' . max(0, $daysPastDue) . ' days'));

        return $this->db->table('fee_chalan fc')
            ->select('fc.chalan_id, fc.student_id, fc.amount, fc.due_date, s.first_name, s.last_name, p.father_contact, p.email')
            ->join('students s', 's.student_id = fc.student_id')
            ->join('parents p', 'p.parent_id = s.parent_id', 'left')
            ->where('s.campus_id', $campusId)
            ->whereIn('fc.status', ['unpaid', 'Unpaid'])
            ->where('fc.due_date <', $cutoff)
            ->orderBy('fc.due_date', 'ASC')
            ->limit(500)
            ->get()
            ->getResultArray();
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    public function sendSmsReminders(array $rows, object $smsSettings, string $template): int
    {
        if ($rows === [] || trim($template) === '') {
            return 0;
        }

        $sent   = 0;
        $url    = 'https://lifetimesms.com/plain';
        $client = \Config\Services::curlrequest();

        foreach ($rows as $row) {
            $phone = preg_replace('/\D+/', '', (string) ($row['father_contact'] ?? ''));
            if ($phone === '') {
                continue;
            }
            if (strlen($phone) === 10 && $phone[0] === '3') {
                $phone = '92' . $phone;
            }

            $message = str_replace(
                ['{name}', '{amount}', '{due_date}'],
                [
                    trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
                    (string) ($row['amount'] ?? ''),
                    (string) ($row['due_date'] ?? ''),
                ],
                $template
            );

            try {
                $client->post($url, [
                    'form_params' => [
                        'api_token'  => $smsSettings->api_token ?? '',
                        'api_secret' => $smsSettings->api_secret ?? '',
                        'to'         => $phone,
                        'from'       => $smsSettings->masking_name ?? '',
                        'message'    => $message,
                    ],
                    'verify' => true,
                ]);
                $sent++;
            } catch (\Throwable $e) {
                log_message('error', 'FeeReminderService SMS: ' . $e->getMessage());
            }
        }

        return $sent;
    }
}
