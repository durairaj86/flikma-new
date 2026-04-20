<header class="sticky-top bg-white border-bottom shadow-sm" style="z-index: 1020;">
    <div class="container-fluid px-3 px-md-4">
        <div class="d-flex align-items-center" style="height: 56px;">

            <div class="d-flex align-items-center gap-2">
                <button class="d-lg-none btn btn-link text-secondary p-2 text-decoration-none"
                        @click="sidebarOpen = true" aria-label="Open sidebar">
                    <i class="bi bi-list fs-4"></i>
                </button>

                <div class="text-dark fw-semibold">
                    @yield('title', 'Dashboard')
                </div>
            </div>

            <div class="ms-auto d-flex align-items-center">
                @auth
                    <div x-data="{ open: false }" class="position-relative">
                        <button @click="open = !open" @keydown.escape.window="open=false"
                                class="btn border-0 d-flex align-items-center gap-2 px-2 py-1 hover-bg-light">

                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white small fw-bold"
                              style="width: 28px; height: 28px; font-size: 0.75rem;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </span>

                            <span class="d-none d-sm-inline text-dark small fw-medium">{{ Auth::user()->name }}</span>

                            <i class="bi bi-chevron-down small text-secondary transition-all"
                               :style="open ? 'transform: rotate(180deg)' : ''"></i>
                        </button>

                        <div x-cloak x-show="open" @click.outside="open=false"
                             class="position-absolute end-0 mt-2 bg-white border rounded shadow-sm overflow-hidden"
                             style="width: 192px; z-index: 1050;">

                            <a href="{{ route('profile.edit') }}" class="dropdown-item px-3 py-2 small d-flex align-items-center gap-2">
                                <i class="bi bi-person text-secondary"></i>
                                Profile
                            </a>

                            <div class="dropdown-divider m-0"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item px-3 py-2 small text-danger d-flex align-items-center gap-2">
                                    <i class="bi bi-box-arrow-right"></i>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</header>

<style>
    /* Adding a simple hover effect since Bootstrap doesn't have a utility for light background hover on buttons */
    .hover-bg-light:hover {
        background-color: #f8f9fa;
        border-radius: 0.375rem;
    }
    .transition-all {
        transition: all 0.2s ease-in-out;
    }
</style>
