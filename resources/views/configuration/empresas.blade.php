
@extends('layouts.dashboard_template')
@section('title','Empresas')
@section('content')
    <link rel="stylesheet" href="{{asset('js/select2/dist/css/select2.min.css')}}">
    <script src="{{asset('js/select2/dist/js/select2.min.js')}}"></script>

    <div class="page-heading">
        <x-navegation-view text="Lista de empresas activos registrados en el sistema." />
        @livewire('configuracion.empresas')
    </div>
    <script src="{{asset('js/domain.js')}}"></script>
@endsection
