<?= form_open_multipart(base_url('admin/students/save_admission'), ['id' => 'student-admission-form', 'class' => 'needs-validation']) ?>
<?= csrf_field() ?>




<div class="container-fluid px-3">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Student Admission Form</h5>
            <button type="button" class="btn btn-light btn-sm" onclick="window.print();">
                <i class="fas fa-print"></i> Print / Save PDF
            </button>
        </div>
        <div class="card-body">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Please fill out all required fields marked with an asterisk (<span class="text-danger">*</span>)
            </div>

            <div id="admissionAccordion">
                <!-- Student Info -->
                <div class="card mb-3">
                    <div class="card-header" id="headingStudent">
                        <h6 class="mb-0">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStudent" aria-expanded="true">
                                <i class="fas fa-user me-2"></i> Student Information
                            </button>
                        </h6>
                    </div>
                    <div id="collapseStudent" class="collapse show" data-bs-parent="#admissionAccordion">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="first_name" class="form-label required">Student Name</label>
                                    <input type="text" required class="form-control form-control-sm" name="first_name" id="first_name" value="<?= esc($first_name) ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="reg_no" class="form-label">Registration No</label>
                                    <input type="text" class="form-control form-control-sm" name="reg_no" id="reg_no" value="<?= esc($reg_no) ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="gr_no" class="form-label">G.R #</label>
                                    <input type="text" class="form-control form-control-sm" name="gr_no" id="gr_no" value="<?= esc($gr_no ?? '') ?>">
                                </div>

                               <div class="col-md-4 mb-3">
    <label for="gr_date" class="form-label">G.R Date</label>
    <div class="input-group">
        <span class="input-group-text"><i class="far fa-calendar"></i></span>
        <input type="text" class="form-control form-control-sm datepicker" name="gr_date" id="gr_date" 
               value="<?= esc($gr_date ?? date('d/m/Y')) ?>" placeholder="dd/mm/yyyy" readonly>
    </div>
</div>

                              <div class="col-md-4 mb-3">
    <label for="date_of_admission" class="form-label required">Admission Date</label>
    <div class="input-group">
        <span class="input-group-text"><i class="far fa-calendar"></i></span>
        <input type="text" required class="form-control form-control-sm datepicker" name="date_of_admission" 
               id="date_of_admission" value="<?= esc($date_of_admission ?? date('d/m/Y')) ?>" 
               placeholder="dd/mm/yyyy" readonly>
    </div>
</div>

                                <div class="col-md-4 mb-3">
                                    <label for="date_of_birth" class="form-label required">Date of Birth</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="far fa-calendar"></i></span>
                                        <input type="text" required class="form-control form-control-sm datepicker" name="date_of_birth" id="date_of_birth" value="<?= esc($date_of_birth ?? '') ?>" placeholder="dd/mm/yyyy" readonly>
                                    </div>
                                    <div id="age-badge" class="mt-2"></div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-control form-control-sm" name="gender" id="gender">
                                        <option value="male" <?= ($gender ?? '') == 'male' ? 'selected' : '' ?>>Male</option>
                                        <option value="female" <?= ($gender ?? '') == 'female' ? 'selected' : '' ?>>Female</option>
                                        <option value="other" <?= ($gender ?? '') == 'other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="previous_school" class="form-label">Previous School</label>
                                    <input type="text" class="form-control form-control-sm" name="previous_school" value="<?= esc($previous_school ?? '') ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="ps_city" class="form-label">Previous City</label>
                                    <input type="text" class="form-control form-control-sm" name="ps_city" value="<?= esc($ps_city ?? '') ?>">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="student_cnic" class="form-label">Student National ID</label>
                                    <input type="text" class="form-control form-control-sm cnic-mask" name="student_cnic" id="student_cnic" value="<?= esc($student_cnic) ?>" placeholder="XXXXX-XXXXXXX-X">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="health_conditions" class="form-label">Health Conditions</label>
                                    <textarea class="form-control" name="health_conditions" rows="2"><?= esc($health_conditions ?? '') ?></textarea>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="major_injuries" class="form-label">Major Injuries</label>
                                    <textarea class="form-control" name="major_injuries" rows="2"><?= esc($major_injuries ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fee Structure -->
                <div class="card mb-3">
                    <div class="card-header" id="headingFee">
                        <h6 class="mb-0">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFee">
                                <i class="fas fa-money-check-alt me-2"></i> Fee Structure & Discounts
                            </button>
                        </h6>
                    </div>
                    <div id="collapseFee" class="collapse" data-bs-parent="#admissionAccordion">
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-4 mb-3">
                                    <label for="section_id" class="form-label required">Section</label>
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
                                    <label for="fee_issue_date" class="form-label required">Issue Date</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="far fa-calendar"></i></span>
                                        <input type="text" required class="form-control form-control-sm datepicker" name="fee_issue_date" id="fee_issue_date" placeholder="dd/mm/yyyy" readonly>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="fee_due_date" class="form-label required">Due Date</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="far fa-calendar"></i></span>
                                        <input type="text" required class="form-control form-control-sm datepicker" name="fee_due_date" id="fee_due_date" placeholder="dd/mm/yyyy" readonly>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="fee_month" class="form-label required">Fee Month</label>
                                    <input type="month" required class="form-control form-control-sm" name="fee_month" id="fee_month">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Invoice Number</label>
                                    <input type="text" class="form-control form-control-sm" id="invoice_number_preview" readonly>
                                    <input type="hidden" name="invoice_number" id="invoice_number">
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fee Type</th>
                                            <th width="20%">Default Amount</th>
                                            <th width="20%">Student Amount</th>
                                            <th width="20%">Discount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="fee-type-container">
                                        <!-- Fee types will be loaded here -->
                                    </tbody>
                                    <tfoot class="table-group-divider">
                                        <tr>
                                            <th class="text-end">Total</th>
                                            <th id="total-default">0.00</th>
                                            <th id="total-student">0.00</th>
                                            <th id="total-discount">0.00</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Parent Info -->
                <div class="card mb-3">
                    <div class="card-header" id="headingParent">
                        <h6 class="mb-0">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseParent">
                                <i class="fas fa-user-friends me-2"></i> Parent & Contact Information
                            </button>
                        </h6>
                    </div>
                    <div id="collapseParent" class="collapse" data-bs-parent="#admissionAccordion">
                        <div class="card-body">
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
                                        <label for="<?= $name ?>" class="form-label <?= $required ? 'required' : '' ?>"><?= $label ?></label>
                                        <?php if($name === 'father_cnic'): ?>
                                            <input type="text" class="form-control form-control-sm cnic-mask" name="<?= $name ?>" id="<?= $name ?>" value="<?= esc($value) ?>" <?= $required ? 'required' : '' ?> placeholder="XXXXX-XXXXXXX-X">
                                        <?php elseif(in_array($name, ['father_contact', 'mother_contact', 'emergency_contact', 'whatsapp_contact'])): ?>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                <input type="text" class="form-control form-control-sm phone-mask" name="<?= $name ?>" id="<?= $name ?>" value="<?= esc($value) ?>" <?= $required ? 'required' : '' ?> placeholder="+XX XXX XXXXXXX">
                                            </div>
                                        <?php else: ?>
                                            <input type="text" class="form-control form-control-sm" name="<?= $name ?>" id="<?= $name ?>" value="<?= esc($value) ?>" <?= $required ? 'required' : '' ?>>
                                        <?php endif; ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attachments -->
                <div class="card">
                    <div class="card-header" id="headingAttachments">
                        <h6 class="mb-0">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAttachments">
                                <i class="fas fa-paperclip me-2"></i> Attachments
                            </button>
                        </h6>
                    </div>
                    <div id="collapseAttachments" class="collapse" data-bs-parent="#admissionAccordion">
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>Please upload clear scanned copies of the following documents.
                            </div>
                            
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
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-primary">
                                                <i class="fas fa-file me-2"></i><?= esc($value->a_type_name) ?>
                                            </h6>
                                            
                                            <input type="hidden" class="a_type_id" value="<?= $value->a_type_id ?>">
                                            <input type="hidden" class="attachement_id" value="<?= $attachement->attachement_id ?? 0 ?>">
                                            
                                            <img id="preview_<?= $value->a_type_id ?>" 
                                                src="<?= $attachement ? base_url('studentattachements/' . $attachement->attachement_path) : 'https://via.placeholder.com/300x200?text=Upload+Image' ?>"
                                                class="attachment-preview mb-3">
                                            
                                            <div class="d-grid gap-2">
                                                <input type="file" class="form-control d-none attachment-file" data-typeid="<?= $value->a_type_id ?>" id="attachment_<?= $value->a_type_id ?>" accept="image/*">
                                                <button class="btn btn-sm btn-outline-primary" onclick="document.getElementById('attachment_<?= $value->a_type_id ?>').click()">
                                                    <i class="fas fa-upload me-2"></i>Upload File
                                                </button>
                                                
                                                <?php if ($attachement): ?>
                                                    <a href="<?= base_url('studentattachements/' . $attachement->attachement_path) ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                                        <i class="fas fa-eye me-2"></i>View Document
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Buttons -->
            <div class="d-flex justify-content-center mt-4">
                <button type="submit" class="btn btn-success px-4">
                    <i class="fas fa-save me-2"></i>Save Admission
                </button>
                <button type="reset" class="btn btn-secondary px-4 ms-2">
                    <i class="fas fa-redo me-2"></i>Reset
                </button>
                <a href="<?= base_url('admin/students') ?>" class="btn btn-light px-4 ms-2">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </div>
    </div>
</div>

<?= form_close() ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/4.0.9/jquery.inputmask.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">

<script>
$(document).ready(function() {
     // First set default values for empty fields
    const today = new Date();
    const dueDate = new Date();
    dueDate.setDate(today.getDate() + 10); // 10 days from today
    
    // Format dates as dd/mm/yyyy
    const formatDate = (date) => {
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        return `${day}/${month}/${date.getFullYear()}`;
    };

    const todayFormatted = formatDate(today);
    const dueDateFormatted = formatDate(dueDate);

    // Set default values if empty
    if (!$('#gr_date').val()) {
        $('#gr_date').val(todayFormatted);
    }
    
    if (!$('#date_of_admission').val()) {
        $('#date_of_admission').val(todayFormatted);
    }
    
    if (!$('#fee_issue_date').val()) {
        $('#fee_issue_date').val(todayFormatted);
    }
    
    if (!$('#fee_due_date').val()) {
        $('#fee_due_date').val(dueDateFormatted);
    }

    // Now initialize datepickers
    $('.datepicker').datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true,
        endDate: new Date()
    }).attr('readonly', 'readonly');
    
    // Update datepicker with the values
    $('.datepicker').each(function() {
        $(this).datepicker('update', $(this).val());
    });
    
    // Set fee month to current month
    const month = (today.getMonth() + 1).toString().padStart(2, '0');
    $('#fee_month').val(`${today.getFullYear()}-${month}`);
    
    // Rest of your initialization code...
    $('.cnic-mask').inputmask('99999-9999999-9');
    $('.phone-mask').inputmask('+99 999 9999999');

    $('.attachment-file').on('change', function() {
        const typeId = $(this).data('typeid');
        const file = this.files[0];
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#preview_' + typeId).attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // Age calculation and badge display
    function calculateAge(dobStr) {
        if (!dobStr) return '';
        
        const parts = dobStr.split('/');
        if (parts.length !== 3) return '';
        
        const dob = new Date(parts[2], parts[1] - 1, parts[0]);
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

    function updateAgeBadge() {
        const dob = $('#date_of_birth').val();
        const age = calculateAge(dob);
        
        if (age) {
            const parts = dob.split('/');
            const dobDate = new Date(parts[2], parts[1] - 1, parts[0]);
            const today = new Date();
            const ageYears = today.getFullYear() - dobDate.getFullYear();
            
            let badgeClass = 'text-bg-info';
            if (ageYears < 5) badgeClass = 'text-bg-warning';
            if (ageYears > 18) badgeClass = 'text-bg-danger';
            
            $('#age-badge').html(`
                <div class="d-flex mt-2">
                    <span class="badge ${badgeClass} text-white badge-custom me-2">
                        <i class="fas fa-user-clock me-1"></i> ${age}
                    </span>
                    <span class="badge ${ageYears >= 5 && ageYears <= 18 ? 'text-bg-success' : 'text-bg-danger'} text-white badge-custom">
                        <i class="fas ${ageYears >= 5 && ageYears <= 18 ? 'fa-check-circle' : 'fa-exclamation-triangle'} me-1"></i>
                        ${ageYears >= 5 && ageYears <= 18 ? 'Valid age' : 'Age not in standard range'}
                    </span>
                </div>
            `);
        } else {
            $('#age-badge').empty();
        }
    }
    
    // Trigger badge update on DOB change
    $('#date_of_birth').on('change', updateAgeBadge);
    updateAgeBadge();

    // Load fee structure when section is selected
    $('#section_id').on('change', function() {
        const cls_sec_id = $(this).val();
        if (!cls_sec_id) return;
        
        $.ajax({
            url: '<?= site_url("admin/ajax/get_class_fee_amounts") ?>',
            method: 'POST',
            data: { cls_sec_id: cls_sec_id },
            dataType: 'json',
            beforeSend: function() {
                $('#fee-type-container').html('<tr><td colspan="4" class="text-center py-3">Loading fee structure...</td></tr>');
            },
            success: function(res) {
                if (res.status === 'success') {
                    let html = '';
                    let totalDefault = 0;
                    let totalStudent = 0;
                    
                    res.data.forEach((fee, i) => {
                        totalDefault += parseFloat(fee.default_amount) || 0;
                        totalStudent += parseFloat(fee.default_amount) || 0;
                        
                        html += `
                            <tr class="fee-row">
                                <td>
                                    ${fee.fee_type_title}
                                    <input type="hidden" name="fee_type_id[]" value="${fee.fee_type_id}">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm default-amount" 
                                        value="${fee.default_amount}" readonly>
                                </td>
                                <td>
                                    <input type="number" name="student_amount[]" 
                                        class="form-control form-control-sm student-amount" 
                                        value="${fee.default_amount}" 
                                        data-default="${fee.default_amount}">
                                </td>
                                <td class="text-danger discount-info">
                                    0.00
                                </td>
                            </tr>`;
                    });
                    
                    $('#fee-type-container').html(html);
                    updateTotals();
                    
                    // Add event listeners to student amount fields
                    $('.student-amount').on('input', function() {
                        const defaultVal = parseFloat($(this).data('default')) || 0;
                        const studentVal = parseFloat($(this).val()) || 0;
                        const discount = defaultVal - studentVal;
                        
                        $(this).closest('.fee-row').find('.discount-info').text(discount.toFixed(2));
                        updateTotals();
                    });
                } else {
                    $('#fee-type-container').html('<tr><td colspan="4" class="text-center py-3 text-danger">Failed to load fee structure</td></tr>');
                }
            },
            error: function() {
                $('#fee-type-container').html('<tr><td colspan="4" class="text-center py-3 text-danger">Error loading fee structure</td></tr>');
            }
        });
    });
    
    // Initialize with current section if selected
    <?php if (!empty($section_id)): ?>
        $('#section_id').trigger('change');
    <?php endif; ?>
    
    // Calculate totals and discounts
    function updateTotals() {
        let totalDefault = 0;
        let totalStudent = 0;
        let totalDiscount = 0;
        
        $('.fee-row').each(function() {
            const defaultVal = parseFloat($(this).find('.default-amount').val()) || 0;
            const studentVal = parseFloat($(this).find('.student-amount').val()) || 0;
            const discount = defaultVal - studentVal;
            
            totalDefault += defaultVal;
            totalStudent += studentVal;
            totalDiscount += discount;
        });
        
        $('#total-default').text(totalDefault.toFixed(2));
        $('#total-student').text(totalStudent.toFixed(2));
        $('#total-discount').text(totalDiscount.toFixed(2));
    }
    
    // Generate preview invoice number
 function generateInvoicePreview() {
    const today = new Date();
    const year = today.getFullYear().toString().substr(2, 2); // Last 2 digits of year
    
    // This is just a preview - actual number will be generated on server
    // Using a random 5-digit number for preview purposes
    const randomSequence = Math.floor(Math.random() * 90000 + 10000); // Random 5-digit number
    const invoiceNo = `${year}-INV-${randomSequence}`;
    
    $('#invoice_number_preview').val(invoiceNo);
    $('#invoice_number').val(invoiceNo);
}
    
    // Generate invoice preview
    generateInvoicePreview();
    
    // Form validation and submission
    $('#student-admission-form').validate({
        rules: {
            first_name: 'required',
            father_cnic: 'required',
            f_name: 'required',
            date_of_admission: 'required',
            date_of_birth: 'required',
            section_id: 'required',
            fee_issue_date: 'required',
            fee_due_date: 'required',
            fee_month: 'required'
        },
        messages: {
            first_name: 'Please enter student first name',
            father_cnic: 'Please enter father CNIC',
            f_name: 'Please enter father name',
            date_of_admission: 'Please select admission date',
            date_of_birth: 'Please select date of birth',
            section_id: 'Please select a section',
            fee_issue_date: 'Please select fee issue date',
            fee_due_date: 'Please select fee due date',
            fee_month: 'Please select fee month'
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
                        toastr.success(response.msg);
                         if (response.pdf_url) {
                         window.open(response.pdf_url, '_blank');
        }
                        setTimeout(function() {
                            window.location.href = '<?= base_url('admin/students') ?>';
                        }, 1500);
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
</script>