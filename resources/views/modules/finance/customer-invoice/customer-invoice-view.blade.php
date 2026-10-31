<div class="offcanvas offcanvas-end customer-drawer doc-ui" tabindex="-1" id="moduleDrawer" style="width: 60%;">
    <div class="offcanvas-header border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <span class="dh-ico"><i class="bi bi-receipt"></i></span>
            <div>
                <h5 id="moduleDrawerLabel" class="mb-0 fw-bold">{{ __('Customer Invoice Details') }}</h5>
                <small class="dh-sub" id="drawerSubtitle"></small>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
    </div>

    <div class="offcanvas-body px-4 py-3" id="moduleOverview">
        <div class="text-center py-5 text-muted">
            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
            {{ __('Loading...') }}
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="paymentHistoryDrawer" style="width: 35%;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title mb-0">{{ __('Collection History') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
    </div>
    <div class="offcanvas-body px-4 py-3">
        <div id="paymentHistoryBody"></div>
    </div>
</div>

<style>
    .customer-drawer .offcanvas-header {
        border-bottom: 1px solid #e3e6f0;
    }
</style>

@include('modules.common.doc-ui')
