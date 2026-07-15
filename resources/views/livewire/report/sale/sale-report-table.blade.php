<div>
    <div class="table-responsive d-print-none">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                    <th class="ps-4 border-0">Invoice Number</th>
                    <th class="border-0">Date</th>
                    <th class="border-0">Customer</th>
                    <th class="border-0">Job Number</th>
                    <th class="border-0">Currency</th>
                    <th class="text-end border-0">Amount</th>
                    <th class="text-end border-0">Tax</th>
                    <th class="text-end border-0">Total</th>
                    <th class="text-center pe-4 border-0">Status</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                @if(isset($saleReportData['sales']) && count($saleReportData['sales']) > 0)
                    @foreach($saleReportData['sales'] as $sale)
                        <tr wire:key="sr-{{ $sale->id ?? $loop->index }}">
                            <td class="ps-4">
                                <span class="fw-bold text-dark">{{ $sale->invoice_number ?? $sale->row_no }}</span>
                            </td>
                            <td class="small text-muted">{{ \Carbon\Carbon::parse($sale->invoice_date)->format('d M Y') }}</td>
                            <td class="small">{{ $sale->customer->name ?? $sale->customer->name_en ?? 'N/A' }}</td>
                            <td class="small text-muted">{{ $sale->job->row_no ?? $sale->job->job_no ?? 'N/A' }}</td>
                            <td class="small">{{ $sale->currency ?? '—' }}</td>
                            <td class="text-end tabular-nums small">{{ number_format($sale->sub_total ?? 0, 2) }}</td>
                            <td class="text-end tabular-nums small">{{ number_format($sale->tax_total ?? 0, 2) }}</td>
                            <td class="text-end tabular-nums fw-bold">{{ number_format($sale->grand_total ?? 0, 2) }}</td>
                            <td class="text-center pe-4">
                                @php
                                    $status = $sale->status ?? '';
                                    $badgeClass = match(true) {
                                        $status == 'draft' || $status == 1 => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                                        $status == 'approved' || $status == 3 => 'bg-success-subtle text-success border-success-subtle',
                                        $status == 'cancelled' || $status == 4 => 'bg-danger-subtle text-danger border-danger-subtle',
                                        default => 'bg-light text-muted border',
                                    };
                                    $label = match(true) {
                                        $status == 'draft' || $status == 1 => 'Draft',
                                        $status == 'approved' || $status == 3 => 'Approved',
                                        $status == 'cancelled' || $status == 4 => 'Cancelled',
                                        default => ucfirst($sale->status ?? '—'),
                                    };
                                @endphp
                                <span class="badge rounded-pill px-2 py-1 border {{ $badgeClass }}">{{ $label }}</span>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <div class="bg-light rounded-circle p-4 d-inline-block mb-3">
                                <i class="bi bi-receipt h2 text-muted"></i>
                            </div>
                            <div class="small">No sales data found for the selected period.</div>
                        </td>
                    </tr>
                @endif
            </tbody>
            @if(isset($saleReportData['sales']) && count($saleReportData['sales']) > 0)
            <tfoot class="bg-light border-top-2">
                <tr class="fw-bold">
                    <td colspan="5" class="ps-4 py-3">Totals</td>
                    <td class="text-end tabular-nums">{{ number_format($saleReportData['total_amount'] ?? 0, 2) }}</td>
                    <td class="text-end tabular-nums">{{ number_format($saleReportData['total_tax'] ?? 0, 2) }}</td>
                    <td class="text-end tabular-nums text-pr">{{ number_format($saleReportData['total_grand'] ?? 0, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- Bank-statement style layout: used for Print and PDF export only --}}
    <div id="sr-print" class="stmt-print d-none d-print-block"
         data-pdf-filename="SaleReport-{{ $startDate ?? '' }}-{{ $endDate ?? '' }}.pdf">

        <table class="stmt-meta">
            <tr>
                <td>
                    <div class="stmt-company">{{ optional(authUserCompany())->name ?? config('app.name') }}</div>
                </td>
                <td class="text-end">
                    <div class="stmt-title">SALE REPORT</div>
                    <div class="stmt-sub">Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</div>
                    <div class="stmt-sub">Generated: {{ now()->format('d M Y H:i') }}</div>
                </td>
            </tr>
        </table>

        <table class="stmt-table">
            <thead>
            <tr>
                <th>Invoice Number</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Job Number</th>
                <th class="text-end">Amount</th>
                <th class="text-end">Tax</th>
                <th class="text-end">Total</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            @forelse($saleReportData['sales'] as $sale)
                <tr>
                    <td>{{ $sale->invoice_number ?? $sale->row_no }}</td>
                    <td>{{ \Carbon\Carbon::parse($sale->invoice_date)->format('d M Y') }}</td>
                    <td>{{ $sale->customer->name ?? 'N/A' }}</td>
                    <td>{{ $sale->job->row_no ?? 'N/A' }}</td>
                    <td class="text-end">{{ number_format($sale->sub_total ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($sale->tax_total ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($sale->grand_total ?? 0, 2) }}</td>
                    <td>{{ ucfirst($sale->status ?? '—') }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">No sales data found for the selected period.</td></tr>
            @endforelse
            </tbody>
            <tfoot>
            <tr class="stmt-strong">
                <td colspan="4">Totals</td>
                <td class="text-end">{{ number_format($saleReportData['total_amount'] ?? 0, 2) }}</td>
                <td class="text-end">{{ number_format($saleReportData['total_tax'] ?? 0, 2) }}</td>
                <td class="text-end">{{ number_format($saleReportData['total_grand'] ?? 0, 2) }}</td>
                <td></td>
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
