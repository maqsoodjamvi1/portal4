(function () {
  'use strict';

  const CFG = window.BP_BULK_CONFIG || {};
  const TYPE_KEYS = ['mcq', 'mcq_multi', 'tf', 'fill', 'short', 'descriptive', 'match'];
  const TYPE_LABELS = {
    mcq: 'MCQ',
    mcq_multi: 'Multi MCQ',
    tf: 'True / False',
    fill: 'Fill in blank',
    short: 'Short answer',
    descriptive: 'Descriptive',
    match: 'Match',
  };

  const classSel = document.getElementById('bp_class_id');
  const gradeHidden = document.getElementById('prep_grade_level');
  const subjectSel = document.getElementById('subject_id');
  const boardSel = document.getElementById('prep_board_publisher_id');
  const classHidden = document.getElementById('bp_filter_class_id');
  const wizard = document.getElementById('bpBulkWizard');
  const chaptersHead = document.getElementById('bpChaptersHead');
  const chaptersBody = document.getElementById('bpChaptersBody');
  const chaptersLoading = document.getElementById('bpChaptersLoading');
  const chaptersEmpty = document.getElementById('bpChaptersEmpty');
  const chaptersTableWrap = document.getElementById('bpChaptersTableWrap');
  const typeCountsBody = document.getElementById('bpTypeCountsBody');
  const previewBody = document.getElementById('bpPreviewBody');
  const previewSummary = document.getElementById('bpPreviewSummary');
  const titlePattern = document.getElementById('bp_title_pattern');
  const groupsJsonInput = document.getElementById('bp_groups_json');
  const submitBtn = document.getElementById('bpBulkSubmitBtn');
  const submitLabel = document.getElementById('bpBulkSubmitLabel');
  const settingsSummary = document.getElementById('bpSettingsSummary');
  const bulkForm = document.getElementById('bpBulkForm');

  let topics = [];
  let subjectName = '';
  let subjectShort = '';
  let classId = 0;

  function clearSubjects(label) {
    if (!subjectSel) return;
    subjectSel.innerHTML = '';
    const opt = document.createElement('option');
    opt.value = '';
    opt.textContent = label || 'Select subject';
    subjectSel.appendChild(opt);
  }

  function selectedClassId() {
    const fromSelect = classSel ? parseInt(classSel.value || '0', 10) : 0;
    if (fromSelect > 0) return fromSelect;
    return classHidden ? parseInt(classHidden.value || '0', 10) : 0;
  }

  function filtersReady() {
    const board = boardSel ? parseInt(boardSel.value || '0', 10) : 0;
    const cls = selectedClassId();
    const subj = subjectSel ? parseInt(subjectSel.value || '0', 10) : 0;
    return board > 0 && cls > 0 && subj > 0;
  }

  function getGroupingMode() {
    const checked = document.querySelector('#bpGroupingMode input[name="grouping_mode"]:checked');
    return checked ? checked.value : 'per_chapter';
  }

  function selectedTopics() {
    if (!chaptersBody) return [];
    const ids = [];
    chaptersBody.querySelectorAll('.bp-chapter-cb:checked').forEach(function (cb) {
      const id = parseInt(cb.value || '0', 10);
      const topic = topics.find(function (t) { return t.id === id; });
      if (topic) ids.push(topic);
    });
    return ids;
  }

  function sumCountsForTopics(topicList) {
    const sum = {};
    TYPE_KEYS.forEach(function (k) { sum[k] = 0; });
    topicList.forEach(function (t) {
      const c = t.counts || {};
      TYPE_KEYS.forEach(function (k) {
        sum[k] += parseInt(c[k] || 0, 10);
      });
    });
    return sum;
  }

  function buildGroups(selected, mode) {
    if (!selected.length) return [];

    if (mode === 'all_one') {
      return [{
        topic_ids: selected.map(function (t) { return t.id; }),
        chapter_names: selected.map(function (t) { return t.topic_name; }),
        chapters_label: selected.map(function (t) { return shortChapterLabel(t); }).join(' + '),
      }];
    }

    if (mode === 'pairs') {
      const groups = [];
      for (let i = 0; i < selected.length; i += 2) {
        const chunk = selected.slice(i, i + 2);
        groups.push({
          topic_ids: chunk.map(function (t) { return t.id; }),
          chapter_names: chunk.map(function (t) { return t.topic_name; }),
          chapters_label: chunk.map(function (t) { return shortChapterLabel(t); }).join(' + '),
        });
      }
      return groups;
    }

    return selected.map(function (t) {
      return {
        topic_ids: [t.id],
        chapter_names: [t.topic_name],
        chapters_label: shortChapterLabel(t),
      };
    });
  }

  function shortChapterLabel(topic) {
    const name = (topic.topic_name || '').trim();
    if (name.length <= 40) return name;
    return 'Ch ' + (topic.chapter_no || '');
  }

  function displaySubjectName() {
    return subjectShort || subjectName || 'Subject';
  }

  function applyTitlePattern(pattern, chaptersLabel, firstChapter) {
    return (pattern || '{subject} – {chapters}')
      .replace(/\{subject\}/g, displaySubjectName())
      .replace(/\{chapters\}/g, chaptersLabel)
      .replace(/\{chapter\}/g, firstChapter || chaptersLabel);
  }

  function getPickCounts() {
    const counts = {};
    TYPE_KEYS.forEach(function (k) {
      const inp = document.getElementById('bp_pick_' + k);
      counts[k] = inp ? Math.max(0, parseInt(inp.value || '0', 10)) : 0;
    });
    return counts;
  }

  function pickTotal() {
    return Object.values(getPickCounts()).reduce(function (a, b) { return a + b; }, 0);
  }

  function groupHasEnough(group, pick) {
    const avail = sumCountsForTopics(
      topics.filter(function (t) { return group.topic_ids.indexOf(t.id) >= 0; })
    );
    return TYPE_KEYS.every(function (k) {
      const need = pick[k] || 0;
      if (need <= 0) {
        return true;
      }
      return avail[k] >= need;
    });
  }

  function computeMinAvailForGroups(groups) {
    const minAvail = {};
    TYPE_KEYS.forEach(function (k) { minAvail[k] = null; });

    groups.forEach(function (g) {
      const avail = sumCountsForTopics(
        topics.filter(function (t) { return g.topic_ids.indexOf(t.id) >= 0; })
      );
      TYPE_KEYS.forEach(function (k) {
        const v = avail[k] || 0;
        if (minAvail[k] === null || v < minAvail[k]) {
          minAvail[k] = v;
        }
      });
    });

    if (!groups.length) {
      TYPE_KEYS.forEach(function (k) { minAvail[k] = 0; });
    }

    return minAvail;
  }

  let lastTypeCountsSig = '';
  let typeCountsInputBound = false;

  function activeTypeKeys(minAvail) {
    return TYPE_KEYS.filter(function (k) {
      return (minAvail[k] || 0) > 0;
    });
  }

  function topicsWithQuestions() {
    return topics.filter(function (t) {
      return (t.total || 0) > 0;
    });
  }

  function activeTypeKeysForTopics(topicList) {
    const sums = {};
    TYPE_KEYS.forEach(function (k) { sums[k] = 0; });
    topicList.forEach(function (t) {
      const c = t.counts || {};
      TYPE_KEYS.forEach(function (k) {
        sums[k] += parseInt(c[k] || 0, 10);
      });
    });
    return TYPE_KEYS.filter(function (k) {
      return sums[k] > 0;
    });
  }

  function updateHorizontalPickTotal(activeKeys) {
    const el = document.getElementById('bpPickTotal');
    if (!el) {
      return;
    }
    let sum = 0;
    activeKeys.forEach(function (k) {
      const inp = document.getElementById('bp_pick_' + k);
      sum += inp ? Math.max(0, parseInt(inp.value || '0', 10)) : 0;
    });
    el.textContent = String(sum);
  }

  /** Horizontal table: types as columns; hide types with zero min available. */
  function renderTypeCountsHorizontal(groups) {
    const table = document.getElementById('bpTypeCountsTable');
    const thead = document.getElementById('bpTypeCountsHead');
    const emptyMsg = document.getElementById('bpTypeCountsEmpty');
    if (!typeCountsBody || !thead || !table) {
      return;
    }

    const minAvail = computeMinAvailForGroups(groups);
    const activeKeys = activeTypeKeys(minAvail);
    const sig = activeKeys.join('|');

    if (sig === lastTypeCountsSig && typeCountsBody.querySelector('.bp-pick-count')) {
      activeKeys.forEach(function (k) {
        const cell = typeCountsBody.querySelector('[data-bp-min-cell="' + k + '"] .badge');
        if (cell) {
          cell.textContent = String(minAvail[k] || 0);
        }
        const inp = document.getElementById('bp_pick_' + k);
        if (inp) {
          inp.max = String(minAvail[k] || 0);
        }
      });
      updateHorizontalPickTotal(activeKeys);
      return;
    }

    lastTypeCountsSig = sig;
    const picks = getPickCounts();

    if (activeKeys.length === 0) {
      thead.innerHTML = '';
      typeCountsBody.innerHTML = '';
      table.classList.add('d-none');
      if (emptyMsg) {
        emptyMsg.classList.remove('d-none');
      }
      return;
    }

    table.classList.remove('d-none');
    if (emptyMsg) {
      emptyMsg.classList.add('d-none');
    }

    let head = '<tr><th style="min-width:7rem"></th>';
    activeKeys.forEach(function (k) {
      head += '<th class="text-center small text-nowrap">' + escapeHtml(TYPE_LABELS[k] || k) + '</th>';
    });
    head += '<th class="text-center small">Total</th></tr>';
    thead.innerHTML = head;

    let minSum = 0;
    let minRow = '<tr class="table-light"><td class="fw-bold small">Min available</td>';
    activeKeys.forEach(function (k) {
      const v = minAvail[k] || 0;
      minSum += v;
      minRow += '<td class="text-center" data-bp-min-cell="' + k + '"><span class="badge text-bg-light">' + v + '</span></td>';
    });
    minRow += '<td class="text-center fw-bold">' + minSum + '</td></tr>';

    let pickSum = 0;
    let pickRow = '<tr><td class="fw-bold small">Pick</td>';
    activeKeys.forEach(function (k) {
      const val = Math.max(0, parseInt(picks[k] || '0', 10));
      pickSum += val;
      pickRow += '<td class="text-center"><input type="number" class="form-control form-control-sm text-center bp-pick-count" ' +
        'id="bp_pick_' + k + '" name="count_' + k + '" form="bpBulkForm" min="0" max="' + (minAvail[k] || 0) + '" ' +
        'step="1" value="' + val + '" inputmode="numeric"></td>';
    });
    pickRow += '<td class="text-center fw-bold"><span id="bpPickTotal">' + pickSum + '</span></td></tr>';

    typeCountsBody.innerHTML = minRow + pickRow;

    if (!typeCountsInputBound) {
      typeCountsInputBound = true;
      table.addEventListener('input', function (e) {
        if (!e.target || !e.target.classList.contains('bp-pick-count')) {
          return;
        }
        const selected = selectedTopics();
        const mode = getGroupingMode();
        const g = buildGroups(selected, mode);
        const keys = activeTypeKeys(computeMinAvailForGroups(g));
        updateHorizontalPickTotal(keys);
        refreshPreview(false);
      });
    }
  }

  function refreshPreview(skipTypeTable) {
    const selected = selectedTopics();
    const mode = getGroupingMode();
    const groups = buildGroups(selected, mode);
    const pick = getPickCounts();
    const totalPick = pickTotal();
    const pattern = titlePattern ? titlePattern.value : '{subject} – {chapters}';

    if (skipTypeTable !== false) {
      renderTypeCountsHorizontal(groups);
    } else {
      updateHorizontalPickTotal(activeTypeKeys(computeMinAvailForGroups(groups)));
    }

    if (!previewBody || !previewSummary) return;

    previewBody.innerHTML = '';
    let okCount = 0;

    groups.forEach(function (g, idx) {
      const firstCh = g.chapter_names[0] || '';
      const title = applyTitlePattern(pattern, g.chapters_label, firstCh);
      const enough = totalPick > 0 && groupHasEnough(g, pick);
      if (enough) okCount++;

      const tr = document.createElement('tr');
      tr.innerHTML =
        '<td>' + (idx + 1) + '</td>' +
        '<td>' + escapeHtml(title) + '</td>' +
        '<td class="small">' + escapeHtml(g.chapters_label) + '</td>' +
        '<td class="text-center">' + totalPick + '</td>' +
        '<td class="small ' + (enough ? 'bp-status-ok' : 'bp-status-warn') + '">' +
        (totalPick <= 0 ? 'Set counts' : (enough ? '<i class="fas fa-check"></i> Ready' : '<i class="fas fa-exclamation-triangle"></i> Not enough Qs')) +
        '</td>';
      previewBody.appendChild(tr);

      g.title = title;
    });

    const boardText = boardSel && boardSel.selectedIndex >= 0 ? boardSel.options[boardSel.selectedIndex].text : '';
    const classText = classSel && classSel.selectedIndex >= 0 ? classSel.options[classSel.selectedIndex].text : '';

    if (!filtersReady()) {
      previewSummary.className = 'alert alert-secondary py-2 small mb-3';
      previewSummary.textContent = 'Select board, class, subject, and chapters to see a preview.';
    } else if (!selected.length) {
      previewSummary.className = 'alert alert-warning py-2 small mb-3';
      previewSummary.textContent = 'Select at least one chapter.';
    } else if (totalPick <= 0) {
      previewSummary.className = 'alert alert-warning py-2 small mb-3';
      previewSummary.textContent = groups.length + ' quiz(zes) planned — set question counts in step 3.';
    } else if (okCount === groups.length) {
      previewSummary.className = 'alert alert-success py-2 small mb-3';
      previewSummary.innerHTML = '<strong>' + groups.length + ' quiz' + (groups.length === 1 ? '' : 'zes') + '</strong> ready for ' +
        escapeHtml(boardText) + ' · ' + escapeHtml(classText) + ' · ' + escapeHtml(displaySubjectName()) +
        ' — ' + totalPick + ' questions each, audience: board prep students.';
    } else {
      previewSummary.className = 'alert alert-danger py-2 small mb-3';
      previewSummary.innerHTML = '<strong>' + okCount + ' of ' + groups.length + '</strong> quiz groups have enough questions. Reduce counts or add more questions to the bank.';
    }

    if (groupsJsonInput) {
      groupsJsonInput.value = JSON.stringify(groups.map(function (g) {
        return { title: g.title, topic_ids: g.topic_ids };
      }));
    }

    const canSubmit = filtersReady() && selected.length > 0 && totalPick > 0 && okCount === groups.length && groups.length > 0;
    if (submitBtn) submitBtn.disabled = !canSubmit;
    if (submitLabel) {
      submitLabel.textContent = groups.length <= 1
        ? 'Create 1 quiz'
        : 'Create ' + groups.length + ' quizzes';
    }

    return groups;
  }

  function escapeHtml(str) {
    return String(str || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function renderChaptersTable() {
    if (!chaptersBody || !chaptersHead) return;

    const visibleTopics = topicsWithQuestions();
    const typeCols = activeTypeKeysForTopics(visibleTopics);

    if (!visibleTopics.length) {
      chaptersHead.innerHTML = '';
      chaptersBody.innerHTML = '';
      if (chaptersEmpty) {
        chaptersEmpty.textContent = 'No chapters with questions for this board, class, and subject.';
        chaptersEmpty.classList.remove('d-none');
      }
      if (chaptersTableWrap) chaptersTableWrap.classList.add('d-none');
      refreshPreview();
      return;
    }

    if (chaptersEmpty) chaptersEmpty.classList.add('d-none');
    if (chaptersTableWrap) chaptersTableWrap.classList.remove('d-none');

    let head = '<tr>' +
      '<th style="width:2.5rem"><span class="sr-only">Select</span></th>' +
      '<th style="width:3rem">#</th>' +
      '<th>Chapter</th>';
    typeCols.forEach(function (k) {
      head += '<th class="text-center bp-ch-type-col">' + escapeHtml(TYPE_LABELS[k] || k) + '</th>';
    });
    head += '<th class="text-center" style="width:4rem">Total</th></tr>';
    chaptersHead.innerHTML = head;

    chaptersBody.innerHTML = '';
    visibleTopics.forEach(function (t, idx) {
      const c = t.counts || {};
      const tr = document.createElement('tr');
      let row =
        '<td><div class="custom-control custom-checkbox mb-0">' +
        '<input type="checkbox" class="custom-control-input bp-chapter-cb" id="bp_ch_' + t.id + '" value="' + t.id + '" checked>' +
        '<label class="custom-control-label" for="bp_ch_' + t.id + '"><span class="sr-only">Select</span></label></div></td>' +
        '<td>' + (idx + 1) + '</td>' +
        '<td>' + escapeHtml(t.topic_name) + '</td>';
      typeCols.forEach(function (k) {
        row += '<td class="text-center small">' + (c[k] || 0) + '</td>';
      });
      row += '<td class="text-center fw-bold">' + (t.total || 0) + '</td>';
      tr.innerHTML = row;
      chaptersBody.appendChild(tr);
    });

    chaptersBody.querySelectorAll('.bp-chapter-cb').forEach(function (cb) {
      cb.addEventListener('change', refreshPreview);
    });

    refreshPreview();
  }

  function setWizardLocked(locked) {
    if (!wizard) return;
    wizard.classList.toggle('bp-bulk-locked', locked);
    wizard.classList.toggle('bp-bulk-ready', !locked);
  }

  function loadTopics() {
    if (!filtersReady()) {
      topics = [];
      setWizardLocked(true);
      if (chaptersEmpty) chaptersEmpty.classList.add('d-none');
      if (chaptersTableWrap) chaptersTableWrap.classList.add('d-none');
      refreshPreview();
      return;
    }

    setWizardLocked(true);
    if (chaptersLoading) chaptersLoading.classList.remove('d-none');
    if (chaptersEmpty) chaptersEmpty.classList.add('d-none');
    if (chaptersTableWrap) chaptersTableWrap.classList.add('d-none');

    const params = new URLSearchParams({
      class_id: String(selectedClassId()),
      subject_id: subjectSel.value,
      board_publisher_id: boardSel.value,
    });

    fetch(CFG.topicsUrl + '?' + params.toString(), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        topics = (res && res.ok && res.topics) ? res.topics : [];
        subjectName = (res && res.subject_name) || '';
        subjectShort = (res && res.subject_short) || '';
        classId = (res && res.class_id) || 0;
        if (classHidden) classHidden.value = String(classId);

        if (chaptersLoading) chaptersLoading.classList.add('d-none');

        if (!topics.length) {
          if (chaptersEmpty) {
            chaptersEmpty.textContent = 'No chapters found for this board, class, and subject. Add topics under Question Bank → Topics.';
            chaptersEmpty.classList.remove('d-none');
          }
          if (chaptersTableWrap) chaptersTableWrap.classList.add('d-none');
          setWizardLocked(true);
        } else if (!topicsWithQuestions().length) {
          renderChaptersTable();
          setWizardLocked(true);
        } else {
          renderChaptersTable();
          setWizardLocked(false);
        }
        refreshPreview();
      })
      .catch(function () {
        topics = [];
        if (chaptersLoading) chaptersLoading.classList.add('d-none');
        if (chaptersEmpty) {
          chaptersEmpty.textContent = 'Error loading chapters. Try again.';
          chaptersEmpty.classList.remove('d-none');
        }
        setWizardLocked(true);
      });
  }

  function loadSubjects(classId, preselect) {
    classId = parseInt(classId || '0', 10);
    if (!subjectSel || classId <= 0) {
      clearSubjects('Select class first');
      if (classHidden) classHidden.value = '0';
      subjectSel && (subjectSel.disabled = true);
      loadTopics();
      return;
    }

    subjectSel.disabled = true;
    clearSubjects('Loading…');

    fetch(CFG.subjectsUrl + '?class_id=' + encodeURIComponent(String(classId)), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        clearSubjects('Select subject');
        if (!res || !res.ok) {
          clearSubjects(res && res.msg ? res.msg : 'No subjects');
          loadTopics();
          return;
        }

        if (classHidden) classHidden.value = String(res.class_id || classId);
        if (gradeHidden && res.prep_grade_level) {
          gradeHidden.value = res.prep_grade_level;
        }

        (res.data || []).forEach(function (row) {
          const opt = document.createElement('option');
          opt.value = row.subject_id;
          opt.textContent = row.subject_name || row.subject_short_name || ('Subject ' + row.subject_id);
          if (preselect && String(preselect) === String(row.subject_id)) {
            opt.selected = true;
          }
          subjectSel.appendChild(opt);
        });

        if (!(res.data || []).length) {
          clearSubjects('No subjects for this class');
        }
        loadTopics();
      })
      .catch(function () {
        clearSubjects('Error loading subjects');
        loadTopics();
      })
      .finally(function () {
        subjectSel.disabled = false;
      });
  }

  function updateSettingsSummary() {
    if (!settingsSummary) return;
    const time = document.getElementById('time_limit_min');
    const attempts = document.getElementById('max_attempts');
    const published = document.getElementById('is_published');
    const parts = [];
    if (time && time.value && parseInt(time.value, 10) > 0) {
      parts.push(time.value + ' min');
    } else {
      parts.push('No time limit');
    }
    if (attempts) parts.push(attempts.value + ' attempt(s)');
    if (published && published.checked) parts.push('Published');
    settingsSummary.textContent = parts.join(' · ');
  }

  if (classSel) {
    classSel.addEventListener('change', function () {
      if (classHidden) classHidden.value = this.value || '0';
      loadSubjects(this.value, '');
    });
  }

  if (subjectSel) {
    subjectSel.addEventListener('change', loadTopics);
  }

  if (boardSel) {
    boardSel.addEventListener('change', loadTopics);
  }

  document.querySelectorAll('#bpGroupingMode input[name="grouping_mode"]').forEach(function (radio) {
    radio.addEventListener('change', refreshPreview);
  });

  if (titlePattern) {
    titlePattern.addEventListener('input', refreshPreview);
  }

  const selectAllBtn = document.getElementById('bpSelectAllChapters');
  const selectNoneBtn = document.getElementById('bpSelectNoneChapters');
  if (selectAllBtn) {
    selectAllBtn.addEventListener('click', function () {
      chaptersBody.querySelectorAll('.bp-chapter-cb').forEach(function (cb) { cb.checked = true; });
      refreshPreview();
    });
  }
  if (selectNoneBtn) {
    selectNoneBtn.addEventListener('click', function () {
      chaptersBody.querySelectorAll('.bp-chapter-cb').forEach(function (cb) { cb.checked = false; });
      refreshPreview();
    });
  }

  ['time_limit_min', 'max_attempts', 'is_published'].forEach(function (id) {
    const el = document.getElementById(id);
    if (el) {
      el.addEventListener('change', updateSettingsSummary);
      el.addEventListener('input', updateSettingsSummary);
    }
  });

  if (bulkForm) {
    bulkForm.addEventListener('submit', function (e) {
      e.preventDefault();
      if (submitBtn && submitBtn.disabled) return;

      const fd = new FormData(bulkForm);
      if (CFG.csrfName && CFG.csrfHash) {
        fd.set(CFG.csrfName, CFG.csrfHash);
      }

      submitBtn.disabled = true;
      const origHtml = submitBtn.innerHTML;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Creating…';

      fetch(CFG.storeUrl, {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res && res.ok) {
            let msg = res.msg || 'Quizzes created.';
            if (res.errors && res.errors.length) {
              msg += '\n\nSkipped:\n' + res.errors.map(function (err) {
                return (err.title || 'Quiz') + ': ' + (err.msg || '');
              }).join('\n');
            }
            if (res.redirect) {
              window.location.href = res.redirect;
              return;
            }
            alert(msg);
          } else {
            alert((res && res.msg) || 'Failed to create quizzes.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = origHtml;
          }
        })
        .catch(function () {
          alert('Network error. Please try again.');
          submitBtn.disabled = false;
          submitBtn.innerHTML = origHtml;
        });
    });
  }

  updateSettingsSummary();
  renderTypeCountsHorizontal([]);

  const initClass = classSel ? classSel.value : '';
  const initSub = subjectSel ? subjectSel.value : '';
  if (initClass) {
    loadSubjects(initClass, initSub);
  } else {
    setWizardLocked(true);
  }
})();
