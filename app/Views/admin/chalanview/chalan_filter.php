<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />

<style>
    :root {
        --primary-color: #4361ee;
        --secondary-color: #3f37c9;
        --success-color: #4cc9f0;
        --info-color: #4895ef;
        --warning-color: #f72585;
        --light-bg: #f8f9fa;
        --border-radius: 12px;
        --box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    }

    .filter-card {
        background: #fff;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        padding: 25px;
        margin-bottom: 25px;
        border: none;
    }
    
    .filter-section {
        background: var(--light-bg);
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        border-left: 4px solid var(--primary-color);
    }
    
    .filter-section h4 {
        margin-top: 0;
        margin-bottom: 20px;
        color: var(--primary-color);
        font-size: 16px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .filter-section h4 i {
        font-size: 18px;
    }
    
    .select2-container--bootstrap {
        width: 100% !important;
    }
    
    .preview-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-left: none;
        padding: 15px 20px;
        margin-bottom: 20px;
        border-radius: 50px;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .preview-info i {
        margin-right: 8px;
    }
    
    .view-type-cards {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-top: 10px;
    }
    
    .view-type-card {
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .view-type-card:hover {
        border-color: var(--primary-color);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(67, 97, 238, 0.1);
    }
    
    .view-type-card.selected {
        border-color: var(--primary-color);
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .view-type-card.selected i {
        color: white;
    }
    
    .view-type-card i {
        font-size: 32px;
        color: var(--primary-color);
        margin-bottom: 10px;
    }
    
    .view-type-card.selected i {
        color: white;
    }
    
    .view-type-card .card-title {
        font-weight: 600;
        font-size: 14px;
        margin: 5px 0;
    }
    
    .view-type-card .card-subtitle {
        font-size: 11px;
        color: #6c757d;
    }
    
    .view-type-card.selected .card-subtitle {
        color: rgba(255,255,255,0.9);
    }
    
    .toggle-switch-container {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }
    
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }
    
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    
    input:checked + .toggle-slider {
        background-color: var(--primary-color);
    }
    
    input:checked + .toggle-slider:before {
        transform: translateX(26px);
    }
    
    .toggle-label {
        font-weight: 500;
        color: #495057;
    }
    
    .message-box {
        background: white;
        border-radius: 8px;
        padding: 20px;
        border: 2px solid #e9ecef;
    }
    
    .message-options {
        display: flex;
        gap: 20px;
        margin-bottom: 15px;
        padding: 10px;
        background: var(--light-bg);
        border-radius: 8px;
    }
    
    .message-option {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .message-option input[type="radio"] {
        accent-color: var(--primary-color);
        width: 18px;
        height: 18px;
    }
    
    .char-counter {
        font-size: 12px;
        color: #6c757d;
        margin-top: 5px;
        text-align: right;
    }
    
    .char-counter.warning {
        color: #f39c12;
    }
    
    .char-counter.danger {
        color: #e74c3c;
    }
    
    .badge-feature {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        margin-left: 10px;
    }
    
    .action-buttons {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        margin-top: 20px;
    }
    
    .btn-view {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 12px 30px;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-view:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .btn-reset {
        background: #e9ecef;
        border: none;
        color: #495057;
        padding: 12px 30px;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-reset:hover {
        background: #dee2e6;
    }
    
    @media (max-width: 768px) {
        .view-type-cards {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-file-invoice mr-2"></i>Fee Chalan Generator</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/fee-management') ?>">Fee Management</a></li>
                    <li class="breadcrumb-item active">Generate Chalan</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <form action="<?= base_url('admin/fee-chalan/generate') ?>" method="get" id="chalanForm" target="_blank">
            <div class="filter-card">
                <!-- View Type Selection - Card Based -->
                <div class="filter-section">
                    <h4><i class="fas fa-layer-group"></i> 1. Select View Type</h4>
                    
                    <!-- View Type Cards -->
                    <div class="view-type-cards">
                        <div class="view-type-card <?= ($selected_view ?? 'student_three_copy') == 'student_three_copy' ? 'selected' : '' ?>" 
                             data-value="student_three_copy">
                            <i class="fas fa-user-graduate"></i>
                            <div class="card-title">Student Wise</div>
                            <div class="card-subtitle">3 Copies per Student</div>
                        </div>
                        
                        <div class="view-type-card <?= ($selected_view ?? '') == 'student_single_page' ? 'selected' : '' ?>" 
                             data-value="student_single_page">
                            <i class="fas fa-users"></i>
                            <div class="card-title">Student Wise</div>
                            <div class="card-subtitle">Single Page (3 Students)</div>
                        </div>
                        
                        <div class="view-type-card <?= ($selected_view ?? '') == 'family_three_copy' ? 'selected' : '' ?>" 
                             data-value="family_three_copy">
                            <i class="fas fa-family"></i>
                            <div class="card-title">Family Wise</div>
                            <div class="card-subtitle">3 Copies per Student</div>
                        </div>
                        
                        <div class="view-type-card <?= ($selected_view ?? '') == 'family_single_page' ? 'selected' : '' ?>" 
                             data-value="family_single_page">
                            <i class="fas fa-people-arrows"></i>
                            <div class="card-title">Family Wise</div>
                            <div class="card-subtitle">All Students on One Page</div>
                        </div>
                    </div>
                    
                    <!-- Hidden select for form submission -->
                    <select name="view_type" id="view_type" class="d-none">
                        <option value="student_three_copy" <?= ($selected_view ?? 'student_three_copy') == 'student_three_copy' ? 'selected' : '' ?>>Student Wise - 3 Copies</option>
                        <option value="student_single_page" <?= ($selected_view ?? '') == 'student_single_page' ? 'selected' : '' ?>>Student Wise - Single Page</option>
                        <option value="family_three_copy" <?= ($selected_view ?? '') == 'family_three_copy' ? 'selected' : '' ?>>Family Wise - 3 Copies</option>
                        <option value="family_single_page" <?= ($selected_view ?? '') == 'family_single_page' ? 'selected' : '' ?>>Family Wise - Single Page</option>
                    </select>
                    
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="toggle-switch-container">
                                <span class="toggle-label">Discount Column:</span>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="show_discount" value="yes" 
                                           <?= ($show_discount ?? 'yes') == 'yes' ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label" id="discountLabel">Show</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fas fa-calendar-alt mr-1"></i>Fee Month</label>
                                <input type="month" class="form-control" name="fee_month" 
                                       value="<?= $fee_month ?? '' ?>" 
                                       placeholder="Select Month">
                                <small class="text-muted">Leave empty to show all unpaid months</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden fields to store selected IDs -->
                <input type="hidden" name="selected_student_id" id="selected_student_id" value="">
                <input type="hidden" name="selected_parent_id" id="selected_parent_id" value="">
                
                <!-- Search Section -->
                <div class="filter-section">
                    <h4><i class="fas fa-search"></i> 2. Search</h4>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label><strong><i class="fas fa-user mr-1"></i>Search by Name / Reg No / CNIC</strong></label>
                                <select class="form-control" id="search_select" name="search">
                                    <option value="">Type at least 3 characters to search...</option>
                                </select>
                                <input type="hidden" name="selected_item_id" id="selected_item_id" value="">
                                <small class="text-muted" id="searchHelp">
                                    <i class="fas fa-info-circle"></i> 
                                    Search for student name, father name, or registration number
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><strong><i class="fas fa-id-card mr-1"></i>Or Direct Family ID</strong></label>
                                <input type="number" class="form-control" name="family_id" 
                                       value="<?= $family_id ?? '' ?>" placeholder="Enter Family ID">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Class Filters (shown only for student-wise) -->
                <div class="filter-section" id="classFilters" style="<?= (strpos($selected_view ?? 'student_three_copy', 'student') !== false) ? 'display:block;' : 'display:none;' ?>">
                    <h4><i class="fas fa-school"></i> 3. Class Filter (Student Wise Only)</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Class</strong></label>
                                <select name="class_id" id="class_id" class="form-control select2">
                                    <option value="">All Classes</option>
                                    <?php foreach ($classes ?? [] as $class): ?>
                                        <option value="<?= $class['class_id'] ?>" <?= ($class_id ?? '') == $class['class_id'] ? 'selected' : '' ?>>
                                            <?= esc($class['class_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Section</strong></label>
                                <select name="section_id" id="section_id" class="form-control select2">
                                    <option value="">All Sections</option>
                                    <?php foreach ($sectionsclassinfo ?? [] as $section): ?>
                                        <option value="<?= $section['section_id'] ?>" <?= ($section_id ?? '') == $section['section_id'] ? 'selected' : '' ?>>
                                            <?= esc($section['sectionclassname']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Message Options - Replaces Footer Lines -->
                <div class="filter-section">
                    <h4><i class="fas fa-envelope"></i> 4. Message Options</h4>
                    
                    <div class="message-box">
                        <div class="message-options">
                            <div class="message-option">
                                <input type="radio" name="message_position" id="pos_header" value="header" 
                                       <?= (!isset($message_position) || $message_position == 'header') ? 'checked' : '' ?>>
                                <label for="pos_header"><i class="fas fa-arrow-up text-success"></i> Show in Header</label>
                            </div>
                            <div class="message-option">
                                <input type="radio" name="message_position" id="pos_footer" value="footer"
                                       <?= ($message_position ?? '') == 'footer' ? 'checked' : '' ?>>
                                <label for="pos_footer"><i class="fas fa-arrow-down text-info"></i> Show in Footer</label>
                            </div>
                            <div class="message-option">
                                <input type="radio" name="message_position" id="pos_none" value="none"
                                       <?= ($message_position ?? '') == 'none' ? 'checked' : '' ?>>
                                <label for="pos_none"><i class="fas fa-ban text-danger"></i> Don't Show</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><strong>Message Text</strong></label>
                            <textarea class="form-control" name="message_text" id="message_text" rows="3" 
                                      placeholder="Enter your message here..." maxlength="200"><?= $message_text ?? '' ?></textarea>
                            <div class="char-counter" id="charCounter">
                                <span id="charCount">0</span>/200 characters
                            </div>
                        </div>
                        
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle mr-2"></i>
                            Message will be displayed at the selected position on all chalans. Max 200 characters.
                        </div>
                    </div>
                </div>

                <!-- Additional Options -->
                <div class="filter-section">
                    <h4><i class="fas fa-cog"></i> 5. Additional Options</h4>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" 
                                       name="show_payment_history" id="show_payment_history" value="1"
                                       <?= ($show_payment_history ?? 0) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="show_payment_history">
                                    <strong>Payment History</strong>
                                </label>
                                <small class="d-block text-muted">Show last 12 months payments</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="custom-control custom-switch">
                                <input class="custom-control-input" type="checkbox" 
                                       name="fine_after_due_date" id="fine_after_due_date" value="1"
                                       <?= ($fine_after_due_date ?? 0) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="fine_after_due_date">
                                    <strong>Display Fine</strong>
                                </label>
                                <small class="d-block text-muted">Show late fee calculation</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button type="submit" class="btn btn-view" id="generateBtn">
                        <i class="fas fa-file-pdf mr-2"></i> View Chalans
                    </button>
                    <button type="reset" class="btn btn-reset">
                        <i class="fas fa-undo mr-2"></i> Reset
                    </button>
                </div>
            </div>
        </form>

        <!-- Preview Info -->
        <div class="preview-info" id="previewInfo">
            <i class="fas fa-info-circle"></i>
            <strong>Current Selection:</strong> 
            <span id="previewText">
                <?php 
                $selectedView = $selected_view ?? 'student_three_copy';
                $showDiscount = $show_discount ?? 'yes';
                $feeMonth = $fee_month ?? '';
                $messagePos = $message_position ?? 'header';
                
                $preview = '';
                if (strpos($selectedView, 'student') !== false) {
                    $preview .= 'Student Wise - ';
                    $preview .= strpos($selectedView, 'three_copy') !== false ? '3 Copies per Student' : 'Single Page (3 Students)';
                } else {
                    $preview .= 'Family Wise - ';
                    $preview .= strpos($selectedView, 'three_copy') !== false ? '3 Copies per Student' : 'All Family Students on One Page';
                }
                $preview .= ' | Discount: ' . ($showDiscount == 'yes' ? 'Shown' : 'Hidden');
                $preview .= ' | Month: ' . (!empty($feeMonth) ? $feeMonth : 'All Months');
                $preview .= ' | Message: ' . ($messagePos == 'none' ? 'Hidden' : ucfirst($messagePos));
                echo $preview;
                ?>
            </span>
        </div>
    </div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap',
        width: '100%'
    });
    
    // Initialize Select2 for search
    initSearch();
    
    // View Type Card Selection
    $('.view-type-card').click(function() {
        $('.view-type-card').removeClass('selected');
        $(this).addClass('selected');
        const value = $(this).data('value');
        $('#view_type').val(value).trigger('change');
    });
    
    // Handle view type change
    $('#view_type').change(function() {
        const viewType = $(this).val();
        const isStudentWise = viewType.includes('student');
        
        // Clear search selections when changing view type
        $('#search_select').val(null).trigger('change');
        $('#selected_student_id').val('');
        $('#selected_parent_id').val('');
        $('input[name="search"]').val('');
        $('input[name="family_id"]').val('');
        
        // Update class filters visibility
        if (isStudentWise) {
            $('#classFilters').slideDown();
            $('#searchHelp').text('Search for student name, father name, or registration number');
            initStudentSearch();
        } else {
            $('#classFilters').slideUp();
            $('#searchHelp').text('Search for father name, CNIC, or contact number');
            initFamilySearch();
        }
        
        updatePreviewText();
        
        // Update help text
        let helpText = '';
        if (viewType == 'student_three_copy') {
            helpText = 'Each student gets 3 separate copies (Bank, School, Student)';
        } else if (viewType == 'student_single_page') {
            helpText = '3 students per page, each with a single copy';
        } else if (viewType == 'family_three_copy') {
            helpText = 'Grouped by family, each student gets 3 copies';
        } else {
            helpText = 'All students of a family on one page with single copies';
        }
        $('#viewHelpText').text(helpText);
    });

    // Discount toggle
    $('input[name="show_discount"]').change(function() {
        $('#discountLabel').text($(this).is(':checked') ? 'Show' : 'Hide');
        updatePreviewText();
    });

    // Function to update preview text
    function updatePreviewText() {
        const viewType = $('#view_type').val();
        const isStudentWise = viewType.includes('student');
        const layout = viewType.includes('three_copy') ? '3 Copies' : 'Single Page';
        const grouping = isStudentWise ? 'Student Wise' : 'Family Wise';
        const discount = $('input[name="show_discount"]').is(':checked') ? 'Shown' : 'Hidden';
        const month = $('input[name="fee_month"]').val();
        const monthDisplay = month ? month : 'All Months';
        const messagePos = $('input[name="message_position"]:checked').val();
        const messageDisplay = messagePos == 'none' ? 'Hidden' : (messagePos == 'header' ? 'Header' : 'Footer');
        
        let preview = grouping + ' - ' + layout;
        preview += ' | Discount: ' + discount;
        preview += ' | Month: ' + monthDisplay;
        preview += ' | Message: ' + messageDisplay;
        $('#previewText').text(preview);
    }

    // Discount option change
    $('input[name="show_discount"]').change(function() {
        updatePreviewText();
    });

    // Fee month change
    $('input[name="fee_month"]').on('input change', function() {
        updatePreviewText();
    });

    // Message position change
    $('input[name="message_position"]').change(function() {
        updatePreviewText();
    });

    // Character counter for message text
    $('#message_text').on('input', function() {
        const count = $(this).val().length;
        $('#charCount').text(count);
        const counter = $('.char-counter');
        counter.removeClass('warning danger');
        if (count > 180) {
            counter.addClass('warning');
        }
        if (count > 195) {
            counter.addClass('danger');
        }
    }).trigger('input');

    // Class change - reload sections
    $('#class_id').change(function() {
        const classId = $(this).val();
        if (classId) {
            loadSections(classId);
        }
    });

    // Handle student/family selection
    $('#search_select').on('select2:select', function(e) {
        var data = e.params.data;
        var viewType = $('#view_type').val();
        var isStudentWise = viewType.includes('student');
        
        if (isStudentWise) {
            $('input[name="search"]').val(data.id);
            $('#selected_student_id').val(data.id);
            $('#selected_parent_id').val('');
            $('#family_id').val('');
        } else {
            var parentId = data.parent_id || data.id;
            $('input[name="search"]').val(parentId);
            $('#selected_parent_id').val(parentId);
            $('#selected_student_id').val('');
            $('#family_id').val(parentId);
        }
    });

    // Handle clearing the selection
    $('#search_select').on('select2:clear', function(e) {
        $('input[name="search"]').val('');
        $('#selected_student_id').val('');
        $('#selected_parent_id').val('');
        $('#family_id').val('');
    });

    // Initialize search based on current view
    function initSearch() {
        if ($('#view_type').val().includes('student')) {
            initStudentSearch();
        } else {
            initFamilySearch();
        }
    }

    // Initialize student search
    function initStudentSearch() {
        if ($('#search_select').hasClass('select2-hidden-accessible')) {
            $('#search_select').select2('destroy');
        }
        
        $('#search_select').select2({
            theme: 'bootstrap',
            placeholder: 'Search by student name, reg no...',
            allowClear: true,
            minimumInputLength: 3,
            ajax: {
                url: '<?= base_url('admin/fee-chalan/search-students') ?>',
                type: 'POST',
                dataType: 'json',
                delay: 300,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                data: function(params) {
                    return {
                        term: params.term,
                        class_id: $('#class_id').val(),
                        section_id: $('#section_id').val()
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                }
            },
            templateResult: formatStudentResult,
            templateSelection: formatStudentSelection,
            escapeMarkup: function(m) { return m; }
        });
    }

    // Initialize family search
    function initFamilySearch() {
        if ($('#search_select').hasClass('select2-hidden-accessible')) {
            $('#search_select').select2('destroy');
        }
        
        $('#search_select').select2({
            theme: 'bootstrap',
            placeholder: 'Search by father name, CNIC...',
            allowClear: true,
            minimumInputLength: 3,
            ajax: {
                url: '<?= base_url('admin/fee-chalan/search-families') ?>',
                type: 'POST',
                dataType: 'json',
                delay: 300,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                data: function(params) {
                    return {
                        term: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                }
            },
            templateResult: formatFamilyResult,
            templateSelection: formatFamilySelection,
            escapeMarkup: function(m) { return m; }
        });
    }

    // Format student dropdown items
    function formatStudentResult(item) {
        if (item.loading) return item.text;
        
        var parts = item.text.split('|');
        var mainText = parts[0] || item.text;
        var subText = parts[1] || '';
        
        return $('<div class="p-2"><strong>' + mainText + '</strong>' + 
                (subText ? '<br><small class="text-muted">' + subText + '</small>' : '') + '</div>');
    }

    function formatStudentSelection(item) {
        if (!item.id) return item.text;
        var parts = item.text.split('|');
        return parts[0] || item.text;
    }

    function formatFamilyResult(item) {
        if (item.loading) return item.text;
        
        var parts = item.text.split(' - ');
        var mainText = parts[0] || item.text;
        var subText = parts.slice(1).join(' - ') || '';
        
        return $('<div class="p-2"><strong>' + mainText + '</strong>' + 
                (subText ? '<br><small class="text-muted">' + subText + '</small>' : '') + '</div>');
    }

    function formatFamilySelection(item) {
        if (!item.id) return item.text;
        var parts = item.text.split(' - ');
        return parts[0] || item.text;
    }

    // Load sections for selected class
    function loadSections(classId) {
        $.ajax({
            url: '<?= base_url('admin/fee-chalan/get-sections-by-class') ?>',
            data: {class_id: classId},
            success: function(data) {
                const $section = $('#section_id');
                $section.html('<option value="">All Sections</option>');
                data.forEach(function(s) {
                    $section.append('<option value="' + s.section_id + '">' + s.section_name + '</option>');
                });
                $section.trigger('change');
            }
        });
    }
    
    // Form submission
    $('#chalanForm').submit(function(e) {
        var viewType = $('#view_type').val();
        var isStudentWise = viewType.includes('student');
        
        if (isStudentWise) {
            var studentId = $('#selected_student_id').val();
            if (studentId) {
                $('input[name="search"]').val(studentId);
            }
        } else {
            var parentId = $('#selected_parent_id').val();
            if (parentId) {
                $('input[name="family_id"]').val(parentId);
                $('input[name="search"]').val('');
            }
        }
        
        $('#generateBtn').html('<i class="fas fa-spinner fa-spin mr-2"></i> Generating...').prop('disabled', true);
        
        setTimeout(function() {
            $('#generateBtn').html('<i class="fas fa-file-pdf mr-2"></i> View Chalans').prop('disabled', false);
        }, 5000);
    });
});
</script>

<?= $this->endSection() ?>