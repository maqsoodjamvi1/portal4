(function (window, $) {
  'use strict';

  function injectCompatibilityStyles() {
    if (document.getElementById('bootstrap5-compat-styles')) {
      return;
    }

    var style = document.createElement('style');
    style.id = 'bootstrap5-compat-styles';
    style.textContent = [
      '.close{box-sizing:content-box;width:1em;height:1em;padding:.25em;color:#000;background:transparent;border:0;border-radius:.375rem;opacity:.5;font-size:1.5rem;line-height:1}',
      '.close:hover{color:#000;text-decoration:none;opacity:.75}',
      '.close:focus{outline:0;box-shadow:0 0 0 .25rem rgba(13,110,253,.25);opacity:1}',
      '.close span{pointer-events:none}',
      '.custom-control{position:relative;display:block;min-height:1.5rem;padding-left:1.5rem}',
      '.custom-control-inline{display:inline-flex;margin-right:1rem}',
      '.custom-control-input{position:absolute;left:0;z-index:-1;width:1rem;height:1.25rem;opacity:0}',
      '.custom-control-label{position:relative;margin-bottom:0;vertical-align:top}',
      '.custom-control-label::before,.custom-control-label::after{position:absolute;top:.25rem;left:-1.5rem;display:block;width:1rem;height:1rem;content:""}',
      '.custom-control-label::before{pointer-events:none;background-color:#fff;border:#adb5bd solid 1px}',
      '.custom-checkbox .custom-control-label::before{border-radius:.25rem}',
      '.custom-radio .custom-control-label::before{border-radius:50%}',
      '.custom-control-input:checked~.custom-control-label::before{color:#fff;border-color:#0d6efd;background-color:#0d6efd}',
      '.custom-checkbox .custom-control-input:checked~.custom-control-label::after{background-image:url("data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 20 20%27%3e%3cpath fill=%27none%27 stroke=%27%23fff%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27 stroke-width=%273%27 d=%27m6 10 3 3 6-6%27/%3e%3c/svg%3e")}',
      '.custom-radio .custom-control-input:checked~.custom-control-label::after{background-image:url("data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%27-4 -4 8 8%27%3e%3ccircle r=%273%27 fill=%27%23fff%27/%3e%3c/svg%3e")}',
      '.custom-switch{padding-left:2.25rem}',
      '.custom-switch .custom-control-label::before{left:-2.25rem;width:1.75rem;border-radius:2rem}',
      '.custom-switch .custom-control-label::after{top:calc(.25rem + 2px);left:calc(-2.25rem + 2px);width:calc(1rem - 4px);height:calc(1rem - 4px);background-color:#adb5bd;border-radius:2rem;transition:transform .15s ease-in-out}',
      '.custom-switch .custom-control-input:checked~.custom-control-label::after{background-color:#fff;transform:translateX(.75rem)}',
      'input[type="checkbox"].switchchk-native{appearance:none;-webkit-appearance:none;width:2.8rem;height:1.35rem;border:1px solid #9fb0c3;border-radius:999px;background:#cbd5e1;position:relative;cursor:pointer;vertical-align:middle;transition:background-color .15s ease,border-color .15s ease;display:inline-block}',
      'input[type="checkbox"].switchchk-native::after{content:"";position:absolute;top:.12rem;left:.12rem;width:1.05rem;height:1.05rem;border-radius:50%;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.18);transition:transform .15s ease}',
      'input[type="checkbox"].switchchk-native:checked{background:#3c8dbc;border-color:#367fa9}',
      'input[type="checkbox"].switchchk-native:checked::after{transform:translateX(1.45rem)}',
      'input[type="checkbox"].switchchk-native:focus{outline:0;box-shadow:0 0 0 .18rem rgba(60,141,188,.22)}',
      'input[type="checkbox"].switchchk-native[disabled]{cursor:not-allowed;opacity:.65}',
      'input[type="checkbox"].bootstrap-toggle-native{appearance:none;-webkit-appearance:none;width:4.65rem;height:1.8rem;border:1px solid #9fb0c3;border-radius:999px;background:#e2e8f0;position:relative;cursor:pointer;vertical-align:middle;transition:background-color .15s ease,border-color .15s ease;display:inline-block}',
      'input[type="checkbox"].bootstrap-toggle-native::before{content:attr(data-off);position:absolute;inset:0 .55rem 0 1.65rem;display:flex;align-items:center;justify-content:center;color:#64748b;font-size:.68rem;font-weight:800;white-space:nowrap}',
      'input[type="checkbox"].bootstrap-toggle-native::after{content:"";position:absolute;top:.16rem;left:.16rem;width:1.38rem;height:1.38rem;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(15,23,42,.22);transition:transform .15s ease}',
      'input[type="checkbox"].bootstrap-toggle-native:checked{background:#0f8f78;border-color:#0f766e}',
      'input[type="checkbox"].bootstrap-toggle-native:checked::before{content:attr(data-on);inset:0 1.65rem 0 .55rem;color:#fff}',
      'input[type="checkbox"].bootstrap-toggle-native:checked::after{transform:translateX(2.85rem)}',
      'input[type="checkbox"].bootstrap-toggle-native:focus{outline:0;box-shadow:0 0 0 .18rem rgba(15,143,120,.22)}',
      'input[type="checkbox"].bootstrap-toggle-native[disabled]{cursor:not-allowed;opacity:.65}',
      '.input-group-append,.input-group-prepend{display:flex}',
      '.input-group>.input-group-prepend>.btn,.input-group>.input-group-prepend>.input-group-text{border-top-right-radius:0;border-bottom-right-radius:0}',
      '.input-group>.input-group-append>.btn,.input-group>.input-group-append>.input-group-text{border-top-left-radius:0;border-bottom-left-radius:0}',
      '.hidden-xs{display:none!important}@media (min-width:576px){.hidden-xs{display:inline-block!important}}',
      '.panel{margin-bottom:1rem;background-color:#fff;border:1px solid rgba(0,0,0,.125);border-radius:.375rem}',
      '.panel-heading{padding:.5rem 1rem;border-bottom:1px solid rgba(0,0,0,.125);border-top-left-radius:calc(.375rem - 1px);border-top-right-radius:calc(.375rem - 1px);font-weight:600}',
      '.panel-primary>.panel-heading{color:#fff;background-color:#0d6efd;border-color:#0d6efd}',
      '.panel-body{padding:1rem}',
      '.input-group-addon{display:flex;align-items:center;padding:.375rem .75rem;font-size:1rem;font-weight:400;line-height:1.5;color:#212529;text-align:center;white-space:nowrap;background-color:#e9ecef;border:1px solid #ced4da}',
      '.input-group-addon:first-child{border-right:0;border-top-left-radius:.375rem;border-bottom-left-radius:.375rem}',
      '.input-group-addon:last-child{border-left:0;border-top-right-radius:.375rem;border-bottom-right-radius:.375rem}',
      '.glyphicon{display:inline-block;font-family:"Font Awesome 5 Free";font-weight:900;font-style:normal;text-rendering:auto;line-height:1}',
      '.glyphicon-calendar::before{content:"\\f073"}',
      '.glyphicon-time::before{content:"\\f017"}',
      '.glyphicon-lock::before{content:"\\f023"}',
      '.glyphicon-user::before{content:"\\f007"}',
      '.glyphicon-cog::before{content:"\\f013"}',
      '.glyphicon-log-out::before{content:"\\f2f5"}',
      '.glyphicon-search::before{content:"\\f002"}',
    ].join('\n');

    document.head.appendChild(style);
  }

  function initBootstrapSwitchFallback() {
    if (!$ || $.fn.bootstrapSwitch) {
      return;
    }

    $.fn.bootstrapSwitch = function (option) {
      var args = Array.prototype.slice.call(arguments, 1);

      if (typeof option === 'string') {
        if (option === 'state') {
          if (args.length) {
            return this.each(function () {
              $(this).prop('checked', !!args[0]).trigger('change');
            });
          }

          return this.first().prop('checked');
        }

        if (option === 'destroy') {
          return this.each(function () {
            $(this)
              .removeClass('switchchk-native')
              .off('.bootstrapSwitchCompat')
              .removeData('bootstrap-switch-compat');
          });
        }

        return this;
      }

      var options = option || {};

      return this.each(function () {
        var el = this;
        var $el = $(el);

        if (el.type !== 'checkbox') {
          return;
        }

        $el.addClass('switchchk-native').data('bootstrap-switch-compat', true);

        if (options.disabled !== undefined) {
          $el.prop('disabled', !!options.disabled);
        }

        $el.off('change.bootstrapSwitchCompat').on('change.bootstrapSwitchCompat', function () {
          if (typeof options.onSwitchChange === 'function') {
            var event = $.Event('switchChange.bootstrapSwitch');
            event.currentTarget = el;
            event.target = el;
            options.onSwitchChange.call(el, event, el.checked);
          }
        });
      });
    };
  }

  function initBootstrapToggleFallback() {
    if (!$ || $.fn.bootstrapToggle) {
      return;
    }

    $.fn.bootstrapToggle = function (option) {
      var args = Array.prototype.slice.call(arguments, 1);

      if (typeof option === 'string') {
        if (option === 'on' || option === 'off') {
          return this.each(function () {
            $(this).prop('checked', option === 'on').trigger('change');
          });
        }

        if (option === 'toggle') {
          return this.each(function () {
            $(this).prop('checked', !$(this).prop('checked')).trigger('change');
          });
        }

        if (option === 'enable' || option === 'disable') {
          return this.each(function () {
            $(this).prop('disabled', option === 'disable');
          });
        }

        if (option === 'destroy') {
          return this.each(function () {
            $(this)
              .removeClass('bootstrap-toggle-native')
              .off('.bootstrapToggleCompat')
              .removeData('bootstrap-toggle-compat');
          });
        }

        return this;
      }

      return this.each(function () {
        var el = this;
        var $el = $(el);

        if (el.type !== 'checkbox') {
          return;
        }

        $el
          .addClass('bootstrap-toggle-native')
          .attr('data-on', $el.attr('data-on') || 'On')
          .attr('data-off', $el.attr('data-off') || 'Off')
          .data('bootstrap-toggle-compat', true);

        if (option && option.disabled !== undefined) {
          $el.prop('disabled', !!option.disabled);
        }
      });
    };
  }

  function patchSelect2LegacyApi() {
    if (!$ || !$.fn.select2 || $.fn.select2.__smsLegacyPatched) {
      return;
    }

    var originalSelect2 = $.fn.select2;

    $.fn.select2 = function (option) {
      if (option === 'val') {
        if (arguments.length > 1) {
          var value = arguments[1];
          return this.each(function () {
            $(this).val(value).trigger('change');
          });
        }

        return this.first().val();
      }

      if (option === undefined || (option && typeof option === 'object' && !Array.isArray(option))) {
        var nextOptions = $.extend({}, option || {});
        if (!nextOptions.width) {
          nextOptions.width = '100%';
        }
        if (!nextOptions.theme && document.querySelector('link[href*="select2-bootstrap-5-theme"]')) {
          nextOptions.theme = 'bootstrap-5';
        }

        return originalSelect2.call(this, nextOptions);
      }

      return originalSelect2.apply(this, arguments);
    };

    $.extend($.fn.select2, originalSelect2);
    $.fn.select2.__smsLegacyPatched = true;
  }

  function migrateDataAttributes(root) {
    var scope = root && root.querySelectorAll ? root : document;
    var attrs = [
      ['data-toggle', 'data-bs-toggle'],
      ['data-target', 'data-bs-target'],
      ['data-dismiss', 'data-bs-dismiss'],
      ['data-spy', 'data-bs-spy'],
      ['data-ride', 'data-bs-ride'],
      ['data-slide', 'data-bs-slide'],
      ['data-slide-to', 'data-bs-slide-to'],
      ['data-parent', 'data-bs-parent'],
      ['data-offset', 'data-bs-offset'],
      ['data-placement', 'data-bs-placement'],
      ['data-container', 'data-bs-container'],
      ['data-trigger', 'data-bs-trigger'],
      ['data-html', 'data-bs-html'],
      ['data-content', 'data-bs-content'],
      ['data-original-title', 'data-bs-title'],
    ];

    attrs.forEach(function (pair) {
      if (scope.nodeType === 1 && scope.hasAttribute(pair[0]) && !scope.hasAttribute(pair[1])) {
        scope.setAttribute(pair[1], scope.getAttribute(pair[0]));
      }

      scope.querySelectorAll('[' + pair[0] + ']').forEach(function (el) {
        if (!el.hasAttribute(pair[1])) {
          el.setAttribute(pair[1], el.getAttribute(pair[0]));
        }
      });
    });
  }

  function migrateClasses(root) {
    var scope = root && root.querySelectorAll ? root : document;
    var nodes = [];

    if (scope.nodeType === 1) {
      nodes.push(scope);
    }

    if (scope.querySelectorAll) {
      Array.prototype.push.apply(nodes, scope.querySelectorAll('[class]'));
    }

    nodes.forEach(function (el) {
      if (!el.classList) {
        return;
      }

      if (el.classList.contains('custom-control')) {
        el.classList.add('form-check');
      }
      if (el.classList.contains('custom-control-inline')) {
        el.classList.add('form-check-inline');
      }
      if (el.classList.contains('custom-switch')) {
        el.classList.add('form-switch');
      }
      if (el.classList.contains('custom-control-input')) {
        el.classList.add('form-check-input');
      }
      if (el.classList.contains('custom-control-label')) {
        el.classList.add('form-check-label');
      }
      if (el.classList.contains('custom-select')) {
        el.classList.add('form-select');
      }
      if (el.classList.contains('sr-only')) {
        el.classList.add('visually-hidden');
      }
      if (el.classList.contains('float-left')) {
        el.classList.add('float-start');
      }
      if (el.classList.contains('float-right')) {
        el.classList.add('float-end');
      }
      if (el.classList.contains('text-left')) {
        el.classList.add('text-start');
      }
      if (el.classList.contains('text-right')) {
        el.classList.add('text-end');
      }

      Array.prototype.slice.call(el.classList).forEach(function (className) {
        var match = className.match(/^(m|p)(l|r)(?:-(auto|0|1|2|3|4|5))$/);
        if (match) {
          el.classList.add(match[1] + (match[2] === 'l' ? 's' : 'e') + '-' + match[3]);
        }
      });

      if (el.classList.contains('badge')) {
        [
          'primary',
          'secondary',
          'success',
          'danger',
          'warning',
          'info',
          'light',
          'dark'
        ].forEach(function (variant) {
          if (
            el.classList.contains('badge-' + variant) &&
            !el.classList.contains('text-bg-' + variant) &&
            !el.classList.contains('bg-' + variant)
          ) {
            el.classList.add('text-bg-' + variant);
          }
        });
      }
    });
  }

  function defineJQueryPlugin(pluginName, bootstrapClassName) {
    if (!$ || !window.bootstrap || !window.bootstrap[bootstrapClassName]) {
      return;
    }

    var BootstrapClass = window.bootstrap[bootstrapClassName];

    $.fn[pluginName] = function (config) {
      var args = Array.prototype.slice.call(arguments, 1);

      return this.each(function () {
        var options = typeof config === 'object' ? config : {};
        var instance = BootstrapClass.getOrCreateInstance(this, options);

        if (typeof config === 'string') {
          if (config === 'destroy') {
            instance.dispose();
          } else if (typeof instance[config] === 'function') {
            instance[config].apply(instance, args);
          }
        }
      });
    };

    $.fn[pluginName].Constructor = BootstrapClass;
  }

  function initJQueryBridge() {
    [
      ['alert', 'Alert'],
      ['button', 'Button'],
      ['carousel', 'Carousel'],
      ['collapse', 'Collapse'],
      ['dropdown', 'Dropdown'],
      ['modal', 'Modal'],
      ['offcanvas', 'Offcanvas'],
      ['popover', 'Popover'],
      ['scrollspy', 'ScrollSpy'],
      ['tab', 'Tab'],
      ['toast', 'Toast'],
      ['tooltip', 'Tooltip'],
    ].forEach(function (plugin) {
      defineJQueryPlugin(plugin[0], plugin[1]);
    });
  }

  function initBootstrapWidgets(root) {
    if (!window.bootstrap) {
      return;
    }

    var scope = root && root.querySelectorAll ? root : document;

    if (window.bootstrap.Tooltip) {
      scope.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        window.bootstrap.Tooltip.getOrCreateInstance(el);
      });
    }

    if (window.bootstrap.Popover) {
      scope.querySelectorAll('[data-bs-toggle="popover"]').forEach(function (el) {
        window.bootstrap.Popover.getOrCreateInstance(el);
      });
    }
  }

  injectCompatibilityStyles();
  migrateDataAttributes(document);
  migrateClasses(document);
  initJQueryBridge();
  initBootstrapSwitchFallback();
  initBootstrapToggleFallback();
  patchSelect2LegacyApi();
  initBootstrapWidgets(document);

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      injectCompatibilityStyles();
      migrateDataAttributes(document);
      migrateClasses(document);
      initJQueryBridge();
      initBootstrapSwitchFallback();
      initBootstrapToggleFallback();
      patchSelect2LegacyApi();
      initBootstrapWidgets(document);
    });
  }

  if (window.MutationObserver) {
    new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        mutation.addedNodes.forEach(function (node) {
          if (node.nodeType === 1) {
            migrateDataAttributes(node);
            migrateClasses(node);
            initBootstrapWidgets(node);
          }
        });
      });
    }).observe(document.documentElement, { childList: true, subtree: true });
  }
})(window, window.jQuery);
