<?php if (empty($rows)): ?>
  <div class="alert alert-info mb-0">No unpaid fee chalans found.</div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-sm table-striped mb-0">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Month</th>
          <th>Amount</th>
          <th>Discount</th>
          <th>Balance</th>
          <th>Status</th>
          <th>Created</th>
          <th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $i => $r): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><?= esc($r['month']) ?></td>
          <td><?= number_format((float)$r['amount']) ?></td>
          <td><?= number_format((float)$r['discount']) ?></td>
          <td><?= number_format((float)$r['balance']) ?></td>
          <td>
            <?php if ((int)$r['status'] === 0): ?>
              <span class="badge text-bg-warning">Unpaid</span>
            <?php else: ?>
              <span class="badge text-bg-success">Paid</span>
            <?php endif; ?>
          </td>
          <td><?= esc(date('d-M-Y', strtotime($r['created_at'] ?? 'now'))) ?></td>
          <td class="text-end">
            <button type="button"
              class="btn btn-primary btn-sm"
              onclick="openPayModal(<?= (int)$r['id'] ?>)">
              Pay
            </button>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
