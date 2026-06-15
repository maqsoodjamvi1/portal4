<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => $title ?? 'Hifz Program',
    'icon' => 'fas fa-quran',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Hifz Program'],
        ['label' => $title ?? '', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="card sms-card card-outline card-info">
    <div class="card-body text-center py-5">
      <i class="fas fa-tools fa-3x text-muted mb-3"></i>
      <h4 class="text-muted"><?= esc($title ?? 'Coming soon') ?></h4>
      <p class="text-muted mb-4"><?= esc($description ?? '') ?></p>
      <a href="<?= base_url('admin/hifz/sections') ?>" class="btn btn-primary">
        <i class="fas fa-list"></i> Hifz Sections
      </a>
    </div>
  </div>
</section>

<?= $this->endSection() ?>
