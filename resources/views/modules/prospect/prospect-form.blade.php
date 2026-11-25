<div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save" data-modal-size="md" data-modal-height="300"
     data-button-save="Save Prospect">
    <!-- Meta Info -->
    <div class="row g-3 align-items-center bg-white border-bottom py-2 mb-3 small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                <div class="module-info">
                    <span class="fw-semibold fs-5">{{ $prospect->name ?? __('New Prospect') }}</span> <small
                        class="text-secondary">{{ $prospect->row_no ? ' - ' . $prospect->row_no : '' }}</small>
                </div>

            </div>

            <!-- Save & Next Button -->
            <div id="show-buttons"></div>
        </div>
    </div>
    <div class="row">
        <form id="moduleForm" novalidate action="{{ request()->url() }}">
            @csrf
            {{-- without the id an edit saved a second prospect --}}
            <input type="hidden" name="data-id" value="{{ $prospect->id ?? '' }}">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Prospect Name') }} <span class="text-danger">*</span></label>
                    <input type="text" id="quick-prospect-name" name="quick_prospect_name" class="form-control"
                           value="{{ $prospect->name }}" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Salesperson') }} <span class="text-danger">*</span></label>
                    <x-common.salesperson
                        :value="$prospect->salesperson_id" required></x-common.salesperson>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label>
                    <input type="email" name="quick_prospect_email" class="form-control" value="{{ $prospect->email }}"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Phone') }} <span class="text-danger">*</span></label>
                    <input type="text" name="quick_prospect_phone" class="form-control" value="{{ $prospect->phone }}"
                           required>
                </div>
            </div>

            {{-- lead details (the sales workbook's customer line); the quick form from enquiries / quotations leaves them alone --}}
            <input type="hidden" name="lead_details" value="1">
            <div class="border-top pt-2 mb-2 small fw-semibold text-muted">{{ __('Lead details') }}</div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('Contact Person') }}</label>
                    <input type="text" name="contact_person" class="form-control" maxlength="100" value="{{ $prospect->contact_person }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('Rank') }}</label>
                    <select name="rank" class="tom-select" data-placeholder="{{ __('--Select--') }}">
                        <option value="">{{ __('--Select--') }}</option>
                        @foreach(\App\Services\Crm\CrmSettings::RANKS as $r => $meaning)
                            <option value="{{ $r }}" @selected($prospect->rank === $r)>{{ $r }} — {{ __($meaning) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('Location') }}</label>
                    <input type="text" name="city" class="form-control" maxlength="255" value="{{ $prospect->city }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('Sector / Route') }}</label>
                    <input type="text" name="route" class="form-control" maxlength="150" value="{{ $prospect->route }}" placeholder="{{ __('e.g. China to Dammam') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('Service Type') }}</label>
                    <select name="service_id" class="tom-select" data-placeholder="{{ __('--Select--') }}">
                        <option value="">{{ __('--Select--') }}</option>
                        @foreach(services() as $sid => $sname)
                            <option value="{{ $sid }}" @selected((int) $prospect->service_id === (int) $sid)>{{ __($sname) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('Approx Volume') }}</label>
                    <input type="text" name="approx_volume" class="form-control" maxlength="100" value="{{ $prospect->approx_volume }}" placeholder="{{ __('e.g. 2x40\' FOB') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('Incoterm') }}</label>
                    <select name="incoterm" class="tom-select" data-placeholder="{{ __('--Select--') }}">
                        <option value="">{{ __('--Select--') }}</option>
                        @foreach(\Illuminate\Support\Facades\DB::table('incoterms')->orderBy('code')->pluck('code') as $code)
                            <option value="{{ $code }}" @selected($prospect->incoterm === $code)>{{ $code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('Approx Cost') }}</label>
                    <input type="text" name="approx_cost" class="form-control decimal" value="{{ $prospect->approx_cost }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('Approx Selling') }}</label>
                    <input type="text" name="approx_selling" class="form-control decimal" value="{{ $prospect->approx_selling }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('Address') }}</label>
                <textarea name="quick_prospect_address" class="form-control h-100"
                          rows="2">{{ $prospect->address }}</textarea>
            </div>

        </form>
    </div>
</div>
