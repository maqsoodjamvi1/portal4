<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<html dir="rtl" lang="ur">
<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<!-- Content Header (Page header) -->
<style>
@media print
{
.pagebreak { page-break-before: always; }
#user-edit-form{display: none;}
}
.chalanwrapper{border: 1px solid #000000;text-align: center;float: left;width: 100%;font-size:15px; line-height:22px;"> <span style="font-weight:bold;}
th{ text-align:left; padding-left:10px;}
td{ text-align:left; padding-left:10px;}
.chalanrows{border-bottom:1px solid #000000; text-align:left;padding-left: 10px;}
.feeinfo{font-size: 13px;border:1px solid #000000;border-bottom:0 none;margin: 3px;float: left;width: 98%;margin-bottom:0px;line-height:25px;}
.chalancolleft{border-bottom:1px solid #000000; width:50%;float:left;padding-left: 10px;padding-right:10px;text-align:left;}
.chalancolright{border-bottom:1px solid #000000;width:50%;float:left;padding-left: 10px;text-align:left;}
.feetable{margin:3px;line-height:25px; text-align:left; padding-left:10px; font-size:13px;}

</style>
<?php 
  if(isset($_GET['footer_line1'])){
  $footer_line1 = $_GET['footer_line1'];
  }else{
    $footer_line1 = '';
  }

  if(isset($_GET['show_line1'])){
    $show_line1 = $_GET['show_line1'];
  }else{
    $show_line1 = '';
  }

  if(isset($_GET['footer_line2'])){
    $footer_line2 = $_GET['footer_line2'];
  }else{
    $footer_line2 = '';
  }

  if(isset($_GET['show_line2'])){
    $show_line2 = $_GET['show_line2'];
  }else{
    $show_line2 = '';
  }
?>
<section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-4">
            <h1>
               Family Fee Chalan
            </h1>
          </div>
          <div class="col-sm-4 text-center">
            <a href="<?= base_url('admin/fee_chalan_pdf') ?>" class="btn btn-primary">Print Individual Chalan</a>
          </div>
          <div class="col-sm-4">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Family Fee Chalan</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
</section>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <form action="<?= base_url('admin/fee_chalan_familywise') ?>" role="form" id="user-edit-form" method="get" accept-charset="utf-8">  
        <div class="row">
        
            <div class="col-lg-4 form-group">
                <label>Footer Lines 1:</label>
                  <input type="text" class="form-control float-end"  value="<?php echo $footer_line1; ?>" name="footer_line1">
                <!-- /.input group -->
            </div>
            
             <div class="col-lg-4 form-group">
                <label>Footer Lines 2:</label>
                  <input type="text" class="form-control float-end"  value="<?php echo $footer_line2; ?>" name="footer_line2">
                <!-- /.input group -->
            </div>
            <div class="col-lg-2 form-group">
                <label>Show Footer Line 1:</label>
                  <input type="checkbox" class="form-control float-end" <?php if($show_line1 == 1){ ?> checked <?php } ?>  value="1" name="show_line1">
                <!-- /.input group -->
            </div>
            <div class="col-lg-2 form-group">
                <label>Show Footer Line 2:</label>
                  <input type="checkbox" class="form-control float-end" <?php if($show_line2 == 1){ ?> checked <?php } ?>  value="1" name="show_line2">
                <!-- /.input group -->
            </div>
            <div class="col-sm-2">
              <input style="margin-bottom: 25px;" class="btn btn-primary" name="submit" type="submit" value="view">
            </div>
      </div>       
     </form>
       <div class="card card-primary card-outline card-tabs">
            <div class="card-header p-0 pt-1 border-bottom-0">
        <div class="card-body">
        <?php foreach($data  as $student_info){ ?>
        <div class="tab-content pagebreak table-responsive no-padding" style="padding: 0px !important; ">
          <div  style="margin-bottom: 20px;float: left;width: 100%;">    
            <div class="" style="width:100%;" id="printarea">
              <div style="width:32%; float:left; margin-left:1%;">
                <div style="text-align: center;"> <?php echo rtrim($student_info['emergency_contact'].", ".$student_info['mother_contact'].", ".$student_info['father_contact']); ?></div>
                <div class="chalanwrapper">
                  <div class="row">
                    <div class="col-sm-3 ms-2 mt-2"></div>
                    <div class="col-sm-8" style="font-weight:bold;">Bank Copy</div><br />
                    <div class="col-sm-3 ms-2 mt-2"><img style="width: 100%;" src="<?php echo base_url();?>system-logo/<?php echo $student_info['logo']; ?>"></div>
                    <div class="col-sm-8"><?php echo $student_info['system_name']; ?><br />
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
                  <div class="feeinfo">
                    <div class="chalanrows"> Chalan# <?php echo $student_info['chalan_no']; ?><span style="float: right;margin-right: 10px;">Family# : <?php echo $student_info['family_no']; ?></span></div>
                    <div class="chalanrows" style="line-height: 17px;font-size: 11px;"> <?php echo substr($student_info['stdinfo'],0,-1); ?></div>
                    <div class="chalanrows">Father Name: <?php echo $student_info['f_name']; ?></div>
                    <div class="chalancolleft">Issue Date: <?php echo $student_info['issue_date']; ?></div>
                    <div class="chalancolright">Due Date: <?php echo $student_info['due_date']; ?></div>
                    <div class="chalancolright" >Fee Month:<?php echo $student_info['fee_month']; ?></div>
                  </div>
                  <table width="98%" border="1" class="feetable">
                    <tr>
                      <th>Particulars</th>
                      <th>Amount</th>
                     <!--  <th>Discount</th> -->
                    </tr>
                    <?php $total = 0;
                      $nCount = 0;
                      $arialSum = 0;
                    ?>
                    <?php foreach($student_info['student_fee'] as $fee_info){ ?>
                    <?php $total = $total + $fee_info['amount'];  ?>
                    <?php if($nCount < 5){ ?>
                    <tr>
                      <td> <?php echo $fee_info['fee_month']; ?></td>
                      <td><?php echo ($fee_info['amount'] -  $fee_info['discount']); ?>/-</td>
                      <!-- <td><?php echo $fee_info['discount']; ?></td> -->
                    </tr>
                     <?php }else{ ?>
                      <?php  $arialSum = $arialSum + ($fee_info['amount'] -  $fee_info['discount']); ?>
                     <?php } ?>
                    <?php $total = ($total-$fee_info['discount']); ?>
                    <?php  $nCount++; ?>
                    <?php } ?>
                     <?php if($arialSum > 0){ ?>
                    <tr>
                      <td> Arrears</td>
                      <td><?php echo $arialSum; ?>/-</td>
                      <!-- <td><?php echo $fee_info['discount']; ?></td> -->
                    </tr>
                  <?php } ?>
                  <?php if($nCount < 5){ ?>
                    <?php for($i=1; $i <= 6-$nCount; $i++){ ?>
                     <tr>
                      <td style="height: 34px;"></td>
                      <td></td>
                      <!-- <td><?php echo $fee_info['discount']; ?></td> -->
                    </tr>
                    <?php } ?>
                    <?php } ?>
                    <?php foreach($student_info['fee_fine'] as  $value) { 
                      $total = ($total + $value['fine_amount']);
                      ?>
                     <?php if($value['fine_amount']){ ?>
                      <tr><td>Fine</td><td><?php echo $value['fine_amount']."/-"; ?></td></tr> 
                    <?php } ?>
                  
                   <?php } ?>
                   
                    <tr>
                    <td>Total Payable</td>
                      <td><?php echo $total; ?>/-</td>
                     <!--  <td></td> -->
                    </tr>
					
                  </table>
                  <br />
                  <div style="text-align:left;margin-left: 5px;font-size: 13px;"><strong>Note: </strong><?php echo $student_info['chalan_f_msg']; ?></div>
                </div>
                <?php if($show_line1 == 1){ ?>
                  <div style="float:left;width:98%; border-bottom:1px solid;margin-top:20px;"><?php echo $footer_line1; ?>&nbsp;&nbsp;</div>
                <?php } ?>
                <?php if($show_line2 == 1){ ?>
                <div style="float:left;width:98%; border-bottom:1px solid;margin-top:20px;margin-bottom: 20px;"><?php echo $footer_line2; ?>&nbsp;&nbsp;</div>
                <?php } ?>
              </div>
              <div style="width:32%; float:left; margin-left:1%;">
              <div style="text-align: center;"> <?php echo trim($student_info['emergency_contact'].", ".$student_info['mother_contact'].", ".$student_info['father_contact']); ?></div>
                <div class="chalanwrapper"> 
                  <div class="row">
                    <div class="col-sm-3 ms-2 mt-2"></div>
                    <div class="col-sm-8" style="font-weight:bold;">School Copy</div><br />
                    <div class="col-sm-3 ms-2 mt-2"><img style="width: 100%;" src="<?php echo base_url();?>system-logo/<?php echo $student_info['logo']; ?>"></div>
                    <div class="col-sm-8"><?php echo $student_info['system_name']; ?><br />
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
                  <div class="feeinfo">
                    <div class="chalanrows"> Chalan# <?php echo $student_info['chalan_no']; ?><span style="float: right;margin-right: 10px;">Family# : <?php echo $student_info['family_no']; ?></span></div>
                    <div class="chalanrows" style="line-height: 17px;font-size: 11px;"> <?php echo substr($student_info['stdinfo'],0,-1); ?></div>
                    <div class="chalanrows">Father Name: <?php echo $student_info['f_name']; ?></div>
                    <div class="chalancolleft">Issue Date: <?php echo $student_info['issue_date']; ?></div>
                    <div class="chalancolright">Due Date: <?php echo $student_info['due_date']; ?></div>
                    <div class="chalancolright" >Fee Month:<?php echo $student_info['fee_month']; ?></div>
                  </div>
                  <table width="98%" border="1" class="feetable">
                    <tr>
                      <th>Particulars</th>
                      <th>Amount</th>
                      <!-- <th>Discount</th> -->
                    </tr>
                    <?php $total = 0;
                      $nCount = 0;
                      $arialSum = 0;
                    ?>
                    <?php foreach($student_info['student_fee'] as $fee_info){ ?>
                    <?php $total = $total + $fee_info['amount'];  ?>
                    <?php if($nCount < 5){ ?>
                    <tr>
                      <td> <?php echo $fee_info['fee_month']; ?></td>
                      <td><?php echo ($fee_info['amount'] -  $fee_info['discount']); ?>/-</td>
                      <!-- <td><?php echo $fee_info['discount']; ?></td> -->
                    </tr>
                     <?php }else{ ?>
                      <?php  $arialSum = $arialSum + ($fee_info['amount'] -  $fee_info['discount']); ?>
                     <?php } ?>
                    <?php $total = ($total-$fee_info['discount']); ?>
                    <?php  $nCount++; ?>
                    <?php } ?>
                     <?php if($arialSum > 0){ ?>
                    <tr>
                      <td> Arrears</td>
                      <td><?php echo $arialSum; ?>/-</td>
                      <!-- <td><?php echo $fee_info['discount']; ?></td> -->
                    </tr>
                  <?php } ?>
                  <?php if($nCount < 5){ ?>
                    <?php for($i=1; $i <= 6-$nCount; $i++){ ?>
                     <tr>
                      <td style="height: 34px;"></td>
                      <td></td>
                      <!-- <td><?php echo $fee_info['discount']; ?></td> -->
                    </tr>
                    <?php } ?>
                    <?php } ?>
                    <?php foreach($student_info['fee_fine'] as  $value) { 
                      //print_r($value);
                      $total = ($total + $value['fine_amount']);
                      ?>
                     <?php if($value['fine_amount']){ ?>
                      <tr><td>Fine</td><td><?php echo $value['fine_amount']."/-"; ?></td><!-- <td></td> --></tr>
                    <?php } ?>
                   <?php } ?>
                    <tr>
                    <td>Total Payable</td>
                      <td><?php echo $total; ?>/-</td>
                     <!--  <td></td> -->
                    </tr>

                  </table>
                  <br />
                  <div style="text-align:left;margin-left: 5px;font-size: 13px;"><strong>Note: </strong><?php echo $student_info['chalan_f_msg']; ?></div>
                </div>
                <?php if($show_line1 == 1){ ?>
                  <div style="float:left;width:98%; border-bottom:1px solid;margin-top:20px;"><?php echo $footer_line1; ?>&nbsp;&nbsp;</div>
                <?php } ?>
                <?php if($show_line2 == 1){ ?>
                <div style="float:left;width:98%; border-bottom:1px solid;margin-top:20px;margin-bottom: 20px;"><?php echo $footer_line2; ?>&nbsp;&nbsp;</div>
                <?php } ?>
              </div>
              <div style="width:32%; float:left; margin-left:1%;">
               <div style="text-align: center;"> <?php echo trim($student_info['emergency_contact'].", ".$student_info['mother_contact'].", ".$student_info['father_contact']); ?></div>
                <div class="chalanwrapper">
                  <div class="row">
                    <div class="col-sm-3 ms-2 mt-2"></div>
                    <div class="col-sm-8" style="font-weight:bold;">Student Copy</div><br />
                    <div class="col-sm-3 ms-2 mt-2"><img style="width: 100%;" src="<?php echo base_url();?>system-logo/<?php echo $student_info['logo']; ?>"></div>
                    <div class="col-sm-8"><?php echo $student_info['system_name']; ?><br />
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
                  <div class="feeinfo">
                    <div class="chalanrows"> Chalan# <?php echo $student_info['chalan_no']; ?><span style="float: right;margin-right: 10px;">Family# : <?php echo $student_info['family_no']; ?></span></div>
                    <div class="chalanrows" style="line-height: 17px;font-size: 11px;"> <?php echo substr($student_info['stdinfo'],0,-1); ?></div>
                    <div class="chalanrows">Father Name: <?php echo $student_info['f_name']; ?></div>
                    <div class="chalancolleft"> Issue Date: <?php echo $student_info['issue_date']; ?></div>
                    <div class="chalancolright"> Due Date: <?php echo $student_info['due_date']; ?></div>
                     <div class="chalancolright">Fee Month:<?php echo $student_info['fee_month']; ?></div>
                  
                  </div>
                  <table width="98%" border="1" class="feetable">
                    <tr>
                      <th>Particulars</th>
                      <th>Amount</th>
                      <!-- <th>Discount</th> -->
                    </tr>
                    <?php $total = 0;
                      $nCount = 0;
                      $arialSum = 0;
                    ?>
                    <?php foreach($student_info['student_fee'] as $fee_info){ ?>
                    <?php $total = $total + $fee_info['amount'];  ?>
                    <?php if($nCount < 5){ ?>
                    <tr>
                      <td> <?php echo $fee_info['fee_month']; ?></td>
                      <td><?php echo ($fee_info['amount'] -  $fee_info['discount']); ?>/-</td>
                      <!-- <td><?php echo $fee_info['discount']; ?></td> -->
                    </tr>
                     <?php }else{ ?>
                      <?php  $arialSum = $arialSum + ($fee_info['amount'] -  $fee_info['discount']); ?>
                     <?php } ?>
                    <?php $total = ($total-$fee_info['discount']); ?>
                    <?php  $nCount++; ?>
                    <?php } ?>
                    <?php if($arialSum > 0){ ?>
                    <tr>
                      <td> Arrears</td>
                      <td><?php echo $arialSum; ?>/-</td>
                      <!-- <td><?php echo $fee_info['discount']; ?></td> -->
                    </tr>
                  <?php } ?>
                  <?php if($nCount < 5){ ?>
                    <?php for($i=1; $i <= 6-$nCount; $i++){ ?>
                     <tr>
                      <td style="height: 34px;"></td>
                      <td></td>
                      <!-- <td><?php echo $fee_info['discount']; ?></td> -->
                    </tr>
                    <?php } ?>
                    <?php } ?>
                    <?php foreach($student_info['fee_fine'] as  $value) { 
                      //print_r($value);
                      $total = ($total + $value['fine_amount']);
                      ?>    
                    <?php if($value['fine_amount']){ ?>
                      <tr><td>Fine</td><td><?php echo $value['fine_amount']."/-"; ?></td><!-- <td></td> --></tr>
                    <?php } ?>
                   <?php } ?>
                    <tr>
                    <td>Total Payable</td>
                      <td><?php echo $total; ?>/-</td>
                     <!--  <td></td> -->
                    </tr>

                  </table>
                  <br />
                  <div style="text-align:left;margin-left: 5px;font-size: 13px;"><strong>Note: </strong><?php echo $student_info['chalan_f_msg']; ?></div>
                </div>
                <?php if($show_line1 == 1){ ?>
                  <div style="float:left;width:98%; border-bottom:1px solid;margin-top:20px;"><?php echo $footer_line1; ?>&nbsp;&nbsp;</div>
                <?php } ?>
                <?php if($show_line2 == 1){ ?>
                <div style="float:left;width:98%; border-bottom:1px solid;margin-top:20px;margin-bottom: 20px;"><?php echo $footer_line2; ?>&nbsp;&nbsp;</div>
                <?php } ?>
              </div>
            </div>
          </div>
          <!-- Row End-->
        </div>
        <!-- <div style="break-after:page"></div> -->
         <?php } ?>
      </div>
    </div>
      <!-- /.box-body -->
    </div>
    <!-- /.box -->
  </div>
  </div>
</section>
<!-- /.content -->
<script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/0.9.0rc1/jspdf.min.js"></script>

<?= $this->endSection() ?>