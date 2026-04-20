<div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Job Number</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Activity</th>
                    <th>Invoice Number</th>
                    <th>Invoice Date</th>
                    <th>Description</th>
                    <th class="text-end">Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($jobIncomeReportData['jobs']) && count($jobIncomeReportData['jobs']) > 0)
                    @foreach($jobIncomeReportData['jobs'] as $job)
                        @php $firstRow = true; $rowCount = count($job['invoice_details']); @endphp

                        @foreach($job['invoice_details'] as $index => $detail)
                            <tr>
                                @if($firstRow)
                                    <td rowspan="{{ $rowCount }}">{{ $job['job_number'] }}</td>
                                    <td rowspan="{{ $rowCount }}">{{ $job['job_date'] }}</td>
                                    <td rowspan="{{ $rowCount }}">{{ $job['customer'] }}</td>
                                    <td rowspan="{{ $rowCount }}">{{ $job['activity'] }}</td>
                                    @php $firstRow = false; @endphp
                                @endif
                                <td>{{ $detail['invoice_number'] }}</td>
                                <td>{{ $detail['invoice_date'] }}</td>
                                <td>{{ $detail['description'] }}</td>
                                <td class="text-end">{{ number_format($detail['amount'], 2) }}</td>

                                @if($index === 0)
                                    <td rowspan="{{ $rowCount }}">
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
                                @endif
                            </tr>
                        @endforeach

                        <!-- Job Total Row -->
                        <tr class="table-light">
                            <td colspan="7" class="text-end fw-bold">Job Total:</td>
                            <td class="text-end fw-bold">{{ number_format($job['total_income'], 2) }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="9" class="text-center">No data available</td>
                    </tr>
                @endif
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th colspan="7" class="text-end">Grand Total:</th>
                    <th class="text-end">{{ isset($jobIncomeReportData['total_income']) ? number_format($jobIncomeReportData['total_income'], 2) : '0.00' }}</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
