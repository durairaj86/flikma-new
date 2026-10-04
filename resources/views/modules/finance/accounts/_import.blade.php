{{-- Chart of accounts import: upload any file, AI maps it to our structure, customer reviews, then imports or cancels. --}}
<style>
    #accountImportModal .import-table { font-size: .82rem; }
    #accountImportModal .import-table th { position: sticky; top: 0; background: var(--bs-light, #f8f9fa); z-index: 1; }
    #accountImportModal .import-scroll { max-height: 52vh; overflow: auto; border: 1px solid #e9ecef; border-radius: .5rem; }
    #accountImportModal .import-drop { border: 2px dashed #c7d2fe; border-radius: .75rem; background: #f5f3ff; cursor: pointer; }
    #accountImportModal .import-drop:hover { background: #eef2ff; }
    #accountImportModal tr.row-skip { opacity: .55; }
</style>

<div class="modal fade" id="accountImportModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold"><i class="bi bi-stars text-primary me-1"></i> {{ __('Import Chart of Accounts') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body">
                {{-- Step 1: upload --}}
                <div id="imp-step-upload">
                    <label class="import-drop d-block text-center p-5 mb-0" for="imp-file">
                        <i class="bi bi-cloud-arrow-up fs-1 text-primary"></i>
                        <div class="fw-semibold mt-2">{{ __('Click to choose your file') }}</div>
                        <div class="text-muted small">{{ __('Excel, CSV, PDF or a photo — any layout. AI will map it to our structure and show it to you before anything is saved.') }}</div>
                        <input type="file" id="imp-file" class="d-none" accept=".xlsx,.xls,.csv,.txt,.pdf,image/*">
                    </label>
                </div>
                {{-- Step 2: analysing --}}
                <div id="imp-step-loading" class="text-center py-5 d-none">
                    <div class="spinner-border text-primary mb-3"></div>
                    <div class="fw-semibold" id="imp-loading-text">{{ __('AI is reading your file…') }}</div>
                    <div class="text-muted small">{{ __('Large files can take a minute') }}</div>
                </div>
                {{-- Step 3: review --}}
                <div id="imp-step-review" class="d-none">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2" id="imp-summary"></div>
                    <div class="import-scroll">
                        <table class="table table-sm table-hover align-middle mb-0 import-table">
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
                </div>
                <div class="alert alert-danger border-0 mb-0 d-none" id="imp-error"></div>
            </div>
            <div class="modal-footer border-0 pt-0 d-none" id="imp-footer">
                <span class="text-muted small me-auto" id="imp-footer-hint">{{ __('Everything look right?') }}</span>
                <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary btn-sm px-4" id="imp-confirm"><i class="bi bi-check-lg me-1"></i> {{ __('Yes, Import') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    document.querySelectorAll('body > #accountImportModal').forEach(function (o) { o.remove(); });
    var modalEl = document.getElementById('accountImportModal');
    document.body.appendChild(modalEl);
    var $ = function (id) { return document.getElementById(id); };
    var rows = [];
    var T = {
        statusNew: @json(__('New')), statusExists: @json(__('Already exists')), statusInvalid: @json(__('Needs attention')),
        total: @json(__('rows')), toImport: @json(__('to import')), skipped: @json(__('skipped')), invalid: @json(__('need attention')),
        importing: @json(__('Importing…')), yes: @json(__('Yes, Import')), failed: @json(__('Could not analyse the file.')),
        importN: @json(__('Yes, Import')), none: @json(__('Nothing selected to import.'))
    };
    function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }
    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
    function show(step) {
        ['upload', 'loading', 'review'].forEach(function (s) { $('imp-step-' + s).classList.toggle('d-none', s !== step); });
        $('imp-footer').classList.toggle('d-none', step !== 'review');
    }
    function showError(m) { var e = $('imp-error'); e.textContent = m; e.classList.remove('d-none'); }
    function reset() { $('imp-error').classList.add('d-none'); $('imp-file').value = ''; rows = []; show('upload'); }

    document.addEventListener('click', function (e) {
        if (e.target.closest('#btn-import-accounts')) { reset(); bootstrap.Modal.getOrCreateInstance(modalEl).show(); }
    });

    function selectedCount() { return rows.filter(function (r) { return r.status === 'new' && r.checked; }).length; }
    function refreshSummary() {
        var n = rows.filter(function (r) { return r.status === 'new'; }).length;
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

    $('imp-file').addEventListener('change', function (e) {
        var file = e.target.files && e.target.files[0];
        if (!file) return;
        $('imp-error').classList.add('d-none');
        show('loading');
        var fd = new FormData();
        fd.append('file', file);
        fd.append('_token', csrf());
        fetch('/finance/account/import/preview', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
            .then(function (res) {
                if (!res.ok) { show('upload'); showError(res.d.error || (res.d.errors && Object.values(res.d.errors).flat().join(' ')) || res.d.message || T.failed); return; }
                rows = res.d.rows.map(function (r) { r.checked = r.status === 'new'; return r; });
                show('review');
                render();
            })
            .catch(function () { show('upload'); showError(T.failed); });
    });

    $('imp-confirm').addEventListener('click', function () {
        var chosen = rows.filter(function (r) { return r.status === 'new' && r.checked; });
        if (!chosen.length) { showError(T.none); return; }
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> ' + T.importing;
        $('imp-error').classList.add('d-none');
        fetch('/finance/account/import', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ rows: chosen.map(function (r) { return { code: r.code, name: r.name, type: r.type, parent_code: r.parent_code, parent_name: r.parent_name, description: r.description, account_number: r.account_number }; }) })
        })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
            .then(function (res) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> ' + T.yes;
                if (!res.ok || res.d.status !== 'success') { showError(res.d.message || T.failed); return; }
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                if (window.toastr) toastr.success(res.d.message);
                window.location.reload();
            })
            .catch(function () { btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> ' + T.yes; showError(T.failed); });
    });
})();
</script>
