<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  $header = 'Manage Students';
  $id = '';
  $cls_sec_id = $_GET['cls_sec_id'] ?? '';
?>


<?= view('components/bulk_students_header', [
  'title' => 'Student Names',
  'subtitle' => 'Student Names'
]) ?>

<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline shadow-sm">
      <div class="card-header pb-0">
         <?= view('components/bulk_students_tabs', ['active' => 'names']) ?>
      </div>

      <div class="card-body">
        <?= form_open_multipart(base_url('admin/addbulkstudents/save'), ['id' => 'user-edit-form']) ?>
        <?= form_hidden('id', $id) ?>
        <?= form_hidden('current_session', $current_session ?? '') ?>

        <div class="row">
          <div class="col-lg-3">
            <div class="form-group">
              <label for="cls_sec_id">Section <span class="text-danger">*</span></label>
              <select class="form-control" name="cls_sec_id" id="cls_sec_id" required>
                <option value="0" <?= empty($cls_sec_id) ? 'selected' : '' ?>>Select Section</option>
                <?php if (!empty($sectionsclassinfo)) : ?>
                  <?php foreach ($sectionsclassinfo as $section) :
                    $s_id = is_array($section) ? ($section['cls_sec_id'] ?? null) : ($section->cls_sec_id ?? null);
                    $label = is_array($section) ? ($section['sectionclassname'] ?? trim(($section['class_name'] ?? '') . ' (' . ($section['section_name'] ?? '') . ')')) : ($section->sectionclassname ?? trim(($section->class_name ?? '') . ' (' . ($section->section_name ?? '') . ')'));
                    if (!$s_id || !$label) continue;
                  ?>
                    <option value="<?= esc($s_id) ?>" <?= ((int)$cls_sec_id === (int)$s_id) ? 'selected' : '' ?>>
                      <?= esc($label) ?>
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
          </div>
          
          <!-- Add Edit Mode Toggle -->
          <div class="col-lg-2">
            <div class="form-group">
              <label>&nbsp;</label>
              <div>
                <div class="form-check form-switch">
                  <input type="checkbox" class="form-check-input" id="editModeToggle">
                  <label class="form-check-label" for="editModeToggle">Edit Mode</label>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Add Edit Student Search (shown in edit mode) -->
          <div class="col-lg-4" id="editStudentSearchDiv" style="display:none;">
            <div class="form-group">
              <label>Search Student to Edit</label>
              <select id="edit_student_search" class="form-control select2" style="width:100%">
                <option value="">Type student name...</option>
              </select>
            </div>
          </div>
        </div>

        <div id="studentsTableWrap" class="my-3"></div>

        <div class="row mt-4">
          <div class="col-lg-3">
            <button type="submit" class="btn btn-primary" id="submitBtn">Save</button>
            <button type="reset" class="btn btn-secondary">Reset</button>
            <button type="button" class="btn btn-light" onclick="history.go(-1);">Cancel</button>
          </div>
        </div>
        <?= form_close() ?>
      </div>
    </div>
  </div>
</section>

<!-- Individual Drop Modal with SLC Options -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title" id="deleteModalLabel">Drop Student: <span id="studentNameDisplay"></span></h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div class="form-check form-check">
            <input type="radio" id="dropOnly" name="dropOption" value="drop_only" class="form-check-input" checked>
            <label class="form-check-label" for="dropOnly">Drop Only (Mark as Dropped)</label>
          </div>
          <div class="form-check form-check mt-2">
            <input type="radio" id="dropWithSLC" name="dropOption" value="drop_with_slc" class="form-check-input">
            <label class="form-check-label" for="dropWithSLC">Drop & Generate School Leaving Certificate</label>
          </div>
        </div>
        
        <div id="slcFields" class="mt-4 p-3 border rounded bg-light d-none">
          <h6 class="text-primary">School Leaving Certificate Information</h6>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="leaving_date">Date of Leaving <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="leaving_date" value="<?= date('Y-m-d') ?>">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="leaving_reason">Reason for Leaving <span class="text-danger">*</span></label>
                <select class="form-control" id="leaving_reason">
                  <option value="">Select Reason</option>
                  <option value="Family relocated">Family relocated</option>
                  <option value="School transfer">School transfer</option>
                  <option value="Financial reasons">Financial reasons</option>
                  <option value="Health issues">Health issues</option>
                  <option value="Completed education">Completed education</option>
                  <option value="Other">Other</option>
                </select>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label for="leaving_reason_other">Other Reason (Specify)</label>
                <input type="text" class="form-control" id="leaving_reason_other" placeholder="Please specify reason">
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDelete">Drop Student</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Student Name Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Edit Student Name</h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_student_id">
                <div class="form-group">
                    <label>First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="edit_first_name" placeholder="First Name">
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" class="form-control" id="edit_last_name" placeholder="Last Name">
                </div>
                <div class="form-group">
                    <label>Registration Number</label>
                    <input type="text" class="form-control" id="edit_reg_no" readonly>
                </div>
                <div class="form-group">
                    <label>Current Class</label>
                    <input type="text" class="form-control" id="edit_class" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveNameChanges">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- SLC Update Modal -->
<div class="modal fade" id="slcUpdateModal" tabindex="-1" role="dialog" aria-labelledby="slcUpdateModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h5 class="modal-title" id="slcUpdateModalLabel">Update Student Information for SLC</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="slcUpdateForm">
          <input type="hidden" id="slc_student_id">
          
          <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Registration No.</label>
                    <input type="text" class="form-control" id="slc_reg_no" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Father's Contact</label>
                    <input type="text" class="form-control" id="slc_father_contact">
                </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Mother's Contact</label>
                    <input type="text" class="form-control" id="slc_mother_contact">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Emergency Contact</label>
                    <input type="text" class="form-control" id="slc_emergency_contact">
                </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="slc_full_name" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Father's Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="slc_father_name" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Mother's Name</label>
                <input type="text" class="form-control" id="slc_mother_name">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Date of Birth <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="slc_dob" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Religion</label>
                <input type="text" class="form-control" id="slc_religion">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Nationality</label>
                <input type="text" class="form-control" id="slc_nationality" value="Pakistani">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Date of Admission</label>
                <input type="date" class="form-control" id="slc_admission_date">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Class at time of leaving</label>
                <input type="text" class="form-control" id="slc_class" readonly>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveAndGenerateSLC">Save & Generate SLC</button>
      </div>
    </div>
  </div>
</div>

<div id="loader" class="text-center d-none">
  <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
$(function () {
    const $loader = $('#loader');
    const $section = $('#cls_sec_id');
    const $target = $('#studentsTableWrap');
    const $form = $('#user-edit-form');
    const CSRF_NAME = '<?= csrf_token() ?>';
    const CSRF_HASH = '<?= csrf_hash() ?>';
    let currentStudentId = null;
    let currentStudentName = null;
    let isEditMode = false;

    // Initialize Select2 for edit student search
    $('#edit_student_search').select2({
        placeholder: 'Search student by name...',
        minimumInputLength: 2,
        ajax: {
            url: '<?= base_url("admin/students_bulk_info/search-by-name") ?>',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term,
                    limit: 20,
                    cls_sec_id: $section.val() || 0
                };
            },
            processResults: function(data) {
                return {
                    results: data.results || []
                };
            }
        }
    });

    // Toggle edit mode
    $('#editModeToggle').on('change', function() {
        isEditMode = $(this).is(':checked');
        if (isEditMode) {
            $('#editStudentSearchDiv').show();
            $('#submitBtn').text('Update Selected Student');
            $target.html('<div class="alert alert-info">Search for a student above to edit their name.</div>');
        } else {
            $('#editStudentSearchDiv').hide();
            $('#submitBtn').text('Save');
            if ($section.val() && $section.val() !== '0') {
                loadStudents($section.val());
            }
        }
    });

    // When student is selected for editing
    $('#edit_student_search').on('select2:select', function(e) {
        var studentId = e.params.data.id;
        var studentName = e.params.data.text;
        
        $loader.removeClass('d-none');
        
        $.ajax({
            url: "<?= base_url('admin/addbulkstudents/get-student-details') ?>",
            type: "POST",
            data: {
                student_id: studentId,
                [CSRF_NAME]: CSRF_HASH
            },
            dataType: 'json',
            success: function(res) {
                if (res.success && res.student) {
                    const student = res.student;
                    $('#edit_student_id').val(studentId);
                    $('#edit_first_name').val(student.first_name || '');
                    $('#edit_last_name').val(student.last_name || '');
                    $('#edit_reg_no').val(student.reg_no || 'Auto-generated');
                    $('#edit_class').val(student.current_class || student.class_name || 'N/A');
                    
                    $('#editStudentModal').modal('show');
                } else {
                    toastr.error('Failed to load student details');
                }
            },
            error: function() {
                toastr.error('Error loading student details');
            },
            complete: function() {
                $loader.addClass('d-none');
            }
        });
    });

    // Save name changes
    $('#saveNameChanges').on('click', function() {
        const studentId = $('#edit_student_id').val();
        const firstName = $('#edit_first_name').val().trim();
        const lastName = $('#edit_last_name').val().trim();
        
        if (!firstName) {
            toastr.error('First name is required');
            return;
        }
        
        $loader.removeClass('d-none');
        
        $.ajax({
            url: "<?= base_url('admin/addbulkstudents/update-student-name') ?>",
            type: "POST",
            data: {
                student_id: studentId,
                first_name: firstName,
                last_name: lastName,
                [CSRF_NAME]: CSRF_HASH
            },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    toastr.success('Student name updated successfully');
                    $('#editStudentModal').modal('hide');
                    $('#edit_student_search').val(null).trigger('change');
                    
                    // Reload the table if in add mode
                    if (!isEditMode && $section.val() && $section.val() !== '0') {
                        loadStudents($section.val());
                    }
                } else {
                    toastr.error(res.msg || 'Failed to update student name');
                }
            },
            error: function() {
                toastr.error('Error updating student name');
            },
            complete: function() {
                $loader.addClass('d-none');
            }
        });
    });

    function initializeEventHandlers() {
        // Add Row
        $('#addNewStudentRow').off('click').on('click', function () {
            const nextIndex = $('#newStudentsBody tr').length + 1;
            const newRow = `
                <tr>
                    <td class="text-center align-middle">${nextIndex}</td>
                    <td>
                        <input type="hidden" name="student_id[]" value="0">
                        <input type="text" class="form-control form-control-sm full-name-input" name="full_name[]" placeholder="Enter name" required>
                    </td>
                    <td class="align-middle text-muted small">
                        Auto-generated
                        <input type="hidden" name="reg_no[]" value="">
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-sm btn-link text-danger btn-remove-new-row"><i class="fas fa-times"></i></button>
                        <button type="button" class="btn btn-sm btn-link text-primary btn-edit-student" 
                                data-student-id="0" data-student-name="" style="display:none;">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>`;
            $('#newStudentsBody').append(newRow);
            $('#newStudentsBody tr:last .full-name-input').focus();
        });

        // Remove Row
        $(document).off('click', '.btn-remove-new-row').on('click', '.btn-remove-new-row', function() {
            const $tbody = $('#newStudentsBody');
            if ($tbody.find('tr').length > 1) {
                $(this).closest('tr').remove();
                $tbody.find('tr').each(function(i) { $(this).find('td:first').text(i + 1); });
            } else {
                $tbody.find('input[type="text"]').val('');
            }
        });

        // Edit existing student button
        $(document).off('click', '.btn-edit-student').on('click', '.btn-edit-student', function() {
            const studentId = $(this).data('student-id');
            const studentName = $(this).data('student-name');
            
            if (studentId && studentId !== '0') {
                loadStudentInfoForEdit(studentId);
            }
        });

        // Drop Button
        $(document).off('click', '.btn-drop-student').on('click', '.btn-drop-student', function() {
            currentStudentId = $(this).data('student-id');
            currentStudentName = $(this).data('student-name');
            
            $('#studentNameDisplay').text(currentStudentName);
            $('#slcFields').addClass('d-none');
            $('#dropOnly').prop('checked', true);
            
            $('#deleteModal').modal('show');
        });

        $('.full-name-input:first').focus();
    }

    // Load student info for edit
    function loadStudentInfoForEdit(studentId) {
        $loader.removeClass('d-none');
        $.ajax({
            url: "<?= base_url('admin/addbulkstudents/get-student-details') ?>",
            type: "POST",
            data: {
                student_id: studentId,
                [CSRF_NAME]: CSRF_HASH
            },
            dataType: 'json',
            success: function(res) {
                if (res.success && res.student) {
                    const student = res.student;
                    $('#edit_student_id').val(studentId);
                    $('#edit_first_name').val(student.first_name || '');
                    $('#edit_last_name').val(student.last_name || '');
                    $('#edit_reg_no').val(student.reg_no || 'Auto-generated');
                    $('#edit_class').val(student.current_class || student.class_name || 'N/A');
                    
                    $('#editStudentModal').modal('show');
                } else {
                    toastr.error('Failed to load student details');
                }
            },
            error: function() {
                toastr.error('Error loading student details');
            },
            complete: function() {
                $loader.addClass('d-none');
            }
        });
    }

    function loadStudents(clsSecId) {
        if (!clsSecId || clsSecId === '0') { 
            $target.html('');
            return; 
        }
        $loader.removeClass('d-none');
        $.ajax({
            url: "<?= base_url('admin/addbulkstudents/select-student-by-class-section') ?>",
            type: "POST",
            data: { 
                cls_sec_id: clsSecId, 
                [CSRF_NAME]: CSRF_HASH 
            },
            success: function(html) {
                $target.html(html);
                initializeEventHandlers();
            },
            error: function(xhr, status, error) {
                console.error('Error loading students:', error);
                toastr.error('Failed to load students. Please try again.');
            },
            complete: function() { $loader.addClass('d-none'); }
        });
    }

    $section.on('change', function() { 
        if (!isEditMode) {
            loadStudents(this.value); 
        }
    });
    
    <?php if (!empty($cls_sec_id)) : ?>
        loadStudents('<?= (int) $cls_sec_id ?>');
    <?php endif; ?>

    // Form handling for bulk add
    function collectFormData() {
        const formData = [];
        
        $('input[name="student_id[]"]').each(function(index) {
            const $row = $(this).closest('tr');
            const studentId = $(this).val();
            const fullName = $row.find('input[name="full_name[]"]').val() || '';
            const regNo = $row.find('input[name="reg_no[]"]').val() || '';
            
            if (studentId !== '0' || fullName.trim() !== '') {
                formData.push({ name: 'student_id[]', value: studentId });
                formData.push({ name: 'full_name[]', value: fullName });
                formData.push({ name: 'reg_no[]', value: regNo });
            }
        });
        
        return formData;
    }

    // Handle form submission - different behavior based on mode
    $form.on('submit', function(e) {
        if (isEditMode) {
            e.preventDefault();
            // Edit mode: trigger the edit modal instead
            const selectedStudent = $('#edit_student_search').val();
            if (!selectedStudent) {
                toastr.error('Please search and select a student to edit');
                return false;
            }
            // Trigger the edit (the select2 select event already opened the modal)
            return false;
        }
        
        const studentData = collectFormData();
        
        if (studentData.length === 0) {
            e.preventDefault();
            toastr.error('Please add at least one student to save.');
            return false;
        }
        
        let hasEmptyNewRows = false;
        $('input[name="student_id[]"]').each(function() {
            const $row = $(this).closest('tr');
            const studentId = $(this).val();
            const fullName = $row.find('input[name="full_name[]"]').val() || '';
            
            if (studentId === '0' && fullName.trim() === '') {
                hasEmptyNewRows = true;
                $row.find('input[name="full_name[]"]').addClass('is-invalid');
            }
        });
        
        if (hasEmptyNewRows) {
            e.preventDefault();
            toastr.error('Please fill in all student names or remove empty rows.');
            return false;
        }
        
        return true;
    });

    // Drop confirmation
    $(document).off('click', '#confirmDelete').on('click', '#confirmDelete', function() {
        const dropOption = $('input[name="dropOption"]:checked').val();
        let reason = $('#leaving_reason').val();
        let leavingDate = $('#leaving_date').val();
        
        if (dropOption === 'drop_with_slc') {
            if (reason === 'Other') {
                reason = $('#leaving_reason_other').val();
            }
            if (!reason) {
                toastr.error('Please enter reason'); 
                return;
            }
            if (!leavingDate) {
                toastr.error('Please select leaving date');
                return;
            }
        }
        
        $loader.removeClass('d-none');
        
        $.ajax({
            url: "<?= base_url('admin/addbulkstudents/drop-student') ?>",
            type: "POST",
            data: {
                student_id: currentStudentId,
                drop_option: dropOption,
                leaving_reason: reason,
                leaving_date: leavingDate,
                current_session: $('#current_session').val(),
                [CSRF_NAME]: CSRF_HASH
            },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    toastr.success(res.msg);
                    if (dropOption === 'drop_with_slc' && res.slc_id) {
                        const slcUrl = "<?= base_url('admin/slc/view/') ?>" + res.slc_id;
                        window.open(slcUrl, '_blank');
                    }
                    loadStudents($section.val());
                    $('#deleteModal').modal('hide');
                } else {
                    toastr.error(res.msg || 'Failed to drop student');
                }
            },
            error: function() {
                toastr.error('Error processing drop operation');
            },
            complete: function() {
                $loader.addClass('d-none');
                resetDropModal();
            }
        });
    });

    function resetDropModal() {
        $('#leaving_reason').val('');
        $('#leaving_reason_other').val('');
        $('#leaving_date').val('<?= date('Y-m-d') ?>');
        $('#dropOnly').prop('checked', true);
        $('#slcFields').addClass('d-none');
        $('#leaving_reason_other').closest('.col-md-12').hide();
    }

    // Radio button change handler
    $(document).on('change', '#dropOnly, #dropWithSLC', function() {
        const isSLC = $('#dropWithSLC').is(':checked');
        $('#slcFields').toggleClass('d-none', !isSLC);
    });

    $(document).on('change', '#leaving_reason', function() {
        $('#leaving_reason_other').closest('.col-md-12').toggle($(this).val() === 'Other');
    });

    $form.ajaxForm({
        beforeSubmit: function(formData, $form, options) {
            $loader.removeClass('d-none');
            
            const studentData = collectFormData();
            formData.length = 0;
            
            formData.push({ name: 'cls_sec_id', value: $section.val() });
            
            const currentSession = $('#current_session').val();
            if (currentSession) {
                formData.push({ name: 'current_session', value: currentSession });
            }
            
            formData.push({ name: CSRF_NAME, value: CSRF_HASH });
            
            studentData.forEach(item => {
                formData.push({ name: item.name, value: item.value });
            });
            
            return true;
        },
        success: function(res) {
            let json = typeof res === 'string' ? JSON.parse(res) : res;
            if (json.success) {
                toastr.success(json.msg || 'Saved');
                loadStudents($section.val());
            } else { 
                toastr.error(json.msg); 
            }
        },
        error: function() {
            toastr.error('Failed to save. Please try again.');
        },
        complete: function() { 
            $loader.addClass('d-none'); 
        }
    });
});

// Save student information function for SLC
function saveStudentInfo() {
    const studentId = document.getElementById('slc_student_id').value;
    const fullName = document.getElementById('slc_full_name').value;
    const fatherName = document.getElementById('slc_father_name').value;
    const motherName = document.getElementById('slc_mother_name').value;
    const dob = document.getElementById('slc_dob').value;
    const religion = document.getElementById('slc_religion').value;
    const nationality = document.getElementById('slc_nationality').value;
    const admissionDate = document.getElementById('slc_admission_date').value;
    const fatherContact = document.getElementById('slc_father_contact').value;
    const motherContact = document.getElementById('slc_mother_contact').value;
    const emergencyContact = document.getElementById('slc_emergency_contact').value;
    
    const nameParts = fullName.split(' ');
    const firstName = nameParts[0] || '';
    const lastName = nameParts.slice(1).join(' ') || '';

    const formData = new URLSearchParams({
        student_id: studentId,
        first_name: firstName,
        last_name: lastName,
        father_name: fatherName,
        mother_name: motherName,
        dob: dob,
        religion: religion,
        nationality: nationality,
        admission_date: admissionDate,
        father_contact: fatherContact,
        mother_contact: motherContact,
        emergency_contact: emergencyContact,
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
    });

    fetch('<?= base_url('admin/addbulkstudents/update-student-info') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.msg || 'Failed to update student information'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating student information');
    });
}
</script>

<style>
  #loader { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999; }
  .table td, .table th { vertical-align: middle; }
  .reg-no-display { font-family: monospace; font-weight: bold; color: #495057; }
  
  @media print {
      body * { visibility: hidden; }
      #slcPrintContent, #slcPrintContent * { visibility: visible; }
      #slcPrintContent { position: absolute; left: 0; top: 0; width: 100%; }
      .modal-footer { display: none; }
  }
  
  .slc-container {
      background: white;
      padding: 30px;
      font-size: 14px;
      line-height: 1.6;
  }
  
  .is-invalid {
      border-color: #dc3545;
  }
  
  .row {
    display: flex !important;
    margin-bottom: 15px;
  }
</style>

<?= $this->endSection() ?>