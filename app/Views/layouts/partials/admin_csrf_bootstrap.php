<?php
/**
 * Global admin AJAX CSRF sync (cookie-based CSRF + httponly cookie).
 * Included once from layouts/header.php after jQuery loads.
 */
$csrfTokenName  = csrf_token();
$csrfTokenHash  = csrf_hash();
$csrfHeaderName = csrf_header();
?>
<meta name="csrf-token-name" content="<?= esc($csrfTokenName) ?>">
<meta name="csrf-token-hash" content="<?= esc($csrfTokenHash) ?>">
<script>
(function ($) {
    if (!$ || !$.ajaxPrefilter) {
        return;
    }

    window.ADMIN_CSRF = {
        name: <?= json_encode($csrfTokenName) ?>,
        hash: <?= json_encode($csrfTokenHash) ?>,
        header: <?= json_encode($csrfHeaderName) ?>
    };

    function readAdminCsrfFromDom() {
        var name = $('meta[name="csrf-token-name"]').attr('content') || window.ADMIN_CSRF.name;
        var hash = $('meta[name="csrf-token-hash"]').attr('content') || window.ADMIN_CSRF.hash;
        window.ADMIN_CSRF.name = name;
        window.ADMIN_CSRF.hash = hash;
        return { name: name, hash: hash };
    }

    window.refreshAdminCsrf = function (xhr) {
        if (!xhr || !xhr.getResponseHeader) {
            return;
        }
        var hash = xhr.getResponseHeader(window.ADMIN_CSRF.header)
            || xhr.getResponseHeader('X-CSRF-TOKEN');
        if (!hash) {
            return;
        }
        window.ADMIN_CSRF.hash = hash;
        $('meta[name="csrf-token-hash"]').attr('content', hash);
        $('[data-csrf-meta]').each(function () {
            this.setAttribute('content', hash);
        });
    };

    window.adminCsrfPayload = function (extra) {
        var pair = readAdminCsrfFromDom();
        var data = extra && typeof extra === 'object' ? $.extend({}, extra) : {};
        if (pair.name && pair.hash != null) {
            data[pair.name] = pair.hash;
        }
        return data;
    };

    $.ajaxPrefilter(function (options) {
        var method = (options.type || options.method || 'GET').toUpperCase();
        if (method !== 'POST' && method !== 'PUT' && method !== 'PATCH' && method !== 'DELETE') {
            return;
        }
        var url = String(options.url || '');
        if (url.indexOf('/admin') === -1 && url.indexOf('admin/') === -1) {
            return;
        }

        var pair = readAdminCsrfFromDom();
        if (!pair.name || pair.hash == null) {
            return;
        }

        options.headers = $.extend({}, options.headers || {});
        options.headers[window.ADMIN_CSRF.header] = pair.hash;

        if (options.data instanceof FormData) {
            options.data.append(pair.name, pair.hash);
            return;
        }

        if (typeof options.data === 'string') {
            var contentType = String(options.contentType || '').toLowerCase();
            // JSON bodies: CSRF is sent via header only (appending breaks JSON.parse on server)
            if (contentType.indexOf('application/json') !== -1) {
                return;
            }
            options.data += (options.data ? '&' : '')
                + encodeURIComponent(pair.name) + '=' + encodeURIComponent(pair.hash);
            return;
        }

        options.data = options.data || {};
        if (typeof options.data === 'object') {
            options.data[pair.name] = pair.hash;
        }
    });

    $(document).ajaxComplete(function (_e, xhr, settings) {
        if (!settings || !settings.url) {
            return;
        }
        var url = String(settings.url);
        if (url.indexOf('/admin') === -1 && url.indexOf('admin/') === -1) {
            return;
        }
        refreshAdminCsrf(xhr);
    });
})(window.jQuery);
</script>
