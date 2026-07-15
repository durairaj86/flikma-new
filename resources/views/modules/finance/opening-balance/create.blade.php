@section('page-title','New Opening Balance')
<x-app-layout>
    <main class="gmail-content bg-white px-3 pb-5">

        {{-- Page header --}}
        <div class="d-flex justify-content-between align-items-center py-3 border-bottom mb-3">
            <div>
                <h5 class="fw-bold mb-0">New Opening Balance Entry</h5>
                <small class="text-muted">Add customer, supplier, and GL account opening balances</small>
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

        <form method="POST" action="{{ route('finance.opening-balance.store') }}" id="ob-form"
              x-data="openingBalanceCreator()">
            @csrf

            {{-- Header card --}}
            <div class="card border-0 shadow-sm mb-0">
                <div class="card-header bg-white py-3">
                    <div class="row g-3 align-items-start">
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Balance Date <span class="text-danger">*</span></label>
                            @if($lockedDate)
                                <input type="date" name="posted_at" value="{{ $lockedDate }}"
                                       class="form-control bg-light" readonly>
                                <div class="form-text text-info">
                                    <i class="bi bi-lock-fill me-1"></i> Date locked based on previous entries.
                                </div>
                            @else
                                <input type="date" name="posted_at" class="form-control"
                                       value="{{ old('posted_at', date('Y-m-d')) }}" required>
                                <div class="form-text text-muted">This date will be used for all balances.</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Nav Tabs --}}
                <ul class="nav nav-tabs nav-fill bg-light border-bottom mb-0" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link py-3 fw-semibold small border-0 rounded-0"
                                :class="activeTab === 'customers' ? 'active bg-white text-primary' : 'text-muted'"
                                @click="activeTab = 'customers'">
                            <i class="bi bi-people me-1"></i> Customers ({{ count($customers) }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link py-3 fw-semibold small border-0 rounded-0"
                                :class="activeTab === 'suppliers' ? 'active bg-white text-primary' : 'text-muted'"
                                @click="activeTab = 'suppliers'">
                            <i class="bi bi-truck me-1"></i> Suppliers ({{ count($suppliers) }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link py-3 fw-semibold small border-0 rounded-0"
                                :class="activeTab === 'accounts' ? 'active bg-white text-primary' : 'text-muted'"
                                @click="activeTab = 'accounts'">
                            <i class="bi bi-journal-text me-1"></i> GL Accounts ({{ count($accounts) }})
                        </button>
                    </li>
                </ul>

                {{-- Customers tab --}}
                <div x-show="activeTab === 'customers'" class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0 border-top-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="width:35%">Customer Name</th>
                                <th class="text-end" style="width:20%">Debit (Amount Owed to You)</th>
                                <th class="text-end" style="width:20%">Credit</th>
                                <th class="pe-4">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $index => $c)
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-semibold">{{ $c->name_en }}</span>
                                        <input type="hidden" name="balances[c{{ $index }}][customer_id]" value="{{ $c->id }}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0"
                                               name="balances[c{{ $index }}][debit]"
                                               class="form-control form-control-sm text-end border-0 bg-transparent row-debit"
                                               placeholder="0.00" value=""
                                               @input="calculateTotals()">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0"
                                               name="balances[c{{ $index }}][credit]"
                                               class="form-control form-control-sm text-end border-0 bg-transparent row-credit"
                                               placeholder="0.00" value=""
                                               @input="calculateTotals()">
                                    </td>
                                    <td class="pe-4">
                                        <input type="text" name="balances[c{{ $index }}][notes]"
                                               class="form-control form-control-sm border-0 bg-transparent"
                                               placeholder="Add optional notes...">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">
                                        All customers already have opening balances or no customers found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Suppliers tab --}}
                <div x-show="activeTab === 'suppliers'" class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0 border-top-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="width:35%">Supplier Name</th>
                                <th class="text-end" style="width:20%">Debit</th>
                                <th class="text-end" style="width:20%">Credit</th>
                                <th class="pe-4">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($suppliers as $index => $s)
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-semibold">{{ $s->name_en }}</span>
                                        <input type="hidden" name="balances[s{{ $index }}][supplier_id]" value="{{ $s->id }}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0"
                                               name="balances[s{{ $index }}][debit]"
                                               class="form-control form-control-sm text-end border-0 bg-transparent row-debit"
                                               placeholder="0.00" value=""
                                               @input="calculateTotals()">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0"
                                               name="balances[s{{ $index }}][credit]"
                                               class="form-control form-control-sm text-end border-0 bg-transparent row-credit"
                                               placeholder="0.00" value=""
                                               @input="calculateTotals()">
                                    </td>
                                    <td class="pe-4">
                                        <input type="text" name="balances[s{{ $index }}][notes]"
                                               class="form-control form-control-sm border-0 bg-transparent"
                                               placeholder="Add optional notes...">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">
                                        All suppliers already have opening balances or no suppliers found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- GL Accounts tab --}}
                <div x-show="activeTab === 'accounts'" class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0 border-top-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="width:35%">Account</th>
                                <th class="text-end" style="width:20%">Debit</th>
                                <th class="text-end" style="width:20%">Credit</th>
                                <th class="pe-4">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accounts as $index => $acc)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold">
                                            {{ $acc->code ? $acc->code.' - '.$acc->name : $acc->name }}
                                        </div>
                                        <input type="hidden" name="balances[a{{ $index }}][account_id]" value="{{ $acc->id }}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0"
                                               name="balances[a{{ $index }}][debit]"
                                               class="form-control form-control-sm text-end border-0 bg-transparent row-debit"
                                               placeholder="0.00" value=""
                                               @input="calculateTotals()">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0"
                                               name="balances[a{{ $index }}][credit]"
                                               class="form-control form-control-sm text-end border-0 bg-transparent row-credit"
                                               placeholder="0.00" value=""
                                               @input="calculateTotals()">
                                    </td>
                                    <td class="pe-4">
                                        <input type="text" name="balances[a{{ $index }}][notes]"
                                               class="form-control form-control-sm border-0 bg-transparent"
                                               placeholder="Add optional notes...">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">
                                        All accounts already have opening balances or no posting accounts found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Footer --}}
                <div class="card-footer bg-light p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex gap-4">
                                <div class="small">
                                    <span class="text-muted">Valid Entries:</span>
                                    <span class="fw-bold text-primary" x-text="validEntries">0</span>
                                </div>
                                <div class="small">
                                    <span class="text-muted">Total Debit:</span>
                                    <span class="fw-bold" x-text="formatCurrency(totalDebit)">0.00</span>
                                </div>
                                <div class="small">
                                    <span class="text-muted">Total Credit:</span>
                                    <span class="fw-bold" x-text="formatCurrency(totalCredit)">0.00</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('finance.opening-balance') }}"
                                   class="btn btn-outline-secondary px-4 rounded-pill">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary px-5 rounded-pill"
                                        :disabled="validEntries === 0">
                                    <i class="bi bi-check2-circle me-1"></i> Save All Balances
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </main>

    <script>
        function openingBalanceCreator() {
            return {
                activeTab: 'customers',
                validEntries: 0,
                totalDebit: 0,
                totalCredit: 0,

                init() {
                    this.calculateTotals();
                },

                calculateTotals() {
                    let totalD = 0, totalC = 0, entries = 0;

                    document.querySelectorAll('.row-debit').forEach(el => {
                        let val = parseFloat(el.value) || 0;
                        totalD += val;
                        let row = el.closest('tr');
                        let creditVal = parseFloat(row.querySelector('.row-credit').value) || 0;
                        if (val > 0 || creditVal > 0) entries++;
                    });

                    document.querySelectorAll('.row-credit').forEach(el => {
                        totalC += parseFloat(el.value) || 0;
                    });

                    this.totalDebit = totalD;
                    this.totalCredit = totalC;
                    this.validEntries = entries;
                },

                formatCurrency(amount) {
                    return new Intl.NumberFormat('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(amount);
                }
            };
        }
    </script>
</x-app-layout>
