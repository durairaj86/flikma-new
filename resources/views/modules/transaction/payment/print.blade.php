<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment #{{ $payment->row_no }}</title>
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
        .company-info {
            margin-bottom: 30px;
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
            <h2>PAYMENT VOUCHER</h2>
        </div>

        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">Payment Information</div>
                    <div class="card-body">
                        <table class="borderless">
                            <tr>
                                <th width="40%">Payment Number:</th>
                                <td>{{ $payment->row_no }}</td>
                            </tr>
                            <tr>
                                <th>Payment Date:</th>
                                <td>{{ $payment->payment_date }}</td>
                            </tr>
                            <tr>
                                <th>Payment Method:</th>
                                <td>{{ $payment->payment_method }}</td>
                            </tr>
                            <tr>
                                <th>Reference Number:</th>
                                <td>{{ $payment->reference_no ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Currency:</th>
                                <td>{{ strtoupper($payment->currency) }}</td>
                            </tr>
                            <tr>
                                <th>Currency Rate:</th>
                                <td>{{ number_format($payment->currency_rate, 4) }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @if($payment->status == 1)
                                        <span class="badge badge-warning">Draft</span>
                                    @elseif($payment->status == 2)
                                        <span class="badge badge-success">Approved</span>
                                    @elseif($payment->status == 3)
                                        <span class="badge badge-danger">Disapproved</span>
                                    @endif
                                </td>
                            </tr>
                            @if($payment->status == 3)
                                <tr>
                                    <th>Disapproval Reason:</th>
                                    <td>{{ $payment->disapproval_reason }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-header">Supplier & Job Information</div>
                    <div class="card-body">
                        <table class="borderless">
                            <tr>
                                <th width="40%">Supplier:</th>
                                <td>{{ $payment->supplier->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Supplier Address:</th>
                                <td>{{ $payment->supplier->address ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Supplier Contact:</th>
                                <td>{{ $payment->supplier->phone ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Job Number:</th>
                                <td>{{ $payment->job_no ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Invoices Paid</div>
            <div class="card-body">
                <table class="bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Invoice Number</th>
                            <th>Invoice Date</th>
                            <th>Due Date</th>
                            <th>Invoice Total</th>
                            <th>Payment Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payment->paymentInvoices as $index => $paymentInvoice)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $paymentInvoice->supplierInvoice->row_no ?? 'N/A' }}</td>
                                <td>{{ $paymentInvoice->supplierInvoice->invoice_date ?? 'N/A' }}</td>
                                <td>{{ $paymentInvoice->supplierInvoice->due_at ?? 'N/A' }}</td>
                                <td class="text-end">{{ number_format($paymentInvoice->supplierInvoice->grand_total ?? 0, 2) }} {{ strtoupper($payment->currency) }}</td>
                                <td class="text-end">{{ number_format($paymentInvoice->amount, 2) }} {{ strtoupper($payment->currency) }}</td>
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
                            <th class="text-end">{{ number_format($payment->sub_total, 2) }} {{ strtoupper($payment->currency) }}</th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-end">Tax Total:</th>
                            <th class="text-end">{{ number_format($payment->tax_total, 2) }} {{ strtoupper($payment->currency) }}</th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-end">Grand Total:</th>
                            <th class="text-end">{{ number_format($payment->grand_total, 2) }} {{ strtoupper($payment->currency) }}</th>
                        </tr>
                        @if($payment->currency != 'SAR')
                            <tr>
                                <th colspan="5" class="text-end">Base Currency Total:</th>
                                <th class="text-end">{{ number_format($payment->base_grand_total, 2) }} SAR</th>
                            </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>

        @if($payment->notes)
            <div class="card">
                <div class="card-header">Notes</div>
                <div class="card-body">
                    {{ $payment->notes }}
                </div>
            </div>
        @endif

        <div class="row" style="margin-top: 50px;">
            <div class="col">
                <div style="border-top: 1px solid #333; padding-top: 10px; text-align: center;">
                    <p>Prepared By</p>
                    <p>{{ $payment->createdBy->name ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="col">
                <div style="border-top: 1px solid #333; padding-top: 10px; text-align: center;">
                    <p>Approved By</p>
                    <p>{{ $payment->approvedBy->name ?? 'N/A' }}</p>
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
