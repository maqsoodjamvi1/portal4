<html dir="rtl" lang="ur">
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
.feeinfo { font-size: 13px; border: 1px solid #000000; border-bottom: none; margin: 3px; float: left; width: 98%; line-height: 25px; }
.chalancolleft, .chalancolright { border-bottom: 1px solid #000000; width: 50%; float: left; padding-left: 10px; text-align: left; }
.feetable { margin: 3px; line-height: 25px; text-align: left; padding-left: 10px; font-size: 13px; }
</style>
<?php 
$footer_line1 = $_GET['footer_line1'] ?? '';
$show_line1   = $_GET['show_line1'] ?? '';
$footer_line2 = $_GET['footer_line2'] ?? '';
$show_line2   = $_GET['show_line2'] ?? '';
?>
<?= view('components/page_header', [
    'title' => 'Hostel Fee Chalan',
    'icon' => 'fas fa-bed',
    'actionsHtml' => '<div class="text-sm-right">'
        . '<a href="' . esc(base_url('admin/fee-chalan/pdf'), 'attr') . '" class="btn btn-primary btn-sm">Print Individual Chalan</a></div>',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Print Fee Chalan', 'url' => base_url('admin/print-fee-chalan')],
        ['label' => 'Hostel', 'active' => true],
    ],
]) ?>
<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <form action="<?= base_url('admin/fee-chalan/hostel') ?>" method="get" id="user-edit-form">
        <div class="row">
          <div class="col-lg-4 form-group">
            <label>Footer Lines 1:</label>
            <input type="text" class="form-control" name="footer_line1" value="<?= esc($footer_line1) ?>">
          </div>
          <div class="col-lg-4 form-group">
            <label>Footer Lines 2:</label>
            <input type="text" class="form-control" name="footer_line2" value="<?= esc($footer_line2) ?>">
          </div>
          <div class="col-lg-2 form-group">
            <label>Show Footer Line 1:</label>
            <input type="checkbox" class="form-control" name="show_line1" value="1" <?= $show_line1 == 1 ? 'checked' : '' ?> />
          </div>
          <div class="col-lg-2 form-group">
            <label>Show Footer Line 2:</label>
            <input type="checkbox" class="form-control" name="show_line2" value="1" <?= $show_line2 == 1 ? 'checked' : '' ?> />
          </div>
          <div class="col-sm-2">
            <input class="btn btn-primary" type="submit" value="View" style="margin-bottom: 25px;">
          </div>
        </div>
      </form>
      <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
          <div class="card-body">
            <?= view('admin/partials/hostel_chalan_view', [
              'data' => $data,
              'footer_line1' => $footer_line1,
              'show_line1' => $show_line1,
              'footer_line2' => $footer_line2,
              'show_line2' => $show_line2
            ]) ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script src="<?= base_url('resource/bootstrap-switch/js/bootstrap-switch.min.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/0.9.0rc1/jspdf.min.js"></script>
<?= $this->endSection() ?>
