
@extends('layouts.auth_template_error')
@section('title','401 - Venturis')
@section('content')
    <div class="error-page container">
        <div class="col-md-8 col-12 offset-md-2 text-center">
            <img class="img-error" src="{{asset('errors/401.svg')}}" style="width: 70%;" alt="Not Found">
            <div class="text-center">
                <h1 class="error-title mt-2">No autorizado</h1>
                <p class="fs-5 text-gray-600">
                    Lo sentimos, no tienes permiso para acceder a esta página. Por favor, inicia sesión con una cuenta autorizada o contacta al administrador si crees que es un error.
                </p>
                <a href="{{route('intranet')}}" class="btn btn-lg btn-outline-primary mt-3 mb-4">Volver</a>
            </div>
        </div>
    </div>

@endsection
