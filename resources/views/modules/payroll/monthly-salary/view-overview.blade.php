@extends('includes.print-header')
@section('print-content')
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                padding: 0;
                margin: 0;
                background: #fff;
            }

            .container {
                border: none !important;
                width: 100%;
                max-width: 100%;
            }
        }

        .slip-header {
            border-bottom: 2px solid #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .info-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            color: #555;
            width: 150px;
        }

        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
        }

        .salary-table th, .salary-table td {
            border: 1px solid #333;
            padding: 8px 12px;
            font-size: 13px;
        }

        .salary-table th {
            background: #f8f9fa;
            text-transform: uppercase;
        }

        .total-box {
            background: #eee;
            font-weight: bold;
        }

        .net-salary-section {
            border: 2px solid #333;
            margin-top: 20px;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .words {
            font-style: italic;
            font-size: 12px;
        }

        .signature-section {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }

        .sig-box {
            width: 200px;
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 5px;
            font-size: 13px;
            font-weight: bold;
        }
    </style>

    <div class="container">
        {{-- DRAFT Watermark --}}
        @if($monthlySalary->status == 'cancelled')
            <div class="draft-watermark">CANCELLED</div>
        @elseif($monthlySalary->status == 'draft')
            <div class="draft-watermark">DRAFT</div>
        @endif
        <div class="no-print d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-dark btn-sm"
                    onclick="MONTHLY_SALARY.printPreview('{{ $monthlySalary->id }}')">
                <i class="bi bi-printer me-1"></i> Print Slip
            </button>
        </div>

        <div class="slip-header d-flex justify-content-between align-items-center">
            <div>
                <img src="{{ asset('img/logo.png') }}" alt="Logo" style="max-height: 50px;">
            </div>
            <div class="text-center">
                <div class="title">Payslip</div>
                <div class="fw-bold">{{ date('F Y', mktime(0, 0, 0, $monthlySalary->month, 10)) }}</div>
            </div>
            <div class="text-end" style="font-size: 12px;">
                <div class="fw-bold">{{ config('app.name') }}</div>
                <div>{{ $monthlySalary->row_no ?? 'REF: MS-'.str_pad($monthlySalary->id, 5, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Employee Name</td>
                <td>: {{ $monthlySalary->employee->name }}</td>
                <td class="label">Employee ID</td>
                <td>: {{ $monthlySalary->employee->employee_code ?? $monthlySalary->employee_id }}</td>
            </tr>
            <tr>
                <td class="label">Department</td>
                <td>: {{ $monthlySalary->employee->department->name ?? 'General' }}</td>
                <td class="label">Payment Mode</td>
                <td>: {{ strtoupper($monthlySalary->payment_method) }}</td>
            </tr>
            <tr>
                <td class="label">Designation</td>
                <td>: {{ $monthlySalary->employee->position ?? 'Staff' }}</td>
                <td class="label">Payment Date</td>
                <td>: {{ date('d-m-Y', strtotime($monthlySalary->payment_date)) }}</td>
            </tr>
        </table>

        <table class="salary-table">
            <thead>
            <tr>
                <th>Earnings</th>
                <th class="text-end">Amount</th>
                <th>Deductions</th>
                <th class="text-end">Amount</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Basic Salary</td>
                <td class="text-end">{{ number_format($monthlySalary->basic_salary, 2) }}</td>
                <td>Loan Recovery</td>
                <td class="text-end">{{ number_format($monthlySalary->loan_deduction, 2) }}</td>
            </tr>
            <tr>
                <td>Housing Allowance</td>
                <td class="text-end">{{ number_format($monthlySalary->housing_allowance, 2) }}</td>
                <td>Other Deductions</td>
                <td class="text-end">{{ number_format($monthlySalary->deductions, 2) }}</td>
            </tr>
            <tr>
                <td>Transportation</td>
                <td class="text-end">{{ number_format($monthlySalary->transportation_allowance, 2) }}</td>
                <td></td>
                <td class="text-end"></td>
            </tr>
            <tr>
                <td>Other Allowances</td>
                <td class="text-end">{{ number_format($monthlySalary->food_allowance + $monthlySalary->phone_allowance + $monthlySalary->other_allowance, 2) }}</td>
                <td></td>
                <td class="text-end"></td>
            </tr>
            <tr>
                <td>Overtime / Bonus</td>
                <td class="text-end">{{ number_format($monthlySalary->overtime_amount + $monthlySalary->bonus, 2) }}</td>
                <td></td>
                <td class="text-end"></td>
            </tr>
            <tr class="total-box">
                <td>Total Earnings (A)</td>
                <td class="text-end">{{ number_format($monthlySalary->basic_salary + $monthlySalary->housing_allowance + $monthlySalary->transportation_allowance + $monthlySalary->food_allowance + $monthlySalary->phone_allowance + $monthlySalary->other_allowance + $monthlySalary->overtime_amount + $monthlySalary->bonus, 2) }}</td>
                <td>Total Deductions (B)</td>
                <td class="text-end">{{ number_format($monthlySalary->deductions + $monthlySalary->loan_deduction, 2) }}</td>
            </tr>
            </tbody>
        </table>

        <div class="net-salary-section">
            <div>
                <div class="fw-bold">Net Salary Payable (A - B)</div>
                <div class="words">Amount in words: {{ amountInWords($monthlySalary->total_salary) }} Only</div>
            </div>
            <div class="text-end">
                <h4 class="fw-bold mb-0">{{ number_format($monthlySalary->total_salary, 2) }}</h4>
            </div>
        </div>

        @if($monthlySalary->remarks)
            <div class="mt-3 small">
                <strong>Remarks:</strong> {{ $monthlySalary->remarks }}
            </div>
        @endif

        <div class="signature-section">
            <div class="sig-box">Employee Signature</div>
            <div class="sig-box">Director / Manager</div>
        </div>

        <div class="text-center mt-5 text-muted" style="font-size: 10px;">
            This is a computer-generated payslip and does not require a physical signature.
        </div>
    </div>
@endsection
