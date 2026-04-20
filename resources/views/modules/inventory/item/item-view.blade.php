<!-- Item Drawer Offcanvas -->
<div class="offcanvas offcanvas-end item-drawer" tabindex="-1" id="itemDrawer" style="width: 600px;">
    <div class="offcanvas-header border-bottom bg-light px-4 py-3 d-flex justify-content-between align-items-center">
        <h5 id="itemDrawerLabel" class="mb-0 fw-bold">Item Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body px-4 py-3">
        <!-- Tab Content -->
        <div class="tab-pane fade show active" id="overviewTab">
            <div id="itemOverview"></div>
        </div>
    </div>
</div>

<style>
    /* Offcanvas Drawer */
    .item-drawer .offcanvas-header {
        border-bottom: 1px solid #e3e6f0;
    }

    .item-drawer .nav-tabs .nav-link {
        border: none;
        color: #495057;
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }

    .item-drawer .nav-tabs .nav-link.active {
        color: #6366F1;
        border-bottom: 3px solid #6366F1;
        font-weight: 600;
    }

    .item-drawer .nav-tabs .nav-link:hover {
        color: #6366F1;
    }

    .item-drawer ul li {
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
    }

    .item-drawer ul li i {
        font-size: 1rem;
    }
</style>
