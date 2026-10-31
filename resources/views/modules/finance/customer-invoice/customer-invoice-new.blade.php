@section('page-title', __('New Customer Invoice'))
@section('hide-topbar', true)
<x-app-layout>
    <main class="gmail-content bg-white px-3 pb-5">
        <style>
            /* the page is white all the way down, not just as tall as its content */
            .content-scroll-area:has(#cinForm) { background: #fff; }
            .cin-grid { overflow-x: auto; }
            .cin-grid table { border-collapse: collapse; min-width: 1500px; width: 100%; font-size: .85rem; }
            .cin-grid th, .cin-grid td { border: 1px solid #dee2e6; padding: 4px 6px; vertical-align: middle; }
            .cin-grid thead th { text-align: center; font-weight: 700; background: #fff; }
            .cin-grid thead th.sale { background: #6366F1; color: #fff; }
            .cin-grid thead th.cost { background: #334155; color: #fff; }
            .cin-grid td.sale { background: #eef2ff; color: #312e81; }
            .cin-grid td.cost { background: #f1f5f9; color: #1e293b; }
            .cin-grid td.sale input, .cin-grid td.cost input, .cin-grid td.sale select, .cin-grid td.cost select { background: #fff; color: #212529; border-color: #cbd5e1; }
            .cin-grid td.sale, .cin-grid td.cost { text-align: right; }
            .cin-grid .tax-line { display: flex; justify-content: space-between; gap: 8px; margin-top: 2px; font-size: .8rem; }
            .cin-grid .tax-line .drcr { font-weight: 600; }
            .cin-grid .calc { display: flex; align-items: center; gap: 2px; justify-content: center; font-size: .8rem; }
            .cin-grid .calc input { width: 62px; padding: 2px 4px; font-size: .8rem; text-align: right; }
            .cin-grid .calc input.price { width: 84px; }
            .cin-grid .ico { cursor: pointer; font-size: 1.15rem; background: none; border: 0; padding: 0 4px; color: inherit; }
            .cin-grid .ico.rm { color: #dc3545; }
            .cin-grid td.num-cell { height: 1px; padding-top: 8px; padding-bottom: 8px; }
            .cin-gp-pos { color: #008000; font-weight: 700; }
            .cin-gp-neg { color: #e00000; font-weight: 700; }
            .cin-grid .ts-wrapper { min-width: 110px; }
            .cin-grid .ts-control { font-size: .8rem; padding: 3px 8px; min-height: 0; background: #fff; }
            .ts-dropdown { z-index: 2000; }
            .cin-cur { flex-wrap: nowrap; }
            .cin-cur .ts-wrapper { flex: 0 0 110px; width: 110px !important; }
            .cin-cur .ts-wrapper .ts-control { border-top-right-radius: 0; border-bottom-right-radius: 0; width: 100% !important; }
            .cin-cur input { margin-left: -1px; border-top-left-radius: 0; border-bottom-left-radius: 0; }
            .cin-grid tr.cost-locked td.cost input { background: #e2e8f0; cursor: not-allowed; }
            .cin-totals th { text-align: center; background: #fff; }
            .cin-totals td { text-align: center; font-weight: 600; }
            .cin-note { text-align: center; font-weight: 600; padding: 10px 0; }
        </style>

        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-3 pb-2">
            <h4 class="fw-bold text-dark mb-0">{{ __('New Customer Invoice') }} <span class="badge bg-secondary-subtle text-secondary-emphasis fs-6 ms-1">{{ __('Draft') }}</span></h4>
            <div class="d-flex gap-2">
                <a href="{{ url('/invoice/customer') }}" class="btn btn-outline-secondary rounded-pill px-4">{{ __('Cancel') }}</a>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="cinSave">{{ __('Save Invoice') }}</button>
            </div>
        </div>
        <div id="cinAlert" class="alert alert-danger d-none py-2"></div>

        <form id="cinForm" novalidate onsubmit="return false">
            @csrf
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">{{ __('Job') }} <sup class="text-danger">*</sup></label>
                    <x-common.jobs :jobs="$jobs" :value="$selectedJob" id="cinJob" :required="true" :extra="fn ($job) => ['data-currency' => $job->customer?->currency]"/>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('Customer') }}</label>
                    <input type="text" id="cinCustomerName" class="form-control" readonly>
                    <input type="hidden" name="customer" id="cinCustomer">
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('Invoice Date') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="invoice_date" id="cinDate" class="form-control datepicker" value="{{ now()->format('d-m-Y') }}" autocomplete="off" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('Due Date') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="due_date" id="cinDue" class="form-control datepicker" value="{{ now()->addDays(30)->format('d-m-Y') }}" autocomplete="off" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('Currency') }} / {{ __('Ex. Rate') }}</label>
                    <div class="input-group cin-cur">
                        <select name="currency" id="cinCurrency" class="tom-select" data-live-search="true" data-max-width="110">
                            @foreach($currencies as $code => $name)
                                <option value="{{ $code }}" @selected($code == 'SAR')>{{ $code }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="currency_rate" id="cinRate" class="form-control text-end" value="1">
                    </div>
                </div>
                <div class="col-md-4 d-none" id="cinQuoteWrap">
                    <div class="d-flex justify-content-between align-items-end">
                        <label class="form-label">{{ __('Pick up from this job') }}{{-- <small class="text-muted fw-normal">({{ __('quotation or supplier invoice') }})</small>--}}</label>
                        <small class="mb-2 fw-semibold text-nowrap" id="cinPending"></small>
                    </div>
                    <select id="cinPick" class="tom-select" data-live-search="true" data-placeholder="{{ __('Add lines from a quotation (sale) or a supplier invoice (cost)') }}"></select>
                </div>
            </div>

            <div class="cin-grid">
                <table id="cinTable">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th style="min-width:200px">{{ __('Description') }}</th>
                        <th>{{ __('Unit') }}</th>
                        <th class="cost" style="min-width:210px">{{ __('Cost') }}</th>
                        <th class="cost">{{ __('Amount') }}<br>(<span class="cur">SAR</span>)</th>
                        <th class="cost" style="min-width:170px">{{ __('Tax') }}<br><small class="fw-normal">{{ __('Amount') }} (<span class="cur">SAR</span>) · {{ __('Dr / Cr') }}</small></th>
                        <th class="sale" style="min-width:210px">{{ __('Sale') }}</th>
                        <th class="sale">{{ __('Amount') }}<br>(<span class="cur">SAR</span>)</th>
                        <th class="sale" style="min-width:170px">{{ __('Tax') }}<br><small class="fw-normal">{{ __('Amount') }} (<span class="cur">SAR</span>) · {{ __('Dr / Cr') }}</small></th>
                        <th>{{ __('GP') }}</th>
                    </tr>
                    </thead>
                    <tbody id="cinBody"></tbody>
                </table>
            </div>
            <div class="mt-2 d-flex gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm" id="cinAddRow"><i class="bi bi-plus-circle me-1"></i>{{ __('Add Line') }}</button>
            </div>

            <div class="cin-note mt-3">{{ __('Cost') }} - ({{ __('Qty') }} x {{ __('Amount Per Unit') }} x {{ __('Ex. Rate') }}) | {{ __('Sale') }} - ({{ __('Qty') }} x {{ __('Amount Per Unit') }} x {{ __('Ex. Rate') }})</div>

            <div class="cin-grid">
                <table class="cin-totals" style="min-width:1100px">
                    <thead>
                    <tr>
                        <th>{{ __('Total Cost') }} (<span class="cur">SAR</span>)</th>
                        <th>{{ __('Total Sale') }} (<span class="cur">SAR</span>)</th>
                        <th>{{ __('Gross Profit') }} (<span class="cur">SAR</span>)</th>
                        <th>{{ __('Cost GP%') }}</th>
                        <th>{{ __('Sale GP%') }}</th>
                        <th>{{ __('Cost Tax Amount') }}</th>
                        <th>{{ __('Sale Tax Amount') }}</th>
                        <th>{{ __('Cost With Tax') }}</th>
                        <th>{{ __('Sale With Tax') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td id="tCost">0.00</td><td id="tSale">0.00</td><td id="tGp">0.00</td><td id="tCostGp">0.00%</td><td id="tSaleGp">0.00%</td>
                        <td id="tCostTax">0.00</td><td id="tSaleTax">0.00</td><td id="tCostWith">0.00</td><td id="tSaleWith">0.00</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="row mt-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">{{ __('Terms & Conditions') }}</label>
                    <textarea name="terms" class="form-control" rows="3" maxlength="1000" placeholder="{{ __('Any additional notes...') }}"></textarea>
                </div>
            </div>
        </form>

        <template id="cinRowTpl">
            <tr class="cin-row">
                <td class="num-cell"><div class="d-flex flex-column justify-content-between align-items-center h-100 gap-2"><span class="line-no"></span><button type="button" class="ico rm" title="{{ __('Remove') }}"><i class="bi bi-trash"></i></button></div></td>
                <td>
                    <div class="src-label small fw-semibold text-primary mb-1 d-none"></div><input type="hidden" class="src-type"><input type="hidden" class="src-id"><input type="hidden" class="src-sub">
                    <select class="tom-select desc" data-live-search="true" data-dropdown-parent="body" data-dropdown-width="320" data-placeholder="{{ __('Select') }}"><option value="">{{ __('Select') }}</option>
                        @foreach($descriptions as $d)<option value="{{ $d->id }}" data-acc="{{ $d->sale_account_id }}">{{ $d->description }}</option>@endforeach
                    </select>
                    <input type="text" class="form-control form-control-sm mt-1 comment" maxlength="500" placeholder="{{ __('Comment') }}">
                </td>
                <td>
                    <select class="tom-select unit" data-live-search="true" data-dropdown-parent="body" data-dropdown-width="200" data-placeholder="{{ __('Select Unit') }}"><option value="">{{ __('Select Unit') }}</option>
                        @foreach($units as $u)<option value="{{ $u->id }}">{{ $u->unit_symbol ?: $u->unit_name }}</option>@endforeach
                    </select>
                </td>
                <td class="cost"><div class="calc"><input class="form-control c-qty" value="1"> x <input class="form-control price c-price" placeholder="0.00"> x <span class="rate-txt">1</span></div></td>
                <td class="cost c-amt">0.00</td>
                <td class="cost"><select class="tom-select c-tax" data-live-search="true" data-dropdown-parent="body" data-dropdown-width="300">@foreach($taxes as $t)<option value="{{ $t['code'] }}" data-p="{{ $t['percent'] }}" @selected($t['code'] === 'STANDARD')>{{ $t['label'] }}</option>@endforeach</select><div class="tax-line"><span class="c-taxamt">0.00</span> <span class="drcr">Dr(+)</span></div></td>
                <td class="sale"><div class="calc"><input class="form-control s-qty" value="1"> x <input class="form-control price s-price" placeholder="0.00"> x <span class="rate-txt">1</span></div></td>
                <td class="sale s-amt">0.00</td>
                <td class="sale"><select class="tom-select s-tax" data-live-search="true" data-dropdown-parent="body" data-dropdown-width="300">@foreach($taxes as $t)<option value="{{ $t['code'] }}" data-p="{{ $t['percent'] }}">{{ $t['label'] }}</option>@endforeach</select><div class="tax-line"><span class="s-taxamt">0.00</span> <span class="drcr">Cr(+)</span></div></td>
                <td class="text-end gp">0.00</td>
            </tr>
        </template>

        <script>
            window.addEventListener('load', function () {
                const $ = (s, r = document) => r.querySelector(s);
                const $$ = (s, r = document) => [...r.querySelectorAll(s)];
                const num = v => parseFloat(String(v ?? '').replace(/,/g, '')) || 0;
                const fmt = v => v.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                const body = $('#cinBody');
                const taxes = @json($taxes);
                const baseCur = (typeof baseCurrency !== 'undefined' && baseCurrency) ? baseCurrency : 'SAR';
                let quoteData = null;

                const rate = () => num($('#cinRate').value) || 1;
                // selects are tom-select boxes: keep the underlying <select> and the box in step
                const initTs = scope => { if (typeof initTomSelectForm === 'function') initTomSelectForm(window.jQuery(scope)); };
                const setSel = (el, v) => { if (el.tomselect) el.tomselect.setValue(String(v), true); else el.value = v; };

                function addRow(v = {}) {
                    const tr = $('#cinRowTpl').content.firstElementChild.cloneNode(true);
                    body.appendChild(tr);
                    initTs(tr);
                    if (v.description_id) setSel($('.desc', tr), v.description_id);
                    if (v.unit_id) setSel($('.unit', tr), v.unit_id);
                    if (v.comment) $('.comment', tr).value = v.comment;
                    if (v.s_price != null) $('.s-price', tr).value = v.s_price;
                    if (v.s_qty != null) $('.s-qty', tr).value = v.s_qty;
                    if (v.c_price != null) $('.c-price', tr).value = v.c_price;
                    if (v.c_qty != null) $('.c-qty', tr).value = v.c_qty;
                    if (v.tax) setSel($('.s-tax', tr), v.tax);
                    if (v.c_tax) setSel($('.c-tax', tr), v.c_tax);
                    // cost taken from a supplier invoice is fixed: only the sale side is edited
                    if (v.source_type === 'supplier_invoice') {
                        $$('.c-qty,.c-price', tr).forEach(i => { i.readOnly = true; i.tabIndex = -1; });
                        $('.c-tax', tr).tomselect?.lock();
                        tr.classList.add('cost-locked');
                    }
                    if (v.source_label) { const l = $('.src-label', tr); l.textContent = v.source_label; l.classList.remove('d-none'); }
                    $('.src-type', tr).value = v.source_type || '';
                    $('.src-id', tr).value = v.source_id || '';
                    $('.src-sub', tr).value = v.source_sub_id || '';
                    calc();
                    return tr;
                }

                function calc() {
                    const r = rate();
                    let t = {sale: 0, cost: 0, sTax: 0, cTax: 0};
                    $$('.cin-row', body).forEach((tr, i) => {
                        $('.line-no', tr).textContent = i + 1;
                        $$('.rate-txt', tr).forEach(e => e.textContent = r);
                        const sAmt = num($('.s-qty', tr).value) * num($('.s-price', tr).value) * r;
                        const cAmt = num($('.c-qty', tr).value) * num($('.c-price', tr).value) * r;
                        const sp = num($('.s-tax option:checked', tr)?.dataset.p), cp = num($('.c-tax option:checked', tr)?.dataset.p);
                        const sTax = Math.round(sAmt * sp) / 100, cTax = Math.round(cAmt * cp) / 100;
                        $('.s-amt', tr).textContent = fmt(sAmt); $('.s-taxamt', tr).textContent = fmt(sTax);
                        $('.c-amt', tr).textContent = fmt(cAmt); $('.c-taxamt', tr).textContent = fmt(cTax);
                        const gp = sAmt - cAmt, g = $('.gp', tr);
                        g.textContent = fmt(gp); g.className = 'text-end gp ' + (gp < 0 ? 'cin-gp-neg' : 'cin-gp-pos');
                        t.sale += sAmt; t.cost += cAmt; t.sTax += sTax; t.cTax += cTax;
                    });
                    const gp = t.sale - t.cost;
                    $('#tSale').textContent = fmt(t.sale); $('#tCost').textContent = fmt(t.cost); $('#tGp').textContent = fmt(gp);
                    $('#tSaleGp').textContent = (t.sale ? gp / t.sale * 100 : 0).toFixed(2) + '%';
                    $('#tCostGp').textContent = (t.cost ? gp / t.cost * 100 : 0).toFixed(2) + '%';
                    $('#tSaleTax').textContent = fmt(t.sTax); $('#tCostTax').textContent = fmt(t.cTax);
                    $('#tSaleWith').textContent = fmt(t.sale + t.sTax); $('#tCostWith').textContent = fmt(t.cost + t.cTax);
                    $$('.cur').forEach(e => e.textContent = $('#cinCurrency').value);
                }

                body.addEventListener('input', calc);
                body.addEventListener('change', e => {
                    if (e.target.classList.contains('desc')) {
                        // the description decides the default unit-less account; nothing else to prefill
                    }
                    calc();
                });
                body.addEventListener('click', e => {
                    const tr = e.target.closest('.cin-row');
                    if (!tr) return;
                    if (e.target.closest('.rm')) { if ($$('.cin-row', body).length > 1) { tr.remove(); calc(); } }
                });
                // Enter in a price/qty box moves to the next field; Enter on the last price adds a line
                body.addEventListener('keydown', e => {
                    if (e.key !== 'Enter' || !e.target.matches('.s-qty,.s-price,.c-qty,.c-price,.comment')) return;
                    e.preventDefault();
                    const f = $$('.s-qty,.s-price,.c-qty,.c-price,.ts-control input,.comment', $('#cinTable')).filter(x => !x.disabled && !x.readOnly && x.offsetParent);
                    const i = f.indexOf(e.target);
                    if (i === f.length - 1) { addRow(); $$('.desc', body).pop().tomselect?.focus(); } else f[i + 1].focus();
                });
                $('#cinAddRow').onclick = () => addRow();
                $('#cinRate').addEventListener('input', calc);

                $('#cinCurrency').addEventListener('change', function () {
                    $('#cinRate').value = '';
                    fetch('/currency/rate/' + this.value + '/' + baseCur, {headers: {'Accept': 'application/json'}})
                        .then(r => r.json()).then(j => { $('#cinRate').value = j.conversion_rate || 1; calc(); })
                        .catch(() => { $('#cinRate').value = 1; calc(); });
                });

                const onJob = function () {
                    const o = this.selectedOptions[0];
                    $('#cinCustomer').value = o?.dataset.customerId || '';
                    $('#cinCustomerName').value = o?.dataset.customerName || '';
                    quoteData = null;
                    const pick = $('#cinPick').tomselect;
                    pick.clear(true); pick.clearOptions(); pick.clearOptionGroups();
                    $('#cinQuoteWrap').classList.add('d-none');
                    if (!this.value) return;
                    fetch('/invoice/customer/job/' + this.value + '/costs', {headers: {'Accept': 'application/json'}})
                        .then(r => r.json()).then(j => {
                            quoteData = j;
                            const billed = x => x.billed_in.length ? ' · {{ __('billed in') }} ' + x.billed_in.join(', ') : '';
                            const customer = $('#cinCustomerName').value;
                            if (j.quotations.length) pick.addOptionGroup('q', {value: 'q', label: '{{ __('Quotation') }} ({{ __('fills the sale side') }})'});
                            if (j.bills.length) pick.addOptionGroup('b', {value: 'b', label: '{{ __('Supplier Invoice') }} ({{ __('fills the cost side') }})'});
                            j.quotations.forEach((q, i) => pick.addOption({value: 'q:' + i, optgroup: 'q', text: q.row_no, subtext: `${fmt(q.total)} ${q.currency ?? ''}${customer ? ' · ' + customer : ''}${billed(q)}`}));
                            j.bills.forEach((b, i) => pick.addOption({value: 'b:' + i, optgroup: 'b', text: b.row_no, subtext: `${fmt(b.grand_total)} ${b.currency ?? ''}${b.supplier ? ' · ' + b.supplier : ''}${billed(b)}`}));
                            pick.refreshOptions(false);
                            // how many are still to bill (not yet on a customer invoice)
                            const open = list => list.filter(x => !x.billed_in.length).length;
                            $('#cinPending').innerHTML = `<span class="text-primary">{{ __('Quotation') }}: ${open(j.quotations)}</span> <span class="text-muted mx-1">|</span> <span class="text-dark">{{ __('Supplier Invoice') }}: ${open(j.bills)}</span> <span class="text-warning-emphasis">{{ __('pending') }}</span>`;
                            if (j.quotations.length || j.bills.length) $('#cinQuoteWrap').classList.remove('d-none');
                        });
                };
                $('#cinJob').addEventListener('change', onJob);

                $('#cinPick').addEventListener('change', function () {
                    if (!this.value || !quoteData) return;
                    const [k, i] = this.value.split(':');
                    // drop the untouched starter row
                    $$('.cin-row', body).forEach(tr => { if (!num($('.s-price', tr).value) && !num($('.c-price', tr).value) && !$('.desc', tr).value) tr.remove(); });
                    const r = rate();
                    if (k === 'q') {
                        const q = quoteData.quotations[i];
                        q.lines.forEach(l => addRow({
                            description_id: l.description_id, unit_id: l.unit_id, comment: l.comment, tax: l.tax_code,
                            s_qty: l.quantity, s_price: +(l.unit_price_local / r).toFixed(4),
                            source_type: 'quotation', source_id: q.id, source_sub_id: l.source_sub_id,
                        }));
                    } else {
                        const b = quoteData.bills[i];
                        // cost lines: the cost goes to the Cost side; add your price on the Sale side
                        b.lines.forEach(l => addRow({
                            description_id: l.description_id, unit_id: l.unit_id, comment: l.comment, c_tax: l.tax_code,
                            c_qty: l.quantity, c_price: l.unit_price,
                            source_label: '{{ __('Supplier Invoice') }} ' + b.row_no, source_type: 'supplier_invoice', source_id: b.id, source_sub_id: l.source_sub_id,
                        }));
                    }
                    setSel(this, '');
                });

                function fail(msg) { const a = $('#cinAlert'); a.textContent = msg; a.classList.remove('d-none'); window.scrollTo({top: 0, behavior: 'smooth'}); }

                $('#cinSave').addEventListener('click', function () {
                    $('#cinAlert').classList.add('d-none');
                    const f = $('#cinForm');
                    if (!$('#cinJob').value) return fail('{{ __('Select a job.') }}');
                    if (!$('#cinDate').value || !$('#cinDue').value) return fail('{{ __('Invoice date and due date are required.') }}');
                    // only lines with a selling price go on the invoice; cost-only lines stay out (they never bill the customer)
                    const rows = $$('.cin-row', body).filter(tr => num($('.s-price', tr).value) > 0);
                    const skipped = $$('.cin-row', body).length - rows.length;
                    if (!rows.length) return fail('{{ __('Add at least one line with a sale price.') }}');
                    for (const tr of rows) if (!$('.desc', tr).value || !$('.unit', tr).value) return fail('{{ __('Every line needs a description and a unit.') }}');
                    if (skipped && !confirm(skipped + ' {{ __('cost-only line(s) without a sale price will not be saved. Continue?') }}')) return;

                    const fd = new FormData(f);
                    const r = rate();
                    rows.forEach(tr => {
                        const d = $('.desc option:checked', tr);
                        fd.append('description_id[]', d.value);
                        fd.append('comment[]', $('.comment', tr).value);
                        fd.append('unit_id[]', $('.unit', tr).value);
                        fd.append('quantity[]', num($('.s-qty', tr).value) || 1);
                        fd.append('unit_price[]', num($('.s-price', tr).value).toFixed(2));
                        fd.append('tax[]', $('.s-tax', tr).value);
                        fd.append('account[]', d.dataset.acc || 32);
                        fd.append('source_type[]', $('.src-type', tr).value);
                        fd.append('source_id[]', $('.src-id', tr).value);
                        fd.append('source_sub_id[]', $('.src-sub', tr).value);
                        // cost behind the line in company currency, excl. VAT
                        const cost = num($('.c-qty', tr).value) * num($('.c-price', tr).value) * r;
                        fd.append('cost_base_amount[]', cost > 0 ? cost.toFixed(4) : '');
                    });

                    this.disabled = true;
                    fetch('{{ url('/invoice/customer/create') }}', {method: 'POST', body: fd, headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': $('meta[name=csrf-token]').content}})
                        .then(async res => ({ok: res.ok, status: res.status, j: await res.json().catch(() => ({}))}))
                        .then(({ok, status, j}) => {
                            if (ok && j.status === 'success') return location.href = '{{ url('/invoice/customer') }}';
                            this.disabled = false;
                            fail((status === 422 && j.errors) ? Object.values(j.errors).flat().join(' ') : (j.message || '{{ __('Could not save the invoice.') }}'));
                        })
                        .catch(() => { this.disabled = false; fail('{{ __('Could not save the invoice.') }}'); });
                });

                if (typeof datepicker === 'function') datepicker();
                initTs($('#cinForm'));
                addRow();
                // call the handler directly: a synthetic change event makes the app's validator flag the empty Job box red on load
                if ($('#cinJob').value) onJob.call($('#cinJob'));
            });
        </script>
    </main>
</x-app-layout>
