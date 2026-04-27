<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
/* Your existing styles remain the same */
.activity-card {
    margin-bottom: 20px;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.3s;
}
.activity-card:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.activity-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 20px;
}
.activity-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 5px;
}
.activity-meta {
    font-size: 12px;
    opacity: 0.9;
}
.activity-body {
    padding: 20px;
}
.activity-description {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 8px;
    margin: 10px 0;
}
.activity-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    margin-right: 8px;
}
.badge-discussion { background: #28a745; color: white; }
.badge-group-work { background: #17a2b8; color: white; }
.badge-presentation { background: #ffc107; color: #333; }
.badge-lab { background: #fd7e14; color: white; }
.badge-lecture { background: #6f42c1; color: white; }
.badge-other { background: #6c757d; color: white; }

.task-icon {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 8px;
}
.task-icon.video { background: #e3f2fd; color: #1976d2; }
.task-icon.audio { background: #fce4ec; color: #c2185b; }
.task-icon.group { background: #e8f5e9; color: #388e3c; }

.media-gallery {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-top: 15px;
}
.media-item {
    flex: 1;
    min-width: 200px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 10px;
    background: #f9f9f9;
}
.media-preview iframe,
.media-preview img,
.media-preview video {
    width: 100%;
    max-height: 150px;
    border-radius: 6px;
}
.review-box {
    background: #e8f5e9;
    border-left: 4px solid #4caf50;
    padding: 12px;
    border-radius: 8px;
    margin-top: 15px;
}
.rating-stars {
    color: #ffc107;
    font-size: 18px;
}
.filter-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}
.btn-add-media {
    border: 1px dashed #28a745;
    background: transparent;
    color: #28a745;
}
.btn-add-media:hover {
    background: #28a745;
    color: white;
}
.modal-media .modal-dialog {
    max-width: 600px;
}
.session-info {
    background: #e3f2fd;
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: inline-block;
}
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>My Activity Report</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Activity Report</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <!-- Session Info Banner -->
    <div class="session-info">
        <i class="fa fa-calendar"></i> <strong>Current Session:</strong> <?= esc($session_name) ?>
    </div>
    
    <!-- Filter Section - Only Term Selection -->
    <div class="filter-section">
        <form method="get" action="<?= base_url('admin/activity-report/teacher-report') ?>" class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Select Term</label>
                    <select name="term_id" class="form-control" onchange="this.form.submit()">
                        <?php foreach ($terms as $ts): ?>
                            <option value="<?= $ts->term_session_id ?>" <?= ($selected_term == $ts->term_session_id) ? 'selected' : '' ?>>
                                <?= esc($ts->term_name) ?> (<?= date('d M Y', strtotime($ts->start_date)) ?> - <?= date('d M Y', strtotime($ts->end_date)) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa fa-filter"></i> Filter Activities
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Activities List -->
    <?php if (empty($activities)): ?>
        <div class="alert alert-info text-center">
            <i class="fa fa-info-circle fa-2x"></i>
            <p>No activities found for the selected term.</p>
        </div>
    <?php else: ?>
        <?php foreach ($activities as $activity): ?>
            <div class="activity-card">
                <div class="activity-header">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="activity-title"><?= date('l, d M Y', strtotime($activity->date)) ?></div>
                            <div class="activity-meta">
                                <i class="fa fa-graduation-cap"></i> <?= esc($activity->class_name) ?> - <?= esc($activity->section_name) ?>
                                &nbsp;|&nbsp;
                                <i class="fa fa-book"></i> <?= esc($activity->subject_name) ?>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-light" onclick="printActivity(<?= $activity->did ?>)">
                            <i class="fa fa-print"></i> Print
                        </button>
                    </div>
                </div>
                
                <div class="activity-body">
                    <?php foreach ($activity->activities_list as $act): ?>
                        <div class="mb-4">
                            <h5>
                                <?= esc($act['name']) ?>
                                <span class="activity-badge badge-<?= $act['type'] ?>">
                                    <?php
                                    $types = ['discussion' => 'Discussion', 'group-work' => 'Group Work', 
                                             'presentation' => 'Presentation', 'lab' => 'Lab', 
                                             'lecture' => 'Lecture', 'other' => 'Activity'];
                                    echo $types[$act['type']] ?? 'Activity';
                                    ?>
                                </span>
                                <?php if (isset($act['duration_minutes'])): ?>
                                    <small class="text-muted">(<?= $act['duration_minutes'] ?> mins)</small>
                                <?php endif; ?>
                            </h5>
                            
                            <?php if (!empty($act['description'])): ?>
                                <div class="activity-description">
                                    <?= nl2br(esc($act['description'])) ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($act['learning_objective'])): ?>
                                <div class="text-muted small mt-2">
                                    <i class="fa fa-bullseye"></i> <strong>Objective:</strong> <?= esc($act['learning_objective']) ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Task Icons -->
                            <div class="mt-2">
                                <?php if (!empty($act['video_task']['enabled'])): ?>
                                    <span class="task-icon video"><i class="fa fa-video-camera"></i> Video Task</span>
                                <?php endif; ?>
                                <?php if (!empty($act['audio_task']['enabled'])): ?>
                                    <span class="task-icon audio"><i class="fa fa-headphones"></i> Audio Task</span>
                                <?php endif; ?>
                                <?php if (!empty($act['group_activity']['enabled'])): ?>
                                    <span class="task-icon group"><i class="fa fa-users"></i> Group Work</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Media Gallery -->
                    <?php 
                    $activityMedia = [];
                    if (!empty($activity->media_links)) {
                        foreach ($activity->media_links as $actId => $media) {
                            $activityMedia = array_merge($activityMedia, $media);
                        }
                    }
                    ?>
                    <?php if (!empty($activityMedia)): ?>
                        <div class="media-gallery">
                            <?php foreach ($activityMedia as $media): ?>
                                <div class="media-item">
                                    <div class="media-preview">
                                        <?php if ($media['type'] == 'youtube'): 
                                            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $media['url'], $matches);
                                            $videoId = $matches[1] ?? '';
                                        ?>
                                            <iframe src="https://www.youtube.com/embed/<?= $videoId ?>" frameborder="0" allowfullscreen></iframe>
                                        <?php elseif ($media['type'] == 'image'): ?>
                                            <img src="<?= $media['url'] ?>" alt="Activity Media">
                                        <?php elseif ($media['type'] == 'video'): ?>
                                            <video controls><source src="<?= $media['url'] ?>"></video>
                                        <?php else: ?>
                                            <a href="<?= $media['url'] ?>" target="_blank" class="btn btn-sm btn-outline-primary btn-block">
                                                <i class="fa fa-external-link"></i> View Media
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($media['caption'])): ?>
                                        <div class="media-caption small text-muted mt-1"><?= esc($media['caption']) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Add Media Button -->
                    <div class="mt-3">
                        <button type="button" class="btn btn-sm btn-add-media" 
                                onclick="showAddMediaModal('<?= $activity->did ?>', '<?= $activity->activities_list[0]['activity_id'] ?? '' ?>', '<?= addslashes($activity->activities_list[0]['name'] ?? 'Activity') ?>')">
                            <i class="fa fa-plus"></i> Add Media Link (YouTube, Image, etc.)
                        </button>
                    </div>
                    
                    <!-- Principal Review -->
                    <?php if ($activity->rating): ?>
                        <div class="review-box">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong><i class="fa fa-star text-warning"></i> Principal's Review</strong>
                                <div class="rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fa fa-star <?= ($i <= $activity->rating) ? 'text-warning' : 'text-muted' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p><?= nl2br(esc($activity->feedback)) ?></p>
                            <?php if ($activity->strengths): ?>
                                <div class="small text-success"><strong>Strengths:</strong> <?= esc($activity->strengths) ?></div>
                            <?php endif; ?>
                            <?php if ($activity->areas_for_improvement): ?>
                                <div class="small text-warning"><strong>Areas to Improve:</strong> <?= esc($activity->areas_for_improvement) ?></div>
                            <?php endif; ?>
                            <div class="small text-muted mt-2">
                                Reviewed on: <?= date('d M Y', strtotime($activity->review_date)) ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-secondary mt-3">
                            <i class="fa fa-clock-o"></i> Awaiting principal's review...
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<!-- Add Media Modal -->
<div class="modal fade modal-media" id="addMediaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fa fa-link"></i> Add Media Link</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="media_did">
                <input type="hidden" id="media_activity_id">
                <div class="form-group">
                    <label>Media URL <span class="text-danger">*</span></label>
                    <input type="url" id="media_url" class="form-control" 
                           placeholder="YouTube URL, Image URL, Facebook Post URL, etc.">
                    <small class="text-muted">Supported: YouTube, Facebook, Instagram, Images, Videos</small>
                </div>
                <div class="form-group">
                    <label>Caption (Optional)</label>
                    <input type="text" id="media_caption" class="form-control" 
                           placeholder="Describe what this media shows">
                </div>
                <div id="media_preview" class="mt-2" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="submitMediaLink()">
                    <i class="fa fa-save"></i> Add Link
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const URL_ADD_MEDIA = "<?= base_url('admin/activity-report/add-media-link') ?>";
const CSRF_NAME = "<?= csrf_token() ?>";
let CSRF_HASH = "<?= csrf_hash() ?>";

function addCsrf(payload) {
    if (CSRF_NAME && CSRF_HASH) payload[CSRF_NAME] = CSRF_HASH;
    return payload;
}

function refreshCsrfFromXHR(xhr) {
    const t = xhr && (xhr.getResponseHeader('X-CSRF-TOKEN') || xhr.getResponseHeader('X-CSRF-Token'));
    if (t) { CSRF_HASH = t; }
}

function showAddMediaModal(did, activityId, activityName) {
    $('#media_did').val(did);
    $('#media_activity_id').val(activityId);
    $('#media_url').val('');
    $('#media_caption').val('');
    $('#media_preview').hide().html('');
    $('#addMediaModal').modal('show');
}

$('#media_url').on('input', function() {
    const url = $(this).val();
    if (url) {
        let previewHtml = '<div class="alert alert-info p-2"><i class="fa fa-eye"></i> Preview:<br>';
        if (url.includes('youtube.com') || url.includes('youtu.be')) {
            const videoId = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/);
            if (videoId) {
                previewHtml += '<iframe width="100%" height="150" src="https://www.youtube.com/embed/' + videoId[1] + '" frameborder="0"></iframe>';
            }
        } else if (url.match(/\.(jpg|jpeg|png|gif|webp)$/i)) {
            previewHtml += '<img src="' + url + '" class="img-fluid rounded" style="max-height: 150px;">';
        } else {
            previewHtml += '<a href="' + url + '" target="_blank">Open Link</a>';
        }
        previewHtml += '</div>';
        $('#media_preview').html(previewHtml).show();
    } else {
        $('#media_preview').hide();
    }
});

function submitMediaLink() {
    const url = $('#media_url').val().trim();
    if (!url) {
        toastr.error('Please enter a URL');
        return;
    }
    
    const payload = addCsrf({
        did: $('#media_did').val(),
        activity_id: $('#media_activity_id').val(),
        media_url: url,
        caption: $('#media_caption').val()
    });
    
    $.ajax({
        url: URL_ADD_MEDIA,
        type: 'POST',
        data: payload,
        dataType: 'json',
        success: function(res, status, xhr) {
            refreshCsrfFromXHR(xhr);
            if (res.success) {
                toastr.success('Media link added successfully');
                $('#addMediaModal').modal('hide');
                location.reload();
            } else {
                toastr.error(res.msg || 'Failed to add media link');
            }
        },
        error: function() {
            toastr.error('Error adding media link');
        }
    });
}

function printActivity(did) {
    window.open('<?= base_url('admin/activity-report/print-activity/') ?>' + did, '_blank');
}
</script>

<?= $this->endSection() ?>