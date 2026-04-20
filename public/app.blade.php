<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes"/>
    <meta name="color-scheme" content="light dark"/>
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)"/>
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)"/>
    <meta name="supported-color-schemes" content="light dark"/>
    <meta name="description" content="Free Logistics Software"/>
    <meta name="keywords" content="Free Logistics Software"/>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="module" content="@yield('js')">
    <meta name="app-js" content="{{ env('APP_JS') }}">
    <meta name="app-version" content="{{ appVersion() }}">
    <meta name="turbo-visit-control" content="reload">


    <title>{{ config('app.name', 'Flikma') }}</title>
    <!-- Fonts -->
    {{--<link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>--}}
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet"/>

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

    <link rel="stylesheet" href="{{ asset('css/adminlte.css?v='.appVersion()) }}"/>
    <link rel="stylesheet" href="{{ asset('css/manual.css?v='.appVersion()) }}"/>
    <link rel="stylesheet" href="{{ asset('css/jquery-confirm.css') }}"/>

    <!--end::Required Plugin(AdminLTE)-->

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('css/dataTables/dataTables.bootstrap5.min.css')}}">

    <!-- Toastr CSS -->
    <link href="{{ asset('css/toastr/toastr.min.css')}}" rel="stylesheet"/>

    <!-- SweetAlert2 CSS -->
    <link href="{{ asset('css/sweetalert/sweetalert2.min.css')}}" rel="stylesheet">

    {{--<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">--}}
    <link rel="stylesheet" href="{{ asset('css/bootstrap-select.css')}}">

    <!-- SweetAlert2 JS -->
    <script src="{{ asset('js/sweetalert2/sweetalert2.js')}}"></script>

    <link href="{{ asset('css/tom-select/tom-select.bootstrap5.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/flatpickr/flatpickr.min.css') }}">

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <style>
        body { font-family: 'Figtree', sans-serif; }
        .wrapper { display: flex; min-height: 100vh; }
        .main-content { flex: 1; min-width: 0; background-color: #f8f9fa; }
    </style>

    <!-- Scripts -->
    {{--@if(env('APP_JS') == 'local')
        <div id="dynamic-scripts"></div>
    @endif--}}
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    {{--<script
        src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
        crossorigin="anonymous"
    ></script>--}}

    @include('includes.js')
    {{--<script>
        //This for first tim error occur. otherwise turbo not work properly. after login. don't remove
        window.addEventListener("error", function (event) {
            /*console.error("⚠️ Global Uncaught Error:", event.message);*/
            window.turboHasError = true;
        }, true);
    </script>--}}
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary" data-module="@yield('js')">
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
<div class="app-wrapper">

    @include('includes.navigation')
    {{--<main class="app-main">
        @yield('content', $slot ?? '')
    </main>--}}
    {{--<aside class="gmail-sidebar bg-white border-end" id="gmailSidebar">
        <div class="p-3">
            <button class="btn btn-primary w-100 mb-3 rounded-pill d-flex align-items-center justify-content-center">
                <i class="bi bi-plus-lg"></i> <span class="ms-2 label-text">New</span>
            </button>
            <ul class="nav flex-column gmail-menu">
                <li><a href="#" class="active"><i class="bi bi-inbox me-2"></i> <span class="label-text">All Customers</span></a></li>
                <li><a href="#"><i class="bi bi-star me-2"></i> <span class="label-text">Starred</span></a></li>
                <li><a href="#"><i class="bi bi-send me-2"></i> <span class="label-text">Sent</span></a></li>
                <li><a href="#"><i class="bi bi-trash me-2"></i> <span class="label-text">Trash</span></a></li>
            </ul>
        </div>
    </aside>--}}
    <main id="content-wrapper" class="flex-grow-1 vh-100">
        <div class="gmail-layout bg-white">
            @include('includes.header')
            <div class="gmail-body">
                <turbo-frame id="main-content">
                    <div class="main-panel">
                        @yield('content', $slot ?? '')
                    </div>
                </turbo-frame>
            </div>
        </div>
        <div class="modal fade" id="globalModal" tabindex="-1" aria-hidden="true"
             data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-xl" id="globalModalDialog">
                <div class="modal-content bg-white rounded-3 shadow">
                    <div id="globalModalBody"></div>
                    <div class="modal-footer" id="globalModalFooter"></div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="quickModal" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog <!--modal-dialog-centered--> modal-lg" id="quickModalDialog">
                <div class="modal-content">
                    <div id="quickModalBody"></div>
                    <div class="modal-footer" id="quickModalFooter">
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" form="quickModuleForm">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{--<script>
            document.getElementById('openShortcutBtn').addEventListener('click', function () {
                document.getElementById('shortcutPanel').classList.remove('d-none');
            });

            document.getElementById('closeShortcutBtn').addEventListener('click', function () {
                document.getElementById('shortcutPanel').classList.add('d-none');
            });
        </script>--}}
    </main>
    @include('includes.footer')
    {{--<div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <!-- Page Heading -->


        <!-- Page Content -->
        <main>
            @yield('content', $slot ?? '')
        </main>
    </div>--}}
    <section class="space-y-6">
        <!-- Global Reusable Modal -->
        {{--<div class="modal fade" id="globalModal" tabindex="-1" aria-hidden="true"
             data-bs-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-xl" id="globalModalDialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-person-lines-fill me-2"></i><span id="globalModalTitle">Modal</span>
                        </h5>

                        <a class="btn-close" data-bs-dismiss="modal"></a>
                    </div>
                    <div class="modal-body" id="globalModalBody">
                        <div class="text-center p-4">
                            <div class="spinner-border text-primary" role="status"></div>
                            <div class="mt-2">Loading...</div>
                        </div>
                    </div>
                    <div class="modal-footer" id="globalModalFooter"></div>
                </div>
            </div>
        </div>--}}




        <!-- Bootstrap Confirm Modal -->
        {{--<div class="modal fade" id="confirmModal" aria-hidden="true" data-bs-backdrop="static"
             data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Action</h5>
                        --}}{{--<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>--}}{{--
                    </div>
                    <div class="modal-body">
                        <p id="confirmMessage">Are you sure you want to close this modal?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="confirmCancel" data-bs-dismiss="modal">
                            Discard
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmYes">Yes, Close</button>
                    </div>
                </div>
            </div>
        </div>--}}


    </section>
</div>
{{--<aside class="app-sidebar p-3 bg-dark text-white" style="min-width: 250px;">
    <h4 class="mb-4">My App</h4>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link text-white">Dashboard</a>
        </li>
        <li class="nav-item">
            <a href="{{ route('customers') }}" class="nav-link text-white">Customers</a>
        </li>
    </ul>
</aside>

--}}{{-- Content wrapper (only this reloads) --}}{{--
<main id="content-wrapper" class="flex-grow-1 p-4">
    <turbo-frame id="main-content">
        @yield('content', $slot ?? '')
    </turbo-frame>
</main>--}}
{{--<script src="{{ asset('js/adminlte.js') }}"></script>--}}
<div id="dynamic-scripts"></div>
<ul id="dropdown-suggestions"></ul>
<iframe id="print-frame" style="width:100%; height:60vh;display:none;"></iframe>
@include('activity.feed-view')
</body>
</html>
