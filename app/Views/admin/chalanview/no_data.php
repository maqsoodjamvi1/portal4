<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>No Data Found</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .no-data-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 40px;
            text-align: center;
            max-width: 500px;
        }
        .no-data-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        .no-data-icon svg {
            width: 80px;
            height: 80px;
        }
        .no-data-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .no-data-message {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .no-data-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #007bff;
            color: white;
            border: 1px solid #007bff;
        }
        .btn-primary:hover {
            background: #0056b3;
            border-color: #0056b3;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
            border: 1px solid #6c757d;
        }
        .btn-secondary:hover {
            background: #545b62;
            border-color: #545b62;
        }
        .filter-summary {
            background: #f8f9fa;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-top: 20px;
            text-align: left;
            border-radius: 4px;
        }
        .filter-summary h4 {
            margin-top: 0;
            color: #856404;
            font-size: 16px;
        }
        .filter-summary ul {
            margin-bottom: 0;
            padding-left: 20px;
            color: #666;
        }
        .filter-summary li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="no-data-container">
        <div class="no-data-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <div class="no-data-title">No Fee Challans Found</div>
        <div class="no-data-message">
            No unpaid fee challans match your selected criteria. 
            Please try adjusting your filters or check back later.
        </div>
        
        <?php if (!empty($params)): ?>
        <div class="filter-summary">
            <h4>Applied Filters:</h4>
            <ul>
                <?php if (!empty($params['fee_month'])): ?>
                    <li><strong>Fee Month:</strong> <?= esc($params['fee_month']) ?></li>
                <?php endif; ?>
                <?php if (!empty($params['class_id'])): ?>
                    <li><strong>Class ID:</strong> <?= esc($params['class_id']) ?></li>
                <?php endif; ?>
                <?php if (!empty($params['section_id'])): ?>
                    <li><strong>Section ID:</strong> <?= esc($params['section_id']) ?></li>
                <?php endif; ?>
                <?php if (!empty($params['search'])): ?>
                    <li><strong>Search:</strong> <?= esc($params['search']) ?></li>
                <?php endif; ?>
                <?php if (!empty($params['family_id'])): ?>
                    <li><strong>Family ID:</strong> <?= esc($params['family_id']) ?></li>
                <?php endif; ?>
                <?php if (empty($params['fee_month']) && empty($params['class_id']) && empty($params['section_id']) && empty($params['search']) && empty($params['family_id'])): ?>
                    <li>No filters applied (showing all students)</li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="no-data-actions">
            <a href="<?= base_url('admin/fee-chalan/generate') ?>" class="btn btn-primary">
                ? Back to Filters
            </a>
            <a href="<?= base_url('admin/fee-chalan') ?>" class="btn btn-secondary">
                Go to Fee Chalan List
            </a>
        </div>
    </div>
</body>
</html>