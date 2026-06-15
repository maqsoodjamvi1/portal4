<?php foreach ($weekDays as $day): ?>
<div class="cd-day-card card mb-3" data-date="<?= $day['date'] ?>" data-activities='<?= htmlspecialchars(json_encode($day['activities'] ?? []), ENT_QUOTES, 'UTF-8') ?>'>
    <input type="hidden" class="diary-date" value="<?= $day['date'] ?>">
    <input type="hidden" class="diary-id" value="<?= $day['did'] ?? 0 ?>">
    <input type="hidden" class="has-activities-indicator" value="<?= ($day['has_activities'] ?? 0) ?>">
    
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <strong><?= date('l', strtotime($day['date'])) ?></strong>
            <span class="text-muted"><?= date('M j, Y', strtotime($day['date'])) ?></span>
            <span class="subject-badge">
                <i class="fa fa-book"></i> <?= esc($subject_name) ?>
            </span>
        </div>
        <div class="cd-header-right">
            <button type="button" class="btn btn-sm btn-outline-primary btn-add-activity-header" 
                    onclick="openActivityModal('<?= $day['date'] ?>')">
                <i class="fa fa-calendar-plus"></i> Add Activity
            </button>
            <span class="autosave-pill saved" style="display:none; margin-left: 10px;">
                <span class="autosave-dot"></span>
                <span class="autosave-text">Saved</span>
            </span>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Activities Section - Only shown when activities exist -->
        <?php 
        $activities = $day['activities'] ?? [];
        if (!empty($activities)): 
        ?>
        <div class="activities-section mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="section-label mb-0">
                    <i class="fa fa-tasks text-success"></i> Classroom Activities
                </label>
            </div>
            <div class="activities-container">
                <?php foreach ($activities as $index => $activity): 
                    $typeLabel = [
                        'discussion' => 'Group Discussion',
                        'group-work' => 'Group Work',
                        'presentation' => 'Presentation',
                        'lab' => 'Lab Work',
                        'lecture' => 'Lecture',
                        'other' => 'Activity'
                    ][$activity['type']] ?? 'Activity';
                    
                    $typeIcon = [
                        'discussion' => 'fa-comments',
                        'group-work' => 'fa-users',
                        'presentation' => 'fa-chalkboard',
                        'lab' => 'fa-flask',
                        'lecture' => 'fa-microphone-alt',
                        'other' => 'fa-star'
                    ][$activity['type']] ?? 'fa-tasks';
                    
                    $typeClass = [
                        'discussion' => 'discussion',
                        'group-work' => 'group-work',
                        'presentation' => 'presentation',
                        'lab' => 'lab',
                        'lecture' => 'lecture',
                        'other' => 'other'
                    ][$activity['type']] ?? 'other';
                ?>
                <div class="activity-item" data-activity-id="<?= $activity['activity_id'] ?>">
                    <div class="activity-header">
                        <div class="activity-title">
                            <i class="fa <?= $typeIcon ?>"></i>
                            <span class="activity-name"><?= esc($activity['name']) ?></span>
                            <span class="activity-badge <?= $typeClass ?> ms-2"><?= $typeLabel ?></span>
                            <?php if (isset($activity['duration_minutes'])): ?>
                                <span class="badge text-bg-light ms-1">
                                    <i class="fa fa-clock-o"></i> <?= $activity['duration_minutes'] ?> min
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="activity-actions">
                            <button type="button" class="btn btn-sm btn-link text-primary p-0 me-2" 
                                    onclick="openActivityModal('<?= $day['date'] ?>', '<?= $activity['activity_id'] ?>')"
                                    title="Edit Activity">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" 
                                    onclick="deleteActivity('<?= $day['date'] ?>', '<?= $activity['activity_id'] ?>')"
                                    title="Delete Activity">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <?php if (!empty($activity['description'])): ?>
                        <div class="activity-description">
                            <?= nl2br(esc($activity['description'])) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($activity['learning_objective'])): ?>
                        <div class="activity-objective small text-muted mt-1">
                            <i class="fa fa-bullseye"></i> <strong>Objective:</strong> <?= esc($activity['learning_objective']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($activity['materials']) && is_array($activity['materials'])): ?>
                        <div class="activity-materials small text-muted mt-1">
                            <i class="fa fa-cubes"></i> <strong>Materials:</strong> <?= implode(', ', array_map('esc', $activity['materials'])) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($activity['video_task']['enabled']) || !empty($activity['audio_task']['enabled']) || !empty($activity['group_activity']['enabled'])): ?>
                        <div class="activity-tasks mt-2">
                            <?php if (!empty($activity['video_task']['enabled'])): ?>
                                <span class="task-badge video-task" title="Video Task">
                                    <i class="fa fa-video-camera"></i> Video Task
                                    <?php if (!empty($activity['video_task']['caption'])): ?>
                                        <small class="text-muted d-block ms-4"><?= esc($activity['video_task']['caption']) ?></small>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($activity['audio_task']['enabled'])): ?>
                                <span class="task-badge audio-task" title="Audio Task">
                                    <i class="fa fa-headphones"></i> Audio Task
                                    <?php if (!empty($activity['audio_task']['caption'])): ?>
                                        <small class="text-muted d-block ms-4"><?= esc($activity['audio_task']['caption']) ?></small>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($activity['group_activity']['enabled'])): ?>
                                <span class="task-badge group-task" title="Group Activity">
                                    <i class="fa fa-users"></i> Group Work 
                                    (<?= $activity['group_activity']['group_size'] ?> students/group)
                                    <?php if (!empty($activity['group_activity']['instructions'])): ?>
                                        <small class="text-muted d-block ms-4"><?= esc($activity['group_activity']['instructions']) ?></small>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Requirements Row - Modern Toggle Switches -->
        <div class="requirements-row mb-3">
            <div class="d-flex flex-wrap gap-3 align-items-center">
                <div class="requirement-item">
                    <label class="switch-label">
                        <input type="checkbox" class="toggle-audio modern-toggle" <?= ($day['is_audio'] ?? 0) ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                        <span class="toggle-text"><i class="fa fa-microphone"></i> Audio</span>
                    </label>
                </div>
                <div class="requirement-item">
                    <label class="switch-label">
                        <input type="checkbox" class="toggle-video modern-toggle" <?= ($day['is_video'] ?? 0) ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                        <span class="toggle-text"><i class="fa fa-video-camera"></i> Video</span>
                    </label>
                </div>
                <div class="requirement-item">
                    <label class="switch-label">
                        <input type="checkbox" class="toggle-picture modern-toggle" <?= ($day['is_picture'] ?? 0) ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                        <span class="toggle-text"><i class="fa fa-camera"></i> Picture</span>
                    </label>
                </div>
                <div class="requirement-item">
                    <label class="switch-label">
                        <input type="checkbox" class="toggle-quiz modern-toggle" <?= ($day['is_quiz'] ?? 0) ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                        <span class="toggle-text"><i class="fa fa-question-circle"></i> Quiz</span>
                    </label>
                </div>
                <div class="requirement-divider"></div>
                <div class="requirement-item">
                    <label class="switch-label">
                        <input type="checkbox" class="toggle-book modern-toggle" <?= ($day['is_book'] ?? 1) ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                        <span class="toggle-text"><i class="fa fa-book"></i> Book</span>
                    </label>
                </div>
                <div class="requirement-item">
                    <label class="switch-label">
                        <input type="checkbox" class="toggle-notebook modern-toggle" <?= ($day['is_notebook'] ?? 1) ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                        <span class="toggle-text"><i class="fa fa-pencil-square-o"></i> Notebook</span>
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Audio Caption -->
        <div class="form-group-compact audio-caption-group" style="<?= ($day['is_audio'] ?? 0) ? '' : 'display:none' ?>">
            <label class="caption-label"><i class="fa fa-microphone text-danger"></i> Audio Task Caption</label>
            <input type="text" class="form-control task-caption-input audio-caption-input" 
                   placeholder="Describe what students should record..." 
                   value="<?= esc($day['audio_caption'] ?? '') ?>">
        </div>
        
        <!-- Video Caption -->
        <div class="form-group-compact video-caption-group" style="<?= ($day['is_video'] ?? 0) ? '' : 'display:none' ?>">
            <label class="caption-label"><i class="fa fa-video-camera text-primary"></i> Video Task Caption</label>
            <input type="text" class="form-control task-caption-input video-caption-input" 
                   placeholder="Describe what students should record on video..." 
                   value="<?= esc($day['video_caption'] ?? '') ?>">
        </div>
        
        <!-- Picture Caption -->
        <div class="form-group-compact picture-caption-group" style="<?= ($day['is_picture'] ?? 0) ? '' : 'display:none' ?>">
            <label class="caption-label"><i class="fa fa-camera text-success"></i> Picture Task Caption</label>
            <input type="text" class="form-control task-caption-input picture-caption-input" 
                   placeholder="Describe what students should take picture of..." 
                   value="<?= esc($day['picture_caption'] ?? '') ?>">
        </div>
        
        <!-- Quiz Selection -->
        <div class="form-group-compact quiz-group" style="<?= ($day['is_quiz'] ?? 0) ? '' : 'display:none' ?>">
            <label class="caption-label"><i class="fa fa-question-circle text-warning"></i> Select Quiz</label>
            <select class="form-control quiz-select-field">
                <option value="">-- Select Quiz --</option>
                <?php if (!empty($quizzes)): ?>
                    <?php foreach ($quizzes as $quiz): ?>
                        <option value="<?= $quiz['quiz_id'] ?>" <?= (($day['quiz_id'] ?? 0) == $quiz['quiz_id']) ? 'selected' : '' ?>>
                            <?= esc($quiz['title']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        
        <!-- Class Work -->
        <div class="form-group-compact mt-3">
            <label class="section-label"><i class="fa fa-chalkboard text-info"></i> Class Work</label>
            <textarea class="form-control plain-textarea classwork-textarea" rows="3" 
                      placeholder="Enter class work details..."><?= esc($day['other_detail'] ?? '') ?></textarea>
        </div>
        
        <!-- Homework -->
        <div class="form-group-compact mt-3">
            <label class="section-label"><i class="fa fa-home text-warning"></i> Homework</label>
            <textarea class="form-control plain-textarea homework-textarea" rows="3" 
                      placeholder="Enter homework details..."><?= esc($day['detail'] ?? '') ?></textarea>
        </div>
    </div>
</div>
<?php endforeach; ?>

<style>
/* Modern Toggle Switches */
.requirements-row {
    background: #f8f9fa;
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 15px;
}

.d-flex.gap-3 {
    gap: 20px;
}

.gap-2 {
    gap: 8px;
}

.requirement-item {
    display: inline-block;
}

.switch-label {
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
}

.modern-toggle {
    display: none;
}

.toggle-slider {
    width: 44px;
    height: 24px;
    background-color: #ccc;
    border-radius: 34px;
    position: relative;
    transition: all 0.3s ease;
    cursor: pointer;
    display: inline-block;
    margin-right: 8px;
}

.toggle-slider:before {
    content: "";
    position: absolute;
    width: 18px;
    height: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    border-radius: 50%;
    transition: transform 0.3s ease;
}

.modern-toggle:checked + .toggle-slider {
    background-color: #4f46e5;
}

.modern-toggle:checked + .toggle-slider:before {
    transform: translateX(20px);
}

.toggle-text {
    font-size: 13px;
    font-weight: 500;
    color: #333;
}

.toggle-text i {
    margin-right: 4px;
}

.requirement-divider {
    width: 1px;
    height: 30px;
    background-color: #ddd;
    margin: 0 5px;
}

/* Activity Styles */
.activities-section {
    background: #fff;
    border-radius: 8px;
    padding: 0;
    margin-bottom: 15px;
}

.activity-item {
    background: #f8f9fa;
    border-start: 4px solid #007bff;
    padding: 12px;
    margin-bottom: 10px;
    border-radius: 6px;
    transition: all 0.2s;
}

.activity-item:hover {
    background: #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.activity-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}

.activity-title {
    flex: 1;
}

.activity-name {
    font-weight: 600;
    color: #007bff;
    font-size: 14px;
    margin-left: 6px;
}

.activity-actions {
    flex-shrink: 0;
}

.activity-badge {
    font-size: 10px;
    padding: 2px 8px;
    border-radius: 12px;
    background: #6c757d;
    color: white;
    margin-left: 8px;
}

.activity-badge.discussion { background: #28a745; }
.activity-badge.group-work { background: #17a2b8; }
.activity-badge.presentation { background: #ffc107; color: #333; }
.activity-badge.lab { background: #fd7e14; }
.activity-badge.lecture { background: #6f42c1; }
.activity-badge.other { background: #6c757d; }

.activity-description {
    font-size: 13px;
    color: #555;
    margin: 8px 0;
    padding-left: 20px;
}

.activity-objective, .activity-materials {
    font-size: 12px;
    padding-left: 20px;
    margin: 4px 0;
}

.activity-tasks {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    margin-top: 10px;
    padding-left: 20px;
}

.task-badge {
    font-size: 11px;
    padding: 4px 10px;
    border-radius: 15px;
    background: #fff;
    border: 1px solid #ddd;
    display: inline-block;
}

.task-badge.video-task {
    background: #e3f2fd;
    border-color: #2196f3;
    color: #1976d2;
}

.task-badge.audio-task {
    background: #fce4ec;
    border-color: #e91e63;
    color: #c2185b;
}

.task-badge.group-task {
    background: #e8f5e9;
    border-color: #4caf50;
    color: #388e3c;
}

/* Header Add Activity Button */
.btn-add-activity-header {
    border: 1px solid #4f46e5;
    color: #4f46e5;
    background: transparent;
    transition: all 0.2s;
}

.btn-add-activity-header:hover {
    background: #4f46e5;
    color: white;
    border-color: #4f46e5;
}

/* Caption Labels */
.caption-label {
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 5px;
    display: block;
    color: #555;
}

/* Section Labels */
.section-label {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
    display: block;
    padding-bottom: 5px;
    border-bottom: 2px solid #e9ecef;
}

/* Subject Badge */
.subject-badge {
    background: #4f46e5;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    display: inline-block;
}

/* Form Controls */
.plain-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    resize: vertical;
    transition: border-color 0.2s;
}

.plain-textarea:focus {
    border-color: #4f46e5;
    outline: none;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
}

.task-caption-input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 13px;
    margin-bottom: 10px;
}

.task-caption-input:focus {
    border-color: #4f46e5;
    outline: none;
}

/* Card Header */
.cd-day-card .card-header {
    background: #f5f5f5;
    padding: 12px 15px;
    border-bottom: 1px solid #e0e0e0;
}

.cd-day-card {
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 20px;
}

/* Responsive */
@media (max-width: 768px) {
    .d-flex.gap-3 {
        flex-wrap: wrap;
        gap: 12px;
    }
    .requirement-divider {
        display: none;
    }
    .card-header > div {
        flex-wrap: wrap;
    }
    .activity-header {
        flex-direction: column;
    }
    .activity-actions {
        margin-top: 8px;
    }
    .activity-tasks {
        flex-direction: column;
        gap: 8px;
    }
}
</style>

<script>
$(function() {
    // Initialize activities data for each card
    $('.cd-day-card').each(function() {
        const $card = $(this);
        const activitiesData = $card.data('activities');
        if (activitiesData && Array.isArray(activitiesData)) {
            $card.data('activities', activitiesData);
        } else {
            $card.data('activities', []);
        }
    });
    
    // Toggle caption fields visibility
    $('.toggle-audio').on('change', function() {
        $(this).closest('.cd-day-card').find('.audio-caption-group').toggle($(this).is(':checked'));
        if (typeof window.triggerAutoSave === 'function') window.triggerAutoSave();
    });
    
    $('.toggle-video').on('change', function() {
        $(this).closest('.cd-day-card').find('.video-caption-group').toggle($(this).is(':checked'));
        if (typeof window.triggerAutoSave === 'function') window.triggerAutoSave();
    });
    
    $('.toggle-picture').on('change', function() {
        $(this).closest('.cd-day-card').find('.picture-caption-group').toggle($(this).is(':checked'));
        if (typeof window.triggerAutoSave === 'function') window.triggerAutoSave();
    });
    
    $('.toggle-quiz').on('change', function() {
        $(this).closest('.cd-day-card').find('.quiz-group').toggle($(this).is(':checked'));
        if (typeof window.triggerAutoSave === 'function') window.triggerAutoSave();
    });
    
    $('.toggle-book, .toggle-notebook').on('change', function() {
        if (typeof window.triggerAutoSave === 'function') window.triggerAutoSave();
    });
    
    // Caption inputs
    $('.task-caption-input').on('input', function() {
        if (typeof window.triggerAutoSave === 'function') window.triggerAutoSave();
    });
    
    // Quiz select
    $('.quiz-select-field').on('change', function() {
        if (typeof window.triggerAutoSave === 'function') window.triggerAutoSave();
    });
    
    // Textareas
    $('.plain-textarea').on('input', function() {
        if (typeof window.triggerAutoSave === 'function') window.triggerAutoSave();
    });
});
</script>