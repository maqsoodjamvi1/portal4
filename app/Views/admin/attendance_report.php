<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- Include Month Picker CSS and JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker3.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

<!-- Print Styles -->
<style media="print">
    @media print {
        body * {
            visibility: hidden;
        }
        #printable-report, #printable-report * {
            visibility: visible;
        }
        #printable-report {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            margin: 0;
            padding: 20px;
            background: white;
        }
        .no-print, .no-print * {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        .table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        .table th, .table td {
            border: 1px solid #000 !important;
            padding: 8px !important;
        }
        .badge {
            border: none !important;
            background: transparent !important;
            color: #000 !important;
            font-weight: bold !important;
        }
        @page {
            margin: 2cm;
            size: landscape;
        }
    }
</style>

<section class="content-header no-print">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-calendar-check"></i> Attendance Report</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Attendance Report</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <?php
        $classSectionOptions = [['value' => '', 'label' => 'All Classes & Sections']];
        if (!empty($class_sections)) {
            foreach ($class_sections as $cs) {
                $classSectionOptions[] = [
                    'value' => $cs->cls_sec_id,
                    'label' => $cs->class_name . (isset($cs->section_name) && $cs->section_name ? ' - ' . $cs->section_name : ''),
                ];
            }
        }

        echo view('components/report_filter_bar', [
            'formId' => 'attendance-filter-form',
            'title' => 'Attendance Filters',
            'cardClass' => 'card card-primary no-print report-filter-card',
            'fields' => [
                [
                    'type' => 'select',
                    'id' => 'filter_type',
                    'name' => 'filter_type',
                    'label' => 'Report Type *',
                    'class' => 'form-control report-select2',
                    'required' => true,
                    'options' => [
                        ['value' => '', 'label' => 'Select Report Type'],
                        ['value' => 'month', 'label' => 'Monthly Report'],
                        ['value' => 'current_week', 'label' => 'Current Week'],
                        ['value' => 'current_session', 'label' => 'Current Session'],
                        ['value' => 'custom_range', 'label' => 'Custom Date Range'],
                    ],
                    'col_class' => 'col-md-3 mb-2',
                ],
                [
                    'type' => 'text',
                    'id' => 'monthpicker',
                    'name' => 'month_year',
                    'label' => 'Select Month',
                    'class' => 'form-control',
                    'placeholder' => 'Select Month & Year',
                    'attrs' => 'autocomplete="off"',
                    'col_class' => 'col-md-2 month-fields mb-2',
                    'col_style' => 'display:none;',
                ],
                [
                    'type' => 'date',
                    'id' => 'start_date',
                    'name' => 'start_date',
                    'label' => 'Start Date',
                    'class' => 'form-control',
                    'col_class' => 'col-md-3 custom-fields mb-2',
                    'col_style' => 'display:none;',
                ],
                [
                    'type' => 'date',
                    'id' => 'end_date',
                    'name' => 'end_date',
                    'label' => 'End Date',
                    'class' => 'form-control',
                    'col_class' => 'col-md-2 custom-fields mb-2',
                    'col_style' => 'display:none;',
                ],
                [
                    'type' => 'select',
                    'id' => 'cls_sec_id',
                    'name' => 'cls_sec_id',
                    'label' => 'Class Section',
                    'class' => 'form-control report-select2',
                    'options' => $classSectionOptions,
                    'col_class' => 'col-md-3 mb-2',
                ],
                [
                    'type' => 'select',
                    'id' => 'skip_absent',
                    'name' => 'skip_absent',
                    'label' => 'Skip Absent >=',
                    'class' => 'form-control report-select2',
                    'options' => [
                        ['value' => '0', 'label' => 'Show All'],
                        ['value' => '1', 'label' => 'Skip 1+ absent (show >=1)'],
                        ['value' => '2', 'label' => 'Skip 2+ absent (show >=2)'],
                        ['value' => '3', 'label' => 'Skip 3+ absent (show >=3)'],
                        ['value' => '4', 'label' => 'Skip 4+ absent (show >=4)'],
                        ['value' => '5', 'label' => 'Skip 5+ absent (show >=5)'],
                        ['value' => '10', 'label' => 'Skip 10+ absent (show >=10)'],
                    ],
                    'col_class' => 'col-md-2 mb-2',
                ],
                [
                    'type' => 'select',
                    'id' => 'message_type',
                    'name' => 'message_type',
                    'label' => 'WhatsApp Message Type',
                    'class' => 'form-control report-select2',
                    'options' => [
                        ['value' => 'family', 'label' => 'Family Message (All Children)'],
                        ['value' => 'student', 'label' => 'Student Message (Individual)'],
                    ],
                    'col_class' => 'col-md-3 mb-2',
                ],
                [
                    'type' => 'raw',
                    'label' => 'Include Absent Dates',
                    'col_class' => 'col-md-2 mb-2',
                    'html' => '<div class="custom-control custom-switch mt-1"><input type="checkbox" class="custom-control-input" id="include_dates" checked><label class="custom-control-label" for="include_dates">Yes / No</label></div>',
                ],
            ],
            'actions' => [
                [
                    'type' => 'submit',
                    'id' => 'btn-generate-report',
                    'label' => 'Generate Report',
                    'icon' => 'fas fa-search mr-1',
                    'class' => 'btn btn-primary btn-block',
                    'col_class' => 'col-md-2 mb-2',
                ],
                [
                    'type' => 'button',
                    'id' => 'print-report',
                    'label' => 'Print Report',
                    'icon' => 'fas fa-print mr-1',
                    'class' => 'btn btn-info btn-block',
                    'attrs' => 'style="display:none;"',
                    'col_class' => 'col-md-2 mb-2',
                ],
                [
                    'type' => 'button',
                    'id' => 'export-excel',
                    'label' => 'Export',
                    'icon' => 'fas fa-file-excel mr-1',
                    'class' => 'btn btn-success btn-block',
                    'attrs' => 'style="display:none;"',
                    'col_class' => 'col-md-2 mb-2',
                ],
            ],
        ]);
    ?>
    
    <!-- Summary Card -->
    <div class="card card-info no-print" id="summary-card" style="display:none;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-bar"></i> Report Summary</h3>
        </div>
        <div class="card-body">
            <div class="row" id="summary-content"></div>
        </div>
    </div>
    
    <!-- SINGLE Report Card - Used for both screen and print -->
    <div class="card card-primary" id="report-card" style="display:none;">
        <div class="card-header no-print">
            <h3 class="card-title"><i class="fas fa-table"></i> Attendance Details</h3>
            <div class="card-tabs">
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body table-responsive" id="printable-report">
            <!-- Report header for print -->
            <!-- Report header for print -->
<div class="report-header" style="text-align: center; margin-bottom: 20px; display: none;" id="print-header">
    <h2>Attendance Report</h2>
    <p><strong>Generated On:</strong> <span id="generated-date"></span></p>
    <p><strong>Report Period (Actual Date Range Used):</strong> <span id="report-period"></span></p>
    <p><strong>Class:</strong> <span id="report-class"></span></p>
    <div id="report-period-note"></div>
    <hr>
</div>
            
            <!-- Update table headers -->
            <table id="attendance-datatable" class="table table-bordered table-hover table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Name / Class</th>
                        <th>Absent Details</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="report-tbody">
                    <tr>
                        <td colspan="4" class="text-center">Select report type and click Generate Report</td>
                    </tr>
                </tbody>
            </table>
            
            <!-- Report footer for print -->
            <div class="report-footer" style="text-align: center; margin-top: 20px; font-size: 12px; display: none;" id="print-footer">
                <hr>
                <p>This is a system generated report</p>
            </div>
        </div>
    </div>
</section>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {
    if (window.ReportUI && window.ReportUI.initReportSelects) {
        window.ReportUI.initReportSelects('#attendance-filter-form');
    }

    let dataTable = null;
    let currentReportData = null;
    let currentFilteredStudents = null;
    let currentSummary = null;
    let currentSkipAbsent = 0;
    
    // Initialize Month Picker
    $('#monthpicker').datepicker({
        format: "MM yyyy",
        viewMode: "months",
        minViewMode: "months",
        autoclose: true,
        todayHighlight: true
    });
    
    // Show/hide fields based on report type
    $('#filter_type').on('change', function() {
        const type = $(this).val();
        
        // Hide all extra fields
        $('.month-fields, .custom-fields').hide();
        $('.month-fields input, .custom-fields input').prop('required', false);
        
        if (type === 'month') {
            $('.month-fields').show();
            $('#monthpicker').prop('required', true);
        } else if (type === 'custom_range') {
            $('.custom-fields').show();
            $('.custom-fields input').prop('required', true);
        }
    });
    
    // Print report - Use the same report that's on screen
    $('#print-report').on('click', function() {
        if (currentReportData) {
            // Show print header and footer
            $('#print-header, #print-footer').show();
            // Trigger print
            window.print();
            // Hide print header and footer after print dialog closes
            setTimeout(function() {
                $('#print-header, #print-footer').hide();
            }, 500);
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'No Report',
                text: 'Please generate a report first'
            });
        }
    });
    
    // Handle form submission
    $('#attendance-filter-form').on('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        const filterType = $('#filter_type').val();
        if (!filterType) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select a report type'
            });
            return;
        }
        
        // Validate month picker for monthly report
        if (filterType === 'month') {
            const monthValue = $('#monthpicker').val();
            if (!monthValue) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select a month'
                });
                return;
            }
        }
        
        // Validate custom date range
        if (filterType === 'custom_range') {
            const startDate = $('input[name="start_date"]').val();
            const endDate = $('input[name="end_date"]').val();
            if (!startDate || !endDate) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select both start and end dates'
                });
                return;
            }
        }
        
        const formData = $(this).serialize();
        
        // Show loading
        Swal.fire({
            title: 'Loading...',
            text: 'Generating attendance report',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Disable AJAX cache
        $.ajaxSetup({ cache: false });
        
        $.post('<?= base_url("admin/attendance-report/data") ?>', formData, function(response) {
            Swal.close();
            
            if (response.success) {
                currentReportData = response.data;
                // Clear existing table data first
                clearReport();
                // Display new report
                displayReport(response.data);
                $('#export-excel, #print-report').show();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Report generated successfully',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.msg || 'Failed to generate report'
                });
            }
        }, 'json').fail(function(xhr) {
            Swal.close();
            console.error(xhr.responseText);
            Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: 'Please try again later'
            });
        });
    });
    
    // Function to clear existing report
    function clearReport() {
        // Clear table body
        $('#report-tbody').html('');
        
        // Destroy DataTable if it exists
        if (dataTable !== null) {
            dataTable.destroy();
            dataTable = null;
        }
        
        // Hide cards
        $('#summary-card').hide();
        $('#report-card').hide();
        $('#export-excel, #print-report').hide();
        
        // Hide print header/footer
        $('#print-header, #print-footer').hide();
    }
    
    // Function to display report
  // Function to display report
function displayReport(data) {
    const summary = data.summary;
    currentSummary = summary;
    
    // Display summary with actual date range used
    let summaryHtml = `
        <div class="col-lg-3 col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-calendar-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Report Period</span>
                    <span class="info-box-number">${summary.start_date} - ${summary.end_date}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-calendar-week"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Working Days</span>
                    <span class="info-box-number">${summary.working_days}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Students</span>
                    <span class="info-box-number">${summary.total_students}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-user-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Absent Days</span>
                    <span class="info-box-number">${summary.total_absent_count}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="fas fa-user-graduate"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Students with Absent</span>
                    <span class="info-box-number">${summary.students_with_absent}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-secondary"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Average Absent per Student</span>
                    <span class="info-box-number">${summary.avg_absent_per_student}</span>
                </div>
            </div>
        </div>
    `;
    
    $('#summary-content').html(summaryHtml);
    $('#summary-card').show();
    
    // Get skip absent value
    currentSkipAbsent = parseInt($('#skip_absent').val()) || 0;
    
    // Filter students with absent count >= skip value
    let students = data.students;
    if (currentSkipAbsent > 0) {
        students = students.filter(function(student) {
            return student.absent_count >= currentSkipAbsent;
        });
    }
    
    currentFilteredStudents = students;
    
    // Update print header info with actual date range used
       // Update print header info with actual date range used
const selectedClass = $('#cls_sec_id option:selected').text() || 'All Classes';
const now = new Date();
const formattedDate = now.toLocaleString('en-PK', { 
    dateStyle: 'full', 
    timeStyle: 'medium' 
});

// Get the actual start and end dates from summary
const actualStartDate = summary.start_date;
const actualEndDate = summary.end_date;
const dateNote = summary.date_note || '';

$('#generated-date').text(formattedDate);
$('#report-period').text(actualStartDate + ' - ' + actualEndDate);
$('#report-class').text(selectedClass + (currentSkipAbsent > 0 ? ' (Showing students with ' + currentSkipAbsent + '+ absents)' : ''));

// Show note if date was adjusted
if (dateNote) {
    $('#report-period-note').remove();
    const noteHtml = '<div id="report-period-note" style="color: #856404; background-color: #fff3cd; padding: 8px; border-radius: 4px; margin-top: 10px; font-size: 12px;"><i class="fas fa-info-circle"></i> ' + dateNote + '</div>';
    $('#print-header').append(noteHtml);
    $('.info-box:first .info-box-number').html(actualStartDate + ' - ' + actualEndDate + '<small style="font-size: 12px; color: #856404;">' + (dateNote ? ' *' : '') + '</small>');
}
    
    // Display table
    let tbodyHtml = '';
    
    if (students.length === 0) {
        tbodyHtml = '<tr><td colspan="4" class="text-center">No students found with absent count >= ' + currentSkipAbsent + 'NeueNeue';
    } else {
        students.forEach(function(student, index) {
            const absentDates = student.absent_dates || '-';
            const absentCount = student.absent_count;
            
            // Student name with class in same cell
            const studentInfo = '<strong>' + (student.first_name || '') + ' ' + (student.last_name || '') + '</strong><br><small class="text-muted">' + (student.class_section || '-') + '</small>';
            
            // Absent details: count + dates comma separated on same line
            let absentDetails = '';
            if (absentCount > 0) {
                absentDetails = '<span class="badge badge-danger" style="font-size: 14px; margin-right: 8px;">' + absentCount + ' day(s)</span>';
                if (absentDates !== '-') {
                    absentDetails += '<span class="absent-dates-text">' + absentDates + '</span>';
                }
            } else {
                absentDetails = '<span class="badge badge-success">0 Absent</span>';
            }
            
            // WhatsApp button - only show if student has whatsapp number
          // WhatsApp button - only show if student has whatsapp number
let whatsappBtn = '';
if (student.whatsapp && student.whatsapp !== '') {
    // Escape special characters for data attributes
    const escapedName = (student.first_name || '') + ' ' + (student.last_name || '');
    const escapedDates = absentDates.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    const escapedClassSection = (student.class_section || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    
    whatsappBtn = '<button type="button" class="btn btn-success btn-sm send-whatsapp" ' + 
        'data-student-id="' + student.student_id + '" ' +
        'data-student-name="' + escapedName + '" ' +
        'data-whatsapp="' + student.whatsapp + '" ' +
        'data-absent-count="' + absentCount + '" ' +
        'data-absent-dates="' + escapedDates + '" ' +
        'data-report-period="' + actualStartDate + ' - ' + actualEndDate + '" ' +
        'data-parent-name="' + (student.parent_name || 'Parent') + '" ' +
        'data-working-days="' + summary.working_days + '" ' +
        'data-class-section="' + escapedClassSection + '" ' +
        'data-siblings=\'' + JSON.stringify(student.siblings || []) + '\'>' +
        '<i class="fab fa-whatsapp"></i> WhatsApp</button>';
} else {
    whatsappBtn = '<button type="button" class="btn btn-secondary btn-sm" disabled title="No WhatsApp number available">' +
        '<i class="fab fa-whatsapp"></i> No Number</button>';
}
            
            tbodyHtml += `
                <tr>
                    <td style="width: 50px; text-align: center;">${index + 1}</td>
                    <td style="width: 180px;">${studentInfo}</td>
                    <td style="min-width: 400px; word-break: break-word; white-space: normal;">${absentDetails}</td>
                    <td style="width: 100px; text-align: center;">${whatsappBtn}</td>
                </tr>
            `;
        });
    }
    
    // Update table body
    $('#report-tbody').html(tbodyHtml);
    
    // Initialize DataTable
    if (dataTable !== null) {
        dataTable.destroy();
        dataTable = null;
    }
    
    dataTable = $('#attendance-datatable').DataTable({
        paging: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        ordering: true,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [2, 3] },
            {
                targets: 0,
                width: "50px",
                className: "text-center"
            },
            {
                targets: 1,
                width: "180px"
            },
            {
                targets: 2,
                width: "auto"
            },
            {
                targets: 3,
                width: "100px",
                className: "text-center"
            }
        ],
        autoWidth: false,
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ students",
            zeroRecords: "No matching students found",
            emptyTable: "No data available"
        },
        destroy: true,
        retrieve: true,
        drawCallback: function() {
            // Re-bind WhatsApp buttons after each DataTable draw
            bindWhatsAppButtons();
        }
    });
    
    // Show report card
    $('#report-card').show();
    
    // Scroll to report
    $('html, body').animate({
        scrollTop: $('#summary-card').offset().top - 100
    }, 500);
}

// Function to bind WhatsApp button click events (using event delegation)
// Message templates
// Message templates (WhatsApp-friendly - No emojis)
const messageTemplates = {
    // Family Message Template (All Children)
    family: {
        name: 'Family Message',
        template: function(parentName, startDate, endDate, workingDays, children, includeDates, schoolName, maxAbsent) {
            // Filter children with absents
            const childrenWithAbsents = children.filter(c => c.absent_count > 0);
            
            if (childrenWithAbsents.length === 0) {
                return `Dear *${parentName}*,\n\n` +
                       `This is a notification regarding your children's attendance for the period *${startDate}* – *${endDate}*.\n\n` +
                       `Total Working Days: ${workingDays}\n\n` +
                       `Good news! None of your children had any absences during this period.\n\n` +
                       `Keep up the good attendance!\n\n` +
                       `Regards,\n${schoolName}`;
            }
            
            let message = `Dear *${parentName}*,\n\n`;
            message += `This is a notification regarding your children's attendance for the period *${startDate}* – *${endDate}*.\n\n`;
            message += `Total Working Days: ${workingDays}\n\n`;
            message += `[ ATTENDANCE DETAILS ]\n`;
            message += `----------------------------------------\n\n`;
            
            let childNumber = 1;
            childrenWithAbsents.forEach(function(child) {
                const classInfo = child.class_section ? ` (${child.class_section})` : '';
                message += `${childNumber}. ${child.student_name}${classInfo}\n`;
                message += `   Absent for: ${child.absent_count} day(s)\n`;
                
                if (includeDates && child.absent_dates && child.absent_dates !== '-') {
                    message += `   Absent Dates: ${child.absent_dates}\n`;
                }
                message += `\n`;
                childNumber++;
            });
            
            // Add alert based on max absent
            if (maxAbsent >= 10) {
                message += `[!!! URGENT !!!] This is a serious concern. Please contact the school office immediately to discuss attendance.\n\n`;
            } else if (maxAbsent >= 5) {
                message += `[!! IMPORTANT !!] Please prioritize attendance as repeated absences affect academic progress.\n\n`;
            } else if (maxAbsent >= 3) {
                message += `[! NOTICE !] Please ensure regular attendance to avoid falling behind in studies.\n\n`;
            }
            
            message += `[REQUEST] Kindly ensure regular attendance. If there are any health or other concerns, please inform the school.\n\n`;
            message += `Thank you for your cooperation.\n\n`;
            message += `Regards,\n${schoolName}`;
            
            return message;
        }
    },
    
    // Student Message Template (Individual Student)
    student: {
        name: 'Student Message',
        template: function(parentName, studentName, classSection, startDate, endDate, workingDays, absentCount, absentDates, includeDates, schoolName, maxAbsent) {
            if (absentCount === 0) {
                return `Dear *${parentName}*,\n\n` +
                       `This is a notification regarding your child *${studentName}*${classSection ? ' (' + classSection + ')' : ''}'s attendance for the period *${startDate}* – *${endDate}*.\n\n` +
                       `Total Working Days: ${workingDays}\n\n` +
                       `Great news! *${studentName}* had perfect attendance during this period with 0 absences.\n\n` +
                       `Keep up the excellent attendance record!\n\n` +
                       `Regards,\n${schoolName}`;
            }
            
            let message = `Dear *${parentName}*,\n\n`;
            message += `This is a notification regarding your child *${studentName}*${classSection ? ' (' + classSection + ')' : ''}'s attendance for the period *${startDate}* – *${endDate}*.\n\n`;
            message += `Total Working Days: ${workingDays}\n\n`;
            message += `Absent for: ${absentCount} day(s)\n`;
            
            if (includeDates && absentDates && absentDates !== '-') {
                message += `\nAbsent Dates: ${absentDates}\n`;
            }
            
            // Add alert based on absent count
            if (absentCount >= 10) {
                message += `\n[!!! URGENT !!!] This is a serious concern. Please contact the school office immediately to discuss attendance.\n`;
            } else if (absentCount >= 5) {
                message += `\n[!! IMPORTANT !!] Please prioritize attendance as repeated absences affect academic progress.\n`;
            } else if (absentCount >= 3) {
                message += `\n[! NOTICE !] Please ensure regular attendance to avoid falling behind in studies.\n`;
            }
            
            message += `\n[REQUEST] Kindly ensure regular attendance. If there are any health or other concerns, please inform the school.\n\n`;
            message += `Thank you for your cooperation.\n\n`;
            message += `Regards,\n${schoolName}`;
            
            return message;
        }
    }
};

// Helper function to get alert message based on absent count
function getAlertMessage(absentCount) {
    if (absentCount >= 10) {
        return `⚠️⚠️⚠️ *URGENT:* This is a serious concern. Please contact the school office immediately to discuss attendance.\n`;
    } else if (absentCount >= 5) {
        return `⚠️⚠️ *IMPORTANT:* Please prioritize attendance as repeated absences affect academic progress.\n`;
    } else if (absentCount >= 3) {
        return `⚠️ *NOTICE:* Please ensure regular attendance to avoid falling behind in studies.\n`;
    }
    return '';
}


// Function to bind WhatsApp button click events
// Function to bind WhatsApp button click events
function bindWhatsAppButtons() {
    $(document).off('click', '.send-whatsapp').on('click', '.send-whatsapp', function(e) {
        e.preventDefault();
        
        const studentId = $(this).data('student-id');
        const studentName = $(this).data('student-name');
        const whatsappNumber = $(this).data('whatsapp');
        const reportPeriod = $(this).data('report-period');
        const parentName = $(this).data('parent-name');
        const siblings = $(this).data('siblings');
        const workingDays = $(this).data('working-days');
        const classSection = $(this).data('class-section') || '';
        const filterType = $('#filter_type').val();
        const absentCount = $(this).data('absent-count');
        const absentDates = $(this).data('absent-dates');
        
        // Get user preferences
        const messageType = $('#message_type').val();
        const includeDates = $('#include_dates').is(':checked');
        const schoolName = '<?= getSchoolInfo()->system_name ?? "School" ?>';
        
        // Parse report period
        const periodParts = reportPeriod.split(' - ');
        const startDate = periodParts[0];
        const endDate = periodParts[1];
        
        // First, generate a secure share token
        Swal.fire({
            title: 'Generating secure link...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: '<?= base_url("admin/attendance-report/generateShareToken") ?>',
            type: 'POST',
            data: { student_id: studentId },
            dataType: 'json',
            success: function(tokenResponse) {
                if (tokenResponse.success) {
                    const shareLink = tokenResponse.share_url;
                    
                    // Build the message based on report type
                    let message = '';
                    
                    if (filterType === 'current_session') {
                        // Fetch weekly attendance data
                        $.ajax({
                            url: '<?= base_url("admin/attendance-report/getWeeklyAttendance") ?>',
                            type: 'POST',
                            data: { student_id: studentId },
                            dataType: 'json',
                            success: function(response) {
                                Swal.close();
                                
                                if (response.success) {
                                    const weeklyData = response.data;
                                    const message = generateWeeklyMessage(
                                        weeklyData, parentName, schoolName, includeDates, shareLink
                                    );
                                    
                                    const encodedMessage = encodeURIComponent(message);
                                    const whatsappUrl = 'https://wa.me/' + whatsappNumber + '?text=' + encodedMessage;
                                    window.open(whatsappUrl, '_blank');
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.msg || 'Failed to fetch weekly attendance'
                                    });
                                }
                            },
                            error: function() {
                                Swal.close();
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Failed to fetch weekly attendance data'
                                });
                            }
                        });
                        
                    } else {
                        Swal.close();
                        
                        // Regular attendance format
                        message = `Dear *${parentName}*,\n\n`;
                        message += `📋 *Attendance Notification*\n`;
                        message += `Student: *${studentName}* (${classSection})\n`;
                        message += `Period: ${reportPeriod}\n`;
                        message += `Working Days: ${workingDays}\n`;
                        message += `Absent Days: ${absentCount}\n`;
                        
                        if (includeDates && absentDates && absentDates !== '-') {
                            message += `Absent Dates: ${absentDates}\n`;
                        }
                        
                        if (messageType === 'family' && siblings && siblings.length > 0) {
                            message += `\n👨‍👩‍👧‍👦 *Siblings Attendance:*\n`;
                            siblings.forEach(function(sibling) {
                                if (sibling.student_name !== studentName) {
                                    message += `• ${sibling.student_name} (${sibling.class_section}): ${sibling.absent_count} day(s)\n`;
                                }
                            });
                        }
                        
                        if (absentCount >= 10) {
                            message += `\n⚠️⚠️⚠️ *URGENT:* Please contact the school office immediately.\n`;
                        } else if (absentCount >= 5) {
                            message += `\n⚠️⚠️ *IMPORTANT:* Please prioritize regular attendance.\n`;
                        } else if (absentCount >= 3) {
                            message += `\n⚠️ *NOTICE:* Please ensure regular attendance.\n`;
                        }
                        
                        message += `\n🔗 *View Complete Details (Secure Link):*\n${shareLink}\n\n`;
                        message += `This link is valid for 30 days and is unique to your child.\n\n`;
                        message += `Regards,\n${schoolName}`;
                        
                        const encodedMessage = encodeURIComponent(message);
                        const whatsappUrl = 'https://wa.me/' + whatsappNumber + '?text=' + encodedMessage;
                        window.open(whatsappUrl, '_blank');
                    }
                } else {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: tokenResponse.msg || 'Failed to generate secure link'
                    });
                }
            },
            error: function() {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to generate secure share link'
                });
            }
        });
    });
}


// Function to bind WhatsApp button click events
// function bindWhatsAppButtons() {
//     $(document).off('click', '.send-whatsapp').on('click', '.send-whatsapp', function(e) {
//         e.preventDefault();
        
//         const studentId = $(this).data('student-id');
//         const studentName = $(this).data('student-name');
//         const whatsappNumber = $(this).data('whatsapp');
//         const reportPeriod = $(this).data('report-period');
//         const parentName = $(this).data('parent-name');
//         const siblings = $(this).data('siblings');
//         const workingDays = $(this).data('working-days');
//         const classSection = $(this).data('class-section') || '';
//         const filterType = $('#filter_type').val();
//         const absentCount = $(this).data('absent-count');
//         const absentDates = $(this).data('absent-dates');
        
//         // Generate share link for detailed view
//         const shareLink = `<?= base_url() ?>parent/attendance/share/${studentId}`;
        
//         // Get user preferences
//         const messageType = $('#message_type').val();
//         const includeDates = $('#include_dates').is(':checked');
//         const schoolName = '<?= getSchoolInfo()->system_name ?? "School" ?>';
        
//         // Parse report period
//         const periodParts = reportPeriod.split(' - ');
//         const startDate = periodParts[0];
//         const endDate = periodParts[1];
        
//         // Build the message based on report type
//         let message = '';
        
//         if (filterType === 'current_session') {
//             // Weekly attendance format for session report
//             message = `Dear *${parentName}*,\n\n`;
//             message += `📊 *Weekly Attendance Report*\n`;
//             message += `Student: *${studentName}* (${classSection})\n`;
//             message += `Period: ${reportPeriod}\n`;
//             message += `Working Days: ${workingDays}\n`;
//             message += `Total Absent: ${absentCount} days\n\n`;
            
//             if (includeDates && absentDates && absentDates !== '-') {
//                 message += `📅 Absent Dates: ${absentDates}\n\n`;
//             }
            
//             message += `🔗 *View Complete Details:*\n${shareLink}\n\n`;
//             message += `Click the link above to see detailed attendance calendar and history.\n\n`;
//             message += `Regards,\n${schoolName}`;
            
//         } else {
//             // Regular attendance format for other report types
//             message = `Dear *${parentName}*,\n\n`;
//             message += `📋 *Attendance Notification*\n`;
//             message += `Student: *${studentName}* (${classSection})\n`;
//             message += `Period: ${reportPeriod}\n`;
//             message += `Working Days: ${workingDays}\n`;
//             message += `Absent Days: ${absentCount}\n`;
            
//             if (includeDates && absentDates && absentDates !== '-') {
//                 message += `Absent Dates: ${absentDates}\n`;
//             }
            
//             // Add siblings info if family message type
//             if (messageType === 'family' && siblings && siblings.length > 0) {
//                 message += `\n👨‍👩‍👧‍👦 *Siblings Attendance:*\n`;
//                 siblings.forEach(function(sibling) {
//                     if (sibling.student_name !== studentName) {
//                         message += `• ${sibling.student_name} (${sibling.class_section}): ${sibling.absent_count} day(s)\n`;
//                     }
//                 });
//             }
            
//             message += `\n🔗 *View Complete Details:*\n${shareLink}\n\n`;
//             message += `Click the link above to see detailed attendance calendar and history.\n\n`;
//             message += `Regards,\n${schoolName}`;
//         }
        
//         // Encode and send WhatsApp message
//         const encodedMessage = encodeURIComponent(message);
//         const whatsappUrl = 'https://wa.me/' + whatsappNumber + '?text=' + encodedMessage;
//         window.open(whatsappUrl, '_blank');
//     });
// }
// Generate weekly format message for session report
// Generate weekly format message for session report
// Generate weekly format message with WhatsApp-safe symbols

// Generate weekly format message for session report with sharing link
function generateWeeklyMessage(data, parentName, schoolName, includeDates, shareLink) {
    let message = `Dear *${parentName}*,\n\n`;
    message += `Weekly Attendance Report of *${data.student_name}* (${data.class_section}).\n\n`;
    message += `*📅 SESSION SUMMARY*\n`;
    message += `Total Working Days: ${data.summary.total_working_days}\n`;
    message += `Total Absent Days: ${data.summary.total_absent_days}\n\n`;
    
    // Add weekly breakdown
    message += `*📊 WEEKLY BREAKDOWN*\n`;
    message += `----------------------------------------\n\n`;
    
    data.weekly_data.forEach(function(term) {
        if (term.weeks.length === 0) return;
        
        message += `*📚 ${term.term_name}*\n`;
        
        term.weeks.forEach(function(week) {
            // Only show weeks that have working days (not just days with records)
            if (week.working_days > 0) {
                
                message += `\n*Week ${week.week_no}*\n`;
                
                // Create attendance line - INCLUDE ALL working days (including OFF)
                let dayLine = '';
                week.days.forEach(function(day) {
                    // Include ALL working days (is_working_day = true)
                    // This includes OFF days (has_record = false but is_working_day = true)
                    if (day.is_working_day === true && !day.is_future) {
                        let statusSymbol = '';
                        
                        if (day.status === 'P') {
                            statusSymbol = 'P';
                        } else if (day.status === 'A') {
                            statusSymbol = 'A';
                        } else if (day.status === 'L') {
                            statusSymbol = 'L';
                        } else if (day.status === 'EL') {
                            statusSymbol = 'EL';
                        } else if (day.status === 'OFF') {
                            statusSymbol = 'Off';  // Show as "Off" for days without records
                        } else {
                            statusSymbol = day.status;
                        }
                        
                        dayLine += `${day.day_name}: ${statusSymbol} | `;
                    }
                });
                // Remove trailing " | "
                dayLine = dayLine.replace(/ \| $/, '');
                message += `${dayLine}\n`;
                
                // Show absent dates (only for actual absent records, not OFF days)
                if (includeDates && week.absent_count > 0) {
                    let absentDateList = '';
                    week.days.forEach(function(day) {
                        // Only include dates where status is 'A' (actual absent, not OFF)
                        if (day.has_record && day.status === 'A' && !day.is_future) {
                            absentDateList += `${day.date_formatted}, `;
                        }
                    });
                    if (absentDateList) {
                        absentDateList = absentDateList.replace(/, $/, '');
                        message += `Absent Dates: ${absentDateList}\n`;
                    }
                }
                
                // Show absent count based on working days
                message += `Absent: ${week.absent_count}/${week.working_days} days\n`;
            }
        });
        message += `\n`;
    });
    
    // Add alert based on total absent days
    if (data.summary.total_absent_days >= 10) {
        message += `*!!! URGENT:* Your child has ${data.summary.total_absent_days} absences. Please contact the school office immediately.\n\n`;
    } else if (data.summary.total_absent_days >= 5) {
        message += `*!! IMPORTANT:* Your child has ${data.summary.total_absent_days} absences. Please prioritize regular attendance.\n\n`;
    } else if (data.summary.total_absent_days >= 3) {
        message += `*! NOTICE:* Your child has ${data.summary.total_absent_days} absences. Please ensure regular attendance.\n\n`;
    }
    
    message += `*Request:* Kindly ensure regular attendance. If there are any health or other concerns, please inform the school.\n\n`;
    message += `Thank you for your cooperation.\n\n`;
    
    // Add sharing link
    if (shareLink) {
        message += `🔗 *View Complete Details:*\n${shareLink}\n\n`;
        message += `Click the link above to see detailed attendance calendar and history.\n\n`;
    }
    
    message += `Regards,\n*${schoolName}*`;
    
    return message;
}
// function generateWeeklyMessage(data, parentName, schoolName, includeDates) {
//     let message = `Dear *${parentName}*,\n\n`;
//     message += `This is a weekly attendance report for your child *${data.student_name}* (${data.class_section}).\n\n`;
//     message += `▬▬▬ SESSION SUMMARY ▬▬▬\n`;
//     message += `Total Days with Records: ${data.summary.total_working_days}\n`;
//     message += `Total Absent Days: ${data.summary.total_absent_days}\n\n`;
    
//     // Add weekly breakdown
//     message += `▬▬▬ WEEKLY BREAKDOWN ▬▬▬\n`;
//     message += `----------------------------------------\n\n`;
    
//     data.weekly_data.forEach(function(term) {
//         if (term.weeks.length === 0) return;
        
//         message += `═══ ${term.term_name} ═══\n`;
        
//         term.weeks.forEach(function(week) {
//             if (week.days_with_record > 0) {
//                 message += `\n◈ Week ${week.week_no}: ${week.week_name}\n`;
                
//                 // Create attendance line (only days with records)
//                 let dayLine = '';
//                 week.days.forEach(function(day) {
//                     if (day.has_record) {
//                         let statusSymbol = '';
//                         if (day.is_future) {
//                             statusSymbol = '?';
//                         } else if (day.status === 'P') {
//                             statusSymbol = '✓';  // Checkmark
//                         } else if (day.status === 'A') {
//                             statusSymbol = '✗';  // Cross mark
//                         } else if (day.status === 'L') {
//                             statusSymbol = 'L';
//                         } else if (day.status === 'EL') {
//                             statusSymbol = 'EL';
//                         } else if (day.status === 'LC') {
//                             statusSymbol = 'LC';
//                         } else {
//                             statusSymbol = day.status;
//                         }
//                         dayLine += `${day.day_name}:${statusSymbol} | `;
//                     }
//                 });
//                 // Remove trailing " | "
//                 dayLine = dayLine.replace(/ \| $/, '');
//                 message += `${dayLine}\n`;
                
//                 if (includeDates && week.absent_count > 0) {
//                     let absentDateList = '';
//                     week.days.forEach(function(day) {
//                         if (day.has_record && day.status === 'A' && !day.is_future) {
//                             absentDateList += `${day.date_formatted}, `;
//                         }
//                     });
//                     if (absentDateList) {
//                         absentDateList = absentDateList.replace(/, $/, '');
//                         message += `Absent Dates: ${absentDateList}\n`;
//                     }
//                 }
//                 message += `Absent: ${week.absent_count}/${week.days_with_record} days\n`;
//             }
//         });
//         message += `\n`;
//     });
    
//     // Add alert based on total absent days
//     if (data.summary.total_absent_days >= 10) {
//         message += `⚠️⚠️⚠️ URGENT ⚠️⚠️⚠️\nYour child has ${data.summary.total_absent_days} absences. Please contact the school office immediately.\n\n`;
//     } else if (data.summary.total_absent_days >= 5) {
//         message += `⚠️⚠️ IMPORTANT ⚠️⚠️\nYour child has ${data.summary.total_absent_days} absences. Please prioritize regular attendance.\n\n`;
//     } else if (data.summary.total_absent_days >= 3) {
//         message += `⚠️ NOTICE ⚠️\nYour child has ${data.summary.total_absent_days} absences. Please ensure regular attendance.\n\n`;
//     }
    
//     message += `📝 REQUEST: Kindly ensure regular attendance. If there are any health or other concerns, please inform the school.\n\n`;
//     message += `Thank you for your cooperation.\n\n`;
//     message += `Regards,\n${schoolName}`;
    
//     return message;
// }

// // Generate weekly format message for session report (WhatsApp-friendly version)
// // Generate weekly format message for session report (WhatsApp-friendly version)
// // Generate weekly format message for session report
// function generateWeeklyMessage(data, parentName, schoolName, includeDates) {
//     let message = `Dear *${parentName}*,\n\n`;
//     message += `Weekly Attendance Report of *${data.student_name}* .\n\n`;
//     message += `*📅 SESSION SUMMARY*\n`;
//     message += `Total Working Days: ${data.summary.total_working_days}\n`;
//     message += `Total Absent Days: ${data.summary.total_absent_days}\n\n`;
    
//     // Add weekly breakdown
//     message += `*📊 WEEKLY BREAKDOWN*\n`;
//     message += `----------------------------------------\n\n`;
    
//     data.weekly_data.forEach(function(term) {
//         if (term.weeks.length === 0) return;
        
//         message += `*📚 ${term.term_name}*\n`;
        
//         term.weeks.forEach(function(week) {
//             if (week.days_with_record > 0) {
                
//                 message += `\n*Week ${week.week_no}*\n`;
                
//                 // Create attendance line (only days with records)
//                 let dayLine = '';
//                 week.days.forEach(function(day) {
//                     if (day.has_record) {
//                         let statusSymbol = '';
//                         if (day.is_future) {
//                             statusSymbol = '?';
//                         } else if (day.status === 'P') {
//                             statusSymbol = 'P';
//                         } else if (day.status === 'A') {
//                             statusSymbol = 'A';
//                         } else if (day.status === 'L') {
//                             statusSymbol = 'L';
//                         } else if (day.status === 'EL') {
//                             statusSymbol = 'EL';
//                         } else {
//                             statusSymbol = day.status;
//                         }
//                         dayLine += `${day.day_name}: ${statusSymbol} | `;
//                     }
//                 });
//                 // Remove trailing " | "
//                 dayLine = dayLine.replace(/ \| $/, '');
//                 message += `${dayLine}\n`;
                
//                 if (includeDates && week.absent_count > 0) {
//                     let absentDateList = '';
//                     week.days.forEach(function(day) {
//                         if (day.has_record && day.status === 'A' && !day.is_future) {
//                             absentDateList += `${day.date_formatted}, `;
//                         }
//                     });
//                     if (absentDateList) {
//                         absentDateList = absentDateList.replace(/, $/, '');
//                         message += `Absent Dates: ${absentDateList}\n`;
//                     }
//                 }
//                 message += `Absent: ${week.absent_count}/${week.days_with_record} days\n`;
//             }
//         });
//         message += `\n`;
//     });
    
//     // Add alert based on total absent days
//     if (data.summary.total_absent_days >= 10) {
//         message += `*!!! URGENT:* Your child has ${data.summary.total_absent_days} absences. Please contact the school office immediately.\n\n`;
//     } else if (data.summary.total_absent_days >= 5) {
//         message += `*!! IMPORTANT:* Your child has ${data.summary.total_absent_days} absences. Please prioritize regular attendance.\n\n`;
//     } else if (data.summary.total_absent_days >= 3) {
//         message += `*! NOTICE:* Your child has ${data.summary.total_absent_days} absences. Please ensure regular attendance.\n\n`;
//     }
    
//     message += `*Request:* Kindly ensure regular attendance. If there are any health or other concerns, please inform the school.\n\n`;
//     message += `Thank you for your cooperation.\n\n`;
//     message += `Regards,\n*${schoolName}*`;
    
//     return message;
// }
    // Export to Excel
    $('#export-excel').on('click', function() {
        const formData = $('#attendance-filter-form').serialize();
        
        const $form = $('<form method="POST" action="<?= base_url("admin/attendance-report/export") ?>"></form>');
        
        $.each(formData.split('&'), function(i, pair) {
            const parts = pair.split('=');
            $form.append($('<input>', {
                type: 'hidden',
                name: decodeURIComponent(parts[0]),
                value: decodeURIComponent(parts[1] || '')
            }));
        });
        
        $('body').append($form);
        $form.submit();
        $form.remove();
    });
});
</script>

<style>
.info-box {
    min-height: 90px;
    margin-bottom: 15px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
    border-radius: 5px;
}
.info-box-icon {
    border-radius: 5px 0 0 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 70px;
    height: 90px;
    font-size: 30px;
}
.info-box-content {
    padding: 10px 15px;
}
.info-box-number {
    font-size: 20px;
    font-weight: bold;
}
.badge-danger {
    font-size: 14px;
    padding: 5px 10px;
    border-radius: 4px;
}
.badge-success {
    font-size: 14px;
    padding: 5px 10px;
    border-radius: 4px;
}
.absent-dates-text {
    font-size: 13px;
    color: #dc3545;
}
#attendance-datatable_wrapper {
    overflow-x: auto;
}
.datepicker {
    z-index: 9999 !important;
}
table.dataTable tbody td {
    vertical-align: middle;
}
/* Print styles - ensure header/footer show only when printing */
@media print {
    #print-header, #print-footer {
        display: block !important;
    }
}

/* Column width adjustments */
#attendance-datatable th:nth-child(1),
#attendance-datatable td:nth-child(1) {
    width: 50px !important;
    max-width: 50px !important;
    text-align: center;
}

#attendance-datatable th:nth-child(2),
#attendance-datatable td:nth-child(2) {
    width: 180px !important;
    max-width: 180px !important;
}

#attendance-datatable th:nth-child(3),
#attendance-datatable td:nth-child(3) {
    width: auto !important;
    min-width: 400px !important;
    max-width: none !important;
    word-break: break-word;
    white-space: normal;
}

/* For print media */
@media print {
    #attendance-datatable th:nth-child(1),
    #attendance-datatable td:nth-child(1) {
        width: 30px !important;
    }
    
    #attendance-datatable th:nth-child(2),
    #attendance-datatable td:nth-child(2) {
        width: 150px !important;
    }
    
    #attendance-datatable th:nth-child(3),
    #attendance-datatable td:nth-child(3) {
        width: auto !important;
        min-width: 300px !important;
    }
}

/* Action column */
#attendance-datatable th:last-child,
#attendance-datatable td:last-child {
    width: 100px !important;
    min-width: 100px !important;
    max-width: 100px !important;
    text-align: center !important;
}

/* WhatsApp button styling */
.send-whatsapp {
    background-color: #25D366;
    border-color: #25D366;
    color: white;
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 4px;
    cursor: pointer;
}

.send-whatsapp:hover {
    background-color: #128C7E;
    border-color: #128C7E;
}

.btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
    cursor: not-allowed;
}

/* Custom Switch Styling */
.custom-control-input:checked ~ .custom-control-label::before {
    background-color: #25D366;
    border-color: #25D366;
}

.custom-switch .custom-control-label::before {
    width: 50px;
    border-radius: 25px;
}

.custom-switch .custom-control-label::after {
    width: 20px;
    border-radius: 20px;
}

.custom-switch .custom-control-input:checked ~ .custom-control-label::after {
    transform: translateX(26px);
}
</style>

<?= $this->endSection() ?>