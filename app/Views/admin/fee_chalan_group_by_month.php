<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><i class="fas fa-calendar-alt mr-2"></i> Fee Dues - Grouped by Month</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Group by Month</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="card card-primary card-outline">
    <div class="card-header">
      <form id="groupByMonthForm" class="form-inline">
        <div class="form-group mr-3">
          <label for="parent_id" class="mr-2">Select Parent</label>
          <select class="form-control select2" name="parent_id" id="parent_id" style="width: 300px;">
            <option value="">-- Select Parent --</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary">Fetch Records</button>
      </form>
    </div>

    <div class="card-body">
      <div id="monthGroupedResults" class="table-responsive"></div>
    </div>
  </div>
</section>

<script>
$(function () {
  $('.select2').select2();

  $('#parent_id').select2({
    minimumInputLength: 2,
    ajax: {
      url: '<?= base_url('admin/fee-chalan-pay/get-parent-info') ?>',
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

  $('#groupByMonthForm').on('submit', function (e) {
    e.preventDefault();
    let parentId = $('#parent_id').val();
    if (!parentId) {
      alert('Please select a parent.');
      return;
    }

    $.post('<?= base_url('admin/fee-chalan-pay/get-group-by-month') ?>', { parent_id: parentId }, function (res) {
      $('#monthGroupedResults').html(res || '<div class="alert alert-info">No unpaid records found.</div>');
    });
  });
});
</script>

<?= $this->endSection() ?>
