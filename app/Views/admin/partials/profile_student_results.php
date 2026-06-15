<?php
$sessions = $sessions ?? [];
$insights = $insights ?? ['strengths' => [], 'weaknesses' => []];
$current_session_id = (int) ($current_session_id ?? 0);

$pctClass = static function ($pct): string {
    if ($pct === null || $pct === '') {
        return '';
    }
    $pct = (float) $pct;
    if ($pct >= 80) {
        return 'sp-res-pct-high';
    }
    if ($pct >= 60) {
        return 'sp-res-pct-mid';
    }

    return 'sp-res-pct-low';
};

$ordinal = static function (?int $n): string {
    if ($n === null || $n < 1) {
        return '—';
    }
    if (! in_array($n % 100, [11, 12, 13], true)) {
        switch ($n % 10) {
            case 1: return $n . 'st';
            case 2: return $n . 'nd';
            case 3: return $n . 'rd';
        }
    }

    return $n . 'th';
};
?>
<?php if (empty($sessions)): ?>
    <div class="alert alert-info mb-0">
        <i class="fas fa-info-circle me-1"></i> No exam subject results are recorded for this student yet. Results appear here after marks are entered for exams.
    </div>
<?php else: ?>
    <div class="sp-results-wrap">
        <?php if (! empty($insights['strengths']) || ! empty($insights['weaknesses'])): ?>
            <div class="row mb-3 sp-results-insights">
                <?php if (! empty($insights['strengths'])): ?>
                    <div class="col-md-6 mb-2 mb-md-0">
                        <div class="card border-success h-100 mb-0">
                            <div class="card-header py-2 bg-success text-white">
                                <i class="fas fa-arrow-up me-1"></i> <strong>Strengths</strong>
                                <small class="d-block fw-normal opacity-90">Subjects averaging 75%+ across all exams</small>
                            </div>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($insights['strengths'] as $s): ?>
                                    <li class="list-group-item py-2 d-flex justify-content-between align-items-center">
                                        <span><?= esc($s['subject_name']) ?></span>
                                        <span>
                                            <span class="badge text-bg-success"><?= esc((string) $s['avg_pct']) ?>%</span>
                                            <small class="text-muted ms-1">(<?= (int) $s['exam_count'] ?> exams)</small>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (! empty($insights['weaknesses'])): ?>
                    <div class="col-md-6">
                        <div class="card border-warning h-100 mb-0">
                            <div class="card-header py-2 bg-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i> <strong>Needs attention</strong>
                                <small class="d-block fw-normal">Subjects below 75% on average</small>
                            </div>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($insights['weaknesses'] as $w): ?>
                                    <li class="list-group-item py-2 d-flex justify-content-between align-items-center">
                                        <span><?= esc($w['subject_name']) ?></span>
                                        <span>
                                            <span class="badge text-bg-warning text-dark"><?= esc((string) $w['avg_pct']) ?>%</span>
                                            <small class="text-muted ms-1">(<?= (int) $w['exam_count'] ?> exams)</small>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <p class="text-muted small mb-3">
            <i class="fas fa-layer-group me-1"></i>
            Results are grouped by <strong>academic session</strong> (school year). Expand a year to compare subjects across terms/exams in one table.
        </p>

        <div class="accordion sp-results-accordion" id="spResultsAccordion">
            <?php $idx = 0; foreach ($sessions as $session): ?>
                <?php
                $sid = (int) $session['session_id'];
                $collapseId = 'spResSession' . $sid;
                $expanded = ! empty($session['is_current']) || ($current_session_id > 0 && $sid === $current_session_id) || ($current_session_id === 0 && $idx === 0);
                $exams = $session['exams'] ?? [];
                $subjects = $session['subjects'] ?? [];
                ?>
                <div class="card mb-2 border shadow-sm">
                    <div class="card-header py-2 px-3 sp-results-session-head <?= $expanded ? '' : 'collapsed' ?>"
                            id="heading<?= $collapseId ?>"
                            data-bs-toggle="collapse"
                            data-bs-target="#<?= $collapseId ?>"
                            aria-expanded="<?= $expanded ? 'true' : 'false' ?>"
                            aria-controls="<?= $collapseId ?>"
                            role="button">
                        <div class="d-flex flex-wrap justify-content-between align-items-center w-100">
                            <div>
                                <i class="fas fa-chevron-down sp-results-chevron me-2 text-muted"></i>
                                <strong><?= esc($session['session_name']) ?></strong>
                                <?php if (! empty($session['is_current'])): ?>
                                    <span class="badge text-bg-primary ms-1">Current</span>
                                <?php endif; ?>
                                <?php if (! empty($session['class_label'])): ?>
                                    <span class="text-muted ms-2"><i class="fas fa-graduation-cap"></i> <?= esc($session['class_label']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="text-muted small mt-1 mt-md-0">
                                <?= count($exams) ?> exam<?= count($exams) === 1 ? '' : 's' ?>
                                · <?= count($subjects) ?> subject<?= count($subjects) === 1 ? '' : 's' ?>
                            </div>
                        </div>
                        <?php if (! empty($exams)): ?>
                            <div class="sp-exam-chips mt-2 ms-4" onclick="event.stopPropagation();">
                                <?php foreach ($exams as $exam): ?>
                                    <?php $ep = $exam['overall_pct'] ?? null; ?>
                                    <span class="badge text-bg-light border me-1 mb-1 sp-exam-chip" title="<?= esc($exam['exam_name']) ?>">
                                        <?= esc($exam['exam_name']) ?>
                                        <?php if ($ep !== null): ?>
                                            <span class="<?= $pctClass($ep) ?> fw-bold ms-1"><?= esc((string) $ep) ?>%</span>
                                        <?php endif; ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div id="<?= $collapseId ?>" class="collapse <?= $expanded ? 'show' : '' ?>" aria-labelledby="heading<?= $collapseId ?>">
                        <div class="card-body p-0">
                            <?php if (empty($exams) || empty($subjects)): ?>
                                <p class="text-muted p-3 mb-0">No detailed marks for this session.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0 sp-results-matrix">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="sp-res-subject-col">Subject</th>
                                                <?php foreach ($exams as $exam): ?>
                                                    <th class="text-center sp-res-exam-col">
                                                        <div class="fw-bold"><?= esc($exam['exam_name']) ?></div>
                                                        <?php if (! empty($exam['term_name'])): ?>
                                                            <small class="text-muted d-block"><?= esc($exam['term_name']) ?></small>
                                                        <?php endif; ?>
                                                        <?php if ($exam['overall_pct'] !== null): ?>
                                                            <small class="d-block mt-1">
                                                                Overall:
                                                                <span class="<?= $pctClass($exam['overall_pct']) ?> fw-bold"><?= esc((string) $exam['overall_pct']) ?>%</span>
                                                                <?php if (! empty($exam['overall_grade'])): ?>
                                                                    <span class="text-muted">(<?= esc($exam['overall_grade']) ?>)</span>
                                                                <?php endif; ?>
                                                            </small>
                                                        <?php endif; ?>
                                                        <?php if (! empty($exam['position'])): ?>
                                                            <small class="text-muted d-block">Pos: <?= esc($ordinal($exam['position'])) ?></small>
                                                        <?php endif; ?>
                                                    </th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($subjects as $sub): ?>
                                                <tr>
                                                    <th class="sp-res-subject-col"><?= esc($sub['subject_name']) ?></th>
                                                    <?php foreach ($exams as $exam): ?>
                                                        <?php
                                                        $cell = $sub['cells'][$exam['eid']] ?? null;
                                                        $cp = $cell['pct'] ?? null;
                                                        ?>
                                                        <td class="text-center sp-res-cell <?= $pctClass($cp) ?>">
                                                            <?php if ($cell === null): ?>
                                                                <span class="text-muted">—</span>
                                                            <?php else: ?>
                                                                <?php if ($cp !== null): ?>
                                                                    <div class="sp-res-pct fw-bold"><?= esc((string) $cp) ?>%</div>
                                                                <?php endif; ?>
                                                                <?php if (! empty($cell['total'])): ?>
                                                                    <small class="text-muted d-block"><?= esc((string) (int) $cell['obtained']) ?>/<?= esc((string) (int) $cell['total']) ?></small>
                                                                <?php endif; ?>
                                                                <?php if (! empty($cell['grade'])): ?>
                                                                    <small class="d-block"><?= esc($cell['grade']) ?></small>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php
                                            $examTotals = $session['exam_totals'] ?? [];
                                            $hasTotals = ! empty($examTotals);
                                            ?>
                                            <?php if ($hasTotals): ?>
                                                <tr class="table-active fw-bold">
                                                    <th class="sp-res-subject-col">Total</th>
                                                    <?php foreach ($exams as $exam): ?>
                                                        <?php
                                                        $t = $examTotals[$exam['eid']] ?? null;
                                                        $tp = $t['pct'] ?? null;
                                                        ?>
                                                        <td class="text-center <?= $pctClass($tp) ?>">
                                                            <?php if ($t && ! empty($t['total'])): ?>
                                                                <?php if ($tp !== null): ?>
                                                                    <div class="sp-res-pct"><?= esc((string) $tp) ?>%</div>
                                                                <?php endif; ?>
                                                                <small class="text-muted d-block"><?= esc((string) (int) $t['obtained']) ?>/<?= esc((string) (int) $t['total']) ?></small>
                                                                <?php if (! empty($t['grade'])): ?>
                                                                    <small class="d-block"><?= esc($t['grade']) ?></small>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <span class="text-muted">—</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php $idx++; endforeach; ?>
        </div>
    </div>
<?php endif; ?>
