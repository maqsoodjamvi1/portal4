<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'QB AI Generate',
    'icon' => 'fas fa-robot',
    'subtitle' => 'Generate questions with Gemini, review, then save to the question bank.',
    'actionsHtml' => '<div class="text-sm-right d-flex flex-wrap justify-content-sm-end" style="gap:.5rem;">'
        . '<a href="' . esc(site_url('admin/question-bank/overview'), 'attr') . '" class="btn btn-outline-secondary btn-sm"><i class="fas fa-sitemap"></i> Overview</a>'
        . '<a href="' . esc(site_url('admin/question-bank/form'), 'attr') . '" class="btn btn-outline-secondary btn-sm"><i class="fas fa-edit"></i> Add / Edit</a>'
        . '<a href="' . esc(site_url('admin/qb-topics'), 'attr') . '" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener"><i class="fas fa-tags"></i> Topics</a>'
        . '</div>',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Question bank', 'url' => site_url('admin/question-bank/overview')],
        ['label' => 'AI Generate', 'active' => true],
    ],
]) ?>

<section class="content">

  <div class="card sms-card card-primary">
    <div class="card-header">
      <h3 class="card-title mb-0">Class, subject &amp; topic</h3>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="form-group col-md-4">
          <label for="class_id">Class</label>
          <select id="class_id" class="form-control" required>
            <option value="">-- Select Class --</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?= (int) $c->class_id ?>"><?= esc($c->class_name) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group col-md-4">
          <label for="subject_id">Subject</label>
          <select id="subject_id" class="form-control" required>
            <option value="">-- Select Subject --</option>
          </select>
        </div>
        <div class="form-group col-md-4">
          <label for="topic_id">Topic</label>
          <div class="input-group">
            <select id="topic_id" class="form-control" required>
              <option value="">-- Select Topic --</option>
            </select>
            <button type="button" class="btn btn-outline-primary" id="btnAddTopic" title="Add topic">
                <i class="fas fa-plus"></i>
              </button>
          </div>
        </div>
      </div>

      <div id="subjectInfoWrap" class="mb-2" style="display:none;">
        <span class="text-muted small">Subject:</span>
        <strong id="subjectInfoLabel" class="small"></strong>
      </div>

      <div id="topicDescWrap" class="form-group mb-0" style="display:none;">
        <label for="topic_description">Topic description <span class="text-muted fw-normal">(editable — used for AI context)</span></label>
        <textarea id="topic_description" class="form-control" rows="3"
          placeholder="Describe what this topic covers: chapters, concepts, board syllabus…"></textarea>
        <div class="d-flex flex-wrap align-items-center mt-2" style="gap:.5rem;">
          <button type="button" class="btn btn-sm btn-outline-secondary" id="btnSaveTopicDesc">
            <i class="fas fa-save"></i> Save description
          </button>
          <span id="topicDescStatus" class="small text-muted"></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Add topic modal -->
  <div class="modal fade" id="qbAiTopicModal" tabindex="-1" role="dialog" aria-labelledby="qbAiTopicModalTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="qbAiTopicModalTitle">Add topic</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="new_topic_name">Topic name</label>
            <input type="text" id="new_topic_name" class="form-control" required placeholder="e.g. Quadratic equations">
          </div>
          <div class="form-group mb-0">
            <label for="new_topic_description">Description <span class="text-muted fw-normal">(optional)</span></label>
            <textarea id="new_topic_description" class="form-control" rows="3"
              placeholder="Syllabus scope, key concepts, chapter references…"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="btnSaveNewTopic">
            <i class="fas fa-plus"></i> Add topic
          </button>
        </div>
      </div>
    </div>
  </div>

  <div id="qbAiExistingSummary" class="card card-outline card-secondary mt-3" style="display:none;">
    <div class="card-header py-2">
      <h3 class="card-title mb-0"><i class="fas fa-database me-1"></i> Already in question bank</h3>
    </div>
    <div class="card-body py-3">
      <p id="qbAiSummaryEmpty" class="text-muted small mb-0" style="display:none;">No questions saved for this topic yet.</p>
      <div id="qbAiSummaryCounts" class="qb-ai-summary-row text-center"></div>
      <p id="qbAiSummaryMcqMulti" class="small text-muted mt-2 mb-0" style="display:none;"></p>
      <p class="mb-0 mt-2">
        <a id="qbAiProofReadLink" href="#" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener" style="display:none;">
          <i class="fas fa-eye"></i> Proof read existing questions
        </a>
      </p>
    </div>
  </div>

  <style>
    .qb-ai-sum-card {
      border: 1px solid #dee2e6;
      border-radius: .5rem;
      padding: .65rem .5rem;
      background: #fff;
      min-height: 4.5rem;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }
    .qb-ai-sum-val {
      font-size: 1.75rem;
      font-weight: 700;
      color: #2b2f3a;
      line-height: 1.1;
    }
    .qb-ai-sum-lbl {
      font-size: .75rem;
      color: #6c757d;
      margin-top: .2rem;
      line-height: 1.2;
    }
    .qb-ai-summary-row {
      display: flex;
      flex-wrap: wrap;
      gap: .5rem;
    }
    .qb-ai-sum-item {
      flex: 0 0 auto;
      min-width: 6.5rem;
    }
  </style>

  <div class="card card-outline card-info">
    <div class="card-header">
      <h3 class="card-title mb-0">Question mix</h3>
    </div>
    <div class="card-body">
      <p class="text-muted small">Enter how many of each type to generate (max 50 per type, 60 total per run).</p>
      <div class="row">
        <div class="form-group col-md-2 col-6">
          <label for="count_mcq">MCQ</label>
          <input type="number" id="count_mcq" class="form-control" min="0" max="50" value="5">
        </div>
        <div class="form-group col-md-2 col-6">
          <label for="count_tf">True / False</label>
          <input type="number" id="count_tf" class="form-control" min="0" max="50" value="0">
        </div>
        <div class="form-group col-md-2 col-6">
          <label for="count_fill">Fill in blanks</label>
          <input type="number" id="count_fill" class="form-control" min="0" max="50" value="0">
        </div>
        <div class="form-group col-md-2 col-6">
          <label for="count_short">Short answer</label>
          <input type="number" id="count_short" class="form-control" min="0" max="50" value="0">
        </div>
        <div class="form-group col-md-2 col-6">
          <label for="count_descriptive" title="3–5 line model answer; marked manually by teacher">Descriptive</label>
          <input type="number" id="count_descriptive" class="form-control" min="0" max="50" value="0">
        </div>
        <div class="form-group col-md-2 col-6">
          <label for="count_match">Match pairs</label>
          <input type="number" id="count_match" class="form-control" min="0" max="50" value="0">
        </div>
        <div class="form-group col-md-2 col-12 d-flex align-items-end">
          <p class="mb-2"><strong>Total:</strong> <span id="totalCountLabel">5</span></p>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-8">
          <label for="extra_instructions">Extra instructions (optional)</label>
          <textarea id="extra_instructions" class="form-control" rows="2"
            placeholder="e.g. Punjab board, Urdu medium, focus on chapter 3…"></textarea>
        </div>
        <div class="form-group col-md-4">
          <label for="difficulty">Default difficulty</label>
          <select id="difficulty" class="form-control">
            <option value="easy">Easy</option>
            <option value="normal" selected>Normal</option>
            <option value="hard">Hard</option>
          </select>
        </div>
      </div>

      <p class="small text-muted mb-2">
        <strong>Descriptive:</strong> question + model answer (3–5 sentences) stored as a guideline for students; teachers mark answers manually.
      </p>
      <p class="small text-muted mb-3">
        Uses <strong>Gemini 2.5 Pro</strong> (paid API via Google AI Studio). Usage is billed to your API account.
        Set <code>google.api_key</code> and optional <code>qb.ai.model</code> in <code>.env</code>.
      </p>

      <button type="button" id="btnGenerate" class="btn btn-primary">
        <i class="fas fa-magic" id="btnGenerateIcon"></i>
        <span class="qb-ai-btn-label">Generate questions</span>
      </button>
      <span id="generateStatus" class="ms-2"></span>

      <div id="qbAiGenerateLoader" class="qb-ai-generate-loader mt-3" style="display:none;" aria-live="polite" aria-busy="true">
        <div class="d-flex align-items-center mb-2">
          <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
            <span class="visually-hidden">Loading…</span>
          </div>
          <strong id="qbAiLoaderTitle">Generating questions with Gemini…</strong>
        </div>
        <p id="qbAiLoaderHint" class="small text-muted mb-2 mb-md-1">
          This may take 30 seconds to a few minutes depending on how many questions you requested. Please wait.
        </p>
        <div class="progress" style="height: 1.25rem;">
          <div
            id="qbAiProgressBar"
            class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
            role="progressbar"
            style="width: 100%;"
            aria-valuenow="100"
            aria-valuemin="0"
            aria-valuemax="100"
          >Processing…</div>
        </div>
      </div>
    </div>
  </div>

  <style>
    .qb-ai-generate-loader {
      padding: 1rem 1.15rem;
      border: 1px solid #b8daff;
      border-radius: .5rem;
      background: #f0f7ff;
    }
    .qb-ai-generate-loader.is-active {
      display: block !important;
    }
    #btnGenerate.is-loading {
      pointer-events: none;
      opacity: 0.85;
    }
  </style>

  <div id="previewWrap" class="card card-outline card-success" style="display:none;">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
      <h3 class="card-title mb-0">Preview &amp; edit</h3>
      <span id="previewStats" class="text-muted small">0 generated · 0 selected</span>
    </div>
    <div class="card-body">
      <div class="mb-3">
        <button type="button" id="btnSelectAll" class="btn btn-sm btn-outline-primary me-1">Select all</button>
        <button type="button" id="btnUnselectAll" class="btn btn-sm btn-outline-secondary me-2">Unselect all</button>
        <button type="button" id="btnSaveSelected" class="btn btn-sm btn-success" disabled>
          Save selected to question bank
        </button>
      </div>
      <div id="previewList"></div>
    </div>
  </div>

</section>

<script>
/* Same AJAX URLs as question_bank_form.php (base_url, not site_url) */
window.QB_AI_CONFIG = {
  csrfName: <?= json_encode(csrf_token()) ?>,
  csrfHash: <?= json_encode(csrf_hash()) ?>,
  subjectsUrl: <?= json_encode(base_url('admin/question-bank/subjects')) ?>,
  topicsUrl: <?= json_encode(base_url('admin/question-bank/topics')) ?>,
  generateUrl: <?= json_encode(base_url('admin/question-bank-ai/generate')) ?>,
  saveTopicUrl: <?= json_encode(base_url('admin/question-bank-ai/save-topic')) ?>,
  updateTopicUrl: <?= json_encode(base_url('admin/question-bank-ai/update-topic')) ?>,
  saveUrl: <?= json_encode(base_url('admin/question-bank/save-ajax')) ?>,
  summaryUrl: <?= json_encode(base_url('admin/question-bank/summary')) ?>,
  proofReadUrl: <?= json_encode(base_url('admin/question-bank/proof-read')) ?>,
};

(function () {
  const classSelect = document.getElementById('class_id');
  const subjectSelect = document.getElementById('subject_id');
  const topicSelect = document.getElementById('topic_id');
  const topicDescWrap = document.getElementById('topicDescWrap');
  const topicDescInput = document.getElementById('topic_description');
  const topicDescStatus = document.getElementById('topicDescStatus');
  const subjectInfoWrap = document.getElementById('subjectInfoWrap');
  const subjectInfoLabel = document.getElementById('subjectInfoLabel');
  const btnAddTopic = document.getElementById('btnAddTopic');
  const btnSaveTopicDesc = document.getElementById('btnSaveTopicDesc');
  const btnSaveNewTopic = document.getElementById('btnSaveNewTopic');

  const summaryBox = document.getElementById('qbAiExistingSummary');
  const summaryCounts = document.getElementById('qbAiSummaryCounts');
  const summaryEmpty = document.getElementById('qbAiSummaryEmpty');
  const summaryMcqMulti = document.getElementById('qbAiSummaryMcqMulti');
  const proofReadLink = document.getElementById('qbAiProofReadLink');

  function resetQbAiSummary() {
    if (summaryBox) {
      summaryBox.style.display = 'none';
    }
    if (summaryCounts) {
      summaryCounts.innerHTML = '';
    }
  }

  function updateSubjectInfo() {
    if (!subjectInfoWrap || !subjectInfoLabel) {
      return;
    }
    const sid = subjectSelect.value;
    if (!sid) {
      subjectInfoWrap.style.display = 'none';
      subjectInfoLabel.textContent = '';
      return;
    }
    const opt = subjectSelect.options[subjectSelect.selectedIndex];
    subjectInfoLabel.textContent = opt ? (opt.textContent || '') : '';
    subjectInfoWrap.style.display = 'block';
  }

  function resetTopicDescription() {
    if (topicDescWrap) {
      topicDescWrap.style.display = 'none';
    }
    if (topicDescInput) {
      topicDescInput.value = '';
    }
    if (topicDescStatus) {
      topicDescStatus.textContent = '';
      topicDescStatus.className = 'small text-muted';
    }
  }

  function showTopicDescription() {
    const tid = topicSelect.value;
    if (!tid || !topicDescWrap || !topicDescInput) {
      resetTopicDescription();
      return;
    }
    const opt = topicSelect.options[topicSelect.selectedIndex];
    topicDescInput.value = opt && opt.dataset.description ? opt.dataset.description : '';
    topicDescWrap.style.display = 'block';
    if (topicDescStatus) {
      topicDescStatus.textContent = '';
      topicDescStatus.className = 'small text-muted';
    }
  }

  function loadTopicsForSubject(selectTopicId) {
    const cid = classSelect.value;
    const sid = subjectSelect.value;
    topicSelect.innerHTML = '<option value="">Loading...</option>';
    resetQbAiSummary();
    resetTopicDescription();

    if (!cid || !sid) {
      topicSelect.innerHTML = '<option value="">-- Select Topic --</option>';
      return Promise.resolve();
    }

    return fetch(
      window.QB_AI_CONFIG.topicsUrl
        + '?class_id=' + encodeURIComponent(cid)
        + '&subject_id=' + encodeURIComponent(sid),
      { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' }
    )
      .then(function (r) { return r.json(); })
      .then(function (d) {
        const topics = Array.isArray(d) ? d : (d.topics || []);
        topicSelect.innerHTML = '<option value="">-- Select Topic --</option>';
        topics.forEach(function (t) {
          const o = document.createElement('option');
          o.value = t.id;
          o.textContent = t.topic_name || ('Topic ' + t.id);
          if (t.description) {
            o.dataset.description = t.description;
          }
          topicSelect.appendChild(o);
        });
        if (selectTopicId) {
          const match = topicSelect.querySelector('option[value="' + String(selectTopicId) + '"]');
          if (match) {
            topicSelect.value = String(selectTopicId);
            showTopicDescription();
            loadQbAiSummary();
          }
        }
      })
      .catch(function (err) {
        console.error('QB AI: load topics failed', err);
        topicSelect.innerHTML = '<option value="">-- Select Topic --</option>';
      });
  }

  function renderQbAiSummaryCards(items) {
    if (!summaryCounts) {
      return;
    }
    summaryCounts.innerHTML = '';
    items.forEach(function (item) {
      const wrap = document.createElement('div');
      wrap.className = 'qb-ai-sum-item';
      wrap.innerHTML =
        '<div class="qb-ai-sum-card">' +
        '<div class="qb-ai-sum-val">' + item.value + '</div>' +
        '<div class="qb-ai-sum-lbl">' + item.label + '</div>' +
        '</div>';
      summaryCounts.appendChild(wrap);
    });
  }

  function loadQbAiSummary() {
    const cid = classSelect.value;
    const sid = subjectSelect.value;
    const tid = topicSelect.value;

    if (!cid || !sid || !tid) {
      resetQbAiSummary();
      return;
    }

    fetch(
      window.QB_AI_CONFIG.summaryUrl
        + '?class_id=' + encodeURIComponent(cid)
        + '&subject_id=' + encodeURIComponent(sid)
        + '&topic_id=' + encodeURIComponent(tid),
      { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' }
    )
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || data.error) {
          resetQbAiSummary();
          return;
        }

        const total = parseInt(data.total, 10) || 0;
        const mcq = parseInt(data.mcq, 10) || 0;
        const mcqMulti = parseInt(data.mcq_multi, 10) || 0;
        const tf = parseInt(data.tf, 10) || 0;
        const fill = parseInt(data.fill, 10) || 0;
        const shortN = parseInt(data.short, 10) || 0;
        const descriptive = parseInt(data.descriptive, 10) || 0;
        let matchTotal = parseInt(data.match, 10) || 0;
        if (matchTotal === 0) {
          matchTotal = (parseInt(data.match_drag, 10) || 0) + (parseInt(data.match_nodrag, 10) || 0);
        }

        const mcqTotal = mcq + mcqMulti;
        const items = [];
        if (total > 0) {
          items.push({ label: 'Total', value: total });
        }
        if (mcqTotal > 0) {
          items.push({ label: 'MCQ', value: mcqTotal });
        }
        if (tf > 0) {
          items.push({ label: 'True / False', value: tf });
        }
        if (fill > 0) {
          items.push({ label: 'Fill blanks', value: fill });
        }
        if (shortN > 0) {
          items.push({ label: 'Short', value: shortN });
        }
        if (descriptive > 0) {
          items.push({ label: 'Descriptive', value: descriptive });
        }
        if (matchTotal > 0) {
          items.push({ label: 'Match', value: matchTotal });
        }
        renderQbAiSummaryCards(items);

        if (summaryMcqMulti) {
          if (mcqMulti > 0) {
            summaryMcqMulti.style.display = 'block';
            summaryMcqMulti.textContent =
              'Includes ' + mcqMulti + ' multiple-correct MCQ' + (mcqMulti === 1 ? '' : 's') + ' (shown in MCQ total).';
          } else {
            summaryMcqMulti.style.display = 'none';
            summaryMcqMulti.textContent = '';
          }
        }

        if (summaryBox) {
          summaryBox.style.display = 'block';
        }
        if (summaryCounts) {
          summaryCounts.style.display = total > 0 ? '' : 'none';
        }
        if (summaryEmpty) {
          summaryEmpty.style.display = total > 0 ? 'none' : 'block';
        }
        if (proofReadLink && total > 0) {
          proofReadLink.style.display = 'inline-block';
          proofReadLink.href =
            window.QB_AI_CONFIG.proofReadUrl
            + '?class_id=' + encodeURIComponent(cid)
            + '&subject_id=' + encodeURIComponent(sid)
            + '&topic_id=' + encodeURIComponent(tid);
        } else if (proofReadLink) {
          proofReadLink.style.display = 'none';
        }
      })
      .catch(function (err) {
        console.error('QB AI: load summary failed', err);
        resetQbAiSummary();
      });
  }

  if (!classSelect || !subjectSelect || !topicSelect) {
    return;
  }

  classSelect.addEventListener('change', function () {
    const cid = classSelect.value;
    subjectSelect.innerHTML = '<option value="">-- Select Subject --</option>';
    topicSelect.innerHTML = '<option value="">-- Select Topic --</option>';
    resetQbAiSummary();
    resetTopicDescription();
    if (subjectInfoWrap) {
      subjectInfoWrap.style.display = 'none';
    }

    if (!cid) {
      return;
    }

    fetch(window.QB_AI_CONFIG.subjectsUrl + '?class_id=' + encodeURIComponent(cid), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
    })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        const subjects = Array.isArray(d) ? d : (d.subjects || []);
        subjectSelect.innerHTML = '<option value="">-- Select Subject --</option>';
        subjects.forEach(function (s) {
          const o = document.createElement('option');
          o.value = s.subject_id;
          o.textContent = s.subject_name || s.subject_short_name || ('Subject ' + s.subject_id);
          subjectSelect.appendChild(o);
        });
        topicSelect.innerHTML = '<option value="">-- Select Topic --</option>';
        resetQbAiSummary();
      })
      .catch(function (err) {
        console.error('QB AI: load subjects failed', err);
        alert('Failed to load subjects. Please try again.');
      });
  });

  subjectSelect.addEventListener('change', function () {
    updateSubjectInfo();
    loadTopicsForSubject(null);
  });

  topicSelect.addEventListener('change', function () {
    showTopicDescription();
    loadQbAiSummary();
  });

  if (btnAddTopic) {
    btnAddTopic.addEventListener('click', function () {
      if (!classSelect.value || !subjectSelect.value) {
        alert('Please select class and subject first.');
        return;
      }
      document.getElementById('new_topic_name').value = '';
      document.getElementById('new_topic_description').value = '';
      if (typeof $ !== 'undefined' && $('#qbAiTopicModal').modal) {
        $('#qbAiTopicModal').modal('show');
        setTimeout(function () {
          document.getElementById('new_topic_name').focus();
        }, 200);
      }
    });
  }

  if (btnSaveNewTopic) {
    btnSaveNewTopic.addEventListener('click', function () {
      const name = (document.getElementById('new_topic_name').value || '').trim();
      const desc = (document.getElementById('new_topic_description').value || '').trim();
      if (!name) {
        alert('Enter a topic name.');
        return;
      }
      btnSaveNewTopic.disabled = true;
      const body = new URLSearchParams();
      body.append(window.QB_AI_CONFIG.csrfName, window.QB_AI_CONFIG.csrfHash);
      body.append('class_id', classSelect.value);
      body.append('subject_id', subjectSelect.value);
      body.append('topic_name', name);
      body.append('description', desc);

      fetch(window.QB_AI_CONFIG.saveTopicUrl, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        credentials: 'same-origin',
        body: body.toString(),
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          btnSaveNewTopic.disabled = false;
          if (!data || data.status !== 'ok') {
            alert((data && data.message) || 'Could not add topic.');
            return;
          }
          if (typeof $ !== 'undefined' && $('#qbAiTopicModal').modal) {
            $('#qbAiTopicModal').modal('hide');
          }
          loadTopicsForSubject(data.id).then(function () {
            if (topicDescInput && desc) {
              topicDescInput.value = desc;
            }
          });
        })
        .catch(function () {
          btnSaveNewTopic.disabled = false;
          alert('Request failed. Please try again.');
        });
    });
  }

  if (btnSaveTopicDesc) {
    btnSaveTopicDesc.addEventListener('click', function () {
      const tid = topicSelect.value;
      if (!tid) {
        alert('Select a topic first.');
        return;
      }
      const desc = topicDescInput ? topicDescInput.value.trim() : '';
      btnSaveTopicDesc.disabled = true;
      if (topicDescStatus) {
        topicDescStatus.textContent = 'Saving…';
        topicDescStatus.className = 'small text-muted';
      }

      const body = new URLSearchParams();
      body.append(window.QB_AI_CONFIG.csrfName, window.QB_AI_CONFIG.csrfHash);
      body.append('topic_id', tid);
      body.append('description', desc);

      fetch(window.QB_AI_CONFIG.updateTopicUrl, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        credentials: 'same-origin',
        body: body.toString(),
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          btnSaveTopicDesc.disabled = false;
          if (!data || data.status !== 'ok') {
            if (topicDescStatus) {
              topicDescStatus.textContent = (data && data.message) || 'Save failed.';
              topicDescStatus.className = 'small text-danger';
            }
            return;
          }
          const opt = topicSelect.options[topicSelect.selectedIndex];
          if (opt) {
            if (desc) {
              opt.dataset.description = desc;
            } else {
              delete opt.dataset.description;
            }
          }
          if (topicDescStatus) {
            topicDescStatus.textContent = 'Saved.';
            topicDescStatus.className = 'small text-success';
          }
        })
        .catch(function () {
          btnSaveTopicDesc.disabled = false;
          if (topicDescStatus) {
            topicDescStatus.textContent = 'Save failed.';
            topicDescStatus.className = 'small text-danger';
          }
        });
    });
  }

  window.loadQbAiSummary = loadQbAiSummary;
  window.getQbAiTopicDescription = function () {
    return topicDescInput ? topicDescInput.value.trim() : '';
  };
})();
</script>
<script src="<?= base_url('assets/js/question_bank_ai_generate.js') ?>?v=7"></script>

<?= $this->endSection() ?>
