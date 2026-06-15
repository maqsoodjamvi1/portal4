<script>

jQuery(function ($) {

  function escapeHtml(str) {

    return $('<div>').text(str || '').html();

  }



  function syncPaperSelectionMode() {

    $('#paper_selection_mode').val($('#selection_mode').val() || 'auto');

  }



  function updatePaperCountPreview() {

    /* count kept in selection only — no UI display */

  }



  const paperClsSecSel = document.getElementById('paper_cls_sec_id');

  const paperSubjSel   = document.getElementById('paper_subject_id');



  function clearPaperSubjects(label) {

    if (!paperSubjSel) return;

    paperSubjSel.innerHTML = '';

    const opt = document.createElement('option');

    opt.value = '';

    opt.textContent = label || 'Select subject';

    paperSubjSel.appendChild(opt);

  }



  function loadPaperSubjects(clsSecId, preselect) {

    if (!paperSubjSel) return;

    preselect = preselect || '';



    if (!clsSecId) {

      clearPaperSubjects();

      return;

    }

    paperSubjSel.disabled = true;

    clearPaperSubjects('Loading…');



    fetch('<?= base_url('admin/quizzes/ajax/section-subjects') ?>/' + encodeURIComponent(clsSecId), {

      headers: { 'X-Requested-With': 'XMLHttpRequest' },

    })

      .then(function (r) { return r.json(); })

      .then(function (j) {

        clearPaperSubjects();

        if (j && j.ok && Array.isArray(j.data) && j.data.length) {

          j.data.forEach(function (row) {

            const opt = document.createElement('option');

            const value = (row.sec_sub_id !== undefined && row.sec_sub_id !== null)

              ? row.sec_sub_id

              : row.subject_id;

            opt.value = value;

            opt.textContent = row.subject_name || row.name || row.subject_short_name || ('Subject ' + value);

            if (String(preselect) === String(value)) {

              opt.selected = true;

            }

            paperSubjSel.appendChild(opt);

          });

        } else {

          clearPaperSubjects('No subjects');

        }

      })

      .catch(function () { clearPaperSubjects('Error'); })

      .finally(function () {

        paperSubjSel.disabled = false;

        refreshPapers();

      });

  }



  if (paperClsSecSel) {

    paperClsSecSel.addEventListener('change', function () {

      loadPaperSubjects(this.value, '');

    });

  }



  <?php

    $initCls = (int) old('cls_sec_id', $quizDefaults['cls_sec_id'] ?? 0);

    $initSub = (int) old('subject_id', $quizDefaults['sec_sub_id'] ?? 0);

    if ($initCls > 0):

  ?>

  loadPaperSubjects('<?= $initCls ?>', '<?= $initSub ?>');

  <?php endif; ?>



  const previousQpOnTopicsChanged = window.qpOnTopicsChanged;

  window.qpOnTopicsChanged = function () {

    if (typeof previousQpOnTopicsChanged === 'function') {

      previousQpOnTopicsChanged();

    }

    updatePaperCountPreview();

    syncPaperLabelsFromTopics();

  };



  function syncPaperLabelsFromTopics() {

    const keys = window.qpSelectedKeys ? Array.from(window.qpSelectedKeys) : [];

    if (!keys.length || !window.qpTopicMeta) {

      return;

    }

    const first = window.qpTopicMeta[keys[0]];

    if (!first) {

      return;

    }

    if (!$('#paper_subject').val()) {

      $('#paper_subject').val(first.subject_name || '');

    }

    if (!$('#paper_class').val()) {

      $('#paper_class').val(first.class_name || '');

    }

  }



  $(document).on('change input', '.qp-type-count, .qp-marks-input', updatePaperCountPreview);

  $(document).on('change', '.qp-manual-check', updatePaperCountPreview);

  $('#qpManualSelectAll, #qpManualClear').on('click', function () {

    setTimeout(updatePaperCountPreview, 0);

  });

  $('.qp-selection-tab').on('click', function () {

    setTimeout(function () {

      syncPaperSelectionMode();

      updatePaperCountPreview();

    }, 0);

  });



  $('#paper_term_session_id, #paper_cls_sec_id, #paper_subject_id').on('change', refreshPapers);



  function refreshPapers() {

    const $wrap = $('#existingPapersWrap');

    if (!$wrap.length) {

      return;

    }



    const termId = $('#paper_term_session_id').val();

    const clsId  = $('#paper_cls_sec_id').val();

    const secSub = $('#paper_subject_id').val();



    if (!termId || !clsId || !secSub) {

      $wrap.html('<span class="text-muted small">Select term, class &amp; subject.</span>');

      return;

    }



    $wrap.html('<span class="text-muted small">Loading…</span>');



    const url = window.QP_CONFIG.papersByFiltersUrl

      + '?term_session_id=' + encodeURIComponent(termId)

      + '&cls_sec_id=' + encodeURIComponent(clsId)

      + '&sec_sub_id=' + encodeURIComponent(secSub);



    $.getJSON(url).done(function (res) {

      if (!res || !res.ok || !res.data || !res.data.length) {

        $wrap.html('<span class="text-muted small">None</span>');

        return;

      }



      let html = '<div class="row g-0">';

      res.data.forEach(function (p) {

        const printUrl = window.QP_CONFIG.printSettingsUrl + '/' + encodeURIComponent(p.id);

        html += '<div class="col-6 col-md-4 mb-1 px-1">'

          + '<a href="' + printUrl + '" class="d-block small border rounded px-2 py-1 text-dark">'

          + escapeHtml(p.title) + ' <span class="text-muted">(' + (p.questions_count || 0) + ')</span>'

          + '</a></div>';

      });

      html += '</div>';

      $wrap.html(html);

    }).fail(function () {

      $wrap.html('<p class="text-danger mb-0">Could not load saved papers.</p>');

    });

  }



  const PAPER_TYPE_KEYS = ['mcq', 'mcq_multi', 'tf', 'fill', 'short', 'descriptive', 'match'];

  const PAPER_PRINT_LS_KEY = 'ab_paper_print_defaults';



  function syncPaperPrintMarksFromTable() {

    PAPER_TYPE_KEYS.forEach(function (k) {

      const src = document.getElementById('marks_' + k);

      const dst = document.getElementById('ps_marks_' + k);

      if (dst) {

        dst.value = src ? (src.value || '0') : '0';

      }

    });

  }



  function readPaperPrintDefaults() {

    try {

      const raw = localStorage.getItem(PAPER_PRINT_LS_KEY);

      return raw ? JSON.parse(raw) : null;

    } catch (e) {

      return null;

    }

  }



  function savePaperPrintDefaults() {

    const modal = document.getElementById('paperPrintSettingsModal');

    if (!modal) return;

    const data = {};

    modal.querySelectorAll('input, select, textarea').forEach(function (el) {

      if (!el.name || el.type === 'hidden' && el.id && el.id.indexOf('ps_marks_') === 0) {

        return;

      }

      if (el.type === 'checkbox') {

        data[el.name] = el.checked ? '1' : '0';

      } else if (el.type !== 'hidden' || el.id === 'descriptive_lines') {

        data[el.name] = el.value;

      }

    });

    try {

      localStorage.setItem(PAPER_PRINT_LS_KEY, JSON.stringify(data));

    } catch (e) { /* ignore */ }

  }



  function applyPaperPrintDefaults() {

    const data = readPaperPrintDefaults();

    if (!data) return;

    const modal = document.getElementById('paperPrintSettingsModal');

    if (!modal) return;

    Object.keys(data).forEach(function (name) {

      const el = modal.querySelector('[name="' + name + '"]');

      if (!el) return;

      if (el.type === 'checkbox') {

        el.checked = data[name] === '1' || data[name] === true;

      } else {

        el.value = data[name];

      }

    });

  }



  function updatePaperPrintSettingsSummary() {

    const box = document.getElementById('paperPrintSettingsSummary');

    if (!box) return;

    const columns = document.getElementById('columns');

    const fontSize = document.getElementById('font_size');

    const paperMode = document.getElementById('paper_mode');

    const versions = document.getElementById('versions');

    const showMarks = document.getElementById('show_question_marks');

    const parts = [];

    parts.push((columns ? columns.value : '2') + ' col');

    parts.push(fontSize ? fontSize.options[fontSize.selectedIndex].text : 'Normal');

    if (paperMode) {

      const pm = paperMode.value;

      parts.push(pm === 'key' ? 'Answer key' : (pm === 'both' ? 'Paper + key' : 'Student paper'));

    }

    if (versions && parseInt(versions.value, 10) > 1) {

      parts.push(versions.value + ' versions');

    }

    const flags = [];

    if (showMarks && showMarks.checked) flags.push('show marks');

    ['show_topics', 'mcq_inline', 'shuffle_questions', 'group_by_topic'].forEach(function (id) {

      const el = document.getElementById(id);

      if (el && el.checked) {

        flags.push(el.parentElement ? el.parentElement.textContent.trim().toLowerCase() : id);

      }

    });

    box.textContent = parts.join(' · ') + (flags.length ? ' · ' + flags.slice(0, 3).join(', ') : '');

  }



  function postSavedPaperPrint(paperId) {

    const url = window.QP_CONFIG.printSavedUrl + '/' + encodeURIComponent(paperId);

    const $form = $('<form method="post" target="_blank" style="display:none">')

      .attr('action', url)

      .appendTo('body');

    function add(name, val) {

      $('<input type="hidden">').attr('name', name).val(val).appendTo($form);

    }

    add(window.QP_CONFIG.csrfName, window.QP_CONFIG.csrfHash);

    ['paper_title', 'paper_subject', 'paper_class', 'exam_date', 'exam_time', 'duration', 'instructions',

      'paper_mode', 'font_size', 'versions', 'columns', 'descriptive_lines'].forEach(function (name) {

      const el = document.getElementById(name) || document.querySelector('#paperSaveForm [name="' + name + '"]');

      if (el) add(name, el.value || '');

    });

    ['show_name', 'show_roll', 'show_section', 'show_topics', 'mcq_inline', 'page_break_topic',

      'shuffle_questions', 'shuffle_mcq_options', 'group_by_topic', 'show_question_marks',

      'descriptive_answer_space'].forEach(function (name) {

      const el = document.getElementById(name);

      add(name, el && el.checked ? '1' : '0');

    });

    PAPER_TYPE_KEYS.forEach(function (k) {

      const el = document.getElementById('ps_marks_' + k) || document.getElementById('marks_' + k);

      add('marks_' + k, el ? (el.value || '0') : '0');

    });

    $form[0].submit();

    $form.remove();

  }



  $('#paperSaveForm').on('submit', function (e) {

    e.preventDefault();

    syncPaperSelectionMode();

    syncPaperLabelsFromTopics();

    syncPaperPrintMarksFromTable();

    const $wrap = $('#paperFormFieldsWrap');

    $wrap.empty();



    if (!window.QpBuilderApi || !window.QpBuilderApi.syncPaperFormFromSelection($wrap)) {

      return false;

    }



    const $btn = $('#btnSavePaper');

    $btn.prop('disabled', true);



    const fd = new FormData(this);



    $.ajax({

      url: window.QP_CONFIG.storeUrl,

      method: 'POST',

      data: fd,

      processData: false,

      contentType: false,

      headers: { 'X-Requested-With': 'XMLHttpRequest' },

      dataType: 'json',

    }).done(function (res) {

      if (!res || !res.ok || !res.id) {

        alert((res && res.msg) ? res.msg : 'Could not save question paper.');

        return;

      }

      savePaperPrintDefaults();

      postSavedPaperPrint(res.id);

      refreshPapers();

    }).fail(function (xhr) {

      let msg = 'Could not save question paper.';

      try {

        const j = xhr.responseJSON;

        if (j && j.msg) msg = j.msg;

      } catch (err) { /* ignore */ }

      alert(msg);

    }).always(function () {

      $btn.prop('disabled', false);

    });

  });



  applyPaperPrintDefaults();

  updatePaperPrintSettingsSummary();



  const printModal = document.getElementById('paperPrintSettingsModal');

  if (printModal) {

    printModal.querySelectorAll('input, select, textarea').forEach(function (el) {

      el.addEventListener('change', updatePaperPrintSettingsSummary);

      el.addEventListener('input', updatePaperPrintSettingsSummary);

    });

    $('#btnPaperPrintSettingsDone').on('click', function () {

      savePaperPrintDefaults();

      updatePaperPrintSettingsSummary();

    });

    if (window.jQuery) {

      jQuery(printModal).on('shown.bs.modal hidden.bs.modal', updatePaperPrintSettingsSummary);

    }

  }



  syncPaperSelectionMode();

  updatePaperCountPreview();

  syncPaperLabelsFromTopics();

  refreshPapers();

});

</script>
