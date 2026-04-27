<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
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
			$caste =  $info->caste;
			$gr_no =  $info->gr_no;
			$gr_date =  $info->gr_date;
			$discounted_amount =  $info->discounted_amount;
			$transport_discount = $info->transport_discount;
			$emergency_contact_person =  $parentsinfo->emergency_contact_person;
			$emergency_contact =  $parentsinfo->emergency_contact;
	    $emergency_address = $parentsinfo->a_address;
			$health_conditions =  $info->health_conditions;
			$major_injuries =  $info->major_injuries;
			$fee_plan =  $info->fee_plan;
			$profile_photo =  $info->profile_photo;
			if($studentclassinfo){
				$section_id =  $studentclassinfo->cls_sec_id;
			}else{
				$section_id =  $info->class_id;
			}
	    
	    $session_id =  $info->session_id;
	    $status = intval($info->status);
			$campus_id = $sessionData['campusid'];
			$student_cnic = $info->std_cnic;
				
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
			$classesfee = 0;
			$transportfee = 0;
			$m_last_name = '';
			$mother_contact = '';
	    $whatsapp_contact = '';
	    $address_line1 = '';
			$city = '';
			$previous_school = '';
			$ps_city = '';
			$hear_source = '';
			$date_of_admission = '';
			$caste =  '';
			$gr_no =  '';
			$gr_date =  '';
			$class_id = 0;
			$section_id = 0;
			$session_id = 0;
			$discounted_amount = 0;
			$transport_discount = 0;
			$emergency_contact_person = '';
			$emergency_contact = '';
			$emergency_address = '';
			$health_conditions = '';
			$major_injuries = '';
			$profile_photo =  '';
			$fee_plan =  '';
			$status = 0;
			$student_cnic = '';
			$campus_id = $sessionData['campusid'];
	}
?>
<script>
function checkfathercnic() {
 		var father_cnic = $('#father_cnic').val();
	  $.ajax({
	     url: "<?= base_url('admin/ajax/check_father_cinic') ?>",
	    type: "POST",
	    data:{father_cnic: father_cnic},
	    success:function(res){
				if(res){

						var sjson = res;
						console.log(sjson.parent_id);
				
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
        <h1>Students</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Students</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<!-- Main content -->
<section class="content">
  <div class="container-fluid px-2">
    <div class="card shadow border">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><?= isset($info) ? 'Edit Student' : 'Add Student' ?></h5>
      </div>

      <div class="card-body">

        <ul class="nav nav-tabs nav-fill mb-3" id="custom-tabs" role="tablist">
          <li class="nav-item" role="presentation">
            <a class="nav-link active" id="tab-basic" data-toggle="tab" href="#tab-basic-pane" role="tab">
              <i class="fas fa-user-graduate mr-2"></i> Basic Info
            </a>
          </li>
          <li class="nav-item" role="presentation">
            <a class="nav-link <?= !isset($info) ? 'disabled' : '' ?>" id="tab-contact" data-toggle="tab" href="#tab-contact-pane" role="tab">
              <i class="fas fa-address-book mr-2"></i> Contact Info
            </a>
          </li>
          <li class="nav-item" role="presentation">
            <a class="nav-link <?= !isset($info) ? 'disabled' : '' ?>" id="tab-general" data-toggle="tab" href="#tab-general-pane" role="tab">
              <i class="fas fa-info-circle mr-2"></i> General Info
            </a>
          </li>
       
        </ul>

        <div class="tab-content" id="custom-tabs-content">
          <div class="tab-pane fade show active" id="tab-basic-pane" role="tabpanel">
            <?php include('studentstabs/basic_info.php'); ?>
          </div>
          <div class="tab-pane fade" id="tab-contact-pane" role="tabpanel">
            <?php include('studentstabs/contact_info.php'); ?>
          </div>
          <div class="tab-pane fade" id="tab-general-pane" role="tabpanel">
            <?php include('studentstabs/general_info.php'); ?>
          </div>
          <div class="tab-pane fade" id="tab-attachments-pane" role="tabpanel">
            <?php include('studentstabs/attachements.php'); ?>
          </div>
        </div>

        <?php if (isset($info)) : ?>
          <div class="mt-4">
            <a class="btn btn-primary" href="<?= base_url('admin/students/add') ?>">Add New Student</a>
            <a target="_blank" class="btn btn-success" href="<?= base_url('admin/fee_chalan_single/add?id=' . $info->student_id) ?>">
              Add Current Student Chalan
            </a>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</section>
<!-- /.content -->

<script>
  // Optional: preserve active tab on reload
  $(function () {
    const hash = window.location.hash;
    if (hash) {
      $('#custom-tabs a[href="' + hash + '"]').tab('show');
    }

    $('#custom-tabs a').on('shown.bs.tab', function (e) {
      window.location.hash = e.target.hash;
    });
  });
</script>

<style>
  .nav-tabs .nav-link {
    font-weight: 600;
    color: #495057;
  }

  .nav-tabs .nav-link.active {
    color: #fff;
    background-color: #007bff;
    border-color: #dee2e6 #dee2e6 #fff;
  }

  .tab-content {
    background-color: #fff;
    padding: 20px;
    border-radius: 0 0 6px 6px;
    border: 1px solid #dee2e6;
    border-top: none;
  }

  .disabled {
    pointer-events: none;
    opacity: 0.5;
  }

  @media print {
    .noprint {
      display: none !important;
    }
  }
</style>


<?= $this->endSection() ?>