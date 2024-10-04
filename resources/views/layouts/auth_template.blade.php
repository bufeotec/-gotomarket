<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title')</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <link rel="stylesheet" href="{{asset('assets/css/general.css')}}">
        <link rel="stylesheet" href="{{asset('assets/auth/feather/feather.css')}}">
        <link rel="stylesheet" href="{{asset('assets/auth/ti-icons/css/themify-icons.css')}}">
        <link rel="stylesheet" href="{{asset('assets/auth/css/vendor.bundle.base.css')}}">
        <link rel="stylesheet" href="{{asset('assets/auth/vertical-layout-light/style.css')}}">
        <link rel="stylesheet" href="{{asset('fontawesone/css/all.css')}}">
        <link rel="shortcut icon" href="{{asset('isologoCompleteGo.png')}}" />

        @livewireStyles
    </head>
    <body>
        <div class="container-scroller">
            <div class="container-fluid page-body-wrapper full-page-wrapper">
                <div class="content-wrapper d-flex align-items-center auth px-0">
                    @yield('content')
                </div>
            </div>
        </div>
        @livewireScripts
        <script src="{{asset('assets/auth/js/vendor.bundle.base.js')}}"></script>
        <script src="{{asset('assets/auth/js/off-canvas.js')}}"></script>
        <script src="{{asset('assets/auth/js/hoverable-collapse.js')}}"></script>
        <script src="{{asset('assets/auth/js/template.js')}}"></script>
        <script src="{{asset('assets/auth/js/settings.js')}}"></script>
        <script src="{{asset('assets/auth/js/todolist.js')}}"></script>
    </body>
</html>
