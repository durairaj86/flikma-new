@section('page-title', 'Loan Details')
@section('hide-topbar', true)
<x-app-layout>
<div class="pr-wrap">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 fw-bold text-dark mb-0">Loan Details</h1>
        </div>
        <div>
            <a href="{{ route('employee-loans.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            <a href="{{ route('employee-loans.edit', $employeeLoan) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil"></i> Edit
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Loan Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th class="text-muted">Employee</th>
                            <td>{{ $employeeLoan->employee->name }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Loan Amount</th>
                            <td>{{ number_format($employeeLoan->loan_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Total Installments</th>
                            <td>{{ $employeeLoan->total_installments }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Installment Amount</th>
                            <td>{{ number_format($employeeLoan->installment_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Paid Amount</th>
                            <td>{{ number_format($employeeLoan->paid_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Remaining</th>
                            <td>{{ number_format($employeeLoan->remaining_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Status</th>
                            <td>
                                @if($employeeLoan->status == 'active')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Active</span>
                                @elseif($employeeLoan->status == 'closed')
                                    <span class="badge bg-success-subtle text-success border">Closed</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border">Cancelled</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Description</th>
                            <td>{{ $employeeLoan->description ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            @if($employeeLoan->isActive())
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">Record Payment</h5>
                    </div>
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger small py-2"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                        @endif
                        <form action="{{ route('employee-loans.pay-installment', $employeeLoan) }}" method="POST" id="payInstForm">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Amount <span class="text-muted fw-normal">(max {{ number_format($employeeLoan->remaining_amount, 2) }})</span></label>
                                <input type="number" name="amount" class="form-control" step="0.01"
                                    min="0.01" max="{{ $employeeLoan->remaining_amount }}" value="{{ old('amount') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Payment Date</label>
                                <input type="text" name="paid_date" class="form-control datepicker" value="{{ old('paid_date', now()->format('d-m-Y')) }}" autocomplete="off" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Received In</label>
                                <select name="bank_account_id" class="form-select tom-select" data-live-search="true" data-placeholder="Select cash / bank account" required>
                                    <option value="">Select cash / bank account</option>
                                    @foreach($paymentAccounts as $acc)
                                        <option value="{{ $acc->id }}" {{ (string) old('bank_account_id') === (string) $acc->id ? 'selected' : '' }}>{{ $acc->code }} — {{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2" maxlength="500" placeholder="Description / remarks (optional)">{{ old('remarks') }}</textarea>
                            </div>
                            <button type="button" class="btn btn-success w-100" onclick="confirmLoanPayment()"><i class="bi bi-cash"></i> Record Payment</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">Installment History</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="small fw-bold text-uppercase text-secondary">
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Received In</th>
                        <th class="px-4 py-3">Remarks</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($employeeLoan->installments as $i => $inst)
                        <tr>
                            <td class="px-4 py-2">{{ $i + 1 }}</td>
                            <td class="px-4 py-2">{{ number_format($inst->amount, 2) }}</td>
                            <td class="px-4 py-2">{{ $inst->paid_date->format('d-m-Y') }}</td>
                            <td class="px-4 py-2">{{ $inst->bankAccount ? $inst->bankAccount->name : ($inst->bank_account_id ? '—' : 'Payroll deduction') }}</td>
                            <td class="px-4 py-2 text-muted">{{ $inst->remarks ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-center text-muted">No installments recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

<div class="modal fade" id="confirmPayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-delete-top">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2"><h6 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle text-warning me-1"></i>Confirm payment</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body small">
                <div class="mb-2">You are about to record this payment:</div>
                <table class="table table-sm mb-2">
                    <tr><td class="text-muted">Amount</td><td class="fw-bold" id="cpAmount"></td></tr>
                    <tr><td class="text-muted">Date</td><td id="cpDate"></td></tr>
                    <tr><td class="text-muted">Received in</td><td id="cpAcc"></td></tr>
                </table>
                <div class="text-danger"><i class="bi bi-info-circle me-1"></i>It will be posted to the ledger and cannot be undone or edited afterwards.</div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-light border btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm" id="cpGo">Yes, record payment</button>
            </div>
        </div>
    </div>
</div>
<script>
    function confirmLoanPayment() {
        var f = document.getElementById('payInstForm');
        if (!f.reportValidity()) return;
        var sel = f.querySelector('[name=bank_account_id]');
        document.getElementById('cpAmount').textContent = parseFloat(f.amount.value).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('cpDate').textContent = f.paid_date.value;
        document.getElementById('cpAcc').textContent = sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : '';
        var go = document.getElementById('cpGo');
        go.disabled = false;
        go.onclick = function () { go.disabled = true; f.submit(); };   // disabled after the first click: no double posting
        bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmPayModal')).show();
    }
</script>
</div>
</x-app-layout>
