<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	$schoolinfo = getSchoolInfo();
	if(isset($info)){
		//print_r($info);
		$header = 'Edit Campus Plan';
		$id = $info->campus_id;	
		$plan_id = $campus_bills_info->plan_id;
		$install_id = $campus_bills_info->install_id;
		$max_students = $campus_bills_info->max_students;
		$max_fee = $campus_bills_info->max_fee;	
		$late_fee_fine = $info->late_fee_fine;
		$fee_issue_date = $info->fee_issue_date;
		$fee_due_date = $info->fee_due_date;

		$plan_id = $campus_bills_info->plan_id;
		$install_id = $campus_bills_info->install_id;
		$max_students = $campus_bills_info->max_students;
		$max_fee = $campus_bills_info->max_fee;

	}else{
		$header = 'Add Campus Plan';
		$id = '';
		$plan_id = '';
		$install_id = '';
		$max_students = '';
		$max_fee = '';
		$expiry = '';
		$fee_issue_date = '';
		$fee_due_date = '';
	}
?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>
          Campus Plan
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Campus Plan</li>
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
	<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/campus_plans') ?>">Campus Plan</a></li>
	<?php if($id == ''){ ?>
	<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/campus_plans/add') ?>"><?php echo $header;?></a></li>
	<?php }else{ ?>
	<li class="nav-item"><a class="nav-link active" href="<?php echo '#/campus_plans?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
	<?php } ?>
</ul>
<div class="card-body">
<div class="tab-content">
	<?php
		echo form_open('c=campus_plans&m=save', 'role="form" id="campus-edit-form"');
		echo form_hidden('id', $id);
		echo form_hidden('system_id', $schoolinfo->system_id);
	?>
	<div class="row">
		<div class="form-inline">
		<div class="form-group" style="float: left;margin-left: 8px;">
          <label for="location">Max Number Of Students</label>
          <select name="max_students" id="max_students" class="form-control" required>
          		<option value="300">0-300</option>
          		<option value="500">301-500</option>
          		<option value="1000">501-1000</option>
          </select>
		</div>
		<div class="form-group" style="float: left;margin-left: 15px;">
         <label for="location">Max Student Fee</label>
      <select name="max_fee" id="max_fee" class="form-control" required>
      		<option  value="5000">5000</option>
      </select>
		</div>
		<div class="form-group" style="float: left;margin-left: 15px;"> 
	        <input type="button" value="Calculate Package" id="calculatePackage" class="btn btn-success btn-sm" style="margin-top: 0px;height: 35px;">
	    </div>
		</div>
		<div class="col-lg-12">
			<br>
       <div id="packagePrice">
       </div>
	   </div>	
		<div class="form-group col-lg-12">
          <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
		  <button type="reset" class="btn btn-default">Reset</button>
		  <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
        </div>
        </div>
       <?php echo form_close();?>
	</div>
</div>
</div>
</div>
</div>
</div>
</section>
    <!-- /.content -->
<script type="text/javascript">
$(function(){
	$('#max_students').select2();
	$('#max_fee').select2();	
	$('#campus-edit-form').validate({
		rules:{
			campus_name:{
				required:true,
			}
		},
		messages:{
			campus_name:{
				required:'campus is Required',
			}
		}
	});
	$('#campus-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			return $('#campus-edit-form').valid();
			$('#submitBtn').html("Ajax Request is Processing!");
      		$('#submitBtn').prop('disabled', true);
		},
		success:function(responseText, statusText, xhr, form){
			$('#submitBtn').html("Submit");
      		$('#submitBtn').prop('disabled', false);
			var json = $.parseJSON(responseText);
			if(json.success){
				toastr.success(json.msg);
				<?php
				if($id == ''){
					?>
					location.href = '#/campus_plans';
					<?php
				}else{
					?>
					location.href = '#/campus_plans?m=edit&id=<?php echo $id;?>&after=edit';
					<?php
				}
				?>
			}else{
				toastr.error(json.msg);
			}
			return false;
		}
	});
});
$("#calculatePackage").click(function(){
        var max_fee = $('#max_fee').val();
        var max_students = $('#max_students').val();
         $.ajax({
            url: '<?php echo base_url('admin/campus/get_packages'); ?>',
            type: "POST",
            data:{max_fee:max_fee,max_students:max_students},
            success:function(res){
 			   $("#packagePrice").html(res);
			 }
         });
    });
</script>

<?= $this->endSection() ?>