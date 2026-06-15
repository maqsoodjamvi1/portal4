<?= $this->include('layouts/header') ?>

<?= $this->renderSection('pageStyles') ?>

<div class="content-wrapper">
  <?php if ($this->renderSection('content_header')): ?>
    <section class="content-header">
      <div class="container-fluid">
        <?= $this->renderSection('content_header') ?>
      </div>
    </section>
  <?php endif; ?>

  <section class="content">
    <div class="container-fluid pt-3">
      <?= $this->renderSection('content') ?>
    </div>
  </section>
</div>

<?= $this->renderSection('modals') ?>
<?= $this->renderSection('pageScripts') ?>

<?= view('layouts/partials/legacy_sammy_shim') ?>
<script type="text/javascript" src="<?= base_url('resource/js/server.js?v=20260610') ?>"></script>

<?= $this->include('layouts/footer') ?>
