@section('js','zatca')
@section('page-title','Zatca Integration')

<x-app-layout>
    <main class="gmail-content bg-white d-flex">
        @include('includes.settings-navigation')

        <section class="flex-grow-1 px-4 d-flex flex-column">
            @include('includes.master-header')

            <div class="">

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

                <div class="row g-5">
                    <div class="col-lg-6">
                        <div class="card h-100 border-start border-5 border-warning shadow-lg">
                            <div class="card-body p-4 p-md-5">
                                <span class="badge bg-warning text-dark mb-3 fs-6">STAGE 1: SIMULATION</span>
                                <h4 class="card-title fw-bold text-warning-emphasis mb-3 me-3">Compliance Certification</h4>

                                <p class="small text-muted mb-4">
                                    This phase validates your system's technical compatibility with ZATCA's standards. Successful validation generates the **Compliance CSID**, which is necessary to pass the internal transaction testing phase.
                                </p>

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
                                    <h6 class="fw-bold text-success">STATUS: Ready for Production Activation</h6>
                                    <p class="small mb-0">The Compliance CSID has been successfully issued and registered. You may now proceed to Stage 2.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card h-100 border-start border-5 border-success shadow-lg">
                            <div class="card-body p-4 p-md-5">
                                <span class="badge bg-success mb-3 fs-6">STAGE 2: PRODUCTION</span>
                                <h4 class="card-title fw-bold text-success-emphasis mb-3 me-3">Live System Activation</h4>

                                <p class="small text-muted mb-4">
                                    This is the final step. It requires a **new, unique OTP** from the ZATCA portal and grants your system the **Production CSID**, allowing you to submit live e-invoices.
                                </p>

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

                                <div class="mt-4 pt-3 border-top">
                                    <h6 class="fw-bold text-primary pb-2">Pre-requisite Checklist</h6>
                                    <ul class="list-unstyled small mb-0">
                                        <li><i class="bi bi-check-circle-fill text-success me-2"></i> Successfully completed Stage 1 (Simulation).</li>
                                        <li><i class="bi bi-check-circle-fill text-success me-2"></i> Obtained a fresh, unique OTP for Production.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </main>
</x-app-layout>
