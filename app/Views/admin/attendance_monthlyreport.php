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
</style>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Students Attendance Report</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Students Attendance Report</li>
        </ol>
      </div>
    </div>
  </div>
</section>

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
<div class="d-flex justify-content-center align-items-end">

<div class="form-group mr-3">
<label>Sections</label>
<select class="form-control select2"
        name="section_id"
        id="section_id"
        style="height:32px;padding:0 8px;min-width:220px">
<option value="">Select Section</option>
<?php if (!empty($sectionsclassinfo)): ?>
<?php foreach ($sectionsclassinfo as $row): ?>
<option value="<?= esc($row['section_id']) ?>">
    <?= esc($row['sectionclassname']) ?>
</option>
<?php endforeach ?>
<?php endif ?>
</select>
</div>

<div class="form-group mr-3">
<label>Month</label>
<input type="month"
       id="date"
       value="<?= esc(date('Y-m')) ?>"
       class="form-control"
       style="height:32px">
</div>

<div class="form-group">
<button type="button"
        onclick="getstudents()"
        class="btn btn-sm btn-primary"
        style="margin-top:24px;height:32px">
View
</button>
</div>

</div>
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
    var section_id = $('#section_id').val();
    var campus_id  = $('#campus_id').val();
    var date       = $('#date').val();

    if(!section_id){
        alert('Please select section');
        return;
    }

    $("#loader-1").show();

    $.ajax({
        url: "<?= base_url('admin/attendance-monthly-report/get-students-byclass') ?>",
        type: "POST",
        data: {
            <?= $csrfName ?>: "<?= $csrfHash ?>",
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
    $('.select2').select2();
});
</script>

<?= $this->endSection() ?>