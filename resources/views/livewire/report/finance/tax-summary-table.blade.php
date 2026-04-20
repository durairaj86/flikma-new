<div>
    <div class="container-fluid px-0 py-2">
        @php
            $totalInput = $taxSummaryData['total_input_tax'] ?? 0;
            $totalOutput = $taxSummaryData['total_output_tax'] ?? 0;
            $netTax = $taxSummaryData['net_tax'] ?? 0;
        @endphp

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 border-start border-4 border-primary">
                    <span class="text-muted small fw-bold text-uppercase">Total Output Tax (Sales)</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalOutput, 2) }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 border-start border-4 border-warning">
                    <span class="text-muted small fw-bold text-uppercase">Total Input Tax (Purchases)</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalInput, 2) }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 {{ $netTax >= 0 ? 'bg-danger text-white' : 'bg-success text-white' }}">
                    <span class="small fw-bold opacity-75 text-uppercase">Net Tax {{ $netTax >= 0 ? 'Payable' : 'Refundable' }}</span>
                    <h3 class="fw-bold mb-0">{{ number_format(abs($netTax), 2) }}</h3>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-arrow-up-right-circle me-2"></i>Output Tax Accounts</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light small text-uppercase">
                            <tr>
                                <th class="ps-4">Code</th>
                                <th>Account Name</th>
                                <th class="text-end pe-4">Balance</th>
                            </tr>
                            </thead>
                            <tbody class="small">
                            @forelse(collect($taxSummaryData['tax_accounts'])->where('type', 'Output') as $account)
                                <tr>
                                    <td class="ps-4 text-secondary">{{ $account['account_code'] }}</td>
                                    <td class="fw-medium">{{ $account['account_name'] }}</td>
                                    <td class="text-end pe-4 tabular-nums fw-bold">{{ number_format($account['balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-4 text-muted">No output tax activity</td></tr>
                            @endforelse
                            </tbody>
                            <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="2" class="ps-4">Total Output</td>
                                <td class="text-end pe-4 text-primary">{{ number_format($totalOutput, 2) }}</td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="mb-0 fw-bold text-warning"><i class="bi bi-arrow-down-left-circle me-2"></i>Input Tax Accounts</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light small text-uppercase">
                            <tr>
                                <th class="ps-4">Code</th>
                                <th>Account Name</th>
                                <th class="text-end pe-4">Balance</th>
                            </tr>
                            </thead>
                            <tbody class="small">
                            @forelse(collect($taxSummaryData['tax_accounts'])->where('type', 'Input') as $account)
                                <tr>
                                    <td class="ps-4 text-secondary">{{ $account['account_code'] }}</td>
                                    <td class="fw-medium">{{ $account['account_name'] }}</td>
                                    <td class="text-end pe-4 tabular-nums fw-bold">{{ number_format($account['balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-4 text-muted">No input tax activity</td></tr>
                            @endforelse
                            </tbody>
                            <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="2" class="ps-4">Total Input</td>
                                <td class="text-end pe-4 text-warning">{{ number_format($totalInput, 2) }}</td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 card border-0 shadow-sm overflow-hidden">
            <div class="card-body bg-light-subtle p-4 border-top border-4 {{ $netTax >= 0 ? 'border-danger' : 'border-success' }}">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="fw-bold mb-1">Tax Reconciliation Summary</h6>
                        <p class="text-muted small mb-0">
                            Total Output Tax ({{ number_format($totalOutput, 2) }}) minus Total Input Tax ({{ number_format($totalInput, 2) }})
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <div class="display-6 fw-bold {{ $netTax >= 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format(abs($netTax), 2) }}
                        </div>
                        <span class="small text-muted text-uppercase fw-bold">
                        {{ $netTax >= 0 ? 'Balance Due to Tax Authority' : 'Estimated Tax Refund' }}
                    </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bg-light-subtle { background-color: #f8faff !important; }
        .tabular-nums { font-variant-numeric: tabular-nums; font-family: 'Courier New', Courier, monospace; }
        .letter-spacing-2 { letter-spacing: 2px; }

        @media print {
            .d-print-none { display: none !important; }
            .card { border: 1px solid #dee2e6 !important; box-shadow: none !important; }
            .bg-danger, .bg-success { color: black !important; background: transparent !important; border: 2px solid #000 !important; -webkit-print-color-adjust: exact; }
            .text-white { color: black !important; }
        }
    </style>

</div>
