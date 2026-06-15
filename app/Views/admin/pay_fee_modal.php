<div class="modal fade" id="payfee" tabindex="-1" role="dialog" aria-labelledby="payFeeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="payFeeModalLabel">Pay Fee</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="payFee" class="btn btn-primary">Confirm Payment</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="partialModal" tabindex="-1" role="dialog" aria-labelledby="partialModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="partialModalLabel">Partial Payment</h5>
        <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
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

        <div class="form-group text-end">
          <button type="button" class="btn btn-success w-100" onclick="submitPartial()">
            <i class="fas fa-check-circle me-1"></i> Submit Partial Payment
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
