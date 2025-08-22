@extends('layouts.dashboard_template')
@section('title','Seleccionar Premios')
@section('content')



    <div class="page-heading">
        <x-navegation-view text="Lista de premios activos registrados en el sistema." />

        @livewire('crm.seleccionarpremios')

    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
