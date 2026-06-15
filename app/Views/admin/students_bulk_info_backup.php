<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- Content Header -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2 align-items-center">
      <div class="col-sm-6">
        <h1>Student Other Info</h1>
      </div>
      <div class="col-sm-6 text-end">
        <ol class="breadcrumb float-sm-right bg-transparent p-0 m-0">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Other Student Info</li>
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
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/addbulkstudents/add') ?>">Student Names</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_enroll') ?>">Enroll Students</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_bulk_cnic') ?>">Father Names</a></li>
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
          <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/students_bulk_info') ?>">Other Student Info</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/studentsbulkcsv/addbulk') ?>">Entries through Excel</a></li>
        </ul>
      </div>

      <div class="p-3">
        <div class="col-lg-6 form-group">
          <label for="cls_sec_id"><strong>Class</strong></label>
          <select class="form-control" name="cls_sec_id" id="cls_sec_id">
            <option value="">All Classes</option>
            <?php if (!empty($sectionsclassinfo)) : ?>
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
        <div id="studentsList">
          <div class="text-center text-muted">Select a class to view students...</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Loader -->
<div id="loader-1" style="display:none;position:fixed;left:0;top:0;width:100vw;height:100vh;z-index:9999;background:rgba(255,255,255,0.7);">
  <div style="position:absolute;top:45%;left:50%;transform:translate(-50%,-50%);">
    <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
    <div>Loading...</div>
  </div>
</div>

<script>
$(function () {
  $('#cls_sec_id').on('change', function () {
    $("#loader-1").show();
    const cls_sec_id = $(this).val();
    $.ajax({
      url: "<?= base_url('admin/students_bulk_info/data') ?>",
      type: "POST",
      data: {
        cls_sec_id: cls_sec_id,
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
      },
      success: function (res) {
        $("#studentsList").html(res);
        $("#loader-1").hide();
      },
      error: function () {
        alert("Failed to load student info.");
        $("#loader-1").hide();
      }
    });
  });

  $('#studentsList').on('click', '.saveStudentBtn', function () {
    let row = $(this).closest('tr');
    let formData = new FormData();

    formData.append('student_id', row.find('[name="student_id"]').val());
    formData.append('date_of_birth', row.find('[name="date_of_birth"]').val());
    formData.append('gender', row.find('[name="gender"]').val());
    formData.append('daycare_flag', row.find('[name="daycare_flag"]').val());

    let imageInput = row.find('[name="image"]')[0];
    if (imageInput.files.length > 0) {
      formData.append('image', imageInput.files[0]);
    }

    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    $.ajax({
      url: "<?= base_url('admin/students_bulk_info/save_student_info') ?>",
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      beforeSend: function () {
        $("#loader-1").show();
      },
      success: function (res) {
        $("#loader-1").hide();
        if (res.success) {
          toastr.success(res.msg);
        } else {
          toastr.error("Error saving student info.");
        }
      },
      error: function () {
        $("#loader-1").hide();
        toastr.error("AJAX error.");
      }
    });
  });
});
</script>

<?= $this->endSection() ?>

