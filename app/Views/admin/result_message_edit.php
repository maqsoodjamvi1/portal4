<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){

		$header = 'Edit Result Message';
		$id = $info->enquiry_id;
		$name = $info->name;
		$email = $info->email;
		$contact = $info->contact;
		$address = $info->address;
		$description = $info->description;
		$date = $info->date;
		$campus_id = $sessionData['campusid'];
		$session_id = $sessionData['sessionid'];

	}else{
		$header = 'Add  Result Message';
		$id = '';
		$name = '';
		$contact = '';
		$email = '';
		$address = '';
		$description = '';
		$date = '';
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
           Result Message
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Result Message</li>
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
		<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/result_message') ?>"><?php echo $header;?></a></li>
	</ul>
	<div class="card-body">
	<div class="tab-content">
	<?php
		echo form_open('c=result_message&m=save', 'role="form" id="user-edit-form"');
		echo form_hidden('id', $id);
	?>
	<div class="row">
	<input type="hidden" name="campus_id" id="campus_id" value="<?php echo $campus_id; ?>" />
		<div class="col-lg-2">
			 <div class="form-group">
              <label for="term">Exam</label>
			  <select name="eid" id="eid" class="form-control">
			  <?php foreach($examinfo as $exam){ ?>
              <option value="<?php echo $exam->eid; ?>"><?php echo $exam->exam_name; ?></option>
			  <?php } ?>
			  </select>
			</div>
		</div>
    <div class="col-lg-3">
      <div class="form-group pull-left">
          <label for="class">Sections</label>
          <select class="form-control select2" name="cls_sec_id" id="cls_sec_id">
          	 <option value="0">Select Section</option>
            <?php if(isset($sectionsclassinfo)){
			  foreach ($sectionsclassinfo as  $secionvalue) { ?>
            <option value="<?php echo $secionvalue['section_id']; ?>"><?php echo $secionvalue['sectionclassname']; ?></option>
          	<?php } ?>
            <?php } ?>
          </select>
        </div>
    </div>
	<div class="col-lg-2">	<a href="<?= base_url('admin/result_message') ?>" style="height: 33px;line-height: 17px;margin-top: 30px;"  onclick="submitFilter();" class="btn btn-primary">View</a></div>
	<div class="col-md-12 bg">
		    <div id="loader-1" class="overlay text-center" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
		</div>  
	</div>
	<div id="defaultersList"></div>		
	<div style="clear: both;"></div>
	<div class="form-group">
    <button type="submit" id="submitBtn" class="btn btn-primary">Send SMS</button>
		<button type="reset" class="btn btn-default">Reset</button>
		<button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
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
	 	function submitFilter(){
			var month = $('#month').val();
			var fee_type_id = $('#fee_type').val();
				$("#loader-1").css("display", "block");
			 $.ajax({
            url: 'admin.php?c=result_message&m=data',
            type: "POST",
            data:{month:month,fee_type_id:fee_type_id},
            success:function(res){
		 			  $("#defaultersList").html(res);
		 			   	$("#loader-1").css("display", "none");
		 			  }
		    });	
		}

</script>
<script type="text/javascript">
$(function(){
	$('#datepicker').datetimepicker({
       format: 'YYYY-MM-DD'
     });
    $('#datepicker2').datetimepicker({
        format: 'YYYY-MM-DD'
    });
	$('#user-edit-form').validate({
		rules:{
			name:{
				required:true,
			}
		},
		messages:{
			name:{
				required:'Term is Required',	
			}
		}
	});
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
					location.href = '#/messages';
					<?php
				}else{
					?>
					location.href = '#/messages';
					<?php
				}
				?>
			}else{
				toastr.error(json.msg);
			}
			return false;
		}
	});
})
</script>

<?= $this->endSection() ?>