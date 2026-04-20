<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Slip - {{ $monthlySalary->employee->name ?? 'Employee' }}</title>
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
        .employee-info, .salary-period {
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
        .salary-details {
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
            <div class="company-name">{{ config('app.name', 'Company Name') }}</div>
            <div class="document-title">SALARY SLIP</div>
            <div>{{ $monthlySalary->row_no ?? 'MS-00000' }}</div>
        </div>

        <div class="employee-details">
            <div class="employee-info">
                <div class="section-title">Employee Information</div>
                <div class="info-row">
                    <div class="info-label">Employee Name:</div>
                    <div class="info-value">{{ $monthlySalary->employee->name ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Employee ID:</div>
                    <div class="info-value">{{ $monthlySalary->employee->id ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Department:</div>
                    <div class="info-value">{{ $monthlySalary->employee->department ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Position:</div>
                    <div class="info-value">{{ $monthlySalary->employee->position ?? 'N/A' }}</div>
                </div>
            </div>

            <div class="salary-period">
                <div class="section-title">Salary Period</div>
                <div class="info-row">
                    <div class="info-label">Month:</div>
                    <div class="info-value">{{ date('F', mktime(0, 0, 0, $monthlySalary->month, 10)) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Year:</div>
                    <div class="info-value">{{ $monthlySalary->year }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Payment Date:</div>
                    <div class="info-value">{{ date('d-m-Y', strtotime($monthlySalary->payment_date)) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Payment Method:</div>
                    <div class="info-value">{{ ucwords(str_replace('_', ' ', $monthlySalary->payment_method)) }}</div>
                </div>
            </div>
        </div>

        <div class="salary-details">
            <div class="section-title">Salary Details</div>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Basic Salary</td>
                        <td style="text-align: right;">{{ number_format($monthlySalary->basic_salary, 2) }}</td>
                    </tr>
                    @if($monthlySalary->housing_allowance > 0)
                    <tr>
                        <td>Housing Allowance</td>
                        <td style="text-align: right;">{{ number_format($monthlySalary->housing_allowance, 2) }}</td>
                    </tr>
                    @endif
                    @if($monthlySalary->transportation_allowance > 0)
                    <tr>
                        <td>Transportation Allowance</td>
                        <td style="text-align: right;">{{ number_format($monthlySalary->transportation_allowance, 2) }}</td>
                    </tr>
                    @endif
                    @if($monthlySalary->food_allowance > 0)
                    <tr>
                        <td>Food Allowance</td>
                        <td style="text-align: right;">{{ number_format($monthlySalary->food_allowance, 2) }}</td>
                    </tr>
                    @endif
                    @if($monthlySalary->phone_allowance > 0)
                    <tr>
                        <td>Phone Allowance</td>
                        <td style="text-align: right;">{{ number_format($monthlySalary->phone_allowance, 2) }}</td>
                    </tr>
                    @endif
                    @if($monthlySalary->other_allowance > 0)
                    <tr>
                        <td>Other Allowance</td>
                        <td style="text-align: right;">{{ number_format($monthlySalary->other_allowance, 2) }}</td>
                    </tr>
                    @endif
                    @if($monthlySalary->overtime_hours > 0)
                    <tr>
                        <td>Overtime ({{ $monthlySalary->overtime_hours }} hours)</td>
                        <td style="text-align: right;">{{ number_format($monthlySalary->overtime_amount, 2) }}</td>
                    </tr>
                    @endif
                    @if($monthlySalary->bonus > 0)
                    <tr>
                        <td>Bonus</td>
                        <td style="text-align: right;">{{ number_format($monthlySalary->bonus, 2) }}</td>
                    </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <th>Gross Salary</th>
                        <th style="text-align: right;">{{ number_format($monthlySalary->basic_salary + $monthlySalary->housing_allowance + $monthlySalary->transportation_allowance + $monthlySalary->food_allowance + $monthlySalary->phone_allowance + $monthlySalary->other_allowance + $monthlySalary->overtime_amount + $monthlySalary->bonus, 2) }}</th>
                    </tr>
                </tfoot>
            </table>

            <div class="section-title">Deductions</div>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @if($monthlySalary->deductions > 0)
                    <tr>
                        <td>General Deductions</td>
                        <td style="text-align: right;">{{ number_format($monthlySalary->deductions, 2) }}</td>
                    </tr>
                    @endif
                    @if($monthlySalary->loan_deduction > 0)
                    <tr>
                        <td>Loan Deduction</td>
                        <td style="text-align: right;">{{ number_format($monthlySalary->loan_deduction, 2) }}</td>
                    </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <th>Total Deductions</th>
                        <th style="text-align: right;">{{ number_format($monthlySalary->deductions + $monthlySalary->loan_deduction, 2) }}</th>
                    </tr>
                </tfoot>
            </table>

            <div class="section-title">Net Salary</div>
            <table>
                <tbody>
                    <tr class="total-row">
                        <td>Net Salary</td>
                        <td style="text-align: right;">{{ number_format($monthlySalary->total_salary, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            @if($monthlySalary->remarks)
            <div class="section-title">Remarks</div>
            <p>{{ $monthlySalary->remarks }}</p>
            @endif
        </div>

        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">Employee Signature</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Authorized Signature</div>
            </div>
        </div>

        <div class="footer">
            <p>This is a computer-generated document. No signature is required.</p>
            <p>Printed on: {{ date('d-m-Y H:i:s') }}</p>
        </div>
    </div>

    <div class="print-button" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()">Print Salary Slip</button>
    </div>
</body>
</html>
