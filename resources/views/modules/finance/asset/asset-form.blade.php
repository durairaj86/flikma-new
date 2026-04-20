<div class="g-3 align-items-center border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $asset->row_no ?? 'New Asset' }}</span>
            </div>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="Save Asset">
    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" id="data-id" value="{{ $asset->id }}">

        <div class="mb-4 mt-3 border-0 px-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Asset No</label>
                        <input name="row_no" class="form-control" value="{{ $asset->row_no }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label required">Asset Name (EN) <sup class="text-danger">*</sup></label>
                        <input name="name_en" class="form-control" required value="{{ $asset->name_en }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Asset Name (AR)</label>
                        <input name="name_ar" class="form-control" value="{{ $asset->name_ar }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="tom-select" data-live-search="true">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected($asset->category_id == $cat->id)>
                                    {{ $cat->name_en }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Acquisition Date <sup class="text-danger">*</sup></label>
                        <input type="date" name="acquisition_date" class="form-control datepicker" required
                               value="{{ $asset->acquisition_date ?? \Carbon\Carbon::today()->format('d-m-Y') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Cost <sup class="text-danger">*</sup></label>
                        <input name="cost" class="form-control text-end" required value="{{ number_format($asset->cost, 2) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Residual Value</label>
                        <input name="residual_value" class="form-control text-end" value="{{ number_format($asset->residual_value, 2) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Useful Life (Months)</label>
                        <input type="number" name="useful_life_months" class="form-control" min="1" max="600"
                               value="{{ $asset->useful_life_months }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Depreciation Method</label>
                        <select name="depreciation_method" class="form-select">
                            <option value="straight_line" @selected($asset->depreciation_method == 'straight_line')>Straight Line</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Depreciation Start Date</label>
                        <input type="date" name="depreciation_start_date" class="form-control datepicker"
                               value="{{ $asset->depreciation_start_date }}">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ $asset->notes }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
