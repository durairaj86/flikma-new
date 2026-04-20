<div class="modal-header justify-content-between border-bottom py-3" data-close-title="enquiry">
    <div class="row align-items-center bg-white  small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $enquiry->row_no ?? 'New Enquiry' }}</span>
            </div>

            <!-- Save & Next Button -->

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
    <div id="show-buttons"></div>
</div>
<div class="modal-body p-0">
    <div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
         data-button-save="Save Enquiry">
        <!-- Meta Info -->


        <!-- Main Card -->
        <div class="row">
            {{--<div class="d-flex justify-content-center">
                <div class="d-inline-block p-1">
                    <ul class="nav status-tabs align-items-center border-bottom mb-0 mt-0 justify-content-center"
                        id="modalTabs" role="tablist">
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn active"
                                data-bs-toggle="tab" data-bs-target="#tab-basic"
                                type="button">
                                <i class="bi bi-info-circle me-1"></i> General
                            </button>
                        </li>
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-container"
                                type="button">
                                <i class="bi bi-sliders me-1"></i> Container/Package
                            </button>
                        </li>
                        <li class="nav-item">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-note"
                                type="button">
                                <i class="bi bi-journal-text me-1"></i> Notes
                            </button>
                        </li>
                    </ul>
                </div>
            </div>--}}
            <form id="moduleForm" novalidate action="{{ request()->url() }}">
                @csrf
                <input type="hidden" name="data-id" id="data-id" value="{{ $enquiry->id }}">

                <!-- Customer Info -->
                <div class="tab-content">
                    <!-- Tab 1: Basic Info -->
                    <div class="tab-pane show active" id="tab-basic">
                        <div class="model-form-tab-div">
                            <div class="model-form-sub-title">
                                <h5>General</h5>
                            </div>
                            <div class="mb-3 row g-3">
                                {{--<div class="col-md-4">
                                    <label
                                        class="form-label d-flex justify-content-between align-items-center">Type</label>
                                    <div>
                                        <select class="form-control tom-select" name="customer_type"
                                                id="customer_type">
                                            <option
                                                value="customer" @selected($enquiry->customer_type == 'customer' || !$enquiry->customer_type)>
                                                Existing Customer
                                            </option>
                                            <option value="prospect" @selected($enquiry->customer_type == 'prospect')>
                                                Prospect Customer
                                            </option>
                                        </select>
                                    </div>


                                </div>--}}
                                {{--<div class="col-md-3">
                                    <label for="customer" class="form-label">Select Customer</label>
                                    <x-common.customers :value="$enquiry->customer_id"></x-common.customers>
                                </div>--}}
                                <div class="col-md-4" id="customer-select">
                                    <label class="form-label d-flex justify-content-between align-items-center">
                                        Select Customer
                                    </label>

                                    <x-common.customers :value="$enquiry->customer_id" :required="true"></x-common.customers>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label d-flex justify-content-between align-items-center">
                                        Select Prospect Customer
                                    </label>
                                    <select name="prospect" id="prospect"
                                            class="tom-select" {{ $enquiry->prospect_id ? 'data-has-prospect=true' : '' }}>
                                        <option value="">--Select--</option>
                                        @foreach(\App\Models\Prospect\Prospect::prospectCustomers() as $prospect)
                                            <option value="{{ encodeId($prospect->id) }}"
                                                    data-subtext="{{ $prospect->row_no }}"
                                                @selected($prospect->id==$enquiry->prospect_id)>
                                                {{ $prospect->name_en }}
                                            </option>
                                        @endforeach
                                        <option data-divider="true"></option>
                                        <option value="__new__" data-type="new" data-module="PROSPECT">+ Add New
                                            Prospect
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-5 row g-3">
                                <div class="col-md-4">
                                    <label for="salesperson" class="form-label">Select Salesperson</label>
                                    <x-common.salesperson :value="$enquiry->salesperson_id"></x-common.salesperson>
                                </div>

                                <div class="col-md-4">
                                    <label for="expiry_date" class="form-label">Expiry Date</label>
                                    <input type="date" name="expiry_date" id="expiry_date"
                                           class="form-control rounded-3 datepicker"
                                           value="{{ $enquiry->expiry_date }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="created_at" class="form-label">Creation Date</label>
                                    <input type="text" readonly disabled
                                           class="form-control rounded-3" id="created_at"
                                           value="{{ $enquiry->created_at ? $enquiry->created_at : \Carbon\Carbon::today()->format('d-m-Y') }}">
                                </div>
                            </div>
                            <div class="model-form-sub-title">
                                <h5>Shipment Details</h5>
                            </div>
                            <div class="mb-5 row g-3">
                                {{--<div class="col-md-4">
                                    <label for="shipment_type" class="form-label">Mode</label>
                                    <select class="form-control tom-select" name="shipment_mode" id="shipment_mode"
                                            required>
                                        @foreach(shipmentMode() as $modeId => $mode)
                                            <option
                                                value="{{ $modeId }}" @selected($enquiry->shipment_mode == $modeId)>{{ $mode }}</option>
                                        @endforeach
                                    </select>
                                </div>--}}
                                <div class="col-md-4">
                                    <label for="shipment_type" class="form-label">Place of Receipt</label>
                                    <input type="text" class="form-control" name="place_of_receipt"
                                           id="place-of-receipt"
                                           value="{{ $enquiry->place_of_receipt }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="activity_id" class="form-label">Activity</label>
                                    <x-common.activity :value="$enquiry->activity_id"
                                                       :shipmentMode="$enquiry->shipment_mode"></x-common.activity>
                                </div>
                                <div class="col-md-4">
                                    <label for="shipment_category" class="form-label">Category</label>
                                    <select id="shipment_category" name="shipment_category"
                                            class="form-control rounded-3 tom-select"
                                            required>
                                        @if($enquiry->shipment_category != 'package')
                                            <option
                                                value="container" @selected($enquiry->shipment_category == 'container')>
                                                Container
                                            </option>
                                        @endif
                                        @if($enquiry->shipment_category != 'container')
                                            <option value="package" @selected($enquiry->shipment_category == 'package')>
                                                Package
                                            </option>
                                        @endif
                                    </select>
                                </div>
                                {{--<div class="col-md-4">
                                    <label for="weight" class="form-label">Weight (kg)</label>
                                    <input type="number" name="weight" id="weight" step="0.01"
                                           class="form-control rounded-3"
                                           value="{{ $enquiry->weight }}">
                                </div>--}}
                                <div class="col-md-4">
                                    <label for="weight" class="form-label">Shipper</label>
                                    <input type="text" name="shipper" id="shipper"
                                           class="form-control rounded-3"
                                           value="{{ $enquiry->shipper }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="volume" class="form-label">Volume (m³)</label>
                                    <input type="number" name="volume" id="volume" step="0.01"
                                           class="form-control rounded-3"
                                           value="{{ $enquiry->volume }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="volume" class="form-label">Incoterm</label>
                                    <select class="form-control tom-select" name="incoterm" id="incoterm"
                                            data-live-search="true">
                                        <option value="">Select</option>
                                        @foreach(incoterms() as $incoterm)
                                            <option value="{{ $incoterm->code }}"
                                                    data-subtext="{{ $incoterm->description }}" @selected($enquiry->incoterm == $incoterm->code)>{{ $incoterm->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="model-form-sub-title">
                                <h5>Origin & Destination</h5>
                            </div>
                            <div class="mb-5 row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Port of Loading (POL)</label>
                                    <select id="pol" name="pol" class="tom-select-search" autocomplete="off" required
                                            data-placeholder="--Select Port of Loading--">
                                        <option value="">--Select--</option>
                                        @if($enquiry->pol)
                                            <option value="{{ $enquiry->pol }}" selected>{{ $enquiry->pol }}</option>
                                        @endif
                                        @foreach($polPod as $pol)
                                            <option value="{{ $pol->id }}">{{ $pol->name }}</option>
                                        @endforeach
                                    </select>
                                    {{--<x-common.country :value="$enquiry->origin_country" inputName="origin_country"></x-common.country>--}}
                                </div>
                                {{--<div class="col-md-3">
                                    <label for="origin_city" class="form-label">Origin City</label>
                                    <input type="text" name="origin_city" id="origin_city"
                                           class="form-control rounded-3"
                                           value="{{ $enquiry->origin_city }}" required>
                                </div>--}}
                                <div class="col-md-4">
                                    <label class="form-label">Port of Discharge (POD)</label>
                                    <select id="pod" name="pod" class="tom-select-search" autocomplete="off" required
                                            data-placeholder="--Select Port of Discharge--">
                                        <option value="" @selected(!$enquiry->pod)>--Select Port of Discharge--</option>
                                        @if($enquiry->pod)
                                            <option value="{{ $enquiry->pod }}" selected>{{ $enquiry->pod }}</option>
                                        @endif
                                        @foreach($polPod as $pod)
                                            <option value="{{ $pod->id }}">{{ $pod->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="pickup_date" class="form-label">Pickup Date</label>
                                    <input type="date" name="pickup_date" id="pickup_date"
                                           class="form-control rounded-3 datepicker"
                                           value="{{ $enquiry->pickup_date }}">
                                </div>
                            </div>
                            <div class="model-form-sub-title">
                                <h5>Additional Notes</h5>
                            </div>
                            <div class="mb-5 row g-3">
                                <div class="col-md-12">
        <textarea name="remark" id="remark" rows="3" class="form-control rounded-3 h-100"
                  placeholder="Any additional information...">{{ $enquiry->remark }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{--<div class="tab-pane show" id="tab-container">
                        <div class="model-form-tab-div">
                            <div class="model-form-sub-title d-flex justify-content-between border-0">
                                <h5>Containers / Packages</h5>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addItem">
                                    <i class="bi bi-plus me-1"></i> Add Item
                                </button>
                            </div>
                            <div class="mb-5">
                                <div id="itemsContainer">
                                    <table class="table align-middle">
                                        <thead class="table-light">
                                        @if(!$enquiry->id)
                                            <tr class="container-fields">
                                                <th>Container Size</th>
                                                <th>Container Type</th>
                                                <th>Quantity</th>
                                                <th>Hazardous</th>
                                                <th width="5%">Action</th>
                                            </tr>
                                            <tr class="package-fields d-none">
                                                <th>Package Type</th>
                                                <th>Length</th>
                                                <th>Width</th>
                                                <th>Height</th>
                                                <th>Weight</th>
                                                <th width="5%">Action</th>
                                            </tr>
                                        @endif
                                        @if($enquiry->shipment_category === 'container')
                                            <tr>
                                                <th>Container Size</th>
                                                <th>Container Type</th>
                                                <th>Quantity</th>
                                                <th>Hazardous</th>
                                                <th width="5%">Action</th>
                                            </tr>
                                        @elseif($enquiry->shipment_category === 'package')
                                            <tr>
                                                <th>Package Type</th>
                                                <th>Length</th>
                                                <th>Width</th>
                                                <th>Height</th>
                                                <th>Weight</th>
                                                <th width="5%">Action</th>
                                            </tr>
                                        @endif
                                        </thead>
                                        <tbody id="enquiry-row">
                                        --}}{{-- For new Enquiry (no existing rows) --}}{{--
                                        @if(!$enquiry->id)
                                            --}}{{-- Container Fields --}}{{--
                                            <tr class="item-row container-fields">
                                                <td>
                                                    <x-common.container_size name="container[size][]"/>
                                                </td>
                                                <td>
                                                    <x-common.container_types name="container[type][]"/>
                                                </td>
                                                <td><input type="number" name="container[quantity][]"
                                                           class="form-control rounded-3"></td>
                                                <td>
                                                    <select name="container[hazardous][]"
                                                            class="form-control tom-select" data-max-width="200">
                                                        <option value="0">No</option>
                                                        <option value="1">Yes</option>
                                                    </select>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-danger remove-row">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            --}}{{-- Package Fields --}}{{--
                                            <tr class="item-row package-fields d-none">
                                                <td>
                                                    <select class="form-control tom-select" name="package[type][]">
                                                        <option value="">Package Type</option>
                                                        @foreach(packageType() as $type => $name)
                                                            <option value="{{ $type }}">{{ $name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td><input type="number" name="package[length][]"
                                                           class="form-control rounded-3"
                                                           placeholder="Length"></td>
                                                <td><input type="number" name="package[width][]"
                                                           class="form-control rounded-3"
                                                           placeholder="Width"></td>
                                                <td><input type="number" name="package[height][]"
                                                           class="form-control rounded-3"
                                                           placeholder="Height"></td>
                                                <td><input type="number" name="package[weight][]"
                                                           class="form-control rounded-3"
                                                           placeholder="Weight"></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-danger remove-row">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endif

                                        --}}{{-- Existing Enquiry Rows --}}{{--
                                        @foreach($enquiry->enquirySubs as $item)
                                            @if($enquiry->shipment_category === 'container')
                                                <tr class="item-row container-fields">
                                                    <td>
                                                        <x-common.container_size :value="$item->container_size"
                                                                                 name="container[size][]"/>
                                                    </td>
                                                    <td>
                                                        <x-common.container_types :value="$item->container_type"
                                                                                  name="container[type][]"/>
                                                    </td>
                                                    <td><input type="number" name="container[quantity][]"
                                                               class="form-control rounded-3"
                                                               value="{{ $item->container_quantity }}"></td>
                                                    <td>
                                                        <select name="container[hazardous][]"
                                                                class="form-control tom-select">
                                                            <option
                                                                value="0" @selected($item->container_hazardous == 0)>
                                                                No
                                                            </option>
                                                            <option
                                                                value="1" @selected($item->container_hazardous == 1)>
                                                                Yes
                                                            </option>
                                                        </select>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-danger remove-row">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @elseif($enquiry->shipment_category === 'package')
                                                <tr class="item-row package-fields">
                                                    <td>
                                                        <select class="form-control tom-select"
                                                                name="package[type][]">
                                                            <option value="">Package Type</option>
                                                            @foreach(packageType() as $type => $name)
                                                                <option
                                                                    value="{{ $type }}" @selected($item->package_type == $type)>{{ $name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td><input type="number" name="package[length][]"
                                                               class="form-control rounded-3"
                                                               value="{{ $item->length }}"></td>
                                                    <td><input type="number" name="package[width][]"
                                                               class="form-control rounded-3"
                                                               value="{{ $item->width }}"></td>
                                                    <td><input type="number" name="package[height][]"
                                                               class="form-control rounded-3"
                                                               value="{{ $item->height }}"></td>
                                                    <td><input type="number" name="package[weight][]"
                                                               class="form-control rounded-3"
                                                               value="{{ $item->weight }}"></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-danger remove-row">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>--}}
                    {{--<div class="tab-pane show" id="tab-note">
                        <div class="model-form-sub-title">
                            <h5>Additional Notes</h5>
                        </div>
                        <div class="mb-5 row g-3">
                            <div class="col-md-12">
        <textarea name="remark" id="remark" rows="3" class="form-control rounded-3 h-100"
                  placeholder="Any additional information...">{{ $enquiry->remark }}</textarea>
                            </div>
                        </div>
                    </div>--}}
                </div>

            </form>
        </div>
    </div>
</div>
<script type="text/javascript">


</script>
<style>
    /* Slide from Left */
    .slide-in-left {
        animation: slideInLeft .35s ease forwards;
    }

    @keyframes slideInLeft {
        0% {
            opacity: 0;
            transform: translateX(-30px);
        }
        100% {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Slide from Right */
    .slide-in-right {
        animation: slideInRight .35s ease forwards;
    }

    @keyframes slideInRight {
        0% {
            opacity: 0;
            transform: translateX(30px);
        }
        100% {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Hide instantly (no animation) */
    .hidden-block {
        display: none !important;
    }

</style>
