<!-- ready form data inserted properly 30th June 2026 Accordion-Based Student Admission Form --> 
<?= form_open_multipart(base_url('admin/students/save_admission'), ['id' => 'student-admission-form', 'class' => 'needs-validation']) ?>
<?= csrf_field() ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
@media print {
  .btn, .card-header button, .form-label, .form-control {
    display: none !important;
  }
  body {
    -webkit-print-color-adjust: exact !important;
  }
  .card {
    border: none;
    box-shadow: none;
  }
}
</style>


<div class="container-fluid px-3">
  <div class="card shadow mb-4">
    <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Student Admission Form</h5>
      <button type="button" class="btn btn-light btn-sm" onclick="window.print();">
        <i class="fas fa-print"></i> Print / Save PDF
      </button>
    </div>
    <div class="card-body">

      <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"> <?= session()->getFlashdata('success') ?> </div>
      <?php endif; ?>

      <div id="admissionAccordion">
        <!-- Student Info -->
        <div class="card">
          <div class="card-header" id="headingStudent">
            <h6 class="mb-0">
              <button class="btn btn-link text-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStudent">
                <i class="fas fa-user"></i> Student Information
              </button>
            </h6>
          </div>
          <div id="collapseStudent" class="collapse show" data-bs-parent="#admissionAccordion">
            <div class="card-body">
              <div class="row">
                <!-- Include your student info fields here like first_name, reg_no, gr_no, etc. -->
                 <div class="row">
        <div class="col-md-4 mb-3">
          <label for="first_name">Student Name <span class="text-danger">*</span></label>
          <input type="text" required class="form-control form-control-sm" name="first_name" id="first_name" value="<?= esc($first_name) ?>">
        </div>

        <div class="col-md-4 mb-3">
          <label for="reg_no">Registration No</label>
          <input type="text" class="form-control form-control-sm" name="reg_no" id="reg_no" value="<?= esc($reg_no) ?>">
        </div>

        <div class="col-md-4 mb-3">
          <label for="gr_no">G.R #</label>
          <input type="text" class="form-control form-control-sm" name="gr_no" id="gr_no" value="<?= esc($gr_no ?? '') ?>">
        </div>




        <div class="col-md-4 mb-3">
          <label for="gr_date">G.R Date</label>
          <input type="text" class="form-control form-control-sm datepicker" name="gr_date" id="gr_date"
           value="<?= esc($gr_date ?? '') ?>" placeholder="dd/mm/yyyy" readonly onkeydown="return false;">
        </div>


        <div class="col-md-4 mb-3">
          <label for="date_of_admission">Admission Date <span class="text-danger">*</span></label>
          <input type="text" required class="form-control form-control-sm datepicker" name="date_of_admission" id="date_of_admission" value="<?= esc($date_of_admission ?? '') ?>" placeholder="dd/mm/yyyy" readonly onkeydown="return false;">
         
        </div>

        <div class="col-md-4 mb-3">
          <label for="date_of_birth">Date of Birth <span class="text-danger">*</span></label>
          <input type="text" class="form-control form-control-sm datepicker" name="date_of_birth" id="date_of_birth"
       value="<?= esc($date_of_birth ?? '') ?>" placeholder="dd/mm/yyyy" readonly onkeydown="return false;">
          <div id="age-badge" class="mt-1"></div>
        </div>


        <div class="col-md-4 mb-3">
          <label for="gender">Gender</label>
          <select class="form-control form-control-sm" name="gender" id="gender">
            <option value="male" <?= ($gender ?? '') == 'male' ? 'selected' : '' ?>>Male</option>
            <option value="female" <?= ($gender ?? '') == 'female' ? 'selected' : '' ?>>Female</option>
            <option value="other" <?= ($gender ?? '') == 'other' ? 'selected' : '' ?>>Other</option>
          </select>
        </div>

        <div class="col-md-4 mb-3">
          <label for="previous_school">Previous School</label>
          <input type="text" class="form-control form-control-sm" name="previous_school" value="<?= esc($previous_school ?? '') ?>">
        </div>

        <div class="col-md-4 mb-3">
          <label for="ps_city">Previous City</label>
          <input type="text" class="form-control form-control-sm" name="ps_city" value="<?= esc($ps_city ?? '') ?>">
        </div>

        <div class="col-md-8 mb-3">
          <label for="health_conditions">Health Conditions</label>
          <textarea class="form-control" name="health_conditions" rows="2"><?= esc($health_conditions ?? '') ?></textarea>
        </div>

        <div class="col-md-8 mb-3">
          <label for="major_injuries">Major Injuries</label>
          <textarea class="form-control" name="major_injuries" rows="2"><?= esc($major_injuries ?? '') ?></textarea>
        </div>

        <div class="col-md-4 mb-3">
          <label for="student_cnic">Student National ID</label>
          <input type="text" class="form-control form-control-sm" name="student_cnic" id="student_cnic" value="<?= esc($student_cnic) ?>" placeholder="XXXXX-XXXXXXX-X">
        </div>
      </div>

                <!-- (Use the same fields you've provided, keeping the same names and IDs) -->
              </div>
            </div>
          </div>
        </div>

        <!-- Fee Structure -->
        <div class="card">
          <div class="card-header" id="headingFee">
            <h6 class="mb-0">
              <button class="btn btn-link collapsed text-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFee">
                <i class="fas fa-money-check-alt"></i> Fee Structure & Discounts
              </button>
            </h6>
          </div>
          <div id="collapseFee" class="collapse" data-bs-parent="#admissionAccordion">
            <div class="card-body">
              <div class="row">
                <!-- Include your fee fields here: section_id, class_fee, discounted_amount, etc. -->

      <!-- Fee Details -->
      <div class="section-header mt-5 mb-4">
        <h5 class="text-primary"><i class="fas fa-money-check-alt me-2"></i>Fee Structure & Discounts</h5>
        <hr class="mt-1 mb-4">
      </div>

      <div class="row">
        <div class="col-md-4 mb-3">
          <label for="section_id">Section <span class="text-danger">*</span></label>
          <select class="form-control form-control-sm" name="section_id" id="section_id" required>
            <option value="">Select Section</option>
            <?php foreach ($sectionsclassinfo as $sectionvalue): ?>
              <option value="<?= $sectionvalue['section_id'] ?>" <?= ($sectionvalue['section_id'] == ($section_id ?? 0)) ? 'selected' : '' ?>>
                <?= esc($sectionvalue['sectionclassname']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-4 mb-3">
          <label for="class_fee">Class Fee <span class="text-danger">*</span></label>
          <input type="number" readonly required class="form-control form-control-sm" name="class_fee" id="class_fee" value="<?= esc($classesfee ?? 0) ?>">
        </div>

        <div class="col-md-4 mb-3">
          <label for="discounted_amount">Student Fee <span class="text-danger">*</span></label>
          <input type="number" class="form-control form-control-sm" name="discounted_amount" id="discounted_amount" value="<?= esc(($classesfee ?? 0) - ($discounted_amount ?? 0)) ?>" disabled>
        </div>

        <div class="col-md-4 mb-3">
          <label for="discount_display">Discount</label>
          <input type="text" class="form-control form-control-sm bg-light fw-bold" readonly id="discount_display" value="0">
        </div>

        <div class="col-md-4 mb-3">
          <label for="transport_fee">Transport Fee</label>
          <input type="number" class="form-control form-control-sm" name="transport_fee" id="transport_fee" value="<?= esc(($transportfee ?? 0) - ($transport_discount ?? 0)) ?>">
        </div>

        <div class="col-md-4 mb-3">
          <label for="fee_plan">Student Fee Plan</label>
          <select class="form-control form-control-sm" name="fee_plan">
            <option value="0" <?= ($fee_plan ?? 0) == 0 ? 'selected' : '' ?>>Monthly</option>
            <?php foreach ($fee_plans as $value): ?>
              <option value="<?= $value->plan_id ?>" <?= ($value->plan_id == ($fee_plan ?? 0)) ? 'selected' : '' ?>><?= esc($value->plan_name) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Parent Info -->
        <div class="card">
          <div class="card-header" id="headingParent">
            <h6 class="mb-0">
              <button class="btn btn-link collapsed text-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapseParent">
                <i class="fas fa-user-friends"></i> Parent & Contact Information
              </button>
            </h6>
          </div>
          <div id="collapseParent" class="collapse" data-bs-parent="#admissionAccordion">
            <div class="card-body">
              <div class="row">
                <!-- Include contact/parent fields: father_name, contact numbers, etc. -->

      <div class="row">
        <?php
        $fields = [
          ['father_cnic', 'Father CNIC', $father_cnic ?? '', true],
          ['f_name', 'Father Name', $f_name ?? '', true],
          ['father_contact', 'Father Contact', $father_contact ?? ''],
          ['father_email', 'Father Email', $father_email ?? ''],
          ['father_occupation', 'Father Occupation', $father_occupation ?? ''],
          ['father_office_address', 'Father Office Address', $father_office_address ?? ''],
          ['m_name', 'Mother Name', $m_name ?? ''],
          ['mother_contact', 'Mother Contact', $mother_contact ?? ''],
          ['whatsapp_contact', 'WhatsApp Contact', $whatsapp_contact ?? ''],
          ['address_line1', 'Address', $address_line1 ?? ''],
          ['city', 'City', $city ?? ''],
          ['hear_source', 'Hear Source', $hear_source ?? ''],
          ['emergency_contact_person', 'Emergency Contact Person', $emergency_contact_person ?? ''],
          ['emergency_contact', 'Emergency Contact', $emergency_contact ?? ''],
          ['a_address', 'Emergency Address', $a_address ?? ''],
        ];
        foreach ($fields as $field) {
          [$name, $label, $value, $required] = array_pad($field, 4, false);
        ?>
          <div class="col-md-4 mb-3">
            <label for="<?= $name ?>"><?= $label ?><?= $required ? ' <span class="text-danger">*</span>' : '' ?></label>
            <input type="text" class="form-control form-control-sm" name="<?= $name ?>" id="<?= $name ?>" value="<?= esc($value) ?>" <?= $required ? 'required' : '' ?>>
          </div>
        <?php } ?>
      </div>
              </div>
            </div>
          </div>
        </div>

       <!-- Attachments -->
        <div class="card">
          <div class="card-header" id="headingAttachments">
            <h6 class="mb-0">
              <button class="btn btn-link collapsed text-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAttachments">
                <i class="fas fa-paperclip"></i> Attachments
              </button>
            </h6>
          </div>
          <div id="collapseAttachments" class="collapse" data-bs-parent="#admissionAccordion">
            <div class="card-body">
              <div class="row">
                <?php
                $db = \Config\Database::connect();
                foreach ($attachementTypesInfo as $value):
                  $attachement = $db->table('attachements')
                    ->where('student_id', $id ?? 0)
                    ->where('a_type_id', $value->a_type_id)
                    ->get()
                    ->getRow();
                ?>
                <div class="col-md-6 col-lg-4 mb-4 attachment-wrapper">
                  <label class="fw-bold"> <?= esc($value->a_type_name) ?> </label>
                  <input type="hidden" class="a_type_id" value="<?= $value->a_type_id ?>">
                  <input type="hidden" class="attachement_id" value="<?= $attachement->attachement_id ?? 0 ?>">

                  <div class="mb-3 mb-2">
                    <input type="file" class="form-control attachment-file" data-typeid="<?= $value->a_type_id ?>" id="attachment_<?= $value->a_type_id ?>">
                    <label class="form-label" for="attachment_<?= $value->a_type_id ?>">Choose file</label>
                  </div>

                  <div class="text-center">
                    <?php if ($attachement): ?>
                      <a href="<?= base_url('studentattachements/' . $attachement->attachement_path) ?>" target="_blank" class="d-block mb-1">View Document</a>
                    <?php endif; ?>
                    <img id="preview_<?= $value->a_type_id ?>" 
                         src="<?= $attachement ? base_url('studentattachements/' . $attachement->attachement_path) : base_url('assets/img/no-image.png') ?>"
                         class="img-thumbnail" style="max-height: 100px;">
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

     <!-- Form Buttons -->
      <div class="text-center mt-4">
        <button type="submit" class="btn btn-success px-4">
          <i class="fas fa-save me-2"></i>Save Admission
        </button>
        <button type="reset" class="btn btn-secondary px-4">
          <i class="fas fa-redo me-2"></i>Reset
        </button>
        <a href="<?= base_url('admin/students') ?>" class="btn btn-light px-4">
          <i class="fas fa-times me-2"></i>Cancel
        </a>
      </div>

    </div>
  </div>
</div>

<?= form_close() ?>







<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/4.0.9/jquery.inputmask.bundle.min.js"></script>



<script>
// Age calculation utility
function calculateAge(dobStr) {
  const parts = dobStr.split('/');
  if (parts.length !== 3) return '';

  const dob = new Date(`${parts[2]}-${parts[1]}-${parts[0]}`); // Convert dd/mm/yyyy to yyyy-mm-dd
  const today = new Date();

  let years = today.getFullYear() - dob.getFullYear();
  let months = today.getMonth() - dob.getMonth();
  let days = today.getDate() - dob.getDate();

  if (days < 0) {
    months--;
    days += new Date(today.getFullYear(), today.getMonth(), 0).getDate();
  }

  if (months < 0) {
    years--;
    months += 12;
  }

  return `${years} year${years !== 1 ? 's' : ''}, ${months} month${months !== 1 ? 's' : ''}, ${days} day${days !== 1 ? 's' : ''}`;
}

// Show age badge under DOB input
function updateAgeBadge() {
  const dob = $('#date_of_birth').val();
  const age = calculateAge(dob);

  if (age) {
    $('#age-badge').html(`<span class="badge text-bg-info p-2"><i class="fas fa-user-clock me-1"></i>${age}</span>`);
  } else {
    $('#age-badge').empty();
  }
}

// Trigger badge update on DOB change
$('#date_of_birth').on('change blur', updateAgeBadge);

// Show badge on load if DOB is pre-filled
updateAgeBadge();



$(document).ready(function () {
  $('.datepicker').datepicker({
    format: 'dd/mm/yyyy',
    autoclose: true,
    todayHighlight: true
  });
    // Initialize input masks
    $('#father_cnic, #student_cnic').inputmask('99999-9999999-9');
    $('input[name="father_contact"], input[name="mother_contact"], input[name="emergency_contact"]').inputmask('+99 999 9999999');

    // File input preview
 $('.attachment-file').on('change', function () {
    const typeId = $(this).data('typeid');
    const file = this.files[0];

    if (file && typeId) {
      const reader = new FileReader();
      reader.onload = function (e) {
        $('#preview_' + typeId).attr('src', e.target.result);
      };
      reader.readAsDataURL(file);

      $(this).next('.form-label').text(file.name);
    }
  });


$('#section_id').on('change', function () {
  const sectionId = $(this).val();
  if (sectionId) {
    $('#discounted_amount').prop('disabled', false);

    $.ajax({
      url: "<?= base_url('admin/students/get_fee_amount') ?>",
      type: "POST",
      data: {
        section_id: sectionId,
        <?= csrf_token() ?>: "<?= csrf_hash() ?>"
      },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          $('#class_fee').val(response.monthly_fee);
          $('#discounted_amount').val(response.monthly_fee); // Default: no discount
          $('#discount_display').val(0);
        } else {
          $('#class_fee').val('');
          $('#discounted_amount').val('');
          $('#discount_display').val('');
          toastr.error(response.msg || 'Fee data not found');
        }
      },
      error: function () {
        $('#class_fee').val('');
        $('#discounted_amount').val('');
        $('#discount_display').val('');
        toastr.error('Failed to fetch class fee.');
      }
    });
  } else {
    $('#class_fee').val('');
    $('#discounted_amount').val('').prop('disabled', true);
    $('#discount_display').val('');
  }
});

// Update discount dynamically as student fee is entered
$('#discounted_amount').on('input', function () {
  const classFee = parseFloat($('#class_fee').val()) || 0;
  const studentFee = parseFloat($(this).val()) || 0;

  if (studentFee > classFee) {
    toastr.warning('Student fee must be less than or equal to class fee.');
    $(this).val(classFee); // Reset to max allowed
    $('#discount_display').val(0);
  } else {
    const discount = classFee - studentFee;
    $('#discount_display').val(discount.toFixed(2));
  }
});

    // New logic: Load class fee on section change
    $('#section_id').on('change', function () {
        const sectionId = $(this).val();
        if (!sectionId) {
            $('#class_fee').val('');
            return;
        }

        $.ajax({
            url: "<?= site_url('admin/students/get_fee_amount') ?>",
            type: "POST",
            data: {
                section_id: sectionId,
                <?= csrf_token() ?>: "<?= csrf_hash() ?>"
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    $('#class_fee').val(response.monthly_fee);
                } else {
                    $('#class_fee').val('');
                    toastr.warning(response.msg || 'Fee not found.');
                }
            },
            error: function () {
                $('#class_fee').val('');
                toastr.error('Could not fetch class fee.');
            }
        });
    });

    // Form validation and submission
    $('#student-admission-form').validate({
        rules: {
            first_name: 'required',
            father_cnic: 'required',
            f_name: 'required',
            date_of_admission: 'required',
            date_of_birth: 'required',
            section_id: 'required'
        },
        messages: {
            first_name: 'Please enter student first name',
            father_cnic: 'Please enter father CNIC',
            f_name: 'Please enter father name',
            date_of_admission: 'Please select admission date',
            date_of_birth: 'Please select date of birth',
            section_id: 'Please select a section'
        },
        errorElement: 'small',
        errorClass: 'text-danger form-text',
        highlight: function(element) {
            $(element).addClass('is-invalid').removeClass('is-valid');
        },
        unhighlight: function(element) {
            $(element).removeClass('is-invalid').addClass('is-valid');
        },
        submitHandler: function(form) {
            const formData = new FormData(form);
            
            $.ajax({
                url: $(form).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function() {
                    $('button[type="submit"]').prop('disabled', true)
                        .html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...');
                },
                success: function(response) {
                    if (response.success) {
                        // Only reload the form, do not show message
                        location.reload();
                    } else {
                        toastr.error(response.msg);
                        $('button[type="submit"]').prop('disabled', false)
                            .html('<i class="fas fa-save me-2"></i>Save Admission');
                    }
                },
                error: function() {
                    toastr.error('An error occurred. Please try again.');
                    $('button[type="submit"]').prop('disabled', false)
                        .html('<i class="fas fa-save me-2"></i>Save Admission');
                }
            });
        }
    });
});


$('#section_id').on('change', function () {
    const sectionId = $(this).val();
    
    if (sectionId) {
        // Enable discounted_amount field
        $('#discounted_amount').prop('disabled', false);

        // Optional: fetch and update class_fee here as well
        $.ajax({
            url: "<?= base_url('admin/students/get_fee_amount') ?>",
            type: "POST",
            data: {
                section_id: sectionId,
                <?= csrf_token() ?>: "<?= csrf_hash() ?>"
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $('#class_fee').val(response.monthly_fee);
                    $('#discounted_amount').val(response.monthly_fee); // You can reset or keep previous
                } else {
                    $('#class_fee').val('');
                    $('#discounted_amount').val('');
                    toastr.error(response.msg || 'Fee data not found');
                }
            },
            error: function() {
                $('#class_fee').val('');
                $('#discounted_amount').val('');
                toastr.error('Failed to fetch class fee.');
            }
        });

    } else {
        $('#discounted_amount').prop('disabled', true);
        $('#class_fee').val('');
        $('#discounted_amount').val('');
    }
});
</script>

<style>
.section-header {
    background-color: #f8f9fa;
    padding: 10px 15px;
    border-radius: 5px;
    border-start: 4px solid #4e73df;
}
.datepicker {
    z-index: 1151 !important;
}
.form-label::after {
    content: "Browse";
}
.is-invalid {
    border-color: #e74a3b;
}
.is-valid {
    border-color: #1cc88a;
}

#discount_display {
  background-color: #f8f9fa;
  font-weight: bold;
}
</style>