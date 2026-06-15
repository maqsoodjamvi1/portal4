<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php 
  $status = ''; 
  if(!empty($_GET['status'])){
   $status = $_GET['status']; 
  }
?>
<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<?= view('components/page_header', [
    'title' => 'Enroll Student',
    'icon' => 'fas fa-user-plus',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Students', 'url' => base_url('admin/students')],
        ['label' => 'Enroll', 'active' => true],
    ],
]) ?>
<?php ob_start(); ?>
<div class="form-group mb-0">
    <label for="cls_sec_id" class="report-label">Class section</label>
    <select class="form-control form-control-sm" name="cls_sec_id" id="cls_sec_id">
        <option value="">All Classes</option>
        <?php if (!empty($sectionsclassinfo)) : ?>
            <?php foreach ($sectionsclassinfo as $sectionvalue) : ?>
                <option value="<?= esc($sectionvalue['section_id']) ?>"><?= esc($sectionvalue['sectionclassname']) ?></option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>
</div>
<?php $filterBodyHtml = ob_get_clean(); ?>

<section class="content">
    <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
            <ul class="nav nav-tabs">
                <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/addbulkstudents/add') ?>">Student Names</a></li>
                <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/students_enroll') ?>">Enroll Students</a></li>
                <li class="nav-item"><a href="<?= base_url('admin/students_bulk_cnic') ?>" class="nav-link">Father Names</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/studentsbulk') ?>">Fee Detail</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_bulk_contacts') ?>">Contact Numbers</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_bulk_info') ?>">Other Student Info</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students/addbulk') ?>">Entries through Excel</a></li>
            </ul>
        </div>
        <div class="card-body pt-3">
            <?= view('components/filter_card', [
                'title' => 'Section',
                'bodyHtml' => $filterBodyHtml,
                'cardClass' => 'card sms-filter-card report-filter-card mb-3',
            ]) ?>
            <div id="studentsList"></div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
$(function(){
  $('#cls_sec_id').on('change', function() {  
  $("#loader-1").css("display", "block"); 
  var cls_sec_id = $('#cls_sec_id').val();
  $.ajax({
            url: '/admin/students_enroll/data', 
            type: "POST",
            data:{cls_sec_id:cls_sec_id},
            success:function(res){
             $("#studentsList").html(res);
             $("#loader-1").css("display", "none");
          }
      });
  });
});
</script>

<?= $this->endSection() ?>