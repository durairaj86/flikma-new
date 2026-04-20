<div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save" data-button-save="Save Account">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
        <h5 class="fw-semibold mb-0">{{ $account->name ?? 'New Account' }}</h5>
        <div id="show-buttons"></div>
    </div>

    <!-- Form -->
    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" value="{{ $account->id }}">

        <div class="row gy-3">
            <!-- Account Type -->
            <div class="col-md-8 d-flex align-items-center">
                <label class="col-4 col-form-label fw-semibold text-secondary">Account Type <span
                        class="text-danger">*</span></label>
                <div class="col-8">
                    <select name="parent_id" id="parent_id" class="tom-select" data-live-search="true"
                            required>
                        <option value="">Select Parent Account</option>

                        @foreach($accountTypes as $type => $accounts)
                            <optgroup label="{{ ucfirst($type) }}">
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}" @selected($account->parent_id == $acc->id)>
                                        {{ $buildPath($acc) }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Account Name -->
            <div class="col-md-8 d-flex align-items-center">
                <label class="col-4 col-form-label fw-semibold text-secondary">Account Name <span
                        class="text-danger">*</span></label>
                <div class="col-8">
                    <input type="text" class="form-control" name="account_name" value="{{ $account->name }}"
                           placeholder="Enter account name" autocomplete="off" required>
                </div>
            </div>

            <!-- Account Code -->
            <div class="col-md-8 d-flex align-items-center">
                <label class="col-4 col-form-label fw-semibold text-secondary">Account Code</label>
                <div class="col-8">
                    <input type="text" class="form-control" name="account_code" autocomplete="off" value="{{ $account->code }}"
                           placeholder="Enter account code">
                </div>
            </div>

            <!-- Description -->
            <div class="col-md-8 d-flex align-items-start">
                <label class="col-4 col-form-label fw-semibold text-secondary pt-2">Description</label>
                <div class="col-8">
                    <textarea class="form-control h-100" name="description" rows="2"
                              placeholder="Enter description">{{ $account->description }}</textarea>
                </div>
            </div>

            <!-- Active Status -->
            <div class="col-md-8 d-flex align-items-center">
                <label class="col-4 col-form-label fw-semibold text-secondary">Status</label>
                <div class="col-8">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive"
                               value="1" @checked($account->is_active)>
                        <label class="form-check-label fw-semibold" for="isActive">Active</label>
                    </div>
                </div>
            </div>

            <!-- ================= Bank Details ================= -->
            <div class="col-md-8 d-none" id="bankDetailsCard">
                <hr class="my-2">
                <h6 class="fw-semibold text-secondary small mb-3">Bank Details</h6>

                <div class="d-flex align-items-center mb-2">
                    <label class="col-4 col-form-label fw-semibold text-secondary">Account Number</label>
                    <div class="col-8">
                        <input type="text" class="form-control" name="account_number" placeholder="Enter bank account number">
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <label class="col-4 col-form-label fw-semibold text-secondary">Currency</label>
                    <div class="col-8">
                        <select name="currency" id="currency" class="form-control selectpicker" data-live-search="true">
                            <option value="INR">INR - Indian Rupee</option>
                            <option value="USD">USD - US Dollar</option>
                            <option value="EUR">EUR - Euro</option>
                            <option value="GBP">GBP - British Pound</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const accountType = document.getElementById('accountType');
        const parentBox = document.getElementById('parentAccountBox');
        const parentSelect = document.getElementById('parentAccount');
        const selectPicker = $('#parentAccount');
        const isSubAccount = document.getElementById('isSubAccount');
        const bankDetailsCard = document.getElementById('bankDetailsCard');

        // Toggle sub-account
        isSubAccount.addEventListener('change', function () {
            if (this.checked) {
                parentBox.classList.remove('d-none');
            } else {
                parentBox.classList.add('d-none');
                parentSelect.value = '';
                selectPicker.selectpicker('refresh');
            }
        });

        // Handle account type changes
        accountType.addEventListener('change', function () {
            const type = this.value;

            // Show/Hide Bank Details
            if (type === 'bank') {
                bankDetailsCard.classList.remove('d-none');
            } else {
                bankDetailsCard.classList.add('d-none');
            }

            // Fetch Parent Accounts
            if (!type) {
                parentSelect.innerHTML = '<option value="">Select Parent Account</option>';
                selectPicker.selectpicker('refresh');
                return;
            }

            fetch(`/finance/account/get/${type}`)
                .then(res => res.json())
                .then(data => {
                    let options = '<option value="">Select Parent Account</option>';
                    data.forEach(acc => {
                        options += `<option value="${acc.id}">${acc.name}</option>`;
                    });
                    parentSelect.innerHTML = options;
                    selectPicker.selectpicker('refresh');
                })
                .catch(() => {
                    parentSelect.innerHTML = '<option value="">Error loading accounts</option>';
                    selectPicker.selectpicker('refresh');
                });
        });
    });
</script>
