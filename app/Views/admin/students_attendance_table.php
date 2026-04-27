<?php if (!empty($students) && is_array($students)): ?>
    <table border="1" cellpadding="5" cellspacing="0" style="width:100%; border-collapse:collapse;">
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Roll No</th>
                <th>Class ID</th>
                <th>Section ID</th>
                <!-- Add more columns if needed -->
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?= $student->student_id ?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No students found for the selected class/section.</p>
<?php endif; ?>
