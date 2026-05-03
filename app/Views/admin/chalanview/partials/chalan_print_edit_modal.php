<?php
/** Print preview: jQuery/Bootstrap + shared challan edit modal (CSRF meta id on page must match). */
?>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<?= view('admin/chalanview/partials/chalan_edit_modal_shared', [
    'csrfMetaId'          => 'csrf-meta-print-chalan',
    'chalanEditAfterSave' => 'reload',
]) ?>
