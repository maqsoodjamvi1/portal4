<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<style>
.table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
    padding: 4px 8px !important;
    vertical-align: middle !important;
}
.page {page-break-after: always;} 
th{text-align: center;}
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Datesheet</h1>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="<?= base_url('admin/datesheet_classwise') ?>" method="get">
                        <div class="row">
                            <div class="col-lg-4 form-group">
                                <label><strong>Select Class</strong></label>
                                <select class="form-control" name="cls_sec_id">
                                    <option value="">All Classes</option>
                                    <?php foreach ($sectionsclassinfo as $sectionvalue): ?>
                                        <option value="<?= $sectionvalue['section_id'] ?>" <?= ($cls_sec_id ?? '') == $sectionvalue['section_id'] ? 'selected' : '' ?>>
                                            <?= $sectionvalue['sectionclassname'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-lg-2 form-group" style="margin-top: 30px;">
                                <button type="submit" class="btn btn-primary">Show Datesheet</button>
                            </div>
                        </div>
                    </form>

                    <?php if (!empty($data)): ?>
                        <div id="printJS-form">
                            <?php foreach ($data as $classData): ?>
                                <div class="page">
                                    <div style="text-align: center; margin-bottom: 20px;">
                                        <h2 style="margin: 5px 0;"><?= htmlspecialchars($classData['campus_name']) ?></h2>
                                        <?php if (!empty($classData['campus_location'])): ?>
                                            <h4 style="margin: 5px 0;"><?= htmlspecialchars($classData['campus_location']) ?></h4>
                                        <?php endif; ?>
                                        <span style="margin: 5px 0;font-size: 13;"><?= htmlspecialchars($classData['terms'] ?? '') ?></span>
                                        <span style="margin: 10px 0;font-size: 13;">Class: <?= htmlspecialchars($classData['class'] ?? '') ?></span>
                                    </div>

                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th style="width:120px;">Date</th>
                                                <th>Day</th>
                                                <th style="width:120px;">Subject</th>
                                                <th>Marks</th>
                                                <th>Syllabus</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($classData['datesheetbysubject'] as $subject): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($subject['exam_date'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($subject['dayOfWeek'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($subject['subjectname'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($subject['total_marks'] ?? '') ?></td>
                                                    <td><?= strip_tags($subject['syllabus'] ?? '') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No datesheet found for selected criteria</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>