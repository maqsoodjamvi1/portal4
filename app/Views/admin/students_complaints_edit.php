<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info) ){

		$header = 'Edit Students Complaints';
		$id = $info->student_id;
		$class_id = $info->class_id;
		$subject_id = $info->sub_id;
		$obtained_marks = $info->obtained_marks;
		$total_marks = $info->Total_marks;
		$campus_id = $sessionData['campusid'];
		$session_id = $sessionData['sessionid'];
	}else{
		$header = 'Add Students Complaints';
		$id = '';
		$class_id = '';
		$subject_id = '';
		$obtained_marks = 0;
		$total_marks = 0;
		$campus_id = $sessionData['campusid'];
		$session_id = $sessionData['sessionid'];
	}
?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>
          Student Complaints
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Student Complaints</li>
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
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students-complaints') ?>">Students Complaints</a></li>
          <?php if($id == ''){ ?>
          <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/students_compaints/add') ?>"><?php echo $header;?></a></li>
          <?php }else{ ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo '/admin/students_compaints/edit?id=' . $id;?>"><?php echo $header;?></a></li>
          <?php } ?>
        </ul>
    <div class="card-body">
    <div class="tab-content">
		 <?php
			echo form_open( base_url('admin/students_complaints/save'), 'role="form" id="user-edit-form"');
			echo form_hidden('id', (string)$id);
		  ?>			
		<div class="row">
		   <input type="hidden" name="campus_id" id="campus_id" value="<?php echo $campus_id; ?>" />
		    <div class="form-inline" style="margin-bottom: 20px;">
	            <div class="form-group pull-left" style="margin-left:8px;">
	              <select class="form-control select2" name="section_id" id="section_id">
	              	 <option value="0">Select Section</option>
	                <?php if(isset($sectionsclassinfo)){
						  foreach ($sectionsclassinfo as  $sectionvalue) { ?>
	                <option  value="<?php echo $sectionvalue['section_id']; ?>"><?php echo $sectionvalue['sectionclassname']; ?></option>
	              	  <?php } ?>
	                <?php	} ?>
	              </select>
	            </div>
	            <div class="form-group  pull-left" style="margin-left:15px;">
	            	<select class="form-control select2" name="type" id="type_id">
	            		<option value="Study">Study</option>
	            		<option value="Discipline">Discipline</option>
	            	</select>
	           </div>
	            <div class="form-group  pull-left" style="margin-left:15px;">
	             <input type="date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" class="form-control" style="line-height: 15px;padding: 0 10px;">
	           </div>
	            <div class="form-group  pull-left">
	            <button type="button" onclick="getstudents();" class="btn btn-primary" style="height:40px;margin-left:15px;line-height: 10px;">View</button>
	           </div>
	       </div> 
		 </div> 
		<div class="row"> 
		  <div class="col-md-12 bg">
		        <div id="loader-1" class="overlay text-center" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
		  </div>       
		  <div class="col-lg-12">
         	<div id="students_list_container" ></div>
		  </div>
		  <div class="col-lg-12">
		  <div class="form-group">
            <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
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
<script>
function getstudents() {
 		$("#loader-1").css("display", "block");
		var campus_id = $('#campus_id').val();
		var section_id = $('#section_id').val();
		var type = $('#type_id').val();
		var date = $('#date').val();
		
 	      $.ajax({
            url: '/admin/students_complaints/get_students_byclass',
            type: "POST",
            data:{section_id: section_id,campus_id:campus_id,type:type,date:date },
            success:function(res){
 			   $("#students_list_container").html(res);
 			   $("#loader-1").css("display", "none");
 			  }
         });
 }
</script>
<script type="text/javascript">

$(function(){
	//$(".select").select2({closeOnSelect:false});	
	
	$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			$('#submitBtn').html("Ajax Request is Processing!");
      		$('#submitBtn').prop('disabled', true);
		},
		success:function(responseText, statusText, xhr, form){
			$('#submitBtn').html("Submit");
      		$('#submitBtn').prop('disabled', false);
			var json = $.parseJSON(responseText);
			if(json.success){
				toastr.success(json.msg);
				<?php
				if($id == ''){
					?>
					location.href = '/admin/students_complaints';
					<?php
				}else{
					?>
					location.href = '/admin/students_complaints/edit?id=<?php echo $id;?>&after=edit';
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