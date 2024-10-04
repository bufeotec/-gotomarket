
@extends('layouts.auth_template')
@section('title','Iniciar Sesión')
@section('content')

    <div class="row w-100 mx-0">
        <div class="col-lg-4 col-md-4 col-sm-6 mx-auto">
            <div class="auth-form-light text-left py-5 px-4 px-sm-5">
                <div class="brand-logo d-flex justify-content-center align-items-center">
                    <img src="{{asset('isologoCompleteGo.png')}}" style="width: 50px!important;" alt="logo">
                    <h3 class="mb-0 font-weight-bold ml-2" style="font-weight: 800!important;">Go To Market SAC</h3>
                </div>
                <h4>Hola, Comencemos.</h4>
                <h6 class="font-weight-light">Inicie sesión para continuar.</h6>
                @livewire('auth.login')
            </div>
        </div>
    </div>
@endsection
