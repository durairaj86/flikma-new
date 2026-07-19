<div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="Save Term">
    <!-- Meta Info -->
    <div class="row g-3 align-items-center bg-white border-bottom py-2 mb-3 small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                <div class="module-info">
                    <span class="fw-semibold fs-5">{{ $quotationTerm->title ?? 'New Quotation Term' }}</span>
                </div>
            </div>
            <div id="show-buttons"></div>
        </div>
    </div>
    <div class="row">
        <form id="moduleForm" novalidate action="{{ request()->url() }}">
            @csrf
            <input type="hidden" name="data-id" value="{{ $quotationTerm->id ?? '' }}">

            <div class="model-form-tab-div">
                <div class="row g-3">
                    <div class="col-6 form-group">
                        <label class="form-label required">Title <sup class="text-danger">*</sup></label>
                        <input type="text" name="title" class="form-control" required
                               value="{{ $quotationTerm->title ?? '' }}">
                    </div>

                    <div class="col-6 form-group d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" role="switch" id="is_general"
                                   name="is_general" value="1"
                                   @checked($quotationTerm->is_general ?? false)>
                            <label class="form-check-label" for="is_general">
                                General term (applies to every activity)
                            </label>
                        </div>
                    </div>

                    <div class="col-6 form-group" id="activityFieldWrapper"
                         style="{{ ($quotationTerm->is_general ?? false) ? 'display:none;' : '' }}">
                        <label class="form-label required">Activity <sup class="text-danger">*</sup></label>
                        <select class="tom-select" id="activity_id" name="activity_id" data-live-search="true"
                                @unless($quotationTerm->is_general ?? false) required @endunless>
                            <option value="">--Select Activity--</option>
                            @foreach($activities as $activity)
                                <option value="{{ $activity->id }}"
                                        @selected(($quotationTerm->activity_id ?? null) == $activity->id)>
                                    {{ $activity->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 form-group d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" role="switch" id="is_active"
                                   name="is_active" value="1"
                                   @checked($quotationTerm->is_active ?? true)>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>

                    <div class="col-12 form-group">
                        <label class="form-label required">Terms &amp; Conditions Text <sup class="text-danger">*</sup></label>
                        <div id="terms-editor" style="background:#fff;height:250px;"></div>
                        {{-- Quill has no underlying form input of its own — this hidden
                             textarea is what actually gets submitted; kept in sync with
                             the editor's HTML on every edit (see quotation_term.js). --}}
                        <textarea name="terms" id="terms-hidden" class="d-none" required>{{ $quotationTerm->terms ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
