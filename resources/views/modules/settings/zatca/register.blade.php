@section('js','zatca')
@section('page-title', __('Zatca Integration'))
@section('page-subtitle', __('E-Invoicing registration with ZATCA'))
@php
    $zCore = $zatcaConfig->status == \App\Enums\Zatca::CORE_MODE;
    $zCompany = authUserCompany();
    $zFields = \App\Http\Controllers\Zatca\ZatcaOnboardingController::requiredCompanyFields($zCompany);
    $zMissing = array_keys(array_filter($zFields, fn ($v) => blank($v)));
    $zDetails = \App\Models\Zatca\ZatcaRegisterDetails::where('company_id', companyId())->first();
    $zStartDate = $zDetails->wave_date ?? null;
@endphp

<x-app-layout>
    <main class="gmail-content bg-white d-flex ">
        @include('includes.settings-navigation')

        <section class="flex-grow-1 px-4 d-flex flex-column">
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-xl-8 col-lg-10">

                        {{-- ── Header ── --}}
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-success p-3 rounded-3 me-3">
                                <i class="bi bi-shield-check text-white fs-3"></i>
                            </div>
                            <div>
                                <h2 class="fw-bold mb-0">{{ __('ZATCA Phase 2 Registration') }}</h2>
                                <p class="text-muted mb-0">
                                    {{ $zCore ? __('Your EGS device is active and connected to ZATCA.') : __('Onboard your E-Invoicing solution to the Fatoora Portal') }}
                                </p>
                            </div>
                            <div class="ms-auto">
                                <span class="badge {{ $zCore ? 'bg-success' : 'bg-secondary' }} px-3 py-2">
                                    <i class="bi bi-circle-fill me-1" style="font-size:8px;"></i>
                                    {{ $zCore ? __('Registered') : __('Not Registered') }}
                                </span>
                            </div>
                        </div>

                        @if(!$zCore)
                            {{-- ════ NOT REGISTERED: registration form ════ --}}
                            <div class="card border-0 shadow-sm mb-4 position-relative">
                                <div class="card-body p-0">

                                    {{-- Steps bar --}}
                                    <div class="d-flex text-center border-bottom small fw-bold text-uppercase">
                                        <div id="step-1" class="col py-3 border-end bg-light text-indigo border-bottom border-indigo border-3 step-indicator">{{ __('1. Info & Environment') }}</div>
                                        <div id="step-2" class="col py-3 border-end text-muted step-indicator">{{ __('2. CSR & Keys') }}</div>
                                        <div id="step-3" class="col py-3 border-end text-muted step-indicator">{{ __('3. Compliance CSID') }}</div>
                                        <div id="step-4" class="col py-3 border-end text-muted step-indicator">{{ __('4. Compliance Checks') }}</div>
                                        <div id="step-5" class="col py-3 text-muted step-indicator">{{ __('5. Production CSID') }}</div>
                                    </div>

                                    <div>
                                        <div id="registration-messages"></div>
                                        <div id="registration-error" class="alert alert-danger d-none m-4"></div>

                                        <form id="registration-form" onsubmit="return false;" class="p-4 p-md-5">
                                            @csrf

                                            {{-- Company details (pre-filled, read-only) --}}
                                            <div class="mb-4">
                                                <div class="d-flex align-items-center mb-3">
                                                    <h5 class="fw-bold mb-0">{{ __('Organization Details') }}</h5>
                                                    <span class="badge bg-indigo ms-2 small">{{ __('Auto-filled from Company Settings') }}</span>
                                                </div>

                                                <div class="row g-3">
                                                    @foreach($zFields as $label => $value)
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-bold">
                                                                {{ __($label) }}
                                                                @if($label === 'Tax Registration Number (TRN)')<span class="text-danger">*</span>@endif
                                                            </label>
                                                            <input type="text" class="form-control bg-light {{ blank($value) ? 'is-invalid' : '' }}" value="{{ $value }}" readonly>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                @if($zMissing)
                                                    <div class="alert alert-warning mt-3 small mb-0">
                                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                                        {{ __('Some company details are missing') }} ({{ implode(', ', array_map(fn ($m) => __($m), $zMissing)) }}).
                                                        {{ __('Please update your') }}
                                                        <a href="/settings/company" class="fw-bold">{{ __('Company Settings') }}</a>
                                                        {{ __('before registering.') }}
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Environment: production (live ZATCA) --}}
                                            <div class="d-none">
                                                <input class="form-check-input" type="radio" name="environment" id="env_core" value="core" checked>
                                            </div>

                                            {{-- Solution identification --}}
                                            <div class="mb-4">
                                                <h5 class="fw-bold mb-3">{{ __('Solution Identification') }}</h5>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">{{ __('Solution Name') }}</label>
                                                        <input type="text" name="sol_name" class="form-control" value="Flikma-ERP" readonly>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">{{ __('Device Serial Number') }} <span class="text-danger">*</span></label>
                                                        <input type="text" name="serial" class="form-control" placeholder="e.g. AD-RIYADH"
                                                               value="{{ 'AD-' . strtoupper(preg_replace('/\s+/', '', (string) $zCompany->city ?: 'EGS')) . '-' . rand(100, 999) }}" required>
                                                        <div class="form-text">{{ __('Unique identifier for this EGS device.') }}</div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- ZATCA invoice send start date --}}
                                            <div class="mb-4">
                                                <h5 class="fw-bold mb-3">{{ __('ZATCA Invoice Send Start Date') }}</h5>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold d-flex align-items-center gap-1">
                                                            {{ __('Start Date') }} <span class="text-danger">*</span>
                                                            <i class="bi bi-question-circle text-secondary" data-bs-toggle="tooltip"
                                                               title="{{ __('From this date onwards, all approved invoices are sent to ZATCA. This cannot be changed once the device is registered.') }}"></i>
                                                        </label>
                                                        <input type="date" name="zatca_submit_start_date" class="form-control"
                                                               value="{{ now()->format('Y-m-d') }}" required>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- OTP --}}
                                            <div class="mb-4 p-4 bg-light rounded-3 border">
                                                <h5 class="fw-bold mb-1"><i class="bi bi-key me-2 text-indigo"></i>{{ __('Portal OTP') }}</h5>
                                                <p class="text-muted small mb-3">
                                                    {{ __('Enter the 6-digit OTP generated from the') }}
                                                    <a href="https://fatoora.zatca.gov.sa" target="_blank" class="fw-bold">{{ __('ZATCA Fatoora Portal') }}</a>
                                                    {{ __('under Device Management.') }}
                                                </p>
                                                <div class="col-md-4">
                                                    <input type="text" name="otp" inputmode="numeric" autocomplete="one-time-code"
                                                           class="form-control form-control-lg text-center fw-bold letter-spacing-5"
                                                           maxlength="6" placeholder="000000" required>
                                                </div>
                                            </div>

                                            {{-- Submit --}}
                                            <div class="d-flex justify-content-between align-items-center border-top pt-4">
                                                <a href="https://zatca.gov.sa/en/E-Invoicing/Pages/default.aspx" target="_blank" class="text-decoration-none text-muted fw-bold small">
                                                    <i class="bi bi-question-circle me-1"></i>{{ __('Need help with registration?') }}
                                                </a>
                                                <button type="submit" id="submit-onboard" class="btn btn-indigo px-5 fw-bold" {{ $zMissing ? 'disabled' : '' }}>
                                                    <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                                                    {{ __('Generate Certificate & Onboard') }}
                                                    <i class="bi bi-arrow-right ms-2"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- Pre-requisites --}}
                            <div class="card border-0 bg-dark text-white shadow-sm">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>{{ __('Pre-requisites for Registration') }}</h6>
                                    <ul class="small mb-0 opacity-75 list-unstyled">
                                        <li class="mb-2">
                                            <i class="bi bi-check2-circle me-2 {{ $zCompany->vat_number ? 'text-success' : 'text-warning' }}"></i>
                                            {{ __('Valid Tax Registration Number (TRN)') }}
                                            @if($zCompany->vat_number) — <span class="text-success">{{ $zCompany->vat_number }}</span>
                                            @else — <span class="text-warning">{{ __('Missing in Company Settings') }}</span>@endif
                                        </li>
                                        <li class="mb-2">
                                            <i class="bi bi-check2-circle me-2 {{ $zCompany->cr_number ? 'text-success' : 'text-warning' }}"></i>
                                            {{ __('CR Number') }}
                                            @if($zCompany->cr_number) — <span class="text-success">{{ $zCompany->cr_number }}</span>
                                            @else — <span class="text-warning">{{ __('Missing in Company Settings') }}</span>@endif
                                        </li>
                                        <li class="mb-2"><i class="bi bi-check2-circle me-2 text-success"></i>{{ __('Access to ZATCA Fatoora Portal (Sandbox / Simulation / Production).') }}</li>
                                        <li><i class="bi bi-check2-circle me-2 text-success"></i>{{ __('Unique Device Serial Number for this EGS (Electronic Generating System).') }}</li>
                                    </ul>
                                </div>
                            </div>
                        @else
                            {{-- ════ FULLY REGISTERED: success view ════ --}}
                            <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                                <div class="card-header bg-success text-white py-3 border-0">
                                    <h5 class="mb-0 fw-bold"><i class="bi bi-check-circle-fill me-2"></i>{{ __('Device Onboarded Successfully') }}</h5>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row g-3 mb-4">
                                        <div class="col-sm-6"><p class="text-muted small mb-1">{{ __('Company Name') }}</p><p class="fw-bold mb-0">{{ $zCompany->name }}</p></div>
                                        <div class="col-sm-6"><p class="text-muted small mb-1">{{ __('TRN Associated') }}</p><p class="fw-bold text-indigo mb-0">{{ $zCompany->vat_number ?: '-' }}</p></div>
                                        <div class="col-sm-6"><p class="text-muted small mb-1">{{ __('CR Number') }}</p><p class="fw-bold mb-0">{{ $zCompany->cr_number ?: 'N/A' }}</p></div>
                                        <div class="col-sm-6"><p class="text-muted small mb-1">{{ __('City') }}</p><p class="fw-bold mb-0">{{ $zCompany->city ?: 'N/A' }}</p></div>
                                        <div class="col-sm-6"><p class="text-muted small mb-1">{{ __('Registration Date') }}</p><p class="fw-bold mb-0">{{ $zatcaConfig->updated_at ? \Carbon\Carbon::parse($zatcaConfig->updated_at)->format('d M, Y') : 'N/A' }}</p></div>
                                        <div class="col-sm-6"><p class="text-muted small mb-1">{{ __('Device Serial Number') }}</p><p class="fw-bold mb-0">{{ $zDetails->custom_id ?? 'N/A' }}</p></div>
                                        <div class="col-sm-6"><p class="text-muted small mb-1">{{ __('Environment') }}</p><p class="fw-bold mb-0 text-uppercase">{{ __('Core (Production)') }}</p></div>
                                        <div class="col-sm-6">
                                            <p class="text-muted small mb-1 d-flex align-items-center gap-1">
                                                {{ __('ZATCA Invoice Send Start Date') }}
                                                <i class="bi bi-question-circle text-secondary" data-bs-toggle="tooltip"
                                                   title="{{ __('From this date onwards, all approved invoices are sent to ZATCA. Set during device registration and locked afterwards.') }}"></i>
                                            </p>
                                            <p class="fw-bold mb-0">{{ $zStartDate ? \Carbon\Carbon::parse($zStartDate)->format('d M, Y') : 'N/A' }}</p>
                                        </div>
                                    </div>

                                    <hr>

                                    <h5 class="fw-bold mb-3 mt-4">{{ __('Compliance Guide (Phase 2)') }}</h5>
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <div class="p-3 border rounded h-100 bg-white shadow-sm">
                                                <h6 class="fw-bold text-primary border-bottom pb-2"><i class="bi bi-building me-1"></i> {{ __('ZATCA Cleared (Standard)') }}</h6>
                                                <ul class="small text-muted ps-3 mb-0">
                                                    <li>{{ __("Must include Buyer's TRN.") }}</li>
                                                    <li>{{ __('Must be') }} <strong>{{ __('cleared') }}</strong> {{ __('by ZATCA in real-time before sharing with the buyer.') }}</li>
                                                    <li>{{ __('Requires XML format with Digital Signature.') }}</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="p-3 border rounded h-100 bg-white shadow-sm">
                                                <h6 class="fw-bold text-success border-bottom pb-2"><i class="bi bi-person me-1"></i> {{ __('ZATCA Reported (Simplified)') }}</h6>
                                                <ul class="small text-muted ps-3 mb-0">
                                                    <li>{{ __('Must be') }} <strong>{{ __('reported') }}</strong> {{ __('to ZATCA within 24 hours of issuance.') }}</li>
                                                    <li>{{ __('Must display a Phase 2 compliant QR code.') }}</li>
                                                    <li>{{ __('Archive locally and sync via API.') }}</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-light border-0 py-3">
                                    <div class="d-flex justify-content-around text-center">
                                        <a href="https://zatca.gov.sa/en/E-Invoicing/Introduction/Pages/Technical-Requirements.aspx" target="_blank" class="text-decoration-none small fw-bold">
                                            <i class="bi bi-file-earmark-pdf me-1"></i> {{ __('ZATCA Technical Docs') }}
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 bg-dark text-white shadow-sm">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>{{ __('Critical ZATCA Rules') }}</h6>
                                    <div class="row small opacity-75">
                                        <div class="col-md-6">
                                            <p class="mb-1"><i class="bi bi-dot"></i> {{ __('Sequential numbering is mandatory.') }}</p>
                                            <p class="mb-1"><i class="bi bi-dot"></i> {{ __('Anti-tampering features must be active.') }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><i class="bi bi-dot"></i> {{ __('Archiving for 6 years is required.') }}</p>
                                            <p class="mb-1"><i class="bi bi-dot"></i> {{ __('No editing/deleting of issued invoices.') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </section>
    </main>
</x-app-layout>

<style>
    .btn-indigo { background-color: #4f46e5; color: white; }
    .btn-indigo:hover { background-color: #4338ca; color: white; }
    .btn-indigo:disabled { background-color: #a5b4fc; color: white; cursor: not-allowed; }
    .text-indigo { color: #4f46e5; }
    .bg-indigo { background-color: #4f46e5 !important; }
    .border-indigo { border-color: #4f46e5 !important; }
    .letter-spacing-5 { letter-spacing: 5px; }
    .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, .1); }
    #registration-messages:not(:empty) {
        position: absolute; top: 10%; left: 0; width: 100%; height: 90%;
        background: rgba(255, 255, 255, 0.85); z-index: 10; display: flex; align-items: center; justify-content: center;
        backdrop-filter: blur(4px); border-radius: 0.5rem;
    }
    #registration-messages .alert { width: 80%; max-width: 500px; margin-bottom: 0; box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important; }
</style>

<script>
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) { new bootstrap.Tooltip(el); });

    $(function () {
        const csrf = $('meta[name="csrf-token"]').attr('content');
        const T = {
            csr: @json(__('Generating Private Key and CSR...')),
            compliance: @json(__('Requesting Compliance CSID from ZATCA...')),
            checks: @json(__('CSR Accepted. Now performing compliance checks (Sample Invoices)...')),
            production: @json(__('Compliance passed. Final stage: Requesting Production CSID. Please do not refresh...')),
            done: @json(__('ZATCA Device Registered Successfully! Redirecting...')),
            unexpected: @json(__('An unexpected error occurred.'))
        };

        $('#registration-form').on('submit', function (e) {
            e.preventDefault();
            const form = $(this), submitBtn = $('#submit-onboard'), spinner = submitBtn.find('.spinner-border'), messages = $('#registration-messages');
            messages.empty();
            submitBtn.prop('disabled', true);
            spinner.removeClass('d-none');

            function updateStepUI(step) {
                $('.step-indicator').removeClass('bg-light text-indigo border-bottom border-indigo border-3').addClass('text-muted');
                $('#step-' + step).addClass('bg-light text-indigo border-bottom border-indigo border-3').removeClass('text-muted');
                for (let i = 1; i < step; i++) {
                    $('#step-' + i).addClass('text-success').removeClass('text-muted text-indigo');
                    if (!$('#step-' + i + ' i.bi-check-all').length) $('#step-' + i).prepend('<i class="bi bi-check-all me-1"></i>');
                }
            }
            function updateMessage(msg, type, icon) {
                messages.html('<div class="alert alert-' + type + ' border-0 shadow-sm" role="alert"><i class="bi bi-' + icon + ' me-2"></i> ' + msg +
                    (type === 'info' ? '<div class="spinner-border spinner-border-sm ms-2" role="status"></div>' : '') + '</div>');
            }
            function handleError(xhr) {
                let msg = T.unexpected;
                if (typeof xhr === 'string') msg = xhr;
                else if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                else if (xhr && xhr.responseJSON && xhr.responseJSON.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                messages.html('<div class="alert alert-danger border-0 shadow-sm" role="alert"><i class="bi bi-exclamation-triangle-fill me-2"></i> ' + msg +
                    '<div class="mt-2"><button type="button" class="btn btn-sm btn-outline-danger" id="registration-retry">' + @json(__('Try again')) + '</button></div></div>');
                $('#registration-retry').on('click', function () {
                    messages.empty();
                    $('.step-indicator').removeClass('bg-light text-indigo border-bottom border-indigo border-3 text-success').addClass('text-muted').find('i.bi-check-all').remove();
                    $('#step-1').addClass('bg-light text-indigo border-bottom border-indigo border-3').removeClass('text-muted');
                });
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
            function post(url, data) {
                return $.ajax({ url: url, method: 'POST', data: Object.assign({ _token: csrf }, data || {}), dataType: 'json' });
            }
            function ok(res) { return res && res.type === 'success'; }

            // Step 2: private key + CSR
            updateStepUI(2);
            updateMessage(T.csr, 'info', 'key');
            post('/settings/zatca/onboard/csr', form.serialize().split('&').reduce(function (o, kv) { const p = kv.split('='); o[decodeURIComponent(p[0])] = decodeURIComponent((p[1] || '').replace(/\+/g, ' ')); return o; }, {}))
                .then(function (r) {
                    if (!ok(r)) throw r.message;
                    // Step 3: Compliance CSID
                    updateStepUI(3);
                    updateMessage(T.compliance, 'info', 'shield-lock');
                    return post('/settings/zatca/onboard/compliance');
                })
                .then(function (r) {
                    if (!ok(r)) throw r.message;
                    // Step 4: compliance checks
                    updateStepUI(4);
                    updateMessage(T.checks, 'info', 'check2-square');
                    return post('/settings/zatca/onboard/checks');
                })
                .then(function (r) {
                    if (!ok(r)) throw r.message;
                    // Step 5: Production CSID
                    updateStepUI(5);
                    updateMessage(T.production, 'info', 'cloud-upload');
                    return post('/settings/zatca/onboard/production');
                })
                .then(function (r) {
                    if (!ok(r)) throw r.message;
                    updateStepUI(6);
                    updateMessage(T.done, 'success', 'check-circle-fill');
                    setTimeout(function () { window.location.reload(); }, 2000);
                })
                .catch(function (err) { handleError(err); });
        });
    });
</script>
