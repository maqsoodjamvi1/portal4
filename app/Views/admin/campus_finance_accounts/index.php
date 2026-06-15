<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Finance Accounts',
    'icon' => 'fas fa-wallet',
    'subtitle' => 'Easypaisa, JazzCash, bank, cash — used for fee collection and expenses',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Finance Accounts', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="container-fluid">

    <?php if (empty($tables_ready)): ?>
      <div class="alert alert-warning">
        Finance tables are not installed yet. Refresh this page after setup runs, or run: <code>php spark migrate</code>
        <?php if (! empty($setup_messages)): ?>
          <ul class="mb-0 mt-2 small">
            <?php foreach ($setup_messages as $msg): ?>
              <li><?= esc($msg) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <?php if (! empty($setup_messages)): ?>
        <div class="alert alert-info alert-dismissible fade show">
          <?php foreach ($setup_messages as $msg): ?>
            <div><?= esc($msg) ?></div>
          <?php endforeach; ?>
          <button type="button" class="close" data-bs-dismiss="alert"><span>&times;</span></button>
        </div>
      <?php endif; ?>

    <div class="row">
      <div class="col-lg-4">
        <div class="card sms-card card-outline card-primary">
          <div class="card-header"><h3 class="card-title">Settings</h3></div>
          <div class="card-body">
            <div class="form-check form-switch mb-3">
              <input type="checkbox" class="form-check-input" id="enablePettyCash"
                <?= ((int)($settings->enable_user_petty_cash ?? 0) === 1) ? 'checked' : '' ?>>
              <label class="form-check-label" for="enablePettyCash">Enable per-user petty cash</label>
            </div>
            <p class="small text-muted">When enabled, fee pay defaults to the logged-in user's petty cash account instead of campus cash.</p>
            <button type="button" class="btn btn-primary btn-sm" id="btnSaveSettings">Save settings</button>
          </div>
        </div>

        <div class="card card-outline card-success">
          <div class="card-header"><h3 class="card-title">Add / Edit Account</h3></div>
          <div class="card-body">
            <form id="accountForm">
              <input type="hidden" name="account_id" id="account_id" value="0">
              <div class="form-group">
                <label>Type</label>
                <select name="account_type" id="account_type" class="form-control">
                  <?php foreach ($account_types as $t): ?>
                    <option value="<?= esc($t) ?>"><?= esc(ucfirst($t)) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label>Name</label>
                <input type="text" name="account_name" id="account_name" class="form-control" required placeholder="e.g. HBL Main Branch">
              </div>
              <div class="form-group">
                <label>Account / mobile number</label>
                <input type="text" name="account_number" id="account_number" class="form-control" placeholder="Optional">
              </div>
              <div class="form-group">
                <label>Opening balance</label>
                <input type="number" step="0.01" name="opening_balance" id="opening_balance" class="form-control" value="0">
              </div>
              <div class="form-check form-check mb-3">
                <input type="checkbox" class="form-check-input" id="is_active" checked>
                <label class="form-check-label" for="is_active">Active</label>
              </div>
              <button type="submit" class="btn btn-success btn-sm">Save account</button>
              <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetForm">Clear</button>
            </form>
          </div>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Campus accounts</h3>
            <button type="button" class="btn btn-outline-primary btn-sm" id="btnRefreshBalances"><i class="fas fa-sync-alt"></i> Refresh balances</button>
          </div>
          <div class="card-body p-0">
            <table class="table table-striped mb-0" id="accountsTable">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Type</th>
                  <th>Number</th>
                  <th class="text-end">Opening</th>
                  <th class="text-end">Balance</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php
                $financeSvc = new \App\Libraries\CampusFinanceService();
                foreach ($accounts as $acc):
                  $bal = $financeSvc->getAccountBalance((int)$acc->account_id);
                  $isCampusCash = (int)($acc->is_campus_cash ?? 0) === 1;
                ?>
                <tr data-account='<?= esc(json_encode([
                  'account_id' => (int)$acc->account_id,
                  'account_type' => $acc->account_type,
                  'account_name' => $acc->account_name,
                  'account_number' => $acc->account_number ?? '',
                  'opening_balance' => (float)$acc->opening_balance,
                  'is_active' => (int)$acc->is_active,
                  'is_campus_cash' => $isCampusCash,
                ]), 'attr') ?>'>
                  <td>
                    <?= esc($acc->account_name) ?>
                    <?php if ($isCampusCash): ?><span class="badge text-bg-info">Campus Cash</span><?php endif; ?>
                  </td>
                  <td><?= esc(ucfirst($acc->account_type)) ?></td>
                  <td><?= esc($acc->account_number ?? '—') ?></td>
                  <td class="text-end"><?= number_format((float)$acc->opening_balance, 2) ?></td>
                  <td class="text-end balance-cell" data-id="<?= (int)$acc->account_id ?>"><?= number_format($bal, 2) ?></td>
                  <td><?= (int)$acc->is_active === 1 ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Inactive</span>' ?></td>
                  <td>
                    <?php if (! $isCampusCash): ?>
                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit">Edit</button>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($accounts)): ?>
                <tr><td colspan="7" class="text-center text-muted">No accounts yet. Campus Cash will be created automatically.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <?php endif; ?>
  </div>
</section>

<script>
$(function () {
  $('#btnSaveSettings').on('click', function () {
    $.post('<?= base_url('admin/campus-finance-accounts/save-settings') ?>', {
      enable_user_petty_cash: $('#enablePettyCash').is(':checked') ? 1 : 0
    }, function (r) {
      if (r.success) toastr.success(r.msg); else toastr.error(r.msg || 'Failed');
    }, 'json');
  });

  $('#accountForm').on('submit', function (e) {
    e.preventDefault();
    $.post('<?= base_url('admin/campus-finance-accounts/save-account') ?>', {
      account_id: $('#account_id').val(),
      account_type: $('#account_type').val(),
      account_name: $('#account_name').val(),
      account_number: $('#account_number').val(),
      opening_balance: $('#opening_balance').val(),
      is_active: $('#is_active').is(':checked') ? 1 : 0
    }, function (r) {
      if (r.success) { toastr.success(r.msg); location.reload(); }
      else toastr.error(r.msg || 'Failed');
    }, 'json');
  });

  $('#btnResetForm').on('click', function () {
    $('#account_id').val(0);
    $('#accountForm')[0].reset();
    $('#is_active').prop('checked', true);
    $('#account_type').prop('disabled', false);
  });

  $(document).on('click', '.btn-edit', function () {
    var d = $(this).closest('tr').data('account');
    if (!d) return;
    $('#account_id').val(d.account_id);
    $('#account_type').val(d.account_type);
    $('#account_name').val(d.account_name);
    $('#account_number').val(d.account_number);
    $('#opening_balance').val(d.opening_balance);
    $('#is_active').prop('checked', d.is_active === 1);
    if (d.is_campus_cash) $('#account_type').prop('disabled', true);
    else $('#account_type').prop('disabled', false);
    $('html, body').animate({ scrollTop: $('#accountForm').offset().top - 80 }, 300);
  });

  $('#btnRefreshBalances').on('click', function () {
    $.get('<?= base_url('admin/campus-finance-accounts/balances') ?>', function (r) {
      if (!r.success) return;
      r.accounts.forEach(function (a) {
        $('.balance-cell[data-id="' + a.account_id + '"]').text(Number(a.balance).toLocaleString(undefined, {minimumFractionDigits: 2}));
      });
      toastr.success('Balances updated');
    }, 'json');
  });
});
</script>

<?= $this->endSection() ?>
