<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\ResponseInterface;

class Bulk_Events extends BaseController
{
    /** @var BaseConnection */
    protected $db;
    protected string $tbl = 'sports_events';

    public function __construct()
    {
        $this->db = db_connect();
        helper(['form', 'url', 'text']);
    }

    public function index()
    {
        $campusId  = (int) (session('member_campusid') ?? 0);
        $sessionId = $this->resolveSessionId();

        return view('admin/sports/bulk_events_view', [
            'campusId'  => $campusId,
            'sessionId' => $sessionId,
        ]);
    }

    /**
     * POST: admin/sports/bulk-events/load
     * body: event_type, gender, event_date
     * returns HTML rows for the table (existing records only, no delete button).
     */
    public function load(): ResponseInterface
    {
        if (!$this->request->is('post')) {
            return $this->response->setStatusCode(405);
        }

        $campusId  = (int) (session('member_campusid') ?? 0);
          $sessionId = (int) ($this->session->get('member_sessionid') ?? $this->session->get('academic_session_id') ?? 0);

        $type   = trim((string) $this->request->getPost('event_type'));
        $gender = trim((string) $this->request->getPost('gender'));
        $date   = trim((string) $this->request->getPost('event_date'));

        // Basic guard
        if (!$campusId || !$sessionId || !$type || !$gender || !$date) {
            return $this->response->setJSON([
                'ok' => false,
                'msg' => 'Missing filters (campus/session/type/gender/date).'
            ]);
        }

        $rows = $this->db->table($this->tbl)
            ->select('event_id, event_name, event_type, gender, event_date, per_house_count, min_age, max_age, team_size')
            ->where('campus_id', $campusId)
            ->where('session_id', $sessionId)
            ->where('event_type', $type)
            ->where('gender', $gender)
            ->where('event_date', $date)
            ->orderBy('event_id', 'ASC')
            ->get();

            
        if ($rows === false) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'DB error loading rows.']);
        }

        $data = $rows->getResultArray();
        $html = $this->rowsHtml($data, /*existing*/ true, $type);

        return $this->response->setJSON([
            'ok' => true,
            'html' => $html,
        ]);
    }

    /**
     * POST: admin/sports/bulk-events/save
     * Handles update for existing rows and insert for new rows in one transaction.
     *
     * Payload:
     * - Hidden filters: campus_id, session_id, event_type, gender, event_date
     * - Rows posted as arrays:
     *   existing[event_id][event_name|per_house_count|min_age|max_age|team_size?]
     *   new[event_name[]|per_house_count[]|min_age[]|max_age[]|team_size[]]
     */
    public function save(): ResponseInterface
    {
        if (!$this->request->is('post')) {
            return $this->response->setStatusCode(405);
        }

        $campusId  = (int) ($this->request->getPost('campus_id') ?? session('member_campusid') ?? 0);
        $sessionId = (int) ($this->request->getPost('session_id') ?? 0);
        $type      = trim((string) $this->request->getPost('event_type'));
        $gender    = trim((string) $this->request->getPost('gender'));
        $date      = trim((string) $this->request->getPost('event_date'));

        if (!$campusId)  { $campusId  = (int) (session('member_campusid') ?? 0); }
        if (!$sessionId) { $sessionId = $this->resolveSessionId(); }

        // Validation of top filters
        if (!$campusId || !$sessionId || !$type || !$gender || !$date) {
            return $this->response->setJSON([
                'ok' => false,
                'msg' => 'Missing required filters (campus/session/type/gender/date).'
            ]);
        }

        // Gather posted rows
        $existing = $this->request->getPost('existing') ?? [];
        $newNames = $this->request->getPost('new_event_name') ?? [];
        $newPHC   = $this->request->getPost('new_per_house_count') ?? [];
        $newMin   = $this->request->getPost('new_min_age') ?? [];
        $newMax   = $this->request->getPost('new_max_age') ?? [];
        $newTeam  = $this->request->getPost('new_team_size') ?? [];

        // Server-side validate each row quickly
        $errors = [];

        // Existing rows validation
        foreach ($existing as $eventId => $row) {
            $ename = trim((string) ($row['event_name'] ?? ''));
            $phc   = (int) ($row['per_house_count'] ?? 0);
            $minA  = (int) ($row['min_age'] ?? 0);
            $maxA  = (int) ($row['max_age'] ?? 0);
            $ts    = isset($row['team_size']) ? (int) $row['team_size'] : null;

            if ($ename === '' || strlen($ename) < 3) $errors["existing.$eventId.event_name"] = 'Min 3 chars';
            if ($phc <= 0) $errors["existing.$eventId.per_house_count"] = 'Must be > 0';
            if ($minA < 0) $errors["existing.$eventId.min_age"] = 'Must be >= 0';
            if ($maxA < $minA) $errors["existing.$eventId.max_age"] = 'Max >= Min';
            if ($type === 'team' && (int)$ts <= 0) $errors["existing.$eventId.team_size"] = 'Team size required for team events';
        }

        // New rows validation
        $rowCount = max(count($newNames), count($newPHC), count($newMin), count($newMax), count($newTeam));
        for ($i = 0; $i < $rowCount; $i++) {
            $ename = trim((string) ($newNames[$i] ?? ''));
            $phc   = (int) ($newPHC[$i] ?? 0);
            $minA  = (int) ($newMin[$i] ?? 0);
            $maxA  = (int) ($newMax[$i] ?? 0);
            $ts    = isset($newTeam[$i]) ? (int) $newTeam[$i] : null;

            // Skip completely empty lines
            if ($ename === '' && $phc === 0 && $minA === 0 && $maxA === 0 && (is_null($ts) || $ts === 0)) {
                continue;
            }

            if ($ename === '' || strlen($ename) < 3) $errors["new.$i.event_name"] = 'Min 3 chars';
            if ($phc <= 0) $errors["new.$i.per_house_count"] = 'Must be > 0';
            if ($minA < 0) $errors["new.$i.min_age"] = 'Must be >= 0';
            if ($maxA < $minA) $errors["new.$i.max_age"] = 'Max >= Min';
            if ($type === 'team' && (int)$ts <= 0) $errors["new.$i.team_size"] = 'Team size required for team events';
        }

        if (!empty($errors)) {
            return $this->response->setJSON(['ok' => false, 'errors' => $errors]);
        }

        // Transaction: update existing + insert new
        $this->db->transStart();

        // Update existing
        foreach ($existing as $eventId => $row) {
            $payload = [
                'event_name'       => trim((string) $row['event_name']),
                'per_house_count'  => (int) $row['per_house_count'],
                'min_age'          => (int) $row['min_age'],
                'max_age'          => (int) $row['max_age'],
            ];
            if ($type === 'team') {
                $payload['team_size'] = (int) ($row['team_size'] ?? 0);
            }

            $this->db->table($this->tbl)
                ->where('event_id', (int)$eventId)
                ->where('campus_id', $campusId)
                ->where('session_id', $sessionId)
                ->update($payload);
        }

        // Insert new
        for ($i = 0; $i < $rowCount; $i++) {
            $ename = trim((string) ($newNames[$i] ?? ''));
            if ($ename === '') continue; // ignore blank line

            $payload = [
                'campus_id'        => $campusId,
                'session_id'       => $sessionId,
                'event_name'       => $ename,
                'event_type'       => $type,
                'gender'           => $gender,
                'event_date'       => $date,
                'per_house_count'  => (int) ($newPHC[$i] ?? 0),
                'min_age'          => (int) ($newMin[$i] ?? 0),
                'max_age'          => (int) ($newMax[$i] ?? 0),
                'team_size'        => $type === 'team' ? (int) ($newTeam[$i] ?? 0) : null,
            ];

            $this->db->table($this->tbl)->insert($payload);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->response->setJSON([
                'ok' => false,
                'msg' => 'DB transaction failed',
                'dberr' => [
                    'code'    => $this->db->error()['code'] ?? 0,
                    'message' => $this->db->error()['message'] ?? '',
                ],
            ]);
        }

        return $this->response->setJSON(['ok' => true, 'msg' => 'Saved successfully.']);
    }

    /* ----------------------- helpers ----------------------- */

    /**
     * Prefer session('academic_session_id'), else find active session (status=1),
     * else the latest by end_date.
     */
    protected function resolveSessionId(): int
    {
        $sid = (int) (session('academic_session_id') ?? 0);
        if ($sid) return $sid;

        // Try active session
        $q = $this->db->table('academic_session')->select('session_id')
            ->where('status', 1)->orderBy('end_date', 'DESC')->limit(1)->get();
        if ($q && ($row = $q->getRow())) return (int) $row->session_id;

        // Fallback latest
        $q2 = $this->db->table('academic_session')->select('session_id')
            ->orderBy('end_date', 'DESC')->limit(1)->get();
        if ($q2 && ($row2 = $q2->getRow())) return (int) $row2->session_id;

        return 0;
    }

    /**
     * Build HTML <tr> rows. If $existing = true, do NOT render delete button.
     * When type=team, include the team_size column.
     *
     * @param array<int,array<string,mixed>> $rows
     */
    protected function rowsHtml(array $rows, bool $existing, string $type): string
    {
        $isTeam = ($type === 'team');
        ob_start();
        foreach ($rows as $idx => $r) {
            $eventId = (int) ($r['event_id'] ?? 0);
            $ename   = esc((string) ($r['event_name'] ?? ''));
            $phc     = (int) ($r['per_house_count'] ?? 0);
            $minA    = (int) ($r['min_age'] ?? 0);
            $maxA    = (int) ($r['max_age'] ?? 0);
            $ts      = (int) ($r['team_size'] ?? 0);

            if ($existing) {
                // Existing row — inputs named under existing[event_id][field]
                ?>
                <tr class="row-existing" data-event-id="<?= $eventId ?>">
                    <td class="align-middle">
                        <input type="hidden" name="existing[<?= $eventId ?>][event_id]" value="<?= $eventId ?>">
                        <input type="text" class="form-control form-control-sm" name="existing[<?= $eventId ?>][event_name]" value="<?= $ename ?>" required minlength="3">
                    </td>
                    <td><input type="number" class="form-control form-control-sm" name="existing[<?= $eventId ?>][per_house_count]" value="<?= $phc ?>" min="1" required></td>
                    <td><input type="number" class="form-control form-control-sm" name="existing[<?= $eventId ?>][min_age]" value="<?= $minA ?>" min="0" required></td>
                    <td><input type="number" class="form-control form-control-sm" name="existing[<?= $eventId ?>][max_age]" value="<?= $maxA ?>" min="0" required></td>
                    <?php if ($isTeam): ?>
                        <td><input type="number" class="form-control form-control-sm" name="existing[<?= $eventId ?>][team_size]" value="<?= $ts ?>" min="1" required></td>
                    <?php endif; ?>
                    <td class="text-center align-middle">
                        <span class="badge bg-secondary">loaded</span>
                    </td>
                </tr>
                <?php
            } else {
                // New row — inputs named as new_*[]
                ?>
                <tr class="row-new">
                    <td><input type="text" class="form-control form-control-sm" name="new_event_name[]" required minlength="3" placeholder="Event name"></td>
                    <td><input type="number" class="form-control form-control-sm" name="new_per_house_count[]" min="1" required></td>
                    <td><input type="number" class="form-control form-control-sm" name="new_min_age[]" min="0" required></td>
                    <td><input type="number" class="form-control form-control-sm" name="new_max_age[]" min="0" required></td>
                    <?php if ($isTeam): ?>
                        <td><input type="number" class="form-control form-control-sm" name="new_team_size[]" min="1" required></td>
                    <?php endif; ?>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-sm btn-danger btn-del-row">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php
            }
        }
        return (string) ob_get_clean();
    }
}
