<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Airway Bill - {{ $airwayBill->row_no }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 14px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .row {
            display: flex;
            margin-bottom: 10px;
        }
        .col {
            flex: 1;
            padding-right: 15px;
        }
        .label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .value {
            margin-bottom: 10px;
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
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
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
        <div class="title">AIRWAY BILL</div>
        <div class="subtitle">{{ $airwayBill->row_no }}</div>
    </div>

    <div class="section">
        <div class="row">
            <div class="col">
                <div class="label">Customer:</div>
                <div class="value">{{ $airwayBill->customer->name ?? 'N/A' }}</div>

                <div class="label">Job Reference:</div>
                <div class="value">{{ $airwayBill->job->row_no ?? 'N/A' }}</div>

                <div class="label">Airway Bill Date:</div>
                <div class="value">{{ $airwayBill->airway_bill_date ? date('d/m/Y', strtotime($airwayBill->airway_bill_date)) : 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="label">Delivery Date:</div>
                <div class="value">{{ $airwayBill->delivery_date ? date('d/m/Y', strtotime($airwayBill->delivery_date)) : 'N/A' }}</div>

                <div class="label">Status:</div>
                <div class="value">{{ ucfirst($airwayBill->status) }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Flight Information</div>
        <div class="row">
            <div class="col">
                <div class="label">Origin Airport:</div>
                <div class="value">{{ $airwayBill->origin_airport ?? 'N/A' }}</div>

                <div class="label">Destination Airport:</div>
                <div class="value">{{ $airwayBill->destination_airport ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="label">Carrier:</div>
                <div class="value">{{ $airwayBill->carrier ?? 'N/A' }}</div>

                <div class="label">Flight Number:</div>
                <div class="value">{{ $airwayBill->flight_number ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="label">Departure Time:</div>
                <div class="value">{{ $airwayBill->departure_time ? date('d/m/Y H:i', strtotime($airwayBill->departure_time)) : 'N/A' }}</div>

                <div class="label">Arrival Time:</div>
                <div class="value">{{ $airwayBill->arrival_time ? date('d/m/Y H:i', strtotime($airwayBill->arrival_time)) : 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Delivery Information</div>
        <div class="row">
            <div class="col">
                <div class="label">Delivery Address:</div>
                <div class="value">{{ $airwayBill->delivery_address ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="label">Contact Person:</div>
                <div class="value">{{ $airwayBill->contact_person ?? 'N/A' }}</div>

                <div class="label">Contact Phone:</div>
                <div class="value">{{ $airwayBill->contact_phone ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Shipment Details</div>
        <div class="row">
            <div class="col">
                <div class="label">Shipment Type:</div>
                <div class="value">{{ ucfirst($airwayBill->shipment_type) ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="label">Service Type:</div>
                <div class="value">{{ ucfirst($airwayBill->service_type) ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="label">Payment Method:</div>
                <div class="value">{{ ucfirst(str_replace('_', ' ', $airwayBill->payment_method)) ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Items</div>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Comment</th>
                    <th>Quantity</th>
                    <th>Weight (kg)</th>
                    <th>Dimensions (cm)</th>
                    <th>Fragile</th>
                </tr>
            </thead>
            <tbody>
                @if(count($airwayBill->airwayBillSubs) > 0)
                    @foreach($airwayBill->airwayBillSubs as $item)
                        <tr>
                            <td>{{ $item->description->description ?? 'N/A' }}</td>
                            <td>{{ $item->comment ?? 'N/A' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->weight }}</td>
                            <td>{{ $item->length }} x {{ $item->width }} x {{ $item->height }}</td>
                            <td>{{ $item->fragile ? 'Yes' : 'No' }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" class="text-center">No items found</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    @if($airwayBill->special_instructions)
        <div class="section">
            <div class="section-title">Special Instructions</div>
            <div>{{ $airwayBill->special_instructions }}</div>
        </div>
    @endif

    <div class="footer">
        <p>This document was generated on {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
