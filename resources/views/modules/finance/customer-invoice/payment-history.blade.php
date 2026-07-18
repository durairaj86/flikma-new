<div class="px-1">
    <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
        <div>
            <h5 class="fw-bold mb-1">{{ $customerInvoice->row_no }}</h5>
            <div class="text-muted small">{{ $customerInvoice->customer->name_en ?? '' }}</div>
        </div>
        <div class="text-end">
            <div class="small text-muted">Invoice Total</div>
            <div class="fw-bold">{{ number_format($customerInvoice->grand_total, 2) }} {{ strtoupper($customerInvoice->currency) }}</div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-4 text-center">
            <div class="small text-muted text-uppercase">Grand Total</div>
            <div class="fw-bold">{{ number_format($customerInvoice->grand_total, 2) }}</div>
        </div>
        <div class="col-4 text-center">
            <div class="small text-muted text-uppercase">Paid</div>
            <div class="fw-bold text-success">{{ number_format($customerInvoice->paid_amount ?? 0, 2) }}</div>
        </div>
        <div class="col-4 text-center">
            <div class="small text-muted text-uppercase">Balance</div>
            <div class="fw-bold {{ $balance > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($balance, 2) }}</div>
        </div>
    </div>

    <h6 class="fw-bold mb-2">Collections</h6>
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle">
            <thead class="table-light">
            <tr>
                <th>Collection No</th>
                <th>Date</th>
                <th class="text-end">Amount</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            @forelse($collectionInvoices as $ci)
                @php $statusLabels = [1 => ['Draft', 'secondary'], 2 => ['Approved', 'success'], 3 => ['Cancelled', 'danger']]; @endphp
                <tr>
                    <td>
                        <a href="{{ url('transaction/collections/' . $ci->collection_id) }}" target="_blank">
                            {{ $ci->collection->row_no ?? '—' }}
                        </a>
                    </td>
                    <td>{{ $ci->collection->collection_date ?? '—' }}</td>
                    <td class="text-end">{{ number_format($ci->amount, 2) }} {{ strtoupper($ci->collection->currency ?? '') }}</td>
                    <td>
                        @php $label = $statusLabels[$ci->collection->status ?? 0] ?? ['Unknown', 'secondary']; @endphp
                        <span class="badge bg-{{ $label[1] }}-subtle text-{{ $label[1] }}-emphasis">{{ $label[0] }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">No payments recorded against this invoice yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($balance > 0)
        {{--<div class="d-grid mt-3">
            <button type="button" class="btn btn-primary" onclick="CUSTOMER_INVOICE.recordPayment('{{ $customerInvoice->id }}')">
                <i class="bi bi-cash-coin me-1"></i> Record Payment
            </button>
        </div>--}}
    @endif
</div>
