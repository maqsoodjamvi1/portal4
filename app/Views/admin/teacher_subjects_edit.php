<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>
<?php
    $header = isset($info) ? 'Edit Subject Teacher' : 'Add Subject Teacher';
    $id = (string) ($info->sst ?? 0);
    $subject_id = (string) ($info->subject_id ?? '');
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Subjects Teacher</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Subjects Teacher</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
<div class="row">
<div class="col-lg-12">
<div class="card card-primary card-outline card-tabs">
<div class="card-header p-0 pt-1 border-bottom-0">
<ul class="nav nav-tabs">
    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/teacher_subjects') ?>">Subjects Teacher</a></li>
    <li class="nav-item"><a class="nav-link active" href="#"><?= esc($header) ?></a></li>
</ul>
<div class="card-body">
<div class="tab-content">

<?= form_open('admin/teacher_subjects/save', ['id' => 'teacher-form']) ?>
<?= form_hidden('id', $id) ?>

<div class="form-group">
    <label for="subject_id">Subjects</label>
    <select class="form-control" name="sub_id" id="subject_id" required>
        <option value="">Select Subject</option>
        <?php foreach ($subjectinfo ?? [] as $subject): ?>
            <option value="<?= esc($subject->sid) ?>" <?= $subject_id == $subject->sid ? 'selected' : '' ?>>
                <?= esc($subject->subject_name) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="col-md-12 bg">
    <div id="loader-1" class="overlay" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
</div>

<div class="teacher_subjects_table"></div>

<div class="form-group mt-3">
    <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
    <button type="reset" class="btn btn-secondary">Reset</button>
    <a href="<?= base_url('admin/teacher_subjects') ?>" class="btn btn-light">Cancel</a>
</div>

<?= form_close() ?>
</div>
</div>
</div>
</div>
</div>
</section>

<script>
$(function() {
    $("#subject_id").change(function() {
        var subject_id = $('#subject_id').val();
        $("#loader-1").show();
        $.post("<?= site_url('admin/teacher_subjects/data') ?>", { subject_id: subject_id }, function(res) {
            $(".teacher_subjects_table").html(res);
            $("#loader-1").hide();
        });
    });

    $('#teacher-form').submit(function(e) {
        e.preventDefault();
        const form = $(this);
        $("#submitBtn").prop('disabled', true).text('Saving...');
        $.post(form.attr('action'), form.serialize(), function(response) {
            $("#submitBtn").prop('disabled', false).text('Save');
            if (response.success) {
                toastr.success(response.msg);
                window.location.href = "<?= base_url('admin/teacher_subjects/add') ?>";
            } else {
                toastr.error(response.msg);
            }
        }, 'json');
    });
});
</script>

<?= $this->endSection() ?>

<?= $this->endSection() ?>