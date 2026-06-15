<?php $uiNeedsDataTables = false; $uiNeedsSummernote = false; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
helper(['form', 'url', 'text', 'profile']);

$infoExists = isset($info) && is_object($info);
$header = $infoExists ? 'Edit Employee' : 'Add Employee';
$today = date('Y-m-d');

// Extract user data
$id = $info->id ?? '';
$username = $info->username ?? '';
$email = $info->email ?? '';
$status = isset($info->status) ? (int)$info->status : 1;
$first_name = $info->first_name ?? '';
$last_name = $info->last_name ?? '';
$cnic = $info->cnic ?? '';
$f_name = $info->f_name ?? '';
$dob = $info->dob ?? '';
$gender = $info->gender ?? '';
$marital_status = $info->marital_status ?? '';
$joining_date = $info->joining_date ?? $today;
$mobile_no = $info->mobile_no ?? '';
$mobile_no2 = $info->mobile_no2 ?? '';
$address = $info->address ?? '';
$emergency_contact_person = $info->emergency_contact_person ?? '';
$emergency_contact_no = $info->emergency_contact_no ?? '';
$qualification = $info->qualification ?? '';
$experience = $info->experience ?? '';
$skills = $info->skills ?? '';
$contract_start = $info->contract_start ?? '';
$contract_end = $info->contract_end ?? '';
$salary = $info->basic_salary ?? '';
$designation = $info->designation ?? '';
$bank_name = $info->bank_name ?? '';
$account_title = $info->account_title ?? '';
$branch_code = $info->branch_code ?? '';
$account_number = $info->account_number ?? '';
$bank_address = $info->bank_address ?? '';
$photo = $info->photo ?? '';

// Subject and class data from controller
$availableSubjects = $availableSubjects ?? [];
$selectedSubjects = $selectedSubjects ?? [];
$availableClasses = $availableClasses ?? [];
$selectedClassTeachers = $selectedClassTeachers ?? [];

// Role data from controller
$assignableRoles = $assignableRoles ?? [];
$selectedRoleIds = $selectedRoleIds ?? [];
$selectedRoleDetails = $selectedRoleDetails ?? [];
$currentUserLevel = $currentUserLevel ?? 999;
$currentUserRoleName = $currentUserRoleName ?? 'Unknown';
$canAssignMultipleRoles = $canAssignMultipleRoles ?? false;
$requireOldPasswordForPasswordChange = $requireOldPasswordForPasswordChange ?? true;
$levelNames = $levelNames ?? [
    1 => '🔹 Super Admin',
    2 => '🔸 Administrator',
    3 => '📋 Manager/Coordinator',
    4 => '📚 Teacher/Faculty',
    5 => '👥 Staff/Assistant',
    6 => '🔰 Support Staff',
    999 => '📌 Custom Role'
];

// Group roles by level
$rolesByLevel = [];
foreach ($assignableRoles as $role) {
    $level = $role->level ?? 6;
    if (!isset($rolesByLevel[$level])) {
        $rolesByLevel[$level] = [];
    }
    $rolesByLevel[$level][] = $role;
}
ksort($rolesByLevel);
?>

<style>
    /* Employee edit tabs */
    .nav-tabs-custom {
        margin-bottom: 20px;
        background: #fff;
        border: 1px solid #dbe4ee;
        border-radius: 8px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }
    .nav-tabs-custom .nav-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        padding: 0.75rem;
        border-bottom: 1px solid #dbe4ee;
        background: #f8fafc;
    }
    .nav-tabs-custom .nav-tabs .nav-item {
        margin-bottom: 0;
    }
    .nav-tabs-custom .nav-tabs .nav-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 38px;
        padding: 0.5rem 0.78rem;
        border: 1px solid transparent;
        border-radius: 6px;
        color: #475569;
        background: transparent;
        font-size: 0.86rem;
        font-weight: 700;
        line-height: 1.2;
        text-decoration: none;
        white-space: nowrap;
        transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
    }
    .nav-tabs-custom .nav-tabs .nav-link:hover,
    .nav-tabs-custom .nav-tabs .nav-link:focus {
        background: #eef4fa;
        border-color: #c9d6e4;
        color: #1f5f8b;
        text-decoration: none;
    }
    .nav-tabs-custom .nav-tabs .nav-link.active {
        background: #ffffff;
        border-color: #3c8dbc;
        color: #1f5f8b;
        box-shadow: 0 4px 12px rgba(60, 141, 188, 0.14);
    }
    .nav-tabs-custom .nav-tabs .nav-link.active::before {
        content: "";
        width: 0.45rem;
        height: 0.45rem;
        margin-right: 0.45rem;
        border-radius: 999px;
        background: #3c8dbc;
    }
    .nav-tabs-custom .tab-content {
        padding: 1.25rem;
        background: #fff;
    }
    @media (max-width: 991.98px) {
        .nav-tabs-custom .nav-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    }
    
    /* Subject card styling */
    .subject-card {
        background: #f9f9f9;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 10px;
        transition: all 0.2s;
    }
    .subject-card:hover {
        background: #f0f0f0;
    }
    .subject-card.assigned {
        background: #e8f5e9;
        border-start: 3px solid #4caf50;
    }
    .subject-card.assigned-to-other {
        background: #fff3e0;
        border-start: 3px solid #ff9800;
    }
    .badge-assigned {
        background: #4caf50;
        color: white;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 10px;
    }
    .badge-other {
        background: #ff9800;
        color: white;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 10px;
    }
    .section-class-teacher-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        margin-bottom: 10px;
        background: #eef6ff;
        border: 1px solid #cfe2ff;
        border-radius: 8px;
    }
    .section-class-teacher-row.assigned {
        background: #e8f5e9;
        border-color: #a5d6a7;
    }
    .section-class-teacher-row.assigned-to-other {
        background: #fff3e0;
        border-color: #ffcc80;
    }
    #assignmentSaveStatus.is-saving { color: #007bff; }
    #assignmentSaveStatus.is-saved { color: #28a745; }
    #assignmentSaveStatus.is-error { color: #dc3545; }
    
    /* Loading spinner */
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #3c8dbc;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .bg-success-light {
        background-color: #e8f5e9 !important;
    }
    .form-check-input:checked ~ .form-check-label::before {
        background-color: #28a745;
        border-color: #28a745;
    }
    
    /* Role option styling */
    .role-option {
        padding: 8px 12px;
        margin-bottom: 5px;
        border-radius: 5px;
        transition: all 0.2s;
        border: 1px solid #e0e0e0;
    }
    .role-option.selected {
        background-color: #e3f2fd;
        border-color: #2196f3;
    }
    .role-selector-container {
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 15px;
    }
    .level-header {
        font-size: 14px;
        margin-bottom: 10px;
        padding: 8px 12px;
        background: #f8f9fa;
        border-radius: 4px;
    }
    .level-roles {
        border-start: 2px dashed #dee2e6;
        margin-left: 15px;
        padding-left: 15px;
    }
    .availability-feedback {
        display: block;
        min-height: 18px;
        margin-top: 4px;
        font-size: 12px;
    }
    .availability-feedback.text-success,
    .availability-feedback.text-danger,
    .availability-feedback.text-muted {
        font-weight: 600;
    }


    /* Class card styling */
.class-card {
    background: #f9f9f9;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 10px;
    transition: all 0.2s;
    border: 1px solid #e0e0e0;
}
.class-card:hover {
    background: #f0f0f0;
}
.class-card.assigned {
    background: #e8f5e9;
    border-start: 3px solid #4caf50;
}
.class-card.assigned-to-other {
    background: #fff3e0;
    border-start: 3px solid #ff9800;
}
.class-info {
    flex: 1;
}
</style>

<?= view('components/page_header', [
    'title' => $header,
    'icon' => 'fas fa-user-edit',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Employees', 'url' => base_url('admin/users')],
        ['label' => $header, 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="row">
        <div class="col-md-3">
            <!-- Profile Image -->
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <?php if (!empty($photo)): ?>
                            <img class="profile-user-img img-fluid img-circle"
                                 src="<?= base_url('uploads/employees/' . $photo) ?>"
                                 alt="User profile picture"
                                 style="width: 100px; height: 100px; object-fit: cover;">
                        <?php else: ?>
                            <img class="profile-user-img img-fluid img-circle"
                                 src="<?= base_url('resource/adminlte/dist/img/avatar.png') ?>"
                                 alt="User profile picture">
                        <?php endif; ?>
                    </div>
                    
                    <h3 class="profile-username text-center"><?= esc($first_name . ' ' . $last_name) ?></h3>
                    <p class="text-muted text-center"><?= esc($designation) ?></p>
                    
                    <div class="text-center mt-3">
                        <label class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-camera"></i> Change Photo
                            <input type="file" name="image" id="profile_image" accept="image/*" style="display: none;">
                        </label>
                    </div>
                    <div class="text-center mt-2">
                        <small class="text-muted">Max size: 2MB | JPG, PNG only</small>
                    </div>
                    <?php if (!empty($photo)): ?>
                        <input type="hidden" name="existing_photo" value="<?= esc($photo) ?>">
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Info Card -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Quick Info</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Username</b> <span class="float-end"><?= esc($username) ?></span>
                        </li>
                        <li class="list-group-item">
                            <b>Email</b> <span class="float-end"><?= esc($email) ?></span>
                        </li>
                        <li class="list-group-item">
                            <b>Mobile</b> <span class="float-end"><?= esc($mobile_no) ?></span>
                        </li>
                        <li class="list-group-item">
                            <b>Status</b>
                            <span class="float-end">
                                <?php if ($status == 1): ?>
                                    <span class="badge text-bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge text-bg-danger">Inactive</span>
                                <?php endif; ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <?= form_open_multipart(base_url('admin/users/save'), ['id' => 'user-edit-form', 'class' => 'needs-validation', 'novalidate' => 'novalidate', 'autocomplete' => 'off']) ?>
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= esc($id) ?>">
            <input type="hidden" id="originalusername" value="<?= esc($username) ?>">
            <input type="hidden" id="originalemail" value="<?= esc($email) ?>">
            
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" href="#tab_basic" data-bs-toggle="tab" role="tab" aria-controls="tab_basic" aria-selected="true">Basic Info</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" href="#tab_contact" data-bs-toggle="tab" role="tab" aria-controls="tab_contact" aria-selected="false">Contact &amp; Bank</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" href="#tab_employment" data-bs-toggle="tab" role="tab" aria-controls="tab_employment" aria-selected="false">Employment</a>
                    </li>
                    <?php if (!empty($id)): ?>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" href="#tab_password" data-bs-toggle="tab" role="tab" aria-controls="tab_password" aria-selected="false">Password</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" href="#tab_roles" data-bs-toggle="tab" role="tab" aria-controls="tab_roles" aria-selected="false">Roles &amp; Permissions</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" href="#tab_subjects" data-bs-toggle="tab" role="tab" aria-controls="tab_subjects" aria-selected="false">Subject &amp; Class Assignments</a>
                    </li>
                </ul>
                
                <div class="tab-content">
                    <!-- TAB 1: Basic Information -->
                    <div class="tab-pane active" id="tab_basic">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="first_name" value="<?= esc($first_name) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input type="text" class="form-control" name="last_name" value="<?= esc($last_name) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="username" id="username" value="<?= esc($username) ?>" required>
                                    <small id="usernameAvailability" class="availability-feedback text-muted"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" id="email" value="<?= esc($email) ?>" required>
                                    <small id="emailAvailability" class="availability-feedback text-muted"></small>
                                </div>
                            </div>
                            <?php if (empty($id)): ?>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="password" id="password" minlength="6" autocomplete="new-password" required>
                                    <small class="text-muted">Minimum 6 characters</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="confirm_password" id="confirm_password" minlength="6" autocomplete="new-password" required>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Designation</label>
                                    <input type="text" class="form-control" name="designation" value="<?= esc($designation) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>CNIC <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="cnic" value="<?= esc($cnic) ?>" data-inputmask='"mask": "99999-9999999-9"' data-mask required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Father Name</label>
                                    <input type="text" class="form-control" name="f_name" value="<?= esc($f_name) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date of Birth</label>
                                    <input type="date" class="form-control" name="dob" value="<?= esc($dob) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Gender</label>
                                    <select class="form-control select2" name="gender" style="width: 100%;">
                                        <option value="">Select</option>
                                        <option value="male" <?= $gender == 'male' ? 'selected' : '' ?>>Male</option>
                                        <option value="female" <?= $gender == 'female' ? 'selected' : '' ?>>Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Marital Status</label>
                                    <select class="form-control select2" name="marital_status" style="width: 100%;">
                                        <option value="">Select</option>
                                        <option value="single" <?= $marital_status == 'single' ? 'selected' : '' ?>>Single</option>
                                        <option value="married" <?= $marital_status == 'married' ? 'selected' : '' ?>>Married</option>
                                        <option value="divorced" <?= $marital_status == 'divorced' ? 'selected' : '' ?>>Divorced</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Qualification</label>
                                    <input type="text" class="form-control" name="qualification" value="<?= esc($qualification) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Experience (years)</label>
                                    <input type="text" class="form-control" name="experience" value="<?= esc($experience) ?>">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Skills (comma separated)</label>
                                    <input type="text" class="form-control" name="skills" value="<?= esc($skills) ?>" placeholder="e.g., PHP, MySQL, JavaScript">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Address</label>
                                    <textarea class="form-control" name="address" rows="2"><?= esc($address) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- TAB 2: Contact & Bank Details -->
                    <div class="tab-pane" id="tab_contact">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Mobile No</label>
                                    <input type="text" class="form-control" name="mobile_no" value="<?= esc($mobile_no) ?>" data-inputmask='"mask": "0399-9999999"' data-mask>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Alternate Mobile</label>
                                    <input type="text" class="form-control" name="mobile_no2" value="<?= esc($mobile_no2) ?>" data-inputmask='"mask": "0399-9999999"' data-mask>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Emergency Contact Person</label>
                                    <input type="text" class="form-control" name="emergency_contact_person" value="<?= esc($emergency_contact_person) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Emergency Contact No</label>
                                    <input type="text" class="form-control" name="emergency_contact_no" value="<?= esc($emergency_contact_no) ?>" data-inputmask='"mask": "0399-9999999"' data-mask>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        <h5 class="text-primary"><i class="fas fa-university"></i> Bank Account Details</h5>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Bank Name</label>
                                    <input type="text" class="form-control" name="bank_name" value="<?= esc($bank_name) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Account Title</label>
                                    <input type="text" class="form-control" name="account_title" value="<?= esc($account_title) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Account Number</label>
                                    <input type="text" class="form-control" name="account_number" value="<?= esc($account_number) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Branch Code</label>
                                    <input type="text" class="form-control" name="branch_code" value="<?= esc($branch_code) ?>">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Bank Address</label>
                                    <input type="text" class="form-control" name="bank_address" value="<?= esc($bank_address) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- TAB 3: Employment Information -->
                    <div class="tab-pane" id="tab_employment">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Joining Date</label>
                                    <input type="date" class="form-control" name="joining_date" value="<?= esc($joining_date) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Basic Salary (PKR)</label>
                                    <input type="number" step="0.01" class="form-control" name="salary" value="<?= esc($salary) ?>" placeholder="Enter basic salary">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Contract Type</label>
                                    <select class="form-control" name="contract_type">
                                        <option value="permanent" <?= ($info->contract_type ?? '') == 'permanent' ? 'selected' : '' ?>>Permanent</option>
                                        <option value="contract" <?= ($info->contract_type ?? '') == 'contract' ? 'selected' : '' ?>>Contract</option>
                                        <option value="probation" <?= ($info->contract_type ?? '') == 'probation' ? 'selected' : '' ?>>Probation</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Payment Method</label>
                                    <select class="form-control" name="salary_payment_method">
                                        <option value="bank" <?= ($info->salary_payment_method ?? '') == 'bank' ? 'selected' : '' ?>>Bank Transfer</option>
                                        <option value="cash" <?= ($info->salary_payment_method ?? '') == 'cash' ? 'selected' : '' ?>>Cash</option>
                                        <option value="cheque" <?= ($info->salary_payment_method ?? '') == 'cheque' ? 'selected' : '' ?>>Cheque</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Contract Start Date</label>
                                    <input type="date" class="form-control" name="contract_start" value="<?= esc($contract_start) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Contract End Date</label>
                                    <input type="date" class="form-control" name="contract_end" value="<?= esc($contract_end) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- TAB 4: Password -->
                    <?php if (!empty($id)): ?>
                    <div class="tab-pane" id="tab_password">
                            <?php if ($requireOldPasswordForPasswordChange): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-shield-alt"></i>
                                    For your role, current password is required to change password.
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-user-shield"></i>
                                    You can update this employee password without old password.
                                </div>
                            <?php endif; ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Your Current Password <?= $requireOldPasswordForPasswordChange ? '<span class="text-danger">*</span>' : '(Optional)' ?></label>
                                        <input type="password" class="form-control" name="current_password" id="current_password" autocomplete="new-password">
                                    </div>
                                </div>
                                <div class="col-md-6"></div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>New Password</label>
                                        <input type="password" class="form-control" name="new_password" id="new_password" minlength="6" autocomplete="new-password">
                                        <small class="text-muted">Minimum 6 characters</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Confirm New Password</label>
                                        <input type="password" class="form-control" name="confirm_new_password" id="confirm_new_password" minlength="6" autocomplete="new-password">
                                    </div>
                                </div>
                            </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- TAB 4: Role Selection -->
                    <div class="tab-pane" id="tab_roles">
                        <?php if (!empty($assignableRoles)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Your Role Level:</strong> <?= $levelNames[$currentUserLevel] ?? 'Level ' . $currentUserLevel ?>
                                <br>
                                <small>You can assign roles at or below your level</small>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="role-selector-container">
                                        <?php foreach ($rolesByLevel as $level => $roles): 
                                            $levelName = $levelNames[$level] ?? 'Level ' . $level;
                                            $isAssignable = ($level >= $currentUserLevel);
                                        ?>
                                        <div class="level-group mb-3">
                                            <h6 class="level-header">
                                                <?= $levelName ?>
                                                <?php if (!$isAssignable): ?>
                                                    <span class="badge text-bg-danger float-end">Cannot Assign</span>
                                                <?php endif; ?>
                                            </h6>
                                            <div class="level-roles">
                                                <?php foreach ($roles as $role): 
                                                    $isSelected = in_array($role->id, $selectedRoleIds);
                                                ?>
                                                <div class="role-option <?= $isSelected ? 'selected' : '' ?>">
                                                    <div class="d-flex align-items-center">
                                                        <input type="checkbox" 
                                                               name="role_ids[]" 
                                                               value="<?= $role->id ?>"
                                                               id="role_<?= $role->id ?>"
                                                               class="role-checkbox"
                                                               <?= $isSelected ? 'checked' : '' ?>
                                                               <?= !$isAssignable ? 'disabled' : '' ?>>
                                                        <label for="role_<?= $role->id ?>" class="mb-0 ms-2">
                                                            <?= esc($role->rolename) ?>
                                                            <?php if ($role->issys == 1): ?>
                                                                <span class="badge text-bg-warning ms-1">System</span>
                                                            <?php endif; ?>
                                                        </label>
                                                    </div>
                                                    <?php if (!empty($role->detail)): ?>
                                                        <small class="text-muted ms-4 d-block"><?= esc($role->detail) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card card-primary">
                                        <div class="card-header">
                                            <h6 class="card-title">Selected Roles</h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="selectedRolesList">
                                                <?php if (!empty($selectedRoleIds)): ?>
                                                    <?php foreach ($selectedRoleIds as $rid): ?>
                                                        <?php if (isset($selectedRoleDetails[$rid])): ?>
                                                            <span class="badge text-bg-primary m-1 p-2">
                                                                <?= esc($selectedRoleDetails[$rid]['name']) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">No roles selected</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">No assignable roles found for your plan.</div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- TAB 5: Subject & Class Teacher Assignments -->
                    <div class="tab-pane" id="tab_subjects">
                        <div class="alert alert-info mb-2">
                            <i class="fas fa-info-circle"></i>
                            Toggle switches to assign subjects or class teacher. Changes save automatically — no Save button needed on this tab.
                            <span class="badge text-bg-success">Green</span> = assigned to this teacher,
                            <span class="badge text-bg-warning">Orange</span> = assigned to someone else.
                        </div>
                        
                        <div class="form-group">
                            <div class="input-group">
                                <input type="text" id="subjectSearch" class="form-control" placeholder="Search class, section, or subject...">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                        </div>

                        <div class="mb-2">
                            <small id="assignmentSaveStatus" class="text-muted"></small>
                        </div>
                        
                        <div id="subjectsList" class="row" data-teacher-id="<?= $id ?>">
                            <div class="col-12 text-center">
                                <div class="loading-spinner"></div> Loading assignments...
                            </div>
                        </div>
                    </div>
            
            <div class="form-group text-end">
                <button type="submit" id="submitBtn" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Save Employee
                </button>
                <a href="<?= base_url('admin/users') ?>" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
            
            <?= form_close() ?>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    const requireOldPasswordForPasswordChange = <?= $requireOldPasswordForPasswordChange ? 'true' : 'false' ?>;
    const availabilityUrl = '<?= base_url('admin/users/check-availability') ?>';
    const availabilityState = {
        username: null,
        email: null
    };
    const availabilityTimers = {};

    function setAvailabilityStatus(field, statusClass, message) {
        $('#' + field + 'Availability')
            .removeClass('text-muted text-success text-danger')
            .addClass(statusClass)
            .text(message);
    }

    function checkAvailability(field) {
        const input = $('#' + field);
        const value = $.trim(input.val());

        availabilityState[field] = null;

        if (!value) {
            setAvailabilityStatus(field, 'text-muted', '');
            return;
        }

        setAvailabilityStatus(field, 'text-muted', 'Checking...');

        $.ajax({
            url: availabilityUrl,
            type: 'GET',
            dataType: 'json',
            data: {
                field: field,
                value: value,
                id: $('input[name="id"]').val()
            },
            success: function(response) {
                availabilityState[field] = !!(response && response.available);
                setAvailabilityStatus(
                    field,
                    availabilityState[field] ? 'text-success' : 'text-danger',
                    response && response.msg ? response.msg : ''
                );
            },
            error: function() {
                availabilityState[field] = null;
                setAvailabilityStatus(field, 'text-danger', 'Could not check availability');
            }
        });
    }

    $('#username, #email').on('input blur', function() {
        const field = this.id;
        clearTimeout(availabilityTimers[field]);
        availabilityTimers[field] = setTimeout(function() {
            checkAvailability(field);
        }, 350);
    });


    function getCsrfData() {
        var tokenName = '<?= csrf_token() ?>';
        var data = {};
        data[tokenName] = $('input[name="' + tokenName + '"]').val() || '';
        return data;
    }

    function setAssignmentSaveStatus(state, message) {
        var $el = $('#assignmentSaveStatus');
        $el.removeClass('is-saving is-saved is-error');
        if (state) {
            $el.addClass('is-' + state);
        }
        $el.text(message || '');
    }

    function postAssignment(url, payload, onSuccess, onFail) {
        var data = $.extend({}, payload, getCsrfData());
        setAssignmentSaveStatus('saving', 'Saving...');
        return $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response && response.success) {
                    setAssignmentSaveStatus('saved', 'Saved');
                    if (typeof onSuccess === 'function') {
                        onSuccess(response);
                    }
                } else {
                    setAssignmentSaveStatus('error', 'Save failed');
                    toastr.error((response && response.msg) ? response.msg : 'Operation failed');
                    if (typeof onFail === 'function') {
                        onFail(response);
                    }
                }
            },
            error: function() {
                setAssignmentSaveStatus('error', 'Save failed');
                toastr.error('Error processing request');
                if (typeof onFail === 'function') {
                    onFail();
                }
            }
        });
    }

    // Initialize select2
    $('.select2').select2({ width: '100%' });
    $('[data-mask]').inputmask();
    
    // Profile image upload preview
    $('#profile_image').on('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('.profile-user-img').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
            
            // Create a new file input for form submission
            var dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            $('#profile_image')[0].files = dataTransfer.files;
        }
    });
    
    // Load subjects via AJAX
    function loadSubjects() {
        var teacherId = $('#subjectsList').data('teacher-id');
        if (!teacherId) {
            $('#subjectsList').html('<div class="col-12 text-center text-muted">No teacher selected</div>');
            return;
        }
        
        $.ajax({
            url: '<?= base_url("admin/users/get-teacher-subjects/") ?>' + teacherId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                displaySubjects(response);
            },
            error: function() {
                $('#subjectsList').html('<div class="col-12 text-center text-danger">Error loading subjects</div>');
            }
        });
    }
    
    function displaySubjects(subjects) {
        if (!subjects.length) {
            $('#subjectsList').html('<div class="col-12 text-center text-muted">No subjects available</div>');
            return;
        }

        var teacherId = parseInt($('#subjectsList').data('teacher-id'), 10);
        var grouped = {};

        subjects.forEach(function(subject) {
            var key = String(subject.cls_sec_id);
            if (!grouped[key]) {
                grouped[key] = {
                    cls_sec_id: subject.cls_sec_id,
                    class_name: subject.class_name,
                    section_name: subject.section_name,
                    is_class_teacher: !!subject.is_class_teacher,
                    section_class_teacher_id: parseInt(subject.section_class_teacher_id || 0, 10),
                    section_class_teacher_name: subject.section_class_teacher_name || '',
                    subjects: []
                };
            }
            grouped[key].subjects.push(subject);
        });

        var html = '';
        Object.keys(grouped).forEach(function(sectionKey) {
            var section = grouped[sectionKey];
            var sectionLabel = escapeHtml(section.class_name) + ' - ' + escapeHtml(section.section_name);
            var sectionSearchText = (section.class_name + ' ' + section.section_name).toLowerCase();

            var classTeacherRowClass = 'section-class-teacher-row';
            if (section.is_class_teacher) {
                classTeacherRowClass += ' assigned';
            } else if (section.section_class_teacher_id && section.section_class_teacher_id !== teacherId) {
                classTeacherRowClass += ' assigned-to-other';
            }

            html += '<div class="col-12 mb-3 assignment-section-group" data-section-search="' + escapeAttr(sectionSearchText) + '">';
            html += '<div class="card">';
            html += '<div class="card-header bg-light">';
            html += '<strong><i class="fas fa-chalkboard"></i> ' + sectionLabel + '</strong>';
            html += '<span class="badge text-bg-info float-end">' + section.subjects.length + ' subjects</span>';
            html += '</div>';
            html += '<div class="card-body p-2">';

            html += '<div class="' + classTeacherRowClass + '">';
            html += '<div>';
            html += '<strong><i class="fas fa-user-tie"></i> Class Teacher</strong>';
            if (section.is_class_teacher) {
                html += ' <span class="badge-assigned"><i class="fas fa-check"></i> This teacher</span>';
            } else if (section.section_class_teacher_name) {
                html += ' <span class="badge-other"><i class="fas fa-user"></i> ' + escapeHtml(section.section_class_teacher_name) + '</span>';
            }
            html += '</div>';
            html += '<div class="form-check form-switch">';
            html += '<input type="checkbox" class="form-check-input class-teacher-toggle" id="class_teacher_' + section.cls_sec_id + '"';
            html += section.is_class_teacher ? ' checked' : '';
            html += ' data-cls-sec-id="' + section.cls_sec_id + '">';
            html += '<label class="form-check-label" for="class_teacher_' + section.cls_sec_id + '"></label>';
            html += '</div>';
            html += '</div>';

            html += '<div class="row">';
            section.subjects.forEach(function(subject) {
                var cardClass = 'subject-card';
                if (subject.is_selected) {
                    cardClass += ' assigned';
                } else if (subject.assigned_teacher_id && subject.assigned_teacher_id !== teacherId) {
                    cardClass += ' assigned-to-other';
                }

                html += '<div class="col-md-6">';
                html += '<div class="' + cardClass + '" data-sec-sub-id="' + subject.sec_sub_id + '">';
                html += '<div class="d-flex justify-content-between align-items-center">';
                html += '<div>';
                html += '<strong>' + escapeHtml(subject.subject_name) + '</strong>';
                if (subject.is_selected) {
                    html += ' <span class="badge-assigned"><i class="fas fa-check"></i> Assigned</span>';
                } else if (subject.assigned_teacher_name) {
                    html += ' <span class="badge-other"><i class="fas fa-user"></i> ' + escapeHtml(subject.assigned_teacher_name) + '</span>';
                }
                html += '</div>';
                html += '<div class="form-check form-switch">';
                html += '<input type="checkbox" class="form-check-input subject-toggle" id="subj_' + subject.sec_sub_id + '"';
                html += subject.is_selected ? ' checked' : '';
                html += ' data-sec-sub-id="' + subject.sec_sub_id + '">';
                html += '<label class="form-check-label" for="subj_' + subject.sec_sub_id + '"></label>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
            });
            html += '</div></div></div></div>';
        });

        $('#subjectsList').html(html);

        $('.subject-toggle').on('change', function() {
            var $toggle = $(this);
            var secSubId = $toggle.data('sec-sub-id');
            var isChecked = $toggle.is(':checked');
            var action = isChecked ? 'assign' : 'unassign';

            $toggle.prop('disabled', true);
            var $card = $toggle.closest('.subject-card');
            $card.css('opacity', '0.6');

            postAssignment(
                '<?= base_url("admin/users/assign-subject") ?>',
                {
                    teacher_id: teacherId,
                    sec_sub_id: secSubId,
                    action: action
                },
                function() {
                    loadSubjects();
                },
                function() {
                    $toggle.prop('checked', !isChecked);
                }
            ).always(function() {
                $toggle.prop('disabled', false);
                $card.css('opacity', '1');
            });
        });

        $('.class-teacher-toggle').on('change', function() {
            var $toggle = $(this);
            var clsSecId = $toggle.data('cls-sec-id');
            var isChecked = $toggle.is(':checked');
            var action = isChecked ? 'assign' : 'unassign';

            $toggle.prop('disabled', true);
            var $row = $toggle.closest('.section-class-teacher-row');
            $row.css('opacity', '0.6');

            postAssignment(
                '<?= base_url("admin/users/assign-class-teacher") ?>',
                {
                    teacher_id: teacherId,
                    cls_sec_id: clsSecId,
                    action: action
                },
                function() {
                    loadSubjects();
                },
                function() {
                    $toggle.prop('checked', !isChecked);
                }
            ).always(function() {
                $toggle.prop('disabled', false);
                $row.css('opacity', '1');
            });
        });
    }

    function escapeAttr(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
    
    // Search filter for sections and subjects
    $('#subjectSearch').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('.assignment-section-group').each(function() {
            var $group = $(this);
            var sectionMatch = (($group.attr('data-section-search') || '').indexOf(searchTerm) !== -1);
            var anySubject = false;

            $group.find('.subject-card').each(function() {
                var subjectMatch = ($(this).text().toLowerCase().indexOf(searchTerm) !== -1);
                $(this).closest('.col-md-6').toggle(subjectMatch || sectionMatch);
                if (subjectMatch) {
                    anySubject = true;
                }
            });

            $group.toggle(sectionMatch || anySubject);
        });
    });
    
    // Update selected roles display
    $('.role-checkbox').on('change', function() {
        var selected = [];
        $('.role-checkbox:checked').each(function() {
            var label = $(this).next('label').text();
            selected.push('<span class="badge text-bg-primary m-1 p-2">' + label + '</span>');
        });
        $('#selectedRolesList').html(selected.length ? selected.join('') : '<span class="text-muted">No roles selected</span>');
        
        // Highlight selected role options
        $('.role-option').removeClass('selected');
        $('.role-checkbox:checked').each(function() {
            $(this).closest('.role-option').addClass('selected');
        });
    });
    
    // Load subjects on page load
    if ($('#subjectsList').length && $('#subjectsList').data('teacher-id')) {
        loadSubjects();
    }
    
    // Form validation and submit
    $('#user-edit-form').on('submit', function(e) {
        e.preventDefault();
        
        // Basic validation
        if (!$('input[name="first_name"]').val()) {
            toastr.error('First name is required');
            return false;
        }
        if (!$('input[name="username"]').val()) {
            toastr.error('Username is required');
            return false;
        }
        if (!$('input[name="email"]').val()) {
            toastr.error('Email is required');
            return false;
        }
        if (availabilityState.username === false) {
            toastr.error('Username is already taken');
            $('#username').focus();
            return false;
        }
        if (availabilityState.email === false) {
            toastr.error('Email is already taken or invalid');
            $('#email').focus();
            return false;
        }
        
        var userId = $('input[name="id"]').val();
        if (!userId && $('.role-checkbox:checked').length === 0) {
            toastr.error('Please select at least one role before saving employee');
            $('a[href="#tab_roles"]').tab('show');
            return false;
        }

        if (!userId) {
            var password = $('#password').val();
            var confirmPassword = $('#confirm_password').val();
            if (!password || password.length < 6) {
                toastr.error('Password is required and must be at least 6 characters');
                return false;
            }
            if (password !== confirmPassword) {
                toastr.error('Password and confirm password do not match');
                return false;
            }
        } else {
            var newPassword = $('#new_password').val();
            var confirmNewPassword = $('#confirm_new_password').val();
            var currentPassword = $('#current_password').val();
            if (newPassword || confirmNewPassword) {
                if (requireOldPasswordForPasswordChange && !currentPassword) {
                    toastr.error('Current password is required to change password');
                    return false;
                }
                if (newPassword.length < 6) {
                    toastr.error('New password must be at least 6 characters');
                    return false;
                }
                if (newPassword !== confirmNewPassword) {
                    toastr.error('New password and confirm password do not match');
                    return false;
                }
            }
        }
        
        var formData = new FormData(this);
        
        $('#submitBtn').html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.msg);
                    setTimeout(function() {
                        window.location.href = '<?= base_url("admin/users") ?>';
                    }, 1500);
                } else {
                    toastr.error(response.msg);
                    $('#submitBtn').html('<i class="fas fa-save"></i> Save Employee').prop('disabled', false);
                }
            },
            error: function(xhr) {
                var errorMsg = 'Error saving employee';
                if (xhr.responseJSON && xhr.responseJSON.msg) {
                    errorMsg = xhr.responseJSON.msg;
                }
                toastr.error(errorMsg);
                $('#submitBtn').html('<i class="fas fa-save"></i> Save Employee').prop('disabled', false);
            }
        });
    });
});
</script>

<?= $this->endSection() ?>
