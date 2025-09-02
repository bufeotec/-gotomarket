@extends('layouts.dashboard_template')
@section('title','Consulta de Stock')
@section('content')



    <div class="page-heading">
        <x-navegation-view text="Listado de Stock Mercadería." />

        @livewire('crm.stocks')

    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
