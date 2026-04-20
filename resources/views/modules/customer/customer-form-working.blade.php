<div>
    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" value="{{ $customer->id }}">

        <!-- Centered Nav Tabs -->
        <div class="d-flex justify-content-center">
            <div class="d-inline-block shadow-sm rounded p-1 bg-white">
                <ul class="nav nav-tabs border-0 justify-content-center" id="modalTabs" role="tablist">
                    <li class="nav-item me-2">
                        <button class="nav-link active px-3 py-2" data-bs-toggle="tab" data-bs-target="#tab-basic"
                                type="button">
                            <i class="bi bi-person-lines-fill"></i> Basic Info
                        </button>
                    </li>
                    <li class="nav-item me-2">
                        <button class="nav-link px-3 py-2" data-bs-toggle="tab" data-bs-target="#tab-address"
                                type="button">
                            <i class="bi bi-geo-alt-fill"></i> Address
                        </button>
                    </li>
                    <li class="nav-item me-2">
                        <button class="nav-link px-3 py-2" data-bs-toggle="tab" data-bs-target="#tab-contact"
                                type="button">
                            <i class="bi bi-telephone-fill"></i> Contact
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link px-3 py-2" data-bs-toggle="tab" data-bs-target="#tab-logistics"
                                type="button">
                            <i class="bi bi-truck"></i> Logistics
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link px-3 py-2" data-bs-toggle="tab" data-bs-target="#tab-salesman"
                                type="button">
                            <i class="bi bi-person-badge"></i> Salesman
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content mt-4">
            <!-- Tab 1: Basic Info -->
            <div class="tab-pane show active" id="tab-basic">
                <div class="model-form-tab-div">
                    <div class="model-form-sub-title">
                        <h5>General</h5>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="form-label">Customer Name (English)</label>
                            <input type="text" name="name_en" class="form-control" required
                                   value="{{ $customer->name_en }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Customer Name (Arabic)</label>
                            <input type="text" name="name_ar" class="form-control" dir="rtl" required
                                   value="{{ $customer->name_ar }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Default Currency</label>
                            <x-common.currencies :value="$customer->currency"></x-common.currencies>
                        </div>
                    </div>
                    <div class="model-form-sub-title">
                        <h5>Business Settings</h5>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="form-label d-block mb-2">Business Type</label>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="business_type" id="registered"
                                       value="registered"
                                       required {{ $customer->business_type == 'registered' ? 'checked' : '' }}>
                                <label class="form-check-label" for="registered">Registered</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="business_type" id="unregistered"
                                       value="unregistered"
                                    {{ ($customer->business_type != 'registered' || !$customer->business_type == 'unregistered') ? 'checked' : '' }}>
                                <label class="form-check-label" for="unregistered">Unregistered</label>
                            </div>
                        </div>


                        <div class="col-md-4 form-group">
                            <label class="form-label">CR Number</label>
                            <input type="text" name="cr_number" class="form-control" value="{{ $customer->cr_number }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">VAT Number</label>
                            <input type="text" name="vat_number" class="form-control"
                                   value="{{ $customer->vat_number }}">
                        </div>
                    </div>
                    <div class="model-form-sub-title">
                        <h5>Credit Settings</h5>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="form-label">Credit Limit</label>
                            <input type="number" name="credit_limit" class="form-control" min="0"
                                   value="{{ $customer->credit_limit }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Credit Days</label>
                            <input type="number" name="credit_days" class="form-control" min="0"
                                   value="{{ $customer->credit_days }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Address -->
            <div class="tab-pane" id="tab-address">
                <div class="model-form-tab-div">
                    <div class="model-form-sub-title">
                        <h5>Address</h5>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group mb-5">
                            <label class="form-label">Address (English)</label>
                            <textarea name="address1_en"
                                      class="form-control h-100">{{ $customer->address1_en }}</textarea>
                        </div>
                        <div class="col-md-4 form-group mb-5">
                            <label class="form-label">Address (Arabic)</label>
                            <textarea name="address1_ar" class="form-control h-100"
                                      dir="rtl">{{ $customer->address1_ar }}</textarea>
                        </div>
                        {{--<div class="col-md-4 form-group">
                            <label class="form-label">Address Line 2 (English)</label>
                            <input type="text" name="address2_en" class="form-control"
                                   value="{{ $customer->address2_en }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Address Line 2 (Arabic)</label>
                            <input type="text" name="address2_ar" class="form-control" dir="rtl"
                                   value="{{ $customer->address2_ar }}">
                        </div>--}}
                        <div class="col-md-4 form-group">
                            <label class="form-label">City (English)</label>
                            <input type="text" name="city_en" class="form-control" value="{{ $customer->city_en }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">City (Arabic)</label>
                            <input type="text" name="city_ar" class="form-control" dir="rtl"
                                   value="{{ $customer->city_ar }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Building Number</label>
                            <input type="text" name="building_number" class="form-control"
                                   value="{{ $customer->building_number }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Plot No</label>
                            <input type="text" name="plot_no" class="form-control" value="{{ $customer->plot_no }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Postal Code</label>
                            <input type="text" name="postal_code" class="form-control"
                                   value="{{ $customer->postal_code }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Country</label>
                            <x-common.country :value="$customer->country"></x-common.country>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Tab 3: Contact -->
            <div class="tab-pane" id="tab-contact">
                <div class="model-form-tab-div">
                    <div class="model-form-sub-title">
                        <h5>Contact</h5>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required
                                   value="{{ $customer->email }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" required pattern="[0-9]{10}"
                                   value="{{ $customer->phone }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Alternate Phone</label>
                            <input type="text" name="alt_phone" class="form-control" value="{{ $customer->alt_phone }}">
                        </div>
                    </div>
                </div>
            </div>


            <!-- Tab 4: Logistics -->
            <div class="tab-pane" id="tab-logistics">
                <div class="model-form-tab-div">
                    <div class="model-form-sub-title">
                        <h5>Logistics</h5>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="form-label">Preferred Shipping Method</label>
                            <select name="preferred_shipping" class="form-control selectpicker">
                                <option value="">Select</option>
                                @foreach(shipmentMode() as $mode => $name)
                                    <option
                                        value="{{ $mode }}" @selected($customer->preferred_shipping == $mode)>{{$name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Default Port</label>
                            <input type="text" name="default_port" class="form-control"
                                   value="{{ $customer->default_port }}">
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="form-label">Payment Terms</label>
                            <textarea name="payment_terms" rows="2"
                                      class="form-control">{{ $customer->payment_terms }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 5: Salesman -->
            <div class="tab-pane" id="tab-salesman">
                <div class="model-form-tab-div">
                    <div class="model-form-sub-title">
                        <h5>Salesman</h5>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="form-label">Salesman Name</label>
                            <x-common.salesperson :value="$customer->salesperson_id"></x-common.salesperson>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Assigned Since</label>
                            <input type="text" class="form-control"
                                   value="{{ $customer->salesman_assigned_since ?? \Carbon\Carbon::today()->format('d-m-Y') }}"
                                   disabled>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Primary</label>
                            <input type="text" class="form-control" value="Yes" disabled>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
<script type="text/javascript">
    $("input[name='business_type']").on("change", function () {
        var type = $("input[name='business_type']:checked").val();
        if (type === "registered") {
            $("input[name='cr_number'], input[name='vat_number']").attr("required", true);
        } else {
            $("input[name='cr_number'], input[name='vat_number']").removeAttr("required");
            $("input[name='cr_number'], input[name='vat_number']").removeClass("is-invalid").next(".invalid-feedback").remove();
        }
    });

    // Trigger change on load to set correct state if editing
    $("input[name='business_type']:checked").trigger("change");
</script>
