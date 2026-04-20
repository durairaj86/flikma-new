<div class="modal-header justify-content-between border-bottom py-3" data-close-title="item">
    <div class="row align-items-center bg-white small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $item->name_en ?? 'New Item' }}</span> <small
                    class="text-secondary">{{ $item->sku_code ? ' - ' . $item->sku_code : '' }}</small>
            </div>
        </div>
    </div>
    <div id="show-buttons"></div>
</div>
<div class="modal-body p-0">
    <div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
         data-button-save="Save Item">
        <!-- Main Card -->
        <div class="row">
            <div class="d-flex justify-content-center">
                <div class="d-inline-block p-1">
                    <ul class="nav status-tabs align-items-center border-bottom mb-0 mt-0 justify-content-center"
                        id="modalTabs" role="tablist">
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn active"
                                data-bs-toggle="tab" data-bs-target="#tab-basic"
                                type="button">
                                <i class="bi bi-info-circle me-1"></i> Item Info
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <form id="moduleForm" novalidate action="{{ isset($item->id) ? url('/inventory/items/' . $item->id . '/create') : url('/inventory/items/create') }}">
                @csrf
                <input type="hidden" name="data-id" value="{{ $item->id ?? '' }}">

                <div class="tab-content error-border-off">
                    <!-- Tab 1: Basic Info -->
                    <div class="tab-pane show active" id="tab-basic">
                        <div class="model-form-tab-div">
                            <div class="model-form-sub-title">
                                <h5>General Information</h5>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="form-label required">Item Name (English) <sup class="text-danger">*</sup></label>
                                    <input type="text" name="name_en" class="form-control" required
                                           value="{{ $item->name_en ?? '' }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label required">Item Name (Arabic) <sup class="text-danger">*</sup></label>
                                    <input type="text" name="name_ar" class="form-control" dir="rtl" required
                                           value="{{ $item->name_ar ?? '' }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label required">Account Type <sup class="text-danger">*</sup></label>
                                    <select class="form-control" id="account_type" name="account_type" required>
                                        <option value="">-- Select Account Type --</option>
                                        <option value="expense" {{ (isset($item) && $item->account_type == 'expense') ? 'selected' : '' }}>Expense</option>
                                        <option value="income" {{ (isset($item) && $item->account_type == 'income') ? 'selected' : '' }}>Income</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label">SKU Code</label>
                                    <input type="text" class="form-control" id="sku_code" value="{{ $item->sku_code ?? 'Will be generated automatically' }}" readonly>
                                </div>
                            </div>
                            <div class="model-form-sub-title">
                                <h5>Pricing</h5>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="form-label">Cost Price</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="cost_price" name="cost_price" value="{{ $item->cost_price ?? '' }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label">Selling Price</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="selling_price" name="selling_price" value="{{ $item->selling_price ?? '' }}">
                                    <div class="invalid-feedback" id="selling_price_error"></div>
                                </div>
                            </div>

                            <div class="model-form-sub-title">
                                <h5>Accounting</h5>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="form-label">Cost Account</label>
                                    <select class="form-control" id="cost_account_id" name="cost_account_id">
                                        <option value="">-- Select Cost Account --</option>
                                        @foreach($costAccounts as $account)
                                            <option value="{{ $account->id }}" {{ (isset($item) && $item->cost_account_id == $account->id) ? 'selected' : '' }}>
                                                {{ $account->name }} ({{ $account->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label">Sales Account</label>
                                    <select class="form-control" id="sales_account_id" name="sales_account_id">
                                        <option value="">-- Select Sales Account --</option>
                                        @foreach($salesAccounts as $account)
                                            <option value="{{ $account->id }}" {{ (isset($item) && $item->sales_account_id == $account->id) ? 'selected' : '' }}>
                                                {{ $account->name }} ({{ $account->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <script>
                                $(document).ready(function() {
                                    // Validate selling price is greater than cost price
                                    $('#cost_price, #selling_price').on('change', function() {
                                        validatePrices();
                                    });

                                    // Initial validation
                                    validatePrices();

                                    function validatePrices() {
                                        const costPrice = parseFloat($('#cost_price').val()) || 0;
                                        const sellingPrice = parseFloat($('#selling_price').val()) || 0;

                                        if (costPrice > 0 && sellingPrice > 0 && sellingPrice <= costPrice) {
                                            $('#selling_price').addClass('is-invalid');
                                            $('#selling_price_error').text('Selling price must be greater than cost price');
                                            return false;
                                        } else {
                                            $('#selling_price').removeClass('is-invalid');
                                            $('#selling_price_error').text('');
                                            return true;
                                        }
                                    }

                                    // Override form submission to validate prices
                                    $('#moduleForm').on('submit', function(e) {
                                        if (!validatePrices()) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            return false;
                                        }
                                    });
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
