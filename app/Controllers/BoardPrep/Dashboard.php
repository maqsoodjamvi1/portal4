<?php

namespace App\Controllers\BoardPrep;

use App\Libraries\BoardPrepProfileService;
use App\Libraries\BoardPrepQuizCatalogService;
use App\Libraries\QbBoardPublisherService;

class Dashboard extends BoardPrepBaseController
{
    public function index()
    {
        // Public site (e.g. liveeducationquiz.com): guests get the open quiz
        // catalog with "play as guest" + "sign up to save results". Signed-in
        // prep users get their personalized dashboard below.
        if (! board_prep_auth()) {
            return $this->publicCatalog();
        }

        helper('server');

        $auth      = board_prep_auth();
        $studentId = board_prep_linked_student_id();
        $stats     = $this->loadQuickStats($studentId);
        $catalog   = new BoardPrepQuizCatalogService();
        $grade     = (string) ($auth['grade_level'] ?? '');
        $boardId   = (int) ($auth['board_publisher_id'] ?? 0);

        $profile  = (new BoardPrepProfileService())->loadForUser((int) ($auth['user_id'] ?? 0));
        $board    = $this->loadBoardDetails($boardId, (string) ($auth['board_name'] ?? ''));
        $photoUrl = getStudentPhotoUrl($profile ? ($profile->profile_photo ?? '') : '');

        return view('board_prep/dashboard', [
            'productName'   => $this->boardPrepConfig()->productName,
            'auth'          => $auth,
            'stats'         => $stats,
            'subjectGroups' => $catalog->loadQuizzesGroupedBySubject($studentId, $grade, $boardId),
            'success'       => session()->getFlashdata('success'),
            'boardName'     => $board['name'],
            'boardLogoUrl'  => $board['logo_url'],
            'photoUrl'      => $photoUrl,
            'gradeLabel'    => board_prep_grade_label($grade),
        ]);
    }

    /** Public quiz catalog for guests (no auth). */
    private function publicCatalog()
    {
        $catalog = new BoardPrepQuizCatalogService();

        $host        = strtolower((string) ($this->request->getServer('HTTP_HOST') ?? ''));
        $productName = str_contains($host, 'liveeducationquiz')
            ? 'Live Education Quiz'
            : $this->boardPrepConfig()->productName;

        return view('board_prep/public_catalog', [
            'productName'   => $productName,
            'subjectGroups' => $catalog->loadAllPublishedGroupedBySubject(),
            'signupUrl'     => board_prep_url('signup'),
            'loginUrl'      => board_prep_url('login'),
        ]);
    }

    /**
     * @return array{name:string,logo_url:?string}
     */
    private function loadBoardDetails(int $boardId, string $fallbackName): array
    {
        $name    = $fallbackName !== '' ? $fallbackName : 'Board';
        $logoUrl = null;

        if ($boardId <= 0 || ! $this->db->tableExists('qb_board_publishers')) {
            return ['name' => $name, 'logo_url' => null];
        }

        $select = 'name';
        if ($this->db->fieldExists('logo', 'qb_board_publishers')) {
            $select .= ', logo';
        }

        $row = $this->db->table('qb_board_publishers')
            ->select($select)
            ->where('id', $boardId)
            ->where('system_id', QbBoardPublisherService::GLOBAL_SYSTEM_ID)
            ->get()
            ->getRow();

        if ($row) {
            $name = trim((string) ($row->name ?? $name));
            if ($name === '') {
                $name = $fallbackName !== '' ? $fallbackName : 'Board';
            }
            $logoUrl = QbBoardPublisherService::logoUrl($row->logo ?? null);
        }

        return ['name' => $name, 'logo_url' => $logoUrl];
    }

    /**
     * @return array{attempts:int,avg_percent:float,quizzes_available:int}
     */
    private function loadQuickStats(int $studentId): array
    {
        $stats = [
            'attempts'          => 0,
            'avg_percent'       => 0.0,
            'quizzes_available' => 0,
        ];

        if ($studentId <= 0 || ! $this->db->tableExists('quiz_attempts')) {
            return $stats;
        }

        $auth = board_prep_auth();
        if (! $auth) {
            return $stats;
        }

        $pctSql     = board_prep_attempt_percent_sql('quiz_attempts');
        $attemptRes = $this->db->table('quiz_attempts')
            ->select("COUNT(*) AS cnt, AVG({$pctSql}) AS avg_pct", false)
            ->where('student_id', $studentId)
            ->whereIn('status', ['submitted', 'completed'])
            ->get();
        $attemptRow = $attemptRes ? $attemptRes->getRow() : null;

        $stats['attempts']    = (int) ($attemptRow->cnt ?? 0);
        $stats['avg_percent'] = round((float) ($attemptRow->avg_pct ?? 0), 1);

        if ($this->db->fieldExists('audience', 'quizzes')) {
            $grade = (string) ($auth['grade_level'] ?? '');
            $board = (int) ($auth['board_publisher_id'] ?? 0);

            $builder = $this->db->table('quizzes')
                ->whereIn('audience', ['board_prep', 'both'])
                ->where('is_published', 1)
                ->where('prep_grade_level', $grade)
                ->groupStart()
                    ->where('prep_board_publisher_id IS NULL', null, false)
                    ->orWhere('prep_board_publisher_id', $board)
                ->groupEnd();

            $stats['quizzes_available'] = (int) $builder->countAllResults();
        }

        return $stats;
    }
}
