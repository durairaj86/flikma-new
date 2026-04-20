<header>
    <div class="p-4 pt-3 pb-3" {{--style="background: #eff3ff"--}}>
        <div>
            <!--begin::Row-->
            <div class="row d-none">
                <div class="col-sm-6">
                    @php
                        if(in_array($segment1,['customers'])){
                            $breadcrumb = 'customer';
                        }elseif(in_array($segment1,['suppliers'])){
                            $breadcrumb = 'supplier';
                        }elseif(in_array($segment1,['masters'])){
                            $breadcrumb = 'user';
                        }
                    @endphp
                    <h4 class="fw-bold d-flex align-items-center">
                        {{--<a class="nav-link me-2" data-lte-toggle="sidebar" href="#" role="button">
                            <i class="bi bi-list"></i>
                        </a>--}}
                        @yield('page-title')
                    </h4>
                    @if(isset($breadcrumb))
                        @include('includes.breadcrumb.'.$breadcrumb)
                    @endif
                </div>
                <div class="col-sm-6">
                    <nav class="app-header navbar navbar-expand">
                        <ul class="navbar-nav ms-auto align-items-center">

                            <!-- Messages Dropdown -->
                            {{--<li class="nav-item dropdown">
                                <button class="nav-link btn btn-link" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-chat-text"></i>
                                    <span class="badge bg-danger rounded-pill">3</span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                                    <a href="#" class="dropdown-item d-flex align-items-start">
                                        <img src="./assets/img/user1-128x128.jpg" alt="User Avatar" class="img-size-50 rounded-circle me-3" />
                                        <div>
                                            <h6 class="mb-0">Brad Diesel <i class="bi bi-star-fill text-danger float-end"></i></h6>
                                            <small class="text-muted">Call me whenever you can...</small><br>
                                            <small class="text-secondary"><i class="bi bi-clock-fill me-1"></i> 4 Hours Ago</small>
                                        </div>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a href="#" class="dropdown-item d-flex align-items-start">
                                        <img src="./assets/img/user8-128x128.jpg" alt="User Avatar" class="img-size-50 rounded-circle me-3" />
                                        <div>
                                            <h6 class="mb-0">John Pierce <i class="bi bi-star-fill text-secondary float-end"></i></h6>
                                            <small class="text-muted">I got your message bro</small><br>
                                            <small class="text-secondary"><i class="bi bi-clock-fill me-1"></i> 4 Hours Ago</small>
                                        </div>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a href="#" class="dropdown-item d-flex align-items-start">
                                        <img src="./assets/img/user3-128x128.jpg" alt="User Avatar" class="img-size-50 rounded-circle me-3" />
                                        <div>
                                            <h6 class="mb-0">Nora Silvester <i class="bi bi-star-fill text-warning float-end"></i></h6>
                                            <small class="text-muted">The subject goes here</small><br>
                                            <small class="text-secondary"><i class="bi bi-clock-fill me-1"></i> 4 Hours Ago</small>
                                        </div>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a href="#" class="dropdown-item dropdown-footer text-center">See All Messages</a>
                                </div>
                            </li>--}}


                            <li class="nav-item dropdown me-3">
                                <div class="d-inline-flex align-items-center rounded-pill px-3 py-2 shadow-sm"
                                     data-bs-toggle="dropdown" aria-expanded="false">
                                    <!-- Company Icon -->
                                    <div
                                        class="me-3 d-flex align-items-center justify-content-center rounded-circle border bg-light"
                                        style="width:40px; height:40px;">
                                        <i class="fa fa-building fs-5 text-primary"></i>
                                    </div>

                                    <!-- Company Info -->
                                    <div class="lh-sm">
                                        <div class="fw-semibold">ABC Corporation</div>
                                        <small class="text-muted">BR001 - Chennai Branch</small>
                                    </div>
                                </div>

                                <!-- Dropdown Menu -->
                                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow">
                                    <li class="dropdown-header fw-semibold">Switch Branch</li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="#">BR001 - Chennai Branch</a></li>
                                    <li><a class="dropdown-item" href="#">BR002 - Bangalore Branch</a></li>
                                    <li><a class="dropdown-item" href="#">BR003 - Mumbai Branch</a></li>
                                </ul>

                            </li>
                            <!-- Fullscreen Toggle -->
                            <li class="nav-item">
                                <button class="nav-link btn btn-link" id="fullscreenToggle">
                                    <i class="bi bi-arrows-fullscreen"></i>
                                </button>
                            </li>
                            <!-- Notifications Dropdown -->
                            <li class="nav-item dropdown">
                                <button class="nav-link btn btn-link" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-bell-fill"></i>
                                    <span class="badge bg-warning rounded-pill">15</span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                                    <span class="dropdown-item dropdown-header">15 Notifications</span>
                                    <div class="dropdown-divider"></div>
                                    <a href="#" class="dropdown-item">
                                        <i class="bi bi-envelope me-2"></i> 4 new messages
                                        <span class="float-end text-muted">3 mins</span>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a href="#" class="dropdown-item">
                                        <i class="bi bi-people-fill me-2"></i> 8 friend requests
                                        <span class="float-end text-muted">12 hours</span>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a href="#" class="dropdown-item">
                                        <i class="bi bi-file-earmark-fill me-2"></i> 3 new reports
                                        <span class="float-end text-muted">2 days</span>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a href="#" class="dropdown-item dropdown-footer text-center">See All
                                        Notifications</a>
                                </div>
                            </li>
                            <!-- User Menu Dropdown -->
                            <li class="nav-item dropdown">
                                {{--<button class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
                                    <img src="./assets/img/user2-160x160.jpg" class="rounded-circle me-2" width="32" height="32" alt="User Image">
                                    <span class="d-none d-md-inline">Alexander Pierce</span>
                                </button>--}}
                                <div class="d-inline-flex align-items-center rounded-pill px-3 py-2 shadow-sm"
                                     data-bs-toggle="dropdown" aria-expanded="false">


                                    <!-- User Info -->
                                    <div class="d-flex align-items-center">
                                        <!-- Avatar -->
                                        {{--<img src="https://via.placeholder.com/40" alt="User" class="rounded-circle" width="40" height="40">--}}
                                        <div style="height: 40px;width: 40px"><i class="fa fa-user"></i></div>


                                    </div>
                                </div>

                                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-3 shadow rounded-3"
                                    style="min-width: 280px;">
                                    <!-- User Info -->
                                    <li class="d-flex align-items-center mb-3">
                                        <div class="flex-shrink-0">
                                            <img src="./assets/img/user2-160x160.jpg" class="rounded-circle" width="50"
                                                 height="50" alt="User Image">
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0 fw-semibold">Alexander Pierce</h6>
                                            <small class="text-muted">Web Developer</small><br>
                                            <small class="text-muted">Member since Nov. 2023</small>
                                        </div>
                                    </li>

                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>

                                    <!-- Stats -->
                                    <li class="d-flex justify-content-around text-center py-2">
                                        <div>
                                            <div class="fw-semibold">200</div>
                                            <small class="text-muted">Followers</small>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">120</div>
                                            <small class="text-muted">Sales</small>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">80</div>
                                            <small class="text-muted">Friends</small>
                                        </div>
                                    </li>

                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>

                                    <!-- Actions -->
                                    <li class="d-flex justify-content-between">
                                        <a href="#" class="btn btn-outline-primary btn-sm rounded-pill">Profile</a>
                                        <a href="/logout" class="btn btn-outline-danger btn-sm rounded-pill">Sign out</a>
                                    </li>
                                </ul>

                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
            <!--end::Row-->
        </div>

        {{--<h5 class="fw-bold">Good Morning Ganesh</h5>
        <p class="text-muted">We personally select every item that you can ship on the weariness.</p>--}}

        <!-- Tabs -->
        <!-- Nav pills -->
        {{--<ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active"
                        type="button" role="tab" aria-controls="active" aria-selected="true">
                    <i class="bi bi-play-circle"></i> Active
                </button>
            </li>
            --}}{{--<li class="nav-item" role="presentation">
                <button class="nav-link" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming"
                        type="button" role="tab" aria-controls="upcoming" aria-selected="false">
                    <i class="bi bi-calendar-event"></i> Overdue
                </button>
            </li>--}}{{--
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="cancelled-tab" data-bs-toggle="tab" data-bs-target="#cancelled"
                        type="button" role="tab" aria-controls="cancelled" aria-selected="false">
                    <i class="bi bi-x-circle"></i> Blocked
                </button>
            </li>
        </ul>--}}


    </div>
</header>
