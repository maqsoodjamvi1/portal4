<?php
/**
 * Legacy Sammy.js no-op shim.
 * Old cached server.js builds call $.sammy() / app.run() on admin pages.
 * CI4 serves full pages — Sammy is not loaded; this prevents JS crashes on live
 * until browsers pick up server.js?v=20260526 (Sammy removed).
 */
?>
<script>
(function (window) {
  'use strict';
  if (!window.jQuery || window.jQuery.sammy) {
    return;
  }

  window.Sammy = window.Sammy || {};

  window.Sammy.PushLocation = window.Sammy.PushLocation || function () {
    return function (app) { return app; };
  };

  var chain = {
    use: function () { return chain; },
    get: function () { return chain; },
    post: function () { return chain; },
    put: function () { return chain; },
    del: function () { return chain; },
    bind: function () { return chain; },
    before: function () { return chain; },
    after: function () { return chain; },
    swap: function () { return chain; },
    setLocationProxy: function () { return chain; },
    run: function () { return chain; }
  };

  window.jQuery.sammy = function (callback) {
    if (typeof callback === 'function') {
      try { callback.apply(chain, [chain]); } catch (e) { /* legacy router disabled */ }
    }
    return chain;
  };
})(window);
</script>
