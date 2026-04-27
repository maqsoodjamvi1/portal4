<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	{
	$header = 'Add WorkSheet';
    $id = '';
    $date1 = '';
    $class_id = '';
    $subject_id = '';
    $cat_id = '';
    $detail = '';
    $topic_id = '';
	}
?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>
           Worksheet
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Worksheet</li>
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
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/worksheet') ?>">WorkSheet</a></li>
          <?php if($id == ''){ ?>
          <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/worksheet/add') ?>"><?php echo $header;?></a></li>
          <?php }else{ ?>
          <li class="nav-item"><a class="nav-item" href="<?php echo '#/worksheet?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
          <?php } ?>
        </ul>
    <div class="card-body">
    <?php
        echo form_open_multipart('c=worksheet&m=save', 'role="form" id="user-edit-form"');
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
       <div class="col-lg-4">
            <!-- <div class="form-group">
              <label for="class">Parent</label>
              <select  class="form-control" name="parent_sheet_id" id="parent_sheet_id">
              	<option value="0">Select Child Worksheets</option>
	               <?php 
				    if(!empty($worksheetinfo)){
				        $i = 0; 
				    foreach ($worksheetinfo as $key => $value) { 
				  ?>
                <option value="<?php echo $value->content_id; ?>"><?php echo $value->doc_title; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </div> -->
       </div>
		   <input type="hidden"  name="template_id" value="8" id="template_id">   
		  </div>
		  <div class="row">
		  <div class="col-lg-12">
		  	<div id="workseetsList">
		  <table id="myTable" class=" table order-list">
		    <thead>
		    <tr>
			 <td style="width: 50%;" ><div style="text-align: center;font-weight: bold;">Description</div></td>
             <td><div style="text-align: center;font-weight: bold;">WorkSheet Reference</div></td>
             <td></td>
		    </tr>
		    </thead>
		    <tbody>
  <?php 
    if(!empty($worksheetinfo)){
        $i = 0; 
    foreach ($worksheetinfo as $key => $value) { 
  ?>
      <tr>
		  <td>	
		     <input type="hidden" name="questioncount[]" value="1" />
             <input type="hidden" name="id<?php echo $i; ?>" 
             value="<?php echo $value->content_id; ?>">
             <label>Detail</label>
             <input type="text" id="slugme<?php echo $i; ?>" class="form-control" placeholder="Title" name="title<?php echo $i; ?>" value="<?php echo $value->doc_title; ?>">
             <input type="text" placeholder="Slug" value="<?php echo $value->doc_slug; ?>" name="doc_slug<?php echo $i; ?>" class="form-control slug<?php echo $i; ?> doc_slug">
             <textarea rows="3" placeholder="Description"  name="text<?php echo $i; ?>" class="form-control"><?php echo $value->doc_description; ?></textarea>
             <label>Meta Data</label>
             <input type="text" id="meta_title<?php echo $i; ?>" class="form-control" placeholder="Meta Title" name="meta_title<?php echo $i; ?>" value="<?php echo $value->meta_title; ?>">
             <textarea rows="3" placeholder="Meta Keywords" name="meta_keywords<?php echo $i; ?>" class="form-control"><?php echo $value->meta_keywords; ?></textarea>
             <textarea rows="3" placeholder="Meta Description" name="meta_description<?php echo $i; ?>" class="form-control"><?php echo $value->meta_description; ?></textarea>
             <label>Indexable</label>
             <select class="form-control" name="no_index<?php echo $i; ?>">
             	<option <?php if($value->no_index == 1){ ?> selected <?php } ?> value="1">No</option>
             	<option <?php if($value->no_index == 0){ ?> selected <?php } ?> value="0">Yes</option>
             </select>
          </td>
          <td>
          	<input type="file" name="document_url<?php echo $i; ?>" class="form-control">
			 <input type="hidden" name="document_url<?php echo $i; ?>" size="20" value="<?php echo $value->doc_url; ?>" />

            <small>Select Worksheet</small>
            <br>
            <?php echo $value->doc_url; ?>
		  </td>
		  <td> 
			<input type="file" name="thumbnail<?php echo $i; ?>" class="form-control">
            <small>Select Thumb</small>
            <input type="hidden" name="thumbnail<?php echo $i; ?>" size="20" value="<?php echo $value->doc_thumbnail; ?>" />
            <br>
            <?php if($value->doc_thumbnail){ ?>
            <img style="width:105%;" src="worksheets/<?php echo $value->doc_thumbnail; ?>">
          <?php } ?>
		  </td>
      <td>
        <a href="javascript:;" onclick="del_confirm('notice', 'Are you sure delete this record', '<?php echo site_url('c=worksheet&m=delete&id='.$value->content_id);?>','users-datatable');" title=" delete" class="btn btn-default btn-xs"><i class="fa fa-trash icon-trash"></i></a>
      </td>
		  <script type="text/javascript">
			 $(function(){
                $('#slugme<?php echo $i; ?>').slugIt({
                    output: '.slug<?php echo $i; ?>'
                });
            });
		  </script>
      </tr>	
        <?php $i++ ?>  
       <?php } ?>
       <?php }else{ 
            $i = 1; 
            ?> 
           <tr>
          <td>  
             <input type="hidden" name="questioncount[]" value="1" />
             <input type="hidden" name="id0" value="0">
             <label>Detail</label>
             <input type="text" id="slugme0" class="form-control" placeholder="Title" name="title0" value="">
              <input type="text" value="" placeholder="Slug" name="doc_slug0" class="form-control slug0 doc_slug">
             <textarea rows="3" placeholder="Description" name="text0" class="form-control"></textarea>
             <label>Meta Data</label>
             <input type="text" id="meta_title0" class="form-control" placeholder="Meta Title" name="meta_title0" value="">
             <textarea rows="3" placeholder="Meta Keywords" name="meta_keywords0" class="form-control"></textarea>
             <textarea rows="3" placeholder="Meta Description" name="meta_description0" class="form-control"></textarea>
             <label>Indexable</label>
             <select class="form-control" name="no_index0">
             	<option value="1">No</option>
             	<option value="0">Yes</option>
             </select>
          </td>
          <td>
             <input type="file" name="document_url0" class="form-control">
             <small>Select Worksheet</small>
          </td>
           <td> 
			<input type="file" name="thumbnail0" class="form-control">
            <small>Select Thumb</small>
          </td>
          <td></td>
          <script type="text/javascript">
          $(function(){
                $('#slugme0').slugIt({
                    output: '.slug0'
                });
            });
          </script>
          </tr> 

        <?php } ?>

		  </tbody>
		  <tfoot>
        <tr>
        <td colspan="5" style="text-align: left;">
              <input type="button" class="btn btn-lg btn-block btn-primary"  id="addrow" value="Add Worksheet" />
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
           <button type="reset" class="btn btn-default">Reset</button>
           <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
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
$(document).ready(function () {
 $("#parent_sheet_id").change(function(){
    var parent_sheet_id = $('#parent_sheet_id').val();
	  $.ajax({
        url: 'admin.php?c=worksheet&m=selectWorksheetsByParent',
        type: "POST",
        data:{parent_sheet_id:parent_sheet_id },
        success:function(res){
          $("#workseetsList").html(res);
        }
    });
});
});
</script>
<script type="text/javascript">
$(document).ready(function () {
    var counter = '<?php echo $i ?>';
    $("#addrow").on("click", function () {
        var newRow = $("<tr>");
        var cols = "";
        cols += '<td><input type="hidden" name="id'+counter+'" value="0"><label> Detail</label><input type="text" class="form-control" id="slugme'+ counter +'" placeholder="Title" name="title'+ counter +'" value=""><input type="text" value="" name="doc_slug'+ counter +'" placeholder="Slug" class="form-control slug'+ counter +' doc_slug"><textarea placeholder="Description" rows="3" name="text'+ counter +'" class="form-control"></textarea><label>Meta Data</label><input type="text" id="meta_title'+ counter +'" class="form-control" placeholder="Meta Title" name="meta_title'+ counter +'" value=""><textarea rows="3" placeholder="Meta Keywords" name="meta_keywords'+ counter +'" class="form-control"></textarea><textarea rows="3" placeholder="Meta Description" name="meta_description'+ counter +'" class="form-control"></textarea><label>Indexable</label><select class="form-control" name="no_index'+ counter +'"><option value="1">No</option><option value="0">Yes</option></select></td><td><input type="hidden" name="questioncount[]" value="1" /><input type="file" name="document_url'+ counter +'" class="form-control"><small>Select Worksheet</small></td>';
        cols += '<td><input type="file" name="thumbnail'+ counter +'" class="form-control"><small>Select Thumbnail </small></td>';
        cols += '<td><input type="button" class="ibtnDel btn btn-md btn-danger "  value="Delete"></td>';
        newRow.append(cols);
        $("table.order-list").append(newRow);

        $('#slugme'+ counter).slugIt({
            output: '.slug'+ counter
        });
		
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
  $(".select2").select2({closeOnSelect:false});
	$('#user-edit-form').validate({  
  });

$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
		},
		success:function(responseText, statusText, xhr, form){
			var json = $.parseJSON(JSON.stringify(responseText));
			if(json.success){
				toastr.success(json.msg);
				<?php
				if($id == ''){
					?>
					location.href = '#/worksheet';
					<?php
				}else{
					?>
					location.href = '#/worksheet?m=edit&id=<?php echo $id;?>&after=edit';
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