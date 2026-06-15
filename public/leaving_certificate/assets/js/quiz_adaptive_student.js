/**
 * Adaptive quiz: submit level, show pass/fail sheet (no Bootstrap modal).
 */
(function () {
  'use strict';

  var cfg = window.__adaptiveQuizConfig;
  if (!cfg || !cfg.enabled) {
    return;
  }

  function truthy(v) {
    return v === true || v === 1 || v === '1' || v === 'true';
  }

  function csrfName() {
    return cfg.csrfName || 'csrf_test_name';
  }

  function csrfValue() {
    if (cfg.csrfHash) {
      return cfg.csrfHash;
    }
    var el = document.querySelector('input[name="' + csrfName() + '"]');
    return el ? el.value : '';
  }

  function mountOverlaysOnBody() {
    ['adaptiveResultSheet', 'adaptiveLoadingOverlay'].forEach(function (id) {
      var el = document.getElementById(id);
      if (el && el.parentNode !== document.body) {
        document.body.appendChild(el);
      }
    });
  }

  mountOverlaysOnBody();

  function showLoading(msg) {
    var overlay = document.getElementById('adaptiveLoadingOverlay');
    var text = document.getElementById('adaptiveLoadingText');
    if (text && msg) {
      text.textContent = msg;
    }
    if (overlay) {
      overlay.classList.add('is-active');
    }
    hideResultSheet();
  }

  function hideLoading() {
    var overlay = document.getElementById('adaptiveLoadingOverlay');
    if (overlay) {
      overlay.classList.remove('is-active');
    }
  }

  function hideResultSheet() {
    var sheet = document.getElementById('adaptiveResultSheet');
    if (!sheet) {
      return;
    }
    sheet.classList.remove('is-open');
    sheet.setAttribute('hidden', 'hidden');
    document.body.classList.remove('adaptive-result-open');
    document.querySelectorAll('.modal-backdrop').forEach(function (bd) {
      bd.parentNode.removeChild(bd);
    });
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
  }

  function showResultSheet() {
    var sheet = document.getElementById('adaptiveResultSheet');
    if (!sheet) {
      return;
    }
    hideLoading();
    document.querySelectorAll('.modal-backdrop').forEach(function (bd) {
      bd.parentNode.removeChild(bd);
    });
    document.body.classList.remove('modal-open');
    sheet.removeAttribute('hidden');
    sheet.classList.add('is-open');
    document.body.classList.add('adaptive-result-open');
  }

  function formDataFromAttempt() {
    var form = document.getElementById('attemptForm');
    var fd = form ? new FormData(form) : new FormData();
    fd.set('attempt_id', String(cfg.attemptId));
    fd.set(csrfName(), csrfValue());
    return fd;
  }

  function postForm(url) {
    return fetch(url, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formDataFromAttempt(),
      credentials: 'same-origin',
    }).then(function (r) {
      return r.json();
    });
  }

  function makeBtn(label, cls, handler) {
    var b = document.createElement('button');
    b.type = 'button';
    b.className = 'btn ' + cls;
    b.textContent = label;
    b.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      handler();
    });
    return b;
  }

  function showResultModal(data) {
    hideLoading();

    var passed = truthy(data.passed);
    var isFinal = truthy(data.is_final_level);
    var hasNext = truthy(data.has_next_level);

    var sheet = document.getElementById('adaptiveResultSheet');
    if (!sheet) {
      if (passed && isFinal) {
        completeQuiz();
      } else if (passed && hasNext) {
        moveNext();
      } else if (!passed) {
        retryLevel();
      } else {
        window.location.href = cfg.urls.review;
      }
      return;
    }

    var icon = document.getElementById('adaptiveResultIcon');
    var title = document.getElementById('adaptiveResultTitle');
    var message = document.getElementById('adaptiveResultMessage');
    var yourScore = document.getElementById('adaptiveYourScore');
    var required = document.getElementById('adaptiveRequiredScore');
    var actions = document.getElementById('adaptiveResultActions');

    if (icon) {
      icon.className = 'mb-2 text-center ' + (passed ? 'text-success' : 'text-danger');
      icon.innerHTML = passed
        ? '<i class="fas fa-trophy"></i>'
        : '<i class="fas fa-redo"></i>';
    }
    if (title) {
      if (passed && isFinal) {
        title.textContent = 'Quiz complete!';
      } else if (passed) {
        title.textContent = 'Level passed!';
      } else {
        title.textContent = 'Try again';
      }
    }
    if (message) {
      message.textContent = data.message || '';
    }
    if (yourScore && data.score) {
      yourScore.textContent = (data.score.percentage != null ? data.score.percentage : '-') + '%';
    }
    if (required) {
      required.textContent = (data.min_pass != null ? data.min_pass : cfg.passPct) + '%';
    }

    if (actions) {
      actions.innerHTML = '';

      if (passed && isFinal) {
        actions.appendChild(makeBtn('View results', 'btn-success btn-lg', function () {
          hideResultSheet();
          completeQuiz();
        }));
        actions.appendChild(makeBtn('Back to quizzes', 'btn-outline-secondary', function () {
          window.location.href = cfg.urls.catalog;
        }));
      } else if (passed && hasNext) {
        actions.appendChild(makeBtn('Continue to next level', 'btn-success btn-lg', function () {
          hideResultSheet();
          moveNext();
        }));
      } else if (!passed) {
        actions.appendChild(makeBtn('Retry this level', 'btn-warning btn-lg', function () {
          hideResultSheet();
          retryLevel();
        }));
        actions.appendChild(makeBtn('Back to quizzes', 'btn-outline-secondary', function () {
          window.location.href = cfg.urls.catalog;
        }));
      } else if (passed) {
        actions.appendChild(makeBtn('Continue', 'btn-success btn-lg', function () {
          window.location.href = cfg.urls.review;
        }));
      }
    }

    showResultSheet();
  }

  function moveNext() {
    showLoading('Loading next level...');
    postForm(cfg.urls.nextLevel)
      .then(function (data) {
        hideLoading();
        if (data.success && data.redirect) {
          window.location.href = data.redirect;
          return;
        }
        alert(data.message || 'Could not start next level.');
      })
      .catch(function () {
        hideLoading();
        alert('Network error. Please try again.');
      });
  }

  function retryLevel() {
    showLoading('Starting new attempt...');
    postForm(cfg.urls.retryLevel)
      .then(function (data) {
        hideLoading();
        if (data.success && data.redirect) {
          window.location.href = data.redirect;
          return;
        }
        alert(data.message || 'Could not retry level.');
      })
      .catch(function () {
        hideLoading();
        alert('Network error. Please try again.');
      });
  }

  function completeQuiz() {
    showLoading('Finishing quiz...');
    postForm(cfg.urls.completeQuiz)
      .then(function (data) {
        hideLoading();
        if (data.success && data.redirect) {
          window.location.href = data.redirect;
          return;
        }
        if (cfg.urls.review) {
          window.location.href = cfg.urls.review;
          return;
        }
        alert(data.message || 'Could not complete quiz.');
      })
      .catch(function () {
        hideLoading();
        if (cfg.urls.review) {
          window.location.href = cfg.urls.review;
        } else {
          alert('Network error. Please try again.');
        }
      });
  }

  window.adaptiveSubmitLevel = function () {
    showLoading('Submitting level...');
    postForm(cfg.urls.submitLevel)
      .then(function (data) {
        if (!data || !data.success) {
          hideLoading();
          alert((data && data.message) || 'Could not submit level.');
          return;
        }
        showResultModal(data);
      })
      .catch(function () {
        hideLoading();
        alert('Network error. Please try again.');
      });
  };
})();
