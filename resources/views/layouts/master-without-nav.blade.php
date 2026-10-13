<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-topbar="light" data-sidebar-image="none">

<head>
    <meta charset="utf-8"/>
    <title>@yield('title') | Markaz LMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Markaz LMS" name="description"/>
    <meta content="Markaz LMS" name="author"/>
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('images/markaz-lms.svg') }}">
    @include('layouts.head-css')
    @yield('css')
</head>

@yield('body')

@yield('content')

@include('layouts.vendor-scripts')
</body>
</html>
