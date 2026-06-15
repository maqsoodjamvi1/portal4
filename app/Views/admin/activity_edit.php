<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
if(isset($info)){
  $header = 'Edit Activity';
  $id = $info->did;
  $class_id = $info->class_id;
  $subject_id = $info->subject_id;
  $detail = $info->detail;
  $date1 = $info->date1;
  $type1 = $info->type1;
  $path1 = $info->path1;
}else{
  $header = 'Add Activity';
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
    'title' => 'Activity',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Activity', 'active' => true],
    ],
]) ?>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-12">
      <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
          <li><a href="<?= base_url('admin/activity') ?>">Activity</a></li>
          <?php if($id == ''){ ?>
          <li class="active"><a href="<?= base_url('admin/activity/add') ?>"><?php echo $header;?></a></li>
          <?php }else{ ?>
          <li class="active"><a href="<?php echo '#/activity?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
          <?php } ?>
        </ul>
        <div class="tab-content">
    <?php
			echo form_open_multipart('c=activity&m=save', 'role="form" id="activity-edit-form"');
			echo form_hidden('id', $id);
		?>
		<div class="row">
		<div class="col-lg-12">
          <div class="col-lg-2">
            <div class="form-group">
              <label for="class">Class</label>
              <select class="form-control" name="class_id" id="class_id">
              	<?php if(!isset($topic_skills_info)){ ?>
                    <option value="">Select Class</option>
			          <?php } ?>
                <?php if(isset($classesinfo)){
						    foreach ($classesinfo as  $classvalue) { ?>
                <option <?php if($classvalue->class_id == $class_id) { ?> selected <?php } ?> value="<?php echo $classvalue->class_id; ?>"><?php echo $classvalue->class_name; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="col-lg-2">
            <div class="form-group">
              <label for="class">Subjects</label>
              <select class="form-control" name="subject_id" id="subject_id">
                <?php if(isset($subjectinfo)){
                		foreach ($subjectinfo as  $subjectvalue) { ?>
                <option <?php if($subjectvalue->sid == $subject_id) { ?> selected <?php } ?> value="<?php echo $subjectvalue->sid; ?>"><?php echo $subjectvalue->subject_name; ?></option>
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
                <option <?php if($sub_category_value->sub_cat_id == $cat_id) { ?> selected <?php } ?> value="<?php echo $sub_category_value->sub_cat_id; ?>"><?php echo $sub_category_value->cat_name; ?></option>
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
		   <div class="col-lg-2">
		    <div class="form-group">
              <label for="class">Topic Skills</label>
              <select class="form-control" name="topic_skill_id" id="topic_skill_id">
              	<?php if(isset($topic_skills_info)){
	                foreach ($topic_skills_info as  $topicskillsvalue) { ?>
	                <option <?php if($topicskillsvalue->topic_skills_id == $topic_id) { ?> selected <?php } ?> value="<?php echo $topicskillsvalue->topic_skills_id; ?>"><?php echo $topicskillsvalue->topic_skill; ?></option>
	                <?php } ?>
                <?php } ?>
              </select>
               <input type="hidden"  name="doc_type_id" value="3" id="doc_type_id">
             </div>
		   </div>	   
		  </div>
		  </div>
		  <div class="row">
		  <div class="col-lg-12">
		  <table id="myTable" class=" table order-list">
		    <thead>
		        <tr>
			       <td><div style="text-align: center;font-weight: bold;">Activity Reference</div></td>
		        </tr>
		    </thead>
		    <tbody>
           <tr>
		      <td class="col-sm-10" style="border: 4px solid blue;">	
		      	<input type="hidden" name="questioncount[]" value="1" />
			  <input type="file" name="document_url0" class="form-control">
        <small>Select Activity</small>
		      </td>
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
		 <div class="row">
		 <div class="col-lg-12">
		 <div class="col-lg-3">
         <div class="form-group">
           <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
           <button type="reset" class="btn btn-secondary">Reset</button>
           <button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
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
$(document).ready(function () {
 $("#submitBtn").one('click', function (event) {  
   event.preventDefault();
   $(this).prop('disabled', true);
 });
}); 
$(document).ready(function () {
    var counter = 1;
    $("#addrow").on("click", function () {
        var newRow = $("<tr>");
        var cols = "";
        cols += '<td style="border: 4px solid blue; border-end: 0 none !important;"><input type="hidden" name="questioncount[]" value="1" />  <input type="file" name="document_url'+ counter +'" class="form-control"><small>Select Activity</small></td>';
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

function selecttermWeek(){
	var term_id = $('#term_id').val();
  var class_id = $('#class_id').val();
	var subject_id = $('#subject_id').val();
	var term_weeks = $('#term_weeks').val();
	 $.ajax({
            url: 'admin.php?c=ajax&m=termweekdate',
            type: "POST",
            data:{term_weeks:term_weeks },
            success:function(res){
 			         $("#termweekdates").html(res);
			      }
         });
  }
</script>
<script type="text/javascript">
$(function(){
	$('#activity-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
		},
		success:function(responseText, statusText, xhr, form){
      var json = $.parseJSON(responseText);			
			if(json.success){
				toastr.success(json.msg);
				<?php
				if($id == ''){
					?>
					location.href = '#/activity';
					<?php
				}else{
					?>
					location.href = '#/activity?m=edit&id=<?php echo $id;?>&after=edit';
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