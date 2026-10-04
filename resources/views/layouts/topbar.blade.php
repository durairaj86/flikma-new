{{--
    Slim topbar shown when the company has the full top header turned off.

    The rail takes over the action icons in that mode, but the page still needs
    its own heading — breadcrumb, title, subtitle and the page-title action
    slot — so it is rendered here at a reduced height instead of the full
    includes/header.blade.php. Mirrors the reference app's topbar.blade.php.

    Only the heading lives here on purpose: company switcher, search, profile
    and theme controls stay in the rail so the two modes don't duplicate them.
--}}
{{--
    Header off, every page: no bar. The page title (and subtitle / "How it works" action) is printed at the top of the
    page instead. Pages that print their own title inside their content (@section('hide-topbar'), every Masters page)
    get only the small phone menu button here, so the sidebar can still be opened on a phone.
--}}
@auth
    @if(View::hasSection('hide-topbar') || request()->is('masters', 'masters/*'))
        <div class="d-lg-none bg-white px-3 pt-2 d-print-none">
            <button class="btn btn-light border" type="button" @click="sidebarOpen = !sidebarOpen" aria-label="{{ __('Menu') }}">
                <i class="bi bi-list fs-5"></i>
            </button>
        </div>
    @else
        <div class="page-title-strip bg-white px-3 px-lg-4 pt-3 pb-2 d-flex align-items-start gap-3 d-print-none">
            <button class="btn btn-light border d-lg-none flex-shrink-0" type="button" @click="sidebarOpen = !sidebarOpen" aria-label="{{ __('Menu') }}">
                <i class="bi bi-list fs-5"></i>
            </button>
            @if(trim(strip_tags($__env->yieldContent('page-title'))) !== '')
                <div class="min-w-0">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                        @stack('page-title-action')
                    </div>
                    @hasSection('page-subtitle')
                        <div class="text-muted small mt-1">@yield('page-subtitle')</div>
                    @endif
                </div>
            @endif
        </div>
    @endif
@endauth
