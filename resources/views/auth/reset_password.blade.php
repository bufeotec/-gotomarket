
@extends('layouts.auth_template')
@section('title','Restablecer la contraseña')
@section('content')

    <div class="row w-100 mx-0">
        <div class="col-lg-4 mx-auto">
            <div class="auth-form-light text-left py-5 px-4 px-sm-5">
                <div class="brand-logo text-center">
                    <img src="{{asset('logoComplete.png')}}" style="width: 200px!important;" alt="logo">
                </div>
                <h3 class="text-center text-primary">Restablecer la contraseña</h3>
                <h6 class="font-weight-light mt-4 text-center">Para {{ $request->email }}</h6>
                @livewire('auth.reset-password',['token'=>$request->route('token'),'email'=>$request->email])
            </div>
        </div>
    </div>
@endsection
