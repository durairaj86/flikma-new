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
                            <li><a class="dropdown-item py-2" href="#"><i class="bi bi-file-pdf text-danger me-2"></i>PDF Document</a></li>
                            <li><a class="dropdown-item py-2" href="#"><i class="bi bi-file-excel text-success me-2"></i>Excel Sheet</a></li>
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
                        <select class="form-select bg-light border-0 py-2 no-ts" wire:model="supplierId">
                            <option value="">Select a supplier...</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup['id'] }}">{{ $sup['name_en'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">From Date</label>
                        <input type="text" id="ss-start-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $startDate }}" />
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">To Date</label>
                        <input type="text" id="ss-end-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $endDate }}" />
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-supplier fw-bold py-2 flex-grow-1 shadow-sm" wire:click="applyFilter">
                                <i class="bi bi-filter-left me-2"></i>Generate
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
            <div class="row g-4">
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
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-journal-text me-2 text-supplier"></i>Transaction Ledger</h6>
                            <span class="badge bg-supplier-subtle text-supplier border border-supplier-subtle px-3 py-2">Currency: SAR</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                                    <th class="ps-4 border-0">Date</th>
                                    <th class="border-0">Voucher No</th>
                                    <th class="border-0">Description</th>
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
                                        <td colspan="6" class="text-center py-4 text-muted small italic">No transactions found for the selected period.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                                <tfoot class="bg-light border-top-2">
                                <tr class="fw-bold">
                                    <td colspan="3" class="ps-4 py-3">Closing Totals</td>
                                    <td class="text-end tabular-nums text-supplier">{{ number_format($invoicedAmount, 2) }}</td>
                                    <td class="text-end tabular-nums text-success">{{ number_format($paidAmount, 2) }}</td>
                                    <td class="text-end pe-4 text-supplier fs-5 tabular-nums">{{ number_format($closingBalance, 2) }}</td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-white border rounded shadow-sm d-print-block">
                        <div class="row text-center text-muted x-small">
                            <div class="col-md-4">Prepared By: _________________</div>
                            <div class="col-md-4">Verified By: _________________</div>
                            <div class="col-md-4">Supplier Signature: _________________</div>
                        </div>
                    </div>
                </div>
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
                const startEl = document.getElementById('ss-start-date');
                const endEl   = document.getElementById('ss-end-date');

                if (startEl && !startEl._flatpickr) {
                    flatpickr(startEl, {
                        dateFormat:    'Y-m-d',
                        altInput:      true,
                        altFormat:     'd-m-Y',
                        allowInput:    true,
                        disableMobile: true,
                        defaultDate:   startEl.value || null,
                        onChange(selectedDates, dateStr) {
                            $wire.set('startDate', dateStr, false);
                        },
                    });
                }

                if (endEl && !endEl._flatpickr) {
                    flatpickr(endEl, {
                        dateFormat:    'Y-m-d',
                        altInput:      true,
                        altFormat:     'd-m-Y',
                        allowInput:    true,
                        disableMobile: true,
                        defaultDate:   endEl.value || null,
                        onChange(selectedDates, dateStr) {
                            $wire.set('endDate', dateStr, false);
                        },
                    });
                }
            }

            initFlatpickr();

            Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
                succeed(({ snapshot, effect }) => {
                    queueMicrotask(() => initFlatpickr());
                });
            });
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
            .card { box-shadow: none !important; border: 1px solid #eee !important; }
            .col-xl-3 { width: 100% !important; margin-bottom: 2rem; }
            .col-xl-9 { width: 100% !important; }
        }
    </style>
</div>
