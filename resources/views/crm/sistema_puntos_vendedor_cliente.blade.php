@extends('layouts.dashboard_template')
@section('title','CRM')
@section('content')



    <div class="page-heading">
        <x-navegation-view text="Opciones." />
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4>GESTIÓN DE VENDEDORES</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <a href="{{route('CRM.gestion_vendedores')}}" class="btn btn-success">Ingresar</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4>GESTIÓN DE CAMPAÑAS</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <a href="{{route('CRM.gestion_campanias')}}" class="btn btn-success">Ingresar</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4>GESTIÓN DE PREMIOS</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <a href="{{route('CRM.gestion_premios')}}" class="btn btn-success">Ingresar</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4>GESTIÓN DE PUNTOS</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <a href="{{route('CRM.gestion_puntos')}}" class="btn btn-success">Ingresar</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4>SELECCIONAR PREMIOS</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <a href="{{route('CRM.seleccionar_premios')}}" class="btn btn-success">Ingresar</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4>REPORTES</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <a href="{{route('CRM.reporte_campania')}}" class="btn btn-success">Ingresar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{asset('js/domain.js')}}"></script>
    {{--    <script src="{{asset('js/configuration.js')}}"></script>--}}


@endsection
