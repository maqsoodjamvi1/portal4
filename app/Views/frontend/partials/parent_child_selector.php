<?php
/**
 * Horizontal child chips for parent portal (same look as dashboard).
 *
 * @var list<array<string,mixed>> $children
 * @var int                        $activeStudentId
 * @var string                     $returnPath e.g. student/fees (no leading slash)
 */
if (empty($children)) {
    return;
}
helper(['server', 'url', 'language']);

$title = lang('ParentPortal.select_child');
$returnPath = trim(str_replace('\\', '/', (string) ($returnPath ?? 'student/dashboard')), '/');
?>
<div class="card shadow-sm mb-0 parent-dash-students-card parent-subpage-student-bar">
    <div class="card-header bg-white">
        <div class="select-children-label text-dark d-flex align-items-center justify-content-center flex-wrap">
            <i class="fa fa-users me-2 text-primary" aria-hidden="true"></i><?= esc($title) ?>
        </div>
    </div>
    <div class="card-body">
        <div class="kids-scroll">
            <div class="kids-scroll-track">
            <?php foreach ($children as $child): ?>
                <?php
                $sid       = (int) ($child['student_id'] ?? 0);
                $isActive  = $activeStudentId === $sid;
                $fullName  = trim((string) ($child['name'] ?? ''));
                if ($fullName === '') {
                    $fullName = trim(($child['first_name'] ?? '') . ' ' . ($child['last_name'] ?? ''));
                }
                $shortName = strlen($fullName) > 11 ? substr($fullName, 0, 10) . '...' : $fullName;
                $classDisplay = trim((string) ($child['class_display'] ?? ''));
                if ($classDisplay === '') {
                    $classDisplay = trim(($child['class_name'] ?? '') . ' ' . ($child['section_name'] ?? ''));
                }
                $photoUrl = $child['profile_photo_url'] ?? '';
                if ($photoUrl === '' || $photoUrl === base_url()) {
                    $photoUrl = getStudentPhotoUrl($child['profile_photo'] ?? '');
                }
                $age = '';
                if (! empty($child['date_of_birth'])) {
                    $dob   = new DateTime($child['date_of_birth']);
                    $today = new DateTime();
                    $age   = (string) $dob->diff($today)->y;
                }
                $href = base_url('student/switch/' . $sid . '?to=' . rawurlencode($returnPath));
                ?>
                <a href="<?= esc($href) ?>"
                   class="kid-card <?= $isActive ? 'active' : '' ?>"
                   title="<?= esc($fullName) ?>">
                    <?php if (! empty($photoUrl) && $photoUrl !== base_url()): ?>
                        <img src="<?= esc($photoUrl) ?>" alt="<?= esc($fullName) ?>" class="kid-avatar"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="kid-avatar-placeholder" style="display: none;">
                            <span><?= strtoupper(substr($fullName, 0, 1)) ?></span>
                        </div>
                    <?php else: ?>
                        <div class="kid-avatar-placeholder">
                            <span><?= strtoupper(substr($fullName, 0, 1)) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="kid-name"><?= esc($shortName) ?></div>
                    <div class="kid-class">
                        <?= esc($classDisplay) ?>
                        <?php if ($age !== '' && (int) $age > 0): ?>
                            <span class="kid-age">(<?= esc($age) ?>y)</span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
