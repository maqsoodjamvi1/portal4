<?php
$uiNeedsDataTables = false;
$uiNeedsSummernote = false;
?>
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
        border-start: 4px solid #2196f3;
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
        border-start: 3px solid #17a2b8;
    }
    .permission-item[data-depth="1"] {
        border-start: 3px solid #6f42c1;
    }
    .permission-item[data-depth="2"],
    .permission-item[data-depth="3"],
    .permission-item[data-depth="4"] {
        border-start: 3px solid #6c757d;
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
    .menu-access-section {
        margin-bottom: 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: #fff;
    }
    .menu-access-section-header {
        background: #f8f9fa;
        padding: 10px 14px;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #eee;
        gap: 12px;
    }
    .menu-access-section-title {
        flex: 1;
        cursor: pointer;
        min-width: 0;
    }
    .menu-access-section-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }
    .menu-access-section-actions .form-switch {
        padding-left: 2.25rem;
    }
    .menu-save-status {
        font-size: 12px;
        color: #6c757d;
        margin-left: 8px;
    }
    .menu-save-status.is-saving {
        color: #007bff;
    }
    .menu-save-status.is-saved {
        color: #28a745;
    }
    .menu-save-status.is-error {
        color: #dc3545;
    }
    .menu-access-section-body {
        display: none;
        padding: 8px 12px 12px;
    }
    .menu-access-section-body.show {
        display: block;
    }
    .menu-access-header-row {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #6c757d;
        padding: 8px 4px 4px;
        letter-spacing: 0.04em;
    }
    .menu-access-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 8px 6px;
        border-bottom: 1px solid #f0f0f0;
    }
    .menu-access-item:last-child {
        border-bottom: none;
    }
    .menu-access-item-label {
        flex: 1;
        min-width: 0;
        font-size: 13px;
    }
    .menu-access-item-label i {
        width: 18px;
        text-align: center;
        margin-right: 6px;
        color: #6c757d;
    }
    .menu-access-shared {
        display: block;
        font-size: 10px;
        color: #856404;
        margin-top: 2px;
    }
    .menu-access-section.no-search-match,
    .menu-access-item.no-search-match {
        display: none !important;
    }
</style>

<?php 
$header = isset($role) ? 'Edit Role' : 'Add Role';
$id = isset($role) ? $role->id : 0;
$role_name_id = isset($role) ? $role->role_name_id : '';
helper('role');
$plan_id = isset($plan_id) ? (int) $plan_id : getRolePlanId();
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

<?= view('components/page_header', [
    'title' => $header,
    'icon' => 'fas fa-user-tag',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Roles', 'url' => base_url('admin/roles')],
        ['label' => $header, 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Role Information</h3>
                </div>
                
                <div class="card-body">
                    <form id="roleForm" class="needs-validation" novalidate method="post" action="<?= base_url('admin/roles/save') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" id="roleId" value="<?= $id ?>">
                        
                        <div class="row">
                            <div class="col-md-12">
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
                            
                        </div>

                        <input type="hidden" name="plan_id" id="plan_id" value="<?= (int) $plan_id ?>">

                        <div class="role-info-box">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Editing Role:</strong> <span id="editingRoleNameText"><?= $current_role_name ?: '-' ?></span>
                            <span class="float-end">
                                <i class="fas fa-key"></i> Role ID: <span id="editingRoleIdText"><?= $id ?: 0 ?></span>
                            </span>
                            <div class="mt-2 text-muted" id="switchRoleHint">Change Role Name to load another existing role on this page.</div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> Role Permissions</label>
                            <ul class="nav nav-tabs mb-3" id="permTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="menu-access-tab" data-bs-toggle="tab" href="#menuAccessPanel" role="tab">
                                        <i class="fas fa-bars"></i> Menu Access
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="advanced-perm-tab" data-bs-toggle="tab" href="#advancedPermPanel" role="tab">
                                        <i class="fas fa-key"></i> Advanced Permissions
                                    </a>
                                </li>
                            </ul>
                            <div class="tab-content" id="permTabContent">
                                <div class="tab-pane fade show active" id="menuAccessPanel" role="tabpanel">
                                    <p class="text-muted small mb-2">Toggle sidebar menu items this role can see. Changes save automatically — no Save button needed.</p>
                                    <div class="perm-search-wrap">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            <input type="search" class="form-control" id="menuSearchInput"
                                                   placeholder="Search menu items…" autocomplete="off">
                                            <button type="button" class="btn btn-outline-secondary" id="menuSearchClear">Clear</button>
                                        </div>
                                        <small class="text-muted" id="menuSearchHint"></small>
                                    </div>
                                    <div class="action-buttons">
                                        <button type="button" class="btn btn-sm btn-success" id="menuShowAllBtn">
                                            <i class="fas fa-eye"></i> Show All Menus
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning" id="menuHideAllBtn">
                                            <i class="fas fa-eye-slash"></i> Hide All Menus
                                        </button>
                                        <button type="button" class="btn btn-sm btn-info" id="menuExpandAllBtn">
                                            <i class="fas fa-expand-alt"></i> Expand All
                                        </button>
                                        <button type="button" class="btn btn-sm btn-info" id="menuCollapseAllBtn">
                                            <i class="fas fa-compress-alt"></i> Collapse All
                                        </button>
                                        <span class="stats-info" id="menuStats"></span>
                                        <span class="menu-save-status" id="menuSaveStatus"></span>
                                    </div>
                                    <div id="menuAccessContainer">
                                        <div class="loading-spinner">
                                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                                            <p>Loading menu access...</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="advancedPermPanel" role="tabpanel">
                                    <div class="perm-search-wrap">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            <input type="search" class="form-control" id="permSearchInput"
                                                   placeholder="Search by permission name or key…" autocomplete="off">
                                            <button type="button" class="btn btn-outline-secondary" id="permSearchClear" title="Clear search">Clear</button>
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
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <a href="<?= base_url('admin/roles') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Roles
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
var menuCatalogData = [];
var menuItemsIndex = {};
var menuEnabledState = {};
var menuSyncLock = false;
var menuSaveTimer = null;
var menuSaveInFlight = false;
var menuSaveQueued = false;
var menuAccessHasOverrides = false;
var menuPermissionsReady = false;
var permSaveTimer = null;
var permSaveInFlight = false;

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
            <?= json_encode(csrf_token()) ?>: $('input[name="<?= csrf_token() ?>"]').val() || <?= json_encode(csrf_hash()) ?>
        },
        dataType: 'json',
        success: function(data) {
            if ($.isArray(data) && data.length > 0) {
                renderPermissions(data);
                updateStats();
                if (menuPermissionsReady) {
                    syncPermsToMenu();
                }
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
    var html = '<div class="permission-item perm-nested" data-depth="' + depth + '" data-perm-id="' + node.id + '" data-perm-key="' + escapeAttr((node.permKey || '').toLowerCase()) + '" data-perm-search="' + searchBlob + '">';
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
    updateStats();
    syncPermsToMenu();
    saveRolePermissionsNow();
}

function bindPermissionChoiceEvents() {
    $('#permissionsContainer').off('click', '.perm-choice-btn').on('click', '.perm-choice-btn', function () {
        var $btn = $(this);
        var $item = $btn.closest('.permission-item[data-perm-id]');
        var value = $btn.attr('data-value');
        setPermissionChoice($item, value);
        updateStats();
        syncPermsToMenu();
        saveRolePermissionsNow();
    });
}

function isPermKeyAllowed(permKey) {
    if (!permKey) return false;
    var key = String(permKey).toLowerCase();
    var allowed = false;
    $('.permission-item[data-perm-key]').each(function () {
        if (($(this).attr('data-perm-key') || '') === key) {
            if ($(this).find('input.perm-input').val() === '1') {
                allowed = true;
            }
        }
    });
    return allowed;
}

function setPermIdsValue(permIds, value) {
    (permIds || []).forEach(function (id) {
        var $item = $('.permission-item[data-perm-id="' + id + '"]');
        if ($item.length) {
            setPermissionChoice($item, value);
        }
    });
}

function computeMenuEnabledFromPerms(item) {
    if (!item || !item.permKeys || item.permKeys.length === 0) {
        return true;
    }
    for (var i = 0; i < item.permKeys.length; i++) {
        if (isPermKeyAllowed(item.permKeys[i])) {
            return true;
        }
    }
    return false;
}

function getEnabledMenuKeys() {
    var enabled = {};
    Object.keys(menuItemsIndex).forEach(function (key) {
        var item = menuItemsIndex[key];
        if (item.permKeys && item.permKeys.length === 0) {
            enabled[key] = true;
            return;
        }
        if (menuEnabledState[key]) {
            enabled[key] = true;
        }
    });
    return enabled;
}

function isPermKeyRequiredByOtherMenus(permKey, excludeMenuKey) {
    var needed = false;
    Object.keys(menuItemsIndex).forEach(function (key) {
        if (key === excludeMenuKey) return;
        if (!menuEnabledState[key]) return;
        var item = menuItemsIndex[key];
        (item.permKeys || []).forEach(function (pk) {
            if (pk === permKey) {
                needed = true;
            }
        });
    });
    return needed;
}

function applyMenuToggle(menuKey, enabled, options) {
    options = options || {};
    if (menuSyncLock) return;
    menuSyncLock = true;

    var item = menuItemsIndex[menuKey];
    if (!item) {
        menuSyncLock = false;
        return;
    }

    if (item.permKeys && item.permKeys.length === 0) {
        menuEnabledState[menuKey] = true;
        menuSyncLock = false;
        return;
    }

    menuEnabledState[menuKey] = !!enabled;

    if (!menuAccessHasOverrides) {
        if (enabled) {
            setPermIdsValue(item.permIds, '1');
        } else {
            (item.permKeys || []).forEach(function (pk) {
                if (!isPermKeyRequiredByOtherMenus(pk, menuKey)) {
                    $('.permission-item[data-perm-key="' + pk + '"]').each(function () {
                        setPermissionChoice($(this), '0');
                    });
                }
            });
        }
        updateStats();
    }
    updateMenuStats();
    updateSectionToggleUiForMenuKey(menuKey);
    menuSyncLock = false;

    if (!options.skipSave) {
        saveMenuAccessNow();
    }
}

function sectionEnabledFor(sec) {
    var sectionKey = sec.key || '';
    if (sectionKey && menuEnabledState[sectionKey] !== undefined) {
        return !!menuEnabledState[sectionKey];
    }
    var any = false;
    if (!sec.children) {
        return false;
    }
    for (var j = 0; j < sec.children.length; j++) {
        var child = sec.children[j];
        if (child.header || !child.key) {
            continue;
        }
        if (menuEnabledState[child.key]) {
            any = true;
            break;
        }
    }
    return any;
}

function updateSectionToggleUiForMenuKey(menuKey) {
    var $item = $('.menu-access-item').has('.menu-access-toggle[data-menu-key="' + menuKey + '"]');
    var $section = $item.closest('.menu-access-section[data-section-key]');
    if (!$section.length) {
        return;
    }
    var sectionKey = $section.attr('data-section-key') || '';
    if (!sectionKey) {
        return;
    }
    var anyEnabled = false;
    $section.find('.menu-access-toggle:not(:disabled)').each(function () {
        if ($(this).is(':checked')) {
            anyEnabled = true;
        }
    });
    menuEnabledState[sectionKey] = anyEnabled;
    var $toggle = $section.find('.menu-section-toggle');
    $toggle.prop('checked', anyEnabled);
    $toggle.next('label').text(anyEnabled ? 'Show' : 'Hide');
}

function applySectionToggle(sectionKey, enabled) {
    menuEnabledState[sectionKey] = !!enabled;
    var $section = $('.menu-access-section[data-section-key="' + sectionKey + '"]');
    $section.find('.menu-access-toggle:not(:disabled)').each(function () {
        var childKey = $(this).data('menu-key');
        $(this).prop('checked', enabled);
        $(this).next('label').text(enabled ? 'Show' : 'Hide');
        applyMenuToggle(childKey, enabled, { skipSave: true });
    });
    var $toggle = $section.find('.menu-section-toggle');
    $toggle.prop('checked', enabled);
    $toggle.next('label').text(enabled ? 'Show' : 'Hide');
    saveMenuAccessNow();
}

function collectMenuAccessPayload() {
    var payload = {};
    Object.keys(menuItemsIndex).forEach(function (key) {
        var item = menuItemsIndex[key];
        if (item.permKeys && item.permKeys.length === 0) {
            payload[key] = '1';
            return;
        }
        payload[key] = menuEnabledState[key] ? '1' : '0';
    });
    $('.menu-section-toggle').each(function () {
        var sk = $(this).data('section-key');
        if (sk) {
            payload[sk] = $(this).is(':checked') ? '1' : '0';
        }
    });
    return payload;
}

function setMenuSaveStatus(state, message) {
    var $el = $('#menuSaveStatus');
    $el.removeClass('is-saving is-saved is-error');
    if (state) {
        $el.addClass('is-' + state);
    }
    $el.text(message || '');
}

function buildMenuAccessPostBody(roleId, menuAccess) {
    var parts = [
        'roleid=' + encodeURIComponent(roleId),
        encodeURIComponent('<?= csrf_token() ?>') + '=' + encodeURIComponent($('input[name="<?= csrf_token() ?>"]').val() || '')
    ];
    Object.keys(menuAccess).forEach(function (key) {
        parts.push(
            'menu_access[' + encodeURIComponent(key) + ']=' + encodeURIComponent(menuAccess[key])
        );
    });
    return parts.join('&');
}

function doSaveMenuAccess(roleId) {
    if (menuSaveInFlight) {
        menuSaveQueued = true;
        return;
    }
    if (roleId <= 0) {
        return;
    }

    menuSaveInFlight = true;
    setMenuSaveStatus('saving', 'Saving...');

    $.ajax({
        url: '<?= base_url('admin/roles/save_menu_access') ?>',
        type: 'POST',
        data: buildMenuAccessPostBody(roleId, collectMenuAccessPayload()),
        dataType: 'json',
        success: function (resp) {
            if (resp && resp.success) {
                menuAccessHasOverrides = true;
                setMenuSaveStatus('saved', 'Saved');
                clearPermissionDirty();
            } else {
                setMenuSaveStatus('error', 'Save failed — reload page');
                toastr.error((resp && resp.msg) ? resp.msg : 'Failed to save menu access');
            }
            if (resp && resp.csrf_hash) {
                $('input[name="<?= csrf_token() ?>"]').val(resp.csrf_hash);
            }
        },
        error: function (xhr) {
            setMenuSaveStatus('error', 'Save failed — reload page');
            var msg = 'Failed to save menu access';
            if (xhr && xhr.status === 403) {
                msg = 'Session expired. Please reload the page and try again.';
            }
            toastr.error(msg);
        },
        complete: function () {
            menuSaveInFlight = false;
            if (menuSaveQueued) {
                menuSaveQueued = false;
                doSaveMenuAccess(roleId);
            }
        }
    });
}

function saveMenuAccessNow() {
    var roleId = getEditingRoleId();
    if (roleId <= 0) {
        return;
    }
    clearTimeout(menuSaveTimer);
    menuSaveTimer = setTimeout(function () {
        doSaveMenuAccess(roleId);
    }, 250);
}

function collectPermsPayload() {
    var perms = {};
    $('input.perm-input').each(function () {
        var match = (this.name || '').match(/^perms\[(\d+)\]$/);
        if (match) {
            perms[match[1]] = $(this).val();
        }
    });
    return perms;
}

function buildPermsPostBody(roleId, perms) {
    var parts = [
        'id=' + encodeURIComponent(roleId),
        'role_name_id=' + encodeURIComponent($('#role_name_id').val() || ''),
        'plan_id=' + encodeURIComponent($('#plan_id').val() || ''),
        encodeURIComponent('<?= csrf_token() ?>') + '=' + encodeURIComponent($('input[name="<?= csrf_token() ?>"]').val() || '')
    ];
    Object.keys(perms).forEach(function (permId) {
        parts.push('perms[' + encodeURIComponent(permId) + ']=' + encodeURIComponent(perms[permId]));
    });
    return parts.join('&');
}

function saveRolePermissionsNow() {
    var roleId = getEditingRoleId();
    if (roleId <= 0 || permSaveInFlight) {
        return;
    }
    clearTimeout(permSaveTimer);
    permSaveTimer = setTimeout(function () {
        permSaveInFlight = true;
        $.ajax({
            url: '<?= base_url('admin/roles/save') ?>',
            type: 'POST',
            data: buildPermsPostBody(roleId, collectPermsPayload()),
            dataType: 'json',
            success: function (resp) {
                if (resp && resp.success) {
                    clearPermissionDirty();
                } else {
                    toastr.error((resp && resp.msg) ? resp.msg : 'Failed to save permissions');
                }
            },
            error: function () {
                toastr.error('Failed to save permissions');
            },
            complete: function () {
                permSaveInFlight = false;
            }
        });
    }, 400);
}

function syncPermsToMenu() {
    if (menuSyncLock || !menuItemsIndex) return;
    menuSyncLock = true;

    if (!menuAccessHasOverrides) {
        Object.keys(menuItemsIndex).forEach(function (key) {
            var item = menuItemsIndex[key];
            if (item.permKeys && item.permKeys.length === 0) {
                menuEnabledState[key] = true;
                return;
            }
            menuEnabledState[key] = computeMenuEnabledFromPerms(item);
        });
    }

    $('.menu-access-toggle').each(function () {
        var key = $(this).data('menu-key');
        var locked = $(this).data('locked') === 1 || $(this).data('locked') === '1';
        if (locked) {
            $(this).prop('checked', true).prop('disabled', true);
            return;
        }
        $(this).prop('checked', !!menuEnabledState[key]);
        $(this).next('label').text(menuEnabledState[key] ? 'Show' : 'Hide');
    });
    $('.menu-section-toggle').each(function () {
        var sectionKey = $(this).data('section-key');
        var enabled = menuEnabledState[sectionKey];
        if (enabled === undefined) {
            enabled = $(this).is(':checked');
        }
        $(this).prop('checked', !!enabled);
        $(this).next('label').text(enabled ? 'Show' : 'Hide');
    });
    updateMenuStats();
    menuSyncLock = false;
}

function loadMenuPermissions(roleId, onReady) {
    menuPermissionsReady = false;
    $('#menuAccessContainer').html('<div class="loading-spinner"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Loading menu access...</p></div>');
    $.ajax({
        url: '<?= base_url('admin/roles/menu_perm_data') ?>',
        type: 'POST',
        data: {
            roleid: roleId,
            <?= json_encode(csrf_token()) ?>: $('input[name="<?= csrf_token() ?>"]').val() || <?= json_encode(csrf_hash()) ?>
        },
        dataType: 'json',
        success: function (data) {
            menuCatalogData = data.sections || [];
            menuItemsIndex = data.items || {};
            menuAccessHasOverrides = !!data.hasOverrides;
            menuEnabledState = {};
            Object.keys(data.state || {}).forEach(function (key) {
                menuEnabledState[key] = !!(data.state[key] && data.state[key].enabled);
            });
            Object.keys(menuItemsIndex).forEach(function (key) {
                if (menuEnabledState[key] === undefined) {
                    menuEnabledState[key] = false;
                }
            });
            renderMenuAccess(menuCatalogData);
            updateMenuStats();
            setMenuSaveStatus('', '');
            menuPermissionsReady = true;
            if (typeof onReady === 'function') {
                onReady();
            }
        },
        error: function () {
            $('#menuAccessContainer').html('<div class="alert alert-danger">Failed to load menu access.</div>');
        }
    });
}

function loadRolePermissionPanels(roleId) {
    loadMenuPermissions(roleId, function () {
        loadPermissions(roleId);
    });
}

function renderMenuAccess(sections) {
    var html = '';
    for (var i = 0; i < sections.length; i++) {
        var sec = sections[i];
        if (!sec.children || sec.children.length === 0) continue;
        var groupId = 'menu_group_' + i;
        var sectionKey = sec.key || '';
        var sectionEnabled = sectionEnabledFor(sec);
        if (sectionKey) {
            menuEnabledState[sectionKey] = sectionEnabled;
        }
        html += '<div class="menu-access-section" data-section-key="' + escapeAttr(sectionKey) + '" data-menu-search="' + escapeAttr((sec.label || '').toLowerCase()) + '">';
        html += '<div class="menu-access-section-header">';
        html += '<span class="menu-access-section-title" onclick="toggleMenuGroup(\'' + groupId + '\')"><i class="' + escapeHtml(sec.icon || 'fas fa-folder') + '"></i> ' + escapeHtml(sec.label) + '</span>';
        html += '<div class="menu-access-section-actions" onclick="event.stopPropagation()">';
        if (sectionKey) {
            var secToggleId = 'menu_section_' + String(sectionKey).replace(/[^a-zA-Z0-9_-]/g, '_');
            html += '<div class="form-check form-switch">';
            html += '<input type="checkbox" class="form-check-input menu-section-toggle" id="' + secToggleId + '" data-section-key="' + escapeAttr(sectionKey) + '" ' + (sectionEnabled ? 'checked' : '') + '>';
            html += '<label class="form-check-label" for="' + secToggleId + '">' + (sectionEnabled ? 'Show' : 'Hide') + '</label>';
            html += '</div>';
        }
        html += '<i class="fas fa-chevron-down" style="cursor:pointer" onclick="toggleMenuGroup(\'' + groupId + '\')"></i>';
        html += '</div></div>';
        html += '<div id="' + groupId + '" class="menu-access-section-body">';
        for (var j = 0; j < sec.children.length; j++) {
            var child = sec.children[j];
            if (child.header) {
                html += '<div class="menu-access-header-row">' + escapeHtml(child.label) + '</div>';
                continue;
            }
            var enabled = !!menuEnabledState[child.key];
            var locked = !!child.locked;
            html += '<div class="menu-access-item" data-menu-item-search="' + escapeAttr((child.label || '').toLowerCase()) + '">';
            html += '<div class="menu-access-item-label">';
            html += '<i class="' + escapeHtml(child.icon || 'far fa-circle') + '"></i> ' + escapeHtml(child.label);
            if (child.superAdminOnly) {
                html += '<span class="menu-access-shared text-muted"><i class="fas fa-user-shield"></i> Super admin sidebar only</span>';
            } else if (child.directorQuizzesMenu) {
                html += '<span class="menu-access-shared text-muted"><i class="fas fa-user-tie"></i> Director / principal quizzes</span>';
            }
            if (child.sharedWith && child.sharedWith.length > 0) {
                html += '<span class="menu-access-shared"><i class="fas fa-link"></i> Also affects: ' + escapeHtml(child.sharedWith.join(', ')) + '</span>';
            }
            html += '</div>';
            html += '<div class="form-check form-switch">';
            var safeToggleId = 'menu_toggle_' + String(child.key).replace(/[^a-zA-Z0-9_-]/g, '_');
            html += '<input type="checkbox" class="form-check-input menu-access-toggle" id="' + safeToggleId + '" data-menu-key="' + escapeAttr(child.key) + '" data-locked="' + (locked ? '1' : '0') + '" ' + (enabled || locked ? 'checked' : '') + (locked ? ' disabled' : '') + '>';
            html += '<label class="form-check-label" for="' + safeToggleId + '">' + (locked ? 'Always' : (enabled ? 'Show' : 'Hide')) + '</label>';
            html += '</div>';
            html += '</div>';
        }
        html += '</div></div>';
    }
    $('#menuAccessContainer').html(html || '<div class="alert alert-warning">No menu items found.</div>');
    $('.menu-access-section-body').first().addClass('show');
    bindMenuAccessEvents();
}

function bindMenuAccessEvents() {
    $('#menuAccessContainer').off('change', '.menu-access-toggle').on('change', '.menu-access-toggle', function () {
        var key = $(this).data('menu-key');
        var enabled = $(this).is(':checked');
        $(this).next('label').text(enabled ? 'Show' : 'Hide');
        applyMenuToggle(key, enabled);
    });
    $('#menuAccessContainer').off('change', '.menu-section-toggle').on('change', '.menu-section-toggle', function () {
        var sectionKey = $(this).data('section-key');
        var enabled = $(this).is(':checked');
        $(this).next('label').text(enabled ? 'Show' : 'Hide');
        applySectionToggle(sectionKey, enabled);
    });
}

function updateMenuStats() {
    var total = 0;
    var shown = 0;
    Object.keys(menuItemsIndex).forEach(function (key) {
        var item = menuItemsIndex[key];
        if (!item.permKeys || item.permKeys.length === 0) return;
        total++;
        if (menuEnabledState[key]) shown++;
    });
    $('#menuStats').html('Menu items: ' + shown + ' shown / ' + total + ' configurable');
}

function toggleMenuGroup(groupId) {
    $('#' + groupId).toggleClass('show');
}

var menuSearchTimer = null;
function applyMenuSearch(raw) {
    var q = (raw || '').trim().toLowerCase();
    if (!q) {
        $('.menu-access-section, .menu-access-item').removeClass('no-search-match');
        $('#menuSearchHint').text('');
        return;
    }
    var visibleSections = 0;
    var visibleItems = 0;
    $('.menu-access-section').each(function () {
        var $sec = $(this);
        var secMatch = (($sec.attr('data-menu-search') || '').indexOf(q) !== -1);
        var anyItem = false;
        $sec.find('.menu-access-item').each(function () {
            var m = (($(this).attr('data-menu-item-search') || '').indexOf(q) !== -1);
            $(this).toggleClass('no-search-match', !m);
            if (m) anyItem = true;
        });
        var show = secMatch || anyItem;
        $sec.toggleClass('no-search-match', !show);
        if (show) {
            visibleSections++;
            visibleItems += $sec.find('.menu-access-item:not(.no-search-match)').length;
            $sec.find('.menu-access-section-body').addClass('show');
        }
    });
    $('#menuSearchHint').text(visibleItems ? ('Showing ' + visibleItems + ' item(s) in ' + visibleSections + ' section(s)') : 'No matching menu items.');
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
        var ok = window.confirm('Permission changes may still be saving. Switch role anyway?');
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
            loadRolePermissionPanels(newRoleId);
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
    var roleId = getEditingRoleId();
    loadRolePermissionPanels(roleId);
    
    $('#role_name_id').on('change', function() {
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

    $('#menuSearchInput').on('input', function () {
        clearTimeout(menuSearchTimer);
        var v = $(this).val();
        menuSearchTimer = setTimeout(function () { applyMenuSearch(v); }, 200);
    });
    $('#menuSearchClear').on('click', function () {
        $('#menuSearchInput').val('');
        applyMenuSearch('');
    });
    $('#menuShowAllBtn').on('click', function () {
        Object.keys(menuItemsIndex).forEach(function (key) {
            var item = menuItemsIndex[key];
            if (item.permKeys && item.permKeys.length > 0) {
                applyMenuToggle(key, true, { skipSave: true });
            }
        });
        $('.menu-access-toggle:not(:disabled)').prop('checked', true).each(function () {
            $(this).next('label').text('Show');
        });
        $('.menu-section-toggle').prop('checked', true).each(function () {
            $(this).next('label').text('Show');
        });
        saveMenuAccessNow();
    });
    $('#menuHideAllBtn').on('click', function () {
        Object.keys(menuItemsIndex).forEach(function (key) {
            var item = menuItemsIndex[key];
            if (item.permKeys && item.permKeys.length > 0) {
                applyMenuToggle(key, false, { skipSave: true });
            }
        });
        $('.menu-access-toggle:not(:disabled)').prop('checked', false).each(function () {
            $(this).next('label').text('Hide');
        });
        $('.menu-section-toggle').prop('checked', false).each(function () {
            $(this).next('label').text('Hide');
        });
        saveMenuAccessNow();
    });
    $('#menuExpandAllBtn').on('click', function () {
        $('.menu-access-section-body').addClass('show');
    });
    $('#menuCollapseAllBtn').on('click', function () {
        $('.menu-access-section-body').removeClass('show');
    });
    
    $('#roleForm').on('submit', function (e) {
        e.preventDefault();
    });
});
</script>

<?= $this->endSection() ?>