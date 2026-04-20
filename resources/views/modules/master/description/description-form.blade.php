<div class="row g-3 align-items-center <!--bg-white--> border-bottom py-2 px-4 mb-1 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $description->name ?? 'New Description' }}</span>
            </div>

        </div>

        <!-- Save & Next Button -->
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container px-4 align-items-center" id="modal-buttons" data-buttons="cancel,save" data-button-save="Save">
    <div class="row">
        <form id="moduleForm" novalidate action="{{ request()->url() }}">
            @csrf
            <input type="hidden" name="data-id" value="{{ $description->id }}">
            <div class="row py-3">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Description <small>(In English)</small> <span class="text-danger">*</span></label>
                    <input type="text" id="description" name="description" class="form-control"
                           value="{{ $description->description }}" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Description <small>(In Arabic)</small> <span class="text-danger">*</span></label>
                    <input type="text" name="description_local" class="form-control"
                           value="{{ $description->description_local }}"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Sale Account</label>
                    <x-common.account-groups :parentAccount="$salesParents" name="sale_account"
                                             :subAccounts="$salesSubAccounts"
                                             :value="$description->sale_account_id"></x-common.account-groups>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Purchase Account</label>
                    <x-common.account-groups :parentAccount="$purchaseParents" name="purchase_account"
                                             :subAccounts="$purchaseSubAccounts"
                                             :value="$description->purchase_account_id"></x-common.account-groups>
                </div>


            </div>
        </form>
    </div>
</div>
