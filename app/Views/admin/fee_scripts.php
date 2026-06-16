<?php // app/Views/admin/fee_scripts.php ?>

<link rel="stylesheet" href="<?= base_url('resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/js/bootstrap5-compat.js?v=20260615b') ?>"></script>




<script>
// ✅ Global fee pool — accessible to all scripts
var feePool = [];
var financeAccountsEnabled = <?= ! empty($finance_enabled ?? false) ? 'true' : 'false' ?>;
var defaultCollectionAccountId = <?= (int) ($default_collection_account_id ?? 0) ?>;

function applyFinanceAccountsPayload(r) {
    if (!r || !r.success) return;
    financeAccountsEnabled = !!r.enabled;
    defaultCollectionAccountId = r.default_account_id || defaultCollectionAccountId || 0;
    if (!$('#collectionAccountId').length) return;

    var $sel = $('#collectionAccountId').empty();
    if (!financeAccountsEnabled) {
        $('#financeReceiveRow').hide();
        return;
    }
    $('#financeReceiveRow').show();
    (r.accounts || []).forEach(function (a) {
        $sel.append($('<option>', { value: a.account_id, text: a.label || a.account_name }));
    });
    if (defaultCollectionAccountId) {
        $sel.val(String(defaultCollectionAccountId));
    }
    if (r.received_by) {
        $('#receivedByLabel').val(r.received_by);
    }
}

function loadFinanceAccounts() {
    if (!$('#collectionAccountId').length) return;

    if (window.FINANCE_ACCOUNTS_BOOT && window.FINANCE_ACCOUNTS_BOOT.enabled) {
        applyFinanceAccountsPayload(window.FINANCE_ACCOUNTS_BOOT);
    }

    $.get('<?= base_url('admin/campus-finance-accounts/accounts-json') ?>', function (r) {
        applyFinanceAccountsPayload(r);
    }, 'json').fail(function () {
        if (!financeAccountsEnabled && window.FINANCE_ACCOUNTS_BOOT) {
            applyFinanceAccountsPayload(window.FINANCE_ACCOUNTS_BOOT);
        }
    });
}

window.FINANCE_ACCOUNTS_BOOT = <?= json_encode([
    'success' => true,
    'enabled' => ! empty($finance_enabled ?? false),
    'accounts' => $finance_accounts ?? [],
    'default_account_id' => (int) ($default_collection_account_id ?? 0),
    'received_by' => $received_by_name ?? '',
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

$(function () { loadFinanceAccounts(); });

var FEE_CSRF_NAME = <?= json_encode(csrf_token()) ?>;
var FEE_CSRF_HASH = <?= json_encode(csrf_hash()) ?>;
var FEE_CSRF_HEADER = <?= json_encode(csrf_header()) ?>;

function getFeeCsrfPair() {
    var meta = document.getElementById('csrf-meta-pay-chalan');
    if (meta) {
        FEE_CSRF_NAME = meta.getAttribute('name') || FEE_CSRF_NAME;
        FEE_CSRF_HASH = meta.getAttribute('content') || FEE_CSRF_HASH;
    }
    return { name: FEE_CSRF_NAME, value: FEE_CSRF_HASH };
}

function refreshFeeCsrfFromXHR(xhr) {
    if (!xhr || !xhr.getResponseHeader) return;
    var hash = xhr.getResponseHeader(FEE_CSRF_HEADER)
        || xhr.getResponseHeader('X-CSRF-TOKEN');
    if (!hash) return;
    FEE_CSRF_HASH = hash;
    var meta = document.getElementById('csrf-meta-pay-chalan');
    if (meta) meta.setAttribute('content', hash);
}

function appendFeeCsrfToAjax(options) {
    var pair = getFeeCsrfPair();
    if (!pair.name || pair.value == null) return;

    options.headers = $.extend({}, options.headers || {});
    options.headers[FEE_CSRF_HEADER] = pair.value;

    if (options.data instanceof FormData) {
        options.data.append(pair.name, pair.value);
        return;
    }

    if (typeof options.data === 'string') {
        options.data += (options.data ? '&' : '')
            + encodeURIComponent(pair.name) + '=' + encodeURIComponent(pair.value);
        return;
    }

    options.data = options.data || {};
    if (typeof options.data === 'object') {
        options.data[pair.name] = pair.value;
    }
}

$.ajaxPrefilter(function (options) {
    var method = (options.type || 'GET').toUpperCase();
    if (method !== 'POST' && method !== 'PUT' && method !== 'PATCH' && method !== 'DELETE') {
        return;
    }
    appendFeeCsrfToAjax(options);
});

$(document).ajaxComplete(function (_e, xhr) {
    refreshFeeCsrfFromXHR(xhr);
});

let lastMoveType = null;

// Enhanced fee scripts with additional functionality
$(document).ready(function() {
    // Initialize date picker
    $('#datepicker2').datetimepicker({
        format: 'YYYY-MM-DD',
        defaultDate: new Date()
    });

    // Initialize student select2
    $('#student_id').select2({
        placeholder: 'Search by student name or ID',
        minimumInputLength: 2,
        width: '100%',
        dropdownParent: $('#student_id').closest('.card-body, .content-wrapper, body').first(),
        ajax: {
            url: '<?= base_url('admin/fee-chalan-pay/get-student-info'); ?>',
            type: 'POST',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                var payload = { term: params.term || '' };
                if (typeof window.adminCsrfPayload === 'function') {
                    return window.adminCsrfPayload(payload);
                }
                var pair = getFeeCsrfPair();
                if (pair.name) payload[pair.name] = pair.value;
                return payload;
            },
            processResults: function(data) {
                if (data && data.error) {
                    console.error('Student search error:', data.error);
                    return { results: [] };
                }
                if (!Array.isArray(data)) {
                    console.error('Student search: invalid response', data);
                    return { results: [] };
                }
                return {
                    results: $.map(data, function(item) {
                        return {
                            id: item.id,
                            text: item.text
                        };
                    })
                };
            },
            error: function(xhr, status) {
                // Select2 aborts the previous request when the user keeps typing — not a real error.
                if (status === 'abort') {
                    return;
                }
                console.error('Student search failed', xhr.status, xhr.responseText);
                refreshFeeCsrfFromXHR(xhr);
            },
            cache: false
        },
        templateResult: formatStudent,
        templateSelection: formatStudentSelection
    })
    .on('select2:select', function(e) {
        // ✅ Clear fee pool every time a new student is selected
        clearFeePool();
        loadStudentCard(e.params.data.id);
    })
    .on('select2:clear', function() {
        // ✅ Also clear pool if selection is cleared
        clearFeePool();
    });

    // Initialize bootstrap switch
    $("input[data-bootstrap-switch]").each(function(){
        $(this).bootstrapSwitch();
    });
});

function formatStudent(student) {
    if (student.loading) return student.text;
    return $('<div class="student-result">' + student.text + '</div>');
}

function formatStudentSelection(student) {
    return student.text || student.id;
}


function loadStudentCard(student_id) {
    // ✅ Always clear fee pool before loading new student
    clearFeePool();

    $.ajax({
        url: '<?= base_url('admin/fee-chalan-pay/getStudentCardAjax'); ?>',
        type: 'POST',
        data: { student_id: student_id },
        dataType: 'json',
        success: function(response, _textStatus, xhr) {
            refreshFeeCsrfFromXHR(xhr);
            if (response.success) {
                lastStudentCardAjaxResponse = response;
                $('#student-card-container').html(response.html);
                
                // Update parent summary
                $('#parentSummary').show();
                $('#familyTotalAmount').text(response.family_total ?? '0');
                
                // ✅ Load parent-related data
                fetchParentFeeSummary(response.parent_id);
                loadPaidFeeTable(response.parent_id);

                // Re-init toggle switches if any
                $("input[data-bootstrap-switch]").each(function() {
                    $(this).bootstrapSwitch();
                });

            } else {
                $('#student-card-container').html('<div class="alert alert-danger">' + response.html + '</div>');
                
                if (response.parent_id) {
                    fetchParentFeeSummary(response.parent_id);
                }
                $('#parentSummary').hide();
                $('#lastFamilyPayments').hide();
            }
        },
        error: function(xhr) {
            console.error('Student card load failed', xhr.status, xhr.responseText);
            $('#student-card-container').html('<div class="alert alert-danger">Error loading student data</div>');
            $('#parentSummary').hide();
            $('#lastFamilyPayments').hide();
            refreshFeeCsrfFromXHR(xhr);
        }
    });
}


function fetchParentFeeSummary(parentId) {
    $.ajax({
        url: '<?= base_url('admin/fee-chalan-pay/get-parent-fee-summary') ?>',
        type: 'POST',
        data: { parent_id: parentId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Show the parent summary block
                $('#parentSummary').show();

                // Update values in the unified block
               // $('#parentName').text('Parent: ' + (response.parent_name ?? ''));
                $('#todayPaidAmount').text(parseFloat(response.totalToday || 0).toFixed(0));
                $('#monthPaidAmount').text(parseFloat(response.totalMonth || 0).toFixed(0));
                $('#familyTotalAmount').text(parseFloat(response.familyTotalDue || 0).toFixed(0));
               // $('#studentCount').text(response.student_count || '0');

                // Set date labels
                const today = new Date();
                const todayFormatted = today.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' });
                const monthFormatted = today.toLocaleDateString('en-US', { month: 'long' });

                $('#todayDateLabel').text(todayFormatted);
                $('#monthLabel').text(monthFormatted);

                const lastBody = $('#lastFamilyPaymentsBody');
                lastBody.empty();
                if (response.last_payments && response.last_payments.length) {
                    response.last_payments.forEach(function (payment) {
                        lastBody.append(
                            '<tr>' +
                                '<td class="py-0 ps-0">' + payment.payment_date_label + '</td>' +
                                '<td class="py-0 pe-0 text-end fw-bold">' +
                                    parseFloat(payment.total_received || 0).toFixed(0) +
                                '</td>' +
                            '</tr>'
                        );
                    });
                    $('#lastFamilyPayments').show();
                } else {
                    lastBody.append('<tr><td colspan="2" class="py-0 ps-0 text-muted">No prior payments</td></tr>');
                    $('#lastFamilyPayments').show();
                }
            } else {
                $('#parentSummary').hide();
                $('#lastFamilyPayments').hide();
            }
        },
        error: function() {
            $('#parentSummary').hide();
            $('#lastFamilyPayments').hide();
        }
    });
}



function markFeeUnpaid(chalanId, studentId, parentId, reverseDiscount) {
    const undoDiscount = reverseDiscount === 1 || reverseDiscount === true;
    $.post('<?= base_url('admin/fee-chalan-pay/make-unpaid') ?>', {
        chalan_id: chalanId,
        reverse_discount: undoDiscount ? 1 : 0
    }, function(resp) {
        if (resp.success) {
            const parentId = $('#student-card-container .student-card').data('parent-id');
            if (!parentId) {
                return;
            }
            loadStudentCard(studentId);
            fetchParentFeeSummary(parentId);
            loadPaidFeeTable(parentId);
            showToast(resp.message || (undoDiscount ? 'Discount reversed' : 'Marked unpaid'), 'success');
        } else {
            showToast(resp.message || 'Failed to update status', 'danger');
        }
    });
}

function showToast(message, type = 'success') {
    const toastId = 'toast-' + Date.now();
    const toast = `
        <div id="${toastId}" class="toast bg-${type} text-white fade show" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 20px; right: 20px; min-width: 250px; z-index: 1050;">
            <div class="toast-header bg-${type} text-white">
                <strong class="me-auto"><i class="fas fa-info-circle"></i> Info</strong>
                <button type="button" class="ms-2 mb-1 close text-white" data-bs-dismiss="toast" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;

    $('body').append(toast);
    setTimeout(() => {
        $('#' + toastId).toast('hide').remove();
    }, 3000);
}


function loadPaidFeeTable(parentId) {
    $.post('<?= base_url('admin/fee-chalan-pay/get-monthly-paid-fees') ?>', { parent_id: parentId }, function(resp) {
        if (!resp || typeof resp !== 'object') {
            return;
        }
        if (resp.success) {
            const tbody = $('#paidFeeTableBody');
            tbody.empty();

            if (resp.data.length === 0) {
                tbody.append('<tr><td colspan="5" class="text-center text-muted">No payments found</td></tr>');
                return;
            }
resp.data.forEach((fee, index) => {
    const isTodayUpdate = fee.can_reverse_today === true || fee.can_reverse_today === 1;
    const entryType = fee.entry_type || 'payment';
    const isDiscountEntry = entryType === 'discount' || entryType === 'discount_pending';
    const amount = parseFloat(fee.amount) || 0;
    const discount = parseFloat(fee.discount) || 0;
    const net = parseFloat(fee.net_amount) || (amount - discount);

    const highlight = index === 0
        ? (isDiscountEntry ? 'table-warning' : 'table-success')
        : '';

    let amountHtml;
    if (isDiscountEntry) {
        amountHtml = `<div class="text-warning fw-bold">Discount: Rs ${discount.toFixed(0)}</div>`;
        if (entryType === 'discount_pending') {
            amountHtml += '<small class="text-muted">Pending in pool</small>';
        }
    } else {
        amountHtml = `<div>Rs ${net.toFixed(0)}</div>`;
    }

    if (fee.paid_date && !isDiscountEntry) {
        amountHtml += `<small class="text-muted d-block">${fee.paid_date}</small>`;
    }

    const reverseFlag = isDiscountEntry ? 1 : 0;
    const btnClass = isDiscountEntry ? 'btn-warning' : 'btn-danger';
    const btnLabel = isDiscountEntry ? 'Undo disc.' : 'Unpaid';
    const canUnpaid = isTodayUpdate
        ? `<button class="btn btn-sm ${btnClass}" onclick="markFeeUnpaid(${fee.chalan_id}, ${fee.student_id}, ${fee.parent_id || 0}, ${reverseFlag})">${btnLabel}</button>`
        : '';

    tbody.append(`
        <tr class="${highlight}">
            <td>${fee.first_name} ${fee.last_name}</td>
            <td>${fee.fee_type_name}<br><small class="text-muted">(${fee.fee_month})</small></td>
            <td>${amountHtml}</td>
            <td>${canUnpaid}</td>
        </tr>
    `);
});

            var $scrollTarget = $('#paidFeeTableWrapper');
            if (!$scrollTarget.length) {
                $scrollTarget = $('#parentSummary');
            }
            var offset = $scrollTarget.length ? $scrollTarget.offset() : null;
            if (offset) {
                $('html, body').animate({ scrollTop: offset.top - 100 }, 500);
            }
        } else {
            $('#paidFeeTableBody').empty();
        }
    }).fail(function(xhr) {
        refreshFeeCsrfFromXHR(xhr);
        $('#paidFeeTableBody').empty();
    });
}






function renderFeePool() {
    let html = '';
    let total = 0;

    feePool.forEach((fee, index) => {
        const amount = parseFloat(fee.amount) || 0;
        const discount = parseFloat(fee.discount) || 0;
        const paid = amount - discount;
        total += paid;

        html += `
            <tr>
                <td>
                    <strong>${fee.student_name ?? 'N/A'}</strong>
                </td>
                <td>
                    <strong>${fee.feeType}</strong><br>
                    <small class="text-muted">${fee.feeMonth}</small>
                </td>
                <td>
                     Rs ${amount.toFixed(2)}
                    ${discount > 0 ? `<br><small class="text-success">(Discount: Rs ${discount.toFixed(2)})</small>` : ''}
                </td>
                <td>
                    <button class="btn btn-sm btn-danger" onclick="removeFeeFromPool(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    if (feePool.length === 0) {
        html = `<tr><td colspan="4" class="text-center text-muted">No fees selected.</td></tr>`;
        $('#paymentPoolCard').hide();
    } else {
        $('#paymentPoolCard').show();
    }

    $('#paymentPoolTable tbody').html(html);
    $('#confirmPaymentBtn').toggle(feePool.length > 0);
    $('#clearPoolBtn').toggle(feePool.length > 0);
    $('#poolTotalAmount').text(total.toFixed(2));
    if ($('#poolItemCount').length) {
        $('#poolItemCount').text(feePool.length);
    }
}


function clearFeePool() {
    
        feePool.forEach(fee => {
            const $btn = $('#fee-btn-' + fee.chalan_id);
            if ($btn.length) {
                $('#fee-buttons-' + fee.student_id).append($btn);
                $btn.show();
            }
        });

        feePool = [];
        renderFeePool();
    
}



function removeFeeFromPool(index) {
    const removed = feePool.splice(index, 1)[0];
    renderFeePool();

    if (removed && removed.chalan_id) {
        const $btn = $('#fee-btn-' + removed.chalan_id);

        // ✅ If fee button exists in hidden block, move it back to fee card
        if ($btn.length) {
            $('#fee-buttons-' + removed.student_id).append($btn);
            $btn.show();
        } else {
            // 🔁 Optional fallback: reload the entire student card if button is missing
            console.warn('Fee button not found in DOM, reloading card...');
            reloadStudentCard(removed.student_id);
        }
    }
}



function reloadStudentCard(studentId) {
    $.ajax({
        url: '<?= base_url('admin/fee-chalan-pay/getStudentCardAjax') ?>',
        type: 'POST',
        data: { student_id: studentId },
        success: function(resp) {
            if (resp.success && resp.html) {
                $('#student-card-container').html(resp.html);
                removePooledFeesFromCard(); // Hide pooled fees again
            }
        }
    });
}


$('#confirmPaymentBtn').click(function () {
    const paid_date = $('#datePaid').val();
    const studentId = $('#student_id').val(); // hidden input in modal (selected student)
    const parentId = $('#student-card-container .student-card').data('parent-id'); // from fee card

    if (!paid_date) {
        toastr.error('Please select a payment date');
        return;
    }

    if (feePool.length === 0) {
        toastr.error('No fees in payment pool to confirm');
        return;
    }

    $.ajax({
        url: '<?= base_url('admin/fee-chalan-pay/mark-multiple-fees-as-paid') ?>', // ✅ use lowercase/kebab-case
        type: 'POST',
        data: {
            fees: JSON.stringify(feePool),
            paid_date: paid_date,
            student_id: studentId,
            account_id: financeAccountsEnabled ? ($('#collectionAccountId').val() || defaultCollectionAccountId) : ''
        },
        success: function (response) {
            if (response.success) {
                loadStudentCard(studentId);

                // ✅ Refresh parent summary
                fetchParentFeeSummary(parentId);

                loadPaidFeeTable(parentId);           // ✅ Refresh paid fee entries

                // ✅ Refresh paid table with highlight
                if (response.last_chalan_id) {
                    loadPaidFeeTable(parentId, response.last_chalan_id); // highlight row
                } else {
                    loadPaidFeeTable(parentId); // fallback
                }

                // ✅ Clear fee pool and re-render
                feePool = [];
                renderFeePool();

                // ✅ Update dues for all siblings
                if (response.student_dues_all && Array.isArray(response.student_dues_all)) {
                    response.student_dues_all.forEach(function (student) {
                        const formatted = new Intl.NumberFormat().format(student.amount);
                        $('#student-dues-' + student.student_id).text('Rs ' + formatted);
                    });
                }

                // ✅ Update family dues
                if (response.family_dues) {
                    $('#family-dues').text('Rs ' + response.family_dues.amount);
                }

            } else {
                toastr.error(response.message || 'Payment confirmation failed');
                feePool.forEach(fee => {
                    if (fee.cardElement) fee.cardElement.show();
                });
            }
        },
        error: function () {
            toastr.error('Error processing payment confirmation');
            feePool.forEach(fee => {
                if (fee.cardElement) fee.cardElement.show();
            });
        }
    });
});



function removePooledFeesFromCard() {
    feePool.forEach(fee => {
        $('#fee-row-' + fee.chalan_id).addClass('d-none');
    });
}






function restoreFeeRowFromCard(chalan_id) {
    $('#fee-row-' + chalan_id).removeClass('d-none');
}


function submitPartial() {
    const chalanId = $('#partialChalanId').val();
    const studentId = $('#partialStudentId').val();
    const paid = parseFloat($('#partialPaid').val()) || 0;
    const discount = parseFloat($('#partialDiscount').val()) || 0;
    const total = parseFloat($('#partialTotal').val()) || 0;
    const balance = total - (paid + discount);

    if (paid <= 0 && discount <= 0) {
        toastr.error('Please enter either payment amount or discount');
        return;
    }

    if (balance < 0) {
        toastr.error('Payment + discount cannot exceed total amount');
        return;
    }

    $.ajax({
        url: '<?= base_url('admin/fee-chalan-pay/addPartialFeeToPool') ?>',
        type: 'POST',
        data: {
            chalan_id: chalanId,
            student_id: studentId,
            paid_amount: paid,
            discount_amount: discount,
            paid_date: $('#datePaid').val()
        },
        success: function(response) {
            if (response.success) {
                $('#partialModal').modal('hide');
                const remainingBalance = paid + discount;
                // 1️⃣ Push the paid portion into the pool
                feePool.push({
                    chalan_id: response.fee.chalan_id,
                    student_id: response.fee.student_id,
                      student_name: response.fee.student_name, // ✅ now filled
                      amount: remainingBalance,
                    paid: paid,
                    discount: discount,
                    feeType: response.fee.feeType,
                    feeMonth: response.fee.feeMonth
                });

                // ✅ Hide the original fee button manually
        $('#fee-btn-' + chalanId).addClass('d-none');

                renderFeePool();

                // 2️⃣ Refresh the current student card
                $.ajax({
                    url: '<?= base_url('admin/fee-chalan-pay/getStudentCardAjax') ?>',
                    method: 'POST',
                    data: { student_id: studentId },
                    success: function(resp) {
                        if (resp.success && resp.html) {
                            $('#student-card-container').html(resp.html);
                             removePooledFeesFromCard(); // ✅ Hide duplicates
                        } else {
                            toastr.warning('Could not refresh student card.');
                        }
                    },
                    error: function() {
                        toastr.error('Error refreshing student card.');
                    }
                });
            } else {
                toastr.error(response.message || 'Partial payment failed');
            }
        },
        error: function() {
            toastr.error('Error processing partial payment');
        }
    });
}


// ----------------------
// 1️⃣ Single Fee Payment
// ----------------------
function paySingleFee(button) {
    const $btn = $(button);
    const feeId = $btn.data('fee-id');
    const studentId = $btn.data('student');
    const amount = parseFloat($btn.data('amount')) || 0;
    const discount = parseFloat($btn.data('discount')) || 0;

    const isPartial = $('#partialToggle').is(':checked');

    if (!isPartial) {
        const studentName = $btn.data('student-name') 
            || $btn.closest('.student-card').find('.student-header h5').text().trim();

        // Prevent duplicate entries in pool
        if (feePool.some(f => f.chalan_id === feeId)) return;

        // If switching from family/student move, clear pool first
        if (lastMoveType === 'student' || lastMoveType === 'family') {
            clearFeePool();
        }

        // Push fee to pool
        feePool.push({
            chalan_id: feeId,
            student_id: studentId,
            student_name: studentName,
            amount: amount,
            paid: amount,
            discount: discount,
            feeType: $btn.data('feetype'),
            feeMonth: $btn.data('feemonth')
        });

        $('#fee-hidden-' + studentId).append($btn);
        $btn.hide();

        renderFeePool();

        lastMoveType = 'single';
    } else {
        // Partial move
        if (lastMoveType === 'student' || lastMoveType === 'family') {
            clearFeePool();
        }

        $('#partialChalanId').val(feeId);
        $('#partialStudentId').val(studentId);

        let payable = amount - discount;
        $('#partialTotal').val(payable.toFixed(2));
        $('#partialPaid').val('0');
        $('#partialDiscount').val('0');
        $('#partialBalance').val(payable.toFixed(2));

        $('#partialModal').modal('show');

        lastMoveType = 'partial';
    }
}


// ---------------------------
// 2️⃣ Add all unpaid fees (student move)
// ---------------------------
function addAllUnpaidFeesToPool(button) {
    // If switching from single or partial, clear first
    if (lastMoveType === 'single' || lastMoveType === 'partial') {
        clearFeePool();
    }

    const domStudentName = $(button).closest('.student-card').find('.student-header h5').text().trim();
    const studentId = $(button).data('student-id');

    $.ajax({
        url: '<?= base_url('admin/fee-chalan-pay/get-unpaid-fees') ?>',
        type: 'POST',
        data: { student_id: studentId },
        success: function(response) {
            if (response.success && response.fees?.length) {
                let addedCount = 0;

                response.fees.forEach(fee => {
                    const feeId = fee.chalan_id;

                    if (!feePool.some(f => f.chalan_id === feeId)) {
                        const amount = parseFloat(fee.amount) || 0;
                        const discount = parseFloat(fee.discount || 0);
                        const netAmount = amount - discount;

                        if (netAmount > 0) {
                            feePool.push({
                                chalan_id: feeId,
                                student_id: fee.student_id,
                                student_name: fee.student_name || domStudentName,
                                amount: amount,
                                paid: netAmount,
                                discount: discount,
                                feeType: fee.fee_type_name,
                                feeMonth: fee.fee_month
                            });

                            const $btn = $('#fee-btn-' + feeId);
                            if ($btn.length) {
                                $('#fee-hidden-' + fee.student_id).append($btn);
                                $btn.hide();
                            }

                            addedCount++;
                        }
                    }
                });

                if (addedCount > 0) {
                    renderFeePool();
                }
            } else {
                toastr.warning(response.message || 'No unpaid fees found');
            }
        },
        error: () => toastr.error('Error fetching unpaid fees')
    });

    lastMoveType = 'student';
}


// ---------------------------
// 3️⃣ Add all unpaid family fees
// ---------------------------
function addFamilyUnpaidFeesToPool(parentId) {
    // Family move always clears pool first
    clearFeePool();

    $.ajax({
        url: '<?= base_url('admin/fee-chalan-pay/get-family-unpaid-fees') ?>',
        type: 'POST',
        data: { parent_id: parentId },
        success: function(response) {
            if (response.success && Array.isArray(response.fees) && response.fees.length) {
                let addedCount = 0;

                response.fees.forEach(fee => {
                    const feeId = fee.chalan_id;
                    if (!feePool.some(f => f.chalan_id === feeId)) {
                        const amount = parseFloat(fee.amount || 0);
                        const discount = parseFloat(fee.discount || 0);
                        const netAmount = amount - discount;

                        if (netAmount > 0) {
                            feePool.push({
                                chalan_id: feeId,
                                student_id: fee.student_id,
                                student_name: fee.student_name,
                                amount: amount,
                                paid: netAmount,
                                discount: discount,
                                feeType: fee.fee_type_name,
                                feeMonth: fee.fee_month
                            });

                            const $btn = $('#fee-btn-' + feeId);
                            if ($btn.length) {
                                $('#fee-hidden-' + fee.student_id).append($btn);
                                $btn.hide();
                            }

                            addedCount++;
                        }
                    }
                });

                if (addedCount > 0) {
                    renderFeePool();
                }
            } else {
                toastr.warning(response.message || 'No unpaid family fees found');
            }
        },
        error: () => toastr.error('Error fetching unpaid family fees')
    });

    lastMoveType = 'family';
}



var familyHistoryParentId = null;

function feePayAppendCsrf(data) {
  var $m = $('#csrf-meta-pay-chalan');
  if ($m.length) {
    var n = $m.attr('name');
    if (n) { data[n] = $m.attr('content'); }
    return;
  }
  <?php if (function_exists('csrf_token')): ?>
  data['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>';
  <?php endif; ?>
}

function loadFamilyFeeHistory() {
  if (!familyHistoryParentId) {
    return;
  }
  $('#familyHistoryContainer').html('<div class="text-center p-3 text-muted">Loading…</div>');

  var postData = {
    parent_id: familyHistoryParentId,
    limit: 200,
    start_date: $('#fhStart').val() || '',
    end_date: $('#fhEnd').val() || ''
  };
  feePayAppendCsrf(postData);

  $.ajax({
    url: '<?= site_url('admin/fee-chalan-pay/get-family-fee-history') ?>',
    method: 'POST',
    dataType: 'json',
    data: postData
  })
  .done(function (res) {
    if (res && res.csrfName && res.csrfHash !== undefined) {
      var $m = $('#csrf-meta-pay-chalan');
      if ($m.length) {
        $m.attr('name', res.csrfName).attr('content', res.csrfHash);
      }
    }
    if (res && res.success) {
      $('#familyHistoryContainer').html(res.html || '');
      $('[data-bs-toggle="tooltip"]').tooltip({container:'body'});
    } else {
      $('#familyHistoryContainer').html(res?.html || '<div class="alert alert-danger">Request failed.</div>');
    }
  })
  .fail(function (xhr) {
    console.error('Family fee history AJAX failed:', xhr.status, xhr.responseText);
    $('#familyHistoryContainer').html('<div class="alert alert-danger">Error ' + xhr.status + ' loading history.</div>');
  });
}

function showFamilyFeeHistoryPage(parentId) {
  familyHistoryParentId = parseInt(parentId, 10) || 0;
  if (!familyHistoryParentId) {
    return;
  }
  if (!$('#familyHistoryContainer').length) {
    if (typeof toastr !== 'undefined') {
      toastr.warning('Family history is available on the fee payment page.');
    }
    return;
  }
  var $modal = $('#familyFeeHistoryModal');
  if ($modal.length) {
    $modal.modal('show');
  }
  loadFamilyFeeHistory();
}

$(function () {
  $(document).on('click', '#fhApplyFilter', function () {
    loadFamilyFeeHistory();
  });
  $(document).on('click', '#fhClearFilter', function () {
    $('#fhStart, #fhEnd').val('');
    loadFamilyFeeHistory();
  });
});



function updatePartialBalance() {
    const total = parseFloat($('#partialTotal').val()) || 0;
    const paid = parseFloat($('#partialPaid').val()) || 0;
    const discount = parseFloat($('#partialDiscount').val()) || 0;
    const balance = total - (paid + discount);

    $('#partialBalance').val(balance.toFixed(2));
}

// Bind events to update balance live
$('#partialPaid, #partialDiscount').on('input', updatePartialBalance);


let firstStudentId = null;
<!-- JS: Enhanced modal + function -->

(function ensureEditFeeModal() {
  if (document.getElementById('editStudentFeeModal')) return;
  $('body').append(`
    <div class="modal fade" id="editStudentFeeModal" tabindex="-1" role="dialog" aria-labelledby="editStudentFeeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
          <div class="modal-header py-2">
            <h5 class="modal-title" id="editStudentFeeModalLabel">
              <i class="fas fa-edit me-2"></i>Edit Monthly Fees
            </h5>
            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span>&times;</span></button>
          </div>

          <div class="modal-body p-2">
            <!-- Summary bar -->
            <div id="feeSummary" class="row text-center mx-1 mb-2" style="gap:8px;">
              <div class="col bg-light rounded py-2">
                <div class="small text-muted">Total Class Fee</div>
                <div id="sumClassFee" class="fw-bold">Rs 0.00</div>
              </div>
              <div class="col bg-light rounded py-2">
                <div class="small text-muted">Total Current Fee</div>
                <div id="sumCurrentFee" class="fw-bold">Rs 0.00</div>
              </div>
              <div class="col bg-light rounded py-2">
                <div class="small text-muted">Total New Fee</div>
                <div id="sumNewFee" class="fw-bold">Rs 0.00</div>
              </div>
              <div class="col bg-light rounded py-2">
                <div class="small text-muted">Δ (New - Current)</div>
                <div id="sumDelta" class="fw-bold">Rs 0.00</div>
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-sm table-striped table-hover mb-0">
                <thead class="table-dark">
                  <tr>
                    <th style="width:56px;">S#</th>
                    <th>Student</th>
                    <th>Class</th>
                    <th class="text-end">Class Fee</th>
                    <th class="text-end">Current Fee</th>
                    <th style="min-width:140px;">New Fee</th>
                  </tr>
                </thead>
                <tbody id="studentFeeEditBody">
                  <tr><td colspan="6" class="text-center p-4">Loading…</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="modal-footer py-2">
            <div class="me-auto small text-muted">Adjust the “New Fee” column; totals update live.</div>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button id="saveFeeChanges" type="button" class="btn btn-primary">
              <i class="fas fa-save me-1"></i>Save Changes
            </button>
          </div>
        </div>
      </div>
    </div>
  `);
})();

// ---------- Utilities ----------
function fmtCurrency(n) {
  const x = isFinite(n) ? Number(n) : 0;
  return 'Rs ' + x.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function num(v) {
  const n = parseFloat(v);
  return isFinite(n) ? n : 0;
}
function prettyClass(s) {
  // Try common keys from your responses; fallback gracefully
  const cls = s.class_name ?? s.class ?? s.class_title ?? '';
  const sec = s.section_name ?? s.section ?? s.sec ?? '';
  return (cls && sec) ? `${cls} - ${sec}` : (cls || sec || 'N/A');
}
function getClassFee(s) {
  // Try several likely fields for "class/base fee"
  const c = s.class_fee ?? s.base_class_fee ?? s.base_fee ?? s.monthly_class_fee ?? s.classfee;
  return num(c);
}
function getCurrentFee(s) {
  // Your prior code used student.monthly_fee as the editable base
  return num(s.monthly_fee ?? s.fee ?? s.current_fee ?? 0);
}
function recalcTotals() {
  let totalClass = 0, totalCurrent = 0, totalNew = 0;
  $('#studentFeeEditBody tr[data-student-id]').each(function(){
    const $tr = $(this);
    totalClass  += num($tr.data('class-fee'));
    totalCurrent+= num($tr.data('current-fee'));
    totalNew    += num($tr.find('input.new-fee').val());
  });
  $('#sumClassFee').text(fmtCurrency(totalClass));
  $('#sumCurrentFee').text(fmtCurrency(totalCurrent));
  $('#sumNewFee').text(fmtCurrency(totalNew));
  $('#sumDelta').text(fmtCurrency(totalNew - totalCurrent));
}

// ---------- Main launcher ----------
window.showEditStudentFeeModal = function showEditStudentFeeModal(student_id) {
  window.firstStudentId = student_id; // keep your existing pattern
  const $modal = $('#editStudentFeeModal');
  $('#studentFeeEditBody').html('<tr><td colspan="6" class="text-center p-4">Loading…</td></tr>');
  $('#sumClassFee, #sumCurrentFee, #sumNewFee, #sumDelta').text('Rs 0.00');
  $modal.modal('show');

  $.ajax({
    url: '<?= base_url('admin/fee-chalan-pay/getStudentCardAjax'); ?>',
    type: 'POST',
    data: { student_id: student_id },
    success: function(response) {
      if (response.success && response.student_details?.length) {
        let rows = '';
        let idx = 1;

        response.student_details.forEach((s) => {
          const sid        = s.student_id;
          const name       = s.student_name ?? 'N/A';
          const classLabel = prettyClass(s);
          const classFee   = getClassFee(s);
          const currentFee = getCurrentFee(s);
          const newFee     = currentFee; // default new == current

          rows += `
            <tr data-student-id="${sid}"
                data-class-fee="${classFee}"
                data-current-fee="${currentFee}">
              <td class="align-middle text-muted">${idx++}</td>
              <td class="align-middle">
                <div class="fw-semibold">${name}</div>
              </td>
              <td class="align-middle">
                <span class="badge text-bg-info">${classLabel}</span>
              </td>
              <td class="align-middle text-end">${fmtCurrency(classFee)}</td>
              <td class="align-middle text-end">${fmtCurrency(currentFee)}</td>
              <td class="align-middle">
                <input type="number" step="0.01" min="0" 
                       name="new_fee[${sid}]" 
                       class="form-control form-control-sm new-fee" 
                       value="${newFee}">
              </td>
            </tr>
          `;
        });

        $('#studentFeeEditBody').html(rows);

        // Live totals
        recalcTotals();
        $('#studentFeeEditBody').on('input', 'input.new-fee', recalcTotals);

        // Tooltips inside modal (optional)
        $modal.find('[data-bs-toggle="tooltip"]').tooltip({ container: 'body' });

      } else {
        $('#studentFeeEditBody').html('<tr><td colspan="6" class="text-center p-4 text-muted">No student found.</td></tr>');
        toastr.warning("No student found for this parent");
      }
    },
    error: function() {
      $('#studentFeeEditBody').html('<tr><td colspan="6" class="text-center p-4 text-danger">Error loading data.</td></tr>');
      toastr.error("Error fetching student fee info.");
    }
  });
};

// ---------- Save handler (kept, but totals remain) ----------
$(document).off('click', '#saveFeeChanges').on('click', '#saveFeeChanges', function () {
  const data = {};
  $('#studentFeeEditBody input.new-fee[name^="new_fee"]').each(function () {
    const m = $(this).attr('name').match(/\[(\d+)\]/);
    if (!m) return;
    const studentId = m[1];
    const enteredFee = parseFloat($(this).val());
    if (!isNaN(enteredFee)) data[studentId] = enteredFee;
  });

  $.ajax({
    url: '<?= base_url('admin/fee-chalan-pay/updateStudentDiscount'); ?>',
    method: 'POST',
    data: { fees: data },
    success: function (res) {
      if (res.success) {
        toastr.success('Discounts updated successfully.');
        $('#editStudentFeeModal').modal('hide');

        // 🔁 Refresh student card
        const firstStudentId = $('.student-card').first().data('student-id');
        if (firstStudentId) {
          $.ajax({
            url: '<?= base_url('admin/fee-chalan-pay/getStudentCardAjax'); ?>',
            method: 'POST',
            data: { student_id: firstStudentId },
            success: function (resp) {
              console.log("Student card refresh response:", resp);
              if (resp.success && resp.html) {
                $('#student-card-container').html(resp.html);
              } else {
                toastr.warning('Could not refresh student card.');
              }
            },
            error: function () {
              toastr.error('Refresh failed.');
            }
          });
        }
      } else {
        toastr.warning(res.message || 'Update failed.');
      }
    },
    error: function () {
      toastr.error('AJAX request failed.');
    }
  });
});



function showAdvanceFeeStudentModal(student_id) {
    firstStudentId = student_id;

    $.ajax({
        url: '<?= base_url('admin/fee-chalan-pay/getAdvanceFeeStudentsAjax'); ?>',
        type: 'POST',
        data: { student_id: student_id },
        success: function(response) {
            if (response.success && response.student_dues?.length) {
                let tbody = '';
                response.student_dues.forEach((student) => {
                    const name = student.student_name ?? 'N/A';
                    const dues = parseFloat(student.total_due) || 0;
                    const advanceFee = parseFloat(student.advance_fee) || 0;
                    const readOnly = dues > 0 ? 'readonly' : '';

                    tbody += `
                        <tr>
                            <td>${name}</td>
                            <td>Rs ${dues.toFixed(2)}</td>
                            <td class="text-end text-muted">Rs ${advanceFee.toFixed(2)}</td>
                            <td>
                                <input type="number" step="0.01" min="0" name="advance_fee[${student.student_id}]"
                                    class="form-control" value="" placeholder="0.00" ${readOnly}>
                            </td>
                        </tr>
                    `;
                });

                $('#advanceStudentFeeBody').html(tbody);
                $('#advanceStudentFeeModal').modal('show');
            } else {
                toastr.warning("No students found");
            }
        },
        error: function() {
            toastr.error("Error fetching advance student fee info.");
        }
    });
}



$('#saveAdvanceFee').on('click', function () {
    let data = {};

    $('input[name^="advance_fee"]').each(function () {
        const studentId = $(this).attr('name').match(/\[(\d+)\]/)[1];
        const fee = parseFloat($(this).val());
        const isDisabled = $(this).prop('readonly');

        if (!isNaN(fee) && fee > 0 && !isDisabled) {
            data[studentId] = fee;
        }
    });

    $.ajax({
        url: '<?= base_url('admin/fee-chalan-pay/saveAdvanceFee'); ?>', // ✅ Save endpoint
        method: 'POST',
        data: {
            fees: JSON.stringify(data),
            paid_date: $('#datePaid').val() || '',
            account_id: financeAccountsEnabled ? ($('#collectionAccountId').val() || defaultCollectionAccountId) : ''
        },
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                toastr.success('Advance fees updated successfully.');
                $('#advanceStudentFeeModal').modal('hide');

                // Optional: Refresh card
            } else {
                toastr.warning(res.message || 'Update failed.');
            }
        },
        error: function () {
            toastr.error('AJAX request failed.');
        }
    });
});

$(document).on("click", ".fee-history-btn", function() {
    var parentId = $(this).data("parent-id");
    if (parentId) {
        showFamilyFeeHistoryPage(parentId);
    }
});

</script>
