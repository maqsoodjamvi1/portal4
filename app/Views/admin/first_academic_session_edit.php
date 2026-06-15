<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>School | Login</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="<?php echo base_url();?>resource/adminlte/dist/css/AdminLTE.min.css">
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
   </div>
  <!-- /.login-logo -->
  <div class="login-box-body">
    <p class="login-box-msg">Academic Session</p>
    
<?php
	echo form_open('c=academic_session&m=save', 'role="form" id="academic-session-edit-form"');
	echo form_hidden('id', $id);
?>
			<p class="page-header">Basic</p>
                <div class="form-group">
                  <label for="session_name">Session Name (e.g 2019-20)</label>
                  <input type="text" class="form-control" name="session_name" id="session_name"  data-inputmask='"mask": "9999-99"' data-mask value="">
				  <input type="hidden" id="originalsession_name" value="<?php echo $session_name;?>">
				</div>
				<div class="form-group">
                  <label for="start_date">Start Date</label>
                  <?php if($start_date){?>
                  <input type="date" readonly class="form-control" name="start_date" id="start_date" value="<?php echo $start_date;?>">
                  <?php }else{ ?> 
                  	 <input type="date" class="form-control" name="start_date" id="start_date" value="<?php echo $start_date;?>">
                  <?php } ?>
				</div>
				<div class="form-group">
                  <label for="end_date">End Date</label>
                  <input type="date" class="form-control" name="end_date" id="end_date" value="<?php echo $end_date;?>">
				</div>
              <div class="form-group">
                <button type="submit" class="btn btn-primary">Save</button>
				<button type="reset" class="btn btn-secondary">Reset</button>
				<button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
              </div>
            <?php echo form_close();?>

    <div class="social-auth-links text-center">

    </div>
    <!-- /.social-auth-links -->

  
  </div>
  <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

<!-- jQuery 2.1.4 -->
<script src="<?php echo base_url();?>resource/adminlte/plugins/jQuery/jQuery-2.1.4.min.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>resource/js/jquery.form.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>resource/js/bootbox.js" type="text/javascript"></script>
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
	$('#loginform').validate({
		rules:{
			username:{
				required:true
			},
			password:{
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
			password:{
				required:'Please Enter Password'
			},
			captcha_code:{
				required:'Pleace Enter Captcha Code'
			}
		}

	});
	$('#loginform').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			return $('#loginform').valid();
		},
		success:function(responseText, statusText, xhr, form){
			var json = $.parseJSON(responseText);
			//console.log(json);
			if(json.session_id == false){
				window.location.href = '<?php echo base_url() . $this->config->item('index_page');?>#/academic_session?m=add';
				return;
			}
			if(json.success){
				window.location.href = '<?php echo base_url() . $this->config->item('index_page');?>';
			}else{
				$('#captcha_img').attr('src', '<?php echo base_url();?>api/captcha?_t=' + Math.random());
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