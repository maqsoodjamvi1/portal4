<div class="container-fluid" style="background:#282828;clear: both;">
<div class="container">
	<div class="row">
	<div class="col-md-12 btm_link">
		<!-- <div class="text-center" style=" color: #fff;"><a href="privacy_policy.php">Privacy Policy</a> - <a href="campus_policy.php">Campus Policy</a> - <a href="refund_policy.php">Refund Policy</a> - <a href="terms_and_services.php">Term And Services</a> </div> -->
		<div class="text-center" style="margin-top: 10px; color: #fff;">Copyright © 2016 : <?php echo date('Y'); ?> - TIME Soft Solution.</div>
	</div>
</div>
</div>
</div>
<!-- <script type="text/javascript">
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
</script>  --> 
</body></html>