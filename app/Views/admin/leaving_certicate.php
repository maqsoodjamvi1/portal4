<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
if (isset($info)) {
    $header = 'Edit Certificate';
    $id = $info->student_id;
    $campus_id = $info->campus_id;
    $reg_no = $info->reg_no;
    $first_name = $info->first_name;
    $last_name = $info->last_name;
    $date_of_birth = $info->date_of_birth;
    $gender = $info->gender;
    $religion = $parentsinfo->religion ?? '';
    $f_name = $parentsinfo->f_name ?? '';
    $father_contact = $parentsinfo->father_contact ?? '';
    $m_name  = $parentsinfo->m_name ?? '';
    $mother_contact = $parentsinfo->mother_contact ?? '';
    $date_of_admission = $info->date_of_admission;
    $caste = $parentsinfo->caste ?? '';
    $gr_no = $info->gr_no;
    $gr_date = $info->gr_date;
    $emergency_contact = $parentsinfo->emergency_contact ?? '';
    $class_id = $info->class_id;
    $class = $class_info->class_name;
    $section_id = $info->cls_sec_id;
    $session_id = $info->session_id;
    $leaving_date = $info->leaving_date;
    $status = intval($info->status);
} else {
    $header = 'Add Certificate'; 
    $id = '';
    $campus_id = '';
    $reg_no = ''; // FIXED
    $first_name = '';
    $last_name = '';
    $date_of_birth = '';
    $gender = '';
    $religion = '';
    $father_cnic = '';
    $f_name = '';
    $father_contact = '';
    $father_email = '';
    $father_occupation = '';
    $father_office_address = '';
    $father_office_contact = '';
    $m_name = '';
    $m_last_name = '';
    $mother_contact = '';
    $address_line1 = '';
    $city = '';
    $previous_school = '';
    $ps_city = '';
    $hear_source = '';
    $date_of_admission = '';
    $caste = '';
    $gr_no = '';
    $gr_date = '';
    $class_id = 0;
    $section_id = 0;
    $session_id = 0;
    $discounted_amount = '';
    $emergency_contact_person = '';
    $emergency_contact = '';
    $emergency_address = '';
    $health_conditions = '';
    $major_injuries = '';
    $profile_photo = '';
    $status = 1;
}
?>

<!-- Content Header (Page header) -->
<section class="content-header">
      <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>
             Certificate
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item active">Certificate</li>
          </ol>
        </div>
      </div>
    </div><!-- /.container-fluid -->
</section>
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-lg-12">
		 <div class="card card-primary card-outline card-tabs">
      		<div class="card-header p-0 pt-1 border-bottom-0">
			<ul class="nav nav-tabs">
			<li class="nav-item"><a class="nav-link" href="<?php echo base_url('admin/leaving_certificate/download?regno=').$reg_no;?>">Download Certificate</a></li>
			<?php if($id == ''){ ?>
			<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/leaving_certificate/download') ?>"><?php echo $header;?></a></li>
			<?php }else{ ?>
			<li class="nav-item"><a class="nav-link active" href="<?php echo base_url('admin/leaving_certificate/edit?id=') . $id;?>"><?php echo $header;?></a></li>
			<?php } ?>
		</ul>
		<div class="card-body">	
		<div class="tab-content">
			<?php
			echo form_open( base_url('admin/leaving_certificate/save') , 'role="form" id="user-edit-form"');
			echo form_hidden('id', $id);
			echo form_hidden('campus_id', $campus_id);
			?>
			<div class="row">
			<div class="col-lg-3">
            <div class="form-group">
              <label for="reg_no">Registration No</label>
              <input type="text" readonly class="form-control" name="reg_no" id="reg_no" value="<?php echo $reg_no;?>">
              <input type="hidden" id="originalreg_no" value="<?php echo $reg_no;?>">
            </div>
			</div>
			<div class="col-lg-3">
              <div class="form-group">
                <label for="father_cnic">Name</label>
                <input type="text" class="form-control" name="name" id="name"    value="<?php echo $first_name." ".$last_name;?>" >
              </div>
            </div>
            <div class="col-lg-3">
              <div class="form-group">
                <label for="f_first_name">Father Name</label>
                <input type="text" class="form-control" name="f_name" id="f_name" value="<?php echo $f_name;?>">
              </div>
            </div>
            <div class="col-lg-3">
              <div class="form-group">
                <label for="gender">Gender</label>
                <input type="text" class="form-control" name="gender" id="gender" value="<?php echo $gender;?>">
              </div>
            </div>
            <div class="col-lg-3">
              <div class="form-group">
                <label for="nationality">Nationality</label>
                <input type="text" class="form-control" name="nationality" id="nationality" value="Pakistani">
              </div>
            </div>
            <div class="col-lg-3">
              <div class="form-group">
                <label for="religion">Religion </label>
                <input type="text" class="form-control" name="religion" id="religion" required=""  value="<?php echo $religion;?>" >
              </div>
            </div>
            <div class="col-lg-3">
		        <div class="form-group">
		          <label for="caste">Caste</label>
		          <input type="text" class="form-control" name="caste" id="caste" value="<?php echo $caste; ?>">
		        </div>
		       </div>
		       <div class="col-lg-3">
		        <div class="form-group">
		          <label for="gr_no">G.R #</label>
		          <input type="text" class="form-control" name="gr_no" id="gr_no" value="<?php  echo $gr_no; ?>">
		        </div>
		        </div>
		        <div class="col-lg-3">
		            <?php 
		              // if(!empty($gr_date) && $gr_date != 0){
		              //   $gr_date = DateTime::createFromFormat('Y-m-d',$gr_date);
		              //   $gr_date = $gr_date->format('d/m/Y');
		              // }else{  
		              //   $gr_date = date('d/m/Y');         
		              // } 
		            ?>
		            <div class="form-group">
		              <label>G.R Date <span class="text-danger">*</span></label>
		                <input type="date" class="form-control datetimepicker-input"  id="gr_date" name="gr_date" required  value="<?php echo $gr_date; ?>" d/>
		              <!-- /.input group -->
		            </div>
		        </div>
            <div class="col-lg-3">
              <div class="form-group">
                <label for="date_of_admission">Date Of Admission</label>
                <input type="date" class="form-control" name="date_of_admission" id="date_of_admission" required="" value="<?php echo $date_of_admission;?>">
              </div>
            </div>
            <div class="col-lg-3">
              <div class="form-group">
                <label for="leaving_date">Date of leaving</label>
                <input type="date" class="form-control" name="leaving_date" id="leaving_date" required="" value="<?php echo date('Y-m-d');?>">
              </div>
            </div>
            <div class="col-lg-3">
              <div class="form-group">
                <label for="date_of_birth">Date Of Birth</label>
                <input type="date" class="form-control" name="date_of_birth" id="date_of_birth" required="" value="<?php echo $date_of_birth;?>">
              </div>
            </div>
            <div class="col-lg-3">
              <div class="form-group">
                <label for="class_passed">Class Passed</label>
                <input type="text" class="form-control" name="class_passed" id="class_passed" value="<?php echo $class; ?>">
              </div>
            </div>
            <!-- Column End -->
            <div class="col-lg-3">
              <div class="form-group">
                <label for="mother_contact">Contact</label>
                <input type="text" class="form-control" name="mother_contact" id="mother_contact" value="<?php echo $mother_contact;?>">
              </div>
            </div>
            <!-- Column End -->
            <div class="col-lg-3">
              <div class="form-group">
                <label for="remarks">Remarks</label>
                <input type="text" class="form-control" name="remarks" id="remarks" value="">
            </div>
			</div>
          <!-- Column End -->
         <div class="col-lg-3">
          <div class="form-group">
            <button type="submit" class="btn btn-primary">Generate SLC </button>
			<button type="reset" class="btn btn-default">Reset</button>
			<button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
          </div>
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
	$('#user-edit-form').validate({
	});
	$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			//return $('#user-edit-form').valid();
		},
		success:function(responseText, statusText, xhr, form){
			var json = $.parseJSON(responseText);
			if(json.success){
				toastr.success(json.msg);
				<?php if($id == ''){
					?>
					location.href = 'admin/leaving_certificate/certificate-<?php echo $reg_no;?>.html';
					<?php }else{ ?>
					location.href = 'admin/leaving_certificate/download?regno=<?php echo $reg_no;?>';
					<?php } ?>
			}else{
				toastr.error(json.msg);
			}
			return false;
		}
	});
});
</script>

<?= $this->endSection() ?>