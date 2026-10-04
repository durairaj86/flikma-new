@section('js','company')
@section('hide-topbar', true)
@section('page-title', __('Manage Business'))
<x-app-layout>
    <main class="gmail-content bg-white d-flex ">
        @include('includes.settings-navigation')
        <section class="flex-grow-1 d-flex flex-column">
            <div class="company-setup-page py-5 border-top">
                <div class="container " style="max-width: 1000px;">
                    {{--<h2 class="fw-bolder mb-1 text-primary">Business Profile Setup</h2>
                    <p class="text-muted mb-5">
                        Manage your company's core information, contact details, and compliance data used on all documents and invoices.
                    </p>--}}

                    <form id="company-form" class="was-validated" action="/settings/company" method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="row g-5">

                            <div class="col-12">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-light fw-bold">{{ __('1. Identity & Branding') }}</div>
                                    <div class="card-body">
                                        <div class="row g-4 align-items-center">

                                            <div class="col-md-3 d-flex flex-column align-items-center">
                                                <label class="form-label text-center w-100">{{ __('Business Logo') }}</label>
                                                <div
                                                    class="upload-box text-center p-3 rounded-3 border border-dashed w-100"
                                                    id="logoUploadBox" style="cursor: pointer; min-height: 120px;">
                                                    <input type="file" id="logoInput" name="logoInput" class="d-none"
                                                           accept="image/png, image/jpeg">
                                                    <img id="logoPreview"
                                                         src="{{ $company->logo ? asset('storage/'.$company->logo) : '' }}"
                                                         alt="{{ __('Logo Preview') }}"
                                                         class="img-fluid mb-2 rounded @if(!$company->logo) d-none @endif">
                                                    <div class="upload-text">
                                                        <i class="bi bi-cloud-arrow-up text-primary fs-4"></i>
                                                        <p class="mb-0 small text-muted">{{ __('Upload Logo') }}</p>
                                                        <small class="text-secondary" style="font-size: 0.75rem;">{{ __('PNG / JPG - Max 5MB') }}</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-9">
                                                <div class="mb-3">
                                                    <label for="business_name_en" class="form-label">{{ __('Business Name (In English)') }}<span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="business_name_en"
                                                           name="business_name_en" value="{{ $company->name }}"
                                                           placeholder="{{ __('Enter your official business name') }}" required>
                                                </div>

                                                <div class="text-end">
                                                    <label for="business_name_ar" class="form-label">{{ __('Business Name (In Arabic)') }}<span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control text-end"
                                                           id="business_name_ar" name="business_name_ar"
                                                           value="{{ $company->name_ar }}"
                                                           placeholder="{{ __('Enter your official business name') }}" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="card h-100 shadow-sm border-0">
                                    <div class="card-header bg-light fw-bold">{{ __('2. Contact & Location') }}</div>
                                    <div class="card-body">
                                        <div class="row g-3">

                                            <div class="col-md-6">
                                                <label for="companyEmail" class="form-label">{{ __('Company Email') }}<span
                                                        class="text-danger">*</span></label>
                                                <input type="email" class="form-control" id="companyEmail" required
                                                       value="{{ $company->email }}"
                                                       name="companyEmail" placeholder="info@company.com">
                                            </div>

                                            <div class="col-md-6">
                                                <label for="companyPhone" class="form-label">{{ __('Company Phone') }}<span
                                                        class="text-danger">*</span></label>
                                                <input type="tel" class="form-control" id="companyPhone" required
                                                       value="{{ $company->phone }}"
                                                       name="companyPhone" placeholder="+91 98765 43210">
                                            </div>

                                            <div class="col-6 pb-0 mb-0">
                                                <label for="address_en" class="form-label">{{ __('Company Address (In English)') }}<span
                                                        class="text-danger">*</span></label>
                                                <textarea class="form-control h-50" id="address_en" required
                                                          name="address_en" rows="4"
                                                          placeholder="{{ __('Street, Building name, etc.') }}">{{ $company->address }}</textarea>
                                            </div>

                                            <div class="col-6 pb-0 mb-0 text-end">
                                                <label for="address_ar" class="form-label">{{ __('Company Address (In Arabic)') }}<span
                                                        class="text-danger">*</span></label>
                                                <textarea class="form-control h-50 text-end" id="address_ar" required
                                                          name="address_ar" rows="4"
                                                          placeholder="{{ __('Street, Building name, etc.') }}">{{ $company->address_ar }}</textarea>
                                            </div>

                                            <div class="col-md-6 pt-0 mt-0">
                                                <label for="city" class="form-label">{{ __('City (In English)') }}<span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="city_en" name="city_en"
                                                       required value="{{ $company->city }}">
                                            </div>

                                            <div class="col-md-6 pt-0 mt-0 text-end">
                                                <label for="city" class="form-label">{{ __('City (In Arabic)') }}<span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control text-end" id="city_ar"
                                                       name="city_ar" required value="{{ $company->city_ar }}">
                                            </div>

                                            <div class="col-6">
                                                <label for="city_sub_division" class="form-label">{{ __('City Sub Division') }}</label>
                                                <input type="text" class="form-control" id="city_sub_division" name="city_sub_division"
                                                       value="{{ $company->city_sub_division }}">
                                            </div>

                                            <div class="col-md-6">
                                                <label for="pincode" class="form-label">{{ __('Pincode') }}<span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="pincode" name="pincode"
                                                       required value="{{ $company->postal_code }}">
                                            </div>

                                <div class="col-md-6">
                                                <label for="country" class="form-label">{{ __('Country') }}<span class="text-danger">*</span></label>
                                                @php
                                                    $cmpCountry = $company->country;
                                                    $cmpCountry = countries()[$cmpCountry] ?? $cmpCountry; // old rows may hold the 2-letter code
                                                @endphp
                                                @if($cmpCountry)
                                                    {{-- Chosen once: locked afterwards (ZATCA / tax setup depends on it). --}}
                                                    <select id="country" class="tom-select" disabled>
                                                        <option value="{{ $cmpCountry }}" selected>{{ $cmpCountry }}</option>
                                                    </select>
                                                    <div class="form-text"><i class="bi bi-lock-fill me-1"></i>{{ __('Country cannot be changed once saved.') }}</div>
                                                @else
                                                    <select id="country" name="country" class="tom-select" data-live-search="true" required>
                                                        <option value="">{{ __('Select country') }}</option>
                                                        @foreach(countries() as $cName)
                                                            <option value="{{ $cName }}" @selected($cName === 'Saudi Arabia')>{{ $cName }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="form-text">{{ __('Choose carefully: the country cannot be changed after saving.') }}</div>
                                                @endif
                                            </div>

                                            <div class="col-md-3">
                                                <label for="building_number" class="form-label">{{ __('Building No') }}</label>
                                                <input type="text" class="form-control" id="building_number" name="building_number" maxlength="20"
                                                       value="{{ $company->building_number }}" placeholder="1234">
                                            </div>

                                            <div class="col-md-3">
                                                <label for="plot_no" class="form-label">{{ __('Plot No') }}</label>
                                                <input type="text" class="form-control" id="plot_no" name="plot_no" maxlength="20"
                                                       value="{{ $company->plot_no }}">
                                            </div>

                                            <div class="col-md-6">
                                                <label for="timezone" class="form-label">{{ __('Time Zone') }}</label>
                                                <select class="tom-select" id="timezone" name="timezone"
                                                        data-live-search="true">
                                                    @foreach(\DateTimeZone::listIdentifiers() as $tz)
                                                        <option value="{{ $tz }}" @selected($company->timezone === $tz)>{{ $tz }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="card h-100 shadow-sm border-0">
                                    <div class="card-header bg-light fw-bold">{{ __('3. Business Type & Registration') }}</div>
                                    <div class="card-body">
                                        <div class="row g-3">

                                            <div class="col-md-6">
                                                <label for="businessType" class="form-label">{{ __('Business Type') }}</label>
                                                <select class="tom-select" id="businessType" name="businessType[]"
                                                        data-live-search="true"
                                                        multiple placeholder="{{ __('Business Type Selection') }}">
                                                    @foreach(industrialTypes() as $typeValue => $typeName)
                                                        <option
                                                            value="{{ $typeValue }}" @selected(in_array($typeValue, $company->business_type ?? []))>{{ $typeName }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            @if($company->vat_status == 1)
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">{{ __('Are you VAT Registered?') }}</label>
                                                    <select id="vat_status" class="tom-select" disabled>
                                                        <option value="1" selected>{{ __('Yes') }}</option>
                                                    </select>
                                                    <div class="form-text"><i class="bi bi-lock-fill me-1"></i>{{ __('VAT registration, VAT number and CR number are locked once saved.') }}</div>
                                                </div>

                                                <div class="row g-3 px-0 mx-0 vat-compliance-group">

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-medium">{{ __('VAT Number (TRN)') }}</label>
                                                        <input type="text" class="form-control" disabled
                                                               value="{{ $company->vat_number }}">
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-medium">{{ __('Commercial Registration (CR) Number') }}</label>
                                                        <input type="text" class="form-control" disabled
                                                               value="{{ $company->cr_number }}">
                                                    </div>
                                                </div>
                                            @else
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">{{ __('Are you VAT Registered?') }}</label>
                                                    <select id="vat_status" name="vat_status" class="tom-select">

                                                        <option value="0" selected>{{ __('No') }}</option>
                                                        <option value="1">{{ __('Yes') }}</option>
                                                    </select>
                                                    <div class="form-text">{{ __('Choosing Yes makes the VAT and CR numbers mandatory. It cannot be changed after saving.') }}</div>
                                                </div>

                                                <div class="row g-3 px-0 mx-0 vat-compliance-group">

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-medium">{{ __('VAT Number (TRN)') }} <span class="text-danger vat-req d-none">*</span></label>
                                                        <input type="text" class="form-control" id="vatNumber"
                                                               name="vatNumber" placeholder="300XXXXXXXXXXX"
                                                               value="{{ $company->vat_number }}">
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-medium">{{ __('Commercial Registration (CR) Number') }} <span class="text-danger vat-req d-none">*</span></label>
                                                        <input type="text" class="form-control"
                                                               id="crNumber" name="crNumber"
                                                               value="{{ $company->cr_number }}"
                                                               placeholder="1234567890">
                                                    </div>
                                                </div>
                                            @endif


                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-light fw-bold">{{ __('4. Invoice Footer Details') }}</div>
                                    <div class="card-body">
                                        <div class="row g-4">
                                            <div class="col-12">
                                                <div class="alert alert-secondary py-2 small">
                                                    <i class="bi bi-info-circle me-2"></i>
                                                    {{ __('**Note:** The signature and any terms/conditions added here will appear on your final invoices.') }}
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label">{{ __('Authorized Signature') }}</label>
                                                <div
                                                    class="signature-box text-center p-3 rounded-3 border border-dashed"
                                                    id="signatureUploadBox" style="cursor: pointer; min-height: 120px;">
                                                    <input type="file" id="signatureInput" name="signatureInput" hidden
                                                           accept="image/*">
                                                    <img id="signaturePreview"
                                                         src="{{ $company->signature_path ? asset('storage/'.$company->signature_path) : '' }}"
                                                         alt="{{ __('Signature Preview') }}"
                                                         class="img-fluid mb-2 rounded @if(!$company->signature_path) d-none @endif">
                                                    <div class="upload-text">
                                                        <i class="bi bi-pencil-square text-primary fs-4"></i>
                                                        <p class="mb-0 small text-muted">{{ __('+ Add Signature Image') }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-8">
                                                <label for="terms" class="form-label">{{ __('Terms & Conditions (Invoice Note)') }}</label>
                                                <textarea class="form-control" id="terms" name="terms" rows="3"
                                                          placeholder="{{ __('e.g., Payment due within 30 days...') }}">{{ $company->invoice_terms }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>

                    <div class="col-12 mt-5">
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-primary btn-lg px-5 py-2 fw-bold shadow-sm"
                                    id="submit">
                                <i class="bi bi-save me-2"></i> {{ __('Update') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    {{-- Logo crop modal: opens when a logo file is chosen; the cropped result replaces the file in the form. --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
    <style>
        #logoCropModal .crop-stage { height: 56vh; background: #f2f4f7; border-radius: .75rem; overflow: hidden; }
        #logoCropModal .crop-stage img { display: block; max-width: 100%; }
        #logoCropModal .cropper-view-box, #logoCropModal .cropper-face { border-radius: 0; }
        #logoCropRatio .btn.active { background: #4f46e5; border-color: #4f46e5; color: #fff; }
    </style>
    <div class="modal fade" id="logoCropModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold"><i class="bi bi-crop me-2 text-primary"></i><span id="logoCropTitle">{{ __('Crop your logo') }}</span></h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="crop-stage"><img id="logoCropImage" alt=""></div>
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3">
                        <div class="btn-group btn-group-sm" id="logoCropRatio" role="group" aria-label="{{ __('Aspect ratio') }}">
                            <button type="button" class="btn btn-outline-secondary active" data-ratio="0">{{ __('Free') }}</button>
                            <button type="button" class="btn btn-outline-secondary" data-ratio="1">1:1</button>
                            <button type="button" class="btn btn-outline-secondary" data-ratio="1.7778">16:9</button>
                            <button type="button" class="btn btn-outline-secondary" data-ratio="3">3:1</button>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary" id="logoCropRotate" title="{{ __('Rotate') }}"><i class="bi bi-arrow-clockwise"></i></button>
                            <button type="button" class="btn btn-outline-secondary" id="logoCropReset" title="{{ __('Reset') }}"><i class="bi bi-arrow-counterclockwise"></i></button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-primary btn-sm px-4" id="logoCropSave"><i class="bi bi-check-lg me-1"></i>{{ __('Crop & Use') }}</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<style>
    /* ... existing styles ... */
    .company-setup-page {
        background: #f8fafc;
    }

    .form-control, .bootstrap-select .dropdown-toggle, .tom-select .ts-control {
        height: 44px;
        border-radius: 8px !important;
        border-color: #d0d5dd;
        font-size: 14px;
    }

    /* Adjust TomSelect to fit the new height style */
    .tom-select .ts-control {
        padding: 8px 12px;
    }

    .upload-box, .signature-box {
        border: 2px dashed #d0d5dd;
        border-radius: 8px;
        background: #fff;
        padding: 25px;
        cursor: pointer;
    }

    .upload-text, .signature-box span {
        color: #6b7280;
        font-size: 13px;
    }

    .bank-details-note {
        font-size: 13px;
        color: #6b7280;
    }

    .bank-details-note strong {
        color: #4b5563;
    }

    /* ... end existing styles ... */
</style>
