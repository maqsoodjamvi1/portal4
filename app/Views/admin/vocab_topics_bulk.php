<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Bulk Topic Manager',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Bulk Topic Manager', 'active' => true],
    ],
]) ?>


<section class="content">

  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Filter (Class + Subject)</h3>
    </div>

    <div class="card-body">
      <div class="row">
        <div class="form-group col-md-4">
          <label>Class</label>
          <select id="class_id" class="form-control">
            <option value="">-- Select Class --</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?= $c->class_id ?>"><?= esc($c->class_name) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group col-md-4">
          <label>Subject</label>
          <select id="subject_id" class="form-control" disabled>
            <option value="">-- Select Subject --</option>
          </select>
        </div>

        <div class="form-group col-md-4 d-flex align-items-end">
          <button type="button" id="btnLoad" class="btn btn-primary" disabled>
            <i class="fa fa-search"></i> Load Topics
          </button>
          <div class="ms-2 small text-muted" id="loadHint"></div>
        </div>
      </div>

      <hr>

      <div id="editorWrap" style="display:none;">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <h5 class="mb-0">
            Topics Editor
            <small class="text-muted">(Edit existing / add new rows)</small>
          </h5>

          <div>
            <button type="button" class="btn btn-secondary btn-sm" id="btnAddRow">
              + Add Row
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm" id="btnClearNew">
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
                  <th>Topic Name</th>
                  <th style="width:120px;">Action</th>
                </tr>
              </thead>
              <tbody id="topicTbody">
              </tbody>
            </table>
          </div>

          <div class="text-end">
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
  const dataUrl    = '<?= base_url('admin/vocab-topics/data') ?>';
  const saveUrl    = '<?= base_url('admin/vocab-topics/save') ?>';

  const csrfName = '<?= csrf_token() ?>';
  const csrfHash = '<?= csrf_hash() ?>';

  const classSel   = document.getElementById('class_id');
  const subjectSel = document.getElementById('subject_id');
  const btnLoad    = document.getElementById('btnLoad');
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

  function renumberRows() {
    const rows = tbody.querySelectorAll('tr');
    rows.forEach((tr, i) => {
      const num = tr.querySelector('.row-num');
      if (num) num.textContent = (i + 1);
    });
  }

  function addRow(id = 0, name = '') {
    const i = rowIndex++;
    const tr = document.createElement('tr');
    tr.dataset.idx = String(i);

    tr.innerHTML = `
      <td class="row-num">-</td>
      <td>
        <input type="hidden" class="topic-id" value="${parseInt(id || 0, 10)}">
        <input type="text"
               class="form-control form-control-sm topic-name"
               value="${escapeHtml(name)}"
               placeholder="Enter topic name..."
               autocomplete="off">
      </td>
      <td class="text-center">
        <button type="button" class="btn btn-outline-danger btn-sm btn-remove">
          <i class="fa fa-trash"></i>
        </button>
      </td>
    `;

    tr.querySelector('.btn-remove').addEventListener('click', function () {
      tr.remove();
      renumberRows();
    });

    tbody.appendChild(tr);
    renumberRows();
  }

  function clearEmptyRows() {
    const rows = Array.from(tbody.querySelectorAll('tr'));
    rows.forEach(tr => {
      const input = tr.querySelector('.topic-name');
      const idInp = tr.querySelector('.topic-id');
      const idVal = idInp ? parseInt(idInp.value || '0', 10) : 0;

      // remove only empty NEW rows (id=0)
      if (idVal === 0 && (!input || !input.value.trim())) {
        tr.remove();
      }
    });
    renumberRows();
  }

  function getRowsPayload() {
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const out = [];

    rows.forEach(tr => {
      const idVal = parseInt(tr.querySelector('.topic-id')?.value || '0', 10) || 0;
      const name  = (tr.querySelector('.topic-name')?.value || '').trim();
      if (!name) return;

      out.push({ id: idVal, topic_name: name });
    });

    return out;
  }

  function loadSubjects(classId) {
    subjectSel.innerHTML = '<option value="">-- Select Subject --</option>';
    subjectSel.disabled = true;
    btnLoad.disabled = true;

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

    fetch(
      dataUrl
      + '?class_id=' + encodeURIComponent(classId)
      + '&subject_id=' + encodeURIComponent(subjectId),
      { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
    )
      .then(r => r.json())
      .then(res => {
        if (!res || res.status !== 'ok') {
          alert(res?.msg || 'Failed to load topics.');
          return;
        }

        const rows = res.rows || [];
        rows.forEach(r => addRow(r.id, r.topic_name));

        // 1 empty row for new topic
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

  // Events
  classSel.addEventListener('change', function () {
    editorWrap.style.display = 'none';
    loadSubjects(classSel.value);
  });

  subjectSel.addEventListener('change', function () {
    btnLoad.disabled = !(classSel.value && subjectSel.value);
  });

  btnLoad.addEventListener('click', function () {
    loadTopics(classSel.value, subjectSel.value);
  });

  btnAddRow.addEventListener('click', function () {
    addRow(0, '');
    const last = tbody.querySelector('tr:last-child .topic-name');
    if (last) last.focus();
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

    // Send JSON (robust)
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
        // CSRF header (works well for JSON posts)
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

        // reload to get new IDs for inserted rows
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
