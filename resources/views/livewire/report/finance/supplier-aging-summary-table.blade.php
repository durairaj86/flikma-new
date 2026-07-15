<div>
    <div class="table-responsive d-print-none">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th colspan="9" class="text-center">Supplier Aging Summary Report</th>
                </tr>
                <tr>
                    <th>Supplier ID</th>
                    <th>Supplier Name</th>
                    <th class="text-end">Current</th>
                    <th class="text-end">1-30 Days</th>
                    <th class="text-end">31-60 Days</th>
                    <th class="text-end">61-90 Days</th>
                    <th class="text-end">91-120 Days</th>
                    <th class="text-end">Over 120 Days</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($agingData['suppliers']) && count($agingData['suppliers']) > 0)
                    @foreach($agingData['suppliers'] as $supplier)
                        <tr>
                            <td>{{ $supplier['supplier_code'] }}</td>
                            <td>{{ $supplier['supplier_name'] }}</td>
                            <td class="text-end">{{ number_format($supplier['current'], 2) }}</td>
                            <td class="text-end">{{ number_format($supplier['days_1_30'], 2) }}</td>
                            <td class="text-end">{{ number_format($supplier['days_31_60'], 2) }}</td>
                            <td class="text-end">{{ number_format($supplier['days_61_90'], 2) }}</td>
                            <td class="text-end">{{ number_format($supplier['days_91_120'], 2) }}</td>
                            <td class="text-end">{{ number_format($supplier['days_over_120'], 2) }}</td>
                            <td class="text-end">{{ number_format($supplier['total'], 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="9" class="text-center">No suppliers with outstanding invoices found</td>
                    </tr>
                @endif
            </tbody>
            <tfoot class="table-dark">
                <tr>
                    <th colspan="2" class="text-end">Total</th>
                    <th class="text-end">{{ isset($agingData['totals']['current']) ? number_format($agingData['totals']['current'], 2) : '0.00' }}</th>
                    <th class="text-end">{{ isset($agingData['totals']['days_1_30']) ? number_format($agingData['totals']['days_1_30'], 2) : '0.00' }}</th>
                    <th class="text-end">{{ isset($agingData['totals']['days_31_60']) ? number_format($agingData['totals']['days_31_60'], 2) : '0.00' }}</th>
                    <th class="text-end">{{ isset($agingData['totals']['days_61_90']) ? number_format($agingData['totals']['days_61_90'], 2) : '0.00' }}</th>
                    <th class="text-end">{{ isset($agingData['totals']['days_91_120']) ? number_format($agingData['totals']['days_91_120'], 2) : '0.00' }}</th>
                    <th class="text-end">{{ isset($agingData['totals']['days_over_120']) ? number_format($agingData['totals']['days_over_120'], 2) : '0.00' }}</th>
                    <th class="text-end">{{ isset($agingData['totals']['grand_total']) ? number_format($agingData['totals']['grand_total'], 2) : '0.00' }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Bank-statement style layout: used for Print and PDF export only --}}
    <div id="sas-print" class="stmt-print d-none d-print-block"
         data-pdf-filename="SupplierAgingSummary-{{ $asOfDate ?? '' }}.pdf">

        <table class="stmt-meta">
            <tr>
                <td>
                    <div class="stmt-company">{{ optional(authUserCompany())->name ?? config('app.name') }}</div>
                </td>
                <td class="text-end">
                    <div class="stmt-title">SUPPLIER AGING SUMMARY</div>
                    <div class="stmt-sub">As of: {{ \Carbon\Carbon::parse($asOfDate)->format('d M Y') }}</div>
                    <div class="stmt-sub">Generated: {{ now()->format('d M Y H:i') }} &nbsp;|&nbsp; Currency: SAR</div>
                </td>
            </tr>
        </table>

        <table class="stmt-table">
            <thead>
            <tr>
                <th>Supplier Code</th>
                <th>Supplier Name</th>
                <th class="text-end">Current</th>
                <th class="text-end">1-30</th>
                <th class="text-end">31-60</th>
                <th class="text-end">61-90</th>
                <th class="text-end">91-120</th>
                <th class="text-end">&gt;120</th>
                <th class="text-end">Total</th>
            </tr>
            </thead>
            <tbody>
            @forelse($agingData['suppliers'] as $supplier)
                <tr>
                    <td>{{ $supplier['supplier_code'] }}</td>
                    <td>{{ $supplier['supplier_name'] }}</td>
                    <td class="text-end">{{ $supplier['current'] > 0 ? number_format($supplier['current'], 2) : '' }}</td>
                    <td class="text-end">{{ $supplier['days_1_30'] > 0 ? number_format($supplier['days_1_30'], 2) : '' }}</td>
                    <td class="text-end">{{ $supplier['days_31_60'] > 0 ? number_format($supplier['days_31_60'], 2) : '' }}</td>
                    <td class="text-end">{{ $supplier['days_61_90'] > 0 ? number_format($supplier['days_61_90'], 2) : '' }}</td>
                    <td class="text-end">{{ $supplier['days_91_120'] > 0 ? number_format($supplier['days_91_120'], 2) : '' }}</td>
                    <td class="text-end">{{ $supplier['days_over_120'] > 0 ? number_format($supplier['days_over_120'], 2) : '' }}</td>
                    <td class="text-end stmt-strong">{{ number_format($supplier['total'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center">No suppliers with outstanding invoices found.</td></tr>
            @endforelse
            </tbody>
            <tfoot>
            <tr class="stmt-strong">
                <td colspan="2">Total</td>
                <td class="text-end">{{ number_format($agingData['totals']['current'] ?? 0, 2) }}</td>
                <td class="text-end">{{ number_format($agingData['totals']['days_1_30'] ?? 0, 2) }}</td>
                <td class="text-end">{{ number_format($agingData['totals']['days_31_60'] ?? 0, 2) }}</td>
                <td class="text-end">{{ number_format($agingData['totals']['days_61_90'] ?? 0, 2) }}</td>
                <td class="text-end">{{ number_format($agingData['totals']['days_91_120'] ?? 0, 2) }}</td>
                <td class="text-end">{{ number_format($agingData['totals']['days_over_120'] ?? 0, 2) }}</td>
                <td class="text-end">{{ number_format($agingData['totals']['grand_total'] ?? 0, 2) }}</td>
            </tr>
            </tfoot>
        </table>
    </div>

    @include('includes.report-print-css')
</div>
