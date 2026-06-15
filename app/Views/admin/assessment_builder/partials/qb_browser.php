<?php /** Assessment Builder — topic browser (embedded in questions card) */ ?>

<div class="ab-topics-toolbar mb-2">

  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">

    <?php if (empty($boardPrepMode)): ?>
    <div>

      <label class="small fw-bold mb-1 d-block">Boards</label>

      <div id="qpBoardPublisherToggles" class="bp-toggle-group" role="group">

        <?php if (empty($boardPublishers)): ?>

          <span class="text-muted small">—</span>

        <?php else: ?>

          <?php foreach ($boardPublishers as $bp): ?>

            <button type="button" class="bp-toggle" data-id="<?= (int) $bp['id'] ?>" aria-pressed="false"><?= esc($bp['name']) ?></button>

          <?php endforeach; ?>

        <?php endif; ?>

      </div>

    </div>
    <?php else: ?>
    <div id="qpBoardPublisherToggles" class="d-none" aria-hidden="true"></div>
    <?php endif; ?>

    <div class="input-group input-group-sm ab-topic-search" style="max-width:280px;">

      <span class="input-group-text"><i class="fas fa-search"></i></span>

      <input type="search" class="form-control" id="qbSearch" placeholder="Search class, subject, topic…" autocomplete="off">

      <button type="button" class="btn btn-outline-secondary" id="qbSearchClear">×</button>

    </div>

  </div>



  <div class="row qb-browser-row g-0 border rounded overflow-hidden bg-white">

    <div class="col-lg-3 col-md-4 border-end qb-browser-col">

      <div class="qb-col-header"><span>Class</span><span class="badge text-bg-light" id="qbClassBadge">0</span></div>

      <div id="qbClassList" class="qb-scroll-list"><div class="qb-col-placeholder text-muted p-3 small">Loading…</div></div>

    </div>

    <div class="col-lg-3 col-md-4 border-end qb-browser-col">

      <div class="qb-col-header"><span>Subject</span><span class="badge text-bg-light" id="qbSubjectBadge">0</span></div>

      <div id="qbSubjectList" class="qb-scroll-list"><div class="qb-col-placeholder text-muted p-3 small">Select class</div></div>

    </div>

    <div class="col-lg-6 col-md-4 qb-browser-col">

      <div class="qb-col-header"><span>Topics</span><span class="badge text-bg-light" id="qbTopicBadge">0</span></div>

      <div id="qbTopicList" class="qb-scroll-list"><div class="qb-col-placeholder text-muted p-3 small">Select subject</div></div>

    </div>

  </div>

</div>
