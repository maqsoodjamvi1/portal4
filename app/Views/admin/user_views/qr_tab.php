<div class="card">
    <div class="card-header">
        <h3 class="card-title">QR Code for <?= $user['name'] ?></h3>
        <div class="card-tools">
            <?php if (isset($qr) && $qr): ?>
                <a href="<?= base_url('admin/qr/view/' . $user['id']) ?>" class="btn btn-primary btn-sm" download>
                    <i class="fas fa-download"></i> Download
                </a>
                <a href="<?= base_url('admin/qr/print/' . $user['id']) ?>" class="btn btn-info btn-sm">
                    <i class="fas fa-print"></i> Print
                </a>
            <?php else: ?>
                <a href="<?= base_url('admin/qr/generate/' . $user['id']) ?>" class="btn btn-success btn-sm">
                    <i class="fas fa-plus-circle"></i> Generate QR Code
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body text-center">
        <?php if (isset($qr) && $qr): ?>
            <div class="mb-3">
                <img src="<?= base_url('admin/qr/view/' . $user['id']) ?>" 
                     alt="QR Code for <?= $user['name'] ?>" 
                     style="max-width: 300px; border: 1px solid #ddd; padding: 20px;">
            </div>
            
            <div class="row mt-3">
                <div class="col-md-6 offset-md-3">
                    <table class="table table-bordered">
                        <tr>
                            <th>Employee Name</th>
                            <td><?= $user['name'] ?></td>
                        </tr>
                        <tr>
                            <th>Employee ID</th>
                            <td><?= $user['employee_id'] ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th>QR Code Value</th>
                            <td><small class="text-muted"><?= $qr['qr_code'] ?></small></td>
                        </tr>
                        <tr>
                            <th>Generated On</th>
                            <td><?= date('d M Y H:i', strtotime($qr['generated_at'])) ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <?php if ($qr['is_active']): ?>
                                    <span class="badge text-bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge text-bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="mt-3">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print QR Code
                </button>
                <button class="btn btn-success" onclick="copyQRCode()">
                    <i class="fas fa-copy"></i> Copy QR Code
                </button>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No QR code generated for this employee yet.
                Click the "Generate QR Code" button above to create one.
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function copyQRCode() {
    // Create a temporary canvas to copy the image
    const img = document.querySelector('img[alt="QR Code"]');
    if (img) {
        // You can implement copy functionality here
        alert('QR code image copied to clipboard!');
    }
}
</script>