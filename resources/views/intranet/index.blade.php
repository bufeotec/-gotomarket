
@extends('layouts.dashboard_template')
@section('title','Panel Principal')
@section('content')

    @if (session()->has('status'))
        <x-alert text="{{ session('status') }}" type="success" duration="3" />
    @endif
    @if (session()->has('error'))
        <x-alert text="{{ session('error') }}" type="danger" duration="3" />
    @endif
{{--    <div class="page-heading">--}}
{{--        <h3>Estadísticas</h3>--}}
{{--        <p class="text-subtitle text-muted">Información correspondiente a camiones y transportistas.</p>--}}
{{--    </div>--}}
    @php
        $id_users = \Illuminate\Support\Facades\Auth::id();
        if ($id_users){
            $user = \App\Models\User::find($id_users);
            $role = $user->roles()->first();
            $roleName = "";
            if ($role) {
                $roleName = $role->name;
            }
             if (file_exists($user->profile_picture)){
                $rutaImagen = $user->profile_picture;
            }else{
                $rutaImagen = "assets/images/faces/1.jpg";
            }
        }
    @endphp

    <div class="mt- mb-3">
        <img src="{{asset('banner_gtm.png')}}" style="width: 100%">
    </div>

{{--    <div class="page-content">--}}
{{--        <section class="row">--}}
{{--            <div class="col-12 col-lg-9">--}}
{{--                <div class="row">--}}
{{--                    <div class="col-6 col-lg-3 col-md-6">--}}
{{--                        <div class="card">--}}
{{--                            <div class="card-body px-3 py-4-5">--}}
{{--                                <div class="row">--}}
{{--                                    <div class="col-md-4">--}}
{{--                                        <div class="stats-icon purple">--}}
{{--                                            <i class="iconly-boldShow"></i>--}}
{{--                                            <i class="fa-solid fa-people-carry-box"></i>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <div class="col-md-8">--}}
{{--                                        <h6 class="text-muted font-semibold">Transportistas</h6>--}}
{{--                                        <h6 class="font-extrabold mb-0">22</h6>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="col-6 col-lg-3 col-md-6">--}}
{{--                        <div class="card">--}}
{{--                            <div class="card-body px-3 py-4-5">--}}
{{--                                <div class="row">--}}
{{--                                    <div class="col-md-4">--}}
{{--                                        <div class="stats-icon blue">--}}
{{--                                            <i class="iconly-boldProfile"></i>--}}
{{--                                            <i class="fa-solid fa-truck"></i>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <div class="col-md-8">--}}
{{--                                        <h6 class="text-muted font-semibold">Camiones</h6>--}}
{{--                                        <h6 class="font-extrabold mb-0">40</h6>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="col-6 col-lg-3 col-md-6">--}}
{{--                        <div class="card">--}}
{{--                            <div class="card-body px-3 py-4-5">--}}
{{--                                <div class="row">--}}
{{--                                    <div class="col-md-4">--}}
{{--                                        <div class="stats-icon green">--}}
{{--                                            <i class="iconly-boldAdd-User"></i>--}}
{{--                                            <i class="fa-solid fa-car-on"></i>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <div class="col-md-8">--}}
{{--                                        <h6 class="text-muted font-semibold">C. Despachados</h6>--}}
{{--                                        <h6 class="font-extrabold mb-0">12</h6>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="col-6 col-lg-3 col-md-6">--}}
{{--                        <div class="card">--}}
{{--                            <div class="card-body px-3 py-4-5">--}}
{{--                                <div class="row">--}}
{{--                                    <div class="col-md-4">--}}
{{--                                        <div class="stats-icon red">--}}
{{--                                            <i class="iconly-boldBookmark"></i>--}}
{{--                                            <i class="fa-solid fa-car-side"></i>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <div class="col-md-8">--}}
{{--                                        <h6 class="text-muted font-semibold">C. Sin Despacho</h6>--}}
{{--                                        <h6 class="font-extrabold mb-0">28</h6>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="row">--}}
{{--                    <div class="col-12">--}}
{{--                        <div class="card">--}}
{{--                            <div class="card-header">--}}
{{--                                <h4>Estadísticas de Entregas Realizadas</h4>--}}
{{--                            </div>--}}
{{--                            <div class="card-body">--}}
{{--                                <div id="chart-profile-visit"></div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-12 col-lg-3">--}}
{{--                <div class="card">--}}
{{--                    <div class="card-body px-3 py-4-5">--}}
{{--                        <div class="row">--}}
{{--                            <div class="col-md-4">--}}
{{--                                <div class="stats-icon secondary">--}}
{{--                                    <i class="fa-solid fa-car-side"></i>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="col-md-8">--}}
{{--                                <h6 class="text-muted font-semibold">C. En Ruta</h6>--}}
{{--                                <h6 class="font-extrabold mb-0">10</h6>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="card">--}}
{{--                    <div class="card-header">--}}
{{--                        <h4>Estadísticas de octubre</h4>--}}
{{--                    </div>--}}
{{--                    <div class="card-body">--}}
{{--                        <div id="chart-visitors-profile"></div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </section>--}}
{{--    </div>--}}

    <script src="{{asset('assets/vendors/apexcharts/apexcharts.js')}}"></script>
    <script src="{{asset('assets/js/pages/dashboard.js')}}"></script>
@endsection
