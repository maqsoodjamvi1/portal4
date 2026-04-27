<html dir="rtl" lang="ur">
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<!-- Content Header (Page header) -->
<style>
@media print {
    .pagebreak { page-break-before: always; }
    #user-edit-form { display: none; }
}
.chalanwrapper {
    border: 1px solid #000000;
    text-align: center;
    float: left;
    width: 100%;
    font-size: 15px;
    line-height: 22px;
}
th { text-align: left; padding-left: 10px; }
td { text-align: left; padding-left: 10px; }
.chalanrows { border-bottom: 1px solid #000000; text-align: left; padding-left: 10px; }
.feeinfo { font-size: 13px; border: 1px solid #000000; border-bottom: 0 none; margin: 3px; float: left; width: 98%; margin-bottom: 0px; line-height: 25px; }
.chalancolleft { border-bottom: 1px solid #000000; width: 50%; float: left; padding-left: 10px; padding-right: 10px; text-align: left; }
.chalancolright { border-bottom: 1px solid #000000; width: 50%; float: left; padding-left: 10px; text-align: left; }
.feetable { margin: 3px; line-height: 25px; text-align: left; padding-left: 10px; font-size: 13px; }
</style>

<section class="content">
    <div class="row">
        <div class="col-lg-12">
            <!-- Form section remains unchanged -->
            <?php
              // Initialize form variables at the top
              $footer_line1 = $_GET['footer_line1'] ?? '';
              $footer_line2 = $_GET['footer_line2'] ?? '';
              $show_line1 = isset($_GET['show_line1']) ? 1 : 0;
              $show_line2 = isset($_GET['show_line2']) ? 1 : 0;
            ?>
            <!-- In the form section -->
            <!-- <div class="col-lg-2 form-group">
                <label>Show Footer Line 1:</label>
                <input type="checkbox" class="form-control pull-right" <?= $show_line1 ? 'checked' : '' ?> value="1" name="show_line1">
            </div>
            <div class="col-lg-2 form-group">
                <label>Show Footer Line 2:</label>
                <input type="checkbox" class="form-control pull-right" <?= $show_line2 ? 'checked' : '' ?> value="1" name="show_line2">
            </div> -->
            <div class="card card-primary card-outline card-tabs">
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <div class="card-body">
                        <?php foreach ($data as $student_info) { ?>
                        <div class="tab-content pagebreak table-responsive no-padding">
                            <div style="margin-bottom: 20px; float: left; width: 100%;">
                                <div style="width:100%;" id="printarea">
                                    <?php for ($copy = 1; $copy <= 3; $copy++) { ?>
                                    <div style="width:32%; float:left; margin-left:1%;">
                                        <div style="text-align: center;">
                                            <?= htmlspecialchars(trim(
                                                $student_info['father_contact'] . ", " . 
                                                $student_info['mother_contact'] . ", " . 
                                                $student_info['emergency_contact']
                                            )) ?>
                                        </div>
                                        <div class="chalanwrapper">
                                            <div class="row">
                                                <div class="col-sm-3 ml-2 mt-2">
                                                    <img style="width: 100%;" src="<?= base_url() ?>system-logo/<?= $student_info['logo'] ?>">
                                                </div>
                                                <div class="col-sm-8">
                                                    <div style="font-weight: bold;">
                                                        <?= $copy === 1 ? 'Bank Copy' : ($copy === 2 ? 'School Copy' : 'Student Copy') ?>
                                                    </div>
                                                    <?= htmlspecialchars($student_info['system_name']) ?><br>
                                                    <?= htmlspecialchars($student_info['campus_name']) ?>, <?= htmlspecialchars($student_info['location']) ?>
                                                </div>
                                            </div>
                                            
                                            <div class="ml-2 mt-2" style="text-align: left;">
                                                <?php if (!empty($student_info['bank_name'])): ?>
                                                <?= htmlspecialchars($student_info['bank_name']) ?>, 
                                                <?php endif; ?>
                                                <?= htmlspecialchars($student_info['bank_address']) ?><br>
                                                <?php if (!empty($student_info['bank_code'])): ?>
                                                Code: <?= htmlspecialchars($student_info['bank_code']) ?><br>
                                                <?php endif; ?>
                                                <?php if (!empty($student_info['bank_acc'])): ?>
                                                Account No: <?= htmlspecialchars($student_info['bank_acc']) ?>
                                                <?php endif; ?>
                                            </div>

                                            <div class="feeinfo">
                                                <div class="chalanrows">
                                                    Chalan# <?= $student_info['chalan_no'] ?>
                                                    <span style="float: right; margin-right: 10px;">
                                                        Family#: <?= $student_info['family_no'] ?>
                                                    </span>
                                                </div>
                                                <div class="chalanrows" style="line-height: 25px; font-size: 11px;">
                                                   Student Name: <?= ($student_info['stdinfo']) ?>
                                                </div>
                                                <div class="chalanrows">
                                                    Father Name: <?= htmlspecialchars($student_info['f_name']) ?>
                                                </div>
                                                <div class="chalancolleft">
                                                    Issue Date: <?= $student_info['issue_date'] ?>
                                                </div>
                                                <div class="chalancolright">
                                                    Due Date: <?= $student_info['due_date'] ?>
                                                </div>
                                                <div class="chalancolright">
                                                    Fee Month: <?= $student_info['fee_month'] ?>
                                                </div>
                                            </div>

                                            <table width="98%" border="1" class="feetable">
                                                <tr>
                                                    <th>Particulars</th>
                                                    <th>Amount</th>
                                                </tr>
                                                <?php
                                                $total = 0;
                                                $row_count = 0;
                                                $arrears = 0;
                                                
                                                foreach ($student_info['student_fee'] as $fee) {
                                                    $amount = $fee['amount'] - $fee['discount'];
                                                    if ($row_count < 5) {
                                                        echo "<tr>
                                                            <td>{$fee['fee_month']}</td>
                                                            <td>{$amount}/-</td>
                                                        </tr>";
                                                        $row_count++;
                                                    } else {
                                                        $arrears += $amount;
                                                    }
                                                    $total += $amount;
                                                }
                                                
                                                if ($arrears > 0) {
                                                    echo "<tr>
                                                        <td>Arrears</td>
                                                        <td>{$arrears}/-</td>
                                                    </tr>";
                                                    //$total += $arrears;
                                                }
                                                
                                                // Fill remaining rows
                                                for ($i = $row_count; $i < 5; $i++) {
                                                    echo "<tr><td style='height:34px;'></td><td></td></tr>";
                                                }
                                                // print_r($student_info['fee_fine']);
                                                // exit;
                                                if (!empty($student_info['fee_fine'][0]['amount'])) {
                                                    $fine = $student_info['fee_fine'][0]['amount'];
                                                    echo "<tr>
                                                        <td>Fine</td>
                                                        <td>{$fine}/-</td>
                                                    </tr>";
                                                    $total += $fine;
                                                }
                                                ?>
                                                <tr>
                                                    <td>Total Payable</td>
                                                    <td><?= $total ?>/-</td>
                                                </tr>
                                            </table>

                                            <div style="text-align:left; margin-left: 5px; font-size: 13px;">
                                                <strong>Note: </strong><?= htmlspecialchars($student_info['chalan_f_msg']) ?>
                                            </div>
                                        </div>

                                        <?php if ($show_line1): ?>
                                        <div style="float:left; width:98%; border-bottom:1px solid; margin-top:20px;">
                                            <?= htmlspecialchars($footer_line1) ?>
                                        </div>
                                        <?php endif; ?>

                                        <?php if ($show_line2): ?>
                                        <div style="float:left; width:98%; border-bottom:1px solid; margin-top:20px; margin-bottom: 20px;">
                                            <?= htmlspecialchars($footer_line2) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->
<script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/0.9.0rc1/jspdf.min.js"></script>
<?= $this->endSection() ?>