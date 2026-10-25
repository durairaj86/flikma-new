@section('page-title', __('Finance Reports'))
@section('page-subtitle', __('Statements, ledgers, VAT and sales reports in one place'))
@php
    /* Every finance report the sidebar used to list, grouped. 'en' keeps the English name and description searchable in Arabic too. */
    $groups = [
        ['title' => 'Financial Statements', 'icon' => 'bi-journal-text', 'color' => '#4f46e5', 'bg' => '#eef2ff', 'reports' => [
            ['/reports/trial-balance', 'Trial Balance', 'Debit and credit balance of every account, to check that the books balance.', 'bi-list-columns-reverse'],
            ['/reports/balance-sheet', 'Balance Sheet', 'Assets, liabilities and equity on a chosen date.', 'bi-layout-split'],
            ['/reports/profit-and-loss', 'Profit & Loss Report', 'Income and expenses for a period, and the net profit or loss.', 'bi-graph-up-arrow'],
        ]],
        ['title' => 'Ledgers', 'icon' => 'bi-book', 'color' => '#0284c7', 'bg' => '#e0f2fe', 'reports' => [
            ['/reports/general-ledger', 'Customer Ledger', 'Every transaction of a customer with its running balance.', 'bi-person-lines-fill'],
            ['/reports/supplier-ledger', 'Supplier Ledger', 'Every transaction of a supplier with its running balance.', 'bi-truck'],
        ]],
        ['title' => 'VAT', 'icon' => 'bi-percent', 'color' => '#d97706', 'bg' => '#fef3c7', 'reports' => [
            ['/reports/tax-summary', 'Tax Summary', 'Your VAT return: VAT on sales and purchases and the net VAT due.', 'bi-receipt-cutoff'],
            ['/reports/input-tax', 'Input Tax', 'VAT paid on purchases that you can claim back.', 'bi-box-arrow-in-down'],
            ['/reports/output-tax', 'Output Tax', 'VAT charged on sales that you have to pay.', 'bi-box-arrow-up'],
        ]],
        ['title' => 'Sales & Commission', 'icon' => 'bi-trophy', 'color' => '#16a34a', 'bg' => '#dcfce7', 'reports' => [
            ['/reports/sales-commission', 'Sales Commission', 'Incentive on gross profit, paid by when the customer paid.', 'bi-calculator'],
            ['/reports/customer-wise-sales', 'Customer Wise Sales', 'Invoiced, cost and gross profit per customer.', 'bi-people'],
            ['/reports/invoice-wise-sales', 'Invoice Wise Sales', 'Invoiced, cost and gross profit per invoice.', 'bi-file-earmark-text'],
        ]],
    ];
    $total = collect($groups)->sum(fn ($g) => count($g['reports']));
@endphp
<x-app-layout>
    <div class="fr-page">
        <style>
            .fr-page { padding: 1.25rem 1.5rem 3rem; }
            .fr-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; }
            .fr-head .sub { color: #6b7280; font-size: .85rem; }
            .fr-search { position: relative; width: 340px; max-width: 100%; }
            .fr-search i.bi-search { position: absolute; top: 50%; inset-inline-start: .85rem; transform: translateY(-50%); color: #9ca3af; }
            .fr-search input { padding-inline-start: 2.4rem; padding-inline-end: 2.6rem; height: 42px; border-radius: 10px; border-color: #e5e7eb; }
            .fr-search kbd { position: absolute; top: 50%; inset-inline-end: .7rem; transform: translateY(-50%); background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; font-size: .7rem; padding: .05rem .4rem; }
            .fr-group { margin-bottom: 1.75rem; }
            .fr-group-title { display: flex; align-items: center; gap: .5rem; font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; margin-bottom: .75rem; }
            .fr-group-title .count { color: #9ca3af; font-weight: 500; }
            .fr-card { display: flex; gap: .9rem; align-items: flex-start; height: 100%; background: #fff; border: 1px solid #e8ecf1; border-radius: 12px; padding: 1rem 1.1rem; color: inherit; text-decoration: none; transition: border-color .15s, box-shadow .15s, transform .15s; }
            .fr-card:hover, .fr-card:focus { border-color: var(--c); box-shadow: 0 4px 14px rgba(17, 24, 39, .06); transform: translateY(-1px); outline: none; color: inherit; }
            .fr-card:focus-visible { box-shadow: 0 0 0 3px var(--bgc); }
            .fr-icon { flex: 0 0 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; background: var(--bgc); color: var(--c); }
            .fr-name { font-weight: 600; color: #111827; line-height: 1.3; }
            .fr-desc { color: #6b7280; font-size: .82rem; margin-top: .2rem; line-height: 1.45; }
            .fr-go { margin-inline-start: auto; align-self: center; color: #c4c9d2; transition: color .15s; }
            [dir="rtl"] .fr-go { transform: scaleX(-1); }
            .fr-card:hover .fr-go, .fr-card:focus .fr-go { color: var(--c); }
            .fr-card mark { background: #fef08a; padding: 0 .1rem; border-radius: 3px; }
            .fr-empty { text-align: center; padding: 3rem 1rem; color: #6b7280; }
            .fr-empty i { font-size: 2rem; color: #c4c9d2; }
        </style>

        <div class="fr-head">
            <div class="fr-search" role="search">
                <i class="bi bi-search"></i>
                <input type="text" id="frSearch" class="form-control" placeholder="{{ __('Search reports...') }}" aria-label="{{ __('Search reports') }}" autocomplete="off" autofocus>
                <kbd>/</kbd>
            </div>
            <div class="sub"><span id="frCount">{{ $total }}</span> {{ __('reports') }}</div>
        </div>

        @foreach($groups as $group)
            <section class="fr-group" data-fr-group>
                <div class="fr-group-title"><i class="bi {{ $group['icon'] }}" style="color: {{ $group['color'] }}"></i>{{ __($group['title']) }}<span class="count" data-fr-group-count>{{ count($group['reports']) }}</span></div>
                <div class="row g-3">
                    @foreach($group['reports'] as [$url, $name, $desc, $icon])
                        <div class="col-12 col-md-6 col-xl-4" data-fr-item data-search="{{ mb_strtolower(__($name) . ' ' . __($desc) . ' ' . $name . ' ' . $desc . ' ' . __($group['title'])) }}">
                            <a href="{{ url($url) }}" class="fr-card" style="--c: {{ $group['color'] }}; --bgc: {{ $group['bg'] }};">
                                <span class="fr-icon"><i class="bi {{ $icon }}"></i></span>
                                <span>
                                    <span class="fr-name d-block" data-fr-text>{{ __($name) }}</span>
                                    <span class="fr-desc d-block" data-fr-text>{{ __($desc) }}</span>
                                </span>
                                <i class="bi bi-chevron-right fr-go"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="fr-empty d-none" id="frEmpty">
            <i class="bi bi-search d-block mb-2"></i>
            <div class="fw-semibold text-dark">{{ __('No report matches your search') }}</div>
            <div class="small">{{ __('Try another word, for example VAT, ledger or profit.') }}</div>
        </div>
    </div>

    <script>
        (function () {
            const input = document.getElementById('frSearch');
            const items = [...document.querySelectorAll('[data-fr-item]')];
            const esc = s => s.replace(/[&<>"]/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;'}[c]));
            items.forEach(it => it.querySelectorAll('[data-fr-text]').forEach(t => t.dataset.plain = t.textContent));
            const visibleCards = () => items.filter(it => !it.classList.contains('d-none')).map(it => it.querySelector('.fr-card'));

            function filter() {
                const q = input.value.trim().toLowerCase();
                const words = q.split(/\s+/).filter(Boolean);
                let shown = 0;
                items.forEach(it => {
                    const hit = words.every(w => it.dataset.search.includes(w));
                    it.classList.toggle('d-none', !hit);
                    if (hit) shown++;
                    // highlight the typed words in the visible name and description
                    it.querySelectorAll('[data-fr-text]').forEach(t => {
                        let html = esc(t.dataset.plain);
                        words.forEach(w => { html = html.replace(new RegExp('(' + w.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi'), '<mark>$1</mark>'); });
                        t.innerHTML = html;
                    });
                });
                document.querySelectorAll('[data-fr-group]').forEach(g => {
                    const n = g.querySelectorAll('[data-fr-item]:not(.d-none)').length;
                    g.classList.toggle('d-none', n === 0);
                    g.querySelector('[data-fr-group-count]').textContent = n;
                });
                document.getElementById('frCount').textContent = shown;
                document.getElementById('frEmpty').classList.toggle('d-none', shown > 0);
            }

            input.addEventListener('input', filter);
            input.addEventListener('keydown', e => {
                const cards = visibleCards();
                if (e.key === 'Enter' && cards.length) { e.preventDefault(); location.href = cards[0].href; }
                if (e.key === 'ArrowDown' && cards.length) { e.preventDefault(); cards[0].focus(); }
                if (e.key === 'Escape' && input.value) { e.preventDefault(); input.value = ''; filter(); }
            });
            // arrows move between the cards; "/" jumps back to the search box
            document.addEventListener('keydown', e => {
                const inField = /INPUT|TEXTAREA|SELECT/.test(document.activeElement.tagName);
                if (e.key === '/' && !inField) { e.preventDefault(); input.focus(); input.select(); return; }
                const card = document.activeElement.closest && document.activeElement.closest('.fr-card');
                if (!card) return;
                const cards = visibleCards(), i = cards.indexOf(card);
                if (['ArrowDown', 'ArrowRight', 'ArrowUp', 'ArrowLeft'].includes(e.key)) {
                    e.preventDefault();
                    const rtl = document.documentElement.dir === 'rtl';
                    const next = e.key === 'ArrowDown' || e.key === (rtl ? 'ArrowLeft' : 'ArrowRight');
                    if (!next && i === 0) { input.focus(); return; }
                    cards[Math.max(0, Math.min(cards.length - 1, i + (next ? 1 : -1)))]?.focus();
                }
            });
        })();
    </script>
</x-app-layout>
