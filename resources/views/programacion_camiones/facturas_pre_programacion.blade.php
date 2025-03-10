@extends('layouts.dashboard_template')
@section('title','Registro de documentos')
@section('content')

    <link rel="stylesheet" href="{{asset('js/select2/dist/css/select2.min.css')}}">
    <script src="{{asset('js/select2/dist/js/select2.min.js')}}"></script>

    <div class="page-heading">
        <x-navegation-view text="Registro detallado de los documentos." />
        @livewire('programacioncamiones.facturaspreprogramaciones')
    </div>

    <script src="{{asset('js/domain.js')}}"></script>


@endsection
