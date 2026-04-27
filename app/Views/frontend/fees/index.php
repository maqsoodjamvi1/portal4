<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>
<h4 class="mb-3">Fees</h4>
<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="border rounded p-3"><div class="small text-muted">Total</div><div class="h5 mb-0"><?= number_to_currency($summary['total'] ?? 0,'PKR') ?></div></div></div>
  <div class="col-md-4"><div class="border rounded p-3"><div class="small text-muted">Paid</div><div class="h5 mb-0"><?= number_to_currency($summary['paid'] ?? 0,'PKR') ?></div></div></div>
  <div class="col-md-4"><div class="border rounded p-3"><div class="small text-muted">Balance</div><div class="h5 mb-0"><?= number_to_currency($summary['balance'] ?? 0,'PKR') ?></div></div></div>
</div>
<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead class="table-light"><tr><th>#</th><th>Month</th><th>Due Date</th><th>Amount</th><th>Discount</th><th>Paid</th><th>Status</th><th>Created</th></tr></thead>
    <tbody>
    <?php if (empty($fees)): ?>
      <tr><td colspan="8" class="text-center text-muted">No fee records found.</td></tr>
    <?php else: foreach ($fees as $i=>$r): ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td><?= esc($r['month']??'-') ?></td>
        <td><?= esc($r['due_date']??'-') ?></td>
        <td><?= number_to_currency($r['amount']??0,'PKR') ?></td>
        <td><?= number_to_currency($r['discount']??0,'PKR') ?></td>
        <td><?= number_to_currency($r['paid_amount']??0,'PKR') ?></td>
        <td><span class="badge bg-<?= ($r['status']??'')==='paid'?'success':(($r['status']??'')==='unpaid'?'warning text-dark':'secondary') ?>"><?= esc(ucfirst($r['status']??'N/A')) ?></span></td>
        <td><?= esc($r['created_at']??'-') ?></td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
<?= $this->endSection() ?>
