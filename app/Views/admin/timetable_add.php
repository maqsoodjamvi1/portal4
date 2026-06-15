<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>
<?php
$slotsBootstrap = [];
if (! empty($slots)) {
    foreach ($slots as $s) {
        $slotsBootstrap[] = [
            'slot_id'    => (int) $s->slot_id,
            'start_time' => (string) $s->start_time,
            'end_time'   => (string) $s->end_time,
        ];
    }
}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.3/dragula.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.3/dragula.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>


<?= view('components/page_header', [
    'title' => 'Create Timetable',
    'icon' => 'fas fa-calendar-plus',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Timetable', 'url' => base_url('admin/timetable')],
        ['label' => 'Create', 'active' => true],
    ],
]) ?>

<!-- Main Content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Create New Timetable</h3>
            </div>

            <div class="card-body">
                <form id="timetableForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="clsSecSelect">Class Section</label>
                                <select class="form-control select2" id="clsSecSelect" name="cls_sec_id" required>
                                    <option value="">-- Select Class Section --</option>
                                    <?php if (!empty($sections)): ?>
                                        <?php foreach ($sections as $section): ?>
                                            <option value="<?= esc($section['cls_sec_id']) ?>">
                                                <?= esc($section['class_name'] . ' - ' . $section['section_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>No class sections available</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mt-4 mt-md-0 pt-md-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="allowSameSubjectPerDay">
                                    <label class="form-check-label" for="allowSameSubjectPerDay">
                                        Allow same subject multiple times in the same day
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    Keep OFF to enforce one subject per day for this class. Teacher conflict checks are always enforced.
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="showFullWeekDays">
                                <label class="form-check-label" for="showFullWeekDays">
                                    Show full week (Mon–Sun) for assignment
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Default columns follow this section’s working days from School Timing (check-in and check-out). Enable this to assign subjects on any weekday; cells stay editable afterward.
                            </small>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Subjects</h3>
                                </div>
                                <div class="card-body p-2" id="subjectPool">
                                    <div class="alert alert-info">
                                        Select a class section to load subjects
                                    </div>
                                </div>

                            </div>
                            <div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title">Teacher Load</h3>
    </div>
    <div class="card-body p-2" id="teacherLoadContainer">
        <div class="alert alert-info">Load will appear after class selection</div>
    </div>
</div>
                        </div>
                        <div class="col-md-9">
                            <div id="timetableContainer">
                                <div class="alert alert-info">
                                    Please select a class section to begin creating the timetable.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mt-3">
                        <button type="button" id="saveTimetable" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Timetable
                        </button>
                        <button type="button" id="clearTimetable" class="btn btn-danger float-end">
                            <i class="fas fa-trash"></i> Clear All
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
window.TTIMETABLE_BOOTSTRAP = {
    fullWeekDays: <?= json_encode($full_week_days ?? []) ?>,
    slots: <?= json_encode($slotsBootstrap) ?>
};

$(document).ready(function () {
    // Initialize Select2
    $('.select2').select2({ theme: 'bootstrap-5' });

    // Global variables
    let currentClsSecId = null;
    let subjects = [];
    let timetableData = {};
    let dragulaInstance = null;
    let selectedSubject = null;
    let allowSameSubjectPerDay = false;
    let blockedSlotsMap = {};
    let constraintsLoading = false;
    let ttMeta = {
        workingDays: [],
        fullWeekDays: window.TTIMETABLE_BOOTSTRAP.fullWeekDays || [],
        dayTiming: {},
        slots: window.TTIMETABLE_BOOTSTRAP.slots || [],
        showFullWeek: false
    };

    $('#allowSameSubjectPerDay').on('change', function () {
        allowSameSubjectPerDay = $(this).is(':checked');
        if (selectedSubject) {
            fetchAndPaintConstraints(selectedSubject);
        }
    });

    $('#showFullWeekDays').on('change', function () {
        ttMeta.showFullWeek = $(this).is(':checked');
        if (currentClsSecId && subjects.length) {
            renderTimetableGrid(subjects, timetableData);
            initializeDragAndDrop();
            if (selectedSubject) {
                fetchAndPaintConstraints(selectedSubject);
            }
        }
    });

    // On class section change
    $('#clsSecSelect').change(function () {
        currentClsSecId = $(this).val();
        
        if (!currentClsSecId) {
            $('#subjectPool').html('<div class="alert alert-info">Select a class section to load subjects</div>');
            $('#timetableContainer').html('<div class="alert alert-info">Please select a class section.</div>');
            return;
        }

        loadTimetableData(currentClsSecId);
    });

    // Load timetable data (subjects and existing timetable)
    function loadTimetableData(clsSecId) {
        showLoading('#subjectPool');
        showLoading('#timetableContainer');

        $.ajax({
            url: "<?= base_url('admin/timetable/get-subjects-timetable') ?>",
            method: "POST",
            data: { cls_sec_id: clsSecId },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    subjects = response.subjects;
                    timetableData = response.timetable || {};
                    ttMeta.workingDays = response.working_days || ttMeta.fullWeekDays;
                    ttMeta.fullWeekDays = response.full_week_days || ttMeta.fullWeekDays;
                    ttMeta.dayTiming = response.day_timing || {};
                    ttMeta.slots = (response.slots && response.slots.length) ? response.slots : (window.TTIMETABLE_BOOTSTRAP.slots || []);

                    renderSubjectPool(response.subjects);
                    renderTimetableGrid(response.subjects, response.timetable);
                    renderTeacherLoad(response.teacherLoad);
                    initializeDragAndDrop();
                } else {
                    showError(response.msg || "Unable to load timetable data.");
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
                showError("Server error while loading timetable.");
            }
        });
    }

    function renderTeacherLoad(teacherLoad) {
    if (!teacherLoad || teacherLoad.length === 0) {
        $('#teacherLoadContainer').html('<div class="alert alert-warning">No teacher data</div>');
        return;
    }

    let html = `<ul class="list-group">`;
    teacherLoad.forEach(t => {
        html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                    ${t.name}
                    <span class="badge text-bg-primary rounded-pill">${t.count}</span>
                </li>`;
    });
    html += `</ul>`;

    $('#teacherLoadContainer').html(html);
}



    // Render subject pool with selection capability
    function renderSubjectPool(subjects) {
        let html = '<div class="d-flex flex-wrap">';
        
        subjects.forEach(subject => {
            const teacherName = subject.first_name ? `${subject.first_name} ${subject.last_name}` : 'No teacher';
            html += `
                <div class="subject-card bg-gradient-info m-1 p-2 rounded" 
                     data-subject-id="${subject.subject_id}"
                     draggable="true">
                    <small>${subject.subject_name}</small>
                    <div class="text-xs">${teacherName}</div>
                </div>
            `;
        });
        
        html += '</div>';
        $('#subjectPool').html(html);

        // Add click handlers for subject selection
        $('.subject-card').on('click', function() {
            // Remove selection from all subjects
            $('.subject-card').removeClass('selected-subject');
            
            // Add selection to clicked subject
            $(this).addClass('selected-subject');
            selectedSubject = $(this).data('subject-id');
            fetchAndPaintConstraints(selectedSubject);
        });
    }

    function getDisplayDays() {
        if (ttMeta.showFullWeek && ttMeta.fullWeekDays.length) {
            return ttMeta.fullWeekDays;
        }
        return ttMeta.workingDays.length ? ttMeta.workingDays : ttMeta.fullWeekDays;
    }

    // Render timetable grid (columns = working days or full week)
    function renderTimetableGrid(subjects, timetable) {
        const days = getDisplayDays();
        const slots = ttMeta.slots || [];

        if (!days.length) {
            $('#timetableContainer').html('<div class="alert alert-warning">No days to display. Configure School Timing for this section or enable full week.</div>');
            return;
        }
        if (!slots.length) {
            $('#timetableContainer').html('<div class="alert alert-warning">No period slots found for this campus.</div>');
            return;
        }

        let html = '<div class="table-responsive"><table class="table table-bordered table-sm tt-slot-grid"><thead><tr><th class="tt-slot-col text-center" style="width:1%">Slot</th>';
        days.forEach(function (day) {
            html += '<th class="text-center">' + day + '</th>';
        });
        html += '</tr></thead><tbody>';

        slots.forEach(function (slot, slotIndex) {
            const slotId = String(slot.slot_id);
            const slotLabel = 'Slot ' + (slotIndex + 1);
            html += '<tr><td class="align-middle tt-slot-col"><div class="d-flex align-items-center justify-content-between flex-nowrap gap-1">';
            html += '<span class="text-nowrap small fw-bold">' + slotLabel + '</span>';
            html += '<button type="button" class="btn btn-sm btn-outline-secondary tt-fill-row py-0 px-1" data-slot-id="' + slotId + '" title="Put selected subject in this slot on every visible day"><i class="fas fa-arrows-alt-h"></i></button>';
            html += '</div></td>';

            days.forEach(function (day) {
                const cellId = 'cell-' + day + '-' + slotId;
                const rowForDay = timetable[day] || {};
                const cellEntry = rowForDay[slotId] || rowForDay[parseInt(slotId, 10)];
                const currentSubject = cellEntry ? cellEntry.subject_id : null;
                const subject = currentSubject
                    ? subjects.find(s => String(s.subject_id) === String(currentSubject))
                    : null;

                html += '<td id="' + cellId + '" class="timetable-cell" data-day="' + day + '" data-slot-id="' + slotId + '" onclick="handleCellClick(this)">';
                if (subject) {
                    html += '<div class="subject-card bg-gradient-info m-1 p-2 rounded" data-subject-id="' + subject.subject_id + '" draggable="true">';
                    html += '<small>' + subject.subject_name + '</small>';
                    html += '<div class="text-xs">' + (subject.first_name ? (subject.first_name + ' ' + subject.last_name) : '') + '</div>';
                    html += '<button class="btn btn-sm btn-danger remove-subject" style="position: absolute; top: 0; right: 0;"><i class="fas fa-times"></i></button>';
                    html += '</div>';
                }
                html += '</td>';
            });
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        $('#timetableContainer').html(html);
    }

    $('#timetableContainer').on('click', '.tt-fill-row', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const slotId = $(this).data('slot-id');
        if (!currentClsSecId) {
            toastr.error('Please select a class section first');
            return;
        }
        if (!selectedSubject) {
            toastr.info('Select a subject on the left, then click the small week button on that slot row.');
            return;
        }
        const days = getDisplayDays();
        if (!days.length) {
            return;
        }

        $.ajax({
            url: "<?= base_url('admin/timetable/bulk-update-slot-row') ?>",
            method: 'POST',
            dataType: 'json',
            data: {
                cls_sec_id: currentClsSecId,
                slot_id: slotId,
                subject_id: selectedSubject,
                days: JSON.stringify(days),
                allow_same_subject_day: allowSameSubjectPerDay ? 1 : 0
            },
            success: function (response) {
                if (response.success) {
                    const sub = subjects.find(s => String(s.subject_id) === String(selectedSubject));
                    if (sub) {
                        days.forEach(function (day) {
                            if (!timetableData[day]) {
                                timetableData[day] = {};
                            }
                            timetableData[day][String(slotId)] = {
                                subject_id: sub.subject_id,
                                subject_name: sub.subject_name
                            };
                        });
                    }
                    toastr.success(response.msg || 'Row updated');
                    renderTimetableGrid(subjects, timetableData);
                    initializeDragAndDrop();
                    if (response.teacherLoad) {
                        updateTeacherLoadUI(response.teacherLoad);
                    }
                    if (selectedSubject) {
                        fetchAndPaintConstraints(selectedSubject);
                    }
                } else {
                    toastr.error(response.msg || 'Could not fill row');
                    if (currentClsSecId) {
                        loadTimetableData(currentClsSecId);
                    }
                }
            },
            error: function () {
                toastr.error('Server error while updating row');
            }
        });
    });

    // Initialize drag and drop functionality
    function initializeDragAndDrop() {
        // Destroy previous instance if exists
        if (dragulaInstance) {
            dragulaInstance.destroy();
        }

        // Initialize dragula
        dragulaInstance = dragula({
            isContainer: function (el) {
                return el.classList.contains('timetable-cell') || el.id === 'subjectPool';
            },
            moves: function (el, source, handle, sibling) {
                // Only allow dragging if it's a subject card
                return el.classList.contains('subject-card');
            },
            accepts: function (el, target, source, sibling) {
                // Only allow dropping into timetable cells (not other subject cards)
                if (target.id === 'subjectPool') return true;
                if (!target.classList.contains('timetable-cell')) return false;

                // While constraints are loading, don't allow drop into table.
                if (constraintsLoading) return false;

                const day = target.getAttribute('data-day');
                const slotId = target.getAttribute('data-slot-id');
                if (isBlockedCell(day, slotId)) return false;
                return true;
            }
        });

        // Handle drop events
        dragulaInstance.on('drop', function (el, target, source, sibling) {
            if (target.classList.contains('timetable-cell')) {
                const subjectId = el.getAttribute('data-subject-id');
                const day = target.getAttribute('data-day');
                const slotId = target.getAttribute('data-slot-id');
                
                // Check for conflicts (same subject in same day)
                const conflict = checkForConflicts(subjectId, day, slotId);
                
                if (conflict) {
                    // Only local same-day duplicate check (when toggle is OFF)
                    toastr.warning('This subject is already scheduled at another time today.');
                    dragulaInstance.cancel(true); // Revert the drag
                    return;
                }
                if (isBlockedCell(day, slotId)) {
                    // Blocked slots are now visually restricted and non-droppable.
                    dragulaInstance.cancel(true);
                    return;
                }
                
                // Add remove button if coming from subject pool
                if (source.id === 'subjectPool') {
                    const removeBtn = document.createElement('button');
                    removeBtn.className = 'btn btn-sm btn-danger remove-subject';
                    removeBtn.style = 'position: absolute; top: 0; right: 0;';
                    removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                    removeBtn.onclick = function(e) {
                        e.stopPropagation();
                        el.remove();
                        updateTimetableSlot(day, slotId, null);
                    };
                    el.appendChild(removeBtn);
                }
                
                // Clear the target cell first (only one subject per cell)
                target.innerHTML = '';
                target.appendChild(el);
                
                // Update the backend
                updateTimetableSlot(day, slotId, subjectId);
            }
        });
    }

    // Handle cell clicks to place selected subject
    window.handleCellClick = function(cell) {
        if (!selectedSubject) {
            toastr.info('Please select a subject first');
            return;
        }
        if (constraintsLoading) {
            return;
        }

        const day = cell.getAttribute('data-day');
        const slotId = cell.getAttribute('data-slot-id');
        const subjectId = selectedSubject;
        
        // Check for conflicts
        const conflict = checkForConflicts(subjectId, day, slotId);
        if (conflict) {
            toastr.warning('This subject is already scheduled at another time today');
            return;
        }
        if (isBlockedCell(day, slotId)) {
            // Keep blocked slots silent; they are already visually marked as restricted.
            return;
        }

        // Find the subject data
        const subject = subjects.find(s => s.subject_id == subjectId);
        if (!subject) return;

        // Create the subject card
        const subjectCard = document.createElement('div');
        subjectCard.className = 'subject-card bg-gradient-info m-1 p-2 rounded';
        subjectCard.setAttribute('data-subject-id', subjectId);
        subjectCard.setAttribute('draggable', 'true');
        subjectCard.innerHTML = `
            <small>${subject.subject_name}</small>
            <div class="text-xs">${subject.first_name ? `${subject.first_name} ${subject.last_name}` : ''}</div>
            <button class="btn btn-sm btn-danger remove-subject" style="position: absolute; top: 0; right: 0;">
                <i class="fas fa-times"></i>
            </button>
        `;

        // Add remove button handler
        subjectCard.querySelector('.remove-subject').addEventListener('click', function(e) {
            e.stopPropagation();
            subjectCard.remove();
            updateTimetableSlot(day, slotId, null);
        });

        // Update the cell
        cell.innerHTML = '';
        cell.appendChild(subjectCard);

        // Update the backend
        updateTimetableSlot(day, slotId, subjectId);
    };

    // Check for scheduling conflicts
    function checkForConflicts(subjectId, day, slotId) {
        if (allowSameSubjectPerDay) {
            return false;
        }
        // Check if this subject is already scheduled on this day
        for (const [existingSlotId, data] of Object.entries(timetableData[day] || {})) {
            if (data.subject_id == subjectId && existingSlotId != slotId) {
                return true;
            }
        }
        return false;
    }

    // Update timetable slot in backend
    function updateTimetableSlot(day, slotId, subjectId) {
        $.ajax({
            url: "<?= base_url('admin/timetable/update-slot') ?>",
            method: "POST",
            dataType: "json",
            data: {
                cls_sec_id: currentClsSecId,
                day: day,
                slot_id: slotId,
                subject_id: subjectId,
                allow_same_subject_day: allowSameSubjectPerDay ? 1 : 0
            },
            success: function (response) {

                if (response.success) {
                    // Update our local timetable data

                    if (!timetableData[day]) timetableData[day] = {};
                    
                    if (subjectId) {
                        timetableData[day][slotId] = {
                            subject_id: subjectId,
                            subject_name: subjects.find(s => s.subject_id == subjectId).subject_name
                        };
                    } else {
                        delete timetableData[day][slotId];
                        if (Object.keys(timetableData[day]).length === 0) {
                            delete timetableData[day];
                        }
                    }
                    
                    toastr.success(response.msg || "Timetable updated");
                    if (response.teacherLoad) {
            updateTeacherLoadUI(response.teacherLoad);
        }
                    if (selectedSubject) {
                        fetchAndPaintConstraints(selectedSubject);
                    }
                } else {
                    toastr.error(response.msg || "Failed to update timetable");
                    // Re-sync from server to avoid UI/DB mismatch after conflict rejection
                    if (currentClsSecId) {
                        loadTimetableData(currentClsSecId);
                    }
                }
            },
            error: function () {
                toastr.error("Unexpected error occurred");
            }
        });
    }


    function updateTeacherLoadUI(teacherLoad) {
    if (!teacherLoad || teacherLoad.length === 0) {
        $('#teacherLoadContainer').html('<div class="alert alert-warning">No teacher data</div>');
        return;
    }

    let html = `<ul class="list-group">`;
    teacherLoad.forEach(t => {
        html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                    ${t.name}
                    <span class="badge text-bg-primary rounded-pill">${t.count}</span>
                </li>`;
    });
    html += `</ul>`;

    $('#teacherLoadContainer').html(html);
}


    // Save entire timetable
    $('#saveTimetable').click(function() {
        if (!currentClsSecId) {
            toastr.error("Please select a class section first");
            return;
        }

        $.ajax({
            url: "<?= base_url('admin/timetable/save') ?>",
            method: "POST",
            dataType: "json",
            data: {
                cls_sec_id: currentClsSecId,
                timetable: JSON.stringify(timetableData),
                allow_same_subject_day: allowSameSubjectPerDay ? 1 : 0
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.msg || "Timetable saved successfully");
                } else {
                    toastr.error(response.msg || "Failed to save timetable");
                }
            },
            error: function() {
                toastr.error("Server error while saving timetable");
            }
        });
    });

    // Clear entire timetable
    $('#clearTimetable').click(function() {
        if (!currentClsSecId) {
            toastr.error("Please select a class section first");
            return;
        }

        if (!confirm("Are you sure you want to clear the entire timetable?")) {
            return;
        }

        $.ajax({
            url: "<?= base_url('admin/timetable/clear') ?>",
            method: "POST",
            dataType: "json",
            data: { cls_sec_id: currentClsSecId },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.msg || "Timetable cleared");
                    timetableData = {};
                    renderTimetableGrid(subjects, {});
                } else {
                    toastr.error(response.msg || "Failed to clear timetable");
                }
            },
            error: function() {
                toastr.error("Server error while clearing timetable");
            }
        });
    });

    // Helper functions
    function showLoading(selector) {
        $(selector).html(`
            <div class="text-center p-4">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Loading...</p>
            </div>
        `);
    }

    function showError(message) {
        console.error("Error:", message);
        $('#timetableContainer').html(`<div class="alert alert-danger">${message}</div>`);
    }

    function makeSlotKey(day, slotId) {
        return `${day}|${slotId}`;
    }

    function isBlockedCell(day, slotId) {
        return !!blockedSlotsMap[makeSlotKey(day, slotId)];
    }

    function getBlockedReason(day, slotId) {
        const item = blockedSlotsMap[makeSlotKey(day, slotId)];
        return item ? item.reason : '';
    }

    function clearConstraintPaint() {
        $('.timetable-cell').removeClass('slot-blocked slot-allowed').removeAttr('title');
    }

    function paintConstraints() {
        clearConstraintPaint();
        if (!selectedSubject) return;

        $('.timetable-cell').each(function () {
            const day = $(this).data('day');
            const slotId = $(this).data('slot-id');
            if (isBlockedCell(day, slotId)) {
                $(this).addClass('slot-blocked').attr('title', getBlockedReason(day, slotId));
            } else {
                $(this).addClass('slot-allowed').attr('title', 'Recommended slot');
            }
        });
    }

    function fetchAndPaintConstraints(subjectId) {
        if (!currentClsSecId || !subjectId) {
            blockedSlotsMap = {};
            clearConstraintPaint();
            return;
        }

        $.ajax({
            url: "<?= base_url('admin/timetable/get-subject-constraints') ?>",
            method: "POST",
            dataType: "json",
            data: {
                cls_sec_id: currentClsSecId,
                subject_id: subjectId,
                allow_same_subject_day: allowSameSubjectPerDay ? 1 : 0
            },
            beforeSend: function () {
                constraintsLoading = true;
            },
            success: function (response) {
                blockedSlotsMap = {};
                if (response && response.success && Array.isArray(response.blocked)) {
                    response.blocked.forEach(function (b) {
                        blockedSlotsMap[makeSlotKey(b.day, b.slot_id)] = b;
                    });
                }
                paintConstraints();
            },
            error: function () {
                blockedSlotsMap = {};
                clearConstraintPaint();
            },
            complete: function () {
                constraintsLoading = false;
            }
        });
    }
});
</script>

<style>
.subject-card {
    position: relative;
    cursor: pointer;
    min-width: 80px;
    color: white;
    text-align: center;
    transition: all 0.2s;
}

.subject-card.selected-subject {
    transform: scale(1.05);
    box-shadow: 0 0 10px rgba(0,0,0,0.5);
    z-index: 10;
}

.timetable-cell {
    min-width: 120px;
    min-height: 60px;
    position: relative;
    cursor: pointer;
}

.timetable-cell:hover {
    background-color: rgba(0,0,0,0.05);
}

.timetable-cell.slot-blocked {
    background: rgba(220, 53, 69, 0.16) !important;
    border: 2px dashed rgba(220, 53, 69, 0.8) !important;
    cursor: not-allowed !important;
}

.timetable-cell.slot-allowed {
    background: rgba(40, 167, 69, 0.08) !important;
}

.gu-mirror {
    opacity: 0.8;
    cursor: grabbing;
}

.gu-transit {
    opacity: 0.2;
}

.remove-subject {
    padding: 0 0.25rem;
    font-size: 0.6rem;
    line-height: 1.2;
}

.tt-slot-grid .tt-slot-col {
    width: 1%;
    white-space: nowrap;
    vertical-align: middle;
}

.tt-slot-grid .tt-fill-row {
    font-size: 0.7rem;
    line-height: 1;
    min-width: auto;
}
</style>

<?= $this->endSection() ?>