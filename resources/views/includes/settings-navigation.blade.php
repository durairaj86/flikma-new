@php
    $segments = request()->segments();
    $segment1 = $segments[0] ?? '';
    $page1 = $segments[1] ?? '';
    $page2 = $segments[2] ?? '';
    $page3 = $segments[3] ?? '';
@endphp

    <!-- TOP SETTINGS NAVIGATION -->
<header class="border-bottom" style="background-color: #f8f9fa; position: sticky; top: 0; z-index: 20;">
    <div class="px-4 pt-3 pb-2 d-flex align-items-center flex-wrap gap-3">
        <h5 class="fw-semibold mb-0 text-secondary me-3">Settings</h5>

        <ul class="nav fw-medium flex-grow-1" id="master-navigation">
            <li class="nav-item" data-url="/settings/account">
                <a href="{{ url('/settings/account') }}"
                   class="nav-link d-flex align-items-center py-2 px-3 {{ request()->is('settings/account*') ? 'active' : 'text-dark' }}">
                    <i class="bi bi-person-circle text-secondary me-2"></i> Account
                </a>
            </li>

            <li class="nav-item" data-url="/settings/company">
                <a href="{{ url('/settings/company') }}"
                   class="nav-link d-flex align-items-center py-2 px-3 {{ request()->is('settings/company*') ? 'active' : 'text-dark' }}">
                    <i class="bi bi-person-circle text-secondary me-2"></i> Manage Business
                </a>
            </li>

            <li class="nav-item" data-url="/settings/invoice">
                <a href="{{ url('/settings/invoice') }}"
                   class="nav-link d-flex align-items-center py-2 px-3 {{ request()->is('settings/invoice*') ? 'active' : 'text-dark' }}">
                    <i class="bi bi-person-circle text-secondary me-2"></i> Invoice Settings
                </a>
            </li>

            {{--<li class="nav-item" data-url="/settings/tax">
                <a href="{{ url('/settings/tax') }}"
                   class="nav-link d-flex align-items-center py-2 px-3 {{ request()->is('settings/tax*') ? 'active' : 'text-dark' }}">
                    <i class="bi bi-person-circle text-secondary me-2"></i> Tax Settings
                </a>
            </li>--}}

            <li class="nav-item" data-url="/settings/zatca/register">
                <a href="{{ url('/settings/zatca/register') }}"
                   class="nav-link d-flex align-items-center py-2 px-3 {{ request()->is('settings/zatca*') ? 'active' : 'text-dark' }}">
                    <i class="bi bi-person-circle text-secondary me-2"></i> Zatca Integration
                </a>
            </li>
        </ul>
    </div>
</header>
<style>
    /* Top nav link base */
    #master-navigation {
        list-style: none;
    }

    #master-navigation li {
        list-style: none;
    }

    #master-navigation .nav-link {
        color: #333;
        border-radius: 6px;
        transition: all 0.25s ease;
        white-space: nowrap;
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
        border-bottom: 2px solid #0d6efd;
    }

    /* Active icon */
    #master-navigation .nav-link.active i {
        color: #0d6efd !important;
    }

    /* Parent button hover */
    #master-navigation button.nav-link:hover {
        background-color: #eef3f8;
    }
</style>
