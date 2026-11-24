<div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="{{ __('Save Department') }}">
    <div class="row g-3 align-items-center bg-white border-bottom py-2 mb-3 small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $department->name ?? __('New Department') }}</span>
            </div>
            <div id="show-buttons"></div>
        </div>
    </div>
    <div class="row">
        <div class="d-flex justify-content-center">
            <div class="d-inline-block p-1">
                <ul class="nav status-tabs align-items-center border-bottom mb-0 mt-0 justify-content-center"
                    id="modalTabs" role="tablist">
                    <li class="nav-item me-2">
                        <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn active"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button">
                            <i class="bi bi-building me-1"></i> {{ __('Details') }}
                        </button>
                    </li>
                    <li class="nav-item me-2">
                        <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-rights" type="button">
                            <i class="bi bi-shield-lock me-1"></i> {{ __('User Rights') }}
                        </button>
                    </li>
                </ul>
            </div>
        </div>
        <form id="moduleForm" novalidate action="{{ request()->url() }}">
            @csrf
            <input type="hidden" name="data-id" value="{{ $department->id ?? '' }}">

            <div class="tab-content">
                <div class="tab-pane show active" id="tab-basic">
                    <div class="model-form-tab-div">
                        <div class="row mt-2">
                            <div class="col-4 form-group">
                                <label class="form-label">{{ __('Department Name') }}</label>
                                <input type="text" name="name" class="form-control" required
                                       value="{{ $department->name ?? '' }}">
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">{{ __('Code') }}</label>
                                <input type="text" name="code" class="form-control"
                                       value="{{ $department->code ?? '' }}">
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select name="is_active" class="tom-select" required>
                                    <option value="1" @selected(($department->is_active ?? true))>{{ __('Active') }}</option>
                                    <option value="0" @selected(!($department->is_active ?? true))>{{ __('Inactive') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="tab-rights">
                    <div class="model-form-tab-div">
                        <p class="text-secondary small mb-3">
                            {{ __('Choose what users in this department can see and do in each part of the software. The Super User (the account that registered the company) is never limited by these rights.') }}
                        </p>
                        @include('modules.master.department.rights-table', ['rights' => $rights])
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
