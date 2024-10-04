
@extends('layouts.dashboard_template')
@section('title','Roles')
@section('content')


    <div class="page-heading">
        <x-navegation-view text="Lista de roles registrados en el sistema." />

        @livewire('configuracion.roles')

    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
