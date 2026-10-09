<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Debit Note') }} {{ $debitNote->row_no }}</title>
    <style>
        @page { size: A4; margin: 14mm; }
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #1f2937; font-size: 10pt; margin: 0; }
        .head { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #dc2626; padding-bottom: 10px; }
        .head h1 { margin: 0; font-size: 20pt; letter-spacing: 1px; color: #dc2626; }
        .muted { color: #6b7280; }
        .box { margin-top: 14px; display: flex; gap: 24px; }
        .box > div { flex: 1; }
        .box h4 { margin: 0 0 4px; font-size: 8.5pt; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; padding-bottom: 3px; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 18px; }
        table.items th { background: #f3f4f6; text-align: left; font-size: 8.5pt; text-transform: uppercase; padding: 7px 6px; border-bottom: 2px solid #dc2626; }
        table.items td { padding: 7px 6px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        .r { text-align: right; }
        .totals { width: 280px; margin-left: auto; margin-top: 14px; }
        .totals div { display: flex; justify-content: space-between; padding: 4px 0; }
        .totals .grand { border-top: 2px solid #1f2937; font-weight: 700; font-size: 12pt; margin-top: 4px; padding-top: 8px; }
        .foot { margin-top: 28px; font-size: 9pt; }
        .sign { display: flex; justify-content: space-between; margin-top: 50px; }
        .sign div { width: 30%; border-top: 1px solid #9ca3af; padding-top: 4px; text-align: center; font-size: 8.5pt; color: #6b7280; }
    </style>
</head>
<body>
<div class="head">
    <div>
        <strong style="font-size: 13pt;">{{ $company->name ?? companyName() }}</strong><br>
        <span class="muted">{{ $company->address ?? '' }} {{ $company->city ?? '' }}</span><br>
        @if(!empty($company->vat_number))<span class="muted">VAT: {{ $company->vat_number }}</span>@endif
        @if(!empty($company->cr_number))<span class="muted"> · CR: {{ $company->cr_number }}</span>@endif
    </div>
    <div style="text-align: right;">
        <h1>{{ __('DEBIT NOTE') }}</h1>
        <strong>{{ $debitNote->row_no }}</strong><br>
        <span class="muted">{{ __('Date') }}: {{ $debitNote->posted_at ? \Carbon\Carbon::parse($debitNote->posted_at)->format('d-m-Y') : '-' }}</span>
    </div>
</div>

<div class="box">
    <div>
        <h4>{{ __('Debit To (Supplier)') }}</h4>
        <strong>{{ $debitNote->supplier->name_en ?? '-' }}</strong><br>
        <span class="muted">{{ $debitNote->supplier->address1_en ?? '' }} {{ $debitNote->supplier->city_en ?? '' }}</span><br>
        @if(!empty($debitNote->supplier->vat_number))<span class="muted">VAT: {{ $debitNote->supplier->vat_number }}</span>@endif
    </div>
    <div>
        <h4>{{ __('Reference') }}</h4>
        {{ __('Supplier Invoice') }}: <strong>{{ $debitNote->invoice->row_no ?? '-' }}</strong><br>
        {{ __('Job') }}: <strong>{{ $debitNote->job_no ?: '-' }}</strong><br>
        {{ __('Reason') }}: <strong>{{ ucfirst(str_replace('_', ' ', $debitNote->reason ?? '-')) }}</strong>
    </div>
</div>

<table class="items">
    <thead>
    <tr>
        <th style="width:4%">#</th>
        <th>{{ __('Description') }}</th>
        <th class="r">{{ __('Qty') }}</th>
        <th class="r">{{ __('Unit Price') }}</th>
        <th class="r">{{ __('Tax %') }}</th>
        <th class="r">{{ __('Amount') }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($debitNote->debitNoteSubs as $line)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $line->description }}@if($line->comment)<br><span class="muted">{{ $line->comment }}</span>@endif</td>
            <td class="r">{{ $line->quantity }}</td>
            <td class="r">{{ number_format($line->unit_price, decimals()) }}</td>
            <td class="r">{{ $line->tax_percent }}%</td>
            <td class="r">{{ number_format($line->total_with_tax ?? $line->total, decimals()) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="totals">
    <div><span>{{ __('Subtotal') }}</span><span>{{ number_format($debitNote->sub_total, decimals()) }}</span></div>
    <div><span>{{ __('Tax') }}</span><span>{{ number_format($debitNote->tax_total, decimals()) }}</span></div>
    <div class="grand"><span>{{ __('Total') }} ({{ strtoupper($debitNote->currency ?? 'SAR') }})</span><span>{{ number_format($debitNote->grand_total, decimals()) }}</span></div>
</div>

@if($debitNote->terms)
    <div class="foot"><strong>{{ __('Terms & Conditions') }}</strong><br>{{ $debitNote->terms }}</div>
@endif

<div class="sign">
    <div>{{ __('Prepared By') }}</div>
    <div>{{ __('Approved By') }}</div>
    <div>{{ __('Supplier Acknowledgement') }}</div>
</div>
</body>
</html>
