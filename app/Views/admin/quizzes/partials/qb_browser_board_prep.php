<?php /** Board prep — question filter only: Class | Topics */ ?>
<div id="qbSelectedPanel" class="qb-selected-panel mb-2 d-none">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-1">
    <strong class="text-primary small"><i class="fas fa-check-double me-1"></i> <span id="qbSelectedCount">0</span> topics for question filter</strong>
    <button type="button" class="btn btn-sm btn-outline-danger" id="qbClearTopics">Clear topics</button>
  </div>
  <div id="qbSelectedChips" class="qb-selected-chips"></div>
</div>

<div class="input-group input-group-sm mb-2">
  <span class="input-group-text"><i class="fas fa-search"></i></span>
  <input type="search" class="form-control" id="qbSearch" placeholder="Search class or topic…" autocomplete="off">
  <button type="button" class="btn btn-outline-secondary" id="qbSearchClear" title="Clear">×</button>
</div>

<div class="row qb-browser-row qb-browser-row--filter g-0 border rounded overflow-hidden bg-white">
  <div class="col-md-4 border-end qb-browser-col">
    <div class="qb-col-header d-flex justify-content-between align-items-center">
      <span>Class <span class="text-muted fw-normal">(filter)</span></span>
      <span class="badge text-bg-light" id="qbClassBadge">0</span>
    </div>
    <div id="qbClassList" class="qb-scroll-list"><div class="qb-col-placeholder text-muted p-3 small">Select board &amp; subject above</div></div>
  </div>
  <div class="col-md-8 qb-browser-col">
    <div class="qb-col-header d-flex justify-content-between align-items-center">
      <span>Topics <span class="text-muted fw-normal">(filter questions)</span> <span class="badge text-bg-light ms-1" id="qbTopicBadge">0</span></span>
      <button type="button" class="btn btn-sm btn-outline-primary d-none" id="qbSelectAllTopicsVisible">All shown</button>
    </div>
    <div id="qbTopicList" class="qb-scroll-list"><div class="qb-col-placeholder text-muted p-3 small">Select a class</div></div>
  </div>
</div>
