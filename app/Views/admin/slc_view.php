<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
<meta name="csrf-name" content="<?= csrf_token() ?>">
   <!-- SweetAlert2 CSS and JS - Correct CDN links -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLC - <?= esc(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #e0e0e0;
            font-family: 'Times New Roman', Times, serif;
            display: flex;
            justify-content: center;
            padding: 30px 0;
        }

        /* A4 Page Setup */
        .a4-page {
            width: 210mm;
            min-height: 297mm;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            position: relative;
        }

        .certificate {
            height: 100%;
            padding: 15mm 12mm;
            position: relative;
            border: 2px solid #000;
        }

        .corner {
            position: absolute;
            width: 15mm;
            height: 15mm;
            border: 2px solid #000;
        }

        .corner-tl {
            top: 8mm;
            left: 8mm;
            border-right: none;
            border-bottom: none;
        }

        .corner-tr {
            top: 8mm;
            right: 8mm;
            border-left: none;
            border-bottom: none;
        }

        .corner-bl {
            bottom: 8mm;
            left: 8mm;
            border-right: none;
            border-top: none;
        }

        .corner-br {
            bottom: 8mm;
            right: 8mm;
            border-left: none;
            border-top: none;
        }

        /* Header */
        .header {
            display: flex;
            align-items: center;
            margin-bottom: 10mm;
            padding-bottom: 3mm;
            border-bottom: 3px solid #000;
        }

        .logo-area {
            flex: 0 0 25mm;
            text-align: center;
        }

        .school-logo {
            width: 22mm;
            height: 22mm;
            object-fit: contain;
            border: 1px solid #000;
            padding: 2mm;
        }

        .logo-placeholder {
            width: 22mm;
            height: 22mm;
            border: 2px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            background: #f0f0f0;
        }

        .school-info {
            flex: 1;
            text-align: center;
            padding: 0 5mm;
        }

        .school-name {
            font-size: 28px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 2mm;
        }

        .school-address {
            font-size: 14px;
            color: #333;
            margin-bottom: 1mm;
        }

        .school-contact {
            font-size: 12px;
            color: #444;
        }

        .slc-number {
            flex: 0 0 35mm;
            text-align: right;
            border: 1px solid #000;
            padding: 3mm;
            background: #f8f8f8;
        }

        .slc-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #555;
        }

        .slc-value {
            font-size: 18px;
            font-weight: bold;
            font-family: 'Courier New', monospace;
        }

        /* Title */
        .title-section {
            text-align: center;
            margin: 8mm 0 10mm;
        }

        .main-title {
            font-size: 32px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 3px;
            display: inline-block;
            padding: 0 10mm;
            position: relative;
        }

        .main-title:before,
        .main-title:after {
            content: "•";
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 24px;
        }

        .main-title:before {
            left: 0;
        }

        .main-title:after {
            right: 0;
        }

        /* Student Info Tables */
        .student-info-container {
            display: flex;
            gap: 5mm;
            margin-bottom: 8mm;
        }

        .photo-section {
            flex: 0 0 30mm;
        }

        .photo-frame {
            width: 30mm;
            height: 35mm;
            border: 2px solid #000;
            overflow: hidden;
        }

        .student-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #f0f0f0;
            color: #666;
            font-size: 10px;
        }

        .photo-placeholder i {
            font-size: 20px;
            margin-bottom: 2px;
        }

        .info-tables {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 3mm;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }

        .info-table th {
            background: #333;
            color: white;
            padding: 2mm;
            font-size: 12px;
            font-weight: 600;
            text-align: left;
            border: 1px solid #000;
        }

        .info-table td {
            padding: 2.5mm;
            border: 1px solid #999;
            font-size: 14px;
        }

        .info-table td.label {
            font-weight: 600;
            background: #f0f0f0;
            width: 35%;
        }

        .info-table td.value {
            font-weight: 500;
        }

        .dob-words {
            font-size: 11px;
            font-style: italic;
            color: #555;
            display: block;
            margin-top: 1mm;
        }

        /* Academic Table */
        .academic-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6mm 0;
            border: 1px solid #000;
        }

        .academic-table th {
            background: #333;
            color: white;
            padding: 3mm;
            font-size: 14px;
            font-weight: 600;
            text-align: left;
            border: 1px solid #000;
        }

        .academic-table td {
            padding: 3mm;
            border: 1px solid #999;
            font-size: 14px;
        }

        .academic-table td.label {
            font-weight: 600;
            background: #f0f0f0;
            width: 35%;
        }

        .academic-table td.value {
            font-weight: 500;
        }

        /* Status Box */
        .status-box {
            margin: 6mm 0;
            padding: 4mm 6mm;
            border: 2px solid #000;
            background: #fff;
            display: flex;
            align-items: center;
            gap: 5mm;
        }

        .status-paid {
            background: #f0f0f0;
        }

        .status-unpaid {
            background: #e0e0e0;
            border: 3px double #000;
        }

        .status-icon {
            font-size: 24px;
        }

        .status-content {
            flex: 1;
        }

        .status-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 2mm;
        }

        .status-message {
            font-size: 13px;
            color: #333;
        }

        /* Declaration */
        .declaration {
            margin: 6mm 0;
            padding: 5mm;
            border: 1px solid #333;
            background: #f8f8f8;
        }

        .declaration-text {
            font-size: 14px;
            line-height: 1.6;
            text-align: justify;
        }

        /* Signatures - Updated as requested */
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 12mm;
            padding: 3mm 0;
        }

        .signature-item {
            text-align: center;
            width: 70mm;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 100%;
            margin: 2mm 0 1mm;
        }

        .signature-label {
            font-size: 11px;
            color: #444;
            text-transform: uppercase;
            margin-bottom: 2mm;
            font-weight: 600;
        }

        .signature-caption {
            font-size: 12px;
            font-weight: normal;
            margin-top: 1mm;
        }

        .seal-mark {
            margin-top: 3mm;
            font-size: 10px;
            color: #666;
            border: 1px solid #999;
            display: inline-block;
            padding: 1mm 3mm;
        }

        /* Footer */
        .footer {
            margin-top: 5mm;
            padding-top: 3mm;
            border-top: 1px dashed #999;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        /* Action Buttons */
        .action-buttons {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 1000;
        }

        .btn {
            padding: 10px 20px;
            border: 2px solid #000;
            background: #fff;
            color: #000;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn:hover {
            background: #000;
            color: #fff;
        }
        
        .btn-edit {
            border-color: #000;
        }
        
        .btn-print {
            border-color: #000;
        }
        
        .btn-back {
            border-color: #000;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.6);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 30px auto;
            padding: 0;
            border: 2px solid #000;
            width: 90%;
            max-width: 900px;
            border-radius: 0;
        }

        .modal-header {
            background: #333;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #000;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 18px;
        }

        .modal-header .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .modal-body {
            padding: 25px;
            max-height: 70vh;
            overflow-y: auto;
        }

        .modal-footer {
            padding: 15px 20px;
            background: #f0f0f0;
            border-top: 2px solid #000;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* Form Styles */
        .edit-form {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .form-group {
            margin-bottom: 10px;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
            font-size: 13px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px 10px;
            border: 2px solid #999;
            font-size: 14px;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #000;
        }

        .btn-save {
            background: #333;
            color: white;
            padding: 10px 25px;
            border: 2px solid #000;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-save:hover {
            background: #000;
        }

        .btn-cancel {
            background: #999;
            color: white;
            padding: 10px 25px;
            border: 2px solid #000;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-cancel:hover {
            background: #666;
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .action-buttons {
                display: none;
            }
            
            .a4-page {
                box-shadow: none;
                width: 100%;
                height: 100%;
            }
            
            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .info-table th,
            .academic-table th {
                background: black !important;
                color: white !important;
            }
            
            .status-box {
                background: #f0f0f0 !important;
            }
            
            .status-unpaid {
                border: 3px double black !important;
            }
            
            .signature-line {
                border-top: 1px solid black !important;
            }
        }
    </style>
</head>
<body>

    <div class="action-buttons">
        <a href="<?= base_url('admin/addbulkstudents/add') ?>" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <button class="btn btn-edit" onclick="openEditModal()">
            <i class="fas fa-edit"></i> Edit
        </button>
        <button class="btn btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Print
        </button>
    </div>

    <!-- Edit Modal -->
  <!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Edit Student Informationnnnn</h3>
            <span class="close" onclick="closeEditModal()">&times;</span>
        </div>
        <div class="modal-body" id="editModalBody">
            <div style="text-align: center; padding: 40px;">
                <i class="fas fa-spinner fa-spin" style="font-size: 40px;"></i>
                <p style="margin-top: 20px;">Loading form...</p>
            </div>
        </div>
    </div>
</div>

<!-- Add this script right after the modal -->
<script>
// Global function to handle update
window.handleSLCUpdate = async function() {
    console.log('Global update function called');
    
    const studentId = document.getElementById('student_id')?.value;
    const firstName = document.getElementById('first_name')?.value;
    const lastName = document.getElementById('last_name')?.value;
    const dob = document.getElementById('dob')?.value;
    const admissionDate = document.getElementById('admission_date')?.value;
    const fatherName = document.getElementById('father_name')?.value;
    const motherName = document.getElementById('mother_name')?.value;
    const religion = document.getElementById('religion')?.value;
    const nationality = document.getElementById('nationality')?.value;
    const leavingDate = document.getElementById('leaving_date')?.value;
    const conduct = document.getElementById('conduct')?.value;
    const leavingReason = document.getElementById('leaving_reason')?.value;
    const skipFeeCheckbox = document.getElementById('skip_pending_fee');
    const skipPendingFee = skipFeeCheckbox?.checked ? '1' : '0';
    
    // Validate
    if (!firstName || !fatherName || !dob) {
        alert('Please fill all required fields (First Name, Father\'s Name, Date of Birth)');
        return;
    }
    
    const updateBtn = document.getElementById('updateBtn');
    const originalHtml = updateBtn?.innerHTML;
    if (updateBtn) {
        updateBtn.disabled = true;
        updateBtn.innerHTML = '<span class="loading-spinner"></span> Updating...';
    }
    
    // Create form data
    const formData = new URLSearchParams();
    formData.append('student_id', studentId);
    formData.append('first_name', firstName);
    formData.append('last_name', lastName || '');
    formData.append('dob', dob);
    formData.append('admission_date', admissionDate || '');
    formData.append('father_name', fatherName);
    formData.append('mother_name', motherName || '');
    formData.append('religion', religion || '');
    formData.append('nationality', nationality || 'Pakistani');
    formData.append('leaving_date', leavingDate || '');
    formData.append('conduct', conduct || 'Good');
    formData.append('leaving_reason', leavingReason || '');
    formData.append('skip_pending_fee', skipPendingFee);
    
    console.log('Sending data:', Object.fromEntries(formData));
    
    try {
        const response = await fetch('<?= base_url('admin/addbulkstudents/update-student-info') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
        
        const data = await response.json();
        console.log('Response:', data);
        
        if (data.status === 'success') {
            alert('Student information updated successfully!');
            closeEditModal();
            setTimeout(() => window.location.reload(), 500);
        } else {
            alert('Error: ' + (data.message || 'Update failed'));
            if (updateBtn) {
                updateBtn.disabled = false;
                updateBtn.innerHTML = originalHtml;
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error: ' + error.message);
        if (updateBtn) {
            updateBtn.disabled = false;
            updateBtn.innerHTML = originalHtml;
        }
    }
};

// Global function to handle cancel
window.handleSLCCancel = function() {
    console.log('Global cancel function called');
    closeEditModal();
};

// Function to attach events after form loads
window.attachFormEvents = function() {
    console.log('Attaching form events');
    
    const updateBtn = document.getElementById('updateBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    
    if (updateBtn) {
        console.log('Found update button, attaching click handler');
        updateBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Update button clicked');
            window.handleSLCUpdate();
        };
    } else {
        console.log('Update button not found yet');
    }
    
    if (cancelBtn) {
        console.log('Found cancel button, attaching click handler');
        cancelBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Cancel button clicked');
            window.handleSLCCancel();
        };
    } else {
        console.log('Cancel button not found yet');
    }
};

// Updated openEditModal function
window.openEditModal = function() {
    console.log('Opening edit modal');
    const modal = document.getElementById('editModal');
    const modalBody = document.getElementById('editModalBody');
    
    if (!modal || !modalBody) {
        console.error('Modal elements not found');
        return;
    }
    
    modal.style.display = 'block';
    
    modalBody.innerHTML = `
        <div style="text-align: center; padding: 40px;">
            <i class="fas fa-spinner fa-spin" style="font-size: 40px;"></i>
            <p style="margin-top: 20px;">Loading form...</p>
        </div>
    `;
    
    const studentId = '<?= $student['student_id'] ?? '' ?>';
    const csrfName = '<?= csrf_token() ?>';
    const csrfHash = '<?= csrf_hash() ?>';
    
    fetch('<?= base_url('admin/addbulkstudents/get-edit-form') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            student_id: studentId,
            [csrfName]: csrfHash
        })
    })
    .then(response => response.text())
    .then(html => {
        modalBody.innerHTML = html;
        console.log('Form loaded, attaching events...');
        
        // Small delay to ensure DOM is updated
        setTimeout(() => {
            window.attachFormEvents();
        }, 100);
    })
    .catch(error => {
        console.error('Error loading form:', error);
        modalBody.innerHTML = '<div style="text-align: center; padding: 50px; color: #b91c1c;"><i class="fas fa-exclamation-circle"></i> Error loading form. Please try again.</div>';
    });
};

// Keep the original closeEditModal
window.closeEditModal = function() {
    console.log('Closing edit modal');
    const modal = document.getElementById('editModal');
    if (modal) {
        modal.style.display = 'none';
    }
};

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        window.closeEditModal();
    }
};

console.log('Global SLC edit functions registered');
</script>

    <!-- Add this after the action-buttons div -->
<div class="search-container" style="position: fixed; top: 20px; left: 20px; z-index: 1000; width: 300px;">
    <div class="search-box" style="background: white; border: 2px solid #000; padding: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="display: flex; gap: 5px;">
            <input type="text" 
                   id="slcSearch" 
                   placeholder="Search SLC by student name..." 
                   style="flex: 1; padding: 8px; border: 1px solid #999; font-size: 14px;"
                   autocomplete="off">
            <button class="btn" style="padding: 8px 15px;" onclick="searchSLC()">
                <i class="fas fa-search"></i>
            </button>
        </div>
        <div id="searchResults" style="display: none; position: absolute; background: white; border: 1px solid #000; width: 100%; max-height: 300px; overflow-y: auto; margin-top: 5px; z-index: 1001;"></div>
    </div>
</div>

<!-- Add this CSS -->
<style>
    .search-container {
        font-family: 'Times New Roman', Times, serif;
    }
    
    #slcSearch {
        font-family: 'Times New Roman', Times, serif;
    }
    
    .search-result-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
        transition: background 0.2s;
    }
    
    .search-result-item:hover {
        background: #f0f0f0;
    }
    
    .search-result-item .student-name {
        font-weight: bold;
        color: #000;
    }
    
    .search-result-item .slc-info {
        font-size: 12px;
        color: #666;
        margin-top: 3px;
    }
    
    .search-result-item .slc-info span {
        margin-right: 10px;
    }
    
    .no-results {
        padding: 15px;
        text-align: center;
        color: #666;
        font-style: italic;
    }
    
    /* Adjust for print */
    @media print {
        .search-container {
            display: none;
        }
    }
</style>

    <?php
    // Helper function to convert number to words
  function numberToWords($number) {
    $words = array(
        0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
        6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
        11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
        16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
        21 => 'Twenty One', 22 => 'Twenty Two', 23 => 'Twenty Three', 24 => 'Twenty Four', 25 => 'Twenty Five',
        26 => 'Twenty Six', 27 => 'Twenty Seven', 28 => 'Twenty Eight', 29 => 'Twenty Nine', 30 => 'Thirty',
        31 => 'Thirty One', 32 => 'Thirty Two', 33 => 'Thirty Three', 34 => 'Thirty Four', 35 => 'Thirty Five',
        36 => 'Thirty Six', 37 => 'Thirty Seven', 38 => 'Thirty Eight', 39 => 'Thirty Nine', 40 => 'Forty',
        41 => 'Forty One', 42 => 'Forty Two', 43 => 'Forty Three', 44 => 'Forty Four', 45 => 'Forty Five',
        46 => 'Forty Six', 47 => 'Forty Seven', 48 => 'Forty Eight', 49 => 'Forty Nine', 50 => 'Fifty',
        51 => 'Fifty One', 52 => 'Fifty Two', 53 => 'Fifty Three', 54 => 'Fifty Four', 55 => 'Fifty Five',
        56 => 'Fifty Six', 57 => 'Fifty Seven', 58 => 'Fifty Eight', 59 => 'Fifty Nine', 60 => 'Sixty',
        61 => 'Sixty One', 62 => 'Sixty Two', 63 => 'Sixty Three', 64 => 'Sixty Four', 65 => 'Sixty Five',
        66 => 'Sixty Six', 67 => 'Sixty Seven', 68 => 'Sixty Eight', 69 => 'Sixty Nine', 70 => 'Seventy',
        71 => 'Seventy One', 72 => 'Seventy Two', 73 => 'Seventy Three', 74 => 'Seventy Four', 75 => 'Seventy Five',
        76 => 'Seventy Six', 77 => 'Seventy Seven', 78 => 'Seventy Eight', 79 => 'Seventy Nine', 80 => 'Eighty',
        81 => 'Eighty One', 82 => 'Eighty Two', 83 => 'Eighty Three', 84 => 'Eighty Four', 85 => 'Eighty Five',
        86 => 'Eighty Six', 87 => 'Eighty Seven', 88 => 'Eighty Eight', 89 => 'Eighty Nine', 90 => 'Ninety',
        91 => 'Ninety One', 92 => 'Ninety Two', 93 => 'Ninety Three', 94 => 'Ninety Four', 95 => 'Ninety Five',
        96 => 'Ninety Six', 97 => 'Ninety Seven', 98 => 'Ninety Eight', 99 => 'Ninety Nine', 100 => 'One Hundred',
        101 => 'One Hundred One', 200 => 'Two Hundred', 300 => 'Three Hundred', 400 => 'Four Hundred', 500 => 'Five Hundred',
        600 => 'Six Hundred', 700 => 'Seven Hundred', 800 => 'Eight Hundred', 900 => 'Nine Hundred', 1000 => 'One Thousand',
        2000 => 'Two Thousand', 3000 => 'Three Thousand', 4000 => 'Four Thousand', 5000 => 'Five Thousand'
    );
    
    if (isset($words[$number])) {
        return $words[$number];
    }
    
    // For numbers not in array, do basic conversion
    if ($number < 100) {
        $tens = floor($number / 10) * 10;
        $ones = $number % 10;
        return ($tens > 0 ? $words[$tens] : '') . ($ones > 0 ? ' ' . $words[$ones] : '');
    }
    
    return $number;
}

    // Function to convert date to words
    function dateToWords($date) {
    if (empty($date)) return '';
    
    $timestamp = strtotime($date);
    $day = (int)date('j', $timestamp);
    $month = date('F', $timestamp);
    $year = (int)date('Y', $timestamp);
    
    // Split year into components for better readability
    $yearInWords = numberToWords($year);
    
    return numberToWords($day) . ' ' . $month . ' ' . $yearInWords;
}

    // Determine gender-based pronouns
    $gender = strtolower($student['gender'] ?? '');
    if ($gender == 'male') {
        $pronoun_subject = 'He';
        $pronoun_object = 'him';
        $pronoun_possessive = 'His';
        $title = 'Mr.';
        $son_daughter = 'son';
    } elseif ($gender == 'female') {
        $pronoun_subject = 'She';
        $pronoun_object = 'her';
        $pronoun_possessive = 'Her';
        $title = 'Ms.';
        $son_daughter = 'daughter';
    } else {
        $pronoun_subject = 'The student';
        $pronoun_object = 'the student';
        $pronoun_possessive = 'The student\'s';
        $title = 'Mr./Ms.';
        $son_daughter = 'son/daughter';
    }
    ?>

    <!-- A4 Certificate -->
    <div class="a4-page">
        <div class="certificate">
            <!-- Corner Decorations -->
            <div class="corner corner-tl"></div>
            <div class="corner corner-tr"></div>
            <div class="corner corner-bl"></div>
            <div class="corner corner-br"></div>

            <!-- Header -->
            <div class="header">
                <div class="logo-area">
                    <?php if (!empty($school->logo)): ?>
                        <img src="<?= base_url('system-logo/' . $school->logo) ?>" alt="School Logo" class="school-logo">
                    <?php else: ?>
                        <div class="logo-placeholder">
                            <span>SCHOOL</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="school-info">
                    <h1 class="school-name"><?= esc($school->system_name ?? 'School name') ?></h1>
                    <div class="school-address">
                        <?= esc($school->address ?? 'Main Boulevard, Education City') ?>
                    </div>
                    <div class="school-contact">
                        <?= esc($school->landline_number ?? '+92 123 456789') ?> | 
                        <?= esc($school->email ?? 'info@school.edu') ?>
                    </div>
                </div>
                
                <div class="slc-number">
                    <div class="slc-label">Certificate No.</div>
                    <div class="slc-value"><?= esc($slc['slc_no'] ?? 'SLC/' . date('Y') . '/001') ?></div>
                </div>
            </div>

            <!-- Title -->
            <div class="title-section">
                <h2 class="main-title">SCHOOL LEAVING CERTIFICATE</h2>
            </div>

            <!-- Student Information in Tabular Form -->
            <div class="student-info-container">
                <div class="photo-section">
                    <div class="photo-frame">
                        <?php if (!empty($student['profile_photo'])): ?>
                            <img src="<?= base_url('uploads/' . $student['profile_photo']) ?>" alt="Student" class="student-photo">
                        <?php else: ?>
                            <div class="photo-placeholder">
                                <i class="fas fa-user"></i>
                                <span>Photo</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                
                <div class="info-tables">
                    <!-- Personal Information Table -->
                    <table class="info-table">
                        <thead>
                            <tr>
                                <th colspan="2">PERSONAL INFORMATION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="label">Admission No.</td>
                                <td class="value"><?= esc($student['reg_no'] ?? 'REG-' . date('Y') . '-001') ?></td>
                            </tr>
                            <tr>
                                <td class="label">Student Name</td>
                                <td class="value"><?= esc(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></td>
                            </tr>
                            <tr>
                                <td class="label">Father's Name</td>
                                <td class="value"><?= esc($student['f_name'] ?? '____________________') ?></td>
                            </tr>
                            <tr>
                                <td class="label">Date of Birth</td>
    <td class="value">
        <?php if (!empty($student['date_of_birth'])): ?>
            <?= date('d-m-Y', strtotime($student['date_of_birth'])) ?>
            <span class="dob-words">
                (<?= dateToWords($student['date_of_birth']) ?>)
            </span>
        <?php else: ?>
            ________
        <?php endif; ?>
    </td>
                            </tr>
                            <tr>
                                <td class="label">Gender</td>
                                <td class="value"><?= ucfirst(esc($student['gender'] ?? 'Not Specified')) ?></td>
                            </tr>
                            <tr>
                                <td class="label">Nationality</td>
                                <td class="value"><?= esc($student['nationality'] ?? 'Pakistani') ?></td>
                            </tr>
                        </tbody>
                    </table>

                  
                </div>
            </div>

            <!-- Academic Information Table -->
            <table class="academic-table">
                <thead>
                    <tr>
                        <th colspan="2">ACADEMIC RECORD</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="label">Admission Class</td>
                        <td class="value">
                            <?= esc($admission_class ?? '________________') ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Date of Admission</td>
                        <td class="value"><?= !empty($student['date_of_admission']) ? date('d-m-Y', strtotime($student['date_of_admission'])) : '________________' ?></td>
                    </tr>
                    <tr>
                        <td class="label">Class at Leaving</td>
                        <td class="value"><?= esc($class['class_name'] ?? '________________') ?> <?= !empty($class['section_name']) ? '- ' . esc($class['section_name']) : '' ?></td>
                    </tr>
                    <tr>
                        <td class="label">Date of Leaving</td>
                        <td class="value"><?= !empty($slc['leaving_date']) ? date('d-m-Y', strtotime($slc['leaving_date'])) : '________________' ?></td>
                    </tr>
                    <tr>
                        <td class="label">Reason for Leaving</td>
                        <td class="value"><?= esc($slc['leaving_reason'] ?? 'On Request / Transfer') ?></td>
                    </tr>
                    <tr>
                        <td class="label">General Conduct</td>
                        <td class="value"><?= esc($slc['conduct'] ?? 'Good') ?></td>
                    </tr>
                </tbody>
            </table>

            <!-- Financial Status -->
            <div class="status-box <?= ($has_dues && !$skip_fee) ? 'status-unpaid' : 'status-paid' ?>">
                <div class="status-icon">
                    <i class="fas <?= ($has_dues && !$skip_fee) ? 'fa-exclamation-triangle' : 'fa-check' ?>"></i>
                </div>
                <div class="status-content">
                    <div class="status-title">
                        <?php if ($skip_fee): ?>
                            FEE VERIFICATION SKIPPED
                        <?php elseif ($has_dues): ?>
                            PAYMENT PENDING
                        <?php else: ?>
                            ALL DUES CLEARED
                        <?php endif; ?>
                    </div>

                    <!-- Outstanding Fee Management Section -->
<div class="card mt-4">
    <div class="card-header bg-warning text-white">
        <h5 class="mb-0">
            <i class="fas fa-money-bill-wave mr-2"></i> 
            Fee Management
            <span id="outstandingBadge" class="badge badge-light ml-2"></span>
        </h5>
    </div>
    <div class="card-body">
        <div id="feeLoading" class="text-center py-4" style="display: none;">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2">Loading fee details...</p>
        </div>
        
        <div id="feeContent" style="display: none;">
            <!-- Outstanding Balance Summary -->
            <div class="alert alert-info mb-3" id="outstandingSummary">
                <i class="fas fa-info-circle mr-2"></i>
                <span id="outstandingMessage">Loading...</span>
            </div>
            
            <!-- Fee Entries Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="feeEntriesTable">
                    <thead class="bg-light">
                        <tr>
                            <th width="40"><input type="checkbox" id="selectAllFee"></th>
                            <th>Fee Month</th>
                            <th>Fee Type</th>
                            <th>Total Amount</th>
                            <th>Already Paid</th>
                            <th>Remaining</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="feeEntriesBody">
                        <td><td colspan="7" class="text-center">No outstanding fees</td></tr>
                    </tbody>
                    <tfoot id="feeTableFooter" style="display: none;">
                        <tr class="bg-light">
                            <th colspan="3" class="text-right">Total Selected:</th>
                            <th id="selectedTotal">0.00</th>
                            <th id="selectedPaid">0.00</th>
                            <th id="selectedRemaining">0.00</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <!-- Payment Actions Panel -->
            <div id="paymentActions" class="mt-3" style="display: none;">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-credit-card mr-2"></i> Payment Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fas fa-tag"></i> Action Type</label>
                                    <select id="paymentType" class="form-control">
                                        <option value="full">Full Payment</option>
                                        <option value="partial">Partial Payment</option>
                                        <option value="discount">Apply Discount</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fas fa-calendar-alt"></i> Payment Date</label>
                                    <input type="date" id="paymentDate" class="form-control" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fas fa-wallet"></i> Payment Method</label>
                                    <select id="paymentMethod" class="form-control">
                                        <option value="cash">Cash</option>
                                        <option value="bank">Bank Transfer</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="online">Online Payment</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group" id="amountInputGroup">
                                    <label><i class="fas fa-rupee-sign"></i> Amount</label>
                                    <input type="number" id="paymentAmount" class="form-control" step="100" placeholder="Enter amount">
                                </div>
                                <div class="form-group" id="discountInputGroup" style="display: none;">
                                    <label><i class="fas fa-tag"></i> Discount Amount</label>
                                    <input type="number" id="discountAmount" class="form-control" step="100" placeholder="Enter discount">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><i class="fas fa-sticky-note"></i> Remarks</label>
                                    <textarea id="paymentRemarks" class="form-control" rows="2" placeholder="Add remarks..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-right">
                                <button type="button" id="processPaymentBtn" class="btn btn-success">
                                    <i class="fas fa-check-circle mr-1"></i> Process Payment
                                </button>
                                <button type="button" id="clearSelectionBtn" class="btn btn-secondary">
                                    <i class="fas fa-times mr-1"></i> Clear Selection
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal for Confirmation -->
<div class="modal fade" id="paymentConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-circle mr-2"></i> Confirm Payment</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="paymentConfirmBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" id="confirmPaymentBtn" class="btn btn-success">Confirm & Process</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const studentId = <?= $student['student_id'] ?? 0 ?>;
    
    if (studentId) {
        loadOutstandingFee(studentId);
    }
    
    function loadOutstandingFee(studentId) {
        $('#feeLoading').show();
        $('#feeContent').hide();
        
        $.ajax({
            url: '<?= site_url('admin/students/get_outstanding_fee') ?>',
            type: 'POST',
            data: {
                student_id: studentId,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                $('#feeLoading').hide();
                
                if (response.success) {
                    displayFeeEntries(response.data, response.total_outstanding);
                } else {
                    $('#feeContent').html('<div class="alert alert-danger">Error loading fee data</div>');
                    $('#feeContent').show();
                }
            },
            error: function() {
                $('#feeLoading').hide();
                $('#feeContent').html('<div class="alert alert-danger">Failed to load fee data</div>');
                $('#feeContent').show();
            }
        });
    }
    
    function displayFeeEntries(entries, totalOutstanding) {
        if (!entries || entries.length === 0) {
            $('#feeEntriesBody').html('<tr><td colspan="7" class="text-center text-muted">No outstanding fees found</td></tr>');
            $('#outstandingSummary').html('<i class="fas fa-check-circle mr-2"></i> All fees are cleared! No outstanding balance.');
            $('#outstandingSummary').removeClass('alert-info').addClass('alert-success');
            $('#paymentActions').hide();
            $('#feeTableFooter').hide();
            $('#outstandingBadge').text('');
            return;
        }
        
        let html = '';
        let totalRemaining = 0;
        
        entries.forEach(function(entry) {
            const remaining = entry.remaining || entry.net_amount;
            totalRemaining += remaining;
            
            html += `<tr data-chalan-id="${entry.chalan_id}" data-fee-month="${entry.fee_month}" 
                           data-fee-type-id="${entry.fee_type_id}" data-net-amount="${entry.net_amount}" 
                           data-remaining="${remaining}">
                        <td><input type="checkbox" class="fee-selector" data-remaining="${remaining}" data-net-amount="${entry.net_amount}"></td>
                        <td>${formatMonthYear(entry.fee_month)}</td>
                        <td>${escapeHtml(entry.fee_type_name)}</td>
                        <td class="text-right">${formatNumber(entry.net_amount)}</td>
                        <td class="text-right">${formatNumber(entry.total_paid || 0)}</td>
                        <td class="text-right text-danger font-weight-bold remaining-amount">${formatNumber(remaining)}</td>
                        <td>
                            ${entry.partial_payments && entry.partial_payments.length > 0 ? 
                                `<button type="button" class="btn btn-sm btn-info view-payments" data-chalan-id="${entry.chalan_id}">
                                    <i class="fas fa-history"></i> History
                                </button>` : ''}
                        </td>
                     </tr>`;
            
            // Add partial payment history row (hidden by default)
            if (entry.partial_payments && entry.partial_payments.length > 0) {
                let historyHtml = `<tr id="history-${entry.chalan_id}" class="payment-history-row" style="display: none;">
                    <td colspan="7">
                        <div class="bg-light p-2">
                            <strong>Payment History:</strong>
                            <table class="table table-sm table-borderless mb-0">
                                <thead>
                                    <tr><th>Date</th><th>Amount</th><th>Method</th><th>Transaction ID</th></tr>
                                </thead>
                                <tbody>`;
                entry.partial_payments.forEach(function(payment) {
                    historyHtml += `<tr>
                        <td>${payment.payment_date}</td>
                        <td>${formatNumber(payment.amount_paid)}</td>
                        <td>${payment.payment_method}</td>
                        <td>${payment.transaction_id || 'N/A'}</td>
                    </tr>`;
                });
                historyHtml += `</tbody></table></div></td></tr>`;
                html += historyHtml;
            }
        });
        
        $('#feeEntriesBody').html(html);
        $('#outstandingSummary').html(`<i class="fas fa-exclamation-triangle mr-2"></i> Total Outstanding Balance: <strong>PKR ${formatNumber(totalRemaining)}</strong>`);
        $('#outstandingSummary').removeClass('alert-success').addClass('alert-warning');
        $('#outstandingBadge').html(`PKR ${formatNumber(totalRemaining)}`);
        $('#paymentActions').show();
        $('#feeTableFooter').show();
        
        // Update selected totals when checkboxes change
        $('.fee-selector').on('change', updateSelectedTotals);
        $('#selectAllFee').on('change', function() {
            $('.fee-selector').prop('checked', $(this).prop('checked')).trigger('change');
        });
        
        // View payment history
        $('.view-payments').on('click', function() {
            const chalanId = $(this).data('chalan-id');
            $('#history-' + chalanId).toggle();
        });
        
        updateSelectedTotals();
    }
    
    function updateSelectedTotals() {
        let totalSelected = 0;
        let totalRemaining = 0;
        
        $('.fee-selector:checked').each(function() {
            const row = $(this).closest('tr');
            totalSelected += parseFloat(row.data('net-amount')) || 0;
            totalRemaining += parseFloat(row.data('remaining')) || 0;
        });
        
        $('#selectedTotal').text(formatNumber(totalSelected));
        $('#selectedRemaining').text(formatNumber(totalRemaining));
        
        // Update payment amount max
        const paymentType = $('#paymentType').val();
        if (paymentType === 'full') {
            $('#paymentAmount').attr('max', totalRemaining).val(totalRemaining);
            $('#paymentAmount').prop('readonly', true);
        } else if (paymentType === 'partial') {
            $('#paymentAmount').attr('max', totalRemaining).val('').prop('readonly', false);
        } else if (paymentType === 'discount') {
            $('#discountAmount').attr('max', totalRemaining).val('');
        }
    }
    
    $('#paymentType').on('change', function() {
        const type = $(this).val();
        if (type === 'full') {
            $('#amountInputGroup').show();
            $('#discountInputGroup').hide();
            const remaining = parseFloat($('#selectedRemaining').text().replace(/,/g, '')) || 0;
            $('#paymentAmount').val(remaining).prop('readonly', true);
        } else if (type === 'partial') {
            $('#amountInputGroup').show();
            $('#discountInputGroup').hide();
            $('#paymentAmount').prop('readonly', false).val('');
        } else if (type === 'discount') {
            $('#amountInputGroup').hide();
            $('#discountInputGroup').show();
        }
    });
    
    $('#processPaymentBtn').on('click', function() {
        const selectedRows = [];
        $('.fee-selector:checked').each(function() {
            const row = $(this).closest('tr');
            selectedRows.push({
                chalan_id: row.data('chalan-id'),
                fee_month: row.data('fee-month'),
                fee_type_id: row.data('fee-type-id'),
                net_amount: row.data('net-amount'),
                remaining: row.data('remaining')
            });
        });
        
        if (selectedRows.length === 0) {
            Swal.fire('No Selection', 'Please select at least one fee entry to process', 'warning');
            return;
        }
        
        const paymentType = $('#paymentType').val();
        let amount = 0;
        let discount = 0;
        
        if (paymentType === 'full') {
            amount = parseFloat($('#selectedRemaining').text().replace(/,/g, '')) || 0;
            if (amount <= 0) {
                Swal.fire('Invalid Amount', 'No amount to pay', 'warning');
                return;
            }
        } else if (paymentType === 'partial') {
            amount = parseFloat($('#paymentAmount').val()) || 0;
            if (amount <= 0) {
                Swal.fire('Invalid Amount', 'Please enter a valid payment amount', 'warning');
                return;
            }
            const totalRemaining = parseFloat($('#selectedRemaining').text().replace(/,/g, '')) || 0;
            if (amount > totalRemaining) {
                Swal.fire('Invalid Amount', `Payment amount cannot exceed remaining balance of ${formatNumber(totalRemaining)}`, 'warning');
                return;
            }
        } else if (paymentType === 'discount') {
            discount = parseFloat($('#discountAmount').val()) || 0;
            if (discount <= 0) {
                Swal.fire('Invalid Discount', 'Please enter a valid discount amount', 'warning');
                return;
            }
        }
        
        // Build confirmation message
        let confirmHtml = `<p><strong>Selected Fee Entries:</strong> ${selectedRows.length}</p>
                          <p><strong>Total Remaining:</strong> PKR ${$('#selectedRemaining').text()}</p>`;
        
        if (paymentType === 'full') {
            confirmHtml += `<p><strong>Action:</strong> Full Payment</p>
                           <p><strong>Amount to Pay:</strong> PKR ${formatNumber(amount)}</p>`;
        } else if (paymentType === 'partial') {
            confirmHtml += `<p><strong>Action:</strong> Partial Payment</p>
                           <p><strong>Amount to Pay:</strong> PKR ${formatNumber(amount)}</p>`;
        } else {
            confirmHtml += `<p><strong>Action:</strong> Apply Discount</p>
                           <p><strong>Discount Amount:</strong> PKR ${formatNumber(discount)}</p>`;
        }
        
        confirmHtml += `<p><strong>Payment Method:</strong> ${$('#paymentMethod option:selected').text()}</p>
                        <p><strong>Payment Date:</strong> ${$('#paymentDate').val()}</p>`;
        
        $('#paymentConfirmBody').html(confirmHtml);
        $('#paymentConfirmModal').modal('show');
        
        $('#confirmPaymentBtn').off('click').on('click', function() {
            processPayment(selectedRows, paymentType, amount, discount);
        });
    });
    
    function processPayment(selectedRows, paymentType, amount, discount) {
        const btn = $('#confirmPaymentBtn');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        const feeEntries = selectedRows.map(row => ({
            chalan_id: row.chalan_id,
            fee_month: row.fee_month,
            fee_type_id: row.fee_type_id,
            net_amount: row.net_amount,
            paid_amount: paymentType === 'partial' ? amount / selectedRows.length : 
                        (paymentType === 'full' ? row.remaining : 0),
            discount_amount: paymentType === 'discount' ? discount / selectedRows.length : 0
        }));
        
        $.ajax({
            url: '<?= site_url('admin/students/process_fee_payment') ?>',
            type: 'POST',
            data: JSON.stringify({
                student_id: <?= $student['student_id'] ?? 0 ?>,
                payment_type: paymentType,
                fee_entries: feeEntries,
                payment_date: $('#paymentDate').val(),
                payment_method: $('#paymentMethod').val(),
                remarks: $('#paymentRemarks').val()
            }),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                $('#paymentConfirmModal').modal('hide');
                btn.prop('disabled', false).html('Confirm & Process');
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        html: `Payment processed successfully.<br>Remaining Balance: PKR ${formatNumber(response.remaining_total)}`,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        loadOutstandingFee(<?= $student['student_id'] ?? 0 ?>);
                        // Refresh the page to update student status
                        if (response.all_cleared) {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire('Error', response.msg || 'Payment processing failed', 'error');
                }
            },
            error: function(xhr) {
                $('#paymentConfirmModal').modal('hide');
                btn.prop('disabled', false).html('Confirm & Process');
                let errorMsg = 'An error occurred';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMsg = response.msg || errorMsg;
                } catch(e) {}
                Swal.fire('Error', errorMsg, 'error');
            }
        });
    }
    
    $('#clearSelectionBtn').on('click', function() {
        $('.fee-selector').prop('checked', false);
        $('#selectAllFee').prop('checked', false);
        updateSelectedTotals();
        $('#paymentAmount').val('');
        $('#discountAmount').val('');
    });
    
    function formatNumber(num) {
        if (num === undefined || num === null) return '0.00';
        return parseFloat(num).toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    
    function formatMonthYear(monthYear) {
        if (!monthYear) return 'N/A';
        const [year, month] = monthYear.split('-');
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return `${monthNames[parseInt(month)-1]} ${year}`;
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        return String(text).replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
});
</script>

<style>
    #feeEntriesTable tbody tr:hover {
        background-color: #f8f9fa;
    }
    .payment-history-row {
        background-color: #f9f9f9;
    }
    .payment-history-row td {
        padding: 10px !important;
    }
    #selectedTotal, #selectedRemaining, #selectedPaid {
        font-weight: bold;
    }
    .fee-selector {
        cursor: pointer;
    }
</style>


                    <div class="status-message">
                        <?php if ($skip_fee): ?>
                            Fee verification was skipped for this certificate as per admin request.
                        <?php elseif ($has_dues): ?>
                            Outstanding balance: Rs. <?= number_format($outstanding_balance ?? 0, 0) ?>/-. Must be cleared before certificate collection.
                        <?php else: ?>
                            Verified: All financial obligations have been fulfilled.
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Declaration -->
            <div class="declaration">
                <div class="declaration-text">
                    <strong>CERTIFIED</strong> that <?= $title ?> <strong><?= esc(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></strong> 
                    <?= $son_daughter ?> of <strong><?= esc($student['f_name'] ?? '____________________') ?></strong> 
                    was a student of this institution from <strong><?= !empty($student['date_of_admission']) ? date('d-m-Y', strtotime($student['date_of_admission'])) : '________' ?></strong> 
                    to <strong><?= !empty($slc['leaving_date']) ? date('d-m-Y', strtotime($slc['leaving_date'])) : '________' ?></strong>. 
                    
                    <?php if (!empty($student['date_of_birth'])): ?>
                        <?= $pronoun_possessive ?> date of birth, according to school records, is <strong><?= date('d-m-Y', strtotime($student['date_of_birth'])) ?></strong>.
                    <?php endif; ?>
                    
                    <?= $pronoun_possessive ?> conduct during this period was found to be <strong><?= esc($slc['conduct'] ?? 'Good') ?></strong>. 
                    <?= $pronoun_subject ?> is hereby granted this School Leaving Certificate for further studies.
                </div>
            </div>

            <!-- Signatures Section - Single line with caption -->
            <div class="signatures">
                <div class="signature-item">
                   
                    <div class="signature-line"></div>
                    <div class="signature-caption">Class Teacher</div>
                </div>
                
                <div class="signature-item">
                   
                    <?php if (!empty($principal_signature)): ?>
                        <div class="signature-image">
                            <img src="<?= base_url($principal_signature) ?>" 
                                 alt="Principal Signature" 
                                 style="max-width: 150px; max-height: 40px; margin: 2px 0;">
                        </div>
                    <?php else: ?>
                        <div class="signature-line"></div>
                    <?php endif; ?>
                    <div class="signature-caption">Principal</div>
                    <div class="seal-mark">(SCHOOL SEAL)</div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <span>Generated on: <?= date('d-m-Y') ?> | Certificate #: <?= esc($slc['slc_no'] ?? 'SLC/' . date('Y') . '/001') ?></span>
                <span style="margin-left: 10px;">| This is a computer generated certificate</span>
            </div>
        </div>
    </div>

    <script>
    

        function populateEditForm() {
            // Populate form with current data
            const fields = {
                'slc_student_id': '<?= $student['student_id'] ?? '' ?>',
                'slc_full_name': '<?= esc(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?>',
                'slc_father_name': '<?= esc($student['f_name'] ?? '') ?>',
                'slc_mother_name': '<?= esc($student['m_name'] ?? '') ?>',
                'slc_dob': '<?= $student['date_of_birth'] ?? '' ?>',
                'slc_gender': '<?= esc($student['gender'] ?? '') ?>',
                'slc_religion': '<?= esc($student['religion'] ?? '') ?>',
                'slc_nationality': '<?= esc($student['nationality'] ?? 'Pakistani') ?>',
                'slc_admission_date': '<?= $student['date_of_admission'] ?? '' ?>',
                'slc_class_admission': '<?= esc($student['class_at_admission'] ?? '') ?>',
                'slc_class': '<?= esc(($class['class_name'] ?? '') . ' - ' . ($class['section_name'] ?? '')) ?>',
                'slc_reg_no': '<?= esc($student['reg_no'] ?? '') ?>',
                'slc_father_contact': '<?= esc($student['father_contact'] ?? '') ?>',
                'slc_mother_contact': '<?= esc($student['mother_contact'] ?? '') ?>',
                'slc_emergency_contact': '<?= esc($student['emergency_contact'] ?? '') ?>',
                'slc_photo': '<?= esc($student['profile_photo'] ?? '') ?>'
            };

            for (let [id, value] of Object.entries(fields)) {
                const element = document.getElementById(id);
                if (element) element.value = value;
            }
        }

        // Save student information
        function saveStudentInfo() {
            const studentId = document.getElementById('slc_student_id').value;
            const fullName = document.getElementById('slc_full_name').value;
            const fatherName = document.getElementById('slc_father_name').value;
            const motherName = document.getElementById('slc_mother_name').value;
            const dob = document.getElementById('slc_dob').value;
            const gender = document.getElementById('slc_gender').value;
            const religion = document.getElementById('slc_religion').value;
            const nationality = document.getElementById('slc_nationality').value;
            const admissionDate = document.getElementById('slc_admission_date').value;
            const classAdmission = document.getElementById('slc_class_admission').value;
            const fatherContact = document.getElementById('slc_father_contact').value;
            const motherContact = document.getElementById('slc_mother_contact').value;
            const emergencyContact = document.getElementById('slc_emergency_contact').value;
            
            // Get SLC fields
            const leavingDate = document.getElementById('slc_leaving_date_edit')?.value || '';
            const leavingReason = document.getElementById('slc_leaving_reason_edit')?.value || '';
            const conduct = document.getElementById('slc_conduct_edit')?.value || 'Good';

            if (!fullName || !fatherName || !dob) {
                alert('Please fill all required fields');
                return;
            }

            const saveBtn = document.getElementById('saveAndGenerateSLC');
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            saveBtn.disabled = true;

            const nameParts = fullName.split(' ');
            const firstName = nameParts[0] || '';
            const lastName = nameParts.slice(1).join(' ') || '';

            fetch('<?= base_url('admin/addbulkstudents/update-student-info') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    student_id: studentId,
                    first_name: firstName,
                    last_name: lastName,
                    father_name: fatherName,
                    mother_name: motherName,
                    dob: dob,
                    gender: gender,
                    religion: religion,
                    nationality: nationality,
                    admission_date: admissionDate,
                    class_at_admission: classAdmission,
                    father_contact: fatherContact,
                    mother_contact: motherContact,
                    emergency_contact: emergencyContact,
                    leaving_date: leavingDate,
                    leaving_reason: leavingReason,
                    conduct: conduct,
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Information updated successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (data.msg || 'Update failed'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving');
            })
            .finally(() => {
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            });
        }

        // Keyboard shortcut for print (Ctrl+P)
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });



        // Live search functionality
let searchTimeout;
const searchInput = document.getElementById('slcSearch');
const searchResults = document.getElementById('searchResults');

searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.trim();
    
    if (query.length < 2) {
        searchResults.style.display = 'none';
        return;
    }
    
    searchTimeout = setTimeout(() => {
        performSearch(query);
    }, 300);
});

searchInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const query = this.value.trim();
        if (query.length >= 2) {
            performSearch(query, true);
        }
    }
});

// Close results when clicking outside
document.addEventListener('click', function(e) {
    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.style.display = 'none';
    }
});



function performSearch(query, exact = false) {
    const CSRF = { name: '<?= csrf_token() ?>', hash: '<?= csrf_hash() ?>' };
    
    // Don't search if query is too short
    if (query.length < 2) {
        searchResults.style.display = 'none';
        return;
    }
    
    // Show loading indicator
    searchResults.innerHTML = '<div class="no-results"><i class="fas fa-spinner fa-spin"></i> Searching...</div>';
    searchResults.style.display = 'block';
    
    // Log the search query
    console.log('Searching for:', query);
    
    fetch('<?= base_url('admin/addbulkstudents/search-slc') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            query: query,
            exact: exact ? '1' : '0',
            [CSRF.name]: CSRF.hash
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Search response:', data); // Debug log
        
        if (data.success) {
            displaySearchResults(data.results);
        } else {
            searchResults.innerHTML = '<div class="no-results">Error: ' + (data.msg || 'Search failed') + '</div>';
        }
    })
    .catch(error => {
        console.error('Search error:', error);
        searchResults.innerHTML = '<div class="no-results">Error connecting to server</div>';
    });
}

function displaySearchResults(results) {
    if (!results || results.length === 0) {
        searchResults.innerHTML = '<div class="no-results">No matching records found</div>';
        return;
    }
    
    let html = '';
    results.forEach(item => {
        const isSLC = item.source === 'slc';
        const studentName = item.full_name || `${item.first_name || ''} ${item.last_name || ''}`.trim();
        
        if (isSLC) {
            // Display SLC record
            const formattedDate = item.created_at ? new Date(item.created_at).toLocaleDateString('en-GB') : 'N/A';
            html += `
                <div class="search-result-item" onclick="viewSLC(${item.id})">
                    <div class="student-name">${escapeHtml(studentName)}</div>
                    <div class="slc-info">
                        <span><i class="fas fa-certificate"></i> ${escapeHtml(item.slc_no || 'N/A')}</span>
                        <span><i class="fas fa-calendar"></i> ${formattedDate}</span>
                    </div>
                    <div class="source-badge slc-badge">SLC Generated</div>
                </div>
            `;
        } else {
            // Display student record with generate button
            html += `
                <div class="search-result-item">
                    <div class="student-name">${escapeHtml(studentName)}</div>
                    <div class="student-info">
                        <span><i class="fas fa-id-card"></i> Reg: ${escapeHtml(item.reg_no || 'N/A')}</span>
                        <span><i class="fas fa-graduation-cap"></i> ${escapeHtml(item.class_name || 'N/A')}</span>
                    </div>
                    <div class="student-actions">
                        <button class="generate-slc-btn" onclick="event.stopPropagation(); confirmGenerateSLC(${item.student_id}, '${escapeHtml(studentName)}')">
                            <i class="fas fa-file-pdf"></i> Generate SLC
                        </button>
                    </div>
                    <div class="source-badge student-badge">No SLC</div>
                </div>
            `;
        }
    });
    
    searchResults.innerHTML = html;
}

// Function to confirm SLC generation using browser's confirm
// Function to confirm SLC generation
function confirmGenerateSLC(studentId, studentName) {
    searchResults.style.display = 'none';
    
    Swal.fire({
        title: 'Generate School Leaving Certificate',
        html: `
            <p>Generate SLC for <strong>${escapeHtml(studentName)}</strong>?</p>
            <div style="text-align: left; margin: 15px 0;">
                <label style="display: block; margin-bottom: 5px;"><strong>Leaving Date:</strong></label>
                <input type="date" id="swal-leaving-date" class="swal2-input" value="${new Date().toISOString().split('T')[0]}" style="width: 100%;">
                
                <label style="display: block; margin: 10px 0 5px;"><strong>Reason for Leaving:</strong></label>
                <select id="swal-reason" class="swal2-select" style="width: 100%; padding: 8px;">
                    <option value="On Request">On Request</option>
                    <option value="Transfer">Transfer to Another School</option>
                    <option value="Family Relocation">Family Relocation</option>
                    <option value="Completed Education">Completed Education</option>
                    <option value="Other">Other</option>
                </select>
                
                <label style="display: block; margin: 10px 0 5px;"><strong>Conduct:</strong></label>
                <select id="swal-conduct" class="swal2-select" style="width: 100%; padding: 8px;">
                    <option value="Excellent">Excellent</option>
                    <option value="Good" selected>Good</option>
                    <option value="Satisfactory">Satisfactory</option>
                    <option value="Average">Average</option>
                </select>
                
                <label style="display: block; margin: 10px 0 5px;"><strong>Options:</strong></label>
                <select id="swal-drop" class="swal2-select" style="width: 100%; padding: 8px;">
                    <option value="generate_only">Generate SLC Only (Keep Student Active)</option>
                    <option value="drop_with_slc">Generate SLC and Mark Student as Dropped</option>
                </select>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Generate',
        cancelButtonText: 'Cancel',
        width: '500px',
        preConfirm: () => {
            return {
                leaving_date: document.getElementById('swal-leaving-date').value,
                leaving_reason: document.getElementById('swal-reason').value,
                conduct: document.getElementById('swal-conduct').value,
                drop_option: document.getElementById('swal-drop').value
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            processSimpleSLCGeneration(studentId, result.value);
        }
    });
}

// Simplified generation function using existing logic
function processSimpleSLCGeneration(studentId, options) {
    const CSRF = { name: '<?= csrf_token() ?>', hash: '<?= csrf_hash() ?>' };
    
    // Show loading with SweetAlert
    Swal.fire({
        title: 'Generating SLC',
        text: 'Please wait...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // First, get the student data from edit form
    fetch('<?= base_url('admin/addbulkstudents/get-edit-form') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            student_id: studentId,
            [CSRF.name]: CSRF.hash
        })
    })
    .then(response => response.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Extract student data
        const formData = {
            student_id: studentId,
            full_name: doc.querySelector('#slc_full_name')?.value || '',
            father_name: doc.querySelector('#slc_father_name')?.value || '',
            mother_name: doc.querySelector('#slc_mother_name')?.value || '',
            date_of_birth: doc.querySelector('#slc_dob')?.value || '',
            gender: doc.querySelector('#slc_gender')?.value || '',
            religion: doc.querySelector('#slc_religion')?.value || '',
            nationality: doc.querySelector('#slc_nationality')?.value || 'Pakistani',
            admission_date: doc.querySelector('#slc_admission_date')?.value || '',
            class_admission: doc.querySelector('#slc_class_admission')?.value || '',
            class_name: doc.querySelector('#slc_class')?.value?.split(' - ')[0] || '',
            section_name: doc.querySelector('#slc_class')?.value?.split(' - ')[1] || '',
            reg_no: doc.querySelector('#slc_reg_no')?.value || '',
            profile_photo: doc.querySelector('#slc_photo')?.value || '',
            leaving_date: options.leaving_date,
            leaving_reason: options.leaving_reason,
            conduct: options.conduct
        };
        
        // Split full name
        const nameParts = formData.full_name.split(' ');
        formData.first_name = nameParts[0] || '';
        formData.last_name = nameParts.slice(1).join(' ') || '';
        
        // Generate SLC
        return fetch('<?= base_url('admin/addbulkstudents/generate-slc') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                student_data: JSON.stringify(formData),
                drop_option: options.drop_option,
                [CSRF.name]: CSRF.hash
            })
        });
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Log the full response to see what's coming back
            console.log('SLC Generation Response:', data);
            
            // Check if we have the SLC data properly
            const slcId = data.slc?.id || data.slc_id || null;
            const slcNo = data.slc?.slc_no || data.slc_number || 'N/A';
            const studentName = data.slc?.full_name || data.student_name || 'Student';
            
            if (!slcId) {
                console.error('No SLC ID in response:', data);
                Swal.fire({
                    icon: 'warning',
                    title: 'SLC Generated but ID Missing',
                    html: `
                        <p><strong>SLC Number:</strong> ${slcNo}</p>
                        <p><strong>Student:</strong> ${studentName}</p>
                        <p>The certificate was generated but we couldn't get the ID.</p>
                        <p>Please check the SLC list to view it.</p>
                    `,
                    confirmButtonText: 'OK'
                });
                
                // Refresh search results
                const currentSearch = document.getElementById('slcSearch').value;
                if (currentSearch.length >= 2) {
                    setTimeout(() => performSearch(currentSearch), 500);
                }
                return;
            }
            
            // Success message with SweetAlert
            Swal.fire({
                icon: 'success',
                title: 'SLC Generated Successfully',
                html: `
                    <p><strong>SLC Number:</strong> ${slcNo}</p>
                    <p><strong>Student:</strong> ${studentName}</p>
                    <p><strong>Leaving Date:</strong> ${options.leaving_date}</p>
                `,
                showCancelButton: true,
                confirmButtonText: 'View SLC',
                cancelButtonText: 'Close',
                showDenyButton: true,
                denyButtonText: 'Print SLC'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open('<?= base_url('admin/slc/view/') ?>' + slcId, '_blank');
                } else if (result.isDenied) {
                    const printWindow = window.open('<?= base_url('admin/slc/view/') ?>' + slcId + '?print=1', '_blank');
                    printWindow.onload = function() {
                        printWindow.print();
                    };
                }
            });
            
            // Refresh search results
            const currentSearch = document.getElementById('slcSearch').value;
            if (currentSearch.length >= 2) {
                setTimeout(() => performSearch(currentSearch), 500);
            }
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Generation Failed',
                text: data.msg || 'Could not generate SLC'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while generating SLC'
        });
    });
}


// Function to confirm SLC generation
function confirmGenerateSLC(studentId, studentName) {
    searchResults.style.display = 'none';
    
    Swal.fire({
        title: 'Generate School Leaving Certificate',
        html: `
            <p>Generate SLC for <strong>${escapeHtml(studentName)}</strong>?</p>
            <div style="text-align: left; margin: 15px 0;">
                <label style="display: block; margin-bottom: 5px;"><strong>Leaving Date:</strong></label>
                <input type="date" id="swal-leaving-date" class="swal2-input" value="${new Date().toISOString().split('T')[0]}" style="width: 100%;">
                
                <label style="display: block; margin: 10px 0 5px;"><strong>Reason for Leaving:</strong></label>
                <select id="swal-reason" class="swal2-select" style="width: 100%; padding: 8px;">
                    <option value="On Request">On Request</option>
                    <option value="Transfer">Transfer to Another School</option>
                    <option value="Family Relocation">Family Relocation</option>
                    <option value="Completed Education">Completed Education</option>
                    <option value="Other">Other</option>
                </select>
                
                <label style="display: block; margin: 10px 0 5px;"><strong>Conduct:</strong></label>
                <select id="swal-conduct" class="swal2-select" style="width: 100%; padding: 8px;">
                    <option value="Excellent">Excellent</option>
                    <option value="Good" selected>Good</option>
                    <option value="Satisfactory">Satisfactory</option>
                    <option value="Average">Average</option>
                </select>
                
                <label style="display: block; margin: 10px 0 5px;"><strong>Options:</strong></label>
                <select id="swal-drop" class="swal2-select" style="width: 100%; padding: 8px;">
                    <option value="generate_only">Generate SLC Only (Keep Student Active)</option>
                    <option value="drop_with_slc">Generate SLC and Mark Student as Dropped</option>
                </select>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Generate',
        cancelButtonText: 'Cancel',
        width: '500px',
        preConfirm: () => {
            return {
                leaving_date: document.getElementById('swal-leaving-date').value,
                leaving_reason: document.getElementById('swal-reason').value,
                conduct: document.getElementById('swal-conduct').value,
                drop_option: document.getElementById('swal-drop').value
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            processSimpleSLCGeneration(studentId, result.value);
        }
    });
}

// Simplified generation function using existing logic (KEEP ONLY THIS ONE)
function processSimpleSLCGeneration(studentId, options) {
    const CSRF = { name: '<?= csrf_token() ?>', hash: '<?= csrf_hash() ?>' };
    
    // Show loading with SweetAlert
    Swal.fire({
        title: 'Generating SLC',
        text: 'Please wait...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // First, get the student data from edit form
    fetch('<?= base_url('admin/addbulkstudents/get-edit-form') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            student_id: studentId,
            [CSRF.name]: CSRF.hash
        })
    })
    .then(response => response.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Extract student data
        const formData = {
            student_id: studentId,
            full_name: doc.querySelector('#slc_full_name')?.value || '',
            father_name: doc.querySelector('#slc_father_name')?.value || '',
            mother_name: doc.querySelector('#slc_mother_name')?.value || '',
            date_of_birth: doc.querySelector('#slc_dob')?.value || '',
            gender: doc.querySelector('#slc_gender')?.value || '',
            religion: doc.querySelector('#slc_religion')?.value || '',
            nationality: doc.querySelector('#slc_nationality')?.value || 'Pakistani',
            admission_date: doc.querySelector('#slc_admission_date')?.value || '',
            class_admission: doc.querySelector('#slc_class_admission')?.value || '',
            class_name: doc.querySelector('#slc_class')?.value?.split(' - ')[0] || '',
            section_name: doc.querySelector('#slc_class')?.value?.split(' - ')[1] || '',
            reg_no: doc.querySelector('#slc_reg_no')?.value || '',
            profile_photo: doc.querySelector('#slc_photo')?.value || '',
            leaving_date: options.leaving_date,
            leaving_reason: options.leaving_reason,
            conduct: options.conduct
        };
        
        // Split full name
        const nameParts = formData.full_name.split(' ');
        formData.first_name = nameParts[0] || '';
        formData.last_name = nameParts.slice(1).join(' ') || '';
        
        // Generate SLC
        return fetch('<?= base_url('admin/addbulkstudents/generate-slc') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                student_data: JSON.stringify(formData),
                drop_option: options.drop_option,
                [CSRF.name]: CSRF.hash
            })
        });
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Log the full response to see what's coming back
            console.log('SLC Generation Response:', data);
            
            // Check if we have the SLC data properly
            const slcId = data.slc?.id || data.slc_id || null;
            const slcNo = data.slc?.slc_no || data.slc_number || 'N/A';
            const studentName = data.slc?.full_name || data.student_name || 'Student';
            
            if (!slcId) {
                console.error('No SLC ID in response:', data);
                Swal.fire({
                    icon: 'warning',
                    title: 'SLC Generated but ID Missing',
                    html: `
                        <p><strong>SLC Number:</strong> ${slcNo}</p>
                        <p><strong>Student:</strong> ${studentName}</p>
                        <p>The certificate was generated but we couldn't get the ID.</p>
                        <p>Please check the SLC list to view it.</p>
                    `,
                    confirmButtonText: 'OK'
                });
                
                // Refresh search results
                const currentSearch = document.getElementById('slcSearch').value;
                if (currentSearch.length >= 2) {
                    setTimeout(() => performSearch(currentSearch), 500);
                }
                return;
            }
            
            // Success message with SweetAlert
            Swal.fire({
                icon: 'success',
                title: 'SLC Generated Successfully',
                html: `
                    <p><strong>SLC Number:</strong> ${slcNo}</p>
                    <p><strong>Student:</strong> ${studentName}</p>
                    <p><strong>Leaving Date:</strong> ${options.leaving_date}</p>
                `,
                showCancelButton: true,
                confirmButtonText: 'View SLC',
                cancelButtonText: 'Close',
                showDenyButton: true,
                denyButtonText: 'Print SLC'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open('<?= base_url('admin/slc/view/') ?>' + slcId, '_blank');
                } else if (result.isDenied) {
                    const printWindow = window.open('<?= base_url('admin/slc/view/') ?>' + slcId + '?print=1', '_blank');
                    printWindow.onload = function() {
                        printWindow.print();
                    };
                }
            });
            
            // Refresh search results
            const currentSearch = document.getElementById('slcSearch').value;
            if (currentSearch.length >= 2) {
                setTimeout(() => performSearch(currentSearch), 500);
            }
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Generation Failed',
                text: data.msg || 'Could not generate SLC'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while generating SLC'
        });
    });
}

// Function to view existing SLC
function viewSLC(slcId) {
    window.open('<?= base_url('admin/slc/view/') ?>' + slcId, '_blank');
}

// Function to handle clicking on a student without SLC
function generateSLCForStudent(studentId) {
    Swal.fire({
        title: 'Generate SLC',
        text: 'This student does not have an SLC. Would you like to generate one?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Generate',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Call your existing generate function
            if (typeof generateSlc === 'function') {
                generateSlc(studentId, new Event('click'));
            } else {
                // Redirect to student print page with this student selected
                window.location.href = '<?= base_url('admin/students_print') ?>?student_id=' + studentId;
            }
        }
    });
}


function loadSLC(slcId) {
    // Redirect to the SLC view page
    window.location.href = '<?= base_url('admin/slc/view/') ?>' + slcId;
}

function searchSLC() {
    const query = searchInput.value.trim();
    if (query.length >= 2) {
        performSearch(query, true);
    } else {
        alert('Please enter at least 2 characters to search');
    }
}

// Helper function to escape HTML
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Handle keyboard navigation in results
searchResults.addEventListener('keydown', function(e) {
    const items = this.querySelectorAll('.search-result-item');
    const currentIndex = Array.from(items).findIndex(item => item === document.activeElement);
    
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (currentIndex < items.length - 1) {
            items[currentIndex + 1].focus();
        }
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (currentIndex > 0) {
            items[currentIndex - 1].focus();
        }
    } else if (e.key === 'Enter' && currentIndex >= 0) {
        items[currentIndex].click();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'k') {
        e.preventDefault();
        document.getElementById('slcSearch').focus();
    }
});
    </script>

   <style>
    .student-actions {
        margin: 8px 0 5px;
    }

    .generate-slc-btn {
        background: #28a745;
        color: white;
        border: none;
        padding: 5px 12px;
        border-radius: 3px;
        font-size: 12px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: background 0.2s;
    }

    .generate-slc-btn:hover {
        background: #218838;
    }

    .source-badge {
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 12px;
        display: inline-block;
        margin-top: 5px;
        font-weight: 500;
    }

    .slc-badge {
        background: #28a745;
        color: white;
    }

    .student-badge {
        background: #ffc107;
        color: #333;
    }

    .search-result-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
        transition: background 0.2s;
    }

    .search-result-item:hover {
        background: #f5f5f5;
    }

    .student-name {
        font-weight: bold;
        color: #333;
        margin-bottom: 3px;
    }

    .slc-info, .student-info {
        font-size: 12px;
        color: #666;
        display: flex;
        gap: 10px;
        margin: 3px 0;
    }

    .slc-info span, .student-info span {
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }

    .no-results {
        padding: 15px;
        text-align: center;
        color: #666;
        font-style: italic;
    }

    /* Additional styles for signature section */
    .signature-caption {
        font-size: 12px;
        color: #333;
        margin-top: 1mm;
    }
    
    .signature-image {
        margin: 2px 0;
        padding: 2px;
        background: white;
        border: 1px solid #ddd;
        display: inline-block;
    }
    
    .signature-image img {
        display: block;
        max-width: 100%;
        height: auto;
    }
    
    /* Print optimization */
    @media print {
        .signature-line {
            border-top: 1px solid black !important;
        }
        
        .info-table,
        .academic-table {
            page-break-inside: avoid;
        }
        
        .search-container {
            display: none;
        }
    }
</style>

</body>
</html>