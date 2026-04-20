@section('js','supplier')
@section('page-title','Supplier Statement')

<x-app-layout>
    <div class="container py-4" id="supplier-statement">
        <!-- Header -->
        <div class="mb-4 text-center">
            <h4 class="mb-1 fw-bold text-primary">Supplier Statement</h4>
            <small class="text-muted">
                From {{ \Carbon\Carbon::parse($fromDate)->format('d-m-Y') }}
                to {{ \Carbon\Carbon::parse($toDate)->format('d-m-Y') }}
            </small><br>
            <small class="text-muted">
                Currency: {{ $selectedSupplier->currency ?? 'SAR' }}
            </small>
        </div>

        <!-- Filters -->
        <form method="GET" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Select Supplier</label>
                    <select name="supplier_id" class="form-select">
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}"
                                @selected($selectedSupplier && $supplier->id == $selectedSupplier->id)>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                </div>
            </div>
        </form>

        <!-- Summary Section -->
        <div class="row mb-4">
            <!-- Left: Supplier Info -->
            <div class="col-md-8 mb-3">
                @if($selectedSupplier)
                    <div class="p-3 border rounded bg-white shadow-sm h-100">
                        <h5 class="fw-bold mb-2 text-primary">{{ $selectedSupplier->name }}</h5>
                        <div class="small text-muted">
                            <div>Email: {{ $selectedSupplier->email ?? '-' }}</div>
                            <div>Phone: {{ $selectedSupplier->phone ?? '-' }}</div>
                            <div>Code: <span class="fw-semibold">{{ $selectedSupplier->row_no ?? '-' }}</span></div>
                            <div>Currency: <span class="fw-semibold">{{ $selectedSupplier->currency ?? 'SAR' }}</span></div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right: Account Summary -->
            <div class="col-md-4 mb-3">
                <div class="p-3 border rounded bg-light shadow-sm h-100">
                    <div class="fw-bold mb-3 text-secondary text-uppercase">Account Summary</div>

                    <div class="d-flex justify-content-between py-1">
                        <span>Opening Balance</span>
                        <span class="fw-semibold">{{ $selectedSupplier->currency ?? 'SAR' }} {{ number_format($openingBalance, 2) }}</span>
                    </div>
                    <hr class="my-1">

                    <div class="d-flex justify-content-between py-1">
                        <span>Total Invoiced</span>
                        <span class="text-danger fw-semibold">{{ $selectedSupplier->currency ?? 'SAR' }} {{ number_format($invoicedAmount, 2) }}</span>
                    </div>
                    <hr class="my-1">

                    <div class="d-flex justify-content-between py-1">
                        <span>Total Paid</span>
                        <span class="text-success fw-semibold">{{ $selectedSupplier->currency ?? 'SAR' }} {{ number_format($paidAmount, 2) }}</span>
                    </div>
                    <hr class="my-1">

                    <div class="d-flex justify-content-between py-1 fw-bold border-top pt-2">
                        <span>Closing Balance</span>
                        <span class="{{ $closingBalance >= 0 ? 'text-danger' : 'text-success' }}">
                            {{ $selectedSupplier->currency ?? 'SAR' }}
                            {{ $closingBalance >= 0
                                ? number_format($closingBalance, 2)
                                : '('.number_format(abs($closingBalance), 2).')' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Table -->
        <div class="table-responsive shadow-sm border rounded" style="max-height: 550px; overflow-y:auto;">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead class="table-light sticky-top">
                <tr>
                    <th>Date</th>
                    <th>Voucher No</th>
                    <th>Reference</th>
                    <th>Job No</th>
                    <th>Description</th>
                    <th class="text-end">Debit</th>
                    <th class="text-end">Credit</th>
                    <th class="text-end">Balance</th>
                </tr>
                </thead>
                <tbody>
                @php $runningBalance = $openingBalance; @endphp
                <tr class="fw-semibold bg-light">
                    <td colspan="7">Opening Balance</td>
                    <td class="text-end">{{ number_format($runningBalance,2) }}</td>
                </tr>

                @forelse($transactions as $txn)
                    @php
                        $debit = $txn->base_debit ?? 0;
                        $credit = $txn->base_credit ?? 0;
                        $runningBalance += ($debit - $credit);
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($txn->reference_date)->format('d-m-Y') }}</td>
                        <td>{{ $txn->voucher_no }}</td>
                        <td>{{ $txn->reference_no ?? '-' }}</td>
                        <td>{{ $txn->job_number ?? '-' }}</td>
                        <td>{{ $txn->description ?? '-' }}</td>
                        <td class="text-end text-danger">{{ $debit > 0 ? number_format($debit,2) : '-' }}</td>
                        <td class="text-end text-success">{{ $credit > 0 ? number_format($credit,2) : '-' }}</td>
                        <td class="text-end fw-semibold">{{ number_format($runningBalance,2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-3">No transactions found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

<style>
    .table th, .table td { padding: 8px 10px; vertical-align: middle; }
    .table thead th { position: sticky; top: 0; z-index: 2; }
    .text-danger { color: #dc3545 !important; }
    .text-success { color: #198754 !important; }
    .shadow-sm { box-shadow: 0 1px 3px rgba(0,0,0,.1) !important; }
</style>
