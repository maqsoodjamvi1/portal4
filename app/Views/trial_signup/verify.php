<?php
$pageTitle = esc($productName) . ' — Verify Email';
$loadIntlTel = false;
$itiBase = '';
$embed = ! empty($embed);
?>
<?= view('trial_signup/_head', compact('pageTitle', 'productName', 'itiBase', 'loadIntlTel', 'embed')) ?>

<div class="trial-wrap<?= $embed ? ' trial-wrap--embed' : '' ?>">
  <?php if (! $embed) : ?>
  <div class="trial-hero">
    <h1>Verify your <span class="highlight">email</span></h1>
    <p class="lead">One last step before we create your school workspace.</p>
  </div>
  <?php endif; ?>

  <div class="trial-verify-card">
    <h2>Enter verification code</h2>
    <p class="subtitle">We sent a 6-digit code to your inbox. Enter it below (check spam if you don&rsquo;t see it).</p>

    <div class="trial-email-badge"><i class="fas fa-envelope"></i> <?= esc($maskedEmail ?? '') ?></div>

    <?php if (! empty($error)) : ?>
      <div class="alert alert-danger"><?= esc($error) ?></div>
    <?php endif; ?>
    <?php if (! empty($success)) : ?>
      <div class="alert alert-success"><?= esc($success) ?></div>
    <?php endif; ?>

    <?= form_open(base_url('signup/verify'), ['id' => 'verifyForm', 'class' => 'trial-form', 'autocomplete' => 'off']) ?>
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= esc($token) ?>">
    <?php if ($embed) : ?><input type="hidden" name="embed" value="1"><?php endif; ?>

    <div class="form-group">
      <label class="form-label" for="otp_code">6-digit code</label>
      <input type="text" class="form-control trial-otp-input" id="otp_code" name="otp_code"
             inputmode="numeric" pattern="[0-9]*" maxlength="6" minlength="6"
             placeholder="000000" required autofocus autocomplete="one-time-code">
    </div>

    <button type="submit" class="btn btn-trial-primary">Verify &amp; create school</button>
    <?= form_close() ?>

    <div class="trial-resend-row">
      Didn&rsquo;t receive the code?
      <?= form_open(base_url('signup/resend'), ['id' => 'resendForm', 'class' => 'd-inline']) ?>
      <?= csrf_field() ?>
      <input type="hidden" name="token" value="<?= esc($token) ?>">
      <button type="submit" class="btn btn-link btn-sm p-0 align-baseline fw-bold" id="resendBtn">Resend code</button>
      <?= form_close() ?>
      <span id="resendTimer" class="text-muted" style="display:none;"></span>
    </div>

    <a href="<?= base_url('signup') ?>" class="trial-back-link"><i class="fas fa-arrow-left me-1"></i> Use a different email</a>
  </div>

  <p class="trial-site-footer mb-0">&copy; <?= date('Y') ?> TIME Soft Solution</p>
</div>

<script>
(function () {
  var cooldown = <?= (int) ($resendCooldown ?? 60) ?>;
  var resendBtn = document.getElementById('resendBtn');
  var resendTimer = document.getElementById('resendTimer');
  var remaining = cooldown;
  function tick() {
    if (remaining <= 0) { resendBtn.disabled = false; resendTimer.style.display = 'none'; return; }
    resendBtn.disabled = true;
    resendTimer.style.display = 'inline';
    resendTimer.textContent = ' (wait ' + remaining + 's)';
    remaining--;
    setTimeout(tick, 1000);
  }
  tick();
  document.getElementById('otp_code').addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '').slice(0, 6);
  });
})();
</script>
</body>
</html>
