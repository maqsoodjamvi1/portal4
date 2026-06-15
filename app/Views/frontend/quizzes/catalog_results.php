<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<?php
$isParent = (($role ?? '') === 'parent');
$activeId = (int) ($active_student_id ?? 0);
$sidQs    = $sidQs ?? (($isParent && $activeId > 0) ? ('?sid=' . $activeId) : '');
$resultTermGroups     = $resultTermGroups ?? [];
$currentTermSessionId = (int) ($currentTermSessionId ?? 0);

$formatPct = static function ($pct) {
    if ($pct === null || $pct === '') {
        return '—';
    }

    return rtrim(rtrim(number_format((float) $pct, 1, '.', ''), '0'), '.') . '%';
};

$formatDate = static function ($dt) {
    if (empty($dt) || $dt === '0000-00-00 00:00:00') {
        return '—';
    }
    $ts = strtotime($dt);

    return $ts ? date('d M Y, h:i A', $ts) : esc($dt);
};
?>
<link rel="stylesheet" href="<?= base_url('assets/css/parent_portal_subpages.css') ?>">

<section class="content parent-subpage-content pt-2">
    <div class="container-fluid px-2 px-md-3">
        <?php if ($isParent): ?>
            <div class="ds-page-layout">
                <aside class="ds-page-layout__filter ds-sticky-filter-mobile" aria-label="Student selection">
                    <div class="ds-datesheet-filter">
                        <?= view('frontend/partials/parent_child_selector', [
                            'children'        => $children ?? [],
                            'activeStudentId' => $activeId,
                            'returnPath'      => 'student/quizzes/my-results',
                        ]) ?>
                    </div>
                </aside>
                <div class="ds-page-layout__content">
        <?php endif; ?>

        <div class="parent-subpage-panel">
            <div class="parent-subpage-title-row flex-wrap align-items-center">
                <div class="flex-grow-1">
                    <h2 class="parent-subpage-title mb-0">
                        <i class="fas fa-chart-bar text-primary me-2"></i> Quiz results
                    </h2>
                    <p class="text-muted small mb-0 mt-1">Results by term, subject, and quiz. Each attempt can be opened for review.</p>
                    <?php if (! empty($sessionName)): ?>
                        <p class="text-muted small mb-0 mt-1">
                            <i class="fas fa-graduation-cap me-1"></i><?= esc($sessionName) ?>
                        </p>
                    <?php endif; ?>
                </div>
                <a href="<?= esc(site_url('student/quizzes/all') . $sidQs) ?>" class="btn btn-outline-secondary btn-sm mt-2 mt-md-0">
                    <i class="fas fa-arrow-left me-1"></i> Back to quizzes
                </a>
            </div>

            <?php if (! empty($err)): ?>
                <div class="alert alert-warning mt-3"><?= esc($err) ?></div>
            <?php endif; ?>

            <?php if (empty($resultTermGroups)): ?>
                <div class="alert alert-info mt-3 mb-0 text-center">
                    No submitted quiz attempts yet. Take a quiz from the catalog to see results here.
                </div>
            <?php else: ?>
                <div class="quiz-results-term-accordion mt-3" id="quizResultsTerms">
                    <?php foreach ($resultTermGroups as $group): ?>
                        <?php
                        $tsid      = (int) ($group['term_session_id'] ?? 0);
                        $termKey   = 'qr_term_' . $tsid;
                        $isCurrent = ! empty($group['is_current']);
                        $expanded  = $isCurrent;
                        $termLabel = trim((string) ($group['term_name'] ?? 'Term'));
                        $termShort = trim((string) ($group['term_short'] ?? ''));
                        if ($termShort !== '' && $termShort !== $termLabel) {
                            $termLabel = $termShort . ' — ' . $termLabel;
                        }
                        $tSum = $group['summary'] ?? [];
                        $subjects = $group['subjects'] ?? [];
                        ?>
                        <div class="card quiz-term-card mb-2 border-0 shadow-sm">
                            <div class="card-header p-0 border-0 bg-white" id="<?= esc($termKey) ?>_h">
                                <button class="btn btn-link w-100 text-start quiz-term-toggle d-flex justify-content-between align-items-center py-3 px-3"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#<?= esc($termKey) ?>_b"
                                        aria-expanded="<?= $expanded ? 'true' : 'false' ?>">
                                    <div>
                                        <strong class="text-dark"><?= esc($termLabel) ?></strong>
                                        <?php if ($isCurrent): ?>
                                            <span class="badge text-bg-primary ms-2">Current term</span>
                                        <?php endif; ?>
                                        <?php if (! empty($group['start_date']) && ! empty($group['end_date'])): ?>
                                            <div class="text-muted small mt-1">
                                                <?= esc(substr((string) $group['start_date'], 0, 10)) ?>
                                                → <?= esc(substr((string) $group['end_date'], 0, 10)) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-end flex-shrink-0 ms-2">
                                        <?php if (! empty($tSum['avg_percentage'])): ?>
                                            <div class="small text-muted">Term avg</div>
                                            <strong class="text-primary"><?= $formatPct($tSum['avg_percentage'] ?? null) ?></strong>
                                        <?php endif; ?>
                                        <i class="fas fa-chevron-down text-muted quiz-term-chevron d-block mt-1"></i>
                                    </div>
                                </button>
                            </div>
                            <div id="<?= esc($termKey) ?>_b" class="collapse<?= $expanded ? ' show' : '' ?>">
                                <div class="card-body pt-0 pb-3 px-2 px-md-3">
                                    <div class="quiz-term-overall mb-3 p-3 rounded" style="background:#f0f4ff;">
                                        <div class="row text-center small">
                                            <div class="col-6 col-md-3 mb-2 mb-md-0">
                                                <div class="text-muted">Subjects</div>
                                                <strong><?= count($subjects) ?></strong>
                                            </div>
                                            <div class="col-6 col-md-3 mb-2 mb-md-0">
                                                <div class="text-muted">Attempts</div>
                                                <strong><?= (int) ($tSum['attempt_count'] ?? 0) ?></strong>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="text-muted">Term average</div>
                                                <strong><?= $formatPct($tSum['avg_percentage'] ?? null) ?></strong>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="text-muted">Best score</div>
                                                <strong><?= $formatPct($tSum['best_percentage'] ?? null) ?></strong>
                                            </div>
                                        </div>
                                    </div>

                                    <?php foreach ($subjects as $sub): ?>
                                        <?php
                                        $subSum  = $sub['summary'] ?? [];
                                        $quizzes = $sub['quizzes'] ?? [];
                                        $subKey  = 'qr_sub_' . $tsid . '_' . (int) ($sub['subject_id'] ?? 0);
                                        ?>
                                        <div class="card border mb-3 quiz-subject-card">
                                            <div class="card-header bg-white py-2 px-3">
                                                <button class="btn btn-link w-100 text-start p-0 d-flex justify-content-between align-items-center"
                                                        type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#<?= esc($subKey) ?>_b"
                                                        aria-expanded="true">
                                                    <span>
                                                        <i class="fas fa-book-open text-primary me-2"></i>
                                                        <strong><?= esc($sub['subject_label'] ?? 'Subject') ?></strong>
                                                        <span class="badge text-bg-light border ms-2"><?= count($quizzes) ?> quiz<?= count($quizzes) === 1 ? '' : 'zes' ?></span>
                                                    </span>
                                                    <span class="small text-muted">
                                                        Avg <?= $formatPct($subSum['avg_percentage'] ?? null) ?>
                                                        <i class="fas fa-chevron-down ms-1"></i>
                                                    </span>
                                                </button>
                                            </div>
                                            <div id="<?= esc($subKey) ?>_b" class="collapse show">
                                                <div class="card-body p-0">
                                                    <?php foreach ($quizzes as $quiz): ?>
                                                        <?php
                                                        $attempts = $quiz['attempts'] ?? [];
                                                        $qid      = (int) ($quiz['quiz_id'] ?? 0);
                                                        ?>
                                                        <div class="border-top px-3 py-3" id="quiz-results-<?= $qid ?>">
                                                            <div class="d-flex flex-wrap justify-content-between align-items-start mb-2">
                                                                <div>
                                                                    <strong><?= esc($quiz['quiz_title'] ?? 'Quiz') ?></strong>
                                                                    <?php if (! empty($quiz['best_percentage'])): ?>
                                                                        <span class="badge text-bg-success ms-2">Best <?= $formatPct($quiz['best_percentage']) ?></span>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <span class="text-muted small"><?= count($attempts) ?> attempt<?= count($attempts) === 1 ? '' : 's' ?></span>
                                                            </div>
                                                            <?php if ($attempts === []): ?>
                                                                <p class="text-muted small mb-0">No attempts.</p>
                                                            <?php else: ?>
                                                                <div class="table-responsive">
                                                                    <table class="table table-sm table-hover mb-0 quiz-attempts-table">
                                                                        <thead class="table-light">
                                                                            <tr>
                                                                                <th>#</th>
                                                                                <th>Score</th>
                                                                                <th>%</th>
                                                                                <th>Correct</th>
                                                                                <th>Submitted</th>
                                                                                <th></th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php foreach ($attempts as $att): ?>
                                                                                <tr>
                                                                                    <td><?= (int) ($att['attempt_no'] ?? 0) ?></td>
                                                                                    <td>
                                                                                        <?= rtrim(rtrim(number_format((float) ($att['score'] ?? 0), 1, '.', ''), '0'), '.') ?>
                                                                                        <?php if ((float) ($att['max_marks'] ?? 0) > 0): ?>
                                                                                            <span class="text-muted">/ <?= rtrim(rtrim(number_format((float) $att['max_marks'], 1, '.', ''), '0'), '.') ?></span>
                                                                                        <?php endif; ?>
                                                                                    </td>
                                                                                    <td><?= $formatPct($att['percentage'] ?? null) ?></td>
                                                                                    <td>
                                                                                        <span class="text-success"><?= (int) ($att['correct'] ?? 0) ?></span>
                                                                                        /
                                                                                        <span class="text-danger"><?= (int) ($att['wrong'] ?? 0) ?></span>
                                                                                    </td>
                                                                                    <td class="small"><?= $formatDate($att['submitted_at'] ?? '') ?></td>
                                                                                    <td class="text-end">
                                                                                        <a href="<?= esc($att['review_url'] ?? '#') ?>" class="btn btn-info btn-sm btn-sm">
                                                                                            Review
                                                                                        </a>
                                                                                    </td>
                                                                                </tr>
                                                                            <?php endforeach; ?>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <?php if ($subjects === []): ?>
                                        <p class="text-muted small mb-0 px-2">No results for this term.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($isParent): ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.quiz-term-card { border-radius: 14px; overflow: hidden; }
.quiz-term-toggle { text-decoration: none !important; color: inherit; }
.quiz-term-toggle:hover { background: #f8fafc; text-decoration: none !important; }
.quiz-term-toggle[aria-expanded="true"] .quiz-term-chevron { transform: rotate(180deg); }
.quiz-term-chevron { transition: transform .2s ease; }
.quiz-subject-card { border-radius: 12px; }
.quiz-attempts-table th { font-size: .75rem; white-space: nowrap; }
.quiz-attempts-table td { font-size: .85rem; vertical-align: middle; }
</style>

<?= $this->endSection() ?>
