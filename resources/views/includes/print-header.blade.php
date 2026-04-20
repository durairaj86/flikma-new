<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('includes.print-css')
</head>
<body>
@yield('print-content')
</body>
</html>
