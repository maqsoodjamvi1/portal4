<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- Include Bootstrap Toggle CSS -->
<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><i class="fas fa-receipt"></i> Manage Fee Types</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Fee Type</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="card card-primary">
    <div class="card-header">
      <h3 class="card-title">Fee Type List</h3>
      <div class="card-tools">
        <a href="<?= base_url('admin/fee_type/add') ?>" class="btn btn-success btn-sm">
          <i class="fas fa-plus"></i> Add Fee Type
        </a>
      </div>
    </div>
    <div class="card-body">
      <table id="fee-type-datatable" class="table table-bordered table-hover">
        <thead>
          <tr>
            <th>#</th>
            <th>Fee Type Name</th>
            <th>Monthly</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</section>

<!-- Bootstrap Toggle JS -->
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

<script>
$(function () {
  const table = $('#fee-type-datatable').DataTable({
  paging: false,
  info: false,
  searching: false,
  ajax: {
    url: "<?= base_url('admin/fee_type/data') ?>",
    type: "POST",
    dataSrc: "data"
  },
  columns: [
    { data: 'sno', title: '#' },
    { data: 'fee_type_name' },
    {
      data: 'is_monthly_fee',
      render: function (data, type, row) {
        const checked = data == 1 ? 'checked' : '';
        return `<input type="radio" name="monthly_fee" class="set-monthly-fee" data-id="${row.id}" ${checked}>`;
      }
    },
    {
      data: 'status',
      render: function (data, type, row) {
        const checked = data == 1 ? 'checked' : '';
        return `<input type="checkbox" class="toggle-status" data-id="${row.id}" ${checked}
                data-toggle="toggle" data-size="sm" data-on="Active" data-off="Inactive"
                data-onstyle="success" data-offstyle="danger">`;
      }
    }
  ],
  drawCallback: function () {
    $('.toggle-status').bootstrapToggle();

    // Toggle status handler
    $('.toggle-status').off().on('change', function () {
      const rowId = $(this).data('id');
      const newStatus = $(this).prop('checked') ? 1 : 0;
$.ajax({
  url: "<?= site_url('admin/fee_type/toggle-status') ?>",
  type: "POST",
  dataType: "json",
  data: {
    fee_type_id: rowId,
    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
  },
  success: function (res) {
    if (res.success) {
      toastr.success(res.msg || 'Status updated');
      if (typeof res.status !== 'undefined') {
        const $row = $('#row-' + rowId);
        $row.find('.status-badge')
            .toggleClass('badge-success', res.status == 1)
            .toggleClass('badge-secondary', res.status == 0)
            .text(res.status == 1 ? 'Active' : 'Inactive');
      }
    } else {
      toastr.error(res.msg || 'Failed to update status.');
    }
  },
  error: function () {
    toastr.error('An error occurred while updating status.');
  }
});
    });

    // Set Monthly Fee Handler
    $('.set-monthly-fee').off().on('change', function () {
      const feeTypeId = $(this).data('id');

      $.ajax({
        url: "<?= base_url('admin/fee_type/set-monthly-fee') ?>",
        type: "POST",
        dataType: "json",
        data: {
          id: feeTypeId,
          '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        success: function (res) {
          if (res.success) {
            toastr.success(res.msg);
            table.ajax.reload(null, false); // Refresh only data
          } else {
            toastr.error(res.msg);
          }
        },
        error: function () {
          toastr.error('An error occurred while setting monthly fee type.');
        }
      });
    });
  }
});
    
});
</script>

<?= $this->endSection() ?>
