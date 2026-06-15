<?php $uiNeedsDataTables = false; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
$campus_id  = $sessionData['campusid'] ?? '';
$session_id = $sessionData['sessionid'] ?? '';

$csrfName  = csrf_token();
$csrfHash  = csrf_hash();
?>

<style>
.verticalTableHeader{padding:0!important;}
.table>tbody>tr>th{border:0!important;}
.table-bordered>thead>tr>th,
.table-bordered>tbody>tr>th,
.table-bordered>tfoot>tr>th,
.table-bordered>thead>tr>td,
.table-bordered>tbody>tr>td,
.table-bordered>tfoot>tr>td{
    border:1px solid #000!important;
}
.table>tbody>tr>td,
.table>tbody>tr>th,
.table>tfoot>tr>td,
.table>tfoot>tr>th,
.table>thead>tr>td,
.table>thead>tr>th{
    padding:1px!important;
    vertical-align:middle;
    text-align:center;
}
.loader{
    display:flex;
    gap:6px;
    justify-content:center;
    align-items:center
}
.loader span{
    width:8px;height:8px;
    background:#007bff;
    border-radius:50%;
    animation:b 1s infinite alternate
}
.loader span:nth-child(2){animation-delay:.15s}
.loader span:nth-child(3){animation-delay:.3s}
.loader span:nth-child(4){animation-delay:.45s}
@keyframes b{from{transform:translateY(0)}to{transform:translateY(-8px)}}

/* Prevent page-level horizontal scroll; keep scroll inside report area only */
.content-wrapper,
.content,
.card-body{
    overflow-x:hidden;
}
#students_list_container{
    width:100%;
    max-width:100%;
    overflow-x:auto;
}
</style>

<?= view('components/page_header', [
    'title' => 'Monthly Attendance Report',
    'icon' => 'fas fa-calendar-alt',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Monthly Attendance Report', 'active' => true],
    ],
]) ?>

<section class="content">
<div class="row">
<div class="col-lg-12">
<div class="card card-primary card-outline card-tabs">
<div class="card-body">

<div class="row">
<div class="col-md-12">
<div class="loader" id="loader-1" style="display:none;">
    <span></span><span></span><span></span><span></span>
</div>
</div>

<input type="hidden" id="campus_id" value="<?= esc($campus_id) ?>">

<div class="col-lg-12">
<?php
$monthlyClassOptions = [['value' => '', 'label' => 'Select Class']];
$monthlySectionOptions = [['value' => '', 'label' => 'All Sections of selected class']];
$monthlySectionsMeta = [];
if (!empty($sectionsclassinfo)) {
    foreach ($sectionsclassinfo as $row) {
        $classId = $row['class_id'] ?? null;
        $className = $row['class_name'] ?? '';
        if ($classId !== null && $className !== '') {
            $monthlyClassOptions[(string)$classId] = ['value' => $classId, 'label' => $className];
        }

        $clsSecId = $row['cls_sec_id'] ?? $row['section_id'];
        $monthlySectionsMeta[] = [
            'value' => $clsSecId,
            'label' => $row['sectionclassname'],
            'class_id' => $classId,
        ];
        $monthlySectionOptions[] = [
            // Always send class_section id; some helpers also include raw section_id.
            'value' => $clsSecId,
            'label' => $row['sectionclassname'],
        ];
    }
}
$monthlyClassOptions = array_values($monthlyClassOptions);
echo view('components/report_filter_bar', [
    'formId' => 'monthlyAttendanceFilterForm',
    'title' => 'Attendance Filters',
    'method' => 'post',
    'fields' => [
        [
            'type' => 'select',
            'id' => 'class_id',
            'name' => 'class_id',
            'label' => 'Class',
            'class' => 'form-control report-select2',
            'options' => $monthlyClassOptions,
            'col_class' => 'col-md-4 mb-2',
        ],
        [
            'type' => 'select',
            'id' => 'section_id',
            'name' => 'section_id',
            'label' => 'Section',
            'class' => 'form-control report-select2',
            'options' => $monthlySectionOptions,
            'col_class' => 'col-md-4 mb-2',
        ],
        [
            'type' => 'month',
            'id' => 'date',
            'name' => 'date',
            'label' => 'Month',
            'value' => date('Y-m'),
            'class' => 'form-control',
            'col_class' => 'col-md-4 mb-2',
        ],
    ],
    'actions' => [],
]);
?>
</div>
</div>

<div class="row mt-3">
<div class="col-lg-12">
<div id="students_list_container"></div>
</div>
</div>

</div>
</div>
</div>
</div>
</section>

<script>
function getstudents(){
    var class_id   = $('#class_id').val();
    var section_id = $('#section_id').val();
    var campus_id  = $('#campus_id').val();
    var date       = $('#date').val();

    if(!class_id){
        alert('Please select class');
        return;
    }

    $("#loader-1").show();

    $.ajax({
        url: "<?= base_url('admin/attendance-monthly-report/get-students-byclass') ?>",
        type: "POST",
        data: {
            <?= $csrfName ?>: "<?= $csrfHash ?>",
            class_id: class_id,
            section_id: section_id,
            campus_id: campus_id,
            date: date
        },
        success: function(res){
            $("#students_list_container").html(res);
        },
        error: function(){
            alert('Failed to load attendance report');
        },
        complete: function(){
            $("#loader-1").hide();
        }
    });
}

$(function(){
    var sectionsMeta = <?= json_encode($monthlySectionsMeta) ?>;
    var $section = $('#section_id');
    function populateSectionsByClass(classId) {
        $section.empty();
        $section.append('<option value="">All Sections of selected class</option>');
        if (!classId) return;
        sectionsMeta.forEach(function (item) {
            if (String(item.class_id) === String(classId)) {
                $section.append('<option value="' + item.value + '">' + item.label + '</option>');
            }
        });
    }

    if (window.ReportUI && window.ReportUI.initReportSelects) {
        window.ReportUI.initReportSelects('#monthlyAttendanceFilterForm');
    } else if ($.fn.select2) {
        $('#class_id, #section_id').select2({ width: '100%' });
    }

    var autoLoadMonthly = function () {
        var class_id = $('#class_id').val();
        var section_id = $('#section_id').val();
        var date = $('#date').val();
        if (class_id && date) {
            getstudents();
        }
    };

    $('#class_id').on('change', function() {
        populateSectionsByClass($(this).val());
        if ($.fn.select2) {
            $section.trigger('change.select2');
        }
        autoLoadMonthly();
    });
    $('#section_id').on('change', autoLoadMonthly);
    $('#date').on('change', autoLoadMonthly);
});
</script>

<?= $this->endSection() ?>