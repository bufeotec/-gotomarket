@extends('layouts.dashboard_template')
@section('title','Registrar guías de remisión')
@section('content')

    <link rel="stylesheet" href="{{asset('js/select2/dist/css/select2.min.css')}}">
    <script src="{{asset('js/select2/dist/js/select2.min.js')}}"></script>

    <div class="page-heading">
        <x-navegation-view text="Lista de guías de remisión registradas en el sistema." />

        @livewire('programacioncamiones.registrarguiasremisiones')

    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
