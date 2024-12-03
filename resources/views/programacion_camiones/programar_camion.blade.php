@extends('layouts.dashboard_template')
@section('title','Programación de camiones')
@section('content')

    <link rel="stylesheet" href="{{asset('js/select2/dist/css/select2.min.css')}}">
    <script src="{{asset('js/select2/dist/js/select2.min.js')}}"></script>
{{--    <link href="https://cdn.datatables.net/v/bs5/dt-2.1.8/af-2.7.0/b-3.2.0/b-html5-3.2.0/datatables.min.css" rel="stylesheet">--}}
    <div class="page-heading">
        <x-navegation-view text="Programar un camión." />

        @livewire('programacioncamiones.option-tabs')

    </div>

    <script src="{{asset('js/domain.js')}}"></script>
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>--}}
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>--}}
{{--    <script src="https://cdn.datatables.net/v/bs5/dt-2.1.8/af-2.7.0/b-3.2.0/b-html5-3.2.0/datatables.min.js"></script>--}}

@endsection
