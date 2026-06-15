<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\FeeReminderService;
use App\Libraries\ParentNotificationMailer;

class FeeReminders extends BaseController
{
    public function run()
    {
        check_permission('admin-fee-chalan');

        $campusId = (int) session()->get('member_campusid');
        $days     = (int) $this->request->getPost('days_past_due');

        $service = new FeeReminderService();
        $rows    = $service->getOverdueChallans($campusId, $days);

        $school = getSchoolInfo();
        $db     = db_connect();
        $smsRow = $db->table('sms_settings')
            ->where('system_id', $school->system_id ?? 0)
            ->get()
            ->getRow();

        $template = trim((string) $this->request->getPost('message'));
        if ($template === '') {
            $template = 'Fee reminder: {name} - amount {amount} was due on {due_date}.';
        }

        $smsSent = $smsRow ? $service->sendSmsReminders($rows, $smsRow, $template) : 0;

        $mailer = new ParentNotificationMailer();
        $emails = 0;
        foreach ($rows as $row) {
            $email = trim((string) ($row['email'] ?? ''));
            if ($email !== '' && $mailer->feeDueReminder(
                $email,
                'Parent',
                trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
                (string) ($row['amount'] ?? ''),
                (string) ($row['due_date'] ?? '')
            )) {
                $emails++;
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'overdue' => count($rows),
            'sms_sent' => $smsSent,
            'emails_sent' => $emails,
        ]);
    }
}
