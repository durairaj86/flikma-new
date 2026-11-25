<div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="{{ __('Save Action') }}">
    <div class="row g-3 align-items-center bg-white border-bottom py-2 mb-3 small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $action->id ? $action->label() : __('New Action') }}</span>
            </div>
            <div id="show-buttons"></div>
        </div>
    </div>
    <div class="row">
        <form id="moduleForm" novalidate action="{{ request()->url() }}">
            @csrf
            <input type="hidden" name="data-id" value="{{ $action->id ?? '' }}">
            <div class="model-form-tab-div">
                <div class="row g-3">
                    <div class="col-md-6 form-group">
                        <label class="form-label required">{{ __('Action') }} <sup class="text-danger">*</sup></label>
                        <input type="text" name="name" class="form-control" required maxlength="100"
                               value="{{ $action->name ?? '' }}" placeholder="{{ __('e.g. Site Visit') }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="form-label required">{{ __('Category') }} <sup class="text-danger">*</sup></label>
                        @if($action->code)
                            {{-- a standard action keeps its category: the system and the summaries count on it --}}
                            <div class="form-control bg-light" title="{{ __('A standard action keeps its category.') }}">
                                <span class="badge rounded-pill" style="{{ \App\Models\Crm\CrmAction::badgeStyle($action->category) }}">{{ \App\Models\Crm\CrmAction::categoryLabel($action->category) }}</span>
                            </div>
                        @else
                            <select name="category" class="tom-select" required>
                                @foreach(\App\Models\Crm\CrmAction::CATEGORIES as $key => $c)
                                    <option value="{{ $key }}" @selected($action->category === $key)>{{ __($c['label']) }} — {{ __($c['meaning']) }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    <div class="col-12 form-group">
                        <label class="form-label">{{ __('When to use it') }}</label>
                        <input type="text" name="description" class="form-control" maxlength="255"
                               value="{{ $action->description ? __($action->description) : '' }}">
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="form-label">{{ __('Sort order') }}</label>
                        <input type="text" name="sort_order" class="form-control integer" data-max-int="3"
                               value="{{ $action->sort_order ?? 0 }}">
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="form-label">{{ __('Status') }}</label>
                        <select name="is_active" class="tom-select" required>
                            <option value="1" @selected($action->is_active ?? true)>{{ __('Active') }}</option>
                            <option value="0" @selected(!($action->is_active ?? true))>{{ __('Inactive') }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
