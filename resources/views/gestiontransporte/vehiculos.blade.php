
@extends('layouts.dashboard_template')
@section('title','Vehículos')
@section('content')



    <div class="page-heading">
        <x-navegation-view text="Lista de vehículos activos registrados" />

        @livewire('gestiontransporte.vehiculos')

    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
