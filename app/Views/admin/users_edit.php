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
$salary = $emp_salary_info->salary ?? '';
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
    /* Tab styling */
    .nav-tabs-custom {
        margin-bottom: 20px;
        background: #fff;
        border-radius: 4px;
        box-shadow: 0 1px 1px rgba(0,0,0,0.05);
    }
    .nav-tabs-custom .nav-tabs {
        border-bottom: 1px solid #ddd;
        background: #f5f5f5;
        border-radius: 4px 4px 0 0;
    }
    .nav-tabs-custom .nav-tabs li a {
        padding: 10px 15px;
        color: #555;
        border-radius: 0;
    }
    .nav-tabs-custom .nav-tabs li.active a {
        border-top: 2px solid #3c8dbc;
        color: #3c8dbc;
        background: #fff;
    }
    .nav-tabs-custom .tab-content {
        padding: 20px;
        background: #fff;
        border-radius: 0 0 4px 4px;
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
        border-left: 3px solid #4caf50;
    }
    .subject-card.assigned-to-other {
        background: #fff3e0;
        border-left: 3px solid #ff9800;
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
    .custom-control-input:checked ~ .custom-control-label::before {
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
        border-left: 2px dashed #dee2e6;
        margin-left: 15px;
        padding-left: 15px;
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
    border-left: 3px solid #4caf50;
}
.class-card.assigned-to-other {
    background: #fff3e0;
    border-left: 3px solid #ff9800;
}
.class-info {
    flex: 1;
}
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><?= esc($header) ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/users') ?>">Employees</a></li>
                    <li class="breadcrumb-item active"><?= esc($header) ?></li>
                </ol>
            </div>
        </div>
    </div>
</section>

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
                            <b>Username</b> <span class="float-right"><?= esc($username) ?></span>
                        </li>
                        <li class="list-group-item">
                            <b>Email</b> <span class="float-right"><?= esc($email) ?></span>
                        </li>
                        <li class="list-group-item">
                            <b>Mobile</b> <span class="float-right"><?= esc($mobile_no) ?></span>
                        </li>
                        <li class="list-group-item">
                            <b>Status</b>
                            <span class="float-right">
                                <?php if ($status == 1): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Inactive</span>
                                <?php endif; ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <?= form_open_multipart(base_url('admin/users/save'), ['id' => 'user-edit-form', 'autocomplete' => 'off']) ?>
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= esc($id) ?>">
            <input type="hidden" id="originalusername" value="<?= esc($username) ?>">
            <input type="hidden" id="originalemail" value="<?= esc($email) ?>">
            
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#tab_basic" data-toggle="tab">Basic Info</a></li>
                    <li><a href="#tab_contact" data-toggle="tab">Contact & Bank</a></li>
                    <li><a href="#tab_employment" data-toggle="tab">Employment</a></li>
                    <li><a href="#tab_roles" data-toggle="tab">Roles & Permissions</a></li>
                    <li><a href="#tab_subjects" data-toggle="tab">Subject Assignments</a></li>
                    <?php if (!empty($availableClasses)): ?>
                    <li><a href="#tab_classes" data-toggle="tab">Class Teacher</a></li>
                    <?php endif; ?>
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
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" id="email" value="<?= esc($email) ?>" required>
                                </div>
                            </div>
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
                                                    <span class="badge badge-danger float-right">Cannot Assign</span>
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
                                                        <label for="role_<?= $role->id ?>" class="mb-0 ml-2">
                                                            <?= esc($role->rolename) ?>
                                                            <?php if ($role->issys == 1): ?>
                                                                <span class="badge badge-warning ml-1">System</span>
                                                            <?php endif; ?>
                                                        </label>
                                                    </div>
                                                    <?php if (!empty($role->detail)): ?>
                                                        <small class="text-muted ml-4 d-block"><?= esc($role->detail) ?></small>
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
                                                            <span class="badge badge-primary m-1 p-2">
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
                    
                    <!-- TAB 5: Subject Assignments -->
                    <div class="tab-pane" id="tab_subjects">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Subject Assignment:</strong> Toggle the switch to assign/unassign subjects.
                            <span class="badge badge-success">Green border</span> = Currently assigned to this teacher,
                            <span class="badge badge-warning">Orange border</span> = Assigned to another teacher
                        </div>
                        
                        <div class="form-group">
                            <div class="input-group">
                                <input type="text" id="subjectSearch" class="form-control" placeholder="Search subjects...">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                            </div>
                        </div>
                        
                        <div id="subjectsList" class="row" data-teacher-id="<?= $id ?>">
                            <div class="col-12 text-center">
                                <div class="loading-spinner"></div> Loading subjects...
                            </div>
                        </div>
                    </div>
                  <!-- TAB 6: Class Teacher Assignments with Toggle Buttons -->
<?php if (!empty($availableClasses)): ?>
<div class="tab-pane" id="tab_classes">
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        <strong>Class Teacher Assignment:</strong> Toggle the switch to assign/unassign as class teacher.
        <span class="badge badge-success">Green border</span> = Currently assigned to this teacher,
        <span class="badge badge-warning">Orange border</span> = Assigned to another teacher
    </div>
    
    <div class="form-group">
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
            </div>
            <input type="text" id="classSearch" class="form-control" placeholder="Search classes by name or section...">
        </div>
    </div>
    
    <div id="classesList" class="row" data-teacher-id="<?= $id ?>">
        <div class="col-12 text-center">
            <div class="loading-spinner"></div> Loading classes...
        </div>
    </div>
</div>
<?php endif; ?>
            
            <div class="form-group text-right">
                <button type="submit" id="submitBtn" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Save Employee
                </button>
                <a href="<?= base_url('admin/users') ?>" class="btn btn-default btn-lg">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
            
            <?= form_close() ?>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {


    // Load classes via AJAX for class teacher assignment
function loadClasses() {
    var teacherId = $('#classesList').data('teacher-id');
    if (!teacherId) {
        $('#classesList').html('<div class="col-12 text-center text-muted">No teacher selected</div>');
        return;
    }
    
    $.ajax({
        url: '<?= base_url("admin/users/get-teacher-classes/") ?>' + teacherId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            displayClasses(response);
        },
        error: function() {
            $('#classesList').html('<div class="col-12 text-center text-danger">Error loading classes</div>');
        }
    });
}

function displayClasses(classes) {
    if (!classes.length) {
        $('#classesList').html('<div class="col-12 text-center text-muted">No classes available</div>');
        return;
    }
    
    var html = '<div class="col-12">';
    html += '<div class="row">';
    
    classes.forEach(function(classItem) {
        var cardClass = 'class-card';
        if (classItem.is_selected) {
            cardClass += ' assigned';
        } else if (classItem.assigned_teacher_id && classItem.assigned_teacher_id != $('#classesList').data('teacher-id')) {
            cardClass += ' assigned-to-other';
        }
        
        html += '<div class="col-md-6">';
        html += '<div class="' + cardClass + '" data-cls-sec-id="' + classItem.cls_sec_id + '">';
        html += '<div class="d-flex justify-content-between align-items-center">';
        html += '<div class="class-info">';
        html += '<strong><i class="fas fa-chalkboard-teacher"></i> ' + escapeHtml(classItem.class_name) + '</strong>';
        html += '<br><small class="text-muted">Section: ' + escapeHtml(classItem.section_name) + '</small>';
        if (classItem.is_selected) {
            html += ' <span class="badge-assigned"><i class="fas fa-check"></i> Current Class Teacher</span>';
        } else if (classItem.assigned_teacher_name) {
            html += ' <span class="badge-other"><i class="fas fa-user"></i> ' + escapeHtml(classItem.assigned_teacher_name) + '</span>';
        }
        html += '</div>';
        html += '<div class="custom-control custom-switch">';
        html += '<input type="checkbox" class="custom-control-input class-toggle" id="class_' + classItem.cls_sec_id + '"';
        html += classItem.is_selected ? ' checked' : '';
        html += ' data-cls-sec-id="' + classItem.cls_sec_id + '">';
        html += '<label class="custom-control-label" for="class_' + classItem.cls_sec_id + '"></label>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
    });
    
    html += '</div>';
    html += '</div>';
    
    $('#classesList').html(html);
    
    // Bind toggle events for class teacher
    $('.class-toggle').on('change', function() {
        var $toggle = $(this);
        var clsSecId = $toggle.data('cls-sec-id');
        var teacherId = $('#classesList').data('teacher-id');
        var isChecked = $toggle.is(':checked');
        var action = isChecked ? 'assign' : 'unassign';
        
        $toggle.prop('disabled', true);
        var $card = $toggle.closest('.class-card');
        $card.css('opacity', '0.6');
        
        $.ajax({
            url: '<?= base_url("admin/users/assign-class-teacher") ?>',
            type: 'POST',
            data: {
                teacher_id: teacherId,
                cls_sec_id: clsSecId,
                action: action,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(isChecked ? 'Class Teacher assigned successfully' : 'Class Teacher unassigned successfully');
                    // Reload classes to update status
                    loadClasses();
                } else {
                    toastr.error(response.msg || 'Operation failed');
                    $toggle.prop('checked', !isChecked);
                }
            },
            error: function() {
                toastr.error('Error processing request');
                $toggle.prop('checked', !isChecked);
            },
            complete: function() {
                $toggle.prop('disabled', false);
                $card.css('opacity', '1');
            }
        });
    });
}

// Search filter for classes
$('#classSearch').on('keyup', function() {
    var searchTerm = $(this).val().toLowerCase();
    $('.class-card').each(function() {
        var text = $(this).text().toLowerCase();
        $(this).toggle(text.indexOf(searchTerm) > -1);
    });
});

// Load classes on page load
if ($('#classesList').length && $('#classesList').data('teacher-id')) {
    loadClasses();
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
        
        // Group by class
        var grouped = {};
        subjects.forEach(function(subject) {
            var classKey = subject.class_name + ' - ' + subject.section_name;
            if (!grouped[classKey]) grouped[classKey] = [];
            grouped[classKey].push(subject);
        });
        
        var html = '';
        for (var className in grouped) {
            html += '<div class="col-12 mb-3">';
            html += '<div class="card">';
            html += '<div class="card-header bg-light">';
            html += '<strong><i class="fas fa-chalkboard"></i> ' + className + '</strong>';
            html += '<span class="badge badge-info float-right">' + grouped[className].length + ' subjects</span>';
            html += '</div>';
            html += '<div class="card-body p-2">';
            html += '<div class="row">';
            
            grouped[className].forEach(function(subject) {
                var cardClass = 'subject-card';
                if (subject.is_selected) {
                    cardClass += ' assigned';
                } else if (subject.assigned_teacher_id && subject.assigned_teacher_id != $('#subjectsList').data('teacher-id')) {
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
                html += '<div class="custom-control custom-switch">';
                html += '<input type="checkbox" class="custom-control-input subject-toggle" id="subj_' + subject.sec_sub_id + '"';
                html += subject.is_selected ? ' checked' : '';
                html += ' data-sec-sub-id="' + subject.sec_sub_id + '">';
                html += '<label class="custom-control-label" for="subj_' + subject.sec_sub_id + '"></label>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
            });
            
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
        }
        
        $('#subjectsList').html(html);
        
        // Bind toggle events
        $('.subject-toggle').on('change', function() {
            var $toggle = $(this);
            var secSubId = $toggle.data('sec-sub-id');
            var teacherId = $('#subjectsList').data('teacher-id');
            var isChecked = $toggle.is(':checked');
            var action = isChecked ? 'assign' : 'unassign';
            
            $toggle.prop('disabled', true);
            var $card = $toggle.closest('.subject-card');
            $card.css('opacity', '0.6');
            
            $.ajax({
                url: '<?= base_url("admin/users/assign-subject") ?>',
                type: 'POST',
                data: {
                    teacher_id: teacherId,
                    sec_sub_id: secSubId,
                    action: action,
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success(isChecked ? 'Subject assigned successfully' : 'Subject unassigned successfully');
                        loadSubjects();
                    } else {
                        toastr.error(response.msg || 'Operation failed');
                        $toggle.prop('checked', !isChecked);
                    }
                },
                error: function() {
                    toastr.error('Error processing request');
                    $toggle.prop('checked', !isChecked);
                },
                complete: function() {
                    $toggle.prop('disabled', false);
                    $card.css('opacity', '1');
                }
            });
        });
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
    
    // Search filter for subjects
    $('#subjectSearch').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('.subject-card').each(function() {
            var text = $(this).text().toLowerCase();
            $(this).closest('.col-md-6').toggle(text.indexOf(searchTerm) > -1);
        });
    });
    
    // Update selected classes display
    $('input[name="class_teachers[]"]').on('change', function() {
        var selected = [];
        $('input[name="class_teachers[]"]:checked').each(function() {
            var label = $(this).next('label').text();
            selected.push('<span class="badge badge-warning m-1 p-2">' + label + '</span>');
        });
        $('#selectedClassesList').html(selected.length ? selected.join('') : '<span class="text-muted">No classes selected</span>');
    });
    
    // Update selected roles display
    $('.role-checkbox').on('change', function() {
        var selected = [];
        $('.role-checkbox:checked').each(function() {
            var label = $(this).next('label').text();
            selected.push('<span class="badge badge-primary m-1 p-2">' + label + '</span>');
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