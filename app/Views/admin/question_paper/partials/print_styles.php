<style>
@page { size: A4 portrait; margin: 12mm 14mm; }
* { box-sizing: border-box; }
body {
  margin: 0;
  font-family: Arial, Helvetica, 'Segoe UI', sans-serif;
  font-size: 11pt;
  color: #000;
  background: #e8eaed;
}
body.qp-print-sheet { background: #fff; margin: 0 auto; max-width: 210mm; padding: 10mm; }
.qp-urdu, .qp-q-text.qp-urdu {
  font-family: 'Jameel Noori Nastaleeq', 'Nafees Web Naskh', serif;
  direction: rtl;
  text-align: right;
}
.qp-font-small { font-size: 9.5pt; }
.qp-font-normal { font-size: 11pt; }
.qp-font-large { font-size: 13pt; }
.qp-cols-2 .qp-questions-body {
  column-count: 2;
  column-gap: 16px;
}
.qp-paper-header {
  text-align: center;
  border: 2px solid #000;
  padding: 10px 12px;
  margin-bottom: 14px;
  page-break-inside: avoid;
}
.qp-header-top {
  display: grid;
  grid-template-columns: 88px 1fr 88px;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
  padding-bottom: 8px;
  border-bottom: 1px solid #ccc;
  page-break-inside: avoid;
}
.qp-logo-cell {
  justify-self: start;
}
.qp-logo-spacer {
  width: 88px;
  max-width: 88px;
}
.qp-school-logo {
  display: block;
  max-height: 72px;
  max-width: 88px;
  width: auto;
  height: auto;
  margin: 0;
  object-fit: contain;
}
.qp-school-heading-row {
  text-align: center;
  min-width: 0;
}
.qp-school {
  font-size: 1.65em;
  font-weight: 700;
  margin: 0;
  padding: 0;
  line-height: 1.2;
  text-align: center;
}
.qp-school-sub {
  font-size: 1em;
  font-weight: 600;
  margin-top: 3px;
  text-align: center;
}
.qp-title {
  font-size: 1.55em;
  font-weight: 700;
  margin: 8px 0 6px;
  text-align: center;
  line-height: 1.25;
}
.qp-meta-primary,
.qp-meta-secondary {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  gap: 8px 16px;
  padding: 0 4px;
}
.qp-meta-primary {
  font-size: 1.05em;
  margin-top: 8px;
  font-weight: 600;
}
.qp-meta-secondary {
  font-size: 0.95em;
  margin-top: 4px;
  justify-content: center;
}
.qp-meta-primary .qp-meta-item {
  flex: 1 1 0;
  min-width: 8rem;
  text-align: center;
}
.qp-meta-primary .qp-meta-item:first-child { text-align: left; }
.qp-meta-primary .qp-meta-item:last-child { text-align: right; }
.qp-meta-primary .qp-meta-item:only-child { text-align: center; }
.qp-meta-secondary .qp-meta-item { margin: 0 10px; }
.qp-student-fields {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: 8px;
  margin-top: 10px;
  font-size: 0.95em;
  text-align: left;
}
.qp-instructions {
  text-align: left;
  margin-top: 8px;
  font-size: 0.9em;
  border-top: 1px solid #ccc;
  padding-top: 6px;
}
.qp-topic-heading {
  font-weight: 700;
  font-size: 1.05em;
  margin: 14px 0 8px;
  padding: 6px 10px;
  background: #f0f4ff;
  border-start: 4px solid #3b82f6;
  page-break-after: avoid;
}
.qp-section-title {
  text-align: center;
  font-weight: 700;
  font-size: 1.25em;
  margin: 12pt 0 0.85rem;
  padding: 0.35rem 0;
  width: 100%;
  page-break-after: avoid;
}
.qp-section-choice-note {
  text-align: center;
  font-weight: 600;
  font-size: 1.02em;
  margin: -0.35rem 0 0.75rem;
  font-style: italic;
}
.qp-or-divider {
  text-align: center;
  margin: 0.35rem 0 0.5rem;
  font-weight: 700;
  font-size: 1.05em;
  letter-spacing: 0.08em;
  page-break-inside: avoid;
}
.qp-or-divider span {
  display: inline-block;
  padding: 0.1rem 1.25rem;
  border-top: 1px solid #333;
  border-bottom: 1px solid #333;
}
.qp-pair-card {
  margin-bottom: 12pt;
  page-break-inside: avoid;
}
.qp-pair-alt .qp-q-head {
  padding-left: 1.25em;
}
.qp-pair-alt .qp-q-num {
  display: none;
}
.qp-type-section {
  margin-bottom: 0.5rem;
}
.qp-q-num {
  font-weight: 700;
  margin-right: 0.35em;
  text-transform: lowercase;
}
.qp-q-marks {
  font-weight: 600;
  font-size: 0.92em;
  margin-left: 0.2em;
  white-space: nowrap;
}
.qp-question-card {
  margin-bottom: 12px;
  break-inside: avoid;
  page-break-inside: avoid;
}
.qp-q-head-mcq,
.qp-q-head-mcq .qp-q-text,
.qp-q-head-mcq .qp-q-num {
  font-weight: 700;
}
.qp-q-text { line-height: 1.45; }
.qp-mcq-list { list-style: none; padding: 0; margin: 6px 0 0; }
.qp-mcq-inline-line { margin: 4px 0 0; padding: 0; line-height: 1.2; }
.qp-mcq-inline-line .qp-mcq-opt {
  display: inline;
  margin-right: 14px;
  padding: 0;
  border: none;
  border-radius: 0;
}
.qp-mcq-inline { display: flex; flex-wrap: wrap; gap: 6px; }
.qp-mcq-opt {
  padding: 4px 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
}
.qp-mcq-opt.qp-correct {
  border: 2px solid #000;
  font-weight: 700;
  background: #f0fdf4;
}
.qp-ans-box {
  margin-top: 6px;
  padding: 6px 8px;
  background: #f8fafc;
  border: 1px solid #ddd;
  font-size: 0.95em;
}
.qp-line {
  border-bottom: 1px solid #333;
  height: 1.6em;
  margin: 4px 0;
}
.qp-match-grid { display: flex; gap: 12px; margin-top: 6px; }
.qp-match-col { flex: 1; }
.qp-match-item {
  padding: 4px 6px;
  border: 1px solid #ccc;
  margin-bottom: 4px;
  min-height: 28px;
}
.qp-page-break { page-break-before: always; }
.no-print {
  position: sticky;
  top: 0;
  z-index: 50;
  background: #1e293b;
  color: #fff;
  padding: 10px 16px;
  display: flex;
  gap: 10px;
  align-items: center;
}
.no-print button {
  background: #3b82f6;
  color: #fff;
  border: 0;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
}
@media print {
  body { background: #fff !important; }
  .no-print { display: none !important; }
  body.qp-print-sheet { padding: 0; max-width: none; }
}
</style>
