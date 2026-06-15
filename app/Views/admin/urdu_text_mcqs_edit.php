<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){
      $header = 'Edit Urdu MCQS';
      $id = $info->did;
      $class_id = $info->class_id;
      $subject_id = $info->subject_id;
      $detail = $info->detail;
      $date1 = $info->date1;
      $type1 = $info->type1;
      $path1 = $info->path1;
  }else{
      $header = 'Add Urdu MCQS';
      $id = '';
      $date1 = '';
      $class_id = '';
      $subject_id = '';
      $cat_id = '';
      $detail = '';
      $topic_id = '';
      $topic_skill_id = '';
  }
?>
<?= view('components/page_header', [
    'title' => 'Urdu MCQS',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Urdu MCQS', 'active' => true],
    ],
]) ?>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs">
          <li class="nav-item"><a class="nav-link" href="<?php echo '#/urdu_text_mcqs&topic_id='.$_GET['topic_id'];?>">Urdu MCQS</a></li>
          <?php if($id == ''){ ?>
          <li class="nav-item"><a class="nav-link active" href="<?php echo '#/urdu_text_mcqs?m=add&topic_id='.$_GET['topic_id'];?>"><?php echo $header;?></a></li>
          <?php }else{ ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo '#/urdu_text_mcqs?m=edit&topic_id='.$_GET['topic_id'];?>"><?php echo $header;?></a></li>
          <?php } ?>
       </ul>
  <div class="card-body">
  <?php
    echo form_open_multipart('c=urdu_text_mcqs&m=save' ,'role="form" id="user-edit-form"');
    //echo form_open_multipart('upload/do_upload');
    echo form_hidden('id', $id);
  ?>
  <div class="row">
      <div class="col-lg-2">
        <div class="form-group">
          <label for="class">Subjects</label>
            <select class="form-control" name="subject_id" id="subject_id">
              <?php if(isset($subjectinfo)){
              	foreach ($subjectinfo as  $subjectvalue) { ?>
                  <option <?php if($subjectvalue->sub_id == $subject_id) { ?> selected <?php } ?> value="<?php echo $subjectvalue->sub_id; ?>"><?php echo $subjectvalue->subject; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </div>
          </div>
		    <div class="col-lg-2">
		     <div class="form-group">
              <label for="class">Categories</label>
              <select class="form-control" name="cat_id" id="cat_id">
              	<?php if(isset($sub_category_info)){
                	foreach ($sub_category_info as  $sub_category_value) { ?>
                <option <?php if($sub_category_value->sub_cat_id == $cat_id) { ?> selected <?php } ?> value="<?php echo $sub_category_value->sub_cat_id; ?>"><?php echo $sub_category_value->cat_title; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
             </div>
		  </div>
		  <div class="col-lg-2">
		  <div class="form-group">
      <label for="class">Topic</label>
      <select class="form-control" name="topic_id" id="topic_id">
      <?php if(isset($topicinfo)){
          foreach ($topicinfo as  $topicvalue) { ?>
            <option <?php if($topicvalue->sub_cat_topic_id == $topic_id) { ?> selected <?php } ?> value="<?php echo $topicvalue->sub_cat_topic_id; ?>"><?php echo $topicvalue->topic; ?></option>
        <?php } ?>
      <?php } ?>
      </select>
      </div>
    </div>
   <input type="hidden"  name="content_type_id" value="5" id="content_type_id">
</div>
<div class="col-lg-12">
  <table id="myTable" class=" table order-list">
    <thead>
      <tr>
        <td><div style="text-align: center;font-weight: bold;">Questions</div></td>
        <td><div style="text-align: center;font-weight: bold;">Hint</div></td>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style="border: 4px solid blue; border-end: 0 none !important;">
          <input type="hidden" name="optionscount[]" value="1" />
          <textarea class="form-control editor" name="question_text0" placeholder="Question" id="question_text" style="margin-bottom: 4px;"></textarea>
          <div class="col-sm-6"><input type="text" name="option_text00" id="option_text0" class="form-control" placeholder="Correct Option" style="margin-bottom: 5px;" /></div> 
          <div class="col-sm-6"><input type="text" name="option_text10" id="option_text1" class="form-control" placeholder="Alternate Option 1" style="margin-bottom: 5px;" /></div>     
        </td>     
        <td style="border: 4px solid blue;border-start: 0 none !important;border-end: 0 none !important;"> <textarea class="form-control editor" name="hint_text0" placeholder="Hint" id="hint_text"></textarea>
        <div class="col-sm-6"> <input type="text" name="option_text20" id="option_text3" class="form-control" placeholder="Alternate Option 2" style="margin-bottom: 5px;" /></div>
        <div class="col-sm-6"><input type="text" name="option_text30" id="option_text4" class="form-control" placeholder="Alternate Option 3" style="margin-bottom: 5px;" /></div>
        </td>
        <td style="border: 4px solid blue; border-start: 0 none !important;"><a class="deleteRow"></a></td>
      </tr>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="5" style="text-align: left;">
          <input type="button" class="btn btn-lg w-100 btn-primary"  id="addrow" value="Add Question" />
        </td>
      </tr>
      <tr>
      </tr>
    </tfoot>
</table>
</div>
<div class="row">
  <div class="col-lg-12">
    <div class="col-lg-3">
      <div class="form-group">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="reset" class="btn btn-secondary">Reset</button>
        <button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
      </div>	 
    </div>
  </div>
</div>
</div>
<?php echo form_close();?> </div>
</div>
</div>
</div>
</section>
<!-- /.content -->
<script type="text/javascript">
$(document).ready(function() {
  $('.editor').summernote();
});	
$("#topic_id").change(function(){
  var topic_id = $('#topic_id').val();
  $.ajax({
    url: 'admin.php?c=ajax&m=selectSkillsbyTopic',
    type: "POST",
    data:{topic_id:topic_id },
    success:function(res){
      $("#topic_skill_id").html(res);
    }
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
      //console.log(res);
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
      //console.log(res);
      $("#topic_id").html(res);
    }
  });
});

$(function(){
$("#quiz_type").change(function () {
    var DropVal = this.value;
    if(DropVal == 2){
    $("#addrow").attr('disabled','disabled');
    $("#option_text").removeAttr('disabled');
		$("#option_image").removeAttr('disabled');
		$("#option_audio").removeAttr('disabled');	
		}else if(DropVal == 3){
		$("#addrow").attr('disabled','disabled');
		$("#option_text").attr('disabled','disabled');
		$("#option_image").attr('disabled','disabled');
		$("#option_audio").attr('disabled','disabled');		
		}else if(DropVal == 1 || DropVal == 4){
		$("#addrow").removeAttr('disabled');
		$("#option_text").removeAttr('disabled');
		$("#option_image").removeAttr('disabled');
		$("#option_audio").removeAttr('disabled');
		}
});
$('#user-edit-form').validate({ });
$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			//return $('#user-edit-form').valid();
		},
		success:function(responseText, statusText, xhr, form){
			var json = $.parseJSON(JSON.stringify(responseText));
			if(json.success){
				toastr.success(json.msg);
				<?php
				if($id == ''){
					?>
					location.href = '#/question_quiz';
					<?php
				}else{
					?>
					location.href = '#/question_quiz?m=edit&id=<?php echo $id;?>&after=edit';
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
$(document).ready(function () {
    var counter = 1;
    $("#addrow").on("click", function () {
        var newRow = $("<tr>");
        var cols = "";
        cols += '<td style="border: 4px solid blue; border-end: 0 none !important;"><input type="hidden" name="optionscount[]" value="1" /> <textarea class="form-control editor2" name="question_text'+ counter +'" placeholder="Question" id="question_text" style="margin-bottom: 4px;"></textarea><div class="col-sm-6"><input type="text" name="option_text0'+ counter +'" id="option_text0" class="form-control" placeholder="Correct Option" style="margin-bottom: 5px;" /></div><div class="col-sm-6"><input type="text" name="option_text1'+ counter +'" id="option_text1" class="form-control" placeholder="Alternate Option 1" style="margin-bottom: 5px;" /></div></td><td style="border: 4px solid blue; border-end: 0 none !important;border-start: 0 none !important;"><textarea class="form-control editor2" name="hint_text'+ counter +'" placeholder="Hint" id="hint_text"></textarea><div class="col-sm-6"><input type="text" name="option_text2'+ counter +'" id="option_text2" class="form-control" placeholder="Alternate Option 2" style="margin-bottom: 5px;" /></div><div class="col-sm-6"><input type="text" name="option_text3'+ counter +'" id="option_text3" class="form-control" placeholder="Alternate Option 3" style="margin-bottom: 5px;" /></div></td>';

        cols += '<td style="border: 4px solid blue; border-start: 0 none !important;"><input type="button" class="ibtnDel btn btn-md btn-danger "  value="Delete"></td>';
        newRow.append(cols);
        $("table.order-list").append(newRow);
		
  		$('.editor2').summernote();
        counter++;
});

$("table.order-list").on("click", ".ibtnDel", function (event) {
      $(this).closest("tr").remove();       
        counter -= 1
    });
});
</script>

<?= $this->endSection() ?>