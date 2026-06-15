(function () {
  'use strict';

  const cfg = window.QB_AI_CONFIG || {};
  const classSelect = document.getElementById('class_id');
  const subjectSelect = document.getElementById('subject_id');
  const topicSelect = document.getElementById('topic_id');
  // Class → subject → topic loading is inline in question_bank_ai_generate.php (same as QB form).
  const previewList = document.getElementById('previewList');
  const previewWrap = document.getElementById('previewWrap');
  const previewStats = document.getElementById('previewStats');
  const btnGenerate = document.getElementById('btnGenerate');
  const btnGenerateIcon = document.getElementById('btnGenerateIcon');
  const btnSaveSelected = document.getElementById('btnSaveSelected');
  const generateStatus = document.getElementById('generateStatus');
  const generateLoader = document.getElementById('qbAiGenerateLoader');
  const generateLoaderTitle = document.getElementById('qbAiLoaderTitle');
  const generateLoaderHint = document.getElementById('qbAiLoaderHint');
  const countInputs = {
    mcq: document.getElementById('count_mcq'),
    tf: document.getElementById('count_tf'),
    fill: document.getElementById('count_fill'),
    short: document.getElementById('count_short'),
    descriptive: document.getElementById('count_descriptive'),
    match: document.getElementById('count_match'),
  };

  let previewQuestions = [];
  let selectedSet = new Set();

  function totalCount() {
    return Object.keys(countInputs).reduce((sum, k) => sum + (parseInt(countInputs[k].value, 10) || 0), 0);
  }

  function updateTotalLabel() {
    const el = document.getElementById('totalCountLabel');
    if (el) {
      el.textContent = String(totalCount());
    }
  }

  Object.values(countInputs).forEach((inp) => {
    if (inp) {
      inp.addEventListener('input', updateTotalLabel);
    }
  });
  updateTotalLabel();

  function taxonomyOk() {
    return classSelect.value && subjectSelect.value && topicSelect.value;
  }

  function typeLabel(type) {
    const map = {
      mcq: 'MCQ',
      tf: 'True / False',
      fill: 'Fill',
      short: 'Short',
      descriptive: 'Descriptive',
      match: 'Match',
    };
    return map[type] || (type || '').toUpperCase();
  }

  function escapeHtml(s) {
    const d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }

  function syncQuestionFromCard(card, idx) {
    const q = previewQuestions[idx];
    if (!q) {
      return;
    }
    const type = q.question_type;
    const qText = card.querySelector('.js-q-text');
    if (qText) {
      q.question = qText.value;
    }
    if (type === 'mcq') {
      ['a', 'b', 'c', 'd'].forEach((letter) => {
        const inp = card.querySelector('.js-opt-' + letter);
        if (inp) {
          q['option_' + letter] = inp.value;
        }
      });
      const cor = card.querySelector('.js-correct');
      if (cor) {
        q.correct_option = cor.value;
      }
    } else if (type === 'tf') {
      const ans = card.querySelector('.js-answer');
      if (ans) {
        q.answer_text = ans.value;
      }
    } else if (type === 'fill' || type === 'short' || type === 'descriptive') {
      const ans = card.querySelector('.js-answer');
      if (ans) {
        q.answer_text = ans.value;
      }
    } else if (type === 'match') {
      const rows = card.querySelectorAll('.js-match-row');
      const pairs = [];
      rows.forEach((row) => {
        const left = row.querySelector('.js-match-left');
        const right = row.querySelector('.js-match-right');
        if (left && right) {
          pairs.push({ left: left.value, right: right.value });
        }
      });
      q.match_pairs = pairs;
    }
  }

  function buildCardHtml(q, idx) {
    const type = q.question_type || 'mcq';
    let body = '';

    body += '<div class="form-group mb-2">';
    body += '<label class="small mb-0">Question</label>';
    body +=
      '<textarea class="form-control form-control-sm js-q-text" rows="2">' +
      escapeHtml(q.question || '') +
      '</textarea></div>';

    if (type === 'mcq') {
      ['A', 'B', 'C', 'D'].forEach((letter) => {
        const key = 'option_' + letter.toLowerCase();
        body += '<div class="form-group mb-1">';
        body += '<label class="small mb-0">Option ' + letter + '</label>';
        body +=
          '<input type="text" class="form-control form-control-sm js-opt-' +
          letter.toLowerCase() +
          '" value="' +
          escapeHtml(q[key] || '') +
          '"></div>';
      });
      const cor = (q.correct_option || 'A').toUpperCase();
      body += '<div class="form-group mb-1"><label class="small mb-0">Correct</label><select class="form-control form-control-sm js-correct">';
      ['A', 'B', 'C', 'D'].forEach((letter) => {
        body +=
          '<option value="' +
          letter +
          '"' +
          (cor === letter ? ' selected' : '') +
          '>' +
          letter +
          '</option>';
      });
      body += '</select></div>';
    } else if (type === 'tf') {
      const val = q.answer_text === 'True' ? 'True' : 'False';
      body += '<div class="form-group mb-1"><label class="small mb-0">Answer</label><select class="form-control form-control-sm js-answer">';
      body += '<option value="True"' + (val === 'True' ? ' selected' : '') + '>True</option>';
      body += '<option value="False"' + (val === 'False' ? ' selected' : '') + '>False</option>';
      body += '</select></div>';
    } else if (type === 'fill' || type === 'short') {
      body +=
        '<div class="form-group mb-1"><label class="small mb-0">Answer</label><input type="text" class="form-control form-control-sm js-answer" value="' +
        escapeHtml(q.answer_text || '') +
        '"></div>';
    } else if (type === 'descriptive') {
      body +=
        '<div class="form-group mb-1"><label class="small mb-0">Model answer (guideline, 3–5 sentences)</label>' +
        '<textarea class="form-control form-control-sm js-answer" rows="5">' +
        escapeHtml(q.answer_text || '') +
        '</textarea></div>';
    } else if (type === 'match') {
      body += '<div class="js-match-pairs">';
      (q.match_pairs || []).forEach((p, pi) => {
        body += '<div class="form-row mb-1 js-match-row">';
        body +=
          '<div class="col"><input type="text" class="form-control form-control-sm js-match-left" placeholder="Left" value="' +
          escapeHtml(p.left || '') +
          '"></div>';
        body +=
          '<div class="col"><input type="text" class="form-control form-control-sm js-match-right" placeholder="Right" value="' +
          escapeHtml(p.right || '') +
          '"></div></div>';
      });
      body += '</div>';
      body +=
        '<button type="button" class="btn btn-xs btn-outline-secondary js-add-pair mt-1">+ Pair</button>';
    }

    return (
      '<div class="card qb-ai-preview-card mb-3" data-idx="' +
      idx +
      '">' +
      '<div class="card-header py-2 d-flex justify-content-between align-items-center">' +
      '<div><input type="checkbox" class="js-select mr-2" ' +
      (selectedSet.has(idx) ? 'checked' : '') +
      '> <span class="badge badge-info">' +
      escapeHtml(typeLabel(type)) +
      '</span> <span class="text-muted small">#' +
      (idx + 1) +
      '</span></div>' +
      '<button type="button" class="btn btn-sm btn-outline-danger js-delete">Delete</button>' +
      '</div><div class="card-body py-2">' +
      body +
      '</div></div>'
    );
  }

  function updatePreviewStats() {
    const n = previewQuestions.length;
    const sel = selectedSet.size;
    previewStats.textContent = n + ' generated · ' + sel + ' selected';
    btnSaveSelected.disabled = sel === 0;
  }

  function renderPreview() {
    previewList.innerHTML = '';
    previewQuestions.forEach((q, idx) => {
      previewList.insertAdjacentHTML('beforeend', buildCardHtml(q, idx));
    });

    previewWrap.style.display = previewQuestions.length ? 'block' : 'none';

    previewList.querySelectorAll('.qb-ai-preview-card').forEach((card) => {
      const idx = parseInt(card.dataset.idx, 10);
      const chk = card.querySelector('.js-select');
      chk.addEventListener('change', () => {
        if (chk.checked) {
          selectedSet.add(idx);
        } else {
          selectedSet.delete(idx);
        }
        updatePreviewStats();
      });
      card.querySelectorAll('input, textarea, select').forEach((el) => {
        el.addEventListener('change', () => syncQuestionFromCard(card, idx));
        el.addEventListener('input', () => syncQuestionFromCard(card, idx));
      });
      card.querySelector('.js-delete').addEventListener('click', () => {
        previewQuestions.splice(idx, 1);
        selectedSet.clear();
        renderPreview();
      });
      const addPair = card.querySelector('.js-add-pair');
      if (addPair) {
        addPair.addEventListener('click', () => {
          const container = card.querySelector('.js-match-pairs');
          const row = document.createElement('div');
          row.className = 'form-row mb-1 js-match-row';
          row.innerHTML =
            '<div class="col"><input type="text" class="form-control form-control-sm js-match-left" placeholder="Left"></div>' +
            '<div class="col"><input type="text" class="form-control form-control-sm js-match-right" placeholder="Right"></div>';
          container.appendChild(row);
          syncQuestionFromCard(card, idx);
        });
      }
    });

    updatePreviewStats();
  }

  document.getElementById('btnSelectAll')?.addEventListener('click', () => {
    selectedSet = new Set(previewQuestions.map((_, i) => i));
    previewList.querySelectorAll('.js-select').forEach((c) => {
      c.checked = true;
    });
    updatePreviewStats();
  });

  document.getElementById('btnUnselectAll')?.addEventListener('click', () => {
    selectedSet.clear();
    previewList.querySelectorAll('.js-select').forEach((c) => {
      c.checked = false;
    });
    updatePreviewStats();
  });

  function setGenerateLoading(active) {
    const n = totalCount();
    if (btnGenerate) {
      btnGenerate.disabled = active;
      btnGenerate.classList.toggle('is-loading', active);
    }
    if (btnGenerateIcon) {
      btnGenerateIcon.classList.toggle('fa-magic', !active);
      btnGenerateIcon.classList.toggle('fa-spinner', active);
      btnGenerateIcon.classList.toggle('fa-spin', active);
    }
    if (generateLoader) {
      generateLoader.style.display = active ? 'block' : 'none';
      generateLoader.setAttribute('aria-busy', active ? 'true' : 'false');
      if (active) {
        generateLoader.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    }
    if (generateLoaderTitle && active) {
      generateLoaderTitle.textContent =
        'Generating ' + n + ' question' + (n === 1 ? '' : 's') + ' with Gemini…';
    }
    if (generateLoaderHint && active) {
      generateLoaderHint.textContent =
        n > 15
          ? 'Large batches are processed in multiple steps. This may take 1–3 minutes. Please do not close this page.'
          : 'This usually takes 20–90 seconds. Please do not close this page.';
    }
    Object.values(countInputs).forEach((inp) => {
      if (inp) {
        inp.disabled = active;
      }
    });
    const extra = document.getElementById('extra_instructions');
    const diff = document.getElementById('difficulty');
    if (extra) {
      extra.disabled = active;
    }
    if (diff) {
      diff.disabled = active;
    }
    if (classSelect) {
      classSelect.disabled = active;
    }
    if (subjectSelect) {
      subjectSelect.disabled = active;
    }
    if (topicSelect) {
      topicSelect.disabled = active;
    }
    if (!active) {
      generateStatus.textContent = '';
    }
  }

  btnGenerate.addEventListener('click', async () => {
    if (!taxonomyOk()) {
      alert('Please select class, subject, and topic.');
      return;
    }
    if (totalCount() <= 0) {
      alert('Enter at least one question count.');
      return;
    }
    if (totalCount() > 60) {
      alert('Maximum 60 questions per generation.');
      return;
    }

    setGenerateLoading(true);
    generateStatus.textContent = '';
    generateStatus.className = 'text-muted ml-2';

    if (previewWrap) {
      previewWrap.style.display = 'none';
    }

    const body = new URLSearchParams();
    body.append(cfg.csrfName, cfg.csrfHash);
    body.append('class_id', classSelect.value);
    body.append('subject_id', subjectSelect.value);
    body.append('topic_id', topicSelect.value);
    body.append('extra_instructions', document.getElementById('extra_instructions').value || '');
    body.append('difficulty', document.getElementById('difficulty').value || 'normal');
    Object.keys(countInputs).forEach((k) => {
      body.append('count_' + k, countInputs[k].value || '0');
    });

    try {
      const res = await fetch(cfg.generateUrl, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: body.toString(),
      });
      const data = await res.json();
      setGenerateLoading(false);

      if (data.status !== 'ok' || !data.questions || !data.questions.length) {
        const msg = data.message || 'Generation failed.';
        const errDetail = (data.errors || []).join('\n');
        alert(errDetail ? msg + '\n\n' + errDetail : msg);
        return;
      }

      previewQuestions = data.questions;
      selectedSet = new Set(previewQuestions.map((_, i) => i));
      renderPreview();
      previewList.querySelectorAll('.js-select').forEach((c) => {
        c.checked = true;
      });

      const modelInfo = [data.provider, data.model].filter(Boolean).join(' / ');
      generateStatus.textContent = 'Generated ' + data.questions.length + ' question(s)' + (modelInfo ? ' (' + modelInfo + ')' : '') + '.';
      generateStatus.className = 'text-success ml-2';
      if (data.errors && data.errors.length) {
        console.warn('QB AI warnings', data.errors);
      }
    } catch (e) {
      setGenerateLoading(false);
      alert('Could not reach the server. Check your connection.');
    }
  });

  btnSaveSelected.addEventListener('click', async () => {
    if (!taxonomyOk()) {
      alert('Class, subject, and topic are required.');
      return;
    }
    if (selectedSet.size === 0) {
      alert('Select at least one question to save.');
      return;
    }

    previewList.querySelectorAll('.qb-ai-preview-card').forEach((card) => {
      syncQuestionFromCard(card, parseInt(card.dataset.idx, 10));
    });

    const indices = Array.from(selectedSet).sort((a, b) => a - b);
    const fd = new FormData();
    fd.append(cfg.csrfName, cfg.csrfHash);

    indices.forEach((origIdx, i) => {
      const q = previewQuestions[origIdx];
      if (!q) {
        return;
      }
      const prefix = 'questions[' + i + ']';
      fd.append(prefix + '[class_id]', classSelect.value);
      fd.append(prefix + '[subject_id]', subjectSelect.value);
      fd.append(prefix + '[topic_id]', topicSelect.value);
      fd.append(prefix + '[question_type]', q.question_type || 'mcq');
      fd.append(prefix + '[question]', q.question || '');
      fd.append(prefix + '[question_media]', 'text');
      fd.append(prefix + '[difficulty]', q.difficulty || document.getElementById('difficulty').value || 'normal');
      if (q.question_type === 'mcq') {
        fd.append(prefix + '[option_a]', q.option_a || '');
        fd.append(prefix + '[option_b]', q.option_b || '');
        fd.append(prefix + '[option_c]', q.option_c || '');
        fd.append(prefix + '[option_d]', q.option_d || '');
        fd.append(prefix + '[correct_option]', q.correct_option || 'A');
      } else if (
        q.question_type === 'tf' ||
        q.question_type === 'fill' ||
        q.question_type === 'short' ||
        q.question_type === 'descriptive'
      ) {
        fd.append(prefix + '[answer_text]', q.answer_text || '');
      } else if (q.question_type === 'match' && Array.isArray(q.match_pairs)) {
        q.match_pairs.forEach((p, pi) => {
          fd.append(prefix + '[match_pairs][' + pi + '][left]', p.left || '');
          fd.append(prefix + '[match_pairs][' + pi + '][right]', p.right || '');
        });
        fd.append(prefix + '[is_drag]', '0');
      }
    });

    btnSaveSelected.disabled = true;
    btnSaveSelected.textContent = 'Saving…';

    try {
      const res = await fetch(cfg.saveUrl, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd,
      });
      const data = await res.json();
      btnSaveSelected.disabled = false;
      btnSaveSelected.textContent = 'Save selected to question bank';

      if (data.status !== 1) {
        alert(data.message || 'Save failed.');
        return;
      }

      alert(data.message || 'Saved.');

      const savedCount = parseInt(data.saved, 10) || 0;
      if (savedCount >= indices.length) {
        const removeIdx = new Set(indices);
        previewQuestions = previewQuestions.filter((_, i) => !removeIdx.has(i));
      }
      selectedSet.clear();
      renderPreview();
      if (typeof window.loadQbAiSummary === 'function') {
        window.loadQbAiSummary();
      }
    } catch (e) {
      btnSaveSelected.disabled = false;
      btnSaveSelected.textContent = 'Save selected to question bank';
      alert('Save request failed.');
    }
  });
})();
