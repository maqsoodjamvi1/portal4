<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SportsTeamMembers extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
        helper(['url', 'form']);
    }

    /**
     * Must be compatible with BaseController::index() (no params).
     * Use as a landing redirect back to Teams.
     */
    public function index()
    {
        return redirect()->to(base_url('admin/sports/teams'));
    }

    /**
     * List members + add form for a specific team
     */
    public function byTeam(int $teamId)
    {
        $team = $this->db->table('sports_teams t')
            ->select('t.*, e.event_name, h.house_name')
            ->join('sports_events e', 'e.event_id=t.event_id', 'left')
            ->join('sports_houses h', 'h.house_id=t.house_id', 'left')
            ->where('t.team_id', $teamId)
            ->get()->getRowArray();

        if (!$team) {
            return redirect()->to(base_url('admin/sports/teams'))
                ->with('error', 'Team not found');
        }

       $sessionId = (int) (session('member_sessionid') ?? 0);

$members = $this->db->table('sports_team_members m')
    ->select("
        m.*,
        s.student_id, s.first_name, s.last_name, s.date_of_birth, s.profile_photo,
        c.class_name, sec.section_name
    ")
    ->join('students s', 's.student_id = m.student_id', 'left')
    // pick the row from student_class for this session (adjust column names if needed)
    ->join('student_class sc', "sc.student_id = s.student_id" . ($sessionId ? " AND sc.session_id = {$sessionId}" : ''), 'left')
    ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left')
    ->join('classes c', 'c.class_id = cs.class_id', 'left')
    ->join('sections sec', 'sec.section_id = cs.section_id', 'left')
    ->where('m.team_id', $teamId)
    ->groupBy('m.stm_id, s.student_id, s.first_name, s.last_name, s.date_of_birth, s.profile_photo, c.class_name, sec.section_name') // avoid dup rows
    ->orderBy('m.is_captain', 'DESC')
    ->orderBy('s.first_name', 'ASC')
    ->get()->getResultArray();

        return view('admin/sports/team_members', [
            'team'    => $team,
            'members' => $members,
        ]);
    }

    public function add()
    {
        $teamId    = (int) $this->request->getPost('team_id');
        $studentId = (int) $this->request->getPost('student_id');
        $isCaptain = (int) ($this->request->getPost('is_captain') ?? 0);
        $userId    = (int) (session('id') ?? 0) ?: null;

        if ($teamId <= 0 || $studentId <= 0) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Missing team or student.']);
        }

        // Prevent duplicate in the same team
        $exists = $this->db->table('sports_team_members')
            ->where(['team_id' => $teamId, 'student_id' => $studentId])
            ->countAllResults();
        if ($exists > 0) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Student already in this team.']);
        }

        $ok = $this->db->table('sports_team_members')->insert([
            'team_id'    => $teamId,
            'student_id' => $studentId,
            'is_captain' => $isCaptain ? 1 : 0,
            'user_id'    => $userId,
        ]);

        return $this->response->setJSON(['ok' => (bool)$ok, 'msg' => $ok ? null : 'Insert failed.']);
    }

    public function delete()
    {
        $stmId = (int) $this->request->getPost('stm_id');
        if ($stmId <= 0) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Invalid id.']);
        }

        $this->db->table('sports_team_members')->delete(['stm_id' => $stmId]);
        return $this->response->setJSON(['ok' => true]);
    }
}
