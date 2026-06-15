<?php
  $status = ''; 
  if(!empty($_GET['status'])){
   $status = $_GET['status']; 
  }
    if(isset($info)){
        $header = 'Edit Student Bulk';
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
        if($studentclassinfo){
            $section_id =  $studentclassinfo->cls_sec_id;
        }else{
            $section_id =  $info->class_id;
        }
        $session_id =  $info->session_id;
        $status = intval($info->status);
            $campus_id = $sessionData['campusid'];
                
        }else{
        $header = 'Add Student Bulk';
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
            url: 'admin.php?c=ajax&m=check_father_cinic',
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
           Entries through Excel
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?php echo '#/';?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Entries through Excel</li>
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
         <li class="nav-item"><a class="nav-link" href="<?php echo '#/addbulkstudents?m=add';?>"> Student Names</a></li> 
        <li class="nav-item"><a class="nav-link" href="<?php echo '#/students_enroll';?>"> Enroll Students</a>
        <li class="nav-item"><a href="<?php echo '#/students_bulk_cnic';?>" class="nav-link">Father Names</a></li>        
        <li class="nav-item"><a class="nav-link" href="<?php echo '#/studentsbulk';?>">Fee Detail</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo '#/students_bulk_contacts';?>"> Contact Numbers</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo '#/students_bulk_info';?>"> Other Student Info</a>
        </li>
        <li class="nav-item"><a class="nav-link active"  href="#/students?m=addbulk">Entries through Excel</a></li>
        </ul>    
        
        <div class="card-body">  
            <a class="btn btn-primary" style="margin-left: 11px;float: left;margin-right: 15px;margin-top: 9px;" href="/uploads/addStudentSample_latest.csv">Download Sample CSV for Bulk Records</a>
         <audio autoplay  controls>
         <source src="audio/AddStudentBulk.m4a" type="audio/ogg">
          <source src="audio/AddStudentBulk.m4a" type="audio/mpeg">
        Your browser does not support the audio element.
        </audio>  
        <div class="tab-content p-3"  style="background: linear-gradient(to left, #dce35b, #45b649);">
        <?php
             if(!empty($max_limit)){
            echo $max_limit;
            exit;

        } ?>    
       
        <!-- <input type="hidden" name="parent_id" id="parent_id" value="<?php echo $parent_id; ?>"  /> -->
    <div class="row">
      <div class="col-12 col-sm-9">
      <div class="tab-content" id="v-pills-tabContent">  
    <?= form_open_multipart('#', ['role' => 'form', 'id' => 'students-edit-form-basicinfo']) ?>
<?= form_hidden('id', $id) ?>
<?= form_hidden('campus_id', $campus_id) ?>

 <input type="file" name="file" required>
<br><br>
<div id="status-msg"></div>
<div class="row">
  <div class="col-lg-12 noprint">
    <div class="form-group">
      <button type="submit" id="submitBtn" class="btn btn-primary studentsubmit">Save</button>
      <button type="reset" class="btn btn-secondary">Reset</button>
      <button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
    </div>
  </div>
</div>
<?= form_close(); ?>
      </div>
      
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


<script>
$(document).ready(function () {
  $('#students-edit-form-basicinfo').validate({
    rules: {
      file: {
        required: true,
        extension: "csv"
      }
    },
    messages: {
      file: {
        required: 'Please select a file to upload.',
        extension: 'Only .csv files are allowed.'
      }
    },
    submitHandler: function (form) {
      const formData = new FormData(form);

      swal({
        title: "Confirm Upload",
        text: "Are you sure you want to upload student data?",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, Upload",
        cancelButtonText: "Cancel",
        closeOnConfirm: false,
        showLoaderOnConfirm: true
      }, function () {
        $.ajax({
          url: "<?= base_url('admin/studentsbulkcsv/import') ?>",
          type: "POST",
          data: formData,
          dataType: "json",
          contentType: false,
          processData: false,
          cache: false,
          success: function (response) {
            if (response.type === 'success') {
              swal("Success!", $(response.message).text(), "success");
            } else {
              swal("Error", $(response.message).text(), "error");
            }
          },
          error: function (xhr) {
            swal("Server Error", "Something went wrong while uploading. Please try again.", "error");
          }
        });
      });
    }
  });
});
</script>


