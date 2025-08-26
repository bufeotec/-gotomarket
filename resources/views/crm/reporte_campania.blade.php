@extends('layouts.dashboard_template')
@section('title','Reporte Campaña')
@section('content')



    <div class="page-heading">
        <x-navegation-view text="Reporte por campaña." />

        @livewire('crm.reportescampanias')

    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
