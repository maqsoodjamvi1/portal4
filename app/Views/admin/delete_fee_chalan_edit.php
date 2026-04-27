<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Delete Fee Chalan</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Delete Fee Chalan</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
          <ul class="nav nav-tabs">
            <li class="nav-item">
              <a class="nav-link active" href="<?= base_url('admin/delete-fee-chalan') ?>">Delete Fee Chalan</a>
            </li>
          </ul>
        </div>
        <div class="card-body">
          <div class="tab-content">
            <?php if (!empty($studentFeeDetail)): ?>
              <?= form_open('admin/delete-fee-chalan/delete', ['id' => 'delete-form', 'class' => 'form-horizontal']) ?>
                
                <div class="table-responsive">
                  <table class="table table-bordered table-striped table-hover">
                    <thead class="bg-primary">
                      <tr>
                        <th width="50"><input type="checkbox" id="select-all"></th>
                        <th>ID</th>
                        <th>Student Name</th>
                        <th>Fee Type</th>
                        <th>Fee Month</th>
                        <th>Amount</th>
                        <th>Created Date</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($studentFeeDetail as $row): ?>
                        <tr>
                          <td class="text-center">
                            <input type="checkbox" name="chalan_ids[]" value="<?= $row['chalan_id'] ?>">
                          </td>
                          <td><?= $row['chalan_id'] ?></td>
                          <td><?= esc($row['student_name']) ?></td>
                          <td>
                            <span class="badge badge-info">
                              <?= esc($row['fee_type_name'] ?? 'N/A') ?>
                            </span>
                          </td>
                          <td>
                            <?php 
                            // Format fee month for display
                            $feeMonth = $row['fee_month'] ?? '';
                            if (!empty($feeMonth)) {
                                // Handle different date formats
                                if (strpos($feeMonth, '-') !== false) {
                                    $parts = explode('-', $feeMonth);
                                    if (strlen($parts[0]) == 4) {
                                        // YYYY-MM format
                                        echo date('M Y', strtotime($feeMonth . '-01'));
                                    } else {
                                        // MM-YYYY format
                                        echo $parts[0] . '/' . $parts[1];
                                    }
                                } else {
                                    echo $feeMonth;
                                }
                            } else {
                                echo 'N/A';
                            }
                            ?>
                          </td>
                          <td class="text-right"><?= number_format($row['amount'], 0) ?></td>
                          <td><?= date('d-m-Y H:i', strtotime($row['created_date'])) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>

                <div class="row mt-3">
                  <div class="col-md-6">
                    <div class="alert alert-info mb-0">
                      <i class="fas fa-info-circle"></i> 
                      <strong>Total Selected:</strong> <span id="selected-count">0</span> chalans
                    </div>
                  </div>
                  <div class="col-md-6 text-right">
                    <button type="submit" id="submitBtn" class="btn btn-danger">
                      <i class="fas fa-trash"></i> Delete Selected Fee Chalans
                    </button>
                    <button type="button" class="btn btn-default" onclick="window.location.href='<?= base_url('admin/delete-fee-chalan') ?>'">
                      <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button type="button" class="btn btn-default" onclick="history.go(-1);">
                      <i class="fas fa-times"></i> Cancel
                    </button>
                  </div>
                </div>
              <?= form_close() ?>
            <?php else: ?>
              <div class="alert alert-info">
                <i class="fas fa-info-circle fa-2x float-left mr-3"></i>
                <h5>No Fee Chalans Found</h5>
                <p class="mb-0">
                  No unpaid fee chalans found for today's date (<?= date('d-m-Y') ?>). 
                  Only chalans created today can be deleted.
                </p>
              </div>
              <div class="mt-3">
                <a href="<?= base_url('admin/delete-fee-chalan') ?>" class="btn btn-primary">
                  <i class="fas fa-sync-alt"></i> Refresh
                </a>
                <a href="<?= base_url('admin/fee-chalan') ?>" class="btn btn-default">
                  <i class="fas fa-arrow-left"></i> Back to Fee Chalan
                </a>
              </div>
            <?php endif; ?> 
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
$(function() {
  // Select all checkbox functionality
  $('#select-all').click(function() {
    $('input[name="chalan_ids[]"]').prop('checked', this.checked);
    updateSelectedCount();
  });

  // Update selected count when checkboxes change
  $('input[name="chalan_ids[]"]').change(function() {
    updateSelectedCount();
  });

  function updateSelectedCount() {
    var count = $('input[name="chalan_ids[]"]:checked').length;
    $('#selected-count').text(count);
  }

  // Initialize count
  updateSelectedCount();

  // Form validation
  $('#delete-form').validate({
    rules: {
      'chalan_ids[]': {
        required: true,
        minlength: 1
      }
    },
    messages: {
      'chalan_ids[]': {
        required: 'Please select at least one chalan to delete',
        minlength: 'Please select at least one chalan to delete'
      }
    },
    submitHandler: function(form) {
      $('#submitBtn').html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
      $('#submitBtn').prop('disabled', true);
      
      $.ajax({
        url: form.action,
        type: 'POST',
        data: $(form).serialize(),
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            toastr.success(response.msg);
            setTimeout(function() {
              location.reload();
            }, 1500);
          } else {
            toastr.error(response.msg);
            $('#submitBtn').html('<i class="fas fa-trash"></i> Delete Selected Fee Chalans');
            $('#submitBtn').prop('disabled', false);
          }
        },
        error: function(xhr, status, error) {
          console.error('AJAX Error:', error);
          console.error('Response:', xhr.responseText);
          toastr.error('An error occurred while deleting. Please check the console for details.');
          $('#submitBtn').html('<i class="fas fa-trash"></i> Delete Selected Fee Chalans');
          $('#submitBtn').prop('disabled', false);
        }
      });
      
      return false;
    }
  });
});
</script>

<style>
.table th, .table td {
  vertical-align: middle;
}
.table thead.bg-primary th {
  color: white;
  font-weight: 600;
}
.badge-info {
  background-color: #17a2b8;
  color: white;
  padding: 5px 10px;
  font-size: 12px;
}
#selected-count {
  font-weight: bold;
  font-size: 18px;
  color: #dc3545;
}
</style>

<?= $this->endSection() ?>