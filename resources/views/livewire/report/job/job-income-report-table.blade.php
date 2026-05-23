<div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                    <th class="ps-4 border-0">Job No</th>
                    <th class="border-0">Date</th>
                    <th class="border-0">Customer</th>
                    <th class="border-0">Activity</th>
                    <th class="border-0">Invoice No</th>
                    <th class="border-0">Invoice Date</th>
                    <th class="border-0">Description</th>
                    <th class="text-end border-0">Amount</th>
                    <th class="text-center pe-4 border-0">Status</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                @if(isset($jobIncomeReportData['jobs']) && count($jobIncomeReportData['jobs']) > 0)
                    @foreach($jobIncomeReportData['jobs'] as $job)
                        @php
                            $firstRow = true;
                            $rowCount = count($job['invoice_details']);
                        @endphp

                        @foreach($job['invoice_details'] as $index => $detail)
                            <tr wire:key="jir-{{ $job['job_number'] }}-{{ $index }}">
                                @if($firstRow)
                                    <td class="ps-4" rowspan="{{ $rowCount }}">
                                        <span class="fw-bold text-dark">{{ $job['job_number'] }}</span>
                                    </td>
                                    <td rowspan="{{ $rowCount }}" class="small text-muted">{{ \Carbon\Carbon::parse($job['job_date'])->format('d M Y') }}</td>
                                    <td rowspan="{{ $rowCount }}" class="small">{{ $job['customer'] }}</td>
                                    <td rowspan="{{ $rowCount }}" class="small">{{ $job['activity'] }}</td>
                                    @php $firstRow = false; @endphp
                                @endif
                                <td class="small">{{ $detail['invoice_number'] }}</td>
                                <td class="small text-muted">{{ \Carbon\Carbon::parse($detail['invoice_date'])->format('d M Y') }}</td>
                                <td class="small">{{ $detail['description'] }}</td>
                                <td class="text-end tabular-nums">{{ number_format($detail['amount'], 2) }}</td>

                                @if($index === 0)
                                    <td class="text-center pe-4" rowspan="{{ $rowCount }}">
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
                                @endif
                            </tr>
                        @endforeach

                        {{-- Job Total Row --}}
                        <tr class="bg-light-subtle">
                            <td colspan="7" class="text-end fw-bold small text-muted ps-4 py-2">
                                <i class="bi bi-arrow-return-right me-1"></i>Job Total:
                            </td>
                            <td class="text-end fw-bold tabular-nums py-2">{{ number_format($job['total_income'], 2) }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <div class="bg-light rounded-circle p-4 d-inline-block mb-3">
                                <i class="bi bi-cash-stack h2 text-muted"></i>
                            </div>
                            <div class="small">No income data found for the selected period.</div>
                        </td>
                    </tr>
                @endif
            </tbody>
            @if(isset($jobIncomeReportData['total_income']) && $jobIncomeReportData['total_income'] > 0)
            <tfoot class="bg-light border-top-2">
                <tr class="fw-bold">
                    <td colspan="7" class="ps-4 py-3">Grand Total</td>
                    <td class="text-end tabular-nums text-pr">{{ number_format($jobIncomeReportData['total_income'], 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
