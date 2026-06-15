<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'School Timing',
    'icon' => 'fas fa-clock',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'School Timing', 'url' => base_url('admin/school_timing')],
        ['label' => 'Edit', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline">
        <div class="card-body">
          <?= form_open(base_url('admin/school_timing/save'), ['id' => 'user-edit-form']) ?>

          <div class="position-relative mb-3">
            <div id="loader-1" class="overlay text-center" style="display: none;">
              <i class="fas fa-2x fa-sync-alt fa-spin"></i>
            </div>
            <div id="timetablearea" class="table-responsive"></div>
          </div>

          <div class="form-group mb-0">
            <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
            <button type="reset" class="btn btn-secondary">Reset</button>
            <button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
          </div>

          <?= form_close() ?>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
$(function () {
  const CSRF_NAME = '<?= csrf_token() ?>';
  const CSRF_HASH = '<?= csrf_hash() ?>';

  function loadTimingGrid() {
    $('#loader-1').show();
    $.ajax({
      url: '<?= base_url('admin/school_timing/data') ?>',
      type: 'POST',
      data: {
        [CSRF_NAME]: CSRF_HASH
      },
      success: function (res) {
        $('#timetablearea').html(res);
      },
      error: function () {
        toastr.error('Failed to load school timing grid.');
      },
      complete: function () {
        $('#loader-1').hide();
      }
    });
  }

  loadTimingGrid();

  $('#user-edit-form').ajaxForm({
    beforeSubmit: function () {
      $('#submitBtn').prop('disabled', true).text('Saving...');
    },
    success: function (responseText) {
      $('#submitBtn').prop('disabled', false).text('Save');
      const json = typeof responseText === 'string' ? JSON.parse(responseText) : responseText;
      if (json.success) {
        toastr.success(json.msg);
        loadTimingGrid();
      } else {
        toastr.error(json.msg || 'Save failed.');
      }
    },
    error: function () {
      $('#submitBtn').prop('disabled', false).text('Save');
      toastr.error('Failed to save school timing.');
    }
  });
});
</script>

<?= $this->endSection() ?>