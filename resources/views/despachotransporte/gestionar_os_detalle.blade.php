@extends('layouts.dashboard_template')
@section('title','Gestionar OS Detalle')
@section('content')

{{--    <link rel="stylesheet" href="{{asset('js/select2/dist/css/select2.min.css')}}">--}}
{{--    <script src="{{asset('js/select2/dist/js/select2.min.js')}}"></script>--}}

    <div class="page-heading">
        <x-navegation-view text="Gestionar OS." />

        @livewire('despachotransporte.gestionarosdetalles',['id_despacho'=>$informacion_despacho->id_despacho])

    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
