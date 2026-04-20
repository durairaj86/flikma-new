<div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th colspan="10" class="text-center">Supplier Aging Report</th>
                </tr>
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Due Date</th>
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
                @if(isset($agingData['invoices']) && count($agingData['invoices']) > 0)
                    @foreach($agingData['invoices'] as $invoice)
                        <tr>
                            <td>{{ $invoice['invoice_no'] }}</td>
                            <td>{{ $invoice['invoice_date'] }}</td>
                            <td>{{ $invoice['due_date'] }}</td>
                            <td class="text-end">{{ number_format($invoice['current'], 2) }}</td>
                            <td class="text-end">{{ number_format($invoice['days_1_30'], 2) }}</td>
                            <td class="text-end">{{ number_format($invoice['days_31_60'], 2) }}</td>
                            <td class="text-end">{{ number_format($invoice['days_61_90'], 2) }}</td>
                            <td class="text-end">{{ number_format($invoice['days_91_120'], 2) }}</td>
                            <td class="text-end">{{ number_format($invoice['days_over_120'], 2) }}</td>
                            <td class="text-end">{{ number_format($invoice['total'], 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="10" class="text-center">No invoices found for this supplier</td>
                    </tr>
                @endif
            </tbody>
            <tfoot class="table-dark">
                <tr>
                    <th colspan="3" class="text-end">Total</th>
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
