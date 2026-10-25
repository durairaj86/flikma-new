{{-- Postings behind one Trial Balance amount (loaded into the global modal, which has no header of its own) --}}
<div class="modal-header justify-content-between border-bottom py-3">
    <div>
        <div class="fw-semibold fs-5">{{ $account->code }} &middot; {{ $account->name }}</div>
        <div class="small text-muted">
            <span class="badge {{ $type === 'debit' ? 'text-bg-primary' : 'text-bg-danger' }}">{{ $type === 'debit' ? __('Debit entries') : __('Credit entries') }}</span>
            {{ __('up to') }} {{ \Carbon\Carbon::parse($to)->translatedFormat('d M Y') }}
            @if($multiple)<span class="ms-1">({{ __('including sub-accounts') }})</span>@endif
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
                @if($multiple)<th>{{ __('Account') }}</th>@endif
                <th class="text-end pe-3">{{ __('Amount') }}</th>
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
                    @if($multiple)<td class="text-nowrap">{{ $l->account_code }} {{ $l->account_name }}</td>@endif
                    <td class="text-end pe-3 tabular-nums fw-bold {{ $type === 'debit' ? 'text-primary' : 'text-danger' }}">{{ number_format((float) $l->{'base_' . $type}, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="{{ $multiple ? 5 : 4 }}" class="text-center py-4 text-muted">{{ __('No entries found for this account and period.') }}</td></tr>
            @endforelse
            </tbody>
            @if($lines->count())
                <tfoot>
                <tr class="table-light fw-bold">
                    <td colspan="{{ $multiple ? 4 : 3 }}" class="ps-3">{{ __('Total') }} ({{ $lines->count() }} {{ __($lines->count() === 1 ? 'entry' : 'entries') }})</td>
                    <td class="text-end pe-3 tabular-nums">{{ number_format((float) $total, 2) }}</td>
                </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
