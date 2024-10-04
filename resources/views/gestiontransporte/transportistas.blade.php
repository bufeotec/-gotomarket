
@extends('layouts.dashboard_template')
@section('title','Transportistas')
@section('content')



    <div class="page-heading">
        <x-navegation-view text="Lista de transportistas registrados en el sistema." />

{{--        @livewire('configuracion.menus')--}}

    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
