<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
    table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 20px;
    }
    th, td {
        border: 1px solid #000;
        padding: 8px;
        font-size: 12px;
    }
    th {
        background-color: #f2f2f2;
    }
    .rtl {
        direction: rtl;
    }
    .page-break {
        page-break-before: always;
    }
</style>

<?php foreach ($data as $value): ?>
    <div class="page-break"></div>
    <div style="border: 2px solid #000; padding: 10px;">
        <h3 style="text-align: center; margin-bottom: 5px;">Scheme Of Studies (<?= esc($value['term_name']) ?> - <?= esc($value['session_name']) ?>)</h3>
        <h4 style="text-align: center; margin-top: 0;">Class: <?= esc($value['class']) ?></h4>
        <table>
            <thead>
                <tr>
                    <th>Subject</th>
                    <?php foreach ($value['week_name'] as $term): ?>
                        <th><?= esc($term['week_name']) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($value['result'] as $subject => $entries): ?>
                    <tr>
                        <td><?= esc($subject) ?></td>
                        <?php
                            $weekCount = count($value['week_name']);
                            $filledCount = count($entries);
                            $emptyCols = $weekCount - $filledCount;
                        ?>
                        <?php foreach ($entries as $content): ?>
                            <td class="<?= in_array($subject, ['Urdu', 'Islamiat']) ? 'rtl' : '' ?>"><?= esc($content) ?></td>
                        <?php endforeach; ?>
                        <?php for ($i = 0; $i < $emptyCols; $i++): ?>
                            <td>0</td>
                        <?php endfor; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endforeach; ?>

<?= $this->endSection() ?>