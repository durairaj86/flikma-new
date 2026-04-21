<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="module" content="@yield('js')">
    <meta name="app-js" content="{{ env('APP_JS') }}">
    <meta name="app-version" content="{{ appVersion() }}">
    <meta name="turbo-visit-control" content="reload">

    <title>{{ config('app.name', 'Flikma') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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

    <link href="{{ asset('css/tom-select/tom-select.bootstrap5.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/flatpickr/flatpickr.min.css') }}">

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

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
            border-right: 1px solid #dee2e6;
            background-color: rgba(52, 58, 64);
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

        .dropdown-menu {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @include('includes.js')
</head>
<body data-module="@yield('js')">
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
        @include('includes.header')

        <main class="content-scroll-area">
            @yield('content', $slot ?? '')
        </main>
    </div>
</div>


<div class="modal fade" id="globalModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
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

    @livewireScripts
</body>
</html>
