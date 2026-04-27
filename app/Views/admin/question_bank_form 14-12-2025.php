  <?= $this->extend('layouts/admin_template') ?>
  <?= $this->section('content') ?>

  <section class="content-header">
    <h1>Question Bank (JSON MCQ Import)</h1>
  </section>

  <section class="content">

  <?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('msg')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header"><h3 class="card-title">Create Questions</h3></div>

    <form action="<?= base_url('admin/question-bank/save') ?>" method="post">
      <?= csrf_field() ?>
      <div class="card-body">

        <!-- ================= Class / Subject / Topic ================= -->
  <!-- ================= Class / Subject / Topic ================= -->
<div class="form-row">

  <!-- Class -->
  <div class="form-group col-md-4">
    <label for="class_id">Class</label>
    <select name="class_id" id="class_id" class="form-control" required>
      <option value="">-- Select Class --</option>
      <?php foreach ($classes as $c): ?>
        <option value="<?= $c->class_id ?>"><?= esc($c->class_name) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <!-- Subject -->
  <div class="form-group col-md-4">
    <label for="subject_id">Subject</label>
    <select name="subject_id" id="subject_id" class="form-control" required>
      <option value="">-- Select Subject --</option>
    </select>
  </div>

  <!-- Topic + "+" button aligned with the select -->
  <div class="form-group col-md-4">
    <label for="topic_id">Topic</label>
    <div class="input-group">
      <select name="topic_id" id="topic_id" class="form-control" required>
        <option value="">-- Select Topic --</option>
      </select>
      <div class="input-group-append">
        <button type="button"
                class="btn btn-secondary"
                data-toggle="modal"
                data-target="#topicModal">
          +
        </button>
      </div>
    </div>
  </div>

</div>


<!-- ================= Question Summary ================= -->
<div id="questionSummaryBox" class="card mt-3" style="display:none;">
  <div class="card-header text-white">
    <i class="fa fa-database"></i> Question Summary
  </div>

  <div class="card-body">
    <div class="row text-center">

      <!-- Total -->
      <div class="col-sm-2 mb-3 d-flex">
        <div class="qs-card flex-fill">
          <div class="qs-icon"><i class="fa fa-list-ul"></i></div>
          <div id="qsTotal" class="qs-value">0</div>
          <div class="qs-label">Total<br>Questions</div>
        </div>
      </div>

      <!-- MCQ -->
      <div class="col-sm-2 mb-3 d-flex">
        <div class="qs-card flex-fill">
          <div class="qs-icon"><i class="fa fa-check-circle"></i></div>
          <div id="qsMcq" class="qs-value">0</div>
          <div class="qs-label">MCQ<br>(Single)</div>
        </div>
      </div>

      <!-- Fill -->
      <div class="col-sm-2 mb-3 d-flex">
        <div class="qs-card flex-fill">
          <div class="qs-icon"><i class="fa fa-pen-alt"></i></div>
          <div id="qsFill" class="qs-value">0</div>
          <div class="qs-label">Fill in<br>the Blanks</div>
        </div>
      </div>

      <!-- Short -->
      <div class="col-sm-2 mb-3 d-flex">
        <div class="qs-card flex-fill">
          <div class="qs-icon"><i class="fa fa-comment-dots"></i></div>
          <div id="qsShort" class="qs-value">0</div>
          <div class="qs-label">Short<br>Questions</div>
        </div>
      </div>

      <!-- Match Drag -->
      <div class="col-sm-2 mb-3 d-flex">
        <div class="qs-card flex-fill">
          <div class="qs-icon"><i class="fa fa-arrows-alt"></i></div>
          <div id="qsMatchDrag" class="qs-value">1</div>
          <div class="qs-label">Match<br>(Draggable)</div>
        </div>
      </div>

      <!-- Match Non-Drag -->
      <div class="col-sm-2 mb-3 d-flex">
        <div class="qs-card flex-fill">
          <div class="qs-icon"><i class="fa fa-random"></i></div>
          <div id="qsMatchNoDrag" class="qs-value">0</div>
          <div class="qs-label">Match<br>(Normal)</div>
        </div>
      </div>

    </div>
  </div>
</div>



       

        <!-- ================= JSON  Loader ================= -->
    <div class="card mb-3 border-info">
    <div class="card-header bg-info text-white">
      <strong>Bulk Question JSON Loader (MCQ / True-False / Fill)</strong>
    </div>
    <div class="card-body">
      <div class="form-group">
        <label>Paste Questions JSON</label>
        <textarea
          id="mcq_json"
          class="form-control"
          rows="12"
          placeholder='Example:
  {
    "questions": [
      {
        "type": "mcq",
        "question": "Which word is a noun?",
        "option_a": "Run",
        "option_b": "Happy",
        "option_c": "Dog",
        "option_d": "Quickly",
        "correct_option": "C"
      },
      {
        "type": "tf",
        "question": "A pronoun is a describing word.",
        "answer_text": "False"
      },
      {
        "type": "fill",
        "question": "The sun rises in the ___.",
        "answer_text": "east"
      }
    ]
  }'
        ></textarea>
        <small class="form-text text-muted">
          Supported formats:<br>
          1) <code>{"questions": [ { type, question, option_a..d?, correct_option?, answer_text? } ]}</code><br>
          2) <code>{"mcqs": [ ... ]}</code> (old MCQ-only)<br>
          3) <code>[ { ... }, { ... } ]</code> (array of question objects)
        </small>
      </div>
      <button type="button" id="btnParseJson" class="btn btn-info">
        Load Questions
      </button>
    </div>
  </div>


        <div id="aiResults" style="display:none;" class="mb-3">
          <label>Parsed MCQs (tick to load into form):</label>
          <div id="aiCards" class="d-flex flex-wrap"></div>
        </div>

        <!-- ================= Multi-question container ================= -->
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0">Questions to Save</h5>
          <div>
            <button type="button" id="btnAddBlank" class="btn btn-secondary btn-sm">+ Add Blank</button>
            <button type="button" id="btnClearAll" class="btn btn-outline-danger btn-sm">Clear All</button>
          </div>
        </div>
        <div id="questionList"></div>

      </div>
      <div class="card-footer text-right">
        <button type="submit" class="btn btn-success">Save All Questions</button>
      </div>
    </form>
  </div>
  </section>

  <!-- ================= Topic Modal ================= -->
<div class="modal fade" id="topicModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <!-- point to your saveTopic route -->
    <form id="topicForm" action="<?= base_url('admin/question-bank/save-topic') ?>" method="post">
      <?= csrf_field() ?>
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Topic</h5>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">

          <!-- IMPORTANT: add name attributes that match saveTopic() -->
          <input type="hidden" id="t_class_id"   name="class_id">
          <input type="hidden" id="t_subject_id" name="subject_id">

          <div class="form-group">
            <label>Topic Name</label>
            <!-- name must be topic_name (your saveTopic() expects this) -->
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



  <style type="text/css">

    .qs-card {
  background: #ffffff;
  border-radius: 12px;
  padding: 18px 10px;
  box-shadow: 0 3px 8px rgba(0,0,0,0.12);
  transition: all 0.25s ease;
  min-height: 110px;
}

.qs-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 14px rgba(0,0,0,0.18);
}

/* Value styling */
.qs-value {
  font-size: 32px;
  font-weight: 700;
  color: #2b2f3a;
  margin-bottom: 6px;
}

/* Label styling */
.qs-label {
  font-size: 13px;
  color: #555;
  font-weight: 500;
  line-height: 1.2;
}

/* Icons */
.qs-icon {
  font-size: 26px;
  color: #3498db;
  margin-bottom: 5px;
}

/* Header */
#questionSummaryBox .card-header {
  background: linear-gradient(90deg, #0066ff, #0099ff);
  border-radius: 8px 8px 0 0;
  font-size: 18px;
  padding: 12px 15px;
}


   .qb-summary-card {
  border-radius: 0.75rem;
  box-shadow: 0 2px 4px rgba(0,0,0,.08);
  background: #ffffff;
  padding: 12px 10px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 90px;              /* same height for all */
}

/* big number */
.qb-summary-count {
  font-size: 1.8rem;
  font-weight: 700;
  margin-bottom: 4px;
}

/* label always keeps 2-line space */
.qb-summary-label {
  font-size: .85rem;
  font-weight: 600;
  text-transform: uppercase;
  text-align: center;
  line-height: 1.2;
  min-height: 2.4em;             /* force 2-line block */
  display: flex;
  align-items: center;
  justify-content: center;
}

/* colored left borders like your screenshot */
.qb-summary-border-total  { border-left: 4px solid #28a745; }
.qb-summary-border-mcq    { border-left: 4px solid #17a2b8; }
.qb-summary-border-fill   { border-left: 4px solid #20c997; }
.qb-summary-border-short  { border-left: 4px solid #ffc107; }
.qb-summary-border-drag   { border-left: 4px solid #6f42c1; }
.qb-summary-border-normal { border-left: 4px solid #dc3545; }

/* nicer header bar */
#qbSummaryBox .card-header {
  padding: .6rem 1rem;
}
#qbSummaryBox .card-header .fa {
  margin-right: 6px;
}

/* better widths on large screens – 6 cards in one row */
@media (min-width: 992px) {
  .qb-summary-col {
    flex: 0 0 16.6667%;
    max-width: 16.6667%;
  }
}

  </style>

  <!-- ========== Template for one question block ============= -->
<script type="text/template" id="tplQuestionBlock">
  <div class="card mb-3" data-qidx="{{i}}">
    <div class="card-header d-flex justify-content-between align-items-center">

       <input type="hidden" class="is-drag-hidden" name="questions[{{i}}][is_drag]" value="1">
      <strong>Question #{{n}}</strong>
      <div>
        <select name="questions[{{i}}][question_type]"
                class="form-control form-control-sm q-type"
                style="width:auto;display:inline-block;">
          <option value="mcq">MCQ</option>
          <option value="mcq_multi">MCQ (Multi)</option>
          <option value="tf">True / False</option>
          <option value="short">Short</option>
          <option value="fill">Fill</option>
          <option value="match">Match</option>
        </select>

        <button type="button" class="btn btn-light btn-sm ml-2 btn-move-up">↑</button>
        <button type="button" class="btn btn-light btn-sm btn-move-down">↓</button>
        <button type="button" class="btn btn-danger btn-sm ml-2 btn-remove">×</button>
      </div>
    </div>

    <div class="card-body">
      <!-- Hidden IDs -->
      <input type="hidden" name="questions[{{i}}][class_id]" value="{{class_id}}">
      <input type="hidden" name="questions[{{i}}][subject_id]" value="{{subject_id}}">
      <input type="hidden" name="questions[{{i}}][topic_id]" value="{{topic_id}}">

     <!-- Question text -->
      <div class="form-group">
        <label>Question</label>
        <textarea name="questions[{{i}}][question]"
                  class="form-control q-text"
                  rows="2"
                  required></textarea>
      </div>

      <!-- ================= MCQ / MCQ_MULTI ================= -->
     <div class="q-block q-mcq q-mcq_multi">
  <div class="form-row">
    <div class="form-group col-md-6">
      <label>A</label>
      <input type="text"
             class="form-control js-opt-a"
             name="questions[{{i}}][option_a]">
    </div>
    <div class="form-group col-md-6">
      <label>B</label>
      <input type="text"
             class="form-control js-opt-b"
             name="questions[{{i}}][option_b]">
    </div>
    <div class="form-group col-md-6">
      <label>C</label>
      <input type="text"
             class="form-control js-opt-c"
             name="questions[{{i}}][option_c]">
    </div>
    <div class="form-group col-md-6">
      <label>D</label>
      <input type="text"
             class="form-control js-opt-d"
             name="questions[{{i}}][option_d]">
    </div>
  </div>

  <div class="mcq-single-correct">
    <label>Correct Option</label>
    <select name="questions[{{i}}][correct_option]" class="form-control mcq-single-select">
      <option value="A">A</option>
      <option value="B">B</option>
      <option value="C">C</option>
      <option value="D">D</option>
    </select>
  </div>

  <div class="mcq-multi-correct">
    <label>Select Correct Options (Multiple)</label>
    <div>
      <label><input type="checkbox" class="mcq-multi-checkbox" data-letter="A" name="questions[{{i}}][correct_multi][]" value="A"> A</label><br>
      <label><input type="checkbox" class="mcq-multi-checkbox" data-letter="B" name="questions[{{i}}][correct_multi][]" value="B"> B</label><br>
      <label><input type="checkbox" class="mcq-multi-checkbox" data-letter="C" name="questions[{{i}}][correct_multi][]" value="C"> C</label><br>
      <label><input type="checkbox" class="mcq-multi-checkbox" data-letter="D" name="questions[{{i}}][correct_multi][]" value="D"> D</label><br>
    </div>
  </div>
</div>

      <!-- ================= TRUE / FALSE ================= -->
      <div class="q-block q-tf d-none">
        <label>Answer</label>
        <select name="questions[{{i}}][answer_text]" class="form-control">
          <option value="True">True</option>
          <option value="False">False</option>
        </select>
      </div>

      <!-- ================= SHORT ANSWER ================= -->
      <div class="q-block q-short d-none">
        <label>Expected Answer</label>
        <input type="text" name="questions[{{i}}][answer_text]" class="form-control">
      </div>

      <!-- ================= FILL IN THE BLANK ================= -->
      <div class="q-block q-fill d-none">
        <label>Correct Word / Phrase</label>
        <input type="text" name="questions[{{i}}][answer_text]" class="form-control">
      </div>

      <!-- ================= MATCH THE PAIRS ================= -->
      <div class="q-block q-match d-none">
        <!-- Draggable toggle will be inserted by JS -->
        <label>Match Pairs (Left → Right)</label>
        <div class="match-pairs">
          <!-- rows added by JS -->
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary btn-add-pair mt-2">+ Add Pair</button>
      </div>
    </div>
  </div>
</script>



  <script>

    const questionSummaryBox = document.getElementById('questionSummaryBox');
const qsTotal        = document.getElementById('qsTotal');
const qsMcq          = document.getElementById('qsMcq');
const qsFill         = document.getElementById('qsFill');
const qsShort        = document.getElementById('qsShort');
const qsMatchDrag    = document.getElementById('qsMatchDrag');
const qsMatchNoDrag  = document.getElementById('qsMatchNoDrag');

function resetSummary()
 { if (!questionSummaryBox) 
return; questionSummaryBox.style.display = 'none'; 
if (qsTotal) qsTotal.textContent = '0'; 
if (qsMcq) qsMcq.textContent = '0'; 
if (qsFill) qsFill.textContent = '0'; 
if (qsShort) qsShort.textContent = '0'; 
if (qsMatchDrag) qsMatchDrag.textContent = '0';
 if (qsMatchNoDrag) qsMatchNoDrag.textContent = '0'; 
} 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           
  /* === Refs === */
  const classSelect     = document.getElementById('class_id'),
        subjectSelect   = document.getElementById('subject_id'),
        topicSelect     = document.getElementById('topic_id'),
        qList           = document.getElementById('questionList'),
        tpl             = document.getElementById('tplQuestionBlock').innerHTML,
        btnAddBlank     = document.getElementById('btnAddBlank'),
        btnClearAll     = document.getElementById('btnClearAll'),
        aiResults       = document.getElementById('aiResults'),
        aiCards         = document.getElementById('aiCards'),
        mcqJsonInput    = document.getElementById('mcq_json'),
        btnParseJson    = document.getElementById('btnParseJson'),
        topicForm       = document.getElementById('topicForm'),
        t_class_id      = document.getElementById('t_class_id'),
        t_subject_id    = document.getElementById('t_subject_id'),
        t_topic_name    = document.getElementById('t_topic_name');
        topicSaveUrl = '<?= base_url('admin/question-bank/save-topic') ?>';

$('#topicModal').on('show.bs.modal', function () {
  if (!classSelect.value || !subjectSelect.value) {
    alert('Please select Class and Subject first.');
    $('#topicModal').modal('hide');
    return;
  }

  // copy selected class & subject into hidden inputs
  t_class_id.value   = classSelect.value;
  t_subject_id.value = subjectSelect.value;
  t_topic_name.value = '';
  setTimeout(() => t_topic_name.focus(), 150);
});

  let qIndex = 0;
  let parsedQuestions = [];

  /* === Utilities === */
 function toggleBlocks(card, type){
  // Hide ALL blocks first
  card.querySelectorAll('.q-block').forEach(b => b.classList.add('d-none'));

  const idx = card.dataset.qidx;

  // 👉 For MCQ / MCQ_MULTI / MATCH: just show the right block, no name changes
  if (type === 'mcq' || type === 'mcq_multi' || type === 'match') {
    const target = card.querySelector('.q-' + type);
    if (target) {
      target.classList.remove('d-none');
    }
    return;
  }

  // 👉 For TF / FILL / SHORT: we manage answer_text name
  const selector = `select[name="questions[${idx}][answer_text]"], input[name="questions[${idx}][answer_text]"]`;
  card.querySelectorAll(selector).forEach(el => {
    el.removeAttribute('name');
  });

  const target = card.querySelector('.q-' + type);
  if (target) {
    target.classList.remove('d-none');

    // Only the active block should have the answer_text name
    const ans = target.querySelector('select, input');
    if (ans) {
      ans.setAttribute('name', `questions[${idx}][answer_text]`);
    }
  }
}



  function renumber(){
    Array.from(qList.children).forEach((c, i) => {
      const s = c.querySelector('strong');
      if (s) s.textContent = 'Question #' + (i + 1);
    });
  }

  function renderTpl(html, map){
    return html.replace(/{{(\w+)}}/g, (_, k) => map[k] ?? '');
  }

  function syncIds(){
    const cls = classSelect.value,
          sub = subjectSelect.value,
          top = topicSelect.value;

    Array.from(qList.children).forEach(c => {
      const i = c.dataset.qidx;
      c.querySelector(`[name="questions[${i}][class_id]"]`).value   = cls;
      c.querySelector(`[name="questions[${i}][subject_id]"]`).value = sub;
      c.querySelector(`[name="questions[${i}][topic_id]"]`).value   = top;
    });
  }

  function normalizeMcq(i){
    if (i.question_type !== 'mcq') return i;
    const k = ['option_a','option_b','option_c','option_d'];
    const s = new Set();
    for (const x of k){
      let v = (i[x] || '').trim();
      if (!v || s.has(v.toLowerCase())) v = '—';
      s.add(v.toLowerCase());
      i[x] = v;
    }
    if (!['A','B','C','D'].includes(i.correct_option)){
      i.correct_option = 'A';
    }
    return i;
  }

  /* === Block builder === */
  function addMatchRow(container, i, rowIdx, leftVal = '', rightVal = '') {
    const row = document.createElement('div');
    row.className = 'form-row mb-1';
    row.innerHTML = `
      <div class="col">
        <input type="text"
               class="form-control form-control-sm"
               name="questions[${i}][match_pairs][${rowIdx}][left]"
               placeholder="Left">
      </div>
      <div class="col">
        <input type="text"
               class="form-control form-control-sm"
               name="questions[${i}][match_pairs][${rowIdx}][right]"
               placeholder="Right">
      </div>
    `;
    container.appendChild(row);

    const inputs = row.querySelectorAll('input');
    if (inputs[0]) inputs[0].value = leftVal || '';
    if (inputs[1]) inputs[1].value = rightVal || '';
  }

function updateMcqMode(card, type){
  const single = card.querySelector('.mcq-single-correct');
  const multi  = card.querySelector('.mcq-multi-correct');
  if (!single || !multi) return;

  if (type === 'mcq_multi') {
    single.classList.add('d-none');
    multi.classList.remove('d-none');
  } else {
    single.classList.remove('d-none');
    multi.classList.add('d-none');
  }
}




function addBlock(p = {}) {
  const i = qIndex++;
  const map = {
    i: i,
    n: qList.children.length + 1,
    class_id: classSelect.value,
    subject_id: subjectSelect.value,
    topic_id: topicSelect.value
  };

  const wrapper = document.createElement('div');
  wrapper.innerHTML = renderTpl(tpl, map);
  const card = wrapper.firstElementChild;
  qList.appendChild(card);

  console.log("=== CARD HTML DUMP FOR INDEX:", i, " ===");
  console.log(card.innerHTML);

  // 🔹 If this question is coming from the bank, keep its ID in a hidden field
  if (p.id || p.question_id) {
    const qid = p.id || p.question_id;

    const hid = document.createElement('input');
    hid.type  = 'hidden';
    hid.name  = `questions[${i}][id]`;  // <--- IMPORTANT
    hid.value = qid;

    card.querySelector('.card-body').appendChild(hid);
  }


  // --------------------------
  // Type selector (mcq, mcq_multi, tf, fill, short, match)
  // --------------------------
  const t = card.querySelector('.q-type');
  const initialType = p.question_type || 'mcq';
  t.value = initialType;
  toggleBlocks(card, initialType);
  updateMcqMode(card, initialType);

  // --------------------------
  // Add toggle for is_drag (only for match)
  // --------------------------
 let dragToggle = null;
if (initialType === 'match') {
  const infoDiv = card.querySelector('.card-header');
  if (infoDiv) {
    dragToggle = document.createElement('div');
    dragToggle.className = 'is-drag-toggle d-inline-block ml-2';
    dragToggle.innerHTML = `
      <label class="switch">
        <input type="checkbox" name="questions[${i}][is_drag]" value="1" checked>
        <span class="slider round"></span>
      </label>
      <small class="ml-1">Draggable</small>
    `;
    infoDiv.appendChild(dragToggle);
  }
}

  // --------------------------
  // Show/hide toggle on type change (only match shows)
  // --------------------------
  // make sure dragToggle is defined in the proper scope

t.onchange = () => {
  const newType = t.value;
  
  toggleBlocks(card, newType);
  updateMcqMode(card, newType);

  if (newType === 'match') {
    const mpContainer = card.querySelector('.q-match .match-pairs');
    
    if (mpContainer && mpContainer.children.length === 0) {
      addMatchRow(mpContainer, i, 0, '', '');
    }

    if (!dragToggle) {
      const infoDiv = card.querySelector('.card-header');
      dragToggle = document.createElement('div');
      dragToggle.className = 'is-drag-toggle d-inline-block ml-2';
      dragToggle.innerHTML = `
        <label class="switch">
          <input type="checkbox" name="questions[${i}][is_drag]" value="1">
          <span class="slider round"></span>
        </label>
        <small class="ml-1">Draggable</small>
      `;
      infoDiv.appendChild(dragToggle);
    }
    dragToggle.classList.remove('d-none');
  } else {
    if (dragToggle) {
      dragToggle.classList.add('d-none');
      const checkbox = dragToggle.querySelector('input[type="checkbox"]');
      if (checkbox) checkbox.checked = false;
    }
  }
};

  // --------------------------
  // Question text
  // --------------------------
  if (p.question) {
    const qText = card.querySelector('.q-text');
    if (qText) qText.value = p.question;
  }

  // --------------------------
  // Hidden IDs
  // --------------------------
  card.querySelector(`[name="questions[${i}][class_id]"]`).value = classSelect.value;
  card.querySelector(`[name="questions[${i}][subject_id]"]`).value = subjectSelect.value;
  card.querySelector(`[name="questions[${i}][topic_id]"]`).value = topicSelect.value;

  // --------------------------
  // MCQ options (A–D)
  // --------------------------



 // NEW – use classes so we don't depend on name matching/index details
const optMap = {
  option_a: '.js-opt-a',
  option_b: '.js-opt-b',
  option_c: '.js-opt-c',
  option_d: '.js-opt-d',
};

Object.keys(optMap).forEach(k => {
  const val = p[k];
  if (val === undefined || val === null || val === '') return;

  const selector = optMap[k];
  const inp = card.querySelector(selector);

 

  if (inp) {
    inp.value = val;
  }
});


  // --------------------------
  // Correct option (single MCQ)
  // --------------------------
  if (p.correct_option) {
    const co = card.querySelector(`[name="questions[${i}][correct_option]"]`);
    if (co) co.value = p.correct_option;
  }

  // --------------------------
  // Correct options (MCQ_MULTI)
  // --------------------------
  if (p.question_type === 'mcq_multi' && Array.isArray(p.correct_options)) {
    const letters = p.correct_options.map(v => v.toString().trim().toUpperCase());
    card.querySelectorAll('.mcq-multi-checkbox').forEach(cb => {
      const letter = (cb.getAttribute('data-letter') || cb.value || '').toUpperCase();
      cb.checked = letters.includes(letter);
    });
  }

  // --------------------------
  // Answer text for TF / FILL / SHORT
  // --------------------------
  if (p.answer_text && !['mcq', 'mcq_multi', 'match'].includes(p.question_type)) {
    const activeBlock = card.querySelector('.q-block:not(.d-none)');
    if (activeBlock) {
      const at = activeBlock.querySelector(`[name="questions[${i}][answer_text]"]`);
      if (at) at.value = p.answer_text;
    }
  }



  // --------------------------
  // MATCH PAIRS
  // --------------------------
  if (p.question_type === 'match') {
    const mpContainer = card.querySelector('.q-match .match-pairs');
    if (mpContainer) {
      mpContainer.innerHTML = '';
      const pairs = Array.isArray(p.match_pairs) ? p.match_pairs : [];
      if (pairs.length) {
        pairs.forEach((pair, idx) => addMatchRow(mpContainer, i, idx, pair.left || '', pair.right || ''));
      } else {
        addMatchRow(mpContainer, i, 0, '', '');
      }
    }
  }

topicForm.addEventListener('submit', function (e) {
  e.preventDefault();

  const name = t_topic_name.value.trim();
  if (!name) {
    alert('Please enter topic name.');
    return;
  }

  const fd = new FormData(topicForm); // already has class_id, subject_id, topic_name + CSRF

  fetch(topicSaveUrl, {
    method: 'POST',
    body: fd,
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
    .then(r => r.json())
    .then(res => {
      if (res.status !== 'ok') {
        alert(res.msg || 'Failed to save topic.');
        return;
      }

      // Append new topic to dropdown and select it
      const opt = new Option(res.topic_name, res.id, true, true);
      topicSelect.add(opt);
      topicSelect.value = res.id;

      // sync hidden ids inside all question blocks
      syncIds();

      // (optional) reload summary for new topic
      if (typeof loadSummary === 'function') {
        loadSummary();
      }

      // close modal
      $('#topicModal').modal('hide');
    })
    .catch(err => {
      console.error('Topic save error:', err);
      alert('Error while saving topic.');
    });
});


  // "+ Add Pair" button
  const addPairBtn = card.querySelector('.q-match .btn-add-pair');
  if (addPairBtn) {
    addPairBtn.addEventListener('click', () => {
      const mpContainer = card.querySelector('.q-match .match-pairs');
      const rowIdx = mpContainer.querySelectorAll('.form-row').length;
      addMatchRow(mpContainer, i, rowIdx, '', '');
    });
  }

  // --------------------------
  // Remove / move up / move down
  // --------------------------
  card.querySelector('.btn-remove').onclick = () => { card.remove(); renumber(); };
  card.querySelector('.btn-move-up').onclick = () => {
    if (card.previousElementSibling) { qList.insertBefore(card, card.previousElementSibling); renumber(); }
  };
  card.querySelector('.btn-move-down').onclick = () => {
    if (card.nextElementSibling) { qList.insertBefore(card.nextElementSibling, card); renumber(); }
  };

  renumber();
}


/* === Subject / Topic loading === */
classSelect.onchange = () => {
  resetSummary(); // clear because subject/topic will change

  const cid = classSelect.value;
  subjectSelect.innerHTML = '<option value="">-- Select Subject --</option>';
  topicSelect.innerHTML   = '<option value="">-- Select Topic --</option>';

  if (!cid) {
    return;
  }

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

      topicSelect.innerHTML = '<option value="">-- Select Topic --</option>';
      syncIds();
    })
    .catch(err => {
      console.error('Failed to load subjects', err);
      subjectSelect.innerHTML = '<option value="">-- Select Subject --</option>';
      topicSelect.innerHTML   = '<option value="">-- Select Topic --</option>';
    });
};

/* === Subject change === */
subjectSelect.onchange = () => {
  resetSummary(); // topic will reload

  const cid = classSelect.value;
  const sid = subjectSelect.value;

  topicSelect.innerHTML = '<option value="">Loading...</option>';

  if (!cid || !sid) {
    topicSelect.innerHTML = '<option value="">-- Select Topic --</option>';
    return;
  }

  fetch(
    '<?= base_url('admin/question-bank/topics') ?>'
    + '?class_id='   + encodeURIComponent(cid)
    + '&subject_id=' + encodeURIComponent(sid)
  )
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

      // user will choose topic → then we load summary
      topicSelect.value = '';
      syncIds();
    })
    .catch(err => {
      console.error('Failed to load topics', err);
      topicSelect.innerHTML = '<option value="">-- Select Topic --</option>';
    });
};

/* === Topic change === */
topicSelect.onchange = () => {
  syncIds();
  loadSummary();   // NOW we safely load and show the summary
};
  /* === Toolbar === */
  btnAddBlank.onclick = () => addBlock({ question_type: 'mcq' });
  btnClearAll.onclick = () => { qList.innerHTML = ''; };


  /* === JSON Parsing & Cards Rendering === */
  btnParseJson.onclick = () => {
    const raw = mcqJsonInput.value.trim();
    if (!raw) {
      alert('Please paste Questions JSON first.');
      return;
    }

    let parsed;
    try {
      parsed = JSON.parse(raw);
    } catch (e) {
      alert('Invalid JSON: ' + e.message);
      return;
    }

    // Accept:
    // - { "questions": [ ... ] }
    // - { "mcqs": [ ... ] } (old MCQ-only format)
    // - [ ... ] plain array of question objects
    let list = [];
    if (Array.isArray(parsed)) {
      list = parsed;
    } else if (parsed && Array.isArray(parsed.questions)) {
      list = parsed.questions;
    } else if (parsed && Array.isArray(parsed.mcqs)) {
      list = parsed.mcqs;
    } else {
      alert('JSON must be an array or contain "questions" or "mcqs" array.');
      return;
    }

    // Map each item into a structure our form understands
    parsedQuestions = list.map(item => {
      let type = (item.type || '').toString().toLowerCase().trim();

      // Normalize some aliases
      if (['mcq_multi', 'multi', 'multiple', 'multiple_choice_multi'].includes(type)) {
        type = 'mcq_multi';
      }

      // If no type provided, try to infer it
      if (!type) {
        if (Array.isArray(item.match_pairs)) {
          type = 'match';
        } else {
          const hasOptions = item.options || item.option_a || item.option_b || item.option_c || item.option_d;
          if (hasOptions) {
            type = 'mcq';
          } else if (
            typeof item.answer_text === 'string' &&
            ['true', 'false'].includes(item.answer_text.toLowerCase())
          ) {
            type = 'tf';
          } else {
            type = 'fill'; // default if nothing else matches
          }
        }
      }

      // Normalise MCQ-style options if present
      const opts = item.options || item.choices || {};

      // Raw "correct" value – can be string or array for mcq_multi
      const rawCorrect =
        item.correct_options ??
        item.correctAnswers ??
        item.correct_answers ??
        item.correct ??
        item.correct_option ??
        '';

      let correctOption  = '';
      let correctOptions = [];

      if (type === 'mcq_multi') {
        // Multi-answer
        if (Array.isArray(rawCorrect)) {
          correctOptions = rawCorrect
            .map(v => v.toString().trim().toUpperCase())
            .filter(Boolean);
        } else if (typeof rawCorrect === 'string') {
          correctOptions = rawCorrect
            .split(/[,; ]+/)
            .map(v => v.trim().toUpperCase())
            .filter(Boolean);
        }
        // Deduplicate
        correctOptions = Array.from(new Set(correctOptions));
      } else {
        // Single-answer
        correctOption = rawCorrect.toString().trim().toUpperCase();
      }



      let q = {
        question_type: type,                 // "mcq" | "mcq_multi" | "tf" | "fill" | "short" | "match" ...
        question: item.question || '',
        option_a: item.option_a || opts.A || opts.a || '',
        option_b: item.option_b || opts.B || opts.b || '',
        option_c: item.option_c || opts.C || opts.c || '',
        option_d: item.option_d || opts.D || opts.d || '',
        correct_option:  correctOption,
        correct_options: correctOptions,
        answer_text: item.answer_text || item.answer || '',
        match_pairs: []
      };

      // Per-type cleanup
      if (type === 'mcq') {
        q = normalizeMcq(q); // your existing helper for MCQs
        q.answer_text = '';  // ignore answer_text for mcq
      } else if (type === 'mcq_multi') {
        // Multi-answers: normalise options (remove duplicates / blanks) but ignore correct_option string
        q = normalizeMcq(q);
        q.correct_option = ''; // we will use correct_options[] instead
        q.answer_text = '';
      } else if (type === 'tf') {
        // True/False normalization
        let v = q.answer_text.toString().trim().toLowerCase();
        if (v === 'true' || v === 't' || v === '1') {
          q.answer_text = 'True';
        } else if (v === 'false' || v === 'f' || v === '0') {
          q.answer_text = 'False';
        } else {
          // fallback: respect original text but default if empty
          q.answer_text = q.answer_text || 'False';
        }
        q.option_a = q.option_b = q.option_c = q.option_d = '';
        q.correct_option = '';
        q.correct_options = [];
      } else if (type === 'fill' || type === 'short') {
        // Fill / short answers use answer_text only
        q.answer_text = q.answer_text || '';
        q.option_a = q.option_b = q.option_c = q.option_d = '';
        q.correct_option = '';
        q.correct_options = [];
      } else if (type === 'match') {
        // Match-the-column / match-the-pair questions
        const rawPairs = Array.isArray(item.match_pairs)
          ? item.match_pairs
          : (Array.isArray(item.pairs) ? item.pairs : []);

        q.match_pairs = rawPairs.map(p => ({
          left:  p.left  || p.L || p.l || p[0] || '',
          right: p.right || p.R || p.r || p[1] || ''
        }));

        q.option_a = q.option_b = q.option_c = q.option_d = '';
        q.correct_option  = '';
        q.correct_options = [];
        q.answer_text = ''; // not used here
      } else {
        // Any other type: keep answer_text, clear MCQ options
        q.option_a = q.option_b = q.option_c = q.option_d = '';
        q.correct_option  = '';
        q.correct_options = [];
      }

      return q;
    });

    // ===== Render preview cards with checkboxes =====
    aiCards.innerHTML = '';
    const sel = new Set();

    const bar = document.createElement('div');
    bar.className = 'w-100 mb-2';
    bar.innerHTML = `
      <button type="button" id="btnSelAll" class="btn btn-sm btn-outline-primary mr-2">Select All</button>
      <button type="button" id="btnUnselAll" class="btn btn-sm btn-outline-secondary mr-2">Unselect</button>
      <button type="button" id="btnLoadSel" class="btn btn-sm btn-success">Load Selected to Form</button>
    `;
    aiCards.appendChild(bar);

    parsedQuestions.forEach((q, idx) => {
      const card = document.createElement('div');
      card.className = 'border rounded p-2 mr-2 mb-2 position-relative';
      card.style.width = '280px';

      const chk = document.createElement('input');
      chk.type = 'checkbox';
      chk.className = 'position-absolute';
      chk.style.top = '6px';
      chk.style.right = '6px';

      card.onclick = e => {
        if (e.target === chk) return;
        chk.checked = !chk.checked;
        chk.dispatchEvent(new Event('change'));
      };

      chk.onchange = () => {
        if (chk.checked) {
          sel.add(idx);
          card.style.background = '#f3f8ff';
        } else {
          sel.delete(idx);
          card.style.background = '';
        }
      };

      const typeLabel = (q.question_type || 'mcq').toUpperCase();
      let html = `<strong>Q${idx + 1}.</strong> (${typeLabel})<br>${q.question || ''}<br>`;

      if (q.question_type === 'mcq' || q.question_type === 'mcq_multi') {
        html +=
          `A) ${q.option_a || ''}<br>` +
          `B) ${q.option_b || ''}<br>` +
          `C) ${q.option_c || ''}<br>` +
          `D) ${q.option_d || ''}<br>`;

        let correctLabel = '';
        if (q.question_type === 'mcq_multi' && Array.isArray(q.correct_options) && q.correct_options.length) {
          correctLabel = q.correct_options.join(', ');
        } else {
          correctLabel = q.correct_option || '';
        }
        html += `<small>Correct: ${correctLabel}</small>`;
      } else if (q.question_type === 'tf') {
        html += `<small>Answer: ${q.answer_text || ''}</small>`;
      } else if (q.question_type === 'fill' || q.question_type === 'short') {
        html += `<small>Answer: ${q.answer_text || ''}</small>`;
      } else if (q.question_type === 'match') {
        if (q.match_pairs && q.match_pairs.length) {
          html += '<small>';
          q.match_pairs.forEach(p => {
            html += `${p.left || ''} → ${p.right || ''}<br>`;
          });
          html += '</small>';
        } else {
          html += '<small>No pairs defined.</small>';
        }
      } else {
        html += `<small>Answer: ${q.answer_text || ''}</small>`;
      }

      const body = document.createElement('div');
      body.innerHTML = html;

      card.append(chk, body);
      aiCards.appendChild(card);
    });

    const btnSelAll   = document.getElementById('btnSelAll');
    const btnUnselAll = document.getElementById('btnUnselAll');
    const btnLoadSel  = document.getElementById('btnLoadSel');

    btnSelAll.onclick = () => {
      aiCards.querySelectorAll('input[type=checkbox]').forEach((c, i) => {
        c.checked = true;
        sel.add(i);
        c.dispatchEvent(new Event('change'));
      });
    };

    btnUnselAll.onclick = () => {
      aiCards.querySelectorAll('input[type=checkbox]').forEach((c, i) => {
        c.checked = false;
        sel.delete(i);
        c.dispatchEvent(new Event('change'));
      });
    };

    btnLoadSel.onclick = () => {
      if (!sel.size) {
        alert('Select at least one question card.');
        return;
      }
      const arr = parsedQuestions || [];
      Array.from(sel).forEach(i => addBlock(arr[i]));
      syncIds();
      aiResults.scrollIntoView({ behavior: 'smooth' });
    };

    aiResults.style.display = 'block';
  };


  /* === Sync IDs on change and submit === */
  

  document.querySelector('form[action$="question-bank/save"]').onsubmit = e => {
  syncIds();
  if (!classSelect.value || !subjectSelect.value || !topicSelect.value) {
    e.preventDefault();
    alert('Select class, subject and topic first');
    return false;
  }
  if (!qList.children.length) {
    e.preventDefault();
    alert('Add at least one question');
    return false;
  }
};

function loadSummary() {
  const cid = classSelect.value;
  const sid = subjectSelect.value;
  const tid = topicSelect.value;

  // No topic selected → hide summary & stop
  if (!cid || !sid || !tid) {
    resetSummary();
    return;
  }

  // If somehow box missing, just stop (prevents classList errors)
  if (!questionSummaryBox) {
    console.warn('questionSummaryBox element not found in DOM');
    return;
  }

  const url =
    '<?= base_url('admin/question-bank/summary') ?>'
    + '?class_id='   + encodeURIComponent(cid)
    + '&subject_id=' + encodeURIComponent(sid)
    + '&topic_id='   + encodeURIComponent(tid);

  fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
      console.log('Summary response:', data);

      // If backend returned an error flag, hide & exit
      if (!data || data.error) {
        resetSummary();
        return;
      }

      // Fill values (fallback 0 if missing)
      if (qsTotal)       qsTotal.textContent       = data.total        ?? 0;
      if (qsMcq)         qsMcq.textContent         = data.mcq          ?? 0;
      if (qsFill)        qsFill.textContent        = data.fill         ?? 0;
      if (qsShort)       qsShort.textContent       = data.short        ?? 0;
      if (qsMatchDrag)   qsMatchDrag.textContent   = data.match_drag   ?? 0;
      if (qsMatchNoDrag) qsMatchNoDrag.textContent = data.match_nodrag ?? 0;

      // Show the box safely (no classList on null anymore)
      questionSummaryBox.style.display = 'block';
    })
    .catch(err => {
      console.error('Failed to load summary', err);
      resetSummary();
    });
}


  </script>

  <?= $this->endSection() ?>
