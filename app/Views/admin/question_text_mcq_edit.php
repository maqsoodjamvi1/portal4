<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
{
		$header = 'Add  Quiz Question';
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
<!-- Content Header (Page header) -->
<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>
               Quiz Question
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Quiz Question</li>
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
          <li class="nav-item"><a class="nav-link" href="<?php echo '#/question_text_mcqs&topic_id='.$_GET['topic_id'];?>">Quiz Question</a></li>
          <?php if($id == ''){ ?>
          <li class="nav-item"><a class="nav-link active" href="<?php echo '#/question_text_mcqs?m=add&topic_id='.$_GET['topic_id'];?>"><?php echo $header;?></a>
          </li>
          <?php }else{ ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo '#/question_text_mcqs?m=edit&topic_id='.$_GET['topic_id'];?>"><?php echo $header;?></a>
          </li>
          <?php } ?>
        </ul>
   <div class="card-body">
    <?php
		echo form_open_multipart('c=question_text_mcqs&m=save' ,'role="form" id="user-edit-form"');
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
       <div class="col-lg-3">
        <div class="form-group">
              <label for="class">Template Type</label>
              <select class="form-control" name="template_id" id="template_id">
                <option value="">Select Template</option>
                 <?php if(isset($templateinfo)){
                    foreach ($templateinfo as  $templatevalue) { ?>
                <option <?php if($templatevalue->temp_id == $topic_id) { ?> selected <?php } ?> value="<?php echo $templatevalue->temp_id; ?>"><?php echo $templatevalue->name; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
             </div>
       </div>   
		  </div>
		<div class="col-lg-12">
      <table id="customFields" class=" table order-list">
        <thead>
            <tr>
             <td><div style="text-align: center;font-weight: bold;">Questions</div></td>
             <td><div style="text-align: center;font-weight: bold;">Explanation</div></td>
            </tr>
        </thead>
        <tbody>
  <?php
    $i = 0;        
    if($info){     
      foreach ($info as $key => $value) {
      $this->db->where('content_id', $value->content_id);
      $questionOptions = $this->db->get('question_options')->result();
    ?>
    <tr>
          <td style="" colspan="2">
            <input type="hidden" name="optionscount[]" value="1" />
            <input type="hidden" name="id<?php echo $i; ?>" value="<?php echo $value->content_id; ?>">
            <select class="form-control" id="question_type<?php echo $i; ?>" name="question_type<?php echo $i; ?>">
            <option value="">Question Type</option>
            <option <?php if($value->question_type == 'text'){ ?> selected <?php } ?> value="text">Text</option>
            <option <?php if($value->question_type == 'image'){ ?> selected <?php } ?> value="image">Image</option>
            <option <?php if($value->question_type == 'video'){ ?> selected <?php } ?> value="video">Video</option>
          </select>
          <div id="questionArea<?php echo $i; ?>">
            <?php if($value->question_type == 'text'){ ?>
            <textarea rows="3" class="form-control editor" name="question_text0" placeholder="Question" id="question_text<?php echo $i; ?>" style="margin-bottom: 4px;"><?php echo $value->question; ?></textarea>
            <?php } ?>
            <?php if($value->question_type == 'video'){ ?>
              <textarea rows="3" class="form-control editor" name="question_text<?php echo $i; ?>" placeholder="Question" id="question_text<?php echo $i; ?>" style="margin-bottom: 4px;"><?php echo $value->question; ?></textarea><input type="url" rows="3" class="form-control editor" name="video_url<?php echo $i; ?>"  placeholder="Video URL" value="<?php echo $value->video_url; ?>" id="video_url<?php echo $i; ?>" style="margin-bottom: 4px;">
            <?php } ?>
            <?php if($value->question_type == 'image'){ ?>
            <input type="file" rows="3" class="form-control editor" name="question_image<?php echo $i; ?>" placeholder="Question" id="question_image<?php echo $i; ?>" style="margin-bottom: 4px;">
            <input type="hidden" rows="3" class="form-control editor" name="question_image<?php echo $i; ?>" value="<?php echo $value->question_image; ?>" placeholder="Question" id="question_image<?php echo $i; ?>" style="margin-bottom: 4px;">
            <?php if($value->question_image){ ?>
            <img style="width: 90px;"src="worksheets/<?php echo $value->question_image; ?>">
            <?php } ?>
            <?php } ?>
            </div>
             </td>     
             <td colspan="2" style="border-left: 0 none !important;border-right: 0 none !important;"> <textarea class="form-control editor" name="hint_text0<?php echo $i; ?>" placeholder="Explanation" rows="3" id="hint_text<?php echo $i; ?>"><?php echo $value->explanation; ?></textarea>
             </td>
             <td style="border-left: 0 none !important;"><a class="deleteRow"></a></td>
            </tr></tr>
            <?php 
            $j=0;
            foreach ($questionOptions as $key => $optionvalue) {
              echo '<td><div class="col-sm-12"><input type="text" name="option_text0'.$j.'" id="option_text'.$i.''.$j.'" class="form-control" value="'.$optionvalue->option_text.'" placeholder="Option" style="margin-bottom: 5px;" /></div></td>';
               $j++;
            }  
            ?>  
            </tr>
            <script>
            $(document).ready(function(){
            $("#question_type<?php echo $i; ?>").change(function(){
              var inputValue = $(this).val();
              var rowcount = <?php echo $i; ?>;
              var content_id = <?php echo $value->content_id; ?>;
              $.ajax({
                url: "admin.php?c=question_text_mcqs&m=loadQuestion",
                type: "POST",
                data:{questionType:inputValue,rowcount:rowcount,content_id:content_id},
                success:function(res){
                  $("#questionArea<?php echo $i; ?>").html(res);
                }
              });  
            });
          });
        </script>
        <?php
           $i++; 
           }
        } ?>
     </tbody>
        <tfoot>
            <tr>
            <td colspan="5" style="text-align: center;">
               <!--  <input type="button" class="btn btn-lg btn-block btn-primary"  id="addrow" value="Add Question" /> -->
                <a style="display:block;background-color: #3c8dbc !important;width: 100%; text-align: center;color: #fff;padding: 7px 15px;margin:0 auto;" href="javascript:void(0);" class="addCF">Add Question</a>
             </td>
            </tr>
            <tr>
            </tr>
        </tfoot>
    </table>
<script type="text/javascript">
  $(document).ready(function(){
  var counter = <?php echo $i; ?>;
  $(".addCF").click(function(){
    var cols = "";
    var cols2 = "";
    var optionNums = "";
    
    cols += '<td colspan="2" style="border-right: 0 none !important;"><input type="hidden" name="optionscount[]" value="1" /> <select class="form-control" id="question_type'+ counter +'" name="question_type'+ counter +'"><option value="">Question Type</option><option value="text">Text</option><option value="image">Image</option><option value="video">Video</option></select><div id="questionArea'+ counter +'">';
    cols += '<textarea style="display:none" class="form-control" name="question_text'+ counter +'" placeholder="Question" id="question_text'+ counter +'" style="margin-bottom: 4px;"></textarea>';

    cols += '<input type="file" style="display:none" class="form-control" name="question_image'+ counter +'" placeholder="Image" id="question_image'+ counter +'" style="margin-bottom: 4px;">';
   
    cols += '<input type="url" style="display:none" class="form-control" name="video_url'+ counter +'" placeholder="Video URL" id="video_url'+ counter +'" style="margin-bottom: 4px;">';
   
    cols += '</div></td><td colspan="2" style="border-right: 0 none !important;border-left: 0 none !important;"><textarea class="form-control editor2" name="explanation_text'+ counter +'" placeholder="Explanation" id="explanation_text'+ counter +'"></textarea></td>';
           
        var template_id = $('#template_id').val();   
        if(template_id == 1 || template_id ==2){ 
          optionNums = 8;
        }

        if(template_id == 3 || template_id ==4){ 
          optionNums = 7;
        }

        if(template_id == 5 || template_id ==6){ 
          optionNums = 6;
        }

        if(template_id == 7 || template_id ==8 || template_id ==9){ 
          optionNums = 5;
        }

        if(template_id == 10 || template_id ==11){ 
          optionNums = 4;
        }

        if(template_id == 12 || template_id ==13){ 
          optionNums = 3;
        }

        cols2 += '<td colspan="4"><div class="row">';
        for(var k=1; k<= optionNums; k++){
          cols2 += '<div class="col-sm-3"><input type="text" name="option_text0'+ k +'" id="option_text0'+ k +'" class="form-control" placeholder="Correct Option" style="margin-bottom: 5px;" /></div>';
      }
      
    cols2 += '</div></td>';

    $("#customFields").append('<tr valign="">'+cols+'<td> <a style="background-color:#dc3545;color: #fff;padding: 7px 10px;" href="javascript:void(0);" class="remCF">Remove</a><script>$("#customFields").on("change","#question_type'+counter+'",function(){ var inputValue = $(this).val(); $.ajax({ url: "admin.php?c=question_text_mcqs&m=loadQuestion",type: "POST",data:{questionType:inputValue,rowcount:'+counter+'},success:function(res){ $("#questionArea'+counter+'").html(res);} });  });<\/script></td></tr><tr>'+cols2+'</tr>');
    
      counter++;
  
  });
  
    $("#customFields").on('click','.remCF',function(){
        //$(this).parent().parent().remove();
        var closestRow = $(this).closest("tr");
        closestRow.add(closestRow.next()).remove();      
        counter--;
    });
});
</script>
		</div>
		<div class="row">
		 <div class="col-lg-12">
		 <div class="col-lg-3">
         <div class="form-group">
           <button type="submit" class="btn btn-primary">Save</button>
           <button type="reset" class="btn btn-default">Reset</button>
           <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
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
</script>
<script type="text/javascript">
$(function(){

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
</script>

<?= $this->endSection() ?>