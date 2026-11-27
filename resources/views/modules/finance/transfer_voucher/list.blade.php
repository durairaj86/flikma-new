@section('page-title', __('Transfer Vouchers'))
@section('js','transfer_voucher')
@section('hide-topbar', true)
<x-app-layout>
    <!-- Main Content -->
    <main class="gmail-content bg-white px-3">
        <style>
            .jv-title { display: none; }
            body:not(.has-top-header) .jv-title { display: block; }
            #dataTable thead th { background-color: #f8f9fa !important; }
            #dataTable thead th:first-child { border-top-left-radius: 8px; }
            #dataTable thead th:last-child { border-top-right-radius: 8px; }
            #listTabs .status-btn:not(.active) { background: #f1f3f5; color: #495057; }
            #listTabs .status-btn:not(.active) > span:first-child > i { color: var(--bs-warning) !important; }
            #listTabs .status-btn.active { background: rgb(13, 110, 253) !important; color: #fff !important; }
            #listTabs .status-btn.active > span:first-child > i { color: #fff !important; }
        </style>
        {{-- same header as the other lists: title + video link (only when the top header is off), New on the right --}}
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-0">
            <div class="d-flex align-items-center gap-2 jv-title">
                <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                @stack('page-title-action')
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Transfer') }}</button>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <ul class="nav align-items-center" id="listTabs" role="tablist" aria-label="{{ __('Transfer voucher status') }}">
                <li class="nav-item me-2">
                    <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn active"
                            data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="draft">
                        <span><i class="bi bi-clock me-1"></i> {{ __('Draft') }} -</span>
                        <span class="status-count ms-2" id="draftCount">0</span>
                    </button>
                </li>
                <li class="nav-item me-2">
                    <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="approved">
                        <span><i class="bi bi-check-circle me-1"></i> {{ __('Approved') }} -</span>
                        <span class="status-count ms-2" id="approvedCount">0</span>
                    </button>
                </li>
            </ul>
            <div class="search-box position-relative">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                       placeholder="{{ __('Search transfer vouchers...') }}" aria-label="{{ __('Search transfer vouchers...') }}">
            </div>
        </div>

        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <div class="">
                <table class="table align-middle dataTable" id="dataTable" @if(userCan('finances.transfer-vouchers', 'approve')) data-bulk-approve="finance/transfer-vouchers/bulk/approve" @endif data-empty-noun="{{ __('transfer vouchers') }}" data-module-url="finance/transfer-vouchers" data-title="{{ __('Transfer Voucher') }}" data-model-size="lg" data-min-height="min-height:75vh;">
                    <thead class="table-light bg-white">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Voucher No') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('From Account') }}</th>
                        <th>{{ __('To Account') }}</th>
                        <th>{{ __('Currency') }}</th>
                        <th class="text-end">{{ __('Amount') }}</th>
                        <th class="text-end">{{ __('Bank Charge') }}</th>
                        <th>{{ __('Reference No') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>

        <!-- Disapproval Reason Modal -->
        <div class="modal fade" id="disapprovalReasonModal" tabindex="-1" aria-labelledby="disapprovalReasonModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="disapprovalReasonModalLabel">{{ __('Disapproval Reason') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <form id="disapprovalReasonForm">
                            <input type="hidden" id="journal_voucher_id" name="journal_voucher_id">
                            <div class="mb-3">
                                <label for="reason" class="form-label">{{ __('Reason for Disapproval') }}</label>
                                <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-danger" id="submitDisapprovalReason">{{ __('Submit') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </main>
    @include('modules.finance.journal_voucher.journal-voucher-view', ['drawerTitle' => __('Transfer Voucher Details')])
</x-app-layout>
