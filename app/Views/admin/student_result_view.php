<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th rowspan="2">Term</th>
                <?php foreach ($academicSessions as $session) : ?>
                    <th colspan="2" style="text-align: center;"><?= htmlspecialchars($session->session_name) ?></th>
                <?php endforeach; ?>
            </tr>
            <tr>
                <?php foreach ($academicSessions as $session) : ?>
                    <th>Total Marks</th>
                    <th>Obtained</th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($terms as $term) : ?>
                <tr>
                    <td><?= htmlspecialchars($term->name) ?></td>
                    <?php foreach ($academicSessions as $session) : ?>
                        <?php 
                            $result = $organizedResults[$term->term_id][$session->session_id] ?? null;
                            $total = $result ? $result->exam_total_mark : '-';
                            $obtained = $result ? $result->obtain_total_mark : '-';
                        ?>
                        <td style="text-align: center;"><?= $total ?></td>
                        <td style="text-align: center;"><?= $obtained ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
.table {
    font-size: 14px;
}
.table th {
    background-color: #f8f9fa;
    vertical-align: middle;
}
.table td {
    vertical-align: middle;
}
.text-center {
    text-align: center;
}
.alert {
    margin: 20px;
}
</style>

<?= $this->endSection() ?>