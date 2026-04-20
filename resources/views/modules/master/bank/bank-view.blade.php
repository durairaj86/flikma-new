<!-- Customer Drawer Offcanvas -->
<div class="offcanvas offcanvas-end customer-drawer" tabindex="-1" id="bankDrawer" style="width: 600px;">
    <div class="offcanvas-header border-bottom bg-light px-4 py-3 d-flex justify-content-between align-items-center">
        <h5 id="customerDrawerLabel" class="mb-0 fw-bold">Bank Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body px-4 py-3">
        <div class="tab-pane fade show active" id="overviewTab">
            <div id="customerOverview"></div>
        </div>
    </div>
</div>

<style>
    /* Offcanvas Drawer */
    .customer-drawer .offcanvas-header {
        border-bottom: 1px solid #e3e6f0;
    }

    .customer-drawer ul li {
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
    }

    .customer-drawer ul li i {
        font-size: 1rem;
    }
</style>
