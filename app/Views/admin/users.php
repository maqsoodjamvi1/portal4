<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
helper(['url', 'form']);
$status = (string) ($status ?? '1');
$role_filter = (string) ($role_filter ?? 'all');
$csrfName = csrf_token();
$csrfHash = csrf_hash();
?>

<link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>">
<style>
    .status-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: normal;
    }
    .status-badge.active {
        background-color: #28a745;
        color: white;
    }
    .status-badge.inactive {
        background-color: #dc3545;
        color: white;
    }
    .action-buttons .btn {
        margin: 0 2px;
        padding: 4px 8px;
    }
    .employee-avatar {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        object-fit: cover;
    }
    .users-filters .form-control,
    .users-filters .form-select {
        min-width: 9rem;
    }
    @media (min-width: 768px) {
        .users-filters {
            max-width: 28rem;
        }
    }
</style>

<?= view('components/page_header', [
    'title' => 'Employee Management',
    'icon' => 'fas fa-users',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Employees', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card sms-card">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                        <h3 class="card-title mb-2 mb-md-0">
                            <i class="fas fa-users me-2"></i>
                            Employees
                        </h3>
                        <div class="card-tools">
                            <a href="<?= base_url('admin/users/add') ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-user-plus me-1"></i> Add New Employee
                            </a>
                            <a href="<?= base_url('admin/users_bulk_info') ?>" class="btn btn-outline-primary btn-sm ms-1">
                                <i class="fas fa-users-cog me-1"></i> Bulk info
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-end mb-3 users-filters">
                            <div class="form-group mb-2 mb-md-0 me-md-3">
                                <label for="filterStatus" class="small text-muted mb-1 d-block">Employment status</label>
                                <select id="filterStatus" class="form-control form-control-sm form-select">
                                    <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Active</option>
                                    <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Dropped</option>
                                    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All</option>
                                </select>
                            </div>
                        </div>

                        <table id="users-table" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="56">S.No</th>
                                    <th>Employee</th>
                                    <th>Contact</th>
                                    <th>Role</th>
                                    <th>Designation</th>
                                    <th width="120">Status</th>
                                    <th width="250">Actions</th>
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

<script src="<?= base_url('resource/adminlte/plugins/bootstrap-switch/js/bootstrap-switch.min.js') ?>"></script>

<script>
$(function() {
    const csrfName = '<?= $csrfName ?>';
    const csrfHash = '<?= $csrfHash ?>';

    let filterStatus = '<?= esc($status, 'js') ?>';
    let filterRole = '<?= esc($role_filter, 'js') ?>';

    const table = $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url("admin/users/data") ?>',
            type: 'POST',
            data: function(d) {
                d.status = filterStatus;
                d.role_filter = filterRole;
                d[csrfName] = csrfHash;
            }
        },
        order: [[1, 'desc']],
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center align-middle',
                render: function(data, type, row, meta) {
                    return meta.settings._iDisplayStart + meta.row + 1;
                }
            },
            {
                data: null,
                name: 'full_name',
                className: 'align-middle',
                render: function(data) {
                    return `
                        <div class="d-flex align-items-center">
                            <div>
                                <strong>${data.full_name}</strong>
                                <br><small class="text-muted">${data.email}</small>
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: 'mobile_no',
                className: 'align-middle',
                render: function(data) {
                    return data ? `<i class="fas fa-phone me-1"></i>${data}` : '-';
                }
            },
            {
                data: 'role',
                className: 'align-middle',
                render: function(data) {
                    if (!data) {
                        return `<span class="badge text-bg-secondary">No Role</span>`;
                    }
                    const roles = String(data)
                        .split(',')
                        .map(role => role.trim())
                        .filter(Boolean);
                    if (!roles.length) {
                        return `<span class="badge text-bg-secondary">No Role</span>`;
                    }
                    return roles.map(role => `<span class="badge text-bg-info me-1 mb-1">${role}</span>`).join('');
                }
            },
            {
                data: 'designation',
                className: 'align-middle',
                render: function(data) {
                    return data ? data : '-';
                }
            },
            {
                data: 'status',
                name: 'status',
                className: 'align-middle text-center',
                render: function(data, type, row) {
                    const checked = parseInt(data, 10) === 1 ? 'checked' : '';
                    const label = parseInt(data, 10) === 1 ? 'Active' : 'Dropped';
                    return `
                        <div class="form-check form-switch justify-content-center d-inline-flex flex-column align-items-center">
                            <input type="checkbox"
                                   class="form-check-input status-switch"
                                   id="status_${row.id}"
                                   data-id="${row.id}"
                                   ${checked}>
                            <label class="form-check-label small" for="status_${row.id}">${label}</label>
                        </div>
                    `;
                }
            },
            {
                data: 'id',
                className: 'align-middle text-center',
                orderable: false,
                searchable: false,
                render: function(data) {
                    return `
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-info dropdown-toggle"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cog"></i> Actions
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="<?= base_url('admin/users/view') ?>/${data}">
                                    <i class="fas fa-user me-2 text-primary"></i>View Profile
                                </a>
                                <a class="dropdown-item" href="<?= base_url('admin/users/edit') ?>/${data}">
                                    <i class="fas fa-edit me-2 text-warning"></i>Edit Details
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="<?= base_url('admin/users/subjects') ?>/${data}">
                                    <i class="fas fa-book me-2 text-success"></i>Subjects
                                </a>
                                <a class="dropdown-item" href="<?= base_url('admin/users/timetable') ?>/${data}">
                                    <i class="fas fa-clock me-2 text-info"></i>Time Table
                                </a>
                                <a class="dropdown-item" href="<?= base_url('admin/users/salary') ?>/${data}">
                                    <i class="fas fa-money-bill me-2 text-danger"></i>Salary History
                                </a>
                            </div>
                        </div>
                    `;
                }
            }
        ]
    });

    function syncUrl() {
        const params = new URLSearchParams();
        if (filterStatus !== '1') {
            params.set('status', filterStatus);
        }
        const q = params.toString();
        const base = '<?= base_url('admin/users') ?>';
        history.replaceState(null, '', q ? base + '?' + q : base);
    }

    $('#filterStatus').on('change', function() {
        filterStatus = $('#filterStatus').val();
        syncUrl();
        table.ajax.reload();
    });

    $(document).on('change', '.status-switch', function() {
        const $this = $(this);
        const userId = $this.data('id');
        const newStatus = $this.is(':checked') ? 1 : 0;

        $.ajax({
            url: '<?= base_url("admin/users/toggleStatus") ?>',
            type: 'POST',
            data: {
                id: userId,
                status: newStatus,
                [csrfName]: csrfHash
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(newStatus === 1 ? 'Employee marked active' : 'Employee marked dropped');
                    $this.next('label').text(newStatus === 1 ? 'Active' : 'Dropped');
                    table.ajax.reload(null, false);
                } else {
                    toastr.error(response.msg || 'Failed to update status');
                    $this.prop('checked', !newStatus);
                }
            },
            error: function() {
                toastr.error('Request failed');
                $this.prop('checked', !newStatus);
            }
        });
    });
});
</script>

<?= $this->endSection() ?>
