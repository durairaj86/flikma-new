@section('page-title', 'Report Index')

<x-app-layout>
    <div class="container-fluid px-4 py-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h4 fw-bold text-dark mb-1">Reports Overview</h1>
                <p class="text-muted small mb-0">Select a report below to view detailed data and analytics.</p>
            </div>
            <i class="bi bi-bar-chart-line h2 text-muted opacity-25"></i>
        </div>

        <div class="row g-4">
            @php
                $reportGroups = [
                    [
                        'title' => 'Job Reports',
                        'icon' => 'bi-briefcase',
                        'color' => 'primary',
                        'reports' => [
                            ['url' => '/reports/job-report', 'name' => 'Job Report', 'desc' => 'General overview of all active and completed jobs.'],
                            ['url' => '/reports/job-balance-report', 'name' => 'Job Balance Report', 'desc' => 'Outstanding balances and payment statuses per job.'],
                            ['url' => '/reports/job-income-report', 'name' => 'Job Income Report', 'desc' => 'Analysis of revenue generated from specific job categories.'],
                        ]
                    ],
                    [
                        'title' => 'Operations Reports',
                        'icon' => 'bi-cart-check',
                        'color' => 'success',
                        'reports' => [
                            ['url' => '/reports/sale-report', 'name' => 'Sales Report', 'desc' => 'Daily, weekly, and monthly sales transaction summaries.'],
                        ]
                    ],
                    [
                        'title' => 'Finance Reports',
                        'icon' => 'bi-bank',
                        'color' => 'info',
                        'reports' => [
                            ['url' => '/reports/trial-balance', 'name' => 'Trial Balance', 'desc' => 'Mathematical verification of ledger balances.'],
                            ['url' => '/reports/balance-sheet', 'name' => 'Balance Sheet', 'desc' => 'Snapshot of company assets, liabilities, and equity.'],
                            ['url' => '/reports/profit-and-loss', 'name' => 'Profit & Loss', 'desc' => 'Revenue and expense summary for a specific period.'],
                            ['url' => '/reports/general-ledger', 'name' => 'General Ledger', 'desc' => 'Complete record of all financial transactions.'],
                        ]
                    ],
                    [
                        'title' => 'Tax Compliance',
                        'icon' => 'bi-percent',
                        'color' => 'warning',
                        'reports' => [
                            ['url' => '/reports/tax-summary', 'name' => 'Tax Summary', 'desc' => 'High-level overview of total Input vs Output tax.'],
                            ['url' => '/reports/input-tax', 'name' => 'Input Tax', 'desc' => 'Detailed list of tax paid on purchases (Recoverable).'],
                            ['url' => '/reports/output-tax', 'name' => 'Output Tax', 'desc' => 'Detailed list of tax collected on sales (Payable).'],
                        ]
                    ],
                ];
            @endphp

            @foreach($reportGroups as $group)
                <div class="col-12 col-xl-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white py-3 border-bottom-0">
                            <div class="d-flex align-items-center">
                                <div class="bg-{{ $group['color'] }} bg-opacity-10 p-2 rounded me-3">
                                    <i class="bi {{ $group['icon'] }} text-{{ $group['color'] }} fs-5"></i>
                                </div>
                                <h5 class="mb-0 fw-bold">{{ $group['title'] }}</h5>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="list-group list-group-flush">
                                @foreach($group['reports'] as $report)
                                    <a href="{{ url($report['url']) }}" class="list-group-item list-group-item-action border-0 px-0 py-3 d-flex align-items-start transition-all">
                                        <div class="me-3 mt-1">
                                            <i class="bi bi-file-earmark-text text-muted"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $report['name'] }}</div>
                                            <div class="text-muted small">{{ $report['desc'] }}</div>
                                        </div>
                                        <i class="bi bi-chevron-right ms-auto text-muted small align-self-center"></i>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <style>
        .transition-all {
            transition: all 0.2s ease-in-out;
        }
        .list-group-item-action:hover {
            background-color: #f8f9fa !important;
            transform: translateX(5px);
        }
        .card {
            border-radius: 12px;
        }
    </style>
</x-app-layout>
