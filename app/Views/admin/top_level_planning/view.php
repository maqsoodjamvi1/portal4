<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'View Top Level Planning',
    'icon' => 'fas fa-eye',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Top Level Planning', 'url' => base_url('admin/top_level_planning')],
        ['label' => 'View', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-eye me-2"></i>
                        Top Level Planning
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" id="collapseAllBtn" title="Collapse all cards">
                            <i class="fas fa-compress-alt"></i> Collapse All
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Choose how you want to view planning:
                        <ul class="mt-2 mb-0">
                            <li><strong>Term Wise</strong> - Shows selected terms with all classes and their subjects</li>
                            <li><strong>Class Wise</strong> - Shows classes (1 per row) with subjects table</li>
                            <li><strong>Subject Wise</strong> - Shows subjects (1 per row) with classes table</li>
                        </ul>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="view_type">View Type <span class="text-danger">*</span></label>
                                <select class="form-control" name="view_type" id="view_type" required>
                                    <option value="">Select View Type</option>
                                    <option value="term_wise">Term Wise</option>
                                    <option value="class_wise">Class Wise</option>
                                    <option value="subject_wise" selected>Subject Wise</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="term_ids">Select Terms <span class="text-danger">*</span></label>
                                <select class="form-control select2-multiple" name="term_ids[]" id="term_ids" multiple="multiple">
                                    <?php foreach ($terms as $term): ?>
                                        <option value="<?= $term->term_session_id ?>">
                                            <?= esc($term->term_name) ?> (<?= date('d M Y', strtotime($term->start_date)) ?> - <?= date('d M Y', strtotime($term->end_date)) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">You can select one or more terms</small>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="btn-group w-100">
                                    <button type="button" id="view_btn" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i> View
                                    </button>
                                    <button type="button" id="print_btn" class="btn btn-success" style="display: none;">
                                        <i class="fas fa-print me-1"></i> Print
                                    </button>
                                    <button type="button" id="clear_btn" class="btn btn-secondary">
                                        <i class="fas fa-undo-alt me-1"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="loader" class="text-center" style="display: none;">
                        <i class="fas fa-2x fa-spinner fa-spin"></i> Loading...
                    </div>
                    
                    <div id="planning_view_container" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// ============================================
// GLOBAL FUNCTIONS (defined outside document.ready)
// ============================================

// Initialize accordion for Subject Wise View
function initSubjectWiseAccordion() {
    // Initially all bodies are closed
    $('.subject-main-card-body').removeClass('open').css('max-height', '0');
    $('.expand-icon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
    
    // Subject card click handler
    $('.subject-main-card-header').off('click').on('click', function(e) {
        e.stopPropagation();
        var targetBodyId = $(this).data('target');
        var currentBody = $('#' + targetBodyId);
        var isOpen = currentBody.hasClass('open');
        
        if (isOpen) {
            currentBody.removeClass('open').css('max-height', '0');
            $(this).find('.expand-icon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        } else {
            currentBody.addClass('open');
            currentBody.css('max-height', currentBody[0].scrollHeight + 'px');
            $(this).find('.expand-icon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }
    });
}

// Initialize accordion for Class Wise View
function initClassWiseAccordion() {
    // Initially all bodies are closed
    $('.class-main-card-body').removeClass('open').css('max-height', '0');
    $('.expand-icon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
    
    // Class card click handler
    $('.class-main-card-header').off('click').on('click', function(e) {
        e.stopPropagation();
        var targetBodyId = $(this).data('target');
        var currentBody = $('#' + targetBodyId);
        var isOpen = currentBody.hasClass('open');
        
        if (isOpen) {
            currentBody.removeClass('open').css('max-height', '0');
            $(this).find('.expand-icon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        } else {
            currentBody.addClass('open');
            currentBody.css('max-height', currentBody[0].scrollHeight + 'px');
            $(this).find('.expand-icon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }
    });
}

// Collapse all cards function
function collapseAllCards() {
    $('.subject-main-card-body, .class-main-card-body').each(function() {
        $(this).removeClass('open').css('max-height', '0');
        $(this).siblings('.subject-main-card-header, .class-main-card-header').find('.expand-icon')
            .removeClass('fa-chevron-up').addClass('fa-chevron-down');
    });
}

$(document).ready(function() {
    // Initialize select2
    $('.select2-multiple').select2({ 
        width: '100%',
        placeholder: 'Select one or more terms...'
    });
    
    // Clear button click handler
    $('#clear_btn').click(function() {
        $('#view_type').val('subject_wise').trigger('change');
        $('#term_ids').val(null).trigger('change');
        $('#planning_view_container').hide().empty();
        $('#print_btn').hide();
        toastr.info('Form cleared');
    });
    
    // View button click handler
    $('#view_btn').click(function() {
        var view_type = $('#view_type').val();
        var term_ids = $('#term_ids').val();
        
        if (!view_type) {
            toastr.warning('Please select view type');
            return;
        }
        
        if (!term_ids || term_ids.length === 0) {
            toastr.warning('Please select at least one term');
            return;
        }
        
        $('#loader').show();
        $('#planning_view_container').hide();
        $('#print_btn').hide();
        
        $.ajax({
            url: '<?= base_url('admin/top_level_planning/getViewData') ?>',
            type: 'POST',
            data: {
                view_type: view_type,
                term_ids: term_ids
            },
            dataType: 'json',
            success: function(res) {
                $('#planning_view_container').html(res.html).show();
                $('#loader').hide();
                $('#print_btn').show();
                
                // Initialize appropriate accordion based on view type
                if (view_type === 'subject_wise') {
                    initSubjectWiseAccordion();
                } else if (view_type === 'class_wise') {
                    initClassWiseAccordion();
                }
                
                // Collapse All button functionality
                $('#collapseAllBtn').off('click').on('click', function() {
                    collapseAllCards();
                    toastr.info('All cards collapsed');
                });
                
                // Store current view data for printing
                $('#print_btn').off('click').on('click', function() {
                    var url = '<?= base_url('admin/top_level_planning/printReport') ?>';
                    url += '?view_type=' + view_type;
                    term_ids.forEach(function(id) {
                        url += '&term_ids[]=' + id;
                    });
                    window.open(url, '_blank');
                });
            },
            error: function(xhr, status, error) {
                $('#loader').hide();
                console.log('Error:', error);
                toastr.error('Error loading data');
            }
        });
    });
    
    toastr.options = {
        "positionClass": "toast-top-right",
        "timeOut": "3000"
    };
});
</script>

<style>
/* ============================================ */
/* TOP LEVEL PLANNING VIEWS - Subject & Class Wise */
/* ============================================ */

/* Subjects Container - 1 subject per row (full width) */
.subjects-container {
    display: flex;
    flex-direction: column;
    gap: 25px;
    margin-top: 20px;
}

/* Level 1: Subject Main Card - Full width */
.subject-main-card {
    border: 1px solid #e0e7ef;
    border-radius: 12px;
    background: white;
    overflow: hidden;
    transition: box-shadow 0.2s ease;
    width: 100%;
}

.subject-main-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.subject-main-card-header {
    background: #2c3e66;
    color: white;
    padding: 14px 20px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
    font-size: 16px;
    transition: background 0.2s;
}

.subject-main-card-header:hover {
    background: #1f2c4b;
}

.subject-main-card-body {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out;
    padding: 0;
    background: #ffffff;
}

.subject-main-card-body.open {
    max-height: 2000px;
    padding: 20px;
}

.class-count-badge {
    background: rgba(255,255,255,0.2);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
}

/* Classes Container - 1 class per row (full width) */
.classes-container {
    display: flex;
    flex-direction: column;
    gap: 25px;
    margin-top: 20px;
}

/* Level 1: Class Main Card - Full width */
.class-main-card {
    border: 1px solid #e0e7ef;
    border-radius: 12px;
    background: white;
    overflow: hidden;
    transition: box-shadow 0.2s ease;
    width: 100%;
}

.class-main-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.class-main-card-header {
    background: #2c3e66;
    color: white;
    padding: 14px 20px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
    font-size: 16px;
    transition: background 0.2s;
}

.class-main-card-header:hover {
    background: #1f2c4b;
}

.class-main-card-body {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out;
    padding: 0;
    background: #ffffff;
}

.class-main-card-body.open {
    max-height: 2000px;
    padding: 20px;
}

.subject-count-badge {
    background: rgba(255,255,255,0.2);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
}

/* Table Styles for both views */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.planning-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    background: white;
}

.planning-table th {
    background-color: #f0f4f8;
    padding: 12px;
    text-align: left;
    border: 1px solid #dce5ef;
    font-weight: 600;
    color: #1e4663;
}

.planning-table td {
    padding: 12px;
    border: 1px solid #e0e7ef;
    vertical-align: top;
}

.class-name-cell,
.subject-name-cell {
    background-color: #f8fafc;
    font-weight: 500;
    color: #2c3e66;
    width: 180px;
}

.objective-cell {
    line-height: 1.5;
    color: #2d3e50;
    word-wrap: break-word;
    white-space: normal;
}

.text-muted em {
    color: #999;
    font-style: italic;
}

/* Report Header */
.report-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-start: 4px solid #2c7da0;
}

.term-badge-summary {
    background: #e9f0f5;
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 12px;
    color: #2c5282;
}

.expand-icon {
    transition: transform 0.2s;
    font-size: 12px;
}

/* Responsive Tables */
@media (max-width: 768px) {
    .planning-table {
        font-size: 11px;
    }
    
    .planning-table th,
    .planning-table td {
        padding: 8px;
    }
    
    .class-name-cell,
    .subject-name-cell {
        width: 120px;
    }
    
    .subject-main-card-body.open,
    .class-main-card-body.open {
        padding: 12px;
    }
}

/* Print Styles - Clean and Printable */
@media print {
    /* Hide non-printable elements */
    .no-print, 
    .btn, 
    .card-tools, 
    .form-group, 
    .alert-info, 
    #clear_btn, 
    #view_btn, 
    #print_btn, 
    select, 
    .select2-container,
    .card-header .btn-tool, 
    #collapseAllBtn,
    button {
        display: none !important;
    }
    
    /* Force all cards to be fully expanded in print */
    .subject-main-card-body,
    .class-main-card-body {
        max-height: none !important;
        display: block !important;
        padding: 10px !important;
        overflow: visible !important;
    }
    
    /* Card borders for print */
    .subject-main-card,
    .class-main-card {
        border: 1px solid #ccc !important;
        margin-bottom: 20px !important;
        page-break-inside: avoid;
        break-inside: avoid;
    }
    
    /* Header colors for print */
    .subject-main-card-header,
    .class-main-card-header {
        background: #e0e7f0 !important;
        color: #1f3a5f !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    /* Table header for print */
    .planning-table th {
        background: #e0e7f0 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    /* Ensure table borders print properly */
    .planning-table,
    .planning-table th,
    .planning-table td {
        border: 1px solid #aaa !important;
    }
    
    /* Page setup */
    body {
        padding: 0.2in !important;
        margin: 0 !important;
        background: white !important;
    }
    
    /* Report header for print */
    .report-header {
        border: 1px solid #ccc !important;
        background: #f9f9f9 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}

/* Increased font sizes for better readability */
.planning-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;  /* Increased from 13px */
    background: white;
}

.planning-table th {
    background-color: #f0f4f8;
    padding: 12px 15px;
    text-align: left;
    border: 1px solid #dce5ef;
    font-weight: 600;
    color: #1e4663;
    font-size: 14px;  /* Increased */
}

.planning-table td {
    padding: 12px 15px;
    border: 1px solid #e0e7ef;
    vertical-align: top;
    font-size: 14px;  /* Increased */
}

.class-name-cell,
.subject-name-cell {
    background-color: #f8fafc;
    font-weight: 500;
    color: #2c3e66;
    width: 120px;  /* Reduced for short class names */
    font-size: 14px;
}

.objective-cell {
    line-height: 1.5;
    color: #2d3e50;
    word-wrap: break-word;
    white-space: normal;
    font-size: 14px;  /* Increased */
}

/* Report Header font sizes */
.report-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-start: 4px solid #2c7da0;
    font-size: 14px;
}

.report-header strong {
    font-size: 15px;
}

.term-badge-summary {
    background: #e9f0f5;
    padding: 5px 14px;
    border-radius: 30px;
    font-size: 13px;  /* Increased */
    color: #2c5282;
}

/* Card header font sizes */
.subject-main-card-header,
.class-main-card-header {
    background: #2c3e66;
    color: white;
    padding: 14px 20px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
    font-size: 16px;  /* Increased */
    transition: background 0.2s;
}

.class-count-badge,
.subject-count-badge {
    background: rgba(255,255,255,0.2);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 13px;  /* Increased */
}

/* Print styles with increased fonts */
@media print {
    .planning-table {
        font-size: 12px;
    }
    
    .planning-table th,
    .planning-table td {
        padding: 8px 10px;
    }
    
    .subject-main-card-header,
    .class-main-card-header {
        font-size: 14px;
    }
}
</style>

<?= $this->endSection() ?>