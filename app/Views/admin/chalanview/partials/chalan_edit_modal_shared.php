<?php
/**
 * Shared modal + JS for editing unpaid challan lines (FeeChalan::getEditForm / saveEdit).
 *
 * @var string $csrfMetaId        DOM id of <meta name="csrf..." content="...">
 * @var string $chalanEditAfterSave 'reload' | 'refresh_pay_card'
 */
$csrfMetaId          = $csrfMetaId ?? 'csrf-meta-chalan-edit';
$chalanEditAfterSave = $chalanEditAfterSave ?? 'reload';
$getEditUrl          = base_url('admin/fee-chalan/get-edit-form');
$saveUrl             = base_url('admin/fee-chalan/save-edit');
?>
<div class="modal fade" id="chalanEditModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header py-2 bg-light">
                <h5 class="modal-title mb-0">Edit challan lines (unpaid)</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-2" id="chalanEditModalBody">
                <p class="text-muted mb-0">Loading…</p>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="chalanEditSaveBtn">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var getEditUrl = <?= json_encode($getEditUrl) ?>;
    var saveUrl = <?= json_encode($saveUrl) ?>;
    var csrfMetaId = <?= json_encode($csrfMetaId) ?>;
    var afterSave = <?= json_encode($chalanEditAfterSave) ?>;

    function getCsrfPair() {
        var meta = document.getElementById(csrfMetaId);
        if (!meta) return null;
        return { name: meta.getAttribute('name'), value: meta.getAttribute('content') };
    }

    function appendCsrf(fd) {
        var p = getCsrfPair();
        if (p && p.name) fd.append(p.name, p.value);
    }

    function getChalanEditForm() {
        return $('#chalanEditModal').find('#chalan-edit-form');
    }

    function chalanEditRecalc() {
        var $form = getChalanEditForm();
        if (!$form.length) return;

        function visibleRows() {
            return $form.find('tbody tr').filter(function () {
                return !$(this).hasClass('chalan-new-template');
            });
        }

        var ta = 0, td = 0;
        visibleRows().each(function () {
            var $r = $(this);
            var a = parseFloat($r.find('.amount-input').val()) || 0;
            var d = parseFloat($r.find('.discount-input').val()) || 0;
            if (d > a) {
                d = a;
                $r.find('.discount-input').val(d);
            }
            var net = a - d;
            $r.find('.net-amount-cell').text(net.toFixed(2));
            ta += a;
            td += d;
        });
        $form.find('#total-amount').text(ta.toFixed(2));
        $form.find('#total-discount').text(td.toFixed(2));
        $form.find('#total-net').text((ta - td).toFixed(2));
    }

    function initChalanEditCalculations() {
        chalanEditRecalc();
    }

    function applyChalanStdFeeToRow($row) {
        var p = window.CHALAN_EDIT_STD_FEES;
        if (!p || !p.amount_map || !p.students) return;
        var $selStudent = $row.find('.chalan-student-select');
        var sid = 0;
        if ($selStudent.length) {
            sid = parseInt($selStudent.val(), 10) || 0;
        } else {
            var $h = $row.find('input[name="line_student_id[]"]');
            if ($h.length) sid = parseInt($h.first().val(), 10) || 0;
        }
        var st = p.students[String(sid)];
        if (!st) return;
        var $ft = $row.find('.chalan-fee-type-select');
        if (!$ft.length) return;
        var ftid = parseInt($ft.val(), 10);
        if (!ftid) return;
        var key = st.class_id + '_' + ftid;
        var base = p.amount_map[key];
        if (base === undefined || base === null) base = 0;
        var isMonthly = String($ft.find('option:selected').attr('data-is-monthly')) === '1';
        var pv = parseInt(st.plan_value, 10) || 1;
        if (pv < 1) pv = 1;
        var gross, disc;
        if (isMonthly) {
            gross = base * pv;
            disc = (parseFloat(st.monthly_discount) || 0) * pv;
            if (disc > gross) disc = gross;
        } else {
            gross = base;
            disc = 0;
        }
        $row.find('.amount-input').val(Number(gross).toFixed(2));
        $row.find('.discount-input').val(Number(disc).toFixed(2));
        chalanEditRecalc();
    }

    function loadChalanEditForm(fd) {
        $('#chalanEditModalBody').html('<p class="text-muted mb-0">Loading…</p>');
        $('#chalanEditModal').modal('show');
        appendCsrf(fd);

        fetch(getEditUrl, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (r) {
                return r.text().then(function (text) {
                    var data = null;
                    try {
                        data = text ? JSON.parse(text) : null;
                    } catch (e) {
                        data = null;
                    }
                    return { ok: r.ok, status: r.status, data: data, raw: text };
                });
            })
            .then(function (res) {
                if (!res.ok || !res.data) {
                    var hint = res.raw ? res.raw.substring(0, 400) : '';
                    $('#chalanEditModalBody').html(
                        '<div class="alert alert-danger mb-0">HTTP ' + res.status + ' — could not load edit form. ' +
                        (hint ? '<pre class="small mt-2 mb-0" style="white-space:pre-wrap;">' + $('<div>').text(hint).html() + '</pre>' : '') +
                        '</div>'
                    );
                    return;
                }
                if (!res.data.success) {
                    $('#chalanEditModalBody').html('<div class="alert alert-danger mb-0">' + (res.data.msg || 'Failed to load') + '</div>');
                    return;
                }
                window.CHALAN_EDIT_STD_FEES = res.data.std_fees || { amount_map: {}, students: {} };
                $('#chalanEditModalBody').html(res.data.html);
                initChalanEditCalculations();
            })
            .catch(function () {
                $('#chalanEditModalBody').html('<div class="alert alert-danger mb-0">Network error.</div>');
            });
    }

    $('#chalanEditModal')
        .off('input.chalanEdit', '.amount-input, .discount-input')
        .on('input.chalanEdit', '.amount-input, .discount-input', function () {
            var $r = $(this).closest('tr');
            var a = parseFloat($r.find('.amount-input').val()) || 0;
            $r.find('.discount-input').attr('max', a);
            var d = parseFloat($r.find('.discount-input').val()) || 0;
            if (d > a) $r.find('.discount-input').val(a);
            chalanEditRecalc();
        });

    $('#chalanEditModal')
        .off('click.chalanAdd', '#chalan-add-row')
        .on('click.chalanAdd', '#chalan-add-row', function (e) {
            e.preventDefault();
            var $form = getChalanEditForm();
            if (!$form.length) return;
            var $tpl = $form.find('tr.chalan-new-template').first();
            if (!$tpl.length) return;
            var $row = $tpl.clone(true, true);
            $row.removeClass('chalan-new-template d-none').addClass('chalan-data-row');
            $row.find('input, select, textarea').prop('disabled', false);
            $tpl.before($row);
            chalanEditRecalc();
        });

    $('#chalanEditModal')
        .off('click.chalanRm', '.chalan-remove-row')
        .on('click.chalanRm', '.chalan-remove-row', function (e) {
            e.preventDefault();
            $(this).closest('tr').remove();
            chalanEditRecalc();
        });

    $('#chalanEditModal')
        .off('change.chalanStd', '.chalan-fee-type-select, .chalan-student-select')
        .on('change.chalanStd', '.chalan-fee-type-select, .chalan-student-select', function () {
            applyChalanStdFeeToRow($(this).closest('tr'));
        });

    $(document).on('click', '.chalan-edit-fees-btn', function () {
        var $btn = $(this);
        var sid = parseInt($btn.data('student-id'), 10) || 0;
        var pid = parseInt($btn.data('parent-id'), 10) || 0;
        var fd = new FormData();
        if (pid) fd.append('parent_id', pid);
        else fd.append('student_id', sid);
        loadChalanEditForm(fd);
    });

    window.openChalanEditForPay = function (parentId, studentId) {
        var fd = new FormData();
        var pid = parseInt(parentId, 10) || 0;
        var sid = parseInt(studentId, 10) || 0;
        if (pid) fd.append('parent_id', pid);
        else if (sid) fd.append('student_id', sid);
        else {
            toastr.warning('No family or student selected.');
            return;
        }
        loadChalanEditForm(fd);
    };

    $('#chalanEditSaveBtn').on('click', function () {
        var form = document.getElementById('chalan-edit-form');
        if (!form) return;
        var fd = new FormData(form);

        var $btn = $(this).prop('disabled', true);
        fetch(saveUrl, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (r) {
                return r.text().then(function (text) {
                    try {
                        return { ok: r.ok, data: text ? JSON.parse(text) : {} };
                    } catch (e) {
                        return { ok: r.ok, data: { success: false, msg: 'Invalid response (HTTP ' + r.status + ')' } };
                    }
                });
            })
            .then(function (res) {
                if (!res.data || !res.data.success) {
                    alert((res.data && res.data.msg) ? res.data.msg : 'Save failed');
                    return;
                }
                if (afterSave === 'refresh_pay_card') {
                    $('#chalanEditModal').modal('hide');
                    var sid = $('#student_id').val();
                    if (sid && typeof loadStudentCard === 'function') {
                        loadStudentCard(sid);
                    }
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.data.msg || 'Saved.');
                    } else {
                        alert(res.data.msg || 'Saved.');
                    }
                    return;
                }
                window.location.reload();
            })
            .catch(function () {
                alert('Network error while saving.');
            })
            .finally(function () {
                $btn.prop('disabled', false);
            });
    });
})();
</script>
