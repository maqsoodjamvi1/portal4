<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	{
	$header = 'Add Tests';
    $id = '';
    $date1 = '';
    $class_id = '';
    $subject_id = '';
    $cat_id = '';
    $detail = '';
    $topic_id = '';
	}
?>
<?= view('components/page_header', [
    'title' => 'Tests',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Tests', 'active' => true],
    ],
]) ?>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs">
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/tests') ?>">Tests</a></li>
          <?php if($id == ''){ ?>
          <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/tests/add') ?>"><?php echo $header;?></a></li>
          <?php }else{ ?>
          <li class="nav-item"><a class="nav-item" href="<?php echo base_url('admin/tests/edit?id=') . $id;?>"><?php echo $header;?></a></li>
          <?php } ?>
        </ul>
    <div class="card-body">
    <?php
        echo form_open_multipart( base_url('admin/tests/save'), 'role="form" id="user-edit-form"');
		    echo form_hidden('id', $id);
        echo form_hidden('session_id', $session_id);
		?>
		<div class="row">
      <div class="col-lg-2">
            <div class="form-group">
              <label for="class">Test Series</label>
              <select class="form-control" name="t_series_id" id="t_series_id">
                <?php if(isset($test_series)){
                    foreach ($test_series as  $seriesvalue) { ?>
                <option  value="<?php echo $seriesvalue->t_series_id; ?>"><?php echo $seriesvalue->series_name; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="col-lg-2">
            <div class="form-group">
              <label for="class">Classes</label>
              <select class="form-control" name="cls_sec_id" id="cls_sec_id">
                <option value=">">Select Class</option>
                <?php if(isset($sectionsclassinfo)){
                    foreach ($sectionsclassinfo as  $classvalue) { ?>
                <option value="<?php echo $classvalue['section_id']; ?>"><?php echo $classvalue['sectionclassname']; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </div>
          </div>
	        <div class="col-lg-2">
            <div class="form-group">
              <label>Subjects</label>
              <select class="form-control" name="subject_id" id="subject_id">
                
              </select>
            </div>
          </div>
      
      </div>
		  <div class="row">
		  <div class="col-lg-12">
		  <div id="testsList">
		  
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
         <?php echo form_close();?> </div>
      </div>
    </div>
  </div>
</section>
<!-- /.content -->
><script type="text/javascript">
$(document).ready(function () {
   $("#cls_sec_id").change(function(){
        var cls_sec_id = $("#cls_sec_id").val();
         $.ajax({
            url: "/admin/tests/selectsubjectbysection",
            type: "POST",
            data:{cls_sec_id:cls_sec_id },
            success:function(res){
         $("#subject_id").html(res);
       }
         });
    });

   $("#subject_id").change(function(){
        var t_series_id = $("#t_series_id").val();
        var cls_sec_id = $("#cls_sec_id").val();
        var subject_id = $("#subject_id").val();
           $.ajax({
                url: "/admin/tests/selecttestslist",
                type: "POST",
                data:{t_series_id:t_series_id,cls_sec_id:cls_sec_id,subject_id:subject_id },
                success:function(res){
                 $("#testsList").html(res);
                }
             });
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
			var json = $.parseJSON(responseText);
			if(json.success){
				toastr.success(json.msg);
				<?php
				if($id == ''){
					?>
					location.href = '/admin/tests';
					<?php
				}else{
					?>
					location.href = '/admin/tests/edit?id=<?php echo $id;?>&after=edit';
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