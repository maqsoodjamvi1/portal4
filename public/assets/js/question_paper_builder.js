(function (window, $) {
  'use strict';

  const cfg = window.QP_CONFIG || {};
  window.qpSelectedKeys = window.qpSelectedKeys || new Set();
  let poolQuestions = [];
  let manualSelected = new Set();

  function getTopicKeys() {
    return Array.from(window.qpSelectedKeys || []);
  }

  function getCheckedTypes() {
    const types = [];
    $('.qp-type-check:checked').each(function () {
      types.push($(this).val());
    });
    return types;
  }

  function getCheckedDifficulties() {
    const d = [];
    $('.qp-diff-check:checked').each(function () {
      d.push($(this).val());
    });
    return d;
  }

  function getSelectedBoardPublisherIds() {
    const ids = [];
    const $toggles = $('#qpBoardPublisherToggles .bp-toggle.active');
    if ($toggles.length) {
      $toggles.each(function () {
        const id = parseInt($(this).data('id'), 10);
        if (id > 0) {
          ids.push(id);
        }
      });
      return ids;
    }
    $('#qpBoardPublisherFilter option:selected').each(function () {
      const id = parseInt($(this).val(), 10);
      if (id > 0) ids.push(id);
    });
    return ids;
  }

  function setBoardPublisherFilter(ids) {
    const idSet = new Set((ids || []).map(function (id) { return parseInt(id, 10); }));
    if ($('#qpBoardPublisherToggles').length) {
      $('#qpBoardPublisherToggles .bp-toggle').each(function () {
        const id = parseInt($(this).data('id'), 10);
        const on = idSet.has(id);
        $(this).toggleClass('active', on).attr('aria-pressed', on ? 'true' : 'false');
      });
      return;
    }
    if ($('#qpBoardPublisherFilter').length) {
      $('#qpBoardPublisherFilter').val(Array.from(idSet).map(String));
    }
  }

  const QP_TYPE_KEYS = ['mcq', 'mcq_multi', 'tf', 'fill', 'short', 'descriptive', 'match'];

  const QP_TYPE_LABELS = {
    mcq: 'MCQ',
    mcq_multi: 'Multi',
    tf: 'T/F',
    fill: 'Fill',
    short: 'Short',
    descriptive: 'Desc',
    match: 'Match',
  };

  /** Section keys: mcq + mcq_multi share one section (A). */
  const QP_SECTION_DEFS = [
    { key: 'mcq', types: ['mcq', 'mcq_multi'] },
    { key: 'tf', types: ['tf'] },
    { key: 'fill', types: ['fill'] },
    { key: 'short', types: ['short'] },
    { key: 'descriptive', types: ['descriptive'] },
    { key: 'match', types: ['match'] },
  ];

  function syncDescriptiveAnswerSpaceUi() {
    const on = $('#descriptive_answer_space').is(':checked');
    $('#descriptive_lines_wrap').toggleClass('d-none', !on);
    $('#descriptive_lines').prop('disabled', !on);
  }

  function typeCount(k) {
    return parseInt($('#count_' + k).val(), 10) || 0;
  }

  function marksActiveForType(k) {
    const bank = parseInt((window.qpBankCounts || {})[k], 10) || 0;
    if (bank <= 0) {
      return false;
    }
    const cnt = typeCount(k);
    if (cnt <= 0) {
      return false;
    }
    if (k === 'mcq_multi' && typeCount('mcq') > 0) {
      return false;
    }
    return true;
  }

  function updateTableTotals() {
    const bank = window.qpBankCounts || {};
    const hasTopics = getTopicKeys().length > 0;
    let bankSum = 0;
    QP_TYPE_KEYS.forEach(function (k) {
      bankSum += parseInt(bank[k], 10) || 0;
    });

    const $bankTotal = $('#bank_count_total');
    if ($bankTotal.length) {
      if (!hasTopics) {
        $bankTotal.text('—').removeClass('qp-bank-badge--ok qp-bank-badge--zero').addClass('qp-bank-badge--empty');
      } else {
        const total = bank.total != null ? parseInt(bank.total, 10) : bankSum;
        $bankTotal.text(String(total));
        $bankTotal.removeClass('qp-bank-badge--empty qp-bank-badge--zero qp-bank-badge--ok');
        $bankTotal.addClass(total > 0 ? 'qp-bank-badge--ok' : 'qp-bank-badge--zero');
      }
    }

    const picked = totalPickedCount();
    $('#pick_count_total').text(String(picked));

    let marksTotal = 0;
    const sectionMarks = {};
    QP_TYPE_KEYS.forEach(function (k) {
      sectionMarks[k] = parseInt($('#marks_' + k).val(), 10) || 0;
    });
    QP_SECTION_DEFS.forEach(function (def) {
      const has = def.types.some(function (t) { return typeCount(t) > 0; });
      if (has) {
        marksTotal += resolveSectionMarksForKey(def.key, sectionMarks);
      }
    });
    $('#marks_count_total').text(String(marksTotal));
    $('#qpTotalMarksDisplay').text(marksTotal > 0 ? String(marksTotal) : '0');
    $('#total_marks_display').text(marksTotal > 0 ? String(marksTotal) : '0');
    if ($('#qpPickedTotalDisplay').length) {
      $('#qpPickedTotalDisplay').text(String(picked));
    }
  }

  function syncVisibleTypeColumns() {
    if (!$('.qp-type-col').length) {
      return;
    }

    const bank = window.qpBankCounts || {};
    const hasTopics = getTopicKeys().length > 0;
    let visibleCount = 0;

    QP_TYPE_KEYS.forEach(function (k) {
      const avail = hasTopics ? (parseInt(bank[k], 10) || 0) : 0;
      const show = avail > 0;
      if (show) {
        visibleCount++;
      }
      $('.qp-type-col[data-type="' + k + '"]').toggleClass('d-none', !show);
      if (!show) {
        $('#count_' + k).val(0);
        $('#marks_' + k).val(0);
      }
    });

    const $empty = $('#qpTypeTableEmpty');
    const $dataRows = $('.qp-bank-row, .qp-pick-row, .qp-marks-row');
    if (!$empty.length) {
      return;
    }

    if (!hasTopics) {
      $empty.removeClass('d-none');
      $dataRows.addClass('d-none');
      $('#qpTypeTableEmptyMsg').text('Select topics to see question types.');
    } else if (visibleCount === 0) {
      $empty.removeClass('d-none');
      $dataRows.addClass('d-none');
      $('#qpTypeTableEmptyMsg').text('No questions in bank for the selected topics.');
    } else {
      $empty.addClass('d-none');
      $dataRows.removeClass('d-none');
    }
  }

  function syncPickInputsAvailability() {
    const bank = window.qpBankCounts || {};
    const hasTopics = getTopicKeys().length > 0;

    syncVisibleTypeColumns();

    QP_TYPE_KEYS.forEach(function (k) {
      const avail = parseInt(bank[k], 10) || 0;
      const $input = $('#count_' + k);
      const $cell = $input.closest('.qp-count-field');
      const pick = typeCount(k);
      const colHidden = $cell.hasClass('d-none');

      if (!hasTopics || avail <= 0 || colHidden) {
        if (pick > 0) {
          $input.val(0);
        }
        $input.prop('disabled', true).attr('max', 0);
        $cell.addClass('qp-pick-disabled');
        $input.removeClass('is-over-bank');
      } else {
        $input.prop('disabled', false).attr('max', avail);
        $cell.removeClass('qp-pick-disabled');
        $input.toggleClass('is-over-bank', pick > avail);
      }
    });

    syncSectionMarksUi();
    updateTableTotals();
  }

  function syncSectionMarksUi() {
    QP_TYPE_KEYS.forEach(function (k) {
      const active = marksActiveForType(k);
      const $marks = $('#marks_' + k);
      $marks.prop('disabled', !active);
      $('#count_' + k).closest('.qp-count-field').toggleClass('qp-marks-inactive', !active);
    });
    updateSectionMarksSummary();
  }

  function resolveSectionMarksForKey(sectionKey, sectionMarks) {
    const def = QP_SECTION_DEFS.find(function (d) { return d.key === sectionKey; });
    const keys = def ? def.types : [sectionKey];
    for (let i = 0; i < keys.length; i++) {
      const m = parseFloat(sectionMarks[keys[i]]) || 0;
      if (m > 0) {
        return m;
      }
    }
    return 0;
  }

  function countActiveSections() {
    let n = 0;
    QP_SECTION_DEFS.forEach(function (def) {
      const has = def.types.some(function (t) { return typeCount(t) > 0; });
      if (has) {
        n++;
      }
    });
    return n;
  }

  function updateSectionMarksSummary() {
    const sectionMarks = {};
    QP_TYPE_KEYS.forEach(function (k) {
      sectionMarks[k] = parseInt($('#marks_' + k).val(), 10) || 0;
    });

    let total = 0;
    QP_SECTION_DEFS.forEach(function (def) {
      const has = def.types.some(function (t) { return typeCount(t) > 0; });
      if (has) {
        total += resolveSectionMarksForKey(def.key, sectionMarks);
      }
    });

    const sections = countActiveSections();
    $('#qpSectionSummary').text(sections + (sections === 1 ? ' section' : ' sections'));
    updateTableTotals();
    syncDescChoiceUi();
  }

  let descPairsStore = [];

  function descriptiveQuestionCount() {
    return typeCount('descriptive');
  }

  function questionNumberOptions(n) {
    let html = '';
    for (let i = 1; i <= n; i++) {
      html += '<option value="' + i + '">Q' + i + '</option>';
    }
    return html;
  }

  function syncDescPairsJson() {
    $('#descriptive_pairs_json').val(JSON.stringify(descPairsStore));
  }

  function renderDescPairRows() {
    const n = descriptiveQuestionCount();
    const $list = $('#desc_pairs_list');
    $list.empty();
    if (!descPairsStore.length) {
      $list.html('<p class="small text-muted mb-0">No pairs yet. Add pairs or use auto-pair.</p>');
      syncDescPairsJson();
      return;
    }
    const opts = questionNumberOptions(n);
    descPairsStore.forEach(function (pair, idx) {
      const a = pair[0] || 1;
      const b = pair[1] || Math.min(2, n);
      const row =
        '<div class="qp-desc-pair-row" data-idx="' + idx + '">' +
        '<span class="qp-pair-badge">Pair ' + (idx + 1) + '</span>' +
        '<select class="form-control form-control-sm qp-pair-a">' + opts + '</select>' +
        '<span class="qp-pair-or-label">OR</span>' +
        '<select class="form-control form-control-sm qp-pair-b">' + opts + '</select>' +
        '<button type="button" class="btn btn-xs btn-outline-danger qp-pair-remove" title="Remove">&times;</button>' +
        '</div>';
      const $row = $(row);
      $row.find('.qp-pair-a').val(String(a));
      $row.find('.qp-pair-b').val(String(b));
      $list.append($row);
    });
    syncDescPairsJson();
  }

  function readDescPairsFromDom() {
    const pairs = [];
    $('#desc_pairs_list .qp-desc-pair-row').each(function () {
      const a = parseInt($(this).find('.qp-pair-a').val(), 10) || 0;
      const b = parseInt($(this).find('.qp-pair-b').val(), 10) || 0;
      if (a > 0 && b > 0 && a !== b) {
        pairs.push([a, b]);
      }
    });
    descPairsStore = pairs;
    syncDescPairsJson();
  }

  function autoPairDescriptive() {
    const n = descriptiveQuestionCount();
    descPairsStore = [];
    for (let i = 1; i + 1 <= n; i += 2) {
      descPairsStore.push([i, i + 1]);
    }
    renderDescPairRows();
  }

  function syncDescChoiceUi() {
    const n = descriptiveQuestionCount();
    const $panel = $('#qpDescChoicePanel');
    if (n <= 0) {
      $panel.addClass('d-none');
      return;
    }
    $panel.removeClass('d-none');

    const mode = $('#descriptive_choice_mode').val() || 'none';
    $('#desc_attempt_any_wrap').toggleClass('d-none', mode !== 'attempt_any');
    $('#desc_pairs_wrap').toggleClass('d-none', mode !== 'pairs');

    const $anyCnt = $('#descriptive_attempt_any_count');
    $anyCnt.attr('max', String(n));
    if (parseInt($anyCnt.val(), 10) > n) {
      $anyCnt.val(String(n));
    }
    if (parseInt($anyCnt.val(), 10) <= 0) {
      $anyCnt.val(String(Math.min(6, n)));
    }

    if (mode === 'pairs') {
      renderDescPairRows();
    }
  }

  function appendDescChoiceFields(fd) {
    fd.append('descriptive_choice_mode', $('#descriptive_choice_mode').val() || 'none');
    fd.append('descriptive_attempt_any_count', $('#descriptive_attempt_any_count').val() || '0');
    readDescPairsFromDom();
    fd.append('descriptive_pairs_json', $('#descriptive_pairs_json').val() || '[]');
  }

  function applyDescriptiveChoiceFromConfig(l) {
    const dc = l.descriptive_choice || {};
    $('#descriptive_choice_mode').val(dc.mode || 'none');
    $('#descriptive_attempt_any_count').val(dc.attempt_any_count || 6);
    descPairsStore = Array.isArray(dc.pairs) ? dc.pairs.map(function (p) {
      return [parseInt(p[0], 10) || 0, parseInt(p[1], 10) || 0];
    }).filter(function (p) { return p[0] > 0 && p[1] > 0; }) : [];
    syncDescPairsJson();
    syncDescChoiceUi();
    if ((dc.mode || '') === 'pairs') {
      renderDescPairRows();
    }
  }

  function appendFormFields(fd) {
    fd.append(cfg.csrfName, cfg.csrfHash);
    getTopicKeys().forEach(function (k) {
      fd.append('topic_keys[]', k);
    });
    getCheckedTypes().forEach(function (t) {
      fd.append('question_types[]', t);
    });
    getCheckedDifficulties().forEach(function (d) {
      fd.append('difficulties[]', d);
    });
    getSelectedBoardPublisherIds().forEach(function (id) {
      fd.append('board_publisher_ids[]', String(id));
    });

    const fields = [
      'paper_title', 'paper_subject', 'paper_class', 'exam_date', 'exam_time',
      'duration', 'instructions', 'selection_mode',
      'paper_mode', 'font_size', 'versions',
    ];
    fields.forEach(function (name) {
      const el = document.getElementById(name) || document.querySelector('[name="' + name + '"]');
      if (el) fd.append(name, el.value || '');
    });

    ['show_name', 'show_roll', 'show_section', 'show_topics', 'mcq_inline', 'page_break_topic',
      'shuffle_questions', 'shuffle_mcq_options', 'group_by_topic', 'fixed_questions',
      'descriptive_answer_space', 'show_question_marks'].forEach(function (name) {
      const el = document.getElementById(name);
      fd.append(name, el && el.checked ? '1' : '0');
    });

    fd.append('columns', $('#columns').val() || '1');
    fd.append('descriptive_lines', $('#descriptive_lines').val() || '6');

    QP_TYPE_KEYS.forEach(function (k) {
      fd.append('count_' + k, $('#count_' + k).val() || '0');
      fd.append('marks_' + k, $('#marks_' + k).val() || '0');
    });

    if ($('#selection_mode').val() === 'manual') {
      manualSelected.forEach(function (id) {
        fd.append('question_ids[]', String(id));
      });
    }
    appendDescChoiceFields(fd);
  }

  function postToNewTab(url) {
    const $form = $('<form method="post" target="_blank" style="display:none">')
      .attr('action', url)
      .appendTo('body');
    function add(name, val) {
      $('<input type="hidden">').attr('name', name).val(val).appendTo($form);
    }
    add(cfg.csrfName, cfg.csrfHash);
    getTopicKeys().forEach(function (k) {
      add('topic_keys[]', k);
    });
    getCheckedTypes().forEach(function (t) {
      add('question_types[]', t);
    });
    getCheckedDifficulties().forEach(function (d) {
      add('difficulties[]', d);
    });
    getSelectedBoardPublisherIds().forEach(function (id) {
      add('board_publisher_ids[]', String(id));
    });
    [
      'paper_title', 'paper_subject', 'paper_class', 'exam_date', 'exam_time',
      'duration', 'instructions', 'selection_mode',
      'paper_mode', 'font_size', 'versions', 'columns', 'descriptive_lines',
    ].forEach(function (name) {
      const el = document.getElementById(name);
      if (el) add(name, el.value || '');
    });
    ['show_name', 'show_roll', 'show_section', 'show_topics', 'mcq_inline', 'page_break_topic',
      'shuffle_questions', 'shuffle_mcq_options', 'group_by_topic', 'fixed_questions',
      'descriptive_answer_space', 'show_question_marks'].forEach(function (name) {
      const el = document.getElementById(name);
      add(name, el && el.checked ? '1' : '0');
    });
    QP_TYPE_KEYS.forEach(function (k) {
      add('count_' + k, $('#count_' + k).val() || '0');
      add('marks_' + k, $('#marks_' + k).val() || '0');
    });
    if ($('#selection_mode').val() === 'manual') {
      manualSelected.forEach(function (id) {
        add('question_ids[]', String(id));
      });
    }
    readDescPairsFromDom();
    add('descriptive_choice_mode', $('#descriptive_choice_mode').val() || 'none');
    add('descriptive_attempt_any_count', $('#descriptive_attempt_any_count').val() || '0');
    add('descriptive_pairs_json', $('#descriptive_pairs_json').val() || '[]');
    $form[0].submit();
    $form.remove();
  }

  function totalPickedCount() {
    let n = 0;
    QP_TYPE_KEYS.forEach(function (k) {
      n += typeCount(k);
    });
    return n;
  }

  function validatePickAgainstBank() {
    syncPickInputsAvailability();
  }

  function renderCounts(counts) {
    window.qpBankCounts = counts || {};
    const hasTopics = getTopicKeys().length > 0;
    const total = counts && counts.total ? parseInt(counts.total, 10) : 0;

    QP_TYPE_KEYS.forEach(function (k) {
      const $badge = $('#bank_count_' + k);
      if (!$badge.length) {
        return;
      }
      if (!hasTopics) {
        $badge.text('—').removeClass('qp-bank-badge--ok qp-bank-badge--zero').addClass('qp-bank-badge--empty');
        return;
      }
      const n = counts && counts[k] ? parseInt(counts[k], 10) : 0;
      $badge.text(String(n));
      $badge.removeClass('qp-bank-badge--empty qp-bank-badge--ok qp-bank-badge--zero');
      if (n > 0) {
        $badge.addClass('qp-bank-badge--ok');
      } else {
        $badge.addClass('qp-bank-badge--zero');
      }
    });

    const $totalHdr = $('#qpBankTotalDisplay');
    if ($totalHdr.length) {
      if (!hasTopics) {
        $totalHdr.text('Select topics to see bank counts');
      } else if (total > 0) {
        $totalHdr.html('<strong class="text-primary">' + total + '</strong> in bank');
      } else {
        $totalHdr.text('No questions in bank');
      }
    }

    syncPickInputsAvailability();

    const $legacyBox = $('#qpTypeCounts');
    if ($legacyBox.length) {
      if (!hasTopics || !total) {
        $legacyBox.html('<span class="text-muted small">Select topics to see available counts.</span>');
      } else {
        const parts = ['Total: ' + total];
        const map = [
          ['mcq', 'MCQ'], ['mcq_multi', 'MCQ multi'], ['tf', 'T/F'], ['fill', 'Fill'],
          ['short', 'Short'], ['descriptive', 'Descriptive'], ['match', 'Match'],
        ];
        map.forEach(function (pair) {
          const n = counts[pair[0]] || 0;
          if (n > 0) {
            parts.push(pair[1] + ': ' + n);
          }
        });
        $legacyBox.html(parts.join(' · '));
      }
    }
  }

  function updateManualSelectionSummary() {
    const $box = $('#qpManualSelectionSummary');
    if (!$box.length) {
      return;
    }

    const counts = {};
    QP_TYPE_KEYS.forEach(function (k) {
      counts[k] = 0;
    });

    poolQuestions.forEach(function (row) {
      if (!manualSelected.has(row.id)) {
        return;
      }
      const t = String(row.question_type || 'mcq').toLowerCase();
      if (Object.prototype.hasOwnProperty.call(counts, t)) {
        counts[t]++;
      }
    });

    const total = manualSelected.size;
    if (total === 0) {
      $box.text('No questions selected.');
      return;
    }

    const parts = ['Selected: ' + total];
    QP_TYPE_KEYS.forEach(function (k) {
      if (counts[k] > 0) {
        parts.push((QP_TYPE_LABELS[k] || k) + ': ' + counts[k]);
      }
    });
    $box.text(parts.join(' · '));
  }

  function renderManualList() {
    const q = ($('#qpManualSearch').val() || '').toLowerCase();
    const $list = $('#qpManualList');
    if (!poolQuestions.length) {
      $list.html('<p class="text-muted small p-2">No questions in pool.</p>');
      updateManualSelectionSummary();
      return;
    }
    let html = '';
    poolQuestions.forEach(function (row) {
      const text = (row.question || '').toLowerCase();
      if (q && text.indexOf(q) === -1 && (row.topic_name || '').toLowerCase().indexOf(q) === -1) {
        return;
      }
      const id = row.id;
      const checked = manualSelected.has(id);
      html +=
        '<label class="d-block border-bottom px-2 py-1 mb-0 small qp-manual-row">' +
        '<input type="checkbox" class="qp-manual-check me-2" data-id="' +
        id +
        '"' +
        (checked ? ' checked' : '') +
        '> <span class="badge text-bg-light">' +
        (row.question_type || '') +
        '</span> ' +
        $('<div>').text((row.question || '').substring(0, 120)).html() +
        ' <span class="text-muted">(' +
        (row.topic_name || '') +
        ')</span></label>';
    });
    $list.html(html || '<p class="text-muted small p-2">No match.</p>');
    updateManualSelectionSummary();
  }

  window.qpReloadPool = function () {
    if (!getTopicKeys().length) {
      poolQuestions = [];
      manualSelected.clear();
      renderCounts({});
      renderManualList();
      return;
    }
    const fd = new FormData();
    appendFormFields(fd);
    $.ajax({
      url: cfg.questionsUrl,
      method: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      dataType: 'json',
    }).done(function (res) {
      if (!res || !res.ok) return;
      poolQuestions = res.data || [];
      renderCounts(res.counts || {});
      renderManualList();
      if (window.QpBuilderApi && window.QpBuilderApi.updateQuizQuestionCountPreview) {
        window.QpBuilderApi.updateQuizQuestionCountPreview();
      }
    });
  };

  window.qpOnTopicsChanged = function () {
    const keys = getTopicKeys();
    if (keys.length && window.qpTopicMeta) {
      const first = window.qpTopicMeta[keys[0]];
      if (first && !$('#paper_subject').val()) {
        $('#paper_subject').val(first.subject_name || '');
        $('#paper_class').val(first.class_name || '');
      }
    }
    window.qpReloadPool();
  };

  function flatRowsToTree(rows) {
    const treeMap = {};
    (rows || []).forEach(function (r) {
      const cid = String(r.class_id);
      const sid = String(r.subject_id);
      if (!treeMap[cid]) {
        treeMap[cid] = {
          class_id: r.class_id,
          class_name: r.class_name || 'Class ' + cid,
          subjects: {},
        };
      }
      if (!treeMap[cid].subjects[sid]) {
        treeMap[cid].subjects[sid] = {
          subject_id: r.subject_id,
          subject_name: r.subject_name || 'Subject ' + sid,
          topics: [],
        };
      }
      treeMap[cid].subjects[sid].topics.push({
        class_id: r.class_id,
        subject_id: r.subject_id,
        topic_id: r.topic_id,
        topic_name: r.topic_name || 'Topic',
        question_count: parseInt(r.question_count, 10) || 0,
      });
    });
    return Object.keys(treeMap).map(function (cid) {
      const cls = treeMap[cid];
      cls.subjects = Object.keys(cls.subjects).map(function (sid) {
        return cls.subjects[sid];
      });
      return cls;
    });
  }

  function normalizeSummaryPayload(data) {
    if (!Array.isArray(data) || !data.length) {
      return [];
    }
    const first = data[0];
    if (first && Array.isArray(first.subjects)) {
      return data;
    }
    return flatRowsToTree(data);
  }

  function showQbLoadError(msg) {
    $('#qbClassList').html(
      '<div class="qb-col-placeholder text-muted p-3 small text-danger">' + $('<div>').text(msg || 'Failed to load').html() + '</div>'
    );
    $('#qbSubjectList').html('<div class="qb-col-placeholder text-muted p-3 small">—</div>');
    $('#qbTopicList').html('<div class="qb-col-placeholder text-muted p-3 small">—</div>');
    if ($('#qpBankTotalDisplay').length) {
      $('#qpBankTotalDisplay').text('Could not load question bank');
    }
    renderCounts({});
  }

  function applySummaryResponse(res) {
    if (!res || !res.ok) {
      showQbLoadError((res && res.msg) || 'Could not load question bank topics.');
      return;
    }
    const tree = normalizeSummaryPayload(res.data || []);
    if (!window.QuestionPaperQbBrowser) {
      showQbLoadError('Topic browser script failed to load. Hard-refresh the page.');
      return;
    }
    if (!tree.length) {
      showQbLoadError('No questions in the bank yet. Add questions under QB Overview first.');
      return;
    }
    window.QuestionPaperQbBrowser.render(tree);
    if (window.AB_BOARD_PREP_MODE && window.QuestionPaperQbBrowser.focusClassSubject) {
      const cidEl = document.getElementById('bp_filter_class_id');
      const sidEl = document.getElementById('subject_id');
      if (cidEl && sidEl && cidEl.value && sidEl.value !== '0' && sidEl.value) {
        window.QuestionPaperQbBrowser.focusClassSubject(cidEl.value, sidEl.value);
      }
    }
    if (window.qpPendingTopicKeys && window.qpPendingTopicKeys.length && window.QuestionPaperQbBrowser.applySavedKeys) {
      window.qpSelectedKeys.clear();
      window.QuestionPaperQbBrowser.applySavedKeys(window.qpPendingTopicKeys);
      window.qpPendingTopicKeys = null;
    }
  }

  function summaryQueryString() {
    const params = [];
    getSelectedBoardPublisherIds().forEach(function (id) {
      params.push('board_publisher_ids[]=' + encodeURIComponent(String(id)));
    });
    if (typeof cfg.extraSummaryParams === 'function') {
      (cfg.extraSummaryParams() || []).forEach(function (p) {
        if (p) params.push(p);
      });
    }
    return params.length ? ('?' + params.join('&')) : '';
  }

  function loadSummary() {
    const primary = cfg.summaryUrl + summaryQueryString();
    const fallback = cfg.summaryFallbackUrl;

    $.getJSON(primary)
      .done(function (res) {
        if (res && res.ok && res.data && res.data.length) {
          applySummaryResponse(res);
          return;
        }
        if (fallback) {
          $.getJSON(fallback).done(applySummaryResponse).fail(function (xhr) {
            showQbLoadError('Network error loading topics (' + (xhr.status || '') + ').');
          });
          return;
        }
        applySummaryResponse(res);
      })
      .fail(function () {
        if (!fallback) {
          showQbLoadError('Network error. Check that question-paper routes are deployed.');
          return;
        }
        $.getJSON(fallback).done(applySummaryResponse).fail(function (xhr) {
          showQbLoadError('Network error loading topics (' + (xhr.status || '') + ').');
        });
      });
  }

  function loadTemplates() {
    $.getJSON(cfg.templatesUrl).done(function (res) {
      const $box = $('#qpTemplateList');
      if (!res || !res.ok || !res.data || !res.data.length) {
        $box.html('<span class="text-muted small">No saved templates yet.</span>');
        return;
      }
      let html = '';
      res.data.forEach(function (t) {
        const name = $('<div>').text(t.name).html();
        html +=
          '<span class="qp-tpl-chip">' +
          '<button type="button" class="btn btn-light qp-load-tpl" data-id="' +
          t.id +
          '" title="Load template">' +
          name +
          '</button>' +
          '<button type="button" class="btn btn-light text-danger qp-del-tpl" data-id="' +
          t.id +
          '" title="Delete">&times;</button></span>';
      });
      $box.html(html);
    });
  }

  function applyTemplate(data) {
    const c = data.config || {};
    const h = c.header || {};
    const l = c.layout || {};
    $('#paper_title').val(h.title || '');
    $('#paper_subject').val(h.subject || '');
    $('#paper_class').val(h.class_label || '');
    $('#exam_date').val(h.exam_date || '');
    $('#exam_time').val(h.exam_time || '');
    $('#duration').val(h.duration || '');
    $('#instructions').val(h.instructions || '');
    $('#show_name').prop('checked', !!h.show_name);
    $('#show_roll').prop('checked', !!h.show_roll);
    $('#show_section').prop('checked', !!h.show_section);
    $('#paper_mode').val(l.paper_mode || 'student');
    $('#columns').val(String(l.columns || 1));
    $('#font_size').val(l.font_size || 'normal');
    $('#show_topics').prop('checked', l.show_topics !== false);
    $('#mcq_inline').prop('checked', !!l.mcq_inline);
    const descSpace = l.descriptive_answer_space != null
      ? !!l.descriptive_answer_space
      : (parseInt(l.descriptive_lines, 10) || 0) > 0;
    $('#descriptive_answer_space').prop('checked', descSpace);
    $('#descriptive_lines').val(l.descriptive_lines != null ? l.descriptive_lines : 6);
    syncDescriptiveAnswerSpaceUi();
    $('#page_break_topic').prop('checked', !!l.page_break_topic);
    $('#shuffle_questions').prop('checked', !!l.shuffle_questions);
    $('#shuffle_mcq_options').prop('checked', !!l.shuffle_mcq_options);
    $('#group_by_topic').prop('checked', !!l.group_by_topic);
    $('#show_question_marks').prop('checked', !!l.show_question_marks);
    $('#versions').val(l.versions || 1);
    $('#selection_mode').val(c.selection_mode || 'auto');
    $('#fixed_questions').prop('checked', !!c.fixed_questions);
    const counts = c.counts || {};
    const sectionMarks = c.section_marks || {};
    QP_TYPE_KEYS.forEach(function (k) {
      $('#count_' + k).val(counts[k] || 0);
      $('#marks_' + k).val(sectionMarks[k] || 0);
    });
    syncSectionMarksUi();
    applyDescriptiveChoiceFromConfig(l);
    if (c.filters && c.filters.question_types) {
      $('.qp-type-check').prop('checked', false);
      c.filters.question_types.forEach(function (t) {
        $('.qp-type-check[value="' + t + '"]').prop('checked', true);
      });
    }
    const savedTopicKeys = (data.topic_keys && data.topic_keys.length)
      ? data.topic_keys
      : ((c.topic_keys && c.topic_keys.length) ? c.topic_keys : []);

    if (c.filters && c.filters.board_publisher_ids && c.filters.board_publisher_ids.length) {
      setBoardPublisherFilter(c.filters.board_publisher_ids);
      window.qpPendingTopicKeys = savedTopicKeys;
      loadSummary();
    } else if (savedTopicKeys.length) {
      window.qpSelectedKeys.clear();
      window.QuestionPaperQbBrowser.applySavedKeys(savedTopicKeys);
    }
    if (data.question_ids && data.question_ids.length) {
      manualSelected = new Set(data.question_ids.map(Number));
      $('#selection_mode').val('manual');
      updateManualSelectionSummary();
    }
    $('.qp-selection-tab[data-mode="' + $('#selection_mode').val() + '"]').click();
    window.qpReloadPool();
  }

  $(function () {
    const isBuilder = !!window.AB_ASSESSMENT_BUILDER;

    loadSummary();
    if ($('.qp-type-col').length && !getTopicKeys().length) {
      renderCounts({});
    }
    if (!isBuilder) {
      loadTemplates();
    }
    $('#qpApplyBoardFilter').on('click', function () {
      window.qpSelectedKeys.clear();
      loadSummary();
    });
    $('#qpClearBoardFilter').on('click', function () {
      setBoardPublisherFilter([]);
      window.qpSelectedKeys.clear();
      loadSummary();
    });
    $(document).on('click', '#qpBoardPublisherToggles .bp-toggle', function () {
      const on = !$(this).hasClass('active');
      $(this).toggleClass('active', on).attr('aria-pressed', on ? 'true' : 'false');
      window.qpSelectedKeys.clear();
      loadSummary();
    });
    syncDescriptiveAnswerSpaceUi();
    syncSectionMarksUi();
    $('#descriptive_answer_space').on('change', syncDescriptiveAnswerSpaceUi);
    $(document).on('input change', '.qp-type-count, .qp-marks-input', syncSectionMarksUi);
    $('#descriptive_choice_mode').on('change', syncDescChoiceUi);
    $('#btnDescAddPair').on('click', function () {
      const n = descriptiveQuestionCount();
      if (n < 2) {
        alert('Set at least 2 descriptive questions to create pairs.');
        return;
      }
      readDescPairsFromDom();
      descPairsStore.push([1, Math.min(2, n)]);
      renderDescPairRows();
    });
    $('#btnDescAutoPair').on('click', autoPairDescriptive);
    $(document).on('click', '.qp-pair-remove', function () {
      readDescPairsFromDom();
      const idx = $(this).closest('.qp-desc-pair-row').index();
      if (idx >= 0) {
        descPairsStore.splice(idx, 1);
      }
      renderDescPairRows();
    });
    $(document).on('change', '.qp-pair-a, .qp-pair-b', readDescPairsFromDom);

    $('.qp-selection-tab').on('click', function () {
      const mode = $(this).data('mode');
      $('#selection_mode').val(mode);
      $('.qp-selection-tab').removeClass('active');
      $(this).addClass('active');
      $('#qpAutoPanel').toggle(mode === 'auto' || mode === 'all');
      $('#qpManualPanel').toggle(mode === 'manual');
      if (mode === 'manual') {
        updateManualSelectionSummary();
      }
    });

    $(document).on('change', '.qp-manual-check', function () {
      const id = parseInt($(this).data('id'), 10);
      if ($(this).is(':checked')) manualSelected.add(id);
      else manualSelected.delete(id);
      updateManualSelectionSummary();
    });

    $('#qpManualSelectAll').on('click', function () {
      poolQuestions.forEach(function (r) {
        manualSelected.add(r.id);
      });
      renderManualList();
    });
    $('#qpManualClear').on('click', function () {
      manualSelected.clear();
      renderManualList();
    });
    $('#qpManualSearch').on('input', renderManualList);

    if (!isBuilder) {
    $('#btnPreview').on('click', function () {
      const fd = new FormData();
      appendFormFields(fd);
      $.ajax({
        url: cfg.previewUrl,
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        dataType: 'json',
      }).done(function (res) {
        if (!res || !res.ok) {
          alert((res && res.msg) || 'Could not generate paper');
          return;
        }
        $('#qpPreviewArea').removeClass('d-none').find('.card-body').html(res.html || '');
        $('#qpPreviewMeta').text(res.count + ' question(s)');
      });
    });

    $('#btnPrint').on('click', function () {
      postToNewTab(cfg.printUrl);
    });
    $('#btnPrintKey').on('click', function () {
      postToNewTab(cfg.printKeyUrl);
    });
    $('#btnPrintVersions').on('click', function () {
      postToNewTab(cfg.printVersionsUrl);
    });
    $('#btnDownloadWord').on('click', function () {
      postToNewTab(cfg.downloadWordUrl);
    });
    $('#btnDownloadWordKey').on('click', function () {
      postToNewTab(cfg.downloadWordKeyUrl);
    });

    $('#btnSaveTemplate').on('click', function () {
      const name = $('#template_name').val();
      if (!name) {
        alert('Enter a template name');
        return;
      }
      const fd = new FormData();
      appendFormFields(fd);
      fd.append('name', name);
      fd.append('template_id', $('#template_id').val() || '0');
      fd.append('fixed_questions', $('#fixed_questions').is(':checked') ? '1' : '0');
      getTopicKeys().forEach(function (k) {
        fd.append('topic_keys_save[]', k);
      });
      $.ajax({
        url: cfg.saveTemplateUrl,
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        dataType: 'json',
      }).done(function (res) {
        alert((res && res.msg) || (res.ok ? 'Saved' : 'Failed'));
        if (res && res.ok) {
          $('#template_id').val(res.id || '');
          loadTemplates();
        }
      });
    });

    $(document).on('click', '.qp-load-tpl', function () {
      const id = $(this).data('id');
      $.getJSON(cfg.loadTemplateUrl + '/' + id).done(function (res) {
        if (!res || !res.ok) {
          alert((res && res.msg) || 'Load failed');
          return;
        }
        const payload = res.data || {};
        if (payload.config && payload.config.topic_keys) {
          payload.topic_keys = payload.config.topic_keys;
        }
        $('#template_id').val(payload.id);
        $('#template_name').val(payload.name);
        applyTemplate(payload);
      });
    });

    $(document).on('click', '.qp-del-tpl', function () {
      if (!confirm('Delete this template?')) return;
      const id = $(this).data('id');
      const fd = new FormData();
      fd.append(cfg.csrfName, cfg.csrfHash);
      $.ajax({
        url: cfg.deleteTemplateUrl + '/' + id,
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        dataType: 'json',
      }).done(function () {
        loadTemplates();
      });
    });
    } // end !isBuilder print/template handlers
  });

  function sampleByTypeCounts(pool, counts) {
    const canonical = function (t) {
      t = String(t || '').toLowerCase();
      if (t === 'mcq' || t === 'mcq_single') return 'mcq';
      if (t === 'true_false') return 'tf';
      if (t === 'fill_blank') return 'fill';
      if (t === 'short_answer') return 'short';
      return t;
    };
    const typeMap = {
      mcq: 'mcq',
      mcq_multi: 'mcq_multi',
      tf: 'tf',
      fill: 'fill',
      short: 'short',
      descriptive: 'descriptive',
      match: 'match',
    };
    const buckets = {};
    (pool || []).forEach(function (q) {
      const t = canonical(q.question_type);
      if (!buckets[t]) {
        buckets[t] = [];
      }
      buckets[t].push(q);
    });
    const picked = [];
    Object.keys(typeMap).forEach(function (key) {
      const type = typeMap[key];
      const need = Math.max(0, parseInt(counts[key], 10) || 0);
      if (need <= 0 || !buckets[type] || !buckets[type].length) {
        return;
      }
      const bucket = buckets[type].slice();
      for (let i = bucket.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        const tmp = bucket[i];
        bucket[i] = bucket[j];
        bucket[j] = tmp;
      }
      picked.push.apply(picked, bucket.slice(0, need));
    });
    return picked;
  }

  function resolveSelectedQuestions() {
    const mode = ($('#selection_mode').val() || 'auto').toString();
    if (!poolQuestions.length) {
      return [];
    }
    if (mode === 'manual') {
      return poolQuestions.filter(function (q) {
        return manualSelected.has(q.id);
      });
    }
    if (mode === 'all') {
      return poolQuestions.slice();
    }
    const counts = {};
    QP_TYPE_KEYS.forEach(function (k) {
      counts[k] = typeCount(k);
    });
    return sampleByTypeCounts(poolQuestions, counts);
  }

  function countQuestionsByQuizType(questions) {
    const c = {
      mcq_single: 0,
      mcq_multi: 0,
      tf: 0,
      fill: 0,
      short: 0,
      match: 0,
    };
    (questions || []).forEach(function (q) {
      const t = String(q.question_type || '').toLowerCase();
      if (t === 'mcq' || t === 'mcq_single') {
        c.mcq_single++;
      } else if (t === 'mcq_multi') {
        c.mcq_multi++;
      } else if (t === 'tf' || t === 'true_false') {
        c.tf++;
      } else if (t === 'fill' || t === 'fill_blank') {
        c.fill++;
      } else if (t === 'short' || t === 'short_answer' || t === 'descriptive') {
        c.short++;
      } else if (t === 'match') {
        c.match++;
      }
    });
    return c;
  }

  function setQuizPlannedTypeCounts(mode) {
    if (mode === 'auto') {
      $('#quiz_count_mcq_single').val(typeCount('mcq'));
      $('#quiz_count_mcq_multi').val(typeCount('mcq_multi'));
      $('#quiz_count_tf').val(typeCount('tf'));
      $('#quiz_count_fill').val(typeCount('fill'));
      $('#quiz_count_short').val(typeCount('short') + typeCount('descriptive'));
      $('#quiz_count_match').val(typeCount('match'));
      return;
    }
    const typeCounts = countQuestionsByQuizType(resolveSelectedQuestions());
    $('#quiz_count_mcq_single').val(typeCounts.mcq_single);
    $('#quiz_count_mcq_multi').val(typeCounts.mcq_multi);
    $('#quiz_count_tf').val(typeCounts.tf);
    $('#quiz_count_fill').val(typeCounts.fill);
    $('#quiz_count_short').val(typeCounts.short);
    $('#quiz_count_match').val(typeCounts.match);
  }

  function updateQuizQuestionCountPreview() {
    const $total = $('#questions_count');
    if (!$total.length) {
      return;
    }
    const questions = resolveSelectedQuestions();
    $total.val(questions.length);
    setQuizPlannedTypeCounts($('#selection_mode').val() || 'auto');
  }

  function syncQuizFormFromSelection() {
    const questions = resolveSelectedQuestions();
    if (!questions.length) {
      alert('Select topics, set question counts (Pick row), then save the quiz.');
      return false;
    }
    if (window.AB_BOARD_PREP_MODE) {
      const boardId = parseInt($('#prep_board_publisher_id').val() || '0', 10);
      const grade = ($('#prep_grade_level').val() || '').toString().trim();
      const subjId = parseInt($('#subject_id').val() || '0', 10);
      if (!boardId || !grade || !subjId) {
        alert('Select board, grade, and subject first.');
        return false;
      }
    }
    const topicKeys = getTopicKeys();
    if ($('#topic_keys_json').length) {
      $('#topic_keys_json').val(JSON.stringify(topicKeys));
    }
    const $wrap = $('#quizQuestionIdsWrap');
    $wrap.empty();
    questions.forEach(function (q) {
      $('<input type="hidden" name="question_ids[]">').val(q.id).appendTo($wrap);
    });
    const topicIdSeen = {};
    topicKeys.forEach(function (k) {
      const parts = String(k).split('|');
      if (parts.length < 3) {
        return;
      }
      const tid = parseInt(parts[2], 10);
      if (tid > 0 && !topicIdSeen[tid]) {
        topicIdSeen[tid] = true;
        $('<input type="hidden" name="quiz_topic_ids[]">').val(tid).appendTo($wrap);
      }
    });
    setQuizPlannedTypeCounts($('#selection_mode').val() || 'auto');
    $('#questions_count').val(questions.length);
    return true;
  }

  function syncPaperFormFromSelection($wrap) {
    const questions = resolveSelectedQuestions();
    if (!questions.length) {
      alert('Select topics in step 1, then choose questions in step 2 before saving.');
      return false;
    }

    function add(name, val) {
      $('<input type="hidden">').attr('name', name).val(val).appendTo($wrap);
    }

    getTopicKeys().forEach(function (k) {
      add('topic_keys[]', k);
    });
    getSelectedBoardPublisherIds().forEach(function (id) {
      add('board_publisher_ids[]', String(id));
    });
    add('selection_mode', $('#selection_mode').val() || 'auto');

    QP_TYPE_KEYS.forEach(function (k) {
      add('count_' + k, $('#count_' + k).val() || '0');
      add('marks_' + k, $('#marks_' + k).val() || '0');
    });

    add('descriptive_choice_mode', $('#descriptive_choice_mode').val() || 'none');
    add('descriptive_attempt_any_count', $('#descriptive_attempt_any_count').val() || '0');
    add('descriptive_pairs_json', $('#descriptive_pairs_json').val() || '[]');

    questions.forEach(function (q) {
      add('question_ids[]', String(q.id));
    });
    add('fixed_questions', '1');

    return true;
  }

  window.QpBuilderApi = {
    resolveSelectedQuestions: resolveSelectedQuestions,
    syncQuizFormFromSelection: syncQuizFormFromSelection,
    updateQuizQuestionCountPreview: updateQuizQuestionCountPreview,
    syncPaperFormFromSelection: syncPaperFormFromSelection,
    reloadSummary: loadSummary,
    setBoardPublisherFilter: setBoardPublisherFilter,
  };

  const previousQpOnTopicsChanged = window.qpOnTopicsChanged;
  window.qpOnTopicsChanged = function () {
    if (typeof previousQpOnTopicsChanged === 'function') {
      previousQpOnTopicsChanged();
    }
    updateQuizQuestionCountPreview();
  };

  $(document).on('change input', '.qp-type-count, .qp-marks-input', updateQuizQuestionCountPreview);
  $(document).on('change', '.qp-manual-check', updateQuizQuestionCountPreview);
  $('#qpManualSelectAll, #qpManualClear').on('click', function () {
    setTimeout(updateQuizQuestionCountPreview, 0);
  });
  $('.qp-selection-tab').on('click', function () {
    setTimeout(updateQuizQuestionCountPreview, 0);
  });
})(window, jQuery);
