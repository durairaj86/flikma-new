@section('page-title', __('Opening Balance'))
@section('page-subtitle', __('Maintain beginning balances for accounts and sub-ledgers.'))
<x-app-layout>
    <main class="gmail-content bg-white d-flex">
        @include('includes.master-navigation')
        <section class="flex-grow-1 px-4 d-flex flex-column">
        @push('master-title-actions')
            <a href="{{ route('finance.opening-balance.import') }}" class="btn btn-outline-primary rounded-pill px-4"><i class="bi bi-upload me-1"></i>{{ __('Import') }}</a>
            <a href="{{ route('finance.opening-balance.create') }}" class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Entry') }}</a>
            <button class="btn btn-link btn-sm p-0 text-decoration-none lh-1 ms-2" id="openingBalanceWatchVideo"
            data-bs-toggle="modal" data-bs-target="#openingBalanceVideoModal" title="{{ __('Watch video') }}">
        <i class="bi bi-play-circle-fill fs-6"></i><span class="d-none d-md-inline ms-1" style="font-size:0.8rem;">{{ __('Watch video') }}</span>
    </button>
@endpush
        @include('includes.master-page-title')

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

        {{-- Same layout as the other Masters lists: filters on the left, actions on the right, then the table. --}}
        <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 pb-3">
            <form method="GET" id="obFilter" action="{{ route('finance.opening-balance') }}" class="d-flex flex-wrap align-items-end gap-2">
                <div style="width:210px">
                    <select name="account_id" class="tom-select" data-live-search="true" data-placeholder="{{ __('All Accounts') }}">
                        <option value="">{{ __('All Accounts') }}</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ (string)request('account_id') === (string)$acc->id ? 'selected' : '' }}>{{ $acc->name }}{{ $acc->code ? ' ('.$acc->code.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="width:190px">
                    <select name="customer_id" class="tom-select" data-live-search="true" data-placeholder="{{ __('All Customers') }}">
                        <option value="">{{ __('All Customers') }}</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ (string)request('customer_id') === (string)$c->id ? 'selected' : '' }}>{{ $c->name_en }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="width:190px">
                    <select name="supplier_id" class="tom-select" data-live-search="true" data-placeholder="{{ __('All Suppliers') }}">
                        <option value="">{{ __('All Suppliers') }}</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ (string)request('supplier_id') === (string)$s->id ? 'selected' : '' }}>{{ $s->name_en }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- The calendar adds its own visible box, so the width is set on the wrapper. --}}
                <div style="width:135px"><input type="text" name="date_from" class="form-control datepicker" value="{{ request('date_from') }}" autocomplete="off"></div>
                <div style="width:135px"><input type="text" name="date_to" class="form-control datepicker" value="{{ request('date_to') }}" autocomplete="off"></div>
                <button class="btn btn-primary rounded-pill px-4">{{ __('Filter') }}</button>
                @if(request()->hasAny(['account_id', 'customer_id', 'supplier_id', 'date_from', 'date_to']))
                    <a href="{{ route('finance.opening-balance') }}" class="btn btn-light border rounded-pill px-3" title="{{ __('Clear') }}"><i class="bi bi-x-lg"></i></a>
                @endif
            </form>
        </div>

        {{-- Table card --}}
        <div class="border bdr-r-10 pt-0 pb-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light sticky-top bg-white">
                        <tr>
                            <th class="ps-4">{{ __('Date') }}</th>
                            <th>{{ __('Account') }}</th>
                            <th>{{ __('Party') }}</th>
                            <th class="text-end">{{ __('Debit') }}</th>
                            <th class="text-end">{{ __('Credit') }}</th>
                            <th class="text-end pe-4">{{ __('Actions') }}</th>
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
                                        <span class="badge bg-primary">{{ __('Customer') }}</span>
                                        <span class="ms-1 small">{{ $ob->customer->name_en }}</span>
                                    @elseif($ob->supplier)
                                        <span class="badge bg-warning text-dark">{{ __('Supplier') }}</span>
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
                                           title="{{ __('Edit') }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-light border text-danger"
                                                onclick="deleteEntry({{ $ob->id }})"
                                                title="{{ __('Delete') }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    {{ __('No opening balance entries found.') }}
                                    <div class="mt-2">
                                        <a href="{{ route('finance.opening-balance.create') }}" class="btn btn-primary btn-sm rounded-pill">
                                            {{ __('Create First Entry') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($openingBalances->hasPages())
                <div class="px-4 pt-3 d-flex justify-content-end">
                    {{ $openingBalances->links() }}
                </div>
            @endif
        </div>
        </section>
    </main>

    {{-- Delete confirmation modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h6 class="modal-title fw-bold text-danger">
                        <i class="bi bi-exclamation-triangle me-1"></i> {{ __('Delete Entry') }}
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{ __('Are you sure you want to delete this entry? This action cannot be undone.') }}
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-danger btn-sm" id="confirmDeleteBtn">{{ __('Delete') }}</button>
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
            btn.textContent = "{{ __('Deleting...') }}";

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
                    alert(data.message || "{{ __('Error deleting entry.') }}");
                    btn.disabled = false;
                    btn.textContent = "{{ __('Delete') }}";
                }
            })
            .catch(() => {
                alert("{{ __('An error occurred.') }}");
                btn.disabled = false;
                btn.textContent = "{{ __('Delete') }}";
            });
        });
    </script>

    <script>
        // Searchable dropdowns and calendars for the filter bar.
        window.addEventListener('load', function () {
            if (typeof initTomSelectForm === 'function') initTomSelectForm($('#obFilter'));
            if (typeof datepicker === 'function') datepicker();
        });
    </script>
    @include('modules.finance.opening-balance._video')
</x-app-layout>
