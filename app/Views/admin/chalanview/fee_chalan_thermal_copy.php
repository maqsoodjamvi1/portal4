<html dir="rtl" lang="ur">
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>" />

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
th, td {
    text-align: left;
    padding-left: 10px;
}
.chalanrows {
    border-bottom: 1px solid #000000;
    text-align: left;
    padding-left: 10px;
}
.feeinfo {
    font-size: 12px;
    border: 1px solid #000000;
    border-bottom: 0 none;
    margin: 3px;
    float: left;
    width: 98%;
    margin-bottom: 0px;
    line-height: 25px;
}
.chalancolleft {
    border-bottom: 1px solid #000000;
    width: 50%;
    float: left;
    padding-left: 10px;
    padding-right: 10px;
    text-align: left;
}
.chalancolright {
    border-bottom: 1px solid #000000;
    width: 50%;
    float: left;
    padding-left: 10px;
    text-align: left;
}
.feetable {
    margin: 3px;
    line-height: 25px;
    text-align: left;
    padding-left: 10px;
    font-size: 13px;
}
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Fee Chalan Single Copy</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Fee Chalan Single Copy</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
<div class="row">
<div class="col-lg-12">
<form action="<?= site_url('admin/fee-chalan/thermal-copy') ?>" method="get" id="user-edit-form" accept-charset="utf-8">
    <div class="row">
        <div class="col-lg-4 form-group">
            <label>Fee Month:</label>
            <input type="month" class="form-control pull-right" name="fee_month" value="<?= esc($fee_month) ?>">
        </div>

        <div class="col-lg-5 form-group">
            <label for="class"><strong>Class</strong></label><br>
            <select class="form-control" name="cls_sec_id" id="cls_sec_id">
                <option value="">All Classes</option>
                <?php foreach ($sectionsclassinfo ?? [] as $sectionvalue): ?>
                    <option value="<?= esc($sectionvalue['section_id']) ?>" <?= $cls_sec_id == $sectionvalue['section_id'] ? 'selected' : '' ?>>
                        <?= esc($sectionvalue['sectionclassname']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-lg-4 form-group">
            <label>Footer Line 1:</label>
            <input type="text" class="form-control pull-right" name="footer_line1" value="<?= esc($footer_line1) ?>">
        </div>

        <div class="col-lg-4 form-group">
            <label>Footer Line 2:</label>
            <input type="text" class="form-control pull-right" name="footer_line2" value="<?= esc($footer_line2) ?>">
        </div>

        <div class="col-lg-2 form-group">
            <label>Show Footer Line 1:</label>
            <input type="checkbox" class="form-control pull-right" name="show_line1" value="1" <?= $show_line1 == 1 ? 'checked' : '' ?>>
        </div>

        <div class="col-lg-2 form-group">
            <label>Show Footer Line 2:</label>
            <input type="checkbox" class="form-control pull-right" name="show_line2" value="1" <?= $show_line2 == 1 ? 'checked' : '' ?>>
        </div>

        <div class="col-sm-2">
            <input class="btn btn-primary" style="margin-bottom: 25px;" type="submit" value="View">
        </div>
    </div>
</form>

<div class="card card-primary card-outline card-tabs">
    <div class="card-header p-0 pt-1 border-bottom-0"></div>
    <div class="card-body">
        <div class="tab-content pagebreak table-responsive no-padding">
            <div style="margin-bottom: 20px; float: left; width: 100%;">
                <div id="printarea">
                <?php if (!empty($data)): ?>
                    <?php foreach ($data as $key => $student_info): ?>
                    <div style="width:32%; float:left; margin-left:1%;margin-bottom: 10px;">
                        <div dir="rtl" lang="ur"><?= $student_info['chalan_h_msg'] ?></div>
                        <div class="chalanwrapper">
                            <div class="row">
                                <div class="col-sm-3 ml-2 mt-2">
                                    <img style="width: 100%;" src="<?= base_url('system-logo/' . $student_info['logo']) ?>">
                                </div>
                                <div class="col-sm-8 mt-2">
                                    <?= $student_info['system_name'] ?><br />
                                    <?= $student_info['campus_name'] ?>, <?= $student_info['location'] ?>
                                </div>
                            </div>
                            <div class="ml-2 mt-2" style="text-align: left;">
                                <?= esc($student_info['bank_name'] . ', ' . $student_info['bank_address'] . ', ' . $student_info['bank_code']) ?><br />
                                <?php if (!empty($student_info['bank_acc'])): ?>
                                    Account No: <?= esc($student_info['bank_acc']) ?><br />
                                <?php endif; ?>
                            </div>
                            <div class="feeinfo">
                                <div class="chalanrows">Chalan# <?= $student_info['chalan_id'] ?> <span style="float: right; margin-right: 10px;">Family# : <?= $student_info['parent_id'] ?></span><span style="float:right;margin-right: 10px;">Reg# : <?= $student_info['reg_no'] ?></span></div>
                                <div class="chalanrows">Name: <?= $student_info['student_name'] ?></div>
                                <div class="chalanrows">Father Name: <?= $student_info['f_name'] ?></div>
                                <div class="chalancolleft"><?= $student_info['class_name'] ?></div>
                                <div class="chalancolright">Fee Month: <?= $student_info['fee_month'] ?></div>
                                <div class="chalancolleft">Issue Date: <?= $student_info['issue_date'] ?></div>
                                <div class="chalancolright">Due Date: <?= $student_info['due_date'] ?></div>
                            </div>
                            <table width="98%" border="1" class="feetable">
                                <tr><th>Particulars</th><th>Amount</th></tr>
                                <?php
                                $total = $arialSum = 0;
                                $nCount = 0;
                                foreach ($student_info['student_fee'] as $fee_info):
                                    $net = $fee_info['amount'] - $fee_info['discount'];
                                    $total += $net;
                                    if ($nCount < 5): ?>
                                        <tr>
                                            <td><?= $fee_info['fee_name'] ?> (<?= $fee_info['fee_month'] ?>)</td>
                                            <td><?= $net ?>/-</td>
                                        </tr>
                                    <?php else:
                                        $arialSum += $net;
                                    endif;
                                    if ($fee_info['is_monthly_fee'] == 1) $total -= $fee_info['discount'];
                                    $nCount++;
                                endforeach;

                                if ($arialSum > 0): ?>
                                    <tr><td>Arrears</td><td><?= $arialSum ?>/-</td></tr>
                                <?php endif;

                                for ($i = 1; $i <= max(0, 6 - $nCount); $i++): ?>
                                    <tr><td style="height: 34px;"></td><td></td></tr>
                                <?php endfor;

                                foreach ($student_info['fee_fine'] as $fine):
                                    $total += $fine['fine_amount']; ?>
                                    <tr><td>Fine (<?= $fine['fee_month'] ?>)</td><td><?= $fine['fine_amount'] ?>/-</td></tr>
                                <?php endforeach; ?>
                                <tr><td>Total Payable</td><td><?= $total ?>/-</td></tr>
                            </table>
                            <br>
                            <div style="text-align:left;margin-left: 5px;"><?= $student_info['chalan_f_msg'] ?></div>
                        </div>

                        <?php if ($show_line1 == 1): ?>
                            <div style="float:left;width:98%; border-bottom:1px solid;margin-top:20px;"><?= $footer_line1 ?></div>
                        <?php endif; ?>
                        <?php if ($show_line2 == 1): ?>
                            <div style="float:left;width:98%; border-bottom:1px solid;margin-top:20px;margin-bottom: 20px;"><?= $footer_line2 ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/0.9.0rc1/jspdf.min.js"></script>
<?= $this->endSection() ?>
