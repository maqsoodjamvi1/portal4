<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Defaulter Message',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Defaulter Message', 'active' => true],
    ],
]) ?>


<section class="content">
<div class="row">
<div class="col-lg-12">
  <div class="card card-primary card-outline card-tabs">
    <div class="card-header p-0 pt-1 border-bottom-0">
      <ul class="nav nav-tabs">
        <li class="nav-item">
          <a class="nav-link active" href="<?= site_url('admin/defaulter-message') ?>">Add Defaulter Message</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= site_url('admin/defaulter-message/parent_sms') ?>">Unique SMS to Parent</a>
        </li>
      </ul>
    </div>
    <div class="card-body">
      <form id="defaulter-message-form" method="post" action="<?= site_url('admin/defaulter-message/save') ?>">
        <div class="row mb-3">
          <div class="col-lg-3">
            <select class="form-control select2" id="month" name="month">
              <option value="">Select Fee Month</option>
              <?php foreach ($months as $month): ?>
                <option value="<?= esc($month['id']) ?>"><?= esc($month['value']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-lg-3">
            <select class="form-control select2" id="fee_type" name="fee_type">
              <option value="">Select Fee Type</option>
              <?php foreach ($fee_types as $fee): ?>
                <option value="<?= esc($fee->fee_type_id) ?>"><?= esc($fee->fee_type_name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-lg-2">
            <button type="button" onclick="submitFilter()" class="btn btn-primary w-100">View</button>
          </div>
        </div>

        <div class="col-md-12 bg">
          <div id="loader-1" class="overlay text-center" style="display: none;">
            <i class="fas fa-2x fa-sync-alt fa-spin"></i>
          </div>
        </div>

        <div id="defaultersList"></div>

        <div class="form-group mt-4">
          <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
          <button type="reset" class="btn btn-secondary">Reset</button>
          <button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>
</div>
</section>

<script>
function submitFilter() {
  const month = $('#month').val();
  const fee_type_id = $('#fee_type').val();

  $("#loader-1").show();

  $.ajax({
    url: "<?= site_url('admin/defaulter-message/data') ?>",
    type: "POST",
    data: { month: month, fee_type_id: fee_type_id },
    success: function (res) {
      $("#defaultersList").html(res);
      $("#loader-1").hide();
    },
    error: function () {
      toastr.error('Failed to load defaulter data.');
      $("#loader-1").hide();
    }
  });
}

$(function () {
  $('#defaulter-message-form').on('submit', function (e) {
    e.preventDefault();
    $('#submitBtn').text("Saving...").prop('disabled', true);

    $.post($(this).attr('action'), $(this).serialize(), function (res) {
      $('#submitBtn').text("Save").prop('disabled', false);
      if (res.success) {
        toastr.success(res.msg);
        location.reload();
      } else {
        toastr.error(res.msg || "An error occurred");
      }
    }, 'json');
  });
});
</script>

<?= $this->endSection() ?>
