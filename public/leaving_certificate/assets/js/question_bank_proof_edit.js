(function () {
  'use strict';

  var cfg = window.QB_PROOF_EDIT || {};
  if (!cfg.saveUrl) return;

  var modal = $('#qbProofEditModal');
  var form = document.getElementById('qbProofEditForm');
  var currentCard = null;
  var currentQ = null;

  function el(id) { return document.getElementById(id); }

  function toggleTypeBlocks(type) {
    document.querySelectorAll('#qbProofEditModal .qb-pe-block').forEach(function (b) {
      b.classList.add('d-none');
    });
    document.querySelectorAll('#qbProofEditModal .qb-pe-mcq, #qbProofEditModal .qb-pe-mcq_multi').forEach(function (b) {
      b.classList.remove('d-none');
    });

    var mcqSingle = document.querySelector('#qbProofEditModal .qb-pe-mcq-single');
    var mcqMulti = document.querySelector('#qbProofEditModal .qb-pe-mcq-multi');
    if (mcqSingle) mcqSingle.classList.toggle('d-none', type === 'mcq_multi');
    if (mcqMulti) mcqMulti.classList.toggle('d-none', type !== 'mcq_multi');

    if (type === 'mcq' || type === 'mcq_multi') {
      return;
    }

    document.querySelectorAll('#qbProofEditModal .qb-pe-mcq, #qbProofEditModal .qb-pe-mcq_multi').forEach(function (b) {
      b.classList.add('d-none');
    });

    var block = document.querySelector('#qbProofEditModal .qb-pe-' + type);
    if (block) block.classList.remove('d-none');
  }

  function syncAnswerFieldName(type) {
    var tf = el('qb_pe_answer_tf');
    var at = el('qb_pe_answer_text');
    if (tf) tf.removeAttribute('name');
    if (at) at.removeAttribute('name');

    if (type === 'tf' && tf) {
      tf.setAttribute('name', 'questions[0][answer_text]');
    } else if ((type === 'fill' || type === 'short') && at) {
      at.setAttribute('name', 'questions[0][answer_text]');
    }
  }

  function toggleMediaMode(media) {
    var textWrap = document.querySelector('#qbProofEditModal .qb-pe-text-wrap');
    var imageWrap = document.querySelector('#qbProofEditModal .qb-pe-image-wrap');
    if (textWrap) textWrap.classList.toggle('d-none', media === 'image');
    if (imageWrap) imageWrap.classList.toggle('d-none', media !== 'image');
  }

  function loadTopics(classId, subjectId, selectedId) {
    var sel = el('qb_pe_topic_id');
    if (!sel) return Promise.resolve();
    sel.innerHTML = '<option value="">Loading…</option>';

    return fetch(cfg.topicsUrl + '?class_id=' + classId + '&subject_id=' + subjectId, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function (r) { return r.json(); })
      .then(function (topics) {
        sel.innerHTML = '';
        (topics || []).forEach(function (t) {
          var opt = document.createElement('option');
          opt.value = t.id;
          opt.textContent = t.topic_name || ('Topic #' + t.id);
          if (String(t.id) === String(selectedId)) opt.selected = true;
          sel.appendChild(opt);
        });
      })
      .catch(function () {
        sel.innerHTML = '<option value="">(could not load topics)</option>';
      });
  }

  function addMatchPairRow(left, right, idx) {
    var wrap = el('qb_pe_match_pairs');
    if (!wrap) return;
    var row = document.createElement('div');
    row.className = 'form-row mb-2 qb-pe-pair-row';
    row.innerHTML =
      '<div class="col-5"><input type="text" class="form-control form-control-sm qb-pe-left" name="questions[0][match_pairs][' + idx + '][left]" value="' + escapeAttr(left || '') + '"></div>' +
      '<div class="col-5"><input type="text" class="form-control form-control-sm qb-pe-right" name="questions[0][match_pairs][' + idx + '][right]" value="' + escapeAttr(right || '') + '"></div>' +
      '<div class="col-2"><button type="button" class="btn btn-sm btn-outline-danger qb-pe-rm-pair">&times;</button></div>';
    wrap.appendChild(row);
    row.querySelector('.qb-pe-rm-pair').addEventListener('click', function () {
      row.remove();
    });
  }

  function escapeAttr(s) {
    return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
  }

  function fillForm(q) {
    currentQ = q;
    el('qb_pe_id').value = q.id || '';
    el('qb_pe_class_id').value = q.class_id || '';
    el('qb_pe_subject_id').value = q.subject_id || '';
    el('qb_pe_question_type').value = q.question_type || 'mcq';
    el('qb_pe_difficulty').value = q.difficulty || 'normal';
    el('qb_pe_question_media').value = q.question_media || 'text';
    el('qb_pe_question').value = q.question || '';
    el('qb_pe_question_image_alt').value = q.question_image_alt || '';
    el('qb_pe_is_drag').value = q.is_drag ? '1' : '0';
    if (el('qb_pe_is_drag_chk')) el('qb_pe_is_drag_chk').checked = !!q.is_drag;

    el('qb_pe_option_a').value = q.option_a || '';
    el('qb_pe_option_b').value = q.option_b || '';
    el('qb_pe_option_c').value = q.option_c || '';
    el('qb_pe_option_d').value = q.option_d || '';
    el('qb_pe_correct_option').value = (q.correct_option || 'A').toUpperCase();

    document.querySelectorAll('.qb-pe-cm').forEach(function (cb) {
      cb.checked = (q.correct_options || []).indexOf(cb.value) !== -1;
    });

    var ans = q.answer_text || '';
    if (el('qb_pe_answer_tf')) {
      el('qb_pe_answer_tf').value = (String(ans).toLowerCase() === 'true') ? 'True' : 'False';
    }
    if (el('qb_pe_answer_text')) el('qb_pe_answer_text').value = ans;

    var preview = el('qb_pe_image_preview');
    if (preview) {
      if (q.question_image_public_url) {
        preview.src = q.question_image_public_url;
        preview.classList.remove('d-none');
      } else {
        preview.src = '';
        preview.classList.add('d-none');
      }
    }

    var pairsWrap = el('qb_pe_match_pairs');
    if (pairsWrap) {
      pairsWrap.innerHTML = '';
      var pairs = q.match_pairs || [];
      if (!pairs.length) pairs = [{ left: '', right: '' }];
      pairs.forEach(function (p, i) { addMatchPairRow(p.left, p.right, i); });
    }

    var ctx = el('qbProofEditContext');
    if (ctx) {
      ctx.textContent = [q.class_name, q.subject_name, q.topic_name].filter(Boolean).join(' · ') + ' · #' + q.id;
    }

    var type = q.question_type || 'mcq';
    toggleTypeBlocks(type);
    syncAnswerFieldName(type);
    toggleMediaMode(q.question_media || 'text');

    return loadTopics(q.class_id, q.subject_id, q.topic_id);
  }

  function openEdit(qid, card) {
    currentCard = card;
    var url = cfg.questionUrl + '/' + qid;
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || data.status !== 1 || !data.question) {
          alert((data && data.message) || 'Could not load question.');
          return;
        }
        fillForm(data.question).then(function () {
          modal.modal('show');
        });
      })
      .catch(function () {
        alert('Could not load question.');
      });
  }

  function deleteQuestion(id, card) {
    if (!id) return;
    if (!confirm('Delete this question permanently?')) return;

    var body = new FormData();
    body.append('id', id);
    body.append(cfg.csrfName, cfg.csrfHash);

    fetch(cfg.deleteUrl, {
      method: 'POST',
      body: body,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data && data.status === 1) {
          if (modal && modal.hasClass('show')) modal.modal('hide');
          if (card) card.remove();
          else window.location.reload();
          return;
        }
        alert((data && data.message) || 'Delete failed.');
      })
      .catch(function () { alert('Delete failed.'); });
  }

  document.addEventListener('click', function (e) {
    var editBtn = e.target.closest('.qb-proof-btn-edit');
    if (editBtn) {
      e.preventDefault();
      var card = editBtn.closest('.qb-proof-item');
      var qid = editBtn.getAttribute('data-qid');
      if (qid) openEdit(qid, card);
      return;
    }

    var delBtn = e.target.closest('.qb-proof-btn-delete');
    if (delBtn) {
      e.preventDefault();
      var delCard = delBtn.closest('.qb-proof-item');
      deleteQuestion(delBtn.getAttribute('data-qid'), delCard);
    }
  });

  if (el('qb_pe_question_type')) {
    el('qb_pe_question_type').addEventListener('change', function () {
      toggleTypeBlocks(this.value);
      syncAnswerFieldName(this.value);
    });
  }

  if (el('qb_pe_question_media')) {
    el('qb_pe_question_media').addEventListener('change', function () {
      toggleMediaMode(this.value);
    });
  }

  if (el('qb_pe_is_drag_chk')) {
    el('qb_pe_is_drag_chk').addEventListener('change', function () {
      el('qb_pe_is_drag').value = this.checked ? '1' : '0';
    });
  }

  if (el('qb_pe_add_pair')) {
    el('qb_pe_add_pair').addEventListener('click', function () {
      var n = document.querySelectorAll('#qb_pe_match_pairs .qb-pe-pair-row').length;
      addMatchPairRow('', '', n);
    });
  }

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      syncAnswerFieldName(el('qb_pe_question_type').value);

      var fd = new FormData(form);
      var saveBtn = el('qbProofSaveBtn');
      if (saveBtn) saveBtn.disabled = true;

      fetch(cfg.saveUrl, {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data && data.status === 1) {
            modal.modal('hide');
            window.location.reload();
            return;
          }
          alert((data && data.message) || 'Save failed.');
        })
        .catch(function () { alert('Save failed.'); })
        .finally(function () {
          if (saveBtn) saveBtn.disabled = false;
        });
    });
  }

  if (el('qbProofDeleteBtn')) {
    el('qbProofDeleteBtn').addEventListener('click', function () {
      deleteQuestion(el('qb_pe_id').value, currentCard);
    });
  }
})();
