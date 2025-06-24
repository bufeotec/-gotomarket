@extends('layouts.dashboard_template')
@section('title','Crear usuario')
@section('content')



    <div class="page-heading">
        <x-navegation-view text="Agregar nuevo usuario en el sistema." />

        @livewire('configuracion.crearusuarios', ['id_users' => $id_users])

    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
