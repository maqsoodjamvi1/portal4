<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- Include Bootstrap Toggle CSS -->
<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<?= view('components/page_header', [
    'title' => 'Manage Section',
    'icon' => 'fas fa-school',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Sections', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="card card-primary">
    <div class="card-header">
      <h3 class="card-title">Section List</h3>
      <div class="card-tools">
        <a href="<?= base_url('admin/sections/add') ?>" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Add Section</a>
      </div>
    </div>
    <div class="card-body">
      <table id="classes-datatable" class="table table-bordered table-hover">
        <thead>
          <tr>
            <th>#</th>
            <th>Section Name</th>
            <th>Short Name</th>
            <th>Section Code</th>
            
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
  const table = $('#classes-datatable').DataTable({
   paging: false,       // Disable pagination
  info: false,         // Disable "Showing x to y of z entries" text
  searching: false,    // Optional: disable search
    ajax: {
      url: "<?= base_url('admin/sections/data') ?>",
      type: "POST"
    },
   columns: [
  { data: 'sno', title: '#' },
  { data: 'section_name' },
  { data: 'short_name' },
  { data: 'section_id' },
  
  {
    data: 'status',
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

        $.post("<?= base_url('admin/sections/toggle-status') ?>", {
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
