@section('page-title','Opening Balance')
<x-app-layout>
    <main class="gmail-content bg-white px-3">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-2 mb-0" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-2 mb-0" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="fw-bold mb-0">Opening Balances</h5>
                <small class="text-muted">Maintain beginning balances for accounts and sub-ledgers.</small>
            </div>
            <a href="{{ route('finance.opening-balance.create') }}" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-plus-lg me-1"></i> New Entry
            </a>
        </div>

        {{-- Filter Card --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">Filters</h6>
            </div>
            <div class="card-body py-3">
                <form method="GET" action="{{ route('finance.opening-balance') }}" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-medium">Account</label>
                        <select name="account_id" class="form-select form-select-sm">
                            <option value="">All Accounts</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ (string)request('account_id') === (string)$acc->id ? 'selected' : '' }}>
                                    {{ $acc->name }}{{ $acc->code ? ' ('.$acc->code.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-medium">Customer</label>
                        <select name="customer_id" class="form-select form-select-sm">
                            <option value="">All Customers</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ (string)request('customer_id') === (string)$c->id ? 'selected' : '' }}>
                                    {{ $c->name_en }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-medium">Supplier</label>
                        <select name="supplier_id" class="form-select form-select-sm">
                            <option value="">All Suppliers</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}" {{ (string)request('supplier_id') === (string)$s->id ? 'selected' : '' }}>
                                    {{ $s->name_en }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-medium">From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-medium">To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1">
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-primary w-100">Filter</button>
                            <a href="{{ route('finance.opening-balance') }}" class="btn btn-sm btn-light border" title="Clear">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Table card --}}
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Account</th>
                            <th>Party</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($openingBalances as $ob)
                            <tr>
                                <td class="ps-4">
                                    {{ $ob->finance?->posted_at ? \Carbon\Carbon::parse($ob->finance->posted_at)->format('d M Y') : '—' }}
                                </td>
                                <td>
                                    <div class="fw-semibold">
                                        {{ $ob->account ? (($ob->account->code ? $ob->account->code.' - ' : '').$ob->account->name) : '—' }}
                                    </div>
                                    @if($ob->description)
                                        <div class="text-muted small">{{ $ob->description }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($ob->customer)
                                        <span class="badge bg-primary">Customer</span>
                                        <span class="ms-1 small">{{ $ob->customer->name_en }}</span>
                                    @elseif($ob->supplier)
                                        <span class="badge bg-warning text-dark">Supplier</span>
                                        <span class="ms-1 small">{{ $ob->supplier->name_en }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format((float)$ob->debit, 2) }}</td>
                                <td class="text-end">{{ number_format((float)$ob->credit, 2) }}</td>
                                <td class="text-end pe-4">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <a href="{{ route('finance.opening-balance.edit', $ob->id) }}"
                                           class="btn btn-sm btn-light border"
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-light border text-danger"
                                                onclick="deleteEntry({{ $ob->id }})"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    No opening balance entries found.
                                    <div class="mt-2">
                                        <a href="{{ route('finance.opening-balance.create') }}" class="btn btn-primary btn-sm rounded-pill">
                                            Create First Entry
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($openingBalances->hasPages())
                <div class="card-footer bg-white border-top px-4 py-3 d-flex justify-content-end">
                    {{ $openingBalances->links() }}
                </div>
            @endif
        </div>
    </main>

    {{-- Delete confirmation modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h6 class="modal-title fw-bold text-danger">
                        <i class="bi bi-exclamation-triangle me-1"></i> Delete Entry
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this entry? This action cannot be undone.
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-sm" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let deleteId = null;

        function deleteEntry(id) {
            deleteId = id;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteModal')).show();
        }

        document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
            if (!deleteId) return;
            const btn = this;
            btn.disabled = true;
            btn.textContent = 'Deleting...';

            fetch(`/masters/finance/opening-balance/${deleteId}/delete`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                    window.location.reload();
                } else {
                    alert(data.message || 'Error deleting entry.');
                    btn.disabled = false;
                    btn.textContent = 'Delete';
                }
            })
            .catch(() => {
                alert('An error occurred.');
                btn.disabled = false;
                btn.textContent = 'Delete';
            });
        });
    </script>
</x-app-layout>
