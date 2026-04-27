<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
    .student-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: transform 0.3s ease;
        cursor: pointer;
    }
    
    .student-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .student-avatar {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e2e8f0;
    }
    
    .stats-row {
        display: flex;
        justify-content: center;
        gap: 30px;
        margin-top: 15px;
    }
    
    .stat-item {
        text-align: center;
    }
    
    .stat-icon {
        font-size: 1.8rem;
    }
    
    .stat-icon.audio {
        color: #dc2626;
    }
    
    .stat-icon.video {
        color: #4f46e5;
    }
    
    .stat-count {
        font-size: 1.5rem;
        font-weight: 700;
        margin-top: 5px;
    }
    
    .stat-count.audio {
        color: #dc2626;
    }
    
    .stat-count.video {
        color: #4f46e5;
    }
    
    .progress-bar-custom {
        height: 6px;
        border-radius: 3px;
        background: #e2e8f0;
        overflow: hidden;
        margin-top: 15px;
    }
    
    .progress-fill-audio {
        height: 100%;
        background: #dc2626;
        border-radius: 3px;
    }
    
    .progress-fill-video {
        height: 100%;
        background: #4f46e5;
        border-radius: 3px;
    }
    
    .btn-view {
        margin-top: 15px;
        width: 100%;
    }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fa fa-chart-line me-2 text-primary"></i> Student Progress</h2>
        <div class="text-muted"><?= date('l, F j, Y') ?></div>
    </div>
    
    <div class="row">
        <?php if (!empty($students)): ?>
            <?php foreach ($students as $student): ?>
                <?php 
                // Get student photo URL
                $photoFile = $student['profile_photo'] ?? '';
                $photoUrl = base_url('assets/img/avatar-student.png');
                
                if (!empty($photoFile)) {
                    $photoFile = ltrim($photoFile, '/');
                    $possiblePaths = [
                        'uploads/' . $photoFile,
                        'student_photos/' . $photoFile,
                        'system-logo/' . $photoFile
                    ];
                    
                    foreach ($possiblePaths as $path) {
                        if (file_exists(FCPATH . $path)) {
                            $photoUrl = base_url($path);
                            break;
                        }
                    }
                }
                ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="student-card" onclick="window.location.href='<?= base_url('admin/recordings/student-details/' . $student['student_id']) ?>'">
                        <div class="d-flex align-items-center">
                            <img src="<?= $photoUrl ?>" 
                                 class="student-avatar me-3" 
                                 alt="Student"
                                 onerror="this.src='<?= base_url('assets/img/avatar-student.png') ?>'">
                            <div>
                                <h6 class="mb-0"><?= esc($student['first_name'] . ' ' . $student['last_name']) ?></h6>
                                <small class="text-muted"><?= esc($student['reg_no'] ?? 'N/A') ?></small>
                                <div class="small text-muted">
                                    <?= esc($student['class_name'] ?? '') ?> <?= esc($student['section_name'] ?? '') ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stats-row">
                            <div class="stat-item">
                                <div class="stat-icon audio">
                                    <i class="fa fa-microphone"></i>
                                </div>
                                <div class="stat-count audio"><?= $student['pending_audio'] ?? 0 ?></div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-icon video">
                                    <i class="fa fa-video"></i>
                                </div>
                                <div class="stat-count video"><?= $student['pending_video'] ?? 0 ?></div>
                            </div>
                        </div>
                        
                        <?php 
                        $total = ($student['pending_audio'] ?? 0) + ($student['pending_video'] ?? 0);
                        $audioPercent = $total > 0 ? (($student['pending_audio'] ?? 0) / $total) * 100 : 0;
                        $videoPercent = $total > 0 ? (($student['pending_video'] ?? 0) / $total) * 100 : 0;
                        ?>
                        
                        <div class="progress-bar-custom">
                            <div class="progress-fill-audio" style="width: <?= $audioPercent ?>%"></div>
                            <div class="progress-fill-video" style="width: <?= $videoPercent ?>%"></div>
                        </div>
                        
                        <button class="btn btn-sm btn-outline-primary btn-view">
                            <i class="fa fa-chart-line"></i> View Details
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fa fa-check-circle fa-4x text-success mb-3"></i>
                    <h4>No Pending Submissions</h4>
                    <p class="text-muted">All student recordings have been reviewed!</p>
                    <a href="<?= base_url('admin/recordings') ?>" class="btn btn-primary mt-3">
                        <i class="fa fa-arrow-left"></i> Back to Recordings
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>