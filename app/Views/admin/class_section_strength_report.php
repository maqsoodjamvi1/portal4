<?php $uiNeedsDataTables = false; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Class / Section Strength Report',
    'icon' => 'fas fa-users',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Strength Report', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="container-fluid">
        <div class="card sms-card card-primary card-outline shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users me-2"></i>Report Filters</h3>
            </div>
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <div class="form-group mb-md-0">
                            <label>View Type <span class="text-danger">*</span></label>
                            <div class="btn-group btn-group-toggle d-flex" data-bs-toggle="buttons">
                                <label class="btn btn-outline-primary active flex-fill mb-0">
                                    <input type="radio" name="view_mode" value="class" checked> Class Wise
                                </label>
                                <label class="btn btn-outline-primary flex-fill mb-0">
                                    <input type="radio" name="view_mode" value="section"> Section Wise
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-md-0">
                            <label for="session_id">Academic Session <span class="text-danger">*</span></label>
                            <select id="session_id" class="form-control" required>
                                <option value="">Select Session</option>
                                <?php foreach ($sessions ?? [] as $session): ?>
                                    <?php
                                    $sid = (int) ($session['session_id'] ?? 0);
                                    $label = trim((string) ($session['session_name'] ?? ('Session ' . $sid)));
                                    ?>
                                    <option value="<?= $sid ?>" <?= $sid === (int) ($currentSessionId ?? 0) ? 'selected' : '' ?>>
                                        <?= esc($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-success w-100 mb-2" id="generateStrengthReportBtn">
                            <i class="fas fa-chart-bar me-1"></i> Generate Report
                        </button>
                        <button type="button" class="btn btn-outline-success w-100 mb-2" id="exportStrengthReportBtn">
                            <i class="fas fa-file-csv me-1"></i> Export CSV
                        </button>
                        <button type="button" class="btn btn-primary w-100 d-none" id="printStrengthReportBtnTop">
                            <i class="fas fa-print me-1"></i> Print Report
                        </button>
                    </div>
                </div>

                <div class="position-relative mt-4" style="min-height:120px;">
                    <div id="strengthReportLoader" class="text-center py-5 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="strengthReportContainer"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.strength-report-table .col-num {
    width: 48px;
}

@media print {
    .content-header,
    .card-header,
    .no-print,
    .main-sidebar,
    .main-header,
    .main-footer,
    .breadcrumb,
    .sidebar-controls {
        display: none !important;
    }

    .content-wrapper,
    .content {
        margin: 0 !important;
        padding: 0 !important;
    }

    .card {
        border: 0 !important;
        box-shadow: none !important;
    }

    .card-body {
        padding: 0 !important;
    }

    .strength-report-print-header {
        display: block !important;
    }

    .strength-report-table {
        font-size: 11px;
    }
}
</style>

<script>
(function () {
    const dataUrl = <?= json_encode(site_url('admin/class-section-strength-report/data')) ?>;
    const exportUrl = <?= json_encode(site_url('admin/class-section-strength-report/export')) ?>;
    const csrfName = <?= json_encode(csrf_token()) ?>;
    const csrfHash = <?= json_encode(csrf_hash()) ?>;

    function selectedViewMode() {
        return $('input[name="view_mode"]:checked').val() || 'class';
    }

    function printReport() {
        window.print();
    }

    function loadReport() {
        const sessionId = $('#session_id').val();
        const viewMode  = selectedViewMode();

        if (!sessionId) {
            toastr.warning('Please select an academic session.');
            return;
        }

        $('#strengthReportLoader').removeClass('d-none');
        $('#strengthReportContainer').empty();
        $('#printStrengthReportBtnTop').addClass('d-none');

        $.ajax({
            url: dataUrl,
            type: 'POST',
            data: {
                session_id: sessionId,
                view_mode: viewMode,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            success: function (html) {
                $('#strengthReportContainer').html(html);
                $('#printStrengthReportBtnTop').removeClass('d-none');
            },
            error: function () {
                $('#strengthReportContainer').html(
                    '<div class="alert alert-danger mb-0">Failed to load report. Please try again.</div>'
                );
            },
            complete: function () {
                $('#strengthReportLoader').addClass('d-none');
            }
        });
    }

    $('#generateStrengthReportBtn').on('click', loadReport);

    $('#exportStrengthReportBtn').on('click', function () {
        const sessionId = $('#session_id').val();
        const viewMode  = selectedViewMode();
        if (!sessionId) {
            toastr.warning('Please select an academic session.');
            return;
        }
        const $form = $('<form method="post"></form>').attr('action', exportUrl);
        $form.append($('<input type="hidden">').attr('name', csrfName).val(csrfHash));
        $form.append($('<input type="hidden" name="session_id">').val(sessionId));
        $form.append($('<input type="hidden" name="view_mode">').val(viewMode));
        $('body').append($form);
        $form.trigger('submit');
        $form.remove();
    });

    $(document).on('click', '#printStrengthReportBtn, #printStrengthReportBtnTop', function () {
        printReport();
    });

    $('input[name="view_mode"]').on('change', function () {
        if ($('#strengthReportContainer').children().length) {
            loadReport();
        }
    });
})();
</script>

<?= $this->endSection() ?>
