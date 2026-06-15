<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Grades & Policy',
    'icon' => 'fas fa-graduation-cap',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Grades', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card sms-card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0 d-flex align-items-center justify-content-between flex-wrap">
          <ul class="nav nav-tabs border-0">
            <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/grades') ?>">Overview</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/grades/setup') ?>">Manage grades &amp; policy</a></li>
          </ul>
          <a href="<?= base_url('admin/grades/setup') ?>" class="btn btn-primary btn-sm m-2">
            <i class="fas fa-edit"></i> Edit all
          </a>
        </div>
        <div class="card-body">
          <table class="table table-striped table-bordered table-hover" id="classes-datatable" width="100%">
            <thead>
              <tr>
                <th>#</th>
                <th>Grade</th>
                <th>Detail</th>
                <th>% From</th>
                <th>% To</th>
                <th>Fail</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>

<script type="text/javascript">
$(function () {
  $('#classes-datatable').DataTable({
    deferRender: true,
    ajax: {
      url: '<?= base_url('admin/grades/data') ?>',
      type: 'post'
    },
    columns: [
      { data: 'id' },
      { data: 'name' },
      { data: 'detail' },
      { data: 'mark_from' },
      { data: 'mark_to' },
      { data: 'is_fail' }
    ],
    order: [[3, 'desc']]
  });
});
</script>

<?= $this->endSection() ?>
