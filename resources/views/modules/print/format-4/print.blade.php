<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TAX INVOICE</title>
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: 'Courier New', Consolas, monospace;
            margin: 0;
            padding: 0;
            color: #000;
            font-size: 8.3pt;
            line-height: 1.25;
        }

        .invoice-shell {
            max-width: 794px;
            margin: 0 auto;
            padding: 14px;
            border: 2px solid #000;
        }

        table.grid { width: 100%; border-collapse: collapse; }
        table.grid td, table.grid th { border: 1px solid #000; padding: 3px 6px; vertical-align: top; }

        .tg-title {
            text-align: center;
            font-size: 13pt;
            font-weight: 700;
            letter-spacing: 2px;
            border: 1px solid #000;
            border-bottom: none;
            padding: 4px;
            background: #eee;
        }
        .tg-title .ar { font-size: 10pt; font-weight: 400; direction: rtl; }

        .tg-company {
            display: flex;
            justify-content: space-between;
            border: 1px solid #000;
            border-bottom: none;
            padding: 6px 8px;
        }
        .tg-company .name { font-weight: 700; font-size: 11pt; }
        .tg-company .addr { font-size: 7.8pt; text-align: right; }

        .tg-meta-table td { width: 25%; font-size: 8pt; }
        .tg-meta-table td b { display: block; font-size: 7pt; text-transform: uppercase; color: #333; }

        .tg-parties-table td { width: 50%; font-size: 8pt; vertical-align: top; }
        .tg-parties-table b.hd { display: block; font-size: 7.5pt; text-transform: uppercase; background: #eee; margin: -3px -6px 4px -6px; padding: 2px 6px; border-bottom: 1px solid #000; }

        .tg-items { margin-top: -1px; }
        .tg-items th { background: {{ $settings->primary_color ?? '#000' }}; color: #fff; font-size: 7.5pt; text-transform: uppercase; text-align: center; }
        .tg-items td { text-align: right; font-size: 8pt; }
        .tg-items td.left { text-align: left; }

        .tg-totals-table { margin-top: -1px; }
        .tg-totals-table td { text-align: right; }
        .tg-totals-table td.label { text-align: left; font-weight: 700; width: 70%; }
        .tg-totals-table tr.grand td { font-weight: 700; font-size: 10pt; }
        .tg-totals-table tr.bal td { font-style: italic; }

        .tg-words { border: 1px solid #000; border-top: none; padding: 6px 8px; font-size: 7.8pt; }

        .tg-footer-table td { width: 50%; vertical-align: top; font-size: 7.8pt; }
        .tg-footer-table .qr { text-align: center; }

        .tg-note { text-align: center; font-size: 7pt; border: 1px solid #000; border-top: none; padding: 4px; }

        .draft-watermark {
            position: fixed; top: 40%; left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 150px; color: rgba(0,0,0,0.08); font-weight: 800;
            text-transform: uppercase; z-index: 2; pointer-events: none;
            white-space: nowrap;
        }

        .container-table, .package-table { width: 100%; border-collapse: collapse; font-size: 7.8pt; margin-top: -1px; }
        .container-table th, .package-table th { border: 1px solid #000; padding: 3px 6px; background: #eee; }
        .container-table td, .package-table td { border: 1px solid #000; padding: 3px 6px; }

        @media print {
            @page { margin: 8mm; }
            .invoice-shell { padding: 6px; }
        }
    </style>
    @php
        $__colorCssTpl = '.tg-items th { background: __COLOR__; }';
    @endphp
    <style id="dynamic-color-style">{!! str_replace('__COLOR__', $settings->primary_color ?? '#000', $__colorCssTpl) !!}</style>
    <script id="color-css-template" type="application/json">{!! json_encode($__colorCssTpl) !!}</script>
</head>
<body>
<div class="invoice-shell">
    @if($customerInvoice->status == 1)
        <div class="draft-watermark">DRAFT</div>
    @endif

    <div class="tg-title">TAX INVOICE <span class="ar">فاتورة ضريبية</span></div>

    <div class="tg-company">
        <div>
            <div class="name">{{ $company->name }}</div>
            <div>{{ $company->name_ar }}</div>
        </div>
        <div class="addr">
            <div>{{ $company->address_1 }}, {{ $company->city }} {{ $company->postal_code }}</div>
            @if($company->tax_number)
                <div>CR: {{ $company->cr_number }} | VAT: {{ $company->tax_number }}</div>
            @endif
            <div data-toggle="show_phone" style="{{ ($settings->show_phone ?? true) ? '' : 'display:none' }}">{{ $company->phone ?? '' }}</div>
        </div>
    </div>

    <table class="grid tg-meta-table">
        <tr>
            <td><b>Invoice No.</b>{{ $customerInvoice->row_no }}</td>
            <td><b>Invoice Date</b>{{ \Carbon\Carbon::parse($customerInvoice->invoice_date)->format('d-M-Y') }}@if(\Carbon\Carbon::parse($customerInvoice->invoice_date)->isToday())<span data-toggle="show_time" style="{{ ($settings->show_time ?? false) ? '' : 'display:none' }}"> {{ \Carbon\Carbon::parse($customerInvoice->created_at)->format('H:i') }}</span>@endif</td>
            <td><b>Due Date</b>{{ \Carbon\Carbon::parse($customerInvoice->due_date)->format('d-M-Y') }}</td>
            <td><b>Job No.</b>{{ $customerInvoice->job?->row_no }} / {{ \Carbon\Carbon::parse($customerInvoice->job?->posted_at)->format('d-M-Y') }}</td>
        </tr>
    </table>

    <table class="grid tg-parties-table">
        <tr>
            <td>
                <b class="hd">Bill To / Party Details</b>
                <div style="font-weight:700;">{{ $customerInvoice->customer->name_en }}</div>
                <div>{{ $customerInvoice->customer->address1_en }}</div>
                <div>{{ $customerInvoice->customer->city_en }}, {{ $customerInvoice->customer->country }} {{ $customerInvoice->customer->postal_code }}</div>
                <div data-toggle="show_phone" style="{{ ($settings->show_phone ?? true) ? '' : 'display:none' }}">Ph: {{ $customerInvoice->customer->phone }}</div>
                <div>VAT/CR: {{ $customerInvoice->customer->vat_number }} / {{ $customerInvoice->customer->cr_number }}</div>
                @foreach($extraPartyFields ?? [] as $field)
                    <div data-toggle="{{ $field['key'] }}" style="{{ $field['visible'] ? '' : 'display:none' }}">{{ $field['label_en'] }}: {{ $field['value'] }}</div>
                @endforeach
            </td>
            <td>
                <b class="hd">Shipment / Job Details</b>
                <div>Shipper: {{ $customerInvoice->job?->shipper }}</div>
                <div>Consignee: {{ $customerInvoice->job?->consignee }}</div>
                <div data-toggle="awb_hbl" style="{{ ($settings->awb_hbl ?? false) ? '' : 'display:none' }}">AWB/HBL: {{ collect([$customerInvoice->job?->awb_number, $customerInvoice->job?->hbl_number])->filter()->implode(' / ') }}</div>
                <div data-toggle="pol_pod" style="{{ ($settings->pol_pod ?? false) ? '' : 'display:none' }}">POL/POD: {{ $customerInvoice->job?->pol }} / {{ $customerInvoice->job?->pod }}</div>
                <div data-toggle="carrier" style="{{ ($settings->carrier ?? false) ? '' : 'display:none' }}">Carrier: {{ $customerInvoice->job?->carrier }}</div>
                <div data-toggle="voyage_flight" style="{{ ($settings->voyage_flight ?? false) ? '' : 'display:none' }}">Voy/Flight: {{ $customerInvoice->job?->voyage_flight_no }}</div>
                <div data-toggle="incoterm" style="{{ ($settings->incoterm ?? false) ? '' : 'display:none' }}">Incoterm: {{ $customerInvoice->job?->incoterm }}</div>
                <div data-toggle="shipment_mode" style="{{ ($settings->shipment_mode ?? false) ? '' : 'display:none' }}">Mode: {{ $customerInvoice->job?->shipment_mode }}</div>
                @foreach($extraJobFields ?? [] as $field)
                    <div data-toggle="{{ $field['key'] }}" style="{{ $field['visible'] ? '' : 'display:none' }}">{{ $field['label_en'] }}: {{ $field['value'] }}</div>
                @endforeach
            </td>
        </tr>
    </table>

    <table class="grid tg-items">
        <thead>
        <tr>
            <th style="width:26%;">Description</th>
            <th data-toggle="hsn_sac" style="{{ ($settings->hsn_sac ?? false) ? '' : 'display:none' }}">HSN</th>
            <th data-toggle="rate" style="{{ ($settings->rate ?? true) ? '' : 'display:none' }}">Rate</th>
            <th>Qty</th>
            <th data-toggle="unit" style="{{ ($settings->unit ?? true) ? '' : 'display:none' }}">Unit</th>
            <th data-toggle="discount" style="{{ ($settings->discount ?? false) ? '' : 'display:none' }}">Disc.</th>
            <th>Amount</th>
            <th>VAT%</th>
            <th>VAT Amt</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($customerInvoice->customerInvoiceSubs as $items)
            <tr>
                <td class="left">
                    {{ $items->description }}
                    @if($items->comment)<span data-toggle="item_description" style="{{ ($settings->item_description ?? true) ? '' : 'display:none' }}"> - {{ $items->comment }}</span>@endif
                </td>
                <td data-toggle="hsn_sac" style="{{ ($settings->hsn_sac ?? false) ? '' : 'display:none' }}">-</td>
                <td data-toggle="rate" style="{{ ($settings->rate ?? true) ? '' : 'display:none' }}">{{ amountFormat($items->unit_price) }}</td>
                <td>{{ $items->quantity }}</td>
                <td data-toggle="unit" style="{{ ($settings->unit ?? true) ? '' : 'display:none' }}">{{ $items->unit }}</td>
                <td data-toggle="discount" style="{{ ($settings->discount ?? false) ? '' : 'display:none' }}">0.00</td>
                <td>{{ amountFormat($items->total) }}</td>
                <td>{{ $items->tax_percent }}</td>
                <td>{{ amountFormat($items->tax_amount) }}</td>
                <td>{{ amountFormat($items->total_with_tax) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="grid tg-totals-table">
        <tr><td class="label">Subtotal</td><td>{{ amountFormat($customerInvoice->sub_total) }} {{ $customerInvoice->currency }}</td></tr>
        <tr><td class="label">VAT Total</td><td>{{ amountFormat($customerInvoice->tax_total) }} {{ $customerInvoice->currency }}</td></tr>
        <tr class="grand"><td class="label">GRAND TOTAL</td><td>{{ amountFormat($customerInvoice->grand_total) }} {{ $customerInvoice->currency }}</td></tr>
        <tr class="bal" data-toggle="party_balance" style="{{ ($settings->party_balance ?? false) ? '' : 'display:none' }}"><td class="label">Outstanding Balance</td><td>{{ amountFormat($customerBalance) }} {{ $company->currency ?? 'SAR' }}</td></tr>
    </table>

    <div class="tg-words">
        <div>Amount in Words: {{ amountInWords(round($customerInvoice->grand_total, 2)) }}</div>
        <div style="direction: rtl;">{{ convert(round($customerInvoice->grand_total, 2)) }}</div>
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

    <table class="grid tg-footer-table">
        <tr>
            <td>
                <b class="hd" style="display:block; font-size:7.5pt; text-transform:uppercase; background:#eee; margin:-3px -6px 4px -6px; padding:2px 6px; border-bottom:1px solid #000;">Bank Details</b>
                @include('modules.print.partials.bank-details')
            </td>
            <td class="qr">
                @include('modules.print.partials.zatca-qr')
            </td>
        </tr>
    </table>

    <div class="tg-note">
        This is a computer generated document and does not require a signature / هذا المستند تم انتاجه بواسطة الكمبيوتر ولا يحتاج الى توقيع
    </div>
</div>
</body>
</html>
