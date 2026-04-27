<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
    /* Your styles here (same as before) */
    .permission-group {
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: #fff;
    }
    .permission-group-header {
        background: #f8f9fa;
        padding: 12px 15px;
        font-weight: bold;
        cursor: pointer;
        border-bottom: 1px solid #ddd;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .permission-group-header:hover {
        background: #e9ecef;
    }
    .permission-group-header i {
        margin-right: 8px;
    }
    .permission-group-header .badge {
        background: #007bff;
        color: white;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 11px;
    }
    .permission-group-body {
        padding: 15px;
        display: none;
        background: #fff;
        max-height: 500px;
        overflow-y: auto;
    }
    .permission-group-body.show {
        display: block;
    }
    .permission-item {
        display: inline-block;
        width: 280px;
        margin: 5px;
        padding: 8px;
        background: #fefefe;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        vertical-align: top;
    }
    .permission-item:hover {
        background: #f8f9fa;
        border-color: #007bff;
    }
    .permission-item label {
        font-weight: 500;
        margin-bottom: 5px;
        display: block;
        font-size: 13px;
    }
    .permission-item select {
        width: 100%;
        font-size: 12px;
        padding: 4px 8px;
    }
    .perm-key {
        font-size: 10px;
        color: #999;
        margin-top: 5px;
        word-break: break-all;
    }
    .loading-spinner {
        text-align: center;
        padding: 40px;
    }
    .action-buttons {
        margin-bottom: 15px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 4px;
    }
    .stats-info {
        display: inline-block;
        margin-left: 10px;
        font-size: 12px;
        color: #666;
    }
    .badge-allow {
        background: #28a745;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 10px;
        margin-left: 5px;
    }
    .badge-deny {
        background: #dc3545;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 10px;
        margin-left: 5px;
    }
    .badge-ignore {
        background: #6c757d;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 10px;
        margin-left: 5px;
    }
    .role-info-box {
        background: #e3f2fd;
        border-left: 4px solid #2196f3;
        padding: 10px 15px;
        margin-bottom: 15px;
        border-radius: 4px;
    }
</style>

<?php 
$header = isset($role) ? 'Edit Role' : 'Add Role';
$id = isset($role) ? $role->id : 0;
$role_name_id = isset($role) ? $role->role_name_id : '';
$plan_id = isset($role) ? $role->plan_id : '';
$current_role_name = '';

// Get current role name if editing
if (isset($role) && $role->role_name_id && isset($role_names)) {
    foreach ($role_names as $rn) {
        if ($rn->role_name_id == $role->role_name_id) {
            $current_role_name = $rn->rolename;
            break;
        }
    }
}
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-user-tag"></i> <?= $header ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/roles') ?>">Roles</a></li>
                    <li class="breadcrumb-item active"><?= $header ?></li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Role Information</h3>
                </div>
                
                <div class="card-body">
                    <form id="roleForm" method="post" action="<?= base_url('admin/roles/save') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" id="roleId" value="<?= $id ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role_name_id">Role Name <span class="text-danger">*</span></label>
                                    <select class="form-control" name="role_name_id" id="role_name_id" required>
                                        <option value="">Select Role Name</option>
                                        <?php if (isset($role_names) && !empty($role_names)): ?>
                                            <?php foreach ($role_names as $rn): ?>
                                                <option value="<?= $rn->role_name_id ?>" 
                                                    <?= ($role_name_id == $rn->role_name_id) ? 'selected' : '' ?>>
                                                    <?= $rn->rolename ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="plan_id">Plan</label>
                                    <select class="form-control" name="plan_id" id="plan_id">
                                        <option value="">No Plan</option>
                                        <?php if (isset($plans) && !empty($plans)): ?>
                                            <?php foreach ($plans as $plan): ?>
                                                <option value="<?= $plan->plan_id ?>" 
                                                    <?= ($plan_id == $plan->plan_id) ? 'selected' : '' ?>>
                                                    <?= $plan->plan_name ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($id > 0 && $current_role_name): ?>
                        <div class="role-info-box">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Editing Role:</strong> <?= $current_role_name ?>
                            <span class="float-right">
                                <i class="fas fa-key"></i> Role ID: <?= $id ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> Role Permissions</label>
                            <div class="action-buttons">
                                <button type="button" class="btn btn-sm btn-success" id="allowAllBtn">
                                    <i class="fas fa-check-double"></i> Allow All
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" id="denyAllBtn">
                                    <i class="fas fa-times-circle"></i> Deny All
                                </button>
                                <button type="button" class="btn btn-sm btn-info" id="expandAllBtn">
                                    <i class="fas fa-expand-alt"></i> Expand All
                                </button>
                                <button type="button" class="btn btn-sm btn-info" id="collapseAllBtn">
                                    <i class="fas fa-compress-alt"></i> Collapse All
                                </button>
                                <span class="stats-info" id="permStats"></span>
                            </div>
                            <div id="permissionsContainer">
                                <div class="loading-spinner">
                                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                                    <p>Loading permissions...</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save"></i> Save Role
                            </button>
                            <a href="<?= base_url('admin/roles') ?>" class="btn btn-default">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
var currentRoleId = <?= $id ?>;

function updateStats() {
    var total = 0;
    var allowed = 0;
    var denied = 0;
    var ignored = 0;
    
    $('select[name*="perms["]').each(function() {
        total++;
        var val = $(this).val();
        if (val == '1') allowed++;
        else if (val == '0') denied++;
        else if (val == 'x') ignored++;
    });
    
    $('#permStats').html('Total: ' + total + ' | <span class="text-success">Allow: ' + allowed + '</span> | <span class="text-danger">Deny: ' + denied + '</span> | <span class="text-secondary">Ignore: ' + ignored + '</span>');
}

function loadPermissions(roleId) {
    $('#permissionsContainer').html('<div class="loading-spinner"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Loading permissions...</p></div>');
    
    $.ajax({
        url: '<?= base_url('admin/roles/perm_data') ?>',
        type: 'POST',
        data: { 
            roleid: roleId, 
            action: roleId > 0 ? 'edit' : 'add'
        },
        dataType: 'text',
        success: function(response) {
            try {
                var cleanResponse = response.trim();
                var data = eval('(' + cleanResponse + ')');
                
                if (data && data.length > 0) {
                    renderPermissions(data);
                    updateStats();
                } else {
                    $('#permissionsContainer').html('<div class="alert alert-warning">No permissions found in the system.</div>');
                }
            } catch(e) {
                console.error('Parse error:', e);
                $('#permissionsContainer').html('<div class="alert alert-danger">Error parsing permissions data. Please refresh the page.</div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            $('#permissionsContainer').html('<div class="alert alert-danger">Failed to load permissions. Please refresh the page.</div>');
        }
    });
}

function renderPermissions(data) {
    var html = '';
    var moduleCount = 0;
    
    for (var i = 0; i < data.length; i++) {
        var node = data[i];
        var groupId = 'group_' + node.id;
        moduleCount++;
        
        var statusText = '';
        var statusClass = '';
        if (node.chk == '1') {
            statusText = 'Allow';
            statusClass = 'badge-allow';
        } else if (node.chk == '0') {
            statusText = 'Deny';
            statusClass = 'badge-deny';
        } else {
            statusText = 'Ignore';
            statusClass = 'badge-ignore';
        }
        
        html += '<div class="permission-group">';
        html += '<div class="permission-group-header" onclick="toggleGroup(\'' + groupId + '\')">';
        html += '<div><i class="fas fa-folder-open"></i> ' + escapeHtml(node.name) + '</div>';
        html += '<span class="badge ' + statusClass + '">' + statusText + '</span>';
        html += '</div>';
        html += '<div id="' + groupId + '" class="permission-group-body">';
        
        var selectedValue = node.chk || 'x';
        html += '<div class="permission-item">';
        html += '<label><strong><i class="fas fa-cog"></i> ' + escapeHtml(node.name) + '</strong></label>';
        html += '<select name="perms[' + node.id + ']" class="form-control perm-select" onchange="updateStats()">';
        html += '<option value="1" ' + (selectedValue == '1' ? 'selected' : '') + '>✓ Allow</option>';
        html += '<option value="0" ' + (selectedValue == '0' ? 'selected' : '') + '>✗ Deny</option>';
        html += '<option value="x" ' + (selectedValue == 'x' ? 'selected' : '') + '>○ Ignore</option>';
        html += '</select>';
        if (node.permKey) {
            html += '<div class="perm-key">' + escapeHtml(node.permKey) + '</div>';
        }
        html += '</div>';
        
        if (node.children && node.children.length > 0) {
            for (var j = 0; j < node.children.length; j++) {
                var child = node.children[j];
                var childValue = child.chk || 'x';
                html += '<div class="permission-item">';
                html += '<label>' + escapeHtml(child.name) + '</label>';
                html += '<select name="perms[' + child.id + ']" class="form-control perm-select" onchange="updateStats()">';
                html += '<option value="1" ' + (childValue == '1' ? 'selected' : '') + '>✓ Allow</option>';
                html += '<option value="0" ' + (childValue == '0' ? 'selected' : '') + '>✗ Deny</option>';
                html += '<option value="x" ' + (childValue == 'x' ? 'selected' : '') + '>○ Ignore</option>';
                html += '</select>';
                if (child.permKey) {
                    html += '<div class="perm-key">' + escapeHtml(child.permKey) + '</div>';
                }
                html += '</div>';
            }
        }
        
        html += '</div></div>';
    }
    
    $('#permissionsContainer').html(html);
    
    if (moduleCount > 0) {
        $('.permission-group-body').first().addClass('show');
    }
}

function toggleGroup(groupId) {
    $('#' + groupId).toggleClass('show');
}

function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

$(document).ready(function() {
    loadPermissions(currentRoleId);
    
    $('#allowAllBtn').click(function() {
        $('select[name*="perms["]').val('1');
        updateStats();
        toastr.success('All permissions set to Allow');
    });
    
    $('#denyAllBtn').click(function() {
        $('select[name*="perms["]').val('0');
        updateStats();
        toastr.success('All permissions set to Deny');
    });
    
    $('#expandAllBtn').click(function() {
        $('.permission-group-body').addClass('show');
    });
    
    $('#collapseAllBtn').click(function() {
        $('.permission-group-body').removeClass('show');
    });
    
    $('#roleForm').submit(function(e) {
        e.preventDefault();
        
        if (!$('#role_name_id').val()) {
            toastr.error('Please select a role name');
            $('#role_name_id').focus();
            return false;
        }
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: '<?= base_url('admin/roles/save') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.msg);
                    setTimeout(function() {
                        window.location.href = '<?= base_url('admin/roles') ?>';
                    }, 1500);
                } else {
                    if (response.errors) {
                        $.each(response.errors, function(key, value) {
                            toastr.error(value);
                        });
                    } else {
                        toastr.error(response.msg || 'Failed to save role');
                    }
                    $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Save Role');
                }
            },
            error: function() {
                toastr.error('Failed to save role');
                $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Save Role');
            }
        });
    });
});
</script>

<?= $this->endSection() ?>