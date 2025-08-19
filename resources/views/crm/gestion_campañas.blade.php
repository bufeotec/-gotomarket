@extends('layouts.dashboard_template')
@section('title','Gestión de Campañas')
@section('content')



    <div class="page-heading">
        <x-navegation-view text="Lista de campañas registrados en el sistema." />

        @livewire('crm.gestioncampañas')

    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
