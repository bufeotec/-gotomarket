
@extends('layouts.auth_template_error')
@section('title','400 - Venturis')
@section('content')
    <div class="error-page container">
        <div class="col-md-8 col-12 offset-md-2 text-center">
            <img class="img-error" src="{{asset('errors/400.svg')}}" style="width: 70%;" alt="Not Found">
            <div class="text-center">
                <h1 class="error-title mt-2">Solicitud incorrecta</h1>
                <p class="fs-5 text-gray-600">
                    La solicitud no pudo ser procesada debido a un error en la entrada. Por favor, verifica los datos enviados e intenta nuevamente.
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
