<div class="g-3 align-items-center border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $airwayBill->row_no ?? 'New Airway Bill' }}</span>
            </div>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="Save Airway Bill">
    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" value="{{ $airwayBill->id }}">

        <!-- Airway Bill Header -->
        <div class="mb-4 mt-3 border-0 px-4">
            <div class="card-body">
                <div class="row g-3">
                    <!-- Job Reference -->
                    <div class="col-md-4">
                        <label class="form-label required">Job <sup class="text-danger">*</sup></label>
                        <select name="job_id" class="tom-select" data-live-search="true" required>
                            <option value="">Select Job</option>
                            @foreach($jobs as $job)
                                <option value="{{ $job->id }}"
                                        @selected($airwayBill->job_id == $job->id) data-subtext="{{ $job->customer->name_en }}">
                                    {{ $job->row_no }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Customer -->
                    <div class="col-md-4">
                        <label class="form-label required">Customer <sup class="text-danger">*</sup></label>
                        <x-common.customers :value="$airwayBill->customer_id ?? ''"
                                            :required="true"></x-common.customers>
                    </div>

                    <!-- Airway Bill Date -->
                    <div class="col-md-4">
                        <label class="form-label required">Airway Bill Date <sup class="text-danger">*</sup></label>
                        <input type="date" id="airway_bill_date" name="airway_bill_date" class="form-control datepicker"
                               value="{{ showDate($airwayBill->airway_bill_date) }}" required>
                    </div>

                    <!-- Airway Bill No -->
                    {{--<div class="col-md-4">
                        <label class="form-label required">Airway Bill No <sup class="text-danger">*</sup></label>
                        <input name="row_no" class="form-control" required value="{{ $airwayBill->row_no }}">
                    </div>--}}

                    <!-- Delivery Date -->
                    <div class="col-md-4">
                        <label class="form-label required">Delivery Date <sup class="text-danger">*</sup></label>
                        <input type="date" id="delivery_date" name="delivery_date" class="form-control datepicker"
                               value="{{ showDate($airwayBill->delivery_date) }}" required>
                    </div>

                    <!-- Attachments -->
                    <div class="col-md-4">
                        <label class="form-label">Attachments</label>
                        <input type="file" name="attachments[]" class="form-control" multiple>
                        @if($airwayBill->documents && count($airwayBill->documents))
                            <small class="text-primary text-decoration-underline cursor-pointer"
                                   data-bs-toggle="offcanvas" data-bs-target="#attachmentsDrawer">
                                {{ $airwayBill->documents->count() }}
                                {{ \Illuminate\Support\Str::plural('Document', $airwayBill->documents->count()) }}
                            </small>
                        @endif
                    </div>
                </div>

                <!-- Flight Information Section -->
                <div class="row g-3 mt-3">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">Flight Information</h5>
                    </div>

                    <!-- Origin Airport -->
                    <div class="col-md-3">
                        <label class="form-label required">Origin Airport <sup class="text-danger">*</sup></label>
                        <select id="origin_airport" name="origin_airport" class="tom-select-search" autocomplete="off"
                                data-placeholder="--Select Origin Airport--">
                            <option value="">--Select--</option>
                            @if($airwayBill->origin_airport)
                                @php
                                    $polSplit = explode('-',$airwayBill->origin_airport);
                                @endphp
                                <option value="{{ $airwayBill->origin_airport }}" selected
                                        data-code="{{ $polSplit[1] ?? 'N/A' }}"
                                >{{ $polSplit[0] }}</option>
                            @endif

                            @foreach($polPod as $pol)
                                <option value="{{ $pol->name }}"
                                        data-code="{{ $pol->code }}" data-id="{{ $pol->id }}"
                                >{{ $pol->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Destination Airport -->
                    <div class="col-md-3">
                        <label class="form-label required">Destination Airport <sup class="text-danger">*</sup></label>
                        <select id="destination_airport" name="destination_airport" class="tom-select-search"
                                autocomplete="off"
                                data-placeholder="--Select Port of Discharge--">
                            <option value="" @selected(!$airwayBill->destination_airport)>--Select
                                Destination Airport--
                            </option>
                            @if($airwayBill->destination_airport)
                                @php
                                    $podSplit = explode('-',$airwayBill->destination_airport);
                                @endphp
                                <option value="{{ $airwayBill->destination_airport }}" selected
                                        data-code="{{ $podSplit[1] ?? 'N/A' }}"
                                >{{ $podSplit[0] }}</option>
                            @endif
                            @foreach($polPod as $pod)
                                <option value="{{ $pod->name }}"
                                        data-code="{{ $pod->code }}"
                                        data-id="{{ $pod->id }}">{{ $pod->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Carrier -->
                    <div class="col-md-3">
                        <label class="form-label">Carrier</label>
                        <input type="text" name="carrier" class="form-control" value="{{ $airwayBill->carrier ?? '' }}">
                    </div>

                    <!-- Flight Number -->
                    <div class="col-md-3">
                        <label class="form-label">Flight Number</label>
                        <input type="text" name="flight_number" class="form-control"
                               value="{{ $airwayBill->flight_number ?? '' }}">
                    </div>

                    <!-- Departure Time -->
                    <div class="col-md-3">
                        <label class="form-label">Departure Time</label>
                        <input type="datetime-local" name="departure_time" class="form-control timepicker" autocomplete="off"
                               value="{{ $airwayBill->departure_time ?? '' }}">
                    </div>

                    <!-- Arrival Time -->
                    <div class="col-md-3">
                        <label class="form-label">Arrival Time</label>
                        <input type="datetime-local" name="arrival_time" class="form-control timepicker" autocomplete="off"
                               value="{{ $airwayBill->arrival_time ?? '' }}">
                    </div>
                </div>

                <!-- Delivery Information Section -->
                <div class="row g-3 mt-3">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">Delivery Information</h5>
                    </div>

                    <!-- Delivery Address -->
                    <div class="col-md-6">
                        <label class="form-label required">Delivery Address <sup class="text-danger">*</sup></label>
                        <textarea name="delivery_address" class="form-control h-75" rows="3"
                                  required>{{ $airwayBill->delivery_address ?? '' }}</textarea>
                    </div>

                    <!-- Contact Information -->
                    <div class="col-md-6">
                        <div class="row g-3">
                            <!-- Contact Person -->
                            <div class="col-md-12">
                                <label class="form-label required">Contact Person <sup
                                        class="text-danger">*</sup></label>
                                <input type="text" name="contact_person" class="form-control"
                                       value="{{ $airwayBill->contact_person ?? '' }}" required>
                            </div>

                            <!-- Contact Phone -->
                            <div class="col-md-12">
                                <label class="form-label required">Contact Phone <sup
                                        class="text-danger">*</sup></label>
                                <input type="text" name="contact_phone" class="form-control"
                                       value="{{ $airwayBill->contact_phone ?? '' }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipment Details Section -->
                <div class="row g-3 mt-3">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">Shipment Details</h5>
                    </div>

                    <!-- Shipment Type -->
                    <div class="col-md-4">
                        <label class="form-label required">Shipment Type <sup class="text-danger">*</sup></label>
                        <div class="d-flex gap-4 mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="shipment_type"
                                       id="shipment_type_document" value="document"
                                       {{ ($airwayBill->shipment_type ?? '') == 'document' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="shipment_type_document">
                                    Document
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="shipment_type"
                                       id="shipment_type_parcel"
                                       value="parcel" {{ ($airwayBill->shipment_type ?? '') == 'parcel' ? 'checked' : '' }}>
                                <label class="form-check-label" for="shipment_type_parcel">
                                    Parcel
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="shipment_type"
                                       id="shipment_type_freight"
                                       value="freight" {{ ($airwayBill->shipment_type ?? '') == 'freight' ? 'checked' : '' }}>
                                <label class="form-check-label" for="shipment_type_freight">
                                    Freight
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Service Type -->
                    <div class="col-md-4">
                        <label class="form-label required">Service Type <sup class="text-danger">*</sup></label>
                        <select name="service_type" class="tom-select" required>
                            <option value="">Select Service Type</option>
                            <option
                                value="standard" {{ ($airwayBill->service_type ?? '') == 'standard' ? 'selected' : '' }}>
                                Standard
                            </option>
                            <option
                                value="express" {{ ($airwayBill->service_type ?? '') == 'express' ? 'selected' : '' }}>
                                Express
                            </option>
                            <option
                                value="same_day" {{ ($airwayBill->service_type ?? '') == 'same_day' ? 'selected' : '' }}>
                                Same Day
                            </option>
                        </select>
                    </div>

                    <!-- Payment Method -->
                    <div class="col-md-4">
                        <label class="form-label required">Payment Method <sup class="text-danger">*</sup></label>
                        <select name="payment_method" class="tom-select" required>
                            <option value="">Select Payment Method</option>
                            <option
                                value="prepaid" {{ ($airwayBill->payment_method ?? '') == 'prepaid' ? 'selected' : '' }}>
                                Prepaid
                            </option>
                            <option
                                value="collect" {{ ($airwayBill->payment_method ?? '') == 'collect' ? 'selected' : '' }}>
                                Collect
                            </option>
                            <option
                                value="third_party" {{ ($airwayBill->payment_method ?? '') == 'third_party' ? 'selected' : '' }}>
                                Third Party
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Airway Bill Items Table -->
        <div class="border-0 mb-4">
            <div class="card-body p-0">
                <table class="table align-middle mb-0" id="airwayBillItemsTable">
                    <thead class="table-light">
                    <tr>
                        <th>Description</th>
                        <th>Comment</th>
                        <th class="text-end">Quantity</th>
                        <th class="text-end">Weight (kg)</th>
                        <th class="text-end">Dimensions (cm)</th>
                        <th class="text-center">Fragile</th>
                        <th></th>
                    </tr>
                    </thead>

                    <tbody id="AIRWAY_BILL-tbody">
                    @if(count($airwayBill->airwayBillSubs) > 0)
                        @foreach($airwayBill->airwayBillSubs as $subItem)
                            <tr class="align-middle main-row">
                                <!-- Description -->
                                <td class="col-md-3">
                                    <x-common.description :value="$subItem->description_id" required="required"/>
                                </td>

                                <!-- Comment -->
                                <td class="col-md-3">
                                    <textarea name="comment[]" class="form-control">{{ $subItem->comment }}</textarea>
                                </td>

                                <!-- Quantity -->
                                <td class="col-md-1">
                                    <input type="text" name="quantity[]" class="form-control text-end float quantity"
                                           autocomplete="off" value="{{ $subItem->quantity }}" min="1" required>
                                </td>

                                <!-- Weight -->
                                <td class="col-md-1">
                                    <input type="text" name="weight[]" class="form-control text-end float weight"
                                           autocomplete="off" value="{{ $subItem->weight }}" min="0">
                                </td>

                                <!-- Dimensions -->
                                <td class="col-md-2">
                                    <div class="input-group">
                                        <input type="text" name="length[]" placeholder="L"
                                               class="form-control text-end float"
                                               autocomplete="off" value="{{ $subItem->length }}" min="0">
                                        <input type="text" name="width[]" placeholder="W"
                                               class="form-control text-end float"
                                               autocomplete="off" value="{{ $subItem->width }}" min="0">
                                        <input type="text" name="height[]" placeholder="H"
                                               class="form-control text-end float"
                                               autocomplete="off" value="{{ $subItem->height }}" min="0">
                                    </div>
                                </td>

                                <!-- Fragile -->
                                <td class="col-md-1 text-center">
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" name="fragile[]" value="1"
                                            {{ $subItem->fragile ? 'checked' : '' }}>
                                    </div>
                                </td>

                                <td class="col-md-1 align-content-center">
                                    <div class="d-flex justify-content-between gap-3 action-icons">
                                        <div class="add-row">
                                            <i class="bi bi-plus-circle text-muted"></i>
                                        </div>
                                        <div class="remove-row">
                                            <i class="bi bi-trash text-danger"></i>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr class="align-middle main-row">
                            <!-- Description -->
                            <td class="col-md-3">
                                <x-common.description required="required"/>
                            </td>

                            <!-- Comment -->
                            <td class="col-md-3">
                                <textarea name="comment[]" class="form-control"></textarea>
                            </td>

                            <!-- Quantity -->
                            <td class="col-md-1">
                                <input type="text" name="quantity[]" class="form-control text-end float quantity"
                                       autocomplete="off" value="1" min="1" required>
                            </td>

                            <!-- Weight -->
                            <td class="col-md-1">
                                <input type="text" name="weight[]" class="form-control text-end float weight"
                                       autocomplete="off" value="0" min="0">
                            </td>

                            <!-- Dimensions -->
                            <td class="col-md-2">
                                <div class="input-group">
                                    <input type="text" name="length[]" placeholder="L"
                                           class="form-control text-end float"
                                           autocomplete="off" value="0" min="0">
                                    <input type="text" name="width[]" placeholder="W"
                                           class="form-control text-end float"
                                           autocomplete="off" value="0" min="0">
                                    <input type="text" name="height[]" placeholder="H"
                                           class="form-control text-end float"
                                           autocomplete="off" value="0" min="0">
                                </div>
                            </td>

                            <!-- Fragile -->
                            <td class="col-md-1 text-center">
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input" type="checkbox" name="fragile[]" value="1">
                                </div>
                            </td>

                            <td class="col-md-1 align-content-center">
                                <div class="d-flex justify-content-between gap-3 action-icons">
                                    <div class="add-row">
                                        <i class="bi bi-plus-circle text-muted"></i>
                                    </div>
                                    <div class="remove-row">
                                        <i class="bi bi-trash text-danger"></i>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Special Instructions -->
        <div class="mt-3 px-4">
            <label class="form-label fw-semibold">Special Instructions</label>
            <textarea name="special_instructions" class="form-control h-100" rows="4"
                      placeholder="Any special handling instructions...">{{ $airwayBill->special_instructions ?? '' }}</textarea>
        </div>
    </form>
</div>
