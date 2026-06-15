        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fa fa-bag-shopping me-2 text-primary"></i> 
                    <?php
                    $todayNum = date('N');
                    $dayNames = [
                        1 => 'Tuesday',      // Monday -> pack for Tuesday
                        2 => 'Wednesday',    // Tuesday -> pack for Wednesday
                        3 => 'Thursday',     // Wednesday -> pack for Thursday
                        4 => 'Friday',       // Thursday -> pack for Friday
                        5 => 'Monday',       // Friday -> pack for Monday
                        6 => 'Monday',       // Saturday -> pack for Monday
                        7 => 'Monday'        // Sunday -> pack for Monday
                    ];
                    echo $dayNames[$todayNum] . '\'s Bag Pack';
                    ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($bagPackItems)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Item</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $counter = 1; ?>
                                <?php foreach ($bagPackItems as $item): ?>
                                    <tr>
                                        <td><?= $counter++ ?>.</td>
                                        <td>
                                            <i class="fa <?= esc($item['icon']) ?> me-2 text-primary"></i>
                                            <?= esc($item['item_name']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fa fa-lightbulb me-2"></i>
                        <?php
                        $todayNum = date('N');
                        if ($todayNum == 5 || $todayNum == 6 || $todayNum == 7) {
                            echo 'Get ready for Monday! Pack these items.';
                        } else {
                            echo 'Don\'t forget to pack these items for tomorrow!';
                        }
                        ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3 text-muted">
                        <i class="fa fa-bag-shopping fa-2x mb-2 opacity-50"></i>
                        <p>No items to pack.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
</div>
