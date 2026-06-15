<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Bulk Salary Adjustment',
    'icon' => 'fas fa-users-cog',
    'subtitle' => 'Enter off/leave/late/early counts — total deductions are calculated automatically from salary settings.',
    'actionsHtml' => '<div class="text-sm-right">'
        . '<a href="' . esc(base_url('admin/salary-settings'), 'attr') . '" class="btn btn-outline-secondary btn-sm">'
        . '<i class="fas fa-cog me-1"></i> Salary Settings</a></div>',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Salary Settings', 'url' => base_url('admin/salary-settings')],
        ['label' => 'Bulk Adjustment', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="container-fluid">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
        <?php if (! empty($loadError)): ?>
            <div class="alert alert-warning"><?= esc($loadError) ?></div>
        <?php endif; ?>
        <?php if (! $settings): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-1"></i>
                Campus salary settings are not configured.
                <a href="<?= base_url('admin/salary-settings') ?>">Configure settings first</a>.
            </div>
        <?php endif; ?>

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Select Month</h3>
            </div>
            <div class="card-body">
                <form id="filterForm" class="row align-items-end">
                    <div class="form-group col-md-3">
                        <label>Year</label>
                        <select name="year" id="filterYear" class="form-control" required>
                            <?php for ($y = (int) date('Y') - 1; $y <= (int) date('Y') + 1; $y++): ?>
                                <option value="<?= $y ?>" <?= (int) $year === $y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Month</label>
                        <select name="month" id="filterMonth" class="form-control" required>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= (int) $month === $m ? 'selected' : '' ?>>
                                    <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <button type="submit" class="btn btn-primary w-100" id="loadBtn">
                            <i class="fas fa-sync-alt me-1"></i> Load Employees
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row mb-3" id="summaryCards" style="display:none;">
            <div class="col-md-3 col-6">
                <div class="info-box bg-light">
                    <span class="info-box-icon"><i class="fas fa-users text-primary"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Employees</span>
                        <span class="info-box-number" id="sumCount">0</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="info-box bg-light">
                    <span class="info-box-icon"><i class="fas fa-money-bill-wave text-success"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Basic Salary</span>
                        <span class="info-box-number" id="sumBasic">0.00</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="info-box bg-light">
                    <span class="info-box-icon"><i class="fas fa-minus-circle text-danger"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Deductions</span>
                        <span class="info-box-number" id="sumDeductions">0.00</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="info-box bg-light">
                    <span class="info-box-icon"><i class="fas fa-wallet text-info"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Net Salary</span>
                        <span class="info-box-number" id="sumNet">0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" id="bulkTableCard" style="<?= empty($rows) ? 'display:none;' : '' ?>">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="card-title mb-2 mb-md-0">Employee Salary Grid</h3>
                <div class="d-flex flex-wrap" style="gap:6px;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="selectAllBtn">Select All</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllBtn">Deselect All</button>
                    <button type="button" class="btn btn-sm btn-success" id="generateBtn">
                        <i class="fas fa-calculator me-1"></i> Generate Salary
                    </button>
                    <button type="button" class="btn btn-sm btn-warning" id="regenerateBtn" title="Replace pending slips with updated counts">
                        <i class="fas fa-redo me-1"></i> Regenerate Selected
                    </button>
                    <a href="#" class="btn btn-sm btn-outline-primary" id="printSlipsBtn" target="_blank">
                        <i class="fas fa-print me-1"></i> Print Slips
                    </a>
                    <a href="#" class="btn btn-sm btn-outline-success" id="exportSlipsBtn">
                        <i class="fas fa-download me-1"></i> Download CSV
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm mb-0" id="bulkSalaryTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px">
                                    <input type="checkbox" id="checkAll" checked title="Select all">
                                </th>
                                <th>Employee</th>
                                <th class="text-end">Basic Salary</th>
                                <th class="text-center" style="min-width:80px">Off Days</th>
                                <th class="text-center" style="min-width:80px">Leave Days</th>
                                <th class="text-center" style="min-width:70px">Late</th>
                                <th class="text-center" style="min-width:80px">Early Left</th>
                                <th class="text-end">Total Deductions</th>
                                <th class="text-end">Net Salary</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="bulkSalaryBody">
                            <?php foreach ($rows as $row): ?>
                                <?= view('admin/partials/salary_bulk_adjustment_row', ['row' => $row]) ?>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light fw-bold" id="bulkSalaryFoot">
                            <tr>
                                <td colspan="2" class="text-end">Totals (all rows):</td>
                                <td class="text-end" id="footBasic">0.00</td>
                                <td colspan="4"></td>
                                <td class="text-end text-danger" id="footDeductions">0.00</td>
                                <td class="text-end" id="footNet">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="alert alert-info mt-3" id="emptyState" style="<?= ! empty($rows) ? 'display:none;' : '' ?>">
            <i class="fas fa-info-circle me-1"></i>
            Select a month and click <strong>Load Employees</strong> to review attendance and deductions.
        </div>
    </div>
</section>

<script>
(function () {
    var csrfName = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';
    var loadUrl = '<?= base_url('admin/salary-settings/bulk-adjustment/load') ?>';
    var generateUrl = '<?= base_url('admin/salary-settings/bulk-adjustment/generate') ?>';
    var printBaseUrl = '<?= base_url('admin/salary-settings/bulk-adjustment/print') ?>';
    var exportBaseUrl = '<?= base_url('admin/salary-settings/bulk-adjustment/export') ?>';
    var viewSlipBaseUrl = '<?= base_url('admin/users/view-salary-slip') ?>';
    var calcSettings = <?= json_encode($calcSettings ?? []) ?>;

    function fmt(n) {
        return (Math.round(parseFloat(n || 0) * 100) / 100).toFixed(2);
    }

    function dayDeduction(days, basic, dailySalary, applyDeduction) {
        days = parseInt(days || 0, 10);
        if (days <= 0 || !applyDeduction) return 0;
        var type = calcSettings.deduction_type || 'per_day_salary';
        if (type === 'per_day_salary') return days * dailySalary;
        if (type === 'fixed_amount') return days * parseFloat(calcSettings.deduction_per_day_amount || 0);
        if (type === 'percentage') {
            return days * (basic * (parseFloat(calcSettings.deduction_per_day_percentage || 0) / 100));
        }
        return 0;
    }

    function calcTotalDeductions(tr) {
        var basic = parseFloat(tr.dataset.basic || 0);
        var dailySalary = parseFloat(tr.dataset.dailySalary || 0);
        var applyDeduction = parseInt(tr.dataset.applyDeduction || 1, 10) === 1;
        var lateMinutesPer = parseInt(tr.dataset.lateMinutesPer || 0, 10);

        var offDays = parseInt(tr.querySelector('.cnt-off').value || 0, 10);
        var leaveDays = parseInt(tr.querySelector('.cnt-leave').value || 0, 10);
        var lateCount = parseInt(tr.querySelector('.cnt-late').value || 0, 10);
        var earlyCount = parseInt(tr.querySelector('.cnt-early').value || 0, 10);

        var absent = dayDeduction(offDays, basic, dailySalary, applyDeduction);
        var leave = dayDeduction(leaveDays, basic, dailySalary, applyDeduction);

        var late = 0;
        if (parseInt(calcSettings.late_deduction_enabled || 0, 10) && lateCount > 0) {
            var perMinute = parseFloat(calcSettings.late_deduction_amount || 0);
            late = lateMinutesPer > 0
                ? lateCount * lateMinutesPer * perMinute
                : lateCount * perMinute;
        }

        var early = 0;
        if (parseInt(calcSettings.early_leave_deduction_enabled || 0, 10) && earlyCount > 0) {
            early = earlyCount * parseFloat(calcSettings.early_leave_deduction_amount || 0);
        }

        return absent + leave + late + early;
    }

    function recalcRow(tr) {
        var basic = parseFloat(tr.dataset.basic || 0);
        var attBonus = parseFloat(tr.dataset.attBonus || 0);
        var otherBonus = parseFloat(tr.dataset.otherBonus || 0);
        var security = parseFloat(tr.dataset.security || 0);
        var advance = parseFloat(tr.dataset.advance || 0);
        var totalDed = calcTotalDeductions(tr);
        var fullDed = totalDed + security + advance;
        var net = basic + attBonus + otherBonus - fullDed;
        tr.querySelector('.total-deductions').textContent = fmt(fullDed);
        tr.querySelector('.net-salary').textContent = fmt(net);
        updateTotals();
    }

    function updateTotals() {
        var sumBasic = 0, sumDed = 0, sumNet = 0, count = 0;
        document.querySelectorAll('#bulkSalaryBody tr.bulk-row').forEach(function (tr) {
            count++;
            sumBasic += parseFloat(tr.dataset.basic || 0);
            sumDed += parseFloat(tr.querySelector('.total-deductions').textContent || 0);
            sumNet += parseFloat(tr.querySelector('.net-salary').textContent || 0);
        });
        document.getElementById('sumCount').textContent = count;
        document.getElementById('sumBasic').textContent = fmt(sumBasic);
        document.getElementById('sumDeductions').textContent = fmt(sumDed);
        document.getElementById('sumNet').textContent = fmt(sumNet);
        document.getElementById('footBasic').textContent = fmt(sumBasic);
        document.getElementById('footDeductions').textContent = fmt(sumDed);
        document.getElementById('footNet').textContent = fmt(sumNet);
        document.getElementById('summaryCards').style.display = count > 0 ? '' : 'none';
    }

    function updateActionLinks() {
        var year = document.getElementById('filterYear').value;
        var month = document.getElementById('filterMonth').value;
        var q = '?year=' + encodeURIComponent(year) + '&month=' + encodeURIComponent(month);
        document.getElementById('printSlipsBtn').href = printBaseUrl + q;
        document.getElementById('exportSlipsBtn').href = exportBaseUrl + q;
    }

    function bindRowEvents(tr) {
        tr.querySelectorAll('.count-input').forEach(function (input) {
            input.addEventListener('input', function () { recalcRow(tr); });
        });
    }

    function renderRows(rows, settings) {
        if (settings && Object.keys(settings).length) {
            calcSettings = settings;
        }
        var tbody = document.getElementById('bulkSalaryBody');
        tbody.innerHTML = '';
        if (!rows || !rows.length) {
            document.getElementById('bulkTableCard').style.display = 'none';
            document.getElementById('emptyState').style.display = '';
            document.getElementById('emptyState').innerHTML = '<i class="fas fa-info-circle me-1"></i> No employees with basic salary found for this month.';
            return;
        }

        rows.forEach(function (row) {
            var isPaid = row.existing_payment_status === 'paid';
            var tr = document.createElement('tr');
            tr.className = 'bulk-row' + (row.has_existing_slip ? (isPaid ? ' table-success' : ' table-warning') : '');
            tr.dataset.userId = row.user_id;
            tr.dataset.slipId = row.existing_slip_id || 0;
            tr.dataset.paymentStatus = row.existing_payment_status || '';
            tr.dataset.basic = row.basic_salary;
            tr.dataset.dailySalary = row.daily_salary;
            tr.dataset.applyDeduction = row.apply_deduction;
            tr.dataset.lateMinutesPer = row.late_minutes_per_occurrence || 0;
            tr.dataset.attBonus = row.attendance_bonus;
            tr.dataset.otherBonus = row.other_bonus;
            tr.dataset.security = row.security_deduction;
            tr.dataset.advance = row.advance_deduction;

            var statusHtml = isPaid
                ? '<span class="badge text-bg-success">Paid</span>'
                : (row.has_existing_slip
                    ? '<span class="badge text-bg-warning">Generated</span>'
                    : '<span class="badge text-bg-secondary">Pending</span>');
            if (row.existing_slip_id) {
                statusHtml += '<br><a href="' + viewSlipBaseUrl + '/' + row.user_id + '/' + row.existing_slip_id + '" target="_blank" class="small">View slip</a>';
            }

            var ro = isPaid ? ' readonly' : '';
            var chk = isPaid ? '' : ' checked';
            var chkDis = isPaid ? ' disabled title="Paid slips cannot be regenerated"' : '';

            tr.innerHTML =
                '<td><input type="checkbox" class="row-check"' + chk + chkDis + '></td>' +
                '<td><strong>' + escapeHtml(row.name) + '</strong>' + (row.designation ? '<br><small class="text-muted">' + escapeHtml(row.designation) + '</small>' : '') + '</td>' +
                '<td class="text-end col-basic">' + fmt(row.basic_salary) + '</td>' +
                '<td><input type="number" min="0" step="1" class="form-control form-control-sm text-center count-input cnt-off" value="' + (row.off_days || 0) + '"' + ro + '></td>' +
                '<td><input type="number" min="0" step="1" class="form-control form-control-sm text-center count-input cnt-leave" value="' + (row.leave_days || 0) + '"' + ro + '></td>' +
                '<td><input type="number" min="0" step="1" class="form-control form-control-sm text-center count-input cnt-late" value="' + (row.late_count || 0) + '"' + ro + '></td>' +
                '<td><input type="number" min="0" step="1" class="form-control form-control-sm text-center count-input cnt-early" value="' + (row.early_left_count || 0) + '"' + ro + '></td>' +
                '<td class="text-end total-deductions fw-bold text-danger">' + fmt(row.total_full_deductions || row.total_attendance_deductions || 0) + '</td>' +
                '<td class="text-end net-salary fw-bold">' + fmt(row.net_salary) + '</td>' +
                '<td class="slip-status">' + statusHtml + '</td>';

            tbody.appendChild(tr);
            bindRowEvents(tr);
        });

        document.getElementById('bulkTableCard').style.display = '';
        document.getElementById('emptyState').style.display = 'none';
        document.getElementById('checkAll').checked = true;
        updateTotals();
        updateActionLinks();
    }

    function escapeHtml(str) {
        return String(str || '').replace(/[&<>"']/g, function (m) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
        });
    }

    document.getElementById('filterForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = document.getElementById('loadBtn');
        btn.disabled = true;
        var fd = new FormData();
        fd.append('year', document.getElementById('filterYear').value);
        fd.append('month', document.getElementById('filterMonth').value);
        fd.append(csrfName, csrfHash);

        fetch(loadUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    alert(data.msg || 'Failed to load employees');
                    return;
                }
                renderRows(data.rows, data.calc_settings);
            })
            .catch(function () { alert('Failed to load employees'); })
            .finally(function () { btn.disabled = false; });
    });

    document.getElementById('checkAll').addEventListener('change', function () {
        document.querySelectorAll('.row-check:not(:disabled)').forEach(function (cb) {
            cb.checked = document.getElementById('checkAll').checked;
        });
    });

    document.getElementById('selectAllBtn').addEventListener('click', function () {
        document.querySelectorAll('.row-check:not(:disabled)').forEach(function (cb) { cb.checked = true; });
        document.getElementById('checkAll').checked = true;
    });

    document.getElementById('deselectAllBtn').addEventListener('click', function () {
        document.querySelectorAll('.row-check:not(:disabled)').forEach(function (cb) { cb.checked = false; });
        document.getElementById('checkAll').checked = false;
    });

    function submitGenerate(regenerate) {
        var year = document.getElementById('filterYear').value;
        var month = document.getElementById('filterMonth').value;
        var employees = [];
        document.querySelectorAll('#bulkSalaryBody tr.bulk-row').forEach(function (tr) {
            var cb = tr.querySelector('.row-check');
            if (!cb || !cb.checked || cb.disabled) return;
            employees.push({
                selected: 1,
                user_id: tr.dataset.userId,
                off_days: tr.querySelector('.cnt-off').value,
                leave_days: tr.querySelector('.cnt-leave').value,
                late_count: tr.querySelector('.cnt-late').value,
                early_left_count: tr.querySelector('.cnt-early').value,
                late_minutes_per_occurrence: tr.dataset.lateMinutesPer || 0
            });
        });

        if (!employees.length) {
            alert(regenerate
                ? 'Select at least one employee with a pending (non-paid) slip to regenerate.'
                : 'Select at least one employee without a paid slip.');
            return;
        }

        var action = regenerate ? 'Regenerate' : 'Generate';
        if (!confirm(action + ' salary for ' + employees.length + ' selected employee(s)?')) {
            return;
        }

        var btn = regenerate ? document.getElementById('regenerateBtn') : document.getElementById('generateBtn');
        btn.disabled = true;
        var fd = new FormData();
        fd.append('year', year);
        fd.append('month', month);
        if (regenerate) fd.append('regenerate', '1');
        fd.append(csrfName, csrfHash);
        employees.forEach(function (emp, i) {
            Object.keys(emp).forEach(function (k) {
                fd.append('employees[' + i + '][' + k + ']', emp[k]);
            });
        });

        fetch(generateUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                alert(data.msg || (data.success ? 'Done' : 'Failed'));
                if (data.success) {
                    document.getElementById('filterForm').dispatchEvent(new Event('submit'));
                }
            })
            .catch(function () { alert('Request failed'); })
            .finally(function () { btn.disabled = false; });
    }

    document.getElementById('generateBtn').addEventListener('click', function () { submitGenerate(false); });
    document.getElementById('regenerateBtn').addEventListener('click', function () { submitGenerate(true); });

    document.getElementById('filterYear').addEventListener('change', updateActionLinks);
    document.getElementById('filterMonth').addEventListener('change', updateActionLinks);

    document.querySelectorAll('#bulkSalaryBody tr.bulk-row').forEach(function (tr) {
        bindRowEvents(tr);
        recalcRow(tr);
    });
    updateActionLinks();
    if (document.querySelectorAll('#bulkSalaryBody tr.bulk-row').length) {
        updateTotals();
    }
})();
</script>

<?= $this->endSection() ?>
