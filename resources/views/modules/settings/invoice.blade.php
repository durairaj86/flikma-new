@section('js','user')
@section('page-title','Users')
<x-app-layout>
    <main class="gmail-content bg-white d-flex">
        @include('includes.settings-navigation')
        <section class="flex-grow-1 d-flex flex-column">
            <div class="px-4">
                @include('includes.master-header')
            </div>
            <div class="col-lg-12">
                <div class="<!--card border-0 shadow-lg--> rounded-4">
                    <div class="card-body p-0">
                        <div class="row">
                            <!-- Left side - Preview -->
                            <div class="col-md-6 border-end">
                                <div class="p-3">
                                    <h5 class="fw-bold mb-3 text-primary d-flex align-items-center"><i class="bi bi-eye me-2"></i> Invoice Preview</h5>
                                    <div id="invoice-preview" class="border rounded p-3" style="height: 75vh; overflow-y: auto;">
                                        <div class="text-center mb-3 text-muted">
                                            <p>Preview will update based on selected settings</p>
                                        </div>
                                        <!-- Invoice preview content will be loaded here -->
                                        <div id="preview-content">
                                            <!-- Simplified invoice preview structure -->
                                            <div class="border border-dark">
                                                <div class="d-flex justify-content-between border-bottom border-dark p-2">
                                                    <div class="logo-box">COMPANY LOGO</div>
                                                    <div class="text-end">
                                                        <p class="mb-0 fw-bold">COMPANY NAME</p>
                                                        <p class="mb-0 small">Company Address</p>
                                                        <p class="mb-0 small">City, Postal Code</p>
                                                    </div>
                                                </div>

                                                <div class="d-flex justify-content-between border-bottom border-dark">
                                                    <h5 class="m-0 p-1 text-center w-50">TAX INVOICE</h5>
                                                    <h5 class="m-0 p-1 text-center w-50">فاتورة ضريبية</h5>
                                                </div>

                                                <div class="border-bottom border-dark p-2">
                                                    <div class="row">
                                                        <div class="col-6 border-end">
                                                            <p class="mb-1 fw-bold">Customer:</p>
                                                            <p class="mb-0 small">Customer Name</p>
                                                            <p class="mb-0 small">Customer Address</p>
                                                        </div>
                                                        <div class="col-6 text-end">
                                                            <p class="mb-1 fw-bold">العميل:</p>
                                                            <p class="mb-0 small">اسم العميل</p>
                                                            <p class="mb-0 small">عنوان العميل</p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div id="preview-invoice-details" class="border-bottom border-dark p-2">
                                                    <!-- Invoice details will be dynamically updated -->
                                                    <div class="row mb-2">
                                                        <div class="col-4 fw-bold">Invoice No.:</div>
                                                        <div class="col-4 text-center">INV-12345</div>
                                                        <div class="col-4 text-end fw-bold">رقم الفاتورة:</div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-4 fw-bold">Invoice Date:</div>
                                                        <div class="col-4 text-center">01-Jan-23</div>
                                                        <div class="col-4 text-end fw-bold">تاريخ:</div>
                                                    </div>
                                                </div>

                                                <div id="preview-job-details" class="p-2">
                                                    <!-- Job details will be dynamically updated -->
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <table class="w-100 small">
                                                                <tr>
                                                                    <td class="fw-bold" width="40%">Shipper:</td>
                                                                    <td>Shipper Name</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="fw-bold">HBL No:</td>
                                                                    <td>HBL12345</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="fw-bold">Place of Origin:</td>
                                                                    <td>Origin City</td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                        <div class="col-6">
                                                            <table class="w-100 small">
                                                                <tr>
                                                                    <td class="fw-bold" width="40%">Job Number:</td>
                                                                    <td>JOB12345</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="fw-bold">Job Date:</td>
                                                                    <td>01-Jan-23</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="fw-bold">ETD:</td>
                                                                    <td>05-Jan-23</td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                                <table class="table table-sm table-bordered mb-0 small">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Description</th>
                                                            <th>Qty</th>
                                                            <th>Rate</th>
                                                            <th>Amount</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>Service Item</td>
                                                            <td>1</td>
                                                            <td>100.00</td>
                                                            <td>100.00</td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td colspan="2" class="fw-bold">Total:</td>
                                                            <td colspan="2" class="text-end fw-bold">100.00 SAR</td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right side - Settings -->
                            <div class="col-md-6">
                                <div class="overflow-auto" style="max-height: 85vh;">
                                    <div class="p-4 p-md-5 border-bottom">
                                        <h5 class="fw-bold mb-4 text-primary d-flex align-items-center"><i class="bi bi-palette me-2"></i> Design & Branding</h5>

                                <div class="mb-4 pb-4 border-bottom">
                                    <div class="d-flex align-items-center mb-3">
                                        <label class="form-check-label radio-container d-flex align-items-center me-3" for="defaultThemeRadio">
                                            <input class="form-check-input" type="radio" name="themeMode" id="defaultThemeRadio" checked>
                                            <span class="fw-bold fs-6 ms-2">Select Default Theme</span>
                                        </label>
                                    </div>

                                    <div class="d-flex flex-wrap theme-show gap-3 pt-2">
                                        <div class="theme-container" style="width: 140px;">
                                            <div class="theme active border border-primary p-2 rounded-3 text-center shadow-sm" data-theme="stylish" style="cursor:pointer; transform: translateY(-3px);">
                                                <div class="image-container" style="height: 80px; display:flex; align-items:center; justify-content:center;">
                                                    <img src="https://mybillbook.in/app//assets/images/themes/stylish.svg" alt="Stylish" class="img-fluid rounded" style="max-height:100%;">
                                                </div>
                                                <div class="theme-name small fw-medium mt-2 text-primary">Stylish</div>
                                            </div>
                                        </div>

                                        <div class="theme-container" style="width: 140px;">
                                            <div class="theme border p-2 rounded-3 text-center" data-theme="luxury" style="cursor:pointer;">
                                                <div class="image-container" style="height: 80px; display:flex; align-items:center; justify-content:center;">
                                                    <img src="https://mybillbook.in/app//assets/images/themes/luxury-theme.svg" alt="Luxury" class="img-fluid rounded" style="max-height:100%;">
                                                </div>
                                                <div class="theme-name small fw-medium mt-2">Luxury</div>
                                            </div>
                                        </div>

                                        <div class="theme-container" style="width: 140px;">
                                            <div class="theme border p-2 rounded-3 text-center" data-theme="advance-gst-tally" style="cursor:pointer;">
                                                <div class="image-container" style="height: 80px; display:flex; align-items:center; justify-content:center;">
                                                    <img src="https://mybillbook.in/app//assets/images/themes/advance-gst-tally.svg" alt="Advanced GST (Tally)" class="img-fluid rounded" style="max-height:100%;">
                                                </div>
                                                <div class="theme-name small fw-medium mt-2">Advanced GST (Tally)</div>
                                            </div>
                                        </div>

                                        <div class="theme-container" style="width: 140px;">
                                            <div class="theme border p-2 rounded-3 text-center" data-theme="billbook" style="cursor:pointer;">
                                                <div class="image-container" style="height: 80px; display:flex; align-items:center; justify-content:center;">
                                                    <img src="https://mybillbook.in/app//assets/images/themes/billbook.svg" alt="Billbook" class="img-fluid rounded" style="max-height:100%;">
                                                </div>
                                                <div class="theme-name small fw-medium mt-2">Billbook</div>
                                            </div>
                                        </div>

                                        <div class="theme-container" style="width: 140px;">
                                            <button class="btn btn-outline-secondary w-100 h-100 border-2 border-dashed" style="height: 120px;">
                                                <i class="bi bi-grid-3x3-gap me-1"></i> See All Themes
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-3 border-top">
                                        <div class="d-flex align-items-center mb-3">
                                            <label class="form-check-label radio-container d-flex align-items-center me-3" for="customThemeRadio">
                                                <input class="form-check-input" type="radio" name="themeMode" id="customThemeRadio">
                                                <span class="fw-bold fs-6 ms-2">Create Custom Theme</span>
                                                <i class="bi bi-info-circle ms-2 text-muted" data-bs-toggle="tooltip" title="Design your own template from scratch."></i>
                                            </label>
                                        </div>
                                        <button class="btn btn-outline-primary w-100 fw-bold" id="createCustomBtn"><i class="bi bi-magic me-2"></i> Design Your Own Layout</button>
                                    </div>

                                </div>

                                <div class="select-color pt-3">
                                    <div class="fw-bold fs-6 mb-3">Select Primary Accent Color</div>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="color-show rounded-circle border border-dark border-opacity-25 selected" data-color="#0b6aa0" style="background:#0b6aa0; width: 32px; height: 32px; cursor:pointer; outline: 3px solid rgba(13,110,253,0.3);"></div>
                                        <div class="color-show rounded-circle border border-dark border-opacity-25" data-color="#000000" style="background:#000; width: 32px; height: 32px; cursor:pointer;"></div>
                                        <div class="color-show rounded-circle border border-dark border-opacity-25" data-color="#407400" style="background:#407400; width: 32px; height: 32px; cursor:pointer;"></div>
                                        <div class="color-show rounded-circle border border-dark border-opacity-25" data-color="#840bb2" style="background:#840bb2; width: 32px; height: 32px; cursor:pointer;"></div>
                                        <div class="color-show rounded-circle border border-dark border-opacity-25" data-color="#c11111" style="background:#c11111; width: 32px; height: 32px; cursor:pointer;"></div>
                                        <div class="color-show rounded-circle border border-dark border-opacity-25" data-color="#5b57ae" style="background:#5b57ae; width: 32px; height: 32px; cursor:pointer;"></div>
                                        <div class="color-show rounded-circle border border-dark border-opacity-25" data-color="#cd9d23" style="background:#cd9d23; width: 32px; height: 32px; cursor:pointer;"></div>
                                        <div class="color-show rounded-circle border border-dark border-opacity-25" data-color="#bf6200" style="background:#bf6200; width: 32px; height: 32px; cursor:pointer;"></div>
                                        <button class="btn btn-outline-secondary btn-sm ms-3"><i class="bi bi-eyedropper me-1"></i> Custom</button>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 p-md-5 border-bottom">
                                <h5 class="fw-bold mb-4 text-primary d-flex align-items-center"><i class="bi bi-gear me-2"></i> Display Options</h5>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <div>
                                                <label class="fw-medium mb-0">Show party balance in invoice</label>
                                                <i class="bi bi-question-circle ms-2 text-muted small" data-bs-toggle="tooltip" title="Displays the customer's total outstanding balance."></i>
                                            </div>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input" type="checkbox" role="switch" id="togglePartyBalance" data-key="partyBalance">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <div>
                                                <label class="fw-medium mb-0">Enable free item quantity</label>
                                            </div>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input" type="checkbox" role="switch" id="toggleFreeItem" data-key="freeItemQty">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <div>
                                                <label class="fw-medium mb-0">Show item description in invoice</label>
                                            </div>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input" type="checkbox" role="switch" id="toggleItemDesc" data-key="itemDescription" checked>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <div>
                                                <label class="fw-medium mb-0">Show Alternate Unit in Invoice</label>
                                            </div>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input" type="checkbox" role="switch" id="toggleAltUnit" data-key="altUnit">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <div>
                                                <label class="fw-medium mb-0">Show phone number on Invoice</label>
                                            </div>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input" type="checkbox" role="switch" id="toggleShowPhone" data-key="showPhone" checked>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <div>
                                                <label class="fw-medium mb-0">Show time on Invoices</label>
                                                <i class="bi bi-info-circle ms-2 text-muted small" data-bs-toggle="tooltip" title="Time will be shown only if Invoice Date is today's Date."></i>
                                            </div>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input" type="checkbox" role="switch" id="toggleShowTime" data-key="showTime">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="p-4 p-md-5">
                                <h5 class="fw-bold mb-4 text-primary d-flex align-items-center"><i class="bi bi-sliders me-2"></i> Advanced Invoice Structure</h5>

                                <div class="card mb-3 border-secondary border-opacity-25">
                                    <div class="card-header bg-white p-3 collapsed" id="headingInvoiceDetails" data-bs-toggle="collapse" data-bs-target="#collapseInvoiceDetails" aria-expanded="false" aria-controls="collapseInvoiceDetails" style="cursor: pointer;">
                                        <h6 class="mb-0 fw-bold d-flex justify-content-between align-items-center">
                                            Invoice Details
                                            <i class="bi bi-chevron-down ms-2"></i>
                                        </h6>
                                    </div>
                                    <div id="collapseInvoiceDetails" class="collapse" aria-labelledby="headingInvoiceDetails">
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Original 6 columns -->
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colAwb" data-invoice-detail="awb_hbl"><label class="form-check-label">AWB / HBL No</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colIncoterm" data-invoice-detail="incoterm"><label class="form-check-label">Incoterm</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colPolPod" data-invoice-detail="pol_pod"><label class="form-check-label">POL / POD</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colVoyage" data-invoice-detail="voyage_flight"><label class="form-check-label">Voyage / Flight No</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colShipMode" data-invoice-detail="shipment_mode"><label class="form-check-label">Shipment Mode</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colCarrier" data-invoice-detail="carrier"><label class="form-check-label">Carrier</label></div></div>

                                                <!-- Additional job columns -->
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colJobNumber" data-invoice-detail="job_number"><label class="form-check-label">Job Number</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colRefNumber" data-invoice-detail="reference_number"><label class="form-check-label">Reference Number</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colActivity" data-invoice-detail="activity"><label class="form-check-label">Logistics Activity</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colShipCategory" data-invoice-detail="shipment_category"><label class="form-check-label">Shipment Category</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colPlaceReceipt" data-invoice-detail="place_of_receipt"><label class="form-check-label">Place of Receipt</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colPlaceDelivery" data-invoice-detail="place_of_delivery"><label class="form-check-label">Place of Delivery</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colFinalDest" data-invoice-detail="final_destination"><label class="form-check-label">Final Destination</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colCommodity" data-invoice-detail="commodity"><label class="form-check-label">Commodity</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colPickupDate" data-invoice-detail="pickup_date"><label class="form-check-label">Pickup Date</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colDeliveryDate" data-invoice-detail="delivery_date"><label class="form-check-label">Delivery Date</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colEta" data-invoice-detail="eta"><label class="form-check-label">ETA</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colEtd" data-invoice-detail="etd"><label class="form-check-label">ETD</label></div></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-3 border-secondary border-opacity-25">
                                    <div class="card-header bg-white p-3" id="headingPartyDetails" data-bs-toggle="collapse" data-bs-target="#collapsePartyDetails" aria-expanded="true" aria-controls="collapsePartyDetails" style="cursor: pointer;">
                                        <h6 class="mb-0 fw-bold d-flex justify-content-between align-items-center">
                                            Party Details: Custom Fields
                                            <i class="bi bi-chevron-up ms-2"></i>
                                        </h6>
                                    </div>
                                    <div id="collapsePartyDetails" class="collapse show" aria-labelledby="headingPartyDetails">
                                        <div class="card-body">
                                            <h6 class="mb-3">Customer Information</h6>
                                            <div class="row">
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyCode" data-party-detail="code"><label class="form-check-label">Customer Code</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyUniqueCode" data-party-detail="unique_code"><label class="form-check-label">Unique Code</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyBusinessType" data-party-detail="business_type"><label class="form-check-label">Business Type</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyCrNumber" data-party-detail="cr_number"><label class="form-check-label">CR Number</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyVatNumber" data-party-detail="vat_number"><label class="form-check-label">VAT Number</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyCreditLimit" data-party-detail="credit_limit"><label class="form-check-label">Credit Limit</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyCreditDays" data-party-detail="credit_days"><label class="form-check-label">Credit Days</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyRegion" data-party-detail="region"><label class="form-check-label">Region</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyPostalCode" data-party-detail="postal_code"><label class="form-check-label">Postal Code</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyCountry" data-party-detail="country"><label class="form-check-label">Country</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyEmail" data-party-detail="email"><label class="form-check-label">Email</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyAltPhone" data-party-detail="alt_phone"><label class="form-check-label">Alternative Phone</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyPreferredShipping" data-party-detail="preferred_shipping"><label class="form-check-label">Preferred Shipping</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyPreferredCarrier" data-party-detail="preferred_carrier"><label class="form-check-label">Preferred Carrier</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyDefaultPort" data-party-detail="default_port"><label class="form-check-label">Default Port</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyPaymentMethod" data-party-detail="payment_method"><label class="form-check-label">Payment Method</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyIban" data-party-detail="iban"><label class="form-check-label">IBAN</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyPaymentTerms" data-party-detail="payment_terms"><label class="form-check-label">Payment Terms</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partySalesperson" data-party-detail="salesperson"><label class="form-check-label">Salesperson</label></div></div>
                                            </div>

                                            <hr class="my-3">

                                            <h6 class="mb-3">Custom Fields</h6>
                                            <button class="btn btn-outline-primary btn-sm border-dashed fw-medium" id="addCustomFieldBtn"><i class="bi bi-plus me-1"></i> Add Custom Field</button>
                                            <div id="customFieldsList" class="mt-3 small text-muted">
                                                <p class="mb-0">No custom fields added yet.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-3 border-secondary border-opacity-25">
                                    <div class="card-header bg-white p-3 collapsed" id="headingItemCols" data-bs-toggle="collapse" data-bs-target="#collapseItemCols" aria-expanded="false" aria-controls="collapseItemCols" style="cursor: pointer;">
                                        <h6 class="mb-0 fw-bold d-flex justify-content-between align-items-center">
                                            Item Table Columns Visibility
                                            <i class="bi bi-chevron-down ms-2"></i>
                                        </h6>
                                    </div>
                                    <div id="collapseItemCols" class="collapse" aria-labelledby="headingItemCols">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="colHsn">
                                                        <label class="form-check-label" for="colHsn">HSN / SAC</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="colUnit" checked>
                                                        <label class="form-check-label" for="colUnit">Unit</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="colRate" checked>
                                                        <label class="form-check-label" for="colRate">Rate</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" value="" id="colDiscount">
                                                        <label class="form-check-label" for="colDiscount">Discount</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-3 border-secondary border-opacity-25">
                                    <div class="card-header bg-white p-3 collapsed" id="headingMisc" data-bs-toggle="collapse" data-bs-target="#collapseMisc" aria-expanded="false" aria-controls="collapseMisc" style="cursor: pointer;">
                                        <h6 class="mb-0 fw-bold d-flex justify-content-between align-items-center">
                                            Miscellaneous Details <span class="badge bg-info text-dark ms-3">New</span>
                                            <i class="bi bi-chevron-down ms-2"></i>
                                        </h6>
                                    </div>
                                    <div id="collapseMisc" class="collapse" aria-labelledby="headingMisc">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="miscInvoiceNotes" data-misc-detail="invoice_notes"><label class="form-check-label">Invoice Notes</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="miscTermsConditions" data-misc-detail="terms_conditions"><label class="form-check-label">Terms & Conditions</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="miscPaymentInstructions" data-misc-detail="payment_instructions"><label class="form-check-label">Payment Instructions</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="miscDeliveryInstructions" data-misc-detail="delivery_instructions"><label class="form-check-label">Delivery Instructions</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="miscHandlingInstructions" data-misc-detail="handling_instructions"><label class="form-check-label">Special Handling</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="miscAdditionalContacts" data-misc-detail="additional_contacts"><label class="form-check-label">Additional Contacts</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="miscReferenceNumbers" data-misc-detail="reference_numbers"><label class="form-check-label">Reference Numbers</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="miscAttachments" data-misc-detail="attachments"><label class="form-check-label">Attachments</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="miscPackingSlip" data-misc-detail="packing_slip"><label class="form-check-label">Packing Slip</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="miscTransportInfo" data-misc-detail="transport_info"><label class="form-check-label">Transport Information</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="miscCustomsInfo" data-misc-detail="customs_info"><label class="form-check-label">Customs Information</label></div></div>
                                                <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="miscInsuranceInfo" data-misc-detail="insurance_info"><label class="form-check-label">Insurance Information</label></div></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // Initialize tooltips (requires Bootstrap JS to be loaded)
                document.addEventListener('DOMContentLoaded', function() {
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    });

                    // Initialize settings from the server
                    initializeSettings();

                    // Initialize the preview
                    updatePreview();

                    // Add event listeners for toggle switches
                    document.querySelectorAll('.form-check-input[type="checkbox"][data-key]').forEach(function(toggle) {
                        toggle.addEventListener('change', function() {
                            saveSettings();
                        });
                    });

                    // Add event listeners for theme selection
                    document.querySelectorAll('.theme').forEach(function(theme) {
                        theme.addEventListener('click', function() {
                            // Remove active class from all themes
                            document.querySelectorAll('.theme').forEach(function(t) {
                                t.classList.remove('active', 'border-primary');
                                t.style.transform = '';
                                t.querySelector('.theme-name').classList.remove('text-primary');
                            });

                            // Add active class to selected theme
                            this.classList.add('active', 'border-primary');
                            this.style.transform = 'translateY(-3px)';
                            this.querySelector('.theme-name').classList.add('text-primary');

                            saveSettings();
                        });
                    });

                    // Add event listeners for color selection
                    document.querySelectorAll('.color-show').forEach(function(color) {
                        color.addEventListener('click', function() {
                            // Remove selected class from all colors
                            document.querySelectorAll('.color-show').forEach(function(c) {
                                c.classList.remove('selected');
                                c.style.outline = '';
                            });

                            // Add selected class to selected color
                            this.classList.add('selected');
                            this.style.outline = '3px solid rgba(13,110,253,0.3)';

                            saveSettings();
                        });
                    });

                    // Add event listeners for invoice details checkboxes
                    document.querySelectorAll('#collapseInvoiceDetails input[type="checkbox"]').forEach(function(checkbox) {
                        checkbox.addEventListener('change', function() {
                            saveSettings();
                            updatePreview();
                        });
                    });

                    // Add event listeners for party details checkboxes
                    document.querySelectorAll('#collapsePartyDetails input[type="checkbox"]').forEach(function(checkbox) {
                        checkbox.addEventListener('change', function() {
                            saveSettings();
                            updatePreview();
                        });
                    });

                    // Add event listeners for miscellaneous details checkboxes
                    document.querySelectorAll('#collapseMisc input[type="checkbox"]').forEach(function(checkbox) {
                        checkbox.addEventListener('change', function() {
                            saveSettings();
                            updatePreview();
                        });
                    });

                    // Add event listeners for item table columns checkboxes
                    document.querySelectorAll('#collapseItemCols input[type="checkbox"]').forEach(function(checkbox) {
                        checkbox.addEventListener('change', function() {
                            saveSettings();
                            updatePreview();
                        });
                    });
                });

                function initializeSettings() {
                    // If settings are available, initialize the UI
                    @if(isset($settings))
                        // Set theme
                        const theme = '{{ $settings->theme }}';
                        document.querySelectorAll('.theme').forEach(function(t) {
                            if (t.dataset.theme === theme) {
                                t.classList.add('active', 'border-primary');
                                t.style.transform = 'translateY(-3px)';
                                t.querySelector('.theme-name').classList.add('text-primary');
                            } else {
                                t.classList.remove('active', 'border-primary');
                                t.style.transform = '';
                                t.querySelector('.theme-name').classList.remove('text-primary');
                            }
                        });

                        // Set color
                        const color = '{{ $settings->primary_color }}';
                        document.querySelectorAll('.color-show').forEach(function(c) {
                            if (c.dataset.color === color) {
                                c.classList.add('selected');
                                c.style.outline = '3px solid rgba(13,110,253,0.3)';
                            } else {
                                c.classList.remove('selected');
                                c.style.outline = '';
                            }
                        });

                        // Set toggle switches
                        document.getElementById('togglePartyBalance').checked = {{ $settings->party_balance ? 'true' : 'false' }};
                        document.getElementById('toggleFreeItem').checked = {{ $settings->free_item_qty ? 'true' : 'false' }};
                        document.getElementById('toggleItemDesc').checked = {{ $settings->item_description ? 'true' : 'false' }};
                        document.getElementById('toggleAltUnit').checked = {{ $settings->alt_unit ? 'true' : 'false' }};
                        document.getElementById('toggleShowPhone').checked = {{ $settings->show_phone ? 'true' : 'false' }};
                        document.getElementById('toggleShowTime').checked = {{ $settings->show_time ? 'true' : 'false' }};

                        // Set invoice details checkboxes
                        document.getElementById('colAwb').checked = {{ $settings->awb_hbl ? 'true' : 'false' }};
                        document.getElementById('colIncoterm').checked = {{ $settings->incoterm ? 'true' : 'false' }};
                        document.getElementById('colPolPod').checked = {{ $settings->pol_pod ? 'true' : 'false' }};
                        document.getElementById('colVoyage').checked = {{ $settings->voyage_flight ? 'true' : 'false' }};
                        document.getElementById('colShipMode').checked = {{ $settings->shipment_mode ? 'true' : 'false' }};
                        document.getElementById('colCarrier').checked = {{ $settings->carrier ? 'true' : 'false' }};

                        // Set item table columns checkboxes
                        document.getElementById('colHsn').checked = {{ $settings->hsn_sac ? 'true' : 'false' }};
                        document.getElementById('colUnit').checked = {{ $settings->unit ? 'true' : 'false' }};
                        document.getElementById('colRate').checked = {{ $settings->rate ? 'true' : 'false' }};
                        document.getElementById('colDiscount').checked = {{ $settings->discount ? 'true' : 'false' }};

                        // Initialize custom fields from JSON
                        @if(isset($settings->custom_fields) && $settings->custom_fields)
                            try {
                                const customFields = JSON.parse('{!! addslashes($settings->custom_fields) !!}');

                                // Initialize invoice details checkboxes
                                if (customFields.invoice_details) {
                                    Object.keys(customFields.invoice_details).forEach(function(key) {
                                        const checkbox = document.querySelector(`#collapseInvoiceDetails input[data-invoice-detail="${key}"]`);
                                        if (checkbox) {
                                            checkbox.checked = customFields.invoice_details[key];
                                        }
                                    });
                                }

                                // Initialize party details checkboxes
                                if (customFields.party_details) {
                                    Object.keys(customFields.party_details).forEach(function(key) {
                                        const checkbox = document.querySelector(`#collapsePartyDetails input[data-party-detail="${key}"]`);
                                        if (checkbox) {
                                            checkbox.checked = customFields.party_details[key];
                                        }
                                    });
                                }

                                // Initialize miscellaneous details checkboxes
                                if (customFields.misc_details) {
                                    Object.keys(customFields.misc_details).forEach(function(key) {
                                        const checkbox = document.querySelector(`#collapseMisc input[data-misc-detail="${key}"]`);
                                        if (checkbox) {
                                            checkbox.checked = customFields.misc_details[key];
                                        }
                                    });
                                }
                            } catch (e) {
                                console.error('Error parsing custom fields:', e);
                            }
                        @endif
                    @endif
                }

                function updatePreview() {
                    // Update the preview based on selected settings
                    const previewJobDetails = document.getElementById('preview-job-details');
                    const previewInvoiceDetails = document.getElementById('preview-invoice-details');

                    // Clear existing content
                    previewJobDetails.innerHTML = '';
                    previewInvoiceDetails.innerHTML = '';

                    // Create job details content based on selected checkboxes
                    let jobDetailsHTML = '<div class="row"><div class="col-6"><table class="w-100 small">';

                    // Add job details based on selected checkboxes
                    if (document.getElementById('colJobNumber').checked) {
                        jobDetailsHTML += '<tr><td class="fw-bold" width="40%">Job Number:</td><td>JOB12345</td></tr>';
                    }
                    if (document.getElementById('colShipper') && document.getElementById('colShipper').checked) {
                        jobDetailsHTML += '<tr><td class="fw-bold" width="40%">Shipper:</td><td>Shipper Name</td></tr>';
                    } else {
                        jobDetailsHTML += '<tr><td class="fw-bold" width="40%">Shipper:</td><td>Shipper Name</td></tr>';
                    }
                    if (document.getElementById('colAwb').checked) {
                        jobDetailsHTML += '<tr><td class="fw-bold">HBL No:</td><td>HBL12345</td></tr>';
                    }
                    if (document.getElementById('colPolPod').checked) {
                        jobDetailsHTML += '<tr><td class="fw-bold">Place of Origin:</td><td>Origin City</td></tr>';
                    }
                    if (document.getElementById('colIncoterm').checked) {
                        jobDetailsHTML += '<tr><td class="fw-bold">Incoterm:</td><td>FOB</td></tr>';
                    }
                    if (document.getElementById('colVoyage').checked) {
                        jobDetailsHTML += '<tr><td class="fw-bold">Voyage/Flight No:</td><td>VF12345</td></tr>';
                    }
                    if (document.getElementById('colShipMode').checked) {
                        jobDetailsHTML += '<tr><td class="fw-bold">Shipment Mode:</td><td>Sea</td></tr>';
                    }
                    if (document.getElementById('colCarrier').checked) {
                        jobDetailsHTML += '<tr><td class="fw-bold">Carrier:</td><td>Carrier Name</td></tr>';
                    }

                    jobDetailsHTML += '</table></div><div class="col-6"><table class="w-100 small">';

                    // Add more job details for the right column
                    if (document.getElementById('colEtd').checked) {
                        jobDetailsHTML += '<tr><td class="fw-bold" width="40%">ETD:</td><td>05-Jan-23</td></tr>';
                    }
                    if (document.getElementById('colEta').checked) {
                        jobDetailsHTML += '<tr><td class="fw-bold" width="40%">ETA:</td><td>15-Jan-23</td></tr>';
                    }
                    if (document.getElementById('colFinalDest').checked) {
                        jobDetailsHTML += '<tr><td class="fw-bold" width="40%">Final Destination:</td><td>Destination City</td></tr>';
                    }
                    if (document.getElementById('colCommodity').checked) {
                        jobDetailsHTML += '<tr><td class="fw-bold" width="40%">Commodity:</td><td>General Cargo</td></tr>';
                    }

                    jobDetailsHTML += '</table></div></div>';

                    // Update the preview
                    previewJobDetails.innerHTML = jobDetailsHTML;

                    // Create invoice details content
                    let invoiceDetailsHTML = '';
                    invoiceDetailsHTML += '<div class="row mb-2"><div class="col-4 fw-bold">Invoice No.:</div><div class="col-4 text-center">INV-12345</div><div class="col-4 text-end fw-bold">رقم الفاتورة:</div></div>';
                    invoiceDetailsHTML += '<div class="row"><div class="col-4 fw-bold">Invoice Date:</div><div class="col-4 text-center">01-Jan-23</div><div class="col-4 text-end fw-bold">تاريخ:</div></div>';

                    // Update the preview
                    previewInvoiceDetails.innerHTML = invoiceDetailsHTML;
                }

                function saveSettings() {
                    // Get selected theme
                    let theme = 'stylish';
                    document.querySelectorAll('.theme.active').forEach(function(t) {
                        theme = t.dataset.theme;
                    });

                    // Get selected color
                    let color = '#0b6aa0';
                    document.querySelectorAll('.color-show.selected').forEach(function(c) {
                        color = c.dataset.color;
                    });

                    // Get toggle switch values
                    const partyBalance = document.getElementById('togglePartyBalance').checked;
                    const freeItemQty = document.getElementById('toggleFreeItem').checked;
                    const itemDescription = document.getElementById('toggleItemDesc').checked;
                    const altUnit = document.getElementById('toggleAltUnit').checked;
                    const showPhone = document.getElementById('toggleShowPhone').checked;
                    const showTime = document.getElementById('toggleShowTime').checked;

                    // Get invoice details checkbox values
                    const awbHbl = document.getElementById('colAwb').checked;
                    const incoterm = document.getElementById('colIncoterm').checked;
                    const polPod = document.getElementById('colPolPod').checked;
                    const voyageFlight = document.getElementById('colVoyage').checked;
                    const shipmentMode = document.getElementById('colShipMode').checked;
                    const carrier = document.getElementById('colCarrier').checked;

                    // Get item table columns checkbox values
                    const hsnSac = document.getElementById('colHsn').checked;
                    const unit = document.getElementById('colUnit').checked;
                    const rate = document.getElementById('colRate').checked;
                    const discount = document.getElementById('colDiscount').checked;

                    // Collect invoice details JSON data
                    const invoiceDetails = {};
                    document.querySelectorAll('#collapseInvoiceDetails input[type="checkbox"][data-invoice-detail]').forEach(function(checkbox) {
                        invoiceDetails[checkbox.dataset.invoiceDetail] = checkbox.checked;
                    });

                    // Collect party details JSON data
                    const partyDetails = {};
                    document.querySelectorAll('#collapsePartyDetails input[type="checkbox"][data-party-detail]').forEach(function(checkbox) {
                        partyDetails[checkbox.dataset.partyDetail] = checkbox.checked;
                    });

                    // Collect miscellaneous details JSON data
                    const miscDetails = {};
                    document.querySelectorAll('#collapseMisc input[type="checkbox"][data-misc-detail]').forEach(function(checkbox) {
                        miscDetails[checkbox.dataset.miscDetail] = checkbox.checked;
                    });

                    // Update the preview
                    updatePreview();

                    // Prepare data for AJAX request
                    const data = {
                        theme: theme,
                        primary_color: color,
                        party_balance: partyBalance ? 1 : 0,
                        free_item_qty: freeItemQty ? 1 : 0,
                        item_description: itemDescription ? 1 : 0,
                        alt_unit: altUnit ? 1 : 0,
                        show_phone: showPhone ? 1 : 0,
                        show_time: showTime ? 1 : 0,
                        awb_hbl: awbHbl ? 1 : 0,
                        incoterm: incoterm ? 1 : 0,
                        pol_pod: polPod ? 1 : 0,
                        voyage_flight: voyageFlight ? 1 : 0,
                        shipment_mode: shipmentMode ? 1 : 0,
                        carrier: carrier ? 1 : 0,
                        hsn_sac: hsnSac ? 1 : 0,
                        unit: unit ? 1 : 0,
                        rate: rate ? 1 : 0,
                        discount: discount ? 1 : 0,
                        invoice_details: invoiceDetails,
                        party_details: partyDetails,
                        misc_details: miscDetails,
                        _token: '{{ csrf_token() }}'
                    };

                    // Send AJAX request
                    fetch('{{ route("settings.invoice.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Show success message
                            const toast = document.createElement('div');
                            toast.className = 'position-fixed bottom-0 end-0 p-3';
                            toast.style.zIndex = '5';
                            toast.innerHTML = `
                                <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                                    <div class="toast-header bg-success text-white">
                                        <strong class="me-auto">Success</strong>
                                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                                    </div>
                                    <div class="toast-body">
                                        ${data.message}
                                    </div>
                                </div>
                            `;
                            document.body.appendChild(toast);
                            setTimeout(() => {
                                toast.remove();
                            }, 3000);
                        } else {
                            // Show error message
                            console.error('Error saving settings:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error saving settings:', error);
                    });
                }
            </script>
        </section>
    </main>
</x-app-layout>
