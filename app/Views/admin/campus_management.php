<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- Include Bootstrap Toggle CSS -->
<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<!-- Include SweetAlert2 -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
.selected-row {
    background-color: #e3f2fd !important;
}
.batch-actions-bar {
    display: none;
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    padding: 12px 24px;
    z-index: 1000;
    border: 1px solid #dee2e6;
    animation: slideUp 0.3s ease;
}
@keyframes slideUp {
    from {
        transform: translate(-50%, 100%);
        opacity: 0;
    }
    to {
        transform: translate(-50%, 0);
        opacity: 1;
    }
}
.batch-actions-bar .selected-count {
    font-weight: bold;
    color: #007bff;
    margin-right: 15px;
}
.select-all-container {
    display: flex;
    align-items: center;
    gap: 8px;
}
</style>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><i class="fas fa-building"></i> Campus Management</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Campus Management</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <!-- Filters Card -->
  <div class="card card-info">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-filter"></i> Filters</h3>
      <div class="card-tools">
        <button type="button" class="btn btn-tool" data-card-widget="collapse">
          <i class="fas fa-minus"></i>
        </button>
      </div>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label><i class="fas fa-school"></i> School</label>
            <select id="filterSchool" class="form-control select2">
              <option value="">All Schools</option>
              <?php foreach($schools as $school): ?>
                <option value="<?= $school->system_id ?>"><?= esc($school->system_name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label><i class="fas fa-chart-line"></i> Campus Status</label>
            <select id="filterStatus" class="form-control">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="expired">Expired</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label><i class="fas fa-search"></i> Search</label>
            <input type="text" id="filterSearch" class="form-control" placeholder="School name, Campus name, Address...">
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label>&nbsp;</label>
            <button id="applyFilters" class="btn btn-primary btn-block">
              <i class="fas fa-search"></i> Apply
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Campuses Table Card -->
  <div class="card card-primary">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-list"></i> Campus List</h3>
      <div class="card-tools">
        <button id="exportReport" class="btn btn-success btn-sm">
          <i class="fas fa-download"></i> Export Report
        </button>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="campus-datatable" class="table table-bordered table-hover">
          <thead>
            <tr>
              <th width="40px">
                <div class="select-all-container">
                  <input type="checkbox" id="selectAllCheckbox">
                </div>
              </th>
              <th>#</th>
              <th>School</th>
              <th>Campus Name</th>
              <th>Address</th>
              <th>Contact</th>
              <th>Status</th>
              <th>Students</th>
              <th>Staff</th>
              <th>Expiry Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<!-- Batch Actions Bar -->
<div id="batchActionsBar" class="batch-actions-bar">
  <div class="d-flex align-items-center gap-3">
    <span class="selected-count">
      <i class="fas fa-check-circle"></i> <span id="selectedCount">0</span> campus(es) selected
    </span>
    <button id="bulkDeleteBtn" class="btn btn-danger btn-sm">
      <i class="fas fa-trash-alt"></i> Delete Selected
    </button>
    <button id="clearSelectionBtn" class="btn btn-secondary btn-sm">
      <i class="fas fa-times"></i> Clear
    </button>
  </div>
</div>

<!-- Campus Details Modal -->
<div class="modal fade" id="campusDetailsModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h5 class="modal-title"><i class="fas fa-building"></i> Campus Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="campusDetailsContent">
        <div class="text-center">
          <i class="fas fa-spinner fa-spin fa-2x"></i> Loading...
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Bulk Delete Confirmation Modal -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title"><i class="fas fa-trash-alt"></i> Bulk Delete Campuses</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-triangle fa-2x float-left mr-3"></i>
          <strong>DANGER!</strong> This action is IRREVERSIBLE and will delete ALL selected campuses and ALL their associated data.
        </div>
        
        <div class="card bg-light mb-3">
          <div class="card-body">
            <h6><i class="fas fa-school"></i> Campuses to be deleted:</h6>
            <div id="bulkDeleteCampusesList" class="mt-2" style="max-height: 200px; overflow-y: auto;"></div>
          </div>
        </div>
        
        <div class="alert alert-warning">
          <i class="fas fa-database"></i> <strong>Data Impact:</strong> This operation will delete:
          <ul class="mt-2 mb-0">
            <li>All students (active and inactive) from these campuses</li>
            <li>All staff members (active and inactive) from these campuses</li>
            <li>All parents associated with these campuses</li>
            <li>All classes, sections, and subjects</li>
            <li>All attendance records (students and staff)</li>
            <li>All salary slips and financial records</li>
            <li>All fee challans and invoices</li>
            <li>All exam results and grades</li>
            <li>All quiz attempts and question banks</li>
            <li>All notices and communications</li>
            <li>All assets and expenses</li>
            <li>All sports and hostel records</li>
            <li>All photos and files from these campuses</li>
          </ul>
        </div>
        
        <div class="form-check mt-3">
          <input class="form-check-input" type="checkbox" id="bulkConfirmDelete">
          <label class="form-check-label" for="bulkConfirmDelete">
            I fully understand that this will delete <strong>ALL DATA AND FILES</strong> for the selected campuses and this action is <strong>IRREVERSIBLE</strong>
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" id="confirmBulkDeleteBtn" class="btn btn-danger" disabled>
          <i class="fas fa-trash-alt"></i> Permanently Delete All Selected
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap Toggle JS -->
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

<script>
$(function () {
  let selectedCampuses = new Set();
  let currentDeleteCampusId = null;
  let currentDeleteCampusName = null;

  // Initialize DataTable
  const table = $('#campus-datatable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "<?= base_url('admin/campus-management/data') ?>",
      type: "POST",
      data: function(d) {
        d.school_id = $('#filterSchool').val();
        d.status = $('#filterStatus').val();
        d.search_value = $('#filterSearch').val();
      },
      complete: function() {
        // Re-attach checkbox handlers after table redraw
        attachCheckboxHandlers();
        updateBatchActionsBar();
      }
    },
    columns: [
      {
        data: null,
        orderable: false,
        render: function(data) {
          return `<input type="checkbox" class="campus-select-checkbox" data-id="${data.campus_id}" data-name="${data.campus_name}">`;
        }
      },
      { data: 'sno', title: '#', orderable: false },
      { data: 'school_name' },
      { data: 'campus_name' },
      { data: 'campus_address' },
      { data: 'contact' },
      {
        data: 'campus_status',
        render: function(data) {
          let badgeClass = '';
          let badgeText = '';
          switch(data) {
            case 'active':
              badgeClass = 'badge-success';
              badgeText = 'Active';
              break;
            case 'expired':
              badgeClass = 'badge-warning';
              badgeText = 'Expired';
              break;
            default:
              badgeClass = 'badge-danger';
              badgeText = 'Inactive';
          }
          return `<span class="badge ${badgeClass}">${badgeText}</span>`;
        }
      },
      { data: 'total_students' },
      { data: 'total_staff' },
      {
        data: 'campus_expiry',
        render: function(data) {
          return data ? new Date(data).toLocaleDateString() : 'N/A';
        }
      },
      {
        data: null,
        orderable: false,
        render: function(data) {
          return `
            <div class="btn-group btn-group-sm">
              <button class="btn btn-info view-campus" data-id="${data.campus_id}">
                <i class="fas fa-eye"></i>
              </button>
              <button class="btn btn-danger delete-campus" data-id="${data.campus_id}" data-name="${data.campus_name}">
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>
          `;
        }
      }
    ],
    pageLength: 25,
    order: [[2, 'asc']],
    language: {
      search: "Search:",
      lengthMenu: "Show _MENU_ entries per page",
      info: "Showing _START_ to _END_ of _TOTAL_ campuses"
    },
    drawCallback: function() {
      // Restore selected checkboxes after table redraw
      $('.campus-select-checkbox').each(function() {
        const id = $(this).data('id');
        if (selectedCampuses.has(id)) {
          $(this).prop('checked', true);
          $(this).closest('tr').addClass('selected-row');
        }
      });
      updateBatchActionsBar();
    }
  });

  // Attach checkbox handlers
  function attachCheckboxHandlers() {
    $('.campus-select-checkbox').off('change').on('change', function() {
      const id = $(this).data('id');
      const name = $(this).data('name');
      if ($(this).is(':checked')) {
        selectedCampuses.add(id);
        $(this).closest('tr').addClass('selected-row');
      } else {
        selectedCampuses.delete(id);
        $(this).closest('tr').removeClass('selected-row');
      }
      updateBatchActionsBar();
      updateSelectAllCheckbox();
    });
  }

  // Update batch actions bar visibility and count
  function updateBatchActionsBar() {
    const count = selectedCampuses.size;
    if (count > 0) {
      $('#batchActionsBar').fadeIn(200);
      $('#selectedCount').text(count);
    } else {
      $('#batchActionsBar').fadeOut(200);
    }
    updateSelectAllCheckbox();
  }

  // Update select all checkbox state
  function updateSelectAllCheckbox() {
    const totalCheckboxes = $('.campus-select-checkbox').length;
    const checkedCheckboxes = $('.campus-select-checkbox:checked').length;
    const selectAllCheckbox = $('#selectAllCheckbox');
    
    if (totalCheckboxes === 0) {
      selectAllCheckbox.prop('checked', false);
      selectAllCheckbox.prop('indeterminate', false);
    } else if (checkedCheckboxes === totalCheckboxes) {
      selectAllCheckbox.prop('checked', true);
      selectAllCheckbox.prop('indeterminate', false);
    } else if (checkedCheckboxes > 0) {
      selectAllCheckbox.prop('checked', false);
      selectAllCheckbox.prop('indeterminate', true);
    } else {
      selectAllCheckbox.prop('checked', false);
      selectAllCheckbox.prop('indeterminate', false);
    }
  }

  // Select all functionality
  $('#selectAllCheckbox').on('change', function() {
    const isChecked = $(this).is(':checked');
    $('.campus-select-checkbox').each(function() {
      const id = $(this).data('id');
      if (isChecked) {
        selectedCampuses.add(id);
        $(this).prop('checked', true);
        $(this).closest('tr').addClass('selected-row');
      } else {
        selectedCampuses.delete(id);
        $(this).prop('checked', false);
        $(this).closest('tr').removeClass('selected-row');
      }
    });
    updateBatchActionsBar();
  });

  // Clear selection
  $('#clearSelectionBtn').on('click', function() {
    selectedCampuses.clear();
    $('.campus-select-checkbox').prop('checked', false);
    $('.campus-select-checkbox').closest('tr').removeClass('selected-row');
    updateBatchActionsBar();
  });

  // Bulk delete button click
  $('#bulkDeleteBtn').on('click', function() {
    if (selectedCampuses.size === 0) {
      Swal.fire({
        icon: 'warning',
        title: 'No Selection',
        text: 'Please select at least one campus to delete.',
        confirmButtonText: 'OK'
      });
      return;
    }
    
    // Get selected campuses details
    const selectedCampusesList = [];
    $('.campus-select-checkbox:checked').each(function() {
      selectedCampusesList.push({
        id: $(this).data('id'),
        name: $(this).data('name')
      });
    });
    
    // Populate the modal
    const listHtml = selectedCampusesList.map(campus => 
      `<div class="d-flex justify-content-between align-items-center p-2 border-bottom">
        <span><i class="fas fa-building"></i> ${escapeHtml(campus.name)}</span>
        <span class="badge badge-secondary">ID: ${campus.id}</span>
      </div>`
    ).join('');
    
    $('#bulkDeleteCampusesList').html(listHtml);
    $('#bulkConfirmDelete').prop('checked', false);
    $('#confirmBulkDeleteBtn').prop('disabled', true);
    $('#bulkDeleteModal').modal('show');
  });

  // Confirm bulk delete
  $('#confirmBulkDeleteBtn').on('click', function() {
    const btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
    
    const campusIds = Array.from(selectedCampuses);
    
    $.post("<?= base_url('admin/campus-management/bulk-delete') ?>", {
      campus_ids: campusIds,
      '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
    }, function(response) {
      if (response.success) {
        Swal.fire({
          icon: 'success',
          title: 'Deleted!',
          html: response.msg,
          timer: 5000,
          showConfirmButton: true
        });
        $('#bulkDeleteModal').modal('hide');
        selectedCampuses.clear();
        table.ajax.reload();
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: response.msg,
          confirmButtonText: 'OK'
        });
      }
    }, 'json').always(function() {
      btn.prop('disabled', false).html('<i class="fas fa-trash-alt"></i> Permanently Delete All Selected');
    });
  });

  $('#bulkConfirmDelete').on('change', function() {
    $('#confirmBulkDeleteBtn').prop('disabled', !$(this).is(':checked'));
  });

  // Escape HTML helper
  function escapeHtml(text) {
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
  }

  // Apply filters
  $('#applyFilters').click(function() {
    selectedCampuses.clear();
    table.ajax.reload();
  });

  // Enter key search
  $('#filterSearch').keypress(function(e) {
    if (e.which === 13) {
      selectedCampuses.clear();
      table.ajax.reload();
    }
  });

  // Export report
  $('#exportReport').click(function() {
    const params = new URLSearchParams({
      school_id: $('#filterSchool').val(),
      status: $('#filterStatus').val(),
      search: $('#filterSearch').val()
    });
    window.location.href = "<?= base_url('admin/campus-management/export') ?>?" + params.toString();
  });

  // View campus details
  $('#campus-datatable').on('click', '.view-campus', function() {
    const campusId = $(this).data('id');
    $('#campusDetailsModal').modal('show');
    $('#campusDetailsContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i> Loading...</div>');

    $.post("<?= base_url('admin/campus-management/get-details') ?>", {
      campus_id: campusId,
      '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
    }, function(response) {
      if (response.success) {
        const data = response.data;
        const campus = data.campus;
        const stats = data.statistics;
        
        let feeHtml = '';
        if (data.fee_collection && data.fee_collection.length > 0) {
          feeHtml = `
            <div class="mt-4">
              <h6><i class="fas fa-chart-line"></i> Fee Collection (Last 12 Months)</h6>
              <div class="table-responsive">
                <table class="table table-sm table-bordered">
                  <thead>
                    <tr>
                      <th>Month</th>
                      <th>Total Collection</th>
                    </tr>
                  </thead>
                  <tbody>
                    ${data.fee_collection.map(item => `
                      <tr>
                        <td>${item.month}</td>
                        <td>${campus.currency_code || 'PKR'} ${parseFloat(item.total).toLocaleString()}</td>
                      </tr>
                    `).join('')}
                  </tbody>
                </table>
              </div>
            </div>
          `;
        }

        $('#campusDetailsContent').html(`
          <div class="row">
            <div class="col-md-6">
              <div class="info-box bg-light">
                <div class="info-box-content">
                  <span class="info-box-text text-center"><strong>Basic Information</strong></span>
                </div>
              </div>
              <table class="table table-sm table-bordered">
                <tr>
                  <th width="40%">School Name:</th>
                  <td>${campus.system_name || 'N/A'}</td>
                </tr>
                <tr>
                  <th>Campus Name:</th>
                  <td>${campus.campus_name}</td>
                </tr>
                <tr>
                  <th>Short Name:</th>
                  <td>${campus.short_name || 'N/A'}</td>
                </tr>
                <tr>
                  <th>Address:</th>
                  <td>${campus.location || 'N/A'}</td>
                </tr>
                <tr>
                  <th>Landline:</th>
                  <td>${campus.landline || 'N/A'}</td>
                </tr>
                <tr>
                  <th>Mobile:</th>
                  <td>${campus.mobile_no || 'N/A'}</td>
                </tr>
                <tr>
                  <th>Website:</th>
                  <td>${campus.website || 'N/A'}</td>
                </tr>
                <tr>
                  <th>Currency:</th>
                  <td>${campus.currency_code || 'PKR'}</td>
                </tr>
              </table>
            </div>
            <div class="col-md-6">
              <div class="info-box bg-light">
                <div class="info-box-content">
                  <span class="info-box-text text-center"><strong>Statistics</strong></span>
                </div>
              </div>
              <table class="table table-sm table-bordered">
                <tr>
                  <th width="40%">Total Students:</th>
                  <td>${stats.total_students}</td>
                </tr>
                <tr>
                  <th>Total Staff:</th>
                  <td>${stats.total_staff}</td>
                </tr>
                <tr>
                  <th>Active Classes:</th>
                  <td>${stats.total_classes}</td>
                </tr>
                <tr>
                  <th>Campus Status:</th>
                  <td>${campus.campus_status || 'Inactive'}</td>
                </tr>
                <tr>
                  <th>Expiry Date:</th>
                  <td>${campus.campus_expiry || 'N/A'}</td>
                </tr>
                <tr>
                  <th>Bill Amount:</th>
                  <td>${campus.bill_amount ? campus.currency_code + ' ' + parseFloat(campus.bill_amount).toLocaleString() : 'N/A'}</td>
                </tr>
                <tr>
                  <th>Bill Status:</th>
                  <td>${campus.bill_status || 'N/A'}</td>
                </tr>
                <tr>
                  <th>Owner Name:</th>
                  <td>${campus.owner_name || 'N/A'}</td>
                </tr>
              </table>
            </div>
          </div>
          ${feeHtml}
        `);
      } else {
        $('#campusDetailsContent').html(`
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> ${response.msg || 'Failed to load campus details'}
          </div>
        `);
      }
    }, 'json').fail(function() {
      $('#campusDetailsContent').html(`
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-circle"></i> Failed to load campus details
        </div>
      `);
    });
  });

  // Single delete campus
  $('#campus-datatable').on('click', '.delete-campus', function() {
    currentDeleteCampusId = $(this).data('id');
    currentDeleteCampusName = $(this).data('name');
    
    Swal.fire({
      title: 'Delete Campus?',
      html: `Are you sure you want to delete <strong>${currentDeleteCampusName}</strong>?<br><br>
             <span class="text-danger">This will delete ALL data associated with this campus!</span>`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: '<i class="fas fa-trash-alt"></i> Yes, delete it!',
      cancelButtonText: 'Cancel',
      input: 'checkbox',
      inputPlaceholder: 'I understand this action is irreversible',
      inputValidator: (result) => {
        if (!result) {
          return 'You must confirm that you understand this action is irreversible';
        }
        return null;
      }
    }).then((result) => {
      if (result.isConfirmed) {
        $.post("<?= base_url('admin/campus-management/delete') ?>", {
          campus_id: currentDeleteCampusId,
          '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        }, function(response) {
          if (response.success) {
            Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              html: response.msg,
              timer: 3000,
              showConfirmButton: true
            });
            table.ajax.reload();
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: response.msg,
              confirmButtonText: 'OK'
            });
          }
        }, 'json');
      }
    });
  });

  // Reset filters
  $('#filterSchool, #filterStatus').on('change', function() {
    selectedCampuses.clear();
    table.ajax.reload();
  });
});
</script>

<style>
.badge-success {
  background-color: #28a745;
  color: white;
  padding: 5px 10px;
  border-radius: 20px;
}
.badge-warning {
  background-color: #ffc107;
  color: #212529;
  padding: 5px 10px;
  border-radius: 20px;
}
.badge-danger {
  background-color: #dc3545;
  color: white;
  padding: 5px 10px;
  border-radius: 20px;
}
.info-box {
  margin-bottom: 10px;
}
.select-all-container {
  display: flex;
  justify-content: center;
  align-items: center;
}
.campus-select-checkbox {
  cursor: pointer;
  width: 18px;
  height: 18px;
}
.selected-row {
  background-color: #e3f2fd !important;
}
.batch-actions-bar {
  display: none;
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.15);
  padding: 12px 24px;
  z-index: 1000;
  border: 1px solid #dee2e6;
  animation: slideUp 0.3s ease;
}
@keyframes slideUp {
  from {
    transform: translate(-50%, 100%);
    opacity: 0;
  }
  to {
    transform: translate(-50%, 0);
    opacity: 1;
  }
}
.batch-actions-bar .selected-count {
  font-weight: bold;
  color: #007bff;
  margin-right: 15px;
}
</style>

<?= $this->endSection() ?>