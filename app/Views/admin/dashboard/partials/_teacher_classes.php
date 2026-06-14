<!-- Teacher: My Classes -->
<?php if ($isTeacher && !empty($teacherSections)): ?>
<?php
$orderedSections = ['ct' => [], 'other' => []];
foreach ($teacherSections as $section) {
    $sectionId = (int) ($section->cls_sec_id ?? 0);
    $isCt = isset($classTeacherMap[$sectionId])
        && (int) ($classTeacherMap[$sectionId]['id'] ?? 0) === (int) ($user_id ?? 0);
    $orderedSections[$isCt ? 'ct' : 'other'][] = $section;
}
$sortedSections = array_merge($orderedSections['ct'], $orderedSections['other']);
$pendingSectionIds = $teacherPendingSectionIds ?? [];
$today = $attendanceDate ?? date('Y-m-d');
?>
<section class="dash-section" id="teacher-classes-section">
    <div class="dash-section__head">
        <h2 class="dash-section__title"><i class="fas fa-chalkboard-teacher"></i> My Classes &amp; Subjects</h2>
        <span class="dash-section__line"></span>
        <span class="text-muted small"><?= count($sortedSections) ?> section(s)</span>
    </div>
    <div class="dash-class-grid">
        <?php foreach ($sortedSections as $section):
            $sectionSubjects = $subjectsPerSection[$section->cls_sec_id] ?? [];
            $isClassTeacher = false;
            $classTeacherName = 'Not Assigned';
            if (isset($classTeacherMap[$section->cls_sec_id])) {
                $classTeacherName = $classTeacherMap[$section->cls_sec_id]['name'];
                $isClassTeacher = ($classTeacherMap[$section->cls_sec_id]['id'] == ($user_id ?? 0));
            }
            $attPending = $isClassTeacher && in_array((int) $section->cls_sec_id, $pendingSectionIds, true);
        ?>
        <div class="dash-class-card <?= $isClassTeacher ? 'dash-class-card--ct' : '' ?>">
            <div class="dash-class-card__head">
                <div class="dash-class-card__title">
                    <i class="fas fa-users me-1"></i>
                    <?= esc($section->class_short_name ?? $section->class_name) ?> &mdash; <?= esc($section->section_short_name ?? $section->section_name) ?>
                </div>
                <div class="dash-class-card__teacher">
                    <i class="fas fa-user-tie"></i> <?= esc($classTeacherName) ?>
                    <?php if ($isClassTeacher): ?><span class="badge text-bg-light ms-1">You</span><?php endif; ?>
                </div>
            </div>
            <div class="dash-class-card__body">
                <?php if (!empty($sectionSubjects)): ?>
                <div class="dash-subject-tags">
                    <?php foreach ($sectionSubjects as $subject): ?>
                    <span class="dash-subject-tag" title="<?= esc($subject->subject_name) ?>"><?= esc($subject->subject_short_name ?? $subject->subject_name) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <span class="text-muted small">No subjects assigned</span>
                <?php endif; ?>
            </div>
            <div class="dash-class-card__foot dash-class-card__foot--wrap">
                <?php if (hasPermission('admin-classdairy') || hasPermission('admin-add-classdairy')): ?>
                <a href="<?= base_url('admin/classdiary/add?cls_sec_id=' . $section->cls_sec_id) ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-book-open"></i> Diary</a>
                <?php endif; ?>
                <?php if (hasPermission('admin-top-level-planning') || hasPermission('admin-add-top-level-planning')): ?>
                <a href="<?= base_url('admin/top_level_planning/add?class_id=' . $section->class_id) ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-layer-group"></i> Plan</a>
                <?php endif; ?>
                <?php if (hasPermission('admin-students-subject-results') || hasPermission('admin-add-students-subject-results')): ?>
                <a href="<?= base_url('admin/students-subject-results/add') ?>" class="btn btn-outline-info btn-sm"><i class="fas fa-poll"></i> Results</a>
                <?php endif; ?>
                <?php if ($isClassTeacher && (hasPermission('admin-add-student-absentees') || hasPermission('admin-db-attendance'))): ?>
                <a href="<?= base_url('admin/students_absentees/add?cls_sec_id=' . $section->cls_sec_id . '&date=' . urlencode($today)) ?>" class="btn btn-sm <?= $attPending ? 'btn-primary' : 'btn-outline-warning' ?>"><i class="fas fa-clipboard-check"></i> Attendance</a>
                <?php endif; ?>
                <?php if (hasPermission('admin-students') || hasPermission('admin-students-contact-list')): ?>
                <a href="<?= base_url('admin/students?status=1') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-user-graduate"></i> Students</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php elseif ($isTeacher && empty($teacherSections)): ?>
<section class="dash-section" id="teacher-classes-section">
    <div class="dash-panel">
        <div class="dash-panel__body text-center py-4">
            <i class="fas fa-info-circle text-warning fa-2x mb-2"></i>
            <p class="mb-0 text-muted">No classes assigned. Contact the administrator to assign your classes.</p>
        </div>
    </div>
</section>
<?php endif; ?>
