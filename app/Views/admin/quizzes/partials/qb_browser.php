<?php
/**
 * 3-column Question Bank browser: Classes | Subjects | Topics
 */
?>
<div id="qbSelectedPanel" class="qb-selected-panel mb-3 d-none">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
    <strong class="text-primary"><i class="fas fa-check-double me-1"></i> <span id="qbSelectedCount">0</span> topics selected</strong>
    <button type="button" class="btn btn-sm btn-outline-danger" id="qbClearTopics">Clear all topics</button>
  </div>
  <div id="qbSelectedChips" class="qb-selected-chips"></div>
</div>

<div class="input-group input-group-sm mb-3">
  <span class="input-group-text"><i class="fas fa-search"></i></span>
  <input type="search" class="form-control" id="qbSearch" placeholder="Search class, subject, or topic…" autocomplete="off">
  <button type="button" class="btn btn-outline-secondary" id="qbSearchClear" title="Clear">×</button>
</div>

<div class="row qb-browser-row g-0 border rounded overflow-hidden bg-white">
  <div class="col-lg-3 col-md-4 border-end qb-browser-col">
    <div class="qb-col-header"><span>Classes</span><span class="badge text-bg-light" id="qbClassBadge">0</span></div>
    <div id="qbClassList" class="qb-scroll-list"><div class="qb-col-placeholder text-muted p-3 small">Loading…</div></div>
  </div>
  <div class="col-lg-3 col-md-4 border-end qb-browser-col">
    <div class="qb-col-header"><span>Subjects</span><span class="badge text-bg-light" id="qbSubjectBadge">0</span></div>
    <div id="qbSubjectList" class="qb-scroll-list"><div class="qb-col-placeholder text-muted p-3 small">Select a class</div></div>
  </div>
  <div class="col-lg-6 col-md-4 qb-browser-col">
    <div class="qb-col-header d-flex justify-content-between align-items-center">
      <span>Topics <span class="badge text-bg-light ms-1" id="qbTopicBadge">0</span></span>
      <button type="button" class="btn btn-sm btn-outline-primary d-none" id="qbSelectAllTopicsInSubject">All in subject</button>
    </div>
    <div id="qbTopicList" class="qb-scroll-list"><div class="qb-col-placeholder text-muted p-3 small">Select a subject</div></div>
  </div>
</div>

<div id="qbNestedWrap" class="d-none"></div>
