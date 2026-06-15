<?= $this->extend('layouts/admin_template') ?>

<?= $this->section('pageStyles') ?>
<style>
.emp-att-toolbar {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: .5rem;
    padding: .75rem 1rem;
    margin-bottom: 1rem;
}
.emp-att-toolbar .form-control,
.emp-att-toolbar .btn {
    min-height: 38px;
}
.emp-att-search-wrap {
    position: relative;
    flex: 1 1 180px;
    min-width: 0;
}
.emp-att-search-wrap .fa-search {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #adb5bd;
    pointer-events: none;
}
.emp-att-search-wrap input {
    padding-left: 2rem;
}
.emp-att-summary .badge {
    font-size: .85rem;
    margin-right: .35rem;
    margin-bottom: .25rem;
}
.emp-att-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e9ecef;
}
.emp-att-status-group label.btn {
    margin: 2px;
    font-size: .78rem;
    padding: .35rem .55rem;
    opacity: .72;
    transition: opacity .15s, box-shadow .15s, transform .1s;
}
.emp-att-status-group label.btn.active {
    opacity: 1;
    font-weight: 700;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, .45);
    transform: translateY(-1px);
}
.emp-att-status-group label.btn.active.btn-outline-success { background-color: #28a745; color: #fff; border-color: #28a745; }
.emp-att-status-group label.btn.active.btn-outline-danger { background-color: #dc3545; color: #fff; border-color: #dc3545; }
.emp-att-status-group label.btn.active.btn-outline-warning { background-color: #ffc107; color: #212529; border-color: #ffc107; }
.emp-att-status-group label.btn.active.btn-outline-info { background-color: #17a2b8; color: #fff; border-color: #17a2b8; }
.emp-att-status-group label.btn.active.btn-outline-secondary { background-color: #6c757d; color: #fff; border-color: #6c757d; }
.emp-att-status-group label.btn input {
    position: absolute;
    clip: rect(0,0,0,0);
    pointer-events: none;
}
.emp-att-remarks-wrap {
    margin-top: .5rem;
    max-width: 100%;
}
.emp-att-row[data-has-saved="1"] {
    background: #f8fbff;
}
.emp-att-list-head {
    display: none;
    grid-template-columns: 1fr 240px 110px 110px;
    gap: .75rem;
    padding: .5rem .85rem;
    font-weight: 600;
    font-size: .85rem;
    color: #495057;
    background: #f1f3f5;
    border-radius: .35rem .35rem 0 0;
    border: 1px solid #dee2e6;
    border-bottom: none;
}
.emp-att-list {
    border: 1px solid #dee2e6;
    border-radius: 0 0 .35rem .35rem;
    overflow: hidden;
}
.emp-att-item {
    display: grid;
    grid-template-columns: 1fr;
    gap: .65rem;
    padding: .85rem;
    border-bottom: 1px solid #eee;
    background: #fff;
}
.emp-att-item:last-child {
    border-bottom: none;
}
.emp-att-item-person {
    display: flex;
    align-items: center;
    gap: .75rem;
    min-width: 0;
}
.emp-att-item-meta {
    flex: 1;
    min-width: 0;
}
.emp-att-item-status .btn-group {
    display: flex;
    flex-wrap: wrap;
    width: 100%;
}
.emp-att-item-status .btn-group label {
    flex: 1 1 calc(20% - 4px);
    min-width: 58px;
}
@media (min-width: 768px) {
    .emp-att-list-head {
        display: grid;
    }
    .emp-att-list {
        border-radius: 0 0 .35rem .35rem;
    }
    .emp-att-item {
        grid-template-columns: 1fr 240px 110px 110px;
        align-items: center;
        gap: .75rem;
        padding: .65rem .85rem;
    }
    .emp-att-item-status .btn-group label {
        flex: 0 0 auto;
    }
}
.emp-att-sticky-actions {
    position: sticky;
    bottom: 0;
    z-index: 20;
    background: linear-gradient(180deg, rgba(255,255,255,0) 0%, #fff 24%);
    padding: 1rem 0 .25rem;
    margin-top: 1rem;
}
#loader-emp-att {
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.emp-att-row.emp-att-filtered-out {
    display: none !important;
}
@media (max-width: 767.98px) {
    .emp-att-toolbar .toolbar-row {
        flex-direction: column;
        align-items: stretch !important;
    }
    .emp-att-toolbar .btn-group-bulk {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
    }
    .emp-att-toolbar .btn-group-bulk .btn {
        flex: 1 1 calc(50% - .35rem);
    }
    .emp-att-sticky-actions .btn {
        width: 100%;
        margin-bottom: .35rem;
    }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$campus_id = (int) ($sessionData['campusid'] ?? 0);
$today = date('Y-m-d');
?>

<?= view('components/page_header', [
    'title' => 'Mark Employee Attendance',
    'icon' => 'fas fa-clipboard-check',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Employee Attendance', 'url' => base_url('admin/employees_attendance')],
        ['label' => 'Mark', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title mb-0"><i class="fas fa-user-check me-2"></i>Daily attendance</h3>
            </div>
            <div class="card-body">
                <?php
                echo form_open(base_url('admin/employees_attendance/save'), [
                    'role' => 'form',
                    'id'   => 'emp-attendance-form',
                    'class'=> 'emp-attendance-form',
                ]);
                echo form_hidden('campus_id', (string) $campus_id);
                ?>

                <div class="emp-att-toolbar">
                    <div class="d-flex flex-wrap align-items-center toolbar-row" style="gap:.5rem;">
                        <div class="form-group mb-0">
                            <label for="date" class="visually-hidden">Date</label>
                            <input type="date" name="date" id="date" required value="<?= esc($today) ?>" class="form-control">
                        </div>
                        <button type="button" id="btn-load-employees" class="btn btn-primary">
                            <i class="fas fa-sync-alt me-1"></i> Load
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-today" title="Today">Today</button>
                        <a href="<?= base_url('admin/employee-face-attendance') ?>" class="btn btn-outline-primary" title="Face check-in / check-out">
                            <i class="fas fa-camera me-1"></i> Face scanner
                        </a>

                        <div class="emp-att-search-wrap">
                            <i class="fas fa-search"></i>
                            <input type="search" id="emp-att-search" class="form-control" placeholder="Search employee…" disabled>
                        </div>

                        <div class="btn-group-bulk ms-md-auto d-flex" style="gap:.35rem;">
                            <button type="button" class="btn btn-sm btn-outline-success btn-mark-all" data-status="P" disabled>
                                <i class="fas fa-check-double"></i> All Present
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-mark-all" data-status="A" disabled>
                                All Absent
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-mark-all" data-status="L" disabled>
                                All Leave
                            </button>
                        </div>
                    </div>
                </div>

                <div id="loader-emp-att" class="text-center text-muted" style="display:none;">
                    <i class="fas fa-2x fa-sync-alt fa-spin"></i>
                    <div class="mt-2 small">Loading employees…</div>
                </div>

                <div id="employees_list_container"></div>

                <div class="emp-att-sticky-actions">
                    <button type="submit" id="submitBtn" class="btn btn-primary btn-lg" disabled>
                        <i class="fas fa-save me-1"></i> Save attendance
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="history.back();">Cancel</button>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</section>

<script>
(function ($) {
    'use strict';

    var $form = $('#emp-attendance-form');
    var $container = $('#employees_list_container');
    var $loader = $('#loader-emp-att');
    var $search = $('#emp-att-search');
    var $submit = $('#submitBtn');

    function showLoader(show) {
        $loader.toggle(!!show);
        if (show) {
            $container.empty();
        }
    }

    function setToolbarEnabled(hasRows) {
        $search.prop('disabled', !hasRows);
        $submit.prop('disabled', !hasRows);
        $('.btn-mark-all').prop('disabled', !hasRows);
    }

    function updateSummary() {
        var counts = { P: 0, A: 0, LC: 0, EL: 0, L: 0, _none: 0 };
        $container.find('.emp-att-row:not(.emp-att-filtered-out)').each(function () {
            var $checked = $(this).find('.emp-att-status-radio:checked');
            if ($checked.length) {
                var v = $checked.val();
                if (counts[v] !== undefined) {
                    counts[v]++;
                }
            } else {
                counts._none++;
            }
        });

        $('#emp-att-summary .emp-att-count').each(function () {
            var s = $(this).data('status');
            $(this).text((s === '_none' ? '—: ' : s + ': ') + (counts[s] || 0));
        });
    }

    function applyStatusToRow($row, status) {
        var $group = $row.find('.emp-att-status-group');
        $group.find('label').removeClass('active');
        var $radio = $group.find('.emp-att-status-radio[value="' + status + '"]');
        if ($radio.length) {
            $radio.prop('checked', true);
            $radio.closest('label').addClass('active');
        }
        $row.attr('data-status', status);
        $row.find('.emp-att-time-in').toggleClass('d-none', status !== 'LC');
        $row.find('.emp-att-time-out').toggleClass('d-none', status !== 'EL');
        var $remarksWrap = $row.find('.emp-att-remarks-wrap');
        var showRemarks = status === 'A' || status === 'L';
        $remarksWrap.toggleClass('d-none', !showRemarks);
        var $remarks = $remarksWrap.find('.emp-att-remarks');
        if (showRemarks) {
            var label = $remarksWrap.find('label');
            if (status === 'A') {
                label.text('Remarks (required for absent)');
                $remarks.attr('placeholder', 'Reason for absence…').prop('required', true);
            } else {
                label.text('Leave note');
                $remarks.attr('placeholder', 'Optional leave note…').prop('required', false);
            }
        } else {
            $remarks.prop('required', false);
        }
    }

    function syncAllRowsFromDom() {
        $container.find('.emp-att-row').each(function () {
            var $row = $(this);
            var $checked = $row.find('.emp-att-status-radio:checked');
            if ($checked.length) {
                applyStatusToRow($row, $checked.val());
            } else {
                applyStatusToRow($row, '');
            }
        });
        updateSummary();
    }

    function bindGridEvents() {
        $container.off('change.empAtt click.empAtt');

        $container.on('change.empAtt', '.emp-att-status-radio', function () {
            var $radio = $(this);
            var status = $radio.val();
            var empId = $radio.attr('name').replace('_status', '');
            $container.find('.emp-att-row[data-emp-id="' + empId + '"]').each(function () {
                applyStatusToRow($(this), status);
            });
            updateSummary();
        });

        syncAllRowsFromDom();
    }

    function loadEmployees() {
        var campusId = $('#campus_id').val();
        var date = $('#date').val();
        if (!date) {
            toastr.warning('Please select a date.');
            return;
        }

        showLoader(true);
        $.ajax({
            url: '<?= base_url('admin/employees_attendance/get_employees') ?>',
            type: 'POST',
            dataType: 'html',
            data: { campus_id: campusId, date: date },
            success: function (html) {
                $container.html(html);
                var hasRows = $container.find('.emp-att-row').length > 0;
                setToolbarEnabled(hasRows);
                if (hasRows) {
                    bindGridEvents();
                }
            },
            error: function (xhr) {
                var msg = 'Could not load employees.';
                if (xhr.responseJSON && xhr.responseJSON.msg) {
                    msg = xhr.responseJSON.msg;
                }
                $container.html("<div class='alert alert-danger mb-0'>" + msg + "</div>");
                setToolbarEnabled(false);
                toastr.error(msg);
            },
            complete: function () {
                showLoader(false);
            }
        });
    }

    $search.on('input', function () {
        var q = $(this).val().toLowerCase().trim();
        $container.find('.emp-att-row').each(function () {
            var name = $(this).data('name') || '';
            $(this).toggleClass('emp-att-filtered-out', q !== '' && name.indexOf(q) === -1);
        });
        updateSummary();
    });

    $('.btn-mark-all').on('click', function () {
        var status = $(this).data('status');
        $container.find('.emp-att-row:not(.emp-att-filtered-out)').each(function () {
            applyStatusToRow($(this), status);
        });
        updateSummary();
    });

    $('.btn-today').on('click', function () {
        $('#date').val('<?= esc($today) ?>');
    });

    $('#btn-load-employees').on('click', loadEmployees);

    $('#date').on('change', function () {
        if ($container.find('.emp-att-row').length) {
            loadEmployees();
        }
    });

    $form.on('submit', function (e) {
        e.preventDefault();
        if ($submit.prop('disabled')) {
            return;
        }
        var unchecked = 0;
        var missingRemarks = 0;
        $container.find('.emp-att-row:not(.emp-att-filtered-out)').each(function () {
            var $row = $(this);
            var $checked = $row.find('.emp-att-status-radio:checked');
            if (!$checked.length) {
                unchecked++;
                return;
            }
            if ($checked.val() === 'A') {
                var remarks = $.trim($row.find('.emp-att-remarks').val() || '');
                if (!remarks) {
                    missingRemarks++;
                }
            }
        });
        if (unchecked > 0) {
            toastr.warning('Please set status for all employees (' + unchecked + ' remaining).');
            return;
        }
        if (missingRemarks > 0) {
            toastr.warning('Please enter remarks for absent employees (' + missingRemarks + ' missing).');
            return;
        }

        $submit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving…');

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function (json) {
                if (json.success) {
                    toastr.success(json.msg);
                    loadEmployees();
                } else {
                    toastr.error(json.msg || 'Save failed.');
                }
            },
            error: function () {
                toastr.error('Save request failed. Please try again.');
            },
            complete: function () {
                $submit.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save attendance');
            }
        });
    });

    $(function () {
        loadEmployees();
    });
})(jQuery);
</script>

<?= $this->endSection() ?>
