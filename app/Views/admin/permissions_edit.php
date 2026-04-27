<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php 
if(isset($info)){
    $action = 'edit';
    $header = 'Edit Permission';
    $parent_id = $info->parent_id;
    $permKey = $info->permKey;
    $permName = $info->permName;
    $id = $info->id;
    $sortid = isset($info->sortid) ? $info->sortid : 0;
} else {
    $action = 'add';
    $header = 'Add Permission';
    $parent_id = isset($parent_id) ? $parent_id : 0;
    $permKey = '';
    $permName = '';
    $id = '';
    $sortid = 0;
}

// Get permission groups for dropdown
$db = \Config\Database::connect();
$permissionGroups = $db->table('permissions')
    ->select('id, permName, parent_id')
    ->orderBy('sortid', 'ASC')
    ->orderBy('id', 'ASC')
    ->get()
    ->getResult();

// Build permission options recursively
function buildPermissionOptions($permissions, $parentId = 0, $level = 0, $excludeId = null, $selectedId = null) {
    $options = '';
    foreach ($permissions as $perm) {
        if ($perm->parent_id == $parentId) {
            if ($excludeId && $perm->id == $excludeId) {
                continue;
            }
            $prefix = str_repeat('&nbsp;&nbsp;&nbsp;', $level);
            if ($level > 0) {
                $prefix .= '└─ ';
            }
            $selected = ($selectedId == $perm->id) ? 'selected="selected"' : '';
            $options .= "<option value='{$perm->id}' {$selected}>{$prefix}{$perm->permName}</option>";
            $options .= buildPermissionOptions($permissions, $perm->id, $level + 1, $excludeId, $selectedId);
        }
    }
    return $options;
}

$perm_options = buildPermissionOptions($permissionGroups, 0, 0, $id, $parent_id);
?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>
                    <i class="fas fa-key"></i> <?= $header ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/permissions') ?>">Permissions</a></li>
                    <li class="breadcrumb-item active"><?= $header ?></li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit"></i> Permission Details
                    </h3>
                    <div class="card-tools">
                        <a href="<?= base_url('admin/permissions') ?>" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <?= form_open('admin/permissions/save', 'class="bs-docs-example" id="permission-edit-form"') ?>
                    <?= form_hidden('id', $id) ?>
                    
                    <div class="form-group">
                        <label class="control-label">Parent Permission</label>
                        <div class="controls">
                            <select name="parent_id" id="parent_id" class="form-control select2">
                                <option value="0">Top Level (Root)</option>
                                <?= $perm_options ?>
                            </select>
                            <small class="form-text text-muted">Select parent permission to create hierarchy</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Permission Name <span class="text-danger">*</span></label>
                        <input type="text" name="permName" value="<?= htmlspecialchars($permName) ?>" 
                               class="form-control" placeholder="e.g., Manage Users" required />
                        <small class="form-text text-muted">Human-readable permission name</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Permission Key <span class="text-danger">*</span></label>
                        <input type="text" name="permKey" value="<?= htmlspecialchars($permKey) ?>" 
                               class="form-control auto-slug" placeholder="e.g., manage-users" required />
                        <small class="form-text text-muted">Unique identifier (use lowercase and hyphens)</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Sort Order</label>
                        <input type="number" name="sortid" value="<?= $sortid ?>" 
                               class="form-control" placeholder="0" />
                        <small class="form-text text-muted">Lower numbers appear first in the list</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="controls">
                            <button type="submit" id="submitBtn" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Permission
                            </button>
                            <button type="reset" class="btn btn-default">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                            <button type="button" class="btn btn-default" onclick="history.go(-1);">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </div>
                    
                    <?= form_close() ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Help Information
                    </h3>
                </div>
                <div class="card-body">
                    <h5>Permission Naming Guidelines:</h5>
                    <ul>
                        <li>Use clear, descriptive names</li>
                        <li>Example: "Manage Users", "View Reports"</li>
                    </ul>
                    
                    <h5>Permission Key Format:</h5>
                    <ul>
                        <li>Use lowercase letters only</li>
                        <li>Use hyphens for spaces</li>
                        <li>Follow module-action pattern</li>
                        <li>Example: <code>users-manage</code>, <code>reports-view</code></li>
                    </ul>
                    
                    <h5>Hierarchy Tips:</h5>
                    <ul>
                        <li>Group related permissions under a parent</li>
                        <li>Parent permissions act as modules/groups</li>
                        <li>Child permissions inherit parent's access</li>
                    </ul>
                    
                    <h5>Wildcard Support:</h5>
                    <ul>
                        <li><code>admin-*</code> - All admin permissions</li>
                        <li><code>users-*</code> - All user-related permissions</li>
                        <li><code>*</code> - Super admin (all permissions)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
$(function(){
    // Initialize Select2
    if (typeof $.fn.select2 !== 'undefined') {
        $("#parent_id").select2({
            placeholder: "Select parent permission",
            allowClear: true
        });
    }
    
    // Form validation
    $('#permission-edit-form').validate({
        rules: {
            permName: {
                required: true,
                minlength: 3
            },
            permKey: {
                required: true,
                minlength: 3,
                pattern: /^[a-z0-9\-_]+$/
            }
        },
        messages: {
            permName: {
                required: 'Permission Name is Required',
                minlength: 'Permission Name must be at least 3 characters'
            },
            permKey: {
                required: 'Permission Key is Required',
                minlength: 'Permission Key must be at least 3 characters',
                pattern: 'Permission Key can only contain lowercase letters, numbers, hyphens, and underscores'
            }
        },
        errorElement: 'span',
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        },
        highlight: function(element) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function(element) {
            $(element).removeClass('is-invalid');
        }
    });
    
    // Auto-generate permission key from name
    $('input[name="permName"]').on('keyup', function() {
        var permKey = $(this).val();
        permKey = permKey.toLowerCase()
            .replace(/[^a-z0-9\s]/g, '')
            .replace(/\s+/g, '-');
        
        var currentKey = $('input[name="permKey"]').val();
        if (currentKey === '' || currentKey === $('input[name="permKey"]').data('original')) {
            $('input[name="permKey"]').val(permKey);
            $('input[name="permKey"]').data('original', permKey);
        }
    });
    
    // Store original value
    $('input[name="permKey"]').data('original', $('input[name="permKey"]').val());
    
    // AJAX form submission - FIXED VERSION
    $('#permission-edit-form').on('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        if (!$('#permission-edit-form').valid()) {
            return false;
        }
        
        var formData = $(this).serialize();
        
        console.log('Submitting form data:', formData);
        
        $('#submitBtn').html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        $('#submitBtn').prop('disabled', true);
        
        $.ajax({
            url: '<?= base_url('admin/permissions/save') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log('Response:', response);
                
                if (response.success) {
                    toastr.success(response.msg);
                    
                    // Redirect after successful save
                    setTimeout(function() {
                        window.location.href = '<?= base_url('admin/permissions') ?>';
                    }, 1500);
                } else {
                    if (response.errors) {
                        $.each(response.errors, function(key, value) {
                            toastr.error(value);
                        });
                    } else {
                        toastr.error(response.msg || 'Failed to save permission');
                    }
                    $('#submitBtn').html('<i class="fas fa-save"></i> Save Permission');
                    $('#submitBtn').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.log('Response text:', xhr.responseText);
                
                let errorMsg = 'Failed to save permission';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.msg) errorMsg = response.msg;
                    if (response.errors) {
                        errorMsg = Object.values(response.errors).join(', ');
                    }
                } catch(e) {
                    errorMsg = xhr.responseText || errorMsg;
                }
                
                toastr.error(errorMsg);
                $('#submitBtn').html('<i class="fas fa-save"></i> Save Permission');
                $('#submitBtn').prop('disabled', false);
            }
        });
        
        return false;
    });
    
    // Prevent Enter key from submitting form in a weird way
    $('#permission-edit-form input').on('keypress', function(e) {
        if (e.which === 13 && !$(e.target).is('textarea')) {
            e.preventDefault();
            $('#submitBtn').click();
            return false;
        }
    });
});
</script>

<style>
/* Additional styling for better UX */
.select2-container--default .select2-selection--single {
    height: 38px;
    padding: 5px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}

.invalid-feedback {
    display: block;
}

.form-group .control-label {
    font-weight: 600;
    margin-bottom: 5px;
}

.card-info ul {
    padding-left: 20px;
}

.card-info li {
    margin-bottom: 5px;
}

code {
    background: #f4f4f4;
    padding: 2px 4px;
    border-radius: 3px;
    color: #d14;
}
</style>

<?= $this->endSection() ?>