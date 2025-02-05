@extends('layouts.dashboard_template')
@section('title', 'Gestión de facturas y programación')
@section('content')

    <link rel="stylesheet" href="{{asset('js/select2/dist/css/select2.min.css')}}">
    <script src="{{asset('js/select2/dist/js/select2.min.js')}}"></script>

    <div class="page-heading">
        <x-navegation-view text="Gestión y programación de facturas y camiones." />
    </div>

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h4>Facturas por Aprobar</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <a href="{{route('Programacioncamion.facturas_aprobar')}}" class="btn btn-success">Ingresar</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h4>Programación Camiones</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <a href="{{route('Programacioncamion.programar_camion')}}" class="btn btn-success">Ingresar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{asset('js/domain.js')}}"></script>

@endsection
