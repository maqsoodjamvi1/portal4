<?php

$uiNeedsDataTables = true;

?>

<?= $this->extend('layouts/admin_template') ?>

<?= $this->section('content') ?>



<?= view('components/page_header', [

    'title' => 'Role Management',

    'icon' => 'fas fa-users',

    'breadcrumbs' => [

        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],

        ['label' => 'Roles', 'active' => true],

    ],

]) ?>



<section class="content">

  <?php ob_start(); ?>

  <table class="table table-striped table-bordered table-hover mb-0" id="roles-datatable" width="100%">

    <thead>

      <tr>

        <th width="50">#</th>

        <th>Role Name</th>

        <th width="100">Actions</th>

      </tr>

    </thead>

    <tbody></tbody>

  </table>

  <?php $tableHtml = ob_get_clean(); ?>

  <?= view('components/data_table_card', [

      'title' => 'Roles List',

      'icon' => 'fas fa-list',

      'toolbarHtml' => '<a href="' . esc(base_url('admin/roles/add'), 'attr') . '" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Role</a>',

      'tableHtml' => $tableHtml,

  ]) ?>

</section>



<?= view('components/modal_shell', [

    'id' => 'deleteModal',

    'title' => 'Confirm Delete',

    'bodyHtml' => '<p>Are you sure you want to delete this role?</p><p class="text-danger"><small>Note: System roles cannot be deleted.</small></p>',

    'footerHtml' => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>'

        . '<button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>',

]) ?>



<?= view('components/confirm_dialog') ?>



<script type="text/javascript">

var deleteRoleId = null;



$(function() {

    $('#roles-datatable').DataTable({

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

            { data: 'actions', orderable: false, searchable: false, width: '100' }

        ],

        order: [[0, 'desc']],

        language: {

            processing: '<i class="fas fa-spinner fa-spin"></i> Loading...',

            emptyTable: 'No roles found',

            zeroRecords: 'No matching roles found'

        }

    });

});



function deleteRole(id) {

    deleteRoleId = id;

    $('#deleteModal').modal('show');

}



$('#confirmDeleteBtn').on('click', function() {

    if (!deleteRoleId) return;

    $.ajax({

        url: '<?= base_url('admin/roles/delete') ?>/' + deleteRoleId,

        type: 'DELETE',

        dataType: 'json',

        data: { '<?= csrf_token() ?>': '<?= csrf_hash() ?>' },

        success: function(response) {

            if (response.success) {

                toastr.success(response.msg);

                $('#deleteModal').modal('hide');

                $('#roles-datatable').DataTable().ajax.reload();

            } else {

                toastr.error(response.msg);

            }

        },

        error: function() { toastr.error('Failed to delete role'); }

    });

});

</script>



<?= $this->endSection() ?>


