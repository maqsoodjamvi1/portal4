<script type="text/javascript">
(function ($) {
  'use strict';

  if (!$('body').hasClass('admin-shell-active')) {
    return;
  }

  var MOBILE_NAV_MQ = window.matchMedia('(max-width: 991.98px)');

  function isMobileNav() {
    return MOBILE_NAV_MQ.matches;
  }

  function openMobileNav() {
    $('body').addClass('admin-mobile-nav-open sidebar-open').removeClass('sidebar-collapse sidebar-closed');
    if (!$('.admin-mobile-nav-backdrop').length) {
      $('<div class="admin-mobile-nav-backdrop" aria-hidden="true"></div>').appendTo('body');
    }
  }

  function closeMobileNav() {
    $('body').removeClass('admin-mobile-nav-open sidebar-open').addClass('sidebar-collapse');
    $('.admin-mobile-nav-backdrop').remove();
  }

  function toggleMobileNav() {
    if ($('body').hasClass('admin-mobile-nav-open')) {
      closeMobileNav();
    } else {
      openMobileNav();
    }
  }

  window.adminMobileNav = { open: openMobileNav, close: closeMobileNav, toggle: toggleMobileNav };

  /* Capture phase — before AdminLTE PushMenu */
  document.addEventListener('click', function (e) {
    var btn = e.target && e.target.closest ? e.target.closest('[data-widget="pushmenu"]') : null;
    if (!btn || !isMobileNav()) {
      return;
    }
    e.preventDefault();
    e.stopImmediatePropagation();
    toggleMobileNav();
  }, true);

  $(function () {
    if (isMobileNav()) {
      closeMobileNav();
    }

    $(document).on('click', '.admin-mobile-nav-backdrop', closeMobileNav);

    $(document).on('click', '.main-sidebar a.nav-link', function () {
      if (!isMobileNav()) {
        return;
      }
      var href = ($(this).attr('href') || '').trim();
      if (href && href !== '#' && !/^javascript:/i.test(href) && !/^#[^/]*$/i.test(href)) {
        closeMobileNav();
      }
    });

    $(window).on('resize', function () {
      if (!isMobileNav()) {
        closeMobileNav();
      }
    });

    if (typeof MOBILE_NAV_MQ.addEventListener === 'function') {
      MOBILE_NAV_MQ.addEventListener('change', function (ev) {
        if (!ev.matches) {
          closeMobileNav();
        }
      });
    }
  });
})(jQuery);
</script>
