<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>" />
<style>
@media print {
  .pagebreak { page-break-before: always; }
  #user-edit-form { display: none; }
}
.chalanwrapper { border: 1px solid #000000; text-align: center; float: left; width: 100%; font-size: 15px; line-height: 22px; }
th, td { text-align: left; padding-left: 10px; }
.chalanrows { border-bottom: 1px solid #000000; padding-left: 10px; }
.feeinfo { font-size: 12px; border: 1px solid #000000; border-bottom: none; margin: 3px; float: left; width: 98%; line-height: 25px; }
.chalancolleft, .chalancolright { border-bottom: 1px solid #000000; width: 50%; float: left; padding-left: 10px; text-align: left; }
.feetable { margin: 3px; line-height: 25px; text-align: left; padding-left: 10px; font-size: 13px; }
</style>

<?php
$request = \Config\Services::request();
$cls_sec_id    = $request->getGet('cls_sec_id') ?? '';
$fee_month     = $request->getGet('fee_month') ?? '';
$footer_line1  = $request->getGet('footer_line1') ?? '';
$show_line1    = $request->getGet('show_line1') ?? '';
$footer_line2  = $request->getGet('footer_line2') ?? '';
$show_line2    = $request->getGet('show_line2') ?? '';
?>

<?= view('components/page_header', [
    'title' => 'Fee Chalan Single Copy',
    'icon' => 'fas fa-copy',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Print Fee Chalan', 'url' => base_url('admin/print-fee-chalan')],
        ['label' => 'Single Copy', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <form action="<?= current_url() ?>" method="get" id="user-edit-form" accept-charset="utf-8">
        <div class="row">
          <div class="col-lg-4 form-group">
            <label>Fee Month:</label>
            <input type="month" class="form-control" name="fee_month" value="<?= esc($fee_month) ?>">
          </div>
          <div class="col-lg-5 form-group">
            <label><strong>Class</strong></label>
            <select class="form-control" name="cls_sec_id">
              <option value="">All Classes</option>
              <?php foreach ($sectionsclassinfo ?? [] as $sectionvalue): ?>
                <option value="<?= esc($sectionvalue['section_id']) ?>" <?= $cls_sec_id == $sectionvalue['section_id'] ? 'selected' : '' ?>>
                  <?= esc($sectionvalue['sectionclassname']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-lg-4 form-group">
            <label>Footer Line 1:</label>
            <input type="text" class="form-control" name="footer_line1" value="<?= esc($footer_line1) ?>">
          </div>
          <div class="col-lg-4 form-group">
            <label>Footer Line 2:</label>
            <input type="text" class="form-control" name="footer_line2" value="<?= esc($footer_line2) ?>">
          </div>
          <div class="col-lg-2 form-group">
            <label>Show Line 1:</label>
            <input type="checkbox" class="form-control" name="show_line1" value="1" <?= $show_line1 == 1 ? 'checked' : '' ?>>
          </div>
          <div class="col-lg-2 form-group">
            <label>Show Line 2:</label>
            <input type="checkbox" class="form-control" name="show_line2" value="1" <?= $show_line2 == 1 ? 'checked' : '' ?>>
          </div>
          <div class="col-sm-2">
            <input class="btn btn-primary" type="submit" value="View" style="margin-bottom: 25px;">
          </div>
        </div>
      </form>

      <div class="card card-primary card-outline card-tabs">
        <div class="card-body">
          <div class="tab-content pagebreak table-responsive no-padding">
            <div style="margin-bottom: 20px; float: left; width: 100%;" id="printarea">
              <?php if (!empty($data)): ?>
                <?php foreach ($data as $key => $student_info): ?>
                  <?= view('admin/partials/fee_chalan_single', compact('student_info', 'show_line1', 'footer_line1', 'show_line2', 'footer_line2')) ?>
                  <?php if (($key + 1) % 3 === 0): ?><div style="clear:both;page-break-after: always;"></div><?php endif; ?>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/0.9.0rc1/jspdf.min.js"></script>
<?= $this->endSection() ?>
