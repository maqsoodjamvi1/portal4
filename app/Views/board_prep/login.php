<?= $this->extend('board_prep/layout') ?>
<?= $this->section('content') ?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="board-prep-card">
        <div class="card-head"><h4 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Sign in</h4></div>
        <div class="p-4">
          <?php if (! empty($error)) : ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>
          <?php if (! empty($success)) : ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
          <?= form_open(board_prep_url('login'), ['autocomplete' => 'off']) ?>
          <?= csrf_field() ?>
          <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required value="<?= esc(old('username')) ?>">
          </div>
          <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-bp-primary w-100">Sign in</button>
          <?= form_close() ?>
          <p class="text-center mt-3 mb-0">No account? <a href="<?= board_prep_url('signup') ?>">Sign up free</a></p>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
