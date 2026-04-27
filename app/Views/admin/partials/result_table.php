<?php
/**
 * View: admin/partials/result_table.php
 * This includes subject marks, grades, percentages, positions, and attendance summary
 */

useShortName = $settings['useShortName'];
rowHeight = $settings['rowHeight'];
showMarks = $settings['showMarks'];
showPercentage = $settings['showPercentage'];
showGrades = $settings['showGrades'];
showAttendance = $settings['showAttendance'];
showPosition = $settings['showPosition'];

//$controller = $controller; // for calling grade() and ordinal() methods if needed

// Begin rendering result table below (this is placeholder and should be completed as per your original logic)
echo '<table class="table table-bordered">';
echo '<thead><tr><th>Subject</th>';
foreach ($examInfo as $exam) {
    if ($showMarks) {
        echo '<th>Obt</th><th>Total</th>';
    }
    if ($showPercentage) {
        echo '<th>%</th>';
    }
    if ($showGrades) {
        echo '<th>Grade</th>';
    }
}
echo '</tr></thead><tbody>';

foreach ($subjects as $subject) {
    $subjectLabel = $useShortName && !empty($subject->subject_short_name) ? $subject->subject_short_name : $subject->subject_name;
    echo '<tr><td>' . esc($subjectLabel) . '</td>';

    foreach ($examInfo as $exam) {
        $res = db()->table('subject_results')
            ->where('student_id', $studentinfo->student_id)
            ->where('eid', $exam['eid'])
            ->where('sec_sub_id', $subject->sec_sub_id)
            ->get()->getRow();

        $ds = db()->table('datesheet')
            ->where('eid', $exam['eid'])
            ->where('sec_sub_id', $subject->sec_sub_id)
            ->get()->getRow();

        if ($res && $ds && $ds->total_marks > 0) {
            $perc = round(($res->obtained_marks / $ds->total_marks) * 100);
            $grade = $controller->grade($perc)->grade_name ?? '-';

            if ($showMarks) {
                echo '<td>' . $res->obtained_marks . '</td><td>' . $ds->total_marks . '</td>';
            }
            if ($showPercentage) {
                echo '<td>' . $perc . '%</td>';
            }
            if ($showGrades) {
                echo '<td>' . $grade . '</td>';
            }
        } else {
            if ($showMarks) echo '<td>-</td><td>-</td>';
            if ($showPercentage) echo '<td>-</td>';
            if ($showGrades) echo '<td>-</td>';
        }
    }
    echo '</tr>';
}

// Totals, Position, and Attendance Rows
if ($showMarks || $showPercentage || $showGrades) {
    echo '<tr><th>Total</th>';
    foreach ($examInfo as $exam) {
        $total_obt = db()->query("SELECT SUM(r.obtained_marks) as obt FROM subject_results r JOIN datesheet d ON r.eid = d.eid AND r.sec_sub_id = d.sec_sub_id WHERE r.student_id = ? AND r.eid = ? AND d.cls_sec_id = ?", [$studentinfo->student_id, $exam['eid'], $cls_sec_id])->getRow()->obt ?? 0;

        $total_max = db()->query("SELECT SUM(d.total_marks) as total FROM datesheet d WHERE d.eid = ? AND d.cls_sec_id = ?", [$exam['eid'], $cls_sec_id])->getRow()->total ?? 0;

        $perc = ($total_max > 0) ? round(($total_obt / $total_max) * 100) : 0;
        $grade = $controller->grade($perc)->grade_name ?? '-';

        if ($showMarks) echo '<td>' . $total_obt . '</td><td>' . $total_max . '</td>';
        if ($showPercentage) echo '<td>' . $perc . '%</td>';
        if ($showGrades) echo '<td>' . $grade . '</td>';
    }
    echo '</tr>';
}

if ($showPosition) {
    echo '<tr><th>Position</th>';
    foreach ($examInfo as $exam) {
        $rank = $examRankings[$exam['eid']][$studentinfo->student_id] ?? '-';
        $rank_display = is_numeric($rank) ? $controller->ordinal($rank) : '-';
        $colspan = 0;
        if ($showMarks) $colspan += 2;
        if ($showPercentage) $colspan++;
        if ($showGrades) $colspan++;
        echo '<td colspan="' . $colspan . '" style="text-align:center;font-weight:bold;">' . $rank_display . '</td>';
    }
    echo '</tr>';
}

if ($showAttendance) {
    echo '<tr><th>Attendance</th>';
    foreach ($examInfo as $exam) {
        $term_session = db()->table('terms_session')->where('term_id', $exam['term_id'])->where('session_id', $exam['session_id'])->get()->getRow();
        $present = $absent = $late = $early = 0;

        if ($term_session && $term_session->start_date && $term_session->end_date) {
            $attendance_summary = db()->query("SELECT status, COUNT(*) as count FROM attendance WHERE student_id = ? AND date BETWEEN ? AND ? GROUP BY status", [$studentinfo->student_id, $term_session->start_date, $term_session->end_date])->getResult();
            foreach ($attendance_summary as $att) {
                switch ($att->status) {
                    case 'P': $present = $att->count; break;
                    case 'A': $absent = $att->count; break;
                    case 'LC': $late = $att->count; break;
                    case 'EL': $early = $att->count; break;
                }
            }
        }

        $colspan = 0;
        if ($showMarks) $colspan += 2;
        if ($showPercentage) $colspan++;
        if ($showGrades) $colspan++;

        echo '<td colspan="' . $colspan . '">';
        echo '<table style="width: 100%; font-size: 12px; text-align: center; border-collapse: collapse;"><tr>';
        echo '<td style="background: #e6ffed; color: #1a7f37; padding: 4px 6px; border-radius: 6px;"><strong>✅ Present:</strong> ' . $present . '</td>';
        echo '<td style="background: #ffeaea; color: #c0392b; padding: 4px 6px; border-radius: 6px;"><strong>❌ Absent:</strong> ' . $absent . '</td>';
        echo '<td style="background: #fff8e1; color: #b9770e; padding: 4px 6px; border-radius: 6px;"><strong>🕒 Late:</strong> ' . $late . '</td>';
        echo '<td style="background: #f0f0f5; color: #34495e; padding: 4px 6px; border-radius: 6px;"><strong>🚪 Early:</strong> ' . $early . '</td>';
        echo '</tr></table>';
        echo '</td>';
    }
    echo '</tr>';
}

echo '</tbody></table>';
