<?php $school_name = isset($schoolinfo->system_name) ? $schoolinfo->system_name : 'School Name'; ?> <!DOCTYPE html> <html lang="en"> <head> <meta charset="utf-8"> <meta name="viewport" content="width=device-width, initial-scale=1"> <title> <?php echo $school_name; ?> </title>
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
    <!-- Theme style -->
    <!-- <link rel="stylesheet" href="<?= base_url('resource/adminlte/dist/css/adminlte.min.css') ?>">-->    
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
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= base_url('resource/adminlte/dist/css/adminlte.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/js/sweetalert/sweetalert.css') ?>"> 
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>"> 
    <script type="text/javascript">
      var BASE_URL = '<?= base_url() ?>';
      var RELA_PATH = './';
    </script>
  <!-- jQuery -->
  <!-- <script  src="<?= base_url('resource/adminlte/plugins/jquery/jquery.min.js') ?>"></script> -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
  <!-- jQuery UI 1.11.4 -->
  <script src="<?= base_url('resource/adminlte/plugins/jquery-ui/jquery-ui.min.js') ?>"></script>
  <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
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
  <!-- AdminLTE App -->
  <script type="text/javascript" src="<?= base_url('resource/arttemplate/template-native.js') ?>"></script>
  <script type="text/javascript" src="<?= base_url('resource/artdialog/dialog-plus-min.js') ?>"></script>
  <script type="text/javascript" src="<?= base_url('resource/js/jquery.cookie.js') ?>"></script>
  <script type="text/javascript" src="<?= base_url('resource/js/jquery.form.js') ?>"></script>
  <script type="text/javascript" src="<?= base_url('resource/js/bootbox.js') ?>"></script>
  <script type="text/javascript" src="<?= base_url('resource/toastr/toastr.min.js') ?>"></script>
  <script type="text/javascript" src="<?= base_url('resource/adminlte/plugins/fastclick/fastclick.js') ?>"></script>
  <script type="text/javascript" src="<?= base_url('resource/autosize/autosize.min.js') ?>"></script>
  <!-- <script type="text/javascript" src="<?= base_url('resource/adminlte/dist/js/app.min.js') ?>"></script> -->
  <script type="text/javascript" src="<?= base_url('resource/sammy/lib/min/sammy-latest.min.js') ?>"></script>
  <script type="text/javascript" src="<?= base_url('resource/js/server.js') ?>"></script>
  <!--  <script type="text/javascript" src="<?= base_url('assets/js/bootstrap-clockpicker.min.js') ?>">
  </script>  -->
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
// Get current campus flags once
$session   = session();
$campusId  = (int) ($session->get('member_campusid') ?? 0);
$hasTransport = $hasHostel = $hasAcademy = false;

if ($campusId) {
    $db = \Config\Database::connect();
    $flags = $db->table('campus')
        ->select('t_flag, h_flag, a_flag')
        ->where('campus_id', $campusId)
        ->get()->getRow();

    if ($flags) {
        $hasTransport = ((int)($flags->t_flag ?? 0) === 1);
        $hasHostel    = ((int)($flags->h_flag ?? 0) === 1);
        $hasAcademy   = ((int)($flags->a_flag ?? 0) === 1);
    }
}

// ===== Dynamic metrics (badges) =====
$metrics = [
  'unread_messages'     => 0, // SMS/Inbox items not read
  'pending_emp_leaves'  => 0, // Employee leave requests pending
  'pending_std_leaves'  => 0, // Student leave requests pending
  'unpaid_fee_chalans'  => 0, // Unpaid challans (current campus/session)
];

$safeCount = function (string $sql, array $binds = []) use ($db): int {
    try {
        $q   = $db->query($sql, $binds);
        $row = $q ? $q->getRow() : null;
        return (int) ($row->c ?? 0);
    } catch (\Throwable $e) {
        return 0; // fail-safe: never break the menu
    }
};

$metrics['unread_messages'] = $safeCount(
    "SELECT COUNT(*) c FROM messages 
     WHERE (is_read = 0 OR is_read IS NULL)
       AND (? = 0 OR campus_id = ?)",
    [(int)$curr_campus_id, (int)$curr_campus_id]
);

$metrics['pending_emp_leaves'] = $safeCount(
    "SELECT COUNT(*) c FROM employee_leaves 
     WHERE (status = 'Pending' OR status = 0 OR approved = 0 OR COALESCE(approved,0) = 0)
       AND (? = 0 OR campus_id = ?)",
    [(int)$curr_campus_id, (int)$curr_campus_id]
);

$metrics['pending_std_leaves'] = $safeCount(
    "SELECT COUNT(*) c FROM students_leaves 
     WHERE (status = 'Pending' OR status = 0 OR approved = 0 OR COALESCE(approved,0) = 0)
       AND (? = 0 OR campus_id = ?)",
    [(int)$curr_campus_id, (int)$curr_campus_id]
);

$metrics['unpaid_fee_chalans'] = $safeCount(
    "SELECT COUNT(*) c FROM fee_chalan 
     WHERE (paid = 0 OR status = 0 OR paid_status = 0 OR COALESCE(paid,0) = 0)
       AND (? = 0 OR session_id = ?)
       AND (? = 0 OR campus_id  = ?)",
    [(int)$curr_session_id, (int)$curr_session_id, (int)$curr_campus_id, (int)$curr_campus_id]
);

?>




<?php //if($_SERVER['HTTP_HOST'] == 'demo.timesoftsol.com'){ ?>  
<!--Start of Tawk.to Script (0.3.3)-->
<!-- <script type="text/javascript">
var Tawk_API=Tawk_API||{};
Tawk_API.visitor = {"name":"","email":""};var Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/5fd33959a8a254155ab2536b/default';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script> -->
<!--End of Tawk.to Script (0.3.3)-->
<?php //} ?>
  <!-- AdminLTE App -->
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
  <style type="text/css">
  .content-header h1{font-size: 22px !important;}
  .nav-sidebar .nav-treeview{
    margin: 0 10px !important;
  }
  .heading3 {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 16px !important;
    font-weight: normal;
  }
  .content-header{
    padding: 2px .5rem;
  }
  .dt-buttons{float: right;}
  .main-header .navbar-nav li a{color: #fff; }
  .card-body{
    padding: 10px !important;
  }
.form-control{
    height: 35px !important;
    font-size: 16px !important;
    line-height: 17px !important;
    padding: 5px 15px;
    border-radius: 0px;
    border: 1px solid blue;
}
.input-group-addon {
    padding: 1px 8px !important;
  }
.select2-container--default .select2-selection--single, .select2-selection .select2-selection--single {
      border: 1px solid #d2d6de;
      border-radius: 0;
      padding: 6px 12px;
      height: 30px !important;
  }
.select2-container--default .select2-selection--single .select2-selection__arrow{
    height: 22px !important;
    right: 3px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #444;
    line-height: 24px !important;
}
@media print {
  body {-webkit-print-color-adjust: exact;}
  .resultReport th {
    background: #494E53 !important;
    print-color-adjust: exact; 
    color: #fff;
    text-align: left;
}
  .printable_result_header_width{width: 730px !important;}
  .nav-tabs{ display: none; }
  .card-primary.card-outline{    border-top: 0px none !important;}
  .main-footer{display: none;}
  .btn {display: none;}
  .no-print,.nav-tabs,.main-footer,.no-print *{ display: none !important; }
  #form-filter,.operation,.main-footer,.nav-tabs,.dt-buttons,.dataTables_info,.paging_simple_numbers,.no-print, .no-print *{display: none !important; }
    @page { 
        size: auto;
    }
    
    }
  </style>
    <?php
    $session = \Config\Services::session();
    $curr_campus_id = $session->get('member_campusid'); 
    $member_reg_text = $session->get('member_reg_text');
    if(empty($member_reg_text)){
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
                //var json = $.parseJSON(res);
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
  <body class="hold-transition sidebar-mini layout-fixed">
    <div class="modal fade" id="schoolshortname" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content"><div class="modal-header">
          <h5 class="modal-title pull-left" id="exampleModalLabel"><?php echo $school_name; ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="systemID" id="systemID" value="<?php if (isset($schoolinfo) && is_object($schoolinfo)): ?>
    <?= $schoolinfo->system_id ?>
<?php endif; ?>">
          <div class="form-group">
          <label for="reg_text">School Short Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" required="" name="reg_text" id="reg_text"  maxlength="3" value=""> 
          </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" id="updateRegText" class="btn btn-primary">Submit</button>
          </div>
        </div>
      </div>
    </div>
    <!-- <div class="bg-warning text-white text-center no-print" style="position: relative;width: 100%;z-index: 1046;text-align: center;font-size: 16px;">
      Our whatsapp number has been updated +92 332 7659393
    </div> -->
    <?php if($_SERVER['HTTP_HOST'] == 'trial.timesoftsol.com'){ ?>
      <div class="bg-warning text-white" style="position: relative;width: 100%;z-index: 1046;text-align: center;font-size: 16px;">
       Your trial period will expire in 30 Days.Pay your bill for live data.This data will not be available for live account.
      </div>
    <?php } ?>
    <div class="wrapper">
      <?php   
       $db = \Config\Database::connect();
        $session = \Config\Services::session();

        $curr_campus_id = $session->get('member_campusid');

        $currentCampusBill = $db->query('SELECT * FROM campus_bills WHERE campus_id = ' . intval($curr_campus_id))->getRow();
        $plan_id = $currentCampusBill->plan_id ?? 0;

        $builder = $db->table('system_plans');
        $plan_info = $builder->where('plan_id', $plan_id)->get()->getRow();

        $userid = $session->get('member_userid');

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
          <!-- Left navbar links -->
          <ul class="navbar-nav col-lg-6">
            <li class="nav-item d-sm-inline-block nav-link">
              <a style="padding-top: 5px;" class="nav-link" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars">
                </i>
              </a>
            </li>
            <li class="nav-item d-none d-sm-inline-block nav-link" style="font-size: 14px;">
               <a style="padding-top: 5px;" class="nav-link" href="#"> <?php if($plan_info){ echo $plan_info->plan_name; }  ?></a>
            </li>
            <?php if (hasPermission('admin-campus') && $schoolinfo->system_id != 60): ?>
            <li class="nav-item d-sm-inline-block nav-link col-lg-6 col-sm-4">
              <select name="campus_id" id="campusID" class="form-control">
                <?php foreach($campuses as $campus): ?>
                  <option value="<?= $campus->campus_id ?>"
                          <?= $curr_campus_id == $campus->campus_id ? 'selected' : '' ?>>
                    <?= esc($campus->campus_name) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </li>
          <?php endif; ?>
          <?php if(hasPermission('admin-view-global-session')){ ?>
           
            <li class="nav-item d-sm-inline-block nav-link col-lg-3 col-sm-4">
             <select name="session_id" id="sessionID" class="form-control">
              <?php foreach ($academic_sessions as $academic_session): ?>
                <option value="<?= esc($academic_session->session_id) ?>"
                  <?= ($curr_session_id == $academic_session->session_id) ? 'selected' : '' ?>>
                  <?= esc($academic_session->session_name) ?>
                </option>
              <?php endforeach; ?>
            </select>

            </li>
          <?php } ?>

          </ul>
          <!-- Right navbar links -->
          <!-- Sidebar toggle button-->
        <ul class="navbar-nav ml-auto">
          <?php if($_SERVER['HTTP_HOST'] == 'demo.timesoftsol.com'){ ?>
          <li style="margin-right:60px;"> <a style="padding: 6px 11px;" href="https://timesoftsol.com/signup/" class="btn btn-lg btn-flat btn-danger btn-block">Create Your Own School</a></li>
        <?php } ?>
        <!-- User Account: style can be found in dropdown.less -->        
      
      <li class="dropdown user user-menu pull-right">
        <a class="nav-link" style="margin-top:5px;" href="javascript:;" class="dropdown-toggle" data-toggle="dropdown"> 
         <?php if (!empty($user) && !empty($user->photo)): ?>
              <img class="user-image" src="<?= base_url('admin/employees-img/' . $user->photo) ?>" />
          <?php else: ?>
              <i class="fa fa-user"></i>
          <?php endif; ?>
          <span class="d-none d-sm-inline-block">
            <?php if (!empty($user) && !empty($user->username)): ?>
            <?php echo $user->username;?> (
            <?php echo $role_name_info->rolename;?>)
            <?php endif; ?>
          </span> 
        </a>
        <ul class="dropdown-menu">
          <!-- User image --> 
            <li class="user-header d-none d-sm-block"> 
              <?php if (!empty($user) && !empty($user->photo)): ?>
                  <img class="user-image" src="<?= base_url('admin/employees-img/' . $user->photo) ?>" />
              <?php else: ?>
                  <i class="fa fa-user"></i>
              <?php endif; ?>
              <p> 
                <?php if (!empty($user) && !empty($user->username)): ?>
                <?php echo $user->username;?> 
                <?php endif; ?>
              </p>
            </li>
            <!-- Menu Footer-->
            <li class="user-footer">
              <div class="pull-left d-none d-sm-block" style="float: left;"> 
                <a href="<?= base_url('admin/profile') ?>" class="btn btn-default btn-flat">
                  <i class="fa fa-gear">
                  </i> Profile
                </a> 
              </div>
              <div class="pull-right" style="float: right;"> 
                <a href="<?= base_url('admin/logout') ?>" class="btn btn-default btn-flat">
                  <i class="fa fa-sign-out">
                  </i> Logout
                </a> 
              </div>
            </li>
        </ul>
    </li>
  <!-- Control Sidebar Toggle Button -->
  </ul>
</nav>
<!-- Left side column. contains the logo and sidebar -->  
<!-- Main Sidebar Container -->


<!-- Main Sidebar Container (REPLACEMENT) -->
<?php
  // ===== Context/Flags =====
  $session          = \Config\Services::session();
  $db               = \Config\Database::connect();
  $curr_session_id  = $session->get('member_sessionid') ?? null;
  $curr_campus_id   = (int) ($session->get('member_campusid') ?? 0);

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

  // ===== Helpers =====
  $uri          = service('uri');
  $currentPath  = trim($uri->getPath(), '/');

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

  // ===== Menu Schema (declarative) =====
  // Each item: label, icon, url?, match?, perms?[], children?[], visible? (closure/bool)
  $sections = [];

  // Dashboard
  $sections[] = [
    'label' => 'Dashboard',
    'icon'  => 'fas fa-tachometer-alt',
    'url'   => $link('admin/dashboard'),
    'match' => 'admin/dashboard',
    'visible' => true
  ];

  // Profiles
  $profiles = [
    ['label'=>'User Profile',     'icon'=>'fas fa-id-badge', 'url'=>$link('admin/profile'),         'match'=>'admin/profile',         'perms'=>[]],
    ['label'=>'Campus Profile',   'icon'=>'fas fa-school',   'url'=>$link('admin/profile-campus'),  'match'=>'admin/profile-campus',  'perms'=>['admin-add-campus-profile']],
    ['label'=>'System Profile',   'icon'=>'fas fa-cogs',     'url'=>$link('admin/profile-system'),  'match'=>'admin/profile-system',  'perms'=>['admin-add-system-profile']],
  ];
  $sections[] = [
    'label'=>'Profiles','icon'=>'fas fa-th','children'=>$profiles,
    'visible'=> (bool) array_filter($profiles, fn($i)=>empty($i['perms']) || $canAny($i['perms']))
  ];

  // Sessions
  $sessionsItems = [
    ['label'=>'Academic Sessions','icon'=>'fa fa-calendar', 'url'=>$link('admin/academic_session'), 'match'=>'admin/academic_session', 'perms'=>['admin-academic-session','admin-academic-session','admin-academic-session','admin-academic-session','admin-academic-session']], // kept permissive
    ['label'=>'Terms','icon'=>'fa fa-list',                 'url'=>$link('admin/terms'),            'match'=>'admin/terms',            'perms'=>['admin-terms']],
    ['label'=>'Term Sessions','icon'=>'fa fa-list',         'url'=>$link('admin/terms_session'),    'match'=>'admin/terms_session',    'perms'=>['admin-terms-sessions']],
    ['label'=>'Term Weeks','icon'=>'fa fa-list',            'url'=>$link('admin/term_weeks'),       'match'=>'admin/term_weeks',       'perms'=>['admin-term-weeks']],
  ];
  $sections[] = [
    'label'=>'Sessions','icon'=>'fas fa-cogs','children'=>$sessionsItems,
    'visible'=> (bool) array_filter($sessionsItems, fn($i)=>$canAny($i['perms'] ?? []))
  ];

  // Classes
  $classesItems = [
    ['label'=>'Classes','icon'=>'fa fa-list',       'url'=>$link('admin/classes'),         'match'=>'admin/classes',         'perms'=>['admin-classes']],
    ['label'=>'Sections','icon'=>'fa fa-flask',     'url'=>$link('admin/sections'),        'match'=>'admin/sections',        'perms'=>['admin-sections']],
    ['label'=>'Class Sections','icon'=>'fa fa-flask','url'=>$link('admin/class_section'),  'match'=>'admin/class_section',   'perms'=>['admin-class-section']],
    ['label'=>'Subjects','icon'=>'fa fa-list',      'url'=>$link('admin/subjects'),        'match'=>'admin/subjects',        'perms'=>['admin-subjects']],
    ['label'=>'Section Subjects','icon'=>'fa fa-list','url'=>$link('admin/section_subjects'),'match'=>'admin/section_subjects','perms'=>['admin-section-subjects']],
  ];
  $sections[] = [
    'label'=>'Classes','icon'=>'fa fa-list-alt','children'=>$classesItems,
    'visible'=> (bool) array_filter($classesItems, fn($i)=>$canAny($i['perms']))
  ];

  // Admissions / Students
  $studentsItems = [
    ['label'=>'Enrolled Students (Print)','icon'=>'fas fa-users', 'url'=>$link('admin/students_print?status=1'), 'match'=>'admin/students_print', 'perms'=>['admin-students']],
    ['label'=>'Admission','icon'=>'fas fa-user-plus',              'url'=>$link('admin/students/add'),           'match'=>'admin/students/add',  'perms'=>['admin-students']],
    ['label'=>'Add Bulk Students','icon'=>'fas fa-layer-group',    'url'=>$link('admin/addbulkstudents/add'),    'match'=>'admin/addbulkstudents','perms'=>['admin-students']],
    ['label'=>'Student ID Card','icon'=>'far fa-id-card',          'url'=>$link('admin/student_id_card'),        'match'=>'admin/student_id_card','perms'=>['admin-student-id-cards']],
    ['label'=>'Promotion','icon'=>'fas fa-angle-double-up',        'url'=>$link('admin/student_class'),          'match'=>'admin/student_class', 'perms'=>['admin-student-class']],
    ['label'=>'Attachment Types','icon'=>'fas fa-paperclip',       'url'=>$link('admin/attachment_types'),       'match'=>'admin/attachment_types','perms'=>['admin-attachment-types']],
    ['label'=>'Data Verification Form','icon'=>'fas fa-user-check','url'=>$link('admin/student_data_verification_form'),'match'=>'admin/student_data_verification_form','perms'=>['admin-students']],
    ['label'=>'Fee Verification Form','icon'=>'fas fa-file-invoice-dollar','url'=>$link('admin/student_data_verification_form/student_fee_verification'),'match'=>'admin/student_data_verification_form/student_fee_verification','perms'=>['admin-students']],
  ];
  $sections[] = [
    'label'=>'Students','icon'=>'fas fa-user-graduate','children'=>$studentsItems,
    'visible'=> (bool) array_filter($studentsItems, fn($i)=>$canAny($i['perms']))
  ];

  // Faculty
  $facultyItems = [
    ['label'=>'Employees','icon'=>'fa fa-user',        'url'=>$link('admin/users?status=1'),      'match'=>'admin/users',         'perms'=>['admin-users']],
    ['label'=>'Subject Teachers','icon'=>'fa fa-book', 'url'=>$link('admin/teacher_subjects/add'),'match'=>'admin/teacher_subjects','perms'=>['admin-add-teacher-subject']],
    ['label'=>'Section Incharges','icon'=>'fa fa-book','url'=>$link('admin/teacher_section/add'), 'match'=>'admin/teacher_section','perms'=>['admin-add-teacher-section']],
    ['label'=>'Employee Timing','icon'=>'fa fa-clock', 'url'=>$link('admin/emp_timing/add'),      'match'=>'admin/emp_timing',    'perms'=>['admin-add-teacher-section']],
  ];
  $sections[] = [
    'label'=>'Faculty','icon'=>'fa fa-user','children'=>$facultyItems,
    'visible'=> (bool) array_filter($facultyItems, fn($i)=>$canAny($i['perms']))
  ];

  // Exams & Tests
  $examsItems = [
    ['label'=>'Exams','icon'=>'fa fa-list',        'url'=>$link('admin/exam'),                 'match'=>'admin/exam',                  'perms'=>['admin-exams']],
    ['label'=>'Date Sheet','icon'=>'fa fa-calendar','url'=>$link('admin/datesheet'),          'match'=>'admin/datesheet',             'perms'=>['admin-datesheet']],
    ['label'=>'Results','icon'=>'fa fa-list',      'url'=>$link('admin/students-results/add'),'match'=>'admin/students-results',      'perms'=>['admin-students-results']],
    ['label'=>'Results List','icon'=>'fa fa-list', 'url'=>$link('admin/students-results-list'),'match'=>'admin/students-results-list', 'perms'=>['admin-students-results']],
    ['label'=>'Subject Results','icon'=>'fa fa-list','url'=>$link('admin/students-subject-results/add'),'match'=>'admin/students-subject-results','perms'=>['admin-students-subject-results']],
    ['label'=>'Grades','icon'=>'fa fa-list',       'url'=>$link('admin/grades/add'),           'match'=>'admin/grades',               'perms'=>['admin-grades']],
    ['label'=>'Grading Policy','icon'=>'fa fa-list','url'=>$link('admin/grading-policy'),      'match'=>'admin/grading-policy',        'perms'=>['admin-grading-policy']],
  ];
  $testsItems = [
    ['label'=>'Add Tests Results','icon'=>'fa fa-list',         'url'=>$link('admin/test-results'),            'match'=>'admin/test-results',             'perms'=>['admin-test-series']],
    ['label'=>'Tests Series Results Card','icon'=>'fa fa-list', 'url'=>$link('admin/test-series-result-card'), 'match'=>'admin/test-series-result-card',  'perms'=>['admin-test-series']],
  ];
  $sections[] = [
    'label'=>'Exams & Tests','icon'=>'fas fa-diagnoses',
    'children'=>array_merge($examsItems, $testsItems),
    'visible'=> (bool) array_filter(array_merge($examsItems, $testsItems), fn($i)=>$canAny($i['perms']))
  ];

  // Attendance
  $attendanceItems = [
    ['label'=>'Employees Attendance','icon'=>'fa fa-cubes','url'=>$link('admin/employees_attendance/add'),'match'=>'admin/employees_attendance','perms'=>['admin-add-student-attendance']],
    ['label'=>'Create Employee Leaves','icon'=>'fa fa-cubes','url'=>$link('admin/employee_leaves/add'),    'match'=>'admin/employee_leaves/add','perms'=>['admin-add-student-attendance']],
   ['label'=>'Employee Leaves Applications','icon'=>'fa fa-cubes','url'=>$link('admin/employee_leaves'),'match'=>'admin/employee_leaves','perms'=>['admin-add-student-attendance'],
  'badge'=>['key'=>'pending_emp_leaves','class'=>'badge-danger'] // 👈
],
    ['label'=>'Employees Attendance Report','icon'=>'fa fa-cubes','url'=>$link('admin/emp_attendance_monthlyreport'),'match'=>'admin/emp_attendance_monthlyreport','perms'=>['admin-emp-attendance-monthly-report']],
    ['label'=>'Absentees','icon'=>'far fa-clock','url'=>$link('admin/students_absentees/add'),       'match'=>'admin/students_absentees','perms'=>['admin-add-student-absentees']],
    ['label'=>'Create Leaves Applications','icon'=>'far fa-clock','url'=>$link('admin/students_leaves/add'),'match'=>'admin/students_leaves/add','perms'=>['admin-add-student-leaves']],
   ['label'=>'Leaves Applications','icon'=>'far fa-clock','url'=>$link('admin/students_leaves'),'match'=>'admin/students_leaves','perms'=>['admin-student-leaves'],
  'badge'=>['key'=>'pending_std_leaves','class'=>'badge-danger'] // 👈
],
  ];
  $sections[] = [
    'label'=>'Attendance','icon'=>'far fa-address-card','children'=>$attendanceItems,
    'visible'=> (bool) array_filter($attendanceItems, fn($i)=>$canAny($i['perms']))
  ];

  // Timetable
  $timetableItems = [
    ['label'=>'Time Table','icon'=>'far fa-clock','url'=>$link('admin/timetable/add'),     'match'=>'admin/timetable',      'perms'=>['admin-timetable']],
    ['label'=>'School Timing','icon'=>'far fa-clock','url'=>$link('admin/school_timing/add'),'match'=>'admin/school_timing','perms'=>['admin-school-timing']],
    ['label'=>'School Timing Type','icon'=>'far fa-clock','url'=>$link('admin/school_timming_type'),'match'=>'admin/school_timming_type','perms'=>['admin-school-timing']],
    ['label'=>'Slots','icon'=>'far fa-clock','url'=>$link('admin/slots'),                  'match'=>'admin/slots',          'perms'=>['admin-slots']],
  ];
  $sections[] = [
    'label'=>'Time Table','icon'=>'far fa-clock','children'=>$timetableItems,
    'visible'=> (bool) array_filter($timetableItems, fn($i)=>$canAny($i['perms']))
  ];

  // Academics
  $academicsItems = [
    ['label'=>'Top Level Planning','icon'=>'fa fa-list','url'=>$link('admin/top_level_planning'),      'match'=>'admin/top_level_planning',       'perms'=>['admin-top-level-planning']],
    ['label'=>'Scheme Of Studies','icon'=>'fa fa-list','url'=>$link('admin/scheme_of_studies_view'),   'match'=>'admin/scheme_of_studies_view',   'perms'=>['admin-weekly-planning']],
    ['label'=>'Weekly Planning','icon'=>'fa fa-list','url'=>$link('admin/weekly_planning_view'),       'match'=>'admin/weekly_planning_view',     'perms'=>['admin-weekly-planning']],
    ['label'=>'Weekly Objectives','icon'=>'fa fa-list','url'=>$link('admin/wp_objectives'),            'match'=>'admin/wp_objectives',            'perms'=>['admin-weekly-planning']],
    ['label'=>'Subjects Objectives','icon'=>'fa fa-list','url'=>$link('admin/wp-subjects-objectives/add'),'match'=>'admin/wp-subjects-objectives','perms'=>['admin-weekly-planning']],
    ['label'=>'Add Student Weekly Progress','icon'=>'fa fa-list','url'=>$link('admin/wp_std_weeekly_progress/add'),'match'=>'admin/wp_std_weeekly_progress','perms'=>['admin-weekly-planning']],
    ['label'=>'Student Weekly Progress','icon'=>'fa fa-list','url'=>$link('admin/wp-results-card'),    'match'=>'admin/wp-results-card',          'perms'=>['admin-weekly-planning']],
    ['label'=>'Daily Diary','icon'=>'fa fa-list','url'=>$link('admin/classdairy-view'),                'match'=>'admin/classdairy-view',          'perms'=>['admin-classdairy']],
  ];
  $sections[] = [
    'label'=>'Academics','icon'=>'far fa-address-card','children'=>$academicsItems,
    'visible'=> (bool) array_filter($academicsItems, fn($i)=>$canAny($i['perms']))
  ];

  // Communication (SMS & WhatsApp)
  $commItems = [
    // SMS
    ['label'=>'Message Templates','icon'=>'fas fa-comment-dots','url'=>$link('admin/message-templates'),          'match'=>'admin/message-templates',      'perms'=>['admin-update-message-templates']],
    ['label'=>'Messages','icon'=>'fas fa-comments','url'=>$link('admin/messages'),                                  'match'=>'admin/messages',               'perms'=>['admin-messages']],
    ['label'=>'Bulk Excel SMS','icon'=>'fas fa-file-upload','url'=>$link('admin/bulksms'),                          'match'=>'admin/bulksms',                'perms'=>['admin-bulk-messages']],
    ['label'=>'Defaulter SMS','icon'=>'fas fa-exclamation-circle','url'=>$link('admin/defaulter-message'),         'match'=>'admin/defaulter-message',      'perms'=>['admin-defaulter-message']],
    ['label'=>'Result SMS','icon'=>'fas fa-poll','url'=>$link('admin/result-message'),                              'match'=>'admin/result-message',         'perms'=>['admin-result-message']],
    // WhatsApp
    ['label'=>'Send Test Series Result (WA)','icon'=>'fab fa-whatsapp','url'=>$link('admin/students_list?status=1'),'match'=>'admin/students_list',         'perms'=>['admin-result-message','admin-messages']],
    ['label'=>'Send Result (WA)','icon'=>'fab fa-whatsapp','url'=>$link('admin/students_w_result_list?status=1'),  'match'=>'admin/students_w_result_list', 'perms'=>['admin-result-message','admin-messages']],
    ['label'=>'Send Fee Chalan (WA)','icon'=>'fab fa-whatsapp','url'=>$link('admin/family_chalan_whatsapp'),       'match'=>'admin/family_chalan_whatsapp', 'perms'=>['admin-result-message','admin-messages']],
    ['label'=>'Send Daily Diary (WA)','icon'=>'fab fa-whatsapp','url'=>$link('admin/family_diary_whatsapp'),       'match'=>'admin/family_diary_whatsapp',  'perms'=>['admin-result-message','admin-messages']],
    ['label'=>'Students Absentees Report','icon'=>'far fa-address-card','url'=>$link('admin/students_attendance/report'),'match'=>'admin/students_attendance/report','perms'=>['admin-add-student-attendance']],
  ];
 $sections[] = [
  'label'=>'Communication',
  'icon'=>'fas fa-sms',
  'badge_sum_children' => true, // 👈 sum child badges on parent
  'children'=>[
    ['label'=>'Message Templates','icon'=>'fas fa-comment-dots','url'=>$link('admin/message-templates'),'match'=>'admin/message-templates','perms'=>['admin-update-message-templates']],
    ['label'=>'Messages','icon'=>'fas fa-comments','url'=>$link('admin/messages'),'match'=>'admin/messages','perms'=>['admin-messages'],
      'badge'=>['key'=>'unread_messages','class'=>'badge-warning']  // 👈
    ],
    ['label'=>'Bulk Excel SMS','icon'=>'fas fa-file-upload','url'=>$link('admin/bulksms'),'match'=>'admin/bulksms','perms'=>['admin-bulk-messages']],
    ['label'=>'Defaulter SMS','icon'=>'fas fa-exclamation-circle','url'=>$link('admin/defaulter-message'),'match'=>'admin/defaulter-message','perms'=>['admin-defaulter-message']],
    ['label'=>'Result SMS','icon'=>'fas fa-poll','url'=>$link('admin/result-message'),'match'=>'admin/result-message','perms'=>['admin-result-message']],

    // WhatsApp (no badges here by default)
    ['label'=>'Send Test Series Result (WA)','icon'=>'fab fa-whatsapp','url'=>$link('admin/students_list?status=1'),'match'=>'admin/students_list','perms'=>['admin-result-message','admin-messages']],
    ['label'=>'Send Result (WA)','icon'=>'fab fa-whatsapp','url'=>$link('admin/students_w_result_list?status=1'),'match'=>'admin/students_w_result_list','perms'=>['admin-result-message','admin-messages']],
    ['label'=>'Send Fee Chalan (WA)','icon'=>'fab fa-whatsapp','url'=>$link('admin/family_chalan_whatsapp'),'match'=>'admin/family_chalan_whatsapp','perms'=>['admin-result-message','admin-messages']],
    ['label'=>'Send Daily Diary (WA)','icon'=>'fab fa-whatsapp','url'=>$link('admin/family_diary_whatsapp'),'match'=>'admin/family_diary_whatsapp','perms'=>['admin-result-message','admin-messages']],
    ['label'=>'Students Absentees Report','icon'=>'far fa-address-card','url'=>$link('admin/students_attendance/report'),'match'=>'admin/students_attendance/report','perms'=>['admin-add-student-attendance']],
  ],
  'visible'=> (bool) array_filter($commItems ?? [], fn($i)=>$canAny($i['perms'] ?? ['admin-messages']))
];

  // Finance (Fee Management + Accounts)
  $feeItems = [
    ['label'=>'Fee Type','icon'=>'fas fa-money-check-alt', 'url'=>$link('admin/fee_type'),           'match'=>'admin/fee_type',            'perms'=>['admin-fee-type']],
    ['label'=>'Fee Plan Months','icon'=>'fas fa-money-check-alt','url'=>$link('admin/fee_plan_months/add'),'match'=>'admin/fee_plan_months','perms'=>['admin-fee-plan-months']],
    ['label'=>'Fee Structure','icon'=>'fa fa-calendar',   'url'=>$link('admin/fee_amount/add'),      'match'=>'admin/fee_amount',          'perms'=>['admin-fee-amount']],
    ['label'=>'Generate Fee Chalan','icon'=>'fas fa-file-invoice','url'=>$link('admin/fee-chalan/add'),'match'=>'admin/fee-chalan/add',   'perms'=>['admin-fee-chalan']],
    ['label'=>'Print Fee Chalan','icon'=>'fas fa-file-invoice',   'url'=>$link('admin/fee-chalan'),   'match'=>'admin/fee-chalan$',         'perms'=>['admin-fee-chalan']],
   ['label'=>'Pay Fee Chalan','icon'=>'fas fa-file-invoice','url'=>$link('admin/fee-chalan-pay'),'match'=>'admin/fee-chalan-pay','perms'=>['admin-fee-chalan'],
  'badge'=>['key'=>'unpaid_fee_chalans','class'=>'badge-info'] // 👈
],
    ['label'=>'Delete Fee Chalan','icon'=>'fas fa-file-invoice',  'url'=>$link('admin/delete-fee-chalan'),'match'=>'admin/delete-fee-chalan','perms'=>['admin-del-fee-chalan']],
    ['label'=>'Monthly Balance','icon'=>'far fa-money-bill-alt',  'url'=>$link('admin/fee-chalan-balance'),'match'=>'admin/fee-chalan-balance','perms'=>['admin-fee-chalan-balance']],
  ];
  $accountsItems = [
    ['label'=>'Expense Heads','icon'=>'fas fa-money-check-alt','url'=>$link('admin/expense_head'), 'match'=>'admin/expense_head', 'perms'=>['admin-account-heads']],
    ['label'=>'Expenses','icon'=>'fas fa-money-check-alt','url'=>$link('admin/expenses'),           'match'=>'admin/expenses',     'perms'=>['admin-account-expenses']],
    ['label'=>'Asset Heads','icon'=>'fas fa-money-check-alt','url'=>$link('admin/asset_heads'),     'match'=>'admin/asset_heads',  'perms'=>['admin-asset-heads']],
    ['label'=>'Assets','icon'=>'fas fa-money-check-alt','url'=>$link('admin/assets'),               'match'=>'admin/assets',       'perms'=>['admin-assets']],
  ];
  $sections[] = [
    'label'=>'Finance','icon'=>'fas fa-receipt',
    'children'=>array_merge(
      [['label'=>'— Fee Management —','icon'=>'','header'=>true]],
      $feeItems,
      [['label'=>'— Accounts —','icon'=>'','header'=>true]],
      $accountsItems
    ),
    'visible'=> (bool) array_filter(array_merge($feeItems,$accountsItems), fn($i)=>$canAny($i['perms'] ?? []))
  ];

  // Reports (incl defaulters & fee)
  $reportsItems = [
    ['label'=>'Defaulters Report by Fee Type','icon'=>'fas fa-users','url'=>$link('admin/defaulter_students_fee_report'),'match'=>'admin/defaulter_students_fee_report','perms'=>['admin-defaulter-student-fee-report']],
    ['label'=>'Student Prev Fee Report','icon'=>'fas fa-users','url'=>$link('admin/students_prevfee'),                 'match'=>'admin/students_prevfee',        'perms'=>['admin-defaulter-student-fee-report']],
    ['label'=>'Family Prev Fee Report','icon'=>'fas fa-users','url'=>$link('admin/parents_prevfee'),                  'match'=>'admin/parents_prevfee',         'perms'=>['admin-defaulter-student-fee-report']],

    ['label'=>'Attendance Monthly Reports','icon'=>'far fa-clock','url'=>$link('admin/attendance-monthly-report'),    'match'=>'admin/attendance-monthly-report','perms'=>['admin-attendance-monthly-report']],
    ['label'=>'Fee Report','icon'=>'fas fa-users','url'=>$link('admin/student_fee_report'),                           'match'=>'admin/student_fee_report',      'perms'=>['admin-student-fee-report']],
    ['label'=>'Family Paid Fee Report','icon'=>'fas fa-users','url'=>$link('admin/parents_paidfee'),                  'match'=>'admin/parents_paidfee',         'perms'=>['admin-student-fee-report']],
    ['label'=>'Family Balance Fee Report','icon'=>'fas fa-users','url'=>$link('admin/parents_balancefee'),            'match'=>'admin/parents_balancefee',      'perms'=>['admin-student-fee-report']],
    ['label'=>'Fee Report By Month','icon'=>'fas fa-users','url'=>$link('admin/fee_chalan_month'),                    'match'=>'admin/fee_chalan_month',        'perms'=>['admin-student-fee-report']],
    ['label'=>'Family Fee Report','icon'=>'fas fa-users','url'=>$link('admin/family_fee_report'),                     'match'=>'admin/family_fee_report',       'perms'=>['admin-family-fee-report']],
    ['label'=>'Report By Fee Type','icon'=>'fas fa-users','url'=>$link('admin/student_fee_report/report_by_fee_type'),'match'=>'admin/student_fee_report/report_by_fee_type','perms'=>['admin-report-by-fee-type']],
    ['label'=>'Report By Student Fee','icon'=>'fas fa-users','url'=>$link('admin/student_fee_report/report_by_fee_student'),'match'=>'admin/student_fee_report/report_by_fee_student','perms'=>['admin-report-by-student-fee']],

    ['label'=>'Class Wise Result','icon'=>'fas fa-users','url'=>$link('admin/classwise_results'),                     'match'=>'admin/classwise_results',       'perms'=>['admin-classwise-result-report']],
    ['label'=>'Student Results','icon'=>'fas fa-users','url'=>$link('admin/student_results'),                         'match'=>'admin/student_results',         'perms'=>['admin-students-result-report']],
    ['label'=>'Datesheet Report','icon'=>'fas fa-users','url'=>$link('admin/datesheet_report/add'),                   'match'=>'admin/datesheet_report',        'perms'=>['admin-datesheet-report']],

    ['label'=>'Expenses Report','icon'=>'fas fa-money-check-alt','url'=>$link('admin/expense_report'),                'match'=>'admin/expense_report',          'perms'=>['admin-expense-reports']],
    ['label'=>'Assets Report','icon'=>'fas fa-money-check-alt','url'=>$link('admin/assets_report'),                   'match'=>'admin/assets_report',           'perms'=>['admin-assets-report']],
    ['label'=>'Profit/Loss Report','icon'=>'fas fa-money-check-alt','url'=>$link('admin/profit_loss_report'),         'match'=>'admin/profit_loss_report',      'perms'=>['admin-profit-loss-reports']],
  ];
  $sections[] = [
    'label'=>'Reports','icon'=>'far fa-address-card','children'=>$reportsItems,
    'visible'=> (bool) array_filter($reportsItems, fn($i)=>$canAny($i['perms']))
  ];

  // Optional Modules (Campus flags)
  if ($hasTransport) {
    $transportItems = [
      ['label'=>'Vehicles','icon'=>'fa fa-list','url'=>$link('admin/vehicles'),'match'=>'admin/vehicles','perms'=>['admin-vehicles']],
      // Transport Fee (commented in original)
    ];
    $sections[] = [
      'label'=>'Transport','icon'=>'far fa-address-card','children'=>$transportItems,
      'visible'=> (bool) array_filter($transportItems, fn($i)=>$canAny($i['perms']))
    ];
  }
  if ($hasHostel) {
    $hostelItems = [
      ['label'=>'Blocks','icon'=>'fa fa-list','url'=>$link('admin/h_blocks'),'match'=>'admin/h_blocks','perms'=>['admin-blocks']],
      ['label'=>'Rooms','icon'=>'fa fa-list','url'=>$link('admin/h_rooms'),'match'=>'admin/h_rooms','perms'=>['admin-blocks']],
      ['label'=>'Beds','icon'=>'fa fa-list','url'=>$link('admin/h_beds'),'match'=>'admin/h_beds','perms'=>['admin-blocks']],
      ['label'=>'Block Rooms','icon'=>'fa fa-list','url'=>$link('admin/h_block_rooms'),'match'=>'admin/h_block_rooms','perms'=>['admin-blocks']],
      ['label'=>'Rooms Beds','icon'=>'fa fa-list','url'=>$link('admin/h_room_beds'),'match'=>'admin/h_room_beds','perms'=>['admin-blocks']],
      ['label'=>'Hostel Fee Amount','icon'=>'fa fa-list','url'=>$link('admin/h_fee_amount/add'),'match'=>'admin/h_fee_amount','perms'=>['admin-blocks']],
      ['label'=>'Student Beds','icon'=>'fa fa-list','url'=>$link('admin/h_student_beds/add'),'match'=>'admin/h_student_beds','perms'=>['admin-blocks']],
      ['label'=>'Hostel Student Report','icon'=>'fa fa-list','url'=>$link('admin/h_student_report'),'match'=>'admin/h_student_report$','perms'=>['admin-blocks']],
      ['label'=>'Hostel Student Report2','icon'=>'fa fa-list','url'=>$link('admin/h_student_report/report2'),'match'=>'admin/h_student_report/report2','perms'=>['admin-blocks']],
      ['label'=>'Hostel Student Defaulter','icon'=>'fa fa-list','url'=>$link('admin/h_student_report/defaulter'),'match'=>'admin/h_student_report/defaulter','perms'=>['admin-blocks']],
    ];
    $sections[] = [
      'label'=>'Hostel','icon'=>'fa fa-list','children'=>$hostelItems,
      'visible'=> (bool) array_filter($hostelItems, fn($i)=>$canAny($i['perms']))
    ];
  }
  if ($hasAcademy) {
    $academyItems = [
      ['label'=>'A Groups','icon'=>'fa fa-list','url'=>$link('admin/a_groups'),'match'=>'admin/a_groups','perms'=>['admin-academy']],
      ['label'=>'Class Subjects','icon'=>'fa fa-list','url'=>$link('admin/a_section_subjects'),'match'=>'admin/a_section_subjects','perms'=>['admin-academy']],
      ['label'=>'Subject Groups','icon'=>'fa fa-list','url'=>$link('admin/a_subject_group/add'),'match'=>'admin/a_subject_group','perms'=>['admin-academy']],
      ['label'=>'Teacher Groups','icon'=>'fa fa-list','url'=>$link('admin/a_teacher_group/add'),'match'=>'admin/a_teacher_group','perms'=>['admin-academy']],
      ['label'=>'A Fee Amount','icon'=>'fa fa-list','url'=>$link('admin/a_fee_amount/add'),'match'=>'admin/a_fee_amount','perms'=>['admin-academy']],
      ['label'=>'A Students','icon'=>'fa fa-list','url'=>$link('admin/students_bulk_academy_fee'),'match'=>'admin/students_bulk_academy_fee','perms'=>['admin-academy']],
    ];
    $sections[] = [
      'label'=>'Academy','icon'=>'fa fa-list','children'=>$academyItems,
      'visible'=> (bool) array_filter($academyItems, fn($i)=>$canAny($i['perms']))
    ];
  }

  // Campus
  if ($can('admin-campus')) {
    $sections[] = ['label'=>'Campus','icon'=>'fa fa-home','url'=>$link('admin/campus'),'match'=>'admin/campus','visible'=>true];
  }
  if ($can('admin-custom-campus')) {
    $sections[] = ['label'=>'Custom Campus','icon'=>'fa fa-home','url'=>$link('admin/custom_campus/add'),'match'=>'admin/custom_campus','visible'=>true];
  }

  // Billing & Admin
  $billingItems = [
    ['label'=>'Bill Type','icon'=>'fa fa-list','url'=>$link('admin/bill_type'),'match'=>'admin/bill_type','perms'=>['admin-bill-type']],
    ['label'=>'Bill Amount','icon'=>'fa fa-list','url'=>$link('admin/bill_amount/add'),'match'=>'admin/bill_amount','perms'=>['admin-bill-amount']],
    ['label'=>'Bill Plan Months','icon'=>'fa fa-list','url'=>$link('admin/bill_plan_months'),'match'=>'admin/bill_plan_months','perms'=>['admin-bill-plan-months']],
    ['label'=>'Pay Campus Chalan','icon'=>'fa fa-list','url'=>$link('admin/campus_chalan_pay'),'match'=>'admin/campus_chalan_pay','perms'=>['admin-campus-chalan-pay']],
    ['label'=>'Pay Campus Bill','icon'=>'fa fa-home','url'=>$link('admin/pay_campus_bill'),'match'=>'admin/pay_campus_bill','perms'=>['admin-pay-campus-bill']],
    ['label'=>'Billing Invoice','icon'=>'fas fa-file-invoice','url'=>$link('admin/campus_plans'),'match'=>'admin/campus_plans','perms'=>['admin-campus-plans']],
    ['label'=>'Pay System Bill','icon'=>'fa fa-home','url'=>$link('admin/pay_system_bill'),'match'=>'admin/pay_system_bill','perms'=>['admin-pay-system-bill']],
    ['label'=>'Login Log','icon'=>'fa fa-home','url'=>$link('admin/ci_session_view'),'match'=>'admin/ci_session_view','perms'=>['admin-ci-session_view']],
    ['label'=>'Demo Login Log','icon'=>'fa fa-home','url'=>$link('admin/ci_session_view_demo'),'match'=>'admin/ci_session_view_demo','perms'=>['admin-ci-session_view']],
  ];
  $planMgmt = [
    ['label'=>'Roles','icon'=>'fa fa-users','url'=>$link('admin/roles'),'match'=>'admin/roles','perms'=>['admin-roles']],
    ['label'=>'Permissions','icon'=>'fa fa-users','url'=>$link('admin/permissions'),'match'=>'admin/permissions','perms'=>['admin-permissions']],
  ];
  $sections[] = [
    'label'=>'Billing & Admin','icon'=>'far fa-address-card',
    'children'=>array_merge(
      [['label'=>'— Billing —','icon'=>'','header'=>true]],
      $billingItems,
      [['label'=>'— Plan Management —','icon'=>'','header'=>true]],
      $planMgmt
    ),
    'visible'=> (bool) array_filter(array_merge($billingItems,$planMgmt), fn($i)=>$canAny($i['perms']))
  ];

  // ===== Renderers =====
 $renderItem = function($item) use($isActive, $metrics) {
    if (!empty($item['header'])) {
        return '<li class="nav-header text-xs text-muted">'.esc($item['label']).'</li>';
    }

    $hasChildren = !empty($item['children']);

    // Resolve own badge (if configured)
    $selfBadge = 0;
    if (!empty($item['badge'])) {
        $key = is_array($item['badge']) ? ($item['badge']['key'] ?? null) : $item['badge'];
        $selfBadge = $key && isset($metrics[$key]) ? (int)$metrics[$key] : 0;
    }

    // If parent wants sum of children badges
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

    // Build label with optional badge
    $badgeHtml = '';
    $badgeClass = 'badge-danger';
    if (!empty($item['badge']) && is_array($item['badge']) && !empty($item['badge']['class'])) {
        $badgeClass = $item['badge']['class'];
    }
    $displayCount = $sumChildren ? $childBadgeSum : $selfBadge;
    if ($displayCount > 0) {
        $badgeHtml = '<span class="right badge '.$badgeClass.'">'.$displayCount.'</span>';
    }

    $label = '<p>'.esc($item['label']).($hasChildren ? '<i class="right fas fa-angle-left"></i>' : '').$badgeHtml.'</p>';

    if ($hasChildren) {
        $html = '<li class="'.$liClass.'">
          <a href="#" class="nav-link'.($active ? ' active' : '').'">'.$icon.$label.'</a>
          <ul class="nav nav-treeview">';
        foreach ($item['children'] as $ch) {
            if (!empty($ch['header'])) {
                $html .= '<li class="nav-header text-xs text-muted pl-3">'.esc($ch['label']).'</li>';
                continue;
            }
            // permission vis check (same as before)
            if (isset($ch['perms']) && !empty($ch['perms'])) {
                $visible = array_reduce($ch['perms'], fn($ok,$p)=>$ok||hasPermission($p), false);
                if (!$visible) continue;
            }
            $chActive = !empty($ch['match']) && $isActive($ch['match']);

            // child badge
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
  : '<span class="nav-icon nav-icon-blank"></span>'; // keeps text aligned if no icon

$html .= '<li class="nav-item">
            <a href="'.esc($ch['url']).'" class="nav-link'.($chActive?' active':'').'">
              '.$childIcon.'
              <p>'.esc($ch['label']).$childBadge.'</p>
            </a>
          </li>';
        }
        $html .= '</ul></li>';
        return $html;
    } else {
        // simple item
        if (isset($item['perms']) && !empty($item['perms'])) {
            $visible = array_reduce($item['perms'], fn($ok,$p)=>$ok||hasPermission($p), false);
            if (!$visible) return '';
        }
        return '<li class="'.$liClass.'">
                  <a href="'.esc($item['url']).'" class="'.$aClass.'">
                    '.$icon.$label.'
                  </a>
                </li>';
    }
};

?>
<aside class="main-sidebar sidebar-dark-orange elevation-4 sidebar-slim" <?php if($_SERVER['HTTP_HOST'] == 'trial.timesoftsol.com'){ ?> style="top:24px"<?php } ?>>

  <!-- Brand Logo -->
  <a style="padding:7px;background-color:#3c8dbc" href="<?= base_url() ?>" class="brand-link">
    <span class="brand-text font-weight-light"><?= esc($school_name ?? 'School Name') ?></span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar" <?php if(empty($curr_session_id)){ ?>style="pointer-events:none;opacity:.3"<?php } ?>>
    <!-- School Logo -->
    <div class="image text-center mt-3 mb-2">
      <?php if(!empty($schoolinfo) && !empty($schoolinfo->logo)): ?>
        <img style="height:70px;max-width:100%" src="<?= base_url('system-logo/'.$schoolinfo->logo) ?>" alt="Logo">
      <?php endif; ?>
    </div>

    <!-- Search -->
    <div class="form-inline px-2 mb-2">
      <div class="input-group w-100">
        <input id="menuSearch" class="form-control form-control-sidebar" type="search" placeholder="Search menu..." aria-label="Search">
        <div class="input-group-append">
          <button class="btn btn-sidebar"><i class="fas fa-search fa-sm"></i></button>
        </div>
      </div>
    </div>

    <!-- Menu -->
    <nav class="mt-2">
     <ul id="sidebarMenu"
    class="nav nav-pills nav-sidebar flex-column nav-child-indent text-sm"
    data-widget="treeview" role="menu" data-accordion="true">
        <?php
          foreach ($sections as $sec) {
            $visible = $sec['visible'] ?? true;
            if (is_callable($visible)) { $visible = $visible(); }
            if (!$visible) continue;

            // filter: hide section if all children are invisible (for tree groups)
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

<!-- Sidebar UX polish (compact + professional) -->
<style>
  /* ===== Basics ===== */
  .sidebar .nav-header{
    letter-spacing:.04em; text-transform:uppercase;
    color:#94a3b8; padding:8px 10px 4px; font-size:10.5px;
  }
  .sidebar .form-control-sidebar{
    height:32px; border-radius:6px; font-size:13px;
    background:#1f2937; border-color:#111827; color:#e5e7eb;
  }
  .sidebar .form-control-sidebar::placeholder{ color:#9aa0a6; }

  /* ===== Row layout (critical fix) ===== */
  /* Make every menu row a flex line with a reserved icon column */
  .nav-sidebar .nav-link{
    display:flex; align-items:center; gap:10px;
    padding:8px 10px; min-height:36px; box-sizing:border-box;
    font-size:13px; line-height:20px; color:#cbd5e1; background:transparent;
    transition:background .12s ease, color .12s ease;
  }
  .nav-sidebar .nav-link:hover{ background:rgba(255,255,255,.05); color:#e8eef5; }

  /* Icon column: fixed width so text never overlaps */
  .nav-sidebar .nav-link .nav-icon{
    flex:0 0 20px; width:20px; text-align:center;
    font-size:14px; margin:0; color:#9aa0a6; line-height:1;
  }
  .nav-sidebar .nav-link:hover .nav-icon{ color:#e8eef5; }

  /* Placeholder when no icon was provided (keeps text aligned) */
  .nav-icon-blank{ flex:0 0 20px; width:20px; display:inline-block; }

  /* Label area takes the rest of the row */
  .nav-sidebar .nav-link p{
    flex:1 1 auto; margin:0; display:flex; align-items:center;
    gap:8px; min-width:0; /* allows truncation if needed */
  }

  /* Chevron on the right */
  .nav-sidebar .nav-link .right{
    margin-left:auto; font-size:10px; color:#7a8391; transition:transform .15s ease;
  }
  .nav-sidebar .nav-item.menu-open > .nav-link .right{ transform:rotate(90deg); }

  /* ===== Active states ===== */
  .nav-sidebar .nav-link.active{
    background:linear-gradient(90deg,#3c8dbc1a,#3c8dbc2b); color:#fff; position:relative;
  }
  .nav-sidebar .nav-link.active::before{
    content:""; position:absolute; left:0; top:6px; bottom:6px; width:3px;
    background:#3c8dbc; border-radius:0 2px 2px 0;
  }
  .sidebar .nav-sidebar .nav-item.has-treeview.menu-open > .nav-link{ background:#2b3440; }

  /* ===== Submenus ===== */
  .nav-treeview{ margin:4px 0 8px 26px !important; padding-left:10px; border-left:1px dashed rgba(255,255,255,.06); }
  .nav-treeview > .nav-item > .nav-link{
    padding:6px 10px; min-height:32px; font-size:12.5px;
  }
  /* Keep submenu icons visible & small (undo any previous "display:none") */
  .nav-treeview .nav-link .nav-icon{ font-size:12.5px; flex:0 0 18px; width:18px; }
  /* Light active for child */
  .nav-sidebar .nav-treeview .nav-link.active{ background:rgba(60,141,188,.12); }

  /* ===== Badges ===== */
  .right.badge{
    font-weight:600; font-size:10px; padding:2px 6px; border-radius:20px;
    background:#334155; color:#e5e7eb;
  }
  .badge-danger{ background:#ef4444 !important; }
  .badge-warning{ background:#f59e0b !important; color:#1f2937 !important; }
  .badge-info{ background:#0ea5e9 !important; }

  /* Label row alignment */
  .nav-sidebar .nav-link p{ justify-content:space-between; width:100%; }

  /* Keyboard focus (accessible) */
  .nav-sidebar .nav-link:focus{
    outline:none; box-shadow:0 0 0 2px rgba(60,141,188,.25) inset; border-radius:6px;
  }

  /* ===== Optional: if you keep "sidebar-slim" class, don't shrink too far ===== */
  .main-sidebar.sidebar-slim{ width:240px; }
  @media (min-width: 992px){
    body.sidebar-mini .main-sidebar.sidebar-slim { width:240px; }
  }
</style>


<script>
  // Instant filter for long menus
  (function(){
    var $input = $('#menuSearch');
    var $menu  = $('#sidebarMenu');

    $input.on('keyup', function(){
      var q = $(this).val().toLowerCase().trim();
      if (!q) {
        // reset: show all, but keep AdminLTE's default tree state
        $menu.find('li.nav-item').show();
        $menu.find('ul.nav-treeview').each(function(){
          var $ul = $(this);
          if (!$ul.find('.nav-link.active').length) {
            $ul.closest('.has-treeview').removeClass('menu-open');
            $ul.hide();
          }
        });
        return;
      }
      // Show matching items + their parents
      $menu.find('li.nav-item').hide();
      $menu.find('ul.nav-treeview').hide();
      $menu.find('a.nav-link').filter(function(){
        return $(this).text().toLowerCase().indexOf(q) > -1;
      }).each(function(){
        var $a = $(this);
        $a.closest('li.nav-item').show();
        var $tree = $a.closest('ul.nav-treeview');
        if ($tree.length) {
          $tree.show().closest('.has-treeview').addClass('menu-open').show();
        }
      });
    });
  })();
</script>
