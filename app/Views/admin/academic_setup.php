<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>
<?php
$wb = isset($wizard_bootstrap) && is_array($wizard_bootstrap) ? $wizard_bootstrap : [];
$system_id = (int) ($wb['system_id'] ?? 0);
$campus_id = (int) ($wb['campus_id'] ?? 0);
?>

<link rel="stylesheet" href="<?= base_url('assets/css/school_setup_wizard.css') ?>?v=1">

<?= view('components/page_header', [
    'title' => 'Academic Setup',
    'icon' => 'fas fa-graduation-cap',
    'subtitle' => 'Define classes, sections, subjects, and assignments.',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Academic Setup', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="container-fluid setup-wizard-page">
        <?= view('admin/partials/setup_step_context', ['setup_step_id' => 'academic']) ?>
        <?php if ($campus_id === 0) : ?>
            <div class="alert alert-warning border-0 shadow-sm mb-3">
                <strong>Campus not detected.</strong> Class–section and subject links need a campus. Log in with a campus selected, or set campus in school/session settings, then reload this page.
            </div>
        <?php endif; ?>

        <?php
        $academicSteps = [
            ['id' => '1', 'step' => 1, 'label' => 'Classes', 'icon' => 'fa-layer-group', 'hint' => 'Grade levels', 'count_id' => 'countStep1'],
            ['id' => '2', 'step' => 2, 'label' => 'Sections', 'icon' => 'fa-users', 'hint' => 'A, B, C…', 'count_id' => 'countStep2'],
            ['id' => '3', 'step' => 3, 'label' => 'Subjects', 'icon' => 'fa-book-open', 'hint' => 'Courses', 'count_id' => 'countStep3'],
            ['id' => '4', 'step' => 4, 'label' => 'Assignments', 'icon' => 'fa-link', 'hint' => 'Link all', 'count_id' => 'countStep4'],
        ];
        ?>

        <div class="setup-wizard-shell fee-setup-shell">
            <?= view('admin/partials/setup_wizard_nav', [
                'steps' => $academicSteps,
                'active_step' => '1',
                'mode' => 'js',
                'total_steps' => 4,
            ]) ?>

            <div class="setup-wizard-body fee-setup-body">

        <!-- Step 1: Classes -->
        <div id="step1" class="setup-wizard-pane step-content is-visible">
            <div class="setup-wizard-panel fee-setup-panel">
                <div class="setup-toolbar">
                    <div class="setup-meta">
                        <div class="meta-item">
                            <label>Step</label>
                            <span class="meta-value">Add Classes</span>
                        </div>
                    </div>
                    <div class="setup-toolbar-actions">
                        <button type="button" class="btn btn-sm btn-primary" id="addClassBtn">
                            <i class="fa fa-plus"></i> Add Class
                        </button>
                    </div>
                </div>
                <div class="grid-card">
                    <div class="grid-scroll grid-scroll--auto">
                        <table class="table setup-wizard-table fee-setup-table mb-0" id="classesTable">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="45%">Class Name</th>
                                    <th width="40%">Short Name</th>
                                    <th width="10%" class="text-center">Remove / Active</th>
                                </tr>
                            </thead>
                            <tbody id="classesBody">
                                <tr class="class-row" data-new-row="1">
                                    <td>1</td>
                                    <td><input type="text" name="class_name[]" class="form-control form-control-sm" placeholder="e.g., Pre-Nursery" required></td>
                                    <td><input type="text" name="class_short[]" class="form-control form-control-sm" placeholder="e.g., PN" required></td>
                                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger remove-row" title="Remove this unsaved row"><i class="fa fa-trash"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <?= view('admin/partials/setup_wizard_footer', [
                        'hint' => 'Unsaved rows can be removed with the trash icon. After saving, use <strong>Active</strong> to turn a class on or off.',
                        'show_prev' => false,
                        'show_next' => true,
                        'next_label' => 'Next: Sections',
                    ]) ?>
                </div>
            </div>
        </div>

        <!-- Step 2: Sections -->
        <div id="step2" class="setup-wizard-pane step-content">
            <div class="setup-wizard-panel fee-setup-panel">
                <div class="setup-toolbar">
                    <div class="setup-meta">
                        <div class="meta-item">
                            <label>Step</label>
                            <span class="meta-value">Add Sections</span>
                        </div>
                    </div>
                    <div class="setup-toolbar-actions">
                        <button type="button" class="btn btn-sm btn-primary" id="addSectionBtn">
                            <i class="fa fa-plus"></i> Add Section
                        </button>
                    </div>
                </div>
                <div class="grid-card">
                    <div class="grid-scroll grid-scroll--auto">
                        <table class="table setup-wizard-table fee-setup-table mb-0" id="sectionsTable">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="45%">Section Name</th>
                                    <th width="45%">Short Name</th>
                                    <th width="10%" class="text-center">Remove / Active</th>
                                </tr>
                            </thead>
                            <tbody id="sectionsBody">
                                <tr class="section-row" data-new-row="1">
                                    <td>1</td>
                                    <td><input type="text" name="section_name[]" class="form-control form-control-sm" placeholder="e.g., Section A" required></td>
                                    <td><input type="text" name="section_short[]" class="form-control form-control-sm" placeholder="e.g., A"></td>
                                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger remove-row" title="Remove this unsaved row"><i class="fa fa-trash"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <?= view('admin/partials/setup_wizard_footer', [
                        'hint' => 'Unsaved rows can be deleted with the trash icon. After saving, use <strong>Active</strong> only—sections stay in the database.',
                        'show_prev' => true,
                        'show_next' => true,
                        'prev_label' => 'Previous: Classes',
                        'next_label' => 'Next: Subjects',
                    ]) ?>
                </div>
            </div>
        </div>

        <!-- Step 3: Subjects -->
        <div id="step3" class="setup-wizard-pane step-content">
            <div class="setup-wizard-panel fee-setup-panel">
                <div class="setup-toolbar">
                    <div class="setup-meta">
                        <div class="meta-item">
                            <label>Step</label>
                            <span class="meta-value">Add Subjects</span>
                        </div>
                    </div>
                    <div class="setup-toolbar-actions">
                        <button type="button" class="btn btn-sm btn-primary" id="addSubjectBtn">
                            <i class="fa fa-plus"></i> Add Subject
                        </button>
                    </div>
                </div>
                <div class="grid-card">
                    <div class="grid-scroll grid-scroll--auto">
                        <table class="table setup-wizard-table fee-setup-table mb-0" id="subjectsTable">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="45%">Subject Name</th>
                                    <th width="45%">Short Name</th>
                                    <th width="10%" class="text-center">Remove / Active</th>
                                </tr>
                            </thead>
                            <tbody id="subjectsBody">
                                <tr class="subject-row" data-new-row="1">
                                    <td>1</td>
                                    <td><input type="text" name="subject_name[]" class="form-control form-control-sm" placeholder="e.g., Mathematics" required></td>
                                    <td><input type="text" name="subject_short[]" class="form-control form-control-sm" placeholder="e.g., Math"></td>
                                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger remove-row" title="Remove this unsaved row"><i class="fa fa-trash"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <?= view('admin/partials/setup_wizard_footer', [
                        'hint' => 'Unsaved rows can be deleted with the trash icon. After saving, use <strong>Active</strong> only—subjects stay in the database.',
                        'show_prev' => true,
                        'show_next' => true,
                        'prev_label' => 'Previous: Sections',
                        'next_label' => 'Next: Assignments',
                    ]) ?>
                </div>
            </div>
        </div>

        <!-- Step 4: Assignments -->
        <div id="step4" class="setup-wizard-pane step-content">
            <div class="setup-wizard-panel fee-setup-panel mb-3">
                <div class="setup-toolbar">
                    <div class="setup-meta">
                        <div class="meta-item">
                            <label>Step</label>
                            <span class="meta-value">Class–Section Links</span>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info setup-wizard-alert mb-3">
                    <i class="fa fa-info-circle"></i> Select which sections belong to each class. A class can have multiple sections.
                </div>
                <div class="grid-card mb-3">
                    <div class="grid-scroll grid-scroll--auto p-2" id="classSectionsAssignment">
                        <div class="text-center text-muted py-5">
                            <i class="fa fa-spinner fa-spin fa-2x"></i>
                            <p>Loading classes and sections...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="setup-wizard-panel fee-setup-panel">
                <div class="setup-toolbar">
                    <div class="setup-meta">
                        <div class="meta-item">
                            <label>Step</label>
                            <span class="meta-value">Subject Assignments</span>
                        </div>
                    </div>
                    <div class="setup-toolbar-actions">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="enableTimetableSetup">
                            <label class="form-check-label" for="enableTimetableSetup"><strong>Enable timetable setup</strong></label>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info setup-wizard-alert mb-2">
                    <i class="fa fa-info-circle"></i> Select which subjects are taught in each section.
                </div>
                <div class="alert alert-secondary py-2 d-none setup-wizard-alert mb-2" id="timetableSetupHelp">
                    <i class="fa fa-clock"></i> Set <strong>Classes/week</strong> for each assigned subject. This value is saved permanently and auto-used in Timetable Generator.
                </div>
                <div class="grid-card">
                    <div class="grid-scroll grid-scroll--auto p-2" id="sectionSubjectsAssignment">
                        <div class="text-center text-muted py-5">
                            <i class="fa fa-spinner fa-spin fa-2x"></i>
                            <p>Loading sections and subjects...</p>
                        </div>
                    </div>
                    <?= view('admin/partials/setup_wizard_footer', [
                        'hint' => 'Link classes to sections first; subject cards appear for each linked pair.',
                        'show_prev' => true,
                        'show_finish' => true,
                        'prev_label' => 'Previous: Subjects',
                    ]) ?>
                </div>
            </div>
        </div>

            </div>
        </div>
    </div>
</section>

<script>
function sortClassSections(list) {
    return (list || []).slice().sort(function (a, b) {
        const classCmp = (parseInt(a.class_id, 10) || 0) - (parseInt(b.class_id, 10) || 0);
        if (classCmp !== 0) {
            return classCmp;
        }
        return (parseInt(a.section_id, 10) || 0) - (parseInt(b.section_id, 10) || 0);
    });
}

$(document).ready(function() {
    let currentStep = 1;
    let classesData = [];
    let sectionsData = [];
    let subjectsData = [];

    const BOOTSTRAP_URL = '<?= base_url('admin/academic-setup/bootstrap-data') ?>';

    function bootstrapPayload() {
        return window.ACADEMIC_WIZARD_BOOTSTRAP || {};
    }

    function updateWizardCounts() {
        const b = bootstrapPayload();
        const nc = (b.classes || []).length;
        const ns = (b.sections || []).length;
        const nsub = (b.subjects || []).length;
        const ncs = (b.class_sections || []).length;
        $('#countStep1').text(nc);
        $('#countStep2').text(ns);
        $('#countStep3').text(nsub);
        $('#countStep4').text(ncs > 0 ? ncs + ' links' : '—');
        $('#wizardStepLabel').text('Step ' + currentStep + ' of 4');
        const pct = Math.round((currentStep / 4) * 100);
        $('#wizardProgressBar').css('width', pct + '%').attr('aria-valuenow', String(pct));
    }

    function parseBootstrapJson(text) {
        var raw = (text == null ? '' : String(text)).replace(/^\uFEFF/, '').trim();
        // Real payload always starts with {"success":...} — skip HTML/CSS/PHP noise or an extra "{"
        var m = raw.match(/\{\s*"success"\s*:/);
        if (m && m.index >= 0) {
            raw = raw.substring(m.index);
        } else {
            var i = raw.indexOf('{');
            if (i > 0) {
                raw = raw.substring(i);
            }
        }
        // "{{" at start breaks JSON.parse at position 1 — drop duplicate leading "{"
        while (raw.length > 1 && raw.charAt(0) === '{' && raw.charAt(1) === '{') {
            raw = raw.substring(1);
        }
        return JSON.parse(raw);
    }

    function refreshWizardBootstrap(done) {
        $.ajax({
            url: BOOTSTRAP_URL,
            method: 'GET',
            dataType: 'text',
            cache: false,
            timeout: 120000
        }).done(function (text) {
            var res;
            try {
                res = parseBootstrapJson(text);
            } catch (e) {
                console.error('academic-wizard-bootstrap parse error', e, (text || '').substring(0, 500));
                toastr.error('Invalid JSON from server. Check Network → academic-wizard-bootstrap → Response. ' + (e.message || ''));
                if (typeof done === 'function') {
                    done(null);
                }
                return;
            }
            if (res && res.success) {
                window.ACADEMIC_WIZARD_BOOTSTRAP = res;
                classesData = res.classes || [];
                sectionsData = res.sections || [];
                subjectsData = res.subjects || [];
                hydrateTablesFromBootstrap();
            } else {
                toastr.error((res && res.msg) || 'Could not refresh data');
            }
            if (typeof done === 'function') {
                done(res);
            }
        }).fail(function (xhr, textStatus, err) {
            var msg = 'Network error while refreshing data';
            if (textStatus === 'timeout') {
                msg = 'Request timed out (120s). Server may be slow; try again or narrow data.';
            } else if (textStatus === 'parsererror') {
                msg = 'Response was not valid JSON (parsererror). Open academic-wizard-bootstrap in a new tab and look for HTML or PHP notices before {.';
            } else if (xhr.status === 0) {
                msg = 'Request failed (status 0). Check connection / mixed content (HTTPS page must call HTTPS URLs).';
            } else if (xhr.status >= 400) {
                msg = 'Server returned HTTP ' + xhr.status + '.';
            }
            if (xhr.responseText) {
                var t = xhr.responseText.trim();
                if (t.charAt(0) === '{') {
                    try {
                        var j = JSON.parse(t);
                        if (j && j.msg) {
                            msg = j.msg;
                        }
                    } catch (ignore) {}
                }
            }
            toastr.error(msg);
            if (typeof done === 'function') {
                var failRes = null;
                if (xhr.responseText) {
                    var t = xhr.responseText.trim();
                    if (t.charAt(0) === '{') {
                        try {
                            failRes = JSON.parse(t);
                        } catch (ignore) {}
                    }
                }
                done(failRes || { success: false, msg: msg });
            }
        });
    }

    function applyStep4FromBootstrap(b) {
        b = b || bootstrapPayload();
        renderClassSectionsAssignment(b.classes || [], b.sections || [], b.class_sections || []);
        renderSectionSubjectsAssignment(b.class_sections || [], b.subjects || [], b.section_subjects || []);
        updateWizardCounts();
    }

    let classSectionSyncTimer = null;
    let classSectionSaveChain = $.when();

    function collectClassSectionAssignments() {
        const assignments = [];
        $('.class-section-checkbox').each(function () {
            assignments.push({
                class_id: $(this).data('class-id'),
                section_id: $(this).data('section-id'),
                status: $(this).is(':checked') ? 1 : 0
            });
        });
        return assignments;
    }

    function mergeActiveClassSectionsIntoBootstrap(activeList) {
        const b = bootstrapPayload();
        b.class_sections = activeList || [];
        window.ACADEMIC_WIZARD_BOOTSTRAP = b;
    }

    function buildClassSectionsFromChecks(b) {
        b = b || bootstrapPayload();
        const classesById = {};
        const sectionsById = {};
        (b.classes || []).forEach(function (c) {
            classesById[String(c.class_id)] = c;
        });
        (b.sections || []).forEach(function (s) {
            sectionsById[String(s.section_id)] = s;
        });
        const savedMap = {};
        (b.class_sections || []).forEach(function (cs) {
            savedMap[String(cs.class_id) + '|' + String(cs.section_id)] = cs;
        });

        const result = [];
        const seen = {};
        $('.class-section-checkbox:checked').each(function () {
            const classId = String($(this).data('class-id'));
            const sectionId = String($(this).data('section-id'));
            const key = classId + '|' + sectionId;
            if (seen[key]) {
                return;
            }
            seen[key] = true;
            if (savedMap[key]) {
                result.push(savedMap[key]);
                return;
            }
            const cls = classesById[classId];
            const sec = sectionsById[sectionId];
            if (!cls || !sec) {
                return;
            }
            result.push({
                cls_sec_id: 0,
                class_id: classId,
                section_id: sectionId,
                class_name: cls.class_name || '',
                section_name: sec.section_name || '',
                _pending: true
            });
        });

        return sortClassSections(result);
    }

    function refreshSubjectAssignmentFromChecks() {
        const b = bootstrapPayload();
        renderSectionSubjectsAssignment(
            buildClassSectionsFromChecks(b),
            b.subjects || [],
            b.section_subjects || []
        );
    }

    function scheduleClassSectionsSync() {
        if (currentStep !== 4) {
            return;
        }
        clearTimeout(classSectionSyncTimer);
        classSectionSyncTimer = setTimeout(function () {
            classSectionSaveChain = classSectionSaveChain.then(function () {
                return syncClassSectionsAndRefreshSubjects();
            });
        }, 400);
    }

    function syncClassSectionsAndRefreshSubjects() {
        if (currentStep !== 4) {
            return $.when();
        }

        const assignments = collectClassSectionAssignments();
        if (assignments.length === 0) {
            refreshSubjectAssignmentFromChecks();
            return $.when();
        }

        $('#classSectionsAssignment').addClass('cs-matrix-saving');

        return saveClassSectionsData({ silent: true, allowEmpty: true })
            .then(function (res) {
                if (res && Array.isArray(res.class_sections)) {
                    mergeActiveClassSectionsIntoBootstrap(res.class_sections);
                    return res;
                }
                return refreshWizardBootstrapPromise();
            })
            .then(function (res) {
                if (res && res.success && Array.isArray(res.class_sections)) {
                    mergeActiveClassSectionsIntoBootstrap(res.class_sections);
                }
                const b = bootstrapPayload();
                renderSectionSubjectsAssignment(
                    b.class_sections || [],
                    b.subjects || [],
                    b.section_subjects || []
                );
                updateWizardCounts();
            })
            .fail(function () {
                const b = bootstrapPayload();
                renderClassSectionsAssignment(b.classes || [], b.sections || [], b.class_sections || []);
                renderSectionSubjectsAssignment(
                    b.class_sections || [],
                    b.subjects || [],
                    b.section_subjects || []
                );
            })
            .always(function () {
                $('#classSectionsAssignment').removeClass('cs-matrix-saving');
            });
    }

    function hasBootstrapPayload(b) {
        b = b || {};
        return !!(b.system_id || (b.classes && b.classes.length) || (b.sections && b.sections.length));
    }

    function assignmentSpinner(msg) {
        return '<div class="text-center text-muted py-4"><i class="fa fa-spinner fa-spin fa-2x"></i><p class="mb-0">' + msg + '</p></div>';
    }

    function loadStep4Assignments() {
        $('#classSectionsAssignment').html(assignmentSpinner('Loading class–section matrix…'));
        $('#sectionSubjectsAssignment').html(assignmentSpinner('Loading subject assignments…'));
        refreshWizardBootstrap(function (res) {
            if (res && res.success) {
                applyStep4FromBootstrap(res);
                return;
            }

            const embedded = bootstrapPayload();
            if (hasBootstrapPayload(embedded)) {
                applyStep4FromBootstrap(embedded);
                if (res && res.msg) {
                    toastr.warning(res.msg);
                }
                return;
            }

            var why = (res && res.msg) ? $('<div/>').text(res.msg).html() : '';
            $('#classSectionsAssignment').html(
                '<div class="alert alert-danger mb-0">Could not load assignments. ' +
                (why ? ('<br><small>' + why + '</small>') : 'Try again.') + '</div>'
            );
            $('#sectionSubjectsAssignment').html('');
        });
    }

    function hydrateTablesFromBootstrap() {
        const b = bootstrapPayload();
        classesData = b.classes || [];
        sectionsData = b.sections || [];
        subjectsData = b.subjects || [];
        if (classesData.length > 0) {
            $('#classesBody').empty();
            classesData.forEach(function (cls, index) {
                const id = parseInt(cls.class_id, 10) || 0;
                const st = cls.status !== undefined && cls.status !== null ? parseInt(cls.status, 10) : 1;
                addClassRow(cls.class_name || '', cls.class_short_name || '', index + 1, id, isNaN(st) ? 1 : st);
            });
        }
        if (sectionsData.length > 0) {
            $('#sectionsBody').empty();
            sectionsData.forEach(function (section, index) {
                const id = parseInt(section.section_id, 10) || 0;
                const st = section.status !== undefined && section.status !== null ? parseInt(section.status, 10) : 1;
                addSectionRow(section.section_name || '', section.short_name || '', index + 1, id, isNaN(st) ? 1 : st);
            });
        }
        if (subjectsData.length > 0) {
            $('#subjectsBody').empty();
            subjectsData.forEach(function (subject, index) {
                const id = parseInt(subject.sid, 10) || 0;
                const st = subject.status !== undefined && subject.status !== null ? parseInt(subject.status, 10) : 1;
                addSubjectRow(subject.subject_name || '', subject.subject_short_name || '', index + 1, id, isNaN(st) ? 1 : st);
            });
        }
        updateWizardCounts();
    }

    window.ACADEMIC_WIZARD_BOOTSTRAP = window.ACADEMIC_WIZARD_BOOTSTRAP || <?= json_encode($wb, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) ?: '{}' ?>;
    hydrateTablesFromBootstrap();
    updateStepIndicators();

    // Add class row
    $('#addClassBtn').click(function() {
        addClassRow('', '', $('#classesBody tr').length + 1, 0, 1);
    });

    function addClassRow(name, short, count, entityId, status) {
        entityId = parseInt(entityId, 10) || 0;
        status = (parseInt(status, 10) === 0) ? 0 : 1;
        const isNew = entityId <= 0;
        let actionHtml;
        if (isNew) {
            actionHtml = '<button type="button" class="btn btn-sm btn-danger remove-row" title="Remove this unsaved row"><i class="fa fa-trash"></i></button>';
        } else {
            const uid = 'class-active-' + entityId;
            const checked = status ? ' checked' : '';
            actionHtml = '<div class="form-check form-switch text-start d-inline-block">' +
                '<input type="checkbox" class="form-check-input entity-active-toggle" id="' + uid + '"' + checked + '>' +
                '<label class="form-check-label small" for="' + uid + '"><span class="toggle-label">' + (status ? 'Active' : 'Inactive') + '</span></label></div>' +
                '<input type="hidden" name="class_id[]" value="' + entityId + '">';
        }
        const inactiveClass = (!isNew && !status) ? ' table-secondary text-muted' : '';
        const dataNew = isNew ? ' data-new-row="1"' : '';
        const row = '<tr class="class-row' + inactiveClass + '"' + dataNew + '>' +
            '<td>' + count + '</td>' +
            '<td><input type="text" name="class_name[]" class="form-control form-control-sm" value="' + escapeHtml(name) + '" placeholder="e.g., Pre-Nursery" required></td>' +
            '<td><input type="text" name="class_short[]" class="form-control form-control-sm" value="' + escapeHtml(short) + '" placeholder="e.g., PN" required></td>' +
            '<td class="text-center align-middle">' + actionHtml + '</td></tr>';
        $('#classesBody').append(row);
        renumberRows('#classesBody');
    }

    // Add section row
    $('#addSectionBtn').click(function() {
        addSectionRow('', '', $('#sectionsBody tr').length + 1, 0, 1);
    });

    function addSectionRow(name, short, count, entityId, status) {
        entityId = parseInt(entityId, 10) || 0;
        status = (parseInt(status, 10) === 0) ? 0 : 1;
        const isNew = entityId <= 0;
        let actionHtml;
        if (isNew) {
            actionHtml = '<button type="button" class="btn btn-sm btn-danger remove-row" title="Remove this unsaved row"><i class="fa fa-trash"></i></button>';
        } else {
            const uid = 'section-active-' + entityId;
            const checked = status ? ' checked' : '';
            actionHtml = '<div class="form-check form-switch text-start d-inline-block">' +
                '<input type="checkbox" class="form-check-input entity-active-toggle" id="' + uid + '"' + checked + '>' +
                '<label class="form-check-label small" for="' + uid + '"><span class="toggle-label">' + (status ? 'Active' : 'Inactive') + '</span></label></div>' +
                '<input type="hidden" name="section_id[]" value="' + entityId + '">';
        }
        const inactiveClass = (!isNew && !status) ? ' table-secondary text-muted' : '';
        const dataNew = isNew ? ' data-new-row="1"' : '';
        const row = '<tr class="section-row' + inactiveClass + '"' + dataNew + '>' +
            '<td>' + count + '</td>' +
            '<td><input type="text" name="section_name[]" class="form-control form-control-sm" value="' + escapeHtml(name) + '" placeholder="e.g., Section A" required></td>' +
            '<td><input type="text" name="section_short[]" class="form-control form-control-sm" value="' + escapeHtml(short) + '" placeholder="e.g., A"></td>' +
            '<td class="text-center align-middle">' + actionHtml + '</td></tr>';
        $('#sectionsBody').append(row);
        renumberRows('#sectionsBody');
    }

    // Add subject row
    $('#addSubjectBtn').click(function() {
        addSubjectRow('', '', $('#subjectsBody tr').length + 1, 0, 1);
    });

    function addSubjectRow(name, short, count, entityId, status) {
        entityId = parseInt(entityId, 10) || 0;
        status = (parseInt(status, 10) === 0) ? 0 : 1;
        const isNew = entityId <= 0;
        let actionHtml;
        if (isNew) {
            actionHtml = '<button type="button" class="btn btn-sm btn-danger remove-row" title="Remove this unsaved row"><i class="fa fa-trash"></i></button>';
        } else {
            const uid = 'subject-active-' + entityId;
            const checked = status ? ' checked' : '';
            actionHtml = '<div class="form-check form-switch text-start d-inline-block">' +
                '<input type="checkbox" class="form-check-input entity-active-toggle" id="' + uid + '"' + checked + '>' +
                '<label class="form-check-label small" for="' + uid + '"><span class="toggle-label">' + (status ? 'Active' : 'Inactive') + '</span></label></div>' +
                '<input type="hidden" name="subject_id[]" value="' + entityId + '">';
        }
        const inactiveClass = (!isNew && !status) ? ' table-secondary text-muted' : '';
        const dataNew = isNew ? ' data-new-row="1"' : '';
        const row = '<tr class="subject-row' + inactiveClass + '"' + dataNew + '>' +
            '<td>' + count + '</td>' +
            '<td><input type="text" name="subject_name[]" class="form-control form-control-sm" value="' + escapeHtml(name) + '" placeholder="e.g., Mathematics" required></td>' +
            '<td><input type="text" name="subject_short[]" class="form-control form-control-sm" value="' + escapeHtml(short) + '" placeholder="e.g., Math"></td>' +
            '<td class="text-center align-middle">' + actionHtml + '</td></tr>';
        $('#subjectsBody').append(row);
        renumberRows('#subjectsBody');
    }

    $(document).on('change', '.entity-active-toggle', function () {
        const on = $(this).is(':checked');
        $(this).siblings('label').find('.toggle-label').text(on ? 'Active' : 'Inactive');
        $(this).closest('tr').toggleClass('table-secondary text-muted', !on);
    });

    // Remove row (only unsaved / new rows)
    $(document).on('click', '.remove-row', function() {
        const $tr = $(this).closest('tr');
        if ($tr.attr('data-new-row') !== '1') {
            toastr.warning('Saved records cannot be deleted. Turn off Active to deactivate.');
            return;
        }
        $tr.remove();
        renumberRows($(this).closest('tbody'));
    });

    function renumberRows(tbody) {
        $(tbody).find('tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    let navigationLock = false;

    function refreshWizardBootstrapPromise() {
        return $.Deferred(function (def) {
            refreshWizardBootstrap(function (res) {
                if (res && res.success) {
                    def.resolve(res);
                } else {
                    def.reject();
                }
            });
        }).promise();
    }

    function saveClassesData(options) {
        options = options || {};
        const defer = $.Deferred();
        const classes = [];
        $('#classesBody tr').each(function () {
            const $tr = $(this);
            const name = ($tr.find('input[name="class_name[]"]').val() || '').trim();
            const short = ($tr.find('input[name="class_short[]"]').val() || '').trim();
            if (!name && !short) {
                return;
            }
            if (!name || !short) {
                toastr.error('Each class needs both a name and a short name');
                defer.reject();
                return false;
            }
            const id = parseInt($tr.find('input[name="class_id[]"]').val(), 10) || 0;
            const status = $tr.find('.entity-active-toggle').length ? ($tr.find('.entity-active-toggle').is(':checked') ? 1 : 0) : 1;
            classes.push({ class_id: id, name: name, short_name: short, status: status });
        });
        if (defer.state() === 'rejected') {
            return defer.promise();
        }

        if (classes.length === 0) {
            toastr.error('Please add at least one class');
            return defer.reject().promise();
        }

        $.ajax({
            url: '<?= base_url("admin/academic-setup/save-classes") ?>',
            method: 'POST',
            data: { classes: classes },
            dataType: 'json'
        }).done(function (res) {
            if (res.success) {
                if (!options.silent) {
                    toastr.success(res.msg);
                }
                refreshWizardBootstrap(function () {
                    if (currentStep === 4) {
                        loadStep4Assignments();
                    }
                    defer.resolve(res);
                });
            } else {
                toastr.error(res.msg);
                defer.reject();
            }
        }).fail(function () {
            toastr.error('Network error');
            defer.reject();
        });

        return defer.promise();
    }

    function saveSectionsData(options) {
        options = options || {};
        const defer = $.Deferred();
        const sections = [];
        $('#sectionsBody tr').each(function () {
            const $tr = $(this);
            const name = ($tr.find('input[name="section_name[]"]').val() || '').trim();
            if (!name) {
                return;
            }
            const short = ($tr.find('input[name="section_short[]"]').val() || '').trim();
            const id = parseInt($tr.find('input[name="section_id[]"]').val(), 10) || 0;
            const status = $tr.find('.entity-active-toggle').length ? ($tr.find('.entity-active-toggle').is(':checked') ? 1 : 0) : 1;
            sections.push({ section_id: id, name: name, short_name: short, status: status });
        });

        if (sections.length === 0) {
            toastr.error('Please add at least one section');
            return defer.reject().promise();
        }

        $.ajax({
            url: '<?= base_url("admin/academic-setup/save-sections") ?>',
            method: 'POST',
            data: { sections: sections },
            dataType: 'json'
        }).done(function (res) {
            if (res.success) {
                if (!options.silent) {
                    toastr.success(res.msg);
                }
                refreshWizardBootstrap(function () {
                    if (currentStep === 4) {
                        loadStep4Assignments();
                    }
                    defer.resolve(res);
                });
            } else {
                toastr.error(res.msg);
                defer.reject();
            }
        }).fail(function () {
            toastr.error('Network error');
            defer.reject();
        });

        return defer.promise();
    }

    function saveSubjectsData(options) {
        options = options || {};
        const defer = $.Deferred();
        const subjects = [];
        $('#subjectsBody tr').each(function () {
            const $tr = $(this);
            const name = ($tr.find('input[name="subject_name[]"]').val() || '').trim();
            if (!name) {
                return;
            }
            const short = ($tr.find('input[name="subject_short[]"]').val() || '').trim();
            const id = parseInt($tr.find('input[name="subject_id[]"]').val(), 10) || 0;
            const status = $tr.find('.entity-active-toggle').length ? ($tr.find('.entity-active-toggle').is(':checked') ? 1 : 0) : 1;
            subjects.push({ subject_id: id, name: name, short_name: short, status: status });
        });

        if (subjects.length === 0) {
            toastr.error('Please add at least one subject');
            return defer.reject().promise();
        }

        $.ajax({
            url: '<?= base_url("admin/academic-setup/save-subjects") ?>',
            method: 'POST',
            data: { subjects: subjects },
            dataType: 'json'
        }).done(function (res) {
            if (res.success) {
                if (!options.silent) {
                    toastr.success(res.msg);
                }
                refreshWizardBootstrap(function () {
                    if (currentStep === 4) {
                        loadStep4Assignments();
                    }
                    defer.resolve(res);
                });
            } else {
                toastr.error(res.msg);
                defer.reject();
            }
        }).fail(function () {
            toastr.error('Network error');
            defer.reject();
        });

        return defer.promise();
    }

    function saveClassSectionsData(options) {
        options = options || {};
        const defer = $.Deferred();
        const assignments = collectClassSectionAssignments();

        if (assignments.length === 0) {
            if (options.allowEmpty) {
                return defer.resolve().promise();
            }
            toastr.warning('No class–section links to save');
            return defer.reject().promise();
        }

        const hasActive = assignments.some(function (a) {
            return parseInt(a.status, 10) === 1;
        });
        if (!hasActive && !options.allowEmpty) {
            toastr.warning('Please link at least one class to a section');
            return defer.reject().promise();
        }

        $.ajax({
            url: '<?= base_url("admin/academic-setup/save-class-sections") ?>',
            method: 'POST',
            data: { assignments: assignments },
            dataType: 'json',
            timeout: 30000
        }).done(function (res) {
            if (res.success) {
                if (!options.silent) {
                    toastr.success(res.msg);
                }
                defer.resolve(res);
            } else {
                toastr.error(res.msg || 'Save failed');
                defer.reject();
            }
        }).fail(function () {
            toastr.error('Network error');
            defer.reject();
        });

        return defer.promise();
    }

    function saveSectionSubjectsData(options) {
        options = options || {};
        const defer = $.Deferred();
        const timetableEnabled = isTimetableSetupEnabled();
        const assignments = [];
        $('.subject-checkbox:checked').each(function () {
            const clsSecId = parseInt($(this).data('cls-sec-id'), 10) || 0;
            if (clsSecId <= 0) {
                return;
            }
            const subjectId = $(this).data('subject-id');
            const item = {
                cls_sec_id: clsSecId,
                subject_id: subjectId
            };
            if (timetableEnabled) {
                const $weekly = $('.ss-weekly-classes[data-cls-sec-id="' + clsSecId + '"][data-subject-id="' + subjectId + '"]');
                let weekly = parseInt($weekly.val(), 10);
                if (!Number.isFinite(weekly) || weekly < 0) {
                    weekly = 0;
                }
                item.classes_per_week = weekly;
            }
            assignments.push(item);
        });

        if (assignments.length === 0 && !options.allowEmpty) {
            toastr.warning('Please assign at least one subject to a section');
            return defer.reject().promise();
        }

        $.ajax({
            url: '<?= base_url("admin/academic-setup/save-section-subjects") ?>',
            method: 'POST',
            data: { assignments: assignments },
            dataType: 'json',
            timeout: 30000
        }).done(function (res) {
            if (res.success) {
                if (Array.isArray(res.section_subjects)) {
                    const b = bootstrapPayload();
                    b.section_subjects = res.section_subjects;
                    window.ACADEMIC_WIZARD_BOOTSTRAP = b;
                }
                if (!options.silent) {
                    toastr.success(res.msg);
                }
                defer.resolve(res);
            } else {
                toastr.error(res.msg || 'Save failed');
                defer.reject();
            }
        }).fail(function () {
            toastr.error('Network error');
            defer.reject();
        });

        return defer.promise();
    }

    function saveStep4Data(options) {
        options = options || {};
        const allowEmpty = !!options.allowEmpty;
        return saveClassSectionsData({ silent: true, allowEmpty: allowEmpty })
            .then(function () {
                return saveSectionSubjectsData({ silent: true, allowEmpty: allowEmpty });
            })
            .then(function () {
                return refreshWizardBootstrapPromise().then(function () {
                    if (currentStep === 4) {
                        loadStep4Assignments();
                    }
                });
            });
    }

    function saveCurrentStep(options) {
        options = options || {};
        switch (currentStep) {
            case 1:
                return saveClassesData(options);
            case 2:
                return saveSectionsData(options);
            case 3:
                return saveSubjectsData(options);
            case 4:
                return saveStep4Data(options);
            default:
                return $.when();
        }
    }

    function goToStep(step) {
        $('.setup-wizard-pane, .step-content').removeClass('is-visible');
        $('#step' + step).addClass('is-visible');
        currentStep = step;
        updateStepIndicators();
        if (currentStep === 4) {
            loadStep4Assignments();
        }
    }

    function setNavigationBusy(busy) {
        const $els = $('.setup-wizard-next, .setup-wizard-prev, .next-step, .prev-step, .step-nav, .setup-wizard-finish, #finishSetupBtn');
        $els.prop('disabled', busy);
        if (busy) {
            $els.filter('.setup-wizard-next, .setup-wizard-prev, .next-step, .prev-step').prepend('<span class="wizard-nav-spinner fa fa-spinner fa-spin me-1"></span>');
        } else {
            $('.wizard-nav-spinner').remove();
        }
    }

    function navigateAfterSave(targetStep, options) {
        if (navigationLock) {
            return;
        }
        navigationLock = true;
        setNavigationBusy(true);

        const saveOpts = $.extend({ silent: true }, options || {});
        if (currentStep === 4 && targetStep !== 'finish' && targetStep < 4) {
            saveOpts.allowEmpty = true;
        }

        saveCurrentStep(saveOpts)
            .done(function () {
                if (targetStep === 'finish') {
                    runFinishSetup();
                } else if (typeof targetStep === 'number' && targetStep !== currentStep) {
                    goToStep(targetStep);
                }
            })
            .always(function () {
                navigationLock = false;
                setNavigationBusy(false);
            });
    }

    function runFinishSetup() {
        var $btn = $('#finishSetupBtn');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Checking...');
        var systemId = <?= $system_id ?? 0 ?>;

        $.ajax({
            url: '<?= base_url("admin/academic-setup/check-fee-types") ?>',
            method: 'GET',
            data: { system_id: systemId },
            dataType: 'json',
            timeout: 10000,
            success: function (res) {
                if (res.success) {
                    if (res.has_fee_types) {
                        toastr.success('Academic setup completed successfully!');
                    } else {
                        toastr.info('Next: configure your fee types and billing.');
                    }
                } else {
                    toastr.warning('Unable to verify fee setup. Opening fee configuration.');
                }
                setTimeout(function () {
                    window.location.href = '<?= base_url("admin/fee_setup?tab=types") ?>';
                }, 1500);
            },
            error: function () {
                toastr.warning('Could not verify fee setup. Opening fee configuration.');
                setTimeout(function () {
                    window.location.href = '<?= base_url("admin/fee_setup?tab=types") ?>';
                }, 1500);
            }
        });
    }

function renderClassSectionsAssignment(classes, sections, existing) {
    let actClasses = (classes || []).filter(function (c) {
        return parseInt(c.status, 10) === 1;
    });
    const actSections = (sections || []).filter(function (s) {
        return parseInt(s.status, 10) === 1;
    });
    actClasses.sort(function (a, b) {
        return (parseInt(a.class_id, 10) || 0) - (parseInt(b.class_id, 10) || 0);
    });
    actSections.sort(function (a, b) {
        return (parseInt(a.section_id, 10) || 0) - (parseInt(b.section_id, 10) || 0);
    });

    const seenClassIds = {};
    actClasses = actClasses.filter(function (c) {
        const id = String(c.class_id);
        if (seenClassIds[id]) {
            return false;
        }
        seenClassIds[id] = true;
        return true;
    });

    if (!actClasses.length) {
        $('#classSectionsAssignment').html('<div class="alert alert-warning">No <strong>active</strong> classes. Turn a class on in Step 1 (Active switch), or add new classes and return here.</div>');
        return;
    }

    if (!actSections.length) {
        $('#classSectionsAssignment').html('<div class="alert alert-warning">No <strong>active</strong> sections. Turn a section on in Step 2, or add new sections and return here.</div>');
        return;
    }

    let html = '<div class="table-responsive"><table class="table table-bordered table-sm academic-matrix"><thead><tr><th class="align-middle">Class</th>';
    actSections.forEach(function (section) {
        const sn = section.short_name ? escapeHtml(String(section.short_name)) : '—';
        html += '<th class="text-center small">' + escapeHtml(String(section.section_name)) + '<br><span class="text-muted">' + sn + '</span></th>';
    });
    html += '<th class="text-center text-nowrap" style="width:1%">Quick</th></tr></thead><tbody>';

    actClasses.forEach(function (cls) {
        html += '<tr><td class="align-middle"><strong>' + escapeHtml(String(cls.class_name)) + '</strong></td>';
        actSections.forEach(function (section) {
            const isChecked = existing && existing.some(function (e) {
                return String(e.class_id) === String(cls.class_id) && String(e.section_id) === String(section.section_id);
            });
            const toggleId = 'cs-' + cls.class_id + '-' + section.section_id;
            html += '<td class="text-center align-middle cs-matrix-cell">' +
                '<label class="cs-link-toggle" for="' + toggleId + '" title="Link ' + escapeHtml(String(cls.class_name)) + ' with ' + escapeHtml(String(section.section_name)) + '">' +
                '<input type="checkbox" class="class-section-checkbox" id="' + toggleId + '" data-class-id="' + cls.class_id + '" data-section-id="' + section.section_id + '"' + (isChecked ? ' checked' : '') + '>' +
                '<span class="cs-link-toggle-inner">' +
                '<i class="fas fa-plus-circle"></i>' +
                '<i class="fas fa-check-circle"></i>' +
                '<span class="cs-link-toggle-text">' + (isChecked ? 'Linked' : 'Link') + '</span>' +
                '</span></label></td>';
        });
        html += '<td class="text-center align-middle">' +
            '<div class="btn-group btn-group-sm" role="group">' +
            '<button type="button" class="btn btn-outline-primary js-row-all" data-class-id="' + cls.class_id + '" title="Select all sections for this class">All</button>' +
            '<button type="button" class="btn btn-outline-secondary js-row-none" data-class-id="' + cls.class_id + '" title="Clear this row">None</button>' +
            '</div></td></tr>';
    });

    html += '</tbody></table></div>';
    $('#classSectionsAssignment').html(html);
}

function renderSectionSubjectsAssignment(classSections, subjects, existing) {
    if (!classSections || classSections.length === 0) {
        $('#sectionSubjectsAssignment').html('<div class="alert alert-warning">No class-sections available. Please assign classes to sections first.</div>');
        return;
    }

    const seenLinks = {};
    classSections = sortClassSections(classSections).filter(function (cs) {
        const key = String(cs.class_id) + '|' + String(cs.section_id);
        if (seenLinks[key]) {
            return false;
        }
        seenLinks[key] = true;
        return true;
    });

    const actSubjects = (subjects || []).filter(function (s) {
        return parseInt(s.status, 10) === 1;
    });

    if (!actSubjects.length) {
        $('#sectionSubjectsAssignment').html('<div class="alert alert-warning">No <strong>active</strong> subjects. Turn subjects on in Step 3 (Active), or add new subjects and return here.</div>');
        return;
    }

    const existingMap = {};
    (existing || []).forEach(function (e) {
        existingMap[String(e.cls_sec_id) + '|' + String(e.subject_id)] = e;
        if (e.class_id != null && e.section_id != null) {
            existingMap['c' + e.class_id + '|s' + e.section_id + '|' + e.subject_id] = e;
        }
    });

    let html = '';
    classSections.forEach(cs => {
        const clsSecId = parseInt(cs.cls_sec_id, 10) || 0;
        const pending = clsSecId <= 0 || !!cs._pending;
        const rowKey = pending ? ('p-' + cs.class_id + '-' + cs.section_id) : String(clsSecId);
        const pendingNote = pending
            ? '<p class="small text-muted mb-2"><i class="fa fa-spinner fa-spin"></i> Saving class–section link…</p>'
            : '';
        html += `<div class="card mb-3">
                    <div class="card-header bg-light">
                        <strong>${escapeHtml(cs.class_name)} - ${escapeHtml(cs.section_name)}</strong>
                    </div>
                    <div class="card-body">
                        ${pendingNote}
                        <div class="row">`;
        actSubjects.forEach(function (subject) {
            let entry = existingMap[String(clsSecId) + '|' + String(subject.sid)] || null;
            if (!entry && cs.class_id != null && cs.section_id != null) {
                entry = existingMap['c' + cs.class_id + '|s' + cs.section_id + '|' + subject.sid] || null;
            }
            const isChecked = !!entry;
            const weekly = Math.max(0, parseInt((entry && entry.classes_per_week) || 0, 10) || 0);
            const short = subject.subject_short_name ? escapeHtml(String(subject.subject_short_name)) : '—';
            const inputId = 'ss-' + rowKey + '-' + subject.sid;
            const disabledAttr = pending ? ' disabled' : '';
            const clsSecAttr = pending ? '' : (' data-cls-sec-id="' + clsSecId + '"');
            const chipClass = 'subject-pick-chip' + (isChecked ? ' is-picked' : '') + (pending ? ' is-disabled' : '');
            html += '<div class="col-sm-6 col-md-4 col-lg-3 mb-2">' +
                '<label class="' + chipClass + '" for="' + inputId + '">' +
                '<input type="checkbox" class="subject-checkbox" id="' + inputId + '"' + clsSecAttr + ' data-row-key="' + rowKey + '" data-subject-id="' + subject.sid + '"' + (isChecked ? ' checked' : '') + disabledAttr + '>' +
                '<span class="subject-pick-chip-body">' +
                '<span class="subject-pick-icon"><i class="fas fa-book-open"></i></span>' +
                '<span class="subject-pick-check"><i class="fas fa-check-circle"></i></span>' +
                '<span class="subject-pick-text">' + escapeHtml(String(subject.subject_name)) + '</span>' +
                '<span class="subject-pick-short">' + short + '</span>' +
                '</span></label>' +
                '<div class="ss-weekly-wrap d-none mt-2 ps-1">' +
                '<label class="small text-muted mb-1">Classes/week</label>' +
                '<input type="number" min="0" class="form-control form-control-sm ss-weekly-classes" data-row-key="' + rowKey + '" data-subject-id="' + subject.sid + '"' + (pending ? '' : (' data-cls-sec-id="' + clsSecId + '"')) + ' value="' + weekly + '"' + ((isChecked && !pending) ? '' : ' disabled') + '>' +
                '</div>' +
                '</div>';
        });
        html += '</div></div></div>';
    });
    $('#sectionSubjectsAssignment').html(html);
    applyTimetableSetupVisibility();
}

function isTimetableSetupEnabled() {
    return $('#enableTimetableSetup').is(':checked');
}

function applyTimetableSetupVisibility() {
    const enabled = isTimetableSetupEnabled();
    $('#timetableSetupHelp').toggleClass('d-none', !enabled);
    $('.ss-weekly-wrap').toggleClass('d-none', !enabled);
    $('.ss-weekly-classes').prop('disabled', function () {
        const rowKey = $(this).data('row-key');
        const clsSecId = $(this).data('cls-sec-id');
        const subjectId = $(this).data('subject-id');
        let checked = false;
        if (rowKey) {
            checked = $('#ss-' + rowKey + '-' + subjectId).is(':checked');
        } else if (clsSecId) {
            checked = $('#ss-' + clsSecId + '-' + subjectId).is(':checked');
        }
        return (!enabled) || (!checked);
    });
}

    $(document).on('change', '.class-section-checkbox', function () {
        const on = $(this).is(':checked');
        $(this).closest('.cs-link-toggle').find('.cs-link-toggle-text').text(on ? 'Linked' : 'Link');
        refreshSubjectAssignmentFromChecks();
        scheduleClassSectionsSync();
    });
    // Step navigation — auto-save current step before moving
    $('.next-step').click(function () {
        if (currentStep < 4) {
            navigateAfterSave(currentStep + 1);
        }
    });

    $('.prev-step').click(function () {
        if (currentStep > 1) {
            navigateAfterSave(currentStep - 1);
        }
    });

    $(document).on('click', '.step-nav', function () {
        const step = parseInt($(this).data('go-step'), 10);
        if (!step || step === currentStep) {
            return;
        }
        navigateAfterSave(step);
    });

    $('#finishSetupBtn').click(function () {
        navigateAfterSave('finish', { allowEmpty: false });
    });

    $(document).on('change', '.subject-checkbox', function () {
        $(this).closest('.subject-pick-chip').toggleClass('is-picked', $(this).is(':checked'));
        const rowKey = $(this).data('row-key');
        const clsSecId = $(this).data('cls-sec-id');
        const subjectId = $(this).data('subject-id');
        let $weekly;
        if (clsSecId) {
            $weekly = $('.ss-weekly-classes[data-cls-sec-id="' + clsSecId + '"][data-subject-id="' + subjectId + '"]');
        } else if (rowKey) {
            $weekly = $('.ss-weekly-classes[data-row-key="' + rowKey + '"][data-subject-id="' + subjectId + '"]');
        } else {
            $weekly = $();
        }
        const on = $(this).is(':checked');
        $weekly.prop('disabled', !on || !isTimetableSetupEnabled() || !clsSecId);
        if (!on) {
            $weekly.val('0');
        }
    });

    $('#enableTimetableSetup').on('change', function () {
        applyTimetableSetupVisibility();
    });

    function updateStepIndicators() {
        for (let i = 1; i <= 4; i++) {
            const $el = $('#step' + i + 'Indicator');
            $el.removeClass('is-active is-completed active completed');
            if (i < currentStep) {
                $el.addClass('is-completed completed');
            } else if (i === currentStep) {
                $el.addClass('is-active active');
            }
        }
        updateWizardCounts();
    }

    $(document).on('click', '.js-row-all', function () {
        const cid = $(this).data('class-id');
        $('.class-section-checkbox[data-class-id="' + cid + '"]').prop('checked', true)
            .closest('.cs-link-toggle').find('.cs-link-toggle-text').text('Linked');
        refreshSubjectAssignmentFromChecks();
        scheduleClassSectionsSync();
    });
    $(document).on('click', '.js-row-none', function () {
        const cid = $(this).data('class-id');
        $('.class-section-checkbox[data-class-id="' + cid + '"]').prop('checked', false)
            .closest('.cs-link-toggle').find('.cs-link-toggle-text').text('Link');
        refreshSubjectAssignmentFromChecks();
        scheduleClassSectionsSync();
    });

});
</script>

<?= $this->endSection() ?>
