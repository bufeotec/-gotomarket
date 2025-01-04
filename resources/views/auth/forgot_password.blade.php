
@extends('layouts.auth_template')
@section('title','Has olvidado tu contraseña')
@section('content')

    <div class="row w-100 mx-0">
        <div class="col-lg-4 mx-auto">
            <div class="auth-form-light text-left py-5 px-4 px-sm-5">
                <div class="brand-logo text-center d-flex align-items-center justify-content-center">
                    <img src="{{asset('isologoCompleteGo.png')}}" style="width: 50px!important;" alt="logo">
                    <h3 class="mb-0 font-weight-bold ml-2" style="font-weight: 800!important;">GO TO MARKET</h3>
                </div>
                <h3 class="text-center text-primary">¿Has olvidado tu contraseña? </h3>
                <h6 class="font-weight-light mt-4 text-center">Ingresa tu correo electrónico y te enviaremos instrucciones para restablecer tu contraseña</h6>
                @livewire('auth.forgot-password')
            </div>
        </div>
    </div>
@endsection
