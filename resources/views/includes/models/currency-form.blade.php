<div class="modal fade" id="currencyModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">{{ __('Select Currency') }}</h6>
                <a type="button" class="btn-close" data-bs-dismiss="modal"></a>
            </div>
            <div class="modal-body p-2">
                <input type="text" id="currency-search" class="form-control mb-2" placeholder="{{ __('Search...') }}">

                <ul class="list-group" id="currencyList" style="max-height: 200px; overflow-y: auto;">
                    <li class="list-group-item" data-code="USD">{{ __('USD - US Dollar') }}</li>
                    <li class="list-group-item" data-code="EUR">{{ __('EUR - Euro') }}</li>
                    <li class="list-group-item" data-code="INR">{{ __('INR - Indian Rupee') }}</li>
                    <li class="list-group-item" data-code="GBP">{{ __('GBP - British Pound') }}</li>
                    <li class="list-group-item" data-code="AED">{{ __('AED - UAE Dirham') }}</li>
                    <li class="list-group-item" data-code="SAR">{{ __('SAR - Saudi Riyal') }}</li>
                    <li class="list-group-item" data-code="JPY">{{ __('JPY - Japanese Yen') }}</li>
                    <li class="list-group-item" data-code="CNY">{{ __('CNY - Chinese Yuan') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
