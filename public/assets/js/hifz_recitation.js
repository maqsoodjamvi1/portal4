/**
 * Hifz daily recitation UI helpers (loaded after window.hifzRecitationConfig is set).
 */
(function (window, $) {
  'use strict';

  if (typeof $ === 'undefined') {
    return;
  }

  window.HifzRecitation = window.HifzRecitation || {};

  window.HifzRecitation.esc = function (s) {
    return $('<div/>').text(s == null ? '' : String(s)).html();
  };

  window.HifzRecitation.qualityOptions = function (qualities, selected) {
    var html = '<option value="">—</option>';
    $.each(qualities || {}, function (val, label) {
      html +=
        '<option value="' +
        window.HifzRecitation.esc(val) +
        '"' +
        (selected === val ? ' selected' : '') +
        '>' +
        window.HifzRecitation.esc(label) +
        '</option>';
    });
    return html;
  };
})(window, window.jQuery);
