<?php
/**
 * Documents SweetAlert confirm pattern — include once per page that uses inline confirms.
 * Prefer data-confirm on buttons handled by sms-form-validation.js
 */
?>
<script>
window.SmsConfirm = window.SmsConfirm || {
  ask: function (opts) {
    opts = opts || {};
    var title = opts.title || 'Are you sure?';
    var text = opts.text || '';
    var confirmText = opts.confirmText || 'Yes';
    var cancelText = opts.cancelText || 'Cancel';
    var onConfirm = opts.onConfirm || function () {};
    if (typeof swal === 'function') {
      swal({
        title: title,
        text: text,
        type: opts.type || 'warning',
        showCancelButton: true,
        confirmButtonColor: opts.confirmColor || '#d33',
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        closeOnConfirm: true
      }, function (isConfirm) {
        if (isConfirm) onConfirm();
      });
      return;
    }
    if (window.confirm(title + (text ? '\n' + text : ''))) {
      onConfirm();
    }
  }
};
</script>
