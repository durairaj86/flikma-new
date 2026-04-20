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
        <h5 class="fw-semibold mb-3 text-secondary">Settings</h5>

        {{--<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-body p-0">
                <ul class="list-group list-group-flush account-nav">
                    <li class="list-group-item active"><i class="bi bi-person-circle me-2"></i> Profile</li>
                    <li class="list-group-item"><i class="bi bi-building me-2"></i> Company Details</li>
                    <li class="list-group-item"><i class="bi bi-credit-card me-2"></i> Billing Settings</li>
                    <li class="list-group-item"><i class="bi bi-shield-lock me-2"></i> Security</li>
                    <li class="list-group-item"><i class="bi bi-people me-2"></i> Users & Permissions</li>
                </ul>
            </div>
        </div>--}}

        <ul class="nav flex-column fw-medium" id="master-navigation">
            <li class="nav-item" data-url="/settings/account">
                <a href="{{ asset('/settings/account') }}"
                   class="nav-link d-flex align-items-center py-2 {{ $page1 == 'account' ? 'active' : 'text-dark' }}">
                    <i class="bi bi-person-circle text-secondary me-2"></i> Account
                </a>
            </li>

            <li class="nav-item" data-url="/settings/company">
                <a href="{{ asset('/settings/company') }}"
                   class="nav-link d-flex align-items-center py-2 {{ $page1 == 'company' ? 'active' : 'text-dark' }}">
                    <i class="bi bi-person-circle text-secondary me-2"></i> Manage Business
                </a>
            </li>

            <li class="nav-item" data-url="/settings/invoice">
                <a href="{{ asset('/settings/invoice') }}"
                   class="nav-link d-flex align-items-center py-2 {{ $page1 == 'invoice' ? 'active' : 'text-dark' }}">
                    <i class="bi bi-person-circle text-secondary me-2"></i> Invoice Settings
                </a>
            </li>

            {{--<li class="nav-item" data-url="/settings/tax">
                <a href="{{ asset('/settings/tax') }}"
                   class="nav-link d-flex align-items-center py-2 {{ $page1 == 'tax' ? 'active' : 'text-dark' }}">
                    <i class="bi bi-person-circle text-secondary me-2"></i> Tax Settings
                </a>
            </li>--}}

            <li class="nav-item" data-url="/settings/zatca/register">
                <a href="{{ asset('/settings/zatca/register') }}"
                   class="nav-link d-flex align-items-center py-2 {{ $page1 == 'zatca' ? 'active' : 'text-dark' }}">
                    <i class="bi bi-person-circle text-secondary me-2"></i> Zatca Integration
                </a>
            </li>
        </ul>
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
