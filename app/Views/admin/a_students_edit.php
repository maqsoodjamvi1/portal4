<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  $status = ''; 
  if(!empty($_GET['status'])){
   $status = $_GET['status']; 
  }
	if(isset($info)){
		$header = 'Edit Student';
		$id =  $info->student_id;
		$parent_id = $parentsinfo->parent_id;
		$reg_no =  $info->reg_no;
		$first_name =  $info->first_name;
		$last_name =  $info->last_name;
		$date_of_birth =  $info->date_of_birth;
		$gender =  $info->gender;
		$religion =  $parentsinfo->religion;
		$father_cnic =  $parentsinfo->father_cnicnew;
		$f_name =  $parentsinfo->f_name;
		$father_contact =  $parentsinfo->father_contact;
		$father_email =  $parentsinfo->father_email;
		$father_occupation =  $parentsinfo->father_occupation;
		$father_office_address =  $parentsinfo->father_office_address;
		$m_name  =  $parentsinfo->m_name;
		$mother_contact =  $parentsinfo->mother_contact;
        $whatsapp_contact =  $parentsinfo->whatsapp;
      	$address_line1 =  $parentsinfo->address_line1;
		$previous_school =  $info->previous_school;
		$ps_city =  $info->ps_city;
       	$city =  $parentsinfo->city;
		$hear_source =  $parentsinfo->hear_source;
		$date_of_admission =  $info->date_of_admission;
		$discounted_amount =  $info->discounted_amount;
		$emergency_contact_person =  $parentsinfo->emergency_contact_person;
		$emergency_contact =  $parentsinfo->emergency_contact;
       	$emergency_address = $parentsinfo->a_address;
		$health_conditions =  $info->health_conditions;
		$major_injuries =  $info->major_injuries;
		$profile_photo =  $info->profile_photo;
		
        $session_id =  $info->session_id;
      	$status = intval($info->status);
		$campus_id = $sessionData['campusid'];
				
		}else{
		$header = 'Add Student';
		$id = '';
		$parent_id = '';
		$reg_no = $reg_no;
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
		$classesfee = '';
		$m_last_name = '';
		$mother_contact = '';
        $whatsapp_contact = '';
      	$address_line1 = '';
		$city = '';
		$previous_school = '';
		$ps_city = '';
		$hear_source = '';
		$date_of_admission = '';
		$class_id = 0;
		$section_id = 0;
		$session_id = 0;
		$discounted_amount = '';
		$emergency_contact_person = '';
		$emergency_contact = '';
		$emergency_address = '';
		$health_conditions = '';
		$major_injuries = '';
		$profile_photo =  '';
		$status = 0;
		$campus_id = $sessionData['campusid'];
	}
?>
<script>
function checkfathercnic() {
 	var father_cnic = $('#father_cnic').val();
 	      $.ajax({
            url: 'admin.php?c=ajax&m=a_check_father_cinic',
            type: "POST",
            data:{father_cnic: father_cnic, },
            success:function(res){
			if(res){

			var sjson = $.parseJSON(res);
			//console.log(sjson.parent_id);
			
			   $("#parent_id").val(sjson.parent_id);
			   $("#religion").val(sjson.religion);
			   $("#f_name").val(sjson.f_name);
			   $("#father_contact").val(sjson.father_contact);
			   $("#father_email").val(sjson.father_email);
			   $("#father_occupation").val(sjson.father_occupation);
			   $("#father_office_address").val(sjson.father_office_address);	
			   $("#address_line1").val(sjson.address_line1);
			   $("#city").val(sjson.city);
			   $("#m_name").val(sjson.m_name);
			   $("#mother_contact").val(sjson.mother_contact);
         	   $("#whatsapp_contact").val(sjson.whatsapp);
			   $("#hear_source").val(sjson.hear_source);
			   $("#emergency_contact_person").val(sjson.emergency_contact_person);
			   $("#emergency_contact").val(sjson.emergency_contact);
			   $("#a_address").val(sjson.a_address);
			}
			}
         });

 }
</script>
<style type="text/css">	
	@media print{
   .noprint{
       display:none;
   }
}
.nav-pills-custom .nav-link.active{
	color: #fff !important;
}
</style>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>
           Students
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Students</li>
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
        
        <div class="card-body">  
        <div class="tab-content p-3"  style="background: linear-gradient(to left, #dce35b, #45b649);">
      	<?php
      		 if(!empty($max_limit)){
      		echo $max_limit;
      		exit;

      	} ?>	
		<!-- <input type="hidden" name="parent_id" id="parent_id" value="<?php echo $parent_id; ?>"  /> -->
	<div class="row">
      <div class="col-5 col-sm-3">
        <div class="nav flex-column nav-pills nav-pills-custom" id="v-pills-tab" role="tablist" aria-orientation="vertical">
          <a  class="nav-link mb-3 p-3 shadow active" id="v-pills-home-tab" data-toggle="pill" href="#vert-tabs-home" role="tab" aria-controls="v-pills-home" aria-selected="true">Basic Information</a>
          <a class="nav-link mb-3 p-3 shadow" id="vert-tabs-profile-tab" data-toggle="pill" href="#vert-tabs-profile" role="tab" aria-controls="v-pills-profile" aria-selected="true">Contact Information</a>
          <a class="nav-link mb-3 p-3 shadow" id="vert-tabs-messages-tab" data-toggle="pill" href="#vert-tabs-messages" role="tab" aria-controls="v-pills-messages" aria-selected="true">General Information</a>
          <a class="nav-link mb-3 p-3 shadow" id="vert-tabs-settings-tab" data-toggle="pill" href="#vert-tabs-settings" role="tab" aria-controls="v-pills-settings" aria-selected="true">Student Subjects</a>
        
      </div>
      </div>
      <div class="col-7 col-sm-9">
      <div class="tab-content" id="v-pills-tabContent">  
      <div style="min-height: 370px;" class="tab-pane fade shadow rounded bg-white show active p-3" id="vert-tabs-home" role="tabpanel" aria-labelledby="v-pills-home-tab"> 
      <?php include('a_studentstabs/basic_info.php'); ?>
      </div>
      <div style="min-height: 370px;" class="tab-pane fade shadow rounded bg-white p-2" id="vert-tabs-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">   <?php include('a_studentstabs/contact_info.php'); ?>
      </div>
      <div style="min-height: 370px;" class="tab-pane fade shadow rounded bg-white p-3" id="vert-tabs-messages" role="tabpanel" aria-labelledby="v-pills-messages-tab">  
       <?php include('a_studentstabs/general_info.php'); ?>
			</div> 
      </div>
      <div style="min-height: 370px;" class="tab-pane fade shadow rounded bg-white p-3" id="vert-tabs-settings" role="tabpanel" aria-labelledby="vert-tabs-settings-tab">
       <?php include('a_studentstabs/student_subjects.php'); ?>
      </div>
      <br>
      <?php if(isset($info)){ ?>
      <a class="btn btn-primary" href="<?= base_url('admin/a_students?m=add') ?>">Add New Student</a>
      <?php } ?>
    </div>
    </div>
  </div>
  </div>
  </div>
  <!-- Row End -->
  </div>
  </div>
  </div>
  </div>
</section>
<!-- /.content -->

<?= $this->endSection() ?>