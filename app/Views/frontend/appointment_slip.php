<!-- <!DOCTYPE html>
<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Appointment Slip</title>

<style type="text/css">
	@media print {
		.no-print
	    {
	      display: none !important;
	    }
	}
</style>
</head>
<body> -->
<?php require_once(APPPATH.'/views/templates/sidebar.php'); ?>	
<style media="print" type="text/css">
	@media print{
		*,:after,:before{color:#000!important;text-shadow:none!important;background:0 0!important;-webkit-box-shadow:none!important;box-shadow:none!important}
	}
	@media print { body { -webkit-print-color-adjust: exact; } }
	@media print {
		 body { 
		    -webkit-print-color-adjust: exact; 
		  }
		#container{
		  -webkit-print-color-adjust:exact !important;
		  print-color-adjust:exact !important;
		}
		.no-print,.container-fluid
	    {
	      display: none !important;
	    }
	    .ma{background-color: rgb(236, 50, 55);}
	    #container{ -webkit-print-color-adjust:exact !important;
  		print-color-adjust:exact !important; }
  		.table-bordered > thead > tr > th, .table-bordered > tbody > tr > th, .table-bordered > tfoot > tr > th, .table-bordered > thead > tr > td, .table-bordered > tbody > tr > td, .table-bordered > tfoot > tr > td {
	    	border: 1px solid #000 !important;
	    	
	    }
	}

	@print {
    @page :footer {
        display: none
    }
  
    @page :header {
        display: none
    }
	}
	
</style>
<style type="text/css">
	.table-bordered > thead > tr > th, .table-bordered > tbody > tr > th, .table-bordered > tfoot > tr > th, .table-bordered > thead > tr > td, .table-bordered > tbody > tr > td, .table-bordered > tfoot > tr > td {
	    	border: 1px solid #000 !important;
	    	padding: 4px !important;
	    	height: 30px !important;
	    	
	    }
.break-after {
    display: block;
    page-break-after: always;
    position: relative;
}

.break-before {
    display: block;
    page-break-before: always;
    position: relative;
}
</style>
<link href="<?=base_url('assets/css/appointment_slip.css'); ?>" rel="stylesheet" type="text/css">
<!-- <button class="no-print" style="cursor: pointer;color:#fff;background-color: #1D6744 !important;border-radius: 5px;font-size: 17px;padding: 6px 18px;" onclick="history.back()">back to home</button> -->
<script>
function printPageArea(areaID){
    var printContent = document.getElementById(areaID).innerHTML;
    var originalContent = document.body.innerHTML;
    document.body.innerHTML = printContent;
    window.print();
    document.body.innerHTML = originalContent;
}
</script>

<button id="print" class="btn btn-primary no-print" onclick="printPageArea('container');" >Print</button>
<?php foreach ($adRegistrationsInfo as $key => $adRegistrationInfo) { ?>
<!-- <div class="break-after"></div> -->
<div id="container">
<page style="">
<div style="padding:10px;width:800px;min-height: 700px;" >
<?php 
$date = strtotime(date('Y'));
$new_date = strtotime('+ 1 year', $date);
$next_year = date('Y', $new_date);
//print_r($studentInfo);
$age_till_date = '23-02-28';
$bday = new DateTime($studentInfo[$key]->date_of_birth); // Your date of birth
$today = new Datetime(date($age_till_date));
$diff = $today->diff($bday);
?>	

<div id="wb_Text1" style="width: 100%;border: 1px dashed;text-align:center;z-index:0;border-radius: 5px;padding: 10px;float:left;margin: 0 auto;">
<div style="float: left;margin-left: 50px;">
<span style="color:#000000;font-family:Arial;font-size:29px;margin-left: 25px;display: block;font-weight: bold;">THE PREP SCHOOL</span><span style="color:#000000;font-family:Arial;font-size:13px;"></span><span style="color:#000000;font-family:Arial;font-size:13px;"></span><span style="color:#000000;font-family:Arial;font-size:21px;"><u>STUDENTS' ADMISSIONS ... ACADEMIC SESSION <?php echo date('Y') ?>-<?php echo substr($next_year, -2); ?></u></span><span style="color:#000000;font-family:Arial;font-size:13px;"><br></span><span style="color:#000000;font-family:Arial;font-size:21px;"><u>APPOINTMENT SLIP </u></span>
</div>
<div style="float:right;">
<img src="<?=base_url('assets/imgs/sen-pre-logo.jpg'); ?>" class="img-responsive header_logo" style="max-height:100px;float: right;">	
</div>
</div>

<table style="position:relative;left:0px;top:15px;width:100%;font-size:16px;color: #000;z-index:2;border: 1px solid #000;" id="Table2">
	<tr><th colspan="4" style="font-weight:bold;text-transform:uppercase;background-color:#1D6744 !important;border:1px #000000 solid;text-align:center;vertical-align:middle;height: 30px;"><span style="color:#fff !important;font-family:Arial;font-size: 16px;">CANDIDATE'S PARTICULARS</span></th></tr>
<tr>
<td  style="padding: 0px;width: 30%;border: 1px solid #000;print-color-adjust: exact;"><div style="print-color-adjust: exact;text-align:center;line-height: 33px;font-weight: bold;">Level:</div></td>
<td style="padding: 0px;width: 25%;print-color-adjust: exact;border: 1px solid #000;">
<?php if($adRegistrationInfo->class_id == 1){ ?>
	<!-- color:#fff !important;background-color: rgb(236, 50, 55); -->
	<img src="<?=base_url('assets/imgs/ma.jpg'); ?>" class="img-responsive header_logo" style="width:100%;float: left;">
<?php }else{ ?>
	<div class="ma"  style="width: 100%;float: left;text-align: center;border-bottom: 0;line-height: 30px;border-right: 0;border-top: 0;border-left: 0;">Montessori Alpha</div>
<?php } ?>
</td>
<td  style="padding: 0px;width: 25%;print-color-adjust: exact;border: 1px solid #000;">	
	<?php if($adRegistrationInfo->class_id == 2){ ?>
		<!-- color:#fff !important;background-color: #00A85A; -->
		<img src="<?=base_url('assets/imgs/mj.jpg'); ?>" class="img-responsive header_logo" style="width:100%;max-height:100px;float: left;">
	<?php }else{ ?>
	<div style="width: 100%;float: left;text-align: center;padding: 0px;line-height: 30px;border-bottom: 0;border-top: 0;border-right: 0;">Montessori Junior</div>
	<?php } ?>
</td>
<td  style="padding: 0px;width: 25%;print-color-adjust: exact;border: 1px solid #000;">
	<?php if($adRegistrationInfo->class_id == 3){ ?>
		<!-- color:#fff !important;background-color: #3C4099; -->
		<img src="<?=base_url('assets/imgs/ms.jpg'); ?>" class="img-responsive header_logo" style="width:100%;max-height:100px;float: left;">
	<?php }else{ ?> 
	<div style="width: 100%;float: left;text-align: center;line-height: 30px;border-bottom: 0;border-top: 0;border-right: 0;">Montessori Advance</div>
	<?php } ?>
</td>
</tr>	
<tr>
<td style="width: 30%;background-color:transparent;border:1px #000000 solid;text-align:center;vertical-align:middle;height:25px;"><div><span style="color:#000000;font-family:Arial;font-size:14px;font-weight: bold;"> Registration No:</span></div>
</td>
<td colspan="3" style="background-color:transparent;border:1px #000000 solid;text-align:center;vertical-align:middle;height:25px;"><div><span style="color:#000000;font-family:Arial;font-size:14px;"><?php echo $studentInfo[$key]->reg_no; ?></span></div>
</td>
</tr>
<tr>	

<td style="width: 30%;background-color:transparent;border:1px #000000 solid;text-align:center;vertical-align:middle;height:25px;font-weight: bold;"><div><span style="color:#000000;font-family:Arial;font-size:14px;"> </span><span style="background-color:#FFFFFF;color:#000000;font-family:Arial;font-size:14px;">Candidate’s Name:</span></div>
</td>
<td colspan="3" style="width: 30%;background-color:transparent;border:1px #000000 solid;text-align:center;vertical-align:middle;height:25px;" ><div><span style="color:#000000;font-family:Arial;font-size:14px;text-transform: uppercase;"><?php echo $studentInfo[$key]->first_name; ?></span></div>
</td>
</tr>
<tr>
<td style="font-size:14px;background-color:transparent;border:1px #000000 solid;text-align:center;vertical-align:middle;height:25px;font-weight: bold;">Date of Birth: </td>
<td colspan="3"  style="background-color:transparent;border:1px #000000 solid;text-align:center;vertical-align:middle;width:270px;height:25px;"><div><span style="color:#000000;font-family:Arial;font-size:14px;"><?php echo date("d-m-Y", strtotime($studentInfo[$key]->date_of_birth)); ?></span></div>
</td>
</tr>
<tr>
<td style="font-size:14px;background-color:transparent;border:1px #000000 solid;text-align:center;vertical-align:middle;height:25px;font-weight: bold;">Age:</td>
<td colspan="3"  style="background-color:transparent;border:1px #000000 solid;text-align:center;vertical-align:middle;height:25px;"><div><span style="color:#000000;font-family:Arial;font-size:14px;"><?php printf('%d Y, %d M, %d D', $diff->y, $diff->m, $diff->d); //2022Y, 2M, 28D ?></span></div>
</td>
</tr></table>

<table style="position:relative;left:0px;top:45px;width:100%;z-index:4;font-size:16px;color: #000;" id="Table4">
<tr>
<th colspan="4" style="background-color:#1D6744 !important;font-weight:bold;text-transform:uppercase;border:1px #000000 solid;text-align:center;vertical-align:middle;height: 30px;"><span style="font-family:Arial;font-size: 16px;color: #fff !important;">PARENT'S PARTICULARS</span></th>
</tr>
<tr>
<td style="width: 30%;background-color:transparent;border:1px #000000 solid;text-align:center;vertical-align:middle;height:25px;"><div><span style="color:#000000;font-family:Arial;font-size:14px;font-weight: bold;">Father`s Name:</span></div>
</td>
<td colspan="3" style="width: 75%;background-color:transparent;border:1px #000000 solid;text-align:center;vertical-align:middle;height:25px;"><div><span style="color:#000000;font-family:Arial;font-size:14px;text-transform: uppercase;"><?php echo $parentInfo->f_name; ?></span></div>
</td>
</tr>
<tr>
<td style="width: 25%;background-color:transparent;border:1px #000000 solid;text-align:center;vertical-align:middle;height:30px;font-weight: bold;"><div><span style="color:#000000;font-family:Arial;font-size:14px;">Father`s CNIC #:</span></div>
</td>
<td colspan="3"  style="width: 75%;background-color:transparent;border:1px #000000 solid;text-align:center;vertical-align:middle;height:30px;"><div><span style="color:#000000;font-family:Arial;font-size:14px;"><?php echo $parentInfo->father_cnicnew; ?></span></div>
</td>
<?php if($studentInfo[$key]->student_cnic){ ?>
<td style="background-color:transparent;border:1px #000000 solid;text-align:center;vertical-align:middle;height:25px;font-weight: bold;"><div><span style="color:#000000;font-family:Arial;font-size:14px;"> </span><span style="background-color:#FFFFFF;color:#000000;font-family:Arial;font-size:17px;">Candidate’s CNIC:</span></div>
</td>
<?php } ?>
<?php if($studentInfo[$key]->student_cnic){ ?>
<td style="background-color:transparent;border:1px #000000 solid;text-align:center;vertical-align:middle;height:25px;" colspan="3"><div><span style="color:#000000;font-family:Arial;font-size:14px;"><?php echo $studentInfo[$key]->student_cnic; ?></span></div>
</td>
<?php } ?>
</tr>

</table>
<table style="position:relative;left:0px;top:60px;width:100%;z-index:5;font-size:16px;color: #000;" id="Table5">
<tr>
<td colspan="6" style="background-color:#1D6744 !important;font-weight:bold;text-transform:uppercase;border:1px #000000 solid;text-align:center;vertical-align:middle;height:30px;"><div><span style="font-family:Arial;font-size:16px;color: #fff !important;">Appointment Details</span></div>
</td>
</tr>
<tr>
<td style="width: 30%;background-color:transparent;border:1px #000000 solid;text-align:center;vertical-align:middle;height:25px;font-weight: bold;"><div><span style="color:#000000;font-family:Arial;font-size:14px;">Date:</span></div>
</td>
<td style="width: 75%;background-color:transparent;border:1px #000000 solid;text-align:center;vertical-align:middle;height:25px;"><div><span style="color:#000000;font-family:Arial;font-size:14px;"><?php if($adPhaseInfo){ echo date("d-m-Y", strtotime($adPhaseInfo[$key]->observation_date)); } ?></span></div>
</td>
</tr>
<tr>
<td style="width: 25%;background-color:transparent;border:1px #000000 solid;text-align:center;vertical-align:middle;height:25px;font-weight: bold;"><div><span style="color:#000000;font-family:Arial;font-size:14px;">Day:</span></div>
</td>
<td style="width: 75%;background-color:transparent;border:1px #000000 solid;text-align:center;vertical-align:middle;height:25px;"><div><span style="color:#000000;font-family:Arial;font-size:14px;"><?php if(!empty($adPhaseInfo)){ echo date('l', strtotime($adPhaseInfo[$key]->observation_date)); } //echo date("D", $adPhaseInfo->observation_date); ?></span></div>
</td>

</tr>
<tr>

<td style="background-color:transparent;border:1px #000000 solid;text-align:center;vertical-align:middle;height:25px;font-weight: bold;"><div><span style="color:#000000;font-family:Arial;font-size:14px;">Time:</span></div>
</td>

<td style="background-color:transparent;border:1px #000000 solid;text-align:center;vertical-align:middle;height:25px;"><div><span style="color:#000000;font-family:Arial;font-size:14px;"><?php if(!empty($adSlotsInfo)){ echo  date("g:i A", strtotime($adSlotsInfo[$key]->start_time)); } ?></span></div>
</td>
</tr>
<tr>
<td style="background-color:transparent;border:1px #000000 solid;text-align:center;vertical-align:middle;height:25px;font-weight: bold;"><div><span style="color:#000000;font-family:Arial;font-size:14px;"> <?php if($adRegistrationInfo->class_id == 4 || $adRegistrationInfo->class_id == 4){ ?>Room <?php }else{ ?>Interview and Observation Panel: <?php } ?></span></div>
</td>
<td style="background-color:transparent;border:1px #000000 solid;text-align:center;vertical-align:middle;height:25px;"><div><span style="color:#000000;font-family:Arial;font-size:14px;"><?php if($adRegistrationInfo->class_id == 4 || $adRegistrationInfo->class_id == 4){ ?>
		<?php if($roomInfo){ echo $roomInfo->room_name;} ?>
	<?php }else{ ?> 
		<?php if($adpanelsInfo){ echo $adpanelsInfo[$key]->panel_short_name; } ?>
	<?php } ?></span></div>
</td>
</tr>
<tr>
<td style="background-color:transparent;border:1px #000000 solid;text-align:center;vertical-align:middle;height:40px;font-weight: bold;"><div><span style="color:#000000;font-family:Arial;font-size:14px;">&nbsp;&nbsp; Campus to Report:&nbsp;&nbsp; </span></div>
</td>
<td style="word-wrap: break-word;background-color:transparent;border:1px #000000 solid;text-align:center;vertical-align:middle;height:40px;padding-left:10px;" colspan="3"><div><span style="color:#000000;font-family:Arial;font-size:14px;">

	<?php //if(!empty($campusInfo)){ echo $campusInfo->campus_name; } ?>
	<span style="display:block;text-align: center;">
	<?php if(!empty($campusInfo->location)){ echo $campusInfo->location; } ?><br><?php if(!empty($campusInfo->landline)){ echo $campusInfo->landline; } ?><?php if(!empty($campusInfo->email)){ echo $campusInfo->email; } ?>
	</span>
</span></div>
</td>
</tr>
</table>
<div id="wb_Text2" style="position:relative;left:5px;top:70px;width:100%;z-index:7;text-align:left;">
<!---<div><span style="color:#000000;font-family:Arial;font-size:18.5px;"><strong>Admission of each candidate is finalized by the Admission Committee after the following:</strong></span></div>
<div style="margin-left:24px;"><span style="color:#000000;font-family:Wingdings;font-size:19px;">Ø  </span><span style="color:#000000;font-family:Arial;font-size:19px;">An interview with the child’s parents</span></div>
<div style="margin-left:24px;"><span style="color:#000000;font-family:Wingdings;font-size:19px;">Ø  </span><span style="color:#000000;font-family:Arial;font-size:19px;">an observation regarding </span></div>
<div style="margin-left:120px;"><span style="color:#000000;font-family:Wingdings;font-size:19px;">§ </span><span style="color:#000000;font-family:Arial;font-size:19px;">Child’s physical growth</span></div>
<div style="margin-left:120px;"><span style="color:#000000;font-family:Wingdings;font-size:19px;">§ </span><span style="color:#000000;font-family:Arial;font-size:19px;">Performance in the age-specific activities</span></div>
<div><span style="color:#000000;font-family:Arial;font-size:19px;"><br></span></div>-->
<div><span style="color:#000000;font-family:Arial;font-size:17px;"><strong>To be brought by parents on the aforementioned day:</strong></span></div>
<div style="margin-left:24px;"><span style="color:#000000;font-family:Wingdings;font-size:17px;">Ø  </span><span style="color:#000000;font-family:Arial;font-size:16px;">the Appointment Slip (this page), as it carries the Registration Number and other particulars</span></div>
<div style="margin-left:24px;"><span style="color:#000000;font-family:Wingdings;font-size:17px;">Ø  </span><span style="color:#000000;font-family:Arial;font-size:16px;">a photocopy of the Candidate`s Birth Certificate <strong>or</strong> Child Registration Certificate (commonly</span></div>
<div style="margin-left:24px;"><span style="color:#000000;font-family:Arial;font-size:17px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; known as B-Form) <strong>or</strong> Family Registration Certificate </span></div>
<div style="margin-left:24px;"><span style="color:#000000;font-family:Wingdings;font-size:17px;">Ø  </span><span style="color:#000000;font-family:Arial;font-size:16px;">a photocopy of Father's CNIC</span></div>

<div><span style="color:#000000;font-family:Arial;font-size:17px;"></span></div>
<div><span style="color:#000000;font-family:Arial;font-size:17px;"><strong>Important: </strong></span></div>
<div style="margin-left:24px;"><span style="color:#000000;font-family:Wingdings;font-size:17px;">Ø  </span><span style="color:#000000;font-family:Arial;font-size:17px;">Report 15 minutes prior to the given appointment.</span></div>
<div style="margin-left:24px;"><span style="color:#000000;font-family:Wingdings;font-size:17px;">Ø  </span><span style="color:#000000;font-family:Arial;font-size:17px;">Keep the Appointment Slip/Registration Number with a good care as you may need it for </span></div>
<div style="margin-left:24px;"><span style="color:#000000;font-family:Arial;font-size:17px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;finding the child’s name in the merit list or for any other future reference.</span></div>
<div style="margin-left:24px;"><span style="color:#000000;font-family:Arial;font-size:17px;"><br></span></div>
<div style="margin-left:24px;"><span style="color:#000000;font-family:Arial;font-size:17px;">Admission office</span></div>
<div style="margin-left:24px;"><span style="color:#000000;font-family:Arial;font-size:17px;">THE PREP SCHOOL</span></div>
</div>
<div id="wb_Text1" style="font-size:10px !important;width: 100%;border: 1px dashed;text-align:center;z-index:0;border-radius: 5px;padding: 5px;float:left;position: relative;margin: 0 auto;top: 85px;">
	https://portal.theprepschool.com.pk/signin<br>
	Username: <?php echo $parentInfo->father_cnicnew; ?><br>
	Password: <?php echo $parentInfo->pwd; ?>
</div>

</div>
</page>
<div style="clear: right;width: 100%;float: left;height: 100px;"></div>
</div>
<div class="break-before"></div>
<?php } ?>
</body>
</html>