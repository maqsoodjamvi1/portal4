<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Role Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Roles</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Roles Listtt</h3>
                    <div class="card-tools">
                        <a href="<?= base_url('admin/roles/add') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Role
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped" id="roles-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Role Name</th>
                                <th>Plan</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($roles) && !empty($roles)): ?>
                                <?php foreach ($roles as $role): ?>
                                    <tr>
                                        <td><?= $role->id ?></td>
                                        <td><?= $role->role_name ?? $role->role_name_id ?></td>
                                        <td><?= $role->plan_name ?? 'No Plan' ?></td>
                                        <td>
                                            <a href="<?= base_url('admin/roles/edit/' . $role->id) ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="javascript:void(0)" onclick="deleteRole(<?= $role->id ?>)" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No roles found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function deleteRole(id) {
    if (confirm('Are you sure you want to delete this role?')) {
        $.ajax({
            url: '<?= base_url('admin/roles/delete') ?>/' + id,
            type: 'DELETE',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.msg);
                    location.reload();
                } else {
                    toastr.error(response.msg);
                }
            }
        });
    }
}
</script>

<?= $this->endSection() ?>