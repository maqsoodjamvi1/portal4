<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info) && count($info) > 0){
		$header = 'Edit Top Level Planning';
		$id = $info->id;
		$subject_name = $info->$subject_name;
	}else{
		$header = 'Add Top Level Planning';
		$id = '';
		$subject_name = '';
	}
?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>
          Top Level Planning
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Top Level Planning</li>
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
			<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/top_level_planning') ?>">Top Level Planning</a></li>
			<?php if($id == ''){ ?>
			<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/top_level_planning/add') ?>"><?php echo $header;?></a></li>
				<?php }else{ ?>
			<li class="nav-item"><a class="nav-link active" href="<?php echo '#/top_level_planning?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
			<?php } ?>
			<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/top_level_planning_gradewise') ?>">Grade Wise Views</a></li>
		</ul>
	<div class="card-body"> 		
	<div class="tab-content">
		<?php
			 echo form_open('c=top_level_planning&m=save', 'role="form" id="user-edit-form"');
			 echo form_hidden('id', $id);
		?>
	<?php //echo form_open('c=top_level_planning&m=save', 'role="form" id="user-edit-form"'); ?>
	<?php //echo form_hidden('id', $id); ?>
	<?php //echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
	<!-- Rest of the form -->
		<div class="row">
			<div class="col-lg-4">
				 <div class="form-group">
                  <label for="term">Session</label>
				  <select name="session_id" id="session_id" class="form-control">
				  <?php foreach($academic_session as $session){ ?>
                  <option value="<?php echo $session->session_id; ?>"><?php echo $session->session_name; ?></option>
				  <?php } ?>
				  </select>
				</div>
			</div>
			<div class="col-lg-3">
              <div class="form-group">
	              <label for="class">Sections</label>
	              <select class="form-control select2" name="section_id" id="section_id">
	              	 <option value="0">Select Section</option>
	                <?php if(isset($sectionsclassinfo)){
						  foreach ($sectionsclassinfo as  $secionvalue) { ?>
	                <option value="<?php echo $secionvalue['section_id']; ?>"><?php echo $secionvalue['sectionclassname']; ?></option>
	              	<?php } ?>
	                <?php } ?>
	              </select>
	            </div>
				</div>
				<div class="col-lg-3">
				 <div class="form-group">
                  <label for="term">Subjects</label>
				  <select name="subject_id" id="subject_id" class="form-control">
				  </select>
				</div>
			</div>
			<div class="col-lg-2">
	          	<div class="form-group">
	          		<label>Synch to all campus</label><br>
	          		<input type="checkbox" value="1"  name="synch">
	          	</div>
           </div>
        <div class="col-md-12 bg">
		    <div id="loader-1" class="overlay text-center" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
		</div>  
		<div class="col-lg-12">
			<div id="subjects_list"></div>	
		</div>	
		</div>
	    <div class="form-group">
        <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
		<button type="reset" class="btn btn-secondary">Reset</button>
		<button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
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
$('#tid').change(function(){
    $('#class_id').prop('selectedIndex',0);
});
$("#session_id").change(function(){
	$("#subjects_list").html('');
});	
$('#session_id').on('change', function() {
	var session_id = $( "#session_id" ).val();
	$.ajax({
            url: 'admin.php?c=ajax&m=selectExam',
            type: "POST",
            data:{session_id: session_id, },
            success:function(res){
 			  // alert(res);		  
			   $("#eid").html(res);
 			   }
         });

	});
$('#section_id').on('change', function() {
	var section_id = $( "#section_id" ).val();
	$.ajax({
            url: 'admin.php?c=ajax&m=selectsubjectbySection',
            type: "POST",
            data:{section_id: section_id },
            success:function(res){
 			  // alert(res);		  
			   $("#subject_id").html(res);
 			   }
         });
	});

$('#subject_id').on('change', function() {
	//$("#loader-1").css("display", "block");
	var subject_id = $( "#subject_id" ).val();	
	var session_id = $( "#session_id" ).val();
	var section_id = $( "#section_id" ).val();
	
	$.ajax({
            url: 'admin.php?c=top_level_planning&m=selectSubjectsforTopLevelPlanning',
            type: "POST",
            data:{section_id:section_id,subject_id: subject_id,session_id:session_id },
            success:function(res){ 
			 			   //alert("res");		  
						   $("#subjects_list").html(res);
						   //$("#loader-1").css("display", "none");
		 			   }
         });
	});

	$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			$('#submitBtn').html("Saving");
      		$('#submitBtn').prop('disabled', true);
		},
		success:function(responseText, statusText, xhr, form){
			$('#submitBtn').html("Save");
      		$('#submitBtn').prop('disabled', false);
			var json = $.parseJSON(responseText);
			if(json.success){
			  toastr.success(json.msg);
			   $("#subjects_list").html(json.msg);
			  //location.reload();
			}else{
				toastr.error(json.msg);
			}

			return false;

		}

	});

});

</script>

<?= $this->endSection() ?>