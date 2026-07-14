<div class="container-fluid px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
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
                    <li class="nav-item me-2">
                        <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#charges"
                                type="button">
                            <i class="bi bi-receipt me-1"></i> Charges
                        </button>
                    </li>
                </ul>
            </div>
        </div>
        <form id="moduleForm" novalidate action="{{ request()->url() }}">
            @csrf
            <input type="hidden" name="data-id" value="{{ $quotation->id }}">
            <input type="hidden" name="enquiry_id" value="{{ $quotation->enquiry_id }}">

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
                                    Prospect Customer
                                </label>
                                <select name="prospect" id="prospect" class="tom-select"
                                        data-live-search="true" {{ $quotation->prospect_id ? 'data-has-prospect=true' : '' }}>
                                    <option value="">--Select--</option>
                                    @foreach(\App\Models\Prospect\Prospect::prospectCustomers() as $prospect)
                                        <option value="{{ encodeId($prospect->id) }}"
                                                data-subtext="{{ $prospect->row_no }}"
                                            @selected($prospect->id==$quotation->prospect_id)>
                                            {{ $prospect->name }}
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
                                <label class="form-label">Origin</label>
                                <select id="pol" name="pol" class="tom-select-search" autocomplete="off" required
                                        data-placeholder="--Select Origin--">
                                    <option value="">--Select Origin--</option>
                                    @if($quotation->pol)
                                        <option value="{{ $quotation->pol }}" selected>{{ $quotation->pol }}</option>
                                    @endif
                                    @foreach($polPod as $pol)
                                        <option value="{{ $pol->id }}">{{ $pol->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Destination</label>
                                <select id="pod" name="pod" class="tom-select-search" autocomplete="off" required
                                        data-placeholder="--Select Destination--">
                                    <option value="" @selected(!$quotation->pod)>--Select Destination--
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

                    @php
                        $containers = $quotation->containers && $quotation->containers->count() > 0
                            ? $quotation->containers
                            : [null];
                    @endphp

                    <div id="containerList">
                    @foreach($containers as $container)
                        <div class="container-card card mb-3 border">
                            <div class="card-header d-flex justify-content-between align-items-center py-2 bg-light">
                                <span class="fw-semibold small text-muted">Container / Consignment</span>
                                <button type="button" class="btn btn-outline-danger btn-sm remove-row">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <div class="card-body p-3">
                                {{-- Row 1 --}}
                                <div class="row g-2 align-items-end">
                                    <div class="col-xl col-md-3 col-sm-6">
                                        <label class="form-label small mb-1">Size</label>
                                        <x-common.container_size :value="$container->container_size ?? ''"/>
                                    </div>
                                    <div class="col-xl col-md-3 col-sm-6">
                                        <label class="form-label small mb-1">Container No.</label>
                                        <input type="text" name="container_number[]" class="form-control form-control-sm"
                                               value="{{ $container->container_number ?? '' }}" autocomplete="off">
                                    </div>
                                    <div class="col-xl col-md-3 col-sm-6">
                                        <label class="form-label small mb-1">Seal No.</label>
                                        <input type="text" name="seal_number[]" class="form-control form-control-sm"
                                               value="{{ $container->seal_number ?? '' }}" autocomplete="off">
                                    </div>
                                    <div class="col-xl col-md-3 col-sm-6">
                                        <label class="form-label small mb-1">Carrier</label>
                                        <input type="text" name="ctn_carrier[]" class="form-control form-control-sm"
                                               value="{{ $container->carrier ?? '' }}" autocomplete="off">
                                    </div>
                                    <div class="col-xl col-md-3 col-sm-6">
                                        <label class="form-label small mb-1">Vessel Name</label>
                                        <input type="text" name="vessel_name[]" class="form-control form-control-sm"
                                               value="{{ $container->vessel_name ?? '' }}" autocomplete="off">
                                    </div>
                                    <div class="col-xl col-md-3 col-sm-6">
                                        <label class="form-label small mb-1">Voyage No.</label>
                                        <input type="text" name="voyage_no[]" class="form-control form-control-sm"
                                               value="{{ $container->voyage_no ?? '' }}" autocomplete="off">
                                    </div>
                                    <div class="col-xl col-md-3 col-sm-4">
                                        <label class="form-label small mb-1">No. of Pcs</label>
                                        <input type="number" name="no_of_pcs[]" class="form-control form-control-sm"
                                               value="{{ $container->no_of_pcs ?? '' }}" min="0">
                                    </div>
                                    <div class="col-xl col-md-3 col-sm-4">
                                        <label class="form-label small mb-1">Gross Wt (Kg)</label>
                                        <input type="number" name="gross_weight[]" class="form-control form-control-sm"
                                               value="{{ $container->gross_weight ?? '' }}" step="0.01" min="0">
                                    </div>
                                    <div class="col-xl col-md-3 col-sm-4">
                                        <label class="form-label small mb-1">Net Wt (Kg)</label>
                                        <input type="number" name="net_weight[]" class="form-control form-control-sm"
                                               value="{{ $container->net_weight ?? '' }}" step="0.01" min="0">
                                    </div>
                                    <div class="col-xl col-md-3 col-sm-4">
                                        <label class="form-label small mb-1">Wt Unit</label>
                                        <input type="text" name="weight_unit[]" class="form-control form-control-sm"
                                               value="{{ $container->weight_unit ?? '' }}"
                                               list="ctn-weight-unit-list" placeholder="KGS">
                                    </div>
                                </div>

                                {{-- Row 2 --}}
                                <div class="row g-2 align-items-end mt-1">
                                    <div class="col-xl col-md-3 col-sm-4">
                                        <label class="form-label small mb-1">Hazardous</label>
                                        <select name="hazardous[]" class="form-select form-select-sm tom-select hazardous">
                                            <option value="0" @selected(!isset($container->hazardous) || $container->hazardous == 0)>No</option>
                                            <option value="1" @selected(($container->hazardous ?? 0) == 1)>Yes</option>
                                        </select>
                                    </div>
                                    <div class="col-xl col-md-3 col-sm-4">
                                        <label class="form-label small mb-1">Volume (CBM)</label>
                                        <input type="number" name="volume[]" class="form-control form-control-sm"
                                               value="{{ $container->volume ?? '' }}" step="0.01" min="0">
                                    </div>
                                    <div class="col-xl col-md-3 col-sm-4">
                                        <label class="form-label small mb-1">Volume Weight</label>
                                        <input type="number" name="volume_weight[]" class="form-control form-control-sm"
                                               value="{{ $container->volume_weight ?? '' }}" step="0.01" min="0">
                                    </div>
                                    <div class="col-xl col-md-3 col-sm-4">
                                        <label class="form-label small mb-1">Vol Unit</label>
                                        <input type="text" name="volume_unit[]" class="form-control form-control-sm"
                                               value="{{ $container->volume_unit ?? '' }}"
                                               list="ctn-volume-unit-list" placeholder="CBM">
                                    </div>
                                    <div class="col-xl col-md-3 col-sm-4">
                                        <label class="form-label small mb-1">Chargeable Unit</label>
                                        <input type="number" name="chargeable_unit[]" class="form-control form-control-sm"
                                               value="{{ $container->chargeable_unit ?? '' }}" step="0.01" min="0" readonly>
                                    </div>
                                    <div class="col-xl col-md-3 col-sm-4">
                                        <label class="form-label small mb-1">HS Code</label>
                                        <input type="text" name="ctn_hs_code[]" class="form-control form-control-sm"
                                               value="{{ $container->hs_code ?? '' }}" autocomplete="off">
                                    </div>
                                    <div class="col-xl col-md-3 col-sm-6">
                                        <label class="form-label small mb-1">Container Type</label>
                                        <x-common.container_types name="container_type[]" :value="$container->container_type ?? null"/>
                                    </div>
                                    <div class="col-xl col-md-3 col-sm-6">
                                        <label class="form-label small mb-1">Description</label>
                                        <textarea name="description[]" class="form-control form-control-sm" rows="1">{{ $container->description ?? '' }}</textarea>
                                    </div>
                                    <div class="col-xl col-md-3 col-sm-6">
                                        <label class="form-label small mb-1">Consignment Remarks</label>
                                        <textarea name="consignment_remarks[]" class="form-control form-control-sm" rows="1">{{ $container->consignment_remarks ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>{{-- .card-body --}}
                        </div>{{-- .container-card --}}
                    @endforeach
                    </div>{{-- #containerList --}}

                    {{-- datalists (shared across all cards) --}}
                    <datalist id="ctn-weight-unit-list">
                        <option value="KGS"><option value="LBS"><option value="MT"><option value="TON">
                    </datalist>
                    <datalist id="ctn-volume-unit-list">
                        <option value="CBM"><option value="CFT"><option value="CM">
                    </datalist>

                    <button type="button" id="addContainerRow" class="btn btn-sm btn-primary mt-1">
                        <i class="bi bi-plus-circle me-1"></i>Add Container
                    </button>
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

                <!-- Charges Tab -->
                <div class="tab-pane mt-3" id="charges" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle" id="chargesTable">
                            <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:38px">#</th>
                                <th style="min-width:200px">Charge Description</th>
                                <th style="min-width:110px">Unit</th>
                                <th style="min-width:60px">Qty</th>
                                <th style="min-width:85px">Currency</th>
                                <th style="min-width:80px">Ex.Rate</th>
                                <th style="min-width:100px">Amount/Qty</th>
                                <th style="min-width:105px">FCY Amount</th>
                                <th style="min-width:110px">Amount (Local)</th>
                                <th style="min-width:100px">Tax Group</th>
                                <th style="min-width:140px">Remarks</th>
                                <th style="width:70px" class="text-center">Action</th>
                            </tr>
                            </thead>
                            <tbody id="chargesBody">
                            @php
                                $charges = $quotation->charges && $quotation->charges->count() > 0
                                    ? $quotation->charges
                                    : [null];
                            @endphp
                            @foreach($charges as $i => $charge)
                                <tr class="charge-row">
                                    <td class="text-center text-muted small chg-line-no">{{ $i + 1 }}</td>
                                    <td>
                                        <select name="chg_description[]" class="form-select form-select-sm chg-description">
                                            <option value="">— Select —</option>
                                            @foreach($chargeDescriptions as $desc)
                                                <option value="{{ $desc->description }}"
                                                    @selected(($charge->charge_description ?? '') === $desc->description)>
                                                    {{ $desc->description }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="chg_unit[]" class="form-select form-select-sm chg-unit">
                                            <option value="">— Select —</option>
                                            @foreach(\App\Models\Master\Unit::units() as $unit)
                                                <option value="{{ $unit->unit_name }}"
                                                    @selected(($charge->unit ?? '') === $unit->unit_name)>
                                                    {{ $unit->unit_name }}{{ $unit->unit_symbol ? ' ('.$unit->unit_symbol.')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="chg_qty[]"
                                               class="form-control form-control-sm chg-qty"
                                               value="{{ $charge->qty ?? 1 }}" min="1" step="1">
                                    </td>
                                    <td>
                                        <select name="chg_currency[]" class="form-select form-select-sm chg-currency">
                                            @foreach(currencies() as $code => $name)
                                                <option value="{{ $code }}"
                                                    @selected(($charge->currency ?? 'SAR') === $code)>{{ $code }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="chg_ex_rate[]"
                                               class="form-control form-control-sm chg-ex-rate"
                                               value="{{ $charge->ex_rate ?? 1 }}" step="0.000001" min="0">
                                    </td>
                                    <td>
                                        <input type="number" name="chg_amt_qty[]"
                                               class="form-control form-control-sm chg-amt-qty"
                                               value="{{ $charge->amount_per_qty ?? '' }}"
                                               step="0.01" min="0" placeholder="0.00">
                                    </td>
                                    <td>
                                        <input type="number" name="chg_fcy_amount[]"
                                               class="form-control form-control-sm chg-fcy-amount bg-light"
                                               value="{{ $charge->fcy_amount ?? '' }}"
                                               step="0.01" readonly placeholder="0.00">
                                    </td>
                                    <td>
                                        <input type="number" name="chg_local_amount[]"
                                               class="form-control form-control-sm chg-local-amount bg-light"
                                               value="{{ $charge->local_amount ?? '' }}"
                                               step="0.01" readonly placeholder="0.00">
                                    </td>
                                    <td>
                                        <select name="chg_tax_group[]" class="form-select form-select-sm">
                                            <option value="">—</option>
                                            @foreach(vat() as $vat)
                                                <option value="{{ $vat['code'] }}"
                                                        data-subtext="{{ $vat['description'] }}"
                                                    @selected(($charge->tax_group_code ?? '') === $vat['code'])>
                                                    {{ $vat['name'] }} ({{ $vat['percent'] }}%)
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="chg_remarks[]" class="form-control form-control-sm"
                                               value="{{ $charge->remarks ?? '' }}" autocomplete="off">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-primary btn-sm chg-clone-row"
                                                title="Clone"><i class="bi bi-copy"></i></button>
                                        <button type="button" class="btn btn-outline-danger btn-sm chg-remove-row"
                                                title="Delete"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr class="table-light fw-semibold small">
                                <td colspan="7" class="text-end pe-2">Totals:</td>
                                <td><span id="chgGrandFcy">0.00</span></td>
                                <td><span id="chgGrandLocal">0.00</span></td>
                                <td colspan="3"></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>

                    <button type="button" id="addChargeRow" class="btn btn-sm btn-primary mt-2">
                        <i class="bi bi-plus-circle me-1"></i>Add Charge
                    </button>
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
