<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php $reportAdjustUrl = base_url('admin/timetable/report?adjust=1'); ?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-8">
                <h1 class="m-0"><i class="fas fa-cogs me-2"></i> Timetable Generator</h1>
                <p class="text-muted small mb-0">Set weekly demands per subject, generate the campus timetable, or fine-tune on the report page.</p>
            </div>
            <div class="col-sm-4">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/timetable/add') ?>">Timetable</a></li>
                    <li class="breadcrumb-item active">Generator</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card card-outline card-primary mb-3">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div class="mb-2 mb-md-0">
                        <span class="text-muted small d-block">Actions</span>
                    </div>
                    <div class="d-flex flex-wrap align-items-center">
                        <button type="button" class="btn btn-primary me-2 mb-2" id="saveConstraintsBtn">
                            <i class="fas fa-save"></i> Save Constraints
                        </button>
                        <button type="button" class="btn btn-success me-2 mb-2" id="generateBtn">
                            <i class="fas fa-cogs"></i> Generate Timetable
                        </button>
                        <a href="<?= esc($reportAdjustUrl) ?>" class="btn btn-info mb-2">
                            <i class="fas fa-sliders-h"></i> Timetable Report &amp; Adjust
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header py-2">
                <button class="btn btn-link btn-sm p-0 text-dark fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#genSettingsCollapse" aria-expanded="false">
                    <i class="fas fa-chevron-down me-1"></i> Generation settings
                </button>
            </div>
            <div id="genSettingsCollapse" class="collapse">
                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-2">
                                <input type="checkbox" class="form-check-input" id="useMondayTemplate">
                                <label class="form-check-label" for="useMondayTemplate">Use Monday timetable for whole week</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-2">
                                <input type="checkbox" class="form-check-input" id="strictMode" checked>
                                <label class="form-check-label" for="strictMode">Strict conflict mode</label>
                            </div>
                        </div>
                    </div>
                    <hr class="my-2">
                    <label class="mb-2"><strong>Friday active slots</strong> <span class="text-muted fw-normal">(half-day)</span></label>
                    <div id="fridaySlotsWrap" class="d-flex flex-wrap"></div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body py-2 tt-sub-toolbar">
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-4 mb-2">
                        <input type="text" id="demandSearch" class="form-control form-control-sm" placeholder="Search class or subject...">
                    </div>
                    <div class="col-lg-9 col-md-8 mb-2 d-flex flex-wrap align-items-center">
                        <select id="bulkClassSection" class="form-control form-control-sm me-2 mb-1" style="min-width:200px;max-width:240px;">
                            <option value="">Bulk: section...</option>
                        </select>
                        <input type="number" min="0" id="bulkWeeklyValue" class="form-control form-control-sm me-2 mb-1" style="width:88px;" placeholder="0">
                        <button type="button" class="btn btn-sm btn-outline-primary me-2 mb-1" id="applyBulkWeeklyBtn">Apply</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary me-2 mb-1" id="setAllZeroBtn">Set all to 0</button>
                        <div class="form-check form-check me-3 mb-1">
                            <input type="checkbox" class="form-check-input" id="showOverflowOnly">
                            <label class="form-check-label small" for="showOverflowOnly">Overflow only</label>
                        </div>
                        <span id="overflowCountBadge" class="badge text-bg-secondary me-2 mb-1">Overflow: 0</span>
                    </div>
                </div>
            </div>
        </div>

        <div id="capacityAlertWrap" class="mb-2"></div>
        <div id="missingSectionAlertWrap" class="mb-2"></div>

        <div class="card">
            <div class="card-body">
                <div id="constraintsLoader" class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2 text-muted">Loading constraints...</p>
                </div>
                <div id="constraintsRoot" style="display:none;">
                    <p class="small text-muted mb-3">
                        Subjects come from <a href="<?= base_url('admin/section_subjects') ?>">Section Subjects</a>.
                        Check subjects to include in the timetable and set <strong>classes per week</strong>.
                    </p>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <h6 class="mb-2">Classes</h6>
                            <div id="classesList"></div>
                        </div>
                        <div class="col-md-9 mb-3">
                            <div id="selectedClassInfo" class="mb-2"></div>
                            <div id="sectionsContainer"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <p class="small text-muted mb-2">
                    <strong>Generate</strong> clears all existing timetable entries for this campus and rebuilds from saved constraints.
                </p>
                <div id="generateSummary"></div>
            </div>
        </div>
    </div>
</section>

<style>
.tt-gen-class-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}
.tt-gen-class-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.tt-gen-class-card.active {
    border: 3px solid #ffc107;
    box-shadow: 0 0 0 1px rgba(255,193,7,0.4);
}
.tt-gen-section-card {
    background: #fff;
    border-radius: 10px;
    padding: 16px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 2px solid #dee2e6;
}
.tt-gen-section-card.border-success { border-color: #28a745 !important; }
.tt-gen-section-card.border-danger { border-color: #dc3545 !important; }
.tt-gen-section-card.is-section-off {
    opacity: 0.75;
    border-color: #ced4da !important;
}
.tt-gen-subject-item {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 10px;
    margin-bottom: 10px;
    height: 100%;
}
.tt-gen-subject-item.is-included {
    background: #e8f5e9;
    border-color: #c3e6cb;
}
.tt-gen-subject-item.is-disabled { opacity: 0.55; }
.tt-gen-cap-bar {
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
    margin: 8px 0 12px;
}
.tt-gen-cap-fill {
    height: 100%;
    background: linear-gradient(90deg, #28a745, #20c997);
    border-radius: 4px;
    transition: width 0.25s;
}
.tt-gen-cap-fill.is-overflow { background: linear-gradient(90deg, #dc3545, #fd7e14); }
.tt-sub-toolbar { background: #f8f9fa; border-radius: 6px; }
.tt-gen-weekly-input { max-width: 100%; margin-top: 6px; }
@media (max-width: 768px) {
    .tt-gen-class-card { padding: 10px; }
}
</style>

<script>
$(function () {
    const bootstrapUrl = "<?= base_url('admin/timetable/generator-bootstrap') ?>";
    const saveUrl = "<?= base_url('admin/timetable/save-generator-constraints') ?>";
    const generateUrl = "<?= base_url('admin/timetable/generate-from-constraints') ?>";
    const reportAdjustUrl = "<?= esc($reportAdjustUrl) ?>";

    let state = {
        options: { use_monday_template: 0, strict_mode: 1, friday_active_slots: [] },
        slots: [],
        rows: [],
        capacity: {},
        validation: { sum_by_section: {}, overflow: [] },
        placedCount: null,
        unplacedCount: null,
        sectionOptions: [],
        missingSections: [],
        sectionInclude: {},
        classGroups: [],
        currentClassIndex: 0
    };
    let autoSaveTimer = null;

    function escapeHtml(str) {
        return String(str || '').replace(/[&<>"]/g, function (m) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[m];
        });
    }

    function loadBootstrap() {
        $('#constraintsLoader').show();
        $('#constraintsRoot').hide();
        $.ajax({ url: bootstrapUrl, method: 'GET', dataType: 'json' })
            .done(function (res) {
                if (!res || !res.success) {
                    toastr.error((res && res.msg) || 'Could not load generator constraints.');
                    return;
                }
                state.options = res.options || state.options;
                state.slots = res.slots || [];
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
                recomputeValidation();
                renderConstraintsView();
                paintValidation();
                $('#constraintsLoader').hide();
                $('#constraintsRoot').show();
            })
            .fail(function () {
                toastr.error('Network error while loading generator constraints.');
            });
    }

    function buildClassGroups() {
        const byClass = {};
        state.rows.forEach(function (r, idx) {
            const classId = Number(r.class_id || 0);
            if (!byClass[classId]) {
                byClass[classId] = {
                    class_id: classId,
                    class_name: String(r.class_name || 'Class'),
                    sections: {}
                };
            }
            const cid = Number(r.cls_sec_id);
            if (!byClass[classId].sections[cid]) {
                byClass[classId].sections[cid] = {
                    cls_sec_id: cid,
                    section_id: Number(r.section_id || 0),
                    section_name: String(r.section_name || 'Section'),
                    class_name: String(r.class_name || ''),
                    label: sectionLabel(r),
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
    }

    function paintOptions() {
        $('#useMondayTemplate').prop('checked', Number(state.options.use_monday_template) === 1);
        $('#strictMode').prop('checked', Number(state.options.strict_mode) === 1);
        const friday = new Set((state.options.friday_active_slots || []).map(Number));
        let html = '';
        state.slots.forEach(function (slot) {
            const sid = Number(slot.slot_id);
            html += '<div class="me-3 mb-2"><div class="form-check form-check">' +
                '<input type="checkbox" class="form-check-input friday-slot" id="fri-slot-' + sid + '" value="' + sid + '" ' + (friday.has(sid) ? 'checked' : '') + '>' +
                '<label class="form-check-label" for="fri-slot-' + sid + '">Slot ' + sid + '</label></div></div>';
        });
        $('#fridaySlotsWrap').html(html || '<span class="text-muted">No slots found.</span>');
    }

    function sectionLabel(row) {
        return (row.class_name || 'Class') + ' - ' + (row.section_name || 'Section');
    }

    function isSectionIncluded(cid) {
        return Number(state.sectionInclude[cid] || 0) === 1;
    }

    function isRowActive(row) {
        return isSectionIncluded(row.cls_sec_id) && Number(row.include_in_timetable || 0) === 1;
    }

    function buildSectionOptions() {
        const seen = {};
        const out = [];
        state.rows.forEach(function (r) {
            const cid = Number(r.cls_sec_id);
            if (!cid || seen[cid]) return;
            seen[cid] = true;
            out.push({ cls_sec_id: cid, class_id: Number(r.class_id || 0), section_id: Number(r.section_id || 0), label: sectionLabel(r) });
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
        let html = '<option value="">Bulk: section...</option>';
        state.sectionOptions.forEach(function (s) {
            html += '<option value="' + s.cls_sec_id + '">' + escapeHtml(s.label) + '</option>';
        });
        $('#bulkClassSection').html(html);
        if (prev && state.sectionOptions.some(function (s) { return String(s.cls_sec_id) === String(prev); })) {
            $('#bulkClassSection').val(prev);
        }
    }

    function sectionPassesFilters(sec) {
        const query = String($('#demandSearch').val() || '').trim().toLowerCase();
        const overflowOnly = $('#showOverflowOnly').is(':checked');
        const key = Number(sec.cls_sec_id);
        const cap = Number((state.capacity[key] && state.capacity[key].capacity) || 0);
        const req = isSectionIncluded(key) ? Number((state.validation.sum_by_section || {})[key] || 0) : 0;
        const overflow = isSectionIncluded(key) && req > cap;
        if (overflowOnly && !overflow) return false;
        if (!query) return true;
        if (String(sec.label || '').toLowerCase().indexOf(query) !== -1) return true;
        return sec.subjects.some(function (entry) {
            return String(entry.row.subject_name || '').toLowerCase().indexOf(query) !== -1;
        });
    }

    function renderClassSidebar() {
        if (!state.classGroups.length) {
            $('#classesList').html('<div class="text-muted small">No classes with section subjects.</div>');
            return;
        }
        let html = '';
        state.classGroups.forEach(function (g, i) {
            const active = i === state.currentClassIndex ? ' active' : '';
            html += '<div class="tt-gen-class-card' + active + '" data-class-index="' + i + '">' +
                '<div class="fw-bold">' + escapeHtml(g.class_name) + '</div>' +
                '<small>' + g.sections.length + ' section' + (g.sections.length !== 1 ? 's' : '') + '</small></div>';
        });
        $('#classesList').html(html);
    }

    function renderSectionsPanel() {
        const g = state.classGroups[state.currentClassIndex];
        if (!g) {
            $('#selectedClassInfo').empty();
            $('#sectionsContainer').html('<div class="alert alert-info">Select a class or assign subjects in <a href="<?= base_url('admin/section_subjects') ?>">Section Subjects</a>.</div>');
            return;
        }
        $('#selectedClassInfo').html('<h4 class="mb-0">' + escapeHtml(g.class_name) + '</h4>');
        const sumMap = state.validation.sum_by_section || {};
        let html = '';
        g.sections.forEach(function (sec) {
            if (!sectionPassesFilters(sec)) return;
            const key = Number(sec.cls_sec_id);
            const cap = Number((state.capacity[key] && state.capacity[key].capacity) || 0);
            const sectionIncluded = isSectionIncluded(key);
            const req = sectionIncluded ? Number(sumMap[key] || 0) : 0;
            const overflow = sectionIncluded && req > cap;
            const pct = cap > 0 ? Math.min(100, Math.round((req / cap) * 100)) : 0;
            const borderCls = !sectionIncluded ? 'is-section-off' : (overflow ? 'border-danger' : 'border-success');

            html += '<div class="tt-gen-section-card tt-section-card ' + borderCls + '" data-section-id="' + key + '">';
            html += '<div class="d-flex flex-wrap justify-content-between align-items-start mb-2">';
            html += '<div class="d-flex align-items-center flex-wrap">';
            html += '<label class="mb-0 me-2" title="Include section in timetable">';
            html += '<input type="checkbox" class="include-section-timetable me-1" data-cid="' + key + '" ' + (sectionIncluded ? 'checked' : '') + '>';
            html += '<strong>Section ' + escapeHtml(sec.section_name) + '</strong></label>';
            html += '</div>';
            html += '<div class="d-flex flex-wrap align-items-center mt-1 mt-md-0">';
            html += '<span class="badge text-bg-info me-2">Capacity: <span class="sec-capacity">' + cap + '</span></span>';
            html += '<span class="badge sec-requested me-2 ' + (!sectionIncluded ? 'text-bg-secondary' : (overflow ? 'text-bg-danger' : 'text-bg-success')) + '">Requested: ' + req + '</span>';
            html += '<button type="button" class="btn btn-outline-secondary btn-sm section-include-all me-1" data-cid="' + key + '" ' + (sectionIncluded ? '' : 'disabled') + '>All</button>';
            html += '<button type="button" class="btn btn-outline-secondary btn-sm section-include-none me-1" data-cid="' + key + '" ' + (sectionIncluded ? '' : 'disabled') + '>None</button>';
            html += '<input type="number" min="0" class="form-control form-control-sm section-bulk-value me-1" data-cid="' + key + '" style="width:72px;" placeholder="0" ' + (sectionIncluded ? '' : 'disabled') + '>';
            html += '<button type="button" class="btn btn-outline-primary btn-sm section-bulk-apply" data-cid="' + key + '" ' + (sectionIncluded ? '' : 'disabled') + '>Set</button>';
            html += '</div></div>';
            html += '<div class="tt-gen-cap-bar"><div class="tt-gen-cap-fill ' + (overflow ? 'is-overflow' : '') + '" style="width:' + pct + '%"></div></div>';
            html += '<div class="row">';

            sec.subjects.forEach(function (entry) {
                const i = entry.idx;
                const r = entry.row;
                const included = Number(r.include_in_timetable || 0) === 1;
                const active = sectionIncluded && included;
                html += '<div class="col-md-4 col-sm-6">';
                html += '<div class="tt-gen-subject-item tt-subject-cell ' + (active ? 'is-included' : '') + (sectionIncluded ? '' : ' is-disabled') + '" data-row-index="' + i + '">';
                html += '<div class="form-check form-check mb-1">';
                html += '<input type="checkbox" class="form-check-input include-timetable" id="inc-' + i + '" data-idx="' + i + '" ' + (included ? 'checked' : '') + ' ' + (sectionIncluded ? '' : 'disabled') + '>';
                html += '<label class="form-check-label fw-bold" for="inc-' + i + '">' + escapeHtml(r.subject_name || '') + '</label>';
                html += '</div>';
                html += '<label class="small text-muted mb-0">Classes / week</label>';
                html += '<input type="number" min="0" class="form-control form-control-sm weekly-classes tt-gen-weekly-input text-center" value="' + Number(r.weekly_classes || 0) + '" data-idx="' + i + '" ' + (active ? '' : 'disabled') + '>';
                html += '</div></div>';
            });

            html += '</div></div>';
        });

        if (!html) {
            html = '<div class="alert alert-light border">No sections match the current filters for this class.</div>';
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
                '<p class="text-muted">No subjects assigned to any section.</p>' +
                '<p class="small"><a href="<?= base_url('admin/section_subjects') ?>">Section Subjects</a> · ' +
                '<a href="<?= base_url('admin/academic-setup') ?>">Academic Setup</a></p></div>'
            );
            return;
        }
        renderClassSidebar();
        renderSectionsPanel();
    }

    function updateSubjectCell(idx) {
        const r = state.rows[idx];
        if (!r) return;
        const $cell = $('.tt-subject-cell[data-row-index="' + idx + '"]');
        if (!$cell.length) return;
        const sectionIncluded = isSectionIncluded(r.cls_sec_id);
        const included = Number(r.include_in_timetable || 0) === 1;
        const active = sectionIncluded && included;
        $cell.find('.include-timetable').prop('checked', included).prop('disabled', !sectionIncluded);
        $cell.find('.weekly-classes').val(Number(r.weekly_classes || 0)).prop('disabled', !active);
        $cell.toggleClass('is-included', active).toggleClass('is-disabled', !sectionIncluded);
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

        $card.toggleClass('is-section-off', !sectionIncluded)
            .toggleClass('border-danger', sectionIncluded && overflow)
            .toggleClass('border-success', sectionIncluded && !overflow);
        $card.find('.include-section-timetable').prop('checked', sectionIncluded);
        $card.find('.sec-capacity').text(cap);
        $card.find('.sec-requested')
            .removeClass('text-bg-danger text-bg-success text-bg-secondary')
            .addClass(!sectionIncluded ? 'text-bg-secondary' : (overflow ? 'text-bg-danger' : 'text-bg-success'))
            .text('Requested: ' + req);
        $card.find('.tt-gen-cap-fill').css('width', pct + '%').toggleClass('is-overflow', overflow);
        $card.find('.section-include-all, .section-include-none, .section-bulk-apply, .section-bulk-value').prop('disabled', !sectionIncluded);
        state.rows.forEach(function (r, idx) {
            if (Number(r.cls_sec_id) === key) updateSubjectCell(idx);
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
            $('#capacityAlertWrap').html('<div class="alert alert-success py-2 mb-0">All sections are within weekly slot capacity.</div>');
        } else {
            let html = '<div class="alert alert-danger py-2 mb-0"><strong>Capacity overflow:</strong><ul class="mb-0 ps-3">';
            over.forEach(function (o) {
                const sec = state.sectionOptions.find(function (s) { return Number(s.cls_sec_id) === Number(o.cls_sec_id); });
                html += '<li>' + escapeHtml(sec ? sec.label : ('Section ' + o.cls_sec_id)) + ': ' + o.requested + ' &gt; ' + o.capacity + '</li>';
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
        html += '<a href="<?= base_url('admin/section_subjects') ?>">Section Subjects</a><ul class="mb-0 ps-3 small">';
        items.forEach(function (m) { html += '<li>' + escapeHtml(m.label || '') + '</li>'; });
        html += '</ul></div>';
        $('#missingSectionAlertWrap').html(html);
    }

    function paintSummary() {
        if (!Number.isFinite(state.placedCount)) {
            $('#generateSummary').html('<div class="alert alert-info mb-0">No generation run yet. Save constraints, then click Generate.</div>');
            return;
        }
        let msg = '<strong>Placed:</strong> ' + Number(state.placedCount);
        if (Number.isFinite(state.unplacedCount) && state.unplacedCount > 0) {
            msg += ' &nbsp;|&nbsp; <strong>Unplaced:</strong> ' + Number(state.unplacedCount);
        }
        msg += '<div class="mt-3"><a href="' + reportAdjustUrl + '" class="btn btn-info btn-sm">' +
            '<i class="fas fa-sliders-h"></i> Open Timetable Report &amp; Adjust</a></div>';
        $('#generateSummary').html('<div class="alert alert-success mb-0">' + msg + '</div>');
    }

    function collectPayload() {
        state.options.use_monday_template = $('#useMondayTemplate').is(':checked') ? 1 : 0;
        state.options.strict_mode = $('#strictMode').is(':checked') ? 1 : 0;
        state.options.friday_active_slots = $('.friday-slot:checked').map(function () { return Number($(this).val()); }).get();
        if (!state.options.friday_active_slots.length && state.slots.length) {
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
                    toastr.success(res.msg || 'Saved');
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

    $(document).on('click', '.tt-gen-class-card', function () {
        state.currentClassIndex = Number($(this).data('class-index'));
        renderClassSidebar();
        renderSectionsPanel();
    });

    $(document).on('change', '.include-section-timetable', function () {
        const cid = Number($(this).data('cid'));
        state.sectionInclude[cid] = $(this).is(':checked') ? 1 : 0;
        recomputeValidation();
        updateSectionCard(cid);
        paintValidation();
        queueAutoSave();
    });

    $(document).on('change', '.include-timetable', function () {
        const idx = Number($(this).data('idx'));
        state.rows[idx].include_in_timetable = $(this).is(':checked') ? 1 : 0;
        recomputeValidation();
        updateSubjectCell(idx);
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

    $('#demandSearch').on('input', function () {
        renderSectionsPanel();
    });

    $('#showOverflowOnly').on('change', function () {
        renderSectionsPanel();
    });

    $(document).on('click', '.section-include-all', function () {
        const cid = Number($(this).data('cid'));
        if (!isSectionIncluded(cid)) return;
        state.rows.forEach(function (r) {
            if (Number(r.cls_sec_id) === cid) r.include_in_timetable = 1;
        });
        recomputeValidation();
        updateSectionCard(cid);
        paintValidation();
        queueAutoSave();
    });

    $(document).on('click', '.section-include-none', function () {
        const cid = Number($(this).data('cid'));
        if (!isSectionIncluded(cid)) return;
        state.rows.forEach(function (r) {
            if (Number(r.cls_sec_id) === cid) r.include_in_timetable = 0;
        });
        recomputeValidation();
        updateSectionCard(cid);
        paintValidation();
        queueAutoSave();
    });

    $(document).on('click', '.section-bulk-apply', function () {
        const cid = Number($(this).data('cid'));
        let weekly = Number($('.section-bulk-value[data-cid="' + cid + '"]').val());
        if (!Number.isFinite(weekly) || weekly < 0) weekly = 0;
        state.rows.forEach(function (r, idx) {
            if (Number(r.cls_sec_id) === cid && Number(r.include_in_timetable || 0) === 1 && isSectionIncluded(cid)) {
                r.weekly_classes = weekly;
                updateSubjectCell(idx);
            }
        });
        recomputeValidation();
        updateSectionCard(cid);
        paintValidation();
        queueAutoSave();
    });

    $('#setAllZeroBtn').on('click', function () {
        state.rows.forEach(function (r, idx) {
            r.weekly_classes = 0;
            updateSubjectCell(idx);
        });
        recomputeValidation();
        state.classGroups.forEach(function (_, i) {
            state.classGroups[i].sections.forEach(function (sec) {
                updateSectionCard(sec.cls_sec_id);
            });
        });
        paintValidation();
        queueAutoSave();
    });

    $('#applyBulkWeeklyBtn').on('click', function () {
        const cid = Number($('#bulkClassSection').val());
        let weekly = Number($('#bulkWeeklyValue').val());
        if (!cid) { toastr.warning('Select a section.'); return; }
        if (!isSectionIncluded(cid)) { toastr.warning('That section is excluded.'); return; }
        if (!Number.isFinite(weekly) || weekly < 0) weekly = 0;
        state.rows.forEach(function (r, idx) {
            if (Number(r.cls_sec_id) === cid && Number(r.include_in_timetable || 0) === 1) {
                r.weekly_classes = weekly;
                updateSubjectCell(idx);
            }
        });
        recomputeValidation();
        updateSectionCard(cid);
        paintValidation();
        queueAutoSave();
    });

    $('#saveConstraintsBtn').on('click', function () {
        const $btn = $(this);
        const old = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
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
        const $btn = $(this);
        const old = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generating...');
        $.ajax({ url: generateUrl, method: 'POST', dataType: 'json' })
            .done(function (res) {
                if (res && res.success) {
                    state.placedCount = Number(res.placed_count || 0);
                    state.unplacedCount = Number(res.unplaced_count || 0);
                    paintSummary();
                    toastr.success(res.msg || 'Generated');
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

    paintSummary();
    loadBootstrap();
});
</script>

<?= $this->endSection() ?>
