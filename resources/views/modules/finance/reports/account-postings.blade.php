{{-- Postings behind one Profit & Loss / Balance Sheet balance (loaded into the global modal, which has no header of its own) --}}
<div class="modal-header justify-content-between border-bottom py-3">
    <div>
        <div class="fw-semibold fs-5">{{ $account->code }} &middot; {{ $account->name }}</div>
        <div class="small text-muted">
            <span class="badge text-bg-secondary">{{ $account->type }}</span>
            @if($from){{ \Carbon\Carbon::parse($from)->format('d M Y') }} &ndash; @else{{ __('up to') }} @endif{{ \Carbon\Carbon::parse($to)->format('d M Y') }}
        </div>
    </div>
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>{{ __('Close') }}</button>
</div>
<div class="p-0">
    <div class="table-responsive" style="max-height: 58vh; overflow-y: auto;">
        <table class="table table-hover table-sm align-middle mb-0">
            <thead class="table-light sticky-top">
            <tr class="small fw-bold text-muted">
                <th class="ps-3">{{ __('Date') }}</th>
                <th>{{ __('Voucher') }}</th>
                <th>{{ __('Reference / Description') }}</th>
                <th class="text-end">{{ __('Debit') }}</th>
                <th class="text-end pe-3">{{ __('Credit') }}</th>
            </tr>
            </thead>
            <tbody class="small">
            @forelse($lines as $l)
                <tr>
                    <td class="ps-3 text-nowrap">{{ $l->transaction_date?->format('d-m-Y') }}</td>
                    <td class="text-nowrap"><span class="fw-semibold">{{ $l->voucher_no }}</span> <span class="badge text-bg-light border">{{ $l->voucher_type }}</span></td>
                    <td>
                        <div class="fw-semibold text-dark">{{ $l->reference_no }}</div>
                        <div class="text-muted" style="font-size:.75rem;">{{ $l->description }}</div>
                    </td>
                    <td class="text-end tabular-nums text-primary">{{ $l->base_debit > 0 ? number_format((float) $l->base_debit, 2) : '' }}</td>
                    <td class="text-end pe-3 tabular-nums text-danger">{{ $l->base_credit > 0 ? number_format((float) $l->base_credit, 2) : '' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center py-4 text-muted">{{ __('No entries found for this account and period.') }}</td></tr>
            @endforelse
            </tbody>
            @if($lines->count())
                <tfoot>
                <tr class="table-light fw-bold">
                    <td colspan="3" class="ps-3">{{ __('Total') }} ({{ $lines->count() }} {{ __($lines->count() === 1 ? 'entry' : 'entries') }})</td>
                    <td class="text-end tabular-nums">{{ number_format($debit, 2) }}</td>
                    <td class="text-end pe-3 tabular-nums">{{ number_format($credit, 2) }}</td>
                </tr>
                <tr class="fw-bold">
                    <td colspan="3" class="ps-3">{{ __('Balance (as shown on the report)') }}</td>
                    <td colspan="2" class="text-end pe-3 tabular-nums">{{ number_format($net, 2) }}</td>
                </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
