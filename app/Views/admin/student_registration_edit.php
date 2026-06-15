<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){

		$header = 'Edit Student Registration';
		$id = $info->ad_student_id;
		$parent_id = $info->parent_id;
		$class_id = $info->class_id;
		$campus_id = $info->campus_id;
		$reg_no = $info->reg_no;
		$student_name = $info->first_name;
		$father_cnic = $parentsinfo->father_cnicnew;
		$father_name = $parentsinfo->f_name;
		$date_of_birth = $info->date_of_birth;
		$gender = $info->gender;
		$father_phone = $parentsinfo->father_contact;
		$mother_phone = $parentsinfo->mother_contact;
		$address = $parentsinfo->address_line1;
		$landline = $parentsinfo->emergency_contact;
		$email = $parentsinfo->father_email;
		$student_cnic = $info->student_cnic;
		// $date = $info->date;

	}else{
		$header = 'Add Student';
		$id = '';
		$parent_id = '';
		$father_cnic = '';
		$class_id = '';
		$campus_id = '';
		$reg_no = '';
		$student_name = '';
		$father_name = '';
		$date_of_birth = '';
		$gender = '';
		$father_phone = '';
		$mother_phone = '';
		$address = '';
		$landline = '';
		$email = '';
		$student_cnic = '';
	}
?>
<script>
function checkfathercnic() {
 	var father_cnic = $('#cnic').val();
 	      $.ajax({
            url: 'admin.php?c=ajax&m=check_father_cinic',
            type: "POST",
            data:{father_cnic: father_cnic},
            success:function(res){
						if(res){
							 var sjson = $.parseJSON(res);
							 var students = sjson.student_info;
							 $("#parent_id").val(sjson.parent_id);
						   $("#father_name").val(sjson.f_name);
						   $("#father_phone").val(sjson.father_contact);
						   $("#email").val(sjson.father_email);
						   $("#father_occupation").val(sjson.father_occupation);
						   $("#father_office_address").val(sjson.father_office_address);	
						   $("#mother_phone").val(sjson.mother_contact);
			         $("#landline").val(sjson.emergency_contact);
						   $("#address").val(sjson.address_line1);

						   if(students){
							   	students.forEach(myFunction);
							   	var strInfo = 'Already Registed Studends';
									function myFunction(item) {
									    $("#studentInfo").append(item.student_name+'<br>');
									}
								}

						}
					}
        });

 }
</script>
<?= view('components/page_header', [
    'title' => 'Student Registration',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Student Registration', 'active' => true],
    ],
]) ?>

<!-- Main content -->
<section class="content">
<div class="row">
<div class="col-lg-12">
  <div class="card card-primary card-outline card-tabs">
    <div class="card-header p-0 pt-1 border-bottom-0">
	<ul class="nav nav-tabs">
		<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/student_registration') ?>">Registered Students </a></li>
		<?php if($id == ''){ ?>
		<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/student_registration/add') ?>"><?php echo $header;?></a></li>
		<?php }else{ ?>
		<li class="nav-item"><a class="nav-link active" href="<?php echo '#/student_registration?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
		<?php } ?>
	</ul>
	<div class="card-body">
	<div class="tab-content">
	<?php
		echo form_open('c=student_registration&m=save', 'role="form" id="user-edit-form"');
		echo form_hidden('id', $id);
	?>
	<input type="hidden" name="parent_id" id="parent_id" value="<?php echo $parent_id; ?>"  /> 
	<div class="row">	
		<?php if($id){ ?>
		<div class="col-sm-6">	
		   <div class="form-group">
          <label for="student_name">Reg No</label>
          <input type="text" readonly class="form-control" name="reg_no" id="reg_no" value="<?php echo $reg_no;?>">
			</div>
		</div>
	<?php } ?>
	<label class="col-sm-12">Parent Info</label>
		<div class="col-sm-4">	
		   <div class="form-group">
          <label for="father_cnic">Father's CNIC</label> 
          <input type="text" <?php if($id){ ?> readonly <?php } ?> class="form-control" name="cnic" id="cnic"  onkeyup="checkfathercnic()"  value="<?php echo $father_cnic;?>" required data-inputmask='"mask": "99999-9999999-9"' data-mask>
			<div id="studentInfo"></div>
			</div>
		</div>
			<div class="col-sm-4">	
		   <div class="form-group">
          <label for="father_name">Father's Name</label>
          <input type="text" class="form-control" name="father_name" id="father_name" value="<?php echo $father_name;?>" required>
			</div>
			</div>
			<div class="col-sm-4">
			 <div class="form-group">
          <label for="father_phone">Cell# 1</label>
          <input type="text" class="form-control" name="father_phone" id="father_phone" value="<?php echo $father_phone;?>" data-inputmask="'mask': '0399-999999999'"  type = "number" maxlength = "12"  data-mask>
			</div>
			</div>
			<div class="col-sm-4">
			 <div class="form-group">
          <label for="mother_phone">Cell# 2</label>
          <input type="text" class="form-control" name="mother_phone" id="mother_phone" value="<?php echo $mother_phone;?>" data-inputmask="'mask': '0399-999999999'"  type = "number" maxlength = "12"  data-mask>
			</div>
			</div>
			<div class="col-sm-4">
			 <div class="form-group">
          <label for="mother_phone">Landline</label>
          <input type="text" class="form-control" name="landline" id="landline" value="<?php echo $landline; ?>">
			</div>
			</div>
			<div class="col-sm-4">
			 <div class="form-group">
          <label for="address">Address</label>
          <input type="address" class="form-control" name="address" id="address" value="<?php echo $address;?>">
			</div>
			</div>
			
			<!--  <div class="col-sm-6">
			 <div class="form-group">
          <label for="email">Email</label>
          <input type="email" class="form-control" name="email" id="email" value="<?php echo $email;?>" >
			</div>
			</div> -->
			<label class="col-sm-12">Candidate's Info</label>
			<div class="col-sm-4">	
		   <div class="form-group">
          <label for="student_name">Candidate's Name</label>
          <input type="text" class="form-control" name="student_name" id="student_name" value="<?php echo $student_name;?>" required>
			</div>
			</div>
			<div class="col-sm-4">
				<label for="mother_phone">Classes</label>
			 	<select class="form-control"  name="class_id" id="class_id" required >
            <option value="">Select Class</option>
            <?php if(isset($classinfo)){
            foreach ($classinfo as  $classvalue) { ?>
            <option <?php if($classvalue['class_id'] == $class_id){ ?> selected<?php } ?> value="<?php echo $classvalue['class_id']; ?>"><?php echo $classvalue['class_name']; ?></option>
            <?php } ?>
            <?php } ?>
      	</select>
			</div>

			<div <?php if($class_id == 4 || $class_id == 5){ ?> style="display:block;" <?php }else{ ?> style="display: none;" <?php } ?> class="col-sm-4 studentCnic">
		   <div class="form-group">
          <label for="student_name">Candidate's CNIC</label>
          <input class="form-control" type="text" id="student_cnic"  name="student_cnic" value="<?php echo $student_cnic;?>" data-inputmask='"mask": "99999-9999999-9"' data-mask>
			</div>
			</div>
			<div class="col-sm-4">	
		   <div id="dateOfBirth" class="form-group">
          <div class="form-group"> 
          <label>Date of Birth</label>
		  		<div class="input-group date" id="datepicker" data-target-input="nearest">
          <input type="text" class="form-control datetimepicker-input" data-bs-target="#datepicker"  name="date_of_birth" required value="<?php echo $date_of_birth;?>"/>
          <span class="input-group-text" data-bs-target="#datepicker" data-bs-toggle="datetimepicker"><i class="fa fa-calendar"></i></span>
        </div>
      	</div>
			</div>
			</div>
			<div class="col-sm-4">
				<div class="form-group">
					<label>Gender</label><br>
					<div class="candidate_genders">
					<label><input type="radio" name="gender" <?php if($gender == 'b'){ ?> checked <?php } ?> required value="b"> &nbsp;Boy&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
					<label><input type="radio" name="gender" <?php if($gender == 'g'){ ?> checked <?php } ?> required value="g"> &nbsp;Girl</label>
					</div>
				</div>
			</div>
			<div class="col-sm-12">
				<div class="form-group">
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

	$('#datepicker').datetimepicker({
       format: 'YYYY-MM-DD'
     });
    $('#datepicker2').datetimepicker({
        format: 'YYYY-MM-DD'
    });

	$('[data-mask]').inputmask();
  $('#user-edit-form').validate({
    rules:{
    	<?php if(!$id){ ?>
      cnic:{
        required:true,
        // remote:{
        //   param:{
        //     url:'<?= base_url('admin/ajax/check_parent_value&table=parents&field=father_cnicnew') ?>'
        //   },
        //   depends:function(element){
        //     var id = $(element).attr('id');
        //     return ($(element).val() !== $('#original' + id).val());
        //   }
        // }
      },
      email:{
        email:true,
        // remote:{
        //   param:{
        //     url:'<?php echo base_url('admin/ajax/check_parent_email&table=parents&field=father_email'); ?>' 
        //   },
        //   depends:function(element){
        //     var id = $(element).attr('id');
        //     return ($(element).val() !== $('#original' + id).val());
        //   }
        // }
      },
    <?php } ?>
    },
    messages:{
      cnic:{
      	required:'CNIC is Required',
      	<?php //if(!$id){ ?>
        	// remote:'CNIC already exists.'
      	<?php //} ?>
      	},
      email:{
        email:'Invalid Email',
        <?php //if(!$id){ ?>
        // remote:'Email is exists'
      <?php //} ?>
      }
    }
  });
	$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			return $('#user-edit-form').valid();
			$('#submitBtn').html("Saving!");
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
					location.href = '#/student_registration';
					<?php
				}else{
					?>
					location.href = '#/student_registration';
					<?php
				}
				?>
			}else{
				toastr.error(json.msg);
			}
			return false;
		}
	});

	$("#class_id").change(function(){

  var class_id = $(this).val();
  if(class_id == 4 || class_id == 5){
    $('.studentCnic').show();
  }else{
    $('.studentCnic').hide();
  }

  // $.ajax({
  //       url: 'admin.php?c=ajax&m=getAgeCriteria',
  //       type: "POST",
  //       data:{class_id:class_id},
  //       success:function(res){
  //         $("#dateOfBirth").html(res);
  //      }
  // });

  $.ajax({
        url: 'admin.php?c=ajax&m=getGenderCriteria',
        type: "POST",
        data:{class_id:class_id},
        success:function(res){
          $(".candidate_genders").html(res);
       }
  });

});

})
</script>

<?= $this->endSection() ?>