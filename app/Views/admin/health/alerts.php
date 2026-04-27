<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-bell mr-2"></i>Health Alerts</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/health/bmi-dashboard') ?>">BMI</a></li>
                    <li class="breadcrumb-item active">Health Alerts</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Health Alerts</h3>
            <div class="card-tools">
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-primary" id="showUnread">Unread</button>
                    <button class="btn btn-sm btn-outline-secondary" id="showAll">All</button>
                </div>
                <button class="btn btn-sm btn-success ml-2" id="markAllRead">
                    <i class="fas fa-check-double"></i> Mark All Read
                </button>
            </div>
        </div>
        <div class="card-body">
            <div id="alertsList">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Loading alerts...</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    let currentStatus = 'unread';
    
    function loadAlerts() {
        $('#alertsList').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div><p>Loading alerts...</p></div>');
        
        $.ajax({
            url: '<?= base_url("admin/health/alerts/data") ?>',
            type: 'POST',
            data: {
                status: currentStatus,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(alerts) {
                if (alerts.length === 0) {
                    $('#alertsList').html('<div class="text-center text-muted py-4"><i class="fas fa-check-circle fa-3x mb-2 text-success"></i><p>No alerts found</p></div>');
                    return;
                }
                
                let html = '';
                alerts.forEach(function(alert) {
                    let alertClass = alert.alert_type;
                    let icon = alert.alert_type == 'underweight' ? 'fa-chart-line' : (alert.alert_type == 'overweight' ? 'fa-chart-simple' : 'fa-exclamation-triangle');
                    
                    html += `
                        <div class="alert-item alert-${alertClass}" data-id="${alert.alert_id}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <i class="fas ${icon} mr-2"></i>
                                    <strong>${alert.first_name} ${alert.last_name}</strong>
                                    <span class="badge badge-${alertClass == 'underweight' ? 'info' : (alertClass == 'overweight' ? 'warning' : 'danger')} ml-2">
                                        ${alert.alert_type.toUpperCase()}
                                    </span>
                                    <div class="small text-muted mt-1">
                                        BMI: ${alert.bmi_value} | 
                                        Class: ${alert.class_name || ''} ${alert.section_name || ''} |
                                        Date: ${new Date(alert.created_date).toLocaleString()}
                                    </div>
                                </div>
                                <div>
                                    ${alert.is_read == 0 ? '<button class="btn btn-sm btn-success mark-read" data-id="' + alert.alert_id + '"><i class="fas fa-check"></i> Mark Read</button>' : '<span class="badge badge-secondary">Read</span>'}
                                </div>
                            </div>
                            <div class="mt-2">${alert.message}</div>
                        </div>
                    `;
                });
                $('#alertsList').html(html);
            },
            error: function() {
                $('#alertsList').html('<div class="text-center text-danger py-4">Error loading alerts</div>');
            }
        });
    }
    
    $('#showUnread').click(function() {
        currentStatus = 'unread';
        $(this).addClass('btn-outline-primary').removeClass('btn-outline-secondary');
        $('#showAll').addClass('btn-outline-secondary').removeClass('btn-outline-primary');
        loadAlerts();
    });
    
    $('#showAll').click(function() {
        currentStatus = 'all';
        $(this).addClass('btn-outline-primary').removeClass('btn-outline-secondary');
        $('#showUnread').addClass('btn-outline-secondary').removeClass('btn-outline-primary');
        loadAlerts();
    });
    
    $(document).on('click', '.mark-read', function() {
        const alertId = $(this).data('id');
        const $this = $(this);
        
        $.ajax({
            url: '<?= base_url("admin/health/alerts/mark-read") ?>/' + alertId,
            type: 'POST',
            data: { <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $this.closest('.alert-item').fadeOut(300, function() {
                        $(this).remove();
                        if ($('#alertsList .alert-item').length === 0) {
                            $('#alertsList').html('<div class="text-center text-muted py-4"><i class="fas fa-check-circle fa-3x mb-2 text-success"></i><p>No alerts found</p></div>');
                        }
                    });
                    toastr.success('Alert marked as read');
                }
            }
        });
    });
    
    $('#markAllRead').click(function() {
        if (confirm('Mark all alerts as read?')) {
            $.ajax({
                url: '<?= base_url("admin/health/alerts/mark-all-read") ?>',
                type: 'POST',
                data: { <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        loadAlerts();
                        toastr.success('All alerts marked as read');
                    }
                }
            });
        }
    });
    
    loadAlerts();
});
</script>

<style>
.alert-item {
    padding: 15px;
    border-left: 4px solid;
    margin-bottom: 15px;
    background: #f8f9fa;
    border-radius: 5px;
    transition: all 0.2s;
}
.alert-item:hover {
    background: #e9ecef;
}
.alert-underweight { border-left-color: #3498db; }
.alert-overweight { border-left-color: #f39c12; }
.alert-obese { border-left-color: #e74c3c; }
</style>

<?= $this->endSection() ?>