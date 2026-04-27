<!DOCTYPE html>
<html dir="<?= session('member_language') == 'ur' ? 'rtl' : 'ltr' ?>" lang="<?= session('member_language') ?? 'en' ?>">
<head>
    <meta charset="UTF-8">
    <title>Student Fee Challan - 3 Copies (A4 Landscape)</title>
    <?php include 'chalan_print_styles.php'; ?>
    <style>
        /* Any view-specific styles can go here */
        <?php if ($show_discount): ?>
        .feetable colgroup col.discount { 
            width: 20%; 
        }
        <?php endif; ?>
        
        /* Ensure proper spacing */
        .slip-row {
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin-bottom: 20px;
            gap: 15px;
        }
        
        .slip-col {
            width: 32%;
            flex: 1;
            page-break-inside: avoid;
        }
        
        /* Style for copy label inside chalan */
        .copy-label-inside {
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            padding: 2px 0;
            border-bottom: 1px dashed #999;
            margin-bottom: 5px;
            color: #333;
        }
        
        /* Payment History Section - Compact for student view */
        .payment-history-section {
            margin-top: 10px;
            border-top: 2px solid #333;
            padding-top: 8px;
            page-break-inside: avoid;
        }
        
        .payment-history-title {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 11px;
        }
        
        .payment-history-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            border: 1px solid #999;
        }
        
        .payment-history-table th {
            background: #e0e0e0;
            font-weight: bold;
            text-align: center;
            padding: 3px;
            border: 1px solid #999;
            white-space: nowrap;
        }
        
        .payment-history-table td {
            text-align: right;
            padding: 3px;
            border: 1px solid #999;
            direction: ltr;
        }
        
        .payment-history-table td:first-child {
            text-align: left;
            font-weight: 500;
            background: #fafafa;
        }
        
        /* Edit button styles - only visible on screen, not in print */
        .edit-chalan-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0,123,255,0.3);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .edit-chalan-btn:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,123,255,0.4);
        }
        
        .edit-chalan-btn i {
            font-size: 18px;
        }
        
        @media print {
            .edit-chalan-btn {
                display: none !important;
            }
        }
        
        /* Edit mode styles */
        .edit-mode-highlight {
            outline: 3px solid #ffc107;
            outline-offset: 5px;
            position: relative;
        }
        
        .edit-mode-highlight::after {
            content: 'EDIT MODE';
            position: absolute;
            top: -25px;
            left: 10px;
            background: #ffc107;
            color: #000;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php if (!empty($students)): ?>
        <?php 
        $studentIds = [];
        foreach ($students as $student) {
            $studentIds[] = $student['student_id'];
        }
        $studentIdsJson = json_encode($studentIds);
        ?>
        
        <!-- Edit Button -->
        <button class="edit-chalan-btn no-print" onclick="openEditMode()">
            <i class="fas fa-edit"></i> Edit Selected Chalans
        </button>
        
        <?php foreach ($students as $index => $student): ?>
            <?php if ($index > 0 && $index % 2 == 0): // 2 students per page in landscape ?>
                <div class="pagebreak"></div>
            <?php endif; ?>
            
            <div class="slip-row" data-student-id="<?= $student['student_id'] ?>">
                <?php foreach (['Bank Copy', 'School Copy', 'Student Copy'] as $copyType): ?>
                    <div class="slip-col">
                        <?php 
                        // Get student payment history if available
                        $studentPaymentHistory = [];
                        if (isset($student['payment_history'])) {
                            $studentPaymentHistory = $student['payment_history'];
                        } elseif (isset($student['student_payment_history'])) {
                            $studentPaymentHistory = $student['student_payment_history'];
                        }
                        
                        // Add copy type to student data
                        $studentWithCopy = $student;
                        $studentWithCopy['copy_label'] = $copyType;
                        
                        $data = [
                            'student' => $studentWithCopy,
                            'show_discount' => $show_discount,
                            'fine_after_due_date' => $fine_after_due_date ?? 0,
                            'footer_line1' => $footer_line1 ?? '',
                            'footer_line2' => $footer_line2 ?? '',
                            'show_line1' => $show_line1 ?? 0,
                            'show_line2' => $show_line2 ?? 0,
                            'fee_month' => $fee_month ?? '',
                            'show_copy_label' => true,
                            'show_payment_history' => $show_payment_history ?? false,
                            'payment_history' => $studentPaymentHistory,
                            'is_family' => false
                        ];
                        echo view('admin/chalanview/partials/chalan_template', $data);
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 50px;">
            <h3>No fee challans found for the selected criteria</h3>
            <p class="no-print">Please try different filters.</p>
        </div>
    <?php endif; ?>
    
    <!-- Edit Modal -->
    <div class="modal fade no-print" id="editChalanModal" tabindex="-1" role="dialog" aria-labelledby="editChalanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editChalanModalLabel">
                        <i class="fas fa-edit mr-2"></i> Edit Fee Chalan
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="edit-loading" class="text-center py-4">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <p>Loading chalan details...</p>
                    </div>
                    <div id="edit-content" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="save-chalan-btn" onclick="saveChalanEdit()">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    
    <script>
        let selectedStudentIds = <?= $studentIdsJson ?? '[]' ?>;
        let currentEditStudentId = null;
        
        function openEditMode() {
            if (selectedStudentIds.length === 0) {
                toastr.warning('No students to edit');
                return;
            }
            
            if (selectedStudentIds.length > 1) {
                // If multiple students, show selection dialog
                showStudentSelection();
            } else {
                // If single student, open edit directly
                loadEditForm(selectedStudentIds[0]);
            }
        }
        
        function showStudentSelection() {
            const selectHtml = `
                <div class="form-group">
                    <label for="student-select">Select Student to Edit:</label>
                    <select class="form-control" id="student-select">
                        ${generateStudentOptions()}
                    </select>
                </div>
            `;
            
            // Show in a simple modal or use SweetAlert
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Select Student',
                    html: selectHtml,
                    showCancelButton: true,
                    confirmButtonText: 'Edit',
                    preConfirm: () => {
                        const studentId = document.getElementById('student-select').value;
                        loadEditForm(studentId);
                    }
                });
            } else {
                // Fallback to custom modal
                const modal = $('#editChalanModal');
                $('#edit-loading').hide();
                $('#edit-content').html(selectHtml).show();
                modal.modal('show');
                $('#save-chalan-btn').hide();
            }
        }
        
        function generateStudentOptions() {
            let options = '';
            <?php foreach ($students as $student): ?>
                options += `<option value="<?= $student['student_id'] ?>"><?= esc($student['student_name']) ?> (<?= esc($student['reg_no'] ?? '') ?>)</option>`;
            <?php endforeach; ?>
            return options;
        }
        
        function loadEditForm(studentId) {
            currentEditStudentId = studentId;
            
            $.ajax({
                url: '<?= base_url('admin/fee-chalan/get-edit-form') ?>',
                type: 'POST',
                data: {
                    student_id: studentId,
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                dataType: 'json',
                beforeSend: function() {
                    $('#editChalanModal').modal('show');
                    $('#edit-loading').show();
                    $('#edit-content').hide();
                    $('#save-chalan-btn').show();
                },
                success: function(response) {
                    $('#edit-loading').hide();
                    if (response.success) {
                        $('#edit-content').html(response.html).show();
                    } else {
                        toastr.error(response.msg || 'Failed to load edit form');
                        $('#editChalanModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    $('#edit-loading').hide();
                    toastr.error('Error loading edit form');
                    console.error(xhr.responseText);
                    $('#editChalanModal').modal('hide');
                }
            });
        }
        
        function saveChalanEdit() {
            const formData = $('#chalan-edit-form').serialize();
            
            $.ajax({
                url: '<?= base_url('admin/fee-chalan/save-edit') ?>',
                type: 'POST',
                data: formData + '&<?= csrf_token() ?>=<?= csrf_hash() ?>',
                dataType: 'json',
                beforeSend: function() {
                    $('#save-chalan-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Saving...');
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.msg || 'Chalan updated successfully');
                        $('#editChalanModal').modal('hide');
                        
                        // Reload the page to show updated data
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        toastr.error(response.msg || 'Failed to update chalan');
                        $('#save-chalan-btn').prop('disabled', false).html('<i class="fas fa-save mr-2"></i> Save Changes');
                    }
                },
                error: function(xhr) {
                    toastr.error('Error saving changes');
                    $('#save-chalan-btn').prop('disabled', false).html('<i class="fas fa-save mr-2"></i> Save Changes');
                    console.error(xhr.responseText);
                }
            });
        }
        
        // Optional: Add double-click to edit
        $('.slip-row').dblclick(function() {
            const studentId = $(this).data('student-id');
            if (studentId) {
                loadEditForm(studentId);
            }
        });
        
        window.onload = function() {
            // Optional: Auto-print if needed
            // window.print();
        }
    </script>
</body>
</html>