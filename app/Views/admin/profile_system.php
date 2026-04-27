<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
$header = isset($info) ? 'Edit System' : 'Add System';

$id               = $info->system_id          ?? '';
$system_name      = $info->system_name        ?? '';
$address          = $info->address            ?? '';
$city             = $info->city               ?? '';
$state            = $info->state              ?? '';
$zip              = $info->zip                ?? '';
$country          = $info->country            ?? '';
$owner_name       = $info->owner_name         ?? '';
$landline_number  = $info->landline_number    ?? '';
$mob_number       = $info->mob_number         ?? '';
$reg_text         = $info->reg_text           ?? '';
$logo             = $info->logo               ?? '';
$chalan_header    = $info->chalan_header      ?? '';
$slogan           = $info->slogan             ?? '';

$masking_name     = $sms_settings_info->masking_name ?? '';
$api_secret       = $sms_settings_info->api_secret   ?? '';
$api_token        = $sms_settings_info->api_token    ?? '';
?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>System Profile
          <?php $schoolinfo = getSchoolInfo();
          if (empty($schoolinfo->reg_text)) : ?>
            <span style="background: green; color: #fff; float: right; padding: 5px 10px; font-size: 16px;">
              Step 1 Of 12 To Complete System Configuration
            </span>
            <div style="text-align: center;">
              <audio autoplay controls>
                <source src="<?= base_url('audio/Step1CampusProfile.m4a') ?>" type="audio/mpeg">
                Your browser does not support the audio element.
              </audio>
            </div>
          <?php endif; ?>
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">System Profile</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="row">
    <!-- Left Column: Logo -->
    <div class="col-md-3">
      <div class="card card-primary card-outline">
        <div class="card-body box-profile text-center">
          <img id="output" class="profile-user-img img-fluid"
               src="<?= base_url('system-logo/' . ($logo ?: 'Time-soft-sol-logo.png')) ?>" alt="Logo">
          <h3 class="profile-username text-center"><?= esc($system_name) ?></h3>
          <a href="<?= base_url('logout') ?>" class="btn btn-danger btn-block"><b>Logout</b></a>
        </div>
      </div>
      <div class="card card-primary card-outline">
        <div class="card-body box-profile text-center">
          <img id="output2" class="profile-user-img img-fluid"
               src="<?= base_url('system-logo/' . ($chalan_header ?: 'Time-soft-sol-logo.png')) ?>" alt="Chalan Header">
        </div>
      </div>
    </div>

    <!-- Right Column: Form -->
    <div class="col-md-9">
      <div class="card">
        <div class="card-header p-2">
          <ul class="nav nav-pills">
            <li class="nav-item"><a class="nav-link active" href="#settings" data-toggle="tab">Settings</a></li>
          </ul>
        </div>
        <div class="card-body">
          <div class="tab-content">
            <div class="active tab-pane" id="settings">
              <?= form_open_multipart(base_url('admin/profile-system/save'), ['id' => 'user-edit-form']) ?>
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= esc($id) ?>">

              <div class="row">
                <!-- Loader -->
                <div class="col-md-12">
                  <div class="loader" id="loader-1" style="display: none;">
                    <span></span><span></span><span></span><span></span>
                  </div>
                </div>

                <!-- Column 1 -->
                <div class="col-lg-4">
                  <?= form_label('System Name', 'system_name') ?>
                  <?= form_input('system_name', esc($system_name), 'class="form-control"') ?>

                  <?= form_label('City', 'city') ?>
                  <?= form_input('city', esc($city), 'class="form-control"') ?>

                  <?= form_label('Owner Name', 'owner_name') ?>
                  <?= form_input('owner_name', esc($owner_name), 'class="form-control"') ?>

                  <?= form_label('Address', 'address') ?>
                  <?= form_input('address', esc($address), 'class="form-control"') ?>
                </div>

                <!-- Column 2 -->
                <div class="col-lg-4">
                  <?= form_label('System Short Name (e.g. TSS)', 'reg_text') ?>
                  <?= form_input([
                    'name' => 'reg_text',
                    'id' => 'reg_text',
                    'value' => esc($reg_text),
                    'maxlength' => 3,
                    'required' => true,
                    'class' => 'form-control'
                  ]) ?>

                  <?= form_label('State', 'state') ?>
                  <?= form_input('state', esc($state), 'class="form-control"') ?>

                  <?= form_label('Zip', 'zip') ?>
                  <?= form_input('zip', esc($zip), 'class="form-control"') ?>

                  <?= form_label('Country', 'country') ?>
                  <?= form_input('country', esc($country), 'class="form-control"') ?>
                </div>

                <!-- Column 3 -->
                <div class="col-lg-4">
                  <?= form_label('Landline No', 'landline_number') ?>
                  <?= form_input('landline_number', esc($landline_number), 'class="form-control"') ?>

                  <?= form_label('Mobile No', 'mob_number') ?>
                  <?= form_input('mob_number', esc($mob_number), 'class="form-control" required') ?>

                  <?= form_label('Slogan', 'slogan') ?>
                  <?= form_input('slogan', esc($slogan), 'class="form-control"') ?>

                  <?= form_label('School Logo') ?><br>
                  <input type="file" name="image" id="file" accept="image/*" onchange="loadFile(event)" hidden>
                  <label for="file" class="btn btn-default"><i class="fa fa-image"></i> Upload Logo</label>

                  <?= form_label('Fee Header') ?><br>
                  <input type="file" name="image2" id="file2" accept="image/*" onchange="loadFile2(event)" hidden>
                  <label for="file2" class="btn btn-default"><i class="fa fa-image"></i> Upload Header</label>
                </div>

                <!-- SMS Settings -->
                <div class="col-lg-12">
                  <h4>Branded SMS Settings</h4>
                  <div class="row">
                    <div class="col-lg-4">
                      <?= form_label('Masking Name', 'masking_name') ?>
                      <?= form_input('masking_name', esc($masking_name), 'class="form-control" readonly') ?>
                    </div>
                    <div class="col-lg-4">
                      <?= form_label('API Token', 'api_token') ?>
                      <?= form_input('api_token', esc($api_token), 'class="form-control" readonly') ?>
                    </div>
                    <div class="col-lg-4">
                      <?= form_label('API Secret', 'api_secret') ?>
                      <?= form_input('api_secret', esc($api_secret), 'class="form-control" readonly') ?>
                    </div>
                  </div>
                </div>

                <!-- Actions -->
                <div class="col-lg-12 mt-3">
                  <button type="submit" class="btn btn-primary">Save</button>
                  <button type="reset" class="btn btn-default">Reset</button>
                  <a href="<?= base_url('academic_session/add') ?>" class="btn btn-default">Cancel</a>
                </div>
              </div>

              <?= form_close() ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</section>

<script>
  const loadFile = (event) => {
    document.getElementById('output').src = URL.createObjectURL(event.target.files[0]);
  };
  const loadFile2 = (event) => {
    document.getElementById('output2').src = URL.createObjectURL(event.target.files[0]);
  };

  $(function () {
    $('#user-edit-form').validate({
      rules: {
        reg_text: { required: true },
        mob_number: { required: true }
      },
      messages: {
        reg_text: { required: 'Reg Text is required' },
        mob_number: { required: 'Mobile Number is required' }
      }
    });

    $('#user-edit-form').ajaxForm({
      beforeSubmit: () => $('#user-edit-form').valid(),
      success: (response) => {
        const json = typeof response === 'string' ? JSON.parse(response) : response;
        if (json.session_id === false) {
          window.location.href = '<?= base_url('academic_session/add') ?>';
          return;
        }
        if (json.success) {
          toastr.success(json.msg);
          location.href = '<?= base_url('admin/profile-system') ?>';
        } else {
          toastr.error(json.msg);
        }
      }
    });
  });
</script>

<?= $this->endSection() ?>
