<div>
    <div class="row justify-content-center">
        <div class="col-xl-12">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="bg-light">
                    <tr class="small text-uppercase fw-bold ls-1 text-muted">
                        <th class="ps-4 py-3 border-0" style="width: 20%">Code</th>
                        <th class="py-3 border-0" style="width: 55%">Operating Revenue</th>
                        <th class="pe-4 py-3 text-end border-0" style="width: 25%">Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($profitAndLossData['revenue'] ?? [] as $account)
                        <tr wire:key="pl-rev-{{ $loop->index }}">
                            <td class="ps-4 text-secondary small">{{ $account['account_code'] }}</td>
                            <td class="text-dark">{{ $account['account_name'] }}</td>
                            <td class="pe-4 text-end tabular-nums">{{ number_format($account['balance'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-3 text-muted small">No revenue activity recorded</td>
                        </tr>
                    @endforelse

                    @if(count($profitAndLossData['revenue'] ?? []) > 0)
                    <tr class="fw-bold bg-light-subtle">
                        <td colspan="2" class="ps-4 py-3 small">Total Operating Revenue</td>
                        <td class="pe-4 text-end border-top border-dark py-3 tabular-nums">
                            {{ number_format($profitAndLossData['total_revenue'] ?? 0, 2) }}
                        </td>
                    </tr>
                    @endif

                    <tr><td colspan="3" class="py-2 border-0"></td></tr>

                    @if(($profitAndLossData['total_revenue'] ?? 0) > 0)
                    <tr class="fw-bold text-white" style="background-color: #0ea5e9;">
                        <td colspan="2" class="ps-4 py-3 h6 mb-0">TOTAL INCOME</td>
                        <td class="pe-4 py-3 text-end h6 mb-0 tabular-nums">
                            {{ number_format($profitAndLossData['total_revenue'] ?? 0, 2) }}
                        </td>
                    </tr>
                    <tr><td colspan="3" class="py-3 border-0"></td></tr>
                    @endif

                    <thead class="bg-light">
                    <tr class="small text-uppercase fw-bold ls-1 text-muted">
                        <th class="ps-4 py-3 border-0">Code</th>
                        <th class="py-3 border-0">Operating Expenses</th>
                        <th class="pe-4 py-3 text-end border-0">Amount</th>
                    </tr>
                    </thead>
                    @forelse($profitAndLossData['expenses'] ?? [] as $account)
                        <tr wire:key="pl-exp-{{ $loop->index }}">
                            <td class="ps-4 text-secondary small">{{ $account['account_code'] }}</td>
                            <td class="text-dark">{{ $account['account_name'] }}</td>
                            <td class="pe-4 text-end tabular-nums text-danger">
                                ({{ number_format(abs($account['balance']), 2) }})
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-3 text-muted small">No expense activity recorded</td>
                        </tr>
                    @endforelse

                    @if(count($profitAndLossData['expenses'] ?? []) > 0)
                    <tr class="fw-bold bg-light-subtle">
                        <td colspan="2" class="ps-4 py-3 small">Total Operating Expenses</td>
                        <td class="pe-4 text-end border-top border-dark py-3 tabular-nums text-danger">
                            {{ number_format($profitAndLossData['total_expenses'] ?? 0, 2) }}
                        </td>
                    </tr>
                    @endif

                    <tr><td colspan="3" class="py-3 border-0"></td></tr>

                    @php
                        $netIncome = $profitAndLossData['net_income'] ?? 0;
                        $isProfit = $netIncome >= 0;
                    @endphp
                    <tr class="fw-bold text-white {{ $isProfit ? 'bg-success' : 'bg-danger' }}" style="{{ $isProfit ? '' : '' }}">
                        <td colspan="2" class="ps-4 py-4 h5 mb-0">
                            NET {{ $isProfit ? 'PROFIT' : 'LOSS' }} FOR THE PERIOD
                        </td>
                        <td class="pe-4 py-4 text-end h5 mb-0 tabular-nums">
                            {{ $isProfit ? '' : '(' }}{{ number_format(abs($netIncome), 2) }}{{ $isProfit ? '' : ')' }}
                        </td>
                    </tr>
                </table>
            </div>

            @if(($profitAndLossData['total_revenue'] ?? 0) > 0)
                <div class="mt-3 text-center text-muted small">
                    <i class="bi bi-graph-up-arrow me-1"></i> Net Profit Margin:
                    <strong class="text-dark">
                        {{ number_format(($netIncome / $profitAndLossData['total_revenue']) * 100, 2) }}%
                    </strong>
                </div>
            @endif
        </div>
    </div>

    <style>
        .tabular-nums { font-variant-numeric: tabular-nums; }
        .h5 { border-bottom: 4px double rgba(255,255,255,0.4); padding-bottom: 5px; }

        @media print {
            .bg-success, .bg-danger {
                background-color: transparent !important;
                color: black !important;
                border: 2px solid #000 !important;
                -webkit-print-color-adjust: exact;
            }
            [style*="background-color: #0ea5e9"] {
                background-color: transparent !important;
                color: black !important;
                border: 2px solid #000 !important;
            }
        }
    </style>
</div>
