<div class="modal-header justify-content-between border-bottom py-3" data-close-title="enquiry">
    <div class="row align-items-center bg-white  small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $supplier->name ?? 'New Supplier' }}</span> <small
                    class="text-secondary">{{ $supplier->row_no ? ' - ' . $supplier->row_no : '' }}</small>
            </div>
        </div>
    </div>
    <div id="show-buttons"></div>
</div>
<div class="modal-body p-0">
    <div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
         data-button-save="Save Supplier">
        <div class="row">
            <div class="d-flex justify-content-center">
                <div class="d-inline-block p-1">
                    <ul class="nav status-tabs align-items-center border-bottom mb-0 mt-0 justify-content-center"
                        id="modalTabs" role="tablist">
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn active"
                                data-bs-toggle="tab" data-bs-target="#tab-basic"
                                type="button">
                                <i class="bi bi-person-lines-fill me-1"></i> Basic Info
                            </button>
                        </li>
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-address"
                                type="button">
                                <i class="bi bi-geo-alt-fill me-1"></i> Address
                            </button>
                        </li>
                        {{--<li class="nav-item me-2">
                            <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn" data-bs-toggle="tab" data-bs-target="#tab-contact"
                                    type="button">
                                <i class="bi bi-telephone-fill me-2"></i> Contact
                            </button>
                        </li>--}}
                    </ul>
                </div>
            </div>
            <form id="moduleForm" novalidate action="{{ request()->url() }}">
                @csrf
                <input type="hidden" name="data-id" value="{{ $supplier->id }}">

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Tab 1: Basic Info -->
                    <div class="tab-pane show active" id="tab-basic">
                        <div class="model-form-tab-div">
                            <div class="model-form-sub-title">
                                <h5>General</h5>
                            </div>
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label class="form-label">Supplier Name (English)</label>
                                    <input type="text" name="name_en" class="form-control" required
                                           value="{{ $supplier->name_en }}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="form-label">Supplier Name (Arabic)</label>
                                    <input type="text" name="name_ar" class="form-control" dir="rtl"
                                           value="{{ $supplier->name_ar }}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="form-label">Default Currency</label>
                                    <x-common.currencies :value="$supplier->currency"></x-common.currencies>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control"
                                           value="{{ $supplier->email }}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="phone" class="form-control"
                                           pattern="^\+?[0-9]{10,15}$" maxlength="16"
                                           value="{{ $supplier->phone }}"
                                           placeholder="+966 1234567890 or 966 1234567890">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="form-label">Alternate Phone</label>
                                    <input type="text" name="alt_phone" class="form-control"
                                           value="{{ $supplier->alt_phone }}">
                                </div>
                            </div>
                            <div class="model-form-sub-title">
                                <h5>Business Settings</h5>
                            </div>
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label class="form-label d-block mb-2">Business Type</label>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="business_type"
                                               id="registered"
                                               value="registered"
                                               required {{ $supplier->business_type == 'registered' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="registered">Registered</label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="business_type"
                                               id="unregistered"
                                               value="unregistered"
                                            {{ ($supplier->business_type != 'registered' || !$supplier->business_type == 'unregistered') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="unregistered">Unregistered</label>
                                    </div>
                                </div>


                                <div class="col-md-4 form-group">
                                    <label class="form-label">CR Number</label>
                                    <input type="text" name="cr_number" class="form-control"
                                           value="{{ $supplier->cr_number }}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="form-label">VAT Number</label>
                                    <input type="text" name="vat_number" class="form-control"
                                           value="{{ $supplier->vat_number }}">
                                </div>
                            </div>
                            <div class="model-form-sub-title">
                                <h5>Credit Settings</h5>
                            </div>
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label class="form-label">Credit Limit</label>
                                    <input type="number" name="credit_limit" class="form-control" min="0"
                                           value="{{ $supplier->credit_limit }}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="form-label">Credit Days</label>
                                    <input type="number" name="credit_days" class="form-control" min="0"
                                           value="{{ $supplier->credit_days }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Address -->
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
                                              class="form-control h-100">{{ $supplier->address1_en }}</textarea>
                                </div>
                                <div class="col-md-4 form-group mb-5">
                                    <label class="form-label">Address (Arabic)</label>
                                    <textarea name="address1_ar" class="form-control h-100"
                                              dir="rtl">{{ $supplier->address1_ar }}</textarea>
                                </div>
                                {{--<div class="col-md-4 form-group">
                                    <label class="form-label">Address Line 2 (English)</label>
                                    <input type="text" name="address2_en" class="form-control"
                                           value="{{ $supplier->address2_en }}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="form-label">Address Line 2 (Arabic)</label>
                                    <input type="text" name="address2_ar" class="form-control" dir="rtl"
                                           value="{{ $supplier->address2_ar }}">
                                </div>--}}
                                <div class="col-md-4 form-group">
                                    <label class="form-label">City (English)</label>
                                    <input type="text" name="city_en" class="form-control"
                                           value="{{ $supplier->city_en }}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="form-label">City (Arabic)</label>
                                    <input type="text" name="city_ar" class="form-control" dir="rtl"
                                           value="{{ $supplier->city_ar }}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="form-label">Building Number</label>
                                    <input type="text" name="building_number" class="form-control"
                                           value="{{ $supplier->building_number }}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="form-label">Plot No</label>
                                    <input type="text" name="plot_no" class="form-control"
                                           value="{{ $supplier->plot_no }}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="form-label">Postal Code</label>
                                    <input type="text" name="postal_code" class="form-control"
                                           value="{{ $supplier->postal_code }}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="form-label">Country</label>
                                    <x-common.country :value="$supplier->country"></x-common.country>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
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
