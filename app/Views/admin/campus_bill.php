<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style type="text/css">
  #invoice{
    padding: 20px;
}

.invoice {
    position: relative;
    background-color: #FFF;
    min-height: 670px;
    padding: 15px
}

.invoice header {
    padding: 10px 0;
    margin-bottom: 20px;
    border-bottom: 1px solid #3989c6
}

.invoice .company-details {
    text-align: right
}

.invoice .company-details .name {
    margin-top: 0;
    margin-bottom: 0
}

.invoice .contacts {
    margin-bottom: 20px
}

.invoice .invoice-to {
    text-align: left
}

.invoice .invoice-to .to {
    margin-top: 0;
    margin-bottom: 0
}

.invoice .invoice-details {
    text-align: right
}

.invoice .invoice-details .invoice-id {
    margin-top: 0;
    color: #3989c6
}

.invoice main {
    padding-bottom: 40px
}

.invoice main .thanks {
/*    margin-top: -100px;*/
    font-size: 2em;
    margin-bottom: 50px
}

.invoice main .notices {
    padding-left: 6px;
    border-start: 6px solid #3989c6
}

.invoice main .notices .notice {
    font-size: 1.2em
}

.invoice table {
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    margin-bottom: 20px
}

.invoice table td,.invoice table th {
    padding: 15px;
    background: #eee;
    border-bottom: 1px solid #fff
}

.invoice table th {
    white-space: nowrap;
    font-weight: 400;
    font-size: 16px
}

.invoice table td h3 {
    margin: 0;
    font-weight: 400;
    color: #3989c6;
    font-size: 1.2em
}

.invoice table .qty,.invoice table .total,.invoice table .unit {
    text-align: right;
    font-size: 1.2em
}

.invoice table .no {
    color: #fff;
    font-size: 1.6em;
    background: #3989c6
}

.invoice table .unit {
    background: #ddd
}

.invoice table .total {
    background: #3989c6;
    color: #fff
}

.invoice table tbody tr:last-child td {
    border: none
}

.invoice table tfoot td {
    background: 0 0;
    border-bottom: none;
    white-space: nowrap;
    text-align: right;
    padding: 10px 20px;
    font-size: 1.2em;
    border-top: 1px solid #aaa
}

.invoice table tfoot tr:first-child td {
    border-top: none
}

.invoice table tfoot tr:last-child td {
    color: #3989c6;
    font-size: 1.4em;
    border-top: 1px solid #3989c6
}

.invoice table tfoot tr td:first-child {
    border: none
}

.invoice footer {
    width: 100%;
    text-align: center;
    color: #777;
    border-top: 1px solid #aaa;
    padding: 8px 0
}

.ribbon {
    width: 150px;
    height: 150px;
    overflow: hidden;
    position: absolute
}

.ribbon::before,
.ribbon::after {
    position: absolute;
    z-index: -1;
    content: '';
    display: block;
    border: 5px solid #2980b9
}

.ribbon span {
    position: absolute;
    display: block;
    width: 225px;
    padding: 8px 0;
    background-color: #3498db;
    box-shadow: 0 5px 10px rgba(0, 0, 0, .1);
    color: #fff;
    font: 100 18px/1 'Lato', sans-serif;
    text-shadow: 0 1px 1px rgba(0, 0, 0, .2);
    text-transform: uppercase;
    text-align: center
}

.ribbon-top-right {
    top: -10px;
    right: -10px
}

.ribbon-top-right::before,
.ribbon-top-right::after {
    border-top-color: transparent;
    border-end-color: transparent
}

.ribbon-top-right::before {
    top: 0;
    left: 17px
}

.ribbon-top-right::after {
    bottom: 17px;
    right: 0
}

.ribbon-top-right span {
    left: -25px;
    top: 30px;
    transform: rotate(45deg)
}

@media print {

    .ribbon {
    width: 150px;
    height: 150px;
    overflow: hidden;
    position: absolute
}

.ribbon::before,
.ribbon::after {
    position: absolute;
    z-index: -1;
    content: '';
    display: block;
    border: 5px solid #2980b9
}

.ribbon span {
    position: absolute;
    display: block;
    width: 225px;
    padding: 8px 0;
    background-color: #3498db;
    box-shadow: 0 5px 10px rgba(0, 0, 0, .1);
    color: #fff;
    font: 100 18px/1 'Lato', sans-serif;
    text-shadow: 0 1px 1px rgba(0, 0, 0, .2);
    text-transform: uppercase;
    text-align: center
}

.ribbon-top-right {
    top: -10px;
    right: -10px
}

.ribbon-top-right::before,
.ribbon-top-right::after {
    border-top-color: transparent;
    border-end-color: transparent
}

.ribbon-top-right::before {
    top: 0;
    left: 17px
}

.ribbon-top-right::after {
    bottom: 17px;
    right: 0
}

.ribbon-top-right span {
    left: -25px;
    top: 30px;
    transform: rotate(45deg)
}

#invoice{
    padding: 20px;
}

.invoice {
    position: relative;
    background-color: #FFF;
    min-height: 670px;
    padding: 15px
}

.invoice header {
    padding: 10px 0;
    margin-bottom: 20px;
    border-bottom: 1px solid #3989c6
}

.invoice .company-details {
    text-align: right
}

.invoice .company-details .name {
    margin-top: 0;
    margin-bottom: 0
}

.invoice .contacts {
    margin-bottom: 20px
}

.invoice .invoice-to {
    text-align: left
}

.invoice .invoice-to .to {
    margin-top: 0;
    margin-bottom: 0
}

.invoice .invoice-details {
    text-align: right
}

.invoice .invoice-details .invoice-id {
    margin-top: 0;
    color: #3989c6
}

.invoice main {
    padding-bottom: 40px
}

.invoice main .thanks {
    margin-top: -100px;
    font-size: 2em;
    margin-bottom: 50px
}

.invoice main .notices {
    padding-left: 6px;
    border-start: 6px solid #3989c6
}

.invoice main .notices .notice {
    font-size: 1.2em
}

.invoice table {
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    margin-bottom: 20px
}

.invoice table td,.invoice table th {
    padding: 15px;
    background: #eee;
    border-bottom: 1px solid #fff
}

.invoice table th {
    white-space: nowrap;
    font-weight: 400;
    font-size: 16px
}

.invoice table td h3 {
    margin: 0;
    font-weight: 400;
    color: #3989c6;
    font-size: 1.2em
}

.invoice table .qty,.invoice table .total,.invoice table .unit {
    text-align: right;
    font-size: 1.2em
}

.invoice table .no {
    color: #fff;
    font-size: 1.6em;
    background: #3989c6
}

.invoice table .unit {
    background: #ddd
}

.invoice table .total {
    background: #3989c6;
    color: #fff
}

.invoice table tbody tr:last-child td {
    border: none
}

.invoice table tfoot td {
    background: 0 0;
    border-bottom: none;
    white-space: nowrap;
    text-align: right;
    padding: 10px 20px;
    font-size: 1.2em;
    border-top: 1px solid #aaa
}

.invoice table tfoot tr:first-child td {
    border-top: none
}

.invoice table tfoot tr:last-child td {
    color: #3989c6;
    font-size: 1.4em;
    border-top: 1px solid #3989c6
}

.invoice table tfoot tr td:first-child {
    border: none
}

.invoice footer {
    width: 100%;
    text-align: center;
    color: #777;
    border-top: 1px solid #aaa;
    padding: 8px 0
}
.invoice table td, .invoice table th {
    padding: 15px;
    background: #eee !important;
    border-bottom: 1px solid #fff;
}

.invoice table .no {
    color: #fff;
    font-size: 1.6em;
    background: #3989c6 !important;
}
    .bg-warning{display: none;}
    .hidden-print{display: none;}
    .invoice {
        font-size: 11px!important;
        overflow: hidden!important
    }

    .invoice footer {
        position: absolute;
        bottom: 10px;
        page-break-after: always
    }

    .invoice>div:last-child {
        /*page-break-before: always*/
    }
}
</style>
<script type="text/javascript">
   $('#printInvoice').click(function(){
            Popup($('.invoice')[0].outerHTML);
            function Popup(data) 
            {
                window.print();
                return true;
            }
        });
</script>
<?php 
$billingAmount =  $campusbillinfo->bill_amount;
  //$billingAmount = round(($systemPlaninfo->factor*$max_student_feeinfo->max_fee*$number_of_students->no_of_students)/100)*$installmentPlaninfo->month_count;
  //$discountAmount =  (round(100-($installmentPlaninfo->discount_factor*10000))*round(($systemPlaninfo->factor*$max_student_feeinfo->max_fee*$number_of_students->no_of_students)/100)*$installmentPlaninfo->month_count)/100;
?>
<div id="invoice">
    <div class="toolbar hidden-print">
        <div class="text-end">
            <button id="printInvoice" class="btn btn-info"><i class="fa fa-print"></i> Print</button>
            <!-- <button class="btn btn-info"><i class="fa fa-file-pdf-o"></i> Export as PDF</button> -->
        </div>
        <hr>
    </div>
    <div class="invoice">
        <div style="min-width: 600px">
            <div class="ribbon ribbon-top-right"><span><?php echo $campusbillinfo->bill_status; ?></span></div>
            <header>
                <div class="row">
                    <div class="col">
                        <a target="_blank" href="https://timesoftsol.com">
                          <img style="width: 340px;" src="https://timesoftsol.com/wp-content/uploads/2020/12/Time-soft-sol-logo-12-12-1.png" data-holder-rendered="true" />
                        </a>
                    </div>
                     
                    <div class="col company-details">
                        <div>Office # 8 Syed Gul Road, Shakrial Rawalpinid</div>
                        <div>+92-300-5340592</div>
                        <div>info@timesoftsol.com</div>
                    </div>
                    <div class="col col-lg-2"></div>
                </div>
            </header>
            <main>
                <div class="row contacts">
                    <div class="col invoice-to">
                        <div class="text-gray-light">INVOICE TO:</div>
                        <h2 class="to"><?php echo $campusinfo->campus_name; ?></h2>
                        <div class="address"><?php echo $campusinfo->location; ?></div>
                        <div class="email"><?php echo $campusinfo->mobile_no; ?></div>
                    </div>
                    <div class="col invoice-details"> 
                        <h1 class="invoice-id">INVOICE <?php echo $campusinfo->campus_id."-".$campusbillinfo->bill_id; ?>
                        </h1>
                        <div class="date">Date of Invoice: <?php echo date('d/m/Y'); ?></div>
                        <div class="date">Due Date: <?php echo date('d/m/Y',strtotime($campusbillinfo->campus_expiry)); ?></div>
                    </div>
                </div>
                <table border="0" cellspacing="0" cellpadding="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th class="text-start">DESCRIPTION</th>
                            <th class="text-end">MONTH PRICE</th>
                            <th class="text-end">MONTHS</th>
                            <th class="text-end">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                          <td class="no">01</td>
                            <td class="text-start"><h3>TLive Education Web Portal </h3>
                              Students Limit: <?php echo $campusbillinfo->max_students; ?>, Maximum Fee Limit: 
                              <?php echo $campusbillinfo->max_fee; ?>
                            </td>
                            <td class="unit">
                              Rs.<?php echo round(($campusbillinfo->bill_amount)); ?></td>
                            <td class="qty"><?php echo $campusbillinfo->install_id; ?></td>
                            <td class="total">Rs.<?php echo round($campusbillinfo->bill_amount); ?></td>
                        </tr>
                    </tbody>
                    <tfoot>
                       <!--  <tr>
                            <td colspan="2"></td>
                            <td colspan="2">SUBTOTAL</td>
                            <td>Rs.<?php //echo $billingAmount; ?></td>
                        </tr> -->
                        <!-- <tr>
                            <td colspan="2"></td>
                            <td colspan="2">Discount <?php //echo round(100-($installmentPlaninfo->discount_factor*10000)); ?>%</td>
                            <td>Rs.<?php //echo $discountAmount; ?></td>
                        </tr> -->
                        <tr>
                            <td colspan="2"></td>
                            <td colspan="2">TOTAL </td>
                            <td>Rs.<?php echo ($campusbillinfo->bill_amount); ?></td>
                        </tr>
                    </tfoot>
                </table>
                <div class="text-center"><h4>Paid At: <?php 
                $date=date_create($campusbillinfo->paid_date);
                echo date_format($date,"d/m/Y");
                //echo $campusbillinfo->paid_date; ?></h4></div>
                <div class="thanks">Thank you!</div>
                <div class="notices">
                    <div>NOTICE:</div>
                    <div class="notice"><ol>
                        <li>All charges are exclusive Tax.</li><li>
You can not edit student limit or fee limit during billing period.</li><li>
Amount refund cannot be claimed after 30 days of paid date. </li><li>
After payment bill invoice must be mail. And your system will be activated in 24 hours after sending chalan.</li></ol></div>
                </div>
            </main>
            <footer>
                Invoice was created on a computer and is valid without the signature and seal.
            </footer>
        </div>
        <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
        <div></div>
    </div>
</div>

<?= $this->endSection() ?>