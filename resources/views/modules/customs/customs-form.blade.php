@php
    $d = fn($f) => $clearance->{$f};
@endphp
<div class="g-3 align-items-center border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ __('Customs Clearance') }} · {{ $job->row_no ?? '' }}</span>
                <span class="text-muted ms-2">{{ $job->customer->name_en ?? '' }}</span>
            </div>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="{{ __('Save Clearance') }}">
    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" value="{{ $clearance->id }}">

        <div class="px-4 mt-3">
            {{-- Status and parties --}}
            <div class="row g-3">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Clearance') }}</h6></div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Status') }}</label>
                    <select name="clearance_status" class="tom-select">
                        @foreach(clearanceStatus() as $key => $label)
                            <option value="{{ $key }}" @selected(($d('clearance_status') ?: 'pending') == $key)>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Type of Clearance') }}</label>
                    <select name="type_of_clearance" class="tom-select">
                        <option value="">{{ __('--Select--') }}</option>
                        @foreach(['import' => 'Import', 'export' => 'Export', 'transit' => 'Transit'] as $key => $label)
                            <option value="{{ $key }}" @selected($d('type_of_clearance') == $key)>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Customs Broker') }}</label>
                    <input type="text" name="customs_broker" class="form-control" value="{{ $d('customs_broker') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Port of Clearance') }}</label>
                    <input type="text" name="port_clearance" class="form-control" value="{{ $d('port_clearance') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('HS Code') }}</label>
                    <input type="text" name="hs_code" class="form-control" value="{{ $d('hs_code') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Declaration No') }}</label>
                    <input type="text" name="declaration_no" class="form-control" value="{{ $d('declaration_no') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Bayan No') }}</label>
                    <input type="text" name="bayan_no" class="form-control" value="{{ $d('bayan_no') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('D.O No') }}</label>
                    <input type="text" name="do_no" class="form-control" value="{{ $d('do_no') }}">
                </div>
            </div>

            {{-- Milestones, in the order they usually happen --}}
            <div class="row g-3 mt-2">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Documents & Dates') }}</h6></div>
                @foreach([
                    'doc_received' => 'Docs Copy Received', 'bl_receive_date' => 'BL Received', 'original_doc_received' => 'Original Docs Received',
                    'saber_certificate_date' => 'Saber Certificate', 'bayan_date' => 'Bayan Date', 'do_date' => 'D.O Date',
                    'clearance_date' => 'Clearance Date', 'demurrage_date' => 'Demurrage Starts',
                ] as $field => $label)
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">{{ __($label) }}</label>
                        <input type="text" name="{{ $field }}" class="form-control datepicker" value="{{ $d($field) }}">
                    </div>
                @endforeach
            </div>

            {{-- Duty and checks --}}
            <div class="row g-3 mt-2">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Duty & Checks') }}</h6></div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Duty Amount (Our Cost)') }}</label>
                    <input type="text" name="duty_amount" class="form-control float" value="{{ $d('duty_amount') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Duty Amount (Client)') }}</label>
                    <input type="text" name="duty_amount_client" class="form-control float" value="{{ $d('duty_amount_client') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="cc-lab" name="lab_clearance" value="1" @checked($d('lab_clearance'))>
                        <label class="form-check-label" for="cc-lab">{{ __('Lab clearance needed') }}</label>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="cc-insp" name="inspection" value="1" @checked($d('inspection'))>
                        <label class="form-check-label" for="cc-insp">{{ __('Inspection') }}</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Clearance Remarks') }}</label>
                    <textarea name="clearance_remarks" class="form-control" rows="2">{{ $d('clearance_remarks') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('D.O Remarks') }}</label>
                    <textarea name="do_remarks" class="form-control" rows="2">{{ $d('do_remarks') }}</textarea>
                </div>
            </div>
        </div>
    </form>
</div>
