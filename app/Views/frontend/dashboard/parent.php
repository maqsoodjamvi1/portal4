<?= $this->extend('layouts/master_portal') ?>
<?= $this->section('content') ?>

<?php
// Set currency symbol
$currency = 'Rs.';
if (isset($campusInfo) && is_object($campusInfo) && !empty($campusInfo->currency_code)) {
    $currency = $campusInfo->currency_code;
} elseif (isset($schoolInfo) && is_object($schoolInfo) && !empty($schoolInfo->currency_code)) {
    $currency = $schoolInfo->currency_code;
}
?>
<style>
    html, body {
        height: 100%;
    }
    
    .wrapper {
        min-height: 100vh;
    }

    /* ============================================
       SCHOOL HEADER STYLES
    ============================================ */
    .school-header {
        background: linear-gradient(135deg, #1e293b, #0f172a);
        border-radius: 20px;
        padding: 10px 15px;
        margin-bottom: 25px;
        color: white;
        display: flex;
        align-items: center;
    }

    .logo-wrapper {
        width: 80px;
        height: 80px;
        background: white;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .school-logo {
        width: 60px;
        height: 60px;
        object-fit: contain;
    }

    .school-logo-placeholder {
        width: 70px;
        height: 70px;
        border-radius: 15px;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .school-logo-placeholder i {
        color: #4f46e5 !important;
        font-size: 32px;
    }

    .school-name {
        font-size: 1.8rem;
        font-weight: 700;
    }

    /* ============================================
       FEE HISTORY SECTION
    ============================================ */
    .fee-history-table {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .fee-history-table table {
        margin-bottom: 0;
    }

    .fee-history-table th {
        background: #f8fafc;
        padding: 15px 10px;
        text-align: center;
        font-weight: 600;
        color: #475569;
    }

    .fee-history-table td {
        text-align: center;
        padding: 12px 10px;
        vertical-align: middle;
    }

    .unpaid-amount {
        background: #fef2f2;
        padding: 10px 15px;
        border-radius: 10px;
        margin-top: 10px;
    }

    /* ============================================
       KIDS SECTION
    ============================================ */
    .kids-scroll {
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 15px;
        margin-bottom: 10px;
        -webkit-overflow-scrolling: touch;
    }

    .kid-card {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        width: 100px;
        min-height: 140px;
        margin-right: 12px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        border-radius: 15px;
        padding: 12px 8px;
        background: white;
        border: 2px solid transparent;
        vertical-align: top;
    }

    .kid-card.active {
        border-color: #4f46e5;
        background: #f5f3ff;
        box-shadow: 0 5px 15px rgba(79, 70, 229, 0.2);
    }

    .kid-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .kid-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e2e8f0;
        margin-bottom: 8px;
        flex-shrink: 0;
    }

    .kid-avatar-placeholder {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 8px;
        flex-shrink: 0;
        border: 3px solid #e2e8f0;
    }

    .kid-avatar-placeholder span {
        font-size: 24px;
        font-weight: 600;
        color: white;
        text-transform: uppercase;
    }

    .kid-card.active .kid-avatar,
    .kid-card.active .kid-avatar-placeholder {
        border-color: #4f46e5;
    }

    .kid-name {
        font-weight: 600;
        font-size: 12px;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 90px;
        line-height: 1.3;
    }

    .kid-class {
        font-size: 10px;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 90px;
    }

    .kid-age {
        font-size: 9px;
        color: #10b981;
        margin-left: 3px;
    }

    .kid-card.active .kid-name {
        color: #4f46e5;
    }

    .kid-card.active .kid-class {
        color: #6b7280;
    }

    /* ============================================
       STICKY ACTIVE STUDENT BAR
    ============================================ */
    .active-student-sticky {
        position: sticky;
        top: 0;
        z-index: 100;
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        padding: 10px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* ============================================
       ATTENDANCE SECTION
    ============================================ */
    .attendance-grid {
        display: flex;
        gap: 8px;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .attendance-day {
        flex: 1;
        text-align: center;
        padding: 8px;
        background: #f8fafc;
        border-radius: 10px;
        min-width: 55px;
    }

    .attendance-status {
        font-size: 22px;
        font-weight: 700;
        padding: 6px;
        border-radius: 10px;
        margin-top: 5px;
    }

    .attendance-status.bg-warning {
        background-color: #f59e0b !important;
        color: white;
    }

    /* ============================================
       DIARY SECTION
    ============================================ */
    .diary-accordion {
        margin-bottom: 15px;
    }

    .diary-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 12px;
    }

    .diary-card-header {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: white;
        padding: 14px 18px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
    }

    .diary-card-header:hover {
        background: linear-gradient(135deg, #4338ca, #6d28d9);
    }

    .diary-card-header .toggle-icon {
        transition: transform 0.3s ease;
    }

    .diary-card-header.active .toggle-icon {
        transform: rotate(180deg);
    }

    .diary-card-body {
        display: none;
        padding: 0;
    }

    .diary-card-body.active {
        display: block;
    }

    .diary-section {
        padding: 15px 20px;
        border-bottom: 1px solid #f1f5f9;
    }

    .diary-section:last-child {
        border-bottom: none;
    }

    .section-title {
        font-weight: 600;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-title i {
        width: 24px;
    }

    /* Task Caption */
    .task-caption {
        background: #eef2ff;
        padding: 10px 15px;
        border-radius: 10px;
        margin: 10px 0;
        border-left: 4px solid #4f46e5;
    }

    .task-caption p {
        margin: 0;
        color: #1e293b;
    }

    /* Recording Section */
    .recording-compact {
        background: #f8fafc;
        padding: 10px 15px;
        border-radius: 10px;
        margin: 8px 0;
    }

    .recording-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 8px;
    }

    .recording-badge {
        font-size: 12px;
        padding: 3px 8px;
        border-radius: 20px;
    }

    .recording-controls-compact {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-icon-sm {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-icon-sm.btn-danger {
        background: #dc2626;
        color: white;
        border: none;
    }

    .btn-icon-sm.btn-success {
        background: #10b981;
        color: white;
        border: none;
    }

    .btn-icon-sm.btn-outline-primary {
        background: transparent;
        border: 1px solid #4f46e5;
        color: #4f46e5;
    }

    .btn-icon-sm:hover {
        transform: scale(1.05);
    }

    .existing-recording {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px;
        background: white;
        border-radius: 8px;
        margin-bottom: 8px;
        flex-wrap: wrap;
    }

    .existing-recording audio,
    .existing-recording video {
        max-width: 200px;
    }

    /* Quiz Button */
    .quiz-btn {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 13px;
        transition: all 0.3s ease;
    }

    .quiz-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
    }

    /* ============================================
       PRAYER TRACKING SECTION
    ============================================ */
    .prayer-card {
        margin-top: 15px;
        background: linear-gradient(135deg, #1e3a5f, #0f2b44);
        border-radius: 15px;
        overflow: hidden;
    }

    .prayer-header {
        background: #2d4a6e;
        color: white;
        padding: 12px 15px;
        cursor: pointer;
    }

    .prayer-body {
        padding: 15px;
        background: white;
    }

    .prayer-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: space-between;
    }

    .prayer-item {
        flex: 1;
        text-align: center;
        padding: 10px;
        background: #f8fafc;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        min-width: 60px;
    }

    .prayer-item.offered {
        background: #10b981;
        color: white;
    }

    .prayer-item.offered i {
        color: white;
    }

    .prayer-item i {
        font-size: 20px;
        color: #4f46e5;
        display: block;
        margin-bottom: 5px;
    }

    .prayer-item span {
        font-size: 11px;
        display: block;
    }

    .prayer-progress {
        margin-top: 15px;
    }

    .prayer-stats {
        display: flex;
        justify-content: space-between;
        margin-top: 15px;
        padding-top: 10px;
        border-top: 1px solid #e2e8f0;
    }

    .prayer-stat {
        text-align: center;
    }

    .prayer-stat .number {
        font-size: 18px;
        font-weight: bold;
        color: #4f46e5;
    }

    .prayer-stat .label {
        font-size: 10px;
        color: #64748b;
    }

    /* ============================================
       BMI SECTION
    ============================================ */
    .bmi-progress-container {
        position: relative;
        margin: 15px 0;
    }

    .bmi-indicator {
        position: absolute;
        top: -20px;
        transform: translateX(-50%);
        font-size: 12px;
        white-space: nowrap;
    }

    .bmi-category-normal {
        background-color: #28a745;
        color: white;
    }

    .bmi-category-underweight {
        background-color: #ffc107;
        color: #333;
    }

    .bmi-category-overweight {
        background-color: #17a2b8;
        color: white;
    }

    .bmi-category-obese {
        background-color: #dc3545;
        color: white;
    }

    /* ============================================
       CAMERA MODAL
    ============================================ */
    .camera-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .camera-modal-content {
        position: relative;
        width: 90%;
        max-width: 500px;
        background: #1a1a2e;
        border-radius: 20px;
        overflow: hidden;
    }

    .camera-preview {
        width: 100%;
        height: auto;
        background: #000;
    }

    .camera-controls {
        display: flex;
        gap: 15px;
        justify-content: center;
        padding: 15px;
        background: #1a1a2e;
    }

    .camera-btn {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: none;
        font-size: 24px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .camera-btn-capture {
        background: #4f46e5;
        color: white;
    }

    .camera-btn-close {
        background: #dc2626;
        color: white;
    }

    .camera-btn-switch {
        background: #475569;
        color: white;
    }

    /* ============================================
       RESPONSIVE DESIGN
    ============================================ */
    @media (max-width: 768px) {
        .school-name {
            font-size: 1.2rem;
        }

        .logo-wrapper {
            width: 55px;
            height: 55px;
        }

        .school-logo {
            width: 40px;
            height: 40px;
        }

        .attendance-grid {
            gap: 5px;
        }

        .attendance-day {
            padding: 5px;
            font-size: 12px;
            min-width: 45px;
        }

        .attendance-status {
            font-size: 16px;
            padding: 4px;
        }

        .kid-card {
            width: 85px;
            min-height: 125px;
            padding: 10px 5px;
            margin-right: 8px;
        }

        .kid-avatar,
        .kid-avatar-placeholder {
            width: 50px;
            height: 50px;
        }

        .kid-avatar-placeholder span {
            font-size: 20px;
        }

        .kid-name {
            font-size: 11px;
            max-width: 75px;
        }

        .kid-class {
            font-size: 9px;
            max-width: 75px;
        }

        .prayer-grid {
            gap: 5px;
        }

        .prayer-item {
            padding: 8px 5px;
            min-width: 50px;
        }

        .prayer-item i {
            font-size: 16px;
        }

        .prayer-item span {
            font-size: 9px;
        }

        .existing-recording audio,
        .existing-recording video {
            max-width: 100%;
        }
    }

    @media (max-width: 480px) {
        .school-name {
            font-size: 1rem;
        }

        .logo-wrapper {
            width: 45px;
            height: 45px;
            margin-right: 10px;
        }

        .school-logo {
            width: 35px;
            height: 35px;
        }

        .kid-card {
            width: 75px;
            min-height: 115px;
            padding: 8px 4px;
        }

        .kid-avatar,
        .kid-avatar-placeholder {
            width: 45px;
            height: 45px;
        }

        .kid-avatar-placeholder span {
            font-size: 18px;
        }

        .kid-name {
            font-size: 10px;
        }

        .kid-class {
            font-size: 8px;
        }

        .kid-age {
            font-size: 8px;
        }

        .active-student-sticky {
            font-size: 12px;
            padding: 8px 12px;
        }
    }
/* ============================================
   FEE HISTORY TABLE - COMPLETE STYLES
============================================ */

/* Table Wrapper */
.fee-table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* Fee History Table */
.fee-history-table {
    width: 100%;
    border-collapse: collapse;
    font-family: inherit;
    min-width: 450px;
}

.fee-history-table th,
.fee-history-table td {
    border: 1px solid #e2e8f0;
    padding: 6px 2px;
    text-align: center;
}

/* Header Styles */
.fee-history-table th {
    background-color: #f8fafc;
    font-weight: 600;
    font-size: 14px;
    color: #475569;
    padding: 6px 2px;
}

.fee-history-table .month-col {
    text-align: left;
    width: 55px;
    padding-left: 4px;
}

.fee-history-table .month-name {
    font-weight: 600;
    font-size: 14px;
    color: #1e293b;
}

/* Row Label Styles */
.fee-history-table .row-label {
    background-color: #f8fafc;
    font-weight: 600;
    font-size: 14px;
    color: #475569;
    text-align: left;
    padding-left: 4px;
}

.fee-history-table .total-label {
    background-color: #f1f5f9;
    font-weight: 700;
}

/* Amount Cell Styles */
.fee-history-table .amount-cell {
    font-size: 14px;
    padding: 5px 1px;
}

.amount-paid {
    color: #10b981;
    font-weight: 600;
    font-size: 14px;
}

.amount-other {
    color: #3b82f6;
    font-weight: 500;
    font-size: 14px;
}

.amount-total {
    color: #4f46e5;
    font-weight: 700;
    font-size: 14px;
}

.amount-na {
    color: #94a3b8;
    font-size: 14px;
}

.fee-history-table .total-cell {
    background-color: #f8fafc;
}

/* Balance Summary Cards */
.balance-summary {
    display: flex;
    justify-content: space-between;
    gap: 6px;
    padding: 8px 10px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

.balance-item {
    flex: 1;
    text-align: center;
    background: white;
    border-radius: 8px;
    padding: 5px 3px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.balance-label {
    font-size: 10px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 2px;
}

.balance-amount {
    font-size: 16px;
    font-weight: 700;
}

.balance-amount.monthly-bal {
    color: #dc2626;
}

.balance-amount.other-bal {
    color: #f59e0b;
}

.balance-amount.total-bal {
    color: #dc2626;
}

/* Paid Status */
.paid-status {
    text-align: center;
    padding: 10px;
    background: #ecfdf5;
    color: #059669;
    font-size: 13px;
    font-weight: 500;
    border-top: 1px solid #d1fae5;
}

.paid-status i {
    margin-right: 6px;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 30px 20px;
    color: #94a3b8;
}

.empty-state p {
    margin: 0;
    font-size: 13px;
}

/* ========== MOBILE RESPONSIVE ========== */
@media (max-width: 768px) {
    .fee-history-table {
        min-width: 380px;
    }
    
    .fee-history-table th,
    .fee-history-table td {
        padding: 5px 2px;
    }
    
    .fee-history-table th,
    .fee-history-table .month-name,
    .fee-history-table .row-label,
    .fee-history-table .amount-cell,
    .amount-paid,
    .amount-other,
    .amount-total,
    .amount-na {
        font-size: 13px;
    }
    
    .fee-history-table .month-col {
        width: 50px;
    }
    
    .balance-summary {
        padding: 6px 8px;
        gap: 5px;
    }
    
    .balance-item {
        padding: 4px 2px;
    }
    
    .balance-label {
        font-size: 9px;
    }
    
    .balance-amount {
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .fee-history-table {
        min-width: 340px;
    }
    
    .fee-history-table th,
    .fee-history-table td {
        padding: 4px 1px;
    }
    
    .fee-history-table th,
    .fee-history-table .month-name,
    .fee-history-table .row-label,
    .fee-history-table .amount-cell,
    .amount-paid,
    .amount-other,
    .amount-total,
    .amount-na {
        font-size: 12px;
    }
    
    .fee-history-table .month-col {
        width: 45px;
    }
    
    .balance-summary {
        padding: 5px 6px;
        gap: 4px;
    }
    
    .balance-item {
        padding: 3px 2px;
    }
    
    .balance-label {
        font-size: 12px;
    }
    
    .balance-amount {
        font-size: 15px;
    }
}

/* ============================================
   BMI CARDS - 4 IN A ROW
============================================ */
.bmi-card {
    border-radius: 10px;
    transition: all 0.2s ease;
}

.bmi-card-label {
    font-size: 14px;
    opacity: 0.8;
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.bmi-card-value {
    font-size: 24px;
    font-weight: 700;
    line-height: 1.2;
}

.bmi-card-unit {
    font-size: 12px;
    opacity: 0.7;
    margin-top: 2px;
}

/* Mobile specific */
@media (max-width: 768px) {
    .bmi-card-label {
        font-size: 9px;
    }
    
    .bmi-card-value {
        font-size: 16px;
    }
    
    .bmi-card-unit {
        font-size: 8px;
    }
}

@media (max-width: 480px) {
    .bmi-card-label {
        font-size: 12px;
    }
    
    .bmi-card-value {
        font-size: 18px;
    }
    
    .bmi-card-unit {
        font-size: 10px;
    }
}


/* BMI History Table */
.bmi-history-table th,
.bmi-history-table td {
    padding: 6px 4px;
    font-size: 11px;
}

.bmi-history-table .badge {
    font-size: 10px;
    padding: 3px 6px;
}

@media (max-width: 768px) {
    .bmi-history-table th,
    .bmi-history-table td {
        font-size: 10px;
        padding: 4px 2px;
    }
}

/* ============================================
   DIARY SECTION - IMPROVED SYMMETRY
============================================ */

/* Content Box for Class Work & Homework */
.content-box {
    background: #f8fafc;
    border-radius: 10px;
    padding: 12px;
    height: 100%;
}

.content-text {
    font-size: 13px;
    line-height: 1.5;
    color: #334155;
}

/* Tasks Section */
.tasks-title {
    font-weight: 600;
    margin-bottom: 15px;
    font-size: 14px;
    color: #1e293b;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 8px;
}

/* Task Box */
.task-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px;
    height: 100%;
    transition: all 0.2s ease;
}

.task-box.task-disabled {
    background: #f8fafc;
    opacity: 0.6;
}

.task-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    flex-wrap: wrap;
}

.task-header i {
    font-size: 16px;
}

.task-header span {
    font-weight: 600;
    font-size: 13px;
}

.task-badge {
    background: #10b981;
    color: white;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 20px;
    margin-left: auto;
}

.task-caption {
    background: #eef2ff;
    padding: 8px 10px;
    border-radius: 8px;
    font-size: 11px;
    color: #1e293b;
    margin-bottom: 10px;
    border-left: 3px solid #4f46e5;
}

/* Submissions */
.submission-item {
    background: #f1f5f9;
    border-radius: 8px;
    padding: 8px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.submission-audio {
    max-width: 120px;
    height: 35px;
}

.submission-video {
    max-width: 100%;
    max-height: 100px;
    border-radius: 6px;
}

.submission-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 6px;
}

.feedback-text {
    font-size: 10px;
    color: #64748b;
    display: block;
    margin-top: 4px;
}

/* Task Actions */
.task-actions {
    display: flex;
    gap: 8px;
    margin-top: 10px;
    flex-wrap: wrap;
}

.task-btn {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.task-btn:hover {
    background: #e2e8f0;
}

.audio-record-btn {
    background: #dc2626;
    border-color: #dc2626;
    color: white;
}

.audio-record-btn:hover {
    background: #b91c1c;
}

.video-record-btn {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

.video-record-btn:hover {
    background: #2563eb;
}

.picture-capture-btn {
    background: #10b981;
    border-color: #10b981;
    color: white;
}

.picture-capture-btn:hover {
    background: #059669;
}

.task-stop-btn {
    background: #ef4444;
    border-color: #ef4444;
    color: white;
}

.task-upload {
    background: #64748b;
    border-color: #64748b;
    color: white;
}

.task-upload:hover {
    background: #475569;
}

.task-not-required {
    color: #94a3b8;
    font-size: 11px;
    text-align: center;
    padding: 15px 0;
}

.upload-status,
.video-status,
.picture-status {
    font-size: 10px;
    margin-top: 6px;
}

.audio-timer {
    font-size: 11px;
    color: #64748b;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .task-header {
        flex-wrap: wrap;
    }
    
    .task-badge {
        margin-left: 0;
    }
    
    .task-actions {
        flex-direction: column;
    }
    
    .task-btn {
        width: 100%;
        justify-content: center;
    }
    
    .submission-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .submission-audio {
        max-width: 100%;
        width: 100%;
    }
}


/* ============================================
   VIDEO RECORDING MODAL - PROFESSIONAL STYLE
============================================ */
.video-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.85);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(5px);
}

.video-modal-content {
    background: #1e1e2e;
    border-radius: 16px;
    width: 90%;
    max-width: 500px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    animation: modalFadeIn 0.3s ease;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.video-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background: #2d2d3a;
    border-bottom: 1px solid #3d3d4a;
}

.video-modal-title {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #fff;
}

.video-modal-title i {
    margin-right: 8px;
    color: #3b82f6;
}

.video-modal-close {
    background: none;
    border: none;
    color: #a0a0b0;
    font-size: 20px;
    cursor: pointer;
    padding: 5px;
    transition: all 0.2s;
}

.video-modal-close:hover {
    color: #fff;
}

.video-modal-body {
    padding: 20px;
}

.video-preview-container {
    position: relative;
    background: #000;
    border-radius: 12px;
    overflow: hidden;
    aspect-ratio: 16 / 9;
}

.video-preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
    background: #000;
}

.video-recording-indicator {
    position: absolute;
    top: 12px;
    left: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(0, 0, 0, 0.7);
    padding: 6px 12px;
    border-radius: 30px;
    backdrop-filter: blur(4px);
}

.recording-dot {
    width: 10px;
    height: 10px;
    background: #ef4444;
    border-radius: 50%;
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.5;
        transform: scale(1.2);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}

.recording-text {
    font-size: 11px;
    font-weight: 600;
    color: #ef4444;
    letter-spacing: 1px;
}

.recording-timer {
    font-size: 11px;
    font-weight: 500;
    color: #fff;
    background: rgba(0, 0, 0, 0.5);
    padding: 2px 6px;
    border-radius: 12px;
    font-family: monospace;
}

.video-modal-footer {
    display: flex;
    justify-content: center;
    gap: 15px;
    padding: 15px 20px;
    background: #2d2d3a;
    border-top: 1px solid #3d3d4a;
}

.video-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 24px;
    border: none;
    border-radius: 30px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    min-width: 100px;
}

.video-btn i {
    font-size: 14px;
}

.video-btn-record {
    background: #ef4444;
    color: white;
}

.video-btn-record:hover {
    background: #dc2626;
    transform: scale(1.02);
}

.video-btn-stop {
    background: #3b82f6;
    color: white;
}

.video-btn-stop:hover {
    background: #2563eb;
}

.video-btn-close {
    background: #4a4a5a;
    color: #e0e0e0;
}

.video-btn-close:hover {
    background: #5a5a6a;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .video-modal-content {
        width: 95%;
        max-width: 95%;
    }
    
    .video-modal-header {
        padding: 12px 15px;
    }
    
    .video-modal-title {
        font-size: 14px;
    }
    
    .video-modal-body {
        padding: 15px;
    }
    
    .video-btn {
        padding: 8px 16px;
        font-size: 12px;
        min-width: 80px;
    }
    
    .video-btn i {
        font-size: 12px;
    }
}


/* ============================================
   PICTURE CAPTURE MODAL - PROFESSIONAL STYLE
============================================ */
.picture-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(5px);
}

.picture-modal-content {
    background: #1e1e2e;
    border-radius: 20px;
    width: 90%;
    max-width: 450px;
    overflow: hidden;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
    animation: modalSlideUp 0.3s ease;
}

@keyframes modalSlideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.picture-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background: #2d2d3a;
    border-bottom: 1px solid #3d3d4a;
}

.picture-modal-title {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #fff;
}

.picture-modal-title i {
    margin-right: 8px;
    color: #10b981;
}

.picture-modal-close {
    background: none;
    border: none;
    color: #a0a0b0;
    font-size: 20px;
    cursor: pointer;
    padding: 5px;
    transition: all 0.2s;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.picture-modal-close:hover {
    background: #3d3d4a;
    color: #fff;
}

.picture-modal-body {
    padding: 20px;
}

.camera-preview-container {
    position: relative;
    background: #000;
    border-radius: 16px;
    overflow: hidden;
    aspect-ratio: 4 / 3;
}

.camera-preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
    background: #000;
}

.camera-guide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.guide-frame {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 80%;
    height: 80%;
    border: 2px dashed rgba(255, 255, 255, 0.3);
    border-radius: 12px;
}

/* Captured Preview */
.captured-preview {
    background: #1a1a2a;
    border-radius: 16px;
    overflow: hidden;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.captured-image {
    width: 100%;
    height: auto;
    display: block;
    max-height: 350px;
    object-fit: contain;
    background: #0d0d1a;
}

.captured-actions {
    display: flex;
    gap: 15px;
    padding: 15px;
    justify-content: center;
}

.recapture-btn {
    background: #4a4a5a;
    color: #fff;
}

.recapture-btn:hover {
    background: #5a5a6a;
}

.submit-btn {
    background: #10b981;
    color: #fff;
}

.submit-btn:hover {
    background: #059669;
}

.submit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.picture-modal-footer {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    padding: 15px 20px;
    background: #2d2d3a;
    border-top: 1px solid #3d3d4a;
}

.picture-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.capture-btn-main {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: #fff;
    color: #1e1e2e;
    font-size: 28px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    border: 3px solid rgba(255, 255, 255, 0.3);
}

.capture-btn-main:hover {
    transform: scale(1.05);
    background: #f0f0f0;
}

.capture-btn-main:active {
    transform: scale(0.95);
}

.cancel-btn {
    background: #4a4a5a;
    color: #e0e0e0;
    padding: 10px 24px;
    border-radius: 30px;
    font-size: 14px;
    font-weight: 500;
}

.cancel-btn:hover {
    background: #5a5a6a;
}

/* Animation classes */
.fade-in {
    animation: fadeIn 0.3s ease;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .picture-modal-content {
        width: 95%;
        max-width: 95%;
    }
    
    .picture-modal-header {
        padding: 12px 15px;
    }
    
    .picture-modal-title {
        font-size: 14px;
    }
    
    .picture-modal-body {
        padding: 15px;
    }
    
    .capture-btn-main {
        width: 60px;
        height: 60px;
        font-size: 24px;
    }
    
    .cancel-btn {
        padding: 8px 20px;
        font-size: 13px;
    }
    
    .recapture-btn,
    .submit-btn {
        padding: 8px 16px;
        font-size: 13px;
    }
    
    .guide-frame {
        width: 85%;
        height: 85%;
    }
}

@media (max-width: 480px) {
    .capture-btn-main {
        width: 55px;
        height: 55px;
        font-size: 22px;
    }
    
    .captured-actions {
        gap: 10px;
        padding: 12px;
    }
    
    .recapture-btn,
    .submit-btn {
        padding: 6px 14px;
        font-size: 12px;
    }
}
</style>

<!-- School Header -->
<!-- School Header -->
<?php
$schoolData = $schoolInfo ?? null;
$campusData = $campusInfo ?? null;
$schoolName = 'School Management System';
$logoPath = null;
$logoExists = false;

// Try to get from schoolInfo (system table)
if ($schoolData) {
    if (is_object($schoolData)) {
        $schoolName = !empty($schoolData->system_name) ? $schoolData->system_name : $schoolName;
        if (!empty($schoolData->logo)) {
            $logoPath = 'system-logo/' . ltrim($schoolData->logo, '/');
            // Check if file exists
            if (file_exists(FCPATH . $logoPath)) {
                $logoExists = true;
            }
        }
    } elseif (is_array($schoolData)) {
        $schoolName = !empty($schoolData['system_name']) ? $schoolData['system_name'] : $schoolName;
        if (!empty($schoolData['logo'])) {
            $logoPath = 'system-logo/' . ltrim($schoolData['logo'], '/');
            if (file_exists(FCPATH . $logoPath)) {
                $logoExists = true;
            }
        }
    }
}

// If still default, try to get from campusInfo
if ($schoolName === 'School Management System' && $campusData) {
    if (is_object($campusData)) {
        $schoolName = !empty($campusData->campus_name) ? $campusData->campus_name : $schoolName;
    } elseif (is_array($campusData)) {
        $schoolName = !empty($campusData['campus_name']) ? $campusData['campus_name'] : $schoolName;
    }
}

// If still default, try to get from session
if ($schoolName === 'School Management System') {
    $sessionSchool = session('school_name');
    if (!empty($sessionSchool)) {
        $schoolName = $sessionSchool;
    }
}

// If still default, try to get from database directly
if ($schoolName === 'School Management System') {
    try {
        $db = \Config\Database::connect();
        $systemQuery = $db->table('system')
            ->select('system_name, logo')
            ->limit(1)
            ->get()
            ->getRow();
        if ($systemQuery && !empty($systemQuery->system_name)) {
            $schoolName = $systemQuery->system_name;
        }
        if ($systemQuery && !empty($systemQuery->logo) && !$logoExists) {
            $logoPath = 'system-logo/' . ltrim($systemQuery->logo, '/');
            if (file_exists(FCPATH . $logoPath)) {
                $logoExists = true;
            }
        }
    } catch (\Exception $e) {
        // Keep default name
    }
}

// For debugging - remove after fixing
// echo '<!-- Logo Path: ' . $logoPath . ', Exists: ' . ($logoExists ? 'Yes' : 'No') . ' -->';
?>

<div class="school-header d-flex align-items-center">
    <?php if ($logoExists && !empty($logoPath)): ?>
        <div class="logo-wrapper">
            <img src="<?= base_url($logoPath) ?>" alt="School Logo" class="school-logo" onerror="this.onerror=null; this.parentElement.style.display='none'; this.parentElement.nextElementSibling?.style.setProperty('display', 'flex');">
        </div>
    <?php else: ?>
        <div class="logo-wrapper">
            <i class="fa fa-graduation-cap fa-2x" style="color: #4f46e5;"></i>
        </div>
    <?php endif; ?>
    <div>
        <h2 class="mb-0 school-name"><?= esc($schoolName) ?></h2>
        <p class="mb-0 opacity-75">Parent Portal</p>
    </div>
</div>

<!-- Sticky Active Student Bar -->
<div class="active-student-sticky" id="activeStudentBar">
    <div>
        <i class="fa fa-user-circle me-2"></i>
        <strong>Active Student:</strong> <span id="activeStudentName"><?= esc($activeStudentName) ?></span>
        <?php if ($currentClassInfo): ?>
            <span class="ms-2 badge bg-white text-success"><?= esc($currentClassInfo['class_display']) ?></span>
        <?php endif; ?>
    </div>
    
</div>
<!-- ==================== KIDS SECTION ==================== -->
<?php if (!empty($children)): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fa fa-users me-2 text-primary"></i> Students</h5>
        </div>
        <div class="card-body">
            <div class="kids-scroll">
                <?php foreach ($children as $child): ?>
                    <?php 
                    // Calculate age from date_of_birth
                    $age = '';
                    if (!empty($child['date_of_birth'])) {
                        $dob = new DateTime($child['date_of_birth']);
                        $today = new DateTime();
                        $age = $dob->diff($today)->y;
                    }
                    // Limit name to 11 characters
                    $shortName = strlen($child['name']) > 11 ? substr($child['name'], 0, 10) . '...' : $child['name'];
                    ?>
                    <div class="kid-card <?= $activeStudentId === (int)$child['student_id'] ? 'active' : '' ?>" 
                         onclick="event.preventDefault(); switchStudent(<?= $child['student_id'] ?>, '<?= esc($child['name']) ?>')">
                        
                        <!-- Show photo if exists, otherwise show name initial -->
                        <?php if (!empty($child['profile_photo_url']) && $child['profile_photo_url'] != base_url()): ?>
                            <img src="<?= esc($child['profile_photo_url']) ?>" alt="<?= esc($child['name']) ?>" class="kid-avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="kid-avatar-placeholder" style="display: none;">
                                <span><?= strtoupper(substr($child['name'], 0, 1)) ?></span>
                            </div>
                        <?php else: ?>
                            <div class="kid-avatar-placeholder">
                                <span><?= strtoupper(substr($child['name'], 0, 1)) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="kid-name" title="<?= esc($child['name']) ?>"><?= esc($shortName) ?></div>
                        <div class="kid-class">
                            <?= esc($child['class_display']) ?>
                            <?php if ($age > 0): ?>
                                <span class="kid-age">(<?= $age ?>y)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>


<!-- ==================== FEE HISTORY SECTION ==================== -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-2">
        <h5 class="mb-0 fs-6"><i class="fa fa-credit-card me-2 text-primary"></i> Family Fee History</h5>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($feeHistory)): ?>
            <div class="fee-table-wrapper">
                <table class="fee-history-table">
                    <thead>
                        <tr>
                            <th class="month-col">Month</th>
                            <?php foreach ($feeHistory as $record): ?>
                                <th class="month-name"><?= esc(date('M', strtotime($record['month'] . '-01'))) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="row-label">Monthly</td>
                            <?php foreach ($feeHistory as $record): ?>
                                <td class="amount-cell">
                                    <?php if ($record['monthly_paid'] > 0): ?>
                                        <span class="amount-paid"><?= number_format($record['monthly_paid'], 0) ?></span>
                                    <?php else: ?>
                                        <span class="amount-na">—</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td class="row-label">Other</td>
                            <?php foreach ($feeHistory as $record): ?>
                                <td class="amount-cell">
                                    <?php if ($record['other_paid'] > 0): ?>
                                        <span class="amount-other"><?= number_format($record['other_paid'], 0) ?></span>
                                    <?php else: ?>
                                        <span class="amount-na">—</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td class="row-label total-label">Total</td>
                            <?php foreach ($feeHistory as $record): ?>
                                <td class="amount-cell total-cell">
                                    <?php if ($record['total_paid'] > 0): ?>
                                        <span class="amount-total"><?= number_format($record['total_paid'], 0) ?></span>
                                    <?php else: ?>
                                        <span class="amount-na">—</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <?php 
            $unpaidData = $totalUnpaidAmount ?? ['monthly' => 0, 'other' => 0, 'total' => 0];
            if ($unpaidData['total'] > 0): 
            ?>
                <div class="balance-summary">
                    <div class="balance-item">
                        <div class="balance-label">Monthly Bal.</div>
                        <div class="balance-amount monthly-bal"><?= number_format($unpaidData['monthly'], 0) ?></div>
                    </div>
                    <div class="balance-item">
                        <div class="balance-label">Other Bal.</div>
                        <div class="balance-amount other-bal"><?= number_format($unpaidData['other'], 0) ?></div>
                    </div>
                    <div class="balance-item">
                        <div class="balance-label">Total Bal.</div>
                        <div class="balance-amount total-bal"><?= number_format($unpaidData['total'], 0) ?></div>
                    </div>
                </div>
            <?php else: ?>
                <div class="paid-status">
                    <i class="fa fa-check-circle"></i> All fees are paid up to date!
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa fa-credit-card fa-2x mb-2 opacity-50"></i>
                <p>No fee history available.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ==================== ATTENDANCE SECTION ==================== -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
        <h5 class="mb-0"><i class="fa fa-calendar-check me-2 text-primary"></i> This Week's Attendance</h5>
        <?php if (!empty($currentWeekAttendance)): ?>
            <div class="mt-2 mt-sm-0">
                
                <span class="badge bg-danger me-2">Absent: <?= $currentWeekAttendance['absent_days'] ?></span>
                
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (!empty($currentWeekAttendance['week_days'])): ?>
            <div class="attendance-grid">
                <?php foreach ($currentWeekAttendance['week_days'] as $day): ?>
                    <div class="attendance-day">
                        <div class="fw-bold"><?= esc($day['day_short']) ?></div>
                        <div class="attendance-status <?= $day['status_class'] ?>">
                            <?= esc($day['status']) ?>
                        </div>
                        <?php if ($day['status'] !== '—' && $day['status'] !== 'OFF'): ?>
                         
                        <?php elseif ($day['status'] === 'OFF' && !$day['is_school_day']): ?>
                            <small class="text-muted d-block mt-1">Holiday</small>
                        <?php elseif ($day['status'] === 'OFF' && $day['is_past_day']): ?>
                            
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-3 text-muted small">
                <i class="fa fa-info-circle me-1"></i>
                <?php if ($currentWeekAttendance['working_days'] > 0): ?>
                    Attendance: <strong><?= $currentWeekAttendance['attendance_percentage'] ?>%</strong>
                <?php else: ?>
                    No school days this week
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-3 text-muted">
                <i class="fa fa-calendar fa-2x mb-2 opacity-50"></i>
                <p>No attendance data available.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ==================== BMI & HEALTH SECTION ==================== -->
<?php
$currentLang = session()->get('language') ?: 'en';
$isUrdu = ($currentLang == 'ur');

// Calculate age if student info is available
$studentAge = 0;
if (!empty($studentInfo) && isset($studentInfo->date_of_birth)) {
    $studentAge = date_diff(date_create($studentInfo->date_of_birth), date_create('today'))->y;
}
?>

<div class="card shadow-sm mb-4">
    <div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <div>
            <h5 class="mb-1"><i class="fa fa-heartbeat me-2 text-danger"></i> 
                <?= $isUrdu ? 'صحت اور بی ایم آئی مانیٹر' : 'Health & BMI Monitor' ?>
            </h5>
            <?php if ($bmiData && $bmiData->bmi_updated_date): ?>
                <small class="text-muted">
                    <?= $isUrdu ? 'آخری اپ ڈیٹ:' : 'Last updated:' ?> 
                    <?= date('M d, Y', strtotime($bmiData->bmi_updated_date)) ?>
                </small>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <?php if ($bmiData && $bmiData->height && $bmiData->weight): ?>
            <!-- 4 Cards in a Row - Height, Weight, BMI, Age -->
            <div class="row g-2 mb-4">
                <!-- Height Card -->
                <div class="col-3">
                    <div class="bmi-card text-center p-2 bg-light rounded">
                        <div class="bmi-card-label"><?= $isUrdu ? 'قد' : 'Height' ?></div>
                        <div class="bmi-card-value"><?= round($bmiData->height) ?></div>
                        <div class="bmi-card-unit"><?= $isUrdu ? 'سینٹی میٹر' : 'cm' ?></div>
                    </div>
                </div>
                <!-- Weight Card -->
                <div class="col-3">
                    <div class="bmi-card text-center p-2 bg-light rounded">
                        <div class="bmi-card-label"><?= $isUrdu ? 'وزن' : 'Weight' ?></div>
                        <div class="bmi-card-value"><?= round($bmiData->weight) ?></div>
                        <div class="bmi-card-unit"><?= $isUrdu ? 'کلوگرام' : 'kg' ?></div>
                    </div>
                </div>

                <div class="col-3">
    <div class="bmi-card text-center p-2 bg-primary text-white rounded">
        <div class="bmi-card-label"><?= $isUrdu ? 'عمر' : 'Age' ?></div>
        <div class="bmi-card-value">
            <?php
            // Calculate age with years and months
            $ageYears = 0;
            $ageMonths = 0;
            $ageDisplay = '0 years';
            
            if (!empty($studentInfo)) {
                $dob = null;
                if (isset($studentInfo->db_status) && $studentInfo->db_status == 1 && !empty($studentInfo->date_of_birth_age)) {
                    $dob = new DateTime($studentInfo->date_of_birth_age);
                } elseif (!empty($studentInfo->date_of_birth)) {
                    $dob = new DateTime($studentInfo->date_of_birth);
                }
                
                if ($dob) {
                    $today = new DateTime();
                    $diff = $dob->diff($today);
                    $ageYears = $diff->y;
                    $ageMonths = $diff->m;
                    
                    if ($ageYears > 0) {
                        $ageDisplay = $ageYears . 'y';
                        if ($ageMonths > 0) {
                            $ageDisplay .= ' ' . $ageMonths . 'm';
                        }
                    } elseif ($ageMonths > 0) {
                        $ageDisplay = $ageMonths . 'm';
                    } else {
                        $ageDisplay = '0y';
                    }
                }
            }
            ?>
            <?= $ageDisplay ?>
        </div>
        <div class="bmi-card-unit"><?= $isUrdu ? 'سال' : 'years' ?></div>
    </div>
</div>
                <!-- BMI Card -->
                <div class="col-3">
                    <div class="bmi-card text-center p-2 rounded text-white <?= 
                        $bmiData->bmi_category == 'normal' ? 'bg-success' : 
                        ($bmiData->bmi_category == 'underweight' ? 'bg-warning text-dark' : 
                        ($bmiData->bmi_category == 'overweight' ? 'bg-info' : 'bg-danger')) 
                    ?>">
                        <div class="bmi-card-label"><?= $isUrdu ? 'بی ایم آئی' : 'BMI' ?></div>
                        <div class="bmi-card-value"><?= round($bmiData->bmi) ?></div>
                        <div class="bmi-card-unit">
                            <?php
                            if ($isUrdu) {
                                $categoryUrdu = [
                                    'underweight' => 'کم وزن',
                                    'normal' => 'معمول',
                                    'overweight' => 'زیادہ وزن',
                                    'obese' => 'موٹاپا'
                                ];
                                echo $categoryUrdu[$bmiData->bmi_category] ?? $bmiData->bmi_category;
                            } else {
                                echo ucfirst($bmiData->bmi_category ?? 'Normal');
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <!-- Age Card -->
              <!-- Age Card -->

            </div>
            
            <!-- BMI Scale Indicator -->
            <div class="mt-2 mb-3">
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-warning" style="width: 18.5%;"></div>
                    <div class="progress-bar bg-success" style="width: 6%;"></div>
                    <div class="progress-bar bg-info" style="width: 10%;"></div>
                    <div class="progress-bar bg-danger" style="width: 10%;"></div>
                </div>
                <div class="d-flex justify-content-between small mt-1 px-1">
                    <span class="text-warning" style="font-size: 9px;">&lt;18.5</span>
                    <span class="text-success" style="font-size: 9px;">18.5-24.9</span>
                    <span class="text-info" style="font-size: 9px;">25-29.9</span>
                    <span class="text-danger" style="font-size: 9px;">≥30</span>
                </div>
                <div class="mt-1 text-center">
                    <i class="fa fa-arrow-down text-primary"></i>
                    <small class="text-primary"><?= round($bmiData->bmi) ?></small>
                </div>
            </div>
            
            <!-- Health Recommendations -->
            <div class="mt-3">
                <?php if ($bmiData->bmi_category == 'underweight'): ?>
                    <div class="alert alert-warning py-2 mb-2">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong><?= $isUrdu ? 'کم وزن' : 'Underweight' ?></strong>
                        <p class="mb-0 small"><?= $isUrdu ? 'وزن کم ہے۔ صحت مند غذا کا استعمال بڑھائیں۔' : 'Weight is low. Increase healthy food intake.' ?></p>
                    </div>
                    <div class="border rounded p-2 bg-light mb-2">
                        <div class="text-success fw-bold small mb-1"><i class="fa fa-apple"></i> <?= $isUrdu ? 'غذائی تجاویز' : 'Diet Tips' ?></div>
                        <p class="mb-0 small"><?= $isUrdu ? 'پروٹین اور صحت مند چکنائی والی غذائیں شامل کریں۔ دودھ، انڈے، دالیں، گری دار میوے کھائیں۔' : 'Add protein and healthy fats. Eat milk, eggs, lentils, nuts.' ?></p>
                    </div>
                    <div class="border rounded p-2 bg-light">
                        <div class="text-primary fw-bold small mb-1"><i class="fa fa-futbol"></i> <?= $isUrdu ? 'ورزش کی تجاویز' : 'Exercise Tips' ?></div>
                        <p class="mb-0 small"><?= $isUrdu ? 'وزن بڑھانے کے لیے طاقت کی مشقیں کریں۔' : 'Do strength training to gain weight.' ?></p>
                    </div>
                <?php elseif ($bmiData->bmi_category == 'overweight'): ?>
                    <div class="alert alert-info py-2 mb-2">
                        <i class="fa fa-info-circle"></i>
                        <strong><?= $isUrdu ? 'وزن زیادہ ہے' : 'Overweight' ?></strong>
                        <p class="mb-0 small"><?= $isUrdu ? 'وزن زیادہ ہے۔ متوازن غذا اور باقاعدہ ورزش کریں۔' : 'Weight is high. Balanced diet and regular exercise.' ?></p>
                    </div>
                    <div class="border rounded p-2 bg-light mb-2">
                        <div class="text-success fw-bold small mb-1"><i class="fa fa-apple"></i> <?= $isUrdu ? 'غذائی تجاویز' : 'Diet Tips' ?></div>
                        <p class="mb-0 small"><?= $isUrdu ? 'چکنائی اور میٹھی چیزیں کم کریں۔ سبزیاں، پھل زیادہ کھائیں۔' : 'Reduce fats and sugars. Eat more vegetables and fruits.' ?></p>
                    </div>
                    <div class="border rounded p-2 bg-light">
                        <div class="text-primary fw-bold small mb-1"><i class="fa fa-futbol"></i> <?= $isUrdu ? 'ورزش کی تجاویز' : 'Exercise Tips' ?></div>
                        <p class="mb-0 small"><?= $isUrdu ? 'روزانہ 30-45 منٹ ورزش کریں۔' : 'Exercise 30-45 minutes daily.' ?></p>
                    </div>
                <?php elseif ($bmiData->bmi_category == 'obese'): ?>
                    <div class="alert alert-danger py-2 mb-2">
                        <i class="fa fa-exclamation-circle"></i>
                        <strong><?= $isUrdu ? 'موٹاپا' : 'Obese' ?></strong>
                        <p class="mb-0 small"><?= $isUrdu ? 'موٹاپا ہے۔ ڈاکٹر سے رجوع کریں۔' : 'Obese. Consult a doctor.' ?></p>
                    </div>
                    <div class="border rounded p-2 bg-light mb-2">
                        <div class="text-success fw-bold small mb-1"><i class="fa fa-apple"></i> <?= $isUrdu ? 'غذائی تجاویز' : 'Diet Tips' ?></div>
                        <p class="mb-0 small"><?= $isUrdu ? 'میٹھا، تلی ہوئی چیزیں کم کریں۔ سبزیاں، پھل زیادہ کھائیں۔' : 'Reduce sugar and fried foods. Eat more vegetables and fruits.' ?></p>
                    </div>
                    <div class="border rounded p-2 bg-light">
                        <div class="text-primary fw-bold small mb-1"><i class="fa fa-futbol"></i> <?= $isUrdu ? 'ورزش کی تجاویز' : 'Exercise Tips' ?></div>
                        <p class="mb-0 small"><?= $isUrdu ? 'روزانہ چہل قدمی سے شروع کریں۔' : 'Start with daily walking.' ?></p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success py-2 mb-2">
                        <i class="fa fa-check-circle"></i>
                        <strong><?= $isUrdu ? 'وزن معمول پر ہے' : 'Normal Weight' ?></strong>
                        <p class="mb-0 small"><?= $isUrdu ? 'وزن معمول پر ہے۔ صحت مند طرز زندگی جاری رکھیں۔' : 'Weight is normal. Maintain healthy lifestyle.' ?></p>
                    </div>
                    <div class="border rounded p-2 bg-light mb-2">
                        <div class="text-success fw-bold small mb-1"><i class="fa fa-apple"></i> <?= $isUrdu ? 'غذائی تجاویز' : 'Diet Tips' ?></div>
                        <p class="mb-0 small"><?= $isUrdu ? 'متوازن غذا کھائیں۔' : 'Eat balanced diet.' ?></p>
                    </div>
                    <div class="border rounded p-2 bg-light">
                        <div class="text-primary fw-bold small mb-1"><i class="fa fa-futbol"></i> <?= $isUrdu ? 'ورزش کی تجاویز' : 'Exercise Tips' ?></div>
                        <p class="mb-0 small"><?= $isUrdu ? 'روزانہ 30 منٹ ورزش کریں۔' : 'Exercise 30 minutes daily.' ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
         <!-- BMI History -->
<?php if (!empty($bmiHistory) && count($bmiHistory) > 0): ?>
<div class="mt-3">
  <h6 class="mb-2 fw-bold" style="font-size: 1.5rem;"><i class="fa fa-line-chart"></i> <?= $isUrdu ? 'بی ایم آئی ہسٹری' : 'BMI History' ?></h6>
    <div class="table-responsive">
        <table class="table table-sm table-bordered">
            <thead class="table-light">
                <tr>
                    <th class="text-center"><?= $isUrdu ? 'مہینہ' : 'Month' ?></th>
                    <th class="text-center"><?= $isUrdu ? 'قد (سینٹی میٹر)' : 'Height (cm)' ?></th>
                    <th class="text-center"><?= $isUrdu ? 'وزن (کلوگرام)' : 'Weight (kg)' ?></th>
                    <th class="text-center"><?= $isUrdu ? 'بی ایم آئی' : 'BMI' ?></th>
                    <th class="text-center"><?= $isUrdu ? 'کیٹیگری' : 'Category' ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bmiHistory as $history): ?>
                <tr>
                    <td class="text-center small fw-bold"><?= esc($history['month_display']) ?></td>
                    <td class="text-center small"><?= round($history['height']) ?></td>
                    <td class="text-center small"><?= round($history['weight']) ?></td>
                    <td class="text-center small fw-bold"><?= round($history['bmi']) ?></td>
                    <td class="text-center">
                        <span class="badge <?= 
                            $history['bmi_category'] == 'normal' ? 'bg-success' : 
                            ($history['bmi_category'] == 'underweight' ? 'bg-warning' : 
                            ($history['bmi_category'] == 'overweight' ? 'bg-info' : 'bg-danger')) 
                        ?>">
                            <?= ucfirst($history['bmi_category']) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

        <?php else: ?>
            <div class="text-center py-4 text-muted">
                <i class="fa fa-heartbeat fa-3x mb-3 opacity-50"></i>
                <p><?= $isUrdu ? 'اس طالب علم کے لیے کوئی ہیلتھ ریکارڈ موجود نہیں۔' : 'No health records available for this student.' ?></p>
                <p class="small"><?= $isUrdu ? 'براہ کرم قد اور وزن اپ ڈیٹ کرنے کے لیے اسکول سے رابطہ کریں۔' : 'Please contact the school to update height and weight measurements.' ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
<!-- ==================== TODAY'S DIARY SECTION ==================== -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fa fa-book-open me-2 text-primary"></i> 
            <?php 
            if (!empty($todayDiary) && isset($todayDiary[0]['diary_day_name'])) {
                echo 'Diary - ' . $todayDiary[0]['diary_day_name'];
            } else {
                echo 'Today\'s Diary - ' . date('l');
            }
            ?>
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($todayDiary)): ?>
            <div class="diary-accordion">
                <?php foreach ($todayDiary as $index => $diary): ?>
                    <div class="diary-card">
                        <div class="diary-card-header" data-card="<?= $diary['did'] ?>">
                            <span>
                                <i class="fa fa-graduation-cap me-2"></i> 
                                <strong><?= esc($diary['subject_name']) ?></strong>
                            </span>
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($diary['has_quiz']): ?>
                                    <a href="<?= base_url('student/quizzes/start/' . $diary['quiz_id'] . '?sid=' . $activeStudentId) ?>" 
                                       class="quiz-btn btn-sm" onclick="event.stopPropagation()">
                                        <i class="fa fa-play-circle me-1"></i> Quiz
                                    </a>
                                <?php endif; ?>
                                <i class="fa fa-chevron-down toggle-icon"></i>
                            </div>
                        </div>
                        
                        <div class="diary-card-body" data-body="<?= $diary['did'] ?>">
                            
                            <!-- ========== CLASS WORK & HOMEWORK - SIDE BY SIDE ========== -->
                            <div class="diary-section">
                                <div class="row g-3">
                                    <!-- Class Work -->
                                    <div class="col-md-6">
                                        <div class="content-box">
                                            <div class="section-title">
                                                <i class="fa fa-chalkboard text-primary"></i>
                                                <span>Class Work</span>
                                            </div>
                                            <div class="content-text"><?= strip_tags($diary['classwork_formatted']) ?></div>
                                        </div>
                                    </div>
                                    <!-- Homework -->
                                    <div class="col-md-6">
                                        <div class="content-box">
                                            <div class="section-title">
                                                <i class="fa fa-pencil-alt text-warning"></i>
                                                <span>Homework</span>
                                            </div>
                                            <div class="content-text"><?= strip_tags($diary['homework_formatted']) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                      <!-- ========== TASKS SECTION - AUDIO, VIDEO, PICTURE ========== -->
<?php if ($diary['requires_audio'] || $diary['requires_video'] || $diary['requires_picture']): ?>
<div class="diary-section">
    <div class="tasks-title">
        <i class="fa fa-tasks me-2"></i> Required Tasks
    </div>
    <div class="row g-3">
        
        <!-- Audio Task - ONLY show if required -->
        <?php if ($diary['requires_audio']): ?>
        <div class="col-md-4">
            <div class="task-box">
                <div class="task-header">
                    <i class="fa fa-microphone text-danger"></i>
                    <span>Audio Task</span>
                    <?php if (!empty($diary['audio_recordings'])): ?>
                        <span class="task-badge"><?= count($diary['audio_recordings']) ?> uploaded</span>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($diary['audio_caption'])): ?>
                    <div class="task-caption"><?= nl2br(esc($diary['audio_caption'])) ?></div>
                <?php endif; ?>
                
                <?php if (!empty($diary['audio_recordings'])): ?>
                    <?php foreach ($diary['audio_recordings'] as $audio): ?>
                        <div class="submission-item">
                            <audio controls class="submission-audio">
                                <source src="<?= base_url($audio['audio_file_path']) ?>" type="audio/webm">
                            </audio>
                            <span class="badge <?= $audio['status'] === 'approved' ? 'bg-success' : ($audio['status'] === 'rejected' ? 'bg-danger' : 'bg-warning') ?>">
                                <?= ucfirst($audio['status']) ?>
                            </span>
                        </div>
                        <?php if ($audio['teacher_feedback']): ?>
                            <small class="feedback-text"><?= esc($audio['teacher_feedback']) ?></small>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div class="task-actions">
                    <button class="task-btn audio-record-btn" data-diary-id="<?= $diary['did'] ?>" data-student-id="<?= $activeStudentId ?>">
                        <i class="fa fa-microphone"></i> Record
                    </button>
                    <button class="task-btn task-stop-btn audio-stop-btn" data-diary-id="<?= $diary['did'] ?>" style="display: none;">
                        <i class="fa fa-stop"></i> Stop
                    </button>
                    <span class="audio-timer" id="audio-timer-<?= $diary['did'] ?>"></span>
                </div>
                <div id="upload-status-<?= $diary['did'] ?>" class="upload-status"></div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Video Task - ONLY show if required -->
        <?php if ($diary['requires_video']): ?>
        <div class="col-md-4">
            <div class="task-box">
                <div class="task-header">
                    <i class="fa fa-video-camera text-primary"></i>
                    <span>Video Task</span>
                    <?php if (!empty($diary['video_recordings'])): ?>
                        <span class="task-badge"><?= count($diary['video_recordings']) ?> uploaded</span>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($diary['video_caption'])): ?>
                    <div class="task-caption"><?= nl2br(esc($diary['video_caption'])) ?></div>
                <?php endif; ?>
                
                <?php if (!empty($diary['video_recordings'])): ?>
                    <?php foreach ($diary['video_recordings'] as $video): ?>
                        <div class="submission-item">
                            <video controls class="submission-video">
                                <source src="<?= base_url($video['video_file_path']) ?>" type="video/mp4">
                            </video>
                            <span class="badge <?= $video['status'] === 'approved' ? 'bg-success' : ($video['status'] === 'rejected' ? 'bg-danger' : 'bg-warning') ?>">
                                <?= ucfirst($video['status'] ?? 'pending') ?>
                            </span>
                        </div>
                        <?php if (!empty($video['teacher_remarks'])): ?>
                            <small class="feedback-text"><?= esc($video['teacher_remarks']) ?></small>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div class="task-actions">
                    <button class="task-btn video-record-btn" data-diary-id="<?= $diary['did'] ?>" data-student-id="<?= $activeStudentId ?>">
                        <i class="fa fa-video-camera"></i> Record
                    </button>
                    <label class="task-btn task-upload">
                        <i class="fa fa-upload"></i> Upload
                        <input type="file" accept="video/*" class="d-none video-file-input" data-diary-id="<?= $diary['did'] ?>" data-student-id="<?= $activeStudentId ?>">
                    </label>
                </div>
                <div class="video-status" id="video-status-<?= $diary['did'] ?>"></div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Picture Task - ONLY show if required -->
        <?php if ($diary['requires_picture']): ?>
        <div class="col-md-4">
            <div class="task-box">
                <div class="task-header">
                    <i class="fa fa-camera text-success"></i>
                    <span>Picture Task</span>
                    <?php if (!empty($diary['picture_recordings'])): ?>
                        <span class="task-badge"><?= count($diary['picture_recordings']) ?> uploaded</span>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($diary['picture_caption'])): ?>
                    <div class="task-caption"><?= nl2br(esc($diary['picture_caption'])) ?></div>
                <?php endif; ?>
                
                <?php if (!empty($diary['picture_recordings'])): ?>
                    <?php foreach ($diary['picture_recordings'] as $picture): ?>
                        <div class="submission-item">
                            <img src="<?= base_url($picture['picture_path']) ?>" class="submission-image">
                            <span class="badge <?= $picture['status'] === 'approved' ? 'bg-success' : ($picture['status'] === 'rejected' ? 'bg-danger' : 'bg-warning') ?>">
                                <?= ucfirst($picture['status'] ?? 'pending') ?>
                            </span>
                        </div>
                        <?php if (!empty($picture['teacher_remarks'])): ?>
                            <small class="feedback-text"><?= esc($picture['teacher_remarks']) ?></small>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div class="task-actions">
                    <button class="task-btn picture-capture-btn" data-diary-id="<?= $diary['did'] ?>" data-student-id="<?= $activeStudentId ?>">
                        <i class="fa fa-camera"></i> Capture
                    </button>
                    <label class="task-btn task-upload">
                        <i class="fa fa-upload"></i> Upload
                        <input type="file" accept="image/*" class="d-none picture-file-input" data-diary-id="<?= $diary['did'] ?>" data-student-id="<?= $activeStudentId ?>">
                    </label>
                </div>
                <div class="picture-status" id="picture-status-<?= $diary['did'] ?>"></div>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
</div>
<?php endif; ?>                            
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-4 text-muted">
                <i class="fa fa-calendar-alt fa-3x mb-3 opacity-50"></i>
                <h5>No diary entries for today</h5>
                <p>Check back later for today's homework and activities.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Calculate student age for prayer tracking
$studentAge = 0;
if (!empty($studentInfo) && isset($studentInfo->date_of_birth)) {
    $studentAge = date_diff(date_create($studentInfo->date_of_birth), date_create('today'))->y;
}

$campusPrayerStartAge = isset($campusInfo->prayer_tracking_start_age) ? $campusInfo->prayer_tracking_start_age : 7;
$campusPrayerMandatoryAge = isset($campusInfo->prayer_tracking_mandatory_age) ? $campusInfo->prayer_tracking_mandatory_age : 10;

$isEligibleForPrayer = $studentAge >= $campusPrayerStartAge;
$isMandatory = $studentAge >= $campusPrayerMandatoryAge;

// Debug - remove after testing
// echo '<!-- Age: ' . $studentAge . ', Eligible: ' . ($isEligibleForPrayer ? 'Yes' : 'No') . ', Mandatory: ' . ($isMandatory ? 'Yes' : 'No') . ' -->';
?>
<!-- ==================== BAG PACK + PRAYER TRACKING SECTION ==================== -->
<div class="row">
    <!-- Bag Pack Section - Takes full width if prayer is hidden, otherwise half -->
    <div class="<?= ($isEligibleForPrayer ?? false) ? 'col-md-6' : 'col-md-12' ?>">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fa fa-bag-shopping me-2 text-primary"></i> 
                    <?php
                    $todayNum = date('N');
                    $dayNames = [
                        1 => 'Tuesday',      // Monday -> pack for Tuesday
                        2 => 'Wednesday',    // Tuesday -> pack for Wednesday
                        3 => 'Thursday',     // Wednesday -> pack for Thursday
                        4 => 'Friday',       // Thursday -> pack for Friday
                        5 => 'Monday',       // Friday -> pack for Monday
                        6 => 'Monday',       // Saturday -> pack for Monday
                        7 => 'Monday'        // Sunday -> pack for Monday
                    ];
                    echo $dayNames[$todayNum] . '\'s Bag Pack';
                    ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($bagPackItems)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Item</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $counter = 1; ?>
                                <?php foreach ($bagPackItems as $item): ?>
                                    <tr>
                                        <td><?= $counter++ ?>.</td>
                                        <td>
                                            <i class="fa <?= esc($item['icon']) ?> me-2 text-primary"></i>
                                            <?= esc($item['item_name']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fa fa-lightbulb me-2"></i>
                        <?php
                        $todayNum = date('N');
                        if ($todayNum == 5 || $todayNum == 6 || $todayNum == 7) {
                            echo 'Get ready for Monday! Pack these items.';
                        } else {
                            echo 'Don\'t forget to pack these items for tomorrow!';
                        }
                        ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3 text-muted">
                        <i class="fa fa-bag-shopping fa-2x mb-2 opacity-50"></i>
                        <p>No items to pack.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Prayer Tracking Card - Only shown if student is eligible (age >= 7) -->
    <?php if ($isEligibleForPrayer ?? false): ?>
    <div class="col-md-6">
        <div class="card shadow-sm mb-4 prayer-card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fa fa-mosque me-2 text-success"></i> Daily Prayers</h5>
            </div>
            <div class="card-body p-0">
                <div class="prayer-body">
                    <?php if ($isMandatory ?? false): ?>
                        <div class="alert alert-warning py-2 mb-3">
                            <i class="fa fa-exclamation-triangle"></i> <strong>Mandatory:</strong> You must offer all 5 prayers daily.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info py-2 mb-3">
                            <i class="fa fa-info-circle"></i> <strong>Encouraged:</strong> Start building the habit of 5 daily prayers.
                        </div>
                    <?php endif; ?>
                    
                    <div class="prayer-grid" id="prayerGrid">
                        <div class="prayer-item" data-prayer="fajr">
                            <i class="fa fa-star-of-david"></i>
                            <span>Fajr</span>
                        </div>
                        <div class="prayer-item" data-prayer="dhuhr">
                            <i class="fa fa-sun"></i>
                            <span>Dhuhr</span>
                        </div>
                        <div class="prayer-item" data-prayer="asr">
                            <i class="fa fa-cloud-sun"></i>
                            <span>Asr</span>
                        </div>
                        <div class="prayer-item" data-prayer="maghrib">
                            <i class="fa fa-moon"></i>
                            <span>Maghrib</span>
                        </div>
                        <div class="prayer-item" data-prayer="isha">
                            <i class="fa fa-star-and-crescent"></i>
                            <span>Isha</span>
                        </div>
                    </div>
                    
                    <div class="prayer-progress">
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" id="prayerProgressBar" style="width: 0%"></div>
                        </div>
                        <div class="text-center mt-2">
                            <span id="prayerCount">0</span>/5 Prayers Offered
                        </div>
                    </div>
                    
                    <div class="prayer-stats">
                        <div class="prayer-stat">
                            <div class="number" id="weeklyStreak">0</div>
                            <div class="label">Days This Week</div>
                        </div>
                        <div class="prayer-stat">
                            <div class="number" id="monthlyStreak">0</div>
                            <div class="label">Days This Month</div>
                        </div>
                        <div class="prayer-stat">
                            <div class="number" id="totalDays">0</div>
                            <div class="label">Total Days</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>


<?= csrf_field() ?>

<script>
// ============================================
// SWITCH STUDENT
// ============================================
function switchStudent(studentId, studentName) {
    document.getElementById('activeStudentName').innerHTML = studentName;
    window.location.href = '<?= base_url("student/switch/") ?>' + studentId;
}

// ============================================
// EXPANDABLE CARDS
// ============================================
document.querySelectorAll('.diary-card-header').forEach(header => {
    header.addEventListener('click', function(e) {
        if (e.target.closest('.quiz-btn')) return;
        
        const cardId = this.dataset.card;
        const body = document.querySelector(`.diary-card-body[data-body="${cardId}"]`);
        const isActive = body.classList.contains('active');
        
        document.querySelectorAll('.diary-card-body').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.diary-card-header').forEach(h => h.classList.remove('active'));
        
        if (!isActive) {
            body.classList.add('active');
            this.classList.add('active');
        }
    });
});

const firstCard = document.querySelector('.diary-card-header');
if (firstCard) firstCard.click();

// ============================================
// AUDIO RECORDING - Updated Selectors
// ============================================
let mediaRecorder = null;
let audioChunks = [];
let timerInterval = null;
let startTime = 0;

function startAudioTimer(diaryId) {
    const timerDiv = document.getElementById(`audio-timer-${diaryId}`);
    let seconds = 0;
    if (timerInterval) clearInterval(timerInterval);
    timerInterval = setInterval(() => {
        seconds++;
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        if (timerDiv) timerDiv.textContent = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }, 1000);
}

function stopAudioTimer(diaryId) {
    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
    }
    const timerDiv = document.getElementById(`audio-timer-${diaryId}`);
    if (timerDiv) timerDiv.textContent = '';
}

// Audio Record Button
document.querySelectorAll('.audio-record-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const diaryId = this.dataset.diaryId;
        const studentId = this.dataset.studentId;
        const stopBtn = document.querySelector(`.audio-stop-btn[data-diary-id="${diaryId}"]`);
        const uploadStatus = document.getElementById(`upload-status-${diaryId}`);
        
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            audioChunks = [];
            
            mediaRecorder.ondataavailable = event => {
                audioChunks.push(event.data);
            };
            
            mediaRecorder.onstop = async () => {
                const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                const formData = new FormData();
                formData.append('audio_recording', audioBlob, 'recording.webm');
                formData.append('student_id', studentId);
                formData.append('diary_id', diaryId);
                formData.append('duration', Math.floor(Date.now() / 1000) - startTime);
                
                if (uploadStatus) uploadStatus.innerHTML = '<span class="text-info">Uploading...</span>';
                
                const response = await fetch('<?= base_url("student/upload-audio") ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                const result = await response.json();
                if (result.success) {
                    if (uploadStatus) uploadStatus.innerHTML = '<span class="text-success">✓ Uploaded! Refreshing...</span>';
                    setTimeout(() => location.reload(), 1500);
                } else {
                    if (uploadStatus) uploadStatus.innerHTML = '<span class="text-danger">✗ Failed: ' + result.message + '</span>';
                }
                
                stream.getTracks().forEach(track => track.stop());
            };
            
            mediaRecorder.start();
            startTime = Math.floor(Date.now() / 1000);
            
            // Update UI
            this.style.display = 'none';
            if (stopBtn) stopBtn.style.display = 'inline-flex';
            startAudioTimer(diaryId);
            
        } catch (err) {
            alert('Unable to access microphone. Please check permissions.');
        }
    });
});

// Audio Stop Button
document.querySelectorAll('.audio-stop-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const diaryId = this.dataset.diaryId;
        const recordBtn = document.querySelector(`.audio-record-btn[data-diary-id="${diaryId}"]`);
        
        if (mediaRecorder && mediaRecorder.state === 'recording') {
            mediaRecorder.stop();
            this.style.display = 'none';
            if (recordBtn) recordBtn.style.display = 'inline-flex';
            stopAudioTimer(diaryId);
        }
    });
});

// ============================================
// VIDEO RECORDING
// ============================================
let videoMediaRecorder = null;
let videoChunks = [];
let isVideoRecording = false;
let videoStartTime = 0;
let currentVideoStream = null;

document.querySelectorAll('.video-record-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const diaryId = this.dataset.diaryId;
        const studentId = this.dataset.studentId;
        const statusDiv = document.getElementById(`video-status-${diaryId}`);
        
        if (isVideoRecording && videoMediaRecorder && videoMediaRecorder.state === 'recording') {
            videoMediaRecorder.stop();
            isVideoRecording = false;
            this.innerHTML = '<i class="fa fa-video-camera"></i> Record';
            this.classList.remove('btn-danger');
            if (statusDiv) statusDiv.innerHTML = '<span class="text-muted">Processing video...</span>';
            return;
        }
        
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            currentVideoStream = stream;
            showVideoModal(stream, diaryId, studentId);
        } catch (err) {
            if (statusDiv) statusDiv.innerHTML = '<span class="text-danger">Unable to access camera: ' + err.message + '</span>';
        }
    });
});
function showVideoModal(stream, diaryId, studentId) {
    // Remove existing modal if any
    if (document.getElementById('videoModal')) {
        document.getElementById('videoModal').remove();
    }
    
    const modalHtml = `
        <div id="videoModal" class="video-modal">
            <div class="video-modal-content">
                <div class="video-modal-header">
                    <h4 class="video-modal-title">
                        <i class="fa fa-video-camera"></i> Record Video
                    </h4>
                    <button class="video-modal-close" id="closeVideoModalBtn">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="video-modal-body">
                    <div class="video-preview-container">
                        <video id="videoPreview" class="video-preview" autoplay playsinline muted></video>
                        <div class="video-recording-indicator" id="videoRecordingIndicator" style="display: none;">
                            <span class="recording-dot"></span>
                            <span class="recording-text">RECORDING</span>
                            <span class="recording-timer" id="videoTimer">00:00</span>
                        </div>
                    </div>
                </div>
                <div class="video-modal-footer">
                    <button class="video-btn video-btn-record" id="startVideoRecordBtn">
                        <i class="fa fa-circle"></i> Record
                    </button>
                    <button class="video-btn video-btn-stop" id="stopVideoRecordBtn" style="display: none;">
                        <i class="fa fa-stop"></i> Stop
                    </button>
                    <button class="video-btn video-btn-close" id="cancelVideoBtn">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    const videoElement = document.getElementById('videoPreview');
    videoElement.srcObject = stream;
    
    let mediaRecorder = null;
    let recordedChunks = [];
    let isRecording = false;
    let recordStartTime = 0;
    let timerInterval = null;
    
    const startBtn = document.getElementById('startVideoRecordBtn');
    const stopBtn = document.getElementById('stopVideoRecordBtn');
    const closeBtn = document.getElementById('closeVideoModalBtn');
    const cancelBtn = document.getElementById('cancelVideoBtn');
    const recordingIndicator = document.getElementById('videoRecordingIndicator');
    const timerDisplay = document.getElementById('videoTimer');
    
    // Format time as MM:SS
    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }
    
    // Start recording
    startBtn.onclick = () => {
        recordedChunks = [];
        mediaRecorder = new MediaRecorder(stream, { mimeType: 'video/webm' });
        
        mediaRecorder.ondataavailable = (event) => {
            if (event.data.size > 0) {
                recordedChunks.push(event.data);
            }
        };
        
        mediaRecorder.onstop = async () => {
            const blob = new Blob(recordedChunks, { type: 'video/webm' });
            const duration = Math.floor((Date.now() - recordStartTime) / 1000);
            
            // Stop timer
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
            
            // Show uploading state
            startBtn.disabled = true;
            startBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';
            
            await uploadVideo(blob, diaryId, studentId, duration);
            
            // Close modal after upload
            setTimeout(() => {
                if (currentVideoStream) {
                    currentVideoStream.getTracks().forEach(track => track.stop());
                }
                document.getElementById('videoModal').remove();
            }, 1500);
        };
        
        mediaRecorder.start(1000);
        isRecording = true;
        recordStartTime = Date.now();
        
        // Update UI
        startBtn.style.display = 'none';
        stopBtn.style.display = 'flex';
        recordingIndicator.style.display = 'flex';
        
        // Start timer
        timerInterval = setInterval(() => {
            const elapsed = Math.floor((Date.now() - recordStartTime) / 1000);
            timerDisplay.textContent = formatTime(elapsed);
        }, 1000);
    };
    
    // Stop recording
    stopBtn.onclick = () => {
        if (mediaRecorder && isRecording) {
            mediaRecorder.stop();
            isRecording = false;
            stopBtn.disabled = true;
            stopBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
            recordingIndicator.style.display = 'none';
        }
    };
    
    // Close modal function
    function closeModal() {
        if (mediaRecorder && isRecording) {
            mediaRecorder.stop();
        }
        if (timerInterval) {
            clearInterval(timerInterval);
        }
        if (currentVideoStream) {
            currentVideoStream.getTracks().forEach(track => track.stop());
        }
        document.getElementById('videoModal').remove();
    }
    
    closeBtn.onclick = closeModal;
    cancelBtn.onclick = closeModal;
    
    // Close on backdrop click
    document.querySelector('.video-modal').onclick = (e) => {
        if (e.target === document.querySelector('.video-modal')) {
            closeModal();
        }
    };
}
async function uploadVideo(blob, diaryId, studentId, duration) {
    const statusDiv = document.getElementById(`video-status-${diaryId}`);
    if (statusDiv) statusDiv.innerHTML = '<span class="text-info">Uploading video...</span>';
    
    const formData = new FormData();
    formData.append('video_recording', blob, 'recording.webm');
    formData.append('student_id', studentId);
    formData.append('diary_id', diaryId);
    formData.append('duration', duration);
    
    try {
        const response = await fetch('<?= base_url("student/upload-video") ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const result = await response.json();
        if (result.success) {
            if (statusDiv) statusDiv.innerHTML = '<span class="text-success">✓ Video uploaded! Refreshing...</span>';
            setTimeout(() => location.reload(), 1500);
        } else {
            if (statusDiv) statusDiv.innerHTML = '<span class="text-danger">✗ Upload failed: ' + (result.message || 'Unknown error') + '</span>';
        }
    } catch (err) {
        if (statusDiv) statusDiv.innerHTML = '<span class="text-danger">✗ Network error: ' + err.message + '</span>';
    }
}

// Video file upload
document.querySelectorAll('.video-file-input').forEach(input => {
    input.addEventListener('change', async function() {
        const diaryId = this.dataset.diaryId;
        const studentId = this.dataset.studentId;
        const statusDiv = document.getElementById(`video-status-${diaryId}`);
        
        if (this.files.length === 0) return;
        const file = this.files[0];
        
        if (!file.type.startsWith('video/')) {
            if (statusDiv) statusDiv.innerHTML = '<span class="text-danger">Please select a video file.</span>';
            return;
        }
        if (file.size > 100 * 1024 * 1024) {
            if (statusDiv) statusDiv.innerHTML = '<span class="text-danger">File too large. Max 100MB.</span>';
            return;
        }
        
        await uploadVideo(file, diaryId, studentId, 0);
        this.value = '';
    });
});

// ============================================
// PICTURE CAPTURE
// ============================================
document.querySelectorAll('.picture-capture-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const diaryId = this.dataset.diaryId;
        const studentId = this.dataset.studentId;
        
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            showPictureModal(stream, diaryId, studentId);
        } catch (err) {
            alert('Unable to access camera. Please check permissions.');
        }
    });
});
function showPictureModal(stream, diaryId, studentId) {
    // Remove existing modal if any
    if (document.getElementById('pictureModal')) {
        document.getElementById('pictureModal').remove();
    }
    
    const modalHtml = `
        <div id="pictureModal" class="picture-modal">
            <div class="picture-modal-content">
                <div class="picture-modal-header">
                    <h4 class="picture-modal-title">
                        <i class="fa fa-camera"></i> Capture Picture
                    </h4>
                    <button class="picture-modal-close" id="closePictureModalBtn">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="picture-modal-body">
                    <div class="camera-preview-container">
                        <video id="cameraPreview" class="camera-preview" autoplay playsinline></video>
                        <canvas id="photoCanvas" style="display: none;"></canvas>
                        <div class="camera-guide">
                            <div class="guide-frame"></div>
                        </div>
                    </div>
                    <div id="capturedPreview" class="captured-preview" style="display: none;">
                        <img id="capturedImage" class="captured-image">
                        <div class="captured-actions">
                            <button class="capture-btn recapture-btn" id="recaptureBtn">
                                <i class="fa fa-refresh"></i> Retake
                            </button>
                            <button class="capture-btn submit-btn" id="submitPictureBtn">
                                <i class="fa fa-check"></i> Submit
                            </button>
                        </div>
                    </div>
                </div>
                <div class="picture-modal-footer">
                    <button class="picture-btn capture-btn-main" id="capturePhotoBtn">
                        <i class="fa fa-camera"></i>
                    </button>
                    <button class="picture-btn cancel-btn" id="cancelPictureBtn">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    const videoElement = document.getElementById('cameraPreview');
    const canvas = document.getElementById('photoCanvas');
    const capturedPreview = document.getElementById('capturedPreview');
    const cameraContainer = document.querySelector('.camera-preview-container');
    const captureBtn = document.getElementById('capturePhotoBtn');
    const recaptureBtn = document.getElementById('recaptureBtn');
    const submitBtn = document.getElementById('submitPictureBtn');
    const closeBtn = document.getElementById('closePictureModalBtn');
    const cancelBtn = document.getElementById('cancelPictureBtn');
    const capturedImage = document.getElementById('capturedImage');
    
    videoElement.srcObject = stream;
    
    let capturedImageData = null;
    
    // Function to close modal
    function closeModal() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
        document.getElementById('pictureModal').remove();
    }
    
    // Capture photo
    captureBtn.onclick = () => {
        const context = canvas.getContext('2d');
        
        // Set canvas dimensions to match video
        canvas.width = videoElement.videoWidth;
        canvas.height = videoElement.videoHeight;
        
        // Draw video frame to canvas
        context.drawImage(videoElement, 0, 0, canvas.width, canvas.height);
        
        // Get image data
        capturedImageData = canvas.toDataURL('image/jpeg', 0.9);
        capturedImage.src = capturedImageData;
        
        // Hide camera preview, show captured preview
        cameraContainer.style.display = 'none';
        capturedPreview.style.display = 'block';
        captureBtn.style.display = 'none';
        
        // Add animation
        capturedPreview.classList.add('fade-in');
    };
    
    // Retake photo
    recaptureBtn.onclick = () => {
        // Show camera preview again
        cameraContainer.style.display = 'block';
        capturedPreview.style.display = 'none';
        captureBtn.style.display = 'flex';
        capturedImageData = null;
    };
    
    // Submit photo
    submitBtn.onclick = async () => {
        if (!capturedImageData) return;
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';
        
        const blob = await (await fetch(capturedImageData)).blob();
        const formData = new FormData();
        formData.append('picture', blob, 'capture.jpg');
        formData.append('student_id', studentId);
        formData.append('diary_id', diaryId);
        
        const statusDiv = document.getElementById(`picture-status-${diaryId}`);
        if (statusDiv) statusDiv.innerHTML = '<span class="text-info">Uploading picture...</span>';
        
        try {
            const response = await fetch('<?= base_url("student/upload-picture") ?>', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const result = await response.json();
            if (result.success) {
                if (statusDiv) statusDiv.innerHTML = '<span class="text-success">✓ Picture uploaded! Refreshing...</span>';
                setTimeout(() => location.reload(), 1500);
            } else {
                if (statusDiv) statusDiv.innerHTML = '<span class="text-danger">✗ Upload failed: ' + (result.message || 'Unknown error') + '</span>';
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa fa-check"></i> Submit';
            }
        } catch (err) {
            if (statusDiv) statusDiv.innerHTML = '<span class="text-danger">✗ Network error: ' + err.message + '</span>';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa fa-check"></i> Submit';
        }
    };
    
    // Close modal
    closeBtn.onclick = closeModal;
    cancelBtn.onclick = closeModal;
    
    // Close on backdrop click
    document.querySelector('.picture-modal').onclick = (e) => {
        if (e.target === document.querySelector('.picture-modal')) {
            closeModal();
        }
    };
}

// Picture file upload
document.querySelectorAll('.picture-file-input').forEach(input => {
    input.addEventListener('change', async function() {
        const diaryId = this.dataset.diaryId;
        const studentId = this.dataset.studentId;
        const statusDiv = document.getElementById(`picture-status-${diaryId}`);
        
        if (this.files.length === 0) return;
        const file = this.files[0];
        
        if (!file.type.startsWith('image/')) {
            if (statusDiv) statusDiv.innerHTML = '<span class="text-danger">Please select an image file.</span>';
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            if (statusDiv) statusDiv.innerHTML = '<span class="text-danger">File too large. Max 5MB.</span>';
            return;
        }
        
        const formData = new FormData();
        formData.append('picture', file);
        formData.append('student_id', studentId);
        formData.append('diary_id', diaryId);
        
        if (statusDiv) statusDiv.innerHTML = '<span class="text-info">Uploading picture...</span>';
        
        try {
            const response = await fetch('<?= base_url("student/upload-picture") ?>', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const result = await response.json();
            if (result.success) {
                if (statusDiv) statusDiv.innerHTML = '<span class="text-success">✓ Picture uploaded! Refreshing...</span>';
                setTimeout(() => location.reload(), 1500);
            } else {
                if (statusDiv) statusDiv.innerHTML = '<span class="text-danger">✗ Upload failed: ' + (result.message || 'Unknown error') + '</span>';
            }
        } catch (err) {
            if (statusDiv) statusDiv.innerHTML = '<span class="text-danger">✗ Network error: ' + err.message + '</span>';
        }
        
        this.value = '';
    });
});


// ============================================
// PRAYER TRACKING
// ============================================
let currentPrayers = {
    fajr: 0, dhuhr: 0, asr: 0, maghrib: 0, isha: 0
};

function loadPrayerStatus() {
    const studentId = <?= $activeStudentId ?>;
    fetch('<?= base_url("student/get-prayer-status") ?>?student_id=' + studentId + '&date=<?= date('Y-m-d') ?>', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentPrayers = data.prayers;
            updatePrayerUI();
        }
    })
    .catch(err => console.error('Error loading prayer status:', err));
}

function updatePrayerUI() {
    let offeredCount = 0;
    const prayerItems = document.querySelectorAll('.prayer-item');
    
    prayerItems.forEach(item => {
        const prayer = item.dataset.prayer;
        if (currentPrayers[prayer] === 1) {
            item.classList.add('offered');
            offeredCount++;
        } else {
            item.classList.remove('offered');
        }
    });
    
    const percentage = (offeredCount / 5) * 100;
    document.getElementById('prayerProgressBar').style.width = percentage + '%';
    document.getElementById('prayerCount').innerText = offeredCount;
}

document.querySelectorAll('.prayer-item').forEach(item => {
    item.addEventListener('click', function() {
        const prayer = this.dataset.prayer;
        const studentId = <?= $activeStudentId ?>;
        const newValue = currentPrayers[prayer] === 1 ? 0 : 1;
        
        fetch('<?= base_url("student/save-prayer") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                student_id: studentId,
                prayer_date: '<?= date('Y-m-d') ?>',
                prayer_name: prayer,
                value: newValue,
                csrf_test_name: document.querySelector('input[name="csrf_test_name"]')?.value || ''
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentPrayers[prayer] = newValue;
                updatePrayerUI();
                loadPrayerStats();
            }
        })
        .catch(err => console.error('Error saving prayer:', err));
    });
});

function loadPrayerStats() {
    const studentId = <?= $activeStudentId ?>;
    fetch('<?= base_url("student/get-prayer-stats") ?>?student_id=' + studentId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('weeklyStreak').innerText = data.weekly_streak || 0;
            document.getElementById('monthlyStreak').innerText = data.monthly_streak || 0;
            document.getElementById('totalDays').innerText = data.total_days || 0;
        }
    })
    .catch(err => console.error('Error loading prayer stats:', err));
}

// Initialize
loadPrayerStatus();
loadPrayerStats();
</script>

<?= $this->endSection() ?>