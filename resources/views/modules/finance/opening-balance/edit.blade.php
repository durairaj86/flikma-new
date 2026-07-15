@section('page-title','Edit Opening Balance')
<x-app-layout>
    <main class="gmail-content bg-white px-3 pb-5">

        {{-- Page header --}}
        <div class="d-flex justify-content-between align-items-center py-3 border-bottom mb-3">
            <div>
                <h5 class="fw-bold mb-0">Edit Opening Balance</h5>
                <small class="text-muted">Update the beginning balance details.</small>
            </div>
            <a href="{{ route('finance.opening-balance') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form action="{{ route('finance.opening-balance.update', $balance->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            {{-- Account: only for GL rows (not party rows) --}}
                            @if(!$balance->party_type)
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Account <span class="text-danger">*</span></label>
                                    <select name="account_id" class="form-select" required>
                                        <option value="">-- Select Account --</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}" @selected($balance->account_id == $acc->id)>
                                                {{ $acc->code ? $acc->code.' - '.$acc->name : $acc->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            {{-- Party --}}
                            @if($balance->party_type === 'customer')
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Customer</label>
                                    <select name="customer_id" class="form-select">
                                        <option value="">None</option>
                                        @foreach($customers as $c)
                                            <option value="{{ $c->id }}" @selected($balance->customer_id == $c->id)>
                                                {{ $c->name_en }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @elseif($balance->party_type === 'supplier')
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Supplier</label>
                                    <select name="supplier_id" class="form-select">
                                        <option value="">None</option>
                                        @foreach($suppliers as $s)
                                            <option value="{{ $s->id }}" @selected($balance->supplier_id == $s->id)>
                                                {{ $s->name_en }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium text-muted">Balance Date (Locked)</label>
                                    <input type="date" value="{{ $balance->balance_date }}"
                                           class="form-control bg-light" disabled>
                                    <input type="hidden" name="balance_date" value="{{ $balance->balance_date }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Notes</label>
                                    <input type="text" name="notes"
                                           value="{{ old('notes', $balance->notes) }}"
                                           class="form-control" placeholder="Optional notes">
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Debit</label>
                                    <input type="number" step="0.01" min="0" name="debit"
                                           value="{{ old('debit', (float)$balance->debit) }}"
                                           class="form-control text-end">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Credit</label>
                                    <input type="number" step="0.01" min="0" name="credit"
                                           value="{{ old('credit', (float)$balance->credit) }}"
                                           class="form-control text-end">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 border-top pt-4">
                                <a href="{{ route('finance.opening-balance') }}"
                                   class="btn btn-light border px-4">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4 rounded-pill">
                                    <i class="bi bi-check2-circle me-1"></i> Update Opening Balance
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm bg-light">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark mb-3">
                            <i class="bi bi-info-circle me-2 text-primary"></i> Information
                        </h6>
                        <ul class="small text-muted ps-3">
                            <li class="mb-2"><strong>Account:</strong> The general ledger account for this balance. For customer/supplier rows the account is auto-assigned (AR/AP).</li>
                            <li class="mb-2"><strong>Party:</strong> The customer or supplier this balance belongs to. Leave blank for direct GL account entries.</li>
                            <li class="mb-2"><strong>Date:</strong> The balance date is locked and shared across all entries in the same voucher.</li>
                            <li><strong>Double Entry:</strong> Ensure your total debits and credits are eventually balanced across all opening entries.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </main>
</x-app-layout>
