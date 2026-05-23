<div>
    <div class="table-responsive">
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
</div>
