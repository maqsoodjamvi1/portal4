<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php $status = $_GET['status'] ?? ''; ?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"/>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 (optional, only if you use it) -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 (optional, only if you use it) -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>
<!-- Content Header -->


<!-- Main Content -->
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline shadow-sm">
      <div class="card-header pb-0">
        <ul class="nav nav-tabs card-header-tabs">
          <li class="nav-item"><a class="nav-link " href="<?= base_url('admin/addbulkstudents/add') ?>">Student Names</a></li>
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
          <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/students_bulk_contacts') ?>">Contact Numbers</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_bulk_info') ?>">Other Student Info</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/studentsbulkcsv/addbulk') ?>">Entries through Excel</a></li>

        </ul>
      </div>



<div class="p-3">
  <div class="form-row align-items-end">
    <div class="form-group col-md-6">
      <label for="cls_sec_id" class="font-weight-bold">Select Class</label>
      <select class="form-control select2" name="cls_sec_id" id="cls_sec_id">
  <option value="">All Classes</option>
  <?php if (!empty($sectionsclassinfo)) : ?>
    <?php foreach ($sectionsclassinfo as $row) : ?>
      <option value="<?= esc($row['cls_sec_id'] ?? $row['section_id']) ?>">
        <?= esc($row['sectionclassname'] ?? ($row['class_name'].' - '.$row['section_name'])) ?>
      </option>
    <?php endforeach; ?>
  <?php endif; ?>
</select>
    </div>
  </div>

  <div class="card shadow-sm mt-3">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0">Students Contact List</h5>
    </div>
    <div class="card-body">
      <div id="studentsList">
        <p class="text-muted">Please select a class to view students.</p>
      </div>
    </div>
  </div>
</div>
<!-- Loader -->
<div id="loader-1" class="text-center my-3 d-none">
  <div class="spinner-border text-primary" role="status">
    <span class="sr-only">Loading...</span>
  </div>
</div>

<style>
    table.table-bordered th:last-child,
  table.table-bordered td:last-child { width: 50px; }
  .input-group.input-group-sm .input-group-text {
    min-width: 34px; justify-content: center;
  }
  td.p-1 .input-group { margin-bottom: .25rem; }
 
</style>

<!-- AJAX Script -->

<script>
(function ($) {
  'use strict';

  const $cls    = $('#cls_sec_id');
  const $list   = $('#studentsList');
  const $loader = $('#loader-1');
  let loadXHR   = null;

  function initTooltips() {
    if ($.fn.tooltip) {
      $list.find('[data-toggle="tooltip"]').tooltip({
        container: 'body',
        trigger: 'hover'
      });
    }
  }

  function loadStudents() {
    const clsVal = $cls.val();

    // Abort any in-flight request
    if (loadXHR && loadXHR.readyState !== 4) {
      loadXHR.abort();
    }

    $loader.removeClass('d-none');

    loadXHR = $.ajax({
      url: "<?= base_url('admin/students_bulk_contacts/data') ?>",
      method: "POST",
      cache: false,
      dataType: "html",
      data: {
        cls_sec_id: clsVal,
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
      },
      success: function (html) {
        $list.html(html || '<div class="alert alert-warning m-0">No data.</div>');
        initTooltips();
      },
      error: function (xhr) {
        console.error('Load error:', xhr.responseText || xhr.statusText);
        $list.html('<div class="alert alert-danger m-0">Failed to load student list.</div>');
      },
      complete: function () {
        $loader.addClass('d-none');
      }
    });
  }

  function saveContacts($btn) {
    const $tr = $btn.closest('tr');
    const payload = {
      parent_id:         $btn.data('parent-id'),
      father_contact:    $tr.find('.father-contact').val(),
      mother_contact:    $tr.find('.mother-contact').val(),
      whatsapp_contact:  $tr.find('.whatsapp-contact').val(),
      emergency_contact: $tr.find('.emergency-contact').val(),
      '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
    };

    $.ajax({
      url: "<?= base_url('admin/students_bulk_contacts/savestudentcontacts') ?>",
      method: "POST",
      dataType: "json",
      data: payload,
      success: function (res) {
        if (res && res.success) {
          if (window.toastr) toastr.success(res.msg || 'Saved successfully');
          else alert(res.msg || 'Saved successfully');
        } else {
          const msg = (res && res.msg) || 'Save failed';
          if (window.toastr) toastr.error(msg); else alert(msg);
        }
      },
      error: function (xhr) {
        console.error('Save error:', xhr.responseText || xhr.statusText);
        if (window.toastr) toastr.error('Save failed'); else alert('Save failed');
      }
    });
  }

  $(function () {
    // Enhance select, if available
    if ($.fn.select2) $cls.select2({ width: '100%' });

    // Initial tooltip setup (for any static items)
    initTooltips();

    // Load when class/section changes
    $cls.on('change', loadStudents);

    // Delegate saving to container (works with dynamic rows)
    $list.on('click', '.save-contacts', function () {
      saveContacts($(this));
    });

    // Auto-load if a value is preselected
    if ($cls.val()) loadStudents();
  });

})(jQuery);
</script>

<?= $this->endSection() ?>
