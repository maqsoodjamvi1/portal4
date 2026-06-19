<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php ob_start(); ?>
<a href="<?= esc(base_url('admin/classes/add'), 'attr') ?>" class="btn btn-primary btn-sm">
    <i class="fas fa-plus me-1"></i> Add Class
</a>
<?php $headerActions = trim(ob_get_clean()); ?>

<?= view('components/page_header', [
    'title' => 'Class Management',
    'subtitle' => 'Organize academic classes, monitor strength, and control active availability.',
    'icon' => 'fas fa-school',
    'actionsHtml' => $headerActions,
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Classes', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="card sms-card sms-index-card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-layer-group me-2"></i>
                Academic Classes
            </h3>
            <div class="card-tools">
                <span class="badge bg-primary">Campus structure</span>
            </div>
        </div>
        <div class="card-body">
            <div class="sms-section-note mb-3">
                <i class="fas fa-info-circle"></i>
                Review class names, student strength, and live status from one place.
            </div>

            <table id="classes-datatable" class="table table-bordered table-hover" data-sms-table-name="classes">
                <thead>
                    <tr>
                        <th width="60">#</th>
                        <th>Class Name</th>
                        <th>Short Name</th>
                        <th>Code</th>
                        <th width="120">Strength</th>
                        <th width="160">Status</th>
                        <th width="140">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</section>

<script>
$(function () {
    const table = $('#classes-datatable').DataTable({
        responsive: true,
        autoWidth: false,
        searchDelay: 350,
        order: [[1, 'asc']],
        ajax: {
            url: "<?= base_url('admin/classes/data') ?>",
            type: "POST"
        },
        columns: [
            { data: 'sno', title: '#', className: 'text-center align-middle' },
            { data: 'class_name', className: 'align-middle' },
            { data: 'class_short_name', className: 'align-middle' },
            { data: 'class_id', className: 'align-middle' },
            { data: 'strength', className: 'text-center align-middle' },
            {
                data: 'status',
                className: 'text-center align-middle',
                render: function (data, type, row) {
                    const checked = parseInt(data, 10) === 1 ? 'checked' : '';
                    return `<input type="checkbox"
                                   class="toggle-status"
                                   data-id="${row.id}"
                                   ${checked}
                                   data-bs-toggle="toggle"
                                   data-size="sm"
                                   data-on="Active"
                                   data-off="Inactive"
                                   data-onstyle="success"
                                   data-offstyle="secondary">`;
                }
            },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                className: 'text-center align-middle',
                render: function (data) {
                    return `<a href="<?= base_url('admin/classes/edit?id=') ?>${data}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>`;
                }
            }
        ],
        drawCallback: function () {
            $('.toggle-status').bootstrapToggle();

            $('.toggle-status').off('change').on('change', function () {
                const rowId = $(this).data('id');
                const newStatus = $(this).prop('checked') ? 1 : 0;

                $.post("<?= base_url('admin/classes/toggle-status') ?>", {
                    id: rowId,
                    status: newStatus,
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                }, function (res) {
                    if (res.success) {
                        toastr.success(res.msg);
                    } else {
                        toastr.error(res.msg || 'Unable to update class status');
                    }
                }, 'json');
            });
        }
    });
});
</script>

<?= $this->endSection() ?>
