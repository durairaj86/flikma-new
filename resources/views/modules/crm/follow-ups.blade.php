@section('page-title', __('Follow-ups'))
@section('js','crm_follow_up')
@section('hide-topbar', true)
<x-app-layout>
    <style>
        #listTabs .status-btn.active { background: rgb(13, 110, 253) !important; color: #fff !important; }
        #listTabs .status-btn.active > span:first-child > i { color: #fff !important; }
        #dataTable thead th:first-child { border-top-left-radius: 10px; }
        #dataTable thead th:last-child { border-top-right-radius: 10px; }
        .fu-overdue { color: #b91c1c; font-weight: 600; }
    </style>
    <main class="gmail-content bg-white px-3">
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-0">
            <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <a href="/sales/pipeline" class="btn btn-outline-secondary rounded-pill px-3"><i class="bi bi-kanban me-1"></i>{{ __('Pipeline') }}</a>
                <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('Log Activity') }}</button>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <ul class="nav align-items-center" id="listTabs" role="tablist" aria-label="{{ __('Follow-ups') }}">
                @foreach([
                    'overdue' => ['icon' => 'bi-exclamation-circle text-danger', 'label' => __('Overdue')],
                    'today' => ['icon' => 'bi-alarm text-warning', 'label' => __('Today')],
                    'upcoming' => ['icon' => 'bi-calendar-event text-primary', 'label' => __('Upcoming')],
                    'done' => ['icon' => 'bi-check2-circle text-success', 'label' => __('Done')],
                ] as $key => $tab)
                    <li class="nav-item me-2">
                        <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn {{ $key === 'overdue' ? 'active' : '' }}"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="{{ $key }}">
                            <span><i class="bi {{ $tab['icon'] }} me-1"></i> {{ $tab['label'] }} -</span>
                            <span class="status-count ms-2" id="{{ $key }}Count">0</span>
                        </button>
                    </li>
                @endforeach
            </ul>
            <div class="d-flex align-items-center gap-2">
                <div id="fuFilters" class="d-flex align-items-center gap-2">
                <div style="width:170px"><select id="fuOwner" class="tom-select" aria-label="{{ __('Whose follow-ups') }}">
                    <option value="mine">{{ __('My follow-ups') }}</option>
                    <option value="all">{{ __('Everyone') }}</option>
                </select></div>
                <div style="width:190px"><select id="fuAction" class="tom-select" aria-label="{{ __('Action') }}">
                    <option value="">{{ __('All actions') }}</option>
                    @foreach(\App\Models\Crm\CrmAction::options()->where('category', \App\Models\Crm\CrmAction::PLANNED) as $a)
                        <option value="{{ $a->id }}">{{ $a->label() }}</option>
                    @endforeach
                </select></div>
                </div>
                <div class="search-box position-relative">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                           placeholder="{{ __('Search notes...') }}" aria-label="{{ __('Search notes...') }}">
                </div>
            </div>
        </div>

        <div class="border bdr-r-10 pb-3 flex-grow-1">
            <table class="table align-middle dataTable" id="dataTable" data-title="{{ __('Activity') }}" data-model-size="md" data-min-height="min-height:0;">
                <thead class="table-light sticky-top bg-white">
                <tr>
                    <th>#</th>
                    <th>{{ __('Due') }}</th>
                    <th>{{ __('With') }}</th>
                    <th>{{ __('Action') }}</th>
                    <th>{{ __('Note') }}</th>
                    <th>{{ __('Assigned to') }}</th>
                    <th class="text-center">{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </main>
</x-app-layout>
