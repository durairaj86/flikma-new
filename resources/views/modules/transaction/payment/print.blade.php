@php
    $cur = strtoupper($payment->currency ?: 'SAR');
    $base = strtoupper(optional(authUserCompany())->currency ?: 'SAR');
    $isDraft = (int) ($payment->status instanceof \BackedEnum ? $payment->status->value : $payment->status) === 1;
    $isCancelled = (int) ($payment->status instanceof \BackedEnum ? $payment->status->value : $payment->status) === 3;
    $accountName = $payment->account ? \Illuminate\Support\Facades\DB::table('accounts')->where('id', $payment->account)->value('name') : null;
    $method = $payment->payment_method;
    $paid = (float) $payment->grand_total;
    // bank / other charges and any extra postings made with this payment (shown as a note, they are not invoice lines)
    $extras = collect();
    if ((float) $payment->bank_charges > 0) $extras->push([__('Bank charges'), (float) $payment->bank_charges]);
    if ((float) $payment->other_charges > 0) $extras->push([__('Other charges'), (float) $payment->other_charges]);
    foreach ($payment->additionalTransactions ?? [] as $at) {
        $extras->push([$at->description ?: ($at->account->name ?? __('Additional')), (float) $at->amount * ($at->is_debit ? 1 : -1)]);
    }
    $preparedBy = $payment->createdBy->name ?? null;
    $approvedBy = $payment->approvedBy->name ?? null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Payment Voucher') }} {{ $payment->row_no }}</title>
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
        .stamp.cancelled { border-color: #e0a3a3; color: #b42323; }
        .wm.cancelled { color: rgba(180, 35, 35, .08); }
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
        .sign td { width: 33.33%; padding-right: 22px; font-size: 10.5px; color: #374151; }
        .sign .line { border-top: 1px solid #9ca3af; margin-top: 34px; padding-top: 5px; }
        .thanks { margin-top: 18px; text-align: center; color: var(--muted); font-size: 10.5px; }
        .foot { background: var(--tint); border-top: 1px solid var(--line); text-align: center; font-size: 9.5px; color: var(--muted); padding: 7px 10px; }
    </style>
</head>
<body>
<div class="receipt">
    @if($isDraft)<div class="wm">DRAFT</div>@elseif($isCancelled)<div class="wm cancelled">{{ __('CANCELLED') }}</div>@endif
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
                    <div class="en">{{ $isCancelled ? __('CANCELLED PAYMENT VOUCHER') : ($isDraft ? __('DRAFT PAYMENT VOUCHER') : __('PAYMENT VOUCHER')) }}</div>
                    <div class="ar">سند صرف</div>
                    <div class="no">{{ $payment->row_no }}</div>
                </td>
            </tr>
        </table>

        <div class="strip">
            <table>
                <tr>
                    <td><div class="lbl">{{ __('Date') }}</div><div class="val">{{ $payment->payment_date }}</div></td>
                    <td><div class="lbl">{{ __('Reference') }}</div><div class="val">{{ $payment->reference_no ?: '-' }}</div></td>
                    <td><div class="lbl">{{ __('Paid from') }}</div><div class="val">{{ $accountName ?: '-' }}</div></td>
                    <td><div class="lbl">{{ $method ? __('Method') : __('Job') }}</div><div class="val">{{ $method ? \Illuminate\Support\Str::headline($method) : ($payment->job_no ?: '-') }}</div></td>
                </tr>
            </table>
        </div>

        <table class="two" style="margin-top: 18px;">
            <tr>
                <td style="width: 50%; padding-right: 20px;">
                    <div class="card-h">{{ __('Paid to') }} <span style="letter-spacing: 0;">|</span> <span class="ar" style="letter-spacing: 0;">صرفنا إلى</span></div>
                    <div class="party-name">{{ $payment->supplier->name_en ?? $payment->supplier->name ?? '-' }}</div>
                    @if($payment->supplier?->name_ar)<div class="ar" style="font-size: 12px; margin-top: 2px;">{{ $payment->supplier->name_ar }}</div>@endif
                    <div class="party-sub">
                        {{ $payment->supplier->address1_en ?? $payment->supplier->address ?? '' }}@if($payment->supplier?->city_en), {{ $payment->supplier->city_en }}@endif
                        @if($payment->supplier?->phone)<br>{{ __('Phone') }}: {{ $payment->supplier->phone }}@endif
                        @if($payment->supplier?->vat_number)<br>{{ __('VAT No') }}: {{ $payment->supplier->vat_number }}@endif
                        @if($cur !== $base)<br>{{ __('Exchange rate') }}: 1 {{ $cur }} = {{ number_format($payment->currency_rate, 4) }} {{ $base }}@endif
                    </div>
                </td>
                <td>
                    <div class="hero">
                        @if($isCancelled)<div class="stamp cancelled">{{ __('CANCELLED') }}</div>@elseif(!$isDraft)<div class="stamp">{{ __('PAID') }}</div>@endif
                        <div class="lbl">{{ __('Amount paid') }} | المبلغ المدفوع</div>
                        <div class="fig">{{ $cur }} {{ number_format($paid, 2) }}</div>
                        <div class="words">
                            <div>{{ amountInWords(round($paid, 2)) }}</div>
                            <div class="ar" style="text-align: right;">{{ convert(round($paid, 2), $payment->currency) }}</div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="card-h" style="margin-top: 20px;">{{ __('Against supplier invoices') }} <span style="letter-spacing: 0;">|</span> <span class="ar" style="letter-spacing: 0;">عن فواتير الموردين</span></div>
        <table class="lines">
            <thead>
            <tr>
                <th style="width: 6%;">#</th>
                <th>{{ __('Invoice') }}</th>
                <th>{{ __('Invoice date') }}</th>
                <th class="n">{{ __('Invoice total') }}</th>
                <th class="n">{{ __('Paid') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($payment->paymentInvoices->filter(fn ($pi) => (float) $pi->amount > 0) as $pi)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><b>{{ $pi->supplierInvoice->row_no ?? '-' }}</b></td>
                    <td>{{ $pi->supplierInvoice->invoice_date ?? '-' }}</td>
                    <td class="n">{{ number_format($pi->supplierInvoice->grand_total ?? 0, 2) }}</td>
                    <td class="n">{{ number_format($pi->amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted" style="text-align: center;">{{ __('No invoices') }}</td></tr>
            @endforelse
            <tr class="total">
                <td colspan="4" class="n">{{ __('Total paid') }} ({{ $cur }})</td>
                <td class="n"><b>{{ number_format($paid, 2) }}</b></td>
            </tr>
            </tbody>
        </table>

        @if($extras->isNotEmpty())
            <div class="note">
                <b>{{ __('Charges & other postings with this payment') }}:</b>
                @foreach($extras as [$label, $amt]){{ $label }} {{ number_format($amt, 2) }}{{ $loop->last ? '' : ', ' }}@endforeach
            </div>
        @endif
        @if($payment->notes)
            <div class="note"><b>{{ __('Notes') }}:</b> {{ $payment->notes }}</div>
        @endif

        <table class="sign">
            <tr>
                <td><div class="line">{{ __('Prepared by') }}@if($preparedBy): <b>{{ $preparedBy }}</b>@endif</div></td>
                <td><div class="line">{{ __('Approved by') }}@if($approvedBy): <b>{{ $approvedBy }}</b>@endif</div></td>
                <td><div class="line">{{ __('Received by (supplier)') }}</div></td>
            </tr>
        </table>

        <div class="thanks"></div>
    </div>
    <div class="foot">{{ __('This is a computer generated payment voucher.') }}</div>
</div>
@if(!empty($autoPrint))
    <script>window.addEventListener('load', function () { window.print(); });</script>
@endif
</body>
</html>
