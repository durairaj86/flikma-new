<div class="offcanvas offcanvas-end customer-drawer" tabindex="-1" id="moduleDrawer" style="width: 55%;">
    <div class="offcanvas-header border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
        <div class="flex-grow-1 d-flex align-items-center gap-3">
            <span class="dh-ico"><i class="bi bi-file-earmark-text"></i></span>
            <div>
                <h5 id="moduleDrawerLabel" class="mb-0 fw-bold">{{ __('Quotation Details') }}</h5>
                <small class="dh-sub" id="drawerSubtitle"></small>
            </div>
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
        background: linear-gradient(90deg, #eef0ff, #f8f9ff 60%, #fff);
    }
    .customer-drawer .dh-ico { width: 40px; height: 40px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; background: #6366f1; color: #fff; font-size: 1.15rem; box-shadow: 0 4px 10px rgba(99, 102, 241, .35); }
    .customer-drawer .dh-sub { display: inline-block; margin-top: 2px; padding: 0 10px; border-radius: 999px; background: #e0e3ff; color: #4338ca; font-weight: 700; font-size: .72rem; letter-spacing: .03em; }
    .customer-drawer .dh-sub:empty { display: none; }
    .customer-drawer .da-btn { display: inline-flex; align-items: center; gap: 6px; border: 0; border-radius: 999px; padding: 6px 16px; font-size: .8rem; font-weight: 700; transition: transform .1s, box-shadow .15s, background .15s; }
    .customer-drawer .da-btn:hover { transform: translateY(-1px); }
    .customer-drawer .da-accept { background: #10b981; color: #fff; box-shadow: 0 4px 10px rgba(16, 185, 129, .3); }
    .customer-drawer .da-accept:hover { background: #059669; }
    .customer-drawer .da-cancel { background: #fee2e2; color: #b91c1c; }
    .customer-drawer .da-cancel:hover { background: #fecaca; }
    .customer-drawer .da-soft { background: #eef0ff; color: #4338ca; }
    .customer-drawer .da-soft:hover { background: #e0e3ff; }
    .customer-drawer .da-icon { width: 34px; height: 34px; border: 0; border-radius: 50%; background: #fff; color: #4b5563; box-shadow: 0 1px 3px rgba(45, 58, 79, .18); display: inline-flex; align-items: center; justify-content: center; transition: background .15s, color .15s; }
    .customer-drawer .da-icon:hover { background: #6366f1; color: #fff; }

    .customer-drawer .nav-tabs .nav-link {
        border: none;
        color: #495057;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        border-radius: 0;
        transition: all 0.2s ease;
    }

    .customer-drawer .nav-tabs .nav-link.active {
        color: #4f46e5;
        border-bottom: 2px solid #4f46e5;
        font-weight: 600;
        background: transparent;
    }

    .customer-drawer .nav-tabs .nav-link:hover:not(.active) {
        color: #4f46e5;
        background: rgba(99, 102, 241, 0.06);
    }

    .customer-drawer .nav-tabs {
        border-bottom: 1px solid #dee2e6;
    }
</style>
