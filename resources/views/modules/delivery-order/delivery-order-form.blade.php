<div class="g-3 align-items-center border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $order->row_no ?? __('New Delivery Order') }}</span>
            </div>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="{{ __('Save Delivery Order') }}">
    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" value="{{ $order->id }}">

        <div class="px-4 mt-3">
            <div class="row g-3">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Order') }}</h6></div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Job') }} <small class="text-muted fw-normal">({{ __('optional') }})</small></label>
                    <select name="job_id" id="dlv-job" class="tom-select" data-live-search="true">
                        <option value="">{{ __('Select Job') }}</option>
                        @foreach($jobs as $job)
                            <option value="{{ $job->id }}" @selected($order->job_id == $job->id)
                                    data-customer-id="{{ $job->customer_id ? encodeId($job->customer_id) : '' }}"
                                    data-consignee="{{ $job->consignee }}" data-address="{{ $job->delivery_address }}" data-pickup="{{ $job->pickup_address }}"
                                    data-container="{{ $job->container_no }}" data-packages="{{ $job->no_of_pieces }}" data-weight="{{ $job->weight }}"
                                    data-description="{{ \Illuminate\Support\Str::limit((string) $job->description, 200, '') }}">
                                {{ $job->row_no }} - {{ $job->customer->name_en ?? '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">{{ __('Choosing a job fills the consignee, address and cargo.') }}</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Customer') }} <sup class="text-danger">*</sup></label>
                    <x-common.customers :value="$order->customer_id" :new="false" :required="true"></x-common.customers>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">{{ __('Order Date') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="do_date" class="form-control datepicker" value="{{ $order->do_date?->format('d-m-Y') }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">{{ __('Planned Delivery') }}</label>
                    <input type="text" name="delivery_date" class="form-control datepicker" value="{{ $order->delivery_date?->format('d-m-Y') }}">
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Pickup & Delivery') }}</h6></div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Pickup Location') }}</label>
                    <input type="text" name="pickup_location" id="dlv-pickup" class="form-control" value="{{ $order->pickup_location }}" placeholder="{{ __('Port, warehouse or yard') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Consignee') }}</label>
                    <input type="text" name="consignee" id="dlv-consignee" class="form-control" value="{{ $order->consignee }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Delivery Address') }} <sup class="text-danger">*</sup></label>
                    <textarea name="delivery_address" id="dlv-address" class="form-control" rows="3" required>{{ $order->delivery_address }}</textarea>
                </div>
                <div class="col-md-6">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ __('Contact Person') }}</label>
                            <input type="text" name="contact_person" class="form-control" value="{{ $order->contact_person }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ __('Contact Phone') }}</label>
                            <input type="text" name="contact_phone" class="form-control" value="{{ $order->contact_phone }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Transport') }}</h6></div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Transporter') }}</label>
                    <input type="text" name="transporter" class="form-control" value="{{ $order->transporter }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Vehicle No') }}</label>
                    <input type="text" name="vehicle_no" class="form-control" value="{{ $order->vehicle_no }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Driver') }}</label>
                    <input type="text" name="driver_name" class="form-control" value="{{ $order->driver_name }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Driver Phone') }}</label>
                    <input type="text" name="driver_phone" class="form-control" value="{{ $order->driver_phone }}">
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Cargo') }}</h6></div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Container No(s)') }}</label>
                    <input type="text" name="container_no" id="dlv-container" class="form-control" value="{{ $order->container_no }}" placeholder="{{ __('Separate several with commas') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Packages') }}</label>
                    <input type="number" min="0" name="packages" id="dlv-packages" class="form-control" value="{{ $order->packages }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Weight (kg)') }}</label>
                    <input type="text" name="weight" id="dlv-weight" class="form-control float" value="{{ $order->weight }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Cargo Description') }}</label>
                    <textarea name="cargo_description" id="dlv-description" class="form-control" rows="2">{{ $order->cargo_description }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Remarks') }}</label>
                    <textarea name="remarks" class="form-control" rows="2">{{ $order->remarks }}</textarea>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Proof of Delivery') }} <small class="text-muted fw-normal">({{ __('fill in when delivered') }})</small></h6></div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Received By') }}</label>
                    <input type="text" name="received_by" class="form-control" value="{{ $order->received_by }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">{{ __('POD Notes') }}</label>
                    <input type="text" name="pod_notes" class="form-control" value="{{ $order->pod_notes }}" placeholder="{{ __('Stamp, signature, damages …') }}">
                </div>
            </div>
        </div>
    </form>
</div>
