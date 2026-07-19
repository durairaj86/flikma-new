@section('js', 'supplier_statement')
@section('page-title', 'Supplier Statement')

<div class="statement-wrapper min-vh-100 bg-light py-4">
    <div class="container-fluid px-lg-5">

        <div class="row align-items-center mb-4 d-print-none">
            <div class="col-md-6">
                <h1 class="h3 fw-bold text-slate-900 mb-1">Supplier Statement</h1>
                <p class="text-muted small mb-0">Manage and track supplier account transaction history</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <div class="btn-group shadow-sm">
                    <button class="btn btn-white border border-end-0" onclick="window.print()">
                        <i class="bi bi-printer me-2"></i>Print
                    </button>
                    <div class="btn-group">
                        <button class="btn btn-white border dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-download me-2"></i>Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                            <li><a class="dropdown-item py-2" href="#" onclick="ssExportPdf(event)"><i class="bi bi-file-pdf text-danger me-2"></i>PDF Document</a></li>
                            <li><a class="dropdown-item py-2" href="#" wire:click.prevent="exportExcel"><i class="bi bi-file-excel text-success me-2"></i>Excel Sheet</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4 d-print-none">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">Supplier</label>
                        <select class="form-select bg-light border-0 py-2 no-ts" wire:model.live="supplierId">
                            <option value="">Select a supplier...</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup['id'] }}" wire:key="sup-opt-{{ $sup['id'] }}">{{ $sup['name_en'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">From Date</label>
                        <div wire:ignore>
                            <input type="text" id="ss-start-date"
                                   class="form-control bg-light border-0 py-2"
                                   placeholder="dd-mm-yyyy"
                                   value="{{ $startDate }}" />
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">To Date</label>
                        <div wire:ignore>
                            <input type="text" id="ss-end-date"
                                   class="form-control bg-light border-0 py-2"
                                   placeholder="dd-mm-yyyy"
                                   value="{{ $endDate }}" />
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-supplier fw-bold py-2 flex-grow-1 shadow-sm" onclick="ssApplyFilter()" wire:loading.attr="disabled">
                                <i class="bi bi-filter-left me-2"></i>
                                <span wire:loading.remove>Generate</span>
                                <span wire:loading><span class="spinner-border spinner-border-sm me-1"></span>Loading...</span>
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
                                    <span class="small text-muted">Opening:</span>
                                    <span class="small fw-bold text-dark">{{ number_format($openingBalance, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="small text-muted">Invoiced (+):</span>
                                    <span class="small fw-bold text-supplier">{{ number_format($invoicedAmount, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="small text-muted">Paid (-):</span>
                                    <span class="small fw-bold text-success">{{ number_format($paidAmount, 2) }}</span>
                                </div>
                            </div>

                            <div class="mt-4 text-center">
                                <label class="small text-uppercase text-muted d-block mb-1 fw-bold">Current Balance</label>
                                <h3 class="fw-bold text-supplier mb-0 tabular-nums">
                                    <small class="h6">SAR</small> {{ number_format($closingBalance, 2) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-9">
                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-journal-text me-2 text-supplier"></i>Transaction Ledger</h6>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-dark border px-3 py-2">
                                    Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                                </span>
                                <span class="badge bg-supplier-subtle text-supplier border border-supplier-subtle px-3 py-2">Currency: {{ $company->base_currency ?? 'SAR' }}</span>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                                    <th class="ps-4 border-0">Date</th>
                                    <th class="border-0">Voucher No</th>
                                    <th class="border-0">Description</th>
                                    <th class="border-0">FCY Amount</th>
                                    <th class="text-end border-0">Invoiced</th>
                                    <th class="text-end border-0">Paid</th>
                                    <th class="text-end pe-4 border-0">Balance</th>
                                </tr>
                                </thead>
                                <tbody class="border-top-0">
                                <tr class="bg-light-orange fw-bold">
                                    <td class="ps-4 py-3" colspan="3">Balance Brought Forward</td>
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
                                            <span class="x-small text-muted uppercase">{{ $txn->voucher_type === 'SI' ? 'Supplier Invoice' : ($txn->voucher_type === 'PV' ? 'Payment Voucher' : $txn->voucher_type) }}</span>
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
                                        <td colspan="7" class="text-center py-4 text-muted small italic">No transactions found for the selected period.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                                <tfoot class="bg-light border-top-2">
                                <tr class="fw-bold">
                                    <td colspan="3" class="ps-4 py-3">Closing Totals</td>
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
                            <div class="col-md-4">Prepared By: _________________</div>
                            <div class="col-md-4">Verified By: _________________</div>
                            <div class="col-md-4">Supplier Signature: _________________</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bank-statement style layout: used for Print and PDF export only --}}
            <div id="supplier-statement-print" class="stmt-print d-none d-print-block"
                 data-pdf-filename="SupplierStatement-{{ $supplier->row_no }}-{{ $startDate }}_{{ $endDate }}.pdf">

                <table class="stmt-meta">
                    <tr>
                        <td>
                            <div class="stmt-company">{{ $company->name ?? config('app.name') }}</div>
                            <div class="stmt-sub">
                                @if(!empty($company->phone)) Phone: {{ $company->phone }} @endif
                                @if(!empty($company->email)) &nbsp;|&nbsp; {{ $company->email }} @endif
                            </div>
                            @if(!empty($company->vat_number))
                                <div class="stmt-sub">VAT No: {{ $company->vat_number }}</div>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="stmt-title">SUPPLIER STATEMENT OF ACCOUNT</div>
                            <div class="stmt-sub">Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</div>
                            <div class="stmt-sub">Generated: {{ now()->format('d M Y H:i') }}</div>
                            <div class="stmt-sub">Currency: {{ $company->base_currency ?? 'SAR' }}</div>
                        </td>
                    </tr>
                </table>

                <table class="stmt-meta stmt-box">
                    <tr>
                        <td>
                            <div class="stmt-sub" style="text-transform: uppercase;">Supplier</div>
                            <div class="stmt-strong">{{ $supplier->name_en }} ({{ $supplier->row_no }})</div>
                            <div class="stmt-sub">
                                @if($supplier->email) {{ $supplier->email }} @endif
                                @if($supplier->phone) &nbsp;|&nbsp; {{ $supplier->phone }} @endif
                            </div>
                        </td>
                        <td class="text-end">
                            <table class="stmt-summary">
                                <tr><td>Opening Balance</td><td class="text-end">{{ number_format($openingBalance, 2) }}</td></tr>
                                <tr><td>Invoiced (+)</td><td class="text-end">{{ number_format($invoicedAmount, 2) }}</td></tr>
                                <tr><td>Paid (-)</td><td class="text-end">{{ number_format($paidAmount, 2) }}</td></tr>
                                <tr class="stmt-strong"><td>Closing Balance</td><td class="text-end">{{ number_format($closingBalance, 2) }}</td></tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <table class="stmt-table">
                    <thead>
                    <tr>
                        <th style="width: 10%;">Date</th>
                        <th style="width: 12%;">Voucher No</th>
                        <th style="width: 12%;">Type</th>
                        <th>Description</th>
                        <th style="width: 13%;">FCY Amount</th>
                        <th class="text-end" style="width: 12%;">Invoiced</th>
                        <th class="text-end" style="width: 12%;">Paid</th>
                        <th class="text-end" style="width: 13%;">Balance</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="stmt-strong">
                        <td colspan="7">Balance Brought Forward</td>
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
                            <td class="text-end">{{ number_format((float)$txn->balance, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No transactions found for the selected period.</td>
                        </tr>
                    @endforelse
                    </tbody>
                    <tfoot>
                    <tr class="stmt-strong">
                        <td colspan="4">Closing Totals</td>
                        <td class="text-end"></td>
                        <td class="text-end">{{ number_format($invoicedAmount, 2) }}</td>
                        <td class="text-end">{{ number_format($paidAmount, 2) }}</td>
                        <td class="text-end">{{ number_format($closingBalance, 2) }}</td>
                    </tr>
                    </tfoot>
                </table>

                <div class="stmt-footnote">
                    This is a system generated statement. Please report any discrepancy within 15 days of receipt.
                </div>

                <table class="stmt-meta stmt-signatures">
                    <tr>
                        <td>Prepared By: _________________</td>
                        <td class="text-center">Verified By: _________________</td>
                        <td class="text-end">Supplier Signature: _________________</td>
                    </tr>
                </table>
            </div>
        @else
            <div class="card border-0 shadow-sm text-center py-5">
                <div class="card-body">
                    <div class="bg-light rounded-circle p-4 d-inline-block mb-3">
                        <i class="bi bi-building h1 text-muted"></i>
                    </div>
                    <h5 class="fw-bold">No Supplier Selected</h5>
                    <p class="text-muted mx-auto" style="max-width: 300px;">Please use the filters above to select a supplier and date range to view the statement.</p>
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

            // Reset: the server chose new dates — reflect them in the pickers.
            $wire.on('statement-dates-reset', function (event) {
                var s = document.getElementById('ss-start-date');
                var e = document.getElementById('ss-end-date');
                if (s && s._flatpickr) s._flatpickr.setDate(event.startDate, false);
                if (e && e._flatpickr) e._flatpickr.setDate(event.endDate, false);
            });

            window.ssExportPdf = function (e) {
                e.preventDefault();
                var area = document.getElementById('supplier-statement-print');
                if (!area) {
                    alert('Please select a supplier first.');
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

        @media print {
            body { background: white !important; }
            .d-print-none { display: none !important; }
            .statement-wrapper { padding: 0 !important; background: white !important; }
            .container-fluid { padding: 0 !important; }
        }
    </style>

    @include('includes.report-print-css', ['orientation' => 'portrait'])
</div>
