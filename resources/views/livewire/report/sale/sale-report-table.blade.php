<div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Invoice Number</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Job Number</th>
                    <th>Currency</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">Tax</th>
                    <th class="text-end">Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($saleReportData['sales']) && count($saleReportData['sales']) > 0)
                    @foreach($saleReportData['sales'] as $sale)
                        <tr>
                            <td>{{ $sale->invoice_number ?? $sale->row_no }}</td>
                            <td>{{ $sale->invoice_date }}</td>
                            <td>{{ $sale->customer->name ?? $sale->customer->name_en ?? 'N/A' }}</td>
                            <td>{{ $sale->job->row_no ?? 'N/A' }}</td>
                            <td>{{ $sale->currency }}</td>
                            <td class="text-end">{{ number_format($sale->sub_total, 2) }}</td>
                            <td class="text-end">{{ number_format($sale->tax_total, 2) }}</td>
                            <td class="text-end">{{ number_format($sale->grand_total, 2) }}</td>
                            <td>
                                @if($sale->status == 'draft')
                                    <span class="badge bg-secondary">Draft</span>
                                @elseif($sale->status == 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($sale->status == 'cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                @else
                                    <span class="badge bg-info">{{ $sale->status }}</span>
                                @endif
                            </td>
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
                    <th colspan="5" class="text-end">Total</th>
                    <th class="text-end">{{ isset($saleReportData['total_amount']) ? number_format($saleReportData['total_amount'], 2) : '0.00' }}</th>
                    <th class="text-end">{{ isset($saleReportData['total_tax']) ? number_format($saleReportData['total_tax'], 2) : '0.00' }}</th>
                    <th class="text-end">{{ isset($saleReportData['total_grand']) ? number_format($saleReportData['total_grand'], 2) : '0.00' }}</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
