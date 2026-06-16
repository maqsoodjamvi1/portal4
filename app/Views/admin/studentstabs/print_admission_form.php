<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Form - <?= esc(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Print-specific styles */
        @media print {
            body {
                background-color: white;
                padding: 15px;
                font-size: 12pt;
            }
            
            .no-print {
                display: none !important;
            }
            
            .print-container {
                width: 100%;
                max-width: 100%;
                padding: 0;
                margin: 0;
                box-shadow: none;
                border: none;
            }
            
            .watermark {
                position: fixed;
                bottom: 0;
                right: 0;
                opacity: 0.1;
                font-size: 72pt;
                color: #ccc;
                pointer-events: none;
                z-index: 9999;
            }
            
            .signature-area {
                margin-top: 80px;
                border-top: 1px solid #333;
                padding-top: 20px;
            }
        }
        
        body {
            background-color: #f4f6f9;
            padding: 20px;
        }
        
        .print-container {
            background-color: white;
            padding: 30px;
            margin: 0 auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 800px;
        }
        
        .header-section {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #007bff;
        }
        
        .institution-logo {
            max-height: 80px;
            margin-bottom: 10px;
        }
        
        .student-photo {
            width: 150px;
            height: 180px;
            object-fit: cover;
            border: 2px solid #ddd;
            float: right;
        }
        
        .section-title {
            background-color: #f8f9fa;
            padding: 8px 15px;
            margin-top: 20px;
            border-start: 4px solid #007bff;
            font-weight: bold;
        }
        
        .info-label {
            font-weight: bold;
            min-width: 160px;
        }
        
        .signature-line {
            display: inline-block;
            width: 250px;
            border-bottom: 1px solid #333;
            margin-top: 50px;
        }
        
        .watermark {
            position: fixed;
            bottom: 0;
            right: 0;
            opacity: 0.1;
            font-size: 72pt;
            color: #ccc;
            pointer-events: none;
            z-index: 9999;
        }
        
        .table th {
            background-color: #f8f9fa;
        }
    </style>
</head>


<body>
      <div class="print-container">
    <div class="no-print mb-3 text-center">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print me-2"></i> Print Form
        </button>
        <a href="<?= base_url('admin/students') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back to Students
        </a>
    </div>
    
    <div class="print-container">
        <!-- Watermark -->
        <div class="watermark">ADMISSION FORM</div>
        
        <!-- Header Section -->
        <div class="header-section">
            <?php if ($campus['logo']): ?>
                <img src="<?= base_url('uploads/campus/' . esc($campus['logo'])) ?>" 
                     alt="Institution Logo" class="institution-logo">
            <?php endif; ?>
            
            <h2 class="mb-0"><?= esc($campus['campus_name']) ?></h2>
            <p class="mb-0"><?= esc($campus['location']) ?></p>
            <h4 class="mt-4 text-primary">STUDENT ADMISSION FORM</h4>
        </div>
        
        <!-- Student Photo -->
        <?php if ($student['profile_photo']): ?>
            <img src="<?= base_url('studentphotos/' . esc($student['profile_photo'])) ?>" 
                 alt="Student Photo" class="student-photo">
        <?php else: ?>
            <div class="student-photo bg-light text-center d-flex align-items-center justify-content-center">
                <i class="fas fa-user fa-3x text-secondary"></i>
            </div>
        <?php endif; ?>
        
        <!-- Student Information -->
        <h5 class="section-title">STUDENT INFORMATION</h5>
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="d-flex">
                    <span class="info-label">Registration No:</span>
                    <span><?= esc($student['reg_no'] ?? 'N/A') ?></span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex">
                    <span class="info-label">GR Number:</span>
                    <span><?= esc($student['gr_no'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="d-flex">
                    <span class="info-label">Full Name:</span>
                    <span><?= esc($student['first_name'] . ' ' . esc($student['last_name'])) ?></span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex">
                    <span class="info-label">Father's Name:</span>
                    <span><?= esc($student['father_name'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="d-flex">
                    <span class="info-label">Date of Birth:</span>
                    <span><?= $student['date_of_birth'] ? date('M j, Y', strtotime($student['date_of_birth'])) : 'N/A' ?></span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex">
                    <span class="info-label">Gender:</span>
                    <span><?= ucfirst(esc($student['gender'])) ?></span>
                </div>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="d-flex">
                    <span class="info-label">CNIC/B-Form:</span>
                    <span><?= esc($student['std_cnic'] ?? 'N/A') ?></span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex">
                    <span class="info-label">Admission Date:</span>
                    <span><?= $student['date_of_admission'] ? date('M j, Y', strtotime($student['date_of_admission'])) : 'N/A' ?></span>
                </div>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="d-flex">
                    <span class="info-label">Previous School:</span>
                    <span><?= esc($student['previous_school'] ?? 'N/A') ?></span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex">
                    <span class="info-label">City:</span>
                    <span><?= esc($student['ps_city'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="d-flex">
                    <span class="info-label">Health Conditions:</span>
                    <span><?= esc($student['health_conditions'] ?? 'None') ?></span>
                </div>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="d-flex">
                    <span class="info-label">Major Injuries:</span>
                    <span><?= esc($student['major_injuries'] ?? 'None') ?></span>
                </div>
            </div>
        </div>
        
        <!-- Class Information -->
        <h5 class="section-title">CLASS INFORMATION</h5>
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="d-flex">
                    <span class="info-label">Campus:</span>
                    <span><?= esc($campus['campus_name']) ?></span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex">
                    <span class="info-label">Session:</span>
                    <span><?= esc($studentClass['session_id'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="d-flex">
                    <span class="info-label">Class:</span>
                    <span><?= esc($class['class_name'] ?? 'N/A') ?></span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex">
                    <span class="info-label">Section:</span>
                    <span><?= esc($section['section_name'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>
        
        <!-- Parent Information -->
        <h5 class="section-title">PARENT/GUARDIAN INFORMATION</h5>
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="d-flex">
                    <span class="info-label">Father's CNIC:</span>
                    <span><?= esc($parent['father_cnic'] ?? 'N/A') ?></span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex">
                    <span class="info-label">Father's Contact:</span>
                    <span><?= esc($parent['father_contact'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="d-flex">
                    <span class="info-label">Father's Email:</span>
                    <span><?= esc($parent['father_email'] ?? 'N/A') ?></span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex">
                    <span class="info-label">Father's Occupation:</span>
                    <span><?= esc($parent['father_occupation'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="d-flex">
                    <span class="info-label">Mother's Name:</span>
                    <span><?= esc($parent['m_name'] ?? 'N/A') ?></span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex">
                    <span class="info-label">Mother's Contact:</span>
                    <span><?= esc($parent['mother_contact'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="d-flex">
                    <span class="info-label">WhatsApp:</span>
                    <span><?= esc($parent['whatsapp'] ?? 'N/A') ?></span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex">
                    <span class="info-label">How You Heard:</span>
                    <span><?= esc($parent['hear_source'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="d-flex">
                    <span class="info-label">Residential Address:</span>
                    <span><?= esc($parent['address_line1'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="d-flex">
                    <span class="info-label">City:</span>
                    <span><?= esc($parent['city'] ?? 'N/A') ?></span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex">
                    <span class="info-label">Emergency Contact:</span>
                    <span><?= esc($parent['emergency_contact'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>
        
        <!-- Fee Information -->
        <h5 class="section-title">FEE INFORMATION</h5>
        <?php if (!empty($invoices) || !empty($feeInvoices)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Invoice No</th>
                            <th>Fee Month</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td><?= esc($invoice['invoice_no']) ?></td>
                                <td><?= esc($invoice['fee_month']) ?></td>
                                <td><?= date('M j, Y', strtotime($invoice['issue_date'])) ?></td>
                                <td><?= date('M j, Y', strtotime($invoice['due_date'])) ?></td>
                                <td><?= number_format($invoice['amount'], 2) ?> PKR</td>
                                <td>
                                    <span class="badge text-bg-<?=  $invoice['status'] == 'paid' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($invoice['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php foreach ($feeInvoices as $invoice): ?>
                            <tr>
                                <td><?= esc($invoice['invoice_no']) ?></td>
                                <td><?= esc($invoice['fee_month']) ?></td>
                                <td><?= date('M j, Y', strtotime($invoice['issue_date'])) ?></td>
                                <td><?= date('M j, Y', strtotime($invoice['due_date'])) ?></td>
                                <td><?= number_format($invoice['amount'], 2) ?> PKR</td>
                                <td>
                                    <span class="badge text-bg-<?=  $invoice['status'] == 'paid' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($invoice['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center py-3">No fee records found</p>
        <?php endif; ?>
        
        <!-- Signature Section -->
        <div class="signature-area mt-5">
            <div class="row">
                <div class="col-md-6 text-center">
                    <div class="signature-line"></div>
                    <div class="mt-2">Parent/Guardian Signature</div>
                </div>
                <div class="col-md-6 text-center">
                    <div class="signature-line"></div>
                    <div class="mt-2">School Official Signature</div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6 text-center">
                    <div class="signature-line"></div>
                    <div class="mt-2">Date</div>
                </div>
                <div class="col-md-6 text-center">
                    <div class="signature-line"></div>
                    <div class="mt-2">Date</div>
                </div>
            </div>
            
            <div class="text-center mt-5 pt-3">
                <div class="bg-light p-2 d-inline-block">
                    <strong>Form Generated On:</strong> <?= $currentDate ?>
                </div>
            </div>
        </div>
    </div>
     </div>
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Print Now
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            <i class="fas fa-times"></i> Close
        </button>
    </div>
    
    <!-- Bootstrap & jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/js/bootstrap5-compat.js?v=20260615b') ?>"></script>
    
    <script>
        // Automatically trigger print dialog when page loads
        $(document).ready(function() {
            // Only trigger print if URL has ?print parameter
            if(window.location.search.includes('?print')) {
                window.print();
            }
        });
    </script>
</body>
</html>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .print-container, .print-container * {
        visibility: visible;
    }
    .print-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .no-print {
        display: none !important;
    }
}
</style>
