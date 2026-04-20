<!-- Customer Drawer Offcanvas -->
<div class="offcanvas offcanvas-end customer-drawer" tabindex="-1" id="supplierDrawer" style="width: 600px;">
    <div class="offcanvas-header border-bottom bg-light px-4 py-3 d-flex justify-content-between align-items-center">
        <h5 id="customerDrawerLabel" class="mb-0 fw-bold">Supplier Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body px-4 py-3">
        <!-- Tabs -->
        {{--<ul class="nav nav-tabs mb-4" id="customerDrawerTabs" role="tablist">
            <li class="nav-item me-2">
                <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#overviewTab"
                        type="button">
                    Overview
                </button>
            </li>
            <li class="nav-item me-2">
                <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#invoicesTab" type="button">
                    Invoices
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#transactionsTab"
                        type="button">
                    Transactions
                </button>
            </li>
        </ul>--}}

        <!-- Tab Content -->
        <div class="tab-pane fade show active" id="overviewTab">
            <div id="customerOverview"></div>
        </div>

        <!-- Invoices -->
        <div class="tab-pane fade" id="invoicesTab">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Recent Invoices</h6>
                    <div id="customerInvoices"></div>
                </div>
            </div>
        </div>

        <!-- Transactions -->
        <div class="tab-pane fade" id="transactionsTab">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Recent Transactions</h6>
                    <div id="customerTransactions"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Offcanvas Drawer */
    .customer-drawer .offcanvas-header {
        border-bottom: 1px solid #e3e6f0;
    }

    .customer-drawer .nav-tabs .nav-link {
        border: none;
        color: #495057;
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }

    .customer-drawer .nav-tabs .nav-link.active {
        color: #6366F1;
        border-bottom: 3px solid #6366F1;
        font-weight: 600;
    }

    .customer-drawer .nav-tabs .nav-link:hover {
        color: #6366F1;
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
