<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- Bootstrap Switch CSS -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css'); ?>"> 

<div class="modal fade" id="payfee" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal fade" id="payfee" tabindex="-1" role="dialog" aria-labelledby="payFeeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="payFeeModalLabel">Pay Fee</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                
            

                <!-- Payment Form -->
                <form id="payFeeData">
                    <input type="hidden" name="chalan_id" id="ChalanID">
                    <input type="hidden" name="PaidDate" id="PaidDate">
                    <input type="hidden" name="student_id" id="studentID">
                    <input type="hidden" name="fineamount" id="fineamount">

                  

                    <!-- Fine Options (Initially Hidden) -->
                    <div class="form-group" id="feeFine" style="display:none;">
                        <label>Fine Options:</label>
                        <div class="form-check">
                            <input class="form-check-input fine" type="radio" name="fine" id="payWithFine" value="paywithfine">
                            <label class="form-check-label" for="payWithFine">Pay With Full Fine</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input fine" type="radio" name="fine" id="payWithoutFine" value="paywithoutfine">
                            <label class="form-check-label" for="payWithoutFine">Pay Without Fine</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input fine" type="radio" name="fine" id="payDiscountFine" value="paywithdiscountfine" checked>
                            <label class="form-check-label" for="payDiscountFine">Pay With Discounted Fine</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" id="payFee" class="btn btn-primary">Confirm Payment</button>
            </div>
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="partialModal" tabindex="-1" role="dialog" aria-labelledby="partialModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="partialModalLabel">Partial Payment</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="partialChalanId" name="chalan_id">
        <input type="hidden" id="partialStudentId" name="student_id">

        <div class="form-group">
          <label>Total Fee</label>
          <input type="number" id="partialTotal" name="total_fee" class="form-control" readonly>
        </div>

        <div class="form-group">
          <label>Paid Amount</label>
          <input type="number" id="partialPaid" name="paid_amount" class="form-control" min="0" step="0.01">
        </div>

        <div class="form-group">
          <label>Discount</label>
          <input type="number" id="partialDiscount" name="discount_amount" class="form-control" min="0" step="0.01">
        </div>

        <div class="form-group">
          <label>Balance</label>
          <input type="number" id="partialBalance" name="balance" class="form-control" readonly>
        </div>

        <div class="form-group text-right">
          <button type="button" class="btn btn-success btn-block" onclick="submitPartial()">
            <i class="fas fa-check-circle mr-1"></i> Submit Partial Payment
          </button>
        </div>
      </div>
    </div>
  </div>
</div>




<div class="modal fade" id="editStudentFeeModal" tabindex="-1" role="dialog" aria-labelledby="editFeeLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="editFeeLabel">Edit Student Fees</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered table-sm">
          <thead>
            <tr>
              <th>Student Name</th>
              <th>Current Fee (Rs)</th>
              <th>New Fee</th>
            </tr>
          </thead>
          <tbody id="studentFeeEditBody">
            <!-- dynamically filled -->
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
       <button class="btn btn-success btn-sm" id="saveFeeChanges">Save Changes</button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="advanceStudentFeeModal" tabindex="-1" role="dialog" aria-labelledby="advanceFeeLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="advanceFeeLabel">Advance Fee Collection</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered table-sm">
          <thead>
            <tr>
              <th>Student Name</th>
              <th>Current Dues (Rs)</th>
              <th>Advance Fee</th>
            </tr>
          </thead>
          <tbody id="advanceStudentFeeBody">
            <!-- populated by JS -->
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
        <button class="btn btn-success btn-sm" id="saveAdvanceFee">Save Advance Fee</button>
      </div>
    </div>
  </div>
</div>



</section>
<!-- /.content -->

<script type="text/javascript">

</script>

<?= $this->endSection() ?>