@auth
    @php
        $authUser = Auth::user();
        $headerOn = $headerEnabled ?? true;

        /* Quick-create entries. These are modal fragments, not pages, so they are
           opened through the app's own webModal.openGlobalModal() rather than
           navigated to — see the delegated handler at the bottom of this file.
           The two invoice forms need jobId=list to show their job picker. */
        $createLinks = [
            ['label' => 'New Enquiry', 'icon' => 'bi-envelope-plus', 'title' => 'Add Enquiry', 'url' => url('sales/enquiry/create'), 'size' => 'xxl', 'minHeight' => '650px', 'scroll' => false],
            ['label' => 'New Quotation', 'icon' => 'bi-chat-left-quote', 'title' => 'New Quotation', 'url' => url('sales/quotation/create'), 'size' => 'xxl', 'minHeight' => '700px', 'scroll' => false],
            ['label' => 'Customer Invoice', 'icon' => 'bi-receipt', 'title' => 'New Customer Invoice', 'url' => url('invoice/customer/create?jobId=list'), 'size' => '4xl', 'scroll' => false],
            ['label' => 'Supplier Invoice', 'icon' => 'bi-receipt-cutoff', 'title' => 'New Supplier Invoice', 'url' => url('invoice/supplier/create?jobId=list'), 'size' => 'xl'],
            ['label' => 'Waybill', 'icon' => 'bi-box-seam', 'title' => 'New Waybill', 'url' => url('bl/waybill/create'), 'size' => 'lg', 'scroll' => false],
            ['label' => 'Expense', 'icon' => 'bi-cash-coin', 'title' => 'Add Expense', 'url' => url('finance/expense/create'), 'size' => 'lg'],
        ];

        $gridLinks = [
            ['label' => 'Settings', 'icon' => 'bi-gear', 'url' => route('settings.company.edit'), 'bg' => '#eef2ff', 'fg' => '#4f46e5'],
            ['label' => 'Reports', 'icon' => 'bi-bar-chart', 'url' => url('reports'), 'bg' => '#e6f4ec', 'fg' => '#1b7a4d'],
            ['label' => 'Customers', 'icon' => 'bi-people', 'url' => route('customers'), 'bg' => '#e7f0f9', 'fg' => '#1d6fb8'],
            ['label' => 'Jobs', 'icon' => 'bi-briefcase', 'url' => url('operation/jobs'), 'bg' => '#faf1e6', 'fg' => '#b5651d'],
            ['label' => 'Invoices', 'icon' => 'bi-receipt', 'url' => route('invoices.customer'), 'bg' => '#f0e9f7', 'fg' => '#6b3fa0'],
            ['label' => 'Billing', 'icon' => 'bi-credit-card', 'url' => route('billing.index'), 'bg' => '#fff7e6', 'fg' => '#b7791f'],
        ];
        // Billing is for the account owner only.
        if (!isSuperUser()) {
            $gridLinks = array_values(array_filter($gridLinks, fn($l) => $l['label'] !== 'Billing'));
        }

        $moreLinks = [
            ['label' => 'Supplier Bills', 'icon' => 'bi-truck', 'url' => url('invoice/supplier')],
            ['label' => 'Waybills', 'icon' => 'bi-boxes', 'url' => route('bl.waybill')],
            ['label' => 'Expenses', 'icon' => 'bi-wallet2', 'url' => route('expenses.index')],
            ['label' => 'Credit Notes', 'icon' => 'bi-file-earmark-minus', 'url' => route('adjustments.credit-notes')],
            ['label' => 'Statements', 'icon' => 'bi-journal-text', 'url' => route('customers.statement')],
            ['label' => 'AI Usage', 'icon' => 'bi-cpu', 'url' => route('billing.ai-usage')],
        ];

        /* Swatch colours are copied from the --sb-* blocks in layouts/sidebar.blade.php
           so the rail preview shows exactly the sidebar the user is about to get.
           Keys must stay in sync with that file's THEMES array. */
        $sidebarThemes = [
            ['key' => 'gray', 'label' => 'Dark Gray', 'swatch' => '#343a40', 'accent' => '#7CC4F0'],
            ['key' => 'light', 'label' => 'Light', 'swatch' => '#ffffff', 'accent' => '#5B4FE5'],
            ['key' => 'dark', 'label' => 'Dark', 'swatch' => '#12141c', 'accent' => '#8B7CF6'],
            ['key' => 'indigo', 'label' => 'Indigo', 'swatch' => '#1e1b4b', 'accent' => '#FBBF24'],
            ['key' => 'ocean', 'label' => 'Ocean', 'swatch' => '#0b3d54', 'accent' => '#22D3EE'],
            ['key' => 'forest', 'label' => 'Forest', 'swatch' => '#10291d', 'accent' => '#FBBF24'],
        ];
    @endphp

    <div x-data="{
            profileMenuOpen: false,
            createOpen: false,
            themeOpen: false,
            sidebarTheme: localStorage.getItem('flikma-sidebar-theme') || 'gray',
            setSidebarTheme(theme) {
                this.sidebarTheme = theme;
                document.documentElement.setAttribute('data-sidebar-theme', theme);
                localStorage.setItem('flikma-sidebar-theme', theme);
                this.themeOpen = false;
            }
         }"
         @keydown.escape.window="profileMenuOpen = false; createOpen = false; themeOpen = false"
         @open-profile-panel.window="profileMenuOpen = true; createOpen = false; themeOpen = false"
         class="profile-menu-fixed d-flex flex-column align-items-center gap-2">

        {{-- The rail is the header-off counterpart of the top header: it carries the
             same actions, so it only renders its own buttons when the header is hidden.
             The container itself always renders — it is the parent of the account card —
             and collapses to zero width via body.has-top-header instead of display:none. --}}
        @unless($headerOn)
            <div class="position-relative" @click.outside="createOpen = false">
                <button @click="createOpen = !createOpen" class="profile-menu-btn profile-menu-btn-bell" title="Create" aria-label="Create">
                    <i class="bi bi-plus-lg"></i>
                </button>
                <div x-cloak x-show="createOpen" x-transition class="profile-menu-side-dropdown">
                    @foreach($createLinks as $link)
                        <button type="button"
                                class="profile-menu-side-dropdown-item"
                                data-create-url="{{ $link['url'] }}"
                                data-create-title="{{ $link['title'] }}"
                                data-create-size="{{ $link['size'] }}"
                                data-create-min-height="{{ $link['minHeight'] ?? '' }}"
                                data-create-scroll="{{ array_key_exists('scroll', $link) && $link['scroll'] === false ? '0' : '1' }}">
                            <i class="bi {{ $link['icon'] }}"></i> {{ $link['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Language toggle — one click switches to the other language. --}}
            <a href="{{ route('locale.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}"
               class="profile-menu-btn profile-menu-btn-bell"
               title="{{ app()->getLocale() === 'ar' ? 'Switch to English' : 'Switch to العربية' }}"
               aria-label="Switch language">
                <i class="bi bi-translate"></i>
            </a>

            @if(\App\Services\Briefing\OperationsBriefing::allowed($authUser))
                {{-- Today's briefing: arrivals, departures and what needs attention --}}
                <button type="button" data-briefing-open class="profile-menu-btn profile-menu-btn-bell position-relative"
                        title="{{ __('Today\'s Briefing') }}" aria-label="{{ __('Today\'s Briefing') }}">
                    <i class="bi bi-stars"></i>
                    <span class="briefing-badge" data-briefing-badge></span>
                </button>
            @endif

            {{-- Notifications / activity feed — the header's bell (same #activity-feed trigger, wired in startup.js).
                 The header and rail are never both rendered, so the id stays unique. --}}
            <a href="#" id="activity-feed" class="profile-menu-btn profile-menu-btn-bell position-relative"
               title="{{ __('Notifications') }}" aria-label="{{ __('Notifications') }}">
                <i class="bi bi-bell"></i>
            </a>

            {{-- Header on/off shortcut. Sits in the rail while the header is hidden,
                 and in the header itself while it is showing, so one click is always
                 available to flip between the two layouts. --}}
            <form method="POST" action="{{ route('header.toggle') }}" class="m-0">
                @csrf
                <button type="submit" class="profile-menu-btn profile-menu-btn-bell" title="Show Top Header" aria-label="Show top header">
                    <i class="bi bi-layout-text-window-reverse"></i>
                </button>
            </form>

            <div class="position-relative" @click.outside="themeOpen = false">
                <button @click="themeOpen = !themeOpen" class="profile-menu-btn profile-menu-btn-bell" title="Sidebar Theme" aria-label="Sidebar theme">
                    <i class="bi bi-palette2"></i>
                </button>
                <div x-cloak x-show="themeOpen" x-transition class="profile-menu-side-dropdown theme-picker-dropdown">
                    <div class="theme-picker-heading">Sidebar Theme</div>
                    <div class="theme-swatch-grid">
                        @foreach($sidebarThemes as $theme)
                            <button type="button"
                                    @click="setSidebarTheme('{{ $theme['key'] }}')"
                                    class="theme-swatch-btn"
                                    :class="sidebarTheme === '{{ $theme['key'] }}' ? 'active' : ''"
                                    title="{{ $theme['label'] }}">
                                <span class="theme-swatch-preview" style="background: {{ $theme['swatch'] }};">
                                    <span class="theme-swatch-accent" style="background: {{ $theme['accent'] }};"></span>
                                    <i class="bi bi-check-lg theme-swatch-check" x-show="sidebarTheme === '{{ $theme['key'] }}'"></i>
                                </span>
                                <span class="theme-swatch-label">{{ $theme['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="profile-menu-rail-divider"></div>
        @endunless

        {{-- Account menu — rendered in both modes; the rail's own avatar button is
             hidden when the header is on because the header shows one instead. --}}
        <div class="position-relative profile-menu-rail-first" @click.outside="profileMenuOpen = false">
            @unless($headerOn)
                <button @click="profileMenuOpen = !profileMenuOpen"
                        class="profile-menu-btn"
                        title="{{ $authUser->name }}"
                        aria-label="Account menu">
                    @if(! empty($authUser->photo_path))
                        <img src="{{ asset('storage/'.$authUser->photo_path) }}" alt="{{ $authUser->name }}">
                    @else
                        <span>{{ strtoupper(substr($authUser->name, 0, 1)) }}</span>
                    @endif
                </button>
            @endunless

            <div x-cloak x-show="profileMenuOpen"
                 x-transition:enter="profile-slide-enter"
                 x-transition:enter-start="profile-slide-enter-start"
                 x-transition:enter-end="profile-slide-enter-end"
                 x-transition:leave="profile-slide-leave"
                 x-transition:leave-start="profile-slide-leave-start"
                 x-transition:leave-end="profile-slide-leave-end"
                 class="profile-menu-card">

                <div class="profile-menu-scroll">
                    <div class="profile-menu-header">
                        <div class="profile-menu-avatar-sq">
                            @if(! empty($authUser->photo_path))
                                <img src="{{ asset('storage/'.$authUser->photo_path) }}" alt="{{ $authUser->name }}">
                            @else
                                <i class="bi bi-person-fill"></i>
                            @endif
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-bold text-dark text-truncate">{{ $authUser->name }}</div>
                            <div class="text-muted small text-truncate">{{ $authUser->email }}</div>
                        </div>
                        <button type="button" @click="profileMenuOpen = false" class="profile-menu-close" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="profile-menu-ids">
                        User ID: {{ $authUser->id }} &nbsp;&bull;&nbsp; Organization ID: {{ $authUser->company_id }}
                    </div>

                    <div class="profile-menu-lang-row">
                        <span class="profile-menu-lang-icon"><i class="bi bi-translate"></i></span>
                        <div class="profile-menu-lang-toggle">
                            <a href="{{ route('locale.switch', 'en') }}" class="profile-menu-lang-btn {{ app()->getLocale() === 'en' ? 'active' : '' }}">English</a>
                            <a href="{{ route('locale.switch', 'ar') }}" class="profile-menu-lang-btn {{ app()->getLocale() === 'ar' ? 'active' : '' }}">العربية</a>
                        </div>
                    </div>

                    <div class="profile-menu-actions-row">
                        <a href="{{ url('settings/account') }}" class="profile-menu-link">{{ __('My Account') }}</a>
                        <form method="POST" action="{{ url('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="profile-menu-link text-danger border-0 bg-transparent">
                                <i class="bi bi-box-arrow-right"></i> {{ __('Sign Out') }}
                            </button>
                        </form>
                    </div>

                    <div class="profile-menu-divider"></div>

                    <div class="profile-menu-grid">
                        @foreach($gridLinks as $link)
                            <a href="{{ $link['url'] }}" class="profile-menu-grid-item">
                                <div class="profile-menu-grid-icon" style="background:{{ $link['bg'] }}; color:{{ $link['fg'] }};">
                                    <i class="bi {{ $link['icon'] }}"></i>
                                </div>
                                <span>{{ $link['label'] }}</span>
                            </a>
                        @endforeach
                    </div>

                    <div class="profile-menu-pill-wrap">
                        <a href="{{ route('settings.company.edit') }}" class="profile-menu-pill">
                            <span class="profile-menu-pill-icon"><i class="bi bi-bell"></i></span>
                            <span class="flex-grow-1">{{ __('Notification Preferences') }}</span>
                            <i class="bi bi-chevron-right small text-muted"></i>
                        </a>
                    </div>

                    <div class="profile-menu-divider"></div>

                    <div class="profile-menu-more-heading">More</div>
                    <div class="pb-2">
                        @foreach($moreLinks as $link)
                            <a href="{{ $link['url'] }}" class="profile-menu-more-item">
                                <i class="bi {{ $link['icon'] }}"></i>
                                <span class="flex-grow-1">{{ $link['label'] }}</span>
                                <i class="bi bi-chevron-right small"></i>
                            </a>
                        @endforeach
                    </div>

                    <div class="profile-menu-help-card">
                        <div class="profile-menu-help-title">Need help?</div>
                        <a href="mailto:support@flikma.com" class="profile-menu-help-link">Email support@flikma.com</a>
                        <a href="https://wa.me/966595555343" target="_blank" rel="noopener" class="profile-menu-help-link">WhatsApp +966 59 555 5343</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* A slim, full-height icon rail on the right edge — the counterpart to the main
           left sidebar — shown only when the top header is off (its icons live in the
           header itself when that's on, so this whole rail hides in that mode instead). */
        .profile-menu-fixed {
            position: fixed;
            top: 0;
            right: 0;
            height: 100vh;
            width: 60px;
            padding-top: 16px;
            background: #fff;
            border-left: 1px solid #eef0f3;
            /* Elevation shadow cast leftward — the rail sits at a higher z-index
               than offcanvas drawers (Bootstrap's default z-index: 1045) and
               genuinely renders on top of/overlapping them, but without this
               shadow the flat 1px border reads as a seam beside the drawer
               rather than a floating panel above it. */
            box-shadow: -6px 0 16px rgba(0, 0, 0, .06);
            z-index: 1090;
        }

        /* Collapse the rail to nothing visible when the header is on — NOT display:none,
           because this container is also the parent of the profile/activity panels, which
           the header's icons open via dispatched events. A display:none ancestor hides its
           entire subtree regardless of the panels' own position:fixed, which broke opening
           them from the header entirely. Zeroing width/padding/border is invisible the same
           way without touching the children's own ability to render. !important because the
           element also carries Bootstrap's .d-flex utility class, which is itself !important. */
        body.has-top-header .profile-menu-fixed {
            width: 0 !important;
            min-width: 0 !important;
            padding: 0 !important;
            border: none !important;
        }

        /* Same collapse on mobile — the bottom nav covers navigation there. Width/
           padding/border only (not display:none) for the same reason as above. Unlike the
           has-top-header case, the rail's trigger buttons ARE still rendered here (they're
           only removed server-side when the header is on), so overflow:hidden is required
           too — otherwise those normal-flow children keep painting past the zeroed box.
           The sliding panels are unaffected: they're position:fixed, not clipped by an
           ancestor's overflow. */
        @media (max-width: 991.98px) {
            .profile-menu-fixed {
                width: 0 !important;
                min-width: 0 !important;
                padding: 0 !important;
                border: none !important;
                overflow: hidden !important;
            }
        }

        .profile-menu-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 2px solid #fff;
            padding: 0;
            overflow: hidden;
            background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%);
            color: #fff;
            font-weight: 700;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,.18);
            cursor: pointer;
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .profile-menu-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,.22);
        }

        .profile-menu-btn img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-menu-btn-bell {
            background: #f8f9fa;
            border-color: #f8f9fa;
            color: #6c757d;
            font-size: 1rem;
            box-shadow: none;
        }

        .profile-menu-btn-bell:hover {
            background: #f1f3f5;
            color: #212529;
            box-shadow: none;
        }

        .profile-menu-rail-divider {
            width: 28px;
            height: 1px;
            background: #eef0f3;
            margin: 4px 0;
            flex-shrink: 0;
        }

        /* Visually moves the profile button to the front of the rail without relocating
           its large dropdown panel markup — simpler and safer than moving the DOM block. */
        .profile-menu-rail-first {
            order: -1;
        }

        .profile-menu-side-dropdown {
            position: absolute;
            top: 0;
            right: calc(100% + 10px);
            min-width: 220px;
            background: #fff;
            border-radius: .75rem;
            box-shadow: 0 16px 40px rgba(0,0,0,.22);
            padding: .4rem;
            z-index: 1095;
        }

        [dir="rtl"] .profile-menu-side-dropdown {
            right: auto;
            left: calc(100% + 10px);
        }

        .profile-menu-side-dropdown-item {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .55rem .6rem;
            border-radius: .5rem;
            font-size: .82rem;
            font-weight: 500;
            color: #374151;
            text-decoration: none;
        }

        /* The create entries are <button> elements (they open a modal rather than
           navigating), so they need the link styling's border/background/fill
           reset to line up with the rest of the dropdown. */
        button.profile-menu-side-dropdown-item {
            width: 100%;
            background: transparent;
            border: 0;
            text-align: left;
        }

        button.profile-menu-side-dropdown-item:hover,
        button.profile-menu-side-dropdown-item:focus {
            background: #f6f5fd;
            color: #374151;
        }

        /* Font-size setting (Settings > Auto Settings > Right Panel Font Size) */
        .profile-menu-fixed.fs-small .profile-menu-btn { font-size: .72rem; }
        .profile-menu-fixed.fs-small .profile-menu-btn-bell { font-size: .85rem; }
        .profile-menu-fixed.fs-small .profile-menu-side-dropdown-item { font-size: .72rem; }

        .profile-menu-fixed.fs-large .profile-menu-btn { font-size: 1rem; }
        .profile-menu-fixed.fs-large .profile-menu-btn-bell { font-size: 1.2rem; }
        .profile-menu-fixed.fs-large .profile-menu-side-dropdown-item { font-size: .95rem; }

        .profile-menu-side-dropdown-item:hover {
            background: #F6F5FD;
            color: #5B4FE5;
        }

        .theme-picker-dropdown {
            min-width: 200px;
        }

        .theme-picker-heading {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #9CA3AF;
            padding: .3rem .5rem .5rem;
        }

        .theme-swatch-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .4rem;
            padding: 0 .1rem .1rem;
        }

        .theme-swatch-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .3rem;
            padding: .3rem;
            border: 0;
            background: transparent;
            border-radius: .5rem;
        }

        .theme-swatch-btn:hover {
            background: #F6F5FD;
        }

        .theme-swatch-preview {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 2px solid #E5E7EB;
            overflow: hidden;
        }

        .theme-swatch-btn.active .theme-swatch-preview {
            border-color: #5B4FE5;
        }

        .theme-swatch-accent {
            position: absolute;
            bottom: 3px;
            right: 3px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,.6);
        }

        .theme-swatch-check {
            color: #fff;
            font-size: .85rem;
            text-shadow: 0 1px 3px rgba(0,0,0,.6);
        }

        .theme-swatch-label {
            font-size: .68rem;
            font-weight: 500;
            color: #374151;
        }

        .profile-menu-activity-item {
            color: #212529 !important;
        }

        .profile-menu-activity-item i:first-child {
            color: #6c757d;
        }

        .profile-menu-card {
            position: fixed;
            top: 14px;
            right: 68px;
            width: 320px;
            height: calc(100vh - 28px);
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 16px 40px rgba(0,0,0,.22);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* With the top header on, the trigger buttons here are hidden (the header's
           own icons dispatch the open events instead), so anchor the panel below
           the header and flush to the right edge instead of beside a hidden bell. */
        body.has-top-header .profile-menu-card {
            top: 70px;
            right: 20px;
            height: calc(100vh - 84px);
        }

        /* RTL mirrors — this whole rail sits on the physical right in LTR, so in RTL it
           should sit on the physical left instead (the natural "trailing" edge either way). */
        [dir="rtl"] .profile-menu-fixed { right: auto; left: 0; border-left: none; border-right: 1px solid #eef0f3; box-shadow: 6px 0 16px rgba(0, 0, 0, .06); }
        [dir="rtl"] .profile-menu-card { right: auto; left: 68px; }
        [dir="rtl"] body.has-top-header .profile-menu-card { right: auto; left: 20px; }

        .profile-slide-enter {
            transition: transform .22s ease-out, opacity .22s ease-out;
        }
        .profile-slide-enter-start {
            opacity: 0;
            transform: translateX(28px);
        }
        .profile-slide-enter-end {
            opacity: 1;
            transform: translateX(0);
        }
        .profile-slide-leave {
            transition: transform .16s ease-in, opacity .16s ease-in;
        }
        .profile-slide-leave-start {
            opacity: 1;
            transform: translateX(0);
        }
        .profile-slide-leave-end {
            opacity: 0;
            transform: translateX(28px);
        }

        .profile-menu-scroll {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding-bottom: .5rem;
        }

        .activity-panel-header {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1rem .75rem;
            border-bottom: 1px solid #eef0f2;
        }

        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            padding: .75rem 1rem;
            text-decoration: none;
            border-bottom: 1px solid #f4f5f6;
        }

        .activity-item:last-child {
            border-bottom: 0;
        }

        .activity-item:hover {
            background: #f8f9fa;
        }

        .activity-item-icon {
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .95rem;
        }

        .activity-item-title {
            display: block;
            font-size: .82rem;
            font-weight: 600;
            color: #212529;
            line-height: 1.35;
        }

        .activity-item-time {
            display: block;
            font-size: .72rem;
            color: #868e96;
            margin-top: .15rem;
        }

        .profile-menu-header {
            display: flex;
            align-items: flex-start;
            gap: .65rem;
            padding: 1rem 1rem .5rem;
        }

        .profile-menu-avatar-sq {
            flex-shrink: 0;
            width: 42px;
            height: 42px;
            border-radius: .5rem;
            overflow: hidden;
            background: #e9ecef;
            color: #adb5bd;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-menu-avatar-sq img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-menu-close {
            flex-shrink: 0;
            border: 0;
            background: transparent;
            color: #dc3545;
            font-size: .8rem;
            padding: .25rem;
            line-height: 1;
        }

        .profile-menu-ids {
            padding: 0 1rem .5rem;
            font-size: .72rem;
            color: #868e96;
        }

        .profile-menu-lang-row {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: 0 1rem .75rem;
        }

        .profile-menu-lang-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            color: #6c757d;
            font-size: .75rem;
            flex-shrink: 0;
        }

        .profile-menu-lang-toggle {
            display: flex;
            background: #f1f3f5;
            border-radius: .6rem;
            padding: 2px;
            gap: 2px;
        }

        .profile-menu-lang-btn {
            border: 0;
            background: transparent;
            color: #6c757d;
            font-size: .75rem;
            font-weight: 600;
            padding: .35rem .75rem;
            border-radius: .5rem;
            transition: all .15s ease;
        }

        .profile-menu-lang-btn:hover {
            color: #343a40;
        }

        .profile-menu-lang-btn.active {
            background: #4f46e5;
            color: #fff;
            box-shadow: 0 2px 6px rgba(79, 70, 229, .3);
        }

        .profile-menu-actions-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1rem .75rem;
        }

        .profile-menu-link {
            font-size: .82rem;
            font-weight: 600;
            color: #0d6efd;
            text-decoration: none;
            padding: 0;
            display: inline-flex;
            align-items: center;
            gap: .3rem;
        }

        .profile-menu-divider {
            border-top: 1px solid #eef0f2;
            margin: 0 0 .5rem;
        }

        .profile-menu-trial {
            margin: 0 1rem .5rem;
            background: #f8f9fa;
            border-radius: .6rem;
            padding: .6rem .75rem;
            font-size: .75rem;
            color: #495057;
            display: flex;
            align-items: flex-start;
            gap: .4rem;
        }

        .profile-menu-trial i {
            margin-top: .1rem;
            color: #6c757d;
        }

        .profile-menu-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .5rem;
            padding: .25rem .75rem .5rem;
        }

        .profile-menu-grid-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .35rem;
            padding: .6rem .25rem;
            border-radius: .6rem;
            text-decoration: none;
            color: #495057;
            font-size: .68rem;
            text-align: center;
            line-height: 1.2;
        }

        .profile-menu-grid-item:hover {
            background: #f8f9fa;
            color: #212529;
        }

        .profile-menu-grid-icon {
            width: 40px;
            height: 40px;
            border-radius: .6rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .profile-menu-pill-wrap {
            padding: 0 1rem .25rem;
        }

        .profile-menu-pill {
            display: flex;
            align-items: center;
            gap: .6rem;
            background: #f8f9fa;
            border-radius: .6rem;
            padding: .55rem .75rem;
            text-decoration: none;
            color: #343a40;
            font-size: .78rem;
            font-weight: 600;
        }

        .profile-menu-pill:hover {
            background: #f1f3f5;
        }

        .profile-menu-pill-icon {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: #fff;
            border: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: .75rem;
            flex-shrink: 0;
        }

        .profile-menu-more-heading {
            padding: .5rem 1rem .25rem;
            font-size: .85rem;
            font-weight: 700;
            color: #212529;
        }

        .profile-menu-more-item {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .5rem 1rem;
            font-size: .8rem;
            color: #0d6efd;
            text-decoration: none;
        }

        .profile-menu-more-item:hover {
            background: #f8f9fa;
        }

        .profile-menu-more-item i:first-child {
            color: #868e96;
            width: 16px;
            text-align: center;
        }

        .profile-menu-help-card {
            margin: .25rem 1rem 1rem;
            padding: 1rem;
            background: #fff;
            border: 1px solid #eef0f3;
            border-radius: .75rem;
            display: flex;
            flex-direction: column;
        }

        .profile-menu-help-title {
            font-size: .85rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: .5rem;
        }

        .profile-menu-help-link {
            display: block;
            width: 100%;
            text-align: left;
            border: 0;
            background: transparent;
            padding: .3rem 0;
            font-size: .82rem;
            font-weight: 500;
            color: #5B4FE5;
        }

        .profile-menu-help-link:hover {
            text-decoration: underline;
        }

        /* Info floating bar — a compact, content-height card (unlike the full-height
           profile/activity panels) that floats beside the rail like a notification. */
        .profile-info-card {
            height: auto;
            max-height: calc(100vh - 28px);
            width: 300px;
            overflow-y: auto;
        }

        body.has-top-header .profile-info-card {
            height: auto;
            max-height: calc(100vh - 28px);
        }

        .profile-info-card .profile-menu-scroll {
            overflow: visible;
            padding-bottom: .5rem;
        }

        .profile-info-section {
            background: #f8fafc;
            border: 1px solid #eef0f3;
            border-radius: .75rem;
            padding: .75rem;
            margin-bottom: .75rem;
        }

        .profile-info-section:last-child {
            margin-bottom: 0;
        }

        .profile-info-heading {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #9ca3af;
            margin-bottom: .6rem;
        }

        .profile-info-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .3rem 0;
        }

        .profile-info-row-label {
            font-size: .74rem;
            color: #6b7280;
            flex-shrink: 0;
        }

        .profile-info-row-value {
            font-size: .78rem;
            font-weight: 600;
            color: #16181d;
            text-align: right;
            min-width: 0;
        }

        .profile-info-muted {
            font-weight: 500;
            color: #9ca3af;
            white-space: nowrap;
        }

        .profile-info-plan-badge {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #fff;
            font-size: .68rem;
            font-weight: 700;
            text-transform: capitalize;
            letter-spacing: .03em;
            padding: .28rem .6rem;
            border-radius: 2rem;
        }

        .profile-info-status {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            font-size: .72rem;
            font-weight: 600;
            padding: .18rem .55rem;
            border-radius: 2rem;
        }

        .profile-info-status.status-trial,
        .profile-info-status.status-inactive {
            background: #fef3c7;
            color: #b45309;
        }

        .profile-info-status.status-active {
            background: #d1fae5;
            color: #047857;
        }

        .profile-info-status.status-suspended,
        .profile-info-status.status-expired,
        .profile-info-status.status-cancelled {
            background: #fee2e2;
            color: #b91c1c;
        }

        .profile-info-bar-track {
            height: 6px;
            background: #e5e7eb;
            border-radius: 3rem;
            overflow: hidden;
            margin-top: .5rem;
        }

        .profile-info-bar-fill {
            height: 100%;
            border-radius: 3rem;
            background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 100%);
        }

        .profile-info-bar-fill.is-warning {
            background: linear-gradient(90deg, #f59e0b 0%, #fbbf24 100%);
        }

        .profile-info-bar-fill.is-danger {
            background: #ef4444;
        }

        .profile-info-hint {
            font-size: .7rem;
            color: #6b7280;
            margin-top: .4rem;
        }

        .profile-info-link {
            display: flex;
            align-items: center;
            gap: .4rem;
            margin-top: .7rem;
            padding-top: .6rem;
            border-top: 1px dashed #e5e7eb;
            font-size: .74rem;
            font-weight: 600;
            color: #4f46e5;
            text-decoration: none;
        }

        .profile-info-link:hover {
            color: #4338ca;
        }

        .profile-info-card .activity-panel-header {
            border-radius: 1rem 1rem 0 0;
        }

        [dir="rtl"] .profile-info-row-value {
            text-align: left;
        }

        @media print {
            .profile-menu-fixed { display: none !important; }
        }
    
    </style>

    <script>
        /* One delegated handler for every quick-create entry, in the rail and in
           the top header alike — this partial is rendered in both header modes,
           so the buttons it describes can live in either place.

           The create screens are modal fragments, not pages: navigating to them
           returns a bare form with no layout. The app already has a loader for
           this (webModal.openGlobalModal), which the module pages use, so reuse
           it rather than linking to the fragment directly. */
        (function () {
            if (document.documentElement.dataset.createModalBound) {
                return;
            }
            document.documentElement.dataset.createModalBound = '1';

            document.addEventListener('click', function (event) {
                var trigger = event.target.closest('[data-create-url]');
                if (! trigger) {
                    return;
                }
                event.preventDefault();

                if (typeof webModal === 'undefined' || typeof GLOBAL_FN === 'undefined') {
                    return;
                }

                var minHeight = trigger.getAttribute('data-create-min-height');
                webModal.openGlobalModal({
                    title: trigger.getAttribute('data-create-title'),
                    url: GLOBAL_FN.buildUrl(trigger.getAttribute('data-create-url')),
                    content: null,
                    size: trigger.getAttribute('data-create-size'),
                    scroll: trigger.getAttribute('data-create-scroll') !== '0',
                    minHeight: minHeight || undefined
                });
            });
        })();
    </script>
@endauth
