@php
    // SetLocale (app/Http/Middleware/SetLocale.php) applies the session
    // locale the EN/AR toggle writes before this view ever renders, so
    // app()->getLocale() already reflects it here.
    $isArabicUi = app()->getLocale() === 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isArabicUi ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="module" content="@yield('js')">
    <meta name="app-js" content="{{ env('APP_JS') }}">
    <meta name="app-version" content="{{ appVersion() }}">
    <meta name="turbo-visit-control" content="reload">

    @php
        $tabApp = config('app.name') && config('app.name') !== 'Laravel' ? config('app.name') : 'Flikma';
        $tabPage = trim(html_entity_decode(strip_tags($__env->yieldContent('page-title')), ENT_QUOTES));
    @endphp
    <title>{{ $tabPage !== '' ? $tabPage . ' | ' . $tabApp : $tabApp }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/{{ $isArabicUi ? 'bootstrap.rtl.min.css' : 'bootstrap.min.css' }}" rel="stylesheet">

    {{--<link rel="preload" href="{{ asset('css/adminlte.css') }}" as="style"/>--}}
    <link href="{{ asset('fontawesome/css/all.css') }}" as="style"/>


    <!--begin::Fonts-->
    <link
        rel="stylesheet"
        href="{{ asset('css/fontsource/source-sans-3@5.0.12/index.css')}}"
        crossorigin="anonymous"
        media="print"
        onload="this.media='all'"
    />
    <!--end::Fonts-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
        rel="stylesheet"
        href="{{ asset('css/overlayscrollbars/overlayscrollbars.min.css')}}"
        crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->
    <!--begin::Required Plugin(AdminLTE)-->

    <link rel="stylesheet" href="{{ asset(($isArabicUi ? 'css/adminlte.rtl.css' : 'css/adminlte.css').'?v='.appVersion()) }}"/>

    <link rel="stylesheet" href="{{ asset('css/jquery-confirm.css') }}"/>

    <!--end::Required Plugin(AdminLTE)-->

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('css/dataTables/dataTables.bootstrap5.min.css')}}">

    <link rel="stylesheet" href="{{ asset('css/manual.css?v='.appVersion()) }}"/>

    <!-- Toastr CSS -->
    <link href="{{ asset('css/toastr/toastr.min.css')}}" rel="stylesheet"/>

    <!-- SweetAlert2 CSS -->
    <link href="{{ asset('css/sweetalert/sweetalert2.min.css')}}" rel="stylesheet">

    {{--<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">--}}
    <link rel="stylesheet" href="{{ asset('css/bootstrap-select.css')}}">

    <!-- SweetAlert2 JS -->
    <script src="{{ asset('js/sweetalert2/sweetalert2.js')}}"></script>

    {{-- Alpine comes from Livewire (@livewireScripts) and serves every x-data/x-show in the shell too.
         Loading it again from a CDN makes Livewire refuse to start ("multiple instances of Alpine"). --}}

    <link href="{{ asset('css/tom-select/tom-select.bootstrap5.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/flatpickr/flatpickr.min.css') }}">

    {{-- Alpine is bundled with Livewire v3 (@livewireScripts) — do not load it separately --}}

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @livewireStyles
    <style>
        body {
            font-family: 'Figtree', sans-serif;
        }
        body, html {
            height: 100%;
            margin: 0;
            overflow: hidden; /* Prevent double scrollbars */
        }

        .wrapper {
            display: flex;
            height: 100vh; /* Full viewport height */
            width: 100vw;
        }

        /* Keep sidebar fixed width and scrollable if menu is long */
        #sidebar-container {
            width: 250px; /* Adjust to your sidebar's actual width */
            height: 100%;
            overflow-y: auto;
            flex-shrink: 0;
            /* Bound to the sidebar theme (presets defined in
               layouts/sidebar.blade.php) so the column below a short menu
               matches the menu instead of staying hardcoded dark gray. */
            border-right: 1px solid var(--sb-border, #dee2e6);
            background-color: var(--sb-bg, #f8f9fa);
        }

        /* flex row's main-axis is already writing-direction aware, so the
           sidebar/main-content order mirrors on its own under dir="rtl" —
           only the physical border side needs flipping by hand. */
        [dir="rtl"] #sidebar-container {
            border-right: none;
            border-left: 1px solid var(--sb-border, #dee2e6);
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0; /* Prevents flex items from overflowing */
            background-color: #f8f9fa;
            height: 100%;
        }

        /* This allows the content area to scroll while header stays top */
        .content-scroll-area {
            flex: 1;
            overflow-y: auto;
            padding-bottom: 2rem;
        }

        /* The right rail is position:fixed, so without the header on it would sit
           on top of the page. Reserve its 60px column here; when the header is
           showing the rail collapses to zero width and needs no room. */
        body:not(.has-top-header) .wrapper {
            padding-right: 60px;
        }

        /* The rail itself lives in layouts/profile-menu.blade.php and needs
           its own `right: 0` -> `left: 0` flip there; this just reserves the
           matching column on the mirrored side. */
        [dir="rtl"] body:not(.has-top-header) .wrapper {
            padding-right: 0;
            padding-left: 60px;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        /* Hide app chrome when printing: only the page content should print */
        @media print {
            #sidebar-container,
            header.navbar,
            .profile-menu-fixed {
                display: none !important;
            }
            body, html {
                overflow: visible !important;
                height: auto !important;
            }
            .wrapper {
                display: block !important;
                height: auto !important;
                width: auto !important;
                padding-right: 0 !important;
            }
            .main-content {
                display: block !important;
                height: auto !important;
                background: white !important;
            }
            .content-scroll-area {
                overflow: visible !important;
                padding-bottom: 0 !important;
            }
        }
    </style>
    <script>
        // Applied before the sidebar paints so a saved theme never flashes the
        // default dark gray first. The color rules for each preset live in
        // layouts/sidebar.blade.php.
        document.documentElement.setAttribute('data-sidebar-theme',
            localStorage.getItem('flikma-sidebar-theme') || 'gray');
    </script>
    @include('includes.js')
</head>
@php
    /* Top header on/off. When it's on, the header hosts the quick-create /
       theme / account actions and the right rail collapses to nothing. When
       it's off, those same actions move into the rail instead. Defaulting to
       true keeps the header showing for anyone who has never set it.
       Resolved before <body> because the body tag carries the mode class that
       the rail's CSS keys off. */
    $headerEnabled = headerEnabledForUser();
@endphp
<body data-module="@yield('js')" class="@if($headerEnabled) has-top-header @endif">
<input type="hidden" value="@yield('extra-js')" id="extra-js">
@php
    $segments = request()->segments();
        $segment1 = $segments[0] ?? '';
        $segment2 = $segments[1] ?? '';
        $segment3 = $segments[2] ?? '';
        $segment4 = $segments[3] ?? '';
        $menu = $segment1;
        $submenu = $segment2;
        $user = \Illuminate\Support\Facades\Auth::user();
    @endphp
<div x-data="{ sidebarOpen: false }" class="wrapper">

    <div id="sidebar-container">
        @include('layouts.sidebar')
    </div>

    <div class="main-content">
        @if($headerEnabled)
            {{-- Masters pages print the header inside their right-hand content (includes/master-page-title),
                 so it starts beside the Master Data menu instead of spanning above it. --}}
            @unless(request()->is('masters', 'masters/*'))
                @include('includes.header')
            @endunless
        @else
            {{-- Header off: the rail carries the actions, but the page keeps its
                 own breadcrumb/title/subtitle in a slim topbar. --}}
            @include('layouts.topbar')
        @endif

        <main class="content-scroll-area">
            @yield('content', $slot ?? '')
        </main>
    </div>

    @include('layouts.profile-menu')
</div>


<div class="modal fade" id="globalModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl" id="globalModalDialog">
        <div class="modal-content border-0 shadow-lg">
            <div id="globalModalBody"></div>
            <div class="modal-footer" id="globalModalFooter"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="quickModal" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div id="quickModalBody"></div>
            <div class="modal-footer" id="quickModalFooter">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" form="quickModuleForm">Save</button>
            </div>
        </div>
    </div>
</div>

<div id="dynamic-scripts"></div>
<ul id="dropdown-suggestions"></ul>
<iframe id="print-frame" style="display:none;"></iframe>
    @include('activity.feed-view')

    @include('partials.briefing-modal')

    @livewireScripts
{{-- One-line help under confusing form fields (texts live in includes/master-field-hints) --}}
@auth
    @include('includes.master-field-hints')
@endauth
</body>
</html>
