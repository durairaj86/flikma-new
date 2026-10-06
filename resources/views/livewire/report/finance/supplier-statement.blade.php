@section('js', 'supplier_statement')
@section('page-title', __('Supplier Statement'))
@section('page-subtitle', __('Manage and track supplier account transaction history'))
@section('hide-topbar', true)

<div class="statement-wrapper min-vh-100 bg-light pb-4">
    <div class="container-fluid px-3">
        <style>
            .rpt-title { display: none; }
            body:not(.has-top-header) .rpt-title { display: block; }
        </style>
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-2 d-print-none">
            <div class="rpt-title">
                <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                @hasSection('page-subtitle')<div class="text-muted small mt-1">@yield('page-subtitle')</div>@endif
            </div>
            <div class="btn-group shadow-sm ms-auto position-relative">
                                <button class="btn btn-white border border-end-0" onclick="window.print()">
                                    <i class="bi bi-printer me-2"></i>{{ __('Print') }}
                                </button>
                                <div class="btn-group position-relative" x-data="{ open: false, pos: '', toggle() { this.open = !this.open; if (this.open) { const r = this.$refs.btn.getBoundingClientRect(); this.pos = 'position:fixed;left:auto;bottom:auto;right:' + (window.innerWidth - r.right) + 'px;top:' + (r.bottom + 4) + 'px;'; } } }" @click.outside="open = false" @keydown.escape.window="open = false" @scroll.window="open = false" @resize.window="open = false">
                                    <button type="button" class="btn btn-white border dropdown-toggle" x-ref="btn" @click="toggle()" :aria-expanded="open">
                                        <i class="bi bi-download me-2"></i>{{ __('Export') }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow" :class="{ show: open }" x-cloak :style="pos" @click="open = false">
                                        <li><a class="dropdown-item py-2" href="#" onclick="ssExportPdf(event)"><i class="bi bi-file-pdf text-danger me-2"></i>{{ __('PDF Document') }}</a></li>
                                        <li><a class="dropdown-item py-2" href="#" wire:click.prevent="exportExcel"><i class="bi bi-file-excel text-success me-2"></i>{{ __('Excel Sheet') }}</a></li>
                                    </ul>
                                </div>
                            </div>
        </div>

                <div class="card border-0 shadow-sm mb-4 d-print-none">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4 col-xl-2 col-xxl-3" id="ss-supplier-wrap" wire:ignore>
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('Supplier') }}</label>
                        <x-common.suppliers wire:model.live="supplierId" id="ss-supplier" name="ss-supplier"
                                            :value="$supplierId ? [(int) $supplierId] : null"
                                            :suppliers="\App\Models\Supplier\Supplier::orderBy('name_en')->get()"
                                            placeholder="{{ __('Select a supplier...') }}"></x-common.suppliers>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('From Date') }}</label>
                        <div wire:ignore>
                            <input type="text" id="ss-start-date"
                                   class="form-control bg-light border-0 py-2"
                                   placeholder="dd-mm-yyyy"
                                   value="{{ $startDate }}" />
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('To Date') }}</label>
                        <div wire:ignore>
                            <input type="text" id="ss-end-date"
                                   class="form-control bg-light border-0 py-2"
                                   placeholder="dd-mm-yyyy"
                                   value="{{ $endDate }}" />
                        </div>
                    </div>
                    <div class="col-lg-12 col-xl-6 col-xxl-5">
                        <div class="d-flex flex-wrap gap-2 justify-content-end align-items-center">
                            <button type="button" class="btn btn-supplier fw-bold py-2 shadow-sm" onclick="ssApplyFilter()" wire:loading.attr="disabled">
                                <i class="bi bi-filter-left me-2"></i>
                                <span wire:loading.remove>{{ __('Generate') }}</span>
                                <span wire:loading><span class="spinner-border spinner-border-sm me-1"></span>{{ __('Loading...') }}</span>
                            </button>
<button type="button" class="btn btn-outline-secondary border-0 bg-light py-2 px-3" wire:click="resetFilter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($supplier)
            <div class="row g-4 d-print-none">
                <div class="col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <div class="avatar-ui mx-auto mb-3">{{ substr($supplier->name_en, 0, 1) }}</div>
                                <h5 class="fw-bold mb-0">{{ $supplier->name_en }}</h5>
                                <code class="text-supplier small fw-bold">{{ $supplier->row_no }}</code>
                            </div>

                            @if($supplier->email || $supplier->phone)
                                <div class="mb-3 pb-3 border-bottom border-light">
                                    @if($supplier->email)
                                        <div class="d-flex align-items-center gap-2 small text-muted mb-1">
                                            <i class="bi bi-envelope text-supplier"></i>
                                            <span>{{ $supplier->email }}</span>
                                        </div>
                                    @endif
                                    @if($supplier->phone)
                                        <div class="d-flex align-items-center gap-2 small text-muted">
                                            <i class="bi bi-telephone text-supplier"></i>
                                            <span>{{ $supplier->phone }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <div class="space-y-3 py-3 border-top border-bottom border-light">
                                <div class="d-flex justify-content-between">
                                    <span class="small text-muted">{{ __('Opening:') }}</span>
                                    <span class="small fw-bold text-dark">{{ number_format($openingBalance, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="small text-muted">{{ __('Invoiced (+):') }}</span>
                                    <span class="small fw-bold text-supplier">{{ number_format($invoicedAmount, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="small text-muted">{{ __('Paid (-):') }}</span>
                                    <span class="small fw-bold text-success">{{ number_format($paidAmount, 2) }}</span>
                                </div>
                            </div>

                            <div class="mt-4 text-center">
                                <label class="small text-uppercase text-muted d-block mb-1 fw-bold">{{ __('Current Balance') }}</label>
                                <h3 class="fw-bold text-supplier mb-0 tabular-nums">
                                    <small class="h6">{{ __('SAR') }}</small> {{ number_format($closingBalance, 2) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-9">
                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h6 class="mb-0 fw-bold flex-grow-1"><i class="bi bi-journal-text me-2 text-supplier"></i>{{ __('Transaction Ledger') }}</h6>
                            <div class="d-flex align-items-center gap-2 ms-auto flex-shrink-0">
                                <span class="badge bg-light text-dark border px-3 py-2">
                                    {{ __('Period:') }} {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                                </span>
                                <span class="badge bg-supplier-subtle text-supplier border border-supplier-subtle px-3 py-2">{{ __('Currency:') }} {{ $company->base_currency ?? 'SAR' }}</span>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                                    <th class="ps-4 border-0">{{ __('Date') }}</th>
                                    <th class="border-0">{{ __('Voucher No') }}</th>
                                    <th class="border-0">{{ __('Description') }}</th>
                                    <th class="border-0">{{ __('FCY Amount') }}</th>
                                    <th class="text-end border-0">{{ __('Invoiced') }}</th>
                                    <th class="text-end border-0">{{ __('Paid') }}</th>
                                    <th class="text-end pe-4 border-0">{{ __('Balance') }}</th>
                                </tr>
                                </thead>
                                <tbody class="border-top-0">
                                <tr class="bg-light-orange fw-bold">
                                    <td class="ps-4 py-3" colspan="3">{{ __('Balance Brought Forward') }}</td>
                                    <td class="text-end"></td>
                                    <td class="text-end"></td>
                                    <td class="text-end"></td>
                                    <td class="text-end pe-4 tabular-nums">{{ number_format($openingBalance, 2) }}</td>
                                </tr>

                                @forelse($transactions as $txn)
                                    <tr wire:key="txn-{{ $loop->index }}">
                                        <td class="ps-4 small text-muted">{{ \Carbon\Carbon::parse($txn->reference_date)->format('d M Y') }}</td>
                                        <td>
                                            <span class="fw-medium d-block">{{ $txn->voucher_no }}</span>
                                            <span class="x-small text-muted uppercase">{{ $txn->voucher_type === 'SI' ? __('Supplier Invoice') : ($txn->voucher_type === 'PV' ? __('Payment Voucher') : $txn->voucher_type) }}</span>
                                        </td>
                                        <td class="small">{{ $txn->description }}</td>
                                        <td class="small">
                                            @if($txn->fcy_amount !== null)
                                                <span class="fw-medium d-block">{{ $txn->currency }} {{ number_format($txn->fcy_amount, 2) }}</span>
                                                <span class="x-small text-muted">{{ $company->base_currency ?? 'SAR' }} {{ number_format($txn->exchange_rate, 4) }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end tabular-nums text-supplier">
                                            {{ $txn->voucher_type === 'SI' && (float)$txn->base_credit > 0 ? number_format((float)$txn->base_credit, 2) : '—' }}
                                        </td>
                                        <td class="text-end tabular-nums text-success">
                                            {{ $txn->voucher_type === 'PV' && (float)$txn->base_debit > 0 ? number_format((float)$txn->base_debit, 2) : '—' }}
                                        </td>
                                        <td class="text-end pe-4 fw-bold tabular-nums">{{ number_format((float)$txn->balance, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted small italic">{{ __('No transactions found for the selected period.') }}</td>
                                    </tr>
                                @endforelse
                                </tbody>
                                <tfoot class="bg-light border-top-2">
                                <tr class="fw-bold">
                                    <td colspan="3" class="ps-4 py-3">{{ __('Closing Totals') }}</td>
                                    <td class="text-end"></td>
                                    <td class="text-end tabular-nums text-supplier">{{ number_format($invoicedAmount, 2) }}</td>
                                    <td class="text-end tabular-nums text-success">{{ number_format($paidAmount, 2) }}</td>
                                    <td class="text-end pe-4 text-supplier fs-5 tabular-nums">{{ number_format($closingBalance, 2) }}</td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-white border rounded shadow-sm">
                        <div class="row text-center text-muted x-small">
                            <div class="col-md-4">{{ __('Prepared By:') }} _________________</div>
                            <div class="col-md-4">{{ __('Verified By:') }} _________________</div>
                            <div class="col-md-4">{{ __('Supplier Signature:') }} _________________</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bank-statement style layout: used for Print and PDF export only --}}
            <div id="supplier-statement-print" class="stmt-print d-none d-print-block"
                 data-pdf-filename="SupplierStatement-{{ $supplier->row_no }}-{{ $startDate }}_{{ $endDate }}.pdf">

                <div class="stmt-head">
                    <div class="stmt-title">{{ __('SUPPLIER STATEMENT OF ACCOUNT') }}</div>
                    <div class="stmt-period">{{ __('Period:') }} {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} {{ __('to') }} {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}</div>
                </div>

                <div class="stmt-band">
                    <div class="stmt-band-left">
                        <div class="stmt-company">{{ $company->name ?? config('app.name') }}</div>
                        <div class="stmt-sub">
                            @if(!empty($company->phone)) {{ __('Phone:') }} {{ $company->phone }} @endif
                            @if(!empty($company->email)) &nbsp;|&nbsp; {{ $company->email }} @endif
                        </div>
                        @if(!empty($company->vat_number))
                            <div class="stmt-sub">{{ __('VAT No:') }} {{ $company->vat_number }}</div>
                        @endif
                    </div>
                    <div class="stmt-band-right">
                        <div class="stmt-sub">{{ __('Currency:') }} {{ $company->base_currency ?? 'SAR' }}</div>
                        <div class="stmt-sub">{{ __('Generated:') }} {{ now()->format('d-m-Y H:i') }}</div>
                    </div>
                </div>

                <div class="stmt-holder">
                    <div class="stmt-holder-label">{{ __('Supplier') }}</div>
                    <div class="stmt-holder-name">{{ $supplier->name_en }} <span>({{ $supplier->row_no }})</span></div>
                    <div class="stmt-holder-line">
                        @if($supplier->email) {{ $supplier->email }} @endif
                        @if($supplier->phone) &nbsp;|&nbsp; {{ $supplier->phone }} @endif
                    </div>
                </div>

                <div class="stmt-cards">
                    <div class="stmt-card"><div class="stmt-card-label">{{ __('Opening Balance') }}</div><div class="stmt-card-value">{{ number_format($openingBalance, 2) }}</div></div>
                    <div class="stmt-card"><div class="stmt-card-label">{{ __('Invoiced (+)') }}</div><div class="stmt-card-value">{{ number_format($invoicedAmount, 2) }}</div></div>
                    <div class="stmt-card"><div class="stmt-card-label">{{ __('Paid (-)') }}</div><div class="stmt-card-value">{{ number_format($paidAmount, 2) }}</div></div>
                    <div class="stmt-card"><div class="stmt-card-label">{{ __('Closing Balance') }}</div><div class="stmt-card-value">{{ number_format($closingBalance, 2) }}</div></div>
                </div>

                <table class="stmt-table">
                    <thead>
                    <tr>
                        <th style="width: 10%;">{{ __('Date') }}</th>
                        <th style="width: 12%;">{{ __('Voucher No') }}</th>
                        <th style="width: 12%;">{{ __('Type') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th style="width: 13%;">{{ __('FCY Amount') }}</th>
                        <th class="text-end" style="width: 12%;">{{ __('Invoiced') }}</th>
                        <th class="text-end" style="width: 12%;">{{ __('Paid') }}</th>
                        <th class="text-end" style="width: 13%;">{{ __('Balance') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="stmt-strong stmt-bf">
                        <td colspan="7">{{ __('Balance Brought Forward') }}</td>
                        <td class="text-end">{{ number_format($openingBalance, 2) }}</td>
                    </tr>
                    @forelse($transactions as $txn)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($txn->reference_date)->format('d M Y') }}</td>
                            <td>{{ $txn->voucher_no }}</td>
                            <td>{{ $this->voucherTypeLabel($txn->voucher_type) }}</td>
                            <td>{{ $txn->description }}</td>
                            <td>
                                @if($txn->fcy_amount !== null)
                                    {{ $txn->currency }} {{ number_format($txn->fcy_amount, 2) }}<br>
                                    <span style="font-size:10px;color:#666;">{{ $company->base_currency ?? 'SAR' }} {{ number_format($txn->exchange_rate, 4) }}</span>
                                @endif
                            </td>
                            <td class="text-end">{{ $txn->voucher_type === 'SI' && (float)$txn->base_credit > 0 ? number_format((float)$txn->base_credit, 2) : '' }}</td>
                            <td class="text-end">{{ $txn->voucher_type === 'PV' && (float)$txn->base_debit > 0 ? number_format((float)$txn->base_debit, 2) : '' }}</td>
                            <td class="text-end stmt-bal">{{ number_format((float)$txn->balance, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">{{ __('No transactions found for the selected period.') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                    <tfoot>
                    <tr class="stmt-strong">
                        <td colspan="4">{{ __('Closing Totals') }}</td>
                        <td class="text-end"></td>
                        <td class="text-end">{{ number_format($invoicedAmount, 2) }}</td>
                        <td class="text-end">{{ number_format($paidAmount, 2) }}</td>
                        <td class="text-end">{{ number_format($closingBalance, 2) }}</td>
                    </tr>
                    </tfoot>
                </table>

                <div class="stmt-footnote">
                    {{ __('This is a system generated statement. Please report any discrepancy within 15 days of receipt.') }}
                </div>

                <table class="stmt-meta stmt-signatures">
                    <tr>
                        <td>{{ __('Prepared By:') }} _________________</td>
                        <td class="text-center">{{ __('Verified By:') }} _________________</td>
                        <td class="text-end">{{ __('Supplier Signature:') }} _________________</td>
                    </tr>
                </table>
            </div>
        @else
            <div class="card border-0 shadow-sm text-center py-5">
                <div class="card-body">
                    <div class="bg-light rounded-circle p-4 d-inline-block mb-3">
                        <i class="bi bi-building h1 text-muted"></i>
                    </div>
                    <h5 class="fw-bold">{{ __('No Supplier Selected') }}</h5>
                    <p class="text-muted mx-auto" style="max-width: 300px;">{{ __('Please use the filters above to select a supplier and date range to view the statement.') }}</p>
                </div>
            </div>
        @endif
    </div>

    @script
    <script>
        (function () {
            function initFlatpickr() {
                [['ss-start-date', 'startDate'], ['ss-end-date', 'endDate']].forEach(function (pair) {
                    var el = document.getElementById(pair[0]);
                    if (!el || el._flatpickr) return;

                    flatpickr(el, {
                        dateFormat:    'Y-m-d',
                        altInput:      true,
                        altFormat:     'd-m-Y',
                        allowInput:    true,
                        disableMobile: true,
                        defaultDate:   el.value || null,
                        onChange: function (selectedDates, dateStr) {
                            if (dateStr) {
                                $wire.set(pair[1], dateStr);
                            }
                        },
                    });
                });
            }

            initFlatpickr();

            // Generate: push whatever is in the pickers, then run the filter in one request.
            window.ssApplyFilter = function () {
                var s = document.getElementById('ss-start-date');
                var e = document.getElementById('ss-end-date');
                if (s && s.value) $wire.set('startDate', s.value, false);
                if (e && e.value) $wire.set('endDate', e.value, false);
                $wire.call('applyFilter');
            };

            // Supplier picker: shared tom-select component, wire:ignore'd so Livewire commits can't wipe it.
            var ssSup = document.getElementById('ss-supplier');
            if (ssSup && !ssSup.tomselect) { initTomSelectForm($('#ss-supplier-wrap')); }

            // Reset: the server chose new dates/supplier — reflect them in the pickers.
            $wire.on('statement-dates-reset', function (event) {
                var s = document.getElementById('ss-start-date');
                var e = document.getElementById('ss-end-date');
                if (s && s._flatpickr) s._flatpickr.setDate(event.startDate, false);
                if (e && e._flatpickr) e._flatpickr.setDate(event.endDate, false);
                if (ssSup && ssSup.tomselect) { ssSup.tomselect.setValue(event.supplier || '', true); }
            });

            window.ssExportPdf = function (e) {
                e.preventDefault();
                var area = document.getElementById('supplier-statement-print');
                if (!area) {
                    alert("{{ __('Please select a supplier first.') }}");
                    return;
                }
                var clone = area.cloneNode(true);
                clone.classList.remove('d-none');
                clone.style.padding = '10px';
                var opt = {
                    margin: 0.4,
                    filename: area.dataset.pdfFilename || 'SupplierStatement.pdf',
                    html2canvas: { scale: 2, useCORS: true },
                    jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' },
                    pagebreak: { mode: ['avoid-all', 'css'] }
                };
                html2pdf().set(opt).from(clone).save();
            };
        })();
    </script>
    @endscript

    <style>
        :root {
            --supplier-primary: #d97706;
            --supplier-dark: #92400e;
            --supplier-light: #fffbeb;
            --slate-900: #0f172a;
        }

        .btn-supplier { background-color: var(--supplier-primary); border-color: var(--supplier-primary); color: #fff; }
        .btn-supplier:hover { background-color: #b45309; border-color: #b45309; color: #fff; }
        .text-supplier { color: var(--supplier-primary) !important; }
        .bg-supplier-subtle { background-color: #fef3c7 !important; }
        .border-supplier-subtle { border-color: #fde68a !important; }

        .avatar-ui {
            width: 56px; height: 56px; background: #fef3c7; color: var(--supplier-primary);
            display: flex; align-items: center; justify-content: center;
            border-radius: 12px; font-weight: 800; font-size: 1.5rem;
        }

        .ls-1 { letter-spacing: 0.05em; }
        .x-small { font-size: 0.7rem; text-transform: uppercase; }
        .tabular-nums { font-variant-numeric: tabular-nums; }
        .bg-light-orange { background-color: #fffbeb; }
        .space-y-3 > * + * { margin-top: 0.75rem; }

        .card { border-radius: 1rem; }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(217, 119, 6, 0.1);
            border-color: var(--supplier-primary);
        }

        /* Bank-statement layout (print + PDF export). Styles live outside
           @media print so html2pdf can render the same markup.
           Deliberately restrained: black/grey text, one dark navy accent, no coloured amounts —
           this document goes to the customer. */
        #supplier-statement-print {
            --ink: #111827; --muted: #6b7280; --line: #d1d5db; --accent: #1f2d4d;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: var(--ink);
            background: #fff;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        #supplier-statement-print table { width: 100%; border-collapse: collapse; }
        #supplier-statement-print .text-end { text-align: right; }
        #supplier-statement-print .text-center { text-align: center; }
        #supplier-statement-print .stmt-band { display: flex; justify-content: space-between; gap: 16px; padding-bottom: 10px; border-bottom: 2px solid var(--accent); }
        #supplier-statement-print .stmt-band-right { text-align: right; }
        #supplier-statement-print .stmt-company { font-size: 17px; font-weight: 700; color: var(--accent); }
        #supplier-statement-print .stmt-head { text-align: center; margin-bottom: 10px; }
        #supplier-statement-print .stmt-period { font-size: 11px; color: var(--muted); margin-top: 2px; }
        #supplier-statement-print .stmt-title { font-size: 15px; font-weight: 700; letter-spacing: .08em; color: var(--accent); }
        #supplier-statement-print .stmt-sub { font-size: 10px; color: var(--muted); }
        #supplier-statement-print .stmt-strong { font-weight: 700; }
        #supplier-statement-print .stmt-holder { padding: 10px 0 8px; }
        #supplier-statement-print .stmt-holder-label { font-size: 9px; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); }
        #supplier-statement-print .stmt-holder-name { font-size: 13px; font-weight: 700; }
        #supplier-statement-print .stmt-holder-name span { font-weight: 400; color: var(--muted); font-size: 11px; }
        #supplier-statement-print .stmt-holder-line { font-size: 10px; color: var(--muted); }
        #supplier-statement-print .stmt-cards { display: flex; margin: 6px 0 14px; border: 1px solid var(--line); }
        #supplier-statement-print .stmt-card { flex: 1; padding: 7px 12px; border-right: 1px solid var(--line); }
        #supplier-statement-print .stmt-card:last-child { border-right: 0; background: #f3f4f6; }
        #supplier-statement-print .stmt-card-label { font-size: 9px; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); }
        #supplier-statement-print .stmt-card-value { font-size: 13px; font-weight: 700; }
        #supplier-statement-print .stmt-table { margin-top: 4px; }
        #supplier-statement-print .stmt-table th {
            padding: 6px 8px;
            font-size: 9.5px;
            letter-spacing: .05em;
            text-transform: uppercase;
            text-align: left;
            color: var(--ink);
            background: #f3f4f6;
            border-top: 1px solid var(--ink);
            border-bottom: 1px solid var(--ink);
        }
        #supplier-statement-print .stmt-table th.text-end { text-align: right; }
        #supplier-statement-print .stmt-table td { border: 0; border-bottom: 1px solid #e5e7eb; padding: 6px 8px; vertical-align: top; }
        #supplier-statement-print .stmt-table tr.stmt-bf td { font-weight: 700; background: #fafafa; border-bottom: 1px solid var(--line); }
        #supplier-statement-print .stmt-bal { font-weight: 700; }
        #supplier-statement-print .stmt-table tfoot td { font-weight: 700; padding: 7px 8px; border-top: 1px solid var(--ink); border-bottom: 2px solid var(--ink); background: #f3f4f6; }
        #supplier-statement-print .stmt-footnote { margin-top: 12px; font-size: 9px; color: var(--muted); font-style: italic; }
        #supplier-statement-print .stmt-signatures { margin-top: 34px; font-size: 10px; color: #374151; }
        #supplier-statement-print .stmt-meta td { vertical-align: top; padding: 2px 0; }

        @media print {
            body { background: white !important; }
            .inline-page-title { display: none !important; }
            .d-print-none { display: none !important; }
            .statement-wrapper { padding: 0 !important; background: white !important; }
            .container-fluid { padding: 0 !important; }
        }
    </style>

    @include('includes.report-print-css', ['orientation' => 'portrait'])
</div>
