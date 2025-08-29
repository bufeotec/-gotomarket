@extends('layouts.dashboard_template')
@section('title','STOCK')
@section('content')



    <div class="page-heading">
        <x-navegation-view text="Lista de stock registrados en el sistema." />

        @livewire('crm.stocks')

    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
