<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
    /* ============================================
       GLOBAL & RESPONSIVE STYLES
    ============================================ */
    :root {
        --primary-color: #007bff;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --info-color: #17a2b8;
    }
    
    /* Mobile-first approach */
    .bulk-update-container {
        padding: 0;
    }
    
    /* Stats Cards */
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 15px;
        color: white;
        text-align: center;
        transition: transform 0.2s;
    }
    .stats-card:hover {
        transform: translateY(-3px);
    }
    .stats-card.primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .stats-card.success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .stats-card.warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .stats-card.info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .stats-number {
        font-size: 28px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .stats-label {
        font-size: 12px;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    /* Student Card Styles - Mobile First */
    .student-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 16px;
        overflow: hidden;
        transition: all 0.3s ease;
        border-left: 4px solid var(--primary-color);
    }
    .student-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }
    
    .student-card-header {
        background: #f8f9fa;
        padding: 15px;
        cursor: pointer;
        transition: background 0.2s;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    .student-card-header:hover {
        background: #e9ecef;
    }
    
    .student-info {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .student-avatar {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, var(--primary-color), var(--info-color));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 18px;
    }
    .student-details {
        flex: 1;
    }
    .student-name {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 4px;
        color: #333;
    }
    .student-meta {
        font-size: 12px;
        color: #6c757d;
    }
    .student-meta i {
        margin-right: 4px;
        width: 14px;
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
        gap: 5px;
    }
    .status-badge.active { background: #d4edda; color: #155724; }
    .status-badge.pending { background: #fff3cd; color: #856404; }
    
    .card-actions {
        display: flex;
        gap: 8px;
    }
    .card-actions button {
        padding: 6px 12px;
        font-size: 12px;
    }
    
    /* Collapsible Body */
    .student-card-body {
        padding: 0;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
    }
    .student-card.active .student-card-body {
        max-height: 800px;
        transition: max-height 0.5s ease-in;
    }
    .student-card-body-inner {
        padding: 20px;
        background: white;
    }
    
    /* Form Grid - Responsive */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
    }
    @media (min-width: 768px) {
        .form-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .form-grid-full {
            grid-column: span 2;
        }
    }
    
    .form-field {
        margin-bottom: 0;
    }
    .form-field label {
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 5px;
        color: #495057;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .form-field input, .form-field select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.2s;
    }
    .form-field input:focus, .form-field select:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
    }
    
    /* BMI Display */
    .bmi-display {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 12px;
        text-align: center;
    }
    .bmi-value {
        font-size: 24px;
        font-weight: bold;
    }
    .bmi-category {
        font-size: 12px;
        padding: 4px 10px;
        border-radius: 20px;
        display: inline-block;
        margin-top: 5px;
    }
    .bmi-underweight { background: #3498db; color: white; }
    .bmi-normal { background: #2ecc71; color: white; }
    .bmi-overweight { background: #f39c12; color: white; }
    .bmi-obese { background: #e74c3c; color: white; }
    
    /* Toggle Switch */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
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
        transition: 0.3s;
        border-radius: 24px;
    }
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.3s;
        border-radius: 50%;
    }
    input:checked + .toggle-slider {
        background-color: var(--success-color);
    }
    input:checked + .toggle-slider:before {
        transform: translateX(26px);
    }
    
    /* Filter Bar - Responsive */
    .filter-bar {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .filter-row {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    @media (min-width: 768px) {
        .filter-row {
            flex-direction: row;
            align-items: flex-end;
            gap: 15px;
        }
        .filter-group {
            flex: 1;
        }
        .filter-actions {
            flex-shrink: 0;
        }
    }
    .filter-group label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
        color: #6c757d;
    }
    
    /* Loader */
    .loader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.6);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
    }
    .loader-overlay.active {
        display: flex;
    }
    .loader-content {
        background: white;
        padding: 25px 35px;
        border-radius: 16px;
        text-align: center;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 50px 20px;
        background: #f8f9fa;
        border-radius: 12px;
    }
    .empty-state i {
        font-size: 48px;
        color: #adb5bd;
        margin-bottom: 15px;
    }
    
    /* Toast Customizations */
    .toast-success { background: #28a745 !important; }
    .toast-error { background: #dc3545 !important; }
    
    /* Responsive Table Fallback (for larger screens) */
    @media (min-width: 992px) {
        .mobile-view {
            display: none;
        }
        .desktop-view {
            display: block;
        }
    }
    @media (max-width: 991px) {
        .desktop-view {
            display: none;
        }
        .mobile-view {
            display: block;
        }
    }
    
    /* Progress Indicator */
    .save-progress {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: white;
        border-radius: 40px;
        padding: 10px 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        display: none;
        align-items: center;
        gap: 12px;
        z-index: 1000;
    }
    .save-progress.active {
        display: flex;
    }
</style>

<?= view('components/bulk_students_header', [
    'title' => 'Bulk Update (DOB & BMI)',
    'subtitle' => 'DOB & BMI'
]) ?>

<!-- Main Content -->
<section class="content">
    <div class="container-fluid bulk-update-container">
        <div class="card card-primary card-outline shadow-sm">
            
            <!-- Nav Tabs - Responsive -->
            <div class="card-header pb-0">
                <?= view('components/bulk_students_tabs', ['active' => 'dob']) ?>
            </div>

            <!-- Filter Bar -->
            <div class="card-body">
                <div class="filter-bar">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label><i class="fas fa-graduation-cap"></i> SELECT CLASS SECTION</label>
                            <select class="form-control" id="cls_sec_id">
                                <option value="">-- Select Class --</option>
                                <?php if (!empty($sectionsclassinfo)) : ?>
                                    <?php foreach ($sectionsclassinfo as $sectionvalue) : ?>
                                        <option value="<?= esc($sectionvalue['cls_sec_id']) ?>">
                                            <?= esc($sectionvalue['sectionclassname']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label><i class="fas fa-search"></i> SEARCH STUDENT</label>
                            <input type="text" id="searchStudentInput" class="form-control" placeholder="Type to filter students...">
                        </div>
                        <div class="filter-actions">
                            <button class="btn btn-primary btn-block" id="expandAllBtn">
                                <i class="fas fa-expand-alt"></i> Expand All
                            </button>
                            <button class="btn btn-secondary btn-block mt-2" id="collapseAllBtn">
                                <i class="fas fa-compress-alt"></i> Collapse All
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stats Summary Cards -->
                <div class="row mb-4" id="statsRow" style="display: none;">
                    <div class="col-6 col-md-3">
                        <div class="stats-card primary">
                            <div class="stats-number" id="totalStudents">0</div>
                            <div class="stats-label">Total Students</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stats-card success">
                            <div class="stats-number" id="completeCount">0</div>
                            <div class="stats-label">Complete Records</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stats-card warning">
                            <div class="stats-number" id="pendingCount">0</div>
                            <div class="stats-label">Pending Updates</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stats-card info">
                            <div class="stats-number" id="bmiCount">0</div>
                            <div class="stats-label">BMI Calculated</div>
                        </div>
                    </div>
                </div>

                <!-- Students List Container -->
                <div id="studentsListContainer">
                    <div id="loaderContainer" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading students...</p>
                    </div>
                    <div id="studentsList"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Save Progress Indicator -->
<div id="saveProgress" class="save-progress">
    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
    <span>Saving changes...</span>
</div>

<!-- Global Loader Overlay -->
<div id="globalLoader" class="loader-overlay">
    <div class="loader-content">
        <div class="spinner-border text-primary mb-2" role="status"></div>
        <div>Processing...</div>
    </div>
</div>

<script>
// ============================================
// MAIN APPLICATION
// ============================================
(function() {
    'use strict';

    // Configuration
    const URL_DATA = "<?= base_url('admin/students_bulk_info_date_of_birth/data') ?>";
    const URL_SAVE = "<?= base_url('admin/students_bulk_info_date_of_birth/save_student_info') ?>";
    const CSRF_NAME = "<?= csrf_token() ?>";
    let CSRF_HASH = "<?= csrf_hash() ?>";

    // State
    let currentStudents = [];
    let saveQueue = [];
    let isSaving = false;

    // ============================================
    // UTILITY FUNCTIONS
    // ============================================
    
    function calculateBMI(height, weight) {
        if (!height || !weight || height <= 0 || weight <= 0) return null;
        const heightInMeters = height / 100;
        const bmi = weight / (heightInMeters * heightInMeters);
        return Math.round(bmi * 100) / 100;
    }

    function getBMICategory(bmi) {
        if (!bmi) return null;
        if (bmi < 18.5) return { text: 'Underweight', class: 'bmi-underweight' };
        if (bmi < 25) return { text: 'Normal', class: 'bmi-normal' };
        if (bmi < 30) return { text: 'Overweight', class: 'bmi-overweight' };
        return { text: 'Obese', class: 'bmi-obese' };
    }

    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;
        return date.toLocaleDateString('en-PK');
    }

    function updateBMIPreview(card) {
        const height = parseFloat(card.querySelector('.height-input')?.value);
        const weight = parseFloat(card.querySelector('.weight-input')?.value);
        const bmiDisplay = card.querySelector('.bmi-display');
        
        if (height && weight && height > 0 && weight > 0) {
            const bmi = calculateBMI(height, weight);
            const category = getBMICategory(bmi);
            if (bmiDisplay) {
                bmiDisplay.innerHTML = `
                    <div class="bmi-value">${bmi}</div>
                    <div class="bmi-category ${category.class}">${category.text}</div>
                `;
            }
        } else {
            if (bmiDisplay) {
                bmiDisplay.innerHTML = `
                    <div class="bmi-value">—</div>
                    <div class="bmi-category" style="background:#6c757d;color:white;">Not calculated</div>
                `;
            }
        }
    }

    function updateStats() {
        const total = currentStudents.length;
        let complete = 0;
        let pending = 0;
        let bmiCalculated = 0;
        
        currentStudents.forEach(student => {
            if (student.date_of_birth && student.date_of_birth !== '0000-00-00') complete++;
            else pending++;
            if (student.height && student.weight && student.height > 0 && student.weight > 0) bmiCalculated++;
        });
        
        $('#totalStudents').text(total);
        $('#completeCount').text(complete);
        $('#pendingCount').text(pending);
        $('#bmiCount').text(bmiCalculated);
        $('#statsRow').show();
    }

    function filterStudents() {
        const searchTerm = $('#searchStudentInput').val().toLowerCase();
        $('.student-card').each(function() {
            const name = $(this).find('.student-name').text().toLowerCase();
            const regNo = $(this).find('.student-meta').text().toLowerCase();
            if (name.includes(searchTerm) || regNo.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    // ============================================
    // SAVE FUNCTIONALITY
    // ============================================
    
    async function saveStudent(studentId, data) {
        return new Promise((resolve, reject) => {
            const fd = new FormData();
            fd.append('student_id', studentId);
            fd.append('selected_fields[]', 'date_of_birth');
            fd.append('date_of_birth', data.date_of_birth || '');
            fd.append('height', data.height || '');
            fd.append('weight', data.weight || '');
            fd.append('db_status', data.db_status ? 1 : 0);
            fd.append('date_of_birth_age', data.date_of_birth_age || '');
            fd.append(CSRF_NAME, CSRF_HASH);

            $.ajax({
                url: URL_SAVE,
                type: "POST",
                data: fd,
                processData: false,
                contentType: false,
                dataType: "json",
                success: function(res, _status, xhr) {
                    const newToken = xhr.getResponseHeader && (xhr.getResponseHeader('X-CSRF-TOKEN') || xhr.getResponseHeader('X-CSRF-Token'));
                    if (newToken) CSRF_HASH = newToken;
                    
                    if (res && res.success) {
                        resolve(res);
                    } else {
                        reject(res?.msg || 'Save failed');
                    }
                },
                error: function(xhr) {
                    reject('AJAX error: ' + xhr.status);
                }
            });
        });
    }

    async function saveAllStudents() {
        $('#saveProgress').addClass('active');
        let saved = 0;
        let failed = 0;
        
        for (const student of currentStudents) {
            try {
                await saveStudent(student.student_id, {
                    date_of_birth: student.date_of_birth,
                    height: student.height,
                    weight: student.weight,
                    db_status: student.db_status,
                    date_of_birth_age: student.date_of_birth_age
                });
                saved++;
                $('#saveProgress span').text(`Saving... ${saved}/${currentStudents.length}`);
            } catch(e) {
                failed++;
                console.error(`Failed to save student ${student.student_id}:`, e);
            }
        }
        
        $('#saveProgress').removeClass('active');
        
        if (failed === 0) {
            toastr.success(`Successfully saved ${saved} student records`);
            loadStudentsByClass();
        } else {
            toastr.warning(`Saved ${saved} records, ${failed} failed`);
        }
    }

    // ============================================
    // RENDER FUNCTIONS
    // ============================================
    
    function renderStudentCard(student, index) {
        const hasValidDOB = student.date_of_birth && student.date_of_birth !== '0000-00-00';
        const bmiPreview = (student.height && student.weight) ? calculateBMI(student.height, student.weight) : null;
        const bmiCategory = bmiPreview ? getBMICategory(bmiPreview) : null;
        
        return `
            <div class="student-card" data-student-id="${student.student_id}" data-index="${index}">
                <div class="student-card-header" onclick="toggleCard(this)">
                    <div class="student-info">
                        <div class="student-avatar">
                            ${(student.first_name?.charAt(0) || 'S')}${(student.last_name?.charAt(0) || '')}
                        </div>
                        <div class="student-details">
                            <div class="student-name">${escapeHtml(student.first_name || '')} ${escapeHtml(student.last_name || '')}</div>
                            <div class="student-meta">
                                <i class="fas fa-id-card"></i> ${student.reg_no || 'No Reg'} &nbsp;|&nbsp;
                                <i class="fas fa-graduation-cap"></i> ${student.class_name || 'N/A'} ${student.section_name ? '-' + student.section_name : ''}
                            </div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <span class="status-badge ${hasValidDOB ? 'active' : 'pending'}">
                            <i class="fas ${hasValidDOB ? 'fa-check-circle' : 'fa-clock'}"></i>
                            ${hasValidDOB ? 'DOB Set' : 'Pending'}
                        </span>
                        <button class="btn btn-sm btn-primary save-student-btn" onclick="event.stopPropagation(); saveSingleStudent(${student.student_id})">
                            <i class="fas fa-save"></i> Save
                        </button>
                    </div>
                </div>
                <div class="student-card-body">
                    <div class="student-card-body-inner">
                        <div class="form-grid">
                            <div class="form-field">
                                <label><i class="fas fa-calendar"></i> Date of Birth</label>
                                <input type="date" class="form-control dob-input" 
                                       value="${student.date_of_birth || ''}"
                                       data-student-id="${student.student_id}">
                            </div>
                            <div class="form-field">
                                <label><i class="fas fa-ruler"></i> Height (cm)</label>
                                <input type="number" step="0.1" class="form-control height-input" 
                                       value="${student.height || ''}"
                                       data-student-id="${student.student_id}"
                                       placeholder="e.g., 150.5">
                            </div>
                            <div class="form-field">
                                <label><i class="fas fa-weight"></i> Weight (kg)</label>
                                <input type="number" step="0.1" class="form-control weight-input" 
                                       value="${student.weight || ''}"
                                       data-student-id="${student.student_id}"
                                       placeholder="e.g., 45.5">
                            </div>
                            <div class="form-field">
                                <label><i class="fas fa-heartbeat"></i> BMI Result</label>
                                <div class="bmi-display">
                                    ${bmiPreview ? `
                                        <div class="bmi-value">${bmiPreview}</div>
                                        <div class="bmi-category ${bmiCategory.class}">${bmiCategory.text}</div>
                                    ` : `
                                        <div class="bmi-value">—</div>
                                        <div class="bmi-category" style="background:#6c757d;color:white;">Enter height & weight</div>
                                    `}
                                </div>
                            </div>
                            <div class="form-field">
                                <label><i class="fas fa-database"></i> DB Status</label>
                                <div class="d-flex align-items-center gap-3">
                                    <label class="toggle-switch">
                                        <input type="checkbox" class="db-status-toggle" ${student.db_status == 1 ? 'checked' : ''}>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <span class="text-muted small">Use custom DOB age</span>
                                </div>
                            </div>
                            <div class="form-field">
                                <label><i class="fas fa-clock"></i> Actual DOB (if different)</label>
                                <input type="date" class="form-control dob-age-input" 
                                       value="${student.date_of_birth_age || ''}"
                                       ${student.db_status != 1 ? 'disabled' : ''}
                                       placeholder="Enter actual DOB for age calculation">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function renderStudents(students) {
        currentStudents = students;
        if (!students || students.length === 0) {
            $('#studentsList').html(`
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h5>No Students Found</h5>
                    <p class="text-muted">Select a class to view students</p>
                </div>
            `);
            $('#statsRow').hide();
            return;
        }
        
        let html = '';
        students.forEach((student, index) => {
            html += renderStudentCard(student, index);
        });
        $('#studentsList').html(html);
        updateStats();
        attachEventListeners();
    }

    // ============================================
    // EVENT HANDLERS
    // ============================================
    
    function attachEventListeners() {
        // DOB change
        $('.dob-input').off('change').on('change', function() {
            const studentId = $(this).data('student-id');
            const student = currentStudents.find(s => s.student_id == studentId);
            if (student) {
                student.date_of_birth = $(this).val();
            }
        });
        
        // Height change
        $('.height-input').off('input').on('input', function() {
            const studentId = $(this).data('student-id');
            const student = currentStudents.find(s => s.student_id == studentId);
            if (student) {
                student.height = $(this).val();
                updateBMIPreview($(this).closest('.student-card')[0]);
            }
        });
        
        // Weight change
        $('.weight-input').off('input').on('input', function() {
            const studentId = $(this).data('student-id');
            const student = currentStudents.find(s => s.student_id == studentId);
            if (student) {
                student.weight = $(this).val();
                updateBMIPreview($(this).closest('.student-card')[0]);
            }
        });
        
        // DB Status toggle
        $('.db-status-toggle').off('change').on('change', function() {
            const $card = $(this).closest('.student-card');
            const studentId = $card.data('student-id');
            const student = currentStudents.find(s => s.student_id == studentId);
            const isChecked = $(this).is(':checked');
            
            if (student) {
                student.db_status = isChecked ? 1 : 0;
            }
            
            $card.find('.dob-age-input').prop('disabled', !isChecked);
            if (!isChecked) {
                $card.find('.dob-age-input').val('');
                if (student) student.date_of_birth_age = '';
            }
        });
        
        // DOB Age input
        $('.dob-age-input').off('change').on('change', function() {
            const $card = $(this).closest('.student-card');
            const studentId = $card.data('student-id');
            const student = currentStudents.find(s => s.student_id == studentId);
            if (student) {
                student.date_of_birth_age = $(this).val();
            }
        });
    }
    
    // Global functions for onclick handlers
    window.toggleCard = function(element) {
        const card = $(element).closest('.student-card');
        card.toggleClass('active');
    };
    
    window.saveSingleStudent = async function(studentId) {
        const student = currentStudents.find(s => s.student_id == studentId);
        if (!student) return;
        
        $('#saveProgress').addClass('active');
        $('#saveProgress span').text('Saving...');
        
        try {
            await saveStudent(studentId, {
                date_of_birth: student.date_of_birth,
                height: student.height,
                weight: student.weight,
                db_status: student.db_status,
                date_of_birth_age: student.date_of_birth_age
            });
            toastr.success('Student information saved successfully');
            loadStudentsByClass(); // Reload to refresh
        } catch(e) {
            toastr.error('Failed to save: ' + e);
        } finally {
            $('#saveProgress').removeClass('active');
        }
    };
    
    window.saveAllStudents = saveAllStudents;

    // ============================================
    // DATA LOADING
    // ============================================
    
   function loadStudentsByClass() {
    const clsSecId = $('#cls_sec_id').val();
    
    // If no class selected, show empty state and return
    if (!clsSecId || clsSecId === '0' || clsSecId === '') {
        $('#studentsList').html(`
            <div class="empty-state">
                <i class="fas fa-graduation-cap"></i>
                <h5>Select a Class</h5>
                <p class="text-muted">Please select a class section to view students</p>
            </div>
        `);
        $('#statsRow').hide();
        $('#loaderContainer').hide();
        return;
    }
    
    $('#loaderContainer').show();
    $('#studentsList').html('');
    $('#statsRow').hide();
    
    $.ajax({
        url: URL_DATA,
        type: "POST",
        data: {
            cls_sec_id: clsSecId,
            [CSRF_NAME]: CSRF_HASH
        },
        dataType: "json",
        success: function(response, _status, xhr) {
            $('#loaderContainer').hide();
            
            const newToken = xhr.getResponseHeader && (xhr.getResponseHeader('X-CSRF-TOKEN') || xhr.getResponseHeader('X-CSRF-Token'));
            if (newToken) CSRF_HASH = newToken;
            
            if (response && response.success) {
                if (response.data && response.data.length > 0) {
                    renderStudents(response.data);
                } else {
                    $('#studentsList').html(`
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <h5>No Students Found</h5>
                            <p class="text-muted">${response.msg || 'No students found in this class'}</p>
                        </div>
                    `);
                    $('#statsRow').hide();
                }
            } else {
                $('#studentsList').html(`
                    <div class="empty-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h5>Error Loading Data</h5>
                        <p class="text-muted">${response?.msg || 'Failed to load students'}</p>
                        <button class="btn btn-primary mt-3" onclick="loadStudentsByClass()">Retry</button>
                    </div>
                `);
                $('#statsRow').hide();
            }
        },
        error: function(xhr) {
            $('#loaderContainer').hide();
            console.error('AJAX Error:', xhr);
            let errorMsg = 'Failed to load students';
            try {
                const res = JSON.parse(xhr.responseText);
                if (res && res.msg) errorMsg = res.msg;
            } catch(e) {}
            $('#studentsList').html(`
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h5>Error Loading Data</h5>
                    <p class="text-muted">${errorMsg}</p>
                    <button class="btn btn-primary mt-3" onclick="loadStudentsByClass()">Retry</button>
                </div>
            `);
            $('#statsRow').hide();
        }
    });
}

   // ============================================
// INITIALIZATION - MODIFIED
// ============================================

$(function() {
    // Load on class section change only (NOT on page load)
    $('#cls_sec_id').on('change', function() {
        loadStudentsByClass();
    });
    
    $('#searchStudentInput').on('keyup', filterStudents);
    $('#expandAllBtn').on('click', () => $('.student-card').addClass('active'));
    $('#collapseAllBtn').on('click', () => $('.student-card').removeClass('active'));
    
    // Add Save All button to filter bar if not already there
    if ($('.filter-actions .btn-success').length === 0) {
        $('.filter-actions').append(`
            <button class="btn btn-success btn-block mt-2" onclick="saveAllStudents()">
                <i class="fas fa-save"></i> Save All
            </button>
        `);
    }
    
    // REMOVE the automatic load on page load
    // Only show empty state message
    $('#studentsList').html(`
        <div class="empty-state">
            <i class="fas fa-graduation-cap"></i>
            <h5>Select a Class</h5>
            <p class="text-muted">Please select a class section from the dropdown above to view students</p>
        </div>
    `);
    $('#statsRow').hide();
    $('#loaderContainer').hide();
});
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
})();
</script>

<?= $this->endSection() ?>