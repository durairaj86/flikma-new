{{--
    Slim topbar shown when the company has the full top header turned off.

    The rail takes over the action icons in that mode, but the page still needs
    its own heading — breadcrumb, title, subtitle and the page-title action
    slot — so it is rendered here at a reduced height instead of the full
    includes/header.blade.php. Mirrors the reference app's topbar.blade.php.

    Only the heading lives here on purpose: company switcher, search, profile
    and theme controls stay in the rail so the two modes don't duplicate them.
--}}
{{-- A page can drop the slim topbar with @section('hide-topbar', true). Every Masters page drops it too (the full header already hides itself there); their title is printed in the page content by includes/master-page-title. --}}
@auth
@unless(View::hasSection('hide-topbar') || request()->is('masters', 'masters/*'))
    <header class="sticky-top bg-white border-bottom shadow-sm px-3 py-2" style="z-index: 1020;">
        <div class="container-fluid d-flex align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3 overflow-hidden">
                <button class="btn btn-light d-lg-none border" type="button" @click="sidebarOpen = !sidebarOpen">
                    <i class="bi bi-list fs-5"></i>
                </button>
                @include('includes.page-heading')
            </div>
            <div class="d-flex align-items-center gap-2">
                @include('includes.language-toggle')
            </div>
        </div>
    </header>
@endunless
@endauth
