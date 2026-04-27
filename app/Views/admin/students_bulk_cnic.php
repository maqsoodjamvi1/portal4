<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php 
  $status = $_GET['status'] ?? ''; 
?>



<!-- Content Header -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2 align-items-center">
      <div class="col-sm-6">
        <h1>Student Names</h1>
      </div>
      <div class="col-sm-6 text-right">
        <ol class="breadcrumb float-sm-right bg-transparent p-0 m-0">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Student Names</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<!-- Main Content -->
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline shadow-sm">
      <div class="card-header pb-0">
        <ul class="nav nav-tabs card-header-tabs">
          <li class="nav-item"><a class="nav-link " href="<?= base_url('admin/addbulkstudents/add') ?>">Student Names</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_enroll') ?>">Enroll Students</a></li>
          <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/students_bulk_cnic') ?>">Father Names</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/studentsbulk') ?>">Fee Detail</a></li>
          <?php if (!empty($campus_info->a_flag)) : ?>
            <li class="nav-item"><a class="nav-link" href="#/students_bulk_academy_fee">Academy Fee Detail</a></li>
          <?php endif; ?>
          <?php if (!empty($campus_info->h_flag)) : ?>
            <li class="nav-item"><a class="nav-link" href="#/h_student_beds?m=add">Student Bed</a></li>
          <?php endif; ?>
          <?php if (!empty($campus_info->t_flag)) : ?>
            <li class="nav-item"><a class="nav-link" href="#/students_vehicle">Students Vehicle</a></li>
          <?php endif; ?>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_bulk_contacts') ?>">Contact Numbers</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_bulk_info') ?>">Other Student Info</a></li>
           <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/studentsbulkcsv/addbulk') ?>">Entries through Excel</a></li>

        </ul>
      </div>

        <div class="p-3">
          <div class="col-lg-6 form-group">
            <label for="cls_sec_id"><strong>Class</strong></label>
            <select class="form-control" name="cls_sec_id" id="cls_sec_id">
              <option value="">All Classes</option>
              <?php if (isset($sectionsclassinfo)) : ?>
                <?php foreach ($sectionsclassinfo as $sectionvalue) : ?>
                  <option value="<?= esc($sectionvalue['section_id']) ?>">
                    <?= esc($sectionvalue['sectionclassname']) ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
        </div>

        <div class="card-body">
          <div id="studentsList"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
  table.table-bordered th:last-child,
  table.table-bordered td:last-child {
    width: 50px;
  }
</style>

<script type="text/javascript">
  $(function () {
    $('#cls_sec_id').on('change', function () {
      var cls_sec_id = $('#cls_sec_id').val();
      $("#loader-1").show();

      $.ajax({
        url: "<?= base_url('admin/students_bulk_cnic/data') ?>",
        type: "POST",
        data: {
          cls_sec_id: cls_sec_id,
          <?= csrf_token() ?>: "<?= csrf_hash() ?>"
        },
        success: function (res) {
          $("#studentsList").html(res);
          $("#loader-1").hide();
        },
        error: function () {
          alert("Something went wrong while fetching data.");
          $("#loader-1").hide();
        }
      });
    });
  });
</script>

<?= $this->endSection() ?>
