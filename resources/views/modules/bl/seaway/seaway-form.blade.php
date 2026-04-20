<div class="g-3 align-items-center border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $seawayBill->row_no ?? 'New Seaway Bill' }}</span>
            </div>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="Save Seaway Bill">
    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" value="{{ $seawayBill->id }}">

        <!-- Seaway Bill Header -->
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
                                        @selected($seawayBill->job_id == $job->id) data-subtext="{{ $job->customer->name_en }}">
                                    {{ $job->row_no }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Customer -->
                    <div class="col-md-4">
                        <label class="form-label required">Customer <sup class="text-danger">*</sup></label>
                        <x-common.customers :value="$seawayBill->customer_id ?? ''"
                                            :required="true"></x-common.customers>
                    </div>

                    <!-- Seaway Bill Date -->
                    <div class="col-md-4">
                        <label class="form-label required">Seaway Bill Date <sup class="text-danger">*</sup></label>
                        <input type="date" id="seaway_bill_date" name="seaway_bill_date" class="form-control datepicker"
                               value="{{ showDate($seawayBill->seaway_bill_date) }}"
                               required>
                    </div>

                    <!-- Seaway Bill No -->
                    {{--<div class="col-md-4">
                        <label class="form-label required">Seaway Bill No <sup class="text-danger">*</sup></label>
                        <input name="row_no" class="form-control" required value="{{ $seawayBill->row_no }}">
                    </div>--}}

                    <!-- Delivery Date -->
                    <div class="col-md-4">
                        <label class="form-label required">Delivery Date <sup class="text-danger">*</sup></label>
                        <input type="date" id="delivery_date" name="delivery_date" class="form-control datepicker"
                               value="{{ showDate($seawayBill->delivery_date) }}" required>
                    </div>

                    <!-- Attachments -->
                    <div class="col-md-4">
                        <label class="form-label">Attachments</label>
                        <input type="file" name="attachments[]" class="form-control" multiple>
                        @if($seawayBill->documents && count($seawayBill->documents))
                            <small class="text-primary text-decoration-underline cursor-pointer"
                                   data-bs-toggle="offcanvas" data-bs-target="#attachmentsDrawer">
                                {{ $seawayBill->documents->count() }}
                                {{ \Illuminate\Support\Str::plural('Document', $seawayBill->documents->count()) }}
                            </small>
                        @endif
                    </div>
                </div>

                <!-- Vessel Information Section -->
                <div class="row g-3 mt-3">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">Vessel Information</h5>
                    </div>

                    <!-- Origin Port -->
                    <div class="col-md-3">
                        <label class="form-label required">Origin Port <sup class="text-danger">*</sup></label>
                        <select id="origin_port" name="origin_port" class="tom-select-search" autocomplete="off"
                                data-placeholder="--Select Origin Port--">
                            <option value="">--Select--</option>
                            @if($seawayBill->origin_port)
                                @php
                                    $polSplit = explode('-',$seawayBill->origin_port);
                                @endphp
                                <option value="{{ $seawayBill->origin_port }}" selected
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

                    <!-- Destination Port -->
                    <div class="col-md-3">
                        <label class="form-label required">Destination Port <sup class="text-danger">*</sup></label>
                        <select id="destination_port" name="destination_port" class="tom-select-search"
                                autocomplete="off"
                                data-placeholder="--Select Destination Port--">
                            <option value="" @selected(!$seawayBill->destination_port)>--Select
                                Destination Airport--
                            </option>
                            @if($seawayBill->destination_port)
                                @php
                                    $podSplit = explode('-',$seawayBill->destination_port);
                                @endphp
                                <option value="{{ $seawayBill->destination_port }}" selected
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

                    <!-- Vessel Name -->
                    <div class="col-md-3">
                        <label class="form-label">Vessel Name</label>
                        <input type="text" name="vessel_name" class="form-control"
                               value="{{ $seawayBill->vessel_name ?? '' }}">
                    </div>

                    <!-- Voyage Number -->
                    <div class="col-md-3">
                        <label class="form-label">Voyage Number</label>
                        <input type="text" name="voyage_number" class="form-control"
                               value="{{ $seawayBill->voyage_number ?? '' }}">
                    </div>

                    <!-- Departure Time -->
                    <div class="col-md-3">
                        <label class="form-label">Departure Time</label>
                        <input type="datetime-local" name="departure_time" class="form-control timepicker" autocomplete="off"
                               value="{{ $seawayBill->departure_time ?? '' }}">
                    </div>

                    <!-- Arrival Time -->
                    <div class="col-md-3">
                        <label class="form-label">Arrival Time</label>
                        <input type="datetime-local" name="arrival_time" class="form-control timepicker" autocomplete="off"
                               value="{{ $seawayBill->arrival_time ?? '' }}">
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
                                  required>{{ $seawayBill->delivery_address ?? '' }}</textarea>
                    </div>

                    <!-- Contact Information -->
                    <div class="col-md-6">
                        <div class="row g-3">
                            <!-- Contact Person -->
                            <div class="col-md-12">
                                <label class="form-label required">Contact Person <sup
                                        class="text-danger">*</sup></label>
                                <input type="text" name="contact_person" class="form-control"
                                       value="{{ $seawayBill->contact_person ?? '' }}" required>
                            </div>

                            <!-- Contact Phone -->
                            <div class="col-md-12">
                                <label class="form-label required">Contact Phone <sup
                                        class="text-danger">*</sup></label>
                                <input type="text" name="contact_phone" class="form-control"
                                       value="{{ $seawayBill->contact_phone ?? '' }}" required>
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
                                       {{ ($seawayBill->shipment_type ?? '') == 'document' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="shipment_type_document">
                                    Document
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="shipment_type"
                                       id="shipment_type_parcel"
                                       value="parcel" {{ ($seawayBill->shipment_type ?? '') == 'parcel' ? 'checked' : '' }}>
                                <label class="form-check-label" for="shipment_type_parcel">
                                    Parcel
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="shipment_type"
                                       id="shipment_type_freight"
                                       value="freight" {{ ($seawayBill->shipment_type ?? '') == 'freight' ? 'checked' : '' }}>
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
                                value="standard" {{ ($seawayBill->service_type ?? '') == 'standard' ? 'selected' : '' }}>
                                Standard
                            </option>
                            <option
                                value="express" {{ ($seawayBill->service_type ?? '') == 'express' ? 'selected' : '' }}>
                                Express
                            </option>
                            <option
                                value="same_day" {{ ($seawayBill->service_type ?? '') == 'same_day' ? 'selected' : '' }}>
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
                                value="prepaid" {{ ($seawayBill->payment_method ?? '') == 'prepaid' ? 'selected' : '' }}>
                                Prepaid
                            </option>
                            <option
                                value="collect" {{ ($seawayBill->payment_method ?? '') == 'collect' ? 'selected' : '' }}>
                                Collect
                            </option>
                            <option
                                value="third_party" {{ ($seawayBill->payment_method ?? '') == 'third_party' ? 'selected' : '' }}>
                                Third Party
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seaway Bill Items Table -->
        <div class="border-0 mb-4">
            <div class="card-body p-0">
                <table class="table align-middle mb-0" id="seawayBillItemsTable">
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

                    <tbody id="SEAWAYBILL-tbody">
                    @if(count($seawayBill->seawayBillSubs) > 0)
                        @foreach($seawayBill->seawayBillSubs as $subItem)
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
                      placeholder="Any special handling instructions...">{{ $seawayBill->special_instructions ?? '' }}</textarea>
        </div>
    </form>
</div>
