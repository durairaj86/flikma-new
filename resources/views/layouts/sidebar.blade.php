<aside class="app-sidebar sidebar-themed">
    <!-- Sidebar Brand -->
    <div class="sidebar-brand d-flex align-items-center justify-content-between">
        <a class="navbar-brand d-flex align-items-center me-2" href="{{ route('dashboard') }}">
            <span class="sidebar-logo-chip d-inline-flex align-items-center justify-content-center flex-shrink-0">
                <img src="{{ asset('img/logos/Flikma_logo.svg') }}" alt="Flikma" class="sidebar-logo-img">
            </span>
        </a>
        <div class="sidebar-brand-actions d-flex align-items-center flex-shrink-0">
            <button type="button" id="sidebarThemeBtn" class="sidebar-theme-btn btn btn-sm border-0 p-1"
                    title="{{ __('Sidebar theme') }}" aria-label="{{ __('Change sidebar theme') }}">
                <i class="bi bi-palette"></i>
            </button>
            <button type="button" id="sidebarToggleBtn" class="sidebar-toggle-btn btn btn-sm border-0 p-1"
                    title="{{ __('Collapse menu') }}" aria-label="{{ __('Toggle sidebar menu') }}">
                <i class="bi bi-chevron-double-left"></i>
            </button>
        </div>
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
                        <p>{{ __('Dashboard') }}</p>
                    </a>
                </li>

                <!-- Customers -->
                @php
                    $customerReportSlugs = ['customer-statement', 'customer-activity-report', 'customer-balance-summary', 'customer-aging', 'customer-aging-all'];
                    $customersOpen = in_array($menu, ['customers', 'customer', 'prospects'])
                        || ($menu == 'reports' && in_array($submenu, $customerReportSlugs));
                @endphp
                <li class="nav-item {{ $customersOpen ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-people-fill"></i>
                        <p>
                            {{ __('Customers') }}
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="/customers" class="nav-link {{ $menu == 'customers' ? 'active' : '' }}"
                               id="menu-customer-list">
                                <p>{{ __('Customer List') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/reports/customer-statement"
                               class="nav-link {{ $submenu == 'customer-statement' ? 'active' : '' }}"
                               id="menu-customer-statement-list">
                                <p>{{ __('Customer Statement') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/reports/customer-balance-summary"
                               class="nav-link {{ $submenu == 'customer-balance-summary' ? 'active' : '' }}">
                                <p>{{ __('Customer Balance Summary') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/reports/customer-activity-report"
                               class="nav-link {{ $submenu == 'customer-activity-report' ? 'active' : '' }}">
                                <p>{{ __('Customer Activity Report') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/reports/customer-aging"
                               class="nav-link {{ $submenu == 'customer-aging' ? 'active' : '' }}"
                               id="menu-customer-aging-list">
                                <p>{{ __('Customer Aging') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/reports/customer-aging-all"
                               class="nav-link {{ $submenu == 'customer-aging-all' ? 'active' : '' }}"
                               id="menu-customer-aging-all-list">
                                <p>{{ __('Customer Aging (All)') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/prospects" class="nav-link {{ $menu == 'prospects' ? 'active' : '' }}"
                               id="menu-prospect-list">
                                <p>{{ __('Prospect') }}</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <!-- Suppliers / Agents -->
                @php
                    $supplierReportSlugs = ['supplier-statement', 'supplier-balance-summary', 'supplier-aging', 'supplier-aging-all'];
                    $suppliersOpen = $menu == 'suppliers'
                        || ($menu == 'reports' && in_array($submenu, $supplierReportSlugs));
                @endphp
                <li class="nav-item {{ $suppliersOpen ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-truck"></i>
                        <p>
                            {{ __('Suppliers / Agents') }}
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="/suppliers" class="nav-link {{ $menu == 'suppliers' ? 'active' : '' }}"
                               id="menu-supplier-list">
                                <p>{{ __('Supplier List') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/reports/supplier-statement"
                               class="nav-link {{ $submenu == 'supplier-statement' ? 'active' : '' }}">
                                <p>{{ __('Supplier Statement') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/reports/supplier-balance-summary"
                               class="nav-link {{ $submenu == 'supplier-balance-summary' ? 'active' : '' }}">
                                <p>{{ __('Supplier Balance Summary') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/reports/supplier-aging"
                               class="nav-link {{ $submenu == 'supplier-aging' ? 'active' : '' }}"
                               id="menu-supplier-aging-list">
                                <p>{{ __('Supplier Aging') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/reports/supplier-aging-all"
                               class="nav-link {{ $submenu == 'supplier-aging-all' ? 'active' : '' }}"
                               id="menu-supplier-aging-all-list">
                                <p>{{ __('Supplier Aging (All)') }}</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Sales -->
                @php
                    $salesOpen = $menu == 'sales' || ($menu == 'reports' && $submenu == 'sale-report');
                @endphp
                <li class="nav-item {{ $salesOpen ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-cart"></i>
                        <p>
                            {{ __('Sales') }}
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="/sales/enquiries" class="nav-link {{ $submenu == 'enquiries' ? 'active' : '' }}">
                                <p>{{ __('Enquiries') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/sales/quotations" class="nav-link {{ $submenu == 'quotations' ? 'active' : '' }}">
                                <p>{{ __('Quotations') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/reports/sale-report" class="nav-link {{ $submenu == 'sale-report' ? 'active' : '' }}">
                                <p>{{ __('Sales Report') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/sales/overview" class="nav-link {{ $submenu == 'overview' ? 'active' : '' }}">
                                <p>{{ __('Overview') }}</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Operations -->
                <li class="nav-item {{ $menu == 'operation' ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-gear"></i>
                        <p>
                            {{ __('Operations') }}
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="/operation/jobs" class="nav-link {{ $submenu == 'jobs' ? 'active' : '' }}">
                                <p>{{ __('Jobs') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/operation/job-overview" class="nav-link {{ $submenu == 'job-overview' ? 'active' : '' }}">
                                <p>{{ __('Job Overview') }}</p>
                            </a>
                        </li>
                        {{--<li class="nav-item">
                            <a href="/operations/tracking"
                               class="nav-link {{ $submenu == 'tracking' ? 'active' : '' }}">
                                <p>{{ __('Shipment Tracking') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/operations/documents"
                               class="nav-link {{ $submenu == 'documents' ? 'active' : '' }}">
                                <p>{{ __('Documents') }}</p>
                            </a>
                        </li>--}}
                    </ul>
                </li>

                <!-- Invoices & Finance -->


                <li class="nav-item {{ in_array($menu,['invoice','adjustment','finance']) ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-wallet2"></i>
                        <p>
                            {{ __('Finances') }}
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item {{ $menu == 'invoice' ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link">
                                <p>
                                    {{ __('Invoices') }}
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview ms-3">
                                <li class="nav-item">
                                    <a href="/invoice/proforma"
                                       class="nav-link {{ $submenu == 'proforma' ? 'active' : '' }}">
                                        <p>{{ __('Proforma Invoice') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/invoice/supplier"
                                       class="nav-link {{ $submenu == 'supplier' ? 'active' : '' }}">
                                        <p>{{ __('Supplier Invoice') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/invoice/customer"
                                       class="nav-link {{ $submenu == 'customer' ? 'active' : '' }}">
                                        <p>{{ __('Customer Invoice') }}</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item {{ $menu == 'adjustment' ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link">
                                <p>
                                    {{ __('Adjustments') }}
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview ms-3">
                                <li class="nav-item">
                                    <a href="/adjustment/credit-note"
                                       class="nav-link {{ $submenu == 'credit-note' ? 'active' : '' }}">
                                        <p>{{ __('Credit Notes') }}</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        {{--<li class="nav-item {{ $menu == 'voucher' ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link">
                                <p>
                                    {{ __('Vouchers') }}
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview ms-3">
                                <li class="nav-item">
                                    <a href="/invoice/proforma"
                                       class="nav-link {{ $submenu == 'proforma' ? 'active' : '' }}">
                                        <p>{{ __('Payments') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/invoice/supplier"
                                       class="nav-link {{ $submenu == 'supplier' ? 'active' : '' }}">
                                        <p>{{ __('Collections') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/invoice/customer"
                                       class="nav-link {{ $submenu == 'customer' ? 'active' : '' }}">
                                        <p>{{ __('Journal Voucher') }}</p>
                                    </a>
                                </li>
                            </ul>
                        </li>--}}
                        <li class="nav-item">
                            <a href="/finance/expense"
                               class="nav-link {{ $submenu == 'expense' ? 'active' : '' }}">
                                <p>{{ __('Expenses') }}</p>
                            </a>
                        </li>
                        {{--<li class="nav-item">
                            <a href="/finance/asset"
                               class="nav-link {{ $submenu == 'asset' ? 'active' : '' }}">
                                <p>{{ __('Assets') }}</p>
                            </a>
                        </li>--}}
                        <li class="nav-item">
                            <a href="/finance/accounts"
                               class="nav-link {{ $submenu == 'accounts' ? 'active' : '' }}">
                                <p>{{ __('Chart of Accounts') }}</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item {{ $menu == 'transaction' ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-cart"></i>
                        <p>
                            {{ __('Transactions') }}
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="/transaction/payments"
                               class="nav-link {{ $submenu == 'payments' ? 'active' : '' }}">
                                <p>{{ __('Payments') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/transaction/collections"
                               class="nav-link {{ $submenu == 'collections' ? 'active' : '' }}">
                                <p>{{ __('Collections') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/transaction/overview"
                               class="nav-link {{ $submenu == 'overview' ? 'active' : '' }}">
                                <p>{{ __('Transactions Overview') }}</p>
                            </a>
                        </li>
                        {{--<li class="nav-item">
                            <a href="/transaction/vouchers" class="nav-link {{ $submenu == 'vouchers' ? 'active' : '' }}">
                                <p>{{ __('Vouchers') }}</p>
                            </a>
                        </li>--}}
                    </ul>
                </li>

                <li class="nav-item {{ $menu == 'bl' ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-cart"></i>
                        <p>
                            {{ __('Bill of Lading') }}
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="/bl/airway-bill" class="nav-link {{ $submenu == 'airway-bill' ? 'active' : '' }}">
                                <p>{{ __('Airway Bill') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/bl/seaway" class="nav-link {{ $submenu == 'seaway' ? 'active' : '' }}">
                                <p>{{ __('Seaway Bill') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/bl/waybill" class="nav-link {{ $submenu == 'waybill' ? 'active' : '' }}">
                                <p>{{ __('Waybill') }}</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item {{ $menu == 'payroll' ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-cart"></i>
                        <p>
                            {{ __('Payroll') }}
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="/payroll/attendance"
                               class="nav-link {{ $submenu == 'collections' ? 'active' : '' }}">
                                <p>{{ __('Attendance') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/payroll/basic/salary"
                               class="nav-link {{ $submenu == 'basic' ? 'active' : '' }}">
                                <p>{{ __('Basic Salary') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/payroll/monthly/salary"
                               class="nav-link {{ $submenu == 'collections' ? 'active' : '' }}">
                                <p>{{ __('Monthly Salary') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/payroll/employee/loan"
                               class="nav-link {{ $submenu == 'employee-loan' ? 'active' : '' }}">
                                <p>{{ __('Employee Loan') }}</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Reports -->
                <li class="nav-item {{ $menu == 'reports' ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-bar-chart-line"></i>
                        <p>
                            {{ __('Reports') }}
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @php
                            $jobReportSlugs = ['job-balance-report', 'job-income-report', 'provisional-report'];
                        @endphp
                        <li class="nav-item {{ in_array($submenu, $jobReportSlugs) ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link">
                                <p>{{ __('Job Report') }} <i class="nav-arrow bi bi-chevron-right"></i></p>
                            </a>
                            <ul class="nav nav-treeview ms-3">
                                {{--<li class="nav-item">
                                    <a href="/reports/job-report"
                                       class="nav-link {{ $submenu == 'job-report' ? 'active' : '' }}">
                                        <p>{{ __('Job Report') }}</p>
                                    </a>
                                </li>--}}
                                <li class="nav-item">
                                    <a href="/reports/job-balance-report"
                                       class="nav-link {{ $submenu == 'job-balance-report' ? 'active' : '' }}">
                                        <p>{{ __('Job Balance Report') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/reports/job-income-report"
                                       class="nav-link {{ $submenu == 'job-income-report' ? 'active' : '' }}">
                                        <p>{{ __('Job Income Report') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/reports/provisional-report"
                                       class="nav-link {{ $submenu == 'provisional-report' ? 'active' : '' }}">
                                        <p>{{ __('Provisional Report') }}</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item {{ $menu == 'reports' ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link">
                                <p>{{ __('Finance Report') }} <i class="nav-arrow bi bi-chevron-right"></i></p>
                            </a>
                            <ul class="nav nav-treeview ms-3">
                                <li class="nav-item">
                                    <a href="/reports/trial-balance"
                                       class="nav-link {{ $submenu == 'trial-balance' ? 'active' : '' }}">
                                        <p>{{ __('Trial Balance') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/reports/balance-sheet"
                                       class="nav-link {{ $submenu == 'balance-sheet' ? 'active' : '' }}">
                                        <p>{{ __('Balance Sheet') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/reports/profit-and-loss"
                                       class="nav-link {{ $submenu == 'profit-and-loss' ? 'active' : '' }}">
                                        <p>{{ __('Profit & Loss Report') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/reports/general-ledger"
                                       class="nav-link {{ $submenu == 'general-ledger' ? 'active' : '' }}">
                                        <p>{{ __('Customer Ledger') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/reports/supplier-ledger"
                                       class="nav-link {{ $submenu == 'supplier-ledger' ? 'active' : '' }}">
                                        <p>{{ __('Supplier Ledger') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/reports/tax-summary"
                                       class="nav-link {{ $submenu == 'tax-summary' ? 'active' : '' }}">
                                        <p>{{ __('Tax Summary') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/reports/input-tax"
                                       class="nav-link {{ $submenu == 'input-tax' ? 'active' : '' }}">
                                        <p>{{ __('Input Tax') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/reports/output-tax"
                                       class="nav-link {{ $submenu == 'output-tax' ? 'active' : '' }}">
                                        <p>{{ __('Output Tax') }}</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a href="/reports/waybill-report"
                               class="nav-link {{ $submenu == 'waybill-report' ? 'active' : '' }}">
                                <p>{{ __('Waybill Report') }}</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Inventory -->
                <li class="nav-item {{ $menu == 'inventory' ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-bar-chart-line"></i>
                        <p>
                            {{ __('Inventory') }}
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="/inventory/items"
                               class="nav-link {{ $submenu == 'items' ? 'active' : '' }}">
                                <p>{{ __('Items') }}</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Masters -->
                <li class="nav-item">
                    <a href="/masters/users" class="nav-link {{ $segment1 == 'masters' ? 'active' : '' }}">
                        <i class="nav-icon bi bi-database"></i>
                        <p>{{ __('Masters') }}</p>
                    </a>
                </li>
                {{--<li class="nav-item {{ $segment1 == 'masters' ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-database"></i>
                        <p>
                            {{ __('Masters') }}
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">

                        <!-- Users -->
                        <li class="nav-item">
                            <a href="/masters/users" class="nav-link {{ $submenu == 'users' ? 'active' : '' }}">
                                <p>{{ __('Users') }}</p>
                            </a>
                        </li>

                        <!-- Transport Directory -->
                        <li class="nav-item">
                            <a href="/masters/transport/directories/seaports"
                               class="nav-link {{ $submenu == 'directories' ? 'active' : '' }}">
                                <p>{{ __('Transport Directory') }}</p>
                            </a>
                        </li>

                        <!-- Reference Data (Submenu with children) -->
                        <!-- Logistics Data -->
                        <li class="nav-item {{ in_array($segment2,['services','package','container','incoterms','currencies','quotation-terms','hs-tariffs','period-closing']) ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link">
                                <p>{{ __('Predefined Data') }} <i class="nav-arrow bi bi-chevron-right"></i></p>
                            </a>
                            <ul class="nav nav-treeview ms-3">
                                <li class="nav-item">
                                    <a href="/masters/services"
                                       class="nav-link {{ $submenu=='services' ? 'active':'' }}">
                                        <p>{{ __('Logistics Services') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/masters/package/codes"
                                       class="nav-link {{ $submenu=='packages' ? 'active':'' }}">
                                        <p>{{ __('Package Codes') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/masters/container/types"
                                       class="nav-link {{ $submenu=='container' ? 'active':'' }}">
                                        <p>{{ __('Container Types') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/masters/incoterms"
                                       class="nav-link {{ $submenu=='incoterms' ? 'active':'' }}">
                                        <p>{{ __('Incoterms') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/masters/quotation-terms"
                                       class="nav-link {{ $submenu=='quotation-terms' ? 'active':'' }}">
                                        <p>{{ __('Quotation Terms') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/masters/hs-tariffs"
                                       class="nav-link {{ $submenu=='hs-tariffs' ? 'active':'' }}">
                                        <p>{{ __('HS Tariff') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/masters/period-closing"
                                       class="nav-link {{ $submenu=='period-closing' ? 'active':'' }}">
                                        <p>{{ __('Period Closing') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/masters/currencies"
                                       class="nav-link {{ $submenu=='currencies' ? 'active':'' }}">
                                        <p>{{ __('Currencies') }}</p>
                                    </a>
                                </li>
                                --}}{{--<li class="nav-item">
                                    <a href="/masters/categories" class="nav-link {{ $submenu=='categories' ? 'active':'' }}">
                                        <p>{{ __('Categories') }}</p>
                                    </a>
                                </li>--}}{{--
                            </ul>
                        </li>

                        <!-- Banks -->
                        <li class="nav-item">
                            <a href="/masters/banks" class="nav-link {{ $submenu == 'banks' ? 'active' : '' }}">
                                <p>{{ __('Banks') }}</p>
                            </a>
                        </li>

                    </ul>
                </li>--}}


                <!-- Billing -->
                <li class="nav-item {{ $menu == 'billing' ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-credit-card"></i>
                        <p>
                            {{ __('Billing') }}
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="/billing" class="nav-link {{ $menu == 'billing' && ! $submenu ? 'active' : '' }}">
                                <p>{{ __('Billing & Subscription') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/billing/ai-usage" class="nav-link {{ $submenu == 'ai-usage' ? 'active' : '' }}">
                                <p>{{ __('AI Usage') }}</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Settings -->
                <li class="nav-item">
                    <a href="/settings/company" class="nav-link {{ $menu == 'settings' ? 'active' : '' }}">
                        <i class="nav-icon bi bi-gear"></i>
                        <p>{{ __('Settings') }}</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>

<style>
    /* ---- Sidebar theme system --------------------------------------------
       Every theme pairs a background with text/icon colors guaranteed to stay
       readable (light-on-dark or dark-on-light, never mixed), so switching
       themes can never make sidebar text or icons vanish. The active theme is
       stored as data-sidebar-theme on <html> and painted before first render
       (see components/layouts/app.blade.php) so it never flashes light first. */
    /* Default theme: the dark gray the sidebar used before this theme system
       existed — Bootstrap's dark secondary background (#343a40) with the same
       muted gray links (#c2c7d0) AdminLTE was drawing. Declared on :root so a
       first-time visitor with no saved preference still gets it. */
    :root,
    html[data-sidebar-theme="gray"] {
        --sb-accent: #7CC4F0;
        --sb-accent-2: #4FA3E3;
        --sb-accent-bg: #3F4A54;
        --sb-accent-soft: #45525E;
        --sb-bg: #343a40;
        --sb-bg-alt: #2b3035;
        --sb-text: #c2c7d0;
        --sb-text-muted: #9aa2ad;
        --sb-label: #8b939e;
        --sb-border: #495057;
        --sb-divider: #495057;
        --sb-hover: #3d444c;
        --sb-danger: #f87171;
        --sb-danger-bg: #4a2b2b;
    }

    /* The reference's light theme, which used to be the default here. */
    html[data-sidebar-theme="light"] {
        --sb-accent: #5B4FE5;
        --sb-accent-2: #8B5CF6;
        --sb-accent-bg: #F4F2FE;
        --sb-bg: #ffffff;
        --sb-bg-alt: #FAFAFB;
        --sb-text: #374151;
        --sb-text-muted: #6B7280;
        --sb-label: #9CA3AF;
        --sb-border: #EEF0F3;
        --sb-divider: #E5E7EB;
        --sb-hover: #F6F5FD;
        --sb-danger: #DC3545;
        --sb-danger-bg: #FEF2F2;
    }

    html[data-sidebar-theme="dark"] {
        --sb-accent: #8B7CF6;
        --sb-accent-2: #5B4FE5;
        --sb-accent-bg: #24213f;
        --sb-accent-soft: #2c2850;
        --sb-bg: #12141c;
        --sb-bg-alt: #171a24;
        --sb-text: #E5E7EB;
        --sb-text-muted: #9CA3AF;
        --sb-label: #6B7280;
        --sb-border: #262a36;
        --sb-divider: #262a36;
        --sb-hover: #1c2030;
        --sb-danger: #f87171;
        --sb-danger-bg: #3a1e22;
    }

    html[data-sidebar-theme="indigo"] {
        --sb-accent: #FBBF24;
        --sb-accent-2: #F59E0B;
        --sb-accent-bg: #322c6e;
        --sb-accent-soft: #3a3480;
        --sb-bg: #1e1b4b;
        --sb-bg-alt: #221f57;
        --sb-text: #E0E7FF;
        --sb-text-muted: #A5B4FC;
        --sb-label: #818CF8;
        --sb-border: #33306e;
        --sb-divider: #33306e;
        --sb-hover: #292468;
        --sb-danger: #FCA5A5;
        --sb-danger-bg: #3a2030;
    }

    html[data-sidebar-theme="ocean"] {
        --sb-accent: #22D3EE;
        --sb-accent-2: #0EA5E9;
        --sb-accent-bg: #0e5871;
        --sb-accent-soft: #12657f;
        --sb-bg: #0b3d54;
        --sb-bg-alt: #093344;
        --sb-text: #E0F2FE;
        --sb-text-muted: #93C5DD;
        --sb-label: #6FA3BC;
        --sb-border: #145169;
        --sb-divider: #145169;
        --sb-hover: #0e4a63;
        --sb-danger: #FCA5A5;
        --sb-danger-bg: #3a2030;
    }

    html[data-sidebar-theme="forest"] {
        --sb-accent: #FBBF24;
        --sb-accent-2: #F59E0B;
        --sb-accent-bg: #1f3a28;
        --sb-accent-soft: #234430;
        --sb-bg: #10291d;
        --sb-bg-alt: #0c2117;
        --sb-text: #DCFCE7;
        --sb-text-muted: #86C9A0;
        --sb-label: #5C9C7B;
        --sb-border: #1c3b2a;
        --sb-divider: #1c3b2a;
        --sb-hover: #163325;
        --sb-danger: #FCA5A5;
        --sb-danger-bg: #3a2020;
    }

    /* The sidebar surface itself. Scoped to #sidebar-container so these win over
       AdminLTE's own .app-sidebar dark background without !important.
       min-height fills the column: AdminLTE sizes .app-sidebar to its content
       only, which left the area below a short menu showing the container's
       background instead of the themed one. */
    #sidebar-container .app-sidebar.sidebar-themed {
        background: var(--sb-bg);
        color: var(--sb-text);
        min-height: 100%;
        border-right-color: var(--sb-border) !important;
        font-family: 'Figtree', -apple-system, 'Inter', sans-serif;
    }

    #sidebar-container .sidebar-brand {
        border-bottom-color: var(--sb-border) !important;
    }

    #sidebar-container .sidebar-wrapper {
        overflow-x: hidden;
        scrollbar-width: thin;
        scrollbar-color: var(--sb-divider) transparent;
    }

    #sidebar-container .sidebar-wrapper::-webkit-scrollbar {
        width: 4px;
    }

    #sidebar-container .sidebar-wrapper::-webkit-scrollbar-thumb {
        background: var(--sb-divider);
        border-radius: 10px;
    }

    /* Top-level rows: pill shape with a left accent bar that lights up when the
       section you're currently in is active. */
    #sidebar-container .sidebar-menu > .nav-item > .nav-link,
    #sidebar-container .sidebar-menu > .nav-item > .nav-link:hover {
        position: relative;
        background: transparent;
        color: var(--sb-text);
        font-size: .85rem;
        font-weight: 500;
        padding: 9px 14px;
        margin: 1px 10px;
        border-radius: 8px;
        border-left: 3px solid transparent;
        transition: background-color .15s ease, color .15s ease;
    }

    #sidebar-container .sidebar-menu .nav-icon {
        color: var(--sb-text-muted);
        font-size: .95rem;
        width: auto;
        margin-right: .5rem;
    }

    #sidebar-container .sidebar-menu > .nav-item > .nav-link:hover,
    #sidebar-container .sidebar-menu > .nav-item > .nav-link:hover .nav-icon {
        background: var(--sb-hover);
        color: var(--sb-accent);
    }

    /* Flikma marks a selected page in one of two places, so both are covered
       here: top-level leaves (Dashboard, Settings) carry `active` on the <li>,
       while submenu rows carry it on the <a> itself. */
    #sidebar-container .sidebar-menu > .nav-item > .nav-link.active,
    #sidebar-container .sidebar-menu > .nav-item.active > .nav-link,
    #sidebar-container .sidebar-menu > .nav-item > .nav-link.active .nav-icon,
    #sidebar-container .sidebar-menu > .nav-item.active > .nav-link .nav-icon {
        background: var(--sb-accent-bg);
        color: var(--sb-accent);
        font-weight: 600;
        border-left-color: var(--sb-accent);
    }

    /* Group headers carry no `active` class of their own — Flikma instead
       auto-opens the group that owns the current page, so the open group is
       what marks "you are in this section". This is the equivalent of the
       reference testing a whole route family on its group button. */
    #sidebar-container .sidebar-menu > .nav-item.menu-open > .nav-link,
    #sidebar-container .sidebar-menu > .nav-item.menu-open > .nav-link .nav-icon {
        background: var(--sb-accent-bg);
        color: var(--sb-accent);
        font-weight: 600;
    }

    /* Submenu rows sit on a continuous rail so the active row's own border
       segment paints over it, giving one connected line with only the
       selected portion highlighted. */
    #sidebar-container .nav-treeview {
        position: relative;
        background: transparent;
        padding: 2px 0;
    }

    #sidebar-container .nav-treeview::before {
        content: '';
        position: absolute;
        top: 2px;
        bottom: 2px;
        left: 25px;
        width: 2px;
        background: var(--sb-divider);
    }

    #sidebar-container .nav-treeview .nav-link,
    #sidebar-container .nav-treeview .nav-link:hover {
        position: relative;
        display: block;
        background: transparent;
        color: var(--sb-text-muted);
        font-size: .8rem;
        font-weight: 500;
        padding: 8px 14px 8px 18px;
        margin: 0 10px 0 26px;
        border-radius: 0 8px 8px 0;
        border-left: 2px solid transparent;
        transition: background-color .15s ease, color .15s ease;
    }

    #sidebar-container .nav-treeview .nav-link:hover {
        background: var(--sb-hover);
        color: var(--sb-accent);
    }

    #sidebar-container .nav-treeview .nav-link.active {
        background: var(--sb-accent-bg);
        color: var(--sb-accent);
        font-weight: 600;
        border-left-color: var(--sb-accent);
    }

    #sidebar-container .nav-arrow {
        color: var(--sb-text-muted);
        font-size: .7rem;
        transition: transform .3s ease-in-out;
    }

    /* AdminLTE centres this absolutely-positioned arrow with
       translateY(-50%) on top of `top: 50%`. Rotating it without keeping that
       translate drops the chevron ~9px, which pinned it against the bottom
       edge of the row whenever the group was open. */
    #sidebar-container .nav-item.menu-open > .nav-link .nav-arrow {
        transform: translateY(-50%) rotate(90deg);
    }

    /* Sign out keeps the danger colour in every theme rather than inheriting
       the accent, so it never reads as a normal section. */
    #sidebar-container .sidebar-menu .nav-link.text-danger,
    #sidebar-container .sidebar-menu .nav-link.text-danger:hover {
        color: var(--sb-danger);
    }

    #sidebar-container .sidebar-menu .nav-link.text-danger:hover {
        background: var(--sb-danger-bg);
    }

    /* RTL mirrors — the accent bar and rail line sit on the physical left in
       LTR; flip them to the opposite edge in RTL. */
    [dir="rtl"] #sidebar-container .sidebar-menu > .nav-item > .nav-link {
        border-left: none;
        border-right: 3px solid transparent;
        border-radius: 8px;
    }

    [dir="rtl"] #sidebar-container .sidebar-menu > .nav-item > .nav-link.active {
        border-right-color: var(--sb-accent);
    }

    [dir="rtl"] #sidebar-container .nav-treeview::before {
        left: auto;
        right: 25px;
    }

    [dir="rtl"] #sidebar-container .nav-treeview .nav-link {
        padding: 8px 18px 8px 14px;
        margin: 0 26px 0 10px;
        border-left: none;
        border-right: 2px solid transparent;
        border-radius: 8px 0 0 8px;
    }

    [dir="rtl"] #sidebar-container .nav-treeview .nav-link.active {
        border-right-color: var(--sb-accent);
    }

    [dir="rtl"] #sidebar-container .sidebar-menu .nav-icon {
        margin-right: 0;
        margin-left: .5rem;
    }

    #sidebar-container {
        position: relative;
        transition: width 0.25s ease;
    }

    /* Collapsed rail: shrink the container AND the inner aside (which otherwise
       keeps AdminLTE's fixed 250px min/max-width and just gets clipped). */
    #sidebar-container.sidebar-collapsed {
        width: 70px;
        overflow: visible; /* let the hover flyout below spill past the 70px rail */
    }

    #sidebar-container.sidebar-collapsed .app-sidebar {
        width: 70px;
        min-width: 70px !important;
        max-width: 70px !important;
        overflow: hidden;
        transition: width 0.2s ease, min-width 0.2s ease, max-width 0.2s ease;
    }

    /* Brand logo — the mark is drawn on a white chip so its own blue/teal
       colors stay legible on the dark themes. The light theme is already light
       behind the logo, so it needs no chip at all. */
    .sidebar-logo-chip {
        background: transparent;
        border-radius: 9px;
        padding: 0;
        line-height: 0;
        margin-right: .5rem;
    }

    html[data-sidebar-theme="gray"] .sidebar-logo-chip,
    html[data-sidebar-theme="dark"] .sidebar-logo-chip,
    html[data-sidebar-theme="indigo"] .sidebar-logo-chip,
    html[data-sidebar-theme="ocean"] .sidebar-logo-chip,
    html[data-sidebar-theme="forest"] .sidebar-logo-chip {
        background: #fff;
        padding: 4px 7px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .22);
    }
    .sidebar-logo-img {
        width: 92px;
        height: auto;
        display: block;
    }

    #sidebar-container.sidebar-collapsed .sidebar-brand {
        justify-content: center;
    }

    /* Collapsed: shrink the chip to just the mark so it fits the 68px rail */
    #sidebar-container.sidebar-collapsed .sidebar-logo-chip {
        margin-right: 0;
    }

    html[data-sidebar-theme="gray"] #sidebar-container.sidebar-collapsed .sidebar-logo-chip,
    html[data-sidebar-theme="dark"] #sidebar-container.sidebar-collapsed .sidebar-logo-chip,
    html[data-sidebar-theme="indigo"] #sidebar-container.sidebar-collapsed .sidebar-logo-chip,
    html[data-sidebar-theme="ocean"] #sidebar-container.sidebar-collapsed .sidebar-logo-chip,
    html[data-sidebar-theme="forest"] #sidebar-container.sidebar-collapsed .sidebar-logo-chip {
        padding: 4px 5px;
    }
    #sidebar-container.sidebar-collapsed .sidebar-logo-img {
        width: 30px;
    }

    /* Hover-to-preview restores the full wordmark */
    #sidebar-container.sidebar-collapsed:hover .sidebar-logo-chip {
        margin-right: .5rem;
        padding: 4px 7px;
    }
    #sidebar-container.sidebar-collapsed:hover .sidebar-logo-img {
        width: 92px;
    }

    #sidebar-container.sidebar-collapsed .sidebar-brand-text,
    #sidebar-container.sidebar-collapsed .sidebar-menu .nav-link p,
    #sidebar-container.sidebar-collapsed .nav-arrow {
        display: none;
    }

    #sidebar-container.sidebar-collapsed .navbar-brand {
        margin-right: 0 !important;
    }

    #sidebar-container.sidebar-collapsed .nav-treeview {
        display: none !important;
    }

    #sidebar-container.sidebar-collapsed .sidebar-menu .nav-link {
        justify-content: center;
        padding-left: 0;
        padding-right: 0;
    }

    /* Hover-to-preview: mousing over the collapsed rail flies the full menu
       out on top of the page content, without pushing/reflowing it. */
    #sidebar-container.sidebar-collapsed:hover .app-sidebar {
        position: absolute;
        top: 0;
        left: 0;
        width: 250px;
        min-width: 250px !important;
        max-width: 250px !important;
        height: 100%;
        overflow-y: auto;
        z-index: 1051;
        box-shadow: 4px 0 16px rgba(0, 0, 0, 0.25);
    }

    #sidebar-container.sidebar-collapsed:hover .sidebar-brand {
        justify-content: space-between;
    }

    #sidebar-container.sidebar-collapsed:hover .sidebar-brand-text,
    #sidebar-container.sidebar-collapsed:hover .sidebar-menu .nav-link p,
    #sidebar-container.sidebar-collapsed:hover .nav-arrow {
        display: inline-block;
    }

    #sidebar-container.sidebar-collapsed:hover .navbar-brand {
        margin-right: 0.5rem !important;
    }

    #sidebar-container.sidebar-collapsed:hover .sidebar-menu .nav-link {
        justify-content: flex-start;
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }

    #sidebar-container.sidebar-collapsed:hover .sidebar-menu .menu-open > .nav-treeview {
        display: block !important;
    }

    /* Collapse + theme buttons get the same muted-to-accent treatment as the
       menu rows so they stay legible in every theme — the previous hardcoded
       #fff was invisible on the light theme. */
    #sidebar-container .sidebar-toggle-btn,
    #sidebar-container .sidebar-theme-btn {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: var(--sb-text-muted);
        background: transparent;
        border: 0;
        border-radius: 6px;
        opacity: .85;
        transition: transform .25s ease, opacity .15s ease, background-color .15s ease, color .15s ease;
    }

    #sidebar-container .sidebar-toggle-btn:hover,
    #sidebar-container .sidebar-theme-btn:hover {
        opacity: 1;
        color: var(--sb-accent);
        background-color: var(--sb-hover);
    }

    #sidebar-container.sidebar-collapsed .sidebar-toggle-btn {
        transform: rotate(180deg);
    }

    /* The 70px collapsed rail only has room for the mark and the collapse
       arrow, so the palette button joins them again on hover-preview. */
    #sidebar-container.sidebar-collapsed .sidebar-theme-btn {
        display: none;
    }

    #sidebar-container.sidebar-collapsed:hover .sidebar-theme-btn {
        display: inline-flex;
    }
</style>

@php
    /* Masters and Settings each render their own secondary sidebar, so the main
       menu collapses to its 70px rail by default on those pages and hands the
       width back to the content. A manual toggle is remembered per section so
       the choice sticks instead of resetting on every page load. */
    $sidebarAutoCollapseSection = in_array($segment1 ?? '', ['masters', 'settings'], true) ? $segment1 : null;
@endphp
<script>
    (function () {
        var sidebarContainer = document.getElementById('sidebar-container');
        var toggleBtn = document.getElementById('sidebarToggleBtn');
        var themeBtn = document.getElementById('sidebarThemeBtn');
        if (!sidebarContainer) {
            return;
        }

        /* ---- Collapse ---------------------------------------------------- */
        if (toggleBtn) {
            var STORAGE_KEY = 'flikma-sidebar-collapsed';
            var SECTION_KEY = 'flikma-sidebar-collapsed-sections';
            var AUTO_SECTION = {!! json_encode($sidebarAutoCollapseSection) !!};

            function readSectionState(section) {
                if (!section) {
                    return null;
                }
                try {
                    var all = JSON.parse(localStorage.getItem(SECTION_KEY) || '{}');
                    return (all && typeof all === 'object' && typeof all[section] === 'boolean') ? all[section] : null;
                } catch (e) {
                    return null;
                }
            }

            function writeSectionState(section, collapsed) {
                if (!section) {
                    return;
                }
                try {
                    var all = JSON.parse(localStorage.getItem(SECTION_KEY) || '{}');
                    if (!all || typeof all !== 'object') {
                        all = {};
                    }
                    all[section] = collapsed;
                    localStorage.setItem(SECTION_KEY, JSON.stringify(all));
                } catch (e) {
                    /* Private mode or quota exceeded: keep the default. */
                }
            }

            function applyState(collapsed) {
                sidebarContainer.classList.toggle('sidebar-collapsed', collapsed);
                toggleBtn.setAttribute('title', collapsed ? 'Expand menu' : 'Collapse menu');
            }

            var sectionState = readSectionState(AUTO_SECTION);
            if (sectionState === null) {
                sectionState = AUTO_SECTION ? true : localStorage.getItem(STORAGE_KEY) === '1';
            }
            applyState(sectionState);

            toggleBtn.addEventListener('click', function () {
                var collapsed = !sidebarContainer.classList.contains('sidebar-collapsed');
                applyState(collapsed);
                localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
                writeSectionState(AUTO_SECTION, collapsed);
            });
        }

        /* ---- Sidebar theme ------------------------------------------------
           Cycles the reference's presets plus the dark gray the sidebar used
           before themes existed, which is the default. The attribute on <html>
           is what the CSS keys off; the head script sets it before first
           paint, and this only handles the click + persistence. */
        if (themeBtn) {
            var THEMES = ['gray', 'light', 'dark', 'indigo', 'ocean', 'forest'];
            var THEME_LABELS = {
                gray: 'Dark Gray',
                light: 'Light',
                dark: 'Dark',
                indigo: 'Indigo',
                ocean: 'Ocean',
                forest: 'Forest'
            };
            var THEME_KEY = 'flikma-sidebar-theme';
            var current = document.documentElement.getAttribute('data-sidebar-theme') || 'gray';
            if (THEMES.indexOf(current) === -1) {
                current = 'gray';
            }

            function describeTheme(theme) {
                var label = 'Sidebar theme: ' + THEME_LABELS[theme];
                themeBtn.setAttribute('title', label);
                themeBtn.setAttribute('aria-label', 'Change ' + label.toLowerCase());
            }

            describeTheme(current);

            themeBtn.addEventListener('click', function () {
                current = THEMES[(THEMES.indexOf(current) + 1) % THEMES.length];
                document.documentElement.setAttribute('data-sidebar-theme', current);
                localStorage.setItem(THEME_KEY, current);
                describeTheme(current);
            });
        }
    })();
</script>
