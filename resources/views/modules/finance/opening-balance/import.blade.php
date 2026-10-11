@section('page-title', __('Import Opening Balance'))
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
            .imp-table th { position: sticky; top: 0; background: #f8f9fa; z-index: 1; }
            tr.row-skip { opacity: .55; }
        </style>
        <style>
            .map-row { display: grid; grid-template-columns: 190px 1fr; gap: 10px; align-items: center; padding: 6px 0; border-bottom: 1px solid #eef0f3; }
            .map-row:last-child { border-bottom: 0; }
            .map-row label { font-weight: 600; font-size: .88rem; margin: 0; }
            .map-row small { color: #667085; font-weight: 400; }
            .ob-totals { display: flex; gap: 22px; flex-wrap: wrap; align-items: center; background: #f8f9fa; border-radius: 10px; padding: 10px 16px; margin: 10px 0; }
            .ob-totals b { font-size: 1.05rem; }
            .num { text-align: right; font-variant-numeric: tabular-nums; }
        </style>

        <div class="imp-wrap">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <div class="imp-crumb"><a href="{{ route('finance.opening-balance') }}">{{ __('Opening Balance') }}</a> &nbsp;/&nbsp; {{ __('Import') }}</div>
                    <h1 class="imp-h1">{{ __('Import Opening Balance') }}</h1>
                    <div class="imp-sub">{{ __('Upload a trial balance or a list of customer and supplier balances, tell us which column is which, check the preview, then import.') }}</div>
                </div>
                <a href="{{ route('finance.opening-balance') }}" class="btn btn-outline-secondary btn-sm px-3"><i class="bi bi-arrow-left me-1"></i>{{ __('Back to Opening Balance') }}</a>
            </div>

            <div class="row g-3 mt-2">
                {{-- Step 1 --}}
                <div class="col-lg-6">
                    <div class="imp-card d-flex flex-column">
                        <h6><span class="imp-step">1</span>{{ __('Upload the file') }}</h6>
                        <input type="file" id="ob-file" class="form-control" accept=".xlsx,.xls,.csv,.txt">
                        <div class="imp-help mt-2">{{ __('.xlsx, .xls or .csv. The first row with column names is used as the heading. Up to 5,000 rows.') }}</div>

                        <div class="row g-2 mt-2">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">{{ __('Opening balance date') }} <span class="text-danger">*</span></label>
                                <input type="date" id="ob-date" class="form-control" value="{{ $lockedDate }}" @if($lockedDate) readonly @endif>
                                @if($lockedDate)<div class="form-text">{{ __('Fixed: opening balances already exist on this date.') }}</div>@endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">{{ __('The rows in my file are') }}</label>
                                <select id="ob-kind" class="form-select">
                                    <option value="auto">{{ __('A mix (find out for each row)') }}</option>
                                    <option value="account">{{ __('Accounts') }}</option>
                                    <option value="customer">{{ __('Customers') }}</option>
                                    <option value="supplier">{{ __('Suppliers') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="alert alert-danger border-0 mt-3 mb-0 d-none" id="ob-error"></div>
                        <div class="d-flex gap-2 mt-auto pt-4">
                            <button type="button" class="btn btn-primary" id="ob-upload" disabled><i class="bi bi-upload me-1"></i>{{ __('Upload') }}</button>
                            <a href="{{ route('finance.opening-balance.import.sample') }}" class="btn btn-outline-secondary"><i class="bi bi-download me-1"></i>{{ __('Download sample CSV') }}</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="imp-card">
                        <h6>{{ __('What the file needs') }}</h6>
                        <ul class="imp-list">
                            <li><strong>{{ __('A name or code') }}</strong> {{ __('for each line: the account, customer or supplier it belongs to.') }}</li>
                            <li><strong>{{ __('Debit and Credit') }}</strong> {{ __('columns, or one Amount column with a Dr/Cr column (or "1,200 Dr").') }}</li>
                            <li>{{ __('An optional Type column (Account, Customer, Supplier) helps when names could match more than one.') }}</li>
                            <li>{{ __('Customer balances post to Accounts Receivable and supplier balances to Accounts Payable automatically.') }}</li>
                        </ul>
                        <h6 class="mt-3">{{ __('How it is checked') }}</h6>
                        <table class="imp-rules">
                            <tr><td class="text-success">{{ __('New') }}</td><td>{{ __('Matched, and has no opening balance yet. Will be imported.') }}</td></tr>
                            <tr><td class="text-secondary">{{ __('Already has one') }}</td><td>{{ __('That account, customer or supplier already has an opening balance. Skipped.') }}</td></tr>
                            <tr><td style="color:#d97706;">{{ __('Needs attention') }}</td><td>{{ __('Not found, listed twice, or the amount is not a number. Not imported.') }}</td></tr>
                            <tr><td class="text-primary">{{ __('Balanced') }}</td><td>{{ __('Total debit must equal total credit. If it does not, you choose a balancing account (for example Owner Capital).') }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Step 2: map the columns --}}
            <div class="imp-card mt-3 d-none" id="obMapCard">
                <h6><span class="imp-step">2</span>{{ __('Match your columns') }}</h6>
                <div class="imp-help mb-2">{{ __('We matched what we could from the column names. Change anything that is wrong.') }}</div>
                <div class="row g-4">
                    <div class="col-lg-5" id="ob-map"></div>
                    <div class="col-lg-7">
                        <div class="fw-semibold small mb-1">{{ __('First rows of your file') }}</div>
                        <div class="imp-scroll" style="max-height:260px"><table class="table table-sm imp-table mb-0" id="ob-sample"></table></div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 mt-3">
                    <span class="text-muted small me-auto" id="ob-rowcount"></span>
                    <button type="button" class="btn btn-primary px-4" id="ob-check"><i class="bi bi-eye me-1"></i>{{ __('Preview') }}</button>
                </div>
            </div>

            {{-- Step 3: preview --}}
            <div class="imp-card mt-3 d-none" id="obReviewCard">
                <h6><span class="imp-step">3</span>{{ __('Review and confirm') }}</h6>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1" id="ob-summary"></div>
                <div class="ob-totals">
                    <div>{{ __('Debit') }}: <b id="ob-tdr">0.00</b></div>
                    <div>{{ __('Credit') }}: <b id="ob-tcr">0.00</b></div>
                    <div>{{ __('Difference') }}: <b id="ob-tdiff">0.00</b></div>
                    <div id="ob-contra" class="d-none ms-auto d-flex align-items-center gap-2">
                        <span class="small text-danger fw-semibold">{{ __('Does not balance. Balancing account:') }}</span>
                        <select id="ob-contra-id" class="form-select form-select-sm" style="min-width:240px">
                            <option value="">{{ __('Choose an account...') }}</option>
                            @foreach($contra as $a)<option value="{{ $a->id }}">{{ $a->code }} · {{ $a->name }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="imp-scroll">
                    <table class="table table-sm table-hover align-middle mb-0 imp-table">
                        <thead><tr>
                            <th style="width:34px"><input type="checkbox" class="form-check-input" id="ob-check-all" checked></th>
                            <th>{{ __('Status') }}</th><th>{{ __('In your file') }}</th><th>{{ __('Matched to') }}</th>
                            <th class="num">{{ __('Debit') }}</th><th class="num">{{ __('Credit') }}</th><th>{{ __('Note') }}</th>
                        </tr></thead>
                        <tbody id="ob-tbody"></tbody>
                    </table>
                </div>
                <div class="d-flex align-items-center gap-2 mt-3">
                    <span class="text-muted small me-auto">{{ __('Nothing is saved until you press Import.') }}</span>
                    <button type="button" class="btn btn-outline-secondary px-3" id="ob-back">{{ __('Change columns') }}</button>
                    <button type="button" class="btn btn-primary px-4" id="ob-confirm"><i class="bi bi-check-lg me-1"></i>{{ __('Import') }}</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        var $ = function (id) { return document.getElementById(id); };
        var URL_ = {upload: @json(route('finance.opening-balance.import.upload')), check: @json(route('finance.opening-balance.import.check')), confirm: @json(route('finance.opening-balance.import.confirm'))};
        var FIELDS = [
            ['name', @json(__('Name')), @json(__('Account, customer or supplier name'))], ['code', @json(__('Code')), @json(__('optional'))], ['kind', @json(__('Type')), @json(__('optional: Account / Customer / Supplier'))],
            ['debit', @json(__('Debit')), ''], ['credit', @json(__('Credit')), ''], ['amount', @json(__('Amount')), @json(__('use instead of Debit / Credit'))],
            ['drcr', @json(__('Dr / Cr')), @json(__('with Amount'))], ['notes', @json(__('Notes')), @json(__('optional'))]
        ];
        var state = {token: null, headers: [], rows: []};
        var T = {new: @json(__('New')), exists: @json(__('Already has one')), invalid: @json(__('Needs attention')), skip: @json(__('Skipped')), rows: @json(__('rows')), toImport: @json(__('to import')), importing: @json(__('Importing…')), import: @json(__('Import'))};
        function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }
        function esc(s) { return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) { return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;'}[c]; }); }
        function money(n) { return Number(n).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
        function err(m) { var e = $('ob-error'); e.textContent = m; e.classList.remove('d-none'); window.scrollTo({top: 0, behavior: 'smooth'}); }
        function clearErr() { $('ob-error').classList.add('d-none'); }
        function post(url, body) {
            return fetch(url, {method: 'POST', body: body, headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'}})
                .then(function (r) { return r.json().then(function (d) { return {ok: r.ok, d: d}; }); });
        }
        function currentMap() {
            var m = {};
            FIELDS.forEach(function (f) { var v = $('map-' + f[0]).value; if (v !== '') m[f[0]] = v; });
            return m;
        }
        function payload() {
            var fd = new FormData();
            fd.append('_token', csrf()); fd.append('token', state.token); fd.append('kind', $('ob-kind').value); fd.append('date', $('ob-date').value);
            var m = currentMap(); Object.keys(m).forEach(function (k) { fd.append('map[' + k + ']', m[k]); });
            return fd;
        }

        $('ob-file').addEventListener('change', function () { $('ob-upload').disabled = !this.files.length; clearErr(); });

        $('ob-upload').addEventListener('click', function () {
            var f = $('ob-file').files[0]; if (!f) return;
            clearErr(); var b = this; b.disabled = true;
            var fd = new FormData(); fd.append('file', f); fd.append('_token', csrf());
            post(URL_.upload, fd).then(function (res) {
                b.disabled = false;
                if (!res.ok) { err(res.d.error || (res.d.errors ? Object.values(res.d.errors)[0][0] : @json(__('Could not read this file.')))); return; }
                state.token = res.d.token; state.headers = res.d.headers;
                // mapping selects
                $('ob-map').innerHTML = FIELDS.map(function (f) {
                    var opts = '<option value="">' + @json(__('— not in my file —')) + '</option>' + res.d.headers.map(function (h, i) { return '<option value="' + i + '"' + (String(res.d.suggest[f[0]]) === String(i) ? ' selected' : '') + '>' + esc(h || (@json(__('Column')) + ' ' + (i + 1))) + '</option>'; }).join('');
                    return '<div class="map-row"><label>' + f[1] + (f[2] ? '<br><small>' + f[2] + '</small>' : '') + '</label><select class="form-select form-select-sm" id="map-' + f[0] + '">' + opts + '</select></div>';
                }).join('');
                $('ob-sample').innerHTML = '<thead><tr>' + res.d.headers.map(function (h) { return '<th>' + esc(h) + '</th>'; }).join('') + '</tr></thead><tbody>' +
                    res.d.sample.map(function (r) { return '<tr>' + r.map(function (c) { return '<td>' + esc(c) + '</td>'; }).join('') + '</tr>'; }).join('') + '</tbody>';
                $('ob-rowcount').textContent = res.d.total + ' ' + T.rows;
                $('obMapCard').classList.remove('d-none'); $('obReviewCard').classList.add('d-none');
                $('obMapCard').scrollIntoView({behavior: 'smooth'});
            });
        });

        function render() {
            var sel = state.rows.filter(function (r) { return r.status === 'new' && r.checked; });
            var dr = 0, cr = 0; sel.forEach(function (r) { dr += r.debit; cr += r.credit; });
            var diff = Math.round((dr - cr) * 100) / 100;
            $('ob-tdr').textContent = money(dr); $('ob-tcr').textContent = money(cr);
            $('ob-tdiff').textContent = money(Math.abs(diff)); $('ob-tdiff').className = diff === 0 ? 'text-success' : 'text-danger';
            $('ob-contra').classList.toggle('d-none', diff === 0);
            var c = {total: state.rows.length, exists: 0, invalid: 0};
            state.rows.forEach(function (r) { if (r.status === 'exists') c.exists++; if (r.status === 'invalid') c.invalid++; });
            $('ob-summary').innerHTML = '<span class="badge bg-light text-dark border">' + c.total + ' ' + T.rows + '</span><span class="badge bg-success">' + sel.length + ' ' + T.toImport + '</span>' +
                (c.exists ? '<span class="badge bg-secondary">' + c.exists + ' ' + T.exists + '</span>' : '') + (c.invalid ? '<span class="badge bg-warning text-dark">' + c.invalid + ' ' + T.invalid + '</span>' : '');
            $('ob-confirm').disabled = !sel.length;
            $('ob-tbody').innerHTML = state.rows.map(function (r, i) {
                var cls = r.status === 'new' ? 'bg-success-subtle text-success border border-success-subtle' : (r.status === 'invalid' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : 'bg-secondary-subtle text-secondary border');
                var kind = r.kind ? '<span class="badge bg-light text-dark border me-1">' + {account: @json(__('Account')), customer: @json(__('Customer')), supplier: @json(__('Supplier'))}[r.kind] + '</span>' : '';
                return '<tr class="' + (r.status === 'new' ? '' : 'row-skip') + '"><td><input type="checkbox" class="form-check-input ob-chk" data-i="' + i + '"' + (r.status === 'new' ? (r.checked ? ' checked' : '') : ' disabled') + '></td>' +
                    '<td><span class="badge ' + cls + '">' + T[r.status] + '</span></td><td class="fw-semibold">' + esc(r.name) + '</td><td>' + kind + esc(r.matched || '—') + '</td>' +
                    '<td class="num">' + (r.debit ? money(r.debit) : '') + '</td><td class="num">' + (r.credit ? money(r.credit) : '') + '</td><td class="text-muted">' + esc(r.note) + '</td></tr>';
            }).join('');
        }
        $('ob-tbody').addEventListener('change', function (e) { if (e.target.classList.contains('ob-chk')) { state.rows[+e.target.dataset.i].checked = e.target.checked; render(); } });
        $('ob-check-all').addEventListener('change', function (e) { state.rows.forEach(function (r) { if (r.status === 'new') r.checked = e.target.checked; }); render(); });
        $('ob-contra-id').addEventListener('change', function () { /* chosen on confirm */ });

        $('ob-check').addEventListener('click', function () {
            clearErr(); var b = this; b.disabled = true;
            post(URL_.check, payload()).then(function (res) {
                b.disabled = false;
                if (!res.ok) { err(res.d.error || (res.d.errors ? Object.values(res.d.errors)[0][0] : @json(__('Could not check the file.')))); return; }
                state.rows = res.d.rows.filter(function (r) { return r.status !== 'skip'; }).map(function (r) { r.checked = true; return r; });
                render();
                $('obReviewCard').classList.remove('d-none'); $('obReviewCard').scrollIntoView({behavior: 'smooth'});
            });
        });
        $('ob-back').addEventListener('click', function () { $('obReviewCard').classList.add('d-none'); $('obMapCard').scrollIntoView({behavior: 'smooth'}); });

        $('ob-confirm').addEventListener('click', function () {
            var sel = state.rows.filter(function (r) { return r.status === 'new' && r.checked; });
            if (!sel.length) return;
            var dr = 0, cr = 0; sel.forEach(function (r) { dr += r.debit; cr += r.credit; });
            if (Math.round((dr - cr) * 100) !== 0 && !$('ob-contra-id').value) { err(@json(__('Choose a balancing account for the difference.'))); return; }
            clearErr(); var b = this; b.disabled = true; b.textContent = T.importing;
            var fd = payload(); fd.append('balancing_account_id', $('ob-contra-id').value);
            sel.forEach(function (r) { fd.append('selected[]', r.row); });
            post(URL_.confirm, fd).then(function (res) {
                if (!res.ok) { b.disabled = false; b.textContent = T.import; err(res.d.message || res.d.error || @json(__('Import failed.'))); return; }
                if (window.toastr) toastr.success(res.d.message);
                window.location.href = res.d.url;
            });
        });
    })();
    </script>
</x-app-layout>
