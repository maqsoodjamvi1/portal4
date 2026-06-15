<?php if (!empty($subjects)): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Subject</th>
                <th>Class</th>
                <th>Section</th>
                <th>Assigned Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subjects as $index => $subject): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= esc($subject->subject_name) ?></td>
                <td><?= esc($subject->class_name) ?></td>
                <td><?= esc($subject->section_name) ?></td>
                <td><?= date('d M Y', strtotime($subject->created_date)) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i> No subjects assigned to this teacher.
    </div>
<?php endif; ?>