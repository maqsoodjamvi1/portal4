<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php ob_start(); ?>
<a href="<?= esc(base_url('admin/academic_session/add'), 'attr') ?>" class="btn btn-primary btn-sm">
    <i class="fas fa-plus me-1"></i> Add Session
</a>
<?php $headerActions = trim(ob_get_clean()); ?>

<?= view('components/page_header', [
    'title' => 'Academic Sessions',
    'icon' => 'fas fa-calendar',
    'subtitle' => 'Manage school session years, opening dates, and closing dates from one place.',
    'actionsHtml' => $headerActions,
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Academic Session', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="container-fluid">
        <div class="card sms-card sms-index-card card-primary card-outline card-tabs sms-list-surface shadow-sm">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Session Planner
                </h3>
                <div class="card-tools">
                    <span class="sms-data-chip">
                        <i class="fas fa-school"></i>
                        Academic cycle setup
                    </span>
                </div>
            </div>

            <div class="card-header p-0 pt-1 border-bottom-0">
                <ul class="nav nav-tabs sms-status-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= base_url('admin/academic_session'); ?>" role="tab">
                            Academic Sessions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('admin/academic_session/add') ?>">Add Academic Session</a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="sms-filter-bar">
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-4 col-md-6">
                            <label for="globalSearch">Search</label>
                            <input id="globalSearch" type="text" class="form-control form-control-sm" placeholder="Search session name">
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <label for="filterStart">Start from</label>
                            <input id="filterStart" type="date" class="form-control form-control-sm" placeholder="YYYY-MM-DD">
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <label for="filterEnd">End to</label>
                            <input id="filterEnd" type="date" class="form-control form-control-sm" placeholder="YYYY-MM-DD">
                        </div>
                        <div class="col-xl-2 col-md-6">
                            <div class="sms-filter-actions">
                                <button id="applyFilters" class="btn btn-primary btn-sm">
                                    <i class="fas fa-filter me-1"></i> Apply
                                </button>
                                <button id="resetFilters" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-undo me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <table class="table table-bordered table-hover" id="academic-session-datatable" data-sms-table-name="academic sessions">
                    <thead class="sticky-thead">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Session Name</th>
                            <th>Session Start</th>
                            <th>Session End</th>
                            <th style="width: 130px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <div class="sms-section-note mt-3">
                    <i class="fas fa-info-circle"></i>
                    Search by session name or narrow the list by start and end dates to review planning windows quickly.
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .sticky-thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #f8f9fa;
    }
</style>

<script>
const endpointData = '<?= base_url('admin/academic_session/data') ?>';
const sessionId = <?= json_encode($member_sessionid ?? null); ?>;
const csrfName = <?= json_encode(csrf_token()) ?>;
const csrfHash = <?= json_encode(csrf_hash()) ?>;

function fmt(dateStr) {
    return dateStr || '';
}

function printHeader() {
    const printDate = new Date();
    const pad = n => (n < 10 ? '0' + n : n);
    const absolute = printDate.getFullYear() + '-' + pad(printDate.getMonth() + 1) + '-' + pad(printDate.getDate())
        + ' ' + pad(printDate.getHours()) + ':' + pad(printDate.getMinutes());

    const fs = document.getElementById('filterStart').value || '-';
    const fe = document.getElementById('filterEnd').value || '-';
    return `
        <div style="margin-bottom:8px;">
            <strong>Academic Sessions</strong><br/>
            <span>Print Date: ${absolute}</span><br/>
            <span>Filter Range: Start >= ${fs}, End <= ${fe}</span>
        </div>
    `;
}

(function() {
    if (!$.fn.DataTable) {
        console.error('DataTables not found. Please ensure DataTables scripts are loaded.');
        return;
    }

    const $table = $('#academic-session-datatable');
    const $search = $('#globalSearch');

    const dt = $table.DataTable({
        processing: true,
        deferRender: true,
        responsive: true,
        autoWidth: false,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        pageLength: 10,
        order: [[1, 'asc']],
        dom:
            "<'row mb-2'<'col-sm-6'l><'col-sm-6 text-end'B>>" +
            "<'row'<'col-12'tr>>" +
            "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: (function() {
            if (!$.fn.dataTable.Buttons) return [];
            return [
                { extend: 'copy', className: 'btn btn-outline-secondary btn-sm', title: 'Academic Sessions' },
                { extend: 'excel', className: 'btn btn-outline-secondary btn-sm', title: 'Academic Sessions' },
                { extend: 'csv', className: 'btn btn-outline-secondary btn-sm', title: 'Academic Sessions' },
                {
                    extend: 'print',
                    className: 'btn btn-outline-secondary btn-sm',
                    title: '',
                    customize: function(win) {
                        const hdr = printHeader();
                        const $body = $(win.document.body);
                        $body.css('font-size', '12px');
                        $body.prepend(hdr);
                    }
                }
            ];
        })(),
        ajax: {
            url: endpointData,
            type: 'POST',
            data: function(d) {
                d[csrfName] = csrfHash;
                d.filter_start = $('#filterStart').val() || '';
                d.filter_end = $('#filterEnd').val() || '';
            },
            dataSrc: function(res) {
                if (res && res.csrf && res.csrf.hash) {
                    document.querySelectorAll(`input[name="${res.csrf.token}"]`).forEach(el => el.value = res.csrf.hash);
                }
                return res && res.data ? res.data : (res || []);
            },
            error: function() {
                toastr.error('Failed to load sessions. Please try again.');
            }
        },
        columns: [
            {
                data: null,
                className: 'align-middle text-muted',
                orderable: false,
                render: function(data, type, row, meta) {
                    return meta.settings._iDisplayStart + meta.row + 1;
                }
            },
            {
                data: 'session_name',
                className: 'align-middle fw-semibold',
                render: function(val) {
                    return val ? $('<div/>').text(val).html() : '';
                }
            },
            {
                data: 'start_date',
                className: 'align-middle',
                render: function(val) {
                    return `<span class="badge text-bg-light border">${fmt(val)}</span>`;
                }
            },
            {
                data: 'end_date',
                className: 'align-middle',
                render: function(val) {
                    return `<span class="badge text-bg-light border">${fmt(val)}</span>`;
                }
            },
            {
                data: 'id',
                orderable: false,
                className: 'align-middle text-center',
                render: function(id) {
                    return `<a href="<?= base_url('admin/academic_session/edit?id='); ?>${id}" class="btn btn-outline-primary btn-sm" title="Edit session">
                                <i class="fa fa-edit me-1"></i> Edit
                            </a>`;
                }
            }
        ],
        rowCallback: function(row, data) {
            if (data.id == sessionId) {
                $(row).addClass('table-success');
            }
        }
    });

    $search.on('keyup change', function() {
        dt.search(this.value).draw();
    });

    $('#applyFilters').on('click', function() {
        dt.ajax.reload();
    });

    $('#resetFilters').on('click', function() {
        $('#filterStart').val('');
        $('#filterEnd').val('');
        $search.val('');
        dt.search('').ajax.reload();
    });
})();
</script>

<?= $this->endSection() ?>
