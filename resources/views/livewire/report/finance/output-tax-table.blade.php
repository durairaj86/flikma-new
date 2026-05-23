<div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                    <th class="ps-4 border-0 py-3">Account</th>
                    <th class="border-0 py-3">Reference No</th>
                    <th class="border-0 py-3">Date</th>
                    <th class="border-0 py-3">Description</th>
                    <th class="text-end pe-4 border-0 py-3">Tax Amount</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                @if(isset($outputTaxData['output_tax_transactions']) && count($outputTaxData['output_tax_transactions']) > 0)
                    @foreach($outputTaxData['output_tax_transactions'] as $transaction)
                        <tr wire:key="ot-{{ $loop->index }}">
                            <td class="ps-4">
                                <span class="fw-bold text-dark">{{ $transaction['account_name'] }}</span>
                                <div class="text-muted" style="font-size:0.7rem;">{{ $transaction['account_code'] }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border fw-normal px-2 py-1">
                                    {{ $transaction['reference_no'] }}
                                </span>
                            </td>
                            <td class="small text-muted">
                                {{ \Carbon\Carbon::parse($transaction['reference_date'])->format('d M Y') }}
                            </td>
                            <td class="small text-muted">{{ $transaction['description'] }}</td>
                            <td class="pe-4 text-end tabular-nums fw-bold text-success">
                                {{ number_format($transaction['amount'], 2) }}
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <div class="bg-light rounded-circle p-4 d-inline-block mb-3">
                                <i class="bi bi-journal-arrow-up h2 text-muted"></i>
                            </div>
                            <div class="small">No output tax transactions found for this period.</div>
                        </td>
                    </tr>
                @endif
            </tbody>
            @if(isset($outputTaxData['total_output_tax']) && $outputTaxData['total_output_tax'] > 0)
            <tfoot class="bg-light border-top-2">
                <tr class="fw-bold">
                    <td colspan="4" class="ps-4 py-3 text-uppercase small text-muted">Total Output Tax</td>
                    <td class="pe-4 text-end tabular-nums text-success py-3">
                        {{ number_format($outputTaxData['total_output_tax'], 2) }}
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
