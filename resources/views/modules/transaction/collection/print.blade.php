<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collection #{{ $collection->row_no }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            font-size: 14px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .row {
            display: flex;
            margin-bottom: 20px;
        }
        .col {
            flex: 1;
            padding: 0 15px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }
        .card-body {
            padding: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table.bordered {
            border: 1px solid #ddd;
        }
        table.bordered th,
        table.bordered td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        table.borderless th,
        table.borderless td {
            border: none;
            padding: 5px 0;
        }
        th {
            text-align: left;
            background-color: #f8f9fa;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 3px 7px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 4px;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $company->name ?? 'Company Name' }}</h1>
            <p>{{ $company->address ?? 'Company Address' }}</p>
            <p>Phone: {{ $company->phone ?? 'Company Phone' }} | Email: {{ $company->email ?? 'Company Email' }}</p>
            <h2>COLLECTION VOUCHER</h2>
        </div>

        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">Collection Information</div>
                    <div class="card-body">
                        <table class="borderless">
                            <tr>
                                <th width="40%">Collection Number:</th>
                                <td>{{ $collection->row_no }}</td>
                            </tr>
                            <tr>
                                <th>Collection Date:</th>
                                <td>{{ $collection->collection_date }}</td>
                            </tr>
                            <tr>
                                <th>Collection Method:</th>
                                <td>{{ $collection->collection_method }}</td>
                            </tr>
                            <tr>
                                <th>Reference Number:</th>
                                <td>{{ $collection->reference_no ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Currency:</th>
                                <td>{{ strtoupper($collection->currency) }}</td>
                            </tr>
                            <tr>
                                <th>Currency Rate:</th>
                                <td>{{ number_format($collection->currency_rate, 4) }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @if($collection->status == 1)
                                        <span class="badge badge-warning">Draft</span>
                                    @elseif($collection->status == 2)
                                        <span class="badge badge-success">Approved</span>
                                    @elseif($collection->status == 3)
                                        <span class="badge badge-danger">Disapproved</span>
                                    @endif
                                </td>
                            </tr>
                            @if($collection->status == 3)
                                <tr>
                                    <th>Disapproval Reason:</th>
                                    <td>{{ $collection->disapproval_reason }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-header">Customer & Job Information</div>
                    <div class="card-body">
                        <table class="borderless">
                            <tr>
                                <th width="40%">Customer:</th>
                                <td>{{ $collection->customer->name_en ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Customer Address:</th>
                                <td>{{ $collection->customer->address1_en ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Customer Contact:</th>
                                <td>{{ $collection->customer->phone ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Job Number:</th>
                                <td>{{ $collection->job_no ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Invoices Collected</div>
            <div class="card-body">
                <table class="bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Invoice Number</th>
                            <th>Invoice Date</th>
                            <th>Due Date</th>
                            <th>Invoice Total</th>
                            <th>Collection Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($collection->collectionInvoices as $index => $collectionInvoice)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $collectionInvoice->customerInvoice->row_no ?? 'N/A' }}</td>
                                <td>{{ $collectionInvoice->customerInvoice->invoice_date ?? 'N/A' }}</td>
                                <td>{{ $collectionInvoice->customerInvoice->due_at ?? 'N/A' }}</td>
                                <td class="text-end">{{ number_format($collectionInvoice->customerInvoice->grand_total ?? 0, 2) }} {{ strtoupper($collection->currency) }}</td>
                                <td class="text-end">{{ number_format($collectionInvoice->amount, 2) }} {{ strtoupper($collection->currency) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No invoices found</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end">Sub Total:</th>
                            <th class="text-end">{{ number_format($collection->sub_total, 2) }} {{ strtoupper($collection->currency) }}</th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-end">Tax Total:</th>
                            <th class="text-end">{{ number_format($collection->tax_total, 2) }} {{ strtoupper($collection->currency) }}</th>
                        </tr>
                        @if($collection->bank_charges)
                            <tr>
                                <th colspan="5" class="text-end">Bank Charges:</th>
                                <th class="text-end">{{ number_format($collection->bank_charges, 2) }} {{ strtoupper($collection->currency) }}</th>
                            </tr>
                        @endif
                        @if($collection->other_charges)
                            <tr>
                                <th colspan="5" class="text-end">Other Charges:</th>
                                <th class="text-end">{{ number_format($collection->other_charges, 2) }} {{ strtoupper($collection->currency) }}</th>
                            </tr>
                        @endif
                        <tr>
                            <th colspan="5" class="text-end">Grand Total:</th>
                            <th class="text-end">{{ number_format($collection->grand_total, 2) }} {{ strtoupper($collection->currency) }}</th>
                        </tr>
                        @if($collection->currency != 'SAR')
                            <tr>
                                <th colspan="5" class="text-end">Base Currency Total:</th>
                                <th class="text-end">{{ number_format($collection->base_grand_total, 2) }} SAR</th>
                            </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>

        @if($collection->notes)
            <div class="card">
                <div class="card-header">Notes</div>
                <div class="card-body">
                    {{ $collection->notes }}
                </div>
            </div>
        @endif

        <div class="row" style="margin-top: 50px;">
            <div class="col">
                <div style="border-top: 1px solid #333; padding-top: 10px; text-align: center;">
                    <p>Prepared By</p>
                    <p>{{ $collection->createdBy->name ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="col">
                <div style="border-top: 1px solid #333; padding-top: 10px; text-align: center;">
                    <p>Approved By</p>
                    <p>{{ $collection->approvedBy->name ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="col">
                <div style="border-top: 1px solid #333; padding-top: 10px; text-align: center;">
                    <p>Received By</p>
                    <p>&nbsp;</p>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>This is a computer-generated document. No signature is required.</p>
            <p>Printed on: {{ now()->format('d-m-Y H:i:s') }}</p>
        </div>

        <div class="no-print" style="margin-top: 20px; text-align: center;">
            <button onclick="window.print()" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Print Document
            </button>
        </div>
    </div>
</body>
</html>
