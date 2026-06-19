<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
$schoolinfo = getSchoolInfo();
$campusId = (int) ($info->campus_id ?? session('member_campusid') ?? 0);

$templates = [
    'welcome_sms' => [
        'title' => 'Welcome SMS',
        'context' => 'Admission and onboarding confirmation',
        'value' => (string) ($info->welcome_sms ?? ''),
    ],
    'attendance_sms' => [
        'title' => 'Attendance SMS',
        'context' => 'Daily attendance or absence notification',
        'value' => (string) ($info->attendance_sms ?? ''),
    ],
    'student_fee_sms' => [
        'title' => 'Student Fee SMS',
        'context' => 'Single-student fee reminders and notices',
        'value' => (string) ($info->student_fee_sms ?? ''),
    ],
    'family_fee_sms' => [
        'title' => 'Family Fee SMS',
        'context' => 'Consolidated family fee communication',
        'value' => (string) ($info->family_fee_sms ?? ''),
    ],
];

$placeholders = [
    'first_name' => 'First Name',
    'last_name' => 'Last Name',
    'father_name' => 'Father Name',
    'date' => 'Date',
    'class' => 'Class',
];

ob_start();
?>
<button type="submit" form="message-template-form" id="saveTemplatesBtn" class="btn btn-primary btn-sm">
    <i class="fas fa-save me-1"></i> Save Templates
</button>
<?php
$headerActions = trim(ob_get_clean());
?>

<style>
    .message-template-summary__label {
        display: block;
        margin-bottom: 0.32rem;
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .message-template-summary__value {
        color: #10263f;
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.35;
    }

    .message-template-stack {
        display: grid;
        gap: 1rem;
    }

    .message-template-placeholder-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .message-template-token {
        display: inline-flex;
        align-items: center;
        padding: 0.4rem 0.72rem;
        border: 1px solid #d8e3ee;
        border-radius: 999px;
        background: #f8fbfd;
        color: #33506b;
        font-size: 0.78rem;
        font-weight: 800;
        line-height: 1.2;
    }

    .message-template-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .message-template-panel {
        display: grid;
        gap: 0.85rem;
        padding: 1rem;
        border: 1px solid #dbe6f0;
        border-radius: 12px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbfd 100%);
    }

    .message-template-panel__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .message-template-panel__title {
        margin: 0;
        color: #10263f;
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.25;
    }

    .message-template-panel__meta {
        margin: 0.25rem 0 0;
        color: #64748b;
        font-size: 0.82rem;
        line-height: 1.45;
    }

    .message-template-panel__count {
        flex: 0 0 auto;
        min-width: 5.5rem;
        justify-content: center;
    }

    .message-template-editor {
        min-height: 11rem;
        resize: vertical;
        line-height: 1.55;
    }

    .message-template-panel__tools {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }

    .message-template-panel__tools .btn {
        min-height: 32px;
        border-radius: 999px !important;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .message-template-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.85rem;
    }

    .message-template-actions__buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
    }

    @media (max-width: 991.98px) {
        .message-template-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }
</style>

<?= view('components/page_header', [
    'title' => 'Message Templates',
    'subtitle' => 'Set campus SMS wording for admissions, attendance, and fee communication.',
    'icon' => 'fas fa-comment-dots',
    'actionsHtml' => $headerActions,
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Message Templates', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="row">
        <div class="col-xl-3 col-lg-4">
            <div class="card sms-card sms-index-card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-school me-2"></i>
                        Campus Setup
                    </h3>
                </div>
                <div class="card-body message-template-stack">
                    <div>
                        <span class="message-template-summary__label">School</span>
                        <div class="message-template-summary__value"><?= esc($schoolinfo->system_name ?? 'Current Campus') ?></div>
                    </div>
                    <div>
                        <span class="message-template-summary__label">Template Library</span>
                        <div class="message-template-summary__value"><?= count($templates) ?> Active Templates</div>
                    </div>
                    <div class="sms-section-note">
                        <i class="fas fa-info-circle"></i>
                        These templates are stored at campus level and reused in day-to-day student communication.
                    </div>
                </div>
            </div>

            <div class="card sms-card sms-index-card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tags me-2"></i>
                        Reusable Fields
                    </h3>
                </div>
                <div class="card-body">
                    <div class="message-template-placeholder-list">
                        <?php foreach ($placeholders as $token => $label) : ?>
                            <span class="message-template-token">{<?= esc($token) ?>}</span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-9 col-lg-8">
            <form id="message-template-form" action="<?= esc(base_url('admin/message-templates/save'), 'attr') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= esc((string) $campusId, 'attr') ?>">

                <div class="card sms-card sms-index-card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-edit me-2"></i>
                            Template Editor
                        </h3>
                        <div class="card-tools">
                            <span class="sms-data-chip">
                                <i class="fas fa-envelope-open-text"></i>
                                SMS ready
                            </span>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="message-template-grid">
                            <?php foreach ($templates as $field => $template) : ?>
                                <section class="message-template-panel">
                                    <div class="message-template-panel__head">
                                        <div>
                                            <h4 class="message-template-panel__title"><?= esc($template['title']) ?></h4>
                                            <p class="message-template-panel__meta"><?= esc($template['context']) ?></p>
                                        </div>
                                        <span class="badge text-bg-light border message-template-panel__count">
                                            <span data-char-count="<?= esc($field, 'attr') ?>">0</span>&nbsp;chars
                                        </span>
                                    </div>

                                    <textarea
                                        class="form-control message-template-editor"
                                        rows="6"
                                        id="<?= esc($field, 'attr') ?>"
                                        name="<?= esc($field, 'attr') ?>"
                                        data-char-source="<?= esc($field, 'attr') ?>"
                                    ><?= esc($template['value']) ?></textarea>

                                    <div class="message-template-panel__tools">
                                        <?php foreach ($placeholders as $token => $label) : ?>
                                            <button
                                                type="button"
                                                class="btn btn-outline-secondary btn-sm template-token-btn"
                                                data-target="<?= esc($field, 'attr') ?>"
                                                data-token="<?= esc($token, 'attr') ?>"
                                            >
                                                <?= esc($label) ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                </section>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="message-template-actions">
                            <div class="sms-section-note" id="messageTemplateSaveState">
                                <i class="fas fa-circle-notch"></i>
                                Ready to save
                            </div>

                            <div class="message-template-actions__buttons">
                                <button type="submit" class="btn btn-primary" id="saveTemplatesFooterBtn">
                                    <i class="fas fa-save me-1"></i> Save Templates
                                </button>
                                <button type="reset" class="btn btn-outline-secondary" id="resetTemplatesBtn">
                                    <i class="fas fa-undo me-1"></i> Reset
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="history.go(-1);">
                                    <i class="fas fa-arrow-left me-1"></i> Back
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
$(function () {
    const $form = $('#message-template-form');
    const $saveButtons = $('#saveTemplatesBtn, #saveTemplatesFooterBtn');
    const $status = $('#messageTemplateSaveState');
    const $editors = $('.message-template-editor');

    function updateCharCount($field) {
        const fieldId = $field.attr('id');
        const count = ($field.val() || '').length;
        $('[data-char-count="' + fieldId + '"]').text(count);
    }

    function updateAllCounts() {
        $editors.each(function () {
            updateCharCount($(this));
        });
    }

    function insertToken(targetId, token) {
        const field = document.getElementById(targetId);
        if (!field) {
            return;
        }

        const placeholder = '{' + token + '}';
        const start = field.selectionStart || 0;
        const end = field.selectionEnd || 0;
        const value = field.value || '';

        field.value = value.substring(0, start) + placeholder + value.substring(end);
        field.selectionStart = field.selectionEnd = start + placeholder.length;
        field.focus();

        updateCharCount($(field));
        $(field).trigger('input');
    }

    updateAllCounts();

    if (typeof autosize === 'function') {
        autosize(document.querySelectorAll('.message-template-editor'));
    }

    $editors.on('input keyup change', function () {
        updateCharCount($(this));
    });

    $('.template-token-btn').on('click', function () {
        insertToken($(this).data('target'), $(this).data('token'));
    });

    $('#resetTemplatesBtn').on('click', function () {
        window.setTimeout(function () {
            updateAllCounts();
            $status.html('<i class="fas fa-circle-notch"></i> Reset to loaded values');
        }, 0);
    });

    $form.on('submit', function (event) {
        event.preventDefault();

        const originalText = $saveButtons.first().html();
        $saveButtons.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving');
        $status.html('<i class="fas fa-spinner fa-spin"></i> Saving templates');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            dataType: 'json'
        }).done(function (response) {
            if (response && response.success) {
                toastr.success(response.msg || 'Message templates updated successfully');
                $status.html('<i class="fas fa-check-circle"></i> Saved successfully');
                return;
            }

            toastr.error((response && response.msg) || 'Unable to save message templates');
            $status.html('<i class="fas fa-exclamation-circle"></i> Save failed');
        }).fail(function () {
            toastr.error('Request failed while saving message templates');
            $status.html('<i class="fas fa-exclamation-circle"></i> Save failed');
        }).always(function () {
            $saveButtons.prop('disabled', false).html(originalText);
        });
    });
});
</script>

<?= $this->endSection() ?>
