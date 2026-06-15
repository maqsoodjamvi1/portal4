/**
 * Modal accessibility: focus trap hint, ESC, restore focus on hide.
 */
(function ($) {
  'use strict';
  if (!$) return;

  var lastFocus = null;

  $(document).on('show.bs.modal', '.sms-modal, .modal', function () {
    lastFocus = document.activeElement;
    var $modal = $(this);
    $modal.attr('aria-hidden', 'false');
    setTimeout(function () {
      var $first = $modal.find('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])').filter(':visible').first();
      if ($first.length) $first.trigger('focus');
    }, 150);
  });

  $(document).on('hidden.bs.modal', '.sms-modal, .modal', function () {
    $(this).attr('aria-hidden', 'true');
    if (lastFocus && lastFocus.focus) {
      try { lastFocus.focus(); } catch (e) {}
    }
  });
})(typeof jQuery !== 'undefined' ? jQuery : null);
