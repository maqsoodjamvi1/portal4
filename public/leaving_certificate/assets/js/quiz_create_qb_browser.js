/**
 * 3-column Question Bank browser for quiz create form.
 * Expects jQuery and globals: selectedKeys (Set), syncTopicKeysHidden, reloadQuestionsFromSelected, resetQuestionBank
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
            question_count: t.question_count || 0
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
        if (window.quizQbSelectedKeys && window.quizQbSelectedKeys.has(topicKey(t))) n++;
      });
    });
    return n;
  }

  function countSelectedInSubject(subj) {
    let n = 0;
    (subj.topics || []).forEach(function (t) {
      if (window.quizQbSelectedKeys && window.quizQbSelectedKeys.has(topicKey(t))) n++;
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
    const keys = window.quizQbSelectedKeys ? Array.from(window.quizQbSelectedKeys) : [];
    const $panel = $('#qbSelectedPanel');
    const $chips = $('#qbSelectedChips');
    $('#qbSelectedCount').text(keys.length);

    if (!keys.length) {
      $panel.addClass('d-none');
      $chips.empty();
      return;
    }
    $panel.removeClass('d-none');
    let html = '';
    keys.forEach(function (k) {
      const m = topicMeta[k];
      const label = m
        ? (m.class_name + ' › ' + m.subject_name + ' › ' + m.topic_name)
        : k;
      html += '<span class="badge badge-primary qb-chip mr-1 mb-1" data-key="' + escHtml(k) + '">' +
        escHtml(label) +
        ' <button type="button" class="qb-chip-remove" data-key="' + escHtml(k) + '" aria-label="Remove">&times;</button></span>';
    });
    $chips.html(html);
  }

  function onTopicSelectionChanged() {
    if (window.quizQbSyncTopicKeys) window.quizQbSyncTopicKeys();
    updateSelectedPanel();
    renderClassList();
    renderSubjectList();
    renderTopicList();
    if (!window.quizQbSelectedKeys || !window.quizQbSelectedKeys.size) {
      if (window.quizQbResetQuestionBank) window.quizQbResetQuestionBank('Select one or more topics to load questions.');
    } else if (window.quizQbReloadQuestions) {
      window.quizQbReloadQuestions();
    }
  }

  function toggleTopic(k, on) {
    if (!window.quizQbSelectedKeys) return;
    k = String(k);
    if (on) window.quizQbSelectedKeys.add(k);
    else window.quizQbSelectedKeys.delete(k);
    onTopicSelectionChanged();
  }

  function renderClassList() {
    const $list = $('#qbClassList');
    const filtered = filterTree(qbTree, searchQuery);
    $('#qbClassBadge').text(filtered.length);

    if (!filtered.length) {
      $list.html('<div class="qb-col-placeholder text-muted p-3 small">No classes match search</div>');
      return;
    }

    let html = '';
    filtered.forEach(function (cls) {
      const id = String(cls.class_id);
      const active = id === String(activeClassId) ? ' active' : '';
      const sel = countSelectedInClass(cls);
      const topicTotal = (cls.subjects || []).reduce(function (sum, s) {
        return sum + (s.topics ? s.topics.length : 0);
      }, 0);
      html += '<button type="button" class="qb-list-item' + active + '" data-class-id="' + escHtml(id) + '">' +
        '<span class="qb-list-label">' + escHtml(cls.class_name) + '</span>' +
        '<span class="qb-list-meta">' +
        (sel ? '<span class="badge badge-primary">' + sel + ' sel</span> ' : '') +
        '<span class="text-muted small">' + topicTotal + ' topics</span>' +
        '</span></button>';
    });
    $list.html(html);
  }

  function renderSubjectList() {
    const $list = $('#qbSubjectList');
    const cls = getActiveClass();
    if (!cls) {
      $list.html('<div class="qb-col-placeholder text-muted p-3 small">Select a class</div>');
      $('#qbSubjectBadge').text('0');
      $('#qbSelectAllTopicsInSubject').addClass('d-none');
      return;
    }

    let subjects = cls.subjects || [];
    if (searchQuery) {
      const filtered = filterTree([cls], searchQuery);
      subjects = filtered.length ? filtered[0].subjects : [];
    }
    $('#qbSubjectBadge').text(subjects.length);

    if (!subjects.length) {
      $list.html('<div class="qb-col-placeholder text-muted p-3 small">No subjects</div>');
      return;
    }

    let html = '';
    subjects.forEach(function (subj) {
      const id = String(subj.subject_id);
      const active = id === String(activeSubjectId) ? ' active' : '';
      const sel = countSelectedInSubject(subj);
      html += '<button type="button" class="qb-list-item' + active + '" data-subject-id="' + escHtml(id) + '">' +
        '<span class="qb-list-label">' + escHtml(subj.subject_name) + '</span>' +
        '<span class="qb-list-meta">' +
        (sel ? '<span class="badge badge-primary">' + sel + ' sel</span> ' : '') +
        '<span class="text-muted small">' + (subj.topics ? subj.topics.length : 0) + ' topics</span>' +
        '</span></button>';
    });
    $list.html(html);
    $('#qbSelectAllTopicsInSubject').removeClass('d-none');
  }

  function renderTopicList() {
    const $list = $('#qbTopicList');
    const subj = getActiveSubject();
    if (!subj) {
      $list.html('<div class="qb-col-placeholder text-muted p-3 small">Select a subject</div>');
      $('#qbTopicBadge').text('0');
      return;
    }

    let topics = subj.topics || [];
    if (searchQuery) {
      topics = topics.filter(function (t) {
        return matchesSearch(t.topic_name);
      });
    }
    $('#qbTopicBadge').text(topics.length);

    if (!topics.length) {
      $list.html('<div class="qb-col-placeholder text-muted p-3 small">No topics match</div>');
      return;
    }

    let html = '<div class="qb-topic-toolbar px-2 py-1 border-bottom bg-light small">' +
      '<label class="mb-0"><input type="checkbox" id="qbTopicSelectAllVisible"> Select all shown</label></div>';
    topics.forEach(function (t) {
      const k = topicKey(t);
      const checked = window.quizQbSelectedKeys && window.quizQbSelectedKeys.has(k);
      html += '<label class="qb-topic-row' + (checked ? ' selected' : '') + '">' +
        '<input type="checkbox" class="qb-topic-check" data-key="' + escHtml(k) + '"' + (checked ? ' checked' : '') + '>' +
        '<span class="qb-topic-row-name" title="' + escHtml(t.topic_name) + '">' + escHtml(t.topic_name) + '</span>' +
        '<span class="badge badge-info">' + (t.question_count || 0) + '</span>' +
        '</label>';
    });
    $list.html(html);

    const allChecked = topics.length && topics.every(function (t) {
      return window.quizQbSelectedKeys && window.quizQbSelectedKeys.has(topicKey(t));
    });
    $('#qbTopicSelectAllVisible').prop('checked', allChecked);
  }

  function setActiveClass(classId) {
    activeClassId = classId;
    const cls = getActiveClass();
    if (cls && cls.subjects && cls.subjects.length) {
      if (!activeSubjectId || !(cls.subjects || []).some(function (s) {
        return String(s.subject_id) === String(activeSubjectId);
      })) {
        activeSubjectId = cls.subjects[0].subject_id;
      }
    } else {
      activeSubjectId = null;
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
    $('#qbClassList').off('click.qbb').on('click.qbb', '.qb-list-item', function () {
      setActiveClass($(this).data('class-id'));
    });

    $('#qbSubjectList').off('click.qbb').on('click.qbb', '.qb-list-item', function () {
      setActiveSubject($(this).data('subject-id'));
    });

    $('#qbTopicList').off('change.qbb', '.qb-topic-check').on('change.qbb', '.qb-topic-check', function () {
      const k = $(this).data('key');
      toggleTopic(k, $(this).is(':checked'));
    });

    $('#qbTopicList').off('change.qbb', '#qbTopicSelectAllVisible').on('change.qbb', '#qbTopicSelectAllVisible', function () {
      const subj = getActiveSubject();
      if (!subj || !window.quizQbSelectedKeys) return;
      const on = $(this).is(':checked');
      (subj.topics || []).forEach(function (t) {
        if (!searchQuery || matchesSearch(t.topic_name)) {
          const k = topicKey(t);
          if (on) window.quizQbSelectedKeys.add(k);
          else window.quizQbSelectedKeys.delete(k);
        }
      });
      onTopicSelectionChanged();
    });

    $('#qbSelectAllTopicsInSubject').off('click.qbb').on('click.qbb', function () {
      const subj = getActiveSubject();
      if (!subj || !window.quizQbSelectedKeys) return;
      (subj.topics || []).forEach(function (t) {
        window.quizQbSelectedKeys.add(topicKey(t));
      });
      onTopicSelectionChanged();
    });

    $('#qbSelectedChips').off('click.qbb', '.qb-chip-remove').on('click.qbb', '.qb-chip-remove', function (e) {
      e.preventDefault();
      toggleTopic($(this).data('key'), false);
    });

    $('#qbClearTopics').off('click.qbb').on('click.qbb', function () {
      if (window.quizQbSelectedKeys) window.quizQbSelectedKeys.clear();
      onTopicSelectionChanged();
    });

    $('#qbSearch').off('input.qbb').on('input.qbb', function () {
      searchQuery = String($(this).val() || '').trim().toLowerCase();
      const filtered = filterTree(qbTree, searchQuery);
      if (filtered.length && (!activeClassId || !filtered.some(function (c) {
        return String(c.class_id) === String(activeClassId);
      }))) {
        activeClassId = filtered[0].class_id;
        activeSubjectId = filtered[0].subjects && filtered[0].subjects[0]
          ? filtered[0].subjects[0].subject_id : null;
      }
      renderClassList();
      renderSubjectList();
      renderTopicList();
    });

    $('#qbSearchClear').off('click.qbb').on('click.qbb', function () {
      $('#qbSearch').val('');
      searchQuery = '';
      renderClassList();
      renderSubjectList();
      renderTopicList();
    });
  }

  window.QuizQbBrowser = {
    render: function (tree) {
      qbTree = tree || [];
      buildTopicMeta(qbTree);
      searchQuery = '';
      $('#qbSearch').val('');

      if (!qbTree.length) {
        $('#qbClassList').html('<div class="qb-col-placeholder text-muted p-3 small">No records</div>');
        $('#qbSubjectList').html('<div class="qb-col-placeholder text-muted p-3 small">—</div>');
        $('#qbTopicList').html('<div class="qb-col-placeholder text-muted p-3 small">—</div>');
        return;
      }

      activeClassId = qbTree[0].class_id;
      activeSubjectId = qbTree[0].subjects && qbTree[0].subjects[0]
        ? qbTree[0].subjects[0].subject_id : null;

      bindEvents();
      renderClassList();
      renderSubjectList();
      renderTopicList();
      updateSelectedPanel();
    },
    applySavedKeys: function (keys) {
      if (!keys || !keys.length || !window.quizQbSelectedKeys) return;
      keys.forEach(function (k) {
        window.quizQbSelectedKeys.add(String(k));
      });
      const first = keys[0].split('|');
      if (first.length >= 2) {
        activeClassId = first[0];
        activeSubjectId = first[1];
      }
      updateSelectedPanel();
      renderClassList();
      renderSubjectList();
      renderTopicList();
      if (window.quizQbReloadQuestions) window.quizQbReloadQuestions();
    }
  };
})(window, jQuery);
