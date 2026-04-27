<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- Single Fee Payment Modal -->
<div class="modal fade" id="paySingleModal" tabindex="-1" role="dialog" aria-labelledby="payModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="payModalTitle">Pay Fee</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="chalanIdField">
        <input type="hidden" id="studentIdField">

        <div class="form-group">
          <label for="payAmountField">Amount</label>
          <input type="text" id="payAmountField" class="form-control" readonly>
        </div>

        <div class="form-group">
          <label for="paidDateField">Date Paid</label>
          <input type="text" id="paidDateField" class="form-control" value="<?= date('Y-m-d') ?>" readonly>
        </div>

        <div class="form-group text-right">
          <button type="button" id="btnPaySingle" class="btn btn-success btn-block">
            <i class="fas fa-check-circle mr-1"></i> Submit Payment
          </button>
        </div>
      </div>
    </div>
  </div>
</div>


<?= $this->endSection() ?>