@extends('layouts.dashboard_template')
@section('title','Nuevo perfil')
@section('content')



    <div class="page-heading">
        <x-navegation-view text="Agregar nuevo perfil en el sistema." />
        @livewire('configuracion.nuevoperfiles', ['id_perfil' => $id_perfil])


    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
