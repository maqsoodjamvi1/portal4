

<?php 
  $systemInfo = getSchoolInfoFront();

?>
<html><head>
<title><?php echo $systemInfo['system_name']; ?></title>
<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="<?php echo $systemInfo['system_name']; ?> is an online School Management system with a professional Website completely Free. You can manage  Classes, Subjects, Exams, Results and etc.">
<meta name="keywords" content="School, school system, online school system, school management, students, teachers, fee collection, classes, subjects, exams, datesheet, results">
<link href="<?=base_url('assets/css/bootstrap.css'); ?>" rel="stylesheet" type="text/css">
<link href="<?=base_url('assets/css/style_frontend.css'); ?>" rel="stylesheet" type="text/css">
<!-- <link rel="stylesheet" href="<?php echo base_url();?>resource/adminlte/plugins/select2/select2.min.css"> -->
<link href="<?=base_url('assets/js/toastr/toastr.min.css'); ?>" rel="stylesheet" type="text/css">
<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" type="text/css">
<script src="<?=base_url('assets/js/jquery-1.11.1.min.js'); ?>" type="text/javascript"></script>
<script src="<?=base_url('assets/js/jquery.form.js'); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" type="text/javascript"></script>
 <script src='https://www.google.com/recaptcha/api.js'></script>
<script src="<?=base_url('assets/js/jquery.validate.min.js'); ?>" type="text/javascript"></script>
<script src="<?=base_url('assets/js/jquery-validation_additional-methods.js'); ?>" type="text/javascript"></script>
<!-- <script type="text/javascript" src="<?php echo base_url();?>resource/adminlte/plugins/select2/select2.full.min.js"></script> -->
<script type="text/javascript" src="<?=base_url('assets/js/toastr/toastr.min.js'); ?>"></script>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7013803723685197"
     crossorigin="anonymous"></script>
</head>
<body>
<div class="container-fluid no-print" style="background:#006cb7;height:80px;">
	<div class="container">
	<div class="row">
      <div class="col-sm-3" style="padding:10px;"><a href="index.php"><img src="<?php echo base_url();?>system-logo/<?php echo $systemInfo['logo']; ?>" class="img-fluid header_logo" style="max-height:63px;"></a></div>
      <div class="col-sm-9" style="margin-top:20px;"> <nav class="navbar navbar-default" style="margin-bottom: 0px;z-index:9999;"> 
        <div class="container-fluid" style="padding-right:0px;padding-left:0px;"> 
          <div class="navbar-header" style="margin-top: -75px;"> 
            <button type="button" class="navbar-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#bs-example-navbar-collapse-1" aria-expanded="false"> 
            <span class="visually-hidden">Toggle navigation</span> <span class="icon-bar"></span> 
            <span class="icon-bar"></span> <span class="icon-bar"></span> </button>
          </div>
        <div class="collapse navbar-collapse collapse_menu_bg" id="bs-example-navbar-collapse-1" style="position:absolute;z-index:9999;width:100%;margin-left: 0px;"> 
            
          </div>
        </div>
        </nav> </div>
		</div>
	</div>
</div>