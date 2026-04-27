<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/toastr/toastr.min.css') ?>">
<script src="<?= base_url('assets/plugins/toastr/toastr.min.js') ?>"></script>
<!-- Page Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Add Timetable</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/timetable') ?>">Timetable</a></li>
                    <li class="breadcrumb-item active">Add</li>
                </ol>
            </div>
        </div>
    </div>
</div>

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
                    </div>
                    
                    <div id="timetableContainer" class="mt-4">
                        <div class="alert alert-info">
                            Please select a class section to begin creating the timetable.
                        </div>
                    </div>
                    
                    <div class="form-group mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Timetable
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>


<script>
$(document).ready(function () {
    // Initialize Select2 on dropdowns
    $('.select2').select2({ theme: 'bootstrap4' });

    console.log("Document ready - timetable add page loaded");

    // On class section change
    $('#clsSecSelect').change(function () {
        const clsSecId = $(this).val();
        console.log("Class section changed to:", clsSecId);

        if (!clsSecId) {
            $('#timetableContainer').html('<div class="alert alert-info">Please select a class section.</div>');
            return;
        }

        loadTimetableForm(clsSecId);
    });

    function loadTimetableForm(clsSecId) {
        console.log("Loading timetable form for:", clsSecId);

        $('#timetableContainer').html(`
            <div class="text-center p-4">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Loading timetable...</p>
            </div>
        `);

        $.ajax({
            url: "<?= base_url('admin/timetable/get-subjects-timetable') ?>",
            method: "POST",
            data: { cls_sec_id: clsSecId },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    renderTimetableForm(response.subjects, response.timetable);
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

  function renderTimetableForm(subjects, timetable) {
    console.log("Rendering timetable with subjects:", subjects);

    let html = `<div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Time/Day</th>`;

    <?php foreach ($days as $day): ?>
        html += `<th><?= esc($day) ?></th>`;
    <?php endforeach; ?>
    
    html += `</tr></thead><tbody>`;

    <?php foreach ($slots as $slot): ?>
        <?php
        $start = new \DateTime($slot->start_time);
        $end = new \DateTime($slot->end_time);
        $timeStr = $start->format('h:i A') . ' - ' . $end->format('h:i A');
        $slotId = $slot->slot_id;
        ?>
        html += `<tr><td><?= esc($timeStr) ?></td>`;

        <?php foreach ($days as $day): ?>
            (function(day, slotId) {
                html += `<td><select class="form-control subject-select" 
                    name="timetable[${day}][${slotId}]"
                    data-day="${day}" 
                    data-slot-id="${slotId}">
                    <option value="">-- Free --</option>`;

                const current = (timetable[day] && timetable[day][slotId]) 
                    ? timetable[day][slotId].subject_id 
                    : '';

                subjects.forEach(subject => {
                    const selected = subject.subject_id == current ? 'selected' : '';
                    html += `<option value="${subject.subject_id}" ${selected}>${subject.subject_name} ${subject.first_name ? `(${subject.first_name} ${subject.last_name})` : ''}</option>`;
                });

                html += `</select></td>`;
            })("<?= esc($day) ?>", "<?= esc($slotId) ?>");
        <?php endforeach; ?>

        html += `</tr>`;
    <?php endforeach; ?>

    html += `</tbody></table></div>`;

    $('#timetableContainer').html(html);

    // Initialize Select2 again
    $('.subject-select').select2({ theme: 'bootstrap4', width: '100%' });



        // On change of any subject dropdown
        $(document).off('change', '.subject-select').on('change', '.subject-select', function () {
            const subjectId = $(this).val();
            const day = $(this).data('day');
            const slotId = $(this).data('slot-id');
            const clsSecId = $('#clsSecSelect').val();
            const cell = $(this).closest('td');

            console.log("Subject changed:", { day, slotId, subjectId });

            if (subjectId) {
                $.ajax({
                    url: "<?= base_url('admin/timetable/save-slot') ?>",
                    method: "POST",
                    dataType: "json",
                    data: {
                        cls_sec_id: clsSecId,
                        day: day,
                        slot_id: slotId,
                        subject_id: subjectId
                    },
                    success: function (response) {
                        if (response.success) {
                            toastr.success(response.msg || "Saved successfully");
                            cell.removeClass("table-danger").addClass("table-success");
                        } else {
                            toastr.error(response.msg || "Failed to save");
                            cell.removeClass("table-success").addClass("table-danger");
                        }
                    },
                    error: function () {
                        toastr.error("Unexpected error occurred");
                        cell.removeClass("table-success").addClass("table-danger");
                    }
                });
            } else {
                $.ajax({
                    url: "<?= base_url('admin/timetable/clear-slot') ?>",
                    method: "POST",
                    data: {
                        cls_sec_id: clsSecId,
                        day: day,
                        slot_id: slotId
                    },
                    success: function () {
                        toastr.info("Slot cleared");
                        cell.removeClass("table-danger table-success");
                    }
                });
            }
        });
    }

    function showError(message) {
        console.error("Error:", message);
        $('#timetableContainer').html(`<div class="alert alert-danger">${message}</div>`);
    }
});
</script>

<?= $this->endSection() ?>
