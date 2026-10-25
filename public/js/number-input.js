/*
 * Number inputs: <input class="integer"> accepts whole numbers, <input class="decimal"> accepts decimals.
 * They look and behave like normal text boxes (no spinner arrows), accept digits only and show thousands
 * separators while typing (1234567.5 → 1,234,567.5).
 *
 *   data-decimals="2"   max decimals for .decimal (default: taken from the old step="" attribute, else 2)
 *   data-negative       allow a leading minus sign
 *   data-max-int="9"    most digits before the decimal point (set from the column type by input-maxlength.js)
 *
 * The element's .value (read by Alpine x-model, jQuery, plain scripts) is always the CLEAN number ("1234567.5");
 * what is shown is the formatted text, and forms submit the clean number too (formdata event).
 * Works for inputs added later (Alpine x-for rows, modals) and in any layout. Arabic-Indic digits are converted.
 */
(function () {
    'use strict';
    if (window.__ffNumberInput) return;
    window.__ffNumberInput = true;

    var SEL = 'input.integer, input.decimal';
    var proto = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');
    var AR = { '٠': '0', '١': '1', '٢': '2', '٣': '3', '٤': '4', '٥': '5', '٦': '6', '٧': '7', '٨': '8', '٩': '9', '۰': '0', '۱': '1', '۲': '2', '۳': '3', '۴': '4', '۵': '5', '۶': '6', '۷': '7', '۸': '8', '۹': '9', '٫': '.' };

    // no spinner arrows on any number box in the app
    var st = document.createElement('style');
    st.textContent = 'input[type=number]{-moz-appearance:textfield;appearance:textfield}input[type=number]::-webkit-inner-spin-button,input[type=number]::-webkit-outer-spin-button{-webkit-appearance:none;margin:0}';
    (document.head || document.documentElement).appendChild(st);

    function isInt(el) { return el.classList.contains('integer'); }

    function decimalsOf(el) {
        if (isInt(el)) return 0;
        if (el.dataset.decimals !== undefined && el.dataset.decimals !== '') return Math.max(0, parseInt(el.dataset.decimals, 10) || 0);
        var s = el.dataset.step || '';
        var m = String(s).match(/\.(\d+)/);
        return m ? m[1].length : 2;
    }

    function allowNeg(el) { return el.hasAttribute('data-negative') || (el.dataset.min !== undefined && parseFloat(el.dataset.min) < 0); }

    // keep only what this box may hold: [-]digits[.digits]
    function clean(text, el) {
        text = String(text == null ? '' : text).replace(/[٠-٩۰-۹٫]/g, function (c) { return AR[c]; });
        var neg = allowNeg(el) && /^\s*-/.test(text);
        text = text.replace(/[^0-9.]/g, '');
        if (isInt(el)) text = text.split('.')[0];
        var dot = text.indexOf('.');
        if (dot !== -1) {
            var dec = text.slice(dot + 1).replace(/\./g, '').slice(0, decimalsOf(el));
            text = text.slice(0, dot) + '.' + dec;
        }
        var parts = text.split('.');
        parts[0] = parts[0].replace(/^0+(?=\d)/, '');
        var maxInt = parseInt(el.dataset.maxInt, 10);
        if (maxInt > 0) parts[0] = parts[0].slice(0, maxInt);
        text = parts.join('.');
        return (neg ? '-' : '') + text;
    }

    function group(raw) {
        var neg = raw.charAt(0) === '-', body = neg ? raw.slice(1) : raw, dot = body.indexOf('.');
        var ip = dot === -1 ? body : body.slice(0, dot), rest = dot === -1 ? '' : body.slice(dot);
        return (neg ? '-' : '') + ip.replace(/\B(?=(\d{3})+(?!\d))/g, ',') + rest;
    }

    function setDisplay(el, raw) { proto.set.call(el, raw === '' ? '' : group(raw)); }
    function rawOf(el) { return clean(proto.get.call(el), el); }

    function init(el) {
        if (el.__ffNum) return;
        el.__ffNum = true;
        // remember the old number-box rules, then become a plain text box
        if (el.getAttribute('step') && !el.dataset.decimals) el.dataset.step = el.getAttribute('step');
        if (el.getAttribute('min') !== null) el.dataset.min = el.getAttribute('min');
        if (el.type === 'number') el.type = 'text';
        el.setAttribute('inputmode', isInt(el) ? 'numeric' : 'decimal');
        el.setAttribute('autocomplete', 'off');
        el.removeAttribute('step');
        Object.defineProperty(el, 'value', {
            configurable: true,
            get: function () { return rawOf(el); },
            set: function (v) { setDisplay(el, clean(v, el)); }
        });
        setDisplay(el, clean(proto.get.call(el), el));
    }

    function scan(root) {
        if (root.nodeType !== 1) return;
        if (root.matches && root.matches(SEL)) init(root);
        var list = root.querySelectorAll ? root.querySelectorAll(SEL) : [];
        for (var i = 0; i < list.length; i++) init(list[i]);
    }

    // typing / paste / drop: re-format and keep the caret next to the same digit
    document.addEventListener('input', function (e) {
        var el = e.target;
        if (!el || !el.matches || !el.matches(SEL)) return;
        init(el);
        var shown = proto.get.call(el), caret = el.selectionStart == null ? shown.length : el.selectionStart;
        var before = shown.slice(0, caret).replace(/[^0-9.\-٠-٩۰-۹٫]/g, '').length;
        var raw = clean(shown, el);
        var out = raw === '' ? '' : group(raw);
        if (out !== shown) {
            proto.set.call(el, out);
            var pos = 0, seen = 0;
            while (pos < out.length && seen < before) { if (/[0-9.\-]/.test(out.charAt(pos))) seen++; pos++; }
            try { el.setSelectionRange(pos, pos); } catch (err) { /* not focusable */ }
        }
    }, true);

    // block letters etc. before they appear
    document.addEventListener('beforeinput', function (e) {
        var el = e.target;
        if (!el || !el.matches || !el.matches(SEL) || !e.data || e.inputType.indexOf('insert') !== 0) return;
        init(el);
        var ok = isInt(el) ? /^[0-9٠-٩۰-۹,\s-]*$/ : /^[0-9٠-٩۰-۹.,٫\s-]*$/;
        if (!ok.test(e.data)) e.preventDefault();
    }, true);

    // click / tab into the box: select the whole number so typing replaces it (a second click then places the caret as usual)
    var justFocused = null;
    document.addEventListener('focusin', function (e) {
        var el = e.target;
        if (!el || !el.matches || !el.matches(SEL) || el.readOnly) return;
        init(el);
        justFocused = el;
        el.select();
        setTimeout(function () { if (justFocused === el) el.select(); justFocused = null; }, 0);
    }, true);
    document.addEventListener('mouseup', function (e) {
        if (justFocused && e.target === justFocused) { e.preventDefault(); }   // stop the click from collapsing the selection
    }, true);

    // forms and FormData(form) get the clean number, not the comma text
    document.addEventListener('formdata', function (e) {
        var form = e.target;
        if (!form || !form.querySelectorAll) return;
        var els = form.querySelectorAll(SEL);
        for (var i = 0; i < els.length; i++) {
            var el = els[i];
            if (el.name && !el.disabled && e.formData.has(el.name)) {
                if (el.__ffNum) {
                    var all = e.formData.getAll(el.name), cleaned = all.map(function (v) { return typeof v === 'string' ? clean(v, el) : v; });
                    e.formData.delete(el.name);
                    cleaned.forEach(function (v) { e.formData.append(el.name, v); });
                }
            }
        }
    });

    function start() {
        scan(document.body || document.documentElement);
        new MutationObserver(function (list) {
            for (var i = 0; i < list.length; i++) {
                var added = list[i].addedNodes;
                for (var j = 0; j < added.length; j++) scan(added[j]);
                if (list[i].type === 'attributes') scan(list[i].target);
            }
        }).observe(document.documentElement, { childList: true, subtree: true, attributes: true, attributeFilter: ['class'] });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start); else start();
})();
