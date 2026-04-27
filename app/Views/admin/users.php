<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
helper(['url', 'form']);
$status = (string) (service('request')->getGet('status') ?? '1');
$csrfName = csrf_token();
$csrfHash = csrf_hash();
?>

<link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">

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
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Employee Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Employees</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-users mr-2"></i>
                            <?= $status === '1' ? 'Current Employees' : 'Dropped Employees' ?>
                        </h3>
                        <div class="card-tools">
                            <a href="<?= base_url('admin/users/add') ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-user-plus mr-1"></i> Add New Employee
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <ul class="nav nav-pills mb-3">
                            <li class="nav-item">
                                <a class="nav-link <?= $status === '1' ? 'active' : '' ?>" 
                                   href="<?= base_url('admin/users?status=1') ?>">
                                    <i class="fas fa-user-check mr-1"></i> Current Employees
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $status === '0' ? 'active' : '' ?>" 
                                   href="<?= base_url('admin/users?status=0') ?>">
                                    <i class="fas fa-user-slash mr-1"></i> Dropped Employees
                                </a>
                            </li>
                        </ul>

                        <table id="users-table" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="40">#</th>
                                    <th>Employee</th>
                                    <th>Contact</th>
                                    <th>Role</th>
                                    <th>Designation</th>
                                    <th width="100">Status</th>
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
<script src="<?= base_url('resource/adminlte/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('resource/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>

<script>
$(function() {
    const csrfName = '<?= $csrfName ?>';
    const csrfHash = '<?= $csrfHash ?>';
    const currentStatus = '<?= $status ?>';

    const table = $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url("admin/users/data") ?>',
            type: 'POST',
            data: function(d) {
                d.status = currentStatus;
                d[csrfName] = csrfHash;
            }
        },
        columns: [
            { 
                data: 'id',
                className: 'text-center align-middle'
            },
            { 
                data: null,
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
                    return data ? `<i class="fas fa-phone mr-1"></i>${data}` : '-';
                }
            },
            { 
                data: 'role',
                className: 'align-middle',
                render: function(data) {
                    return `<span class="badge badge-info">${data}</span>`;
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
                className: 'align-middle text-center',
                render: function(data, type, row) {
                    if (currentStatus === '0') {
                        return `<span class="badge badge-danger">Inactive</span>`;
                    }
                    
                    let checked = data == 1 ? 'checked' : '';
                    return `
                        <div class="custom-control custom-switch">
                            <input type="checkbox" 
                                   class="custom-control-input status-switch" 
                                   id="status_${row.id}"
                                   data-id="${row.id}"
                                   ${checked}>
                            <label class="custom-control-label" for="status_${row.id}">
                                ${data == 1 ? 'Active' : 'Inactive'}
                            </label>
                        </div>
                    `;
                }
            },
            {
                data: 'id',
                className: 'align-middle text-center',
                orderable: false,
                render: function(data) {
                    return `
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-info dropdown-toggle" 
                                    data-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cog"></i> Actions
                            </button>
                            <div class="dropdown-menu">
                              

                                <a class="dropdown-item" href="<?= base_url('admin/users/view') ?>/${data}">
    <i class="fas fa-user mr-2 text-primary"></i>View Profile
</a>


                                <a class="dropdown-item" href="<?= base_url('admin/users/edit') ?>/${data}">
                                    <i class="fas fa-edit mr-2 text-warning"></i>Edit Details
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="<?= base_url('admin/users/subjects') ?>/${data}">
                                    <i class="fas fa-book mr-2 text-success"></i>Subjects
                                </a>
                                <a class="dropdown-item" href="<?= base_url('admin/users/timetable') ?>/${data}">
                                    <i class="fas fa-clock mr-2 text-info"></i>Time Table
                                </a>
                                <a class="dropdown-item" href="<?= base_url('admin/users/salary') ?>/${data}">
                                    <i class="fas fa-money-bill mr-2 text-danger"></i>Salary History
                                </a>
                            </div>
                        </div>
                    `;
                }
            }
        ]
    });

    // Handle status toggle
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
                    toastr.success('Status updated successfully');
                    $this.next('label').text(newStatus == 1 ? 'Active' : 'Inactive');
                } else {
                    toastr.error('Failed to update status');
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