<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>TIME |Soft Solution </title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.5 -->
  <link rel="stylesheet" href="<?php echo base_url();?>resource/adminlte/bootstrap/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?php echo base_url();?>resource/adminlte/font-awesome/4.4.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="<?php echo base_url();?>resource/adminlte/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?php echo base_url();?>resource/adminlte/dist/css/AdminLTE.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="<?php echo base_url();?>resource/adminlte/plugins/iCheck/square/blue.css">
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">
    <a href="<?php echo site_url('');?>">TIME</a>
  </div>
  <!-- /.login-logo -->
<div class="login-box-body">
<?php if($success){ ?>
	<p class="login-box-msg">TIME</p>
	<?php 
	echo form_open('c=login&m=change_password', 'id="findpasswordform"');
	echo form_hidden('user_id', $user_id);
	?>
      <div class="form-group has-feedback">
        <input type="password" class="form-control" required name="password" id="password" placeholder="">
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
		<span id="passworderror"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" class="form-control" required name="confirmpassword" id="confirmpassword" placeholder="">
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
		<span id="confirmpassworderror"></span>
      </div>	  
      <div class="row">
        <div class="col-xs-8">
        </div>
        <!-- /.col -->
        <div class="col-xs-4">
          <button type="submit" class="btn btn-primary btn-block btn-flat"></button>
        </div>
        <!-- /.col -->
      </div>
    <?php echo form_close();?>
	<?php }else{ ?>
		<p class="login-box-msg"><?php echo $message;?></p>
	<?php } ?>
    	<a href="<?php echo site_url('c=login');?>"></a>
  </div>
  <!-- /.login-box-body -->
</div>
<!-- /.login-box -->
<!-- jQuery 2.1.4 -->
<script src="<?php echo base_url();?>resource/adminlte/plugins/jQuery/jQuery-2.1.4.min.js"></script>
<!-- Bootstrap 3.3.5 -->
<script src="<?php echo base_url();?>resource/adminlte/bootstrap/js/bootstrap.min.js"></script>
<script src="<?php echo base_url();?>resource/js/jquery.form.js"></script>
<script src="<?php echo base_url();?>resource/js/bootbox.js"></script>
<script src="<?php echo base_url();?>resource/js/jquery.validate.min.js"></script>
<!-- iCheck -->
<script src="<?php echo base_url();?>resource/adminlte/plugins/iCheck/icheck.min.js"></script>
<script>
  $(function () {
    $('input').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue',
      increaseArea: '20%' // optional
    });
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
			password:{
				required:true
			},
			confirmpassword:{
				required:true,
				equalTo:'#password'
			}
		},
		messages:{
			password:{
				required:''
			},
			confirmpassword:{
				required:'',
				equalTo:''
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
				location.href = 'admin.php#/users?status=1';
			}else{
				bootbox.alert(json.msg);
			}
			return false;
		}
	});	
  });
</script>
</body>
</html>

<?= $this->endSection() ?>