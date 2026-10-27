<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Journal Voucher') }} {{ $journalVoucher->row_no }}</title>
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
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 24px; color: #1f2937; font-size: 13px; background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .doc { max-width: 960px; margin: 0 auto; }
        .top { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 14px; }
        .company { font-size: 13px; color: #6b7280; margin-bottom: 4px; }
        h1 { margin: 0; font-size: 22px; color: #111827; }
        .sub { color: #6b7280; margin-top: 3px; }
        .badge { display: inline-block; padding: 5px 14px; border-radius: 999px; color: #fff; font-weight: 700; font-size: 12px; }
        .b-draft { background: #6b7280; } .b-approved { background: #198754; } .b-disapproved { background: #dc3545; }
        .alert { border: 1px solid #f1aeb5; background: #f8d7da; color: #842029; border-radius: 6px; padding: 8px 12px; margin-bottom: 12px; }
        .card { border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 14px; overflow: hidden; }
        .card-body { padding: 14px 16px; }
        .grid { display: flex; flex-wrap: wrap; gap: 12px 0; }
        .grid > div { width: 25%; padding-inline-end: 12px; }
        .lbl { color: #6b7280; font-size: 11px; }
        .val { font-weight: 600; }
        .card-head { padding: 10px 16px; font-weight: 700; border-bottom: 1px solid #e5e7eb; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 12px; text-align: start; border-bottom: 1px solid #e5e7eb; }
        thead th { background: #f3f4f6; font-size: 12px; }
        .text-end { text-align: end; }
        tfoot td { font-weight: 700; }
        .ok td { background: #d1e7dd; } .bad td { background: #f8d7da; }
        .sign { display: flex; justify-content: space-between; margin-top: 56px; }
        .sign div { width: 30%; border-top: 1px solid #1f2937; padding-top: 4px; text-align: center; }
        @media print { body { padding: 0; } @page { margin: 14mm; } }
    </style>
</head>
<body>
<div class="doc">
    <div class="top">
        <div>
            <div class="company">{{ optional(authUserCompany())->name ?? config('app.name') }}</div>
            <h1>{{ __('Journal Voucher') }} {{ $v->row_no }}</h1>
            <div class="sub">{{ $typeLabel }} · {{ \Carbon\Carbon::parse($v->voucher_date)->translatedFormat('d M Y') }}</div>
        </div>
        <span class="badge {{ (int) $v->status === 2 ? 'b-approved' : ((int) $v->status === 3 ? 'b-disapproved' : 'b-draft') }}">{{ $statusLabel }}</span>
    </div>

    @if((int) $v->status === 3 && $v->disapproval_reason)
        <div class="alert"><strong>{{ __('Disapproval Reason') }}:</strong> {{ $v->disapproval_reason }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="grid">
                <div><div class="lbl">{{ __('Voucher Type') }}</div><div class="val">{{ $typeLabel }}</div></div>
                <div><div class="lbl">{{ __('Voucher Date') }}</div><div class="val">{{ \Carbon\Carbon::parse($v->voucher_date)->translatedFormat('d M Y') }}</div></div>
                <div><div class="lbl">{{ __('Reference No') }}</div><div class="val">{{ $v->reference_no ?: '—' }}</div></div>
                <div><div class="lbl">{{ __('Job') }}</div><div class="val">{{ $v->job_no ?: ($v->job->row_no ?? '—') }}</div></div>
                <div><div class="lbl">{{ __('Currency') }}</div><div class="val">{{ $currency }}@if((float) $v->currency_rate && (float) $v->currency_rate != 1) <span style="font-weight:400;color:#6b7280">(1 = {{ rtrim(rtrim(number_format((float) $v->currency_rate, 4), '0'), '.') }})</span>@endif</div></div>
                <div><div class="lbl">{{ __('Created by') }}</div><div class="val">{{ $v->createdBy->name ?? '—' }}</div></div>
                <div><div class="lbl">{{ __('Approved by') }}</div><div class="val">{{ $v->approvedBy->name ?? '—' }}</div></div>
                <div><div class="lbl">{{ __('Notes') }}</div><div class="val">{{ $v->notes ?: '—' }}</div></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">{{ __('Journal Entries') }}</div>
        <table>
            <thead>
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
            <tfoot>
            <tr><td colspan="3" class="text-end">{{ __('Total') }}</td><td class="text-end">{{ $fmt($debit) }}</td><td class="text-end">{{ $fmt($credit) }}</td><td class="text-end">{{ $fmt($tax) }}</td></tr>
            <tr class="{{ abs($debit - $credit) > 0.005 ? 'bad' : 'ok' }}"><td colspan="3" class="text-end">{{ __('Balance') }}</td><td colspan="3">{{ $fmt(abs($debit - $credit)) }}</td></tr>
            </tfoot>
        </table>
    </div>

    <div class="sign">
        <div>{{ __('Prepared By:') }}</div>
        <div>{{ __('Verified By:') }}</div>
        <div>{{ __('Approved By:') }}</div>
    </div>
</div>
    @if(!empty($autoPrint))
        <script>window.addEventListener('load', function () { window.print(); });</script>
    @endif
</body>
</html>
