<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

class QuestionPaperService
{
    public const MAX_QUESTIONS = 120;

    protected BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /**
     * @param list<int> $boardPublisherIds
     * @return array{ok: bool, data?: list<array<string, mixed>>, msg?: string}
     */
    public function fetchSummaryRows(?int $classId = null, ?int $subjectId = null, array $boardPublisherIds = []): array
    {
        try {
            $rows = $this->runSummaryQuery($classId, $subjectId, true, $boardPublisherIds);
            if ($rows === []) {
                $rows = $this->runSummaryQuery($classId, $subjectId, false, $boardPublisherIds);
            }

            $rows = $this->attachBoardLabelsToSummaryRows($rows);

            return ['ok' => true, 'data' => $rows];
        } catch (\Throwable $e) {
            log_message('error', 'QuestionPaperService::fetchSummaryRows - ' . $e->getMessage());

            return ['ok' => false, 'msg' => $e->getMessage()];
        }
    }

    /**
     * Same shape as Quizzes::ajaxQbSummary (flat rows per class/subject/topic).
     *
     * @return list<array<string, mixed>>
     */
    /**
     * @param list<int> $boardPublisherIds
     * @return list<array<string, mixed>>
     */
    private function runSummaryQuery(?int $classId, ?int $subjectId, bool $strictStatus, array $boardPublisherIds = []): array
    {
        $systemId = $this->resolveSystemId();
        $boardPublisherIds = $this->intList($boardPublisherIds);

        $builder = $this->db->table('qb_questions q');
        $builder->select([
            'q.class_id',
            'q.subject_id',
            'q.topic_id',
            'c.class_name',
            's.subject_name',
            't.topic_name',
            'COUNT(q.id) AS question_count',
        ]);

        $classJoin = 'c.class_id = q.class_id AND c.system_id = ' . $systemId;
        if ($strictStatus) {
            $classJoin .= ' AND c.status = 1';
        }
        $builder->join('classes c', $classJoin, 'inner');

        $subJoin = 's.sid = q.subject_id AND s.system_id = ' . $systemId;
        if ($strictStatus) {
            $subJoin .= ' AND s.status = 1';
        }
        $builder->join('allsubject s', $subJoin, 'inner');
        $builder->join('qb_topics t', 't.id = q.topic_id', 'inner');

        if ($classId > 0) {
            $builder->where('q.class_id', $classId);
        }
        if ($subjectId > 0) {
            $builder->where('q.subject_id', $subjectId);
        }

        $boardService = new QbBoardPublisherService($this->db);
        $boardSql     = $boardService->topicMatchesBoardFilterSql('t.id', $boardPublisherIds);
        if ($boardSql !== null) {
            $builder->where($boardSql, null, false);
        }

        $builder->groupBy([
            'q.class_id',
            'q.subject_id',
            'q.topic_id',
            'c.class_name',
            's.subject_name',
            't.topic_name',
        ]);
        $builder->orderBy('c.class_name', 'ASC');
        $builder->orderBy('s.subject_name', 'ASC');
        $builder->orderBy('t.topic_name', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function attachBoardLabelsToSummaryRows(array $rows): array
    {
        if ($rows === []) {
            return $rows;
        }

        $topicIds = [];
        foreach ($rows as $row) {
            $tid = (int) ($row['topic_id'] ?? 0);
            if ($tid > 0) {
                $topicIds[] = $tid;
            }
        }

        $boardService = new QbBoardPublisherService($this->db);
        $labelMap     = $boardService->getLabelsMapForTopics($topicIds);

        foreach ($rows as &$row) {
            $tid = (int) ($row['topic_id'] ?? 0);
            $row['board_publishers'] = $labelMap[$tid] ?? [];
        }
        unset($row);

        return $rows;
    }

    private function resolveSystemId(): int
    {
        $school = getSchoolInfo();
        if (is_object($school)) {
            $id = (int) ($school->system_id ?? 0);
            if ($id > 0) {
                return $id;
            }
        }

        // Match quiz create QB browser (production data often keyed to system 1).
        return 1;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    public function buildSummaryTree(array $rows): array
    {
        $tree = [];
        foreach ($rows as $r) {
            $cid = (int) ($r['class_id'] ?? 0);
            $sid = (int) ($r['subject_id'] ?? 0);
            if ($cid <= 0 || $sid <= 0) {
                continue;
            }
            if (!isset($tree[$cid])) {
                $tree[$cid] = [
                    'class_id'   => $cid,
                    'class_name' => (string) ($r['class_name'] ?? ''),
                    'subjects'   => [],
                ];
            }
            if (!isset($tree[$cid]['subjects'][$sid])) {
                $tree[$cid]['subjects'][$sid] = [
                    'subject_id'   => $sid,
                    'subject_name' => (string) ($r['subject_name'] ?? ''),
                    'topics'       => [],
                ];
            }
            $tree[$cid]['subjects'][$sid]['topics'][] = [
                'class_id'          => $cid,
                'subject_id'        => $sid,
                'topic_id'          => (int) ($r['topic_id'] ?? 0),
                'topic_name'        => (string) ($r['topic_name'] ?? ''),
                'question_count'    => (int) ($r['question_count'] ?? 0),
                'board_publishers'  => $r['board_publishers'] ?? [],
            ];
        }

        $out = [];
        foreach ($tree as $cls) {
            $cls['subjects'] = array_values($cls['subjects']);
            $out[] = $cls;
        }

        return $out;
    }

    /**
     * @param array{
     *   class_ids?: list<int>,
     *   subject_ids?: list<int>,
     *   topic_ids?: list<int>,
     *   board_publisher_ids?: list<int>,
     *   question_types?: list<string>,
     *   difficulties?: list<string>,
     * } $filters
     * @return list<array<string, mixed>>
     */
    public function fetchPool(array $filters): array
    {
        $classIds   = $this->intList($filters['class_ids'] ?? []);
        $subjectIds = $this->intList($filters['subject_ids'] ?? []);
        $topicIds   = $this->intList($filters['topic_ids'] ?? []);
        $boardIds   = $this->intList($filters['board_publisher_ids'] ?? []);
        $types      = $this->stringList($filters['question_types'] ?? []);
        $diffs      = $this->stringList($filters['difficulties'] ?? []);

        if ($topicIds !== [] && $boardIds !== []) {
            $boardService = new QbBoardPublisherService($this->db);
            $topicIds     = $boardService->filterTopicIdsByBoardPublishers($topicIds, $boardIds);
        }

        if ($classIds === [] && $subjectIds === [] && $topicIds === []) {
            return [];
        }

        $builder = $this->db->table('qb_questions q');
        $builder->select('q.*, c.class_name, s.subject_name, t.topic_name');
        $builder->join('classes c', 'c.class_id = q.class_id', 'left');
        $builder->join('allsubject s', 's.sid = q.subject_id', 'left');
        $builder->join('qb_topics t', 't.id = q.topic_id', 'left');

        if ($classIds !== []) {
            $builder->whereIn('q.class_id', $classIds);
        }
        if ($subjectIds !== []) {
            $builder->whereIn('q.subject_id', $subjectIds);
        }
        if ($topicIds !== []) {
            $builder->whereIn('q.topic_id', $topicIds);
        } elseif ($boardIds !== []) {
            $boardService = new QbBoardPublisherService($this->db);
            $boardSql     = $boardService->topicMatchesBoardFilterSql('t.id', $boardIds);
            if ($boardSql !== null) {
                $builder->where($boardSql, null, false);
            }
        }
        if ($types !== []) {
            $builder->whereIn('q.question_type', $types);
        }
        if ($diffs !== []) {
            $builder->whereIn('q.difficulty', $diffs);
        }

        $builder->orderBy('q.id', 'ASC');
        $rows = $builder->get()->getResult();

        return QbQuestionPresenter::normalizeMany($rows);
    }

    /**
     * @param list<array<string, mixed>> $pool
     * @param array<string, mixed> $options
     * @return list<array<string, mixed>>
     */
    public function assemble(array $pool, array $options): array
    {
        $mode = (string) ($options['selection_mode'] ?? 'auto');
        $manualIds = $this->intList($options['question_ids'] ?? []);

        if ($mode === 'manual' && $manualIds !== []) {
            $byId = [];
            foreach ($pool as $q) {
                $byId[(int) ($q['id'] ?? 0)] = $q;
            }
            $picked = [];
            foreach ($manualIds as $id) {
                if (isset($byId[$id])) {
                    $picked[] = $byId[$id];
                }
            }
            $questions = $picked;
        } elseif ($mode === 'all') {
            $questions = $pool;
        } else {
            $questions = $this->sampleByTypeCounts($pool, $options['counts'] ?? []);
        }

        if (!empty($options['shuffle_questions'])) {
            shuffle($questions);
        }

        if (!empty($options['shuffle_mcq_options'])) {
            foreach ($questions as &$q) {
                if (in_array($q['question_type'] ?? '', ['mcq', 'mcq_multi'], true)) {
                    $this->shuffleMcqOptions($q);
                }
            }
            unset($q);
        }

        if (!empty($options['group_by_topic'])) {
            usort($questions, static function (array $a, array $b): int {
                $ta = ($a['topic_name'] ?? '') . '|' . ($a['topic_id'] ?? 0);
                $tb = ($b['topic_name'] ?? '') . '|' . ($b['topic_id'] ?? 0);

                return strcmp($ta, $tb);
            });
        }

        if (count($questions) > self::MAX_QUESTIONS) {
            $questions = array_slice($questions, 0, self::MAX_QUESTIONS);
        }

        return $questions;
    }

    /**
     * @param list<array<string, mixed>> $pool
     * @param array<string, int> $counts
     * @return list<array<string, mixed>>
     */
    public function sampleByTypeCounts(array $pool, array $counts): array
    {
        $map = [
            'mcq'         => 'mcq',
            'mcq_multi'   => 'mcq_multi',
            'tf'          => 'tf',
            'fill'        => 'fill',
            'short'       => 'short',
            'descriptive' => 'descriptive',
            'match'       => 'match',
        ];

        $buckets = [];
        foreach ($pool as $q) {
            $t = (string) ($q['question_type'] ?? '');
            if (!isset($buckets[$t])) {
                $buckets[$t] = [];
            }
            $buckets[$t][] = $q;
        }

        $picked = [];
        foreach ($map as $key => $type) {
            $need = max(0, (int) ($counts[$key] ?? 0));
            if ($need <= 0 || empty($buckets[$type])) {
                continue;
            }
            $bucket = $buckets[$type];
            shuffle($bucket);
            $picked = array_merge($picked, array_slice($bucket, 0, $need));
        }

        return $picked;
    }

    /**
     * @param list<array<string, mixed>> $questions
     * @return list<array{topic_name: string, topic_id: int, questions: list<array<string, mixed>>}>
     */
    public function groupByTopic(array $questions): array
    {
        $groups = [];
        foreach ($questions as $q) {
            $tid = (int) ($q['topic_id'] ?? 0);
            $key = $tid . '|' . ($q['topic_name'] ?? '');
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'topic_id'   => $tid,
                    'topic_name' => (string) ($q['topic_name'] ?? 'General'),
                    'questions'  => [],
                ];
            }
            $groups[$key]['questions'][] = $q;
        }

        return array_values($groups);
    }

    /**
     * Group questions into Section A, B, C… by question type (MCQ block, TF, fill, etc.).
     *
     * @param list<array<string, mixed>> $questions
     * @return list<array{
     *   letter: string,
     *   title: string,
     *   type_key: string,
     *   questions: list<array<string, mixed>>
     * }>
     */
    public function groupByTypeSections(array $questions): array
    {
        $definitions = [
            ['key' => 'mcq', 'types' => ['mcq', 'mcq_multi']],
            ['key' => 'tf', 'types' => ['tf']],
            ['key' => 'fill', 'types' => ['fill']],
            ['key' => 'short', 'types' => ['short']],
            ['key' => 'descriptive', 'types' => ['descriptive']],
            ['key' => 'match', 'types' => ['match']],
        ];

        $byType = [];
        foreach ($questions as $q) {
            $t = strtolower((string) ($q['question_type'] ?? ''));
            if (!isset($byType[$t])) {
                $byType[$t] = [];
            }
            $byType[$t][] = $q;
        }

        $sections   = [];
        $letterIdx  = 0;
        $assigned   = [];

        foreach ($definitions as $def) {
            $bucket = [];
            foreach ($def['types'] as $type) {
                if (!empty($byType[$type])) {
                    $bucket = array_merge($bucket, $byType[$type]);
                    $assigned[$type] = true;
                }
            }
            if ($bucket === []) {
                continue;
            }

            $letter = chr(65 + $letterIdx);
            $letterIdx++;
            $sections[] = [
                'letter'    => $letter,
                'title'     => 'Section ' . $letter,
                'type_key'  => $def['key'],
                'questions' => $bucket,
            ];
        }

        foreach ($byType as $type => $bucket) {
            if (isset($assigned[$type]) || $bucket === []) {
                continue;
            }
            $letter = chr(65 + $letterIdx);
            $letterIdx++;
            $sections[] = [
                'letter'    => $letter,
                'title'     => 'Section ' . $letter,
                'type_key'  => $type,
                'questions' => $bucket,
            ];
        }

        return $sections;
    }

    /**
     * Attach section marks and per-question marks from builder config.
     *
     * @param list<array<string, mixed>> $sections
     * @param array<string, float|int|string> $sectionMarks keyed by count field (mcq, descriptive, …)
     * @return list<array<string, mixed>>
     */
    public function enrichTypeSections(array $sections, array $sectionMarks): array
    {
        $markKeyMap = [
            'mcq'         => ['mcq', 'mcq_multi'],
            'tf'          => ['tf'],
            'fill'        => ['fill'],
            'short'       => ['short'],
            'descriptive' => ['descriptive'],
            'match'       => ['match'],
        ];

        foreach ($sections as &$section) {
            $typeKey = (string) ($section['type_key'] ?? '');
            $keys    = $markKeyMap[$typeKey] ?? [$typeKey];
            $marks   = $this->resolveSectionMarksForKeys($keys, $sectionMarks);
            $n       = count($section['questions'] ?? []);

            $section['section_marks']          = $marks;
            $section['marks_per_question']     = ($n > 0 && $marks > 0) ? $marks / $n : 0.0;
            $section['marks_per_question_label'] = self::formatMarksValue($section['marks_per_question']);
        }
        unset($section);

        return $sections;
    }

    /**
     * @param list<string> $keys
     * @param array<string, float|int|string> $sectionMarks
     */
    public function resolveSectionMarksForKeys(array $keys, array $sectionMarks): float
    {
        foreach ($keys as $k) {
            $m = (float) ($sectionMarks[$k] ?? 0);
            if ($m > 0) {
                return $m;
            }
        }

        return 0.0;
    }

    /**
     * @param list<array<string, mixed>> $sections
     */
    public function sumSectionMarks(array $sections): float
    {
        $total = 0.0;
        foreach ($sections as $section) {
            $total += (float) ($section['section_marks'] ?? 0);
        }

        return $total;
    }

    public static function formatMarksValue(float $value): string
    {
        if ($value <= 0) {
            return '';
        }
        if (abs($value - round($value)) < 0.001) {
            return (string) (int) round($value);
        }

        return rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.');
    }

    /**
     * Extra line under descriptive section title (attempt any N, OR pairs, etc.).
     *
     * @param array<string, mixed> $choice
     */
    public function descriptiveChoiceSectionNote(array $choice, int $questionCount): string
    {
        if ($questionCount <= 0) {
            return '';
        }

        $mode = (string) ($choice['mode'] ?? 'none');
        if ($mode === 'attempt_any') {
            $n = (int) ($choice['attempt_any_count'] ?? 0);
            if ($n > 0) {
                $n = min($n, $questionCount);

                return 'Attempt any ' . $n . ' question' . ($n === 1 ? '' : 's');
            }
        }

        if ($mode === 'pairs') {
            $pairs = $this->normalizeDescriptivePairs($choice['pairs'] ?? [], $questionCount);
            if ($pairs !== []) {
                $pc = count($pairs);

                return 'Attempt one question from each pair (' . $pc . ' pair' . ($pc === 1 ? '' : 's') . ')';
            }
        }

        return '';
    }

    /**
     * Build render sequence for descriptive section (questions + OR dividers).
     *
     * @param list<array<string, mixed>> $questions
     * @param array<string, mixed> $choice
     * @return list<array{type: string, q?: array, roman?: int}>
     */
    public function buildDescriptiveDisplayItems(array $questions, array $choice): array
    {
        $count = count($questions);
        if ($count === 0) {
            return [];
        }

        $mode = (string) ($choice['mode'] ?? 'none');
        if ($mode === 'pairs') {
            $pairs = $this->normalizeDescriptivePairs($choice['pairs'] ?? [], $count);
            if ($pairs !== []) {
                return $this->buildDescriptivePairDisplayItems($questions, $pairs);
            }
        }

        $items = [];
        $roman   = 0;
        foreach ($questions as $q) {
            $roman++;
            $items[] = ['type' => 'question', 'q' => $q, 'roman' => $roman];
        }

        return $items;
    }

    /**
     * @param list<array<string, mixed>> $questions
     * @param list<array{0: int, 1: int}> $pairs
     * @return list<array{type: string, q?: array, roman?: int}>
     */
    protected function buildDescriptivePairDisplayItems(array $questions, array $pairs): array
    {
        $items = [];
        $used  = [];
        $roman = 0;

        foreach ($pairs as $pair) {
            $slotQuestions = [];
            foreach ($pair as $idx) {
                if ($idx < 1 || $idx > count($questions) || isset($used[$idx])) {
                    continue;
                }
                $used[$idx]        = true;
                $slotQuestions[] = $questions[$idx - 1];
            }

            if ($slotQuestions === []) {
                continue;
            }

            $roman++;
            $lastPi = count($slotQuestions) - 1;
            foreach ($slotQuestions as $pi => $q) {
                $items[] = [
                    'type'         => 'question',
                    'q'            => $q,
                    'roman'        => $roman,
                    'show_number'  => $pi === 0,
                    'pair_part'    => true,
                    'pair_end'     => $pi === $lastPi,
                ];
                if ($pi < $lastPi) {
                    $items[] = ['type' => 'or'];
                }
            }
        }

        for ($i = 1; $i <= count($questions); $i++) {
            if (isset($used[$i])) {
                continue;
            }
            $roman++;
            $items[] = [
                'type'        => 'question',
                'q'           => $questions[$i - 1],
                'roman'       => $roman,
                'show_number' => true,
            ];
        }

        return $items;
    }

    /**
     * @param mixed $pairs
     * @return list<array{0: int, 1: int}>
     */
    public function normalizeDescriptivePairs($pairs, int $maxQuestions): array
    {
        if (!is_array($pairs) || $maxQuestions <= 0) {
            return [];
        }

        $out = [];
        foreach ($pairs as $pair) {
            if (!is_array($pair) || count($pair) < 2) {
                continue;
            }
            $a = (int) ($pair[0] ?? 0);
            $b = (int) ($pair[1] ?? 0);
            if ($a < 1 || $b < 1 || $a > $maxQuestions || $b > $maxQuestions || $a === $b) {
                continue;
            }
            $out[] = [$a, $b];
        }

        return $out;
    }

    /**
     * Lowercase Roman numerals for question numbering (i, ii, iii, iv, …).
     */
    public static function toRoman(int $n): string
    {
        if ($n <= 0) {
            return '';
        }

        $map = [
            1000 => 'm', 900 => 'cm', 500 => 'd', 400 => 'cd',
            100  => 'c', 90  => 'xc', 50  => 'l', 40  => 'xl',
            10   => 'x', 9   => 'ix', 5   => 'v', 4   => 'iv',
            1    => 'i',
        ];

        $out = '';
        foreach ($map as $value => $numeral) {
            while ($n >= $value) {
                $out .= $numeral;
                $n -= $value;
            }
        }

        return $out;
    }

    /**
     * @param list<int> $questionIds
     * @return list<array<string, mixed>>
     */
    public function fetchByIds(array $questionIds): array
    {
        $ids = $this->intList($questionIds);
        if ($ids === []) {
            return [];
        }

        $builder = $this->db->table('qb_questions q');
        $builder->select('q.*, c.class_name, s.subject_name, t.topic_name');
        $builder->join('classes c', 'c.class_id = q.class_id', 'left');
        $builder->join('allsubject s', 's.sid = q.subject_id', 'left');
        $builder->join('qb_topics t', 't.id = q.topic_id', 'left');
        $builder->whereIn('q.id', $ids);
        $rows = $builder->get()->getResult();
        $norm = QbQuestionPresenter::normalizeMany($rows);
        $byId = [];
        foreach ($norm as $q) {
            $byId[(int) $q['id']] = $q;
        }
        $ordered = [];
        foreach ($ids as $id) {
            if (isset($byId[$id])) {
                $ordered[] = $byId[$id];
            }
        }

        return $ordered;
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, int>
     */
    public function countByTypeInPool(array $filters): array
    {
        $pool = $this->fetchPool($filters);
        $sum  = [
            'total'         => count($pool),
            'mcq'           => 0,
            'mcq_multi'     => 0,
            'tf'            => 0,
            'fill'          => 0,
            'short'         => 0,
            'descriptive'   => 0,
            'match'         => 0,
        ];
        foreach ($pool as $q) {
            $t = (string) ($q['question_type'] ?? '');
            if (isset($sum[$t])) {
                $sum[$t]++;
            }
        }

        return $sum;
    }

    /**
     * @param array<string, mixed> $q
     */
    protected function shuffleMcqOptions(array &$q): void
    {
        $letters = ['A', 'B', 'C', 'D'];
        $opts    = [];
        foreach ($letters as $L) {
            $key = 'option_' . strtolower($L);
            $opts[$L] = (string) ($q[$key] ?? '');
        }

        $correct = strtoupper(trim((string) ($q['correct_option'] ?? 'A')));
        $correctText = $opts[$correct] ?? '';

        $keys = array_keys($opts);
        shuffle($keys);
        $new = [];
        $newCorrect = 'A';
        $i = 0;
        foreach ($keys as $oldL) {
            $newL = $letters[$i];
            $new[$newL] = $opts[$oldL];
            if ($oldL === $correct) {
                $newCorrect = $newL;
            }
            $i++;
        }

        foreach ($letters as $L) {
            $q['option_' . strtolower($L)] = $new[$L] ?? '';
        }
        $q['correct_option'] = $newCorrect;

        if (($q['question_type'] ?? '') === 'mcq_multi' && is_array($q['correct_options'] ?? null)) {
            // keep multi labels as-is after shuffle (complex); skip remapping
        }
    }

    /**
     * @param mixed $list
     * @return list<int>
     */
    protected function intList($list): array
    {
        if (!is_array($list)) {
            return [];
        }
        $out = [];
        foreach ($list as $v) {
            $n = (int) $v;
            if ($n > 0) {
                $out[] = $n;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * @param mixed $list
     * @return list<string>
     */
    protected function stringList($list): array
    {
        if (!is_array($list)) {
            return [];
        }
        $out = [];
        foreach ($list as $v) {
            $s = trim((string) $v);
            if ($s !== '') {
                $out[] = $s;
            }
        }

        return array_values(array_unique($out));
    }
}
