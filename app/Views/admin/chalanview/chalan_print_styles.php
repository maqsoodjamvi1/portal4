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
        margin: 0.2in;
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

/* Chalan Wrapper — compact for A4 landscape + discount columns */
.chalan-wrapper {
    border: 2px solid #000;
    background: #fff;
    padding: 0.1in 0.12in;
    margin: 0;
    font-family: 'Arial', 'Helvetica', sans-serif;
    font-size: 8.5pt;
    line-height: 1.2;
    page-break-inside: avoid;
}

/* SECTION 1: HEADER — logo + text stack */
.chalan-header {
    border-bottom: 2px solid #000;
    margin-bottom: 0.1in;
    padding-bottom: 0.05in;
}

.header-brand {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.1in;
    width: 100%;
    padding: 0.04in 0;
}

.header-logo-box {
    flex: 0 0 0.58in;
    width: 0.58in;
    text-align: center;
    padding: 0.03in 0;
    box-sizing: border-box;
}

.header-logo-box img {
    max-width: 0.52in;
    max-height: 0.52in;
    object-fit: contain;
    display: block;
    margin: 0 auto;
}

.logo-placeholder {
    width: 0.5in;
    height: 0.5in;
    border: 1px solid #000;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 7pt;
    margin: 0 auto;
    background: #fff;
    color: #000;
}

.header-brand-text {
    flex: 1;
    min-width: 0;
    text-align: center;
    padding-top: 0.02in;
}

.school-name {
    font-size: 11pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    white-space: nowrap;
    overflow: visible;
    line-height: 1.15;
    color: #000;
}

.campus-line {
    font-size: 8.5pt;
    font-weight: 600;
    margin-top: 0.03in;
    color: #000;
    line-height: 1.2;
}

.bank-line,
.acc-line {
    font-size: 7.5pt;
    color: #000;
    margin-top: 0.015in;
    line-height: 1.2;
}

/* SECTION 2: STUDENT INFORMATION — one readable type scale */
.student-info-section {
    border-bottom: 1px solid #000;
    margin-bottom: 0.06in;
    padding-bottom: 0.04in;
    font-size: 8.25pt;
    line-height: 1.25;
}

.section-title {
    font-size: 9pt;
    font-weight: bold;
    background: #fff;
    padding: 0.02in 0.06in;
    margin-bottom: 0.04in;
    border-left: 3px solid #000;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #000;
}

.info-grid-family,
.info-grid-student {
    display: flex;
    flex-direction: column;
    gap: 0.02in;
}

/* Single Row Layout */
.info-row-single {
    display: flex;
    border-bottom: 1px dotted #ccc;
    padding: 0.02in 0;
}

.info-row-single .info-label {
    width: 18%;
    font-weight: 600;
    font-size: 8.25pt;
    flex-shrink: 0;
    color: #000;
}

.info-row-single .info-value {
    width: 82%;
    font-weight: normal;
    font-size: 8.25pt;
    white-space: normal;
    word-wrap: break-word;
    color: #000;
}

.student-name-line {
    font-weight: 700;
    font-size: 8.75pt;
    letter-spacing: 0.02em;
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
    font-size: 8.25pt;
    white-space: nowrap;
    color: #000;
}

.father-name-value {
    flex: 1;
    font-weight: normal;
    font-size: 8.25pt;
    color: #000;
    min-width: 0;
}

.family-id-right {
    font-weight: 600;
    font-size: 8.25pt;
    color: #000;
    white-space: nowrap;
    font-variant-numeric: tabular-nums;
}

/* Issue / Due / Month — left / center / right (dates stay LTR for readability) */
.info-row-dates-triple {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 0.02in;
    align-items: baseline;
    border-bottom: 1px dotted #ccc;
    padding: 0.04in 0;
    font-size: 8.25pt;
    line-height: 1.2;
    color: #000;
    direction: ltr;
    unicode-bidi: embed;
}

.info-row-dates-triple .date-cell {
    min-width: 0;
    white-space: nowrap;
}

.info-row-dates-triple .date-cell-left {
    text-align: left;
}

.info-row-dates-triple .date-cell-center {
    text-align: center;
}

.info-row-dates-triple .date-cell-right {
    text-align: right;
}

.info-row-dates-triple .date-lbl {
    font-weight: 600;
    margin-right: 0.03in;
}

/* Triple row (legacy) — keep nowrap if used elsewhere */
.info-row-triple {
    display: flex;
    flex-wrap: nowrap;
    gap: 0.08in;
    border-bottom: 1px dotted #ccc;
    padding: 0.02in 0;
    font-size: 7.5pt;
}

.info-item {
    flex: 1 1 0;
    min-width: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.info-value-inline {
    font-weight: normal;
    font-size: 7.5pt;
    color: #000;
}

.class-badge {
    font-weight: 600;
    margin-left: 0.05in;
    font-size: 8.25pt;
    white-space: nowrap;
    color: #222;
}

.due-date {
    font-weight: bold;
}

.left-align {
    text-align: left !important;
}
/* SECTION 3: FEE DETAIL TABLE - Conditional Columns */
.fee-detail-section {
    margin-bottom: 0.06in;
}

.fee-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 7.5pt;
    table-layout: fixed;
}

.fee-table th,
.fee-table td {
    border: 1px solid #000;
    padding: 0.02in 0.03in;
    vertical-align: middle;
    color: #000;
}

.fee-table th {
    background: #fff;
    font-weight: bold;
    text-align: center;
    font-size: 7pt;
    line-height: 1.1;
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
    width: 4%;
}

.col-particulars {
    width: 44%;
}

.col-amount,
.col-discount,
.col-payable {
    width: 14%;
}

/* Column widths when discount is hidden - single column for payable */
.col-payable-full {
    width: 46%;
}

.particulars-cell {
    font-size: 7.5pt;
    line-height: 1.15;
}

.particulars-cell strong {
    font-weight: 600;
}

.fee-month-small {
    font-size: 6.5pt;
    margin-left: 0.03in;
    color: #000;
}

.payable-amount {
    font-weight: bold;
}

/* Uniform row height for fee detail body (5 rows, compact) */
.fee-table tbody tr.fee-detail-fixed td {
    border: 1px solid #000;
    min-height: 0.26in;
    height: 0.26in;
    vertical-align: middle;
}

/* Blank rows - completely empty */
.blank-row td {
    border: 1px solid #000;
}

/* SECTION 4: FEE SUMMARY — horizontal 3 columns */
.fee-summary-section.fee-summary-compact {
    border-top: 2px solid #000;
    border-bottom: 1px solid #000;
    padding: 0.04in 0.04in;
    margin-bottom: 0.05in;
    text-align: center;
}

.summary-strip {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 0.04in;
    width: 100%;
    border: 1px solid #000;
    background: #fafafa;
    padding: 0.03in 0.04in;
    align-items: stretch;
}

.summary-col {
    border-right: 1px dotted #ccc;
    padding: 0.02in 0.03in;
    text-align: center;
    min-width: 0;
}

.summary-col:last-child {
    border-right: 0;
}

.summary-col-label {
    font-size: 6.5pt;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    color: #000;
    margin-bottom: 0.015in;
    line-height: 1.1;
}

.summary-col-value {
    font-size: 7.5pt;
    font-weight: 600;
    color: #000;
    white-space: nowrap;
    line-height: 1.15;
}

.summary-col-total {
    background: #fff;
}

.summary-col-value-grand {
    font-size: 8.5pt;
    font-weight: bold;
}

.summary-after-due {
    margin-top: 0.04in;
    text-align: right;
    font-size: 7.5pt;
}

.summary-after-due-label {
    font-weight: 600;
    margin-right: 0.06in;
}

.summary-after-due-value {
    font-weight: bold;
}

.fine-note {
    display: block;
    font-size: 7pt;
    margin-top: 0.02in;
    font-weight: normal;
    color: #000;
}

/* Payment History Table */
.payment-history-section {
    margin-top: 0.06in;
    margin-bottom: 0.04in;
}

.history-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 7pt;
    table-layout: fixed;
}

.history-table th.history-corner-cell,
.history-table td.history-label {
    width: 11%;
    max-width: 0.5in;
    white-space: nowrap;
}

.history-table th,
.history-table td {
    border: 1px solid #000;
    padding: 0.02in;
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

.history-row-sum {
    border-top: 1px solid #000;
}

.history-label-sum {
    font-weight: bold;
}

.history-sum-cell {
    font-weight: 700;
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

.chalan-accounts-disclaimer {
    font-size: 8pt;
    text-align: center;
    margin-top: 0.05in;
    padding: 0.02in 0.04in;
    clear: both;
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