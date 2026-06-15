<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<!-- Content Header (Page header) -->
<style>
@media print
{
.pagebreak { page-break-before: always; }
}
.chalanwrapper{border: 1px solid #000000;text-align: center;float: left;width: 100%;font-size:15px; line-height:22px;"> <span style="font-weight:bold;}
th{ text-align:left; padding-left:10px;}
td{ text-align:left; padding-left:10px;}
.chalanrows{border-bottom:1px solid #000000; text-align:left;padding-left: 10px;}
.feeinfo{font-size: 12px;border:1px solid #000000;border-bottom:0 none;margin: 3px;float: left;width: 98%;margin-bottom:0px;line-height:25px;}
.chalancolleft{border-bottom:1px solid #000000; width:50%;float:left;padding-left: 10px;padding-right:10px;text-align:left;}
.chalancolright{border-bottom:1px solid #000000;width:50%;float:left;padding-left: 10px;text-align:left;}
.feetable{margin:3px;line-height:25px; text-align:left; padding-left:10px; font-size:13px;}
</style>
<style type="text/css">
  .content-header h1{font-size: 22px !important;}
  .nav-sidebar .nav-treeview{
    margin: 0 10px !important;
  }
  .heading3 {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 16px !important;
    font-weight: normal;
  }
  .content-header{
    padding: 2px .5rem;
  }
  .dt-buttons{float: right;}
  .main-header .navbar-nav li a{color: #fff; }
  .card-body{
    padding: 10px !important;
  }
.form-control{
    height: 35px !important;
    font-size: 16px !important;
    line-height: 17px !important;
    padding: 5px 15px;
    border-radius: 0px;
    border: 1px solid blue;
}
.input-group-text {
    padding: 1px 8px !important;
  }
.select2-container--default .select2-selection--single, .select2-selection .select2-selection--single {
      border: 1px solid #d2d6de;
      border-radius: 0;
      padding: 6px 12px;
      height: 30px !important;
  }
.select2-container--default .select2-selection--single .select2-selection__arrow{
    height: 22px !important;
    right: 3px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #444;
    line-height: 24px !important;
}
@media print {
  body {-webkit-print-color-adjust: exact;}
  .resultReport th {
    background: #494E53 !important;
    print-color-adjust: exact; 
    color: #fff;
    text-align: left;
}
  .printable_result_header_width{width: 730px !important;}
  .nav-tabs{ display: none; }
  .card-primary.card-outline{    border-top: 0px none !important;}
  .main-footer{display: none;}
  .btn {display: none;}
  .no-print,.nav-tabs,.main-footer,.no-print *
    {
      display: none !important;
    }
    #form-filter,.operation,.main-footer,.nav-tabs,.dt-buttons,.dataTables_info,.paging_simple_numbers,.no-print, .no-print *{
      display: none !important;
    }
    @page { 
        size: auto;
    }
    
    }
    </style>
<!-- Main content -->
<section class="container content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
        <?php foreach($data  as $student_info){ ?>
        <div class="card-body ">  
        <div class="tab-content" >
            <div class="" style="width:100%;" id="printarea">
             
<div style="width:99%; float:left; margin-left:1%;">
<div dir="rtl" lang="ur"> <?php echo $student_info['chalan_h_msg']; ?></div>
<div class="chalanwrapper">
<div class="row">
  <div class="col-sm-3 ms-2 mt-2"></div>
  <div class="col-sm-8" style="font-weight:bold;">Student Copy</div><br />
  <div class="col-sm-3 ms-2 mt-2" style="width: 25%;float: left;padding-left: 25px;"><img style="width: 70px;" src="<?php echo base_url();?>system-logo/<?php echo $student_info['logo']; ?>"></div>
  <div class="col-sm-8"  style="width: 75%;float: left;"><?php echo $student_info['system_name']; ?><br />
  <?php echo $student_info['campus_name']; ?>, <?php echo $student_info['location']; ?></div>
</div>  
<div class="ms-2 mt-2" style="text-align: left;">
<?php 
  if($student_info['bank_name']){
    echo $student_info['bank_name'].', '; 
  }
?>
<?php 
  if($student_info['bank_address']){
  echo $student_info['bank_address'].', ';
  }
?> 
<?php if($student_info['bank_code']){ echo $student_info['bank_code']; } ?><br />
<?php if($student_info['bank_acc']){ ?>
  Account No: <?php echo $student_info['bank_acc']; ?><br />
<?php } ?>
</div>
<div class="feeinfo" >
  <div class="chalanrows"> Chalan# <?php echo $student_info['chalan_no']; ?></div>
  <div class="chalanrows"> Name: <?php echo $student_info['student_name']; ?></div>
  <div class="chalanrows">Father Name: <?php echo $student_info['f_name']; ?></div>
  <div class="chalancolleft"><?php echo $student_info['class_name']; ?></div>
  <div class="chalancolright" >Fee Month:<?php echo $student_info['fee_month']; ?></div>
  <div class="chalancolleft">Issue Date: <?php echo $student_info['issue_date']; ?></div>
  <div class="chalancolright">Due Date: <?php echo $student_info['due_date']; ?></div>
</div>
<table width="98%" border="1" class="feetable">
  <tr>
    <th>Particulars</th>
    <th>Amount</th>
    <th>Discount</th>
  </tr>
<?php 
                      $total = 0;
                      $nCount = 0;
                      $arialSum = 0;
                    ?>
                    <?php foreach($student_info['student_fee'] as $fee_info){ ?>
                    <?php $total = $total + $fee_info['amount'];  ?>
                    <?php if($nCount < 5){ ?>
                    <tr>
                      <td><?php echo $fee_info['fee_name']; ?> (<?php echo $fee_info['fee_month']; ?>)</td>
                      <td><?php echo $fee_info['amount']; ?>/-</td>
                    <td><?php 
                     if($fee_info['is_monthly_fee'] == 1){
                         echo $fee_info['discount'];
                     }
                   ?></td>
                    </tr>
                  <?php }else{ ?>
                      <?php  $arialSum = $arialSum + ($fee_info['amount'] -  $fee_info['discount']); ?>
                  <?php } ?>
                  
                  <?php 
                     if($fee_info['is_monthly_fee'] == 1){
                       $total = ($total-$fee_info['discount']); 
                    }
                   ?>
                  <?php  $nCount++; ?>
                  <?php } ?>
                  <?php if($arialSum > 0){ ?>
                    <tr>
                      <td> Arial</td>
                      <td><?php echo $arialSum; ?>/-</td>
                      <!-- <td><?php echo $fee_info['discount']; ?></td> -->
                    </tr>
                  <?php } ?>
                  <?php if($nCount < 5){ ?>
                    <?php for($i=1; $i <= 6-$nCount; $i++){ ?>
                     <tr>
                      <td style="height: 34px;"></td>
                      <td></td>
                      <td></td>
                    </tr>
                    <?php } ?>
                    <?php } ?>
<?php foreach($student_info['fee_fine'] as  $value) { 
  $total = ($total + $value['fine_amount']);
?>
<tr><td>Fine(<?php echo $value['fee_month']; ?>)</td><td><?php echo $value['fine_amount']; ?></td><td></td></tr>
<?php } ?>
<tr>
  <td>Total Payable</td>
  <td><?php echo $total; ?>/-</td>
  <td></td>
</tr>
</table>
<br />
<div style="text-align:left;margin-left: 5px;"><?php echo $student_info['chalan_f_msg']; ?></div>
</div>
</div>
</div>
</div>
<div class="pagebreak"></div>
<?php } ?>
<!-- Row End-->
</div>
</div>
<!-- /.box-body -->
</div>
<!-- /.box -->
</div>
</div>
</section>
<!-- /.content -->