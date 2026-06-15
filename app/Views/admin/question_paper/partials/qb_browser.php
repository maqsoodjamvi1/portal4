<?php /** Question paper — 3-column topic browser (same markup as quiz QB browser) */ ?>
<div class="row mb-3">
  <div class="form-group col-12 col-md-8">
    <label class="small fw-bold mb-1">Boards / Publisher <span class="text-muted">(filter topics)</span></label>
    <select id="qpBoardPublisherFilter" class="form-control form-control-sm" multiple size="3">
      <?php foreach (($boardPublishers ?? []) as $bp): ?>
        <option value="<?= (int) $bp['id'] ?>"><?= esc($bp['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <small class="text-muted">Optional. Untagged topics appear for every filter. Hold Ctrl to select multiple.</small>
  </div>
  <div class="form-group col-12 col-md-4 d-flex align-items-end">
    <button type="button" class="btn btn-outline-primary btn-sm" id="qpApplyBoardFilter">
      <i class="fas fa-filter"></i> Apply filter
    </button>
    <button type="button" class="btn btn-outline-secondary btn-sm ms-1" id="qpClearBoardFilter">Clear</button>
  </div>
</div>

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
