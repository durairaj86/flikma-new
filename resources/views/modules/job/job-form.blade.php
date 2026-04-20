<div class="g-3 align-items-center <!--bg-white--> border-bottom py-3 px-4 mb-1 small" >
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $job->row_no ?? 'New Job' }}</span>
            </div>

        </div>

        <!-- Save & Next Button -->
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container-fluid px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="Save Job">
    <!-- Meta Info -->

    <div class="row">
        <div class="d-flex justify-content-center">
            <div class="d-inline-block p-1">
                <ul class="nav status-tabs align-items-center border-bottom mb-0 mt-0 justify-content-center"
                    id="modalTabs" role="tablist">
                    <li class="nav-item me-2">
                        <button
                            class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn active"
                            data-bs-toggle="tab" data-bs-target="#tab-general"
                            type="button">
                            <i class="bi bi-info-circle me-1"></i> General
                        </button>
                    </li>
                    <li class="nav-item me-2">
                        <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-clearance"
                                type="button">
                            <i class="bi bi-shield-check me-1"></i> Customs & Clearance
                        </button>
                    </li>
                    <li class="nav-item me-2">
                        <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-containers"
                                type="button">
                            <i class="bi bi-layout-wtf me-1"></i> Containers
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-packages"
                                type="button">
                            <i class="bi bi-box-seam me-1"></i> Packages
                        </button>
                    </li>
                    {{--<li class="nav-item">
                        <button class="nav-link px-3 py-2" data-bs-toggle="tab" data-bs-target="#tab-documents"
                                type="button">
                            <i class="bi bi-truck"></i> Documents
                        </button>
                    </li>--}}
                </ul>
            </div>
        </div>
        <form id="moduleForm" novalidate action="{{ request()->url() }}"
              style="min-height:650px;max-height: 75vh; overflow-y: auto; overflow-x: hidden">
            @csrf
            <input type="hidden" name="data-id" value="{{ $job->id }}">

            <div class="tab-content <!--error-border-off-->">

                <!-- General Info -->
                <div class="tab-pane show active" id="tab-general">
                    <div class="model-form-tab-div">
                        <div class="model-form-sub-title">
                            <h5>General</h5>
                        </div>
                        <div class="row mb-3">
                            {{--<div class="col-md-3">
                                <label class="form-label">Job Number</label>
                                <input type="text" name="job_number" class="form-control" placeholder="Auto/Manual">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Quotation No *</label>
                                <input type="text" name="quotation_id" class="form-control" required>
                            </div>--}}
                            <div class="col-md-4">
                                <label class="form-label required">Customer <sup class="text-danger">*</sup></label>
                                <x-common.customers :value="$job->customer_id" :required="true"></x-common.customers>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required">Job Date <sup class="text-danger">*</sup></label>
                                <input type="date" name="posting_date" class="form-control datepicker" required
                                       value="{{ $job->posted_at ?? \Carbon\Carbon::today()->format('d-m-Y') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Salesperson</label>
                                <x-common.salesperson :value="$job->salesperson_id"></x-common.salesperson>
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label required">Services <sup class="text-danger">*</sup></label>
                                <x-common.service :value="$job->services"></x-common.service>
                            </div>
                            <div class="col-md-4 mt-3">
                                <label for="activity_id" class="form-label required">Activity <sup
                                        class="text-danger">*</sup></label>
                                <x-common.activity :value="$job->activity_id"
                                                   shipmentMode="{{ $job->shipment_mode }}"></x-common.activity>
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label">Cargo Type</label>
                                <select id="cargoType" name="cargo_type" class="tom-select"
                                        data-placeholder="Select Cargo Type">
                                    <option value="">Select</option>
                                    <option value="AOG" @selected('AOG' == $job->cargo_type)>AOG</option>
                                    <option value="ROU" @selected('ROU' == $job->cargo_type)>ROU</option>
                                </select>
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label">Cargo Requirements</label>
                                <select id="cargoRequirements" name="requirements[]" class="tom-select" multiple
                                        data-placeholder="Select Requirements">
                                    @foreach(cargoRequirements() as $cargoRequirement)
                                        <option
                                            value="{{ $cargoRequirement }}"
                                            {{--@selected(in_array($cargoRequirement,$job->cargo_requirements)--}})>{{ $cargoRequirement }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3">{{ $job->remarks }}</textarea>
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label required">Freza Job No <sup class="text-danger">*</sup></label>
                                <input name="row_no" class="form-control" required value="{{ $job->row_no }}">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label">Suppliers</label>
                                <x-common.suppliers :value="$job->supplier_json" multiple="true"></x-common.suppliers>
                            </div>
                        </div>
                    </div>

                    <div class="model-form-tab-div">
                        <div class="model-form-sub-title">
                            <h5>Shipment</h5>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">AWB/MBL No</label>
                                <input type="text" name="awb_number" class="form-control" value="{{ $job->awb_no }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">HBL/HAWB No</label>
                                <input type="text" name="hbl_number" class="form-control" value="{{ $job->hbl_no }}">
                            </div>
                            {{--<div class="col-md-4">
                                <label class="form-label required">Shipment Mode <sup
                                        class="text-danger">*</sup></label>
                                <select class="tom-select" name="shipment_mode" id="shipment_mode"
                                        required>
                                    @foreach(shipmentMode() as $modeId => $mode)
                                        <option
                                            value="{{ $modeId }}" @selected($job->shipment_mode == $modeId)>{{ $mode }}</option>
                                    @endforeach
                                </select>
                            </div>--}}
                            <div class="col-md-4">
                                <label class="form-label">Carrier / Line</label>
                                <input type="text" name="carrier" class="form-control" value="{{ $job->carrier }}">
                                {{--<select id="carrier" name="carrier" class="tom-select-search" data-live-search="true"
                                        data-placeholder="--Select Carrier--" autocomplete="off">
                                    <option value="">--Select--</option>
                                    @if($job->carrier)
                                        <option value="{{ $job->carrier }}" selected>{{ $job->carrier }}</option>
                                    @endif
                                    @foreach($carriers as $carrier)
                                        <option value="{{ $carrier->name }}">{{ $carrier->name }}</option>
                                    @endforeach
                                </select>--}}
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label">Voyage / Flight No.</label>
                                <input type="text" name="voyage_flight_no" class="form-control"
                                       value="{{ $job->voyage_flight_no }}">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label">Incoterm</label>
                                <select class="tom-select" name="incoterm" data-live-search="true">
                                    <option value="">Select</option>
                                    @foreach(incoterms() as $incoterm)
                                        <option value="{{ $incoterm->code }}"
                                                data-subtext="{{ $incoterm->description }}" @selected($job->incoterm == $incoterm->code)>{{ $incoterm->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label">Volume</label>
                                <input type="text" name="volume" class="form-control" value="{{ $job->volume }}">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label">Weight</label>
                                <input type="text" name="weight" class="form-control" value="{{ $job->weight }}">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label">Commodity</label>
                                <input type="text" name="commodity" class="form-control"
                                       value="{{ $job->commodity }}">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label">No. of pieces</label>
                                <input type="text" name="no_of_pieces" class="form-control" maxlength="8"
                                       value="{{ $job->no_of_pieces }}">
                            </div>
                        </div>
                    </div>

                    <div class="model-form-tab-div">
                        <div class="model-form-sub-title">
                            <h5>Parties</h5>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Shipper</label>
                                <input type="text" name="shipper" class="form-control" value="{{ $job->shipper }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Shipper Address</label>
                                <textarea name="shipper_address"
                                          class="form-control">{{ $job->shipper_address }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Shipping Ref.</label>
                                <input type="text" name="shipping_reference_no" class="form-control"
                                       value="{{ $job->shipping_ref }}">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label">Client Ref.</label>
                                <input type="text" name="client_ref" class="form-control"
                                       value="{{ $job->client_ref }}">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label">Consignee</label>
                                <input type="text" name="consignee" class="form-control" value="{{ $job->consignee }}">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label">Consignee Address</label>
                                <textarea name="consignee_address"
                                          class="form-control">{{ $job->consignee_address }}</textarea>
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label">Pickup Date</label>
                                <input type="text" name="pickup_date" class="form-control datepicker"
                                       value="{{ $job->pickup_date }}">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label">Pickup Address</label>
                                <textarea name="pickup_address"
                                          class="form-control">{{ $job->pickup_address }}</textarea>
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label">Delivery Date</label>
                                <input type="text" name="delivery_date" class="form-control datepicker"
                                       value="{{ $job->delivery_date }}">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label">Delivery Address</label>
                                <textarea name="delivery_address"
                                          class="form-control">{{ $job->delivery_address }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="model-form-tab-div">
                        <div class="model-form-sub-title">
                            <h5>Routing</h5>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Place of Receipt</label>
                                <input type="text" name="place_of_receipt" class="form-control"
                                       value="{{ $job->place_of_receipt }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">POL <small>(Port of Loading)</small></label>
                                <select id="pol" name="pol" class="tom-select-search" autocomplete="off"
                                        data-placeholder="--Select Port of Loading--">
                                    <option value="">--Select--</option>
                                    @if($job->pol)
                                        @php
                                            $polSplit = explode('-',$job->pol);
                                        @endphp
                                        <option value="{{ $job->pol }}" selected
                                                data-code="{{ $job->polCode ?? 'N/A' }}"
                                        >{{ $job->polName }}</option>
                                    @endif

                                    @foreach($polPod as $pol)
                                        <option value="{{ $pol->code.'-'.$pol->name }}"
                                                data-code="{{ $pol->code }}" data-id="{{ $pol->id }}"
                                        >{{ $pol->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">POD <small>(Port of Discharge)</small></label>
                                <select id="pod" name="pod" class="tom-select-search" autocomplete="off"
                                        data-placeholder="--Select Port of Discharge--">
                                    <option value="" @selected(!$job->pod)>--Select
                                        Port of Discharge--
                                    </option>
                                    @if($job->pod)
                                        @php
                                            $podSplit = explode('-',$job->pod);
                                        @endphp
                                        <option value="{{ $job->pod }}" selected
                                                data-code="{{ $job->podCode ?? 'N/A' }}"
                                        >{{ $job->podName }}</option>
                                    @endif
                                    @foreach($polPod as $pod)
                                        <option value="{{ $pod->code.'-'.$pod->name }}"
                                                data-code="{{ $pod->code }}"
                                                data-id="{{ $pod->id }}">{{ $pod->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label">Place of Delivery</label>
                                <input type="text" name="place_of_delivery" class="form-control"
                                       value="{{ $job->place_of_delivery }}">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label">Final Destination</label>
                                <input type="text" name="final_destination" class="form-control"
                                       value="{{ $job->final_destination }}">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label">Transshipment Port</label>
                                <input type="text" name="transshipment_port" class="form-control"
                                       value="{{ $job->transshipment_port }}">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label">ETD <small>(Estimated Time of Departure)</small></label>
                                <input type="date" name="etd" data-default-date="false" class="form-control datepicker"
                                       value="{{ $job->etd }}">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label">ETA <small>(Estimated Time of Arrival)</small></label>
                                <input type="date" name="eta" data-default-date="false" value="{{ $job->eta }}"
                                       class="form-control datepicker">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label">ATD <small>(Actual Time of Departure)</small></label>
                                <input type="date" name="atd" data-default-date="false" class="form-control datepicker"
                                       value="{{ $job->atd }}">
                            </div>
                            <div class="col-md-4 mt-3">
                                <label class="form-label">ATA <small>(Actual Time of Arrival)</small></label>
                                <input type="date" name="ata" data-default-date="false" value="{{ $job->ata }}"
                                       class="form-control datepicker">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Customs & Clearance -->
                <div class="tab-pane" id="tab-clearance">
                    <div class="model-form-tab-div">

                        <!-- ========================= -->
                        <!--   SECTION TITLE 1         -->
                        <!-- ========================= -->
                        <div class="model-form-sub-title">
                            <h5>Customs & Documentation</h5>
                        </div>

                        <div class="row mb-3">

                            <div class="col-md-3">
                                <label class="form-label">HS Code</label>
                                <input type="text" name="hs_code" class="form-control"
                                       value="{{ $job->clearance->hs_code }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Customs Declaration No</label>
                                <input type="text" name="declaration_no" class="form-control"
                                       value="{{ $job->clearance->declaration_no }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Customs Broker</label>
                                <input type="text" name="customs_broker" class="form-control"
                                       value="{{ $job->clearance->customs_broker }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Port of Clearance</label>
                                <input type="text" name="port_clearance" class="form-control"
                                       value="{{ $job->clearance->port_clearance }}">
                            </div>

                            <div class="col-md-3 mt-3">
                                <label class="form-label">Type of Clearance</label>
                                <select name="type_of_clearance" class="tom-select">
                                    <option value="">--Select--</option>
                                    {{--@foreach(clearanceTypes() as $id => $name)
                                        <option value="{{ $id }}" @selected($job->type_of_clearance == $id)>
                                            {{ $name }}
                                        </option>
                                    @endforeach--}}
                                </select>
                            </div>

                            <div class="col-md-3 mt-3">
                                <label class="form-label">Docs Copy Received Date</label>
                                <input type="date" name="doc_received" class="form-control datepicker"
                                       value="{{ $job->clearance->doc_received }}">
                            </div>

                            <div class="col-md-3 mt-3">
                                <label class="form-label">BL Receive Date</label>
                                <input type="date" name="bl_receive_date" class="form-control datepicker"
                                       value="{{ $job->clearance->bl_receive_date }}">
                            </div>

                            <div class="col-md-3 mt-3">
                                <label class="form-label">Original Docs Received Date</label>
                                <input type="date" name="original_doc_received" class="form-control datepicker"
                                       value="{{ $job->clearance->original_doc_received }}">
                            </div>

                            <div class="col-md-3 mt-3">
                                <label class="form-label">Saber Certificate Date</label>
                                <input type="date" name="saber_certificate_date" class="form-control datepicker"
                                       value="{{ $job->clearance->saber_certificate_date }}">
                            </div>

                            <div class="col-md-3 mt-3">
                                <label class="form-label">Bayan Date</label>
                                <input type="date" name="bayan_date" class="form-control datepicker"
                                       value="{{ $job->clearance->bayan_date }}">
                            </div>

                            <div class="col-md-3 mt-3">
                                <label class="form-label">Bayan No</label>
                                <input type="text" name="bayan_no" class="form-control"
                                       value="{{ $job->clearance->bayan_no }}">
                            </div>

                        </div>


                        <!-- ========================= -->
                        <!--   SECTION TITLE 2         -->
                        <!-- ========================= -->
                        <div class="model-form-sub-title mt-4">
                            <h5>Delivery Order & Duty Details</h5>
                        </div>

                        <div class="row mb-3">

                            <div class="col-md-3">
                                <label class="form-label">D.O Date</label>
                                <input type="date" name="do_date" class="form-control datepicker"
                                       value="{{ $job->clearance->do_date }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">D.O No</label>
                                <input type="text" name="do_no" class="form-control"
                                       value="{{ $job->clearance->do_no }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Duty Amount - CEL</label>
                                <input type="number" step="0.01" name="duty_amount" class="form-control"
                                       value="{{ $job->clearance->duty_amount }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Duty Amount - Client</label>
                                <input type="number" step="0.01" name="duty_amount_client" class="form-control"
                                       value="{{ $job->clearance->duty_amount_client }}">
                            </div>

                            <div class="col-md-3 mt-3">
                                <label class="form-label">Demurrage Date</label>
                                <input type="date" name="demurrage_date" class="form-control datepicker"
                                       value="{{ $job->clearance->demurrage_date }}">
                            </div>

                            <div class="col-md-6 mt-3">
                                <label class="form-label">D.O Remarks</label>
                                <textarea name="do_remarks" class="form-control"
                                          rows="2">{{ $job->clearance->do_remarks }}</textarea>
                            </div>

                        </div>


                        <!-- ========================= -->
                        <!--   SECTION TITLE 3         -->
                        <!-- ========================= -->
                        <div class="model-form-sub-title mt-4">
                            <h5>Inspections, Tests & Final Clearance</h5>
                        </div>

                        <div class="row mb-3">

                            <div class="col-md-3">
                                <label class="form-label">Lab Test Required?</label>
                                <select name="lab_clearance" class="tom-select">
                                    <option value="">--Select--</option>
                                    <option value="0" @selected(!$job->clearance->lab_clearance)>No</option>
                                    <option value="1" @selected($job->clearance->lab_clearance)>Yes</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Inspection Required?</label>
                                <select name="inspection" class="tom-select">
                                    <option value="">--Select--</option>
                                    <option value="0" @selected($job->clearance->inspection==0)>No</option>
                                    <option value="1" @selected($job->clearance->inspection==1)>Yes</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Clearance Status</label>
                                <select name="clearance_status" class="tom-select">
                                    <option value="">--Select--</option>
                                    @foreach(clearanceStatus() as $id => $name)
                                        <option value="{{ $id }}" @selected($job->clearance->clearance_status == $id)>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Clearance Date</label>
                                <input type="date" name="clearance_date" class="form-control datepicker"
                                       value="{{ $job->clearance->clearance_date }}">
                            </div>

                            <div class="col-md-6 mt-3">
                                <label class="form-label">Remarks</label>
                                <textarea name="clearance_remarks" class="form-control"
                                          rows="2">{{ $job->clearance->clearance_remarks }}</textarea>
                            </div>

                        </div>

                    </div>
                </div>

                <!-- Containers / Cargo -->
                <div class="tab-pane mt-4" id="tab-containers">
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
                            @foreach($containers as $container)
                                <tr>
                                    <td>
                                        <x-common.container_size :value="$container->container_size"/>
                                    </td>

                                    <td>
                                        <x-common.container_types :value="$container->container_type"/>
                                    </td>

                                    <td>
                                        <input type="text" name="container_no[]" class="form-control"
                                               placeholder="ABC1234567" value="{{ $container->container_number }}">
                                    </td>

                                    <td>
                                        <input type="text" name="seal_no[]" class="form-control"
                                               placeholder="SEAL001" value="{{ $container->seal_number }}">
                                    </td>

                                    <td>
                                        <input type="text" name="gross[]" class="form-control float" maxlength="10"
                                               step="0.01" value="{{ $container->gross_weight }}">
                                    </td>

                                    <td>
                                        <input type="text" name="net[]" class="form-control float"
                                               step="0.01" value="{{ $container->net_weight }}">
                                    </td>

                                    <td>
                                        <input type="text" name="vol[]" class="form-control float"
                                               step="0.01" value="{{ $container->volume }}">
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

                                    <!-- NEW: Quantity + UOM -->
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
                                               placeholder="Notes" value="{{ $container->remarks }}">
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
                <div class="tab-pane mt-4" id="tab-packages" role="tabpanel">
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
                        @foreach($packages as $package)
                            <tr>
                                {{--<td>
                                    <select name="commodity_type[]" class="tom-select" data-max-width="150">
                                        <option value="">Select</option>
                                        @foreach(commodityType() as $id => $name)
                                            <option value="{{ $id }}"
                                                    {{ $package && $package->commodity_type == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>--}}

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

                                {{--<td>
                                    <input type="text" name="package_hs_code[]" class="form-control"
                                           value="{{ $package->hs_code ?? '' }}">
                                </td>--}}

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


                <!-- Documents -->
                <div class="tab-pane" id="tab-documents">
                    <div class="row g-3">

                        <!-- Bill of Lading -->
                        <div class="col-md-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-body text-center">
                                    <h6 class="fw-semibold mb-2">Bill of Lading</h6>
                                    <input type="file" name="bl_copy" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- Airway Bill -->
                        <div class="col-md-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-body text-center">
                                    <h6 class="fw-semibold mb-2">Airway Bill</h6>
                                    <input type="file" name="awb_copy" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- Invoice -->
                        <div class="col-md-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-body text-center">
                                    <h6 class="fw-semibold mb-2">Invoice</h6>
                                    <input type="file" name="invoice_copy" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- Packing List -->
                        <div class="col-md-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-body text-center">
                                    <h6 class="fw-semibold mb-2">Packing List</h6>
                                    <input type="file" name="packing_list" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- Other Docs -->
                        <div class="col-md-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-body text-center">
                                    <h6 class="fw-semibold mb-2">Other Documents</h6>
                                    <input type="file" name="other_docs[]" multiple class="form-control">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>


                <!-- Tracking -->
                {{--<div class="tab-pane" id="tracking">
                    <div class="row mb-3">
                        <div class="col-md-3"><label class="form-label">Current Status</label>
                            <select name="status" class="form-select">
                                <option>Open</option>
                                <option>Booked</option>
                                <option>In Transit</option>
                                <option>Customs Clearance</option>
                                <option>Delivered</option>
                                <option>Closed</option>
                            </select>
                        </div>
                        <div class="col-md-3"><label class="form-label">Status Date</label><input type="date" name="status_date"
                                                                                                  class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Remarks</label><textarea name="status_remarks"
                                                                                                 class="form-control"></textarea>
                        </div>
                    </div>
                </div>--}}

            </div>


        </form>
    </div>
</div>
