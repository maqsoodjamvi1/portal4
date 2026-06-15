<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>" />

<?= view('components/page_header', [
    'title' => 'Print Fee Chalan',
    'icon' => 'fas fa-file-invoice-dollar',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Fee Chalan', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card sms-card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
          <ul class="nav nav-tabs">
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url('admin/fee-chalan') ?>">Fee Entries</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url('admin/fee-chalan/thermal-copy') ?>">Print Chalan Thermal Copy</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url('admin/fee-chalan/single-copy') ?>">Single Copy Print Chalan</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url('admin/fee-chalan/pdf') ?>">Three Copy Print Chalan</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url('admin/fee-chalan/without-discount') ?>">Print Chalan Without Discount</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url('admin/fee-chalan/familywise') ?>">Family Wise Print Chalan</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url('admin/fee-chalan/familywise/single-copy') ?>">Single Copy Family Wise Print Chalan</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url('admin/fee-chalan/with-header') ?>">Fee Chalan with Header</a>
            </li>
          </ul>
          <div class="card-body">
            <div class="col-lg-12">
              <table class="table table-striped table-bordered table-hover" id="users-datatable" width="100%">
                <thead>
                  <tr>
                    <th nowrap>Student</th>
                    <th nowrap>Fee Type</th>
                    <th nowrap>Fee Month</th>
                    <th nowrap>Amount</th>
                    <th nowrap>Status</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="<?= base_url('resource/bootstrap-switch/js/bootstrap-switch.min.js') ?>"></script>
<script type="text/javascript">
$(function() {
  $('#users-datatable thead tr').clone(true).appendTo('#users-datatable thead');
  $('#users-datatable thead tr:eq(1) th').each(function(i) {
    var title = $(this).text();
    $(this).html('<input type="text" style="width:90px" placeholder=" ' + title + '" />');
    $('input', this).on('keyup change', function() {
      if (table.column(i).search() !== this.value) {
        table.column(i).search(this.value).draw();
      }
    });
  });

  var table = $('#users-datatable').DataTable({
    deferRender: true,
    searching: true,
    select: {
      style: 'single',
      blurable: true
    },
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= base_url('admin/fee-chalan/data') ?>',
      type: 'POST'
    },
    columns: [
      {
        data: 'student_name',
        render: function(data, type, row) {
          return data + ' <br>' + row['reg_no'];
        }
      },
      { data: 'fee_name' },
      { data: 'fee_month' },
      { data: 'amount' },
      { data: 'status' }
    ],
    fnDrawCallback: function(oSettings) {
      $(".switchchk").bootstrapSwitch({
        onSwitchChange: function(e, state) {
          var fieldval = state ? 1 : 0;
          var $element = $(e.currentTarget);
          var tablename = $element.attr('data-table');
          var fieldname = $element.attr('data-field');
          var rowid = $element.attr('data-pk');

          $.post("<?= base_url('admin/ajax/set-bool-attribute') ?>", {
            act: 'upsort',
            tbname: tablename,
            tbfield: fieldname,
            tbfieldvalue: fieldval,
            id: rowid
          }, function(data) {
            if (data == 'success') {
              toastr.success('change success');
            } else {
              toastr.error('change error');
            }
          });
        }
      });
    }
  });
});
</script>

<?= $this->endSection() ?>