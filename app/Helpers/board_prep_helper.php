<?php

/**
 * Board prep portal helpers.
 */

if (! function_exists('board_prep_config')) {
    function board_prep_config(): \Config\BoardPrep
    {
        return config('BoardPrep');
    }
}

if (! function_exists('board_prep_request_host')) {
    function board_prep_request_host(): string
    {
        $host = strtolower((string) (service('request')->getServer('HTTP_HOST') ?? ''));

        return preg_replace('/:\d+$/', '', $host) ?: $host;
    }
}

if (! function_exists('board_prep_path_prefix')) {
    function board_prep_path_prefix(): string
    {
        return trim(board_prep_config()->pathPrefix, '/');
    }
}

if (! function_exists('board_prep_is_on_path_prefix')) {
    function board_prep_is_on_path_prefix(): bool
    {
        if (board_prep_is_prep_subdomain()) {
            return false;
        }

        $prefix = board_prep_path_prefix();
        if ($prefix === '') {
            return false;
        }

        $path = trim(service('uri')->getPath(), '/');

        return $path === $prefix || str_starts_with($path, $prefix . '/');
    }
}

if (! function_exists('board_prep_is_active_host')) {
    function board_prep_is_active_host(): bool
    {
        if (board_prep_is_prep_subdomain()) {
            return true;
        }

        if (board_prep_is_on_path_prefix()) {
            return true;
        }

        $cfg = board_prep_config();
        if ($cfg->enablePathPrefix) {
            $path = trim(service('uri')->getPath(), '/');
            $prefix = board_prep_path_prefix();
            if ($prefix !== '' && ($path === $prefix || str_starts_with($path, $prefix . '/'))) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('board_prep_url')) {
    function board_prep_url(string $path = ''): string
    {
        $path = ltrim($path, '/');
        $cfg  = board_prep_config();

        if (board_prep_is_public_quiz_host()) {
            $request = service('request');
            $scheme  = $request->isSecure() ? 'https' : 'http';
            $host    = board_prep_request_host();

            return rtrim($scheme . '://' . $host, '/') . ($path === '' ? '/' : '/' . $path);
        }

        if (! board_prep_is_prep_subdomain()) {
            $prefix = board_prep_path_prefix();
            if ($prefix !== '') {
                $path = $path === '' ? $prefix : $prefix . '/' . $path;
            }
        }

        return base_url($path);
    }
}

if (! function_exists('board_prep_is_prep_subdomain')) {
    function board_prep_is_prep_subdomain(): bool
    {
        if (board_prep_is_public_quiz_host()) {
            return true;
        }

        $cfg  = board_prep_config();
        $host = board_prep_request_host();

        foreach (array_filter(array_map('trim', explode(',', $cfg->hosts))) as $allowed) {
            $allowed = strtolower($allowed);
            if ($host === $allowed || str_ends_with($host, '.' . $allowed)) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('board_prep_is_public_quiz_host')) {
    function board_prep_is_public_quiz_host(): bool
    {
        $host = board_prep_request_host();

        return $host === 'liveeducationquiz.com'
            || $host === 'www.liveeducationquiz.com'
            || str_ends_with($host, '.liveeducationquiz.com');
    }
}

if (! function_exists('board_prep_product_name')) {
    function board_prep_product_name(): string
    {
        return board_prep_is_public_quiz_host()
            ? 'Live Education Quiz'
            : board_prep_config()->productName;
    }
}

if (! function_exists('board_prep_grade_label')) {
    function board_prep_grade_label(string $gradeLevel): string
    {
        $labels = board_prep_config()->gradeLabels;

        return $labels[$gradeLevel] ?? strtoupper($gradeLevel);
    }
}

if (! function_exists('board_prep_auth')) {
    /**
     * @return array<string, mixed>|null
     */
    function board_prep_auth(): ?array
    {
        $auth = session()->get('board_prep_auth');

        return is_array($auth) && ! empty($auth['logged_in']) ? $auth : null;
    }
}

if (! function_exists('board_prep_linked_student_id')) {
    function board_prep_linked_student_id(): int
    {
        $auth = board_prep_auth();

        return (int) ($auth['linked_student_id'] ?? session()->get('student_id') ?? 0);
    }
}

if (! function_exists('board_prep_quiz_total_marks_sql')) {
    /**
     * SQL subquery: sum of question marks for a quiz (quiz_attempts has score_obtained only).
     */
    function board_prep_quiz_total_marks_sql(string $quizIdExpr): string
    {
        return "(SELECT COALESCE(SUM(qq.marks), 0) FROM quiz_questions qq WHERE qq.quiz_id = {$quizIdExpr})";
    }
}

if (! function_exists('board_prep_attempt_percent_sql')) {
    /**
     * SQL expression for attempt score as a percentage (alias e.g. qa).
     */
    function board_prep_attempt_percent_sql(string $attemptAlias = 'qa'): string
    {
        $quizId = "{$attemptAlias}.quiz_id";
        $total  = board_prep_quiz_total_marks_sql($quizId);

        return "CASE WHEN {$total} > 0 THEN (({$attemptAlias}.score_obtained / {$total}) * 100) ELSE NULL END";
    }
}

if (! function_exists('board_prep_quiz_total_marks')) {
    function board_prep_quiz_total_marks(int $quizId): float
    {
        if ($quizId <= 0) {
            return 0.0;
        }

        $db  = \Config\Database::connect();
        $row = $db->table('quiz_questions')
            ->selectSum('marks', 'total')
            ->where('quiz_id', $quizId)
            ->get()
            ->getRow();

        $total = (float) ($row->total ?? 0);
        if ($total > 0) {
            return $total;
        }

        $quiz = $db->table('quizzes')
            ->select('questions_count')
            ->where('quiz_id', $quizId)
            ->get()
            ->getRow();

        return (float) ($quiz->questions_count ?? 0);
    }
}

if (! function_exists('board_prep_attempt_percent')) {
    /**
     * @param object{score_obtained?:mixed,quiz_id?:mixed,questions_count?:mixed} $attempt
     */
    function board_prep_attempt_percent(object $attempt): ?float
    {
        $score = $attempt->score_obtained ?? null;
        if ($score === null || $score === '') {
            return null;
        }

        $quizId     = (int) ($attempt->quiz_id ?? 0);
        $totalMarks = board_prep_quiz_total_marks($quizId);
        if ($totalMarks <= 0) {
            $totalMarks = (float) ($attempt->questions_count ?? 0);
        }
        if ($totalMarks <= 0) {
            return null;
        }

        return round(((float) $score / $totalMarks) * 100, 1);
    }
}
