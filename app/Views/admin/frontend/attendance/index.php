<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/parent_portal_subpages.css') ?>">
<?php helper('language'); ?>

<?php
$isParent = (($role ?? '') === 'parent');
$sessionInfo = $session_info ?? null;
$sessions = $sessions ?? [];
$currentSessionId = (int) ($current_session_id ?? 0);

$currentSessionBlock = null;
foreach ($sessions as $sb) {
    if (! empty($sb['is_current'])) {
        $currentSessionBlock = $sb;
        break;
    }
}

$codeClass = static function (?string $c): string {
    $c = $c ?? '';
    switch ($c) {
        case 'P':
            return 'att-code att-code--p';
        case 'A':
            return 'att-code att-code--a';
        case 'L':
            return 'att-code att-code--l';
        case 'LC':
            return 'att-code att-code--lc';
        case 'EL':
            return 'att-code att-code--el';
        default:
            return 'att-code att-code--empty';
    }
};
?>

<div class="content-header parent-subpage-breadcrumb-bar">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <ol class="breadcrumb float-sm-right mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('student/dashboard') ?>"><?= lang('ParentPortal.breadcrumb_dashboard') ?></a></li>
                    <li class="breadcrumb-item active"><?= lang('ParentPortal.breadcrumb_attendance') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content parent-subpage-content">
    <div class="container-fluid">
        <?php if ($isParent): ?>
            <?= view('frontend/partials/parent_child_selector', [
                'children'        => $children ?? [],
                'activeStudentId' => (int) (session('active_student_id') ?? 0),
                'returnPath'      => 'student/attendance',
            ]) ?>
        <?php endif; ?>

        <div class="parent-subpage-panel">
            <div class="parent-subpage-title-row flex-wrap">
                <div>
                    <h2 class="parent-subpage-title mb-1"><?= lang('ParentPortal.attendance_title') ?></h2>
                    <?php if (! empty($sessionInfo)): ?>
                        <?php
                        $sd = ! empty($sessionInfo['start_date']) ? strtotime(substr($sessionInfo['start_date'], 0, 10)) : false;
                        $ed = ! empty($sessionInfo['end_date']) ? strtotime(substr($sessionInfo['end_date'], 0, 10)) : false;
                        ?>
                        <p class="text-muted small mb-0">
                            <i class="far fa-calendar-alt me-1"></i>
                            <?= esc($sessionInfo['session_name'] ?? lang('ParentPortal.attendance_current_session')) ?>
                            <span class="mx-1">·</span>
                            <?= $sd ? esc(date('j M Y', $sd)) : esc($sessionInfo['start_date'] ?? '') ?>
                            <?= ' ' . esc(lang('ParentPortal.attendance_date_range_to')) . ' ' ?>
                            <?= $ed ? esc(date('j M Y', $ed)) : esc($sessionInfo['end_date'] ?? '') ?>
                            <span class="mx-1">·</span>
                            <?= lang('ParentPortal.attendance_mon_fri_only') ?>
                        </p>
                    <?php else: ?>
                        <p class="text-muted small mb-0"><?= lang('ParentPortal.attendance_no_session_note') ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="att-legend mb-4 d-flex flex-wrap align-items-center gap-3">
                <span class="text-muted small fw-bold text-uppercase"><?= lang('ParentPortal.attendance_legend_key') ?></span>
                <span><span class="<?= $codeClass('P') ?> att-code--sm">P</span> <?= lang('ParentPortal.attendance_legend_present') ?></span>
                <span><span class="<?= $codeClass('A') ?> att-code--sm">A</span> <?= lang('ParentPortal.attendance_legend_absent') ?></span>
                <span><span class="<?= $codeClass('L') ?> att-code--sm">L</span> <?= lang('ParentPortal.attendance_legend_leave') ?></span>
                <span><span class="<?= $codeClass('LC') ?> att-code--sm">LC</span> <?= lang('ParentPortal.attendance_legend_late') ?></span>
                <span><span class="<?= $codeClass('EL') ?> att-code--sm">EL</span> <?= lang('ParentPortal.attendance_legend_early_leave') ?></span>
                <span class="text-muted small"><?= lang('ParentPortal.attendance_legend_no_record') ?></span>
            </div>

            <?php if ($currentSessionBlock && ! empty($currentSessionBlock['terms'])): ?>
                <h3 class="h6 text-uppercase text-muted fw-bold mb-3"><?= lang('ParentPortal.attendance_current_overview') ?></h3>
                <div class="row">
                    <?php foreach ($currentSessionBlock['terms'] as $ct): ?>
                        <?php $sum = $ct['summary'] ?? []; ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card att-kpi-card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <div class="fw-bold text-dark"><?= esc($ct['term_name'] ?? '') ?></div>
                                            <?php if (! empty($ct['start_date']) && ! empty($ct['end_date'])): ?>
                                                <div class="text-muted small"><?= esc(substr($ct['start_date'], 0, 10)) ?> → <?= esc(substr($ct['end_date'], 0, 10)) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (($sum['present_pct'] ?? null) !== null): ?>
                                            <div class="att-kpi-pill text-success"><?= esc($sum['present_pct']) ?>%</div>
                                        <?php endif; ?>
                                    </div>
                                    <?= view('frontend/attendance/_term_summary', ['summary' => $sum]) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="parent-subpage-panel mt-3">
            <div class="parent-subpage-title-row flex-wrap">
                <div>
                    <h3 class="h5 mb-1"><?= lang('ParentPortal.attendance_history_title') ?></h3>
                    <p class="text-muted small mb-0"><?= lang('ParentPortal.attendance_history_help') ?></p>
                </div>
            </div>

            <?php if (empty($sessions)): ?>
                <div class="alert alert-info mb-0">No class/session history found for this student.</div>
            <?php else: ?>
                <div class="accordion mt-3" id="attSessionsAcc">
                    <?php foreach ($sessions as $s): ?>
                        <?php
                        $sessId = (int) ($s['session_id'] ?? 0);
                        $sessKey = 'sess_' . $sessId;
                        $sessLabel = trim(($s['class_short'] ?? '') . ' ' . ($s['section_name'] ?? ''));
                        $sessLabel = $sessLabel !== '' ? $sessLabel : 'Class Session';
                        $isCurrentSess = ! empty($s['is_current']);
                        ?>
                        <div class="card mb-2 border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-2 px-3" id="<?= esc($sessKey) ?>_h">
                                <button class="btn btn-link text-start p-0 w-100 d-flex justify-content-between align-items-center"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#<?= esc($sessKey) ?>_b"
                                        aria-expanded="false"
                                        aria-controls="<?= esc($sessKey) ?>_b">
                                    <div>
                                        <strong><?= esc($sessLabel) ?></strong>
                                        <?php if ($isCurrentSess): ?>
                                            <span class="badge text-bg-primary ms-2"><?= lang('ParentPortal.attendance_current_session') ?></span>
                                        <?php endif; ?>
                                        <div class="text-muted small">
                                            <?= esc($s['session_name'] ?? '') ?>
                                            <?php if (!empty($s['start_date']) && !empty($s['end_date'])): ?>
                                                <span class="mx-1">·</span><?= esc(substr($s['start_date'], 0, 10)) ?> → <?= esc(substr($s['end_date'], 0, 10)) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-down text-muted"></i>
                                </button>
                            </div>
                            <div id="<?= esc($sessKey) ?>_b" class="collapse" aria-labelledby="<?= esc($sessKey) ?>_h" data-bs-parent="#attSessionsAcc">
                                <div class="card-body py-3 px-3">
                                    <?php $terms = $s['terms'] ?? []; ?>
                                    <?php if (empty($terms)): ?>
                                        <div class="text-muted small">No terms found for this session.</div>
                                    <?php else: ?>
                                        <?php foreach ($terms as $t): ?>
                                            <?php
                                            $tsid = (int) ($t['term_session_id'] ?? 0);
                                            $termKey = 'term_' . $sessId . '_' . $tsid;
                                            $sum = $t['summary'] ?? [];
                                            ?>
                                            <div class="card mb-3 border att-term-card">
                                                <div class="card-body py-3">
                                                    <div class="d-flex flex-wrap justify-content-between align-items-start mb-2">
                                                        <div>
                                                            <strong class="text-dark"><?= esc($t['term_name'] ?? 'Term') ?></strong>
                                                            <?php if (!empty($t['start_date']) && !empty($t['end_date'])): ?>
                                                                <div class="text-muted small"><?= esc(substr($t['start_date'], 0, 10)) ?> → <?= esc(substr($t['end_date'], 0, 10)) ?></div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if ($isCurrentSess): ?>
                                                            <button class="btn btn-sm btn-outline-primary d-inline-flex align-items-center"
                                                                    type="button"
                                                                    data-bs-toggle="collapse"
                                                                    data-bs-target="#<?= esc($termKey) ?>_b"
                                                                    aria-expanded="false"
                                                                    aria-controls="<?= esc($termKey) ?>_b">
                                                                <i class="fas fa-calendar-week me-2"></i><?= lang('ParentPortal.attendance_weekly_detail') ?>
                                                                <i class="fas fa-chevron-down ms-2 small text-muted"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>

                                                    <?= view('frontend/attendance/_term_summary', ['summary' => $sum]) ?>

                                                    <?php if (! $isCurrentSess): ?>
                                                        <p class="text-muted small mb-0 mt-2"><i class="fas fa-info-circle me-1"></i><?= lang('ParentPortal.attendance_past_no_detail') ?></p>
                                                    <?php else: ?>
                                                        <div id="<?= esc($termKey) ?>_b" class="collapse mt-3 pt-3 border-top">
                                                            <div class="att-term-weeks"
                                                                 data-loaded="0"
                                                                 data-allow-detail="1"
                                                                 data-term-session-id="<?= (int) $tsid ?>"></div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
(function(){
  var fallbackMsg = <?= json_encode(lang('ParentPortal.attendance_detail_unavailable')) ?>;
  function fetchTermWeeks(termSessionId, targetEl) {
    var url = "<?= base_url('student/attendance/term-weeks') ?>/" + termSessionId;
    return fetch(url, { credentials: 'same-origin' })
      .then(function(r){ return r.json(); })
      .then(function(j){
        if (!j || !j.ok) {
          var m = (j && j.message) ? j.message : fallbackMsg;
          throw new Error(m);
        }
        targetEl.innerHTML = j.html || '';
        targetEl.dataset.loaded = "1";
      });
  }

  document.addEventListener('shown.bs.collapse', function (evt) {
    var body = evt.target;
    if (!body || !body.id) return;
    var wrap = body.querySelector('.att-term-weeks[data-term-session-id]');
    if (!wrap) return;
    if (wrap.dataset.allowDetail !== '1') return;
    if (wrap.dataset.loaded === "1") return;
    var tsid = parseInt(wrap.dataset.termSessionId || wrap.getAttribute('data-term-session-id') || '0', 10);
    if (!tsid) return;
    wrap.innerHTML = '<div class="text-center py-3 text-muted"><i class="fas fa-spinner fa-spin me-2"></i></div>';
    fetchTermWeeks(tsid, wrap).catch(function(err){
      wrap.innerHTML = '<div class="alert alert-light border mb-0 text-muted small"><i class="fas fa-info-circle me-2"></i>' +
        (err && err.message ? err.message : fallbackMsg) + '</div>';
      wrap.dataset.loaded = "1";
    });
  });
})();
</script>

<?= $this->endSection() ?>
