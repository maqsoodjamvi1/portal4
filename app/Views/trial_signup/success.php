<?php
$pageTitle = 'Trial Created — ' . esc($productName);
$loadIntlTel = false;
$itiBase = '';
$embed = ! empty($embed);
?>
<?= view('trial_signup/_head', compact('pageTitle', 'productName', 'itiBase', 'loadIntlTel', 'embed')) ?>

<div class="trial-wrap d-flex align-items-center" style="min-height:calc(100vh - 80px);">
  <div class="trial-verify-card text-center w-100">
    <div style="font-size:3.5rem;color:#28a745;margin-bottom:1rem;"><i class="fas fa-check-circle"></i></div>
    <h2 class="mb-2">Your trial is ready!</h2>
    <?php if (! empty($autoLoginFailed)) : ?>
    <div class="alert alert-info text-start mb-3">
      Your school account was created successfully. Sign in below with your email and the password you chose during signup.
    </div>
    <?php endif; ?>
    <p class="subtitle mb-4">
      <strong><?= esc($schoolName) ?></strong> has been created with a
      <?= (int) $expiryDays ?>-day free trial.
    </p>

    <div class="text-start p-3 mb-4" style="background:#f4f8fc;border-radius:12px;font-size:1.0625rem;">
      <p class="mb-2"><span class="text-muted d-block" style="font-size:0.875rem;font-weight:600;text-transform:uppercase;">Login URL</span>
        <a href="<?= esc($loginUrl) ?>"><?= esc($loginUrl) ?></a></p>
      <p class="mb-2"><span class="text-muted d-block" style="font-size:0.875rem;font-weight:600;text-transform:uppercase;">Username</span>
        <?= esc($username) ?></p>
      <p class="mb-0"><span class="text-muted d-block" style="font-size:0.875rem;font-weight:600;text-transform:uppercase;">Email</span>
        <?= esc($email) ?></p>
    </div>

    <p class="field-hint mb-4">Use the password you chose during signup.</p>

    <a href="<?= esc($loginUrl) ?>" class="btn btn-trial-primary d-inline-block" style="width:auto;min-width:220px;padding-left:2rem;padding-right:2rem;" id="trialSuccessLoginBtn">
      <i class="fas fa-sign-in-alt me-1"></i> Go to login
    </a>
  </div>
</div>
<?php if ($embed) : ?>
<script>
(function () {
  if (window.self === window.top) return;
  var loginUrl = <?= json_encode($loginUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  try { window.top.location.href = loginUrl; } catch (e) {
    var btn = document.getElementById('trialSuccessLoginBtn');
    if (btn) btn.setAttribute('target', '_top');
  }
})();
</script>
<?php endif; ?>
</body>
</html>
