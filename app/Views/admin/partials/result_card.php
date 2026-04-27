<?php
/**
 * View: admin/partials/result_card.php
 * Rendered for each student result card
 */
if (empty($student_info) || empty($parent_info) || empty($exams)) return;
?>

<div class="student-result-card" style="page-break-inside: avoid;">
    <div class="printable-header" style="overflow: hidden; position: relative; height: auto; margin-bottom: 10px; padding: 10px; border-bottom: 2px solid #000;">
        <?php if (!empty($schoolinfo->logo)) : ?>
            <img src="<?= base_url('system-logo/' . $schoolinfo->logo) ?>" style="position: absolute; left: 10px; top: 10px; width: 120px; height: 120px; object-fit: contain; border: none;">
        <?php endif; ?>
        <h1 style="margin: 0 auto; font-size: 60px; font-family: Bebas Neue, cursive; letter-spacing: 2px; word-spacing: 4px; transform: scaleX(1.3); display: block; text-align: center; width: fit-content;">
            <?= esc($schoolinfo->system_name) ?>
        </h1>
        <p style="margin: 1px 0; text-align: center; font-size: 20px;"><strong>Campus:</strong> <?= esc($campus_info->campus_name ?? '') ?> | <strong>Phone:</strong> <?= esc($campus_info->landline ?? '') ?></p>
        <p style="margin: 0px 0; text-align: center; font-size: 20px;"><strong>Location:</strong> <?= esc($campus_info->location ?? '') ?></p>
        <p style="margin: 0px 0; text-align: center; font-size: 20px;"><strong>Website:</strong> <?= esc($campus_info->website ?? '') ?></p>
        <h2 style="margin: 0 auto; font-size: 40px; font-family: Bebas Neue, cursive; letter-spacing: 2px; word-spacing: 4px; transform: scaleX(1.3); display: block; text-align: center; width: fit-content;">
            Academic Report of <?= esc(end($exams)['exam_name'] ?? 'Latest Exam') ?>
        </h2>
    </div>

    <div style="background: linear-gradient(90deg, #f0f4f8, #d9e2ec); padding: 15px; border-radius: 12px; margin: 10px 0; font-size: 16px; color: #2c3e50; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; box-shadow: 0 2px 6px rgba(0,0,0,0.1); display: flex; justify-content: space-between;">
        <div style="width: 78%;">
            <div style="width:100%; margin-bottom:10px; text-align:center;">
                <div style="display:inline-block; font-size: 22px; font-weight: bold; color: #1a1a1a;">
                    🏫 Class: <?= esc($class_info['sectionclassname'] ?? '') ?>
                </div>
            </div>
            <div style="width:100%; display:flex; justify-content:space-between; margin-bottom:5px;">
                <div style="width:48%; font-size: 18px;"><strong>🆔 Reg No:</strong> <?= esc($student_info->reg_no ?? '') ?></div>
                <div style="width:48%; font-size: 18px;"><strong>📞 Father Contact:</strong> <?= esc($parent_info->father_contact ?? '') ?></div>
            </div>
            <div style="width:100%; display:flex; justify-content:space-between; margin-bottom:5px;">
                <div style="width:48%; font-size: 18px;"><strong>👤 Name:</strong> <?= esc(($student_info->first_name ?? '') . ' ' . ($student_info->last_name ?? '')) ?></div>
                <div style="width:48%; font-size: 18px;"><strong>📱 Mother Contact:</strong> <?= esc($parent_info->mother_contact ?? '') ?></div>
            </div>
            <div style="width:100%; display:flex; justify-content:space-between;">
                <div style="width:48%; font-size: 18px;"><strong>👨‍👧 Father:</strong> <?= esc($parent_info->f_name ?? '') ?></div>
                <div style="width:48%; font-size: 18px;"><strong>🚨 Emergency:</strong> <?= esc($parent_info->emergency_contact ?? '') ?></div>
            </div>
        </div>
        <div style="width: 20%; text-align: center;">
            <?php if (!empty($student_info->profile_photo)) : ?>
                <img src="<?= base_url('uploads/' . $student_info->profile_photo) ?>" style="width: 100px; height: 130px; object-fit: cover; border-radius: 8px; border: 2px solid #ccc;">
            <?php else : ?>
                <div style="width: 100px; height: 130px; border-radius: 8px; border: 1px solid #000; text-align: center; line-height: 130px; font-size: 24px;">
                    <i class="fa fa-user"></i>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php
        // Pass subjects, exams, student, settings, etc, to your result_table partial as before.
        $table = view('admin/partials/result_table', [
            'studentinfo' => $studentinfo,
            'student_info' => $student_info,
            'subjects'    => $subjects,
            'exams'       => $exams,
            'examRankings' => $examRankings ?? [],
            'cls_sec_id'  => $cls_sec_id,
            'settings'    => $settings,
        ]);
        echo $table;
    ?>

    <div class="signature-section" style="margin-top: 40px; display: flex; justify-content: space-between;">
        <div class="signature-box" style="text-align: center;">
            <div class="signature-line" style="border-top: 1px solid #000; width: 200px; margin: 0 auto 5px auto;"></div>
            <strong>Class Teacher</strong>
        </div>
        <div class="signature-box" style="text-align: center;">
            <div class="signature-line" style="border-top: 1px solid #000; width: 200px; margin: 0 auto 5px auto;"></div>
            <strong>Principal</strong>
        </div>
    </div>
</div>
<div style="page-break-after:always;"></div>
