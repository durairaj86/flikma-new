<div class="offcanvas offcanvas-end customer-drawer" tabindex="-1" id="moduleDrawer" style="width: 45%;">
    <div class="offcanvas-header border-bottom bg-light px-4 py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 id="moduleDrawerLabel" class="mb-0 fw-bold">Credit Note Details</h5>
            <small class="text-muted" id="drawerSubtitle"></small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body p-0">
        <!-- Tab Nav -->
        <ul class="nav nav-tabs px-4 pt-3 border-bottom bg-white" id="cnDrawerTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active fw-semibold" id="cn-overview-tab"
                        data-bs-toggle="tab" data-bs-target="#cnOverviewTab" type="button" role="tab">
                    <i class="bi bi-file-earmark-text me-1"></i> Overview
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" id="cn-items-tab"
                        data-bs-toggle="tab" data-bs-target="#cnItemsTab" type="button" role="tab">
                    <i class="bi bi-list-ul me-1"></i> Line Items
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" id="cn-documents-tab"
                        data-bs-toggle="tab" data-bs-target="#cnDocumentsTab" type="button" role="tab">
                    <i class="bi bi-paperclip me-1"></i> Documents
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content px-4 py-3" id="cnDrawerTabContent">

            <!-- Overview Tab -->
            <div class="tab-pane fade show active" id="cnOverviewTab" role="tabpanel">
                <div id="moduleOverview">
                    <div class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        Loading...
                    </div>
                </div>
            </div>

            <!-- Items Tab -->
            <div class="tab-pane fade" id="cnItemsTab" role="tabpanel">
                <div id="cnItemsContent">
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-list-ul fs-2 mb-2 d-block"></i>
                        Select a credit note to view items.
                    </div>
                </div>
            </div>

            <!-- Documents Tab -->
            <div class="tab-pane fade" id="cnDocumentsTab" role="tabpanel">
                <div id="cnDocumentsContent">
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-paperclip fs-2 mb-2 d-block"></i>
                        No documents found.
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    /* Credit Note Drawer */
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
