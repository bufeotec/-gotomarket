@extends('layouts.dashboard_template')
@section('title','Gestionar notas de crédito')
@section('content')

    <link rel="stylesheet" href="{{asset('js/select2/dist/css/select2.min.css')}}">
    <script src="{{asset('js/select2/dist/js/select2.min.js')}}"></script>

    <div class="page-heading">
        <x-navegation-view text="Listado de notas de crédito." />
        @livewire('programacioncamiones.gestionarnotascreditos')
    </div>

    <script src="{{asset('js/domain.js')}}"></script>


@endsection
