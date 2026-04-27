<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  $header = isset($info) ? 'Edit Day On Reset' : 'Add Day On Reset';
  $id     = $id ?? 0;

  // Expect: $infoschooltimingtypes (array of rows: type_id, type_name ...), $default_type_id
?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Day On Reset</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Day On Reset</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline">
        <div class="card-body">
          <?php
              echo form_open(base_url('admin/day_on_reset/save'), 'role="form" id="dayonreset-form"');
  echo form_hidden('id', (string)$id);
          ?>

        <!-- Keep a hidden input so form submits the value (optional but handy) -->
<input type="hidden" name="school_timing_type_id" value="<?= esc($default_type_id ?? 0) ?>">

<!-- Date selection -->
<div class="form-group row">
  <label for="reset_date" class="col-sm-3 col-form-label">Date</label>
  <div class="col-sm-6">
    <input type="date" id="reset_date" name="reset_date"
           class="form-control"
           value="<?= esc($selected_date ?? date('Y-m-d')) ?>">
  </div>
</div>

          <!-- Loader -->
          <div class="col-md-12 bg">
            <div id="loader-1" class="overlay text-center" style="display:none;">
              <i class="fas fa-2x fa-sync-alt fa-spin"></i>
            </div>
          </div>

          <!-- Injected table (checkboxes per class section) -->
          <div id="timetablearea" class="timetablearea"></div>

          <div class="form-group mt-3">
            <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
            <button type="reset" class="btn btn-default">Reset</button>
            <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
          </div>

          <?= form_close(); ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CSRF (for AJAX) -->
<meta name="csrf-token-name" content="<?= csrf_token() ?>">
<meta name="csrf-token" content="<?= csrf_hash() ?>">


<script>
  $(function () {
    // ---- CSRF helpers (reads the <meta> tags you added) ----
    function getCsrf() {
      var nameEl = document.querySelector('meta[name="csrf-token-name"]');
      var hashEl = document.querySelector('meta[name="csrf-token"]');
      return {
        name: nameEl ? nameEl.getAttribute('content') : '',
        hash: hashEl ? hashEl.getAttribute('content') : ''
      };
    }

    // ---- Bind Select All inside #timetablearea after HTML is injected ----
    function bindSelectAll() {
      var $wrap = $('#timetablearea');
      var $all  = $wrap.find('#select_all_sections, #select_all');
      $all.off('change').on('change', function () {
        $wrap.find('.section-check').prop('checked', this.checked);
      });
      $wrap.off('change', '.section-check').on('change', '.section-check', function () {
        var total = $wrap.find('.section-check').length;
        var ck    = $wrap.find('.section-check:checked').length;
        $all.prop('checked', total && total === ck);
      });
    }


    // ---- Load the sections table (checkboxes) ----
    function loadSections(typeId) {
      var csrf = getCsrf();
      var payload = {};
      if (csrf.name && csrf.hash) payload[csrf.name] = csrf.hash;

      // If you still have a timing type at backend, pass it; otherwise backend will fallback.
      if (typeId) payload['school_timing_type_id'] = typeId;

      // Date from picker
      var selDate = $('#reset_date').val();
      if (selDate) payload['date'] = selDate;   // <-- send date; server will compute day name

      $('#loader-1').show();
      $.ajax({
        url: "<?= base_url('admin/day_on_reset/data') ?>",
        type: "POST",
        data: payload,
        dataType: "html"
      })
      .done(function (res) {
        $('#timetablearea').html(res || "<div class='alert alert-info mb-0'>No sections found.</div>");
        bindSelectAll();
      })
      .fail(function (xhr) {
        console.error("AJAX /data failed", xhr.status, xhr.responseText);
        toastr.error("Could not load sections. (" + xhr.status + ")");
      })
      .always(function () { $('#loader-1').hide(); });
    }

    // ---- Initial auto-load (no dropdown). Backend can also fallback to first active type. ----
    var defaultType = "<?= esc($default_type_id ?? '') ?>";
    // Initial load with the date input’s current value
    loadSections(defaultType);

 $('#reset_date').on('change', function () {
      loadSections(defaultType);
    });
 
    // ---- AJAX form submit (expects pure JSON from controller) ----
    $('#dayonreset-form').ajaxForm({
      dataType: 'json',
      beforeSubmit: function () {
        $('#submitBtn').text('Saving').prop('disabled', true);
      },
      success: function (json) {
        $('#submitBtn').text('Save').prop('disabled', false);
        if (json && json.success) {
          toastr.success(json.msg || 'Saved.');
          window.location.href = "<?= base_url('admin/school_timing/add') ?>";
        } else {
          toastr.error((json && json.msg) ? json.msg : 'Save failed.');
        }
      },
      error: function (xhr) {
        $('#submitBtn').text('Save').prop('disabled', false);
        toastr.error('Save failed. (' + xhr.status + ')');
      }
    });
  });
</script>


<?= $this->endSection() ?>
