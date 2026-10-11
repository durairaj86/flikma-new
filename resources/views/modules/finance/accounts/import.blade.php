@section('page-title', __('Import Chart of Accounts'))
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

        <div class="imp-wrap">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <div class="imp-crumb"><a href="/finance/accounts">{{ __('Finance') }}</a> &nbsp;/&nbsp; <a href="/finance/accounts">{{ __('Chart of Accounts') }}</a> &nbsp;/&nbsp; {{ __('Import') }}</div>
                    <h1 class="imp-h1">{{ __('Import Chart of Accounts') }}</h1>
                    <div class="imp-sub">{{ __('Moving from another system? Upload your chart of accounts here. AI maps any layout to ours and you confirm before anything is saved.') }}</div>
                </div>
                <a href="/finance/accounts" class="btn btn-outline-secondary btn-sm px-3"><i class="bi bi-arrow-left me-1"></i>{{ __('Back to Chart of Accounts') }}</a>
            </div>

            <div class="row g-3 mt-2">
                {{-- Step 1: upload --}}
                <div class="col-lg-6">
                    <div class="imp-card d-flex flex-column" id="impUploadCard">
                        <h6><span class="imp-step">1</span>{{ __('Upload the file') }}</h6>
                        <input type="file" id="imp-file" class="form-control" accept=".xlsx,.xls,.csv,.txt,.pdf,image/*">
                        <div class="imp-help mt-2">{{ __('.xlsx, .xls, .csv, .pdf or a photo of the page. Up to 10 MB.') }}</div>
                        <div class="imp-help mt-3">{{ __('Nothing is saved yet. You will see every row checked first, and confirm before anything is imported.') }}</div>

                        <div id="imp-loading" class="text-center py-4 d-none">
                            <div class="spinner-border text-primary mb-2"></div>
                            <div class="fw-semibold">{{ __('AI is reading your file…') }}</div>
                            <div class="imp-help">{{ __('Large files can take a minute') }}</div>
                        </div>
                        <div class="alert alert-danger border-0 mt-3 mb-0 d-none" id="imp-error"></div>

                        <div class="d-flex gap-2 mt-auto pt-4">
                            <button type="button" class="btn btn-primary" id="imp-upload" disabled><i class="bi bi-upload me-1"></i>{{ __('Upload & check') }}</button>
                            <a href="/finance/account/import/sample" class="btn btn-outline-secondary"><i class="bi bi-download me-1"></i>{{ __('Download sample CSV') }}</a>
                        </div>
                    </div>
                </div>

                {{-- Guide --}}
                <div class="col-lg-6">
                    <div class="imp-card">
                        <h6>{{ __('What the file must look like') }}</h6>
                        <ul class="imp-list">
                            <li>{{ __('Excel, CSV, PDF or a photo, in any layout and any language. One account per line.') }}</li>
                            <li><strong>{{ __('Account name') }}</strong> {{ __('is the only thing that must be there.') }}</li>
                            <li><strong>{{ __('Code') }}</strong> {{ __('(4 to 6 digits) is optional. Missing or clashing codes get a new one automatically.') }}</li>
                            <li><strong>{{ __('Type') }}</strong> {{ __('(Asset, Liability, Equity, Income, Expense) is optional. It is worked out from the section heading, the first digit of the code or the name.') }}</li>
                            <li><strong>{{ __('Parent / group') }}</strong> {{ __('by code or by name. Indentation and code prefixes (1100 under 1000) are understood too.') }}</li>
                            <li>{{ __('Titles, blank rows and "Total" rows are ignored.') }}</li>
                        </ul>
                        <h6 class="mt-3">{{ __('How it is checked') }}</h6>
                        <table class="imp-rules">
                            <tr><td class="text-success">{{ __('New') }}</td><td>{{ __('Not in your chart yet. It will be created if you confirm.') }}</td></tr>
                            <tr><td class="text-secondary">{{ __('Already exists') }}</td><td>{{ __('Same name or code and name already in your chart. Skipped, so uploading twice is safe.') }}</td></tr>
                            <tr><td style="color:#d97706;">{{ __('Needs attention') }}</td><td>{{ __('No account name, or the account type could not be worked out. These rows are never imported.') }}</td></tr>
                            <tr><td class="text-primary">{{ __('Note') }}</td><td>{{ __('Anything we adjusted for you (new code, parent placed under its main type) is written in the Note column for you to review.') }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Step 2: review --}}
            <div class="imp-card mt-3 d-none" id="impReviewCard">
                <h6><span class="imp-step">2</span>{{ __('Review and confirm') }}</h6>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2" id="imp-summary"></div>
                <div class="imp-scroll">
                    <table class="table table-sm table-hover align-middle mb-0 imp-table">
                        <thead>
                        <tr>
                            <th style="width:34px"><input type="checkbox" class="form-check-input" id="imp-check-all" checked></th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Code') }}</th>
                            <th>{{ __('Account Name') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Parent Account') }}</th>
                            <th>{{ __('Note') }}</th>
                        </tr>
                        </thead>
                        <tbody id="imp-tbody"></tbody>
                    </table>
                </div>
                <div class="d-flex align-items-center gap-2 mt-3">
                    <span class="text-muted small me-auto">{{ __('Everything look right?') }}</span>
                    <button type="button" class="btn btn-outline-secondary px-3" id="imp-cancel">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-primary px-4" id="imp-confirm"><i class="bi bi-check-lg me-1"></i>{{ __('Yes, Import') }}</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        var $ = function (id) { return document.getElementById(id); };
        var rows = [];
        var T = {
            statusNew: @json(__('New')), statusExists: @json(__('Already exists')), statusInvalid: @json(__('Needs attention')),
            total: @json(__('rows')), toImport: @json(__('to import')), skipped: @json(__('skipped')), invalid: @json(__('need attention')),
            importing: @json(__('Importing…')), yes: @json(__('Yes, Import')), failed: @json(__('Could not analyse the file.')), none: @json(__('Nothing selected to import.'))
        };
        function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }
        function esc(s) { return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
        function showError(m) { var e = $('imp-error'); e.textContent = m; e.classList.remove('d-none'); }
        function clearError() { $('imp-error').classList.add('d-none'); }

        $('imp-file').addEventListener('change', function () { $('imp-upload').disabled = !this.files.length; clearError(); });
        $('imp-cancel').addEventListener('click', function () {
            rows = []; $('impReviewCard').classList.add('d-none'); $('imp-file').value = ''; $('imp-upload').disabled = true; clearError();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

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
                return '<tr class="' + (can ? '' : 'row-skip') + '"><td><input type="checkbox" class="form-check-input imp-check" data-i="' + i + '"' + (can ? (r.checked ? ' checked' : '') : ' disabled') + '></td>' +
                    '<td>' + badge + '</td><td class="fw-semibold">' + esc(r.code) + '</td>' +
                    '<td style="padding-left:' + (8 + (r.level || 0) * 14) + 'px">' + esc(r.name) + '</td>' +
                    '<td>' + esc(r.type || '') + '</td><td>' + esc(r.parent_name ? (r.parent_code ? r.parent_code + ' · ' : '') + r.parent_name : '—') + '</td>' +
                    '<td class="text-muted">' + esc(r.note) + '</td></tr>';
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
            var fd = new FormData(); fd.append('file', file); fd.append('_token', csrf());
            fetch('/finance/account/import/preview', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
                .then(function (res) {
                    $('imp-loading').classList.add('d-none'); btn.disabled = false;
                    if (!res.ok) { showError(res.d.error || (res.d.errors && Object.values(res.d.errors).flat().join(' ')) || res.d.message || T.failed); return; }
                    rows = res.d.rows.map(function (r) { r.checked = r.status === 'new'; return r; });
                    $('impReviewCard').classList.remove('d-none');
                    render();
                    $('impReviewCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
                })
                .catch(function () { $('imp-loading').classList.add('d-none'); btn.disabled = false; showError(T.failed); });
        });

        $('imp-confirm').addEventListener('click', function () {
            var chosen = rows.filter(function (r) { return r.status === 'new' && r.checked; });
            if (!chosen.length) { showError(T.none); return; }
            var btn = this; btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> ' + T.importing;
            clearError();
            fetch('/finance/account/import', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ rows: chosen.map(function (r) { return { code: r.code, name: r.name, type: r.type, parent_code: r.parent_code, parent_name: r.parent_name, description: r.description, account_number: r.account_number }; }) })
            })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
                .then(function (res) {
                    if (!res.ok || res.d.status !== 'success') { btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> ' + T.yes; showError(res.d.message || T.failed); window.scrollTo({ top: 0, behavior: 'smooth' }); return; }
                    if (window.toastr) toastr.success(res.d.message);
                    setTimeout(function () { window.location.href = '/finance/accounts'; }, 700);
                })
                .catch(function () { btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> ' + T.yes; showError(T.failed); });
        });
    })();
    </script>
</x-app-layout>
