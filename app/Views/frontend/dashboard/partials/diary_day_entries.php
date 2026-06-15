<?php
helper('language');

$diaryEntries = $diaryEntries ?? [];
$diaryDate = (string) ($diaryDate ?? '');
$diaryDayName = (string) ($diaryDayName ?? '');
$showOuterCard = $showOuterCard ?? true;

$diarySubmissionStatus = static function (?string $s): string {
    $s = strtolower((string) $s);
    return match ($s) {
        'approved' => lang('ParentPortal.diary_status_approved'),
        'rejected' => lang('ParentPortal.diary_status_rejected'),
        'pending'  => lang('ParentPortal.diary_status_pending'),
        default    => $s !== '' ? esc(ucfirst($s)) : lang('ParentPortal.diary_status_pending'),
    };
};
?>

<?php if (! empty($showOuterCard)): ?>
<div class="card shadow-sm mb-3">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fa fa-calendar-day me-2 text-primary"></i>
            <?= esc($diaryDayName !== '' ? $diaryDayName : 'Diary') ?>
            <?php if ($diaryDate !== ''): ?>
                <small class="text-muted ms-2"><?= esc($diaryDate) ?></small>
            <?php endif; ?>
        </h5>
    </div>
    <div class="card-body">
<?php endif; ?>
        <?php if (! empty($diaryEntries)): ?>
            <div class="diary-accordion">
                <?php foreach ($diaryEntries as $diary): ?>
                    <div class="diary-card">
                        <div class="diary-card-header" data-card="<?= $diary['did'] ?>">
                            <span>
                                <i class="fa fa-graduation-cap me-2"></i>
                                <strong><?= esc($diary['subject_name'] ?? 'General') ?></strong>
                            </span>
                            <div class="d-flex align-items-center gap-2">
                                <?php if (! empty($diary['has_quiz'])): ?>
                                    <a href="<?= base_url('student/quizzes/start/' . $diary['quiz_id'] . '?sid=' . $activeStudentId) ?>"
                                       class="quiz-btn btn-sm" onclick="event.stopPropagation()">
                                        <i class="fa fa-play-circle me-1"></i> <?= lang('ParentPortal.diary_quiz') ?>
                                    </a>
                                <?php endif; ?>
                                <i class="fa fa-chevron-down toggle-icon"></i>
                            </div>
                        </div>

                        <div class="diary-card-body" data-body="<?= $diary['did'] ?>">
                            <div class="diary-section">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="content-box">
                                            <div class="section-title">
                                                <i class="fa fa-chalkboard text-primary"></i>
                                                <span><?= lang('ParentPortal.diary_class_work') ?></span>
                                            </div>
                                            <div class="content-text"><?= strip_tags($diary['classwork_formatted'] ?? '') ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="content-box">
                                            <div class="section-title">
                                                <i class="fa fa-pencil-alt text-warning"></i>
                                                <span><?= lang('ParentPortal.diary_homework') ?></span>
                                            </div>
                                            <div class="content-text"><?= strip_tags($diary['homework_formatted'] ?? '') ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if (! empty($diary['requires_audio']) || ! empty($diary['requires_video']) || ! empty($diary['requires_picture'])): ?>
                                <div class="diary-section">
                                    <div class="tasks-title">
                                        <i class="fa fa-tasks me-2"></i> <?= lang('ParentPortal.diary_required_tasks') ?>
                                    </div>
                                    <div class="row g-3">
                                        <?php if (! empty($diary['requires_audio'])): ?>
                                            <div class="col-md-4">
                                                <div class="task-box">
                                                    <div class="task-header">
                                                        <i class="fa fa-microphone text-danger"></i>
                                                        <span><?= lang('ParentPortal.diary_audio_task') ?></span>
                                                        <?php if (! empty($diary['audio_recordings'])): ?>
                                                            <span class="task-badge"><?= lang('ParentPortal.diary_n_uploaded', [count($diary['audio_recordings'])]) ?></span>
                                                        <?php endif; ?>
                                                    </div>

                                                    <?php if (! empty($diary['audio_caption'])): ?>
                                                        <div class="task-caption"><?= nl2br(esc($diary['audio_caption'])) ?></div>
                                                    <?php endif; ?>

                                                    <?php if (! empty($diary['audio_recordings'])): ?>
                                                        <?php foreach ($diary['audio_recordings'] as $audio): ?>
                                                            <div class="submission-item">
                                                                <audio controls class="submission-audio">
                                                                    <source src="<?= base_url($audio['audio_file_path']) ?>" type="audio/webm">
                                                                </audio>
                                                                <span class="badge <?= ($audio['status'] ?? '') === 'approved' ? 'bg-success' : (($audio['status'] ?? '') === 'rejected' ? 'bg-danger' : 'bg-warning') ?>">
                                                                    <?= $diarySubmissionStatus($audio['status'] ?? '') ?>
                                                                </span>
                                                            </div>
                                                            <?php if (! empty($audio['teacher_feedback'])): ?>
                                                                <small class="feedback-text"><?= esc($audio['teacher_feedback']) ?></small>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>

                                                    <div class="task-actions">
                                                        <button class="task-btn audio-record-btn" data-diary-id="<?= $diary['did'] ?>" data-student-id="<?= $activeStudentId ?>">
                                                            <i class="fa fa-microphone"></i> <?= lang('ParentPortal.diary_record') ?>
                                                        </button>
                                                        <button class="task-btn task-stop-btn audio-stop-btn" data-diary-id="<?= $diary['did'] ?>" style="display: none;">
                                                            <i class="fa fa-stop"></i> <?= lang('ParentPortal.diary_stop') ?>
                                                        </button>
                                                        <span class="audio-timer" id="audio-timer-<?= $diary['did'] ?>"></span>
                                                    </div>
                                                    <div id="upload-status-<?= $diary['did'] ?>" class="upload-status"></div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (! empty($diary['requires_video'])): ?>
                                            <div class="col-md-4">
                                                <div class="task-box">
                                                    <div class="task-header">
                                                        <i class="fa fa-video-camera text-primary"></i>
                                                        <span><?= lang('ParentPortal.diary_video_task') ?></span>
                                                        <?php if (! empty($diary['video_recordings'])): ?>
                                                            <span class="task-badge"><?= lang('ParentPortal.diary_n_uploaded', [count($diary['video_recordings'])]) ?></span>
                                                        <?php endif; ?>
                                                    </div>

                                                    <?php if (! empty($diary['video_caption'])): ?>
                                                        <div class="task-caption"><?= nl2br(esc($diary['video_caption'])) ?></div>
                                                    <?php endif; ?>

                                                    <?php if (! empty($diary['video_recordings'])): ?>
                                                        <?php foreach ($diary['video_recordings'] as $video): ?>
                                                            <div class="submission-item">
                                                                <video controls class="submission-video">
                                                                    <source src="<?= base_url($video['video_file_path']) ?>" type="video/mp4">
                                                                </video>
                                                                <span class="badge <?= ($video['status'] ?? '') === 'approved' ? 'bg-success' : (($video['status'] ?? '') === 'rejected' ? 'bg-danger' : 'bg-warning') ?>">
                                                                    <?= $diarySubmissionStatus($video['status'] ?? 'pending') ?>
                                                                </span>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>

                                                    <div class="task-actions">
                                                        <button class="task-btn video-record-btn" data-diary-id="<?= $diary['did'] ?>" data-student-id="<?= $activeStudentId ?>">
                                                            <i class="fa fa-video-camera"></i> <?= lang('ParentPortal.diary_record') ?>
                                                        </button>
                                                        <label class="task-btn task-upload">
                                                            <i class="fa fa-upload"></i> <?= lang('ParentPortal.diary_upload') ?>
                                                            <input type="file" accept="video/*" class="d-none video-file-input" data-diary-id="<?= $diary['did'] ?>" data-student-id="<?= $activeStudentId ?>">
                                                        </label>
                                                    </div>
                                                    <div class="video-status" id="video-status-<?= $diary['did'] ?>"></div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (! empty($diary['requires_picture'])): ?>
                                            <div class="col-md-4">
                                                <div class="task-box">
                                                    <div class="task-header">
                                                        <i class="fa fa-camera text-success"></i>
                                                        <span><?= lang('ParentPortal.diary_picture_task') ?></span>
                                                        <?php if (! empty($diary['picture_recordings'])): ?>
                                                            <span class="task-badge"><?= lang('ParentPortal.diary_n_uploaded', [count($diary['picture_recordings'])]) ?></span>
                                                        <?php endif; ?>
                                                    </div>

                                                    <?php if (! empty($diary['picture_caption'])): ?>
                                                        <div class="task-caption"><?= nl2br(esc($diary['picture_caption'])) ?></div>
                                                    <?php endif; ?>

                                                    <?php if (! empty($diary['picture_recordings'])): ?>
                                                        <?php foreach ($diary['picture_recordings'] as $picture): ?>
                                                            <div class="submission-item">
                                                                <img src="<?= base_url($picture['picture_path']) ?>" class="submission-image">
                                                                <span class="badge <?= ($picture['status'] ?? '') === 'approved' ? 'bg-success' : (($picture['status'] ?? '') === 'rejected' ? 'bg-danger' : 'bg-warning') ?>">
                                                                    <?= $diarySubmissionStatus($picture['status'] ?? 'pending') ?>
                                                                </span>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>

                                                    <div class="task-actions">
                                                        <button class="task-btn picture-capture-btn" data-diary-id="<?= $diary['did'] ?>" data-student-id="<?= $activeStudentId ?>">
                                                            <i class="fa fa-camera"></i> <?= lang('ParentPortal.diary_capture') ?>
                                                        </button>
                                                        <label class="task-btn task-upload">
                                                            <i class="fa fa-upload"></i> <?= lang('ParentPortal.diary_upload') ?>
                                                            <input type="file" accept="image/*" class="d-none picture-file-input" data-diary-id="<?= $diary['did'] ?>" data-student-id="<?= $activeStudentId ?>">
                                                        </label>
                                                    </div>
                                                    <div class="picture-status" id="picture-status-<?= $diary['did'] ?>"></div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-4 text-muted">
                <i class="fa fa-calendar-times fa-3x mb-3 opacity-50"></i>
                <h5><?= lang('ParentPortal.diary_empty_for_date') ?></h5>
                <p><?= lang('ParentPortal.diary_empty_help') ?></p>
            </div>
        <?php endif; ?>
<?php if (! empty($showOuterCard)): ?>
    </div>
</div>
<?php endif; ?>

