<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>" />
<!-- Content Header (Page header) -->
<style>
    .list-group-item{
        width: 33% !important;
        float: left !important;
        padding: 1px 10px !important;
        border-right: 0 none;
        border-left: 0 none;
    }
    table{
        background-color: transparent;
        border: 2px solid #000;
        margin-top: 2px;
        float: left;
    }
    .table-bordered>thead>tr>th, .table-bordered>tbody>tr>th, .table-bordered>tfoot>tr>th,
    .table-bordered>thead>tr>td, .table-bordered>tbody>tr>td, .table-bordered>tfoot>tr>td{
        border:1px solid #333;
        padding: 0 4px !important;
        font-size: 12px;
        line-height: 30px !important;
        text-align: center;
    }
    .heading2{
        border:2px solid #000;float:left;width:100%;background:#800000;text-align:center;font-weight:bold;padding: 5px;font-size: 18px;color: #fff;line-height: 20px;
    }
    .heading{
        border:2px solid #000;float:left;width:100%;text-align:center;font-weight:bold;padding: 5px; background:#800000;font-size: 18px;color: #fff;line-height: 20px;
    }
    @media print {
        body {-webkit-print-color-adjust: exact;}
        .heading{
            border:2px solid #000;float:left;width:100%;text-align:center;font-weight:bold;padding: 5px; background:maroon;font-size: 18px;color: #fff;line-height: 20px;background-color: #800000 !important;
            -webkit-print-color-adjust: exact;
        }
        .heading2{
            border:2px solid #000;float:left;width:100%;background:maroon;text-align:center;font-weight:bold;padding: 5px;font-size: 18px;color: #fff;line-height: 20px;background-color: #800000 !important;
            -webkit-print-color-adjust: exact;
        }

        .no-print,.nav-tabs,.main-footer,.no-print * {
            display: none !important;
        }
    }
</style>
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>
                    Students Results
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Students Results</li>
                </ol>
            </div>
        </div>
    </div>
</section>
<!-- Main content -->
<section class="content"> 
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-primary card-outline card-tabs" style="background: #fff !important;">
                <div class="card-header p-0 pt-1 border-bottom-0"></div>
                <div class="card-body">
                    <div class="no-print">
                        <div class="col-lg-12">
                            <label for="class"><strong>Academic Result</strong></label><br>
                            <ul class="list-group list-group-horizontal">
                                <li class="list-group-item">
                                    <div class="icheck-primary d-inline">
                                        <input type="checkbox" class="academic_results" id="marks" name="academic_result[]" value="marks"><label for="marks"> Marks</label>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="icheck-primary d-inline">
                                        <input type="checkbox" class="academic_results" id="percentage" name="academic_result[]" value="percentage"><label for="percentage"> Percentage</label>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="icheck-primary d-inline">
                                        <input type="checkbox" class="academic_results" id="grade" name="academic_result[]" value="grade"><label for="grade"> Grade</label>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="icheck-primary d-inline">
                                        <input type="checkbox" class="academic_results" id="position" name="academic_result[]" value="position"><label for="position"> Position</label>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="icheck-primary d-inline">
                                        <input type="checkbox" class="academic_results" id="subject_remarks" name="academic_result[]" value="subject_remarks"><label for="subject_remarks"> Subject Remarks</label>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="icheck-primary d-inline">
                                        <input type="checkbox" class="academic_results" id="total_remarks" name="academic_result[]" value="total_remarks"><label for="total_remarks"> Total Remarks</label>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="col-lg-12">
                            <label for="class"><strong>Exams</strong></label><br>
                            <ul class="list-group list-group-horizontal">
                                <?php foreach ($exams as $key => $exam) { ?>
                                    <li class="list-group-item">
                                        <div class="icheck-primary d-inline">
                                            <input type="checkbox" class="examids" id="eid<?= esc($exam->eid) ?>" required="required" name="exam_id" value="<?= esc($exam->eid) ?>">
                                            <label for="eid<?= esc($exam->eid) ?>"><?= esc($exam->exam_name) ?></label>
                                        </div>
                                    </li>
                                <?php } ?>
                            </ul>
                            <div class="row">
                                <div class="col-lg-6 form-group">
                                    <label for="class"><strong>Class</strong></label><br>
                                    <select class="form-control" name="cls_sec_id" id="cls_sec_id">
                                        <option value="">Select Class</option>
                                        <?php if (isset($sectionsclassinfo)) {
                                            foreach ($sectionsclassinfo as $sectionvalue) { ?>
                                                <option value="<?= esc($sectionvalue['section_id']) ?>"><?= esc($sectionvalue['sectionclassname']) ?></option>
                                        <?php }
                                        } ?>
                                    </select>
                                </div>
                                <div class="col-lg-6 form-group">
                                    <label for="class"><strong>Subject</strong></label><br>
                                    <select class="form-control select2" name="subject_id" id="subject_id" style="height: 24px;width: 100%;">
                                        <option value="0">All Subjects</option>
                                        <?php foreach ($allsubject as $key => $subject) { ?>
                                            <option value="<?= esc($subject->sid) ?>"><?= esc($subject->subject_name) ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <input style="line-height: 19px;margin: 10px 0px;" type="button" class="btn btn-primary pull-right" value="View Result Card" name="View" id="ViewResutlt">
                        </div>
                    </div>
                    <div id="loader-1" class="overlay col-md-12 text-center" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
                    <div id="resultContainer"></div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->
<script src="<?= base_url('resource/bootstrap-switch/js/bootstrap-switch.min.js') ?>"></script>
<script>
$(function() {
    $('#cls_sec_id').on('change', function() {
        var section_id = $("#cls_sec_id").val();
        $.ajax({
            url: "<?= base_url('admin/ajax/selectsubjectbySection') ?>",
            type: "POST",
            data: { section_id: section_id },
            success: function(res) {
                $("#subject_id").html(res);
            }
        });
    });

    $('#ViewResutlt').on('click', function() {
        $("#loader-1").css("display", "block");
        var academic_result = [];
        var examids = [];
        var cls_sec_id = $('#cls_sec_id').val();
        var subject_id = $('#subject_id').val();

        $(".academic_results:checked").each(function() {
            academic_result.push($(this).val());
        });

        $(".examids:checked").each(function() {
            examids.push($(this).val());
        });

        $.ajax({
            url: "<?= base_url('admin/classwise_results/data') ?>",
            type: "POST",
            data: {
                academic_result: academic_result,
                examids: examids,
                cls_sec_id: cls_sec_id,
                subject_id: subject_id
            },
            success: function(res) {
                $("#resultContainer").html(res);
                $("#loader-1").css("display", "none");
            }
        });
    });
});
</script>

<?= $this->endSection() ?>
