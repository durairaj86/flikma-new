@php
    $cur = strtoupper($collection->currency ?: 'SAR');
    $base = strtoupper(optional(authUserCompany())->currency ?: 'SAR');
    $isDraft = (int) ($collection->status instanceof \BackedEnum ? $collection->status->value : $collection->status) === 1;
    $accountName = $collection->account ? \Illuminate\Support\Facades\DB::table('accounts')->where('id', $collection->account)->value('name') : null;
    $method = $collection->payment_method ?: $collection->collection_method;
    $received = (float) $collection->grand_total;
    // customer credit used to settle these invoices instead of cash (shown only when there is any)
    $creditApplied = \Illuminate\Support\Facades\Schema::hasTable('customer_credit_applications')
        ? \Illuminate\Support\Facades\DB::table('customer_credit_applications as ca')
            ->leftJoin('customer_invoices as f', 'f.id', '=', 'ca.from_invoice_id')
            ->leftJoin('customer_invoices as t', 't.id', '=', 'ca.to_invoice_id')
            ->where('ca.collection_id', $collection->id)->where('ca.status', 1)
            ->get(['ca.amount', 'f.row_no as from_no', 't.row_no as to_no'])
        : collect();
    $receivedBy = $collection->approvedBy->name ?? $collection->createdBy->name ?? null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Receipt') }} {{ $collection->row_no }}</title>
    <style>
        @page { size: A4; margin: 10mm; }
        * { box-sizing: border-box; }
        :root { --ink: #111827; --muted: #6b7280; --line: #e5e7eb; --accent: #374151; --tint: #f6f7f9; --ok: #15803d; }
        body { font-family: Arial, Helvetica, sans-serif; color: var(--ink); font-size: 12px; margin: 0; background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .receipt { width: 720px; max-width: 100%; margin: 0 auto; border: 1px solid var(--line); border-radius: 8px; overflow: hidden; position: relative; }
        .pad { padding: 20px 28px 22px; }
        table { width: 100%; border-collapse: collapse; }
        .muted { color: var(--muted); }
        .ar { direction: rtl; unicode-bidi: isolate; }
        .wm { position: absolute; top: 42%; left: 50%; transform: translate(-50%, -50%) rotate(-28deg); font-size: 120px; font-weight: 800; color: rgba(0, 0, 0, .05); pointer-events: none; }

        .head td { vertical-align: top; }
        .co { font-size: 17px; font-weight: 700; color: var(--accent); }
        .co-sub { color: var(--muted); line-height: 1.55; margin-top: 3px; }
        .doc { text-align: right; }
        .doc .en { font-size: 24px; font-weight: 800; letter-spacing: .12em; color: var(--accent); line-height: 1; }
        .doc .ar { font-size: 16px; font-weight: 700; color: var(--accent); margin-top: 3px; }
        .doc .no { display: inline-block; margin-top: 8px; padding: 4px 12px; border: 1px solid #c3c9d3; border-radius: 20px; font-weight: 700; font-size: 12.5px; color: var(--accent); }

        .strip { background: var(--tint); border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); margin: 16px -28px 0; padding: 10px 28px; }
        .strip td { width: 25%; vertical-align: top; }
        .lbl { font-size: 9px; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); }
        .val { font-size: 12.5px; font-weight: 700; margin-top: 2px; }

        .two td { vertical-align: top; }
        .card-h { font-size: 9.5px; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); border-bottom: 1px solid var(--line); padding-bottom: 5px; margin-bottom: 8px; }
        .party-name { font-size: 15px; font-weight: 700; }
        .party-sub { color: var(--muted); line-height: 1.55; margin-top: 5px; }

        .hero { background: var(--tint); color: var(--ink); border: 1px solid #d9dee6; border-radius: 8px; padding: 14px 18px; position: relative; }
        .hero .lbl { color: var(--muted); }
        .hero .fig { font-size: 28px; font-weight: 800; margin-top: 3px; white-space: nowrap; }
        .hero .words { margin-top: 8px; padding-top: 8px; border-top: 1px solid #d9dee6; font-size: 11px; line-height: 1.55; }
        .stamp { position: absolute; top: 12px; right: 14px; border: 2px solid #86c79a; color: #2f8f4e; border-radius: 5px; padding: 1px 9px; font-size: 11px; font-weight: 800; letter-spacing: .12em; transform: rotate(-6deg); }

        .lines { margin-top: 4px; }
        .lines th { background: #f1f3f6; color: #4b5563; text-align: left; font-size: 9.5px; letter-spacing: .06em; text-transform: uppercase; padding: 8px 10px; border-top: 1px solid #d9dee6; border-bottom: 1px solid #d9dee6; }
                .lines td { padding: 8px 10px; border-bottom: 1px solid var(--line); }
        .lines tbody tr:nth-child(even) td { background: #fafbfd; }
        .n { text-align: right !important; white-space: nowrap; }
        .total td { font-weight: 800; font-size: 13px; border-bottom: 0; padding-top: 10px; }
        .total td.n b { border-bottom: 3px double var(--accent); padding-bottom: 1px; }

        .note { margin-top: 12px; padding: 8px 12px; background: var(--tint); border-left: 3px solid var(--accent); border-radius: 0 4px 4px 0; font-size: 11px; line-height: 1.5; }
        .sign { margin-top: 38px; }
        .sign td { width: 50%; padding-right: 30px; font-size: 10.5px; color: #374151; }
        .sign .line { border-top: 1px solid #9ca3af; margin-top: 34px; padding-top: 5px; }
        .thanks { margin-top: 18px; text-align: center; color: var(--muted); font-size: 10.5px; }
        .foot { background: var(--tint); border-top: 1px solid var(--line); text-align: center; font-size: 9.5px; color: var(--muted); padding: 7px 10px; }
    </style>
</head>
<body>
<div class="receipt">
    @if($isDraft)<div class="wm">DRAFT</div>@endif
    <div class="pad">

        <table class="head">
            <tr>
                <td style="width: 56%;">
                    @if(companyLogo() && optional(authUserCompany())->logo)
                        <img src="{{ companyLogo() }}" alt="" style="max-height: 50px; max-width: 190px; display: block; margin-bottom: 5px;">
                    @endif
                    <div class="co">{{ companyName() }}</div>
                    <div class="co-sub">
                        {{ companyAddress() }}<br>
                        @if(companyPhone()){{ __('Phone') }}: {{ companyPhone() }}@endif @if(companyEmail()) &nbsp;|&nbsp; {{ companyEmail() }}@endif
                    </div>
                </td>
                <td class="doc">
                    <div class="en">{{ $isDraft ? __('DRAFT RECEIPT') : __('RECEIPT') }}</div>
                    <div class="ar">سند قبض</div>
                    <div class="no">{{ $collection->row_no }}</div>
                </td>
            </tr>
        </table>

        <div class="strip">
            <table>
                <tr>
                    <td><div class="lbl">{{ __('Date') }}</div><div class="val">{{ $collection->collection_date }}</div></td>
                    <td><div class="lbl">{{ __('Reference') }}</div><div class="val">{{ $collection->reference_no ?: '-' }}</div></td>
                    <td><div class="lbl">{{ __('Paid into') }}</div><div class="val">{{ $accountName ?: '-' }}</div></td>
                    <td><div class="lbl">{{ $method ? __('Method') : __('Job') }}</div><div class="val">{{ $method ? \Illuminate\Support\Str::headline($method) : ($collection->job_no ?: '-') }}</div></td>
                </tr>
            </table>
        </div>

        <table class="two" style="margin-top: 18px;">
            <tr>
                <td style="width: 50%; padding-right: 20px;">
                    <div class="card-h">{{ __('Received from') }} <span style="letter-spacing: 0;">|</span> <span class="ar" style="letter-spacing: 0;">استلمنا من</span></div>
                    <div class="party-name">{{ $collection->customer->name_en ?? '-' }}</div>
                    @if($collection->customer?->name_ar)<div class="ar" style="font-size: 12px; margin-top: 2px;">{{ $collection->customer->name_ar }}</div>@endif
                    <div class="party-sub">
                        {{ $collection->customer->address1_en ?? '' }}@if($collection->customer?->city_en), {{ $collection->customer->city_en }}@endif
                        @if($collection->customer?->phone)<br>{{ __('Phone') }}: {{ $collection->customer->phone }}@endif
                        @if($collection->customer?->vat_number)<br>{{ __('VAT No') }}: {{ $collection->customer->vat_number }}@endif
                        @if($cur !== $base)<br>{{ __('Exchange rate') }}: 1 {{ $cur }} = {{ number_format($collection->currency_rate, 4) }} {{ $base }}@endif
                    </div>
                </td>
                <td>
                    <div class="hero">
                        @unless($isDraft)<div class="stamp">{{ __('RECEIVED') }}</div>@endunless
                        <div class="lbl">{{ __('Amount received') }} | المبلغ المستلم</div>
                        <div class="fig">{{ $cur }} {{ number_format($received, 2) }}</div>
                        <div class="words">
                            <div>{{ amountInWords(round($received, 2)) }}</div>
                            <div class="ar" style="text-align: right;">{{ convert(round($received, 2), $collection->currency) }}</div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="card-h" style="margin-top: 20px;">{{ __('Against invoices') }} <span style="letter-spacing: 0;">|</span> <span class="ar" style="letter-spacing: 0;">عن الفواتير</span></div>
        <table class="lines">
            <thead>
            <tr>
                <th style="width: 6%;">#</th>
                <th>{{ __('Invoice') }}</th>
                <th>{{ __('Invoice date') }}</th>
                <th class="n">{{ __('Invoice total') }}</th>
                <th class="n">{{ __('Received') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($collection->collectionInvoices->filter(fn ($ci) => (float) $ci->amount > 0) as $ci)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><b>{{ $ci->customerInvoice->row_no ?? '-' }}</b></td>
                    <td>{{ $ci->customerInvoice->invoice_date ?? '-' }}</td>
                    <td class="n">{{ number_format($ci->customerInvoice->grand_total ?? 0, 2) }}</td>
                    <td class="n">{{ number_format($ci->amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted" style="text-align: center;">{{ __('No invoices') }}</td></tr>
            @endforelse
            <tr class="total">
                <td colspan="4" class="n">{{ __('Total received') }} ({{ $cur }})</td>
                <td class="n"><b>{{ number_format($received, 2) }}</b></td>
            </tr>
            </tbody>
        </table>

        @if($creditApplied->isNotEmpty())
            <div class="note">
                <b>{{ __('Settled with customer credit') }}:</b>
                @foreach($creditApplied as $ca){{ number_format($ca->amount, 2) }} ({{ $ca->from_no }} → {{ $ca->to_no }}){{ $loop->last ? '' : ', ' }}@endforeach
            </div>
        @endif
        @if($collection->notes)
            <div class="note"><b>{{ __('Notes') }}:</b> {{ $collection->notes }}</div>
        @endif

        <table class="sign">
            <tr>
                <td><div class="line">{{ __('Received by') }}@if($receivedBy): <b>{{ $receivedBy }}</b>@endif</div></td>
                <td><div class="line">{{ __('Customer signature') }}</div></td>
            </tr>
        </table>

        <div class="thanks">{{ __('Thank you for your payment') }} | <span class="ar">شكراً لكم على الدفع</span></div>
    </div>
    <div class="foot">{{ __('This is a computer generated receipt.') }}</div>
</div>
</body>
</html>
