<?php
// Remove the old language setup and use CI4's lang() function
$school_name = isset($schoolinfo->system_name) ? $schoolinfo->system_name : 'School Name';
$current_language = session('language') ?? 'en';
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
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') ?>">
    <!-- iCheck -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css') ?>">
    <!-- JQVMap -->
    <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/jqvmap/jqvmap.min.css') ?>">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') ?>">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/daterangepicker/daterangepicker.css') ?>">
    <!-- summernote -->
    <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/summernote/summernote-bs4.min.css') ?>">
    <!-- DataTables -->
    <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') ?>">
    <!-- Select2 -->
    <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/select2/css/select2.min.css') ?>">
    <!-- Toastr -->
    <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/toastr/toastr.min.css') ?>">

    <!-- RTL Support -->
    <?php if (in_array($current_language, ['ar', 'ur'])): ?>
      <link rel="stylesheet" href="<?= base_url('resource/adminlte/dist/css/adminlte.rtl.min.css') ?>">
    <?php else: ?>
      <link rel="stylesheet" href="<?= base_url('resource/adminlte/dist/css/adminlte.min.css') ?>">
    <?php endif; ?>

    <link rel="stylesheet" href="<?= base_url('assets/js/sweetalert/sweetalert.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">

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
    <!-- jQuery UI 1.11.4 -->
    <script src="<?= base_url('resource/adminlte/plugins/jquery-ui/jquery-ui.min.js') ?>"></script>
    <script>
      $.widget.bridge('uibutton', $.ui.button)
    </script>
    <!-- Bootstrap 4 -->
    <script src="<?= base_url('resource/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <!-- ChartJS -->
    <script src="<?= base_url('resource/adminlte/plugins/chart.js/Chart.min.js') ?>"></script>
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
    <!-- Tempusdominus Bootstrap 4 -->
    <script src="<?= base_url('resource/adminlte/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') ?>"></script>
    <!-- Summernote -->
    <script src="<?= base_url('resource/adminlte/plugins/summernote/summernote-bs4.min.js') ?>"></script>
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
    
    <script type="text/javascript" src="<?= base_url('resource/sammy/lib/min/sammy-latest.min.js') ?>"></script>
    <script type="text/javascript" src="<?= base_url('resource/js/server.js') ?>"></script>

    <!-- DataTables  & Plugins -->
    <script src="<?= base_url('resource/adminlte/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
    <script src="<?= base_url('resource/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
    <script src="<?= base_url('resource/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') ?>"></script>
    <script src="<?= base_url('resource/adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/jquery.slugit.js') ?>"></script>
    <script src="<?= base_url('resource/adminlte/plugins/datatables-buttons/js/dataTables.buttons.min.js') ?>"></script>
    <script src="<?= base_url('resource/adminlte/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') ?>"></script>
    <script src="<?= base_url('resource/adminlte/plugins/jszip/jszip.min.js') ?>"></script>
    <script src="<?= base_url('resource/adminlte/plugins/pdfmake/pdfmake.min.js') ?>"></script>
    <script src="<?= base_url('resource/adminlte/plugins/pdfmake/vfs_fonts.js') ?>"></script>
    <script src="<?= base_url('resource/adminlte/plugins/datatables-buttons/js/buttons.html5.min.js') ?>"></script>
    <script src="<?= base_url('resource/adminlte/plugins/datatables-buttons/js/buttons.print.min.js') ?>"></script>
    <script src="<?= base_url('resource/adminlte/plugins/datatables-buttons/js/buttons.colVis.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/sweetalert/sweetalert.js') ?>"></script>

<?php
// ==== Session + IDs (define these first) ====
$session         = session();
$curr_campus_id  = (int) ($session->get('member_campusid')  ?? 0);
$curr_session_id = (int) ($session->get('member_sessionid') ?? 0);

// ==== Campus flags (transport/hostel/academy) ====
$hasTransport = $hasHostel = $hasAcademy = false;
if ($curr_campus_id) {
    $db = \Config\Database::connect();
    $flags = $db->table('campus')
        ->select('t_flag, h_flag, a_flag')
        ->where('campus_id', $curr_campus_id)
        ->get()->getRow();
    if ($flags) {
        $hasTransport = ((int)($flags->t_flag ?? 0) === 1);
        $hasHostel    = ((int)($flags->h_flag ?? 0) === 1);
        $hasAcademy   = ((int)($flags->a_flag ?? 0) === 1);
    }
}

// ==== Dynamic metrics (badges) ====
$metrics = [
  'unread_messages'     => 0,
  'pending_emp_leaves'  => 0,
  'pending_std_leaves'  => 0,
  'unpaid_fee_chalans'  => 0,
];

$safeCount = static function (string $sql, array $binds = []): int {
    try {
        $db  = \Config\Database::connect();
        $q   = $db->query($sql, $binds);
        $row = $q ? $q->getRow() : null;
        return (int)($row->c ?? 0);
    } catch (\Throwable $e) {
        return 0;
    }
};

$metrics['unread_messages'] = $safeCount(
    "SELECT COUNT(*) c FROM messages 
     WHERE (is_read = 0 OR is_read IS NULL)
       AND (? = 0 OR campus_id = ?)",
    [$curr_campus_id, $curr_campus_id]
);

$metrics['pending_emp_leaves'] = $safeCount(
    "SELECT COUNT(*) c FROM employee_leaves 
     WHERE (status = 'Pending' OR status = 0 OR approved = 0 OR COALESCE(approved,0) = 0)
       AND (? = 0 OR campus_id = ?)",
    [$curr_campus_id, $curr_campus_id]
);

$metrics['pending_std_leaves'] = $safeCount(
    "SELECT COUNT(*) c FROM students_leaves 
     WHERE (status = 'Pending' OR status = 0 OR approved = 0 OR COALESCE(approved,0) = 0)
       AND (? = 0 OR campus_id = ?)",
    [$curr_campus_id, $curr_campus_id]
);

$metrics['unpaid_fee_chalans'] = $safeCount(
    "SELECT COUNT(*) c FROM fee_chalan 
     WHERE ( status = 'unpaid' )
       AND (? = 0 OR session_id = ?)
       AND (? = 0 OR campus_id  = ?)",
    [$curr_session_id, $curr_session_id, $curr_campus_id, $curr_campus_id]
);
?>
    <script type="text/javascript">
      $(document).ready(function(){
        $("#campusID").change(function(){
          var id = $(this).val();
          var dataString = 'id='+ id;
          $.ajax({
            type: "POST",
            url: "<?=  base_url('admin/ajax/change-campus') ?>",
            data: dataString,
            cache: false,
            success: function(html)
            {
              location.reload();
            }
          });
        });
      });
      $(document).ready(function(){
        $("#sessionID").change(function(){
          var id=$(this).val();
          var dataString = 'session_id='+ id;
          $.ajax({
            type: "POST",
            url: "<?=  base_url('admin/ajax/select-session') ?>",
            data: dataString,
            cache: false,
            success: function(html)
            {
              location.reload();
            }
          });
        });
      });
    </script>
    <script>
      function changeLanguage(lang) {
          if (!LANG_URLS[lang]) {
              console.error('Language URL not found for:', lang);
              return;
          }

          const $switcher = $('.language-switcher .nav-link');
          const originalHtml = $switcher.html();
          $switcher.html('<i class="fas fa-spinner fa-spin mr-1"></i> <?= lang("app.loading") ?>');

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
          $("#schoolshortname").modal('show');

          $('#updateRegText').click(function(){
            var reg_text = $('#reg_text').val();
            var systemID = $('#systemID').val();

            $.ajax({
              url: '<?=  base_url('admin/profile-system/update-reg-text') ?>',
              type: 'POST',
              data:{reg_text: reg_text,systemID:systemID},
              success:function(res){
                  var json = res;
                  if(json.success){
                      toastr.success(json.msg);
                      location.reload();
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

  <body class="hold-transition sidebar-mini layout-fixed <?= in_array($current_language, ['ar', 'ur']) ? 'rtl-support' : '' ?>">
    <div class="modal fade" id="schoolshortname" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title pull-left" id="exampleModalLabel"><?= $school_name; ?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="systemID" id="systemID" value="<?php if (isset($schoolinfo) && is_object($schoolinfo)): ?><?= $schoolinfo->system_id ?><?php endif; ?>">
            <div class="form-group">
              <label for="reg_text">School Short Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" required name="reg_text" id="reg_text" maxlength="3" value="">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" id="updateRegText" class="btn btn-primary">Save</button>
          </div>
        </div>
      </div>
    </div>

    <?php if($_SERVER['HTTP_HOST'] == 'trial.timesoftsol.com'){ ?>
      <div class="bg-warning text-white" style="position: relative;width: 100%;z-index: 1046;text-align: center;font-size: 16px;">
       Your trial period will expire in 30 Days.Pay your bill for live data.This data will not be available for live account.
      </div>
    <?php } ?>

    <div class="wrapper">
      <?php
        $db             = \Config\Database::connect();
        $session        = \Config\Services::session();
        $curr_campus_id = $session->get('member_campusid');

        $currentCampusBill = $db->query('SELECT * FROM campus_bills WHERE campus_id = ' . intval($curr_campus_id))->getRow();
        $plan_id           = $currentCampusBill->plan_id ?? 0;

        $builder  = $db->table('system_plans');
        $plan_info = $builder->where('plan_id', $plan_id)->get()->getRow();

        $userid   = $session->get('member_userid');
        $currentuserrole = $db->query("SELECT * FROM user_roles WHERE userID = " . floatval($userid) . " ORDER BY addDate ASC")->getRow();

        $role_name_info = null;
        if ($currentuserrole) {
            $role_builder = $db->table('role_name');
            $role_name_info = $role_builder->where('role_name_id', $currentuserrole->roleID)->get()->getRow();
        }
      ?>
      <!-- Navbar -->
      <nav class="main-header navbar navbar-expand text-white" style="background-color: #3c8dbc;padding: 0px;">
        <ul class="navbar-nav col-lg-12">
          <ul class="navbar-nav col-lg-8">
            <li class="nav-item d-sm-inline-block nav-link">
              <a style="padding-top: 5px;" class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block nav-link" style="font-size: 14px;">
              <a style="padding-top: 5px;" class="nav-link" href="#"><?php if($plan_info){ echo $plan_info->plan_name; } ?></a>
            </li>
            <?php if (hasPermission('admin-campus') && $schoolinfo->system_id != 60): ?>
              <li class="nav-item d-sm-inline-block nav-link col-lg-4 col-sm-4">
                <select name="campus_id" id="campusID" class="form-control">
                  <?php foreach($campuses as $campus): ?>
                    <option value="<?= $campus->campus_id ?>" <?= $curr_campus_id == $campus->campus_id ? 'selected' : '' ?>>
                      <?= esc($campus->campus_name) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </li>
            <?php endif; ?>

            <?php if(hasPermission('admin-view-global-session')): ?>
              <li class="nav-item d-sm-inline-block nav-link col-lg-3 col-sm-4">
                <select name="session_id" id="sessionID" class="form-control">
                  <?php foreach ($academic_sessions as $academic_session): ?>
                    <option value="<?= esc($academic_session->session_id) ?>" <?= ($curr_session_id == $academic_session->session_id) ? 'selected' : '' ?>>
                      <?= esc($academic_session->session_name) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </li>
            <?php endif; ?>

            <li class="nav-item d-sm-inline-block nav-link col-lg-3 col-sm-4">
              <?php
                helper('currency');
                $currs = currency()->listActive();
                $cur   = session('currency_code') ?? config('Currency')->defaultDisplay;
              ?>
              <form action="<?= base_url('settings/set-currency') ?>" method="post" class="ml-2">
                <?= csrf_field() ?>
                <select name="currency_code" onchange="this.form.submit()" class="form-control form-control-sm">
                  <?php foreach ($currs as $c): ?>
                    <option value="<?= esc($c['code']) ?>" <?= $cur === $c['code'] ? 'selected' : '' ?>>
                      <?= esc($c['code']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </form>
            </li>
          </ul>

          <ul class="navbar-nav ml-auto">
            <?php if($_SERVER['HTTP_HOST'] == 'demo.timesoftsol.com'){ ?>
              <li style="margin-right:60px;">
                <a style="padding: 6px 11px;" href="https://timesoftsol.com/signup/" class="btn btn-lg btn-flat btn-danger btn-block">Create Your Own School</a>
              </li>
            <?php } ?>

            <li class="dropdown user user-menu pull-right">
              <a class="nav-link" style="margin-top:5px;" href="javascript:;" data-toggle="dropdown">
                <?php if (!empty($user) && !empty($user->photo)): ?>
                  <img class="user-image" src="<?= base_url('admin/employees-img/' . $user->photo) ?>" />
                <?php else: ?>
                  <i class="fa fa-user"></i>
                <?php endif; ?>
                <span class="d-none d-sm-inline-block">
                  <?php if (!empty($user) && !empty($user->username)): ?>
                    <?= $user->username; ?> (<?= $role_name_info->rolename ?? '' ?>)
                  <?php endif; ?>
                </span>
              </a>
              <ul class="dropdown-menu">
                <li class="user-header d-none d-sm-block">
                  <?php if (!empty($user) && !empty($user->photo)): ?>
                    <img class="user-image" src="<?= base_url('admin/employees-img/' . $user->photo) ?>" />
                  <?php else: ?>
                    <i class="fa fa-user"></i>
                  <?php endif; ?>
                  <p><?= !empty($user->username) ? $user->username : '' ?></p>
                </li>
                <li class="user-footer">
                  <div class="pull-left d-none d-sm-block" style="float: left;">
                    <a href="<?= base_url('admin/profile') ?>" class="btn btn-default btn-flat">
                      <i class="fa fa-gear"></i> Profile
                    </a>
                  </div>
                  <div class="pull-right" style="float: right;">
                    <a href="<?= base_url('admin/logout') ?>" class="btn btn-default btn-flat">
                      <i class="fa fa-sign-out"></i> Logout
                    </a>
                  </div>
                </li>
              </ul>
            </li>
          </ul>

        </ul>
      </nav>

<?php
  $session         = \Config\Services::session();
  $db              = \Config\Database::connect();
  $curr_session_id = $session->get('member_sessionid') ?? null;
  $curr_campus_id  = (int) ($session->get('member_campusid') ?? 0);
  helper('permission');

  $hasTransport = $hasHostel = $hasAcademy = false;
  if ($curr_campus_id) {
      $flags = $db->table('campus')
          ->select('t_flag, h_flag, a_flag')
          ->where('campus_id', $curr_campus_id)
          ->get()->getRow();
      if ($flags) {
          $hasTransport = ((int)($flags->t_flag ?? 0) === 1);
          $hasHostel    = ((int)($flags->h_flag ?? 0) === 1);
          $hasAcademy   = ((int)($flags->a_flag ?? 0) === 1);
      }
  }

  $uri         = service('uri');
  $currentPath = trim($uri->getPath(), '/');

  $can = function(string $perm): bool {
      return function_exists('hasPermission') ? hasPermission($perm) : false;
  };
  $canAny = function(array $perms) use($can): bool {
      foreach ($perms as $p) { if ($can($p)) return true; }
      return false;
  };
  $isActive = function(string $needle) use($currentPath): bool {
      $needle = trim($needle, '/');
      return $needle !== '' && strpos($currentPath, $needle) === 0;
  };
  $link = function(string $path): string { return base_url($path); };

  // ===== Menu Schema =====
  $sections = [];

  // Dashboard
  $sections[] = [
    'key'   => 'dashboard',
    'label' => 'Dashboard',
    'icon'  => 'fas fa-tachometer-alt',
    'url'   => ('/admin/dashboard'),
    'match' => 'admin/dashboard',
    'visible' => true
  ];

  // Profiles
  $profiles = [
    ['key'=>'profiles.user-profile','label'=>'User Profile','icon'=>'fas fa-id-badge','url'=>('/admin/profile'),'match'=>'admin/profile','perms'=>[]],
    ['key'=>'profiles.campus-profile','label'=>'Campus Profile','icon'=>'fas fa-school','url'=>('/admin/profile-campus'),'match'=>'admin/profile-campus','perms'=>['admin-add-campus-profile']],
    ['key'=>'profiles.system-profile','label'=>'System Profile','icon'=>'fas fa-cogs','url'=>('/admin/profile-system'),'match'=>'admin/profile-system','perms'=>['admin-add-system-profile']],
  ];
  $sections[] = [
    'key' => 'profiles',
    'label'=>'Profiles',
    'icon'=>'fas fa-th',
    'children'=>$profiles,
    'visible'=> (bool) array_filter($profiles, fn($i)=>empty($i['perms']) || $canAny($i['perms']))
  ];

  // Sessions
  $sessionsItems = [
  [
  'key'   => 'sessions.calendar-builder',
  'label' => 'Calendar Builder',
  'icon'  => 'fa fa-project-diagram',
  'url'   => $link('admin/academic-calendar/builder'),
  'match' => 'admin/academic-calendar/builder',
  'perms' => ['admin-academic-session'],
],

    ['key'=>'sessions.academic-sessions','label'=>'Academic Sessions','icon'=>'fa fa-calendar','url'=>$link('admin/academic_session'),'match'=>'admin/academic_session','perms'=>['admin-academic-session']],
    ['key'=>'sessions.terms','label'=>'Terms','icon'=>'fa fa-list','url'=>$link('admin/terms'),'match'=>'admin/terms','perms'=>['admin-terms']],
    ['key'=>'sessions.term-sessions','label'=>'Term Sessions','icon'=>'fa fa-list','url'=>$link('admin/terms_session'),'match'=>'admin/terms_session','perms'=>['admin-terms-sessions']],
    ['key'=>'sessions.term-weeks','label'=>'Term Weeks','icon'=>'fa fa-list','url'=>$link('admin/term_weeks'),'match'=>'admin/term_weeks','perms'=>['admin-term-weeks']],
];

  $sections[] = [
    'key'=>'sessions',
    'label'=>'Sessions',
    'icon'=>'fas fa-cogs',
    'children'=>$sessionsItems,
    'visible'=> (bool) array_filter($sessionsItems, fn($i)=>$canAny($i['perms'] ?? []))
  ];

  // Classes
  $classesItems = [
    ['key'=>'classes.classes','label'=>'Classes','icon'=>'fa fa-list','url'=>$link('admin/classes'),'match'=>'admin/classes','perms'=>['admin-classes']],
    ['key'=>'classes.sections','label'=>'Sections','icon'=>'fa fa-flask','url'=>$link('admin/sections'),'match'=>'admin/sections','perms'=>['admin-sections']],
    ['key'=>'classes.class-sections','label'=>'Class Sections','icon'=>'fa fa-flask','url'=>$link('admin/class_section'),'match'=>'admin/class_section','perms'=>['admin-class-section']],
    ['key'=>'classes.subjects','label'=>'Subjects','icon'=>'fa fa-list','url'=>$link('admin/subjects'),'match'=>'admin/subjects','perms'=>['admin-subjects']],
    ['key'=>'classes.section-subjects','label'=>'Section Subjects','icon'=>'fa fa-list','url'=>$link('admin/section_subjects'),'match'=>'admin/section_subjects','perms'=>['admin-section-subjects']],
  ];
  $sections[] = [
    'key'=>'classes',
    'label'=>'Classes',
    'icon'=>'fa fa-list-alt',
    'children'=>$classesItems,
    'visible'=> (bool) array_filter($classesItems, fn($i)=>$canAny($i['perms']))
  ];

  // Students / Admissions
  $studentsItems = [
    ['key'=>'students.enrolled-print','label'=>'Enrolled Students (Print)','icon'=>'fas fa-users','url'=>$link('admin/students_print?status=1'),'match'=>'admin/students_print','perms'=>['admin-students']],
    ['key'=>'students.admission','label'=>'Admission','icon'=>'fas fa-user-plus','url'=>$link('admin/students/add'),'match'=>'admin/students/add','perms'=>['admin-students']],
    ['key'=>'students.add-bulk','label'=>'Add Bulk Students','icon'=>'fas fa-layer-group','url'=>$link('admin/addbulkstudents/add'),'match'=>'admin/addbulkstudents','perms'=>['admin-students']],
    ['key'=>'students.id-card','label'=>'Student ID Card','icon'=>'far fa-id-card','url'=>$link('admin/student_id_card'),'match'=>'admin/student_id_card','perms'=>['admin-student-id-cards']],
    ['key'=>'students.promotion','label'=>'Promotion','icon'=>'fas fa-angle-double-up','url'=>$link('admin/student_class'),'match'=>'admin/student_class','perms'=>['admin-student-class']],
    ['key'=>'students.attachment-types','label'=>'Attachment Types','icon'=>'fas fa-paperclip','url'=>$link('admin/attachment_types'),'match'=>'admin/attachment_types','perms'=>['admin-attachment-types']],
    ['key'=>'students.data-verification','label'=>'Data Verification Form','icon'=>'fas fa-user-check','url'=>$link('admin/student_data_verification_form'),'match'=>'admin/student_data_verification_form','perms'=>['admin-students']],
    ['key'=>'students.fee-verification','label'=>'Fee Verification Form','icon'=>'fas fa-file-invoice-dollar','url'=>$link('admin/student_data_verification_form/student_fee_verification'),'match'=>'admin/student_data_verification_form/student_fee_verification','perms'=>['admin-students']],
  ];
  $sections[] = [
    'key'=>'students',
    'label'=>'Students',
    'icon'=>'fas fa-user-graduate',
    'children'=>$studentsItems,
    'visible'=> (bool) array_filter($studentsItems, fn($i)=>$canAny($i['perms']))
  ];

  // Faculty
  $facultyItems = [
    ['key'=>'faculty.employees','label'=>'Employees','icon'=>'fa fa-user','url'=>$link('admin/users?status=1'),'match'=>'admin/users','perms'=>['admin-users']],
    ['key'=>'faculty.subject-teachers','label'=>'Subject Teachers','icon'=>'fa fa-book','url'=>$link('admin/teacher_subjects/add'),'match'=>'admin/teacher_subjects','perms'=>['admin-add-teacher-subject']],
    ['key'=>'faculty.section-incharges','label'=>'Section Incharges','icon'=>'fa fa-book','url'=>$link('admin/teacher_section/add'),'match'=>'admin/teacher_section','perms'=>['admin-add-teacher-section']],
    ['key'=>'faculty.employee-timing','label'=>'Employee Timing','icon'=>'fa fa-clock','url'=>$link('admin/emp_timing/add'),'match'=>'admin/emp_timing','perms'=>['admin-add-teacher-section']],
  ];
  $sections[] = [
    'key'=>'faculty',
    'label'=>'Faculty',
    'icon'=>'fa fa-user',
    'children'=>$facultyItems,
    'visible'=> (bool) array_filter($facultyItems, fn($i)=>$canAny($i['perms']))
  ];

  // Exams & Tests
  $examsItems = [
    ['key'=>'quiz.quiz','label'=>'Add Quiz','icon'=>'fa fa-list','url'=>$link('admin/quiz-ai'),'match'=>'admin/quiz-ai','perms'=>['admin-exams']],

    ['key'=>'exams.exam','label'=>'Exam','icon'=>'fa fa-list','url'=>$link('admin/exam'),'match'=>'admin/exam','perms'=>['admin-exams']],



    ['key'=>'exams.datesheet','label'=>'Date Sheet','icon'=>'fa fa-calendar','url'=>$link('admin/datesheet'),'match'=>'admin/datesheet','perms'=>['admin-datesheet']],
    ['key'=>'exams.results-add','label'=>'Results','icon'=>'fa fa-list','url'=>$link('admin/students-results/add'),'match'=>'admin/students-results','perms'=>['admin-students-results']],
    ['key'=>'exams.results-list','label'=>'Results List','icon'=>'fa fa-list','url'=>$link('admin/students-results-list'),'match'=>'admin/students-results-list','perms'=>['admin-students-results']],
    ['key'=>'exams.subject-results','label'=>'Subject Results','icon'=>'fa fa-list','url'=>$link('admin/students-subject-results/add'),'match'=>'admin/students-subject-results','perms'=>['admin-students-subject-results']],
    ['key'=>'exams.grades','label'=>'Grades','icon'=>'fa fa-list','url'=>$link('admin/grades/add'),'match'=>'admin/grades','perms'=>['admin-grades']],
    ['key'=>'exams.grading-policy','label'=>'Grading Policy','icon'=>'fa fa-list','url'=>$link('admin/grading-policy'),'match'=>'admin/grading-policy','perms'=>['admin-grading-policy']],

    [
    'key'   => 'quiz.play-admin',
    'label' => 'Play Quiz (Admin)',
    'icon'  => 'fa fa-gamepad',
    'url'   => $link('admin/quiz-assign'),
    'match' => 'admin/quiz-assign',
    'perms' => ['admin-classdairy']
],


  ];
  $testsItems = [
    ['key'=>'tests.results','label'=>'Add Tests Results','icon'=>'fa fa-list','url'=>$link('admin/test-results'),'match'=>'admin/test-results','perms'=>['admin-test-series']],
    ['key'=>'tests.series-result-card','label'=>'Tests Series Results Card','icon'=>'fa fa-list','url'=>$link('admin/test-series-result-card'),'match'=>'admin/test-series-result-card','perms'=>['admin-test-series']],
  ];
  $sections[] = [
    'key'=>'exams-tests',
    'label'=>'Exams & Tests',
    'icon'=>'fas fa-diagnoses',
    'children'=>array_merge($examsItems, $testsItems),
    'visible'=> (bool) array_filter(array_merge($examsItems, $testsItems), fn($i)=>$canAny($i['perms']))
  ];


$quizzesItems = [
    [
        'key'   => 'question-bank.question-bank',
        'label' => 'Question Bank',
        'icon'  => 'fa fa-list',
        'url'   => $link('admin/question-bank'),
        'match' => 'admin/question-bank',
        'perms' => ['admin-exams'],
    ],

    // ✅ NEW: Topic Manager
    [
        'key'   => 'question-bank.topics',
        'label' => 'QB Topics',
        'icon'  => 'fa fa-tags',
        'url'   => $link('admin/qb-topics'),
        'match' => 'admin/qb-topics',
        'perms' => ['admin-exams'],
    ],
    [
        'key'   => 'vocabulary-bank.topics',
        'label' => 'Vocab Topics',
        'icon'  => 'fa fa-tags',
        'url'   => $link('admin/vocab-topics'),
        'match' => 'admin/vocab-topics',
        'perms' => ['admin-exams'],
    ],

    [
        'key'   => 'vocab-bank.vocab-bank',
        'label' => 'Vocabulary Bank',
        'icon'  => 'fa fa-list',
        'url'   => $link('admin/vocab-bank'),
        'match' => 'admin/vocab-bank',
        'perms' => ['admin-exams'],
    ],

    [
        'key'   => 'vocab-bank.report',
        'label' => 'Vocabulary Report',
        'icon'  => 'fa fa-table',
        'url'   => $link('admin/vocab-bank/report'),
        'match' => 'admin/vocab-bank/report',
        'perms' => ['admin-exams'],
    ],

    [
        'key'   => 'quizzes.quizzes',
        'label' => 'Quizzes',
        'icon'  => 'fa fa-calendar',
        'url'   => $link('admin/quizzes'),
        'match' => 'admin/quizzes',
        'perms' => ['admin-datesheet'],
    ],
];

    
  
  $sections[] = [
    'key'=>'quizzes',
    'label'=>'Quizzes',
    'icon'=>'fas fa-diagnoses',
    'children'=>$quizzesItems,
    'visible'=> (bool) array_filter($quizzesItems, fn($i)=>$canAny($i['perms']))
  ];

  // Attendance
$attendanceItems = [
    ['key'=>'attendance.employees-attendance','label'=>'Employees Attendance','icon'=>'fa fa-cubes','url'=>$link('admin/employees_attendance/add'),'match'=>'admin/employees_attendance','perms'=>['admin-add-student-attendance']],
    ['key'=>'attendance.emp-leaves-add','label'=>'Create Employee Leaves','icon'=>'fa fa-cubes','url'=>$link('admin/employee_leaves/add'),'match'=>'admin/employee_leaves/add','perms'=>['admin-add-student-attendance']],
    ['key'=>'attendance.emp-leaves','label'=>'Employee Leaves Applications','icon'=>'fa fa-cubes','url'=>$link('admin/employee_leaves'),'match'=>'admin/employee_leaves','perms'=>['admin-add-student-attendance'],'badge'=>['key'=>'pending_emp_leaves','class'=>'badge-danger']],
    ['key'=>'attendance.emp-attendance-report','label'=>'Employees Attendance Report','icon'=>'fa fa-cubes','url'=>$link('admin/emp_attendance_monthlyreport'),'match'=>'admin/emp_attendance_monthlyreport','perms'=>['admin-emp-attendance-monthly-report']],
    
    // Student Reports Section - ADDED NEW ITEMS
    ['key'=>'attendance.student-monthly-report','label'=>'Students Monthly Report','icon'=>'fa fa-calendar-alt','url'=>$link('admin/attendance-monthly-report'),'match'=>'admin/attendance-monthly-report','perms'=>['admin-emp-attendance-monthly-report']],
    ['key'=>'attendance.student-session-report','label'=>'Students Session Report','icon'=>'fa fa-calendar','url'=>$link('admin/attendance-monthly-report/student-session-report'),'match'=>'admin/attendance-monthly-report/student-session-report','perms'=>['admin-attendance-monthly-report']],
    
    ['key'=>'attendance.absentees','label'=>'Absentees','icon'=>'far fa-clock','url'=>$link('admin/students_absentees/add'),'match'=>'admin/students_absentees','perms'=>['admin-add-student-absentees']],
    ['key'=>'attendance.std-leaves-add','label'=>'Create Leaves Applications','icon'=>'far fa-clock','url'=>$link('admin/students_leaves/add'),'match'=>'admin/students_leaves/add','perms'=>['admin-add-student-leaves']],
    ['key'=>'attendance.std-leaves','label'=>'Leaves Applications','icon'=>'far fa-clock','url'=>$link('admin/students_leaves'),'match'=>'admin/students_leaves','perms'=>['admin-student-leaves'],'badge'=>['key'=>'pending_std_leaves','class'=>'badge-danger']],
];
  $sections[] = [
    'key'=>'attendance',
    'label'=>'Attendance',
    'icon'=>'far fa-address-card',
    'children'=>$attendanceItems,
    'visible'=> (bool) array_filter($attendanceItems, fn($i)=>$canAny($i['perms']))
  ];

  // Time Table
  $timetableItems = [
    ['key'=>'timetable.timetable','label'=>'Time Table','icon'=>'far fa-clock','url'=>$link('admin/timetable/add'),'match'=>'admin/timetable','perms'=>['admin-timetable']],
    ['key'=>'timetable.school-timing','label'=>'School Timing','icon'=>'far fa-clock','url'=>$link('admin/school_timing/add'),'match'=>'admin/school_timing','perms'=>['admin-school-timing']],
    ['key'=>'timetable.timing-type','label'=>'School Timing Type','icon'=>'far fa-clock','url'=>$link('admin/school_timming_type'),'match'=>'admin/school_timming_type','perms'=>['admin-school-timing']],
    ['key'=>'timetable.slots','label'=>'Slots','icon'=>'far fa-clock','url'=>$link('admin/slots'),'match'=>'admin/slots','perms'=>['admin-slots']],
  ];
  $sections[] = [
    'key'=>'timetable',
    'label'=>'Time Table',
    'icon'=>'far fa-clock',
    'children'=>$timetableItems,
    'visible'=> (bool) array_filter($timetableItems, fn($i)=>$canAny($i['perms']))
  ];

  // Academics
 $academicsItems = [
    ['key'=>'academics.top-level-planning','label'=>'Top Level Planning','icon'=>'fa fa-list',
     'url'=>$link('admin/top_level_planning'),'match'=>'admin/top_level_planning',
     'perms'=>['admin-top-level-planning']],

    ['key'=>'academics.weekly-planning','label'=>'Weekly Planning','icon'=>'fa fa-list',
     'url'=>$link('admin/weekly_planning_view'),'match'=>'admin/weekly_planning_view',
     'perms'=>['admin-weekly-planning']],

    ['key'=>'academics.daily-diary','label'=>'Daily Diary','icon'=>'fa fa-list',
     'url'=>$link('admin/classdairy-view'),'match'=>'admin/classdairy-view',
     'perms'=>['admin-classdairy']],

    // ⭐ New Item — Bag Pack
    ['key'=>'academics.bag-pack','label'=>'Bag Pack','icon'=>'fa fa-shopping-bag',
     'url'=>$link('admin/bagpack'),'match'=>'admin/bagpack',
     'perms'=>['admin-classdairy']],
];

  $sections[] = [
    'key'=>'academics',
    'label'=>'Academics',
    'icon'=>'far fa-address-card',
    'children'=>$academicsItems,
    'visible'=> (bool) array_filter($academicsItems, fn($i)=>$canAny($i['perms']))
  ];


  // Communication
  $commItems = [
    ['key'=>'communication.templates','label'=>'Message Templates','icon'=>'fas fa-comment-dots','url'=>$link('admin/message-templates'),'match'=>'admin/message-templates','perms'=>['admin-update-message-templates']],
    ['key'=>'communication.messages','label'=>'Messages','icon'=>'fas fa-comments','url'=>$link('admin/messages'),'match'=>'admin/messages','perms'=>['admin-messages'],'badge'=>['key'=>'unread_messages','class'=>'badge-warning']],
    ['key'=>'communication.bulk-excel-sms','label'=>'Bulk Excel SMS','icon'=>'fas fa-file-upload','url'=>$link('admin/bulksms'),'match'=>'admin/bulksms','perms'=>['admin-bulk-messages']],
    ['key'=>'communication.defaulter-sms','label'=>'Defaulter SMS','icon'=>'fas fa-exclamation-circle','url'=>$link('admin/defaulter-message'),'match'=>'admin/defaulter-message','perms'=>['admin-defaulter-message']],
    ['key'=>'communication.result-sms','label'=>'Result SMS','icon'=>'fas fa-poll','url'=>$link('admin/result-message'),'match'=>'admin/result-message','perms'=>['admin-result-message']],

    ['key'=>'communication.wa-test-series','label'=>'Send Test Series Result (WA)','icon'=>'fab fa-whatsapp','url'=>$link('admin/students_list?status=1'),'match'=>'admin/students_list','perms'=>['admin-result-message','admin-messages']],
    ['key'=>'communication.wa-result','label'=>'Send Result (WA)','icon'=>'fab fa-whatsapp','url'=>$link('admin/students_w_result_list?status=1'),'match'=>'admin/students_w_result_list','perms'=>['admin-result-message','admin-messages']],
    ['key'=>'communication.wa-fee-chalan','label'=>'Send Fee Chalan (WA)','icon'=>'fab fa-whatsapp','url'=>$link('admin/family_chalan_whatsapp'),'match'=>'frontend/family_chalan_whatsapp','perms'=>['admin-result-message','admin-messages']],
    ['key'=>'communication.wa-daily-diary','label'=>'Send Daily Diary (WA)','icon'=>'fab fa-whatsapp','url'=>$link('frontend/family_diary_whatsapp'),'match'=>'frontend/family_diary_whatsapp','perms'=>['admin-result-message','admin-messages']],
    ['key'=>'communication.absentees-report','label'=>'Students Absentees Report','icon'=>'far fa-address-card','url'=>$link('admin/students_attendance/report'),'match'=>'admin/students_attendance/report','perms'=>['admin-add-student-attendance']],
  ];
  $sections[] = [
    'key'=>'communication',
    'label'=>'Communication',
    'icon'=>'fas fa-sms',
    'badge_sum_children'=>true,
    'children'=>$commItems,
    'visible'=> (bool) array_filter($commItems, fn($i)=>$canAny($i['perms'] ?? ['admin-messages']))
  ];

  // Finance
  $feeItems = [
    ['key'=>'finance.fee.fee-type','label'=>'Fee Type','icon'=>'fas fa-money-check-alt','url'=>$link('admin/fee_type'),'match'=>'admin/fee_type','perms'=>['admin-fee-type']],
    ['key'=>'finance.fee.plan-months','label'=>'Fee Plan Months','icon'=>'fas fa-money-check-alt','url'=>$link('admin/fee_plan_months/add'),'match'=>'admin/fee_plan_months','perms'=>['admin-fee-plan-months']],
    ['key'=>'finance.fee.structure','label'=>'Fee Structure','icon'=>'fa fa-calendar','url'=>$link('admin/fee_amount/add'),'match'=>'admin/fee_amount','perms'=>['admin-fee-amount']],
    ['key'=>'finance.fee.generate-chalan','label'=>'Generate Fee Chalan','icon'=>'fas fa-file-invoice','url'=>$link('admin/fee-chalan/add'),'match'=>'admin/fee-chalan/add','perms'=>['admin-fee-chalan']],
    ['key'=>'finance.fee.print-chalan','label'=>'Print Fee Chalan','icon'=>'fas fa-file-invoice','url'=>$link('admin/fee-chalan'),'match'=>'admin/fee-chalan$','perms'=>['admin-fee-chalan']],
    ['key'=>'finance.fee.print-chalan-new','label'=>'Print Fee Chalan new','icon'=>'fas fa-file-invoice','url'=>$link('admin/print-fee-chalan'),'match'=>'admin/print-fee-chalan$','perms'=>['admin-fee-chalan']],
    ['key'=>'finance.fee.pay-chalan','label'=>'Pay Fee Chalan','icon'=>'fas fa-file-invoice','url'=>$link('admin/fee-chalan-pay'),'match'=>'admin/fee-chalan-pay','perms'=>['admin-fee-chalan'],'badge'=>['key'=>'unpaid_fee_chalans','class'=>'badge-info']],
    ['key'=>'finance.fee.pay-chalan1','label'=>'Pay Fee Chalan1','icon'=>'fas fa-file-invoice','url'=>$link('admin/fee-chalan-pay1'),'match'=>'admin/fee-chalan-pay1','perms'=>['admin-fee-chalan'],'badge'=>['key'=>'unpaid_fee_chalans','class'=>'badge-info']],
    ['key'=>'finance.fee.delete-chalan','label'=>'Delete Fee Chalan','icon'=>'fas fa-file-invoice','url'=>$link('admin/delete-fee-chalan'),'match'=>'admin/delete-fee-chalan','perms'=>['admin-del-fee-chalan']],
    ['key'=>'finance.fee.monthly-balance','label'=>'Monthly Balance','icon'=>'far fa-money-bill-alt','url'=>$link('admin/fee-chalan-balance'),'match'=>'admin/fee-chalan-balance','perms'=>['admin-fee-chalan-balance']],
  ];
  $accountsItems = [
    ['key'=>'finance.accounts.expense-heads','label'=>'Expense Heads','icon'=>'fas fa-money-check-alt','url'=>$link('admin/expense_head'),'match'=>'admin/expense_head','perms'=>['admin-account-heads']],
    ['key'=>'finance.accounts.expenses','label'=>'Expenses','icon'=>'fas fa-money-check-alt','url'=>$link('admin/expenses'),'match'=>'admin/expenses','perms'=>['admin-account-expenses']],
    ['key'=>'finance.accounts.asset-heads','label'=>'Asset Heads','icon'=>'fas fa-money-check-alt','url'=>$link('admin/asset_heads'),'match'=>'admin/asset_heads','perms'=>['admin-asset-heads']],
    ['key'=>'finance.accounts.assets','label'=>'Assets','icon'=>'fas fa-money-check-alt','url'=>$link('admin/assets'),'match'=>'admin/assets','perms'=>['admin-assets']],
  ];
  $sections[] = [
    'key'=>'finance',
    'label'=>'Finance',
    'icon'=>'fas fa-receipt',
    'children'=>array_merge(
      [['label'=>'— Fee Management —','icon'=>'','header'=>true]],
      $feeItems,
      [['label'=>'— Accounts —','icon'=>'','header'=>true]],
      $accountsItems
    ),
    'visible'=> (bool) array_filter(array_merge($feeItems, $accountsItems), fn($i)=>$canAny($i['perms'] ?? []))
  ];


// sports

// Sports
$sportsItems = [
  [
    'key'   => 'sports.houses',
    'label' => 'Houses',
    'icon'  => 'fas fa-flag',
    'url'   => $link('admin/sports/houses'),
    'match' => 'admin/sports/houses',
    'perms' => ['admin-classdairy'],
  ],
  [
    'key'   => 'sports.mapping',
    'label' => 'Assign Students to Houses',
    'icon'  => 'fas fa-users-cog',
    'url'   => $link('admin/sports/mapping'),
    'match' => 'admin/sports/mapping',
    'perms' => ['admin-classdairy'],
  ],
  [
    'key'   => 'sports.mentors',
    'label' => 'House Mentors',
    'icon'  => 'fas fa-user-friends',
    'url'   => $link('admin/sports/mentors'),
    'match' => 'admin/sports/mentors',
    'perms' => ['admin-classdairy'],
  ],
  

  [
  'key'   => 'sports.bulk_events',
  'label' => 'Bulk Events',
  'icon'  => 'fas fa-layer-group',
  'url'   => $link('admin/sports/bulk-events'),
  'match' => 'admin/sports/bulk-events*',
  'perms' => ['admin-classdairy'],
],

[
  'key'   => 'sports.results',
  'label' => 'Event Results',
  'icon'  => 'fas fa-trophy',
  'url'   => $link('admin/sports/results'),
  'match' => 'admin/sports/results*',
  'perms' => ['admin-classdairy'],   // or your correct permission key
],

  // ─────────── New Items for Teams / Members / Entries ───────────
  [
    'key'   => 'sports.teams',
    'label' => 'Teams',
    'icon'  => 'fas fa-users',
    'url'   => $link('admin/sports/teams'),
    'match' => 'admin/sports/teams',
    'perms' => ['admin-classdairy'],
  ],
  [
    'key'   => 'sports.team-members',
    'label' => 'Team Members',
    'icon'  => 'fas fa-user-plus',
    'url'   => $link('admin/sports/teams'),
    'match' => 'admin/sports/team-members',
    'perms' => ['admin-classdairy'],
  ],
  [
    'key'   => 'sports.entries',
    'label' => 'Event Entries',
    'icon'  => 'far fa-file-alt',
    'url'   => $link('admin/sports/events'),
    'match' => 'admin/sports/entries',
    'perms' => ['admin-classdairy'],
  ],

  // ✅ NEW: Seats (Per House) UI
 
  // ────────────────────────────────────────────────────────────────

  [
    'key'   => 'sports.rules',
    'label' => 'Scoring Rules',
    'icon'  => 'fas fa-list-ol',
    'url'   => $link('admin/sports/rules'),
    'match' => 'admin/sports/rules',
    'perms' => ['admin-classdairy'],
  ],
  [
    'key'   => 'sports.leaderboard',
    'label' => 'Leaderboard',
    'icon'  => 'fas fa-trophy',
    'url'   => $link('admin/sports/leaderboard'),
    'match' => 'admin/sports/leaderboard',
    'perms' => ['admin-classdairy'],
  ],
  [
    'key'   => 'sports.house-sheet',
    'label' => 'House Result Sheet',
    'icon'  => 'far fa-file-alt',
    'url'   => $link('admin/sports/house-sheet'),
    'match' => 'admin/sports/house-sheet',
    'perms' => ['admin-classdairy'],
  ],
  [
    'key'   => 'sports.reports.events',
    'label' => 'Events & Participants Report',
    'icon'  => 'fas fa-clipboard-list',
    'url'   => $link('admin/sports/reports/events'),
    'match' => 'admin/sports/reports/events',
    'perms' => ['admin-classdairy'],
  ],
  [
    'key'   => 'sports.events.order',
    'label' => 'Arrange Sports Events',
    'icon'  => 'fas fa-arrows-alt',
    'url'   => $link('admin/sports/events/order'),
    'match' => 'admin/sports/events/order',
    'perms' => ['admin-classdairy'],
],

  [
  'key'   => 'sports.entries-seats',
  'label' => 'Event Seats (Per House)',
  'icon'  => 'fas fa-chair',
  'url'   => $link('admin/sports/entries/seats'), // opens selector (index)
  'match' => 'admin/sports/entries/seats',
  'perms' => ['admin-classdairy'],
],

[
  'key'   => 'sports.reports.house-members',
  'label' => 'House Members Report',
  'icon'  => 'fas fa-id-badge',
  'url'   => $link('admin/sports/reports/house-members'),
  'match' => 'admin/sports/reports/house-members',
  'perms' => ['admin-classdairy'],
],

[
  'key'   => 'sports.results',
  'label' => 'Event Results',
  'icon'  => 'fas fa-trophy',
  'url'   => $link('admin/sports/results'),
  'match' => 'admin/sports/results*',
  'perms' => ['admin-classdairy'],   // or your correct permission key
],

[
  'key'   => 'sports.report_points',
  'label' => 'Points Report',
  'icon'  => 'fas fa-medal',
  'url'   => $link('admin/sports/report-points'),
  'match' => 'admin/sports/report-points*',
  'perms' => ['admin-classdairy'],
],

[
  'key'   => 'sports.participation',
  'label' => 'Student Participation',
  'icon'  => 'fas fa-users',
  'url'   => $link('admin/sports/participation-report'),
  'match' => 'admin/sports/participation-report*',
  'perms' => ['admin-classdairy'],
],

[
  'key'   => 'sports.leaderboard',
  'label' => 'Leaderboard',
  'icon'  => 'fas fa-trophy',
  'url'   => $link('admin/sports/leaderboard'),
  'match' => 'admin/sports/leaderboard*',
  'perms' => ['admin-classdairy'],
],

[
  'key'   => 'sports.age_report',
  'label' => 'Age Report',
  'icon'  => 'fas fa-user-clock',
  'url'   => $link('admin/sports/age-report'),
  'match' => 'admin/sports/age-report*',
  'perms' => ['admin-classdairy'],
],
];

$sections[] = [
  'key'      => 'sports',
  'label'    => 'Sports',
  'icon'     => 'fas fa-medal',
  'children' => $sportsItems,
  'visible'  => (bool) array_filter($sportsItems, fn($i) => $canAny($i['perms'] ?? [])),
];


  // Reports
  $reportsItems = [
    ['key'=>'reports.defaulters-by-fee-type','label'=>'Defaulters Report by Fee Type','icon'=>'fas fa-users','url'=>$link('admin/defaulter_students_fee_report'),'match'=>'admin/defaulter_students_fee_report','perms'=>['admin-defaulter-student-fee-report']],
    ['key'=>'reports.student-prev-fee','label'=>'Student Prev Fee Report','icon'=>'fas fa-users','url'=>$link('admin/students_prevfee'),'match'=>'admin/students_prevfee','perms'=>['admin-defaulter-student-fee-report']],
    ['key'=>'reports.family-prev-fee','label'=>'Family Prev Fee Report','icon'=>'fas fa-users','url'=>$link('admin/parents_prevfee'),'match'=>'admin/parents_prevfee','perms'=>['admin-defaulter-student-fee-report']],
    ['key'=>'reports.attendance-monthly','label'=>'Attendance Monthly Reports','icon'=>'far fa-clock','url'=>$link('admin/attendance-monthly-report'),'match'=>'admin/attendance-monthly-report','perms'=>['admin-attendance-monthly-report']],
    ['key'=>'reports.fee','label'=>'Fee Report','icon'=>'fas fa-users','url'=>$link('admin/student_fee_report'),'match'=>'admin/student_fee_report','perms'=>['admin-student-fee-report']],
    ['key'=>'reports.family-paid-fee','label'=>'Family Paid Fee Report','icon'=>'fas fa-users','url'=>$link('admin/parents_paidfee'),'match'=>'admin/parents_paidfee','perms'=>['admin-student-fee-report']],
    ['key'=>'reports.family-balance-fee','label'=>'Family Balance Fee Report','icon'=>'fas fa-users','url'=>$link('admin/parents_balancefee'),'match'=>'admin/parents_balancefee','perms'=>['admin-student-fee-report']],
    ['key'=>'reports.fee-by-month','label'=>'Fee Report By Month','icon'=>'fas fa-users','url'=>$link('admin/fee_chalan_month'),'match'=>'admin/fee_chalan_month','perms'=>['admin-student-fee-report']],
    ['key'=>'reports.family-fee','label'=>'Family Fee Report','icon'=>'fas fa-users','url'=>$link('admin/family_fee_report'),'match'=>'admin/family_fee_report','perms'=>['admin-family-fee-report']],
    ['key'=>'reports.by-fee-type','label'=>'Report By Fee Type','icon'=>'fas fa-users','url'=>$link('admin/student_fee_report/report_by_fee_type'),'match'=>'admin/student_fee_report/report_by_fee_type','perms'=>['admin-report-by-fee-type']],
    ['key'=>'reports.by-student-fee','label'=>'Report By Student Fee','icon'=>'fas fa-users','url'=>$link('admin/student_fee_report/report_by_fee_student'),'match'=>'admin/student_fee_report/report_by_fee_student','perms'=>['admin-report-by-student-fee']],
    ['key'=>'reports.classwise-result','label'=>'Class Wise Result','icon'=>'fas fa-users','url'=>$link('admin/classwise_results'),'match'=>'admin/classwise_results','perms'=>['admin-classwise-result-report']],
    ['key'=>'reports.student-results','label'=>'Student Results','icon'=>'fas fa-users','url'=>$link('admin/student_results'),'match'=>'admin/student_results','perms'=>['admin-students-result-report']],
    ['key'=>'reports.datesheet-report','label'=>'Datesheet Report','icon'=>'fas fa-users','url'=>$link('admin/datesheet_report/add'),'match'=>'admin/datesheet_report','perms'=>['admin-datesheet-report']],
    ['key'=>'reports.expenses','label'=>'Expenses Report','icon'=>'fas fa-money-check-alt','url'=>$link('admin/expense_report'),'match'=>'admin/expense_report','perms'=>['admin-expense-reports']],
    ['key'=>'reports.assets','label'=>'Assets Report','icon'=>'fas fa-money-check-alt','url'=>$link('admin/assets_report'),'match'=>'admin/assets_report','perms'=>['admin-assets-report']],
    ['key'=>'reports.profit-loss','label'=>'Profit/Loss Report','icon'=>'fas fa-money-check-alt','url'=>$link('admin/profit_loss_report'),'match'=>'admin/profit_loss_report','perms'=>['admin-profit-loss-reports']],
    ['key'=>'reports.strength-report','label'=>'Strength Report','icon'=>'fas fa-money-check-alt','url'=>$link('admin/ClasswiseMonthlyStrengthReport'),'match'=>'admin/ClasswiseMonthlyStrengthReport','perms'=>['admin-profit-loss-reports']],
    [
    'key'   => 'reports.fee-collection-session-wise',
    'label' => 'Fee Collection Session Wise',
    'icon'  => 'fas fa-file-invoice-dollar',
    'url'   => $link('admin/fee-collection-session-wise'),
    'match' => 'admin/fee-collection-session-wise',
    'perms' => ['admin-profit-loss-reports'],
],
  ];
  $sections[] = [
    'key'=>'reports',
    'label'=>'Reports',
    'icon'=>'far fa-address-card',
    'children'=>$reportsItems,
    'visible'=> (bool) array_filter($reportsItems, fn($i)=>$canAny($i['perms']))
  ];

  // Optional modules
  if ($hasTransport) {
    $transportItems = [
      ['key'=>'transport.vehicles','label'=>'Vehicles','icon'=>'fa fa-list','url'=>$link('admin/vehicles'),'match'=>'admin/vehicles','perms'=>['admin-vehicles']],
    ];
    $sections[] = [
      'key'=>'transport','label'=>'Transport','icon'=>'far fa-address-card','children'=>$transportItems,
      'visible'=> (bool) array_filter($transportItems, fn($i)=>$canAny($i['perms']))
    ];
  }
  if ($hasHostel) {
    $hostelItems = [
      ['key'=>'hostel.blocks','label'=>'Blocks','icon'=>'fa fa-list','url'=>$link('admin/h_blocks'),'match'=>'admin/h_blocks','perms'=>['admin-blocks']],
      ['key'=>'hostel.rooms','label'=>'Rooms','icon'=>'fa fa-list','url'=>$link('admin/h_rooms'),'match'=>'admin/h_rooms','perms'=>['admin-blocks']],
      ['key'=>'hostel.beds','label'=>'Beds','icon'=>'fa fa-list','url'=>$link('admin/h_beds'),'match'=>'admin/h_beds','perms'=>['admin-blocks']],
      ['key'=>'hostel.block-rooms','label'=>'Block Rooms','icon'=>'fa fa-list','url'=>$link('admin/h_block_rooms'),'match'=>'admin/h_block_rooms','perms'=>['admin-blocks']],
      ['key'=>'hostel.room-beds','label'=>'Rooms Beds','icon'=>'fa fa-list','url'=>$link('admin/h_room_beds'),'match'=>'admin/h_room_beds','perms'=>['admin-blocks']],
      ['key'=>'hostel.fee-amount','label'=>'Hostel Fee Amount','icon'=>'fa fa-list','url'=>$link('admin/h_fee_amount/add'),'match'=>'admin/h_fee_amount','perms'=>['admin-blocks']],
      ['key'=>'hostel.student-beds','label'=>'Student Beds','icon'=>'fa fa-list','url'=>$link('admin/h_student_beds/add'),'match'=>'admin/h_student_beds','perms'=>['admin-blocks']],
      ['key'=>'hostel.student-report','label'=>'Hostel Student Report','icon'=>'fa fa-list','url'=>$link('admin/h_student_report'),'match'=>'admin/h_student_report$','perms'=>['admin-blocks']],
      ['key'=>'hostel.student-report2','label'=>'Hostel Student Report2','icon'=>'fa fa-list','url'=>$link('admin/h_student_report/report2'),'match'=>'admin/h_student_report/report2','perms'=>['admin-blocks']],
      ['key'=>'hostel.defaulter','label'=>'Hostel Student Defaulter','icon'=>'fa fa-list','url'=>$link('admin/h_student_report/defaulter'),'match'=>'admin/h_student_report/defaulter','perms'=>['admin-blocks']],
    ];
    $sections[] = [
      'key'=>'hostel','label'=>'Hostel','icon'=>'fa fa-list','children'=>$hostelItems,
      'visible'=> (bool) array_filter($hostelItems, fn($i)=>$canAny($i['perms']))
    ];
  }
  if ($hasAcademy) {
    $academyItems = [
      ['key'=>'academy.groups','label'=>'A Groups','icon'=>'fa fa-list','url'=>$link('admin/a_groups'),'match'=>'admin/a_groups','perms'=>['admin-academy']],
      ['key'=>'academy.class-subjects','label'=>'Class Subjects','icon'=>'fa fa-list','url'=>$link('admin/a_section_subjects'),'match'=>'admin/a_section_subjects','perms'=>['admin-academy']],
      ['key'=>'academy.subject-groups','label'=>'Subject Groups','icon'=>'fa fa-list','url'=>$link('admin/a_subject_group/add'),'match'=>'admin/a_subject_group','perms'=>['admin-academy']],
      ['key'=>'academy.teacher-groups','label'=>'Teacher Groups','icon'=>'fa fa-list','url'=>$link('admin/a_teacher_group/add'),'match'=>'admin/a_teacher_group','perms'=>['admin-academy']],
      ['key'=>'academy.fee-amount','label'=>'A Fee Amount','icon'=>'fa fa-list','url'=>$link('admin/a_fee_amount/add'),'match'=>'admin/a_fee_amount','perms'=>['admin-academy']],
      ['key'=>'academy.students','label'=>'A Students','icon'=>'fa fa-list','url'=>$link('admin/students_bulk_academy_fee'),'match'=>'admin/students_bulk_academy_fee','perms'=>['admin-academy']],
    ];
    $sections[] = [
      'key'=>'academy','label'=>'Academy','icon'=>'fa fa-list','children'=>$academyItems,
      'visible'=> (bool) array_filter($academyItems, fn($i)=>$canAny($i['perms']))
    ];
  }

  if ($can('admin-campus')) {
    $sections[] = ['key'=>'campus','label'=>'Campus','icon'=>'fa fa-home','url'=>$link('admin/campus'),'match'=>'admin/campus','visible'=>true];
  }
  if ($can('admin-custom-campus')) {
    $sections[] = ['key'=>'custom-campus','label'=>'Custom Campus','icon'=>'fa fa-home','url'=>$link('admin/custom_campus/add'),'match'=>'admin/custom_campus','visible'=>true];
  }

  $billingItems = [
    ['key'=>'billing.bill-type','label'=>'Bill Type','icon'=>'fa fa-list','url'=>$link('admin/bill_type'),'match'=>'admin/bill_type','perms'=>['admin-bill-type']],
    ['key'=>'billing.bill-amount','label'=>'Bill Amount','icon'=>'fa fa-list','url'=>$link('admin/bill_amount/add'),'match'=>'admin/bill_amount','perms'=>['admin-bill-amount']],
    ['key'=>'billing.plan-months','label'=>'Bill Plan Months','icon'=>'fa fa-list','url'=>$link('admin/bill_plan_months'),'match'=>'admin/bill_plan_months','perms'=>['admin-bill-plan-months']],
    ['key'=>'billing.pay-campus-chalan','label'=>'Pay Campus Chalan','icon'=>'fa fa-list','url'=>$link('admin/campus_chalan_pay'),'match'=>'admin/campus_chalan_pay','perms'=>['admin-campus-chalan-pay']],
    ['key'=>'billing.pay-campus-bill','label'=>'Pay Campus Bill','icon'=>'fa fa-home','url'=>$link('admin/pay_campus_bill'),'match'=>'admin/pay_campus_bill','perms'=>['admin-pay-campus-bill']],
    ['key'=>'billing.invoice','label'=>'Billing Invoice','icon'=>'fas fa-file-invoice','url'=>$link('admin/campus_plans'),'match'=>'admin/campus_plans','perms'=>['admin-campus-plans']],
    ['key'=>'billing.pay-system-bill','label'=>'Pay System Bill','icon'=>'fa fa-home','url'=>$link('admin/pay_system_bill'),'match'=>'admin/pay_system_bill','perms'=>['admin-pay-system-bill']],
    ['key'=>'billing.login-log','label'=>'Login Log','icon'=>'fa fa-home','url'=>$link('admin/ci_session_view'),'match'=>'admin/ci_session_view','perms'=>['admin-ci-session_view']],
    ['key'=>'billing.demo-login-log','label'=>'Demo Login Log','icon'=>'fa fa-home','url'=>$link('admin/ci_session_view_demo'),'match'=>'admin/ci_session_view_demo','perms'=>['admin-ci-session_view']],
  ];
  $planMgmt = [
    ['key'=>'admin.roles','label'=>'Roles','icon'=>'fa fa-users','url'=>$link('admin/roles'),'match'=>'admin/roles','perms'=>['admin-roles']],
    ['key'=>'admin.permissions','label'=>'Permissions','icon'=>'fa fa-users','url'=>$link('admin/permissions'),'match'=>'admin/permissions','perms'=>['admin-permissions']],
  ];
  $sections[] = [
    'key'=>'billing-admin',
    'label'=>'Billing & Admin',
    'icon'=>'far fa-address-card',
    'children'=>array_merge(
      [['label'=>'— Billing —','icon'=>'','header'=>true]],
      $billingItems,
      [['label'=>'— Plan Management —','icon'=>'','header'=>true]],
      $planMgmt
    ),
    'visible'=> (bool) array_filter(array_merge($billingItems, $planMgmt), fn($i)=>$canAny($i['perms']))
  ];

  // ===== Renderers =====
  $renderItem = function($item) use($isActive, $metrics) {
      if (!empty($item['header'])) {
          return '<li class="nav-header text-xs text-muted">'.esc($item['label']).'</li>';
      }

      $hasChildren = !empty($item['children']);

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
      $badgeClass = 'badge-danger';
      if (!empty($item['badge']) && is_array($item['badge']) && !empty($item['badge']['class'])) {
          $badgeClass = $item['badge']['class'];
      }
      $displayCount = $sumChildren ? $childBadgeSum : $selfBadge;
      if ($displayCount > 0) {
          $badgeHtml = '<span class="right badge '.$badgeClass.'">'.$displayCount.'</span>';
      }

      $label = '<p>'.esc($item['label']).($hasChildren ? '<i class="right fas fa-angle-left"></i>' : '').$badgeHtml.'</p>';

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
                  $html .= '<li class="nav-header text-xs text-muted pl-3">'.esc($ch['label']).'</li>';
                  continue;
              }
              if (isset($ch['perms']) && !empty($ch['perms'])) {
                  $visible = array_reduce($ch['perms'], fn($ok,$p)=>$ok||hasPermission($p), false);
                  if (!$visible) continue;
              }
              $chActive = !empty($ch['match']) && $isActive($ch['match']);

              $childBadge = '';
              if (!empty($ch['badge'])) {
                  $ckey = is_array($ch['badge']) ? ($ch['badge']['key'] ?? null) : $ch['badge'];
                  $cnum = $ckey && isset($metrics[$ckey]) ? (int)$metrics[$ckey] : 0;
                  $cclass = 'badge-info';
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
                          <a href="'.esc($ch['url']).'" class="nav-link'.($chActive?' active':'').'">
                            '.$childIcon.'
                            <p>'.esc($ch['label']).$childBadge.'</p>
                          </a>
                        </li>';
          }
          $html .= '</ul></li>';
          return $html;
      } else {
          if (isset($item['perms']) && !empty($item['perms'])) {
              $visible = array_reduce($item['perms'], fn($ok,$p)=>$ok||hasPermission($p), false);
              if (!$visible) return '';
          }
          return '<li '.$liAttrs.'>
                    <a href="'.esc($item['url']).'" class="'.$aClass.'">
                      '.$icon.$label.'
                    </a>
                  </li>';
      }
  };
?>

<aside class="main-sidebar sidebar-dark-orange elevation-4 sidebar-slim" <?php if($_SERVER['HTTP_HOST'] == 'trial.timesoftsol.com'){ ?>style="top:24px"<?php } ?>>
  <a style="padding:7px;background-color:#3c8dbc" href="<?= base_url() ?>" class="brand-link">
    <span class="brand-text font-weight-light"><?= esc($school_name ?? 'School Name') ?></span>
  </a>

  <div class="sidebar" <?php if(empty($curr_session_id)){ ?>style="pointer-events:none;opacity:.3"<?php } ?>>
    <div class="image text-center mt-3 mb-2">
      <?php if(!empty($schoolinfo) && !empty($schoolinfo->logo)): ?>
        <img style="height:70px;max-width:100%" src="<?= base_url('system-logo/'.$schoolinfo->logo) ?>" alt="Logo">
      <?php endif; ?>
    </div>

    <button type="button" class="btn btn-tool text-white no-print" data-toggle="modal" data-target="#menuPrefsModal">
      <i class="fas fa-sliders-h"></i> Customize menu
    </button>

    <div class="form-inline px-2 mb-2">
      <div class="input-group w-100">
        <input id="menuSearch" class="form-control form-control-sidebar" type="search" placeholder="Search menu..." aria-label="Search">
        <div class="input-group-append">
          <button class="btn btn-sidebar"><i class="fas fa-search fa-sm"></i></button>
        </div>
      </div>
    </div>

    <nav class="mt-2">
      <ul id="sidebarMenu" class="nav nav-pills nav-sidebar flex-column nav-child-indent text-sm" data-widget="treeview" role="menu" data-accordion="true">
        <?php
          $userMenuPrefs = $session->get('menu_prefs') ?? [];

          $applyPrefs = function(array $items) use (&$applyPrefs, $userMenuPrefs): array {
              $out = [];
              foreach ($items as $item) {
                  $key = $item['key'] ?? null;

                  if ($key !== null && array_key_exists($key, $userMenuPrefs) && $userMenuPrefs[$key] === false) {
                      continue;
                  }

                  if (!empty($item['children'])) {
                      $item['children'] = $applyPrefs($item['children']);
                      $hasRealChildren = (bool) array_filter($item['children'], fn($c)=>empty($c['header']));
                      if (!$hasRealChildren && empty($item['url'])) {
                          continue;
                      }
                  }

                  $out[] = $item;
              }
              return $out;
          };

          $sections = $applyPrefs($sections);

          foreach ($sections as $sec) {
            $visible = $sec['visible'] ?? true;
            if (is_callable($visible)) { $visible = $visible(); }
            if (!$visible) continue;

            if (!empty($sec['children'])) {
              $hasVisibleChildren = false;
              foreach ($sec['children'] as $c) {
                if (!empty($c['header'])) { $hasVisibleChildren = true; break; }
                $v = (empty($c['perms']) || array_reduce(($c['perms'] ?? []), fn($ok,$p)=>$ok||hasPermission($p), false));
                if ($v) { $hasVisibleChildren = true; break; }
              }
              if (!$hasVisibleChildren) continue;
            }

            echo $renderItem($sec);
          }
        ?>
      </ul>
    </nav>
  </div>
</aside>

<div class="modal fade" id="menuPrefsModal" tabindex="-1" role="dialog" aria-labelledby="menuPrefsLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="menuPrefsLabel">Customize Menu</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="mb-2 d-flex justify-content-between">
          <div>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="prefsShowAll">Show All</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="prefsHideAll">Hide All</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="prefsReset">Reset</button>
          </div>
          <input type="search" id="prefsSearch" class="form-control form-control-sm" style="max-width:240px" placeholder="Search items...">
        </div>

        <div id="menuPrefsList">
          <?php
            $flatten = function(array $items, $prefix = '') use (&$flatten) {
              foreach ($items as $it) {
                $key = $it['key'] ?? null;
                $label = $it['label'] ?? ($it['url'] ?? $key);
                if (!$label) continue;

                echo '<div class="form-check my-1" data-item-row data-key="'.esc($key).'" data-label="'.esc(strtolower($label)).'">';
                echo '  <input class="form-check-input menu-item-toggle" type="checkbox" id="mi_'.esc($key).'" data-key="'.esc($key).'" checked>';
                echo '  <label class="form-check-label" for="mi_'.esc($key).'">'.esc($label).'</label>';
                echo '</div>';

                if (!empty($it['children'])) $flatten($it['children'], $key.'.');
              }
            };
            $flatten($sections);
          ?>
        </div>
        <small class="text-muted d-block mt-2">Hidden items won’t appear in the sidebar. You can’t show items you don’t have permission for.</small>
      </div>
      <div class="modal-footer">
        <button type="button" id="menuPrefsSave" class="btn btn-primary">
          <i class="fas fa-save"></i> Save preferences
        </button>
      </div>
    </div>
  </div>
</div>

<script>
$(function () {
  var MENU_PREFS_KEY = 'menu_prefs_v1';
  var $menu   = $('#sidebarMenu');
  var $search = $('#menuSearch');

  function loadPrefs() {
    var p = window.USER_MENU_PREFS || null;
    if (!p) {
      try { p = JSON.parse(localStorage.getItem(MENU_PREFS_KEY) || '{}'); }
      catch(e){ p = {}; }
    }
    return p || {};
  }
  function storePrefs(prefs) {
    try { localStorage.setItem(MENU_PREFS_KEY, JSON.stringify(prefs)); } catch(e){}
    if (window.MENU_PREFS_SAVE_URL) {
      $.post(window.MENU_PREFS_SAVE_URL, { prefs: JSON.stringify(prefs) });
    }
  }

  function applyMenuPrefs(prefs) {
    $menu.find('li.nav-item[data-menu-key]').each(function () {
      var $li  = $(this);
      var key  = $li.data('menu-key');
      var show = (prefs[key] !== false);
      $li.toggle(show);
    });

    $menu.find('li.nav-item.has-treeview').each(function () {
      var $li = $(this);
      var hasVisibleChild = $li.find('> ul.nav-treeview > li.nav-item:visible').length > 0;
      var hasOwnKey = !!$li.data('menu-key');
      $li.toggle(hasVisibleChild || hasOwnKey);
    });
  }

  function setTreeOpenState() {
    $menu.find('ul.nav-treeview').each(function () {
      var $ul = $(this);
      var hasActive       = $ul.find('.nav-link.active').length > 0;
      var hasVisibleChild = $ul.find('> li.nav-item:visible').length > 0;
      var open = hasActive || hasVisibleChild;
      $ul.closest('.has-treeview').toggleClass('menu-open', open);
      $ul.toggle(open);
    });
  }

  function resetToPrefs() {
    applyMenuPrefs(prefs);
    setTreeOpenState();
  }
  function performSearch(q) {
    q = (q || '').toLowerCase().trim();
    if (!q) { resetToPrefs(); return; }

    $menu.find('li.nav-item').hide();
    $menu.find('ul.nav-treeview').hide();

    $menu.find('a.nav-link').filter(function () {
      return $(this).text().toLowerCase().indexOf(q) > -1;
    }).each(function () {
      var $a  = $(this);
      var $li = $a.closest('li.nav-item');
      var key = $li.data('menu-key') || $a.data('menu-key');

      if (key && prefs[key] === false) return;

      $li.show();
      var $ul = $li.closest('ul.nav-treeview');
      if ($ul.length) {
        $ul.show().closest('.has-treeview').addClass('menu-open').show();
      }
    });
  }

  function initTogglesFromPrefs() {
    $('.menu-item-toggle[data-key]').each(function () {
      var k = $(this).data('key');
      var show = (prefs[k] !== false);
      $(this).prop('checked', show);
    });
  }

  $(document).on('change', '.menu-item-toggle[data-key]', function () {
    var k    = $(this).data('key');
    var show = $(this).is(':checked');
    prefs[k] = show;
    storePrefs(prefs);
    applyMenuPrefs(prefs);
    setTreeOpenState();
  });

  $(document).on('click', '#menuPrefsSave', function () {
    var next = {};
    $('.menu-item-toggle[data-key]').each(function () {
      var k = $(this).data('key');
      next[k] = $(this).is(':checked');
    });
    prefs = next;
    storePrefs(prefs);
    applyMenuPrefs(prefs);
    setTreeOpenState();

    if ($.fn.modal) { $('#menuPrefsModal').modal('hide'); }
    if (window.toastr) { toastr.success('Menu preferences saved'); }
  });

  $(document).on('click', '#prefsShowAll', function(){
    $('.menu-item-toggle').prop('checked', true).trigger('change');
  });
  $(document).on('click', '#prefsHideAll', function(){
    $('.menu-item-toggle').prop('checked', false).trigger('change');
  });
  $(document).on('click', '#prefsReset', function(){
    prefs = {};
    try { localStorage.removeItem(MENU_PREFS_KEY); } catch(e){}
    initTogglesFromPrefs();
    applyMenuPrefs(prefs);
    setTreeOpenState();
    if (window.toastr) toastr.info('Menu preferences reset');
  });

  $(document).on('keyup', '#prefsSearch', function(){
    var q = $(this).val().toLowerCase().trim();
    $('[data-item-row]').each(function(){
      var label = ($(this).data('label') || '').toString();
      $(this).toggle(label.indexOf(q) > -1);
    });
  });

  $('#menuPrefsModal').on('shown.bs.modal', function(){
    initTogglesFromPrefs();
  });

  $search.on('keyup', function () {
    performSearch($(this).val());
  });

  var prefs = loadPrefs();
  applyMenuPrefs(prefs);
  setTreeOpenState();
  initTogglesFromPrefs();
});
</script>
