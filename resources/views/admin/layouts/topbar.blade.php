<header class="topbar">
    <div class="d-flex align-items-center gap-2 min-w-0">
        <button class="topbar-icon" onclick="toggleSidebar()" title="Toggle menu" aria-label="Toggle menu">
            <i class="bi bi-layout-sidebar"></i>
        </button>
        {{-- breadcrumb: Dashboard › section › page title (bold) --}}
        <nav aria-label="breadcrumb" class="min-w-0">
            <ol class="breadcrumb flex-nowrap text-nowrap overflow-hidden">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                @php
                    $segments = array_slice(request()->segments(), 1);
                    $trail = '/admin';
                    $crumbs = [];
                    foreach ($segments as $segment) {
                        $trail .= '/' . $segment;
                        $crumbs[] = ['label' => ctype_digit($segment) ? '#' . $segment : ucwords(str_replace(['-', '_'], ' ', $segment)), 'url' => url($trail)];
                    }
                    $pageTitle = html_entity_decode(trim($__env->yieldContent('title', 'Dashboard')), ENT_QUOTES);
                @endphp
                @foreach($crumbs as $crumb)
                    @if($loop->last)
                        <li class="breadcrumb-item active text-truncate">{{ $pageTitle }}</li>
                    @else
                        <li class="breadcrumb-item"><a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a></li>
                    @endif
                @endforeach
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2 flex-shrink-0">
        <div class="dropdown">
            <button class="btn dropdown-toggle d-flex align-items-center gap-2 px-1" data-bs-toggle="dropdown" style="border: none; background: none;" aria-label="Account menu">
                <div class="avatar bg-primary text-white">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="text-start d-none d-md-block">
                    <div class="fw-semibold small">{{ auth()->user()->name }}</div>
                    <div class="text-muted" style="font-size: .65rem;">Platform admin</div>
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li>
                    <div class="px-3 py-2">
                        <div class="fw-semibold small">{{ auth()->user()->name }}</div>
                        <div class="text-muted" style="font-size: .75rem;">{{ auth()->user()->email }}</div>
                    </div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item small" href="{{ url('/dashboard') }}"><i class="bi bi-box-arrow-up-right me-2"></i>Go to App</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item small text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
