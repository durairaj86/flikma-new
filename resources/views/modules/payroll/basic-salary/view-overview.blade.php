@extends('includes.print-header')
@section('print-content')
<div class="container py-4">
    <div class="d-flex justify-content-end align-items-center gap-2 mb-3 no-print">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="BASIC_SALARY.printPreview('{{ $salary->id }}')">
            <i class="bi bi-printer me-1"></i> Print
        </button>
    </div>

    <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4">
        <div>
            <h3 class="text-uppercase fw-bold mb-0">Salary Structure</h3>
            <p class="text-muted small mb-0">Reference No: {{ $salary->row_no ?? 'REF-'.$salary->id }}</p>
        </div>
        <div class="text-end">
            <h5 class="fw-bold mb-0">{{ authUserCompany()->name }}</h5>
            <p class="text-muted small mb-0">Date of Issue: {{ date('d-m-Y') }}</p>
        </div>
    </div>

    <div class="card border-0 bg-light mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="text-muted small d-block">Employee Name</label>
                    <span class="fw-semibold">{{ $salary->employee->name }}</span>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small d-block">Employee Code</label>
                    <span class="fw-semibold">{{ $salary->employee->employee_code ?? 'EMP-' . $salary->employee_id }}</span>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small d-block">Joining Date</label>
                    <span class="fw-semibold">{{ $salary->employee->joining_date ? \Carbon\Carbon::parse($salary->employee->joining_date)->format('d-M-Y') : 'N/A' }}</span>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small d-block">Email Address</label>
                    <span class="fw-semibold text-lowercase">{{ $salary->employee->email }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12">
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                <tr>
                    <th class="py-2 px-3">Description</th>
                    <th class="text-end py-2 px-3" style="width: 200px;">Amount (Monthly)</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="px-3">Basic Salary</td>
                    <td class="text-end px-3">{{ number_format($salary->basic_salary, 2) }}</td>
                </tr>
                <tr>
                    <td class="px-3 text-muted">Housing Allowance</td>
                    <td class="text-end px-3">{{ number_format($salary->housing_allowance, 2) }}</td>
                </tr>
                <tr>
                    <td class="px-3 text-muted">Transportation Allowance</td>
                    <td class="text-end px-3">{{ number_format($salary->transportation_allowance, 2) }}</td>
                </tr>
                <tr>
                    <td class="px-3 text-muted">Food Allowance</td>
                    <td class="text-end px-3">{{ number_format($salary->food_allowance, 2) }}</td>
                </tr>
                <tr>
                    <td class="px-3 text-muted">Phone Allowance</td>
                    <td class="text-end px-3">{{ number_format($salary->phone_allowance, 2) }}</td>
                </tr>
                <tr>
                    <td class="px-3 text-muted">Other Allowance</td>
                    <td class="text-end px-3">{{ number_format($salary->other_allowance, 2) }}</td>
                </tr>
                </tbody>
                <tfoot class="table-light">
                <tr class="fw-bold">
                    <td class="px-3 py-3 fs-5">Total Monthly Gross Salary</td>
                    <td class="text-end px-3 py-3 fs-5 text-primary">
                        {{ number_format($salary->basic_salary + $salary->housing_allowance + $salary->transportation_allowance + $salary->food_allowance + $salary->phone_allowance + $salary->other_allowance, 2) }}
                    </td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <p class="small"><strong>Effective Date:</strong> {{ \Carbon\Carbon::parse($salary->effective_date)->format('d F, Y') }}</p>
            <div class="p-3 bg-light rounded" style="min-height: 80px;">
                <label class="small fw-bold text-muted">Remarks:</label>
                <p class="small mb-0">{{ $salary->remarks }}</p>
            </div>
        </div>
        <div class="col-md-6 text-end d-flex flex-column justify-content-end">
            <div class="mt-5">
                <p class="mb-0 fw-bold text-decoration-underline">Authorized Signatory</p>
                <p class="small text-muted">{{ authUserCompany()->name }}</p>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print { display: none !important; }
        body { background: white !important; }
        .container { width: 100% !important; max-width: 100% !important; }
        .card { border: 1px solid #dee2e6 !important; }
    }
</style>
@endsection
