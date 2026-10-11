<div class="g-3 align-items-center border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="module-info"><span class="fw-semibold fs-5">{{ $c->container_no ?: $c->container_number }}</span>
            <span class="text-muted ms-2">{{ $c->job?->row_no }}</span></div>
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save" data-button-save="{{ __('Save') }}">
    <form id="moduleForm" novalidate action="/operation/demurrage/{{ $c->id }}">
        @csrf
        <div class="px-4 mt-3"><div class="row g-3">
            <div class="col-md-4"><label class="form-label fw-semibold">{{ __('Discharged at Port') }}</label>
                <input type="text" name="discharged_at" class="form-control datepicker" value="{{ $c->discharged_at ? \Illuminate\Support\Carbon::parse($c->discharged_at)->format('d-m-Y') : '' }}"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">{{ __('Gate Out') }}</label>
                <input type="text" name="gate_out_at" class="form-control datepicker" value="{{ $c->gate_out_at ? \Illuminate\Support\Carbon::parse($c->gate_out_at)->format('d-m-Y') : '' }}"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">{{ __('Empty Returned') }}</label>
                <input type="text" name="returned_at" class="form-control datepicker" value="{{ $c->returned_at ? \Illuminate\Support\Carbon::parse($c->returned_at)->format('d-m-Y') : '' }}"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">{{ __('Free Days') }}</label>
                <input type="number" min="0" name="free_days" class="form-control" value="{{ $c->free_days }}" placeholder="7">
                <div class="form-text">{{ __('Given by the shipping line. Empty means 7.') }}</div></div>
            <div class="col-md-8"><label class="form-label fw-semibold">{{ __('Return Depot') }}</label>
                <input type="text" name="return_depot" class="form-control" value="{{ $c->return_depot }}"></div>
        </div></div>
    </form>
</div>
