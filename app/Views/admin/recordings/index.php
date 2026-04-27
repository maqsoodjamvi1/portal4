<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
    /* Stats Cards - Simplified */
    .stats-card {
        background: white;
        border-radius: 12px;
        padding: 15px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: transform 0.3s ease;
    }
    
    .stats-card:hover {
        transform: translateY(-2px);
    }
    
    .stats-number {
        font-size: 2rem;
        font-weight: 700;
        color: #4f46e5;
    }
    
    /* Horizontal Scroll Container */
    .horizontal-scroll {
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 10px;
        -webkit-overflow-scrolling: touch;
    }
    
    .horizontal-scroll::-webkit-scrollbar {
        height: 6px;
    }
    
    .horizontal-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .horizontal-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    
    /* Section Cards - Horizontal */
    .section-card {
        display: inline-block;
        width: 200px;
        background: white;
        border-radius: 12px;
        padding: 15px;
        margin-right: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        white-space: normal;
        vertical-align: top;
    }
    
    .section-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
    
    .section-card.active {
        border-color: #4f46e5;
        background: #f5f3ff;
    }
    
    .section-title {
        font-size: 0.95rem;
        font-weight: 600;
        margin-bottom: 10px;
        word-break: break-word;
    }
    
    .section-stats {
        display: flex;
        gap: 10px;
        margin-top: 8px;
    }
    
    .stat-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .stat-badge.audio {
        background: #fee2e2;
        color: #dc2626;
    }
    
    .stat-badge.video {
        background: #dbeafe;
        color: #2563eb;
    }
    
    /* Subject Cards - Horizontal */
    .subject-card {
        display: inline-block;
        width: 180px;
        background: #f8fafc;
        border-radius: 10px;
        padding: 12px;
        margin-right: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
        white-space: normal;
        vertical-align: top;
    }
    
    .subject-card:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        transform: translateY(-2px);
    }
    
    .subject-card.active {
        background: #e0e7ff;
        border-color: #4f46e5;
    }
    
    .subject-name {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.9rem;
        margin-bottom: 8px;
        word-break: break-word;
    }
    
    .subject-stats {
        display: flex;
        gap: 8px;
        margin-top: 8px;
    }
    
    /* Recording Cards */
    .recording-card {
        background: white;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
    }
    
    .recording-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .student-avatar-sm {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    /* Inline Review Form */
    .inline-review-form {
        background: #f8fafc;
        border-radius: 10px;
        padding: 15px;
        margin-top: 15px;
        border-top: 1px solid #e2e8f0;
        display: none;
    }
    
    .inline-review-form.active {
        display: block;
    }
    
    .rating-stars {
        display: inline-flex;
        gap: 5px;
        cursor: pointer;
    }
    
    .rating-stars i {
        font-size: 1rem;
        color: #cbd5e1;
        transition: color 0.2s ease;
    }
    
    .rating-stars i.active {
        color: #fbbf24;
    }
    
    /* Section Headers */
    .section-header {
        background: white;
        padding: 12px 15px;
        border-radius: 10px;
        margin-bottom: 15px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    
    /* Tabs - Horizontal on same row */
    .recordings-tabs {
        display: flex;
        gap: 10px;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 0;
    }
    
    .recordings-tabs .nav-link {
        padding: 10px 20px;
        border: none;
        background: none;
        font-weight: 600;
        color: #64748b;
        border-bottom: 2px solid transparent;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .recordings-tabs .nav-link.active {
        color: #4f46e5;
        border-bottom-color: #4f46e5;
    }
    
    /* Loading Spinner */
    .loading-spinner {
        display: inline-block;
        width: 30px;
        height: 30px;
        border: 3px solid #e2e8f0;
        border-top-color: #4f46e5;
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    /* Recordings Container */
    .recordings-container {
        max-height: 500px;
        overflow-y: auto;
        padding-right: 5px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .stats-number {
            font-size: 1.5rem;
        }
        
        .section-card {
            width: 160px;
            padding: 10px;
        }
        
        .subject-card {
            width: 150px;
            padding: 10px;
        }
        
        .section-title {
            font-size: 0.85rem;
        }
        
        .stat-badge {
            font-size: 0.65rem;
            padding: 3px 8px;
        }
        
        .recording-card .row {
            flex-direction: column;
        }
        
        .recording-card .col-auto,
        .recording-card .col,
        .recording-card .col-md-4 {
            width: 100%;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .recording-card audio,
        .recording-card video {
            width: 100%;
        }
        
        .recordings-tabs .nav-link {
            padding: 8px 15px;
            font-size: 0.85rem;
        }
    }
    
    @media (max-width: 576px) {
        .stats-card {
            padding: 10px;
        }
        
        .stats-number {
            font-size: 1.2rem;
        }
        
        .stats-card i {
            font-size: 1.5rem !important;
        }
        
        .section-card {
            width: 140px;
        }
        
        .subject-card {
            width: 130px;
        }
    }
</style>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="mb-0"><i class="fa fa-microphone me-2 text-primary"></i> Student Recordings Review</h2>
        <div class="text-muted"><?= date('l, F j, Y') ?></div>
    </div>
    
    <!-- Statistics Cards - Row with 2 cards only -->
    <div class="row mb-4">
        <div class="col-6 col-md-3 mb-3">
            <div class="stats-card">
                <i class="fa fa-microphone fa-2x text-danger mb-2"></i>
                <div class="stats-number" id="stat-pending-audio"><?= $pendingAudio ?? 0 ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="stats-card">
                <i class="fa fa-video fa-2x text-primary mb-2"></i>
                <div class="stats-number" id="stat-pending-video"><?= $pendingVideo ?? 0 ?></div>
            </div>
        </div>
    </div>
    
    <!-- Class Sections - Horizontal Scroll -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fa fa-building me-2 text-primary"></i> Class Sections</h5>
        </div>
        <div class="card-body">
            <div class="horizontal-scroll" id="sections-container">
                <div id="sections-list" style="white-space: nowrap;">
                    <?php if(isset($sections) && !empty($sections)): ?>
                        <?php foreach ($sections as $section): ?>
                            <div class="section-card" data-cls-sec-id="<?= $section['cls_sec_id'] ?>">
                                <div class="section-title"><?= esc($section['sectionclassname']) ?></div>
                                <div class="section-stats">
                                    <span class="stat-badge audio">
                                        <i class="fa fa-microphone"></i> <span class="audio-count">0</span>
                                    </span>
                                    <span class="stat-badge video">
                                        <i class="fa fa-video"></i> <span class="video-count">0</span>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">No sections found</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Subjects - Horizontal Scroll (initially hidden) -->
    <div id="subjects-section" style="display: none;">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fa fa-book me-2 text-primary"></i> Subjects</h5>
            </div>
            <div class="card-body">
                <div class="horizontal-scroll" id="subjects-container">
                    <div id="subjects-list">
                        <div class="text-center py-4 text-muted">Select a class section to view subjects</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recordings Section (initially hidden) -->
    <div id="recordings-section" style="display: none;">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="recordings-tabs">
                    <button class="nav-link active" data-type="audio">
                        <i class="fa fa-microphone me-1"></i> Audio 
                        <span id="audio-count-badge" class="badge bg-danger ms-1">0</span>
                    </button>
                    <button class="nav-link" data-type="video">
                        <i class="fa fa-video me-1"></i> Video 
                        <span id="video-count-badge" class="badge bg-danger ms-1">0</span>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="recordings-container">
                    <div id="recordings-list">
                        <div class="text-center py-5 text-muted">
                            <i class="fa fa-info-circle fa-3x mb-3"></i>
                            <p>Select a subject to view recordings</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Indicator -->
<div id="loading-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; text-align: center; padding-top: 20%;">
    <div class="loading-spinner"></div>
    <p class="text-white mt-2">Loading...</p>
</div>

<script>
let currentClsSecId = null;
let currentSecSubId = null;
let currentType = 'audio';

// ============================================
// Escape HTML to prevent XSS attacks
// ============================================
function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// ============================================
// Attach Event Handlers
// ============================================
function attachEventHandlers() {
    $('.toggle-review-btn').off('click').on('click', function() {
        const id = $(this).data('id');
        $('#review-form-' + id).toggle();
    });
    
    $('.cancel-review-btn').off('click').on('click', function() {
        const id = $(this).data('id');
        $('#review-form-' + id).hide();
    });
    
    $('.rating-stars i').off('click').on('click', function() {
        const rating = parseInt($(this).data('rating'));
        const parentStars = $(this).closest('.rating-stars');
        parentStars.find('i').each(function(index) {
            if (index < rating) {
                $(this).addClass('active');
            } else {
                $(this).removeClass('active');
            }
        });
        parentStars.data('rating-value', rating);
    });
    
    $('.save-review-btn').off('click').on('click', async function() {
        const recordingId = $(this).data('id');
        const type = $(this).data('type');
        const form = $(this).closest('.recording-card').find('.inline-review-form');
        const rating = form.find('.rating-stars i.active').length;
        const status = form.find('.review-status').val();
        const feedback = form.find('.review-feedback').val();
        
        const url = type === 'audio' 
            ? '<?= base_url("admin/recordings/review-audio") ?>'
            : '<?= base_url("admin/recordings/review-video") ?>';
        
        const formData = new FormData();
        formData.append('recording_id', recordingId);
        formData.append('status', status);
        formData.append('feedback', feedback);
        formData.append('rating', rating);
        
        $(this).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        $(this).prop('disabled', true);
        
        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const result = await response.json();
            
            if (result.success) {
                toastr.success('Review saved successfully!');
                await loadPendingCounts();
                if (currentClsSecId) {
                    await loadSubjects(currentClsSecId);
                }
                await loadRecordings(currentType);
            } else {
                toastr.error(result.message || 'Failed to save review');
                $(this).html('<i class="fa fa-save"></i> Save Review');
                $(this).prop('disabled', false);
            }
        } catch (err) {
            console.error('Error:', err);
            toastr.error('Network error. Please try again.');
            $(this).html('<i class="fa fa-save"></i> Save Review');
            $(this).prop('disabled', false);
        }
    });
}

// ============================================
// Load Pending Counts
// ============================================
async function loadPendingCounts() {
    try {
        const response = await fetch('<?= base_url("admin/recordings/get-pending-counts") ?>');
        const counts = await response.json();
        
        $('.section-card').each(function() {
            const clsSecId = $(this).data('cls-sec-id');
            const audioCount = counts[clsSecId]?.audio || 0;
            const videoCount = counts[clsSecId]?.video || 0;
            
            $(this).find('.audio-count').text(audioCount);
            $(this).find('.video-count').text(videoCount);
            
            if (audioCount === 0 && videoCount === 0) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
        
        let totalAudio = 0, totalVideo = 0;
        Object.values(counts).forEach(c => {
            totalAudio += c.audio || 0;
            totalVideo += c.video || 0;
        });
        $('#stat-pending-audio').text(totalAudio);
        $('#stat-pending-video').text(totalVideo);
        
    } catch (err) {
        console.error('Error loading counts:', err);
    }
}

// ============================================
// Load Subjects for Selected Section
// ============================================
async function loadSubjects(clsSecId) {
    $('#subjects-list').html('<div class="text-center py-4"><div class="loading-spinner"></div><p class="mt-2">Loading subjects...</p></div>');
    
    try {
        const response = await fetch('<?= base_url("admin/recordings/get-subjects-by-section") ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'cls_sec_id=' + clsSecId
        });
        const result = await response.json();
        
        if (result.html) {
            $('#subjects-list').html(result.html);
            
            let hasVisibleSubjects = false;
            
            $('.subject-card').each(function() {
                const audioSpan = $(this).find('.stat-badge.audio');
                const videoSpan = $(this).find('.stat-badge.video');
                
                let audioCount = 0;
                let videoCount = 0;
                
                if (audioSpan.length) {
                    const audioText = audioSpan.text();
                    const audioMatch = audioText.match(/\d+/);
                    audioCount = audioMatch ? parseInt(audioMatch[0]) : 0;
                }
                
                if (videoSpan.length) {
                    const videoText = videoSpan.text();
                    const videoMatch = videoText.match(/\d+/);
                    videoCount = videoMatch ? parseInt(videoMatch[0]) : 0;
                }
                
                if (audioCount > 0 || videoCount > 0) {
                    $(this).show();
                    hasVisibleSubjects = true;
                } else {
                    $(this).hide();
                }
            });
            
            if (hasVisibleSubjects) {
                $('#subjects-section').show();
                
                $('.subject-card:visible').off('click').on('click', function() {
                    const secSubId = $(this).data('sec-sub-id');
                    const audioCount = parseInt($(this).find('.stat-badge.audio').text().match(/\d+/)?.[0] || 0);
                    const videoCount = parseInt($(this).find('.stat-badge.video').text().match(/\d+/)?.[0] || 0);
                    
                    $('.subject-card').removeClass('active');
                    $(this).addClass('active');
                    currentSecSubId = secSubId;
                    
                    $('#recordings-section').show();
                    $('#recordings-list').html('<div class="text-center py-5 text-muted"><i class="fa fa-spinner fa-spin fa-2x mb-3"></i><p>Loading recordings...</p></div>');
                    
                    if (audioCount > 0 && videoCount > 0) {
                        loadRecordings(currentType);
                    } else if (audioCount > 0) {
                        currentType = 'audio';
                        $('.recordings-tabs .nav-link').removeClass('active');
                        $('.recordings-tabs .nav-link[data-type="audio"]').addClass('active');
                        loadRecordings('audio');
                    } else if (videoCount > 0) {
                        currentType = 'video';
                        $('.recordings-tabs .nav-link').removeClass('active');
                        $('.recordings-tabs .nav-link[data-type="video"]').addClass('active');
                        loadRecordings('video');
                    }
                });
                
                // Auto-select first visible subject
                const firstSubject = $('.subject-card:visible').first();
                if (firstSubject.length && !currentSecSubId) {
                    firstSubject.click();
                }
            } else {
                $('#subjects-list').html('<div class="text-center py-4 text-muted">No pending recordings for any subject in this section</div>');
                $('#subjects-section').show();
            }
        } else {
            $('#subjects-list').html('<div class="text-center py-4 text-muted">No subjects found for this section</div>');
            $('#subjects-section').show();
        }
    } catch (err) {
        console.error('Error loading subjects:', err);
        $('#subjects-list').html('<div class="text-center py-4 text-danger">Error loading subjects</div>');
        $('#subjects-section').show();
    }
}

// ============================================
// Load Recordings for Selected Subject
// ============================================
async function loadRecordings(type) {
    if (!currentClsSecId || !currentSecSubId) {
        return;
    }
    
    $('#loading-overlay').show();
    currentType = type;
    
    const url = type === 'audio' 
        ? '<?= base_url("admin/recordings/get-filtered-pending-audio") ?>'
        : '<?= base_url("admin/recordings/get-filtered-pending-video") ?>';
    
    const fullUrl = `${url}?cls_sec_id=${currentClsSecId}&sec_sub_id=${currentSecSubId}`;
    
    try {
        const response = await fetch(fullUrl);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const recordings = await response.json();
        
        const countBadge = $('#' + type + '-count-badge');
        countBadge.text(recordings.length || 0);
        
        if (!recordings || recordings.length === 0) {
            $('#recordings-list').html(`<div class="text-center py-5 text-muted">
                <i class="fa fa-check-circle fa-3x mb-3"></i>
                <p>No pending ${type} submissions for this subject!</p>
            </div>`);
            $('#loading-overlay').hide();
            return;
        }
        
        let html = '';
        for (const rec of recordings) {
            const filePath = type === 'audio' ? rec.audio_file_path : rec.video_file_path;
            const recId = rec.recording_id;
            
            const mediaTag = type === 'audio' 
                ? `<audio controls class="w-100" style="height: 40px;"><source src="<?= base_url('') ?>${filePath}" type="audio/webm"></audio>`
                : `<video controls class="w-100" style="max-height: 120px;"><source src="<?= base_url('') ?>${filePath}" type="video/mp4"></video>`;
            
            let ratingStars = '';
            const currentRating = rec.rating || 0;
            for (let i = 1; i <= 5; i++) {
                ratingStars += `<i class="fa fa-star ${i <= currentRating ? 'active' : ''}" data-rating="${i}"></i>`;
            }
            
            html += `
                <div class="recording-card" data-id="${recId}" data-type="${type}">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <img src="${rec.photo_url || '<?= base_url("assets/img/avatar-student.png") ?>'}" 
                                 class="student-avatar-sm" alt="Student"
                                 onerror="this.src='<?= base_url("assets/img/avatar-student.png") ?>'">
                        </div>
                        <div class="col">
                            <div class="fw-bold">${escapeHtml(rec.first_name)} ${escapeHtml(rec.last_name)}</div>
                            <div class="small text-muted">${escapeHtml(rec.reg_no)}</div>
                            <div class="small text-muted">Submitted: ${new Date(rec.created_date).toLocaleString()}</div>
                            ${rec.teacher_feedback ? `<div class="small text-muted mt-1"><strong>Previous Feedback:</strong> ${escapeHtml(rec.teacher_feedback)}</div>` : ''}
                        </div>
                        <div class="col-md-4">
                            ${mediaTag}
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-sm btn-outline-primary toggle-review-btn" data-id="${recId}">
                                <i class="fa fa-edit"></i> Review
                            </button>
                        </div>
                    </div>
                    <div class="inline-review-form" id="review-form-${recId}" style="display: none;">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label class="form-label fw-bold small">Rating</label>
                                <div class="rating-stars" data-rating-value="${rec.rating || 0}">
                                    ${ratingStars}
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label fw-bold small">Status</label>
                                <select class="form-select form-select-sm review-status">
                                    <option value="approved" ${rec.status === 'approved' ? 'selected' : ''}>✅ Approve</option>
                                    <option value="rejected" ${rec.status === 'rejected' ? 'selected' : ''}>❌ Reject</option>
                                    <option value="pending" ${rec.status === 'pending' ? 'selected' : ''}>⏳ Keep Pending</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label fw-bold small">Feedback</label>
                                <textarea class="form-control form-control-sm review-feedback" rows="2" placeholder="Provide feedback...">${escapeHtml(rec.teacher_feedback || '')}</textarea>
                            </div>
                            <div class="col-md-12">
                                <button class="btn btn-sm btn-success save-review-btn" data-id="${recId}" data-type="${type}">
                                    <i class="fa fa-save"></i> Save Review
                                </button>
                                <button class="btn btn-sm btn-secondary cancel-review-btn" data-id="${recId}">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        $('#recordings-list').html(html);
        
        $('.rating-stars').each(function() {
            const ratingValue = parseInt($(this).data('rating-value')) || 0;
            $(this).find('i').each(function(index) {
                if (index < ratingValue) {
                    $(this).addClass('active');
                }
            });
        });
        
        attachEventHandlers();
        
    } catch (err) {
        console.error('Error loading recordings:', err);
        $('#recordings-list').html(`<div class="text-center py-5 text-danger">
            <i class="fa fa-exclamation-triangle fa-3x mb-3"></i>
            <p>Error loading recordings: ${err.message}</p>
        </div>`);
    } finally {
        $('#loading-overlay').hide();
    }
}

// ============================================
// Section Card Click Handler
// ============================================
$(document).on('click', '.section-card', function() {
    const audioCount = parseInt($(this).find('.audio-count').text()) || 0;
    const videoCount = parseInt($(this).find('.video-count').text()) || 0;
    
    if (audioCount === 0 && videoCount === 0) {
        toastr.info('No pending recordings in this section');
        return;
    }
    
    $('.section-card').removeClass('active');
    $(this).addClass('active');
    currentClsSecId = $(this).data('cls-sec-id');
    currentSecSubId = null;
    
    loadSubjects(currentClsSecId);
    $('#recordings-section').hide();
    $('#recordings-list').html('<div class="text-center py-5 text-muted"><i class="fa fa-info-circle fa-3x mb-3"></i><p>Select a subject to view recordings</p></div>');
    $('#audio-count-badge, #video-count-badge').text('0');
});

// ============================================
// Tab Switching
// ============================================
$('.recordings-tabs .nav-link').off('click').on('click', function() {
    $('.recordings-tabs .nav-link').removeClass('active');
    $(this).addClass('active');
    currentType = $(this).data('type');
    loadRecordings(currentType);
});

// ============================================
// Initialize
// ============================================
$(document).ready(function() {
    loadPendingCounts();
});
</script>

<?= $this->endSection() ?>