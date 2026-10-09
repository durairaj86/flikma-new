@section('page-title', __('Customers'))
@section('hide-topbar', true)
<x-app-layout>
    <main class="gmail-content px-3 pt-3 pb-3 cust-split">
        <div class="d-flex align-items-start gap-3">
            {{-- Left: customer list --}}
            <aside class="cust-split-list bg-white rounded shadow-sm">
                <div class="d-flex justify-content-between align-items-center p-3 pb-2">
                    <h5 class="fw-bold mb-0">{{ __('All Customers') }}</h5>
                    <a href="{{ url('/customers') }}" class="btn btn-sm btn-light border" title="{{ __('Back to list') }}" aria-label="{{ __('Back to list') }}"><i class="bi bi-list"></i></a>
                </div>
                <div class="px-3 pb-3 position-relative">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted" style="margin-top:-8px"></i>
                    <input type="text" id="splitSearch" class="form-control ps-5" placeholder="{{ __('Search customers...') }}" autocomplete="off">
                </div>
                <div class="cust-split-items" id="splitItems">
                    @include('modules.customer.partials.split-items', ['activeId' => $customer->id])
                </div>
            </aside>

            {{-- Right: detail --}}
            <section class="cust-split-detail">
                <div class="bg-white rounded shadow-sm px-4 pt-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4 class="fw-bold mb-1">{{ $customer->name_en }}</h4>
                            <div class="text-muted small">
                                <i class="bi bi-envelope me-1"></i>{{ $customer->email }}
                                @if($customer->phone) <span class="mx-1">|</span><i class="bi bi-telephone me-1"></i>{{ $customer->phone }} @endif
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-white border shadow-sm dropdown-toggle" data-bs-toggle="dropdown">{{ __('More') }}</button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ url('/invoice/customer?customer=' . $customer->id) }}"><i class="bi bi-plus-circle me-2"></i>{{ __('New Invoice') }}</a></li>
                                <li><a class="dropdown-item" href="{{ url('/customer/statement?customer=' . $customer->id) }}"><i class="bi bi-file-earmark-text me-2"></i>{{ __('Full Statement') }}</a></li>
                            </ul>
                        </div>
                    </div>
                    <ul class="nav cust-tabs mt-3" id="custTabs">
                        @foreach(['general' => 'General', 'transactions' => 'Transactions', 'invoices' => 'Invoices', 'statement' => 'Statement', 'aging' => 'Aging'] as $key => $label)
                            <li class="nav-item"><button type="button" class="nav-link {{ $loop->first ? 'active' : '' }}" data-tab="{{ $key }}">{{ __($label) }}</button></li>
                        @endforeach
                    </ul>
                </div>
                <div id="custTabBody" class="mt-3"></div>
            </section>
        </div>
    </main>

    <style>
        .cust-split-list { width: 340px; flex: 0 0 340px; align-self: flex-start; position: sticky; top: 12px; max-height: calc(100vh - 24px); display: flex; flex-direction: column; }
        .cust-split-detail { flex: 1 1 0; min-width: 0; }
        .cust-split-items { overflow-y: auto; border-top: 1px solid #eef0f3; }
        .cust-split-item { display: block; padding: .8rem 1rem; color: #212529; border-bottom: 1px solid #eef0f3; }
        .cust-split-item:hover { background: #f8f9fa; }
        .cust-split-item.active { background: #e0e7ff; box-shadow: inset 0 -3px 0 var(--bs-primary); }
        .cust-split-item.active .fw-semibold { color: var(--bs-primary); }
        .cust-tabs .nav-link { color: #6c757d; font-weight: 600; padding: .6rem 1rem; border: 0; border-bottom: 3px solid transparent; border-radius: 0; background: none; }
        .cust-tabs .nav-link.active { color: var(--bs-primary); border-bottom-color: var(--bs-primary); }
        .cust-card { overflow-x: auto; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.08); margin-bottom: 1rem; }
        .cust-card-h { padding: .9rem 1.1rem; font-weight: 700; border-bottom: 1px solid #eef0f3; }
        .cust-card-b { padding: 1rem 1.1rem; }
        .cust-dl { display: grid; grid-template-columns: 140px 1fr; row-gap: .6rem; margin: 0; }
        .cust-dl dt { font-weight: 400; color: #6c757d; } .cust-dl dd { margin: 0; font-weight: 600; }
        .cust-tbl th { font-size: .75rem; text-transform: uppercase; letter-spacing: .03em; background: #f8f9fa; }
        @media (max-width: 991px) { .cust-split-list { display: none; } }
    </style>
    <script>
        (function () {
            const id = {{ $customer->id }};
            const body = document.getElementById('custTabBody');
            function load(tab) {
                document.querySelectorAll('#custTabs .nav-link').forEach(b => b.classList.toggle('active', b.dataset.tab === tab));
                body.innerHTML = '<div class="text-muted p-3">{{ __('Loading...') }}</div>';
                fetch('/customers/' + id + '/tab/' + tab, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
                    .then(r => r.ok ? r.text() : Promise.reject())
                    .then(h => { body.innerHTML = h; })
                    .catch(() => { body.innerHTML = '<div class="alert alert-danger">{{ __('Could not load this tab.') }}</div>'; });
                try { history.replaceState(null, '', location.pathname + '#' + tab); } catch (e) {}
            }
            document.querySelectorAll('#custTabs .nav-link').forEach(b => b.addEventListener('click', () => load(b.dataset.tab)));
            let st = null, ctl = null;
            const itemsEl = document.getElementById('splitItems');
            document.getElementById('splitSearch').addEventListener('input', function () {
                const q = this.value.trim();
                clearTimeout(st);
                st = setTimeout(function () {
                    if (ctl) ctl.abort();
                    ctl = new AbortController();
                    itemsEl.style.opacity = .5;
                    fetch('/customers/search?q=' + encodeURIComponent(q) + '&active=' + id, {headers: {'X-Requested-With': 'XMLHttpRequest'}, signal: ctl.signal})
                        .then(r => r.ok ? r.text() : Promise.reject())
                        .then(h => { itemsEl.innerHTML = h; itemsEl.style.opacity = 1; })
                        .catch(e => { if (!e || e.name !== 'AbortError') itemsEl.style.opacity = 1; });
                }, 600);
            });
            const start = location.hash.replace('#', '');
            load(['general', 'transactions', 'invoices', 'statement', 'aging'].includes(start) ? start : 'general');
            const active = document.querySelector('.cust-split-item.active');
            if (active) active.scrollIntoView({block: 'center'});
        })();
    </script>
</x-app-layout>
