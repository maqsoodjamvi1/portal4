<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- Debug: Check if sectionsclassinfo has data -->
<?php 
if (empty($sectionsclassinfo)) {
    echo '<div class="alert alert-warning">No class sections found. Please check your database.</div>';
} else {
    echo '<div class="alert alert-success">Found ' . count($sectionsclassinfo) . ' class sections.</div>';
}
?>
<meta name="csrf-token-name" content="<?= csrf_token() ?>">
<meta name="csrf-token-hash" content="<?= csrf_hash() ?>">
<section class="content">
    <div class="container-fluid">
        <!-- Quick Stats Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 id="totalStudentsCount">0</h3>
                        <p>Total Students</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 id="currentStudentsCount">0</h3>
                        <p>Current Students</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="droppedStudentsCount">0</h3>
                        <p>Dropped Students</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-slash"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3 id="slcCount">0</h3>
                        <p>SLC Issued</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                </div>
            </div>
        </div>

 <!-- Filters -->
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-filter mr-2"></i>Filters
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Student Name</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="search_name" placeholder="Type 3+ characters...">
                        <div class="input-group-append">
                            <span class="input-group-text clear-search" style="cursor: pointer;" title="Clear">
                                <i class="fas fa-times"></i>
                            </span>
                        </div>
                    </div>
                    <small class="text-muted">Type at least 3 characters for suggestions</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><i class="fas fa-male"></i> Father Name</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="search_father" placeholder="Type 3+ characters...">
                        <div class="input-group-append">
                            <span class="input-group-text clear-search" style="cursor: pointer;" title="Clear">
                                <i class="fas fa-times"></i>
                            </span>
                        </div>
                    </div>
                    <small class="text-muted">Type at least 3 characters for suggestions</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><i class="fas fa-chalkboard"></i> Class</label>
                    <select id="class_id" class="form-control select2">
                        <option value="">All Classes</option>
                        <?php foreach (($classes ?? []) as $c): ?>
                            <option value="<?= (int)$c['class_id'] ?>"><?= esc($c['class_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><i class="fas fa-layer-group"></i> Class Section</label>
                    <select id="cls_sec_id" class="form-control select2">
                        <option value="">All Sections</option>
                        <?php foreach (($classSections ?? []) as $sec): ?>
                            <option value="<?= (int)$sec['cls_sec_id'] ?>">
                                <?= esc($sec['label']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label><i class="fas fa-users"></i> Student Status</label>
                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                        <label class="btn btn-outline-primary active" id="btnCurrentOnly">
                            <input type="radio" name="status_filter" value="current" checked autocomplete="off">
                            <i class="fas fa-user-check mr-1"></i> Current Only
                        </label>
                        <label class="btn btn-outline-secondary" id="btnAllStudents">
                            <input type="radio" name="status_filter" value="all" autocomplete="off">
                            <i class="fas fa-users mr-1"></i> All Students
                        </label>
                    </div>
                    <small class="text-muted">Show only active students or all students</small>
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group text-right">
                    <button type="button" id="resetFilters" class="btn btn-default">
                        <i class="fas fa-undo mr-1"></i> Reset All Filters
                    </button>
                </div>
            </div>
        </div>
        <!-- Filter Status Indicator -->
        <div class="row mt-2">
            <div class="col-12">
                <div class="alert alert-info alert-dismissible fade show mb-0" role="alert" style="padding: 8px 15px;">
                    <i class="fas fa-search mr-2"></i>
                    <span id="filterStatus">No active filters</span>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="padding: 8px 15px;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <div class="btn-group view-presets">
            <button type="button" class="btn btn-sm btn-outline-primary" id="viewBasic">
                <i class="fas fa-eye"></i> Basic
            </button>
            <button type="button" class="btn btn-sm btn-outline-success" id="viewContacts">
                <i class="fas fa-phone"></i> Contacts
            </button>
            <button type="button" class="btn btn-sm btn-outline-info" id="viewAcademic">
                <i class="fas fa-graduation-cap"></i> Academic
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="viewAll">
                <i class="fas fa-eye"></i> All
            </button>
        </div>
    </div>
    <div class="col-md-6 text-right">
        <small class="text-muted">
            <i class="fas fa-info-circle"></i> 
            <span class="badge badge-info">←→</span> Scroll horizontally | 
            <span class="badge badge-info">⬆⬇</span> Scroll vertically | 
            <span class="badge badge-info">📌</span> Sticky columns
        </small>
    </div>
</div>

<!-- Floating Action Buttons -->
<div class="floating-actions">
    <button class="btn btn-primary btn-floating" id="scrollToTop" title="Scroll to Top">
        <i class="fas fa-arrow-up"></i>
    </button>
    <button class="btn btn-info btn-floating" id="scrollToRight" title="Scroll to Right">
        <i class="fas fa-arrow-right"></i>
    </button>
    <button class="btn btn-info btn-floating" id="scrollToLeft" title="Scroll to Left">
        <i class="fas fa-arrow-left"></i>
    </button>
    <button class="btn btn-secondary btn-floating" id="showColumnMenu" title="Toggle Columns">
        <i class="fas fa-columns"></i>
    </button>
</div>
        <!-- Students Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-2"></i>Student List
                </h3>
                <div class="card-tools">
                    <div class="btn-group">
                        <button type="button" class="btn btn-default btn-sm" id="exportExcel">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button type="button" class="btn btn-default btn-sm" id="exportPDF">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                        <button type="button" class="btn btn-default btn-sm" id="toggleColumns">
                            <i class="fas fa-columns"></i> Columns
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="studentsTable" class="table table-bordered table-striped table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Photo</th>
                                <th>Student ID</th>
                                <th>Actions</th>
                                <th>Reg No</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Father</th>
                                <th>Father CNIC</th>
                                <th>Student CNIC</th>
                                <th>Gender</th>
                                <th>DOB</th>
                                <th>Age</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Discounted</th>
                                <th>Admission Date</th>
                                <th>Father Contact</th>
                                <th>Mother Contact</th>
                                <th>Emergency</th>
                                <th>WhatsApp</th>
                                <th>Address</th>
                                <th>Previous School</th>
                                <th>PS City</th>
                                <th>Health Condition</th>
                                <th>Major Injuries</th>
                                <th>Admission Class</th>
                                <th>Caste</th>
                                <th>GR No</th>
                                <th>GR Date</th>
                                <th>Student Type</th>
                                <th>Religion</th>
                                <th>Father Email</th>
                                <th>Father Occupation</th>
                                <th>Mother Name</th>
                                <th>City</th>
                                <th>Heard From</th>
                                <th>Emergency Person</th>
                                <th>Relationship</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Actions Modal -->
<div class="modal fade" id="studentActionsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-graduate mr-2"></i>
                    Student Actions
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="list-group list-group-flush">
                    <a href="#" id="modalProfileLink" class="list-group-item list-group-item-action">
                        <i class="fas fa-user mr-3 text-primary"></i> View Profile
                    </a>
                    <a href="#" id="modalChallansLink" class="list-group-item list-group-item-action">
                        <i class="fas fa-file-invoice mr-3 text-success"></i> Show Challans
                    </a>
                    <a href="#" id="modalCreateChallanLink" class="list-group-item list-group-item-action">
                        <i class="fas fa-plus mr-3 text-info"></i> Create Challan
                    </a>
                    <div class="dropdown-divider m-0"></div>
                    <a href="#" id="modalSlcLink" class="list-group-item list-group-item-action">
                        <i class="fas fa-certificate mr-3 text-warning"></i> <span id="slcLinkText">Generate SLC</span>
                    </a>
                    <div class="dropdown-divider m-0"></div>
                    <a href="#" id="modalEditLink" class="list-group-item list-group-item-action">
                        <i class="fas fa-edit mr-3 text-secondary"></i> Edit Profile
                    </a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Pass stats from PHP to JavaScript
var initialStats = <?= json_encode($stats ?? ['total_students' => 0, 'current_students' => 0, 'dropped_students' => 0, 'slc_count' => 0]) ?>;

// Update stats on page load (before DataTable loads)
$(document).ready(function() {
    $('#totalStudentsCount').text(initialStats.total_students);
    $('#currentStudentsCount').text(initialStats.current_students);
    $('#droppedStudentsCount').text(initialStats.dropped_students);
    $('#slcCount').text(initialStats.slc_count);
    console.log('Initial stats loaded:', initialStats);
});
</script>


<!-- Readmission Modal -->
<div class="modal fade" id="readmitModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fas fa-undo-alt mr-2"></i> Student Readmission
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="readmitLoading" class="text-center p-5">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
                    <p class="mt-3">Loading student information...</p>
                </div>
                <div id="readmitContent" style="display: none;">
                    <!-- Student Info Summary -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-info">
                                <div class="card-header">
                                    <h3 class="card-title">Student Information</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Student Name:</strong>
                                            <p id="readmitStudentName" class="mb-0"></p>
                                        </div>
                                        <div class="col-md-2">
                                            <strong>Registration No:</strong>
                                            <p id="readmitRegNo" class="mb-0"></p>
                                        </div>
                                        <div class="col-md-2">
                                            <strong>Father Name:</strong>
                                            <p id="readmitFatherName" class="mb-0"></p>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Previous Class:</strong>
                                            <p id="readmitPrevClass" class="mb-0"></p>
                                        </div>
                                        <div class="col-md-2">
                                            <strong>Leaving Date:</strong>
                                            <p id="readmitLeavingDate" class="mb-0"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                   <!-- Academic History (Session-wise Classes) -->
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history mr-2"></i> Academic & Fee History
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-2" style="max-height: 400px; overflow-y: auto;">
                <div id="academicHistoryBody" class="academic-history-container">
                    <div class="text-center p-3">
                        <i class="fas fa-spinner fa-spin"></i> Loading history...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
                    <!-- New Class Selection -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card card-success">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-chalkboard mr-2"></i> New Enrollment Details
                                    </h3>
                                </div>
                                <div class="card-body">
                                  <div class="form-group">
    <label><i class="fas fa-layer-group"></i> Select Class Section <span class="text-danger">*</span></label>
    <select id="readmitClsSecId" class="form-control select2" style="width: 100%;">
        <option value="">-- Select Class Section --</option>
        <?php if (!empty($sectionsclassinfo)): ?>
            <?php foreach ($sectionsclassinfo as $sec): ?>
                <option value="<?= (int)$sec['cls_sec_id'] ?>">
                    <?= esc($sec['label']) ?>
                </option>
            <?php endforeach; ?>
        <?php else: ?>
            <option value="" disabled>No class sections available</option>
        <?php endif; ?>
    </select>
</div>
                                    <div class="form-group">
                                        <label><i class="fas fa-calendar-alt"></i> Readmission Date <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control datepicker" id="readmitDate" 
                                               value="<?= date('d/m/Y') ?>" placeholder="DD/MM/YYYY">
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fas fa-sticky-note"></i> Remarks (Optional)</label>
                                        <textarea class="form-control" id="readmitRemarks" rows="2" placeholder="Add any remarks about readmission..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                     <div class="col-md-6">
    <div class="card card-info">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-money-bill-wave mr-2"></i> Fee Details
            </h3>
        </div>
        <div class="card-body">
            <div id="feeLoading" class="text-center" style="display: none;">
                <i class="fas fa-spinner fa-spin"></i> Loading fee structure...
            </div>
            <div id="feeStructureContainer">
                <div class="table-responsive">
                    <table class="table table-bordered" id="feeItemsTable">
                        <thead>
                            <tr class="bg-light">
                                <th style="width: 35%">Fee Type</th>
                                <th style="width: 25%">Standard Fee (PKR)</th>
                                <th style="width: 25%">Net Payable (PKR)</th>
                                <th style="width: 15%">Discount (PKR)</th>
                            </tr>
                            <tr class="bg-secondary">
                                <th colspan="4" class="text-center small">
                                    <i class="fas fa-info-circle"></i> Enter Net Payable amount, Discount will be auto-calculated
                                </th>
                            </tr>
                        </thead>
                        <tbody id="feeItemsBody">
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Select a class section to view fee structure
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-light font-weight-bold">
                                <th colspan="2" class="text-right">Total Payable:</th>
                                <th id="totalPayable" class="text-success">0.00</th>
                                <th id="totalDiscount" class="text-danger">0.00</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-warning" id="btnProcessReadmission">
                    <i class="fas fa-undo-alt mr-1"></i> Process Readmission & Generate Invoice
                </button>
            </div>
        </div>
    </div>
</div>
</section>

<style>
    .dataTables_wrapper .dt-buttons {
        float: left;
        margin-bottom: 10px;
    }
    .dataTables_wrapper .dataTables_filter {
        float: right;
    }
    .student-status-badge {
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 500;
    }
    .status-current {
        background-color: #28a745;
        color: white;
    }
    .status-dropped {
        background-color: #dc3545;
        color: white;
    }
    .dt-button-collection {
        max-height: 400px;
        overflow-y: auto;
    }
    .select2-container .select2-selection--single {
        height: 38px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }

    .dataTables_scrollBody {
    max-height: 65vh !important;
    overflow-y: auto !important;
    overflow-x: auto !important;
}

/* Sticky first column (Row number) */
#studentsTable td:first-child,
#studentsTable th:first-child {
    position: sticky;
    left: 0;
    background: white;
    z-index: 5;
}

#studentsTable th:first-child {
    background: #f8f9fa;
    z-index: 15;
}

/* Sticky action column (last column) */
#studentsTable td:last-child,
#studentsTable th:last-child {
    position: sticky;
    right: 0;
    background: white;
    z-index: 5;
    box-shadow: -2px 0 5px rgba(0,0,0,0.1);
}

#studentsTable th:last-child {
    background: #f8f9fa;
    z-index: 15;
    box-shadow: -2px 0 5px rgba(0,0,0,0.1);
}

/* Sticky name column (optional - keeps name visible) */
#studentsTable td:nth-child(6),
#studentsTable th:nth-child(6) {
    position: sticky;
    left: 40px;
    background: white;
    z-index: 4;
}

#studentsTable th:nth-child(6) {
    background: #f8f9fa;
    z-index: 14;
}

/* Scrollbar styling */
.dataTables_scrollBody::-webkit-scrollbar {
    height: 8px;
    width: 8px;
}

.dataTables_scrollBody::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.dataTables_scrollBody::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Floating actions */
.floating-actions {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.btn-floating {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    transition: all 0.3s;
}

.btn-floating:hover {
    transform: scale(1.1);
}

/* Column visibility dropdown */
.dt-button-collection {
    max-height: 500px !important;
    overflow-y: auto !important;
    columns: 2;
    column-gap: 20px;
}

.dt-button-collection .dt-button {
    width: 100%;
    break-inside: avoid;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* View preset buttons */
.view-presets .btn {
    margin-right: 5px;
}

/* Readmission Modal Styles */
#academicHistoryTable {
    font-size: 13px;
}

#academicHistoryTable tbody tr {
    cursor: pointer;
}

#academicHistoryTable tbody tr:hover {
    background-color: #f5f5f5;
}

#academicHistoryTable tbody tr.selected-history {
    background-color: #fff3cd;
    border-left: 3px solid #ffc107;
}

.fee-row {
    transition: all 0.2s;
}

.fee-row input {
    background-color: #fff;
}

.fee-row input:focus {
    background-color: #e8f0fe;
}

#totalPayable {
    font-size: 18px;
    font-weight: bold;
    color: #28a745;
}

.action-btn-readmit {
    margin-right: 5px;
}

/* Fee table styles */
.standard-amount-cell .badge {
    font-size: 13px;
    padding: 6px 10px;
    background-color: #6c757d;
}

.net-payable-input {
    text-align: right;
    font-weight: bold;
}

.net-payable-input:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.discount-cell {
    font-weight: bold;
    vertical-align: middle;
}

.discount-amount {
    font-size: 14px;
    font-weight: bold;
}

#totalPayable {
    font-size: 18px;
    font-weight: bold;
    color: #28a745;
}

#totalDiscount {
    font-size: 16px;
    font-weight: bold;
    color: #dc3545;
}

.fee-row td {
    vertical-align: middle;
}

.fee-row:hover {
    background-color: #f8f9fa;
}

</style>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2-bootstrap4.min.css">

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style type="text/css">
    
    /* Autocomplete styling */
.ui-autocomplete {
    max-height: 300px;
    overflow-y: auto;
    overflow-x: hidden;
    z-index: 9999;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    font-family: inherit;
}

.ui-autocomplete .ui-menu-item {
    padding: 0;
}

.ui-autocomplete .ui-menu-item-wrapper {
    padding: 8px 12px;
    border-bottom: 1px solid #f0f0f0;
}

.ui-autocomplete .ui-menu-item-wrapper.ui-state-active {
    background: #007bff;
    color: white;
    margin: 0;
    border-radius: 0;
}

.autocomplete-item {
    line-height: 1.4;
}

.autocomplete-item strong {
    font-size: 14px;
}

.autocomplete-item small {
    font-size: 11px;
    color: #6c757d;
}

.ui-autocomplete .ui-state-active .autocomplete-item small {
    color: rgba(255,255,255,0.8);
}

/* Clear button for search fields */
.input-group-clear {
    position: relative;
}

.input-group-clear .clear-search {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #999;
    z-index: 10;
}

.input-group-clear .clear-search:hover {
    color: #dc3545;
}

/* Fix for action button dropdown */
.dt-actions {
    position: relative;
    display: inline-block;
}

.dt-actions .dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    left: auto;
    z-index: 9999;
    min-width: 200px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

/* Ensure dropdown opens upward when near bottom */
.dt-actions .dropdown-menu.show {
    transform: translateY(0) !important;
}

/* For the last row, dropdown opens upward */
tr:last-child .dt-actions .dropdown-menu {
    top: auto;
    bottom: 100%;
    margin-bottom: 5px;
}

/* Make action column wider and not sticky */
#studentsTable th:last-child,
#studentsTable td:last-child {
    min-width: 120px;
    white-space: nowrap;
}

/* Remove sticky from action column to prevent clipping */
#studentsTable td:last-child,
#studentsTable th:last-child {
    position: static;
    box-shadow: none;
}

/* Better dropdown item styling */
.dropdown-item {
    padding: 8px 16px;
    font-size: 13px;
}

.dropdown-item i {
    width: 20px;
    text-align: center;
}

/* Dropdown animation */
.dropdown-menu {
    transition: all 0.2s ease;
}

/* Academic History Styles */
.academic-history-container .card-header {
    background-color: #f8f9fa;
    transition: background-color 0.2s;
}

.academic-history-container .card-header:hover {
    background-color: #e9ecef;
}

.academic-history-container .card-header .row {
    margin: 0;
    align-items: center;
}

.academic-history-container .table-sm th,
.academic-history-container .table-sm td {
    padding: 4px 8px;
    font-size: 12px;
}

.academic-history-container .badge-info {
    background-color: #17a2b8;
}

.academic-history-container .alert-info {
    font-size: 13px;
    padding: 8px 12px;
}
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<script>
$(function () {
    const CSRF = { name: '<?= csrf_token() ?>', hash: '<?= csrf_hash() ?>' };
    const printedBy = <?= json_encode((string) (session('member_name') ?? session('member_username') ?? 'User')) ?>;
    const ADMIN = <?= json_encode(rtrim(base_url('admin'), '/')) ?>;

    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function printedOn() {
        try {
            return new Date().toLocaleString('en-GB', { timeZone: 'Asia/Karachi' });
        } catch {
            return new Date().toLocaleString();
        }
    }

    // Render actions button
    function renderActions(_data, _type, row) {
        const isCurrent = (row.status === 1 || row.status_text === 'Current');
        const readmitButton = !isCurrent ? 
            `<button type="button" class="btn btn-sm btn-warning action-btn-readmit" onclick="showReadmitModal(${row.student_id})" title="Readmit Student" style="margin-right: 5px;">
                <i class="fas fa-undo-alt"></i> Readmit
            </button>` : '';
        
        return `
            <div class="btn-group" role="group">
                ${readmitButton}
                <button type="button" 
                        class="btn btn-sm btn-primary action-btn" 
                        onclick="showStudentActions(${row.student_id})">
                    <i class="fa fa-cog"></i> Actions
                </button>
            </div>
        `;
    }

    let localStateLoaded = false;
    let tableInitialized = false;

    // Autocomplete setup for Student Name
    $('#search_name').autocomplete({
        source: function(request, response) {
            if (request.term.length < 3) {
                response([]);
                return;
            }
            $.ajax({
                url: "<?= site_url('admin/students_print/autocomplete-student') ?>",
                dataType: "json",
                data: {
                    term: request.term,
                    campus_id: '<?= session('campus_id') ?>',
                    [CSRF.name]: CSRF.hash
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            label: `${item.first_name} ${item.last_name || ''} (${item.reg_no || 'No Reg'}) - Class: ${item.class_name || 'N/A'}`,
                            value: item.first_name + ' ' + (item.last_name || ''),
                            student_id: item.student_id,
                            reg_no: item.reg_no,
                            class_name: item.class_name
                        };
                    }));
                }
            });
        },
        minLength: 3,
        select: function(event, ui) {
            $('#search_name').val(ui.item.value);
            Swal.fire({
                icon: 'success',
                title: 'Student Selected',
                html: `<strong>${ui.item.label}</strong><br>Filtering results...`,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            });
            reloadDataTable();
            return false;
        }
    }).autocomplete("instance")._renderItem = function(ul, item) {
        return $("<li>")
            .append(`<div class="autocomplete-item">
                        <strong>${item.label.split(' (')[0]}</strong><br>
                        <small class="text-muted">${item.label.split(' (')[1] || ''}</small>
                     </div>`)
            .appendTo(ul);
    };

    // Autocomplete setup for Father Name
    $('#search_father').autocomplete({
        source: function(request, response) {
            if (request.term.length < 3) {
                response([]);
                return;
            }
            $.ajax({
                url: "<?= site_url('admin/students_print/autocomplete-father') ?>",
                dataType: "json",
                data: {
                    term: request.term,
                    campus_id: '<?= session('campus_id') ?>',
                    [CSRF.name]: CSRF.hash
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            label: `${item.father_name} (Student: ${item.student_name || item.first_name}) - ${item.reg_no || 'No Reg'}`,
                            value: item.father_name,
                            father_name: item.father_name,
                            student_id: item.student_id,
                            reg_no: item.reg_no
                        };
                    }));
                }
            });
        },
        minLength: 3,
        select: function(event, ui) {
            $('#search_father').val(ui.item.father_name);
            Swal.fire({
                icon: 'success',
                title: 'Father Selected',
                html: `<strong>${ui.item.father_name}</strong><br>Showing students with this father name...`,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            });
            reloadDataTable();
            return false;
        }
    });

    // Reload table only
    function reloadDataTable() {
        if ($.fn.DataTable.isDataTable('#studentsTable')) {
            $('#studentsTable').DataTable().ajax.reload(null, false);
        }
    }

    const debouncedReload = debounce(reloadDataTable, 800);

    // Initialize DataTable - OPTIMIZED (only ONE initialization)
    const table = $('#studentsTable').DataTable({
        processing: true,
        serverSide: true,
        orderMulti: true,
        scrollX: true,
        autoWidth: false,
        stateSave: false,  // CHANGED: Disabled to prevent extra calls
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        pageLength: 25,
        dom: 'Bfrtip',
        ajax: {
            url: "<?= site_url('admin/students_print/data') ?>",
            type: "POST",
            data: function(d) {
                d.search_name = $('#search_name').val() || '';
                d.search_father = $('#search_father').val() || '';
                d.class_id = $('#class_id').val() || '';
                d.cls_sec_id = $('#cls_sec_id').val() || '';
                
                let statusValue = $('input[name="status_filter"]:checked').val();
                if (statusValue === 'current') {
                    d.show_all = 'false';
                } else if (statusValue === 'all') {
                    d.show_all = 'true';
                } else {
                    d.show_all = 'false';
                }
                
                d[CSRF.name] = CSRF.hash;
            },
            error: function(xhr) {
                console.error('DataTables Ajax error:', xhr.status);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load data. Please try again.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        },
        columns: [
            { data: 'status', title: 'Status', visible: true },
            { data: 'status_text', title: 'Status Text', visible: false },
            { data: 'rownum', title: '#', orderable: false, searchable: false },
            { data: 'profile_photo', title: 'Photo', orderable: false, searchable: false },
            { data: 'student_id', title: 'Student ID', visible: false },
            { data: null, title: 'Actions', orderable: false, searchable: false, className: 'text-center', width: '100px', render: renderActions },
            { data: 'reg_no', title: 'Reg No' },
            { data: 'student_name', title: 'Name' },
            { data: 'father_name', title: 'Father' },
            { data: 'father_cnic', title: 'Father CNIC' },
            { data: 'std_cnic', title: 'Student CNIC' },
            { data: 'gender', title: 'Gender' },
            { data: 'dob', title: 'DOB' },
            { data: 'age', title: 'Age' },
            { data: 'class_name', title: 'Class' },
            { data: 'section_name', title: 'Section' },
            { data: 'discounted_amount', title: 'Discounted' },
            { data: 'date_of_admission', title: 'Admission Date' },
            { data: 'father_contact', title: 'Father Contact', visible: false },
            { data: 'mother_contact', title: 'Mother Contact', visible: false },
            { data: 'emergency_contact', title: 'Emergency', visible: false },
            { data: 'whatsapp_contact', title: 'WhatsApp', visible: false },
            { data: 'address', title: 'Address', visible: false },
            { data: 'previous_school', title: 'Previous School', visible: false },
            { data: 'ps_city', title: 'PS City', visible: false },
            { data: 'health_condition', title: 'Health Condition', visible: false },
            { data: 'major_injuries', title: 'Major Injuries', visible: false },
            { data: 'admission_class_id', title: 'Admission Class ID', visible: false },
            { data: 'admission_class', title: 'Admission Class', visible: false },
            { data: 'caste', title: 'Caste', visible: false },
            { data: 'gr_no', title: 'GR No', visible: false },
            { data: 'gr_date', title: 'GR Date', visible: false },
            { data: 'std_type', title: 'Student Type', visible: false },
            { data: 'std_type_id', title: 'Student Type ID', visible: false, defaultContent: '' },
            { data: 'religion', title: 'Religion', visible: false },
            { data: 'father_email', title: 'Father Email', visible: false },
            { data: 'father_occupation', title: 'Father Occupation', visible: false },
            { data: 'father_office_address', title: 'Father Office Address', visible: false },
            { data: 'm_name', title: 'Mother Name', visible: false },
            { data: 'city', title: 'City', visible: false },
            { data: 'hear_source', title: 'Heard From', visible: false },
            { data: 'emergency_contact_person', title: 'Emergency Contact Person', visible: false },
            { data: 'relationship', title: 'Relationship', visible: false },
            { data: 'has_slc', visible: false },
            { data: 'slc_id', visible: false }
        ],
        initComplete: function() {
            // Stats are already loaded from PHP - no AJAX call needed
            console.log('DataTable initialized');
        },
        drawCallback: function() {
            // Do nothing extra here
        },
        order: [[12, 'asc'], [13, 'asc'], [5, 'asc']],
        buttons: [
            { extend: 'colvis', text: 'Columns', columns: ':gt(3)', collectionLayout: 'two-column' },
            { extend: 'colvisGroup', text: 'Essential', show: [4, 5, 12, 13, 15], hide: [6, 7, 8, 9, 10, 11, 14, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40] },
            { extend: 'colvisGroup', text: 'Contacts', show: [4, 5, 15, 16, 17, 18, 3], hide: [6, 7, 8, 9, 10, 11, 12, 13, 14, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40] },
            { extend: 'colvisRestore', text: 'Show All' },
            {
                extend: 'csvHtml5',
                text: 'CSV',
                bom: true,
                title: () => 'students_' + new Date().toISOString().replace(/[-:]/g, '').slice(0, 15),
                exportOptions: { columns: (idx, node, col) => table.column(idx).visible() && idx > 3, stripHtml: true },
                customize: (csv) => 'Printed on,' + printedOn() + ',Printed by,' + printedBy + '\n' + csv
            },
            {
                extend: 'pdfHtml5',
                text: 'PDF',
                title: 'Students',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: { columns: (idx, node, col) => table.column(idx).visible() && idx > 3, stripHtml: true },
                customize: function(doc) {
                    doc.content.unshift({
                        columns: [
                            { text: 'Printed on: ' + printedOn(), alignment: 'left', margin: [0, 0, 0, 6] },
                            { text: 'Printed by: ' + printedBy, alignment: 'right', margin: [0, 0, 0, 6] }
                        ],
                        fontSize: 9
                    });
                    doc.styles.tableHeader.alignment = 'left';
                    doc.defaultStyle.fontSize = 9;
                    const tbl = doc.content.find(n => n.table);
                    if (tbl) {
                        const n = tbl.table.body[0].length;
                        tbl.table.widths = Array(n).fill('auto');
                    }
                }
            }
        ]
    });

    table.buttons().container().appendTo('#dtButtons');

    // REMOVED: stateLoaded event (since stateSave is false)
    // REMOVED: init.dt event with applyViewState (was causing extra call)
    
    // Column visibility adjustment - debounced
    let adjustTimeout;
    table.on('column-visibility.dt', function() {
        clearTimeout(adjustTimeout);
        adjustTimeout = setTimeout(() => table.columns.adjust(), 100);
    });
    
    $(document).on('shown.lte.pushmenu collapsed.lte.pushmenu', () => setTimeout(() => table.columns.adjust(), 200));
    $(window).on('resize', () => table.columns.adjust());

    // Instant filtering
    $('#search_name, #search_father').on('keyup', function(e) {
        if (e.keyCode === 13) {
            reloadDataTable();
        } else {
            debouncedReload();
        }
    });
    
    $('.clear-search').on('click', function() {
        $('#search_name').val('');
        $('#search_father').val('');
        reloadDataTable();
    });
    
    $('#class_id').on('change', function() {
        $('#cls_sec_id').val('').trigger('change.select2');
        reloadDataTable();
    });
    
    $('#cls_sec_id').on('change', function() {
        reloadDataTable();
    });
    
    $('input[name="status_filter"]').on('change', function() {
        reloadDataTable();
    });
    
    $('#resetFilters').on('click', function () {
        $('#search_name').val('');
        $('#search_father').val('');
        $('#class_id').val('').trigger('change.select2');
        $('#cls_sec_id').val('').trigger('change.select2');
        $('input[name="status_filter"][value="current"]').prop('checked', true);
        $('#btnCurrentOnly').button('toggle');
        reloadDataTable();
        updateFilterStatus();
    });
    
    function updateFilterStatus() {
        const filters = [];
        if ($('#search_name').val()) filters.push(`Name: ${$('#search_name').val()}`);
        if ($('#search_father').val()) filters.push(`Father: ${$('#search_father').val()}`);
        if ($('#class_id').val()) filters.push(`Class: ${$('#class_id option:selected').text()}`);
        if ($('#cls_sec_id').val()) filters.push(`Section: ${$('#cls_sec_id option:selected').text()}`);
        $('#filterStatus').text(filters.length ? `Active filters: ${filters.join(' | ')}` : 'No active filters');
    }
    
    updateFilterStatus();
    
    table.on('processing.dt', function(e, settings, processing) {
        $('#studentsTable').css('opacity', processing ? '0.6' : '1');
    });
    
    function getCurrentViewState() {
        const visible = [];
        table.columns().every(function (idx) { visible[idx] = this.visible(); });
        return { visible, order: table.order(), length: table.page.len() };
    }

    function applyViewState(state) {
        if (state.visible && Array.isArray(state.visible)) {
            state.visible.forEach((v, idx) => {
                if (idx === 2) return table.column(idx).visible(false, false);
                table.column(idx).visible(!!v, false);
            });
        }
        if (state.length) table.page.len(parseInt(state.length, 10));
        if (state.order && Array.isArray(state.order) && state.order.length) table.order(state.order);
        table.draw(false);
        setTimeout(() => table.columns.adjust(), 60);
    }

    $('#saveDefault').on('click', function () {
        const $btn = $(this).prop('disabled', true).text('Saving...');
        const payload = { page: 'students_browse', state: JSON.stringify(getCurrentViewState()) };
        payload[CSRF.name] = CSRF.hash;

        $.post("<?= site_url('admin/students_print/save-view') ?>", payload)
            .done(function (resp) {
                if (resp && resp.success) {
                    $btn.text('Saved ✓'); 
                    setTimeout(() => $btn.text('Save as default view').prop('disabled', false), 1200);
                } else {
                    alert('Could not save default view.'); 
                    $btn.prop('disabled', false).text('Save as default view');
                }
                if (resp && resp.csrf) { CSRF.hash = resp.csrf; }
            })
            .fail(function () { 
                alert('Error saving default view.'); 
                $btn.prop('disabled', false).text('Save as default view'); 
            });
    });
});

// Global function for showing student actions modal
function showStudentActions(studentId) {
    const ADMIN_URL = window.ADMIN || '<?= rtrim(base_url('admin'), '/') ?>';
    
    const table = $('#studentsTable').DataTable();
    const $button = $(`button[onclick="showStudentActions(${studentId})"]`).first();
    const row = table.row($button.closest('tr')).data();
    
    if (row) {
        const hasSlc = parseInt(row.has_slc) > 0;
        const slcId = row.slc_id;
        
        $('#modalProfileLink').attr('href', `${ADMIN_URL}/profile-student?id=${studentId}`);
        $('#modalChallansLink').attr('href', `${ADMIN_URL}/fee-chalan-single/download?id=${studentId}`);
        $('#modalCreateChallanLink').attr('href', `${ADMIN_URL}/fee-chalan/add?id=${studentId}`);
        $('#modalEditLink').attr('href', `${ADMIN_URL}/students/edit?id=${studentId}`);
        
        if (hasSlc && slcId) {
            $('#modalSlcLink').attr('href', `${ADMIN_URL}/slc/view/${slcId}`);
            $('#modalSlcLink').attr('target', '_blank');
            $('#modalSlcLink').off('click');
            $('#slcLinkText').text('View SLC');
        } else {
            $('#modalSlcLink').attr('href', 'javascript:void(0)');
            $('#modalSlcLink').removeAttr('target');
            $('#modalSlcLink').off('click').on('click', function(e) {
                e.preventDefault();
                generateSlc(studentId);
            });
            $('#slcLinkText').text('Generate SLC');
        }
        
        $('#studentActionsModal').modal('show');
    }
}

// SLC Generation Functions
function generateSlc(studentId) {
    $('#studentActionsModal').modal('hide');
    
    Swal.fire({
        title: 'Generate School Leaving Certificate',
        text: 'Are you sure you want to generate SLC for this student?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Generate',
        cancelButtonText: 'Cancel',
        showDenyButton: true,
        denyButtonText: 'Generate & Drop Student'
    }).then((result) => {
        if (result.isConfirmed) {
            processSlcGeneration(studentId, 'generate_only');
        } else if (result.isDenied) {
            processSlcGeneration(studentId, 'drop_with_slc');
        }
    });
}

function processSlcGeneration(studentId, action) {
    const CSRF = { name: '<?= csrf_token() ?>', hash: '<?= csrf_hash() ?>' };
    
    Swal.fire({
        title: 'Generating SLC',
        text: 'Please wait...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });
    
    fetch('<?= base_url('admin/addbulkstudents/get-edit-form') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            student_id: studentId,
            [CSRF.name]: CSRF.hash
        })
    })
    .then(response => response.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        const formData = {
            student_id: studentId,
            full_name: doc.querySelector('#slc_full_name')?.value || '',
            father_name: doc.querySelector('#slc_father_name')?.value || '',
            mother_name: doc.querySelector('#slc_mother_name')?.value || '',
            date_of_birth: doc.querySelector('#slc_dob')?.value || '',
            gender: doc.querySelector('#slc_gender')?.value || '',
            religion: doc.querySelector('#slc_religion')?.value || '',
            nationality: 'Pakistani',
            admission_date: doc.querySelector('#slc_admission_date')?.value || '',
            class_admission: doc.querySelector('#slc_class_admission')?.value || '',
            class_name: doc.querySelector('#slc_class')?.value?.split(' - ')[0] || '',
            section_name: doc.querySelector('#slc_class')?.value?.split(' - ')[1] || '',
            reg_no: doc.querySelector('#slc_reg_no')?.value || '',
            father_contact: doc.querySelector('#slc_father_contact')?.value || '',
            mother_contact: doc.querySelector('#slc_mother_contact')?.value || '',
            emergency_contact: doc.querySelector('#slc_emergency_contact')?.value || '',
            profile_photo: doc.querySelector('#slc_photo')?.value || '',
            leaving_date: doc.querySelector('#slc_leaving_date_edit')?.value || new Date().toISOString().split('T')[0],
            leaving_reason: doc.querySelector('#slc_leaving_reason_edit')?.value || 'On Request',
            conduct: doc.querySelector('#slc_conduct_edit')?.value || 'Good'
        };
        
        const nameParts = formData.full_name.split(' ');
        formData.first_name = nameParts[0] || '';
        formData.last_name = nameParts.slice(1).join(' ') || '';
        
        return fetch('<?= base_url('admin/addbulkstudents/generate-slc') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                student_data: JSON.stringify(formData),
                drop_option: action,
                [CSRF.name]: CSRF.hash
            })
        });
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'SLC Generated Successfully',
                html: `<p><strong>SLC Number:</strong> ${data.slc.slc_no}</p><p><strong>Student:</strong> ${data.slc.full_name}</p>`,
                showCancelButton: true,
                confirmButtonText: 'View SLC',
                cancelButtonText: 'Close',
                showDenyButton: true,
                denyButtonText: 'Print SLC'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open('<?= base_url('admin/slc/view/') ?>' + data.slc.id, '_blank');
                } else if (result.isDenied) {
                    const printWindow = window.open('<?= base_url('admin/slc/view/') ?>' + data.slc.id + '?print=1', '_blank');
                    printWindow.onload = function() { printWindow.print(); };
                }
            });
            $('#studentsTable').DataTable().ajax.reload(null, false);
        } else {
            Swal.fire({ icon: 'error', title: 'Generation Failed', text: data.msg || 'Could not generate SLC' });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred while generating SLC' });
    });
}

function editSlc(studentId, event) {
    if (event) event.preventDefault();
    $('#studentActionsModal').modal('hide');
    openEditModal(studentId);
}

function openEditModal(studentId) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'slcEditModal';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit SLC Information</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="slcEditModalBody">
                    <div class="text-center p-5">
                        <i class="fas fa-spinner fa-spin fa-3x"></i>
                        <p class="mt-3">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    const CSRF = { name: '<?= csrf_token() ?>', hash: '<?= csrf_hash() ?>' };
    
    fetch('<?= base_url('admin/addbulkstudents/get-edit-form') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            student_id: studentId,
            [CSRF.name]: CSRF.hash
        })
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('slcEditModalBody').innerHTML = html;
        $('#slcEditModal').modal('show');
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('slcEditModalBody').innerHTML = '<div class="alert alert-danger">Error loading form</div>';
    });
    
    $('#slcEditModal').on('hidden.bs.modal', function() { $(this).remove(); });
}

function checkExistingSlc(studentId) {
    const CSRF = { name: '<?= csrf_token() ?>', hash: '<?= csrf_hash() ?>' };
    return fetch('<?= base_url('admin/addbulkstudents/check-existing-slc') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            student_id: studentId,
            [CSRF.name]: CSRF.hash
        })
    }).then(response => response.json());
}
// ============================================
// READMISSION FUNCTIONS - FIXED VERSION
// ============================================

// Global variable to store current readmission student data
let currentReadmitStudent = null;
let currentFeeData = [];

// Helper to get ADMIN URL (defined in closure)
function getAdminUrl() {
    return window.ADMIN || '<?= rtrim(base_url('admin'), '/') ?>';
}
// Show Readmit Modal
function showReadmitModal(studentId) {
    const ADMIN_URL = getAdminUrl();
    currentReadmitStudent = studentId;
    
    // Reset modal content
    $('#readmitLoading').show();
    $('#readmitContent').hide();
    $('#feeItemsBody').html('<tr><td colspan="4" class="text-center text-muted">Select a class section to view fee structure</td></tr>');
    $('#totalPayable').text('0.00');
    $('#readmitClsSecId').val('').trigger('change.select2');
    $('#academicHistoryBody').html('<tr><td colspan="4" class="text-center">Loading history...</td></tr>');
    
    // Get CSRF token from meta tag or global variable
    const csrfName = $('meta[name="csrf-token-name"]').attr('content') || '<?= csrf_token() ?>';
    const csrfHash = $('meta[name="csrf-token-hash"]').attr('content') || '<?= csrf_hash() ?>';
    
    // Also try to get from window if available
    const finalCsrfName = window.CSRF_NAME || csrfName;
    const finalCsrfHash = window.CSRF_HASH || csrfHash;
     
    // Show modal
    $('#readmitModal').modal('show');
    
    // Fetch student info
    $.ajax({
        url: ADMIN_URL + '/students/get_student_readmit_info',
        type: 'POST',
        data: {
            student_id: studentId,
            campus_id: '<?= session('campus_id') ?>',
            [finalCsrfName]: finalCsrfHash
        },
        dataType: 'json',
        success: function(response) {
            $('#readmitLoading').hide();
            $('#readmitContent').show();
            
            if (response.success) {
                // Populate student info
                $('#readmitStudentName').text(response.student.first_name + ' ' + (response.student.last_name || ''));
                $('#readmitRegNo').text(response.student.reg_no || 'N/A');
                $('#readmitFatherName').text(response.student.father_name || 'N/A');
                $('#readmitPrevClass').text(response.previous_class ? 
                    response.previous_class.class_name + ' - ' + response.previous_class.section_name : 'N/A');
                $('#readmitLeavingDate').text(response.student.leaving_date || 'N/A');
                
                // Load academic history
                loadAcademicHistory(studentId);
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: response.msg || 'Failed to load student information' });
                $('#readmitModal').modal('hide');
            }
        },
        error: function(xhr) {
            $('#readmitLoading').hide();
            console.error('Error loading student info:', xhr);
            let errorMsg = 'Failed to load student information';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMsg = response.msg || errorMsg;
            } catch(e) {}
            Swal.fire({ icon: 'error', title: 'Error', text: errorMsg });
            $('#readmitModal').modal('hide');
        }
    });
}
// Load Academic History (Session-wise Classes)
// Load Academic History (Session-wise Classes)
// Load Academic History (Session-wise Classes with Fee Details)
function loadAcademicHistory(studentId) {
    const ADMIN_URL = getAdminUrl();
    const csrfName = $('meta[name="csrf-token-name"]').attr('content') || '<?= csrf_token() ?>';
    const csrfHash = $('meta[name="csrf-token-hash"]').attr('content') || '<?= csrf_hash() ?>';
    const finalCsrfName = window.CSRF_NAME || csrfName;
    const finalCsrfHash = window.CSRF_HASH || csrfHash;
    
    $.ajax({
        url: ADMIN_URL + '/students/get_fee_history',
        type: 'POST',
        data: {
            student_id: studentId,
            campus_id: '<?= session('campus_id') ?>',
            [finalCsrfName]: finalCsrfHash
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.fee_history && response.fee_history.length > 0) {
                let html = '';
                const months = response.months || {};
                
                response.fee_history.forEach(function(history, index) {
                    // Create collapsible section for each session
                    const sessionId = 'session_' + index;
                    const monthlyAmounts = history.monthly_amounts || {};
                    const nonMonthly = history.non_monthly || {};
                    
                    // Calculate total for this session
                    let sessionTotal = 0;
                    for (let month in monthlyAmounts) {
                        sessionTotal += monthlyAmounts[month] || 0;
                    }
                    for (let feeType in nonMonthly) {
                        sessionTotal += nonMonthly[feeType] || 0;
                    }
                    
                    html += `
                        <div class="card card-secondary mb-2">
                            <div class="card-header p-2" style="cursor: pointer;" data-toggle="collapse" data-target="#${sessionId}">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>${escapeHtml(history.session_name || 'N/A')}</strong>
                                    </div>
                                    <div class="col-md-3">
                                        Class: ${escapeHtml(history.class_section || 'N/A')}
                                    </div>
                                    <div class="col-md-3">
                                        Total: <span class="badge badge-info">PKR ${sessionTotal.toLocaleString()}</span>
                                    </div>
                                    <div class="col-md-3 text-right">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>
                            <div id="${sessionId}" class="collapse">
                                <div class="card-body p-2">
                                    ${Object.keys(monthlyAmounts).length > 0 ? `
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead>
                                                    <tr class="bg-light">
                                                        <th>Month</th>
                                                        <th>Amount (PKR)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${Object.keys(monthlyAmounts).filter(m => monthlyAmounts[m] > 0).map(month => `
                                                        <tr>
                                                            <td>${months[month] || month} ${history.session_year}</td>
                                                            <td>${monthlyAmounts[month].toLocaleString()}</td>
                                                        </tr>
                                                    `).join('')}
                                                </tbody>
                                            </table>
                                        </div>
                                    ` : ''}
                                    
                                    ${Object.keys(nonMonthly).length > 0 ? `
                                        <div class="table-responsive mt-2">
                                            <table class="table table-sm table-bordered">
                                                <thead>
                                                    <tr class="bg-light">
                                                        <th>Fee Type</th>
                                                        <th>Amount (PKR)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${Object.keys(nonMonthly).map(feeType => `
                                                        <tr>
                                                            <td>${escapeHtml(feeType)}</td>
                                                            <td>${nonMonthly[feeType].toLocaleString()}</td>
                                                        </tr>
                                                    `).join('')}
                                                </tbody>
                                            </table>
                                        </div>
                                    ` : ''}
                                    
                                    ${Object.keys(monthlyAmounts).length === 0 && Object.keys(nonMonthly).length === 0 ? 
                                        '<p class="text-muted text-center mb-0">No fee records found for this session</p>' : ''}
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                // Add summary at the bottom
                html += `
                    <div class="alert alert-info mt-2 mb-0">
                        <strong>Summary:</strong> 
                        Total Paid: PKR ${(response.total_paid || 0).toLocaleString()} | 
                        Total Discount: PKR ${(response.total_discount || 0).toLocaleString()} | 
                        Total Payments: ${response.total_payments || 0}
                    </div>
                `;
                
                $('#academicHistoryBody').html(html);
            } else {
                $('#academicHistoryBody').html('<tr><td colspan="4" class="text-center text-muted">No previous academic records found</td></tr>');
            }
        },
        error: function(xhr) {
            console.error('Error loading academic history:', xhr);
            $('#academicHistoryBody').html('<tr><td colspan="4" class="text-center text-danger">Failed to load academic history</td></tr>');
        }
    });
}// Load Fee Structure when class section changes
$(document).on('change', '#readmitClsSecId', function() {
    const ADMIN_URL = getAdminUrl();
    const clsSecId = $(this).val();
    const csrfHash = window.CSRF_HASH || '<?= csrf_hash() ?>';
    const csrfName = window.CSRF_NAME || '<?= csrf_token() ?>';
    
    if (!clsSecId) {
        $('#feeItemsBody').html('<tr><td colspan="4" class="text-center text-muted">Select a class section to view fee structure</td></tr>');
        $('#totalPayable').text('0.00');
        $('#totalDiscount').text('0.00');
        currentFeeData = [];
        return;
    }
    
    $('#feeItemsBody').html('<tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading fee structure...</td></tr>');
    
    $.ajax({
        url: ADMIN_URL + '/students/get_class_fee_amounts',
        type: 'POST',
        data: {
            cls_sec_id: clsSecId,
            [csrfName]: csrfHash
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success' && response.data && response.data.length > 0) {
                let html = '';
                currentFeeData = [];
                
                response.data.forEach(function(fee, index) {
                    const standardAmount = parseFloat(fee.default_amount) || 0;
                    const isMonthly = fee.is_monthly || false;
                    
                    currentFeeData.push({
                        fee_type_id: fee.fee_type_id,
                        fee_type_name: fee.fee_type_title,
                        standard_amount: standardAmount,
                        is_monthly: isMonthly,
                        net_payable: standardAmount,
                        discount: 0
                    });
                    
                    html += `<tr class="fee-row" data-fee-type-id="${fee.fee_type_id}" data-is-monthly="${isMonthly}">
                        <td>
                            ${fee.fee_type_title}
                            ${isMonthly ? '<span class="badge badge-info ml-1">Monthly</span>' : ''}
                            <input type="hidden" class="fee-type-id" value="${fee.fee_type_id}">
                            <input type="hidden" class="is-monthly" value="${isMonthly ? '1' : '0'}">
                            <input type="hidden" class="standard-amount" value="${standardAmount}">
                        </td>
                        <td class="standard-amount-cell">
                            <span class="badge badge-secondary">PKR ${standardAmount.toLocaleString()}</span>
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm net-payable-input" 
                                   value="${standardAmount}" step="100" style="width: 130px;">
                        </td>
                        <td class="discount-cell text-danger">
                            <span class="discount-amount">0</span>
                            <input type="hidden" class="discount-input" value="0">
                        </td>
                    </tr>`;
                });
                
                $('#feeItemsBody').html(html);
                updateTotals();
                
                // Add event listeners for net payable changes
                $('.net-payable-input').off('input').on('input', function() {
                    const row = $(this).closest('tr');
                    const standardAmount = parseFloat(row.find('.standard-amount').val()) || 0;
                    const netPayable = parseFloat($(this).val()) || 0;
                    
                    // Calculate discount (Standard - Net Payable)
                    let discount = Math.max(0, standardAmount - netPayable);
                    
                    // Update discount display
                    row.find('.discount-amount').text(discount.toLocaleString());
                    row.find('.discount-input').val(discount);
                    
                    // Update currentFeeData
                    const feeTypeId = row.data('fee-type-id');
                    const feeIndex = currentFeeData.findIndex(f => f.fee_type_id == feeTypeId);
                    if (feeIndex !== -1) {
                        currentFeeData[feeIndex].net_payable = netPayable;
                        currentFeeData[feeIndex].discount = discount;
                    }
                    
                    updateTotals();
                    
                    console.log('Updated - Standard:', standardAmount, 'Net:', netPayable, 'Discount:', discount);
                });
                
            } else {
                $('#feeItemsBody').html('<tr><td colspan="4" class="text-center text-warning">No fee structure found for this class</td></tr>');
                currentFeeData = [];
            }
        },
        error: function(xhr) {
            console.error('Error loading fee structure:', xhr);
            $('#feeItemsBody').html('<tr><td colspan="4" class="text-center text-danger">Failed to load fee structure</td></tr>');
        }
    });
});

// Update totals function
function updateTotals() {
    let totalPayable = 0;
    let totalDiscount = 0;
    
    $('.fee-row').each(function() {
        const netPayable = parseFloat($(this).find('.net-payable-input').val()) || 0;
        const discount = parseFloat($(this).find('.discount-input').val()) || 0;
        totalPayable += netPayable;
        totalDiscount += discount;
    });
    
    $('#totalPayable').text(totalPayable.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('#totalDiscount').text(totalDiscount.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
}

// Process Readmission
$(document).on('click', '#btnProcessReadmission', function() {
    const ADMIN_URL = getAdminUrl();
    const clsSecId = $('#readmitClsSecId').val();
    const readmissionDate = $('#readmitDate').val();
    const remarks = $('#readmitRemarks').val();
    const csrfHash = window.CSRF_HASH || '<?= csrf_hash() ?>';
    
    if (!clsSecId) {
        Swal.fire({ icon: 'warning', title: 'Validation Error', text: 'Please select a class section for readmission' });
        return;
    }
    
    if (!readmissionDate) {
        Swal.fire({ icon: 'warning', title: 'Validation Error', text: 'Please enter readmission date' });
        return;
    }
    
    // Prepare fee data
    const feeData = [];
    $('.fee-row').each(function() {
        const feeTypeId = $(this).data('fee-type-id');
        const netPayable = parseFloat($(this).find('.net-payable-input').val()) || 0;
        const discount = parseFloat($(this).find('.discount-input').val()) || 0;
        const isMonthly = $(this).find('.is-monthly').val() === '1';
        
        console.log('Fee item - Type ID:', feeTypeId, 
                    'Net Payable:', netPayable, 
                    'Discount:', discount, 
                    'Is Monthly:', isMonthly);
        
        if (netPayable > 0) {
            feeData.push({
                fee_type_id: feeTypeId,
                amount: netPayable,
                discount: discount,
                is_monthly: isMonthly,
                issue_date: readmissionDate,
                due_date: calculateDueDate(readmissionDate),
                fee_month: getFeeMonth(readmissionDate)
            });
        }
    });
    
    console.log('Fee data being sent:', feeData);
    
    if (feeData.length === 0) {
        Swal.fire({ icon: 'warning', title: 'Validation Error', text: 'Please enter valid fee amounts' });
        return;
    }
    
    // Disable button and show loading
    const $btn = $(this);
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...');
    
    $.ajax({
        url: ADMIN_URL + '/students/process_readmission',
        type: 'POST',
        data: JSON.stringify({
            student_id: currentReadmitStudent,
            cls_sec_id: clsSecId,
            readmission_date: readmissionDate,
            remarks: remarks,
            fee_data: feeData
        }),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': csrfHash
        },
        dataType: 'json',
        success: function(response) {
            $btn.prop('disabled', false).html('<i class="fas fa-undo-alt mr-1"></i> Process Readmission & Generate Invoice');
            
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Readmission Successful!',
                    html: `<p>Student has been readmitted successfully.</p>
                           <p><strong>Invoice Number:</strong> ${response.invoice_no || 'N/A'}</p>
                           <p><strong>Fee entries created:</strong> ${response.inserted_count || 0}</p>
                           ${response.monthly_discount > 0 ? `<p><strong>Monthly Discount Applied:</strong> PKR ${response.monthly_discount}</p>` : ''}`,
                    showCancelButton: true,
                    confirmButtonText: 'View Student Profile',
                    cancelButtonText: 'Close'
                }).then((result) => {
                    $('#readmitModal').modal('hide');
                    if (result.isConfirmed && response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        $('#studentsTable').DataTable().ajax.reload(null, false);
                    }
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Readmission Failed', text: response.msg || 'An error occurred' });
            }
        },
        error: function(xhr) {
            $btn.prop('disabled', false).html('<i class="fas fa-undo-alt mr-1"></i> Process Readmission & Generate Invoice');
            let errorMsg = 'An error occurred while processing readmission';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMsg = response.msg || errorMsg;
            } catch(e) {}
            Swal.fire({ icon: 'error', title: 'Error', text: errorMsg });
        }
    });
});
// Helper functions
function calculateDueDate(issueDateStr) {
    const parts = issueDateStr.split('/');
    const issueDate = new Date(parts[2], parts[1] - 1, parts[0]);
    const dueDate = new Date(issueDate);
    dueDate.setDate(dueDate.getDate() + 10);
    return `${dueDate.getDate().toString().padStart(2, '0')}/${(dueDate.getMonth() + 1).toString().padStart(2, '0')}/${dueDate.getFullYear()}`;
}

function getFeeMonth(dateStr) {
    const parts = dateStr.split('/');
    return `${parts[2]}-${parts[1].padStart(2, '0')}`;
}


// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    return String(text).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}
</script>
<?= $this->endSection() ?>