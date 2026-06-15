<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	$schoolinfo = getSchoolInfo();
	if(isset($info)){
		$header = 'Edit Campus';
		$id = $info->campus_id;
		$campus_name = $info->campus_name;
		$short_name = $info->short_name;
		$landline = $info->landline;
		$mobile_no = $info->mobile_no;
		$location = $info->location;
		
		$plan_id = $campus_bills_info->plan_id;
		$install_id = $campus_bills_info->install_id;
		$max_students = $campus_bills_info->max_students;
		$max_fee = $campus_bills_info->max_fee;
		
		//$expiry = $info->expiry;
		$bank_name = $info->bank_name;
		$bank_address = $info->bank_address;
		$bank_code = $info->bank_code;
		$bank_acc = $info->bank_acc;
		$chalan_h_msg = $info->chalan_h_msg;
		$chalan_f_msg = $info->chalan_f_msg;
		$late_fee_type = $info->late_fee_type;
		$late_fee_fine = $info->late_fee_fine;
		$fee_issue_date = $info->fee_issue_date;
		$fee_due_date = $info->fee_due_date;
		
		$first_name = $info->first_name;
		$last_name = $info->last_name;
		$username = $info->username;
		$email = $info->email;
		$password = $info->password;
	

	}else{
		$header = 'Add Campus';
		$id = '';
		$campus_name = '';
		$short_name = '';
		$landline = '';
		$mobile_no = '';
		$location = '';
		$username = '';
		$email = '';
		$first_name = '';
		$last_name = '';
		$password = '';

		$plan_id = '';
		$install_id = '';
		$max_students = '';
		$max_fee = '';

		$expiry = '';
		$bank_name = '';
		$bank_address = '';
		$bank_code = '';
		$bank_acc = '';
		$chalan_h_msg = '';
		$chalan_f_msg = '';
		$late_fee_fine = '';
		$late_fee_type = '';
		$fee_issue_date = '';
		$fee_due_date = '';
	}
?>
<?= view('components/page_header', [
    'title' => $header ?? 'Campus',
    'icon' => 'fas fa-school',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Campus', 'url' => base_url('admin/campus')],
        ['label' => isset($info) ? 'Edit' : 'Add', 'active' => true],
    ],
]) ?>
<!-- Main content -->
<section class="content">
<div class="row">
<div class="col-lg-12">
  <div class="card sms-card card-primary card-outline card-tabs">
  <div class="card-header p-0 pt-1 border-bottom-0">
	<ul class="nav nav-tabs">
		<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/campus') ?>">Campus</a></li>
			<?php if($id == ''){ ?>
			<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/campus/add') ?>"><?php echo $header;?></a></li>
			<?php }else{ ?>
			<li class="nav-item"><a class="nav-link active" href="<?php echo '#/campus?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
			<?php } ?>
	</ul>
<div class="card-body">	
<div class="tab-content">
	<?php
		echo form_open(base_url('admin/campus/save'), 'role="form" id="campus-edit-form"');
		echo form_hidden('id', $id);
		echo form_hidden('system_id', $schoolinfo->system_id);
	?>
	<div class="row">
		<div class="form-group col-lg-3">
          <label for="campus_name">Campus Name</label>
          <input type="text" class="form-control" name="campus_name" id="subject_name" value="<?php echo $campus_name;?>">
		</div>
		<div class="form-group col-lg-3">
          <label for="short_name">Short Name</label>
          <input type="text" class="form-control" name="short_name" id="short_name" value="<?php echo $short_name;?>">
		</div>
		<!-- <div class="form-group col-lg-4">
          <label for="landline">Landline</label>
          <input type="text" class="form-control" name="landline" id="landline" value="<?php echo $landline;?>">
		</div> -->
		<div class="form-group col-lg-3">
          <label for="mobile_no">Mobile No</label>
          <input type="text" class="form-control" name="mobile_no" id="mobile_no" value="<?php echo $mobile_no;?>">
		</div>
		<div class="form-group col-lg-3">
          <label for="location">Location</label>
          <input type="text" class="form-control" name="location" id="location" value="<?php echo $location;?>">
		</div>
		
		<p class="col-lg-12" style="font-weight: bold;margin-top: 15px;text-decoration: underline;">Campus Direct User Info</p>
		<div class="form-group col-lg-3">
        <label for="location">First Name</label>
        <input type="text" class="form-control" name="first_name" id="first_name" value="<?php echo $first_name;?>">
		</div>
		<div class="form-group col-lg-3">
        <label for="location">Last Name</label>
        <input type="text" class="form-control" name="last_name" id="last_name" value="<?php echo $last_name;?>">
		</div>
		<div class="form-group col-lg-3">
        <label for="location">Email</label>
        <input type="text" class="form-control" name="email" id="email" value="<?php echo $email;?>">
		</div>
		<div class="form-group col-lg-3">
        <label for="username">User Name</label>
        <input type="text" class="form-control" name="username" id="username" value="<?php echo $username;?>" placeholder="e.g. campus.director01">
        <small id="usernameHelp" class="form-text text-muted">Use letters, numbers, dot, underscore, hyphen.</small>
		</div>
		<div class="form-group col-lg-3">
        <label for="location">Password</label>
        <div class="input-group">
          <input type="password" class="form-control" name="password" id="password" value="" autocomplete="new-password">
          <button type="button" class="btn btn-outline-secondary toggle-password" data-bs-target="#password">Show</button>
        </div>
		</div>
		<div class="form-group col-lg-3">
        <label for="repassword">Re-Password</label>
        <div class="input-group">
          <input type="password" class="form-control" name="repassword" id="repassword" value="" autocomplete="new-password">
          <button type="button" class="btn btn-outline-secondary toggle-password" data-bs-target="#repassword">Show</button>
        </div>
		</div>
		<?php if(isset($info)){ ?>
		<p class="col-lg-12" style="font-weight: bold;margin-top: 15px;text-decoration: underline;">Student Fee Chalan Info</p>
		<div class="form-group col-lg-4">
        <label for="location">Bank Name</label>
        <input type="text" class="form-control" name="bank_name" id="bank_name" value="<?php echo $bank_name;?>">
		</div>
		<div class="form-group col-lg-4">
        <label for="location">Bank Address</label>
        <input type="text" class="form-control" name="bank_address" id="bank_address" value="<?php echo $bank_address;?>">
		</div>
		<div class="form-group col-lg-4">
        <label for="location">Bank Code</label>
        <input type="text" class="form-control" name="bank_code" id="bank_code" value="<?php echo $bank_code;?>">
		</div>
		<div class="form-group col-lg-4">
        <label for="location">Bank Account</label>
        <input type="text" class="form-control" name="bank_acc" id="expiry" value="<?php echo $bank_acc;?>">
		</div>
		<div class="form-group col-lg-4">
        <label for="location">Chalan Header Massage</label>
        <input type="text" class="form-control" name="chalan_h_msg" id="chalan_h_msg" value="<?php echo $chalan_h_msg;?>">
		</div>
		<div class="form-group col-lg-4">
        <label for="location">Chalan Footer Massage</label>
        <input type="text" class="form-control" name="chalan_f_msg" id="chalan_f_msg" value="<?php echo $chalan_f_msg;?>">
		</div>
		<div class="form-group col-lg-4">
        <label for="location">Fine Type</label>
        <select name="fine_type" class="form-control">
        	<option value="per_day_fine">Per Day Fine</option>
        	<option value="fixed_fine">Fixed Fine</option>
        </select>
		</div>
		<div class="form-group col-lg-4">
        <label for="location">Late Fee Fine</label>
        <input type="text" class="form-control" name="late_fee_fine" id="late_fee_fine" value="<?php echo $late_fee_fine;?>">
		</div>
		<div class="form-group col-lg-4">
        <label for="location">Fee Issue Date</label>
        <input type="date" class="form-control" name="fee_issue_date" id="fee_issue_date" value="<?php echo $fee_issue_date;?>">
		</div>
		<div class="form-group col-lg-4">
        <label for="location">Fee Due Date</label>
        <input type="date" class="form-control" name="fee_due_date" id="fee_due_date" value="<?php echo $fee_due_date;?>">
		</div>
		<?php } ?>
		<?php if ($id == '' && !empty($default_plan)) { ?>
		<p class="col-lg-12" style="font-weight: bold;margin-top: 15px;text-decoration: underline;">Campus plan (default)</p>
		<div class="form-group col-lg-12">
			<div class="alert alert-info mb-0">
				New campuses use <strong><?= esc($default_plan->plan_name ?? 'Plan') ?></strong> (plan ID <?= (int) ($default_plan->plan_id ?? 3) ?>)
				with a <strong>yearly</strong> subscription (bill period <?= isset($yearly_install->month_count) ? (int) $yearly_install->month_count : 12 ?> months).
				Limits and amount come from <code>system_plans</code> for that package.
			</div>
		</div>
		<?php } elseif ($id == '') { ?>
		<div class="form-group col-lg-12">
			<div class="alert alert-warning mb-0">Default plan (ID 3) was not found in the database. Save may fail until system_plans is configured.</div>
		</div>
		<?php } else { ?>
		<p class="col-lg-12" style="font-weight: bold;margin-top: 15px;text-decoration: underline;">Campus Plan</p>
		<div class="form-group col-lg-4">
          <label for="location">Max Number Of Students</label>
          <select name="max_students" id="max_students" class="form-control" required>
          		<option value="300">0-300</option>
          		<option value="500">301-500</option>
          		<option value="1000">501-1000</option>
          </select>
		</div>
		<div class="form-group col-lg-4">
      <label for="location">Max Student Fee</label>
      <select name="max_fee" id="max_fee" class="form-control" required>
      		<option  value="5000">5000</option>
      </select>
		</div>
		<div class="col-sm-4"> 
	     <input type="button" value="Calculate Package" id="calculatePackage" class="btn btn-success btn-sm" style="margin-top: 32px;">
	  </div>
		<div class="form-group col-lg-12"> 
        <div class="col-sm-8"> 
        	<div id="packagePrice"></div>
        </div>   
		</div>
		<?php } ?>
		<div class="form-group col-lg-12">
      <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
		  <button type="reset" class="btn btn-secondary">Reset</button>
		  <button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
      </div>
    </div>
    <?php echo form_close();?>
	</div>
</div>
</div>
</div>
</div>
</div>
</section>
<!-- /.content -->
<script type="text/javascript">
$(function(){
	<?php if ($id != '') { ?>
	$('#max_students').select2();
	$('#max_fee').select2();
	<?php } ?>
	$('#campus-edit-form').validate({
		rules:{
			campus_name:{
				required:true,
			}
			<?php if ($id == '') { ?>
			,first_name:{ required:true },
			last_name:{ required:true },
			email:{ required:true, email:true },
			username:{ required:true, minlength:3, maxlength:100, pattern:/^[A-Za-z0-9_.-]+$/ },
			password:{ required:true, minlength:6 },
			repassword:{ required:true, equalTo:'#password' }
			<?php } ?>
		},
		messages:{
			campus_name:{
				required:'Campus name is required',
			}
			<?php if ($id == '') { ?>
			,first_name:{ required:'First name is required' },
			last_name:{ required:'Last name is required' },
			email:{ required:'Email is required', email:'Enter a valid email' },
			username:{ required:'User name is required', minlength:'At least 3 characters', maxlength:'Maximum 100 characters', pattern:'Use letters, numbers, dot, underscore or hyphen only' },
			password:{ required:'Password is required', minlength:'At least 6 characters' },
			repassword:{ required:'Re-password is required', equalTo:'Password and re-password must match' }
			<?php } ?>
		}
	});
	$(document).on('click', '.toggle-password', function(){
		var target = $($(this).attr('data-target'));
		var isPassword = target.attr('type') === 'password';
		target.attr('type', isPassword ? 'text' : 'password');
		$(this).text(isPassword ? 'Hide' : 'Show');
	});
	<?php if ($id == '') { ?>
	var usernameCheckXhr = null;
	function setUsernameHelp(message, cls){
		$('#usernameHelp').removeClass('text-muted text-success text-danger').addClass(cls).text(message);
	}
	function checkUsernameAvailability(){
		var username = $.trim($('#username').val());
		if(username.length < 3){
			setUsernameHelp('Enter at least 3 characters.', 'text-danger');
			return;
		}
		if(usernameCheckXhr){
			usernameCheckXhr.abort();
		}
		setUsernameHelp('Checking availability...', 'text-muted');
		usernameCheckXhr = $.post('<?= base_url('admin/campus/check-username') ?>', {username: username})
			.done(function(response){
				var json = typeof response === 'string' ? $.parseJSON(response) : response;
				if(json.available){
					setUsernameHelp(json.msg || 'Username is available.', 'text-success');
				}else{
					setUsernameHelp(json.msg || 'Username is not available.', 'text-danger');
				}
			})
			.fail(function(){
				setUsernameHelp('Could not check username right now.', 'text-danger');
			});
	}
	var usernameDebounce = null;
	$('#username').on('keyup blur', function(){
		clearTimeout(usernameDebounce);
		usernameDebounce = setTimeout(checkUsernameAvailability, 300);
	});
	<?php } ?>
	$('#campus-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			if(!$('#campus-edit-form').valid()){
				return false;
			}
			$('#submitBtn').html("Saving...");
      		$('#submitBtn').prop('disabled', true);
			return true;
		},
		success:function(responseText, statusText, xhr, form){
			$('#submitBtn').html("Save");
      		$('#submitBtn').prop('disabled', false);
			var json = (typeof responseText === 'string') ? $.parseJSON(responseText) : responseText;
			if(json.success){
				toastr.success(json.msg || 'Campus created successfully.');
				<?php
				if($id == ''){
					?>
					setTimeout(function(){
						location.href = '<?= base_url('admin/dashboard') ?>';
					}, 700);
					<?php
				}else{
					?>
					location.href = '<?= base_url('admin/campus/edit?id=' . (int) $id . '&after=edit') ?>';
					<?php
				}
				?>
			}else{
				toastr.error(json.msg || json.message || 'Save failed');
			}
			return false;
		},
		error:function(xhr){
			$('#submitBtn').html("Save");
      		$('#submitBtn').prop('disabled', false);
			var msg = 'Save failed. Please try again.';
			try{
				var json = xhr.responseJSON || $.parseJSON(xhr.responseText);
				if(json && (json.msg || json.message)){
					msg = json.msg || json.message;
				}
			}catch(e){}
			toastr.error(msg);
		}
	});
});
<?php if ($id != '') { ?>
$("#calculatePackage").click(function(){
        var max_fee = $('#max_fee').val();
        var max_students = $('#max_students').val();
         $.ajax({
            url: '<?php echo base_url('admin/campus/get_packages'); ?>',
            type: "POST",
            data:{max_fee:max_fee,max_students:max_students, },
            success:function(res){
 			   			$("#packagePrice").html(res);
			 			}
         });
    });
<?php } ?>
</script>

<?= $this->endSection() ?>