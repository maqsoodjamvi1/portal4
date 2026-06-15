<!doctype html>
<?php
$a = $_GET['id'];
if(empty($a)){
	echo "No Record Found";
	exit;
}else{
	$query = "SELECT * FROM  `recruitment` WHERE  `id` = '$a' OR cnic = '$a' OR reg_no = '$a'";
}


$result = $this->db->query($query);
 
if($result->num_rows() == 0) // User not found. So, redirect to login_form again.
{
   echo "no record found".$a;
   exit;
}
else
{

$q= $this->db->query($query)->row();
// print_r($q);
// exit;


	$position= $q->post;
	$roll= $q->id;
	$reg_no = $q->reg_no;
	$name= $q->name;
	$fname=$q->fname;
    $room= $q->room;
	$wing= $q->wing;
	$cnic=$q->cnic;
	$day=$q->day;
	$date=$q->date;
	$time=$q->time;

$roll2= $roll;//substr($roll, 2);
$t = '';
{
	if ($roll2 <= 1515 ) 
	{
		$reporting_time = '10:00 am - 10:45 am';
		$t ='11:15 am - 12:15 pm';  
		$d ='12 February 2023 (Sunday)'; 

	}else{
		echo "<script>alert('Registration Closed!!')</script>";
	}
	// else if($roll2 > 1400 and $roll2 <= 2400){

	// 	$reporting_time = '02:00 pm - 02:45 pm';
	// 	$t='2:00 pm - 2:45 pm';
	// 	$d ='12 February 2023 (Sunday)'; 
	// }
	
	
}

}
?>
<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<title>Slip</title>
<meta name="generator" content="image">
<link href="Untitled1.css" rel="stylesheet">
<link href="index.css" rel="stylesheet">
<style media="print" type="text/css">
	@media print{*,:after,:before{color:#000!important;text-shadow:none!important;background:0 0!important;-webkit-box-shadow:none!important;box-shadow:none!important}
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

	}
	div#container {
    width: 805px;
    position: relative;
    margin: 0 auto 0 auto;
    text-align: left;
}
	
</style>

</head>
<body>
<div id="container" class="container" style="position:relative;height: 770px;width: 805px;">

<div id="wb_Text1" style="width: 100%;border: 1px dashed;text-align:center;z-index:0;border-radius: 5px;padding: 10px;margin: 0 auto;height: 120px;margin-top: 20px;margin-bottom: 20px;"><img src="<?=base_url('assets/imgs/tps.png'); ?>" class="img-fluid header_logo" style="max-height:100px;float: left;">
<span style="color:#000000;font-family:Tahoma;font-size: 23px;letter-spacing:1px;float: left;margin-left: 20px;margin-top: 15px;">SIDDEEQEEN EDUCATIONAL NETWORK</span><span style="color:#000000;font-family:Tahoma;font-size:21px;"> </span><span style="color:#000000;font-family:Arial;font-size: 15px;float: left;margin-left: 40px;"><u><b>Appointment Slip ... Screening Test for Recruitment<br></b></u></span>
<img src="<?=base_url('assets/imgs/sen-logo.jpg'); ?>" class="img-fluid header_logo" style="max-height:100px;float: right;margin-top: -50px;">
</div>

<!-- <div id="wb_Text2" style="height:44px;text-align:center;z-index:1;">
<span style="color:#000000;font-family:Arial;font-size:19px;"><u><b>Appointment Slip ... Screening Test for Recruitment<br></b></u></u></span></div>
 -->


<table style="width:100%;height:90px;z-index:3;margin-bottom: 20px;" id="Table1">
<tr>
<td style="background-color:transparent;border:1px #C0C0C0 solid;text-align:center;vertical-align:middle;width:136px;height:35px;"><div><span style="color:#000000;font-family:Arial;font-size:17px;"> Registration # </span></div>
</td>
<td  colspan="3" style="background-color:transparent;border:1px #C0C0C0 solid;text-align:center;vertical-align:middle;width:136px;height:35px;"><div><span style="color:#000000;font-family:Arial;font-size:17px;"><b> <?php echo $reg_no; ?></b></span></div>
</td>
</td>
</tr>
<tr>
<td style="background-color:transparent;border:1px #C0C0C0 solid;text-align:center;vertical-align:middle;width:136px;height:42px;"><div><span style="color:#000000;font-family:Arial;font-size:16px;"> Candidate's Name </span></div>
</td>
<td style="background-color:transparent;border:1px #C0C0C0 solid;text-align:center;vertical-align:middle;height:35px;"><div><span style="color:#000000;font-family:Arial;font-size:13px;"></span><span style="color:#000000;font-family:Arial;font-size:17px;"><b><?php echo $name; ?></b>  </span></div>
</td>
</tr>
<tr>
<td style="background-color:transparent;border:1px #C0C0C0 solid;text-align:center;vertical-align:middle;width:136px;height:35px;"><div><span style="color:#000000;font-family:Arial;font-size:17px;"> CNIC # </span></div>
</td>
<td  colspan="3" style="background-color:transparent;border:1px #C0C0C0 solid;text-align:center;vertical-align:middle;width:136px;height:35px;"><div><span style="color:#000000;font-family:Arial;font-size:17px;"><b> <?php echo $cnic; ?></b></span></div>
</td>

</td>
</tr>
</table>

<table style="width:100%;height:100px;z-index:3;margin-bottom: 40px;" id="Table2">
<tr>
<td colspan="4"style="background-color:transparent;border:1px #C0C0C0 solid;text-align:center;vertical-align:middle;width:136px;height:20px;"><div><span style="color:#000000;font-family:Arial;font-size:17px;"> <b>Test Schedule</b> </span></div>
</td>
</tr>
<tr>
  <td  style="background-color:transparent;border:1px #C0C0C0 solid;text-align:center;width:150px;height:20px;"><span style="color:#000000;font-family:Arial;font-size:17px; ">Date </td> 
  <td  style="background-color:transparent;border:1px #C0C0C0 solid;text-align:center;width:150px;height:20px;"><span style="color:#000000;font-family:Arial;font-size:17px; "><b><?php echo $d; ?></b> </td> 
  <td  style="background-color:transparent;border:1px #C0C0C0 solid;text-align:center;width:150px;height:20px;"><span style="color:#000000;font-family:Arial;font-size:17px; ">Room No. </td> 
  <td  style="background-color:transparent;border:1px #C0C0C0 solid;text-align:center;width:150px;height:20px;"><span style="color:#000000;font-family:Arial;font-size:17px; "> <b><?php echo $room; ?></b> </td> 
</tr>
<tr>
    
  <td  style="background-color:transparent;border:1px #C0C0C0 solid;text-align:center;width:150px;height:25px;"><span style="color:#000000;font-family:Arial;font-size:17px; ">Reporting Time </td> 
  <td  style="background-color:transparent;border:1px #C0C0C0 solid;text-align:center;width:150px;height:25px;"><span style="color:#000000;font-family:Arial;font-size:17px; "><b><?php echo $reporting_time; ?></b> </td> 
  <td  style="background-color:transparent;border:1px #C0C0C0 solid;text-align:center;width:150px;height:25px;"><span style="color:#000000;font-family:Arial;font-size:17px; ">Conduction of test </td> 
  <td  style="background-color:transparent;border:1px #C0C0C0 solid;text-align:center;width:150px;height:25px;"><span style="color:#000000;font-family:Arial;font-size:17px; "><b><?php echo $t; ?></b></td> 
</tr>
</table>


<table style="width:100%;height:50px;z-index:5;" id="Table4">
<tr>
<td style="background-color:transparent;border:1px #C0C0C0 solid;text-align:center;vertical-align:middle;width:245px;height:25px;"><div><span style="color:#000000;font-family:Arial;font-size:17px;">Center </span></div>
</td>
<td style="background-color:transparent;border:1px #C0C0C0 solid;text-align:center;vertical-align:middle;height:25px;"><div><span style="color:#000000;font-family:Arial;font-size:19px;"><b> SIDDEEQ PUBLIC SCHOOL, <br>6<sup>th</sup> Road, Satellite Town, Rawalpindi.</b></span></div>
</td>
</tr>
</table>
<div id="wb_Image2" style="width:131px;height:127px;z-index:8;">
<img src="images/wwb_img4.jpg" id="Image2" alt=""></div>
<div id="wb_Text3" style="width:100%;height:146px;z-index:2;text-align:left;">
<div style="margin-left:10px;"><span style="color:#000000;font-family:Wingdings;font-size:17px;">Ø	</span><span style="color:#000000;font-family:Arial;font-size:17px;"><b>To be brought by the candidate on Test day:</b><br></span></div>
<div style=""><span style="color:#000000;font-family:Wingdings;font-size:17px;">ü	</span><span style="color:#000000;font-family:Arial;font-size:16px;">The Appointment Slip (this page) as it carries requisite&nbsp;particulars</span></div>
<div style=""><span style="color:#000000;font-family:Wingdings;font-size:17px;">ü	</span><span style="color:#000000;font-family:Arial;font-size:16px;">Candidate's CNIC</span></div>

<div><span style="color:#000000;font-family:Arial;font-size:13px;"><?php //echo $cnic; ?></span></div>
</div>
</div>
</div>
</body>
</html>