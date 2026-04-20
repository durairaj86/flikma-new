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
                <li class="nav-item {{ in_array($menu,['customers','customer','prospects']) ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-people-fill"></i>
                        <p>
                            Customers
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="/customers" class="nav-link {{ $menu == 'customers' ? 'active' : '' }}"
                               id="menu-customer-list">
                                <p>Customer List</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/reports/customer-statement"
                               class="nav-link {{ $submenu == 'statement' ? 'active' : '' }}"
                               id="menu-customer-statement-list">
                                <p>Customer Statement</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/reports/customer-aging"
                               class="nav-link {{ $submenu == 'aging' ? 'active' : '' }}"
                               id="menu-customer-aging-list">
                                <p>Customer Aging</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/reports/customer-aging-all"
                               class="nav-link {{ $submenu == 'aging-all' ? 'active' : '' }}"
                               id="menu-customer-aging-all-list">
                                <p>Customer Aging (All)</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/prospects" class="nav-link {{ $menu == 'prospects' ? 'active' : '' }}"
                               id="menu-prospect-list">
                                <p>Prospect</p>
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
                            <a href="/suppliers" class="nav-link {{ $menu == 'suppliers' ? 'active' : '' }}"
                               id="menu-supplier-list">
                                <p>Supplier List</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/reports/supplier-statement"
                               class="nav-link {{ $submenu == 'statement' ? 'active' : '' }}">
                                <p>Supplier Statement</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/reports/supplier-aging"
                               class="nav-link {{ $submenu == 'aging' ? 'active' : '' }}"
                               id="menu-supplier-aging-list">
                                <p>Supplier Aging</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/reports/supplier-aging-all"
                               class="nav-link {{ $submenu == 'aging-all' ? 'active' : '' }}"
                               id="menu-supplier-aging-all-list">
                                <p>Supplier Aging (All)</p>
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


                <li class="nav-item {{ in_array($menu,['invoice','adjustment','finance']) ? 'menu-open' : '' }}">
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
                        <li class="nav-item {{ $menu == 'adjustment' ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link">
                                <p>
                                    Adjustments
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview ms-3">
                                <li class="nav-item">
                                    <a href="/adjustment/credit-note"
                                       class="nav-link {{ $submenu == 'credit-note' ? 'active' : '' }}">
                                        <p>Credit Notes</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        {{--<li class="nav-item {{ $menu == 'voucher' ? 'menu-open' : '' }}">
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
                        </li>--}}
                        <li class="nav-item">
                            <a href="/finance/expense"
                               class="nav-link {{ $submenu == 'expense' ? 'active' : '' }}">
                                <p>Expenses</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/finance/asset"
                               class="nav-link {{ $submenu == 'asset' ? 'active' : '' }}">
                                <p>Assets</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/finance/accounts"
                               class="nav-link {{ $submenu == 'accounts' ? 'active' : '' }}">
                                <p>Chart of Accounts</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item {{ $menu == 'transaction' ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-cart"></i>
                        <p>
                            Transactions
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="/transaction/payments"
                               class="nav-link {{ $submenu == 'payments' ? 'active' : '' }}">
                                <p>Payments</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/transaction/collections"
                               class="nav-link {{ $submenu == 'collections' ? 'active' : '' }}">
                                <p>Collections</p>
                            </a>
                        </li>
                        {{--<li class="nav-item">
                            <a href="/transaction/vouchers" class="nav-link {{ $submenu == 'vouchers' ? 'active' : '' }}">
                                <p>Vouchers</p>
                            </a>
                        </li>--}}
                    </ul>
                </li>

                <li class="nav-item {{ $menu == 'bl' ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-cart"></i>
                        <p>
                            Bill of Lading
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="/bl/airway-bill" class="nav-link {{ $submenu == 'airway-bill' ? 'active' : '' }}">
                                <p>Airway Bill</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/bl/seaway" class="nav-link {{ $submenu == 'seaway' ? 'active' : '' }}">
                                <p>Seaway Bill</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/bl/waybill" class="nav-link {{ $submenu == 'waybill' ? 'active' : '' }}">
                                <p>Waybill</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item {{ $menu == 'payroll' ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-cart"></i>
                        <p>
                            Payroll
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="/payroll/attendance"
                               class="nav-link {{ $submenu == 'collections' ? 'active' : '' }}">
                                <p>Attendance</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/payroll/basic/salary"
                               class="nav-link {{ $submenu == 'basic' ? 'active' : '' }}">
                                <p>Basic Salary</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/payroll/monthly/salary"
                               class="nav-link {{ $submenu == 'collections' ? 'active' : '' }}">
                                <p>Monthly Salary</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/payroll/employee/loan"
                               class="nav-link {{ $submenu == 'employee-loan' ? 'active' : '' }}">
                                <p>Employee Loan</p>
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
                                    <a href="/reports/job-report"
                                       class="nav-link {{ $submenu == 'job-report' ? 'active' : '' }}">
                                        <p>Job Report</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/reports/job-balance-report"
                                       class="nav-link {{ $submenu == 'job-balance-report' ? 'active' : '' }}">
                                        <p>Job Balance Report</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/reports/job-income-report"
                                       class="nav-link {{ $submenu == 'job-income-report' ? 'active' : '' }}">
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
                                    <a href="/reports/sale-report"
                                       class="nav-link {{ $submenu == 'sale-report' ? 'active' : '' }}">
                                        <p>Sales Report</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item {{ $menu == 'reports' ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link">
                                <p>Finance Report <i class="nav-arrow bi bi-chevron-right"></i></p>
                            </a>
                            <ul class="nav nav-treeview ms-3">
                                <li class="nav-item">
                                    <a href="/reports/trial-balance"
                                       class="nav-link {{ $submenu == 'trial-balance' ? 'active' : '' }}">
                                        <p>Trial Balance</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/reports/balance-sheet"
                                       class="nav-link {{ $submenu == 'balance-sheet' ? 'active' : '' }}">
                                        <p>Balance Sheet</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/reports/profit-and-loss"
                                       class="nav-link {{ $submenu == 'profit-and-loss' ? 'active' : '' }}">
                                        <p>Profit & Loss Report</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/reports/general-ledger"
                                       class="nav-link {{ $submenu == 'general-ledger' ? 'active' : '' }}">
                                        <p>General Ledger</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/reports/tax-summary"
                                       class="nav-link {{ $submenu == 'tax-summary' ? 'active' : '' }}">
                                        <p>Tax Summary</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/reports/input-tax"
                                       class="nav-link {{ $submenu == 'input-tax' ? 'active' : '' }}">
                                        <p>Input Tax</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/reports/output-tax"
                                       class="nav-link {{ $submenu == 'output-tax' ? 'active' : '' }}">
                                        <p>Output Tax</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <!-- Inventory -->
                <li class="nav-item {{ $menu == 'inventory' ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-bar-chart-line"></i>
                        <p>
                            Inventory
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="/inventory/items"
                               class="nav-link {{ $submenu == 'items' ? 'active' : '' }}">
                                <p>Items</p>
                            </a>
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
