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
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
        align-items: start;
    }
    .permission-item {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        justify-content: flex-start;
        gap: 8px;
        width: 100%;
        margin: 0;
        padding: 7px 9px;
        background: #fefefe;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
    }
    .permission-item:hover {
        background: #f8f9fa;
        border-color: #007bff;
    }
    .permission-item label {
        font-weight: 500;
        margin-bottom: 0;
        display: block;
        font-size: 13px;
    }
    .permission-main {
        flex: 1 1 auto;
        min-width: 0;
    }
    .perm-controls {
        flex: 0 0 auto;
        white-space: nowrap;
        display: flex;
        justify-content: flex-end;
    }
    .perm-choice-btn {
        min-width: 58px;
    }
    .perm-choice-btn.active {
        font-weight: 600;
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
    .perm-search-wrap {
        max-width: 420px;
        margin-bottom: 10px;
    }
    .perm-search-wrap .form-control {
        border-radius: 8px;
    }
    .permission-item.perm-nested { max-width: 100%; }
    .permission-item[data-depth="0"] {
        border-left: 3px solid #17a2b8;
    }
    .permission-item[data-depth="1"] {
        border-left: 3px solid #6f42c1;
    }
    .permission-item[data-depth="2"],
    .permission-item[data-depth="3"],
    .permission-item[data-depth="4"] {
        border-left: 3px solid #6c757d;
    }
    @media (max-width: 1600px) {
        .permission-group-body.show {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
    @media (max-width: 1200px) {
        .permission-group-body.show {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    @media (max-width: 768px) {
        .permission-group-body.show {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 560px) {
        .permission-group-body.show {
            grid-template-columns: 1fr;
        }
    }
    .permission-group.no-search-match {
        display: none !important;
    }
    .permission-item.no-search-match {
        display: none !important;
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
                        
                        <div class="role-info-box">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Editing Role:</strong> <span id="editingRoleNameText"><?= $current_role_name ?: '-' ?></span>
                            <span class="float-right">
                                <i class="fas fa-key"></i> Role ID: <span id="editingRoleIdText"><?= $id ?: 0 ?></span>
                            </span>
                            <div class="mt-2 text-muted" id="switchRoleHint">Change Role Name + Plan to load another existing role on this page.</div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> Role Permissions</label>
                            <div class="perm-search-wrap">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    </div>
                                    <input type="search" class="form-control" id="permSearchInput"
                                           placeholder="Search by permission name or key…" autocomplete="off">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" id="permSearchClear" title="Clear search">Clear</button>
                                    </div>
                                </div>
                                <small class="text-muted" id="permSearchHint"></small>
                            </div>
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
/** Always use the role row id from the form (editing), not role_name_id / plan. */
function getEditingRoleId() {
    var v = parseInt($('#roleId').val(), 10);
    return isNaN(v) ? 0 : v;
}

function hasUnsavedPermissionChanges() {
    return $('#roleForm').data('permDirty') === true;
}

function markPermissionDirty() {
    $('#roleForm').data('permDirty', true);
}

function clearPermissionDirty() {
    $('#roleForm').data('permDirty', false);
}

function updateRoleInfoBar(roleId) {
    var roleName = $('#role_name_id option:selected').text() || '-';
    $('#editingRoleNameText').text(roleName);
    $('#editingRoleIdText').text(roleId || 0);
}

function updateStats() {
    var total = 0;
    var allowed = 0;
    var denied = 0;
    
    $('input.perm-input').each(function() {
        total++;
        var val = $(this).val();
        if (val == '1') allowed++;
        else denied++;
    });
    
    $('#permStats').html('Total: ' + total + ' | <span class="text-success">Allow: ' + allowed + '</span> | <span class="text-danger">Deny: ' + denied + '</span>');
}

function loadPermissions(roleId) {
    roleId = parseInt(roleId, 10);
    if (isNaN(roleId)) {
        roleId = 0;
    }
    $('#permissionsContainer').html('<div class="loading-spinner"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Loading permissions...</p></div>');
    
    $.ajax({
        url: '<?= base_url('admin/roles/perm_data') ?>',
        type: 'POST',
        data: {
            roleid: roleId,
            action: roleId > 0 ? 'edit' : 'add',
            <?= json_encode(csrf_token()) ?>: <?= json_encode(csrf_hash()) ?>
        },
        dataType: 'json',
        success: function(data) {
            if ($.isArray(data) && data.length > 0) {
                renderPermissions(data);
                updateStats();
                clearPermissionDirty();
            } else if ($.isArray(data)) {
                $('#permissionsContainer').html('<div class="alert alert-warning">No permissions found in the system.</div>');
            } else {
                $('#permissionsContainer').html('<div class="alert alert-danger">Unexpected response loading permissions.</div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error, xhr.responseText);
            var msg = 'Failed to load permissions. Please refresh the page.';
            if (xhr.status === 403) {
                msg = 'Permission load was blocked (403). Check login / CSRF.';
            }
            $('#permissionsContainer').html('<div class="alert alert-danger">' + msg + '</div>');
        }
    });
}

function permSearchText(node) {
    return ((node.name || '') + ' ' + (node.permKey || '')).trim();
}

function escapeAttr(s) {
    if (!s) return '';
    return String(s).replace(/"/g, '&quot;').replace(/</g, '&lt;');
}

function renderPermissionItem(node, depth) {
    depth = depth || 0;
    var selectedValue = (node.chk == '1') ? '1' : '0';
    var pad = Math.min(depth, 2) * 8;
    var searchBlob = escapeAttr(permSearchText(node));
    var html = '<div class="permission-item perm-nested" data-depth="' + depth + '" data-perm-id="' + node.id + '" data-perm-search="' + searchBlob + '">';
    html += '<div class="permission-main" style="padding-left:' + pad + 'px">';
    html += '<label>' + (depth === 0 ? '<strong><i class="fas fa-cog"></i> ' : '') + escapeHtml(node.name) + (depth === 0 ? '</strong>' : '') + '</label>';
    if (node.permKey) {
        html += '<div class="perm-key">' + escapeHtml(node.permKey) + '</div>';
    }
    html += '</div>';
    html += '<div class="perm-controls">';
    html += '<input type="hidden" name="perms[' + node.id + ']" class="perm-input" value="' + selectedValue + '">';
    html += '<div class="btn-group btn-group-sm">';
    html += buildChoiceButton('1', selectedValue, 'Allow', 'success');
    html += buildChoiceButton('0', selectedValue, 'Deny', 'danger');
    html += '</div>';
    html += '</div>';
    html += '</div>';
    if (node.children && node.children.length > 0) {
        for (var k = 0; k < node.children.length; k++) {
            html += renderPermissionItem(node.children[k], depth + 1);
        }
    }
    return html;
}

function buildChoiceButton(value, selectedValue, text, color) {
    var active = (String(value) === String(selectedValue)) ? ' active' : '';
    return '<button type="button" class="btn btn-outline-' + color + ' perm-choice-btn' + active + '" data-value="' + value + '">' + text + '</button>';
}

function setPermissionChoice($item, value) {
    $item.find('input.perm-input').val(value);
    $item.find('.perm-choice-btn').removeClass('active');
    $item.find('.perm-choice-btn[data-value="' + value + '"]').addClass('active');
}

function setAllPermissionChoices(value) {
    $('.permission-item[data-perm-id]').each(function () {
        setPermissionChoice($(this), value);
    });
    markPermissionDirty();
    updateStats();
}

function bindPermissionChoiceEvents() {
    $('#permissionsContainer').off('click', '.perm-choice-btn').on('click', '.perm-choice-btn', function () {
        var $btn = $(this);
        var $item = $btn.closest('.permission-item[data-perm-id]');
        var value = $btn.attr('data-value');
        setPermissionChoice($item, value);
        markPermissionDirty();
        updateStats();
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
        } else {
            statusText = 'Deny';
            statusClass = 'badge-deny';
        }

        var groupSearch = escapeAttr(permSearchText(node));

        html += '<div class="permission-group" data-group-search="' + groupSearch + '">';
        html += '<div class="permission-group-header" onclick="toggleGroup(\'' + groupId + '\')">';
        html += '<div><i class="fas fa-folder-open"></i> ' + escapeHtml(node.name) + '</div>';
        html += '<span class="badge ' + statusClass + '">' + statusText + '</span>';
        html += '</div>';
        html += '<div id="' + groupId + '" class="permission-group-body">';
        html += renderPermissionItem(node, 0);
        html += '</div></div>';
    }

    $('#permissionsContainer').html(html);
    bindPermissionChoiceEvents();

    if (moduleCount > 0) {
        $('.permission-group-body').first().addClass('show');
    }
    applyPermissionSearch($('#permSearchInput').val());
}

var permSearchTimer = null;
function applyPermissionSearch(raw) {
    var q = (raw || '').trim().toLowerCase();
    var $groups = $('.permission-group');
    var $items = $('.permission-item[data-perm-search]');
    if (!q) {
        $groups.removeClass('no-search-match');
        $items.removeClass('no-search-match');
        $('#permSearchHint').text('');
        return;
    }
    var visibleGroups = 0;
    var visibleItems = 0;
    $groups.each(function () {
        var $g = $(this);
        var headerMatch = (($g.attr('data-group-search') || '').toLowerCase().indexOf(q) !== -1);
        var anyItem = false;
        $g.find('.permission-item[data-perm-search]').each(function () {
            var t = ($(this).attr('data-perm-search') || '').toLowerCase();
            var m = t.indexOf(q) !== -1;
            $(this).toggleClass('no-search-match', !m);
            if (m) anyItem = true;
        });
        var show = headerMatch || anyItem;
        $g.toggleClass('no-search-match', !show);
        if (show) {
            visibleGroups++;
            visibleItems += $g.find('.permission-item[data-perm-search]:not(.no-search-match)').length;
            $g.find('.permission-group-body').first().addClass('show');
        }
    });
    $('#permSearchHint').text(visibleItems ? ('Showing ' + visibleItems + ' permission(s) in ' + visibleGroups + ' group(s)') : 'No matching permissions.');
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

function switchToSelectedRole(forceSwitch) {
    var roleNameId = $('#role_name_id').val();
    var planId = $('#plan_id').val();
    if (!roleNameId) {
        return;
    }
    if (!forceSwitch && hasUnsavedPermissionChanges()) {
        var ok = window.confirm('You have unsaved permission changes. Switch role and discard those unsaved changes?');
        if (!ok) {
            return;
        }
    }
    $.ajax({
        url: '<?= base_url('admin/roles/get_role_by_name') ?>',
        type: 'POST',
        dataType: 'json',
        data: {
            role_name_id: roleNameId,
            plan_id: planId,
            <?= json_encode(csrf_token()) ?>: <?= json_encode(csrf_hash()) ?>
        },
        success: function(resp) {
            if (!resp || !resp.success || !resp.role_id) {
                $('#switchRoleHint').text('No existing role found for selected role name + plan.');
                return;
            }
            var newRoleId = parseInt(resp.role_id, 10);
            if (isNaN(newRoleId) || newRoleId <= 0) {
                $('#switchRoleHint').text('Invalid role selected.');
                return;
            }
            $('#roleId').val(newRoleId);
            updateRoleInfoBar(newRoleId);
            loadPermissions(newRoleId);
            $('#switchRoleHint').text('Loaded role #' + newRoleId + ' permissions.');
            if (window.history && window.history.replaceState) {
                window.history.replaceState({}, '', '<?= base_url('admin/roles/edit') ?>/' + newRoleId);
            }
        },
        error: function() {
            $('#switchRoleHint').text('Unable to load selected role. Please try again.');
        }
    });
}

$(document).ready(function() {
    clearPermissionDirty();
    updateRoleInfoBar(getEditingRoleId());
    loadPermissions(getEditingRoleId());
    
    $('#role_name_id, #plan_id').on('change', function() {
        switchToSelectedRole(false);
    });

    $('#permSearchInput').on('input', function () {
        clearTimeout(permSearchTimer);
        var v = $(this).val();
        permSearchTimer = setTimeout(function () {
            applyPermissionSearch(v);
        }, 200);
    });
    $('#permSearchClear').on('click', function () {
        $('#permSearchInput').val('');
        applyPermissionSearch('');
    });
    
    $('#allowAllBtn').click(function() {
        setAllPermissionChoices('1');
        toastr.success('All permissions set to Allow');
    });
    
    $('#denyAllBtn').click(function() {
        setAllPermissionChoices('0');
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
                    clearPermissionDirty();
                    setTimeout(function() {
                        var savedRoleId = parseInt(response.role_id || getEditingRoleId(), 10);
                        if (!isNaN(savedRoleId) && savedRoleId > 0) {
                            window.location.href = '<?= base_url('admin/roles/edit') ?>/' + savedRoleId;
                        } else {
                            window.location.href = '<?= base_url('admin/roles') ?>';
                        }
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