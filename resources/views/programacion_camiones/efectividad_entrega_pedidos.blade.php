@extends('layouts.dashboard_template')
@section('title','Efectividad entrega pedidos')
@section('content')

    <link rel="stylesheet" href="{{asset('js/select2/dist/css/select2.min.css')}}">
    <script src="{{asset('js/select2/dist/js/select2.min.js')}}"></script>
    <script src="{{asset('js/chart.js')}}"></script>

    <div class="page-heading">
        <x-navegation-view text="Reporte efectividad entrega pedidos." />
        @livewire('programacioncamiones.efectividadentregapedidos')
    </div>

    <script src="{{asset('js/domain.js')}}"></script>


@endsection
