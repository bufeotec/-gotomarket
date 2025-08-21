@extends('layouts.dashboard_template')
@section('title','Gestión de Puntos')
@section('content')



    <div class="page-heading">
        <x-navegation-view text="Lista de Puntos de Vendedores por Cliente y Campaña." />

        @livewire('crm.gestionpuntos')

    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
