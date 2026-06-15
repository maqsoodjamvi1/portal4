<html dir="rtl" lang="ur">
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('admin/chalanview/partials/chalan_print_styles') ?>
<style>
  @media print {
    .pagebreak { page-break-before: always; }
    #user-edit-form { display: none; }
    .no-print { display: none !important; }
  }

  .slip-col { width: 32%; float: left; margin-left: 1%; }
  .chalanwrapper {
    border: var(--border);
    background: #fff;
    font-size: var(--font-main);
    width: 100%;
    float: left;
  }

  /* HEADER (no borders). Logo left, stacked text right */
  .header-block {
    display: grid;
    grid-template-columns: 80px 1fr;   /* left logo, right text */
    padding: 6px 0 4px 0;
  }
  .header-logo {
    display: flex; align-items: center; justify-content: center;
    padding: 0 6px; border-end: 0;    /* no vertical divider */
  }
  .header-logo img { max-width: 64px; height: auto; }

  /* Center all header lines + compact vertical rhythm */
  .header-lines{
    display: grid;
    grid-auto-rows: auto;               /* size to content */
    justify-items: center;              /* center each line horizontally */
    align-items: center;                /* center vertically within each row */
    text-align: center;                 /* center text within line */
  }
  .header-line{
    width: 100%;
    text-align: center !important;      /* hard center */
    line-height: 1.2;                   /* tighter spacing */
    padding: 2px var(--pad-x);          /* small vertical padding */
    margin: 0;
    border: 0 !important;               /* ensure borderless header lines */
  }
  .header-line.school{
    font-size: 20px;                     /* default; inline style overrides for long names */
    font-weight: 800;
    line-height: 1.15;                  /* compact large text */
    padding-bottom: 0;                  /* reduce space under school */
    white-space: nowrap;
  }
  .header-line.campus{
    font-size: 16px;
    font-weight: 700;
    line-height: 1.15;
    padding-top: 0;                     /* sit closer to school name */
  }
  .header-line.bank{
    font-size: var(--font-small);
    line-height: 1.15;
    padding-bottom: 0;                  /* tighten below bank line */
  }
  .header-line.acc{
    font-size: var(--font-small);
    line-height: 1.15;
    padding-top: 0;                     /* sit closer to bank line */
  }

  /* Student info rows (equal height) */
  .info-row {
    border-bottom: var(--border);
    min-height: var(--row-h);
    line-height: var(--row-h);
    display: flex; align-items: center;
    padding: 0 var(--pad-x);
  }
  .info-row.meta-row { border-top: var(--border); } /* line above meta row */

  /* Two-column rows with NO vertical divider between cells */
  .info-row.grid-2 {
    display: grid; grid-template-columns: 1fr 1fr; column-gap: 0; padding: 0;
  }
  .cell {
    padding: 0 var(--pad-x);
    display: flex; align-items: center;
    min-height: var(--row-h); line-height: var(--row-h);
    border-end: 0;                      /* no vertical line */
  }

  /* Force left alignment (inside RTL page) where needed */
  .left-ltr { text-align: left !important; direction: ltr !important; }
  .meta-inline { display: inline-flex; gap: 16px; margin-left: auto; }

  /* Particulars table — inner gridlines only (no double border) */
  .feetable{
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    margin: 0;
    border: 0;
  }
  /* make Particulars wider, shrink Amount/Discount */
  .feetable colgroup col.particulars{ width: auto; }  /* takes the rest */
  .feetable colgroup col.amount{ width: 80px; }
  .feetable colgroup col.discount{ width: 80px; }

  .feetable th, .feetable td{
    border: 1px solid #000;
    padding: 0 var(--pad-x);
    min-height: var(--row-h); height: var(--row-h);
    line-height: 1.15;
    vertical-align: middle; text-align: left;
    overflow: hidden; white-space: nowrap; text-overflow: ellipsis;
    font-size: var(--font-small);
  }
  .feetable th:first-child,
  .feetable td:first-child{
    white-space: normal;
    word-break: break-word;
    font-size: 10px;
    line-height: 1.15;
  }
  .feetable thead th{ white-space: nowrap; font-size: 9px; }
  /* avoid double outer border with slip box */
  .feetable thead tr th{ border-top: 0; }
  .feetable tbody tr:last-child td{ border-bottom: 0; }
  .feetable tr th:first-child,
  .feetable tr td:first-child{ border-start: 0; }
  .feetable tr th:last-child,
  .feetable tr td:last-child{ border-end: 0; }

  .feetable thead th{ font-weight: 600; }

  .feetable .fee-summary-mini-row td {
    font-size: 10px;
  }
  /* strong underline under final total row */
  .feetable .total-row td{
    border-bottom: 2px solid #000 !important;
    font-size: 12px;
    font-weight: 700;
  }

  .copy-label {
    text-align: center; font-weight: 600;
    min-height: var(--row-h); line-height: var(--row-h);
    margin-bottom: 4px;
  }
  .slip-footer-msg { text-align: left; padding: 6px var(--pad-x); font-size: var(--font-small); }
  .filter-card { margin-bottom: 12px; }
  .feetable tbody tr.fee-pdf-detail-row td {
    min-height: var(--row-h);
    height: var(--row-h);
    vertical-align: middle;
  }
</style>


<?php
$cls_sec_id = $cls_sec_id ?? '';
$fee_month = $fee_month ?? '';
$fine_after_due_date = $fine_after_due_date ?? '';
$footer_line1 = $footer_line1 ?? '';
$show_line1 = $show_line1 ?? '';
$footer_line2 = $footer_line2 ?? '';
$show_line2 = $show_line2 ?? '';
?>

<div class="no-print">
<?= view('components/page_header', [
    'title' => 'Fee Chalan PDF',
    'icon' => 'fas fa-file-pdf',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Print Fee Chalan', 'url' => base_url('admin/print-fee-chalan')],
        ['label' => 'PDF', 'active' => true],
    ],
]) ?>
</div>

<section class="content">
  <!-- Filters (screen only) -->
  <div class="row no-print">
    <div class="col-lg-12">
      <form action="<?= base_url('admin/fee-chalan/pdf') ?>" id="user-edit-form" method="get" class="filter-card card card-body">
        <div class="row">
          <div class="col-lg-4 form-group">
            <label>Fee Month:</label>
            <input type="month" class="form-control" name="fee_month" value="<?= esc($fee_month) ?>">
          </div>
          <div class="col-lg-4 form-group">
            <label><strong>Class</strong></label>
            <select class="form-control" name="cls_sec_id">
              <option value="">All Classes</option>
              <?php foreach (($sectionsclassinfo ?? []) as $sectionvalue): ?>
                <option value="<?= esc($sectionvalue['section_id'] ?? '') ?>" <?= $cls_sec_id == ($sectionvalue['section_id'] ?? '') ? 'selected' : '' ?>>
                  <?= esc($sectionvalue['sectionclassname'] ?? '') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-sm-2 form-group">
            <label>Display Fine</label>
            <input class="form-control" type="checkbox" name="fine_after_due_date" value="1" <?= (int)$fine_after_due_date === 1 ? 'checked' : '' ?>>
          </div>

          <div class="col-lg-4 form-group">
            <label>Footer Line 1:</label>
            <input type="text" class="form-control" name="footer_line1" value="<?= esc($footer_line1) ?>">
          </div>
          <div class="col-lg-4 form-group">
            <label>Footer Line 2:</label>
            <input type="text" class="form-control" name="footer_line2" value="<?= esc($footer_line2) ?>">
          </div>
          <div class="col-lg-2 form-group">
            <label>Show Footer Line 1:</label>
            <input type="checkbox" class="form-control" name="show_line1" value="1" <?= (int)$show_line1 === 1 ? 'checked' : '' ?>>
          </div>
          <div class="col-lg-2 form-group">
            <label>Show Footer Line 2:</label>
            <input type="checkbox" class="form-control" name="show_line2" value="1" <?= (int)$show_line2 === 1 ? 'checked' : '' ?>>
          </div>

          <div class="col-sm-2 align-self-end">
            <input class="btn btn-primary" type="submit" value="View">
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Slips -->
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
        <div class="card-body">
          <?php if (!empty($data)): ?>
            <?php foreach ($data as $student_info): ?>
              <?php
                // Header fields (from controller)
                $hdr_chalan_id = $student_info['last_chalan_id'] ?? ($student_info['chalan_id'] ?? '');
                $hdr_issue     = $student_info['last_issue_date_label'] ?? ($student_info['last_issue_date'] ?? '');
                $hdr_due       = $student_info['last_due_date_label'] ?? ($student_info['last_due_date'] ?? '');
                $payableTotal  = (float)($student_info['unpaid_total_payable'] ?? 0);
                $payableMonthly = (float) ($student_info['unpaid_payable_monthly'] ?? 0);
                $payableOther   = (float) ($student_info['unpaid_payable_other'] ?? 0);
                if (($payableMonthly + $payableOther) <= 0 && ! empty($student_info['unpaid_rows'])) {
                    foreach ($student_info['unpaid_rows'] as $u) {
                        $net = (float) ($u['net_amount'] ?? ((float) ($u['amount'] ?? 0) - (float) ($u['discount'] ?? 0)));
                        if ((int) ($u['is_monthly_fee'] ?? 0) === 1) {
                            $payableMonthly += $net;
                        } else {
                            $payableOther += $net;
                        }
                    }
                }

                // Strict MM/YYYY for fee month
                $hdr_fee_month_compact = '';
                $rawMonth = $student_info['last_fee_month'] ?? '';
                if ($rawMonth) {
                  $parts = explode('-', $rawMonth);
                  if (count($parts) === 2) {
                    if ((int)$parts[0] > 12) { $y=(int)$parts[0]; $m=(int)$parts[1]; } else { $m=(int)$parts[0]; $y=(int)$parts[1]; }
                    if ($y > 0 && $m >= 1 && $m <= 12) { $hdr_fee_month_compact = sprintf('%02d/%04d', $m, $y); }
                  }
                }
                if (!$hdr_fee_month_compact) {
                  $hdr_fee_month_compact = $student_info['last_fee_month_label'] ?? ($student_info['last_fee_month'] ?? '');
                }

                $displayRows = $student_info['unpaid_display_rows'] ?? [];
                while (count($displayRows) < 5) {
                  $displayRows[] = [
                    'is_blank' => true,
                    'particulars_label' => '',
                    'amount' => '',
                    'discount' => '',
                    'net_amount' => 0,
                  ];
                }
                $displayRows = array_slice($displayRows, 0, 5);
              ?>

              <div class="pagebreak" style="overflow:hidden; clear:both;">
                <?php foreach (['Bank Copy', 'School Copy', 'Student Copy'] as $copyType): ?>
                  <div class="slip-col">
                    <div class="copy-label"><?= esc($copyType) ?></div>

                    <div class="chalanwrapper">
                      <!-- HEADER (no borders; spaced campus->bank; bank->account tight) -->
                      <div class="header-block">
                        <div class="header-logo">
                          <?php if (!empty($student_info['logo'])): ?>
                            <img src="<?= base_url('system-logo/' . $student_info['logo']) ?>" alt="logo">
                          <?php endif; ?>
                        </div>
                        <div class="header-lines">
                          <?php
  $schoolName = trim($student_info['system_name'] ?? '');
  $schoolNameFontPx = school_name_fit_font_size($schoolName, 22, 20.0, 8.0);
?>
<div class="header-line school" style="font-size: <?= esc((string) $schoolNameFontPx, 'attr') ?>px;"><?= esc($schoolName) ?></div>
                          <div class="header-line campus"><?= esc($student_info['campus_name'] ?? '') ?><?= !empty($student_info['location']) ? '، ' . esc($student_info['location']) : '' ?></div>
                          <div class="header-line bank">
                            <?php if (!empty($student_info['bank_name'])): ?>
                              <?= esc($student_info['bank_name']) ?>
                              <?= !empty($student_info['bank_address']) ? '، ' . esc($student_info['bank_address']) : '' ?>
                              <?= !empty($student_info['bank_code']) ? '، ' . esc($student_info['bank_code']) : '' ?>
                            <?php endif; ?>
                          </div>
                          <div class="header-line acc">
                            <?= !empty($student_info['bank_acc']) ? 'Account No: ' . esc($student_info['bank_acc']) : '' ?>
                          </div>
                        </div>
                      </div>

                      <!-- INFO (equal heights) -->
                      <!-- 1) Chalan ID + Reg No + Family ID (with line above) -->
                      <div class="info-row left-ltr meta-row">
                        <div>Ch: <?= esc($hdr_chalan_id) ?></div>
                        <div class="meta-inline">
                          <span>F. id: <?= esc($student_info['parent_id'] ?? '') ?></span>
                        </div>
                      </div>
                      <!-- 2) Student Name -->
                      <div class="info-row left-ltr">Name: <?= esc($student_info['student_name'] ?? '') ?></div>
                      <!-- 3) Father Name -->
                      <div class="info-row left-ltr">Father Name: <?= esc($student_info['f_name'] ?? '') ?></div>
                      <!-- 4) Issue Date + Due Date (no vertical line) -->
                      <div class="info-row grid-2">
                        <div class="cell">Issue: <?= esc($hdr_issue) ?></div>
                        <div class="cell"><strong>Due: <?= esc($hdr_due) ?></strong></div>
                      </div>
                     <?php
  // Build "ClassName - SectionName"
  $className   = trim($student_info['class_name'] ?? '');
  $sectionName = trim($student_info['section_name'] ?? '');
  $classWithSection = $className . ($sectionName !== '' ? ' - ' . $sectionName : '');
?>
<!-- 5) Class + Fee Month (MM/YYYY) -->
<div class="info-row grid-2">
  <div class="cell">
    Class: <?= esc($student_info['class_name'] ?? '') ?>
    <?php if (!empty($student_info['section_short_name'])): ?>
      - <?= esc($student_info['section_short_name']) ?>
    <?php elseif (!empty($student_info['section_name'])): /* optional fallback if you kept it elsewhere */ ?>
      - <?= esc($student_info['section_name']) ?>
    <?php endif; ?>
  </div>
  <div class="cell">Fee Month: <?= esc($hdr_fee_month_compact) ?></div>
</div>

                      <!-- FEE TABLE -->
                      <table class="feetable">
                        <colgroup>
                          <col class="particulars">
                          <col class="amount">
                          <col class="discount">
                        </colgroup>
                        <thead>
                          <tr>
                            <th>Item</th>
                            <th>Amt</th>
                            <th>Disc</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($displayRows as $dr): ?>
                            <?php
                              $isBlank = !empty($dr['is_blank']);
                              $amt     = $isBlank ? '' : number_format((float) ($dr['amount'] ?? 0), 0);
                              $disc    = $isBlank ? '' : (((float) ($dr['discount'] ?? 0)) > 0 ? number_format((float) $dr['discount'], 0) : '');
                              $part    = $isBlank ? '' : ($dr['particulars_label'] ?? '');
                            ?>
                            <tr class="fee-pdf-detail-row">
                              <td><?= esc($part) ?></td>
                              <td><?= money_from_base($amt) ?><?= $amt !== '' ? '/-' : '' ?></td>
                              <td><?= money_from_base($disc) ?></td>
                            </tr>
                          <?php endforeach; ?>

                          <!-- Totals — monthly / other / grand -->
                         <tr class="fee-summary-mini-row">
  <td>Monthly fee (unpaid)</td>
  <td><?= money_from_base(number_format($payableMonthly, 0)) ?>/-</td>
  <td></td>
</tr>
<tr class="fee-summary-mini-row">
  <td>Other fee (unpaid)</td>
  <td><?= money_from_base(number_format($payableOther, 0)) ?>/-</td>
  <td></td>
</tr>
<tr class="total-row">
  <td><strong>Total payable</strong></td>
  <td><strong><?= money_from_base(number_format($payableTotal, 0)) ?>/-</strong></td>
  <td></td>
</tr>

                          <?php
                            $late_fee_fine = 0;
                            if (!empty($student_info['late_fee_fine'])) {
                              $late_fee_fine = ($student_info['fine_type'] ?? '') === 'per_day_fine'
                                ? ((float)$student_info['late_fee_fine']) * 15
                                : (float)$student_info['late_fee_fine'];
                            }
                          ?>
                          <?php if ((int)$fine_after_due_date === 1): ?>
                            <tr>
                              <td>Late Fee Fine</td>
                              <td><?= esc(number_format($late_fee_fine, 2)) ?>/-</td>
                              <td></td>
                            </tr>
                            <tr>
                              <td><strong>Payable After Due Date</strong></td>
                              <td><strong><?= esc(number_format($payableTotal + $late_fee_fine, 2)) ?>/-</strong></td>
                              <td></td>
                            </tr>
                          <?php endif; ?>
                        </tbody>
                      </table>

                      <?php
                      $accountsDisclaimerStd = 'If any mistakes are found in the challan, please contact the Accounts Office.';
                      $customFoot = trim((string) ($student_info['chalan_f_msg'] ?? ''));
                      $footerNotice = $customFoot !== '' ? $customFoot : $accountsDisclaimerStd;
                      ?>
                      <div class="slip-footer-msg chalan-accounts-disclaimer"><?= esc($footerNotice) ?></div>
                    </div>

                    <?php if ((int)$show_line1 === 1): ?>
                      <div style="float:left; width:100%; border-bottom:1px solid; margin-top:10px;"><?= esc($footer_line1) ?>&nbsp;&nbsp;</div>
                    <?php endif; ?>
                    <?php if ((int)$show_line2 === 1): ?>
                      <div style="float:left; width:100%; border-bottom:1px solid; margin-top:10px; margin-bottom:10px;"><?= esc($footer_line2) ?>&nbsp;&nbsp;</div>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/0.9.0rc1/jspdf.min.js"></script>
<?= $this->endSection() ?>
