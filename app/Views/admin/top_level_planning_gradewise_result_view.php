<?php if (empty($grouped_data)): ?>
    <div class="alert alert-info">No data found with the selected filters.</div>
<?php else: ?>
    <!-- Card View Container -->
    <div class="subject-card-container card-view">
        <?php foreach ($grouped_data as $class_id => $class_data): ?>
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h4 class="card-title mb-0"><?= $class_data['class_name'] ?></h4>
                </div>
                <div class="card-body">
                    <?php $subject_counter = 1; ?>
                    <?php foreach ($class_data['subjects'] as $subject_id => $subject_data): ?>
                        <div class="subject-card card mb-3">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <?= $subject_counter ?>. <?= $subject_data['subject_name'] ?>
                                </h5>
                                <div class="action-buttons no-print">
    <?php 
        if (!empty($subject_data['objectives'])):
            $first_term_id = array_key_first($class_data['terms']);
            if (isset($subject_data['objectives'][$first_term_id]['tlp_id'])):
    ?>
                <a href="<?= site_url('admin/top_level_planning_gradewise/edit/' . $subject_data['objectives'][$first_term_id]['tlp_id']) ?>" 
                   class="btn btn-xs btn-primary">
                    <i class="fas fa-edit"></i>
                </a>
                
    <?php 
            endif;
        endif; 
    ?>
  
</div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($class_data['terms'] as $term_id => $term_name): ?>
                                        <?php if (isset($subject_data['objectives'][$term_id])): ?>
                                            <div class="col-md-<?= floor(12 / count($class_data['terms'])) ?>">
                                                <div class="term-col">
                                                    <div class="term-header">
                                                        <?= $term_name ?>
                                                    </div>
                                                    <div class="objective-content">
                                                        <?= nl2br(htmlspecialchars($subject_data['objectives'][$term_id]['text'])) ?>
                                                        <div class="update-date">
                                                            <?php 
                                                            $dates = $subject_data['objectives'][$term_id]['dates'] ?? null;
                                                            $display_date = $dates['updated_date'] ?? $dates['created_date'] ?? 'N/A';
                                                            if ($display_date !== 'N/A') {
                                                                $display_date = date('d M Y', strtotime($display_date));
                                                            }
                                                            ?>
                                                            Updated: <?= $display_date ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php $subject_counter++; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Table View Container -->
    <div class="subject-table-container table-view">
        <?php foreach ($grouped_data as $class_id => $class_data): ?>
            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:20%;">Subject</th>
                            <?php foreach ($class_data['terms'] as $term_id => $term_name): ?>
                                <th><?= $term_name ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($class_data['subjects'] as $subject_id => $subject_data): ?>
                            <tr>
                                <td><?= $subject_data['subject_name'] ?></td>
                                <?php foreach ($class_data['terms'] as $term_id => $term_name): ?>
                                    <td>
                                        <?php if (isset($subject_data['objectives'][$term_id])): ?>
                                            <div class="objective-content">
                                                <?= nl2br(htmlspecialchars($subject_data['objectives'][$term_id]['text'])) ?>
                                                <div class="update-date">
                                                    <?php 
                                                    $dates = $subject_data['objectives'][$term_id]['dates'] ?? null;
                                                    $display_date = $dates['updated_date'] ?? $dates['created_date'] ?? 'N/A';
                                                    if ($display_date !== 'N/A') {
                                                        $display_date = date('d M Y', strtotime($display_date));
                                                    }
                                                    ?>
                                                    Updated: <?= $display_date ?>
                                                </div>
                                            </div>
                                            <div class="action-buttons no-print mt-2">
                                                <a href="<?= site_url('admin/top_level_planning_gradewise/edit/' . $subject_data['objectives'][$term_id]['tlp_id']) ?>" 
                                                   class="btn btn-xs btn-primary">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                            
                                            </div>
                                        <?php else: ?>
                                            <div class="text-muted">No objective set</div>
                                          
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>