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

<?= view('components/page_header', [
    'title' => 'Campus Management',
    'icon' => 'fas fa-building',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Campus Management', 'active' => true],
    ],
]) ?>

<section class="content">
  <!-- Filters Card -->
  <div class="card sms-card card-info">
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
            <button id="applyFilters" class="btn btn-primary w-100">
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
              <th>Owner</th>
              <th>Owner Roles</th>
              <th>Status</th>
              <th>Students</th>
              <th>Staff</th>
              <th>Expiry Date</th>
              <th width="160px">Actions</th>
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
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="campusDetailsContent">
        <div class="text-center">
          <i class="fas fa-spinner fa-spin fa-2x"></i> Loading...
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Change Campus Expiry Modal -->
<div class="modal fade" id="changeExpiryModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title"><i class="fas fa-calendar-alt"></i> Change Campus Expiry</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="mb-2"><strong>Campus:</strong> <span id="expiryCampusName"></span></p>

        <div id="expiryPaymentHistoryWrap" class="mb-3">
          <h6 class="mb-2"><i class="fas fa-history"></i> Subscription Payment History</h6>
          <div id="expiryPaymentHistoryLoading" class="text-center text-muted py-3">
            <i class="fas fa-spinner fa-spin"></i> Loading payment history...
          </div>
          <div id="expiryPaymentSummary" class="alert alert-light border py-2 mb-2" style="display:none;"></div>
          <div id="expiryPaymentHistoryTable" style="display:none;">
            <div class="table-responsive" style="max-height: 220px; overflow-y: auto;">
              <table class="table table-sm table-bordered table-striped mb-0">
                <thead class="table-light">
                  <tr>
                    <th>#</th>
                    <th>Plan</th>
                    <th>Paid Amount</th>
                    <th>Discount</th>
                    <th>Payment Date</th>
                    <th>Valid Until</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody id="expiryPaymentHistoryBody"></tbody>
              </table>
            </div>
          </div>
          <div id="expiryPaymentHistoryEmpty" class="alert alert-secondary py-2 mb-0" style="display:none;">
            No subscription payment records found for this campus.
          </div>
        </div>

        <hr class="my-3">
        <div class="form-group mb-0">
          <label for="newCampusExpiry"><i class="fas fa-calendar"></i> New Expiry Date</label>
          <input type="date" id="newCampusExpiry" class="form-control" required>
          <small class="form-text text-muted">Updates the active subscription expiry for this campus.</small>
        </div>
        <input type="hidden" id="expiryCampusId">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="saveExpiryBtn" class="btn btn-warning">
          <i class="fas fa-save"></i> Save Expiry
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Campus Owner Account Modal -->
<div class="modal fade" id="resetOwnerPasswordModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title"><i class="fas fa-user-shield"></i> Campus Owner Account</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="mb-2"><strong>Campus:</strong> <span id="resetCampusName"></span></p>
        <div class="alert alert-info py-2">
          <small>
            <strong>Owner account</strong> (first user created for this campus):<br>
            <span id="resetOwnerName"></span><br>
            Username: <code id="resetOwnerUsername"></code><br>
            Email: <code id="resetOwnerEmail"></code><br>
            Plan: <span id="resetOwnerPlan">—</span><br>
            Account status: <span id="resetOwnerStatus"></span><br>
            Current role: <span id="resetOwnerRole">Loading...</span>
          </small>
        </div>
        <div id="resetOwnerRoleOk" class="alert alert-success py-2 small" style="display:none;">
          <i class="fas fa-check-circle"></i> <strong>Director System</strong> is assigned for this campus plan.
        </div>
        <div id="resetOwnerRoleMissing" class="alert alert-warning py-2 small" style="display:none;">
          <i class="fas fa-exclamation-triangle"></i>
          <span id="resetOwnerRoleMissingText">Owner does not have a valid <strong>Director System</strong> role for this plan — menu will be limited until assigned.</span>
          <button type="button" id="assignDirectorSystemBtn" class="btn btn-sm btn-warning mt-2">
            <i class="fas fa-user-shield"></i> Assign Director System Role
          </button>
        </div>
        <div id="resetOwnerInactiveAlert" class="alert alert-warning py-2 small" style="display:none;">
          <i class="fas fa-exclamation-triangle"></i>
          This account is <strong>inactive</strong> (<code>users.status</code>). Login shows &quot;Your account is inactive&quot; until activated — separate from campus expiry.
          <button type="button" id="activateOwnerBtn" class="btn btn-sm btn-success mt-2">
            <i class="fas fa-user-check"></i> Activate owner account
          </button>
        </div>
        <hr>
        <h6 class="fw-bold mb-2"><i class="fas fa-key"></i> Reset password</h6>
        <div class="form-group">
          <label for="ownerNewPassword">New Password</label>
          <input type="password" id="ownerNewPassword" class="form-control" minlength="6" autocomplete="new-password">
        </div>
        <div class="form-group mb-0">
          <label for="ownerConfirmPassword">Confirm Password</label>
          <input type="password" id="ownerConfirmPassword" class="form-control" minlength="6" autocomplete="new-password">
        </div>
        <input type="hidden" id="resetCampusId">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" id="saveOwnerPasswordBtn" class="btn btn-primary">
          <i class="fas fa-key"></i> Reset Password
        </button>
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
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-triangle fa-2x float-start me-3"></i>
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
            <li>All sports records</li>
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
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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

  function escapeHtml(text) {
    if (!text) return '';
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
  }

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
        data: null,
        orderable: false,
        render: function(data) {
          if (!data.owner_username) {
            return '<span class="text-muted">—</span>';
          }
          const nameLine = data.owner_name
            ? '<span class="d-block small text-muted">' + escapeHtml(data.owner_name) + '</span>'
            : '';
          const statusBadge = data.owner_status === 1
            ? '<span class="badge text-bg-success">Active</span>'
            : '<span class="badge text-bg-danger">Inactive</span>';
          return nameLine
            + '<code class="small">' + escapeHtml(data.owner_username) + '</code> '
            + statusBadge;
        }
      },
      {
        data: 'owner_roles',
        orderable: false,
        render: function(roles, type, data) {
          const roleList = roles || [];
          if (!roleList.length) {
            return '<span class="badge text-bg-danger">None</span>';
          }
          const roleLabel = escapeHtml(roleList.join(', '));
          if (data.owner_has_only_director_system) {
            return '<span class="badge text-bg-success">' + roleLabel + '</span>';
          }
          if (data.owner_has_director_system) {
            return '<span class="badge text-bg-warning">' + roleLabel + '</span>';
          }
          return '<span class="badge text-bg-secondary">' + roleLabel + '</span>';
        }
      },
      {
        data: 'campus_status',
        render: function(data) {
          let badgeClass = '';
          let badgeText = '';
          switch(data) {
            case 'active':
              badgeClass = 'text-bg-success';
              badgeText = 'Active';
              break;
            case 'expired':
              badgeClass = 'text-bg-warning';
              badgeText = 'Expired';
              break;
            default:
              badgeClass = 'text-bg-danger';
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
          const campusName = escapeHtml(data.campus_name || '');
          return `
            <div class="btn-group btn-group-sm">
              <button type="button" class="btn btn-info view-campus" data-id="${data.campus_id}" title="View details">
                <i class="fas fa-eye"></i>
              </button>
              <button type="button" class="btn btn-warning change-expiry" data-id="${data.campus_id}" data-name="${campusName}" data-expiry="${data.campus_expiry || ''}" title="Change expiry">
                <i class="fas fa-calendar-alt"></i>
              </button>
              <button type="button" class="btn btn-primary reset-owner-password" data-id="${data.campus_id}" data-name="${campusName}" title="Campus owner account (role &amp; password)">
                <i class="fas fa-user-shield"></i>
              </button>
              <button type="button" class="btn btn-danger delete-campus" data-id="${data.campus_id}" data-name="${campusName}" title="Delete campus">
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
        <span class="badge text-bg-secondary">ID: ${campus.id}</span>
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

  function formatDateForInput(dateStr) {
    if (!dateStr) return '';
    return String(dateStr).substring(0, 10);
  }

  function formatDisplayDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    return d.toLocaleDateString();
  }

  function formatMoney(amount, currency) {
    const n = parseFloat(amount);
    if (isNaN(n)) return '—';
    return (currency || 'PKR') + ' ' + n.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });
  }

  function resetExpiryPaymentHistoryUi() {
    $('#expiryPaymentHistoryLoading').show();
    $('#expiryPaymentSummary').hide().empty();
    $('#expiryPaymentHistoryTable').hide();
    $('#expiryPaymentHistoryEmpty').hide();
    $('#expiryPaymentHistoryBody').empty();
  }

  function loadExpiryPaymentHistory(campusId) {
    resetExpiryPaymentHistoryUi();

    $.ajax({
      url: "<?= base_url('admin/campus-management/get-payment-history') ?>",
      type: 'POST',
      dataType: 'json',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        campus_id: campusId,
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
      },
      success: function(response) {
        $('#expiryPaymentHistoryLoading').hide();

        if (!response.success || !response.data) {
          $('#expiryPaymentHistoryEmpty').show().text(response.msg || 'Could not load payment history.');
          return;
        }

        const data = response.data;
        const currency = data.currency_code || 'PKR';
        const summary = data.summary || {};
        const payments = data.payments || [];

        if (data.current_expiry) {
          $('#newCampusExpiry').val(formatDateForInput(data.current_expiry));
        }

        const summaryHtml = `
          <div class="row small">
            <div class="col-md-4"><strong>Current expiry:</strong> ${formatDisplayDate(data.current_expiry)}</div>
            <div class="col-md-4"><strong>Total paid:</strong> ${formatMoney(summary.total_paid_amount, currency)}</div>
            <div class="col-md-4"><strong>Paid invoices:</strong> ${summary.paid_count || 0} / ${summary.total_records || 0}</div>
          </div>
        `;
        $('#expiryPaymentSummary').html(summaryHtml).show();

        if (payments.length === 0) {
          $('#expiryPaymentHistoryEmpty').show();
          return;
        }

        const rows = payments.map(function(p, idx) {
          const statusBadge = p.is_paid
            ? '<span class="badge text-bg-success">Paid</span>'
            : '<span class="badge text-bg-secondary">Unpaid</span>';
          const activeBadge = p.is_active
            ? ' <span class="badge text-bg-info">Active</span>'
            : '';
          const paidAmount = p.is_paid ? formatMoney(p.bill_amount, currency) : '—';
          const payDate = p.is_paid ? formatDisplayDate(p.payment_date || p.paid_date) : '—';

          return `<tr>
            <td>${idx + 1}</td>
            <td>${escapeHtml(p.plan_name || 'N/A')}</td>
            <td>${paidAmount}</td>
            <td>${p.discount > 0 ? formatMoney(p.discount, currency) : '—'}</td>
            <td>${payDate}</td>
            <td>${formatDisplayDate(p.campus_expiry)}</td>
            <td>${statusBadge}${activeBadge}</td>
          </tr>`;
        }).join('');

        $('#expiryPaymentHistoryBody').html(rows);
        $('#expiryPaymentHistoryTable').show();
      },
      error: function(xhr) {
        $('#expiryPaymentHistoryLoading').hide();
        let msg = 'Could not load payment history.';
        if (xhr.responseJSON && xhr.responseJSON.msg) {
          msg = xhr.responseJSON.msg;
        }
        $('#expiryPaymentHistoryEmpty').show().text(msg);
      }
    });
  }

  // Change campus expiry
  $('#campus-datatable').on('click', '.change-expiry', function() {
    const campusId = $(this).data('id');
    const campusName = $(this).data('name');
    const expiry = $(this).data('expiry');

    $('#expiryCampusId').val(campusId);
    $('#expiryCampusName').text(campusName);
    $('#newCampusExpiry').val(formatDateForInput(expiry));
    $('#changeExpiryModal').modal('show');
    loadExpiryPaymentHistory(campusId);
  });

  $('#saveExpiryBtn').on('click', function() {
    const btn = $(this);
    const campusId = $('#expiryCampusId').val();
    const campusExpiry = $('#newCampusExpiry').val();

    if (!campusExpiry) {
      Swal.fire({ icon: 'warning', title: 'Required', text: 'Please select an expiry date.' });
      return;
    }

    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

    $.ajax({
      url: "<?= base_url('admin/campus-management/update-expiry') ?>",
      type: 'POST',
      dataType: 'json',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        campus_id: campusId,
        campus_expiry: campusExpiry,
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
      },
      success: function(response) {
        if (response.success) {
          Swal.fire({ icon: 'success', title: 'Saved', text: response.msg, timer: 2500, showConfirmButton: true });
          loadExpiryPaymentHistory(campusId);
          table.ajax.reload(null, false);
        } else {
          Swal.fire({ icon: 'error', title: 'Error', text: response.msg || 'Could not update expiry.' });
        }
      },
      error: function(xhr) {
        let msg = 'Failed to update expiry.';
        if (xhr.responseJSON && xhr.responseJSON.msg) {
          msg = xhr.responseJSON.msg;
        } else if (xhr.status === 404) {
          msg = 'Update route not found (404). Redeploy app/Config/Routes.php and app/Controllers/Admin/CampusManagement.php on the server.';
        } else if (xhr.status === 403) {
          msg = 'Access denied (403). Check you are logged in as admin.';
        }
        Swal.fire({ icon: 'error', title: 'Error', text: msg });
      },
      complete: function() {
        btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Expiry');
      }
    });
  });

  function renderOwnerRoleState(data) {
    const roles = data.current_roles || [];
    const roleLabel = roles.length ? roles.join(', ') : 'None';
    $('#resetOwnerPlan').text(data.plan_name ? (data.plan_name + ' (plan ' + data.plan_id + ')') : 'N/A');
    $('#resetOwnerRole').html(
      roles.length
        ? '<span class="badge text-bg-secondary">' + escapeHtml(roleLabel) + '</span>'
        : '<span class="badge text-bg-danger">None</span>'
    );
    if (data.has_only_director_system) {
      $('#resetOwnerRoleOk').show();
      $('#resetOwnerRoleMissing').hide();
    } else {
      $('#resetOwnerRoleOk').hide();
      $('#resetOwnerRoleMissing').show();
      if (data.has_director_system) {
        $('#resetOwnerRoleMissingText').html(
          'Owner has <strong>Director System</strong> but also other roles (' + escapeHtml(roleLabel) + '). '
          + 'Assign again to remove all other roles and keep only Director System.'
        );
      } else {
        $('#resetOwnerRoleMissingText').html(
          'Owner does not have a valid <strong>Director System</strong> role for this plan — menu will be limited until assigned.'
        );
      }
    }
  }

  function loadCampusOwner(campusId, campusName, triggerBtn) {
    if (triggerBtn) triggerBtn.prop('disabled', true);
    $('#resetCampusId').val(campusId);
    $('#resetCampusName').text(campusName);
    $('#ownerNewPassword, #ownerConfirmPassword').val('');
    $('#resetOwnerName, #resetOwnerUsername, #resetOwnerEmail, #resetOwnerPlan').text('Loading...');
    $('#resetOwnerRole').text('Loading...');
    $('#resetOwnerStatus').text('Loading...');
    $('#resetOwnerInactiveAlert, #resetOwnerRoleOk, #resetOwnerRoleMissing').hide();
    $('#resetOwnerPasswordModal').modal('show');

    $.post("<?= base_url('admin/campus-management/get-owner') ?>", {
      campus_id: campusId,
      '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
    }, function(response) {
      if (response.success) {
        const owner = response.data.owner;
        const ownerName = [owner.first_name, owner.last_name].filter(Boolean).join(' ') || 'N/A';
        $('#resetOwnerName').text(ownerName);
        $('#resetOwnerUsername').text(owner.username || 'N/A');
        $('#resetOwnerEmail').text(owner.email || 'N/A');
        if (owner.is_active) {
          $('#resetOwnerStatus').html('<span class="badge text-bg-success">Active</span>');
          $('#resetOwnerInactiveAlert').hide();
        } else {
          $('#resetOwnerStatus').html('<span class="badge text-bg-danger">Inactive</span>');
          $('#resetOwnerInactiveAlert').show();
        }
        renderOwnerRoleState(response.data);
      } else {
        $('#resetOwnerPasswordModal').modal('hide');
        Swal.fire({ icon: 'error', title: 'Error', text: response.msg });
      }
    }, 'json').fail(function() {
      $('#resetOwnerPasswordModal').modal('hide');
      Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load owner account.' });
    }).always(function() {
      if (triggerBtn) triggerBtn.prop('disabled', false);
    });
  }

  // Campus owner account (role + password)
  $('#campus-datatable').on('click', '.reset-owner-password', function() {
    loadCampusOwner($(this).data('id'), $(this).data('name'), $(this));
  });

  $('#assignDirectorSystemBtn').on('click', function() {
    const campusId = $('#resetCampusId').val();
    const btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Assigning...');

    $.post("<?= base_url('admin/campus-management/assign-owner-director-system') ?>", {
      campus_id: campusId,
      '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
    }, function(response) {
      if (response.success) {
        Swal.fire({
          icon: 'success',
          title: 'Role assigned',
          text: response.msg,
          timer: 4000,
          showConfirmButton: true
        });
        loadCampusOwner(campusId, $('#resetCampusName').text(), null);
        table.ajax.reload(null, false);
      } else {
        Swal.fire({ icon: 'error', title: 'Error', text: response.msg });
      }
    }, 'json').fail(function(xhr) {
      let msg = 'Failed to assign Director System role.';
      if (xhr.responseJSON && xhr.responseJSON.msg) msg = xhr.responseJSON.msg;
      Swal.fire({ icon: 'error', title: 'Error', text: msg });
    }).always(function() {
      btn.prop('disabled', false).html('<i class="fas fa-user-shield"></i> Assign Director System Role');
    });
  });

  $('#activateOwnerBtn').on('click', function() {
    const campusId = $('#resetCampusId').val();
    const btn = $(this);
    btn.prop('disabled', true);

    $.post("<?= base_url('admin/campus-management/activate-owner') ?>", {
      campus_id: campusId,
      '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
    }, function(response) {
      if (response.success) {
        Swal.fire({ icon: 'success', title: 'Activated', text: response.msg, timer: 3000, showConfirmButton: true });
        $('#resetOwnerStatus').html('<span class="badge text-bg-success">Active</span>');
        $('#resetOwnerInactiveAlert').hide();
        table.ajax.reload(null, false);
      } else {
        Swal.fire({ icon: 'error', title: 'Error', text: response.msg });
      }
    }, 'json').always(function() {
      btn.prop('disabled', false);
    });
  });

  $('#saveOwnerPasswordBtn').on('click', function() {
    const btn = $(this);
    const campusId = $('#resetCampusId').val();
    const password = $('#ownerNewPassword').val();
    const confirmPassword = $('#ownerConfirmPassword').val();

    if (!password && !confirmPassword) {
      Swal.fire({ icon: 'info', title: 'No password', text: 'Enter a new password to reset, or use Assign Director System above.' });
      return;
    }
    if (password.length < 6) {
      Swal.fire({ icon: 'warning', title: 'Invalid', text: 'Password must be at least 6 characters.' });
      return;
    }
    if (password !== confirmPassword) {
      Swal.fire({ icon: 'warning', title: 'Mismatch', text: 'Passwords do not match.' });
      return;
    }

    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Resetting...');

    $.post("<?= base_url('admin/campus-management/reset-owner-password') ?>", {
      campus_id: campusId,
      password: password,
      confirm_password: confirmPassword,
      '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
    }, function(response) {
      if (response.success) {
        Swal.fire({ icon: 'success', title: 'Done', text: response.msg, timer: 3000, showConfirmButton: true });
        $('#resetOwnerPasswordModal').modal('hide');
        $('#ownerNewPassword, #ownerConfirmPassword').val('');
      } else {
        Swal.fire({ icon: 'error', title: 'Error', text: response.msg });
      }
    }, 'json').fail(function() {
      Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to reset password.' });
    }).always(function() {
      btn.prop('disabled', false).html('<i class="fas fa-key"></i> Reset Password');
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
.text-bg-success {
  background-color: #28a745;
  color: white;
  padding: 5px 10px;
  border-radius: 20px;
}
.text-bg-warning {
  background-color: #ffc107;
  color: #212529;
  padding: 5px 10px;
  border-radius: 20px;
}
.text-bg-danger {
  background-color: #dc3545;
  color: white;
  padding: 5px 10px;
  border-radius: 20px;
}
.text-bg-secondary {
  background-color: #6c757d;
  color: white;
  padding: 5px 10px;
  border-radius: 20px;
}
.text-bg-info {
  background-color: #17a2b8;
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