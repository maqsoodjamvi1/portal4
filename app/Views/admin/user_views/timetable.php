<?php if (!empty($schedule)): ?>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr class="bg-light">
                    <th style="width: 120px;">Day</th>
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
                    for ($i = 0; $i < 8; $i++):
                        $slot = $daySlots[$i] ?? null;
                    ?>
                    <td class="align-middle">
                        <?php if ($slot): ?>
                            <div class="text-primary font-weight-bold"><?= esc($slot->subject_name) ?></div>
                            <div class="small"><?= esc($slot->class_name) ?> - <?= esc($slot->section_name) ?></div>
                            <div class="small text-muted">
                                <?= date('h:i A', strtotime($slot->start_time)) ?> - 
                                <?= date('h:i A', strtotime($slot->end_time)) ?>
                            </div>
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
<?php else: ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle mr-2"></i> No timetable assigned to this teacher.
    </div>
<?php endif; ?>