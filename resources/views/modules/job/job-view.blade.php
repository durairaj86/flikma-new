<div class="offcanvas offcanvas-end customer-drawer" tabindex="-1" id="moduleDrawer" style="width: 55%;">
    <div class="offcanvas-header border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <span class="dh-ico"><i class="bi bi-briefcase"></i></span>
            <div>
                <h5 id="moduleDrawerLabel" class="mb-0 fw-bold">{{ __('Job Details') }}</h5>
                <small class="dh-sub" id="drawerSubtitle"></small>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
    </div>

    <div class="offcanvas-body p-0">
        <!-- Tab Nav -->
        <ul class="nav nav-tabs px-4 pt-3 border-bottom bg-white" id="jobDrawerTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active fw-semibold" id="job-general-tab"
                        data-bs-toggle="tab" data-bs-target="#jobGeneralTab" type="button" role="tab">
                    <i class="bi bi-info-circle me-1"></i> {{ __('General') }}
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" id="job-clearance-tab"
                        data-bs-toggle="tab" data-bs-target="#jobClearanceTab" type="button" role="tab">
                    <i class="bi bi-shield-check me-1"></i> {{ __('Clearance') }}
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" id="job-container-tab"
                        data-bs-toggle="tab" data-bs-target="#jobContainerTab" type="button" role="tab">
                    <i class="bi bi-box-seam me-1"></i> {{ __('Container') }}
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" id="job-package-tab"
                        data-bs-toggle="tab" data-bs-target="#jobPackageTab" type="button" role="tab">
                    <i class="bi bi-boxes me-1"></i> {{ __('Package') }}
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" id="job-finance-tab"
                        data-bs-toggle="tab" data-bs-target="#jobFinanceTab" type="button" role="tab">
                    <i class="bi bi-cash-coin me-1"></i> {{ __('Financials') }}
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" id="job-timeline-tab"
                        data-bs-toggle="tab" data-bs-target="#jobTimelineTab" type="button" role="tab">
                    <i class="bi bi-clock-history me-1"></i> {{ __('Timeline') }}
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" id="job-documents-tab"
                        data-bs-toggle="tab" data-bs-target="#jobDocumentsTab" type="button" role="tab">
                    <i class="bi bi-paperclip me-1"></i> {{ __('Documents') }}
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
