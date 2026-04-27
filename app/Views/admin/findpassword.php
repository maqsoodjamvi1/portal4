<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>TLive Education | Findpassword</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.5 -->
  <link rel="stylesheet" href="<?php echo base_url();?>resource/adminlte/bootstrap/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
   <link rel="stylesheet" href="<?php echo base_url();?>resource/adminlte/dist/css/adminlte.min.css">
  <!-- <link rel="stylesheet" href="<?php echo base_url();?>resource/adminlte/plugins/iCheck/square/blue.css">
 -->
 <link rel="stylesheet" href="<?php echo base_url();?>resource/adminlte/plugins/toastr/toastr.min.css">
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body class="hold-transition login-page">
<div class="login-box">
<div class="card card-outline card-primary">	
  <div class="card-header text-center">
    <a class="h1" href="<?php echo site_url('');?>">TLive Education</a>
  </div>
  <!-- /.login-logo -->
  <div class="card-body">
    <p class="login-box-msg">Enter your username or email address and we will send you a link to reset your password.</p>
	
	<?php echo form_open('c=login&m=findpassword', 'id="findpasswordform"');?>
      <div class="input-group mb-3">
        <input type="text" class="form-control" required name="username" id="username" placeholder="Email/Username">
        <div class="input-group-append">
            <div class="input-group-text">
              <span class="fa fa-envelope"></span>
            </div>
          </div>
		    <div class="text-danger col-12" id="usernameerror"></div>
      </div>
	  <!-- <div class="form-group">
		<div class="row">
			<div class="col-lg-7">
				<input type="text" class="form-control" required name="captcha_code" id="captcha_code" placeholder="Captcha Code"><span id="captcha_codeerror"></span>
			</div>
			<div class="col-lg-5">
				<img src="<?php echo base_url();?>api/captcha" onclick="this.src='<?php echo base_url();?>api/captcha?_t=' + Math.random();" style="width:100%;height:35px;">
			</div>	
		</div>
	  </div> -->
      <div class="row">
        <div class="col-lg-8">
        </div>
        <!-- /.col -->
        <div class="col-lg-4">
          <button type="submit" class="btn btn-primary btn-block btn-flat">Submit</button>
        </div>
        <!-- /.col -->
      </div>
    <?php echo form_close();?>
    <a href="<?php echo site_url('c=login');?>">Login</a>
  </div>
  <!-- /.login-box-body -->
</div>
</div>
<!-- /.login-box -->

<!-- jQuery 2.1.4 -->
<script src="<?php echo base_url();?>resource/adminlte/plugins/jquery/jquery.min.js"></script>
<script src="<?php echo base_url();?>resource/adminlte/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>resource/js/jquery.form.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo base_url();?>resource/toastr/toastr.min.js"></script>
<script src="<?php echo base_url();?>resource/js/jquery.validate.min.js" type="text/javascript"></script>
<script>
  $(function () {
	$.validator.setDefaults({
		ignore : "",
		errorPlacement : function (error, element) {
			if ($(document).find('#' + element.attr('id') + 'error')) {
				error.appendTo($('#' + element.attr('id') + 'error'));
			}
		},
		highlight : function (element) {
			$(element).closest('.form-group').removeClass('has-success').addClass('has-error');
		},
		unhighlight : function (element) {
			$(element).closest('.form-group').removeClass('has-error');
		}
	});
	$('#findpasswordform').validate({
		rules:{
			username:{
				required:true
			},
			captcha_code:{
				required:true
			}
		},
		messages:{
			username:{
				required:'Please Enter Username or Email'
			},
			captcha_code:{
				required:'Please Enter Captcha Code'
			}
		}
	});
	$('#findpasswordform').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			return $('#findpasswordform').valid();
		},
		success:function(responseText, statusText, xhr, form){
			var json = $.parseJSON(responseText);
			if(json.success){
				toastr.success(json.msg, function(){
					location.href = '<?php echo base_url() . $this->config->item('index_page');?>';
				});
			}else{
				toastr.error(json.msg);
			}
			return false;
		}
	});	
  });
</script>
</body>
</html>

<?= $this->endSection() ?>