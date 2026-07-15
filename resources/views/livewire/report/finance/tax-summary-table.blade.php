<div>
    @php
        $totalInput = $taxSummaryData['total_input_tax'] ?? 0;
        $totalOutput = $taxSummaryData['total_output_tax'] ?? 0;
        $netTax = $taxSummaryData['net_tax'] ?? 0;
    @endphp

    <div class="row g-4 d-print-none">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-primary">
                        <i class="bi bi-arrow-up-right-circle me-2"></i>Output VAT (Collected on Sales)
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light small text-uppercase fw-bold ls-1 text-muted">
                        <tr>
                            <th class="ps-4 border-0 py-3">Code</th>
                            <th class="border-0 py-3">Account Name</th>
                            <th class="text-end pe-4 border-0 py-3">Balance</th>
                        </tr>
                        </thead>
                        <tbody class="small">
                        @forelse(collect($taxSummaryData['tax_accounts'])->where('type', 'Output Tax') as $account)
                            <tr>
                                <td class="ps-4 text-secondary">{{ $account['account_code'] }}</td>
                                <td class="fw-medium">{{ $account['account_name'] }}</td>
                                <td class="text-end pe-4 tabular-nums fw-bold text-primary">{{ number_format($account['balance'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-4 text-muted small">No output tax activity</td></tr>
                        @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="2" class="ps-4 py-3 small text-muted text-uppercase">Total Output VAT</td>
                            <td class="text-end pe-4 py-3 text-primary tabular-nums">{{ number_format($totalOutput, 2) }}</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-warning">
                        <i class="bi bi-arrow-down-left-circle me-2"></i>Input VAT (Reclaimable on Purchases)
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light small text-uppercase fw-bold ls-1 text-muted">
                        <tr>
                            <th class="ps-4 border-0 py-3">Code</th>
                            <th class="border-0 py-3">Account Name</th>
                            <th class="text-end pe-4 border-0 py-3">Balance</th>
                        </tr>
                        </thead>
                        <tbody class="small">
                        @forelse(collect($taxSummaryData['tax_accounts'])->where('type', 'Input Tax') as $account)
                            <tr>
                                <td class="ps-4 text-secondary">{{ $account['account_code'] }}</td>
                                <td class="fw-medium">{{ $account['account_name'] }}</td>
                                <td class="text-end pe-4 tabular-nums fw-bold text-warning">{{ number_format($account['balance'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-4 text-muted small">No input tax activity</td></tr>
                        @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="2" class="ps-4 py-3 small text-muted text-uppercase">Total Input VAT</td>
                            <td class="text-end pe-4 py-3 text-warning tabular-nums">{{ number_format($totalInput, 2) }}</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Reconciliation --}}
    <div class="mt-4 card border-0 shadow-sm overflow-hidden d-print-none">
        <div class="card-body p-4 border-top border-4 {{ $netTax >= 0 ? 'border-danger' : 'border-success' }}">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h6 class="fw-bold mb-1">VAT Reconciliation Summary</h6>
                    <p class="text-muted small mb-0">
                        Output VAT ({{ number_format($totalOutput, 2) }}) &minus; Input VAT ({{ number_format($totalInput, 2) }})
                        = <strong>{{ $netTax >= 0 ? 'Net VAT Payable' : 'Net VAT Refundable' }}</strong>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="display-6 fw-bold tabular-nums {{ $netTax >= 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format(abs($netTax), 2) }}
                    </div>
                    <span class="small text-muted text-uppercase fw-bold">
                        {{ $netTax >= 0 ? 'Balance Due to ZATCA' : 'Estimated Refund' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Bank-statement style layout: used for Print and PDF export only --}}
    <div id="ts-print" class="stmt-print d-none d-print-block"
         data-pdf-filename="TaxSummary-{{ $startDate ?? '' }}-{{ $endDate ?? '' }}.pdf">

        <table class="stmt-meta">
            <tr>
                <td>
                    <div class="stmt-company">{{ optional(authUserCompany())->name ?? config('app.name') }}</div>
                </td>
                <td class="text-end">
                    <div class="stmt-title">TAX SUMMARY (VAT)</div>
                    <div class="stmt-sub">Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</div>
                    <div class="stmt-sub">Generated: {{ now()->format('d M Y H:i') }} &nbsp;|&nbsp; Currency: SAR</div>
                </td>
            </tr>
        </table>

        <table class="stmt-table">
            <thead>
            <tr>
                <th>Code</th>
                <th>Account Name</th>
                <th>Type</th>
                <th class="text-end">Balance</th>
            </tr>
            </thead>
            <tbody>
            @forelse($taxSummaryData['tax_accounts'] as $account)
                <tr>
                    <td>{{ $account['account_code'] }}</td>
                    <td>{{ $account['account_name'] }}</td>
                    <td>{{ $account['type'] }}</td>
                    <td class="text-end">{{ number_format($account['balance'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">No tax activity in this period.</td></tr>
            @endforelse
            </tbody>
            <tfoot>
            <tr class="stmt-strong"><td colspan="3">Total Input VAT</td><td class="text-end">{{ number_format($totalInput, 2) }}</td></tr>
            <tr class="stmt-strong"><td colspan="3">Total Output VAT</td><td class="text-end">{{ number_format($totalOutput, 2) }}</td></tr>
            <tr class="stmt-strong"><td colspan="3">Net Tax {{ $netTax >= 0 ? '(Payable)' : '(Refundable)' }}</td><td class="text-end">{{ number_format(abs($netTax), 2) }}</td></tr>
            </tfoot>
        </table>

        <div class="stmt-signatures">
            <table class="stmt-meta">
                <tr>
                    <td>Prepared By: _________________</td>
                    <td>Verified By: _________________</td>
                    <td>Approved By: _________________</td>
                </tr>
            </table>
        </div>
    </div>

    @include('includes.report-print-css', ['orientation' => 'portrait'])

    <style>
        .tabular-nums { font-variant-numeric: tabular-nums; }

        @media print {
            .card { border: 1px solid #dee2e6 !important; box-shadow: none !important; }
        }
    </style>
</div>
