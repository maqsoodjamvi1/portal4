<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){
		$header = 'Edit Campus Chalan';
		$id = $info->campus_id;
		$campus_name = $info->campus_name;
		
	}else{
		$header = 'Add Campus Chalan';
		$id = '';
		$chalan_type_name = '';
		$chalan_type_detail = '';
	}
?>
    <?= view('components/page_header', [
    'title' => 'Campus Chalan',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Campus Chalan', 'active' => true],
    ],
]) ?>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-lg-12">
		  <div class="card card-primary card-outline card-tabs">
          	<div class="card-header p-0 pt-1 border-bottom-0">
			<ul class="nav nav-tabs">
				<?php if($id == ''){ ?>
					<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/campus_chalan') ?>"><?php echo $header;?></a></li>
				<?php }else{ ?>
					<li class="nav-item"><a class="nav-link active" href="<?php echo '#/campus_chalan?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
				<?php } ?>
				<li class="nav-item"><a class="nav-link" href="<?php echo '#/campus_chalan_single?m=download&id='.$id.'&after=edit';?>">View Campus Chalan</a></li>
				<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/campus_chalan_pay') ?>">Pay Campus Chalan</a></li>
			</ul>
			<div class="card-body">
			<div class="tab-content">
				<div class="row">
					<div class="col-lg-12">
						<p class="page-header">Campus chalan of <?php echo $campus_name;?></p>
					</div>
				</div>
			<?php	
			echo form_open('c=campus_chalan&m=save', 'role="form" id="user-edit-form"');
			echo form_hidden('campus_id', $id);
			?>
				<div class="row">
				<?php if (! empty($bill_plans_data)) {
					foreach ($bill_plans_data as $bill_plans_value) { ?>
						<div class="col-lg-12">
							<h4>
								<input required name="plan_id" value="<?= (int) $bill_plans_value['plan_id'] ?>" type="radio">
								<?= esc($bill_plans_value['plan_name']) ?>
							</h4>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Amount</label>
								<input type="text" class="form-control" name="bill_amount_<?= (int) $bill_plans_value['plan_id'] ?>" value="<?= esc($bill_plans_value['amount']) ?>" required />
							</div>
						</div>
					<?php }
				} ?>
			</div>		
			<div class="row">	
        	  <div class="col-lg-4">
				<div class="form-group">
                <label>Issue Date:</label>
                <div class="input-group date" id="datepicker2" data-target-input="nearest">
                	<input type="text" class="form-control datetimepicker-input" data-bs-target="#datepicker2"  name="issue_date" required value=""/>
                	<span class="input-group-text" data-bs-target="#datepicker2" data-bs-toggle="datetimepicker"><i class="fa fa-calendar"></i></span>
              	</div>
                <!-- /.input group -->
              </div>				
			  </div>
			   <div class="col-lg-4">      
				<div class="form-group">
                <label>Due Date:</label>
                <div class="input-group date" id="datepicker" data-target-input="nearest">
                	<input type="text" class="form-control datetimepicker-input" data-bs-target="#datepicker"  name="due_date" required value=""/>
                	<span class="input-group-text" data-bs-target="#datepicker" data-bs-toggle="datetimepicker"><i class="fa fa-calendar"></i></span>
              	</div>
                <!-- /.input group -->
              </div>
			  </div>
			  <div class="col-lg-4">			
						
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
  	$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			//return $('#user-edit-form').valid();
		},
		success:function(responseText, statusText, xhr, form){
			var json = $.parseJSON(responseText);
			if(json.success){
				toastr.success(json.msg);
				<?php if($id == ''){ ?>
				location.href = '#/campus_chalan_pay';
				<?php }else{ ?>
				location.href = '#/campus_chalan_pay';
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