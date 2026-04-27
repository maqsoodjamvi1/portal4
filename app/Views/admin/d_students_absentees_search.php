<?php
$campus_id = $_GET['campus_id'] ?? 0;
$session_id = $_GET['session_id'] ?? 0;
$date_value = $_GET['date'] ?? date('Y-m-d');
?>

<style>
.search-container {
    max-width: 600px;
    margin: 0 auto 30px;
}

.search-input-group {
    display: flex;
    gap: 10px;
}

.search-input-group input {
    flex: 1;
    padding: 12px;
    font-size: 16px;
    border: 2px solid #ddd;
    border-radius: 8px;
}

.search-input-group button {
    padding: 12px 24px;
}

.family-card {
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

.family-header {
    background: #f8f9fa;
    padding: 12px 15px;
    border-bottom: 1px solid #ddd;
}

.sibling-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
}

.sibling-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.status-buttons .btn {
    margin: 0 2px;
    padding: 5px 12px;
}

.status-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    margin-left: 10px;
}

.badge-success { background: #28a745; color: white; }
.badge-danger { background: #dc3545; color: white; }
.badge-warning { background: #ffc107; color: #856404; }
.badge-info { background: #17a2b8; color: white; }

.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .sibling-row {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
    .sibling-info {
        flex-direction: column;
    }
    .search-input-group {
        flex-direction: column;
    }
}
</style>

<input type="hidden" id="search_campus_id" value="<?= $campus_id ?>">
<input type="hidden" id="search_session_id" value="<?= $session_id ?>">

<div class="search-container">
    <div class="card bg-light">
        <div class="card-body">
            <div class="form-group">
                <label><i class="fas fa-calendar-alt"></i> Attendance Date</label>
                <input type="date" id="search_date" class="form-control" value="<?= $date_value ?>">
            </div>
            <div class="search-input-group">
                <input type="text" 
                       class="form-control form-control-lg" 
                       id="search_name" 
                       placeholder="Search by student name (minimum 3 characters)..."
                       autocomplete="off">
                <button class="btn btn-primary btn-lg" type="button" id="btnSearch">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
            <small class="text-muted mt-2 d-block">
                <i class="fas fa-info-circle"></i> 
                Shows all siblings of matching students. Type at least 3 characters.
            </small>
        </div>
    </div>
</div>

<div id="search_results_container">
    <div class="text-center text-muted py-5">
        <i class="fas fa-search fa-3x mb-3"></i>
        <p>Enter a student name (minimum 3 characters) to search and mark attendance.</p>
        <p class="small">All siblings of the matching student will be displayed together.</p>
    </div>
</div>

<script>
let searchTimeout;

$('#search_name').on('keyup', function() {
    clearTimeout(searchTimeout);
    let keyword = $(this).val();
    if (keyword.length >= 3) {
        searchTimeout = setTimeout(function() {
            performSearch();
        }, 500);
    } else if (keyword.length > 0 && keyword.length < 3) {
        $('#search_results_container').html('<div class="alert alert-warning">Please enter at least 3 characters to search.</div>');
    } else if (keyword.length === 0) {
        $('#search_results_container').html(`<div class="text-center text-muted py-5"><i class="fas fa-search fa-3x mb-3"></i><p>Enter a student name (minimum 3 characters) to search and mark attendance.</p></div>`);
    }
});

$('#btnSearch').on('click', function() { performSearch(); });
$('#search_date').on('change', function() { if ($('#search_name').val().length >= 3) performSearch(); });

function performSearch() {
    let keyword = $('#search_name').val().trim();
    let date = $('#search_date').val();
    let campus_id = $('#search_campus_id').val();
    let session_id = $('#search_session_id').val();
    
    if (keyword.length < 3) {
        toastr.warning('Please enter at least 3 characters to search.');
        return;
    }
    
    $('#search_results_container').html(`<div class="text-center py-5"><div class="loading-spinner mx-auto mb-3"></div><p>Searching for "${escapeHtml(keyword)}"...</p></div>`);
    
    $.ajax({
        url: '/admin/students_absentees/search_students_by_name',
        type: "POST",
        dataType: 'json',
        data: { keyword: keyword, date: date, campus_id: campus_id, session_id: session_id },
        success: function(response) {
            if (response.success && response.data && response.data.length > 0) {
                renderSearchResults(response.data, date);
            } else {
                $('#search_results_container').html(`<div class="alert alert-info">No active students found matching "${escapeHtml(keyword)}".</div>`);
            }
        },
        error: function() {
            $('#search_results_container').html('<div class="alert alert-danger">Error searching for students. Please try again.</div>');
        }
    });
}

function renderSearchResults(families, date) {
    let totalStudents = families.reduce((sum, f) => sum + f.siblings.length, 0);
    let html = `<div class="mb-3"><div class="alert alert-success"><i class="fas fa-users"></i> Found ${families.length} family/families with ${totalStudents} student(s)</div></div>`;
    
    families.forEach((family) => {
        html += `<div class="family-card">
            <div class="family-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div><i class="fas fa-family text-primary"></i> <strong>${escapeHtml(family.family_name)}</strong> <span class="badge badge-secondary ml-2">${family.sibling_count} children</span></div>
                    <div>
                        <button class="btn btn-sm btn-outline-success bulk-family-mark" data-parent-id="${family.parent_id}" data-status="P">All Present</button>
                        <button class="btn btn-sm btn-outline-danger bulk-family-mark" data-parent-id="${family.parent_id}" data-status="A">All Absent</button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0"><div class="list-group list-group-flush">`;
        
        family.siblings.forEach((student) => {
            html += `<div class="sibling-row" data-student-id="${student.student_id}">
                <div class="sibling-info">
                    <div class="sibling-photo"><i class="fas fa-user"></i></div>
                    <div><div class="font-weight-bold">${escapeHtml(student.name)}</div><div class="small text-muted">Reg: ${escapeHtml(student.reg_no || 'N/A')} | Class: ${escapeHtml(student.class_name)}</div></div>
                </div>
                <div class="sibling-status">
                    <div class="status-buttons btn-group btn-group-toggle" data-toggle="buttons">
                        <label class="btn btn-sm btn-outline-success ${student.status === 'P' ? 'active' : ''}" data-status="P"><input type="radio" name="status_${student.student_id}" value="P" ${student.status === 'P' ? 'checked' : ''}> P</label>
                        <label class="btn btn-sm btn-outline-danger ${student.status === 'A' ? 'active' : ''}" data-status="A"><input type="radio" name="status_${student.student_id}" value="A" ${student.status === 'A' ? 'checked' : ''}> A</label>
                        <label class="btn btn-sm btn-outline-warning ${student.status === 'L' ? 'active' : ''}" data-status="L"><input type="radio" name="status_${student.student_id}" value="L" ${student.status === 'L' ? 'checked' : ''}> L</label>
                        <label class="btn btn-sm btn-outline-info ${student.status === 'LC' ? 'active' : ''}" data-status="LC"><input type="radio" name="status_${student.student_id}" value="LC" ${student.status === 'LC' ? 'checked' : ''}> LC</label>
                    </div>
                    <span class="status-badge badge-${student.status_class}">${student.status_label}</span>
                </div>
            </div>`;
        });
        html += `</div></div></div>`;
    });
    
    $('#search_results_container').html(html);
    attachSearchEventHandlers(date);
}

function attachSearchEventHandlers(date) {
    $('.sibling-row .btn-group label').off('click').on('click', function(e) {
        e.preventDefault();
        let $label = $(this);
        let $row = $label.closest('.sibling-row');
        let studentId = $row.data('student-id');
        let newStatus = $label.data('status');
        
        $label.addClass('active').siblings().removeClass('active');
        $label.find('input').prop('checked', true);
        
        let statusText = getStatusText(newStatus);
        let statusClass = getStatusClass(newStatus);
        $row.find('.status-badge').removeClass('badge-success badge-danger badge-warning badge-info').addClass(`badge-${statusClass}`).text(statusText);
        
        $.ajax({
            url: '/admin/students_absentees/update_attendance_status_single',
            type: "POST",
            data: { student_id: studentId, attendanceDate: date, status: newStatus },
            success: function(response) {
                if (response.success) toastr.success(`Attendance updated to ${response.status_label}`);
                else toastr.error(response.message || 'Error updating attendance');
            },
            error: function() { toastr.error('Server error. Please try again.'); }
        });
    });
    
    $('.bulk-family-mark').off('click').on('click', function() {
        let newStatus = $(this).data('status');
        let $rows = $(this).closest('.family-card').find('.sibling-row');
        $rows.each(function() {
            let studentId = $(this).data('student-id');
            let $targetLabel = $(this).find(`.btn-group label[data-status="${newStatus}"]`);
            $targetLabel.addClass('active').siblings().removeClass('active');
            $targetLabel.find('input').prop('checked', true);
            $(this).find('.status-badge').removeClass('badge-success badge-danger badge-warning badge-info').addClass(`badge-${getStatusClass(newStatus)}`).text(getStatusText(newStatus));
            $.ajax({ url: '/admin/students_absentees/update_attendance_status_single', type: "POST", data: { student_id: studentId, attendanceDate: date, status: newStatus } });
        });
        toastr.success(`All students marked as ${getStatusText(newStatus)}`);
    });
}

function getStatusText(status) {
    const map = { 'P': 'Present', 'A': 'Absent', 'L': 'Leave', 'LC': 'Late Coming' };
    return map[status] || 'Absent';
}

function getStatusClass(status) {
    const map = { 'P': 'success', 'A': 'danger', 'L': 'warning', 'LC': 'info' };
    return map[status] || 'danger';
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}
</script>