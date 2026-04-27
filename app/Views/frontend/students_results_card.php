<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<!-- Content Header (Page header) -->
<style>
	.list-group-item{
	  	width: 33% !important;
	    float: left !important;
	    padding: 1px 10px !important;
	    border-right: 0 none;
	    border-left: 0 none;
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
<?php 
	$schoolinfo = getSchoolInfoFront();

		if($this->input->post('session_id')){
			$sessionid = $this->input->post('session_id');
		}else{
			$sessionid = $schoolinfo['session_id'];	
		}

		$cnic = $this->input->post('cnic');
		$parent_info = $this->db->query('SELECT * from parents where father_cnicnew="'.$cnic.'"')->row();	
		$campus_id = $parent_info->campus_id;	

		$where = "session_id=".$sessionid." AND campus_id=".$campus_id;
		$this->db->where($where);	
		$exams = $this->db->get('exam')->result();

?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>
           Students Results
        </h1>
      </div>
     
    </div>
  </div><!-- /.container-fluid -->
</section>
<!-- Main content -->
<section class="content"> 
<div class="">
  <div class="col-lg-12">
    <div class="card card-primary card-outline card-tabs" style="background: #fff !important;">
       	<div class="card-header p-0 pt-1 border-bottom-0">
				<div class="card-body">
					<div id="loader-1" class="overlay col-md-12 text-center" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
					<div class="container" id="resultContainer"></div>
		    </div>
		    </div>
		</div>
	</div>
</div>
</section>
<!-- /.content -->
<?php 
	
	if(isset($_GET['pid'])){
		$pid = $_GET['pid'];
	}else{
		$pid = '';
	} 

	if(isset($_GET['session_id'])){
		$session_id = $_GET['session_id'];
	}else{
		$session_id = '';
	}

	if(isset($_GET['exam_id'])){
		$exam_id = $_GET['exam_id'];
	}else{
		$exam_id = '';
	}
?>
<script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript">
$(function(){
	//$('#ViewResutlt').on('click', function() {	
	$("#loader-1").css("display", "block");	
	var academic_result = [];
	var examids = [];
	var non_academics = [];
	var examids = <?php echo $exam_id; ?>;
	//var testids =	('#test_id').val();
	var pid = '<?php echo $pid; ?>';
	var session_id = <?php echo $session_id; ?>;

	$.ajax({
        url: '/students_results_card/data',
        type: "POST",
        data:{session_id:session_id,pid:pid,examids:examids},
        success:function(res){
 			  // alert(res);		  
			   $("#resultContainer").html(res);
			   $("#loader-1").css("display", "none");
 					}
         });
	//});
});
</script>