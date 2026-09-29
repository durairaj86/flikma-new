{{--
    Arabic / English toggle.

    The app's views are all in English with no translation strings, so a
    native Laravel locale switch would only relabel a handful of wired-up
    pieces (nothing else in the hundreds of module views would move).
    Instead this drives Google's Website Translator widget: clicking the
    icon sets the `googtrans` cookie to /en/ar (or clears it back to
    English) and reloads, so every word already on the page — and on every
    page after — comes back translated. Google's own widget UI is hidden;
    only our icon button is shown.
--}}
<div id="google_translate_element" style="display:none;"></div>

<button type="button"
        class="btn btn-light border-0 rounded-circle header-icon-btn"
        id="language-toggle-btn"
        title="Switch to Arabic"
        aria-label="Switch language">
    <span id="language-toggle-label" class="fw-bold small text-secondary">EN</span>
</button>

<style>
    /* Hide Google's own translate banner/toolbar and undo the top offset
       it injects on <body> when it activates, so only our icon shows and
       the page doesn't jump down. */
    .goog-te-banner-frame,
    .skiptranslate > iframe {
        display: none !important;
        visibility: hidden !important;
    }

    body {
        top: 0 !important;
    }

    #google_translate_element {
        display: none !important;
    }

    .goog-tooltip,
    .goog-tooltip:hover {
        display: none !important;
    }

    .goog-text-highlight {
        background: none !important;
        box-shadow: none !important;
    }

    /* Self-contained sizing so the button looks right whether it lands in
       the full header (which defines .header-icon-btn itself) or the slim
       topbar (which doesn't). */
    #language-toggle-btn.header-icon-btn {
        width: 38px;
        height: 38px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }
</style>

<script>
    (function () {
        function readLangCookie() {
            var match = document.cookie.match(/(?:^|;\s*)googtrans=([^;]*)/);
            return match ? decodeURIComponent(match[1]) : '';
        }

        function writeLangCookie(value) {
            var host = window.location.hostname;
            var base = 'googtrans=' + encodeURIComponent(value) + ';path=/;';
            document.cookie = base;
            // Also scope it to the bare domain so it survives across
            // subdomains the same way Google's own widget sets it.
            document.cookie = base + 'domain=' + host + ';';
            var parts = host.split('.');
            if (parts.length > 2) {
                document.cookie = base + 'domain=.' + parts.slice(-2).join('.') + ';';
            }
        }

        function isArabic() {
            return readLangCookie() === '/en/ar';
        }

        function paintButton() {
            var label = document.getElementById('language-toggle-label');
            var btn = document.getElementById('language-toggle-btn');
            if (!label || !btn) return;
            if (isArabic()) {
                label.textContent = 'AR';
                btn.title = 'Switch to English';
                btn.setAttribute('aria-label', 'Switch to English');
            } else {
                label.textContent = 'EN';
                btn.title = 'Switch to Arabic';
                btn.setAttribute('aria-label', 'Switch to Arabic');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            paintButton();
            var btn = document.getElementById('language-toggle-btn');
            if (btn) {
                btn.addEventListener('click', function () {
                    writeLangCookie(isArabic() ? '/en/en' : '/en/ar');
                    window.location.reload();
                });
            }
        });

        // Loaded once per page; Google's widget reads the cookie above on
        // init and translates the page automatically when it is /en/ar.
        window.googleTranslateElementInit = function () {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'ar',
                autoDisplay: false
            }, 'google_translate_element');
        };
    })();
</script>
<script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
