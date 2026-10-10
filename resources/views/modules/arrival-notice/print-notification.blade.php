<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Cargo Arrival Notification') }} {{ $notice->row_no }}</title>
    <style>
        @page { size: A4; margin: 12mm; }
        * { box-sizing: border-box; }
        body { font-family: Verdana, Arial, Helvetica, sans-serif; color: #111; font-size: 9pt; margin: 0; }
        .co { text-align: center; line-height: 1.35; font-size: 9pt; margin-top: 4px; }
        .co img { max-height: 54px; display: block; margin: 0 auto 6px; }
        .title { background: #d9d9d9; text-align: center; font-weight: bold; font-size: 14pt; padding: 3px 0; margin-top: 8px; }
        .box { border: 1.5px solid #111; padding: 8px 10px; }
        .row { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
        .kv { display: flex; margin: 2px 0; }
        .kv b { width: 74px; flex: none; }
        .rtl { direction: rtl; text-align: right; font-family: 'Segoe UI', Tahoma, Arial, sans-serif; }
        .info { margin-top: 12px; }
        .info .line { display: flex; margin: 3px 0; }
        .info .line b { width: 100px; flex: none; }
        .info .line .v { flex: 1; }
        .info .grid2 { display: flex; gap: 20px; margin: 3px 0; }
        .info .grid2 > div { display: flex; }
        .info .grid2 b { margin-right: 8px; }
        .hr { border-top: 1.5px solid #111; margin: 6px -10px; }
        table.t { width: 100%; border-collapse: collapse; }
        table.t th { text-align: center; padding: 4px; border-bottom: 1.5px solid #111; font-size: 9pt; }
        table.t td { text-align: center; padding: 2px 4px; font-size: 9pt; }
        h4.det { text-align: center; text-decoration: underline; letter-spacing: 3px; margin: 14px 0 4px; font-size: 10pt; }
        .det-grid { display: flex; border-top: 0; }
        .det-grid > div { flex: 1; }
        .det-grid .hd { text-align: center; font-weight: bold; border-bottom: 1.5px solid #111; padding: 4px; margin: 0 4px; }
        .det-grid .ln { display: flex; justify-content: space-between; padding: 3px 6px; font-size: 8.5pt; }
        .imp { text-align: center; font-size: 8.5pt; line-height: 1.35; margin-top: 12px; }
        .ar-imp { font-size: 9pt; line-height: 1.6; margin-top: 6px; }
    </style>
</head>
<body>
@php
    $cust = $notice->customer;
    $det = $notice->detentionTable();
    $fmt = fn($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
    $coAddr = array_filter([$company->address ?? null, $company->city ?? null]);
@endphp
<div class="co">
    <img src="{{ companyLogo() }}" alt="{{ __('Company Logo') }}">
    <strong>{{ $company->name ?? companyName() }}</strong><br>
    @foreach($coAddr as $a){{ $a }}<br>@endforeach
    @if(!empty($company->phone))Tel: {{ $company->phone }}@endif
    @if(!empty($company->email)) · {{ $company->email }}@endif
</div>
<div class="title">{{ __('CARGO ARRIVAL NOTIFICATION') }}</div>

<div class="box">
    <div class="row">
        <div>
            <div>{{ __('To') }}</div>
            <div style="margin-top:2px;">{{ $cust->name_en ?? '' }}</div>
        </div>
        <div>
            <div class="kv"><b>{{ __('Date') }}</b><span>: {{ $notice->notice_date?->format('d-m-Y') }}</span></div>
            <div class="kv"><b>{{ __('Mobile') }}</b><span>: {{ $notice->to_mobile }}</span></div>
            <div class="kv"><b>{{ __('Fax') }}</b><span>: {{ $notice->to_fax }}</span></div>
        </div>
    </div>
    @if(!empty($cust->name_ar))<div class="rtl" style="margin-top:8px;">{{ $cust->name_ar }}</div>@endif

    <div class="row" style="margin-top:14px;">
        <div style="width:46%;">
            <b>{{ __('Dear Sir,') }}</b><br>
            &nbsp;&nbsp;{{ __('Please contact us to collect the delivery order for your Cargo/Containers on the vessel:') }}
        </div>
        <div class="rtl" style="width:50%;">السلام عليكم ورحمة الله وبركاته نأمل مراجعتنا لنستلم بضائعكم/حاوياتكم القادمة على متن</div>
    </div>

    <div class="info">
        <div class="line"><b>{{ __('Shipper') }}</b><span class="v">: {{ $notice->shipper }}</span></div>
        <div class="line"><b>{{ __('B/L No') }}</b><span class="v">: {{ $notice->bl_no }}</span></div>
        <div class="line">
            <b>{{ __('Vessel Name') }}</b><span style="width:230px;">: {{ $notice->vessel_name }}</span>
            <b style="width:92px;">{{ __('Voyage No') }}</b><span style="width:150px;">: {{ $notice->voyage_no }}</span>
            <b style="width:40px;">ETA</b><span>: {{ $notice->eta?->format('j/n/Y') }}</span>
        </div>
        <div class="line">
            <b>POL</b><span style="width:230px;">: {{ $notice->pol }}</span>
            <b style="width:92px;">FPOD</b><span>: {{ $notice->pod }}</span>
        </div>
        <div class="line"><b>{{ __('Consignee') }}</b><span class="v">: {{ $notice->consignee ?: ($cust->name_en ?? '') }}</span></div>
        <div class="line"><b>{{ __('Notify Details') }}</b><span class="v">: {!! nl2br(e($notice->notify_party)) !!}</span></div>
    </div>
    <div class="hr"></div>
    <b>{{ __('Container Nos') }} : {{ $notice->container_nos }}</b>
    <div class="hr" style="margin-top:4px;"></div>
    <table class="t" style="margin-top:-2px;">
        <tr><th style="width:30%;">{{ __('Packages') }}</th><th>{{ __('Description') }}</th></tr>
        <tr><td>{{ $notice->packages }}</td><td>{{ $notice->commodity }}</td></tr>
    </table>
    <div style="border-top:1.5px solid #111;width:160px;margin-top:22px;"></div>

    <h4 class="det">{{ __('LINE DETENTION') }}</h4>
    <div class="det-grid">
        @foreach(['standard' => __('STANDARD CONTAINER'), 'special' => __('SPECIAL CONTAINER'), 'reefer' => __('REEFER CONTAINER')] as $k => $label)
            <div>
                <div class="hd">{{ $label }}</div>
                <div class="ln"><span>{{ __('FREE TIME') }}</span><span>{{ $det[$k]['free'] }} {{ __('DAYS') }}</span></div>
                @foreach($det[$k]['tiers'] as $t)
                    <div class="ln"><span>{{ $t['days'] ? __('NEXT') . ' ' . $t['days'] . ' ' . __('DAYS') : __('THEREAFTER') }}</span><span>SAR {{ $fmt($t['r20']) }}/20'{{ $fmt($t['r40']) }} /40'</span></div>
                @endforeach
            </div>
        @endforeach
    </div>

    <p style="margin:14px 4px 6px;">{{ __('The above table is provided for your easy reference only.') }}</p>
    <p style="margin:6px 4px;">{{ __('Should you have any question regarding then above shipment, please contact') }} <b>{{ $notice->contact_person }}</b></p>
    <p style="margin:12px 4px 4px;">{{ __('of Shipping Department On:') }}</p>
    <div class="info" style="margin-top:0;">
        <div class="line"><b style="width:90px;">Tel</b><span>: {{ $notice->contact_tel }}</span></div>
        <div class="line"><b style="width:90px;">Fax</b><span>: {{ $notice->contact_fax }}</span></div>
        <div class="line"><b style="width:90px;">email</b><span>: {{ $notice->contact_email }}</span></div>
    </div>
    <p style="margin:4px;">{{ __('Please send us your contact email, and mobile number of the contact person.') }}</p>

    <div class="imp">{{ __('IMPORTANT NOTICE circular received from Port customs, all import shipments not collected within the below mentioned duration will be considered as "Abandoned" after which port customs will take action to either auction or dispose the cargo. All related costs will be liable to be settled by the consignee. Perishable Goods 15 days from the date of discharge. General Cargo (which includes RORO, Break-bulk) 30 days from the date of discharge. Effective Date 20th September 2017') }}</div>
    <div class="rtl ar-imp">إعلان هام بالإشارة الى تعميم الذي تلقيناه من جمرك الميناء فان كافة الشحنات الوارد والتي لم يتم استلمها من قبل اصحابها خلال الفترة المذكورة ادناه سوف تعتبر متروكة و عليه فان جمرك الميناء له إتخاذ ما يراه مناسبا لبيع البضاعة بالمزاد أو اتلفها و إن كافة التكاليف سوف تترتب على حساب العميل. البضاعة القابلة للتلف ١٥ يوم من تاريخ الوصول البضاعة العامة (البضائع السائبة و السيارات) ٣٠ يوم من تاريخ الوصول تاريخ التنفيذ ٢٠ سبتمبر ٢٠١٧ (الموافق ١٤٣٩/١٢/٢٩)</div>
    @if($notice->remarks)<p style="font-size:8.5pt;margin:8px 4px 0;">{{ $notice->remarks }}</p>@endif
</div>
</body>
</html>
