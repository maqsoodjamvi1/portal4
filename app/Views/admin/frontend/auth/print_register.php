<html style="height:750px;margin-top: 20px;"><head>
<meta name="google-site-verification" content="_OOGIahTdWYf5XzJvmYjTvJsHOfz2hzqUg9fwM5tQPc">
<title>The Prep School</title>
<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="TIME School System is a Free online School Management system with a professional Website completely Free. You can manage  Classes, Subjects, Exams, Results and etc.">
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

<script src="<?php echo base_url();?>resource/adminlte/plugins/jquery-validation/jquery.validate.min.js"></script>
<script src="<?php echo base_url();?>resource/adminlte/plugins/jquery-validation/additional-methods.min.js"></script>
<!-- <script type="text/javascript" src="<?php echo base_url();?>resource/adminlte/plugins/select2/select2.full.min.js"></script> -->
<!-- InputMask -->
<script src="<?php echo base_url();?>resource/adminlte/plugins/moment/moment.min.js"></script>
<script src="<?php echo base_url();?>resource/adminlte/plugins/inputmask/jquery.inputmask.min.js"></script>

<script type="text/javascript" src="<?=base_url('assets/js/toastr/toastr.min.js'); ?>"></script>
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
<style>
  .col-lg-6{float: left;width: 50%;}
  input {border: 1px solid #000 !important; box-shadow: 0 none !important; border-radius: 0 !important}
#register-system-form{font-size: 16px !important}
</style>
</head>
<body>
<?php 
$date = strtotime(date('Y'));
$new_date = strtotime('+ 1 year', $date);
$next_year = date('Y', $new_date);
//print_r($studentInfo);

?>
<div class="container-fluid ">
  <div class="container">
    <div id="wb_Text1" style="width: 100%;border: 1px dashed;text-align:center;z-index:0;border-radius: 5px;padding: 10px;float:left;margin: 0 auto;">
<div style="float: left;margin-left: 30px;">
<span style="color:#000000;font-family:Arial;font-size:29px;display: block;margin-left: 25px;">THE PREP SCHOOL</span><span style="color:#000000;font-family:Arial;font-size:13px;"></span><span style="color:#000000;font-family:Arial;font-size:13px;"></span><span style="color:#000000;font-family:Arial;font-size:21px;"><strong><u>STUDENTS' ADMISSIONS ... ACADEMIC SESSION <?php echo date('Y') ?>-<?php echo substr($next_year, -2); ?></u></strong></span><span style="color:#000000;font-family:Arial;font-size:13px;"><br></span><span style="color:#000000;font-family:Arial;font-size:21px;"><strong><u>REGISTRATION FORM</u></strong></span>
</div>
<div style="float:right;">
<img src="<?=base_url('assets/imgs/sen-pre-logo.jpg'); ?>" class="img-fluid header_logo" style="max-height:100px;float: right;"> 
</div>
</div>
  </div>
</div>

<div class="container">
  
	<div class="row" style="margin-bottom:30px;">
	</div>
	<div class="row" style="margin-bottom:50px;">
    <div class="col-md-12"> 
       <b style="
    text-align: left;
    display: block;
"><span style="padding-left:15px;
    font-size: 16px;
    text-transform: uppercase;
    text-align: left;
">Please pay attention</span> </b>
  <ul style="padding-left:15px;">
  <li><span style="font-size:14px;">Provide correct information only. <br>(Any wrong data, entered now, may leads to cancellation of admission at any stage.)<br></span></li>
    <br><li><span style="font-size:14px;"> Fill in the given Registration Form and then click the <b>'Enter'</b> button to get the auto-generated Appointment Slip.</span></li>

</ul>
<div id="errors"></div>

<?php //echo form_open('auth/actionCreate'); ?>
<?php echo form_open('auth/actionCreate', 'role="form" autocomplete="off" class="form-horizontal" id="register-system-form"'); ?>
<div class="">
<div class="col-lg-6 mb-5"> Select Campus in which Admission is Required</div>
<div class="col-lg-6 mb-5">
  <div class="form-group"> 
    <input required class="form-control" id="campus" name="campus" >
    
  </div>
</div>
</div>
<div class="">
<div class="col-lg-6"> Select Level in which Admission is Required</div>
<div class="col-lg-6">
  <div class="form-group">
    <input class="form-control" name="class" id="class" required>
  </div>
</div>
</div>
<div style="clear:both;margin-top: 15px;" class="card border-primary">
    <div class="card-header bg-primary text-white" style="color:#fff !important">Candidate's Particulars</div>
    <div class="card-body">
      <div class="row">
      <div class="col-lg-6">Candidate's Name:</div>
      <div class="col-lg-6">
        <div class="form-group">
          <input class="form-control" type="text" onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || event.charCode == 32" name="name" required>
        </div>
        </div> 
      </div>
      <div class="row">
      <div class="col-lg-6"> Gender of the Candidate:</div>
      <div class="col-lg-6">
        <div class="form-group">
          <div class="candidate_genders">
            <label><input type="checkbox" name="gender" required value="b"> Boy</label>&nbsp;&nbsp;&nbsp;&nbsp;
            <label><input type="checkbox" name="gender" required value="g"> Girl</label>
          </div>
        </div>
     </div>
      </div>
      <div style="display:none;" class="studentCnic">
        <div class="col-lg-6">
          <b>CNIC #:</b> <br>(Candidate's CNIC)
        </div>
        <div class="col-lg-6">
          <div class="form-group">
            <input class="form-control" type="text" id="student_cnic"  name="student_cnic" required data-inputmask='"mask": "99999-9999999-9"' data-mask>
          </div>
        </div>
      </div>
       <div class="row">
      <div class="col-lg-6">Candidate's Date of Birth:</div>
      <div class="col-lg-6">
        <div class="form-group">
          <input class="form-control" type="text" onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || event.charCode == 32" name="name" required>
        </div>
        </div> 
      </div>

      </div>

    </div>

<div style="clear:both;margin-top: 15px;" class="card border-primary">
    <div class="card-header bg-primary text-white" style="color:#fff !important">Parent's Particulars</div>
    <div class="card-body">
<div class="row"  style="margin-bottom:15px;">
  <div class="col-lg-6">
  Father's CNIC #: <br>
  <small style="font-size: 12px;">(In case of any other CNIC # the school may cancel the admission at any stage.)</small> 
  </div>
   <div class="col-lg-6">
    <div class="form-group">
    <input class="form-control" type="text" id="cnic"  name="cnic" required 
     data-inputmask='"mask": "99999-9999999-9"' data-mask >
    <input type="hidden" id="originalcnic"    value="">
    </div>
  </div>
</div>
<div class="row"  style="margin-bottom:15px;">
  <div class="col-lg-6" style="clear:left;">
  Father's Name:
  </div>
  <div class="col-lg-6">
    <div class="form-group">
      <input class="form-control" onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || event.charCode == 32" type="text" name="fname" required>
    </div>
  </div> 
</div>
<div class="row"  style="margin-bottom:15px;">
  <div class="col-lg-6">
    Cellular Phone Number:
  </div>
  <div class="col-lg-6">
    <div class="form-group">
      <input class="form-control" type="text" id="cell" name="cell" required >
      <!-- data-inputmask="'mask': '0399-999999999'"  type = "number" maxlength = "12"  data-mask -->
    </div>
  </div> 
</div>
<div class="row"  style="margin-bottom:15px;">
  <div class="col-lg-6">
    Cellular Phone Number (Additional):
  </div>
  <div class="col-lg-6">
    <div class="form-group">
      <input class="form-control"  type="text" name="cell2" data-inputmask="'mask': '0399-999999999'"  type = "number" maxlength = "12"  data-mask>
    </div>
   </div> 
</div>
<div class="row"  style="margin-bottom:15px;">
  <div class="col-lg-6">
    Landline Number:
  </div>
  <div class="col-lg-6">
    <div class="form-group">
      <input class="form-control" type="text" name="landline" data-inputmask="'mask':'051-99999999'"  maxlength="11" minlength="11"  data-mask>
    </div>

  </div> 
</div>
<div class="row" style="margin-bottom:15px;">
  <div class="col-lg-6">
    Address:
  </div>
  <div class="col-lg-6">
    <div class="form-group" style="margin-bottom: 0px;">
      <input class="form-control" type="text" height="40px" required  autocomplete="off" style="border-bottom: 0px !important;" >
   </div>
   <div class="form-group">
      <input class="form-control" style="border-top: 1px dashed #333!important;" type="text" height="40px" required  autocomplete="off" >
   </div>
   </div> 
 </div>

<!-- <div class="row">
  <div class="col-lg-6">
    Password:<br>
    <small style="font-size: 12px;">( Please enter at least 8 characters: digit / alphabet)</small>
  </div>
  <div class="col-lg-6">
    <div class="form-group">
      <input class="form-control"  name="password"  type="password" minlength="8" required autocomplete="off">
    </div>
   </div> 
</div> -->

</div>
</div>
<div class="col-lg-12" style="margin-bottom:15px;">
  This Registration Form is NOT to be considered as Admission Form. 
</div>

      <?php echo form_close();?>
    </div>
	</div>
</div>
</body>
</html>