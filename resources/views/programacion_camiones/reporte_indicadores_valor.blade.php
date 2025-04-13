
@extends('layouts.dashboard_template')
@section('title','Reportes de Flete: Liquidaciones Aprobadas')
@section('content')

    <script src="{{asset('js/chart.js')}}"></script>


    <div class="page-heading">
        <x-navegation-view text="Lista de reportes registrados en el sistema." />

        @livewire('programacioncamiones.reporteindicadoresvalor')

    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
