<div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="Save Port">
    <!-- Meta Info -->
    <div class="row g-3 align-items-center bg-white border-bottom py-2 mb-3 small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                <div class="module-info">
                    <span class="fw-semibold fs-5">{{ $airport->port_name ?? 'New Airport' }}</span>
                </div>

            </div>

            <!-- Save & Next Button -->
            <div id="show-buttons"></div>
        </div>
    </div>
    <div class="row">
        <form id="moduleForm" novalidate action="{{ request()->url() }}">
            @csrf
            <input type="hidden" name="data-id" value="{{ $airport->id ?? '' }}">

            <div class="model-form-tab-div">

                <div class="row">
                    <div class="col-4 form-group">
                        <label class="form-label">Port Name</label>
                        <input type="text" name="port_name" class="form-control" required
                               value="{{ $airport->name ?? '' }}">
                    </div>
                    <div class="col-4 form-group">
                        <label class="form-label">Port Code</label>
                        <input type="text" name="port_code" class="form-control" required
                               value="{{ $airport->code ?? '' }}">
                    </div>
                    <div class="col-4 form-group">
                        <label class="form-label">Country Code</label>
                        <x-common.country :value="$airport->country_name"></x-common.country>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
