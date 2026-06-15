<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Parent Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= base_url('assets/css/design-tokens.css?v=20260604') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/school-forms.css?v=20260614b') ?>">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?php echo base_url();?>/resource/adminlte/plugins/fontawesome-free/css/all.min.css">

  <style>
    body {
      min-height: 100vh;
      margin: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      font-family: 'Comic Neue', 'Segoe UI', Arial, sans-serif;
    }

    .login-container {
      width: 100%;
      max-width: 380px;
      padding: 15px;
    }

    .login-box {
      background: white;
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      border: 3px solid #ffd166;
    }

    .logo {
      text-align: center;
      margin-bottom: 25px;
    }

    .logo-icon {
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 15px;
      box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
    }

    .logo-icon i {
      font-size: 40px;
      color: white;
    }

    .title {
      text-align: center;
      color: #333;
      font-weight: 700;
      margin-bottom: 5px;
      font-size: 24px;
    }

    .subtitle {
      text-align: center;
      color: #666;
      margin-bottom: 25px;
      font-size: 14px;
    }

    .input-group {
      margin-bottom: 20px;
    }

    .input-label {
      display: block;
      margin-bottom: 8px;
      color: #555;
      font-weight: 600;
      font-size: 14px;
    }

    .cnic-input {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #e0e0e0;
      border-radius: 12px;
      font-size: 16px;
      font-family: monospace;
      letter-spacing: 1px;
      transition: all 0.3s;
      background: #f8f9fa;
    }

    .cnic-input:focus {
      outline: none;
      border-color: #4facfe;
      box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.2);
      background: white;
    }

    .cnic-input::placeholder {
      color: #aaa;
      font-family: inherit;
    }

    .cnic-format {
      font-size: 12px;
      color: #888;
      margin-top: 5px;
      text-align: center;
      font-family: monospace;
    }

    .password-container {
      position: relative;
    }

    .toggle-password {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: #666;
      cursor: pointer;
    }

    .login-btn {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      color: white;
      border: none;
      border-radius: 12px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s;
      margin-top: 10px;
    }

    .login-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
    }

    .login-btn:active {
      transform: translateY(0);
    }

    .help-text {
      text-align: center;
      margin-top: 20px;
      color: #666;
      font-size: 12px;
      line-height: 1.4;
    }

    .kid-friendly {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      background: #ffeaa7;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
      color: #333;
      margin-top: 15px;
    }

    .alerts {
      margin-bottom: 20px;
    }

    .alert {
      padding: 12px 15px;
      border-radius: 10px;
      font-size: 14px;
      margin-bottom: 10px;
    }

    .alert-danger {
      background: #ffe6e6;
      border: 1px solid #ffcccc;
      color: #cc0000;
    }

    .alert-success {
      background: #e6ffe6;
      border: 1px solid #ccffcc;
      color: #009900;
    }

    .kid-emoji {
      position: absolute;
      right: -40px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 30px;
      opacity: 0.7;
    }

    @media (max-width: 480px) {
      .login-box {
        padding: 25px 20px;
      }
      
      .kid-emoji {
        display: none;
      }
    }
  </style>
</head>
<body>

<div class="login-container">
  <div class="login-box">
    <div class="logo">
      <div class="logo-icon">
        <i class="fas fa-user-graduate"></i>
      </div>
      <h1 class="title">Parent Login</h1>
      <p class="subtitle">Access your child's school information</p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alerts">
        <div class="alert alert-danger">
          <?= esc(session()->getFlashdata('error')) ?>
        </div>
      </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
      <div class="alerts">
        <div class="alert alert-success">
          <?= esc(session()->getFlashdata('success')) ?>
        </div>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= route_to('login.post') ?>" novalidate>
      <?= csrf_field() ?>
      
      <!-- CNIC Field with Masking -->
      <div class="input-group">
        <label class="input-label">
          <i class="fas fa-id-card"></i> Father's CNIC
        </label>
        <div style="position: relative;">
          <input
            type="text"
            id="cnic"
            name="login"
            class="cnic-input"
            placeholder="35202-1234567-1"
            maxlength="15"
            required
            pattern="\d{5}-\d{7}-\d{1}"
          >
          <div class="kid-emoji">👨‍👧‍👦</div>
        </div>
        <div class="cnic-format">Format: XXXXX-XXXXXXX-X</div>
      </div>

      <!-- Password Field -->
      <div class="input-group">
        <label class="input-label">
          <i class="fas fa-lock"></i> Password
        </label>
        <div class="password-container">
          <input
            type="password"
            id="password"
            name="password"
            class="cnic-input"
            placeholder="Enter your password"
            required
          >
          <button type="button" class="toggle-password" onclick="togglePassword()">
            <i class="fas fa-eye"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="login-btn">
        <i class="fas fa-sign-in-alt"></i> Sign In
      </button>

      <div class="help-text">
        <div class="kid-friendly">
          <i class="fas fa-child"></i> Kid Friendly Login
        </div>
        <p style="margin-top: 15px;">
          Need help? Ask your parents or contact school office.
        </p>
      </div>
    </form>
  </div>
</div>

<script>
  // CNIC Masking Script
  document.getElementById('cnic').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    
    if (value.length > 13) {
      value = value.substring(0, 13);
    }
    
    let formatted = '';
    
    if (value.length > 0) {
      formatted = value.substring(0, 5);
      if (value.length > 5) {
        formatted += '-' + value.substring(5, 12);
        if (value.length > 12) {
          formatted += '-' + value.substring(12, 13);
        }
      }
    }
    
    e.target.value = formatted;
  });

  // Password visibility toggle
  function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.querySelector('.toggle-password i');
    
    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      toggleIcon.classList.remove('fa-eye');
      toggleIcon.classList.add('fa-eye-slash');
    } else {
      passwordInput.type = 'password';
      toggleIcon.classList.remove('fa-eye-slash');
      toggleIcon.classList.add('fa-eye');
    }
  }

  // Auto-format on load if there's existing value
  document.addEventListener('DOMContentLoaded', function() {
    const cnicInput = document.getElementById('cnic');
    if (cnicInput.value) {
      // Trigger formatting if there's a value
      cnicInput.dispatchEvent(new Event('input'));
    }
    
    // Focus on CNIC field
    cnicInput.focus();
  });

  // Validate format before form submission
  document.querySelector('form').addEventListener('submit', function(e) {
    const cnicInput = document.getElementById('cnic');
    const cnicValue = cnicInput.value.replace(/\D/g, '');
    
    if (cnicValue.length !== 13) {
      e.preventDefault();
      alert('Please enter a complete CNIC (13 digits total)');
      cnicInput.focus();
      return false;
    }
  });
</script>

</body>
</html>
