<div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Job Number</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Activity</th>
                    <th class="text-end">Income</th>
                    <th class="text-end">Expense</th>
                    <th class="text-end">Profit</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($jobBalanceReportData['jobs']) && count($jobBalanceReportData['jobs']) > 0)
                    @foreach($jobBalanceReportData['jobs'] as $job)
                        <tr>
                            <td>{{ $job['job_number'] }}</td>
                            <td>{{ $job['job_date'] }}</td>
                            <td>{{ $job['customer'] }}</td>
                            <td>{{ $job['activity'] }}</td>
                            <td class="text-end">{{ number_format($job['income'], 2) }}</td>
                            <td class="text-end">{{ number_format($job['expense'], 2) }}</td>
                            <td class="text-end {{ $job['profit'] < 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($job['profit'], 2) }}
                            </td>
                            <td>
                                @if($job['status'] == 'draft')
                                    <span class="badge bg-secondary">Draft</span>
                                @elseif($job['status'] == 'active')
                                    <span class="badge bg-primary">Active</span>
                                @elseif($job['status'] == 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($job['status'] == 'cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                @else
                                    <span class="badge bg-info">{{ $job['status'] }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="8" class="text-center">No data available</td>
                    </tr>
                @endif
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th colspan="4" class="text-end">Total</th>
                    <th class="text-end">{{ isset($jobBalanceReportData['total_income']) ? number_format($jobBalanceReportData['total_income'], 2) : '0.00' }}</th>
                    <th class="text-end">{{ isset($jobBalanceReportData['total_expense']) ? number_format($jobBalanceReportData['total_expense'], 2) : '0.00' }}</th>
                    <th class="text-end {{ isset($jobBalanceReportData['total_profit']) && $jobBalanceReportData['total_profit'] < 0 ? 'text-danger' : 'text-success' }}">
                        {{ isset($jobBalanceReportData['total_profit']) ? number_format($jobBalanceReportData['total_profit'], 2) : '0.00' }}
                    </th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
