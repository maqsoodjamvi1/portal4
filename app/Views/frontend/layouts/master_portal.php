<?php
$__authNav = session('auth') ?? [];
$portalHubParent = $portalHubParent ?? false;
$__isParentPortal = $portalHubParent || (($__authNav['role'] ?? '') === 'parent');
$__langNav = session('language') ?? 'en';
?><!doctype html>
<html lang="<?= session('language') ?? 'en' ?>" dir="<?= in_array(session('language'), ['ar', 'ur']) ? 'rtl' : 'ltr' ?>" class="<?= $__isParentPortal ? 'parent-portal-html' : '' ?>">
<head>
  <meta charset="utf-8">
  <title><?= esc($title ?? 'Portal') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Google Font -->
  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/fontawesome-free/css/all.min.css') ?>">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap adapter -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
  <!-- jQuery UI theme (for datepicker etc.) -->
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css') ?>">
  <!-- JQVMap -->
  <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/jqvmap/jqvmap.min.css') ?>">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') ?>">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/daterangepicker/daterangepicker.css') ?>">
  <!-- Summernote -->
  <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/summernote/summernote-lite.min.css') ?>">
  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/select2/css/select2.min.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
  <!-- Toastr -->
  <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/toastr/toastr.min.css') ?>">

  <!-- AdminLTE core -->
  <link rel="stylesheet" href="<?= base_url('resource/adminlte/dist/css/adminlte.min.css') ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

  <link rel="stylesheet" href="<?= base_url('assets/js/sweetalert/sweetalert.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/design-tokens.css?v=20260604') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/components-ui.css?v=20260604') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/school-forms.css?v=20260614b') ?>">
  <?php if ($portalHubParent): ?>
  <link rel="stylesheet" href="<?= base_url('assets/css/parent-portal-hub.css?v=20260608') ?>">
  <?php endif; ?>

  <!-- Custom portal layout styling -->
  <style>
    body.quiz-attempt-page .main-header,
    body.quiz-attempt-page .main-sidebar,
    body.quiz-attempt-page .parent-portal-bottomnav {
      display: none !important;
    }
    body.quiz-attempt-page .content-wrapper {
      margin-left: 0 !important;
      padding-top: 0 !important;
      padding-bottom: 0 !important;
      min-height: 100vh;
      min-height: 100dvh;
    }
    @media (max-width: 767.98px) {
      body.quiz-attempt-page .content-wrapper {
        padding-bottom: 0 !important;
      }
    }

    /* Custom Language Dropdown */
#langMenu a:hover {
    background-color: #f0f0f0 !important;
    color: #000 !important;
}

/* Ensure Bootstrap dropdown text is visible */
.dropdown-menu .dropdown-item {
    color: #333 !important;
}

.dropdown-menu .dropdown-item:hover {
    background-color: #e9ecef !important;
    color: #000 !important;
}

.navbar-nav .dropdown-menu {
    background-color: white !important;
}


    html, body {
      height: 100%;
    }
    .wrapper {
      min-height: 100vh;
    }

    /* Top navbar */
    .portal-navbar {
      background: linear-gradient(90deg, #2563eb, #1d4ed8);
      color: #fff;
      padding-top: 0;
      padding-bottom: 0;
      box-shadow: 0 2px 6px rgba(15, 23, 42, 0.25);
    }
    .portal-navbar .navbar-brand-text {
      font-weight: 700;
      letter-spacing: 0.03em;
      font-size: 16px;
    }
    .portal-navbar .nav-link {
      color: #e5e7eb;
    }
    .portal-navbar .nav-link:hover {
      color: #ffffff;
    }

    /* Sidebar full height & styling */
    .main-sidebar {
      background: linear-gradient(180deg, #0f172a, #020617);
      min-height: 100vh;
    }
    .brand-link {
      border-bottom: 1px solid rgba(15, 23, 42, 0.6);
    }
    .brand-link .brand-text {
      font-weight: 600;
      font-size: 15px;
    }

    .sidebar {
      padding-top: 8px;
    }

    .nav-sidebar > .nav-item > .nav-link {
      border-radius: 999px;
      margin: 2px 8px;
      color: #9ca3af;
      font-size: 13px;
      padding-top: 7px;
      padding-bottom: 7px;
      display: flex;
      align-items: center;
    }

    .nav-sidebar > .nav-item > .nav-link i.nav-icon,
    .nav-sidebar > .nav-item > .nav-link > i.fa {
      width: 20px;
      text-align: center;
      font-size: 14px;
      margin-right: 6px;
    }

    .nav-sidebar > .nav-item > .nav-link:hover {
      background: rgba(148, 163, 184, 0.15);
      color: #e5e7eb;
    }

    .nav-sidebar > .nav-item > .nav-link.active {
      background: linear-gradient(90deg, #3b82f6, #22c55e);
      color: #ffffff;
      box-shadow: 0 2px 8px rgba(15, 23, 42, 0.4);
    }

    .nav-sidebar > .nav-item > .nav-link.active i {
      color: #ffffff;
    }

    .nav-sidebar .nav-treeview .nav-link {
      border-radius: 12px;
      margin-left: 32px;
      font-size: 12px;
    }

    /* Content background */
    .content-wrapper {
      background-color: #f3f4f6;
      min-height: 100vh;
    }

    /* ========== RTL Support for Urdu/Arabic ========== */
    .rtl-support {
        direction: rtl;
        text-align: right;
    }

    .rtl-support .ms-auto {
        margin-left: 0 !important;
        margin-right: auto !important;
    }

    .rtl-support .me-3 {
        margin-right: 0 !important;
        margin-left: 1rem !important;
    }

    .rtl-support .me-1 {
        margin-right: 0 !important;
        margin-left: 0.25rem !important;
    }

    .rtl-support .nav-icon {
        margin-right: 0 !important;
        margin-left: 6px !important;
    }

    .rtl-support .dropdown-menu-end {
        left: 0;
        right: auto;
    }

    .rtl-support .sidebar .nav-link p i.right {
        margin-left: 0;
        margin-right: auto;
    }

    .rtl-support .float-end {
        float: left !important;
    }

    .rtl-support .text-end {
        text-align: left !important;
    }

    /* Language Dropdown Styling */
.dropdown-menu {
    background-color: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.dropdown-menu .dropdown-item {
    color: #1e293b !important;
    font-size: 14px;
    padding: 8px 16px;
}

.dropdown-menu .dropdown-item:hover {
    background-color: #f1f5f9;
    color: #2563eb !important;
}

.dropdown-menu .dropdown-item.active {
    background-color: #2563eb;
    color: #ffffff !important;
}

.dropdown-menu .dropdown-divider {
    border-top-color: #e2e8f0;
}

/* For RTL support */
.rtl-support .dropdown-menu {
    text-align: right;
}

.rtl-support .dropdown-item i {
    margin-left: 8px;
    margin-right: 0;
}

/* Navbar dropdown toggle color */
.portal-navbar .dropdown-toggle {
    color: #ffffff !important;
}

.portal-navbar .dropdown-toggle:hover {
    color: #e2e8f0 !important;
}

/* ========== Language Dropdown Styling - FIX ========== */
/* Dropdown menu background and text colors */
.dropdown-menu {
    background-color: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

.dropdown-menu .dropdown-item {
    color: #1e293b !important;
    background-color: transparent !important;
    font-size: 14px;
    padding: 8px 16px;
}

.dropdown-menu .dropdown-item:hover {
    background-color: #f1f5f9 !important;
    color: #2563eb !important;
}

.dropdown-menu .dropdown-item.active {
    background-color: #2563eb !important;
    color: #ffffff !important;
}

.dropdown-menu .dropdown-divider {
    border-top-color: #e2e8f0 !important;
    margin: 4px 0;
}

/* Make the dropdown toggle button text white */
.portal-navbar .dropdown-toggle {
    color: #ffffff !important;
}

.portal-navbar .dropdown-toggle:hover {
    color: #e2e8f0 !important;
}

/* Ensure dropdown menu appears above other content */
.dropdown-menu {
    z-index: 9999 !important;
}

/* For dark backgrounds, ensure text is visible */
.navbar-nav .dropdown-menu {
    background-color: white !important;
}

.navbar-nav .dropdown-menu a {
    color: #333 !important;
}

.navbar-nav .dropdown-menu a:hover {
    background-color: #f0f0f0 !important;
    color: #000 !important;
}
  </style>

  <!-- jQuery -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
  <!-- jQuery UI -->
  <script src="<?= base_url('resource/adminlte/plugins/jquery-ui/jquery-ui.min.js') ?>"></script>
  <script>
    $.widget.bridge('uibutton', $.ui.button);
  </script>
  <!-- Bootstrap 5 + legacy compatibility bridge -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= base_url('assets/js/bootstrap5-compat.js?v=20260614') ?>"></script>
  <!-- AdminLTE App -->
  <script src="<?= base_url('resource/adminlte/dist/js/adminlte.js') ?>"></script>
</head>

<body class="hold-transition sidebar-mini layout-fixed <?= in_array(session('language'), ['ar', 'ur']) ? 'rtl-support' : '' ?> <?= $__isParentPortal ? 'parent-portal-client' : '' ?><?= $portalHubParent ? ' parent-hub-mobile' : '' ?>">
<div class="wrapper">

  <!-- TOP NAVBAR -->
  <nav class="main-header navbar navbar-expand portal-navbar<?= $portalHubParent ? ' portal-navbar--compact' : '' ?>">
    <!-- Left side: menu toggle + title -->
    <ul class="navbar-nav">
      <?php if (! $portalHubParent): ?>
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button">
          <i class="fas fa-bars"></i>
        </a>
      </li>
      <?php else: ?>
      <li class="nav-item d-md-none">
        <span class="nav-link portal-nav-school-short text-white mb-0">
          <?= esc($school_name ?? 'School Portal') ?>
        </span>
      </li>
      <li class="nav-item d-none d-md-inline-block">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button">
          <i class="fas fa-bars"></i>
        </a>
      </li>
      <?php endif; ?>
      <li class="nav-item d-none d-sm-inline-block">
        <span class="nav-link navbar-brand-text">
          <?= esc($school_name ?? 'School Portal') ?>
        </span>
      </li>
    </ul>
<!-- Language Switcher - Custom Dropdown (No Bootstrap) -->
<li class="nav-item me-2" style="position: relative;">
    <a class="nav-link" href="#" onclick="toggleLangMenu(event);" style="color: #ffffff !important;">
        <i class="fa fa-language"></i>
        <span class="d-none d-sm-inline-block">
            <?php 
            $current_lang = session('language') ?? 'en';
            $langNames = [
                'en' => 'English',
                'ur' => 'اردو',
                'ar' => 'العربية'
            ];
            echo $langNames[$current_lang] ?? 'English';
            ?>
        </span>
        <i class="fa fa-caret-down"></i>
    </a>
    <div id="langMenu" style="display: none; position: absolute; top: 35px; right: 0; background: white; border: 1px solid #ddd; border-radius: 5px; min-width: 130px; z-index: 9999; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
        <a href="#" onclick="changeLanguage('en'); return false;" style="display: block; padding: 8px 15px; color: #333; text-decoration: none; border-bottom: 1px solid #eee;">
            <i class="fa fa-flag-us"></i> English
        </a>
        <a href="#" onclick="changeLanguage('ur'); return false;" style="display: block; padding: 8px 15px; color: #333; text-decoration: none; border-bottom: 1px solid #eee;">
            <i class="fa fa-flag"></i> اردو
        </a>
        <a href="#" onclick="changeLanguage('ar'); return false;" style="display: block; padding: 8px 15px; color: #333; text-decoration: none;">
            <i class="fa fa-flag"></i> العربية
        </a>
    </div>
</li>

<script>
function toggleLangMenu(event) {
    event.preventDefault();
    event.stopPropagation();
    var menu = document.getElementById('langMenu');
    if (menu.style.display === 'none' || menu.style.display === '') {
        menu.style.display = 'block';
        // Close when clicking outside
        document.addEventListener('click', function closeMenu(e) {
            if (!menu.contains(e.target) && !e.target.closest('.nav-link')) {
                menu.style.display = 'none';
                document.removeEventListener('click', closeMenu);
            }
        });
    } else {
        menu.style.display = 'none';
    }
}
</script>
      <li class="nav-item d-flex align-items-center portal-nav-hide-mobile">
        <span class="me-3">
          <i class="far fa-user-circle me-1"></i>
          <?= esc($name ?? '') ?>
        </span>
        <a class="btn btn-sm btn-outline-light" href="<?= route_to('logout') ?>">
          <i class="fas fa-sign-out-alt me-1"></i> Logout
        </a>
      </li>
      <?php if ($portalHubParent): ?>
      <li class="nav-item d-md-none">
        <a class="nav-link" href="<?= base_url('student/profile') ?>" title="Profile">
          <i class="far fa-user-circle fa-lg"></i>
        </a>
      </li>
      <?php endif; ?>
    </ul>
  </nav>

  <!-- LEFT SIDEBAR -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand -->
    <a href="<?= base_url() ?>" class="brand-link">
        <span class="brand-text fw-light">
            <?= esc($school_name ?? 'School Name') ?>
        </span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <?php
        $uri  = service('uri');
        $path = '/' . trim($uri->getPath(), '/');

        // Define ALL menu variables with default false values
        $isDashboard  = (strpos($path, '/student/dashboard') === 0);
        $isFees       = (strpos($path, '/student/fees') === 0);
        $isResults    = (strpos($path, '/student/results') === 0);
        $isAttendance = (strpos($path, '/student/attendance') === 0);
        $isDatesheet  = (strpos($path, '/student/datesheet') === 0);
        $isVocabulary = (strpos($path, '/student/vocabbank') === 0);
        $isCrossword  = (strpos($path, '/student/crossword') === 0);
        $isWordSearch = (strpos($path, '/student/word-search') === 0);
        
        // Quizzes variables
        $pendingPath   = '/student/quizzes/pending';
        $attemptedPath = '/student/quizzes/attempted';
        $isPending     = (strpos($path, $pendingPath) === 0);
        $isAttempted   = (strpos($path, $attemptedPath) === 0);
        $isQuizzesRoot = (strpos($path, '/student/quizzes') === 0);
        $isQuizzesTree = $isPending || $isAttempted || $isQuizzesRoot;
        ?>

        <nav class="mt-2">
            <ul id="sidebarMenu"
                class="nav nav-pills nav-sidebar flex-column nav-child-indent text-sm"
                data-widget="treeview" role="menu" data-accordion="true">

                <li class="nav-item">
                    <a href="<?= base_url('student/dashboard') ?>"
                       class="nav-link <?= $isDashboard ? 'active' : '' ?>">
                        <i class="fa fa-home nav-icon"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= base_url('student/fees') ?>"
                       class="nav-link <?= $isFees ? 'active' : '' ?>">
                        <i class="fa fa-credit-card nav-icon"></i>
                        <p>Fee</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= base_url('student/results') ?>"
                       class="nav-link <?= $isResults ? 'active' : '' ?>">
                        <i class="fa fa-clipboard-check nav-icon"></i>
                        <p>Result</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= base_url('student/attendance') ?>"
                       class="nav-link <?= $isAttendance ? 'active' : '' ?>">
                        <i class="fa fa-user-check nav-icon"></i>
                        <p>Attendance</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= base_url('student/datesheet') ?>"
                       class="nav-link <?= $isDatesheet ? 'active' : '' ?>">
                        <i class="fa fa-calendar-alt nav-icon"></i>
                        <p>Datesheet</p>
                    </a>
                </li>

                <!-- Vocabulary Menu Item -->
                <li class="nav-item">
                    <a href="<?= base_url('student/vocabbank') ?>"
                       class="nav-link <?= $isVocabulary ? 'active' : '' ?>">
                        <i class="fas fa-book-open nav-icon"></i>
                        <p>Vocabulary</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= base_url('student/crossword') ?>"
                       class="nav-link <?= $isCrossword ? 'active' : '' ?>">
                        <i class="fas fa-th nav-icon"></i>
                        <p>Crossword</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= base_url('student/word-search') ?>"
                       class="nav-link <?= $isWordSearch ? 'active' : '' ?>">
                        <i class="fas fa-search nav-icon"></i>
                        <p>Word Puzzle</p>
                    </a>
                </li>

                <!-- Quizzes tree -->
                <li class="nav-item has-treeview <?= $isQuizzesTree ? 'menu-open' : '' ?>">
                    <a href="<?= base_url('student/quizzes') ?>"
                       class="nav-link <?= $isQuizzesTree ? 'active' : '' ?>">
                        <i class="fa fa-question-circle nav-icon"></i>
                        <p>
                            Quizzes
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= base_url('student/quizzes/pending') ?>"
                               class="nav-link <?= $isPending ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Pending Quizzes</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('student/quizzes/attempted') ?>"
                               class="nav-link <?= $isAttempted ? 'active' : '' ?>">
                                <i class="far fa-check-circle nav-icon"></i>
                                <p>Attempted Quizzes</p>
                            </a>
                        </li>
                    </ul>
                </li>

            </ul>
        </nav>
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- MAIN CONTENT -->
  <div class="content-wrapper">
    <?= $this->renderSection('content') ?>
  </div>

</div><!-- /.wrapper -->

<?php if ($portalHubParent): ?>
<?= view('frontend/layouts/partials/parent_hub_bottom_nav') ?>
<?php else: ?>
<?= view('frontend/layouts/partials/portal_bottom_nav') ?>
<?php endif; ?>

<!-- Language Switching Script -->
<script>
function changeLanguage(lang) {
    var LANG_URLS = {
        'en': '<?= base_url("language/set/en") ?>',
        'ur': '<?= base_url("language/set/ur") ?>',
        'ar': '<?= base_url("language/set/ar") ?>'
    };
    
    if (!LANG_URLS[lang]) {
        console.error('Language URL not found for:', lang);
        return;
    }
    
    $.ajax({
        url: LANG_URLS[lang],
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                if (lang === 'ur' || lang === 'ar') {
                    $('html').attr('dir', 'rtl');
                    $('body').addClass('rtl-support');
                } else {
                    $('html').attr('dir', 'ltr');
                    $('body').removeClass('rtl-support');
                }
                if (typeof toastr !== 'undefined') {
                    toastr.success(response.message);
                }
                setTimeout(function() {
                    window.location.reload();
                }, 500);
            }
        },
        error: function(xhr, status, error) {
            console.error('Language change failed:', error);
            if (typeof toastr !== 'undefined') {
                toastr.error('Language change failed. Please try again.');
            }
        }
    });
}
</script>

<!-- Toastr Initialization -->
<script>
$(document).ready(function() {
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };
    }
});
</script>

</body>
</html>
