<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Teacher Time Table</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/users') ?>">Employees</a></li>
                    <li class="breadcrumb-item active">Time Table</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            Time Table for: <?= esc($user->first_name . ' ' . $user->last_name) ?>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr class="bg-light">
                                        <th style="width: 150px;">Day</th>
                                        <th>Period 1</th>
                                        <th>Period 2</th>
                                        <th>Period 3</th>
                                        <th>Period 4</th>
                                        <th>Period 5</th>
                                        <th>Period 6</th>
                                        <th>Period 7</th>
                                        <th>Period 8</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($days as $day): ?>
                                    <tr>
                                        <td class="font-weight-bold bg-light"><?= $day ?></td>
                                        <?php 
                                        $daySlots = $schedule[$day] ?? [];
                                        $slotCount = count($daySlots);
                                        for ($i = 0; $i < 8; $i++):
                                            $slot = $daySlots[$i] ?? null;
                                        ?>
                                        <td class="align-middle">
                                            <?php if ($slot): ?>
                                                <div class="text-primary font-weight-bold"><?= esc($slot->subject_name) ?></div>
                                                <div class="small"><?= esc($slot->class_name) ?> - <?= esc($slot->section_name) ?></div>
                                                <div class="small text-muted"><?= date('h:i A', strtotime($slot->start_time)) ?> - <?= date('h:i A', strtotime($slot->end_time)) ?></div>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php endfor; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>