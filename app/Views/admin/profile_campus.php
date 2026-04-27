<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- 3rd-party (already in your stack) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>

<?php
    $schoolinfo = getSchoolInfo();
    // Initialize defaults
    $id = '';
    $campus_name = $short_name = $landline = $mobile_no = $location = '';
    $bank_name = $bank_address = $bank_code = $bank_acc = '';
    $chalan_h_msg = $chalan_f_msg = $fine_type = $late_fee_fine = '';
    $fee_issue_date = $fee_due_date = $attendance_sms = '';
    $school = $hostel = $transport = $academy = 0;
    $principal_name = $principal_signature = '';

    if(isset($info) && is_object($info)){
        $header        = 'Edit Campus';
        $id            = $info->campus_id ?? '';
        $campus_name   = $info->campus_name ?? '';
        $short_name    = $info->short_name ?? '';
        $landline      = $info->landline ?? '';
        $mobile_no     = $info->mobile_no ?? '';
        $location      = $info->location ?? '';
        $bank_name     = $info->bank_name ?? '';
        $bank_address  = $info->bank_address ?? '';
        $bank_code     = $info->bank_code ?? '';
        $bank_acc      = $info->bank_acc ?? '';
        $chalan_h_msg  = $info->chalan_h_msg ?? '';
        $chalan_f_msg  = $info->chalan_f_msg ?? '';
        $fine_type     = $info->fine_type ?? 'per_day_fine';
        $late_fee_fine = $info->late_fee_fine ?? '';
        $fee_issue_date= $info->fee_issue_date ?? 1;
        $fee_due_date  = $info->fee_due_date ?? 5;
        $school        = $info->s_flag ?? 0;
        $hostel        = $info->h_flag ?? 0;
        $transport     = $info->t_flag ?? 0;
        $currency_code = $info->currency_code ?? '';
        $academy       = $info->a_flag ?? 0;
        $principal_name = $info->principal_name ?? '';
        $principal_signature = $info->principal_signature ?? '';
    } else {
        $header        = 'Add Campus';
        $currency_code = $currencies[0]->code ?? 'USD';
    }
?>

<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between flex-wrap">
      <div>
        <h1 class="mb-1"><?= esc($header) ?> <small class="text-muted">Campus Profile</small></h1>
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="#/">Dashboard</a></li>
          <li class="breadcrumb-item active">Campus Profile</li>
        </ol>
      </div>
      <div class="text-muted small mt-2 mt-md-0">
        <?= esc($schoolinfo->system_name ?? 'School') ?>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">

    <!-- Sticky action bar -->
    <div class="card card-primary card-outline shadow-sm">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title mb-0"><i class="fas fa-school mr-2"></i>Campus Information</h3>
        <div class="small text-muted">Fields marked <span class="text-danger">*</span> are required</div>
      </div>

    <?= form_open_multipart(route_to('profile_campus_save'), 'role="form" id="campus-form"'); ?>
    <?= csrf_field() ?>

      <?php echo form_hidden('id', $id); ?>

      <div class="card-body p-0">
        <!-- Tabs -->
        <ul class="nav nav-tabs nav-tabs-clean px-3 pt-3" id="campusTabs" role="tablist">
          <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-basic"><i class="fas fa-id-card-alt mr-1"></i>Basics</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-principal"><i class="fas fa-user-tie mr-1"></i>Principal</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-qrcode"><i class="fas fa-user-tie mr-1"></i>QR Code</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-services"><i class="fas fa-concierge-bell mr-1"></i>Services</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-finance"><i class="fas fa-university mr-1"></i>Finance</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-regional"><i class="fas fa-globe-asia mr-1"></i>Regional</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-fee"><i class="fas fa-file-invoice-dollar mr-1"></i>Fee Settings</a></li>
        </ul>

        <div class="tab-content p-3">

          <!-- BASICS -->
          <div class="tab-pane fade show active" id="tab-basic">
            <div class="row g-3">
              <div class="col-lg-6">
                <div class="form-group">
                  <label>Campus Name <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-school"></i></span></div>
                    <input type="text" class="form-control" name="campus_name" value="<?= esc($campus_name) ?>" required maxlength="150" placeholder="e.g. ITDS Smart System – Main Campus">
                  </div>
                  <small class="form-text text-muted">Official display name of the campus.</small>
                </div>
              </div>

              <div class="col-lg-6">
                <div class="form-group">
                  <label>Short Name</label>
                  <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-compress-arrows-alt"></i></span></div>
                    <input type="text" class="form-control" name="short_name" value="<?= esc($short_name) ?>" maxlength="20" placeholder="e.g. MAIN">
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <label>Mobile Number <span class="text-danger">*</span></label>
                <div class="input-group">
                  <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-mobile-alt"></i></span></div>
                  <input type="tel" class="form-control" name="mobile_no"
                         value="<?= esc($mobile_no) ?>" required maxlength="16"
                         pattern="^\+[0-9]{6,15}$"
                         data-inputmask="'mask': '+999999999999999'"
                         placeholder="+923001234567">
                </div>
                <small class="text-muted">International format (no spaces). Example: <code>+923001234567</code></small>
              </div>

              <div class="col-md-6">
                <label>Landline</label>
                <div class="input-group">
                  <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-phone"></i></span></div>
                  <input type="tel" class="form-control" name="landline"
                         value="<?= esc($landline) ?>"
                         data-inputmask="'mask': '+999999999999999'"
                         placeholder="+97142345678">
                </div>
              </div>

              <div class="col-12">
                <label>Location / Address</label>
                <div class="input-group">
                  <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span></div>
                  <input type="text" class="form-control" name="location" value="<?= esc($location) ?>" placeholder="Street, City, Country">
                </div>
              </div>
            </div>
          </div>

<!-- CAMPUS QR CODE TAB -->
<div class="tab-pane fade" id="tab-qrcode">
    <div class="row">
        <div class="col-md-12 text-center">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-qrcode mr-2"></i>Campus QR Code
                    </h5>
                    <div class="card-tools">
                        <?php if (isset($campusQR) && !empty($campusQR->qr_code)): ?>
                        <button type="button" class="btn btn-tool" onclick="printQRCode()">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <button type="button" class="btn btn-tool" onclick="downloadQRCode()">
                            <i class="fas fa-download"></i> Download
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body text-center">
                    <?php if (isset($campusQR) && !empty($campusQR->qr_code)): ?>
                        <div id="qr-code-container" class="mb-4">
                            <?php if (isset($qr_image_base64) && $qr_image_base64): ?>
                                <img src="<?= $qr_image_base64 ?>" alt="Campus QR Code" 
                                     style="width: 250px; height: 250px; border: 1px solid #ddd; padding: 10px;">
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    QR Code could not be generated. Please try again.
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6 offset-md-3">
                                <div class="info-box bg-light">
                                    <div class="info-box-content">
                                        <span class="info-box-text">QR Code Value</span>
                                        <span class="info-box-number" style="font-size: 14px; word-break: break-all;">
                                            <?= esc($campusQR->qr_code) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Campus QR Code Usage:</strong>
                            <ul class="mb-0 mt-2 text-left">
                                <li>Scan this QR code to access campus profile</li>
                                <li>Use for student/parent registration</li>
                                <li>Display at campus entrance for visitor check-in</li>
                                <li>Include in marketing materials and brochures</li>
                            </ul>
                        </div>
                        
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            No QR code generated for this campus yet.
                        </div>
                        <button type="button" class="btn btn-primary" onclick="generateCampusQR()">
                            <i class="fas fa-qrcode mr-2"></i> Generate QR Code
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
          <!-- PRINCIPAL TAB - NEW -->
          <div class="tab-pane fade" id="tab-principal">
            <div class="row">
              <div class="col-md-8">
                <div class="card card-outline card-primary">
                  <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-user-tie mr-2"></i>Principal Information</h5>
                  </div>
                  <div class="card-body">
                    <div class="form-group">
                      <label>Principal Name</label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text"><i class="fas fa-user"></i></span>
                        </div>
                        <input type="text" class="form-control" name="principal_name" 
                               value="<?= esc($principal_name) ?>" 
                               placeholder="Enter principal's full name" maxlength="100">
                      </div>
                      <small class="text-muted">This name will appear on certificates and official documents</small>
                    </div>

                    <div class="form-group">
                      <label>Digital Signature</label>
                      <div class="custom-file">
                        <input type="file" class="custom-file-input" name="principal_signature" 
                               id="principal_signature" accept="image/png,image/jpeg,image/jpg">
                        <label class="custom-file-label" for="principal_signature">Choose signature image</label>
                      </div>
                      <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle"></i> 
                        Accepted formats: PNG, JPG, JPEG (Max: 1MB). Transparent PNG recommended for best results.
                      </small>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="col-md-4">
                <div class="card card-outline card-secondary">
                  <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-eye mr-2"></i>Current Signature</h5>
                  </div>
                  <div class="card-body text-center">
                    <?php if (!empty($principal_signature)): ?>
                      <div class="signature-preview">
                        <img src="<?= base_url($principal_signature) ?>" 
                             alt="Principal Signature" 
                             style="max-width: 100%; max-height: 100px; border: 1px solid #ddd; padding: 10px; background: #fff;">
                        <p class="text-success mt-2">
                          <i class="fas fa-check-circle"></i> Signature uploaded
                        </p>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSignature()">
                          <i class="fas fa-trash"></i> Remove
                        </button>
                        <input type="hidden" name="remove_signature" id="remove_signature" value="0">
                      </div>
                    <?php else: ?>
                      <div class="signature-placeholder p-4">
                        <i class="fas fa-signature fa-3x text-muted"></i>
                        <p class="text-muted mt-2">No signature uploaded yet</p>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- SERVICES -->
          <div class="tab-pane fade" id="tab-services">
            <div class="row">
              <div class="col-12 mb-2">
                <div class="text-muted">Enable the services the campus provides.</div>
              </div>
              <?php
                $svc = [
                  ['name'=>'school','label'=>'School','checked'=>$school],
                  ['name'=>'transport','label'=>'Transport','checked'=>$transport],
                  ['name'=>'hostel','label'=>'Hostel','checked'=>$hostel],
                  ['name'=>'academy','label'=>'Academy','checked'=>$academy],
                ];
                foreach ($svc as $s):
              ?>
              <div class="col-sm-6 col-lg-3">
                <div class="custom-control custom-switch mb-3">
                  <input type="checkbox" class="custom-control-input" id="<?= $s['name'] ?>"
                         name="<?= $s['name'] ?>" value="1" <?= $s['checked'] ? 'checked' : '' ?>>
                  <label class="custom-control-label" for="<?= $s['name'] ?>">
                    <i class="fas fa-check-circle text-success mr-1"></i><?= $s['label'] ?>
                  </label>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <small class="text-muted d-block">Changing services may affect fee plans, transport routes, and hostel allocations.</small>
          </div>

          <!-- FINANCE -->
          <div class="tab-pane fade" id="tab-finance">
            <div class="row g-3">
              <div class="col-md-6">
                <label>Bank Name</label>
                <input type="text" class="form-control" name="bank_name" value="<?= esc($bank_name) ?>" placeholder="e.g. HBL, Emirates NBD">
              </div>
              <div class="col-md-6">
                <label>Bank Account Number</label>
                <input type="text" class="form-control" name="bank_acc" value="<?= esc($bank_acc) ?>" placeholder="IBAN or account no.">
              </div>
              <div class="col-md-6">
                <label>Bank Code</label>
                <input type="text" class="form-control" name="bank_code" value="<?= esc($bank_code) ?>" placeholder="SWIFT / Routing">
              </div>
              <div class="col-md-6">
                <label>Bank Address</label>
                <input type="text" class="form-control" name="bank_address" value="<?= esc($bank_address) ?>" placeholder="Branch address">
              </div>
            </div>
            <div class="alert alert-light border mt-3">
              <i class="far fa-lightbulb mr-1"></i>
              Tip: Keep bank details consistent with printed fee challans.
            </div>
          </div>

          <!-- REGIONAL -->
          <div class="tab-pane fade" id="tab-regional">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="default_language">Default Language <span class="text-danger">*</span></label>
                <select class="form-control select2" name="default_language" id="default_language" required>
                  <option value="" disabled <?= empty($info->default_language ?? '') ? 'selected' : '' ?>>Select Language</option>
                  <?php foreach ($languages as $lang):
                      $selected = (isset($info->default_language) && $info->default_language == $lang->code) ? 'selected' : '';
                  ?>
                    <option value="<?= esc($lang->code) ?>" <?= $selected ?>>
                      <?= esc($lang->name) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label for="currency_code">Default Currency <span class="text-danger">*</span></label>
                <select class="form-control select2" name="currency_code" id="currency_code" required>
                  <?php foreach ($currencies as $curr): ?>
                    <option value="<?= esc($curr->code) ?>" <?= ($currency_code == $curr->code) ? 'selected' : '' ?>>
                      <?= esc($curr->name) ?> (<?= esc($curr->symbol) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>

          <!-- FEE SETTINGS -->
          <div class="tab-pane fade" id="tab-fee">
            <div class="row g-3">
              <div class="col-sm-6 col-lg-3">
                <label>Fee Issue Date</label>
                <select class="form-control select2" name="fee_issue_date" id="fee_issue_date">
                  <?php for($i=1; $i<=31; $i++): ?>
                    <option value="<?= $i ?>" <?= ($fee_issue_date == $i ? 'selected' : '') ?>><?= $i ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <div class="col-sm-6 col-lg-3">
                <label>Fee Due Date</label>
                <select class="form-control select2" name="fee_due_date" id="fee_due_date">
                  <?php for($i=1; $i<=31; $i++): ?>
                    <option value="<?= $i ?>" <?= ($fee_due_date == $i ? 'selected' : '') ?>><?= $i ?></option>
                  <?php endfor; ?>
                </select>
                <small class="text-muted" id="dueHelp">Due date should be same or after the issue date.</small>
              </div>
              <div class="col-sm-6 col-lg-3">
                <label>Fine Type</label>
                <select class="form-control select2" name="fine_type" id="fine_type">
                  <option value="per_day_fine" <?= $fine_type == 'per_day_fine' ? 'selected' : '' ?>>Per Day Fine</option>
                  <option value="fixed_fine"   <?= $fine_type == 'fixed_fine'   ? 'selected' : '' ?>>Fixed Fine</option>
                </select>
              </div>
              <div class="col-sm-6 col-lg-3">
                <label>Late Fee Fine Amount</label>
                <div class="input-group">
                  <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-money-bill-wave"></i></span></div>
                  <input type="number" class="form-control" name="late_fee_fine" value="<?= esc($late_fee_fine) ?>" min="0" step="0.01" placeholder="0.00">
                </div>
              </div>

              <div class="col-lg-6">
                <label>Chalan Header Message</label>
                <textarea class="form-control autosize" name="chalan_h_msg" rows="3" maxlength="250" data-count="#hCount"><?= esc($chalan_h_msg) ?></textarea>
                <div class="text-right small text-muted"><span id="hCount">0</span>/250</div>
              </div>
              <div class="col-lg-6">
                <label>Chalan Footer Message</label>
                <textarea class="form-control autosize" name="chalan_f_msg" rows="3" maxlength="250" data-count="#fCount"><?= esc($chalan_f_msg) ?></textarea>
                <div class="text-right small text-muted"><span id="fCount">0</span>/250</div>
              </div>
            </div>

            <div class="alert alert-info mt-3 mb-0">
              <i class="fas fa-info-circle mr-1"></i>
              Fee cycle values are used when generating monthly challans and reminders.
            </div>
          </div>

        </div>
      </div>

      <!-- Sticky footer mirrors the action bar for long forms -->
      <div class="card-footer d-flex justify-content-end gap-2">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save mr-1"></i> Save Changes
        </button>
        <button type="reset" class="btn btn-light border">
          <i class="fas fa-undo mr-1"></i> Reset
        </button>
        <a href="#/" class="btn btn-outline-secondary">Cancel</a>
      </div>
      <?php echo form_close(); ?>
    </div>
  </div>
</section>

<!-- Add this CSS -->
<style>
.signature-preview {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 5px;
    border: 1px dashed #dee2e6;
}

.signature-placeholder {
    background: #f8f9fa;
    border-radius: 5px;
    border: 1px dashed #dee2e6;
}

.custom-file-label::after {
    content: "Browse";
}

/* Preview image animation */
.signature-preview img {
    transition: transform 0.2s;
}

.signature-preview img:hover {
    transform: scale(1.05);
}

/* Tab styling */
.nav-tabs-clean .nav-link {
    border: none;
    border-bottom: 2px solid transparent;
    color: #495057;
}

.nav-tabs-clean .nav-link:hover {
    border-bottom-color: #adb5bd;
}

.nav-tabs-clean .nav-link.active {
    border-bottom-color: #007bff;
    color: #007bff;
}
</style>

<script>


  // QR Code Functions
function printQRCode() {
    var qrContainer = document.getElementById('qr-code-container');
    if (!qrContainer) return;
    
    var printWindow = window.open('', '_blank');
    printWindow.document.write('<html><head><title>Campus QR Code</title>');
    printWindow.document.write('<style>');
    printWindow.document.write('body { text-align: center; font-family: Arial, sans-serif; padding: 20px; }');
    printWindow.document.write('.qr-wrapper { margin: 50px auto; }');
    printWindow.document.write('img { max-width: 300px; }');
    printWindow.document.write('h3 { margin-top: 20px; color: #333; }');
    printWindow.document.write('p { color: #666; margin-top: 10px; }');
    printWindow.document.write('</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write('<div class="qr-wrapper">');
    printWindow.document.write(qrContainer.innerHTML);
    printWindow.document.write('<h3><?= addslashes($campus_name) ?></h3>');
    printWindow.document.write('<p>Scan this QR code to access campus information</p>');
    printWindow.document.write('</div>');
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}

function downloadQRCode() {
    var qrImage = document.querySelector('#qr-code-container img');
    if (!qrImage) {
        toastr.error('No QR code found to download');
        return;
    }
    
    var link = document.createElement('a');
    var campusName = '<?= addslashes($campus_name) ?>';
    var today = new Date().toISOString().slice(0,10);
    link.download = 'campus-qrcode-' + campusName + '-' + today + '.png';
    link.href = qrImage.src;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    toastr.success('QR Code downloaded successfully');
}

function generateCampusQR() {
    $.ajax({
        url: '<?= base_url('admin/profile_campus/generate_qr') ?>',
        type: 'POST',
        data: {
            campus_id: '<?= $id ?>',
            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
        },
        dataType: 'json',
        beforeSend: function() {
            toastr.info('Generating QR Code...');
        },
        success: function(response) {
            if (response.success) {
                toastr.success('QR Code generated successfully');
                setTimeout(function() {
                    location.reload();
                }, 1000);
            } else {
                toastr.error(response.msg || 'Failed to generate QR code');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            toastr.error('Error generating QR code. Please try again.');
        }
    });
}


(function($){
  // Select2
  $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

  // Masks
  $('[name="mobile_no"]').inputmask({ mask: '+999999999999999', placeholder: '', greedy:false });
  $('[name="landline"]').inputmask({ mask: '+999999999999999', placeholder: '', greedy:false });

  // Simple autosize for textareas
  function fitTA(el){
    el.style.height = 'auto';
    el.style.height = (el.scrollHeight) + 'px';
  }
  document.querySelectorAll('.autosize').forEach(function(t){
    fitTA(t);
    t.addEventListener('input', function(){ fitTA(t); });
  });

  // Live counters for messages
  function bindCounter(textarea){
    var max = parseInt(textarea.getAttribute('maxlength')||'0',10);
    var target = textarea.getAttribute('data-count');
    if(!max || !target) return;
    var out = document.querySelector(target);
    var sync = function(){ if(out) out.textContent = textarea.value.length; };
    textarea.addEventListener('input', sync);
    sync();
  }
  document.querySelectorAll('textarea[maxlength][data-count]').forEach(bindCounter);

  // Fee date logic (due >= issue)
  function clampDue(){
    var issue = parseInt($('#fee_issue_date').val()||'1',10);
    var due   = parseInt($('#fee_due_date').val()||'1',10);
    if(due < issue){
      $('#fee_due_date').val(issue).trigger('change.select2');
      $('#dueHelp').addClass('text-danger').removeClass('text-muted').text('Adjusted: Due date cannot be before Issue date.');
      setTimeout(function(){
        $('#dueHelp').removeClass('text-danger').addClass('text-muted').text('Due date should be same or after the issue date.');
      }, 2500);
    }
  }
  $('#fee_issue_date, #fee_due_date').on('change', clampDue);
  clampDue();

  // Service toggles confirm (gentle)
  $('input[type="checkbox"][name="school"],[name="transport"],[name="hostel"],[name="academy"]').on('change', function(){
    var $t = $(this), label = $t.attr('name'), on = $t.is(':checked');
    if(!confirm('Are you sure you want to ' + (on?'enable':'disable') + ' ' + label + '?')){
      $t.prop('checked', !on);
    }
  });

  // Custom file input label update
  $('#principal_signature').on('change', function() {
    var fileName = $(this).val().split('\\').pop();
    $(this).next('.custom-file-label').addClass("selected").html(fileName);
  });

  // jQuery Validate
  $('#campus-form').validate({
    ignore: '.select2-search__field',
    errorClass: 'is-invalid',
    validClass: 'is-valid',
    errorElement: 'div',
    errorPlacement: function(error, element){
      error.addClass('invalid-feedback');
      if (element.parent('.input-group').length) {
        error.insertAfter(element.parent());
      } else {
        error.insertAfter(element);
      }
    },
    rules: {
      campus_name: { required:true, maxlength:150 },
      mobile_no:   { required:true, pattern:/^\+[0-9]{6,15}$/ },
      landline:    { pattern:/^\+?[0-9\s\-()]{6,20}$/ },
      default_language: { required:true },
      currency_code:    { required:true },
      principal_signature: {
        extension: "png|jpeg|jpg",
        filesize: 1048576 // 1MB in bytes
      }
    },
    messages: {
      mobile_no: { pattern: "Use international format e.g. +923001234567" },
      landline:  { pattern: "Enter a valid landline number" },
      principal_signature: {
        extension: "Only PNG, JPG, JPEG files are allowed",
        filesize: "File size must be less than 1MB"
      }
    },
    submitHandler: function(form){
      // AJAX submit (compatible with your backend response)
      var $form = $(form);
      $('.action-bar button, .card-footer button').prop('disabled', true);

      // Optional: show loader (use your own loader if any)
      let $btn = $('.card-footer .btn-primary');
      let old = $btn.html();
      $btn.html('<span class="spinner-border spinner-border-sm mr-1"></span> Saving…');

      $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        data: new FormData(form),
        contentType: false,
        processData: false,
        success: function(resp){
          try {
            var json = (typeof resp === 'object') ? resp : JSON.parse(resp);
            if(json.success){
              toastr.success(json.msg || 'Saved');
              setTimeout(function(){ window.location.href = '#/profile_campus'; }, 1200);
            } else {
              toastr.error(json.msg || 'Failed to save');
            }
          } catch(e){
            toastr.error('Unexpected response');
          }
        },
        error: function(){
          toastr.error('Network/server error');
        },
        complete: function(){
          $('.action-bar button, .card-footer button').prop('disabled', false);
          $btn.html(old);
        }
      });

      return false; // prevent default submit
    }
  });

})(jQuery);

// File size validation extension
jQuery.validator.addMethod('filesize', function(value, element, param) {
    return this.optional(element) || (element.files[0].size <= param);
}, 'File size must be less than {0} bytes');

// File extension validation
jQuery.validator.addMethod('extension', function(value, element, param) {
    param = typeof param === 'string' ? param.replace(/,/g, '|') : 'png|jpe?g|gif';
    return this.optional(element) || value.match(new RegExp('\\.(' + param + ')$', 'i'));
}, 'Please enter a value with a valid extension.');

// Remove signature function
function removeSignature() {
    if (confirm('Are you sure you want to remove the principal signature?')) {
        $('#remove_signature').val('1');
        $('.signature-preview').fadeOut(300, function() {
            $(this).replaceWith(`
                <div class="signature-placeholder p-4">
                    <i class="fas fa-signature fa-3x text-muted"></i>
                    <p class="text-muted mt-2">Signature removed</p>
                </div>
            `);
        });
    }
}

// Bootstrap tabs - prevent jumping on click
$('a[data-toggle="tab"]').on('click', function(e) {
    e.preventDefault();
    $(this).tab('show');
});
</script>

<?= $this->endSection() ?>