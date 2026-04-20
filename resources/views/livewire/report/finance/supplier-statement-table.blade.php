<div>
    <div class="supplier-statement-wrapper font-sans">
        @if(isset($supplierStatementData['supplier']) && $supplierStatementData['supplier'])

            <div class="row g-3 mb-3">
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm h-100 bg-white">
                        <div class="card-body d-flex align-items-center py-2">
                            <div class="flex-shrink-0">
                                <div class="bg-soft-primary text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi bi-building fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 fw-bold text-dark text-uppercase letter-spacing-1">{{ $supplierStatementData['supplier']->name_en }}</h6>
                                <div class="d-flex gap-3 mt-1">
                                    <span class="text-muted x-small"><i class="bi bi-hash"></i> {{ $supplierStatementData['supplier']->row_no }}</span>
                                    <span class="text-muted x-small"><i class="bi bi-envelope"></i> {{ $supplierStatementData['supplier']->email }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="card border-0 shadow-sm h-100 bg-white overflow-hidden">
                        <div class="card-body p-0">
                            <div class="row g-0 h-100 text-center">
                                <div class="col border-end py-3">
                                    <div class="ls-sm mb-1">OPENING</div>
                                    <div class="fw-bold fs-6 font-mono text-dark">{{ number_format($supplierStatementData['openingBalance'], 2) }}</div>
                                </div>
                                <div class="col border-end py-3">
                                    <div class="ls-sm text-primary-emphasis mb-1">INVOICED</div>
                                    <div class="fw-bold fs-6 font-mono text-primary">{{ number_format($supplierStatementData['invoicedAmount'], 2) }}</div>
                                </div>
                                <div class="col border-end py-3">
                                    <div class="ls-sm text-success-emphasis mb-1">PAID</div>
                                    <div class="fw-bold fs-6 font-mono text-success">{{ number_format($supplierStatementData['paidAmount'], 2) }}</div>
                                </div>
                                <div class="col py-3 bg-light border-start border-4 border-danger">
                                    <div class="ls-sm text-danger mb-1">CLOSING</div>
                                    <div class="fw-bold fs-6 font-mono text-danger">{{ number_format($supplierStatementData['closingBalance'], 2) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="mb-0 fw-bold text-dark ls-sm"><i class="bi bi-journals me-2"></i>SUPPLIER LEDGER</h6>
                    <span class="badge bg-secondary-subtle text-secondary border px-3 py-2 rounded-pill font-mono">CURRENCY: {{ $supplierStatementData['supplier']->currency }}</span>
                </div>
                <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light sticky-top">
                        <tr class="text-muted fw-bold">
                            <th class="ps-3 py-3 ls-sm">DATE</th>
                            <th class="py-3 ls-sm">VOUCHER / REF</th>
                            <th class="py-3 ls-sm text-center">JOB</th>
                            <th class="py-3 ls-sm">DESCRIPTION</th>
                            <th class="py-3 ls-sm text-end">DEBIT (-)</th>
                            <th class="py-3 ls-sm text-end">CREDIT (+)</th>
                            <th class="py-3 ls-sm text-end pe-3">BALANCE</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white">
                        <tr class="table-light-subtle">
                            <td colspan="4" class="ps-3 py-2 fw-bold text-muted small italic">Brought Forward (Opening Balance)</td>
                            <td class="text-end font-mono fw-semibold">{{ $supplierStatementData['openingBalance'] < 0 ? number_format(abs($supplierStatementData['openingBalance']), 2) : '-' }}</td>
                            <td class="text-end font-mono fw-semibold">{{ $supplierStatementData['openingBalance'] > 0 ? number_format($supplierStatementData['openingBalance'], 2) : '-' }}</td>
                            <td class="text-end pe-3 font-mono fw-bold">{{ number_format($supplierStatementData['openingBalance'], 2) }}</td>
                        </tr>

                        @forelse($supplierStatementData['transactions'] as $transaction)
                            @php
                                // Skip transactions with zero debit and credit (no activity)
                                if (abs($transaction->base_debit) < 0.001 && abs($transaction->base_credit) < 0.001) continue;
                            @endphp
                            <tr class="border-bottom-0">
                                <td class="ps-3 text-dark font-mono small">{{ \Carbon\Carbon::parse($transaction->reference_date)->format('d/m/Y') }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $transaction->voucher_no }}</div>
                                    <div class="text-muted x-small">{{ $transaction->voucher_type }} | {{ $transaction->reference_no }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-muted fw-normal border px-2">{{ $transaction->job_number }}</span>
                                </td>
                                <td class="text-wrap small text-muted" style="max-width: 250px;">{{ $transaction->description }}</td>
                                <td class="text-end font-mono text-danger">{{ number_format($transaction->base_debit, 2) }}</td>
                                <td class="text-end font-mono text-success">{{ number_format($transaction->base_credit, 2) }}</td>
                                <td class="text-end pe-3 font-mono fw-bold text-dark bg-light-subtle">{{ number_format($transaction->balance, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-inbox text-light display-4"></i>
                                    <p class="text-muted mt-2 mb-0">No transactions found for this period.</p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                        <tfoot class="table-dark sticky-bottom">
                        <tr>
                            <th colspan="4" class="ps-3 py-3 text-uppercase ls-sm">Period Totals</th>
                            <th class="text-end font-mono py-3 fs-6">{{ number_format($supplierStatementData['paidAmount'], 2) }}</th>
                            <th class="text-end font-mono py-3 fs-6">{{ number_format($supplierStatementData['invoicedAmount'], 2) }}</th>
                            <th class="text-end pe-3 font-mono py-3 fs-6 text-warning">{{ number_format($supplierStatementData['closingBalance'], 2) }}</th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        @else
            <div class="text-center py-5 bg-white border rounded-3 shadow-sm mt-3">
                <i class="bi bi-search display-1 text-light"></i>
                <h5 class="text-muted mt-3 fw-normal">Ready to view statements</h5>
                <p class="text-muted small">Please select a supplier from the list to load records.</p>
            </div>
        @endif
    </div>

    <style>
        .x-small { font-size: 0.7rem; font-weight: 500; }
        .ls-sm { font-size: 0.65rem; font-weight: 700;color: #6c757d; }

        .bg-soft-primary { background-color: #e7f1ff; }
        .table-light-subtle { background-color: #fcfcfc; }
        .bg-light-subtle { background-color: #f8f9fa !important; }

        .table > :not(caption) > * > * {
            padding: 0.75rem 0.5rem;
            border-bottom: 1px solid #f1f1f1;
        }

        .sticky-top { top: 0; z-index: 10; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .sticky-bottom { bottom: 0; z-index: 10; }
        .italic { font-style: italic; }
    </style>
</div>
