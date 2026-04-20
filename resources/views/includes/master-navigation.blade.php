@php
    $segments = request()->segments();
    $segment1 = $segments[0] ?? '';
    $page1 = $segments[1] ?? '';
    $page2 = $segments[2] ?? '';
    $page3 = $segments[3] ?? '';
@endphp

    <!-- LEFT SIDEBAR -->
<aside class="border-end d-flex flex-column justify-content-between"
       style="width: 240px; background-color: #f8f9fa; height: 100vh; position: sticky; top: 0;">
    <div class="pt-3 px-3">
        <h5 class="fw-semibold mb-3 text-secondary">Master Data</h5>

        <ul class="nav flex-column fw-medium" id="master-navigation">
            <!-- Users -->
            <li class="nav-item" data-url="/masters/users">
                <a href="{{ asset('/masters/users') }}"
                   class="nav-link d-flex align-items-center py-2 {{ $page1 == 'users' ? 'active' : 'text-dark' }}">
                    <i class="bi bi-person-circle text-secondary me-2"></i> Users
                </a>
            </li>

            <!-- Transport Directory -->
            <li class="nav-item">
                <button class="nav-link d-flex align-items-center py-2 w-100 border-0 bg-transparent text-dark"
                        data-bs-toggle="collapse" data-bs-target="#transportSubmenu"
                        aria-expanded="{{ $page1 == 'transport' ? 'true' : 'false' }}">
                    <i class="bi bi-truck text-secondary me-2"></i> Transport Directory
                    <i class="bi bi-chevron-down ms-auto small"></i>
                </button>
                <ul class="collapse ps-4 {{ $page1 == 'transport' ? 'show' : '' }}" id="transportSubmenu">
                    <li data-url="/masters/transport/directories/seaports">
                        <a href="/masters/transport/directories/seaports"
                           class="nav-link py-1 {{ $page3 == 'seaports' ? 'active' : 'text-dark' }}">
                            <i class="bi bi-file-earmark-text me-2"></i> Seaports
                        </a>
                    </li>
                    <li data-url="/masters/transport/directories/airports">
                        <a href="/masters/transport/directories/airports"
                           class="nav-link py-1 {{ $page3 == 'airports' ? 'active' : 'text-dark' }}">
                            <i class="bi bi-airplane me-2"></i> Airports
                        </a>
                    </li>
                    {{--<li data-url="/masters/transport/directories/shippinglines">
                        <a href="/masters/transport/directories/shippinglines"
                           class="nav-link py-1 {{ $page3 == 'shippinglines' ? 'active' : 'text-dark' }}">
                            <i class="bi bi-ship me-2"></i> Shipping Lines
                        </a>
                    </li>
                    <li data-url="/masters/transport/directories/airlines">
                        <a href="/masters/transport/directories/airlines"
                           class="nav-link py-1 {{ $page3 == 'airlines' ? 'active' : 'text-dark' }}">
                            <i class="bi bi-send me-2"></i> Airlines
                        </a>
                    </li>--}}
                </ul>
            </li>

            <!-- Predefined Data -->
            <li class="nav-item">
                <button class="nav-link d-flex align-items-center py-2 w-100 border-0 bg-transparent text-dark"
                        data-bs-toggle="collapse" data-bs-target="#convertedSubmenu"
                        aria-expanded="{{ in_array($page1,['services','package','container','incoterms','currencies']) ? 'true' : 'false' }}">
                    <i class="bi bi-database-check text-secondary me-2"></i> Predefined Data
                    <i class="bi bi-chevron-down ms-auto small"></i>
                </button>
                <ul class="collapse ps-4 {{ in_array($page1,['services','package','container','incoterms','currencies']) ? 'show' : '' }}"
                    id="convertedSubmenu">
                    <li data-url="/masters/services"><a href="/masters/services"
                                                        class="nav-link py-1 {{ $page1 == 'services' ? 'active' : 'text-dark' }}"><i
                                class="bi bi-tools me-2"></i> Logistic Services</a></li>
                    <li data-url="/masters/activities"><a href="/masters/activities"
                                                        class="nav-link py-1 {{ $page1 == 'activities' ? 'active' : 'text-dark' }}"><i
                                class="bi bi-tools me-2"></i> Logistic Activities</a></li>
                    <li data-url="/masters/package/codes"><a href="/masters/package/codes"
                                                             class="nav-link py-1 {{ $page1 == 'package' ? 'active' : 'text-dark' }}"><i
                                class="bi bi-box me-2"></i> Package Codes</a></li>
                    <li data-url="/masters/container/types"><a href="/masters/container/types"
                                                               class="nav-link py-1 {{ $page1 == 'container' ? 'active' : 'text-dark' }}"><i
                                class="bi bi-grid-3x3-gap me-2"></i> Container Types</a></li>
                    <li data-url="/masters/incoterms"><a href="/masters/incoterms"
                                                         class="nav-link py-1 {{ $page1 == 'incoterms' ? 'active' : 'text-dark' }}"><i
                                class="bi bi-globe me-2"></i> Incoterms</a></li>
                    <li data-url="/masters/currencies"><a href="/masters/currencies"
                                                          class="nav-link py-1 {{ $page1 == 'currencies' ? 'active' : 'text-dark' }}"><i
                                class="bi bi-currency-exchange me-2"></i> Currencies</a></li>
                </ul>
            </li>
            <li class="nav-item" data-url="/masters/banks">
                <a href="{{ asset('/masters/banks') }}"
                   class="nav-link d-flex align-items-center py-2 {{ $page1 == 'banks' ? 'active' : 'text-dark' }}">
                    <i class="bi bi-bank text-secondary me-2"></i> Banks
                </a>
            </li>
            <li class="nav-item" data-url="/masters/descriptions">
                <a href="{{ asset('/masters/descriptions') }}"
                   class="nav-link d-flex align-items-center py-2 {{ $page1 == 'descriptions' ? 'active' : 'text-dark' }}">
                    <i class="bi bi-file-text text-secondary me-2"></i> Descriptions
                </a>
            </li>
            <li class="nav-item" data-url="/masters/units">
                <a href="{{ asset('/masters/units') }}"
                   class="nav-link d-flex align-items-center py-2 {{ $page1 == 'units' ? 'active' : 'text-dark' }}">
                    <i class="bi bi-speedometer2 text-secondary me-2"></i> Units
                </a>
            </li>
            <li class="nav-item" data-url="/masters/salesperson">
                <a href="{{ asset('/masters/salesperson') }}"
                   class="nav-link d-flex align-items-center py-2 {{ $page1 == 'salesperson' ? 'active' : 'text-dark' }}">
                    <i class="bi bi-person-badge text-secondary me-2"></i> Salesperson
                </a>
            </li>
            <li class="nav-item" data-url="/masters/finance/opening-balance">
                <a href="{{ asset('/masters/finance/opening-balance') }}"
                   class="nav-link d-flex align-items-center py-2 {{ $page2 == 'opening-balance' ? 'active' : 'text-dark' }}">
                    <i class="bi bi-person-badge text-secondary me-2"></i> Opening Balance
                </a>
            </li>
        </ul>

        <!-- Reports -->
        {{--<hr>
        <div class="small text-muted text-uppercase">Reports</div>
        <ul class="nav flex-column mt-2">
            <li data-url="/reports/overview">
                <button class="nav-link py-1 w-100 text-start border-0 bg-transparent text-dark"
                        data-bs-toggle="collapse" data-bs-target="#reportSubmenu" aria-expanded="false">
                    <i class="bi bi-bar-chart me-2"></i> Overview
                    <i class="bi bi-chevron-down ms-auto small"></i>
                </button>
                <ul class="collapse ps-4" id="reportSubmenu">
                    <li data-url="/reports/performance"><a href="#" class="nav-link py-1 text-dark"><i
                                class="bi bi-graph-up me-2"></i> Performance</a></li>
                    <li data-url="/reports/sales"><a href="#" class="nav-link py-1 text-dark"><i
                                class="bi bi-receipt me-2"></i> Sales</a></li>
                    <li data-url="/reports/payments"><a href="#" class="nav-link py-1 text-dark"><i
                                class="bi bi-wallet2 me-2"></i> Payments</a></li>
                </ul>
            </li>
        </ul>--}}
    </div>
</aside>
<style>
    /* Sidebar link base */
    #master-navigation li {
        list-style: none;
        padding: 0.1rem 0;
    }

    #master-navigation ul li {
        padding: 0.3rem 0;
    }

    #master-navigation .nav-link {
        color: #333;
        border-radius: 6px;
        transition: all 0.25s ease;
    }

    /* Hover effect */
    #master-navigation .nav-link:hover {
        background-color: #eef3f8;
        color: #0d6efd;
    }

    /* Active state */
    #master-navigation .nav-link.active {
        background-color: #e7f1ff !important;
        color: #0d6efd !important;
        font-weight: 600;
    }

    /* Active icon */
    #master-navigation .nav-link.active i {
        color: #0d6efd !important;
    }

    /* Submenu active indicator */
    #master-navigation .collapse .nav-link.active {
        border-left: 3px solid #0d6efd;
        padding-left: 0.75rem;
    }

    /* Parent button hover */
    #master-navigation button.nav-link:hover {
        background-color: #eef3f8;
    }
</style>
