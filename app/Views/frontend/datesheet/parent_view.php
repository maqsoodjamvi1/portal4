<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><?= esc($title ?? 'Exam Datesheet') ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('student/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Datesheet</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Children Cards -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="mb-3"><i class="fas fa-users mr-2"></i>Select Student</h4>
                <div class="row">
                    <?php foreach ($children as $child): ?>
                        <?php 
                            $isActive = ($active_student_id == $child['student_id']);
                            $activeClass = $isActive ? 'active-student' : '';
                        ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="student-card card <?= $activeClass ?>" data-student-id="<?= $child['student_id'] ?>">
                                <div class="card-body d-flex align-items-center p-3">
                                    <div class="student-avatar mr-3">
                                        <?php if (!empty($child['photo']) && file_exists(FCPATH . $child['photo'])): ?>
                                            <img src="<?= base_url($child['photo']) ?>" 
                                                 alt="<?= esc($child['first_name']) ?>" 
                                                 class="img-circle" style="width: 60px; height: 60px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="avatar-placeholder bg-primary text-white d-flex align-items-center justify-content-center rounded-circle" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="fas fa-user fa-2x"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="student-info flex-grow-1">
                                        <h5 class="mb-1">
                                            <?= esc($child['first_name'] . ' ' . $child['last_name']) ?>
                                            <?php if ($isActive): ?>
                                                <span class="badge badge-success ml-2"><i class="fas fa-check"></i> Active</span>
                                            <?php endif; ?>
                                        </h5>
                                        <p class="mb-1 text-muted">
                                            <i class="fas fa-graduation-cap mr-1"></i>
                                            <?= esc($child['class_name'] ?? 'N/A') ?> - <?= esc($child['section_name'] ?? 'N/A') ?>
                                        </p>
                                        <p class="mb-0 text-muted small">
                                            <i class="fas fa-school mr-1"></i>
                                            <?= esc($child['campus_name'] ?? '') ?>
                                        </p>
                                    </div>
                                    <div class="student-action">
                                        <?php if (!$isActive): ?>
                                            <button class="btn btn-sm btn-outline-primary select-student-btn" 
                                                    data-id="<?= $child['student_id'] ?>">
                                                <i class="fas fa-eye mr-1"></i> View
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-primary" disabled>
                                                <i class="fas fa-check mr-1"></i> Selected
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Datesheet Section -->
        <?php if ($active_student_id > 0 && isset($student)): ?>
            <div id="datesheet-section">
                <div class="card card-primary">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="card-title flex-grow-1">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            <?php if (!empty($exam_name)): ?>
                                <?= esc($exam_name) ?> Datesheet
                            <?php else: ?>
                                Exam Datesheet
                            <?php endif; ?>
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-info">
                                <?= esc($student['class_name'] ?? '') ?> - <?= esc($student['section_name'] ?? '') ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <!-- Student Info Banner -->
                        <div class="student-banner bg-light p-3 mb-4 rounded d-flex align-items-center">
                            <div class="student-avatar mr-3">
                                <?php if (!empty($student['photo']) && file_exists(FCPATH . $student['photo'])): ?>
                                    <img src="<?= base_url($student['photo']) ?>" 
                                         alt="<?= esc($student['first_name']) ?>" 
                                         class="img-circle" style="width: 70px; height: 70px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="avatar-placeholder bg-primary text-white d-flex align-items-center justify-content-center rounded-circle" 
                                         style="width: 70px; height: 70px;">
                                        <i class="fas fa-user fa-2x"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h4 class="mb-1"><?= esc($student['first_name'] . ' ' . $student['last_name']) ?></h4>
                                <div class="text-muted">
                                    <span class="mr-3"><i class="fas fa-id-card mr-1"></i> Roll No: <?= esc($student['roll_no'] ?? 'N/A') ?></span>
                                    <span><i class="fas fa-school mr-1"></i> <?= esc($student['campus_name'] ?? '') ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Datesheet Timeline -->
                        <?php if (!empty($datesheet) && empty($error)): ?>
                            <div class="datesheet-timeline">
                                <?php 
                                $dayNames = ['Sun' => 'Sunday', 'Mon' => 'Monday', 'Tue' => 'Tuesday', 
                                           'Wed' => 'Wednesday', 'Thu' => 'Thursday', 'Fri' => 'Friday', 
                                           'Sat' => 'Saturday'];
                                $counter = 0;
                                ?>
                                
                                <?php foreach ($datesheet as $date => $exams): ?>
                                    <?php 
                                        $dateObj = new DateTime($date);
                                        $dayShort = $dateObj->format('D');
                                        $dayFull = $dayNames[$dayShort] ?? $dayShort;
                                        $formattedDate = $dateObj->format('d M Y');
                                        $counter++;
                                    ?>
                                    
                                    <div class="timeline-item mb-4" id="date-<?= $date ?>">
                                        <div class="timeline-date bg-primary text-white p-3 rounded-top">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h5 class="mb-0"><i class="far fa-calendar mr-2"></i><?= $formattedDate ?></h5>
                                                    <p class="mb-0"><?= $dayFull ?></p>
                                                </div>
                                                <span class="badge badge-light"><?= count($exams) ?> Exam<?= count($exams) > 1 ? 's' : '' ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="timeline-exams border rounded-bottom">
                                            <?php foreach ($exams as $exam): ?>
                                                <div class="exam-card p-3 border-bottom">
                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <h6 class="mb-2">
                                                                <i class="fas fa-book mr-2 text-primary"></i>
                                                                <?= esc($exam['subject_name']) ?>
                                                        
                           
                                        
                       
                                                            
                                                            <?php if (!empty($exam['room_no'])): ?>
                                                                <div class="mb-2">
                                                                    <i class="fas fa-door-open text-muted mr-1"></i>
                                                                    <span class="text-muted">Room: <?= esc($exam['room_no']) ?></span>
                                                                </div>
                                                            <?php endif; ?>
                                                            
                                                            <?php if ($exam['total_marks'] > 0): ?>
                                                                <div class="mb-2">
                                                                    <i class="fas fa-star text-warning mr-1"></i>
                                                                    <span class="text-muted">Total Marks: <?= $exam['total_marks'] ?></span>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        
                                                        <div class="col-md-4 d-flex align-items-center justify-content-end">
                                                            <button class="btn btn-outline-info btn-sm syllabus-toggle" 
                                                                    type="button" 
                                                                    data-toggle="collapse" 
                                                                    data-target="#syllabus-<?= $date . '-' . $exam['subject_id'] ?>">
                                                                <i class="fas fa-book-open mr-1"></i> View Syllabus
                                                            </button>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Syllabus (Collapsible) -->
                                                    <div class="collapse mt-3" id="syllabus-<?= $date . '-' . $exam['subject_id'] ?>">
                                                        <div class="card card-body bg-light">
                                                            <h6 class="text-primary mb-2"><i class="fas fa-list mr-1"></i> Exam Syllabus</h6>
                                                            <?php 
                                                                $syllabus = $exam['syllabus'] ?? '';
                                                                $isUrdu = preg_match('/\p{Arabic}/u', $syllabus);
                                                                $syllClass = $isUrdu ? 'urdu-text' : 'english-text';
                                                            ?>
                                                            <div class="<?= $syllClass ?>">
                                                                <?= nl2br(esc(strip_tags(html_entity_decode($syllabus)))) ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Print Button -->
                            <div class="text-center mt-4">
                                <button class="btn btn-primary print-datesheet" onclick="printDatesheet()">
                                    <i class="fas fa-print mr-2"></i> Print Datesheet
                                </button>
                            </div>
                            
                        <?php else: ?>
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle fa-2x mb-3"></i>
                                <h5>No Datesheet Available</h5>
                                <p><?= esc($error ?? 'Datesheet has not been published yet.') ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php elseif ($active_student_id == 0): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h4>Select a Student</h4>
                    <p class="text-muted">Please select a student from the list above to view their datesheet.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
/* Student Cards */
.student-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
    cursor: pointer;
}

.student-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border-color: #007bff;
}

.student-card.active-student {
    border-color: #28a745;
    background-color: rgba(40, 167, 69, 0.05);
}

/* Timeline */
.timeline-item {
    position: relative;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -20px;
    top: 40px;
    bottom: -20px;
    width: 2px;
    background: #dee2e6;
}

@media (max-width: 768px) {
    .timeline-item::before {
        left: 0;
    }
}

.timeline-date {
    position: relative;
    z-index: 2;
}

/* Exam Cards */
.exam-card {
    background: #fff;
    transition: all 0.3s ease;
}

.exam-card:hover {
    background: #f8f9fa;
}

/* Syllabus */
.urdu-text {
    direction: rtl;
    text-align: right;
    font-family: 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu', 'Segoe UI', sans-serif;
    font-size: 1.1rem;
    line-height: 1.8;
}

.english-text {
    direction: ltr;
    text-align: left;
}

/* Avatar */
.avatar-placeholder {
    font-size: 1.5rem;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .student-card .card-body {
        flex-direction: column;
        text-align: center;
    }
    
    .student-avatar {
        margin-bottom: 15px;
        margin-right: 0;
    }
    
    .student-action {
        margin-top: 15px;
        width: 100%;
    }
    
    .exam-card .row {
        flex-direction: column;
    }
    
    .exam-card .col-md-4 {
        margin-top: 15px;
        justify-content: flex-start !important;
    }
}

/* Print Styles */
@media print {
    .student-card,
    .content-header,
    .breadcrumb,
    .card-header .card-tools,
    .syllabus-toggle,
    .print-datesheet {
        display: none !important;
    }
    
    .timeline-item {
        break-inside: avoid;
    }
    
    .collapse {
        display: block !important;
    }
}
</style>

<script>
$(document).ready(function() {
    // Switch student
    $('.select-student-btn').click(function() {
        const studentId = $(this).data('id');
        
        // Show loading
        $('#datesheet-section').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2">Loading datesheet...</p>
            </div>
        `);
        
        // AJAX request to switch student
        $.ajax({
            url: '<?= base_url("student/datesheet/switch/") ?>' + studentId,
            method: 'POST',
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    // Reload the page to show new datesheet
                    window.location.reload();
                } else {
                    alert('Error: ' + response.message);
                    window.location.reload();
                }
            },
            error: function() {
                alert('Network error. Please try again.');
                window.location.reload();
            }
        });
    });
    
    // Toggle syllabus with animation
    $('.syllabus-toggle').click(function() {
        const icon = $(this).find('i');
        if ($(this).attr('aria-expanded') === 'true') {
            icon.removeClass('fa-book-open').addClass('fa-book');
            $(this).html('<i class="fas fa-book mr-1"></i> View Syllabus');
        } else {
            icon.removeClass('fa-book').addClass('fa-book-open');
            $(this).html('<i class="fas fa-book-open mr-1"></i> Hide Syllabus');
        }
    });
    
    // Student card click
    $('.student-card').click(function() {
        if (!$(this).hasClass('active-student')) {
            const studentId = $(this).data('student-id');
            $('.select-student-btn[data-id="' + studentId + '"]').click();
        }
    });
});

function printDatesheet() {
    window.print();
}
</script>

<?= $this->endSection() ?>