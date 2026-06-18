<?php
// Remove the old language setup and use CI4's lang() function
$school_name = isset($schoolinfo->system_name) ? $schoolinfo->system_name : 'School Name';
$current_language = session('language') ?? 'en';


helper('campus');

$__host = $_SERVER['HTTP_HOST'] ?? '';
$__isTrialHost = ($__host === 'trial.timesoftsol.com');
$__isDemoHost  = ($__host === 'demo.timesoftsol.com');
$__hasAppBanner = $__isTrialHost || $__isDemoHost;

$uiNeedsDataTables = $uiNeedsDataTables ?? true;
$uiNeedsSummernote   = $uiNeedsSummernote ?? false;
$uiNeedsChart        = $uiNeedsChart ?? false;
?>
<!DOCTYPE html>
<html lang="<?= $current_language ?>" dir="<?= in_array($current_language, ['ar', 'ur']) ? 'rtl' : 'ltr' ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $school_name; ?></title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/fontawesome-free/css/all.min.css') ?>">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Tempusdominus Bootstrap adapter -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css') ?>">
    <!-- JQVMap -->
    <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/jqvmap/jqvmap.min.css') ?>">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') ?>">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/daterangepicker/daterangepicker.css') ?>">
    <?php if ($uiNeedsSummernote): ?>
    <!-- summernote -->
    <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/summernote/summernote-lite.min.css') ?>">
    <?php endif; ?>
    <?php if ($uiNeedsDataTables): ?>
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <?php endif; ?>
    <!-- Select2 -->
    <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/select2/css/select2.min.css') ?>">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <!-- Toastr -->
    <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/toastr/toastr.min.css') ?>">

    <!-- RTL Support -->
    <?php if (in_array($current_language, ['ar', 'ur'])): ?>
      <link rel="stylesheet" href="<?= base_url('resource/adminlte/dist/css/adminlte.rtl.min.css') ?>">
    <?php else: ?>
      <link rel="stylesheet" href="<?= base_url('resource/adminlte/dist/css/adminlte.min.css') ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="<?= base_url('assets/js/sweetalert/sweetalert.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/design-tokens.css?v=20260616b') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin-shell.css?v=20260616e') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/report-ui.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components-ui.css?v=20260604') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/school-forms.css?v=20260616j') ?>">

    <script type="text/javascript">
      var BASE_URL   = '<?= base_url() ?>';
      var RELA_PATH  = './';
      var CURRENT_LANG = '<?= $current_language ?>';
      var LANG_URLS = {
        'en': '<?= base_url('language/set/en') ?>',
        'ur': '<?= base_url('language/set/ur') ?>',
        'ar': '<?= base_url('language/set/ar') ?>'
      };
    </script>

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <?= view('layouts/partials/admin_csrf_bootstrap') ?>
    <!-- jQuery UI 1.11.4 -->
    <script src="<?= base_url('resource/adminlte/plugins/jquery-ui/jquery-ui.min.js') ?>"></script>
    <script>
      $.widget.bridge('uibutton', $.ui.button)
    </script>
    <!-- Bootstrap 5 + legacy compatibility bridge -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('assets/js/bootstrap5-compat.js?v=20260616a') ?>"></script>
    <script src="<?= base_url('assets/js/report-ui.js') ?>"></script>
    <?php if ($uiNeedsChart): ?>
    <!-- ChartJS -->
    <script src="<?= base_url('resource/adminlte/plugins/chart.js/Chart.min.js') ?>"></script>
    <?php endif; ?>
    <!-- Sparkline -->
    <script src="<?= base_url('resource/adminlte/plugins/sparklines/sparkline.js') ?>"></script>
    <!-- JQVMap -->
    <script src="<?= base_url('resource/adminlte/plugins/jqvmap/jquery.vmap.min.js') ?>"></script>
    <script src="<?= base_url('resource/adminlte/plugins/jqvmap/maps/jquery.vmap.usa.js') ?>"></script>
    <!-- jQuery Knob Chart -->
    <script src="<?= base_url('resource/adminlte/plugins/jquery-knob/jquery.knob.min.js') ?>"></script>
    <!-- jquery-validation -->
    <script src="<?= base_url('resource/adminlte/plugins/jquery-validation/jquery.validate.min.js') ?>"></script>
    <script src="<?= base_url('resource/adminlte/plugins/jquery-validation/additional-methods.min.js') ?>"></script>
    <!-- daterangepicker -->
    <script src="<?= base_url('resource/adminlte/plugins/moment/moment.min.js') ?>"></script>
    <script src="<?= base_url('resource/adminlte/plugins/daterangepicker/daterangepicker.js') ?>"></script>
    <!-- Tempusdominus Bootstrap adapter -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script src="<?= base_url('assets/js/datetimepicker-compat.js?v=20260614') ?>"></script>
    <?php if ($uiNeedsSummernote): ?>
    <!-- Summernote -->
    <script src="<?= base_url('resource/adminlte/plugins/summernote/summernote-lite.min.js') ?>"></script>
    <?php endif; ?>
    <!-- overlayScrollbars -->
    <script src="<?= base_url('resource/adminlte/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') ?>"></script>
    <!-- AdminLTE App -->
    <script src="<?= base_url('resource/adminlte/dist/js/adminlte.js') ?>"></script>

    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/clockpicker/0.0.7/bootstrap-clockpicker.min.css">
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/clockpicker/0.0.7/bootstrap-clockpicker.min.js"></script>

    <!-- InputMask -->
    <script src="<?= base_url('resource/adminlte/plugins/moment/moment.min.js') ?>"></script>
    <script src="<?= base_url('resource/adminlte/plugins/inputmask/jquery.inputmask.min.js') ?>"></script>
    <!-- Select2 -->
    <script src="<?= base_url('resource/adminlte/plugins/select2/js/select2.full.min.js') ?>"></script>
    <!-- custom js libs -->
    <script type="text/javascript" src="<?= base_url('resource/arttemplate/template-native.js') ?>"></script>
    <script type="text/javascript" src="<?= base_url('resource/artdialog/dialog-plus-min.js') ?>"></script>
    <script type="text/javascript" src="<?= base_url('resource/js/jquery.cookie.js') ?>"></script>
    <script type="text/javascript" src="<?= base_url('resource/js/jquery.form.js') ?>"></script>
    <script type="text/javascript" src="<?= base_url('resource/js/bootbox.js') ?>"></script>
    <script type="text/javascript" src="<?= base_url('resource/toastr/toastr.min.js') ?>"></script>
    <script type="text/javascript" src="<?= base_url('resource/adminlte/plugins/fastclick/fastclick.js') ?>"></script>
    <script type="text/javascript" src="<?= base_url('resource/autosize/autosize.min.js') ?>"></script>

    <?php if ($uiNeedsDataTables): ?>
    <!-- DataTables  & Plugins -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="<?= base_url('assets/js/jquery.slugit.js') ?>"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="<?= base_url('resource/adminlte/plugins/jszip/jszip.min.js') ?>"></script>
    <script src="<?= base_url('resource/adminlte/plugins/pdfmake/pdfmake.min.js') ?>"></script>
    <script src="<?= base_url('resource/adminlte/plugins/pdfmake/vfs_fonts.js') ?>"></script>
    <script src="<?= base_url('resource/adminlte/plugins/datatables-buttons/js/buttons.html5.min.js') ?>"></script>
    <script src="<?= base_url('resource/adminlte/plugins/datatables-buttons/js/buttons.print.min.js') ?>"></script>
    <script src="<?= base_url('resource/adminlte/plugins/datatables-buttons/js/buttons.colVis.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/admin-datatable-ui.js?v=20260616a') ?>"></script>
    <?php endif; ?>
    <script src="<?= base_url('assets/js/sms-form-validation.js?v=20260604') ?>"></script>
    <script src="<?= base_url('assets/js/sms-modal-a11y.js?v=20260604') ?>"></script>
    <script src="<?= base_url('assets/js/sweetalert/sweetalert.js') ?>"></script>
<style type="text/css">/* Expiry notification animations */
@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.6; }
    100% { opacity: 1; }
}

.animated.pulse {
    animation: pulse 1.5s ease-in-out infinite;
}

.expiry-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 500;
    white-space: nowrap;
}

.expiry-badge.bg-danger {
    background-color: #dc3545 !important;
    color: white;
}

.expiry-badge.bg-warning {
    background-color: #ffc107 !important;
    color: #000;
}

.expiry-badge.bg-success {
    background-color: #28a745 !important;
    color: white;
}

.expiry-badge.bg-secondary {
    background-color: #6c757d !important;
    color: white;
}

.expiry-tooltip {
    cursor: help;
    border-bottom: 1px dotted;
}

</style>
<?php
// ==== Session + IDs (define these first) ====
$session         = session();
$curr_campus_id  = (int) ($session->get('member_campusid')  ?? 0);
$curr_session_id = (int) ($session->get('member_sessionid') ?? 0);

// ==== Campus flags (hifz only) â€” hostel/academy/transport modules removed ====
$hasTransport = false;
$hasAcademy   = false;
$hasHifz      = false;
$hasHostel    = false;
if ($curr_campus_id) {
    try {
        $db = \Config\Database::connect();
        if (in_array('hfz_flag', $db->getFieldNames('campus'), true)) {
            $flags = $db->table('campus')
                ->select('hfz_flag')
                ->where('campus_id', $curr_campus_id)
                ->get()
                ->getRow();
            if ($flags) {
                $hasHifz = ((int) ($flags->hfz_flag ?? 0) === 1);
            }
        }
    } catch (\Throwable $e) {
        $hasHifz = false;
    }
}

// ==== Dynamic metrics (badges) ====
helper('server');
$metrics = getAdminHeaderMetrics($curr_campus_id);
?>
    <?= view('layouts/partials/admin_shell_scripts') ?>
    <script>
      function changeLanguage(lang) {
          if (!LANG_URLS[lang]) {
              console.error('Language URL not found for:', lang);
              return;
          }

          const $switcher = $('.language-switcher .nav-link');
          const originalHtml = $switcher.html();
          $switcher.html('<i class="fas fa-spinner fa-spin me-1"></i> <?= lang("app.loading") ?>');

          $.ajax({
              url: LANG_URLS[lang],
              type: 'GET',
              dataType: 'json',
              success: function(response) {
                  if (response.success) {
                      if (typeof toastr !== 'undefined') {
                          toastr.success(response.message);
                      }
                      setTimeout(() => {
                          window.location.reload();
                      }, 500);
                  } else {
                      throw new Error(response.message);
                  }
              },
              error: function(xhr, status, error) {
                  console.error('Language change failed:', error);
                  $switcher.html(originalHtml);
                  if (typeof toastr !== 'undefined') {
                      toastr.error('<?= lang("app.language_changed") ?>');
                  }
              }
          });
      }

      $(document).ready(function(){
          $('.current-language').text(CURRENT_LANG.toUpperCase());
      });
    </script>
<?php
    $session = \Config\Services::session();
    $curr_campus_id = $session->get('member_campusid');
    $member_reg_text = $session->get('member_reg_text');
    if (empty($member_reg_text)) {
?>
    <script>
      $(document).ready(function(){
          <?php
$schoolInfo = getSchoolInfo();
$hasShortName = !empty($schoolInfo->short_name) || !empty($schoolInfo->reg_text);
$regYearExample = date('y');
?>

<?php if (!$hasShortName): ?>
    $("#schoolshortname").modal('show');
<?php endif; ?>

          var regYearExample = '<?= esc($regYearExample) ?>';
          var SETUP_I18N = <?= json_encode([
              'regTextInvalid' => lang('SchoolSetup.reg_text_invalid'),
          ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;

          function updateRegNoPreview() {
            var $input = $('#schoolshortname #reg_text');
            var code = ($input.val() || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
            $input.val(code);
            var displayCode = code || '___';
            $('#regNoPreview').text(regYearExample + '-' + displayCode + '-001');
          }

          $('#schoolshortname #reg_text').on('input', updateRegNoPreview);
          updateRegNoPreview();

          $('#updateRegText').click(function(){
            var reg_text = ($('#schoolshortname #reg_text').val() || '').trim().toUpperCase();
            var systemID = $('#systemID').val();

            if (!/^[A-Z0-9]{2,3}$/.test(reg_text)) {
              toastr.error(SETUP_I18N.regTextInvalid);
              return;
            }

            $.ajax({
              url: '<?=  base_url('admin/profile-system/update-reg-text') ?>',
              type: 'POST',
              data:{reg_text: reg_text, systemID: systemID},
              success:function(res){
                  var json = res;
                  if(json.success){
                      toastr.success(json.msg);
                      window.location.href = '<?= base_url('admin/getting-started') ?>';
                  }else{
                      toastr.error(json.msg);
                  }
              }
            });
         });
      });
    </script>
<?php } ?>
  </head>

  <body class="hold-transition sidebar-mini layout-fixed admin-shell-active<?= $__hasAppBanner ? ' has-app-banner' : '' ?> <?= in_array($current_language, ['ar', 'ur']) ? 'rtl-support' : '' ?>" dir="<?= in_array($current_language, ['ar', 'ur']) ? 'rtl' : 'ltr' ?>">
    <div class="wrapper">
    <div class="modal fade" id="schoolshortname" tabindex="-1" role="dialog" aria-labelledby="schoolShortNameModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <div>
              <h5 class="modal-title mb-0" id="schoolShortNameModalLabel"><?= lang('SchoolSetup.modal_title') ?></h5>
              <small class="text-muted"><?= esc($school_name) ?></small>
            </div>
            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span>&times;</span></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="systemID" id="systemID" value="<?php if (isset($schoolinfo) && is_object($schoolinfo)): ?><?= $schoolinfo->system_id ?><?php endif; ?>">
            <p class="text-muted mb-3"><?= lang('SchoolSetup.modal_explanation') ?></p>
            <div class="alert alert-info py-2 px-3 mb-3">
              <strong><?= lang('SchoolSetup.modal_format_label') ?></strong> <code><?= esc(date('y')) ?>-TSS-239</code>
              <div class="small mt-1 text-muted"><?= lang('SchoolSetup.modal_format_parts') ?></div>
            </div>
            <div class="form-group mb-2">
              <label for="reg_text"><?= lang('SchoolSetup.modal_label_reg_text') ?> <span class="text-danger">*</span></label>
              <input type="text" class="form-control text-uppercase" required name="reg_text" id="reg_text"
                     maxlength="3" placeholder="<?= esc(lang('SchoolSetup.modal_placeholder')) ?>" autocomplete="off" style="text-transform: uppercase;">
              <small class="form-text text-muted"><?= lang('SchoolSetup.modal_hint') ?></small>
            </div>
            <div class="small">
              <strong><?= lang('SchoolSetup.modal_preview') ?></strong> <code id="regNoPreview"><?= esc(date('y')) ?>-___-001</code>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" id="updateRegText" class="btn btn-primary"><?= lang('SchoolSetup.modal_save') ?></button>
          </div>
        </div>
      </div>
    </div>

    <?php include __DIR__ . '/partials/admin_shell_init.php'; ?>
    <?php include __DIR__ . '/partials/admin_banner.php'; ?>
    <?php include __DIR__ . '/partials/admin_setup_guide.php'; ?>

<?php
  helper('permission');

  $uri         = service('uri');
  $currentPath = trim($uri->getPath(), '/');

  // Sidebar shows ALL menu items: permission gating disabled at build time so
  // every section/item is constructed. (Display only — controllers still
  // enforce their own permission checks; Super Admins also bypass those.)
  $can = static function (string $perm): bool {
      return true;
  };
  $canAny = static function (array $perms) use ($can): bool {
      return true;
  };
  $isActive = static function (string $needle) use ($currentPath): bool {
      $needle = trim($needle, '/');
      return $needle !== '' && strpos($currentPath, $needle) === 0;
  };
  $link = static function (string $path): string {
      return base_url($path);
  };

  $sections = \App\Libraries\AdminMenuBuilder::build([
      'link'           => $link,
      'can'            => $can,
      'canAny'         => $canAny,
      'hasTransport'   => $hasTransport,
      'hasHostel'      => $hasHostel,
      'hasAcademy'     => $hasAcademy,
      'hasHifz'        => $hasHifz,
      'role_name_info' => $role_name_info ?? null,
  ]);

  helper('role');

  // Sidebar hide/show gating removed: every menu item is always visible.
  // (Permission/role/menu-prefs checks dropped — controllers still enforce access.)
  $menuItemVisible = static function (array $item): bool {
      return true;
  };

  $filterMenuChildren = static function (array $children, ?string $sectionKey = null) use ($menuItemVisible): array {
      $out           = [];
      $pendingHeader = null;

      foreach ($children as $ch) {
          if (! empty($ch['header'])) {
              $pendingHeader = $ch;
              continue;
          }
          if (! $menuItemVisible($ch)) {
              continue;
          }
          if ($pendingHeader !== null) {
              $out[]         = $pendingHeader;
              $pendingHeader = null;
          }
          $out[] = $ch;
      }

      return $out;
  };

  // Sidebar hide/show gating removed: every section is always visible.
  $menuSectionVisible = static function (array $sec): bool {
      return true;
  };

  $adminBreadcrumbs = \App\Libraries\AdminMenuBuilder::resolveBreadcrumb($currentPath, $sections);
  $adminNavIndex    = \App\Libraries\AdminMenuBuilder::flattenNavIndex($sections, $canAny, $menuItemVisible);
  $userMenuPrefsMap = $session->get('menu_prefs') ?? [];
  if ($userMenuPrefsMap === [] && (int) $session->get('member_userid') > 0) {
      $userMenuPrefsMap = \App\Libraries\UserMenuPrefsLibrary::loadMapForUser((int) $session->get('member_userid'));
      $session->set('menu_prefs', $userMenuPrefsMap);
  }
?>
<link rel="stylesheet" href="<?= base_url('assets/css/admin-command-palette.css?v=20260616b') ?>">

    <div class="admin-shell-headers no-print">
      <?php include __DIR__ . '/partials/admin_app_bar.php'; ?>
      <?php include __DIR__ . '/partials/admin_workspace_mobile.php'; ?>
    </div>

<?php
  // ===== Renderers =====
  $renderItem = function($item) use($isActive, $metrics, $menuItemVisible, $filterMenuChildren) {
      if (!empty($item['header'])) {
          return '<li class="nav-header text-xs text-muted">'.esc($item['label']).'</li>';
      }

      if (!empty($item['disabled'])) {
          return '';
      }

      if (! empty($item['children'])) {
          $parentKey = trim((string) ($item['key'] ?? ''));
          $item['children'] = $filterMenuChildren(
              $item['children'],
              $parentKey !== '' ? $parentKey : null
          );
      }

      $hasChildren = !empty($item['children']);
      if ($hasChildren === false && empty($item['url'])) {
          return '';
      }

      $selfBadge = 0;
      if (!empty($item['badge'])) {
          $key = is_array($item['badge']) ? ($item['badge']['key'] ?? null) : $item['badge'];
          $selfBadge = $key && isset($metrics[$key]) ? (int)$metrics[$key] : 0;
      }

      $sumChildren = !empty($item['badge_sum_children']) && $hasChildren;
      $childBadgeSum = 0;
      if ($sumChildren) {
          foreach ($item['children'] as $ch) {
              if (!empty($ch['badge'])) {
                  $k = is_array($ch['badge']) ? ($ch['badge']['key'] ?? null) : $ch['badge'];
                  if ($k && isset($metrics[$k])) $childBadgeSum += (int)$metrics[$k];
              }
          }
      }

      $active = $hasChildren
          ? (bool) array_filter($item['children'], fn($c)=>!empty($c['match']) && $isActive($c['match']))
          : (!empty($item['match']) && $isActive($item['match']));

      $liClass = 'nav-item'.($hasChildren ? ' has-treeview' : '').($active ? ' menu-open' : '');
      $aClass  = 'nav-link'.($active && !$hasChildren ? ' active' : '');
      $icon    = !empty($item['icon']) ? '<i class="nav-icon '.$item['icon'].'"></i>' : '<i class="nav-icon far fa-circle"></i>';

      $badgeHtml  = '';
      $badgeClass = 'text-bg-danger';
      if (!empty($item['badge']) && is_array($item['badge']) && !empty($item['badge']['class'])) {
          $badgeClass = $item['badge']['class'];
      }
      $displayCount = $sumChildren ? $childBadgeSum : $selfBadge;
      if ($displayCount > 0) {
          $badgeHtml = '<span class="right badge '.$badgeClass.'">'.$displayCount.'</span>';
      }

      $label = '<p title="'.esc($item['label'], 'attr').'">'.esc($item['label']).($hasChildren ? '<i class="right fas fa-angle-left"></i>' : '').$badgeHtml.'</p>';

      $liAttrs = 'class="'.$liClass.'"';
      if (!empty($item['key'])) {
          $liAttrs .= ' data-menu-key="'.esc($item['key'], 'attr').'"';
      }

      if ($hasChildren) {
          $html = '<li '.$liAttrs.'>
            <a href="#" class="nav-link'.($active ? ' active' : '').'">'.$icon.$label.'</a>
            <ul class="nav nav-treeview">';
          foreach ($item['children'] as $ch) {
              if (!empty($ch['header'])) {
                  $html .= '<li class="nav-header text-xs text-muted ps-3">'.esc($ch['label']).'</li>';
                  continue;
              }
              if (!empty($ch['disabled'])) {
                  continue;
              }
              if (! $menuItemVisible($ch)) {
                  continue;
              }
              $chActive = !empty($ch['match']) && $isActive($ch['match']);

              $childBadge = '';
              if (!empty($ch['badge'])) {
                  $ckey = is_array($ch['badge']) ? ($ch['badge']['key'] ?? null) : $ch['badge'];
                  $cnum = $ckey && isset($metrics[$ckey]) ? (int)$metrics[$ckey] : 0;
                  $cclass = 'text-bg-info';
                  if (is_array($ch['badge']) && !empty($ch['badge']['class'])) $cclass = $ch['badge']['class'];
                  if ($cnum > 0) $childBadge = '<span class="right badge '.$cclass.'">'.$cnum.'</span>';
              }

              $childIcon = !empty($ch['icon'])
                  ? '<i class="nav-icon '.$ch['icon'].'"></i>'
                  : '<span class="nav-icon nav-icon-blank"></span>';

              $childLiAttrs = 'class="nav-item"';
              if (!empty($ch['key'])) {
                  $childLiAttrs .= ' data-menu-key="'.esc($ch['key'], 'attr').'"';
              }

              $html .= '<li '.$childLiAttrs.'>
                          <a href="'.esc($ch['url']).'" class="nav-link'.($chActive?' active':'').'" title="'.esc($ch['label'], 'attr').'">
                            '.$childIcon.'
                            <p title="'.esc($ch['label'], 'attr').'">'.esc($ch['label']).$childBadge.'</p>
                          </a>
                        </li>';
          }
          $html .= '</ul></li>';
          return $html;
      } else {
          if (! $menuItemVisible($item)) {
              return '';
          }
          return '<li '.$liAttrs.'>
                    <a href="'.esc($item['url']).'" class="'.$aClass.'">
                      '.$icon.$label.'
                    </a>
                  </li>';
      }
  };
?>
<?php
  $sidebarWorkspaceTitle = trim((string) ($activeCampusLabel ?? ''));
  if ($sidebarWorkspaceTitle === '') {
      $sidebarWorkspaceTitle = trim((string) ($school_name ?? 'School workspace'));
  }
  $sidebarRoleLabel = trim((string) ($role_name_info->rolename ?? ''));
  $sidebarNavCount  = is_array($adminNavIndex ?? null) ? count($adminNavIndex) : 0;
?>

<aside class="main-sidebar sidebar-dark-orange elevation-4 sidebar-slim">
  <a href="<?= base_url('admin/dashboard') ?>" class="brand-link ts-brand-bar">
    <span class="brand-text fw-light"><?= esc($school_name ?? 'School Name') ?></span>
  </a>

  <div class="sidebar">
    <div class="sidebar-shell-top">
      <div class="image text-center sidebar-logo-wrap">
        <?php if(!empty($schoolinfo) && !empty($schoolinfo->logo)): ?>
          <img class="sidebar-logo" src="<?= base_url('system-logo/'.$schoolinfo->logo) ?>" alt="Logo">
        <?php else: ?>
          <div class="sidebar-logo sidebar-logo--fallback" aria-hidden="true">
            <i class="fas fa-school"></i>
          </div>
        <?php endif; ?>
      </div>

      <div class="sidebar-context-card">
        <div class="sidebar-context-card__eyebrow">School workspace</div>
        <div class="sidebar-context-card__title" title="<?= esc($sidebarWorkspaceTitle, 'attr') ?>">
          <?= esc($sidebarWorkspaceTitle) ?>
        </div>
        <div class="sidebar-context-card__subline" title="<?= esc($school_name ?? 'School Name', 'attr') ?>">
          <?= esc($sidebarRoleLabel !== '' ? $sidebarRoleLabel : ($school_name ?? 'School Name')) ?>
        </div>
        <div class="sidebar-context-card__chips">
          <?php if (!empty($activeSessionLabel)): ?>
            <span class="sidebar-context-card__chip">
              <i class="fas fa-calendar-alt" aria-hidden="true"></i>
              <span><?= esc($activeSessionLabel) ?></span>
            </span>
          <?php endif; ?>
          <span class="sidebar-context-card__chip sidebar-context-card__chip--quiet">
            <i class="fas fa-compass" aria-hidden="true"></i>
            <span><span id="sidebarVisibleLinkCount"><?= (int) $sidebarNavCount ?></span> items</span>
          </span>
        </div>
      </div>

      <?php if (empty($curr_session_id) && !empty($canSelectSession)): ?>
        <div class="sidebar-no-session-notice">
          <i class="fas fa-info-circle"></i>
          Choose an academic session in the workspace bar to unlock navigation.
        </div>
      <?php endif; ?>

      <div class="sidebar-controls no-print">
        <div class="sidebar-controls__row">
          <div class="sidebar-controls__search">
            <i class="fas fa-search sidebar-controls__search-icon" aria-hidden="true"></i>
            <input id="menuSearch"
                   class="form-control form-control-sidebar"
                   type="search"
                   placeholder="Find a menu item"
                   aria-label="Search menu">
            <button type="button"
                    id="menuSearchClear"
                    class="sidebar-controls__clear"
                    aria-label="Clear menu search"
                    hidden>
              <i class="fas fa-times" aria-hidden="true"></i>
            </button>
          </div>
        </div>
        <div class="sidebar-controls__hint">Use search to jump through the school menu quickly.</div>
      </div>
    </div>

    <nav class="mt-1 sidebar-nav">
      <div id="sidebarQuickAccess" class="sidebar-quick-access no-print" hidden>
        <div class="sidebar-quick-access__label">
          <i class="fas fa-thumbtack" aria-hidden="true"></i>
          <span>Quick access</span>
        </div>
        <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent text-sm mb-2" id="sidebarQuickAccessList"></ul>
      </div>
      <ul id="sidebarMenu" class="nav nav-pills nav-sidebar flex-column nav-child-indent text-sm" data-widget="treeview" role="menu" data-accordion="true">
        <?php
          foreach ($sections as $sec) {
            if (! $menuSectionVisible($sec)) {
                continue;
            }

            echo $renderItem($sec);
          }
        ?>
      </ul>
      <div id="sidebarMenuEmpty" class="sidebar-nav-empty" hidden>
        <div class="sidebar-nav-empty__icon">
          <i class="fas fa-search" aria-hidden="true"></i>
        </div>
        <div class="sidebar-nav-empty__title">No menu items found</div>
        <div class="sidebar-nav-empty__text">Try another keyword.</div>
      </div>
    </nav>
  </div>
</aside>

<?php include __DIR__ . '/partials/admin_mobile_nav.php'; ?>

<?php include __DIR__ . '/partials/admin_command_palette.php'; ?>

<script>
$(function () {
  var MENU_PREFS_KEY = 'menu_prefs_v2';
  var MENU_FAVORITES_KEY = 'menu_favorites_v1';
  var $menu        = $('#sidebarMenu');
  var $search      = $('#menuSearch');
  var $searchClear = $('#menuSearchClear');
  var $menuEmpty   = $('#sidebarMenuEmpty');
  var $sidebarRoot = $menu.closest('.sidebar');

  function loadPrefs() {
    // Hide/show menu preferences removed — never hide any menu item.
    return {};
    // eslint-disable-next-line no-unreachable
    var p = null;
    try { p = JSON.parse(localStorage.getItem(MENU_PREFS_KEY) || 'null'); }
    catch(e){ p = null; }

    if (!p || typeof p !== 'object' || p.__schema !== 2) {
      p = window.USER_MENU_PREFS || null;
    }

    if (!p || typeof p !== 'object' || p.__schema !== 2) {
      return {};
    }

    var out = $.extend({}, p);
    delete out.__schema;
    return out;
  }
  function storePrefs(prefs) {
    var payload = $.extend({ __schema: 2 }, prefs || {});
    try { localStorage.setItem(MENU_PREFS_KEY, JSON.stringify(payload)); } catch(e){}
    if (window.MENU_PREFS_SAVE_URL) {
      var hidden = [];
      Object.keys(prefs || {}).forEach(function (k) {
        if (prefs[k] === false) hidden.push(k);
      });
      $.ajax({
        url: window.MENU_PREFS_SAVE_URL,
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ hidden: hidden, prefs: prefs })
      });
    }
  }

  function loadFavorites() {
    try {
      var raw = localStorage.getItem(MENU_FAVORITES_KEY);
      var arr = raw ? JSON.parse(raw) : [];
      return Array.isArray(arr) ? arr : [];
    } catch (e) { return []; }
  }

  function renderQuickAccess() {
    var favs = loadFavorites();
    var $wrap = $('#sidebarQuickAccess');
    var $list = $('#sidebarQuickAccessList');
    $list.empty();
    if (!favs.length || !window.ADMIN_NAV_INDEX) {
      $wrap.attr('hidden', true);
      return;
    }
    var byKey = {};
    (window.ADMIN_NAV_INDEX || []).forEach(function (item) {
      if (item.key) byKey[item.key] = item;
    });
    var shown = 0;
    favs.forEach(function (key) {
      var item = byKey[key];
      if (!item || prefs[key] === false) return;
      shown++;
      $list.append(
        '<li class="nav-item" data-menu-key="' + $('<div>').text(key).html() + '">' +
        '<a href="' + item.url + '" class="nav-link" title="' + $('<div>').text(item.label).html() + '">' +
        '<i class="nav-icon ' + (item.icon || 'far fa-circle') + '"></i>' +
        '<p>' + $('<div>').text(item.label).html() + '</p></a></li>'
      );
    });
    $wrap.prop('hidden', shown === 0);
  }

  function syncVisibleHeaders() {
    var $lists = $menu.add($menu.find('ul.nav-treeview'));
    $lists.each(function () {
      var $list = $(this);
      $list.children('li.nav-header').each(function () {
        var $header = $(this);
        var hasVisibleGroupItems = $header
          .nextUntil('li.nav-header')
          .filter(':visible')
          .length > 0;
        $header.toggle(hasVisibleGroupItems);
      });
    });
  }

  function updateVisibleLinkCount() {
    var visibleCount = $menu.find('a.nav-link:visible').filter(function () {
      var href = ($(this).attr('href') || '').trim();
      return href !== '' && href !== '#' && href.toLowerCase().indexOf('javascript:') !== 0;
    }).length;
    $('#sidebarVisibleLinkCount').text(visibleCount);
  }

  function updateMenuEmptyState() {
    var hasVisibleLinks = $menu.find('a.nav-link:visible').filter(function () {
      var href = ($(this).attr('href') || '').trim();
      return href !== '' && href !== '#' && href.toLowerCase().indexOf('javascript:') !== 0;
    }).length > 0;
    $menuEmpty.prop('hidden', hasVisibleLinks);
  }

  function updateSearchUi(q) {
    var hasQuery = !!((q || '').trim());
    $searchClear.prop('hidden', !hasQuery);
    $sidebarRoot.toggleClass('sidebar-is-filtering', hasQuery);
  }

  function applyMenuPrefs(prefs) {
    // Show every menu item and section header (no hide/show prefs at all).
    $menu.find('li.nav-item, li.nav-header').show();

    syncVisibleHeaders();
    updateVisibleLinkCount();
    updateMenuEmptyState();
  }

  function setTreeOpenState() {
    $menu.find('li.has-treeview').removeClass('menu-open');
    $menu.find('ul.nav-treeview').hide();

    var $active = $menu.find('a.nav-link.active').first();
    if (!$active.length) {
      return;
    }

    var $topSection = $active.closest('#sidebarMenu > li.has-treeview');
    if ($topSection.length) {
      $topSection.addClass('menu-open');
      $topSection.children('ul.nav-treeview').show();
    }
  }

  function resetToPrefs() {
    applyMenuPrefs(prefs);
    setTreeOpenState();
    updateSearchUi('');
  }
  function performSearch(q) {
    q = (q || '').toLowerCase().trim();
    if (!q) { resetToPrefs(); return; }

    updateSearchUi(q);

    $menu.find('li').hide();
    $menu.find('ul.nav-treeview').hide();
    $menu.find('li.has-treeview').removeClass('menu-open');

    $menu.find('a.nav-link').filter(function () {
      return $(this).text().toLowerCase().indexOf(q) > -1;
    }).each(function () {
      var $a  = $(this);
      var $li = $a.closest('li.nav-item');
      var key = $li.data('menu-key') || $a.data('menu-key');
      var isBlockedByPrefs = $li.parents('li[data-menu-key]').addBack().filter(function () {
        var ancestorKey = $(this).data('menu-key');
        return ancestorKey && prefs[ancestorKey] === false;
      }).length > 0;

      if ((key && prefs[key] === false) || isBlockedByPrefs) return;

      $li.show();
      if ($li.hasClass('has-treeview')) {
        $li.addClass('menu-open');
        $li.children('ul.nav-treeview').show().children('li.nav-item').each(function () {
          var $child = $(this);
          var childKey = $child.data('menu-key');
          if (!childKey || prefs[childKey] !== false) {
            $child.show();
          }
        });
      }
      var $ul = $li.closest('ul.nav-treeview');
      if ($ul.length) {
        $ul.show().closest('.has-treeview').addClass('menu-open').show();
      }
    });

    syncVisibleHeaders();
    updateVisibleLinkCount();
    updateMenuEmptyState();
  }

  function initTogglesFromPrefs() {
    $('#menuPrefsModal .menu-item-toggle[data-key]').each(function () {
      var k = $(this).data('key');
      var show = (prefs[k] !== false);
      $(this).prop('checked', show);
    });
    updateMenuPrefsSummary();
  }

  function updateMenuPrefsSummary() {
    var $toggles = $('#menuPrefsModal .menu-item-toggle[data-key]');
    var total = $toggles.length;
    var visible = $toggles.filter(':checked').length;
    $('#menuPrefsVisibleCount').text(visible);
    $('#menuPrefsTotalCount').text(total);

    $('#menuPrefsModal .menu-prefs-group').each(function () {
      var $group = $(this);
      var $groupToggles = $group.find('.menu-item-toggle[data-key]');
      var gTotal = $groupToggles.length;
      var gVisible = $groupToggles.filter(':checked').length;
      $group.find('.menu-prefs-group__count').text(gVisible + '/' + gTotal);
      $group.toggleClass('menu-prefs-group--all-hidden', gTotal > 0 && gVisible === 0);
    });
  }

  function previewMenuPrefsFromModal() {
    $('#menuPrefsModal .menu-item-toggle[data-key]').each(function () {
      var k = $(this).data('key');
      prefs[k] = $(this).is(':checked');
    });
    applyMenuPrefs(prefs);
    setTreeOpenState();
    updateMenuPrefsSummary();
  }

  function expandPrefsGroup($group, expand) {
    var $body = $group.find('.menu-prefs-group__body');
    var $btn  = $group.find('.menu-prefs-group__toggle');
    if (expand) {
      $body.addClass('show');
      $btn.attr('aria-expanded', 'true');
      $group.addClass('menu-prefs-group--open');
    } else {
      $body.removeClass('show');
      $btn.attr('aria-expanded', 'false');
      $group.removeClass('menu-prefs-group--open');
    }
  }

  function expandActivePrefsGroup() {
    var $active = $menu.find('a.nav-link.active').first();
    $('#menuPrefsModal .menu-prefs-group').each(function () {
      expandPrefsGroup($(this), false);
    });
    if (!$active.length) {
      expandPrefsGroup($('#menuPrefsModal .menu-prefs-group').first(), true);
      return;
    }
    var activeKey = $active.closest('li.nav-item[data-menu-key]').data('menu-key');
    var $matchGroup = activeKey
      ? $('#menuPrefsModal .menu-prefs-group').filter(function () {
          return $(this).find('[data-key="' + activeKey + '"]').length > 0;
        }).first()
      : $();
    if (!$matchGroup.length) {
      $matchGroup = $('#menuPrefsModal .menu-prefs-group').first();
    }
    expandPrefsGroup($matchGroup, true);
  }

  var prefsSnapshot = {};
  var prefsSavedInModal = false;

  $(document).on('change', '#menuPrefsModal .menu-item-toggle[data-key]', function () {
    previewMenuPrefsFromModal();
  });

  $(document).on('click', '#menuPrefsSave', function () {
    var next = {};
    $('#menuPrefsModal .menu-item-toggle[data-key]').each(function () {
      var k = $(this).data('key');
      next[k] = $(this).is(':checked');
    });
    prefs = next;
    storePrefs(prefs);
    applyMenuPrefs(prefs);
    setTreeOpenState();
    renderQuickAccess();
    prefsSavedInModal = true;

    if ($.fn.modal) { $('#menuPrefsModal').modal('hide'); }
    if (window.toastr) { toastr.success('Menu preferences saved'); }
  });

  $(document).on('click', '#prefsShowAll', function(){
    $('#menuPrefsModal .menu-item-toggle').prop('checked', true);
    previewMenuPrefsFromModal();
  });
  $(document).on('click', '#prefsHideAll', function(){
    $('#menuPrefsModal .menu-item-toggle').prop('checked', false);
    previewMenuPrefsFromModal();
  });
  $(document).on('click', '#prefsReset', function(){
    prefs = {};
    initTogglesFromPrefs();
    applyMenuPrefs(prefs);
    setTreeOpenState();
    if (window.toastr) toastr.info('Menu preferences reset â€” click Save to keep');
  });

  $(document).on('click', '#prefsExpandAll', function () {
    $('#menuPrefsModal .menu-prefs-group').each(function () {
      expandPrefsGroup($(this), true);
    });
  });

  $(document).on('click', '#prefsCollapseAll', function () {
    $('#menuPrefsModal .menu-prefs-group').each(function () {
      expandPrefsGroup($(this), false);
    });
  });

  $(document).on('click', '.prefs-group-show', function () {
    $(this).closest('.menu-prefs-group').find('.menu-item-toggle').prop('checked', true);
    previewMenuPrefsFromModal();
  });

  $(document).on('click', '.prefs-group-hide', function () {
    $(this).closest('.menu-prefs-group').find('.menu-item-toggle').prop('checked', false);
    previewMenuPrefsFromModal();
  });

  $(document).on('click', '.menu-prefs-group__toggle', function () {
    var $group = $(this).closest('.menu-prefs-group');
    var isOpen = $group.hasClass('menu-prefs-group--open');
    expandPrefsGroup($group, !isOpen);
  });

  $(document).on('keyup', '#prefsSearch', function(){
    var q = $(this).val().toLowerCase().trim();
    if (!q) {
      $('#menuPrefsModal [data-item-row]').show();
      $('#menuPrefsModal .menu-prefs-subhead').show();
      $('#menuPrefsModal .menu-prefs-group').show();
      expandActivePrefsGroup();
      return;
    }

    $('#menuPrefsModal .menu-prefs-group').each(function () {
      var $group = $(this);
      var groupLabel = ($group.data('group-label') || '').toString();
      var groupMatch = groupLabel.indexOf(q) > -1;
      var itemMatch = false;

      $group.find('[data-item-row]').each(function () {
        var label = ($(this).data('label') || '').toString();
        var match = label.indexOf(q) > -1 || groupMatch;
        $(this).toggle(match);
        if (match) itemMatch = true;
      });

      $group.find('.menu-prefs-subhead').each(function () {
        var text = $(this).text().toLowerCase();
        $(this).toggle(text.indexOf(q) > -1 || itemMatch || groupMatch);
      });

      var showGroup = groupMatch || itemMatch;
      $group.toggle(showGroup);
      if (showGroup) {
        expandPrefsGroup($group, true);
      }
    });
  });

  $('#menuPrefsModal').on('show.bs.modal', function(){
    prefsSnapshot = $.extend({}, prefs);
    prefsSavedInModal = false;
  });

  $('#menuPrefsModal').on('shown.bs.modal', function(){
    initTogglesFromPrefs();
    $('#prefsSearch').val('');
    expandActivePrefsGroup();
  });

  $('#menuPrefsModal').on('hidden.bs.modal', function(){
    if (!prefsSavedInModal) {
      prefs = $.extend({}, prefsSnapshot);
      initTogglesFromPrefs();
      applyMenuPrefs(prefs);
      setTreeOpenState();
    }
  });

  $(document).on('click', '#menuPrefsCancel', function () {
    prefsSavedInModal = false;
  });

  $search.on('input', function () {
    performSearch($(this).val());
  });

  $searchClear.on('click', function () {
    $search.val('').trigger('input').trigger('focus');
  });

  var prefs = loadPrefs();
  applyMenuPrefs(prefs);
  setTreeOpenState();
  initTogglesFromPrefs();
  renderQuickAccess();
  updateSearchUi($search.val());

  $(document).on('dblclick', '#sidebarMenu a.nav-link[href]', function (e) {
    var key = $(this).closest('li[data-menu-key]').data('menu-key');
    if (!key) return;
    e.preventDefault();
    var favs = loadFavorites();
    var idx = favs.indexOf(key);
    if (idx > -1) {
      favs.splice(idx, 1);
      if (window.toastr) toastr.info('Removed from quick access');
    } else {
      if (favs.length >= 8) favs.shift();
      favs.push(key);
      if (window.toastr) toastr.success('Pinned to quick access');
    }
    try { localStorage.setItem(MENU_FAVORITES_KEY, JSON.stringify(favs)); } catch (err) {}
    renderQuickAccess();
  });

  /* Command palette */
  var $cmdPalette = $('#adminCommandPalette');
  var $cmdInput   = $('#adminCommandInput');
  var $cmdResults = $('#adminCommandResults');
  var cmdActive   = -1;

  function openCommandPalette() {
    $cmdPalette.removeAttr('hidden');
    $cmdInput.val('').trigger('focus');
    renderCommandResults('');
    cmdActive = -1;
  }

  function closeCommandPalette() {
    $cmdPalette.attr('hidden', true);
    cmdActive = -1;
  }

  function renderCommandResults(q) {
    q = (q || '').toLowerCase().trim();
    var items = (window.ADMIN_NAV_INDEX || []).filter(function (item) {
      if (!item.url || prefs[item.key] === false) return false;
      if (!q) return true;
      var hay = (item.label + ' ' + (item.section || '')).toLowerCase();
      return hay.indexOf(q) > -1;
    }).slice(0, 12);

    $cmdResults.empty();
    if (!items.length) {
      $cmdResults.append('<li class="px-3 py-2 text-muted small">No matching pages</li>');
      return;
    }

    items.forEach(function (item, idx) {
      $cmdResults.append(
        '<li role="option">' +
        '<a href="' + item.url + '" class="admin-command-palette__item' + (idx === cmdActive ? ' is-active' : '') + '" data-cmd-idx="' + idx + '">' +
        '<i class="' + (item.icon || 'far fa-circle') + '"></i>' +
        '<span>' + $('<div>').text(item.label).html() + '</span>' +
        '<span class="admin-command-palette__item-meta">' + $('<div>').text(item.section || '').html() + '</span>' +
        '</a></li>'
      );
    });
  }

  $(document).on('click', '#adminCommandOpen', openCommandPalette);
  $(document).on('click', '[data-cmd-close]', closeCommandPalette);

  $cmdInput.on('input', function () {
    cmdActive = -1;
    renderCommandResults($(this).val());
  });

  $cmdInput.on('keydown', function (e) {
    var $links = $cmdResults.find('a.admin-command-palette__item');
    if (e.key === 'Escape') {
      closeCommandPalette();
      return;
    }
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      cmdActive = Math.min(cmdActive + 1, $links.length - 1);
      $links.removeClass('is-active').eq(cmdActive).addClass('is-active');
      return;
    }
    if (e.key === 'ArrowUp') {
      e.preventDefault();
      cmdActive = Math.max(cmdActive - 1, 0);
      $links.removeClass('is-active').eq(cmdActive).addClass('is-active');
      return;
    }
    if (e.key === 'Enter' && cmdActive >= 0) {
      e.preventDefault();
      var href = $links.eq(cmdActive).attr('href');
      if (href) window.location.href = href;
    }
  });

  $(document).on('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      openCommandPalette();
    }
  });

  var TREE_OPEN_KEY = 'admin_sidebar_open_v1';
  function saveTreeOpenState() {
    var keys = [];
    $menu.find('> li.has-treeview.menu-open[data-menu-key]').each(function () {
      var k = $(this).data('menu-key');
      if (k) keys.push(k);
    });
    try { localStorage.setItem(TREE_OPEN_KEY, JSON.stringify(keys)); } catch (e) {}
  }
  function restoreTreeOpenState() {
    if ($search.val().trim()) return;
    try {
      var keys = JSON.parse(localStorage.getItem(TREE_OPEN_KEY) || '[]');
      if (!Array.isArray(keys) || !keys.length) return;
      keys.forEach(function (key) {
        var $li = $menu.find('> li.has-treeview[data-menu-key="' + key + '"]');
        if ($li.length) {
          $li.addClass('menu-open');
          $li.children('ul.nav-treeview').show();
        }
      });
    } catch (e) {}
  }

  $menu.on('click', '> li.has-treeview > a', function () {
    setTimeout(saveTreeOpenState, 80);
  });

  /* Re-sync after AdminLTE treeview init (window load) so only the active section stays open */
  $(window).on('load', function () {
    if (!$search.val().trim()) {
      setTreeOpenState();
      restoreTreeOpenState();
    }
    renderQuickAccess();
  });
});
</script>
