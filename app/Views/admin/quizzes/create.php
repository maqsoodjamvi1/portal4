<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
$qd = $quizDefaults ?? [];
$topicKeysForJs = [];
if (! empty($qd['topic_keys_json'])) {
    $decoded = json_decode((string) $qd['topic_keys_json'], true);
    if (is_array($decoded)) {
        $topicKeysForJs = array_values(array_filter(array_map('strval', $decoded)));
    }
}
$qbBoardPublishers = $qbBoardFilterPublishers ?? [];
?>

<?= view('components/page_header', [
    'title' => 'Create Quiz',
    'icon' => 'fas fa-gamepad',
    'subtitle' => 'Select topics from Question Bank, configure settings, and generate a quiz.',
    'actionsHtml' => '<a href="' . esc(site_url('admin/assessment-builder'), 'attr') . '" class="btn btn-outline-secondary btn-sm me-1">'
        . '<i class="fas fa-layer-group"></i> Quiz + Paper builder</a>'
        . '<a href="' . esc(site_url('admin/quizzes'), 'attr') . '" class="btn btn-outline-secondary btn-sm">'
        . '<i class="fas fa-list"></i> All quizzes</a>',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Quizzes', 'url' => base_url('admin/quizzes')],
        ['label' => 'Create', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="row">
    <div class="col-12">

      <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger py-2"><?= esc(session()->getFlashdata('error')) ?></div>
      <?php endif; ?>
      <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success py-2"><?= esc(session()->getFlashdata('success')) ?></div>
      <?php endif; ?>

      <?= view('admin/assessment_builder/partials/choose_questions', ['boardPublishers' => $qbBoardPublishers]) ?>

      <div class="card card-outline card-primary mb-3" id="abSavePanel">
        <div class="card-header py-2">
          <h3 class="card-title mb-0">2. Quiz details &amp; save</h3>
        </div>
        <div class="card-body pb-2">
          <?= view('admin/assessment_builder/partials/quiz_form', get_defined_vars()) ?>

          <div class="border-top pt-3 mt-2">
            <button type="button" class="btn btn-sm btn-outline-secondary mb-2" data-bs-toggle="collapse"
                    data-bs-target="#existingItemsCollapse" aria-expanded="false">
              <i class="fas fa-history me-1"></i> Previous quizzes
            </button>
            <div id="existingItemsCollapse" class="collapse">
              <div class="small fw-bold text-muted mb-1">Quizzes for this class &amp; subject</div>
              <div id="existingQuizzesWrap" class="ab-existing-wrap">
                <span class="text-muted small">Select term, class &amp; subject.</span>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<?= view('admin/assessment_builder/partials/builder_styles') ?>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
window.AB_BUILDER_MODE = 'quiz';
window.AB_SHARED_QB = true;
window.AB_ASSESSMENT_BUILDER = true;
window.qpPendingTopicKeys = <?= json_encode($topicKeysForJs) ?>;
window.QP_CONFIG = {
  csrfName: <?= json_encode(csrf_token()) ?>,
  csrfHash: <?= json_encode(csrf_hash()) ?>,
  summaryUrl: <?= json_encode(base_url('admin/quizzes/ajax/qb-summary')) ?>,
  summaryFallbackUrl: <?= json_encode(base_url('admin/quizzes/ajax/qb-summary')) ?>,
  questionsUrl: <?= json_encode(base_url('admin/quizzes/ajax/qb-questions')) ?>,
  papersByFiltersUrl: <?= json_encode(base_url('admin/question-paper/ajax/by-filters')) ?>,
  printSettingsUrl: <?= json_encode(base_url('admin/question-paper/print-settings')) ?>,
  printSavedUrl: <?= json_encode(base_url('admin/question-paper/print-saved')) ?>,
  storeUrl: <?= json_encode(base_url('admin/question-paper/store')) ?>,
};
</script>
<script src="<?= base_url('assets/js/question_paper_qb_browser.js') ?>?v=4"></script>
<script src="<?= base_url('assets/js/question_paper_builder.js') ?>?v=19"></script>
<script src="<?= base_url('assets/js/assessment_builder.js') ?>?v=4"></script>
<?= view('admin/assessment_builder/partials/quiz_scripts', get_defined_vars()) ?>
<?= $this->endSection() ?>
