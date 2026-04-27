<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
if(isset($info)){

	$header = 'Edit Term Session';
	$id = $info->term_session_id;
	$term_id = $info->term_id;
	$session_id = $info->session_id;
	$start_date = $info->start_date;
	$end_date = $info->end_date;

}else{
	$header = 'Add Term Session';
	$id = '';
	$term_id = '';
	$session_id = '';
	$start_date = '';
	$end_date = '';

}
?>
<style type="text/css">
	.table td, .table th{
		padding: 2px 0px;
    text-align: center;
    vertical-align: middle;
    font-size: 12px;
    border-top: 1px solid #dee2e6;
	}
	tr{border-bottom:2px solid #000;}
</style>
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-8">
            <h1>
               Student Fee Report
            </h1>
          </div>
          <div class="col-sm-4">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Student Fee Report</li>
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
          	<div class="card-body">	
			<div class="tab-content">
			
		<div class="col-lg-5">
		<div class="form-group">
				<select class="form-control select2" name="student_id" id="student_id" style="height: 24px;width: 100%;">
		       <option value="0">Select Student</option>   
		    </select>
			</div>
		</div>
		<div class="col-lg-2"><input type="button" class="btn btn-primary btn-xs" value="View Report" id="viewReport"></div>
	</div>
			<div class="col-md-12 bg">
		        <div id="loader-1" class="overlay" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
		      </div> 
		    <div id="termssessionarea" class="termssessionarea">
			</div>	
			</div>
		  </div>
        </div>
      </div>
  	</div>
  </section>
    <!-- /.content -->
<script type="text/javascript">

$("#viewReport").click(function(){
     
   var student_id = $('#student_id').val();

   $("#loader-1").css("display", "block");
   $.ajax({
        url:'<?php echo base_url('admin/student_fee_report/single-student-feedata'); ?>', 
        type: "POST",
        data:{student_id:student_id},
        success:function(res){
        	//console.log(res);
		  		 	$("#termssessionarea").html(res);
		   			$("#loader-1").css("display", "none");
	 			}
     });
	 
  });    

$(function(){
 $('.datepicker').datetimepicker({
      format: 'DD/MM/YYYY'
    })

	$('#user-edit-form').validate({
		
	});

	$("#student_id").select2({
    minimumInputLength: 2,
    tags: [],
    ajax: {
        url: '/admin/family_fee_history/get_studentinfo', 
        dataType: 'json',
        type: "POST",
        quietMillis: 50,
        data: function (term) {
            return {
                term: term,
            }
        },
       processResults: function (response) {
        console.log(response);
              return {
                 results: response
              };
           },
           cache: true
    }
 });
	
	
})
</script>

<?= $this->endSection() ?>