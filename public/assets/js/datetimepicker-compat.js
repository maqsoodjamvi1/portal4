(function (window, $) {
  'use strict';

  if (!$) {
    return;
  }

  var apiName = 'datetimepicker';
  var dataKey = 'sms.datetimepickerCompat';

  function mapFormat(format) {
    switch (format) {
      case 'YYYY-MM-DD':
        return 'Y-m-d';
      case 'DD/MM/YYYY':
        return 'd/m/Y';
      case 'MM/YYYY':
        return 'm/Y';
      case 'HH:mm':
      case 'LT':
        return 'H:i';
      default:
        return 'Y-m-d';
    }
  }

  function isTimeOnly(format) {
    return format === 'HH:mm' || format === 'LT';
  }

  function getInput(element) {
    var $element = $(element);

    if ($element.is('input')) {
      return $element;
    }

    return $element.find('input.datetimepicker-input, input[type="text"], input').first();
  }

  function toMoment(value, format) {
    if (!value) {
      return null;
    }

    if (window.moment && window.moment.isMoment && window.moment.isMoment(value)) {
      return value.clone();
    }

    if (value instanceof Date && window.moment) {
      return window.moment(value);
    }

    if (!window.moment) {
      return null;
    }

    var formats = [format, 'YYYY-MM-DD', 'DD/MM/YYYY', 'MM/YYYY', 'HH:mm'];
    var parsed = window.moment(value, formats, true);

    return parsed.isValid() ? parsed : null;
  }

  function toDate(value, format) {
    var parsed = toMoment(value, format);
    return parsed ? parsed.toDate() : null;
  }

  function formatValue(value, format) {
    var parsed = toMoment(value, format);
    return parsed ? parsed.format(format) : '';
  }

  function initPicker(element, options) {
    var $element = $(element);
    var $input = getInput(element);
    var format = (options && options.format) || 'YYYY-MM-DD';
    var state = $element.data(dataKey) || {};

    state.options = $.extend({}, state.options || {}, options || {});
    state.format = format;
    state.input = $input[0] || null;

    if ($input.length && window.flatpickr) {
      if (state.fp && typeof state.fp.destroy === 'function') {
        state.fp.destroy();
      }

      var defaultDate = toDate($input.val(), format);

      state.fp = window.flatpickr($input[0], {
        allowInput: true,
        dateFormat: mapFormat(format),
        defaultDate: defaultDate || undefined,
        enableTime: isTimeOnly(format),
        noCalendar: isTimeOnly(format),
        minDate: state.minDate || undefined,
        onChange: function (selectedDates) {
          var date = selectedDates && selectedDates[0] && window.moment ? window.moment(selectedDates[0]) : false;
          $element.trigger('change.datetimepicker', { date: date });
        },
      });
    }

    $element.data(dataKey, state);

    return state;
  }

  function command(element, name, value) {
    var $element = $(element);
    var state = $element.data(dataKey) || initPicker(element, {});
    var $input = getInput(element);
    var format = state.format || (state.options && state.options.format) || 'YYYY-MM-DD';

    if (name === 'date') {
      if (arguments.length < 3) {
        return toMoment($input.val(), format);
      }

      var formatted = formatValue(value, format);
      $input.val(formatted);

      if (state.fp && formatted) {
        state.fp.setDate(toDate(value, format), false);
      }

      $element.trigger('change.datetimepicker', { date: toMoment(value, format) || false });
      return;
    }

    if (name === 'minDate') {
      state.minDate = toDate(value, format);
      if (state.fp) {
        state.fp.set('minDate', state.minDate || null);
      }
      $element.data(dataKey, state);
      return;
    }

    if (name === 'show') {
      if (state.fp && typeof state.fp.open === 'function') {
        state.fp.open();
      } else {
        $input.trigger('focus');
      }
      return;
    }

    if (name === 'destroy') {
      if (state.fp && typeof state.fp.destroy === 'function') {
        state.fp.destroy();
      }
      $element.removeData(dataKey);
    }
  }

  $.fn[apiName] = function (option, value) {
    var returnValue = this;

    this.each(function () {
      if (typeof option === 'string') {
        var result = command(this, option, value);
        if (result !== undefined) {
          returnValue = result;
          return false;
        }
      } else {
        initPicker(this, option || {});
      }
    });

    return returnValue;
  };

  $(document).on('click', '[data-bs-toggle="datetimepicker"], [data-toggle="datetimepicker"]', function (event) {
    var target = this.getAttribute('data-bs-target') || this.getAttribute('data-target');

    if (!target) {
      return;
    }

    event.preventDefault();
    $(target).datetimepicker('show');
  });
})(window, window.jQuery);
