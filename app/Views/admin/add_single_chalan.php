<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){
		$header = 'Edit Fee Chalan';
		$id = $info->student_id;
		$first_name = $info->first_name;
		$last_name = $info->last_name;
		$discounted_amount = $info->discounted_amount;

	}else{
		$header = 'Add Fee Chalan';
		$id = '';
		$chalan_type_name = '';
		$chalan_type_detail = '';
	}
?>
    <?= view('components/page_header', [
    'title' => 'Fee Chalan',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Fee Chalan', 'active' => true],
    ],
]) ?>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-lg-12">
		  <div class="card card-primary card-outline card-tabs">
          	<div class="card-header p-0 pt-1 border-bottom-0">
			<ul class="nav nav-tabs">
				<li class="nav-item"><a class="nav-link" href="<?php echo base_url('admin/fee-chalan-single/download?id='.$id.'&after=edit'); ?>">View Fee Chalan</a></li>
				<?php if($id == ''){ ?>
					<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/fee_chalan/add') ?>"><?php echo $header;?></a></li>
				<?php }else{ ?>
					<li class="nav-item"><a class="nav-link active" href="<?php echo base_url('admin/fee_chalan/edit?id=') . $id;?>"><?php echo $header;?></a></li>
				<?php } ?>
				<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/fee_chalan_pay') ?>">Pay Fee Chalan</a></li>
			</ul>
			<div class="card-body">
			<div class="tab-content">
				<div class="row">
					<div class="col-lg-12">
						<p class="page-header">Fee chalan of <?php echo $info->first_name." ".$info->last_name;?></p>
					</div>
				</div>
			<?php	
			echo form_open( base_url('admin/fee-chalan-single/save'), 'role="form" id="user-edit-form"');
			echo form_hidden('id', $id);
			?>
				<div class="row">
				<?php if(isset($fee_type_info)){
					foreach ($fee_type_info as  $fee_type_value) { ?>
						<div class="col-lg-3"> <div class="form-group"><label><input  type="checkbox" name="fee_type_name[]" value="<?php echo $fee_type_value['fee_type_id']; ?>" checked="checked" required>
						</label> <?php if($fee_type_value['is_monthly_fee'] == 1){ echo $fee_type_value['fee_type_name']; ?>
						<!-- <small>Regular Discount (<?php echo ($discounted_amount); ?>)</small> -->
						<input type="text" class="form-control" name="fee_amount[<?php echo $fee_type_value['fee_type_id']; ?>]" value="<?php echo $fee_type_value['amount']; ?>"   />
						<br />
						<?php }else{ ?>
					<?php echo $fee_type_value['fee_type_name']; ?><input type="text" class="form-control" name="fee_amount[<?php echo $fee_type_value['fee_type_id']; ?>]" value="<?php echo $fee_type_value['amount']; ?>" required />
						<?php } ?>
						</div></div>
						<?php } ?>
				<?php } ?>
			</div>		
			<div class="row">	
       <div class="col-lg-3">
				<div class="form-group">
                <label>Issue Date:</label>
                <div class="input-group date" id="datepicker2" data-target-input="nearest">
                	<input type="text" class="form-control datetimepicker-input" data-bs-target="#datepicker2"  name="issue_date" required value=""/>
                	<span class="input-group-text" data-bs-target="#datepicker2" data-bs-toggle="datetimepicker"><i class="fa fa-calendar"></i></span>
              	</div>
                <!-- /.input group -->
              </div>				
			  </div>
			   <div class="col-lg-3">      
				<div class="form-group">
                <label>Due Date:</label>
                <div class="input-group date" id="datepicker" data-target-input="nearest">
                	<input type="text" class="form-control datetimepicker-input" data-bs-target="#datepicker"  name="due_date" required value=""/>
                	<span class="input-group-text" data-bs-target="#datepicker" data-bs-toggle="datetimepicker"><i class="fa fa-calendar"></i></span>
              	</div>
                <!-- /.input group -->
              </div>
			  </div>
			  <div class="col-lg-3">			
			<div class="form-group">
                <label>Fee Month:</label>
                <div class="">
                  <input type="month" class="form-control float-end" id="" value="<?php echo  date('Y-m'); ?>" name="fee_month" style="height: 24px;">
				   <input type="hidden" id="originalfee_month" value="">
                </div>
                <!-- /.input group -->
              </div>			
       		</div>	</div>
			<div class="row">
            <div class="col-lg-12">  <div class="form-group">
                <button type="submit"  id="submitBtn" class="btn btn-primary">Save</button>
				<button type="reset" class="btn btn-secondary">Reset</button>
				<button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
              </div>
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
	$(function () {

		$('#datepicker2').datetimepicker({
		  format: 'DD/MM/YYYY',
		  defaultDate:'now'
		});

		var myDate = new Date(new Date().getTime()+(10*24*60*60*1000));
		
	  $('#datepicker').datetimepicker({
		  format: 'DD/MM/YYYY',
		  defaultDate:myDate

		});

		$('#datetimepicker10').datetimepicker({
            viewMode: 'years',
            format: 'MM/YYYY',
        });
    });
	
</script>
<script type="text/javascript">
$(function(){
  	$('#user-edit-form').validate({
		rules:{
			fee_month:{
				required:true,
			}
			},
			messages:{
			fee_month:{
				required:'fee month is Required',
				remote:'fee month is exists'
			}
			}
	});
	$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			return $('#user-edit-form').valid();
		},
		success:function(responseText, statusText, xhr, form){
			var json = $.parseJSON(responseText);
			if(json.success){
				toastr.success(json.msg);
				<?php if($id == ''){ ?>
				location.href = '/admin/fee_chalan_pay';
				<?php }else{ ?>
				location.href = '/admin/fee_chalan_single/download?id=<?php echo $id;?>&after=edit';
				<?php } ?>
			}else{
				toastr.error(json.msg);
			}
			return false;
		}
	});
});
</script>

<?= $this->endSection() ?>