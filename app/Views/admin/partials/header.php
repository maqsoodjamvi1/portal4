<style>
/* ===== Result grid (vertical subject headers) ===== */
.result-scroll{overflow-x:auto;-webkit-overflow-scrolling:touch}
.result-table{border-collapse:separate;border-spacing:0;width:max-content;min-width:100%;font-size:12px}
.result-table thead th{position:sticky;top:0;z-index:3;background:#fff;box-shadow:0 1px 0 rgba(0,0,0,.06);white-space:nowrap}

/* first two sticky columns */
.narrow-col,.sticky-index{width:3.5rem;min-width:3.5rem;max-width:3.5rem}
.student-col,.sticky-student{min-width:12rem;max-width:20rem;text-align:left}
.total-col{width:4.5rem}.pct-col{width:3.5rem}.grade-col{width:4.5rem}
.sticky-index,.sticky-student{position:sticky;left:0;background:#fff;z-index:2}
.sticky-student{left:3.5rem}
.result-table thead .narrow-col{position:sticky;left:0;z-index:4;background:#fff}
.result-table thead .student-col{position:sticky;left:3.5rem;z-index:4;background:#fff}
.sticky-index,.result-table thead .narrow-col{box-shadow:1px 0 0 rgba(0,0,0,.06)}
.sticky-student,.result-table thead .student-col{box-shadow:1px 0 0 rgba(0,0,0,.06)}

/* SUBJECT column cell + vertical label */
.sub-col{
  width:48px;min-width:48px;max-width:56px;
  padding:4px 2px;white-space:nowrap;
  height:110px; /* ensure enough height for the vertical label */
}

/* The label wrapper: try modern vertical first */
.sub-col .v-wrap{
  height:80px; /* visible label height; adjust as needed */
  margin:0 auto 2px;
  display:flex;align-items:center;justify-content:center;
  writing-mode:vertical-rl !important;
  text-orientation:mixed;
}

/* Some browsers render vertical-rl upside-down; correct gracefully */
.sub-col .v-wrap.flip { transform: rotate(180deg); }

/* Fallback for browsers without writing-mode support */
@supports not (writing-mode: vertical-rl) {
  .sub-col .v-wrap{
    writing-mode:initial; /* fallback */
    transform:rotate(-90deg);
    transform-origin:50% 50%;
    height:auto;
  }
}

/* Label text */
.v-text{display:inline-block;line-height:1;font-weight:600;font-size:11px;letter-spacing:.2px}

/* table cells */
.result-table td,.result-table th{vertical-align:middle;text-align:center;padding:6px 6px}

/* summary rows (optional colors) */
tr.summary-row th, tr.summary-row td{background:#f8fafc;font-weight:700}
tr.summary-row.grade-A td{background:#eef2ff}
tr.summary-row.grade-B td{background:#ecfeff}
tr.summary-row.grade-C td{background:#ecfccb}
tr.summary-row.grade-D td{background:#fff7ed}
tr.summary-row.grade-F td{background:#fee2e2}
tr.summary-row.absent td{background:#f3f4f6}

/* Print */
@media print{
  .result-scroll{overflow:visible}
  .result-table{width:100%}
  .sub-col{width:42px;min-width:42px;height:96px}
  .student-col{min-width:10rem}
}
</style>
