<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- Bootstrap Toggle CSS -->
<!-- <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet"> -->
<?= view('components/page_header', [
    'title' => 'Manage Terms',
    'icon' => 'fas fa-calendar-alt',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Terms', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="card card-primary">
    <div class="card-header">
      <h3 class="card-title">Term List</h3>
      <div class="card-tools">
        <a href="<?= base_url('admin/terms/add') ?>" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Add New</a>
      </div>
    </div>
    <div class="card-body">
      <table id="terms-datatable" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Term Name</th>
            <th>Short Name</th>
            <th>Term Code</th>
            <th>Status</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</section>

<!-- Bootstrap Toggle JS -->
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

<script>
$(function () {
  const table = $('#terms-datatable').DataTable({
    paging: false,
    info: false,
    searching: false,
    ajax: {
      url: "<?= base_url('admin/terms/data') ?>",
      type: "POST"
    },
    columns: [
      { data: 'id', title: '#' },
      { data: 'name', title: 'Term Name' },
      { data: 'short_name', title: 'Short Name' },
      { data: 'id', title: 'Term ID' }, 
      {
        data: 'status',
        title: 'Status',
        render: function (data, type, row) {
          let checked = data == 1 ? 'checked' : '';
          return `<input type="checkbox" class="toggle-status" data-id="${row.id}" ${checked}
                    data-bs-toggle="toggle" data-size="sm" data-on="Active" data-off="Inactive"
                    data-onstyle="success" data-offstyle="danger">`;
        }
      }
    ],
    drawCallback: function () {
      $('.toggle-status').bootstrapToggle();

      $('.toggle-status').off().on('change', function () {
        const rowId = $(this).data('id');
        const newStatus = $(this).prop('checked') ? 1 : 0;

        $.post("<?= base_url('admin/terms/toggle-status') ?>", {
          id: rowId,
          status: newStatus,
          '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        }, function (res) {
          if (res.success) {
            toastr.success(res.msg);
          } else {
            toastr.error(res.msg);
          }
        }, 'json');
      });
    }
  });
});
</script>

<?= $this->endSection() ?>
