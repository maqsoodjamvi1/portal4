<?php
$fieldErrors = is_array($errors ?? null) ? $errors : [];
$hasError = static function (string $field) use ($fieldErrors): bool {
    return isset($fieldErrors[$field]);
};
$invalidClass = static function (string $field) use ($fieldErrors): string {
    return isset($fieldErrors[$field]) ? ' is-invalid' : '';
};
$errorMsg = static function (string $field) use ($fieldErrors): string {
    return esc($fieldErrors[$field] ?? '');
};
$emailVerificationEnabled = ! empty($emailVerificationEnabled);
$itiBase = base_url('resource/intl-tel-input');
$itiCdnBase = 'https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build';
$pageTitle = esc($productName) . ' — Start Your Free Trial';
$loadIntlTel = true;
$embed = ! empty($embed);
?>
<?= view('trial_signup/_head', compact('pageTitle', 'productName', 'itiBase', 'itiCdnBase', 'loadIntlTel', 'embed')) ?>

<div class="trial-wrap<?= $embed ? ' trial-wrap--embed' : '' ?>">
  <?php if (! $embed) : ?>
  <div class="trial-hero">
    <h1><span class="highlight"><?= esc($productName) ?></span></h1>
    <p class="lead">Online school management — students, fees, attendance, exams, and parent portal in one place.</p>
  </div>
  <?php endif; ?>

  <div class="trial-card">
    <div class="trial-card-bar">
      <p class="bar-title"><i class="fas fa-graduation-cap"></i> Start your free trial</p>
      <span class="trial-badge"><i class="fas fa-gift"></i> <?= (int) $trialDays ?> days free</span>
    </div>

    <div class="trial-body">
      <?php if (! $embed) : ?>
      <aside class="trial-aside">
        <div class="trial-chips">
          <span><i class="fas fa-check text-success"></i> Students</span>
          <span><i class="fas fa-check text-success"></i> Fees</span>
          <span><i class="fas fa-check text-success"></i> Attendance</span>
          <span><i class="fas fa-check text-success"></i> Exams</span>
          <span><i class="fas fa-check text-success"></i> Portal</span>
        </div>
        <div class="trial-aside-desktop">
          <h3>Everything your school needs</h3>
          <div class="trial-feature"><i class="fas fa-check-circle"></i><span>Student &amp; staff management</span></div>
          <div class="trial-feature"><i class="fas fa-check-circle"></i><span>Fee collection &amp; challans</span></div>
          <div class="trial-feature"><i class="fas fa-check-circle"></i><span>Attendance &amp; reports</span></div>
          <div class="trial-feature"><i class="fas fa-check-circle"></i><span>Exams, datesheets &amp; results</span></div>
          <div class="trial-feature"><i class="fas fa-check-circle"></i><span>Parent &amp; student portal</span></div>
          <div class="trial-feature"><i class="fas fa-check-circle"></i><span>Timetable &amp; class diary</span></div>
          <p class="trial-aside-foot trial-aside-foot-desktop mb-0"><i class="fas fa-lock me-1"></i> No credit card. Set up in minutes.</p>
        </div>
      </aside>
      <?php endif; ?>

      <div class="trial-main">
        <h2>Create your school account</h2>
        <?php if ($emailVerificationEnabled) : ?>
        <p class="subtitle">Fill in your details below. We&rsquo;ll email you a 6-digit code to verify your address before creating your school.</p>
        <?php else : ?>
        <p class="subtitle">Fill in your details below to start your <?= (int) $trialDays ?>-day trial. Your school workspace will be ready right away.</p>
        <?php endif; ?>

        <?php if (! empty($error)) : ?>
          <div class="alert alert-danger" role="alert"><?= esc($error) ?></div>
        <?php endif; ?>
        <?php if (! empty($fieldErrors)) : ?>
          <div class="alert alert-danger" role="alert">
            <strong>Please fix the following:</strong>
            <ul class="mb-0 ps-3">
              <?php foreach ($fieldErrors as $msg) : ?>
                <li><?= esc($msg) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?= form_open('signup/submit', ['autocomplete' => 'off', 'id' => 'trialSignupForm', 'class' => 'trial-form', 'novalidate' => 'novalidate']) ?>
        <?= csrf_field() ?>
        <?php if ($embed) : ?>
        <input type="hidden" name="embed" value="1">
        <?php endif; ?>

        <div class="form-group">
          <label class="form-label" for="school_name">School name <span class="req">*</span></label>
          <div class="input-icon-group">
            <span class="input-icon"><i class="fas fa-school"></i></span>
            <input type="text" class="form-control<?= $invalidClass('school_name') ?>" id="school_name" name="school_name"
                   placeholder="e.g. Sunrise Public School" required value="<?= esc(old('school_name')) ?>">
          </div>
          <?php if ($hasError('school_name')) : ?><div class="invalid-feedback d-block"><?= $errorMsg('school_name') ?></div><?php endif; ?>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label class="form-label" for="first_name">First name <span class="req">*</span></label>
            <div class="input-icon-group">
              <span class="input-icon"><i class="fas fa-user"></i></span>
              <input type="text" class="form-control<?= $invalidClass('first_name') ?>" id="first_name" name="first_name"
                     placeholder="First name" required value="<?= esc(old('first_name')) ?>">
            </div>
            <?php if ($hasError('first_name')) : ?><div class="invalid-feedback d-block"><?= $errorMsg('first_name') ?></div><?php endif; ?>
          </div>
          <div class="form-group col-md-6">
            <label class="form-label" for="last_name">Last name <span class="req">*</span></label>
            <div class="input-icon-group">
              <span class="input-icon"><i class="fas fa-user"></i></span>
              <input type="text" class="form-control<?= $invalidClass('last_name') ?>" id="last_name" name="last_name"
                     placeholder="Last name" required value="<?= esc(old('last_name')) ?>">
            </div>
            <?php if ($hasError('last_name')) : ?><div class="invalid-feedback d-block"><?= $errorMsg('last_name') ?></div><?php endif; ?>
          </div>
        </div>

        <div class="form-group phone-wrap">
          <label class="form-label" for="phone_input">Phone number <span class="req">*</span></label>
          <div id="phoneIntlWrap">
            <input type="tel" class="form-control<?= $invalidClass('phone_no') ?>" id="phone_input"
                   autocomplete="tel" inputmode="tel" required value="<?= esc(old('phone_no')) ?>">
          </div>
          <div id="phoneFallbackWrap" class="phone-fallback-row">
            <select id="phone_dial_fallback" class="dial-select" aria-label="Country code">
              <option value="+92" selected>+92 PK</option>
              <option value="+1">+1 US</option>
              <option value="+971">+971 AE</option>
              <option value="+966">+966 SA</option>
              <option value="+44">+44 UK</option>
            </select>
            <input type="tel" id="phone_fallback_input" class="phone-fallback-input"
                   placeholder="0300-5340592" inputmode="tel" autocomplete="tel">
          </div>
          <input type="hidden" name="phone_no" id="phone_no" value="<?= esc(old('phone_no')) ?>">
          <p class="field-hint">Local (0300…) or international — pick your country with the flag.</p>
          <div class="invalid-feedback d-block" id="phone_client_error" style="display:none;"></div>
          <?php if ($hasError('phone_no')) : ?><div class="invalid-feedback d-block"><?= $errorMsg('phone_no') ?></div><?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="email">Email <span class="req">*</span></label>
          <div class="input-icon-group">
            <span class="input-icon"><i class="fas fa-envelope"></i></span>
            <input type="email" class="form-control<?= $invalidClass('email') ?>" id="email" name="email"
                   placeholder="you@school.edu" required maxlength="254" value="<?= esc(old('email')) ?>">
          </div>
          <?php if ($hasError('email')) : ?>
            <div class="invalid-feedback d-block"><?= $errorMsg('email') ?></div>
          <?php elseif ($emailVerificationEnabled) : ?>
            <p class="field-hint">A 6-digit verification code will be sent to this address.</p>
          <?php endif; ?>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label class="form-label" for="password">Password <span class="req">*</span></label>
            <div class="input-icon-group password-wrap">
              <span class="input-icon"><i class="fas fa-lock"></i></span>
              <input type="password" class="form-control<?= $invalidClass('password') ?>" id="password" name="password"
                     placeholder="8–15 characters" required minlength="8" maxlength="15">
              <button type="button" class="password-toggle" data-bs-target="password" aria-label="Show password"><i class="fas fa-eye"></i></button>
            </div>
            <?php if ($hasError('password')) : ?><div class="invalid-feedback d-block"><?= $errorMsg('password') ?></div><?php endif; ?>
          </div>
          <div class="form-group col-md-6">
            <label class="form-label" for="repassword">Confirm password <span class="req">*</span></label>
            <div class="input-icon-group password-wrap">
              <span class="input-icon"><i class="fas fa-lock"></i></span>
              <input type="password" class="form-control<?= $invalidClass('repassword') ?>" id="repassword" name="repassword"
                     placeholder="Repeat password" required minlength="8" maxlength="15">
              <button type="button" class="password-toggle" data-bs-target="repassword" aria-label="Show password"><i class="fas fa-eye"></i></button>
            </div>
            <?php if ($hasError('repassword')) : ?><div class="invalid-feedback d-block"><?= $errorMsg('repassword') ?></div><?php endif; ?>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="captcha_code">Security code <span class="req">*</span></label>
          <div class="captcha-row">
            <div class="captcha-input-wrap">
              <input type="text" class="form-control" id="captcha_code" name="captcha_code"
                     placeholder="Enter code from image" required autocomplete="off">
            </div>
            <img src="<?= site_url('api/captcha') ?>" id="captchaImg" class="captcha-img" alt="Captcha" title="Click to refresh">
          </div>
          <p class="captcha-hint mb-0"><i class="fas fa-sync-alt me-1"></i> Can&rsquo;t read it? Click the image to refresh.</p>
        </div>

        <button type="submit" class="btn btn-trial-primary" id="submitBtn">
          <i class="fas fa-rocket me-1"></i>
          <?= $emailVerificationEnabled ? 'Continue — verify email' : 'Start free trial' ?>
        </button>

        <?= form_close() ?>

        <p class="trial-login-link mb-0">
          Already have an account? <a href="<?= base_url('admin/login') ?>">Sign in</a>
        </p>
      </div>
    </div>
  </div>

  <?php if (! $embed) : ?>
  <p class="trial-site-footer mb-0">&copy; <?= date('Y') ?> TIME Soft Solution — Islamabad, Pakistan</p>
  <?php endif; ?>
</div>

<script src="<?= esc($itiBase) ?>/js/intlTelInput.min.js" id="itiScriptLocal"
        data-cdn="<?= esc($itiCdnBase) ?>/js/intlTelInput.min.js"></script>
<script>
(function () {
  var ITI_BASE = <?= json_encode($itiBase, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  var ITI_CDN = <?= json_encode($itiCdnBase, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  var phoneInput = document.getElementById('phone_input');
  var phoneHidden = document.getElementById('phone_no');
  var phoneIntlWrap = document.getElementById('phoneIntlWrap');
  var phoneFallbackWrap = document.getElementById('phoneFallbackWrap');
  var phoneFallbackInput = document.getElementById('phone_fallback_input');
  var phoneDialFallback = document.getElementById('phone_dial_fallback');
  var form = document.getElementById('trialSignupForm');
  var captchaImg = document.getElementById('captchaImg');
  var submitBtn = document.getElementById('submitBtn');
  var phoneErrorEl = document.getElementById('phone_client_error');
  var iti = null, useFallback = false;
  var oldPhone = <?= json_encode(old('phone_no') ?: '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  var submitBusy = <?= $emailVerificationEnabled ? json_encode('Sending code…') : json_encode('Creating your school…') ?>;

  function showPhoneError(msg) {
    phoneErrorEl.textContent = msg;
    phoneErrorEl.style.display = 'block';
    (useFallback ? phoneFallbackInput : phoneInput).classList.add('is-invalid');
  }
  function clearPhoneError() {
    phoneErrorEl.style.display = 'none';
    phoneInput.classList.remove('is-invalid');
    phoneFallbackInput.classList.remove('is-invalid');
  }
  function formatPkLocal(digits) {
    digits = digits.replace(/\D/g, '');
    if (digits.charAt(0) === '0' && digits.length <= 11) {
      return digits.length <= 4 ? digits : digits.slice(0, 4) + '-' + digits.slice(4);
    }
    return digits;
  }
  function syncPhoneHidden() {
    if (useFallback) {
      var dial = phoneDialFallback.value || '+92';
      var digits = (phoneFallbackInput.value || '').replace(/\D/g, '');
      phoneHidden.value = (dial === '+92' && digits.charAt(0) === '0') ? digits : (digits ? dial + digits.replace(/^0+/, '') : '');
      return phoneHidden.value;
    }
    if (!iti) return phoneInput.value || '';
    var e164 = iti.getNumber();
    if (e164) { phoneHidden.value = e164; return e164; }
    var dial = (iti.getSelectedCountryData().dialCode || '');
    var digits = (phoneInput.value || '').replace(/\D/g, '');
    phoneHidden.value = digits.charAt(0) === '0' ? digits : (dial ? '+' + dial + digits : digits);
    return phoneHidden.value;
  }
  function activateFallback() {
    useFallback = true;
    phoneIntlWrap.style.display = 'none';
    phoneInput.removeAttribute('required');
    phoneFallbackWrap.classList.add('is-active');
    phoneFallbackInput.setAttribute('required', 'required');
    if (oldPhone) {
      if (oldPhone.indexOf('+') === 0) {
        var m = oldPhone.match(/^(\+\d{1,4})(\d+)$/);
        if (m) { phoneDialFallback.value = m[1]; phoneFallbackInput.value = formatPkLocal(m[2]); }
        else phoneFallbackInput.value = oldPhone;
      } else phoneFallbackInput.value = formatPkLocal(oldPhone);
    }
  }
  function initIntlTel(utilsBase) {
    if (typeof window.intlTelInput !== 'function') {
      activateFallback();
      return;
    }
    iti = window.intlTelInput(phoneInput, {
      initialCountry: 'pk', preferredCountries: ['pk','ae','sa','gb','us'],
      separateDialCode: true, nationalMode: true, autoPlaceholder: 'aggressive',
      utilsScript: utilsBase + '/js/utils.js'
    });
    if (oldPhone) iti.setNumber(oldPhone);
    phoneInput.addEventListener('blur', syncPhoneHidden);
    phoneInput.addEventListener('countrychange', function () { clearPhoneError(); syncPhoneHidden(); });
    phoneInput.addEventListener('input', clearPhoneError);
    syncPhoneHidden();
  }
  if (typeof window.intlTelInput === 'function') {
    initIntlTel(ITI_BASE);
  } else {
    var cdnScript = document.createElement('script');
    cdnScript.src = ITI_CDN + '/js/intlTelInput.min.js';
    cdnScript.onload = function () { initIntlTel(ITI_CDN); };
    cdnScript.onerror = activateFallback;
    document.head.appendChild(cdnScript);
  }
  phoneFallbackInput.addEventListener('input', function () {
    clearPhoneError();
    if ((phoneDialFallback.value || '+92') === '+92') phoneFallbackInput.value = formatPkLocal(phoneFallbackInput.value);
    syncPhoneHidden();
  });
  phoneDialFallback.addEventListener('change', function () { clearPhoneError(); syncPhoneHidden(); });
  if (captchaImg) captchaImg.addEventListener('click', function () { this.src = <?= json_encode(site_url('api/captcha')) ?> + '?_t=' + Date.now(); });
  <?php if (! empty($error) && stripos((string) $error, 'captcha') !== false) : ?>
  if (captchaImg) captchaImg.click();
  <?php endif; ?>
  document.querySelectorAll('.password-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var t = document.getElementById(btn.getAttribute('data-target'));
      var i = btn.querySelector('i');
      if (t.type === 'password') { t.type = 'text'; i.classList.replace('fa-eye','fa-eye-slash'); }
      else { t.type = 'password'; i.classList.replace('fa-eye-slash','fa-eye'); }
    });
  });
  form.addEventListener('submit', function (e) {
    clearPhoneError();
    var digits = (syncPhoneHidden() || '').replace(/\D/g, '');
    if (digits.length < 10 || digits.length > 15) {
      e.preventDefault();
      showPhoneError('Enter a valid phone number (10–15 digits).');
      (useFallback ? phoneFallbackInput : phoneInput).focus();
      return;
    }
    if (!useFallback && window.intlTelInputUtils && iti && !iti.isValidNumber()) {
      e.preventDefault();
      showPhoneError('This phone number does not look valid for the selected country.');
      phoneInput.focus();
      return;
    }
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> ' + submitBusy;
  });
})();
</script>
</body>
</html>
