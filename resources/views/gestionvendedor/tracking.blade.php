@extends('layouts.dashboard_template')
@section('title','Tracking')
@section('content')


    <div class="page-heading">
        <x-navegation-view text="Estado y Seguimiento de Guías." />

        @livewire('gestionvendedor.trackings')

    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
