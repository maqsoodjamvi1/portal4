<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>
<?php
$initialShowAll = !empty($initial_show_all ?? false);
$schoolPrintName = isset($schoolinfo->system_name) && $schoolinfo->system_name !== ''
    ? $schoolinfo->system_name
    : 'School';
?>
<meta name="csrf-token-name" content="<?= csrf_token() ?>">
<meta name="csrf-token-hash" content="<?= csrf_hash() ?>">
<meta id="csrf-meta-chalan-edit" name="<?= csrf_token() ?>" content="<?= csrf_hash() ?>">
<script type="application/json" id="students-print-sections-json"><?= json_encode($classSections ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?></script>
<div class="no-print">
<?= view('components/page_header', [
    'title' => 'Students directory',
    'icon' => 'fas fa-address-book',
    'subtitle' => 'Browse, filter, and export students for this campus. Use Current only for active enrolments, or All students to include leavers.',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Students', 'url' => base_url('admin/students')],
        ['label' => 'Directory', 'active' => true],
    ],
]) ?>
</div>
<section class="content students-print-section">
    <div class="container-fluid students-print-layout">
        <!-- Quick Stats Cards (collapsed by default — expand to view counts) -->
        <div class="card sms-card card-outline card-secondary collapsed-card no-print mb-2" id="studentsStatsCard">
            <div class="card-header py-1">
                <h3 class="card-title text-sm mb-0"><i class="fas fa-chart-bar me-1"></i> Summary</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool btn-sm" data-card-widget="collapse" title="Show / hide summary">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-2">
        <div class="row mb-0 no-print">
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
            </div>
        </div>

 <!-- Filters: collapsed by default to maximize table area; expand from header tool -->
<div class="card card-outline card-primary collapsed-card no-print mb-2 students-filters-card" id="studentsFiltersCard">
    <div class="card-header py-1">
        <h3 class="card-title text-sm mb-0">
            <i class="fas fa-filter me-1"></i> Filters
            <span class="badge text-bg-light text-dark fw-normal ms-2 align-middle" id="filterStatusBadge" title="">Default: current students</span>
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool btn-sm" data-card-widget="collapse" title="Show / hide filters">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="card-body p-2 students-filters-compact">
        <div class="row align-items-end">
            <div class="col-xl-2 col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="small mb-0 text-muted" for="search_name">Student</label>
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="search_name" placeholder="Name…" title="Type 3+ characters for suggestions" autocomplete="off">
                    <span class="input-group-text clear-search px-2" style="cursor:pointer" title="Clear"><i class="fas fa-times"></i></span>
                </div>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="small mb-0 text-muted" for="search_father">Father</label>
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="search_father" placeholder="Father…" title="Type 3+ characters for suggestions" autocomplete="off">
                    <span class="input-group-text clear-search px-2" style="cursor:pointer" title="Clear"><i class="fas fa-times"></i></span>
                </div>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="small mb-0 text-muted" for="class_id">Class</label>
                <select id="class_id" class="form-control form-control-sm select2">
                    <option value="">All Classes</option>
                    <?php foreach (($classes ?? []) as $c): ?>
                        <option value="<?= (int)$c['class_id'] ?>"><?= esc($c['class_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="small mb-0 text-muted" for="cls_sec_id">Section</label>
                <select id="cls_sec_id" class="form-control form-control-sm select2">
                    <option value="">All Sections</option>
                    <?php foreach (($classSections ?? []) as $sec): ?>
                        <option value="<?= (int)$sec['cls_sec_id'] ?>" data-class-id="<?= (int)($sec['class_id'] ?? 0) ?>">
                            <?= esc($sec['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xl-auto col-lg-auto col-md-12 mb-1">
                <label class="small mb-0 text-muted d-block">Status</label>
                <div class="btn-group btn-group-sm btn-group-toggle" data-bs-toggle="buttons">
                    <label class="btn btn-outline-primary <?= !$initialShowAll ? 'active' : '' ?>" id="btnCurrentOnly">
                        <input type="radio" name="status_filter" value="current" <?= !$initialShowAll ? 'checked' : '' ?> autocomplete="off"> Current
                    </label>
                    <label class="btn btn-outline-secondary <?= $initialShowAll ? 'active' : '' ?>" id="btnAllStudents">
                        <input type="radio" name="status_filter" value="all" <?= $initialShowAll ? 'checked' : '' ?> autocomplete="off"> All
                    </label>
                </div>
            </div>
            <div class="col mb-1 text-md-end">
                <button type="button" id="resetFilters" class="btn btn-sm btn-outline-secondary mt-3 mt-md-0">
                    <i class="fas fa-undo"></i> Reset
                </button>
            </div>
        </div>
        <p class="mb-0 mt-1 small text-muted"><code>?status=all</code> in URL loads everyone; name/father search needs 3+ characters for autocomplete.</p>
    </div>
</div>
<span id="filterStatus" class="d-none" aria-hidden="true"></span>

        <!-- Students Table -->
        <div class="card students-print-card">
            <div class="card-header no-print py-2 d-flex flex-wrap align-items-center justify-content-between">
                <div class="d-flex flex-wrap align-items-center mb-1 mb-lg-0">
                    <h3 class="card-title m-0 me-2 mb-0">
                        <i class="fas fa-list me-1"></i> Student List
                    </h3>
                    <div class="btn-group btn-group-sm view-presets me-2" role="group" aria-label="Column presets">
                        <button type="button" class="btn btn-outline-primary" id="viewBasic" title="Core columns"><i class="fas fa-eye"></i> Basic</button>
                        <button type="button" class="btn btn-outline-success" id="viewContacts" title="Contact fields"><i class="fas fa-phone"></i> Contacts</button>
                        <button type="button" class="btn btn-outline-info" id="viewAcademic" title="Academic fields"><i class="fas fa-graduation-cap"></i> Academic</button>
                        <button type="button" class="btn btn-outline-secondary" id="viewAll" title="Show every column"><i class="fas fa-th-list"></i> All</button>
                    </div>
                </div>
                <div class="card-tools d-flex flex-wrap align-items-center justify-content-end">
                    <div class="btn-group btn-group-sm me-2 mb-1 no-print" role="group" aria-label="Print contact list">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Opens a print-ready page for all rows matching current filters (may take a few seconds for large lists)">
                            <i class="fas fa-address-book"></i> Print contacts
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <h6 class="dropdown-header">Grouping</h6>
                            <a class="dropdown-item" href="#" id="printContactListClassWise"><i class="fas fa-school text-muted me-2"></i>Class / section</a>
                            <a class="dropdown-item" href="#" id="printContactListFamilyWise"><i class="fas fa-users text-muted me-2"></i>Family (siblings)</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#" id="printSectionRoster"><i class="fas fa-list-alt text-muted me-2"></i>Section roster (names only)</a>
                            <div class="dropdown-divider"></div>
                            <span class="dropdown-item-text small text-muted">Uses filters above; includes every matching student in one page.</span>
                        </div>
                    </div>
                    <div id="dtButtons" class="dt-buttons-wrap"></div>
                </div>
            </div>
            <div class="card-body">
                <div class="students-print-head d-none d-print-block border-bottom mb-3 pb-2">
                    <div class="text-uppercase small text-muted mb-1"><?= esc($schoolPrintName) ?></div>
                    <h4 class="mb-1">Students directory</h4>
                    <div class="small text-muted" id="studentsPrintMeta">—</div>
                    <div class="small text-muted">Printed: <span id="studentsPrintWhen">—</span> · By: <?= esc((string) (session('member_name') ?? session('member_username') ?? 'User')) ?></div>
                </div>
                <p class="small text-muted no-print mb-0"><i class="fas fa-arrows-alt-h me-1"></i>Top &amp; bottom bars scroll wide tables.</p>
                <div class="students-hscroll-top no-print" id="studentsHScrollTop" title="Scroll columns horizontally">
                    <div class="students-hscroll-top-inner" id="studentsHScrollTopInner"></div>
                </div>
                <div id="studentsDtHost" class="students-dt-host students-print-table-wrap">
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
                                <th title="Father CNIC">F. CNIC</th>
                                <th title="Student CNIC">Std. CNIC</th>
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
                    <i class="fas fa-user-graduate me-2"></i>
                    Student Actions
                </h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="list-group list-group-flush">
                    <a href="#" id="modalProfileLink" class="list-group-item list-group-item-action">
                        <i class="fas fa-user me-3 text-primary"></i> View Profile
                    </a>
                    <a href="#" id="modalChallansLink" class="list-group-item list-group-item-action">
                        <i class="fas fa-file-invoice me-3 text-success"></i> Show Challans
                    </a>
                    <a href="#" id="modalCreateChallanLink" class="list-group-item list-group-item-action">
                        <i class="fas fa-plus me-3 text-info"></i> Add new fee chalan
                    </a>
                    <div class="dropdown-divider m-0"></div>
                    <a href="#" id="modalSlcLink" class="list-group-item list-group-item-action">
                        <i class="fas fa-certificate me-3 text-warning"></i> <span id="slcLinkText">Generate SLC</span>
                    </a>
                    <a href="#" id="modalBonafideLink" class="list-group-item list-group-item-action">
                        <i class="fas fa-file-signature me-3 text-info"></i> Generate Bonafide Certificate
                    </a>
                    <div class="dropdown-divider m-0"></div>
                    <a href="#" id="modalEditLink" class="list-group-item list-group-item-action">
                        <i class="fas fa-edit me-3 text-secondary"></i> Edit Profile
                    </a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Fee challan print options (same template as Fee Chalan → Generate) -->
<div class="modal fade" id="challanGenerateOptionsModal" tabindex="-1" role="dialog" aria-labelledby="challanGenerateOptionsTitle">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="challanGenerateOptionsTitle">
                    <i class="fas fa-file-invoice me-2"></i> Fee challan — options
                </h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Opens the <strong>three-copy</strong> challan (Bank / School / Student) in a new tab. Uses the same layout as <em>Fee Chalan → Generate</em>.</p>
                <input type="hidden" id="cg_student_id" value="">
                <input type="hidden" id="cg_parent_id" value="">
                <div class="form-group">
                    <label for="cg_fee_month">Fee month <span class="text-muted fw-normal">(optional)</span></label>
                    <input type="month" class="form-control" id="cg_fee_month" autocomplete="off">
                    <small class="form-text text-muted">Leave empty to include unpaid challans for all months (same as generate page default).</small>
                </div>
                <div class="form-group">
                    <label for="cg_show_discount">Discount columns</label>
                    <select class="form-control" id="cg_show_discount">
                        <option value="yes" selected>Show amount &amp; discount</option>
                        <option value="no">Hide discount (net only)</option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <div class="form-check form-check">
                        <input type="checkbox" class="form-check-input" id="cg_payment_history" checked>
                        <label class="form-check-label" for="cg_payment_history">Include payment history (last 6 months)</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-check form-check">
                        <input type="checkbox" class="form-check-input" id="cg_fine_after">
                        <label class="form-check-label" for="cg_fine_after">Show payable after due date (late fee), if campus setting applies</label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="cg_scope">Challan scope</label>
                    <select class="form-control" id="cg_scope">
                        <option value="student" selected>This student only — 3 copies</option>
                        <option value="family">Whole family (same parent) — 3 copies</option>
                    </select>
                    <small class="form-text text-muted" id="cg_scope_family_hint" style="display:none;">Family challan uses all siblings with unpaid fees for the selected filters.</small>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-success w-100" id="cgOpenChallanBtn">
                    <i class="fas fa-external-link-alt me-1"></i> Open challan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bonafide certificate options -->
<div class="modal fade" id="bonafideOptionsModal" tabindex="-1" role="dialog" aria-labelledby="bonafideOptionsTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="bonafideOptionsTitle">
                    <i class="fas fa-file-signature me-2"></i> Bonafide certificate options
                </h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="bc_student_id" value="">
                <div class="form-group mb-2">
                    <label class="mb-1">Recipient line</label>
                    <select class="form-control" id="bc_recipient_mode">
                        <option value="twmc" selected>To Whom It May Concern</option>
                        <option value="custom">Custom recipient</option>
                    </select>
                </div>
                <div class="form-group mb-2" id="bc_custom_recipient_wrap" style="display:none;">
                    <label class="mb-1" for="bc_recipient_name">Custom recipient text</label>
                    <input type="text" id="bc_recipient_name" class="form-control" maxlength="120" placeholder="e.g., The Visa Officer">
                </div>
                <div class="form-group mb-2">
                    <label class="mb-1" for="bc_reason"><strong>Certificate reason</strong></label>
                    <select id="bc_reason" class="form-control" required>
                        <option value="">Select reason</option>
                        <option value="school transfer">School transfer</option>
                        <option value="fee concession">Fee concession</option>
                        <option value="scholarship">Scholarship</option>
                        <option value="bank account opening">Bank account opening</option>
                        <option value="visa application">Visa application</option>
                        <option value="record purpose">Record purpose</option>
                    </select>
                    <small class="text-muted">Selected reason will be printed on the certificate.</small>
                </div>

                <label class="mb-1 mt-2">Show / hide information</label>
                <div class="border rounded p-2">
                    <div class="form-check form-check">
                        <input type="checkbox" class="form-check-input" id="bc_show_reg_no" checked>
                        <label class="form-check-label" for="bc_show_reg_no">Registration No</label>
                    </div>
                    <div class="form-check form-check">
                        <input type="checkbox" class="form-check-input" id="bc_show_father" checked>
                        <label class="form-check-label" for="bc_show_father">Father name</label>
                    </div>
                    <div class="form-check form-check">
                        <input type="checkbox" class="form-check-input" id="bc_show_class" checked>
                        <label class="form-check-label" for="bc_show_class">Current class / section</label>
                    </div>
                    <div class="form-check form-check">
                        <input type="checkbox" class="form-check-input" id="bc_show_dob" checked>
                        <label class="form-check-label" for="bc_show_dob">Date of birth</label>
                    </div>
                    <div class="form-check form-check">
                        <input type="checkbox" class="form-check-input" id="bc_show_current_fee">
                        <label class="form-check-label" for="bc_show_current_fee">Current fee (unpaid total)</label>
                    </div>
                    <div class="form-check form-check">
                        <input type="checkbox" class="form-check-input" id="bc_show_monthly_fee">
                        <label class="form-check-label" for="bc_show_monthly_fee">Monthly fee (standard - student discount)</label>
                    </div>
                    <div class="form-check form-check">
                        <input type="checkbox" class="form-check-input" id="bc_show_issue_date" checked>
                        <label class="form-check-label" for="bc_show_issue_date">Issue date</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-info w-100" id="bcOpenCertificateBtn">
                    <i class="fas fa-external-link-alt me-1"></i> Open certificate
                </button>
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
});
</script>

<!-- Column visibility: Bootstrap modal (reliable vs ColVis dropdown clipped by layout) -->
<div class="modal fade" id="studentsColumnModal" tabindex="-1" role="dialog" aria-labelledby="studentsColumnModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="studentsColumnModalTitle"><i class="fas fa-columns me-1"></i> Visible columns</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body py-2" id="studentsColumnModalBody"></div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary" id="studentsColumnApply"><i class="fas fa-check me-1"></i> Apply</button>
            </div>
        </div>
    </div>
</div>

<!-- Readmission Modal -->
<div class="modal fade" id="readmitModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fas fa-undo-alt me-2"></i> Student Readmission
                </h5>
                <button type="button" class="close" data-bs-dismiss="modal">
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
                    <i class="fas fa-history me-2"></i> Academic & Fee History
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
                                        <i class="fas fa-chalkboard me-2"></i> New Enrollment Details
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
                <i class="fas fa-money-bill-wave me-2"></i> Fee Details
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
                            <tr class="bg-light fw-bold">
                                <th colspan="2" class="text-end">Total Payable:</th>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-warning" id="btnProcessReadmission">
                    <i class="fas fa-undo-alt me-1"></i> Process Readmission & Generate Invoice
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

/* Top horizontal scroll synced with DataTables scrollX body */
.students-hscroll-top {
    overflow-x: auto;
    overflow-y: hidden;
    min-height: 14px;
    max-height: 18px;
    border: 1px solid #dee2e6;
    border-bottom: 0;
    background: #f1f3f5;
    cursor: pointer;
}

.students-hscroll-top-inner {
    height: 1px;
}

.students-dt-host .dataTables_scrollBody {
    border: 1px solid #dee2e6;
    border-top: 0;
    min-height: 280px;
}

.students-print-layout .content-header {
    padding-bottom: 0.25rem;
    margin-bottom: 0.25rem;
}

.students-print-layout .content-header h1 {
    font-size: 1.25rem;
}

.students-print-layout .students-print-card {
    margin-bottom: 0;
}

.students-print-layout .students-print-card > .card-body {
    padding-bottom: 0.5rem;
}

.students-print-layout #studentsStatsCard .small-box {
    margin-bottom: 0;
}

.students-print-layout #studentsStatsCard .small-box .inner {
    padding: 8px 10px;
}

.students-print-layout #studentsStatsCard .small-box h3 {
    font-size: 1.45rem;
}

.students-print-card .card-header {
    overflow: visible !important;
    position: relative;
    z-index: 6;
}

.students-filters-compact .select2-container {
    min-width: 100% !important;
}

.students-filters-compact .select2-container--default .select2-selection--single {
    min-height: 31px;
    padding-top: 1px;
    font-size: 0.875rem;
}

#studentsColumnModal {
    z-index: 1055;
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
    border-start: 3px solid #ffc107;
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

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
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

.academic-history-container .text-bg-info {
    background-color: #17a2b8;
}

.academic-history-container .alert-info {
    font-size: 13px;
    padding: 8px 12px;
}

/* DataTables Buttons toolbar (single toolbar — no duplicate Excel/PDF) */
.dt-buttons-wrap .dt-buttons {
    display: inline-flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
    gap: 4px;
}

.dt-buttons-wrap .dt-button {
    margin: 0 !important;
}

/* Print: clean A4 landscape student list */
@media print {
    @page {
        size: A4 landscape;
        margin: 8mm 10mm;
    }

    body {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        background: #fff !important;
        font-size: 9pt;
    }

    .no-print,
    .main-header,
    .main-sidebar,
    .main-footer,
    .control-sidebar,
    .preloader,
  .content-wrapper,
    .wrapper {
        margin: 0 !important;
        padding: 0 !important;
    }

    .content-wrapper > .content {
        padding: 0 !important;
    }

    .content > .container-fluid {
        padding: 0 !important;
        max-width: 100% !important;
    }

    .students-print-card {
        border: 0 !important;
        box-shadow: none !important;
    }

    .students-print-card .card-body {
        padding: 0 !important;
    }

    .students-print-table-wrap {
        overflow: visible !important;
    }

    .dataTables_scrollBody {
        overflow: visible !important;
    }

    #studentsTable {
        width: 100% !important;
        font-size: 8pt !important;
    }

    #studentsTable thead {
        display: table-header-group;
    }

    #studentsTable th,
    #studentsTable td {
        padding: 3px 5px !important;
        vertical-align: top !important;
        border: 0.5pt solid #ccc !important;
    }

    #studentsTable thead th {
        background: #1e293b !important;
        color: #fff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    #studentsTable tbody tr:nth-child(even) td {
        background: #f8fafc !important;
    }

    .badge {
        border: 0 !important;
        color: #000 !important;
        background: #e5e7eb !important;
    }

    .text-bg-success {
        background: #d1fae5 !important;
    }

    .text-bg-warning {
        background: #fef3c7 !important;
    }

    a[href]:after {
        content: none !important;
    }
}
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<script>
$(function () {
    const CSRF = { name: '<?= csrf_token() ?>', hash: '<?= csrf_hash() ?>' };
    const CSRF_HEADER = <?= json_encode(csrf_header()) ?>;

    function refreshStudentsPrintCsrf(xhr) {
        if (!xhr || !xhr.getResponseHeader) {
            return;
        }
        const hash = xhr.getResponseHeader(CSRF_HEADER) || xhr.getResponseHeader('X-CSRF-TOKEN');
        if (!hash) {
            return;
        }
        CSRF.hash = hash;
        $('meta[name="csrf-token-hash"]').attr('content', hash);
        const $meta = $('#csrf-meta-chalan-edit');
        if ($meta.length) {
            $meta.attr('content', hash);
        }
    }

    $(document).ajaxComplete(function (_e, xhr) {
        refreshStudentsPrintCsrf(xhr);
    });

    const printedBy = <?= json_encode((string) (session('member_name') ?? session('member_username') ?? 'User')) ?>;
    const studentsPrintUserId = <?= json_encode((string) (session('member_userid') ?? '0')) ?>;
    const studentsPrintCampusId = <?= json_encode((string) (session('member_campusid') ?? '0')) ?>;
    const schoolPrintName = <?= json_encode($schoolPrintName) ?>;
    const ADMIN = <?= json_encode(rtrim(base_url('admin'), '/')) ?>;
    window.ADMIN = ADMIN;

    if ($.fn.select2) {
        $('#class_id, #cls_sec_id').each(function () {
            const $el = $(this);
            if (!$el.hasClass('select2-hidden-accessible')) {
                $el.select2({ theme: 'bootstrap-5', width: '100%' });
            }
        });
    }

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

    /** RFC-style CSV cell quoting (Excel-safe; neutralize leading = + - @ as formula) */
    function csvQuoteCell(val) {
        let s = String(val ?? '');
        const c0 = s.charAt(0);
        if (c0 === '=' || c0 === '+' || c0 === '-' || c0 === '@') {
            s = "'" + s;
        }
        if (/[",\r\n]/.test(s)) {
            return '"' + s.replace(/"/g, '""') + '"';
        }
        return s;
    }

    function buildCsvPreamble() {
        const summary = ($('#filterStatus').text() || '').replace(/\s+/g, ' ').trim();
        const rows = [
            ['Report', 'Students directory'],
            ['School', schoolPrintName],
            ['Generated (PKT)', printedOn()],
            ['Printed by', printedBy],
            ['Filters / scope', summary || '—'],
            [],
        ];
        return rows.map((r) => (r.length ? r.map(csvQuoteCell).join(',') : '')).join('\r\n') + '\r\n';
    }

    function renderStatusBadge(data, type, row) {
        const s = parseInt(row.status, 10);
        if (type === 'sort' || type === 'filter') {
            return s;
        }
        if (s === 1) {
            return '<span class="badge text-bg-success">Current</span>';
        }
        if (s === 4) {
            return '<span class="badge text-bg-warning">Dropped</span>';
        }
        const t = row.status_text || data || 'Other';
        return '<span class="badge text-bg-secondary">' + $('<div>').text(t).html() + '</span>';
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

    let sectionsJsonList = [];
    try {
        sectionsJsonList = JSON.parse(document.getElementById('students-print-sections-json').textContent || '[]');
    } catch (e) {
        sectionsJsonList = [];
    }

    function rebuildClassSectionOptions() {
        const cid = parseInt($('#class_id').val(), 10) || 0;
        const prev = $('#cls_sec_id').val();
        const $sel = $('#cls_sec_id');
        $sel.empty();
        $sel.append($('<option></option>').val('').text('All Sections'));
        sectionsJsonList.forEach(function (sec) {
            const sid = parseInt(String(sec.class_id || 0), 10);
            if (cid && sid !== cid) {
                return;
            }
            const id = parseInt(String(sec.cls_sec_id || 0), 10);
            const label = sec.label || '';
            $sel.append(
                $('<option></option>')
                    .val(id)
                    .text(label)
                    .attr('data-class-id', sid)
            );
        });
        if (prev && $sel.find('option[value="' + prev + '"]').length) {
            $sel.val(prev);
        } else {
            $sel.val('');
        }
        $sel.trigger('change.select2');
    }

    function syncStudentsPrintStatusUrl() {
        const showAll = $('input[name="status_filter"]:checked').val() === 'all';
        try {
            const u = new URL(window.location.href);
            u.searchParams.set('status', showAll ? 'all' : '1');
            history.replaceState({}, '', u.toString());
        } catch (e) { /* ignore */ }
    }

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
                    campus_id: '<?= (int) session('member_campusid') ?>'
                },
                success: function(data, _textStatus, xhr) {
                    refreshStudentsPrintCsrf(xhr);
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
                    campus_id: '<?= (int) session('member_campusid') ?>'
                },
                success: function(data, _textStatus, xhr) {
                    refreshStudentsPrintCsrf(xhr);
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

    if ($.fn.DataTable.isDataTable('#studentsTable')) {
        $('#studentsTable').DataTable().destroy();
    }

    const STUDENTS_DATA_URL = "<?= site_url('admin/students_print/data') ?>";

    function buildContactListPrintUrl(mode) {
        const ADMIN_URL = window.ADMIN || '<?= rtrim(base_url('admin'), '/') ?>';
        const params = new URLSearchParams();
        params.set('mode', mode === 'family' ? 'family' : 'class');
        const statusValue = $('input[name="status_filter"]:checked').val();
        params.set('show_all', statusValue === 'all' ? '1' : '0');
        const cid = $('#class_id').val();
        if (cid) {
            params.set('class_id', cid);
        }
        const csid = $('#cls_sec_id').val();
        if (csid) {
            params.set('cls_sec_id', csid);
        }
        const sn = ($('#search_name').val() || '').trim();
        if (sn) {
            params.set('search_name', sn);
        }
        const sf = ($('#search_father').val() || '').trim();
        if (sf) {
            params.set('search_father', sf);
        }
        return ADMIN_URL + '/students_print/contact-list?' + params.toString();
    }

    function buildSectionRosterPrintUrl() {
        const ADMIN_URL = window.ADMIN || '<?= rtrim(base_url('admin'), '/') ?>';
        const params = new URLSearchParams();
        const cid = $('#class_id').val();
        if (cid) {
            params.set('class_id', cid);
        }
        const csid = $('#cls_sec_id').val();
        if (csid) {
            params.set('cls_sec_id', csid);
        }
        return ADMIN_URL + '/students_print/section-roster?' + params.toString();
    }

    $('#printContactListClassWise').on('click', function (e) {
        e.preventDefault();
        window.open(buildContactListPrintUrl('class'), '_blank', 'noopener,noreferrer');
    });
    $('#printContactListFamilyWise').on('click', function (e) {
        e.preventDefault();
        window.open(buildContactListPrintUrl('family'), '_blank', 'noopener,noreferrer');
    });
    $('#printSectionRoster').on('click', function (e) {
        e.preventDefault();
        window.open(buildSectionRosterPrintUrl(), '_blank', 'noopener,noreferrer');
    });

    function studentsTableScrollHeightPx() {
        const $host = $('#studentsDtHost');
        if (!$host.length) {
            return Math.max(320, window.innerHeight - 320);
        }
        const hostTop = $host.offset().top || 0;
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop || 0;
        const viewportBottom = scrollTop + window.innerHeight;
        const reservedBottom = 96;
        return Math.max(280, Math.floor(viewportBottom - hostTop - reservedBottom));
    }

    function applyStudentsTableScrollHeight() {
        const px = studentsTableScrollHeightPx() + 'px';
        $('#studentsDtHost .dataTables_scrollBody').css({ maxHeight: px, height: px });
    }

    // Initialize DataTable — scrollY keeps horizontal scrollbar in viewport; dom omits B (avoids duplicate button bars with scrollX) and f (no global search; use filters above).
    const table = $('#studentsTable').DataTable({
        processing: true,
        serverSide: true,
        orderMulti: true,
        scrollX: true,
        scrollY: studentsTableScrollHeightPx() + 'px',
        scrollCollapse: false,
        autoWidth: false,
        stateSave: false,  // CHANGED: Disabled to prevent extra calls
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        pageLength: 25,
        dom: 'lrtip',
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
            complete: function(xhr) {
                refreshStudentsPrintCsrf(xhr);
            },
            error: function(xhr, status) {
                if (status === 'abort') {
                    return;
                }
                refreshStudentsPrintCsrf(xhr);
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
            { data: 'rownum', title: '#', orderable: false, searchable: false },
            { data: 'profile_photo', title: 'Photo', orderable: false, searchable: false },
            { data: 'student_id', title: 'Student ID', visible: false },
            { data: null, title: 'Actions', orderable: false, searchable: false, className: 'text-center', width: '100px', render: renderActions },
            { data: 'reg_no', title: 'Reg No' },
            { data: 'student_name', title: 'Name' },
            { data: 'status', title: 'Status', render: renderStatusBadge },
            { data: 'father_name', title: 'Father' },
            { data: 'father_cnic', title: 'F. CNIC' },
            { data: 'std_cnic', title: 'Std. CNIC' },
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
            { data: 'status_text', title: 'Status Text', visible: false },
            { data: 'has_slc', visible: false },
            { data: 'slc_id', visible: false },
            { data: 'parent_id', visible: false }
        ],
        initComplete: function () {
            $('#dtButtons').empty();
            table.buttons().container().appendTo('#dtButtons');
            applyStudentsTableScrollHeight();
            table.columns.adjust();
            bindStudentsHorizontalScrollSync();
        },
        drawCallback: function () {
            applyStudentsTableScrollHeight();
            bindStudentsHorizontalScrollSync();
        },
        order: [[2, 'asc']],
        buttons: [
            {
                text: '<i class="fas fa-columns"></i> Columns',
                className: 'btn btn-secondary btn-sm',
                action: function () {
                    openStudentsColumnModal();
                },
            },
            {
                text: '<i class="fas fa-file-csv"></i> CSV (all rows)',
                className: 'btn btn-secondary btn-sm',
                action: function () { exportStudentsCsvFull(); },
            },
            {
                text: '<i class="fas fa-file-pdf"></i> PDF (all rows)',
                className: 'btn btn-secondary btn-sm',
                action: function () { exportStudentsPdfFull(); },
            },
        ],
    });

    function showAllPostForExport() {
        const statusValue = $('input[name="status_filter"]:checked').val();
        return statusValue === 'all' ? 'true' : 'false';
    }

    function stripExportCell(val) {
        if (val === null || val === undefined) {
            return '';
        }
        return $('<div>').html(String(val)).text().replace(/\s+/g, ' ').trim();
    }

    function fetchAllStudentRows(done) {
        $.ajax({
            url: STUDENTS_DATA_URL,
            type: 'POST',
            dataType: 'json',
            data: {
                draw: 1,
                start: 0,
                length: 1,
                export_all: '1',
                search_name: $('#search_name').val() || '',
                search_father: $('#search_father').val() || '',
                class_id: $('#class_id').val() || '',
                cls_sec_id: $('#cls_sec_id').val() || '',
                show_all: showAllPostForExport(),
                [CSRF.name]: CSRF.hash,
            },
        }).done(function (res) {
            if (!res || !Array.isArray(res.data)) {
                done(new Error('bad'), []);
                return;
            }
            done(null, res.data);
        }).fail(function (xhr) {
            done(xhr, []);
        });
    }

    function getExportColumnMeta() {
        const meta = [];
        table.columns().every(function (idx) {
            if ([1, 2, 3].indexOf(idx) !== -1) {
                return;
            }
            if (!this.visible()) {
                return;
            }
            const col = this.settings()[0].aoColumns[idx];
            const key = col.data;
            if (key === null || key === undefined || key === '') {
                return;
            }
            const title = (col.sTitle && String(col.sTitle).trim()) || $(this.header()).text().trim() || String(key);
            meta.push({ key: key, title: title });
        });
        return meta;
    }

    function buildCsvFromRows(rows, colMeta) {
        const headerRow = colMeta.map(function (c) { return csvQuoteCell(c.title); });
        const lines = [headerRow.join(',')];
        rows.forEach(function (row) {
            const cells = colMeta.map(function (c) {
                return csvQuoteCell(stripExportCell(row[c.key]));
            });
            lines.push(cells.join(','));
        });
        return lines.join('\r\n');
    }

    function downloadBlob(filename, blob) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(url);
    }

    function exportStudentsCsvFull() {
        const colMeta = getExportColumnMeta();
        if (!colMeta.length) {
            Swal.fire({ icon: 'warning', title: 'No columns', text: 'Show at least one data column before exporting.', timer: 2800, toast: true, position: 'top-end', showConfirmButton: false });
            return;
        }
        Swal.fire({ title: 'Loading all rows…', allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });
        fetchAllStudentRows(function (err, rows) {
            Swal.close();
            if (err) {
                Swal.fire({ icon: 'error', title: 'Export failed', text: 'Could not load all rows for the current filters.' });
                return;
            }
            if (!rows.length) {
                Swal.fire({ icon: 'info', title: 'No data', text: 'Nothing to export.', timer: 2500, toast: true, position: 'top-end', showConfirmButton: false });
                return;
            }
            const bodyCsv = buildCsvFromRows(rows, colMeta);
            const csv = buildCsvPreamble() + bodyCsv;
            const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
            downloadBlob('students_directory_' + new Date().toISOString().slice(0, 10) + '.csv', blob);
        });
    }

    function exportStudentsPdfFull() {
        if (typeof pdfMake === 'undefined') {
            Swal.fire({ icon: 'error', title: 'PDF unavailable', text: 'pdfMake is not loaded.' });
            return;
        }
        const colMeta = getExportColumnMeta();
        if (!colMeta.length) {
            return;
        }
        Swal.fire({ title: 'Loading all rows…', allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });
        fetchAllStudentRows(function (err, rows) {
            Swal.close();
            if (err) {
                Swal.fire({ icon: 'error', title: 'Export failed', text: 'Could not load all rows for the current filters.' });
                return;
            }
            if (!rows.length) {
                Swal.fire({ icon: 'info', title: 'No data', text: 'Nothing to export.', timer: 2500, toast: true, position: 'top-end', showConfirmButton: false });
                return;
            }
            const headRow = colMeta.map(function (c) { return String(c.title).slice(0, 36); });
            const body = rows.map(function (row) {
                return colMeta.map(function (c) {
                    return stripExportCell(row[c.key]).slice(0, 160);
                });
            });
            const docDef = {
                pageOrientation: 'landscape',
                pageSize: 'A4',
                pageMargins: [14, 22, 14, 14],
                defaultStyle: { fontSize: 6 },
                content: [
                    { text: schoolPrintName, fontSize: 11, bold: true, margin: [0, 0, 0, 4] },
                    { text: 'Students directory — ' + rows.length + ' row(s)', fontSize: 9, margin: [0, 0, 0, 2] },
                    { text: 'Printed (PKT): ' + printedOn() + ' · By: ' + printedBy, fontSize: 8, margin: [0, 0, 0, 2] },
                    { text: ($('#filterStatus').text() || '').replace(/\s+/g, ' ').trim(), fontSize: 7, margin: [0, 0, 0, 8] },
                    {
                        table: {
                            headerRows: 1,
                            widths: Array(colMeta.length).fill('auto'),
                            body: [headRow].concat(body),
                        },
                        layout: 'lightHorizontalLines',
                    },
                ],
            };
            pdfMake.createPdf(docDef).download('students_directory_' + new Date().toISOString().slice(0, 10) + '.pdf');
        });
    }

    let hScrollSyncBound = false;
    function bindStudentsHorizontalScrollSync() {
        const $body = $('#studentsDtHost .dataTables_scrollBody');
        const $top = $('#studentsHScrollTop');
        const $inner = $('#studentsHScrollTopInner');
        if (!$body.length || !$top.length) {
            return;
        }
        const bodyEl = $body[0];
        const topEl = $top[0];
        const syncWidth = function () {
            $inner.css('width', bodyEl.scrollWidth + 'px');
        };
        syncWidth();
        if (bodyEl.scrollWidth <= bodyEl.clientWidth + 6) {
            $top.css({ visibility: 'hidden', height: '0', minHeight: '0', border: '0' });
            return;
        }
        $top.css({ visibility: 'visible', height: '', minHeight: '', border: '' });
        if (!hScrollSyncBound) {
            hScrollSyncBound = true;
            $top.on('scroll.stuXsync', function () {
                bodyEl.scrollLeft = topEl.scrollLeft;
            });
            $body.on('scroll.stuXsync', function () {
                topEl.scrollLeft = bodyEl.scrollLeft;
            });
        } else {
            topEl.scrollLeft = bodyEl.scrollLeft;
            syncWidth();
        }
    }

    function openStudentsColumnModal() {
        const $body = $('#studentsColumnModalBody').empty();
        const settings = table.settings()[0];
        const ao = settings.aoColumns;
        const locked = { 2: true, 3: true };
        for (let idx = 0; idx < ao.length; idx++) {
            if (locked[idx]) {
                continue;
            }
            const col = ao[idx];
            const colApi = table.column(idx);
            let title = (col.sTitle && String(col.sTitle).trim()) || $(colApi.header()).text().trim();
            if (!title && col.data !== undefined && col.data !== null && col.data !== '') {
                title = String(col.data);
            }
            if (!title) {
                title = 'Column ' + idx;
            }
            const id = 'stu_col_cb_' + idx;
            const checked = colApi.visible() ? ' checked' : '';
            const safeTitle = $('<div>').text(title || ('Column ' + idx)).html();
            $body.append(
                '<div class="form-check form-check mb-1">' +
                '<input type="checkbox" class="form-check-input stu-col-cb" id="' + id + '" data-col-idx="' + idx + '"' + checked + '>' +
                '<label class="form-check-label" for="' + id + '">' + safeTitle + '</label></div>'
            );
        }
        $('#studentsColumnModal').modal('show');
    }

    const studentsColumnStateKey = `students_print_column_state_v1_u${studentsPrintUserId}_c${studentsPrintCampusId}`;
    function persistStudentsColumnState() {
        try {
            localStorage.setItem(studentsColumnStateKey, JSON.stringify(getCurrentViewState()));
        } catch (e) {
            // Ignore quota/private-mode issues silently
        }
    }

    function restoreStudentsColumnState() {
        try {
            const raw = localStorage.getItem(studentsColumnStateKey);
            if (!raw) return false;
            const parsed = JSON.parse(raw);
            if (!parsed || typeof parsed !== 'object') return false;
            applyViewState(parsed);
            return true;
        } catch (e) {
            return false;
        }
    }

    $(document).on('click', '#studentsColumnApply', function () {
        if (!$.fn.DataTable.isDataTable('#studentsTable')) {
            return;
        }
        const $applyBtn = $(this);
        // Move focus outside modal before hide to avoid aria-hidden focus warning.
        $applyBtn.trigger('blur');
        $('#viewBasic').trigger('focus');
        const dt = $('#studentsTable').DataTable();
        $('.stu-col-cb').each(function () {
            const idx = parseInt($(this).data('col-idx'), 10);
            if (isNaN(idx)) {
                return;
            }
            dt.column(idx).visible($(this).is(':checked'), false);
        });
        dt.column(2).visible(false, false);
        dt.column(3).visible(true, false);
        dt.draw(false);
        persistStudentsColumnState();
        $('#studentsColumnModal').modal('hide');
        setTimeout(function () {
            dt.columns.adjust();
            bindStudentsHorizontalScrollSync();
        }, 80);
    });

    function applyColumnPreset(showIdx) {
        const show = new Set(showIdx);
        const n = table.columns().count();
        for (let i = 0; i < n; i++) {
            table.column(i).visible(show.has(i), false);
        }
        table.column(2).visible(false, false);
        table.draw(false);
        persistStudentsColumnState();
        setTimeout(() => table.columns.adjust(), 80);
    }

    // REMOVED: stateLoaded event (since stateSave is false)
    // REMOVED: init.dt event with applyViewState (was causing extra call)
    
    // Column visibility adjustment - debounced
    let adjustTimeout;
    table.on('column-visibility.dt', function() {
        clearTimeout(adjustTimeout);
        adjustTimeout = setTimeout(function () {
            table.columns.adjust();
            bindStudentsHorizontalScrollSync();
        }, 120);
    });
    
    $(document).on('shown.lte.pushmenu collapsed.lte.pushmenu', function () {
        setTimeout(function () {
            applyStudentsTableScrollHeight();
            table.columns.adjust();
            bindStudentsHorizontalScrollSync();
        }, 200);
    });
    $(window).on('resize', function () {
        applyStudentsTableScrollHeight();
        table.columns.adjust();
        bindStudentsHorizontalScrollSync();
    });

    $(document).on('expanded.lte.cardwidget collapsed.lte.cardwidget', '#studentsStatsCard, #studentsFiltersCard', function () {
        setTimeout(function () {
            applyStudentsTableScrollHeight();
            table.columns.adjust();
            bindStudentsHorizontalScrollSync();
        }, 280);
    });

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
        rebuildClassSectionOptions();
        reloadDataTable();
        updateFilterStatus();
    });

    $('#cls_sec_id').on('change', function() {
        reloadDataTable();
        updateFilterStatus();
    });

    $('input[name="status_filter"]').on('change', function() {
        syncStudentsPrintStatusUrl();
        reloadDataTable();
        updateFilterStatus();
    });

    $('#viewBasic').on('click', function () {
        applyColumnPreset([0, 1, 3, 4, 5, 6, 13, 14, 16]);
    });
    $('#viewContacts').on('click', function () {
        applyColumnPreset([0, 1, 3, 4, 5, 6, 17, 18, 19, 20, 21, 22]);
    });
    $('#viewAcademic').on('click', function () {
        applyColumnPreset([0, 1, 3, 4, 5, 6, 11, 12, 13, 14, 15, 16, 22, 23, 24, 25, 27, 28, 29]);
    });
    $('#viewAll').on('click', function () {
        const n = table.columns().count();
        for (let i = 0; i < n; i++) {
            table.column(i).visible(true, false);
        }
        table.column(2).visible(false, false);
        table.draw(false);
        persistStudentsColumnState();
        setTimeout(() => table.columns.adjust(), 80);
    });

    $('#resetFilters').on('click', function () {
        $('#search_name').val('');
        $('#search_father').val('');
        $('#class_id').val('').trigger('change.select2');
        rebuildClassSectionOptions();
        $('#cls_sec_id').val('').trigger('change.select2');
        $('input[name="status_filter"][value="current"]').prop('checked', true);
        $('input[name="status_filter"][value="all"]').prop('checked', false);
        $('#btnCurrentOnly').addClass('active');
        $('#btnAllStudents').removeClass('active');
        syncStudentsPrintStatusUrl();
        reloadDataTable();
        updateFilterStatus();
    });
    
    function updateFilterStatus() {
        const filters = [];
        if ($('#search_name').val()) filters.push(`Name: ${$('#search_name').val()}`);
        if ($('#search_father').val()) filters.push(`Father: ${$('#search_father').val()}`);
        if ($('#class_id').val()) filters.push(`Class: ${$('#class_id option:selected').text()}`);
        if ($('#cls_sec_id').val()) filters.push(`Section: ${$('#cls_sec_id option:selected').text()}`);
        if ($('input[name="status_filter"]:checked').val() === 'all') {
            filters.push('Status: all students');
        }
        const statusMsg = filters.length ? `Active filters: ${filters.join(' | ')}` : 'No active filters (current students only)';
        $('#filterStatus').text(statusMsg);
        $('#studentsPrintMeta').text(statusMsg);
        const badgeShort = filters.length
            ? (filters.length + ' filter' + (filters.length > 1 ? 's' : ''))
            : 'Current students';
        $('#filterStatusBadge').text(badgeShort).attr('title', statusMsg);
    }

    updateFilterStatus();

    table.on('draw.dt', function () {
        updateFilterStatus();
    });

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
        persistStudentsColumnState();
        setTimeout(() => table.columns.adjust(), 60);
    }

    // Keep focus on a safe target when modal closes
    $('#studentsColumnModal').on('hidden.bs.modal', function () {
        $('#viewBasic').trigger('focus');
    });

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

    // Auto-restore user-selected visible columns/order/length on next visits
    restoreStudentsColumnState();
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
        const parentId = parseInt(row.parent_id, 10) || 0;

        $('#studentActionsModal').data('activeStudentId', studentId);
        $('#studentActionsModal').data('activeParentId', parentId);
        
        $('#modalProfileLink').attr('href', `${ADMIN_URL}/profile-student?id=${studentId}`);
        $('#modalChallansLink').attr('href', '#');
        $('#modalCreateChallanLink').attr('href', '#');
        $('#modalEditLink').attr('href', `${ADMIN_URL}/students/edit?id=${studentId}`);
        $('#modalBonafideLink').off('click').on('click', function(e) {
            e.preventDefault();
            $('#studentActionsModal').modal('hide');
            $('#bc_student_id').val(studentId);
            $('#bc_recipient_mode').val('twmc').trigger('change');
            $('#bc_recipient_name').val('');
            $('#bc_reason').val('');
            $('#bc_show_reg_no').prop('checked', true);
            $('#bc_show_father').prop('checked', true);
            $('#bc_show_class').prop('checked', true);
            $('#bc_show_dob').prop('checked', true);
            $('#bc_show_current_fee').prop('checked', false);
            $('#bc_show_monthly_fee').prop('checked', false);
            $('#bc_show_issue_date').prop('checked', true);
            $('#bonafideOptionsModal').modal('show');
        });
        
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

$(document).on('click', '#modalChallansLink', function (e) {
    e.preventDefault();
    const studentId = $('#studentActionsModal').data('activeStudentId');
    const parentId = $('#studentActionsModal').data('activeParentId') || 0;
    if (!studentId) {
        return;
    }
    $('#studentActionsModal').modal('hide');
    $('#cg_student_id').val(studentId);
    $('#cg_parent_id').val(parentId);
    const $famOpt = $('#cg_scope option[value="family"]');
    if (!parentId) {
        $famOpt.prop('disabled', true);
        if ($('#cg_scope').val() === 'family') {
            $('#cg_scope').val('student');
        }
    } else {
        $famOpt.prop('disabled', false);
    }
    $('#cg_scope_family_hint').toggle($('#cg_scope').val() === 'family');
    $('#challanGenerateOptionsModal').modal('show');
});

$(document).on('click', '#modalCreateChallanLink', function (e) {
    e.preventDefault();
    const studentId = $('#studentActionsModal').data('activeStudentId');
    const parentId = $('#studentActionsModal').data('activeParentId') || 0;
    if (!studentId) {
        return;
    }
    $('#studentActionsModal').modal('hide');
    if (typeof window.openChalanEditForPay === 'function') {
        window.openChalanEditForPay(parentId, studentId);
    }
});

$('#cg_scope').on('change', function () {
    $('#cg_scope_family_hint').toggle($(this).val() === 'family');
});

$('#bc_recipient_mode').on('change', function () {
    const customMode = $(this).val() === 'custom';
    $('#bc_custom_recipient_wrap').toggle(customMode);
    if (!customMode) {
        $('#bc_recipient_name').val('');
    }
});

$('#bcOpenCertificateBtn').on('click', function () {
    const ADMIN_URL = window.ADMIN || '<?= rtrim(base_url('admin'), '/') ?>';
    const studentId = parseInt($('#bc_student_id').val(), 10) || 0;
    if (!studentId) {
        return;
    }

    const recipientMode = $('#bc_recipient_mode').val() === 'custom' ? 'custom' : 'twmc';
    const recipientName = ($('#bc_recipient_name').val() || '').trim();

    if (recipientMode === 'custom' && recipientName === '') {
        alert('Please enter custom recipient text.');
        $('#bc_recipient_name').focus();
        return;
    }

    const reasonText = ($('#bc_reason').val() || '').trim();
    if (!reasonText) {
        alert('Please select certificate reason.');
        $('#bc_reason').focus();
        return;
    }

    const params = new URLSearchParams();
    params.set('student_id', String(studentId));
    params.set('recipient_mode', recipientMode);
    params.set('recipient_name', recipientName);
    params.set('purpose', reasonText);
    params.set('show_reg_no', $('#bc_show_reg_no').is(':checked') ? '1' : '0');
    params.set('show_father', $('#bc_show_father').is(':checked') ? '1' : '0');
    params.set('show_class', $('#bc_show_class').is(':checked') ? '1' : '0');
    params.set('show_dob', $('#bc_show_dob').is(':checked') ? '1' : '0');
    params.set('show_current_fee', $('#bc_show_current_fee').is(':checked') ? '1' : '0');
    params.set('show_monthly_fee', $('#bc_show_monthly_fee').is(':checked') ? '1' : '0');
    params.set('show_issue_date', $('#bc_show_issue_date').is(':checked') ? '1' : '0');

    window.open(`${ADMIN_URL}/students_print/bonafide-certificate?${params.toString()}`, '_blank');
    $('#bonafideOptionsModal').modal('hide');
});

$('#cgOpenChallanBtn').on('click', function () {
    const ADMIN_URL = window.ADMIN || '<?= rtrim(base_url('admin'), '/') ?>';
    const studentId = $('#cg_student_id').val();
    const parentId = $('#cg_parent_id').val();
    const scope = $('#cg_scope').val();
    const showDiscount = $('#cg_show_discount').val() === 'yes' ? 'yes' : 'no';
    const showHistory = $('#cg_payment_history').is(':checked') ? '1' : '0';
    const fineAfter = $('#cg_fine_after').is(':checked') ? '1' : '0';
    const feeMonth = ($('#cg_fee_month').val() || '').trim();

    const params = new URLSearchParams();
    params.set('view_type', scope === 'family' ? 'family_three_copy' : 'student_three_copy');
    params.set('show_discount', showDiscount);
    params.set('show_payment_history', showHistory);
    params.set('fine_after_due_date', fineAfter);
    params.set('message_position', 'none');
    params.set('message_text', '');
    if (feeMonth) {
        params.set('fee_month', feeMonth);
    }
    if (scope === 'family' && parentId) {
        params.set('family_id', String(parentId));
    } else {
        params.set('search', String(studentId));
    }

    const url = `${ADMIN_URL}/fee-chalan/generate?${params.toString()}`;
    window.open(url, '_blank', 'noopener,noreferrer');
    $('#challanGenerateOptionsModal').modal('hide');
});

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
                    <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
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
                            <div class="card-header p-2" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#${sessionId}">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>${escapeHtml(history.session_name || 'N/A')}</strong>
                                    </div>
                                    <div class="col-md-3">
                                        Class: ${escapeHtml(history.class_section || 'N/A')}
                                    </div>
                                    <div class="col-md-3">
                                        Total: <span class="badge text-bg-info">PKR ${sessionTotal.toLocaleString()}</span>
                                    </div>
                                    <div class="col-md-3 text-end">
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
                            ${isMonthly ? '<span class="badge text-bg-info ms-1">Monthly</span>' : ''}
                            <input type="hidden" class="fee-type-id" value="${fee.fee_type_id}">
                            <input type="hidden" class="is-monthly" value="${isMonthly ? '1' : '0'}">
                            <input type="hidden" class="standard-amount" value="${standardAmount}">
                        </td>
                        <td class="standard-amount-cell">
                            <span class="badge text-bg-secondary">PKR ${standardAmount.toLocaleString()}</span>
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
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
    
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
            $btn.prop('disabled', false).html('<i class="fas fa-undo-alt me-1"></i> Process Readmission & Generate Invoice');
            
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
            $btn.prop('disabled', false).html('<i class="fas fa-undo-alt me-1"></i> Process Readmission & Generate Invoice');
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
<?= view('admin/chalanview/partials/chalan_edit_modal_shared', [
    'csrfMetaId' => 'csrf-meta-chalan-edit',
    'chalanEditAfterSave' => 'reload',
]) ?>
<?= $this->endSection() ?>