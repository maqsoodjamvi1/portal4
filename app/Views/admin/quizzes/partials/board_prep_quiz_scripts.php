<script>
(function () {
  const gradeSel    = document.getElementById('prep_grade_level');
  const subjectSel  = document.getElementById('subject_id');
  const boardSel    = document.getElementById('prep_board_publisher_id');
  const classHidden = document.getElementById('bp_filter_class_id');
  const topicsSec   = document.getElementById('bpTopicsSection');
  const filtersHint = document.getElementById('bpFiltersHint');
  const summaryBox  = document.getElementById('bpSelectionSummary');
  const subjectsUrl = window.QP_CONFIG.boardPrepSubjectsUrl;

  function clearSubjects(label) {
    if (!subjectSel) return;
    subjectSel.innerHTML = '';
    const opt = document.createElement('option');
    opt.value = '';
    opt.textContent = label || 'Select subject';
    subjectSel.appendChild(opt);
  }

  function clearTopicSelection() {
    if (window.QuestionPaperQbBrowser && typeof window.QuestionPaperQbBrowser.clearSelection === 'function') {
      window.QuestionPaperQbBrowser.clearSelection();
    } else if (window.qpSelectedKeys) {
      window.qpSelectedKeys.clear();
    }
    if (window.qpReloadPool) {
      window.qpReloadPool();
    }
  }

  function filtersReady() {
    const board = boardSel ? parseInt(boardSel.value || '0', 10) : 0;
    const grade = gradeSel ? (gradeSel.value || '') : '';
    const subj  = subjectSel ? parseInt(subjectSel.value || '0', 10) : 0;
    return board > 0 && grade !== '' && subj > 0;
  }

  function updateFiltersUi() {
    const ready = filtersReady();
    if (topicsSec) {
      topicsSec.classList.toggle('bp-topics-locked', !ready);
      topicsSec.classList.toggle('bp-topics-ready', ready);
    }
    if (filtersHint) {
      filtersHint.classList.toggle('d-none', ready);
    }
    updateSelectionSummary();
  }

  function updateSelectionSummary() {
    if (!summaryBox) return;
    if (!filtersReady()) {
      summaryBox.textContent = 'Select board, grade & subject above, then pick topics.';
      return;
    }
    const boardText = boardSel && boardSel.selectedIndex >= 0
      ? boardSel.options[boardSel.selectedIndex].text : '';
    const gradeText = gradeSel && gradeSel.selectedIndex >= 0
      ? gradeSel.options[gradeSel.selectedIndex].text : '';
    const subjText = subjectSel && subjectSel.selectedIndex >= 0
      ? subjectSel.options[subjectSel.selectedIndex].text : '';
    const topicN = window.qpSelectedKeys ? window.qpSelectedKeys.size : 0;
    summaryBox.textContent = boardText + ' · ' + gradeText + ' · ' + subjText
      + (topicN > 0 ? ' · ' + topicN + ' topic' + (topicN === 1 ? '' : 's') : '');
  }

  function applyBoardFilter() {
    const boardId = boardSel ? parseInt(boardSel.value || '0', 10) : 0;
    if (window.QpBuilderApi && typeof window.QpBuilderApi.setBoardPublisherFilter === 'function') {
      window.QpBuilderApi.setBoardPublisherFilter(boardId > 0 ? [boardId] : []);
    }
  }

  function reloadTopics() {
    if (!filtersReady()) {
      updateFiltersUi();
      return;
    }
    applyBoardFilter();
    if (window.QpBuilderApi && typeof window.QpBuilderApi.reloadSummary === 'function') {
      window.QpBuilderApi.reloadSummary();
    }
    updateFiltersUi();
  }

  function onFilterChange(clearTopics) {
    if (clearTopics) {
      clearTopicSelection();
    }
    updateFiltersUi();
    reloadTopics();
  }

  function loadSubjects(grade, preselect) {
    if (!subjectSel || !grade) {
      clearSubjects('Select grade first');
      if (classHidden) classHidden.value = '0';
      subjectSel && (subjectSel.disabled = true);
      onFilterChange(true);
      return;
    }

    subjectSel.disabled = true;
    clearSubjects('Loading…');

    fetch(subjectsUrl + '?grade_level=' + encodeURIComponent(grade), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        clearSubjects('Select subject');
        if (!res || !res.ok) {
          clearSubjects(res && res.msg ? res.msg : 'No subjects');
          onFilterChange(true);
          return;
        }

        if (classHidden) {
          classHidden.value = String(res.class_id || 0);
        }

        (res.data || []).forEach(function (row) {
          const opt = document.createElement('option');
          const sid = row.subject_id;
          opt.value = sid;
          opt.textContent = row.subject_name || row.subject_short_name || ('Subject ' + sid);
          if (preselect && String(preselect) === String(sid)) {
            opt.selected = true;
          }
          subjectSel.appendChild(opt);
        });

        if (!(res.data || []).length) {
          clearSubjects('No subjects for this grade');
        }
        onFilterChange(true);
      })
      .catch(function () {
        clearSubjects('Error loading subjects');
        onFilterChange(true);
      })
      .finally(function () {
        subjectSel.disabled = false;
      });
  }

  if (gradeSel) {
    gradeSel.addEventListener('change', function () {
      loadSubjects(this.value, '');
    });
  }

  if (subjectSel) {
    subjectSel.addEventListener('change', function () {
      onFilterChange(true);
    });
  }

  if (boardSel) {
    boardSel.addEventListener('change', function () {
      onFilterChange(true);
    });
  }

  const prevOnTopicsChanged = window.qpOnTopicsChanged;
  window.qpOnTopicsChanged = function () {
    if (typeof prevOnTopicsChanged === 'function') {
      prevOnTopicsChanged();
    }
    updateSelectionSummary();
  };

  <?php
    $initGrade = old('prep_grade_level', '');
    $initSub   = (int) old('subject_id', 0);
    if ($initGrade !== ''):
  ?>
  loadSubjects(<?= json_encode($initGrade) ?>, <?= json_encode($initSub > 0 ? (string) $initSub : '') ?>);
  <?php else: ?>
  updateFiltersUi();
  <?php endif; ?>
})();
</script>
