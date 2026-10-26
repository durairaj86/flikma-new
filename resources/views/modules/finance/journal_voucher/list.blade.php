@section('page-title', __('Journal Vouchers'))
@section('js','journal_voucher')
@section('hide-topbar', true)
@push('page-title-action')
    <button class="btn btn-link btn-sm p-0 text-decoration-none lh-1 ms-2" id="journalVoucherWatchVideo"
            data-bs-toggle="modal" data-bs-target="#journalVoucherVideoModal" title="{{ __('Watch video') }}">
        <i class="bi bi-play-circle-fill fs-6"></i><span class="d-none d-md-inline ms-1" style="font-size:0.8rem;">{{ __('Watch video') }}</span>
    </button>
@endpush
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
                <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Journal Voucher') }}</button>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <ul class="nav align-items-center" id="listTabs" role="tablist" aria-label="{{ __('Journal voucher status') }}">
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
                       placeholder="{{ __('Search journal vouchers...') }}" aria-label="{{ __('Search journal vouchers...') }}">
            </div>
        </div>

        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <div class="">
                <table class="table align-middle dataTable" id="dataTable" data-empty-noun="{{ __('journal vouchers') }}">
                    <thead class="table-light bg-white">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Voucher No') }}</th>
                        <th>{{ __('Voucher Type') }}</th>
                        <th>{{ __('Job No') }}</th>
                        <th>{{ __('Voucher Date') }}</th>
                        <th>{{ __('Reference No') }}</th>
                        <th>{{ __('Currency') }}</th>
                        <th>{{ __('Debit Total') }}</th>
                        <th>{{ __('Credit Total') }}</th>
                        <th>{{ __('Status') }}</th>
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
    @include('modules.finance.journal_voucher._video')
</x-app-layout>
