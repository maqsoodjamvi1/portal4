<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>
                    <i class="fas fa-users"></i> Role Management
                </h1>
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

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Roles List
                    </h3>
                    <div class="card-tools">
                        <a href="<?= base_url('admin/roles/add') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Role
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover" id="roles-datatable" width="100%">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Role Name</th>
                                    <th>Plan</th>
                                    <th width="100">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this role?</p>
                <p class="text-danger"><small>Note: System roles cannot be deleted.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
var deleteRoleId = null;

$(function() {
    var table = $('#roles-datatable').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        ajax: {
            url: '<?= base_url('admin/roles/data') ?>',
            type: 'POST',
            data: function(d) {
                d.<?= csrf_token() ?> = '<?= csrf_hash() ?>';
            }
        },
        columns: [
            { data: 'id', width: '50' },
            { data: 'roleName' },
            { data: 'plan_name' },
            { data: 'actions', orderable: false, searchable: false, width: '100' }
        ],
        order: [[0, 'desc']],
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Loading...',
            emptyTable: 'No roles found',
            info: 'Showing _START_ to _END_ of _TOTAL_ roles',
            infoEmpty: 'Showing 0 to 0 of 0 roles',
            infoFiltered: '(filtered from _MAX_ total roles)',
            lengthMenu: 'Show _MENU_ roles',
            search: 'Search:',
            zeroRecords: 'No matching roles found'
        }
    });
});

function deleteRole(id) {
    deleteRoleId = id;
    $('#deleteModal').modal('show');
}

$('#confirmDeleteBtn').on('click', function() {
    if (deleteRoleId) {
        $.ajax({
            url: '<?= base_url('admin/roles/delete') ?>/' + deleteRoleId,
            type: 'DELETE',
            dataType: 'json',
            data: {
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.msg);
                    $('#deleteModal').modal('hide');
                    $('#roles-datatable').DataTable().ajax.reload();
                } else {
                    toastr.error(response.msg);
                }
            },
            error: function() {
                toastr.error('Failed to delete role');
            }
        });
    }
});
</script>

<?= $this->endSection() ?>