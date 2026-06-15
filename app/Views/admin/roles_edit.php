<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style type="text/css">
ul.ztree {margin-top: 10px;overflow-y:none;overflow-x:auto;}
.ztree li span.button.add {margin-left:2px; margin-right: -1px; background-position:-144px 0; vertical-align:top; *vertical-align:middle}
.role-perm-search-wrap { max-width: 420px; margin-bottom: 10px; }
.role-perm-search-wrap .form-control { border-radius: 8px; }
#rolePermSearchHint { display: block; margin-top: 4px; }
</style>
<link rel="stylesheet" href="<?php echo base_url();?>resource/ztree/css/zTreeStyle/zTreeStyle.css" />
<script type="text/javascript" src="<?php echo base_url();?>resource/ztree/js/jquery.ztree.core.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>resource/ztree/js/jquery.ztree.excheck.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>resource/ztree/js/jquery.ztree.exhide.js"></script>
<?php 
	if(isset($info)){
		$header = 'Edit Role';
		$id = $info->id;
		$role_name_id = $info->role_name_id;
		$plan_id = $info->plan_id;
		$action = 'edit';
	}else{
		$header = 'Add Role';
		$id = '';
		$role_name_id = '';
		$plan_id = '';
		$rPerms = array();
		$action = 'add';
	}
?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>
          Roles
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Roles</li>
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
			<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/roles') ?>">Roles</a></li>
		<?php if($id == ''){ ?>
			<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/roles/add') ?>"><?php echo $header;?></a></li>
		<?php }else{ ?>
			<li class="nav-item"><a class="nav-link active" href="<?php echo '#/roles?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>
		<?php  } ?>
		</ul>
	<div class="card-body">	
	<div class="tab-content">
	<?php 
		echo form_open('c=roles&m=save', 'role="form" id="role-edit-form"');
		echo form_hidden('id', $id);
	?>
	<div class="form-group">
	<label for="roleName">Role Name</label>
	<select class="form-control" name="role_name_id">
	<?php foreach($rolenameinfo as $roleinfo){  ?>
		<option <?php if($roleinfo->role_name_id == $role_name_id){ ?> selected <?php } ?> value="<?php echo $roleinfo->role_name_id; ?>"><?php echo $roleinfo->rolename; ?></option>
	<?php } ?> 
	</select> 
	</div>
	<div class="form-group">
	<label for="roleName">Plan Name</label>
	<select class="form-control" name="plan_id">
	<?php foreach($systemplansinfo as $planinfo){  ?>
		<option <?php if($planinfo->plan_id == $plan_id){ ?> selected <?php } ?> value="<?php echo $planinfo->plan_id; ?>"><?php echo $planinfo->plan_name; ?></option>
	<?php } ?> 
	</select> 
	</div>			
	<div class="form-group">
	<label for="">Role Permissions</label>
	<div class="role-perm-search-wrap">
		<div class="input-group input-group-sm">
			<span class="input-group-text"><i class="fas fa-search"></i></span>
			<input type="search" class="form-control" id="rolePermSearchInput" placeholder="Search name or permission key…" autocomplete="off">
			<button type="button" class="btn btn-outline-secondary btn-sm" id="rolePermSearchClear">Clear</button>
		</div>
		<small class="text-muted" id="rolePermSearchHint"></small>
	</div>
	All Node <a href="javascript:;" id="expandAllBtn">expand</a> | <a href="javascript:;" id="collapseAllBtn">collapse</a> | <a href="javascript:;" class="checkall" data-type="1">All Allow</a> | <a href="javascript:;" class="checkall" data-type="0">All Deny</a> | <a href="javascript:;" class="checkall" data-type="x">All Ignore</a>
	<ul id="treeDemo" class="ztree"></ul>
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
	</section>
<!-- /.content -->
<script type="text/javascript">
<!--
var IDMark_Switch = "_switch",
IDMark_Icon = "_ico",
IDMark_Span = "_span",
IDMark_Input = "_input",
IDMark_Check = "_check",
IDMark_Edit = "_edit",
IDMark_Remove = "_remove",
IDMark_Ul = "_ul",
IDMark_A = "_a";
var setting = {
	check:{
	// enable:true
},
view:{
	addDiyDom:addDiyDom
},
edit:{
},
async: {
enable:true,
url:'<?php echo base_url('admin/roles/perm_data'); ?>',
autoParam:[],
otherParam:{'action':'<?php echo $action;?>', 'roleid':'<?php echo $id;?>'}
},
callback:{
	onAsyncSuccess: function(event, treeId, treeNode, msg) {
		if (!treeNode) {
			applyRoleZTreeSearch($('#rolePermSearchInput').val());
		}
	}
}
};

function applyRoleZTreeSearch(raw) {
	var zTree = $.fn.zTree.getZTreeObj('treeDemo');
	if (!zTree) return;
	var q = (raw || '').trim().toLowerCase();
	var nodes = zTree.transformToArray(zTree.getNodes());
	var i, node, p, j, sub;
	if (!q) {
		for (i = 0; i < nodes.length; i++) {
			zTree.showNode(nodes[i]);
		}
		$('#rolePermSearchHint').text('');
		return;
	}
	for (i = 0; i < nodes.length; i++) {
		zTree.hideNode(nodes[i]);
	}
	var matchCount = 0;
	for (i = 0; i < nodes.length; i++) {
		node = nodes[i];
		var name = (node.name || '').toLowerCase();
		var key = (node.permKey || '').toLowerCase();
		if (name.indexOf(q) === -1 && key.indexOf(q) === -1) {
			continue;
		}
		matchCount++;
		zTree.showNode(node);
		p = node.getParentNode();
		while (p) {
			zTree.showNode(p);
			zTree.expandNode(p, true, false, false);
			p = p.getParentNode();
		}
		sub = zTree.transformToArray(node);
		for (j = 0; j < sub.length; j++) {
			zTree.showNode(sub[j]);
		}
	}
	$('#rolePermSearchHint').text(matchCount ? ('Matched ' + matchCount + ' node(s)') : 'No matching permissions.');
}

var rolePermSearchTimer = null;

function addDiyDom(treeId, treeNode){
	var aObj = $('#' + treeNode.tId + IDMark_A);
	var diyStr = "<select name=\"perm_" + treeNode.id + "\"><option value=\"1\" " + (treeNode.chk == '1' ? 'selected="selected"' : '') + ">Allow</option><option value=\"0\" " + (treeNode.chk == '0' ? 'selected="selected"' : '') + ">Deny</option><option value=\"x\" " + (treeNode.chk == 'x' ? 'selected="selected"' : '') + ">Ignore</option></select>";
	aObj.after(diyStr);
}

$(document).ready(function(){
	$.fn.zTree.init($("#treeDemo"), setting);
	$('#expandAllBtn').on('click', function(){
	var zTree = $.fn.zTree.getZTreeObj('treeDemo');
	zTree.expandAll(true);
});

$('#collapseAllBtn').on('click', function(){
	var zTree = $.fn.zTree.getZTreeObj('treeDemo');
	zTree.expandAll(false);
});

$('#rolePermSearchInput').on('input', function(){
	var v = $(this).val();
	clearTimeout(rolePermSearchTimer);
	rolePermSearchTimer = setTimeout(function(){
		applyRoleZTreeSearch(v);
	}, 200);
});
$('#rolePermSearchClear').on('click', function(){
	$('#rolePermSearchInput').val('');
	applyRoleZTreeSearch('');
});
});

//-->
</script>  
<script type="text/javascript">
$(function(){
	$(document).on('click', '.checkall', function(){
	var type = $(this).attr('data-type');
	$('select[name*="perm_"]').each(function(i, n){
	switch(type){
		case '1':
		$(n).val('1');
		break;
		case '0':
		$(n).val('0');				
		break;
		case 'x':
		$(n).val('x');				
		break;
	}
});
});	

$('#role-edit-form').validate({
	
});	
$('#role-edit-form').ajaxForm({
	beforeSubmit:function(formData, jqForm, options){
	//return $('#role-edit-form').valid();
},
success:function(responseText, statusText, xhr, form){
	var json = $.parseJSON(responseText);
	if(json.success){
	toastr.success(json.msg);
<?php  if($id == ''){ ?>
	location.href = '#/roles';
<?php }else{ ?>
	location.href = '#/roles?m=edit&id=<?php echo $id;?>&after=edit';
<?php  } ?>				
}else{
 toastr.error(json.msg);
}
	return false;
}
});			
});
</script>

<?= $this->endSection() ?>