<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TAX INVOICE</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #1f2937;
            font-size: 9pt;
            line-height: 1.4;
        }

        .invoice-shell {
            max-width: 794px;
            margin: 0 auto;
            padding: 30px 32px 40px 32px;
        }

        .bb-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 18px; }
        .bb-header .brand img { width: 250px; height: 100px; object-fit: contain; object-position: left center; margin-bottom: 6px; }
        .bb-header .brand .name { font-size: 14pt; font-weight: 700; color: #111827; }
        .bb-header .brand .name-ar { font-size: 10pt; color: #6b7280; }
        .bb-header .invoice-tag {
            text-align: right;
        }
        .bb-header .invoice-tag .badge {
            display: inline-block;
            background: {{ $settings->primary_color ?? '#0b6aa0' }};
            color: #fff;
            font-size: 11pt;
            font-weight: 700;
            padding: 6px 16px;
            border-radius: 4px;
            letter-spacing: 1px;
        }
        .bb-header .invoice-tag .arabic { font-size: 9pt; color: #6b7280; margin-top: 4px; }

        .bb-info-strip {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            background: #f9fafb;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 16px;
        }
        .bb-info-strip .item .label { font-size: 7pt; color: #6b7280; text-transform: uppercase; letter-spacing: 0.4px; }
        .bb-info-strip .item .value { font-size: 9pt; font-weight: 600; color: #111827; }

        .bb-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px; }
        .bb-card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 14px; }
        .bb-card h5 { margin: 0 0 8px 0; font-size: 8pt; text-transform: uppercase; letter-spacing: 0.5px; color: {{ $settings->primary_color ?? '#0b6aa0' }}; font-weight: 700; }
        .bb-card table { width: 100%; font-size: 8.3pt; }
        .bb-card table td { padding: 2px 0; }
        .bb-card table td:first-child { color: #6b7280; width: 40%; }

        table.bb-items { width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 8.5pt; }
        table.bb-items thead th { text-align: left; font-size: 7.5pt; text-transform: uppercase; color: #6b7280; padding: 6px 8px; border-bottom: 2px solid #e5e7eb; }
        table.bb-items thead th.num { text-align: right; }
        table.bb-items tbody td { padding: 8px; border-bottom: 1px solid #f3f4f6; }
        table.bb-items tbody td.num { text-align: right; }
        table.bb-items .comment { color: #9ca3af; font-size: 7.8pt; }

        .bb-totals { display: flex; justify-content: flex-end; margin-bottom: 16px; }
        .bb-totals table { width: 280px; font-size: 8.5pt; }
        .bb-totals td { padding: 4px 0; }
        .bb-totals td:first-child { color: #6b7280; }
        .bb-totals td.num { text-align: right; }
        .bb-totals tr.grand td { border-top: 2px solid {{ $settings->primary_color ?? '#0b6aa0' }}; font-weight: 700; font-size: 11pt; color: {{ $settings->primary_color ?? '#0b6aa0' }}; padding-top: 8px; }
        .bb-totals tr.balance td { color: #b91c1c; font-size: 8pt; }

        .bb-words { font-size: 8pt; color: #6b7280; margin-bottom: 16px; }
        .bb-words .ar { direction: rtl; }

        .bb-footer { display: flex; justify-content: space-between; gap: 20px; border-top: 1px solid #e5e7eb; padding-top: 14px; margin-top: 10px; }
        .bb-footer .bank { font-size: 8pt; width: 55%; }
        .bb-footer .bank h5 { margin: 0 0 6px 0; font-size: 8pt; text-transform: uppercase; color: {{ $settings->primary_color ?? '#0b6aa0' }}; font-weight: 700; }
        .bb-footer .qr { text-align: right; }

        .bb-note { text-align: center; font-size: 7.5pt; color: #9ca3af; margin-top: 18px; }

        .draft-watermark {
            position: fixed; top: 40%; left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 150px; color: rgba(0,0,0,0.06); font-weight: 800;
            text-transform: uppercase; z-index: 2; pointer-events: none;
            white-space: nowrap;
        }

        .container-table, .package-table { width: 100%; border-collapse: collapse; font-size: 8pt; margin-top: 8px; }
        .container-table th, .package-table th { background: #f9fafb; padding: 5px 8px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        .container-table td, .package-table td { padding: 5px 8px; border-bottom: 1px solid #f3f4f6; }

        @media print {
            @page { margin: 10mm; }
            .invoice-shell { padding: 0; max-width: 100%; }
        }
    </style>
    @php
        $__colorCssTpl = '
            .bb-header .invoice-tag .badge { background: __COLOR__; }
            .bb-card h5 { color: __COLOR__; }
            .bb-totals tr.grand td { border-top: 2px solid __COLOR__; color: __COLOR__; }
            .bb-footer .bank h5 { color: __COLOR__; }
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

    <div class="bb-header">
        <div class="brand">
            <img src="{{ companyLogo() }}" alt="logo">
            <div class="name">{{ $company->name }}</div>
            <div class="name-ar">{{ $company->name_ar }}</div>
        </div>
        <div class="invoice-tag">
            <span class="badge">TAX INVOICE</span>
            <div class="arabic">فاتورة ضريبية</div>
        </div>
    </div>

    <div class="bb-info-strip">
        <div class="item"><div class="label">Invoice No.</div><div class="value">{{ $customerInvoice->row_no }}</div></div>
        <div class="item"><div class="label">Invoice Date</div><div class="value">{{ \Carbon\Carbon::parse($customerInvoice->invoice_date)->format('d M Y') }}@if(\Carbon\Carbon::parse($customerInvoice->invoice_date)->isToday())<span data-toggle="show_time" style="{{ ($settings->show_time ?? false) ? '' : 'display:none' }}"> {{ \Carbon\Carbon::parse($customerInvoice->created_at)->format('h:i A') }}</span>@endif</div></div>
        <div class="item"><div class="label">Due Date</div><div class="value">{{ \Carbon\Carbon::parse($customerInvoice->due_date)->format('d M Y') }}</div></div>
        <div class="item"><div class="label">Job No.</div><div class="value">{{ $customerInvoice->job?->row_no }}</div></div>
    </div>

    <div class="bb-cards">
        <div class="bb-card">
            <h5>Billed To</h5>
            <table>
                <tr><td colspan="2" style="font-weight:600; color:#111827;">{{ $customerInvoice->customer->name_en }}</td></tr>
                <tr><td colspan="2">{{ $customerInvoice->customer->address1_en }}</td></tr>
                <tr><td colspan="2">{{ $customerInvoice->customer->city_en }}, {{ $customerInvoice->customer->country }} {{ $customerInvoice->customer->postal_code }}</td></tr>
                <tr data-toggle="show_phone" style="{{ ($settings->show_phone ?? true) ? '' : 'display:none' }}"><td>Phone</td><td>{{ $customerInvoice->customer->phone }}</td></tr>
                <tr><td>VAT / CR No.</td><td>{{ $customerInvoice->customer->vat_number }} / {{ $customerInvoice->customer->cr_number }}</td></tr>
                @foreach($extraPartyFields ?? [] as $field)
                    <tr data-toggle="{{ $field['key'] }}" style="{{ $field['visible'] ? '' : 'display:none' }}"><td>{{ $field['label_en'] }}</td><td>{{ $field['value'] }}</td></tr>
                @endforeach
            </table>
        </div>
        <div class="bb-card">
            <h5>Shipment</h5>
            <table>
                <tr><td>Shipper</td><td>{{ $customerInvoice->job?->shipper }}</td></tr>
                <tr><td>Consignee</td><td>{{ $customerInvoice->job?->consignee }}</td></tr>
                <tr data-toggle="awb_hbl" style="{{ ($settings->awb_hbl ?? false) ? '' : 'display:none' }}"><td>AWB / HBL</td><td>{{ collect([$customerInvoice->job?->awb_number, $customerInvoice->job?->hbl_number])->filter()->implode(' / ') }}</td></tr>
                <tr data-toggle="pol_pod" style="{{ ($settings->pol_pod ?? false) ? '' : 'display:none' }}"><td>Origin / Dest.</td><td>{{ $customerInvoice->job?->pol }} &rarr; {{ $customerInvoice->job?->pod }}</td></tr>
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

    <table class="bb-items">
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
                        <div class="comment" data-toggle="item_description" style="{{ ($settings->item_description ?? true) ? '' : 'display:none' }}">{{ $items->comment }}</div>
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

    <div class="bb-words">
        <div>{{ amountInWords(round($customerInvoice->grand_total, 2)) }}</div>
        <div class="ar">{{ convert(round($customerInvoice->grand_total, 2)) }}</div>
    </div>

    <div class="bb-totals">
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

    <div class="bb-footer">
        <div class="bank">
            <h5>Bank Details</h5>
            @include('modules.print.partials.bank-details')
        </div>
        <div class="qr">
            @include('modules.print.partials.zatca-qr')
        </div>
    </div>

    <div class="bb-note">
        This is a computer generated document and does not require a signature<br>
        هذا المستند تم انتاجه بواسطة الكمبيوتر ولا يحتاج الى توقيع
    </div>
</div>
</body>
</html>
