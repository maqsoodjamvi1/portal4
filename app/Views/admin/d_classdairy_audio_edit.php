<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){

		$header = 'Edit Class Diary Audio';
		$id = $info->did;
		$date1 = $info->date1;
		$class_id = $info->class_id;
		$subject_id = $info->subject_id;
		$detail = $info->detail;
		$type1 = $info->type1;
		$path1 = $info->path1;

	}else{
		$header = 'Add Class Diary Audio';
		$id = '';
		$date1 = '';
		$class_id = '';
		$subject_id = '';
		//$short_title = '';
		$detail = '';
		$type1 = '';
		$path1 = '';

}
?>
<!-- Content Header (Page header) -->
  <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-8">
            <h1>
               Class Diary 
            </h1>
          </div>
          <div class="col-sm-4">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Class Diary</li>
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
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/classdairy/add') ?>">Add Class Diary</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/classdairy_view') ?>">Weekly Diary</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/classdairy_audio') ?>">Class Diary Audio</a></li>
          <?php if($id == ''){ ?>
          <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/classdairy_audio/add') ?>"><?php echo $header;?></a></li>
          <?php }else{ ?>
          <li class="nav-item"><a class="nav-link active" href="<?php echo base_url('admin/classdairy_audio/edit?id=') . $id;?>"><?php echo $header;?></a></li>
          <?php } ?>  
        </ul>
        <div class="card-body">  
        <div class="tab-content">
          <?php
			echo form_open( base_url('admin/classdairy_audio/save'), 'role="form" id="classdairy-edit-form"');
			echo form_hidden('id', $id);
			?>
			<div class="row">
			<div class="col-lg-2">
		    <div class="form-group">
              <label for="class">Terms</label>
              <select class="form-control" name="term_id" id="term_id">
						    <option value="">Select Term Session</option>
						    <?php foreach ($terms_session_info as $value): ?>
						        <option value="<?= $value->term_session_id ?>">
						            <?= $value->term_name ?>
						        </option>
						    <?php endforeach; ?>
						</select>
            </div>
		   </div> 
		    <div class="col-lg-2">
		    <div class="form-group">
              <label for="class">Term Weeks</label>
              <select class="form-control" name="term_weeks" id="term_weeks">
               
              </select>
            </div>
		   </div>
		   
		  <div class="col-lg-2">
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
		   <a class="btn btn-primary" style="margin-top: 27px;line-height: 15px;height: 30px;" onclick="selecttermWeek();" >Select Term Weeks</a>
		   </div>
		  </div>
		  <div id="termweekdates">
          
		  </div>
		  <div class="row">
		  <div class="col-lg-3">
          <div class="form-group">
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="reset" class="btn btn-secondary">Reset</button>
            <button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
          </div>
		  </div>
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

function selecttermWeek(){
	var term_id = $('#term_id').val();
    var section_id = $('#section_id').val();
	var term_weeks = $('#term_weeks').val();
	 $.ajax({
            url: '/admin/classdairy_audio/termweekdatebyclass',
            type: "POST",
            data:{term_weeks:term_weeks,section_id:section_id },
            success:function(res){
 			   $("#termweekdates").html(res);
			 }
         });
	}
</script>
<script type="text/javascript">
$(function(){
	$('#user-edit-form').validate({
		rules:{
			date:{
				required:true,
			}
		},
		messages:{
			date:{
				required:'date is Required',
			}
		}
	});
	$('#classdairy-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			return $('#classdairy-edit-form').valid();
		},
		success:function(responseText, statusText, xhr, form){
			var json = $.parseJSON(JSON.stringify(responseText));
			if(json.success){
				toastr.success(json.msg);
				<?php
				if($id == ''){
					?>
					location.href = '/admin/classdairy_audio';
					<?php
				}else{
					?>
					location.href = '/admin/classdairy_audio/edit?id=<?php echo $id;?>&after=edit';
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