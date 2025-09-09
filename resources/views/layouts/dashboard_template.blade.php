<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Go To Market SAC</title>

    <link rel="stylesheet" href="{{asset('assets/css/fonts.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/bootstrap.css')}}">

    <link rel="stylesheet" href="{{asset('assets/vendors/perfect-scrollbar/perfect-scrollbar.css')}}">
    <link rel="stylesheet" href="{{asset('assets/vendors/bootstrap-icons/bootstrap-icons.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/app.css')}}">
    <link rel="shortcut icon" href="{{asset('isologoCompleteGo.png')}}" type="image/x-icon">
    <link rel="stylesheet" href="{{asset('assets/css/general.css')}}">
    <link rel="stylesheet" href="{{asset('fontawesone/css/all.css')}}">
    <script src="{{asset('js/jquery-3.6.3.min.js')}}"></script>

    @vite(['resources/css/app.css'])
    @vite(['resources/js/app.js'])

    @livewireStyles
</head>

<body style="background-color: #f5f5f9!important;">
{{--<body style="background-color: #f5f5f9!important;">--}}
    <div id="app">
        @include('layouts.parts.sidebar')
        <div id="main" class='layout-navbar'>
            @include('layouts.parts.topnavbar')

            <div id="maincontent">
                @yield('content')
            </div>
            <div style="padding: 0 2rem">
                @include('layouts.parts.footer')

            </div>

        </div>
    </div>
    @livewireScripts
    <script src="{{asset('assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js')}}"></script>
    <script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>

    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-tooltip="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>
    <script src="{{asset('assets/js/main.js')}}"></script>
    <style>
        .alert {
            position: relative;
            padding: 0.4rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: .25rem;
        }
        .btn-close {
            box-sizing: content-box;
            width: 1em;
            height: 0em;
            padding: .25em;
            color: #000;
            background: transparent url(data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3E%3C/svg%3E) 50% / 1em auto no-repeat;
            border: 0;
            border-radius: .25rem;
            opacity: .5;
        }
        .table {
            --bs-table-bg: transparent;
            --bs-table-striped-color: #607080;
            --bs-table-striped-bg: rgba(0, 0, 0, 0.05);
            --bs-table-active-color: #607080;
            --bs-table-active-bg: rgba(0, 0, 0, 0.1);
            --bs-table-hover-color: #607080;
            --bs-table-hover-bg: rgba(0, 0, 0, 0.075);
            width: 100%;
             margin-bottom: 0rem!important;
            color: #607080;
            vertical-align: top;
            border-color: #eee;
        }
        .table>:not(caption)>*>* {
            padding: .8rem;
            background-color: var(--bs-table-bg);
            background-image: linear-gradient(var(--bs-table-accent-bg), var(--bs-table-accent-bg));
            border-bottom-width: 1px;
        }
    </style>
</body>

</html>
