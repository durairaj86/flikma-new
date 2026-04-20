@section('js','customer')
@section('page-title','Customers')
@php
    $customers = [
        (object)['id'=>1,'code'=>'CUST-001','name'=>'John Doe','company'=>'JD Enterprises','address'=>'123 Main Street, Mumbai, India','email'=>'john@example.com','phone'=>'+91-9876543210'],
        (object)['id'=>2,'code'=>'CUST-002','name'=>'Jane Smith','company'=>'Smith Corp','address'=>'456 Second Street, Delhi, India','email'=>'jane@example.com','phone'=>'+91-9876543211'],
    ];

    $selectedCustomer = $customers[0];

    $openingBalance = 5000;
    $totalDebit = 2000;
    $totalCredit = 3500;
    $closingBalance = $openingBalance + $totalCredit - $totalDebit;

    $transactions = [
        (object)[ 'date'=>'2025-10-01','type'=>'invoice','reference'=>'INV-001','description'=>'Web Design','debit'=>2000,'credit'=>0,'balance'=>3000 ],
        (object)[ 'date'=>'2025-10-05','type'=>'payment','reference'=>'PAY-001','description'=>'Payment Received','debit'=>0,'credit'=>1500,'balance'=>4500 ],
        (object)[ 'date'=>'2025-10-10','type'=>'credit_note','reference'=>'CN-001','description'=>'Discount Adjustment','debit'=>0,'credit'=>500,'balance'=>5000 ],
    ];
@endphp

<x-app-layout>
    <div class="container py-4" id="customer-statement">
        <!-- Header -->
        <div class="mb-4 text-center">
            <h4 class="mb-1">Customer Statement</h4>
            <small class="text-muted">As of {{ \Carbon\Carbon::now()->format('d-m-Y') }}</small>
        </div>

        <!-- Filters -->
        <div class="row mb-3 g-3 align-items-end">
            <div class="col-md-4">
                <label for="customerSelect" class="form-label fw-semibold">Select Customer</label>
                <select id="customerSelect" class="form-control selectpicker">
                    @foreach($customers as $cust)
                        <option value="{{ $cust->id }}" {{ $cust->id == $selectedCustomer->id ? 'selected' : '' }}>
                            {{ strtoupper($cust->name) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="fromDate" class="form-label fw-semibold">From Date</label>
                <input type="date" id="fromDate" class="form-control" value="{{ now()->subMonth()->format('Y-m-d') }}">
            </div>

            <div class="col-md-3">
                <label for="toDate" class="form-label fw-semibold">To Date</label>
                <input type="date" id="toDate" class="form-control" value="{{ now()->format('Y-m-d') }}">
            </div>

            <div class="col-md-2">
                <button class="btn btn-primary w-100">Filter</button>
            </div>
        </div>

        <div class="shadow bdr-r-10 p-3">
            <!-- Customer Details and Account Summary -->
            <div class="row mb-4">
                <!-- Customer Info -->
                <div class="col-md-8 mb-3">
                    <div class="customer-details p-3">
                        <div class="customer-name mb-1">{{ $selectedCustomer->name }}</div>
                        <div class="customer-info">{{ $selectedCustomer->company }}</div>
                        <div class="customer-info">{{ $selectedCustomer->email }}</div>
                        <div class="customer-info">{{ $selectedCustomer->phone }}</div>
                        <div class="customer-info">Code: {{ $selectedCustomer->code }}</div>
                    </div>
                </div>

                <!-- Account Summary -->
                <div class="col-md-4 mb-3">
                    <div class="p-3 border rounded bg-light summary-box">
                        <div class="fw-semibold mb-2">Account Summary</div>
                        <div class="d-flex justify-content-between py-1">
                            <span>Opening Balance</span>
                            <span>₹ {{ number_format($openingBalance,2) }}</span>
                        </div>
                        <hr class="my-1">
                        <div class="d-flex justify-content-between py-1">
                            <span>Total Debit</span>
                            <span class="text-danger">₹ {{ number_format($totalDebit,2) }}</span>
                        </div>
                        <hr class="my-1">
                        <div class="d-flex justify-content-between py-1">
                            <span>Total Credit</span>
                            <span class="text-success">₹ {{ number_format($totalCredit,2) }}</span>
                        </div>
                        <hr class="my-1">
                        <div class="d-flex justify-content-between py-1 fw-bold">
                            <span>Closing Balance</span>
                            <span>{{ $closingBalance >= 0 ? '₹ '.number_format($closingBalance,2) : '-₹ '.number_format(abs($closingBalance),2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Add above transactions table -->
            <div class="d-flex justify-content-end mb-3 gap-2">
                <form action="" method="POST" target="_blank">
                    @csrf
                    <input type="hidden" name="customer_id" value="{{ $selectedCustomer->id }}">
                    <input type="hidden" name="from_date" value="{{ now()->subMonth()->format('Y-m-d') }}">
                    <input type="hidden" name="to_date" value="{{ now()->format('Y-m-d') }}">
                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-file-earmark-pdf"></i> Export PDF</button>
                </form>

                <form action="" method="POST" target="_blank">
                    @csrf
                    <input type="hidden" name="customer_id" value="{{ $selectedCustomer->id }}">
                    <input type="hidden" name="from_date" value="{{ now()->subMonth()->format('Y-m-d') }}">
                    <input type="hidden" name="to_date" value="{{ now()->format('Y-m-d') }}">
                    <button type="submit" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-excel"></i> Export Excel</button>
                </form>
            </div>


            <!-- Transactions Table -->
            <div class="table-responsive shadow-sm border rounded" style="max-height: 500px; overflow-y:auto;">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light sticky-top">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Reference</th>
                        <th>Description</th>
                        <th class="text-end">Debit (₹)</th>
                        <th class="text-end">Credit (₹)</th>
                        <th class="text-end">Balance (₹)</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($transactions as $txn)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($txn->date)->format('d-m-Y') }}</td>
                            <td>{{ ucfirst($txn->type) }}</td>
                            <td>{{ $txn->reference }}</td>
                            <td>{{ $txn->description }}</td>
                            <td class="text-end text-danger">{{ $txn->debit ? number_format($txn->debit,2) : '-' }}</td>
                            <td class="text-end text-success">{{ $txn->credit ? number_format($txn->credit,2) : '-' }}</td>
                            <td class="text-end">{{ $txn->balance >= 0 ? '₹ '.number_format($txn->balance,2) : '-₹ '.number_format(abs($txn->balance),2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

<style>
    .customer-details {
        font-size: 0.9rem;
    }

    .customer-name {
        font-weight: 600;
        font-size: 1.1rem;
    }

    .customer-info {
        font-size: 0.88rem;
        color: #555;
    }

    .summary-box {
        font-size: 0.9rem;
        background-color: #fdfdfd;
    }

    .summary-box hr {
        margin: 4px 0;
        border-color: #dee2e6;
    }

    .summary-box .fw-semibold {
        font-size: 1rem;
        margin-bottom: 6px;
    }

    .table th, .table td {
        padding: 6px 8px;
    }

    .table th {
        background-color: #f8f9fa;
    }

    .text-danger {
        color: #dc3545 !important;
    }

    .text-success {
        color: #28a745 !important;
    }

    @media print {
        body {
            background: #fff;
            color: #000;
        }

        .container {
            padding: 0;
        }

        .customer-details {
            background-color: #fff !important;
            border: 1px solid #000;
        }

        .summary-box {
            background-color: #fff !important;
            border: 1px solid #000;
        }

        .table th, .table td {
            font-size: 0.85rem;
        }

        .table th {
            background-color: #e9ecef !important;
            -webkit-print-color-adjust: exact;
        }
    }
</style>
