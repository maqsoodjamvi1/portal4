<?php
/** Mobile workspace panel — campus, session, subscription (tablet/phone). */
?>
<div class="admin-workspace-panel no-print" id="adminWorkspacePanel" aria-hidden="true">
  <?php if (!empty($showCampusSelector)): ?>
    <div class="form-group">
      <label for="campusIDMobile"><i class="fas fa-school me-1"></i> Campus</label>
      <select name="campus_id" id="campusIDMobile" class="form-control form-control-sm workspace-campus-select" aria-label="Select campus">
        <?php foreach ($campuses as $campus): ?>
          <?php
            $system_id   = $campus->system_id ?? $schoolinfo->system_id ?? '';
            $campus_code = $system_id . '-' . $campus->campus_id;
          ?>
          <option value="<?= (int) $campus->campus_id ?>" <?= $curr_campus_id === (int) $campus->campus_id ? 'selected' : '' ?>>
            <?= esc($campus->campus_name) ?> [<?= esc($campus_code) ?>]
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  <?php endif; ?>

  <?php if (!empty($canSelectSession)): ?>
    <div class="form-group mb-0">
      <label for="sessionIDMobile"><i class="fas fa-calendar-alt me-1"></i> Academic session</label>
      <select name="session_id" id="sessionIDMobile" class="form-control form-control-sm workspace-session-select" aria-label="Select academic session">
        <?php foreach ($academic_sessions as $academic_session): ?>
          <option value="<?= esc($academic_session->session_id) ?>" <?= $curr_session_id === (int) $academic_session->session_id ? 'selected' : '' ?>>
            <?= esc($academic_session->session_name) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  <?php endif; ?>

  <?php if (!empty($expiryInfo) && $curr_campus_id > 0): ?>
    <div class="mt-2 pt-2 border-top">
      <small class="text-muted d-block mb-1">Subscription</small>
      <span class="badge <?= esc($expiryInfo['badge_class']) ?>"><?= esc($expiryInfo['message'] ?? 'Unknown') ?></span>
    </div>
  <?php endif; ?>
</div>
