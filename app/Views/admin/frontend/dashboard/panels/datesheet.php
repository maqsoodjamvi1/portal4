<?php if (!empty($dsDash['show'])): ?>
<div class="card shadow-sm mb-4" id="dash-datesheet-card">
    <?php $dsHeaderExam = trim((string) ($dsDash['exam_name'] ?? '')); ?>
    <div class="card-header bg-white py-3">
        <div class="d-flex align-items-center gap-2">
            <span class="rounded-circle bg-warning text-dark d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                <i class="fa fa-calendar-alt"></i>
            </span>
            <div>
                <h5 class="mb-0"><?= $isUrdu ? '??????? ?????' : 'Exam datesheet' ?></h5>
                <div class="small fw-semibold mt-1">
                    <?php if ($dsHeaderExam !== ''): ?>
                        <i class="fa fa-graduation-cap me-1 text-primary" aria-hidden="true"></i><span class="text-body"><?= esc($dsHeaderExam) ?></span>
                    <?php else: ?>
                        <span class="text-muted"><?= $isUrdu ? '?????? ?? ??? ?????? ????' : 'Exam name not available' ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body border-top">
            <?php if (!empty($dsDash['datesheet'])): ?>
                <?php
                $urduWeekdays = [
                    'Monday' => '???', 'Tuesday' => '????', 'Wednesday' => '???', 'Thursday' => '??????',
                    'Friday' => '????', 'Saturday' => '????', 'Sunday' => '?????',
                ];
                ?>
                <div class="dash-ds-schedule">
                    <?php foreach ($dsDash['datesheet'] as $examDate => $rows): ?>
                        <?php
                        $dObj = new DateTime($examDate);
                        $englishDay = $dObj->format('l');
                        $dayWord = $isUrdu ? ($urduWeekdays[$englishDay] ?? $englishDay) : $englishDay;
                        $dateLine = $dObj->format('j F Y');
                        $nPapers = count($rows);
                        ?>
                        <div class="dash-ds-day-block mb-3">
                            <div class="dash-ds-day-head d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="dash-ds-day-icon text-white rounded d-inline-flex align-items-center justify-content-center">
                                        <i class="fa fa-calendar-day" aria-hidden="true"></i>
                                    </span>
                                    <div>
                                        <div class="dash-ds-day-title"><?= esc($dayWord) ?></div>
                                        <div class="dash-ds-day-sub text-monospace"><?= esc($dateLine) ?></div>
                                    </div>
                                </div>
                                <span class="badge text-bg-light text-dark border dash-ds-day-badge">
                                    <?= (int) $nPapers ?> <?= $isUrdu ? ($nPapers === 1 ? '????' : '?????') : ($nPapers === 1 ? 'paper' : 'papers') ?>
                                </span>
                            </div>
                            <div class="table-responsive dash-ds-table-wrap">
                                <table class="table table-sm table-hover dash-ds-day-table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="dash-ds-th-subject"><?= $isUrdu ? '?????' : 'Subject' ?></th>
                                            <th class="dash-ds-th-marks text-center"><?= $isUrdu ? '?? ????' : 'Total marks' ?></th>
                                            <th class="dash-ds-th-syll text-center"><?= $isUrdu ? '?????' : 'Syllabus' ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rows as $r): ?>
                                            <?php
                                            $secSub = (int) ($r['sec_sub_id'] ?? 0);
                                            $collapseId = 'dshsyl-' . (int) ($dsDash['eid'] ?? 0) . '-' . $secSub . '-' . preg_replace('/\D/', '', (string) $examDate);
                                            $syllabusRaw = (string) ($r['syllabus'] ?? '');
                                            $syllabusPlain = trim(html_entity_decode(strip_tags($syllabusRaw), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                                            $hasSyllabus = $syllabusPlain !== '';
                                            $isUrduSyll = $hasSyllabus ? (bool) preg_match('/\p{Arabic}/u', $syllabusPlain) : false;
                                            ?>
                                            <tr class="dash-ds-data-row">
                                                <td class="align-middle"><?= esc($r['subject_name'] ?? '') ?></td>
                                                <td class="text-center align-middle fw-bold text-nowrap dash-ds-marks-cell">
                                                    <?= isset($r['total_marks']) ? (int) $r['total_marks'] : '—' ?>
                                                </td>
                                                <td class="text-center align-middle dash-ds-syl-cell">
                                                    <?php if ($hasSyllabus): ?>
                                                        <button type="button"
                                                                class="btn btn-outline-primary btn-sm dash-ds-syl-btn text-nowrap"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#<?= esc($collapseId, 'attr') ?>"
                                                                aria-expanded="false"
                                                                aria-controls="<?= esc($collapseId, 'attr') ?>">
                                                            <i class="fa fa-book-open me-1" aria-hidden="true"></i><?= $isUrdu ? '??????' : 'View' ?>
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-muted small">—</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php if ($hasSyllabus): ?>
                                                <tr class="dash-ds-syllabus-row">
                                                    <td colspan="3" class="p-0 border-top-0">
                                                        <div id="<?= esc($collapseId, 'attr') ?>" class="collapse dash-ds-syllabus-panel">
                                                            <div class="dash-ds-syllabus-inner small" dir="<?= $isUrduSyll ? 'rtl' : 'ltr' ?>">
                                                                <div class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.04em;">
                                                                    <?= $isUrdu ? '?????' : 'Syllabus' ?>
                                                                </div>
                                                                <?= nl2br(esc($syllabusPlain)) ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info mb-0">
                    <?= esc($dsDash['message'] ?? ($isUrdu ? '????? ?????? ?????' : 'No schedule rows available yet.')) ?>
                </div>
            <?php endif; ?>
            <div class="text-center mt-3">
                <a href="<?= esc($datesheetUrl) ?>" class="btn btn-primary btn-sm"><?= $isUrdu ? '???? ??????? ????? ??? ???????' : 'See complete exam schedule & details' ?></a>
            </div>
    </div>
</div>
<?php else: ?>
<div class="card shadow-sm mb-4 border-0 bg-light">
    <div class="card-body py-4 text-center">
        <i class="fa fa-calendar-alt fa-2x text-muted mb-2 d-block"></i>
        <p class="text-muted mb-3 small px-2"><?= $isUrdu ? '?? ??? ???? ??? ????? ??? ??????? ????? ????? ?? ????? ??? ????? ????? ?? ???? ??? ??? ???' : 'There is no unpublished exam schedule right now. When the school opens one for the current exam, it will appear here.' ?></p>
        <a href="<?= esc($datesheetUrl) ?>" class="btn btn-outline-primary btn-sm"><?= $isUrdu ? '???? ????? ????' : 'Open full datesheet' ?></a>
    </div>
</div>
<?php endif; ?>
