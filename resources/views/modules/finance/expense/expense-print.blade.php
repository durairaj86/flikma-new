<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense #{{ $expense->row_no }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #2c3e50;
        }
        .company-info {
            text-align: center;
            margin-bottom: 20px;
        }
        .expense-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .expense-info, .supplier-info, .customer-info {
            flex: 1;
        }
        .expense-info h3, .supplier-info h3, .customer-info h3 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .text-end {
            text-align: right;
        }
        .totals {
            width: 300px;
            margin-left: auto;
        }
        .totals table {
            width: 100%;
        }
        .totals th {
            text-align: right;
        }
        .terms {
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>EXPENSE</h1>
    </div>

    <div class="company-info">
        <h2>Your Company Name</h2>
        <p>123 Business Street, City, Country</p>
        <p>Phone: +123 456 7890 | Email: info@yourcompany.com</p>
    </div>

    <div class="expense-details">
        <div class="expense-info">
            <h3>Expense Details</h3>
            <p><strong>Expense No:</strong> {{ $expense->row_no }}</p>
            <p><strong>Date:</strong> {{ $expense->posted_at }}</p>
            <p><strong>Currency:</strong> {{ $expense->currency }}</p>
            @if($expense->currency != 'SAR')
                <p><strong>Exchange Rate:</strong> 1 {{ $expense->currency }} = {{ $expense->currency_rate }} SAR</p>
            @endif
        </div>

        @if($expense->supplier)
        <div class="supplier-info">
            <h3>Supplier</h3>
            <p><strong>Name:</strong> {{ $expense->supplier->name_en }}</p>
            <p><strong>Code:</strong> {{ $expense->supplier->row_no }}</p>
            <p><strong>Contact:</strong> {{ $expense->supplier->contact_person }}</p>
            <p><strong>Phone:</strong> {{ $expense->supplier->phone }}</p>
        </div>
        @endif

        @if($expense->customer)
        <div class="customer-info">
            <h3>Customer</h3>
            <p><strong>Name:</strong> {{ $expense->customer->name_en }}</p>
            <p><strong>Code:</strong> {{ $expense->customer->row_no }}</p>
            <p><strong>Contact:</strong> {{ $expense->customer->contact_person }}</p>
            <p><strong>Phone:</strong> {{ $expense->customer->phone }}</p>
        </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Account</th>
                <th>Comment</th>
                <th>Unit</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Price</th>
                <th class="text-end">Tax (%)</th>
                <th class="text-end">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expense->expenseSubs as $item)
            <tr>
                <td>{{ $item->description->name ?? 'N/A' }}</td>
                <td>{{ $item->account->name ?? 'N/A' }}</td>
                <td>{{ $item->comment }}</td>
                <td>{{ $item->unit->name ?? 'N/A' }}</td>
                <td class="text-end">{{ $item->quantity }}</td>
                <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-end">{{ $item->tax_code }}%</td>
                <td class="text-end">{{ number_format($item->total_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <th>Subtotal:</th>
                <td class="text-end">{{ number_format($expense->sub_total, 2) }} {{ $expense->currency }}</td>
            </tr>
            <tr>
                <th>Tax Total:</th>
                <td class="text-end">{{ number_format($expense->tax_total, 2) }} {{ $expense->currency }}</td>
            </tr>
            <tr>
                <th>Grand Total:</th>
                <td class="text-end"><strong>{{ number_format($expense->grand_total, 2) }} {{ $expense->currency }}</strong></td>
            </tr>
            @if($expense->currency != 'SAR')
            <tr>
                <th>Base Total (SAR):</th>
                <td class="text-end">{{ number_format($expense->base_total, 2) }} SAR</td>
            </tr>
            @endif
        </table>
    </div>

    @if($expense->terms)
    <div class="terms">
        <h3>Terms & Conditions</h3>
        <p>{{ $expense->terms }}</p>
    </div>
    @endif
</body>
</html>
