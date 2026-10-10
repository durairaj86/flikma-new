<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Cargo Arrival Notice') }} {{ $notice->row_no }}</title>
    <style>
        @page { size: A4; margin: 12mm; }
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #111; font-size: 9.5pt; margin: 0; }
        .top { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
        .top .co { font-size: 8.5pt; line-height: 1.35; max-width: 38%; }
        .top .co strong { font-size: 9.5pt; }
        .top img { max-height: 58px; max-width: 190px; }
        .top h1 { font-family: Georgia, 'Times New Roman', serif; font-size: 20pt; margin: 6px 0 0; text-align: right; }
        .meta { margin-top: 14px; border-bottom: 1px solid #111; }
        .meta .row { display: flex; padding: 3px 0; align-items: baseline; }
        .meta .row b { width: 70px; font-size: 10pt; }
        .meta .row span { flex: 1; }
        .meta .serial { margin-left: auto; font-family: 'Courier New', monospace; font-weight: bold; }
        .lead { font-family: Georgia, 'Times New Roman', serif; font-weight: bold; font-size: 11pt; margin: 8px 0 4px; }
        .box { border: 1px solid #111; }
        .cols { display: flex; }
        .cols > div { padding: 6px 8px; font-family: 'Courier New', monospace; font-size: 9pt; line-height: 1.45; }
        .cols .left { width: 56%; border-right: 1px solid #111; }
        .cols .right { width: 44%; }
        .cap { font-weight: bold; text-decoration: underline; display: block; margin-top: 4px; }
        .cap:first-child { margin-top: 0; }
        .kv { display: flex; margin-bottom: 5px; }
        .kv b { width: 112px; flex: none; }
        .sect { border-top: 1px solid #111; padding: 5px 8px; font-family: 'Courier New', monospace; font-size: 9pt; }
        .sect .cap { font-family: 'Courier New', monospace; }
        .nos { display: flex; justify-content: space-between; }
        .serif { font-family: Georgia, 'Times New Roman', serif; }
        .big { font-weight: bold; font-size: 11pt; line-height: 1.35; padding: 8px; border-top: 1px solid #111; }
        .pay { display: flex; border-top: 1px solid #111; }
        .pay > div { padding: 6px 8px; font-family: Georgia, 'Times New Roman', serif; font-weight: bold; font-size: 9pt; }
        .pay .l { width: 44%; border-right: 1px solid #111; }
        .pay .r { width: 56%; text-align: right; }
        .foot { border: 1px solid #111; border-top: 0; text-align: center; font-size: 8pt; padding: 7px; margin-top: 0; }
    </style>
</head>
<body>
@php
    $tel = $company->phone ?? '';
    $cust = $notice->customer;
    $cityLine = trim(($company->address ?? '') . ' ' . ($company->city ?? ''));
    $nos = trim((string) $notice->container_nos);
    $freeDays = (int) $notice->free_days;
    $carrier = $notice->carrier_name ?: __('the carrier');
@endphp
<div class="top">
    <div class="co">
        <strong>{{ $company->name ?? companyName() }}</strong><br>
        {{ $cityLine }}<br>
        @if($tel)Tel: {{ $tel }}<br>@endif
        @if(!empty($company->email))Email: {{ $company->email }}@endif
    </div>
    <div style="text-align:center;flex:1;"><img src="{{ companyLogo() }}" alt="{{ __('Company Logo') }}"></div>
    <h1>{{ __('Cargo Arrival Notice') }}</h1>
</div>

<div class="meta">
    <div class="row"><b>{{ __('Date') }} :</b><span>{{ $notice->notice_date?->format('d M Y') }}</span></div>
</div>
<div class="meta" style="border-top:0;">
    <div class="row"><b>{{ __('To M/S') }}:</b><span>{{ $cust->name_en ?? '' }}</span></div>
    <div class="row"><b>{{ __('Fax') }}#:</b><span>{{ $notice->to_fax }}</span></div>
    <div class="row"><b>{{ __('From') }}:</b><span>{{ $company->name ?? companyName() }} {{ __('Notification Department') }}{{ $tel ? ' · Tel: ' . $tel : '' }}</span></div>
    <div class="row"><b>{{ __('Subject') }}:</b><span>{{ $notice->subject ?: 'Vessel Arriving At ' . $notice->pod }}</span><span class="serial">{{ __('Serial#') }} &nbsp; {{ $notice->row_no }}</span></div>
</div>

<div class="lead">{{ __('We are pleased to announce the expected arrival of your cargo to :port as shown below.', ['port' => $notice->pod]) }}</div>

<div class="box">
    <div class="cols">
        <div class="left">
            <span class="cap">{{ __('Consignee') }}:</span>
            {{ $notice->consignee ?: ($cust->name_en ?? '') }}<br>
            {!! nl2br(e($notice->consignee_address)) !!}
            @if($notice->to_mobile)<br>Tel:{{ $notice->to_mobile }}@endif
            <span class="cap">{{ __('Shipper') }}:</span>
            {{ $notice->shipper }}
            <span class="cap">{{ __('1st Notify') }}:</span>
            {!! nl2br(e($notice->notify_party ?: ($notice->consignee . "\n" . $notice->consignee_address))) !!}
        </div>
        <div class="right">
            <div class="kv"><b>{{ __('Vessel') }}</b><span>: {{ $notice->vessel_name }}</span></div>
            <div class="kv"><b>{{ __('Voyage') }}</b><span>: {{ $notice->voyage_no }}</span></div>
            <div class="kv"><b>E.T.A /ARVD</b><span>: {{ $notice->eta?->format('d-M-y') }}</span></div>
            <div class="kv"><b>{{ __('Load Port') }}</b><span>: {{ $notice->pol }}</span></div>
            <div class="kv"><b>{{ __('Discharge Port') }}</b><span>: <strong>{{ $notice->pod }}</strong></span></div>
            <div class="kv"><b>{{ __('Final Dest') }}</b><span>: {{ $notice->final_destination ?: $notice->pod }}</span></div>
            <div class="kv"><b>B/L(s)</b><span>: {{ $notice->bl_no }}</span></div>
        </div>
    </div>
    <div class="sect nos"><span><b>Nos<br>{{ __('Container(s)') }}</b></span><span>{{ (int) $notice->containers_20 }} <b>x 20'</b></span><span>{{ (int) $notice->containers_40 }} <b>x 40'</b></span></div>
    <div class="sect"><span class="cap">{{ __('Commodity Details') }}</span>{{ $notice->commodity }}@if($notice->packages) &middot; {{ $notice->packages }} {{ __('packages') }}@endif</div>
    <div class="sect serif" style="min-height:48px;">
        <b>{{ __('Container Number(s)') }}</b> <span style="font-size:8pt;">({{ __('If more then 10 container is there please ask for manifest copy for your reference.') }})</span><br>
        <span style="font-family:Arial;">{{ $nos }}</span>
    </div>
    <div class="big serif">
        {{ __('Dear Customers/:port Now, you can return Empty Boxes of :carrier in our :depot without any additional cost. Please collect delivery order against Original bill of lading and arrange customs clearance within permitted free time and return empty container(s) to our yard within :days days from the date of discharge, thereafter, line detention will be charged.', ['port' => $notice->pod, 'carrier' => $carrier, 'depot' => $notice->return_depot ?: __('depot'), 'days' => $freeDays]) }}
    </div>
    <div class="pay">
        <div class="l">{{ __('As per MAWANI instructions, cash will not be accepted for the payments at our shipping offices. Please deposit the related charges in the following bank account or pay through certified cheque.') }}</div>
        <div class="r">
            @if($bank)
                IBAN NO: {{ $bank->iban_code }}<br>
                {{ $bank->bank_name }} @if($bank->swift_code)Swift Code: {{ $bank->swift_code }}@endif<br><br>
            @endif
            @if($notice->contact_person){{ __('For payment advice please contact') }} {{ $notice->contact_person }}<br>@endif
            @if($notice->contact_tel)T: {{ $notice->contact_tel }}@endif @if($notice->contact_email) Email: {{ $notice->contact_email }}@endif
        </div>
    </div>
</div>
<div class="foot">{{ __('Notice') }} : {{ __('Mawani may auction or destroy the cargo, if it is not cleared from the port within 30 days of discharge from ship.') }}</div>
@if($notice->remarks)<p style="font-size:8.5pt;margin-top:8px;">{{ $notice->remarks }}</p>@endif
</body>
</html>
