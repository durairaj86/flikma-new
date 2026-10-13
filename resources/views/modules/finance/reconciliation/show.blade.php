@section('page-title', __('Bank Reconciliation'))
@section('page-subtitle', $rec->account->code . ' ' . $rec->account->name)
@section('hide-topbar', true)
<x-app-layout>
    <div class="bg-light pb-4 imp-page">
    @include('modules.finance.reconciliation._style')
    <main class="imp-wrap" id="recon" data-id="{{ $rec->id }}" data-done="{{ $rec->isCompleted() ? 1 : 0 }}">
    <div class="rc-layout"><div class="rc-main">
        <style>
            .rc-card { border-radius: 12px; padding: .8rem 1.1rem; background: #f9fafb; border: 1px solid #eaecf0; height: 100%; }
            .rc-card .l { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #667085; }
            .rc-card .v { font-size: 1.35rem; font-weight: 700; font-variant-numeric: tabular-nums; }
            .rc-ok { background: #ecfdf3; border-color: #a6f4c5; } .rc-ok .v { color: #05603a; }
            .rc-bad { background: #fff7ed; border-color: #fed7aa; } .rc-bad .v { color: #9a3412; }
            #rc-lines tr { cursor: pointer; } #rc-lines tr.ticked { background: #f0f9ff; }
            .rc-in { color: #175cd3; } .rc-out { color: #b54708; }
        </style>

        <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap pb-3">
            <div>
                <div class="imp-crumb"><a href="/finance/reconciliation">{{ __('Finance') }}</a> &nbsp;/&nbsp; <a href="{{ route('finance.reconciliation') }}">{{ __('Bank Reconciliation') }}</a> &nbsp;/&nbsp; {{ $rec->account->code }}</div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <h1 class="imp-h1">{{ $rec->account->name }}
                        @if($rec->isCompleted()) <span class="badge text-bg-success fs-6 align-middle">{{ __('Reconciled') }}</span>
                        @else <span class="badge text-bg-warning fs-6 align-middle">{{ __('In progress') }}</span> @endif
                    </h1>
                    <a href="#" class="rc-how" data-bs-toggle="modal" data-bs-target="#rcVideo"><i class="bi bi-play-circle-fill me-1"></i>{{ __('How to reconcile') }}</a>
                </div>
            </div>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                @if(!$rec->isCompleted())
                    <button type="button" class="btn btn-outline-danger" id="rc-delete"><i class="bi bi-trash me-1"></i>{{ __('Delete') }}</button>
                    <button type="button" class="btn btn-success" id="rc-finish"><i class="bi bi-check2-circle me-1"></i>{{ __('Finish reconciliation') }}</button>
                @elseif($canUndo)
                    <button type="button" class="btn btn-outline-danger" id="rc-delete"><i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('Undo reconciliation') }}</button>
                @endif
            </div>
        </div>

        {{-- statement date and closing balance --}}
        @if(!$rec->isCompleted())
            <div class="row g-2 align-items-end mb-3" style="max-width: 640px">
                <div class="col-sm-4">
                    <label class="form-label small fw-bold text-uppercase text-muted mb-1">{{ __('Statement date') }}</label>
                    <input type="text" id="rc-date" class="form-control datepicker" data-min-date="01-01-2000" data-max-date="31-12-2035" value="{{ $rec->statement_date->format('d-m-Y') }}" autocomplete="off">
                </div>
                <div class="col-sm-4">
                    <label class="form-label small fw-bold text-uppercase text-muted mb-1">{{ __('Closing balance on statement') }}</label>
                    <input type="text" id="rc-balance" class="form-control decimal" data-negative value="{{ $rec->statement_balance }}">
                </div>
                <div class="col-sm-4"><button type="button" class="btn btn-outline-secondary w-100" id="rc-update">{{ __('Update') }}</button></div>
            </div>
        @else
            <div class="text-muted small mb-3">{{ __('Statement of') }} <b>{{ $rec->statement_date->format('d-m-Y') }}</b> &middot; {{ __('finished on') }} {{ $rec->completed_at?->format('d-m-Y H:i') }}</div>
        @endif

        {{-- the four numbers --}}
        <div class="row g-3 mb-3">
            <div class="col-6 col-lg-3"><div class="rc-card"><div class="l">{{ __('Statement balance') }}</div><div class="v" id="s-statement">{{ number_format($summary['statement'], 2) }}</div></div></div>
            <div class="col-6 col-lg-3"><div class="rc-card"><div class="l">{{ __('Reconciled so far') }}</div><div class="v" id="s-cleared">{{ number_format($summary['cleared'], 2) }}</div>
                <div class="small text-muted"><span id="s-count">{{ $summary['ticked_count'] }}</span> {{ __('ticked') }} &middot; {{ __('earlier') }} {{ number_format($summary['opening'], 2) }}</div></div></div>
            <div class="col-6 col-lg-3"><div class="rc-card {{ abs($summary['difference']) < 0.005 ? 'rc-ok' : 'rc-bad' }}" id="s-diff-card"><div class="l">{{ __('Difference') }}</div><div class="v" id="s-diff">{{ number_format($summary['difference'], 2) }}</div>
                <div class="small text-muted" id="s-diff-note">{{ abs($summary['difference']) < 0.005 ? __('Balanced. You can finish.') : __('Tick entries until this is 0.00') }}</div></div></div>
            <div class="col-6 col-lg-3"><div class="rc-card"><div class="l">{{ __('Books balance') }}</div><div class="v" id="s-book">{{ number_format($summary['book'], 2) }}</div><div class="small text-muted">{{ __('up to the statement date') }}</div></div></div>
        </div>

        {{-- entries the bank shows but the books do not --}}
        @if(!$rec->isCompleted())
            <div class="imp-card mb-3" id="rc-import">
                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <div>
                        <b><i class="bi bi-file-earmark-arrow-up me-1"></i>{{ __('Import your bank statement') }}</b>
                        <div class="imp-help">{{ __('Upload the CSV or Excel file from your bank. Entries that match are ticked for you; anything the bank has and your books do not is listed below.') }}</div>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <input type="file" id="rc-file" class="d-none" accept=".csv,.txt,.xlsx,.xls">
                        <button type="button" class="btn btn-primary" id="rc-import-btn"><i class="bi bi-upload me-1"></i>{{ __('Import statement (CSV / Excel)') }}</button>
                    </div>
                </div>
                <div class="imp-help mt-2">{{ __('The first row must name the columns: Date, Description, and Debit / Credit (or Withdrawal / Deposit, or one Amount column). Nothing is changed until you tick or post.') }}</div>
                <div id="rc-import-result" class="mt-3 d-none"></div>
            </div>

            <div class="mb-3">
                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#rc-add"><i class="bi bi-plus-circle me-1"></i>{{ __('Add an entry from the statement') }}</button>
                <span class="text-muted small ms-2">{{ __('For bank charges, interest or anything the bank shows that is not in your books yet.') }}</span>
                <div class="collapse mt-2" id="rc-add">
                    <div class="card card-body border shadow-sm" style="max-width: 900px">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">{{ __('Type') }}</label>
                                <select id="ae-kind" class="tom-select" data-placeholder="{{ __('Type') }}">
                                    <option value="out">{{ __('Money out (charge, fee)') }}</option>
                                    <option value="in">{{ __('Money in (interest, other)') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">{{ __('Date') }}</label>
                                <input type="text" id="ae-date" class="form-control datepicker" data-min-date="01-01-2000" data-max-date="31-12-2035" value="{{ $rec->statement_date->format('d-m-Y') }}" autocomplete="off">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">{{ __('Amount') }}</label>
                                <input type="text" id="ae-amount" class="form-control decimal" placeholder="0.00">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">{{ __('Other side (account)') }}</label>
                                <select id="ae-account" class="tom-select" data-live-search="true" data-placeholder="{{ __('Search an account...') }}">
                                    @foreach($counterAccounts as $a)
                                        <option value="{{ $a->id }}" data-code="{{ $a->code }}">{{ $a->code }} {{ $a->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-9">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">{{ __('Description') }}</label>
                                <input type="text" id="ae-desc" class="form-control" maxlength="150" placeholder="{{ __('e.g. Monthly bank charges') }}">
                            </div>
                            <div class="col-md-3"><button type="button" class="btn btn-primary w-100" id="ae-save">{{ __('Post entry') }}</button></div>
                        </div>
                        <div class="text-muted small mt-2" id="ae-help"></div>
                    </div>
                </div>
            </div>
        @endif

        <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="fw-bold mb-0">{{ $rec->isCompleted() ? __('Entries on this statement') : __('Entries in the books') }} <span class="text-muted fw-normal small">({{ $lines->count() }})</span></h6>
            @if(!$rec->isCompleted())
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary" id="rc-all">{{ __('Tick all') }}</button>
                    <button type="button" class="btn btn-outline-secondary" id="rc-none">{{ __('Untick all') }}</button>
                </div>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle table-bordered">
                <thead class="table-light">
                <tr>
                    @if(!$rec->isCompleted())<th style="width:44px"></th>@endif
                    <th>{{ __('Date') }}</th><th>{{ __('Voucher') }}</th><th>{{ __('Description') }}</th>
                    <th class="text-end">{{ __('Money in') }}</th><th class="text-end">{{ __('Money out') }}</th>
                </tr>
                </thead>
                <tbody id="rc-lines">
                @forelse($lines as $l)
                    <tr class="{{ $l->ticked ? 'ticked' : '' }}" data-line="{{ $l->id }}">
                        @if(!$rec->isCompleted())<td class="text-center"><input type="checkbox" class="form-check-input rc-tick" @checked($l->ticked)></td>@endif
                        <td class="text-nowrap">{{ \Carbon\Carbon::parse($l->transaction_date)->format('d-m-Y') }}</td>
                        <td class="text-nowrap"><b>{{ $l->voucher_no }}</b> <span class="badge text-bg-light border">{{ $l->voucher_type }}</span></td>
                        <td><div>{{ $l->description }}</div><div class="text-muted small">{{ $l->reference_no }}</div></td>
                        <td class="text-end rc-in">{{ $l->base_debit > 0 ? number_format($l->base_debit, 2) : '' }}</td>
                        <td class="text-end rc-out">{{ $l->base_credit > 0 ? number_format($l->base_credit, 2) : '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">{{ __('No entries to reconcile for this account up to the statement date.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @include('modules.finance.reconciliation._guide')
    </div>
    </main>
    </div>

    <script>
        window.addEventListener('load', function () {
            if (typeof datepicker === 'function') datepicker();
            if (typeof initTomSelectForm === 'function') initTomSelectForm($('#recon'));
        });
        (function () {
            const root = document.getElementById('recon');
            const id = root.dataset.id, base = '/finance/reconciliation/' + id;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
            const money = n => (n < 0 ? '-' : '') + Math.abs(n).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            const $ = s => document.querySelector(s);

            function post(url, data, method) {
                return fetch(url, {method: method || 'POST', headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}, body: JSON.stringify(data || {})})
                    .then(r => r.json().then(j => ({ok: r.ok, j})));
            }
            function fail(j) { (window.toastr ? toastr.error(j.message || '{{ __('Something went wrong.') }}') : alert(j.message)); }
            function show(s) {
                $('#s-statement').textContent = money(s.statement); $('#s-cleared').textContent = money(s.cleared); $('#s-book').textContent = money(s.book);
                $('#s-count').textContent = s.ticked_count; $('#s-diff').textContent = money(s.difference);
                const ok = Math.abs(s.difference) < 0.005;
                $('#s-diff-card').className = 'rc-card ' + (ok ? 'rc-ok' : 'rc-bad');
                $('#s-diff-note').textContent = ok ? '{{ __('Balanced. You can finish.') }}' : '{{ __('Tick entries until this is 0.00') }}';
                const f = $('#rc-finish'); if (f) f.disabled = !ok;
            }
            show(@json($summary));

            // tick / untick (a click anywhere on the row)
            document.querySelectorAll('#rc-lines tr[data-line]').forEach(tr => {
                const box = tr.querySelector('.rc-tick'); if (!box) return;
                const toggle = on => post(base + '/status/tick', {line: tr.dataset.line, on: on}).then(({ok, j}) => {
                    if (!ok) { box.checked = !on; return fail(j); }
                    tr.classList.toggle('ticked', on); show(j.summary);
                });
                box.addEventListener('change', () => toggle(box.checked));
                tr.addEventListener('click', e => { if (e.target !== box) { box.checked = !box.checked; box.dispatchEvent(new Event('change')); } });
            });
            [['#rc-all', true], ['#rc-none', false]].forEach(([sel, on]) => $(sel)?.addEventListener('click', () => post(base + '/status/tick', {all: on}).then(({ok, j}) => {
                if (!ok) return fail(j);
                document.querySelectorAll('#rc-lines tr[data-line]').forEach(tr => { const b = tr.querySelector('.rc-tick'); if (b) { b.checked = on; tr.classList.toggle('ticked', on); } });
                show(j.summary);
            })));

            $('#rc-update')?.addEventListener('click', () => post(base + '/status/update', {statement_date: $('#rc-date').value, statement_balance: $('#rc-balance').value})
                .then(({ok, j}) => ok ? location.reload() : fail(j)));
            $('#rc-finish')?.addEventListener('click', () => post(base + '/status/complete').then(({ok, j}) => ok ? (window.toastr && toastr.success('{{ __('Reconciled.') }}'), location.reload()) : fail(j)));
            $('#rc-delete')?.addEventListener('click', () => {
                if (!confirm(root.dataset.done === '1' ? '{{ __('Undo this reconciliation? Its entries become open again.') }}' : '{{ __('Delete this unfinished reconciliation?') }}')) return;
                post(base, {}, 'DELETE').then(({ok, j}) => ok ? (location.href = j.redirect) : fail(j));
            });

            // import a bank statement file
            const fileEl = $('#rc-file'), resEl = $('#rc-import-result');
            const esc = t => String(t ?? '').replace(/[&<>"]/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;'}[c]));
            $('#rc-import-btn')?.addEventListener('click', () => fileEl.click());
            fileEl?.addEventListener('change', () => {
                if (!fileEl.files.length) return;
                const fd = new FormData(); fd.append('file', fileEl.files[0]);
                resEl.classList.remove('d-none'); resEl.innerHTML = '<div class="text-muted"><span class="spinner-border spinner-border-sm me-2"></span>{{ __('Reading the statement...') }}</div>';
                fetch(base + '/status/import', {method: 'POST', headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}, body: fd})
                    .then(r => r.json().then(j => ({ok: r.ok, j}))).then(({ok, j}) => {
                        fileEl.value = '';
                        if (!ok) { resEl.innerHTML = '<div class="alert alert-danger mb-0">' + esc(j.message || (j.errors ? Object.values(j.errors)[0][0] : '{{ __('The file could not be read.') }}')) + '</div>'; return; }
                        show(j.summary);
                        const un = j.unmatched || [];
                        let h = '<div class="alert alert-success py-2 mb-2"><b>' + j.matched + '</b> {{ __('of') }} ' + j.rows + ' {{ __('statement lines matched your books and were ticked.') }}' + (un.length ? ' <b>' + un.length + '</b> {{ __('are not in your books.') }}' : '') + '</div>';
                        if (un.length) {
                            h += '<div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead class="table-light"><tr><th>{{ __('Date') }}</th><th>{{ __('On the statement') }}</th><th class="text-end">{{ __('Money in') }}</th><th class="text-end">{{ __('Money out') }}</th><th style="min-width:230px">{{ __('Post as entry (other side)') }}</th><th></th></tr></thead><tbody>';
                            un.forEach((r, i) => { h += '<tr data-i="' + i + '"><td class="text-nowrap">' + esc(r.date.split('-').reverse().join('-')) + '</td><td>' + esc(r.description) + '</td><td class="text-end rc-in">' + (r.in ? money(r.in) : '') + '</td><td class="text-end rc-out">' + (r.out ? money(r.out) : '') + '</td><td><select class="un-acc"></select></td><td class="text-end"><button type="button" class="btn btn-sm btn-outline-primary un-post">{{ __('Post') }}</button></td></tr>'; });
                            h += '</tbody></table></div><div class="imp-help mt-2">{{ __('Posting creates a balanced entry in the books (money out: debit the account chosen, credit the bank; money in: the opposite) and ticks it.') }}</div>';
                        }
                        resEl.innerHTML = h;
                        resEl.querySelectorAll('tr[data-i]').forEach(tr => {
                            const r = un[tr.dataset.i], sel = tr.querySelector('.un-acc');
                            acc.querySelectorAll('option').forEach(o => sel.appendChild(o.cloneNode(true)));
                            const def = [...sel.options].find(o => o.dataset.code === (r.kind === 'out' ? '5260' : '4160')); if (def) sel.value = def.value;
                            if (window.TomSelect) new TomSelect(sel, {maxOptions: null, controlInput: null, plugins: ['dropdown_input']});
                            tr.querySelector('.un-post').addEventListener('click', e => {
                                e.target.disabled = true;
                                post(base + '/create', {kind: r.kind, date: r.date, amount: r.amount, account_id: sel.value, description: r.description || ''})
                                    .then(({ok, j}) => { if (!ok) { e.target.disabled = false; return fail(j); } show(j.summary); tr.innerHTML = '<td colspan="6" class="text-success"><i class="bi bi-check2-circle me-1"></i>' + esc(r.description || '') + ' {{ __('posted and ticked') }}</td>'; });
                            });
                        });
                        // show the matched entries as ticked, without reloading (the list of unmatched rows stays)
                        (j.ticked || []).forEach(lineId => { const tr = document.querySelector('#rc-lines tr[data-line="' + lineId + '"]'); if (tr) { tr.classList.add('ticked'); const b = tr.querySelector('.rc-tick'); if (b) b.checked = true; } });
                    });
            });

            // entry from the statement: sensible account for each kind
            const kind = $('#ae-kind'), acc = $('#ae-account');
            if (kind && acc) {
                const pick = () => { const code = kind.value === 'out' ? '5260' : '4160'; const o = [...acc.options].find(x => x.dataset.code === code); if (o) { if (acc.tomselect) acc.tomselect.setValue(o.value); else acc.value = o.value; }
                    $('#ae-help').textContent = kind.value === 'out' ? '{{ __('Posts: debit the account chosen, credit the bank account.') }}' : '{{ __('Posts: debit the bank account, credit the account chosen.') }}'; };
                kind.addEventListener('change', pick); pick();
                $('#ae-save').addEventListener('click', () => post(base + '/create', {kind: kind.value, date: $('#ae-date').value, amount: $('#ae-amount').value, account_id: acc.value, description: $('#ae-desc').value})
                    .then(({ok, j}) => ok ? (window.toastr && toastr.success('{{ __('Entry posted and ticked.') }}'), location.reload()) : fail(j)));
            }
        })();
    </script>
</x-app-layout>
