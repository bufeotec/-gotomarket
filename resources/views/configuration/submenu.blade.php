
@extends('layouts.dashboard_template')
@section('title','Submenus')
@section('content')



    <div class="page-heading">
        <x-navegation-view text="Lista de submenús activos registrados en el menú de {{$informacion_menu->menu_name}}." />

        @livewire('configuracion.submenus',['id'=>$informacion_menu->id_menu])

    </div>

    <script src="{{asset('js/domain.js')}}"></script>
{{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
