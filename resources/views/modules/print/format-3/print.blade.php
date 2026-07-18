<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TAX INVOICE</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Lato:wght@400;700&display=swap');

        * { box-sizing: border-box; }

        body {
            font-family: 'Lato', Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #2b2b2b;
            font-size: 9pt;
            line-height: 1.35;
        }

        .invoice-shell {
            max-width: 794px;
            margin: 0 auto;
            padding: 28px 34px 40px 34px;
        }

        .lux-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 16px;
            border-bottom: 2px solid {{ $settings->primary_color ?? '#0b6aa0' }};
            margin-bottom: 16px;
        }

        .lux-header .brand img { width: 250px; height: 100px; object-fit: contain; object-position: left center; }
        .lux-header .brand .company-name {
            font-family: 'Playfair Display', serif;
            font-size: 16pt;
            font-weight: 700;
            color: {{ $settings->primary_color ?? '#0b6aa0' }};
            margin-top: 4px;
        }
        .lux-header .brand .company-name-ar { font-size: 11pt; color: #555; }

        .lux-header .meta { text-align: right; font-size: 8pt; line-height: 1.4; }
        .lux-header .meta p { margin: 0; }
        .lux-header .meta .cr-vat { font-weight: 700; margin-top: 4px; }

        .lux-title {
            text-align: center;
            margin: 0 0 18px 0;
        }
        .lux-title h1 {
            font-family: 'Playfair Display', serif;
            font-size: 20pt;
            letter-spacing: 3px;
            font-weight: 700;
            color: {{ $settings->primary_color ?? '#0b6aa0' }};
            margin: 0;
        }
        .lux-title .arabic { font-size: 12pt; direction: rtl; color: #555; margin-top: 2px; }

        .lux-panels {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 18px;
        }

        .lux-panel {
            border: 1px solid #e3e3e3;
            border-radius: 6px;
            padding: 12px 14px;
        }
        .lux-panel h4 {
            font-family: 'Playfair Display', serif;
            font-size: 9.5pt;
            color: {{ $settings->primary_color ?? '#0b6aa0' }};
            margin: 0 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .lux-panel table { width: 100%; font-size: 8.5pt; }
        .lux-panel table td { padding: 2px 0; vertical-align: top; }
        .lux-panel table td:first-child { font-weight: 700; width: 42%; color: #555; }

        .lux-meta-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 18px;
            background: {{ $settings->primary_color ?? '#0b6aa0' }}0d;
            border-radius: 6px;
            padding: 10px 14px;
        }
        .lux-meta-row .box { text-align: center; }
        .lux-meta-row .box .label { font-size: 7pt; text-transform: uppercase; letter-spacing: 0.5px; color: #777; }
        .lux-meta-row .box .value { font-size: 9pt; font-weight: 700; color: #2b2b2b; }

        .lux-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 8.5pt; }
        .lux-table thead th {
            background: {{ $settings->primary_color ?? '#0b6aa0' }};
            color: #fff;
            padding: 8px 10px;
            font-weight: 700;
            text-align: left;
        }
        .lux-table thead th.num { text-align: right; }
        .lux-table tbody td { padding: 7px 10px; border-bottom: 1px solid #eee; }
        .lux-table tbody td.num { text-align: right; }
        .lux-table tbody tr:last-child td { border-bottom: none; }
        .lux-table .item-comment { color: #888; font-size: 8pt; }

        .lux-totals { display: flex; justify-content: flex-end; margin-bottom: 18px; }
        .lux-totals table { width: 300px; font-size: 8.5pt; border-collapse: collapse; }
        .lux-totals td { padding: 4px 8px; }
        .lux-totals td:first-child { color: #555; }
        .lux-totals td.num { text-align: right; }
        .lux-totals tr.grand { border-top: 2px solid {{ $settings->primary_color ?? '#0b6aa0' }}; font-weight: 700; font-size: 11pt; color: {{ $settings->primary_color ?? '#0b6aa0' }}; }
        .lux-totals tr.balance td { font-style: italic; color: #a33; }

        .lux-words { font-size: 8pt; color: #666; margin-bottom: 14px; }
        .lux-words .ar { direction: rtl; }

        .lux-footer { display: flex; justify-content: space-between; gap: 20px; margin-top: 20px; padding-top: 14px; border-top: 1px solid #e3e3e3; }
        .lux-footer .bank { font-size: 8pt; width: 55%; }
        .lux-footer .bank h4 { font-family: 'Playfair Display', serif; font-size: 9pt; color: {{ $settings->primary_color ?? '#0b6aa0' }}; margin: 0 0 6px 0; }
        .lux-footer .qr { text-align: right; }

        .lux-note { text-align: center; font-size: 7.5pt; color: #999; margin-top: 20px; }

        .draft-watermark {
            position: fixed; top: 40%; left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 150px; color: rgba(0,0,0,0.08); font-weight: 800;
            text-transform: uppercase; z-index: 2; pointer-events: none;
            white-space: nowrap;
        }

        .container-table, .package-table { width: 100%; border-collapse: collapse; font-size: 8pt; margin-top: 10px; }
        .container-table th, .package-table th { background: #f4f4f4; padding: 4px 8px; text-align: left; border-bottom: 1px solid #ddd; }
        .container-table td, .package-table td { padding: 4px 8px; border-bottom: 1px solid #eee; }

        @media print {
            @page { margin: 12mm; }
            .invoice-shell { padding: 0; max-width: 100%; }
        }
    </style>
    @php
        $__colorCssTpl = '
            .lux-header { border-bottom: 2px solid __COLOR__; }
            .lux-header .brand .company-name { color: __COLOR__; }
            .lux-title h1 { color: __COLOR__; }
            .lux-panel h4 { color: __COLOR__; }
            .lux-meta-row { background: __COLOR__0d; }
            .lux-table thead th { background: __COLOR__; }
            .lux-totals tr.grand { border-top: 2px solid __COLOR__; color: __COLOR__; }
            .lux-footer .bank h4 { color: __COLOR__; }
        ';
    @endphp
    <style id="dynamic-color-style">{!! str_replace('__COLOR__', $settings->primary_color ?? '#0b6aa0', $__colorCssTpl) !!}</style>
    <script id="color-css-template" type="application/json">{!! json_encode($__colorCssTpl) !!}</script>
</head>
<body>
<div class="invoice-shell">
    @if($customerInvoice->status == 1)
        <div class="draft-watermark">DRAFT</div>
    @endif

    <div class="lux-header">
        <div class="brand">
            <img src="{{ companyLogo() }}" alt="logo">
            <div class="company-name">{{ $company->name }}</div>
            <div class="company-name-ar">{{ $company->name_ar }}</div>
        </div>
        <div class="meta">
            <p>{{ $company->address_1 }}</p>
            <p>{{ $company->city }} {{ $company->postal_code }}, {{ $company->city_sub_division }}</p>
            <p>SAUDI ARABIA</p>
            <p data-toggle="show_phone" style="{{ ($settings->show_phone ?? true) ? '' : 'display:none' }}">{{ $company->phone ?? '' }}</p>
            @if($company->tax_number)
                <p class="cr-vat">CR: {{ $company->cr_number }} &nbsp;|&nbsp; VAT: {{ $company->tax_number }}</p>
            @endif
        </div>
    </div>

    <div class="lux-title">
        <h1>TAX INVOICE</h1>
        <div class="arabic">فاتورة ضريبية</div>
    </div>

    <div class="lux-panels">
        <div class="lux-panel">
            <h4>Bill To</h4>
            <table>
                <tr><td colspan="2" style="font-weight:700; font-size:9.5pt; color:#2b2b2b;">{{ $customerInvoice->customer->name_en }}</td></tr>
                <tr><td colspan="2">{{ $customerInvoice->customer->address1_en }}</td></tr>
                <tr><td colspan="2">{{ $customerInvoice->customer->city_en }}, {{ $customerInvoice->customer->country }} {{ $customerInvoice->customer->postal_code }}</td></tr>
                <tr data-toggle="show_phone" style="{{ ($settings->show_phone ?? true) ? '' : 'display:none' }}"><td>Phone</td><td>{{ $customerInvoice->customer->phone }}</td></tr>
                <tr><td>VAT / CR No.</td><td>{{ $customerInvoice->customer->vat_number }} / {{ $customerInvoice->customer->cr_number }}</td></tr>
                @foreach($extraPartyFields ?? [] as $field)
                    <tr data-toggle="{{ $field['key'] }}" style="{{ $field['visible'] ? '' : 'display:none' }}"><td>{{ $field['label_en'] }}</td><td>{{ $field['value'] }}</td></tr>
                @endforeach
            </table>
        </div>
        <div class="lux-panel">
            <h4>Shipment Details</h4>
            <table>
                <tr><td>Shipper</td><td>{{ $customerInvoice->job?->shipper }}</td></tr>
                <tr><td>Consignee</td><td>{{ $customerInvoice->job?->consignee }}</td></tr>
                <tr data-toggle="awb_hbl" style="{{ ($settings->awb_hbl ?? false) ? '' : 'display:none' }}"><td>AWB / HBL No.</td><td>{{ collect([$customerInvoice->job?->awb_number, $customerInvoice->job?->hbl_number])->filter()->implode(' / ') }}</td></tr>
                <tr data-toggle="pol_pod" style="{{ ($settings->pol_pod ?? false) ? '' : 'display:none' }}"><td>Origin / Destination</td><td>{{ $customerInvoice->job?->pol }} &rarr; {{ $customerInvoice->job?->pod }}</td></tr>
                <tr data-toggle="carrier" style="{{ ($settings->carrier ?? false) ? '' : 'display:none' }}"><td>Vessel / Flight</td><td>{{ $customerInvoice->job?->carrier }}</td></tr>
                <tr data-toggle="voyage_flight" style="{{ ($settings->voyage_flight ?? false) ? '' : 'display:none' }}"><td>Voyage / Flight No.</td><td>{{ $customerInvoice->job?->voyage_flight_no }}</td></tr>
                <tr data-toggle="incoterm" style="{{ ($settings->incoterm ?? false) ? '' : 'display:none' }}"><td>Incoterm</td><td>{{ $customerInvoice->job?->incoterm }}</td></tr>
                <tr data-toggle="shipment_mode" style="{{ ($settings->shipment_mode ?? false) ? '' : 'display:none' }}"><td>Shipment Mode</td><td>{{ $customerInvoice->job?->shipment_mode }}</td></tr>
                @foreach($extraJobFields ?? [] as $field)
                    <tr data-toggle="{{ $field['key'] }}" style="{{ $field['visible'] ? '' : 'display:none' }}"><td>{{ $field['label_en'] }}</td><td>{{ $field['value'] }}</td></tr>
                @endforeach
            </table>
        </div>
    </div>

    <div class="lux-meta-row">
        <div class="box"><div class="label">Invoice No.</div><div class="value">{{ $customerInvoice->row_no }}</div></div>
        <div class="box"><div class="label">Invoice Date</div><div class="value">{{ \Carbon\Carbon::parse($customerInvoice->invoice_date)->format('d M Y') }}@if(\Carbon\Carbon::parse($customerInvoice->invoice_date)->isToday())<span data-toggle="show_time" style="{{ ($settings->show_time ?? false) ? '' : 'display:none' }}"> {{ \Carbon\Carbon::parse($customerInvoice->created_at)->format('h:i A') }}</span>@endif</div></div>
        <div class="box"><div class="label">Due Date</div><div class="value">{{ \Carbon\Carbon::parse($customerInvoice->due_date)->format('d M Y') }}</div></div>
        <div class="box"><div class="label">Job No.</div><div class="value">{{ $customerInvoice->job?->row_no }}</div></div>
    </div>

    <table class="lux-table">
        <thead>
        <tr>
            <th>Description</th>
            <th data-toggle="hsn_sac" style="{{ ($settings->hsn_sac ?? false) ? '' : 'display:none' }}">HSN/SAC</th>
            <th class="num" data-toggle="rate" style="{{ ($settings->rate ?? true) ? '' : 'display:none' }}">Rate</th>
            <th class="num">Qty</th>
            <th data-toggle="unit" style="{{ ($settings->unit ?? true) ? '' : 'display:none' }}">Unit</th>
            <th class="num" data-toggle="discount" style="{{ ($settings->discount ?? false) ? '' : 'display:none' }}">Discount</th>
            <th class="num">Amount</th>
            <th class="num">VAT %</th>
            <th class="num">VAT Amt</th>
            <th class="num">Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($customerInvoice->customerInvoiceSubs as $items)
            <tr>
                <td>
                    {{ $items->description }}
                    @if($items->comment)
                        <div class="item-comment" data-toggle="item_description" style="{{ ($settings->item_description ?? true) ? '' : 'display:none' }}">{{ $items->comment }}</div>
                    @endif
                </td>
                <td data-toggle="hsn_sac" style="{{ ($settings->hsn_sac ?? false) ? '' : 'display:none' }}">-</td>
                <td class="num" data-toggle="rate" style="{{ ($settings->rate ?? true) ? '' : 'display:none' }}">{{ amountFormat($items->unit_price) }}</td>
                <td class="num">{{ $items->quantity }}</td>
                <td data-toggle="unit" style="{{ ($settings->unit ?? true) ? '' : 'display:none' }}">{{ $items->unit }}</td>
                <td class="num" data-toggle="discount" style="{{ ($settings->discount ?? false) ? '' : 'display:none' }}">0.00</td>
                <td class="num">{{ amountFormat($items->total) }}</td>
                <td class="num">{{ $items->tax_percent }}%</td>
                <td class="num">{{ amountFormat($items->tax_amount) }}</td>
                <td class="num">{{ amountFormat($items->total_with_tax) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="lux-words">
        <div>{{ amountInWords(round($customerInvoice->grand_total, 2)) }}</div>
        <div class="ar">{{ convert(round($customerInvoice->grand_total, 2)) }}</div>
    </div>

    <div class="lux-totals">
        <table>
            <tr><td>Subtotal</td><td class="num">{{ amountFormat($customerInvoice->sub_total) }} {{ $customerInvoice->currency }}</td></tr>
            <tr><td>VAT</td><td class="num">{{ amountFormat($customerInvoice->tax_total) }} {{ $customerInvoice->currency }}</td></tr>
            <tr class="grand"><td>Grand Total</td><td class="num">{{ amountFormat($customerInvoice->grand_total) }} {{ $customerInvoice->currency }}</td></tr>
            <tr class="balance" data-toggle="party_balance" style="{{ ($settings->party_balance ?? false) ? '' : 'display:none' }}"><td>Outstanding Balance</td><td class="num">{{ amountFormat($customerBalance) }} {{ $company->currency ?? 'SAR' }}</td></tr>
        </table>
    </div>

    @if(isset($jobContainers) && filled($jobContainers))
        <table class="container-table">
            <thead><tr><th>Container Number</th><th>No. of Containers</th><th>Type</th></tr></thead>
            <tbody>
            @foreach($jobContainers as $container)
                <tr><td>{{ $container['container_number'] }}</td><td>{{ $container['qty'] ?? '' }}</td><td>{{ $container['container_size'] ? containerSize($container['container_size']) : '' }}</td></tr>
            @endforeach
            </tbody>
        </table>
    @endif
    @if(isset($jobPackages) && filled($jobPackages))
        <table class="package-table">
            <thead><tr><th>Commodity</th><th>Pcs</th><th>Pack Type</th><th>G.Weight</th><th>Volume</th></tr></thead>
            <tbody>
            @foreach($jobPackages as $package)
                <tr>
                    <td>{{ $package->description_goods }}</td>
                    <td>{{ $package->quantity }}</td>
                    <td>{{ ucfirst($package->package_type) }}</td>
                    <td>{{ $package->package_weight }}</td>
                    <td>{{ $package->volume }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <div class="lux-footer">
        <div class="bank">
            <h4>Bank Details</h4>
            @include('modules.print.partials.bank-details')
        </div>
        <div class="qr">
            @include('modules.print.partials.zatca-qr')
        </div>
    </div>

    <div class="lux-note">
        This is a computer generated document and does not require a signature<br>
        هذا المستند تم انتاجه بواسطة الكمبيوتر ولا يحتاج الى توقيع
    </div>
</div>
</body>
</html>
