<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Login — Parent/Student</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="<?= base_url('assets/bootstrap.min.css') ?>" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-5">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <h4 class="mb-3 text-center">Parent / Student Login</h4>

            <?php if (session()->getFlashdata('error')): ?>
              <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('success')): ?>
              <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= route_to('login.post') ?>">
              <?= csrf_field() ?>
              <div class="mb-3">
                <label class="form-label">Email / CNIC / Reg Nooo</label>
                <input type="text" name="login" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
              </div>
              <button class="btn btn-primary w-100" type="submit">Sign in</button>
            </form>

          </div>
        </div>
        <p class="text-center mt-3 text-muted small">© <?= date('Y') ?> TIME Soft Solutions</p>
      </div>
    </div>
  </div>
</body>
</html>
