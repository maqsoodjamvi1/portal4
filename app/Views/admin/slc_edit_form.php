<?php
// Get data from controller
$student = $student ?? [];
$class = $class ?? [];
$slc = $slc ?? [];
$fullName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
$className = $class['class_name'] ?? '';
$sectionName = $class['section_name'] ?? '';
$hasDues = $has_dues ?? false;
$outstandingBalance = $outstanding_balance ?? 0;
?>

<style>
    .modern-form {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    }
    
    .form-section {
        background: #fff;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
        padding-bottom: 12px;
        border-bottom: 2px solid #f3f4f6;
    }
    
    .section-header i {
        font-size: 20px;
        color: #3b82f6;
        background: #eff6ff;
        padding: 8px;
        border-radius: 10px;
    }
    
    .section-header h4 {
        margin: 0;
        color: #111827;
        font-weight: 600;
        font-size: 16px;
    }
    
    .section-header p {
        margin: 4px 0 0;
        color: #6b7280;
        font-size: 12px;
    }
    
    .two-column-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .full-width {
        grid-column: span 2;
    }
    
    .form-field {
        margin-bottom: 0;
    }
    
    .form-field label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #374151;
        font-size: 13px;
    }
    
    .form-field label i {
        color: #3b82f6;
        margin-right: 6px;
    }
    
    .form-field label .required-star {
        color: #ef4444;
        margin-left: 4px;
    }
    
    .form-field input,
    .form-field select,
    .form-field textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.2s;
        background-color: #fff;
    }
    
    .form-field input:focus,
    .form-field select:focus,
    .form-field textarea:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
    }
    
    .form-field input[readonly] {
        background-color: #f9fafb;
        border-color: #e5e7eb;
        color: #6b7280;
        cursor: not-allowed;
    }
    
    .fee-status-card {
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 20px;
        border-left: 4px solid;
    }
    
    .fee-status-card.pending {
        border-left-color: #dc2626;
        background: #fef2f2;
    }
    
    .fee-status-card.clear {
        border-left-color: #10b981;
        background: #f0fdf4;
    }
    
    .fee-status-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    
    .fee-status-header h5 {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
    }
    
    .fee-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .fee-badge.pending {
        background: #fee2e2;
        color: #dc2626;
    }
    
    .fee-badge.clear {
        background: #d1fae5;
        color: #059669;
    }
    
    .amount-display {
        font-size: 22px;
        font-weight: 700;
        margin: 8px 0;
        color: #dc2626;
    }
    
    .skip-fee-container {
        background: #fffbeb;
        border-radius: 10px;
        padding: 16px;
        margin: 16px 0;
        border: 1px solid #fde68a;
    }
    
    .checkbox-wrapper {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    
    .checkbox-wrapper input[type="checkbox"] {
        width: 18px;
        height: 18px;
        margin-top: 2px;
        cursor: pointer;
    }
    
    .checkbox-wrapper label {
        font-weight: 500;
        color: #111827;
        cursor: pointer;
        flex: 1;
        margin: 0;
    }
    
    .checkbox-wrapper small {
        color: #6b7280;
        display: block;
        margin-top: 4px;
        font-weight: normal;
        font-size: 11px;
    }
    
    .form-actions {
        background: #f9fafb;
        padding: 20px 24px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        position: sticky;
        bottom: 0;
        background: white;
        border-radius: 0 0 16px 16px;
    }
    
    .btn {
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-primary {
        background: #3b82f6;
        color: white;
    }
    
    .btn-primary:hover:not(:disabled) {
        background: #2563eb;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
    }
    
    .btn-primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
    }
    
    .btn-secondary:hover {
        background: #e5e7eb;
    }
    
    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin: 16px 24px;
        font-size: 14px;
        animation: slideDown 0.3s ease;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .alert-danger {
        background: #fee2e2;
        border-left: 4px solid #dc2626;
        color: #991b1b;
    }
    
    .alert-success {
        background: #d1fae5;
        border-left: 4px solid #10b981;
        color: #065f46;
    }
    
    .loading-spinner {
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #3b82f6;
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    @media (max-width: 640px) {
        .two-column-grid {
            grid-template-columns: 1fr;
        }
        .full-width {
            grid-column: span 1;
        }
        .form-section {
            padding: 16px;
        }
        .form-actions {
            padding: 16px;
        }
        .btn {
            padding: 8px 16px;
            font-size: 13px;
        }
    }
</style>

<div class="slc-edit-container">
    <form id="slcUpdateForm" class="modern-form">
        <input type="hidden" name="student_id" id="student_id" value="<?= htmlspecialchars($student['student_id'] ?? '') ?>">
        
        <div id="alertContainer"></div>
        
        <!-- Student Basic Information -->
        <div class="form-section">
            <div class="section-header">
                <i class="fas fa-user-graduate"></i>
                <div>
                    <h4>Student Information</h4>
                    <p>Basic personal details</p>
                </div>
            </div>
            
            <div class="two-column-grid">
                <div class="form-field">
                    <label><i class="fas fa-id-card"></i> Registration No.</label>
                    <input type="text" value="<?= htmlspecialchars($student['reg_no'] ?? '') ?>" readonly>
                </div>
                
                <div class="form-field">
                    <label><i class="fas fa-calendar-alt"></i> Date of Birth <span class="required-star">*</span></label>
                    <input type="date" name="dob" id="dob" value="<?= htmlspecialchars($student['date_of_birth'] ?? '') ?>" required>
                </div>
                
                <div class="form-field">
                    <label><i class="fas fa-calendar-plus"></i> Admission Date</label>
                    <input type="date" name="admission_date" id="admission_date" value="<?= htmlspecialchars($student['date_of_admission'] ?? '') ?>">
                </div>
                
                <div class="form-field">
                    <label><i class="fas fa-chalkboard"></i> Class at Leaving</label>
                    <input type="text" value="<?= htmlspecialchars($className . ($sectionName ? ' - ' . $sectionName : '')) ?>" readonly>
                </div>
                
                <div class="form-field">
                    <label><i class="fas fa-user"></i> First Name <span class="required-star">*</span></label>
                    <input type="text" name="first_name" id="first_name" value="<?= htmlspecialchars($student['first_name'] ?? '') ?>" required>
                </div>
                
                <div class="form-field">
                    <label><i class="fas fa-user"></i> Last Name</label>
                    <input type="text" name="last_name" id="last_name" value="<?= htmlspecialchars($student['last_name'] ?? '') ?>">
                </div>
            </div>
        </div>
        
        <!-- Parent Information -->
        <div class="form-section">
            <div class="section-header">
                <i class="fas fa-users"></i>
                <div>
                    <h4>Parent Information</h4>
                    <p>Guardian details</p>
                </div>
            </div>
            
            <div class="two-column-grid">
                <div class="form-field">
                    <label><i class="fas fa-user-tie"></i> Father's Name <span class="required-star">*</span></label>
                    <input type="text" name="father_name" id="father_name" value="<?= htmlspecialchars($student['f_name'] ?? '') ?>" required>
                </div>
                
                <div class="form-field">
                    <label><i class="fas fa-user-friends"></i> Mother's Name</label>
                    <input type="text" name="mother_name" id="mother_name" value="<?= htmlspecialchars($student['m_name'] ?? '') ?>">
                </div>
                
                <div class="form-field">
                    <label><i class="fas fa-pray"></i> Religion</label>
                    <input type="text" name="religion" id="religion" value="<?= htmlspecialchars($student['religion'] ?? '') ?>">
                </div>
                
                <div class="form-field">
                    <label><i class="fas fa-flag"></i> Nationality</label>
                    <input type="text" name="nationality" id="nationality" value="<?= htmlspecialchars($student['nationality'] ?? 'Pakistani') ?>">
                </div>
            </div>
        </div>
        
        <!-- Fee Status Section -->
        <div class="form-section">
            <div class="section-header">
                <i class="fas fa-money-bill-wave"></i>
                <div>
                    <h4>Fee Clearance Status</h4>
                    <p>Financial clearance verification</p>
                </div>
            </div>
            
            <?php if ($hasDues && $outstandingBalance > 0): ?>
                <div class="fee-status-card pending">
                    <div class="fee-status-header">
                        <h5><i class="fas fa-exclamation-triangle"></i> Outstanding Balance</h5>
                        <span class="fee-badge pending">Pending Clearance</span>
                    </div>
                    <div class="amount-display">₨ <?= number_format($outstandingBalance, 2) ?></div>
                    <p style="color: #6b7280; font-size: 13px; margin: 0;">This amount needs clearance before certificate issuance.</p>
                </div>
                
                <div class="skip-fee-container">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" name="skip_pending_fee" id="skip_pending_fee" value="1" <?= ($slc['skip_fee'] ?? 0) == 1 ? 'checked' : '' ?>>
                        <label for="skip_pending_fee">
                            <strong>Skip Fee Verification</strong>
                            <small>Allow certificate issuance despite pending fees</small>
                        </label>
                    </div>
                </div>
            <?php else: ?>
                <div class="fee-status-card clear">
                    <div class="fee-status-header">
                        <h5><i class="fas fa-check-circle"></i> All Fees Cleared</h5>
                        <span class="fee-badge clear">Cleared</span>
                    </div>
                    <p style="color: #059669; margin: 8px 0 0 0;">No outstanding fees.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- SLC Information -->
        <div class="form-section">
            <div class="section-header">
                <i class="fas fa-graduation-cap"></i>
                <div>
                    <h4>School Leaving Certificate Details</h4>
                    <p>Certificate specific information</p>
                </div>
            </div>
            
            <div class="two-column-grid">
                <div class="form-field">
                    <label><i class="fas fa-calendar-check"></i> Leaving Date</label>
                    <input type="date" name="leaving_date" id="leaving_date" value="<?= htmlspecialchars($slc['leaving_date'] ?? '') ?>">
                </div>
                
                <div class="form-field">
                    <label><i class="fas fa-star"></i> Conduct</label>
                    <select name="conduct" id="conduct">
                        <option value="Excellent" <?= ($slc['conduct'] ?? '') == 'Excellent' ? 'selected' : '' ?>>Excellent</option>
                        <option value="Very Good" <?= ($slc['conduct'] ?? '') == 'Very Good' ? 'selected' : '' ?>>Very Good</option>
                        <option value="Good" <?= ($slc['conduct'] ?? '') == 'Good' ? 'selected' : '' ?>>Good</option>
                        <option value="Satisfactory" <?= ($slc['conduct'] ?? '') == 'Satisfactory' ? 'selected' : '' ?>>Satisfactory</option>
                        <option value="Fair" <?= ($slc['conduct'] ?? '') == 'Fair' ? 'selected' : '' ?>>Fair</option>
                    </select>
                </div>
                
                <div class="form-field full-width">
                    <label><i class="fas fa-question-circle"></i> Reason for Leaving</label>
                    <select name="leaving_reason" id="leaving_reason">
                        <option value="">-- Select Reason --</option>
                        <option value="Family relocation" <?= ($slc['leaving_reason'] ?? '') == 'Family relocation' ? 'selected' : '' ?>>Family Relocation</option>
                        <option value="School transfer" <?= ($slc['leaving_reason'] ?? '') == 'School transfer' ? 'selected' : '' ?>>School Transfer</option>
                        <option value="Financial constraints" <?= ($slc['leaving_reason'] ?? '') == 'Financial constraints' ? 'selected' : '' ?>>Financial Constraints</option>
                        <option value="Health issues" <?= ($slc['leaving_reason'] ?? '') == 'Health issues' ? 'selected' : '' ?>>Health Issues</option>
                        <option value="Completed education" <?= ($slc['leaving_reason'] ?? '') == 'Completed education' ? 'selected' : '' ?>>Completed Education</option>
                        <option value="Other" <?= ($slc['leaving_reason'] ?? '') == 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Form Actions -->
        <div class="form-actions">
            <button type="button" class="btn btn-secondary" id="cancelBtn">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button type="button" class="btn btn-primary" id="updateBtn">
                <i class="fas fa-save"></i> Update Information
            </button>
        </div>
    </form>
</div>

<script>
// Simple direct event binding - no complex initialization
(function() {
    console.log('SLC Edit Form loaded, setting up buttons...');
    
    // Function to show alert
    window.showEditAlert = function(message, type) {
        const alertContainer = document.getElementById('alertContainer');
        if (alertContainer) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = `<i class="fas fa-${type === 'danger' ? 'exclamation-circle' : 'check-circle'}"></i> ${message}`;
            alertContainer.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 5000);
        } else {
            alert(message);
        }
    };
    
   
    
    // Function to collect form data
   
    // Function to cancel
    window.performCancel = function() {
        console.log('Cancel function called');
        const modal = document.getElementById('editModal');
        if (modal) modal.style.display = 'none';
    };
    
    // Get buttons and attach events directly
    const updateBtn = document.getElementById('updateBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    
    if (updateBtn) {
        console.log('Update button found, attaching click handler');
        updateBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Update button clicked');
            window.performUpdate();
        };
    } else {
        console.error('Update button not found');
    }
    
    if (cancelBtn) {
        console.log('Cancel button found, attaching click handler');
        cancelBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Cancel button clicked');
            window.performCancel();
        };
    } else {
        console.error('Cancel button not found');
    }
    
    console.log('Buttons setup complete');
})();
</script>