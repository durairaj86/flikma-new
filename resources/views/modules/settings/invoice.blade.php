@section('page-title','Invoice Settings')
<x-app-layout>
    <main class="gmail-content bg-white d-flex flex-column">
        @include('includes.settings-navigation')
        <section class="flex-grow-1 d-flex flex-column">

            <div class="col-lg-12">
                <div class="<!--card border-0 shadow-lg--> rounded-4">
                    <div class="card-body p-0">
                        <div class="row">
                            <!-- Left side - Preview -->
                            <div class="col-md-6 border-end">
                                <div class="p-3">
                                    <h5 class="fw-bold mb-3 text-primary d-flex align-items-center">
                                        <i class="bi bi-eye me-2"></i> Invoice Preview
                                        <span id="preview-loading" class="spinner-border spinner-border-sm text-primary ms-2" style="display:none;"></span>
                                    </h5>
                                    <div id="invoice-preview" class="border rounded" style="height: 75vh; overflow: hidden;">
                                        <iframe id="preview-frame" title="Invoice preview" style="width:100%; height:100%; border:0;"></iframe>
                                    </div>
                                </div>
                            </div>

                            <!-- Right side - Settings -->
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center px-4 px-md-5 py-3 border-bottom bg-white sticky-top" style="top: 0; z-index: 10;">
                                    <span id="unsaved-indicator" class="small text-warning fw-medium" style="display:none;"><i class="bi bi-circle-fill me-1" style="font-size:6px;"></i>Unsaved changes</span>
                                    <span id="saved-indicator" class="small text-success fw-medium"><i class="bi bi-check-circle me-1"></i>Saved</span>
                                    <button type="button" id="saveSettingsBtn" class="btn btn-primary btn-sm px-4 fw-bold ms-auto">
                                        <i class="bi bi-save me-1"></i> Save
                                    </button>
                                </div>
                                <div class="overflow-auto" style="max-height: 78vh;">
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
                                                    <svg viewBox="0 0 100 80" width="100%" height="100%" style="max-height:100%;">
                                                        <rect x="4" y="4" width="92" height="72" fill="#fff" stroke="#333" stroke-width="1.5"/>
                                                        <rect x="4" y="4" width="92" height="14" fill="none" stroke="#333" stroke-width="1"/>
                                                        <text x="50" y="13" font-size="7" font-weight="700" text-anchor="middle" fill="#333">TAX INVOICE</text>
                                                        <line x1="4" y1="30" x2="96" y2="30" stroke="#999" stroke-width="0.5"/>
                                                        <line x1="4" y1="38" x2="96" y2="38" stroke="#999" stroke-width="0.5"/>
                                                        <rect x="8" y="46" width="84" height="8" fill="#e0e0e0"/>
                                                        <line x1="8" y1="58" x2="92" y2="58" stroke="#ccc" stroke-width="0.5"/>
                                                        <line x1="8" y1="64" x2="92" y2="64" stroke="#ccc" stroke-width="0.5"/>
                                                    </svg>
                                                </div>
                                                <div class="theme-name small fw-medium mt-2 text-primary">Stylish</div>
                                            </div>
                                        </div>

                                        <div class="theme-container" style="width: 140px;">
                                            <div class="theme border p-2 rounded-3 text-center" data-theme="luxury" style="cursor:pointer;">
                                                <div class="image-container" style="height: 80px; display:flex; align-items:center; justify-content:center;">
                                                    <svg viewBox="0 0 100 80" width="100%" height="100%" style="max-height:100%;">
                                                        <rect x="4" y="4" width="92" height="72" rx="4" fill="#fff" stroke="#0b6aa0" stroke-width="1.5"/>
                                                        <text x="50" y="18" font-size="8" font-family="Georgia, serif" font-weight="700" text-anchor="middle" fill="#0b6aa0">TAX INVOICE</text>
                                                        <line x1="10" y1="24" x2="90" y2="24" stroke="#0b6aa0" stroke-width="1"/>
                                                        <rect x="8" y="30" width="38" height="18" rx="2" fill="none" stroke="#ddd" stroke-width="0.5"/>
                                                        <rect x="54" y="30" width="38" height="18" rx="2" fill="none" stroke="#ddd" stroke-width="0.5"/>
                                                        <rect x="8" y="52" width="84" height="6" fill="#0b6aa0"/>
                                                        <line x1="8" y1="62" x2="92" y2="62" stroke="#eee" stroke-width="0.5"/>
                                                        <line x1="8" y1="68" x2="92" y2="68" stroke="#eee" stroke-width="0.5"/>
                                                    </svg>
                                                </div>
                                                <div class="theme-name small fw-medium mt-2">Luxury</div>
                                            </div>
                                        </div>

                                        <div class="theme-container" style="width: 140px;">
                                            <div class="theme border p-2 rounded-3 text-center" data-theme="advance-gst-tally" style="cursor:pointer;">
                                                <div class="image-container" style="height: 80px; display:flex; align-items:center; justify-content:center;">
                                                    <svg viewBox="0 0 100 80" width="100%" height="100%" style="max-height:100%;">
                                                        <rect x="4" y="4" width="92" height="72" fill="#fff" stroke="#000" stroke-width="2"/>
                                                        <rect x="4" y="4" width="92" height="10" fill="#eee" stroke="#000" stroke-width="1"/>
                                                        <text x="50" y="11.5" font-size="6" font-family="monospace" font-weight="700" text-anchor="middle" fill="#000">TAX INVOICE</text>
                                                        <line x1="4" y1="24" x2="96" y2="24" stroke="#000" stroke-width="1"/>
                                                        <line x1="50" y1="14" x2="50" y2="24" stroke="#000" stroke-width="0.5"/>
                                                        <line x1="4" y1="38" x2="96" y2="38" stroke="#000" stroke-width="1"/>
                                                        <line x1="50" y1="24" x2="50" y2="38" stroke="#000" stroke-width="0.5"/>
                                                        <rect x="4" y="44" width="92" height="7" fill="#000"/>
                                                        <line x1="4" y1="58" x2="96" y2="58" stroke="#666" stroke-width="0.5"/>
                                                        <line x1="4" y1="65" x2="96" y2="65" stroke="#666" stroke-width="0.5"/>
                                                    </svg>
                                                </div>
                                                <div class="theme-name small fw-medium mt-2">Advanced GST (Tally)</div>
                                            </div>
                                        </div>

                                        <div class="theme-container" style="width: 140px;">
                                            <div class="theme border p-2 rounded-3 text-center" data-theme="billbook" style="cursor:pointer;">
                                                <div class="image-container" style="height: 80px; display:flex; align-items:center; justify-content:center;">
                                                    <svg viewBox="0 0 100 80" width="100%" height="100%" style="max-height:100%;">
                                                        <rect x="4" y="4" width="92" height="72" rx="6" fill="#fff" stroke="#e5e7eb" stroke-width="1"/>
                                                        <rect x="60" y="10" width="32" height="12" rx="3" fill="#0b6aa0"/>
                                                        <text x="76" y="18.5" font-size="6" font-weight="700" text-anchor="middle" fill="#fff">INVOICE</text>
                                                        <rect x="8" y="28" width="84" height="12" rx="3" fill="#f9fafb"/>
                                                        <rect x="8" y="44" width="40" height="24" rx="3" fill="none" stroke="#e5e7eb" stroke-width="0.75"/>
                                                        <rect x="52" y="44" width="40" height="24" rx="3" fill="none" stroke="#e5e7eb" stroke-width="0.75"/>
                                                    </svg>
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

                    // Toggle switches, color, and checkboxes patch the already-loaded
                    // preview iframe directly (pure client-side, zero network requests).
                    // Only the theme thumbnail click re-fetches, since switching the
                    // whole layout can't be faked by patching the old one.
                    document.querySelectorAll('.form-check-input[type="checkbox"][data-key]').forEach(function(toggle) {
                        toggle.addEventListener('change', function() {
                            const toggleKey = DATA_KEY_TO_TOGGLE[this.dataset.key];
                            if (toggleKey) applyLocalToggle(toggleKey, this.checked);
                            markUnsaved();
                        });
                    });

                    // Add event listeners for theme selection
                    document.querySelectorAll('.theme').forEach(function(theme) {
                        theme.addEventListener('click', function() {
                            document.querySelectorAll('.theme').forEach(function(t) {
                                t.classList.remove('active', 'border-primary');
                                t.style.transform = '';
                                t.querySelector('.theme-name').classList.remove('text-primary');
                            });
                            this.classList.add('active', 'border-primary');
                            this.style.transform = 'translateY(-3px)';
                            this.querySelector('.theme-name').classList.add('text-primary');
                            updatePreview();
                            markUnsaved();
                        });
                    });

                    // Add event listeners for color selection — patches the preview's
                    // dynamic color <style> directly, no request.
                    document.querySelectorAll('.color-show').forEach(function(color) {
                        color.addEventListener('click', function() {
                            document.querySelectorAll('.color-show').forEach(function(c) {
                                c.classList.remove('selected');
                                c.style.outline = '';
                            });
                            this.classList.add('selected');
                            this.style.outline = '3px solid rgba(13,110,253,0.3)';
                            applyLocalColor(this.dataset.color);
                            markUnsaved();
                        });
                    });

                    // Item table column checkboxes — patch matching data-toggle cells
                    document.querySelectorAll('#collapseItemCols input[type="checkbox"]').forEach(function(checkbox) {
                        checkbox.addEventListener('change', function() {
                            const toggleKey = ITEM_COLUMN_TOGGLES[this.id];
                            if (toggleKey) applyLocalToggle(toggleKey, this.checked);
                            markUnsaved();
                        });
                    });

                    // Invoice Details / Party Details accordions already use the exact
                    // same key strings as the templates' data-toggle attributes.
                    document.querySelectorAll(
                        '#collapseInvoiceDetails input[type="checkbox"][data-invoice-detail],' +
                        '#collapsePartyDetails input[type="checkbox"][data-party-detail]'
                    ).forEach(function(checkbox) {
                        checkbox.addEventListener('change', function() {
                            const toggleKey = this.dataset.invoiceDetail || this.dataset.partyDetail;
                            applyLocalToggle(toggleKey, this.checked);
                            markUnsaved();
                        });
                    });

                    // Miscellaneous Details has no backing column in any template
                    // (see memory note) — nothing to patch locally, just tracks unsaved.
                    document.querySelectorAll('#collapseMisc input[type="checkbox"]').forEach(function(checkbox) {
                        checkbox.addEventListener('change', markUnsaved);
                    });

                    document.getElementById('saveSettingsBtn').addEventListener('click', function() {
                        saveSettings();
                        updatePreview();
                    });
                });

                function markUnsaved() {
                    document.getElementById('unsaved-indicator').style.display = '';
                    document.getElementById('saved-indicator').style.display = 'none';
                }

                function markSaved() {
                    document.getElementById('unsaved-indicator').style.display = 'none';
                    document.getElementById('saved-indicator').style.display = '';
                }

                // ── Client-side preview patching (zero network requests) ──────────
                // Every optional block in the 4 real templates is always present in
                // the DOM, tagged data-toggle="<key>", hidden via inline display:none
                // when its setting is off. So toggling a checkbox just flips that
                // inline style directly on the already-loaded iframe — no re-fetch.
                function getPreviewDoc() {
                    const frame = document.getElementById('preview-frame');
                    return frame && frame.contentDocument ? frame.contentDocument : null;
                }

                function applyLocalToggle(key, visible) {
                    const doc = getPreviewDoc();
                    if (!doc) return;
                    doc.querySelectorAll('[data-toggle="' + key + '"]').forEach(function(el) {
                        el.style.display = visible ? '' : 'none';
                    });
                }

                function applyLocalColor(color) {
                    const doc = getPreviewDoc();
                    if (!doc) return;
                    const styleEl = doc.getElementById('dynamic-color-style');
                    const templateEl = doc.getElementById('color-css-template');
                    if (!styleEl || !templateEl) return;
                    const template = JSON.parse(templateEl.textContent);
                    styleEl.textContent = template.split('__COLOR__').join(color);
                }

                // Maps the "Display Options" checkboxes' camelCase data-key to the
                // snake_case key the templates use for data-toggle.
                const DATA_KEY_TO_TOGGLE = {
                    partyBalance: 'party_balance',
                    itemDescription: 'item_description',
                    showPhone: 'show_phone',
                    showTime: 'show_time',
                    // freeItemQty / altUnit have no backing column in this app's
                    // invoice schema, so there's nothing in the template to toggle.
                };

                // Maps the "Item Table Columns" checkboxes to their data-toggle key.
                const ITEM_COLUMN_TOGGLES = {
                    colHsn: 'hsn_sac',
                    colUnit: 'unit',
                    colRate: 'rate',
                    colDiscount: 'discount',
                };

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

                // Gathers the full current form state, shared by both the live
                // preview request and the persisted save request.
                function gatherFormState() {
                    let theme = 'stylish';
                    document.querySelectorAll('.theme.active').forEach(function(t) { theme = t.dataset.theme; });

                    let color = '#0b6aa0';
                    document.querySelectorAll('.color-show.selected').forEach(function(c) { color = c.dataset.color; });

                    const invoiceDetails = {};
                    document.querySelectorAll('#collapseInvoiceDetails input[type="checkbox"][data-invoice-detail]').forEach(function(checkbox) {
                        invoiceDetails[checkbox.dataset.invoiceDetail] = checkbox.checked;
                    });

                    const partyDetails = {};
                    document.querySelectorAll('#collapsePartyDetails input[type="checkbox"][data-party-detail]').forEach(function(checkbox) {
                        partyDetails[checkbox.dataset.partyDetail] = checkbox.checked;
                    });

                    const miscDetails = {};
                    document.querySelectorAll('#collapseMisc input[type="checkbox"][data-misc-detail]').forEach(function(checkbox) {
                        miscDetails[checkbox.dataset.miscDetail] = checkbox.checked;
                    });

                    return {
                        theme: theme,
                        primary_color: color,
                        party_balance: document.getElementById('togglePartyBalance').checked ? 1 : 0,
                        free_item_qty: document.getElementById('toggleFreeItem').checked ? 1 : 0,
                        item_description: document.getElementById('toggleItemDesc').checked ? 1 : 0,
                        alt_unit: document.getElementById('toggleAltUnit').checked ? 1 : 0,
                        show_phone: document.getElementById('toggleShowPhone').checked ? 1 : 0,
                        show_time: document.getElementById('toggleShowTime').checked ? 1 : 0,
                        awb_hbl: document.getElementById('colAwb').checked ? 1 : 0,
                        incoterm: document.getElementById('colIncoterm').checked ? 1 : 0,
                        pol_pod: document.getElementById('colPolPod').checked ? 1 : 0,
                        voyage_flight: document.getElementById('colVoyage').checked ? 1 : 0,
                        shipment_mode: document.getElementById('colShipMode').checked ? 1 : 0,
                        carrier: document.getElementById('colCarrier').checked ? 1 : 0,
                        hsn_sac: document.getElementById('colHsn').checked ? 1 : 0,
                        unit: document.getElementById('colUnit').checked ? 1 : 0,
                        rate: document.getElementById('colRate').checked ? 1 : 0,
                        discount: document.getElementById('colDiscount').checked ? 1 : 0,
                        invoice_details: invoiceDetails,
                        party_details: partyDetails,
                        misc_details: miscDetails,
                        _token: '{{ csrf_token() }}'
                    };
                }

                // Renders the ACTUAL template (chosen theme + current toggles) against
                // a real invoice via the server, so the preview pane matches the real print/PDF output.
                let previewRequestSeq = 0;
                function updatePreview() {
                    const seq = ++previewRequestSeq;
                    const loadingEl = document.getElementById('preview-loading');
                    if (loadingEl) loadingEl.style.display = '';

                    fetch('{{ route("settings.invoice.preview") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(gatherFormState())
                    })
                    .then(response => response.text())
                    .then(html => {
                        if (seq !== previewRequestSeq) return; // a newer request has since started
                        document.getElementById('preview-frame').srcdoc = html;
                        if (loadingEl) loadingEl.style.display = 'none';
                    })
                    .catch(error => {
                        console.error('Error rendering preview:', error);
                        if (loadingEl) loadingEl.style.display = 'none';
                    });
                }

                function saveSettings() {
                    const btn = document.getElementById('saveSettingsBtn');
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

                    fetch('{{ route("settings.invoice.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(gatherFormState())
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            markSaved();
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
                            console.error('Error saving settings:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error saving settings:', error);
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-save me-1"></i> Save';
                    });
                }
            </script>
        </section>
    </main>
</x-app-layout>
