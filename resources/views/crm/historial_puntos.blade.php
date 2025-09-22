@extends('layouts.dashboard_template')
@section('title','Historial de Puntos')
@section('content')



    <div class="page-heading">
        <x-navegation-view text="Historial de puntos." />

        @livewire('crm.historialpuntos')

    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
