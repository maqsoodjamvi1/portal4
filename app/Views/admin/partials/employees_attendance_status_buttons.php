<?php
$empId = (int) ($emp_id ?? 0);
$status = (string) ($status ?? '');
$options = [
    'P'  => ['label' => 'Present', 'class' => 'btn-outline-success'],
    'A'  => ['label' => 'Absent', 'class' => 'btn-outline-danger'],
    'LC' => ['label' => 'Late', 'class' => 'btn-outline-warning'],
    'EL' => ['label' => 'Early', 'class' => 'btn-outline-info'],
    'L'  => ['label' => 'Leave', 'class' => 'btn-outline-secondary'],
];
?>
<div class="btn-group btn-group-toggle emp-att-status-group flex-wrap" data-emp-id="<?= $empId ?>" role="group">
    <?php foreach ($options as $code => $opt): ?>
        <label class="btn btn-sm <?= $opt['class'] ?> <?= $status === $code ? 'active' : '' ?>">
            <input type="radio" name="<?= $empId ?>_status" value="<?= $code ?>"
                   class="emp-att-status-radio" autocomplete="off"
                <?= $status === $code ? 'checked' : '' ?>>
            <?= $opt['label'] ?>
        </label>
    <?php endforeach; ?>
</div>
