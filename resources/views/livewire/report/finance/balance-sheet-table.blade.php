<div>
<div class="container-fluid px-0 py-2">
    <div class="text-center mb-5">
        <h2 class="fw-bold text-dark mb-1">Balance Sheet</h2>
        <p class="text-muted small">As of {{ date('F d, Y') }}</p>
        <div class="d-print-none mt-3">
            <button onclick="window.print()" class="btn btn-outline-dark btn-sm px-4 shadow-sm">
                <i class="bi bi-printer me-2"></i>Print Report
            </button>
            <button class="btn btn-primary btn-sm px-4 shadow-sm">
                <i class="bi bi-file-earmark-pdf me-2"></i>Download PDF
            </button>
        </div>
    </div>

    <div class="row g-5">
        <div class="col-lg-6">
            <h5 class="fw-bold text-primary border-bottom border-2 pb-2 mb-3 text-uppercase small letter-spacing-1">
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
            <h5 class="fw-bold text-danger border-bottom border-2 pb-2 mb-3 text-uppercase small letter-spacing-1">
                Liabilities & Equity
            </h5>

            <div class="mb-4">
                <div class="fw-bold small text-muted mb-2 text-uppercase">Liabilities</div>
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
                <div class="fw-bold small text-muted mb-2 text-uppercase">Equity</div>
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
                <span class="fw-bold mb-0">TOTAL LIABILITIES & EQUITY</span>
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
</div>

<style>
    .letter-spacing-1 { letter-spacing: 1px; }
    .border-bottom-dashed { border-bottom: 1px dashed #dee2e6; }
    .tabular-nums { font-variant-numeric: tabular-nums; font-family: 'Courier New', Courier, monospace; }
    .bg-success-subtle { background-color: #dcfce7 !important; }
    .bg-danger-subtle { background-color: #fee2e2 !important; }

    @media print {
        .d-print-none { display: none !important; }
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
