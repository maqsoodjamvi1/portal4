<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>
<?php $id = 0; ?>
<link rel="stylesheet" href="<?= base_url('resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>" />

<style>
@media print { .pagebreak { page-break-before: always; } }
th { text-align: center; }
.select2-container--default .select2-selection--single,
.select2-selection .select2-selection--single {
  border: 1px solid #d2d6de;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
  right: 3px;
}
.table-bordered th, .table-bordered td {
  border: 1px solid #000 !important;
  vertical-align: middle;
  font-weight: normal;
}
.leftdate { font-weight: normal; text-align: left; }
.rightdata { font-weight: normal; text-align: right; }
.form-group { margin-bottom: 0px; }
</style>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Pay Fee Chalan</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
          <li class="breadcrumb-item active">Pay Fee Chalan</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0"></div>
        <div class="card-body">
          <div class="tab-content">
            <?= form_open( base_url('/admin/fee-chalan-pay/save'), ['role' => 'form', 'id' => 'user-edit-form']) ?>
            <?= form_hidden('id', (string) $id); ?>
            <div class="row">
              <div class="form-group col-lg-4">
                <label>Date Paid:</label>
                <div class="input-group date" id="datepicker2" data-target-input="nearest">
                  <input type="text" id="datePaid" name="paid_date" autocomplete="off" class="form-control datetimepicker-input" data-target="#datepicker2" />
                  <div class="input-group-append" data-target="#datepicker2" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                  </div>
                </div>
              </div>
              <div class="col-lg-4">
                <label for="class">Class</label>
                <select class="form-control select2" name="cls_sec_id" id="cls_sec_id" required>
                  <option value="0">Select Section</option>
                  <?php if (isset($sectionsclassinfo)):
                    foreach ($sectionsclassinfo as $section): ?>
                      <option value="<?= $section['section_id'] ?>"><?= $section['sectionclassname'] ?></option>
                  <?php endforeach; endif; ?>
                </select>
              </div>
              <div class="form-group col-lg-4">
                <label>Reg No</label>
                <input type="text" class="form-control" name="reg_no" id="reg_no">
              </div>
            </div>

            <div class="row">
              <div class="form-group col-lg-4">
                <label>Student Name</label>
                <select class="form-control select2" name="student_id" id="student_id">
                  <option value="0">Select Student</option>
                </select>
              </div>
              <div class="form-group col-lg-4">
                <label>Parent Name</label>
                <select class="form-control select2" name="parent_id" id="parent_id">
                  <option value="0">Select Parent</option>
                </select>
              </div>
              <div class="form-group col-lg-4">
                <label>Family ID</label>
                <input type="text" class="form-control" name="family_id" id="family_id">
              </div>
            </div>
            <div id="feetypeinfo">
              <table style="width: 100%; margin-bottom: 30px;">
                <tbody id="tbody"></tbody>
              </table>
            </div>
            <?= form_close() ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="<?= base_url('resource/bootstrap-switch/js/bootstrap-switch.min.js') ?>"></script>
<script>
$(function () {
  $('#datepicker2').datetimepicker({ format: 'DD/MM/YYYY' });

  $('#student_id').select2({
    minimumInputLength: 2,
    ajax: {
      url: '<?php echo base_url('admin/fee-chalan-pay/get-student-info'); ?>',
      type: 'POST',
      dataType: 'json',
      delay: 250,
      data: function (params) {
        return { term: { term: params.term }, flag: $('#cls_sec_id').val() };
      },
      processResults: function (data) {
        return { results: data };
      }
    }
  });

  $('#parent_id').select2({
    minimumInputLength: 2,
    ajax: {
      url: '<?php echo base_url('admin/fee-chalan-pay/get-parent-info'); ?>',
      type: 'POST',
      dataType: 'json',
      delay: 250,
      data: function (params) {
        return { term: params.term };
      },
      processResults: function (data) {
        return { results: data };
      }
    }
  });

  $('#parent_id, #reg_no, #student_id').on('change keyup select2:select', function () {
    let data = {
      parent_id: $('#parent_id').val(),
      reg_no: $('#reg_no').val(),
      student_id: $('#student_id').val(),
    };
    $.post('<?php echo base_url('admin/fee-chalan-pay/get-students-list'); ?>', data, function (res) {
      $('#feetypeinfo').html(res || 'Record Not Found');
    });
  });
});
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>