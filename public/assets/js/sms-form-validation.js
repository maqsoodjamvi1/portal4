/**
 * Bootstrap 5 client validation + AJAX error mapping for admin forms.
 */
(function (global, $) {
  'use strict';

  if (!$) return;

  var SmsFormValidation = {
    init: function (root) {
      var $root = root ? $(root) : $(document);
      $root.find('form.needs-validation').each(function () {
        var $form = $(this);
        if ($form.data('smsValidated')) return;
        $form.data('smsValidated', true);
        $form.on('submit', function (e) {
          if (!$form[0].checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
          }
          $form.addClass('was-validated');
        });
      });

      $root.find('[data-confirm]').on('click', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var msg = $btn.data('confirm') || 'Are you sure?';
        var run = function () {
          if ($btn.is('a') && $btn.attr('href')) {
            window.location = $btn.attr('href');
          } else if ($btn.data('submitForm')) {
            $($btn.data('submitForm')).trigger('submit');
          }
        };
        if (global.SmsConfirm && global.SmsConfirm.ask) {
          global.SmsConfirm.ask({ text: msg, onConfirm: run });
        } else if (global.confirm(msg)) {
          run();
        }
      });
    },

    applyServerErrors: function ($form, errors) {
      if (!$form || !errors) return;
      $form.find('.is-invalid').removeClass('is-invalid');
      $form.find('.invalid-feedback.d-block').remove();
      Object.keys(errors).forEach(function (key) {
        var msg = errors[key];
        var $el = $form.find('[name="' + key + '"], #' + key).first();
        if (!$el.length) return;
        $el.addClass('is-invalid');
        $el.after('<div class="invalid-feedback d-block">' + $('<div>').text(msg).html() + '</div>');
      });
      $form.addClass('was-validated');
    },

    clearErrors: function ($form) {
      $form.find('.is-invalid').removeClass('is-invalid');
      $form.find('.invalid-feedback.d-block').remove();
      $form.removeClass('was-validated');
    }
  };

  global.SmsFormValidation = SmsFormValidation;

  $(function () {
    SmsFormValidation.init(document);
  });
})(typeof window !== 'undefined' ? window : this, typeof jQuery !== 'undefined' ? jQuery : null);
