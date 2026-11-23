@php
    $isCustomer = $kind === 'customer';
    $title = $isCustomer ? __('Import Customers') : __('Import Suppliers');
    $listUrl = $isCustomer ? '/customers' : '/suppliers';
@endphp
@section('page-title', $title)
@section('hide-topbar', true)
<x-app-layout>
    <div class="bg-light pb-4 imp-page">
        <style>
            .imp-page { min-height: 100%; }
            .imp-wrap { max-width: 1300px; margin: 0 auto; padding: 18px 28px 8px; }
            .imp-crumb { font-size: .9rem; color: #475467; }
            .imp-crumb a { color: #0d6efd; }
            .imp-h1 { font-size: 1.55rem; font-weight: 700; letter-spacing: .02em; text-transform: uppercase; color: #1d2939; margin: 6px 0 2px; }
            .imp-sub { color: #667085; font-size: .92rem; }
            .imp-card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(16, 24, 40, .08); padding: 20px 22px; height: 100%; }
            .imp-card h6 { font-weight: 700; color: #1d2939; display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
            .imp-step { width: 24px; height: 24px; border-radius: 50%; background: #0d6efd; color: #fff; font-size: .8rem; display: inline-flex; align-items: center; justify-content: center; flex: 0 0 auto; }
            .imp-help { color: #667085; font-size: .88rem; }
            .imp-list { padding-left: 1.1rem; margin: 0 0 14px; font-size: .9rem; color: #344054; }
            .imp-list li { margin-bottom: 6px; }
            .imp-rules { width: 100%; font-size: .88rem; border-collapse: collapse; }
            .imp-rules td { padding: 8px 4px; border-bottom: 1px solid #eef0f3; vertical-align: top; }
            .imp-rules td:first-child { width: 22%; font-weight: 700; white-space: nowrap; }
            .imp-rules tr:last-child td { border-bottom: 0; }
            .imp-scroll { max-height: 52vh; overflow: auto; border: 1px solid #e9ecef; border-radius: .5rem; }
            .imp-table { font-size: .82rem; }
            .imp-table th { position: sticky; top: 0; background: #f8f9fa; z-index: 1; white-space: nowrap; }
            .imp-table td .sub { color: #667085; font-size: .78rem; }
            .imp-table td.imp-name { min-width: 200px; }
            .imp-table td.imp-note { min-width: 320px; }
            tr.row-skip { opacity: .55; }
            .map-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); column-gap: 28px; }
            .map-row { display: grid; grid-template-columns: 170px 1fr; gap: 10px; align-items: center; padding: 7px 0; border-bottom: 1px solid #eef0f3; }
            .map-row label { font-weight: 600; font-size: .88rem; margin: 0; }
            .map-row label small { display: block; color: #667085; font-weight: 400; }
            .map-row .map-ex { grid-column: 2; color: #667085; font-size: .78rem; margin-top: -4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            @media (max-width: 575px) { .map-grid { grid-template-columns: 1fr; } .map-row { grid-template-columns: 1fr; } .map-row .map-ex { grid-column: 1; } }
        </style>

        <div class="imp-wrap">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <div class="imp-crumb"><a href="{{ $listUrl }}">{{ $isCustomer ? __('Customers') : __('Suppliers') }}</a> &nbsp;/&nbsp; {{ __('Import') }}</div>
                    <h1 class="imp-h1">{{ $title }}</h1>
                    <div class="imp-sub">{{ $isCustomer ? __('Moving from another system? Upload your customer list here. Every row is checked and you confirm before anything is saved.') : __('Moving from another system? Upload your supplier list here. Every row is checked and you confirm before anything is saved.') }}</div>
                </div>
                <a href="{{ $listUrl }}" class="btn btn-outline-secondary btn-sm px-3"><i class="bi bi-arrow-left me-1"></i>{{ $isCustomer ? __('Back to Customers') : __('Back to Suppliers') }}</a>
            </div>

            <div class="row g-3 mt-2">
                {{-- Step 1: upload --}}
                <div class="col-lg-6">
                    <div class="imp-card d-flex flex-column">
                        <h6><span class="imp-step">1</span>{{ __('Upload the file') }}</h6>
                        <input type="file" id="imp-file" class="form-control" accept=".xlsx,.xls,.csv,.txt,.pdf,image/*">
                        <div class="imp-help mt-2">{{ __('.xlsx, .xls, .csv, .pdf or a photo of the page. Up to 10 MB.') }}</div>
                        <div class="imp-help mt-3">{{ __('Nothing is saved yet. You will see every row checked first, and confirm before anything is imported.') }}</div>

                        <div id="imp-loading" class="text-center py-4 d-none">
                            <div class="spinner-border text-primary mb-2"></div>
                            <div class="fw-semibold">{{ __('Reading your file…') }}</div>
                            <div class="imp-help">{{ __('Large files can take a minute') }}</div>
                        </div>
                        <div class="alert alert-danger border-0 mt-3 mb-0 d-none" id="imp-error"></div>

                        <div class="d-flex gap-2 mt-auto pt-4">
                            <button type="button" class="btn btn-primary" id="imp-upload" disabled><i class="bi bi-upload me-1"></i>{{ __('Upload & check') }}</button>
                            <a href="/{{ $kind }}/import/sample" class="btn btn-outline-secondary"><i class="bi bi-download me-1"></i>{{ __('Download sample CSV') }}</a>
                        </div>
                    </div>
                </div>

                {{-- Guide --}}
                <div class="col-lg-6">
                    <div class="imp-card">
                        <h6>{{ __('What the file must look like') }}</h6>
                        <ul class="imp-list">
                            <li>{{ $isCustomer ? __('Excel, CSV, PDF or a photo, in any layout. One customer per line.') : __('Excel, CSV, PDF or a photo, in any layout. One supplier per line.') }}</li>
                            <li><strong>{{ __('Name') }}</strong> {{ __('(English or Arabic) is the only thing that must be there. A missing English or Arabic name is copied from the other one.') }}</li>
                            <li><strong>{{ __('Columns we understand') }}</strong>: {{ __('email, phone, VAT number, CR number, address, city, building number, additional number, postal code, country, currency, credit limit, credit days, IBAN, contact person.') }}</li>
                            <li><strong>{{ __('Country / Currency') }}</strong> {{ __('when missing, your company\'s country and currency are used.') }}</li>
                            <li>{{ __('Excel / CSV: after uploading you match your columns to our fields. A PDF or photo is read by AI.') }}</li>
                        </ul>
                        <h6 class="mt-3">{{ __('How it is checked') }}</h6>
                        <table class="imp-rules">
                            <tr><td class="text-success">{{ __('New') }}</td><td>{{ __('Not in your list yet. It will be created if you confirm.') }}</td></tr>
                            <tr><td class="text-secondary">{{ __('Already exists') }}</td><td>{{ __('Same name (English or Arabic) or same VAT number already in your list. Skipped, so uploading twice is safe.') }}</td></tr>
                            <tr><td style="color:#d97706;">{{ __('Needs attention') }}</td><td>{{ __('No name, a value too long, or listed twice in the file. These rows are never imported.') }}</td></tr>
                            <tr><td class="text-primary">{{ __('Note') }}</td><td>{{ __('Anything we adjusted for you (copied name, default country or currency, an email or phone that was not valid) is written in the Note column for you to review.') }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Step 2: match the columns --}}
            <div class="imp-card mt-3 d-none" id="impMapCard">
                <h6><span class="imp-step">2</span>{{ __('Match your columns') }}</h6>
                <div class="imp-help mb-2">{{ __('For each of our fields, choose the column of your file that holds it. We matched what we could from the column names: change anything that is wrong, and leave a field empty when your file does not have it. VAT and CR in one column are split automatically.') }}</div>
                <div class="map-grid" id="imp-map"></div>
                <div class="d-flex align-items-center gap-2 mt-3">
                    <span class="text-muted small me-auto" id="imp-rowcount"></span>
                    <button type="button" class="btn btn-outline-secondary px-3" id="imp-map-cancel">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-primary px-4" id="imp-preview"><i class="bi bi-eye me-1"></i>{{ __('Preview') }}</button>
                </div>
            </div>

            {{-- Step 3: review --}}
            <div class="imp-card mt-3 d-none" id="impReviewCard">
                <h6><span class="imp-step">3</span>{{ __('Review and confirm') }}</h6>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2" id="imp-summary"></div>
                <div class="imp-scroll">
                    <table class="table table-sm table-hover align-middle mb-0 imp-table">
                        <thead>
                        <tr>
                            <th style="width:34px"><input type="checkbox" class="form-check-input" id="imp-check-all" checked aria-label="{{ __('Select all') }}"></th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Contact') }}</th>
                            <th>{{ __('VAT / CR') }}</th>
                            <th>{{ __('City / Country') }}</th>
                            @if($isCustomer)<th>{{ __('Salesperson') }}</th>@endif
                            <th>{{ __('Currency') }}</th>
                            <th>{{ __('Credit') }}</th>
                            <th>{{ __('Note') }}</th>
                        </tr>
                        </thead>
                        <tbody id="imp-tbody"></tbody>
                    </table>
                </div>
                <div class="d-flex align-items-center gap-2 mt-3">
                    <span class="text-muted small me-auto">{{ __('Everything look right?') }}</span>
                    <button type="button" class="btn btn-outline-secondary px-3" id="imp-back">{{ __('Change columns') }}</button>
                    <button type="button" class="btn btn-outline-secondary px-3" id="imp-cancel">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-primary px-4" id="imp-confirm"><i class="bi bi-check-lg me-1"></i>{{ __('Yes, Import') }}</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        var $ = function (id) { return document.getElementById(id); };
        var kind = @json($kind), listUrl = @json($listUrl);
        var isCustomer = kind === 'customer';
        // our fields, in the order the match boxes show them: [field, label, hint]
        var FIELDS = [
            ['name_en', @json(__('Name (English)')), @json(__('Name in English or Arabic is required'))], ['name_ar', @json(__('Name (Arabic)')), ''],
            ['vat_number', @json(__('VAT Number')), @json(__('may also hold the CR: it is split'))], ['cr_number', @json(__('CR Number')), ''],
            ['email', @json(__('Email')), ''], ['phone', @json(__('Phone')), ''], ['alt_phone', @json(__('Alternative Phone')), ''], ['contact_person', @json(__('Contact Person')), ''],
            ['address1_en', @json(__('Address 1 (English)')), ''], ['address1_ar', @json(__('Address 1 (Arabic)')), ''], ['address2_en', @json(__('Address 2 / District')), ''],
            ['building_number', @json(__('Building Number')), ''], ['plot_no', @json(__('Additional Number')), ''], ['postal_code', @json(__('Postal Code')), ''],
            ['city_en', @json(__('City (English)')), ''], ['city_ar', @json(__('City (Arabic)')), ''], ['country', @json(__('Country')), @json(__('empty: your company\'s country'))],
            ['currency', @json(__('Currency')), @json(__('empty: your company\'s currency'))], ['credit_limit', @json(__('Credit Limit')), ''], ['credit_days', @json(__('Credit Days')), ''], ['iban', @json(__('IBAN')), '']
        ];
        if (isCustomer) FIELDS.push(['salesperson', @json(__('Salesperson')), @json(__('matched by name'))]);
        var fields = FIELDS.map(function (f) { return f[0]; });
        var rows = [], token = null;
        var T = {
            statusNew: @json(__('New')), statusExists: @json(__('Already exists')), statusInvalid: @json(__('Needs attention')),
            total: @json(__('rows')), toImport: @json(__('to import')), skipped: @json(__('skipped')), invalid: @json(__('need attention')),
            importing: @json(__('Importing…')), yes: @json(__('Yes, Import')), failed: @json(__('Could not analyse the file.')), none: @json(__('Nothing selected to import.')),
            days: @json(__('days')), notInFile: @json(__('— not in my file —')), column: @json(__('Column')), example: @json(__('e.g.')),
            needName: @json(__('Choose which column holds the name (English or Arabic).'))
        };
        function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }
        function esc(s) { return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
        function two(a, b) { return esc(a || '—') + (b ? '<div class="sub">' + esc(b) + '</div>' : ''); }
        function showError(m) { var e = $('imp-error'); e.textContent = m; e.classList.remove('d-none'); }
        function clearError() { $('imp-error').classList.add('d-none'); }

        $('imp-file').addEventListener('change', function () { $('imp-upload').disabled = !this.files.length; clearError(); });
        function reset() {
            rows = []; token = null; $('impMapCard').classList.add('d-none'); $('impReviewCard').classList.add('d-none'); $('imp-file').value = ''; $('imp-upload').disabled = true; clearError();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        $('imp-cancel').addEventListener('click', reset);
        $('imp-map-cancel').addEventListener('click', reset);
        $('imp-back').addEventListener('click', function () {
            $('impReviewCard').classList.add('d-none');
            $('impMapCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
        function post(url, body, json) {
            var h = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf() };
            if (json) h['Content-Type'] = 'application/json';
            return fetch(url, { method: 'POST', body: json ? JSON.stringify(body) : body, headers: h })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); });
        }
        function failMsg(d) { return d.error || (d.errors && Object.values(d.errors).flat().join(' ')) || d.message || T.failed; }
        function showReview(d) {
            rows = d.rows.map(function (r) { r.checked = r.status === 'new'; return r; });
            $('imp-check-all').checked = true;
            $('imp-back').classList.toggle('d-none', !token);
            $('impReviewCard').classList.remove('d-none');
            render();
            $('impReviewCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        function showMap(d) {
            token = d.token;
            $('imp-map').innerHTML = FIELDS.map(function (f) {
                var opts = '<option value="">' + T.notInFile + '</option>' + d.headers.map(function (h, i) {
                    return '<option value="' + i + '"' + (String(d.suggest[f[0]]) === String(i) ? ' selected' : '') + '>' + esc(h || (T.column + ' ' + (i + 1))) + '</option>';
                }).join('');
                return '<div class="map-row"><label for="map-' + f[0] + '">' + f[1] + (f[2] ? '<small>' + f[2] + '</small>' : '') + '</label>' +
                    '<select class="tom-select" data-live-search="true" id="map-' + f[0] + '" data-field="' + f[0] + '">' + opts + '</select><div class="map-ex" id="ex-' + f[0] + '"></div></div>';
            }).join('');
            var examples = d.examples;
            function example(sel) { var v = sel.value === '' ? '' : examples[sel.value]; $('ex-' + sel.dataset.field).textContent = v ? T.example + ' ' + v : ''; }
            $('imp-map').querySelectorAll('select').forEach(function (sel) { example(sel); sel.addEventListener('change', function () { example(sel); }); });
            if (window.initTomSelectForm && window.jQuery) initTomSelectForm(jQuery('#imp-map'));
            $('imp-rowcount').textContent = d.total + ' ' + T.total;
            $('impReviewCard').classList.add('d-none');
            $('impMapCard').classList.remove('d-none');
            $('impMapCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function selectedCount() { return rows.filter(function (r) { return r.status === 'new' && r.checked; }).length; }
        function refreshSummary() {
            var ex = rows.filter(function (r) { return r.status === 'exists'; }).length;
            var inv = rows.filter(function (r) { return r.status === 'invalid'; }).length;
            $('imp-summary').innerHTML = '<span class="badge bg-light text-dark border">' + rows.length + ' ' + T.total + '</span>' +
                '<span class="badge bg-success">' + selectedCount() + ' ' + T.toImport + '</span>' +
                (ex ? '<span class="badge bg-secondary">' + ex + ' ' + T.skipped + '</span>' : '') +
                (inv ? '<span class="badge bg-warning text-dark">' + inv + ' ' + T.invalid + '</span>' : '');
            $('imp-confirm').disabled = selectedCount() === 0;
        }
        function render() {
            $('imp-tbody').innerHTML = rows.map(function (r, i) {
                var badge = r.status === 'new' ? '<span class="badge bg-success-subtle text-success border border-success-subtle">' + T.statusNew + '</span>'
                    : r.status === 'exists' ? '<span class="badge bg-secondary-subtle text-secondary border">' + T.statusExists + '</span>'
                    : '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">' + T.statusInvalid + '</span>';
                var can = r.status === 'new';
                var credit = (r.credit_limit ? r.credit_limit : '') + (r.credit_days ? (r.credit_limit ? ' · ' : '') + r.credit_days + ' ' + T.days : '');
                return '<tr class="' + (can ? '' : 'row-skip') + '"><td><input type="checkbox" class="form-check-input imp-check" data-i="' + i + '"' + (can ? (r.checked ? ' checked' : '') : ' disabled') + '></td>' +
                    '<td>' + badge + '</td>' +
                    '<td class="fw-semibold imp-name">' + two(r.name_en, r.name_ar !== r.name_en ? r.name_ar : '') + '</td>' +
                    '<td>' + two(r.phone + (r.alt_phone ? ' / ' + r.alt_phone : ''), r.email) + '</td>' +
                    '<td>' + two(r.vat_number, r.cr_number) + '</td>' +
                    '<td>' + two(r.city_en, r.country) + '</td>' + (isCustomer ? '<td>' + esc(r.salesperson || '—') + '</td>' : '') +
                    '<td>' + esc(r.currency) + '</td><td>' + esc(credit || '—') + '</td>' +
                    '<td class="text-muted imp-note">' + esc(r.note) + '</td></tr>';
            }).join('');
            refreshSummary();
        }
        $('imp-tbody').addEventListener('change', function (e) {
            if (!e.target.classList.contains('imp-check')) return;
            rows[+e.target.dataset.i].checked = e.target.checked;
            refreshSummary();
        });
        $('imp-check-all').addEventListener('change', function (e) {
            rows.forEach(function (r) { if (r.status === 'new') r.checked = e.target.checked; });
            render();
        });

        $('imp-upload').addEventListener('click', function () {
            var file = $('imp-file').files && $('imp-file').files[0];
            if (!file) return;
            clearError();
            var btn = this; btn.disabled = true; $('imp-loading').classList.remove('d-none');
            var fd = new FormData(); fd.append('file', file);
            post('/' + kind + '/import/upload', fd)
                .then(function (res) {
                    $('imp-loading').classList.add('d-none'); btn.disabled = false;
                    if (!res.ok) { showError(failMsg(res.d)); return; }
                    token = null;
                    if (res.d.mode === 'map') showMap(res.d); else { $('impMapCard').classList.add('d-none'); showReview(res.d); }
                })
                .catch(function () { $('imp-loading').classList.add('d-none'); btn.disabled = false; showError(T.failed); });
        });

        $('imp-preview').addEventListener('click', function () {
            var map = {};
            $('imp-map').querySelectorAll('select').forEach(function (sel) { if (sel.value !== '') map[sel.dataset.field] = +sel.value; });
            if (map.name_en === undefined && map.name_ar === undefined) { showError(T.needName); window.scrollTo({ top: 0, behavior: 'smooth' }); return; }
            clearError();
            var btn = this; btn.disabled = true;
            post('/' + kind + '/import/preview', { token: token, map: map }, true)
                .then(function (res) {
                    btn.disabled = false;
                    if (!res.ok) { showError(failMsg(res.d)); window.scrollTo({ top: 0, behavior: 'smooth' }); return; }
                    showReview(res.d);
                })
                .catch(function () { btn.disabled = false; showError(T.failed); });
        });

        $('imp-confirm').addEventListener('click', function () {
            var chosen = rows.filter(function (r) { return r.status === 'new' && r.checked; });
            if (!chosen.length) { showError(T.none); return; }
            var btn = this; btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> ' + T.importing;
            clearError();
            post('/' + kind + '/import', { token: token, rows: chosen.map(function (r) { var o = {}; fields.forEach(function (f) { o[f] = r[f] == null ? '' : String(r[f]); }); return o; }) }, true)
                .then(function (res) {
                    if (!res.ok || res.d.status !== 'success') { btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> ' + T.yes; showError(res.d.message || T.failed); window.scrollTo({ top: 0, behavior: 'smooth' }); return; }
                    if (window.toastr) toastr.success(res.d.message);
                    setTimeout(function () { window.location.href = listUrl; }, 700);
                })
                .catch(function () { btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> ' + T.yes; showError(T.failed); });
        });
    })();
    </script>
</x-app-layout>
