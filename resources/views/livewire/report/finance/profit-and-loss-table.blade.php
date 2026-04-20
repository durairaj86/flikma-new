<div>
    <div class="container-fluid px-0 py-2">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-dark mb-1">Profit & Loss Statement</h2>
            <p class="text-muted small text-uppercase letter-spacing-2">
                Period: {{ $fromDate ?? 'Current Year' }} &mdash; {{ $toDate ?? date('F d, Y') }}
            </p>
            <div class="d-print-none mt-3">
                <button onclick="window.print()" class="btn btn-outline-dark btn-sm px-4 shadow-sm">
                    <i class="bi bi-printer me-2"></i>Print Report
                </button>
                <button class="btn btn-primary btn-sm px-4 shadow-sm ms-2">
                    <i class="bi bi-file-earmark-pdf me-2"></i>Download PDF
                </button>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-xl-9 col-lg-11">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 text-uppercase small fw-bold" style="width: 20%">Code</th>
                                <th class="py-3 text-uppercase small fw-bold" style="width: 55%">Operating Revenue</th>
                                <th class="pe-4 py-3 text-end small fw-bold" style="width: 25%">Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($profitAndLossData['revenue'] ?? [] as $account)
                                <tr>
                                    <td class="ps-4 text-secondary small">{{ $account['account_code'] }}</td>
                                    <td class="text-dark">{{ $account['account_name'] }}</td>
                                    <td class="pe-4 text-end tabular-nums">{{ number_format($account['balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-3 text-muted">No revenue activity recorded</td>
                                </tr>
                            @endforelse
                            <tr class="fw-bold bg-light-subtle">
                                <td colspan="2" class="ps-4 py-3">Total Operating Revenue</td>
                                <td class="pe-4 text-end border-top border-dark py-3">
                                    {{ number_format($profitAndLossData['total_revenue'] ?? 0, 2) }}
                                </td>
                            </tr>

                            <tr><td colspan="3" class="py-2 border-0"></td></tr>
                            <tr class="bg-indigo text-white fw-bold shadow-sm">
                                <td colspan="2" class="ps-4 py-3 h6 mb-0">TOTAL INCOME</td>
                                <td class="pe-4 py-3 text-end h6 mb-0">
                                    {{ number_format($profitAndLossData['total_revenue'] ?? 0, 2) }}
                                </td>
                            </tr>
                            <tr><td colspan="3" class="py-4 border-0"></td></tr>

                            <thead class="bg-light border-top">
                            <tr>
                                <th class="ps-4 py-3 text-uppercase small fw-bold">Code</th>
                                <th class="py-3 text-uppercase small fw-bold">Operating Expenses</th>
                                <th class="pe-4 py-3 text-end small fw-bold">Amount</th>
                            </tr>
                            </thead>
                            @forelse($profitAndLossData['expenses'] ?? [] as $account)
                                <tr>
                                    <td class="ps-4 text-secondary small">{{ $account['account_code'] }}</td>
                                    <td class="text-dark">{{ $account['account_name'] }}</td>
                                    <td class="pe-4 text-end tabular-nums text-danger">
                                        ({{ number_format(abs($account['balance']), 2) }})
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-3 text-muted">No expense activity recorded</td>
                                </tr>
                            @endforelse
                            <tr class="fw-bold bg-light-subtle">
                                <td colspan="2" class="ps-4 py-3">Total Operating Expenses</td>
                                <td class="pe-4 text-end border-top border-dark py-3 text-danger">
                                    {{ number_format($profitAndLossData['total_expenses'] ?? 0, 2) }}
                                </td>
                            </tr>

                            <tr><td colspan="3" class="py-4 border-0"></td></tr>
                            @php
                                $netIncome = $profitAndLossData['net_income'] ?? 0;
                                $isProfit = $netIncome >= 0;
                            @endphp
                            <tr class="{{ $isProfit ? 'bg-success' : 'bg-danger' }} text-white fw-bold shadow-sm">
                                <td colspan="2" class="ps-4 py-4 h5 mb-0">
                                    NET {{ $isProfit ? 'PROFIT' : 'LOSS' }} FOR THE PERIOD
                                </td>
                                <td class="pe-4 py-4 text-end h5 mb-0">
                                    {{ $isProfit ? '' : '(' }}{{ number_format(abs($netIncome), 2) }}{{ $isProfit ? '' : ')' }}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                @if(($profitAndLossData['total_revenue'] ?? 0) > 0)
                    <div class="mt-4 text-center d-print-none">
                <span class="text-muted small">
                    <i class="bi bi-graph-up-arrow me-1"></i> Net Profit Margin:
                    <strong class="text-dark">
                        {{ number_format(($netIncome / $profitAndLossData['total_revenue']) * 100, 2) }}%
                    </strong>
                </span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .letter-spacing-2 { letter-spacing: 2px; }
        .bg-indigo { background-color: #4f46e5 !important; }
        .bg-light-subtle { background-color: #f8faff !important; }
        .tabular-nums { font-variant-numeric: tabular-nums; font-family: 'Courier New', Courier, monospace; }

        /* Double line for final total effect */
        .h5 { border-bottom: 4px double rgba(255,255,255,0.4); padding-bottom: 5px; }

        @media print {
            .d-print-none { display: none !important; }
            body { background: white !important; }
            .card { border: none !important; box-shadow: none !important; }
            .bg-indigo, .bg-success, .bg-danger {
                background-color: transparent !important;
                color: black !important;
                border: 2px solid #000 !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>

</div>
