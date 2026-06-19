<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php ob_start(); ?>
<a href="<?= esc(base_url('admin/subjects/add'), 'attr') ?>" class="btn btn-primary btn-sm">
    <i class="fas fa-plus me-1"></i> Add Subject
</a>
<?php $headerActions = trim(ob_get_clean()); ?>

<?= view('components/page_header', [
    'title' => 'Subject Management',
    'subtitle' => 'Maintain curriculum subjects, short codes, and active academic availability.',
    'icon' => 'fas fa-book',
    'actionsHtml' => $headerActions,
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Subjects', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="card sms-card sms-index-card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-book-open me-2"></i>
                Subject Directory
            </h3>
            <div class="card-tools">
                <span class="badge bg-primary">Curriculum setup</span>
            </div>
        </div>
        <div class="card-body">
            <div class="sms-section-note mb-3">
                <i class="fas fa-info-circle"></i>
                Keep subject names and short identifiers tidy so every timetable and result sheet stays consistent.
            </div>

            <table id="subjects-datatable" class="table table-bordered table-hover" data-sms-table-name="subjects">
                <thead>
                    <tr>
                        <th width="60">#</th>
                        <th>Subject Name</th>
                        <th>Short Name</th>
                        <th width="120">Code</th>
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
    $('#subjects-datatable').DataTable({
        responsive: true,
        autoWidth: false,
        searchDelay: 350,
        order: [[1, 'asc']],
        ajax: {
            url: "<?= base_url('admin/subjects/data') ?>",
            type: "POST"
        },
        columns: [
            { data: 'sno', title: '#', className: 'text-center align-middle' },
            { data: 'subject_name', className: 'align-middle' },
            { data: 'subject_short_name', className: 'align-middle' },
            { data: 'sid', className: 'align-middle' },
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
                    return `<a href="<?= base_url('admin/subjects/edit?id=') ?>${data}" class="btn btn-outline-primary btn-sm">
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

                $.post("<?= base_url('admin/subjects/toggleStatus') ?>", {
                    id: rowId,
                    status: newStatus,
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                }, function (res) {
                    if (res.success) {
                        toastr.success(res.msg);
                    } else {
                        toastr.error(res.msg || 'Unable to update subject status');
                    }
                }, 'json');
            });
        }
    });
});
</script>

<?= $this->endSection() ?>
