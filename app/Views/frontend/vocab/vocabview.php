<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<?php helper(['language', 'server']); ?>
<?php
$isParent = ! empty($is_parent);
$activeStudentId = (int) ($active_student_id ?? 0);
$hasStudent = $activeStudentId > 0 && ! empty($student);
$showPanel = $hasStudent || (! $isParent && $activeStudentId > 0);
$vocabJsLang = [
    'viewList'       => lang('ParentPortal.vocab_view_list'),
    'viewFlashcards' => lang('ParentPortal.vocab_view_flashcards'),
    'hint'           => lang('ParentPortal.vocab_flash_hint'),
    'flip'           => lang('ParentPortal.vocab_flash_flip'),
    'prev'           => lang('ParentPortal.vocab_flash_prev'),
    'next'           => lang('ParentPortal.vocab_flash_next'),
    'counterTpl'     => lang('ParentPortal.vocab_flash_counter'),
    'subject'        => lang('ParentPortal.vocab_flash_subject'),
    'topic'          => lang('ParentPortal.vocab_flash_topic'),
    'meaningEn'      => lang('ParentPortal.vocab_flash_meaning_en'),
    'meaningUr'      => lang('ParentPortal.vocab_flash_meaning_ur'),
    'example'        => lang('ParentPortal.vocab_flash_example'),
    'extra'          => lang('ParentPortal.vocab_flash_extra'),
    'swipeHint'      => lang('ParentPortal.vocab_flash_swipe_hint'),
    'frontTip'       => lang('ParentPortal.vocab_flash_front_tip'),
    'backIntro'      => lang('ParentPortal.vocab_flash_back_intro'),
    'antonymsLbl'    => lang('ParentPortal.vocab_flash_antonyms'),
    'relatedLbl'     => lang('ParentPortal.vocab_flash_related'),
    'syllablesLbl'   => lang('ParentPortal.vocab_flash_syllables'),
    'listenWord'     => lang('ParentPortal.vocab_listen_word'),
    'listenEn'       => lang('ParentPortal.vocab_listen_meaning_en'),
    'listenUr'       => lang('ParentPortal.vocab_listen_meaning_ur'),
    'listenEx'       => lang('ParentPortal.vocab_listen_example'),
    'pictureAlt'     => lang('ParentPortal.vocab_picture_alt'),
    'ttsHint'        => lang('ParentPortal.vocab_tts_hint'),
    'commonsCreditLink' => lang('ParentPortal.vocab_commons_credit_link'),
    'commonsCreditTitle' => lang('ParentPortal.vocab_commons_credit_title'),
    'pictureCaptionLabel' => lang('ParentPortal.vocab_picture_caption_label'),
    'portalLang'     => session('language') ?? 'en',
];
?>
<link rel="stylesheet" href="<?= base_url('assets/css/parent_portal_subpages.css') ?>">

<section class="content parent-subpage-content pt-2 parent-portal-vocab">
    <div class="container-fluid px-2 px-md-3">
        <?php if ($isParent): ?>
        <div class="ds-page-layout">
            <aside class="ds-page-layout__filter ds-sticky-filter-mobile" aria-label="<?= esc(lang('ParentPortal.vocab_aria_student_selection')) ?>">
                <div class="ds-datesheet-filter">
                    <?= view('frontend/partials/parent_child_selector', [
                        'children'        => $children ?? [],
                        'activeStudentId' => $activeStudentId,
                        'returnPath'      => 'student/vocabbank',
                    ]) ?>
                </div>
            </aside>
            <div class="ds-page-layout__content" aria-label="<?= esc(lang('ParentPortal.vocab_aria_panel')) ?>">
        <?php endif; ?>

        <?php if ($showPanel): ?>
            <div id="vocab-section" class="parent-subpage-panel ds-datesheet-content-panel" aria-label="<?= esc(lang('ParentPortal.vocab_aria_panel')) ?>">
                <div class="parent-subpage-title-row align-items-center flex-wrap">
                    <h2 class="parent-subpage-title mb-0">
                        <i class="fa fa-book me-2 text-primary" aria-hidden="true"></i>
                        <?= lang('ParentPortal.vocab_page_title') ?>
                    </h2>
                    <?php
                    $__cn = trim((string) ($student['class_name'] ?? ''));
                    $__sn = trim((string) ($student['section_name'] ?? ''));
                    $__clsBadge = ($__cn !== '' && $__sn !== '') ? ($__cn . ' - ' . $__sn) : ($__cn !== '' ? $__cn : $__sn);
                    ?>
                    <?php if ($__clsBadge !== ''): ?>
                        <span class="badge text-bg-primary ms-md-2 mt-1 mt-md-0"><?= esc($__clsBadge) ?></span>
                    <?php endif; ?>
                </div>

                <?php if (! empty($error)): ?>
                    <div class="alert alert-warning mb-3" role="alert">
                        <i class="fa fa-exclamation-triangle me-2" aria-hidden="true"></i>
                        <?= esc($error) ?>
                    </div>
                <?php else: ?>
                    <div id="vocabLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden"><?= esc(lang('ParentPortal.vocab_loading')) ?></span>
                        </div>
                        <p class="mt-3 text-muted mb-0"><?= lang('ParentPortal.vocab_loading') ?></p>
                    </div>

                    <div id="vocabToolbar" class="vocab-mode-toolbar mb-3" style="display: none;" role="group" aria-label="<?= esc(lang('ParentPortal.vocab_page_title')) ?>">
                        <div class="vocab-mode-pills">
                            <button type="button" id="vocabModeList" class="vocab-mode-btn vocab-mode-btn--active" aria-pressed="true">
                                <i class="fa fa-list-ul me-1" aria-hidden="true"></i>
                                <span><?= esc(lang('ParentPortal.vocab_view_list')) ?></span>
                            </button>
                            <button type="button" id="vocabModeFlash" class="vocab-mode-btn" aria-pressed="false">
                                <i class="fa fa-columns me-1" aria-hidden="true"></i>
                                <span><?= esc(lang('ParentPortal.vocab_view_flashcards')) ?></span>
                            </button>
                        </div>
                    </div>

                    <div id="vocabularyContent" class="vocab-list-wrap" style="display: none;" aria-live="polite"></div>

                    <div id="vocabFlashcardRoot" class="vocab-flash-root" style="display: none;">
                        <p class="vocab-flash-hint small text-center mb-2 mb-md-3" id="vocabFlashHintText"></p>
                        <p class="vocab-flash-tts-hint small text-center text-muted mb-2 mb-md-3" id="vocabFlashTtsHint" style="display: none;"></p>
                        <div class="vocab-flash-counter text-center mb-2" id="vocabFlashCounter" aria-live="polite"></div>
                        <div class="vocab-flash-progress mb-3" id="vocabFlashProgress" role="progressbar" aria-valuemin="1" aria-valuenow="1" aria-valuemax="1" aria-label="<?= esc(lang('ParentPortal.vocab_view_flashcards')) ?>">
                            <div class="vocab-flash-progress-track">
                                <div class="vocab-flash-progress-fill" id="vocabFlashProgressFill" style="width: 0%;"></div>
                            </div>
                        </div>
                        <div class="vocab-flash-scene" id="vocabFlashScene">
                            <div class="vocab-flash-card vocab-flash-card--tap" id="vocabFlashCardBtn" role="button" tabindex="0" aria-label="<?= esc(lang('ParentPortal.vocab_flash_flip')) ?>">
                                <div class="vocab-flash-inner" id="vocabFlashInner">
                                    <div class="vocab-flash-face vocab-flash-front" id="vocabFlashFront"></div>
                                    <div class="vocab-flash-face vocab-flash-back" id="vocabFlashBack"></div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mb-3">
                            <button type="button" class="btn btn-lg btn-outline-primary rounded-pill px-4 vocab-flip-btn" id="vocabFlipBtn">
                                <i class="fa fa-refresh me-2" aria-hidden="true"></i><span id="vocabFlipBtnLabel"></span>
                            </button>
                        </div>
                        <div class="vocab-flash-nav row g-0 mx-n1">
                            <div class="col-6 px-1">
                                <button type="button" class="btn btn-lg w-100 vocab-flash-nav-btn vocab-flash-nav-btn--prev" id="vocabPrevBtn">
                                    <i class="fa fa-arrow-left me-1" aria-hidden="true"></i><span id="vocabPrevLabel"></span>
                                </button>
                            </div>
                            <div class="col-6 px-1">
                                <button type="button" class="btn btn-lg w-100 vocab-flash-nav-btn vocab-flash-nav-btn--next" id="vocabNextBtn">
                                    <span id="vocabNextLabel"></span><i class="fa fa-arrow-right ms-1" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <p class="vocab-flash-swipe-hint small text-center text-muted mt-2 mb-0 d-md-none" id="vocabSwipeHintText"></p>
                    </div>

                    <div id="vocabEmpty" class="text-center py-5" style="display: none;">
                        <i class="fa fa-book fa-3x text-muted mb-3" aria-hidden="true"></i>
                        <h5 class="text-muted"><?= lang('ParentPortal.vocab_empty_title') ?></h5>
                        <p class="text-muted mb-0" id="vocabEmptyMessage"><?= lang('ParentPortal.vocab_empty_default') ?></p>
                    </div>
                <?php endif; ?>

                <div id="vocabError" class="alert alert-danger mt-3" style="display: none;" role="alert">
                    <span id="vocabErrorMessage"></span>
                </div>
            </div>
        <?php elseif ($isParent && $activeStudentId <= 0): ?>
            <div class="parent-subpage-panel ds-datesheet-content-panel text-center py-5">
                <i class="fa fa-users fa-3x text-muted mb-3" aria-hidden="true"></i>
                <h4><?= lang('ParentPortal.vocab_select_student_title') ?></h4>
                <p class="text-muted mb-0"><?= lang('ParentPortal.vocab_select_student_help') ?></p>
            </div>
        <?php endif; ?>

        <?php if ($isParent): ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php if ($showPanel && empty($error)): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const VOCAB_LANG = <?= json_encode($vocabJsLang, JSON_HEX_TAG | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE) ?>;
    const dataUrl = <?= json_encode(base_url('student/vocabbank/data')) ?>;
    const commonsImagesUrl = <?= json_encode(base_url('student/vocabbank/commons-images')) ?>;
    const loadingEl = document.getElementById('vocabLoading');
    const toolbarEl = document.getElementById('vocabToolbar');
    const contentEl = document.getElementById('vocabularyContent');
    const flashRoot = document.getElementById('vocabFlashcardRoot');
    const emptyEl = document.getElementById('vocabEmpty');
    const emptyMsg = document.getElementById('vocabEmptyMessage');
    const errorEl = document.getElementById('vocabError');
    const errorMsg = document.getElementById('vocabErrorMessage');
    const btnList = document.getElementById('vocabModeList');
    const btnFlash = document.getElementById('vocabModeFlash');
    const flashCardBtn = document.getElementById('vocabFlashCardBtn');
    const flashInner = document.getElementById('vocabFlashInner');
    const flashFront = document.getElementById('vocabFlashFront');
    const flashBack = document.getElementById('vocabFlashBack');
    const flashCounter = document.getElementById('vocabFlashCounter');
    const flashProgress = document.getElementById('vocabFlashProgress');
    const flashProgressFill = document.getElementById('vocabFlashProgressFill');
    const btnFlip = document.getElementById('vocabFlipBtn');
    const btnPrev = document.getElementById('vocabPrevBtn');
    const btnNext = document.getElementById('vocabNextBtn');
    const hintText = document.getElementById('vocabFlashHintText');
    const ttsFlashHint = document.getElementById('vocabFlashTtsHint');
    const swipeHint = document.getElementById('vocabSwipeHintText');
    const flipLabel = document.getElementById('vocabFlipBtnLabel');
    const prevLabel = document.getElementById('vocabPrevLabel');
    const nextLabel = document.getElementById('vocabNextLabel');

    let flatWords = [];
    let flashIndex = 0;
    let mode = 'list';
    let touchStartX = null;

    if (hintText) {
        hintText.textContent = VOCAB_LANG.hint;
    }
    if (swipeHint) {
        swipeHint.textContent = VOCAB_LANG.swipeHint;
    }
    if (flipLabel) {
        flipLabel.textContent = VOCAB_LANG.flip;
    }
    if (prevLabel) {
        prevLabel.textContent = VOCAB_LANG.prev;
    }
    if (nextLabel) {
        nextLabel.textContent = VOCAB_LANG.next;
    }

    function canTTS() {
        return typeof window.speechSynthesis !== 'undefined' &&
            typeof SpeechSynthesisUtterance !== 'undefined';
    }

    function speakText(text, lang) {
        if (!canTTS() || !text) {
            return;
        }
        const t = String(text).trim();
        if (!t) {
            return;
        }
        try {
            window.speechSynthesis.cancel();
            const utt = new SpeechSynthesisUtterance(t);
            utt.lang = lang || 'en-US';
            utt.rate = 0.92;
            window.speechSynthesis.speak(utt);
        } catch (e1) {}
    }

    function speakBtn(text, bcp47, title) {
        if (!canTTS()) {
            return '';
        }
        const t = String(text || '').trim();
        if (!t) {
            return '';
        }
        return '<button type="button" class="vocab-speak-btn" data-vocab-speak="' + encodeURIComponent(t) +
            '" data-vocab-lang="' + escapeHtml(bcp47) + '" title="' + escapeHtml(title) + '" aria-label="' + escapeHtml(title) + '">' +
            '<i class="fa fa-volume-up" aria-hidden="true"></i></button>';
    }

    function exampleTtsLang() {
        const pl = String(VOCAB_LANG.portalLang || 'en').toLowerCase().slice(0, 2);
        if (pl === 'ur') {
            return 'ur-PK';
        }
        if (pl === 'ar') {
            return 'ar-SA';
        }
        return 'en-US';
    }

    function escapeHtml(str) {
        if (typeof str !== 'string') {
            return '';
        }
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function isTrustedCommonsImageUrl(u) {
        if (typeof u !== 'string' || u.indexOf('https://') !== 0) {
            return false;
        }
        return /^https:\/\/upload\.wikimedia\.org\//i.test(u);
    }

    function isTrustedCommonsFilePage(u) {
        return typeof u === 'string' && /^https:\/\/commons\.wikimedia\.org\/wiki\//i.test(u);
    }

    function commonsListIllusMarkup(img, vocabId) {
        if (!img || !isTrustedCommonsImageUrl(img.url)) {
            return '';
        }
        let credit = '';
        if (isTrustedCommonsFilePage(img.file_page)) {
            credit = '<div class="vocab-commons-credit"><a href="' + escapeHtml(img.file_page) + '" target="_blank" rel="noopener noreferrer" title="' +
                escapeHtml(VOCAB_LANG.commonsCreditTitle) + '">' + escapeHtml(VOCAB_LANG.commonsCreditLink) + '</a></div>';
        }
        const desc = String(img.description || '').trim();
        const capId = 'vocab-illus-cap-' + Number(vocabId);
        let cap = '';
        let imgExtras = '';
        if (desc) {
            cap = '<p class="vocab-commons-caption" id="' + escapeHtml(capId) + '"><span class="visually-hidden">' + escapeHtml(VOCAB_LANG.pictureCaptionLabel) + ': </span>' +
                escapeHtml(desc) + '</p>';
            imgExtras = ' aria-describedby="' + escapeHtml(capId) + '"';
        }
        return '<div class="vocab-word-illus vocab-word-illus--commons">' +
            '<img src="' + escapeHtml(img.url) + '" alt="' + escapeHtml(VOCAB_LANG.pictureAlt) + '" class="vocab-word-illus__img" loading="lazy" decoding="async"' + imgExtras + '/>' +
            credit + cap + '</div>';
    }

    function commonsFlashCreditMarkup(w) {
        if (!w || !isTrustedCommonsFilePage(w.commons_file_page)) {
            return '';
        }
        return '<div class="vocab-flash-commons-credit">' +
            '<a href="' + escapeHtml(w.commons_file_page) + '" target="_blank" rel="noopener noreferrer" class="vocab-flash-commons-credit__link" title="' +
            escapeHtml(VOCAB_LANG.commonsCreditTitle) + '">' + escapeHtml(VOCAB_LANG.commonsCreditLink) + '</a></div>';
    }

    function applyCommonsImages(images) {
        if (!images || !images.length) {
            return;
        }
        images.forEach(function (img) {
            const idNum = Number(img.id);
            if (!idNum || !img.url || !isTrustedCommonsImageUrl(img.url)) {
                return;
            }
            flatWords.forEach(function (w) {
                if (Number(w.id) === idNum) {
                    w.illustration_url = img.url;
                    if (isTrustedCommonsFilePage(img.file_page)) {
                        w.commons_file_page = img.file_page;
                    }
                    const d = String(img.description || '').trim();
                    if (d) {
                        w.commons_description = d;
                    }
                }
            });
            if (contentEl) {
                const row = contentEl.querySelector('.vocab-word-row[data-vocab-id="' + idNum + '"]');
                if (row && !row.querySelector('.vocab-word-illus')) {
                    row.insertAdjacentHTML('afterbegin', commonsListIllusMarkup(img, idNum));
                }
            }
            if (mode === 'flash' && flatWords[flashIndex] && Number(flatWords[flashIndex].id) === idNum) {
                renderFlashCard();
            }
        });
    }

    function extractImageDescFromExt(ext) {
        if (!ext || typeof ext !== 'object') {
            return '';
        }
        var keys = ['ImageDescription', 'ObjectName'];
        for (var ki = 0; ki < keys.length; ki++) {
            var block = ext[keys[ki]];
            if (!block || typeof block.value !== 'string') {
                continue;
            }
            var tmp = document.createElement('div');
            tmp.innerHTML = block.value;
            var v = String(tmp.textContent || '').replace(/\s+/g, ' ').trim();
            if (v) {
                return v.length > 320 ? v.slice(0, 320) : v;
            }
        }
        return '';
    }

    function extractFirstThumbFromCommonsJson(json) {
        var pagesRaw = json && json.query && json.query.pages;
        if (!pagesRaw) {
            return null;
        }
        var pagesList = Array.isArray(pagesRaw) ? pagesRaw : Object.keys(pagesRaw).map(function (k) {
            return pagesRaw[k];
        });
        var page = pagesList[0];
        if (!page || page.missing) {
            return null;
        }
        var iiList = page.imageinfo;
        var ii = iiList && iiList[0];
        if (!ii) {
            return null;
        }
        var mime = String(ii.mime || '');
        var okMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        if (okMime.indexOf(mime) === -1) {
            return null;
        }
        var thumb = String(ii.thumburl || '');
        var full = String(ii.url || '');
        var url = '';
        if (isTrustedCommonsImageUrl(thumb)) {
            url = thumb;
        } else if (isTrustedCommonsImageUrl(full)) {
            url = full;
        }
        if (!url) {
            return null;
        }
        var title = String(page.title || '');
        var filePage = '';
        if (title.indexOf('File:') === 0) {
            filePage = 'https://commons.wikimedia.org/wiki/' + encodeURIComponent(title.replace(/ /g, '_'));
        }
        return {
            url: url,
            file_page: filePage,
            description: extractImageDescFromExt(ii.extmetadata),
        };
    }

    function meaningKeywordsBrowser(meaningEn, maxN) {
        var raw = String(meaningEn || '').trim().toLowerCase();
        if (!raw) {
            return [];
        }
        var parts = raw.toLowerCase().split(/[^a-z0-9'\-]+/i).filter(Boolean);
        var stop = {
            a: 1, an: 1, the: 1, to: 1, of: 1, and: 1, or: 1, in: 1, on: 1, at: 1, for: 1, with: 1, by: 1, from: 1,
            is: 1, are: 1, was: 1, were: 1, be: 1, been: 1, being: 1, it: 1, its: 1, this: 1, that: 1, these: 1, those: 1,
            as: 1, also: 1, not: 1, no: 1, yes: 1, but: 1, if: 1, than: 1, then: 1, into: 1, about: 1, such: 1,
        };
        var out = [];
        for (var i = 0; i < parts.length; i++) {
            var p = parts[i].replace(/^[\-']+|[\-']+$/g, '');
            if (p.length < 2 || stop[p]) {
                continue;
            }
            out.push(p);
            if (out.length >= maxN) {
                break;
            }
        }
        return out;
    }

    function commonsSearchQueriesBrowser(term, meaningEn) {
        var t = String(term || '').trim();
        if (!t) {
            return [];
        }
        var out = [t];
        var kw = meaningKeywordsBrowser(meaningEn, 4);
        if (kw.length) {
            out.push(t + ' ' + kw.join(' '));
            out.push(kw.join(' '));
        }
        out.push(t + ' photograph');
        var seen = {};
        return out.filter(function (q) {
            q = q.trim();
            if (!q || seen[q]) {
                return false;
            }
            seen[q] = true;
            return true;
        });
    }

    function fetchCommonsThumbBrowser(term, meaningEn) {
        var queries = commonsSearchQueriesBrowser(term, meaningEn);
        if (!queries.length || typeof fetch !== 'function') {
            return Promise.resolve(null);
        }
        var i = 0;
        function tryNext() {
            if (i >= queries.length) {
                return Promise.resolve(null);
            }
            var q = queries[i++];
            var params = new URLSearchParams({
                action: 'query',
                generator: 'search',
                gsrnamespace: '6',
                gsrsearch: q,
                gsrlimit: '1',
                prop: 'imageinfo',
                iiprop: 'url|mime|thumburl|extmetadata',
                iiurlwidth: '420',
                format: 'json',
                formatversion: '2',
                origin: '*',
            });
            return fetch('https://commons.wikimedia.org/w/api.php?' + params.toString(), {
                credentials: 'omit',
                cache: 'no-store',
            })
                .then(function (r) {
                    return r.json();
                })
                .then(function (json) {
                    var hit = extractFirstThumbFromCommonsJson(json);
                    return hit || tryNext();
                })
                .catch(function () {
                    return tryNext();
                });
        }
        return tryNext();
    }

    /**
     * When server-side curl cannot reach Wikimedia, fill thumbnails from the browser (CORS + origin=*).
     */
    function commonsBrowserFallback(part, serverImages) {
        if (!part || !part.length || typeof fetch !== 'function') {
            return;
        }
        var chain = Promise.resolve();
        part.forEach(function (meta) {
            var idNum = Number(meta.id);
            if (!idNum) {
                return;
            }
            var hadUrl = false;
            if (serverImages && serverImages.length) {
                serverImages.forEach(function (im) {
                    if (Number(im.id) === idNum && im.url && isTrustedCommonsImageUrl(im.url)) {
                        hadUrl = true;
                    }
                });
            }
            if (hadUrl) {
                return;
            }
            chain = chain.then(function () {
                return fetchCommonsThumbBrowser(meta.term, meta.meaning_en).then(function (found) {
                    if (found && found.url) {
                        applyCommonsImages([{
                            id: idNum,
                            url: found.url,
                            file_page: found.file_page || '',
                            credit: '',
                            description: found.description || '',
                        }]);
                    }
                    return new Promise(function (res) {
                        setTimeout(res, 120);
                    });
                });
            });
        });
    }

    function hydrateCommonsFromWikimedia() {
        if (typeof jQuery === 'undefined' || !commonsImagesUrl) {
            return;
        }
        const seen = {};
        const items = [];
        flatWords.forEach(function (w) {
            if (!w || w.id == null) {
                return;
            }
            if (w.illustration_url) {
                return;
            }
            const id = Number(w.id);
            if (!id || seen[id]) {
                return;
            }
            seen[id] = true;
            const term = String(w.word || '').trim();
            if (!term) {
                return;
            }
            items.push({
                id: id,
                term: term,
                meaning_en: String(w.meaning_en || '').trim(),
            });
        });
        if (!items.length) {
            return;
        }
        const chunk = 32;
        let i = 0;
        function next() {
            if (i >= items.length) {
                return;
            }
            const part = items.slice(i, i + chunk);
            i += chunk;
            jQuery.ajax({
                url: commonsImagesUrl,
                type: 'POST',
                contentType: 'application/json; charset=UTF-8',
                dataType: 'json',
                data: JSON.stringify({ items: part }),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
            }).done(function (res) {
                if (res && res.status === 'ok' && res.images) {
                    applyCommonsImages(res.images);
                    commonsBrowserFallback(part, res.images);
                } else {
                    commonsBrowserFallback(part, null);
                }
            }).fail(function (xhr, status, err) {
                if (typeof console !== 'undefined' && console.warn) {
                    console.warn('[vocab] commons-images POST failed', status, err, xhr && xhr.status);
                }
                commonsBrowserFallback(part, null);
            }).always(function () {
                next();
            });
        }
        next();
    }

    const vocabShell = document.getElementById('vocab-section');
    if (vocabShell) {
        vocabShell.addEventListener('click', function (e) {
            const btn = e.target.closest('.vocab-speak-btn');
            if (!btn) {
                return;
            }
            e.preventDefault();
            e.stopPropagation();
            const raw = btn.getAttribute('data-vocab-speak');
            if (!raw) {
                return;
            }
            try {
                speakText(decodeURIComponent(raw), btn.getAttribute('data-vocab-lang') || 'en-US');
            } catch (e2) {}
        });
    }

    function showError(message) {
        if (loadingEl) {
            loadingEl.style.display = 'none';
        }
        if (toolbarEl) {
            toolbarEl.style.display = 'none';
        }
        if (contentEl) {
            contentEl.style.display = 'none';
        }
        if (flashRoot) {
            flashRoot.style.display = 'none';
        }
        if (emptyEl) {
            emptyEl.style.display = 'none';
        }
        if (errorEl && errorMsg) {
            errorMsg.innerHTML = message;
            errorEl.style.display = 'block';
        }
    }

    function flattenWords(data) {
        const out = [];
        if (!data.topics_by_subject) {
            return out;
        }
        Object.keys(data.topics_by_subject).forEach(function (subjectName) {
            data.topics_by_subject[subjectName].forEach(function (topic) {
                const topicName = topic.topic_name || '';
                (topic.vocabulary || []).forEach(function (w) {
                    out.push(Object.assign({}, w, {
                        _subject: subjectName,
                        _topic: topicName
                    }));
                });
            });
        });
        return out;
    }

    function buildListHtml(data) {
        let html = '';

        if (data.summary) {
            html += '<div class="vocab-summary-bar mb-3">' +
                '<span class="badge text-bg-primary me-1">' + escapeHtml(String(data.summary.total_topics || 0)) + ' topics</span>' +
                '<span class="badge text-bg-success">' + escapeHtml(String(data.summary.total_words || 0)) + ' words</span>';
            if (data.summary.all_subjects && data.summary.all_subjects.length) {
                html += '<span class="vocab-summary-subjects text-muted small d-block mt-2">' +
                    escapeHtml(data.summary.all_subjects.join(', ')) + '</span>';
            }
            html += '</div>';
        }

        if (data.topics_by_subject && Object.keys(data.topics_by_subject).length > 0) {
            Object.keys(data.topics_by_subject).forEach(function (subjectName) {
                const subjectTopics = data.topics_by_subject[subjectName];
                const totalWords = subjectTopics.reduce(function (n, t) {
                    return n + ((t.vocabulary && t.vocabulary.length) || 0);
                }, 0);

                html += '<div class="vocab-subject-block mb-3">' +
                    '<div class="vocab-subject-head">' +
                    '<i class="fa fa-book me-2" aria-hidden="true"></i>' + escapeHtml(subjectName) +
                    '<span class="badge text-bg-light float-end">' + subjectTopics.length + ' topics · ' + totalWords + ' words</span>' +
                    '</div><div class="vocab-subject-body">';

                subjectTopics.forEach(function (topic, topicIndex) {
                    const words = topic.vocabulary || [];
                    html += '<div class="vocab-topic-card">' +
                        '<div class="vocab-topic-head">' +
                        '<span class="badge text-bg-primary me-2">' + (topicIndex + 1) + '</span>' +
                        escapeHtml(topic.topic_name || 'Topic') +
                        '<span class="badge text-bg-secondary ms-2">' + words.length + ' words</span>' +
                        '</div><div class="vocab-topic-body">';

                    if (words.length > 0) {
                        words.forEach(function (word, idx) {
                            let ill = '';
                            if (word.illustration_url) {
                                ill = '<div class="vocab-word-illus">' +
                                    '<img src="' + escapeHtml(word.illustration_url) + '" alt="' + escapeHtml(VOCAB_LANG.pictureAlt) + '" class="vocab-word-illus__img" loading="lazy" decoding="async"/>' +
                                    '</div>';
                            }
                            html += '<div class="vocab-word-row" data-vocab-id="' + Number(word.id) + '">' + ill +
                                '<div class="vocab-word-head">' +
                                '<strong class="text-primary">' + escapeHtml(word.word) + '</strong>' +
                                speakBtn(word.word, 'en-US', VOCAB_LANG.listenWord);
                            if (word.part_of_speech) {
                                html += '<span class="badge text-bg-info ms-2">' + escapeHtml(word.part_of_speech) + '</span>';
                            }
                            html += '<span class="badge text-bg-light text-dark ms-auto">' + (idx + 1) + '</span></div>';
                            if (word.meaning_en) {
                                html += '<p class="mb-1 small vocab-word-line">' +
                                    '<span class="text-muted">' + escapeHtml(VOCAB_LANG.meaningEn) + ':</span> ' +
                                    escapeHtml(word.meaning_en) + speakBtn(word.meaning_en, 'en-US', VOCAB_LANG.listenEn) + '</p>';
                            }
                            if (word.meaning_ur) {
                                html += '<p class="mb-1 small urdu-text vocab-word-line">' +
                                    '<span class="text-muted">' + escapeHtml(VOCAB_LANG.meaningUr) + ':</span> ' +
                                    escapeHtml(word.meaning_ur) + speakBtn(word.meaning_ur, 'ur-PK', VOCAB_LANG.listenUr) + '</p>';
                            }
                            if (word.example_sentence) {
                                html += '<p class="mb-0 small text-success vocab-word-line">' +
                                    '<span class="text-muted">' + escapeHtml(VOCAB_LANG.example) + ':</span> ' +
                                    escapeHtml(word.example_sentence) + speakBtn(word.example_sentence, exampleTtsLang(), VOCAB_LANG.listenEx) + '</p>';
                            }
                            html += '</div>';
                        });
                    } else {
                        html += '<p class="text-muted text-center mb-0 small">No words in this topic yet.</p>';
                    }
                    html += '</div></div>';
                });
                html += '</div></div>';
            });
        } else if (data.student_subjects && data.student_subjects.length) {
            html += '<div class="alert alert-info mb-0"><ul class="mb-0 ps-3">';
            data.student_subjects.forEach(function (s) {
                html += '<li>' + escapeHtml(s.subject_name) + ': ' +
                    (s.has_vocabulary ? '✓' : '—') + '</li>';
            });
            html += '</ul></div>';
        }

        return html;
    }

    function setFlashFlipped(on) {
        if (!flashCardBtn) {
            return;
        }
        if (on) {
            flashCardBtn.classList.add('is-flipped');
        } else {
            flashCardBtn.classList.remove('is-flipped');
        }
    }

    function renderFlashCard() {
        if (!flatWords.length || !flashFront || !flashBack || !flashCounter) {
            return;
        }
        const w = flatWords[flashIndex];
        const total = flatWords.length;
        const cur = flashIndex + 1;
        const pct = total > 0 ? Math.round((cur / total) * 100) : 0;

        flashCounter.textContent = VOCAB_LANG.counterTpl
            .replace('{current}', String(cur))
            .replace('{total}', String(total));

        if (flashProgressFill) {
            flashProgressFill.style.width = pct + '%';
        }
        if (flashProgress) {
            flashProgress.setAttribute('aria-valuenow', String(cur));
            flashProgress.setAttribute('aria-valuemax', String(total));
        }

        let illFront = '';
        if (w.illustration_url) {
            const flashCapId = 'vocab-flash-cap-' + flashIndex;
            const cdesc = String(w.commons_description || '').trim();
            let imgExtra = '';
            let capBelow = '';
            if (cdesc) {
                imgExtra = ' aria-describedby="' + escapeHtml(flashCapId) + '"';
                capBelow = '<p class="vocab-flash-illus-caption" id="' + escapeHtml(flashCapId) + '"><span class="visually-hidden">' +
                    escapeHtml(VOCAB_LANG.pictureCaptionLabel) + ': </span>' + escapeHtml(cdesc) + '</p>';
            }
            illFront = '<div class="vocab-flash-illus-wrap">' +
                '<img src="' + escapeHtml(w.illustration_url) + '" alt="' + escapeHtml(VOCAB_LANG.pictureAlt) +
                '" class="vocab-flash-illus-img" loading="lazy" decoding="async"' + imgExtra + '/>' +
                '</div>' + capBelow + commonsFlashCreditMarkup(w);
        }
        let frontHtml = '<div class="vocab-flash-front-inner">' +
            '<div class="vocab-flash-front-badge" aria-hidden="true"><span class="vocab-flash-front-badge__icon">✨</span> ' +
            escapeHtml(VOCAB_LANG.viewFlashcards) + '</div>' +
            illFront +
            '<div class="vocab-flash-word-row">' +
            '<span class="vocab-flash-word">' + escapeHtml(w.word || '') + '</span>' +
            speakBtn(w.word, 'en-US', VOCAB_LANG.listenWord) +
            '</div>';
        if (w.part_of_speech) {
            frontHtml += '<div class="vocab-flash-pos-wrap"><span class="vocab-flash-pos">' + escapeHtml(w.part_of_speech) + '</span></div>';
        }
        frontHtml += '<p class="vocab-flash-front-tip">' + escapeHtml(VOCAB_LANG.frontTip) + '</p>' +
            '<div class="vocab-flash-meta" role="note">' +
            '<div class="vocab-flash-meta-row"><span class="vocab-flash-meta-k">' + escapeHtml(VOCAB_LANG.subject) + '</span> ' +
            '<span class="vocab-flash-meta-v">' + escapeHtml(w._subject || '') + '</span></div>' +
            '<div class="vocab-flash-meta-row"><span class="vocab-flash-meta-k">' + escapeHtml(VOCAB_LANG.topic) + '</span> ' +
            '<span class="vocab-flash-meta-v">' + escapeHtml(w._topic || '') + '</span></div>' +
            '</div></div>';
        flashFront.innerHTML = frontHtml;

        let backHtml = '<div class="vocab-flash-back-wrap">' +
            '<div class="vocab-flash-back-ribbon">' +
            '<span class="vocab-flash-back-ribbon__label">' + escapeHtml(VOCAB_LANG.backIntro) + '</span>' +
            '<div class="vocab-flash-back-ribbon__wordrow">' +
            '<span class="vocab-flash-back-ribbon__word">' + escapeHtml(w.word || '') + '</span>' +
            speakBtn(w.word, 'en-US', VOCAB_LANG.listenWord) +
            '</div></div>';

        if (w.meaning_en) {
            backHtml += '<section class="vocab-flash-panel vocab-flash-panel--en" aria-labelledby="vf-en-' + flashIndex + '">' +
                '<div class="vocab-flash-panel__top">' +
                '<h3 class="vocab-flash-panel__h" id="vf-en-' + flashIndex + '">' + escapeHtml(VOCAB_LANG.meaningEn) + '</h3>' +
                speakBtn(w.meaning_en, 'en-US', VOCAB_LANG.listenEn) +
                '</div>' +
                '<p class="vocab-flash-panel__text">' + escapeHtml(w.meaning_en) + '</p></section>';
        }
        if (w.meaning_ur) {
            backHtml += '<section class="vocab-flash-panel vocab-flash-panel--ur" aria-labelledby="vf-ur-' + flashIndex + '">' +
                '<div class="vocab-flash-panel__top">' +
                '<h3 class="vocab-flash-panel__h" id="vf-ur-' + flashIndex + '">' + escapeHtml(VOCAB_LANG.meaningUr) + '</h3>' +
                speakBtn(w.meaning_ur, 'ur-PK', VOCAB_LANG.listenUr) +
                '</div>' +
                '<p class="vocab-flash-panel__text urdu-text">' + escapeHtml(w.meaning_ur) + '</p></section>';
        }
        if (w.example_sentence) {
            backHtml += '<section class="vocab-flash-panel vocab-flash-panel--ex" aria-labelledby="vf-ex-' + flashIndex + '">' +
                '<div class="vocab-flash-panel__top">' +
                '<h3 class="vocab-flash-panel__h" id="vf-ex-' + flashIndex + '"><i class="fa fa-quote-left me-1" aria-hidden="true"></i>' +
                escapeHtml(VOCAB_LANG.example) + '</h3>' +
                speakBtn(w.example_sentence, exampleTtsLang(), VOCAB_LANG.listenEx) +
                '</div>' +
                '<p class="vocab-flash-panel__text vocab-flash-panel__text--ex">' + escapeHtml(w.example_sentence) + '</p></section>';
        }

        const extras = [];
        if (w.synonyms) {
            extras.push('<div class="vocab-flash-extra-line"><span class="vocab-flash-extra-k">' + escapeHtml(VOCAB_LANG.extra) + '</span> ' +
                '<span class="vocab-flash-extra-v">' + escapeHtml(w.synonyms) + '</span></div>');
        }
        if (w.antonyms) {
            extras.push('<div class="vocab-flash-extra-line"><span class="vocab-flash-extra-k">' + escapeHtml(VOCAB_LANG.antonymsLbl) + '</span> ' +
                '<span class="vocab-flash-extra-v">' + escapeHtml(w.antonyms) + '</span></div>');
        }
        if (w.related_words) {
            extras.push('<div class="vocab-flash-extra-line"><span class="vocab-flash-extra-k">' + escapeHtml(VOCAB_LANG.relatedLbl) + '</span> ' +
                '<span class="vocab-flash-extra-v">' + escapeHtml(w.related_words) + '</span></div>');
        }
        if (w.syllables) {
            extras.push('<div class="vocab-flash-extra-line"><span class="vocab-flash-extra-k">' + escapeHtml(VOCAB_LANG.syllablesLbl) + '</span> ' +
                '<span class="vocab-flash-extra-v">' + escapeHtml(w.syllables) + '</span></div>');
        }
        if (w.difficulty_level) {
            extras.push('<div class="vocab-flash-extra-line mt-2">' +
                '<span class="badge rounded-pill text-bg-warning vocab-flash-diff">' + escapeHtml(w.difficulty_level) + '</span></div>');
        }
        if (extras.length) {
            backHtml += '<section class="vocab-flash-panel vocab-flash-panel--more" aria-label="' + escapeHtml(VOCAB_LANG.extra) + '">' +
                extras.join('') + '</section>';
        }

        backHtml += '</div>';
        flashBack.innerHTML = backHtml;
        setFlashFlipped(false);

        if (btnPrev) {
            btnPrev.disabled = flashIndex <= 0;
        }
        if (btnNext) {
            btnNext.disabled = flashIndex >= total - 1;
        }
    }

    function setMode(next) {
        mode = next;
        const isList = mode === 'list';
        if (btnList && btnFlash) {
            btnList.classList.toggle('vocab-mode-btn--active', isList);
            btnFlash.classList.toggle('vocab-mode-btn--active', !isList);
            btnList.setAttribute('aria-pressed', isList ? 'true' : 'false');
            btnFlash.setAttribute('aria-pressed', isList ? 'false' : 'true');
        }
        if (contentEl) {
            contentEl.style.display = isList ? 'block' : 'none';
        }
        if (flashRoot) {
            flashRoot.style.display = isList ? 'none' : 'block';
        }
        if (ttsFlashHint) {
            if (!isList && canTTS() && VOCAB_LANG.ttsHint) {
                ttsFlashHint.textContent = VOCAB_LANG.ttsHint;
                ttsFlashHint.style.display = 'block';
            } else {
                ttsFlashHint.style.display = 'none';
            }
        }
        if (!isList && flatWords.length) {
            renderFlashCard();
        }
    }

    function renderVocabulary(data) {
        const html = buildListHtml(data);
        flatWords = flattenWords(data);

        if (loadingEl) {
            loadingEl.style.display = 'none';
        }
        if (errorEl) {
            errorEl.style.display = 'none';
        }

        if (html === '' && flatWords.length === 0) {
            if (emptyMsg && data.message) {
                emptyMsg.textContent = data.message;
            }
            if (toolbarEl) {
                toolbarEl.style.display = 'none';
            }
            if (contentEl) {
                contentEl.style.display = 'none';
            }
            if (flashRoot) {
                flashRoot.style.display = 'none';
            }
            if (emptyEl) {
                emptyEl.style.display = 'block';
            }
            return;
        }

        if (emptyEl) {
            emptyEl.style.display = 'none';
        }
        if (contentEl) {
            contentEl.innerHTML = html;
            contentEl.style.display = 'block';
        }
        if (toolbarEl && flatWords.length > 0) {
            toolbarEl.style.display = 'flex';
        } else if (toolbarEl) {
            toolbarEl.style.display = 'none';
        }
        if (flashRoot) {
            flashRoot.style.display = 'none';
        }
        mode = 'list';
        flashIndex = 0;
        setMode('list');
        hydrateCommonsFromWikimedia();
    }

    function flipCard() {
        if (!flashCardBtn) {
            return;
        }
        flashCardBtn.classList.toggle('is-flipped');
    }

    if (btnList) {
        btnList.addEventListener('click', function () {
            setMode('list');
        });
    }
    if (btnFlash) {
        btnFlash.addEventListener('click', function () {
            if (flatWords.length) {
                setMode('flash');
            }
        });
    }
    if (flashCardBtn) {
        flashCardBtn.addEventListener('click', function (e) {
            if (mode !== 'flash') {
                return;
            }
            if (e.target.closest && e.target.closest('.vocab-speak-btn')) {
                return;
            }
            e.preventDefault();
            flipCard();
        });
        flashCardBtn.addEventListener('keydown', function (e) {
            if (mode !== 'flash' || e.target !== flashCardBtn) {
                return;
            }
            if (e.key !== 'Enter' && e.key !== ' ') {
                return;
            }
            e.preventDefault();
            flipCard();
        });
    }
    if (btnFlip) {
        btnFlip.addEventListener('click', function (e) {
            e.stopPropagation();
            if (mode === 'flash') {
                flipCard();
            }
        });
    }
    if (btnPrev) {
        btnPrev.addEventListener('click', function () {
            if (mode !== 'flash' || flashIndex <= 0) {
                return;
            }
            flashIndex -= 1;
            renderFlashCard();
        });
    }
    if (btnNext) {
        btnNext.addEventListener('click', function () {
            if (mode !== 'flash' || flashIndex >= flatWords.length - 1) {
                return;
            }
            flashIndex += 1;
            renderFlashCard();
        });
    }

    const scene = document.getElementById('vocabFlashScene');
    if (scene) {
        scene.addEventListener('touchstart', function (e) {
            if (mode !== 'flash' || !e.changedTouches || !e.changedTouches.length) {
                return;
            }
            if (e.target.closest && e.target.closest('.vocab-speak-btn')) {
                touchStartX = null;
                return;
            }
            touchStartX = e.changedTouches[0].clientX;
        }, { passive: true });
        scene.addEventListener('touchend', function (e) {
            if (mode !== 'flash' || touchStartX === null || !e.changedTouches || !e.changedTouches.length) {
                return;
            }
            const dx = e.changedTouches[0].clientX - touchStartX;
            touchStartX = null;
            if (Math.abs(dx) < 56) {
                return;
            }
            if (dx < 0 && flashIndex < flatWords.length - 1) {
                flashIndex += 1;
                renderFlashCard();
            } else if (dx > 0 && flashIndex > 0) {
                flashIndex -= 1;
                renderFlashCard();
            }
        }, { passive: true });
    }

    document.addEventListener('keydown', function (e) {
        if (mode !== 'flash' || !flatWords.length) {
            return;
        }
        if (e.key === 'ArrowRight' && flashIndex < flatWords.length - 1) {
            flashIndex += 1;
            renderFlashCard();
        } else if (e.key === 'ArrowLeft' && flashIndex > 0) {
            flashIndex -= 1;
            renderFlashCard();
        } else if (e.key === ' ' || e.key === 'Enter') {
            const ae = document.activeElement;
            if (ae && (ae.tagName === 'BUTTON' || ae.closest && ae.closest('.vocab-speak-btn'))) {
                return;
            }
            if (ae && flashCardBtn && ae !== flashCardBtn && flashCardBtn.contains(ae)) {
                return;
            }
            e.preventDefault();
            flipCard();
        }
    });

    if (typeof jQuery === 'undefined') {
        showError('Unable to load vocabulary (jQuery missing).');
        return;
    }

    jQuery.ajax({
        url: dataUrl,
        type: 'GET',
        dataType: 'json',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function (data) {
            if (data.status === 'ok') {
                if ((data.topics_by_subject && Object.keys(data.topics_by_subject).length > 0) ||
                    (data.topics && data.topics.length > 0)) {
                    renderVocabulary(data);
                } else {
                    renderVocabulary({ message: data.message || '', student_subjects: data.student_subjects });
                }
            } else {
                showError(escapeHtml(data.msg || 'Error loading vocabulary.'));
            }
        },
        error: function (xhr) {
            showError('Error ' + xhr.status + ': Unable to load vocabulary.');
        }
    });
});
</script>
<?php endif; ?>

<style>
.parent-portal-vocab .vocab-summary-bar {
    padding: 0.65rem 0.85rem;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
}
.parent-portal-vocab .vocab-mode-toolbar {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.parent-portal-vocab .vocab-mode-pills {
    display: inline-flex;
    padding: 4px;
    border-radius: 999px;
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    border: 2px solid rgba(245, 158, 11, 0.45);
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.2);
}
.parent-portal-vocab .vocab-mode-btn {
    border: none;
    border-radius: 999px;
    padding: 0.45rem 1rem;
    font-weight: 800;
    font-size: 0.95rem;
    background: transparent;
    color: #92400e;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease, transform 0.15s ease;
}
.parent-portal-vocab .vocab-mode-btn:hover {
    color: #78350f;
    transform: scale(1.02);
}
.parent-portal-vocab .vocab-mode-btn--active {
    background: linear-gradient(135deg, #fff, #fffbeb);
    color: #c2410c;
    box-shadow: 0 2px 10px rgba(180, 83, 9, 0.2);
}
.parent-portal-vocab .vocab-flash-root {
    max-width: 560px;
    margin: 0 auto;
}
.parent-portal-vocab .vocab-flash-hint {
    color: #475569 !important;
    line-height: 1.55;
    font-size: 0.95rem;
    max-width: 34rem;
    margin-left: auto;
    margin-right: auto;
}
.parent-portal-vocab .vocab-flash-tts-hint {
    max-width: 34rem;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.5;
}
.parent-portal-vocab .vocab-flash-counter {
    font-size: 1.15rem;
    font-weight: 800;
    color: #3730a3;
    letter-spacing: 0.03em;
}
.parent-portal-vocab .vocab-flash-progress {
    max-width: 100%;
}
.parent-portal-vocab .vocab-flash-progress-track {
    height: 12px;
    border-radius: 999px;
    background: #e2e8f0;
    overflow: hidden;
    box-shadow: inset 0 1px 3px rgba(15, 23, 42, 0.08);
}
.parent-portal-vocab .vocab-flash-progress-fill {
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, #818cf8, #4f46e5, #22c55e);
    transition: width 0.35s ease;
}
.parent-portal-vocab .vocab-flash-scene {
    perspective: 1200px;
    margin-bottom: 0.65rem;
}
.parent-portal-vocab .vocab-flash-card {
    display: block;
    width: 100%;
    padding: 0;
    border: none;
    background: transparent;
    cursor: pointer;
    -webkit-tap-highlight-color: transparent;
}
.parent-portal-vocab .vocab-flash-card:focus {
    outline: none;
}
.parent-portal-vocab .vocab-flash-card:focus-visible {
    outline: 3px solid rgba(79, 70, 229, 0.65);
    outline-offset: 4px;
    border-radius: 28px;
}
.parent-portal-vocab .vocab-flash-inner {
    position: relative;
    width: 100%;
    min-height: clamp(320px, 52vh, 420px);
    transform-style: preserve-3d;
    transition: transform 0.55s cubic-bezier(0.4, 0.2, 0.2, 1);
}
.parent-portal-vocab .vocab-flash-card.is-flipped .vocab-flash-inner {
    transform: rotateY(180deg);
}
.parent-portal-vocab .vocab-flash-face {
    position: absolute;
    inset: 0;
    border-radius: 24px;
    padding: 1.25rem 1.2rem 1.35rem;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
    box-shadow: 0 16px 44px rgba(30, 27, 75, 0.12);
    border: 2px solid rgba(255, 255, 255, 0.95);
    overflow-y: auto;
    overflow-x: hidden;
    -webkit-overflow-scrolling: touch;
    text-align: center;
}
.parent-portal-vocab .vocab-flash-front {
    background: linear-gradient(180deg, #fffbeb 0%, #fef3c7 45%, #fde68a 100%);
    color: #1c1917;
}
.parent-portal-vocab .vocab-flash-front-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    min-height: 100%;
    gap: 0.35rem;
}
.parent-portal-vocab .vocab-flash-front-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #92400e;
    background: rgba(255, 255, 255, 0.75);
    border: 1px solid rgba(245, 158, 11, 0.45);
    border-radius: 999px;
    padding: 0.28rem 0.75rem;
    margin-bottom: 0.25rem;
}
.parent-portal-vocab .vocab-flash-front-badge__icon {
    font-size: 1rem;
    line-height: 1;
}
.parent-portal-vocab .vocab-flash-illus-wrap {
    width: min(220px, 72%);
    margin: 0.25rem auto 0.35rem;
    border-radius: 16px;
    overflow: hidden;
    border: 2px solid rgba(245, 158, 11, 0.35);
    background: rgba(255, 255, 255, 0.65);
    box-shadow: 0 6px 18px rgba(120, 53, 15, 0.12);
}
.parent-portal-vocab .vocab-flash-illus-img {
    display: block;
    width: 100%;
    height: auto;
    max-height: 160px;
    object-fit: cover;
}
.parent-portal-vocab .vocab-flash-illus-caption {
    font-size: 0.78rem;
    line-height: 1.4;
    color: #57534e;
    font-style: italic;
    margin: 0.35rem auto 0.15rem;
    max-width: 28rem;
    text-align: center;
}
.parent-portal-vocab .vocab-flash-commons-credit {
    margin-top: 0.35rem;
    font-size: 0.72rem;
    font-weight: 700;
    text-align: center;
}
.parent-portal-vocab .vocab-flash-commons-credit__link {
    color: #92400e;
    text-decoration: underline;
}
.parent-portal-vocab .vocab-flash-word-row {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.35rem 0.5rem;
    width: 100%;
    margin-top: 0.15rem;
}
.parent-portal-vocab .vocab-flash-word {
    font-size: clamp(2rem, 7.5vw, 3.1rem);
    font-weight: 900;
    line-height: 1.12;
    letter-spacing: 0.01em;
    word-break: break-word;
    max-width: 100%;
    text-shadow: 0 1px 0 rgba(255, 255, 255, 0.6);
    margin-top: 0.15rem;
    margin-bottom: 0.15rem;
}
.parent-portal-vocab .vocab-flash-pos-wrap {
    margin-top: 0.25rem;
}
.parent-portal-vocab .vocab-flash-pos {
    display: inline-block;
    font-size: 0.9rem;
    font-weight: 700;
    color: #1e3a8a;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 999px;
    padding: 0.25rem 0.85rem;
    border: 1px solid rgba(59, 130, 246, 0.35);
}
.parent-portal-vocab .vocab-flash-front-tip {
    font-size: clamp(0.88rem, 3.2vw, 1rem);
    line-height: 1.55;
    color: #44403c;
    max-width: 26rem;
    margin: 0.65rem 0 0.5rem;
    font-weight: 600;
}
.parent-portal-vocab .vocab-flash-meta {
    width: 100%;
    margin-top: auto;
    padding-top: 0.65rem;
    border-top: 1px dashed rgba(120, 53, 15, 0.25);
    text-align: left;
    font-size: 0.88rem;
}
.parent-portal-vocab .vocab-flash-meta-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem 0.5rem;
    margin-bottom: 0.35rem;
    line-height: 1.4;
}
.parent-portal-vocab .vocab-flash-meta-row:last-child {
    margin-bottom: 0;
}
.parent-portal-vocab .vocab-flash-meta-k {
    font-weight: 800;
    color: #78350f;
    min-width: 4.5rem;
}
.parent-portal-vocab .vocab-flash-meta-v {
    font-weight: 600;
    color: #292524;
    flex: 1;
    min-width: 0;
}
.parent-portal-vocab .vocab-flash-back {
    background: linear-gradient(175deg, #eef2ff 0%, #e0e7ff 50%, #ddd6fe 100%);
    color: #1e1b4b;
    transform: rotateY(180deg);
    text-align: left;
}
.parent-portal-vocab .vocab-flash-back-wrap {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    align-items: stretch;
    min-height: 100%;
}
.parent-portal-vocab .vocab-flash-back-ribbon {
    text-align: center;
    padding: 0.55rem 0.75rem;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.92);
    border: 1px solid rgba(99, 102, 241, 0.25);
    box-shadow: 0 2px 10px rgba(79, 70, 229, 0.08);
}
.parent-portal-vocab .vocab-flash-back-ribbon__label {
    display: block;
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #6366f1;
    margin-bottom: 0.2rem;
}
.parent-portal-vocab .vocab-flash-back-ribbon__word {
    display: block;
    font-size: clamp(1.25rem, 4.5vw, 1.65rem);
    font-weight: 900;
    color: #312e81;
    line-height: 1.2;
    word-break: break-word;
}
.parent-portal-vocab .vocab-flash-back-ribbon__wordrow {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.35rem 0.55rem;
    margin-top: 0.25rem;
}
.parent-portal-vocab .vocab-flash-panel__top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.5rem;
}
.parent-portal-vocab .vocab-flash-panel__top .vocab-flash-panel__h {
    flex: 1;
    margin-bottom: 0;
    align-self: center;
}
.parent-portal-vocab .vocab-flash-panel__top .vocab-speak-btn {
    flex-shrink: 0;
    margin-top: 0.05rem;
}
.parent-portal-vocab .vocab-flash-panel {
    background: #fff;
    border-radius: 16px;
    padding: 0.95rem 1.05rem 1rem;
    border: 1px solid rgba(15, 23, 42, 0.08);
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
    text-align: left;
}
.parent-portal-vocab .vocab-flash-panel--en {
    border-start: 5px solid #2563eb;
}
.parent-portal-vocab .vocab-flash-panel--ur {
    border-start: 5px solid #7c3aed;
}
.parent-portal-vocab .vocab-flash-panel--ex {
    border-start: 5px solid #059669;
    background: linear-gradient(180deg, #ffffff 0%, #ecfdf5 100%);
}
.parent-portal-vocab .vocab-flash-panel--more {
    background: #fafafa;
    border-style: dashed;
}
.parent-portal-vocab .vocab-flash-panel__h {
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #64748b;
    margin: 0 0 0.45rem;
}
.parent-portal-vocab .vocab-flash-panel--en .vocab-flash-panel__h {
    color: #1d4ed8;
}
.parent-portal-vocab .vocab-flash-panel--ur .vocab-flash-panel__h {
    color: #6d28d9;
}
.parent-portal-vocab .vocab-flash-panel--ex .vocab-flash-panel__h {
    color: #047857;
}
.parent-portal-vocab .vocab-flash-panel__text {
    font-size: clamp(1.02rem, 3.5vw, 1.2rem);
    font-weight: 600;
    line-height: 1.55;
    color: #0f172a;
    margin: 0;
}
.parent-portal-vocab .vocab-flash-panel__text--ex {
    font-size: clamp(1rem, 3.3vw, 1.12rem);
    font-weight: 600;
    color: #14532d;
    line-height: 1.6;
}
.parent-portal-vocab .vocab-flash-extra-line {
    font-size: 0.92rem;
    line-height: 1.5;
    margin-bottom: 0.4rem;
}
.parent-portal-vocab .vocab-flash-extra-line:last-child {
    margin-bottom: 0;
}
.parent-portal-vocab .vocab-flash-extra-k {
    font-weight: 800;
    color: #475569;
    margin-right: 0.25rem;
}
.parent-portal-vocab .vocab-flash-extra-v {
    font-weight: 600;
    color: #1e293b;
}
.parent-portal-vocab .vocab-flash-diff {
    font-size: 0.85rem;
    padding: 0.35rem 0.65rem;
}
.parent-portal-vocab .vocab-flip-btn {
    font-weight: 800;
    font-size: 1.05rem;
    padding: 0.55rem 1.35rem;
    box-shadow: 0 6px 20px rgba(79, 70, 229, 0.22);
}
.parent-portal-vocab .vocab-flash-nav-btn {
    font-weight: 800;
    font-size: 1.05rem;
    border-radius: 18px !important;
    min-height: 54px;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.1);
}
.parent-portal-vocab .vocab-flash-nav-btn--prev {
    background: linear-gradient(135deg, #94a3b8, #64748b);
    border: none;
    color: #fff;
}
.parent-portal-vocab .vocab-flash-nav-btn--prev:hover {
    color: #fff;
    filter: brightness(1.05);
}
.parent-portal-vocab .vocab-flash-nav-btn--prev:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}
.parent-portal-vocab .vocab-flash-nav-btn--next {
    background: linear-gradient(135deg, #34d399, #059669);
    border: none;
    color: #fff;
}
.parent-portal-vocab .vocab-flash-nav-btn--next:hover {
    color: #fff;
    filter: brightness(1.05);
}
.parent-portal-vocab .vocab-flash-nav-btn--next:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}
.parent-portal-vocab .vocab-subject-block {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(15, 23, 42, 0.05);
}
.parent-portal-vocab .vocab-subject-head {
    background: linear-gradient(135deg, #4f46e5, #6366f1);
    color: #fff;
    padding: 0.65rem 0.9rem;
    font-weight: 700;
    font-size: 0.95rem;
}
.parent-portal-vocab .vocab-subject-body {
    padding: 0.75rem;
    background: #fff;
}
.parent-portal-vocab .vocab-topic-card {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    margin-bottom: 0.65rem;
    overflow: hidden;
}
.parent-portal-vocab .vocab-topic-card:last-child {
    margin-bottom: 0;
}
.parent-portal-vocab .vocab-topic-head {
    background: #f1f5f9;
    padding: 0.5rem 0.75rem;
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.35rem;
}
.parent-portal-vocab .vocab-topic-body {
    padding: 0.65rem 0.75rem;
}
.parent-portal-vocab .vocab-speak-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    padding: 0;
    margin: 0 0 0 0.25rem;
    border: none;
    border-radius: 50%;
    background: rgba(79, 70, 229, 0.12);
    color: #4338ca;
    cursor: pointer;
    vertical-align: middle;
    transition: background 0.15s ease, transform 0.12s ease, color 0.15s ease;
    -webkit-tap-highlight-color: transparent;
}
.parent-portal-vocab .vocab-speak-btn:hover {
    background: rgba(79, 70, 229, 0.22);
    color: #312e81;
}
.parent-portal-vocab .vocab-speak-btn:focus {
    outline: none;
}
.parent-portal-vocab .vocab-speak-btn:focus-visible {
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.45);
}
.parent-portal-vocab .vocab-speak-btn:active {
    transform: scale(0.94);
}
.parent-portal-vocab .vocab-speak-btn .fa {
    font-size: 0.95rem;
}
.parent-portal-vocab .vocab-word-line {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    gap: 0.15rem 0.25rem;
}
.parent-portal-vocab .vocab-word-line.urdu-text {
    flex-direction: row-reverse;
    text-align: right;
}
.parent-portal-vocab .vocab-word-illus {
    float: left;
    margin: 0 0.65rem 0.35rem 0;
    max-width: 42%;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    background: #fff;
}
.parent-portal-vocab .vocab-word-illus__img {
    display: block;
    width: 100%;
    max-height: 120px;
    object-fit: cover;
}
.parent-portal-vocab .vocab-word-illus--commons .vocab-commons-credit {
    font-size: 0.68rem;
    font-weight: 700;
    text-align: center;
    margin-top: 0.25rem;
    line-height: 1.3;
}
.parent-portal-vocab .vocab-word-illus--commons .vocab-commons-credit a {
    color: #4338ca;
    text-decoration: underline;
}
.parent-portal-vocab .vocab-commons-caption {
    font-size: 0.72rem;
    line-height: 1.35;
    color: #475569;
    margin: 0.35rem 0 0;
    font-style: italic;
    clear: both;
}
.parent-portal-vocab .vocab-word-row::after {
    content: "";
    display: table;
    clear: both;
}
.parent-portal-vocab .vocab-word-row {
    border: 1px solid #f1f5f9;
    border-radius: 8px;
    padding: 0.55rem 0.65rem;
    margin-bottom: 0.45rem;
    background: #fafafa;
}
.parent-portal-vocab .vocab-word-row:last-child {
    margin-bottom: 0;
}
.parent-portal-vocab .vocab-word-head {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.25rem;
    margin-bottom: 0.35rem;
}
.parent-portal-vocab .urdu-text {
    direction: rtl;
    text-align: right;
    font-family: 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu', 'Segoe UI', sans-serif;
}
@media (max-width: 576px) {
    .parent-portal-vocab .vocab-flash-face {
        min-height: clamp(300px, 48vh, 380px);
        padding: 1rem 0.95rem 1.1rem;
    }
    .parent-portal-vocab .vocab-flash-inner {
        min-height: clamp(300px, 48vh, 380px);
    }
}
html[dir="rtl"] .parent-portal-vocab .vocab-flash-back {
    text-align: right;
}
html[dir="rtl"] .parent-portal-vocab .vocab-flash-meta {
    text-align: right;
}
html[dir="rtl"] .parent-portal-vocab .vocab-flash-meta-row {
    flex-direction: row-reverse;
}
</style>

<?= $this->endSection() ?>
