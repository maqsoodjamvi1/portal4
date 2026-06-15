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

    /* —— Parent hub-only dashboard shell —— */
    .parent-dash-page {
        min-height: calc(100vh - 4.5rem);
        margin: 0 auto;
        max-width: 1180px;
        padding: 6px 6px 28px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: linear-gradient(165deg, #eef2ff 0%, #f8fafc 38%, #e2e8f0 100%);
        border-radius: 0;
    }
    @media (min-width: 768px) {
        .parent-dash-page {
            padding: 12px 20px 36px;
            margin-top: 6px;
            margin-bottom: 12px;
            border-radius: 24px;
            box-shadow: 0 12px 40px rgba(15, 23, 42, 0.08);
            border: 1px solid rgba(148, 163, 184, 0.25);
            gap: 16px;
        }
    }

    .parent-dash-context {
        border-radius: 16px !important;
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, 0.95) !important;
        box-shadow: 0 6px 24px rgba(15, 23, 42, 0.07) !important;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%) !important;
    }
    .parent-dash-context .card-body {
        padding: 14px 16px !important;
    }
    @media (min-width: 768px) {
        .parent-dash-context .card-body {
            padding: 18px 22px !important;
        }
    }
    .parent-dash-context-avatar {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        overflow: hidden;
        flex-shrink: 0;
        border: 2px solid #e2e8f0;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.25rem;
        font-weight: 700;
    }
    .parent-dash-context-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .parent-dash-context-welcome {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 2px;
    }
    .parent-dash-context-name {
        font-size: 1.15rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.25;
    }
    @media (min-width: 768px) {
        .parent-dash-context-name { font-size: 1.35rem; }
    }

    .parent-dash-hub-badge {
        border-radius: 999px;
        padding: 0.45em 0.85em;
        font-weight: 600;
        font-size: 0.72rem;
        letter-spacing: 0.03em;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.2);
    }

    .parent-dash-footer {
        margin-top: auto;
        padding: 16px 12px 8px;
        text-align: center;
        font-size: 0.8rem;
        color: #64748b;
        line-height: 1.45;
        max-width: 520px;
        margin-left: auto;
        margin-right: auto;
    }
    .parent-dash-footer kbd {
        font-size: 0.7rem;
        padding: 0.1rem 0.35rem;
        border-radius: 4px;
        background: #e2e8f0;
        color: #475569;
    }

    /* Root fills viewport on parent mobile hub (no page scroll) */
    body.parent-portal-client .parent-dash-root {
        flex: 1 1 auto;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    body.parent-portal-client .parent-dash-page {
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
    }
    body.parent-portal-client .parent-dash-app {
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    body.parent-portal-client .parent-dash-hub-wrap {
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    body.parent-portal-client .parent-dash-hub-wrap .card-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    body.parent-portal-client .parent-hub-grid {
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
        align-content: center;
    }

    /* Kids: photo strip only (no “select” card) */
    .parent-dash-kids-strip {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        gap: 10px;
        padding: 6px 4px 4px;
    }
    .kid-bubble {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid #e2e8f0;
        flex-shrink: 0;
        display: block;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    }
    .kid-bubble:hover {
        transform: scale(1.06);
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.2);
    }
    .kid-bubble.active {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.25);
    }
    .kid-bubble img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .kid-bubble-ph {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: #fff;
        font-weight: 700;
        font-size: 1.1rem;
    }

    @media (max-width: 767.98px) {
        body.parent-portal-client .wrapper {
            min-height: 100vh;
            min-height: 100dvh;
            max-height: 100dvh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        body.parent-portal-client .content-wrapper {
            flex: 1 1 auto;
            min-height: 0 !important;
            overflow: hidden !important;
            display: flex;
            flex-direction: column;
        }
        .parent-dash-page {
            min-height: 0 !important;
            gap: 6px !important;
            padding: 4px 4px 6px !important;
            margin: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            border: none !important;
        }
        .school-header {
            padding: 8px 12px !important;
            border-radius: 14px !important;
        }
        .school-name {
            font-size: 1.1rem !important;
        }
        .logo-wrapper {
            width: 52px !important;
            height: 52px !important;
            margin-right: 10px !important;
            border-radius: 12px !important;
        }
        .school-logo {
            width: 40px !important;
            height: 40px !important;
        }
        .parent-dash-context .card-body {
            padding: 10px 12px !important;
        }
        .parent-dash-context-name {
            font-size: 1.05rem !important;
        }
        .parent-dash-kids-strip {
            gap: 8px;
            padding: 2px 0;
        }
        .kid-bubble {
            width: 42px;
            height: 42px;
        }
        .parent-hub-grid {
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 6px !important;
        }
        .parent-hub-tile {
            min-height: 68px !important;
            padding: 6px 4px 8px !important;
            border-radius: 12px !important;
        }
        .parent-hub-icon {
            width: 32px !important;
            height: 32px !important;
            font-size: 0.85rem !important;
            border-radius: 10px !important;
        }
        .parent-hub-label {
            font-size: 8px !important;
        }
        .parent-dash-hub-wrap .card-body {
            padding: 8px 8px 10px !important;
        }
        .parent-dash-hub-badge {
            display: none !important;
        }
        .parent-dash-footer {
            display: none !important;
        }
    }

    .school-header {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 55%, #1e3a5f 100%);
        border-radius: 20px;
        padding: 12px 16px;
        margin-bottom: 0;
        color: white;
        display: flex;
        align-items: center;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.08);
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
        padding-bottom: 4px;
        margin-bottom: 2px;
        -webkit-overflow-scrolling: touch;
    }

    .parent-dash-students-card > .card-header.bg-white {
        padding: 0.35rem 0.75rem !important;
    }
    .parent-dash-students-card > .card-body {
        padding: 0.2rem 0.65rem 0.35rem !important;
    }
    .parent-dash-students-card .select-children-label {
        margin-bottom: 0;
        font-size: 0.95rem;
        font-weight: 600;
        line-height: 1.2;
    }

    .kid-card {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        width: 72px;
        min-height: 0;
        height: auto;
        margin-right: 6px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        border-radius: 10px;
        padding: 3px 3px 5px;
        gap: 1px;
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
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e2e8f0;
        margin: 0;
        flex-shrink: 0;
    }

    .kid-avatar-placeholder {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        flex-shrink: 0;
        border: 2px solid #e2e8f0;
    }

    .kid-avatar-placeholder span {
        font-size: 15px;
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
        font-size: 10px;
        margin: 0;
        padding-top: 1px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 68px;
        line-height: 1.15;
    }

    .kid-class {
        font-size: 8px;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 68px;
        line-height: 1.15;
        margin: 0;
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

    /* Legacy bar (unused on hub layout — kept if re-enabled) */
    .active-student-sticky {
        display: none;
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
        border-start: 4px solid #4f46e5;
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
        .school-header {
            margin-bottom: 6px;
        }
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
            width: 70px;
            min-height: 0;
            padding: 3px 2px 4px;
            margin-right: 6px;
        }

        .kid-avatar,
        .kid-avatar-placeholder {
            width: 38px;
            height: 38px;
        }

        .kid-avatar-placeholder span {
            font-size: 14px;
        }

        .kid-name {
            font-size: 9px;
            max-width: 64px;
        }

        .kid-class {
            font-size: 8px;
            max-width: 64px;
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
            width: 66px;
            min-height: 0;
            padding: 2px 2px 4px;
            margin-right: 5px;
        }

        .kid-avatar,
        .kid-avatar-placeholder {
            width: 36px;
            height: 36px;
        }

        .kid-avatar-placeholder span {
            font-size: 13px;
        }

        .kid-name {
            font-size: 9px;
            max-width: 62px;
        }

        .kid-class {
            font-size: 7px;
            max-width: 62px;
        }

        .kid-age {
            font-size: 7px;
        }

        .active-student-sticky {
            font-size: 11px;
            padding: 6px 10px;
            margin-bottom: 6px;
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
    border-start: 3px solid #4f46e5;
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

    /* ============================================
       PARENT DASH — app-style hub & panels
    ============================================ */
    .parent-dash-app {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        min-height: 0;
        width: 100%;
        max-width: none;
        margin: 0;
    }
    .parent-dash-hub-wrap {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        border-radius: 18px !important;
        box-shadow: 0 8px 32px rgba(15, 23, 42, 0.1) !important;
        border: 1px solid rgba(226, 232, 240, 0.9) !important;
        background: #fff !important;
        min-height: 0;
    }
    .parent-dash-hub-wrap .card-body {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
    }
    @media (max-width: 767.98px) {
        .parent-dash-hub-wrap {
            border-radius: 16px !important;
        }
    }
    .parent-hub-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 10px;
        flex: 1 1 auto;
        align-content: start;
    }
    @media (min-width: 400px) {
        .parent-hub-grid {
            grid-template-columns: repeat(auto-fill, minmax(108px, 1fr));
            gap: 12px;
        }
    }
    @media (min-width: 768px) {
        .parent-hub-grid {
            grid-template-columns: repeat(auto-fill, minmax(128px, 1fr));
            gap: 16px;
        }
    }
    @media (min-width: 1200px) {
        .parent-hub-grid {
            grid-template-columns: repeat(auto-fill, minmax(148px, 1fr));
            gap: 18px;
        }
    }
    .parent-hub-tile {
        border: 1px solid #e2e8f0;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 16px;
        padding: 12px 8px 14px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 96px;
        cursor: pointer;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background 0.18s ease;
        -webkit-tap-highlight-color: transparent;
        width: 100%;
        text-align: center;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }
    @media (min-width: 992px) {
        .parent-hub-tile {
            min-height: 112px;
            padding: 16px 12px 18px;
            border-radius: 18px;
        }
    }
    .parent-hub-tile:hover {
        border-color: #c7d2fe;
        box-shadow: 0 8px 24px rgba(79, 70, 229, 0.12);
        transform: translateY(-2px);
        background: linear-gradient(180deg, #ffffff 0%, #f1f5ff 100%);
    }
    .parent-hub-tile:active {
        transform: scale(0.98);
    }
    .parent-hub-tile.active {
        background: linear-gradient(145deg, #eef2ff, #e0e7ff);
        box-shadow: 0 0 0 2px #4f46e5;
    }
    a.parent-hub-tile {
        text-decoration: none;
        color: inherit;
        box-sizing: border-box;
    }
    a.kid-card {
        text-decoration: none;
        color: inherit;
    }
    .parent-hub-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.05rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    }
    @media (min-width: 992px) {
        .parent-hub-icon {
            width: 52px;
            height: 52px;
            font-size: 1.2rem;
            border-radius: 16px;
        }
    }
    .parent-hub-icon.bg-fee { background: linear-gradient(135deg, #6366f1, #4f46e5); }
    .parent-hub-icon.bg-att { background: linear-gradient(135deg, #0ea5e9, #0284c7); }
    .parent-hub-icon.bg-bmi { background: linear-gradient(135deg, #f43f5e, #e11d48); }
    .parent-hub-icon.bg-ds { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .parent-hub-icon.bg-res { background: linear-gradient(135deg, #22c55e, #16a34a); }
    .parent-hub-icon.bg-diary { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
    .parent-hub-icon.bg-bag { background: linear-gradient(135deg, #14b8a6, #0d9488); }
    .parent-hub-icon.bg-pray { background: linear-gradient(135deg, #10b981, #059669); }
    .parent-hub-icon.bg-profile { background: linear-gradient(135deg, #64748b, #334155); }
    .parent-hub-icon.bg-quiz { background: linear-gradient(135deg, #d946ef, #86198f); }
    .parent-hub-label {
        font-size: 11px;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.25;
        max-width: 100%;
        word-break: break-word;
    }
    @media (min-width: 992px) {
        .parent-hub-label {
            font-size: 12px;
        }
    }
    .parent-dash-panels .parent-dash-panel > .card {
        border-radius: 16px;
        overflow: hidden;
    }
    @media (max-width: 767.98px) {
        .parent-dash-panels .parent-dash-panel > .card {
            border-radius: 14px;
        }
        .school-header {
            border-radius: 16px;
            padding: 12px;
            margin-bottom: 6px;
        }
        .school-name { font-size: 1.35rem !important; }
        .logo-wrapper { width: 64px; height: 64px; margin-right: 10px; }
        .kid-card {
            width: 72px;
            min-height: 0;
            padding: 4px 3px 5px;
        }
        .kid-avatar, .kid-avatar-placeholder {
            width: 40px;
            height: 40px;
        }
    }

    @media (min-width: 768px) {
        .kids-scroll {
            white-space: normal;
            overflow-x: visible;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: flex-start;
            gap: 12px;
            padding: 10px 8px 14px;
        }
        .kid-card {
            margin-right: 0;
            width: 92px;
            padding: 10px 8px 12px;
            border-radius: 14px;
        }
        .kid-avatar,
        .kid-avatar-placeholder {
            width: 52px;
            height: 52px;
        }
        .kid-avatar-placeholder span {
            font-size: 18px;
        }
        .kid-name {
            font-size: 11px;
            max-width: 88px;
        }
        .kid-class {
            font-size: 9px;
            max-width: 88px;
        }
        .parent-dash-students-card > .card-body {
            padding: 0.5rem 1rem 0.75rem !important;
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

<div class="parent-dash-root">
<div class="parent-dash-page">

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
        <?php
        $__plang = strtolower(trim((string) (session()->get('language') ?: 'en')));
        $__portalTag = ($__plang === 'ur') ? 'والدین کا پورٹل' : 'Parent Portal';
        ?>
        <p class="mb-0 opacity-75 small"><?= esc($__portalTag) ?></p>
    </div>
</div>

<?php
$currentLang = session()->get('language') ?: 'en';
$portalLang   = strtolower(trim((string) $currentLang));
$isUrdu       = ($portalLang === 'ur');
$studentAge = 0;
if (!empty($studentInfo) && isset($studentInfo->date_of_birth)) {
    $studentAge = date_diff(date_create($studentInfo->date_of_birth), date_create('today'))->y;
}
$campusPrayerStartAge = isset($campusInfo->prayer_tracking_start_age) ? $campusInfo->prayer_tracking_start_age : 7;
$campusPrayerMandatoryAge = isset($campusInfo->prayer_tracking_mandatory_age) ? $campusInfo->prayer_tracking_mandatory_age : 10;
$isEligibleForPrayer = $studentAge >= $campusPrayerStartAge;
$isMandatory = $studentAge >= $campusPrayerMandatoryAge;
$dsDash = $dashboardUnannouncedDatesheet ?? ['show' => false];

$welcomePhrase   = $isUrdu ? 'خوش آمدید' : 'Welcome';
$hubSubtitle     = $isUrdu ? 'فیس، حاضری، شیڈول اور مزید' : 'Fees, attendance, datesheet & more';
$hubSubtitleLong = $isUrdu ? 'فیس، حاضری، شیڈول اور مزید — ایک ٹیپ میں' : 'Fees, attendance, datesheet & more — one tap away';
$footerHint      = $isUrdu ? 'ذیل کے مینو سے سروس کھولیں۔ بچے تبدیل کرنے کے لیے اوپر تصاویر پر ٹیپ کریں۔' : 'Tap a tile to open that service. Tap a photo above to switch the active child.';
?>

<div class="card parent-dash-context border-0 shadow-sm">
    <div class="card-body">
        <div class="min-w-0">
            <div class="parent-dash-context-welcome"><?= esc($welcomePhrase) ?></div>
            <div class="parent-dash-context-name text-truncate"><?= esc($name ?? '') ?></div>
        </div>
    </div>
</div>

<?php if (!empty($children)): ?>
    <div class="parent-dash-kids-strip" aria-hidden="false">
                <?php foreach ($children as $child): ?>
                    <?php
                    $isActiveBubble = $activeStudentId === (int) $child['student_id'];
                    $photoOk = ! empty($child['profile_photo_url']) && $child['profile_photo_url'] !== base_url();
                    ?>
                    <a class="kid-bubble <?= $isActiveBubble ? 'active' : '' ?>"
                       href="<?= esc(base_url('student/switch/' . (int) $child['student_id'] . '?to=' . rawurlencode('student/dashboard'))) ?>"
                       title="<?= esc($child['name']) ?>"
                       aria-label="<?= esc($child['name']) ?>">
                        <?php if ($photoOk): ?>
                            <img src="<?= esc($child['profile_photo_url']) ?>" alt="" loading="lazy">
                        <?php else: ?>
                            <span class="kid-bubble-ph"><?= esc(strtoupper(substr($child['name'], 0, 1))) ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="parent-dash-app" id="parentDashApp">
    <div class="card shadow-sm border-0 parent-dash-hub-wrap mb-0">
        <div class="card-body py-3 px-3 px-sm-4 py-md-4">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
                <div class="min-w-0">
                    <div class="text-uppercase small text-muted fw-bold letter-spacing-1"><?= $isUrdu ? 'مینو' : 'Quick menu' ?></div>
                    <div class="small text-muted mt-1 mb-0 d-md-none"><?= esc($hubSubtitle) ?></div>
                    <div class="small text-muted mt-1 mb-0 d-none d-md-block"><?= esc($hubSubtitleLong) ?></div>
                </div>
                <?php if (! empty($activeStudentId)): ?>
                    <span class="badge text-bg-primary parent-dash-hub-badge align-self-start"><?= $isUrdu ? 'طالب علم منتخب' : 'Child linked' ?></span>
                <?php endif; ?>
            </div>
            <div class="parent-hub-grid" role="navigation" aria-label="<?= $isUrdu ? 'ڈیش بورڈ مینو' : 'Dashboard sections' ?>">
                <a href="<?= base_url('student/profile') ?>" class="parent-hub-tile">
                    <span class="parent-hub-icon bg-profile"><i class="fa fa-user-circle" aria-hidden="true"></i></span>
                    <span class="parent-hub-label"><?= $isUrdu ? 'پروفائل' : 'Profile' ?></span>
                </a>
                <a href="<?= base_url('student/fees') ?>" class="parent-hub-tile">
                    <span class="parent-hub-icon bg-fee"><i class="fa fa-credit-card" aria-hidden="true"></i></span>
                    <span class="parent-hub-label"><?= $isUrdu ? 'فیس' : 'Fees' ?></span>
                </a>
                <a href="<?= base_url('student/attendance') ?>" class="parent-hub-tile">
                    <span class="parent-hub-icon bg-att"><i class="fa fa-calendar-check" aria-hidden="true"></i></span>
                    <span class="parent-hub-label"><?= $isUrdu ? 'حاضری' : 'Attendance' ?></span>
                </a>
                <a href="<?= base_url('student/dashboard/section/bmi') ?>" class="parent-hub-tile">
                    <span class="parent-hub-icon bg-bmi"><i class="fa fa-heartbeat" aria-hidden="true"></i></span>
                    <span class="parent-hub-label"><?= $isUrdu ? 'صحت / BMI' : 'Health / BMI' ?></span>
                </a>
                <?php if (!empty($activeStudentId) && !empty($dsDash['show'])): ?>
                <a href="<?= base_url('student/datesheet') ?>" class="parent-hub-tile">
                    <span class="parent-hub-icon bg-ds"><i class="fa fa-calendar-alt" aria-hidden="true"></i></span>
                    <span class="parent-hub-label"><?= $isUrdu ? 'شیڈول' : 'Datesheet' ?></span>
                </a>
                <?php endif; ?>
                <?php if (!empty($activeStudentId)): ?>
                <a href="<?= base_url('student/results') ?>" class="parent-hub-tile">
                    <span class="parent-hub-icon bg-res"><i class="fa fa-chart-line" aria-hidden="true"></i></span>
                    <span class="parent-hub-label"><?= $isUrdu ? 'نتائج' : 'Results' ?></span>
                </a>
                <a href="<?= base_url('student/quizzes/all') ?>" class="parent-hub-tile">
                    <span class="parent-hub-icon bg-quiz"><i class="fa fa-question-circle" aria-hidden="true"></i></span>
                    <span class="parent-hub-label"><?= $isUrdu ? 'کوئز' : 'Quizzes' ?></span>
                </a>
                <a href="<?= base_url('student/dashboard/section/diary') ?>" class="parent-hub-tile">
                    <span class="parent-hub-icon bg-diary"><i class="fa fa-book-open" aria-hidden="true"></i></span>
                    <span class="parent-hub-label"><?= $isUrdu ? 'ڈائری' : 'Diary' ?></span>
                </a>
                <a href="<?= base_url('student/dashboard/section/bag') ?>" class="parent-hub-tile">
                    <span class="parent-hub-icon bg-bag"><i class="fa fa-bag-shopping" aria-hidden="true"></i></span>
                    <span class="parent-hub-label"><?= $isUrdu ? 'بیگ' : 'Bag pack' ?></span>
                </a>
                <?php if (!empty($isEligibleForPrayer)): ?>
                <a href="<?= base_url('student/dashboard/section/prayers') ?>" class="parent-hub-tile">
                    <span class="parent-hub-icon bg-pray"><i class="fa fa-mosque" aria-hidden="true"></i></span>
                    <span class="parent-hub-label"><?= esc(lang('ParentPortal.hub_namaz_short')) ?></span>
                </a>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div><!-- #parentDashApp -->

<div class="parent-dash-footer">
    <?= esc($footerHint) ?>
</div>

</div><!-- .parent-dash-page -->

</div><!-- .parent-dash-root -->

<?= csrf_field() ?>

<script>
/* Parent hub opens separate pages; student switch uses links with ?to= */
</script>

<?= $this->endSection() ?>