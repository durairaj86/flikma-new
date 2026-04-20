<div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="Save Customer">
    <!-- Meta Info -->
    <div class="row g-3 align-items-center bg-white border-bottom py-2 mb-3 small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                <div class="module-info">
                    <span class="fw-semibold fs-5">{{ $bank->bank_name ?? 'New Bank Account' }}</span> <small
                        class="text-secondary">{{ $bank->account_number ? ' - ' . $bank->account_number : '' }}</small>
                </div>

            </div>

            <!-- Save & Next Button -->
            <div id="show-buttons"></div>
        </div>
    </div>
    <div class="row">
        <form id="bankForm" novalidate action="{{ request()->url() }}">
            @csrf
            <input type="hidden" name="data-id" value="{{ $bank->id ?? '' }}">

            <div class="model-form-tab-div">
                <div class="row">
                    <!-- Account Holder -->
                    <div class="col-4 form-group">
                        <label class="form-label">Account Holder</label>
                        <input type="text" name="account_holder" id="account-holder" class="form-control" required
                               value="{{ $bank->account_holder ?? '' }}">
                    </div>

                    <!-- Account Number -->
                    <div class="col-4 form-group">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="account_number" id="account-number" class="form-control" required
                               value="{{ $bank->account_number ?? '' }}">
                    </div>

                    <!-- Bank -->
                    <div class="col-4 form-group">
                        <label class="form-label">Bank Name</label>
                        <input type="text" name="bank" id="bank" class="form-control" required
                               value="{{ $bank->bank_name ?? '' }}">
                    </div>
                </div>

                <div class="row">
                    <!-- Branch -->
                    <div class="col-4 form-group">
                        <label class="form-label">Branch</label>
                        <input type="text" name="branch" id="branch" class="form-control"
                               value="{{ $bank->branch_name ?? '' }}">
                    </div>

                    <!-- IBAN Code -->
                    <div class="col-4 form-group">
                        <label class="form-label">IBAN Code</label>
                        <input type="text" name="iban_code" id="iban-code" class="form-control" maxlength="32"
                               value="{{ $bank->iban_code ?? '' }}">
                    </div>

                    <!-- Swift Code -->
                    <div class="col-4 form-group">
                        <label class="form-label">SWIFT Code</label>
                        <input type="text" name="swift_code" id="swift-code" class="form-control"
                               value="{{ $bank->swift_code ?? '' }}">
                    </div>
                </div>

                <div class="row">


                    <!-- Currency (Tally-style selection) -->
                    <div class="col-4 form-group">
                        <label class="form-label">Currency</label>
                        <input type="text" name="currency" id="currency-field" data-open-modal="currency"
                               class="form-control modal-option" readonly
                               value="{{ $bank->currency ?? '' }}"
                               placeholder="Select Currency">
                    </div>
                    <!-- Sort Code -->
                    <div class="col-4 form-group">
                        <label class="form-label">Sort Code</label>
                        <input type="text" name="sort_code" id="sort-code" class="form-control"
                               value="{{ $bank->sort ?? '' }}">
                    </div>

                    <!-- Bank Address -->
                    <div class="col-4 form-group">
                        <label class="form-label">Bank Address</label>
                        <textarea name="bank_address" id="bank-address" class="form-control"
                                  rows="1">{{ $bank->bank_address ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Currency Modal -->
<!-- Currency Modal (no extra backdrop) -->
@include('includes.models.currency-form')
