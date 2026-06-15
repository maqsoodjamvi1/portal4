(function (window, $) {
  'use strict';

  var classRows = [], activeClassId = null, selectedKeys = new Set();
  window.quizQbSelectedKeys = selectedKeys;

  function esc(s) { return $('<div>').text(s ?? '').html(); }
  function topicKey(t) { return String(t.class_id) + '|' + String(t.subject_id) + '|' + String(t.topic_id); }

  function boardId() { return String($('#prep_board_publisher_id').val() || ''); }
  function subjectId() { return String($('#quiz_subject_id').val() || ''); }

  function syncHidden() {
    $('#qb_class_id').val(activeClassId || '');
    $('#topic_keys_json').val(JSON.stringify(Array.from(selectedKeys)));
  }

  function resetQuestions(msg) {
    $('#qbTable tbody').empty();
    $('#qbTableWrap').addClass('d-none');
    $('#qbEmptyHint').text(msg || 'Select topics to load questions.').removeClass('d-none');
    $('#qbCheckMaster').prop('checked', false);
  }

  function recalcTotal() {
    var sum = 0;
    $('.qb-type-count').each(function () {
      var v = parseInt($(this).val(), 10);
      if (!isNaN(v) && v > 0) sum += v;
    });
    $('#questions_count').val(sum);
  }

  function clearTopics() {
    selectedKeys.clear();
    $('#qbSelectedPanel').addClass('d-none');
    $('#qbSelectedChips').empty();
    $('#qbSelectedCount').text('0');
    syncHidden();
    renderTopics();
    resetQuestions('Select class and topics to filter questions.');
  }

  function buildClassRows(flat) {
    var map = {};
    (flat || []).forEach(function (r) {
      var cid = String(r.class_id);
      if (!map[cid]) {
        map[cid] = { class_id: r.class_id, class_name: r.class_name, topics: [] };
      }
      map[cid].topics.push({
        class_id: r.class_id,
        subject_id: r.subject_id,
        topic_id: r.topic_id,
        topic_name: r.topic_name,
        question_count: parseInt(r.question_count || 0, 10)
      });
    });
    return Object.values(map);
  }

  function getActiveClassRow() {
    return classRows.find(function (c) { return String(c.class_id) === String(activeClassId); }) || null;
  }

  function renderClasses() {
    if (!boardId() || !subjectId()) {
      $('#qbClassList').html('<div class="p-3 small text-muted">Select board &amp; quiz subject above</div>');
      $('#qbClassBadge').text('0');
      return;
    }
    if (!classRows.length) {
      $('#qbClassList').html('<div class="p-3 small text-muted">No classes for this board/subject</div>');
      $('#qbClassBadge').text('0');
      return;
    }
    var html = '';
    classRows.forEach(function (cls) {
      var id = String(cls.class_id);
      var n = (cls.topics || []).length;
      html += '<button type="button" class="qb-list-item' + (id === String(activeClassId) ? ' active' : '') +
        '" data-class-id="' + esc(id) + '"><span>' + esc(cls.class_name) + '</span><span class="badge text-bg-light">' + n + '</span></button>';
    });
    $('#qbClassList').html(html);
    $('#qbClassBadge').text(classRows.length);
  }

  function renderTopics() {
    var cls = getActiveClassRow();
    if (!cls) {
      $('#qbTopicList').html('<div class="p-3 small text-muted">Select a class</div>');
      $('#qbTopicBadge').text('0');
      $('#qbSelectAllTopicsVisible').addClass('d-none');
      return;
    }
    var q = String($('#qbSearch').val() || '').trim().toLowerCase();
    var topics = (cls.topics || []).filter(function (t) {
      return !q || String(t.topic_name || '').toLowerCase().indexOf(q) !== -1;
    });
    $('#qbTopicBadge').text(topics.length);
    if (!topics.length) {
      $('#qbTopicList').html('<div class="p-3 small text-muted">No topics</div>');
      return;
    }
    var html = '';
    topics.forEach(function (t) {
      var k = topicKey(t), on = selectedKeys.has(k);
      html += '<label class="qb-topic-row' + (on ? ' selected' : '') + '"><input type="checkbox" class="qb-topic-check" data-key="' +
        esc(k) + '"' + (on ? ' checked' : '') + '><span class="flex-grow-1">' + esc(t.topic_name) + '</span>' +
        '<span class="badge text-bg-info">' + (t.question_count || 0) + '</span></label>';
    });
    $('#qbTopicList').html(html);
    $('#qbSelectAllTopicsVisible').removeClass('d-none');
    updateChips();
  }

  function updateChips() {
    var keys = Array.from(selectedKeys);
    $('#qbSelectedCount').text(keys.length);
    if (!keys.length) {
      $('#qbSelectedPanel').addClass('d-none');
      $('#qbSelectedChips').empty();
      return;
    }
    $('#qbSelectedPanel').removeClass('d-none');
    var html = '';
    keys.forEach(function (k) {
      html += '<span class="badge text-bg-primary me-1 mb-1">' + esc(k.split('|').pop()) +
        ' <button type="button" class="close text-white qb-chip-x" data-key="' + esc(k) + '" style="font-size:1rem">&times;</button></span>';
    });
    $('#qbSelectedChips').html(html);
  }

  function reloadQuestions() {
    if (!selectedKeys.size) {
      resetQuestions('Tick topics to load matching questions.');
      return;
    }
    var topicIds = [], classIds = [];
    selectedKeys.forEach(function (k) {
      var p = String(k).split('|');
      classIds.push(p[0]);
      topicIds.push(p[2]);
    });
    var types = [];
    if (+($('#count_mcq_single').val() || 0) > 0) types.push('mcq', 'mcq_single');
    if (+($('#count_mcq_multi').val() || 0) > 0) types.push('mcq_multi');
    if (+($('#count_tf').val() || 0) > 0) types.push('tf', 'true_false');
    if (+($('#count_fill').val() || 0) > 0) types.push('fill', 'fill_blank');
    if (+($('#count_short').val() || 0) > 0) types.push('short', 'short_answer');
    if (+($('#count_match').val() || 0) > 0) types.push('match');
    if (!types.length) {
      resetQuestions('Set question type counts first.');
      return;
    }
    $.ajax({
      url: (window.__quizCreateBaseUrl || '/') + 'admin/quizzes/ajax/qb-questions',
      method: 'POST',
      dataType: 'json',
      data: {
        class_ids: classIds,
        subject_ids: [subjectId()],
        topic_ids: topicIds,
        question_types: types,
        board_publisher_id: boardId()
      },
      success: function (res) {
        if (!res || !res.ok || !res.data || !res.data.length) {
          resetQuestions('No questions match your filters.');
          return;
        }
        var body = '';
        res.data.forEach(function (q, i) {
          var txt = (q.question || '').substr(0, 200);
          body += '<tr><td><input type="checkbox" class="qb-check" name="question_ids[]" value="' + q.id + '"></td><td>' +
            (i + 1) + '</td><td>' + q.id + '</td><td><span class="badge text-bg-info">' + esc(q.question_type) + '</span></td><td>' + esc(txt) + '</td></tr>';
        });
        $('#qbTable tbody').html(body);
        $('#qbTableWrap').removeClass('d-none');
        $('#qbEmptyHint').addClass('d-none');
      },
      error: function () { resetQuestions('Error loading questions.'); }
    });
  }

  function loadSummary() {
    if (!boardId()) {
      classRows = [];
      activeClassId = null;
      renderClasses();
      renderTopics();
      resetQuestions('Select a board first.');
      return;
    }
    if (!subjectId()) {
      classRows = [];
      activeClassId = null;
      renderClasses();
      renderTopics();
      resetQuestions('Select a quiz subject.');
      return;
    }
    var url = (window.__quizCreateBaseUrl || '/') + 'admin/quizzes/ajax/qb-summary' +
      '?board_publisher_id=' + encodeURIComponent(boardId()) +
      '&subject_id=' + encodeURIComponent(subjectId());
    $.getJSON(url).done(function (res) {
      if (!res || !res.ok) {
        classRows = [];
        renderClasses();
        renderTopics();
        resetQuestions('Could not load question bank.');
        return;
      }
      classRows = buildClassRows(res.data || []);
      var saved = window.__quizCreateQbFilter || {};
      activeClassId = saved.class_id && classRows.some(function (c) { return String(c.class_id) === String(saved.class_id); })
        ? saved.class_id : (classRows[0] ? classRows[0].class_id : null);
      renderClasses();
      renderTopics();
      syncHidden();
      $('#qbEmptyHint').text('Select topics to filter which questions appear below.').removeClass('d-none');
    });
  }

  function loadSubjects(preselect) {
    var $sel = $('#quiz_subject_id');
    if (!boardId()) {
      $sel.prop('disabled', true).html('<option value="">Select board first</option>');
      return $.Deferred().resolve().promise();
    }
    $sel.prop('disabled', true).html('<option value="">Loading subjects…</option>');
    return $.getJSON((window.__quizCreateBaseUrl || '/') + 'admin/quizzes/ajax/board-prep-subjects', {
      board_publisher_id: boardId()
    }).done(function (res) {
      if (!res || !res.ok || !res.data || !res.data.length) {
        $sel.html('<option value="">No subjects for this board</option>');
        return;
      }
      var html = '<option value="">— Select subject —</option>';
      res.data.forEach(function (s) {
        html += '<option value="' + s.subject_id + '">' + esc(s.subject_name) + '</option>';
      });
      $sel.html(html).prop('disabled', false);
      if (preselect) {
        $sel.val(String(preselect));
      }
    });
  }

  function onBoardOrSubjectChange() {
    clearTopics();
    loadSummary();
  }

  $(function () {
    recalcTotal();

    $('#prep_board_publisher_id').on('change', function () {
      var pre = (window.__quizCreateQbFilter || {}).subject_id;
      loadSubjects(pre).always(function () {
        window.__quizCreateQbFilter = window.__quizCreateQbFilter || {};
        window.__quizCreateQbFilter.subject_id = null;
        onBoardOrSubjectChange();
      });
    });

    $('#quiz_subject_id').on('change', function () {
      onBoardOrSubjectChange();
    });

    $('#qbClassList').on('click', '.qb-list-item', function () {
      activeClassId = $(this).data('class-id');
      clearTopics();
      renderClasses();
      renderTopics();
      syncHidden();
    });

    $('#qbTopicList').on('change', '.qb-topic-check', function () {
      var k = String($(this).data('key'));
      if ($(this).is(':checked')) selectedKeys.add(k); else selectedKeys.delete(k);
      syncHidden();
      renderTopics();
      reloadQuestions();
    });

    $('#qbSelectedChips').on('click', '.qb-chip-x', function () {
      selectedKeys.delete(String($(this).data('key')));
      syncHidden();
      renderTopics();
      reloadQuestions();
    });

    $('#qbClearTopics').on('click', clearTopics);

    $('#qbSelectAllTopicsVisible').on('click', function () {
      var cls = getActiveClassRow();
      if (!cls) return;
      (cls.topics || []).forEach(function (t) { selectedKeys.add(topicKey(t)); });
      syncHidden();
      renderTopics();
      reloadQuestions();
    });

    $('#qbSearch').on('input', renderTopics);
    $('#qbSearchClear').on('click', function () { $('#qbSearch').val(''); renderTopics(); });

    $('#qbCheckMaster').on('change', function () { $('.qb-check').prop('checked', $(this).is(':checked')); });
    $('#qbSelectAll').on('click', function () { $('.qb-check').prop('checked', true); $('#qbCheckMaster').prop('checked', true); });
    $('#qbClearAll').on('click', function () { $('.qb-check').prop('checked', false); $('#qbCheckMaster').prop('checked', false); });

    $('.qb-type-count').on('input change', function () {
      recalcTotal();
      if (selectedKeys.size) reloadQuestions();
    });

    $('#quizCreateForm').on('submit', syncHidden);

    var saved = window.__quizCreateQbFilter || {};
    if (saved.board_publisher_id) {
      $('#prep_board_publisher_id').val(String(saved.board_publisher_id));
      loadSubjects(saved.subject_id).always(function () {
        loadSummary();
      });
    }
  });
})(window, jQuery);
