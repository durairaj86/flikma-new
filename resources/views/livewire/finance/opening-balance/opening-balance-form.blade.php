<div>
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom-0">
            <div class="row align-items-center">
                <div class="col">
                    <h6 class="text-uppercase text-muted fw-bold mb-0 small">Journal Entries</h6>
                </div>
                <div class="col-auto">
                    <div class="d-flex gap-4">
                        <div class="text-end">
                            <span class="d-block small text-muted">Total Debit</span>
                            <span class="fw-bold fs-5 @if($totalDebit != $totalCredit) text-danger @else text-dark @endif">
                                {{ number_format($totalDebit, 2) }}
                            </span>
                        </div>
                        <div class="text-end border-start ps-4">
                            <span class="d-block small text-muted">Total Credit</span>
                            <span class="fw-bold fs-5 @if($totalDebit != $totalCredit) text-danger @else text-dark @endif">
                                {{ number_format($totalCredit, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            @if (session()->has('success') || session()->has('error'))
                <div class="px-4 pt-3">
                    @if (session()->has('success'))
                        <div class="alert alert-success d-flex align-items-center mb-0">
                            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        </div>
                    @endif
                    @if (session()->has('error'))
                        <div class="alert alert-danger d-flex align-items-center mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        </div>
                    @endif
                </div>
            @endif

            <form wire:submit.prevent="save">
                <div class="p-4 bg-light-subtle border-bottom mb-0">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Reference Date</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="bi bi-calendar3"></i></span>
                                <input type="date" class="form-control border-start-0" wire:model="date">
                            </div>
                            @error('date') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-9">
                            <label class="form-label fw-semibold small">Description / Notes</label>
                            <input type="text" class="form-control form-control-sm" placeholder="Enter reason for this opening balance..." wire:model="description">
                            @error('description') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 entry-table">
                        <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 text-muted small" style="width: 15%;">ENTITY TYPE</th>
                            <th class="py-3 border-0 text-muted small" style="width: 45%;">ACCOUNT DETAIL</th>
                            <th class="py-3 border-0 text-muted small text-end" style="width: 15%;">DEBIT</th>
                            <th class="py-3 border-0 text-muted small text-end" style="width: 15%;">CREDIT</th>
                            <th class="py-3 border-0 text-center" style="width: 10%;"></th>
                        </tr>
                        </thead>
                        <tbody class="border-top-0">
                        @foreach ($entries as $index => $entry)
                            <tr wire:key="entry-{{ $index }}">
                                <td class="ps-4">
                                    <select class="form-select form-select-sm border-0 bg-transparent fw-medium"
                                            wire:model="entries.{{ $index }}.entry_type"
                                            wire:change="changeEntryType({{ $index }}, $event.target.value)">
                                        <option value="account">Ledger Account</option>
                                        <option value="customer">Customer</option>
                                        <option value="supplier">Supplier</option>
                                    </select>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm border rounded-2 bg-white">
                                            <span class="input-group-text bg-transparent border-0 text-muted">
                                                <i class="bi bi-search small"></i>
                                            </span>
                                        @if($entry['entry_type'] === 'account')
                                            <select class="form-select border-0 shadow-none" wire:model="entries.{{ $index }}.account_id">
                                                <option value="">Search Account...</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($entry['entry_type'] === 'customer')
                                            <select class="form-select border-0 shadow-none" wire:model="entries.{{ $index }}.customer_id">
                                                <option value="">Search Customer...</option>
                                                @foreach ($customers as $customer)
                                                    <option value="{{ $customer->id }}">{{ $customer->name_en }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($entry['entry_type'] === 'supplier')
                                            <select class="form-select border-0 shadow-none" wire:model="entries.{{ $index }}.supplier_id">
                                                <option value="">Search Supplier...</option>
                                                @foreach ($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}">{{ $supplier->name_en }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm text-end fw-bold border-0 bg-light-subtle"
                                           placeholder="0.00"
                                           wire:model.lazy="entries.{{ $index }}.debit"
                                           wire:change="calculateTotals">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm text-end fw-bold border-0 bg-light-subtle"
                                           placeholder="0.00"
                                           wire:model.lazy="entries.{{ $index }}.credit"
                                           wire:change="calculateTotals">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-link btn-sm text-danger opacity-50 hover-opacity-100"
                                            wire:click="removeEntry({{ $index }})">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white p-4 border-top-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <button type="button" class="btn btn-outline-primary btn-sm px-3 fw-bold rounded-pill" wire:click="addEntry">
                                <i class="bi bi-plus-lg me-1"></i> Add New Row
                            </button>
                        </div>
                        <div class="col-auto">
                            @if($totalDebit != $totalCredit)
                                <span class="text-danger small fw-bold me-3">
                                    <i class="bi bi-info-circle me-1"></i> Difference: {{ number_format(abs($totalDebit - $totalCredit), 2) }}
                                </span>
                            @endif
                            <button type="submit" class="btn btn-primary btn-sm px-5 fw-bold shadow-sm rounded-pill">
                                <i class="bi bi-cloud-arrow-up me-1"></i> Finalize Entry
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <style>
        .entry-table input:focus, .entry-table select:focus {
            background-color: #fff !important;
            box-shadow: inset 0 0 0 1px #008cd1 !important;
        }
        .x-small { font-size: 0.75rem; }
        .bg-light-subtle { background-color: #f8fafc !important; }
        .hover-opacity-100:hover { opacity: 1 !important; }
    </style>
</div>
