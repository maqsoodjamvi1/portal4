<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
helper(['url', 'form']);
$status = (string) ($status ?? '1');
$role_filter = (string) ($role_filter ?? 'all');
$csrfName = csrf_token();
$csrfHash = csrf_hash();

ob_start();
?>
<div class="d-flex flex-wrap gap-2 justify-content-end">
    <a href="<?= esc(base_url('admin/users/add'), 'attr') ?>" class="btn btn-primary btn-sm shadow-sm">
        <i class="fas fa-user-plus me-1"></i> Add Employee
    </a>
    <a href="<?= esc(base_url('admin/users_bulk_info'), 'attr') ?>" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-users-cog me-1"></i> Bulk Update
    </a>
</div>
<?php
$headerActions = trim(ob_get_clean());
?>

<style>
    .employee-directory__summary {
        display: flex;
        align-items: center;
    }

    .employee-directory__summary-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.38rem 0.72rem;
        border: 1px solid #c3ead3;
        border-radius: 999px;
        background: #e7f7ee;
        color: #0e6c4b;
        font-size: 0.78rem;
        font-weight: 800;
    }

    .employee-directory__toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem 1.25rem;
        padding: 1rem 1.05rem;
        margin-bottom: 1rem;
        border: 1px solid #dbe6f0;
        border-radius: 12px;
        background: linear-gradient(180deg, #fbfdff 0%, #f3f8fc 100%);
    }

    .employee-directory__toolbar-main {
        display: flex;
        flex: 1 1 32rem;
        flex-wrap: wrap;
        gap: 0.85rem 1rem;
    }

    .employee-directory__field {
        flex: 1 1 13rem;
        min-width: 12rem;
    }

    .employee-directory__field label {
        display: block;
        margin: 0 0 0.42rem;
        color: #60758a;
        font-size: 0.73rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .employee-directory__field .form-control,
    .employee-directory__field .form-select {
        min-width: 0;
        min-height: 42px;
        height: 42px !important;
        font-size: 0.94rem;
        font-weight: 700;
        background: #fff;
    }

    .employee-directory__hint {
        display: flex;
        flex: 0 1 25rem;
        align-items: flex-start;
        gap: 0.55rem;
        color: #60758a;
        font-size: 0.85rem;
        line-height: 1.5;
    }

    .employee-directory__hint i {
        margin-top: 0.1rem;
        color: #256f9f;
    }

    .employee-directory__table.dataTable {
        width: 100% !important;
        margin-top: 0 !important;
        border: 1px solid #dce6f1;
    }

    .employee-directory__table.dataTable thead th {
        padding-top: 0.78rem;
        padding-bottom: 0.78rem;
        font-size: 0.83rem;
        font-weight: 800;
        letter-spacing: 0;
        text-transform: none;
    }

    .employee-directory__table.dataTable thead > tr > th.sorting,
    .employee-directory__table.dataTable thead > tr > th.sorting_asc,
    .employee-directory__table.dataTable thead > tr > th.sorting_desc {
        padding-right: 1.8rem !important;
    }

    .employee-directory__table.dataTable thead > tr > th.sorting::before,
    .employee-directory__table.dataTable thead > tr > th.sorting_asc::before,
    .employee-directory__table.dataTable thead > tr > th.sorting_desc::before {
        right: 0.72rem !important;
        top: calc(50% - 0.72rem) !important;
        color: #a4b4c5;
        opacity: 1 !important;
    }

    .employee-directory__table.dataTable thead > tr > th.sorting::after,
    .employee-directory__table.dataTable thead > tr > th.sorting_asc::after,
    .employee-directory__table.dataTable thead > tr > th.sorting_desc::after {
        right: 0.72rem !important;
        top: calc(50% - 0.06rem) !important;
        color: #7f95ab;
        opacity: 1 !important;
    }

    .employee-directory__table td {
        vertical-align: middle;
    }

    .employee-directory__table-bar,
    .employee-directory__table-footer {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.85rem 1rem;
    }

    .employee-directory__table-bar {
        margin-bottom: 0.95rem;
    }

    .employee-directory__table-footer {
        margin-top: 0.95rem;
    }

    .employee-directory__table-bar .dataTables_length,
    .employee-directory__table-bar .dataTables_filter,
    .employee-directory__table-footer .dataTables_info,
    .employee-directory__table-footer .dataTables_paginate {
        margin: 0;
    }

    .employee-directory__length-control label {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 0;
        color: #5b7084;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .employee-directory__length-control select {
        min-width: 4.4rem;
    }

    .employee-directory__search-control {
        position: relative;
    }

    .employee-directory__search-control label {
        width: 100%;
        margin-bottom: 0;
    }

    .employee-directory__search-icon {
        position: absolute;
        top: 50%;
        left: 0.95rem;
        z-index: 1;
        color: #8aa0b6;
        pointer-events: none;
        transform: translateY(-50%);
    }

    .employee-directory__search-control input {
        min-width: min(22rem, 70vw);
        width: 100%;
        padding-left: 2.4rem !important;
        background: #fff;
    }

    .employee-directory__identity {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        min-width: 0;
    }

    .employee-directory__avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.6rem;
        height: 2.6rem;
        border-radius: 12px;
        overflow: hidden;
        flex: 0 0 auto;
        background: linear-gradient(135deg, #256f9f, #0f9f8f);
        color: #fff;
        font-size: 0.88rem;
        font-weight: 800;
        box-shadow: 0 10px 18px rgba(37, 111, 159, 0.18);
    }

    .employee-directory__avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .employee-directory__identity-copy {
        min-width: 0;
    }

    .employee-directory__name {
        color: #10263f;
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.2;
    }

    .employee-directory__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.3rem 0.7rem;
        margin-top: 0.28rem;
        color: #64748b;
        font-size: 0.78rem;
    }

    .employee-directory__meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.32rem;
        min-width: 0;
    }

    .employee-directory__meta i {
        color: #7b90a6;
    }

    .employee-directory__contact-card {
        display: grid;
        gap: 0.28rem;
    }

    .employee-directory__contact-line,
    .employee-directory__sub {
        display: inline-flex;
        align-items: flex-start;
        gap: 0.45rem;
    }

    .employee-directory__contact-line {
        color: #17324a;
        font-weight: 700;
    }

    .employee-directory__contact-line i,
    .employee-directory__sub i {
        color: #256f9f;
        margin-top: 0.16rem;
    }

    .employee-directory__sub {
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .employee-directory__role-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.38rem;
    }

    .employee-directory__role-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.38rem 0.72rem;
        border: 1px solid #bde4f0;
        border-radius: 999px;
        background: #e8f8fd;
        color: #0c5f78;
        font-size: 0.78rem;
        font-weight: 800;
        line-height: 1.2;
    }

    .employee-directory__role-chip--muted {
        border-color: #dbe5ee;
        background: #f1f5f9;
        color: #64748b;
    }

    .employee-directory__designation {
        color: #17324a;
        font-weight: 700;
    }

    .employee-directory__status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.65rem;
        min-width: 8.25rem;
        padding: 0.45rem 0.7rem;
        border: 1px solid #dbe7f1;
        border-radius: 999px;
        background: #f8fbfd;
    }

    .employee-directory__status-label {
        font-size: 0.78rem;
        font-weight: 800;
    }

    .employee-directory__status-label--active {
        color: #0f7a56;
    }

    .employee-directory__status-label--inactive {
        color: #a14040;
    }

    .employee-directory .form-check.form-switch {
        min-height: 0;
        margin: 0;
        padding-left: 0;
    }

    .employee-directory .form-switch .form-check-input {
        float: none;
        width: 2.4rem;
        height: 1.35rem;
        margin: 0;
        border: none;
        background-color: #c7d3df;
        box-shadow: none;
        cursor: pointer;
    }

    .employee-directory .form-switch .form-check-input:focus {
        box-shadow: 0 0 0 0.18rem rgba(37, 111, 159, 0.16);
    }

    .employee-directory .form-switch .form-check-input:checked {
        background-color: #1fb87a;
    }

    .employee-directory__actions .btn {
        min-width: 7.4rem;
        justify-content: center;
    }

    .employee-directory__action-menu {
        min-width: 15rem;
        padding: 0.45rem;
        right: 0;
        left: auto;
        border: 1px solid #dbe5ef;
        border-radius: 14px;
        box-shadow: 0 18px 30px rgba(15, 23, 42, 0.14);
    }

    .employee-directory__actions {
        text-align: right;
    }

    .employee-directory__action-menu .dropdown-header {
        padding: 0.35rem 0.7rem 0.55rem;
        color: #7b90a6;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .employee-directory__action-menu .dropdown-item {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        padding: 0.72rem 0.8rem;
        border-radius: 10px;
        color: #334155;
        font-weight: 700;
    }

    .employee-directory__action-menu .dropdown-item i {
        width: 1rem;
        color: #256f9f !important;
        text-align: center;
    }

    .employee-directory__action-menu .dropdown-item:hover,
    .employee-directory__action-menu .dropdown-item:focus {
        color: #10263f;
        background: #f4f9fc;
    }

    .employee-directory__action-menu .dropdown-divider {
        margin: 0.4rem 0.3rem;
        border-top-color: #e2ebf3;
    }

    @media (max-width: 991.98px) {
        .employee-directory__toolbar {
            padding: 0.9rem;
        }

        .employee-directory__hint {
            flex-basis: 100%;
        }

        .employee-directory__table-bar > div,
        .employee-directory__table-footer > div {
            flex: 1 1 100%;
        }

        .employee-directory__table-footer .dataTables_paginate {
            justify-content: flex-start !important;
        }
    }

    @media (max-width: 767.98px) {
        .employee-directory__summary {
            width: 100%;
            justify-content: flex-start;
        }

        .employee-directory__identity {
            align-items: flex-start;
        }

        .employee-directory__search-control input {
            min-width: 0;
        }

        .employee-directory__status {
            min-width: 0;
        }
    }
</style>

<?= view('components/page_header', [
    'title' => 'Employee Management',
    'subtitle' => 'Manage staff profiles, campus roles, and teaching assignments.',
    'icon' => 'fas fa-users',
    'actionsHtml' => $headerActions,
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Employees', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card sms-card employee-directory">
                    <div class="card-header">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-address-card me-2"></i>
                            Staff Directory
                        </h3>
                        <div class="employee-directory__summary">
                            <span class="employee-directory__summary-pill">
                                <i class="fas fa-check-circle"></i>
                                Live campus roster
                            </span>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="employee-directory__toolbar">
                            <div class="employee-directory__toolbar-main">
                                <div class="employee-directory__field">
                                    <label for="filterStatus">Employment status</label>
                                    <select id="filterStatus" class="form-control form-control-sm form-select">
                                        <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Active</option>
                                        <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Inactive</option>
                                        <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All employees</option>
                                    </select>
                                </div>
                                <div class="employee-directory__field">
                                    <label for="filterRole">Directory view</label>
                                    <select id="filterRole" class="form-control form-control-sm form-select">
                                        <option value="all" <?= $role_filter === 'all' ? 'selected' : '' ?>>All staff</option>
                                        <option value="teachers" <?= $role_filter === 'teachers' ? 'selected' : '' ?>>Teaching staff only</option>
                                    </select>
                                </div>
                            </div>

                            <div class="employee-directory__hint">
                                <i class="fas fa-info-circle"></i>
                                <span>
                                    Use the directory to update staff status, open individual profiles, and move quickly
                                    between teaching assignments and payroll records.
                                </span>
                            </div>
                        </div>

                        <table id="users-table" class="table table-bordered table-hover employee-directory__table">
                            <thead>
                                <tr>
                                    <th width="60">#</th>
                                    <th>Employee</th>
                                    <th>Contact</th>
                                    <th>Roles</th>
                                    <th>Designation</th>
                                    <th width="170">Status</th>
                                    <th width="190">Actions</th>
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

<script>
$(function() {
    const csrfName = '<?= $csrfName ?>';
    const csrfHash = '<?= $csrfHash ?>';

    let filterStatus = '<?= esc($status, 'js') ?>';
    let filterRole = '<?= esc($role_filter, 'js') ?>';

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function(char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[char];
        });
    }

    function initialsFor(value) {
        const initials = String(value ?? '')
            .trim()
            .split(/\s+/)
            .filter(Boolean)
            .slice(0, 2)
            .map(part => part.charAt(0).toUpperCase())
            .join('');

        return initials || 'EM';
    }

    function decorateDirectoryControls() {
        const $filter = $('#users-table_filter');
        const $length = $('#users-table_length');

        if ($filter.length && !$filter.hasClass('employee-directory__search-control')) {
            $filter.addClass('employee-directory__search-control');
            $filter.prepend('<span class="employee-directory__search-icon"><i class="fas fa-search"></i></span>');
        }

        if ($length.length) {
            $length.addClass('employee-directory__length-control');
        }

        $('#users-table_filter input').attr({
            placeholder: 'Search employee, contact, or role',
            'aria-label': 'Search employees'
        });
    }

    const table = $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        searchDelay: 350,
        pageLength: 10,
        dom:
            "<'employee-directory__table-bar'<'employee-directory__table-length'l><'employee-directory__table-search'f>>" +
            "t" +
            "<'employee-directory__table-footer'<'employee-directory__table-info'i><'employee-directory__table-pagination'p>>",
        language: {
            search: '',
            searchPlaceholder: 'Search employee, contact, or role',
            lengthMenu: 'Rows _MENU_',
            info: 'Showing _START_ to _END_ of _TOTAL_ employees',
            infoEmpty: 'No employees found',
            zeroRecords: 'No matching employees found',
            emptyTable: 'No employees are available for this campus yet.'
        },
        ajax: {
            url: '<?= base_url("admin/users/data") ?>',
            type: 'POST',
            data: function(d) {
                d.status = filterStatus;
                d.role_filter = filterRole;
                d[csrfName] = csrfHash;
            }
        },
        order: [],
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                responsivePriority: 6,
                className: 'text-center align-middle',
                render: function(data, type, row, meta) {
                    return meta.settings._iDisplayStart + meta.row + 1;
                }
            },
            {
                data: null,
                name: 'full_name',
                responsivePriority: 1,
                className: 'align-middle',
                render: function(data) {
                    const fullName = escapeHtml(data.full_name || data.username || 'Employee');
                    const avatarMarkup = data.photo_url
                        ? `<img src="${escapeHtml(data.photo_url)}" alt="${fullName}" loading="lazy">`
                        : `<span>${escapeHtml(initialsFor(fullName))}</span>`;

                    const usernameMarkup = data.username
                        ? `<span><i class="fas fa-at"></i>${escapeHtml(data.username)}</span>`
                        : '';

                    const emailMarkup = data.email
                        ? `<span><i class="fas fa-envelope"></i>${escapeHtml(data.email)}</span>`
                        : `<span><i class="fas fa-envelope"></i>Email not added</span>`;

                    return `
                        <div class="employee-directory__identity">
                            <span class="employee-directory__avatar">${avatarMarkup}</span>
                            <div class="employee-directory__identity-copy">
                                <div class="employee-directory__name">${fullName}</div>
                                <div class="employee-directory__meta">
                                    ${usernameMarkup}
                                    ${emailMarkup}
                                </div>
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: null,
                responsivePriority: 2,
                className: 'align-middle',
                render: function(data) {
                    const mobile = String(data.mobile_no || '').trim();
                    const mobileAlt = String(data.mobile_alt || '').trim();

                    if (!mobile && !mobileAlt) {
                        return '<span class="text-muted small font-weight-bold">Not added</span>';
                    }

                    const primaryMarkup = mobile
                        ? `<div class="employee-directory__contact-line"><i class="fas fa-phone-alt"></i><span>${escapeHtml(mobile)}</span></div>`
                        : '';

                    const secondaryMarkup = mobileAlt
                        ? `<div class="employee-directory__sub"><i class="fas fa-life-ring"></i><span>${escapeHtml(mobileAlt)}</span></div>`
                        : '';

                    return `<div class="employee-directory__contact-card">${primaryMarkup}${secondaryMarkup}</div>`;
                }
            },
            {
                data: 'role',
                className: 'align-middle',
                responsivePriority: 3,
                render: function(data) {
                    if (!data || data === 'No Role') {
                        return '<span class="employee-directory__role-chip employee-directory__role-chip--muted">No role assigned</span>';
                    }

                    const roles = String(data)
                        .split(',')
                        .map(role => role.trim())
                        .filter(Boolean);

                    if (!roles.length) {
                        return '<span class="employee-directory__role-chip employee-directory__role-chip--muted">No role assigned</span>';
                    }

                    return `<div class="employee-directory__role-list">${
                        roles.map(role => `<span class="employee-directory__role-chip">${escapeHtml(role)}</span>`).join('')
                    }</div>`;
                }
            },
            {
                data: 'designation',
                className: 'align-middle',
                responsivePriority: 5,
                render: function(data) {
                    return data
                        ? `<span class="employee-directory__designation">${escapeHtml(data)}</span>`
                        : '<span class="text-muted small font-weight-bold">Not assigned</span>';
                }
            },
            {
                data: 'status',
                name: 'status',
                className: 'align-middle text-center',
                responsivePriority: 2,
                render: function(data, type, row) {
                    const isActive = parseInt(data, 10) === 1;
                    const checked = isActive ? 'checked' : '';
                    const label = isActive ? 'Active' : 'Inactive';
                    const labelClass = isActive
                        ? 'employee-directory__status-label--active'
                        : 'employee-directory__status-label--inactive';
                    const employeeName = escapeHtml(row.full_name || row.username || 'employee');

                    return `
                        <div class="employee-directory__status">
                            <span class="employee-directory__status-label ${labelClass}">${label}</span>
                            <div class="form-check form-switch">
                                <input type="checkbox"
                                       class="form-check-input status-switch"
                                       id="status_${row.id}"
                                       data-id="${row.id}"
                                       aria-label="Toggle status for ${employeeName}"
                                       ${checked}>
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: 'id',
                className: 'align-middle text-end employee-directory__actions',
                orderable: false,
                searchable: false,
                responsivePriority: 1,
                render: function(data, type, row) {
                    const displayName = escapeHtml(row.full_name || row.username || 'Employee');

                    return `
                        <div class="dropdown">
                            <button type="button"
                                    class="btn btn-sm btn-primary dropdown-toggle"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                <i class="fas fa-cog me-1"></i> Manage
                            </button>
                            <div class="dropdown-menu dropdown-menu-end employee-directory__action-menu">
                                <div class="dropdown-header">${displayName}</div>
                                <a class="dropdown-item" href="<?= base_url('admin/users/view') ?>/${data}">
                                    <i class="fas fa-user"></i> Profile
                                </a>
                                <a class="dropdown-item" href="<?= base_url('admin/users/edit') ?>/${data}">
                                    <i class="fas fa-edit"></i> Edit details
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="<?= base_url('admin/users/subjects') ?>/${data}">
                                    <i class="fas fa-book-open"></i> Subjects
                                </a>
                                <a class="dropdown-item" href="<?= base_url('admin/users/timetable') ?>/${data}">
                                    <i class="fas fa-calendar-alt"></i> Timetable
                                </a>
                                <a class="dropdown-item" href="<?= base_url('admin/users/salary') ?>/${data}">
                                    <i class="fas fa-wallet"></i> Salary history
                                </a>
                            </div>
                        </div>
                    `;
                }
            }
        ],
        initComplete: function() {
            decorateDirectoryControls();
        },
        drawCallback: function() {
            decorateDirectoryControls();
        }
    });

    function syncUrl() {
        const params = new URLSearchParams();

        if (filterStatus !== '1') {
            params.set('status', filterStatus);
        }

        if (filterRole !== 'all') {
            params.set('role_filter', filterRole);
        }

        const query = params.toString();
        const base = '<?= base_url('admin/users') ?>';
        history.replaceState(null, '', query ? base + '?' + query : base);
    }

    $('#filterStatus').on('change', function() {
        filterStatus = $(this).val();
        syncUrl();
        table.ajax.reload();
    });

    $('#filterRole').on('change', function() {
        filterRole = $(this).val();
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
                    toastr.success(newStatus === 1 ? 'Employee marked active' : 'Employee marked inactive');
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
