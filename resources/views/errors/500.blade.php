
@extends('layouts.auth_template_error')
@section('title','500 - Venturis')
@section('content')
    <div class="error-page container">
        <div class="col-md-8 col-12 offset-md-2">
            <img class="img-error" src="{{asset('errors/500.svg')}}" alt="Not Found">
            <div class="text-center">
                <h1 class="error-title mt-2">Error del sistema</h1>
                <p class="fs-5 text-gray-600">El sitio web no está disponible en este momento. Inténtalo de nuevo más tarde o ponte en contacto con el desarrollador.</p>
                <a href="{{route('intranet')}}" class="btn btn-lg btn-outline-primary mt-3">Volver</a>
            </div>
        </div>
    </div>
@endsection
