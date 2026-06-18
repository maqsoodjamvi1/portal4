<?php
/** Unified top bar — workspace controls, breadcrumbs, user menu */
$formattedDate = '';
$expiryPercentage = 100;
if (!empty($expiryInfo) && $curr_campus_id > 0 && !empty($expiryInfo['expiry_date'])) {
    $formattedDate = date('d M Y', strtotime($expiryInfo['expiry_date']));
    if (($expiryInfo['days_left'] ?? 0) > 0 && ($expiryInfo['days_left'] ?? 0) <= 365) {
        $expiryPercentage = max(0, min(100, (($expiryInfo['days_left'] / 365) * 100)));
    }
}
?>
<nav class="main-header navbar navbar-expand navbar-dark text-white admin-app-bar align-items-center">
  <div class="admin-bar-row">
    <div class="admin-bar-row__start">
      <ul class="navbar-nav admin-bar-menu">
        <li class="nav-item">
          <button type="button" class="nav-link border-0 bg-transparent admin-pushmenu-btn" data-widget="pushmenu" aria-label="Open navigation menu">
            <i class="fas fa-bars"></i>
          </button>
        </li>
      </ul>
    </div>

    <div class="admin-bar-row__workspace d-none d-lg-flex no-print">
      <?php if (!empty($showCampusSelector)): ?>
        <div class="admin-bar-field">
          <label for="campusID" class="admin-bar-field__label"><i class="fas fa-school"></i></label>
          <select name="campus_id" id="campusID" class="form-control form-control-sm admin-bar-select" aria-label="Select campus">
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
        <div class="admin-bar-field">
          <label for="sessionID" class="admin-bar-field__label"><i class="fas fa-calendar-alt"></i></label>
          <select name="session_id" id="sessionID" class="form-control form-control-sm admin-bar-select admin-bar-select--session" aria-label="Select academic session">
            <?php foreach ($academic_sessions as $academic_session): ?>
              <option value="<?= esc($academic_session->session_id) ?>" <?= $curr_session_id === (int) $academic_session->session_id ? 'selected' : '' ?>>
                <?= esc($academic_session->session_name) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>

      <?php if (empty($curr_session_id) && !empty($canSelectSession)): ?>
        <span class="admin-bar-hint"><i class="fas fa-exclamation-triangle"></i> Select session</span>
      <?php endif; ?>
    </div>

    <button type="button"
            class="admin-workspace-toggle d-lg-none"
            id="adminWorkspaceToggle"
            aria-expanded="false"
            aria-controls="adminWorkspacePanel"
            aria-label="<?= esc($workspaceSummary, 'attr') ?>"
            title="<?= esc($workspaceSummary, 'attr') ?>">
      <i class="fas fa-layer-group" aria-hidden="true"></i>
      <span class="admin-workspace-toggle__label"><?= esc($workspaceSummary) ?></span>
    </button>

    <div class="admin-bar-row__search d-none d-md-flex align-items-center me-2">
      <button type="button"
              class="admin-bar-command-btn"
              id="adminCommandOpen"
              title="Search menu (Ctrl+K)"
              aria-label="Search menu">
        <i class="fas fa-search"></i>
        <span class="admin-bar-command-btn__label">Search</span>
        <kbd class="admin-bar-command-btn__kbd">Ctrl K</kbd>
      </button>
    </div>

    <?php if (!empty($adminBreadcrumbs)): ?>
      <nav class="admin-app-breadcrumb d-none d-md-flex" aria-label="Breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
          <?php foreach ($adminBreadcrumbs as $crumb): ?>
            <?php
              $label = trim((string) ($crumb['label'] ?? ''));
              if ($label === '') {
                  continue;
              }
            ?>
            <?php if (!empty($crumb['active'])): ?>
              <li class="breadcrumb-item active text-white-50" aria-current="page"><?= esc($label) ?></li>
            <?php elseif (!empty($crumb['url'])): ?>
              <li class="breadcrumb-item">
                <a href="<?= esc($crumb['url']) ?>" class="text-white"><?= esc($label) ?></a>
              </li>
            <?php else: ?>
              <li class="breadcrumb-item text-white-50"><?= esc($label) ?></li>
            <?php endif; ?>
          <?php endforeach; ?>
        </ol>
      </nav>
    <?php endif; ?>

    <div class="admin-bar-row__end">
      <ul class="navbar-nav admin-bar-actions align-items-center">
        <?php if (!empty($expiryInfo) && $curr_campus_id > 0): ?>
          <li class="nav-item dropdown d-md-none">
            <button type="button"
                    class="admin-subscription-pill admin-subscription-pill--compact <?= esc($expiryInfo['badge_class']) ?> dropdown-toggle border-0"
                    data-bs-toggle="dropdown"
                    aria-label="Subscription status"
                    title="<?= esc($expiryInfo['details'] ?? '', 'attr') ?>">
              <i class="fas <?= esc($expiryInfo['icon']) ?>"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end p-3" style="min-width:260px">
              <div class="text-center mb-2"><strong>Campus subscription</strong></div>
              <div class="mb-1"><strong>Expiry:</strong> <?= esc($formattedDate) ?></div>
              <div class="mb-2"><strong>Days left:</strong> <?= (int) ($expiryInfo['days_left'] ?? 0) ?></div>
              <?php helper('role'); if (userIsSuperAdmin() && hasPermission('admin-pay-campus-bill')): ?>
                <a href="<?= base_url('admin/pay_campus_bill') ?>" class="btn btn-sm btn-primary w-100">Renew</a>
              <?php endif; ?>
            </div>
          </li>
          <li class="nav-item dropdown d-none d-md-block me-2">
            <button type="button"
                    class="admin-subscription-pill <?= esc($expiryInfo['badge_class']) ?> dropdown-toggle border-0"
                    data-bs-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                    title="<?= esc($expiryInfo['details'] ?? '', 'attr') ?>">
              <i class="fas <?= esc($expiryInfo['icon']) ?>"></i>
              <span class="pill-text-long">
                <?php if ($expiryInfo['status'] === 'expired'): ?>
                  Expired
                <?php elseif ($expiryInfo['status'] === 'critical'): ?>
                  <?= (int) $expiryInfo['days_left'] ?> days left
                <?php elseif ($expiryInfo['status'] === 'warning'): ?>
                  Renews <?= esc($formattedDate) ?>
                <?php else: ?>
                  Active until <?= esc($formattedDate) ?>
                <?php endif; ?>
              </span>
            </button>
            <div class="dropdown-menu dropdown-menu-end p-3" style="min-width:280px">
              <div class="text-center mb-3">
                <strong><i class="fas fa-calendar-alt text-primary me-2"></i>Campus subscription</strong>
              </div>
              <div class="mb-2"><strong>Expiry:</strong> <?= esc($formattedDate) ?></div>
              <div class="mb-2">
                <strong>Days remaining:</strong>
                <?php if (($expiryInfo['days_left'] ?? 0) < 0): ?>
                  <span class="text-danger">Expired <?= abs((int) $expiryInfo['days_left']) ?> days ago</span>
                <?php else: ?>
                  <span class="text-success"><?= (int) $expiryInfo['days_left'] ?> days</span>
                <?php endif; ?>
              </div>
              <?php if (($expiryInfo['days_left'] ?? 0) > 0 && ($expiryInfo['days_left'] ?? 0) <= 365): ?>
                <div class="progress mb-2" style="height:8px">
                  <div class="progress-bar <?= $expiryInfo['status'] === 'critical' ? 'bg-danger' : ($expiryInfo['status'] === 'warning' ? 'bg-warning' : 'bg-success') ?>"
                       style="width:<?= $expiryPercentage ?>%"></div>
                </div>
              <?php endif; ?>
              <?php if (userIsSuperAdmin() && hasPermission('admin-pay-campus-bill')): ?>
                <div class="text-center mt-2">
                  <a href="<?= base_url('admin/pay_campus_bill') ?>" class="btn btn-sm btn-primary">Renew subscription</a>
                </div>
              <?php endif; ?>
            </div>
          </li>
        <?php endif; ?>

        <?php if (!empty($isDemoHost)): ?>
          <li class="nav-item d-none d-xl-flex me-2">
            <a href="<?= base_url('signup') ?>" class="btn btn-sm btn-flat btn-danger">Create your own school</a>
          </li>
        <?php endif; ?>

        <?php
          $__adminLang = session('language') ?? 'en';
          $__langLabels = ['en' => 'EN', 'ur' => 'UR', 'ar' => 'AR'];
        ?>
        <li class="nav-item language-switcher d-none d-md-flex align-items-center me-2">
          <div class="btn-group btn-group-sm admin-lang-pills" role="group" aria-label="<?= esc(lang('SchoolSetup.choose_language')) ?>">
            <button type="button" class="btn btn-flat <?= $__adminLang === 'en' ? 'btn-light' : 'btn-outline-light' ?>" onclick="changeLanguage('en')">EN</button>
            <button type="button" class="btn btn-flat <?= $__adminLang === 'ur' ? 'btn-light' : 'btn-outline-light' ?>" onclick="changeLanguage('ur')">UR</button>
            <button type="button" class="btn btn-flat <?= $__adminLang === 'ar' ? 'btn-light' : 'btn-outline-light' ?>" onclick="changeLanguage('ar')">AR</button>
          </div>
        </li>
        <li class="nav-item language-switcher dropdown d-md-none">
          <a class="nav-link admin-bar-icon-btn dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-label="<?= esc(lang('SchoolSetup.choose_language')) ?>">
            <i class="fas fa-globe" aria-hidden="true"></i>
            <span class="visually-hidden"><?= esc($__langLabels[$__adminLang] ?? 'EN') ?></span>
          </a>
          <div class="dropdown-menu dropdown-menu-end language-dropdown">
            <a class="dropdown-item language-option<?= $__adminLang === 'en' ? ' active' : '' ?>" href="#" onclick="changeLanguage('en'); return false;">English</a>
            <a class="dropdown-item language-option<?= $__adminLang === 'ur' ? ' active' : '' ?>" href="#" onclick="changeLanguage('ur'); return false;">اردو</a>
            <a class="dropdown-item language-option<?= $__adminLang === 'ar' ? ' active' : '' ?>" href="#" onclick="changeLanguage('ar'); return false;">العربية</a>
          </div>
        </li>

        <li class="nav-item d-none d-sm-flex">
          <a class="nav-link admin-bar-icon-btn admin-logout-link"
             href="<?= base_url('admin/logout') ?>"
             aria-label="Logout"
             title="Logout">
            <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
          </a>
        </li>

        <li class="dropdown user user-menu">
          <a class="nav-link admin-user-chip dropdown-toggle" href="#" data-bs-toggle="dropdown" data-toggle="dropdown" aria-expanded="false" aria-label="User menu">
            <?php
              $photoUrl = base_url('resource/adminlte/dist/img/emp-avatar.jpg');
              if (!empty($user) && !empty($user->photo)) {
                  $photoPath = FCPATH . 'uploads/employees/' . $user->photo;
                  if (file_exists($photoPath)) {
                      $photoUrl = base_url('uploads/employees/' . $user->photo);
                  }
              }
              $hasPhoto = ($photoUrl !== base_url('resource/adminlte/dist/img/emp-avatar.jpg'));
            ?>
            <?php if ($hasPhoto): ?>
              <img class="user-image admin-bar-avatar admin-user-chip__avatar" src="<?= $photoUrl ?>" alt="" />
            <?php else: ?>
              <span class="admin-user-chip__avatar admin-user-chip__avatar--fallback">
                <i class="fa fa-user" aria-hidden="true"></i>
              </span>
            <?php endif; ?>
            <span class="admin-user-chip__meta d-none d-md-flex">
              <span class="admin-user-chip__name"><?= !empty($user->username) ? esc($user->username) : 'User' ?></span>
              <?php if (!empty($role_name_info->rolename)): ?>
                <span class="admin-user-chip__role"><?= esc($role_name_info->rolename) ?></span>
              <?php endif; ?>
            </span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li class="user-header">
              <?php if ($hasPhoto): ?>
                <img src="<?= $photoUrl ?>" alt="" style="width:65px;height:65px;border-radius:50%;object-fit:cover" />
              <?php else: ?>
                <i class="fa fa-user fa-3x"></i>
              <?php endif; ?>
              <p><?= !empty($user->username) ? esc($user->username) : '' ?></p>
              <?php if (!empty($role_name_info->rolename)): ?>
                <small><?= esc($role_name_info->rolename) ?></small>
              <?php endif; ?>
            </li>
            <li class="user-footer border-top bg-light">
              <a href="<?= base_url('admin/profile') ?>" class="btn btn-sm btn-outline-secondary btn-flat"><i class="fa fa-gear"></i> Profile</a>
              <a href="<?= base_url('admin/logout') ?>" class="btn btn-sm btn-outline-danger btn-flat"><i class="fa fa-sign-out"></i> Logout</a>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
