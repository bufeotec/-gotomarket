
@extends('layouts.dashboard_template')
@section('title','Usuarios')
@section('content')


    <div class="page-heading">
        <x-navegation-view text="Lista de usuarios activos registrados en el sistema." />

        @livewire('configuracion.usuarios')

    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
