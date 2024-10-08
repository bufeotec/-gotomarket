
@extends('layouts.dashboard_template')
@section('title','Vehiculos')
@section('content')



    <div class="page-heading">
        <x-navegation-view text="Lista de vehiculos activos registrados con {{$informacion_vehiculo->transportista_nom_comercial}}." />

        @livewire('gestiontransporte.vehiculos',['id'=>$informacion_vehiculo->id_transportistas])

    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
