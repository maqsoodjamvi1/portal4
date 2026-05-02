<?php if (empty($exam_groups)): ?>
    <div class="alert alert-info mb-0">
        <i class="fas fa-info-circle mr-1"></i> No exam subject results are recorded for this student yet. Results appear here after marks are entered for exams.
    </div>
<?php else: ?>
    <?php foreach ($exam_groups as $eid => $pack): ?>
        <div class="card mb-3 border shadow-sm">
            <div class="card-header bg-light py-2 d-flex flex-wrap justify-content-between align-items-center">
                <span><i class="fas fa-file-alt text-primary mr-1"></i><strong><?= esc($pack['exam_name']) ?></strong></span>
                <?php if (!empty($pack['session_name'])): ?>
                    <span class="badge badge-secondary font-weight-normal"><?= esc($pack['session_name']) ?></span>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Subject</th>
                                <th class="text-right">Obtained</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pack['rows'] as $r): ?>
                                <tr>
                                    <td><?= esc($r->subject_name ?? '—') ?></td>
                                    <td class="text-right font-weight-bold"><?= esc($r->obtained_marks ?? '—') ?></td>
                                    <td class="text-right text-muted"><?= esc($r->total_marks ?? '—') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
