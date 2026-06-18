<?php



namespace App\Libraries;



use App\Libraries\ExamQuizService;



/**

 * Published board-prep quizzes for a student's grade/board (no schedule or attempt limits).

 */

class BoardPrepQuizCatalogService

{

    protected $db;



    public function __construct()

    {

        $this->db = \Config\Database::connect();

    }



    /**

     * @return list<object>

     */

    public function loadQuizzes(int $studentId, string $gradeLevel, int $boardPublisherId): array

    {

        if ($gradeLevel === '' || ! $this->db->fieldExists('audience', 'quizzes')) {

            return [];

        }



        $extraCols = '';

        if ($this->db->fieldExists('is_adaptive', 'quizzes')) {

            $extraCols .= ', q.is_adaptive';

        }



        $examSvc       = new ExamQuizService();

        $visibilitySql = $examSvc->portalVisibilitySql('q');

        $pctSql        = board_prep_attempt_percent_sql('qa');



        $sql = "

            SELECT

                q.quiz_id,

                q.title,

                q.questions_count,

                q.time_limit_sec,

                q.prep_grade_level,

                q.prep_board_publisher_id

                {$extraCols},

                s.subject_name,

                s.subject_short_name,

                bp.name AS board_name,

                COUNT(qa.attempt_id) AS attempts_used,

                MAX({$pctSql}) AS best_percent

            FROM quizzes q

            LEFT JOIN quiz_attempts qa

                   ON qa.quiz_id = q.quiz_id

                  AND qa.student_id = ?

                  AND qa.status IN ('submitted','completed')

            LEFT JOIN section_subjects ss ON ss.sec_sub_id = q.sec_sub_id

            LEFT JOIN allsubject s ON s.sid = ss.subject_id

            LEFT JOIN qb_board_publishers bp ON bp.id = q.prep_board_publisher_id

            WHERE q.is_published = 1

              AND q.audience IN ('board_prep','both')

              AND q.prep_grade_level = ?

              AND (q.prep_board_publisher_id IS NULL OR q.prep_board_publisher_id = ?)

              {$visibilitySql}

            GROUP BY q.quiz_id

            ORDER BY s.subject_name ASC, q.title ASC

        ";



        $rows = $this->db->query($sql, [$studentId, $gradeLevel, $boardPublisherId])->getResult();



        foreach ($rows as $row) {

            $row->can_start = true;

        }



        return $rows;

    }



    /**
     * All published board-prep quizzes (any grade/board) for the public site.
     *
     * @return list<object>
     */
    public function loadAllPublished(): array
    {
        if (! $this->db->fieldExists('audience', 'quizzes')) {
            return [];
        }

        $extraCols = '';
        if ($this->db->fieldExists('is_adaptive', 'quizzes')) {
            $extraCols .= ', q.is_adaptive';
        }

        $examSvc       = new ExamQuizService();
        $visibilitySql = $examSvc->portalVisibilitySql('q');

        $sql = "
            SELECT
                q.quiz_id,
                q.title,
                q.questions_count,
                q.time_limit_sec,
                q.prep_grade_level,
                q.prep_board_publisher_id
                {$extraCols},
                s.subject_name,
                s.subject_short_name,
                bp.name AS board_name
            FROM quizzes q
            LEFT JOIN section_subjects ss ON ss.sec_sub_id = q.sec_sub_id
            LEFT JOIN allsubject s ON s.sid = ss.subject_id
            LEFT JOIN qb_board_publishers bp ON bp.id = q.prep_board_publisher_id
            WHERE q.is_published = 1
              AND q.audience IN ('board_prep','both')
              {$visibilitySql}
            GROUP BY q.quiz_id
            ORDER BY s.subject_name ASC, q.title ASC
        ";

        $rows = $this->db->query($sql)->getResult();

        foreach ($rows as $row) {
            $row->can_start = true;
        }

        return $rows;
    }

    /**
     * @return list<array{subject_name:string,subject_key:string,quiz_count:int,quizzes:list<object>}>
     */
    public function loadAllPublishedGroupedBySubject(): array
    {
        return $this->groupBySubject($this->loadAllPublished());
    }

    /**
     * @param list<object> $quizzes
     * @return list<array{subject_name:string,subject_key:string,quiz_count:int,quizzes:list<object>}>
     */
    private function groupBySubject(array $quizzes): array
    {
        $groups = [];
        foreach ($quizzes as $quiz) {
            $name = trim((string) ($quiz->subject_name ?? ''));
            if ($name === '') {
                $name = 'General';
            }
            $key = 's' . md5(strtolower($name));
            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'subject_name' => $name,
                    'subject_key'  => $key,
                    'quiz_count'   => 0,
                    'quizzes'      => [],
                ];
            }
            $groups[$key]['quizzes'][] = $quiz;
            $groups[$key]['quiz_count']++;
        }

        $list = array_values($groups);
        usort($list, static fn ($a, $b) => strcmp($a['subject_name'], $b['subject_name']));

        return $list;
    }

    /**

     * @return list<array{subject_name:string,subject_key:string,quiz_count:int,quizzes:list<object>}>

     */

    public function loadQuizzesGroupedBySubject(int $studentId, string $gradeLevel, int $boardPublisherId): array

    {

        $groups = [];

        foreach ($this->loadQuizzes($studentId, $gradeLevel, $boardPublisherId) as $quiz) {

            $name = trim((string) ($quiz->subject_name ?? ''));

            if ($name === '') {

                $name = 'General';

            }

            $key = 's' . md5(strtolower($name));

            if (! isset($groups[$key])) {

                $groups[$key] = [

                    'subject_name' => $name,

                    'subject_key'  => $key,

                    'quiz_count'   => 0,

                    'quizzes'      => [],

                ];

            }

            $groups[$key]['quizzes'][] = $quiz;

            $groups[$key]['quiz_count']++;

        }



        $list = array_values($groups);

        usort($list, static fn ($a, $b) => strcmp($a['subject_name'], $b['subject_name']));



        return $list;

    }

}
