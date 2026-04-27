<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<div class="table-responsive">
    <?php foreach ($organizedData as $sessionId => $sessionData) : ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Academic Session: <?= htmlspecialchars($sessionData['session_name']) ?></h4>
            </div>
            <div class="card-body">
                <?php foreach ($sessionData['terms'] as $termId => $termData) : ?>
                    <div class="mb-4">
                        <h5 class="mb-3">Term: <?= htmlspecialchars($termData['term_name']) ?></h5>
                        
                        <?php if ($termData['exam_result']) : ?>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0">Term Summary</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Total Marks:</strong> <?= $termData['exam_result']->exam_total_mark ?></p>
                                                    <p><strong>Obtained Marks:</strong> <?= $termData['exam_result']->obtain_total_mark ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Position:</strong> <?= $termData['exam_result']->position ?></p>
                                                    <p><strong>Remarks:</strong> <?= htmlspecialchars($termData['exam_result']->remark) ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else : ?>
                            <div class="alert alert-warning">No exam results found for this term.</div>
                        <?php endif; ?>
                        
                        <?php if (!empty($termData['subjects'])) : ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Subject</th>
                                            <th>Obtained Marks</th>
                                            <th>Grade</th>
                                            <th>Attendance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($termData['subjects'] as $subject) : ?>
                                            <tr>
                                                <td><?= htmlspecialchars($subject->subject_name) ?></td>
                                                <td><?= $subject->obtained_marks ?></td>
                                                <td><?= htmlspecialchars($subject->subject_grade) ?></td>
                                                <td><?= htmlspecialchars($subject->attendance) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else : ?>
                            <div class="alert alert-info">No subject results found for this term.</div>
                        <?php endif; ?>
                    </div>
                    <hr>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
    .card {
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .card-header {
        padding: 12px 20px;
    }
    .table {
        font-size: 14px;
        margin-bottom: 0;
    }
    .table th {
        background-color: #f8f9fa;
        vertical-align: middle;
    }
    .table td {
        vertical-align: middle;
    }
    .alert {
        margin: 15px 0;
    }
    hr {
        margin: 30px 0;
        border-top: 1px solid #eee;
    }
</style>

<?= $this->endSection() ?>