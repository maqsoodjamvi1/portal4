<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){
		$header = 'Edit Fee Chalan';
		$id = $info->chalan_id;
		
		$issue_date = DateTime::createFromFormat('Y-m-d',$info->issue_date);
		$issue_date = $issue_date->format('d/m/Y');
		
		$due_date = DateTime::createFromFormat('Y-m-d',$info->due_date);
		$due_date = $due_date->format('d/m/Y');

		$fee_month = $info->fee_month;
		$amount = $info->amount;
		$discount = $info->discount;

	}else{
		$header = 'Add Generate Fee Chalan';
		$id = '';
		$due_date = '';
		$issue_date = '';
		$fee_month = '';
		$amount = '';
		$discount = '';

	}
?>
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>
               Generate Fee Chalan
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Generate Fee Chalan</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
		  <div class="card card-primary card-outline card-tabs">
          <div class="card-header p-0 pt-1 border-bottom-0">
			<ul class="nav nav-tabs">
			<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/fee_chalan') ?>">Fee Chalan</a></li>
			<?php if($id == ''){ ?>
			<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/fee_chalan/add') ?>"><?php echo $header;?></a></li>
			<?php }else{ ?>
			<li class="nav-item"><a class="nav-link active" href="<?php echo base_url('admin/fee_chalan/edit?id=') . $id;?>"><?php echo $header;?></a></li>
			<?php } ?>
			</ul>
			<div class="card-body">
			<div class="tab-content">
			<?php
			echo form_open( base_url('admin/fee_chalan/save'), 'role="form" onSubmit="return confirm("Do you want to submit?") " id="user-edit-form"');
			echo form_hidden('id', $id);
			?>		
			<div class="form-group">
	        <label>Issue Date:</label>
	        <div class="input-group date">
	          <div class="input-group-addon">
	            <i class="fa fa-calendar"></i>
	          </div>
	          <input type="text" class="form-control pull-right" id="datepicker2"  readonly="readonly"  value="<?php echo $issue_date; ?>" name="issue_date">
	        </div>
	        <!-- /.input group -->
	    </div>	
			<div class="form-group">
          <label>Due Date:</label>
	        <div class="input-group date">
            <div class="input-group-addon">
              <i class="fa fa-calendar"></i>
            </div>
            <input type="text" class="form-control pull-right" id="datepicker"  readonly="readonly" value="<?php echo $due_date; ?>" name="due_date">
          </div>
          <!-- /.input group -->
      </div>			  						
			<div class="form-group">
	          <label>Fee Month:</label>
	          <div class="input-group date">
	            <div class="input-group-addon">
	              <i class="fa fa-calendar"></i>
	            </div>
	            <input type="text" class="form-control pull-right" id="datetimepicker10" value="<?php echo $fee_month; ?>"  name="fee_month">
				   	<input type="hidden" id="originalfee_month" value="">
      </div>
        <!-- /.input group -->
      </div>
			<div class="form-group">
          <label>Amount:</label>
            <input type="text" class="form-control pull-right" id="amount" value="<?php echo $amount; ?>"  name="amount">
			    <!-- /.input group -->
      </div>	
			<br /> <br />		
			<div class="form-group">
          <label>Discount:</label>
            <input type="text" class="form-control pull-right" id="discount" value="<?php echo $discount; ?>"  name="discount">
				  <!-- /.input group -->
      </div>			
 			<br /> <br />
      <div class="form-group">
        <button type="submit" id="submitBtn"  class="btn btn-primary">Save</button>
				<button type="reset" class="btn btn-default">Reset</button>
				<button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
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
            $('#datepicker10').datepicker({
              format: "mm/yyyy",
			    		startView: "year", 
			   			minView: "year"
            });
        });
    </script>
	<script type="text/javascript">
	$(function(){
	  //Date picker
	    $('#datepicker').datepicker({
	       format: 'dd/mm/yyyy'
	    })
		 $('#datepicker2').datepicker({
	        format: 'dd/mm/yyyy'
	    })
	<?php if($id == ''){ ?>
	$('#user-edit-form').validate({
		rules:{
			fee_month:{
			required:true,
			remote:{
				param:{
				url:'<?php echo base_url('admin/ajax/check_fee_month&table=fee_chalan&field=fee_month'); ?>'
				},
				depends:function(element){
					var id = $(element).attr('id');
					return ($(element).val() !== $('#original' + id).val());
				}
			}
			}
			},
			messages:{
			fee_month:{
				required:'fee month is Required',
				remote:'fee month is exists'
			}
			}
			
	});
	<?php }else{ ?>
	$('#user-edit-form').validate({	});
	<?php } ?>
	$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			return $('#user-edit-form').valid();
			$('#submitBtn').html("Saving");
      		$('#submitBtn').prop('disabled', true);
		},
		success:function(responseText, statusText, xhr, form){
			$('#submitBtn').html("Save");
      		$('#submitBtn').prop('disabled', false);
			var json = $.parseJSON(responseText);
			if(json.success){
				toastr.success(json.msg);
				<?php
				if($id == ''){
					?>
					location.href = '#/fee_chalan';
					<?php
				}else{
					?>
					location.href = '#/fee_chalan?m=edit&id=<?php echo $id;?>&after=edit';
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


	const es = new EventSource('/admin/bulk_chalan_stream?fee_type_ids=1,2,3&fee_month=2025-09&issue_date=18/08/2025&due_date=25/08/2025');

es.addEventListener('debug', (e) => {
  try {
    const msg = JSON.parse(e.data);
    const { tag, data, level } = msg;
    const prefix = `[SSE DEBUG] ${tag}`;
    if (level === 'error') {
      console.error(prefix, data || msg);
    } else {
      console.debug(prefix, data || msg);
    }
  } catch (err) {
    console.warn('[SSE DEBUG] parse error', err, e.data);
  }
});

es.onmessage = (e) => {
  try {
    const msg = JSON.parse(e.data);
    if (msg.type === 'progress') {
      console.log('[SSE PROGRESS]', msg);
      // update UI...
    } else if (msg.type === 'complete') {
      console.log('[SSE COMPLETE]', msg);
      es.close();
    } else if (msg.type === 'error') {
      console.error('[SSE ERROR]', msg);
    } else if (msg.type === 'debug') {
      // also catches debug if emitted as plain "message"
      console.debug('[SSE DEBUG message]', msg);
    } else {
      console.log('[SSE]', msg);
    }
  } catch (err) {
    console.warn('[SSE] parse error', err, e.data);
  }
};

es.onerror = (e) => {
  console.error('[SSE CONNECTION ERROR]', e);
};
</script>

<?= $this->endSection() ?>