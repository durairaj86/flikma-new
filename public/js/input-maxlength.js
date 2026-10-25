/*
 * Puts maxlength on text inputs and textareas from the length of the database column they save to.
 * The column lengths come from input-lengths.js (php artisan inputs:lengths).
 *
 * The table behind a form is the one whose columns match most of the form's field names; inputs that the table
 * does not know fall back to the longest text column of that name anywhere. An input that already has a
 * maxlength keeps it.
 *
 * Number boxes (.integer / .decimal) are limited by digits instead (data-max-int, data-decimals for number-input.js):
 * from the column type when it is a number (int 9 digits, decimal(15,2) 13 digits + 2 decimals), otherwise
 * from a safe default, because a column that is text in the database says nothing about the number's size.
 * The database itself is never changed.
 */
(function () {
    'use strict';
    var MAP = window.__inputLengths;
    if (!MAP || window.__ffMaxLength) return;
    window.__ffMaxLength = true;

    var TEXT_TYPES = { text: 1, email: 1, tel: 1, url: 1, search: 1, password: 1 };
    var SEL = 'input[name], textarea[name]';

    var DEFAULT_INT = 9;        // integer box: up to 999,999,999
    var DEFAULT_DEC_INT = 13;   // decimal box: up to 13 digits before the point (like decimal(15,2))

    function isNumberBox(el) { return el.classList.contains('integer') || el.classList.contains('decimal'); }

    function limitNumber(el, spec) {
        var isInt = el.classList.contains('integer');
        var digits = isInt ? DEFAULT_INT : DEFAULT_DEC_INT, dec = null;
        if (typeof spec === 'string') {
            var p = spec.split(':');
            digits = parseInt(p[1], 10) || digits;
            if (p[0] === 'd') dec = parseInt(p[2], 10);
        }
        if (!el.dataset.maxInt) el.dataset.maxInt = digits;
        if (!isInt && dec !== null && el.dataset.decimals === undefined) {
            // the box's own step may already allow fewer decimals; never allow more than the column holds
            var st = String(el.dataset.step || el.getAttribute('step') || '').match(/\.(\d+)/);
            el.dataset.decimals = st ? Math.min(st[1].length, dec) : dec;
        }
    }

    function eligible(el) {
        if (el.hasAttribute('maxlength')) return false;
        var n = el.getAttribute('name');
        if (!n) return false;
        if (el.classList.contains('datepicker') || el.classList.contains('flatpickr-input')) return false;
        if (el.tagName === 'INPUT') {
            if (!TEXT_TYPES[(el.getAttribute('type') || 'text').toLowerCase()]) return false;
        }
        return true;
    }

    // What a value of this kind can really be, whatever the column allows (most columns are just varchar(255)).
    // The shorter of this and the column length wins. Exact field names first, then patterns.
    var NAMED = {
        // contact
        email: 100, alt_email: 100, contact_email: 100, cc: 255,
        cr_number: 20, vat_number: 15, iqama_no: 10, national_id: 10, gosi_number: 15, pincode: 10, postal_code: 10,
        building_number: 10, plot_no: 10, city_sub_division: 50,
        city: 100, city_en: 100, city_ar: 100, state: 100, country: 100, origin_city: 100,
        name: 150, name_en: 150, name_ar: 150, full_name: 150, company: 150, contact_person: 100, driver_name: 100,
        received_by: 100, prepared_by: 100, emergency_contact_name: 100, designation: 100, department: 100, title: 150,
        shipper: 150, consignee: 150, notify_party: 150, transporter: 150, customs_broker: 150, carrier: 100, carrier_name: 100, sol_name: 150,
        // bank
        iban: 34, iban_code: 34, swift_code: 11, sort_code: 12, account_number: 34, account_code: 20, account_name: 100,
        account_holder: 100, bank: 100, bank_name: 100, branch: 100,
        // codes and references
        code: 20, port_code: 10, package_code: 10, unit_symbol: 10, unit_name: 50, package_name: 50, port_name: 100,
        hs_code: 12, package_hs_code: 12, reference_no: 50, ref_no: 50, reference_number: 50, client_ref: 50, booking_ref: 50,
        shipping_reference_no: 50, invoice_number: 50, job_number: 50, do_no: 50, bl_no: 50, mbl_no: 50, hbl_number: 50, awb_number: 50,
        declaration_no: 30, bayan_no: 30, voyage_no: 20, voyage_number: 20, voyage_flight_no: 20, flight_number: 20, vessel_flight: 20,
        vessel_name: 100, container_no: 11, seal_no: 20, vehicle_no: 15, plate_no: 15, license_no: 30, serial_number: 50, serial: 50,
        make_model: 100, device_user_id: 20, subject: 150, otp: 6, password: 64, new_password: 64, confirm_password: 64, current_password: 64
    };
    var PATTERNS = [
        [/(^|_)(phone|mobile|tel|fax)(_|$)/, 16],     // +966501234567
        [/_email$/, 100]
    ];

    function semanticMax(key) {
        if (NAMED[key] !== undefined) return NAMED[key];
        for (var i = 0; i < PATTERNS.length; i++) if (PATTERNS[i][0].test(key)) return PATTERNS[i][1];
        return 0;
    }

    function pickLength(key, columnLen) {
        var sem = semanticMax(key);
        if (sem && columnLen) return Math.min(sem, columnLen);
        return sem || columnLen || 0;
    }

    var DEFAULT_TEXT = 255;     // a text box with no column to learn from still gets a sane limit

    // container[number][] -> number, name -> name
    function baseName(n) {
        var m = n.match(/\[([^\]]+)\]\s*(\[\])?$/);
        return m ? m[1] : n.replace(/\[\]$/, '');
    }

    // anything the table pass left without a limit: the same name anywhere, else a default
    function applyDefaults(el) {
        if (el.tagName === 'INPUT' && isNumberBox(el)) { limitNumber(el, null); return; }
        if (!eligible(el) || el.tagName !== 'INPUT') return;
        var key = baseName(el.getAttribute('name'));
        el.setAttribute('maxlength', pickLength(key, MAP.any[key]) || DEFAULT_TEXT);
    }

    function bestTable(names) {
        var best = null, score = 0;
        for (var t in MAP.tables) {
            var cols = MAP.tables[t], s = 0;
            for (var i = 0; i < names.length; i++) if (cols[names[i]] !== undefined) s++;
            if (s > score) { score = s; best = t; }
        }
        return score >= 2 ? MAP.tables[best] : null;
    }

    function applyTo(scope) {
        var fields = scope.querySelectorAll ? scope.querySelectorAll(SEL) : [];
        if (!fields.length) return;
        var names = [], byForm = [];
        for (var i = 0; i < fields.length; i++) {
            var n = fields[i].getAttribute('name');
            if (n && n.indexOf('[') === -1) names.push(n);
            else if (n) names.push(baseName(n));
        }
        var table = bestTable(names);
        for (var j = 0; j < fields.length; j++) {
            var el = fields[j];
            var name = el.getAttribute('name');
            if (el.tagName === 'INPUT' && isNumberBox(el)) {
                var spec = table && typeof table[name] === 'string' ? table[name] : null;
                if (name && name.indexOf('[') !== -1) spec = null;
                limitNumber(el, spec);
                continue;
            }
            if (!eligible(el)) continue;
            var key = name.indexOf('[') === -1 ? name : baseName(name);
            var tv = table ? table[key] : undefined;
            var len = typeof tv === 'number' ? tv : 0;
            if (tv === undefined) len = MAP.any[key] || 0;
            // an input type of known size (phone, VAT, IBAN...) is limited by what it is, not only by the column
            len = pickLength(key, len);
            if (len > 0) el.setAttribute('maxlength', len);
            else if (tv === undefined) applyDefaults(el);
        }
    }

    function scopeOf(node) {
        if (node.nodeType !== 1) return null;
        return node.closest ? (node.closest('form') || node) : node;
    }

    // ---- "7/9" counter: while a limited box is focused and nearly full, show how much of the limit is used ----
    var counter = null, current = null;

    function usage(el) {
        var v = String(el.value == null ? '' : el.value);
        if (el.tagName === 'INPUT' && isNumberBox(el)) {
            var max = parseInt(el.dataset.maxInt, 10);
            if (!(max > 0)) return null;
            return { n: v.replace(/^-/, '').split('.')[0].length, max: max };
        }
        var ml = parseInt(el.getAttribute('maxlength'), 10);
        return ml > 0 ? { n: v.length, max: ml } : null;
    }

    function place() {
        if (!counter || !current) return;
        var r = current.getBoundingClientRect();
        counter.style.top = (r.top - counter.offsetHeight / 2) + 'px';   // centred on the top border: half above, half below
        counter.style.left = (r.right - counter.offsetWidth - 12) + 'px';
    }

    function refresh() {
        if (!current) return;
        var u = usage(current);
        if (!u || u.n < Math.max(u.max - 2, Math.floor(u.max * 0.8))) { if (counter) counter.style.display = 'none'; return; }
        if (!counter) {
            counter = document.createElement('div');
            counter.style.cssText = 'position:fixed;z-index:2000;pointer-events:none;font-size:11px;line-height:1;padding:3px 6px;border-radius:10px;background:#eef2f6;color:#475467;border:1px solid #d0d5dd;font-variant-numeric:tabular-nums';
            document.body.appendChild(counter);
        }
        counter.textContent = u.n + '/' + u.max;
        counter.style.background = u.n >= u.max ? '#fee4e2' : '#eef2f6';
        counter.style.borderColor = u.n >= u.max ? '#fda29b' : '#d0d5dd';
        counter.style.color = u.n >= u.max ? '#b42318' : '#475467';
        counter.style.display = 'block';
        place();
    }

    document.addEventListener('focusin', function (e) {
        var el = e.target;
        current = el && el.matches && el.matches(SEL) ? el : null;
        if (!current && counter) counter.style.display = 'none';
        refresh();
    });
    document.addEventListener('focusout', function () { current = null; if (counter) counter.style.display = 'none'; });
    document.addEventListener('input', function (e) { if (e.target === current) refresh(); }, true);
    window.addEventListener('scroll', place, true);
    window.addEventListener('resize', place);

    function start() {
        var forms = document.querySelectorAll('form');
        for (var i = 0; i < forms.length; i++) applyTo(forms[i]);
        // boxes that sit outside any form
        document.querySelectorAll(SEL).forEach(function (el) { if (!el.closest('form')) { el.hasAttribute('maxlength') || applyDefaults(el); } });
        var pending = [], timer = null;
        new MutationObserver(function (list) {
            for (var i = 0; i < list.length; i++) {
                var added = list[i].addedNodes;
                for (var j = 0; j < added.length; j++) {
                    var s = scopeOf(added[j]);
                    if (s && pending.indexOf(s) === -1) pending.push(s);
                }
            }
            if (!pending.length) return;
            clearTimeout(timer);
            timer = setTimeout(function () {
                var batch = pending; pending = [];
                batch.forEach(function (s) { if (document.contains(s)) applyTo(s); });
            }, 50);
        }).observe(document.documentElement, { childList: true, subtree: true });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start); else start();
})();
