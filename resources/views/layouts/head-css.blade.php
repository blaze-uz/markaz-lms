@yield('css')
@stack('css')
{{-- layout.js, bootstrap.min.css, icons.min.css and app.min.css belong to the paid theme and are not in the repository (see README). --}}
<!-- Layout config Js -->
@if (file_exists(public_path('assets/js/layout.js')))
    <script src="{{ asset('assets/js/layout.js') }}"></script>
@endif
<!-- Bootstrap Css -->
@if (file_exists(public_path('assets/css/bootstrap.min.css')))
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
@endif
<!-- Icons Css -->
@if (file_exists(public_path('assets/css/icons.min.css')))
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
@endif
<!-- App Css-->
@if (file_exists(public_path('assets/css/app.min.css')))
    <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
@endif
<!-- custom Css-->
<link href="{{ asset('assets/css/custom.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />

<style>
    .navbar-menu .navbar-nav .nav-link[data-bs-toggle=collapse]:after {
        content: none;
    }

    .collapse.show {
        color: #fff !important;
    }
</style>
@livewireStyles
