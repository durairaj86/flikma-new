<div>
    <div class="table-responsive d-print-none">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                    <th class="ps-4 border-0">{{ __('Invoice Number') }}</th>
                    <th class="border-0">{{ __('Date') }}</th>
                    <th class="border-0">{{ __('Customer') }}</th>
                    <th class="border-0">{{ __('Job Number') }}</th>
                    <th class="border-0">{{ __('Currency') }}</th>
                    <th class="text-end border-0">{{ __('Amount') }}</th>
                    <th class="text-end border-0">{{ __('Tax') }}</th>
                    <th class="text-end border-0">{{ __('Total') }}</th>
                    <th class="text-center pe-4 border-0">{{ __('Status') }}</th>
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
                            <td class="small">{{ $sale->customer->name ?? $sale->customer->name_en ?? __('N/A') }}</td>
                            <td class="small text-muted">{{ $sale->job->row_no ?? $sale->job->job_no ?? __('N/A') }}</td>
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
                                        $status == 'draft' || $status == 1 => __('Draft'),
                                        $status == 'approved' || $status == 3 => __('Approved'),
                                        $status == 'cancelled' || $status == 4 => __('Cancelled'),
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
                            <div class="small">{{ __('No sales data found for the selected period.') }}</div>
                        </td>
                    </tr>
                @endif
            </tbody>
            @if(isset($saleReportData['sales']) && count($saleReportData['sales']) > 0)
            <tfoot class="bg-light border-top-2">
                <tr class="fw-bold">
                    <td colspan="5" class="ps-4 py-3">{{ __('Totals') }}</td>
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
                    <div class="stmt-title">{{ __('SALE REPORT') }}</div>
                    <div class="stmt-sub">{{ __('Period:') }} {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</div>
                    <div class="stmt-sub">{{ __('Generated:') }} {{ now()->format('d M Y H:i') }}</div>
                </td>
            </tr>
        </table>

        <table class="stmt-table">
            <thead>
            <tr>
                <th>{{ __('Invoice Number') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Customer') }}</th>
                <th>{{ __('Job Number') }}</th>
                <th class="text-end">{{ __('Amount') }}</th>
                <th class="text-end">{{ __('Tax') }}</th>
                <th class="text-end">{{ __('Total') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($saleReportData['sales'] as $sale)
                <tr>
                    <td>{{ $sale->invoice_number ?? $sale->row_no }}</td>
                    <td>{{ \Carbon\Carbon::parse($sale->invoice_date)->format('d M Y') }}</td>
                    <td>{{ $sale->customer->name ?? __('N/A') }}</td>
                    <td>{{ $sale->job->row_no ?? __('N/A') }}</td>
                    <td class="text-end">{{ number_format($sale->sub_total ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($sale->tax_total ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($sale->grand_total ?? 0, 2) }}</td>
                    <td>{{ ucfirst($sale->status ?? '—') }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">{{ __('No sales data found for the selected period.') }}</td></tr>
            @endforelse
            </tbody>
            <tfoot>
            <tr class="stmt-strong">
                <td colspan="4">{{ __('Totals') }}</td>
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
                    <td>{{ __('Prepared By:') }} _________________</td>
                    <td>{{ __('Verified By:') }} _________________</td>
                    <td>{{ __('Approved By:') }} _________________</td>
                </tr>
            </table>
        </div>
    </div>

    @include('includes.report-print-css', ['orientation' => 'landscape'])
</div>
