<div class="card shadow-sm mb-4" id="dash-results-card">
    <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
        <div class="d-flex align-items-center gap-2">
            <span class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                <i class="fa fa-chart-line"></i>
            </span>
            <div>
                <h5 class="mb-0"><?= $isUrdu ? '?????' : 'Results' ?></h5>
                <small class="text-muted">
                    <?php if (!empty($resDash['exam'])): ?>
                        <?= $isUrdu ? '???? ????? ??? ??????: ' : 'Last announced exam: ' ?>
                        <strong><?= esc($resDash['exam']->exam_name ?? '') ?></strong>
                    <?php else: ?>
                        <?= $isUrdu ? '????? ??? ?????? ???? ???' : 'No announced exam in the current session.' ?>
                    <?php endif; ?>
                </small>
            </div>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm dash-collapse-toggle d-inline-flex align-items-center gap-1"
                data-bs-toggle="collapse" data-bs-target="#dashResultsCollapse" aria-expanded="true" aria-controls="dashResultsCollapse">
            <span><?= $isUrdu ? '?????? / ???????' : 'Show / hide' ?></span>
            <i class="fa fa-chevron-down small"></i>
        </button>
    </div>
    <div id="dashResultsCollapse" class="collapse show" role="region">
        <div class="card-body border-top">
            <?php if (empty($resDash['exam'])): ?>
                <p class="text-muted mb-0"><?= $isUrdu ? '?? ????? ????? ?? ????? ??? ?? ???? ???? ?? ???' : 'When the school announces results for the current session, they will appear here.' ?></p>
            <?php else: ?>
                <?php
                $er = $resDash['exam_result'];
                $pct = null;
                if ($er && !empty($er->exam_total_mark) && (float) $er->exam_total_mark > 0) {
                    $pct = round(((float) ($er->obtain_total_mark ?? 0) / (float) $er->exam_total_mark) * 100, 1);
                }
                ?>
                <?php if ($er): ?>
                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-2 text-center bg-light">
                                <div class="small text-muted"><?= $isUrdu ? '????' : 'Obtained' ?></div>
                                <div class="fw-bold"><?= esc($er->obtain_total_mark ?? '—') ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-2 text-center bg-light">
                                <div class="small text-muted"><?= $isUrdu ? '??' : 'Total' ?></div>
                                <div class="fw-bold"><?= esc($er->exam_total_mark ?? '—') ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-2 text-center bg-light">
                                <div class="small text-muted"><?= $isUrdu ? '????' : '%' ?></div>
                                <div class="fw-bold"><?= $pct !== null ? esc((string) $pct) . '%' : '—' ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-2 text-center bg-light">
                                <div class="small text-muted"><?= $isUrdu ? '??????' : 'Position' ?></div>
                                <div class="fw-bold"><?= esc($er->position ?? '—') ?></div>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($er->remark)): ?>
                        <p class="small text-muted mb-3"><strong><?= $isUrdu ? '????:' : 'Remarks:' ?></strong> <?= esc($er->remark) ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-light border mb-3 mb-0">
                        <?= $isUrdu ? '?? ?????? ?? ??? ???? ???? ??? ?? ?????? ????? ???? ???? ????' : 'Overall result for this exam is not entered yet for this student.' ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($resDash['subjects'])): ?>
                    <h6 class="mt-2 mb-2"><?= $isUrdu ? '?????? ?? ????' : 'Subject marks' ?></h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?= $isUrdu ? '?????' : 'Subject' ?></th>
                                    <th class="text-center"><?= $isUrdu ? '????' : 'Obtained' ?></th>
                                    <th class="text-center"><?= $isUrdu ? '??' : 'Total' ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resDash['subjects'] as $sr): ?>
                                    <tr>
                                        <td><?= esc($sr['subject_name'] ?? '') ?></td>
                                        <td class="text-center"><?= esc($sr['obtained_marks'] ?? '—') ?></td>
                                        <td class="text-center"><?= esc($sr['total_marks'] ?? '—') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <div class="text-center mt-3">
                    <a href="<?= esc($resultsUrl) ?>" class="btn btn-outline-primary btn-sm"><?= $isUrdu ? '???? ?????' : 'View all results' ?></a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
