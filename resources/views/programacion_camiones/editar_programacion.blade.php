@extends('layouts.dashboard_template')
@section('title','Programación de camiones')
@section('content')

    <link rel="stylesheet" href="{{asset('js/select2/dist/css/select2.min.css')}}">
    <script src="{{asset('js/select2/dist/js/select2.min.js')}}"></script>
    <div class="page-heading">
        <x-navegation-view text="Modificar informacion de la programacion {{$conteo == 1 ? 'LOCAL' : ''}}{{$conteo == 2 ? 'PROVINCIAL' : ''}}{{$conteo == 3 ? 'MIXTO' : ''}}." />

        @if($conteo == 1) {{-- local--}}
            @livewire('programacioncamiones.local',['id'=>$id_programacion])
        @elseif($conteo == 2) {{-- provincial --}}
            @livewire('programacioncamiones.provincial',['id'=>$id_programacion])
        @else
            @livewire('programacioncamiones.mixto',['id'=>$id_programacion])
        @endif
    </div>

    <script src="{{asset('js/domain.js')}}"></script>
@endsection
