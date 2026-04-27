<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print QR Code - <?= $teacher['first_name'] . ' ' . $teacher['last_name'] ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
        }
        .qr-container {
            max-width: 400px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 20px;
            border-radius: 10px;
        }
        .qr-image {
            margin: 20px 0;
        }
        .qr-image img {
            max-width: 100%;
            height: auto;
        }
        .teacher-info {
            margin-top: 20px;
        }
        .teacher-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .teacher-detail {
            color: #666;
            margin-bottom: 5px;
        }
        .qr-code-text {
            font-size: 12px;
            color: #999;
            margin-top: 20px;
            word-break: break-all;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
        .print-btn {
            margin-top: 20px;
            padding: 10px 30px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .print-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="qr-container">
        <div class="qr-image">
            <img src="<?= $qr_image ?>" alt="QR Code">
        </div>
        
        <div class="teacher-info">
            <div class="teacher-name"><?= $teacher['first_name'] . ' ' . $teacher['last_name'] ?></div>
            <div class="teacher-detail">ID: <?= $teacher['id'] ?></div>
            <?php if (!empty($teacher['designation'])): ?>
                <div class="teacher-detail"><?= $teacher['designation'] ?></div>
            <?php endif; ?>
            <?php if (!empty($teacher['email'])): ?>
                <div class="teacher-detail"><?= $teacher['email'] ?></div>
            <?php endif; ?>
        </div>
        
        <div class="qr-code-text">
            QR: <?= $qr_code ?>
        </div>
    </div>
    
    <div class="no-print">
        <button class="print-btn" onclick="window.print()">Print QR Card</button>
        <br><br>
        <a href="<?= base_url('admin/qr/download-all') ?>">Download All QR Codes</a>
    </div>
    
    <script>
        // Auto print? Uncomment below if you want auto print
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>