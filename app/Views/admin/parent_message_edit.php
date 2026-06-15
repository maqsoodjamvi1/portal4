<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){

		$header = 'Edit Fee Message To Parents';
		$id = $info->enquiry_id;
		$name = $info->name;
		$email = $info->email;
		$contact = $info->contact;
		$address = $info->address;
		$description = $info->description;
		$date = $info->date;

	}else{
		$header = 'Add  Fee Message To Parents';
		$id = '';
		$name = '';
		$contact = '';
		$email = '';
		$address = '';
		$description = '';
		$date = '';
	}
?>
<?= view('components/page_header', [
    'title' => 'Fee Message To Parents',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Fee Message To Parents', 'active' => true],
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
		<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/defaulter_message') ?>">Defaulter Message</a></li>
		<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/defaulter_message/parent_sms') ?>">Unique sms to parent</a></li>
		<?php }else{ ?>
		<li class="nav-item"><a class="nav-link active" href="<?php echo '#/defaulter_message?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
		<?php } ?>
	</ul>
	<div class="card-body">
	<div class="tab-content">
	<?php
		echo form_open('c=defaulter_message&m=saveparent', 'role="form" id="user-edit-form"');
		echo form_hidden('id', $id);
	?>
	<div class="form-group" style="clear:both;">
     <label style="display: block;float: left;margin-right: 10px;"> <input type="checkbox" name="contacts[]" value="father_contact" class="form-control" required > Father Contact </label>
     <label style="display: block;float: left;margin-right: 10px;"> <input type="checkbox" name="contacts[]" value="mother_contact" class="form-control" required> Mother Contact </label>
     <label style="display: block;float: left;margin-right: 10px;"> <input type="checkbox" name="contacts[]" value="emergency_contact" class="form-control" required > Emergency Contact </label>
  </div>
			
		<div class="form-group" style="clear:both;">
      <label for="description">Message</label>
       <textarea class="form-control" name="message" id="message" ><?php echo $defaulter_fee_sms; ?></textarea>
	    	<input type="button" value="Father Name" onclick="formatText ('father_name');" /> 
	    	<input type="button" value="Date" onclick="formatText ('date');" /> 
    <input type="button" value="Balance" onclick="formatText ('balance');" /> 
		<script type="text/javascript">
			function formatText(tag) {
		   var Field = document.getElementById('message');
		   var val = Field.value;
		   var selected_txt = val.substring(Field.selectionStart, Field.selectionEnd);
		   var before_txt = val.substring(0, Field.selectionStart);
		   var after_txt = val.substring(Field.selectionEnd, val.length);
		   Field.value += '{' + tag + '}';
		}
		</script>
		</div>
			<div class="form-group">
				<?php
				foreach ($detaulterArr as $key => $value) {
						echo "<label class='col-lg-3 text-start'><input type='checkbox' name='parent_id[]' checked value='".$value['parent_id']."'  > ".$value['f_name']." (Unpaid Fee: ".$value['unpaid_fee'].")</label>";
				}
				 ?>
			</div>
		 
			
			<div class="form-group">
            <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
			<button type="reset" class="btn btn-secondary">Reset</button>
			<button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
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
	$('#datepicker').datetimepicker({
       format: 'YYYY-MM-DD'
     });
    $('#datepicker2').datetimepicker({
        format: 'YYYY-MM-DD'
    });
	$('#user-edit-form').validate({
		rules:{
			name:{
				required:true,
			}
		},
		messages:{
			name:{
				required:'Term is Required',	
			}
		}
	});
	$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			//return $('#user-edit-form').valid();
			$('#submitBtn').html("Saving!");
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
					location.href = '#/messages';
					<?php
				}else{
					?>
					location.href = '#/messages';
					<?php
				}
				?>
			}else{
				toastr.error(json.msg);
			}
			return false;
		}
	});
})
</script>

<?= $this->endSection() ?>