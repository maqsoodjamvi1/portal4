<?php if (empty($rows)): ?>
  <div class="alert alert-info mb-0">No unpaid fee chalans found.</div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-sm table-striped mb-0">
      <thead class="thead-light">
        <tr>
          <th>#</th>
          <th>Month</th>
          <th>Amount</th>
          <th>Discount</th>
          <th>Balance</th>
          <th>Status</th>
          <th>Created</th>
          <th class="text-right">Action</th>
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
              <span class="badge badge-warning">Unpaid</span>
            <?php else: ?>
              <span class="badge badge-success">Paid</span>
            <?php endif; ?>
          </td>
          <td><?= esc(date('d-M-Y', strtotime($r['created_at'] ?? 'now'))) ?></td>
          <td class="text-right">
            <button type="button"
              class="btn btn-primary btn-xs"
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
