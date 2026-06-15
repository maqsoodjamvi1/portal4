<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){
		$header = 'Edit Topic Skills';
		$id = $info->sub_cat_topic_id;
		$cat_name = $info->sub_cat_id;
		$topic_skill = $info->topic;
		$detail = $info->detail;
	}else{
		$header = 'Add Topic Skills';
		$id = '';
		$cat_name = '';
		$topic_skill = '';
		$detail = '';
	}
?>
<?= view('components/page_header', [
    'title' => 'Topic Skills',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Topics Skills', 'active' => true],
    ],
]) ?>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-lg-12">
	<div class="card card-primary card-outline card-tabs">
    <div class="card-header p-0 pt-1 border-bottom-0">
		<ul class="nav nav-tabs">
			<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/topic_skills') ?>">Topic Skills</a></li>
			<?php if($id == ''){ ?>
				<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/topic_skills/add') ?>"><?php echo $header;?></a></li>
				<?php }else{ ?>
				<li class="nav-item"><a class="nav-link" href="<?php echo '#/topic_skills?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
				<?php } ?>
		</ul>
	<div class="card-body">	
	<div class="tab-content">
	<?php
		echo form_open('c=topic_skills&m=save', 'role="form" id="user-edit-form"');
		echo form_hidden('id', $id);
	?>
	<div class="row">
	<div class="col-lg-3">
		<div class="form-group">
			<label for="class">Subjects</label>
			<select class="form-control" name="subject_id" id="subject_id">
				<option value="">Select Subject</option>
				<?php if(isset($subjectinfo)){
					foreach ($subjectinfo as  $subjectvalue) { ?>
						<option  value="<?php echo $subjectvalue->sub_id; ?>"><?php echo $subjectvalue->subject; ?></option>
				<?php } ?>
				<?php } ?>
			</select>
		</div>
	</div>
    <div class="col-lg-3">
		<div class="form-group">
			<label for="class">Categories</label>
			<select class="form-control" name="cat_id" id="cat_id">
			</select>
		</div>
	</div>
	<div class="col-lg-3">
		<div class="form-group">
			<label for="class">Topic</label>
			<select class="form-control" name="topic_id" id="topic_id">
		</select>
		</div>
	</div>
	</div>	
	<div class="col-lg-12">
		<table id="myTable" class="table order-list">
			<thead>
			<tr>
				<td><label for="subject_name">Topic Skills</label></td>
				<td> <label for="subject_name">Detail</label></td>
				<td></td>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>
					<input type="hidden" name="rowscount[]" value="1" />	
					<input type="text" class="form-control" name="topic_skill0" id="topic_skill0" value="<?php echo $topic_skill;?>">
				</td>
				<td>
					<input type="text" class="form-control" name="detail0" id="detail" value="<?php echo $detail;?>">
				</td>
				<td><a class="deleteRow"></a></td>
			</tr>
			</tbody>
			<tfoot>
			<tr>
				<td colspan="5" style="text-align: left;">
				<input type="button" class="btn btn-lg w-100 " id="addrow" value="Add Row" />
				</td>
			</tr>
			<tr>
			</tr>
			</tfoot>
		</table>
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
	$(document).ready(function () {
    var counter = 1;
    $("#addrow").on("click", function () {
        var newRow = $("<tr>");
        var cols = "";
        cols += '<td><input type="hidden" name="rowscount[]" value="1" /><input type="text" class="form-control" name="topic_skill' + counter + '"/></td>';
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

$("#class_id").change(function(){
    var class_id = $('#class_id').val();
     $.ajax({
		url: 'admin.php?c=ajax&m=selectsubjectbyClass',
		type: "POST",
		data:{class_id:class_id },
		success:function(res){
			   $("#subject_id").html(res);
		}
		});
    });
	$("#subject_id").change(function(){
        var subject_id = $('#subject_id').val();
	     $.ajax({
            url: 'admin.php?c=ajax&m=selectcategoriesbysubject',
            type: "POST",
            data:{subject_id:subject_id },
            success:function(res){
			   $("#cat_id").html(res);
			}
         });
    });
	$("#cat_id").change(function(){
        var cat_id = $('#cat_id').val();
	     $.ajax({
            url: 'admin.php?c=ajax&m=selecttopicbycategories',
            type: "POST",
            data:{cat_id:cat_id },
            success:function(res){
			   $("#topic_id").html(res);
			}
         });
    });

$(function(){
	$('#user-edit-form').validate({
		rules:{
			topic_skill:{
				required:true,
			}
		},
		messages:{
			topic_skill:{
				required:'Topic skill is Required',
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
					location.href = '#/topic_skills';
				<?php }else{ ?>
					location.href = '#/topic_skills?m=edit&id=<?php echo $id;?>&after=edit';
				<?php } ?>
			}else{
				toastr.error(json.msg);
			}
			return false;
		}
	});
})
</script>

<?= $this->endSection() ?>