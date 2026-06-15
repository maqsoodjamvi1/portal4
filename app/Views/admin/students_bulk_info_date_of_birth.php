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
        border-start: 4px solid var(--primary-color);
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
        position: relative;
        flex-shrink: 0;
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
        overflow: hidden;
    }
    .student-avatar--has-photo img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        z-index: 1;
    }
    .student-avatar--has-photo .student-avatar-initials {
        display: none;
        position: absolute;
        inset: 0;
        z-index: 2;
        align-items: center;
        justify-content: center;
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
        max-height: 1400px;
        transition: max-height 0.5s ease-in;
    }
    .student-card-body-inner {
        padding: 20px;
        background: white;
    }
    
    /* Form layout: row 1 = both DOBs; row 2 = weight+toggle | height | BMI */
    .form-grid {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .row-dob,
    .row-metrics {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
        align-items: start;
    }
    @media (min-width: 768px) {
        .row-dob {
            grid-template-columns: 1fr 1fr;
        }
        .row-metrics {
            grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr) minmax(0, 1fr);
        }
    }
    .form-field-weight-db .db-toggle-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #eee;
    }
    .form-field-weight-db .db-toggle-row .text-muted {
        font-size: 12px;
        line-height: 1.3;
        flex: 1;
        min-width: 120px;
    }

    /* Profile photo row — stack on small screens, row on larger */
    .row-photo {
        width: 100%;
    }
    .photo-panel {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 14px;
        padding: 14px;
        background: #f8f9fa;
        border-radius: 10px;
        border: 1px solid #e9ecef;
    }
    @media (min-width: 576px) {
        .photo-panel {
            flex-direction: row;
            align-items: center;
        }
    }
    .photo-thumb {
        flex-shrink: 0;
        align-self: center;
    }
    .photo-thumb-inner {
        width: 88px;
        height: 88px;
        border-radius: 50%;
        overflow: hidden;
        background: #fff;
        border: 2px solid #dee2e6;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .photo-thumb-inner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .photo-thumb-placeholder {
        color: #adb5bd;
        font-size: 32px;
    }
    .photo-actions {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .photo-actions-row {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    @media (min-width: 400px) {
        .photo-actions-row {
            flex-direction: row;
            flex-wrap: wrap;
        }
    }
    .photo-actions .btn {
        min-height: 44px;
        font-size: 14px;
    }
    .photo-hint {
        line-height: 1.35;
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
    
    /* BMI — compact inline strip (same visual weight as inputs) */
    .bmi-display {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-start;
        gap: 8px 10px;
        min-height: 42px;
        padding: 8px 12px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        text-align: left;
    }
    .bmi-value {
        font-size: 15px;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        line-height: 1.2;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .bmi-category {
        font-size: 11px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 6px;
        display: inline-block;
        line-height: 1.2;
        white-space: nowrap;
    }
    .bmi-display .age-preview {
        display: inline-flex;
        align-items: baseline;
        flex-wrap: wrap;
        gap: 5px;
        font-size: 14px;
        color: #343a40;
        max-width: 100%;
    }
    .bmi-display .age-preview .age-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6c757d;
    }
    .bmi-display .age-preview strong.age-value {
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        white-space: normal;
        line-height: 1.25;
    }
    .bmi-display .age-preview--empty strong {
        color: #adb5bd;
        font-weight: 600;
    }
    .bmi-display .metrics-dot {
        color: #ced4da;
        font-weight: 700;
        font-size: 16px;
        line-height: 1;
        padding: 0 1px;
        user-select: none;
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
    
    /* Brief save notices (replaces noisy Toastr on this page) */
    #dobBulkSnackbar {
        position: fixed;
        top: 72px;
        right: 16px;
        z-index: 1000000;
        max-width: min(360px, calc(100vw - 32px));
        pointer-events: none;
    }
    #dobBulkSnackbar .dob-snack {
        pointer-events: auto;
        margin-bottom: 8px;
        padding: 10px 14px;
        border-radius: 8px;
        font-size: 14px;
        line-height: 1.35;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
        border: 1px solid rgba(0, 0, 0, 0.06);
        animation: dobSnackIn 0.2s ease-out;
    }
    @keyframes dobSnackIn {
        from { opacity: 0; transform: translateY(-6px); }
        to { opacity: 1; transform: translateY(0); }
    }
    #dobBulkSnackbar .dob-snack-out {
        opacity: 0;
        transform: translateY(-4px);
        transition: opacity 0.2s ease, transform 0.2s ease;
    }
    #dobBulkSnackbar .dob-snack-success {
        background: #fff;
        color: #155724;
        border-start: 4px solid #28a745;
    }
    #dobBulkSnackbar .dob-snack-warning {
        background: #fff;
        color: #856404;
        border-start: 4px solid #ffc107;
    }
    #dobBulkSnackbar .dob-snack-error {
        background: #fff;
        color: #721c24;
        border-start: 4px solid #dc3545;
    }
    
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
                            <button class="btn btn-primary w-100" id="expandAllBtn">
                                <i class="fas fa-expand-alt"></i> Expand All
                            </button>
                            <button class="btn btn-secondary w-100 mt-2" id="collapseAllBtn">
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
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading students...</p>
                    </div>
                    <div id="studentsList"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Lightweight notices (single-line, auto-dismiss) -->
<div id="dobBulkSnackbar" aria-live="polite" aria-atomic="true"></div>

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
    const PHOTO_UPLOAD_BASE = "<?= rtrim(base_url('uploads'), '/') ?>/";
    const CSRF_NAME = "<?= csrf_token() ?>";
    let CSRF_HASH = "<?= csrf_hash() ?>";

    // State
    let currentStudents = [];
    let saveQueue = [];
    let isSaving = false;
    const pendingPhotos = new Map();       // student_id -> File (not yet saved)
    const photoPreviewUrls = new Map();    // student_id -> object URL for preview
    const MAX_PHOTO_BYTES = 4 * 1024 * 1024;
    const PHOTO_MIME_OK = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

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

    /** { years, months } completed calendar months after birth date; null if invalid / future. */
    function ageYearsMonthsFromYmd(ymd) {
        if (!ymd || ymd === '0000-00-00') return null;
        const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(String(ymd).trim());
        if (!m) return null;
        const y = parseInt(m[1], 10), mo = parseInt(m[2], 10) - 1, d = parseInt(m[3], 10);
        const birth = new Date(y, mo, d);
        if (birth.getFullYear() !== y || birth.getMonth() !== mo || birth.getDate() !== d) return null;
        const today = new Date();
        if (birth.getTime() > today.getTime()) return null;

        let years = today.getFullYear() - birth.getFullYear();
        let months = today.getMonth() - birth.getMonth();
        if (today.getDate() < birth.getDate()) {
            months--;
        }
        while (months < 0) {
            years--;
            months += 12;
        }
        if (years < 0 || years > 120) return null;
        return { years: years, months: months };
    }

    /** e.g. "8 Yr, 6 M" / "6 M" / "8 Yr" / "&lt; 1 M" */
    function formatAgeYmLabel(ymd) {
        const yma = ageYearsMonthsFromYmd(ymd);
        if (!yma) return null;
        const yr = yma.years;
        const mo = yma.months;
        const parts = [];
        if (yr > 0) {
            parts.push(yr + ' Yr');
        }
        if (mo > 0) {
            parts.push(mo + ' M');
        }
        if (parts.length > 0) {
            return parts.join(', ');
        }
        return '< 1 M';
    }

    /** Same rule as save payload: custom age DOB when toggle on, else main DOB. */
    function effectiveDobYmdFromStudent(student) {
        if (!student) return null;
        const dbOn = Number(student.db_status) === 1;
        const alt = student.date_of_birth_age;
        const main = student.date_of_birth;
        if (dbOn && alt && alt !== '0000-00-00') return alt;
        if (main && main !== '0000-00-00') return main;
        return null;
    }

    function effectiveDobYmdFromCard(card) {
        if (!card) return null;
        const dbOn = card.querySelector('.db-status-toggle')?.checked;
        const alt = (card.querySelector('.dob-age-input')?.value || '').trim();
        const main = (card.querySelector('.dob-input')?.value || '').trim();
        if (dbOn && alt) return alt;
        return main || null;
    }

    function buildAgeBmiStripHtml(ageText, bmi, category) {
        const ageSafe = (ageText != null && String(ageText).trim() !== '') ? escapeHtml(String(ageText).trim()) : '';
        const ageBlock = ageSafe
            ? '<span class="age-preview"><span class="age-label">Age</span><strong class="age-value">' + ageSafe + '</strong></span>'
            : '<span class="age-preview age-preview--empty"><span class="age-label">Age</span><strong class="age-value">—</strong></span>';
        const dot = '<span class="metrics-dot" aria-hidden="true">·</span>';
        let bmiBlock;
        if (bmi != null && category) {
            bmiBlock = '<span class="bmi-value">' + bmi + '</span>' +
                '<span class="bmi-category ' + category.class + '">' + category.text + '</span>';
        } else {
            bmiBlock = '<span class="bmi-value">—</span>' +
                '<span class="bmi-category" style="background:#6c757d;color:white;">Add height &amp; weight</span>';
        }
        return ageBlock + ' ' + dot + ' ' + bmiBlock;
    }

    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;
        return date.toLocaleDateString('en-PK');
    }

    /** Short, calm feedback without Toastr’s large green / icon styling */
    function showBriefNotice(message, variant) {
        const root = document.getElementById('dobBulkSnackbar');
        if (!root || !message) return;
        const el = document.createElement('div');
        const v = variant === 'error' ? 'error' : (variant === 'warning' ? 'warning' : 'success');
        el.className = 'dob-snack dob-snack-' + v;
        el.textContent = message;
        root.appendChild(el);
        const ms = v === 'error' ? 3200 : 2000;
        setTimeout(function() {
            el.classList.add('dob-snack-out');
            setTimeout(function() { el.remove(); }, 220);
        }, ms);
    }

    function updateAgeBmiStrip(card) {
        if (!card) return;
        const bmiDisplay = card.querySelector('.bmi-display');
        if (!bmiDisplay) return;
        const dobYmd = effectiveDobYmdFromCard(card);
        const ageText = dobYmd ? formatAgeYmLabel(dobYmd) : null;
        const height = parseFloat(card.querySelector('.height-input')?.value);
        const weight = parseFloat(card.querySelector('.weight-input')?.value);
        let bmi = null;
        let category = null;
        if (height && weight && height > 0 && weight > 0) {
            bmi = calculateBMI(height, weight);
            category = getBMICategory(bmi);
        }
        bmiDisplay.innerHTML = buildAgeBmiStripHtml(ageText, bmi, category);
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
            if (data.profilePhotoFile instanceof File) {
                fd.append('profile_photo', data.profilePhotoFile, data.profilePhotoFile.name);
            }
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
                const res = await saveStudent(student.student_id, {
                    date_of_birth: student.date_of_birth,
                    height: student.height,
                    weight: student.weight,
                    db_status: student.db_status,
                    date_of_birth_age: student.date_of_birth_age,
                    profilePhotoFile: pendingPhotos.get(student.student_id) || null
                });
                if (res && res.data && typeof res.data.profile_photo === 'string') {
                    student.profile_photo = res.data.profile_photo;
                }
                clearPendingPhotoForStudent(student.student_id);
                saved++;
                $('#saveProgress span').text(`Saving... ${saved}/${currentStudents.length}`);
            } catch(e) {
                failed++;
                console.error(`Failed to save student ${student.student_id}:`, e);
            }
        }
        
        $('#saveProgress').removeClass('active');
        
        if (failed === 0) {
            showBriefNotice(saved === 1 ? 'Saved.' : 'Saved ' + saved + ' students.');
            loadStudentsByClass();
        } else {
            showBriefNotice('Saved ' + saved + ', ' + failed + ' failed.', 'warning');
        }
    }

    // ============================================
    // RENDER FUNCTIONS
    // ============================================

    function studentProfilePhotoSrc(student) {
        let photo = (student && student.profile_photo != null ? String(student.profile_photo) : '').trim().replace(/^[/\\]+/, '');
        if (!photo || photo.indexOf('..') !== -1) {
            return null;
        }
        const path = photo.split('/').map(function(part) { return encodeURIComponent(part); }).join('/');
        return PHOTO_UPLOAD_BASE + path;
    }

    /** previewBlobUrl: local object URL before save */
    function studentAvatarHtml(student, previewBlobUrl) {
        const rawInitials = (student.first_name?.charAt(0) || 'S') + (student.last_name?.charAt(0) || '');
        const initials = escapeHtml(rawInitials);
        if (previewBlobUrl) {
            return '<div class="student-avatar student-avatar--has-photo">' +
                '<img src="' + escapeHtml(previewBlobUrl) + '" alt="" decoding="async">' +
                '<span class="student-avatar-initials" aria-hidden="true" style="display:none">' + initials + '</span>' +
                '</div>';
        }
        const src = studentProfilePhotoSrc(student);
        if (src) {
            return '<div class="student-avatar student-avatar--has-photo">' +
                '<img src="' + escapeHtml(src) + '" alt="" loading="lazy" decoding="async" ' +
                'onerror="this.style.visibility=\'hidden\';var el=this.parentNode.querySelector(\'.student-avatar-initials\');if(el){el.style.display=\'flex\';}">' +
                '<span class="student-avatar-initials" aria-hidden="true" style="display:none">' + initials + '</span>' +
                '</div>';
        }
        return '<div class="student-avatar"><span class="student-avatar-initials">' + initials + '</span></div>';
    }

    function buildPhotoThumbInner(student, previewBlobUrl) {
        if (previewBlobUrl) {
            return '<img src="' + escapeHtml(previewBlobUrl) + '" alt="">';
        }
        const src = studentProfilePhotoSrc(student);
        if (src) {
            return '<img src="' + escapeHtml(src) + '" alt="" loading="lazy" decoding="async" ' +
                'onerror="this.parentNode.innerHTML=\'<span class=\\\'photo-thumb-placeholder\\\'><i class=\\\'fas fa-user\\\'></i></span>\';">';
        }
        return '<span class="photo-thumb-placeholder"><i class="fas fa-user"></i></span>';
    }

    function clearPendingPhotoForStudent(studentId) {
        const sid = Number(studentId);
        if (photoPreviewUrls.has(sid)) {
            try {
                URL.revokeObjectURL(photoPreviewUrls.get(sid));
            } catch (e) { /* ignore */ }
            photoPreviewUrls.delete(sid);
        }
        pendingPhotos.delete(sid);
    }

    function refreshCardPhotos(card, student) {
        if (!card || !student) return;
        const sid = Number(student.student_id);
        const blobUrl = photoPreviewUrls.get(sid) || null;
        const av = card.querySelector('.student-info .student-avatar');
        if (av) {
            av.outerHTML = studentAvatarHtml(student, blobUrl);
        }
        const inner = card.querySelector('.photo-panel .photo-thumb-inner');
        if (inner) {
            inner.innerHTML = buildPhotoThumbInner(student, blobUrl);
        }
        const clearBtn = card.querySelector('.js-photo-clear');
        if (clearBtn) {
            if (pendingPhotos.has(sid)) {
                clearBtn.classList.remove('d-none');
            } else {
                clearBtn.classList.add('d-none');
            }
        }
    }

    function initPhotoDelegation() {
        const $root = $('#studentsListContainer');
        $root.off('click.dobPhoto').on('click.dobPhoto', '.js-photo-cam', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).closest('.photo-panel').find('.js-photo-input-camera').trigger('click');
        });
        $root.off('click.dobPhoto2').on('click.dobPhoto2', '.js-photo-pick', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).closest('.photo-panel').find('.js-photo-input-pick').trigger('click');
        });
        $root.off('click.dobPhoto3').on('click.dobPhoto3', '.js-photo-clear', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const panel = $(this).closest('.photo-panel')[0];
            if (!panel) return;
            const sid = Number(panel.getAttribute('data-student-id'));
            const student = currentStudents.find(function(s) { return Number(s.student_id) === sid; });
            clearPendingPhotoForStudent(sid);
            const card = $(panel).closest('.student-card')[0];
            if (card && student) {
                refreshCardPhotos(card, student);
            }
        });
        $root.off('change.dobPhoto').on('change.dobPhoto', '.js-photo-input-camera, .js-photo-input-pick', function() {
            const input = this;
            const file = input.files && input.files[0];
            const panel = $(input).closest('.photo-panel')[0];
            input.value = '';
            if (!file || !panel) return;
            if (file.size > MAX_PHOTO_BYTES) {
                showBriefNotice('Photo too large (max 4 MB).', 'error');
                return;
            }
            if (!PHOTO_MIME_OK.includes(file.type)) {
                showBriefNotice('Use JPG, PNG, or WebP only.', 'error');
                return;
            }
            const sid = Number(panel.getAttribute('data-student-id'));
            const student = currentStudents.find(function(s) { return Number(s.student_id) === sid; });
            if (!student) return;
            if (photoPreviewUrls.has(sid)) {
                try {
                    URL.revokeObjectURL(photoPreviewUrls.get(sid));
                } catch (e) { /* ignore */ }
                photoPreviewUrls.delete(sid);
            }
            const url = URL.createObjectURL(file);
            photoPreviewUrls.set(sid, url);
            pendingPhotos.set(sid, file);
            const card = $(panel).closest('.student-card')[0];
            refreshCardPhotos(card, student);
        });
    }

    function classSectionLabelHtml(student) {
        const cn = (student.class_name != null ? String(student.class_name) : '').trim();
        const sn = (student.section_name != null ? String(student.section_name) : '').trim();
        if (!cn && !sn) {
            return '—';
        }
        if (cn && sn) {
            return escapeHtml(cn) + ' - ' + escapeHtml(sn);
        }
        return escapeHtml(cn || sn);
    }
    
    function renderStudentCard(student, index) {
        const hasValidDOB = student.date_of_birth && student.date_of_birth !== '0000-00-00';
        const bmiPreview = (student.height && student.weight) ? calculateBMI(student.height, student.weight) : null;
        const bmiCategory = bmiPreview ? getBMICategory(bmiPreview) : null;
        const dobForAge = effectiveDobYmdFromStudent(student);
        const agePreview = dobForAge ? formatAgeYmLabel(dobForAge) : null;
        const ageBmiInner = buildAgeBmiStripHtml(agePreview, bmiPreview, bmiCategory);
        const classLineHtml = classSectionLabelHtml(student);
        
        return `
            <div class="student-card" data-student-id="${student.student_id}" data-index="${index}">
                <div class="student-card-header" onclick="toggleCard(this)">
                    <div class="student-info">
                        ${studentAvatarHtml(student)}
                        <div class="student-details">
                            <div class="student-name">${escapeHtml(student.first_name || '')} ${escapeHtml(student.last_name || '')}</div>
                            <div class="student-meta">
                                <i class="fas fa-id-card"></i> ${student.reg_no ? escapeHtml(String(student.reg_no)) : 'No Reg'} &nbsp;|&nbsp;
                                <i class="fas fa-graduation-cap"></i> ${classLineHtml}
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
                            <div class="row-dob">
                                <div class="form-field">
                                    <label><i class="fas fa-calendar"></i> Date of Birth</label>
                                    <input type="date" class="form-control dob-input"
                                           value="${student.date_of_birth || ''}"
                                           data-student-id="${student.student_id}">
                                </div>
                                <div class="form-field">
                                    <label><i class="fas fa-clock"></i> Actual DOB (if different)</label>
                                    <input type="date" class="form-control dob-age-input"
                                           value="${student.date_of_birth_age || ''}"
                                           ${student.db_status != 1 ? 'disabled' : ''}
                                           data-student-id="${student.student_id}">
                                </div>
                            </div>
                            <div class="row-metrics">
                                <div class="form-field form-field-weight-db">
                                    <label><i class="fas fa-weight"></i> Weight (kg)</label>
                                    <input type="number" step="0.1" class="form-control weight-input"
                                           value="${student.weight || ''}"
                                           data-student-id="${student.student_id}"
                                           placeholder="e.g., 45.5">
                                    <div class="db-toggle-row">
                                        <label class="toggle-switch" title="Use custom DOB for age display">
                                            <input type="checkbox" class="db-status-toggle" ${student.db_status == 1 ? 'checked' : ''}>
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span class="text-muted">Use custom DOB age</span>
                                    </div>
                                </div>
                                <div class="form-field">
                                    <label><i class="fas fa-ruler"></i> Height (cm)</label>
                                    <input type="number" step="0.1" class="form-control height-input"
                                           value="${student.height || ''}"
                                           data-student-id="${student.student_id}"
                                           placeholder="e.g., 150.5">
                                </div>
                                <div class="form-field">
                                    <label><i class="fas fa-heartbeat"></i> Age &amp; BMI</label>
                                    <div class="bmi-display">${ageBmiInner}</div>
                                </div>
                            </div>
                            <div class="row-photo">
                                <div class="form-field form-field-photo">
                                    <label><i class="fas fa-camera"></i> Profile photo</label>
                                    <div class="photo-panel" data-student-id="${student.student_id}">
                                        <div class="photo-thumb">
                                            <div class="photo-thumb-inner">${buildPhotoThumbInner(student, null)}</div>
                                        </div>
                                        <div class="photo-actions">
                                            <div class="photo-actions-row">
                                                <button type="button" class="btn btn-outline-primary js-photo-cam">
                                                    <i class="fas fa-camera"></i> Camera
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary js-photo-pick">
                                                    <i class="fas fa-images"></i> Gallery
                                                </button>
                                            </div>
                                            <button type="button" class="btn btn-outline-danger btn-sm js-photo-clear d-none">Remove unsaved photo</button>
                                            <input type="file" class="js-photo-input-camera d-none" accept="image/*" capture="environment" aria-label="Take photo with camera">
                                            <input type="file" class="js-photo-input-pick d-none" accept="image/jpeg,image/png,image/webp" aria-label="Choose photo from gallery">
                                            <p class="text-muted small mb-0 photo-hint">JPG, PNG or WebP — max 4 MB. Tap <strong>Save</strong> above to store with DOB, height &amp; weight.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function renderStudents(students) {
        currentStudents = students;
        photoPreviewUrls.forEach(function(url) {
            try {
                URL.revokeObjectURL(url);
            } catch (e) { /* ignore */ }
        });
        photoPreviewUrls.clear();
        pendingPhotos.clear();
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
            updateAgeBmiStrip($(this).closest('.student-card')[0]);
        });
        
        // Height change
        $('.height-input').off('input').on('input', function() {
            const studentId = $(this).data('student-id');
            const student = currentStudents.find(s => s.student_id == studentId);
            if (student) {
                student.height = $(this).val();
                updateAgeBmiStrip($(this).closest('.student-card')[0]);
            }
        });
        
        // Weight change
        $('.weight-input').off('input').on('input', function() {
            const studentId = $(this).data('student-id');
            const student = currentStudents.find(s => s.student_id == studentId);
            if (student) {
                student.weight = $(this).val();
                updateAgeBmiStrip($(this).closest('.student-card')[0]);
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
            updateAgeBmiStrip($card[0]);
        });
        
        // DOB Age input
        $('.dob-age-input').off('change').on('change', function() {
            const $card = $(this).closest('.student-card');
            const studentId = $card.data('student-id');
            const student = currentStudents.find(s => s.student_id == studentId);
            if (student) {
                student.date_of_birth_age = $(this).val();
            }
            updateAgeBmiStrip($card[0]);
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
        const card = document.querySelector('.student-card[data-student-id="' + studentId + '"]');
        
        $('#saveProgress').addClass('active');
        $('#saveProgress span').text('Saving...');
        
        try {
            const res = await saveStudent(studentId, {
                date_of_birth: student.date_of_birth,
                height: student.height,
                weight: student.weight,
                db_status: student.db_status,
                date_of_birth_age: student.date_of_birth_age,
                profilePhotoFile: pendingPhotos.get(studentId) || null
            });
            if (res && res.data && typeof res.data.profile_photo === 'string') {
                student.profile_photo = res.data.profile_photo;
            }
            clearPendingPhotoForStudent(studentId);
            if (card) {
                refreshCardPhotos(card, student);
            }
            showBriefNotice('Saved.');

        } catch(e) {
            showBriefNotice('Could not save. ' + (typeof e === 'string' ? e : 'Please try again.'), 'error');
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
    initPhotoDelegation();

    // Load on class section change only (NOT on page load)
    $('#cls_sec_id').on('change', function() {
        loadStudentsByClass();
    });
    
    $('#searchStudentInput').on('keyup', filterStudents);
    $('#expandAllBtn').on('click', () => $('.student-card').addClass('active'));
    $('#collapseAllBtn').on('click', () => $('.student-card').removeClass('active'));
    
    
    
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