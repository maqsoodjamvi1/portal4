<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
$id         = (int) ($info->hifz_sec_id ?? 0);
$name       = $info->section_name ?? '';
$sortOrder  = (int) ($info->sort_order ?? 0);
$status     = (int) ($info->status ?? 1);
$isEdit     = $id > 0;
?>

<?= view('components/page_header', [
    'title' => $title ?? (($isEdit ? 'Edit' : 'Add') . ' Hifz Section'),
    'icon' => 'fas fa-quran',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Hifz Sections', 'url' => base_url('admin/hifz/sections')],
        ['label' => $isEdit ? 'Edit' : 'Add', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="card card-primary card-outline">
    <div class="card-body">
      <form id="hifz-section-form">
        <?= csrf_field() ?>
        <input type="hidden" name="hifz_sec_id" value="<?= $id ?>">

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Section Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="section_name" value="<?= esc($name) ?>" maxlength="100" required placeholder="e.g. Green Section">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Sort Order</label>
              <input type="number" class="form-control" name="sort_order" value="<?= $sortOrder ?>" min="0" max="9999">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Status</label><br>
              <input type="checkbox" name="status" value="1" <?= $status ? 'checked' : '' ?>
                     data-bs-toggle="toggle" data-on="Active" data-off="Inactive" data-onstyle="success" data-offstyle="danger">
            </div>
          </div>
        </div>

        <div class="mt-3">
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
          <a href="<?= base_url('admin/hifz/sections') ?>" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</section>

<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
<script>
$(function () {
  $('input[name="status"]').bootstrapToggle();

  $('#hifz-section-form').on('submit', function (e) {
    e.preventDefault();
    const $btn = $(this).find('[type="submit"]').prop('disabled', true);
    $.post('<?= base_url('admin/hifz/sections/save') ?>', $(this).serialize())
      .done(function (res) {
        if (res.success) {
          toastr.success(res.msg);
          setTimeout(function () { window.location.href = '<?= base_url('admin/hifz/sections') ?>'; }, 600);
        } else {
          toastr.error(res.msg || 'Save failed');
          $btn.prop('disabled', false);
        }
      })
      .fail(function () {
        toastr.error('Request failed');
        $btn.prop('disabled', false);
      });
  });
});
</script>

<?= $this->endSection() ?>
