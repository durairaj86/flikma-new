<div class="container-fluid px-0" id="costing-modal-wrap">
    <form id="costingForm" novalidate>
        @csrf
        <input type="hidden" name="charge_id" value="{{ $charge->id ?? '' }}">
        <input type="hidden" name="quotation_new_id" value="{{ $quotation->id }}">

        {{-- ─── CHARGE section (green header) ──────────────────────────── --}}
        <div class="section-header text-white fw-semibold px-3 py-2"
             style="background-color:#5b9a39;">Charge</div>
        <div class="px-3 pt-3 pb-2">

            {{-- Row 1: Line No | Charge | Description | PP/CC --}}
            <div class="row g-3 mb-3">
                <div class="col-md-2">
                    <label class="form-label fw-medium">Line No</label>
                    <input type="text" name="line_no" class="form-control"
                           value="{{ $charge->sort_order ?? 10 }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">
                        Charge <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="charge_description" class="form-control" required
                           value="{{ $charge->charge_description ?? '' }}"
                           placeholder="e.g. AIR WAY BILL | BXB | GST18">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">
                        Description <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="charge_desc_label" class="form-control" required
                           value="{{ $charge->charge_description ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-medium">
                        PP/CC <span class="text-danger">*</span>
                    </label>
                    <select name="freight" class="tom-select" required>
                        <option value="">--</option>
                        <option value="Prepaid" @selected(($charge->freight ?? '') === 'Prepaid')>Prepaid</option>
                        <option value="Collect" @selected(($charge->freight ?? '') === 'Collect')>Collect</option>
                    </select>
                </div>
            </div>

            {{-- Row 2: Unit | Quantity | OFD Type --}}
            <div class="row g-3 mb-3">
                <div class="col-md-5">
                    <label class="form-label fw-medium">Unit</label>
                    <input type="text" name="unit" class="form-control"
                           value="{{ $charge->unit ?? '' }}"
                           placeholder="e.g. 20' DRY CONTAINER-(20'DRY)">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Quantity</label>
                    <input type="number" name="qty" class="form-control costing-qty"
                           value="{{ $charge->qty ?? 1 }}" min="1">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">
                        OFD Type <span class="text-danger">*</span>
                    </label>
                    <select name="ofd_type" class="tom-select" required>
                        <option value="">--Select--</option>
                        @foreach(['ORIGIN','DESTINATION','N/A','OTHERS'] as $ofd)
                            <option value="{{ $ofd }}" @selected(($charge->ofd_type ?? '') === $ofd)>{{ $ofd }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Row 3: Remarks (full width) --}}
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-medium">Remarks</label>
                    <textarea name="charge_remarks" class="form-control" rows="2">{{ $charge->sale_remarks ?? '' }}</textarea>
                </div>
            </div>
        </div>

        {{-- ─── SALE section (orange header) ───────────────────────────── --}}
        <div class="section-header fw-semibold px-3 py-2 mt-1"
             style="background-color:#f0a500;">Sale</div>
        <div class="px-3 pt-3 pb-2">

            {{-- Row 1: Bill To | Currency | Ex.Rate --}}
            <div class="row g-3 mb-3">
                <div class="col-md-5">
                    <label class="form-label fw-medium">Bill To</label>
                    <x-common.customers :value="$charge->bill_to_id ?? null"></x-common.customers>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Currency</label>
                    <input type="text" name="currency" class="form-control"
                           value="{{ $charge->currency ?? 'INR' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Ex.Rate</label>
                    <input type="number" name="ex_rate" class="form-control costing-ex-rate"
                           value="{{ $charge->ex_rate ?? 1 }}" step="0.000001" min="0">
                </div>
            </div>

            {{-- Row 2: Amount/Qty (bold) | FCY Amount | Amount (INR) --}}
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Amount / Qty</label>
                    <input type="number" name="qty_amount" class="form-control fw-bold costing-qty-amount"
                           value="{{ $charge->qty_amount ?? '' }}" step="0.01" min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">FCY Amount</label>
                    <input type="number" name="fcy_amount" class="form-control costing-fcy"
                           value="{{ $charge->fcy_amount ?? '' }}" step="0.01" min="0" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Amount (INR)</label>
                    <input type="number" name="amount_inr" class="form-control costing-amount-inr"
                           value="{{ $charge->amount_inr ?? '' }}" step="0.01" min="0" readonly>
                </div>
            </div>

            {{-- Row 3: Dr/Cr | Tax Group Code | Taxable Amount --}}
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-medium">Dr / Cr</label>
                    <select name="dr_cr" class="tom-select">
                        <option value="Cr(+)" @selected(($charge->dr_cr ?? 'Cr(+)') === 'Cr(+)')>Cr(+)</option>
                        <option value="Dr(-)" @selected(($charge->dr_cr ?? '') === 'Dr(-)')>Dr(-)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Tax Group Code</label>
                    <select name="tax_group_code" class="tom-select">
                        <option value="">--Select--</option>
                        @foreach(['GST 0%','GST 5%','GST 12%','GST 18%','GST 28%'] as $tax)
                            <option value="{{ $tax }}" @selected(($charge->tax_group_code ?? '') === $tax)>{{ $tax }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Taxable Amount</label>
                    <input type="number" name="taxable_amount" class="form-control costing-taxable"
                           value="{{ $charge->taxable_amount ?? '' }}" step="0.01" min="0" readonly>
                </div>
            </div>

            {{-- Row 4: Tax Amount --}}
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-medium">Tax Amount</label>
                    <input type="number" name="tax_amount_sale" class="form-control costing-tax-amount"
                           value="{{ $charge->tax_amount_sale ?? 0 }}" step="0.01" min="0" readonly>
                </div>
            </div>

            {{-- Row 5: Remarks (left) | Gross Profit (right, same row) --}}
            <div class="row g-3">
                <div class="col-md-9">
                    <label class="form-label fw-medium">Remarks</label>
                    <textarea name="sale_remarks" class="form-control" rows="3">{{ $charge->sale_remarks ?? '' }}</textarea>
                </div>
                <div class="col-md-3 d-flex flex-column justify-content-end">
                    <div class="text-muted fw-medium mb-1">Gross Profit</div>
                    <div class="fw-bold fs-4 text-dark" id="grossProfitDisplay">
                        {{ number_format(($charge->qty_amount ?? 0) - ($charge->cost_amount ?? 0), 0) }}
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── COST section (blue header) ──────────────────────────────── --}}
        <div class="section-header text-white fw-semibold px-3 py-2 mt-1"
             style="background-color:#0d6efd;">Cost</div>
        <div class="px-3 pt-3 pb-3">
            {{-- Row 1: Vendor | Reference No. | Date --}}
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-medium">Vendor</label>
                    <x-common.suppliers :value="$charge->vendor_id ?? null"></x-common.suppliers>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Reference No.</label>
                    <input type="text" name="reference_no" class="form-control"
                           value="{{ $charge->reference_no ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-medium">Date</label>
                    <input type="date" name="cost_date" class="form-control datepicker"
                           value="{{ $charge->cost_date ?? '' }}">
                </div>
            </div>
        </div>

    </form>

    {{-- ─── Footer Buttons ──────────────────────────────────────────────── --}}
    {{-- Image 4: X Cancel | ? Help | Delete (red) ... Save and Close | Save and Stay --}}
    <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-danger btn-sm" id="btn-cancel-costing">
                <i class="bi bi-x me-1"></i> Cancel
            </button>
            <button type="button" class="btn btn-secondary btn-sm" id="btn-help-costing">
                <i class="bi bi-question-circle me-1"></i> Help
            </button>
            @if(isset($charge->id))
            <button type="button" class="btn btn-danger btn-sm" id="btn-delete-costing"
                    data-charge-id="{{ $charge->id }}">
                <i class="bi bi-trash me-1"></i> Delete
            </button>
            @endif
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success btn-sm" id="btn-save-close-costing">
                <i class="bi bi-save me-1"></i> Save and Close
            </button>
            <button type="button" class="btn btn-success btn-sm" id="btn-save-stay-costing">
                <i class="bi bi-save2 me-1"></i> Save and Stay
            </button>
        </div>
    </div>
</div>
