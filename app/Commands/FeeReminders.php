<?php

namespace App\Commands;

use App\Libraries\FeeReminderService;
use App\Libraries\ParentNotificationMailer;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class FeeReminders extends BaseCommand
{
    protected $group       = 'School';
    protected $name        = 'fees:remind';
    protected $description = 'Send fee due reminders (SMS/email) for overdue challans per campus.';
    protected $usage       = 'fees:remind [campus_id] [days_past_due]';
    protected $arguments   = [
        'campus_id'     => 'Campus ID (required)',
        'days_past_due' => 'Only challans due more than N days ago (default 0)',
    ];

    public function run(array $params): void
    {
        $campusId = (int) ($params[0] ?? 0);
        $days     = (int) ($params[1] ?? 0);

        if ($campusId <= 0) {
            CLI::error('Usage: php spark fees:remind <campus_id> [days_past_due]');

            return;
        }

        $db       = db_connect();
        $service  = new FeeReminderService($db);
        $rows     = $service->getOverdueChallans($campusId, $days);
        $school   = $db->table('campus')->select('system_id')->where('campus_id', $campusId)->get()->getRow();
        $smsRow   = null;
        if ($school) {
            $smsRow = $db->table('sms_settings')->where('system_id', $school->system_id)->get()->getRow();
        }

        $template = 'Fee reminder: {name} - amount {amount} was due on {due_date}. Please pay at school.';
        $smsSent  = $smsRow ? $service->sendSmsReminders($rows, $smsRow, $template) : 0;

        $mailer = new ParentNotificationMailer();
        $emails = 0;
        foreach ($rows as $row) {
            $email = trim((string) ($row['email'] ?? ''));
            if ($email === '') {
                continue;
            }
            if ($mailer->feeDueReminder(
                $email,
                'Parent',
                trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
                (string) ($row['amount'] ?? ''),
                (string) ($row['due_date'] ?? '')
            )) {
                $emails++;
            }
        }

        CLI::write("Overdue challans: " . count($rows) . "; SMS sent: {$smsSent}; emails sent: {$emails}");
    }
}
