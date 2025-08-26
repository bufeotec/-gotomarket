@extends('layouts.dashboard_template')
@section('title','Clientes')
@section('content')



    <div class="page-heading">
        <x-navegation-view text="Lista de clientes activos registrados en el sistema." />

        @livewire('configuracion.clientes')

    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
