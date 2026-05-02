<html dir="rtl" lang="ur">
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>
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
 <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-4">
            <h1>
               Fee Chalan     
            </h1>
          </div>
          <div class="col-sm-4"><a  class="btn btn-primary" href="<?php echo '#/fee_chalan_pay';?>">Click here to pay fee</a></div>
          <div class="col-sm-4">
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
      <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
        <?php foreach($data  as $student_info){ ?>
        <div class="card-body pagebreak">  
        <div class="tab-content" >
            <div class="" style="width:100%;" id="printarea">
              <div style="width:32%; float:left; margin-left:1%;">
                <div dir="rtl" lang="ur"> <?php echo $student_info['chalan_h_msg']; ?></div>
                <div class="chalanwrapper">
                  <div class="row">
                    <div class="col-sm-3 ml-2 mt-2"></div>
                    <div class="col-sm-8" style="font-weight:bold;">Bank Copy</div><br />
                    <div class="col-sm-3 ml-2 mt-2"><img style="width: 100%;" src="<?php echo base_url();?>system-logo/<?php echo $student_info['logo']; ?>"></div>
                    <div class="col-sm-8"><?php echo $student_info['system_name']; ?><br />
                    <?php echo $student_info['campus_name']; ?>, <?php echo $student_info['location']; ?></div>
                  </div>  
                 <div class="ml-2 mt-2" style="text-align: left;">
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
                  <div  class="feeinfo">
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
                      <td>Total Payable</td>
                        <td><?php echo $total; ?>/-</td>
                        <td></td>
                      </tr>
                    </table>
                    <br />
                    <!-- <div style="text-align:center; padding-top:50px;"><strong>Note: </strong>After Due Date Rs. 10/day fine will be charged</div> -->
                    <div style="text-align:left;margin-left: 5px;"><?php echo $student_info['chalan_f_msg']; ?></div>
<?= view('admin/chalanview/partials/chalan_accounts_disclaimer') ?>
                  </div>
                  </div>
                  <div style="width:32%; float:left; margin-left:1%;">
                  <div dir="rtl" lang="ur"> <?php echo $student_info['chalan_h_msg']; ?></div>
                  <div class="chalanwrapper"> 
                    <div class="row">
                      <div class="col-sm-3 ml-2 mt-2"></div>
                      <div class="col-sm-8" style="font-weight:bold;">School Copy</div><br />
                      <div class="col-sm-3 ml-2 mt-2"><img style="width: 100%;" src="<?php echo base_url();?>system-logo/<?php echo $student_info['logo']; ?>"></div>
                      <div class="col-sm-8"><?php echo $student_info['system_name']; ?><br />
                      <?php echo $student_info['campus_name']; ?>, <?php echo $student_info['location']; ?></div>
                    </div>  
                    <div class="ml-2 mt-2" style="text-align: left;">
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
                    <div class="chalanrows"> Chalan# <?php echo $student_info['chalan_no']; ?></div>
                    <div class="chalanrows"> Name: <?php echo $student_info['student_name']; ?>
                    </div>
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
      <tr><td>Fine(<?php echo $value['fee_month']; ?>)</td><td><?php 
        $value['fine_amount'];
      ?></td><td></td></tr>
<?php } ?>
<tr>
<td>Total Payable</td>
<td><?php echo $total; ?>/-</td>
<td></td>
</tr>
</table>
<br />
<div style="text-align:left;margin-left: 5px;"><?php echo $student_info['chalan_f_msg']; ?></div>
<?= view('admin/chalanview/partials/chalan_accounts_disclaimer') ?>
</div>
</div>
<div style="width:32%; float:left; margin-left:1%;">
<div dir="rtl" lang="ur"> <?php echo $student_info['chalan_h_msg']; ?></div>
<div class="chalanwrapper">
<div class="row">
  <div class="col-sm-3 ml-2 mt-2"></div>
  <div class="col-sm-8" style="font-weight:bold;">Student Copy</div><br />
  <div class="col-sm-3 ml-2 mt-2"><img style="width: 100%;" src="<?php echo base_url();?>system-logo/<?php echo $student_info['logo']; ?>"></div>
  <div class="col-sm-8"><?php echo $student_info['system_name']; ?><br />
  <?php echo $student_info['campus_name']; ?>, <?php echo $student_info['location']; ?></div>
</div>  
<div class="ml-2 mt-2" style="text-align: left;">
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
  <td>Total Payable</td>
  <td><?php echo $total; ?>/-</td>
  <td></td>
</tr>
</table>
<br />
<div style="text-align:left;margin-left: 5px;"><?php echo $student_info['chalan_f_msg']; ?></div>
<?= view('admin/chalanview/partials/chalan_accounts_disclaimer') ?>
</div>
</div>
</div>
</div>
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
<script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript">
$(function(){
var table = $('#users-datatable').DataTable({
deferRender: true,
select:{
style:'single',
blurable: true
},
ajax:{
url:'<?php echo site_url('c=fee_chalan&m=data');?>',
type:'post',
data:function(d){
//d.csrf_test_name = $.cookie(CSRF_COOKIE_NAME);
}
},
columns:[
{
data:'id',
className:'select-checkbox',
render:function(data, type, row){
	return data;
}
},
{data:'fee_name'},
{data:'student_id'},
{data:'due_date'},
{data:'issue_date'},
{data:'fee_month'},
{data:'amount'},
{data:'discount'},
{data:'status'},
{data:'paiddate'},
{
data:'id',
sortable:false,
render:function(data, type, row){
	var html = '';
	html += '<div class="btn-group">';
		html += '<a href="<?php echo '#/fee_chalan?m=edit&id=';?>' + data + '" title="edit" class="btn btn-default btn-xs"><i class="fa fa-pencil icon-pencil"></i></a>';
		if(row.issys == '1'){
    }else{
      html += '<a href="javascript:;" onclick="del_confirm(\'notice\', \'Are you sure delete this record\', \'<?php echo site_url('c=users&m=delete&id=');?>' + data + '\',\'users-datatable\');" title=" delete" class="btn btn-default btn-xs"><i class="fa fa-trash icon-trash"></i></a>';
    }
      html += '</div>';
	     return html;
}
}
],
fnDrawCallback:function(oSettings){
$(".switchchk").bootstrapSwitch({
onSwitchChange:function(e, state){
var fieldval = state;
var $element = $(e.currentTarget);
var tablename = $element.attr('data-table');
var fieldname = $element.attr('data-field');
var rowid = $element.attr('data-pk');
if(fieldval){
	fieldval = 1;
}else{
	fieldval = 0;
}
$.post(
   "<?php echo site_url('c=ajax&m=setboolattribute');?>",
   {
	   act:'upsort',
	   tbname:tablename,
	   tbfield:fieldname,
	   tbfieldvalue:fieldval,
	   id:rowid//,
	   // csrf_test_name:$.cookie(CSRF_COOKIE_NAME)
   },
   function(data){
	//alert(data);
	   if(data=='success'){
		   toastr.success('change success');
	   }else{
		   toastr.error('change error');
	   }
  });
}
});
}
});
});
</script>
<?= $this->endSection() ?>