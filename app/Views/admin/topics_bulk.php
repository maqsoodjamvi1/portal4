<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
  .qb-topics-page .editor-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
  }

  .qb-topics-page .editor-toolbar__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
  }

  .qb-topics-page .topic-desc {
    min-height: 2.75rem;
    resize: vertical;
    width: 100%;
  }

  .qb-topics-page .topic-desc-row td {
    background: #f8f9fa;
    border-top: 0;
    padding-top: 0.35rem;
    padding-bottom: 0.65rem;
  }

  .qb-topics-page .topic-desc-label {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #6c757d;
    margin-bottom: 0.25rem;
  }

  .qb-topics-page .topic-group-spacer td {
    border: 0;
    padding: 0;
    height: 0.5rem;
    background: transparent;
  }

  .qb-topics-page #topicTable tr.topic-main-row td {
    vertical-align: middle;
  }

  @media (min-width: 768px) {
    .qb-topics-page #topicTable tr.topic-main-row td {
      border-bottom: 0;
    }

    .qb-topics-page #topicTable tr.topic-desc-row td {
      border-top: 1px dashed #dee2e6;
    }

    .qb-topics-page #topicTable tr.topic-desc-row {
      margin-bottom: 0.25rem;
    }
  }

  .qb-topics-page .save-bar {
    display: flex;
    justify-content: flex-end;
    margin-top: 1rem;
  }

  .qb-topics-page .bp-toggle-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    align-items: center;
  }

  .qb-topics-page .bp-toggle {
    border: 1px solid #ced4da;
    background: #fff;
    color: #495057;
    border-radius: 0.35rem;
    padding: 0.2rem 0.45rem;
    font-size: 0.72rem;
    font-weight: 600;
    line-height: 1.25;
    letter-spacing: 0.02em;
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    min-width: 2.1rem;
    text-align: center;
  }

  .qb-topics-page .bp-toggle:hover {
    border-color: #80bdff;
    color: #0056b3;
  }

  .qb-topics-page .bp-toggle.active {
    background: #007bff;
    border-color: #007bff;
    color: #fff;
  }

  .qb-topics-page .bp-toggle:focus {
    outline: none;
    box-shadow: 0 0 0 0.15rem rgba(0, 123, 255, 0.25);
  }

  /* ========== ENHANCED MOBILE STYLES ========== */
  @media (max-width: 767.98px) {
    .qb-topics-page .sms-page-header h1,
    .qb-topics-page .sms-page-header__title {
      font-size: 1.35rem;
    }

    .qb-topics-page .editor-toolbar {
      flex-direction: column;
      align-items: stretch;
    }

    .qb-topics-page .editor-toolbar__actions {
      width: 100%;
    }

    .qb-topics-page .editor-toolbar__actions .btn {
      flex: 1 1 calc(50% - 0.25rem);
    }

    /* Hide table header on mobile */
    .qb-topics-page #topicTable thead {
      display: none;
    }

    /* Card-based layout for topics */
    .qb-topics-page #topicTable,
    .qb-topics-page #topicTable tbody,
    .qb-topics-page #topicTable tr,
    .qb-topics-page #topicTable td {
      display: block;
      width: 100%;
    }

    /* Each topic becomes a standalone card */
    .qb-topics-page #topicTable tr.topic-main-row {
      border: 1px solid #dee2e6;
      border-radius: 0.5rem 0.5rem 0 0;
      margin-bottom: 0;
      padding: 0.85rem;
      background: #fff;
      box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    /* Description row attaches below main row like a card footer */
    .qb-topics-page #topicTable tr.topic-desc-row {
      border: 1px solid #dee2e6;
      border-top: 0;
      border-radius: 0 0 0.5rem 0.5rem;
      padding: 0.85rem;
      margin-bottom: 1rem;
      background: #fafafa;
    }

    .qb-topics-page #topicTable tr.topic-group-spacer {
      display: none;
    }

    /* Remove cell borders and padding */
    .qb-topics-page #topicTable tr.topic-main-row td,
    .qb-topics-page #topicTable tr.topic-desc-row td {
      border: 0;
      padding: 0.4rem 0;
    }

    /* Description row cell styling */
    .qb-topics-page #topicTable tr.topic-desc-row td {
      background: transparent;
      padding: 0;
    }

    /* Label styling for each field */
    .qb-topics-page #topicTable td[data-label]::before {
      content: attr(data-label);
      display: block;
      font-size: 0.7rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.03em;
      color: #6c757d;
      margin-bottom: 0.3rem;
    }

    /* No label for description row since it's obvious */
    .qb-topics-page #topicTable tr.topic-desc-row td::before {
      display: none;
    }

    /* Topic number styling */
    .qb-topics-page #topicTable td.row-num-cell {
      font-weight: 600;
      color: #007bff;
      padding-bottom: 0.3rem;
    }
    .qb-topics-page #topicTable td.row-num-cell::before {
      content: 'Topic';
      margin-right: 0.5rem;
      color: #6c757d;
    }

    /* Boards toggle group on mobile - more compact */
    .qb-topics-page .bp-toggle-group {
      gap: 0.3rem;
      margin-top: 0.2rem;
    }
    .qb-topics-page .bp-toggle {
      padding: 0.25rem 0.6rem;
      font-size: 0.7rem;
      min-width: 2.3rem;
    }

    /* ENHANCED: Description field takes more space on mobile */
    .qb-topics-page .topic-desc {
      font-size: 0.9rem;
      padding: 0.6rem;
      min-height: 100px;  /* Taller description field on mobile */
      border-radius: 0.4rem;
      background-color: #fff;
    }

    /* Topic name input larger on mobile */
    .qb-topics-page .topic-name {
      font-size: 1rem;
      padding: 0.6rem;
    }

    /* Save button full width */
    .qb-topics-page .save-bar {
      justify-content: stretch;
      margin-top: 1rem;
    }
    .qb-topics-page .save-bar .btn {
      width: 100%;
      padding: 0.7rem;
      font-size: 1rem;
    }

    /* Add row button more prominent */
    .qb-topics-page #btnAddRow {
      padding: 0.5rem 0.8rem;
    }

    /* Card title style for each topic */
    .qb-topics-page #topicTable tr.topic-main-row {
      position: relative;
    }

    /* Subtle separator between main and description */
    .qb-topics-page #topicTable tr.topic-desc-row {
      margin-bottom: 1.2rem;
    }
  }

  /* Even smaller phones (below 480px) */
  @media (max-width: 480px) {
    .qb-topics-page .topic-desc {
      min-height: 120px;  /* Even taller on very small screens */
    }
    .qb-topics-page #topicTable tr.topic-main-row {
      padding: 0.7rem;
    }
    .qb-topics-page #topicTable tr.topic-desc-row {
      padding: 0.7rem;
    }
  }
</style>

<?= view('components/page_header', [
    'title' => 'Bulk Topic Manager',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Bulk Topic Manager', 'active' => true],
    ],
]) ?>

<section class="content qb-topics-page">

  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Filter (Class + Subject)</h3>
    </div>

    <div class="card-body">
      <div class="row">
        <div class="form-group col-12 col-md-6">
          <label>Class</label>
          <select id="class_id" class="form-control">
            <option value="">-- Select Class --</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?= $c->class_id ?>"><?= esc($c->class_name) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group col-12 col-md-6">
          <label>Subject</label>
          <select id="subject_id" class="form-control" disabled>
            <option value="">-- Select Subject --</option>
          </select>
        </div>
      </div>

      <div class="row mb-2">
        <div class="form-group col-12 col-md-6">
          <label>Filter by Boards / Publisher <span class="text-muted">(optional)</span></label>
          <div id="filter_board_publisher" class="bp-toggle-group" role="group" aria-label="Filter boards publisher">
            <?php if (empty($boardPublishers)): ?>
              <span class="text-muted small">No boards/publishers yet.</span>
            <?php else: ?>
              <?php foreach ($boardPublishers as $bp):
                $bpCode = trim((string) ($bp['short_code'] ?? ''));
                if ($bpCode === '') {
                    $bpName = trim((string) ($bp['name'] ?? ''));
                    $bpWords = preg_split('/\s+/', $bpName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                    if (count($bpWords) >= 2) {
                        $bpCode = strtoupper(mb_substr($bpWords[0], 0, 1) . mb_substr($bpWords[1], 0, 1));
                    } else {
                        $bpCode = mb_strlen($bpName) > 6 ? mb_substr($bpName, 0, 5) . '…' : $bpName;
                    }
                }
              ?>
                <button type="button"
                        class="bp-toggle"
                        data-id="<?= (int) $bp['id'] ?>"
                        title="<?= esc($bp['name'] ?? '') ?>"
                        aria-label="<?= esc($bp['name'] ?? '') ?>"
                        aria-pressed="false"><?= esc($bpCode) ?></button>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <small class="text-muted">Short codes shown — hover for full board name. Tap to filter; leave all off to show every topic.</small>
        </div>
        <div class="form-group col-12 col-md-6 d-flex align-items-end">
          <a href="<?= base_url('admin/qb-board-publishers') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-university"></i> Manage Boards / Publisher list
          </a>
        </div>
      </div>

      <hr>

      <div id="editorWrap" style="display:none;">
        <div class="editor-toolbar mb-2">
          <h5 class="mb-0">
            Topics Editor
            <small class="text-muted d-block d-md-inline">(Edit existing / add new rows)</small>
          </h5>

          <div class="editor-toolbar__actions">
            <button type="button" class="btn btn-secondary btn-sm" id="btnAddRow">
              + Add Row
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnClearNew">
              Clear Empty Rows
            </button>
          </div>
        </div>

        <form id="bulkTopicForm">
          <?= csrf_field() ?>
          <input type="hidden" name="class_id" id="f_class_id">
          <input type="hidden" name="subject_id" id="f_subject_id">

          <div class="table-responsive">
            <table class="table table-bordered table-sm" id="topicTable">
              <thead class="table-light">
                <tr>
                  <th style="width:60px;">#</th>
                  <th style="min-width:180px;">Topic Name</th>
                  <th style="min-width:200px;">Boards <span class="text-muted fw-normal">(short code)</span></th>
                </tr>
              </thead>
              <tbody id="topicTbody">
              </tbody>
            </table>
          </div>

          <div class="save-bar">
            <button type="submit" class="btn btn-success" id="btnSaveAll">
              <i class="fa fa-save"></i> Save All
            </button>
          </div>
        </form>
      </div>

    </div>
  </div>

</section>
<script>
(function () {
  const subjectUrl = '<?= base_url('admin/question-bank/subjects') ?>';
  const dataUrl    = '<?= base_url('admin/qb-topics/data') ?>';
  const saveUrl    = '<?= base_url('admin/qb-topics/save') ?>';

  const csrfHash = '<?= csrf_hash() ?>';
  const boardPublishers = <?= json_encode($boardPublishers ?? []) ?>;

  const classSel   = document.getElementById('class_id');
  const filterBoardGroup = document.getElementById('filter_board_publisher');
  const subjectSel = document.getElementById('subject_id');
  const editorWrap = document.getElementById('editorWrap');

  const tbody      = document.getElementById('topicTbody');
  const form       = document.getElementById('bulkTopicForm');
  const fClass     = document.getElementById('f_class_id');
  const fSubject   = document.getElementById('f_subject_id');

  const btnAddRow   = document.getElementById('btnAddRow');
  const btnClearNew = document.getElementById('btnClearNew');

  let rowIndex = 0;

  function escapeHtml(s) {
    return (s || '').toString().replace(/[&<>"']/g, function(m){
      return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]);
    });
  }

  function boardShortLabel(bp) {
    const code = (bp.short_code || '').toString().trim();
    if (code) return code;
    const name = (bp.name || '').toString().trim();
    if (!name) return '?';
    const words = name.split(/\s+/).filter(Boolean);
    if (words.length >= 2) {
      return words.slice(0, 2).map(function (w) { return w.charAt(0).toUpperCase(); }).join('');
    }
    return name.length > 6 ? name.substring(0, 5) + '…' : name;
  }

  function renumberRows() {
    const rows = tbody.querySelectorAll('tr.topic-main-row');
    rows.forEach((tr, i) => {
      const num = tr.querySelector('.row-num');
      if (num) num.textContent = (i + 1);
    });
  }

  function buildBoardToggleGroup(selectedIds) {
    if (!boardPublishers.length) {
      return '<span class="text-muted small">No boards/publishers yet. <a href="<?= base_url('admin/qb-board-publishers') ?>">Add some</a>.</span>';
    }

    const selected = new Set((selectedIds || []).map(function (v) { return String(v); }));
    let html = '<div class="bp-toggle-group topic-boards" role="group">';
    boardPublishers.forEach(function (bp) {
      const id = String(bp.id);
      const on = selected.has(id);
      const label = boardShortLabel(bp);
      const title = (bp.name || '').toString().trim();
      html += '<button type="button" class="bp-toggle' + (on ? ' active' : '') + '"'
        + ' data-id="' + id + '"'
        + ' title="' + escapeHtml(title) + '"'
        + ' aria-label="' + escapeHtml(title) + '"'
        + ' aria-pressed="' + (on ? 'true' : 'false') + '">'
        + escapeHtml(label)
        + '</button>';
    });
    html += '</div>';
    return html;
  }

  function getSelectedBoardIds(container) {
    const root = container || document;
    const ids = [];
    root.querySelectorAll('.bp-toggle.active').forEach(function (btn) {
      const id = parseInt(btn.dataset.id || '0', 10);
      if (id > 0) ids.push(id);
    });
    return ids;
  }

  function getDescRow(mainTr) {
    const next = mainTr.nextElementSibling;
    return next && next.classList.contains('topic-desc-row') ? next : null;
  }

  function addRow(id = 0, name = '', description = '', boardIds = []) {
    const i = rowIndex++;
    const groupId = 'tg-' + i;

    const mainTr = document.createElement('tr');
    mainTr.className = 'topic-main-row';
    mainTr.dataset.group = groupId;

    mainTr.innerHTML = `
      <td class="row-num-cell" data-label="#">
        <span class="row-num">-</span>
      </td>
      <td data-label="Topic Name">
        <input type="hidden" class="topic-id" value="${parseInt(id || 0, 10)}">
        <input type="text"
               class="form-control form-control-sm topic-name"
               value="${escapeHtml(name)}"
               placeholder="Enter topic name..."
               autocomplete="off">
      </td>
      <td data-label="Boards / Publisher">
        ${buildBoardToggleGroup(boardIds)}
      </td>
    `;

    const descTr = document.createElement('tr');
    descTr.className = 'topic-desc-row';
    descTr.dataset.group = groupId;
    descTr.innerHTML = `
      <td colspan="3" class="topic-desc-cell">
        <label class="topic-desc-label d-none d-md-block mb-1">Description</label>
        <label class="topic-desc-label d-md-none">Description</label>
        <textarea class="form-control form-control-sm topic-desc"
                  rows="4"
                  placeholder="Optional description for this topic...">${escapeHtml(description)}</textarea>
      </td>
    `;

    tbody.appendChild(mainTr);
    tbody.appendChild(descTr);
    renumberRows();
  }

  function removeTopicGroup(mainTr) {
    const descTr = getDescRow(mainTr);
    mainTr.remove();
    if (descTr) descTr.remove();
  }

  function clearEmptyRows() {
    const rows = Array.from(tbody.querySelectorAll('tr.topic-main-row'));
    rows.forEach(mainTr => {
      const descTr = getDescRow(mainTr);
      const input = mainTr.querySelector('.topic-name');
      const desc  = descTr ? descTr.querySelector('.topic-desc') : null;
      const idInp = mainTr.querySelector('.topic-id');
      const idVal = idInp ? parseInt(idInp.value || '0', 10) : 0;
      const hasName = input && input.value.trim();
      const hasDesc = desc && desc.value.trim();

      if (idVal === 0 && !hasName && !hasDesc) {
        removeTopicGroup(mainTr);
      }
    });
    renumberRows();
  }

  function getRowsPayload() {
    const rows = Array.from(tbody.querySelectorAll('tr.topic-main-row'));
    const out = [];

    rows.forEach(mainTr => {
      const descTr = getDescRow(mainTr);
      const idVal = parseInt(mainTr.querySelector('.topic-id')?.value || '0', 10) || 0;
      const name  = (mainTr.querySelector('.topic-name')?.value || '').trim();
      const desc  = (descTr?.querySelector('.topic-desc')?.value || '').trim();
      if (!name) return;

      const boardIds = getSelectedBoardIds(mainTr);

      out.push({
        id: idVal,
        topic_name: name,
        description: desc,
        board_publisher_ids: boardIds
      });
    });

    return out;
  }

  function loadSubjects(classId) {
    subjectSel.innerHTML = '<option value="">-- Select Subject --</option>';
    subjectSel.disabled = true;

    if (!classId) return;

    fetch(subjectUrl + '?class_id=' + encodeURIComponent(classId), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(r => r.json())
      .then(d => {
        const subjects = Array.isArray(d) ? d : (d.subjects || []);
        subjectSel.innerHTML = '<option value="">-- Select Subject --</option>';

        subjects.forEach(s => {
          const o = document.createElement('option');
          o.value = s.subject_id;
          o.textContent = s.subject_name || s.subject_short_name || ('Subject ' + s.subject_id);
          subjectSel.appendChild(o);
        });

        subjectSel.disabled = false;
      })
      .catch(err => {
        console.error('subjects load error', err);
        subjectSel.disabled = true;
      });
  }

  function loadTopics(classId, subjectId) {
    tbody.innerHTML = '';
    rowIndex = 0;
    editorWrap.style.display = 'none';

    if (!classId || !subjectId) return;

    let url = dataUrl
      + '?class_id=' + encodeURIComponent(classId)
      + '&subject_id=' + encodeURIComponent(subjectId);

    getSelectedBoardIds(filterBoardGroup).forEach(function (id) {
      url += '&board_publisher_ids[]=' + encodeURIComponent(String(id));
    });

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json())
      .then(res => {
        if (!res || res.status !== 'ok') {
          alert(res?.msg || 'Failed to load topics.');
          return;
        }

        const rows = res.rows || [];
        rows.forEach(r => addRow(r.id, r.topic_name, r.description || '', r.board_publisher_ids || []));

        addRow(0, '');

        fClass.value = classId;
        fSubject.value = subjectId;

        editorWrap.style.display = 'block';
      })
      .catch(err => {
        console.error('topics data error', err);
        alert('Failed to load topics.');
      });
  }

  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.bp-toggle');
    if (!btn) return;

    const on = !btn.classList.contains('active');
    btn.classList.toggle('active', on);
    btn.setAttribute('aria-pressed', on ? 'true' : 'false');

    if (btn.closest('#filter_board_publisher') && classSel.value && subjectSel.value) {
      loadTopics(classSel.value, subjectSel.value);
    }
  });

  classSel.addEventListener('change', function () {
    editorWrap.style.display = 'none';
    loadSubjects(classSel.value);
  });

  subjectSel.addEventListener('change', function () {
    if (classSel.value && subjectSel.value) {
      loadTopics(classSel.value, subjectSel.value);
    } else {
      editorWrap.style.display = 'none';
    }
  });

  btnAddRow.addEventListener('click', function () {
    addRow(0, '');
    const rows = tbody.querySelectorAll('tr.topic-main-row');
    const lastMain = rows.length ? rows[rows.length - 1] : null;
    const lastName = lastMain ? lastMain.querySelector('.topic-name') : null;
    if (lastName) lastName.focus();
  });

  btnClearNew.addEventListener('click', function () {
    clearEmptyRows();
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    clearEmptyRows();

    const classId = fClass.value;
    const subjectId = fSubject.value;

    if (!classId || !subjectId) {
      alert('Select Class and Subject first.');
      return;
    }

    const topics = getRowsPayload();
    if (!topics.length) {
      alert('Please enter at least one topic name.');
      return;
    }

    const payload = {
      class_id: classId,
      subject_id: subjectId,
      topics: topics
    };

    fetch(saveUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfHash
      },
      body: JSON.stringify(payload)
    })
      .then(r => r.json())
      .then(res => {
        if (!res || res.status !== 'ok') {
          alert(res?.msg || 'Failed to save.');
          console.log('Save response:', res);
          return;
        }

        alert(res.msg || 'Saved successfully.');
        loadTopics(classSel.value, subjectSel.value);
      })
      .catch(err => {
        console.error('save error', err);
        alert('Server error while saving topics.');
      });
  });

})();
</script>

<?= $this->endSection() ?>
