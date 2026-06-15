<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php $id=''; ?>
<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
    <?= view('components/page_header', [
    'title' => 'Scheme Of Studies View',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Scheme Of Studies View', 'active' => true],
    ],
]) ?>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-lg-12">
          <div class="card card-primary card-outline card-tabs">
            <div class="card-header p-0 pt-1 border-bottom-0">
			<ul class="nav nav-tabs">
				<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/scheme_of_studies/add') ?>">Add Scheme Of Studies</a></li>
				<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/scheme_of_studies_view') ?>">Scheme Of Studies View</a></li>
			</ul>
			<div class="card-body">
			<div class="col-lg-12">
				<div class="col-lg-12">
					<div class="col-lg-3">
						 <label for="class">Terms</label>
					<select class="form-control" name="term_id" id="term_id" class="form-control">
						 <option value="">Select Term</option>
						
					</select>
					</div>
					<!-- <div class="col-lg-7">
					    <div class="form-group">
			              <label for="class">Term Weeks</label>
			              <div id="term_weeks">
			               
			              </div>
			           </div>
					</div> -->
					<div class="col-lg-2">	<button style="height: 24px;line-height: 10px;margin-top: 19px;" type="submit" onclick="submitterms();" class="btn btn-primary">View</button></div>
				</div>
			 <script type="text/javascript">
			 	function submitterms(){
					var term_id = $('#term_id').val();
					 $.ajax({
				            url: 'admin.php?c=scheme_of_studies_view&m=data',
				            type: "POST",
				            data:{term_id:term_id },
				            success:function(res){
				 			   $("#termweekdates").html(res);
				 			   }
				         });	
				}

			 </script>

			 <div id="termweekdates"></div>

		  </div>
		    <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
      </div>

    </section>
    <!-- /.content -->
<style type="text/css">
   	tr th:first-child{
   	max-width:20px;padding: 0px; margin:0px; 	
   	}
   	tr th:first-child input[type="text"]{
   		width: 10px !important
   	}
   </style>  	
 <script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript">
$(function(){
	//$(".select2").select2({closeOnSelect:false});
	$("#term_id").change(function(){
        var term_id = $('#term_id').val();
	     $.ajax({
            url: 'admin.php?c=ajax&m=selectTermWeeksByTerm',
            type: "POST",
            data:{term_id:term_id },
            success:function(res){
 			   $("#term_weeks").html(res);
 			 }
         });
    });	
	$('#user-edit-form').validate({
		
	});
	$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			return $('#user-edit-form').valid();
		},
		success:function(responseText, statusText, xhr, form){
			var json = $.parseJSON(responseText);
			if(json.success){
				toastr.success(json.msg);
				<?php
				if($id == ''){
					?>
					location.href = '#/class_subjects';
					<?php
				}else{
					?>
					location.href = '#/class_subjects?m=edit&id=<?php echo $id;?>&after=edit';
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