<div class="g-3 align-items-center border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="module-info">
            <span class="fw-semibold fs-5">{{ $doc->exists ? $doc->title : __('New Document') }}</span>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="{{ __('Save Document') }}">
    <form id="moduleForm" novalidate action="{{ request()->url() }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="data-id" value="{{ $doc->id }}">
        <div class="px-4 mt-3">
            <div class="row g-3">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Document') }}</h6></div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">{{ __('Title') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="title" class="form-control" value="{{ $doc->title }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Type') }}</label>
                    <select name="doc_type" class="tom-select">
                        <option value="">{{ __('Select') }}</option>
                        @foreach($types as $k => $label)
                            <option value="{{ $k }}" @selected($doc->doc_type == $k)>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </div>

                @if(!$doc->exists)
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">{{ __('Belongs To') }} <sup class="text-danger">*</sup></label>
                        <select name="owner_type" id="doc-owner-type" class="tom-select" required>
                            <option value="">{{ __('Select') }}</option>
                            @foreach($owners as $k => $o)
                                <option value="{{ $k }}">{{ __($o[1]) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">{{ __('Record') }} <sup class="text-danger">*</sup></label>
                        <select name="owner_id" id="doc-owner-id" class="tom-select" data-live-search="true" required>
                            <option value="">{{ __('Choose what it belongs to first') }}</option>
                        </select>
                    </div>
                @else
                    <div class="col-12">
                        <div class="form-text">{{ __('Belongs to') }}: <strong>{{ __($owners[$ownerKey][1] ?? class_basename($doc->documentable_type)) }}</strong> {{ $doc->documentable?->row_no ?? $doc->documentable?->name_en ?? '' }}</div>
                    </div>
                @endif

                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Expiry Date') }}</label>
                    <input type="text" name="expiry_date" class="form-control datepicker" value="{{ $doc->expiry_date?->format('d-m-Y') }}">
                    <div class="form-text">{{ __('Leave empty if it never expires.') }}</div>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">{{ __('File') }} @if(!$doc->exists)<sup class="text-danger">*</sup>@endif</label>
                    <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.csv,.txt" @if(!$doc->exists) required @endif>
                    <div class="form-text">@if($doc->exists){{ __('Current file') }}: {{ $doc->file_name }}. {{ __('Choose a new file only to replace it.') }}@else{{ __('PDF, image, Word or Excel, up to 20 MB.') }}@endif</div>
                </div>
            </div>
        </div>
    </form>
</div>
