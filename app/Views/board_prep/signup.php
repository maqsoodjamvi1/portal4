<?= $this->extend('board_prep/layout') ?>
<?= $this->section('content') ?>
<?php
$fieldErrors = is_array($errors ?? null) ? $errors : [];
?>
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="board-prep-card">
        <div class="card-head d-flex justify-content-between align-items-center">
          <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Create your prep account</h4>
          <a href="<?= board_prep_url('login') ?>" class="text-white small">Already registered?</a>
        </div>
        <div class="p-4">
          <?php if (! empty($error)) : ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>
          <?php if ($fieldErrors !== []) : ?>
            <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($fieldErrors as $msg) : ?><li><?= esc($msg) ?></li><?php endforeach; ?></ul></div>
          <?php endif; ?>

          <?= form_open(board_prep_url('signup/submit'), ['autocomplete' => 'off']) ?>
          <?= csrf_field() ?>
          <div class="row">
            <div class="form-group col-md-6">
              <label>Your name <span class="text-danger">*</span></label>
              <input type="text" name="display_name" class="form-control" required maxlength="100" value="<?= esc(old('display_name')) ?>">
            </div>
            <div class="form-group col-md-6">
              <label>Father name <span class="text-danger">*</span></label>
              <input type="text" name="father_name" class="form-control" required maxlength="100" value="<?= esc(old('father_name')) ?>">
            </div>
          </div>
          <div class="row">
            <div class="form-group col-md-6">
              <label>Username <span class="text-danger">*</span></label>
              <input type="text" name="username" class="form-control" required minlength="3" maxlength="32" pattern="[A-Za-z0-9._-]+" value="<?= esc(old('username')) ?>">
              <small class="text-muted">Letters, numbers, dot, dash, underscore</small>
            </div>
            <div class="form-group col-md-6">
              <label>Class <span class="text-danger">*</span></label>
              <select name="grade_level" class="form-control" required>
                <option value="">— Select —</option>
                <?php foreach ($gradeLabels as $key => $label) : ?>
                  <option value="<?= esc($key) ?>" <?= old('grade_level') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Board <span class="text-danger">*</span></label>
            <select name="board_publisher_id" class="form-control" required>
              <option value="">— Select your board —</option>
              <?php foreach ($boards as $board) : ?>
                <option value="<?= (int) $board->id ?>" <?= (string) old('board_publisher_id') === (string) $board->id ? 'selected' : '' ?>><?= esc($board->name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="row">
            <div class="form-group col-md-6">
              <label>Password <span class="text-danger">*</span></label>
              <input type="password" name="password" class="form-control" required minlength="8" maxlength="64">
            </div>
            <div class="form-group col-md-6">
              <label>Confirm password <span class="text-danger">*</span></label>
              <input type="password" name="repassword" class="form-control" required minlength="8" maxlength="64">
            </div>
          </div>
          <div class="form-group">
            <label>Security code <span class="text-danger">*</span></label>
            <div class="d-flex align-items-center">
              <input type="text" name="captcha" class="form-control me-2" required maxlength="8" autocomplete="off" style="max-width:140px">
              <img src="<?= board_prep_url('api/captcha') ?>?t=<?= time() ?>" alt="Captcha" class="captcha-img" id="bpCaptcha" width="120" height="40" title="Click to refresh">
            </div>
          </div>
          <button type="submit" class="btn btn-bp-primary btn-lg">Create account</button>
          <?= form_close() ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
document.getElementById('bpCaptcha').addEventListener('click', function () {
  this.src = '<?= board_prep_url('api/captcha') ?>?t=' + Date.now();
});
</script>
<?= $this->endSection() ?>
