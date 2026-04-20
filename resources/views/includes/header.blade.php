<header class="navbar navbar-expand-lg bg-white border-bottom px-3 py-2 shadow-sm {{ (isset($breadcrumb) && in_array($breadcrumb, ['masters', 'settings', 'reports'])) ? 'd-none' : '' }}">
    <div class="container-fluid d-flex align-items-center justify-content-between">

        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-light d-lg-none border" type="button" @click="sidebarOpen = !sidebarOpen">
                <i class="bi bi-list fs-5"></i>
            </button>

            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center border border-primary border-opacity-25" style="width: 40px; height: 40px; flex-shrink: 0;">
                <i class="bi bi-file-earmark-spreadsheet fs-5"></i>
            </div>

            <div class="lh-sm overflow-hidden">
                @php
                    $breadcrumb = null;
                    if($segment1 == 'dashboard1') { $breadcrumb = 'customer'; }
                    elseif(in_array($segment1,['customers','customer','prospects'])) { $breadcrumb = 'customer'; }
                    elseif(in_array($segment1,['suppliers','supplier'])) { $breadcrumb = 'supplier'; }
                    elseif(in_array($segment1,['masters','settings'])) {
                        $breadcrumb = $segment1; $page1 = $segment2; $page2 = $segment3; $page3 = $segment4;
                    }
                    elseif(in_array($segment1,['sales','operation','invoice'])){
                        if($segment2 != 'overview'){ $breadcrumb = $segment1; }
                        $page1 = $segment2;
                    }
                    elseif($segment1 == 'finance'){ $breadcrumb = 'invoice'; $page1 = 'proforma'; }
                    elseif($segment1 == 'reports'){ $breadcrumb = 'reports'; $page1 = 'reports'; }
                    elseif($segment1 == 'bl'){ $breadcrumb = 'bl'; $page1 = $segment2; }
                @endphp

                <nav aria-label="breadcrumb" class="mb-0 d-none d-sm-block">
                    @if(isset($breadcrumb))
                        @include('includes.breadcrumb.'.$breadcrumb, ['page1'=>$page1??'','page2'=>$page2 ?? '','page3'=>$page3 ?? ''])
                    @endif
                </nav>
                <h5 class="fw-bold text-dark mb-0 text-truncate" style="max-width: 200px;">@yield('page-title')</h5>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <div class="dropdown d-none d-md-block">
                <button class="btn btn-light border-0 rounded-circle p-2" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-grid-3x3-gap fs-5"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-3" style="width: 300px;">
                    <h6 class="dropdown-header px-0 mb-2 text-uppercase fw-bold">Quick Apps</h6>
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <a href="/chat" class="d-block p-2 text-decoration-none rounded hover-bg-light">
                                <i class="bi bi-chat-dots text-primary fs-4"></i>
                                <div class="small text-dark mt-1">Chat</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <a href="#" class="btn btn-light border-0 rounded-circle p-2 position-relative" id="activity-feed">
                <i class="bi bi-bell fs-5 text-secondary"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white" style="font-size: 0.65rem;">3</span>
            </a>

            <div class="dropdown ms-2">
                <button class="btn btn-white border-0 d-flex align-items-center p-1 rounded-pill hover-shadow" type="button" data-bs-toggle="dropdown">
                    @if($user->profile_photo_path)
                        <img src="{{ asset($user->profile_photo_path) }}" class="rounded-circle" width="32" height="32" alt="User" style="object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center fw-bold shadow-sm" style="width: 32px; height: 32px; font-size: 12px;">
                            {{ getInitials($user->name) }}
                        </div>
                    @endif
                    <span class="ms-2 d-none d-md-inline fw-semibold text-dark small me-1">
                        {{ $user->name }}
                    </span>
                    <i class="bi bi-chevron-down small text-muted"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                    <li class="px-3 py-2 border-bottom">
                        <div class="small text-muted">Signed in as:</div>
                        <div class="fw-bold text-dark truncate-email">{{ $user->email }}</div>
                    </li>
                    <li><a class="dropdown-item py-2" href="{{ url('settings/account') }}"><i class="bi bi-person me-2"></i> My Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger py-2" href="/logout"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</header>
