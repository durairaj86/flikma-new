@section('page-title', __('CRM Actions'))
@section('js','crm_action')
<x-app-layout>
    <main class="gmail-content bg-white d-flex">
        @include('includes.master-navigation')
        <section class="flex-grow-1 px-4 d-flex flex-column">
            @include('includes.master-page-title')
            <div class="d-flex justify-content-between align-items-start gap-2 pb-3 flex-wrap">
                <div class="search-box position-relative me-2">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                           placeholder="{{ __('Search...') }}" aria-label="{{ __('Search...') }}">
                </div>
                <button class="btn btn-primary rounded-pill px-4" id="new" title="Alt+N">{{ __('New Action') }}</button>
            </div>
            <p class="small text-muted mb-3">
                {{ __('The actions salespeople pick when they log an activity. A "Planned" action is a to-do on a future date and shows in Follow-ups. Actions marked Auto are also logged by the system from enquiries, quotations, jobs, invoices and collections.') }}
            </p>
            <div class="border bdr-r-10 pt-0 pb-3">
                <div class="flex-grow-1">
                    <table class="table align-middle dataTable" id="dataTable" data-title="{{ __('Action') }}" data-model-size="md" data-min-height="min-height:0;">
                        <thead class="table-light sticky-top bg-white">
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>{{ __('Action') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('When to use it') }}</th>
                            <th>{{ __('Sort order') }}</th>
                            <th>{{ __('Used') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</x-app-layout>
