<style>
/* Reset and Base Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* A4 Landscape Print Styles */
@media print {
    @page {
        size: A4 landscape;
        margin: 0.3in;
    }
    
    body {
        margin: 0;
        padding: 0;
        background: white;
        font-family: 'Arial', 'Helvetica', sans-serif;
    }
    
    .no-print {
        display: none !important;
    }
    
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}

/* Three Copies Layout with Dotted Vertical Line */
.slip-row {
    display: flex;
    justify-content: space-between;
    position: relative;
}

.slip-col {
    flex: 1;
    position: relative;
    border-right: 1px dotted #000;
}

.slip-col:last-child {
    border-right: none;
}

/* Chalan Wrapper */
.chalan-wrapper {
    border: 2px solid #000;
    background: #fff;
    padding: 0.2in;
    margin: 0;
    font-family: 'Arial', 'Helvetica', sans-serif;
    font-size: 11pt; /* Increased by 2 points from 9pt */
    page-break-inside: avoid;
}

/* SECTION 1: HEADER SECTION */
.chalan-header {
    border-bottom: 2px solid #000;
    margin-bottom: 0.15in;
}

/* Top Row: School Name Only - Full Width */
.school-name-row {
    width: 90%;
    text-align: center;
    padding: 0.03in 0 0.06in 0;
    border-bottom: 1px solid #ddd;
    margin-bottom: 0.08in;
}

.school-name {
    font-size: 14pt; /* Increased by 2 points from 16pt */
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 2px;
    white-space: nowrap;
    overflow: visible;
    width: 100%;
    text-align: center;
    line-height: 1.2;
    color: #000;
}

/* Middle Section: Logo and Campus Info Only */
.header-middle {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    padding: 0.03in 0;
    gap: 0.2in;
}

.header-left {
    width: 0.9in;
    flex-shrink: 0;
    text-align: center;
}

.header-logo img {
    max-width: 0.75in;
    max-height: 0.75in;
    object-fit: contain;
}

.logo-placeholder {
    width: 0.75in;
    height: 0.75in;
    border: 1px solid #000;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 10pt; /* Increased */
    background: #fff;
    color: #000;
}

.header-center {
    flex: 1;
    text-align: center;
    max-width: 70%;
}

.campus-name {
    font-size: 11pt; /* Increased from 11pt */
    font-weight: 600;
    margin-bottom: 0.04in;
    white-space: nowrap;
    overflow: visible;
    color: #000;
}

.bank-details,
.account-details {
    font-size: 10pt; /* Increased from 8pt */
    color: #000;
    margin-top: 0.02in;
    white-space: nowrap;
    overflow: visible;
}

/* SECTION 2: STUDENT INFORMATION */
.student-info-section {
    border-bottom: 1px solid #000;
    margin-bottom: 0.1in;
    padding-bottom: 0.06in;
}

.section-title {
    font-size: 12pt; /* Increased from 10pt */
    font-weight: bold;
    background: #fff;
    padding: 0.03in 0.08in;
    margin-bottom: 0.06in;
    border-left: 3px solid #000;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #000;
}

.info-grid-family,
.info-grid-student {
    display: flex;
    flex-direction: column;
    gap: 0.04in;
}

/* Single Row Layout */
.info-row-single {
    display: flex;
    border-bottom: 1px dotted #ccc;
    padding: 0.02in 0;
}

.info-row-single .info-label {
    width: 20%;
    font-weight: 600;
    font-size: 10pt; /* Increased */
    flex-shrink: 0;
    color: #000;
}

.info-row-single .info-value {
    width: 80%;
    font-weight: normal;
    font-size: 10pt; /* Increased */
    white-space: normal;
    word-wrap: break-word;
    color: #000;
}

/* Father Name Row with Family ID Right Aligned */
.info-row-father {
    display: flex;
    align-items: center;
    border-bottom: 1px dotted #ccc;
    padding: 0.02in 0;
    gap: 0.08in;
}

.info-label-inline {
    font-weight: 600;
    font-size: 10pt; /* Increased */
    white-space: nowrap;
    color: #000;
}

.father-name-value {
    flex: 1;
    font-weight: normal;
    font-size: 10pt; /* Increased */
    color: #000;
}

.family-id-right {
    font-weight: normal;
    font-size: 10pt; /* Increased */
    color: #000;
    white-space: nowrap;
}

/* Triple Row Layout */
.info-row-triple {
    display: flex;
    gap: 0.2in;
    border-bottom: 1px dotted #ccc;
    padding: 0.02in 0;
}

.info-item {
    flex: 1;
    min-width: 0;
    overflow: visible;
}

.info-value-inline {
    font-weight: normal;
    font-size: 10pt; /* Increased */
    word-wrap: break-word;
    overflow: visible;
    color: #000;
}

.class-badge {
    font-weight: normal;
    margin-left: 0.06in;
    font-size: 10pt; /* Increased */
    white-space: nowrap;
    color: #000;
}

.due-date {
    font-weight: bold;
}

.left-align {
    text-align: left !important;
}
/* SECTION 3: FEE DETAIL TABLE - Conditional Columns */
.fee-detail-section {
    margin-bottom: 0.1in;
}

.fee-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 10pt;
    table-layout: fixed;
}

.fee-table th,
.fee-table td {
    border: 1px solid #000;
    padding: 0.05in 0.04in;
    vertical-align: middle;
    color: #000;
}

.fee-table th {
    background: #fff;
    font-weight: bold;
    text-align: center;
    font-size: 10pt;
    color: #000;
}

.fee-table td {
    text-align: left;
    word-wrap: break-word;
    color: #000;
}

.fee-table .text-center {
    text-align: center;
}

.fee-table .text-right {
    text-align: right;
}

/* Column widths when discount is shown */
.col-sr {
    width: 5%;
}

.col-particulars {
    width: 50%;
}

.col-amount,
.col-discount,
.col-payable {
    width: 15%;
}

/* Column widths when discount is hidden - single column for payable */
.col-payable-full {
    width: 45%;
}

.particulars-cell strong {
    font-weight: 600;
}

.fee-month-small {
    font-size: 9pt;
    margin-left: 0.04in;
    color: #000;
}

.payable-amount {
    font-weight: bold;
}

/* Blank rows - completely empty */
.blank-row td {
    border: 1px solid #000;
    height: 0.35in;
}

/* SECTION 4: FEE SUMMARY */
.fee-summary-section {
    border-top: 2px solid #000;
    border-bottom: 2px solid #000;
    padding: 0.08in 0;
    margin-bottom: 0.1in;
    text-align: right;
}

.summary-item {
    display: inline-block;
    min-width: 2.2in;
    padding: 0.06in 0.12in;
    text-align: center;
    background: #fff;
}

.summary-item.total {
    background: #fff;
    border: 2px solid #000;
    font-weight: bold;
}

.summary-item.warning-total {
    background: #fff;
    border: 2px solid #000;
    margin-left: 0.15in;
}

.summary-label {
    font-size: 11pt; /* Increased */
    margin-bottom: 0.03in;
    font-weight: 600;
    white-space: nowrap;
    color: #000;
}

.summary-value {
    font-size: 14pt; /* Increased */
    font-weight: bold;
    white-space: nowrap;
    color: #000;
}

.fine-note {
    font-size: 9pt; /* Increased */
    margin-top: 0.03in;
    font-weight: normal;
    white-space: nowrap;
    color: #000;
}

/* Payment History Table */
.payment-history-section {
    margin-top: 0.1in;
    margin-bottom: 0.06in;
}

.history-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 8pt; /* Increased */
    table-layout: fixed;
}

.history-table th,
.history-table td {
    border: 1px solid #000;
    padding: 0.03in;
    text-align: center;
    word-wrap: break-word;
    color: #000;
}

.history-table th {
    background: #fff;
    font-weight: bold;
}

.history-label {
    font-weight: bold;
    background: #fff;
}

.total-amount {
    font-weight: bold;
}

/* Footer Elements */
.footer-note,
.footer-line {
    font-size: 9pt; /* Increased */
    padding: 0.03in;
    margin-top: 0.03in;
    border-top: 1px dashed #999;
    text-align: center;
    color: #000;
}

.copy-label {
    text-align: center;
    font-weight: bold;
    font-size: 10pt; /* Increased */
    margin-top: 0.05in;
    padding: 0.03in;
    background: #fff;
    border: 1px solid #000;
    text-transform: uppercase;
    color: #000;
}

/* Responsive adjustments for screen view */
@media screen {
    body {
        background: #f0f0f0;
        padding: 20px;
    }
    
    .chalan-wrapper {
        max-width: 11in;
        margin: 0 auto 20px auto;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
}

/* Ensure proper page breaks */
.chalan-wrapper,
.student-info-section,
.fee-detail-section,
.fee-summary-section,
.payment-history-section {
    page-break-inside: avoid;
}
</style>