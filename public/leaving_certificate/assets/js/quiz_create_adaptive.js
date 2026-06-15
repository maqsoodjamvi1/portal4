/**

 * Adaptive quiz builder for admin/quizzes/create

 */

(function ($) {

  'use strict';



  var levels = {};

  var activeLevelNo = 0;

  var levelCounter = 0;



  function isAdaptiveOn() {

    return $('#is_adaptive').is(':checked');

  }



  function syncAssignedGlobal() {

    window.assignedQuestions = window.assignedQuestions || {};

    Object.keys(window.assignedQuestions).forEach(function (k) { delete window.assignedQuestions[k]; });

    Object.keys(levels).forEach(function (no) {

      (levels[no].question_ids || []).forEach(function (id) {

        window.assignedQuestions[String(id)] = parseInt(no, 10);

      });

    });

  }



  function renderHiddenFields() {

    var $wrap = $('#levelsWrap');

    $wrap.empty();

    Object.keys(levels).sort(function (a, b) { return parseInt(a, 10) - parseInt(b, 10); }).forEach(function (no) {

      var L = levels[no];

      $wrap.append(

        '<input type="hidden" name="levels[' + no + '][passing_percentage]" value="' + escAttr(L.passing_percentage) + '">' +

        '<input type="hidden" name="levels[' + no + '][base_difficulty]" value="' + escAttr(L.base_difficulty) + '">' +

        '<input type="hidden" name="levels[' + no + '][question_ids]" value="' + escAttr((L.question_ids || []).join(',')) + '">'

      );

    });

  }



  function escAttr(s) {

    return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');

  }



  function updateTotalQuestions() {

    var total = 0;

    Object.keys(levels).forEach(function (no) {

      total += (levels[no].question_ids || []).length;

    });

    var $qc = $('#questions_count');

    if ($qc.length && isAdaptiveOn()) {

      $qc.val(total);

    }

  }



  function sortedLevelKeys() {

    return Object.keys(levels).map(function (k) { return parseInt(k, 10); }).sort(function (a, b) { return a - b; });

  }



  function renderProgress() {

    var $wrap = $('#adaptiveLevelProgress');

    var $body = $('#adaptiveLevelProgressBody');

    if (!$body.length) return;



    var keys = sortedLevelKeys();

    if (!keys.length || !isAdaptiveOn()) {

      $wrap.addClass('d-none');

      $body.empty();

      return;

    }



    $wrap.removeClass('d-none');

    $body.empty();



    keys.forEach(function (no) {

      var L = levels[no];

      var cnt = (L.question_ids || []).length;

      var ready = cnt > 0;

      var isActive = parseInt(no, 10) === parseInt(activeLevelNo, 10);

      var $tr = $('<tr></tr>');

      if (isActive) $tr.addClass('table-info');

      $tr.append('<td><strong>Level ' + no + '</strong></td>');

      $tr.append('<td>' + escAttr(L.passing_percentage) + '%</td>');

      $tr.append('<td>' + cnt + '</td>');

      $tr.append(

        '<td>' + (ready

          ? '<span class="text-success font-weight-bold">Ready</span>'

          : '<span class="text-warning">Needs questions</span>') + '</td>'

      );

      $tr.append(

        '<td><button type="button" class="btn btn-xs btn-outline-primary adaptive-goto-level" data-level="' + no + '">Work on</button></td>'

      );

      $body.append($tr);

    });

  }



  function renderPills() {

    var $pills = $('#adaptiveLevelPills');

    var $hint = $('#adaptiveLevelsHint');

    if (!$pills.length) return;



    $pills.empty();

    var keys = sortedLevelKeys();



    if (!keys.length) {

      $('#adaptiveLevelEditor').addClass('d-none');

      if ($hint.length) $hint.removeClass('d-none');

      renderProgress();

      return;

    }

    if ($hint.length) $hint.addClass('d-none');



    keys.forEach(function (no) {

      var L = levels[no];

      var cnt = (L.question_ids || []).length;

      var $btn = $('<button type="button" class="nav-link qb-level-pill"></button>');

      $btn.attr('data-level', no);

      var pillClass = cnt > 0 ? 'badge-success' : 'badge-warning';

      $btn.html('Level ' + no + ' <span class="badge ' + pillClass + ' ml-1">' + cnt + '</span>');

      if (parseInt(no, 10) === parseInt(activeLevelNo, 10)) {

        $btn.addClass('active');

      }

      $pills.append($('<div class="nav-item"></div>').append($btn));

    });



    if (!activeLevelNo || !levels[activeLevelNo]) {

      activeLevelNo = keys[0];

    }

    renderEditor();

    renderProgress();

  }



  function renderEditor() {

    var L = levels[activeLevelNo];

    var $ed = $('#adaptiveLevelEditor');

    if (!$ed.length || !L) {

      if ($ed.length) $ed.addClass('d-none');

      return;

    }

    $ed.removeClass('d-none');

    $ed.find('.adaptive-passing-input').val(L.passing_percentage);

    $ed.find('.adaptive-difficulty-input').val(L.base_difficulty);



    var $chips = $ed.find('.adaptive-level-chips');

    $chips.empty();

    if (!(L.question_ids || []).length) {

      $chips.html('No questions on this level yet — tick questions in the bank, then click <strong>Assign to this level</strong>.');

    } else {

      $chips.append('<span class="text-muted mr-1">' + L.question_ids.length + ' question(s):</span>');

      L.question_ids.forEach(function (id) {

        $chips.append('<span class="badge badge-primary mr-1 mb-1">#' + id + '</span>');

      });

    }



    $('#activeLevelLabel').text('Level ' + activeLevelNo);

    $('.qb-level-pill').removeClass('active');

    $('.qb-level-pill[data-level="' + activeLevelNo + '"]').addClass('active');

    renderProgress();

  }



  function lockQuestionRows() {

    $('.qb-check').each(function () {

      var id = $(this).val();

      var lvl = window.assignedQuestions[id];

      var $row = $(this).closest('.qb-q-item');

      if (lvl) {

        $row.addClass('qb-assigned');

        $(this).prop('disabled', true).prop('checked', false);

        var badgeText = 'L' + lvl;

        if (!$row.find('.qb-level-badge').length) {

          $row.find('.qb-q-item-meta').append(

            '<span class="badge badge-primary qb-level-badge ml-1">' + badgeText + '</span>'

          );

        } else {

          $row.find('.qb-level-badge').text(badgeText);

        }

      } else {

        $row.removeClass('qb-assigned');

        $(this).prop('disabled', false);

        $row.find('.qb-level-badge').remove();

      }

    });

  }



  function addLevel() {

    levelCounter += 1;

    var no = levelCounter;

    levels[no] = {

      passing_percentage: 60,

      base_difficulty: 'medium',

      question_ids: []

    };

    activeLevelNo = no;

    renderPills();

    renderHiddenFields();

    updateTotalQuestions();

    syncAssignedGlobal();

    lockQuestionRows();

    return no;

  }



  function createLevelsFromInput() {

    var count = parseInt($('#adaptiveLevelCountInput').val(), 10);

    if (isNaN(count) || count < 1) count = 5;

    if (count > 20) count = 20;



    if (Object.keys(levels).length) {

      if (!confirm('Replace existing levels? Assigned questions will be cleared.')) {

        return;

      }

      levels = {};

      levelCounter = 0;

      window.assignedQuestions = {};

    }



    for (var i = 0; i < count; i++) {

      addLevel();

    }

    activeLevelNo = 1;

    renderPills();

    renderHiddenFields();

    syncAssignedGlobal();

    lockQuestionRows();



    if (window.qbReloadFromTypeFilters) {

      window.qbReloadFromTypeFilters();

    }

  }



  function removeActiveLevel() {

    if (!activeLevelNo || !levels[activeLevelNo]) return;

    if (!confirm('Remove Level ' + activeLevelNo + ' and unassign its questions?')) return;



    (levels[activeLevelNo].question_ids || []).forEach(function (id) {

      delete window.assignedQuestions[String(id)];

    });

    delete levels[activeLevelNo];

    var keys = sortedLevelKeys();

    activeLevelNo = keys.length ? keys[0] : 0;

    renderPills();

    renderHiddenFields();

    updateTotalQuestions();

    syncAssignedGlobal();

    lockQuestionRows();

  }



  function goToNextLevel() {

    var keys = sortedLevelKeys();

    if (!keys.length) return;

    var idx = keys.indexOf(parseInt(activeLevelNo, 10));

    if (idx < 0) idx = 0;

    else if (idx < keys.length - 1) idx += 1;

    activeLevelNo = keys[idx];

    renderEditor();

    renderPills();

    var $bar = $('#adaptiveAssignBar');

    if ($bar.length) {

      $('html, body').animate({ scrollTop: $bar.offset().top - 80 }, 200);

    }

  }



  function assignCheckedToActive() {

    if (!activeLevelNo || !levels[activeLevelNo]) {

      alert('Create levels first, then click a level tab (e.g. Level 1).');

      return;

    }

    var ids = [];

    $('.qb-check:checked:not(:disabled)').each(function () {

      ids.push($(this).val());

    });

    if (!ids.length) {

      alert('Tick one or more questions in the bank, then click Assign to this level.');

      return;

    }



    var L = levels[activeLevelNo];

    for (var i = 0; i < ids.length; i++) {

      var id = ids[i];

      if (window.assignedQuestions[id] && parseInt(window.assignedQuestions[id], 10) !== parseInt(activeLevelNo, 10)) {

        alert('Question #' + id + ' is already on Level ' + window.assignedQuestions[id]);

        return;

      }

    }



    ids.forEach(function (id) {

      if (L.question_ids.indexOf(id) === -1) {

        L.question_ids.push(id);

      }

      window.assignedQuestions[id] = parseInt(activeLevelNo, 10);

    });



    $('#qbCheckMaster').prop('checked', false);

    renderPills();

    renderHiddenFields();

    updateTotalQuestions();

    lockQuestionRows();

  }



  function clearActiveLevel() {

    if (!activeLevelNo || !levels[activeLevelNo]) return;

    if (!confirm('Remove all questions from Level ' + activeLevelNo + '?')) return;

    var L = levels[activeLevelNo];

    (L.question_ids || []).forEach(function (id) {

      delete window.assignedQuestions[String(id)];

    });

    L.question_ids = [];

    renderPills();

    renderHiddenFields();

    updateTotalQuestions();

    lockQuestionRows();

  }



  function updateAdaptiveHints() {

    var on = isAdaptiveOn();

    $('#quizTypeCountsRow').toggleClass('d-none', on);

    var $hint = $('#qbEmptyHint');

    if ($hint.length && on) {

      if (!selectedKeysHasTopics()) {

        $hint.text('Adaptive mode: select topics in the bank below (all question types load). Type counts are not used.');

      }

    }

  }



  function selectedKeysHasTopics() {
    var keys = window.quizQbSelectedKeys;
    return keys && typeof keys.size === 'number' && keys.size > 0;
  }



  function toggleAdaptiveUi() {

    var on = isAdaptiveOn();

    $('#adaptiveLevelsCard').toggleClass('d-none', !on);

    $('#adaptiveAssignBar').toggleClass('d-none', !on);

    $('#qbSelectAll, #qbClearAll').prop('disabled', on);

    updateAdaptiveHints();



    if (on) {

      updateTotalQuestions();

      if (window.qbReloadFromTypeFilters) {

        window.qbReloadFromTypeFilters();

      }

    } else {

      renderProgress();

    }

  }



  function beforeSubmit() {

    if (!isAdaptiveOn()) return true;

    renderHiddenFields();

    updateTotalQuestions();

    var keys = sortedLevelKeys();

    if (!keys.length) {

      alert('Create at least one level for an adaptive quiz.');

      return false;

    }

    for (var i = 0; i < keys.length; i++) {

      var no = keys[i];

      if (!(levels[no].question_ids || []).length) {

        alert('Level ' + no + ' has no questions. Assign questions to every level before saving.');

        return false;

      }

    }

    return true;

  }



  function hydrateFromExistingAssigned() {

    var rows = window.existingAssigned || [];

    if (!rows.length) return;



    rows.forEach(function (row) {

      var lvl = parseInt(row.level_no, 10);

      if (!lvl) return;

      if (lvl > levelCounter) levelCounter = lvl;

      if (!levels[lvl]) {

        levels[lvl] = {

          passing_percentage: row.passing_percentage || 60,

          base_difficulty: row.base_difficulty || 'medium',

          question_ids: []

        };

      }

      var qid = String(row.question_id);

      if (levels[lvl].question_ids.indexOf(qid) === -1) {

        levels[lvl].question_ids.push(qid);

      }

    });

    syncAssignedGlobal();

    activeLevelNo = sortedLevelKeys()[0] || 0;

    renderPills();

    renderHiddenFields();

    updateTotalQuestions();

  }



  function init() {

    window.assignedQuestions = window.assignedQuestions || {};

    levels = {};

    levelCounter = 0;



    hydrateFromExistingAssigned();



    $('#createLevelsBtn').off('click').on('click', function (e) {

      e.preventDefault();

      createLevelsFromInput();

    });



    $('#addLevelBtn').off('click').on('click', function (e) {

      e.preventDefault();

      addLevel();

    });



    $('#assignToActiveLevelBtn').off('click').on('click', assignCheckedToActive);

    $('#nextLevelBtn').off('click').on('click', function (e) {

      e.preventDefault();

      goToNextLevel();

    });

    $('#clearLevelAssignmentsBtn').off('click').on('click', clearActiveLevel);

    $('#removeActiveLevelBtn').off('click').on('click', removeActiveLevel);



    $(document).off('click.adaptivePill', '.qb-level-pill').on('click.adaptivePill', '.qb-level-pill', function () {

      activeLevelNo = parseInt($(this).data('level'), 10);

      renderEditor();

      renderPills();

    });



    $(document).off('click.adaptiveGoto', '.adaptive-goto-level').on('click.adaptiveGoto', '.adaptive-goto-level', function () {

      activeLevelNo = parseInt($(this).data('level'), 10);

      renderEditor();

      renderPills();

    });



    $('#adaptiveLevelEditor').on('change', '.adaptive-passing-input, .adaptive-difficulty-input', function () {

      if (!levels[activeLevelNo]) return;

      levels[activeLevelNo].passing_percentage = parseFloat($('#adaptiveLevelEditor .adaptive-passing-input').val()) || 60;

      levels[activeLevelNo].base_difficulty = $('#adaptiveLevelEditor .adaptive-difficulty-input').val() || 'medium';

      renderHiddenFields();

      renderProgress();

    });



    $('#is_adaptive').off('change.adaptive').on('change.adaptive', toggleAdaptiveUi);



    if (window.QuizCreateAdaptiveConfig && window.QuizCreateAdaptiveConfig.isAdaptiveChecked) {

      $('#is_adaptive').prop('checked', true);

    }



    toggleAdaptiveUi();



    window.QuizCreateAdaptive = {

      beforeSubmit: beforeSubmit,

      isEnabled: isAdaptiveOn,

      refreshLocks: lockQuestionRows,

      createLevels: createLevelsFromInput

    };



    var _origApply = window.applyExistingAssignments;

    if (typeof _origApply === 'function') {

      window.applyExistingAssignments = function () {

        _origApply();

        lockQuestionRows();

      };

    }



    $(document).on('DOMNodeInserted', '#qbQuestionsTree', function () {

      if (isAdaptiveOn()) lockQuestionRows();

    });

  }



  $(init);



})(jQuery);

