@section('js','company')
@section('page-title','Manage Business')
<x-app-layout>
    <main class="gmail-content bg-white d-flex">
        @include('includes.settings-navigation')
        <section class="flex-grow-1 d-flex flex-column">
            <div class="px-4">
                @include('includes.master-header')
            </div>
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
                                    <div class="card-header bg-light fw-bold">1. Identity & Branding</div>
                                    <div class="card-body">
                                        <div class="row g-4 align-items-center">

                                            <div class="col-md-3 d-flex flex-column align-items-center">
                                                <label class="form-label text-center w-100">Business Logo</label>
                                                <div
                                                    class="upload-box text-center p-3 rounded-3 border border-dashed w-100"
                                                    id="logoUploadBox" style="cursor: pointer; min-height: 120px;">
                                                    <input type="file" id="logoInput" name="logoInput" class="d-none"
                                                           accept="image/png, image/jpeg">
                                                    <img id="logoPreview"
                                                         src="{{ asset($company->logo_path) }}"
                                                         alt="Logo Preview"
                                                         class="img-fluid mb-2 rounded @if(!$company->logo_path) d-none @endif">
                                                    <div class="upload-text">
                                                        <i class="bi bi-cloud-arrow-up text-primary fs-4"></i>
                                                        <p class="mb-0 small text-muted">Upload Logo</p>
                                                        <small class="text-secondary" style="font-size: 0.75rem;">PNG /
                                                            JPG - Max 5MB</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-9">
                                                <div class="mb-3">
                                                    <label for="business_name_en" class="form-label">Business Name (In
                                                        English)<span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="business_name_en"
                                                           name="business_name_en" value="{{ $company->name }}"
                                                           placeholder="Enter your official business name" required>
                                                </div>

                                                <div class="text-end">
                                                    <label for="business_name_ar" class="form-label">Business Name (In
                                                        Arabic)<span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control text-end"
                                                           id="business_name_ar" name="business_name_ar"
                                                           value="{{ $company->name_ar }}"
                                                           placeholder="Enter your official business name" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="card h-100 shadow-sm border-0">
                                    <div class="card-header bg-light fw-bold">2. Contact & Location</div>
                                    <div class="card-body">
                                        <div class="row g-3">

                                            <div class="col-md-6">
                                                <label for="companyEmail" class="form-label">Company Email<span
                                                        class="text-danger">*</span></label>
                                                <input type="email" class="form-control" id="companyEmail" required
                                                       value="{{ $company->email }}"
                                                       name="companyEmail" placeholder="info@company.com">
                                            </div>

                                            <div class="col-md-6">
                                                <label for="companyPhone" class="form-label">Company Phone<span
                                                        class="text-danger">*</span></label>
                                                <input type="tel" class="form-control" id="companyPhone" required
                                                       value="{{ $company->phone }}"
                                                       name="companyPhone" placeholder="+91 98765 43210">
                                            </div>

                                            <div class="col-6 pb-0 mb-0">
                                                <label for="address_en" class="form-label">Company Address (In
                                                    English)<span
                                                        class="text-danger">*</span></label>
                                                <textarea class="form-control h-50" id="address_en" required
                                                          name="address_en" rows="4"
                                                          placeholder="Street, Building name, etc.">{{ $company->address_1 }}</textarea>
                                            </div>

                                            <div class="col-6 pb-0 mb-0 text-end">
                                                <label for="address_ar" class="form-label">Company Address (In
                                                    Arabic)<span
                                                        class="text-danger">*</span></label>
                                                <textarea class="form-control h-50 text-end" id="address_ar" required
                                                          name="address_ar" rows="4"
                                                          placeholder="Street, Building name, etc.">{{ $company->address_1_ar }}</textarea>
                                            </div>

                                            <div class="col-md-6 pt-0 mt-0">
                                                <label for="city" class="form-label">City (In English)<span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="city_en" name="city_en"
                                                       required value="{{ $company->city }}">
                                            </div>

                                            <div class="col-md-6 pt-0 mt-0 text-end">
                                                <label for="city" class="form-label">City (In Arabic)<span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control text-end" id="city_ar"
                                                       name="city_ar" required value="{{ $company->city_ar }}">
                                            </div>

                                            <div class="col-6">
                                                <label for="city_sub_division" class="form-label">City Sub Division</label>
                                                <input type="text" class="form-control" id="city_sub_division" name="city_sub_division"
                                                       value="{{ $company->city_sub_division }}">
                                            </div>

                                            <div class="col-md-6">
                                                <label for="pincode" class="form-label">Pincode<span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="pincode" name="pincode"
                                                       required value="{{ $company->postal_code }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="card h-100 shadow-sm border-0">
                                    <div class="card-header bg-light fw-bold">3. Business Type & Registration</div>
                                    <div class="card-body">
                                        <div class="row g-3">

                                            <div class="col-md-6">
                                                <label for="businessType" class="form-label">Business Type</label>
                                                <select class="tom-select" id="businessType" name="businessType[]"
                                                        data-live-search="true"
                                                        multiple placeholder="Business Type Selection">
                                                    @foreach(industrialTypes() as $typeValue => $typeName)
                                                        <option
                                                            value="{{ $typeValue }}" @selected(in_array($typeValue,json_decode($company->business_type)))>{{ $typeName }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            @if($company->vat_status == 1)
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">Are you VAT Registered?</label>
                                                    <select id="vat_status" name="vat_status" class="tom-select"
                                                            disabled>
                                                        <option value="1" selected>Yes</option>
                                                    </select>
                                                </div>

                                                <div class="row g-3 px-0 mx-0 vat-compliance-group">

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-medium">VAT Number (TRN)</label>
                                                        <input type="text" class="form-control" disabled
                                                               value="{{ $company->tax_number }}">
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-medium">Commercial Registration (CR)
                                                            Number</label>
                                                        <input type="text" class="form-control" disabled
                                                               value="{{ $company->cr_number }}">
                                                    </div>
                                                </div>
                                            @else
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">Are you VAT Registered?</label>
                                                    <select id="vat_status" name="vat_status" class="tom-select">

                                                        <option value="0" selected>No</option>
                                                        <option value="1">Yes</option>
                                                    </select>
                                                </div>

                                                <div class="row g-3 px-0 mx-0 vat-compliance-group"
                                                     style="display: none;">

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-medium">VAT Number (TRN)</label>
                                                        <input type="text" class="form-control" id="vatNumber"
                                                               name="vatNumber" placeholder="300XXXXXXXXXXX"
                                                               value="{{ $company->tax_number }}">
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-medium">Commercial Registration (CR)
                                                            Number</label>
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
                                    <div class="card-header bg-light fw-bold">4. Invoice Footer Details</div>
                                    <div class="card-body">
                                        <div class="row g-4">
                                            <div class="col-12">
                                                <div class="alert alert-secondary py-2 small">
                                                    <i class="bi bi-info-circle me-2"></i>
                                                    **Note:** The signature and any terms/conditions added here will
                                                    appear on your final invoices.
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label">Authorized Signature</label>
                                                <div
                                                    class="signature-box text-center p-3 rounded-3 border border-dashed"
                                                    id="signatureUploadBox" style="cursor: pointer; min-height: 120px;">
                                                    <input type="file" id="signatureInput" name="signatureInput" hidden
                                                           accept="image/*">
                                                    <img id="signaturePreview"
                                                         src="{{ asset($company->signature_path) }}"
                                                         alt="Signature Preview"
                                                         class="img-fluid mb-2 rounded @if(!$company->signature_path) d-none @endif">
                                                    <div class="upload-text">
                                                        <i class="bi bi-pencil-square text-primary fs-4"></i>
                                                        <p class="mb-0 small text-muted">+ Add Signature Image</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-8">
                                                <label for="terms" class="form-label">Terms & Conditions (Invoice
                                                    Note)</label>
                                                <textarea class="form-control" id="terms" name="terms" rows="3"
                                                          placeholder="e.g., Payment due within 30 days...">{{ $company->invoice_terms }}</textarea>
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
                                <i class="bi bi-save me-2"></i> Update
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
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
