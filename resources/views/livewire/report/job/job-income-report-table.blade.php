<div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                    <th class="border-0" style="width: 40px;"></th>
                    <th class="ps-2 border-0">Job No</th>
                    <th class="border-0">Date</th>
                    <th class="border-0">Customer</th>
                    <th class="border-0">Activity</th>
                    <th class="text-center border-0">Invoices</th>
                    <th class="text-end border-0">Approved Income</th>
                    <th class="text-end border-0">Draft Income</th>
                    <th class="text-end border-0">Job Total</th>
                    <th class="text-center pe-4 border-0">Status</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                @if(isset($jobIncomeReportData['jobs']) && count($jobIncomeReportData['jobs']) > 0)
                    @foreach($jobIncomeReportData['jobs'] as $index => $job)
                        @php
                            $collapseId = 'jir-details-' . $index;
                            $s = $job['status'] ?? '';
                            $badgeClass = match(true) {
                                $s == 'draft'     => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                                $s == 'active'    => 'bg-primary-subtle text-primary border-primary-subtle',
                                $s == 'completed' => 'bg-success-subtle text-success border-success-subtle',
                                $s == 'cancelled' => 'bg-danger-subtle text-danger border-danger-subtle',
                                default           => 'bg-light text-muted border',
                            };
                        @endphp

                        {{-- Job Summary Row --}}
                        <tr wire:key="jir-job-{{ $index }}" class="jir-summary-row">
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 jir-toggle-btn"
                                        data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}"
                                        aria-expanded="false" aria-controls="{{ $collapseId }}"
                                        title="Show details">
                                    <i class="bi bi-chevron-right jir-toggle-icon text-muted"></i>
                                </button>
                            </td>
                            <td class="ps-2">
                                <span class="fw-bold text-dark">{{ $job['job_number'] }}</span>
                            </td>
                            <td class="small text-muted">{{ \Carbon\Carbon::parse($job['job_date'])->format('d M Y') }}</td>
                            <td class="small">{{ $job['customer'] }}</td>
                            <td class="small">{{ $job['activity'] }}</td>
                            <td class="text-center small">
                                <span class="badge rounded-pill bg-light text-dark border">{{ $job['invoice_count'] }}</span>
                            </td>
                            <td class="text-end tabular-nums small text-success">{{ number_format($job['approved_income'], 2) }}</td>
                            <td class="text-end tabular-nums small text-warning-emphasis">{{ number_format($job['draft_income'], 2) }}</td>
                            <td class="text-end fw-bold tabular-nums">{{ number_format($job['total_income'], 2) }}</td>
                            <td class="text-center pe-4">
                                <span class="badge rounded-pill px-2 py-1 border {{ $badgeClass }}">{{ ucfirst($s ?: '—') }}</span>
                            </td>
                        </tr>

                        {{-- Collapsible Details Row --}}
                        <tr class="collapse-details-row">
                            <td colspan="10" class="p-0 border-0">
                                <div class="collapse" id="{{ $collapseId }}">
                                    <div class="bg-light-subtle px-4 py-3">
                                        <table class="table table-sm mb-0 bg-transparent">
                                            <thead>
                                                <tr class="small text-muted text-uppercase">
                                                    <th class="border-0">Invoice No</th>
                                                    <th class="border-0">Invoice Date</th>
                                                    <th class="border-0">Description</th>
                                                    <th class="text-end border-0">Amount</th>
                                                    <th class="text-center border-0">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($job['invoice_details'] as $dIndex => $detail)
                                                    @php
                                                        $lineStatus = match((int) ($detail['invoice_status'] ?? 0)) {
                                                            3 => ['label' => 'Approved', 'class' => 'bg-success-subtle text-success border-success-subtle'],
                                                            1 => ['label' => 'Draft', 'class' => 'bg-secondary-subtle text-secondary border-secondary-subtle'],
                                                            default => ['label' => '—', 'class' => 'bg-light text-muted border'],
                                                        };
                                                    @endphp
                                                    <tr wire:key="jir-{{ $job['job_number'] }}-{{ $dIndex }}">
                                                        <td class="small">{{ $detail['invoice_number'] }}</td>
                                                        <td class="small text-muted">{{ \Carbon\Carbon::parse($detail['invoice_date'])->format('d M Y') }}</td>
                                                        <td class="small">{{ $detail['description'] }}</td>
                                                        <td class="text-end tabular-nums small">{{ number_format($detail['amount'], 2) }}</td>
                                                        <td class="text-center">
                                                            <span class="badge rounded-pill px-2 py-1 border {{ $lineStatus['class'] }}">{{ $lineStatus['label'] }}</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
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
                    <td colspan="8" class="ps-2 py-3">Grand Total</td>
                    <td class="text-end tabular-nums text-pr">{{ number_format($jobIncomeReportData['total_income'], 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    <style>
        .jir-toggle-btn[aria-expanded="true"] .jir-toggle-icon {
            transform: rotate(90deg);
        }
        .jir-toggle-icon {
            display: inline-block;
            transition: transform 0.2s ease;
        }
        .jir-summary-row:hover {
            background-color: var(--jir-light, #f0f9ff);
        }
    </style>
</div>
