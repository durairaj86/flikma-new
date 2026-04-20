<div>
    <div class="table-responsive">
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
</div>
