@section('js','user')
@section('page-title','Users')
<x-app-layout>
    <main class="gmail-content bg-white d-flex">
        @include('includes.settings-navigation')
        <section class="flex-grow-1 px-4 d-flex flex-column">
            @include('includes.master-header')

            <div class="card border-0 shadow-sm rounded-3 p-4 mt-4">

                <h5 class="fw-semibold mb-3">Tax & Compliance (Saudi Arabia)</h5>

                <form class="row g-4">

                    <!-- VAT Registered Toggle -->
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Are you VAT Registered?</label>
                        <select id="vat_status" class="form-select">
                            <option value="no" selected>No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>

                    <!-- VAT Number -->
                    <div class="col-md-6 vat-input">
                        <label class="form-label fw-medium">VAT Number (TRN)</label>
                        <input type="text" class="form-control" placeholder="300XXXXXXXXXXX">
                    </div>

                    <!-- CR Number -->
                    <div class="col-md-6 vat-input">
                        <label class="form-label fw-medium">Commercial Registration (CR) Number</label>
                        <input type="text" class="form-control" placeholder="1234567890">
                    </div>

                    <!-- Company Address (Saudi Invoice Requirement) -->
                    <div class="col-md-6 vat-input">
                        <label class="form-label fw-medium">Registered Company Address</label>
                        <input type="text" class="form-control" placeholder="Riyadh, Saudi Arabia">
                    </div>

                    <!-- Certificate Upload -->
                    <div class="col-md-6 vat-input">
                        <label class="form-label fw-medium">Upload VAT Certificate</label>
                        <input type="file" class="form-control">
                    </div>

                    <!-- QR Code Invoice Compliance (Optional) -->
                    <div class="col-md-6 vat-input">
                        <label class="form-label fw-medium">e-Invoice (ZATCA) QR Format</label>
                        <select class="form-select">
                            <option selected>Standard (Phase 2 Compliant)</option>
                            <option>Simplified Invoice</option>
                        </select>
                    </div>

                </form>

                <div class="text-end mt-4">
                    <button class="btn btn-primary px-4">Save Tax Details</button>
                </div>

            </div>

            <script>
                // Toggle VAT fields visibility
                $(document).ready(function() {
                    function toggleVatFields() {
                        if ($('#vat_status').val() === 'yes') {
                            $('.vat-input').slideDown();
                        } else {
                            $('.vat-input').slideUp();
                        }
                    }
                    $('#vat_status').on('change', toggleVatFields);
                    toggleVatFields(); // run on load
                });
            </script>

            <style>
                .vat-input { display: none; }
            </style>


        </section>
    </main>
</x-app-layout>
