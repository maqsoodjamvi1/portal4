<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
    <h1>Question Bank</h1>
</section>

<section class="content">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">All Questions</h3>
            <a href="<?= site_url('admin/question-bank') ?>" class="btn btn-primary btn-sm">+ Add Question</a>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-bordered mb-0">
                <thead>
                    <tr>
                        <th style="width:60px;">ID</th>
                        <th>Class / Subject / Topic</th>
                        <th>Question</th>
                        <th>Type</th>
                        <th style="width:240px;">Answer / Options</th>
                        <th style="width:100px;">Difficulty</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($questions)): ?>
                    <?php foreach ($questions as $q): ?>
                        <tr>
                            <td><?= $q->id ?></td>
                            <td>
                                <?= esc($q->class_name ?? $q->class_id) ?><br>
                                <?= esc($q->subject_name ?? $q->subject_id) ?><br>
                                <small><?= esc($q->topic_name ?? $q->topic_id) ?></small>
                            </td>
                            <td><?= esc($q->question) ?></td>
                            <td><span class="badge badge-info"><?= strtoupper($q->question_type) ?></span></td>
                            <td>
                                <?php if ($q->question_type === 'mcq'): ?>
                                    A) <?= esc($q->option_a) ?><br>
                                    B) <?= esc($q->option_b) ?><br>
                                    C) <?= esc($q->option_c) ?><br>
                                    D) <?= esc($q->option_d) ?><br>
                                    <strong>Correct:</strong> <?= esc($q->correct_option) ?>
                                <?php elseif (in_array($q->question_type, ['tf', 'short', 'fill'])): ?>
                                    <strong>Answer:</strong> <?= esc($q->answer_text) ?>
                                <?php elseif ($q->question_type === 'match'): ?>
                                    <?php $pairs = json_decode($q->options_json ?? '[]', true); ?>
                                    <?php if ($pairs): ?>
                                        <?php foreach ($pairs as $p): ?>
                                            <?= esc($p['left'] ?? '') ?> ? <?= esc($p['right'] ?? '') ?><br>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td><?= esc(ucfirst($q->difficulty)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center p-3">No questions found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
