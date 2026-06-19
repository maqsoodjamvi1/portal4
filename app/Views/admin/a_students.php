<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
$status = isset($_GET['status']) ? (int) $_GET['status'] : 1;
$statusTabs = [
    1 => 'Current',
    2 => 'Suspended',
    3 => 'Dropped',
    4 => 'Pending',
];
$currentStatusLabel = $statusTabs[$status] ?? 'Current';

ob_start();
?>
<a href="<?= esc(base_url('admin/a_students/add'), 'attr') ?>" class="btn btn-primary btn-sm">
    <i class="fas fa-user-plus me-1"></i> Add Student
</a>
<?php
$headerActions = trim(ob_get_clean());
?>

<?= view('components/page_header', [
    'title' => 'Student Directory',
    'subtitle' => 'Review enrollment status, family links, and fee shortcuts across the full student roster.',
    'icon' => 'fas fa-user-graduate',
    'actionsHtml' => $headerActions,
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Students', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="card sms-card sms-index-card card-primary card-outline card-tabs sms-list-surface">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-id-card-alt me-2"></i>
                        Student Records
                    </h3>
                    <div class="card-tools">
                        <span class="sms-data-chip">
                            <i class="fas fa-layer-group"></i>
                            <?= esc($currentStatusLabel) ?> students
                        </span>
                    </div>
                </div>

                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs sms-status-tabs">
                        <li class="nav-item">
                            <a class="nav-link<?= $status === 1 ? ' active' : '' ?>" href="#/a_students?status=1">Current</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?= $status === 2 ? ' active' : '' ?>" href="#/a_students?status=2">Suspended</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?= $status === 3 ? ' active' : '' ?>" href="#/a_students?status=3">Dropped</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?= $status === 4 ? ' active' : '' ?>" href="#/a_students?status=4">Pending</a>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <input type="hidden" id="status" value="<?= esc((string) $status, 'attr') ?>">

                    <form id="form-filter" class="sms-filter-bar">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-3 col-md-6">
                                <label for="student_id">Student</label>
                                <select class="form-control select2" name="student_id" id="student_id" style="width: 100%;">
                                    <option value="0">Select student</option>
                                </select>
                            </div>

                            <div class="col-xl-3 col-md-6">
                                <label for="parent_id">Parent</label>
                                <select class="form-control select2" name="parent_id" id="parent_id" style="width: 100%;">
                                    <option value="0">Select parent</option>
                                </select>
                            </div>

                            <div class="col-xl-3 col-md-6">
                                <label for="cls_sec_id">Class / Section</label>
                                <select class="form-control select2" name="cls_sec_id" id="cls_sec_id" style="width: 100%;">
                                    <option value="0">All classes</option>
                                    <?php if (isset($sectionsclassinfo) && is_array($sectionsclassinfo)) : ?>
                                        <?php foreach ($sectionsclassinfo as $sectionValue) : ?>
                                            <option value="<?= esc($sectionValue['section_id'] ?? '', 'attr') ?>">
                                                <?= esc($sectionValue['sectionclassname'] ?? '') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-xl-3 col-md-6">
                                <div class="sms-filter-actions">
                                    <button type="button" id="btn-filter" class="btn btn-primary">
                                        <i class="fas fa-filter me-1"></i> Apply
                                    </button>
                                    <button type="button" id="btn-reset" class="btn btn-outline-secondary">
                                        <i class="fas fa-undo me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="sms-section-note mb-3">
                        <i class="fas fa-info-circle"></i>
                        Use the status tabs and filters together to move quickly between current enrollment, pending admissions, and fee actions.
                    </div>

                    <table class="table table-bordered table-hover sms-table-compact" id="students-datatable" style="width: 100%;" data-sms-table-name="students">
                        <thead>
                            <tr>
                                <th width="56">#</th>
                                <th width="72">Picture</th>
                                <th>Reg No</th>
                                <th>Name</th>
                                <th>Father Name</th>
                                <th>Gender</th>
                                <th>Age</th>
                                <th>Class</th>
                                <th>Address</th>
                                <th width="160">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="makeCurrent" tabindex="-1" aria-labelledby="makeCurrentLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="makeCurrentLabel">Update Student Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <input type="hidden" name="studentID" id="studentID">

                    <div class="form-group mb-3">
                        <label for="discount" class="col-form-label">Discount</label>
                        <input type="text" class="form-control" name="discount" id="discount">
                    </div>

                    <div class="form-group mb-0">
                        <label for="cls_secID">Section <span class="required">*</span></label>
                        <select class="form-control select2" name="cls_secID" id="cls_secID" required="required" style="width: 100%;">
                            <option value="0">Select Section</option>
                            <?php if (isset($sectionsclassinfo) && is_array($sectionsclassinfo)) : ?>
                                <?php foreach ($sectionsclassinfo as $sectionValue) : ?>
                                    <option value="<?= esc($sectionValue['section_id'] ?? '', 'attr') ?>">
                                        <?= esc($sectionValue['sectionclassname'] ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="updateStatus" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </div>
</div>

<style>
    #students-datatable td:last-child,
    #students-datatable th:last-child {
        white-space: nowrap;
    }

    #students-datatable td img {
        max-width: 42px;
        max-height: 42px;
        border-radius: 10px;
        object-fit: cover;
    }
</style>

<script type="text/javascript">
$(function() {
    const currentStatus = <?= json_encode($status) ?>;

    var table = $('#students-datatable').DataTable({
        dom:
            "<'row mb-2'<'col-sm-6'B><'col-sm-6 text-end'i>>" +
            "<'row'<'col-12'tr>>" +
            "<'row mt-3'<'col-sm-6'l><'col-sm-6'p>>",
        buttons: [
            { extend: 'colvis', className: 'btn btn-outline-secondary btn-sm' },
            { extend: 'csv', className: 'btn btn-outline-secondary btn-sm' },
            { extend: 'excel', className: 'btn btn-outline-secondary btn-sm' },
            {
                extend: 'pdfHtml5',
                className: 'btn btn-outline-secondary btn-sm',
                exportOptions: {
                    columns: [0, 2, 3, 4, 5, 6, 7, 8]
                }
            }
        ],
        deferRender: true,
        responsive: true,
        autoWidth: false,
        processing: true,
        serverSide: true,
        ordering: false,
        order: [],
        pageLength: 100,
        searching: false,
        ajax: {
            url: '<?= base_url('admin/a_students/data') . '?status=' . $status ?>',
            type: 'post',
            data: function(d) {
                d.status = $('#status').val();
                d.student_id = $('#student_id').val();
                d.cls_sec_id = $('#cls_sec_id').val();
                d.parent_id = $('#parent_id').val();
            }
        },
        columns: [
            {
                data: 'id',
                className: 'text-center align-middle',
                render: function(data, type, row, meta) {
                    return meta.settings._iDisplayStart + meta.row + 1;
                }
            },
            { data: 'profile_photo', className: 'align-middle text-center' },
            { data: 'reg_no', className: 'align-middle' },
            { data: 'name', className: 'align-middle' },
            { data: 'f_name', className: 'align-middle' },
            { data: 'gender', className: 'align-middle' },
            { data: 'age', className: 'align-middle' },
            { data: 'class', className: 'align-middle' },
            { data: 'address', className: 'align-middle' },
            {
                data: 'id',
                sortable: false,
                className: 'align-middle text-center',
                render: function(data, type, row) {
                    var html = '';
                    html += '<div class="dropdown">';
                    html += '<button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">';
                    html += '<i class="fas fa-cog me-1"></i> Manage';
                    html += '</button>';
                    html += '<div class="dropdown-menu dropdown-menu-end">';
                    html += '<a href="<?php echo '#/a_students?m=edit&id='; ?>' + data + '" title="Edit" class="dropdown-item"><i class="fas fa-edit me-2 text-primary"></i>Edit details</a>';
                    html += '<a href="<?php echo '#/a_fee_chalan_single?m=add&id='; ?>' + data + '" title="Fee Chalan" class="dropdown-item"><i class="fas fa-file-invoice me-2 text-success"></i>Fee chalan</a>';
                    html += '</div></div>';
                    return html;
                }
            }
        ]
    });

    $('#btn-filter').click(function() {
        table.ajax.reload();
    });

    $('#btn-reset').click(function() {
        $('#student_id').val('0').trigger('change');
        $('#parent_id').val('0').trigger('change');
        $('#cls_sec_id').val('0').trigger('change');
        $('#status').val(String(currentStatus));
        table.ajax.reload();
    });

    $("#parent_id").select2({
        minimumInputLength: 2,
        tags: [],
        ajax: {
            url: 'admin.php?c=a_students&m=get_parentinfo',
            dataType: 'json',
            type: "POST",
            quietMillis: 50,
            data: function(term) {
                return {
                    term: term,
                };
            },
            processResults: function(response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    });

    $("#cls_sec_id").select2({ minimumInputLength: 0 });
    $("#cls_secID").select2({ minimumInputLength: 0, dropdownParent: $('#makeCurrent') });

    $("#student_id").select2({
        minimumInputLength: 2,
        tags: [],
        ajax: {
            url: 'admin.php?c=a_students&m=get_studentinfo',
            dataType: 'json',
            type: "POST",
            quietMillis: 50,
            data: function(term) {
                return {
                    term: term,
                    status: <?= json_encode($status) ?>
                };
            },
            processResults: function(response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    });
});
</script>

<script>
$('#updateStatus').click(function() {
    var studentID = $('#studentID').val();
    var discount = $('#discount').val();
    var cls_secID = $('#cls_secID').val();

    $.ajax({
        url: 'admin.php?c=ajax&m=a_updatestudentstatus',
        type: 'POST',
        data: { studentID: studentID, discount: discount, cls_secID: cls_secID },
        success: function() {
            $('#updateStatus').html('Updated Successfully');
            location.reload();
        }
    });
});

$('#makeCurrent').on('hidden.bs.modal', function() {
    location.reload();
});

$('#makeCurrent').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget);
    var discount = button.data('discount');
    var studentID = button.data('id');
    var modal = $(this);

    modal.find('#discount').val(discount);
    modal.find('#studentID').val(studentID);
});
</script>

<?= $this->endSection() ?>
