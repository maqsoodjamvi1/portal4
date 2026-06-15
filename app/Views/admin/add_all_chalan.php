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
    'title' => $header ?? 'Bulk Fee Chalan',
    'icon' => 'fas fa-layer-group',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Fee Chalan', 'url' => base_url('admin/fee_chalan')],
        ['label' => 'Bulk generate', 'active' => true],
    ],
]) ?>
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-lg-12">
		  <div class="card sms-card card-primary card-outline card-tabs">
          	<div class="card-header p-0 pt-1 border-bottom-0">
			<ul class="nav nav-tabs">
				<li class="nav-item"><a class="nav-link" href="<?php echo '#/fee_chalan_single?m=download&id='.$id.'&after=edit';?>">View Fee Chalan</a></li>
				<?php if($id == ''){ ?>
					<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/fee_chalan/add') ?>"><?php echo $header;?></a></li>
				<?php }else{ ?>
					<li class="nav-item"><a class="nav-link active" href="<?php echo '#/fee_chalan?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
				<?php } ?>
			</ul>
			<div class="card-body">
			<div class="tab-content">
				
			<?php
			
			echo form_open('c=fee_chalan_all&m=save', 'role="form" id="user-edit-form"');
			echo form_hidden('id', $id);
			?>
					
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
			<div class="form-group">
                <label>Fee Month:</label>
                <div class="">
                  <input type="month" class="form-control float-end" id="month" name="fee_month" value="<?php echo date('Y-m'); ?>" style="height: 24px;">
			    </div>
                <!-- /.input group -->
              </div>			
       		</div>	</div>
       		<div id="loadAllStudents"></div>
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
<style type="text/css">
table {
  text-align: left;
  position: relative;
  border-collapse: collapse; 
}
th, td {
  padding: 0.25rem;
}
tr.red th {
  background: red;
  color: white;
}
tr.green th {
  background: green;
  color: white;
}
tr.purple th {
  background: purple;
  color: white;
}
th {
  background: white;
  position: sticky;
  top: 0; /* Don't forget this, required for the stickiness */
  box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
}
</style>
<script type="text/javascript">
	$(function () {

		var dateNow = new Date();
	  	$('#datepicker2').datetimepicker({
		  format: 'DD/MM/YYYY',
		  defaultDate:dateNow
		});
	  
	  var d = new Date();
		d.setDate(d.getDate() + 10);
	  $('#datepicker').datetimepicker({
		  format: 'DD/MM/YYYY',
		  defaultDate:d
		});
		
    });
</script>
<script type="text/javascript">
$(function(){
	$.ajax({
        url: 'admin.php?c=fee_chalan_all&m=loadStudents', 
        type: "POST",
        data:{},
        success:function(res){
        	$("#loadAllStudents").html(res);
		  }
     });
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
				location.href = '#/fee_chalan';
				<?php }else{ ?>
				location.href = '#/fee_chalan_all?m=download&id=<?php echo $id;?>&after=edit';
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