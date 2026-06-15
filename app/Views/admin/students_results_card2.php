<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<!-- Content Header (Page header) -->
<style>
	.list-group-item{
	  	width: 33% !important;
	    float: left !important;
	    padding: 1px 10px !important;
	    border-end: 0 none;
	    border-start: 0 none;
	}
	table{
	background-color: transparent;
    border: 2px solid #000;
    margin-top: 2px;
    float: left;
	}
	.table-bordered>thead>tr>th, .table-bordered>tbody>tr>th, .table-bordered>tfoot>tr>th, .table-bordered>thead>tr>td, .table-bordered>tbody>tr>td, .table-bordered>tfoot>tr>td{
		border:1px solid #333;
	}
	.heading2{
   			border:2px solid #000;float:left;width:100%;background:#800000;text-align:center;font-weight:bold;padding: 5px;font-size: 18px;color: #fff;line-height: 20px;
   		}
.heading{
   		border:2px solid #000;float:left;width:100%;text-align:center;font-weight:bold;padding: 5px; background:#800000;font-size: 18px;color: #fff;line-height: 20px;
   		}
	@media print {
		body {-webkit-print-color-adjust: exact;}
   		.heading{
   			border:2px solid #000;float:left;width:100%;text-align:center;font-weight:bold;padding: 5px; background:maroon;font-size: 18px;color: #fff;line-height: 20px;background-color: #800000 !important;
        -webkit-print-color-adjust: exact;
   		}
   		.heading2{
   			border:2px solid #000;float:left;width:100%;background:maroon;text-align:center;font-weight:bold;padding: 5px;font-size: 18px;color: #fff;line-height: 20px;background-color: #800000 !important;
        -webkit-print-color-adjust: exact;
   		}

   	.no-print,.nav-tabs,.main-footer,.no-print *
      {
          display: none !important;
      }
  	}
</style>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>
           Students Results
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Students Results</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>
<!-- Main content -->
<section class="content"> 
<div class="row">
  <div class="col-lg-12">
     <div class="card card-primary card-outline card-tabs" style="background: #fff !important;">
       	<div class="card-header p-0 pt-1 border-bottom-0">
     	<ul class="nav nav-tabs .no-print">
 			<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_results2/add') ?>">Add Results</a></li>
			<!-- <li  class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_results_compilation2/add') ?>"> Compile Results </a></li> -->
	    	<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/students_results_card2') ?>">View Results Cards</a></li>	
 		</ul>
		<div class="card-body">
		<div class="no-print">
		<div class="col-lg-12">
		<label for="class"><strong>Academic Result</strong></label><br>
		<ul class="list-group list-group-horizontal">
			<li class="list-group-item">
			<div class="icheck-primary d-inline">	
			<input type="checkbox" class="academic_results" id="marks" name="academic_result[]" value="marks"><label for="marks"> Marks</label>
			</div>
			</li>
			<li class="list-group-item">
			<div class="icheck-primary d-inline">	
			<input type="checkbox" class="academic_results" id="percentage" name="academic_result[]" value="percentage"><label for="percentage"> Percentage</label>
			</div>
			</li>
			<li class="list-group-item">
			<div class="icheck-primary d-inline">	
			<input type="checkbox" class="academic_results" id="grade" name="academic_result[]" value="grade"><label for="grade"> Grade</label>
			</div>
			</li>
			<li class="list-group-item">
			<div class="icheck-primary d-inline">	
			<input type="checkbox" class="academic_results" id="position" name="academic_result[]" value="position"> <label for="position">Position</label>
			</div>
			</li>
			<li class="list-group-item"><
			<div class="icheck-primary d-inline">	
			<input type="number" style="opacity: 1; margin-left: 10px;width: 80%;" class="" id="position_num" name="position_num" value="">
			</div>
			</li>
			<li class="list-group-item">
			<div class="icheck-primary d-inline">	
			<input type="checkbox" class="academic_results" id="subject_remarks" name="academic_result[]" value="subject_remarks"><label for="subject_remarks">  Remarks</label>
			</div>
			</li>
			<li class="list-group-item">
			<div class="icheck-primary d-inline">	
			<input type="checkbox" class="academic_results" id="total_remarks" name="academic_result[]" value="total_remarks"><label for="total_remarks"> Total Remarks</label>
			</div>
			</li> 
		</ul>
	</div>
	<div class="col-lg-12">
		<label for="class"><strong>None Academic Result</strong></label><br>
		<ul class="list-group list-group-horizontal">
			<<!-- li class="list-group-item">
			<div class="icheck-primary d-inline">	
				<input type="checkbox" class="non_academics" name="non_academic['study_complaints']" id="study_complaints" value="study_complaints"> <label for="study_complaints">Study Complaints</label>
			</div>
			</li> -->
			<!-- <li class="list-group-item">
				<div class="icheck-primary d-inline">
				<input type="checkbox"  class="non_academics" name="non_academic['discinpline_complaints']" id="discinpline_complaints" value="discinpline_complaints"> <label for="discinpline_complaints">Discipline Complaints</label>
				</div>
			</li>   -->
			<li class="list-group-item">
				<div class="icheck-primary d-inline">
				<input type="checkbox"  class="non_academics" name="non_academic['presents']" id="presents" value="presents"><label for="presents"> Present</label>
				</div>
			</li> 
			<li class="list-group-item">
				<div class="icheck-primary d-inline">
				<input type="checkbox"  class="non_academics" name="non_academic['absentees']" id="absentees" value="absentees"><label for="absentees"> Absentees</label>
				</div>
			</li>  
			<!-- <li class="list-group-item">
				<div class="icheck-primary d-inline">
				<input type="checkbox"  class="non_academics" name="non_academic['leaves']" id="leaves" value="leaves"><label for="leaves"> Leaves</label>
				</div>
			</li>   -->
			<!-- <li class="list-group-item">
				<div class="icheck-primary d-inline">
				<input type="checkbox"  class="non_academics" name="non_academic['late_comming']" id="late_comming" value="late_comming"> 
				<label for="late_comming">Late Comming</label>
				</div> 
			</li>  -->
			<!-- <li class="list-group-item">
				<div class="icheck-primary d-inline">
				<input type="checkbox"  class="non_academics" name="non_academic['early_left']" id="early_left" value="early_left"> <label for="early_left">Early Left</label>
				</div>
			</li> -->
		</ul> 			  
		</div>
		<div class="col-lg-12">
		<label for="class"><strong>Exams</strong></label><br>
		<ul class="list-group list-group-horizontal">
		<?php  foreach ($exams as $key => $exam) { ?>
			<li class="list-group-item">
				<div class="icheck-primary d-inline">
				<input type="checkbox" class="examids" id="eid<?php echo $exam->eid; ?>" name="exam_id" value="<?php echo $exam->eid; ?>"> <label for="eid<?php echo $exam->eid; ?>"><?php echo $exam->exam_name; ?></label>
				</div>
			</li>
		<?php } ?>
		</ul>
		</div>
		<div class="col-lg-12">
		<label for="class"><strong>Hide/Show</strong></label><br>
		<ul class="list-group list-group-horizontal">
			<li class="list-group-item">
				<div class="icheck-primary d-inline">
				<input type="checkbox" class="sign_lines" id="sign_lines" name="sign_lines" value="1"> <label for="sign_lines">Show Signature Line 1</label>
				</div>
			</li>
			<li class="list-group-item">
				<div class="icheck-primary d-inline">
				<input type="checkbox" class="sign_lines2" id="sign_lines2" name="sign_lines2" value="1"> <label for="sign_lines2">Show Signature Line 2</label>
				</div>
			</li>
			<li class="list-group-item">
				<div class="icheck-primary d-inline">
				<input type="checkbox" class="sign_lines3" id="sign_lines3" name="sign_lines3" value="1"> <label for="sign_lines3">Show Signature Line 3</label>
				</div>
			</li>
			<li class="list-group-item">
				<div class="icheck-primary d-inline">
				<input type="checkbox" class="hide_profile_pic" id="hide_profile_pic" name="hide_profile_pic" value="1"> <label for="hide_profile_pic">Hide Profile Pic</label>
				</div>
			</li>
			<li class="list-group-item">
				<div class="icheck-primary d-inline">
				<input type="checkbox" class="hide_phone_number" id="hide_phone_number" name="hide_phone_number" value="1"> <label for="hide_phone_number"> Hide Phone Number</label>
				</div>
			</li>
		</ul>
		</div>
		<div class="row">
		<div class="col-lg-6 form-group">
			<label for="class"><strong>Class</strong></label><br>
				<select class="form-control" name="cls_sec_id" id="cls_sec_id">
					<option value="">All Classes</option>
				<?php if(isset($sectionsclassinfo)){
					foreach ($sectionsclassinfo as  $sectionvalue) {
				 ?>
				<option value="<?php echo $sectionvalue['section_id']; ?>"><?php echo $sectionvalue['sectionclassname']; ?></option>
				<?php } ?>
				<?php } ?>	
				</select>
		</div>
		</div>
		<div class="col-lg-12">
		<label for="class"><strong>Footer Line 1</strong></label><br>
				<input type="text" class="form-control" id="remarks" name="remarks" value="">
		</div>
		<div class="col-lg-12">
		<label for="class"><strong>Footer Line 2</strong></label><br>
			<div class="row">
				<div class="col-lg-6">
					<input type="text" class="form-control" id="class_teacher_sign" name="class_teacher_sign" value="">
				</div>
				<div class="col-lg-6">
					<input type="text" class="form-control" id="principle_sign" name="principle_sign" value="">	
				</div>
			</div>
		</div>
		<div class="col-lg-12">
		<label for="class"><strong>Footer Line 3</strong></label><br>
				<input type="text" class="form-control" id="parent_sign" name="parent_sign" value="">
		</div>
		<label style="margin-left: 10px;margin-top: 12px;">Select Date Range For Attendance </label><br>
		<div class="row" style="margin: 0px;">
		<div class="col-lg-6">
		<label for="class"><strong>Start Date</strong></label><br>
				<input type="date" class="form-control" id="start_date" name="start_date" value="">
		</div>
		<div class="col-lg-6">
		<label for="class"><strong>End Date</strong></label><br>
				<input type="date" class="form-control" id="end_date" name="end_date" value="">
		</div>
		</div>

		<label style="margin-left: 10px;margin-top: 12px;">Display Previous Session Result </label><br>
		<div class="row" style="margin: 0px;">
		<div class="col-lg-6">
				<input type="checkbox" class="form-control" id="previous_session" name="previous_session" value="1">
		</div>
		</div>
		<div class="col-lg-12"><input style="line-height: 19px;margin: 10px 0px;" type="button" class="btn btn-primary float-end" value="View Result Card " name="View" id="ViewResutlt"></div>
	</div>
		<div id="loader-1" class="overlay col-md-12 text-center" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
	<div id="resultContainer"></div>
    </div>
    </div>
</div>
</div>
</div>
</section>
<!-- /.content -->
<script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript">
$(function(){
	$('#ViewResutlt').on('click', function() {	
	$("#loader-1").css("display", "block");	
	var academic_result = [];
	var examids = [];
	var non_academics = [];
	$(".academic_results:checked").each(function(i, e) {
	    academic_result.push($(this).val());
	});

	$(".examids:checked").each(function(i, e) {
	    examids.push($(this).val());
	});

	$(".non_academics:checked").each(function(i, e) {
	    non_academics.push($(this).val());
	});

	var cls_sec_id = $('#cls_sec_id').val();
	
	var position_num = $('#position_num').val();
	var remarks = $('#remarks').val();
	var class_teacher_sign = $('#class_teacher_sign').val();
	var principle_sign = $('#principle_sign').val();
	var parent_sign = $('#parent_sign').val();

	var sign_lines = $('#sign_lines:checked').val();
	var sign_lines2 = $('#sign_lines2:checked').val();
	var sign_lines3 = $('#sign_lines3:checked').val();
	var hide_profile_pic = $('#hide_profile_pic:checked').val();
	var hide_phone_number = $('#hide_phone_number:checked').val();
	var start_date = $('#start_date').val(); 
	var end_date = $('#end_date').val();
	var previous_session = $('#previous_session').val();

	$.ajax({
            url: 'admin.php?c=students_results_card2&m=data',
            type: "POST",
            data:{academic_result:academic_result,examids:examids,non_academics:non_academics,cls_sec_id:cls_sec_id,remarks:remarks,class_teacher_sign:class_teacher_sign,principle_sign:principle_sign,parent_sign:parent_sign,sign_lines:sign_lines,sign_lines2:sign_lines2,sign_lines3:sign_lines3,hide_profile_pic:hide_profile_pic,hide_phone_number:hide_phone_number,position_num:position_num,start_date:start_date,end_date:end_date,previous_session:previous_session},
            success:function(res){
 			  // alert(res);		  
			   $("#resultContainer").html(res);
			   $("#loader-1").css("display", "none");
 			}
         });
	});
});
</script>

<?= $this->endSection() ?>