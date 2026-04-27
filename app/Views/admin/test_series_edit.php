<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){

			$header = 'Edit Test Series';
			$id = $info->eid;
			$exam_name = $info->exam_name;
			
			$exam_start_date = DateTime::createFromFormat('Y-m-d',$info->exam_start_date);
	        $exam_start_date = $exam_start_date->format('d/m/Y');				
			
			$exam_end_date = DateTime::createFromFormat('Y-m-d',$info->exam_end_date);
	        $exam_end_date = $exam_end_date->format('d/m/Y');
			
			$campus_id = $sessionData['campusid'];
			//$session_id = $sessionData['sessionid'];

	}else{
			$header = 'Add Test Series';
			$id = '';
			$series_name = '';
			$short_name = '';
			$term_id = '';
			$session_id = '';
			$exam_start_date = '';
			$exam_end_date = '';
			$status = 1;
		}
	?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>
           Test Series
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Test Series</li>
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
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/test_series') ?>">Test Series</a></li>
          <?php if($id == ''){ ?>
          <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/test_series/add') ?>"><?php echo $header;?></a></li>
          <?php }else{ ?>
          <li class="nav-item"><a class="nav-link active" href="<?php echo 'admin/test_series/edit?id=' . $id;?>"><?php echo $header;?></a></li>
          <?php } ?>
        </ul>
        <div class="card-body">
        <div class="tab-content">
        <?php
					echo form_open( base_url('admin/test_series/save'), 'role="form" id="user-edit-form"');
					echo form_hidden('id', $id);
				?>
        <div id="exam_list">  
        <div class="">  
       	<div class="row">
				<div class="col-lg-4">
					<div class="form-group">
						<label for="exam_name">Test Series Name</label>
						<input type="text" name="series_name"  value="<?php echo $series_name; ?>" placeholder="Series Name" class="form-control "  />
					</div>
				</div>
				<div class="col-lg-4">
					<div class="form-group">
						<label for="short_name">Short Name</label>
						<input type="text" name="short_name"value="<?php echo $short_name; ?>" placeholder="Short Name" class="form-control"  />
					</div>
         		</div>
         		<div class="col-lg-12">

						<div class="row"><div class="col-lg-4">
         			<div class="form-group">
         				<label for="exam_start_date">Series Start Date</label>
	         			<div class="input-group date" id="exam_start_date" data-target-input="nearest">
                        <input type="text" name="series_start_date"  class="form-control datetimepicker-input" data-target="#exam_start_date"/>
                        <div class="input-group-append" data-target="#exam_start_date" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
         			</div>
         		</div>
         		<div class="col-lg-4">
         			<div class="form-group">
         				<label for="exam_end_date">Series End Date</label>
         				<div class="input-group date" id="exam_end_date" data-target-input="nearest">
                        <input type="text" name="series_end_date"  class="form-control datetimepicker-input" data-target="#exam_end_date"/>
                        <div class="input-group-append" data-target="#exam_end_date" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>

         			</div>
         		</div>
         		</div>
				<script type="text/javascript">
				$(function(){
				$("#exam_start_date").datetimepicker({
				      format: "DD/MM/YYYY",
				    });
				 $("#exam_end_date").datetimepicker({
				      format: "DD/MM/YYYY",
				    });   
				 });
				</script>
         		</div>
         	</div>
     </div>
 </div>	
<div class="form-group">
    <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
    <button type="reset" class="btn btn-default">Reset</button>
    <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
</div>
<?php echo form_close();?> </div>
      </div>
    </div>
  </div>
  </div>
  </div>
</section>
<!-- /.content -->
<script type="text/javascript">
$(function(){

$("#term_session_id").change(function(){
        var term_session_id = $('#term_session_id').val();
         $.ajax({
            url:'<?php echo base_url('admin/exam/getDateRange'); ?>', 
            type: "POST",
            data:{term_session_id:term_session_id },
            success:function(res){
            	//console.log(res);
 			   $("#dateRange").html(res);
 			   $("#loader-1").css("display", "none");
			 }
         });
    });

	$('#user-edit-form').validate({
		rules:{
			exam_name:{
				required:true,

			},
			exam_start_date:{
				required:true,
			}
		},
		messages:{
			exam_name:{
				required:'Exam Name is Required',
			},
			exam_start_date:{
				required:'Exam start date is Required',
				remote:'Exam start date is exists'
			}
		}
	});
	$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			return $('#user-edit-form').valid();
			$('#submitBtn').html("Saving");
      		$('#submitBtn').prop('disabled', true);
		},
		success:function(responseText, statusText, xhr, form){
			$('#submitBtn').html("Save");
      		$('#submitBtn').prop('disabled', false);
      		location.href = '/admin/test_series';
			var json = responseText;
			if(json.success){
				toastr.success(json.msg);
				<?php
				if($id == ''){
					?>
					location.href = '/admin/test_series';
					<?php
				}else{
					?>
					location.href = '/admin/test_series/edit?id=<?php echo $id;?>&after=edit';
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