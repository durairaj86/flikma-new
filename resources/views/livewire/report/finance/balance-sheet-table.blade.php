<div>
    <div class="row g-5 d-print-none">
        <div class="col-lg-6">
            <h5 class="fw-bold text-primary border-bottom border-2 pb-2 mb-3 text-uppercase small ls-1">
                Assets
            </h5>

            <div class="mb-4">
                @if(isset($balanceSheetData['assets']) && count($balanceSheetData['assets']) > 0)
                    @foreach($balanceSheetData['assets'] as $account)
                        <div class="d-flex justify-content-between py-2 border-bottom-dashed">
                            <div>
                                <span class="text-secondary small me-2">{{ $account['account_code'] }}</span>
                                <span>{{ $account['account_name'] }}</span>
                            </div>
                            <span class="tabular-nums fw-medium">{{ number_format($account['balance'], 2) }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-3 text-muted small">No asset accounts with activity</div>
                @endif
            </div>

            <div class="d-flex justify-content-between py-3 px-3 bg-primary text-white rounded shadow-sm mt-auto">
                <span class="fw-bold mb-0">TOTAL ASSETS</span>
                <span class="fw-bold mb-0">{{ number_format($balanceSheetData['total_assets'] ?? 0, 2) }}</span>
            </div>
        </div>

        <div class="col-lg-6">
            <h5 class="fw-bold text-danger border-bottom border-2 pb-2 mb-3 text-uppercase small ls-1">
                Liabilities &amp; Equity
            </h5>

            <div class="mb-4">
                <div class="fw-bold small text-muted mb-2 text-uppercase ls-1">Liabilities</div>
                @if(isset($balanceSheetData['liabilities']) && count($balanceSheetData['liabilities']) > 0)
                    @foreach($balanceSheetData['liabilities'] as $account)
                        <div class="d-flex justify-content-between py-2 border-bottom-dashed">
                            <div>
                                <span class="text-secondary small me-2">{{ $account['account_code'] }}</span>
                                <span>{{ $account['account_name'] }}</span>
                            </div>
                            <span class="tabular-nums fw-medium">{{ number_format($account['balance'], 2) }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-2 text-muted small">No liability accounts</div>
                @endif
                <div class="d-flex justify-content-between py-2 fw-bold bg-light px-2 mt-2 rounded">
                    <span class="small">Total Liabilities</span>
                    <span>{{ number_format($balanceSheetData['total_liabilities'] ?? 0, 2) }}</span>
                </div>
            </div>

            <div class="mb-4 pt-2">
                <div class="fw-bold small text-muted mb-2 text-uppercase ls-1">Equity</div>
                @if(isset($balanceSheetData['equity']) && count($balanceSheetData['equity']) > 0)
                    @foreach($balanceSheetData['equity'] as $account)
                        <div class="d-flex justify-content-between py-2 border-bottom-dashed">
                            <div>
                                <span class="text-secondary small me-2">{{ $account['account_code'] }}</span>
                                <span>{{ $account['account_name'] }}</span>
                            </div>
                            <span class="tabular-nums fw-medium">{{ number_format($account['balance'], 2) }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-2 text-muted small">No equity accounts</div>
                @endif
                <div class="d-flex justify-content-between py-2 fw-bold bg-light px-2 mt-2 rounded">
                    <span class="small">Total Equity</span>
                    <span>{{ number_format($balanceSheetData['total_equity'] ?? 0, 2) }}</span>
                </div>
            </div>

            <div class="d-flex justify-content-between py-3 px-3 bg-dark text-white rounded shadow-sm mt-4">
                <span class="fw-bold mb-0">TOTAL LIABILITIES &amp; EQUITY</span>
                <span class="fw-bold mb-0">{{ number_format($balanceSheetData['total_liabilities_equity'] ?? 0, 2) }}</span>
            </div>

            @php
                $assets = round($balanceSheetData['total_assets'] ?? 0, 2);
                $liabEquity = round($balanceSheetData['total_liabilities_equity'] ?? 0, 2);
            @endphp

            @if($assets == $liabEquity && $assets != 0)
                <div class="mt-4 p-3 bg-success-subtle border border-success border-opacity-25 rounded text-success small d-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    Your Balance Sheet is perfectly balanced.
                </div>
            @elseif($assets != $liabEquity)
                <div class="mt-4 p-3 bg-danger-subtle border border-danger border-opacity-25 rounded text-danger small d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <div>
                        <strong>Out of Balance:</strong>
                        The difference is {{ number_format(abs($assets - $liabEquity), 2) }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Bank-statement style layout: used for Print and PDF export only --}}
    <div id="bs-print" class="stmt-print d-none d-print-block"
         data-pdf-filename="BalanceSheet-{{ $endDate ?? '' }}.pdf">

        <table class="stmt-meta">
            <tr>
                <td>
                    <div class="stmt-company">{{ optional(authUserCompany())->name ?? config('app.name') }}</div>
                </td>
                <td class="text-end">
                    <div class="stmt-title">BALANCE SHEET</div>
                    <div class="stmt-sub">As of: {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</div>
                    <div class="stmt-sub">Generated: {{ now()->format('d M Y H:i') }} &nbsp;|&nbsp; Currency: SAR</div>
                </td>
            </tr>
        </table>

        <table class="stmt-table">
            <thead>
            <tr><th colspan="2">ASSETS</th></tr>
            </thead>
            <tbody>
            @forelse($balanceSheetData['assets'] as $account)
                <tr>
                    <td>{{ $account['account_code'] }} {{ $account['account_name'] }}</td>
                    <td class="text-end">{{ number_format($account['balance'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="2" class="text-center">No asset accounts with activity</td></tr>
            @endforelse
            </tbody>
            <tfoot>
            <tr class="stmt-strong"><td>Total Assets</td><td class="text-end">{{ number_format($balanceSheetData['total_assets'] ?? 0, 2) }}</td></tr>
            </tfoot>
        </table>

        <table class="stmt-table">
            <thead>
            <tr><th colspan="2">LIABILITIES</th></tr>
            </thead>
            <tbody>
            @forelse($balanceSheetData['liabilities'] as $account)
                <tr>
                    <td>{{ $account['account_code'] }} {{ $account['account_name'] }}</td>
                    <td class="text-end">{{ number_format($account['balance'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="2" class="text-center">No liability accounts</td></tr>
            @endforelse
            </tbody>
            <tfoot>
            <tr class="stmt-strong"><td>Total Liabilities</td><td class="text-end">{{ number_format($balanceSheetData['total_liabilities'] ?? 0, 2) }}</td></tr>
            </tfoot>
        </table>

        <table class="stmt-table">
            <thead>
            <tr><th colspan="2">EQUITY</th></tr>
            </thead>
            <tbody>
            @forelse($balanceSheetData['equity'] as $account)
                <tr>
                    <td>{{ $account['account_code'] }} {{ $account['account_name'] }}</td>
                    <td class="text-end">{{ number_format($account['balance'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="2" class="text-center">No equity accounts</td></tr>
            @endforelse
            </tbody>
            <tfoot>
            <tr class="stmt-strong"><td>Total Equity</td><td class="text-end">{{ number_format($balanceSheetData['total_equity'] ?? 0, 2) }}</td></tr>
            <tr class="stmt-strong"><td>Total Liabilities &amp; Equity</td><td class="text-end">{{ number_format($balanceSheetData['total_liabilities_equity'] ?? 0, 2) }}</td></tr>
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
        .ls-1 { letter-spacing: 0.05em; }
        .border-bottom-dashed { border-bottom: 1px dashed #dee2e6; }
        .tabular-nums { font-variant-numeric: tabular-nums; }
        .bg-success-subtle { background-color: #dcfce7 !important; }
        .bg-danger-subtle { background-color: #fee2e2 !important; }

        @media print {
            .bg-primary, .bg-dark {
                background-color: transparent !important;
                color: black !important;
                border: 2px solid black !important;
                box-shadow: none !important;
            }
            .text-primary, .text-danger { color: black !important; }
            .col-lg-6 { width: 50% !important; float: left !important; }
            .row { display: block !important; }
        }
    </style>
</div>
