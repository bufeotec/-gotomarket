@extends('layouts.dashboard_template')
@section('title','Historial liquidación')
@section('content')

    <link rel="stylesheet" href="{{asset('js/select2/dist/css/select2.min.css')}}">
    <script src="{{asset('js/select2/dist/js/select2.min.js')}}"></script>

    <div class="page-heading">
        <x-navegation-view text="Historial Liquidación por Transportista." />

        @livewire('liquidacion.historial-liquidacion')

    </div>

    <script src="{{asset('js/domain.js')}}"></script>


@endsection
