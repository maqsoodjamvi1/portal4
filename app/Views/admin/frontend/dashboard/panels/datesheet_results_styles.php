<style>
#dash-results-card .dash-collapse-toggle[aria-expanded="true"] .fa-chevron-down {
    transform: rotate(180deg);
}
#dash-results-card .dash-collapse-toggle .fa-chevron-down {
    transition: transform 0.2s ease;
    display: inline-block;
}
/* Dashboard datesheet: one block per exam day */
.dash-ds-schedule { max-width: 100%; }
.dash-ds-day-block {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
}
.dash-ds-day-head {
    background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 55%, #3b82f6 100%);
    color: #fff;
    padding: 0.65rem 1rem;
}
.dash-ds-day-icon {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    font-size: 1rem;
}
.dash-ds-day-title {
    font-weight: 700;
    font-size: 1.05rem;
    line-height: 1.2;
}
.dash-ds-day-sub {
    font-size: 0.85rem;
    opacity: 0.92;
}
.dash-ds-day-badge {
    font-weight: 600;
    font-size: 0.75rem;
}
.dash-ds-table-wrap { border-top: 1px solid #e2e8f0; }
.dash-ds-day-table {
    table-layout: fixed;
    width: 100%;
}
.dash-ds-day-table thead th {
    border-bottom: 2px solid #dee2e6;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #475569;
}
.dash-ds-th-subject { width: 58%; }
.dash-ds-th-marks { width: 18%; }
.dash-ds-th-syll { width: 24%; }
.dash-ds-data-row td {
    vertical-align: middle;
    border-color: #eef2f7;
}
.dash-ds-marks-cell { color: #0f172a; }
.dash-ds-syl-btn { min-width: 5.5rem; }
.dash-ds-syllabus-inner {
    padding: 0.75rem 1rem 0.9rem;
    background: #f8fafc;
    border-top: 1px dashed #cbd5e1;
    line-height: 1.55;
    word-break: break-word;
}
@media (max-width: 575.98px) {
    .dash-ds-day-table { table-layout: auto; }
    .dash-ds-th-subject, .dash-ds-th-marks, .dash-ds-th-syll { width: auto; }
    .dash-ds-syl-btn { min-width: auto; }
}
</style>
