@extends('layouts.dashboard_template')
@section('title','Notas de crédito')
@section('content')

    <link rel="stylesheet" href="{{asset('js/select2/dist/css/select2.min.css')}}">
    <script src="{{asset('js/select2/dist/js/select2.min.js')}}"></script>

    <div class="page-heading">
        <x-navegation-view text="Registro detallado de anulaciones y Notas de Crédito." />
        @livewire('programacioncamiones.notascreditos')
    </div>

    <script src="{{asset('js/domain.js')}}"></script>


@endsection
