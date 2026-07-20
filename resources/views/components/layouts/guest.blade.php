<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <style>
        body { font-family: 'Figtree', sans-serif; }
    </style>
</head>
<body class="bg-light text-dark">
<div class="min-vh-100 d-flex flex-column justify-content-center align-items-center">
    {{--<div>
        <a href="/">
            <x-application-logo style="width: 80px; height: 80px;" class="text-secondary" />
        </a>
    </div>--}}

    <div class="w-100 bg-white shadow-sm" style="border-radius: 0.5rem;">
        {{ $slot }}
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script defer src="{{ asset('js/app.js') }}"></script>
</body>
</html>
