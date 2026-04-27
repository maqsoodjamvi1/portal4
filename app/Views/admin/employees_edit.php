<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
			if(isset($info)){

				$header = 'Edit Employee';
				$tid = $info->tid;
				$first_name = $info->first_name;
				$last_name = $info->last_name;
				$dob = $info->dob;
				$f_first_name = $info->f_first_name;
				$f_last_name = $info->f_last_name;
				$cnic = $info->cnic;
				$gender = $info->gender;
				$marital_status = $info->marital_status;
				$joining_date = $info->joining_date;
				$email = $info->email;
				$mobile_no = $info->mobile_no;
				$mobile_no2 = $info->mobile_no2;
				$address_1 = $info->address_1;
				$address_2 = $info->address_2;
				$emergency_contact_person = $info->emergency_contact_person;
				$emergency_contact_no = $info->emergency_contact_no;
				$salary = $info->salary;
				$qualification = $info->qualification;
				$experience = $info->experience;
				$skills = $info->skills;
				$status = $info->status;
				$emp_type_id = $info->emp_type_id;
				
				//$password = '';
				//$user_type = 0;
			}else{
				$header = 'Add Employee';
				$tid = '';
				$emp_type_id= '';
				$first_name = '';
				$last_name = '';
				$dob = '';
				$f_first_name = '';
				$f_last_name = '';
				$cnic = '';
				$gender = '';
				$marital_status = '';
				$joining_date = '';
				$email = '';
				$mobile_no = '';
				$mobile_no2 = '';
				$address_1 = '';
				$address_2 = '';
				$emergency_contact_person = '';
				$emergency_contact_no = '';
				$salary = '';
				$qualification = '';
				$experience = '';
				$skills = '';
				$status = '';
				$password = '';
				$user_type = 0;
			}
			?>

    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Employees
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?= base_url('admin/dashboard') ?>"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">Employees</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
		  <div class="nav-tabs-custom">
			<ul class="nav nav-tabs">
				<li><a href="<?= base_url('admin/employees') ?>">Employees</a></li>
				<?php if($tid == ''){ ?>
					<li class="active"><a href="<?= base_url('admin/employees/add') ?>"><?php echo $header;?></a></li>
				<?php }else{ ?>
					<li class="active"><a href="<?php echo '#/employees?m=edit&id=' . $tid;?>"><?php echo $header;?></a></li>
				<?php } ?>
			</ul>
		<div class="tab-content">
		<?php
			echo form_open('c=employees&m=save', 'role="form" id="employees-edit-form"');
			echo form_hidden('id', $tid);
		?>
		<div class="row">
			<div class="col-lg-4">
                <div class="form-group">
                  <label for="first_name">First Name</label>
                  <input type="text" class="form-control" name="first_name" id="first_name" value="<?php echo $first_name;?>">
				</div>
				<div class="form-group">
					<label for="last_name">Last Name</label>
					<input type="text" class="form-control" name="last_name" id="last_name" value="<?php echo $last_name;?>">
				</div>
                <div class="form-group">
                  <label for="dob">Date Of Birth</label>
                  <input type="date" class="form-control" name="dob" id="dob" value="<?php echo $dob;?>">
				</div>
				<div class="form-group">
                  <label for="f_first_name">Father First Name</label>
                  <input type="text" class="form-control" name="f_first_name" id="f_first_name" value="<?php echo $f_first_name;?>">
				</div>
				<div class="form-group">
                  <label for="f_last_name">Father Last Name</label>
                  <input type="text" class="form-control" name="f_last_name" id="f_last_name" value="<?php echo $f_last_name;?>">
				</div>
				<div class="form-group">
                  <label for="f_cnic">CNIC</label>
                  <input type="text" class="form-control" name="username" required id="username" value="<?php echo $cnic;?>">
                   <input type="hidden" id="originalusername" value="<?php echo $cnic;?>">
				</div>
				<div class="form-group" style="margin-bottom:23px;">
				  <label for="gender">Gender</label><br>
					<label>Male</label> <input type="radio" name="gender" value="male" <?php if($gender == 'male') {?> checked="checked" <?php } ?>>
					<label>Female</label> <input type="radio" name="gender" value="female" <?php if($gender == 'female') {?> checked="checked" <?php } ?>>
				</div>
			</div>
	<div class="col-lg-4">
		<div class="form-group" style="margin-bottom:23px;">
			<label for="marital_statusmarital_status">Marital Status</label><br>
			<label>Married</label> <input type="radio" name="marital_status" value="married"  <?php if($marital_status == 'married') {?> checked="checked" <?php } ?> >
			<label>Single</label> <input type="radio" name="marital_status" value="single" <?php if($marital_status == 'single') {?> checked="checked" <?php } ?>>
		</div>
		<div class="form-group">
			<label for="joining_date">Joining Date</label>
			<input type="date" class="form-control" name="joining_date" id="joining_date" value="<?php echo date($joining_date);?>">
		</div>
		<div class="form-group">
			<label for="email">Email</label>
			<input type="text" class="form-control" name="email" required id="email" value="<?php echo $email;?>">
		</div>
		<div class="form-group">
			<label for="mobile_no">Mobile No</label>
			<input type="text" class="form-control" name="mobile_no" id="mobile_no" value="<?php echo $mobile_no;?>">
		</div>
		<div class="form-group">
			<label for="land_line">Mobile 2</label>
			<input type="text" class="form-control" name="mobile_no2" id="mobile_no2" value="<?php echo $mobile_no2;?>">
		</div>
		<div class="form-group">
			<label for="address_1">Addrees 1</label>
			<input type="text" class="form-control" name="address_1" id="address_1" value="<?php echo $address_1;?>">
		</div>
		<div class="form-group">
			<label for="address_2">Address 2</label>
			<input type="text" class="form-control" name="$address_2" id="$address_2" value="<?php echo $address_2;?>">
		</div>
	</div>

	<div class="col-lg-4">
		<div class="form-group">
			<label for="emergency_contact_person">Designation</label>
			<select name="emp_type_id" class="form-control">
			<?php foreach($emp_types as $emp_type){?>	
				<option value="<?php echo $emp_type->emp_type_id; ?>"><?php echo $emp_type->emp_type_name; ?></option>
			<?php } ?>
			</select>
		</div>
		<div class="form-group">
			<label for="emergency_contact_person">Emergency Contact Person</label>
			<input type="text" class="form-control" name="emergency_contact_person" id="emergency_contact_person" value="<?php echo $emergency_contact_person;?>">
		</div>
		<div class="form-group">
			<label for="emergency_contact_no">Emergency Contact No</label>
			<input type="text" class="form-control" name="emergency_contact_no" id="emergency_contact_no" value="<?php echo $emergency_contact_no;?>">
		</div>
		
		<div class="form-group">
			<label for="qualification">Qualification</label>
			<input type="text" class="form-control" name="qualification" id="qualification" value="<?php echo $qualification;?>">
		</div>
		<div class="form-group">
			<label for="experience">Experience</label>
			<input type="text" class="form-control" name="experience" id="experience" value="<?php echo $experience;?>">
		</div>
		<div class="form-group">
			<label for="kills">Skills</label>
			<input type="text" class="form-control" name="skills" id="skills" value="<?php echo $skills;?>">
		</div>
	</div>

</div>
<div class="row">
	<p style="padding-left: 15px;" class="page-header">User Account Info</p>
		<div class="col-xs-6">
				<div class="form-group">
					<label>User Roles(Select Multi)</label>
					<select multiple="multiple" name="roles[]" class="form-control select2" style="width:100%;">
					<?php 
					$roles = roles_list();
					foreach($roles as $role){
						?>
						<option value="<?php echo $role->id;?>" <?php echo in_array($role->id, $userRoles) ? 'selected="selected"' : '';?>><?php echo $role->roleName;?></option>
						<?php 
					}
					?>
					</select>
					
				</div>
	</div>
	<div class="col-xs-6">
				<div class="form-group">
					<label>Password</label>
				<input type="password" class="form-control" name="password">	
				</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-4">
		<div class="form-group ">
            <button type="submit" class="btn btn-primary">Save</button>
			<button type="reset" class="btn btn-default">Reset</button>
			<button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
        </div>
        <?php echo form_close();?>
	</div>
</div>
</div></div>
</div>
</div>
</section>
<!-- /.content -->
<script type="text/javascript">
$(function(){
	$(".select2").select2({closeOnSelect:false});

	$('#employees-edit-form').validate({
		rules:{
			first_name:{
				required:true,
			},
			lasr_name:{
				required:true,
			},
			username:{
				required:true,
				remote:{
					param:{
						url:'<?php echo base_url('admin/ajax/check_value&table=users&field=username'); ?>'
					},
					depends:function(element){
						var id = $(element).attr('id');
						return ($(element).val() !== $('#original' + id).val());
					}
				}
			},
			email:{
				required:true,
				email:true,
			}
		},
		messages:{
			username:{
				required:'CNIC is Required',
				remote:'CNIC is exists'
			},
			email:{
				required:'Email is Required',
				email:'Invalid Email',
				remote:'Email is exists'
			}
		}
	});
	$('#employees-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			return $('#employees-edit-form').valid();
		},
		success:function(responseText, statusText, xhr, form){
			var json = $.parseJSON(responseText);
			if(json.success){
				toastr.success(json.msg);
				<?php
				if($tid == ''){
					?>
					location.href = '#/employees';
					<?php
				}else{
					?>
					location.href = '#/employees?m=edit&id=<?php echo $tid;?>&after=edit';
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
</script>

<?= $this->endSection() ?>