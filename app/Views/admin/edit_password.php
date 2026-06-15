<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Change Password',
    'icon' => 'fas fa-lock',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Users', 'url' => base_url('admin/users')],
        ['label' => 'Change Password', 'active' => true],
    ],
]) ?>
    <!-- Main content -->
    <section class="content">
      <div class="row">
      <div class="col-lg-12">
		  <div class="card card-primary card-outline card-tabs">
       <div class="card-header p-0 pt-1 border-bottom-0">
				<ul class="nav nav-tabs">
				<li class="nav-item"><a class="nav-link" href="<?php echo '#/users?status=1';?>">Users</a></li>
				<li class="nav-item"><a class="nav-link" href="<?php echo '#/users?m=edit&id=' . $user_id;?>">Edit User</a></li>
				<li class="nav-item"><a class="nav-link active" href="<?php echo '#/users?m=edit_password&user_id=' . $user_id;?>">Change Password</a></li>			
			</ul>
			<div class="card-body">	
			<div class="tab-content">
			<?php 
				echo form_open('c=users&m=edit_password', 'role="form" id="user-edit-form"');
				echo form_hidden('user_id', $user_id);
			?>
                <div class="form-group">
                  <label for="password">New Password</label>
                  <input type="password" class="form-control" name="password" id="password">
                </div>
                <div class="form-group">
                  <label for="confirm_password">Confirm New Password</label>
                  <input type="password" class="form-control" name="confirm_password" id="confirm_password">
                </div>
              <div class="form-group">
                <button type="submit" class="btn btn-primary">Save</button>
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
	$('#user-edit-form').validate({
		rules:{
			password:{
				required:true,
				minlength:6
			},
			confirm_password:{
				required:true,
				equalTo:'#password'
			}
		},
		messages:{
			password:{
				required:'New Password Is Required',
				minlength:'At Least 6 chars'
			},
			confirm_password:{
				required:'Confirm New Password is required',
				equalTo:'Two Password do not match'
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
				location.href = '<?php echo '#/users?status=1';?>';
			}else{
				toastr.error(json.msg);
			}
			return false;
		}
	});			
});
</script>

<?= $this->endSection() ?>