<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>" />

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Admission Enquiry</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Admission Enquiry</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
          <ul class="nav nav-tabs">
            <li class="nav-item">
              <a class="nav-link active" href="<?= base_url('admin/admission-enquiry') ?>">Admission Enquiry</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url('admin/admission-enquiry/add') ?>">Add Admission Enquiry</a>
            </li>
          </ul>
        </div>

        <div class="card-body">
          <div class="col-lg-12">
            <table class="table table-striped table-bordered table-hover" id="users-datatable" width="100%">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Student Name</th>
                  <th>Father Name</th>
                  <th>Father Phone</th>
                  <th>Mother Phone</th>
                  <th>Address</th>
                  <th>Description</th>
                  <th>Date</th>
                  <th>Operation</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="<?= base_url('resource/bootstrap-switch/js/bootstrap-switch.min.js') ?>"></script>

<script>
$(function() {
  var table = $('#users-datatable').DataTable({
    deferRender: true,
    select: {
      style: 'single',
      blurable: true
    },
    ajax: {
      url: '<?= base_url('admin/admission-enquiry/data') ?>',
      type: 'post',
      data: function(d) {
        // CSRF token handling if enabled
      }
    },
    columns: [
      {
        data: 'id',
        className: 'select-checkbox',
        render: function(data) {
          return data;
        }
      },
      { data: 'name' },
      { data: 'father_name' },
      { data: 'contact' },
      { data: 'mother_phone' },
      { data: 'address' },
      { data: 'description' },
      { data: 'date' },
      {
        data: 'id',
        sortable: false,
        render: function(data) {
          return `
            <div class="btn-group">
              <a href="<?= base_url('admin/admission-enquiry/edit') ?>?id=${data}" title="Edit" class="btn btn-default btn-xs">
                <i class="far fa-edit"></i>
              </a>
            </div>`;
        }
      }
    ],
    fnDrawCallback: function() {
      $(".switchchk").bootstrapSwitch({
        onSwitchChange: function(e, state) {
          var $element = $(e.currentTarget);
          var fieldval = state ? 1 : 0;
          $.post(
            '<?= base_url('admin/ajax/setboolattribute') ?>',
            {
              act: 'upsort',
              tbname: $element.data('table'),
              tbfield: $element.data('field'),
              tbfieldvalue: fieldval,
              id: $element.data('pk')
            },
            function(data) {
              if (data === 'success') {
                toastr.success('Change success');
              } else {
                toastr.error('Change error');
              }
            }
          );
        }
      });
    }
  });
});
</script>

<?= $this->endSection() ?>
