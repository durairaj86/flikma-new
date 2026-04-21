@section('js', 'customer_statement')
@section('page-title', 'Customer Statement')

<div class="statement-wrapper min-vh-100 bg-light py-4" wire:key="customer-statement-{{ $customerId }}">
    <div class="container-fluid px-lg-5">
        {{-- Debug info --}}
        @if(isset($debug) && $debug)
            <div class="alert alert-info d-print-none mb-3 py-1 px-2 small">
                ID: {{ $customerId }} | Start: {{ $startDate }} | End: {{ $endDate }} | Customers: {{ count($customers) }}
            </div>
        @endif

        <div class="row align-items-center mb-4 d-print-none">
            <div class="col-md-6">
                <h1 class="h3 fw-bold text-slate-900 mb-1">Customer Statement</h1>
                <p class="text-muted small mb-0">Manage and track account transaction history</p>
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
                <form class="row g-3 align-items-end" wire:submit.prevent="applyFilter" data-turbo="false">
                    <div class="col-lg-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">Customer</label>
                        <select class="form-select bg-light border-0 py-2 no-ts" wire:model.live="customerId">
                            <option value="">Select a customer...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer['id'] }}" wire:key="cust-opt-{{ $customer['id'] }}">{{ $customer['name_en'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">From Date</label>
                        <input type="date" class="form-control bg-light border-0 py-2" wire:model.live="startDate" />
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">To Date</label>
                        <input type="date" class="form-control bg-light border-0 py-2" wire:model.live="endDate" />
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary fw-bold py-2 flex-grow-1 shadow-sm" wire:click="applyFilter">
                                <i class="bi bi-filter-left me-2"></i>Generate
                            </button>
                            <button type="button" class="btn btn-outline-secondary border-0 bg-light py-2 px-3" wire:click="resetFilter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($selectedCustomer)
            <div class="row g-4">
                <div class="col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <div class="avatar-ui mx-auto mb-3">{{ substr($selectedCustomer->name, 0, 1) }}</div>
                                <h5 class="fw-bold mb-0">{{ $selectedCustomer->name }}</h5>
                                <code class="text-primary small fw-bold">{{ $selectedCustomer->code }}</code>
                            </div>

                            <div class="space-y-3 py-3 border-top border-bottom border-light">
                                <div class="d-flex justify-content-between">
                                    <span class="small text-muted">Opening:</span>
                                    <span class="small fw-bold text-dark">{{ number_format($openingBalance, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="small text-muted">Debits (+):</span>
                                    <span class="small fw-bold text-danger">{{ number_format($totalDebit, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="small text-muted">Credits (-):</span>
                                    <span class="small fw-bold text-success">{{ number_format($totalCredit, 2) }}</span>
                                </div>
                            </div>

                            <div class="mt-4 text-center">
                                <label class="small text-uppercase text-muted d-block mb-1 fw-bold">Current Balance</label>
                                <h3 class="fw-bold text-primary mb-0 tabular-nums">
                                    <small class="h6">SAR</small> {{ number_format($closingBalance, 2) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-9">
                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-journal-text me-2 text-primary"></i>Transaction Ledger</h6>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">Currency: SAR</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                                    <th class="ps-4 border-0">Date</th>
                                    <th class="border-0">Reference</th>
                                    <th class="border-0">Description</th>
                                    <th class="text-end border-0">Debit</th>
                                    <th class="text-end border-0">Credit</th>
                                    <th class="text-end pe-4 border-0">Balance</th>
                                </tr>
                                </thead>
                                <tbody class="border-top-0">
                                <tr class="bg-light-blue fw-bold">
                                    <td class="ps-4 py-3" colspan="3">Balance Brought Forward</td>
                                    <td class="text-end"></td>
                                    <td class="text-end"></td>
                                    <td class="text-end pe-4 tabular-nums">{{ number_format($openingBalance, 2) }}</td>
                                </tr>

                                @php $running = $openingBalance; @endphp
                                @forelse($transactions as $txn)
                                    @php $running += (float)$txn->debit - (float)$txn->credit; @endphp
                                    <tr wire:key="txn-{{ $loop->index }}">
                                        <td class="ps-4 small text-muted">{{ $txn->display_date }}</td>
                                        <td>
                                            <span class="fw-medium d-block">{{ $txn->reference }}</span>
                                            <span class="x-small text-muted uppercase">{{ $txn->type }}</span>
                                        </td>
                                        <td class="small">{{ $txn->description }}</td>
                                        <td class="text-end tabular-nums">{{ (float)$txn->debit > 0 ? number_format((float)$txn->debit, 2) : '—' }}</td>
                                        <td class="text-end tabular-nums text-danger">{{ (float)$txn->credit > 0 ? number_format((float)$txn->credit, 2) : '—' }}</td>
                                        <td class="text-end pe-4 fw-bold tabular-nums">{{ number_format($running, 2) }}</td>
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
                                    <td class="text-end tabular-nums text-dark">{{ number_format($totalDebit, 2) }}</td>
                                    <td class="text-end tabular-nums text-danger">{{ number_format($totalCredit, 2) }}</td>
                                    <td class="text-end pe-4 text-primary fs-5 tabular-nums">{{ number_format($closingBalance, 2) }}</td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-white border rounded shadow-sm d-print-block">
                        <div class="row text-center text-muted x-small">
                            <div class="col-md-4">Prepared By: _________________</div>
                            <div class="col-md-4">Verified By: _________________</div>
                            <div class="col-md-4">Customer Signature: _________________</div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="card border-0 shadow-sm text-center py-5">
                <div class="card-body">
                    <div class="bg-light rounded-circle p-4 d-inline-block mb-3">
                        <i class="bi bi-people h1 text-muted"></i>
                    </div>
                    <h5 class="fw-bold">No Customer Selected</h5>
                    <p class="text-muted mx-auto" style="max-width: 300px;">Please use the filters above to select a customer and date range to view the statement.</p>
                </div>
            </div>
        @endif
    </div>

    <style>
        :root {
            --bs-primary: #4f46e5;
            --bs-primary-hover: #4338ca;
            --slate-900: #0f172a;
        }

        .btn-primary { background-color: #4f46e5; border-color: #4f46e5; }
        .btn-primary:hover { background-color: #4338ca; }
        .text-primary { color: var(--bs-primary) !important; }
        .bg-primary-subtle { background-color: #eef2ff !important; }

        .avatar-ui {
            width: 56px; height: 56px; background: #eef2ff; color: var(--bs-primary);
            display: flex; align-items: center; justify-content: center;
            border-radius: 12px; font-weight: 800; font-size: 1.5rem;
        }

        .ls-1 { letter-spacing: 0.05em; }
        .x-small { font-size: 0.7rem; text-transform: uppercase; }
        .tabular-nums { font-variant-numeric: tabular-nums; }
        .bg-light-blue { background-color: #f8faff; }
        .space-y-3 > * + * { margin-top: 0.75rem; }

        .card { border-radius: 1rem; }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.1);
            border-color: var(--bs-primary);
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
