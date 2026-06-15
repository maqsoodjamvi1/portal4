/**
 * 3-column Question Bank browser for Question Paper Generator.
 */
(function (window, $) {
  'use strict';

  let qbTree = [];
  let topicMeta = {};
  let activeClassId = null;
  let activeSubjectId = null;
  let searchQuery = '';

  function escHtml(s) {
    return $('<div>').text(s ?? '').html();
  }

  function topicKey(t) {
    return String(t.class_id) + '|' + String(t.subject_id) + '|' + String(t.topic_id);
  }

  function buildTopicMeta(tree) {
    topicMeta = {};
    (tree || []).forEach(function (cls) {
      (cls.subjects || []).forEach(function (subj) {
        (subj.topics || []).forEach(function (t) {
          const k = topicKey(t);
          topicMeta[k] = {
            key: k,
            class_id: t.class_id,
            subject_id: t.subject_id,
            topic_id: t.topic_id,
            class_name: cls.class_name,
            subject_name: subj.subject_name,
            topic_name: t.topic_name,
            question_count: t.question_count || 0,
          };
        });
      });
    });
  }

  function matchesSearch(text) {
    if (!searchQuery) return true;
    return String(text || '').toLowerCase().indexOf(searchQuery) !== -1;
  }

  function filterTree(tree, q) {
    if (!q) return tree;
    const out = [];
    tree.forEach(function (cls) {
      const classMatch = matchesSearch(cls.class_name);
      const subjects = [];
      (cls.subjects || []).forEach(function (subj) {
        const subjMatch = matchesSearch(subj.subject_name);
        const topics = (subj.topics || []).filter(function (t) {
          return classMatch || subjMatch || matchesSearch(t.topic_name);
        });
        if (topics.length) {
          subjects.push(Object.assign({}, subj, { topics: topics }));
        }
      });
      if (subjects.length) {
        out.push(Object.assign({}, cls, { subjects: subjects }));
      }
    });
    return out;
  }

  function countSelectedInClass(cls) {
    let n = 0;
    (cls.subjects || []).forEach(function (s) {
      (s.topics || []).forEach(function (t) {
        if (window.qpSelectedKeys && window.qpSelectedKeys.has(topicKey(t))) n++;
      });
    });
    return n;
  }

  function countSelectedInSubject(subj) {
    let n = 0;
    (subj.topics || []).forEach(function (t) {
      if (window.qpSelectedKeys && window.qpSelectedKeys.has(topicKey(t))) n++;
    });
    return n;
  }

  function getActiveClass() {
    if (!activeClassId) return null;
    return qbTree.find(function (c) { return String(c.class_id) === String(activeClassId); }) || null;
  }

  function getActiveSubject() {
    const cls = getActiveClass();
    if (!cls || !activeSubjectId) return null;
    return (cls.subjects || []).find(function (s) {
      return String(s.subject_id) === String(activeSubjectId);
    }) || null;
  }

  function updateSelectedPanel() {
    const keys = window.qpSelectedKeys ? Array.from(window.qpSelectedKeys) : [];
    $('#qbSelectedCount').text(keys.length);
    if (!keys.length) {
      $('#qbSelectedPanel').addClass('d-none');
      $('#qbSelectedChips').empty();
      return;
    }
    $('#qbSelectedPanel').removeClass('d-none');
    let html = '';
    keys.forEach(function (k) {
      const m = topicMeta[k];
      const label = m ? m.class_name + ' › ' + m.subject_name + ' › ' + m.topic_name : k;
      html +=
        '<span class="badge badge-primary qb-chip mr-1 mb-1">' +
        escHtml(label) +
        ' <button type="button" class="qb-chip-remove" data-key="' +
        escHtml(k) +
        '">&times;</button></span>';
    });
    $('#qbSelectedChips').html(html);
  }

  function onTopicSelectionChanged() {
    updateSelectedPanel();
    renderClassList();
    renderSubjectList();
    renderTopicList();
    if (window.qpOnTopicsChanged) window.qpOnTopicsChanged();
  }

  function toggleTopic(k, on) {
    if (!window.qpSelectedKeys) return;
    k = String(k);
    if (on) window.qpSelectedKeys.add(k);
    else window.qpSelectedKeys.delete(k);
    onTopicSelectionChanged();
  }

  function renderClassList() {
    const filtered = filterTree(qbTree, searchQuery);
    $('#qbClassBadge').text(filtered.length);
    if (!filtered.length) {
      $('#qbClassList').html('<div class="qb-col-placeholder text-muted p-3 small">No classes</div>');
      return;
    }
    let html = '';
    filtered.forEach(function (cls) {
      const id = String(cls.class_id);
      const active = id === String(activeClassId) ? ' active' : '';
      const sel = countSelectedInClass(cls);
      html +=
        '<button type="button" class="qb-list-item' +
        active +
        '" data-class-id="' +
        escHtml(id) +
        '"><span class="qb-list-label">' +
        escHtml(cls.class_name) +
        '</span></button>';
    });
    $('#qbClassList').html(html);
  }

  function renderSubjectList() {
    const cls = getActiveClass();
    if (!cls) {
      $('#qbSubjectList').html('<div class="qb-col-placeholder text-muted p-3 small">Select a class</div>');
      return;
    }
    let subjects = cls.subjects || [];
    $('#qbSubjectBadge').text(subjects.length);
    let html = '';
    subjects.forEach(function (subj) {
      const id = String(subj.subject_id);
      const active = id === String(activeSubjectId) ? ' active' : '';
      html +=
        '<button type="button" class="qb-list-item' +
        active +
        '" data-subject-id="' +
        escHtml(id) +
        '">' +
        escHtml(subj.subject_name) +
        '</button>';
    });
    $('#qbSubjectList').html(html);
    $('#qbSelectAllTopicsInSubject').removeClass('d-none');
  }

  function renderTopicList() {
    const subj = getActiveSubject();
    if (!subj) {
      $('#qbTopicList').html('<div class="qb-col-placeholder text-muted p-3 small">Select a subject</div>');
      return;
    }
    let topics = subj.topics || [];
    $('#qbTopicBadge').text(topics.length);
    let html =
      '<div class="px-2 py-1 border-bottom bg-light small"><label class="mb-0"><input type="checkbox" id="qbTopicSelectAllVisible"> All shown</label></div>';
    topics.forEach(function (t) {
      const k = topicKey(t);
      const checked = window.qpSelectedKeys && window.qpSelectedKeys.has(k);
      html +=
        '<label class="qb-topic-row' +
        (checked ? ' selected' : '') +
        '"><input type="checkbox" class="qb-topic-check" data-key="' +
        escHtml(k) +
        '"' +
        (checked ? ' checked' : '') +
        '><span class="qb-topic-row-name">' +
        escHtml(t.topic_name) +
        '</span><span class="badge badge-info">' +
        (t.question_count || 0) +
        '</span></label>';
    });
    $('#qbTopicList').html(html);
  }

  function setActiveClass(classId) {
    activeClassId = classId;
    const cls = getActiveClass();
    if (cls && cls.subjects && cls.subjects.length) {
      activeSubjectId = cls.subjects[0].subject_id;
    }
    renderClassList();
    renderSubjectList();
    renderTopicList();
  }

  function setActiveSubject(subjectId) {
    activeSubjectId = subjectId;
    renderSubjectList();
    renderTopicList();
  }

  function bindEvents() {
    $('#qbClassList').on('click', '.qb-list-item', function () {
      setActiveClass($(this).data('class-id'));
    });
    $('#qbSubjectList').on('click', '.qb-list-item', function () {
      setActiveSubject($(this).data('subject-id'));
    });
    $('#qbTopicList').on('change', '.qb-topic-check', function () {
      toggleTopic($(this).data('key'), $(this).is(':checked'));
    });
    $('#qbTopicList').on('change', '#qbTopicSelectAllVisible', function () {
      const subj = getActiveSubject();
      if (!subj || !window.qpSelectedKeys) return;
      const on = $(this).is(':checked');
      (subj.topics || []).forEach(function (t) {
        const k = topicKey(t);
        if (on) window.qpSelectedKeys.add(k);
        else window.qpSelectedKeys.delete(k);
      });
      onTopicSelectionChanged();
    });
    $('#qbSelectAllTopicsInSubject').on('click', function () {
      const subj = getActiveSubject();
      if (!subj || !window.qpSelectedKeys) return;
      (subj.topics || []).forEach(function (t) {
        window.qpSelectedKeys.add(topicKey(t));
      });
      onTopicSelectionChanged();
    });
    $('#qbSelectedChips').on('click', '.qb-chip-remove', function (e) {
      e.preventDefault();
      toggleTopic($(this).data('key'), false);
    });
    $('#qbClearTopics').on('click', function () {
      if (window.qpSelectedKeys) window.qpSelectedKeys.clear();
      onTopicSelectionChanged();
    });
    $('#qbSearch').on('input', function () {
      searchQuery = String($(this).val() || '')
        .trim()
        .toLowerCase();
      renderClassList();
      renderSubjectList();
      renderTopicList();
    });
    $('#qbSearchClear').on('click', function () {
      $('#qbSearch').val('');
      searchQuery = '';
      renderClassList();
      renderSubjectList();
      renderTopicList();
    });
  }

  window.QuestionPaperQbBrowser = {
    render: function (tree) {
      qbTree = tree || [];
      buildTopicMeta(qbTree);
      if (!qbTree.length) return;
      activeClassId = qbTree[0].class_id;
      activeSubjectId =
        qbTree[0].subjects && qbTree[0].subjects[0] ? qbTree[0].subjects[0].subject_id : null;
      bindEvents();
      renderClassList();
      renderSubjectList();
      renderTopicList();
      updateSelectedPanel();
    },
    applySavedKeys: function (keys) {
      if (!keys || !keys.length || !window.qpSelectedKeys) return;
      keys.forEach(function (k) {
        window.qpSelectedKeys.add(String(k));
      });
      updateSelectedPanel();
      renderClassList();
      renderSubjectList();
      renderTopicList();
      if (window.qpOnTopicsChanged) window.qpOnTopicsChanged();
    },
  };
})(window, jQuery);
