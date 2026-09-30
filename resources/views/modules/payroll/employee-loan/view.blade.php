<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Loan Details') }} - {{ $employeeLoan->employee->name ?? __('Employee') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .document-title {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .employee-details {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }
        .employee-info, .loan-info {
            width: 48%;
        }
        .section-title {
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        .info-label {
            width: 40%;
            font-weight: bold;
        }
        .info-value {
            width: 60%;
        }
        .loan-details {
            margin-bottom: 20px;
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
        .total-row {
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }
        .signature-box {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #664d03;
        }
        .status-approved {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .status-rejected {
            background-color: #f8d7da;
            color: #842029;
        }
        .status-paid {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .status-partially_paid {
            background-color: #cfe2ff;
            color: #084298;
        }
        @media print {
            body {
                padding: 0;
            }
            .container {
                border: none;
            }
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-name">{{ companyName() }}</div>
            <div class="document-title">{{ __('EMPLOYEE LOAN DETAILS') }}</div>
            <div>{{ $employeeLoan->row_no ?? 'EL-00000' }}</div>
        </div>

        <div class="employee-details">
            <div class="employee-info">
                <div class="section-title">{{ __('Employee Information') }}</div>
                <div class="info-row">
                    <div class="info-label">{{ __('Employee Name') }}:</div>
                    <div class="info-value">{{ $employeeLoan->employee->name ?? __('N/A') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">{{ __('Employee ID') }}:</div>
                    <div class="info-value">{{ $employeeLoan->employee->id ?? __('N/A') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">{{ __('Department') }}:</div>
                    <div class="info-value">{{ $employeeLoan->employee->department ?? __('N/A') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">{{ __('Position') }}:</div>
                    <div class="info-value">{{ $employeeLoan->employee->position ?? __('N/A') }}</div>
                </div>
            </div>

            <div class="loan-info">
                <div class="section-title">{{ __('Loan Information') }}</div>
                <div class="info-row">
                    <div class="info-label">{{ __('Loan Date') }}:</div>
                    <div class="info-value">{{ date('d-m-Y', strtotime($employeeLoan->loan_date)) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">{{ __('First Payment') }}:</div>
                    <div class="info-value">{{ date('d-m-Y', strtotime($employeeLoan->first_payment_date)) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">{{ __('Payment Method') }}:</div>
                    <div class="info-value">{{ ucwords(str_replace('_', ' ', $employeeLoan->payment_method)) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">{{ __('Status') }}:</div>
                    <div class="info-value">
                        <span class="status-badge status-{{ $employeeLoan->status }}">
                            {{ ucwords(str_replace('_', ' ', $employeeLoan->status)) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="loan-details">
            <div class="section-title">{{ __('Loan Details') }}</div>
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Description') }}</th>
                        <th style="text-align: right;">{{ __('Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ __('Loan Amount') }}</td>
                        <td style="text-align: right;">{{ number_format($employeeLoan->loan_amount, 2) }}</td>
                    </tr>
                    @if($employeeLoan->interest_rate > 0)
                    <tr>
                        <td>{{ __('Interest Rate') }}</td>
                        <td style="text-align: right;">{{ number_format($employeeLoan->interest_rate, 2) }}%</td>
                    </tr>
                    <tr>
                        <td>{{ __('Interest Amount') }}</td>
                        <td style="text-align: right;">{{ number_format($employeeLoan->loan_amount * ($employeeLoan->interest_rate / 100), 2) }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('Total Amount with Interest') }}</td>
                        <td style="text-align: right;">{{ number_format($employeeLoan->loan_amount * (1 + ($employeeLoan->interest_rate / 100)), 2) }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <div class="section-title">{{ __('Repayment Schedule') }}</div>
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Description') }}</th>
                        <th style="text-align: right;">{{ __('Value') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ __('Number of Installments') }}</td>
                        <td style="text-align: right;">{{ $employeeLoan->number_of_installments }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('Installment Amount') }}</td>
                        <td style="text-align: right;">{{ number_format($employeeLoan->installment_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('Remaining Installments') }}</td>
                        <td style="text-align: right;">{{ $employeeLoan->remaining_installments }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('Remaining Amount') }}</td>
                        <td style="text-align: right;">{{ number_format($employeeLoan->remaining_amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            @if($employeeLoan->purpose)
            <div class="section-title">{{ __('Purpose') }}</div>
            <p>{{ $employeeLoan->purpose }}</p>
            @endif

            @if($employeeLoan->remarks)
            <div class="section-title">{{ __('Remarks') }}</div>
            <p>{{ $employeeLoan->remarks }}</p>
            @endif
        </div>

        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">{{ __('Employee Signature') }}</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">{{ __('Authorized Signature') }}</div>
            </div>
        </div>

        <div class="footer">
            <p>{{ __('This is a computer-generated document. No signature is required.') }}</p>
            <p>{{ __('Printed on') }}: {{ date('d-m-Y H:i:s') }}</p>
        </div>
    </div>

    <div class="print-button" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()">{{ __('Print Loan Details') }}</button>
    </div>
</body>
</html>
