<!doctype html>

<html lang="en">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Bonafide Certificate</title>

    <style>

        @page { size: A4; margin: 14mm 16mm; }

        * { box-sizing: border-box; }

        body { margin: 0; font-family: "Times New Roman", Times, serif; color: #000; background: #f1f3f5; }

        .sheet { width: 210mm; min-height: 273mm; margin: 0 auto 14px; background: #fff; border: 1px solid #e5e7eb; padding: 12mm 16mm 14mm; }

        .letterhead {

            display: grid;

            grid-template-columns: 110px 1fr 110px;

            align-items: center;

            margin-bottom: 10px;

            column-gap: 10px;

        }

        .logo-box { text-align: left; }

        .logo-box img { max-width: 88px; max-height: 88px; object-fit: contain; }

        .school-center { text-align: center; }

        .school-name { font-size: 28px; font-weight: 700; text-transform: uppercase; }

        .school-contact { font-size: 14px; margin-top: 4px; line-height: 1.4; }

        .meta-row {

            margin: 18px 0 20px;

            font-size: 16px;

            display: flex;

            justify-content: space-between;

            align-items: center;

        }

        .ref-line {

            display: inline-block;

            min-width: 170px;

            border-bottom: 1px solid #000;

            height: 16px;

            vertical-align: middle;

        }

        .title { text-align: center; font-size: 28px; text-transform: uppercase; font-weight: 700; text-decoration: underline; margin: 8px 0 16px; }

        .body-text { font-size: 17px; line-height: 1.9; margin-bottom: 10px; text-align: left; }

        .facts { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 16px; }

        .facts th, .facts td { border: 1px solid #000; padding: 10px 12px; vertical-align: top; }

        .facts th { text-align: left; background: #fff; }

        .facts th:first-child, .facts td:first-child { width: 36%; font-weight: 700; }

        .closing { margin-top: 14px; font-size: 16px; line-height: 1.9; }

        .signature-block { margin-top: 26px; font-size: 16px; line-height: 1.9; }

        .seal-space {

            width: 220px;

            height: 90px;

            border: 1px dashed #000;

            margin-top: 8px;

        }

        @media print {

            body { background: #fff; color: #000 !important; }

            * { color: #000 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

            .sheet { margin: 0; width: 100%; min-height: 273mm; border: 0; padding: 10mm 16mm 12mm; }

            .letterhead { grid-template-columns: 110px 1fr 110px; }

        }

    </style>

</head>

<body>

<?php

    $toggles = $toggles ?? [];

    $show = static function (string $key) use ($toggles): bool {

        return !empty($toggles[$key]);

    };



    $regNo = trim((string)($student['reg_no'] ?? ''));

    $fatherName = trim((string)($student['father_name'] ?? ''));

    $className = trim((string)($student['class_name'] ?? ''));

    $sectionName = trim((string)($student['section_name'] ?? ''));

    $dobDisplay = trim((string)($dob_display ?? ''));

    $monthlyFeeAmount = (float)($monthly_fee ?? 0);

    $currentFeeAmount = (float)($current_fee ?? 0);

    $schoolPhone = trim((string)($school_phone ?? ''));

    $schoolEmail = trim((string)($school_email ?? ''));

    $schoolAddress = trim((string)($school_address ?? ''));

    $schoolLogo = trim((string)($school_logo ?? ''));

    $principalName = trim((string)($principal_name ?? ''));

    $purposeText = trim((string)($purpose_text ?? ''));



    $factsRows = [

        ['Student Name', (string)($student_name ?? '')],

    ];



    if ($show('show_father')) {

        $factsRows[] = ["Father's Name", $fatherName !== '' ? $fatherName : '-'];

    }

    if ($show('show_class')) {

        $factsRows[] = ['Class', $className !== '' ? $className : '-'];

        $factsRows[] = ['Section', $sectionName !== '' ? $sectionName : '-'];

    }

    if ($show('show_reg_no')) {

        $factsRows[] = ['Registration No.', $regNo !== '' ? $regNo : '-'];

    }

    if ($show('show_dob')) {

        $factsRows[] = ['Date of Birth', $dobDisplay !== '' ? $dobDisplay : 'Not on record'];

    }

    if ($show('show_monthly_fee')) {

        $factsRows[] = ['Monthly Tuition Fee', 'Rs. ' . number_format($monthlyFeeAmount, 0) . '/-'];

    }

    if ($show('show_current_fee')) {

        $factsRows[] = ['Current Fee (Unpaid Total)', 'Rs. ' . number_format($currentFeeAmount, 0) . '/-'];

    }

?>

    <div class="sheet">

        <div class="letterhead">

            <div class="logo-box">

                <?php if ($schoolLogo !== ''): ?>

                    <img src="<?= esc($schoolLogo) ?>" alt="School Logo">

                <?php endif; ?>

            </div>

            <div class="school-center">

                <div class="school-name"><?= esc((string)($school_name ?? 'TIME SCHOOL SYSTEM')) ?></div>

                <div class="school-contact">

                    Address: <?= esc($schoolAddress !== '' ? $schoolAddress : '[Insert School Address]') ?><br>

                    Phone: <?= esc($schoolPhone !== '' ? $schoolPhone : '[Insert Phone Number]') ?>

                    |

                    Email: <?= esc($schoolEmail !== '' ? $schoolEmail : '[Insert Email Address]') ?>

                </div>

            </div>

            <div></div>

        </div>



        <?php if ($show('show_issue_date')): ?>

        <div class="meta-row">

            <div><strong>Date:</strong> <?= esc((string)($issue_date ?? date('d M Y'))) ?></div>

            <div><strong>Ref No:</strong> <span class="ref-line"></span></div>

        </div>

        <?php endif; ?>



        <div class="title">Bonafide Certificate</div>



        <div class="body-text">

            Certified that Mr. <strong><?= esc((string)($student_name ?? '')) ?></strong> is a bonafide student of our school.

            The details of the student are as under:

        </div>



        <table class="facts">

            <tr>

                <th>Particular</th>

                <th>Details</th>

            </tr>

            <?php foreach ($factsRows as [$label, $value]): ?>

            <tr>

                <td><?= esc($label) ?></td>

                <td><?= esc($value) ?></td>

            </tr>

            <?php endforeach; ?>

        </table>



        <div class="closing">

            This certificate is issued upon the request of the student's parent/guardian for

            <strong><?= esc($purposeText !== '' ? $purposeText : '[Reason not provided]') ?></strong>.

        </div>



        <div class="signature-block">

            <div><strong>PRINCIPAL'S SIGNATURE</strong></div>

            <br>

            <div>Name: <?= esc($principalName !== '' ? $principalName : '[Principal Name]') ?></div>

            <div>Designation: Principal</div>

            <div class="mt-2"><strong>Seal/Stamp:</strong></div>

            <div class="seal-space"></div>

        </div>

    </div>

</body>

</html>

