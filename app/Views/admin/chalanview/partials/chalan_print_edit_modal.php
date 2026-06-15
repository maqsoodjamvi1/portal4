<?php
/** Print preview: jQuery/Bootstrap + shared challan edit modal (CSRF meta id on page must match). */
?>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/js/bootstrap5-compat.js?v=20260614') ?>"></script>
<?= view('admin/chalanview/partials/chalan_edit_modal_shared', [
    'csrfMetaId'          => 'csrf-meta-print-chalan',
    'chalanEditAfterSave' => 'reload',
]) ?>
