<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<?= view('components/page_header', [
    'title' => 'Sports Events',
    'icon' => 'fas fa-running',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Sports Events', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
          <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/sports/events') ?>">List</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/sports/events/add') ?>">Add Event</a></li>
          </ul>
        </div>
        <div class="card-body">
          <table id="events-dt" class="table table-striped table-bordered table-hover" width="100%">
            <thead>
              <tr>
                <th style="width:60px;">#</th>
                <th>Name</th>
                <th>Type</th>
                <th>Gender</th>
                <th style="width:120px;">Date</th>
                <th style="width:220px;">Actions</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
  const CSRF_NAME = '<?= csrf_token() ?>';
  const CSRF_HASH = '<?= csrf_hash() ?>';

  function genderBadge(g) {
    const v = (g || '').toLowerCase().trim();
    switch (v) {
        case 'male':
            return '<span class="badge text-bg-primary">Male</span>';
        case 'female':
            return '<span class="badge text-bg-danger">Female</span>';
        case 'mixed':
            return '<span class="badge text-bg-secondary">Mixed</span>';
        default:
            return '<span class="badge text-bg-light">-</span>';
    }
}

$(function(){
  const dt = $('#events-dt').DataTable({
    ajax: { 
        url: "<?= base_url('admin/sports/events/data') ?>", 
        type: "POST",
        data: function (d) {
            d[CSRF_NAME] = CSRF_HASH; 
        }
    },
    columns: [
      { data: null, render: (d,t,r,m)=> m.row+1 },

      { data: 'event_name' },

      { data: 'event_type', render: t => (t || '').charAt(0).toUpperCase() + (t || '').slice(1).toLowerCase() },

      // ✅ Show Male / Female / Mixed
      { data: 'gender', render: g => genderBadge(g) },

      { data: 'event_date' },

      { data: 'event_id', render: id => `
          <a class="btn btn-sm btn-primary" href="<?= base_url('admin/sports/events/edit') ?>/${id}"><i class="fas fa-edit"></i></a>
          <a class="btn btn-sm btn-info" href="<?= base_url('admin/sports/managers') ?>/${id}"><i class="fas fa-user-tie"></i></a>
          <a class="btn btn-sm btn-secondary" href="<?= base_url('admin/sports/entries') ?>/${id}"><i class="fas fa-user-plus"></i></a>
          <a class="btn btn-sm btn-success" href="<?= base_url('admin/sports/results') ?>/${id}"><i class="fas fa-trophy"></i></a>
      ` }
    ],
    paging:false, 
    info:false, 
    searching:false, 
    order:[[4,'asc']]
  });
});
</script>

<?= $this->endSection() ?>
