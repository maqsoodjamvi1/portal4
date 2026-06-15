<div class="container-fluid" style="background:#efefef;padding:15px;">
	<div class="container">
		<div class="row" style="margin-bottom:10px;margin-top:20px;">
			<div class="col-md-3" style="margin-bottom:10px;">
				<div class="d-flex align-items-start">
				  <div class="flex-shrink-0 me-3">
					<a href="#">
					  <img class="img-fluid" src="<?=base_url('assets/png/001C.png'); ?>">
					</a>
				  </div>
				  <div class="flex-grow-1" style="font-size:12px;">
					<h4 class="mt-0" style="font-size:14px;font-weight:bold;">ADDRESS</h4>
					Office # 5 Khyber Plaza Lehtrar Road Burma Town Islamabad </div>
				</div>
			</div>
			<div class="col-md-3" style="margin-bottom:10px;">
				<div class="d-flex align-items-start">
				  <div class="flex-shrink-0 me-3">
					<a href="#">
					  <img class="img-fluid" src="<?=base_url('assets/png/002C.png'); ?>">
					</a>
				  </div>
				  <div class="flex-grow-1" style="font-size:12px;">
					<h4 class="mt-0" style="font-size:14px;font-weight:bold;">PHONE SUPPORT</h4>
					+92-300-5340592	 </div>
				</div>
			</div>
			<div class="col-md-3" style="margin-bottom:10px;">
				<div class="d-flex align-items-start">
				  <div class="flex-shrink-0 me-3">
					<a href="#">
					  <img class="img-fluid" src="<?=base_url('assets/png/003C.png'); ?>">
					</a>
				  </div>
				  <div class="flex-grow-1" style="font-size:12px;">
					<h4 class="mt-0" style="font-size:14px;font-weight:bold;">EMAIL SUPPORT</h4>
					info@timesoftsol.com<br>
					(24/7)
				  </div>
				</div>
			</div>
			<div class="col-md-3" style="margin-bottom:10px;">
				<div class="d-flex align-items-start">
				  <div class="flex-shrink-0 me-3">
					<a href="#">
					  <img class="img-fluid" src="<?=base_url('assets/png/004CA.png'); ?>">
					</a>
				  </div>
				  <div class="flex-grow-1" style="font-size:12px;">
					<h4 class="mt-0" style="font-size:14px;font-weight:bold;">SUPPORT TIME</h4>
					10:00 to 17:00 (Mon - Sat)
				  </div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="container-fluid" style="background:#282828;">
<div class="container">
	<div class="row">
	<div class="col-md-12 btm_link">
		<div class="text-center" style=" color: #fff;"><a href="privacy_policy.php">Privacy Policy</a> - <a href="campus_policy.php">Campus Policy</a> - <a href="refund_policy.php">Refund Policy</a> - <a href="terms_and_services.php">Term And Services</a> </div>
		<div class="text-center" style="margin-top: 10px; color: #fff;">Copyright © 2008 : <?php echo date('Y'); ?> - TIME Soft Solutions.</div>
	</div>
</div>
</div>
</div>
<script type="text/javascript">
$(function(){
	$('#frm_new').validate({
		rules:{
			password:{
				required:true
			},
			first_name:{
				required:true
			},
			last_name:{
				required:true
			},
			phone_no:{
				required:true
			},
			email: {
				required: true,
				email: true
			},
			school_name:{
				required:true
			},
		},		
		messages:{
			email:{
				required: "Please provide a Email",
			},
			password:{
				required:'password is required'
			}
		}
	});	
	$('#frm_new').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			return $('#frm_new').valid();
		},
		success:function(responseText, statusText, xhr, form){
			var json = $.parseJSON(responseText);
			if(json.success){
				toastr.success(json.msg);
			}else{
				toastr.error(json.msg);
			}
			return false;
		}
	});			
});
</script>  
</body></html>