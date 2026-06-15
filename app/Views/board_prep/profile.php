<?= $this->extend('board_prep/app_layout') ?>

<?= $this->section('main') ?>

<h2 class="mb-1">My profile</h2>
<p class="text-muted mb-4">Update your personal details for board exam prep.</p>

<div class="row">
  <div class="col-lg-8">
    <div class="board-prep-card">
      <div class="card-head">
        <h4 class="mb-0"><i class="fas fa-user-edit me-2"></i>Profile details</h4>
      </div>
      <div class="p-4">
        <?= form_open_multipart(board_prep_url('profile/update')) ?>
        <?= csrf_field() ?>

        <div class="row">
          <div class="form-group col-md-6">
            <label class="text-muted small mb-1">Your name</label>
            <input type="text" class="form-control" value="<?= esc($profile->display_name ?? '') ?>" readonly disabled>
          </div>
          <div class="form-group col-md-6">
            <label class="text-muted small mb-1">Username</label>
            <input type="text" class="form-control" value="<?= esc($profile->username ?? '') ?>" readonly disabled>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label class="text-muted small mb-1">Class / grade</label>
            <input type="text" class="form-control" value="<?= esc(board_prep_grade_label($profile->grade_level ?? '')) ?>" readonly disabled>
          </div>
          <div class="form-group col-md-6">
            <label class="text-muted small mb-1">Board</label>
            <input type="text" class="form-control" value="<?= esc($profile->board_name ?? '') ?>" readonly disabled>
          </div>
        </div>

        <hr>

        <div class="form-group text-center mb-4">
          <img src="<?= esc(getStudentPhotoUrl($profile->profile_photo ?? '')) ?>"
               alt="Profile photo"
               class="rounded-circle border"
               style="width:120px;height:120px;object-fit:cover;">
          <div class="mt-2">
            <label class="btn btn-sm btn-outline-secondary mb-0">
              <i class="fas fa-camera me-1"></i> Change photo
              <input type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp" class="d-none">
            </label>
            <small class="d-block text-muted mt-1">JPG or PNG, max 2 MB</small>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label for="father_name">Father name <span class="text-danger">*</span></label>
            <input type="text" name="father_name" id="father_name" class="form-control" required maxlength="100"
                   value="<?= esc(old('father_name', $profile->father_name ?? '')) ?>">
          </div>
          <div class="form-group col-md-6">
            <label for="phone">Phone number</label>
            <input type="text" name="phone" id="phone" class="form-control" maxlength="32"
                   value="<?= esc(old('phone', $profile->phone ?? '')) ?>">
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label for="date_of_birth">Date of birth</label>
            <?php
              $dob = old('date_of_birth', $profile->date_of_birth ?? '');
              if ($dob && preg_match('/^\d{4}-\d{2}-\d{2}/', (string) $dob)) {
                  $dob = substr((string) $dob, 0, 10);
              } else {
                  $dob = '';
              }
            ?>
            <input type="date" name="date_of_birth" id="date_of_birth" class="form-control" value="<?= esc($dob) ?>">
          </div>
          <div class="form-group col-md-6">
            <label for="school_name">School / college name</label>
            <input type="text" name="school_name" id="school_name" class="form-control" maxlength="191"
                   value="<?= esc(old('school_name', $profile->school_name ?? '')) ?>">
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-4">
            <label for="city">City</label>
            <input type="text" name="city" id="city" class="form-control" maxlength="100"
                   value="<?= esc(old('city', $profile->city ?? '')) ?>">
          </div>
          <div class="form-group col-md-4">
            <label for="province">Province</label>
            <input type="text" name="province" id="province" class="form-control" maxlength="100"
                   value="<?= esc(old('province', $profile->province ?? '')) ?>">
          </div>
          <div class="form-group col-md-4">
            <label for="country">Country</label>
            <input type="text" name="country" id="country" class="form-control" maxlength="100"
                   value="<?= esc(old('country', $profile->country ?? 'Pakistan')) ?>">
          </div>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
          <a href="<?= board_prep_url('dashboard') ?>" class="btn btn-outline-secondary">Back to dashboard</a>
          <button type="submit" class="btn btn-bp-primary"><i class="fas fa-save me-1"></i> Save profile</button>
        </div>

        <?= form_close() ?>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
