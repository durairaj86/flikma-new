<div>
    <div class="table-responsive d-print-none">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                    <th class="ps-4 border-0">Job No</th>
                    <th class="border-0">Date</th>
                    <th class="border-0">Customer</th>
                    <th class="border-0">Activity</th>
                    <th class="text-end border-0">Income</th>
                    <th class="text-end border-0">Expense</th>
                    <th class="text-end border-0">Profit / Loss</th>
                    <th class="text-center pe-4 border-0">Status</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                @if(isset($jobBalanceReportData['jobs']) && count($jobBalanceReportData['jobs']) > 0)
                    @foreach($jobBalanceReportData['jobs'] as $job)
                        <tr wire:key="jbr-{{ $job['job_number'] }}">
                            <td class="ps-4">
                                <span class="fw-bold text-dark">{{ $job['job_number'] }}</span>
                            </td>
                            <td class="small text-muted">{{ \Carbon\Carbon::parse($job['job_date'])->format('d M Y') }}</td>
                            <td class="small">{{ $job['customer'] }}</td>
                            <td class="small">{{ $job['activity'] }}</td>
                            <td class="text-end tabular-nums text-pr fw-medium">{{ number_format($job['income'], 2) }}</td>
                            <td class="text-end tabular-nums text-danger">{{ number_format($job['expense'], 2) }}</td>
                            <td class="text-end tabular-nums fw-bold {{ $job['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($job['profit'], 2) }}
                            </td>
                            <td class="text-center pe-4">
                                @php
                                    $s = $job['status'] ?? '';
                                    $badgeClass = match(true) {
                                        $s == 'draft'     => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                                        $s == 'active'    => 'bg-primary-subtle text-primary border-primary-subtle',
                                        $s == 'completed' => 'bg-success-subtle text-success border-success-subtle',
                                        $s == 'cancelled' => 'bg-danger-subtle text-danger border-danger-subtle',
                                        default           => 'bg-light text-muted border',
                                    };
                                @endphp
                                <span class="badge rounded-pill px-2 py-1 border {{ $badgeClass }}">{{ ucfirst($s ?: '—') }}</span>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <div class="bg-light rounded-circle p-4 d-inline-block mb-3">
                                <i class="bi bi-balance-scale h2 text-muted"></i>
                            </div>
                            <div class="small">No balance data found for the selected period.</div>
                        </td>
                    </tr>
                @endif
            </tbody>
            @if(isset($jobBalanceReportData['jobs']) && count($jobBalanceReportData['jobs']) > 0)
            <tfoot class="bg-light border-top-2">
                <tr class="fw-bold">
                    <td colspan="4" class="ps-4 py-3">Totals</td>
                    <td class="text-end tabular-nums text-pr">{{ number_format($jobBalanceReportData['total_income'] ?? 0, 2) }}</td>
                    <td class="text-end tabular-nums text-danger">{{ number_format($jobBalanceReportData['total_expense'] ?? 0, 2) }}</td>
                    <td class="text-end tabular-nums {{ ($jobBalanceReportData['total_profit'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($jobBalanceReportData['total_profit'] ?? 0, 2) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- Bank-statement style layout: used for Print and PDF export only --}}
    <div id="jbr-print" class="stmt-print d-none d-print-block"
         data-pdf-filename="JobBalanceReport-{{ $startDate ?? '' }}-{{ $endDate ?? '' }}.pdf">

        <table class="stmt-meta">
            <tr>
                <td>
                    <div class="stmt-company">{{ optional(authUserCompany())->name ?? config('app.name') }}</div>
                </td>
                <td class="text-end">
                    <div class="stmt-title">JOB BALANCE REPORT</div>
                    <div class="stmt-sub">Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</div>
                    <div class="stmt-sub">Generated: {{ now()->format('d M Y H:i') }} &nbsp;|&nbsp; Currency: SAR</div>
                </td>
            </tr>
        </table>

        <table class="stmt-table">
            <thead>
            <tr>
                <th>Job No</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Activity</th>
                <th class="text-end">Income</th>
                <th class="text-end">Expense</th>
                <th class="text-end">Profit / Loss</th>
            </tr>
            </thead>
            <tbody>
            @forelse($jobBalanceReportData['jobs'] as $job)
                <tr>
                    <td>{{ $job['job_number'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($job['job_date'])->format('d M Y') }}</td>
                    <td>{{ $job['customer'] }}</td>
                    <td>{{ $job['activity'] }}</td>
                    <td class="text-end">{{ number_format($job['income'], 2) }}</td>
                    <td class="text-end">{{ number_format($job['expense'], 2) }}</td>
                    <td class="text-end">{{ number_format($job['profit'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">No balance data found for the selected period.</td></tr>
            @endforelse
            </tbody>
            <tfoot>
            <tr class="stmt-strong">
                <td colspan="4">Total</td>
                <td class="text-end">{{ number_format($jobBalanceReportData['total_income'] ?? 0, 2) }}</td>
                <td class="text-end">{{ number_format($jobBalanceReportData['total_expense'] ?? 0, 2) }}</td>
                <td class="text-end">{{ number_format($jobBalanceReportData['total_profit'] ?? 0, 2) }}</td>
            </tr>
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

    @include('includes.report-print-css', ['orientation' => 'landscape'])
</div>
