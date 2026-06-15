<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
{
	$header = 'Add Slot';
	$id = '';
	$slot_name = '';
	$start_time = '';
	$end_time = '';
	$slot_type = '';
}
?>
<?= view('components/page_header', [
    'title' => 'Slots',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Slots', 'active' => true],
    ],
]) ?>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-lg-12">
	   <div class="card card-primary card-outline card-tabs">
	    <div class="card-header p-0 pt-1 border-bottom-0">
	    <ul class="nav nav-tabs">
			<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/slots') ?>">Slots</a></li>
			<?php if($id == ''){ ?>
			<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/slots/add') ?>"><?php echo $header;?></a></li>
			<?php }else{ ?>
			<li class="nav-item"><a class="nav-link active" href="<?php echo '#/slots?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
			<?php } ?>
		</ul>
<div class="card-body">		
<div class="tab-content">
<?php
	echo form_open('c=slots&m=save', 'role="form" id="classes-edit-form"');
	echo form_hidden('id', $id);
?>
<div class="col-lg-12">
	<div class="table-responsive">  
    <table class="table table-bordered" id="dynamic_field"> 
    <thead>
        <tr>
            <td><label for="subject_name">Slot Name</label></td>
            <td> <label for="subject_name">Start Time</label></td>
            <td> <label for="subject_name">End Time</label></td>
            <td> <label for="subject_name">Slot Type</label></td>
        </tr>
    </thead>	
		<?php 
		$i = 0;
		foreach ($info as $key => $value) { 
      // echo "<pre>";
      // print_r($value);
      // echo "</pre>";
    ?>
            <tr>  
            <td>
            	<input type="hidden" name="rowscount[]" value="1" />	
            	<input type="hidden" name="id<?php echo $i; ?>" value="<?php echo $value->slot_id; ?>">
            	<input type="text" name="slot_name<?php echo $i; ?>"  value="<?php echo $value->slot_name; ?>" placeholder="Slot Name" class="form-control name_list" required="" /></td>  
                 <td> <div class="input-group clockpicker" data-bs-placement="left" data-align="top" data-autoclose="true">
    			<input type="text" class="form-control" name="start_time<?php echo $i; ?>" placeholder="Start Time" value="<?php echo $value->start_time; ?>">
			    <span class="input-group-text btn btn-secondary">
			        <span class="far fa-clock"></span>
			    </span>
				</div></td>
				 <td> <div class="input-group clockpicker" data-bs-placement="left" data-align="top" data-autoclose="true">
    			<input type="text" class="form-control" name="end_time<?php echo $i; ?>" placeholder="End Time" value="<?php echo $value->end_time; ?>">
			    <span class="input-group-text btn btn-secondary">
			        <span class="far fa-clock"></span>
			    </span>
				</div></td>
                <td>
                	<select class="form-control"  name="slot_type<?php echo $i; ?>">
                		<option <?php if($value->slot_type == 'FullDay'){ ?> selected="selected"
                			<?php } ?> value="FullDay">FullDay</option>
                		<option <?php if($value->slot_type == 'HalfDay'){ ?> selected="selected"
                			<?php } ?> value="HalfDay">HalfDay</option>
                	</select>
                </td> 
            </tr>
            <?php $i++ ?>  
            <?php } ?>
            <tr>    <td></td><td></td> <td><button type="button" name="add" id="add" class="btn btn-success">Add More</button></td>  </tr>
        </table>  
    </div>
	 
</div>	

<div class="row">
 <div class="col-lg-12">
    <div class="form-group">
        <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
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
</section>
<!-- /.content -->
<script type="text/javascript">
$(document).ready(function(){      
      var i= <?php echo $i; ?>;  
      //alert(i);
   
      $('#add').click(function(){  
        
           $('#dynamic_field').append('<tr id="row'+i+'" class="dynamic-added"><td><input type="hidden" name="id'+i+'" value="0"><input type="hidden" name="rowscount[]" value="1" /><input type="text" name="slot_name'+i+'" placeholder="Slot Name" class="form-control name_list" required /></td><td> <div class="input-group clockpicker2" data-bs-placement="left" data-align="top" data-autoclose="true"><input type="text" class="form-control" name="start_time'+i+'" placeholder="Start Time" value=""><span class="input-group-text"><span class="far fa-clock"></span></span></div></td> <td> <div class="input-group clockpicker2" data-bs-placement="left" data-align="top" data-autoclose="true"><input type="text" class="form-control" name="end_time'+i+'" placeholder="End Time" value=""><span class="input-group-text"><span class="far fa-clock"></span></span></div></td><td><select class="form-control"  name="slot_type'+i+'"><option value="FullDay">FullDay</option><option  value="HalfDay">HalfDay</option></select></td><td><button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove btn-sm">X</button></td></tr>'); 
              i++;   
              $(".clockpicker2").clockpicker(); 
      });
  
      $(document).on('click', '.btn_remove', function(){  
           var button_id = $(this).attr("id");   
           $('#row'+button_id+'').remove();  
      }); 
      
  
 }); 

$(function(){
	$(".clockpicker").clockpicker();
	$('#classes-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
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
					location.href = '#/slots';
					<?php
				}else{
					?>
					location.href = '#/slots?m=edit&id=<?php echo $id;?>&after=edit';
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
</script>

<?= $this->endSection() ?>