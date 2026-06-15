<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
use App\Libraries\QbBoardPublisherService;
?>

<?= view('components/page_header', [
    'title' => 'Boards / Publisher',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Boards / Publisher', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
      <h3 class="card-title mb-0">Boards / Publisher list</h3>
      <button type="button" class="btn btn-primary btn-sm" id="btnAddBoard">
        <i class="fa fa-plus"></i> Add entry
      </button>
    </div>
    <div class="card-body">
      <p class="text-muted small mb-3">
        Global exam boards and publishers (<code>system_id = 0</code>) shared by all schools and the board prep portal.
        Assign them to QB Topics when editing topics. Upload a logo for each board.
      </p>
      <div class="table-responsive">
        <table class="table table-bordered table-sm" id="boardTable">
          <thead class="table-light">
            <tr>
              <th style="width:50px;">#</th>
              <th style="width:70px;">Logo</th>
              <th>Name</th>
              <th style="width:120px;">Short code</th>
              <th style="width:90px;">Order</th>
              <th style="width:90px;">Status</th>
              <th style="width:140px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $i => $row):
              $logoUrl = QbBoardPublisherService::logoUrl($row['logo'] ?? null);
            ?>
              <tr data-id="<?= (int) $row['id'] ?>">
                <td><?= $i + 1 ?></td>
                <td class="text-center">
                  <?php if ($logoUrl) : ?>
                    <img src="<?= esc($logoUrl, 'attr') ?>" alt="" class="board-logo-thumb" style="max-width:48px;max-height:48px;object-fit:contain;">
                  <?php else : ?>
                    <span class="text-muted small">—</span>
                  <?php endif; ?>
                </td>
                <td><?= esc($row['name']) ?></td>
                <td><?= esc($row['short_code'] ?? '') ?></td>
                <td><?= (int) ($row['sort_order'] ?? 0) ?></td>
                <td><?= (int) ($row['status'] ?? 0) === 1 ? 'Active' : 'Inactive' ?></td>
                <td>
                  <button type="button" class="btn btn-sm btn-outline-primary btn-edit-board"
                    data-id="<?= (int) $row['id'] ?>"
                    data-name="<?= esc($row['name'], 'attr') ?>"
                    data-short="<?= esc($row['short_code'] ?? '', 'attr') ?>"
                    data-order="<?= (int) ($row['sort_order'] ?? 0) ?>"
                    data-status="<?= (int) ($row['status'] ?? 0) ?>"
                    data-logo="<?= esc($logoUrl ?? '', 'attr') ?>">Edit</button>
                  <button type="button" class="btn btn-sm btn-outline-danger btn-del-board" data-id="<?= (int) $row['id'] ?>">Delete</button>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if ($rows === []) : ?>
              <tr id="boardEmptyRow"><td colspan="7" class="text-muted text-center">No entries yet. Click Add entry.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<div class="modal fade" id="boardModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="boardForm" enctype="multipart/form-data">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="boardModalTitle">Add Boards / Publisher</h5>
          <button type="button" class="close" data-bs-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="board_id" name="id" value="0">
          <input type="hidden" id="board_remove_logo" name="remove_logo" value="0">
          <div class="form-group text-center">
            <div id="boardLogoPreviewWrap" class="mb-2 d-none">
              <img id="boardLogoPreview" src="" alt="Board logo" style="max-width:120px;max-height:80px;object-fit:contain;" class="border rounded p-1 bg-white">
            </div>
            <label class="btn btn-sm btn-outline-secondary mb-0">
              <i class="fa fa-image me-1"></i> Upload logo
              <input type="file" name="logo" id="board_logo" accept="image/jpeg,image/png,image/webp" class="d-none">
            </label>
            <button type="button" class="btn btn-sm btn-outline-danger d-none" id="btnRemoveBoardLogo">Remove logo</button>
            <small class="d-block text-muted mt-1">JPG, PNG or WebP — max 2 MB</small>
          </div>
          <div class="form-group">
            <label>Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="board_name" name="name" placeholder="e.g. FBISE, Rawalpindi Board">
          </div>
          <div class="form-group">
            <label>Short code</label>
            <input type="text" class="form-control" id="board_short_code" name="short_code" placeholder="e.g. FBISE">
          </div>
          <div class="row">
            <div class="form-group col-md-6">
              <label>Sort order</label>
              <input type="number" class="form-control" id="board_sort_order" name="sort_order" value="0">
            </div>
            <div class="form-group col-md-6">
              <label>Status</label>
              <select class="form-control" id="board_status" name="status">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="btnSaveBoard">Save</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
(function () {
  const saveUrl = <?= json_encode(base_url('admin/qb-board-publishers/save')) ?>;
  const deleteUrl = <?= json_encode(base_url('admin/qb-board-publishers/delete')) ?>;
  const csrfName = <?= json_encode(csrf_token()) ?>;
  let csrfHash = <?= json_encode(csrf_hash()) ?>;

  function setLogoPreview(url) {
    const $wrap = $('#boardLogoPreviewWrap');
    const $btnRemove = $('#btnRemoveBoardLogo');
    if (url) {
      $('#boardLogoPreview').attr('src', url);
      $wrap.removeClass('d-none');
      $btnRemove.removeClass('d-none');
    } else {
      $('#boardLogoPreview').attr('src', '');
      $wrap.addClass('d-none');
      $btnRemove.addClass('d-none');
    }
  }

  function openModal(row) {
    $('#board_id').val(row ? row.id : 0);
    $('#board_name').val(row ? row.name : '');
    $('#board_short_code').val(row ? row.short_code : '');
    $('#board_sort_order').val(row ? row.sort_order : 0);
    $('#board_status').val(row ? String(row.status) : '1');
    $('#board_remove_logo').val('0');
    $('#board_logo').val('');
    setLogoPreview(row && row.logo ? row.logo : null);
    $('#boardModalTitle').text(row ? 'Edit Boards / Publisher' : 'Add Boards / Publisher');
    $('#boardModal').modal('show');
  }

  $('#btnAddBoard').on('click', function () { openModal(null); });

  $(document).on('click', '.btn-edit-board', function () {
    openModal({
      id: $(this).data('id'),
      name: $(this).data('name'),
      short_code: $(this).data('short'),
      sort_order: $(this).data('order'),
      status: $(this).data('status'),
      logo: $(this).data('logo') || '',
    });
  });

  $('#board_logo').on('change', function () {
    const file = this.files && this.files[0];
    if (!file) return;
    $('#board_remove_logo').val('0');
    const reader = new FileReader();
    reader.onload = function (e) {
      setLogoPreview(e.target.result);
    };
    reader.readAsDataURL(file);
  });

  $('#btnRemoveBoardLogo').on('click', function () {
    $('#board_logo').val('');
    $('#board_remove_logo').val('1');
    setLogoPreview(null);
  });

  $('#boardForm').on('submit', function (e) {
    e.preventDefault();
    if (!$('#board_name').val().trim()) {
      alert('Name is required.');
      return;
    }

    const formData = new FormData(this);
    formData.set('id', parseInt($('#board_id').val(), 10) || 0);
    formData.set('sort_order', parseInt($('#board_sort_order').val(), 10) || 0);
    formData.set('status', parseInt($('#board_status').val(), 10) || 0);
    formData.set(csrfName, csrfHash);

    $.ajax({
      url: saveUrl,
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    }).done(function (res) {
      if (res && res.csrf_hash) csrfHash = res.csrf_hash;
      if (!res || res.status !== 'ok') {
        alert((res && res.msg) || 'Save failed.');
        return;
      }
      location.reload();
    }).fail(function () {
      alert('Save failed.');
    });
  });

  $(document).on('click', '.btn-del-board', function () {
    const id = $(this).data('id');
    if (!id || !confirm('Delete this entry? Topics will be unlinked.')) return;
    const data = {};
    data[csrfName] = csrfHash;
    data.id = id;
    $.post(deleteUrl + '/' + id, data).done(function (res) {
      if (res && res.csrf_hash) csrfHash = res.csrf_hash;
      if (!res || res.status !== 'ok') {
        alert((res && res.msg) || 'Delete failed.');
        return;
      }
      location.reload();
    }).fail(function () {
      alert('Delete failed.');
    });
  });
})();
</script>

<?= $this->endSection() ?>
