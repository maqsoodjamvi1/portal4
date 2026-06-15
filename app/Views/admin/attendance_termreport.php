<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
			if(isset($info) ){

				$header = 'Edit Students Attendance Report';
				$id = $info->student_id;
				$class_id = $info->class_id;
				$subject_id = $info->sub_id;
				$obtained_marks = $info->obtained_marks;
				$total_marks = $info->Total_marks;
				$campus_id = $sessionData['campusid'];
				$session_id = $sessionData['sessionid'];
			}else{
				$header = 'Add Students Attendance Report';
				$id = '';
				$class_id = '';
				$subject_id = '';
				$obtained_marks = 0;
				$total_marks = 0;
				$campus_id = $sessionData['campusid'];
				$session_id = $sessionData['sessionid'];
			}
			?>
<style type="text/css">
	.verticalTableHeader {
    text-align:center;
    white-space:nowrap;
    g-origin:50% 50%;
    -webkit-transform: rotate(90deg);
    -moz-transform: rotate(90deg);
    -ms-transform: rotate(90deg);
    -o-transform: rotate(90deg);
    transform: rotate(90deg);
    padding: 0px !important;
    
}
.verticalTableHeader p {
    margin:0 -100% ;
    display:inline-block;
}
.verticalTableHeader p:before{
    content:'';
    width:0;
    padding-top:110%;/* takes width as reference, + 10% for faking some extra padding */
    display:inline-block;
    vertical-align:middle;
}
table {
    table-layout : fixed;
}
</style>
<?= view('components/page_header', [
    'title' => 'Term Attendance Report',
    'icon' => 'fas fa-calendar',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Term Attendance Report', 'active' => true],
    ],
]) ?>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-12">
      <div class="nav-tabs-custom">
       
        <div class="tab-content">
		 
		 <div class="row">
		 	<div class="col-md-12 bg">
		        <div class="loader" id="loader-1" style="display: none;">
		          <span></span>
		          <span></span>
		          <span></span>
		          <span></span>
		        </div>
		      </div>
		   <input type="hidden" name="campus_id" id="campus_id" value="<?php echo $campus_id; ?>" />
		    <div class="col-lg-6 col-lg-offset-3">
	            <div class="form-group float-start">
	              <label for="class">Sections</label>
	              <select class="form-control select2" name="section_id" id="section_id">
	              	 <option value="0">Select Section</option>
	                <?php if(isset($sectionsclassinfo)){
						  foreach ($sectionsclassinfo as  $secionvalue) { ?>
	                <option value="<?php echo $secionvalue['section_id']; ?>"><?php echo $secionvalue['sectionclassname']; ?></option>
	              	<?php } ?>
	                <?php } ?>
	              </select>
	            </div>
	            <div class="form-group  float-start" style="margin-left: 15px;">
	            	<label for="class">Date</label>
	             <input type="month" name="date" id="date" required value="<?php echo date('Y-m-d'); ?>" class="form-control" style="height: 24px;line-height: 15px;padding: 0 10px;">
	           </div>
	            <div class="form-group  float-start"  style="margin-left: 15px;">
	            <button type="button" onclick="getstudents();" class="btn btn-sm btn-primary" style="margin-top: 19px;height: 24px;line-height: 10px;">View</button>
	           </div>
	          </div> 
		 </div>
		 
		  <div class="row">          
		  <div class="col-lg-12">
         <div id="students_list_container" ></div>
		 </div>
		
		 
		  </div>
		 
		</div>
		</div>
      </div>
    </div>
  </div>
</section>
<!-- /.content -->
<script>
function getstudents() {
 		$("#loader-1").css("display", "block");
		var campus_id = $('#campus_id').val();
		var section_id = $('#section_id').val();
		var date = $('#date').val();
		
 	      $.ajax({
            url: 'admin.php?c=attendance_monthlyreport&m=get_students_byclass',
            type: "POST",
            data:{section_id: section_id,campus_id:campus_id,date:date },
            success:function(res){
 			   $("#students_list_container").html(res);
 			    $("#loader-1").css("display", "none");
 			  }
         });
 }
</script>

<?= $this->endSection() ?>