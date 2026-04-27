<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Employee Profile: <?= esc($user->first_name . ' ' . $user->last_name) ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/users') ?>">Employees</a></li>
                    <li class="breadcrumb-item active">Profile</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                      <div class="text-center">
    <?php if (!empty($user->photo)): ?>
        <?php 
        // Use the same logic as controller - CHECK IN uploads/employees/
        $photoUrl = base_url('resource/adminlte/dist/img/emp-avatar.jpg');
        if (!empty($user->photo)) {
            $photoPath = FCPATH . 'uploads/employees/' . $user->photo;
            if (file_exists($photoPath)) {
                $photoUrl = base_url('uploads/employees/' . $user->photo);
            }
        }
        ?>
        <img id="output" class="img-fluid img-circle elevation-2" 
             style="width:140px;height:140px;object-fit:cover;"
             src="<?= $photoUrl ?>" 
             alt="Employee photo">
    <?php else: ?>
        <img class="profile-user-img img-fluid img-circle"
             src="<?= base_url('resource/adminlte/dist/img/user4-128x128.jpg') ?>"
             alt="User profile picture">
    <?php endif; ?>
</div>
                        
                        <h3 class="profile-username text-center"><?= esc($user->first_name . ' ' . $user->last_name) ?></h3>
                        
                        <p class="text-muted text-center"><?= esc($user->designation ?? 'Employee') ?></p>
                        
                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Username</b> <span class="float-right"><?= esc($user->username) ?></span>
                            </li>
                            <li class="list-group-item">
                                <b>Email</b> <span class="float-right"><?= esc($user->email) ?></span>
                            </li>
                            <li class="list-group-item">
                                <b>Mobile</b> <span class="float-right"><?= esc($user->mobile_no) ?></span>
                            </li>
                            <li class="list-group-item">
                                <b>Status</b> 
                                <span class="float-right">
                                    <?php if ($user->status == 1): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </span>
                            </li>
                        </ul>
                        
                        <a href="<?= base_url('admin/users/edit/' . $user->id) ?>" class="btn btn-primary btn-block">
                            <i class="fas fa-edit mr-1"></i> Edit Profile
                        </a>
                        <a href="<?= base_url('admin/users') ?>" class="btn btn-default btn-block mt-2">
                            <i class="fas fa-arrow-left mr-1"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            <li class="nav-item">
                                <a class="nav-link <?= ($activeTab ?? 'profile') == 'profile' ? 'active' : '' ?>" 
                                   href="<?= base_url('admin/users/view/' . $user->id) ?>">
                                    <i class="fas fa-user mr-1"></i> Profile
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= ($activeTab ?? '') == 'subjects' ? 'active' : '' ?>" 
                                   href="<?= base_url('admin/users/subjects/' . $user->id) ?>">
                                    <i class="fas fa-book mr-1"></i> Subjects
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= ($activeTab ?? '') == 'timetable' ? 'active' : '' ?>" 
                                   href="<?= base_url('admin/users/timetable/' . $user->id) ?>">
                                    <i class="fas fa-clock mr-1"></i> Time Table
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= ($activeTab ?? '') == 'salary' ? 'active' : '' ?>" 
                                   href="<?= base_url('admin/users/salary/' . $user->id) ?>">
                                    <i class="fas fa-money-bill mr-1"></i> Salary History
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= ($activeTab ?? '') == 'qr' ? 'active' : '' ?>" 
                                   href="<?= base_url('admin/users/view/' . $user->id . '?tab=qr') ?>">
                                    <i class="fas fa-qrcode mr-1"></i> QR Code
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="card-body">
                        <?php if (($activeTab ?? 'profile') == 'profile'): ?>
                            <!-- Profile Tab Content -->
                            <div class="row">
                                <div class="col-md-6">
                                    <strong><i class="fas fa-id-card mr-1"></i> CNIC</strong>
                                    <p class="text-muted"><?= esc($user->cnic) ?></p>
                                    
                                    <strong><i class="fas fa-user mr-1"></i> Father's Name</strong>
                                    <p class="text-muted"><?= esc($user->f_name) ?></p>
                                    
                                    <strong><i class="fas fa-calendar mr-1"></i> Date of Birth</strong>
                                    <p class="text-muted"><?= esc($user->dob) ?></p>
                                    
                                    <strong><i class="fas fa-venus-mars mr-1"></i> Gender</strong>
                                    <p class="text-muted"><?= ucfirst(esc($user->gender)) ?></p>
                                </div>
                                
                                <div class="col-md-6">
                                    <strong><i class="fas fa-calendar-check mr-1"></i> Joining Date</strong>
                                    <p class="text-muted"><?= esc($user->joining_date) ?></p>
                                    
                                    <strong><i class="fas fa-graduation-cap mr-1"></i> Qualification</strong>
                                    <p class="text-muted"><?= esc($user->qualification) ?></p>
                                    
                                    <strong><i class="fas fa-briefcase mr-1"></i> Experience</strong>
                                    <p class="text-muted"><?= esc($user->experience) ?></p>
                                    
                                    <strong><i class="fas fa-map-marker mr-1"></i> Address</strong>
                                    <p class="text-muted"><?= esc($user->address) ?></p>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h5 class="text-primary">Bank Details</h5>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Bank Name</strong>
                                            <p><?= esc($user->bank_name) ?></p>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Account Title</strong>
                                            <p><?= esc($user->account_title) ?></p>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Account Number</strong>
                                            <p><?= esc($user->account_number) ?></p>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Branch Code</strong>
                                            <p><?= esc($user->branch_code) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        <?php elseif ($activeTab == 'subjects'): ?>
                            <!-- Subjects & Class Teacher Tab Content -->
                            <?php 
                            if (!empty($subjects) || !empty($classTeacherInfo)):
                                // Group subjects by class
                                $groupedSubjects = [];
                                $totalSubjects = 0;
                                $totalClasses = 0;
                                
                                if (!empty($subjects)) {
                                    foreach ($subjects as $subject) {
                                        $classKey = $subject->class_name . ' - ' . $subject->section_name;
                                        if (!isset($groupedSubjects[$classKey])) {
                                            $groupedSubjects[$classKey] = [];
                                        }
                                        $groupedSubjects[$classKey][] = $subject;
                                        $totalSubjects++;
                                    }
                                    $totalClasses = count($groupedSubjects);
                                }
                                
                                // Get class teacher information if not already provided
                                if (!isset($classTeacherInfo)) {
                                    $db = db_connect();
                                    $classTeacherInfo = $db->table('teacher_section ts')
                                        ->select('c.class_name, sec.section_name, ts.created_date, ts.cls_sec_id')
                                        ->join('class_section cs', 'ts.cls_sec_id = cs.cls_sec_id')
                                        ->join('classes c', 'cs.class_id = c.class_id')
                                        ->join('sections sec', 'cs.section_id = sec.section_id')
                                        ->where('ts.tid', $user->id)
                                        ->where('ts.status', 1)
                                        ->get()
                                        ->getResult();
                                }
                                
                                $totalClassTeacher = count($classTeacherInfo ?? []);
                            ?>
                            
                            <!-- Summary Cards -->
                            <div class="row mb-4">
                                <div class="col-lg-3 col-md-6">
                                    <div class="small-box bg-info">
                                        <div class="inner">
                                            <h3><?= $totalSubjects ?></h3>
                                            <p>Total Subjects Taught</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-book"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="small-box bg-success">
                                        <div class="inner">
                                            <h3><?= $totalClasses ?></h3>
                                            <p>Classes Teaching</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-chalkboard-teacher"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="small-box bg-warning">
                                        <div class="inner">
                                            <h3><?= $totalClassTeacher ?></h3>
                                            <p>Class Incharges</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="small-box bg-danger">
                                        <div class="inner">
                                            <h3><?= $totalClasses + $totalClassTeacher ?></h3>
                                            <p>Total Responsibilities</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-tasks"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Class Teacher Section (if any) -->
                            <?php if (!empty($classTeacherInfo)): ?>
                            <div class="card card-outline card-warning mb-4">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-user-tie mr-2"></i>
                                        Class Incharge Assignments 
                                        <span class="badge badge-warning ml-2"><?= count($classTeacherInfo) ?> Class<?= count($classTeacherInfo) > 1 ? 'es' : '' ?></span>
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php foreach ($classTeacherInfo as $index => $classTeacher): ?>
                                        <div class="col-md-4">
                                            <div class="info-box bg-light">
                                                <span class="info-box-icon bg-warning">
                                                    <i class="fas fa-chalkboard"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text"><?= esc($classTeacher->class_name) ?> - <?= esc($classTeacher->section_name) ?></span>
                                                    <span class="info-box-number">
                                                        <small class="text-muted">
                                                            <i class="far fa-calendar-alt mr-1"></i>
                                                            Since <?= date('d M Y', strtotime($classTeacher->created_date)) ?>
                                                        </small>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Subjects Taught Section -->
                            <?php if (!empty($groupedSubjects)): ?>
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-book mr-2"></i>
                                        Subjects Taught 
                                        <span class="badge badge-primary ml-2"><?= $totalSubjects ?> Subjects in <?= $totalClasses ?> Classes</span>
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php foreach ($groupedSubjects as $classSection => $classSubjects): ?>
                                        <div class="col-md-6">
                                            <div class="card card-outline card-primary">
                                                <div class="card-header bg-light">
                                                    <h5 class="card-title">
                                                        <i class="fas fa-chalkboard mr-2 text-primary"></i>
                                                        <?= esc($classSection) ?>
                                                    </h5>
                                                    <div class="card-tools">
                                                        <span class="badge badge-primary">
                                                            <?= count($classSubjects) ?> Subject<?= count($classSubjects) > 1 ? 's' : '' ?>
                                                        </span>
                                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="card-body p-0">
                                                    <table class="table table-hover mb-0">
                                                        <thead class="bg-light">
                                                            <tr>
                                                                <th style="width: 40px">#</th>
                                                                <th>Subject Name</th>
                                                                <th style="width: 120px">Assigned Date</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($classSubjects as $index => $subject): ?>
                                                            <tr>
                                                                <td><?= $index + 1 ?></td>
                                                                <td>
                                                                    <strong><?= esc($subject->subject_name) ?></strong>
                                                                </td>
                                                                <td>
                                                                    <small class="text-muted">
                                                                        <i class="far fa-calendar-alt mr-1"></i>
                                                                        <?= date('d M Y', strtotime($subject->created_date)) ?>
                                                                    </small>
                                                                </td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i> 
                                    No subjects or class incharges assigned to this teacher.
                                </div>
                            <?php endif; ?>
                            
                        <?php elseif ($activeTab == 'qr'): ?>
                            <!-- QR Code Tab Content -->
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-qrcode mr-2"></i>
                                        QR Code for <?= esc($user->first_name . ' ' . $user->last_name) ?>
                                    </h3>
                                    <div class="card-tools">
                                        <?php if (isset($qr) && $qr): ?>
                                            <a href="<?= base_url('admin/qr/view/' . $user->id) ?>" class="btn btn-primary btn-sm" download>
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                            <a href="<?= base_url('admin/qr/print/' . $user->id) ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-print"></i> Print
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= base_url('admin/qr/generate/' . $user->id) ?>" class="btn btn-success btn-sm">
                                                <i class="fas fa-plus-circle"></i> Generate QR Code
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    <?php if (isset($qr) && $qr): ?>
                                        <div class="mb-3">
                                            <img src="<?= base_url('admin/qr/view/' . $user->id) ?>" 
                                                 alt="QR Code for <?= esc($user->first_name . ' ' . $user->last_name) ?>" 
                                                 style="max-width: 300px; border: 1px solid #ddd; padding: 20px; border-radius: 10px;">
                                        </div>
                                        
                                        <div class="row mt-3">
                                            <div class="col-md-6 offset-md-3">
                                                <table class="table table-bordered">
                                                    <tr>
                                                        <th>Employee Name</th>
                                                        <td><?= esc($user->first_name . ' ' . $user->last_name) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Employee ID</th>
                                                        <td><?= esc($user->id) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>QR Code Value</th>
                                                        <td><small class="text-muted"><?= esc($qr->qr_code ?? '') ?></small></td>
                                                    </tr>
                                                    <?php if (isset($qr->generated_at)): ?>
                                                    <tr>
                                                        <th>Generated On</th>
                                                        <td><?= date('d M Y H:i', strtotime($qr->generated_at)) ?></td>
                                                    </tr>
                                                    <?php endif; ?>
                                                    <tr>
                                                        <th>Status</th>
                                                        <td>
                                                            <?php if (isset($qr->is_active) && $qr->is_active): ?>
                                                                <span class="badge badge-success">Active</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-danger">Inactive</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($recent_attendance)): ?>
                                        <div class="mt-4">
                                            <h5>Recent Attendance</h5>
                                            <table class="table table-sm table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Check In</th>
                                                        <th>Check Out</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($recent_attendance as $att): ?>
                                                    <tr>
                                                        <td><?= date('d M Y', strtotime($att->date)) ?></td>
                                                        <td><?= $att->checkin ? date('H:i', strtotime($att->checkin)) : '-' ?></td>
                                                        <td><?= $att->checkout ? date('H:i', strtotime($att->checkout)) : '-' ?></td>
                                                        <td>
                                                            <?php 
                                                            $statusClass = 'secondary';
                                                            if ($att->status == 'present') $statusClass = 'success';
                                                            elseif ($att->status == 'late') $statusClass = 'warning';
                                                            elseif ($att->status == 'absent') $statusClass = 'danger';
                                                            ?>
                                                            <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($att->status ?? 'unknown') ?></span>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php endif; ?>
                                        
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle mr-2"></i> 
                                            No QR code generated for this employee yet. Click the "Generate QR Code" button above to create one.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                        <?php elseif ($activeTab == 'timetable'): ?>
                            <!-- Timetable Tab Content -->
                            <?php if (!empty($schedule)): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr class="bg-light">
                                                <th style="width: 120px;">Day</th>
                                                <th>Period 1</th>
                                                <th>Period 2</th>
                                                <th>Period 3</th>
                                                <th>Period 4</th>
                                                <th>Period 5</th>
                                                <th>Period 6</th>
                                                <th>Period 7</th>
                                                <th>Period 8</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($days as $day): ?>
                                            <tr>
                                                <td class="font-weight-bold bg-light"><?= $day ?></td>
                                                <?php 
                                                $daySlots = $schedule[$day] ?? [];
                                                for ($i = 0; $i < 8; $i++):
                                                    $slot = $daySlots[$i] ?? null;
                                                ?>
                                                <td class="align-middle">
                                                    <?php if ($slot): ?>
                                                        <div class="text-primary font-weight-bold"><?= esc($slot->subject_name) ?></div>
                                                        <div class="small"><?= esc($slot->class_name) ?> - <?= esc($slot->section_name) ?></div>
                                                        <div class="small text-muted">
                                                            <?= date('h:i A', strtotime($slot->start_time)) ?> - 
                                                            <?= date('h:i A', strtotime($slot->end_time)) ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <?php endfor; ?>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i> No timetable assigned to this teacher.
                                </div>
                            <?php endif; ?>
                            
                        <?php elseif ($activeTab == 'salary'): ?>
                            <!-- Salary Tab Content -->
                            <?php if (!empty($salaries)): ?>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Month</th>
                                            <th>Salary Amount</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($salaries as $salary): ?>
                                        <tr>
                                            <td><?= date('F Y', strtotime($salary->date)) ?></td>
                                            <td><?= number_format($salary->salary) ?></td>
                                            <td>
                                                <?php if ($salary->status == 1): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('admin/users/salarySlip/' . $user->id . '/' . $salary->salary_id) ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-file-pdf mr-1"></i> View Slip
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i> No salary records found.
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>