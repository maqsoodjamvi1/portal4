<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class QuizAssign extends BaseController
{
public function index()
{
    $db = \Config\Database::connect();

    // Get class-section list with names
    $classSections = $db->query("
        SELECT cs.cls_sec_id, c.class_name, s.section_name
        FROM class_section cs
        JOIN classes c ON cs.class_id = c.class_id
        JOIN sections s ON cs.section_id = s.section_id
        WHERE cs.campus_id = 1 AND cs.status = 1
        ORDER BY c.class_name, s.section_name
    ")->getResultArray();

    return view('admin/quizzes/quiz_selector', [
        'classSections' => $classSections
    ]);
}

/**
 * Load students by cls_sec_id
 */


public function load_students_for_quiz($cls_sec_id, $quiz_id)
{
    $db         = \Config\Database::connect();
    $cls_sec_id = (int) $cls_sec_id;
    $quiz_id    = (int) $quiz_id;

    // ==========================================
    // 0) Get quiz meta: subject_name + topic_name + start/end
    // ==========================================
    $quizMeta = $db->table('quizzes q')
        ->select('
            q.quiz_id,
            q.sec_sub_id,
            q.start_at,
            q.end_at,
            a.subject_name,
            t.topic_name
        ')
        ->join('section_subjects ss', 'ss.sec_sub_id = q.sec_sub_id', 'inner')
        ->join('allsubject a', 'a.sid = ss.subject_id', 'left')
        ->join('qb_topics t', 't.id = q.topic_id', 'left')
        ->where('q.quiz_id', $quiz_id)
        ->get()
        ->getRowArray();

    // If quiz not found → nothing to show
    if (!$quizMeta) {
        return $this->response->setJSON([
            'available'      => false,
            'reason'         => 'quiz_not_found',
            'attempted'      => [],
            'not_attempted'  => [],
        ]);
    }

    $subjectName = $quizMeta['subject_name'] ?? '';
    $topicName   = $quizMeta['topic_name']   ?? '';

    // ==========================================
    // ✅ Availability rule
    // - If start_at = end_at        → always available
    // - Else only if end_at not finished
    // ==========================================
    $now     = date('Y-m-d H:i:s');
    $startAt = $quizMeta['start_at'] ?? null;
    $endAt   = $quizMeta['end_at']   ?? null;

    // Normalise empty endAt
    $endAtNullish = (!$endAt || $endAt === '0000-00-00 00:00:00');

    $alwaysAvailable = ($startAt && $endAt && $startAt === $endAt);

    $endNotFinished  = $endAtNullish || ($endAt >= $now);

    $isAvailable = $alwaysAvailable || $endNotFinished;

    if (!$isAvailable) {
        // Quiz is closed / expired → don’t show any students
        return $this->response->setJSON([
            'available'      => false,
            'reason'         => 'quiz_expired',
            'attempted'      => [],
            'not_attempted'  => [],
        ]);
    }

    // ==========================================
    // 1) Attempted students
    // ==========================================
    $attempted = $db->query("
        SELECT 
            s.student_id,
            CONCAT(s.first_name, ' ', s.last_name) AS full_name,
            s.gender,
            s.date_of_birth,
            s.profile_photo,

            c.class_name,
            se.section_name,

            p.f_name AS father_name,
            p.father_cnic,
            p.whatsapp,

            qa.score_obtained,
            qa.attempt_id 

        FROM students s
        JOIN student_class sc ON sc.student_id = s.student_id
        JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
        JOIN classes c ON c.class_id = cs.class_id
        JOIN sections se ON se.section_id = cs.section_id
        LEFT JOIN parents p ON p.parent_id = s.parent_id
        JOIN quiz_attempts qa ON qa.student_id = s.student_id AND qa.quiz_id = ?

        WHERE sc.cls_sec_id = ?
          AND sc.status = 1
    ", [$quiz_id, $cls_sec_id])->getResultArray();

    // ==========================================
    // 2) NOT attempted students
    // ==========================================
    $not_attempted = $db->query("
        SELECT 
            s.student_id,
            CONCAT(s.first_name, ' ', s.last_name) AS full_name,
            s.gender,
            s.date_of_birth,
            s.profile_photo,

            c.class_name,
            se.section_name,

            p.f_name AS father_name,
            p.father_cnic,
            p.whatsapp

        FROM students s
        JOIN student_class sc ON sc.student_id = s.student_id
        JOIN class_section cs ON cs.cls_sec_id = sc.cls_sec_id
        JOIN classes c ON c.class_id = cs.class_id
        JOIN sections se ON se.section_id = cs.section_id
        LEFT JOIN parents p ON p.parent_id = s.parent_id

        WHERE sc.cls_sec_id = ?
          AND sc.status = 1
          AND s.student_id NOT IN (
             SELECT student_id FROM quiz_attempts WHERE quiz_id = ?
          )
    ", [$cls_sec_id, $quiz_id])->getResultArray();

    // ==========================================
    // 3) Age calculation helper
    // ==========================================
    $ageCalc = function ($dob) {
        if (!$dob) {
            return ['years' => 0, 'months' => 0];
        }
        try {
            $d1   = new \DateTime($dob);
            $d2   = new \DateTime();
            $diff = $d1->diff($d2);
            return ['years' => $diff->y, 'months' => $diff->m];
        } catch (\Exception $e) {
            return ['years' => 0, 'months' => 0];
        }
    };

    // ==========================================
    // 4) Enrich rows: age + subject_name + topic_name
    // ==========================================
    foreach ($attempted as &$a) {
        $x = $ageCalc($a['date_of_birth'] ?? null);
        $a['age_years']    = $x['years'];
        $a['age_months']   = $x['months'];
        $a['subject_name'] = $subjectName;
        $a['topic_name']   = $topicName;
    }
    unset($a);

    foreach ($not_attempted as &$na) {
        $x = $ageCalc($na['date_of_birth'] ?? null);
        $na['age_years']    = $x['years'];
        $na['age_months']   = $x['months'];
        $na['subject_name'] = $subjectName;
        $na['topic_name']   = $topicName;
    }
    unset($na);

    // ==========================================
    // 5) Return JSON
    // ==========================================
    return $this->response->setJSON([
        'available'      => true,
        'attempted'      => $attempted,
        'not_attempted'  => $not_attempted,
    ]);
}

    /**
     * Load subjects by cls_sec_id (NOT student_id)
     */
    public function load_subjects($cls_sec_id)
    {
        $db        = \Config\Database::connect();
        $cls_sec_id = (int) $cls_sec_id;

        if ($cls_sec_id <= 0) {
            // Return empty array if invalid
            return $this->response->setJSON([]);
        }

        $subjects = $db->query("
            SELECT 
                sc.sec_sub_id,
                sc.subject_id,
                s.subject_name
            FROM section_subjects sc
            JOIN allsubject s ON sc.subject_id = s.sid
            WHERE sc.cls_sec_id = ?
              AND sc.status = 1
        ", [$cls_sec_id])->getResultArray();

        // JS expects a plain array, so we return it directly
        return $this->response->setJSON($subjects);
    }

    /**
     * Load quizzes by sec_sub_id
     */
    public function load_quizzes($sec_sub_id)
    {
        $db = \Config\Database::connect();
        $sec_sub_id = (int) $sec_sub_id;

        if ($sec_sub_id <= 0) {
            return $this->response->setJSON([]);
        }

        $quizzes = $db->query("
            SELECT quiz_id, title
            FROM quizzes
            WHERE sec_sub_id = ? 
            ORDER BY quiz_id DESC
        ", [$sec_sub_id])->getResultArray();

        return $this->response->setJSON($quizzes);
    }

public function generateImpersonationLink()
{
    $session = session();

    // Only allow AJAX
    if (! $this->request->isAJAX()) {
        return $this->response->setStatusCode(405)
            ->setJSON([
                'success' => false,
                'message' => 'AJAX request required.',
            ]);
    }

   

    $userId = $this->session->get('member_userid');
    if ($userId <= 0) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Access denied (not logged in).',
        ]);
    }

    // Get POST data
    $quiz_id    = (int) $this->request->getPost('quiz_id');
    $student_id = (int) $this->request->getPost('student_id');

    if ($quiz_id <= 0 || $student_id <= 0) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Invalid quiz or student.',
        ]);
    }

    // Create token
    $token     = bin2hex(random_bytes(24)); // 48 hex chars
    $expiresAt = date('Y-m-d H:i:s', strtotime('+3 hours')); // adjust lifetime

    $data = [
        'token'      => $token,
        'quiz_id'    => $quiz_id,
        'student_id' => $student_id,
        'created_by' => $userId,
        'created_at' => date('Y-m-d H:i:s'),
        'expires_at' => $expiresAt,
        'used'       => 0,
    ];

    $db = \Config\Database::connect();
    $db->table('quiz_impersonation_tokens')->insert($data);

    // Must match your frontend route: quiz/start/(:num)
    $link = site_url("quiz/start/{$quiz_id}?impersonate_token={$token}");

    return $this->response->setJSON([
        'success' => true,
        'link'    => $link,
        'message' => 'Quiz link generated.',
    ]);
}

public function load_quizzes_by_clssec($clsSecId = 0)
{
    $db       = \Config\Database::connect();
    $clsSecId = (int) $clsSecId;

    if ($clsSecId <= 0) {
        return $this->response->setJSON([]);
    }

    // Use current academic session from staff session
    $sessionId = (int) (session('member_sessionid') ?? 0);

    $sql = "
        SELECT
            q.quiz_id,
            q.title,

            cs.cls_sec_id,
            c.class_name,
            se.section_name,

            a.subject_name,

            -- topics from quiz_topics
            GROUP_CONCAT(DISTINCT qt.topic_name ORDER BY qt.topic_name SEPARATOR ', ') AS topics,

            q.start_at,
            q.end_at,
            q.time_limit_sec,         -- seconds
            q.questions_count,
            q.count_mcq_single,
            q.count_mcq_multi,
            q.count_tf,
            q.count_short,
            q.count_fill,
            q.count_match,

            -- total students in this class-section + session
            (
              SELECT COUNT(*)
              FROM student_class sc
              WHERE sc.cls_sec_id = cs.cls_sec_id
                AND sc.session_id = :sessionId:
                AND sc.status = 1
            ) AS total_students,

            -- attempts of this quiz by students of this class-section + session
            (
              SELECT COUNT(*)
              FROM quiz_attempts qa
              WHERE qa.quiz_id = q.quiz_id
                AND qa.student_id IN (
                  SELECT sc2.student_id
                  FROM student_class sc2
                  WHERE sc2.cls_sec_id = cs.cls_sec_id
                    AND sc2.session_id = :sessionId:
                    AND sc2.status = 1
                )
            ) AS attempted_students

        FROM quizzes q
        JOIN section_subjects ss ON ss.sec_sub_id = q.sec_sub_id
        JOIN class_section cs    ON cs.cls_sec_id = ss.cls_sec_id
        JOIN classes c           ON c.class_id    = cs.class_id
        JOIN sections se         ON se.section_id = cs.section_id
        JOIN allsubject a        ON a.sid         = ss.subject_id
        LEFT JOIN quiz_topics qtp ON qtp.quiz_id  = q.quiz_id
        LEFT JOIN qb_topics qt    ON qt.id        = qtp.topic_id

        WHERE cs.cls_sec_id = :clsSecId:

        GROUP BY
            q.quiz_id,
            q.title,
            cs.cls_sec_id,
            c.class_name,
            se.section_name,
            a.subject_name,
            q.start_at,
            q.end_at,
            q.time_limit_sec,
            q.questions_count,
            q.count_mcq_single,
            q.count_mcq_multi,
            q.count_tf,
            q.count_short,
            q.count_fill,
            q.count_match

        ORDER BY q.quiz_id DESC
    ";

    $rows = $db->query($sql, [
        'clsSecId'  => $clsSecId,
        'sessionId' => $sessionId,
    ])->getResultArray();

    $availableRows = [];
    $now = new \DateTime();

    foreach ($rows as &$row) {

        // ---------- AVAILABILITY LOGIC ----------
        $startAtRaw = $row['start_at'] ?? null;
        $endAtRaw   = $row['end_at']   ?? null;

        $endNullish = (!$endAtRaw || $endAtRaw === '0000-00-00 00:00:00');

        // If start_at and end_at are exactly same → always available
        $alwaysAvailable = ($startAtRaw !== null && $startAtRaw !== '' && $startAtRaw === $endAtRaw);

        $endNotFinished = false;
        if ($endNullish) {
            $endNotFinished = true;
        } else {
            try {
                $endDT = new \DateTime($endAtRaw);
                $endNotFinished = ($endDT >= $now);
            } catch (\Exception $e) {
                $endNotFinished = false;
            }
        }

        $isAvailable = $alwaysAvailable || $endNotFinished;

        // ❌ Skip expired quizzes
        if (! $isAvailable) {
            continue;
        }

        // ---------- Existing post-processing ----------

        // total / attempted / remaining
        $total = (int) ($row['total_students'] ?? 0);
        $att   = (int) ($row['attempted_students'] ?? 0);
        $row['remaining_students'] = max(0, $total - $att);

        // duration (seconds -> minutes)
        $secs = (int) ($row['time_limit_sec'] ?? 0);
        $row['duration_minutes'] = $secs > 0 ? (int) ceil($secs / 60) : 0;

        // remaining_time + status
        $remainingStr = '-';
        $status       = 'live';

        if ($alwaysAvailable) {
            // Forever quiz: treat as live, no countdown
            $remainingStr = '-';
            $status       = 'live';
        } elseif (! $endNullish) {
            try {
                $end = new \DateTime($endAtRaw);
                $diffSec = $end->getTimestamp() - $now->getTimestamp();

                if ($diffSec <= 0) {
                    $remainingStr = 'Ended';
                    $status       = 'closed';
                } else {
                    $status = 'live';
                    $days  = intdiv($diffSec, 86400);
                    $rem   = $diffSec % 86400;
                    $hours = intdiv($rem, 3600);
                    $rem   = $rem % 3600;
                    $mins  = intdiv($rem, 60);

                    $parts = [];
                    if ($days > 0)  $parts[] = $days . 'd';
                    if ($hours > 0) $parts[] = $hours . 'h';
                    if ($mins > 0 || empty($parts)) $parts[] = $mins . 'm';

                    $remainingStr = implode(' ', $parts);
                }
            } catch (\Exception $e) {
                $remainingStr = '-';
            }
        }

        $row['remaining_time'] = $remainingStr;
        $row['quiz_status']    = $status;

        $availableRows[] = $row;
    }
    unset($row);

    return $this->response->setJSON($availableRows);
}


}
