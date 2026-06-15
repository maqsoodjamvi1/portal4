<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Card Generator - Fixed Layout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Fixed CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #800000 0%, #5a0000 100%);
            border-radius: 10px;
            color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .icon-badge {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
            flex-shrink: 0;
            font-size: 10px;
        }

        .name-badge { background-color: #800000; color: white; }
        .father-badge { background-color: #d4af37; color: #5a0000; }
        .contact-badge { background-color: #28a745; color: white; }
        .class-badge { background-color: #17a2b8; color: white; }
        .dob-badge { background-color: #6f42c1; color: white; }
        
        .status-badge {
            position: absolute;
            top: -7px;
            right: 15px;
            background: #d4af37;
            color: #5a0000;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            z-index: 10;
        }

        /* Font Size Classes */
        .font-size-9 { font-size: 9px !important; }
        .font-size-10 { font-size: 10px !important; }
        .font-size-11 { font-size: 11px !important; }
        .font-size-13 { font-size: 13px !important; }

        /* Card Layout - Fixed */
        .id-card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            padding: 20px;
        }
        
        .id-card {
            width: 85mm;
            height: 54mm;
            border: 2px solid #800000;
            border-radius: 8px;
            position: relative;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
        }
        
        .id-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        
        /* Header Styles - Fixed to contain status */
        .card-header {
            background: linear-gradient(135deg, #800000 0%, #5a0000 100%);
            color: white;
            padding: 8px 0;
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            border-bottom: 2px solid #d4af37;
            position: relative;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .card-header.school {
            font-size: 14px;
            position: relative;
        }
        
        .card-header.school::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #d4af37, transparent);
        }
        
        .school-logo {
            position: absolute;
            top: 50%;
            left: 8px;
            transform: translateY(-50%);
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
        }
        
        .school-logo img {
            max-width: 18px;
            max-height: 18px;
        }
        
        /* Content Area */
        .card-content {
            padding: 8px 10px;
            display: flex;
            height: calc(100% - 28px - 18px); /* Account for header and footer */
        }
        
        .photo-container {
            width: 35%;
            padding: 3px;
            border: 1px solid #e0e0e0;
            margin-right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f8f8;
            border-radius: 4px;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }
        
        .student-photo {
            max-width: 100%;
            max-height: 100%;
            border-radius: 2px;
        }
        
        .photo-placeholder {
            color: #777;
            font-size: 32px;
            opacity: 0.3;
        }
        
        .details-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .detail-row {
            margin-bottom: 4px;
            display: flex;
            align-items: flex-start;
        }
        
        .detail-label {
            display: flex;
            align-items: center;
            min-width: 75px;
        }
        
        .detail-value {
            flex: 1;
            word-break: break-word;
            padding-left: 5px;
            font-weight: 500;
        }
        
        .student-id {
            background: #f0f0f0;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 11px;
            text-align: center;
            margin: 5px 0;
            border: 1px solid #e0e0e0;
            display: inline-block;
            width: 100%;
        }
        
        /* Footer */
        .card-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            font-size: 9px;
            text-align: center;
            padding: 4px 0;
            background: #f0f0f0;
            border-top: 1px dashed #800000;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 18px;
        }
        
        .card-footer::before,
        .card-footer::after {
            content: "•";
            margin: 0 8px;
            color: #800000;
            font-size: 12px;
        }
        
        /* Print Styles */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                margin: 0;
                padding: 5mm;
                background: white;
            }
            
            .no-print, .no-print * {
                display: none !important;
            }
            
            .id-card {
                box-shadow: none;
                margin: 2mm !important;
                page-break-inside: avoid;
            }
            
            .card-header {
                background: linear-gradient(135deg, #800000 0%, #5a0000 100%) !important;
                -webkit-print-color-adjust: exact;
                color: white !important;
            }
            
            .id-card-container {
                padding: 0;
                gap: 0;
            }
        }
        
        /* Controls */
        .form-controls {
            margin: 30px auto;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            max-width: 800px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #5a0000;
            display: block;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input:focus, select:focus {
            border-color: #800000;
            outline: none;
            box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
        }
        
        .btn {
            background: linear-gradient(135deg, #800000 0%, #5a0000 100%);
            color: white;
            border: none;
            padding: 14px 25px;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            display: block;
            width: 100%;
            margin-top: 20px;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 15px rgba(128, 0, 0, 0.3);
        }
        
        .btn:active {
            transform: translateY(1px);
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        /* Loading Indicator */
        #loader-1 {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            display: none;
            text-align: center;
        }
        
        .fa-spin {
            color: #800000;
            font-size: 50px;
            margin-bottom: 20px;
        }
        
        /* Instructions */
        .instructions {
            background: #fff9e6;
            border-start: 4px solid #d4af37;
            padding: 15px;
            border-radius: 0 8px 8px 0;
            margin: 25px 0;
            font-size: 14px;
        }
        
        .instructions h3 {
            color: #5a0000;
            margin-bottom: 10px;
        }
        
        .instructions ul {
            padding-left: 20px;
        }
        
        .instructions li {
            margin-bottom: 8px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .form-controls {
                padding: 15px;
            }
            
            .id-card {
                width: 90%;
                height: auto;
                aspect-ratio: 85/54;
            }
        }
        
        /* Fix for status position */
        .card-header-container {
            position: relative;
        }
    </style>
</head>
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
              <div class="col-lg-4 form-group">
                <label for="class"><i class="fas fa-graduation-cap me-1"></i> Class</label>
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
              <div class="col-lg-4 form-group">
                <label for="status"><i class="fas fa-user-check me-1"></i> Status</label>
                <select class="form-control" id="statusFilter">
                  <option value="all">All Students</option>
                  <option value="active">Active Only</option>
                  <option value="new">New Admissions</option>
                </select>
              </div>
              <div class="col-lg-4 form-group d-flex align-items-end">
                <button class="btn btn-primary w-100" id="ViewResutlt">
                  <i class="fas fa-id-card me-2"></i> Generate ID Cards
                </button>
              </div>
            </div>
            <div class="clearfix"></div>
          </div>
          
          <!-- Loading Indicator -->
          <div id="loader-1" class="overlay">
            <i class="fas fa-3x fa-sync-alt fa-spin"></i>
            <p class="mt-2">Generating ID Cards...</p>
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
  // Generate ID Cards
  $('#ViewResutlt').on('click', function() {
    $("#loader-1").show();
    var cls_sec_id = $('#cls_sec_id').val();
    var statusFilter = $('#statusFilter').val();
    
    $.ajax({
      url: '/admin/student_id_card/data_vertical',
      type: "POST",
      data: { 
        cls_sec_id: cls_sec_id,
        status: statusFilter
      },
      success: function(res) {
        $("#resultContainer").html(res);
        $("#loader-1").hide();
        
        // Add print button after cards are generated
        if($("#resultContainer").html().trim().length > 0) {
          if ($("#PrintCards").length === 0) {
            $('<div class="no-print text-center mt-4 mb-3">' +
              '<button class="btn btn-success me-2" id="PrintCards"><i class="fas fa-print me-2"></i> Print ID Cards</button>' +
              '<button class="btn btn-info" id="SaveAsPDF"><i class="fas fa-file-pdf me-2"></i> Save as PDF</button>' +
              '</div>')
              .insertAfter("#resultContainer")
              .find('#PrintCards')
              .on('click', function() {
                window.print();
              });
          }
        }
      },
      error: function() {
        $("#loader-1").hide();
        $("#resultContainer").html('<div class="alert alert-danger">Error loading ID cards. Please try again.</div>');
      }
    });
  });
  
  // Auto-generate if URL contains parameter
  if(window.location.search.includes('m=vertical')) {
    $('#ViewResutlt').trigger('click');
  }
  
  // Add hover effect for cards
  $(document).on('mouseenter', '.id-card', function() {
    $(this).css('transform', 'translateY(-5px)');
  }).on('mouseleave', '.id-card', function() {
    $(this).css('transform', '');
  });
});
</script>

<?= $this->endSection() ?>