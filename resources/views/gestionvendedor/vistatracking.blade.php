@extends('layouts.dashboard_template')
@section('title','Vista del tracking')
@section('content')



    <div class="page-heading">
        <x-navegation-view text="Info de {{$informacion_fac->fac_pre_prog_factura}}. Número de Documento: {{$num_doc}}" />

        @livewire('gestionvendedor.vistatrackings', ['id' => $informacion_fac->id_fac_pre_prog, 'numdoc' => $num_doc])


    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
