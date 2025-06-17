@extends('layouts.dashboard_template')
@section('title','Vendedores')
@section('content')



    <div class="page-heading">
        <x-navegation-view text="Lista de vendedores activos registrados" />

        @livewire('vendedor.vendedores')

    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
