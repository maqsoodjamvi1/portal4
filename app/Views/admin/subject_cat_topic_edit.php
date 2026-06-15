<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	$header = 'Add Subject Category Topic';
	$id = '';
	$cat_name = '';
	$topic_name = '';
	$class_id = '';
	$detail = '';
	$subject_id = '';
?>
<?= view('components/page_header', [
    'title' => 'Subject Category Topics',
    'icon' => 'fas fa-tags',
    'subtitle' => $header ?? null,
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Category Topics', 'url' => base_url('admin/subject_cat_topic')],
        ['label' => isset($info) ? 'Edit' : 'Add', 'active' => true],
    ],
]) ?>
<!-- Main content -->
<section class="content">
<div class="row">
<div class="col-lg-12">
<div class="card card-primary card-outline card-tabs">
    <div class="card-header p-0 pt-1 border-bottom-0">
	<ul class="nav nav-tabs">
		<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/subject_cat_topic') ?>">Subject Categories Topic</a></li>
		<?php if($id == ''){ ?>
		<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/subject_cat_topic/add') ?>"><?php echo $header;?></a></li>
		<?php }else{ ?>
		<li class="nav-item"><a class="nav-link" href="<?php echo '#/subject_cat_topic?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
		<?php } ?>
	</ul>
<div class="card-body">		
<div class="tab-content">
<?php
	echo form_open('c=subject_cat_topic&m=save', 'role="form" id="user-edit-form"');
	echo form_hidden('id', $id);
?>
<div class="row">	
<div class="col-lg-4">
<div class="form-group">
<label for="class">Subjects</label>
<select class="form-control" name="subject_id" id="subject_id">
	<option value="">Select Subject</option>
<?php if(isset($subjectinfo)){
	foreach ($subjectinfo as  $subjectvalue) { ?>
		<option <?php if($subjectvalue->sub_id == $subject_id) { ?> selected <?php } ?> value="<?php echo $subjectvalue->sub_id; ?>"><?php echo $subjectvalue->subject; ?></option>
<?php } ?>
<?php } ?>
</select>
</div>
</div>
<div class="col-lg-4">
	<div class="form-group">
		<label for="class">Categories</label>
			<select class="form-control" name="cat_id" id="cat_id"> 
			</select>
	</div>	
</div>
</div>
 <div class="col-md-12 bg">
	<div id="loader-1" class="overlay text-center" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
</div>
<div class="col-lg-12 clearfix">
<div id="sub_cat_topic_list"></div>
</div>
<div class="col-lg-12">
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
$(document).ready(function () {
    var counter = 1;
    $("#addrow").on("click", function () {
        var newRow = $("<tr>");
        var cols = "";
        cols += '<td><input type="hidden" name="rowscount[]" value="1" /><input type="text" class="form-control" name="topic_name' + counter + '"/></td>';
        cols += '<td><input type="text" class="form-control" name="detail' + counter + '"/></td>';
        cols += '<td><input type="button" class="ibtnDel btn btn-md btn-danger "  value="Delete"></td>';
        newRow.append(cols);
        $("table.order-list").append(newRow);
        counter++;
    });
    $("table.order-list").on("click", ".ibtnDel", function (event) {
        $(this).closest("tr").remove();       
        counter -= 1
    });
});

$("#subject_id").change(function(){
 var subject_id = $('#subject_id').val();
  $.ajax({
	  url: 'admin.php?c=subject_cat_topic&m=selectcategoriesbysubject',
	  type: "POST",
	  data:{subject_id:subject_id },
	  success:function(res){
   		$("#cat_id").html(res);
   		}
     });

});
	
$("#cat_id").change(function(){
	$("#loader-1").css("display", "block");
    var cat_id = $('#cat_id').val();
     $.ajax({
        url: 'admin.php?c=subject_cat_topic&m=getTopicsCat',
        type: "POST",
        data:{cat_id:cat_id },
        success:function(res){
		   $("#sub_cat_topic_list").html(res);
		   $("#loader-1").css("display", "none");
			}

     });
});
$(function(){
	$('#user-edit-form').validate({
		rules:{
			topic_name:{
				required:true,
			}
		},
		messages:{
			topic_name:{
				required:'Topic is Required',
			}
		}
	});
	$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			return $('#user-edit-form').valid();
			$('#submitBtn').html("Ajax Request is Processing!");
      		$('#submitBtn').prop('disabled', true);
		},
		success:function(responseText, statusText, xhr, form){
			$('#submitBtn').html("Submit");
      		$('#submitBtn').prop('disabled', false);
			var json = $.parseJSON(responseText);
			if(json.success){
				toastr.success(json.msg);
				<?php if($id == ''){ ?>
					location.href = '#/subject_cat_topic';
				<?php }else{ ?>
					location.href = '#/subject_cat_topic?m=edit&id=<?php echo $id;?>&after=edit';
				<?php } ?>
			}else{
				toastr.error(json.msg);
			}
			return false;
		}
	});
});
</script>

<?= $this->endSection() ?>