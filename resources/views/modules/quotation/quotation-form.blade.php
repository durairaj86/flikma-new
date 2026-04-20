<div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="Save Quotation">
    <!-- Meta Info -->
    <div class="row g-3 align-items-center bg-white border-bottom py-2 mb-3 small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                <div class="module-info">
                    <span
                        class="fw-semibold fs-5">{{ $quotation->row_no ?? (isset($enquiryData) ? 'New Quotation from Enquiry' : 'New Quotation') }}</span>
                </div>

            </div>

            <!-- Save & Next Button -->
            <div id="show-buttons"></div>
            {{--<div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" id="btn-cancel">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>
                <button type="submit" class="btn btn-primary" form="moduleForm" id="modalSaveBtn">
                    <i class="bi bi-save me-1"></i> Save Enquiry
                </button>
            </div>--}}
        </div>
    </div>
    <div class="row">
        <div class="d-flex justify-content-center">
            <div class="d-inline-block p-1">
                <ul class="nav status-tabs align-items-center border-bottom mb-0 mt-0 justify-content-center"
                    id="modalTabs" role="tablist">
                    <li class="nav-item me-2">
                        <button
                            class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn active"
                            data-bs-toggle="tab" data-bs-target="#general"
                            type="button">
                            <i class="bi bi-info-circle me-1"></i> General
                        </button>
                    </li>
                    <li class="nav-item me-2">
                        <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#container"
                                type="button">
                            <i class="bi bi-layout-wtf me-1"></i> Containers
                        </button>
                    </li>
                    <li class="nav-item me-2">
                        <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#package"
                                type="button">
                            <i class="bi bi-box-seam me-1"></i> Packages
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#other"
                                type="button">
                            <i class="bi bi bi-collection me-1"></i> Other Info
                        </button>
                    </li>
                </ul>
            </div>
        </div>
        <form id="moduleForm" novalidate action="{{ request()->url() }}">
            @csrf
            <input type="hidden" name="data-id" value="{{ $quotation->id }}">

            <div class="tab-content" id="quotationTabsContent">

                <!-- General Tab -->
                <div class="tab-pane show active" id="general" role="tabpanel">

                    <!-- Quotation Info -->
                    <div class="model-form-tab-div">
                        <div class="model-form-sub-title">
                            <h5>General</h5>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label d-flex align-items-center">
                                    Customer
                                    <i class="bi bi-info-circle ms-1 text-muted" data-bs-toggle="tooltip"
                                       data-bs-placement="right"
                                       title="Enter customer name or select from database"></i>
                                </label>
                                <x-common.customers :value="$quotation->customer_id" :required="true"></x-common.customers>
                                {{--<select id="customer" name="customer" autocomplete="off">
                                    @foreach(\App\Models\Customer\Customer::confirmedCustomers() as $customer)
                                        <option value="{{ encodeId($customer->id) }}"
                                                data-subtext="{{ $customer->email }}">{{ $customer->name_en }}</option>
                                    @endforeach
                                </select>--}}
                            </div>
                            <div class="col-md-4">
                                <label class="form-label d-flex justify-content-between align-items-center">
                                    Select Prospect Customer
                                </label>
                                <select name="prospect" id="prospect" class="tom-select"
                                        data-live-search="true" {{ $quotation->prospect_id ? 'data-has-prospect=true' : '' }}>
                                    <option value="">--Select--</option>
                                    @foreach(\App\Models\Prospect\Prospect::prospectCustomers() as $prospect)
                                        <option value="{{ encodeId($prospect->id) }}"
                                                data-subtext="{{ $prospect->row_no }}"
                                            @selected($prospect->id==$quotation->prospect_id)>
                                            {{ $prospect->name_en }}
                                        </option>
                                    @endforeach
                                    <option data-divider="true"></option>
                                    <option value="__new__" data-type="new" data-module="PROSPECT">+ Add New
                                        Prospect
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label d-flex align-items-center">
                                    Quotation Date
                                    <i class="bi bi-info-circle ms-1 text-muted" data-bs-toggle="tooltip"
                                       data-bs-placement="right" title="Date on which the quotation is created"></i>
                                </label>
                                <input type="date" class="form-control datepicker" name="posted_at"
                                       autocomplete="off"
                                       value="{{ $quotation->posted_at }}"
                                       maxlength="10">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label d-flex align-items-center">
                                    Valid Until
                                    <i class="bi bi-info-circle ms-1 text-muted" data-bs-toggle="tooltip"
                                       data-bs-placement="right" title="The last date this quotation is valid"></i>
                                </label>
                                <input type="date" class="form-control datepicker" name="valid_until" autocomplete="off"
                                       value="{{ $quotation->valid_until }}"
                                       maxlength="10">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Select Services</label>
                                <x-common.service :value="$quotation->services"/>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Salesman</label>
                                <x-common.salesperson
                                    :value="$quotation->salesperson_id"></x-common.salesperson>
                            </div>
                            {{--<div class="col-md-4">
                                <label class="form-label">Prepared By</label>
                                <input type="text" class="form-control" name="prepared_by" autocomplete="off" maxlength="50">
                            </div>--}}
                        </div>
                    </div>

                    <!-- Cargo Routing -->
                    <div class="model-form-tab-div">
                        <div class="model-form-sub-title">
                            <h5>Cargo Routing</h5>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="activity_id" class="form-label">Activity</label>
                                <x-common.activity
                                    :value="$quotation->activity_id"></x-common.activity>
                            </div>
                            {{--<div class="col-md-4">
                                <label class="form-label">Shipment Mode</label>
                                <select class="form-control tom-select" name="shipment_mode" id="shipment_mode">
                                    @foreach(shipmentMode() as $modeId => $mode)
                                        <option
                                            value="{{ $modeId }}" @selected($quotation->shipment_mode == $modeId)>{{ $mode }}</option>
                                    @endforeach
                                </select>
                            </div>--}}
                            {{--<div class="col-md-4">
                                <label class="form-label">Shipment Category</label>
                                <select class="form-control tom-select" name="shipment_category">
                                    @foreach(shipmentCategory() as $shipmentId => $shipmentType)
                                        <option
                                            value="{{ $shipmentId }}" @selected($quotation->shipment_category == $shipmentId)>{{ $shipmentType }}</option>
                                    @endforeach
                                </select>
                            </div>--}}

                            <div class="col-md-4">
                                <label class="form-label">Place of Receipt</label>
                                <input type="text" class="form-control" name="place_of_receipt" autocomplete="off"
                                       value="{{ $quotation->place_of_receipt }}"
                                       maxlength="100">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Port of Loading (POL)</label>
                                <select id="pol" name="pol" class="tom-select-search" autocomplete="off" required
                                        data-placeholder="--Select Port of Loading--">
                                    <option value="">--Select--</option>
                                    @if($quotation->pol)
                                        <option value="{{ $quotation->pol }}" selected>{{ $quotation->pol }}</option>
                                    @endif
                                    @foreach($polPod as $pol)
                                        <option value="{{ $pol->id }}">{{ $pol->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Port of Discharge (POD)</label>
                                <select id="pod" name="pod" class="tom-select-search" autocomplete="off" required
                                        data-placeholder="--Select Port of Discharge--">
                                    <option value="" @selected(!$quotation->pod)>--Select
                                        Port of Discharge--
                                    </option>
                                    @if($quotation->pod)
                                        <option value="{{ $quotation->pod }}" selected>{{ $quotation->pod }}</option>
                                    @endif
                                    @foreach($polPod as $pod)
                                        <option value="{{ $pod->id }}">{{ $pod->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Place of Delivery</label>
                                <input type="text" class="form-control" name="place_of_delivery" autocomplete="off"
                                       value="{{ $quotation->place_of_delivery }}"
                                       maxlength="100">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Final Destination</label>
                                <input type="text" class="form-control" name="final_destination" autocomplete="off"
                                       value="{{ $quotation->final_destination }}"
                                       maxlength="100">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pickup Date</label>
                                <input type="date" name="pickup_date" id="pickup_date"
                                       class="form-control rounded-3 datepicker"
                                       value="{{ $quotation->pickup_date }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pickup Address</label>
                                <textarea name="pickup_address" id="pickup_address"
                                          class="form-control rounded-3">{{ $quotation->pickup_address }}</textarea>
                            </div>
                            {{--<div class="col-md-6 ">
                                <label class="form-label d-flex align-items-center">
                                    Carrier / Line
                                    <i class="bi bi-info-circle ms-1 text-muted" data-bs-toggle="tooltip"
                                       data-bs-placement="right" title="Enter carrier name, suggestions will appear"></i>
                                </label>
                                <select class="form-control tom-select" data-live-search="true">
                                    <option value="">select</option>
                                </select>
                            </div>--}}
                        </div>
                    </div>
                    <div class="model-form-tab-div">
                        <div class="model-form-sub-title">
                            <h5>Cargo Details</h5>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Carrier / Line</label>
                                <select id="carrier" name="carrier" class="tom-select-search" data-live-search="true"
                                        data-placeholder="--Select Carrier--" autocomplete="off">
                                    <option value="">--Select--</option>
                                    <option value="{{ $quotation->carrier }}">{{ $quotation->carrier }}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Shipper</label>
                                <input type="text" name="shipper" id="shipper"
                                       class="form-control rounded-3"
                                       value="{{ $quotation->shipper }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Incoterm</label>
                                <select class="tom-select" name="incoterm" data-live-search="true">
                                    <option value="">Select</option>
                                    @foreach(incoterms() as $incoterm)
                                        <option value="{{ $incoterm->code }}"
                                                data-subtext="{{ $incoterm->description }}" @selected($quotation->incoterm == $incoterm->code)>{{ $incoterm->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Commodity</label>
                                <input type="text" name="commodity" id="commodity" class="form-control rounded-3"
                                       value="{{ $quotation->commodity }}">
                            </div>
                            {{--<div class="col-md-6 ">
                                <label class="form-label d-flex align-items-center">
                                    Carrier / Line
                                    <i class="bi bi-info-circle ms-1 text-muted" data-bs-toggle="tooltip"
                                       data-bs-placement="right" title="Enter carrier name, suggestions will appear"></i>
                                </label>
                                <select class="form-control tom-select" data-live-search="true">
                                    <option value="">select</option>
                                </select>
                            </div>--}}
                        </div>
                    </div>

                </div>

                <!-- Container Tab -->
                <div class="tab-pane mt-4" id="container" role="tabpanel">
                    <table class="table align-middle" id="containerTable">
                        <thead class="table-light">
                        <tr>
                            <th>Size</th>
                            <th>Container No.</th>
                            <th>Seal No.</th>
                            <th>Gross Wt (Kg)</th>
                            <th>Net Wt (Kg)</th>
                            <th>CBM</th>
                            <th>Hazardous</th>
                            <th width="5%"></th>
                        </tr>
                        </thead>
                        <tbody>
                        {{--<tr>
                        <td>
                            <x-common.container_size></x-common.container_size>
                        </td>
                        <td><input type="text" name="container_number[]" class="form-control" autocomplete="off"
                                   maxlength="20"></td>
                        <td><input type="text" name="seal_number[]" class="form-control" autocomplete="off" maxlength="20">
                        </td>
                        <td><input type="number" name="gross_weight[]" class="form-control" autocomplete="off"
                                   maxlength="8"></td>
                        <td><input type="number" name="net_weight[]" class="form-control" autocomplete="off" maxlength="8">
                        </td>
                        <td><input type="number" name="volume[]" class="form-control" autocomplete="off" maxlength="10">
                        </td>
                        <td>
                            <select name="hazardous[]" class="form-select">
                                <option>No</option>
                                <option>Yes</option>
                            </select>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
                        </td>
                        </tr>--}}
                        @php
                            $containers = $quotation->containers && $quotation->containers->count() > 0
                                ? $quotation->containers
                                : [null];
                        @endphp
                        @foreach($containers as $container)
                            <tr>
                                <td>
                                    <x-common.container_size :value="$container->container_size ?? ''"/>
                                </td>
                                <td><input type="text" name="container_number[]" class="form-control"
                                           value="{{ $container->container_number ?? '' }}"></td>
                                <td><input type="text" name="seal_number[]" class="form-control"
                                           value="{{ $container->seal_number ?? '' }}"></td>
                                <td><input type="number" name="gross_weight[]" class="form-control"
                                           value="{{ $container->gross_weight ?? '' }}"></td>
                                <td><input type="number" name="net_weight[]" class="form-control"
                                           value="{{ $container->net_weight ?? '' }}"></td>
                                <td><input type="number" name="volume[]" class="form-control"
                                           value="{{ $container->volume ?? '' }}"></td>
                                <td>
                                    <select name="hazardous[]" class="form-control tom-select hazardous">
                                        <option
                                            value="0" @selected(!isset($container->hazardous) || $container->hazardous == 0)>
                                            No
                                        </option>
                                        <option value="1" {{ ($container->hazardous ?? '') == 1 ? 'selected':'' }}>Yes
                                        </option>
                                    </select>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                    <button type="button" id="addContainerRow" class="btn btn-sm btn-primary">+ Add Container</button>
                </div>

                <!-- Package Tab -->
                <div class="tab-pane mt-4" id="package" role="tabpanel">
                    <table class="table align-middle" id="packageTable">
                        <thead class="table-light">
                        <tr>
                            <th>Commodity</th>
                            <th>Description</th>
                            <th>HS Code</th>
                            <th>L (cm)</th>
                            <th>W (cm)</th>
                            <th>H (cm)</th>
                            <th>Weight (Kg)</th>
                            <th width="5%"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @php
                            $packages = $quotation->packages && $quotation->packages->count() > 0
                                ? $quotation->packages
                                : [null];
                        @endphp
                        @foreach($packages as $package)
                            <tr>
                                <td>
                                    <select name="commodity_type[]" class="form-control tom-select">
                                        <option value="">Select</option>
                                        @foreach(commodityType() as $id => $name)
                                            <option
                                                value="{{ $id }}" @selected($package && $package->commodity_type == $id)>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="text" name="description_goods[]" class="form-control"
                                           value="{{ $package->description_goods ?? '' }}"></td>
                                <td><input type="text" name="hs_code[]" class="form-control"
                                           value="{{ $package->hs_code ?? '' }}"></td>
                                <td><input type="number" name="length[]" class="form-control"
                                           value="{{ $package->length ?? '' }}"></td>
                                <td><input type="number" name="width[]" class="form-control"
                                           value="{{ $package->width ?? '' }}"></td>
                                <td><input type="number" name="height[]" class="form-control"
                                           value="{{ $package->height ?? '' }}"></td>
                                <td><input type="number" name="package_weight[]" class="form-control"
                                           value="{{ $package->package_weight ?? '' }}"></td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <button type="button" id="addPackageRow" class="btn btn-sm btn-primary">+ Add Package</button>
                </div>

                <!-- Other Info Tab -->
                <div class="tab-pane" id="other" role="tabpanel">
                    <div class="row">
                        <div class="col-12 g-3">
                            <label class="form-label">Terms & Conditions</label>
                            <textarea class="form-control h-100" rows="3" name="terms" autocomplete="off"
                                      maxlength="500">{{ $quotation->terms ?? (isset($enquiryData->id) ? "This quotation was created from Enquiry " . $quotation->row_no . (isset($enquiryData->prospect) && $quotation->prospect_id ? "\nProspect Customer: " . $quotation->prospect->name . " (" . $quotation->prospect->row_no . ")" : "") : "") }}</textarea>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>
