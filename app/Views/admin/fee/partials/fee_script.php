<script>
$(document).ready(function () {
  $('#selectAllFees').on('click', function () {
    $('.selectFee').prop('checked', this.checked);
  });

  $('.open_advance_modal').on('click', function () {
    $('#advanceModal').modal('show');
    // Load modal content via AJAX if needed
  });

  $('.open_discount_modal').on('click', function () {
    $('#discountModal').modal('show');
  });

  $('.pay_fee').on('click', function () {
    const feeId = $(this).data('id');
    $('#payFeeModal').modal('show');
    // Load payment form using feeId
  });
});
</script>
