<script type="text/javascript">
(function ($) {
  'use strict';

  function postWorkspaceChange(url, data) {
    return $.ajax({
      type: 'POST',
      url: url,
      data: data,
      cache: false
    }).done(function () {
      location.reload();
    });
  }

  function bindCampusSelect($el) {
    if (!$el.length) return;
    $el.on('change', function () {
      postWorkspaceChange('<?= base_url('admin/ajax/change-campus') ?>', { id: $(this).val() });
    });
  }

  function bindSessionSelect($el) {
    if (!$el.length) return;
    $el.on('change', function () {
      postWorkspaceChange('<?= base_url('admin/ajax/select-session') ?>', { session_id: $(this).val() });
    });
  }

  $(function () {
    bindCampusSelect($('#campusID'));
    bindCampusSelect($('#campusIDMobile'));
    bindCampusSelect($('.workspace-campus-select'));

    bindSessionSelect($('#sessionID'));
    bindSessionSelect($('#sessionIDMobile'));
    bindSessionSelect($('.workspace-session-select'));

    /* Native sidebar scroll — OverlayScrollbars breaks fixed flex layout on scroll */
    if ($('body').hasClass('admin-shell-active')) {
      var $sidebar = $('.main-sidebar .sidebar');
      setTimeout(function () {
        if (!$sidebar.length || typeof $sidebar.overlayScrollbars !== 'function') {
          return;
        }
        try {
          var instance = $sidebar.overlayScrollbars();
          if (instance) {
            $sidebar.overlayScrollbars('destroy');
          }
        } catch (e) { /* ignore */ }
      }, 50);
    }

    var $toggle = $('#adminWorkspaceToggle');
    var $panel  = $('#adminWorkspacePanel');

    $toggle.on('click', function () {
      var open = $panel.hasClass('is-open');
      $panel.toggleClass('is-open', !open);
      $toggle.attr('aria-expanded', open ? 'false' : 'true');
      $panel.attr('aria-hidden', open ? 'true' : 'false');
    });

    $(document).on('click', function (e) {
      if (!$panel.hasClass('is-open')) return;
      if ($(e.target).closest('#adminWorkspacePanel, #adminWorkspaceToggle').length) return;
      $panel.removeClass('is-open');
      $toggle.attr('aria-expanded', 'false');
      $panel.attr('aria-hidden', 'true');
    });
  });
})(jQuery);
</script>
