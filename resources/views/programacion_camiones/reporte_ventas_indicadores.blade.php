
@extends('layouts.dashboard_template')
@section('title','Reporte de Ventas en Indicadores')
@section('content')

    <link rel="stylesheet" href="{{asset('js/select2/dist/css/select2.min.css')}}">
    <script src="{{asset('js/select2/dist/js/select2.min.js')}}"></script>

    <div class="page-heading">
        <x-navegation-view text="Reporte de Ventas e Indicadores" />

        @livewire('programacioncamiones.reporteventaindicadores')

    </div>

    <script src="{{asset('js/domain.js')}}"></script>

@endsection
