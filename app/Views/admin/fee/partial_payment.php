<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><i class="fas fa-wallet mr-1"></i> Partial Fee Payment</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Partial Payment</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="row">
    <div class="col-12">
      <div class="card card-primary card-outline card-tabs">
        <div class="card-body">
          <?= form_open(base_url('admin/fee-chalan-pay/process-payment'), ['id' => 'partial-payment-form']) ?>
          <div class="form-row">
            <div class="form-group col-md-3">
              <label for="datePaid">Date Paid</label>
              <div class="input-group date" id="datepicker" data-target-input="nearest">
                <input type="text" name="paid_date" id="datePaid" class="form-control datetimepicker-input" data-target="#datepicker" autocomplete="off" required />
                <div class="input-group-append" data-target="#datepicker" data-toggle="datetimepicker">
                  <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                </div>
              </div>
            </div>

            <div class="form-group col-md-3">
              <label for="cls_sec_id">Class Section</label>
              <select class="form-control select2" id="cls_sec_id" name="cls_sec_id" required>
                <option value="">Select Section</option>
                <?php if (isset($sectionsclassinfo)): ?>
                  <?php foreach ($sectionsclassinfo as $section): ?>
                    <option value="<?= esc($section['section_id']) ?>"><?= esc($section['sectionclassname']) ?></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>

            <div class="form-group col-md-3">
              <label for="reg_no">Reg No</label>
              <input type="text" class="form-control" name="reg_no" id="reg_no" placeholder="Enter Reg No">
            </div>

            <div class="form-group col-md-3">
              <label for="student_id">Student</label>
              <select class="form-control select2" name="student_id" id="student_id">
                <option value="0">Select Student</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="parent_id">Parent</label>
              <select class="form-control select2" name="parent_id" id="parent_id">
                <option value="0">Select Parent</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="family_id">Family ID</label>
              <input type="text" class="form-control" name="family_id" id="family_id" placeholder="Enter Family ID">
            </div>
          </div>

          <div id="feetypeinfo">
            <table class="table table-bordered table-hover mt-4">
              <thead class="thead-dark">
                <tr>
                  <th>#</th>
                  <th>Fee Type</th>
                  <th>Due Month</th>
                  <th>Amount</th>
                  <th>Paid</th>
                  <th>Balance</th>
                  <th>Pay Now</th>
                </tr>
              </thead>
              <tbody id="tbody">
                <!-- Fee rows will be populated via AJAX -->
              </tbody>
            </table>
          </div>

         
          <?= form_close() ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- JS + Initialization -->
<script src="<?= base_url('resource/bootstrap-switch/js/bootstrap-switch.min.js') ?>"></script>
<script>
$(function () {
  $('#datepicker').datetimepicker({ format: 'DD/MM/YYYY' });

  $('#student_id').select2({
    minimumInputLength: 2,
    ajax: {
      url: '<?= base_url('admin/fee-chalan-pay/get-student-info') ?>',
      type: 'POST',
      dataType: 'json',
      delay: 250,
      data: params => ({ term: { term: params.term }, flag: $('#cls_sec_id').val() }),
      processResults: data => ({ results: data })
    }
  });

  $('#parent_id').select2({
    minimumInputLength: 2,
    ajax: {
      url: '<?= base_url('admin/fee-chalan-pay/get-parent-info') ?>',
      type: 'POST',
      dataType: 'json',
      delay: 250,
      data: params => ({ term: params.term }),
      processResults: data => ({ results: data })
    }
  });

  $('#parent_id, #reg_no, #student_id').on('change keyup select2:select', function () {
    let data = {
      parent_id: $('#parent_id').val(),
      reg_no: $('#reg_no').val(),
      student_id: $('#student_id').val()
    };

    $.post('<?= base_url('admin/fee-chalan-pay/get-students-list') ?>', data, function (res) {
      $('#feetypeinfo').html(res || '<tr><td colspan="7" class="text-center text-danger">No unpaid fee found.</td></tr>');
    });
  });
});
</script>

<?= $this->endSection() ?>
