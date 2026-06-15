<?= $this->extend('layouts/admin_template') ?>

<?= $this->section('content') ?>



<?= view('components/page_header', [

    'title' => 'Board Prep Quizzes',

    'icon' => 'fas fa-book-reader',

    'subtitle' => 'Create one or many chapter-wise quizzes for board-exam prep students.',

    'actionsHtml' => '<a href="' . esc(site_url('admin/quizzes'), 'attr') . '" class="btn btn-outline-secondary btn-sm">'

        . '<i class="fas fa-list"></i> All quizzes</a>',

    'breadcrumbs' => [

        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],

        ['label' => 'Quizzes', 'url' => base_url('admin/quizzes')],

        ['label' => 'Board Prep Quizzes', 'active' => true],

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



      <?= view('admin/quizzes/partials/board_prep_filters', get_defined_vars()) ?>

      <?= view('admin/quizzes/partials/board_prep_bulk_wizard', get_defined_vars()) ?>

      <?= view('admin/quizzes/partials/board_prep_bulk_form', get_defined_vars()) ?>

    </div>

  </div>

</section>



<style>

  #bpBulkWizard.bp-bulk-locked { opacity: 0.45; pointer-events: none; }

  #bpBulkWizard.bp-bulk-ready { opacity: 1; pointer-events: auto; }

  #bpChaptersTable td, #bpChaptersTable th { vertical-align: middle; }
  #bpChaptersTable .bp-ch-type-col { width: 3.25rem; font-size: 0.8rem; }
  .bp-type-counts-table th, .bp-type-counts-table td { vertical-align: middle; }
  .bp-type-counts-table .bp-pick-count { max-width: 5rem; margin: 0 auto; }

  .bp-status-ok { color: #28a745; }

  .bp-status-warn { color: #dc3545; }

</style>



<?= $this->endSection() ?>



<?= $this->section('pageScripts') ?>

<script>

window.BP_BULK_CONFIG = {

  csrfName: <?= json_encode(csrf_token()) ?>,

  csrfHash: <?= json_encode(csrf_hash()) ?>,

  subjectsUrl: <?= json_encode(base_url('admin/quizzes/ajax/board-prep-subjects')) ?>,

  topicsUrl: <?= json_encode(base_url('admin/quizzes/ajax/board-prep-topics')) ?>,

  storeUrl: <?= json_encode(base_url('admin/quizzes/store-board-prep-bulk')) ?>,

};

</script>

<script src="<?= base_url('assets/js/board_prep_bulk.js') ?>?v=5"></script>

<?= $this->endSection() ?>
