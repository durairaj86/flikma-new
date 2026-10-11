<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Salary Slip') }} {{ $record->payroll_number }}</title>
    <style>
        *{box-sizing:border-box}
        body.embed{padding:12px}body{font-family:Arial,Helvetica,sans-serif;color:#212529;background:#f1f3f5;margin:0;padding:24px;font-size:13px}
        .toolbar{max-width:800px;margin:0 auto 12px;text-align:right}
        .toolbar button,.toolbar a{font:inherit;padding:7px 16px;border:1px solid #ced4da;background:#fff;border-radius:6px;cursor:pointer;text-decoration:none;color:#212529;margin-left:6px}
        .toolbar button{background:#0d6efd;border-color:#0d6efd;color:#fff}
        .slip{max-width:800px;margin:0 auto;background:#fff;padding:32px;border:1px solid #dee2e6}
        .head{display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #212529;padding-bottom:12px;margin-bottom:16px}
        .head img{max-height:56px;margin-right:12px}
        .co{display:flex;align-items:center}
        h1{font-size:20px;margin:0}
        .muted{color:#6c757d}
        .title{text-align:right}
        .title h2{margin:0;font-size:18px;text-transform:uppercase;letter-spacing:1px}
        table{width:100%;border-collapse:collapse}
        .info td{padding:4px 0;vertical-align:top}
        .info td:nth-child(odd){color:#6c757d;width:18%}
        .info td:nth-child(even){width:32%;font-weight:600}
        .cols{display:flex;gap:20px;margin-top:18px}
        .cols>div{flex:1}
        .cols th{background:#f1f3f5;text-align:left;padding:7px 8px;border:1px solid #dee2e6}
        .cols th:last-child,.cols td:last-child{text-align:right}
        .cols td{padding:6px 8px;border:1px solid #dee2e6}
        .cols tr.tot td{font-weight:700;background:#f8f9fa}
        .net{margin-top:18px;border:2px solid #212529;padding:10px 14px;display:flex;justify-content:space-between;font-size:16px;font-weight:700}
        .sign{display:flex;justify-content:space-between;margin-top:56px}
        .sign div{width:40%;border-top:1px solid #212529;padding-top:6px;text-align:center;color:#6c757d}
        .foot{margin-top:24px;text-align:center;font-size:11px;color:#6c757d}
        @media print{body{background:#fff;padding:0}.toolbar{display:none}.slip{border:0;max-width:none;padding:0}}
    </style>
</head>
<body class="{{ request()->boolean('embed') ? 'embed' : '' }}">
@php $money = fn ($v) => number_format((float) $v, 2); @endphp
<div class="toolbar">
    @unless(request()->boolean('embed'))<a href="{{ route('payroll.runs.show', $record) }}">{{ __('Back') }}</a>@endunless
    <button type="button" onclick="window.print()">{{ __('Print') }}</button>
</div>
<div class="slip">
    <div class="head">
        <div class="co">
            @if($company?->logo_data_uri)<img src="{{ $company->logo_data_uri }}" alt="">@endif
            <div>
                <h1>{{ $company->company_name ?? '' }}</h1>
                @if($company?->phone)<div class="muted">{{ $company->phone }}</div>@endif
            </div>
        </div>
        <div class="title">
            <h2>{{ __('Salary Slip') }}</h2>
            <div class="muted">{{ \Illuminate\Support\Carbon::create($record->year, $record->month, 1)->format('F Y') }}</div>
        </div>
    </div>

    <table class="info">
        <tr><td>{{ __('Employee') }}</td><td>{{ $record->employee->name ?? '—' }}</td><td>{{ __('Payroll #') }}</td><td>{{ $record->payroll_number }}</td></tr>
        <tr><td>{{ __('Employee Code') }}</td><td>{{ $record->employee->employee_code ?? '—' }}</td><td>{{ __('Status') }}</td><td>{{ __(ucfirst($record->status)) }}</td></tr>
        <tr><td>{{ __('Department') }}</td><td>{{ $record->employee->department ?? '—' }}</td><td>{{ __('Payment Date') }}</td><td>{{ $record->payment_date?->format('d-m-Y') ?? '—' }}</td></tr>
        <tr><td>{{ __('Paid From') }}</td><td colspan="3">{{ $record->isPaid() && $record->bankAccount ? $record->bankAccount->name : '—' }}</td></tr>
    </table>

    <div class="cols">
        <table>
            <tr><th>{{ __('Description') }}</th><th>{{ __('Amount') }}</th></tr>
            <tr><td>{{ __('Basic Salary') }}</td><td>{{ $money($record->basic_salary) }}</td></tr>
            <tr><td>{{ __('Housing Allowance') }}</td><td>{{ $money($record->housing_allowance) }}</td></tr>
            <tr><td>{{ __('Transportation Allowance') }}</td><td>{{ $money($record->transportation_allowance) }}</td></tr>
            <tr><td>{{ __('Other Allowances') }}</td><td>{{ $money($record->other_allowances) }}</td></tr>
            <tr class="tot"><td>{{ __('Total Earnings') }}</td><td>{{ $money($record->total_earnings) }}</td></tr>
            <tr><td>{{ __('Loan Deduction') }}</td><td>− {{ $money($record->loan_deduction) }}</td></tr>
            <tr><td>{{ __('Other Deductions') }}</td><td>− {{ $money($record->other_deductions) }}</td></tr>
            <tr class="tot"><td>{{ __('Total Deductions') }}</td><td>− {{ $money($record->total_deductions) }}</td></tr>
        </table>
    </div>

    <div class="net"><span>{{ __('Net Payable') }}</span><span>{{ $money($record->net_payable) }} {{ $company->currency_code ?? 'SAR' }}</span></div>

    <div class="sign"><div>{{ __('Employee Signature') }}</div><div>{{ __('Authorized Signature') }}</div></div>
    <div class="foot">{{ __('This is a system generated salary slip.') }}</div>
</div>
</body>
</html>
