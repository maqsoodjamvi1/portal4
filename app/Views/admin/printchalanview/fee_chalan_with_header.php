<html dir="rtl" lang="ur">
<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<!-- Content Header (Page header) -->
<style>
@media print
{
.pagebreak { page-break-before: always; }
#user-edit-form{display: none;}
}
.chalanwrapper{border: 1px solid #000000;text-align: center;float: left;width: 100%;font-size:15px; line-height:22px;}
th{ text-align:left; padding-left:10px;}
td{ text-align:left; padding-left:10px;}
.chalanrows{border-bottom:1px solid #000000; text-align:left;padding-left: 10px;}
.feeinfo{font-size: 12px;border:1px solid #000000;border-bottom:0 none;margin: 3px;float: left;width: 98%;margin-bottom:0px;line-height:25px;}
.chalancolleft{border-bottom:1px solid #000000; width:50%;float:left;padding-left: 10px;padding-right:10px;text-align:left;}
.chalancolright{border-bottom:1px solid #000000;width:50%;float:left;padding-left: 10px;text-align:left;}
.feetable{margin:3px;line-height:25px; text-align:left; padding-left:10px; font-size:13px;}
</style>
<?php 
if(isset($_GET['cls_sec_id'])){
  $cls_sec_id = $_GET['cls_sec_id'];
}else{
  $cls_sec_id = '';
}
if(isset($_GET['fee_month'])){
  $fee_month = $_GET['fee_month'];
}else{
  $fee_month = '';
}

if(isset($_GET['fine_after_due_date'])){
  $fine_after_due_date = $_GET['fine_after_due_date'];
}else{
  $fine_after_due_date = '';
}


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
          <div class="col-sm-6">
            <h1>
               Fee Chalan
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?php echo '#/';?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Fee Chalan</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
</section>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-lg-12">
    <form action="/admin.php#/fee_chalan_with_header" role="form" id="user-edit-form" method="get" accept-charset="utf-8">	
      <div class="row">
        <div class="col-lg-4 form-group">
            <label>Fee Month:</label>
              <input type="month" class="form-control pull-right" id="datetimepicker10"  value="<?php echo $fee_month; ?>" name="fee_month">
            <!-- /.input group -->
        </div>
        <div class="col-lg-4 form-group">
        <label for="class"><strong>Class</strong></label><br>
          <select class="form-control" name="cls_sec_id" id="cls_sec_id">
            <option value="">All Classes</option>
          <?php if(isset($sectionsclassinfo)){
            foreach ($sectionsclassinfo as  $sectionvalue) {
           ?>
          <option <?php if($cls_sec_id == $sectionvalue['section_id']){ ?> selected <?php } ?>  value="<?php echo $sectionvalue['section_id']; ?>"><?php echo $sectionvalue['sectionclassname']; ?></option>
          <?php } ?>
          <?php } ?>  
          </select>
      </div>
      <div class="col-sm-2">
        <label>Display Fine</label><br>
        <input class="form-control" type="checkbox" <?php if($fine_after_due_date == 1){ ?> checked <?php } ?> name="fine_after_due_date" value="1">
      </div>
            <div class="col-lg-4 form-group">
                <label>Footer Lines 1:</label>
                  <input type="text" class="form-control pull-right"  value="<?php echo $footer_line1; ?>" name="footer_line1">
                <!-- /.input group -->
            </div>
            
             <div class="col-lg-4 form-group">
                <label>Footer Lines 2:</label>
                  <input type="text" class="form-control pull-right"  value="<?php echo $footer_line2; ?>" name="footer_line2">
                <!-- /.input group -->
            </div>
            <div class="col-lg-2 form-group">
                <label>Show Footer Line 1:</label>
                  <input type="checkbox" class="form-control pull-right" <?php if($show_line1 == 1){ ?> checked <?php } ?>  value="1" name="show_line1">
                <!-- /.input group -->
            </div>
            <div class="col-lg-2 form-group">
                <label>Show Footer Line 2:</label>
                  <input type="checkbox" class="form-control pull-right" <?php if($show_line2 == 1){ ?> checked <?php } ?>  value="1" name="show_line2">
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
      <?php 
      if($data){
      foreach($data  as $student_info){ ?>
      <div class="tab-content pagebreak table-responsive no-padding" style="padding: 0px !important;">
          <div  style="margin-bottom: 20px;float: left;width: 100%;">
            <div class="" style="width:100%;" id="printarea">
              <div style="width:32%; float:left; margin-left:1%;">
                <div dir="rtl" lang="ur"> <?php echo $student_info['chalan_h_msg']; ?></div>
                <div class="chalanwrapper">
                  <div class="row">
                    <div class="col-sm-2 ml-2 mt-2"></div>
                    <div class="col-sm-8" style="font-weight:bold;">Fee Slip - Bank Copy</div><br />
                    <img style="width: 95%;margin-left: 8px;" src="<?php echo base_url();?>system-logo/<?php echo $student_info['chalan_header']; ?>">
                    <!-- <div class="col-sm-3 ml-2 mt-2"><img style="width: 100%;" src="<?php echo base_url();?>system-logo/<?php echo $student_info['logo']; ?>"></div>
                    <div class="col-sm-8"><span style="font-weight:bold;font-size: 16px;"><?php echo $student_info['system_name']; ?></span><br />
                    <?php echo $student_info['campus_name']; ?>, <?php echo $student_info['location']; ?></div> -->
                  </div>  
                  <div class="ml-2 mt-2" style="text-align: left;">

                  <?php 
                    // if($student_info['bank_name']){
                    //   echo $student_info['bank_name'].', '; 
                    // }
                  ?>
                  <?php 
                    // if($student_info['bank_address']){
                    // echo $student_info['bank_address'].', ';
                    // }
                  ?> 
                  <?php 
                  //if($student_info['bank_code']){ echo $student_info['bank_code']; } 
                  ?>
                  <!-- <br /> -->
                  <?php //if($student_info['bank_acc']){ ?>
                    <!-- Account No: <?php //echo $student_info['bank_acc']; ?><br /> -->
                  <?php //} ?>
                  </div>
                  <div  class="feeinfo">
                    <div class="chalanrows"> Chalan# <?php echo $student_info['chalan_no']; ?> <span style="float: right;margin-right: 10px;">Family# : <?php echo $student_info['family_no']; ?></span><span style="float:right;margin-right: 10px;">Reg# : <?php echo $student_info['reg_no']; ?></span></div>
                    <div class="chalanrows"> Name: <?php echo $student_info['student_name']; ?></div>
                    <div class="chalanrows">Father Name: <?php echo $student_info['f_name']; ?></div>
                    <div class="chalancolleft"><?php echo $student_info['class_name']; ?></div>
                    <div class="chalancolright" >Fee Month:<?php echo $student_info['fee_month']; ?></div>
                    <div class="chalancolleft">Issue Date: <?php echo $student_info['issue_date']; ?></div>
                    <div class="chalancolright"><strong>Due Date: <?php echo $student_info['due_date']; ?></strong></div>
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
                     <td>
                      <?php if($fee_info['is_monthly_fee'] == 1){
          					   echo $fee_info['discount'];
          					  } ?>      
                     </td>
                    </tr>
                    <?php }else{ ?>
                    <?php  $arialSum = $arialSum + ($fee_info['amount'] - $fee_info['discount']); ?>
                    <?php } ?>
                    <?php
          					if($fee_info['is_monthly_fee'] == 1){
          					 $total = ($total-$fee_info['discount']);
          					 } ?>
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
                    <td><strong>Payable Within Due Date</strong></td>
                      <td><strong><?php echo $total; ?>/-</strong></td>
                      <td></td>
                    </tr>
                    <?php
                     $late_fee_fine = 0;
                     if($student_info['fine_type'] == 'per_day_fine'){
                        $late_fee_fine = $student_info['late_fee_fine']*15;
                     }else{
                        $late_fee_fine = $student_info['late_fee_fine'];
                     }
                    if($fine_after_due_date == 1){ ?>
                    <tr><td>Late Fee Fine </td><td><?php echo $late_fee_fine; ?>/-</td><td></td></tr>
                    <tr>
                    <td><strong>Payable After Due Date</strong></td>
                      <td><strong><?php echo $total+$late_fee_fine; ?>/-</strong></td>
                      <td></td>
                    </tr>
                    <?php } ?>
					        </table>
                  <br>
                  <div style="text-align:left;margin-left: 5px;"><?php echo $student_info['chalan_f_msg']; ?></div>

                </div>
                 <?php if($show_line1 == 1){ ?>
                  <div style="float:left;width:98%; border-bottom:1px solid;margin-top:20px;"><?php echo $footer_line1; ?>&nbsp;&nbsp;</div>
                <?php } ?>
                <?php if($show_line2 == 1){ ?>
                <div style="float:left;width:98%; border-bottom:1px solid;margin-top:20px;margin-bottom: 20px;"><?php echo $footer_line2; ?>&nbsp;&nbsp;</div>
                <?php } ?>
              </div>
              <div style="width:32%; float:left; margin-left:1%;">
                <div dir="rtl" lang="ur"> <?php echo $student_info['chalan_h_msg']; ?></div>
                <div class="chalanwrapper"> 
                  <div class="row">
                    <div class="col-sm-2 ml-2 mt-2"></div>
                    <div class="col-sm-8" style="font-weight:bold;">School Copy</div><br />
                    <!-- <div class="col-sm-3 ml-2 mt-2"><img style="width: 100%;" src="<?php echo base_url();?>system-logo/<?php echo $student_info['logo']; ?>"></div> -->
                    <img style="width: 95%;margin-left: 8px;" src="<?php echo base_url();?>system-logo/<?php echo $student_info['chalan_header']; ?>">
                    <!-- <div class="col-sm-8" ><span style="font-weight:bold;font-size: 16px;"><?php echo $student_info['system_name']; ?></span><br />
                    <?php echo $student_info['campus_name']; ?>, <?php echo $student_info['location']; ?></div> -->
                  </div>  
                  <div class="ml-2 mt-2" style="text-align: left;">
                  <?php 
                    // if($student_info['bank_name']){
                    //   echo $student_info['bank_name'].', '; 
                    // }
                  ?>
                  <?php 
                    // if($student_info['bank_address']){
                    // echo $student_info['bank_address'].', ';
                    // }
                  ?> 
                  <?php //if($student_info['bank_code']){ echo $student_info['bank_code']; } ?>
                  <!-- <br /> -->
                  <!-- <?php if($student_info['bank_acc']){ ?> -->
                    <!-- Account No: <?php //echo $student_info['bank_acc']; ?><br /> -->
                  <?php //} ?>
                  </div>
                  <div class="feeinfo">
                    <div class="chalanrows"> Chalan# <?php echo $student_info['chalan_no']; ?> <span style="float: right;margin-right: 10px;">Family# : <?php echo $student_info['family_no']; ?></span><span style="float:right;margin-right: 10px;">Reg# : <?php echo $student_info['reg_no']; ?></span></div>
                    <div class="chalanrows"> Name: <?php echo $student_info['student_name']; ?></div>
                    <div class="chalanrows">Father Name: <?php echo $student_info['f_name']; ?></div>
                    <div class="chalancolleft"><?php echo $student_info['class_name']; ?></div>
                    <div class="chalancolright" >Fee Month:<?php echo $student_info['fee_month']; ?></div>
                    <div class="chalancolleft">Issue Date: <?php echo $student_info['issue_date']; ?></div>
                    <div class="chalancolright"><strong>Due Date: <?php echo $student_info['due_date']; ?></strong></div>
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
                      } ?>      
                    </td>
                    </tr>
                    <?php }else{ ?>
                      <?php  $arialSum = $arialSum + ($fee_info['amount'] -  $fee_info['discount']); ?>
                    <?php } ?>
                    <?php
                    if($fee_info['is_monthly_fee'] == 1){
                     $total = ($total-$fee_info['discount']);
                     } ?>
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
                      <td></td>
                    </tr>
                    <?php } ?>
                    <?php } ?>
                    <?php foreach($student_info['fee_fine'] as  $value) { 
                      //print_r($value);
                      $total = ($total + $value['fine_amount']);
                      ?>
                     
                      <tr><td>Fine(<?php echo $value['fee_month']; ?>)</td><td><?php echo $value['fine_amount']; ?></td><td></td></tr>

                  
                   <?php } ?>
                    <?php foreach($student_info['fee_fine'] as  $value) { 
                      $total = ($total + $value['fine_amount']);
                      ?>
                      <tr><td>Fine(<?php echo $value['fee_month']; ?>)</td><td><?php echo $value['fine_amount']; ?></td><td></td></tr>
                     <?php } ?>
                    <tr>
                    <td><strong>Payable Within Due Date</strong></td>
                      <td><strong><?php echo $total; ?>/-</strong></td>
                      <td></td>
                    </tr>
                    <?php
                     $late_fee_fine = 0;
                     if($student_info['fine_type'] == 'per_day_fine'){
                        $late_fee_fine = $student_info['late_fee_fine']*15;
                     }else{
                        $late_fee_fine = $student_info['late_fee_fine'];
                     }
                    if($fine_after_due_date == 1){ ?>
                    <tr><td>Late Fee Fine </td><td><?php echo $late_fee_fine; ?>/-</td><td></td></tr>
                    <tr>
                    <td><strong>Payable After Due Date</strong></td>
                      <td><strong><?php echo $total+$late_fee_fine; ?>/-</strong></td>
                      <td></td>
                    </tr>
                    <?php } ?>
                  </table>
                  <br />
                  <div style="text-align:left;margin-left: 5px;"><?php echo $student_info['chalan_f_msg']; ?></div>
                </div>
                 <?php if($show_line1 == 1){ ?>
                  <div style="float:left;width:98%; border-bottom:1px solid;margin-top:20px;"><?php echo $footer_line1; ?>&nbsp;&nbsp;</div>
                <?php } ?>
                <?php if($show_line2 == 1){ ?>
                <div style="float:left;width:98%; border-bottom:1px solid;margin-top:20px;margin-bottom: 20px;"><?php echo $footer_line2; ?>&nbsp;&nbsp;</div>
                <?php } ?>
              </div>
              <div style="width:32%; float:left; margin-left:1%;">
                   <div dir="rtl" lang="ur"> <?php echo $student_info['chalan_h_msg']; ?></div>
                <div class="chalanwrapper">
                  <div class="row">
                    <div class="col-sm-2 ml-2 mt-2"></div>
                    <div class="col-sm-8" style="font-weight:bold;">Student Copy</div><br />
                    <img style="width: 95%;margin-left: 8px;" src="<?php echo base_url();?>system-logo/<?php echo $student_info['chalan_header']; ?>">
                    <!-- <div class="col-sm-3 ml-2 mt-2"><img style="width: 100%;" src="<?php echo base_url();?>system-logo/<?php echo $student_info['logo']; ?>"></div> -->
                    <!-- <div class="col-sm-8" ><span style="font-weight:bold;font-size: 16px;"><?php echo $student_info['system_name']; ?></span><br />
                    <?php echo $student_info['campus_name']; ?>, <?php echo $student_info['location']; ?></div> -->
                  </div>  
                  <div class="ml-2 mt-2" style="text-align: left;">
                  <?php 
                    // if($student_info['bank_name']){
                    //   echo $student_info['bank_name'].', '; 
                    // }
                  ?>
                  <?php 
                    // if($student_info['bank_address']){
                    // echo $student_info['bank_address'].', ';
                    // }
                  ?> 
                  <?php //if($student_info['bank_code']){ echo $student_info['bank_code']; } ?>
                  <!-- <br /> -->
                  <?php //if($student_info['bank_acc']){ ?>
                    <!-- Account No: <?php //echo $student_info['bank_acc']; ?><br /> -->
                  <?php } ?>
                  </div>
                  <div class="feeinfo" >
                    <div class="chalanrows"> Chalan# <?php echo $student_info['chalan_no']; ?> <span style="float: right;margin-right: 10px;">Family# : <?php echo $student_info['family_no']; ?></span><span style="float:right;margin-right: 10px;">Reg# : <?php echo $student_info['reg_no']; ?></span></div>
                    <div class="chalanrows"> Name: <?php echo $student_info['student_name']; ?></div>
                    <div class="chalanrows">Father Name: <?php echo $student_info['f_name']; ?></div>
                    <div class="chalancolleft"><?php echo $student_info['class_name']; ?></div>
                    <div class="chalancolright" >Fee Month:<?php echo $student_info['fee_month']; ?></div>
                    <div class="chalancolleft">Issue Date: <?php echo $student_info['issue_date']; ?></div>
                    <div class="chalancolright"><strong>Due Date: <?php echo $student_info['due_date']; ?></strong></div>
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
                      } ?>      
                    </td>
                    </tr>
                    <?php }else{ ?>
                      <?php  $arialSum = $arialSum + ($fee_info['amount'] -  $fee_info['discount']); ?>
                    <?php } ?>
                    <?php
                    if($fee_info['is_monthly_fee'] == 1){
                     $total = ($total-$fee_info['discount']);
                     } ?>
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
                      <td></td>
                    </tr>
                    <?php } ?>
                    <?php } ?>
                    <?php foreach($student_info['fee_fine'] as  $value) { 
                      //print_r($value);
                      $total = ($total + $value['fine_amount']);
                      ?>
                      <tr><td>Fine(<?php echo $value['fee_month']; ?>)</td><td><?php echo $value['fine_amount']; ?></td><td></td></tr>
                   <?php } ?>
                    <?php foreach($student_info['fee_fine'] as  $value) { 
                      $total = ($total + $value['fine_amount']);
                      ?>
                      <tr><td>Fine(<?php echo $value['fee_month']; ?>)</td><td><?php echo $value['fine_amount']; ?></td><td></td></tr>
                     <?php } ?>
                    <tr>
                    <td><strong>Payable Within Due Date</strong></td>
                      <td><strong><?php echo $total; ?>/-</strong></td>
                      <td></td>
                    </tr>
                    <?php
                     $late_fee_fine = 0;
                     if($student_info['fine_type'] == 'per_day_fine'){
                        $late_fee_fine = $student_info['late_fee_fine']*15;
                     }else{
                        $late_fee_fine = $student_info['late_fee_fine'];
                     }
                    if($fine_after_due_date == 1){ ?>
                    <tr><td>Late Fee Fine </td><td><?php echo $late_fee_fine; ?>/-</td><td></td></tr>
                    <tr>
                    <td><strong>Payable After Due Date</strong></td>
                      <td><strong><?php echo $total+$late_fee_fine; ?>/-</strong></td>
                      <td></td>
                    </tr>
                    <?php } ?>
                  </table>
                  <br />
                  <div style="text-align:left;margin-left: 5px;"><?php echo $student_info['chalan_f_msg']; ?></div>
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
        <?php } ?>
        <?php } ?>
         
      </div>
      <!-- /.box-body -->
    </div>
    <!-- /.box -->
  </div>
  </div>
  </div>
</section>
<!-- /.content -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/0.9.0rc1/jspdf.min.js"></script>
