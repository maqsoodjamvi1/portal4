<?= $this->extend('layouts/admin_template') ?>



<?= $this->section('pageStyles') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/timetable-report-print.css') ?>">

<?= $this->endSection() ?>



<?= $this->section('content') ?>



<div class="no-print">
<?= view('components/page_header', [
    'title' => 'Timetable Report',
    'icon' => 'fas fa-table',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Timetable', 'url' => base_url('admin/timetable/index')],
        ['label' => 'Report', 'active' => true],
    ],
]) ?>
</div>



<section class="content">

    <div class="container-fluid">

        <div class="card card-primary card-outline no-print">

            <div class="card-header">

                <h3 class="card-title">Class-wise / Teacher-wise Timetable</h3>

            </div>

            <div class="card-body py-3">
                <div class="row align-items-end">
                    <div class="col-lg-auto mb-2 mb-lg-0">
                        <label class="d-block small text-muted mb-1">Report type</label>
                        <div class="d-flex flex-wrap">
                            <div class="form-check form-check form-check-inline me-3">
                                <input class="form-check-input" type="radio" id="modeClass" name="reportMode" value="class" checked>
                                <label for="modeClass" class="form-check-label">Class</label>
                            </div>
                            <div class="form-check form-check form-check-inline">
                                <input class="form-check-input" type="radio" id="modeTeacher" name="reportMode" value="teacher">
                                <label for="modeTeacher" class="form-check-label">Teacher</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-5 mb-2 mb-lg-0" id="classWrap">
                        <label for="clsSecId" class="small text-muted mb-1">Class section</label>
                        <select id="clsSecId" class="form-control form-control-sm">
                            <option value="">-- Select --</option>
                            <option value="all">All timetable sections</option>
                            <?php foreach (($sections ?? []) as $s): ?>
                                <option value="<?= esc($s['cls_sec_id']) ?>">
                                    <?= esc($s['class_name'] . ' - ' . $s['section_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-5 mb-2 mb-lg-0 d-none" id="teacherWrap">
                        <label for="teacherId" class="small text-muted mb-1">Teacher</label>
                        <select id="teacherId" class="form-control form-control-sm">
                            <option value="">-- Select --</option>
                            <option value="all">All teachers (with assignments)</option>
                            <?php foreach (($teachers ?? []) as $t): ?>
                                <option value="<?= esc($t['id']) ?>">
                                    <?= esc(trim(($t['first_name'] ?? '') . ' ' . ($t['last_name'] ?? ''))) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-auto mb-2 mb-lg-0">
                        <button id="btnLoadReport" type="button" class="btn btn-primary btn-sm w-100">Load</button>
                    </div>
                </div>

                <div class="row align-items-center mt-3 pt-2 border-top">
                    <div class="col-lg-8 mb-2 mb-lg-0 d-flex flex-wrap align-items-center">
                        <div class="form-check form-check form-check-inline me-3 mb-1">
                            <input type="checkbox" class="form-check-input" id="chkShowSlotTime" value="1">
                            <label class="form-check-label" for="chkShowSlotTime">Slot times</label>
                            <i class="fas fa-info-circle text-muted ms-1 tt-report-tip" data-bs-toggle="tooltip" data-bs-placement="top" title="Show clock times in the slot column instead of Slot 1, Slot 2, …"></i>
                        </div>
                        <div class="form-check form-check form-check-inline me-3 mb-1">
                            <input type="checkbox" class="form-check-input" id="chkDetailInline" value="1" checked>
                            <label class="form-check-label" for="chkDetailInline" id="lblDetailInline">Teacher with subject</label>
                            <i class="fas fa-info-circle text-muted ms-1 tt-report-tip" id="tipDetailInline" data-bs-toggle="tooltip" data-bs-placement="top" title="Include teacher name beside subject (class-wise). Teacher-wise: subject and class on one line."></i>
                        </div>
                    </div>
                    <div class="col-lg-4 d-flex flex-wrap justify-content-lg-end">
                        <button id="btnPrintReport" type="button" class="btn btn-secondary btn-sm me-1 mb-1" disabled
                            data-bs-toggle="tooltip" data-bs-placement="top" title="A4 portrait — one timetable per page. Load the report first.">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <button id="btnExportPdf" type="button" class="btn btn-outline-danger btn-sm me-1 mb-1" disabled
                            data-bs-toggle="tooltip" data-bs-placement="top" title="Download PDF (portrait). Load the report first.">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                        <button id="btnExportExcel" type="button" class="btn btn-outline-success btn-sm mb-1" disabled
                            data-bs-toggle="tooltip" data-bs-placement="top" title="Download Excel. Load the report first.">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>
            </div>

        </div>



        <div id="adjustContainer" class="d-none"></div>

        <div id="reportContainer" class="tt-print-report-root"></div>

    </div>

</section>



<style>
.tt-adjust-wrap { display: flex; gap: 16px; align-items: flex-start; }
.tt-adjust-pool {
    flex: 0 0 280px;
    max-width: 280px;
    position: sticky;
    top: 12px;
}
.tt-adjust-grid-panel { flex: 1 1 auto; min-width: 0; position: relative; }
.tt-adjust-grid-wrap { position: relative; }
.tt-adjust-grid-loader {
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.85);
    z-index: 30;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
}
.tt-adjust-grid-loader-inner { text-align: center; padding: 16px; }
.tt-adjust-pool.is-loading { opacity: 0.55; pointer-events: none; }
.tt-adjust-cell.is-feasible-replace { position: relative; }
.tt-adjust-cell.is-feasible-replace::before {
    content: '\f362';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    position: absolute;
    top: 4px;
    left: 6px;
    color: #28a745;
    font-size: 11px;
    line-height: 1;
    pointer-events: none;
}
.tt-teacher-mini-grid-wrap {
    position: relative;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    background: #f8f9fa;
    padding: 10px;
    margin-bottom: 12px;
}
.tt-teacher-mini-grid-wrap.is-active { border-color: #17a2b8; box-shadow: 0 0 0 1px rgba(23,162,184,.15); }
.tt-teacher-mini-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}
.tt-teacher-mini-header .teacher-name { font-weight: 700; color: #17a2b8; }
.tt-teacher-mini-hint { font-size: 0.75rem; color: #6c757d; }
.tt-teacher-mini-grid { font-size: 0.78rem; margin-bottom: 0; }
.tt-teacher-cell {
    min-height: 44px;
    vertical-align: top;
    padding: 4px 6px !important;
}
.tt-teacher-cell.is-free { background: #fff; color: #adb5bd; }
.tt-teacher-cell.is-friday-inactive {
    background: repeating-linear-gradient(-45deg, #f1f3f5, #f1f3f5 4px, #e9ecef 4px, #e9ecef 8px);
    color: #adb5bd;
}
.tt-teacher-cell.is-busy { background: #fff8e1; cursor: grab; }
.tt-teacher-cell.is-busy:active { cursor: grabbing; }
.tt-teacher-cell.is-busy.is-drag-disabled { cursor: not-allowed; opacity: 0.7; }
.tt-teacher-cell.is-current-section { box-shadow: inset 0 0 0 2px #17a2b8; }
.tt-teacher-cell .tt-tcell-subject { font-weight: 600; color: #007bff; line-height: 1.15; }
.tt-teacher-cell .tt-tcell-class { font-size: 0.72rem; color: #6c757d; line-height: 1.15; margin-top: 2px; }
.tt-teacher-mini-loader {
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,.75);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
    border-radius: 6px;
}
.tt-teacher-mini-empty { font-size: 0.85rem; color: #6c757d; padding: 8px 0; }
.tt-adjust-cell.is-slot-active { box-shadow: inset 0 0 0 2px #17a2b8; }
.tt-adjust-cell .tt-cell-clear {
    position: absolute;
    top: 2px;
    right: 4px;
    border: none;
    background: transparent;
    color: #dc3545;
    font-size: 0.75rem;
    line-height: 1;
    padding: 0 2px;
    cursor: pointer;
    opacity: 0.55;
    z-index: 1;
}
.tt-adjust-cell .tt-cell-clear:hover { opacity: 1; }
.tt-adjust-cell.is-clearable { position: relative; }
.tt-adjust-pool-item {
    cursor: grab;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 8px 10px;
    margin-bottom: 8px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    user-select: none;
}
.tt-adjust-pool-item:active { cursor: grabbing; }
.tt-adjust-pool-item.dragging { opacity: 0.45; }
.tt-adjust-pool-item.active { border-color: #007bff; background: #e7f1ff; }
.tt-adjust-pool-item .pool-count {
    min-width: 28px;
    text-align: center;
    font-weight: 700;
    border-radius: 12px;
    background: #ffc107;
    color: #212529;
    padding: 2px 8px;
    font-size: 0.85rem;
}
.tt-adjust-cell {
    cursor: default;
    min-height: 52px;
    vertical-align: top;
    transition: background-color 0.15s ease, outline-color 0.15s ease;
}
.tt-adjust-cell.is-feasible { background: #e8f5e9 !important; }
.tt-adjust-cell.is-blocked {
    background: #fdecea !important;
    position: relative;
}
.tt-adjust-cell.is-blocked-teacher { background: #fff3e0 !important; }
.tt-adjust-cell.is-blocked-occupied { background: #fdecea !important; }
.tt-adjust-cell.is-blocked::after {
    content: '\2715';
    position: absolute;
    top: 4px;
    right: 6px;
    color: #dc3545;
    font-weight: 700;
    font-size: 14px;
    line-height: 1;
    pointer-events: none;
}
.tt-adjust-cell.is-friday-inactive {
    background: repeating-linear-gradient(-45deg, #f4f4f4, #f4f4f4 6px, #e9ecef 6px, #e9ecef 12px) !important;
}
.tt-adjust-cell.is-friday-inactive:not(.is-clearable) { cursor: not-allowed !important; }
.tt-friday-na { color: #6c757d; font-size: 0.75rem; }
.tt-adjust-legend { font-size: 0.8rem; }
.tt-adjust-legend .leg-ok { color: #28a745; }
.tt-adjust-legend .leg-no { color: #dc3545; }
.tt-adjust-legend .leg-fri { color: #6c757d; }
.tt-adjust-cell.is-drag-over { outline: 2px dashed #007bff !important; background: #e7f1ff !important; }
.tt-adjust-cell.is-drag-over-force { outline: 2px dashed #fd7e14 !important; background: #fff8e1 !important; }
.tt-adjust-teacher-banner { border-start: 4px solid #17a2b8; }
.tt-adjust-cell.is-empty { background: #fafafa; cursor: pointer; }
.tt-adjust-cell.is-clearable { cursor: pointer; }
.tt-adjust-cell.is-clearable:hover { background: #f8fbff !important; }
.tt-force-effect-remove { color: #dc3545; }
.tt-force-effect-move { color: #fd7e14; }
.tt-force-effect-add { color: #28a745; }
.tt-force-effect-warning { color: #856404; }
#ttForceModal .modal-body { padding-top: 0.75rem; }
.tt-force-slot-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #e9ecef;
    border-radius: 999px;
    padding: 4px 12px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 14px;
}
.tt-force-swap {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 12px;
}
.tt-force-card {
    flex: 1 1 120px;
    max-width: 160px;
    min-width: 110px;
    border-radius: 8px;
    padding: 10px 12px;
    text-align: center;
    border: 2px solid transparent;
}
.tt-force-card-out {
    background: #fff5f5;
    border-color: #f5c6cb;
}
.tt-force-card-in {
    background: #e8f5e9;
    border-color: #c3e6cb;
}
.tt-force-card-other {
    background: #fff8e1;
    border-color: #ffecb3;
}
.tt-force-subject {
    font-weight: 700;
    color: #007bff;
    font-size: 0.95rem;
    line-height: 1.2;
}
.tt-force-meta {
    font-size: 0.78rem;
    color: #6c757d;
    margin-top: 4px;
    line-height: 1.2;
}
.tt-force-arrow {
    color: #6c757d;
    font-size: 1.25rem;
    flex: 0 0 auto;
}
.tt-force-pool-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: center;
    padding-top: 4px;
}
.tt-force-pool-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #fff3cd;
    border: 1px solid #ffeeba;
    border-radius: 999px;
    padding: 4px 10px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #856404;
}
.tt-force-pool-chip i { font-size: 0.75rem; }
.tt-force-hint {
    text-align: center;
    font-size: 0.78rem;
    color: #6c757d;
    margin-bottom: 8px;
}
.tt-adjust-cell .tt-cell-subject { font-weight: 600; color: #007bff; }
.tt-adjust-cell .tt-cell-teacher { font-size: 0.8rem; color: #6c757d; }
.tt-report-block.is-adjust-active .tt-report-table-card {
    border-color: #ffc107;
    box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.35);
}
.tt-report-block.is-adjust-dimmed {
    opacity: 0.45;
}
.btn-section-adjust.is-editing {
    pointer-events: none;
}
@media (max-width: 992px) {
    .tt-adjust-wrap { flex-direction: column; }
    .tt-adjust-pool { flex: 1 1 auto; max-width: 100%; position: static; width: 100%; }
}
</style>



<script>

$(function () {
    function initReportTooltips() {
        const $els = $('.tt-report-tip, #btnPrintReport, #btnExportPdf, #btnExportExcel, .btn-section-adjust');
        $els.each(function () {
            const $t = $(this);
            if ($t.data('bs.tooltip')) {
                $t.tooltip('dispose');
            }
        });
        $els.tooltip({ container: 'body', trigger: 'hover' });
    }

    initReportTooltips();

    let pendingAdjustClsSecId = 0;
    let pendingAdjustAutoLoad = false;
    (function applyReportQueryParams() {
        const params = new URLSearchParams(window.location.search);
        if (params.get('adjust') !== '1') {
            return;
        }
        $('#modeClass').prop('checked', true);
        syncMode();
        const cid = Number(params.get('cls_sec_id') || 0);
        if (cid > 0) {
            pendingAdjustClsSecId = cid;
            $('#clsSecId').val(String(cid));
        } else if (!$('#clsSecId').val()) {
            $('#clsSecId').val('all');
        }
        pendingAdjustAutoLoad = true;
    })();

    let lastMode = 'class';
    let lastClassId = '';
    let lastTeacherId = '';
    let adjustState = {
        data: null,
        dragSubjectId: null,
        dragFromTeacher: null,
        feasibleKeys: {},
        blockedKeys: {},
        blockedCodeKeys: {},
        caseKeys: {},
        fridayInactiveKeys: {},
        selectedSubjectName: '',
        selectedTeacherName: '',
        selectedTeacherId: 0,
        activeTeacherId: 0,
        activeSlotKey: '',
        pendingForce: null,
        isLoading: false,
        teacherGridLoading: false,
        activeClsSecId: 0
    };

    const reportDataUrl = "<?= base_url('admin/timetable/report-data') ?>";
    const adjustDataUrl = "<?= base_url('admin/timetable/report-adjust-data') ?>";
    const adjustFeasibleUrl = "<?= base_url('admin/timetable/report-adjust-feasible') ?>";
    const adjustPlaceUrl = "<?= base_url('admin/timetable/report-adjust-place') ?>";
    const adjustClearUrl = "<?= base_url('admin/timetable/report-adjust-clear') ?>";
    const adjustTeacherUrl = "<?= base_url('admin/timetable/report-adjust-teacher') ?>";



    function syncMode() {

        const mode = $('input[name="reportMode"]:checked').val();

        lastMode = mode;

        if (mode === 'class') {

            $('#classWrap').removeClass('d-none');

            $('#teacherWrap').addClass('d-none');

            $('#lblDetailInline').text('Teacher with subject');
            $('#tipDetailInline').attr('title', 'Include teacher name beside subject. Uncheck for subject name only.')
                .tooltip('dispose').tooltip({ container: 'body', trigger: 'hover' });

        } else {

            $('#classWrap').addClass('d-none');

            $('#teacherWrap').removeClass('d-none');

            $('#lblDetailInline').text('Class with subject');
            $('#tipDetailInline').attr('title', 'Subject and class on one line. Uncheck for subject with class below.')
                .tooltip('dispose').tooltip({ container: 'body', trigger: 'hover' });

        }

    }



    $('input[name="reportMode"]').on('change', function () {
        syncMode();
        closeAdjustPanel();
    });

    syncMode();

    function reportDisplayPayload(clsSecId) {
        return {
            mode: 'class',
            cls_sec_id: clsSecId,
            show_slot_time: $('#chkShowSlotTime').is(':checked') ? 1 : 0,
            show_teacher_with_subject: $('#chkDetailInline').is(':checked') ? 1 : 0
        };
    }

    function updateSectionAdjustButtons() {
        const active = Number(adjustState.activeClsSecId || 0);
        $('.tt-report-block').each(function () {
            const cid = Number($(this).data('cls-sec-id') || 0);
            const isActive = active > 0 && cid === active;
            $(this).toggleClass('is-adjust-active', isActive);
            $(this).toggleClass('is-adjust-dimmed', active > 0 && !isActive);
        });
        $('.btn-section-adjust').each(function () {
            const cid = Number($(this).data('cls-sec-id') || 0);
            const isActive = active > 0 && cid === active;
            $(this).toggleClass('is-editing', isActive);
            if (isActive) {
                $(this).html('<i class="fas fa-edit"></i> Editing…');
            } else {
                $(this).html('<i class="fas fa-sliders-h"></i> Adjust');
            }
        });
    }

    function closeAdjustPanel() {
        adjustState.activeClsSecId = 0;
        adjustState.data = null;
        clearSlotHighlights();
        $('#adjustContainer').addClass('d-none').empty();
        updateSectionAdjustButtons();
        const hasReport = $('#reportContainer .tt-report-block').length > 0 || $('#reportContainer .tt-print-sheet').length > 0;
        if (hasReport) {
            $('#btnExportPdf, #btnExportExcel, #btnPrintReport').prop('disabled', false);
        }
    }

    function openAdjustForSection(clsSecId) {
        const cid = Number(clsSecId);
        if (!cid) {
            return;
        }
        if (adjustState.activeClsSecId === cid && adjustState.data) {
            $('html, body').animate({ scrollTop: $('#adjustContainer').offset().top - 70 }, 200);
            return;
        }
        adjustState.activeClsSecId = cid;
        lastClassId = String(cid);
        $('#clsSecId').val(String(cid));
        updateSectionAdjustButtons();
        reloadAdjustData(cid);
    }

    function refreshReportSection(clsSecId) {
        const cid = Number(clsSecId);
        if (!cid) {
            return;
        }
        $.ajax({
            url: reportDataUrl,
            method: 'POST',
            dataType: 'json',
            data: reportDisplayPayload(cid)
        }).done(function (res) {
            if (!res || !res.success || !res.html) {
                return;
            }
            const $block = $('.tt-report-block[data-cls-sec-id="' + cid + '"]');
            if ($block.length) {
                $block.replaceWith(res.html);
            } else if ($('#reportContainer .tt-report-block').length <= 1) {
                $('#reportContainer').html(res.html);
            }
            updateSectionAdjustButtons();
            initReportTooltips();
        });
    }

    function escapeHtml(str) {
        return String(str || '').replace(/[&<>"]/g, function (m) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'})[m];
        });
    }

    function cellKey(day, slotId) {
        return String(day) + '|' + Number(slotId);
    }

    function dayShort(day) {
        const map = {
            Monday: 'Mon', Tuesday: 'Tue', Wednesday: 'Wed',
            Thursday: 'Thu', Friday: 'Fri', Saturday: 'Sat', Sunday: 'Sun'
        };
        return map[String(day)] || String(day).substring(0, 3);
    }

    function showAdjustGridLoader(text) {
        adjustState.isLoading = true;
        const $loader = $('#ttAdjustGridLoader');
        if (!$loader.length) {
            return;
        }
        $('#ttAdjustGridLoaderText').text(String(text || 'Please wait…'));
        $loader.removeClass('d-none');
    }

    function hideAdjustGridLoader() {
        adjustState.isLoading = false;
        $('#ttAdjustGridLoader').addClass('d-none');
        $('#adjustContainer .tt-adjust-pool').removeClass('is-loading');
    }

    function showTeacherPanelLoader(msg) {
        const $wrap = $('#ttAdjustTeacherPanel .tt-teacher-mini-grid-wrap');
        if (!$wrap.length) {
            return;
        }
        $wrap.find('.tt-teacher-mini-loader').remove();
        $wrap.append(
            '<div class="tt-teacher-mini-loader"><div class="text-center">' +
            '<i class="fas fa-spinner fa-spin text-info"></i>' +
            '<div class="small text-muted mt-1">' + escapeHtml(msg || 'Loading…') + '</div></div></div>'
        );
        adjustState.teacherGridLoading = true;
    }

    function hideTeacherPanelLoader() {
        $('#ttAdjustTeacherPanel .tt-teacher-mini-loader').remove();
        adjustState.teacherGridLoading = false;
    }

    function teacherCellMapFromResponse(res) {
        const map = {};
        (res && res.cells ? res.cells : []).forEach(function (c) {
            map[cellKey(c.day, c.slot_id)] = c;
        });
        return map;
    }

    function renderTeacherMiniGrid(res) {
        const $panel = $('#ttAdjustTeacherPanel');
        if (!$panel.length) {
            return;
        }
        if (!res || !res.success) {
            $panel.removeClass('is-active').html(
                '<div class="tt-teacher-mini-empty">' +
                escapeHtml((res && res.msg) || 'Could not load teacher timetable.') +
                '</div>'
            );
            return;
        }

        adjustState.activeTeacherId = Number(res.teacher_id || 0);
        const teacherName = String(res.teacher_name || 'Teacher');
        const canDrag = !!adjustState.dragSubjectId;
        const cellMap = teacherCellMapFromResponse(res);
        let tableHtml = '<div class="tt-teacher-mini-grid-wrap is-active">' +
            '<div class="tt-teacher-mini-header">' +
            '<div><span class="teacher-name"><i class="fas fa-chalkboard-teacher me-1"></i>' +
            escapeHtml(teacherName) + '</span></div>' +
            '<div class="tt-teacher-mini-hint">' +
            (canDrag
                ? 'Drag a busy cell onto the class grid to move'
                : 'Select a pool subject to drag busy cells') +
            '</div></div>' +
            '<div class="table-responsive"><table class="table table-bordered table-sm mb-0 tt-teacher-mini-grid">' +
            '<thead class="table-light"><tr><th style="width:70px">Slot</th>';
        (res.days || []).forEach(function (d) {
            tableHtml += '<th>' + escapeHtml(d) + '</th>';
        });
        tableHtml += '</tr></thead><tbody>';

        (res.slots || []).forEach(function (slot, i) {
            const slotId = Number(slot.slot_id || 0);
            tableHtml += '<tr><td class="fw-bold bg-light">S' + (i + 1) + '</td>';
            (res.days || []).forEach(function (day) {
                const c = cellMap[cellKey(day, slotId)] || null;
                const friOff = !!(c && c.friday_inactive);
                let cls = 'tt-teacher-cell';
                let inner = '<span class="text-muted small">Free</span>';
                let dragAttrs = '';
                if (friOff) {
                    cls += ' is-friday-inactive';
                    inner = '<span class="text-muted small">N/A</span>';
                } else if (c && !c.empty) {
                    cls += ' is-busy';
                    if (c.is_current_section) {
                        cls += ' is-current-section';
                    }
                    if (canDrag) {
                        dragAttrs = ' draggable="true"';
                    } else {
                        cls += ' is-drag-disabled';
                    }
                    inner = '<div class="tt-tcell-subject">' + escapeHtml(c.subject_name || '') + '</div>' +
                        '<div class="tt-tcell-class">' + escapeHtml(c.class_label || '') + '</div>';
                    dragAttrs += ' data-cls-sec-id="' + Number(c.cls_sec_id || 0) + '"' +
                        ' data-day="' + escapeHtml(day) + '"' +
                        ' data-slot-id="' + slotId + '"' +
                        ' data-time-table-id="' + Number(c.time_table_id || 0) + '"' +
                        ' data-subject-id="' + Number(c.subject_id || 0) + '"';
                } else {
                    cls += ' is-free';
                }
                tableHtml += '<td class="' + cls + '"' + dragAttrs + '>' + inner + '</td>';
            });
            tableHtml += '</tr>';
        });
        tableHtml += '</tbody></table></div></div>';
        $panel.removeClass('d-none').addClass('is-active').html(tableHtml);
        highlightActiveSlotTeacher();
    }

    function highlightActiveSlotTeacher() {
        $('.tt-adjust-cell').removeClass('is-slot-active');
        if (!adjustState.activeSlotKey) {
            return;
        }
        $('.tt-adjust-cell').each(function () {
            const day = String($(this).data('day') || '');
            const slotId = Number($(this).data('slot-id') || 0);
            if (cellKey(day, slotId) === adjustState.activeSlotKey) {
                $(this).addClass('is-slot-active');
            }
        });
    }

    function showTeacherPanelMessage(msg) {
        const $panel = $('#ttAdjustTeacherPanel');
        if (!$panel.length) {
            return;
        }
        adjustState.activeTeacherId = 0;
        $panel.removeClass('d-none').removeClass('is-active').html(
            '<div class="tt-teacher-mini-empty">' + escapeHtml(msg) + '</div>'
        );
    }

    function loadTeacherTimetable(teacherId, slotKey) {
        if (slotKey !== undefined) {
            adjustState.activeSlotKey = slotKey || '';
        }
        if (!teacherId || !adjustState.data) {
            showTeacherPanelMessage('Select a pool subject or click a class slot to view a teacher timetable.');
            highlightActiveSlotTeacher();
            return;
        }
        showTeacherPanelLoader('Loading teacher timetable…');
        $.ajax({
            url: adjustTeacherUrl,
            method: 'POST',
            dataType: 'json',
            data: {
                teacher_id: Number(teacherId),
                cls_sec_id: Number(adjustState.data.cls_sec_id)
            }
        }).done(function (res) {
            renderTeacherMiniGrid(res);
        }).fail(function (xhr) {
            const msg = (xhr.status === 404)
                ? 'Teacher adjust route not found. Redeploy Routes.php and Timetable.php.'
                : 'Could not load teacher timetable.';
            showTeacherPanelMessage(msg);
        }).always(function () {
            hideTeacherPanelLoader();
        });
    }

    function resolveTeacherIdForSlotClick($cell) {
        const data = adjustState.data;
        if (!data) {
            return 0;
        }
        const day = String($cell.data('day') || '');
        const slotId = Number($cell.data('slot-id') || 0);
        const cell = ((data.cells || {})[day] || {})[slotId] || ((data.cells || {})[day] || {})[String(slotId)] || null;
        if (cell && Number(cell.teacher_id || 0) > 0) {
            return Number(cell.teacher_id);
        }
        if (adjustState.dragSubjectId) {
            const poolItem = poolItems(data).find(function (u) {
                return Number(u.subject_id) === Number(adjustState.dragSubjectId);
            });
            if (poolItem && Number(poolItem.teacher_id || 0) > 0) {
                return Number(poolItem.teacher_id);
            }
            if (adjustState.selectedTeacherId > 0) {
                return adjustState.selectedTeacherId;
            }
        }
        return 0;
    }

    function applyAdjustData(data) {
        adjustState.data = data;
        adjustState.dragSubjectId = null;
        adjustState.dragFromTeacher = null;
        adjustState.feasibleKeys = {};
        adjustState.blockedKeys = {};
        adjustState.blockedCodeKeys = {};
        adjustState.caseKeys = {};
        adjustState.fridayInactiveKeys = {};
        adjustState.selectedSubjectName = '';
        adjustState.selectedTeacherName = '';
        adjustState.selectedTeacherId = 0;
        adjustState.activeTeacherId = 0;
        adjustState.activeSlotKey = '';
        (data.friday_inactive || []).forEach(function (f) {
            adjustState.fridayInactiveKeys[cellKey(f.day, f.slot_id)] = true;
        });
        renderAdjustPanel();
    }

    function isFridayInactive(day, slotId) {
        return !!adjustState.fridayInactiveKeys[cellKey(day, slotId)];
    }

    function poolItems(data) {
        return (data && data.pool) ? data.pool : [];
    }

    function forceSubjectCard(item, variant) {
        if (!item) {
            return '';
        }
        const sub = escapeHtml(item.subject_name || 'Subject');
        const teacher = String(item.teacher_name || '').trim();
        const section = String(item.section_label || '').trim();
        let html = '<div class="tt-force-card tt-force-card-' + variant + '">';
        html += '<div class="tt-force-subject">' + sub + '</div>';
        if (teacher) {
            html += '<div class="tt-force-meta">' + escapeHtml(teacher) + '</div>';
        }
        if (section && variant === 'other') {
            html += '<div class="tt-force-meta">' + escapeHtml(section) + '</div>';
        }
        html += '</div>';
        return html;
    }

    function renderForceModal() {
        if ($('#ttForceModal').length) {
            return;
        }
        $('body').append(
            '<div class="modal fade" id="ttForceModal" tabindex="-1" role="dialog">' +
            '<div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content">' +
            '<div class="modal-header py-2 border-0">' +
            '<h5 class="modal-title" id="ttForceTitle"><i class="fas fa-exchange-alt text-warning me-1"></i> Replace slot?</h5>' +
            '<button type="button" class="close" data-bs-dismiss="modal"><span>&times;</span></button></div>' +
            '<div class="modal-body pt-0">' +
            '<div id="ttForceVisual"></div>' +
            '</div>' +
            '<div class="modal-footer py-2 border-0">' +
            '<button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>' +
            '<button type="button" class="btn btn-sm btn-warning" id="ttForceConfirmBtn"><i class="fas fa-check me-1"></i> Confirm</button>' +
            '</div></div></div></div>'
        );
    }

    function showForceModal(preview, pending) {
        renderForceModal();
        adjustState.pendingForce = pending;
        const v = (preview && preview.visual) ? preview.visual : {};
        const kind = String(v.kind || preview.code || 'occupied');
        const day = String(v.day || (preview.target && preview.target.day) || '');
        const slotNum = Number(v.slot_num || (preview.target && preview.target.slot_num) || (preview.target && preview.target.slot_id) || 0);
        const incoming = v.incoming || {
            subject_name: preview.subject_name,
            teacher_name: preview.teacher_name,
            section_label: ''
        };
        const replaced = v.replaced || null;
        const unplaced = v.unplaced || [];
        const conflictRow = unplaced.find(function (u) { return u.reason === 'teacher_busy'; }) || null;
        const replaceRow = unplaced.find(function (u) { return u.reason === 'replace'; }) || replaced;

        let title = '<i class="fas fa-exchange-alt text-warning me-1"></i> Replace slot?';
        if (kind === 'teacher_busy') {
            title = '<i class="fas fa-user-clock text-warning me-1"></i> Teacher busy';
        } else if (kind === 'both') {
            title = '<i class="fas fa-exclamation-triangle text-warning me-1"></i> Replace & free teacher';
        }
        $('#ttForceTitle').html(title);

        let html = '<div class="tt-force-slot-pill"><i class="far fa-calendar-alt"></i> ' +
            escapeHtml(day) + ' · Slot ' + slotNum + '</div>';

        html += '<div class="tt-force-swap">';
        if (kind === 'teacher_busy' && conflictRow) {
            html += forceSubjectCard(conflictRow, 'other');
        } else if (replaceRow) {
            html += forceSubjectCard(replaceRow, 'out');
        } else {
            html += '<div class="tt-force-card tt-force-card-out"><div class="tt-force-meta">Empty</div></div>';
        }
        html += '<div class="tt-force-arrow"><i class="fas fa-long-arrow-alt-right"></i></div>';
        html += forceSubjectCard(incoming, 'in');
        html += '</div>';

        if (unplaced.length) {
            html += '<div class="tt-force-hint"><i class="fas fa-inbox me-1"></i> Returns to unplaced pool</div>';
            html += '<div class="tt-force-pool-chips">';
            unplaced.forEach(function (u) {
                html += '<span class="tt-force-pool-chip"><i class="fas fa-minus-circle"></i> ' +
                    escapeHtml(u.subject_name || 'Subject') + '</span>';
            });
            html += '</div>';
        }

        $('#ttForceVisual').html(html);
        $('#ttForceModal').modal('show');
    }

    function placeSubject(subjectId, day, slotId, force, moveFrom) {
        const data = adjustState.data;
        if (!data) {
            return;
        }
        const subName = adjustState.selectedSubjectName || 'subject';
        showAdjustGridLoader(force ? 'Applying changes…' : ('Placing ' + subName + '…'));
        $('#adjustContainer .tt-adjust-pool').addClass('is-loading');
        const postData = {
            cls_sec_id: Number(data.cls_sec_id),
            subject_id: Number(subjectId),
            day: day,
            slot_id: Number(slotId),
            force: force ? 1 : 0
        };
        if (moveFrom && Number(moveFrom.time_table_id || 0) > 0) {
            postData.move_from_cls_sec_id = Number(moveFrom.cls_sec_id || 0);
            postData.move_from_day = String(moveFrom.day || '');
            postData.move_from_slot_id = Number(moveFrom.slot_id || 0);
            postData.move_from_time_table_id = Number(moveFrom.time_table_id || 0);
        }
        $.ajax({
            url: adjustPlaceUrl,
            method: 'POST',
            dataType: 'json',
            data: postData
        }).done(function (res) {
            if (res && res.success && res.adjust) {
                clearSlotHighlights();
                applyAdjustData(res.adjust);
                refreshReportSection(Number(data.cls_sec_id));
                toastr.success(res.msg || 'Placed.');
                return;
            }
            if (!force && res && res.can_force && res.force_preview) {
                showForceModal(res.force_preview, {
                    subject_id: Number(subjectId),
                    day: day,
                    slot_id: Number(slotId),
                    move_from: moveFrom || null
                });
                return;
            }
            toastr.error((res && res.msg) || 'Could not place subject.');
        }).fail(function () {
            toastr.error('Server error while placing subject.');
        }).always(function () {
            hideAdjustGridLoader();
            adjustState.dragFromTeacher = null;
        });
    }

    function clearCell(day, slotId) {
        const data = adjustState.data;
        if (!data) {
            return;
        }
        $.ajax({
            url: adjustClearUrl,
            method: 'POST',
            dataType: 'json',
            data: { cls_sec_id: Number(data.cls_sec_id), day: day, slot_id: Number(slotId) }
        }).done(function (res) {
            if (res && res.success && res.adjust) {
                applyAdjustData(res.adjust);
                refreshReportSection(Number(data.cls_sec_id));
                toastr.success(res.msg || 'Slot cleared.');
            } else {
                toastr.error((res && res.msg) || 'Could not clear slot.');
            }
        }).fail(function () {
            toastr.error('Server error while clearing slot.');
        });
    }

    function renderAdjustPanel() {
        const data = adjustState.data;
        if (!data || !data.success) {
            $('#adjustContainer').html('<div class="alert alert-warning">No adjustment data.</div>').removeClass('d-none');
            return;
        }

        const stats = data.stats || {};
        let poolHtml = '';
        const items = poolItems(data);
        if (!items.length) {
            poolHtml = '<div class="alert alert-success mb-0 small">All demanded periods are placed for this section.</div>';
        } else {
            items.forEach(function (u) {
                const cnt = Number(u.count || u.remaining || 0);
                const tn = String(u.teacher_name || '').trim();
                const tid = Number(u.teacher_id || 0);
                poolHtml += '<div class="tt-adjust-pool-item" draggable="true" ' +
                    'data-subject-id="' + Number(u.subject_id) + '" ' +
                    'data-subject-name="' + escapeHtml(u.subject_name || '') + '" ' +
                    'data-teacher-name="' + escapeHtml(tn) + '" ' +
                    'data-teacher-id="' + tid + '">' +
                    '<div><div class="fw-bold">' + escapeHtml(u.subject_name || '') + '</div>' +
                    '<div class="small text-primary">' + escapeHtml(tn || 'No teacher assigned') + '</div>' +
                    '<div class="small text-muted">Click to view teacher · drag to place</div></div>' +
                    '<span class="pool-count">' + cnt + '</span></div>';
            });
        }

        let summaryHtml = '<div class="small mb-2">' +
            '<span class="badge text-bg-primary me-1">Demanded: ' + Number(stats.demanded || 0) + '</span>' +
            '<span class="badge text-bg-success me-1">Placed: ' + Number(stats.placed || 0) + '</span>' +
            '<span class="badge text-bg-' + (Number(stats.unplaced || 0) > 0 ? 'warning' : 'secondary') + '">Unplaced: ' + Number(stats.unplaced || 0) + '</span>' +
            '</div>';

        let tableHtml = '<div class="table-responsive"><table class="table table-bordered table-sm mb-0 tt-adjust-table">' +
            '<thead class="table-light"><tr><th style="width:90px">Slot</th>';
        (data.days || []).forEach(function (d) {
            tableHtml += '<th>' + escapeHtml(d) + '</th>';
        });
        tableHtml += '</tr></thead><tbody>';

        (data.slots || []).forEach(function (slot, i) {
            const slotId = Number(slot.slot_id || 0);
            tableHtml += '<tr><td class="fw-bold bg-light">Slot ' + (i + 1) + '</td>';
            (data.days || []).forEach(function (day) {
                const cell = ((data.cells || {})[day] || {})[slotId] || ((data.cells || {})[day] || {})[String(slotId)] || null;
                const occupied = !!(cell && cell.subject_id);
                const friOff = isFridayInactive(day, slotId);
                let cls = 'tt-adjust-cell ' + (occupied ? 'is-clearable' : 'is-empty');
                if (friOff) {
                    cls += ' is-friday-inactive';
                }
                tableHtml += '<td class="' + cls + '" ' +
                    'data-day="' + escapeHtml(day) + '" data-slot-id="' + slotId + '" ' +
                    'data-teacher-id="' + Number((cell && cell.teacher_id) || 0) + '" ' +
                    'title="' + (friOff ? 'Not available on Friday (half-day)' : 'Click to view teacher timetable') + '">';
                if (occupied) {
                    tableHtml += '<button type="button" class="tt-cell-clear" title="Clear slot">&times;</button>';
                    tableHtml += '<div class="tt-cell-subject">' + escapeHtml(cell.subject_name || '') + '</div>';
                    const tn = (cell.teacher_name || '').trim();
                    tableHtml += '<div class="tt-cell-teacher">' + escapeHtml(tn || 'No teacher') + '</div>';
                } else if (friOff) {
                    tableHtml += '<span class="tt-friday-na"><i class="fas fa-ban"></i> N/A</span>';
                } else {
                    tableHtml += '<span class="text-muted small">Free</span>';
                }
                tableHtml += '</td>';
            });
            tableHtml += '</tr>';
        });
        tableHtml += '</tbody></table></div>';

        const html = '<div class="card card-outline card-warning no-print tt-adjust-panel-card">' +
            '<div class="card-header d-flex justify-content-between align-items-center flex-wrap">' +
            '<h3 class="card-title mb-2 mb-md-0">Adjust Timetable: ' + escapeHtml(data.title || '') + '</h3>' +
            '<div class="d-flex align-items-center flex-wrap">' +
            '<button type="button" class="btn btn-sm btn-outline-secondary mb-2 mb-md-0 me-1" id="btnRefreshAdjust"><i class="fas fa-sync"></i> Refresh</button>' +
            '<button type="button" class="btn btn-sm btn-secondary mb-2 mb-md-0" id="btnCloseAdjust"><i class="fas fa-times"></i> Close</button>' +
            '</div></div>' +
            '<div class="card-body">' +
            '<div class="alert alert-light border small mb-3">' +
            '<strong>How to adjust:</strong> Click a pool subject to highlight slots and show its teacher timetable. ' +
            'Drag a pool subject or a busy teacher cell onto the class grid to place. ' +
            '<span class="tt-adjust-legend d-block mt-1">' +
            '<span class="leg-ok"><i class="fas fa-check"></i> Green = teacher free (place or replace)</span> ' +
            '<span class="leg-no"><i class="fas fa-times"></i> Red = teacher busy (force to unplace)</span> ' +
            '<span class="leg-fri"><i class="fas fa-ban"></i> Striped = Friday inactive</span>' +
            '</span></div>' +
            summaryHtml +
            '<div id="ttAdjustTeacherBanner" class="alert alert-info py-2 small mb-2 tt-adjust-teacher-banner d-none"></div>' +
            '<div id="ttAdjustTeacherPanel" class="d-none mb-2">' +
            '<div class="tt-teacher-mini-empty">Select a pool subject or click a class slot to view a teacher timetable.</div>' +
            '</div>' +
            '<div class="tt-adjust-wrap mt-2">' +
            '<div class="tt-adjust-pool">' +
            '<h6 class="mb-2">Unplaced pool</h6>' + poolHtml +
            '</div>' +
            '<div class="tt-adjust-grid-panel">' +
            '<div class="tt-adjust-grid-wrap">' + tableHtml +
            '<div id="ttAdjustGridLoader" class="tt-adjust-grid-loader d-none">' +
            '<div class="tt-adjust-grid-loader-inner">' +
            '<i class="fas fa-spinner fa-spin fa-2x text-primary"></i>' +
            '<div id="ttAdjustGridLoaderText" class="mt-2 small text-muted">Loading…</div>' +
            '</div></div></div></div>' +
            '</div></div></div>';

        $('#adjustContainer').html(html).removeClass('d-none');
        updateSectionAdjustButtons();
        bindAdjustInteractions();
        const $panel = $('#adjustContainer');
        if ($panel.length) {
            $('html, body').animate({ scrollTop: $panel.offset().top - 70 }, 200);
        }
    }

    function setPoolSelectionMeta($item) {
        adjustState.selectedSubjectName = String($item.data('subject-name') || '').trim();
        adjustState.selectedTeacherName = String($item.data('teacher-name') || '').trim();
        adjustState.selectedTeacherId = Number($item.data('teacher-id') || 0);
    }

    function updateTeacherBanner(res) {
        const $banner = $('#ttAdjustTeacherBanner');
        if (!$banner.length || !adjustState.dragSubjectId) {
            $banner.addClass('d-none').empty();
            return;
        }
        const subName = String((res && res.subject_name) || adjustState.selectedSubjectName || 'Subject');
        const teacher = String((res && res.teacher_name) || adjustState.selectedTeacherName || 'No teacher assigned');
        $banner.removeClass('d-none').html(
            '<strong>' + escapeHtml(subName) + '</strong> — <strong>' + escapeHtml(teacher) + '</strong> — ' +
            '<span class="text-success">Green</span> = free · <span class="text-danger">Red</span> = busy elsewhere'
        );
    }

    function attemptPlaceSubject(subjectId, day, slotId, moveFrom) {
        if (adjustState.isLoading) {
            return;
        }
        if (isFridayInactive(day, slotId)) {
            toastr.warning('This Friday slot is permanently inactive (half-day).');
            return;
        }
        if (!adjustState.dragSubjectId) {
            toastr.warning('Select a pool subject first.');
            return;
        }
        placeSubject(subjectId, day, slotId, false, moveFrom || null);
    }

    function bindAdjustInteractions() {
        const $pool = $('#adjustContainer .tt-adjust-pool-item');
        $pool.off('dragstart dragend click');
        $pool.on('click', function () {
            if (adjustState.isLoading) {
                return;
            }
            const sid = Number($(this).data('subject-id'));
            $('.tt-adjust-pool-item').removeClass('active');
            $(this).addClass('active');
            adjustState.dragSubjectId = sid;
            setPoolSelectionMeta($(this));
            loadFeasibleForSubject(sid);
        });
        $pool.on('dragstart', function (e) {
            const sid = Number($(this).data('subject-id'));
            adjustState.dragSubjectId = sid;
            setPoolSelectionMeta($(this));
            $('.tt-adjust-pool-item').removeClass('active');
            $(this).addClass('active dragging');
            if (e.originalEvent && e.originalEvent.dataTransfer) {
                e.originalEvent.dataTransfer.setData('text/plain', String(sid));
                e.originalEvent.dataTransfer.effectAllowed = 'move';
            }
            loadFeasibleForSubject(sid);
        });
        $pool.on('dragend', function () {
            $(this).removeClass('dragging');
            adjustState.dragSubjectId = null;
            adjustState.dragFromTeacher = null;
            adjustState.selectedSubjectName = '';
            adjustState.selectedTeacherName = '';
            adjustState.selectedTeacherId = 0;
            $('.tt-adjust-pool-item').removeClass('active');
            clearSlotHighlights();
        });
    }

    function clearSlotHighlights() {
        adjustState.feasibleKeys = {};
        adjustState.blockedKeys = {};
        adjustState.blockedCodeKeys = {};
        adjustState.caseKeys = {};
        adjustState.activeSlotKey = '';
        $('#ttAdjustTeacherBanner').addClass('d-none').empty();
        $('.tt-adjust-cell')
            .removeClass('is-feasible is-feasible-replace is-blocked is-blocked-teacher is-blocked-occupied is-drag-over is-drag-over-force is-slot-active')
            .each(function () {
                const day = String($(this).data('day') || '');
                const slotId = Number($(this).data('slot-id') || 0);
                if (isFridayInactive(day, slotId)) {
                    $(this).attr('title', 'Not available on Friday (half-day)');
                } else {
                    $(this).attr('title', 'Click to view teacher timetable');
                }
            });
        if (!adjustState.dragSubjectId) {
            showTeacherPanelMessage('Select a pool subject or click a class slot to view a teacher timetable.');
        }
    }

    function applySlotHighlights(res) {
        adjustState.feasibleKeys = {};
        adjustState.blockedKeys = {};
        adjustState.blockedCodeKeys = {};
        adjustState.caseKeys = {};
        const teacherName = String((res && res.teacher_name) || adjustState.selectedTeacherName || 'Teacher');
        const subjectName = String((res && res.subject_name) || adjustState.selectedSubjectName || 'subject');
        if (res && res.teacher_name) {
            adjustState.selectedTeacherName = String(res.teacher_name);
        }
        if (res && res.subject_name) {
            adjustState.selectedSubjectName = String(res.subject_name);
        }
        updateTeacherBanner(res);

        if (res && res.teacher_id) {
            adjustState.selectedTeacherId = Number(res.teacher_id);
            loadTeacherTimetable(Number(res.teacher_id), '');
        } else if (adjustState.selectedTeacherId > 0) {
            loadTeacherTimetable(adjustState.selectedTeacherId, '');
        }

        if (res && res.success && res.cells && res.cells.length) {
            res.cells.forEach(function (c) {
                const k = cellKey(c.day, c.slot_id);
                const caseNum = Number(c.case || 0);
                adjustState.caseKeys[k] = caseNum;
                if (isFridayInactive(c.day, c.slot_id) || c.highlight === 'friday_inactive') {
                    return;
                }
                if (c.highlight === 'green') {
                    adjustState.feasibleKeys[k] = true;
                } else if (c.highlight === 'red') {
                    let reason = teacherName + ' busy';
                    if (c.conflict && c.conflict.subject_name) {
                        reason = teacherName + ' → ' + String(c.conflict.subject_name);
                        if (c.conflict.class_label) {
                            reason += ' (' + String(c.conflict.class_label) + ')';
                        }
                    }
                    adjustState.blockedKeys[k] = reason;
                    adjustState.blockedCodeKeys[k] = 'teacher_conflict';
                }
            });
        } else if (res && res.success) {
            (res.feasible || []).forEach(function (f) {
                adjustState.feasibleKeys[cellKey(f.day, f.slot_id)] = true;
                if (f.case) {
                    adjustState.caseKeys[cellKey(f.day, f.slot_id)] = Number(f.case);
                }
            });
            (res.blocked || []).forEach(function (b) {
                const k = cellKey(b.day, b.slot_id);
                if (isFridayInactive(b.day, b.slot_id)) {
                    return;
                }
                adjustState.blockedKeys[k] = String(b.reason || 'Not suitable');
                adjustState.blockedCodeKeys[k] = String(b.code || 'teacher_conflict');
                if (b.case) {
                    adjustState.caseKeys[k] = Number(b.case);
                }
            });
        }

        $('.tt-adjust-cell').each(function () {
            const day = String($(this).data('day') || '');
            const slotId = Number($(this).data('slot-id') || 0);
            const k = cellKey(day, slotId);
            const caseNum = Number(adjustState.caseKeys[k] || 0);
            $(this).removeClass('is-feasible is-feasible-replace is-blocked is-blocked-teacher is-blocked-occupied');
            if (isFridayInactive(day, slotId)) {
                $(this).attr('title', 'Not available on Friday (half-day)');
                return;
            }
            if (adjustState.feasibleKeys[k]) {
                $(this).addClass('is-feasible');
                if (caseNum === 3) {
                    $(this).addClass('is-feasible-replace');
                    $(this).attr('title', teacherName + ' free — replaces current subject');
                } else {
                    $(this).attr('title', teacherName + ' free — place here');
                }
            } else if (adjustState.blockedKeys[k]) {
                $(this).addClass('is-blocked is-blocked-teacher');
                $(this).attr('title', adjustState.blockedKeys[k]);
            } else {
                $(this).removeAttr('title');
            }
        });
    }

    function loadFeasibleForSubject(subjectId) {
        if (!subjectId || !adjustState.data) {
            clearSlotHighlights();
            return;
        }
        const teacher = adjustState.selectedTeacherName || 'teacher';
        showAdjustGridLoader('Checking ' + teacher + ' availability…');
        $('#adjustContainer .tt-adjust-pool').addClass('is-loading');
        $.ajax({
            url: adjustFeasibleUrl,
            method: 'POST',
            dataType: 'json',
            data: {
                cls_sec_id: Number(adjustState.data.cls_sec_id),
                subject_id: Number(subjectId)
            }
        }).done(function (res) {
            applySlotHighlights(res);
        }).fail(function (xhr) {
            const msg = (xhr.status === 404)
                ? 'Adjust route not found. Redeploy app/Config/Routes.php and Timetable.php, then hard-refresh.'
                : 'Could not load slot highlights.';
            toastr.error(msg);
            clearSlotHighlights();
        }).always(function () {
            hideAdjustGridLoader();
        });
    }

    function reloadAdjustData(clsSecId) {
        const cid = Number(clsSecId || adjustState.activeClsSecId || $('#clsSecId').val());
        if (!cid || cid <= 0) {
            return;
        }
        adjustState.activeClsSecId = cid;
        updateSectionAdjustButtons();
        $('#adjustContainer').html('<div class="alert alert-info">Loading adjustment view...</div>').removeClass('d-none');
        $.ajax({
            url: adjustDataUrl,
            method: 'POST',
            dataType: 'json',
            data: { cls_sec_id: cid }
        }).done(function (res) {
            if (res && res.success) {
                applyAdjustData(res);
            } else {
                $('#adjustContainer').html('<div class="alert alert-danger">' + escapeHtml((res && res.msg) || 'Could not load adjustment data.') + '</div>');
            }
        }).fail(function () {
            $('#adjustContainer').html('<div class="alert alert-danger">Server error while loading adjustment data.</div>');
        });
    }

    $(document).on('click', '#btnRefreshAdjust', function () {
        reloadAdjustData(adjustState.activeClsSecId);
    });

    $(document).on('click', '#btnCloseAdjust', function () {
        closeAdjustPanel();
    });

    $(document).on('click', '.btn-section-adjust', function () {
        if ($('input[name="reportMode"]:checked').val() !== 'class') {
            return;
        }
        const cid = Number($(this).data('cls-sec-id') || 0);
        if (!cid) {
            return;
        }
        if (adjustState.activeClsSecId === cid) {
            closeAdjustPanel();
            return;
        }
        openAdjustForSection(cid);
    });

    $(document).on('click', '#ttForceConfirmBtn', function () {
        const p = adjustState.pendingForce;
        if (!p) {
            $('#ttForceModal').modal('hide');
            return;
        }
        $('#ttForceModal').modal('hide');
        placeSubject(p.subject_id, p.day, p.slot_id, true, p.move_from || null);
        adjustState.pendingForce = null;
    });

    $(document).on('dragover', '.tt-adjust-cell', function (e) {
        if ((!adjustState.dragSubjectId && !adjustState.dragFromTeacher) || adjustState.isLoading) {
            return;
        }
        const day = String($(this).data('day') || '');
        const slotId = Number($(this).data('slot-id') || 0);
        const k = cellKey(day, slotId);
        if (isFridayInactive(day, slotId)) {
            return;
        }
        e.preventDefault();
        if (adjustState.feasibleKeys[k]) {
            $('.tt-adjust-cell').removeClass('is-drag-over is-drag-over-force');
            $(this).addClass('is-drag-over');
        } else if (adjustState.blockedKeys[k]) {
            $('.tt-adjust-cell').removeClass('is-drag-over is-drag-over-force');
            $(this).addClass('is-drag-over-force');
        }
    });

    $(document).on('dragleave', '.tt-adjust-cell', function () {
        $(this).removeClass('is-drag-over is-drag-over-force');
    });

    $(document).on('drop', '.tt-adjust-cell', function (e) {
        e.preventDefault();
        const $cell = $(this);
        $cell.removeClass('is-drag-over is-drag-over-force');
        const subjectId = adjustState.dragSubjectId;
        if (!subjectId) {
            return;
        }
        const day = String($cell.data('day') || '');
        const slotId = Number($cell.data('slot-id') || 0);
        attemptPlaceSubject(subjectId, day, slotId, adjustState.dragFromTeacher || null);
        adjustState.dragFromTeacher = null;
    });

    $(document).on('click', '.tt-adjust-cell', function (e) {
        if ($(e.target).closest('.tt-cell-clear').length) {
            return;
        }
        const $cell = $(this);
        const day = String($cell.data('day') || '');
        const slotId = Number($cell.data('slot-id') || 0);
        if (isFridayInactive(day, slotId)) {
            return;
        }
        const slotKey = cellKey(day, slotId);
        const teacherId = resolveTeacherIdForSlotClick($cell);
        if (teacherId > 0) {
            loadTeacherTimetable(teacherId, slotKey);
            return;
        }
        showTeacherPanelMessage('Select a pool subject to view its teacher timetable.');
        adjustState.activeSlotKey = slotKey;
        highlightActiveSlotTeacher();
    });

    $(document).on('click', '.tt-cell-clear', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (adjustState.dragSubjectId) {
            return;
        }
        const $cell = $(this).closest('.tt-adjust-cell');
        if (!confirm('Clear this slot?')) {
            return;
        }
        clearCell(String($cell.data('day') || ''), Number($cell.data('slot-id') || 0));
    });

    $(document).on('dragstart', '.tt-teacher-cell.is-busy', function (e) {
        if (!adjustState.dragSubjectId) {
            e.preventDefault();
            toastr.info('Select a pool subject first, then drag a busy teacher cell.');
            return;
        }
        const $cell = $(this);
        adjustState.dragFromTeacher = {
            cls_sec_id: Number($cell.data('cls-sec-id') || 0),
            day: String($cell.data('day') || ''),
            slot_id: Number($cell.data('slot-id') || 0),
            time_table_id: Number($cell.data('time-table-id') || 0),
            subject_id: Number($cell.data('subject-id') || 0)
        };
        $cell.addClass('dragging');
        if (e.originalEvent && e.originalEvent.dataTransfer) {
            e.originalEvent.dataTransfer.setData('text/plain', 'teacher-move');
            e.originalEvent.dataTransfer.effectAllowed = 'move';
        }
    });

    $(document).on('dragend', '.tt-teacher-cell.is-busy', function () {
        $(this).removeClass('dragging');
        adjustState.dragFromTeacher = null;
    });



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
            closeAdjustPanel();

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



        closeAdjustPanel();
        $('#reportContainer').html('<div class="alert alert-info">Loading report...</div>');
        $('#reportContainer').removeClass('d-none');



        $.ajax({

            url: "<?= base_url('admin/timetable/report-data') ?>",

            method: "POST",

            dataType: "json",

            data: payload,

            success: function (res) {

                if (res && res.success) {

                    $('#reportContainer').html(res.html || '<div class="alert alert-warning">No data.</div>');

                    $('#btnExportPdf, #btnExportExcel, #btnPrintReport').prop('disabled', false);
                    initReportTooltips();
                    updateSectionAdjustButtons();
                    if (pendingAdjustClsSecId > 0) {
                        const openId = pendingAdjustClsSecId;
                        pendingAdjustClsSecId = 0;
                        openAdjustForSection(openId);
                    }

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

    if (pendingAdjustAutoLoad) {
        $('#btnLoadReport').trigger('click');
    }

});

</script>



<?= $this->endSection() ?>

