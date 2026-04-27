<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<style>
tr td:last-child{font-family: 'Satisfy';}
tr td:first-child{font-weight:bold;}
tr td{vertical-align: middle !important;}
p{margin-bottom: 0px !important;}
.table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th{
	padding: 4px 8px !important;
}
</style>
<?php 
if(isset($_GET['cls_sec_id'])){
  $cls_sec_id = $_GET['cls_sec_id'];
}else{
  $cls_sec_id = '';
}

?>
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>
               Datesheet
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Datesheet</li>
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
			     <ul class="nav nav-tabs">
					   <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/datesheet') ?>">Admit Card</a></li>
            <li  class="nav-item"><a  class="nav-link" href="<?= base_url('admin/datesheet/add') ?>">Add Datesheet</a></li>
				  </ul>
				<div class="card-body">
        <div class="col-lg-12">
    <form action="<?= base_url('admin/datesheet_without_syllabus') ?>" role="form" id="user-edit-form" method="get" accept-charset="utf-8">  
      <div class="row no-print">
        <div class="col-lg-5 form-group">
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
            <div class="col-sm-2"><input style="margin-top: 25px;" class="btn btn-primary" name="submit" type="submit" value="view"></div>
      </div>       
     </form>
  <div id="printJS-form">
  <?php
  if($data){
  foreach ($data as  $value) {  
   $termlastkey = $value['terms'];
   $examName = $termlastkey;
   ?>
  <page>
    <div style="border:1px dashed #000; border-radius:10px; text-align:center;height: 130px;" class="col-lg-12">
    <div  class="col-lg-3" style="float: left;width: 100px;">
  <?php if(!empty($value['profile_photo'])){ ?>
    <img style="width: 70px;margin-top: 16px;border-radius: 8px;height: 70px;" src="uploads/<?php echo $value['profile_photo']; ?>">
  <?php }else{ ?>
  <i style="font-size: 90px;text-align: center;display: block;margin-top: 16px;" class="fa fa-user"></i>
 <?php } ?>
	<!-- <img style="width: 70px;height:70px;margin-top: 10px; float: left;" src="uploads/logo_school.png"> --></div>
<div  class="col-lg-9" style="width: 700px;margin: 0 auto;">
<h1 style="margin-top:5px; font-size:50px; font-family:'Times New Roman', Times, serif;"><?php echo $value['campus_name']; ?></h1>
<h3 style="margin-top:5px;font-family: 'Orbitron';font-size: 22px;"><?php echo $examName." ".$value['campus_location']; ?> </h3>
 </div>
</div>
<div style="border:1px solid #000; float:left; width:100%; margin:10px auto;">
<div style="width:50%; padding-left:15px; border-bottom:1px solid #000; float:left;"> <strong>Name:</strong> <?php echo $value['name']; ?></div>
<div style="width:50%; padding-left:15px; border-bottom:1px solid #000; float:left;"><strong> Reg #:</strong> <?php echo $value['reg_no']; ?></div>
<div style="width:50%; padding-left:15px; border-bottom:1px solid #000; float:left;"> <strong>Father Name:</strong> <?php echo $value['f_name']; ?></div>
<div style="width:50%; padding-left:15px; border-bottom:1px solid #000; float:left;"> <strong>Grade:</strong> <?php echo $value['class']; ?></div>
<div style="width:50%; padding-left:15px;  float:left;"> <strong>Contact # 1:</strong> <?php echo $value['father_contact']; ?></div>
<div style="width:50%; padding-left:15px;  float:left;"> <strong>Contact # 2:</strong>  <?php echo $value['mother_contact']; ?></div>
</div>
<div style="border:2px solid #000; float:left; width:100%; margin:10px auto; padding:2px;">
<div style="border:2px solid #000;float:left;width:100%;text-align:center;font-weight:bold;padding: 5px;font-size: 18px;color: #000;line-height: 20px;">DATE SHEET</div>
<table class="table" style="margin-bottom: 2px;">
<thead>
<tr>
<th style="width: 10%; border-bottom:1px solid #000;">Date</th>
 <th style="width: 10%;border-bottom:1px solid #000;">Day</th>
 <th style="width: 10%;border-bottom:1px solid #000;">Subjects</th>
 <th style="width: 10%;border-bottom:1px solid #000;">Total Marks</th>
<!-- <th style="text-align:center;border-bottom:1px solid #000;">Exam Syllabus</th> -->
</tr>
</thead>
<tbody>
<?php foreach ($value['datesheetbysubject'] as $key => $valueNo){ ?>
<tr>
<?php foreach($valueNo as $numbers){ ?>
<td style="border-bottom:1px solid #000;"><?php echo $numbers; ?></td>
<?php } ?>
</tr>
<?php } ?>
</tbody>
</table>
</div></page><br><br><br><br>
<div style="clear: both;margin-bottom: 60px;"></div>
<p style="page-break-before: always;">&nbsp;</p>
<?php } ?>
<?php } ?>
</div>
 </div>
<!-- /.box-body -->
</div>
<!-- /.box -->
</div>
</div>
</div>
</div>
</section>
<!-- /.content -->
<script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>

<?= $this->endSection() ?>