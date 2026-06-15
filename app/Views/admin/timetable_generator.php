<?= $this->extend('layouts/admin_template') ?>

<?= $this->section('content') ?>



<?php

$reportAdjustUrl = base_url('admin/timetable/report?adjust=1');

$sectionSubjectsUrl = base_url('admin/section_subjects');

?>



<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">



<style>

.tt-gen-class-pill {

    display: block;

    width: 100%;

    text-align: left;

    border: 1px solid #dee2e6;

    background: #fff;

    border-radius: 6px;

    padding: 8px 10px;

    margin-bottom: 6px;

    cursor: pointer;

    transition: border-color 0.15s, background 0.15s;

    font-size: 0.9rem;

}

.tt-gen-class-pill:hover { border-color: #007bff; background: #f8fbff; }

.tt-gen-class-pill.active { border-color: #007bff; background: #e7f1ff; font-weight: 600; }

.tt-gen-class-pill .tt-pill-meta { font-size: 0.75rem; color: #6c757d; }

.tt-accordion .card { border-radius: 6px; margin-bottom: 6px; overflow: hidden; }

.tt-accordion .card-header {

    padding: 8px 12px;

    background: #f8f9fa;

    cursor: pointer;

    border-bottom: 1px solid #dee2e6;

}

.tt-accordion .card-header.is-section-off { opacity: 0.72; }

.tt-accordion .card-header.border-danger { border-start: 3px solid #dc3545; }

.tt-accordion .card-header.border-success { border-start: 3px solid #28a745; }

.tt-accordion .tt-sec-label { font-weight: 600; font-size: 0.95rem; }

.tt-accordion .tt-sec-actions .btn { padding: 2px 8px; font-size: 0.75rem; }

.tt-subject-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(108px, 1fr));
    gap: 8px;
}
.tt-subject-grid > .text-muted { grid-column: 1 / -1; }
.tt-subject-card {
    border: 1px solid #dee2e6;
    border-radius: 6px;
    background: #fff;
    padding: 6px 8px;
    font-size: 0.8rem;
    min-height: 52px;
    transition: border-color 0.15s, background 0.15s;
}
.tt-subject-card.is-included {
    background: #f0fff4;
    border-color: #b8dfc4;
}
.tt-subject-card.is-disabled { opacity: 0.55; }
.tt-subject-card-label {
    display: flex;
    align-items: flex-start;
    gap: 4px;
    margin: 0;
    cursor: pointer;
    font-weight: 600;
    line-height: 1.2;
}
.tt-subject-card-label input { margin-top: 2px; flex-shrink: 0; }
.tt-subject-card .tt-subj-name {
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    word-break: break-word;
}
.tt-subject-card-week { margin-top: 4px; }
.tt-weekly-input { width: 100%; max-width: 72px; text-align: center; margin: 0 auto; display: block; }

.tt-gen-progress { height: 4px; background: #e9ecef; border-radius: 2px; margin-top: 4px; }

.tt-gen-progress-fill { height: 100%; background: #28a745; border-radius: 2px; transition: width 0.2s; }

.tt-gen-progress-fill.is-overflow { background: #dc3545; }

.tt-gen-toolbar { background: #f8f9fa; border-radius: 8px; padding: 12px 14px; }

.tt-gen-settings-panel { border-top: 1px solid #dee2e6; padding-top: 12px; margin-top: 12px; }

.tt-slots-table th, .tt-slots-table td { padding: 4px 8px; font-size: 0.85rem; vertical-align: middle; }

.tt-slots-table input[type="time"] { max-width: 110px; }
.tt-slots-table .slot-range-preview { font-size: 0.78rem; color: #6c757d; white-space: nowrap; }
.tt-slots-table tr.is-slot-invalid { background: #fff5f5; }

@media (max-width: 768px) {

    .tt-gen-header-actions .btn { margin-bottom: 6px; }

}

</style>



<?= view('components/page_header', [

    'title' => 'Timetable Constraints',

    'icon' => 'fas fa-calendar-week',

    'subtitle' => 'Set weekly class demands per section. Use short names for a compact view.',

    'breadcrumbs' => [

        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],

        ['label' => 'Timetable', 'url' => base_url('admin/timetable/generator')],

        ['label' => 'Constraints', 'active' => true],

    ],

]) ?>



<section class="content">

    <div class="container-fluid">

        <div class="card card-outline card-primary">

            <div class="card-header">

                <div class="row align-items-center w-100">

                    <div class="col-lg-5 mb-2 mb-lg-0">

                        <h3 class="card-title mb-0">Weekly demands &amp; generation</h3>

                    </div>

                    <div class="col-lg-7 tt-gen-header-actions text-lg-right">

                        <button type="button" class="btn btn-outline-secondary btn-sm" id="toggleGenSettingsBtn">

                            <i class="fas fa-cog"></i> Settings

                        </button>

                        <button type="button" class="btn btn-primary btn-sm" id="saveConstraintsBtn">

                            <i class="fas fa-save"></i> Save

                        </button>

                        <button type="button" class="btn btn-success btn-sm" id="generateBtn">

                            <i class="fas fa-magic"></i> Generate

                        </button>

                        <a href="<?= esc($reportAdjustUrl) ?>" class="btn btn-info btn-sm">

                            <i class="fas fa-sliders-h"></i> Report &amp; Adjust

                        </a>

                    </div>

                </div>

                <div id="genSettingsPanel" class="tt-gen-settings-panel collapse">

                    <div class="row">

                        <div class="col-md-4">

                            <div class="form-check form-switch mb-2">

                                <input type="checkbox" class="form-check-input" id="useMondayTemplate">

                                <label class="form-check-label" for="useMondayTemplate">Use Monday template for whole week</label>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="form-check form-switch mb-2">

                                <input type="checkbox" class="form-check-input" id="strictMode" checked>

                                <label class="form-check-label" for="strictMode">Strict conflict mode</label>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <label class="small fw-bold mb-1 d-block">Friday active slots <span class="text-muted fw-normal">(half-day)</span></label>

                            <div id="fridaySlotsWrap" class="d-flex flex-wrap"></div>

                        </div>

                    </div>

                    <hr class="my-3">

                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">

                        <h6 class="mb-0 fw-bold"><i class="far fa-clock me-1"></i> Period slots</h6>

                        <div>

                            <button type="button" class="btn btn-outline-secondary btn-sm" id="addSlotRowBtn"><i class="fas fa-plus"></i> Add slot</button>

                            <button type="button" class="btn btn-primary btn-sm" id="saveSlotsBtn"><i class="fas fa-save"></i> Save slots</button>

                        </div>

                    </div>

                    <p class="small text-muted mb-2">
                        Enter each <strong>bell time</strong> when that period ends (and the next begins). The first period starts at <strong>Day start</strong> below.
                        Mark <strong>Break</strong> for recess/lunch — breaks are not used for class placement.
                    </p>
                    <div class="d-flex flex-wrap align-items-center mb-2">
                        <label class="small fw-bold me-2 mb-1" for="slotDayStart">Day start</label>
                        <input type="time" id="slotDayStart" class="form-control form-control-sm me-3" value="07:30">
                        <span id="slotsChainHint" class="small text-muted"></span>
                    </div>
                    <div class="table-responsive">

                        <table class="table table-sm table-bordered tt-slots-table mb-0">

                            <thead class="table-light">

                                <tr>

                                    <th style="width:36px;">#</th>

                                    <th>Name</th>

                                    <th>Bell (ends)</th>

                                    <th>Period</th>

                                    <th style="width:70px;">Break</th>

                                    <th style="width:50px;"></th>

                                </tr>

                            </thead>

                            <tbody id="slotsEditorBody"></tbody>

                        </table>

                    </div>

                </div>

            </div>



            <div class="card-body">

                <div id="constraintsLoader" class="text-center py-5">

                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>

                    <p class="mt-2 text-muted">Loading timetable constraints...</p>

                </div>



                <div id="constraintsRoot" style="display:none;">

                    <div id="capacityAlertWrap" class="mb-3"></div>

                    <div id="missingSectionAlertWrap" class="mb-3"></div>



                    <div class="tt-gen-toolbar mb-3">

                        <div class="row align-items-end">

                            <div class="col-md-4 col-lg-3 mb-2">

                                <label class="small text-muted mb-1">Search</label>

                                <input type="text" id="demandSearch" class="form-control form-control-sm" placeholder="Class or subject...">

                            </div>

                            <div class="col-md-4 col-lg-3 mb-2">

                                <label class="small text-muted mb-1">Show subjects</label>

                                <select id="filterTimetableSubjects" class="form-control form-control-sm">

                                    <option value="all">All subjects</option>

                                    <option value="included">Included in timetable only</option>

                                    <option value="excluded">Excluded from timetable only</option>

                                </select>

                            </div>

                            <div class="col-md-4 col-lg-3 mb-2">

                                <label class="small text-muted mb-1">Bulk weekly classes</label>

                                <div class="input-group input-group-sm">

                                    <select id="bulkClassSection" class="form-select">

                                        <option value="">Section...</option>

                                    </select>

                                    <input type="number" min="0" id="bulkWeeklyValue" class="form-control" style="max-width:70px;" placeholder="0">

                                    <button type="button" class="btn btn-outline-primary" id="applyBulkWeeklyBtn">Set</button>

                                </div>

                            </div>

                            <div class="col-md-12 col-lg-3 mb-2 d-flex flex-wrap align-items-center">

                                <div class="form-check form-check me-3">

                                    <input type="checkbox" class="form-check-input" id="showOverflowOnly">

                                    <label class="form-check-label small" for="showOverflowOnly">Overflow only</label>

                                </div>

                                <span id="overflowCountBadge" class="badge text-bg-secondary me-2">Overflow: 0</span>

                                <button type="button" class="btn btn-sm btn-outline-secondary" id="setAllZeroBtn">All zero</button>

                            </div>

                        </div>

                    </div>



                    <div class="row">

                        <div class="col-md-2 col-lg-2">

                            <h6 class="text-muted text-uppercase small mb-2">Classes</h6>

                            <div id="classesList"></div>

                        </div>

                        <div class="col-md-10 col-lg-10">

                            <div id="selectedClassInfo" class="mb-2"></div>

                            <div id="sectionsContainer" class="tt-accordion"></div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>



<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>

$(function () {

    const bootstrapUrl = "<?= base_url('admin/timetable/generator-bootstrap') ?>";

    const saveUrl = "<?= base_url('admin/timetable/save-generator-constraints') ?>";

    const saveSlotsUrl = "<?= base_url('admin/timetable/save-generator-slots') ?>";

    const generateUrl = "<?= base_url('admin/timetable/generate-from-constraints') ?>";

    const reportAdjustUrl = "<?= esc($reportAdjustUrl) ?>";



    let state = {

        options: { use_monday_template: 0, strict_mode: 1, friday_active_slots: [] },

        slots: [],

        teachingSlotIds: [],

        rows: [],

        capacity: {},

        validation: { sum_by_section: {}, overflow: [] },

        missingSections: [],

        sectionInclude: {},

        classGroups: [],

        currentClassIndex: 0,

        expandedSectionId: null,

        filterTimetable: 'all',

        slotEditorRows: [],
        slotDayStart: '07:30'

    };

    let autoSaveTimer = null;



    function escapeHtml(str) {

        return String(str || '').replace(/[&<>"]/g, function (m) {

            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[m];

        });

    }



    function classDisplayName(row) {

        return String(row.class_short_name || row.class_name || 'Class');

    }



    function subjectDisplayName(row) {

        return String(row.subject_short_name || row.subject_name || '');

    }



    function sectionLabelFromRow(row) {

        return String(row.section_label || ((row.class_name || 'Class') + ' - ' + (row.section_name || 'Section')));

    }



    function toTimeInputValue(t) {

        if (!t) return '';

        const m = String(t).match(/(\d{1,2}):(\d{2})/);

        if (!m) return '';

        return ('0' + m[1]).slice(-2) + ':' + m[2];

    }

    function timeToMinutes(t) {
        const v = toTimeInputValue(t);
        if (!v) return null;
        const p = v.split(':');
        return parseInt(p[0], 10) * 60 + parseInt(p[1], 10);
    }

    function minutesToTime(mins) {
        if (!Number.isFinite(mins)) return '';
        const h = Math.floor(mins / 60) % 24;
        const m = mins % 60;
        return ('0' + h).slice(-2) + ':' + ('0' + m).slice(-2);
    }

    function suggestNextBellTime(rows, dayStart) {
        const startM = timeToMinutes(dayStart);
        if (startM === null) return '';
        let cursor = startM;
        rows.forEach(function (row) {
            const bellM = timeToMinutes(row.bell_time);
            if (bellM !== null && bellM > cursor) cursor = bellM;
        });
        return minutesToTime(cursor + 40);
    }

    function computeSlotRanges(dayStart, rows) {
        let prevEnd = timeToMinutes(dayStart);
        return rows.map(function (row, i) {
            const bellM = timeToMinutes(row.bell_time);
            const startM = i === 0 ? timeToMinutes(dayStart) : prevEnd;
            let valid = startM !== null && bellM !== null && bellM > startM;
            if (i > 0 && prevEnd !== null && startM !== prevEnd) valid = false;
            const start = startM !== null ? minutesToTime(startM) : '';
            const end = bellM !== null ? minutesToTime(bellM) : '';
            if (valid) prevEnd = bellM;
            else if (bellM !== null) prevEnd = bellM;
            return { start: start, end: end, valid: valid };
        });
    }

    function formatRangePreview(start, end) {
        if (!start || !end) return '—';
        return start + ' → ' + end;
    }

    function validateSlotChain(dayStart, rows) {
        const ranges = computeSlotRanges(dayStart, rows);
        for (let i = 0; i < ranges.length; i++) {
            if (!ranges[i].valid) {
                return 'Slot ' + (i + 1) + ': bell time must be after the previous bell (and after day start for the first period).';
            }
        }
        return '';
    }

    function updateSlotsChainHint() {
        const err = validateSlotChain(state.slotDayStart, state.slotEditorRows);
        const $hint = $('#slotsChainHint');
        if (err) {
            $hint.removeClass('text-muted').addClass('text-danger').text(err);
        } else if (state.slotEditorRows.length) {
            const last = state.slotEditorRows[state.slotEditorRows.length - 1];
            $hint.removeClass('text-danger').addClass('text-muted')
                .text('Day ' + state.slotDayStart + ' → last bell ' + (last.bell_time || '—'));
        } else {
            $hint.removeClass('text-danger').addClass('text-muted').text('');
        }
    }



    $('#toggleGenSettingsBtn').on('click', function () {

        $('#genSettingsPanel').collapse('toggle');

    });



    function loadBootstrap() {

        $('#constraintsLoader').show();

        $('#constraintsRoot').hide();

        $.ajax({ url: bootstrapUrl, method: 'GET', dataType: 'json' })

            .done(function (res) {

                if (!res || !res.success) {

                    toastr.error((res && res.msg) || 'Could not load constraints.');

                    return;

                }

                applyBootstrapData(res);

                $('#constraintsLoader').hide();

                $('#constraintsRoot').show();

            })

            .fail(function () {

                toastr.error('Network error while loading constraints.');

            });

    }



    function applyBootstrapData(res) {

        state.options = res.options || state.options;

        state.slots = res.slots || [];

        state.teachingSlotIds = (res.teaching_slot_ids || []).map(Number);

        state.rows = res.rows || [];

        state.capacity = res.capacity || {};

        state.validation = res.validation || state.validation;

        state.missingSections = res.missing_sections || [];

        state.sectionInclude = {};

        state.rows.forEach(function (r) {

            const cid = Number(r.cls_sec_id);

            if (!cid || Object.prototype.hasOwnProperty.call(state.sectionInclude, cid)) return;

            state.sectionInclude[cid] = Number(r.section_include_in_timetable || 0) === 1 ? 1 : 0;

        });

        buildClassGroups();

        buildSectionOptions();

        paintOptions();

        paintBulkSelect();

        initSlotEditor();

        recomputeValidation();

        renderConstraintsView();

        paintValidation();

    }



    function buildClassGroups() {

        const byClass = {};

        state.rows.forEach(function (r, idx) {

            const classId = Number(r.class_id || 0);

            if (!byClass[classId]) {

                byClass[classId] = {

                    class_id: classId,

                    class_name: String(r.class_name || 'Class'),

                    class_short_name: String(r.class_short_name || ''),

                    sections: {}

                };

            }

            const cid = Number(r.cls_sec_id);

            if (!byClass[classId].sections[cid]) {

                byClass[classId].sections[cid] = {

                    cls_sec_id: cid,

                    section_id: Number(r.section_id || 0),

                    section_name: String(r.section_name || 'Section'),

                    section_short_name: String(r.section_short_name || ''),

                    section_label: sectionLabelFromRow(r),

                    subjects: []

                };

            }

            byClass[classId].sections[cid].subjects.push({ idx: idx, row: r });

        });

        state.classGroups = Object.keys(byClass)

            .map(function (k) {

                const g = byClass[k];

                g.sections = Object.keys(g.sections)

                    .map(function (sk) { return g.sections[sk]; })

                    .sort(function (a, b) { return a.section_id - b.section_id; });

                return g;

            })

            .sort(function (a, b) { return a.class_id - b.class_id; });

        if (state.currentClassIndex >= state.classGroups.length) {

            state.currentClassIndex = 0;

        }

        if (state.expandedSectionId === null && state.classGroups.length) {

            const first = state.classGroups[state.currentClassIndex];

            if (first && first.sections.length) {

                state.expandedSectionId = Number(first.sections[0].cls_sec_id);

            }

        }

    }



    function teachingSlots() {
        return state.slots.filter(function (s) { return Number(s.is_break || 0) !== 1; });
    }



    function paintOptions() {

        $('#useMondayTemplate').prop('checked', Number(state.options.use_monday_template) === 1);

        $('#strictMode').prop('checked', Number(state.options.strict_mode) === 1);

        const friday = new Set((state.options.friday_active_slots || []).map(Number));

        const slotsForFriday = teachingSlots();

        let html = '';

        slotsForFriday.forEach(function (slot, i) {

            const sid = Number(slot.slot_id);

            const start = toTimeInputValue(slot.start_time);

            const end = toTimeInputValue(slot.end_time);

            const label = (slot.slot_name || ('Slot ' + (i + 1))) + (start && end ? ' (' + start + '-' + end + ')' : '');

            html += '<div class="me-3 mb-1"><div class="form-check form-check form-check-inline">' +

                '<input type="checkbox" class="form-check-input friday-slot" id="fri-slot-' + sid + '" value="' + sid + '" ' + (friday.has(sid) ? 'checked' : '') + '>' +

                '<label class="form-check-label" for="fri-slot-' + sid + '">' + escapeHtml(label) + '</label></div></div>';

        });

        $('#fridaySlotsWrap').html(html || '<span class="text-muted small">No teaching slots. Add periods in Settings.</span>');

    }



    function buildSectionOptions() {

        const seen = {};

        const out = [];

        state.rows.forEach(function (r) {

            const cid = Number(r.cls_sec_id);

            if (!cid || seen[cid]) return;

            seen[cid] = true;

            out.push({

                cls_sec_id: cid,

                class_id: Number(r.class_id || 0),

                section_id: Number(r.section_id || 0),

                label: sectionLabelFromRow(r)

            });

        });

        out.sort(function (a, b) {

            if (a.class_id !== b.class_id) return a.class_id - b.class_id;

            if (a.section_id !== b.section_id) return a.section_id - b.section_id;

            return a.cls_sec_id - b.cls_sec_id;

        });

        state.sectionOptions = out;

    }



    function paintBulkSelect() {

        const prev = $('#bulkClassSection').val();

        let html = '<option value="">Section...</option>';

        state.sectionOptions.forEach(function (s) {

            html += '<option value="' + s.cls_sec_id + '">' + escapeHtml(s.label) + '</option>';

        });

        $('#bulkClassSection').html(html);

        if (prev && state.sectionOptions.some(function (s) { return String(s.cls_sec_id) === String(prev); })) {

            $('#bulkClassSection').val(prev);

        }

    }



    function initSlotEditor() {

        if (state.slots.length) {
            state.slotDayStart = toTimeInputValue(state.slots[0].start_time) || '07:30';
        } else {
            state.slotDayStart = state.slotDayStart || '07:30';
        }

        state.slotEditorRows = state.slots.map(function (s) {

            return {

                slot_id: Number(s.slot_id || 0),

                slot_name: String(s.slot_name || ''),

                bell_time: toTimeInputValue(s.end_time),

                is_break: Number(s.is_break || 0) === 1 ? 1 : 0,

                slot_type: String(s.slot_type || 'FullDay')

            };

        });

        $('#slotDayStart').val(state.slotDayStart);

        paintSlotsEditor();

    }



    function paintSlotsEditor() {

        const ranges = computeSlotRanges(state.slotDayStart, state.slotEditorRows);

        let html = '';

        if (!state.slotEditorRows.length) {

            html = '<tr><td colspan="6" class="text-muted text-center">No slots yet. Set day start, then click Add slot.</td></tr>';

        }

        state.slotEditorRows.forEach(function (row, i) {

            const range = ranges[i] || { start: '', end: '', valid: false };

            const rowCls = range.valid ? '' : ' is-slot-invalid';

            html += '<tr data-slot-idx="' + i + '" class="' + rowCls.trim() + '">';

            html += '<td>' + (i + 1) + '</td>';

            html += '<td><input type="text" class="form-control form-control-sm slot-name" value="' + escapeHtml(row.slot_name) + '" placeholder="' + (i + 1) + (i === 0 ? 'st' : i === 1 ? 'nd' : i === 2 ? 'rd' : 'th') + '"></td>';

            html += '<td><input type="time" class="form-control form-control-sm slot-bell" value="' + escapeHtml(row.bell_time) + '" title="Bell rings — end of this period"></td>';

            html += '<td><span class="slot-range-preview">' + escapeHtml(formatRangePreview(range.start, range.end)) + '</span></td>';

            html += '<td class="text-center"><input type="checkbox" class="slot-break" ' + (row.is_break ? 'checked' : '') + '></td>';

            html += '<td><button type="button" class="btn btn-outline-danger btn-sm slot-remove" title="Remove"><i class="fas fa-times"></i></button></td>';

            html += '</tr>';

        });

        $('#slotsEditorBody').html(html);

        updateSlotsChainHint();

    }



    function syncSlotEditorFromDom() {

        state.slotDayStart = $('#slotDayStart').val() || state.slotDayStart;

        $('#slotsEditorBody tr[data-slot-idx]').each(function () {

            const idx = Number($(this).data('slot-idx'));

            if (!state.slotEditorRows[idx]) return;

            state.slotEditorRows[idx].slot_name = $(this).find('.slot-name').val();

            state.slotEditorRows[idx].bell_time = $(this).find('.slot-bell').val();

            state.slotEditorRows[idx].is_break = $(this).find('.slot-break').is(':checked') ? 1 : 0;

        });

    }

    function refreshSlotRowPreviews() {

        syncSlotEditorFromDom();

        const ranges = computeSlotRanges(state.slotDayStart, state.slotEditorRows);

        $('#slotsEditorBody tr[data-slot-idx]').each(function () {

            const idx = Number($(this).data('slot-idx'));

            const range = ranges[idx] || { start: '', end: '', valid: false };

            $(this).toggleClass('is-slot-invalid', !range.valid);

            $(this).find('.slot-range-preview').text(formatRangePreview(range.start, range.end));

        });

        updateSlotsChainHint();

    }



    function subjectPassesFilter(entry) {

        const included = Number(entry.row.include_in_timetable || 0) === 1;

        if (state.filterTimetable === 'included' && !included) return false;

        if (state.filterTimetable === 'excluded' && included) return false;

        return true;

    }



    function sectionPassesFilters(sec) {

        const query = String($('#demandSearch').val() || '').trim().toLowerCase();

        const overflowOnly = $('#showOverflowOnly').is(':checked');

        const key = Number(sec.cls_sec_id);

        const cap = Number((state.capacity[key] && state.capacity[key].capacity) || 0);

        const req = isSectionIncluded(key) ? Number((state.validation.sum_by_section || {})[key] || 0) : 0;

        const overflow = isSectionIncluded(key) && req > cap;

        if (overflowOnly && !overflow) return false;

        if (!query) {

            return sec.subjects.some(subjectPassesFilter);

        }

        if (String(sec.section_label || '').toLowerCase().indexOf(query) !== -1) {

            return sec.subjects.some(subjectPassesFilter);

        }

        return sec.subjects.some(function (entry) {

            if (!subjectPassesFilter(entry)) return false;

            const subj = (entry.row.subject_short_name || entry.row.subject_name || '').toLowerCase();

            const full = (entry.row.subject_name || '').toLowerCase();

            return subj.indexOf(query) !== -1 || full.indexOf(query) !== -1;

        });

    }



    function isSectionIncluded(cid) {

        return Number(state.sectionInclude[cid] || 0) === 1;

    }



    function isRowActive(row) {

        return isSectionIncluded(row.cls_sec_id) && Number(row.include_in_timetable || 0) === 1;

    }



    function renderClassSidebar() {

        if (!state.classGroups.length) {

            $('#classesList').html('<div class="text-muted small">No classes with section subjects.</div>');

            return;

        }

        let html = '';

        state.classGroups.forEach(function (g, i) {

            const active = i === state.currentClassIndex ? ' active' : '';

            const label = escapeHtml(g.class_short_name || g.class_name);

            html += '<button type="button" class="tt-gen-class-pill' + active + '" data-class-index="' + i + '">' +

                label + '<div class="tt-pill-meta">' + g.sections.length + ' sec</div></button>';

        });

        $('#classesList').html(html);

    }



    function renderWeeklyInputHtml(idx, r, active) {
        if (!active) return '';
        return '<div class="tt-subject-card-week"><input type="number" min="0" class="form-control form-control-sm tt-weekly-input weekly-classes" value="' +
            Number(r.weekly_classes || 0) + '" data-idx="' + idx + '" title="Classes per week"></div>';
    }



    function renderSectionsPanel() {

        const g = state.classGroups[state.currentClassIndex];

        if (!g) {

            $('#selectedClassInfo').empty();

            $('#sectionsContainer').html(

                '<div class="alert alert-info">Select a class or assign subjects in ' +

                '<a href="<?= esc($sectionSubjectsUrl) ?>">Section Subjects</a>.</div>'

            );

            return;

        }

        const classLabel = escapeHtml(g.class_short_name || g.class_name);

        $('#selectedClassInfo').html('<h5 class="mb-0">' + classLabel + '</h5>');

        const sumMap = state.validation.sum_by_section || {};

        let html = '';

        let visibleCount = 0;



        g.sections.forEach(function (sec) {

            if (!sectionPassesFilters(sec)) return;

            visibleCount++;

            const key = Number(sec.cls_sec_id);

            const cap = Number((state.capacity[key] && state.capacity[key].capacity) || 0);

            const sectionIncluded = isSectionIncluded(key);

            const req = sectionIncluded ? Number(sumMap[key] || 0) : 0;

            const overflow = sectionIncluded && req > cap;

            const pct = cap > 0 ? Math.min(100, Math.round((req / cap) * 100)) : 0;

            const borderCls = !sectionIncluded ? 'is-section-off' : (overflow ? 'border-danger' : 'border-success');

            const expanded = state.expandedSectionId === key;

            const badgeCls = !sectionIncluded ? 'text-bg-secondary' : (overflow ? 'text-bg-danger' : 'text-bg-success');



            html += '<div class="card tt-section-card" data-section-id="' + key + '">';

            html += '<div class="card-header d-flex flex-wrap align-items-center ' + borderCls + '" data-toggle-section="' + key + '">';

            html += '<div class="form-check form-check me-2 mb-0" onclick="event.stopPropagation();">';

            html += '<input type="checkbox" class="form-check-input include-section-timetable" id="sec-inc-' + key + '" data-cid="' + key + '" ' + (sectionIncluded ? 'checked' : '') + '>';

            html += '<label class="form-check-label" for="sec-inc-' + key + '"></label></div>';

            html += '<span class="tt-sec-label me-2">' + escapeHtml(sec.section_label) + '</span>';

            html += '<span class="badge ' + badgeCls + ' sec-requested me-2">' + req + '/' + cap + '</span>';

            html += '<div class="tt-sec-actions ms-auto" onclick="event.stopPropagation();">';

            html += '<button type="button" class="btn btn-outline-secondary section-include-all me-1" data-cid="' + key + '" ' + (sectionIncluded ? '' : 'disabled') + '>All</button>';

            html += '<button type="button" class="btn btn-outline-secondary section-include-none me-1" data-cid="' + key + '" ' + (sectionIncluded ? '' : 'disabled') + '>None</button>';

            html += '<input type="number" min="0" class="form-control form-control-sm d-inline-block section-bulk-value me-1" data-cid="' + key + '" style="width:52px;" placeholder="0" ' + (sectionIncluded ? '' : 'disabled') + '>';

            html += '<button type="button" class="btn btn-outline-primary section-bulk-apply" data-cid="' + key + '" ' + (sectionIncluded ? '' : 'disabled') + '>Set</button>';

            html += '</div>';

            html += '<div class="w-100 tt-gen-progress"><div class="tt-gen-progress-fill ' + (overflow ? 'is-overflow' : '') + '" style="width:' + pct + '%"></div></div>';

            html += '</div>';



            html += '<div class="collapse ' + (expanded ? 'show' : '') + '" id="tt-sec-body-' + key + '">';

            html += '<div class="card-body p-2">';

            html += '<div class="tt-subject-grid">';



            let visibleSubjects = 0;

            sec.subjects.forEach(function (entry) {

                if (!subjectPassesFilter(entry)) return;

                visibleSubjects++;

                const i = entry.idx;

                const r = entry.row;

                const included = Number(r.include_in_timetable || 0) === 1;

                const active = sectionIncluded && included;

                const cardCls = 'tt-subject-card tt-subject-row ' + (active ? 'is-included' : '') + (sectionIncluded ? '' : ' is-disabled');

                html += '<div class="' + cardCls + '" data-row-index="' + i + '">';

                html += '<label class="tt-subject-card-label" for="inc-' + i + '" title="' + escapeHtml(r.subject_name || '') + '">';

                html += '<input type="checkbox" class="include-timetable" id="inc-' + i + '" data-idx="' + i + '" ' + (included ? 'checked' : '') + ' ' + (sectionIncluded ? '' : 'disabled') + '>';

                html += '<span class="tt-subj-name">' + escapeHtml(subjectDisplayName(r)) + '</span>';

                html += '</label>';

                html += renderWeeklyInputHtml(i, r, active);

                html += '</div>';

            });



            if (!visibleSubjects) {

                html += '<div class="text-muted small py-2">No subjects match filters.</div>';

            }

            html += '</div></div></div></div>';

        });



        if (!html) {

            html = '<div class="alert alert-light border">No sections match the current filters.</div>';

        } else if (!visibleCount) {

            html = '<div class="alert alert-light border">No sections match the current filters.</div>';

        }

        $('#sectionsContainer').html(html);

    }



    function renderConstraintsView() {

        if (!state.rows.length) {

            $('#constraintsLoader').hide();

            $('#constraintsRoot').show();

            $('#classesList').empty();

            $('#sectionsContainer').html(

                '<div class="text-center py-4">' +

                '<p class="text-muted mb-2">No subjects assigned to any section.</p>' +

                '<a href="<?= esc($sectionSubjectsUrl) ?>" class="btn btn-primary btn-sm"><i class="fas fa-book"></i> Open Section Subjects</a>' +

                '</div>'

            );

            return;

        }

        renderClassSidebar();

        renderSectionsPanel();

    }



    function updateSubjectRow(idx) {

        const r = state.rows[idx];

        if (!r) return;

        const $card = $('.tt-subject-row[data-row-index="' + idx + '"]');

        if (!$card.length) return;

        const sectionIncluded = isSectionIncluded(r.cls_sec_id);

        const included = Number(r.include_in_timetable || 0) === 1;

        const active = sectionIncluded && included;

        $card.find('.include-timetable').prop('checked', included).prop('disabled', !sectionIncluded);

        $card.toggleClass('is-included', active).toggleClass('is-disabled', !sectionIncluded);

        const $weekWrap = $card.find('.tt-subject-card-week');

        if (active) {

            if (!$weekWrap.find('.weekly-classes').length) {

                $card.append('<div class="tt-subject-card-week"><input type="number" min="0" class="form-control form-control-sm tt-weekly-input weekly-classes" value="' +

                    Number(r.weekly_classes || 0) + '" data-idx="' + idx + '" title="Classes per week"></div>');

            } else {

                $weekWrap.find('.weekly-classes').val(Number(r.weekly_classes || 0));

            }

        } else {

            $weekWrap.remove();

        }

    }



    function updateSectionCard(cid) {

        const key = Number(cid);

        const $card = $('.tt-section-card[data-section-id="' + key + '"]');

        if (!$card.length) return;

        const sectionIncluded = isSectionIncluded(key);

        const cap = Number((state.capacity[key] && state.capacity[key].capacity) || 0);

        const req = sectionIncluded ? Number((state.validation.sum_by_section || {})[key] || 0) : 0;

        const overflow = sectionIncluded && req > cap;

        const pct = cap > 0 ? Math.min(100, Math.round((req / cap) * 100)) : 0;

        const $header = $card.find('.card-header').first();



        $header.toggleClass('is-section-off', !sectionIncluded)

            .toggleClass('border-danger', sectionIncluded && overflow)

            .toggleClass('border-success', sectionIncluded && !overflow);

        $header.find('.include-section-timetable').prop('checked', sectionIncluded);

        $header.find('.sec-requested')

            .removeClass('text-bg-danger text-bg-success text-bg-secondary')

            .addClass(!sectionIncluded ? 'text-bg-secondary' : (overflow ? 'text-bg-danger' : 'text-bg-success'))

            .text(req + '/' + cap);

        $header.find('.tt-gen-progress-fill').css('width', pct + '%').toggleClass('is-overflow', overflow);

        $header.find('.section-include-all, .section-include-none, .section-bulk-apply, .section-bulk-value').prop('disabled', !sectionIncluded);

        state.rows.forEach(function (r, idx) {

            if (Number(r.cls_sec_id) === key) updateSubjectRow(idx);

        });

    }



    function recomputeValidation() {

        const sums = {};

        state.rows.forEach(function (r) {

            if (!isRowActive(r)) return;

            const cid = Number(r.cls_sec_id);

            sums[cid] = (sums[cid] || 0) + Number(r.weekly_classes || 0);

        });

        const overflow = [];

        Object.keys(sums).forEach(function (cid) {

            const cap = Number((state.capacity[cid] && state.capacity[cid].capacity) || 0);

            if (sums[cid] > cap) overflow.push({ cls_sec_id: Number(cid), requested: sums[cid], capacity: cap });

        });

        state.validation = { sum_by_section: sums, overflow: overflow };

    }



    function paintValidation() {

        const over = state.validation.overflow || [];

        $('#overflowCountBadge').removeClass('text-bg-secondary text-bg-danger')

            .addClass(over.length > 0 ? 'text-bg-danger' : 'text-bg-secondary')

            .text('Overflow: ' + over.length);

        if (!over.length) {

            $('#capacityAlertWrap').html('<div class="alert alert-success py-2 mb-0"><i class="fas fa-check-circle me-1"></i> All sections are within weekly slot capacity.</div>');

        } else {

            let html = '<div class="alert alert-danger py-2 mb-0"><strong>Capacity overflow:</strong><ul class="mb-0 ps-3 mt-1">';

            over.forEach(function (o) {

                const sec = state.sectionOptions.find(function (s) { return Number(s.cls_sec_id) === Number(o.cls_sec_id); });

                html += '<li>' + escapeHtml(sec ? sec.label : ('Section ' + o.cls_sec_id)) + ': ' + o.requested + ' requested, capacity ' + o.capacity + '</li>';

            });

            html += '</ul></div>';

            $('#capacityAlertWrap').html(html);

        }

        paintMissingSectionsAlert();

    }



    function paintMissingSectionsAlert() {

        const items = state.missingSections || [];

        if (!items.length) {

            $('#missingSectionAlertWrap').html('');

            return;

        }

        let html = '<div class="alert alert-warning py-2 mb-0"><strong>Sections without subjects:</strong> ';

        html += '<a href="<?= esc($sectionSubjectsUrl) ?>">Section Subjects</a><ul class="mb-0 ps-3 small mt-1">';

        items.forEach(function (m) { html += '<li>' + escapeHtml(m.label || '') + '</li>'; });

        html += '</ul></div>';

        $('#missingSectionAlertWrap').html(html);

    }



    function collectPayload() {

        state.options.use_monday_template = $('#useMondayTemplate').is(':checked') ? 1 : 0;

        state.options.strict_mode = $('#strictMode').is(':checked') ? 1 : 0;

        state.options.friday_active_slots = $('.friday-slot:checked').map(function () { return Number($(this).val()); }).get();

        const teaching = teachingSlots();

        if (!state.options.friday_active_slots.length && teaching.length) {

            toastr.warning('Select at least one Friday slot.');

            return null;

        }

        return {

            options: state.options,

            rows: state.rows.map(function (r) {

                return {

                    cls_sec_id: Number(r.cls_sec_id),

                    subject_id: Number(r.subject_id),

                    weekly_classes: Number(r.weekly_classes || 0),

                    include_in_timetable: Number(r.include_in_timetable || 0) === 1 ? 1 : 0

                };

            }),

            sections: Object.keys(state.sectionInclude).map(function (cid) {

                return { cls_sec_id: Number(cid), include_in_timetable: Number(state.sectionInclude[cid] || 0) === 1 ? 1 : 0 };

            })

        };

    }



    function persistConstraints(silent, done) {

        const payload = collectPayload();

        if (!payload) {

            if (typeof done === 'function') done(false);

            return;

        }

        $.ajax({

            url: saveUrl,

            method: 'POST',

            dataType: 'json',

            data: {

                options: JSON.stringify(payload.options),

                rows: JSON.stringify(payload.rows),

                sections: JSON.stringify(payload.sections)

            }

        }).done(function (res) {

            if (res && res.success) {

                if (res.validation) {

                    state.validation = res.validation;

                    paintValidation();

                    renderSectionsPanel();

                }

                if (!silent) {

                    toastr.success(res.msg || 'Constraints saved.');

                    loadBootstrap();

                }

                if (typeof done === 'function') done(true);

            } else {

                if (res && res.validation) {

                    state.validation = res.validation;

                    paintValidation();

                }

                if (!silent) toastr.error((res && res.msg) || 'Could not save.');

                if (typeof done === 'function') done(false);

            }

        }).fail(function () {

            if (!silent) toastr.error('Network error while saving.');

            if (typeof done === 'function') done(false);

        });

    }



    function queueAutoSave() {

        if (autoSaveTimer) clearTimeout(autoSaveTimer);

        autoSaveTimer = setTimeout(function () { persistConstraints(true); }, 900);

    }



    function showReportLinkToast(placed, unplaced) {

        let msg = 'Timetable generated: ' + placed + ' placed';

        if (unplaced > 0) msg += ', ' + unplaced + ' unplaced';

        msg += '.';

        toastr.success(

            msg + ' <a href="' + reportAdjustUrl + '" class="text-white fw-bold ms-1"><u>Open Report &amp; Adjust</u></a>',

            '',

            { timeOut: 12000, extendedTimeOut: 8000, escapeHtml: false }

        );

    }



    $(document).on('click', '.tt-gen-class-pill', function () {

        state.currentClassIndex = Number($(this).data('class-index'));

        const g = state.classGroups[state.currentClassIndex];

        if (g && g.sections.length) {

            state.expandedSectionId = Number(g.sections[0].cls_sec_id);

        }

        renderClassSidebar();

        renderSectionsPanel();

    });



    $(document).on('click', '[data-toggle-section]', function (e) {

        if ($(e.target).closest('.form-check, .tt-sec-actions, button, input').length) return;

        const key = Number($(this).data('toggle-section'));

        state.expandedSectionId = state.expandedSectionId === key ? null : key;

        renderSectionsPanel();

    });



    $(document).on('change', '.include-section-timetable', function () {

        const cid = Number($(this).data('cid'));

        state.sectionInclude[cid] = $(this).is(':checked') ? 1 : 0;

        recomputeValidation();

        renderSectionsPanel();

        paintValidation();

        queueAutoSave();

    });



    $(document).on('change', '.include-timetable', function () {

        const idx = Number($(this).data('idx'));

        state.rows[idx].include_in_timetable = $(this).is(':checked') ? 1 : 0;

        if (!$(this).is(':checked')) {

            state.rows[idx].weekly_classes = 0;

        }

        recomputeValidation();

        updateSubjectRow(idx);

        updateSectionCard(state.rows[idx].cls_sec_id);

        paintValidation();

        queueAutoSave();

    });



    $(document).on('input', '.weekly-classes', function () {

        const idx = Number($(this).data('idx'));

        let val = Number($(this).val());

        if (!Number.isFinite(val) || val < 0) val = 0;

        state.rows[idx].weekly_classes = val;

        recomputeValidation();

        updateSectionCard(state.rows[idx].cls_sec_id);

        paintValidation();

        queueAutoSave();

    });



    $('#demandSearch').on('input', renderSectionsPanel);

    $('#showOverflowOnly').on('change', renderSectionsPanel);

    $('#filterTimetableSubjects').on('change', function () {

        state.filterTimetable = String($(this).val() || 'all');

        renderSectionsPanel();

    });



    $(document).on('click', '.section-include-all', function () {

        const cid = Number($(this).data('cid'));

        if (!isSectionIncluded(cid)) return;

        state.rows.forEach(function (r) {

            if (Number(r.cls_sec_id) === cid) r.include_in_timetable = 1;

        });

        recomputeValidation();

        renderSectionsPanel();

        paintValidation();

        queueAutoSave();

    });



    $(document).on('click', '.section-include-none', function () {

        const cid = Number($(this).data('cid'));

        if (!isSectionIncluded(cid)) return;

        state.rows.forEach(function (r) {

            if (Number(r.cls_sec_id) === cid) {

                r.include_in_timetable = 0;

                r.weekly_classes = 0;

            }

        });

        recomputeValidation();

        renderSectionsPanel();

        paintValidation();

        queueAutoSave();

    });



    $(document).on('click', '.section-bulk-apply', function () {

        const cid = Number($(this).data('cid'));

        let weekly = Number($('.section-bulk-value[data-cid="' + cid + '"]').val());

        if (!Number.isFinite(weekly) || weekly < 0) weekly = 0;

        state.rows.forEach(function (r) {

            if (Number(r.cls_sec_id) === cid && Number(r.include_in_timetable || 0) === 1 && isSectionIncluded(cid)) {

                r.weekly_classes = weekly;

            }

        });

        recomputeValidation();

        renderSectionsPanel();

        paintValidation();

        queueAutoSave();

    });



    $('#setAllZeroBtn').on('click', function () {

        state.rows.forEach(function (r) { r.weekly_classes = 0; });

        recomputeValidation();

        renderSectionsPanel();

        paintValidation();

        queueAutoSave();

    });



    $('#applyBulkWeeklyBtn').on('click', function () {

        const cid = Number($('#bulkClassSection').val());

        let weekly = Number($('#bulkWeeklyValue').val());

        if (!cid) { toastr.warning('Select a section.'); return; }

        if (!isSectionIncluded(cid)) { toastr.warning('That section is excluded from the timetable.'); return; }

        if (!Number.isFinite(weekly) || weekly < 0) weekly = 0;

        state.rows.forEach(function (r) {

            if (Number(r.cls_sec_id) === cid && Number(r.include_in_timetable || 0) === 1) {

                r.weekly_classes = weekly;

            }

        });

        recomputeValidation();

        renderSectionsPanel();

        paintValidation();

        queueAutoSave();

    });



    $('#addSlotRowBtn').on('click', function () {

        syncSlotEditorFromDom();

        const n = state.slotEditorRows.length + 1;

        const ord = n === 1 ? '1st' : n === 2 ? '2nd' : n === 3 ? '3rd' : n + 'th';

        state.slotEditorRows.push({

            slot_id: 0,

            slot_name: ord,

            bell_time: suggestNextBellTime(state.slotEditorRows, state.slotDayStart),

            is_break: 0,

            slot_type: 'FullDay'

        });

        paintSlotsEditor();

    });

    $(document).on('input change', '#slotDayStart, .slot-bell', refreshSlotRowPreviews);



    $(document).on('click', '.slot-remove', function () {

        syncSlotEditorFromDom();

        const idx = Number($(this).closest('tr').data('slot-idx'));

        state.slotEditorRows.splice(idx, 1);

        paintSlotsEditor();

    });



    $('#saveSlotsBtn').on('click', function () {

        syncSlotEditorFromDom();

        if (!state.slotEditorRows.length) {

            toastr.warning('Add at least one slot.');

            return;

        }

        const chainErr = validateSlotChain(state.slotDayStart, state.slotEditorRows);

        if (chainErr) {

            toastr.error(chainErr);

            return;

        }

        const $btn = $(this);

        const old = $btn.html();

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({

            url: saveSlotsUrl,

            method: 'POST',

            dataType: 'json',

            data: {
                day_start: state.slotDayStart,
                slots: JSON.stringify(state.slotEditorRows)
            }

        }).done(function (res) {

            if (res && res.success) {

                toastr.success(res.msg || 'Slots saved.');

                if (res.slots) state.slots = res.slots;

                if (res.teaching_slot_ids) state.teachingSlotIds = res.teaching_slot_ids.map(Number);

                if (res.options) state.options = res.options;

                if (res.capacity) state.capacity = res.capacity;

                if (res.validation) state.validation = res.validation;

                initSlotEditor();

                paintOptions();

                paintValidation();

                renderSectionsPanel();

            } else {

                toastr.error((res && res.msg) || 'Could not save slots.');

            }

        }).fail(function () {

            toastr.error('Network error while saving slots.');

        }).always(function () {

            $btn.prop('disabled', false).html(old);

        });

    });



    $('#saveConstraintsBtn').on('click', function () {

        const $btn = $(this);

        const old = $btn.html();

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        persistConstraints(false, function () {

            $btn.prop('disabled', false).html(old);

        });

    });



    $('#generateBtn').on('click', function () {

        if (collectPayload() === null) return;

        if ((state.validation.overflow || []).length) {

            toastr.error('Fix capacity overflow before generating.');

            return;

        }

        if (!confirm('Generate will replace all existing timetable entries for this campus. Continue?')) {

            return;

        }

        const $btn = $(this);

        const old = $btn.html();

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({ url: generateUrl, method: 'POST', dataType: 'json' })

            .done(function (res) {

                if (res && res.success) {

                    const placed = Number(res.placed_count || 0);

                    const unplaced = Number(res.unplaced_count || 0);

                    showReportLinkToast(placed, unplaced);

                } else {

                    if (res && Array.isArray(res.missing_sections)) {

                        state.missingSections = res.missing_sections;

                        paintMissingSectionsAlert();

                    }

                    toastr.error((res && res.msg) || 'Generation failed');

                }

            })

            .fail(function () { toastr.error('Network error during generation.'); })

            .always(function () { $btn.prop('disabled', false).html(old); });

    });



    $('#useMondayTemplate, #strictMode').on('change', queueAutoSave);

    $(document).on('change', '.friday-slot', queueAutoSave);



    loadBootstrap();

});

</script>



<?= $this->endSection() ?>


