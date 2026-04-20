<div>

        <div class="container-fluid px-4 py-4">
            <div class="d-flex align-items-center justify-content-between mb-4 d-print-none">
                <div>
                    <h1 class="h4 fw-bold text-dark mb-1">Input Tax Register</h1>
                    <p class="text-muted small mb-0">
                        Comprehensive log of tax transactions and account codes.
                    </p>
                </div>
                <div class="btn-group shadow-sm">
                    <button onclick="window.print()" class="btn btn-white border btn-sm">
                        <i class="bi bi-printer me-1"></i> Print
                    </button>
                    <button class="btn btn-indigo btn-sm">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
                    </button>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm p-3 border-start border-4 border-indigo">
                        <div class="small text-muted fw-bold text-uppercase">Total Input Tax Amount</div>
                        <h2 class="fw-bold mb-0 text-indigo">
                            {{ isset($inputTaxData['total_input_tax']) ? number_format($inputTaxData['total_input_tax'], 2) : '0.00' }}
                        </h2>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light small fw-bold text-uppercase text-secondary">
                        <tr>
                            <th class="ps-4 py-3">Account</th>
                            <th>Reference No</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th class="pe-4 text-end">Tax Amount</th>
                        </tr>
                        </thead>
                        <tbody class="small">
                        @if(isset($inputTaxData['input_tax_transactions']) && count($inputTaxData['input_tax_transactions']) > 0)
                            @foreach($inputTaxData['input_tax_transactions'] as $transaction)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $transaction['account_name'] }}</div>
                                        <div class="text-muted extra-small">{{ $transaction['account_code'] }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border fw-normal">
                                            {{ $transaction['reference_no'] }}
                                        </span>
                                    </td>
                                    <td class="text-muted">
                                        {{ \Carbon\Carbon::parse($transaction['reference_date'])->format('d-m-Y') }}
                                    </td>
                                    <td class="text-muted">{{ $transaction['description'] }}</td>
                                    <td class="pe-4 text-end tabular-nums fw-bold text-indigo">
                                        {{ number_format($transaction['amount'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-exclamation-circle d-block fs-2 mb-2"></i>
                                    No input tax transactions found for this period.
                                </td>
                            </tr>
                        @endif
                        </tbody>
                        <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="4" class="ps-4 py-3 text-end text-uppercase small text-muted">Total Input Tax</td>
                            <td class="pe-4 text-end py-3 text-indigo fs-6">
                                {{ isset($inputTaxData['total_input_tax']) ? number_format($inputTaxData['total_input_tax'], 2) : '0.00' }}
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="card-footer bg-white border-0 py-3 d-print-none">
                    <div class="alert alert-info border-0 mb-0 small d-flex align-items-center">
                        <i class="bi bi-info-circle-fill me-3 fs-5"></i>
                        <div>
                            <strong>Tax Reconciliation:</strong> These figures represent tax paid on inputs.
                            Ensure all entries match your general ledger for the selected accounting period.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            /* Modern UI Accents */
            .text-indigo { color: #4f46e5 !important; }
            .btn-indigo { background-color: #4f46e5; color: white; border: none; }
            .btn-indigo:hover { background-color: #4338ca; color: white; }
            .border-indigo { border-color: #4f46e5 !important; }
            .btn-white { background: white; }
            .tabular-nums { font-variant-numeric: tabular-nums; }
            .extra-small { font-size: 0.75rem; }

            /* Smooth Printing */
            @media print {
                .d-print-none { display: none !important; }
                .container-fluid { padding: 0 !important; }
                .card { border: none !important; box-shadow: none !important; }
                .table thead { background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; }
                .text-indigo { color: black !important; }
                .border-start { border-left: none !important; }
            }
        </style>

</div>
