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
<aside class="main-sidebar sidebar-dark-orange elevation-4" <?php if($_SERVER['HTTP_HOST'] == 'trial.timesoftsol.com'){ ?> style="top: 24px;"
<?php } ?>>
  <!-- Brand Logo -->
  <a style="padding: 7px; background-color: #3c8dbc;" href="<?= base_url() ?>" class="brand-link">
    <span class="brand-text font-weight-light">
      <?php echo $school_name; ?>
    </span>
  </a>
  <!-- Sidebar -->
  <?php //print_r($schoolinfo); ?>
  <div class="sidebar"  <?php if(empty($curr_session_id)){ ?>style="pointer-events:none;opacity: .3;" <?php } ?> >
    <!-- sidebar menu: : style can be found in sidebar.less -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="true" >
        <li><div class="image text-center">
          <?php if($schoolinfo){ ?> 
          <img style="height: 70px; text-align: center;max-width: 100%;" src="<?= base_url('system-logo/' . $schoolinfo->logo) ?>">
          <?php } ?>
        </div></li>
        <li class="nav-item">
          <a href="<?= base_url('admin/dashboard') ?>"" class="nav-link">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>
              Dashboard
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-th"></i>
            <p>Profiles
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
             <li class="nav-item">
              <a href="<?= base_url('admin/profile') ?>"" class="nav-link">
                 <i class="nav-icon fas fa-th"></i>
                 <p>User Profile</p>
              </a>
            </li>
            <?php if(hasPermission('admin-add-campus-profile')){ ?> 
            <li class="nav-item">
              <a href="<?= base_url('admin/profile-campus') ?>"" class="nav-link">
                 <i class="nav-icon fas fa-th"></i>
                 <p>Campus Profile</p>
              </a>
            </li>
             <?php } ?>
            <?php if(hasPermission('admin-add-system-profile')){ ?> 
            <li class="nav-item">
              <a class="nav-link" href="<?=  base_url('admin/profile-system') ?>">
                 <i class="nav-icon fas fa-th"></i>
                 <p>System Profile</p>
              </a>
            </li>
            <?php } ?>
          </ul>
        </li>
        <?php if(hasPermission('admin-enquiry')){ ?> 
        <li class="nav-item">
          <a href="<?= base_url('admin/admission-enquiry') ?>"" class="nav-link">
            <i class="nav-icon fas fa-th"></i>
            <p>Admission Enquiry
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-term-weeks') || hasPermission('admin-terms') || hasPermission('admin-add-academic-session') || hasPermission('admin-academic-session') || hasPermission('admin-terms-sessions')){ ?>
        <li class="nav-item">
          <a class="nav-link" href="#">
            <i class="nav-icon fas fa-cogs"></i> 
            <p>
              Sessions
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-academic-session')){ ?> 
            <li class="nav-item"> 
              <a href="<?= base_url('admin/academic_session') ?>"" class="nav-link"> 
                <i class="nav-icon fa fa-calendar"></i> 
                <p>Academic Sessions</p>
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-terms')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/terms') ?>"" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Terms</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-terms-sessions')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/terms_session') ?>"" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Term Sessions</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-term-weeks')){ ?>  
            <li class="nav-item"> 
              <a href="<?= base_url('admin/term_weeks') ?>"" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Term Weeks</p> 
              </a> 
            </li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-classes') || hasPermission('admin-sections') || hasPermission('admin-subjects') || hasPermission('admin-class-subjects') ||  hasPermission('admin-class-section')){ ?>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fa fa-list-alt" aria-hidden="true"></i> 
            <p>Classes
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-classes')){ ?>
            <li class="nav-item">
              <a href="<?= base_url('admin/classes') ?>" class="nav-link">
                <i class="nav-icon fa fa-list"></i> 
                <p>Classes</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-sections')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/sections') ?>" class="nav-link"> 
                <i class="nav-icon fa fa-flask"></i> 
                <p>Sections</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-class-section')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/class_section') ?>" class="nav-link"> 
                <i class="nav-icon fa fa-flask"></i> 
                <p>Class Sections</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-subjects')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/subjects') ?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Subjects</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-section-subjects')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/section_subjects') ?>" class="nav-link">
                <i class="nav-icon fa fa-list"></i> 
                <p>Section Subjects</p> 
              </a> 
            </li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>   
        <?php if(hasPermission('admin-students') || hasPermission('admin-student-class') ){ ?>  
          <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-user-graduate"></i>
            <p>Students
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <!-- <?php if(hasPermission('admin-students')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/students?status=1'); ?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Enrolled Students
                </p> 
              </a> 
            </li> -->
            <li class="nav-item"> 
              <a href="<?= base_url('admin/students_print?status=1'); ?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Enrolled Students
                </p> 
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/students/add') ?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Admission
                </p> 
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/addbulkstudents/add') ?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Add Bulk Students 
                </p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-student-id-cards')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/student_id_card') ?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Student ID Card</p> 
              </a> 
            </li> 
           <!--  <?php } ?>
            <?php if(hasPermission('admin-students-contact-list')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/students_contact_list?status=1'); ?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Contact List</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-students-contact-list')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/students_defaulters_list?status=1'); ?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Defaulters List</p> 
              </a> 
            </li>
            <?php } ?> -->
           
            <?php if(hasPermission('admin-student-class')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/student_class') ?>" class="nav-link"> 
                <i class="nav-icon fas fa-users"></i> 
                <p>Promotion</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-attachment-types')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/attachment_types') ?>" class="nav-link"> 
                <i class="nav-icon fas fa-users"></i> 
                <p>Attachment Types</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-students')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/student_data_verification_form') ?>"" class="nav-link"> 
                <i class="nav-icon fas fa-users"></i> 
                <p>Data Verification Form</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-students')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/student_data_verification_form/student_fee_verification') ?>"" class="nav-link"> 
                <i class="nav-icon fas fa-users"></i> 
                <p>Fee Verification Form</p> 
              </a> 
            </li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-messages')){ ?>  
          <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-user-graduate"></i>
            <p>Messages
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-update-message-templates')){ ?> 
            <li class="nav-item">
              <a class="nav-link" href="<?=  base_url('admin/message-templates') ?>">
                 <i class="nav-icon fas fa-th"></i>
                 <p>Message Templates</p>
              </a>
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-messages')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/messages') ?>"" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Messages
                </p> 
              </a> 
            </li>
            <?php if(hasPermission('admin-bulk-messages')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/bulksms') ?>"" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Bulk Excel SMS
                </p> 
              </a> 
            </li>
          <?php } ?>
          <?php if(hasPermission('admin-defaulter-message')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/defaulter-message') ?>"" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Defaulter SMS
                </p> 
              </a> 
            </li>
          <?php } ?>
          <?php if(hasPermission('admin-result-message')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/result-message') ?>"" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Result SMS
                </p> 
              </a> 
            </li>
          <?php } ?>
          <?php } ?>            
          </ul>
        </li>
        <?php } ?>
         <?php if(hasPermission('admin-messages')){ ?>  
          <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-user-graduate"></i>
            <p>Whatsapp Messages
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
           
           <?php if(hasPermission('admin-messages')){ ?>
             <?php if(hasPermission('admin-result-message')){ ?>
              <li class="nav-item"> 
                <a href="<?= base_url('admin/students_list?status=1') ?>" class="nav-link">  
                  <i class="nav-icon fas fa-users"></i> 
                  <p>Send Test Series Result
                  </p> 
                </a> 
              </li>
              <li class="nav-item"> 
                <a href="<?= base_url('admin/students_w_result_list?status=1') ?>" class="nav-link">  
                  <i class="nav-icon fas fa-users"></i> 
                  <p>Send Result
                  </p> 
                </a> 
              </li>
              <li class="nav-item"> 
                <a href="<?= base_url('admin/family_chalan_whatsapp') ?>"" class="nav-link">  
                  <i class="nav-icon fas fa-users"></i> 
                  <p>Send Fee Chalan</p> 
                </a> 
              </li>
              <li class="nav-item"> 
                <a href="<?= base_url('admin/family_diary_whatsapp') ?>"" class="nav-link">  
                  <i class="nav-icon fas fa-users"></i> 
                  <p>Send Daily Diary</p> 
                </a> 
              </li>
            <?php } ?>
            <?php if(hasPermission('admin-add-student-attendance')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/students_attendance/report') ?>"" class="nav-link"> 
                <i class="nav-icon far fa-address-card"></i> 
                <p>Students Absentees Report</p> 
              </a> 
            </li>
            <?php } ?>
          
            <?php } ?>            
          </ul>
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-add-teacher-subject') || hasPermission('admin-add-teacher-section') || hasPermission('admin-users') || hasPermission('admin-permissions')){ ?>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fa fa-user"></i>
            <p>Faculty
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-users')){ ?>
            <li class="nav-item">
              <a href="<?= base_url('admin/users?status=1') ?>" class="nav-link">
                <i class="nav-icon fa fa-user"></i> 
                <p>Employees</p>
              </a>
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-add-teacher-subject')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/teacher_subjects/add') ?>"" class="nav-link"> 
                <i class="nav-icon fa fa-book"></i> 
                <p>Subject Teachers
                </p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-add-teacher-section')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/teacher_section/add') ?>"" class="nav-link"> 
                <i class="nav-icon fa fa-book"></i> 
                <p>Section Incharges</p> 
              </a> 
            </li>  
            <?php } ?> 
            <?php if(hasPermission('admin-add-teacher-section')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/emp_timing/add') ?>"" class="nav-link"> 
                <i class="nav-icon fa fa-clock"></i> 
                <p>Employee Timing</p> 
              </a> 
            </li>  
            <?php } ?> 
          </ul>
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-fee-type') || hasPermission('admin-fee-amount') || hasPermission('admin-fee-chalan') || hasPermission('admin-fee-chalan-balance')){ ?>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-receipt"></i> 
            <p>Fee Management
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-fee-type')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/fee_type') ?>" class="nav-link"> 
                <i class="nav-icon fas fa-money-check-alt"></i> 
                <p>Fee Type</p> 
              </a> 
            </li>
            <?php } ?> 
            <?php if(hasPermission('admin-fee-plan-months')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/fee_plan_months/add') ?>" class="nav-link"> 
                <i class="nav-icon fas fa-money-check-alt"></i> 
                <p>Fee Plan Months</p> 
              </a> 
            </li>
            <?php } ?> 
            <?php if(hasPermission('admin-fee-amount')){?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/fee_amount/add') ?>" class="nav-link"> 
                <i class="nav-icon fa fa-calendar"></i> 
                <p>Fee Structure</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-fee-chalan')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/fee-chalan') ?>" class="nav-link"> 
                <i class="nav-icon fas fa-file-invoice"></i> 
                <p>Print Fee Chalan</p> 
              </a> 
            </li>
            <?php } ?> 
            <?php if(hasPermission('admin-fee-chalan')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/fee-chalan/add') ?>" class="nav-link"> 
                <i class="nav-icon fas fa-file-invoice"></i> 
                <p>Generate Fee Chalan</p> 
              </a> 
            </li>
            <?php } ?> 
            <?php if(hasPermission('admin-fee-chalan')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/fee-chalan-pay') ?>" class="nav-link"> 
                <i class="nav-icon fas fa-file-invoice"></i> 
                <p>Pay Fee Chalan</p> 
              </a> 
            </li>
            <?php } ?> 
            <?php if(hasPermission('admin-del-fee-chalan')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/delete-fee-chalan') ?>" class="nav-link"> 
                <i class="nav-icon fas fa-file-invoice"></i> 
                <p>Delete Fee Chalan</p> 
              </a> 
            </li>
            <?php } ?> 
            <?php if(hasPermission('admin-fee-chalan-balance')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/fee-chalan-balance') ?>" class="nav-link"> 
                <i class="nav-icon far fa-money-bill-alt"></i> 
                <p>Monthly Balance</p> 
              </a> 
            </li>
            <?php } ?> 
          </ul>
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-accounts') || hasPermission('admin-account-heads') || hasPermission('admin-account-expenses') || hasPermission('admin-account-reports')){ ?>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-receipt"></i> 
            <p>Accounts Management
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-account-heads')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/expense_head') ?>" class="nav-link"> 
                <i class="nav-icon fas fa-money-check-alt"></i> 
                <p>Expense Heads</p> 
              </a> 
            </li>
           <?php } ?> 
           <?php if(hasPermission('admin-account-expenses')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/expenses') ?>" class="nav-link"> 
                <i class="nav-icon fas fa-money-check-alt"></i> 
                <p>Expenses</p> 
              </a> 
            </li>
          <?php } ?>
          
            <?php if(hasPermission('admin-asset-heads')){ ?>
             <li class="nav-item"> 
              <a href="<?= base_url('admin/asset_heads') ?>" class="nav-link"> 
                <i class="nav-icon fas fa-money-check-alt"></i> 
                <p>Asset Heads</p> 
              </a> 
            </li>
            <?php } ?> 
            <?php if(hasPermission('admin-assets')){ ?>
             <li class="nav-item"> 
              <a href="<?= base_url('admin/assets') ?>" class="nav-link"> 
                <i class="nav-icon fas fa-money-check-alt"></i> 
                <p>Assets</p> 
              </a> 
            </li>
            <?php } ?> 
            </ul>
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-terms') || hasPermission('admin-terms-sessions') || hasPermission('admin-exams') || hasPermission('admin-datesheet') || hasPermission('admin-students-results') || hasPermission('admin-students-subject-results')){ ?>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-diagnoses"></i> 
            <p>Exams
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-exams')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/exam') ?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Exams</p> 
              </a> 
            </li>  
            <?php } ?>
            <?php if(hasPermission('admin-datesheet')){ ?>  
            <li class="nav-item"> 
              <a class="nav-link" href="<?=  base_url('admin/datesheet') ?>" clas="nav-link"> 
                <i class="nav-icon fa fa-calendar"></i> 
                <p>Date Sheet</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-students-results')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/students-results/add') ?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Results</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-students-results')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/students-results-list') ?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Results List</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-students-subject-results')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/students-subject-results/add') ?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Subject Results</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-grades')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/grades/add') ?>"" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Grades</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-grading-policy')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/grading-policy') ?>"" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Grading Policy</p> 
              </a> 
            </li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>

        <?php if(hasPermission('admin-test-series')){ ?>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-diagnoses"></i> 
            <p>Tests
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-test-series')){ ?>
            
            <li class="nav-item"> 
              <a href="<?= base_url('admin/test-results') ?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Add Tests Results</p> 
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/test-series-result-card') ?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Tests Series Results Card</p> 
              </a> 
            </li>
           
            <?php } ?>
           </ul>
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-add-student-attendance') || hasPermission('admin-add-student-absentees') || hasPermission('admin-add-student-latecomming') || hasPermission('admin-add-student-earlyleft') || hasPermission('admin-add-student-leaves') || hasPermission('admin-student-leaves')){ ?>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon far fa-address-card"></i> 
            <p>Attendance
            <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-add-student-attendance')){?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/employees_attendance/add') ?>"" class="nav-link"> 
                <i class="nav-icon fa fa-cubes"></i> 
                <p>Employees Attendance</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-add-student-attendance')){?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/employee_leaves/add') ?>"" class="nav-link"> 
                <i class="nav-icon fa fa-cubes"></i> 
                <p>Create Employee Leaves Applications</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-add-student-attendance')){?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/employee_leaves') ?>" class="nav-link"> 
                <i class="nav-icon fa fa-cubes"></i> 
                <p>Employee Leaves Applications</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-emp-attendance-monthly-report')){?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/emp_attendance_monthlyreport') ?>" class="nav-link"> 
                <i class="nav-icon fa fa-cubes"></i> 
                <p>Employees Attendance Report</p> 
              </a> 
            </li>
            <?php } ?>
            
            <?php if(hasPermission('admin-add-student-absentees')){ ?> 
            <li class="nav-item"> 
              <a href="<?= base_url('admin/students_absentees/add') ?>"" class="nav-link">  <i class="nav-icon far fa-clock"></i> 
                <p>Absentees</p> 
              </a> 
            </li>
            <?php } ?>
           
            
            <?php if(hasPermission('admin-add-student-leaves')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/students_leaves/add') ?>"" class="nav-link"> 
                <i class="nav-icon far fa-clock"></i> 
                <p>Create Leaves Applications</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-student-leaves')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/students_leaves') ?>"" class="nav-link"> 
                <i class="nav-icon far fa-clock"></i> 
                <p> Leaves Applications</p> 
              </a> 
            </li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-slots') || hasPermission('admin-school-timing') || hasPermission('admin-timetable')){ ?>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon far fa-clock"></i> 
            <p>Time Table
            <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-timetable')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/timetable/add') ?>"" class="nav-link"> 
                <i class="nav-icon far fa-clock"></i> 
                <p>Time Table</p>
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-school-timing')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/school_timing/add') ?>"" class="nav-link"> 
                <i class="nav-icon far fa-clock"></i> 
                <p>School Timing</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/school_timming_type') ?>"" class="nav-link"> 
                <i class="nav-icon far fa-clock"></i> 
                <p>School Timing Type</p>
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-slots')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/slots') ?>"" class="nav-link"> 
                <i class="nav-icon far fa-clock"></i> 
                <p>Slots</p>
              </a> 
            </li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-top-level-planning') || hasPermission('admin-weekly-planning') || hasPermission('admin-classdairy') ){ ?>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon far fa-address-card"></i> 
            <p> Academics
            <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-top-level-planning')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/top_level_planning') ?>"" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p> Top Level Planning</p>
              </a> 
            </li>  
            <?php } ?>
            <?php if(hasPermission('admin-weekly-planning')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/scheme_of_studies_view') ?>"" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p> Scheme Of Studies</p>
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-weekly-planning')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/weekly_planning_view') ?>"" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p> Weekly Planning</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/wp_objectives') ?>"" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p> Weekly Objectives</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/wp-subjects-objectives/add') ?>"" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p> Subjects Objectives</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/wp_std_weeekly_progress/add') ?>"" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p> Add Student Weekly Progress</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/wp-results-card') ?>"" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p> Student Weekly Progress</p>
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-classdairy')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/classdairy-view') ?>"" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Daily Diary</p>
               </a> 
            </li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>
       

        <?php if(hasPermission('admin-student-complaints')){ ?>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon far fa-address-card"></i> 
            <p>Complaints <i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-student-complaints')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/students-complaints') ?>"" class="nav-link">
                <i class="nav-icon fas fa-users"></i> 
                <p>Students Complaints</p>
              </a>
            </li>
            <?php } ?>  
          </ul>
        </li>
        <?php } ?>  

 <?php if(hasPermission('admin-defaulter-student-fee-report')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/defaulter_students_fee_report') ?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Defaulters Report by Fee Type</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-defaulter-student-fee-report')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/students_prevfee') ?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Student Prev Fee Report</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-defaulter-student-fee-report')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/parents_prevfee') ?>" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Family Prev Fee Report</p> 
              </a> 
            </li>
            <?php } ?>
            
        <?php if(hasPermission('admin-attendance-monthly-report') || hasPermission('admin-student-fee-report')){ ?>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon far fa-address-card"></i> 
            <p>Reports <i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-attendance-monthly-report')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/attendance-monthly-report') ?>"" class="nav-link">  <i class="nav-icon far fa-clock"></i> 
                <p>Attendance Monthly Reports</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-student-fee-report')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/student_fee_report') ?>"" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Fee Report</p> 
              </a> 
            </li>
           <?php } ?>
           <?php if(hasPermission('admin-student-fee-report')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/parents_paidfee') ?>"" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Family Paid Fee Report</p> 
              </a> 
            </li>
           <?php } ?>
            <?php if(hasPermission('admin-student-fee-report')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/parents_balancefee') ?>"" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Family Balance Fee Report</p> 
              </a> 
            </li>
           <?php } ?>
           
            <?php if(hasPermission('admin-student-fee-report')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/fee_chalan_month') ?>"" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Fee Report By Month</p> 
              </a> 
            </li>
           <?php } ?>
           <?php if(hasPermission('admin-family-fee-history')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/family_fee_history') ?>"" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Family Fee Report</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-report-by-fee-type')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/student_fee_report/report_by_fee_type') ?>"" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Report By Fee Type</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-report-by-student-fee')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/student_fee_report/report_by_fee_student') ?>"" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Report By Student Fee</p> 
              </a> 
            </li>
            <?php } ?>
             <?php if(hasPermission('admin-family-fee-report')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/family_fee_report') ?>"" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Report By Family Fee</p> 
              </a> 
            </li>
          <?php } ?>
           <?php if(hasPermission('admin-classwise-result-report')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/classwise_results') ?>"" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Class Wise Result</p> 
              </a> 
            </li>
          <?php } ?>
           <?php if(hasPermission('admin-students-result-report')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/student_results') ?>"" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Student Results</p> 
              </a> 
            </li> 
           <?php } ?>
           <?php if(hasPermission('admin-datesheet-report')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/datesheet_report/add') ?>"" class="nav-link">  
                <i class="nav-icon fas fa-users"></i> 
                <p>Datesheet Report</p> 
              </a> 
            </li> 
           <?php } ?>
            <?php if(hasPermission('admin-expense-reports')){ ?>
             <li class="nav-item"> 
              <a href="<?= base_url('admin/expense_report') ?>"" class="nav-link"> 
                <i class="nav-icon fas fa-money-check-alt"></i> 
                <p>Expenses Report</p> 
              </a> 
            </li>
            <?php } ?> 
            <?php if(hasPermission('admin-assets-report')){ ?>
             <li class="nav-item"> 
              <a href="<?= base_url('admin/assets_report') ?>"" class="nav-link"> 
                <i class="nav-icon fas fa-money-check-alt"></i> 
                <p>Assets Report</p> 
              </a> 
            </li>
            <?php } ?>
            <?php if(hasPermission('admin-profit-loss-reports')){ ?> 
             <li class="nav-item"> 
              <a href="<?= base_url('admin/profit_loss_report') ?>"" class="nav-link"> 
                <i class="nav-icon fas fa-money-check-alt"></i> 
                <p>Profit/Loss Report</p> 
              </a> 
            </li>
            <?php } ?> 
          </ul>
        </li>
        <?php } ?>  
        <?php if ($hasTransport && (hasPermission('admin-vehicles') || hasPermission('admin-transport-fee-type'))): ?>
<li class="nav-item">
  <a href="#" class="nav-link">
    <i class="nav-icon far fa-address-card"></i>
    <p>Transport <i class="right fas fa-angle-left"></i></p>
  </a>
  <ul class="nav nav-treeview">
    <?php if (hasPermission('admin-vehicles')): ?>
    <li class="nav-item">
      <a href="<?= base_url('admin/vehicles') ?>" class="nav-link">
        <i class="nav-icon fa fa-list"></i>
        <p>Vehicles</p>
      </a>
    </li>
    <?php endif; ?>

    <?php // if (hasPermission('admin-transport-fee-type')): ?>
    <!--
    <li class="nav-item">
      <a href="<?php // echo '#/transport_fee_type?m=add'; ?>" class="nav-link">
        <i class="nav-icon fa fa-list"></i>
        <p>Transport Fee</p>
      </a>
    </li>
    -->
    <?php // endif; ?>
  </ul>
</li>
<?php endif; ?>

     <?php if ($hasHostel && hasPermission('admin-blocks')): ?>
<li class="nav-item">
  <a href="#" class="nav-link">
    <i class="nav-icon fa fa-list"></i>
    <p>Hostel <i class="right fas fa-angle-left"></i></p>
  </a>
  <ul class="nav nav-treeview">
    <li class="nav-item">
      <a href="<?= base_url('admin/h_blocks') ?>" class="nav-link">
        <i class="nav-icon fa fa-list"></i><p>Blocks</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="<?= base_url('admin/h_rooms') ?>" class="nav-link">
        <i class="nav-icon fa fa-list"></i><p>Rooms</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="<?= base_url('admin/h_beds') ?>" class="nav-link">
        <i class="nav-icon fa fa-list"></i><p>Beds</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="<?= base_url('admin/h_block_rooms') ?>" class="nav-link">
        <i class="nav-icon fa fa-list"></i><p>Block Rooms</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="<?= base_url('admin/h_room_beds') ?>" class="nav-link">
        <i class="nav-icon fa fa-list"></i><p>Rooms Beds</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="<?= base_url('admin/h_fee_amount/add') ?>" class="nav-link">
        <i class="nav-icon fa fa-list"></i><p>Hostel Fee Amount</p>
      </a>
    </li>
    <li class="nav-item">
      <a href<?= base_url('admin/h_student_beds/add') ?> class="nav-link">
        <i class="nav-icon fa fa-list"></i><p>Student Beds</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="<?= base_url('admin/h_student_report') ?>" class="nav-link">
        <i class="nav-icon fa fa-list"></i><p>Hostel Student Report</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="<?= base_url('admin/h_student_report/report2') ?>" class="nav-link">
        <i class="nav-icon fa fa-list"></i><p>Hostel Student Report2</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="<?= base_url('admin/h_student_report/defaulter') ?>" class="nav-link">
        <i class="nav-icon fa fa-list"></i><p>Hostel Student Defaulter</p>
      </a>
    </li>
  </ul>
</li>
<?php endif; ?>
       <?php if ($hasAcademy && hasPermission('admin-academy')): ?>
<li class="nav-item">
  <a href="#" class="nav-link">
    <i class="nav-icon fa fa-list"></i>
    <p>Academy <i class="right fas fa-angle-left"></i></p>
  </a>
  <ul class="nav nav-treeview">
    <li class="nav-item">
      <a href="<?= base_url('admin/a_groups') ?>" class="nav-link">
        <i class="nav-icon fa fa-list"></i><p>A Groups</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="<?= base_url('admin/a_section_subjects') ?>" class="nav-link">
        <i class="nav-icon fa fa-list"></i><p>Class Subjects</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="<?= base_url('admin/a_subject_group/add') ?>" class="nav-link">
        <i class="nav-icon fa fa-list"></i><p>Subject Groups</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="<?= base_url('admin/a_teacher_group/add') ?>" class="nav-link">
        <i class="nav-icon fa fa-list"></i><p>Teacher Groups</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="<?= base_url('admin/a_fee_amount/add') ?>" class="nav-link">
        <i class="nav-icon fa fa-list"></i><p>A Fee Amount</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="<?= base_url('admin/students_bulk_academy_fee') ?>" class="nav-link">
        <i class="nav-icon fa fa-list"></i><p>A Students</p>
      </a>
    </li>
  </ul>
</li>
<?php endif; ?>
        <?php //if(hasPermission('admin-subject-category') || hasPermission('admin-subject-category-topics') || hasPermission('admin-topic-skills') || hasPermission('admin-quiz')){ ?>
        <!-- <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fa fa-list"></i> 
            <p>E Learning <i class="right fas fa-angle-left">
              </i></p>
          </a> -->
         <!--  <ul class="nav nav-treeview"> -->
            <?php //if(hasPermission('admin-subjects')){ ?>
           <!--  <li class="nav-item"> 
              <a href="<?= base_url('esubjects') ?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>E Subjects</p>
              </a> 
            </li> -->
            <?php //} ?>
            <?php //if(hasPermission('admin-subject-category')){ ?>
           <!--  <li class="nav-item"> 
              <a href="<?= base_url('subject_cat') ?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>E Categories</p>
              </a> 
            </li> -->
            <?php //} ?>
            <?php //if(hasPermission('admin-subject-category-topics')){ ?>
            <!-- <li class="nav-item"> 
              <a href="<?= base_url('subject_cat_topic') ?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>E Topics</p>
              </a> 
            </li>
            <li class="nav-item"> 
              <a href="<?= base_url('worksheet') ?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Worksheets</p>
              </a> 
            </li> -->
            <?php //} ?>
            <?php //if(hasPermission('admin-topic-skills')){ ?>
           <!--  <li class="nav-item"> 
              <a href="<?= base_url('topic_skills') ?>" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p>Topic Skills</p>
              </a> 
            </li> -->
            <?php //} ?> 
          <!-- </ul>
        </li>  -->
        <?php //if(hasPermission('admin-quiz')){ ?>
        <!-- <li class="nav-item"> 
          <a href="#" class="nav-link">
            <i class="nav-icon fa fa-list"></i> 
            <p>Quiz <i class="right fas fa-angle-left">
              </i></p>
          </a>
           <ul class="nav nav-treeview">
            <li class="nav-item">
            <a href="<?= base_url('quiz') ?>" class="nav-link"> 
            <i class="nav-icon fa fa-list"></i> 
            <p>Quiz List</p>
            </a> 
            </li>
            <li class="nav-item">
            <a href="<?= base_url('quiz_xml') ?>" class="nav-link"> 
            <i class="nav-icon fa fa-list"></i> 
            <p>Quiz Xml</p>
            </a> 
            </li>
          </ul>
        </li> -->
        <?php //} ?>
        <?php //} ?>
        <?php if(hasPermission('admin-campus')){ ?>
        <li class="nav-item"> 
          <a href="<?= base_url('admin/campus') ?>"" class="nav-link"> 
            <i class="nav-icon fa fa-home"></i> 
            <p>Campus 
              <i class="right fas fa-angle-left"></i>
            </p>
          </a> 
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-custom-campus')){ ?>
        <li class="nav-item"> 
          <a href="<?= base_url('admin/custom_campus/add') ?>"" class="nav-link"> 
            <i class="nav-icon fa fa-home"></i> 
            <p>Custom Campus 
              <i class="right fas fa-angle-left"></i>
            </p>
          </a> 
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-users') || hasPermission('admin-roles')){ ?>
         <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon far fa-address-card"></i> 
            <p> Billing
            <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if(hasPermission('admin-bill-type')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/bill_type') ?>"" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p> Bill Type</p>
              </a> 
            </li>
          <?php } ?>
          <?php if(hasPermission('admin-bill-amount')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/bill_amount/add') ?>"" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p> Bill Amount</p>
              </a> 
            </li>
          <?php } ?>
          <?php if(hasPermission('admin-bill-plan-months')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/bill_plan_months') ?>"" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p> Bill Plan Months</p>
              </a> 
            </li>
          <?php } ?>
          <?php if(hasPermission('admin-campus-chalan-pay')){ ?>
            <li class="nav-item"> 
              <a href="<?= base_url('admin/campus_chalan_pay') ?>"" class="nav-link"> 
                <i class="nav-icon fa fa-list"></i> 
                <p> Pay Campus Chalan</p>
              </a> 
            </li>
          <?php } ?>
          
          </ul>
        </li>
      <?php } ?>
        <?php if(hasPermission('admin-users') || hasPermission('admin-roles')){ ?>
        <li class="nav-header">
          Plan Management
        </li>
        <?php if(hasPermission('admin-roles')){?>
        <li class="nav-item">
          <a href="<?= base_url('admin/roles') ?>"" class="nav-link">
            <i class="nav-icon fa fa-users"></i> 
            <p>Roles</p>
          </a>
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-permissions')){ ?>
        <li class="nav-item">
          <a href="<?= base_url('admin/permissions') ?>"" class="nav-link">
            <i class="nav-icon fa fa-users"></i> 
            <p>Permissions</p>
          </a>
        </li>
        <?php } ?>
        <?php } ?>
        <?php if(hasPermission('admin-pay-campus-bill')){ ?>
        <li class="nav-item"> 
          <a href="<?= base_url('admin/pay_campus_bill') ?>"" class="nav-link"> 
            <i class="nav-icon fa fa-home"></i> 
            <p>Pay Campus Bill</p>
          </a> 
        </li> 
        <?php } ?>
        <?php if(hasPermission('admin-campus-plans')){ ?>
        <li class="nav-item"> 
          <a href="<?= base_url('admin/campus_plans') ?>"" class="nav-link"> 
            <i class="nav-icon fas fa-file-invoice"></i>
            <p>Billing Invoice</p>
          </a> 
        </li>
        <?php } ?>
        <?php if(hasPermission('admin-pay-system-bill')){ ?>
        <li class="nav-item"> 
          <a href="<?= base_url('admin/pay_system_bill') ?>"" class="nav-link"> 
            <i class="nav-icon fa fa-home"></i> 
            <p>Pay System Bill</p>
          </a> 
        </li> 
        <?php } ?>
        <?php if(hasPermission('admin-ci-session_view')){ ?>
        <li class="nav-item"> 
          <a href="<?= base_url('admin/ci_session_view') ?>"" class="nav-link"> 
            <i class="nav-icon fa fa-home"></i> 
            <p>Login Log</p>
          </a> 
        </li> 
        <li class="nav-item"> 
          <a href="<?= base_url('admin/ci_session_view_demo') ?>"" class="nav-link"> 
            <i class="nav-icon fa fa-home"></i> 
            <p>Demo Login Log</p>
          </a> 
        </li> 
        <?php } ?>
      </ul>
    <!-- /.sidebar -->
  </nav>
</aside>