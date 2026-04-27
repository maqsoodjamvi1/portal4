<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){		
			$header = 'Edit Datesheet Report';
			$id = 0;
			$class_id = '';
			$tid = '';
			$subject_id = '';
			
		}else{
			$header = 'Add Datesheet Report';
			$id = 0;
			$class_id = '';
			$tid = '';
			$subject_id = '';
		}
?>
<!-- Content Header (Page header) -->  
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>
           Datesheet Report
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active"> Datesheet Report</li>
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
			<?php if($id == ''){ ?>
				<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/datesheet_report/add') ?>"><?php echo $header;?></a></li>
			<?php }else{ ?>
				<li class="nav-item"><a class="nav-link" href="<?php echo '#/timetable?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
			<?php } ?>
			</ul>
	<div class="card-body"> 		
	<div class="tab-content">
			<div class="form-group">
				<select class="form-control" name="exam_id" id="exam_id">
					<option value="">Select Exam</option>
				<?php if(isset($examInfo)){
					foreach ($examInfo as  $exam) {
				 ?>
				<option value="<?php echo $exam->eid; ?>"><?php echo $exam->exam_name; ?></option>
				<?php } ?>
				<?php } ?>	
				</select>
			</div>
			<div class="col-md-12 bg">
		        <div id="loader-1" class="overlay text-center" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
		      </div>
			<div id="datesheetarea" class="datesheetarea">			
			</div>
		</div>
		</div>
        </div>
      </div>
      </div>
      </div>
    </section>
    <!-- /.content -->
<script type="text/javascript">
$(function(){
	//$(".select2").select2({closeOnSelect:false});
	$("#exam_id").change(function(){
        var exam_id = $('#exam_id').val();
        $("#loader-1").css("display", "block");
	     $.ajax({
            url:'<?php echo base_url('admin/datesheet-report/data'); ?>', 
            type: "POST",
            data:{exam_id:exam_id },
            success:function(res){
            	//console.log(res);
 			   $("#datesheetarea").html(res);
 			   $("#loader-1").css("display", "none");
			 }
         });
    });
	
	$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			$('#submitBtn').html("Saving");
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
					location.href = '#/timetable?m=add';
					<?php
				}else{
					?>
					location.href = '#/timetable?m=add';
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

 $(document).on('click', '#dsPrintBtn', function (e) {
    e.preventDefault();
    window.print();
  });
</script>


<style>
/* sticky table + nicer badges */
.table-sticky-wrap { overflow: auto; }
.ds-table { border-collapse: separate; border-spacing: 0; }
.ds-table th, .ds-table td { background: #fff; vertical-align: top; }

.ds-table thead th { position: sticky; top: 0; z-index: 5; }
.sticky-col { position: sticky; left: 0; z-index: 6; background: #fff; }
.th-sec, .td-sec { min-width: 220px; max-width: 340px; }

.ds-badge { font-weight: 600; }
.ds-badge + .small { margin-left: .25rem; }
.ds-toolbar { background: #f8f9fa; border: 1px solid #e9ecef; border-bottom: 0; }

/* Print: hide controls, show header */
@media print {
  .no-print, .main-sidebar, .main-header, .main-footer { display: none !important; }
  .ds-print-header { display: block !important; }
  .table-sticky-wrap { overflow: visible !important; }
  .sticky-col, .ds-table thead th { position: static !important; }
}
</style>

<?= $this->endSection() ?>