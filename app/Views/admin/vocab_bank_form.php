<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <h1>Vocabulary Bank (Bulk JSON Import / Manual)</h1>
</section>

<section class="content">

  <?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('msg')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header"><h3 class="card-title">Add Vocabulary</h3></div>

    <!-- point to your VocabBank save() -->
    <form id="vocabForm" action="<?= base_url('admin/vocab-bank/save') ?>" method="post">
      <?= csrf_field() ?>
      <div class="card-body">

        <!-- Class / Subject / Topic -->
        <div class="form-row mb-3">
          <div class="form-group col-md-4">
            <label for="class_id">Class</label>
            <select name="class_id_master" id="class_id" class="form-control" required>
              <option value="">-- Select Class --</option>
              <?php foreach ($classes as $c): ?>
                <option value="<?= $c->class_id ?>"><?= esc($c->class_name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group col-md-4">
            <label for="subject_id">Subject</label>
            <select name="subject_id_master" id="subject_id" class="form-control" required>
              <option value="">-- Select Subject --</option>
            </select>
          </div>

          <div class="form-group col-md-4">
            <label for="topic_id">Topic</label>
            <div class="input-group">
              <select name="topic_id_master" id="topic_id" class="form-control" required>
                <option value="">-- Select Topic --</option>
              </select>
              <div class="input-group-append">
                <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#topicModal">+</button>
              </div>
            </div>
          </div>
        </div>

        <div class="form-row mb-2">
  <div class="form-group col-md-12">
    <div id="vocabCountInfo"
         class="alert alert-light border py-1 px-2 mb-0 small"
         style="display:none;">
      <div>
        Already <strong><span id="vocabCountNumber">0</span></strong>
        word(s) saved for this <strong>Class + Subject + Topic</strong>.
      </div>

      <div class="d-flex align-items-start mt-1">
        <small class="text-muted mr-2 mb-0" style="white-space:nowrap;">Words:</small>
        <div style="flex:1; max-height:60px; overflow-y:auto; font-size:11px;">
          <span id="vocabWordList"></span>
        </div>
        <button type="button"
                id="btnCopyVocabWords"
                class="btn btn-xs btn-outline-secondary ml-2"
                style="font-size:11px; padding:2px 6px; display:none;">
          Copy
        </button>
      </div>
    </div>
  </div>
</div>

        <!-- ================= JSON Loader ================= -->
        <div class="card mb-3 border-info">
          <div class="card-header bg-info text-white"><strong>Bulk Vocabulary JSON Loader</strong></div>
          <div class="card-body">
            <div class="form-group">
              <label>Paste Vocabulary JSON (format: {"vocab":[ ... ]})</label>
              <textarea id="vocab_json" class="form-control" rows="8" placeholder='{
  "vocab": [
    {
      "word": "Victory",
      "meaning_en": "Winning or success",
      "meaning_ur": "کامیابی",
      "example_sentence": "The team celebrated their victory.",
      "part_of_speech": "Noun",

      "syllables": "vic-to-ry",
      "phonics_pattern": "CVC-CV-CV",
      "synonyms": "win, success, triumph",
      "antonyms": "defeat, loss",
      "related_words": "victorious, victor",
      "confusing_pair": "",
      "confusing_pair_difference": "",

      "difficulty_level": "Easy",
      "dictation_focus": "Spelling and syllable breaking"
    }
  ]
}
'></textarea>
            </div>
            <div>
              <button type="button" id="btnParseVocab" class="btn btn-info">Parse JSON</button>
            </div>
          </div>
        </div>

        <!-- Parsed cards -->
        <div id="jsonPreview" style="display:none;" class="mb-3">
          <label>Parsed Vocabulary (tick to load into form):</label>
          <div id="previewCards" class="d-flex flex-wrap"></div>
          <div class="mt-2">
            <button type="button" id="btnSelectAll" class="btn btn-sm btn-outline-primary">Select All</button>
            <button type="button" id="btnUnselectAll" class="btn btn-sm btn-outline-secondary">Unselect</button>
            <button type="button" id="btnLoadSelected" class="btn btn-sm btn-success">Load Selected to Form</button>
          </div>
        </div>

        <!-- ================= Manual / Loaded Vocab Table ================= -->
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0">Vocabulary to Save</h5>
          <div>
            <button type="button" id="btnAddRow" class="btn btn-secondary btn-sm">+ Add Row</button>
            <button type="button" id="btnClearRows" class="btn btn-outline-danger btn-sm">Clear All</button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered table-sm" id="vocabTable">
           <thead class="thead-light">
<tr>
  <th style="width:3%"></th>
  <th>Word</th>
  <th>Meaning (EN)</th>
  <th>Meaning (UR)</th>
  <th>Example</th>
  <th>POS</th>
  <th>Syllables</th>
  <th>Synonyms</th>
  <th>Antonyms</th>
  <th>Related Words</th>
  <th>Confusing Pair</th>
  <th>Difference</th>
  <th>Difficulty</th>
  <th>Focus</th>
  <th>Pattern</th>
</tr>
</thead>

            <tbody>
              <!-- rows injected here -->
            </tbody>
          </table>
        </div>

        <small class="form-text text-muted">When you click Save, each row will be inserted into <code>vocabulary_bank</code> with the selected Class / Subject / Topic.</small>

      </div>

      <div class="card-footer text-right">
        <button type="submit" class="btn btn-success">Save All Vocabulary</button>
      </div>
    </form>
  </div>
</section>

<!-- Topic modal (keeps same endpoints as before) -->
<div class="modal fade" id="topicModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <form id="topicForm" action="<?= base_url('admin/vocab-bank/save-topic') ?>" method="post">
      <?= csrf_field() ?>
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Topic</h5>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="t_class_id" name="class_id">
          <input type="hidden" id="t_subject_id" name="subject_id">
          <div class="form-group">
            <label>Topic Name</label>
            <input type="text" id="t_topic_name" name="topic_name" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save Topic</button>
        </div>
      </div>
    </form>
  </div>
</div>

<style>
  /* small niceties */
  #previewCards .card { width: 260px; margin-right:10px; margin-bottom:10px; position:relative; }
  #previewCards .card input[type=checkbox] { position:absolute; right:8px; top:8px; transform:scale(1.1); }
  .vocab-remove { cursor:pointer; color:#c00; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

  // Refs
  const classSelect      = document.getElementById('class_id');
  const subjectSelect    = document.getElementById('subject_id');
  const topicSelect      = document.getElementById('topic_id');

  const vocabJsonInput   = document.getElementById('vocab_json');
  const btnParseVocab    = document.getElementById('btnParseVocab');
  const jsonPreview      = document.getElementById('jsonPreview');
  const previewCards     = document.getElementById('previewCards');
  const btnSelectAll     = document.getElementById('btnSelectAll');
  const btnUnselectAll   = document.getElementById('btnUnselectAll');
  const btnLoadSelected  = document.getElementById('btnLoadSelected');

  const vocabCountInfo   = document.getElementById('vocabCountInfo');
  const vocabCountNumber = document.getElementById('vocabCountNumber');
  const vocabWordList    = document.getElementById('vocabWordList');
  const btnCopyVocabWords= document.getElementById('btnCopyVocabWords');

  const btnAddRow        = document.getElementById('btnAddRow');
  const btnClearRows     = document.getElementById('btnClearRows');
  const vocabTableBody   = document.querySelector('#vocabTable tbody');
  const vocabForm        = document.getElementById('vocabForm');

  if (!classSelect || !subjectSelect || !topicSelect) {
    console.error('VocabBank: class/subject/topic select not found in DOM.');
    return;
  }

  // Keep parsed vocab list in memory
  let parsedVocab = [];

  // =============== SMALL HELPERS ===============

  // simple HTML escape
  function escapeHtml(s) {
    return String(s)
      .replace(/&/g,'&amp;')
      .replace(/"/g,'&quot;')
      .replace(/</g,'&lt;')
      .replace(/>/g,'&gt;');
  }

  // Rebuild input names after deletion so server receives contiguous indices
  function rebuildRowNames() {
    const rows = vocabTableBody.querySelectorAll('tr');
    rows.forEach((row, idx) => {
      row.querySelectorAll('input').forEach(inp => {
        const name = inp.getAttribute('name') || '';
        const newName = name.replace(/vocab\[\d+\]/, `vocab[${idx}]`);
        inp.setAttribute('name', newName);
      });
    });
  }

  // Utility: create one empty row (or with data)
 function addRow(data = {}) {
  const rowIndex = vocabTableBody.querySelectorAll('tr').length;
  const r = document.createElement('tr');

  r.innerHTML = `
    <td class="align-middle text-center">
      <span class="vocab-remove">&times;</span>
    </td>

    <td><input name="vocab[${rowIndex}][word]" class="form-control form-control-sm" value="${escapeHtml(data.word||'')}" required></td>

    <td><input name="vocab[${rowIndex}][meaning_en]" class="form-control form-control-sm" value="${escapeHtml(data.meaning_en||'')}"></td>

    <td><input name="vocab[${rowIndex}][meaning_ur]" class="form-control form-control-sm" value="${escapeHtml(data.meaning_ur||'')}"></td>

    <td><input name="vocab[${rowIndex}][example_sentence]" class="form-control form-control-sm" value="${escapeHtml(data.example_sentence||'')}"></td>

    <td><input name="vocab[${rowIndex}][part_of_speech]" class="form-control form-control-sm" value="${escapeHtml(data.part_of_speech||'')}"></td>

    <td><input name="vocab[${rowIndex}][syllables]" class="form-control form-control-sm" value="${escapeHtml(data.syllables||'')}"></td>

    <td><input name="vocab[${rowIndex}][synonyms]" class="form-control form-control-sm" value="${escapeHtml(data.synonyms||'')}"></td>

    <td><input name="vocab[${rowIndex}][antonyms]" class="form-control form-control-sm" value="${escapeHtml(data.antonyms||'')}"></td>

    <td><input name="vocab[${rowIndex}][related_words]" class="form-control form-control-sm" value="${escapeHtml(data.related_words||'')}"></td>

    <td><input name="vocab[${rowIndex}][confusing_pair]" class="form-control form-control-sm" value="${escapeHtml(data.confusing_pair||'')}"></td>

    <td><input name="vocab[${rowIndex}][confusing_pair_difference]" class="form-control form-control-sm" value="${escapeHtml(data.confusing_pair_difference||'')}"></td>

      <td><input name="vocab[${rowIndex}][difficulty_level]" class="form-control form-control-sm" value="${escapeHtml(data.difficulty_level||'')}"></td>

        <td><input name="vocab[${rowIndex}][dictation_focus]" class="form-control form-control-sm" value="${escapeHtml(data.dictation_focus||'')}"></td>

          <td><input name="vocab[${rowIndex}][phonics_pattern]" class="form-control form-control-sm" value="${escapeHtml(data.phonics_pattern||'')}"></td>
  `;

  r.querySelector('.vocab-remove').addEventListener('click', () => {
    r.remove();
    rebuildRowNames();
  });

  vocabTableBody.appendChild(r);
}


  // =============== JSON PARSE + PREVIEW ===============

  if (btnParseVocab) {
    btnParseVocab.addEventListener('click', () => {
      const raw = (vocabJsonInput.value || '').trim();
      if (!raw) { alert('Paste JSON first.'); return; }

      let parsed;
      try {
        parsed = JSON.parse(raw);
      } catch (e) {
        alert('Invalid JSON: ' + e.message);
        return;
      }

      // accept { "vocab": [ ... ] } or plain array
      let list = [];
      if (parsed && Array.isArray(parsed.vocab)) {
        list = parsed.vocab;
      } else if (Array.isArray(parsed)) {
        list = parsed;
      } else {
        alert('JSON must be an array or contain a "vocab" array.');
        return;
      }

      // normalize items (ensure keys exist)
     parsedVocab = list.map(item => ({
  word: item.word || '',
  meaning_en: item.meaning_en || '',
  meaning_ur: item.meaning_ur || '',
  example_sentence: item.example_sentence || '',
  part_of_speech: item.part_of_speech || '',
  syllables: item.syllables || '',
  synonyms: item.synonyms || '',
  antonyms: item.antonyms || '',
  related_words: item.related_words || '',
  confusing_pair: item.confusing_pair || '',
  confusing_pair_difference: item.confusing_pair_difference || '',
  difficulty_level: item.difficulty_level || '',
  dictation_focus: item.dictation_focus || '',
  phonics_pattern: item.phonics_pattern || ''
}));


      renderPreviewCards();
    });
  }

  function renderPreviewCards() {
    previewCards.innerHTML = '';
    if (!parsedVocab.length) {
      jsonPreview.style.display = 'none';
      return;
    }

    parsedVocab.forEach((v, idx) => {
      const card = document.createElement('div');
      card.className = 'card p-2';

      const chk = document.createElement('input');
      chk.type = 'checkbox';
      chk.dataset.idx = idx;

      const body = document.createElement('div');
      body.innerHTML = `<strong>${escapeHtml(v.word)}</strong><br>
        <small>${escapeHtml(v.meaning_en)}</small><br>
        <small>${escapeHtml(v.meaning_ur)}</small><br>
        <small>${escapeHtml(v.example_sentence)}</small><br>
        <small><em>${escapeHtml(v.part_of_speech)}</em></small>`;

      card.appendChild(chk);
      card.appendChild(body);
      previewCards.appendChild(card);

      // clicking card toggles checkbox
      card.addEventListener('click', (e) => {
        if (e.target.tagName.toLowerCase() === 'input') return;
        chk.checked = !chk.checked;
      });
    });

    jsonPreview.style.display = 'block';
  }

  if (btnSelectAll) {
    btnSelectAll.addEventListener('click', () => {
      previewCards.querySelectorAll('input[type=checkbox]').forEach(c => c.checked = true);
    });
  }

  if (btnUnselectAll) {
    btnUnselectAll.addEventListener('click', () => {
      previewCards.querySelectorAll('input[type=checkbox]').forEach(c => c.checked = false);
    });
  }

  if (btnLoadSelected) {
    btnLoadSelected.addEventListener('click', () => {
      const checked = Array.from(previewCards.querySelectorAll('input[type=checkbox]:checked'));
      if (!checked.length) { alert('Select at least one vocabulary item to load.'); return; }

      checked.forEach(chk => {
        const idx = parseInt(chk.dataset.idx, 10);
        if (!Number.isNaN(idx) && parsedVocab[idx]) {
          addRow(parsedVocab[idx]);
        }
      });

      previewCards.querySelectorAll('input[type=checkbox]').forEach(c => c.checked = false);
      vocabTableBody.scrollIntoView({ behavior: 'smooth' });
    });
  }

  // =============== ADD / CLEAR ROWS ===============

  if (btnAddRow) {
    btnAddRow.addEventListener('click', () => addRow({}));
  }

  if (btnClearRows) {
    btnClearRows.addEventListener('click', () => {
      if (!confirm('Clear all vocabulary rows?')) return;
      vocabTableBody.innerHTML = '';
    });
  }

  // =============== VOCAB COUNT + WORD LIST HELPERS ===============

  function resetVocabInfo() {
    if (!vocabCountInfo || !vocabCountNumber || !vocabWordList) return;
    vocabCountInfo.style.display = 'none';
    vocabCountNumber.textContent = '0';
    vocabWordList.textContent    = '';
    if (btnCopyVocabWords) btnCopyVocabWords.style.display = 'none';
  }

  function fetchVocabCount() {
  const cid = classSelect.value;
  const sid = subjectSelect.value;
  const tid = topicSelect.value;

  console.log('VocabBank: fetchVocabCount called', { cid, sid, tid });

  if (!vocabCountInfo || !vocabCountNumber || !vocabWordList) return;

  if (!cid || !sid || !tid) {
    resetVocabInfo();
    return;
  }

  const url = '<?= base_url('admin/vocab-bank/getCount') ?>'
    + '?class_id='   + encodeURIComponent(cid)
    + '&subject_id=' + encodeURIComponent(sid)
    + '&topic_id='   + encodeURIComponent(tid);

  console.log('VocabBank: requesting', url);

  fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => {
      const ct = r.headers.get('Content-Type') || '';
      if (!ct.includes('application/json')) {
        console.warn('VocabBank: response is not JSON, maybe login/HTML?', r);
      }
      return r.json();
    })
    .then(data => {
      console.log('VocabBank: response', data);

      if (!data || data.status !== 'ok') {
        resetVocabInfo();
        return;
      }

      const c    = parseInt(data.count || 0, 10);
      const list = Array.isArray(data.words) ? data.words : [];

      vocabCountNumber.textContent = c;

      if (c > 0 && list.length > 0) {
        vocabWordList.textContent    = list.join(', ');
        vocabCountInfo.style.display = 'block';
        if (btnCopyVocabWords) btnCopyVocabWords.style.display = 'inline-block';
      } else {
        vocabWordList.textContent    = '';
        vocabCountInfo.style.display = 'block';
        if (btnCopyVocabWords) btnCopyVocabWords.style.display = 'none';
      }
    })
    .catch(err => {
      console.error('VocabBank: fetchVocabCount error', err);
      resetVocabInfo();
    });
}

  // =============== CLASS / SUBJECT / TOPIC LOAD ===============

  // Class -> load subjects, reset topics & info
  classSelect.addEventListener('change', () => {
    const cid = classSelect.value;
    subjectSelect.innerHTML = '<option value="">-- Select Subject --</option>';
    topicSelect.innerHTML   = '<option value="">-- Select Topic --</option>';
    resetVocabInfo();

    if (!cid) return;

   fetch('<?= base_url('admin/question-bank/subjects') ?>?class_id=' + encodeURIComponent(cid))
      .then(r => r.json())
      .then(d => {
        const subjects = Array.isArray(d) ? d : (d.subjects || []);
        subjectSelect.innerHTML = '<option value="">-- Select Subject --</option>';
        subjects.forEach(s => {
          const o = document.createElement('option');
          o.value = s.subject_id;
          o.textContent = s.subject_name || s.subject_short_name || ('Subject ' + s.subject_id);
          subjectSelect.appendChild(o);
        });
        resetVocabInfo();
      })
      .catch(() => {
        subjectSelect.innerHTML = '<option value="">-- Select Subject --</option>';
        topicSelect.innerHTML   = '<option value="">-- Select Topic --</option>';
        resetVocabInfo();
      });
  });

  // Subject -> load topics, reset info
  subjectSelect.addEventListener('change', () => {
    const cid = classSelect.value;
    const sid = subjectSelect.value;
    topicSelect.innerHTML = '<option value="">Loading...</option>';
    resetVocabInfo();

    if (!cid || !sid) {
      topicSelect.innerHTML = '<option value="">-- Select Topic --</option>';
      return;
    }

    fetch('<?= base_url('admin/vocab-bank/topics') ?>?class_id=' + encodeURIComponent(cid) + '&subject_id=' + encodeURIComponent(sid))
      .then(r => r.json())
      .then(d => {
        const topics = Array.isArray(d) ? d : (d.topics || []);
        topicSelect.innerHTML = '<option value="">-- Select Topic --</option>';
        topics.forEach(t => {
          const o = document.createElement('option');
          o.value = t.id;
          o.textContent = t.topic_name || ('Topic ' + t.id);
          topicSelect.appendChild(o);
        });
        resetVocabInfo();
      })
      .catch(() => {
        topicSelect.innerHTML = '<option value="">-- Select Topic --</option>';
        resetVocabInfo();
      });
  });

  // Topic -> fetch count + word list 🔥
  topicSelect.addEventListener('change', () => {
    console.log('VocabBank: topicSelect change event fired, value =', topicSelect.value);
    fetchVocabCount();
  });

  // =============== TOPIC MODAL + SAVE ===============

  if (window.jQuery && $('#topicModal').length) {
    $('#topicModal').on('show.bs.modal', function () {
      if (!classSelect.value || !subjectSelect.value) {
        alert('Please select Class and Subject first.');
        $('#topicModal').modal('hide');
        return;
      }
      document.getElementById('t_class_id').value  = classSelect.value;
      document.getElementById('t_subject_id').value= subjectSelect.value;
      document.getElementById('t_topic_name').value= '';
      setTimeout(() => document.getElementById('t_topic_name').focus(), 150);
    });
  }

  const topicForm = document.getElementById('topicForm');
  if (topicForm) {
    topicForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const fd = new FormData(this);
      fetch(this.action, {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(r => r.json())
        .then(res => {
          if (res.status !== 'ok') {
            alert(res.msg || 'Failed to save topic');
            return;
          }
          const opt = new Option(res.topic_name, res.id, true, true);
          topicSelect.add(opt);
          topicSelect.value = res.id;
          if (window.jQuery) {
            $('#topicModal').modal('hide');
          }
          fetchVocabCount();
        })
        .catch(err => {
          console.error(err);
          alert('Error saving topic');
        });
    });
  }

  // =============== FORM SUBMIT ===============

  if (vocabForm) {
    vocabForm.addEventListener('submit', function (e) {
      const cid = classSelect.value;
      const sid = subjectSelect.value;
      const tid = topicSelect.value;

      if (!cid || !sid || !tid) {
        e.preventDefault();
        alert('Select Class, Subject and Topic first.');
        return false;
      }

      if (!vocabTableBody.querySelectorAll('tr').length) {
        e.preventDefault();
        alert('Add at least one vocabulary row.');
        return false;
      }

      document.querySelectorAll('input[name="vocab_master_class_id"], input[name="vocab_master_subject_id"], input[name="vocab_master_topic_id"]').forEach(n => n.remove());

      const hiddenClass = document.createElement('input');
      hiddenClass.type  = 'hidden';
      hiddenClass.name  = 'vocab_master_class_id';
      hiddenClass.value = cid;
      vocabForm.appendChild(hiddenClass);

      const hiddenSubject = document.createElement('input');
      hiddenSubject.type  = 'hidden';
      hiddenSubject.name  = 'vocab_master_subject_id';
      hiddenSubject.value = sid;
      vocabForm.appendChild(hiddenSubject);

      const hiddenTopic = document.createElement('input');
      hiddenTopic.type   = 'hidden';
      hiddenTopic.name   = 'vocab_master_topic_id';
      hiddenTopic.value  = tid;
      vocabForm.appendChild(hiddenTopic);

      rebuildRowNames();
    });
  }

  // =============== COPY WORDS TO CLIPBOARD ===============

  if (btnCopyVocabWords) {
    btnCopyVocabWords.addEventListener('click', function () {
      const text = (vocabWordList.textContent || '').trim();
      if (!text) {
        alert('No words to copy.');
        return;
      }

      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text)
          .then(() => alert('Vocabulary words copied to clipboard.'))
          .catch(() => alert('Unable to copy to clipboard.'));
      } else {
        const ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        try {
          document.execCommand('copy');
          alert('Vocabulary words copied to clipboard.');
        } catch (e) {
          alert('Unable to copy to clipboard.');
        }
        document.body.removeChild(ta);
      }
    });
  }

  // initial: add one empty row and reset info
  addRow({});
  resetVocabInfo();

}); // DOMContentLoaded
</script>


<?= $this->endSection() ?>
