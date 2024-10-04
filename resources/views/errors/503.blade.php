
@extends('layouts.auth_template_error')
@section('title','503 - Venturis')
@section('content')
    <div class="error-page container">
        <div class="col-md-8 col-12 offset-md-2 text-center">
            <img class="img-error" src="{{asset('errors/503.svg')}}" style="width: 70%;" alt="Not Found">
            <div class="text-center">
                <h1 class="error-title mt-2">Servicio No Disponible</h1>
                <p class="fs-5 text-gray-600">
                    Lo sentimos, actualmente estamos realizando tareas de mantenimiento o el servidor no puede procesar su solicitud en este momento.
                </p>
                <a href="{{route('intranet')}}" class="btn btn-lg btn-outline-primary mt-3 mb-4">Volver</a>
            </div>
        </div>
    </div>
    <style>
        #error {
            height: 100vh;
            background-color: #ebf3ff;
            padding-top: 4rem;
        }
    </style>
@endsection
