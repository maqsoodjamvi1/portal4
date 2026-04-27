<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
    .student-header {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        border-radius: 15px;
        padding: 25px;
        color: white;
        margin-bottom: 25px;
    }
    
    .student-avatar-lg {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    
    .info-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .stats-simple {
        display: flex;
        justify-content: center;
        gap: 40px;
    }
    
    .stat-simple {
        text-align: center;
    }
    
    .stat-simple i {
        font-size: 2rem;
    }
    
    .stat-simple .count {
        font-size: 1.8rem;
        font-weight: 700;
    }
    
    .stat-simple .audio-icon {
        color: #dc2626;
    }
    
    .stat-simple .video-icon {
        color: #4f46e5;
    }
    
    .recording-item {
        background: white;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-block;
    }
    
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .status-approved {
        background: #d1fae5;
        color: #065f46;
    }
    
    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .rating-stars-display {
        display: inline-flex;
        gap: 3px;
    }
    
    .rating-stars-display i {
        font-size: 0.9rem;
        color: #cbd5e1;
    }
    
    .rating-stars-display i.active {
        color: #fbbf24;
    }
    
    @media (max-width: 768px) {
        .student-header {
            text-align: center;
        }
        
        .student-avatar-lg {
            margin-bottom: 15px;
        }
        
        .stats-simple {
            gap: 20px;
        }
        
        .recording-item .row > div {
            margin-bottom: 10px;
            text-align: center;
        }
        
        .recording-item audio,
        .recording-item video {
            width: 100%;
        }
    }
</style>

<div class="container-fluid">
    <div class="mb-3">
        <a href="<?= base_url('admin/recordings/student-progress') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Back to Student Progress
        </a>
    </div>
    
    <!-- Student Header -->
    <div class="student-header">
        <div class="row align-items-center">
            <div class="col-md-auto text-center text-md-start">
                <?php 
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
                <img src="<?= $photoUrl ?>" 
                     class="student-avatar-lg" 
                     alt="Student"
                     onerror="this.src='<?= base_url('assets/img/avatar-student.png') ?>'">
            </div>
            <div class="col-md">
                <h3 class="mb-1"><?= esc($student['first_name'] . ' ' . $student['last_name']) ?></h3>
                <p class="mb-0 opacity-75">
                    <i class="fa fa-id-card me-1"></i> <?= esc($student['reg_no'] ?? 'N/A') ?> |
                    <i class="fa fa-graduation-cap me-1"></i> <?= esc($student['class_name'] ?? '') ?> <?= esc($student['section_name'] ?? '') ?> |
                    <i class="fa fa-user-friends me-1"></i> Father: <?= esc($student['father_name'] ?? 'N/A') ?>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Simple Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="info-card">
                <div class="stats-simple">
                    <div class="stat-simple">
                        <i class="fa fa-microphone audio-icon"></i>
                        <div class="count"><?= count($audioRecordings ?? []) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="info-card">
                <div class="stats-simple">
                    <div class="stat-simple">
                        <i class="fa fa-video video-icon"></i>
                        <div class="count"><?= count($videoRecordings ?? []) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Audio Recordings Section -->
    <div class="info-card">
        <h5 class="mb-3"><i class="fa fa-microphone text-danger me-2"></i> Audio Recordings</h5>
        <?php if (!empty($audioRecordings)): ?>
            <?php foreach ($audioRecordings as $recording): ?>
                <div class="recording-item">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <div class="small text-muted"><?= date('M j, Y g:i A', strtotime($recording['created_date'])) ?></div>
                            <div class="fw-bold"><?= esc($recording['subject_name'] ?? 'General') ?></div>
                        </div>
                        <div class="col-md-4">
                            <audio controls class="w-100" style="height: 40px;">
                                <source src="<?= base_url($recording['audio_file_path']) ?>" type="audio/webm">
                            </audio>
                        </div>
                        <div class="col-md-2">
                            <span class="status-badge status-<?= $recording['status'] ?>">
                                <?= ucfirst($recording['status']) ?>
                            </span>
                        </div>
                        <div class="col-md-3">
                            <?php if ($recording['rating']): ?>
                                <div class="rating-stars-display">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fa fa-star <?= $i <= $recording['rating'] ? 'active' : '' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($recording['teacher_feedback']): ?>
                                <div class="small text-muted mt-1">
                                    <i class="fa fa-comment"></i> <?= esc($recording['teacher_feedback']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-4 text-muted">
                <i class="fa fa-microphone fa-2x mb-2 opacity-50"></i>
                <p>No audio recordings found.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Video Recordings Section -->
    <div class="info-card">
        <h5 class="mb-3"><i class="fa fa-video text-primary me-2"></i> Video Recordings</h5>
        <?php if (!empty($videoRecordings)): ?>
            <?php foreach ($videoRecordings as $recording): ?>
                <div class="recording-item">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <div class="small text-muted"><?= date('M j, Y g:i A', strtotime($recording['created_date'])) ?></div>
                            <div class="fw-bold"><?= esc($recording['subject_name'] ?? 'General') ?></div>
                        </div>
                        <div class="col-md-4">
                            <video controls class="w-100" style="max-height: 100px;">
                                <source src="<?= base_url($recording['video_file_path']) ?>" type="video/mp4">
                            </video>
                        </div>
                        <div class="col-md-2">
                            <span class="status-badge status-<?= $recording['status'] ?>">
                                <?= ucfirst($recording['status']) ?>
                            </span>
                        </div>
                        <div class="col-md-3">
                            <?php if ($recording['rating']): ?>
                                <div class="rating-stars-display">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fa fa-star <?= $i <= $recording['rating'] ? 'active' : '' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($recording['teacher_feedback']): ?>
                                <div class="small text-muted mt-1">
                                    <i class="fa fa-comment"></i> <?= esc($recording['teacher_feedback']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-4 text-muted">
                <i class="fa fa-video fa-2x mb-2 opacity-50"></i>
                <p>No video recordings found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>