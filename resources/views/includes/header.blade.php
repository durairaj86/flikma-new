<header class="navbar navbar-expand-lg bg-white border-bottom px-3 py-2 shadow-sm {{ (isset($breadcrumb) && in_array($breadcrumb, ['masters', 'settings', 'reports'])) ? 'd-none' : '' }}">
    <div class="container-fluid d-flex align-items-center justify-content-between">

        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-light d-lg-none border" type="button" @click="sidebarOpen = !sidebarOpen">
                <i class="bi bi-list fs-5"></i>
            </button>

            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center border border-primary border-opacity-25" style="width: 40px; height: 40px; flex-shrink: 0;">
                <i class="bi bi-file-earmark-spreadsheet fs-5"></i>
            </div>

            @include('includes.page-heading')
        </div>

        <div class="d-flex align-items-center gap-2">
            {{-- Quick create / theme / header-toggle: the header-side counterparts of
                 the right rail's buttons, so the same actions are reachable in
                 either layout. The array lists must stay in step with
                 layouts/profile-menu.blade.php. --}}
            @php
                $headerCreateLinks = [
                    ['label' => __('New Enquiry'), 'icon' => 'bi-envelope-plus', 'title' => __('Add Enquiry'), 'url' => url('sales/enquiry/create'), 'size' => 'xxl', 'minHeight' => '650px', 'scroll' => false],
                    ['label' => __('New Quotation'), 'icon' => 'bi-chat-left-quote', 'title' => __('New Quotation'), 'url' => url('sales/quotation/create'), 'size' => 'xxl', 'minHeight' => '700px', 'scroll' => false],
                    ['label' => __('Customer Invoice'), 'icon' => 'bi-receipt', 'title' => __('New Customer Invoice'), 'url' => url('invoice/customer/create?jobId=list'), 'size' => '4xl', 'scroll' => false],
                    ['label' => __('Supplier Invoice'), 'icon' => 'bi-receipt-cutoff', 'title' => __('New Supplier Invoice'), 'url' => url('invoice/supplier/create?jobId=list'), 'size' => 'xl'],
                    ['label' => __('Waybill'), 'icon' => 'bi-box-seam', 'title' => __('New Waybill'), 'url' => url('bl/waybill/create'), 'size' => 'lg', 'scroll' => false],
                    ['label' => __('Expense'), 'icon' => 'bi-cash-coin', 'title' => __('Add Expense'), 'url' => url('finance/expense/create'), 'size' => 'lg'],
                ];

                $headerThemes = [
                    ['key' => 'gray', 'label' => __('Dark Gray'), 'swatch' => '#343a40', 'accent' => '#7CC4F0'],
                    ['key' => 'light', 'label' => __('Light'), 'swatch' => '#ffffff', 'accent' => '#5B4FE5'],
                    ['key' => 'dark', 'label' => __('Dark'), 'swatch' => '#12141c', 'accent' => '#8B7CF6'],
                    ['key' => 'indigo', 'label' => __('Indigo'), 'swatch' => '#1e1b4b', 'accent' => '#FBBF24'],
                    ['key' => 'ocean', 'label' => __('Ocean'), 'swatch' => '#0b3d54', 'accent' => '#22D3EE'],
                    ['key' => 'forest', 'label' => __('Forest'), 'swatch' => '#10291d', 'accent' => '#FBBF24'],
                ];
            @endphp

            <div class="dropdown">
                <button class="btn btn-light border-0 rounded-circle header-icon-btn" type="button" data-bs-toggle="dropdown"
                        title="{{ __('Create') }}" aria-label="{{ __('Create') }}">
                    <i class="bi bi-plus-lg fs-5 text-secondary"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="min-width: 220px;">
                    @foreach($headerCreateLinks as $link)
                        <li>
                            {{-- These are modal fragments, not pages. The delegated
                                 handler in layouts/profile-menu.blade.php opens them
                                 through the app's own webModal loader. --}}
                            <button type="button" class="dropdown-item py-2"
                                    data-create-url="{{ $link['url'] }}"
                                    data-create-title="{{ $link['title'] }}"
                                    data-create-size="{{ $link['size'] }}"
                                    data-create-min-height="{{ $link['minHeight'] ?? '' }}"
                                    data-create-scroll="{{ array_key_exists('scroll', $link) && $link['scroll'] === false ? '0' : '1' }}">
                                <i class="bi {{ $link['icon'] }} me-2"></i> {{ $link['label'] }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>

            <form method="POST" action="{{ route('header.toggle') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-light border-0 rounded-circle header-icon-btn"
                        title="{{ __('Hide Top Header') }}" aria-label="{{ __('Hide top header') }}">
                    <i class="bi bi-layout-text-window-reverse fs-5 text-secondary"></i>
                </button>
            </form>

            <div class="dropdown">
                <button class="btn btn-light border-0 rounded-circle header-icon-btn" type="button" data-bs-toggle="dropdown"
                        title="{{ __('Sidebar Theme') }}" aria-label="{{ __('Sidebar theme') }}">
                    <i class="bi bi-palette2 fs-5 text-secondary"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 p-3 header-theme-picker">
                    <div class="theme-picker-heading">{{ __('Sidebar Theme') }}</div>
                    <div class="theme-swatch-grid">
                        @foreach($headerThemes as $theme)
                            <button type="button" class="theme-swatch-btn" data-theme-key="{{ $theme['key'] }}"
                                    title="{{ $theme['label'] }}">
                                <span class="theme-swatch-preview" style="background: {{ $theme['swatch'] }};">
                                    <span class="theme-swatch-accent" style="background: {{ $theme['accent'] }};"></span>
                                    <i class="bi bi-check-lg theme-swatch-check"></i>
                                </span>
                                <span class="theme-swatch-label">{{ $theme['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            @if(request()->routeIs('dashboard'))
                <button type="button" class="btn btn-light border-0 rounded-circle header-icon-btn" data-widget-panel-toggle
                        onclick="window.dispatchEvent(new CustomEvent('open-widget-panel'))"
                        title="{{ __('Widgets') }}" aria-label="{{ __('Widgets') }}">
                    <i class="bi bi-grid-1x2 fs-5 text-secondary"></i>
                </button>
            @endif

            @include('includes.language-toggle')

            <a href="#" class="btn btn-light border-0 rounded-circle header-icon-btn position-relative" id="activity-feed">
                <i class="bi bi-bell fs-5 text-secondary"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white" style="font-size: 0.65rem;">3</span>
            </a>

            <div class="ms-2 user-account-menu">
                {{-- Opens the same account panel as the right rail's avatar (layouts/profile-menu.blade.php
                     listens for this window event). stopPropagation keeps the panel's own
                     @click.outside from treating this very click as an outside click and closing it. --}}
                <button class="btn btn-white border-0 d-flex align-items-center p-1 rounded-pill hover-shadow" type="button"
                        aria-label="{{ __('Account menu') }}"
                        onclick="event.stopPropagation(); window.dispatchEvent(new CustomEvent('open-profile-panel'));">
                    @php $userName = $user->name ?? __('Guest'); @endphp
                    @if($user->profile_photo_path ?? null)
                        <img src="{{ asset($user->profile_photo_path) }}" class="rounded-circle" width="38" height="38" alt="User" style="object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 0.85rem;">
                            {{ getInitials($userName) }}
                        </div>
                    @endif
                    <span class="ms-2 d-none d-md-inline fw-semibold text-dark small me-1">
                        {{ $userName }}
                    </span>
                    <i class="bi bi-chevron-down small text-muted"></i>
                </button>
            </div>
        </div>
    </div>
</header>

<style>
    /* The email under "Signed in as" was overflowing the dropdown box
       (a long, unspaced string doesn't wrap by default) instead of
       staying inside it — wrap it instead of letting it spill/get clipped. */
    .truncate-email {
        overflow-wrap: break-word;
        word-break: break-word;
        white-space: normal;
    }

    /* public/css/manual.css has a legacy "open dropdowns on hover" rule
       (.navbar .dropdown:hover .dropdown-menu) meant for an old nav.
       It forces the menu visible via CSS only, without Bootstrap's JS
       ever running Popper to position it — so on hover it renders
       unpositioned and appears cut off at the edge of the screen.
       Clicking works fine because Bootstrap's JS + Popper position it
       correctly. Opt every header dropdown out of the hover trick so they
       only open on click (higher specificity than manual.css's rule wins
       here). This covers the account menu plus the create and theme pickers
       added alongside the right rail. */
    @media (min-width: 992px) {
        .navbar .dropdown .dropdown-menu {
            display: none;
            visibility: visible;
            opacity: 1;
            transform: none;
        }

        .navbar .dropdown:hover .dropdown-menu {
            display: none;
            visibility: visible;
            opacity: 1;
            transform: none;
        }

        .navbar .dropdown .dropdown-menu.show {
            display: block;
        }
    }

    /* The header theme picker reuses the swatch styles defined in
       layouts/profile-menu.blade.php, which hides the tick with Alpine's
       x-show. There is no Alpine component in the header, so the tick is
       toggled by a class here instead. */
    .header-theme-picker .theme-swatch-check {
        display: none;
    }

    .header-theme-picker .theme-swatch-btn.active .theme-swatch-check {
        display: inline-block;
    }
    /* rounded-circle only renders a true circle when the box is square, and
       these icon buttons are not: p-2 plus the icon's inherited line-height
       (.btn sets 1.5, so fs-5's 20px font becomes a 30px line box) made them
       36.4 x 46 — a tall ellipse. Pin the box and centre the glyph instead, so
       the shape no longer depends on the icon's own metrics. */
    .header-icon-btn {
        width: 38px;
        height: 38px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    /* Match the right rail's icon size (1rem inside a 38px circle) instead of
       the larger fs-5 glyphs, so header and rail icons look identical. */
    .header-icon-btn i {
        font-size: 1rem !important;
    }
</style>

<script>
    /* Writes the same data-sidebar-theme attribute and localStorage key the
       sidebar's own palette button uses, so the two stay in agreement no
       matter which one the user clicks. */
    (function () {
        var picker = document.querySelector('.header-theme-picker');
        if (! picker) {
            return;
        }

        function paint() {
            var current = document.documentElement.getAttribute('data-sidebar-theme') || 'gray';
            picker.querySelectorAll('.theme-swatch-btn').forEach(function (btn) {
                btn.classList.toggle('active', btn.getAttribute('data-theme-key') === current);
            });
        }

        paint();

        picker.addEventListener('click', function (event) {
            var btn = event.target.closest('.theme-swatch-btn');
            if (! btn) {
                return;
            }
            var theme = btn.getAttribute('data-theme-key');
            document.documentElement.setAttribute('data-sidebar-theme', theme);
            localStorage.setItem('flikma-sidebar-theme', theme);
            paint();
        });
    })();
</script>
