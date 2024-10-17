
@extends('layouts.dashboard_template')
@section('title','Tarifario')
@section('content')



    <div class="page-heading">
        <x-navegation-view text="Lista de tarifarios activos registrados en cada transportista de {{$informacion_transportista->transportista_nom_comercial}}." />

        @livewire('registroflete.tarifarios',['id'=>$informacion_transportista->id_transportistas])

    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
