<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>
               Top Level Planning  Grade Wise
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Top Level Planning  Grade Wise</li>
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
				<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/top_level_planning') ?>">Top Level Planning</a></li>
				<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/top_level_planning/add') ?>">Add Top Level Planning</a></li>
				<li  class="nav-item"><a class="nav-link active" href="<?= base_url('admin/top_level_planning_gradewise') ?>">Grade Wise View</a></li>
			</ul>
	<div class="card-body">
	<div class="col-lg-12">
	<?php foreach ($data as  $value) { ?>
	<page>
	<div style="border:2px solid #000; float:left; width:100%; margin:10px auto; padding:2px;">
		<div style="width:100%;border:2px solid #000;float:left;width:100%;text-align:center;font-weight:bold;padding: 5px;font-size: 18px;color: #000;line-height: 20px;">Top Level Planning (<?php echo $value['session_name']; ?>)
	<div style="width:100%;padding-left:15px;float:left;font-size: 16px;font-weight: normal;margin-top: 0px;">  <?php echo $value['class']; ?></div>
		</div>
				<table class="table" style="margin-bottom: 2px;">
				<thead>
				<tr><th style="width: 5%;border:1px solid #000;">Subject</th>
				  <?php foreach($value['terms'] as $term){ ?>
				  <th style="border:1px solid #000;width: 15.83%;"> <?php echo $term['terms_name']; ?></th>
				 <?php } ?>
				</tr>
				</thead>
				<tbody>
			<?php foreach ($value['result'] as $key => $valueNo) { ?>
				<tr>
				<td style="border:1px solid #000;font-size: 10px;width: 5%;"><?php echo $key; ?></td>
				<?php 
				 $emptycol = (count($value['terms'])-count($valueNo)); 
				if($emptycol >0){	 
					for($i=1; $i<=$emptycol; $i++){
						 echo '<td style="padding:5px;font-size: 10px;width: 5%;">0</td>';
						}
				}
			?>
			<?php foreach($valueNo as $numbers){ ?>
				<td style="width: 15.83%;border:1px solid #000;font-size: 11px;<?php if($key == 'Urdu' || $key == 'Islamiat' || $key == 'Nazra'){ ?> direction: rtl; <?php } ?>"><?php echo $numbers; ?></td> <?php } ?>

				</tr>
				<?php } ?>

				</tbody>
				</table>
				
				</div></page><br><br><br><br>
	         <div style="clear: both;margin-bottom: 60px;"></div>
	          <p style="page-break-before: always;">&nbsp;</p>
				<?php //exit; ?>

	         <?php } ?>
			  </div>
			    <!-- /.box-body -->
	          </div>
	          <!-- /.box -->
	        </div>
	      </div>
	    </section>
    <!-- /.content -->
<style type="text/css">
   	tr th:first-child{
   	max-width:20px;padding: 0px; margin:0px; 	
   	}
   	tr th:first-child input[type="text"]{
   		width: 10px !important
   	}
   </style>  	
 <script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript">
$(function(){

});
</script>

<?= $this->endSection() ?>