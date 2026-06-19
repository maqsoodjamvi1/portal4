<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php ob_start(); ?>
<a href="<?= esc(base_url('admin/campus/add'), 'attr') ?>" class="btn btn-primary btn-sm">
    <i class="fas fa-plus me-1"></i> Add Campus
</a>
<?php $headerActions = trim(ob_get_clean()); ?>

<?= view('components/page_header', [
    'title' => 'Campus Management',
    'subtitle' => 'Oversee campus records, branch contact details, and billing shortcuts from one directory.',
    'icon' => 'fas fa-school',
    'actionsHtml' => $headerActions,
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Campus', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="card sms-card sms-index-card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-building me-2"></i>
                Campus Directory
            </h3>
            <div class="card-tools">
                <span class="badge bg-primary">Branch operations</span>
            </div>
        </div>
        <div class="card-body">
            <div class="sms-section-note mb-3">
                <i class="fas fa-info-circle"></i>
                Review branch contact details, locations, and billing actions without leaving the admin workspace.
            </div>

            <table id="campus-datatable" class="table table-bordered table-hover" data-sms-table-name="campuses">
                <thead>
                    <tr>
                        <th width="60">#</th>
                        <th>Campus Name</th>
                        <th>Short Name</th>
                        <th>Landline</th>
                        <th>Mobile</th>
                        <th>Location</th>
                        <th width="190">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</section>

<script>
$(function () {
    $('#campus-datatable').DataTable({
        responsive: true,
        autoWidth: false,
        searchDelay: 350,
        order: [[1, 'asc']],
        ajax: {
            url: "<?= base_url('admin/campus/data') ?>",
            type: "POST"
        },
        columns: [
            {
                data: 'id',
                className: 'text-center align-middle',
                render: function(data, type, row, meta) {
                    return meta.settings._iDisplayStart + meta.row + 1;
                }
            },
            { data: 'campus_name', className: 'align-middle' },
            { data: 'short_name', className: 'align-middle' },
            { data: 'landline', className: 'align-middle' },
            { data: 'mobile_no', className: 'align-middle' },
            { data: 'location', className: 'align-middle' },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center align-middle',
                render: function(data, type, row) {
                    let buttons = `<a href="<?= base_url('admin/campus/edit?id=') ?>${row.id}" class="btn btn-outline-primary btn-sm me-1">
                                      <i class="fas fa-edit me-1"></i> Edit
                                   </a>`;

                    if (row.bill_id) {
                        buttons += `<a href="#/campus_bill?id=${row.bill_id}" class="btn btn-primary btn-sm">
                                       <i class="fas fa-file-invoice me-1"></i> Bill
                                    </a>`;
                    }

                    return buttons;
                }
            }
        ]
    });
});
</script>

<?= $this->endSection() ?>
