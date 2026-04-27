<?php 
    $subjectName = $subjectNames[$ds->sec_sub_id] ?? '';
?>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Photo</th>
            <th>Student</th>
            <?php foreach ($datesheet as $ds): ?>
                <?php 
                    $subject = array_filter($subjects, function ($s) use ($ds) {
                        return $s->sec_sub_id == $ds->sec_sub_id;
                    });
                    $subject = array_values($subject);
                    $subjectName = '';
                    if (!empty($subject)) {
                        $subjectRow = $this->db->table('allsubject')->where('sid', $subject[0]->subject_id)->get()->getRow();
                        $subjectName = $subjectRow->subject_short_name ?? '';
                    }
                ?>
                <th>
                    <?= esc($subjectName) ?><br>
                    <small>Total: <?= esc($ds->total_marks) ?></small>
                </th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php $i = 1; foreach ($students as $student): ?>
            <?php $std = $this->db->table('students')->where('student_id', $student->student_id)->get()->getRow(); ?>
            <tr>
                <td><?= $i++ ?></td>
                <td>
                    <?php if (!empty($std->profile_photo) && file_exists(FCPATH.'uploads/'.$std->profile_photo)): ?>
                        <img src="<?= base_url('uploads/'.$std->profile_photo) ?>" width="40" height="40" class="rounded-circle">
                    <?php else: ?>
                        <i class="fa fa-user fa-2x"></i>
                    <?php endif; ?>
                </td>
                <td>
                    <b><?= esc($std->reg_no) ?></b><br>
                    <?= esc($std->first_name . ' ' . $std->last_name) ?>
                    <input type="hidden" name="student_id[]" value="<?= esc($std->student_id) ?>">
                </td>
                <?php foreach ($datesheet as $ds): ?>
                    <?php
                        $res = $this->db->table('subject_results')
                            ->where('student_id', $student->student_id)
                            ->where('eid', $eid)
                            ->where('sec_sub_id', $ds->sec_sub_id)
                            ->get()->getRow();
                    ?>
                    <td>
                        <input type="hidden" name="result_id[<?= $student->student_id ?>][<?= $ds->sec_sub_id ?>]" value="<?= $res->result_id ?? 0 ?>">
                        <input type="hidden" name="sec_sub_id[<?= $ds->sec_sub_id ?>]" value="<?= $ds->sec_sub_id ?>">
                        <input type="number" step="any" min="0" max="<?= $ds->total_marks ?>"
                               name="obtained_marks[<?= $student->student_id ?>][<?= $ds->sec_sub_id ?>]"
                               value="<?= $res->obtained_marks ?? 0 ?>"
                               class="form-control">
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
