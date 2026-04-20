<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waybill - Print</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .waybill-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .waybill-header h1 {
            margin: 0;
            color: #0d6efd;
        }
        .waybill-header p {
            margin: 5px 0;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            background-color: #f8f9fa;
            padding: 8px;
            margin-bottom: 10px;
            font-weight: bold;
            border-bottom: 1px solid #dee2e6;
        }
        .row {
            display: flex;
            margin-bottom: 10px;
        }
        .col {
            flex: 1;
            padding: 0 10px;
        }
        .label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 3px;
        }
        .value {
            font-weight: 500;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .barcode {
            text-align: center;
            margin: 20px 0;
        }
        .barcode img {
            max-width: 300px;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }
        .signature-box {
            width: 45%;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
            text-align: center;
        }
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            @page {
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body>
    <div class="waybill-header">
        <h1>WAYBILL</h1>
        <p>WB-2023-001</p>
    </div>

    <div class="section">
        <div class="row">
            <div class="col">
                <div class="label">Customer</div>
                <div class="value">Sample Customer</div>
            </div>
            <div class="col">
                <div class="label">Job No</div>
                <div class="value">JOB-2023-001</div>
            </div>
            <div class="col">
                <div class="label">Waybill Date</div>
                <div class="value">{{ date('d-m-Y') }}</div>
            </div>
            <div class="col">
                <div class="label">Delivery Date</div>
                <div class="value">{{ date('d-m-Y', strtotime('+3 days')) }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Delivery Information</div>
        <div class="row">
            <div class="col">
                <div class="label">Delivery Address</div>
                <div class="value">123 Main Street, City, Country, 12345</div>
            </div>
            <div class="col">
                <div class="label">Contact Person</div>
                <div class="value">John Doe</div>
                <div class="label">Contact Phone</div>
                <div class="value">+1234567890</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Shipment Details</div>
        <div class="row">
            <div class="col">
                <div class="label">Shipment Type</div>
                <div class="value">Parcel</div>
            </div>
            <div class="col">
                <div class="label">Service Type</div>
                <div class="value">Express</div>
            </div>
            <div class="col">
                <div class="label">Payment Method</div>
                <div class="value">Prepaid</div>
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
                    <th class="text-end">Quantity</th>
                    <th class="text-end">Weight (kg)</th>
                    <th class="text-end">Dimensions (cm)</th>
                    <th class="text-center">Fragile</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Sample Item</td>
                    <td>Sample comment</td>
                    <td class="text-end">2</td>
                    <td class="text-end">5.0</td>
                    <td class="text-end">30 x 20 x 10</td>
                    <td class="text-center">Yes</td>
                </tr>
                <tr>
                    <td>Another Item</td>
                    <td>Another comment</td>
                    <td class="text-end">1</td>
                    <td class="text-end">3.5</td>
                    <td class="text-end">25 x 15 x 8</td>
                    <td class="text-center">No</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Special Instructions</div>
        <p>Handle with care. Deliver during business hours only.</p>
    </div>

    <div class="barcode">
        <!-- Placeholder for barcode image -->
        <div style="border: 1px solid #dee2e6; padding: 10px; display: inline-block;">
            <p style="margin: 0;">Barcode Placeholder</p>
        </div>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <p>Sender's Signature</p>
        </div>
        <div class="signature-box">
            <p>Receiver's Signature</p>
        </div>
    </div>
</body>
</html>
