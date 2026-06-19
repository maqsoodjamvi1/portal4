<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php ob_start(); ?>
<a href="<?= esc(base_url('admin/teachers/add'), 'attr') ?>" class="btn btn-primary btn-sm">
    <i class="fas fa-user-plus me-1"></i> Add Teacher
</a>
<?php $headerActions = trim(ob_get_clean()); ?>

<?= view('components/page_header', [
    'title' => 'Teachers',
    'subtitle' => 'Manage teacher profiles, contact details, and classroom readiness from a dedicated faculty list.',
    'icon' => 'fas fa-chalkboard-teacher',
    'actionsHtml' => $headerActions,
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Teachers', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="card sms-card sms-index-card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-graduate me-2"></i>
                Faculty Directory
            </h3>
            <div class="card-tools">
                <span class="badge bg-primary">Teaching staff</span>
            </div>
        </div>
        <div class="card-body">
            <div class="sms-section-note mb-3">
                <i class="fas fa-info-circle"></i>
                Keep teacher records active, easy to update, and ready for timetable or academic assignment work.
            </div>

            <table class="table table-bordered table-hover" id="teachers-datatable" width="100%" data-sms-table-name="teachers">
                <thead>
                    <tr>
                        <th width="60">#</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th width="160">Status</th>
                        <th>Mobile No</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</section>

<script type="text/javascript">
$(function () {
    $('#teachers-datatable').DataTable({
        deferRender: true,
        responsive: true,
        autoWidth: false,
        searchDelay: 350,
        order: [[1, 'asc']],
        ajax: {
            url: '<?= base_url('admin/teachers/data'); ?>',
            type: 'post'
        },
        columns: [
            {
                data: 'id',
                className: 'text-center align-middle',
                render: function(data, type, row, meta) {
                    return meta.settings._iDisplayStart + meta.row + 1;
                }
            },
            { data: 'first_name', className: 'align-middle' },
            { data: 'last_name', className: 'align-middle' },
            {
                data: 'status',
                className: 'text-center align-middle',
                render: function(data, type, row) {
                    var checked = data == '1' ? 'checked="checked"' : '';
                    return '<input type="checkbox" ' + checked + ' class="switch-small switchchk" data-on-text="Active" data-off-text="Inactive" data-table="teachers" data-field="status" data-size="mini" data-pk="' + row.id + '" />';
                }
            },
            { data: 'mobile_no', className: 'align-middle' },
            {
                data: 'id',
                sortable: false,
                searchable: false,
                className: 'text-center align-middle',
                render: function(data, type, row) {
                    var html = '';
                    html += '<div class="btn-group">';
                    html += '<a href="<?= base_url('admin/teachers/edit?id=') ?>' + data + '" title="Edit teacher" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>';

                    if (row.issys !== '1') {
                        html += '<a href="javascript:;" onclick="del_confirm(\'notice\', \'Are you sure delete this record\', \'<?= base_url('admin/teachers/delete&id=') ?>' + data + '\',\'teachers-datatable\');" title="Delete teacher" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash me-1"></i>Delete</a>';
                    }

                    html += '</div>';
                    return html;
                }
            }
        ],
        fnDrawCallback: function() {
            $(".switchchk").bootstrapSwitch({
                onSwitchChange: function(e, state) {
                    var fieldval = state ? 1 : 0;
                    var $element = $(e.currentTarget);
                    var tablename = $element.attr('data-table');
                    var fieldname = $element.attr('data-field');
                    var rowid = $element.attr('data-pk');

                    $.post(
                        "<?= base_url('admin/ajax/setboolattributeteachers'); ?>",
                        {
                            act: 'upsort',
                            tbname: tablename,
                            tbfield: fieldname,
                            tbfieldvalue: fieldval,
                            id: rowid
                        },
                        function(data) {
                            if (data == 'success') {
                                toastr.success('Status updated');
                            } else {
                                toastr.error('Unable to update teacher status');
                            }
                        }
                    );
                }
            });
        }
    });
});
</script>

<?= $this->endSection() ?>
