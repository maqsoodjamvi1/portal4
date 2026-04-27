<?php
// ====================
// Monthly Attendance Partial (works with Class OR Class-Section)
// Paste over: app/Views/admin/partials/attendance_monthly_table.php
// No helper changes required.
// ====================

// ---- Safety & inputs ----
$campus_id  = (int)($campus_id  ?? 0);
$session_id = (int)($session_id ?? 0);
$class_id   = (int)($class_id   ?? 0);
$cls_sec_id = (int)($cls_sec_id ?? ($section_id ?? 0));
$section_id = $cls_sec_id; // keep legacy var name available
$dates      = $dates     ?? [];
$monthyear  = $monthyear ?? (isset($dates[0]) ? date('F Y', strtotime($dates[0])) : date('F Y'));

$db = db_connect();

// If controller already sent students list, re-use it; else we compute here.
$studentsList = $students ?? null;
$headerLabel  = '';

// ====================
// Resolve header + students for either filter
// ====================
if ($cls_sec_id > 0) {
    // --- SPECIFIC SECTION ---
    if (function_exists('getClassSection')) {
        $sectionInfo = getClassSection($cls_sec_id); // may be array or object depending on your helper
        if (is_object($sectionInfo)) {
            $sectionInfo = (array) $sectionInfo;
        }
    } else {
        $sectionInfo = $db->table('class_section cs')
            ->select('cs.cls_sec_id, cs.class_id, cs.section_id, c.class_short_name, c.class_name, s.section_name,
                      CONCAT(c.class_short_name, " - ", s.section_name) AS sectionclassname', false)
            ->join('classes c',  'c.class_id = cs.class_id',  'left')
            ->join('sections s', 's.section_id = cs.section_id', 'left')
            ->where('cs.cls_sec_id', $cls_sec_id)
            ->get()->getRowArray();
    }
    $headerLabel = $sectionInfo['sectionclassname'] ?? (($sectionInfo['class_short_name'] ?? $sectionInfo['class_name'] ?? 'Class').' - '.($sectionInfo['section_name'] ?? 'Section'));

    if ($studentsList === null) {
        if (function_exists('getStudentsBySection')) {
            $studentsList = getStudentsBySection($cls_sec_id); // expected to return a list of objects
        } else {
            $studentsList = $db->table('student_class sc')
                ->select('s.student_id, s.first_name, s.last_name, s.profile_photo, s.reg_no')
                ->join('students s', 's.student_id = sc.student_id', 'inner')
                ->where('sc.status', 1)
                ->where('sc.cls_sec_id', $cls_sec_id)
                ->orderBy('s.first_name', 'ASC')->orderBy('s.last_name', 'ASC')
                ->get()->getResult();
        }
    }
} else {
    // --- WHOLE CLASS (ALL ITS SECTIONS FOR THIS CAMPUS) ---
    $classRow = $db->table('classes')->select('class_id, class_name, class_short_name')->where('class_id', $class_id)->get()->getRowArray();
    $headerLabel = ($classRow['class_short_name'] ?? $classRow['class_name'] ?? 'Class') . ' (All Sections)';

    $secIds = $db->table('class_section')
        ->select('cls_sec_id')
        ->where('class_id', $class_id)
        ->where('campus_id', $campus_id)
        ->get()->getResultArray();
    $secIds = array_map(static fn($r) => (int)$r['cls_sec_id'], $secIds);

    if ($studentsList === null) {
        if (!empty($secIds)) {
            $studentsList = $db->table('student_class sc')
                ->select('s.student_id, s.first_name, s.last_name, s.profile_photo, s.reg_no')
                ->join('students s', 's.student_id = sc.student_id', 'inner')
                ->where('sc.status', 1)
                ->whereIn('sc.cls_sec_id', $secIds)
                ->orderBy('s.first_name', 'ASC')->orderBy('s.last_name', 'ASC')
                ->get()->getResult();
        } else {
            $studentsList = [];
        }
    }
}

$totalStudents = is_array($studentsList) ? count($studentsList) : (is_countable($studentsList) ? count($studentsList) : 0);

?>

<div class="container-fluid">
    <h1 class="text-center">Monthly Attendance Report</h1>

    <div class="row">
        <div class="col-lg-4 text-center">
            <h6><?= esc($headerLabel) ?></h6>
        </div>
        <div class="col-lg-4 text-center">
            <h6><?= esc($monthyear) ?></h6>
        </div>
        <div class="col-lg-4 text-center">
            <h6>Total Students: <?= (int)$totalStudents ?></h6>
        </div>
    </div>

    <?php if (empty($studentsList)): ?>
        <div class="alert alert-info mt-3">No students found for the selected filter.</div>
        <?php return; endif; ?>

    <table class="table table-bordered table-striped table-sm">
        <thead class="bg-primary text-white text-center">
            <tr>
                <th>Photo</th>
                <th>Name</th>
                <?php foreach ($dates as $day): ?>
                    <?php
                        $timestamp = strtotime($day);
                        if ($timestamp > strtotime(date('Y-m-d'))) break; // stop at future days
                        $dayNum  = date('d', $timestamp);
                        $dayName = substr(date('D', $timestamp), 0, 2);
                    ?>
                    <th><?= $dayNum.'<br>'.$dayName ?></th>
                <?php endforeach; ?>
                <th>P</th>
                <th>A</th>
                <th>LC</th>
                <th>EL</th>
                <th>L</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($studentsList as $student): ?>
                <?php
                    // Most helpers return objects; ensure object access
                    if (is_array($student)) { $student = (object)$student; }

                    $p = $a = $lc = $el = $l = 0;
                    $photoPath = 'uploads/'.($student->profile_photo ?? '');
                    $hasPhoto  = !empty($student->profile_photo) && is_file(FCPATH.$photoPath);
                    $photoTag  = $hasPhoto
                        ? '<img src="'.base_url($photoPath).'" class="rounded-circle" width="50" height="50" alt="Photo">'
                        : '<i class="fa fa-user fa-2x"></i>';
                ?>
                <tr>
                    <td class="text-center"><?= $photoTag ?></td>
                    <td>
                        <?= esc(trim(($student->first_name ?? '').' '.($student->last_name ?? ''))) ?><br>
                        <strong><?= esc($student->reg_no ?? '') ?></strong>
                    </td>

                    <?php foreach ($dates as $day): ?>
                        <?php
                            $timestamp = strtotime($day);
                            if ($timestamp > strtotime(date('Y-m-d'))) break;

                            // Query attendance for this student & day
                            $attn = $db->table('attendance')
                                ->select('status, el_duration, lc_duration')
                                ->where('student_id', (int)$student->student_id)
                                ->where('date', $day)
                                ->get()->getRow();

                            $statusHTML = '-';
                            if ($attn) {
                                if ($attn->status === 'P') {
                                    if ((int)$attn->el_duration > 0 && (int)$attn->lc_duration > 0) {
                                        $statusHTML = '<span class="badge bg-warning text-dark">LE</span>';
                                        $el++; $lc++;
                                    } elseif ((int)$attn->el_duration > 0) {
                                        $statusHTML = '<span class="badge bg-warning text-dark">EL</span>';
                                        $el++;
                                    } elseif ((int)$attn->lc_duration > 0) {
                                        $statusHTML = '<span class="badge bg-warning text-dark">LC</span>';
                                        $lc++;
                                    } else {
                                        $statusHTML = '<span class="badge bg-success">P</span>';
                                        $p++;
                                    }
                                } elseif ($attn->status === 'A') {
                                    $statusHTML = '<span class="badge bg-danger">A</span>';
                                    $a++;
                                } elseif ($attn->status === 'L') {
                                    $statusHTML = '<span class="badge bg-secondary">L</span>';
                                    $l++;
                                }
                            }
                        ?>
                        <td class="text-center"><?= $statusHTML ?></td>
                    <?php endforeach; ?>

                    <td><?= (int)$p ?></td>
                    <td><?= (int)$a ?></td>
                    <td><?= (int)$lc ?></td>
                    <td><?= (int)$el ?></td>
                    <td><?= (int)$l ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
