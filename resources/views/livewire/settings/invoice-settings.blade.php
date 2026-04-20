<div>
    <div class="row">
        <!-- Left side - Preview -->
        <div class="col-md-6 border-end">
            <div class="p-3">
                <h5 class="fw-bold mb-3 text-primary d-flex align-items-center"><i class="bi bi-eye me-2"></i> Invoice Preview</h5>
                <div id="invoice-preview" class="border rounded p-3" style="height: 75vh; overflow-y: auto;">
                    <!-- Include the actual print.blade.php template for the preview -->
                    <div class="invoice-wrapper vh-100">
                        @if($customerInvoice->status == 1)
                            <div class="draft-watermark">DRAFT</div>
                        @endif
                        <div class="border border-dark h-100 border-h-100-relative">
                            <div class="header-top border-bottom border-dark p-2">
                                <div class="logo-box">
                                    @if($company->logo_path)
                                        <img src="{{ asset($company->logo_path) }}" alt="Company Logo">
                                    @else
                                        COMPANY LOGO
                                    @endif
                                </div>
                                <div class="address-box">
                                    <h6 class="mb-0 pb-0 fw-bold">{{ $company->name }}</h6>
                                    <h6 class="mb-0 arabic-name">{{ $company->name_ar }}</h6>
                                    <p class="fw-bold">{{ $company->address_1 }}</p>
                                    <p class="fw-bold">{{ $company->city }} {{ $company->postal_code }}
                                        , {{ $company->city_sub_division }}</p>
                                    <p class="fw-bold">SAUDI ARABIA</p>
                                    @if($company->tax_number)
                                        <p class="fw-bold">CR NO: {{ $company->cr_number }}, VAT NO: {{ $company->tax_number }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="invoice-title-row border-bottom border-dark">
                                <h2 class="english-title text-center">TAX INVOICE</h2>
                                <h2 class="arabic-title text-center">فاتورة ضريبية</h2>
                            </div>

                            <div class="details-section">
                                <div class="customer-address-split border-bottom border-dark">
                                    <div class="customer-address-english customer-split-flex">
                                        <div class="customer-label-col">
                                            <p class="customer-name-bold mb-0 px-1">Customer:</p>
                                        </div>
                                        <div class="customer-details-col">
                                            <p class="customer-name-bold text-uppercase">{{ $customer->name_en }}</p>
                                            <p class="text-uppercase">{{ $customer->address1_en }}</p>
                                            <p class="text-uppercase">{{ $customer->city_en }}
                                                , {{ $customer->country_en }}
                                                , {{ $customer->postal_code }}</p>
                                            <p>Phone: {{ $customer->phone }}</p>
                                            <p style="font-weight: 700;">VAT No.: {{ $customer->vat_number }}
                                                /{{ $customer->cr_number }}</p>
                                            <p>Credit Term: CASH</p>
                                        </div>
                                    </div>

                                    <div class="customer-address-arabic customer-split-flex">
                                        <div class="customer-details-col">
                                            <p class="customer-name-bold">{{ $customer->name_ar }}</p>
                                            <p>{{ $customer->address1_ar }}</p>
                                            <p>{{ $customer->city_ar }}, العربية
                                                السعودية, {{ $customer->postal_code }}</p>
                                            <p>{{ $customer->phone }} هاتف: </p>
                                            <p>رقم ضريبة القيمة المضافة للعميل: {{ $customer->vat_number }}
                                                /{{ $customer->cr_number }}</p>
                                        </div>
                                        <div class="customer-label-col">
                                            <p class="mb-0">العميل:</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="border-bottom border-dark">
                                    <div class="metadata-grid-row">
                                        <div class="label-english">Customer VAT No.:</div>
                                        <div class="data-value">{{ $customer->vat_number }}
                                            /{{ $customer->cr_number }}</div>
                                        <div class="label-arabic">رقم ضريبة القيمة المضافة للعميل:</div>
                                    </div>
                                    <div class="metadata-grid-row">
                                        <div class="label-english">Invoice No.:</div>
                                        <div class="data-value">{{ $customerInvoice->row_no }}</div>
                                        <div class="label-arabic">رقم الفاتورة:</div>
                                    </div>
                                    <div class="metadata-grid-row">
                                        <div class="label-english">Invoice Date:</div>
                                        <div
                                            class="data-value text-uppercase">{{ \Carbon\Carbon::parse($customerInvoice->invoice_date)->format('d-M-y') }}
                                        </div>
                                        <div class="label-arabic">تاريخ:</div>
                                    </div>
                                    <div class="metadata-grid-row">
                                        <div class="label-english">Payment Due Date:</div>
                                        <div
                                            class="data-value text-uppercase">{{ \Carbon\Carbon::parse($customerInvoice->due_date)->format('d-M-y') }}</div>
                                        <div class="label-arabic">تاريخ الاستحقاق للدفع:</div>
                                    </div>
                                </div>
                            </div>

                            <div class="shipping-job-section">
                                <table>
                                    @if($this->invoiceDetails['shipper'] ?? false)
                                    <tr>
                                        <td class="label-col">Shipper</td>
                                        <td class="separator-col">:</td>
                                        <td class="data-col">{{ $job->shipper }}</td>
                                    </tr>
                                    @endif
                                    @if($this->invoiceDetails['consignee'] ?? false)
                                    <tr>
                                        <td class="label-col">Consignee</td>
                                        <td class="separator-col">:</td>
                                        <td class="data-col">{{ $job->consignee }}</td>
                                    </tr>
                                    @endif
                                    @if($this->awbHbl)
                                    <tr>
                                        <td class="label-col">HBL No</td>
                                        <td class="separator-col">:</td>
                                        <td class="data-col">{{ $job->hbl_no }}</td>
                                    </tr>
                                    @endif
                                    @if($this->polPod)
                                    <tr>
                                        <td class="label-col">Place of Origin</td>
                                        <td class="separator-col">:</td>
                                        <td class="data-col">{{ $job->pol }}</td>
                                    </tr>
                                    <tr>
                                        <td class="label-col">Final destination</td>
                                        <td class="separator-col">:</td>
                                        <td class="data-col">{{ $job->pod }}</td>
                                    </tr>
                                    @endif
                                    @if($this->carrier)
                                    <tr>
                                        <td class="label-col">Vessel / Flight</td>
                                        <td class="separator-col">:</td>
                                        <td class="data-col">{{ $job->carrier }}</td>
                                    </tr>
                                    @endif
                                    @if($this->voyageFlight)
                                    <tr>
                                        <td class="label-col">Voyage/Flight No.</td>
                                        <td class="separator-col">:</td>
                                        <td class="data-col">{{ $job->voyage_flight_no }}</td>
                                    </tr>
                                    @endif
                                    @if($this->invoiceDetails['shipping_ref'] ?? false)
                                    <tr>
                                        <td class="label-col">Shipper Ref No.</td>
                                        <td class="separator-col">:</td>
                                        <td class="data-col">{{ $job->shipping_ref }}</td>
                                    </tr>
                                    @endif
                                    @if($this->invoiceDetails['customer_po'] ?? false)
                                    <tr>
                                        <td class="label-col">Customer P/O No.</td>
                                        <td class="separator-col">:</td>
                                        <td class="data-col"></td>
                                    </tr>
                                    @endif
                                    @if($this->invoiceDetails['remarks'] ?? false)
                                    <tr>
                                        <td class="label-col">Remarks</td>
                                        <td class="separator-col">:</td>
                                        <td class="data-col">{{ $job->remarks }}</td>
                                    </tr>
                                    @endif
                                </table>

                                <table>
                                    @if($this->invoiceDetails['job_number'] ?? false)
                                    <tr>
                                        <td class="label-col">Job Number</td>
                                        <td class="separator-col">:</td>
                                        <td class="data-col">{{ $job->row_no }}
                                            / {{ \Carbon\Carbon::parse($job->posted_at)->format('d-M-y') }}</td>
                                    </tr>
                                    @endif
                                    @if($this->invoiceDetails['job_date'] ?? false)
                                    <tr>
                                        <td class="label-col">Job Date</td>
                                        <td class="separator-col">:</td>
                                        <td class="data-col text-uppercase">{{ \Carbon\Carbon::parse($job->posted_at)->format('d-M-y') }}</td>
                                    </tr>
                                    @endif
                                    @if($this->invoiceDetails['master_number'] ?? false)
                                    <tr>
                                        <td class="label-col">Master Number</td>
                                        <td class="separator-col">:</td>
                                        <td class="data-col text-uppercase">{{ $job->awb_no }}
                                            / {{ \Carbon\Carbon::parse($job->posted_at)->format('d-M-y') }}</td>
                                    </tr>
                                    @endif
                                    @if($this->invoiceDetails['house_no'] ?? false)
                                    <tr>
                                        <td class="label-col">House No.</td>
                                        <td class="separator-col">:</td>
                                        <td class="data-col"></td>
                                    </tr>
                                    @endif
                                    @if($this->invoiceDetails['no_of_pieces'] ?? false)
                                    <tr>
                                        <td class="label-col">Number of Packs</td>
                                        <td class="separator-col">:</td>
                                        <td class="data-col">{{ $job->no_of_pieces }}</td>
                                    </tr>
                                    @endif
                                    @if($this->invoiceDetails['weight'] ?? false)
                                    <tr>
                                        <td class="label-col">Weight in Kgs</td>
                                        <td class="separator-col">:</td>
                                        <td class="data-col">{{ $job->weight }}</td>
                                    </tr>
                                    @endif
                                    @if($this->invoiceDetails['volume'] ?? false)
                                    <tr>
                                        <td class="label-col">Volume in CBM</td>
                                        <td class="separator-col">:</td>
                                        <td class="data-col">{{ $job->volume }}</td>
                                    </tr>
                                    @endif
                                    @if($this->invoiceDetails['etd'] ?? false)
                                    <tr>
                                        <td class="label-col">ETD</td>
                                        <td class="separator-col">:</td>
                                        <td class="data-col text-uppercase">{{ \Carbon\Carbon::parse($job->etd)->format('d-M-y') }}</td>
                                    </tr>
                                    @endif
                                    @if($this->invoiceDetails['eta'] ?? false)
                                    <tr>
                                        <td class="label-col">ETA</td>
                                        <td class="separator-col">:</td>
                                        <td class="data-col text-uppercase">{{ \Carbon\Carbon::parse($job->eta)->format('d-M-y') }}</td>
                                    </tr>
                                    @endif
                                    @if($this->invoiceDetails['narration'] ?? false)
                                    <tr>
                                        <td class="label-col">Narration</td>
                                        <td class="separator-col">:</td>
                                        <td class="data-col"></td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                            <table class="charges-table">
                                <thead>
                                <tr>
                                    <th class="charge-desc">Charge Description <br> بيان الرسوم</th>
                                    <th style="width: 5%;">Curr. <br> العملة</th>
                                    @if($this->rate)
                                    <th style="width: 8%;">Rate Per Unit <br> السعر لكل وحدة</th>
                                    @endif
                                    @if($this->unit)
                                    <th style="width: 5%;">Unit <br> وحدة</th>
                                    @endif
                                    <th style="width: 10%;">Curr. Amount <br> مبلغ العملة</th>
                                    <th style="width: 5%;">/ROE <br> سعر الصرف</th>
                                    <th style="width: 10%;">Total Price excl. VAT <br> السعر الجمالي بدون الضريبة</th>
                                    <th style="width: 5%;">VAT% <br> الضربة %</th>
                                    <th style="width: 10%;">VAT Amount <br> قيمة الضريبة</th>
                                    <th style="width: 12%;">Total <br> المجموع {{ $customerInvoice->currency }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($customerInvoice->customerInvoiceSubs as $items)
                                    <tr>
                                        <td class="charge-desc text-uppercase">{{ $items->description }} @if($items->comment)
                                                - <span>{{ $items->comment }}</span>
                                            @endif</td>
                                        <td>{{ $customerInvoice->currency }}</td>
                                        @if($this->rate)
                                        <td>{{ number_format($items->unit_price, 2) }}</td>
                                        @endif
                                        @if($this->unit)
                                        <td>{{ $items->quantity }}</td>
                                        @endif
                                        <td>{{ number_format($items->total, 2) }}</td>
                                        <td>1.000000</td>
                                        <td>{{ number_format($items->total, 2) }}</td>
                                        <td>{{ $items->tax_percent }}</td>
                                        <td>{{ number_format($items->tax_amount, 2) }}</td>
                                        <td>{{ number_format($items->total_with_tax, 2) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>

                            <div class="page">
                                <div class="total-summary-section">
                                    <div class="total-summary-row">
                                        <div class="text-and-label-col">
                                            <div class="words-group">
                                                <div class="english-text">One thousand one hundred and fifty Saudi Riyals only</div>
                                                <div class="arabic-text">فقط ألف ومائة وخمسون ريالاً سعودياً لا غير</div>
                                            </div>
                                            <div class="label-group">
                                                <div class="english-text">Total in: {{ $customerInvoice->currency }}</div>
                                                <div class="arabic-text">الإجمالي بـ: ر.س</div>
                                            </div>
                                        </div>
                                        <div class="label-group col-total-excl-vat">
                                            <div class="english-text">{{ number_format($customerInvoice->sub_total, 2) }}</div>
                                            <div class="arabic-text">{{ $customerInvoice->sub_total }}</div>
                                        </div>
                                        <div class="col-vat-percent"></div>
                                        <div class="label-group col-vat-amount">
                                            <div class="english-text">{{ number_format($customerInvoice->tax_total, 2) }}</div>
                                            <div class="arabic-text">{{ $customerInvoice->tax_total }}</div>
                                        </div>
                                        <div class="label-group col-final-total">
                                            <div class="english-text">{{ number_format($customerInvoice->grand_total, 2) }}</div>
                                            <div class="arabic-text">{{ $customerInvoice->grand_total }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="footer-section px-2">
                                <div class="bank-details">
                                    <p style="font-weight: 700; margin-bottom: 5px;">Bank Details</p>
                                    <table style="font-size: 10px">
                                        <tr>
                                            <td>Account Name:</td>
                                            <td>{{ $bank->account_holder }}</td>
                                        </tr>
                                        <tr>
                                            <td>Account Name (Arabic):</td>
                                            <td>{{ $bank->account_holder_arabic }}</td>
                                        </tr>
                                        <tr>
                                            <td>Bank Name:</td>
                                            <td>{{ $bank->bank_name }}</td>
                                        </tr>
                                        <tr>
                                            <td>Account No:</td>
                                            <td>{{ $bank->account_number }}</td>
                                        </tr>
                                        <tr>
                                            <td>IBAN No:</td>
                                            <td>{{ $bank->iban_code }}</td>
                                        </tr>
                                        <tr>
                                            <td>Bank Address:</td>
                                            <td>{{ $bank->bank_address }}</td>
                                        </tr>
                                        <tr>
                                            <td>SWIFT Code:</td>
                                            <td>{{ $bank->swift_code }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div>
                                    <!-- QR code would go here -->
                                </div>
                            </div>

                            <div class="center-footer-text">
                                <p>This is a computer generated document and does not require a signature</p>
                                <p class="arabic">هذا المستند تم انتاجه بواسطة الكمبيوتر ولا يحتاج الى توقيع .</p>
                            </div>
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
                                <div class="theme active border border-primary p-2 rounded-3 text-center shadow-sm" data-theme="stylish" style="cursor:pointer; transform: translateY(-3px);" wire:click="$set('theme', 'stylish')">
                                    <div class="image-container" style="height: 80px; display:flex; align-items:center; justify-content:center;">
                                        <img src="https://mybillbook.in/app//assets/images/themes/stylish.svg" alt="Stylish" class="img-fluid rounded" style="max-height:100%;">
                                    </div>
                                    <div class="theme-name small fw-medium mt-2 text-primary">Stylish</div>
                                </div>
                            </div>

                            <div class="theme-container" style="width: 140px;">
                                <div class="theme border p-2 rounded-3 text-center" data-theme="luxury" style="cursor:pointer;" wire:click="$set('theme', 'luxury')">
                                    <div class="image-container" style="height: 80px; display:flex; align-items:center; justify-content:center;">
                                        <img src="https://mybillbook.in/app//assets/images/themes/luxury-theme.svg" alt="Luxury" class="img-fluid rounded" style="max-height:100%;">
                                    </div>
                                    <div class="theme-name small fw-medium mt-2">Luxury</div>
                                </div>
                            </div>

                            <div class="theme-container" style="width: 140px;">
                                <div class="theme border p-2 rounded-3 text-center" data-theme="advance-gst-tally" style="cursor:pointer;" wire:click="$set('theme', 'advance-gst-tally')">
                                    <div class="image-container" style="height: 80px; display:flex; align-items:center; justify-content:center;">
                                        <img src="https://mybillbook.in/app//assets/images/themes/advance-gst-tally.svg" alt="Advanced GST (Tally)" class="img-fluid rounded" style="max-height:100%;">
                                    </div>
                                    <div class="theme-name small fw-medium mt-2">Advanced GST (Tally)</div>
                                </div>
                            </div>

                            <div class="theme-container" style="width: 140px;">
                                <div class="theme border p-2 rounded-3 text-center" data-theme="billbook" style="cursor:pointer;" wire:click="$set('theme', 'billbook')">
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
                            <div class="color-show rounded-circle border border-dark border-opacity-25 selected" data-color="#0b6aa0" style="background:#0b6aa0; width: 32px; height: 32px; cursor:pointer; outline: 3px solid rgba(13,110,253,0.3);" wire:click="$set('primaryColor', '#0b6aa0')"></div>
                            <div class="color-show rounded-circle border border-dark border-opacity-25" data-color="#000000" style="background:#000; width: 32px; height: 32px; cursor:pointer;" wire:click="$set('primaryColor', '#000000')"></div>
                            <div class="color-show rounded-circle border border-dark border-opacity-25" data-color="#407400" style="background:#407400; width: 32px; height: 32px; cursor:pointer;" wire:click="$set('primaryColor', '#407400')"></div>
                            <div class="color-show rounded-circle border border-dark border-opacity-25" data-color="#840bb2" style="background:#840bb2; width: 32px; height: 32px; cursor:pointer;" wire:click="$set('primaryColor', '#840bb2')"></div>
                            <div class="color-show rounded-circle border border-dark border-opacity-25" data-color="#c11111" style="background:#c11111; width: 32px; height: 32px; cursor:pointer;" wire:click="$set('primaryColor', '#c11111')"></div>
                            <div class="color-show rounded-circle border border-dark border-opacity-25" data-color="#5b57ae" style="background:#5b57ae; width: 32px; height: 32px; cursor:pointer;" wire:click="$set('primaryColor', '#5b57ae')"></div>
                            <div class="color-show rounded-circle border border-dark border-opacity-25" data-color="#cd9d23" style="background:#cd9d23; width: 32px; height: 32px; cursor:pointer;" wire:click="$set('primaryColor', '#cd9d23')"></div>
                            <div class="color-show rounded-circle border border-dark border-opacity-25" data-color="#bf6200" style="background:#bf6200; width: 32px; height: 32px; cursor:pointer;" wire:click="$set('primaryColor', '#bf6200')"></div>
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
                                    <input class="form-check-input" type="checkbox" role="switch" id="togglePartyBalance" data-key="partyBalance" wire:model="partyBalance">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <label class="fw-medium mb-0">Enable free item quantity</label>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="toggleFreeItem" data-key="freeItemQty" wire:model="freeItemQty">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <label class="fw-medium mb-0">Show item description in invoice</label>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="toggleItemDesc" data-key="itemDescription" wire:model="itemDescription">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <label class="fw-medium mb-0">Show Alternate Unit in Invoice</label>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="toggleAltUnit" data-key="altUnit" wire:model="altUnit">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <label class="fw-medium mb-0">Show phone number on Invoice</label>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="toggleShowPhone" data-key="showPhone" wire:model="showPhone">
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
                                    <input class="form-check-input" type="checkbox" role="switch" id="toggleShowTime" data-key="showTime" wire:model="showTime">
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
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colAwb" data-invoice-detail="awb_hbl" wire:model="awbHbl"><label class="form-check-label">AWB / HBL No</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colIncoterm" data-invoice-detail="incoterm" wire:model="incoterm"><label class="form-check-label">Incoterm</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colPolPod" data-invoice-detail="pol_pod" wire:model="polPod"><label class="form-check-label">POL / POD</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colVoyage" data-invoice-detail="voyage_flight" wire:model="voyageFlight"><label class="form-check-label">Voyage / Flight No</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colShipMode" data-invoice-detail="shipment_mode" wire:model="shipmentMode"><label class="form-check-label">Shipment Mode</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colCarrier" data-invoice-detail="carrier" wire:model="carrier"><label class="form-check-label">Carrier</label></div></div>

                                    <!-- Additional job columns -->
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colJobNumber" wire:model="invoiceDetails.job_number"><label class="form-check-label">Job Number</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colRefNumber" wire:model="invoiceDetails.reference_number"><label class="form-check-label">Reference Number</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colActivity" wire:model="invoiceDetails.activity"><label class="form-check-label">Logistics Activity</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colShipCategory" wire:model="invoiceDetails.shipment_category"><label class="form-check-label">Shipment Category</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colPlaceReceipt" wire:model="invoiceDetails.place_of_receipt"><label class="form-check-label">Place of Receipt</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colPlaceDelivery" wire:model="invoiceDetails.place_of_delivery"><label class="form-check-label">Place of Delivery</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colFinalDest" wire:model="invoiceDetails.final_destination"><label class="form-check-label">Final Destination</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colCommodity" wire:model="invoiceDetails.commodity"><label class="form-check-label">Commodity</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colPickupDate" wire:model="invoiceDetails.pickup_date"><label class="form-check-label">Pickup Date</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colDeliveryDate" wire:model="invoiceDetails.delivery_date"><label class="form-check-label">Delivery Date</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colEta" wire:model="invoiceDetails.eta"><label class="form-check-label">ETA</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="colEtd" wire:model="invoiceDetails.etd"><label class="form-check-label">ETD</label></div></div>
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
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyCode" wire:model="partyDetails.code"><label class="form-check-label">Customer Code</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyUniqueCode" wire:model="partyDetails.unique_code"><label class="form-check-label">Unique Code</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyBusinessType" wire:model="partyDetails.business_type"><label class="form-check-label">Business Type</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyCrNumber" wire:model="partyDetails.cr_number"><label class="form-check-label">CR Number</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyVatNumber" wire:model="partyDetails.vat_number"><label class="form-check-label">VAT Number</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyCreditLimit" wire:model="partyDetails.credit_limit"><label class="form-check-label">Credit Limit</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyCreditDays" wire:model="partyDetails.credit_days"><label class="form-check-label">Credit Days</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyRegion" wire:model="partyDetails.region"><label class="form-check-label">Region</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyPostalCode" wire:model="partyDetails.postal_code"><label class="form-check-label">Postal Code</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyCountry" wire:model="partyDetails.country"><label class="form-check-label">Country</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyEmail" wire:model="partyDetails.email"><label class="form-check-label">Email</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyAltPhone" wire:model="partyDetails.alt_phone"><label class="form-check-label">Alternative Phone</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyPreferredShipping" wire:model="partyDetails.preferred_shipping"><label class="form-check-label">Preferred Shipping</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyPreferredCarrier" wire:model="partyDetails.preferred_carrier"><label class="form-check-label">Preferred Carrier</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyDefaultPort" wire:model="partyDetails.default_port"><label class="form-check-label">Default Port</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyPaymentMethod" wire:model="partyDetails.payment_method"><label class="form-check-label">Payment Method</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyIban" wire:model="partyDetails.iban"><label class="form-check-label">IBAN</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partyPaymentTerms" wire:model="partyDetails.payment_terms"><label class="form-check-label">Payment Terms</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="partySalesperson" wire:model="partyDetails.salesperson"><label class="form-check-label">Salesperson</label></div></div>
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
                                            <input class="form-check-input" type="checkbox" value="" id="colHsn" wire:model="hsnSac">
                                            <label class="form-check-label" for="colHsn">HSN / SAC</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="" id="colUnit" wire:model="unit">
                                            <label class="form-check-label" for="colUnit">Unit</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="" id="colRate" wire:model="rate">
                                            <label class="form-check-label" for="colRate">Rate</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" value="" id="colDiscount" wire:model="discount">
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
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="miscInvoiceNotes" wire:model="miscDetails.invoice_notes"><label class="form-check-label">Invoice Notes</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="miscTermsConditions" wire:model="miscDetails.terms_conditions"><label class="form-check-label">Terms & Conditions</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="miscPaymentInstructions" wire:model="miscDetails.payment_instructions"><label class="form-check-label">Payment Instructions</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="miscDeliveryInstructions" wire:model="miscDetails.delivery_instructions"><label class="form-check-label">Delivery Instructions</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="miscHandlingInstructions" wire:model="miscDetails.handling_instructions"><label class="form-check-label">Special Handling</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="miscAdditionalContacts" wire:model="miscDetails.additional_contacts"><label class="form-check-label">Additional Contacts</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="miscReferenceNumbers" wire:model="miscDetails.reference_numbers"><label class="form-check-label">Reference Numbers</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="miscAttachments" wire:model="miscDetails.attachments"><label class="form-check-label">Attachments</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="miscPackingSlip" wire:model="miscDetails.packing_slip"><label class="form-check-label">Packing Slip</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="miscTransportInfo" wire:model="miscDetails.transport_info"><label class="form-check-label">Transport Information</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="miscCustomsInfo" wire:model="miscDetails.customs_info"><label class="form-check-label">Customs Information</label></div></div>
                                    <div class="col-md-4 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" id="miscInsuranceInfo" wire:model="miscDetails.insurance_info"><label class="form-check-label">Insurance Information</label></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast for success/error messages -->
    <div id="settings-toast" class="position-fixed bottom-0 end-0 p-3" style="z-index: 5; display: none;">
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header" id="toast-header">
                <strong class="me-auto" id="toast-title">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toast-message">
                Settings updated successfully
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            // Listen for settings-saved event
            @this.on('settings-saved', (data) => {
                const toast = document.getElementById('settings-toast');
                const toastHeader = document.getElementById('toast-header');
                const toastTitle = document.getElementById('toast-title');
                const toastMessage = document.getElementById('toast-message');

                toastHeader.classList.add('bg-success');
                toastHeader.classList.add('text-white');
                toastHeader.classList.remove('bg-danger');
                toastTitle.innerText = 'Success';
                toastMessage.innerText = data.message;

                toast.style.display = 'block';
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 3000);
            });

            // Listen for settings-error event
            @this.on('settings-error', (data) => {
                const toast = document.getElementById('settings-toast');
                const toastHeader = document.getElementById('toast-header');
                const toastTitle = document.getElementById('toast-title');
                const toastMessage = document.getElementById('toast-message');

                toastHeader.classList.add('bg-danger');
                toastHeader.classList.add('text-white');
                toastHeader.classList.remove('bg-success');
                toastTitle.innerText = 'Error';
                toastMessage.innerText = data.message;

                toast.style.display = 'block';
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 3000);
            });
        });
    </script>
</div>
