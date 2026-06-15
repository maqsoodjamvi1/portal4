<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	$schoolinfo = getSchoolInfo();
	if(isset($info)){
		//print_r($info);
		$header = 'Edit Custom Campus Chalan';
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
		$late_fee_fine = $info->late_fee_fine;
		$fee_issue_date = $info->fee_issue_date;
		$fee_due_date = $info->fee_due_date;
		
		$first_name = $info->first_name;
		$last_name = $info->last_name;
		$username = $info->username;
		$email = $info->email;
		$password = $info->password;
	

	}else{
		$header = 'Add Custom Campus Chalan';
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
		$fee_issue_date = '';
		$fee_due_date = '';
	}
?>
<?= view('components/page_header', [
    'title' => 'Custom Campus Chalan',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Custom Campus Chalan', 'active' => true],
    ],
]) ?>

<!-- Main content -->
<section class="content">
<div class="row">
<div class="col-lg-12">
  <div class="card card-primary card-outline card-tabs">
  <div class="card-header p-0 pt-1 border-bottom-0">
	<ul class="nav nav-tabs">
		<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/custom_campus') ?>">Custom Campus Chalan</a></li>
			<?php if($id == ''){ ?>
			<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/custom_campus/add') ?>"><?php echo $header;?></a></li>
			<?php }else{ ?>
			<li class="nav-item"><a class="nav-link active" href="<?php echo '#/custom_campus?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
			<?php } ?>
	</ul>
<div class="card-body">	
<div class="tab-content">
	<?php
		echo form_open('c=custom_campus&m=save', 'role="form" id="campus-edit-form"');
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
        <label for="location">Password</label>
        <input type="text" class="form-control" name="password" id="password" value="<?php echo $password;?>">
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
		<p class="col-lg-12" style="font-weight: bold;margin-top: 15px;text-decoration: underline;">Campus Plan</p>
		<div class="form-group col-lg-4">
          <label for="location">Max Number Of Students</label>
          <input type="number"  name="max_students" id="max_students" class="form-control" required>
  	</div>
		<div class="form-group col-lg-4">
      <label for="location">Max Student Fee</label>
      <input type="number" name="max_fee" id="max_fee" class="form-control" required>
   	</div>

   	<div class="form-group col-lg-4">
      <label for="location">Campus Bill</label>
      <input type="number"name="price" id="price" class="form-control" required>
   	</div>

		<!-- <div class="col-sm-4"> 
	     <input type="button" value="Calculate Package" id="calculatePackage" class="btn btn-success btn-sm" style="margin-top: 32px;">
	  </div> -->
		<!-- <div class="form-group col-lg-12"> 
        <div class="col-sm-8"> 
        	<div id="packagePrice"></div>
        </div>   
		</div> -->
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
	// $('#max_students').select2();
	// $('#max_fee').select2();	
	$('#campus-edit-form').validate({
		rules:{
			campus_name:{
				required:true,
			}
		},
		messages:{
			campus_name:{
				required:'campus is Required',
			}
		}
	});
	$('#campus-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			return $('#campus-edit-form').valid();
			$('#submitBtn').html("Saving");
      		$('#submitBtn').prop('disabled', true);
		},
		success:function(responseText, statusText, xhr, form){
			$('#submitBtn').html("Save");
      		$('#submitBtn').prop('disabled', false);
			var json = $.parseJSON(responseText);
			if(json.success){
				toastr.success(json.msg);
				<?php
				if($id == ''){
					?>
					location.href = '#/campus';
					<?php
				}else{
					?>
					location.href = '#/campus?m=edit&id=<?php echo $id;?>&after=edit';
					<?php
				}
				?>
			}else{
				toastr.error(json.msg);
			}
			return false;
		}
	});
});
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
</script>

<?= $this->endSection() ?>