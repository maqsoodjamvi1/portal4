<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            background: white;
            padding: 20px;
        }
        
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            
            .page-break {
                page-break-before: always;
            }
            
            .no-break {
                page-break-inside: avoid;
            }
            
            @page {
                size: A4 portrait;
                margin: 1.5cm;
            }
        }
        
        .print-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }
        
        .print-header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .print-header h2 {
            font-size: 18px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .print-header .report-date {
            font-size: 11px;
            color: #999;
            margin-top: 10px;
        }
        
        .report-info {
            background: #f5f5f5;
            padding: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        
        .report-info p {
            margin: 5px 0;
        }
        
        .term-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .term-title {
            background: #007bff;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            margin: 20px 0 15px 0;
            font-size: 16px;
            page-break-after: avoid;
        }
        
        .class-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .class-title {
            background: #e9ecef;
            padding: 6px 10px;
            border-left: 3px solid #28a745;
            margin: 10px 0 8px 0;
            font-size: 14px;
            font-weight: bold;
        }
        
        .subjects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 12px;
        }
        
        .subject-card {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            page-break-inside: avoid;
        }
        
        .subject-header {
            background: #f8f9fa;
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
            font-weight: bold;
        }
        
        .subject-body {
            padding: 8px;
        }
        
        .objective-label {
            font-size: 10px;
            color: #6c757d;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .objective-text {
            font-size: 11px;
            line-height: 1.4;
        }
        
        /* Class wise print styles */
        .class-card {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        
        .class-card-header {
            background: #667eea;
            color: white;
            padding: 10px;
        }
        
        .class-card-header h3 {
            margin: 0;
            font-size: 14px;
        }
        
        .class-card-body {
            padding: 10px;
        }
        
        .subject-entry {
            border-bottom: 1px solid #f0f0f0;
            padding: 8px 0;
        }
        
        .subject-entry:last-child {
            border-bottom: none;
        }
        
        .subject-name {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 12px;
        }
        
        /* Subject wise print styles */
        .subject-card {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        
        .subject-card-header {
            background: #f093fb;
            color: white;
            padding: 10px;
        }
        
        .subject-card-header h3 {
            margin: 0;
            font-size: 14px;
        }
        
        .subject-card-body {
            padding: 10px;
        }
        
        .class-entry {
            border-bottom: 1px solid #f0f0f0;
            padding: 8px 0;
        }
        
        .class-entry:last-child {
            border-bottom: none;
        }
        
        .class-name {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 12px;
        }
        
        .print-footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #999;
        }
        
        button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }
        
        button:hover {
            background: #0056b3;
        }
        
        @media print {
            button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button onclick="window.print()"><i class="fas fa-print"></i> Print / Save as PDF</button>
    
    <div class="print-header">
        <h1><?= esc($title) ?></h1>
        <h2><?= esc($campus_name) ?></h2>
        <p>Academic Session: <?= esc($session_name) ?></p>
        <div class="report-date">
            Report Generated: <?= $print_date ?>
        </div>
    </div>
    
    <?= $content ?>
    
    <div class="print-footer">
        <p>This is a computer generated report. No signature required.</p>
        <p>Page <?= date('Y-m-d') ?></p>
    </div>
</body>
</html>