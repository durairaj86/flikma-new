<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Delivery Order') }} {{ $order->row_no }}</title>
    <style>
        @page { size: A4; margin: 14mm; }
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #1f2937; font-size: 10pt; margin: 0; }
        .head { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0d6efd; padding-bottom: 10px; }
        .head h1 { margin: 0; font-size: 20pt; letter-spacing: 1px; color: #0d6efd; }
        .muted { color: #6b7280; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px 24px; margin-top: 16px; }
        .grid h4 { margin: 0 0 4px; font-size: 8.5pt; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; padding-bottom: 3px; }
        .full { grid-column: 1 / -1; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 5px 4px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        td:first-child { width: 38%; color: #6b7280; }
        .sign { display: flex; justify-content: space-between; margin-top: 60px; }
        .sign div { width: 30%; border-top: 1px solid #9ca3af; padding-top: 4px; text-align: center; font-size: 8.5pt; color: #6b7280; }
        .note { margin-top: 24px; font-size: 8.5pt; color: #6b7280; }
    </style>
</head>
<body>
<div class="head">
    <div>
        <strong style="font-size: 13pt;">{{ $company->name ?? companyName() }}</strong><br>
        <span class="muted">{{ $company->address ?? '' }} {{ $company->city ?? '' }}</span><br>
        @if(!empty($company->phone))<span class="muted">Tel: {{ $company->phone }}</span>@endif
    </div>
    <div style="text-align: right;">
        <h1>{{ __('DELIVERY ORDER') }}</h1>
        <strong>{{ $order->row_no }}</strong><br>
        <span class="muted">{{ __('Date') }}: {{ $order->do_date?->format('d-m-Y') }}</span>
    </div>
</div>

<div class="grid">
    <div>
        <h4>{{ __('Deliver To') }}</h4>
        <strong>{{ $order->consignee ?: ($order->customer->name_en ?? '-') }}</strong><br>
        {{ $order->delivery_address }}<br>
        <span class="muted">{{ $order->contact_person }} {{ $order->contact_phone }}</span>
    </div>
    <div>
        <h4>{{ __('Reference') }}</h4>
        <table>
            <tr><td>{{ __('Customer') }}</td><td>{{ $order->customer->name_en ?? '-' }}</td></tr>
            <tr><td>{{ __('Job') }}</td><td>{{ $order->job->row_no ?? '-' }}</td></tr>
            <tr><td>{{ __('Pickup Location') }}</td><td>{{ $order->pickup_location ?: '-' }}</td></tr>
            <tr><td>{{ __('Planned Delivery') }}</td><td>{{ $order->delivery_date?->format('d-m-Y') ?? '-' }}</td></tr>
        </table>
    </div>
    <div>
        <h4>{{ __('Transport') }}</h4>
        <table>
            <tr><td>{{ __('Transporter') }}</td><td>{{ $order->transporter ?: '-' }}</td></tr>
            <tr><td>{{ __('Vehicle No') }}</td><td>{{ $order->vehicle_no ?: '-' }}</td></tr>
            <tr><td>{{ __('Driver') }}</td><td>{{ $order->driver_name ?: '-' }} {{ $order->driver_phone }}</td></tr>
        </table>
    </div>
    <div>
        <h4>{{ __('Cargo') }}</h4>
        <table>
            <tr><td>{{ __('Container No(s)') }}</td><td>{{ $order->container_no ?: '-' }}</td></tr>
            <tr><td>{{ __('Packages') }}</td><td>{{ $order->packages ?? '-' }}</td></tr>
            <tr><td>{{ __('Weight (kg)') }}</td><td>{{ $order->weight !== null ? number_format($order->weight, 2) : '-' }}</td></tr>
        </table>
    </div>
    @if($order->cargo_description || $order->remarks)
        <div class="full">
            <h4>{{ __('Description / Remarks') }}</h4>
            {{ $order->cargo_description }} {{ $order->remarks }}
        </div>
    @endif
</div>

<p class="note">{{ __('Please deliver the above cargo in good order and obtain the consignee\'s signature and stamp.') }}</p>

<div class="sign">
    <div>{{ __('Issued By') }}</div>
    <div>{{ __('Driver') }}</div>
    <div>{{ __('Received By (Name, Sign & Stamp)') }}</div>
</div>
</body>
</html>
