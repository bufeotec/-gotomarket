@extends('layouts.dashboard_template')
@section('title','Gestión de Vendedores')
@section('content')



    <div class="page-heading">
        <x-navegation-view text="Lista de vendedores activos registrados en el sistema." />

        @livewire('crm.gestionvendedores')

    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
