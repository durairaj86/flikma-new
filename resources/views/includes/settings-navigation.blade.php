@php
    $segments = request()->segments();
    $segment1 = $segments[0] ?? '';
    $page1 = $segments[1] ?? '';
    $page2 = $segments[2] ?? '';
    $page3 = $segments[3] ?? '';
@endphp

    <!-- LEFT SIDEBAR -->
<aside class="border-end d-flex flex-column justify-content-between flex-shrink-0"
       style="width: 240px; background-color: #f8f9fa; height: 100vh; position: sticky; top: 0;">
    <div class="pt-3 px-3">
        <a href="{{ url('/dashboard') }}" id="back-to-dashboard"
           class="d-flex align-items-center text-decoration-none text-secondary fw-medium py-2 mb-3">
            <i class="bi bi-arrow-left me-2"></i> {{ __('Back to Dashboard') }}
        </a>

        <h5 class="fw-semibold mb-3 text-secondary">Settings</h5>

        <ul class="nav flex-column fw-medium" id="settings-navigation">
            <!-- Account -->
            <li class="nav-item" data-url="/settings/account">
                <a href="{{ url('/settings/account') }}"
                   class="nav-link d-flex align-items-center py-2 {{ request()->is('settings/account*') ? 'active' : 'text-dark' }}">
                    <i class="bi bi-person-circle text-secondary me-2"></i> Account
                </a>
            </li>

            <!-- Manage Business -->
            <li class="nav-item" data-url="/settings/company">
                <a href="{{ url('/settings/company') }}"
                   class="nav-link d-flex align-items-center py-2 {{ request()->is('settings/company*') ? 'active' : 'text-dark' }}">
                    <i class="bi bi-building text-secondary me-2"></i> Manage Business
                </a>
            </li>

            <!-- Invoice Settings -->
            <li class="nav-item" data-url="/settings/invoice">
                <a href="{{ url('/settings/invoice') }}"
                   class="nav-link d-flex align-items-center py-2 {{ request()->is('settings/invoice*') ? 'active' : 'text-dark' }}">
                    <i class="bi bi-receipt text-secondary me-2"></i> Invoice Settings
                </a>
            </li>

            {{--<li class="nav-item" data-url="/settings/tax">
                <a href="{{ url('/settings/tax') }}"
                   class="nav-link d-flex align-items-center py-2 {{ request()->is('settings/tax*') ? 'active' : 'text-dark' }}">
                    <i class="bi bi-percent text-secondary me-2"></i> Tax Settings
                </a>
            </li>--}}

            <!-- Zatca Integration -->
            <li class="nav-item" data-url="/settings/zatca/register">
                <a href="{{ url('/settings/zatca/register') }}"
                   class="nav-link d-flex align-items-center py-2 {{ request()->is('settings/zatca*') ? 'active' : 'text-dark' }}">
                    <i class="bi bi-upc-scan text-secondary me-2"></i> Zatca Integration
                </a>
            </li>
        </ul>
    </div>
</aside>
<style>
    /* Back to dashboard */
    #back-to-dashboard {
        border-bottom: 1px solid #dee2e6;
        padding-bottom: .75rem;
        margin-bottom: 1rem !important;
    }
    #back-to-dashboard:hover { color: #0d6efd !important; }

    /* Sidebar link base */
    #settings-navigation li {
        list-style: none;
        padding: 0.1rem 0;
    }

    #settings-navigation ul li {
        padding: 0.3rem 0;
    }

    #settings-navigation .nav-link {
        color: #333;
        border-radius: 6px;
        transition: all 0.25s ease;
    }

    /* Hover effect */
    #settings-navigation .nav-link:hover {
        background-color: #eef3f8;
        color: #0d6efd;
    }

    /* Active state */
    #settings-navigation .nav-link.active {
        background-color: #e7f1ff !important;
        color: #0d6efd !important;
        font-weight: 600;
    }

    /* Active icon */
    #settings-navigation .nav-link.active i {
        color: #0d6efd !important;
    }

    /* Submenu active indicator */
    #settings-navigation .collapse .nav-link.active {
        border-left: 3px solid #0d6efd;
        padding-left: 0.75rem;
    }

    /* Parent button hover */
    #settings-navigation button.nav-link:hover {
        background-color: #eef3f8;
    }
</style>
