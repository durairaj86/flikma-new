{{-- voucher details + entries: shared by the View page and the drawer on the list --}}
@php
    $v = $journalVoucher;
    $typeLabel = \App\Enums\JournalVoucherTypeEnum::tryFrom((int) $v->voucher_type)?->label() ?? '-';
    $statusLabel = \App\Enums\JournalVoucherStatusEnum::tryFrom((int) $v->status)?->label() ?? '-';
    $dec = decimals();
    $fmt = fn ($n) => number_format((float) $n, $dec);
    $currency = strtoupper($v->currency ?: (optional(authUserCompany())->base_currency ?: 'SAR'));
    // names of the customers / suppliers / jobs the lines point at, loaded once
    $ids = fn ($type) => $v->journalVoucherItems->where('entity_type', $type)->pluck('entity_id')->filter()->unique()->all();
    $names = [
        'customer' => $ids('customer') ? \App\Models\Customer\Customer::whereIn('id', $ids('customer'))->pluck('name_en', 'id')->all() : [],
        'supplier' => $ids('supplier') ? \App\Models\Supplier\Supplier::whereIn('id', $ids('supplier'))->pluck('name_en', 'id')->all() : [],
        'job' => $ids('job') ? \App\Models\Job\Job::whereIn('id', $ids('job'))->pluck('row_no', 'id')->all() : [],
    ];
    $entityLabel = fn ($i) => $i->entity_type ? __(ucfirst($i->entity_type)) . ($i->entity_id ? ': ' . ($names[$i->entity_type][$i->entity_id] ?? '#' . $i->entity_id) : '') : '';
    $debit = (float) $v->journalVoucherItems->sum('debit_amount');
    $credit = (float) $v->journalVoucherItems->sum('credit_amount');
    $tax = (float) $v->journalVoucherItems->sum('tax_amount');
@endphp
@if((int) $v->status === 3 && $v->disapproval_reason)
    <div class="alert alert-danger"><strong>{{ __('Disapproval Reason') }}:</strong> {{ $v->disapproval_reason }}</div>
@endif

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3"><div class="text-muted small">{{ __('Voucher Type') }}</div><div class="fw-semibold">{{ $typeLabel }}</div></div>
            <div class="col-md-3"><div class="text-muted small">{{ __('Voucher Date') }}</div><div class="fw-semibold">{{ \Carbon\Carbon::parse($v->voucher_date)->translatedFormat('d M Y') }}</div></div>
            <div class="col-md-3"><div class="text-muted small">{{ __('Reference No') }}</div><div class="fw-semibold">{{ $v->reference_no ?: '—' }}</div></div>
            <div class="col-md-3"><div class="text-muted small">{{ __('Job') }}</div><div class="fw-semibold">{{ $v->job_no ?: ($v->job->row_no ?? '—') }}</div></div>
            <div class="col-md-3"><div class="text-muted small">{{ __('Currency') }}</div><div class="fw-semibold">{{ $currency }}@if((float) $v->currency_rate && (float) $v->currency_rate != 1) <span class="text-muted fw-normal">(1 = {{ rtrim(rtrim(number_format((float) $v->currency_rate, 4), '0'), '.') }})</span>@endif</div></div>
            <div class="col-md-3"><div class="text-muted small">{{ __('Created by') }}</div><div class="fw-semibold">{{ $v->createdBy->name ?? '—' }}</div></div>
            <div class="col-md-3"><div class="text-muted small">{{ __('Approved by') }}</div><div class="fw-semibold">{{ $v->approvedBy->name ?? '—' }}</div></div>
            <div class="col-md-3"><div class="text-muted small">{{ __('Notes') }}</div><div class="fw-semibold">{{ $v->notes ?: '—' }}</div></div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold">{{ __('Journal Entries') }}</div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>{{ __('Account') }}</th><th>{{ __('Entity') }}</th><th>{{ __('Description') }}</th>
                <th class="text-end">{{ __('Debit') }} ({{ $currency }})</th><th class="text-end">{{ __('Credit') }} ({{ $currency }})</th><th class="text-end">{{ __('Tax Amount') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($v->journalVoucherItems as $i)
                <tr>
                    <td>{{ $i->account ? $i->account->code . ' - ' . __($i->account->name) : '-' }}</td>
                    <td>{{ $entityLabel($i) }}</td>
                    <td>{{ $i->description }}</td>
                    <td class="text-end">{{ (float) $i->debit_amount ? $fmt($i->debit_amount) : '' }}</td>
                    <td class="text-end">{{ (float) $i->credit_amount ? $fmt($i->credit_amount) : '' }}</td>
                    <td class="text-end">{{ (float) $i->tax_amount ? $fmt($i->tax_amount) : '' }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot class="fw-bold">
            <tr><td colspan="3" class="text-end">{{ __('Total') }}</td><td class="text-end">{{ $fmt($debit) }}</td><td class="text-end">{{ $fmt($credit) }}</td><td class="text-end">{{ $fmt($tax) }}</td></tr>
            <tr class="{{ abs($debit - $credit) > 0.005 ? 'table-danger' : 'table-success' }}"><td colspan="3" class="text-end">{{ __('Balance') }}</td><td colspan="3">{{ $fmt(abs($debit - $credit)) }}</td></tr>
            </tfoot>
        </table>
    </div>
</div>
