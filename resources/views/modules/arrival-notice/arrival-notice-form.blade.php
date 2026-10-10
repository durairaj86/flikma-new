<div class="g-3 align-items-center border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="module-info">
            <span class="fw-semibold fs-5">{{ $notice->row_no ?? __('New Arrival Notice') }}</span>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="{{ __('Save Arrival Notice') }}">
    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" value="{{ $notice->id }}">

        <div class="px-4 mt-3">
            {{-- 1. To whom --}}
            <div class="row g-3">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Notice') }}</h6></div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Job') }} <small class="text-muted fw-normal">({{ __('optional') }})</small></label>
                    <select name="job_id" id="an-job" class="tom-select" data-live-search="true">
                        <option value="">{{ __('Select Job') }}</option>
                        @foreach($jobs as $job)
                            <option value="{{ $job->id }}" @selected($notice->job_id == $job->id)
                                    data-customer-id="{{ $job->customer_id ? encodeId($job->customer_id) : '' }}"
                                    data-phone="{{ $job->customer->phone ?? '' }}"
                                    data-shipper="{{ $job->shipper }}" data-consignee="{{ $job->consignee ?: ($job->customer->name_en ?? '') }}"
                                    data-address="{{ $job->consignee_address }}" data-pol="{{ $job->pol }}" data-pod="{{ $job->pod }}"
                                    data-eta="{{ $job->eta ? \Carbon\Carbon::parse($job->eta)->format('d-m-Y') : '' }}"
                                    data-carrier="{{ $job->carrier }}" data-voyage="{{ $job->voyage_flight_no }}" data-bl="{{ $job->hbl_number }}"
                                    data-commodity="{{ \Illuminate\Support\Str::limit((string) ($job->commodity ?: $job->description), 300, '') }}"
                                    data-packages="{{ $job->no_of_pieces }}" data-final="{{ $job->final_destination }}"
                                    data-containers="{{ $job->c_nos }}" data-c20="{{ $job->c20 }}" data-c40="{{ $job->c40 }}">
                                {{ $job->row_no }} - {{ $job->customer->name_en ?? '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">{{ __('Choosing a job fills the parties, vessel, route and containers.') }}</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('To (Customer)') }} <sup class="text-danger">*</sup></label>
                    <x-common.customers :value="$notice->customer_id" :new="false" :required="true"></x-common.customers>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Notice Date') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="notice_date" class="form-control datepicker" value="{{ $notice->notice_date?->format('d-m-Y') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Mobile') }}</label>
                    <input type="text" name="to_mobile" id="an-mobile" class="form-control" value="{{ $notice->to_mobile }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Fax') }}</label>
                    <input type="text" name="to_fax" class="form-control" value="{{ $notice->to_fax }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Subject') }}</label>
                    <input type="text" name="subject" id="an-subject" class="form-control" value="{{ $notice->subject }}">
                </div>
            </div>

            {{-- 2. Vessel and route --}}
            <div class="row g-3 mt-2">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Vessel & Route') }}</h6></div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('B/L No') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="bl_no" id="an-bl" class="form-control" value="{{ $notice->bl_no }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Vessel') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="vessel_name" class="form-control" value="{{ $notice->vessel_name }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">{{ __('Voyage No') }}</label>
                    <input type="text" name="voyage_no" id="an-voyage" class="form-control" value="{{ $notice->voyage_no }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">{{ __('ETA') }}</label>
                    <input type="text" name="eta" id="an-eta" class="form-control datepicker" value="{{ $notice->eta?->format('d-m-Y') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Load Port') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="pol" id="an-pol" class="form-control" value="{{ $notice->pol }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Discharge Port') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="pod" id="an-pod" class="form-control" value="{{ $notice->pod }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Final Destination') }}</label>
                    <input type="text" name="final_destination" id="an-final" class="form-control" value="{{ $notice->final_destination }}">
                </div>
            </div>

            {{-- 3. Parties --}}
            <div class="row g-3 mt-2">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Parties') }}</h6></div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Shipper') }}</label>
                    <input type="text" name="shipper" id="an-shipper" class="form-control" value="{{ $notice->shipper }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Consignee') }}</label>
                    <input type="text" name="consignee" id="an-consignee" class="form-control" value="{{ $notice->consignee }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Consignee Address') }}</label>
                    <textarea name="consignee_address" id="an-address" rows="2" class="form-control">{{ $notice->consignee_address }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Notify Party') }}</label>
                    <textarea name="notify_party" id="an-notify" rows="2" class="form-control">{{ $notice->notify_party }}</textarea>
                </div>
            </div>

            {{-- 4. Cargo --}}
            <div class="row g-3 mt-2">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Cargo') }}</h6></div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __("20' Containers") }}</label>
                    <input type="number" min="0" name="containers_20" id="an-c20" class="form-control" value="{{ (int) $notice->containers_20 }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __("40' Containers") }}</label>
                    <input type="number" min="0" name="containers_40" id="an-c40" class="form-control" value="{{ (int) $notice->containers_40 }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Packages') }}</label>
                    <input type="number" min="0" name="packages" id="an-packages" class="form-control" value="{{ $notice->packages }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Carrier / Line') }}</label>
                    <input type="text" name="carrier_name" id="an-carrier" class="form-control" value="{{ $notice->carrier_name }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Container Numbers') }}</label>
                    <textarea name="container_nos" id="an-containers" rows="2" class="form-control" placeholder="{{ __('Separate with commas') }}">{{ $notice->container_nos }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Commodity') }}</label>
                    <textarea name="commodity" id="an-commodity" rows="2" class="form-control">{{ $notice->commodity }}</textarea>
                </div>
            </div>

            {{-- 5. Free time and detention --}}
            <div class="row g-3 mt-2">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Free Time & Line Detention') }}</h6></div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Empty Return Within (days)') }}</label>
                    <input type="number" min="0" name="free_days" class="form-control" value="{{ $notice->free_days ?? 7 }}">
                </div>
                <div class="col-md-9">
                    <label class="form-label fw-semibold">{{ __('Return Depot') }}</label>
                    <input type="text" name="return_depot" class="form-control" value="{{ $notice->return_depot }}" placeholder="{{ __('e.g. GCT (Globe Container Terminal)') }}">
                </div>
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle text-center mb-0" style="font-size:.82rem;">
                            <thead>
                            <tr class="text-muted">
                                <th class="text-start">{{ __('Container') }}</th>
                                <th style="width:90px;">{{ __('Free days') }}</th>
                                @foreach([1, 2, 3, 4] as $i)
                                    <th>{{ $i < 4 ? __('Next') . ' ' . $i : __('Thereafter') }}<div class="fw-normal small">{{ __('days / 20\' / 40\'') }}</div></th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            @foreach(['standard' => __('Standard'), 'special' => __('Special'), 'reefer' => __('Reefer')] as $k => $label)
                                <tr>
                                    <td class="text-start fw-semibold">{{ $label }}</td>
                                    <td><input type="number" min="0" class="form-control form-control-sm text-center" name="detention[{{ $k }}][free]" value="{{ $detention[$k]['free'] }}"></td>
                                    @foreach($detention[$k]['tiers'] as $i => $t)
                                        <td>
                                            <div class="d-flex gap-1">
                                                <input type="number" min="0" class="form-control form-control-sm text-center px-1" style="width:52px;" name="detention[{{ $k }}][tiers][{{ $i }}][days]" value="{{ $t['days'] }}" @if($t['days'] === null) placeholder="-" @endif>
                                                <input type="number" min="0" step="0.01" class="form-control form-control-sm text-center px-1" name="detention[{{ $k }}][tiers][{{ $i }}][r20]" value="{{ $t['r20'] }}">
                                                <input type="number" min="0" step="0.01" class="form-control form-control-sm text-center px-1" name="detention[{{ $k }}][tiers][{{ $i }}][r40]" value="{{ $t['r40'] }}">
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="form-text">{{ __('Printed on the Notification. Rates are per day for a 20\' / 40\' container.') }}</div>
                </div>
            </div>

            {{-- 6. Contact --}}
            <div class="row g-3 mt-2">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Contact for Questions') }}</h6></div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Contact Person') }}</label>
                    <input type="text" name="contact_person" class="form-control" value="{{ $notice->contact_person }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Tel') }}</label>
                    <input type="text" name="contact_tel" class="form-control" value="{{ $notice->contact_tel }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Fax') }}</label>
                    <input type="text" name="contact_fax" class="form-control" value="{{ $notice->contact_fax }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Email') }}</label>
                    <input type="email" name="contact_email" class="form-control" value="{{ $notice->contact_email }}">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">{{ __('Remarks') }}</label>
                    <textarea name="remarks" rows="2" class="form-control">{{ $notice->remarks }}</textarea>
                </div>
            </div>
        </div>
    </form>
</div>
