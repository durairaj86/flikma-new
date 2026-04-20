<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-1">
    <div class="container">
        <div>&nbsp;</div>

        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                {{--<li class="nav-item"><a class="nav-link text-white-50" href="/party/customers">Parties</a></li>
                <li class="nav-item"><a class="nav-link text-white-50" href="#">Sales</a></li>
                <li class="nav-item"><a class="nav-link text-white-50" href="/post-project">Operations</a></li>
                <li class="nav-item"><a class="nav-link text-white-50" href="/place-bid">Finance</a></li>
                <li class="nav-item"><a class="nav-link text-white-50" href="/place-bid">Transactions</a></li>
                <li class="nav-item"><a class="nav-link text-white-50" href="/place-bid">Bill of Lading</a></li>
                <li class="nav-item"><a class="nav-link text-white-50" href="/place-bid">Payroll</a></li>
                <li class="nav-item"><a class="nav-link text-white-50" href="/place-bid">Reports</a></li>--}}
            </ul>

            <div class="d-flex align-items-center gap-3 text-white-50">
                <i class="bi bi-bell pointer"></i>
                <i class="bi bi-envelope pointer"></i>
                <div class="vr mx-2 bg-secondary" style="height: 20px;"></div>
                @auth
                    <div class="dropdown">
                        <div class="d-flex align-items-center gap-2 pointer" data-bs-toggle="dropdown"
                             aria-expanded="false">
                            <span class="small d-none d-md-inline text-white">{{ Auth::user()->name }}</span>
                            <div class="position-relative">
                                <img
                                    src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0D6EFD&color=fff"
                                    class="rounded-circle border border-secondary" width="32">
                                <span
                                    class="position-absolute bottom-0 end-0 bg-success border border-dark rounded-circle"
                                    style="width: 10px; height: 10px;"></span>
                            </div>
                        </div>

                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-3 mt-2"
                            style="width: 280px; font-size: 14px;">
                            <li class="px-3 py-1 text-uppercase small fw-bold text-muted" style="font-size: 11px;">
                                Account
                            </li>
                            <li><a class="dropdown-item py-2" href="/u/user"><i class="bi bi-person me-2"></i> View
                                    Profile</a></li>
                            <li><a class="dropdown-item py-2" href="/membership"><i class="bi bi-star me-2"></i>
                                    Membership</a></li>
                            <li><a class="dropdown-item py-2" href="/analytics"><i class="bi bi-bar-chart me-2"></i>
                                    Account Analytics</a></li>
                            <li><a class="dropdown-item py-2" href="/insights"><i class="bi bi-lightbulb me-2"></i> Bid
                                    Insights</a></li>
                            <li><a class="dropdown-item py-2" href="/user/settings/profile"><i
                                        class="bi bi-gear me-2"></i> Settings</a></li>

                            <li>
                                <hr class="dropdown-divider mx-2">
                            </li>

                            <li class="px-3 py-2 d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-moon-stars me-2"></i> Theme</span>
                                <span class="badge bg-light text-dark border fw-normal">Light</span>
                            </li>

                            <li>
                                <hr class="dropdown-divider mx-2">
                            </li>

                            <li class="px-3 py-1 text-uppercase small fw-bold text-muted" style="font-size: 11px;">
                                Finances
                            </li>
                            <li class="px-3 py-2">
                                <div class="d-flex justify-content-between fw-bold">
                                    <span>Balance</span>
                                    <span class="text-success">₹0.00 INR</span>
                                </div>
                            </li>
                            <li><a class="dropdown-item py-1" href="/funds/add" class="text-primary">Add Funds</a></li>
                            <li><a class="dropdown-item py-1" href="/funds/withdraw">Withdraw Funds</a></li>
                            <li><a class="dropdown-item py-1" href="/transactions">Transaction History</a></li>
                            <li><a class="dropdown-item py-1" href="/financial-dashboard">Financial Dashboard</a></li>

                            <li>
                                <hr class="dropdown-divider mx-2">
                            </li>

                            <li><a class="dropdown-item py-2" href="/support"><i class="bi bi-question-circle me-2"></i>
                                    Support</a></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-2 text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>



<style>
    /* Styling for the second row */
    .sub-nav-link {
        font-size: 13px;
        font-weight: 500;
        color: #4d525b !important;
        padding: 12px 16px !important;
        border-bottom: 3px solid transparent;
        white-space: nowrap;
    }

    .sub-nav-link:hover {
        color: #007fed !important;
    }

    .sub-nav-link.active {
        color: #007fed !important;
        border-bottom-color: #007fed;
    }

    .pointer {
        cursor: pointer;
    }

    /* Global hover for top row */
    @media (min-width: 992px) {
        .has-hover:hover .dropdown-menu {
            display: block;
            margin-top: 0;
        }
    }

    /* Scrollbar hidden for a clean sub-nav look on mobile */
    .custom-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .custom-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
