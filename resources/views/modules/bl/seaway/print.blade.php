<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seaway Bill - {{ $seawayBill->row_no }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .company-address {
            font-size: 12px;
            margin-bottom: 5px;
        }
        .document-title {
            font-size: 16px;
            font-weight: bold;
            margin: 15px 0;
            text-align: center;
            text-transform: uppercase;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .row {
            display: flex;
            margin-bottom: 5px;
        }
        .col-6 {
            width: 50%;
            padding-right: 10px;
        }
        .col-4 {
            width: 33.33%;
            padding-right: 10px;
        }
        .col-3 {
            width: 25%;
            padding-right: 10px;
        }
        .label {
            font-weight: bold;
            margin-right: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 10px;
            text-align: center;
        }
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('images/logo.png') }}" alt="Company Logo" class="logo">
        <div class="company-name">{{ config('app.name') }}</div>
        <div class="company-address">
            123 Shipping Lane, Port City, Country<br>
            Tel: +123 456 7890 | Email: info@example.com
        </div>
    </div>

    <div class="document-title">Seaway Bill</div>

    <div class="section">
        <div class="row">
            <div class="col-6">
                <div class="label">Seaway Bill No:</div>
                {{ $seawayBill->row_no }}
            </div>
            <div class="col-6">
                <div class="label">Seaway Bill Date:</div>
                {{ $seawayBill->seaway_bill_date }}
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <div class="label">Job Reference:</div>
                {{ $seawayBill->job->row_no }}
            </div>
            <div class="col-6">
                <div class="label">Customer:</div>
                {{ $seawayBill->customer->name }}
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Vessel Information</div>
        <div class="row">
            <div class="col-6">
                <div class="label">Origin Port:</div>
                {{ $seawayBill->origin_port }}
            </div>
            <div class="col-6">
                <div class="label">Destination Port:</div>
                {{ $seawayBill->destination_port }}
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <div class="label">Vessel Name:</div>
                {{ $seawayBill->vessel_name ?? 'N/A' }}
            </div>
            <div class="col-6">
                <div class="label">Voyage Number:</div>
                {{ $seawayBill->voyage_number ?? 'N/A' }}
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <div class="label">Departure Time:</div>
                {{ $seawayBill->departure_time ? $seawayBill->departure_time : 'N/A' }}
            </div>
            <div class="col-6">
                <div class="label">Arrival Time:</div>
                {{ $seawayBill->arrival_time ? $seawayBill->arrival_time : 'N/A' }}
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Delivery Information</div>
        <div class="row">
            <div class="col-6">
                <div class="label">Delivery Date:</div>
                {{ $seawayBill->delivery_date }}
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <div class="label">Delivery Address:</div>
                {{ $seawayBill->delivery_address }}
            </div>
            <div class="col-6">
                <div class="label">Contact Person:</div>
                {{ $seawayBill->contact_person }}<br>
                <div class="label">Contact Phone:</div>
                {{ $seawayBill->contact_phone }}
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Shipment Details</div>
        <div class="row">
            <div class="col-4">
                <div class="label">Shipment Type:</div>
                @if($seawayBill->shipment_type == 'document')
                    Document
                @elseif($seawayBill->shipment_type == 'parcel')
                    Parcel
                @elseif($seawayBill->shipment_type == 'freight')
                    Freight
                @endif
            </div>
            <div class="col-4">
                <div class="label">Service Type:</div>
                @if($seawayBill->service_type == 'standard')
                    Standard
                @elseif($seawayBill->service_type == 'express')
                    Express
                @elseif($seawayBill->service_type == 'same_day')
                    Same Day
                @endif
            </div>
            <div class="col-4">
                <div class="label">Payment Method:</div>
                @if($seawayBill->payment_method == 'prepaid')
                    Prepaid
                @elseif($seawayBill->payment_method == 'collect')
                    Collect
                @elseif($seawayBill->payment_method == 'third_party')
                    Third Party
                @endif
            </div>
        </div>
        @if($seawayBill->special_instructions)
        <div class="row">
            <div class="col-12">
                <div class="label">Special Instructions:</div>
                {{ $seawayBill->special_instructions }}
            </div>
        </div>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Shipment Items</div>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Comment</th>
                    <th class="text-right">Quantity</th>
                    <th class="text-right">Weight (kg)</th>
                    <th class="text-right">Dimensions (cm)</th>
                    <th class="text-center">Fragile</th>
                </tr>
            </thead>
            <tbody>
                @foreach($seawayBill->seawayBillSubs as $item)
                <tr>
                    <td>{{ $item->description->name }}</td>
                    <td>{{ $item->comment }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ $item->weight }}</td>
                    <td class="text-right">{{ $item->length }} x {{ $item->width }} x {{ $item->height }}</td>
                    <td class="text-center">{{ $item->fragile ? 'Yes' : 'No' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="row">
            <div class="col-6">
                <div class="label">Shipper's Signature:</div>
                <div style="height: 60px; border-bottom: 1px solid #ddd; margin-top: 30px;"></div>
                <div style="margin-top: 5px;">Date: ___________________</div>
            </div>
            <div class="col-6">
                <div class="label">Receiver's Signature:</div>
                <div style="height: 60px; border-bottom: 1px solid #ddd; margin-top: 30px;"></div>
                <div style="margin-top: 5px;">Date: ___________________</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>This seaway bill is subject to the terms and conditions of the carrier. All goods are carried at owner's risk.</p>
        <p>Printed on: {{ now()->format('d M Y H:i') }}</p>
    </div>
</body>
</html>
