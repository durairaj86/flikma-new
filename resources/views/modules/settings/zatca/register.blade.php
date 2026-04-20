@section('js','zatca')
@section('page-title','Zatca Integration')

<x-app-layout>
    <main class="gmail-content bg-white d-flex">
        @include('includes.settings-navigation')

        <section class="flex-grow-1 px-4 d-flex flex-column">
            @include('includes.master-header')

            <div class="container-fluid py-4">

                {{-- Header and Context --}}
                <div class="mb-5 pb-4 border-bottom">
                    <div>
                        <h2 class="fw-bolder text-primary mb-2">E-Invoicing Security and Registration</h2>
                        <p class="text-muted">
                            This interface manages the cryptographic registration of your E-Invoicing Solution Unit (ESU) with the Zakat, Tax and Customs Authority (ZATCA), ensuring compliance across two mandatory phases.
                        </p>
                    </div>

                    <div class="mt-3">
                        <div class="alert alert-info border-0 shadow-sm" role="alert">
                            <h6 class="alert-heading fw-bold mb-2">🔐 Security Note: OTP is Mandatory</h6>
                            <p class="mb-0 small">The One-Time Password (OTP) secures the communication link and facilitates the issuance of the **Cryptographic Stamp Identifier (CSID)** for your system. **A new OTP is required for each phase.**</p>
                        </div>
                    </div>
                </div>

                {{-- *** NEW SECTION: INTEGRATION COMPLETED *** --}}
                @if($zatcaConfig->status == \App\Enums\Zatca::CORE_MODE)
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-lg p-5 bg-success-subtle text-center">
                                <div class="card-body">
                                    <i class="bi bi-patch-check-fill text-success display-1 mb-4"></i>
                                    <h1 class="fw-bolder text-success mb-3">ZATCA Integration Complete!</h1>
                                    <p class="lead text-success-emphasis mb-4">
                                        Your E-Invoicing Solution Unit (ESU) is **fully registered** and **activated** for live production e-invoicing.
                                    </p>

                                    {{--<div class="d-flex justify-content-center gap-4 border-top pt-4">
                                        <div class="p-3 bg-white rounded shadow-sm">
                                            <h6 class="fw-bold mb-1 text-primary">Status</h6>
                                            <span class="badge bg-success fs-6">Live & Compliant</span>
                                        </div>
                                        <div class="p-3 bg-white rounded shadow-sm">
                                            <h6 class="fw-bold mb-1 text-primary">Last Updated</h6>
                                            <p class="mb-0 small text-muted">{{ $zatcaConfig->last_update_date ?? 'N/A' }}</p>
                                        </div>
                                    </div>--}}

                                    <p class="small text-muted mt-4">
                                        You may now generate and submit electronic invoices according to ZATCA regulations.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- *** ORIGINAL SECTION: STAGES 1 & 2 (Only visible if NOT in PRODUCTION_MODE) *** --}}
                    <div class="row g-5">

                        {{-- STAGE 1: SIMULATION MODE --}}
                        @if(blank($zatcaConfig->status) || $zatcaConfig->status == \App\Enums\Zatca::SIMULATION_MODE)
                            <div class="col-lg-6">
                                <div class="card h-100 border-start border-5 border-warning shadow-lg" id="simulation-card">
                                    <div class="card-body p-4 p-md-5">
                                        <span class="badge bg-warning text-dark mb-3 fs-6">STAGE 1: SIMULATION</span>
                                        <h4 class="card-title fw-bold text-warning-emphasis mb-3 me-3">Compliance Certification</h4>

                                        <p class="small text-muted mb-4">
                                            This phase validates your system's technical compatibility with ZATCA's standards. Successful validation generates the **Compliance CSID**.
                                        </p>

                                        {{-- DYNAMIC CONTENT: OTP Form OR Success Message --}}
                                        @if($zatcaConfig->status != \App\Enums\Zatca::SIMULATION_MODE)
                                            {{-- Case 1: Display OTP Form (Awaiting validation) --}}
                                            <div id="simulation-otp-form-container">
                                                <form id="simulation-form" class="mb-4">
                                                    <div class="mb-3">
                                                        <label for="simulation_otp" class="form-label fw-medium">Simulation Phase OTP <span class="text-danger">*</span></label>
                                                        <small class="text-muted d-block mb-1">
                                                            Paste the unique 6-digit OTP obtained from the ZATCA E-invoicing Portal.
                                                        </small>
                                                        <input class="form-control form-control-lg"
                                                               id="simulation_otp"
                                                               name="simulation_otp"
                                                               placeholder="e.g., 123456"
                                                               minlength="6" maxlength="6"
                                                               type="text"
                                                               required>
                                                    </div>
                                                    <div class="d-grid">
                                                        <button id="submit-simulation-validate"
                                                                type="button"
                                                                class="btn btn-warning text-dark fw-bold">
                                                            <i class="bi bi-gear me-2"></i> Validate Simulation CSID
                                                        </button>
                                                    </div>
                                                </form>

                                                <div class="mt-4 pt-3 border-top">
                                                    <h6 class="fw-bold text-success pb-2">STATUS: Ready for Production Activation</h6>
                                                    <ul class="list-unstyled small mb-0">
                                                        <li><i class="bi bi-check-circle-fill text-success me-2"></i> The Compliance CSID has been successfully issued and registered.</li>
                                                        <li><i class="bi bi-check-circle-fill text-warning me-2"></i> You may now proceed to Stage 2.</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        @else
                                            {{-- Case 2: Display Success Message (Already validated) --}}
                                            <div id="simulation-success-message-container" class="text-center p-4 rounded bg-success-subtle">
                                                <i class="bi bi-check-circle-fill display-4 text-success mb-3"></i>
                                                <h5 class="fw-bold text-success">STAGE 1: ACTIVATED</h5>
                                                <p class="small text-success mb-0">
                                                    The Compliance CSID has been successfully issued and registered. **You may now proceed to Stage 2: Production Activation.**
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif


                        {{-- STAGE 2: PRODUCTION MODE --}}
                        @if($zatcaConfig->status == \App\Enums\Zatca::SIMULATION_MODE || blank($zatcaConfig->status))
                            <div class="col-lg-6">
                                <div class="card h-100 border-start border-5 border-success shadow-lg"
                                     @if($zatcaConfig->status != \App\Enums\Zatca::SIMULATION_MODE) disabled @endif >
                                    <div class="card-body p-4 p-md-5">
                                        <span class="badge bg-success mb-3 fs-6">STAGE 2: PRODUCTION</span>
                                        <h4 class="card-title fw-bold text-success-emphasis mb-3 me-3">Live System Activation</h4>

                                        <p class="small text-muted mb-4">
                                            This is the final step. It requires a **new, unique OTP** from the ZATCA portal and grants your system the **Production CSID**, allowing you to submit live e-invoices.
                                        </p>

                                        {{-- Production OTP Form --}}
                                        <form id="production-form" class="mb-4">
                                            <div class="mb-3">
                                                <label for="core_otp" class="form-label fw-medium">Production Phase OTP <span class="text-danger">*</span></label>
                                                <small class="text-danger d-block mb-1 fw-bold">
                                                    **ATTENTION:** Use a NEW OTP. Do not reuse the Simulation OTP.
                                                </small>
                                                <input class="form-control form-control-lg"
                                                       id="core_otp"
                                                       name="core_otp"
                                                       placeholder="e.g., 654321"
                                                       minlength="6" maxlength="6"
                                                       type="text"
                                                       required>
                                            </div>

                                            <div class="d-grid">
                                                <button id="submit-core-validate"
                                                        type="button"
                                                        class="btn btn-success btn-lg fw-bold">
                                                    <i class="bi bi-rocket-takeoff me-2"></i> Activate Production E-Invoicing
                                                </button>
                                            </div>
                                        </form>

                                        {{-- Production Advisory --}}
                                        <div class="mt-4 pt-3 border-top">
                                            <h6 class="fw-bold text-primary pb-2">Pre-requisite Checklist</h6>
                                            <ul class="list-unstyled small mb-0">
                                                <li><i class="bi bi-check-circle-fill text-success me-2"></i> Successfully completed Stage 1 (Simulation).</li>
                                                <li><i class="bi bi-check-circle-fill text-warning me-2"></i> Obtained a fresh, unique OTP for Production.</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
                {{-- *** END ORIGINAL SECTION *** --}}
            </div>

        </section>
    </main>
</x-app-layout>

{{-- MODAL BACKDROP AND LOADER MARKUP (Unchanged) --}}
<div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="loadingModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body text-center p-5">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 id="loadingModalLabel" class="fw-bold text-primary">Validating ZATCA OTP & Installing...</h5>
                <p class="text-muted small">
                    Establishing a secure connection and requesting CSID certificate. Please do not close this window.
                </p>
            </div>
        </div>
    </div>
</div>

{{-- JAVASCRIPT LOGIC (Unchanged, handles the transition from OTP Form to Simulation Success) --}}
<script>
    /*document.addEventListener('DOMContentLoaded', function() {
        const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
        const simulationFormBtn = document.getElementById('submit-simulation-validate');
        const simulationCardBody = document.querySelector('#simulation-card .card-body');

        // This function simulates the backend call and the resulting DOM update
        const handleSimulationValidation = () => {

            // Check if the card exists (it won't if integration is complete)
            if (!simulationCardBody) return;

            // 1. Show the Loader Modal
            loadingModal.show();

            // 2. Simulate Backend Delay (e.g., an AJAX call)
            setTimeout(() => {

                // --- Backend Response Simulation ---
                const success = true; // Assume success for this flow

                // 3. Hide the Loader Modal
                loadingModal.hide();

                if (success) {
                    // 4. Update the Simulation Card Content (Mimicking successful server response)

                    // For pure JS/HTML simulation:
                    const successContent = `
                        <div id="simulation-success-message-container" class="text-center p-4 rounded bg-success-subtle">
                            <i class="bi bi-check-circle-fill display-4 text-success mb-3"></i>
                            <h5 class="fw-bold text-success">STAGE 1: ACTIVATED</h5>
                            <p class="small text-success mb-0">
                                The Compliance CSID has been successfully issued and registered.
                                **You may now proceed to Stage 2: Production Activation.**
                            </p>
                        </div>
                    `;

                    // Replace the inner content of the simulation card body
                    simulationCardBody.innerHTML = `
                        <span class="badge bg-warning text-dark mb-3 fs-6">STAGE 1: SIMULATION</span>
                        <h4 class="card-title fw-bold text-warning-emphasis mb-3 me-3">Compliance Certification</h4>
                        <p class="small text-muted mb-4">
                            This phase validates your system's technical compatibility with ZATCA's standards. Successful validation generates the **Compliance CSID**.
                        </p>
                        ${successContent}
                    `;
                } else {
                    // Handle failure: show error message on the form
                    alert('Validation failed. Please check the OTP or try again.');
                }

            }, 2500); // 2.5 seconds delay simulation
        };

        if (simulationFormBtn) {
            simulationFormBtn.addEventListener('click', handleSimulationValidation);
        }
    });*/
</script>
