<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info) ){
		$header = 'Edit Students Weekly Progress';
		$id = $info->student_id;
		$class_id = $info->class_id;
		$subject_id = $info->sub_id;
		$obtained_marks = $info->obtained_marks;
		$total_marks = $info->Total_marks;
		$campus_id = $sessionData['campusid'];
		$session_id = $sessionData['sessionid'];

	}else{
		$header = 'Add Students Weekly Progress';
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
           Students Weekly Progress
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Students Weekly Progress</li>
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
		  	<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/wp_results_card') ?>">View Weekly Progress</a></li>	
          <?php if($id == ''){ ?>
          <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/wp_std_weeekly_progress/add') ?>"><?php echo $header;?></a></li>
          <?php } ?>
        </ul>
        <div class="card-body">
        <div class="tab-content">
		<?php
			echo form_open( base_url('admin/wp_std_weeekly_progress/save'), 'role="form" id="user-edit-form"');
			echo form_hidden('id', (string)$id);
		?>
		 <?php foreach($academic_session as $session){ ?>
              <input type="hidden" value="<?php echo $session->session_id; ?>"  name="session_id" id="session_id" class="form-control">
			  <?php } ?>
		 <div class="row no-print">
		 
		<input type="hidden" name="campus_id" id="campus_id" value="<?php echo $campus_id; ?>" />
		<div class="col-lg-3">
						 <label for="class">Terms</label>
							<select class="form-control" name="term_id" id="term_id" class="form-control">
						 <option value="">Select Term</option>
						<?php foreach ($terms_session_info as $value) { ?>
						    <option value="<?= $value->term_session_id ?>">
						        <?= $value->term_name ?>
						    </option>
						<?php } ?>
					</select>
		</div>
   	<div class="col-lg-2">
		    <div class="form-group">
          <label for="class">Term Weeks</label>
          <div>
           <select id="term_weeks" name="term_weeks" class="form-control" ></select>
          </div>
        </div> 
		</div>
	
          <div class="col-lg-3">
            <div class="form-group pull-left">
	              <label for="class">Classes</label>
	              <select class="form-control select2" name="class_id" id="class_id">
	              	 <option value="0">Select Class</option>
	                <?php if(isset($classesinfo)){
						  			foreach ($classesinfo as  $classvalue) { ?>
	                <option value="<?php echo $classvalue->class_id; ?>"><?php echo $classvalue->class_name; ?></option>
	              	<?php } ?>
	                <?php } ?>
	              </select>
	            </div>
          </div>
          <div class="col-lg-3">
          	<div class="form-group">
          		<label>Subjects</label>
          		<select class="form-control" name="sub_id" id="sub_id">
          			
          		</select>
          	</div>
          </div>
		<!--   <div class="col-lg-1">
		   <div class="form-group">
		    <button type="button" onclick="getstudents();" class="btn btn-primary" style="margin-top: 18px;line-height: 10px;height: 25px;">View</button>
          </div>
		  </div> -->
		  <!--  <div class="col-lg-1 pull-right">
		   <div class="form-group">
		    <button type="button" onclick="printout();" class="btn btn-primary" style="margin-top: 18px;line-height: 10px;height: 25px;">Print</button>
          </div>
		  </div> -->
		  </div>
		  <div class="row">          
		  <div class="col-lg-12">
         <div id="students_list_container"></div>
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
<script type="text/javascript">
$(function(){
		$("#term_id").change(function(){
        var term_id = $('#term_id').val();
	     $.ajax({
            url: '/admin/ajax/select-term-weeks',
            type: "POST",
            data:{term_id:term_id },
            success:function(res){
 			   $("#term_weeks").html(res);
 			 }
         });
    });	
});
</script>
<script>
$("#sub_id").change(function(){		
	var session_id = $('#session_id').val();
	var term_weeks = $('#term_weeks').val();
	var campus_id = $('#campus_id').val();
	var class_id = $('#class_id').val();
	var sub_id = $('#sub_id').val();
	$.ajax({
    	url: '/admin/wp_std_weeekly_progress/get_students',
        type: "POST",
        data:{term_weeks:term_weeks,session_id:session_id,sub_id:sub_id,class_id: class_id,campus_id:campus_id },
        success:function(res){
			   $("#students_list_container").html(res);
		}
     });
 });


  $("#class_id").change(function(){
        var class_id = $('#class_id').val();
         $.ajax({
            url: '/admin/wp_std_weeekly_progress/select-section-subjectby-section',
            type: "POST",
            data:{class_id:class_id },
            success:function(res){
 			   $("#sub_id").html(res);
			 }
         });
    });	
</script>
<script type="text/javascript">
$(function(){
	//$(".select").select2({closeOnSelect:false});	
	$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			//return $('#user-edit-form').valid();
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
					//location.href = '#/students_results';
					<?php
				}else{
					?>
					//location.href = '#/students_results?m=edit&id=<?php echo $id;?>&after=edit';
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