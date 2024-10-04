
@extends('layouts.auth_template_error')
@section('title','404 - Venturis')
@section('content')
    <div class="error-page container">
        <div class="col-md-8 col-12 offset-md-2">
            <img class="img-error" src="{{asset('errors/404.svg')}}" alt="Not Found">
            <div class="text-center">
                <h1 class="error-title">NO ENCONTRADO</h1>
                <p class="fs-5 text-gray-600">La página que estás buscando no se encontró.</p>
                <a href="{{route('intranet')}}" class="btn btn-lg btn-outline-primary mt-3">Volver</a>
            </div>
        </div>
    </div>
@endsection
