<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
$status = $_GET['status'] ?? '';
?>
<link rel="stylesheet" href="<?= base_url('resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>" />

<?= view('components/page_header', [
    'title' => 'Students Contact List',
    'icon' => 'fas fa-address-book',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Students Contact List', 'active' => true],
    ],
]) ?>

<?php ob_start(); ?>
<div class="row">
    <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
        <label for="student_id" class="report-label">Student</label>
        <select class="form-control form-control-sm select2" name="student_id" id="student_id" style="width:100%;">
            <option value="0">Select Student</option>
        </select>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
        <label for="parent_id" class="report-label">Parent</label>
        <select class="form-control form-control-sm select2" name="parent_id" id="parent_id" style="width:100%;">
            <option value="0">Select Parent</option>
        </select>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
        <label for="cls_sec_id" class="report-label">Section</label>
        <select class="form-control form-control-sm select2" name="cls_sec_id" id="cls_sec_id" required style="width:100%;">
            <option value="0">Select Section</option>
            <?php if (!empty($sectionsclassinfo)) : ?>
                <?php foreach ($sectionsclassinfo as $secionvalue) : ?>
                    <option value="<?= esc($secionvalue['section_id']) ?>"><?= esc($secionvalue['sectionclassname']) ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
        <label for="test_id" class="report-label">Test Series</label>
        <select name="test_id" id="test_id" class="form-control form-control-sm">
            <?php foreach ($test_series as $test) : ?>
                <option value="<?= (int) $test->t_series_id ?>"><?= esc($test->series_name) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-lg-3 col-md-8 col-sm-12 mb-2 d-flex align-items-end">
        <button type="button" id="btn-filter" class="btn btn-primary btn-sm me-1">Filter</button>
        <button type="button" id="btn-reset" class="btn btn-secondary btn-sm">Reset</button>
    </div>
</div>
<?php $filterBodyHtml = ob_get_clean(); ?>

<?php ob_start(); ?>
<table class="table table-striped table-bordered table-hover mb-0" id="students-datatable" width="100%" style="font-size:13px;">
    <thead>
        <tr style="vertical-align: middle;">
            <th nowrap>#</th>
            <th nowrap>Name</th>
            <th nowrap>Send</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>
<?php $tableHtml = ob_get_clean(); ?>

<section class="content">
    <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
            <ul class="nav nav-tabs">
                <li class="nav-item"><a class="nav-link <?= $status == 1 ? 'active' : '' ?>" href="#/students_contact_list?status=1">Current</a></li>
                <li class="nav-item"><a class="nav-link <?= $status == 2 ? 'active' : '' ?>" href="#/students_contact_list?status=2">Suspended</a></li>
                <li class="nav-item"><a class="nav-link <?= $status == 3 ? 'active' : '' ?>" href="#/students_contact_list?status=3">Dropped</a></li>
                <li class="nav-item"><a class="nav-link <?= $status == 4 ? 'active' : '' ?>" href="#/students_contact_list?status=4">Pending</a></li>
            </ul>
        </div>
        <div class="card-body pt-3">
            <?= view('components/filter_card', [
                'title' => 'Filters',
                'bodyHtml' => $filterBodyHtml,
                'cardClass' => 'card sms-filter-card report-filter-card mb-3',
            ]) ?>
            <?= view('components/data_table_card', [
                'title' => 'Student Contacts',
                'icon' => 'fas fa-list',
                'tableHtml' => $tableHtml,
                'cardClass' => 'card sms-card mb-0',
            ]) ?>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?>
<style>
  table.table-bordered th:last-child,
  table.table-bordered td:last-child { width: 50px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
$(function(){
    var table = $('#students-datatable').DataTable({
        dom: 'Bfrtip',
        buttons: ['colvis', 'csv', 'excel', {
            extend: 'pdfHtml5',
            exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] }
        }],
        deferRender: true,
        select: { style: 'single', blurable: true },
        processing: true,
        serverSide: true,
        ordering: false,
        order: [],
        pageLength: 200,
        searching: false,
        ajax: {
            url: '<?= base_url('admin/students_list/data') ?>?status=<?= (int) ($_GET['status'] ?? 1) ?>&test_id=' + $('#test_id').val(),
            type: 'post',
            data: function(d) {
                d.status = $('#status').val();
                d.student_id = $('#student_id').val();
                d.cls_sec_id = $('#cls_sec_id').val();
                d.parent_id = $('#parent_id').val();
                d.test_id = $('#test_id').val();
            }
        },
        columns: [
            { data: 'id', className: 'select-checkbox', render: function(data) { return data; } },
            { data: 'name', className: 'select-checkbox', render: function(data, type, row) {
                return data + ' (' + row.class + ') C/O ' + row.f_name;
            }},
            { data: 'w_contacts' }
        ]
    });

    $('#btn-filter').click(function() { table.ajax.reload(); });
    $('#btn-reset').click(function() {
        $('#student_id').val('0').trigger('change');
        $('#parent_id').val('0').trigger('change');
        $('#cls_sec_id').prop('selectedIndex', 0);
        table.ajax.reload();
    });

    $('#parent_id').select2({
        minimumInputLength: 2,
        ajax: {
            url: 'admin.php?c=students_contact_list&m=get_parentinfo',
            dataType: 'json',
            type: 'POST',
            delay: 50,
            data: function(term) { return { term: term }; },
            processResults: function(response) { return { results: response }; },
            cache: true
        }
    });

    $('#student_id').select2({
        minimumInputLength: 2,
        ajax: {
            url: 'admin.php?c=students_contact_list&m=get_studentinfo',
            dataType: 'json',
            type: 'POST',
            delay: 50,
            data: function(term) {
                return { term: term, status: <?= (int) ($_GET['status'] ?? 0) ?> };
            },
            processResults: function(response) { return { results: response }; },
            cache: true
        }
    });
});
</script>
<?= $this->endSection() ?>
