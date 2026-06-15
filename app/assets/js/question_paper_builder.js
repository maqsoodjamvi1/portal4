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
    const cnt = typeCount(k);
    if (cnt <= 0) {
      return false;
    }
    if (k === 'mcq_multi' && typeCount('mcq') > 0) {
      return false;
    }
    return true;
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
    $('#qpTotalMarksDisplay').text(total > 0 ? String(total) : '0');
    $('#total_marks_display').text(total > 0 ? String(total) : '0');
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
        '<span class="small text-muted">Pair ' + (idx + 1) + '</span>' +
        '<select class="form-control form-control-sm qp-pair-a" style="width:auto">' + opts + '</select>' +
        '<span class="qp-pair-or-label">OR</span>' +
        '<select class="form-control form-control-sm qp-pair-b" style="width:auto">' + opts + '</select>' +
        '<button type="button" class="btn btn-sm btn-outline-danger qp-pair-remove" title="Remove pair">&times;</button>' +
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

  function renderCounts(counts) {
    const $box = $('#qpTypeCounts');
    if (!counts || !counts.total) {
      $box.html('<span class="text-muted small">Select topics to see available counts.</span>');
      return;
    }
    const parts = [];
    const map = [
      ['mcq', 'MCQ'], ['mcq_multi', 'MCQ multi'], ['tf', 'T/F'], ['fill', 'Fill'],
      ['short', 'Short'], ['descriptive', 'Descriptive'], ['match', 'Match'],
    ];
    map.forEach(function (pair) {
      const n = counts[pair[0]] || 0;
      if (n > 0) parts.push(pair[1] + ': ' + n);
    });
    parts.unshift('Total: ' + counts.total);
    $box.html(parts.join(' · '));
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
        '<input type="checkbox" class="qp-manual-check mr-2" data-id="' +
        id +
        '"' +
        (checked ? ' checked' : '') +
        '> <span class="badge badge-light">' +
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
    $('#qpTypeCounts').html('<span class="text-danger small">' + $('<div>').text(msg || 'Failed to load question bank').html() + '</span>');
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
  }

  function loadSummary() {
    const primary = cfg.summaryUrl;
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
    const topicKeys = [];
    if (c.filters && c.filters.topic_ids && c.filters.class_ids) {
      // rebuild keys from template if stored in config
    }
    if (data.topic_keys && data.topic_keys.length) {
      window.qpSelectedKeys.clear();
      window.QuestionPaperQbBrowser.applySavedKeys(data.topic_keys);
    } else if (c.topic_keys && c.topic_keys.length) {
      window.qpSelectedKeys.clear();
      window.QuestionPaperQbBrowser.applySavedKeys(c.topic_keys);
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
    loadSummary();
    loadTemplates();
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
  });
})(window, jQuery);
