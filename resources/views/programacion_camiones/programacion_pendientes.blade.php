@extends('layouts.dashboard_template')
@section('title','Gestionar Despacho')
@section('content')

    <link rel="stylesheet" href="{{asset('js/select2/dist/css/select2.min.css')}}">
    <script src="{{asset('js/select2/dist/js/select2.min.js')}}"></script>

    <div class="page-heading">
        <x-navegation-view text="Registro detallado de las programaciones pendientes de aprobar." />
        @livewire('programacioncamiones.ProgramacionesPendientes')
    </div>

    <script src="{{asset('js/domain.js')}}"></script>


@endsection
