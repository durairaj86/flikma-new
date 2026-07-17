<div class="offcanvas offcanvas-end customer-drawer" tabindex="-1" id="moduleDrawer" style="width: 60%;">
    <div class="offcanvas-header border-bottom bg-light px-4 py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 id="moduleDrawerLabel" class="mb-0 fw-bold">Expense Details</h5>
            <small class="text-muted" id="drawerSubtitle"></small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body px-4 py-3" id="moduleOverview">
        <div class="text-center py-5 text-muted">
            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
            Loading...
        </div>
    </div>
</div>

<style>
    .customer-drawer .offcanvas-header {
        border-bottom: 1px solid #e3e6f0;
    }
</style>
