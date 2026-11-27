<div class="row g-3 align-items-center border-bottom py-3 px-4 mb-1 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <span class="fw-semibold fs-5">{{ __('Credit limit request') }}</span>
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container-fluid px-0" id="modal-buttons" data-buttons="cancel,save" data-button-save="{{ __('Send for approval') }}">
    <form id="moduleForm" novalidate action="{{ url('finance/credit-approvals/create') }}">
        @csrf
        <div class="px-4 py-3 row g-3">
            <div class="col-md-8">
                <label class="form-label" for="customer">{{ __('Customer') }} <span class="text-danger">*</span></label>
                <x-common.customers :value="$customerId" :required="true" :new="false" name="customer"></x-common.customers>
            </div>
            <div class="col-md-4 small" id="cr_now">
                <div class="text-muted">{{ __('Now') }}</div>
                <div>{{ __('Limit') }}: <span data-cr="limit">—</span> · {{ __('Days') }}: <span data-cr="days">—</span></div>
                <div>{{ __('Outstanding') }}: <span data-cr="outstanding">—</span> · {{ __('Overdue') }}: <span data-cr="overdue">—</span></div>
                <div class="text-warning d-none" data-cr="pending">{{ __('A request is already pending; this one replaces it.') }}</div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="cr_limit">{{ __('New Credit Limit') }}</label>
                <input type="text" class="form-control decimal" id="cr_limit" name="requested_limit">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="cr_days">{{ __('New Credit Days') }}</label>
                <input type="text" class="form-control integer" id="cr_days" name="requested_days" data-max-int="4">
            </div>
            <div class="col-12">
                <label class="form-label" for="cr_reason">{{ __('Reason') }} <span class="text-danger">*</span></label>
                <textarea class="form-control" id="cr_reason" name="reason" rows="3" maxlength="1000" required style="min-height: 80px;"></textarea>
            </div>
        </div>
    </form>
</div>
<script>
    (function () {
        const fmt = (n) => Number(n || 0).toFixed({{ decimals() }}).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        function load(id) {
            if (!id) return;
            $.get(GLOBAL_FN.buildUrl('finance/credit-approvals/customer/' + encodeURIComponent(id)), function (r) {
                $('[data-cr=limit]').text(r.limit ? fmt(r.limit) : '—');
                $('[data-cr=days]').text(r.days || '—');
                $('[data-cr=outstanding]').text(fmt(r.outstanding));
                $('[data-cr=overdue]').text(r.overdue);
                $('[data-cr=pending]').toggleClass('d-none', !r.pending);
            });
        }
        $('#moduleForm').on('change', '#customer', function () { load(this.value); });
        setTimeout(() => load($('#customer').val()), 100);
    })();
</script>
