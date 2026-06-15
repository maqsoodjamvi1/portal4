<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />

<style>
    .fc-chalan-page { --fc-accent: #2563eb; --fc-accent2: #1d4ed8; --fc-muted: #64748b; --fc-border: #e2e8f0; --fc-bg: #f8fafc; }
    .fc-chalan-page .fc-hero {
        background: linear-gradient(135deg, #1e3a5f 0%, var(--fc-accent) 55%, #0ea5e9 100%);
        color: #fff;
        border-radius: 14px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 12px 28px rgba(37, 99, 235, 0.22);
    }
    .fc-chalan-page .fc-hero h2 { font-size: 1.35rem; font-weight: 700; margin: 0 0 .35rem; }
    .fc-chalan-page .fc-hero p { margin: 0; opacity: .95; font-size: .95rem; line-height: 1.5; }
    .fc-chalan-page .fc-card {
        background: #fff;
        border: 1px solid var(--fc-border);
        border-radius: 12px;
        padding: 1.25rem 1.35rem;
        margin-bottom: 1rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    }
    .fc-chalan-page .fc-card h3 {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--fc-muted);
        font-weight: 700;
        margin: 0 0 1rem;
    }
    .fc-chalan-page .fc-view-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }
    @media (max-width: 992px) { .fc-chalan-page .fc-view-grid { grid-template-columns: repeat(2, 1fr); } }
    .fc-chalan-page .fc-view-tile {
        border: 2px solid var(--fc-border);
        border-radius: 10px;
        padding: 14px 10px;
        text-align: center;
        cursor: pointer;
        transition: border-color .2s, box-shadow .2s, transform .15s;
        background: #fff;
    }
    .fc-chalan-page .fc-view-tile:hover { border-color: #93c5fd; transform: translateY(-1px); }
    .fc-chalan-page .fc-view-tile.selected {
        border-color: var(--fc-accent);
        background: linear-gradient(160deg, #eff6ff 0%, #fff 100%);
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.12);
    }
    .fc-chalan-page .fc-view-tile i { font-size: 1.65rem; color: var(--fc-accent); margin-bottom: 8px; display: block; }
    .fc-chalan-page .fc-view-tile.selected i { color: var(--fc-accent2); }
    .fc-chalan-page .fc-view-tile .title { font-weight: 600; font-size: .9rem; color: #0f172a; }
    .fc-chalan-page .fc-view-tile .sub { font-size: .72rem; color: var(--fc-muted); margin-top: 4px; }
    .fc-chalan-page .fc-toolbar { display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; }
    .fc-chalan-page .fc-toolbar .form-group { margin-bottom: 0; min-width: 160px; }
    .fc-chalan-page .fc-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
        padding-top: .5rem;
        border-top: 1px solid var(--fc-border);
        margin-top: 1rem;
    }
    .fc-chalan-page .fc-btn-primary {
        background: linear-gradient(135deg, var(--fc-accent) 0%, var(--fc-accent2) 100%);
        border: none;
        color: #fff;
        font-weight: 600;
        padding: .55rem 1.35rem;
        border-radius: 8px;
    }
    .fc-chalan-page .fc-btn-primary:hover { color: #fff; opacity: .94; }
    .fc-chalan-page .fc-preview {
        background: var(--fc-bg);
        border: 1px dashed var(--fc-border);
        border-radius: 10px;
        padding: .75rem 1rem;
        font-size: .88rem;
        color: #334155;
    }
    .fc-chalan-page .select2-container--bootstrap { width: 100% !important; }
    .fc-chalan-page .fc-collapse-h {
        font-weight: 600;
        color: #0f172a;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .fc-chalan-page .fc-collapse-h:hover { text-decoration: none; color: var(--fc-accent); }
    .fc-chalan-page .char-counter { font-size: 12px; color: var(--fc-muted); text-align: right; margin-top: 4px; }
    .fc-chalan-page .char-counter.warning { color: #d97706; }
    .fc-chalan-page .char-counter.danger { color: #dc2626; }
</style>

<?= view('components/page_header', [
    'title' => 'Generate fee challan',
    'icon' => 'fas fa-file-invoice-dollar',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Fee Chalan', 'url' => base_url('admin/fee_chalan')],
        ['label' => 'Generate challan', 'active' => true],
    ],
]) ?>

<section class="content fc-chalan-page">
    <div class="container-fluid">
        <form action="<?= base_url('admin/fee-chalan/generate') ?>" method="post" id="chalanForm" target="_blank" autocomplete="off">
            <?= csrf_field() ?>
            <div class="fc-hero">
                <h2><i class="fas fa-magic me-2"></i>Fee challan generator</h2>
                <p class="mb-2">
                    <strong>Step 1 — Choose layout.</strong> Then open optional filters only if you need a class, section, or search.
                </p>
                <p class="mb-0 small">
                    Student-wise lists are built from <strong>actual unpaid fee lines</strong> in your database (then class/search narrow the list).
                    Family-wise groups amounts by parent. Leave filters empty to print everyone who still owes fees (can be large — use month or class to shrink).
                </p>
            </div>

            <div class="fc-card">
                <h3><i class="fas fa-th-large me-1"></i>Step 1 — Output type</h3>
                <div class="fc-view-grid">
                    <div class="fc-view-tile <?= ($selected_view ?? 'student_three_copy') === 'student_three_copy' ? 'selected' : '' ?>" data-value="student_three_copy">
                        <i class="fas fa-clone"></i>
                        <div class="title">Student-wise · 3 copies</div>
                        <div class="sub">Each student: Bank, School, Student copy (landscape)</div>
                    </div>
                    <div class="fc-view-tile <?= ($selected_view ?? '') === 'student_single_page' ? 'selected' : '' ?>" data-value="student_single_page">
                        <i class="fas fa-columns"></i>
                        <div class="title">Student-wise · single page</div>
                        <div class="sub">Up to 3 students per printed page</div>
                    </div>
                    <div class="fc-view-tile <?= ($selected_view ?? '') === 'family_three_copy' ? 'selected' : '' ?>" data-value="family_three_copy">
                        <i class="fas fa-home"></i>
                        <div class="title">Family-wise · 3 copies</div>
                        <div class="sub">By family; each child still gets 3 slips</div>
                    </div>
                    <div class="fc-view-tile <?= ($selected_view ?? '') === 'family_single_page' ? 'selected' : '' ?>" data-value="family_single_page">
                        <i class="fas fa-file-alt"></i>
                        <div class="title">Family-wise · one page</div>
                        <div class="sub">All siblings of a family on one sheet</div>
                    </div>
                </div>
                <select name="view_type" id="view_type" class="d-none">
                    <option value="student_three_copy" <?= ($selected_view ?? 'student_three_copy') === 'student_three_copy' ? 'selected' : '' ?>>Student Wise - 3 Copies</option>
                    <option value="student_single_page" <?= ($selected_view ?? '') === 'student_single_page' ? 'selected' : '' ?>>Student Wise - Single Page</option>
                    <option value="family_three_copy" <?= ($selected_view ?? '') === 'family_three_copy' ? 'selected' : '' ?>>Family Wise - 3 Copies</option>
                    <option value="family_single_page" <?= ($selected_view ?? '') === 'family_single_page' ? 'selected' : '' ?>>Family Wise - Single Page</option>
                </select>
                <p class="text-muted small mb-0 mt-2" id="viewHelpText"></p>
            </div>

            <div class="fc-card">
                <h3><i class="fas fa-sliders-h me-1"></i>Step 2 — Quick options</h3>
                <div class="fc-toolbar">
                    <div class="form-group">
                        <label class="d-block mb-1"><strong>Discount column</strong></label>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="show_discount" id="show_discount" value="yes"
                                   <?= ($show_discount ?? 'yes') === 'yes' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="show_discount"><span id="discountLabel"><?= ($show_discount ?? 'yes') === 'yes' ? 'Show' : 'Hide' ?></span></label>
                        </div>
                    </div>
                    <div class="form-group flex-grow-1" style="min-width: 200px;">
                        <label for="fee_month"><strong>Fee month</strong> <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="month" class="form-control" name="fee_month" id="fee_month" value="<?= esc($fee_month ?? '') ?>">
                        <small class="text-muted">Empty = all unpaid months</small>
                    </div>
                    <div class="form-group">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="show_payment_history" id="show_payment_history" value="1"
                                   <?= !empty($show_payment_history) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="show_payment_history"><strong>Payment history</strong></label>
                        </div>
                        <small class="text-muted d-block">Last 12 months</small>
                    </div>
                    <div class="form-group">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="fine_after_due_date" id="fine_after_due_date" value="1"
                                   <?= !empty($fine_after_due_date) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="fine_after_due_date"><strong>Late fine line</strong></label>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="selected_student_id" id="selected_student_id" value="">
            <input type="hidden" name="selected_parent_id" id="selected_parent_id" value="">

            <div class="fc-card">
                <a class="fc-collapse-h" data-bs-toggle="collapse" href="#fcOptionalFilters" role="button" aria-expanded="true" aria-controls="fcOptionalFilters">
                    <i class="fas fa-filter"></i> Step 3 — Optional filters <small class="text-muted fw-normal">(class, section, search…)</small>
                    <i class="fas fa-chevron-down small ms-1"></i>
                </a>
                <div class="collapse show mt-3" id="fcOptionalFilters">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="form-group">
                                <label><strong>Search student or family</strong></label>
                                <select class="form-control" id="search_select" name="search">
                                    <option value="">Type at least 3 characters…</option>
                                </select>
                                <input type="hidden" name="selected_item_id" id="selected_item_id" value="">
                                <small class="text-muted" id="searchHelp">Name, father name, or registration number</small>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label><strong>Family ID</strong></label>
                                <input type="number" class="form-control" name="family_id" id="family_id" value="<?= esc($family_id ?? '') ?>" placeholder="Parent / family id">
                            </div>
                        </div>
                    </div>
                    <div class="row" id="classFilters" style="<?= (strpos($selected_view ?? 'student_three_copy', 'student') !== false) ? '' : 'display:none;' ?>">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Class</strong></label>
                                <select name="class_id" id="class_id" class="form-control select2">
                                    <option value="">All classes</option>
                                    <?php foreach ($classes ?? [] as $class): ?>
                                        <option value="<?= esc($class['class_id']) ?>" <?= (string) ($class_id ?? '') === (string) $class['class_id'] ? 'selected' : '' ?>>
                                            <?= esc($class['class_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Section</strong></label>
                                <select name="section_id" id="section_id" class="form-control select2">
                                    <option value="">All sections</option>
                                    <?php foreach ($sectionsclassinfo ?? [] as $section): ?>
                                        <?php
                                        $secVal = (string) ($section['cls_sec_id'] ?? '');
                                        $selSec = (string) ($section_id ?? '');
                                        $secSelected = ($selSec !== '' && ($selSec === $secVal || $selSec === (string) ($section['section_id'] ?? '')));
                                        ?>
                                        <option value="<?= esc($secVal) ?>" <?= $secSelected ? 'selected' : '' ?>>
                                            <?= esc($section['sectionclassname'] ?? '') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fc-card">
                <a class="fc-collapse-h" data-bs-toggle="collapse" href="#fcMessageBlock" role="button" aria-expanded="false" aria-controls="fcMessageBlock">
                    <i class="fas fa-comment-alt"></i> Message on challan <small class="text-muted fw-normal">(optional)</small>
                    <i class="fas fa-chevron-down small ms-1"></i>
                </a>
                <div class="collapse mt-3" id="fcMessageBlock">
                    <div class="d-flex flex-wrap mb-3">
                        <div class="form-check form-check me-4 mb-2">
                            <input class="form-check-input" type="radio" name="message_position" id="msg_pos_header" value="header"
                                   <?= (!isset($message_position) || $message_position === 'header') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="msg_pos_header">Header</label>
                        </div>
                        <div class="form-check form-check me-4 mb-2">
                            <input class="form-check-input" type="radio" name="message_position" id="msg_pos_footer" value="footer"
                                   <?= ($message_position ?? '') === 'footer' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="msg_pos_footer">Footer</label>
                        </div>
                        <div class="form-check form-check me-4 mb-2">
                            <input class="form-check-input" type="radio" name="message_position" id="msg_pos_none" value="none"
                                   <?= ($message_position ?? '') === 'none' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="msg_pos_none">Do not show</label>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label>Message text <span class="text-muted">(max 200)</span></label>
                        <textarea class="form-control" name="message_text" id="message_text" rows="2" maxlength="200" placeholder="Short note on every slip…"><?= esc($message_text ?? '') ?></textarea>
                        <div class="char-counter" id="charCounter"><span id="charCount">0</span>/200</div>
                    </div>
                </div>
            </div>

            <div class="fc-card">
                <div class="fc-actions">
                    <button type="button" class="btn btn-outline-secondary" id="fcResetBtn"><i class="fas fa-undo me-1"></i> Clear filters</button>
                    <button type="submit" class="fc-btn-primary" id="generateBtn"><i class="fas fa-external-link-alt me-2"></i>Open challans</button>
                </div>
                <div class="fc-preview mt-3 mb-0">
                    <strong>Summary:</strong> <span id="previewText"><?php
                    $selectedView = $selected_view ?? 'student_three_copy';
                    $showDiscount = $show_discount ?? 'yes';
                    $feeMonth = $fee_month ?? '';
                    $messagePos = $message_position ?? 'header';
                    $preview = '';
                    if (strpos($selectedView, 'student') !== false) {
                        $preview .= 'Student · ';
                        $preview .= strpos($selectedView, 'three_copy') !== false ? '3 copies' : 'single page';
                    } else {
                        $preview .= 'Family · ';
                        $preview .= strpos($selectedView, 'three_copy') !== false ? '3 copies' : 'single page';
                    }
                    $preview .= ' · Discount ' . ($showDiscount === 'yes' ? 'on' : 'off');
                    $preview .= ' · Month: ' . (!empty($feeMonth) ? $feeMonth : 'all unpaid');
                    $preview .= ' · Message: ' . ($messagePos === 'none' ? 'off' : $messagePos);
                    echo esc($preview);
                    ?></span>
                </div>
            </div>
        </form>
    </div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    function csrfPair() {
        if (window.adminCsrfPayload) {
            return window.adminCsrfPayload();
        }
        var $i = $('#chalanForm input[name="csrf_test_name"]');
        if (!$i.length && window.ADMIN_CSRF) {
            var o = {};
            o[window.ADMIN_CSRF.name] = window.ADMIN_CSRF.hash;
            return o;
        }
        if (!$i.length) {
            return {};
        }
        var o = {};
        o[$i.attr('name')] = $i.val();
        return o;
    }

    $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
    initSearch();
    syncViewTiles();

    $('.fc-view-tile').on('click', function() {
        $('.fc-view-tile').removeClass('selected');
        $(this).addClass('selected');
        $('#view_type').val($(this).data('value')).trigger('change');
    });

    function syncViewTiles() {
        var v = $('#view_type').val();
        $('.fc-view-tile').removeClass('selected');
        $('.fc-view-tile[data-value="' + v + '"]').addClass('selected');
    }

    $('#view_type').on('change', function(ev, meta) {
        syncViewTiles();
        var viewType = $(this).val();
        var isStudentWise = viewType.indexOf('student') !== -1;
        if (!meta || !meta.skipClear) {
            $('#search_select').val(null).trigger('change');
            $('#selected_student_id').val('');
            $('#selected_parent_id').val('');
            $('#family_id').val('');
        }
        if (isStudentWise) {
            $('#classFilters').slideDown();
            $('#searchHelp').text('Name, father name, or registration number');
            initStudentSearch();
        } else {
            $('#classFilters').slideUp();
            $('#searchHelp').text('Father name, CNIC, or contact');
            initFamilySearch();
        }
        updatePreviewText();
        var helpText = '';
        if (viewType === 'student_three_copy') {
            helpText = 'Each student: three separate slips (bank, school, student).';
        } else if (viewType === 'student_single_page') {
            helpText = 'Up to three students per printed page.';
        } else if (viewType === 'family_three_copy') {
            helpText = 'Grouped by family; each student still gets three copies.';
        } else {
            helpText = 'One page per family with all students.';
        }
        $('#viewHelpText').text(helpText);
    });

    $('#show_discount').on('change', function() {
        $('#discountLabel').text($(this).is(':checked') ? 'Show' : 'Hide');
        updatePreviewText();
    });

    $('#fee_month').on('input change', updatePreviewText);
    $('input[name="message_position"]').on('change', updatePreviewText);

    function updatePreviewText() {
        var viewType = $('#view_type').val();
        var isStudentWise = viewType.indexOf('student') !== -1;
        var layout = viewType.indexOf('three_copy') !== -1 ? '3 copies' : 'single page';
        var grouping = isStudentWise ? 'Student' : 'Family';
        var discount = $('#show_discount').is(':checked') ? 'on' : 'off';
        var month = $('#fee_month').val();
        var monthDisplay = month ? month : 'all unpaid';
        var messagePos = $('input[name="message_position"]:checked').val() || 'header';
        var messageDisplay = messagePos === 'none' ? 'off' : messagePos;
        $('#previewText').text(
            grouping + ' · ' + layout + ' · Discount ' + discount + ' · Month: ' + monthDisplay + ' · Message: ' + messageDisplay
        );
    }

    $('#message_text').on('input', function() {
        var count = $(this).val().length;
        $('#charCount').text(count);
        var counter = $('#charCounter');
        counter.removeClass('warning danger');
        if (count > 180) { counter.addClass('warning'); }
        if (count > 195) { counter.addClass('danger'); }
    }).trigger('input');

    $('#class_id').on('change', function() {
        var classId = $(this).val();
        if (classId) { loadSections(classId); }
    });

    $('#search_select').on('select2:select', function(e) {
        var data = e.params.data;
        var viewType = $('#view_type').val();
        var isStudentWise = viewType.indexOf('student') !== -1;
        if (isStudentWise) {
            $('#selected_student_id').val(data.id);
            $('#selected_parent_id').val('');
            $('#family_id').val('');
        } else {
            var parentId = data.parent_id || data.id;
            $('#selected_parent_id').val(parentId);
            $('#selected_student_id').val('');
            $('#family_id').val(parentId);
        }
    });

    $('#search_select').on('select2:clear', function() {
        $('#selected_student_id').val('');
        $('#selected_parent_id').val('');
    });

    function initSearch() {
        if ($('#view_type').val().indexOf('student') !== -1) {
            initStudentSearch();
        } else {
            initFamilySearch();
        }
    }

    function initStudentSearch() {
        if ($('#search_select').hasClass('select2-hidden-accessible')) {
            $('#search_select').select2('destroy');
        }
        $('#search_select').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search student…',
            allowClear: true,
            minimumInputLength: 3,
            ajax: {
                url: '<?= base_url('admin/fee-chalan/search-students') ?>',
                type: 'POST',
                dataType: 'json',
                delay: 300,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                data: function(params) {
                    return $.extend({
                        term: params.term,
                        class_id: $('#class_id').val(),
                        section_id: $('#section_id').val()
                    }, csrfPair());
                },
                processResults: function(data) {
                    if (data && data.error) {
                        return { results: [] };
                    }
                    if (!Array.isArray(data)) {
                        return { results: [] };
                    }
                    return { results: data };
                }
            },
            templateResult: formatStudentResult,
            templateSelection: formatStudentSelection,
            escapeMarkup: function(m) { return m; }
        });
    }

    function initFamilySearch() {
        if ($('#search_select').hasClass('select2-hidden-accessible')) {
            $('#search_select').select2('destroy');
        }
        $('#search_select').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search family…',
            allowClear: true,
            minimumInputLength: 3,
            ajax: {
                url: '<?= base_url('admin/fee-chalan/search-families') ?>',
                type: 'POST',
                dataType: 'json',
                delay: 300,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                data: function(params) {
                    return $.extend({ term: params.term }, csrfPair());
                },
                processResults: function(data) {
                    return { results: Array.isArray(data) ? data : [] };
                }
            },
            templateResult: formatFamilyResult,
            templateSelection: formatFamilySelection,
            escapeMarkup: function(m) { return m; }
        });
    }

    function formatStudentResult(item) {
        if (item.loading) { 
            return item.text; 
        }
        
        var $div = $('<div class="p-2">');
        $div.append('<strong>' + (item.student_name || item.text) + '</strong>');
        
        if (item.reg_no) {
            $div.append('<br><small class="text-muted">Reg: ' + item.reg_no + '</small>');
        }
        if (item.father_name) {
            $div.append('<br><small class="text-muted">Father: ' + item.father_name + '</small>');
        }
        if (item.class_name) {
            $div.append('<br><small class="text-muted">Class: ' + item.class_name);
            if (item.section_name) {
                $div.append(' ' + item.section_name);
            }
            $div.append('</small>');
        }
        
        return $div;
    }

    function formatStudentSelection(item) {
        if (!item.id) { 
            return item.text; 
        }
        return item.student_name || item.text;
    }

    function formatFamilyResult(item) {
        if (item.loading) { 
            return item.text; 
        }
        var $div = $('<div class="p-2">');
        $div.append('<strong>' + (item.father_name || item.text) + '</strong>');
        
        if (item.father_cnic) {
            $div.append('<br><small class="text-muted">CNIC: ' + item.father_cnic + '</small>');
        }
        if (item.children_count) {
            $div.append('<br><small class="text-muted">Children: ' + item.children_count + '</small>');
        }
        
        return $div;
    }

    function formatFamilySelection(item) {
        if (!item.id) { 
            return item.text; 
        }
        return item.father_name || item.text;
    }

    function loadSections(classId) {
        $.ajax({
            url: '<?= base_url('admin/fee-chalan/get-sections-by-class') ?>',
            data: { class_id: classId },
            success: function(data) {
                var $section = $('#section_id');
                $section.html('<option value="">All sections</option>');
                if (data && Array.isArray(data)) {
                    (data || []).forEach(function(s) {
                        var v = (s.cls_sec_id !== undefined && s.cls_sec_id !== null) ? s.cls_sec_id : s.section_id;
                        $section.append('<option value="' + v + '">' + (s.section_name || '') + '</option>');
                    });
                }
                $section.trigger('change');
            },
            error: function(xhr, status, error) {
                console.error('Load sections error:', error);
            }
        });
    }

    $('#fcResetBtn').on('click', function() {
        $('#class_id').val('').trigger('change');
        $('#section_id').val('').trigger('change');
        $('#family_id').val('');
        $('#fee_month').val('');
        $('#search_select').val(null).trigger('change');
        $('#selected_student_id').val('');
        $('#selected_parent_id').val('');
        $('#message_text').val('').trigger('input');
        $('#msg_pos_header').prop('checked', true);
        updatePreviewText();
    });

    $('#chalanForm').on('submit', function() {
        var viewType = $('#view_type').val();
        var isStudentWise = viewType.indexOf('student') !== -1;
        if (isStudentWise) {
            var studentId = $('#selected_student_id').val();
            if (studentId) {
                if ($('#search_select').find('option[value="' + studentId + '"]').length === 0) {
                    var txt = $('#search_select option:selected').text() || 'Selected student';
                    $('#search_select').append(new Option(txt, studentId, true, true));
                }
                $('#search_select').val(studentId).trigger('change');
            }
        } else {
            var parentId = $('#selected_parent_id').val();
            if (parentId) {
                $('#family_id').val(parentId);
            }
        }
        $('#generateBtn').html('<i class="fas fa-spinner fa-spin me-2"></i>Opening…').prop('disabled', true);
        setTimeout(function() {
            $('#generateBtn').html('<i class="fas fa-external-link-alt me-2"></i>Open challans').prop('disabled', false);
        }, 8000);
    });

    if ($('#view_type').val().indexOf('student') !== -1) {
        $('#classFilters').show();
    } else {
        $('#classFilters').hide();
    }
    
    (function setInitialViewHelp() {
        var viewType = $('#view_type').val();
        var helpText = '';
        if (viewType === 'student_three_copy') {
            helpText = 'Each student: three separate slips (bank, school, student).';
        } else if (viewType === 'student_single_page') {
            helpText = 'Up to three students per printed page.';
        } else if (viewType === 'family_three_copy') {
            helpText = 'Grouped by family; each student still gets three copies.';
        } else {
            helpText = 'One page per family with all students.';
        }
        $('#viewHelpText').text(helpText);
    })();
    
    updatePreviewText();
});
</script>
<?= $this->endSection() ?>
