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
        <h1>{{ __('EXPENSE') }}</h1>
    </div>

    <div class="company-info">
        <h2>{{ companyName() }}</h2>
        <p>{{ companyAddress() }}</p>
        <p>{{ __('Phone') }}: {{ companyPhone() }} | {{ __('Email') }}: {{ companyEmail() }}</p>
    </div>

    <div class="expense-details">
        <div class="expense-info">
            <h3>{{ __('Expense Details') }}</h3>
            <p><strong>{{ __('Expense No') }}:</strong> {{ $expense->row_no }}</p>
            <p><strong>{{ __('Date') }}:</strong> {{ $expense->posted_at }}</p>
            <p><strong>{{ __('Currency') }}:</strong> {{ $expense->currency }}</p>
            @if($expense->currency != 'SAR')
                <p><strong>{{ __('Exchange Rate') }}:</strong> 1 {{ $expense->currency }} = {{ $expense->currency_rate }} SAR</p>
            @endif
        </div>

        @if($expense->supplier)
        <div class="supplier-info">
            <h3>{{ __('Supplier') }}</h3>
            <p><strong>{{ __('Name') }}:</strong> {{ $expense->supplier->name_en }}</p>
            <p><strong>{{ __('Code') }}:</strong> {{ $expense->supplier->row_no }}</p>
            <p><strong>{{ __('Contact') }}:</strong> {{ $expense->supplier->contact_person }}</p>
            <p><strong>{{ __('Phone') }}:</strong> {{ $expense->supplier->phone }}</p>
        </div>
        @endif

        @if($expense->customer)
        <div class="customer-info">
            <h3>{{ __('Customer') }}</h3>
            <p><strong>{{ __('Name') }}:</strong> {{ $expense->customer->name_en }}</p>
            <p><strong>{{ __('Code') }}:</strong> {{ $expense->customer->row_no }}</p>
            <p><strong>{{ __('Contact') }}:</strong> {{ $expense->customer->contact_person }}</p>
            <p><strong>{{ __('Phone') }}:</strong> {{ $expense->customer->phone }}</p>
        </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ __('Description') }}</th>
                <th>{{ __('Account') }}</th>
                <th>{{ __('Comment') }}</th>
                <th>{{ __('Unit') }}</th>
                <th class="text-end">{{ __('Qty') }}</th>
                <th class="text-end">{{ __('Price') }}</th>
                <th class="text-end">{{ __('Tax (%)') }}</th>
                <th class="text-end">{{ __('Amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expense->expenseSubs as $item)
            <tr>
                <td>{{ $item->description->name ?? __('N/A') }}</td>
                <td>{{ $item->account->name ?? __('N/A') }}</td>
                <td>{{ $item->comment }}</td>
                <td>{{ $item->unit->name ?? __('N/A') }}</td>
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
                <th>{{ __('Subtotal') }}:</th>
                <td class="text-end">{{ number_format($expense->sub_total, 2) }} {{ $expense->currency }}</td>
            </tr>
            <tr>
                <th>{{ __('Tax Total') }}:</th>
                <td class="text-end">{{ number_format($expense->tax_total, 2) }} {{ $expense->currency }}</td>
            </tr>
            <tr>
                <th>{{ __('Grand Total') }}:</th>
                <td class="text-end"><strong>{{ number_format($expense->grand_total, 2) }} {{ $expense->currency }}</strong></td>
            </tr>
            @if($expense->currency != 'SAR')
            <tr>
                <th>{{ __('Base Total (SAR)') }}:</th>
                <td class="text-end">{{ number_format($expense->base_total, 2) }} SAR</td>
            </tr>
            @endif
        </table>
    </div>

    @if($expense->terms)
    <div class="terms">
        <h3>{{ __('Terms & Conditions') }}</h3>
        <p>{{ $expense->terms }}</p>
    </div>
    @endif
</body>
</html>
