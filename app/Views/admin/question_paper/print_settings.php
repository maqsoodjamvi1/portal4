<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
$paper = $paper ?? [];
$config = $config ?? [];
$header = $config['header'] ?? [];
$layout = $config['layout'] ?? [];
$sectionMarks = $config['section_marks'] ?? [];
?>

<?= view('components/page_header', [
    'title' => 'Print question paper',
    'icon' => 'fas fa-print',
    'subtitle' => esc($paper['title'] ?? 'Question paper'),
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Assessment Builder', 'url' => base_url('admin/assessment-builder')],
        ['label' => 'Print settings', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="row">
    <div class="col-lg-8">
      <div class="alert alert-info">
        <strong><?= (int) ($paper['questions_count'] ?? 0) ?></strong> questions saved.
        Set layout options below, then print or download.
      </div>

      <form action="<?= base_url('admin/question-paper/print-saved/' . (int) ($paper['id'] ?? 0)) ?>" method="post" target="_blank">
        <?= csrf_field() ?>

        <div class="card card-secondary mb-3">
          <div class="card-header"><h3 class="card-title mb-0">Paper header</h3></div>
          <div class="card-body">
            <p class="text-muted small mb-2">School name and logo come from system settings.</p>
            <div class="row">
              <div class="form-group col-md-12">
                <label for="paper_title">Paper title</label>
                <input type="text" class="form-control" id="paper_title" name="paper_title" value="<?= esc($header['title'] ?? $paper['title'] ?? '') ?>">
              </div>
              <div class="form-group col-md-4">
                <label for="paper_subject">Subject</label>
                <input type="text" class="form-control" id="paper_subject" name="paper_subject" value="<?= esc($header['subject'] ?? $paper['paper_subject'] ?? '') ?>">
              </div>
              <div class="form-group col-md-4">
                <label for="paper_class">Class</label>
                <input type="text" class="form-control" id="paper_class" name="paper_class" value="<?= esc($header['class_label'] ?? $paper['paper_class'] ?? '') ?>">
              </div>
              <div class="form-group col-md-4">
                <label>Total marks</label>
                <div class="form-control-plaintext border rounded px-2 py-1 bg-light"><?= esc($header['total_marks'] ?? '') ?></div>
              </div>
              <div class="form-group col-md-4">
                <label for="exam_date">Date</label>
                <input type="text" class="form-control" id="exam_date" name="exam_date" value="<?= esc($header['exam_date'] ?? '') ?>">
              </div>
              <div class="form-group col-md-4">
                <label for="exam_time">Time</label>
                <input type="text" class="form-control" id="exam_time" name="exam_time" value="<?= esc($header['exam_time'] ?? '') ?>">
              </div>
              <div class="form-group col-md-4">
                <label for="duration">Duration</label>
                <input type="text" class="form-control" id="duration" name="duration" value="<?= esc($header['duration'] ?? '') ?>">
              </div>
              <div class="form-group col-12">
                <label for="instructions">Instructions</label>
                <textarea class="form-control" id="instructions" name="instructions" rows="2"><?= esc($header['instructions'] ?? '') ?></textarea>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <label class="d-block small fw-bold">Student fields on paper</label>
                <label class="me-3"><input type="checkbox" name="show_name" value="1" <?= !empty($header['show_name']) ? 'checked' : '' ?>> Name</label>
                <label class="me-3"><input type="checkbox" name="show_roll" value="1" <?= !empty($header['show_roll']) ? 'checked' : '' ?>> Roll no</label>
                <label class="me-3"><input type="checkbox" name="show_section" value="1" <?= !empty($header['show_section']) ? 'checked' : '' ?>> Section</label>
              </div>
              <div class="col-md-6">
                <label for="paper_mode">Output</label>
                <select class="form-control" id="paper_mode" name="paper_mode">
                  <?php $pm = $layout['paper_mode'] ?? 'student'; ?>
                  <option value="student" <?= $pm === 'student' ? 'selected' : '' ?>>Student paper (no answers)</option>
                  <option value="key" <?= $pm === 'key' ? 'selected' : '' ?>>Answer key only</option>
                  <option value="both" <?= $pm === 'both' ? 'selected' : '' ?>>Student paper + answer key</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <div class="card card-secondary mb-3">
          <div class="card-header"><h3 class="card-title mb-0">Layout</h3></div>
          <div class="card-body">
            <div class="row">
              <div class="form-group col-md-3">
                <label for="columns">Columns</label>
                <select class="form-control" id="columns" name="columns">
                  <option value="1" <?= (int) ($layout['columns'] ?? 1) === 1 ? 'selected' : '' ?>>1 column</option>
                  <option value="2" <?= (int) ($layout['columns'] ?? 1) === 2 ? 'selected' : '' ?>>2 columns</option>
                </select>
              </div>
              <div class="form-group col-md-3">
                <label for="font_size">Font size</label>
                <select class="form-control" id="font_size" name="font_size">
                  <?php $fs = $layout['font_size'] ?? 'normal'; ?>
                  <option value="small" <?= $fs === 'small' ? 'selected' : '' ?>>Small</option>
                  <option value="normal" <?= $fs === 'normal' ? 'selected' : '' ?>>Normal</option>
                  <option value="large" <?= $fs === 'large' ? 'selected' : '' ?>>Large</option>
                </select>
              </div>
              <div class="form-group col-md-3">
                <label for="versions">Shuffled versions</label>
                <input type="number" class="form-control" id="versions" name="versions" min="1" max="4" value="<?= (int) ($layout['versions'] ?? 1) ?>">
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <?php
                $checks = [
                    'show_topics' => 'Show topic headings',
                    'mcq_inline' => 'MCQ options inline',
                    'page_break_topic' => 'Page break before each section',
                    'shuffle_questions' => 'Shuffle questions',
                    'shuffle_mcq_options' => 'Shuffle MCQ options',
                    'show_question_marks' => 'Show marks on each question',
                    'descriptive_answer_space' => 'Blank lines for descriptive answers',
                ];
                foreach ($checks as $key => $lbl):
                  $on = !empty($layout[$key]);
                ?>
                  <label class="me-3"><input type="checkbox" name="<?= esc($key) ?>" value="1" <?= $on ? 'checked' : '' ?>> <?= esc($lbl) ?></label>
                <?php endforeach; ?>
              </div>
            </div>
            <?php foreach (['mcq', 'mcq_multi', 'tf', 'fill', 'short', 'descriptive', 'match'] as $k): ?>
              <input type="hidden" name="marks_<?= $k ?>" value="<?= (int) ($sectionMarks[$k] ?? 0) ?>">
            <?php endforeach; ?>
          </div>
        </div>

        <div class="card">
          <div class="card-body d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary"><i class="fas fa-print"></i> Print paper</button>
            <a href="<?= base_url('admin/assessment-builder') ?>" class="btn btn-secondary">Back</a>
          </div>
        </div>
      </form>

      <form action="<?= base_url('admin/question-paper/print-saved-key/' . (int) ($paper['id'] ?? 0)) ?>" method="post" target="_blank" class="mt-2">
        <?= csrf_field() ?>
        <input type="hidden" name="paper_title" value="<?= esc($header['title'] ?? $paper['title'] ?? '', 'attr') ?>">
        <button type="submit" class="btn btn-outline-primary btn-sm"><i class="fas fa-key"></i> Print answer key only</button>
      </form>
    </div>
  </div>
</section>

<?= $this->endSection() ?>
