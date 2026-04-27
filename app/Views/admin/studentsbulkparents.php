<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
    /* Responsive Design */
    .parent-card {
        transition: all 0.3s ease;
        border-left: 4px solid #007bff;
        margin-bottom: 20px;
    }
    .parent-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .card-header-collapsible {
        cursor: pointer;
        background-color: #f8f9fa;
        transition: background-color 0.2s;
    }
    .card-header-collapsible:hover {
        background-color: #e9ecef;
    }
    .collapse-icon {
        transition: transform 0.3s;
    }
    /* Accordion style - only one open */
    .parent-card .collapse-body {
        display: none;
    }
    .parent-card.active .collapse-body {
        display: block;
    }
    .parent-card.active .collapse-icon {
        transform: rotate(90deg);
    }
    .sibling-badge {
        background-color: #e9ecef;
        padding: 5px 10px;
        border-radius: 20px;
        margin: 3px;
        display: inline-block;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .sibling-badge:hover {
        background-color: #007bff;
        color: white;
    }
    .sibling-badge.active {
        background-color: #28a745;
        color: white;
    }
    @media (max-width: 768px) {
        .parent-card .form-row > [class*="col-"] {
            margin-bottom: 10px;
        }
        .btn-responsive {
            width: 100%;
            margin-top: 10px;
        }
    }
    @media (max-width: 576px) {
        .parent-card {
            font-size: 14px;
        }
        .parent-card .card-body {
            padding: 15px;
        }
    }
    .loader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
    }
    .loader-overlay.active {
        display: flex;
    }
    .search-results-dropdown {
        position: absolute;
        z-index: 1000;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        max-height: 300px;
        overflow-y: auto;
        width: 100%;
        display: none;
    }
    .search-result-item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
    }
    .search-result-item:hover {
        background-color: #f0f0f0;
    }
    .info-badge {
        background: #17a2b8;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        margin-left: 8px;
    }
    .whatsapp-copy-btn {
        cursor: pointer;
        transition: all 0.2s;
    }
    .whatsapp-copy-btn:hover {
        opacity: 0.7;
    }
</style>

<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1>Update Parent Information </h1>
            </div>
           
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-header pb-0">
                <ul class="nav nav-tabs card-header-tabs" style="overflow-x: auto; flex-wrap: nowrap;">
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/addbulkstudents/add') ?>">Student Names</a></li>          
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/studentsbulk') ?>">Class Change</a></li>                    
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_bulk_info') ?>">Other Info</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_bulk_fee_info') ?>">Fee Info</a></li>
                    <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/studentsbulkparents') ?>">Parent Info</a></li>
                     <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_bulk_info_date_of_birth') ?>">Date of Birth & BMI</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_bulk_make_current') ?>">Make Current</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/studentsbulkcsv/addbulk') ?>">Excel Import</a></li>
                </ul>
            </div>

            <div class="card-body">
                <!-- Search & Filter Bar -->
                <div class="row mb-4">
                    <div class="col-md-3 col-sm-12 mb-2">
                        <div class="form-group mb-0">
                            <label>Class Section</label>
                            <select id="filter_cls_sec_id" class="form-control">
                                <option value="0">All Classes</option>
                                <?php foreach ($sectionsclassinfo as $sec): 
                                    $val = (int)($sec['cls_sec_id'] ?? 0);
                                    $text = $sec['sectionclassname'] ?? '';
                                ?>
                                    <option value="<?= esc($val) ?>"><?= esc($text) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-12 mb-2">
                        <div class="form-group mb-0">
                            <label>Search Student</label>
                            <div class="input-group">
                                <input type="text" id="student_search_input" class="form-control" placeholder="Type name to search...">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="searchResults" class="search-results-dropdown"></div>
                        </div>
                    </div>
                    
                    <div class="col-md-2 col-sm-12 mb-2">
                        <div class="form-group mb-0">
                            <label>Quick Actions</label>
                            <div>
                                <button type="button" id="expandAll" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-expand-alt"></i> Expand All
                                </button>
                                <button type="button" id="collapseAll" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-compress-alt"></i> Collapse All
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2 col-sm-12 mb-2">
                        <div class="form-group mb-0">
                            <label>&nbsp;</label>
                            <div>
                                <button type="button" id="applyFilters" class="btn btn-primary btn-block">
                                    <i class="fas fa-filter"></i> Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2 col-sm-12 mb-2">
                        <div class="form-group mb-0">
                            <label>&nbsp;</label>
                            <div>
                                <button type="button" id="bulkSave" class="btn btn-success btn-block">
                                    <i class="fas fa-save"></i> Save All
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Summary -->
                <div id="statsSummary" class="row mb-3" style="display: none;">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <strong><span id="studentCount">0</span> students</strong> loaded 
                            | <span id="linkedCount">0</span> linked to existing parents
                            | <span id="newCount">0</span> new parents to create
                        </div>
                    </div>
                </div>

                <!-- Students List Container - Cards Layout -->
                <div id="studentsListContainer">
                    <div id="loader-1" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Loading students...</p>
                    </div>
                    <div id="studentsList"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Parent Search Modal for Relinking -->
<div class="modal fade" id="parentSearchModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt"></i> Relink Student
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" id="parentSearchTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="search-student-tab" data-toggle="tab" href="#searchStudent" role="tab">
                            <i class="fas fa-user-graduate"></i> Search by Student
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="search-parent-tab" data-toggle="tab" href="#searchParent" role="tab">
                            <i class="fas fa-user-friends"></i> Search by Parent Name
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="search-cnic-tab" data-toggle="tab" href="#searchCNIC" role="tab">
                            <i class="fas fa-id-card"></i> Search by CNIC
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="searchStudent" role="tabpanel">
                        <div class="form-group">
                            <label>Search Student</label>
                            <select id="studentSearchSelect" class="form-control select2" style="width:100%">
                                <option value="">Type student name...</option>
                            </select>
                            <small class="text-muted">Search by student name to find their parent</small>
                        </div>
                        <div id="studentSearchResult" class="mt-3"></div>
                    </div>

                    <div class="tab-pane fade" id="searchParent" role="tabpanel">
                        <div class="form-group">
                            <label>Search Parent Name</label>
                            <select id="parentNameSearch" class="form-control select2" style="width:100%">
                                <option value="">Type parent name...</option>
                            </select>
                            <small class="text-muted">Search by father or mother name</small>
                        </div>
                        <div id="parentNameSearchResult" class="mt-3"></div>
                    </div>

                    <div class="tab-pane fade" id="searchCNIC" role="tabpanel">
                        <div class="form-group">
                            <label>Father CNIC</label>
                            <input type="text" id="cnicSearch" class="form-control" placeholder="XXXXX-XXXXXXX-X">
                            <small class="text-muted">Enter 13-digit CNIC (format: XXXXX-XXXXXXX-X)</small>
                        </div>
                        <button type="button" id="searchByCnicBtn" class="btn btn-primary mt-2">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <div id="cnicSearchResult" class="mt-3"></div>
                    </div>
                </div>
                
                <div id="searchLoader" style="display:none;" class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p>Searching...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Global Loader Overlay -->
<div id="globalLoader" class="loader-overlay">
    <div class="text-center bg-white p-4 rounded">
        <div class="spinner-border text-primary mb-2" role="status"></div>
        <div>Saving changes...</div>
    </div>
</div>

<script>
// Store current students data
let currentStudents = [];
let searchTimeout = null;
let currentRelinkStudentId = null;

// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// Copy value to WhatsApp field
// Copy value to WhatsApp field - FIXED VERSION
function copyToWhatsapp(studentId, sourceType) {
    let sourceValue = '';
    let sourceFieldName = '';
    
    if (sourceType === 'father') {
        sourceValue = $(`#father_contact_${studentId}`).val();
        sourceFieldName = 'Father Contact';
    } else if (sourceType === 'mother') {
        sourceValue = $(`#mother_contact_${studentId}`).val();
        sourceFieldName = 'Mother Contact';
    }
    
    if (sourceValue && sourceValue.trim() !== '') {
        $(`#whatsapp_${studentId}`).val(sourceValue);
        toastr.success(`${sourceFieldName} copied to WhatsApp field`);
        // Trigger change event so the value is saved
        $(`#whatsapp_${studentId}`).trigger('change');
    } else {
        toastr.warning(`${sourceFieldName} field is empty. Cannot copy.`);
    }
}

// Load students function
function loadStudents() {
    var clsSecId = $('#filter_cls_sec_id').val() || 0;
    var studentId = $('#student_search_input').data('selected-id') || 0;
    
    console.log('Loading students with class:', clsSecId, 'student:', studentId);
    
    $('#loader-1').show();
    $('#studentsList').html('');
    
    $.ajax({
        url: '<?= base_url("admin/studentsbulkparents/data") ?>',
        type: 'POST',
        data: {
            cls_sec_id: clsSecId,
            student_id: studentId,
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        success: function(response) {
            $('#studentsList').html(response);
            attachCardEventHandlers();
            updateStats();
            $('#loader-1').hide();
        },
        error: function(xhr) {
            console.error('AJAX Error:', xhr);
            $('#studentsList').html('<div class="alert alert-danger">Failed to load students.</div>');
            $('#loader-1').hide();
        }
    });
}

// Attach event handlers to dynamically loaded cards
// Attach event handlers to dynamically loaded cards
function attachCardEventHandlers() {
    // Accordion style - only one card open at a time
    $('.card-header-collapsible').off('click').on('click', function(e) {
        if ($(e.target).closest('button').length) {
            return;
        }
        
        var $currentCard = $(this).closest('.parent-card');
        var isCurrentlyActive = $currentCard.hasClass('active');
        
        $('.parent-card').removeClass('active');
        
        if (!isCurrentlyActive) {
            $currentCard.addClass('active');
        }
    });
    
    // CNIC lookup on blur
    $('.father-cnic').off('blur').on('blur', function() {
        lookupParentByCNIC($(this));
    });
    
    // Show siblings button
    $('.show-siblings').off('click').on('click', function(e) {
        e.stopPropagation();
        showSiblingDialog($(this).data('student-id'), $(this).data('parent-id'));
    });
    
    // Save individual student
    $('.save-student').off('click').on('click', function(e) {
        e.stopPropagation();
        saveStudent($(this).data('student-id'));
    });
    
    // RELINK PARENT BUTTON
    $('.relink-parent').off('click').on('click', function(e) {
        e.stopPropagation();
        var studentId = $(this).data('student-id');
        var studentName = $(this).data('student-name') || 'Student';
        openParentSearchModal(studentId, studentName);
    });
    
    // Copy father contact to WhatsApp - FIXED
    $('.copy-father-to-whatsapp').off('click').on('click', function(e) {
        e.stopPropagation();
        var studentId = $(this).data('student-id');
        copyToWhatsapp(studentId, 'father');
    });
    
    // Copy mother contact to WhatsApp - FIXED
    $('.copy-mother-to-whatsapp').off('click').on('click', function(e) {
        e.stopPropagation();
        var studentId = $(this).data('student-id');
        copyToWhatsapp(studentId, 'mother');
    });
}
// Update statistics
function updateStats() {
    let total = $('.parent-card').length;
    let linked = $('.parent-card[data-linked="true"]').length;
    let newParents = $('.parent-card[data-new="true"]').length;
    
    if (total > 0) {
        $('#statsSummary').show();
        $('#studentCount').text(total);
        $('#linkedCount').text(linked);
        $('#newCount').text(newParents);
    } else {
        $('#statsSummary').hide();
    }
}

// Lookup parent by CNIC
function lookupParentByCNIC($input) {
    let cnic = $input.val().trim();
    if (!cnic || cnic.length < 10) return;
    
    let $card = $input.closest('.parent-card');
    let $hint = $card.find('.cnic-hint');
    
    $hint.html('<i class="fas fa-spinner fa-spin"></i> Checking...');
    
    $.ajax({
        url: '<?= base_url("admin/students_bulk_info/lookup_parent_by_cnic") ?>',
        type: 'POST',
        data: {
            cnic: cnic,
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        success: function(response) {
            if (response.found) {
                $card.find('.parent-id').val(response.parent_id);
                $card.find('.father-name').val(response.f_name);
                $hint.html('<i class="fas fa-check-circle text-success"></i> Linked to: ' + response.f_name);
                $card.attr('data-linked', 'true').attr('data-new', 'false');
                $card.find('.relink-status').html('<span class="badge badge-success">Linked</span>');
            } else {
                $card.find('.parent-id').val('');
                $hint.html('<i class="fas fa-info-circle text-warning"></i> New parent will be created on save');
                $card.attr('data-linked', 'false').attr('data-new', 'true');
                $card.find('.relink-status').html('<span class="badge badge-warning">New Parent</span>');
            }
            updateStats();
        },
        error: function() {
            $hint.html('<i class="fas fa-exclamation-circle text-danger"></i> Lookup failed');
        }
    });
}

// Save individual student
function saveStudent(studentId) {
    let $card = $(`.parent-card[data-student-id="${studentId}"]`);
    let parentData = {
        f_name: $card.find('.father-name').val(),
        father_cnic: $card.find('.father-cnic').val(),
        father_contact: $card.find('.father-contact').val(),
        mother_name: $card.find('.mother-name').val(),
        mother_contact: $card.find('.mother-contact').val(),
        father_email: $card.find('.father-email').val(),
        father_occupation: $card.find('.father-occupation').val(),
        address: $card.find('.address').val(),
        emergency_contact: $card.find('.emergency-contact').val(),
        whatsapp: $card.find('.whatsapp').val()
    };
    
    let $btn = $card.find('.save-student');
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    
    $.ajax({
        url: '<?= base_url("admin/studentsbulkparents/save") ?>',
        type: 'POST',
        data: JSON.stringify({
            student_id: studentId,
            parent_data: parentData
        }),
        contentType: 'application/json',
        success: function(response) {
            if (response.success) {
                toastr.success(response.msg || 'Saved successfully');
                $btn.html('<i class="fas fa-check"></i> Saved');
                setTimeout(() => {
                    $btn.html('<i class="fas fa-save"></i> Save');
                    $btn.prop('disabled', false);
                }, 2000);
            } else {
                toastr.error(response.msg || 'Save failed');
                $btn.html('<i class="fas fa-save"></i> Save');
                $btn.prop('disabled', false);
            }
        },
        error: function() {
            toastr.error('Server error');
            $btn.html('<i class="fas fa-save"></i> Save');
            $btn.prop('disabled', false);
        }
    });
}

// Show sibling dialog
function showSiblingDialog(studentId, parentId) {
    if (!parentId) {
        toastr.warning('Please save parent info first to see siblings');
        return;
    }
    
    $.ajax({
        url: '<?= base_url("admin/studentsbulkparents/get-siblings") ?>',
        type: 'POST',
        data: {
            parent_id: parentId,
            current_student: studentId,
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        success: function(response) {
            if (response.siblings && response.siblings.length > 0) {
                let html = '<div class="list-group">';
                response.siblings.forEach(sib => {
                    html += `<a href="#" class="list-group-item list-group-item-action sibling-select" 
                                data-student-id="${sib.student_id}">
                                <strong>${escapeHtml(sib.first_name)} ${escapeHtml(sib.last_name)}</strong>
                                <small class="text-muted"> - ${escapeHtml(sib.class_name || 'No class')}</small>
                            </a>`;
                });
                html += '</div>';
                
                bootbox.dialog({
                    title: '<i class="fas fa-users"></i> Siblings of ' + escapeHtml(response.parent_name),
                    message: html,
                    size: 'large',
                    buttons: {
                        cancel: { label: 'Close', className: 'btn-secondary' }
                    }
                });
                
                $('.sibling-select').on('click', function(e) {
                    e.preventDefault();
                    let sibId = $(this).data('student-id');
                    let sibName = $(this).find('strong').text();
                    $('#student_search_input').val(sibName);
                    $('#student_search_input').data('selected-id', sibId);
                    bootbox.hideAll();
                    loadStudents();
                });
            } else {
                toastr.info('No siblings found');
            }
        }
    });
}

// Open parent search modal
function openParentSearchModal(studentId, studentName) {
    currentRelinkStudentId = studentId;
    $('#parentSearchModal').modal('show');
    $('#parentSearchModal .modal-title').html('<i class="fas fa-exchange-alt"></i> Relink: ' + escapeHtml(studentName));
    
    $('#studentSearchResult, #parentNameSearchResult, #cnicSearchResult').html('');
    $('#studentSearchSelect').val(null).trigger('change');
    $('#parentNameSearch').val(null).trigger('change');
    $('#cnicSearch').val('');
}

// Perform the actual relink
function performRelink(studentId, parentId) {
    $('#globalLoader').addClass('active');
    
    $.ajax({
        url: '<?= base_url("admin/studentsbulkparents/relink") ?>',
        type: 'POST',
        data: JSON.stringify({
            student_id: studentId,
            parent_id: parentId
        }),
        contentType: 'application/json',
        success: function(response) {
            $('#globalLoader').removeClass('active');
            if (response.success) {
                toastr.success('Student relinked successfully');
                $('#parentSearchModal').modal('hide');
                loadStudents();
            } else {
                toastr.error(response.msg || 'Relink failed');
            }
        },
        error: function() {
            $('#globalLoader').removeClass('active');
            toastr.error('Server error occurred');
        }
    });
}

$(document).ready(function() {
    
    $('#filter_cls_sec_id').on('change', function() {
        $('#student_search_input').val('');
        $('#student_search_input').removeData('selected-id');
        loadStudents();
    });
    
    $('#applyFilters').on('click', function() {
        loadStudents();
    });
    
    $('#student_search_input').on('change', function() {
        loadStudents();
    });
    
    $('#clearSearch').on('click', function() {
        $('#student_search_input').val('');
        $('#student_search_input').removeData('selected-id');
        $('#searchResults').hide();
        loadStudents();
    });
    
    // Student search with dropdown
    $('#student_search_input').on('input', function() {
        let query = $(this).val().trim();
        if (query.length < 2) {
            $('#searchResults').hide();
            return;
        }
        
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            $.ajax({
                url: '<?= base_url("admin/students_bulk_info/search-by-name") ?>',
                type: 'GET',
                data: { q: query, limit: 10 },
                success: function(data) {
                    if (data.results && data.results.length > 0) {
                        let html = '';
                        data.results.forEach(student => {
                            html += `<div class="search-result-item" data-student-id="${student.id}" data-parent-id="${student.parent_id || 0}">
                                        <strong>${escapeHtml(student.text)}</strong>
                                        <span class="info-badge">ID: ${student.id}</span>
                                    </div>`;
                        });
                        $('#searchResults').html(html).show();
                        
                        $('.search-result-item').off('click').on('click', function() {
                            let stuId = $(this).data('student-id');
                            let stuName = $(this).find('strong').text();
                            $('#student_search_input').val(stuName);
                            $('#student_search_input').data('selected-id', stuId);
                            $('#searchResults').hide();
                            loadStudents();
                        });
                    } else {
                        $('#searchResults').html('<div class="search-result-item">No results found</div>').show();
                    }
                }
            });
        }, 300);
    });
    
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#student_search_input, #searchResults').length) {
            $('#searchResults').hide();
        }
    });
    
    $('#expandAll').on('click', function() {
        $('.parent-card').addClass('active');
    });
    
    $('#collapseAll').on('click', function() {
        $('.parent-card').removeClass('active');
    });
    
    // Bulk save
    $('#bulkSave').on('click', function() {
        let allData = [];
        
        $('.parent-card').each(function() {
            let $card = $(this);
            let studentId = $card.data('student-id');
            
            let parentData = {
                student_id: studentId,
                f_name: $card.find('.father-name').val(),
                father_cnic: $card.find('.father-cnic').val(),
                father_contact: $card.find('.father-contact').val(),
                mother_name: $card.find('.mother-name').val(),
                mother_contact: $card.find('.mother-contact').val(),
                father_email: $card.find('.father-email').val(),
                father_occupation: $card.find('.father-occupation').val(),
                address: $card.find('.address').val(),
                emergency_contact: $card.find('.emergency-contact').val(),
                whatsapp: $card.find('.whatsapp').val()
            };
            
            allData.push(parentData);
        });
        
        if (allData.length === 0) {
            toastr.warning('No data to save');
            return;
        }
        
        $('#globalLoader').addClass('active');
        
        $.ajax({
            url: '<?= base_url("admin/studentsbulkparents/save") ?>',
            type: 'POST',
            data: JSON.stringify({ bulk_data: allData }),
            contentType: 'application/json',
            success: function(response) {
                $('#globalLoader').removeClass('active');
                if (response.success) {
                    toastr.success(response.msg || 'All data saved successfully');
                    loadStudents();
                } else {
                    toastr.error(response.msg || 'Save failed for some records');
                }
            },
            error: function() {
                $('#globalLoader').removeClass('active');
                toastr.error('Server error occurred');
            }
        });
    });
    
    // Initialize Select2 for modals
    $('#studentSearchSelect').select2({
        placeholder: 'Search by student name...',
        minimumInputLength: 2,
        dropdownParent: $('#parentSearchModal'),
        ajax: {
            url: '<?= base_url("admin/students_bulk_info/search-by-name") ?>',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    q: params.term,
                    limit: 20
                };
            },
            processResults: function(data) {
                return {
                    results: data.results || []
                };
            }
        }
    });
    
    $('#parentNameSearch').select2({
        placeholder: 'Search by father or mother name...',
        minimumInputLength: 2,
        dropdownParent: $('#parentSearchModal'),
        ajax: {
            url: '<?= base_url("admin/studentsbulkparents/search-parents-by-name") ?>',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    q: params.term,
                    limit: 20
                };
            },
            processResults: function(data) {
                return {
                    results: data.results || []
                };
            }
        }
    });
    
    // Modal event handlers
    $('#studentSearchSelect').on('select2:select', function(e) {
        var studentId = e.params.data.id;
        $('#searchLoader').show();
        
        $.ajax({
            url: '<?= base_url("admin/studentsbulkparents/get-student-parent") ?>',
            type: 'POST',
            data: {
                student_id: studentId,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            success: function(response) {
                $('#searchLoader').hide();
                if (response.success && response.parent) {
                    var html = `
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <strong><i class="fas fa-check-circle"></i> Parent Found</strong>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Father Name:</strong> ${escapeHtml(response.parent.f_name || 'N/A')}</p>
                                        <p><strong>Father CNIC:</strong> ${escapeHtml(response.parent.father_cnic || 'N/A')}</p>
                                        <p><strong>Father Contact:</strong> ${escapeHtml(response.parent.father_contact || 'N/A')}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Mother Name:</strong> ${escapeHtml(response.parent.m_name || 'N/A')}</p>
                                        <p><strong>Mother Contact:</strong> ${escapeHtml(response.parent.mother_contact || 'N/A')}</p>
                                        <p><strong>Address:</strong> ${escapeHtml(response.parent.address_line1 || 'N/A')}</p>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-success btn-block relink-to-parent" 
                                        data-parent-id="${response.parent.parent_id}"
                                        data-parent-name="${escapeHtml(response.parent.f_name)}">
                                    <i class="fas fa-link"></i> Relink to this Parent
                                </button>
                            </div>
                        </div>
                    `;
                    $('#studentSearchResult').html(html);
                } else {
                    $('#studentSearchResult').html('<div class="alert alert-warning">No parent found for this student.</div>');
                }
            },
            error: function() {
                $('#searchLoader').hide();
                $('#studentSearchResult').html('<div class="alert alert-danger">Error searching student</div>');
            }
        });
    });
    
    $('#parentNameSearch').on('select2:select', function(e) {
        var parentId = e.params.data.id;
        $('#searchLoader').show();
        
        $.ajax({
            url: '<?= base_url("admin/studentsbulkparents/get-parent-details") ?>',
            type: 'POST',
            data: {
                parent_id: parentId,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            success: function(response) {
                $('#searchLoader').hide();
                if (response.success && response.parent) {
                    var html = `
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <strong><i class="fas fa-check-circle"></i> Parent Details</strong>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Father Name:</strong> ${escapeHtml(response.parent.f_name || 'N/A')}</p>
                                        <p><strong>Father CNIC:</strong> ${escapeHtml(response.parent.father_cnic || 'N/A')}</p>
                                        <p><strong>Father Contact:</strong> ${escapeHtml(response.parent.father_contact || 'N/A')}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Mother Name:</strong> ${escapeHtml(response.parent.m_name || 'N/A')}</p>
                                        <p><strong>Mother Contact:</strong> ${escapeHtml(response.parent.mother_contact || 'N/A')}</p>
                                        <p><strong>Address:</strong> ${escapeHtml(response.parent.address_line1 || 'N/A')}</p>
                                    </div>
                                </div>
                                <hr>
                                <h6>Siblings (${response.siblings ? response.siblings.length : 0})</h6>
                                <div class="list-group mb-3">
                                    ${response.siblings ? response.siblings.map(s => 
                                        `<div class="list-group-item">
                                            <strong>${escapeHtml(s.first_name)} ${escapeHtml(s.last_name)}</strong>
                                            <small class="text-muted"> - ${escapeHtml(s.class_name || 'No class')}</small>
                                        </div>`
                                    ).join('') : '<div class="text-muted">No siblings found</div>'}
                                </div>
                                <button type="button" class="btn btn-success btn-block relink-to-parent" 
                                        data-parent-id="${response.parent.parent_id}"
                                        data-parent-name="${escapeHtml(response.parent.f_name)}">
                                    <i class="fas fa-link"></i> Relink to this Parent
                                </button>
                            </div>
                        </div>
                    `;
                    $('#parentNameSearchResult').html(html);
                } else {
                    $('#parentNameSearchResult').html('<div class="alert alert-warning">Parent not found</div>');
                }
            },
            error: function() {
                $('#searchLoader').hide();
                $('#parentNameSearchResult').html('<div class="alert alert-danger">Error loading parent details</div>');
            }
        });
    });
    
    $('#searchByCnicBtn').on('click', function() {
        var cnic = $('#cnicSearch').val().trim();
        if (!cnic) {
            toastr.warning('Please enter a CNIC');
            return;
        }
        
        $('#searchLoader').show();
        
        $.ajax({
            url: '<?= base_url("admin/students_bulk_info/lookup_parent_by_cnic") ?>',
            type: 'POST',
            data: {
                cnic: cnic,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            success: function(response) {
                $('#searchLoader').hide();
                if (response.found) {
                    var html = `
                        <div class="card border-success mt-3">
                            <div class="card-header bg-success text-white">
                                <strong><i class="fas fa-check-circle"></i> Parent Found</strong>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Father Name:</strong> ${escapeHtml(response.f_name || 'N/A')}</p>
                                        <p><strong>Father CNIC:</strong> ${escapeHtml(response.father_cnic || 'N/A')}</p>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-success btn-block relink-to-parent" 
                                        data-parent-id="${response.parent_id}"
                                        data-parent-name="${escapeHtml(response.f_name)}">
                                    <i class="fas fa-link"></i> Relink to this Parent
                                </button>
                            </div>
                        </div>
                    `;
                    $('#cnicSearchResult').html(html);
                } else {
                    $('#cnicSearchResult').html('<div class="alert alert-warning mt-3">No parent found with this CNIC</div>');
                }
            },
            error: function() {
                $('#searchLoader').hide();
                $('#cnicSearchResult').html('<div class="alert alert-danger">Error searching CNIC</div>');
            }
        });
    });
    
    $(document).on('click', '.relink-to-parent', function() {
        var parentId = $(this).data('parent-id');
        var parentName = $(this).data('parent-name');
        
        bootbox.confirm({
            title: 'Confirm Parent Relink',
            message: `Are you sure you want to relink this student to:<br><br>
                      <strong>Parent:</strong> ${escapeHtml(parentName)}<br>
                      <strong>Parent ID:</strong> ${parentId}<br><br>
                      This will update the student's parent information.`,
            buttons: {
                confirm: { label: 'Yes, Relink', className: 'btn-success' },
                cancel: { label: 'Cancel', className: 'btn-secondary' }
            },
            callback: function(result) {
                if (result) {
                    performRelink(currentRelinkStudentId, parentId);
                }
            }
        });
    });
    
    // Initial load
    if ($('#filter_cls_sec_id').val() && $('#filter_cls_sec_id').val() != '0') {
        loadStudents();
    } else {
        $('#studentsList').html('<div class="alert alert-info text-center py-5">Please select a class to view students.</div>');
        $('#loader-1').hide();
    }
});
</script>

<?= $this->endSection() ?>