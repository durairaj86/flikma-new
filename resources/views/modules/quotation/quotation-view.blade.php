<div class="offcanvas offcanvas-end customer-drawer" tabindex="-1" id="moduleDrawer" style="width: 55%;">
    <div class="offcanvas-header border-bottom bg-light px-4 py-3 d-flex justify-content-between align-items-center">
        <div class="flex-grow-1">
            <h5 id="moduleDrawerLabel" class="mb-0 fw-bold">{{ __('Quotation Details') }}</h5>
            <small class="text-muted" id="drawerSubtitle"></small>
        </div>
        <div class="d-flex align-items-center gap-3 ms-auto flex-shrink-0">
            <div class="d-flex align-items-center gap-2" id="drawerActions"></div>
            <button type="button" class="btn-close m-0" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
        </div>
    </div>

    <div class="offcanvas-body p-0">
        <!-- Tab Nav -->
        <ul class="nav nav-tabs px-4 pt-3 border-bottom bg-white" id="quotationDrawerTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active fw-semibold" id="quotation-general-tab"
                        data-bs-toggle="tab" data-bs-target="#quotationGeneralTab" type="button" role="tab">
                    <i class="bi bi-info-circle me-1"></i> {{ __('General') }}
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" id="quotation-container-tab"
                        data-bs-toggle="tab" data-bs-target="#quotationContainerTab" type="button" role="tab">
                    <i class="bi bi-box-seam me-1"></i> {{ __('Container') }}
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" id="quotation-package-tab"
                        data-bs-toggle="tab" data-bs-target="#quotationPackageTab" type="button" role="tab">
                    <i class="bi bi-boxes me-1"></i> {{ __('Package') }}
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" id="quotation-charges-tab"
                        data-bs-toggle="tab" data-bs-target="#quotationChargesTab" type="button" role="tab">
                    <i class="bi bi-receipt me-1"></i> {{ __('Charges') }}
                </button>
            </li>
            {{-- Time frame: icon-only tab at the right end of the tab bar --}}
            <li class="nav-item ms-auto">
                <button class="nav-link fw-semibold" id="quotation-timeframe-tab"
                        data-bs-toggle="tab" data-bs-target="#quotationTimeFrameTab" type="button" role="tab"
                        title="{{ __('Time Frame') }}" aria-label="{{ __('Time Frame') }}">
                    <i class="bi bi-clock-history fs-5"></i>
                </button>
            </li>
        </ul>

        <!-- Tab Content (fully replaced on each drawer open) -->
        <div class="tab-content px-4 py-3" id="moduleOverview">
            <div class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                {{ __('Loading...') }}
            </div>
        </div>
    </div>
</div>

<style>
    .customer-drawer .offcanvas-header {
        border-bottom: 1px solid #e3e6f0;
    }

    .customer-drawer .nav-tabs .nav-link {
        border: none;
        color: #495057;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        border-radius: 0;
        transition: all 0.2s ease;
    }

    .customer-drawer .nav-tabs .nav-link.active {
        color: #0b6aa0;
        border-bottom: 2px solid #0b6aa0;
        font-weight: 600;
        background: transparent;
    }

    .customer-drawer .nav-tabs .nav-link:hover:not(.active) {
        color: #0b6aa0;
        background: rgba(11, 106, 160, 0.05);
    }

    .customer-drawer .nav-tabs {
        border-bottom: 1px solid #dee2e6;
    }
</style>
