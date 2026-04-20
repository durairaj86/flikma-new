<aside class="app-sidebar bg-body-secondary" data-bs-theme="dark">
    <!-- Sidebar Brand -->
    <div class="sidebar-brand">
        <a href="/dashboard" class="brand-link">
            <span class="brand-text fw-light">FLIKMA V1.0</span>
        </a>
    </div>

    <!-- Sidebar Wrapper -->
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation"
                aria-label="Main navigation" data-accordion="false">

                <!-- Dashboard -->
                <li class="nav-item {{ $menu == 'dashboard' ? 'active' : '' }}">
                    <a href="/dashboard" class="nav-link">
                        <i class="nav-icon bi bi-speedometer"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Customers -->
                <li class="nav-item {{ $menu == 'customers' ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-people-fill"></i>
                        <p>
                            Customers
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="/customers" class="nav-link {{ $submenu == 'customers' ? 'active' : '' }}"
                               id="menu-customer-list">
                                <p>Customer List</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/customer/statement"
                               class="nav-link {{ $submenu == 'customer-statement' ? 'active' : '' }}"
                               id="menu-customer-statement-list">
                                <p>Customer Statement</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Suppliers / Agents -->
                <li class="nav-item {{ $menu == 'suppliers' ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-truck"></i>
                        <p>
                            Suppliers / Agents
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="/suppliers" class="nav-link {{ $submenu == 'supplier-list' ? 'active' : '' }}"
                               id="menu-supplier-list">
                                <p>Supplier List</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/supplier/statement"
                               class="nav-link {{ $submenu == 'supplier-statement' ? 'active' : '' }}">
                                <p>Supplier Statement</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Sales -->
                <li class="nav-item {{ $menu == 'sales' ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-cart"></i>
                        <p>
                            Sales
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="/sales/enquiries" class="nav-link {{ $submenu == 'enquiries' ? 'active' : '' }}">
                                <p>Enquiries</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/sales/quotations" class="nav-link {{ $submenu == 'quotations' ? 'active' : '' }}">
                                <p>Quotations</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/sales/overview" class="nav-link {{ $submenu == 'overview' ? 'active' : '' }}">
                                <p>Overview</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/sales/prospect" class="nav-link {{ $submenu == 'prospect' ? 'active' : '' }}">
                                <p>Prospect</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Operations -->
                <li class="nav-item {{ $menu == 'operation' ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-gear"></i>
                        <p>
                            Operations
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="/operation/jobs" class="nav-link {{ $submenu == 'jobs' ? 'active' : '' }}">
                                <p>Jobs</p>
                            </a>
                        </li>
                        {{--<li class="nav-item">
                            <a href="/operations/tracking"
                               class="nav-link {{ $submenu == 'tracking' ? 'active' : '' }}">
                                <p>Shipment Tracking</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/operations/documents"
                               class="nav-link {{ $submenu == 'documents' ? 'active' : '' }}">
                                <p>Documents</p>
                            </a>
                        </li>--}}
                    </ul>
                </li>

                <!-- Invoices & Finance -->


                <li class="nav-item {{ $menu == 'invoice' ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-wallet2"></i>
                        <p>
                            Finances
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item {{ $menu == 'invoice' ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link">
                                <p>
                                    Invoices
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview ms-3">
                                <li class="nav-item">
                                    <a href="/invoice/proforma"
                                       class="nav-link {{ $submenu == 'proforma' ? 'active' : '' }}">
                                        <p>Proforma Invoice</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/invoice/supplier"
                                       class="nav-link {{ $submenu == 'supplier' ? 'active' : '' }}">
                                        <p>Supplier Invoice</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/invoice/customer"
                                       class="nav-link {{ $submenu == 'customer' ? 'active' : '' }}">
                                        <p>Customer Invoice</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item {{ $menu == 'voucher' ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link">
                                <p>
                                    Vouchers
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview ms-3">
                                <li class="nav-item">
                                    <a href="/invoice/proforma"
                                       class="nav-link {{ $submenu == 'proforma' ? 'active' : '' }}">
                                        <p>Payments</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/invoice/supplier"
                                       class="nav-link {{ $submenu == 'supplier' ? 'active' : '' }}">
                                        <p>Collections</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/invoice/customer"
                                       class="nav-link {{ $submenu == 'customer' ? 'active' : '' }}">
                                        <p>Journal Voucher</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a href="/finance/debit-notes"
                               class="nav-link {{ $submenu == 'expenses' ? 'active' : '' }}">
                                <p>Expenses</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/finance/accounts"
                               class="nav-link {{ $submenu == 'expenses' ? 'active' : '' }}">
                                <p>Chart of Accounts</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Reports -->
                <li class="nav-item {{ $menu == 'reports' ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-bar-chart-line"></i>
                        <p>
                            Reports
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item {{ $menu == 'invoice' ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link">
                                <p>Job Report <i class="nav-arrow bi bi-chevron-right"></i></p>
                            </a>
                            <ul class="nav nav-treeview ms-3">
                                <li class="nav-item">
                                    <a href="/invoice/proforma"
                                       class="nav-link {{ $submenu == 'proforma' ? 'active' : '' }}">
                                        <p>Job Report</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/invoice/supplier"
                                       class="nav-link {{ $submenu == 'supplier' ? 'active' : '' }}">
                                        <p>Job Balance Report</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/invoice/customer"
                                       class="nav-link {{ $submenu == 'customer' ? 'active' : '' }}">
                                        <p>Job Income Report</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item {{ $menu == 'invoice' ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link">
                                <p>Operations Report <i class="nav-arrow bi bi-chevron-right"></i></p>
                            </a>
                            <ul class="nav nav-treeview ms-3">
                                <li class="nav-item">
                                    <a href="/invoice/proforma"
                                       class="nav-link {{ $submenu == 'proforma' ? 'active' : '' }}">
                                        <p>Sales Report</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item {{ $menu == 'invoice' ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link">
                                <p>Finance Report <i class="nav-arrow bi bi-chevron-right"></i></p>
                            </a>
                            <ul class="nav nav-treeview ms-3">
                                <li class="nav-item">
                                    <a href="/invoice/proforma"
                                       class="nav-link {{ $submenu == 'proforma' ? 'active' : '' }}">
                                        <p>Trial Balance</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/invoice/supplier"
                                       class="nav-link {{ $submenu == 'supplier' ? 'active' : '' }}">
                                        <p>Balance Sheet</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/invoice/customer"
                                       class="nav-link {{ $submenu == 'customer' ? 'active' : '' }}">
                                        <p>Profit & Loss Report</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <!-- Masters -->
                <li class="nav-item">
                    <a href="/masters/users" class="nav-link {{ $segment1 == 'masters' ? 'active' : '' }}">
                        <i class="nav-icon bi bi-database"></i>
                        <p>Masters</p>
                    </a>
                </li>
                {{--<li class="nav-item {{ $segment1 == 'masters' ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-database"></i>
                        <p>
                            Masters
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">

                        <!-- Users -->
                        <li class="nav-item">
                            <a href="/masters/users" class="nav-link {{ $submenu == 'users' ? 'active' : '' }}">
                                <p>Users</p>
                            </a>
                        </li>

                        <!-- Transport Directory -->
                        <li class="nav-item">
                            <a href="/masters/transport/directories/seaports"
                               class="nav-link {{ $submenu == 'directories' ? 'active' : '' }}">
                                <p>Transport Directory</p>
                            </a>
                        </li>

                        <!-- Reference Data (Submenu with children) -->
                        <!-- Logistics Data -->
                        <li class="nav-item {{ in_array($segment2,['services','package','container','incoterms','currencies']) ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link">
                                <p>Predefined Data <i class="nav-arrow bi bi-chevron-right"></i></p>
                            </a>
                            <ul class="nav nav-treeview ms-3">
                                <li class="nav-item">
                                    <a href="/masters/services"
                                       class="nav-link {{ $submenu=='services' ? 'active':'' }}">
                                        <p>Logistics Services</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/masters/package/codes"
                                       class="nav-link {{ $submenu=='packages' ? 'active':'' }}">
                                        <p>Package Codes</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/masters/container/types"
                                       class="nav-link {{ $submenu=='container' ? 'active':'' }}">
                                        <p>Container Types</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/masters/incoterms"
                                       class="nav-link {{ $submenu=='incoterms' ? 'active':'' }}">
                                        <p>Incoterms</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/masters/currencies"
                                       class="nav-link {{ $submenu=='currencies' ? 'active':'' }}">
                                        <p>Currencies</p>
                                    </a>
                                </li>
                                --}}{{--<li class="nav-item">
                                    <a href="/masters/categories" class="nav-link {{ $submenu=='categories' ? 'active':'' }}">
                                        <p>Categories</p>
                                    </a>
                                </li>--}}{{--
                            </ul>
                        </li>

                        <!-- Banks -->
                        <li class="nav-item">
                            <a href="/masters/banks" class="nav-link {{ $submenu == 'banks' ? 'active' : '' }}">
                                <p>Banks</p>
                            </a>
                        </li>

                    </ul>
                </li>--}}


                <!-- Settings -->
                <li class="nav-item">
                    <a href="/settings/company" class="nav-link {{ $menu == 'settings' ? 'active' : '' }}">
                        <i class="nav-icon bi bi-gear"></i>
                        <p>Settings</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
