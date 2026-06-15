<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .student-photo { width: 150px; height: 180px; object-fit: cover; border: 1px solid #ddd; float: right; }
        .section { margin-bottom: 20px; page-break-inside: avoid; }
        .section-title { background-color: #f5f5f5; padding: 5px 10px; font-weight: bold; border-start: 4px solid #333; }
        .row { display: flex; flex-wrap: wrap; margin-bottom: 10px; }
        .col { flex: 1; min-width: 200px; padding: 0 5px; }
        .label { font-weight: bold; color: #555; }
        .attachment-container { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px; }
        .attachment-item { width: 45%; border: 1px solid #eee; padding: 10px; }
        .attachment-img { max-width: 100%; max-height: 200px; }
        .signature-area { margin-top: 50px; display: flex; justify-content: space-between; }
        .footer { margin-top: 30px; font-size: 0.8em; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h2>STUDENT ADMISSION FORM</h2>
        <p>Date: <?= date('d/m/Y') ?></p>
    </div>

    <?php if(!empty($student->profile_photo)): ?>
        <img src="<?= base_url('uploads/students/'.$student->profile_photo) ?>" class="student-photo">
    <?php endif; ?>

    <div class="section">
        <div class="section-title">STUDENT INFORMATION</div>
        <div class="row">
            <div class="col">
                <span class="label">Full Name:</span> <?= esc($student->first_name.' '.$student->last_name) ?>
            </div>
            <div class="col">
                <span class="label">Registration No:</span> <?= esc($student->reg_no) ?>
            </div>
        </div>
        <!-- Add all other student fields similarly -->
    </div>

    <div class="section">
        <div class="section-title">PARENT INFORMATION</div>
        <div class="row">
            <div class="col">
                <span class="label">Father Name:</span> <?= esc($student->f_name) ?>
            </div>
            <!-- Add all parent fields -->
        </div>
    </div>

    <?php if(!empty($attachments)): ?>
    <div class="section">
        <div class="section-title">ATTACHED DOCUMENTS</div>
        <div class="attachment-container">
            <?php foreach($attachments as $attachment): ?>
            <div class="attachment-item">
                <p><strong><?= esc($attachment->a_type_name) ?></strong></p>
                <img src="<?= base_url('studentattachements/'.$attachment->attachement_path) ?>" class="attachment-img">
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="signature-area">
        <div>
            <p>_________________________</p>
            <p>Parent Signature</p>
        </div>
        <div>
            <p>_________________________</p>
            <p>Admission Officer</p>
        </div>
    </div>

    <div class="footer">
        <p>Generated on <?= date('d F Y H:i') ?> | <?= env('app.name') ?></p>
    </div>
</body>
</html>