<?= form_open_multipart(base_url('admin/students/save_admission'), ['id' => 'student-admission-form', 'class' => 'needs-validation']) ?>
<?= csrf_field() ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">

<style>
:root {
    --primary-color: #3a86ff;
    --secondary-color: #ff006e;
    --accent-color: #8338ec;
    --light-bg: #f8f9fa;
    --success-color: #38b000;
}

.card-header {
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    color: white;
    border-radius: 0.5rem 0.5rem 0 0 !important;
}

.accordion-button {
    font-weight: 600;
    color: #333;
    background-color: var(--light-bg);
    padding: 0.75rem 1.25rem;
}

.accordion-button:not(.collapsed) {
    color: var(--primary-color);
    background-color: #e8f4ff;
    box-shadow: inset 0 -1px 0 rgba(0,0,0,.125);
}

.badge-custom {
    padding: 0.5em 0.8em;
    font-size: 0.9rem;
    font-weight: 500;
}

.attachment-preview {
    width: 100%;
    height: 120px;
    object-fit: contain;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    transition: all 0.3s ease;
    background-color: #f8f9fa;
}

.attachment-preview:hover {
    transform: scale(1.03);
    border-color: var(--primary-color);
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem rgba(58, 134, 255, 0.25);
}

.section-title {
    position: relative;
    padding-left: 1.5rem;
    margin-bottom: 1.5rem;
    font-weight: 600;
    color: var(--primary-color);
}

.section-title:before {
    content: "";
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    height: 24px;
    width: 6px;
    background-color: var(--primary-color);
    border-radius: 3px;
}

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
    .accordion-button:not(.collapsed) {
        background-color: transparent !important;
        color: black !important;
    }
    .accordion-button::after {
        display: none;
    }
    .collapse:not(.show) {
        display: block !important;
    }
}

.btn-primary {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.btn-success {
    background-color: var(--success-color);
    border-color: var(--success-color);
}

.alert-section {
    background-color: #e8f4ff;
    border-start: 4px solid var(--primary-color);
    padding: 1rem;
    border-radius: 0 0.5rem 0.5rem 0;
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 500;
    margin-bottom: 0.3rem;
}

.required:after {
    content: " *";
    color: var(--secondary-color);
}

.is-invalid {
    border-color: #e74a3b;
}

.is-valid {
    border-color: #1cc88a;
}

.form-label::after {
    content: "Browse";
}

#discount_display {
    background-color: #f8f9fa;
    font-weight: bold;
}

.datepicker {
    z-index: 1151 !important;
}
</style>

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
                                        <input type="text" class="form-control form-control-sm datepicker" name="gr_date" id="gr_date" value="<?= esc($gr_date ?? '') ?>" placeholder="dd/mm/yyyy" readonly>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="date_of_admission" class="form-label required">Admission Date</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="far fa-calendar"></i></span>
                                        <input type="text" required class="form-control form-control-sm datepicker" name="date_of_admission" id="date_of_admission" value="<?= esc($date_of_admission ?? '') ?>" placeholder="dd/mm/yyyy" readonly>
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
                            <div class="row">
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
                                    <label for="class_fee" class="form-label required">Class Fee</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" readonly required class="form-control form-control-sm" name="class_fee" id="class_fee" value="<?= esc($classesfee ?? 0) ?>">
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="discounted_amount" class="form-label required">Student Fee</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control form-control-sm" name="discounted_amount" id="discounted_amount" value="<?= esc(($classesfee ?? 0) - ($discounted_amount ?? 0)) ?>" disabled>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="discount_display" class="form-label">Discount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="text" class="form-control form-control-sm bg-light fw-bold" readonly id="discount_display" value="0">
                                        <span class="input-group-text bg-success text-white">
                                            <i class="fas fa-tag"></i>
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="transport_fee" class="form-label">Transport Fee</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control form-control-sm" name="transport_fee" id="transport_fee" value="<?= esc(($transportfee ?? 0) - ($transport_discount ?? 0)) ?>">
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="fee_plan" class="form-label">Student Fee Plan</label>
                                    <select class="form-control form-control-sm" name="fee_plan">
                                        <option value="0" <?= ($fee_plan ?? 0) == 0 ? 'selected' : '' ?>>Monthly</option>
                                        <?php foreach ($fee_plans as $value): ?>
                                            <option value="<?= $value->plan_id ?>" <?= ($value->plan_id == ($fee_plan ?? 0)) ? 'selected' : '' ?>><?= esc($value->plan_name) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-12 mt-3">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-primary rounded-circle p-3 me-3">
                                                            <i class="fas fa-money-bill-wave fa-2x text-white"></i>
                                                        </div>
                                                        <div>
                                                            <h5 class="mb-0">$<span id="total-fee">0.00</span></h5>
                                                            <small class="text-muted">Total Fee</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-success rounded-circle p-3 me-3">
                                                            <i class="fas fa-tag fa-2x text-white"></i>
                                                        </div>
                                                        <div>
                                                            <h5 class="mb-0">$<span id="total-discount">0.00</span></h5>
                                                            <small class="text-muted">Total Discount</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-warning rounded-circle p-3 me-3">
                                                            <i class="fas fa-file-invoice-dollar fa-2x text-white"></i>
                                                        </div>
                                                        <div>
                                                            <h5 class="mb-0">$<span id="net-fee">0.00</span></h5>
                                                            <small class="text-muted">Net Fee</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/4.0.9/jquery.inputmask.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
<script>
$(document).ready(function() {
    // Initialize date pickers
    $('.datepicker').datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true,
        endDate: new Date()
    }).attr('readonly', 'readonly');

    // Initialize input masks
    $('.cnic-mask').inputmask('99999-9999999-9');
    $('.phone-mask').inputmask('+99 999 9999999');

    // File input preview
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

    // Fee calculation logic
    function calculateFees() {
        const classFee = parseFloat($('#class_fee').val()) || 0;
        const studentFee = parseFloat($('#discounted_amount').val()) || 0;
        const transportFee = parseFloat($('#transport_fee').val()) || 0;
        
        const discount = classFee - studentFee;
        const totalFee = classFee + transportFee;
        const netFee = totalFee - discount;
        
        $('#discount_display').val(discount.toFixed(2));
        $('#total-fee').text(totalFee.toFixed(2));
        $('#total-discount').text(discount.toFixed(2));
        $('#net-fee').text(netFee.toFixed(2));
    }
    
    // Initialize section fee values
    $('#section_id').on('change', function() {
        const sectionId = $(this).val();
        if (!sectionId) {
            $('#class_fee').val('');
            $('#discounted_amount').val('');
            $('#discounted_amount').prop('disabled', true);
            return;
        }
        
        $('#discounted_amount').prop('disabled', false);
        
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
                    $('#discounted_amount').val(response.monthly_fee);
                    calculateFees();
                } else {
                    toastr.error(response.msg || 'Fee data not found');
                }
            },
            error: function() {
                toastr.error('Failed to fetch class fee.');
            }
        });
    });

    // Update fees when values change
    $('#discounted_amount, #transport_fee').on('input', calculateFees);
    
    // Initialize with fees calculated
    calculateFees();

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
                        toastr.success(response.msg);
                        setTimeout(function() {
                            location.reload();
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