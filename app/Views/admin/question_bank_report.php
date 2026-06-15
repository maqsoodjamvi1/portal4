<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Question bank',
    'icon' => 'fas fa-book',
    'actionsHtml' => '<div class="text-sm-right d-flex flex-wrap justify-content-sm-end" style="gap:.5rem;">'
        . '<a href="' . esc(site_url('admin/question-bank/form'), 'attr') . '" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> Add / edit questions</a>'
        . '<a href="' . esc(site_url('admin/question-bank/list'), 'attr') . '" class="btn btn-outline-secondary btn-sm"><i class="fas fa-table"></i> Table list</a>'
        . '</div>',
]) ?>

  <section class="content">

  <?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('msg')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

<div class="card sms-card mb-3">
  <div class="card-header d-flex align-items-center justify-content-end py-2">
    <div class="d-flex align-items-center" style="gap:.5rem;">
      <button type="button" id="qbCollapseAll" class="btn btn-light btn-sm" data-bs-toggle="tooltip" title="Collapse all classes">
        <i class="fas fa-minus-square"></i>
      </button>
      <button type="button" id="qbReloadSummary" class="btn btn-light btn-sm" data-bs-toggle="tooltip" title="Reload">
        <i class="fas fa-sync"></i>
      </button>
    </div>
  </div>

  <div class="card-body pt-3">
    <div id="qbSummaryLoader" class="text-center py-4">
      <i class="fas fa-spinner fa-spin"></i>
      <div class="text-muted small mt-2">Loading summary...</div>
    </div>

    <div id="qbSummaryTree" class="qb-tree hidden"></div>

    <div id="qbSummaryEmpty" class="alert alert-light text-center hidden mb-0">
      <i class="far fa-folder-open"></i> No question bank data found yet.
    </div>
  </div>
</div>

<style>
  .hidden{ display:none !important; }

  .qb-tree{ display:grid; gap:1rem; }

  .qb-class{
    border:1px solid #e9ecef;
    border-radius:.9rem;
    background:#fff;
    box-shadow:0 2px 10px rgba(0,0,0,.04);
    overflow:hidden;
  }
  .qb-class-h{
    padding:.75rem 1rem;
    background:linear-gradient(180deg,#fafbfc,#fff);
    border-bottom:1px solid #eef1f4;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:.75rem;
    cursor:pointer;
    user-select:none;
  }
  .qb-class-h:hover{ background:#f4f6f8; }
  .qb-class-title{
    margin:0;
    font-weight:800;
    font-size:1rem;
    display:flex;
    align-items:center;
    gap:.5rem;
  }
  .qb-pill{
    display:inline-flex;
    align-items:center;
    gap:.35rem;
    padding:.2rem .6rem;
    border-radius:999px;
    background:#f6f7f9;
    border:1px solid #eef1f4;
    font-size:.78rem;
    white-space:nowrap;
  }

  .qb-subject-wrap{
    padding:1rem;
    display:flex;
    flex-direction:column;
    gap:1rem;
  }

  .qb-subject{
    border:1px solid #e9ecef;
    border-radius:.85rem;
    overflow:hidden;
    background:#fff;
    box-shadow:0 2px 8px rgba(0,0,0,.04);
  }
  .qb-subject-h{
    padding:.6rem .85rem;
    background:#fbfcfe;
    border-bottom:1px solid #eef1f4;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:.5rem;
    cursor:pointer;
    user-select:none;
  }
  .qb-subject-h:hover{ background:#f1f5f9; }
  .qb-subject-title{
    margin:0;
    font-weight:800;
    font-size:.95rem;
    display:flex;
    align-items:center;
    gap:.45rem;
  }

  .qb-topic-wrap{
    padding:.85rem;
    display:flex;
    flex-direction:column;
    gap:.75rem;
  }

  .qb-topic-filters{
    padding:.55rem .65rem;
    background:#f8fafc;
    border:1px solid #e9ecef;
    border-radius:.5rem;
  }
  .qb-topic-filters__label{
    font-size:.72rem;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.04em;
    color:#64748b;
    margin-bottom:.35rem;
  }
  .qb-bp-filter-group{
    display:flex;
    flex-wrap:wrap;
    gap:.35rem;
    align-items:center;
  }
  .qb-bp-toggle{
    border:1px solid #ced4da;
    background:#fff;
    color:#495057;
    border-radius:999px;
    padding:.2rem .65rem;
    font-size:.75rem;
    line-height:1.3;
    cursor:pointer;
  }
  .qb-bp-toggle:hover{ border-color:#80bdff; color:#0056b3; }
  .qb-bp-toggle.active{
    background:#007bff;
    border-color:#007bff;
    color:#fff;
  }
  .qb-topic-list{
    display:flex;
    flex-direction:column;
    gap:.75rem;
  }

  .qb-topic{
    border:1px solid #eef1f4;
    border-radius:.75rem;
    padding:.65rem .75rem;
    background:#fff;
  }
  .qb-topic-title{
    font-weight:800;
    font-size:.88rem;
    margin:0 0 .35rem 0;
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:.5rem;
  }
  .qb-topic-name{
    display:-webkit-box;
    -webkit-line-clamp:2;
    -webkit-box-orient:vertical;
    overflow:hidden;
    word-break:break-word;
    line-height:1.3;
    min-width:0;
    flex:1 1 auto;
  }

  .qb-counts{
    display:grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap:.25rem .75rem;
    margin-top:.35rem;
    font-size:.8rem;
  }
  .qb-count{
    display:flex;
    align-items:center;
    justify-content:space-between;
    border-bottom:1px dashed #eef1f4;
    padding:.15rem 0;
    gap:.5rem;
  }
  .qb-count:last-child{ border-bottom:0; }
  .qb-count .l{
    color:#6c757d;
    display:flex;
    align-items:center;
    gap:.35rem;
  }
  .qb-count .r{ font-weight:900; }

  .qb-toggle-icon{ opacity:.7; }
  .qb-board-tags{
    display:flex;
    flex-wrap:wrap;
    gap:.3rem;
    margin:.25rem 0 .4rem 1.35rem;
  }
  .qb-board-tag{
    display:inline-flex;
    align-items:center;
    gap:.25rem;
    padding:.12rem .45rem;
    border-radius:999px;
    background:#eef2ff;
    border:1px solid #c7d2fe;
    color:#3730a3;
    font-size:.7rem;
    font-weight:600;
  }
</style>

<script>
(function(){
  const urlAllSummary = "<?= site_url('admin/question-bank/summary-all') ?>";
  const urlForm = "<?= site_url('admin/question-bank/form') ?>";
  const urlProofRead = "<?= site_url('admin/question-bank/proof-read') ?>";

  const $loader = document.getElementById('qbSummaryLoader');
  const $tree   = document.getElementById('qbSummaryTree');
  const $empty  = document.getElementById('qbSummaryEmpty');

  const $btnCollapse = document.getElementById('qbCollapseAll');
  const $btnReload = document.getElementById('qbReloadSummary');

  let boardPublishers = [];

  function showLoader(){
    $loader.classList.remove('hidden');
    $tree.classList.add('hidden');
    $empty.classList.add('hidden');
  }
  function showTree(){
    $loader.classList.add('hidden');
    $tree.classList.remove('hidden');
    $empty.classList.add('hidden');
  }
  function showEmpty(){
    $loader.classList.add('hidden');
    $tree.classList.add('hidden');
    $empty.classList.remove('hidden');
  }

  function sumTopicCounts(topics){
    const sum = { total:0, mcq:0, multi:0, tf:0, fill:0, short:0, descriptive:0, match:0 };
    topics.forEach(t=>{
      sum.total += (t.total_questions||0);
      sum.mcq   += (t.mcq_single_count||0);
      sum.multi += (t.mcq_multi_count||0);
      sum.tf    += (t.tf_count||0);
      sum.fill  += (t.fill_count||0);
      sum.short += (t.short_count||0);
      sum.descriptive += (t.descriptive_count||0);
      sum.match += (t.match_count||0);
    });
    return sum;
  }

  function escapeHtml(str){
    return (str||'').toString()
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
  }

  const QB_TYPE_ROWS = [
    { key: 'mcq',   label: 'MCQ',       icon: 'far fa-check-square', field: 'mcq_single_count' },
    { key: 'multi', label: 'MCQ Multi', icon: 'fas fa-tasks',          field: 'mcq_multi_count' },
    { key: 'tf',    label: 'T/F',       icon: 'fas fa-toggle-on',      field: 'tf_count' },
    { key: 'fill',  label: 'Fill',      icon: 'fas fa-pen',            field: 'fill_count' },
    { key: 'short', label: 'Short',     icon: 'fas fa-align-left',     field: 'short_count' },
    { key: 'desc',  label: 'Descriptive', icon: 'fas fa-file-alt',     field: 'descriptive_count' },
    { key: 'match', label: 'Match',     icon: 'fas fa-random',         field: 'match_count' },
  ];

  function buildBoardFilterHtml(){
    if (!boardPublishers.length) {
      return '';
    }
    const toggles = boardPublishers.map(bp => {
      const id = parseInt(bp.id || 0, 10);
      return `<button type="button" class="qb-bp-toggle" data-bp-id="${id}" aria-pressed="false">${escapeHtml(bp.name || '')}</button>`;
    }).join('');
    return `
      <div class="qb-topic-filters">
        <div class="qb-topic-filters__label">Filter by board / publisher</div>
        <div class="qb-bp-filter-group" role="group">${toggles}</div>
      </div>
    `;
  }

  function buildBoardTagsHtml(topic){
    const boards = topic.board_publishers || [];
    if (!boards.length) return '';
    return `<div class="qb-board-tags">${boards.map(bp =>
      `<span class="qb-board-tag"><i class="fas fa-university"></i>${escapeHtml(bp.name || bp.short_code || '')}</span>`
    ).join('')}</div>`;
  }

  function getSubjectBoardFilterIds(subjectCard){
    const ids = [];
    subjectCard.querySelectorAll('.qb-bp-filter-group .qb-bp-toggle.active').forEach(btn => {
      const id = parseInt(btn.getAttribute('data-bp-id') || '0', 10);
      if (id > 0) ids.push(id);
    });
    return ids;
  }

  function topicPassesBoardFilter(boardIds, selectedIds){
    if (!selectedIds.length) return true;
    if (!boardIds.length) return true;
    return boardIds.some(id => selectedIds.includes(id));
  }

  function applySubjectTopicFilter(subjectCard){
    const selected = getSubjectBoardFilterIds(subjectCard);
    subjectCard.querySelectorAll('.qb-topic').forEach(el => {
      const raw = el.getAttribute('data-board-ids') || '';
      const boardIds = raw.split(',').filter(Boolean).map(v => parseInt(v, 10)).filter(n => n > 0);
      el.classList.toggle('hidden', !topicPassesBoardFilter(boardIds, selected));
    });
  }

  function buildTypeCountRows(topic){
    return QB_TYPE_ROWS
      .map(def => ({ ...def, count: topic[def.field] || 0 }))
      .filter(row => row.count > 0)
      .map(row => `<div class="qb-count"><span class="l"><i class="${row.icon}"></i> ${row.label}</span><span class="r">${row.count}</span></div>`)
      .join('');
  }

  function qbProofReadUrl(classId, subjectId, topicId){
    return urlProofRead
      + '?class_id=' + encodeURIComponent(classId)
      + '&subject_id=' + encodeURIComponent(subjectId || 0)
      + '&topic_id=' + encodeURIComponent(topicId || 0);
  }

  function qbOpenProofInNewTab(classId, subjectId, topicId){
    window.open(qbProofReadUrl(classId, subjectId, topicId), '_blank', 'noopener,noreferrer');
  }

  function qbNavigateToForm(classId, subjectId, topicId, withLoad){
    let u = urlForm
      + '?class_id=' + encodeURIComponent(classId)
      + '&subject_id=' + encodeURIComponent(subjectId || 0)
      + '&topic_id=' + encodeURIComponent(topicId || 0);
    if (withLoad) u += '&load=1';
    window.location.href = u;
  }

  function buildTree(data){
    let html = '';

    data.forEach((cls) => {
      const subjectsWithQuestions = (cls.subjects || []).filter(sub => {
        const st = sumTopicCounts(sub.topics || []);
        return st.total > 0;
      });
      if (!subjectsWithQuestions.length) return;

      const classId = cls.class_id;
      const className = escapeHtml(cls.class_name);
      const subjectCount = subjectsWithQuestions.length;

      html += `
        <div class="qb-class" data-collapsed="1" data-class-id="${classId}">
          <div class="qb-class-h" data-action="toggle-class" role="button" tabindex="0" aria-expanded="false">
            <div class="qb-class-title flex-grow-1" style="min-width:0;">
              <span class="d-inline-flex align-items-center" style="gap:.45rem;">
                <i class="fas fa-layer-group text-muted"></i>
                <span class="text-truncate">${className}</span>
              </span>
            </div>
            <div class="d-flex align-items-center flex-shrink-0" style="gap:.35rem; flex-wrap:wrap; justify-content:flex-end;">
              <span class="qb-pill" data-bs-toggle="tooltip" title="Subjects with questions">
                <i class="fas fa-book"></i> <b>${subjectCount}</b> subject${subjectCount === 1 ? '' : 's'}
              </span>
              <span class="qb-pill qb-toggle-icon">
                <i class="fas fa-chevron-down"></i>
              </span>
            </div>
          </div>

          <div class="qb-subject-wrap hidden">
      `;

      subjectsWithQuestions.forEach((sub) => {
        const subjectId = sub.subject_id;
        const subjectName = escapeHtml(sub.subject_name);

        const subTotals = sumTopicCounts(sub.topics||[]);
        const topicCount = (sub.topics || []).filter(t => (t.total_questions || 0) > 0).length;

        const topicsWithQuestions = (sub.topics || []).filter(t => (t.total_questions || 0) > 0);
        const boardFilterHtml = buildBoardFilterHtml();

        html += `
          <div class="qb-subject" data-collapsed="1" data-subject-id="${subjectId}">
            <div class="qb-subject-h" data-action="toggle-subject" role="button" tabindex="0" aria-expanded="false">
              <div class="qb-subject-title flex-grow-1" style="min-width:0;">
                <span class="d-inline-flex align-items-center" style="gap:.4rem;">
                  <i class="fas fa-book text-muted"></i>
                  <span class="text-truncate">${subjectName}</span>
                </span>
              </div>
              <div class="d-flex align-items-center flex-shrink-0" style="gap:.35rem; flex-wrap:wrap; justify-content:flex-end;">
                <span class="qb-pill" data-bs-toggle="tooltip" title="Questions in this subject">
                  <i class="fas fa-list-ol"></i> <b>${subTotals.total}</b>
                </span>
                <span class="qb-pill" data-bs-toggle="tooltip" title="Topics with questions">
                  <i class="fas fa-tag"></i> <b>${topicCount}</b>
                </span>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-qb-action="proof" data-class-id="${classId}" data-subject-id="${subjectId}" data-topic-id="0">Proof read</button>
                <span class="qb-pill qb-toggle-icon">
                  <i class="fas fa-chevron-down"></i>
                </span>
              </div>
            </div>

            <div class="qb-topic-wrap hidden">
              ${boardFilterHtml}
              <div class="qb-topic-list">
        `;

        topicsWithQuestions.forEach(topic => {
          const topicName = escapeHtml(topic.topic_name);
          const total = topic.total_questions || 0;
          const typeCountRows = buildTypeCountRows(topic);
          const boardIds = (topic.board_publisher_ids || []).join(',');
          const boardTagsHtml = buildBoardTagsHtml(topic);

          html += `
            <div class="qb-topic" data-board-ids="${escapeHtml(boardIds)}">
              <div class="qb-topic-title">
                <span class="d-flex align-items-start flex-grow-1" style="gap:.4rem;min-width:0;">
                  <i class="fas fa-tag text-muted mt-1"></i>
                  <span class="qb-topic-name">${topicName}</span>
                </span>
                <span class="qb-pill flex-shrink-0" data-bs-toggle="tooltip" title="Total questions in this topic">
                  <i class="fas fa-list-ol"></i> <b>${total}</b>
                </span>
              </div>
              ${boardTagsHtml}
              <div class="d-flex flex-wrap mb-2" style="gap:.35rem;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-qb-action="proof" data-class-id="${classId}" data-subject-id="${subjectId}" data-topic-id="${topic.topic_id}">Proof read</button>
                <button type="button" class="btn btn-outline-primary btn-sm" data-qb-action="edit" data-class-id="${classId}" data-subject-id="${subjectId}" data-topic-id="${topic.topic_id}">Edit all</button>
              </div>
              ${typeCountRows ? `<div class="qb-counts">${typeCountRows}</div>` : ''}
            </div>
          `;
        });

        html += `
              </div>
            </div>
          </div>
        `;
      });

      html += `
          </div>
        </div>
      `;
    });

    $tree.innerHTML = html;
  }

  function setClassCollapsed(card, collapsed){
    const body = card.querySelector('.qb-subject-wrap');
    const head = card.querySelector('.qb-class-h');
    const icon = card.querySelector('.qb-class-h .qb-toggle-icon i');
    card.setAttribute('data-collapsed', collapsed ? '1' : '0');
    body?.classList.toggle('hidden', collapsed);
    head?.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    if (icon) icon.className = collapsed ? 'fas fa-chevron-down' : 'fas fa-chevron-up';
    if (collapsed) {
      card.querySelectorAll('.qb-subject').forEach(sub => setSubjectCollapsed(sub, true));
    }
  }

  function setSubjectCollapsed(card, collapsed){
    const body = card.querySelector('.qb-topic-wrap');
    const head = card.querySelector('.qb-subject-h');
    const icon = card.querySelector('.qb-subject-h .qb-toggle-icon i');
    card.setAttribute('data-collapsed', collapsed ? '1' : '0');
    body?.classList.toggle('hidden', collapsed);
    head?.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    if (icon) icon.className = collapsed ? 'fas fa-chevron-down' : 'fas fa-chevron-up';
  }

  function collapseOtherClasses(exceptCard){
    document.querySelectorAll('.qb-class').forEach(c => {
      if (c !== exceptCard) setClassCollapsed(c, true);
    });
  }

  function collapseOtherSubjects(parentClass, exceptCard){
    parentClass.querySelectorAll('.qb-subject').forEach(s => {
      if (s !== exceptCard) setSubjectCollapsed(s, true);
    });
  }

  function bindToggles(){
    $tree.addEventListener('click', function(e){
      const bpBtn = e.target.closest('.qb-bp-toggle');
      if (bpBtn) {
        e.preventDefault();
        e.stopPropagation();
        const on = !bpBtn.classList.contains('active');
        bpBtn.classList.toggle('active', on);
        bpBtn.setAttribute('aria-pressed', on ? 'true' : 'false');
        const subjectCard = bpBtn.closest('.qb-subject');
        if (subjectCard) applySubjectTopicFilter(subjectCard);
        return;
      }

      const qbBtn = e.target.closest('[data-qb-action]');
      if (qbBtn) {
        e.preventDefault();
        e.stopPropagation();
        const action = qbBtn.getAttribute('data-qb-action');
        const cid = parseInt(qbBtn.getAttribute('data-class-id') || '0', 10);
        const sid = parseInt(qbBtn.getAttribute('data-subject-id') || '0', 10);
        const tid = parseInt(qbBtn.getAttribute('data-topic-id') || '0', 10);
        if (action === 'proof') {
          qbOpenProofInNewTab(cid, sid, tid);
        } else if (action === 'edit') {
          qbNavigateToForm(cid, sid, tid, true);
        }
        return;
      }

      const classHead = e.target.closest('.qb-class-h[data-action="toggle-class"]');
      if (classHead) {
        const card = classHead.closest('.qb-class');
        const collapsed = card.getAttribute('data-collapsed') === '1';
        if (collapsed) {
          collapseOtherClasses(card);
          setClassCollapsed(card, false);
        } else {
          setClassCollapsed(card, true);
        }
        return;
      }

      const subjectHead = e.target.closest('.qb-subject-h[data-action="toggle-subject"]');
      if (subjectHead) {
        const card = subjectHead.closest('.qb-subject');
        const parentClass = subjectHead.closest('.qb-class');
        const collapsed = card.getAttribute('data-collapsed') === '1';
        if (collapsed) {
          if (parentClass) collapseOtherSubjects(parentClass, card);
          setSubjectCollapsed(card, false);
        } else {
          setSubjectCollapsed(card, true);
        }
      }
    });

    $tree.addEventListener('keydown', function(e){
      if (e.key !== 'Enter' && e.key !== ' ') return;
      const classHead = e.target.closest('.qb-class-h[data-action="toggle-class"]');
      const subjectHead = e.target.closest('.qb-subject-h[data-action="toggle-subject"]');
      if (!classHead && !subjectHead) return;
      e.preventDefault();
      if (classHead) classHead.click();
      else if (subjectHead) subjectHead.click();
    });

    $btnCollapse && $btnCollapse.addEventListener('click', function(){
      document.querySelectorAll('.qb-class').forEach(c => setClassCollapsed(c, true));
    });

    $btnReload && $btnReload.addEventListener('click', loadAllSummary);
  }

  function initTooltips(){
    if (window.jQuery && window.jQuery.fn.tooltip) {
      window.jQuery('[data-bs-toggle="tooltip"]').tooltip({ container:'body', boundary:'window' });
    }
  }

  async function loadAllSummary(){
    showLoader();
    try{
      const res = await fetch(urlAllSummary, { headers: {'X-Requested-With':'XMLHttpRequest'} });
      const json = await res.json();

      if (!json || !json.status || !json.data || !json.data.length){
        showEmpty();
        return;
      }

      boardPublishers = Array.isArray(json.board_publishers) ? json.board_publishers : [];
      buildTree(json.data);
      showTree();
      initTooltips();
    }catch(err){
      console.error(err);
      showEmpty();
    }
  }

  bindToggles();
  loadAllSummary();
})();
</script>

  </section>

<?= $this->endSection() ?>
