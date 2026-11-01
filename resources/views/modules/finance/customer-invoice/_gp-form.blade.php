        <style>
            /* the page is white all the way down, not just as tall as its content */
            .content-scroll-area:has(#cinForm) { background: #fff; }
            .cin-quick { position: relative; }
            .cin-qbox { position: relative; }
            .cin-qbox > i { position: absolute; inset-inline-start: 12px; top: 50%; transform: translateY(-50%); color: #6b7280; pointer-events: none; }
            .cin-qbox > input { width: 100%; padding: .45rem .75rem .45rem 2.2rem; border: 1px solid #ced4da; border-radius: .5rem; background: #fff; font-size: .9rem; }
            [dir="rtl"] .cin-qbox > input { padding: .45rem 2.2rem .45rem .75rem; }
            .cin-qbox > input:focus { outline: 0; border-color: #6366F1; box-shadow: 0 0 0 3px rgba(99, 102, 241, .18); }
            .cin-qpop { position: absolute; z-index: 1060; top: calc(100% + 6px); inset-inline-start: 0; width: max(100%, 540px); background: #fff; border: 1px solid #e5e7eb; border-radius: .75rem; box-shadow: 0 12px 32px rgba(15, 23, 42, .16); padding: 6px; }
            #cinQuickList { max-height: 260px; overflow-y: auto; }
            .cin-qitem { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 8px 12px; border-radius: .5rem; cursor: pointer; }
            .cin-qitem.active { background: #eef2ff; }
            .cin-qitem .nm { font-weight: 700; font-size: .9rem; color: #1e293b; }
            .cin-qitem .sub { font-size: .75rem; color: #64748b; }
            .cin-qitem .qty { font-size: .75rem; font-weight: 700; color: #4338ca; white-space: nowrap; }
            .cin-qempty { padding: 10px 12px; color: #64748b; font-size: .85rem; }
            .cin-qfoot { border-top: 1px solid #eef0f3; margin-top: 6px; padding: 8px 8px 4px; font-size: .75rem; color: #64748b; }
            .cin-qfoot kbd { background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; border-radius: 4px; padding: 0 5px; font-size: .7rem; margin-inline-end: 2px; box-shadow: none; }
            @keyframes cinFlash { from { box-shadow: inset 0 0 0 100px rgba(99, 102, 241, .22); } to { box-shadow: inset 0 0 0 100px rgba(99, 102, 241, 0); } }
            .cin-row.cin-flash > td { animation: cinFlash 1.4s ease-out; }
            .cin-grid { overflow-x: auto; position: relative; }   /* relative: the hidden <select> of every list is absolutely placed, keep it inside the grid's own scroll box */
            #globalModal .modal-content:has(#cinForm) { overflow-x: hidden; }
            .cin-grid table { border-collapse: collapse; min-width: 1400px; width: 100%; font-size: .85rem; }
            .cin-grid th, .cin-grid td { border: 1px solid #dee2e6; padding: 4px 6px; vertical-align: middle; }
            /* every cell of a line starts at the top, level with the Description box (the Comment box under it makes the line taller) */
            .cin-grid tbody#cinBody td { vertical-align: top; }
            .cin-grid tbody#cinBody td.c-amt, .cin-grid tbody#cinBody td.s-amt, .cin-grid tbody#cinBody td.gp { padding-top: 12px; }
            .cin-grid tbody#cinBody td.c-amt, .cin-grid tbody#cinBody td.s-amt, .cin-grid tbody#cinBody td.gp { white-space: nowrap; }   /* a figure such as 888,888.8888 never wraps or is cut */
            .cin-grid thead th { text-align: center; font-weight: 700; background: #fff; }
            .cin-grid thead th.sale { background: #6366F1; color: #fff; }
            .cin-grid thead th.cost { background: #334155; color: #fff; }
            .cin-grid td.sale { background: #eef2ff; color: #312e81; }
            .cin-grid td.cost { background: #f1f5f9; color: #1e293b; }
            .cin-grid td.sale input, .cin-grid td.cost input, .cin-grid td.sale select, .cin-grid td.cost select { background: #fff; color: #212529; border-color: #cbd5e1; }
            .cin-grid td.sale, .cin-grid td.cost { text-align: right; padding-left: 3px; padding-right: 3px; }
            /* the tax boxes are narrow: less room taken by the box's own padding and arrow so the whole rate name shows */
            .cin-grid td.cost .ts-wrapper.single .ts-control, .cin-grid td.sale .ts-wrapper.single .ts-control { padding-left: 4px !important; padding-right: 20px !important; }
            .cin-grid td.cost .ts-wrapper.single .ts-control > .item, .cin-grid td.sale .ts-wrapper.single .ts-control > .item { font-size: .75rem; }
            .cin-grid .tax-line { display: flex; justify-content: space-between; gap: 8px; margin-top: 2px; font-size: .8rem; }
            .cin-grid .tax-line .drcr { font-weight: 600; }
            .cin-grid .calc { display: flex; align-items: center; gap: 2px; justify-content: center; font-size: .8rem; }
            .cin-grid .calc input { width: 62px; padding: 2px 4px; font-size: .8rem; text-align: right; }
            .cin-grid .calc input.price { width: 96px; }
            .cin-grid .ico { cursor: pointer; font-size: 1.15rem; background: none; border: 0; padding: 0 4px; color: inherit; }
            .cin-grid .ico.rm { color: #dc3545; }
            .cin-grid td.num-cell { height: 1px; padding-top: 8px; padding-bottom: 8px; }
            .cin-grid td input.is-invalid { border-color: #dc3545 !important; }   /* the grid's own input border must not hide the red mark */
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
            /* the summary: one card, cost on the left half and sale on the right half, row for row */
            .cin-card { border: 1px solid #e2e8f0; border-radius: .6rem; overflow: hidden; background: #fff; }
            .cin-sum-h, .cin-sum-r { display: grid; grid-template-columns: 1fr 1fr; }
            /* the Cost / Sale heading is plain text with a thin colour line (no filled bar: it made the summary look crowded) */
            .cin-sum-h > div { padding: 5px 14px; font-size: .78rem; font-weight: 700; color: #1e293b; border-bottom: 2px solid #e5e7eb; }
            .cin-sum-h > div small { font-weight: 500; color: #64748b; }
            .cin-sum-h .cost { border-bottom-color: #334155; }
            .cin-sum-h .sale { text-align: right; border-bottom-color: #6366F1; }
            .cin-sum-r { border-bottom: 1px dotted #e5e7eb; font-size: .78rem; }
            .cin-sum-r .c { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 4px 14px; }
            .cin-sum-r .c.sale { flex-direction: row-reverse; border-left: 1px solid #eef0f3; }   /* sale mirrors cost: text on the right, amount on the left */
            .cin-sum-r .c span { color: #64748b; }
            .cin-sum-r .c b { color: #1e293b; font-variant-numeric: tabular-nums; }
            .cin-sum-r.tot { font-size: .78rem; }
            .cin-sum-r.tot .c.cost { background: #f1f5f9; }
            .cin-sum-r.tot .c.sale { background: #eef2ff; }
            .cin-sum-r.tot .c.sale b { color: #4338ca; }
            /* Gross Profit is the number that matters: larger bold figures (green, red when negative), no coloured band behind it */
            .cin-sum-r.gp { border-bottom: 0; border-top: 2px solid #e5e7eb; background: transparent; }
            .cin-sum-r.gp.neg { border-top-color: #e5e7eb; background: transparent; }
            .cin-sum-r.gp .c { padding-top: 6px; padding-bottom: 6px; }
            .cin-sum-r.gp .c span { color: #1e293b; font-weight: 700; font-size: .82rem; }
            .cin-sum-r.gp .c b { font-size: .88rem; }
            .cin-sum-r.gp .c.full { grid-column: 1 / -1; }
            .cin-sum-r .c b.cin-gp-pos { color: #008000; }
            .cin-sum-r .c b.cin-gp-neg { color: #e00000; }
            .cin-note { font-weight: 600; font-size: .78rem; color: #475569; }
        </style>

        @if(!empty($inModal))
            {{-- the standard modal header: Draft on the left, Cancel / Save on the right --}}
            <div class="g-3 align-items-center border-bottom py-3 px-4 small cin-modal-head">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="module-info"><span class="fw-semibold fs-5">{{ __('Draft') }}</span></div>
                    <div id="cin-head-buttons">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary" id="btn-cancel"><i class="bi bi-x-circle me-1"></i> {{ __('Cancel') }}</button>
                            <button type="button" class="btn btn-primary" id="cinSave"><i class="bi bi-save me-1"></i> {{ __('Save Invoice') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        @else
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-3 pb-2">
            <h4 class="fw-bold text-dark mb-0">{{ __('New Customer Invoice') }} <span class="badge bg-secondary-subtle text-secondary-emphasis fs-6 ms-1">{{ __('Draft') }}</span></h4>
            <div class="d-flex gap-2">
                @if(!empty($inModal))
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                @else
                    <a href="{{ url('/invoice/customer') }}" class="btn btn-outline-secondary rounded-pill px-4">{{ __('Cancel') }}</a>
                @endif
                <button type="button" class="btn btn-primary rounded-pill px-4" id="cinSave">{{ __('Save Invoice') }}</button>
            </div>
        </div>
        @endif
        @if(!empty($inModal))<div id="moduleForm" class="px-4 pt-3 pb-4">@endif
        <div id="cinAlert" class="alert alert-danger d-none py-2"></div>

        @php
            $quickDescriptions = $descriptions->map(function ($d) {
                $sub = trim(($d->category ?? '') . ($d->description_local ? ' · ' . $d->description_local : ''), ' ·');
                return ['id' => $d->id, 'name' => $d->description, 'sub' => $sub];
            })->values();
        @endphp
        <form id="cinForm" novalidate onsubmit="return false">
            @csrf
            @if(!empty($invoice))<input type="hidden" name="data-id" value="{{ $invoice->id }}">@endif
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
                    <input type="text" name="invoice_date" id="cinDate" class="form-control datepicker" value="{{ $invoice->invoice_date ?? now()->format('d-m-Y') }}" autocomplete="off" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('Due Date') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="due_date" id="cinDue" class="form-control datepicker" value="{{ $invoice->due_at ?? now()->addDays(30)->format('d-m-Y') }}" autocomplete="off" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('Currency') }} / {{ __('Ex. Rate') }}</label>
                    <div class="input-group cin-cur">
                        <select name="currency" id="cinCurrency" class="tom-select" data-live-search="true" data-max-width="110">
                            @foreach($currencies as $code => $name)
                                <option value="{{ $code }}" @selected($code == ($invoice->currency ?? 'SAR'))>{{ $code }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="currency_rate" id="cinRate" class="form-control text-end" value="{{ isset($invoice) ? (float) $invoice->currency_rate : 1 }}">
                    </div>
                </div>
                <div class="col-md-3">
                    {{-- quick add (POS style): search a description, Enter adds its line to the grid at once --}}
                    <div class="cin-quick" data-kb-own-enter>
                        <div class="d-flex justify-content-between align-items-end">
                            <label class="form-label" for="cinQuick">{{ __('Add a line by description') }}</label>
                            <small class="mb-2 invisible">&nbsp;</small>   {{-- same label row height as the Pick up box beside it --}}
                        </div>
                        <div class="cin-qbox">
                            <i class="bi bi-search"></i>
                            <input type="text" id="cinQuick" autocomplete="off" spellcheck="false" placeholder="{{ __('Search description') }}" role="combobox" aria-expanded="false" aria-controls="cinQuickList">
                        </div>
                        <div class="cin-qpop d-none" id="cinQuickPop">
                            <div id="cinQuickList" role="listbox"></div>
                            <div class="cin-qfoot">
                                <kbd>↑</kbd><kbd>↓</kbd> {{ __('to choose') }} · <kbd>Enter</kbd> {{ __('to add') }} · <kbd>Esc</kbd> {{ __('to clear') }} · {{ __('Type a quantity first, e.g.') }} <kbd>15 {{ __('freight') }}</kbd>
                            </div>
                        </div>
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
                        <th style="width:44px">#</th>
                        <th style="min-width:250px;width:250px">{{ __('Description') }} <sup class="text-danger">*</sup></th>
                        <th style="min-width:130px;width:130px">{{ __('Unit') }} <sup class="text-danger">*</sup></th>
                        <th class="cost" style="min-width:166px;width:166px">{{ __('Cost') }}</th>
                        <th class="cost" style="min-width:97px;width:97px">{{ __('Amount') }}<br>(<span class="cur">SAR</span>)</th>
                        <th class="cost" style="min-width:169px;width:169px">{{ __('Tax') }}<br><small class="fw-normal">{{ __('Amount') }} (<span class="cur">SAR</span>) · {{ __('Dr / Cr') }}</small></th>
                        <th class="sale" style="min-width:166px;width:166px">{{ __('Sale') }}</th>
                        <th class="sale" style="min-width:97px;width:97px">{{ __('Amount') }}<br>(<span class="cur">SAR</span>)</th>
                        <th class="sale" style="min-width:169px;width:169px">{{ __('Tax') }}<br><small class="fw-normal">{{ __('Amount') }} (<span class="cur">SAR</span>) · {{ __('Dr / Cr') }}</small></th>
                        <th style="min-width:90px">{{ __('GP') }}</th>
                    </tr>
                    </thead>
                    <tbody id="cinBody"></tbody>
                </table>
            </div>

            {{-- summary: the left half holds the Add Line button; the right half is ONE card, the cost side on its left and the sale side on its right, row for row (ids are filled by calc()) --}}
            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="cinAddRow"><i class="bi bi-plus-circle me-1"></i>{{ __('Add Line') }}</button>
                </div>
                <div class="col-md-6">
                    <div class="cin-note d-flex justify-content-between gap-3 mb-2">
                        <span>{{ __('Cost') }} - ({{ __('Qty') }} x {{ __('Amount Per Unit') }}<span class="rate-note"> x {{ __('Ex. Rate') }}</span>)</span>
                        <span class="text-end">{{ __('Sale') }} - ({{ __('Qty') }} x {{ __('Amount Per Unit') }}<span class="rate-note"> x {{ __('Ex. Rate') }}</span>)</span>
                    </div>
                    <div class="cin-card">
                        <div class="cin-sum-h">
                            <div class="cost">{{ __('Cost') }} <small>(<span class="cur">SAR</span>)</small></div>
                            <div class="sale">{{ __('Sale') }} <small>(<span class="cur">SAR</span>)</small></div>
                        </div>
                        <div class="cin-sum-r">
                            <div class="c cost"><span>{{ __('Total Cost') }}</span><b id="tCost">0.00</b></div>
                            <div class="c sale"><span>{{ __('Total Sale') }}</span><b id="tSale">0.00</b></div>
                        </div>
                        <div class="cin-sum-r">
                            <div class="c cost"><span>{{ __('Cost Tax Amount') }}</span><b id="tCostTax">0.00</b></div>
                            <div class="c sale"><span>{{ __('Sale Tax Amount') }}</span><b id="tSaleTax">0.00</b></div>
                        </div>
                        <div class="cin-sum-r tot">
                            <div class="c cost"><span>{{ __('Cost With Tax') }}</span><b id="tCostWith">0.00</b></div>
                            <div class="c sale"><span>{{ __('Sale With Tax') }}</span><b id="tSaleWith">0.00</b></div>
                        </div>
                        <div class="cin-sum-r">
                            <div class="c cost"></div>   {{-- no GP% on the cost side: the cell stays empty so the rows keep matching --}}
                            <div class="c sale"><span>{{ __('Sale GP%') }}</span><b id="tSaleGp">0.00%</b></div>
                        </div>
                        <div class="cin-sum-r gp">
                            <div class="c full"><span>{{ __('Gross Profit') }} (<span class="cur">SAR</span>)</span><b id="tGp">0.00</b></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">{{ __('Terms & Conditions') }}</label>
                    <textarea name="terms" class="form-control" rows="3" style="min-height: calc(3 * 1.5em + .75rem + 2px);" maxlength="1000" placeholder="{{ __('Any additional notes...') }}">{{ $invoice->terms ?? '' }}</textarea>
                </div>
            </div>
        </form>

        <template id="cinRowTpl">
            <tr class="cin-row">
                <td class="num-cell"><div class="d-flex flex-column justify-content-between align-items-center h-100 gap-2"><span class="line-no"></span><button type="button" class="ico rm" title="{{ __('Remove') }}"><i class="bi bi-trash"></i></button></div></td>
                <td>
                    <input type="hidden" class="src-type"><input type="hidden" class="src-id"><input type="hidden" class="src-sub">
                    <select class="tom-select desc" data-live-search="true" data-dropdown-parent="body" data-dropdown-width="320" data-placeholder="{{ __('Select') }}"><option value="">{{ __('Select') }}</option>
                        @foreach($descriptions as $d)<option value="{{ $d->id }}" data-acc="{{ $d->sale_account_id }}">{{ $d->description }}</option>@endforeach
                    </select>
                    <input type="text" class="form-control form-control-sm mt-1 comment" maxlength="500" placeholder="{{ __('Comment') }}">
                    <div class="src-label small fw-semibold text-primary mt-1 d-none"></div>
                </td>
                <td>
                    <select class="tom-select unit" data-live-search="true" data-dropdown-parent="body" data-dropdown-width="200" data-placeholder="{{ __('Select Unit') }}"><option value="">{{ __('Select Unit') }}</option>
                        @foreach($units as $u)<option value="{{ $u->id }}">{{ $u->unit_symbol ?: $u->unit_name }}</option>@endforeach
                    </select>
                </td>
                <td class="cost"><div class="calc position-relative"><input class="form-control decimal c-qty" data-decimals="4" value="1"> x <input class="form-control decimal price c-price" data-decimals="4" placeholder="0.00"></div></td>
                <td class="cost c-amt">0.00</td>
                <td class="cost"><select class="tom-select c-tax" data-live-search="true" data-dropdown-parent="body" data-dropdown-width="280">@foreach($taxes as $taxGroup => $taxRates)<optgroup label="{{ __($taxGroup) }}">@foreach($taxRates as $t)<option value="{{ $t['code'] }}" data-p="{{ $t['percent'] }}" data-subtext="{{ $t['description'] }}" @selected($t['code'] === 'STANDARD')>{{ $t['label'] }}</option>@endforeach</optgroup>@endforeach</select><div class="tax-line"><span class="c-taxamt">0.00</span> <span class="drcr">Dr(+)</span></div></td>
                <td class="sale"><div class="calc position-relative"><input class="form-control decimal s-qty" data-decimals="4" value="1"> x <input class="form-control decimal price s-price" data-decimals="4" placeholder="0.00"></div></td>
                <td class="sale s-amt">0.00</td>
                <td class="sale"><select class="tom-select s-tax" data-live-search="true" data-dropdown-parent="body" data-dropdown-width="280">@foreach($taxes as $taxGroup => $taxRates)<optgroup label="{{ __($taxGroup) }}">@foreach($taxRates as $t)<option value="{{ $t['code'] }}" data-p="{{ $t['percent'] }}" data-subtext="{{ $t['description'] }}" @selected($t['code'] === 'STANDARD')>{{ $t['label'] }}</option>@endforeach</optgroup>@endforeach</select><div class="tax-line"><span class="s-taxamt">0.00</span> <span class="drcr">Cr(+)</span></div></td>
                <td class="text-end gp">0.00</td>
            </tr>
        </template>

        <script>
            (function (run) { document.readyState === 'complete' ? run() : window.addEventListener('load', run); })(function () {
                const $ = (s, r = document) => r.querySelector(s);
                const $$ = (s, r = document) => [...r.querySelectorAll(s)];
                const num = v => parseFloat(String(v ?? '').replace(/,/g, '')) || 0;
                const fmt = v => v.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                // line figures keep up to 4 decimals (at least 2 shown); the summary below is always 2
                const r4 = v => Math.round((v + Number.EPSILON) * 1e4) / 1e4;
                const r2 = v => Math.round((v + Number.EPSILON) * 1e2) / 1e2;
                const fmtLine = v => v.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 4});
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
                        const sAmt = r4(num($('.s-qty', tr).value) * num($('.s-price', tr).value) * r);
                        const cAmt = r4(num($('.c-qty', tr).value) * num($('.c-price', tr).value) * r);
                        const sp = num($('.s-tax option:checked', tr)?.dataset.p), cp = num($('.c-tax option:checked', tr)?.dataset.p);
                        const sTax = r4(sAmt * sp / 100), cTax = r4(cAmt * cp / 100);
                        $('.s-amt', tr).textContent = fmtLine(sAmt); $('.s-taxamt', tr).textContent = fmtLine(sTax);
                        $('.c-amt', tr).textContent = fmtLine(cAmt); $('.c-taxamt', tr).textContent = fmtLine(cTax);
                        const gp = r4(sAmt - cAmt), g = $('.gp', tr);
                        g.textContent = fmtLine(gp); g.className = 'text-end gp ' + (gp < 0 ? 'cin-gp-neg' : 'cin-gp-pos');
                        t.sale += sAmt; t.cost += cAmt; t.sTax += sTax; t.cTax += cTax;
                    });
                    // the summary is rounded to 2 decimals, and every figure in it comes from the rounded ones
                    t.sale = r2(t.sale); t.cost = r2(t.cost); t.sTax = r2(t.sTax); t.cTax = r2(t.cTax);
                    const gp = r2(t.sale - t.cost);
                    $('#tSale').textContent = fmt(t.sale); $('#tCost').textContent = fmt(t.cost); $('#tGp').textContent = fmt(gp);
                    $('#tGp').className = gp < 0 ? 'cin-gp-neg' : 'cin-gp-pos';
                    $('#tGp').closest('.cin-sum-r').classList.toggle('neg', gp < 0);
                    $('#tSaleGp').textContent = (t.sale ? gp / t.sale * 100 : 0).toFixed(2) + '%';
                    $('#tSaleTax').textContent = fmt(t.sTax); $('#tCostTax').textContent = fmt(t.cTax);
                    $('#tSaleWith').textContent = fmt(t.sale + t.sTax); $('#tCostWith').textContent = fmt(t.cost + t.cTax);
                    $$('.cur').forEach(e => e.textContent = $('#cinCurrency').value);
                    $$('.rate-note').forEach(e => e.classList.toggle('d-none', r === 1));   // the exchange rate only enters the formula when it is not 1
                }

                body.addEventListener('input', e => { if (e.target.classList.contains('s-price') || e.target.classList.contains('s-qty')) e.target.setCustomValidity(''); });
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
                    // the invoice is in the customer's own currency (set in the Customer module): follow it and lock the box
                    const curBox = $('#cinCurrency').tomselect;
                    if (curBox) {
                        if (o?.dataset.currency && curBox.getValue() !== o.dataset.currency) curBox.setValue(o.dataset.currency);
                        curBox.lock();   // never chosen on the invoice: it is always the customer's currency
                    }
                    quoteData = null;
                    @if(!empty($invoice))
                    return;   // editing a saved invoice: lines are not picked up from the job again, the "Pick up from this job" box stays hidden
                    @endif
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
                            // only what is actually there: no "Supplier Invoice: 0" when the job has none
                            const parts = [];
                            if (open(j.quotations)) parts.push(`<span class="text-primary">{{ __('Quotation') }}: ${open(j.quotations)}</span>`);
                            if (open(j.bills)) parts.push(`<span class="text-dark">{{ __('Supplier Invoice') }}: ${open(j.bills)}</span>`);
                            $('#cinPending').innerHTML = parts.length ? parts.join(' <span class="text-muted mx-1">|</span> ') + ' <span class="text-warning-emphasis">{{ __('pending') }}</span>' : '';
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
                            source_label: '{{ __('Quotation') }} ' + q.row_no, source_type: 'quotation', source_id: q.id, source_sub_id: l.source_sub_id,
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

                // errors are shown like on every other page: the toast at the bottom (the red fields carry the detail)
                function fail(msg) {
                    if (window.toastr) toastr.error(msg, '', {positionClass: 'toast-bottom-center'});
                    else { const a = $('#cinAlert'); a.textContent = msg; a.classList.remove('d-none'); }
                }

                // same check and same message as the other forms (form-validation.js marks the fields, this says how many)
                function validateForm(f, rows) {
                    // Description, Unit, Sale quantity and Sale price are required on every line (0 does not count for the quantity or the price)
                    $$('.cin-row', body).forEach(tr => {
                        $('.desc', tr).required = true; $('.unit', tr).required = true;
                        const p = $('.s-price', tr);
                        p.required = true;
                        p.setCustomValidity(p.value.trim() !== '' && !(num(p.value) > 0) ? '{{ __('Enter a price above 0.') }}' : '');
                        const q = $('.s-qty', tr);
                        q.required = true;
                        q.setCustomValidity(q.value.trim() !== '' && !(num(q.value) > 0) ? '{{ __('Enter a quantity above 0.') }}' : '');
                    });
                    if (window.jQuery(f).valid()) return true;
                    const form = window.jQuery(f);
                    // dropdown boxes are counted through their (hidden) select: their red outline is added a moment later
                    const visibleBad = form.find('input.is-invalid:visible, textarea.is-invalid:visible, select.is-invalid');
                    const count = visibleBad.length || 1;
                    const firstBad = visibleBad.length ? visibleBad.first() : form.find('.is-invalid').first();
                    const label = (firstBad.closest("[class*='col-'], .form-group, td").find('label').first().text() || '').replace(/\*/g, '').trim();
                    let msg = trans('Validation Failed') + `: ${count} field${count > 1 ? 's' : ''} need to be fixed.`;
                    if (label) msg += ` (${trans('First')}: ${label})`;
                    fail(msg);
                    return false;
                }

                $('#cinSave').addEventListener('click', function () {
                    $('#cinAlert').classList.add('d-none');
                    const f = $('#cinForm');
                    // only lines with a selling price go on the invoice; cost-only lines stay out (they never bill the customer)
                    const rows = $$('.cin-row', body).filter(tr => num($('.s-price', tr).value) > 0);
                    const skipped = $$('.cin-row', body).length - rows.length;
                    if (!validateForm(f, rows)) return;
                    if (skipped && !confirm(skipped + ' {{ __('cost-only line(s) without a sale price will not be saved. Continue?') }}')) return;

                    const fd = new FormData(f);
                    const r = rate();
                    rows.forEach(tr => {
                        const d = $('.desc option:checked', tr);
                        fd.append('description_id[]', d.value);
                        fd.append('comment[]', $('.comment', tr).value);
                        fd.append('unit_id[]', $('.unit', tr).value);
                        fd.append('quantity[]', num($('.s-qty', tr).value) || 1);
                        fd.append('unit_price[]', String(r4(num($('.s-price', tr).value))));
                        fd.append('tax[]', $('.s-tax', tr).value);
                        fd.append('account[]', d.dataset.acc || 32);
                        fd.append('source_type[]', $('.src-type', tr).value);
                        fd.append('source_id[]', $('.src-id', tr).value);
                        fd.append('source_sub_id[]', $('.src-sub', tr).value);
                        // cost behind the line in company currency, excl. VAT
                        const cost = r4(num($('.c-qty', tr).value) * num($('.c-price', tr).value) * r);
                        fd.append('cost_base_amount[]', cost > 0 ? cost.toFixed(4) : '');
                    });

                    this.disabled = true;
                    fetch('{{ url('/invoice/customer/create') }}', {method: 'POST', body: fd, headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': $('meta[name=csrf-token]').content}})
                        .then(async res => ({ok: res.ok, status: res.status, j: await res.json().catch(() => ({}))}))
                        .then(({ok, status, j}) => {
                            if (ok && j.status === 'success') {
                                @if(!empty($inModal))
                                // opened on the invoice list: close the modal and refresh the list
                                bootstrap.Modal.getInstance(document.getElementById('globalModal'))?.hide();
                                if (window.toastr) toastr.success(j.message || '{{ __('Saved') }}');
                                try { window.jQuery('#dataTable').DataTable().ajax.reload(null, false); } catch (e) { /* list not on this page */ }
                                return;
                                @else
                                return location.href = '{{ url('/invoice/customer') }}';
                                @endif
                            }
                            this.disabled = false;
                            fail((status === 422 && j.errors) ? Object.values(j.errors).flat().join(' ') : (j.message || '{{ __('Could not save the invoice.') }}'));
                        })
                        .catch(() => { this.disabled = false; fail('{{ __('Could not save the invoice.') }}'); });
                });

                @if(!empty($inModal))
                // The lists open on the page body (outside the modal), and the modal's focus trap pulls focus back into the
                // modal the moment one opens, which closes it again (the list "blinks"): turn the trap off for this form.
                const gm = document.getElementById('globalModal');
                const freeFocus = () => bootstrap.Modal.getInstance(gm)?._focustrap?.deactivate();
                freeFocus();
                gm.addEventListener('shown.bs.modal', freeFocus, {once: true});
                setTimeout(freeFocus, 500);
                @endif
                // ---- quick add (POS style): search a description, Enter adds its line; the box clears and stays ready for the next ----
                const quickIn = $('#cinQuick'), quickPop = $('#cinQuickPop'), quickList = $('#cinQuickList');
                const quickDesc = @json($quickDescriptions);
                const esc = t => String(t ?? '').replace(/[&<>"']/g, ch => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[ch]));
                let quickHits = [], quickAt = 0, quickQty = 1;
                const isStarterRow = tr => !$('.desc', tr).value && !num($('.s-price', tr).value) && !num($('.c-price', tr).value) && !$('.src-id', tr).value;

                function quickClose() { quickPop.classList.add('d-none'); quickIn.setAttribute('aria-expanded', 'false'); }
                function quickPaint() {
                    quickList.innerHTML = quickHits.length
                        ? quickHits.map((d, i) => `<div class="cin-qitem${i === quickAt ? ' active' : ''}" role="option" data-i="${i}"><div><div class="nm">${esc(d.name)}</div>${d.sub ? `<div class="sub">${esc(d.sub)}</div>` : ''}</div>${quickQty !== 1 ? `<span class="qty">× ${quickQty}</span>` : ''}</div>`).join('')
                        : `<div class="cin-qempty">{{ __('No matching description') }}</div>`;
                    quickPop.classList.remove('d-none'); quickIn.setAttribute('aria-expanded', 'true');
                    quickList.querySelector('.active')?.scrollIntoView({block: 'nearest'});
                }
                function quickSearch() {
                    // "15 freight": a leading number is the quantity of the line
                    const m = quickIn.value.trim().match(/^(\d+(?:\.\d+)?)\s+(.+)$/);
                    quickQty = m ? parseFloat(m[1]) : 1;
                    const term = (m ? m[2] : quickIn.value).trim().toLowerCase();
                    if (!term) { quickHits = []; return quickClose(); }
                    const words = term.split(/\s+/);
                    const hay = d => (d.name + ' ' + d.sub).toLowerCase();
                    quickHits = quickDesc.filter(d => words.every(w => hay(d).includes(w)))
                        .sort((a, b) => (b.name.toLowerCase().startsWith(term) - a.name.toLowerCase().startsWith(term)) || a.name.localeCompare(b.name)).slice(0, 30);
                    quickAt = 0; quickPaint();
                }
                function quickAdd(d) {
                    // the untouched starter line is filled instead of leaving an empty line behind
                    let tr = $$('.cin-row', body).find(isStarterRow);
                    if (tr) setSel($('.desc', tr), d.id); else tr = addRow({description_id: d.id});
                    if (quickQty !== 1) { $('.s-qty', tr).value = quickQty; $('.c-qty', tr).value = quickQty; }
                    calc();
                    tr.classList.remove('cin-flash'); void tr.offsetWidth; tr.classList.add('cin-flash');
                    tr.scrollIntoView({block: 'nearest', behavior: 'smooth'});
                    quickIn.value = ''; quickHits = []; quickQty = 1; quickClose(); quickIn.focus();
                }
                quickIn.addEventListener('input', quickSearch);
                quickIn.addEventListener('focus', () => { if (quickIn.value.trim()) quickSearch(); });
                quickIn.addEventListener('keydown', e => {
                    const open = !quickPop.classList.contains('d-none');
                    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                        if (!open) return quickSearch();
                        e.preventDefault();
                        if (quickHits.length) quickAt = (quickAt + (e.key === 'ArrowDown' ? 1 : quickHits.length - 1)) % quickHits.length;
                        quickPaint();
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (open && quickHits[quickAt]) quickAdd(quickHits[quickAt]);
                    } else if (e.key === 'Escape') {
                        if (quickIn.value || open) { e.preventDefault(); e.stopPropagation(); quickIn.value = ''; quickHits = []; quickClose(); }
                    } else if (e.key === 'Tab') quickClose();
                });
                quickList.addEventListener('mousedown', e => {   // mousedown: it runs before the box loses focus
                    const it = e.target.closest('.cin-qitem');
                    if (!it) return;
                    e.preventDefault();
                    quickAdd(quickHits[+it.dataset.i]);
                });
                quickIn.addEventListener('blur', () => setTimeout(quickClose, 120));

                if (typeof datepicker === 'function') datepicker();
                initTs($('#cinForm'));
                $('#cinCurrency').tomselect?.lock();   // the currency comes from the customer of the job, it is not picked here
                setTimeout(() => $('#cinCurrency').tomselect?.lock(), 600);   // opened as a modal, the page sets the dropdowns up again right after: lock once more
                const editLines = @json($editLines ?? []);
                if (editLines.length) {
                    editLines.forEach(l => addRow(l));
                    // opened as a modal, the page initialises every dropdown once more right after this script ran and drops the values
                    // set so far: put them back once that has finished
                    setTimeout(() => $$('.cin-row', body).forEach((tr, i) => {
                        const l = editLines[i]; if (!l) return;
                        setSel($('.desc', tr), l.description_id); setSel($('.unit', tr), l.unit_id);
                        if (l.tax) setSel($('.s-tax', tr), l.tax);
                        if (l.c_tax) setSel($('.c-tax', tr), l.c_tax);
                        calc();
                        $('#cinJob').tomselect?.lock();   // the job of a saved invoice is not changed
                    }), 0);
                } else addRow();
                // call the handler directly: a synthetic change event makes the app's validator flag the empty Job box red on load
                if ($('#cinJob').value) onJob.call($('#cinJob'));

            });
        </script>
@if(!empty($inModal))</div>@endif
