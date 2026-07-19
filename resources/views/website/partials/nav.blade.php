<!-- ==========================
Navbar
=========================== -->

<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top py-3">

    <div class="container">

        <a class="navbar-brand fw-bold fs-3" href="{{ route('website.home') }}">

            <img src="{{ asset('img/logo.svg') }}" height="42">

        </a>

        <button class="navbar-toggler"
                data-bs-toggle="collapse"
                data-bs-target="#navbar">

            <span class="navbar-toggler-icon"></span>

        </button>

        <div class="collapse navbar-collapse"
             id="navbar">

            <ul class="navbar-nav mx-auto">

                <li class="nav-item">

                    <a class="nav-link {{ request()->routeIs('website.home') ? 'active' : '' }}"
                       href="{{ route('website.home') }}">Home</a>

                </li>

                <li class="nav-item">

                    <a class="nav-link {{ request()->routeIs('website.why-flikma') ? 'active' : '' }}"
                       href="{{ route('website.why-flikma') }}">Why Flikma</a>

                </li>

                <li class="nav-item">

                    <a class="nav-link {{ request()->routeIs('website.features') ? 'active' : '' }}"
                       href="{{ route('website.features') }}">Features</a>

                </li>

                <li class="nav-item">

                    <a class="nav-link {{ request()->routeIs('website.pricing') ? 'active' : '' }}"
                       href="{{ route('website.pricing') }}">Pricing</a>

                </li>

                <li class="nav-item">

                    <a class="nav-link {{ request()->routeIs('website.documentation') ? 'active' : '' }}"
                       href="{{ route('website.documentation') }}">Documentation</a>

                </li>

                <li class="nav-item">

                    <a class="nav-link {{ request()->routeIs('website.contact') ? 'active' : '' }}"
                       href="{{ route('website.contact') }}">Contact</a>

                </li>

            </ul>


            <div class="d-flex gap-3">
                @auth
                    <a href="https://app.flikma.com/dashboard"
                       class="btn btn-primary rounded-pill px-4">
                        My Account
                    </a>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-danger rounded-pill px-4">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="https://app.flikma.com/login"
                       class="btn btn-outline-dark rounded-pill px-4">
                        Login
                    </a>

                    <a href="https://app.flikma.com/register"
                       class="btn btn-primary rounded-pill px-4">
                        Register
                    </a>
                @endauth
            </div>

        </div>

    </div>

</nav>
