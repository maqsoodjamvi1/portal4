<?php /** Assessment Builder — quiz form scripts (no separate question bank UI) */ ?>
<script>
(function () {
  const clsSecSel = document.getElementById('cls_sec_id');
  const subjSel   = document.getElementById('subject_id');

  function clearSubjects(label) {
    if (!subjSel) return;
    subjSel.innerHTML = '';
    const opt = document.createElement('option');
    opt.value = '';
    opt.textContent = label || '-- Select Subject --';
    subjSel.appendChild(opt);
  }

  function setLoading(on) {
    if (!subjSel) return;
    subjSel.disabled = !!on;
    if (on) clearSubjects('Loading...');
  }

  function loadSubjects(clsSecId, preselect) {
    if (!subjSel) return;
    preselect = preselect || '<?= (int) old('subject_id') ?>';

    if (!clsSecId) {
      clearSubjects('-- Select Subject --');
      return;
    }
    setLoading(true);

    fetch('<?= base_url('admin/quizzes/ajax/section-subjects') ?>/' + encodeURIComponent(clsSecId), {
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
      .then(r => r.json())
      .then(j => {
        clearSubjects('-- Select Subject --');
        if (j && j.ok && Array.isArray(j.data) && j.data.length) {
          j.data.forEach(row => {
            const opt = document.createElement('option');
            const value = (row.sec_sub_id !== undefined && row.sec_sub_id !== null)
              ? row.sec_sub_id
              : row.subject_id;
            opt.value = value;
            opt.textContent = row.subject_name || row.name || row.subject_short_name || ('Subject ' + value);
            if (String(preselect) === String(value)) {
              opt.selected = true;
            }
            subjSel.appendChild(opt);
          });
          if (window.jQuery && jQuery.fn && jQuery.fn.select2) {
            jQuery(subjSel).trigger('change.select2');
          }
        } else {
          clearSubjects('No subjects found');
        }
      })
      .catch(() => clearSubjects('Error loading subjects'))
      .finally(() => setLoading(false));
  }

  if (clsSecSel) {
    clsSecSel.addEventListener('change', function () {
      loadSubjects(this.value, '');
    });
  }

  <?php
    $initCls = (int) old('cls_sec_id', $quizDefaults['cls_sec_id'] ?? 0);
    $initSub = (int) old('subject_id', $quizDefaults['sec_sub_id'] ?? 0);
    if ($initCls > 0):
  ?>
  loadSubjects('<?= $initCls ?>', '<?= $initSub ?>');
  <?php endif; ?>
})();
</script>

<script>
(function() {
  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  const startInput = document.getElementById('start_at');
  const endInput   = document.getElementById('end_at');
  const durBox     = document.getElementById('quizDurationText');

  function formatForInput(d) {
    const pad = n => String(n).padStart(2, '0');
    return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate())
      + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
  }

  function updateDuration() {
    if (!startInput || !endInput || !durBox) return;
    if (!startInput.value || !endInput.value) {
      durBox.textContent = 'Duration: --';
      return;
    }
    const s = new Date(startInput.value);
    const e = new Date(endInput.value);
    if (isNaN(s.getTime()) || isNaN(e.getTime()) || e <= s) {
      durBox.textContent = 'Duration: --';
      return;
    }
    const diffHours = Math.round((e - s) / 3600000);
    const days = Math.floor(diffHours / 24);
    const hours = diffHours % 24;
    const parts = [];
    if (days) parts.push(days + ' day' + (days > 1 ? 's' : ''));
    if (hours || !parts.length) parts.push(hours + ' hour' + (hours !== 1 ? 's' : ''));
    durBox.textContent = 'Duration: ' + parts.join(' ');
  }

  function applyStartDefault() {
    if (!startInput || !endInput || !startInput.value) return;
    const s = new Date(startInput.value);
    if (isNaN(s.getTime())) return;
    endInput.value = formatForInput(new Date(s.getTime() + 86400000));
    updateDuration();
  }

  if (startInput && endInput) {
    updateDuration();
  }

  const linkExamCb  = document.getElementById('link_to_exam');
  const publishCb   = document.getElementById('is_published');
  const publishHint = document.getElementById('publishHint');

  function syncExamQuizPublish() {
    if (!linkExamCb || !publishCb) return;
    if (linkExamCb.checked) {
      publishCb.checked = false;
      publishCb.disabled = true;
      if (publishHint) publishHint.classList.remove('d-none');
    } else {
      publishCb.disabled = false;
      if (publishHint) publishHint.classList.add('d-none');
    }
  }

  if (linkExamCb) {
    linkExamCb.addEventListener('change', function () {
      syncExamQuizPublish();
      updateQuizSettingsSummary();
    });
    syncExamQuizPublish();
  }

  function formatShortDateTime(inputVal) {
    if (!inputVal) return '';
    const d = new Date(inputVal);
    if (isNaN(d.getTime())) return '';
    return d.toLocaleString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
  }

  function updateQuizSettingsSummary() {
    const box = document.getElementById('quizSettingsSummary');
    if (!box) return;

    const timeMin = document.getElementById('time_limit_min');
    const attempts = document.getElementById('max_attempts');
    const marks = document.getElementById('per_question_marks');
    const neg = document.getElementById('negative_mark_per_q');
    const shuffleQ = document.getElementById('shuffle_questions');
    const shuffleO = document.getElementById('shuffle_options');
    const showSol = document.getElementById('show_solution');
    const wifi = document.getElementById('wifi_only');
    const urdu = document.getElementById('is_urdu');
    const byType = document.getElementById('is_order_by_qtype');
    const published = document.getElementById('is_published');
    const linkExam = document.getElementById('link_to_exam');

    const parts = [];
    const mins = timeMin ? parseInt(timeMin.value, 10) : 0;
    parts.push(mins > 0 ? mins + ' min limit' : 'No time limit');
    parts.push((attempts ? attempts.value : '1') + ' attempt' + ((attempts && parseInt(attempts.value, 10) !== 1) ? 's' : ''));
    parts.push((marks ? marks.value : '1') + ' mark/Q');
    if (neg && parseFloat(neg.value) > 0) {
      parts.push('-' + neg.value + ' negative/Q');
    }

    if (startInput && startInput.value) {
      parts.push('Starts ' + formatShortDateTime(startInput.value));
    }
    if (endInput && endInput.value) {
      parts.push('Ends ' + formatShortDateTime(endInput.value));
    }

    const flags = [];
    if (shuffleQ && shuffleQ.checked) flags.push('shuffle Q');
    if (shuffleO && shuffleO.checked) flags.push('shuffle options');
    if (showSol && showSol.checked) flags.push('show solution');
    if (wifi && wifi.checked) flags.push('WiFi only');
    if (urdu && urdu.checked) flags.push('Urdu');
    if (byType && byType.checked) flags.push('by type');
    if (linkExam && linkExam.checked) {
      flags.push('exam quiz');
    } else if (published && published.checked) {
      flags.push('published');
    } else {
      flags.push('draft');
    }

    box.textContent = parts.join(' · ') + (flags.length ? ' · ' + flags.join(', ') : '');
  }

  const summaryInputs = document.querySelectorAll(
    '#quizSettingsModal input, #quizSettingsModal textarea, #quizSettingsModal select'
  );
  summaryInputs.forEach(function (el) {
    el.addEventListener('change', updateQuizSettingsSummary);
    el.addEventListener('input', updateQuizSettingsSummary);
  });

  if (startInput && endInput) {
    startInput.addEventListener('change', function () {
      applyStartDefault();
      updateQuizSettingsSummary();
    });
    endInput.addEventListener('change', function () {
      updateDuration();
      updateQuizSettingsSummary();
    });
  }

  const settingsModal = document.getElementById('quizSettingsModal');
  if (settingsModal && window.jQuery) {
    jQuery(settingsModal).on('shown.bs.modal hidden.bs.modal', updateQuizSettingsSummary);
  }

  updateQuizSettingsSummary();

  const termSel = document.getElementById('term_session_id');
  const clsSel  = document.getElementById('cls_sec_id');
  const subjSel = document.getElementById('subject_id');
  const wrap    = document.getElementById('existingQuizzesWrap');

  function refreshQuizzes() {
    if (!termSel || !clsSel || !subjSel || !wrap) return;
    const termId = termSel.value;
    const clsId  = clsSel.value;
    const secSub = subjSel.value;
    if (!termId || !clsId || !secSub) {
      wrap.innerHTML = '<span class="text-muted small">—</span>';
      return;
    }
    wrap.innerHTML = '<span class="text-muted small">Loading…</span>';
    const url = '<?= base_url('admin/quizzes/ajax/by-filters') ?>'
      + '?term_session_id=' + encodeURIComponent(termId)
      + '&cls_sec_id=' + encodeURIComponent(clsId)
      + '&sec_sub_id=' + encodeURIComponent(secSub);
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json())
      .then(j => {
        if (!j || !j.ok || !Array.isArray(j.data) || !j.data.length) {
          wrap.innerHTML = '<span class="text-muted small">None</span>';
          return;
        }
        let html = '<div class="row g-0">';
        j.data.forEach(function (q) {
          html += '<div class="col-6 col-md-4 mb-1 px-1">'
            + '<a href="<?= base_url('admin/quizzes/edit/') ?>' + encodeURIComponent(q.quiz_id)
            + '" class="d-block small border rounded px-2 py-1 text-dark">'
            + escapeHtml(q.title || 'Untitled') + ' <span class="text-muted">(' + (q.questions_count || 0) + ')</span>'
            + '</a></div>';
        });
        wrap.innerHTML = html + '</div>';
      })
      .catch(function () {
        wrap.innerHTML = '<p class="text-danger mb-0">Error loading quizzes.</p>';
      });
  }

  [termSel, clsSel, subjSel].forEach(function (el) {
    if (el) el.addEventListener('change', refreshQuizzes);
  });
  setTimeout(refreshQuizzes, 400);
})();
</script>

<script>
jQuery(function ($) {
  $('#quizCreateForm').on('submit', function (e) {
    if (!window.QpBuilderApi || !window.QpBuilderApi.syncQuizFormFromSelection()) {
      e.preventDefault();
      return false;
    }
  });
});
</script>
