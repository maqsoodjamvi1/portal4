<?php
/** @var CodeIgniter\HTTP\IncomingRequest $request */
$request   = service('request');
$session   = session();

$isDemo    = ($request->getServer('HTTP_HOST') === 'demo.timesoftsol.com');
$isTrial   = ($request->getServer('HTTP_HOST') === 'trial.timesoftsol.com');
$isPortal4 = ($request->getServer('HTTP_HOST') === 'portal4.timesoftsol.com');

// For auto-fill (old input > GET > blank)
$username = old('username') ?? $request->getGet('username') ?? '';
$password = $request->getGet('pass') ?? '';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>School | Login</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/fontawesome-free/css/all.min.css') ?>">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- AdminLTE -->
  <link rel="stylesheet" href="<?= base_url('resource/adminlte/dist/css/adminlte.min.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/design-tokens.css?v=20260604') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/components-ui.css?v=20260604') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/school-forms.css?v=20260614b') ?>">
  <!-- Toastr -->
  <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/toastr/toastr.min.css') ?>">

  <style>
    .login-box, .register-box {
      margin: 0 auto;
    }
    .signupbtnsection {
      width: 350px;
      margin: 0 auto;
    }
    .btn-group-lg > .btn, .btn-lg {
      margin-bottom: 5px;
    }
    .form-control {
      height: 30px !important;
    }
  </style>

  <script src="https://www.google.com/recaptcha/api.js"></script>
</head>
<body class="hold-transition login-page login-page-refined">
<div class="container">

  <?= form_open(base_url('admin/login/submit'), ['id' => 'loginform', 'autocomplete' => 'off']) ?>

  <!-- ========== NORMAL LOGIN BOX (HIDDEN ON DEMO) ========== -->
  <div class="login-box" style="<?= $isDemo ? 'display:none;' : 'margin:3% auto;' ?>">
    <div class="card card-outline card-primary">
      <div class="card-header text-center">
        <a href="<?= base_url(); ?>" class="h1">
          TLive Education
          <?php if ($isTrial): ?> <small class="text-muted">Trial</small><?php endif; ?>
          <?php if ($isDemo): ?> <small class="text-muted">Demo</small><?php endif; ?>
        </a>
      </div>

      <div class="card-body">
        <p class="login-box-msg">Sign in</p>

        <?php
        $sessionExpired = $request->getGet('reason') === 'session_expired'
            || $session->getFlashdata('session_expired');
        $loginRequired = $request->getGet('reason') === 'login_required'
            || $session->getFlashdata('login_required');
        ?>
        <?php if ($sessionExpired): ?>
          <div class="alert alert-info alert-dismissible fade show">
            <i class="fas fa-info-circle me-2"></i>
            <?= esc($session->getFlashdata('session_expired') ?: 'Your session has expired. Please sign in again to continue.') ?>
            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        <?php elseif ($loginRequired): ?>
          <div class="alert alert-info alert-dismissible fade show">
            <i class="fas fa-sign-in-alt me-2"></i>
            <?= esc($session->getFlashdata('login_required') ?: 'Please sign in to continue.') ?>
            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        <?php endif; ?>

        <!-- Flash error (non-AJAX fallback) -->
        <?php if ($session->getFlashdata('perr')): ?>
          <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= esc($session->getFlashdata('perr')) ?>
            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        <?php endif; ?>

        <!-- Username -->
        <div class="form-group">
          <div class="input-group mb-1">
            <input type="text"
                   class="form-control"
                   required
                   name="username"
                   id="username"
                   placeholder="Email / Username"
                   value="<?= esc($username) ?>">
            <span class="input-group-text">
                <span class="fas fa-user"></span>
              </span>
          </div>
          <div class="text-danger" id="usernameerror"></div>
        </div>

        <!-- Password -->
        <div class="form-group">
          <div class="input-group mb-1">
            <input type="password"
                   class="form-control"
                   required
                   name="password"
                   id="password"
                   placeholder="Password"
                   value="<?= esc($password) ?>">
            <span class="input-group-text">
                <span class="fas fa-lock"></span>
              </span>
          </div>
          <div class="text-danger" id="passworderror"></div>
        </div>

        <div class="row">
          <div class="col-8"></div>
          <div class="col-4">
            <button type="submit" class="btn btn-primary w-100 btn-flat signin">
              Sign in
            </button>
          </div>
        </div>

        <?php if (! $isDemo): ?>
          <div class="mt-3">
            <a href="<?= base_url('admin/login/findpassword'); ?>">Forgot Password?</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ========== DEMO MODE BLOCK ========== -->
  <?php if ($isDemo): ?>
    <section class="mt-4">
      <h1 class="text-center">TLive Education Demo</h1>
      <h2 class="text-center">Just Enter Contact Information And Click On Any Role Button</h2>
      <h3 class="text-center">No need of username and password</h3>
      <br>
      <div class="row">
        <div class="col-lg-7 mx-auto">
          <div class="row">
            <div class="col-lg-6 input-group mb-3">
              <input type="text" class="form-control" name="name" id="name" placeholder="Contact Name">
              <div class="text-danger col-12" id="nameerror"></div>
            </div>
            <div class="col-lg-6 input-group mb-3">
              <input type="text" class="form-control" name="phone" id="phone" placeholder="Contact Phone">
              <div class="text-danger col-12" id="phoneerror"></div>
            </div>
          </div>

          <div class="mt-2" role="group" aria-label="School-1">
            <button type="button" class="btn btn-lg btn-info demo-button mb-2" id="system-admin">System Director</button>
            <button type="button" class="btn btn-lg btn-danger demo-button mb-2" id="campus-director">Campus Director</button>
            <button type="button" class="btn btn-lg btn-primary demo-button mb-2" id="director-academic">Director Academic</button>
            <button type="button" class="btn btn-lg btn-success demo-button mb-2" id="director-finance">Director Finance</button>
            <button type="button" class="btn btn-lg btn-warning demo-button mb-2" id="principal">Principal</button>
            <button type="button" class="btn btn-lg btn-info demo-button mb-2" id="teacher">Teacher</button>
          </div>
        </div>
      </div>
    </section>
    <br><br><br>
  <?php endif; ?>

  <!-- (Optional) Signup block for non-portal4 -->
  <?php if (! $isPortal4): ?>
    <br><br>
    <section class="signupbtnsection">
      <center>
        <h3>Create School With Blank Database</h3><br>
        <a href="<?= base_url('signup') ?>" class="btn btn-lg btn-danger w-100">
          Signup For 1 Month Free Trial
        </a>
      </center>
    </section>
  <?php endif; ?>

  <?= form_close(); ?>
</div>

<!-- Scripts -->
<script src="<?= base_url('resource/adminlte/plugins/jquery/jquery.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('resource/js/jquery.form.js') ?>"></script>
<script src="<?= base_url('resource/js/jquery.validate.min.js') ?>"></script>
<script src="<?= base_url('resource/adminlte/plugins/toastr/toastr.min.js') ?>"></script>

<script>
  const BASE_URL = '<?= rtrim(base_url(), '/') ?>/';
</script>

<?php if ($username && $password): ?>
<script>
  // Auto-submit when username & pass are pre-filled via GET
  $(document).ready(function () {
    $('.signin').click();
  });
</script>
<?php endif; ?>

<script>
  // ===== DEMO ROLE BUTTONS FILL USERNAME/PASSWORD =====
  $(function () {
    function quickLogin(username) {
      $('input[name="username"]').val(username);
      $('input[name="password"]').val('123456');
      $('.signin').click();
    }

    $('#system-admin').on('click', function (e) {
      e.preventDefault();
      quickLogin('system-admin');
    });
    $('#campus-director').on('click', function (e) {
      e.preventDefault();
      quickLogin('campus-director');
    });
    $('#director-academic').on('click', function (e) {
      e.preventDefault();
      quickLogin('director-academic');
    });
    $('#director-finance').on('click', function (e) {
      e.preventDefault();
      quickLogin('director-finance');
    });
    $('#principal').on('click', function (e) {
      e.preventDefault();
      quickLogin('principal');
    });
    $('#teacher').on('click', function (e) {
      e.preventDefault();
      quickLogin('teacher');
    });
  });
</script>

<script>
  $(function () {
    // ========== JQUERY VALIDATE GLOBAL SETTINGS ==========
    $.validator.setDefaults({
      ignore: '',
      errorPlacement: function (error, element) {
        const id = element.attr('id');
        const $holder = $('#' + id + 'error');
        if ($holder.length) {
          error.appendTo($holder);
        } else {
          error.insertAfter(element);
        }
      },
      highlight: function (element) {
        $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
      },
      unhighlight: function (element) {
        $(element).closest('.form-group').removeClass('has-error');
      }
    });

    // Build rules dynamically (name/phone only if present = demo host)
    const rules = {
      username: { required: true },
      password: { required: true }
    };
    const messages = {
      username: { required: 'Please enter username or email' },
      password: { required: 'Please enter password' }
    };

    if ($('#name').length) {
      rules.name = { required: true };
      messages.name = { required: 'Please enter contact name' };
    }
    if ($('#phone').length) {
      rules.phone = { required: true };
      messages.phone = { required: 'Please enter contact phone' };
    }

    $('#loginform').validate({
      rules: rules,
      messages: messages
    });

    // ========== AJAX FORM SUBMIT ==========
    $('#loginform').ajaxForm({
      dataType: 'json',
      beforeSubmit: function () {
        return $('#loginform').valid();
      },
      success: function (json) {
        console.log('Login response:', json);

        // Our optimized controller returns:
        // { success, message, code, redirect }
        if (json.success) {
          const redirectUrl = json.redirect || (BASE_URL + 'admin/dashboard');
          window.location.href = redirectUrl;
        } else {
          const msg = json.message || 'Login failed. Please check your details.';
          toastr.error(msg);
        }

        return false;
      },
      error: function (xhr) {
        console.error('Login error:', xhr.responseText);
        toastr.error('An unexpected error occurred. Please try again.');
      }
    });
  });
</script>

</body>
</html>
