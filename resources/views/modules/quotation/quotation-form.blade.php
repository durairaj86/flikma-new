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
                    <div class="mb-3">
                        <table class="table" id="containerTable">
                            <thead>
                            <tr>
                                <th>Size</th>
                                <th>Type</th>
                                <th>Number</th>
                                <th>Seal No</th>
                                <th>Gross Weight (Kg)</th>
                                <th>Net Weight (Kg)</th>
                                <th>Volume (CBM)</th>
                                <th>Hazardous</th>
                                <th>Quantity & UOM</th>
                                <th>Remarks</th>
                                <th width="5%"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $containers = $quotation->containers && $quotation->containers->count() > 0
                                    ? $quotation->containers
                                    : [new \App\Models\Quotation\QuotationContainer()];
                            @endphp
                            @foreach($containers as $container)
                                <tr>
                                    <td>
                                        <x-common.container_size :value="$container->container_size ?? ''"/>
                                    </td>

                                    <td>
                                        <x-common.container_types :value="$container->container_type ?? null"/>
                                    </td>

                                    <td>
                                        <input type="text" name="container_no[]" class="form-control"
                                               placeholder="ABC1234567" value="{{ $container->container_number ?? '' }}">
                                    </td>

                                    <td>
                                        <input type="text" name="seal_no[]" class="form-control"
                                               placeholder="SEAL001" value="{{ $container->seal_number ?? '' }}">
                                    </td>

                                    <td>
                                        <input type="text" name="gross[]" class="form-control float" maxlength="10"
                                               step="0.01" value="{{ $container->gross_weight ?? '' }}">
                                    </td>

                                    <td>
                                        <input type="text" name="net[]" class="form-control float"
                                               step="0.01" value="{{ $container->net_weight ?? '' }}">
                                    </td>

                                    <td>
                                        <input type="text" name="vol[]" class="form-control float"
                                               step="0.01" value="{{ $container->volume ?? '' }}">
                                    </td>

                                    <td>
                                        <select name="haz[]" class="tom-select">
                                            <option value="0" {{ ($container->hazardous ?? '') == 0 ? 'selected':'' }}>
                                                No
                                            </option>
                                            <option value="1" {{ ($container->hazardous ?? '') == 1 ? 'selected':'' }}>
                                                Yes
                                            </option>
                                        </select>
                                    </td>

                                    <td>
                                        <div class="input-group">
                                            <input type="text" step="0.01" name="container_qty[]"
                                                   class="form-control integer" maxlength="6"
                                                   placeholder="Qty"
                                                   value="{{ $container->qty ?? '' }}">
                                            <select name="container_uom[]" class="tom-select">
                                                <option value="PCS" {{ ($container->uom ?? '')=='PCS'?'selected':'' }}>
                                                    PCS
                                                </option>
                                                <option value="CTN" {{ ($container->uom ?? '')=='CTN'?'selected':'' }}>
                                                    CTN
                                                </option>
                                                <option value="PKG" {{ ($container->uom ?? '')=='PKG'?'selected':'' }}>
                                                    PKG
                                                </option>
                                                <option value="MT" {{ ($container->uom ?? '')=='MT'?'selected':'' }}>
                                                    MT
                                                </option>
                                            </select>
                                        </div>
                                    </td>

                                    <td>
                                        <input type="text" name="container_remark[]" class="form-control"
                                               placeholder="Notes" value="{{ $container->remarks ?? '' }}">
                                    </td>

                                    <td class=" align-content-center">
                                        <div class="d-flex justify-content-between gap-3 action-icons">
                                            <div class="addContainerRow">
                                                <i class="bi bi-plus-circle text-muted"></i>
                                            </div>
                                            <div class="remove-row">
                                                <i class="bi bi-trash text-danger"></i>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                            @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>

                <!-- Package Tab -->
                <div class="tab-pane mt-4" id="package" role="tabpanel">
                    <table class="table align-middle" id="packageTable">
                        <thead>
                        <tr>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Qty</th>
                            <th>L (cm)</th>
                            <th>W (cm)</th>
                            <th>H (cm)</th>
                            <th>Weight (Kg)</th>
                            <th>Volume (m3)</th>
                            <th>Total Weight (Kg)</th>
                            <th>Chargeable Weight (Kg)</th>
                            <th width="5%"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @php
                            $packages = $quotation->packages && $quotation->packages->count() > 0
                                ? $quotation->packages
                                : [new \App\Models\Quotation\QuotationPackage()];
                        @endphp
                        @foreach($packages as $package)
                            <tr>
                                <td>
                                    <select name="package_type[]" class="tom-select" data-max-width="100">
                                        <option value="">Select</option>
                                        @foreach(packageType() as $id => $name)
                                            <option value="{{ $id }}"
                                                {{ $package && $package->package_type == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td>
                                    <input type="text" name="description_goods[]" class="form-control"
                                           value="{{ $package->description_goods ?? '' }}">
                                </td>

                                <td>
                                    <input type="text" name="quantity[]" class="form-control integer quantity"
                                           data-decimal="3"
                                           value="{{ amountFormat($package->quantity,3) ?? '' }}">
                                </td>

                                <td>
                                    <input type="text" name="length[]" class="form-control float length"
                                           data-decimal="3"
                                           value="{{ amountFormat($package->length,3) ?? '' }}">
                                </td>

                                <td>
                                    <input type="text" name="width[]" class="form-control float width" data-decimal="3"
                                           value="{{ amountFormat($package->width,3) ?? '' }}">
                                </td>

                                <td>
                                    <input type="text" name="height[]" class="form-control float height"
                                           data-decimal="3"
                                           value="{{ amountFormat($package->height,3) ?? '' }}">
                                </td>

                                <td>
                                    <input type="text" name="package_weight[]" class="form-control float weight"
                                           data-decimal="3"
                                           value="{{ amountFormat($package->package_weight,3) ?? '' }}">
                                </td>

                                <td>
                                    <input type="text" name="package_volume[]" class="form-control float volume"
                                           value="{{ amountFormat($package->volume,3) ?? '' }}">
                                </td>

                                <td>
                                    <input type="text" name="total_weight[]" class="form-control float total_weight"
                                           data-decimal="3"
                                           value="{{ amountFormat($package->total_weight,3) ?? '' }}">
                                </td>

                                <td>
                                    <input type="text" name="chargeable_weight[]"
                                           class="form-control float chargeable_weight" data-decimal="3"
                                           value="{{ amountFormat($package->chargeable_weight,3) ?? '' }}">
                                </td>

                                <td class="align-content-center">
                                    <div class="d-flex justify-content-between gap-3 action-icons">
                                        <div class="addPackageRow">
                                            <i class="bi bi-plus-circle text-muted"></i>
                                        </div>
                                        <div class="remove-row">
                                            <i class="bi bi-trash text-danger"></i>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
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
