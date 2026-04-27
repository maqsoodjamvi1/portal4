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
		if($this->input->get('session_id')){ 
				$sessionid = $this->input->get('session_id');
		}else{
				$sessionid = $schoolinfo['session_id'];	
		}

		$cnic = $this->input->get('cnic');
		$parent_info = $this->db->query('SELECT * from parents where father_cnicnew="'.$cnic.'"')->row();	
		$campus_id = $parent_info->campus_id;	
	
		$where = "session_id=".$sessionid." AND campus_id=".$campus_id;
		$this->db->where($where);	
		$test_series = $this->db->get('test_series')->result();
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>
           Test Results
        </h1>
      </div>
      <!-- <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?php echo '#/';?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Test Results</li>
        </ol>
      </div> -->
    </div>
  </div><!-- /.container-fluid -->
</section>
<!-- Main content -->
<section class="content"> 
<div class="row">
  <div class="col-lg-12">
     <div class="card card-primary card-outline card-tabs" style="background: #fff !important;">
       	<div class="card-header p-0 pt-1 border-bottom-0">
     
		<div class="card-body">
		<!-- <div class="no-print">
			<form>
				<div class="col-lg-12">
					<label for="class"><strong>Tests</strong></label><br>
					<select name="test_id" id="test_id" class="form-control"> 
					<?php  foreach ($test_series as $key => $test) { ?>
							<option value="<?php echo $test->t_series_id; ?>"><?php echo $test->series_name; ?></option>
					<?php } ?>
					</select>
				</div> 
				<div class="col-lg-12"><input style="line-height: 19px;margin: 10px 0px;" type="button" class="btn btn-primary pull-right" value="View Result Card " name="View" id="ViewResutlt"></div>
				</form>
	
	</div> -->
	<div id="loader-1" class="overlay col-md-12 text-center" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
	<div class="col-lg-12" style="clear:both;" id="resultContainer"></div>
    </div>
    </div>
</div>
</div>
</div>
</section>
<?php 
	
	if(isset($_GET['cnic'])){
		$cnic = $_GET['cnic'];
	}else{
		$cnic = '';
	} 

	if(isset($_GET['session_id'])){
		$session_id = $_GET['session_id'];
	}else{
		$session_id = '';
	}

	if(isset($_GET['test_id'])){
		$test_id = $_GET['test_id'];
	}else{
		$test_id = '';
	}

?>
<!-- /.content -->
<script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript">
$(function(){
	//$('#ViewResutlt').on('click', function() {	
	$("#loader-1").css("display", "block");	
	var academic_result = [];
	var testids = [];
	var non_academics = [];
	<?php if($test_id){ ?>
			var testids = <?php echo $test_id; ?>;
	<?php }else{ ?> 
			var testids = $('#test_id').val();
	<?php } ?>
	//var testids =	('#test_id').val();
	var cnic = '<?php echo $cnic; ?>';
	var session_id = <?php echo $session_id; ?>;
	
	$.ajax({
            url: '/test_series_result_card/data',
            type: "POST",
            data:{session_id:session_id,cnic:cnic,testids:testids},
            success:function(res){
 			  // alert(res);		  
			   $("#resultContainer").html(res);
			   $("#loader-1").css("display", "none");
 			}
         });
	//});

	$('#ViewResutlt').on('click', function() {	
	$("#loader-1").css("display", "block");	
	var academic_result = [];
	var testids = [];
	var non_academics = [];
	<?php if($test_id){ ?>
			var testids = <?php echo $test_id; ?>;
	<?php }else{ ?> 
			var testids = $('#test_id').val();
	<?php } ?>
	//var testids =	('#test_id').val();
	var cnic = '<?php echo $cnic; ?>';
	var session_id = <?php echo $session_id; ?>;
	
	$.ajax({
            url: '/test_series_result_card/data',
            type: "POST",
            data:{session_id:session_id,cnic:cnic,testids:testids},
            success:function(res){
 			  // alert(res);		  
			   $("#resultContainer").html(res);
			   $("#loader-1").css("display", "none");
 			}
         });
	});

});
</script>