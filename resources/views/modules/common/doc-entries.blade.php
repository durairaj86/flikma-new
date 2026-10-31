{{-- Cash Flow tab: every journal entry the document posted, line by line (account, debit / credit), then the grand total.
     Usage: @include('modules.common.doc-entries', ['entrySource' => 'customer_invoice', 'entrySourceId' => $id, 'entryPrefix' => 'ci']) --}}
@php
    $deVouchers = \App\Models\Finance\Finance::with(['financeSubs' => fn ($q) => $q->orderBy('id'), 'financeSubs.account:id,code,name,name_en'])
        ->where('source_type', $entrySource)->where('source_id', $entrySourceId)->orderBy('id')->get();
    $deBase = strtoupper((string) (authUserCompany()?->base_currency ?: authUserCompany()?->currency ?: 'SAR'));
    $deDebit = 0.0;
    $deCredit = 0.0;
@endphp
<div class="tab-pane fade" id="{{ $entryPrefix }}EntriesTab" role="tabpanel">
    @forelse($deVouchers as $v)
        @php
            $vDebit = (float) $v->financeSubs->sum(fn ($l) => (float) ($l->base_debit ?? $l->debit));
            $vCredit = (float) $v->financeSubs->sum(fn ($l) => (float) ($l->base_credit ?? $l->credit));
            if (!$v->reversed_by_id) { $deDebit += $vDebit; $deCredit += $vCredit; }
        @endphp
        <div class="section entry-voucher">
            <h6>
                <span>{{ $v->voucher_no }}</span>
                <span class="entry-chip">{{ $v->voucher_type }}</span>
                @if($v->is_provisional)<span class="entry-chip warn">{{ __('Provisional') }}</span>@endif
                @if($v->reverses_id)<span class="entry-chip warn">{{ __('Reversal') }}</span>@endif
                @if($v->reversed_by_id)<span class="entry-chip bad">{{ __('Reversed') }}</span>@endif
                <span class="entry-date ms-auto">{{ $v->posted_at ? \Illuminate\Support\Carbon::parse($v->posted_at)->format('d-m-Y') : '' }}</span>
            </h6>
            @if($v->narration)<div class="entry-narr">{{ $v->narration }}</div>@endif
            <div class="table-responsive">
                <table class="table table-sm align-middle entry-table">
                    <thead>
                    <tr>
                        <th>{{ __('Account') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th class="text-end">{{ __('Debit') }} ({{ $deBase }})</th>
                        <th class="text-end">{{ __('Credit') }} ({{ $deBase }})</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($v->financeSubs as $l)
                        @php
                            $d = (float) ($l->base_debit ?? $l->debit);
                            $c = (float) ($l->base_credit ?? $l->credit);
                        @endphp
                        <tr>
                            <td>
                                <span class="fw-semibold">{{ $l->account->name ?? $l->account->name_en ?? '-' }}</span>
                                <span class="text-muted small">{{ $l->account->code ?? '' }}</span>
                                @if($l->is_tax_line)<span class="entry-chip">{{ __('VAT') }}{{ $l->tax_rate ? ' ' . rtrim(rtrim(number_format((float) $l->tax_rate, 2), '0'), '.') . '%' : '' }}</span>@endif
                            </td>
                            <td class="text-muted small">{{ $l->description }}</td>
                            <td class="text-end {{ $d > 0 ? 'entry-dr' : 'text-muted' }}">{{ $d > 0 ? number_format($d, 2) : '-' }}</td>
                            <td class="text-end {{ $c > 0 ? 'entry-cr' : 'text-muted' }}">{{ $c > 0 ? number_format($c, 2) : '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="2" class="text-end">{{ __('Entry total') }}</td>
                        <td class="text-end">{{ number_format($vDebit, 2) }}</td>
                        <td class="text-end">{{ number_format($vCredit, 2) }}</td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-journal-text fs-2 mb-2 d-block"></i>
            {{ __('No cash flow entries yet. They are posted when this document is approved.') }}
        </div>
    @endforelse

    @if($deVouchers->count())
        <div class="entry-grand {{ abs($deDebit - $deCredit) < 0.005 ? 'balanced' : 'off' }}">
            <div class="eg-label"><i class="bi {{ abs($deDebit - $deCredit) < 0.005 ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }}"></i>
                {{ abs($deDebit - $deCredit) < 0.005 ? __('Balanced') : __('Not balanced') }} · {{ __('Total') }} ({{ $deBase }})</div>
            <div class="eg-cell"><small>{{ __('Debit') }}</small><b>{{ number_format($deDebit, 2) }}</b></div>
            <div class="eg-cell"><small>{{ __('Credit') }}</small><b>{{ number_format($deCredit, 2) }}</b></div>
        </div>
    @endif
</div>
