<?= $this->extend('layouts/admin_template') ?>



<?= $this->section('pageStyles') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/timetable-report-print.css') ?>">

<?= $this->endSection() ?>



<?= $this->section('content') ?>



<section class="content-header no-print">

    <div class="container-fluid">

        <div class="row mb-2">

            <div class="col-sm-6">

                <h1 class="m-0">Timetable Report</h1>

            </div>

            <div class="col-sm-6">

                <ol class="breadcrumb float-sm-right">

                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>

                    <li class="breadcrumb-item"><a href="<?= base_url('admin/timetable/index') ?>">Timetable</a></li>

                    <li class="breadcrumb-item active">Report</li>

                </ol>

            </div>

        </div>

    </div>

</section>



<section class="content">

    <div class="container-fluid">

        <div class="card card-primary card-outline no-print">

            <div class="card-header">

                <h3 class="card-title">Class-wise / Teacher-wise Timetable</h3>

            </div>

            <div class="card-body">

                <div class="alert alert-light border small mb-3">

                    <?php $tname = $timing_type_name ?? ''; ?>

                    <?php if ($tname !== ''): ?>

                        <div><strong>Active timing type:</strong> <?= esc($tname) ?></div>

                    <?php endif; ?>

                    <div class="mt-1 text-muted">

                        Report columns include only <strong>working days</strong> for the chosen class or teacher: days where that section’s school timing has <strong>different check-in and check-out</strong>. Saturday or Sunday appear only when that section’s timing marks them as working days—not because another class has them on.

                    </div>

                </div>

                <div class="row">

                    <div class="col-md-3">

                        <div class="custom-control custom-radio mb-2">

                            <input class="custom-control-input" type="radio" id="modeClass" name="reportMode" value="class" checked>

                            <label for="modeClass" class="custom-control-label">Class-wise</label>

                        </div>

                        <div class="custom-control custom-radio">

                            <input class="custom-control-input" type="radio" id="modeTeacher" name="reportMode" value="teacher">

                            <label for="modeTeacher" class="custom-control-label">Teacher-wise</label>

                        </div>

                    </div>

                    <div class="col-md-4" id="classWrap">

                        <label for="clsSecId">Class Section</label>

                        <select id="clsSecId" class="form-control">

                            <option value="">-- Select Class Section --</option>

                            <option value="all">All class sections</option>

                            <?php foreach (($sections ?? []) as $s): ?>

                                <option value="<?= esc($s['cls_sec_id']) ?>">

                                    <?= esc($s['class_name'] . ' - ' . $s['section_name']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="col-md-4 d-none" id="teacherWrap">

                        <label for="teacherId">Teacher</label>

                        <select id="teacherId" class="form-control">

                            <option value="">-- Select Teacher --</option>

                            <option value="all">All teachers</option>

                            <?php foreach (($teachers ?? []) as $t): ?>

                                <option value="<?= esc($t['id']) ?>">

                                    <?= esc(trim(($t['first_name'] ?? '') . ' ' . ($t['last_name'] ?? ''))) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="col-md-1 d-flex align-items-end">

                        <button id="btnLoadReport" type="button" class="btn btn-primary btn-block">Load</button>

                    </div>

                </div>

                <div class="row mt-3 align-items-center">

                    <div class="col-md-auto">

                        <div class="custom-control custom-checkbox mb-2 mb-md-0">

                            <input type="checkbox" class="custom-control-input" id="chkShowSlotTime" value="1">

                            <label class="custom-control-label" for="chkShowSlotTime">Show slot times</label>

                        </div>

                        <small class="text-muted d-block">Default uses slot numbers (Slot 1, Slot 2, …).</small>

                    </div>

                    <div class="col-md-auto">

                        <div class="custom-control custom-checkbox mb-2 mb-md-0">

                            <input type="checkbox" class="custom-control-input" id="chkDetailInline" value="1" checked>

                            <label class="custom-control-label" for="chkDetailInline" id="lblDetailInline">Show teacher with subject (same line)</label>

                        </div>

                        <small class="text-muted d-block" id="hintDetailInline">Uncheck for subject only (class-wise) or subject + class on separate lines (teacher-wise).</small>

                    </div>

                </div>

                <div class="row mt-2 align-items-center flex-wrap">

                    <div class="col-md d-flex flex-wrap justify-content-md-end">
                        <button id="btnPrintReport" type="button" class="btn btn-secondary btn-sm mr-2 mb-2 mb-md-0" disabled title="Print A4 landscape">
                            <i class="fas fa-print"></i> Print report
                        </button>
                        <button id="btnExportPdf" type="button" class="btn btn-outline-danger btn-sm mr-2 mb-2 mb-md-0" disabled>
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </button>
                        <button id="btnExportExcel" type="button" class="btn btn-outline-success btn-sm mb-2 mb-md-0" disabled>
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                    </div>

                </div>

                <p class="small text-muted mt-2 mb-0"><strong>Print:</strong> Load the report first, then use <em>Print report</em> or the Print button on the grid. Use paper <strong>A4</strong>, orientation <strong>Landscape</strong>, margins default or “Minimum”.</p>

            </div>

        </div>



        <div id="reportContainer" class="tt-print-report-root"></div>

    </div>

</section>



<script>

$(function () {

    let lastMode = 'class';

    let lastClassId = '';

    let lastTeacherId = '';



    function syncMode() {

        const mode = $('input[name="reportMode"]:checked').val();

        lastMode = mode;

        if (mode === 'class') {

            $('#classWrap').removeClass('d-none');

            $('#teacherWrap').addClass('d-none');

            $('#lblDetailInline').text('Show teacher with subject (same line)');

            $('#hintDetailInline').text('Uncheck for subject name only (no teacher).');

        } else {

            $('#classWrap').addClass('d-none');

            $('#teacherWrap').removeClass('d-none');

            $('#lblDetailInline').text('Show class with subject (same line)');

            $('#hintDetailInline').text('Uncheck to show class section under the subject.');

        }

    }



    $('input[name="reportMode"]').on('change', syncMode);

    syncMode();



    $('#btnLoadReport').on('click', function () {

        const mode = $('input[name="reportMode"]:checked').val();

        const payload = { mode: mode };

        lastMode = mode;



        if (mode === 'class') {

            payload.cls_sec_id = $('#clsSecId').val();

            lastClassId = payload.cls_sec_id;

            lastTeacherId = '';

            if (!payload.cls_sec_id) {

                toastr.warning('Please select a class section or All class sections.');

                return;

            }

        } else {

            payload.teacher_id = $('#teacherId').val();

            lastTeacherId = payload.teacher_id;

            lastClassId = '';

            if (!payload.teacher_id) {

                toastr.warning('Please select a teacher or All teachers.');

                return;

            }

        }



        payload.show_slot_time = $('#chkShowSlotTime').is(':checked') ? 1 : 0;

        payload.show_teacher_with_subject = $('#chkDetailInline').is(':checked') ? 1 : 0;



        $('#reportContainer').html('<div class="alert alert-info">Loading report...</div>');



        $.ajax({

            url: "<?= base_url('admin/timetable/report-data') ?>",

            method: "POST",

            dataType: "json",

            data: payload,

            success: function (res) {

                if (res && res.success) {

                    $('#reportContainer').html(res.html || '<div class="alert alert-warning">No data.</div>');

                    $('#btnExportPdf, #btnExportExcel, #btnPrintReport').prop('disabled', false);

                } else {

                    $('#reportContainer').html('<div class="alert alert-danger">' + (res.msg || 'Failed to load report.') + '</div>');

                    $('#btnExportPdf, #btnExportExcel, #btnPrintReport').prop('disabled', true);

                }

            },

            error: function () {

                $('#reportContainer').html('<div class="alert alert-danger">Server error while loading report.</div>');

                $('#btnExportPdf, #btnExportExcel, #btnPrintReport').prop('disabled', true);

            }

        });

    });



    $('#btnPrintReport').on('click', function () {

        if ($(this).prop('disabled')) return;

        window.print();

    });



    function buildExportUrl(format) {

        const params = new URLSearchParams();

        params.set('mode', lastMode);

        params.set('format', format);

        if (lastMode === 'class') {

            params.set('cls_sec_id', lastClassId || '');

        }

        if (lastMode === 'teacher') {

            params.set('teacher_id', lastTeacherId || '');

        }

        params.set('show_slot_time', $('#chkShowSlotTime').is(':checked') ? '1' : '0');

        params.set('show_teacher_with_subject', $('#chkDetailInline').is(':checked') ? '1' : '0');

        return "<?= base_url('admin/timetable/report-export') ?>?" + params.toString();

    }



    $('#btnExportPdf').on('click', function () {

        if ($(this).prop('disabled')) return;

        window.open(buildExportUrl('pdf'), '_blank');

    });



    $('#btnExportExcel').on('click', function () {

        if ($(this).prop('disabled')) return;

        window.open(buildExportUrl('excel'), '_blank');

    });

});

</script>



<?= $this->endSection() ?>

