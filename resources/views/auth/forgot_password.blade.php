
@extends('layouts.auth_template')
@section('title','Has olvidado tu contraseña')
@section('content')

    <div class="row w-100 mx-0">
        <div class="col-lg-4 mx-auto">
            <div class="auth-form-light text-left py-5 px-4 px-sm-5">
                <div class="brand-logo text-center">
                    <img src="{{asset('logoComplete.png')}}" style="width: 200px!important;" alt="logo">
                </div>
                <h3 class="text-center text-primary">¿Has olvidado tu contraseña? </h3>
                <h6 class="font-weight-light mt-4 text-center">Ingresa tu correo electrónico y te enviaremos instrucciones para restablecer tu contraseña</h6>
                @livewire('auth.forgot-password')
            </div>
        </div>
    </div>
@endsection
