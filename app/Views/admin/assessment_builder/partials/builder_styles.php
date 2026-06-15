<style>
  .bp-toggle-group { display: flex; flex-wrap: wrap; gap: 0.35rem; align-items: center; }
  .bp-toggle {
    border: 1px solid #ced4da; background: #fff; color: #495057; border-radius: 999px;
    padding: 0.25rem 0.7rem; font-size: 0.78rem; cursor: pointer;
  }
  .bp-toggle:hover { border-color: #80bdff; color: #0056b3; }
  .bp-toggle.active { background: #007bff; border-color: #007bff; color: #fff; }
  .qb-browser-row { min-height: 320px; }
  .qb-browser-col { display: flex; flex-direction: column; min-height: 320px; }
  .qb-col-header {
    padding: 0.5rem 0.75rem; font-weight: 600; font-size: 0.85rem;
    background: #f8f9fa; border-bottom: 1px solid #dee2e6;
    display: flex; justify-content: space-between; align-items: center;
  }
  .qb-scroll-list { flex: 1; overflow-y: auto; max-height: 320px; min-height: 240px; }
  .qb-list-item {
    display: block; width: 100%; text-align: left; border: none; border-bottom: 1px solid #f1f3f5;
    background: #fff; padding: 0.55rem 0.75rem; font-size: 0.9rem; cursor: pointer;
  }
  .qb-list-item:hover { background: #f8f9fa; }
  .qb-list-item.active { background: #e7f1ff; border-start: 3px solid #007bff; font-weight: 600; }
  .qb-topic-row {
    display: flex; align-items: center; gap: 0.5rem; padding: 0.45rem 0.75rem;
    border-bottom: 1px solid #f1f3f5; cursor: pointer; font-size: 0.9rem; margin: 0;
  }
  .qb-topic-row.selected { background: #e7f1ff; }
  .qp-type-table-wrap {
    overflow-x: auto;
    border-radius: 0.4rem;
    border: 1px solid #dee2e6;
    background: #fff;
  }
  .qp-type-table {
    table-layout: fixed;
    width: 100%;
    min-width: 640px;
    margin-bottom: 0;
  }
  .qp-type-table thead th {
    font-size: 0.78rem;
    font-weight: 700;
    text-align: center;
    vertical-align: middle;
    background: #f1f5f9;
    color: #334155;
    padding: 0.45rem 0.35rem;
    border-color: #e2e8f0;
  }
  .qp-type-table tbody th.qp-type-table-axis,
  .qp-type-table tbody th[scope="row"] {
    width: 4.75rem;
    font-size: 0.75rem;
    font-weight: 700;
    text-align: left;
    vertical-align: middle;
    background: #f8fafc;
    color: #475569;
    padding: 0.4rem 0.55rem;
    white-space: nowrap;
  }
  .qp-type-table td {
    text-align: center;
    vertical-align: middle;
    padding: 0.35rem 0.4rem;
    border-color: #e2e8f0;
  }
  .qp-type-table .form-control-sm {
    width: 100%;
    max-width: none;
    min-width: 3rem;
    text-align: center;
    font-size: 0.9rem;
    padding: 0.3rem 0.35rem;
    height: calc(1.5em + 0.55rem + 2px);
  }
  .qp-type-table tbody tr.qp-bank-row th[scope="row"] { color: #0c5460; }
  .qp-type-table tbody tr.qp-pick-row th[scope="row"] { color: #1e40af; }
  .qp-type-table tbody tr.qp-marks-row th[scope="row"] { color: #7c3aed; }
  .qp-bank-cell { background: #f8fafc; }
  .qp-bank-badge {
    display: inline-block;
    min-width: 2.25rem;
    padding: 0.2rem 0.45rem;
    border-radius: 0.35rem;
    font-size: 0.88rem;
    font-weight: 700;
    line-height: 1.3;
  }
  .qp-bank-badge--empty { color: #adb5bd; background: #f1f3f5; }
  .qp-bank-badge--zero { color: #868e96; background: #e9ecef; }
  .qp-bank-badge--ok { color: #0b7285; background: #e3fafc; }
  .qp-type-count.is-over-bank {
    border-color: #fa5252;
    background: #fff5f5;
  }
  .qp-selection-summary {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem 1rem;
    font-size: 0.82rem;
    color: #495057;
  }
  .qp-summary-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.2rem 0.55rem;
    background: #f1f3f5;
    border-radius: 0.35rem;
  }
  .qp-type-table .qp-type-total-col {
    width: 5.5rem;
    background: #eef2f7;
    font-weight: 700;
    color: #334155;
  }
  .qp-total-cell {
    text-align: center;
    vertical-align: middle;
    background: #f8fafc;
    font-weight: 700;
  }
  .qp-bank-total-cell .qp-bank-badge { font-size: 0.95rem; }
  .qp-pick-total { color: #1c7ed6; font-size: 1rem; }
  .qp-marks-total { color: #7950f2; font-size: 1rem; }
  .qp-count-field.qp-pick-disabled,
  .qp-count-field.qp-pick-disabled .qp-type-count {
    background: #f1f3f5;
    cursor: not-allowed;
  }
  .qp-count-field.qp-pick-disabled .qp-type-count:disabled {
    color: #adb5bd;
  }
  .ab-selected-topics {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.4rem;
    padding: 0.5rem 0.65rem;
  }
  .ab-topics-toolbar .qb-browser-row { min-height: 260px; }
  .ab-topics-toolbar .qb-scroll-list { max-height: 260px; min-height: 200px; }
  .qp-count-field.qp-marks-inactive .qp-type-count { opacity: 0.55; }
  .qp-marks-cell .qp-marks-input:disabled { background: #f1f3f5; }
  .qp-desc-choice-panel { background: #f8fafc; }
  .qp-desc-pairs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 0.65rem;
    width: 100%;
  }
  .qp-desc-pair-row {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    align-items: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #c5d4e8;
    border-radius: 0.5rem;
    background: #fff;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.07);
  }
  .qp-desc-pair-row .qp-pair-a,
  .qp-desc-pair-row .qp-pair-b {
    flex: 1 1 0;
    width: auto !important;
    min-width: 5.5rem;
    max-width: none;
  }
  .qp-pair-badge {
    font-size: 0.7rem;
    font-weight: 700;
    color: #fff;
    background: #5c7cfa;
    border-radius: 999px;
    padding: 0.2rem 0.55rem;
    line-height: 1.2;
    white-space: nowrap;
    flex-shrink: 0;
  }
  .qp-pair-or-label {
    font-weight: 700;
    font-size: 0.8rem;
    color: #868e96;
    white-space: nowrap;
    flex-shrink: 0;
    padding: 0 0.15rem;
  }
  .qp-pair-remove {
    line-height: 1;
    padding: 0.15rem 0.55rem;
    flex-shrink: 0;
  }
  .qp-desc-pairs-actions { margin-top: 0.15rem; }
  .ab-save-block {
    border: 1px solid #e3e8ef;
    border-radius: 0.5rem;
    background: #fff;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
  }
  .ab-save-block__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.7rem 1rem;
    border-bottom: 1px solid #e9ecef;
  }
  .ab-save-block--quiz .ab-save-block__head {
    background: linear-gradient(180deg, #f3fbf5 0%, #eaf7ed 100%);
    border-bottom-color: #d3efd9;
  }
  .ab-save-block--paper .ab-save-block__head {
    background: linear-gradient(180deg, #f8f9fc 0%, #f1f3f8 100%);
    border-bottom-color: #e2e6ef;
  }
  .ab-save-block__title {
    font-weight: 700;
    font-size: 0.95rem;
    color: #343a40;
  }
  .ab-save-block--quiz .ab-save-block__title i { color: #28a745; margin-right: 0.35rem; }
  .ab-save-block--paper .ab-save-block__title i { color: #4c6ef5; margin-right: 0.35rem; }
  .ab-save-block__body { padding: 1rem; min-height: 220px; }
  .ab-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.2rem;
  }
  .ab-settings-summary {
    background: #f8f9fa;
    border-radius: 0.35rem;
    padding: 0.4rem 0.55rem;
    line-height: 1.35;
  }
  .ab-generate-btn {
    font-weight: 600;
    padding: 0.55rem 1rem;
    border-radius: 0.4rem;
  }
  .ab-existing-wrap { min-height: 2rem; }
  @media (max-width: 991px) {
    .qb-browser-col { min-height: 240px; }
    .qb-scroll-list { max-height: 240px; min-height: 180px; }
    .qb-browser-row { min-height: auto; }
  }
</style>
