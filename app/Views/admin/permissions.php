<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style type="text/css">
    ul.ztree {
        margin-top: 10px;
        overflow-y: none;
        overflow-x: auto;
        min-height: 400px;
        max-height: 600px;
    }
    .ztree li span.button.add {
        margin-left: 2px;
        margin-right: -1px;
        background-position: -144px 0;
        vertical-align: top;
        *vertical-align: middle;
    }
    .search-box {
        margin-bottom: 15px;
    }
    .tree-actions {
        margin-bottom: 15px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 4px;
    }
    .badge-permission {
        background-color: #17a2b8;
        color: #fff;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 10px;
        margin-left: 8px;
    }
    .alert-info {
        margin-top: 20px;
    }
</style>

<link rel="stylesheet" href="<?= base_url('resource/ztree/css/zTreeStyle/zTreeStyle.css') ?>" />
<script type="text/javascript" src="<?= base_url('resource/ztree/js/jquery.ztree.core.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('resource/ztree/js/jquery.ztree.excheck.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('resource/ztree/js/jquery.ztree.exedit.js') ?>"></script>

<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Permissions</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Permissions</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-primary card-outline card-tabs">
                <div class="card-header p-0 pt-1 border-bottom-0">    
                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" href="<?= base_url('admin/permissions') ?>">
                                <i class="fas fa-tree"></i> Permissions Tree
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('admin/permissions/add') ?>">
                                <i class="fas fa-plus"></i> Add Permission
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="tree-actions">
                                <div class="search-box">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="searchPermission" 
                                               placeholder="Search permissions...">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button" id="searchBtn">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="btn-group w-100">
                                    <button type="button" class="btn btn-default" id="expandAllBtn">
                                        <i class="fas fa-expand-alt"></i> Expand All
                                    </button>
                                    <button type="button" class="btn btn-default" id="collapseAllBtn">
                                        <i class="fas fa-compress-alt"></i> Collapse All
                                    </button>
                                </div>
                                <div class="mt-2 text-muted small">
                                    <i class="fas fa-info-circle"></i> 
                                    Hover over a permission to see <strong>+</strong> button to add child
                                </div>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <ul id="treeDemo" class="ztree"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
// Global variables
var treeObj = null;

// Main initialization function
function initializePermissionTree() {
    var setting = {
        view: {
            addHoverDom: addHoverDom,
            removeHoverDom: removeHoverDom,
            addDiyDom: addDiyDom,
            selectedMulti: true,
            fontCss: getFontCss
        },
        edit: {
            enable: true,
            editNameSelectAll: true,
            showRemoveBtn: showRemoveBtn,
            showRenameBtn: showRenameBtn,
            removeTitle: 'Delete permission',
            renameTitle: 'Edit permission'
        },
        data: {
            simpleData: {
                enable: true,
                idKey: "id",
                pIdKey: "parent_id",
                rootPId: 0
            }
        },
        callback: {
            beforeRemove: beforeRemove,
            onRemove: onRemove,
            beforeEditName: beforeEditName
        }
    };
    
    // Show loading
    $('#treeDemo').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading permissions...</div>');
    
    $.ajax({
        url: '<?= base_url('admin/permissions/data') ?>',
        type: 'POST',
        dataType: 'text',
        success: function(res) {
            try {
                var jsonData = eval('(' + res + ')');
                
                if(!jsonData || jsonData.length === 0) {
                    $('#treeDemo').html('<div class="alert alert-info">No permissions found. Click "Add Permissions" to create some.</div>');
                    return;
                }
                
                $('#treeDemo').empty();
                treeObj = $.fn.zTree.init($("#treeDemo"), setting, jsonData);
                
                // Auto expand first level
                if(treeObj) {
                    var nodes = treeObj.getNodes();
                    for(var i = 0; i < nodes.length; i++) {
                        treeObj.expandNode(nodes[i], true, false, false);
                    }
                }
            } catch(e) {
                console.error('Error parsing JSON:', e);
                $('#treeDemo').html('<div class="alert alert-danger">Error loading permissions: Invalid data format</div>');
                toastr.error('Failed to load permissions: Invalid data format');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            $('#treeDemo').html('<div class="alert alert-danger">Failed to load permissions. Please check the browser console (F12) for details.</div>');
            toastr.error('Failed to load permissions');
        }
    });
}

// Add hover dom for adding child permissions
function addHoverDom(treeId, treeNode) {
    var sObj = $('#' + treeNode.tId + '_span');
    if(treeNode.editNameFlag || $('#addBtn_' + treeNode.tId).length > 0) return;
    
    var addStr = '<span class="button add" id="addBtn_' + treeNode.tId + '" title="Add child permission" onfocus="this.blur();"></span>';
    sObj.after(addStr);
    
    var btn = $('#addBtn_' + treeNode.tId);
    if(btn) {
        btn.bind('click', function() {
            window.location.href = '<?= base_url('admin/permissions/add') ?>?parent_id=' + treeNode.id;
            return false;
        });
    }
}

// Remove hover dom
function removeHoverDom(treeId, treeNode) {
    $('#addBtn_' + treeNode.tId).unbind().remove();
}

// Add DIY dom to show permission key
function addDiyDom(treeId, treeNode) {
    var aObj = $('#' + treeNode.tId + '_a');
    if(treeNode.permKey) {
        var diyStr = '<span class="badge-permission">' + treeNode.permKey + '</span>';
        aObj.append(diyStr);
    }
}

// Show remove button only for leaf nodes
function showRemoveBtn(treeId, treeNode) {
    return !(treeNode.isParent && treeNode.children && treeNode.children.length > 0);
}

// Show rename/edit button for all nodes
function showRenameBtn(treeId, treeNode) {
    return true;
}

// Get font CSS for parent nodes
function getFontCss(treeId, treeNode) {
    return treeNode.isParent ? {'font-weight': 'bold'} : {};
}

// Before remove confirmation
function beforeRemove(treeId, treeNode) {
    var zTree = $.fn.zTree.getZTreeObj(treeId);
    zTree.selectNode(treeNode);
    
    var msg = 'Are you sure you want to delete permission "' + treeNode.name + '"?';
    if(treeNode.isParent && treeNode.children && treeNode.children.length > 0) {
        msg = 'This permission has child permissions. Deleting it will remove all child permissions. Are you sure?';
    }
    
    return confirm(msg);
}

// On remove handler
function onRemove(event, treeId, treeNode) {
    $.ajax({
        url: '<?= base_url('admin/permissions/delete') ?>',
        type: 'GET',
        data: {id: treeNode.id},
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                toastr.success(response.msg);
                refreshTree();
            } else {
                toastr.error(response.msg);
            }
        },
        error: function() {
            toastr.error('Failed to delete permission');
        }
    });
}

// Before edit name - redirect to edit page
function beforeEditName(treeId, treeNode) {
    window.location.href = '<?= base_url('admin/permissions/edit') ?>?id=' + treeNode.id;
    return false;
}

// Refresh the tree
function refreshTree() {
    if(treeObj) {
        treeObj.destroy();
    }
    initializePermissionTree();
}

// Expand all nodes
function expandAll() {
    if(treeObj) {
        treeObj.expandAll(true);
    }
}

// Collapse all nodes
function collapseAll() {
    if(treeObj) {
        treeObj.expandAll(false);
    }
}

// Search permissions
function searchPermissions() {
    var keyword = $('#searchPermission').val().toLowerCase();
    
    if(!treeObj) return;
    
    if(keyword === '') {
        treeObj.expandAll(false);
        treeObj.showNodes(treeObj.getNodes());
        return;
    }
    
    var matchedIds = [];
    
    function searchNode(node) {
        if(node.name.toLowerCase().indexOf(keyword) !== -1 || 
           (node.permKey && node.permKey.toLowerCase().indexOf(keyword) !== -1)) {
            matchedIds.push(node.id);
        }
        
        if(node.children) {
            for(var i = 0; i < node.children.length; i++) {
                searchNode(node.children[i]);
            }
        }
    }
    
    var nodes = treeObj.getNodes();
    for(var i = 0; i < nodes.length; i++) {
        searchNode(nodes[i]);
    }
    
    var allNodes = treeObj.transformToArray(nodes);
    for(var i = 0; i < allNodes.length; i++) {
        var node = allNodes[i];
        if(matchedIds.indexOf(node.id) !== -1) {
            treeObj.showNode(node);
            treeObj.expandNode(node, true, false, false);
        } else {
            treeObj.hideNode(node);
        }
    }
    
    if(matchedIds.length === 0) {
        toastr.info('No permissions found matching "' + keyword + '"');
    }
}

// Document ready
$(document).ready(function() {
    // Initialize tree
    initializePermissionTree();
    
    // Expand/Collapse buttons
    $('#expandAllBtn').on('click', function() {
        expandAll();
    });
    
    $('#collapseAllBtn').on('click', function() {
        collapseAll();
    });
    
    // Search functionality
    $('#searchBtn, #searchPermission').on('click keyup', function(e) {
        if(e.type === 'keyup' && e.keyCode !== 13) return;
        searchPermissions();
    });
});
</script>

<?= $this->endSection() ?>