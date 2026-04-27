<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>assets/css/card.css" />
<style>
  /* Base Styles */
  .id-card-container {
    font-family: Arial, sans-serif;
    color: #333;
  }
  
  /* Card Layout */
  .id-card {
    width: 85mm;
    height: 54mm;
    border: 2px solid #800000;
    border-radius: 5px;
    margin: 5px;
    float: left;
    position: relative;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  }
  
  /* Header Styles */
  .card-header {
    background-color: #800000;
    color: white;
    padding: 5px;
    text-align: center;
    font-weight: bold;
    font-size: 14px;
    line-height: 1.2;
    border-bottom: 2px solid #000;
  }
  
  .card-header.school {
    font-size: 16px;
    padding: 8px 5px;
  }
  
  /* Content Area */
  .card-content {
    padding: 5px;
    display: flex;
  }
  
  .photo-container {
    width: 30%;
    padding: 2px;
    border: 1px solid #ddd;
    margin-right: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f5f5f5;
  }
  
  .student-photo {
    max-width: 100%;
    max-height: 100%;
  }
  
  .details-container {
    width: 70%;
    font-size: 12px;
  }
  
  .detail-row {
    margin-bottom: 3px;
    display: flex;
  }
  
  .detail-label {
    font-weight: bold;
    width: 40%;
  }
  
  .detail-value {
    width: 60%;
  }
  
  /* Footer */
  .card-footer {
    position: absolute;
    bottom: 0;
    width: 100%;
    font-size: 10px;
    text-align: center;
    padding: 3px 0;
    border-top: 1px dashed #800000;
    background: #f9f9f9;
  }
  
  /* Print Styles */
  @media print {
    body {
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
      margin: 0;
      padding: 0;
      background: white;
    }
    
    .no-print, .no-print * {
      display: none !important;
    }
    
    .id-card {
      page-break-inside: avoid;
      break-inside: avoid;
      margin: 5mm;
    }
    
    .card-header {
      background-color: #800000 !important;
      color: white !important;
    }
  }
  
  /* Controls */
  .form-controls {
    margin-bottom: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 5px;
  }
  
  .form-group {
    margin-bottom: 10px;
  }
  
  label {
    font-weight: 600;
    margin-bottom: 5px;
  }
  
  /* Loading Indicator */
  #loader-1 {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1000;
    display: none;
  }
  
  .fa-spin {
    color: #800000;
  }
  
  /* Clearfix */
  .clearfix::after {
    content: "";
    clear: both;
    display: table;
  }
</style>

<!-- Content Header -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Student ID Card</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Student ID Card</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<!-- Main Content -->
<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0 no-print">
          <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/student_id_card') ?>">Horizontal View</a></li>
            <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/student_id_card/vertical') ?>">Vertical View</a></li>		
          </ul>
        </div>
        
        <div class="card-body">
          <!-- Form Controls -->
          <div class="no-print form-controls">
            <div class="row">
              <div class="col-lg-6 form-group">
                <label for="class"><strong>Class</strong></label>
                <select class="form-control" name="cls_sec_id" id="cls_sec_id">
                  <option value="">All Classes</option>
                  <?php if(isset($sectionsclassinfo)): ?>
                    <?php foreach ($sectionsclassinfo as $sectionvalue): ?>
                      <option value="<?php echo $sectionvalue['section_id']; ?>">
                        <?php echo $sectionvalue['sectionclassname']; ?>
                      </option>
                    <?php endforeach; ?>
                  <?php endif; ?>	
                </select>
              </div>
            </div>
            <button class="btn btn-primary float-right" id="ViewResutlt">
              <i class="fas fa-id-card"></i> Generate ID Cards
            </button>
            <div class="clearfix"></div>
          </div>
          
          <!-- Loading Indicator -->
          <div id="loader-1" class="overlay">
            <i class="fas fa-3x fa-sync-alt fa-spin"></i>
            <p>Generating ID Cards...</p>
          </div>
          
          <!-- Results Container -->
          <div id="resultContainer" class="id-card-container clearfix"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<script type="text/javascript">
$(function() {
  $('#ViewResutlt').on('click', function() {
    $("#loader-1").show();
    var cls_sec_id = $('#cls_sec_id').val();
    
    $.ajax({
      url: 'admin.php?c=student_id_card&m=data_vertical',
      type: "POST",
      data: { cls_sec_id: cls_sec_id },
      success: function(res) {
        $("#resultContainer").html(res);
        $("#loader-1").hide();
        
        // Add print button after cards are generated
        if($("#resultContainer").html().trim().length > 0) {
          $('<button class="btn btn-success no-print" id="PrintCards" style="margin: 15px;"><i class="fas fa-print"></i> Print ID Cards</button>')
            .insertAfter("#resultContainer")
            .on('click', function() {
              window.print();
            });
        }
      },
      error: function() {
        $("#loader-1").hide();
        $("#resultContainer").html('<div class="alert alert-danger">Error loading ID cards. Please try again.</div>');
      }
    });
  });
  
  // Trigger click if coming from another page with parameters
  if(window.location.search.includes('m=vertical')) {
    $('#ViewResutlt').trigger('click');
  }
});
</script>

<?= $this->endSection() ?>