@section('page-title','New Quotation – Charge Details')
@section('js','quotation-new')
<x-app-layout>
    <main class="bg-white px-3 py-3">

        @include('modules.quotation-new.wizard._wizard-header', ['currentStep' => 4])

        <div class="d-flex justify-content-center gap-5 mb-3 text-muted small">
            <span><strong>Client</strong> &nbsp; {{ $quotation->client->name_en ?? '—' }}</span>
            <span><strong>Department</strong> &nbsp; {{ $quotation->department ?? '—' }}</span>
        </div>

        <form id="wizardForm" novalidate>
            @csrf
            <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">

            <div class="card shadow-sm border-0 mx-auto" style="max-width:1350px;">
                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                        <h5 class="fw-semibold text-primary mb-0">
                            <i class="bi bi-cash-stack me-2"></i>Standard Charges
                        </h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="addChargeRow">
                                <i class="bi bi-plus-circle me-1"></i> Add Charge
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" id="deleteSelectedCharges">
                                <i class="bi bi-trash me-1"></i> Delete Selected
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-1 charges-table" id="chargesTable">
                            <thead class="table-light">
                            <tr>
                                {{-- Col 1: checkbox --}}
                                <th style="width:36px;">
                                    <input type="checkbox" class="form-check-input" id="selectAllCharges">
                                </th>
                                {{-- Col 2-11 --}}
                                <th>Charge Description</th>
                                <th style="width:110px;">OFD Type</th>
                                <th style="width:130px;">Unit</th>
                                <th style="width:55px;">Qty</th>
                                <th style="width:100px;">Freight</th>
                                <th style="width:65px;">Dr/Cr</th>
                                <th style="width:110px;">Qty/Amount</th>
                                <th style="width:130px;">FCY Amount</th>
                                <th style="width:120px;">Amount (INR)</th>
                                <th style="width:130px;">Tax Amount (INR)</th>
                            </tr>
                            </thead>
                            <tbody id="chargesBody">
                            @foreach($quotation->charges as $charge)
                                @include('modules.quotation-new._charge-row', ['charge' => $charge])
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Totals --}}
                    <div class="row justify-content-end mt-3">
                        <div class="col-md-5 col-lg-4">
                            <div class="bg-light rounded-3 px-4 py-3">
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted">Sub Total (INR)</span>
                                    <span class="fw-semibold" id="subTotalDisplay">0.00</span>
                                </div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted">Total Tax (INR)</span>
                                    <span class="fw-semibold" id="totalTaxDisplay">0.00</span>
                                </div>
                                <div class="d-flex justify-content-between py-2 mt-1 border-top">
                                    <span class="fw-bold">Grand Total (INR)</span>
                                    <span class="fw-bold text-primary fs-5" id="grandTotalDisplay">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <style>
                .charges-table {
                    font-size: 13.5px;
                }
                .charges-table th {
                    font-size: 12.5px;
                    text-transform: uppercase;
                    letter-spacing: .02em;
                    color: #6c757d;
                    vertical-align: middle;
                }
                .charges-table td {
                    padding: 0.5rem 0.4rem;
                    vertical-align: middle;
                }
                .charges-table tbody tr:hover {
                    background-color: #f8f9fb;
                }
            </style>

            @include('modules.quotation-new.wizard._wizard-footer', ['step' => 4])
        </form>
    </main>
</x-app-layout>
