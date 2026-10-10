@section('page-title', __('Chart of Accounts'))
@section('hide-topbar', true)
@section('js','account')
<x-app-layout>
    <main class="gmail-content bg-white px-3 acc-main">
        <style>
            /* same content width as the import page */
            .acc-main { max-width: 1300px; margin-left: auto; margin-right: auto; padding-left: 28px !important; padding-right: 28px !important; }
            /* the area beside the centred content stays white, not the page's grey */
            .content-scroll-area { background: #fff; }
            .acc-title { display: none; }
            body:not(.has-top-header) .acc-title { display: block; }
            #dataTable thead th { background-color: #f8f9fa !important; }
            #dataTable thead th:first-child { border-top-left-radius: 8px; }
            #dataTable thead th:last-child { border-top-right-radius: 8px; }
            /* Tree: parents are bold with a caret; children are indented under them. */
            .acc-caret { display: inline-flex; width: 20px; justify-content: center; cursor: pointer; color: #6c757d; transition: transform .15s; user-select: none; }
            .acc-caret.collapsed { transform: rotate(-90deg); }
            .acc-caret-spacer { display: inline-block; width: 20px; }
            .acc-name.is-parent { font-weight: 600; }
            .acc-tree-row { border-bottom: 1px solid #f1f3f5; }
        </style>
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-0">
            <h4 class="fw-bold text-dark mb-0 acc-title">@yield('page-title')</h4>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Account') }}</button>
                <a class="btn btn-icon-search rounded-circle" href="/finance/accounts/import" title="{{ __('Import') }}" aria-label="{{ __('Import') }}"><i class="bi bi-upload"></i></a>
            </div>
        </div>

        <div id="filterPanel" class="card shadow-sm border-0 d-none filter-panel-card mt-3">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="filter-panel-icon"><i class="bi bi-funnel-fill"></i></span>
                    <h6 class="mb-0 fw-semibold">{{ __('Filters') }}</h6>
                </div>
            </div>
            <div class="card-body pt-3">
                <form id="list-filter" method="post" novalidate="novalidate">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-6 col-xl-4 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Status') }}</label>
                            <select class="tom-select avoid-filter" name="filter-status" id="filter-status">
                                <option value="all" selected>{{ __('All') }}</option>
                                <option value="active">{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-xl-4 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Parent Account') }}</label>
                            <select class="tom-select avoid-filter" name="filter-parent" id="filter-parent" placeholder="{{ __('All') }}">
                                <option value="">{{ __('All') }}</option>
                                @foreach(\App\Models\Finance\Account\Account::whereIn('id', \App\Models\Finance\Account\Account::whereNotNull('parent_id')->select('parent_id'))->orderBy('code')->get(['id', 'code', 'name']) as $parent)
                                    <option value="{{ $parent->id }}">{{ $parent->code }} - {{ $parent->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="text-center mt-4 pt-2 border-top filter-panel-actions">
                        <button class="btn btn-primary btn-round px-4" type="button" id="apply-filter">
                            <i class="bi bi-search me-1"></i> {{ __('Search') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <div class="align-items-center flex-shrink-0">
                <div class="gap-4">
                    <ul class="nav align-items-center" id="listTabs" role="tablist"
                        aria-label="Navigation 13">
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center active justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-asset" type="button" id="asset" wire:click="switchTab('asset')">
                                <span><i class="bi bi-safe me-1"></i> {{ __('Asset') }} -</span>
                                <span class="status-count ms-2" id="assetCount">0</span>
                            </button>
                        </li>

                        {{--<li class="nav-item me-2">
                            <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="confirmed">
                                <span><i class="bi bi-check-circle me-1"></i> Confirmed -</span>
                                <span class="status-count d-flex align-items-center justify-content-center"
                                      id="confirmedCount">0</span>
                            </button>
                        </li>--}}

                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="liability">
                                <span><i class="bi bi-journal-minus me-1"></i> {{ __('Liability') }} -</span>
                                <span class="status-count ms-2" id="liabilityCount">0</span>
                            </button>
                        </li>

                        <li class="nav-item me-2">
                            <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="equity">
                                <span><i class="bi bi-diagram-3 me-1"></i> {{ __('Equity') }} -</span>
                                <span class="status-count ms-2" id="equityCount">0</span>
                            </button>
                        </li>

                        <li class="nav-item me-2">
                            <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="income">
                                <span><i class="bi bi-graph-up-arrow me-1"></i> {{ __('Income') }} -</span>
                                <span class="status-count ms-2" id="incomeCount">0</span>
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="expense">
                                <span><i class="bi bi-graph-down-arrow me-1"></i> {{ __('Expense') }} -</span>
                                <span class="status-count ms-2" id="expenseCount">0</span>
                            </button>
                        </li>

                    </ul>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="search-box position-relative">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                           placeholder="{{ __('Search...') }}" aria-label="{{ __('Search...') }}">
                </div>
                <button class="btn btn-icon-search rounded-circle" id="filter-box" type="button"
                        title="{{ __('Filter') }}" aria-label="{{ __('Filter') }}"><i class="bi bi-funnel"></i></button>
            </div>
        </div>

        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <div class="d-flex justify-content-between px-3 flex-shrink-0">
                <div id="filtered-data"></div>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-link btn-sm text-decoration-none p-0" id="acc-expand-all"><i class="bi bi-arrows-expand me-1"></i>{{ __('Expand all') }}</button>
                    <button type="button" class="btn btn-link btn-sm text-decoration-none p-0" id="acc-collapse-all"><i class="bi bi-arrows-collapse me-1"></i>{{ __('Collapse all') }}</button>
                </div>
            </div>
            <div class="flex-grow-1">
                <div>
                    <table class="table align-middle dataTable" id="dataTable" data-module-url="account">
                        <thead class="table-light sticky-top bg-white">
                        <tr>
                            <th>{{ __('Account Name') }}</th>
                            <th>{{ __('Code') }}</th>
                            <th>{{ __('Account No') }}</th>
                            <th>{{ __('Active') }}</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="loading-row"><td colspan="10" class="text-center text-muted py-4">{{ __('Loading...') }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</x-app-layout>
