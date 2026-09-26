@section('page-title','New Quotation – Container / Consignment')
@section('js','quotation-new')
<x-app-layout>
    <main class="bg-white px-3 py-3">

        @include('modules.quotation-new.wizard._wizard-header', ['currentStep' => 3])

        <div class="d-flex justify-content-center gap-5 mb-3 text-muted small">
            <span><strong>Client:</strong> {{ $quotation->client->name_en ?? '—' }}</span>
            <span><strong>Department:</strong> {{ $quotation->department ?? '—' }}</span>
        </div>

        <form id="wizardForm" novalidate>
            @csrf
            <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">

            <div class="card shadow-sm border-0 mx-auto" style="max-width:960px;">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-4 text-primary border-bottom pb-2">
                        <i class="bi bi-box-seam me-2"></i>Container / Consignment
                    </h5>
                    <div class="row g-3">

                        <div class="col-md-4">
                            <label class="form-label fw-medium">Airline</label>
                            <select id="qn_carrier" name="carrier" class="tom-select-search"
                                    data-placeholder="Select Airline">
                                <option value="">--Select--</option>
                                @if($quotation->carrier)
                                    <option value="{{ $quotation->carrier }}" selected>{{ $quotation->carrier }}</option>
                                @endif
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-medium">Flight Name</label>
                            <input type="text" name="vessel_name" class="form-control"
                                   value="{{ $quotation->vessel_name }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-medium">Flight No</label>
                            <input type="text" name="voyage_no" class="form-control"
                                   value="{{ $quotation->voyage_no }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-medium">No. of Pcs</label>
                            <input type="number" name="no_of_pcs" class="form-control"
                                   value="{{ $quotation->no_of_pcs }}" min="0">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-medium">Gross Weight</label>
                            <input type="number" name="gross_weight" class="form-control"
                                   value="{{ $quotation->gross_weight }}" step="0.01" min="0">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-medium">Unit</label>
                            <input type="text" name="weight_unit" class="form-control"
                                   value="{{ $quotation->weight_unit }}"
                                   list="weight-unit-list" placeholder="KGS">
                            <datalist id="weight-unit-list">
                                <option value="KGS"><option value="LBS"><option value="MT">
                                <option value="TON"><option value="CM"><option value="CBM">
                            </datalist>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-medium">Volume</label>
                            <input type="number" name="volume" class="form-control qn-volume"
                                   value="{{ $quotation->volume }}" step="0.01" min="0">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-medium">Volume Weight</label>
                            <input type="number" name="volume_weight" class="form-control"
                                   value="{{ $quotation->volume_weight }}" step="0.01" min="0">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-medium">Unit</label>
                            <input type="text" name="volume_unit" class="form-control"
                                   value="{{ $quotation->volume_unit }}"
                                   list="volume-unit-list" placeholder="CBM">
                            <datalist id="volume-unit-list">
                                <option value="CBM"><option value="CFT"><option value="CM">
                                <option value="KGS"><option value="LBS"><option value="MT">
                            </datalist>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-medium">Chargeable Unit</label>
                            <input type="number" name="chargeable_unit" class="form-control"
                                   value="{{ $quotation->chargeable_unit }}" step="0.01" min="0" readonly>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-medium">HS Code</label>
                            <input type="text" name="hs_code" class="form-control"
                                   value="{{ $quotation->hs_code }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium">Description</label>
                            <textarea name="description" class="form-control" rows="2">{{ $quotation->description }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium">Container / Consignment Remarks</label>
                            <textarea name="consignment_remarks" class="form-control" rows="2">{{ $quotation->consignment_remarks }}</textarea>
                        </div>

                    </div>
                </div>
            </div>

            @include('modules.quotation-new.wizard._wizard-footer', ['step' => 3])
        </form>
    </main>
</x-app-layout>
