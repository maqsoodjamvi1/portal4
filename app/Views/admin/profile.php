<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- Dependencies used below (already in your stack; keep if not globally loaded) -->
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.6.2/dist/select2-bootstrap4.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script> -->

<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between flex-wrap">
      <div>
        <h1 class="mb-1"><i class="fas fa-user-circle mr-2"></i>Profile</h1>
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Profile</li>
        </ol>
      </div>
      <div class="mt-2 mt-md-0">
        <a href="<?= base_url('/logout');?>" class="btn btn-danger">
          <i class="fas fa-sign-out-alt mr-1"></i> Logout
        </a>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <!-- LEFT: Profile summary -->
      <div class="col-lg-4 col-xl-3 mb-3">
        <div class="card card-primary card-outline h-100 shadow-sm">
          <div class="card-body box-profile">

            <div class="text-center mb-3">
              <?php if (!empty($user->photo)): ?>
                <img id="output" class="profile-user-img img-fluid img-circle"
                     src="<?= base_url('employees-img/'.$user->photo) ?>" alt="User photo">
              <?php else: ?>
                <img id="output" class="profile-user-img img-fluid img-circle"
                     src="<?= base_url('resource/adminlte/dist/img/user4-128x128.jpg') ?>" alt="User photo">
              <?php endif; ?>
            </div>

            <h3 class="profile-username text-center mb-1"><?= esc($user->username) ?></h3>
            <p class="text-muted text-center mb-3"><?= esc(($user->first_name ?? '').' '.($user->last_name ?? '')) ?></p>

            <ul class="list-group list-group-unbordered mb-3">
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><i class="far fa-calendar-plus mr-1"></i>Join time</span><span><?= esc($user->reg_time) ?></span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><i class="fas fa-sign-in-alt mr-1"></i>Login times</span><span><?= esc($user->login_times) ?></span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><i class="far fa-clock mr-1"></i>Cur Login</span><span><?= esc($user->cur_login_time) ?></span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><i class="fas fa-globe mr-1"></i>Cur IP</span><span><?= esc($user->cur_login_ip) ?></span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><i class="far fa-compass mr-1"></i>Cur Area</span><span><?= esc($user->cur_login_area) ?></span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><i class="far fa-clock mr-1"></i>Last Login</span><span><?= esc($user->last_login_time) ?></span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><i class="fas fa-network-wired mr-1"></i>Last IP</span><span><?= esc($user->last_login_ip) ?></span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><i class="far fa-compass mr-1"></i>Last Area</span><span><?= esc($user->last_login_area) ?></span>
              </li>
            </ul>

            <div class="custom-file w-100">
              <input type="file" class="custom-file-input" id="file" name="image" form="user-edit-form"
                     accept="image/*" onchange="loadFile(event)">
              <label class="custom-file-label" for="file"><i class="fa fa-image mr-1"></i>Upload Photo</label>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT: Tabs + Forms -->
      <div class="col-lg-8 col-xl-9">
        <div class="card card-primary card-outline shadow-sm">
          <div class="card-header p-2">
            <ul class="nav nav-pills">
              <li class="nav-item"><a class="nav-link active" href="#tab-settings" data-toggle="tab"><i class="fas fa-sliders-h mr-1"></i> Settings</a></li>
              <?php if($_SERVER['HTTP_HOST'] != 'demo.timesoftsol.com'): ?>
                <li class="nav-item"><a class="nav-link" href="#tab-password" data-toggle="tab"><i class="fas fa-key mr-1"></i> Change Password</a></li>
              <?php endif; ?>
            </ul>
          </div>

          <div class="card-body">
            <div class="tab-content">

              <!-- SETTINGS -->
              <div class="active tab-pane" id="tab-settings">
                <?php
                  echo form_open_multipart(base_url('admin/profile/save'), 'role="form" id="user-edit-form" autocomplete="off"');
                  echo csrf_field();
                  echo form_hidden('id', $user->id);
                ?>

                <div id="loader-1" class="loader" style="display:none"><span></span><span></span><span></span><span></span></div>

                <div class="row g-3">
                  <div class="col-md-6">
                    <label>Username</label>
                    <div class="input-group mb-3">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user"></i></span></div>
                      <input type="text" class="form-control" value="<?= esc($user->username) ?>" readonly>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <label>Email</label>
                    <div class="input-group mb-3">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="far fa-envelope"></i></span></div>
                      <input type="email" class="form-control" name="email" id="email" value="<?= esc($user->email) ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <label>First Name</label>
                    <input type="text" class="form-control mb-3" name="first_name" id="first_name" value="<?= esc($user->first_name) ?>">
                  </div>

                  <div class="col-md-6">
                    <label>Last Name</label>
                    <input type="text" class="form-control mb-3" name="last_name" id="last_name" value="<?= esc($user->last_name) ?>">
                  </div>

                  <div class="col-md-6">
                    <label>CNIC <small class="text-muted">(#####-#######-#)</small></label>
                    <div class="input-group mb-3">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="far fa-id-card"></i></span></div>
                      <input type="text" class="form-control" name="cnic" id="cnic"
                             value="<?= esc($user->cnic) ?>"
                             data-inputmask="'mask': '99999-9999999-9'" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <label>Father Name</label>
                    <input type="text" class="form-control mb-3" name="f_name" id="f_first_name" value="<?= esc($user->f_name) ?>">
                  </div>

                  <div class="col-md-6">
                    <label>Date of Birth</label>
                    <input type="date" class="form-control mb-3" name="dob" id="dob" value="<?= esc($user->dob) ?>" required>
                  </div>

                  <div class="col-md-6">
                    <label>Joining Date</label>
                    <input type="date" class="form-control mb-3" name="joining_date" id="joining_date"
                           value="<?= esc($user->joining_date) ?>">
                  </div>

                  <div class="col-md-6">
                    <label>Mobile No</label>
                    <div class="input-group mb-3">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-mobile-alt"></i></span></div>
                      <input type="text" class="form-control" name="mobile_no" id="mobile_no"
                             value="<?= esc($user->mobile_no) ?>"
                             data-inputmask="'mask': '0399-99999999'" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <label>Mobile 2</label>
                    <div class="input-group mb-3">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-phone"></i></span></div>
                      <input type="text" class="form-control" name="mobile_no2" id="mobile_no2"
                             value="<?= esc($user->mobile_no2) ?>"
                             data-inputmask="'mask': '0399-99999999'">
                    </div>
                  </div>

                  <div class="col-md-6">
                    <label>Marital Status</label>
                    <div class="d-flex align-items-center mb-3">
                      <div class="custom-control custom-radio mr-3">
                        <input class="custom-control-input" type="radio" id="married" name="marital_status" value="married" <?= ($user->marital_status ?? '')==='married'?'checked':''; ?>>
                        <label class="custom-control-label" for="married">Married</label>
                      </div>
                      <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="single" name="marital_status" value="single" <?= ($user->marital_status ?? '')==='single'?'checked':''; ?>>
                        <label class="custom-control-label" for="single">Single</label>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <label>Address</label>
                    <input type="text" class="form-control mb-3" name="address" id="address" value="<?= esc($user->address) ?>">
                  </div>

                  <div class="col-md-6">
                    <label>Emergency Contact Person</label>
                    <input type="text" class="form-control mb-3" name="emergency_contact_person" id="emergency_contact_person" value="<?= esc($user->emergency_contact_person) ?>">
                  </div>

                  <div class="col-md-6">
                    <label>Emergency Contact No</label>
                    <div class="input-group mb-3">
                      <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-phone-alt"></i></span></div>
                      <input type="text" class="form-control" name="emergency_contact_no" id="emergency_contact_no"
                             value="<?= esc($user->emergency_contact_no) ?>"
                             data-inputmask="'mask': '0399-99999999'">
                    </div>
                  </div>

                  <div class="col-md-6">
                    <label>Qualification</label>
                    <input type="text" class="form-control mb-3" name="qualification" id="qualification" value="<?= esc($user->qualification) ?>">
                  </div>

                  <div class="col-md-6">
                    <label>Experience</label>
                    <input type="text" class="form-control mb-3" name="experience" id="experience" value="<?= esc($user->experience) ?>">
                  </div>

                  <div class="col-md-12">
                    <label>Skills</label>
                    <input type="text" class="form-control mb-3" name="skills" id="skills" value="<?= esc($user->skills) ?>" placeholder="Comma-separated e.g. PHP, MySQL, CI4">
                  </div>

                  <div class="col-12 d-flex justify-content-end">
                    <button type="submit" id="saveProfile" class="btn btn-primary">
                      <i class="fas fa-save mr-1"></i> Save
                    </button>
                    <button type="reset" class="btn btn-light border ml-2">Reset</button>
                    <button type="button" class="btn btn-outline-secondary ml-2" onclick="history.back()">Cancel</button>
                  </div>
                </div>

                <?= form_close(); ?>
              </div>

              <!-- PASSWORD -->
              <?php if($_SERVER['HTTP_HOST'] != 'demo.timesoftsol.com'): ?>
              <div class="tab-pane" id="tab-password">
                <?= form_open(base_url('admin/profile/update_password'), 'class="form-horizontal" id="password-edit-form" autocomplete="off"'); ?>
                <?= csrf_field(); ?>
                <?= form_hidden('user_id', $user->id); ?>

                <div class="row">
                  <div class="col-md-6">
                    <label>New Password</label>
                    <input type="password" class="form-control mb-3" name="password" id="password" required minlength="6">
                  </div>
                  <div class="col-md-6">
                    <label>Confirm Password</label>
                    <input type="password" class="form-control mb-3" name="confirm_password" id="confirm_password" required minlength="6">
                  </div>
                </div>

                <div class="d-flex justify-content-end">
                  <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save</button>
                  <button type="reset" class="btn btn-light border ml-2">Reset</button>
                </div>

                <?= form_close(); ?>
              </div>
              <?php endif; ?>

            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<style>
/* spacing utilities */
.g-3 > [class*="col-"]{ margin-bottom: 1rem; }

/* sticky action bar */
.action-bar{ position: sticky; top: 64px; z-index: 9; background:#fff; border:1px solid #e5e7eb; border-left:0;border-right:0; box-shadow:0 4px 16px rgba(0,0,0,.06); margin-bottom:12px; }
.gap-2 > *{ margin-left:.25rem; margin-right:.25rem; }

/* nicer inputs */
.input-group-text{ background:#f8fafc; }
.custom-file-label::after{ content: "Browse"; }

/* card accent */
.card-primary.card-outline{ border-top: 3px solid #2563eb; }
.card-primary.card-outline:hover{ box-shadow:0 8px 24px rgba(0,0,0,.08); }

/* Select2 sizing */
.select2-container--bootstrap4 .select2-selection--single{ height:2.6rem; }
.select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered{ line-height:2.5rem; }

/* loader dots */
.loader{ display:flex; align-items:center; gap:6px; }
.loader span{ width:8px;height:8px;border-radius:50%;background:#2563eb; display:inline-block; animation:b 1s infinite ease-in-out; }
.loader span:nth-child(2){ animation-delay:.15s }
.loader span:nth-child(3){ animation-delay:.3s }
.loader span:nth-child(4){ animation-delay:.45s }
@keyframes b { 0%,100%{ transform:translateY(0); opacity:.6 } 50%{ transform:translateY(-6px); opacity:1 } }

/* responsive */
@media (max-width: 575.98px){
  .action-bar{ top: 56px; }
}
</style>

<script>
  // Preview uploaded image
  function loadFile(event){
    const image = document.getElementById('output');
    if(event.target.files && event.target.files[0]){
      image.src = URL.createObjectURL(event.target.files[0]);
    }
  }

  $(function(){
    // Masks
    $('[data-inputmask]').inputmask();
    $('#cnic').inputmask('99999-9999999-9');

    // Select2 (if you add any select2 fields here later)
    $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

    // Validate: user profile
    $('#user-edit-form').validate({
      ignore: '.select2-search__field',
      errorClass: 'is-invalid',
      validClass: 'is-valid',
      errorElement: 'div',
      errorPlacement: function(error, element){
        error.addClass('invalid-feedback');
        if (element.parent('.input-group').length) error.insertAfter(element.parent());
        else error.insertAfter(element);
      },
      rules:{
        email:{ required:true, email:true },
        cnic:{ required:true },
        dob:{ required:true },
        mobile_no:{ required:true }
      },
      messages:{
        email:{ required:'Email is required', email:'Invalid email address' }
      }
    });

    // AJAX submit (keeps your existing behavior)
    $('#user-edit-form').ajaxForm({
      beforeSubmit: function(){ if(!$('#user-edit-form').valid()) return false; $('#loader-1').show(); },
      success: function(responseText){
        $('#loader-1').hide();
        let json;
        try { json = (typeof responseText === 'object') ? responseText : JSON.parse(responseText); }
        catch(e){ toastr.error('Unexpected server response'); return; }
        if(json.success){ toastr.success(json.msg || 'Saved'); setTimeout(()=>location.href='<?= base_url('admin/profile'); ?>', 1000); }
        else { toastr.error(json.msg || 'Save failed'); }
      },
      error: function(){ $('#loader-1').hide(); toastr.error('Network/server error'); }
    });

    // Validate: password form
    $('#password-edit-form').validate({
      errorClass: 'is-invalid',
      validClass: 'is-valid',
      errorElement: 'div',
      errorPlacement: function(error, element){
        error.addClass('invalid-feedback'); error.insertAfter(element);
      },
      rules:{
        password:{ required:true, minlength:6 },
        confirm_password:{ required:true, minlength:6, equalTo:'#password' }
      },
      messages:{
        password:{ required:'New password is required', minlength:'At least 6 characters' },
        confirm_password:{ required:'Confirm your new password', equalTo:'Passwords do not match' }
      }
    });

    $('#password-edit-form').ajaxForm({
      beforeSubmit: function(){ return $('#password-edit-form').valid(); },
      success: function(resp){
        let json;
        try { json = (typeof resp === 'object') ? resp : JSON.parse(resp); }
        catch(e){ toastr.error('Unexpected server response'); return; }
        if(json.success){ toastr.success(json.msg || 'Password updated'); $('#password-edit-form')[0].reset(); }
        else { toastr.error(json.msg || 'Update failed'); }
      },
      error: function(){ toastr.error('Network/server error'); }
    });
  });
</script>

<?= $this->endSection() ?>
